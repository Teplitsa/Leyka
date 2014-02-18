<?php
/** Core class. */
class Leyka {
	
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @var string
	 */
	private $_version = LEYKA_VERSION;

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 * @var string
	 */
    private $_plugin_slug = 'leyka';

	/**
	 * Instance of this class.
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Slug of the plugin screen.
	 * @var string
	 */
    private $_plugin_screen_hook_suffix = null;

    /**
     * Gateways list.
     * @var array
     */
    private $_gateways = array();

    /** @var bool Set in true if gateways addition already processed. */
    private $_gateways_added = false;

    /** @var array Of WP_Error instances. */
    private $_form_errors = array();

    /** @var string Gateway URL to process payment data. */
    private $_payment_url = '';

    /** @var array Of key => value pairs of payment form vars to send to the Gateway URL. */
    private $_payment_vars = array();
	
	/**
     * Template list.
     * @var array
     */
	private $templates = null;
	
	/** Initialize the plugin by setting localization, filters, and administration functions. */
	private function __construct() {
        
        if( !get_option('leyka_permalinks_flushed') ) {

            add_action('init', function(){

                flush_rewrite_rules(false);
                update_option('leyka_permalinks_flushed', 1);
            });
        }

        // By default, we'll assume some errors in the payment form, so redirect will get us back to it:
        $this->_payment_form_redirect_url = wp_get_referer();

		// Load files
		$this->load_plugin_files();
		
		// Load public-facing style sheet and JavaScript.
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
				
		// Post types
		add_action('init', array($this, 'register_post_types'), 9);

        if( !session_id() )
            add_action('init', 'session_start', -2);

        // Modules can add/remove all gateways here:
//        $this->_gateways = apply_filters('leyka_gateways_init', $this->_gateways);

		// Admin
		if(is_admin())
			$this->admin_setup();

		/** Handlers: */

        add_action('parse_request', function($request){
            $request = $request->request;
            if(stristr($request, 'leyka/service') !== FALSE) { // Leyka service URL
                $request = explode('/', trim(str_replace('leyka/service', '', $request), '/'));
                
                // $request[0] - Payment method's ID, $request[1] - service action:
                do_action('leyka_service_call-'.$request[0], $request[1]);
                exit();
            }

//            set_transient('leyka_yandex_test_cho', '');
//            set_transient('leyka_yandex_test_pa', '');

//            $cho = get_transient('leyka_yandex_test_cho');
//            $pa = get_transient('leyka_yandex_test_pa');
//            if(trim($pa)) {
//                echo '<pre>Cho: ' . print_r($cho, TRUE) . '</pre>';
//                echo '<pre>Pa: ' . print_r($pa, TRUE) . '</pre>';

//                var_dump($cho);
//                var_dump($pa);
//            }
            
        });

        add_action('template_redirect', array($this, 'gateway_redirect_page'));

		$this->apply_formatting_filters(); // Internal formatting filters 
		
        do_action('leyka_initiated');
	}

    /** Return a single instance of this class */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if( !self::$_instance ) {
            self::$_instance = new self;

            do_action('leyka_add_gateway');
        }
		
        return self::$_instance;
    }
    
    public function __get($param) {
        switch($param) {
            case 'version': return $this->_version;
            case 'plugin_slug': return $this->_plugin_slug;
            case 'payment_url': return $this->_payment_url;
            case 'payment_vars': return $this->_payment_vars;
            default:
                return '';
        }
    }

    public function add_payment_form_error(WP_Error $error) {
        $this->_form_errors[] = $error;
    }

    /** @return bool */
    public function payment_form_has_errors() {
        return count($this->_form_errors) > 0;
    }

    /** @return array Of WP_Error instances, if any. */
    public function get_payment_form_errors() {
        return $this->_form_errors;
    }

    /**
     * Wrapper to work with leyka_errors session var.
     * @param bool $anew
     */
    private function _add_session_errors($anew = false) {
        if(empty($_SESSION['leyka_errors']) || $anew)
            $_SESSION['leyka_errors'] = $this->get_payment_form_errors();
        else
            $_SESSION['leyka_errors'] = array_merge($_SESSION['leyka_errors'], $this->get_payment_form_errors());
    }

    /** @return bool */
    public function has_session_errors() {
        return !empty($_SESSION['leyka_errors']) && count($_SESSION['leyka_errors']);
    }

    /** @return array */
    public function get_session_errors() {
        return empty($_SESSION['leyka_errors']) ? array() : $_SESSION['leyka_errors'];
    }

    /** @return true */
    public function clear_session_errors() {
        return ($_SESSION['leyka_errors'] = array());
    }

    /**
     * Retrieve all available payment/donation statuses.
     * 
     * @return array of status_id => status label pairs
     */
    public function get_donation_statuses() {
        return leyka_get_donation_status_list();
    }

    /**
     * @return array Of Leyka_Gateway objects.
     */
    public function get_gateways() {
        return $this->_gateways;
    }

    /**
     * @todo Maybe, this method won't needed - it's work is done by filter.
     * @param Leyka_Gateway $gateway
     * @return bool
     */
    public function add_gateway(Leyka_Gateway $gateway) {
        if(empty($this->_gateways[$gateway->id]))
            $this->_gateways[$gateway->id] = $gateway;
        else
            return false;
    }

    /** Just in case */
    public function remove_gateway($gateway_id) {
        if( !empty($this->_gateways[$gateway_id]) )
            unset($this->_gateways[$gateway_id]);
    }

    /**
     * Fired when the plugin is activated.
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action,
     * false if WPMU is disabled or plugin is activated on an individual blog.
     */
	public static function activate($network_wide) {
        
        /** Set a flag to flush permalinks (needs to be done a bit later, than this activation itself): */
        update_option('leyka_permalinks_flushed', 0);

        /** Create a thank-you and sorry-donation-failed pages, if needed: */
        if( !leyka_options()->opt('success_page') ) {

            $page = new WP_Query(array(
                'post_type' => 'page',
                'name' => 'thank-you-for-your-donation',
                'posts_per_page' => 1,
                'post_status' => array(
                    'publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'
                ),
            ));
            $page = $page->get_posts();
            $page = reset($page);
        } else
            $page = get_post(leyka_options()->opt('success_page'));

//            echo '<pre>' . print_r($page, TRUE) . '</pre>'; die();

        if($page && $page->post_status != 'publish')
            wp_update_post(array(
                'ID' => $page->ID,
                'post_status' => 'publish',
            ));
        else if( !$page ) {
            $page = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_name' => 'thank-you-for-your-donation',
                'post_title' => __('Your donation is completed!', 'leyka'),
                'post_content' => __('We heartly thank you for your help!', 'leyka'),
//                '' => __('', 'leyka'),
            ));
            leyka_options()->opt('success_page', $page);
        }

        if( !leyka_options()->opt('failure_page') ) {

            $page = new WP_Query(array(
                'post_type' => 'page',
                'name' => 'sorry-donation-failure',
                'posts_per_page' => 1,
                'post_status' => array(
                    'publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'
                ),
            ));
            $page = $page->get_posts();
            $page = reset($page);
        } else
            $page = get_post(leyka_options()->opt('failure_page'));

        if($page && $page->post_status != 'publish')
            wp_update_post(array(
                'ID' => $page->ID,
                'post_status' => 'publish',
            ));
        else if( !$page ) {

            $page = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_name' => 'sorry-donation-failure',
                'post_title' => __('Your donation failed', 'leyka'),
                'post_content' => __('We are deeply sorry, but for some technical reason we failed to receive your donation. Please try again later!', 'leyka'),
//                '' => __('', 'leyka'),
            ));
            leyka_options()->opt('success_page', $page);
        }
    }

	/**
	 * Fired when the plugin is deactivated.
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action,
     * false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate($network_wide) {
		
        delete_option('leyka_permalinks_flushed');
	}
	
	/** Load additional plugin files */
	public function load_plugin_files(){
		require_once(LEYKA_PLUGIN_DIR.'/inc/leyka-class-payment-form.php');
	}
	
	function apply_formatting_filters() {
		add_filter('leyka_the_content', 'wptexturize');
		add_filter('leyka_the_content', 'convert_smilies');
		add_filter('leyka_the_content', 'convert_chars');
		add_filter('leyka_the_content', 'wpautop');			
	}
	
	/** Register and enqueue public-facing style sheet. */
	public function enqueue_styles() {
		wp_enqueue_style($this->_plugin_slug.'-plugin-styles', LEYKA_PLUGIN_BASE_URL.'css/public.css', array(), $this->_version);
	}

	/** Register and enqueues public-facing JavaScript files. */
	public function enqueue_scripts() {

		wp_enqueue_script(
            $this->_plugin_slug.'-modal',
            LEYKA_PLUGIN_BASE_URL.'js/jquery.leanModal.min.js', array('jquery'),
            $this->_version,
			true
        );

		wp_enqueue_script(
            $this->_plugin_slug.'-plugin-script',
            LEYKA_PLUGIN_BASE_URL.'js/public.js', array('jquery', $this->_plugin_slug.'-modal'),
            $this->_version,
			true
        );
		
		$js_data = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
            'correct_donation_amount_required' => __('Donation amount must be specified to submit the form', 'leyka'),
            'donation_amount_too_great' => __('Donation amount you entered is too great (maximum %s allowed)', 'leyka'),
            'donation_amount_too_small' => __('Donation amount you entered is too small (minimum %s allowed)', 'leyka'),
            'checkbox_check_required' => __('This checkbox must be checked to submit the form', 'leyka'),
            'amount_incorrect' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
            'text_required' => __('This field must be filled to submit the form', 'leyka'),
            'email_required' => __('Email must be filled to submit the form', 'leyka'),
            'email_invalid' => __('You have entered an invalid email', 'leyka'),
//            'email_regexp' => '',
		);

		wp_localize_script($this->_plugin_slug . '-plugin-script', 'leyka', $js_data);
	}

	/**
	 * Setup admin for the plugin
	 **/
	
	public function admin_setup(){
		
		require_once(LEYKA_PLUGIN_DIR.'/inc/leyka-admin.php');
		Leyka_Admin_Setup::get_instance();		
	}
	
	
	/**
     * Register post types.
     * Donation CPT:
     */
	function register_post_types(){

        //load related filtes here
        require_once(LEYKA_PLUGIN_DIR.'/inc/leyka-class-campaign.php');
        Leyka_Campaign_Management::get_instance();

        require_once(LEYKA_PLUGIN_DIR.'/inc/leyka-class-donation.php');
        Leyka_Donation_Management::get_instance();
		
		/** Donation CPT */
		$d_labels = array(
			'name'          => __('Donations', 'leyka'),
			'singular_name' => __('Donation', 'leyka'),
			'menu_name'     => __('Donations', 'leyka'),
			'all_items'     => __('Donations', 'leyka'),
			'add_new'       => __('New donation', 'leyka'),
			'add_new_item'  => __('Add new donation', 'leyka'),
			'edit_item'     => __('Donation profile', 'leyka'),
			'new_item'      => __('New donation', 'leyka'),
			'view_item'     => __('View donation', 'leyka'),
			'search_items'  => __('Search donation', 'leyka'),
			'not_found'     => __('Donations not found', 'leyka'),
			'not_found_in_trash' => __('Donations not found in Trash', 'leyka')
		);
		$d_args = array(
			'label' => __('Donations', 'leyka'),
			'labels' => $d_labels,
			'exclude_from_search' => true, //?
			'public' => true,
			'show_ui' => true,			
			'show_in_nav_menus' => false,
			'show_in_menu' => 'leyka',
			'show_in_admin_bar' => false,			
			'supports' => false, // array(),
			'register_meta_box_cb' => array($this, 'leyka_donation_metaboxes'),
			'taxonomies' => array(),
			'has_archive' => false,
			'rewrite' => array('slug' => 'donation', 'with_front' => false)
		);

		register_post_type('leyka_donation', $d_args);

        /** Donation editing messages */
        add_filter('post_updated_messages', function($messages) {
            global $post, $post_ID;

            $messages['leyka_donation'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__('Donation updated. <a href="%s">View it</a>', 'leyka'), esc_url(get_permalink($post_ID))),
                2 => __('Field updated.', 'leyka'),
                3 => __('Field deleted.', 'leyka'),
                4 => __('Donation updated.', 'leyka'),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf( __('Donation restored to revision from %s', 'leyka'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => sprintf(__('Donation published. <a href="%s">View it</a>', 'leyka'), esc_url(get_permalink($post_ID))),
                7 => __('Donation saved.', 'leyka'),
                8 => sprintf(__('Donation submitted. <a target="_blank" href="%s">Preview it</a>', 'leyka'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9 => sprintf(__('Donation scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview it</a>', 'leyka'),
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n(__( 'M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10 => sprintf(__('Donation draft updated. <a target="_blank" href="%s">Preview it</a>', 'leyka'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            );

            return $messages;
        });

		/** Campaign CPT: */
		$p_labels = array(
			'name'          => __('Campaigns', 'leyka'),
			'singular_name' => __('Campaign', 'leyka'),
			'menu_name'     => __('Campaigns', 'leyka'),
			'all_items'     => __('All Campaigns', 'leyka'),
			'add_new'       => __('New campaign', 'leyka'),
			'add_new_item'  => __('Add new campaign', 'leyka'),
			'edit_item'     => __('Edit campaign', 'leyka'),
			'new_item'      => __('New campaign', 'leyka'),
			'view_item'     => __('View campaign', 'leyka'),
			'search_items'  => __('Search campaigns', 'leyka'),
			'not_found'     => __('Campaigns not found', 'leyka'),
			'not_found_in_trash' => __('Campaigns not found in Trash', 'leyka')
		);
		$p_args = array(			
			'labels' => $p_labels,			
			'exclude_from_search' => true, //?
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => 'leyka',			
			'show_in_admin_bar' => false,			
			'supports' => array('title', 'editor', 'thumbnail'), // custom-fileds ?
			'register_meta_box_cb' => array($this, 'leyka_campaign_metaboxes'),
			'taxonomies' => array(),
			'has_archive' => false,
			'rewrite' => array('slug' => 'campaign', 'with_front' => false)
		);

		register_post_type('leyka_campaign', $p_args);

		add_action('admin_menu',  function(){ // manualy add new campaign menu
			add_submenu_page(
				'leyka',
				__('New campaign', 'leyka'),
				__('New campaign', 'leyka'),
				'edit_posts',
				'post-new.php?post_type=leyka_campaign'
			);
		}, 20);

        /** Campaign editing messages */
        add_filter('post_updated_messages', function($messages) {
            global $post, $post_ID;

            $messages['leyka_campaign'] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__('Campaign updated. <a href="%s">View it</a>', 'leyka'), esc_url(get_permalink($post_ID))),
                2 => __('Field updated.', 'leyka'),
                3 => __('Field deleted.', 'leyka'),
                4 => __('Campaign updated.', 'leyka'),
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf( __('Campaign restored to revision from %s', 'leyka'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => sprintf(__('Campaign published. <a href="%s">View it</a>', 'leyka'), esc_url(get_permalink($post_ID))),
                7 => __('Campaign saved.', 'leyka'),
                8 => sprintf(__('Campaign submitted. <a target="_blank" href="%s">Preview it</a>', 'leyka'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9 => sprintf(__('Campaign scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview it</a>', 'leyka'),
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n(__( 'M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10 => sprintf(__('Campaign draft updated. <a target="_blank" href="%s">Preview it</a>', 'leyka'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            );

            return $messages;
        });

        register_post_status('submitted', array(
            'label'                     => _x('Submitted', '«Submitted» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Submitted <span class="count">(%s)</span>',
                'Submitted <span class="count">(%s)</span>',
                'leyka'
            )
        ));

        register_post_status('funded', array(
            'label'                     => _x('Funded', '«Completed» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Funded <span class="count">(%s)</span>',
                'Funded <span class="count">(%s)</span>',
                'leyka'
            )
        ));

        register_post_status('refunded', array(
            'label'                     => _x('Refunded', '«Refunded» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Refunded <span class="count">(%s)</span>',
                'Refunded <span class="count">(%s)</span>',
                'leyka'
            )
        ));

        register_post_status('failed', array(
            'label'                     => _x('Failed', '«Failed» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Failed <span class="count">(%s)</span>',
                'Failed <span class="count">(%s)</span>',
                'edd'
            )
        ));
		
		//registration ended
		do_action('leyka_cpt_registered');
	}

	public function leyka_campaign_metaboxes() {
		do_action('leyka_campaign_metaboxes');
	}

    public function leyka_donation_metaboxes() {
		do_action('leyka_donation_metaboxes');
	}
	
	/**
     * Payment form submissions
     * @var $query WP_Query
     */
    public function gateway_redirect_page() {

        global $wp_query;

		if(isset($wp_query->query_vars['name']) && $wp_query->query_vars['name'] == 'leyka-process-donation') {
            
            if(empty($_POST)) {
                wp_redirect(site_url());
                exit();
            }

			$this->do_payment_form_submission();

			if($this->payment_form_has_errors()) {

                $this->_add_session_errors(); // Error handling
                wp_redirect(wp_get_referer());
			} else {

                header('HTTP/1.1 200 OK');

                add_action('wp_enqueue_scripts', function(){

                    wp_enqueue_script(
                        leyka()->plugin_slug.'-form-autosubmit',
                        LEYKA_PLUGIN_BASE_URL.'js/public.autosubmit.js',
                        array('jquery'),
                        LEYKA_VERSION,
                        false
                    );
                });

//                add_filter('wp_title', function($title, $separator){
//                    $title .= get_bloginfo('name')." $separator ".__('Redirect to the gateway', 'leyka');
//
//                    return $title;
//                }, 10, 2);

                include(LEYKA_PLUGIN_DIR.'templates/leyka-gateway-redirect-page.php'); // Show Gateway redirect page
                exit();
            }
		}
	} // template_redirect

//    public function gateway_redirect_page_display($content) {
//        if(is_page('leyka-process-donation')) {
//
//            add_action('wp_enqueue_scripts', function(){
//
//                wp_enqueue_script(
//                    $this->_plugin_slug.'-form-autosubmit',
//                    LEYKA_PLUGIN_BASE_URL.'js/public.autosubmit.js',
//                    array('jquery'),
//                    $this->_version,
//                    true
//                );
//            });
//
////            $content .= "<form id='leyka-auto-submit' action='{$this->_payment_url}' method='post'>";
////
////            foreach($this->_payment_vars as $name => $value) {
////                $content .= "<input type='hidden' name='$name' value='$value' />";
////            }
////
////            $content .= '</form>';
//        }

//        return $content;
//    }

    public function do_payment_form_submission() {

        $this->clear_session_errors(); // Clear all previous sumbits errors, if there are some
//        $this->

        if( !wp_verify_nonce($_REQUEST['_wpnonce'], 'leyka_payment_form') ) {
            
            $error = new WP_Error('wrong_form_submission', __('Wrong nonce in submitted form data', 'leyka'));
            $this->add_payment_form_error($error);
        }

        $pm = explode('-', $_POST['leyka_payment_method']);
        if( !$pm || count($pm) < 2 ) {

            $error = new WP_Error('wrong_gateway_pm_data', __('Wrong gateway or/and payment method in submitted form data', 'leyka'));
            $this->add_payment_form_error($error);
        }

        if($this->payment_form_has_errors())
            return;

        $donation_id = $this->log_submission();

        /** @todo We may want to replace whole $_POST with some specially created array */
        do_action('leyka_payment_form_submission', $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);

        $this->_payment_vars = apply_filters('leyka_submission_form_data', $this->_payment_vars, $pm[1], $donation_id);

        $this->_payment_url = apply_filters('leyka_submission_redirect_url', $this->_payment_url, $pm[1]); 
        if( !$this->_payment_url ) {

            $error = new WP_Error('wrong_pm_url', __('Wrong payment method URL to submit the form data', 'leyka'));
            $this->add_payment_form_error($error);
        } /* else
            $this->clear_session_errors();*/
        
        if($this->payment_form_has_errors()) // No logging needed if submit attempt have failed
            wp_delete_post($donation_id, true);
	}

    /** Save a base submission info and return new donation ID, so gateway can add it's specific data to the logs. */
    public function log_submission() {

        add_action('save_post', array($this, 'finalize_log_submission'), 2, 2);

		$campaign = get_post((int)$_POST['leyka_campaign_id']);
		$purpose_text = get_post_meta($campaign->ID, 'payment_title', true);
		$purpose_text = (empty($purpose_text) && $campaign->post_title) ? $campaign->post_title : $purpose_text;
        $res = wp_insert_post(array(
            'post_type' => 'leyka_donation',
            'post_status' => 'submitted',
            'post_title' => $purpose_text ?
                $purpose_text : leyka_options()->opt('donation_purpose_text'),
        ), true);
        
        if(is_wp_error($res)) {
            /** @todo Modify this method so it can take any WP_Error as a param, then call it here: */
//            $this->_add_session_errors();
            return false;
        } else {

            $pm_data = leyka_pf_get_payment_method_value();

            update_post_meta($res, 'leyka_donation_amount', leyka_pf_get_amount_value());
            update_post_meta($res, 'leyka_donation_currency', leyka_pf_get_currency_value());
            update_post_meta($res, 'leyka_donor_name', leyka_pf_get_donor_name_value());
            update_post_meta($res, 'leyka_donor_email', leyka_pf_get_donor_email_value());
            update_post_meta($res, 'leyka_payment_method', $pm_data['payment_method_id']);
            update_post_meta($res, 'leyka_gateway', $pm_data['gateway_id']);
            update_post_meta($res, 'leyka_campaign_id', leyka_pf_get_campaign_id_value());
            
            if( !get_post_meta($res, '_leyka_donor_email_date', true) )
                update_post_meta($res, '_leyka_donor_email_date', 0);
            if( !get_post_meta($res, '_leyka_managers_emails_date', true) )
                update_post_meta($res, '_leyka_managers_emails_date', 0);

            update_post_meta(
                $res,
                '_status_log',
                array(array('date' => time(), 'status' => 'submitted'))
            );

            do_action('leyka_log_donation', $res);

            return $res;
        }
	}

    /**
     * A save_post hook wrapper method. It must be used by gateways to add their specific data
     * to the donation in DB while it's saving.
     * 
     * @param $donation_id integer
     * @param $donation WP_Post
     */   
    public function finalize_log_submission($donation_id, WP_Post $donation) {
        if($donation->post_type != 'leyka_donation')
            return;

        do_action('leyka_logging_new_donation', $donation_id, $donation);
    }
	
	/**
	 * Templates manipulations
	 **/
	public function get_templates() { 
		if(empty($this->templates)) { 
			$this->templates = glob(STYLESHEETPATH.'/leyka-template-*.php');
			if($this->templates === false || empty($this->templates)) { // if glob hits an error, it returns false
				// Let's search in own folder:
				
				$this->templates = glob(LEYKA_PLUGIN_DIR . 'templates/leyka-template-*.php');
				
				if($this->templates === false)
					$this->templates = array();
			}
			// get data
			$this->templates = array_map(array($this, 'get_template_data'), $this->templates);
		}
		return (array)$this->templates;
	}

	public function get_template_data($file) {
		$headers = array(
			'name' => 'Leyka Template',
			'description' => 'Description'			
		);

		$data = get_file_data($file, $headers);
		$data['file'] = $file;
		$data['basename'] = basename($file);
		$id = explode('-', str_replace('.php', '', $data['basename']));
		$data['id'] = end($id); //otherwise we'll get error in php 5.4.x
		if(empty($data['name']))
			$data['name'] = $data['basename'];
		return $data;
	}
	
	public function get_template($basename) {
		
		$templates = $this->get_templates();
		if( !$templates )
			return false;
		
		$active = '';
		foreach($templates as $template) {
			
			$cur_basename = explode('-', str_replace('.php', '', $template['basename']));
            $cur_basename = end($cur_basename); //otherwise error in PHP 5.4.x
			if($cur_basename == $basename) {
				$active = $template;
                break;
            }
		}
		
		return $active;
	}

} //class end

// Shorthands for singletons instances:

/**
 * @return Leyka Core object
 */
function leyka() {
    return Leyka::get_instance();
}

/** Orphan strings to localize */
__('Radios', 'leyka');
__('Radio options for each payment method', 'leyka');
__('Toggles', 'leyka');
__('Toggled options for each payment method', 'leyka');