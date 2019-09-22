<?php if( !defined('WPINC') ) die;

/**
 * Leyka Admin setup
 **/

class Leyka_Admin_Setup extends Leyka_Singleton {

	protected static $_instance = null;

	/** @var WP_List_Table */
	protected $_donations_list_table = null;

    /** @var WP_List_Table */
	protected $_donors_list_table = null;

	protected function __construct() {

	    require_once LEYKA_PLUGIN_DIR.'/inc/leyka-admin-functions.php';

		add_action('admin_menu', array($this, 'admin_menu_setup'), 9);

		if(leyka_options()->opt('donor_management_available')) { // Save Donors pages custom options
            add_filter('set-screen-option', function($status, $option, $value) {

                if($option === 'donors_per_page' && (int)$value > 0) {
                    update_user_option(get_current_user_id(), 'donors_per_page', $value);
                }

                return $value;

            }, 10, 3);
        }

		add_action('admin_enqueue_scripts', array($this, 'load_frontend_scripts'));

        add_action('admin_init', array($this, 'pre_admin_actions'));

        add_action('wp_ajax_leyka_send_feedback', array($this, 'ajax_send_feedback'));

        add_filter('plugin_row_meta', array($this, 'set_plugin_meta'), 10, 2);

		add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, array($this, 'add_plugins_list_links'));

        // Metaboxes support where it is needed:
        add_action('leyka_pre_donor_info_actions', array($this, 'full_metaboxes_support'));

		add_action('leyka_post_admin_actions', array($this, 'show_footer'));

		// Donors' tags on the user profile page:
        add_action('show_user_profile', array($this, 'show_user_profile_donor_fields'));
        add_action('edit_user_profile', array($this, 'show_user_profile_donor_fields'));

        add_action('personal_options_update', array($this, 'save_user_profile_donor_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_donor_fields'));

        // Portlet controller API:
		require_once LEYKA_PLUGIN_DIR.'/inc/leyka-class-portlet-controller.php';

    }

    public function set_plugin_meta($links, $file) {

        if($file == LEYKA_PLUGIN_INNER_SHORT_NAME) {
            $links[] = '<a href="https://github.com/Teplitsa/Leyka/">GitHub</a>';
        }

        return $links;

    }

    protected function _show_admin_template($template_id) {

	    if( !$template_id ) {
	        return;
        }

	    $template_file = LEYKA_PLUGIN_DIR.'/inc/admin-templates/leyka-admin-'.$template_id.'.php';
	    if(file_exists($template_file)) {
	        require $template_file;
        }

    }

    // Support the full abilities of the metaboxes on any plugin's page:
    public function full_metaboxes_support($current_stage = false) {?>

        <form style="display:none;" method="get" action="#">
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
        </form>

    <?php }

    public function pre_admin_actions() {

        function leyka_admin_title($admin_title) {

            if(isset($_GET['page']) && $_GET['page'] === 'leyka_settings_new' && isset($_GET['screen'])) {

                $screen_full_id = explode('-', $_GET['screen']);

                // $screen_full_id[0] - view type (e.g. 'wizard' or 'control_panel')
                // $screen_full_id[1] - settings area given (e.g. 'init').

                require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php');

                $admin_title = get_bloginfo('name')
                    .' &#8212; '
                    .Leyka_Settings_Factory::get_instance()->get_controller($screen_full_id[1])->title;

            } else if(isset($_GET['page']) && $_GET['page'] === 'leyka_donor_info' && !empty($_GET['donor'])) {

                try {
                    $donor = new Leyka_Donor(absint($_GET['donor']));
                } catch(Exception $e) {
                    return $admin_title;
                }

                $admin_title = sprintf(__('Leyka: Donor %s'), $donor->name).' &lsaquo; '.get_bloginfo('name');

            }

            return $admin_title;

        }
        add_filter('admin_title', 'leyka_admin_title');

        // Leyka admin notices:
        if(isset($_GET['leyka_reset_msg'])) {
            update_option('leyka_admin_notice_'.$_GET['leyka_reset_msg'], 0);
        }

        if(isset($_GET['leyka-hide-notice']) && isset($_GET['_leyka_notice_nonce'])) {

            if( !wp_verify_nonce($_GET['_leyka_notice_nonce'], 'leyka_hide_notice_nonce') ) {
                wp_die(__('Action failed. Please refresh the page and retry.', 'leyka'));
            }

            if( !current_user_can('manage_options') ) {
                wp_die(__('Action failed: insufficient permissions.', 'leyka'));
            }

            update_option('leyka_admin_notice_'.sanitize_text_field($_GET['leyka-hide-notice']), 1);

        }

        if( !get_option('leyka_admin_notice_v3_update') && (empty($_GET['page']) || $_GET['page'] !== 'leyka_settings_new') ) {

            function leyka_admin_notice_v3_update() {?>

                <div id="message" class="updated leyka-message">
                    <a class="leyka-message-close notice-dismiss" href="<?php echo esc_url(wp_nonce_url(remove_query_arg('leyka_reset_msg', add_query_arg('leyka-hide-notice', 'v3_update')), 'leyka_hide_notice_nonce', '_leyka_notice_nonce'));?>">
                        <?php esc_html_e('Dismiss', 'leyka');?>
                    </a>
                    <p><?php printf(esc_html__('Hello! Thank you for updating Leyka plugin to the 3rd version. Please read about all new features %shere%s.', 'leyka'), '<a href="//te-st.ru/2018/12/18/leyka-3-update/" target="_blank">', '</a>');?></p>
                </div>
            <?php
            }
            add_action('admin_notices', 'leyka_admin_notice_v3_update');

        }

        add_filter('leyka_admin_portlet_title', function($portlet_title, $portlet_id){
            return $portlet_id === 'donations-dynamics' ? $portlet_title.',&nbsp;'.leyka_get_currency_label() : $portlet_title;
        }, 10, 2);

        if(isset($_GET['page']) && $_GET['page'] === 'leyka_donor_info' && !empty($_GET['donor'])) {

            // Add all metaboxes:
            add_meta_box('leyka_donor_info', __("Donor's data", 'leyka'), array($this, 'donor_data_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
            add_meta_box('leyka_donor_admin_comments', __('Comments', 'leyka'), array($this, 'donor_comments_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
            add_meta_box('leyka_donor_tags', __('Tags'), array($this, 'donor_tags_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
            add_meta_box('leyka_donor_donations', __('Donations', 'leyka'), array($this, 'donor_donations_metabox'), 'dashboard_page_leyka_donor_info', 'normal');

        }

    }

    public function admin_menu_setup() {

        // Leyka menu root:
        add_menu_page(__('Leyka Dashboard', 'leyka'), __('Leyka', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        add_submenu_page('leyka', __('Leyka Dashboard', 'leyka'), __('Dashboard', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        if(leyka_get_donations_storage_type() === 'post') { // Post-based donations storage

            add_submenu_page('leyka', __('Donations', 'leyka'), __('Donations', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Donation_Management::$post_type);
            add_submenu_page('leyka', __('New correctional donation', 'leyka'), _x('Add new', 'donation', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Donation_Management::$post_type);

        } else { // Separated donations storage

            $hook = add_submenu_page('leyka', __('Donations', 'leyka'), __('Donations', 'leyka'), 'leyka_manage_donations', 'leyka_donations', array($this, 'donations_list_screen'));
            add_action("load-$hook", array($this, 'donations_list_screen_options'));

        }

        add_submenu_page('leyka', __('Campaigns', 'leyka'), __('Campaigns', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Campaign_Management::$post_type);

        add_submenu_page('leyka', __('New campaign', 'leyka'), _x('Add new', 'campaign', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Campaign_Management::$post_type);

        // Donors' tags taxonomy:
        if(leyka()->opt('donor_management_available')) {

            // Donors list page:
            $hook = add_submenu_page('leyka', __('Donors', 'leyka'), __('Donors', 'leyka'), 'leyka_manage_donations', 'leyka_donors', array($this, 'donors_list_screen'));
            add_action("load-$hook", array($this, 'donors_list_screen_options'));

            // Donors tags page:
            $taxonomy = get_taxonomy(Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME);

            add_submenu_page('leyka', esc_attr($taxonomy->labels->menu_name), esc_attr($taxonomy->labels->menu_name), $taxonomy->cap->manage_terms, 'edit-tags.php?taxonomy='.$taxonomy->name);

            add_filter('submenu_file', function($submenu_file) { // Fix for parent menu

                global $parent_file;
                if($submenu_file == 'edit-tags.php?taxonomy='.Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME) {
                    $parent_file = 'leyka';
                }
                return $submenu_file;

            });

        }

        add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), 'leyka_manage_options', 'leyka_settings', array($this, 'settings_screen'));

        add_submenu_page('leyka', __('Contact us', 'leyka'), __('Feedback', 'leyka'), 'leyka_manage_donations', 'leyka_feedback', array($this, 'feedback_screen'));

        // Fake pages:
        add_submenu_page(NULL, 'Leyka Wizard', 'Leyka Wizard', 'leyka_manage_options', 'leyka_settings_new', array($this, 'settings_new_screen'));

        add_submenu_page(NULL, "Donor's info", "Donor's info", 'leyka_manage_options', 'leyka_donor_info', array($this, 'donor_info_screen'));
        // Fake pages - END

        do_action('leyka_admin_menu_setup');

        global $submenu;

        if( !empty($submenu['leyka']) ) {
            $submenu['leyka'] = apply_filters('leyka_admin_menu_order', $submenu['leyka']);
		}

    }

	/**
     * Settings link in plugin list table.
     *
     * @param $links array
     * @return array
     */
	public function add_plugins_list_links($links) {

		$links[] = '<a href="'.admin_url('admin.php?page=leyka_settings').'">'.__( 'Settings', 'leyka').'</a>';
		return $links;

	}

	public function dashboard_screen() {

		if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('Sorry, but you do not have permissions to access this page.', 'leyka'));
        }

        do_action('leyka_pre_dashboard_actions');

		$this->_show_admin_template('dashboard-page');

		do_action('leyka_post_dashboard_actions');
        do_action('leyka_post_admin_actions');

	}

	public function show_admin_portlet($portlet_id, array $params = array()) {

	    $portlet_file = LEYKA_PLUGIN_DIR.'/inc/portlets/leyka-'.$portlet_id.'.php';
	    if( !file_exists($portlet_file) ) {
	        return;
        }

	    $controller_file = LEYKA_PLUGIN_DIR.'/inc/portlets/'.$portlet_id.'/leyka-class-'.$portlet_id.'-portlet-controller.php';
	    if(file_exists($controller_file)) {
            require_once $controller_file;
        }

	    $portlet_data = get_file_data($portlet_file, array(
            'name' => 'Leyka Portlet',
            'description' => 'Description',
            'title' => 'Title',
            'thumbnail' => 'Thumbnail',
        ));?>

	    <div class="leyka-admin-portlet portlet-<?php echo esc_attr($portlet_id);?>">

            <div class="portlet-header">
                <img src="<?php echo esc_url(LEYKA_PLUGIN_BASE_URL.trim($portlet_data['thumbnail'], '/'));?>" alt="">
                <?php echo apply_filters('leyka_admin_portlet_title', esc_attr__($portlet_data['title'], 'leyka'), $portlet_id);?>
            </div>

            <div class="portlet-content"><?php require_once($portlet_file);?></div>

        </div>

    <?php
	}

	public function has_banners($page = false, $location = false) {
	    return !get_user_meta(get_current_user_id(), 'leyka_dashboard_banner_closed', true);
    }

    public function show_banner($page = false, $location = false) {?>
    
        <div class="banner-wrapper">
        	<div class="banner-inner">
                <a href="<?php echo admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init');?>" class="banner">
                	<img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/banner-run-wizard.svg" alt="">
            	</a>
            	<a class="close" href="#" title="<?php esc_html_e('Close permanently', 'leyka');?>"> </a>
        	</div>
        </div>

    <?php
    }

    /**
     * Display Donor related fields on the User profile admin page.
     *
     * @param $donor_user WP_User
     */
    public function show_user_profile_donor_fields(WP_User $donor_user) {

        if( !current_user_can('administrator') || !leyka_options()->opt('donor_management_available') ) {
            return;
        }?>

        <table class="form-table">
            <tr>
                <th>
                    <label for="leyka-donors-tags-field"><?php _e('Donor tags', 'leyka');?></label>
                </th>
                <td>
                    <?php $all_donors_tags = get_terms(array(
                        'taxonomy' => Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                        'hide_empty' => false,
                    ));

                    $donor_user_tags = wp_get_object_terms(
                        $donor_user->ID,
                        Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                        array('fields' => 'ids')
                    );?>

                    <select id="leyka-donors-tags-field" multiple="multiple" name="leyka_donor_tags[]">
                    <?php foreach($all_donors_tags as $donor_tag) {?>
                        <option value="<?php echo esc_attr($donor_tag->term_id);?>" <?php echo in_array($donor_tag->term_id, $donor_user_tags) ? 'selected="selected"' : '';?>>
                            <?php echo esc_html($donor_tag->name);?>
                        </option>
                    <?php }?>
                    </select>
                </td>
            </tr>
        </table>
        <?php

    }

    /**
     * Handle Donor related fields for the User profile admin page.
     *
     * @param $donor_user_id integer
     * @return boolean True if fields values are saved, false otherwise.
     */
    public function save_user_profile_donor_fields($donor_user_id) {

        if( !current_user_can('administrator') ) {
            return false;
        }

        array_walk($_POST['leyka_donor_tags'], function( &$value ){
            $value = (int)$value;
        });

        return !is_wp_error(wp_set_object_terms(
            $donor_user_id,
            $_POST['leyka_donor_tags'],
            Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME
        ));

    }

	public function is_separate_forms_stage($stage) {
		return in_array($stage, array('email', 'beneficiary', 'technical', 'view', 'additional'));
	}

	// (Separate stored) Donations list methods:
    public function donations_list_screen_options() {

        add_screen_option('per_page', array(
            'label' => __('Donations per page', 'leyka'),
            'default' => 20,
            'option' => 'donations_per_page',
        ));

        if(leyka_get_donations_storage_type() === 'post') {

            wp_redirect(admin_url('edit.php?post_type='.Leyka_Donation_Management::$post_type));
            exit;

        }

        require_once(LEYKA_PLUGIN_DIR.'inc/admin-lists/leyka-class-donations-list-table.php');

        $this->_donations_list_table = new Leyka_Admin_Donations_List_Table();

    }
    public function donations_list_screen() {

        if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        do_action('leyka_pre_donations_list_actions');

        $this->_show_admin_template('donations-list-page');

        do_action('leyka_post_donations_list_actions');
//        do_action('leyka_post_admin_actions'); // It's a "default markup" page, so we don't need this hook

    }
    // Donations list methods - END

    // Donors list methods:
    public function donors_list_screen_options() {

        add_screen_option('per_page', array(
            'label' => __('Donors per page', 'leyka'),
            'default' => 20,
            'option' => 'donors_per_page',
        ));

        require_once(LEYKA_PLUGIN_DIR.'inc/admin-lists/leyka-class-donors-list-table.php');

        $this->_donors_list_table = new Leyka_Admin_Donors_List_Table();

    }
    public function donors_list_screen() {

        if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        do_action('leyka_pre_donors_list_actions');

        $this->_show_admin_template('donors-list-page');

        do_action('leyka_post_donors_list_actions');
        do_action('leyka_post_admin_actions');

    }
    // Donors list methods - END

	public function settings_screen() {

		if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        $current_stage = $this->get_current_settings_tab();

		require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php'); // Basic Controller class
        require_once(LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-admin-template-tags.php');

		do_action('leyka_pre_settings_actions', $current_stage);

	    if( // Process settings change
	        (
	            !empty($_POST["leyka_settings_{$current_stage}_submit"])
                || !empty($_POST["leyka_settings_stage-{$current_stage}_submit"])
            )
	        /** @todo Find what's wrong with the nonce check below: */
//	        && wp_verify_nonce('_leyka_nonce', "leyka_settings_{$current_stage}")
        ) {

			do_action('leyka_settings_submit', $current_stage);
			do_action("leyka_settings_{$current_stage}_submit");

		}

	    $this->_show_admin_template('settings-page');?>

	<?php do_action('leyka_post_settings_actions');
        do_action('leyka_post_admin_actions');

	}

	/** Settings factory-controlled display (ATM, Wizards only) */
	public function settings_new_screen() {

	    if(empty($_GET['screen']) || count(explode('-', $_GET['screen'])) < 2) {

	        $this->settings_screen();
	        return;

	    }

	    $screen_full_id = explode('-', $_GET['screen']);

	    // Normally, we'd constuct settings view based on
	    // - view type ([0], e.g. 'wizard' or 'control_panel')
	    // - settings area given ([1], e.g. 'init').

        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php');

        try {

            Leyka_Settings_Factory::get_instance()->get_render($screen_full_id[0])
                ->set_controller(Leyka_Settings_Factory::get_instance()->get_controller($screen_full_id[1]))
                ->render_page();

        } catch(Exception $ex) {
            echo '<pre>'.print_r('Settings page error (code '.$ex->getCode().'): '.$ex->getMessage(), 1).'</pre>';
        }

        do_action('leyka_post_admin_actions');

	}

	public function get_default_settings_tab() {
		return apply_filters('leyka_default_settings_tab', 'payment');
	}

	public function get_current_settings_tab() {
		return empty($_GET['stage']) ? $this->get_default_settings_tab() : trim($_GET['stage']);
	}

	public function settings_tabs_menu() {

		$base_url = 'admin.php?page=leyka_settings';

		$out = '';
		foreach(Leyka_Options_Allocator::get_instance()->get_tabs() as $tab_id => $tab_label) {
			$out .= '<a href="'
			    .($this->get_default_settings_tab() === $tab_id ? $base_url : add_query_arg('stage', $tab_id, $base_url))
			    .'" class="'.($this->get_current_settings_tab() === $tab_id ? 'nav-tab nav-tab-active' : 'nav-tab').'">'
			    .$tab_label.'</a>';
		}

		$out .= '<a href="'.admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init').'" class="init-wizard-tab"></a>';

		return $out;

	}

	public function donor_info_screen() {

        do_action('leyka_pre_donor_info_actions'); // Add collapsible to metaboxes

//        // Add all metaboxes:
//        add_meta_box('leyka_donor_info', __("Donor's data", 'leyka'), array($this, 'donor_data_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
//        add_meta_box('leyka_donor_admin_comments', __('Comments', 'leyka'), array($this, 'donor_comments_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
//        add_meta_box('leyka_donor_tags', __('Tags'), array($this, 'donor_tags_metabox'), 'dashboard_page_leyka_donor_info', 'normal');
//        add_meta_box('leyka_donor_donations', __('Donations', 'leyka'), array($this, 'donor_donations_metabox'), 'dashboard_page_leyka_donor_info', 'normal');

	    $this->_show_admin_template('donor-info-page');

        do_action('leyka_post_donor_info_actions');
        do_action('leyka_post_admin_actions');

    }

    public function donor_data_metabox() {
        $this->_show_admin_template('metabox-donor-data');
    }
    public function donor_comments_metabox() {
        $this->_show_admin_template('metabox-donor-comments');
    }
    public function donor_tags_metabox() {
        $this->_show_admin_template('metabox-donor-tags');
    }
    public function donor_donations_metabox() {
        $this->_show_admin_template('metabox-donor-donations');
    }

    /** Displaying feedback **/
    public function feedback_screen() {

        if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
		}

        do_action('leyka_pre_feedback_actions');

        $this->_show_admin_template('feedback-page');

        do_action('leyka_post_feedback_actions');
        do_action('leyka_post_admin_actions');

    }

    /** Feedback page processing */
    public function ajax_send_feedback() {

        if( !wp_verify_nonce($_POST['nonce'], 'leyka_feedback_sending') ) {
            die('1');
        }

        $_POST['topic'] = htmlentities(trim($_POST['topic']), ENT_COMPAT, 'UTF-8');
        $_POST['name'] = htmlentities(trim($_POST['name']), ENT_COMPAT, 'UTF-8');
        $_POST['email'] = htmlentities(trim($_POST['email']), ENT_COMPAT, 'UTF-8');
        $_POST['text'] = htmlentities(trim($_POST['text']), ENT_COMPAT, 'UTF-8');

        if( !$_POST['name'] || !$_POST['email'] || !$_POST['text'] || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
            die('2');
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = true;
		$site_env = format_debug_data(humanaize_debug_data(leyka_get_env_and_options()));
		
        foreach((array)explode(',', LEYKA_SUPPORT_EMAIL) as $email) {

            $email = trim($email);
            if( !$email || !filter_var($email, FILTER_VALIDATE_EMAIL) )
                continue;

            $res &= wp_mail(
                $email, __('Leyka: new feedback incoming', 'leyka'),
                sprintf(
                    "Добрый день!<br><br>
                Поступила новая обратная связь от пользователя Лейки.<br><br>
                <strong>Тема:</strong> %s<br>
                <strong>Имя пользователя:</strong> %s<br>
                <strong>Почта пользователя:</strong> %s<br>
                <strong>Текст сообщения:</strong><br>%s<br><br>
                ---------------- Технические данные сайта пользователя --------------<br><br>
                <strong>Cайт пользователя:</strong> <a href='%s'>%s</a> (IP: %s)<br>
                <strong>Версия WP:</strong> %s<br>
                <strong>Версия Лейки:</strong> %s<br>
                <strong>Параметр admin_email:</strong> %s<br>
                <strong>Язык:</strong> %s (кодировка: %s)<br>
                <strong>ПО веб-сервера:</strong> %s<br>
                <strong>Браузер пользователя:</strong> %s<br>
				---------------------------------------------------------------------<br>
				<pre>%s</pre>
				",
                    $_POST['topic'], $_POST['name'], $_POST['email'], nl2br($_POST['text']),
                    home_url(), get_bloginfo('name'), $_SERVER['SERVER_ADDR'],
                    get_bloginfo('version'), LEYKA_VERSION, get_bloginfo('admin_email'),
                    get_bloginfo('language'), get_bloginfo('charset'),
                    $_SERVER['SERVER_SOFTWARE'], $_SERVER['HTTP_USER_AGENT'],
					$site_env
                ),
                array('From: '.get_bloginfo('name').' <no_reply@leyka.te-st.ru>',)
            );
        }

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        die($res ? '0' : '3');

    }

    public function show_footer() {
        leyka_show_admin_footer();
    }

    protected function _load_data_tables() {

        wp_enqueue_style('jquery-dataTables', LEYKA_PLUGIN_BASE_URL.'css/jquery.dataTables.css');
        wp_enqueue_script('jquery-dataTables', LEYKA_PLUGIN_BASE_URL.'js/jquery.dataTables.min.js', array('jquery'), false, true);

        wp_localize_script('jquery-dataTables', 'leyka_dt', array(
            'processing' => __('Processing...', 'leyka'),
            'search' => __('Search:', 'leyka'),
            'lengthMenu' => __('Show _MENU_ entries', 'leyka'),
            'info' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'leyka'),
            'infoEmpty' => __('Showing 0 to 0 of 0 entries', 'leyka'),
            'infoFiltered' => __('(filtered from _MAX_ total entries)', 'leyka'),
            'infoThousands' => __(',', 'leyka'),
            'loadingRecords' => __('Loading...', 'leyka'),
            'infoPostFix' => '',
            'zeroRecords' => __('No matching records found', 'leyka'),
            'emptyTable' => __('No data available in table', 'leyka'),
            'paginate_first' => __('First', 'leyka'),
            'paginate_previous' => __('Previous', 'leyka'),
            'paginate_next' => __('Next', 'leyka'),
            'paginate_last' => __('Last', 'leyka'),
            'aria_sortAsc' => __(': activate to sort column ascending', 'leyka'),
            'aria_sortDesc' => __(': activate to sort column descending', 'leyka'),
        ));

        return 'jquery-dataTables';

    }

	public function load_frontend_scripts() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', array(), LEYKA_VERSION);
		
		wp_enqueue_style('leyka-admin-common', LEYKA_PLUGIN_BASE_URL.'assets/css/admin-common.css', array(), LEYKA_VERSION);

		$screen = get_current_screen();
		if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
			return;
        }

        // Base admin area js/css:
        $leyka_admin_new = (isset($_GET['screen']) && count(explode('-', $_GET['screen'])) >= 2) // New settings pages (from v3.0)
            || (isset($_GET['page']) && $_GET['page'] === 'leyka_settings' /*&& empty($_GET['stage'])*/)
            || ($screen->post_type === Leyka_Campaign_Management::$post_type && $screen->base === 'post')
            || (isset($_GET['page']) && ($_GET['page'] === 'leyka' || $_GET['page'] === 'leyka_donors'))
            || (isset($_GET['page']) && $_GET['page'] === 'leyka_donor_info' && !empty($_GET['donor']));

        $current_screen = get_current_screen();
        $dependencies = array('jquery',);

        if($leyka_admin_new) {
            wp_enqueue_style('leyka-settings', LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css', array(), LEYKA_VERSION);
        } else { // Old admin pages (before v3.0)
	        wp_enqueue_style('leyka-admin', LEYKA_PLUGIN_BASE_URL.'css/admin.css', array(), LEYKA_VERSION);
	    }

        // WP admin metaboxes support:
        if($current_screen->id === 'dashboard_page_leyka_donor_info') {

            $dependencies[] = 'postbox';
            $dependencies[] = 'jquery-ui-accordion';
            $dependencies[] = 'jquery-ui-sortable';
            $dependencies[] = 'tags-box';

            $dependencies[] = $this->_load_data_tables();

        }
        // Metaboxes support - END

        // Settings pages:
        if(stristr($current_screen->id, '_page_leyka_settings') !== false) {

//            $dependencies[] = 'jquery-ui-accordion';
            $dependencies[] = 'jquery-ui-sortable';

            wp_enqueue_script('leyka-sticky', LEYKA_PLUGIN_BASE_URL.'js/jquery.sticky.js', $dependencies, LEYKA_VERSION, true);
            $dependencies[] = 'leyka-sticky';
        }

        if(isset($_GET['page']) && ($_GET['page'] === 'leyka' || $_GET['page'] === 'leyka_donors')) {
            wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );
            $dependencies[] = 'jquery-ui-selectmenu';
        }

        if($current_screen->post_type === Leyka_Donation_Management::$post_type) {

            $dependencies[] = 'jquery-ui-autocomplete';
            $dependencies[] = 'jquery-ui-tooltip';

        }

        $js_data = apply_filters('leyka_admin_js_localized_strings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajax_loader_url' => LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif',
            'field_required' => __('This field is required to be filled', 'leyka'),
            'email_invalid_msg' => __('You have entered an invalid email', 'leyka'),
            'common_error_message' => esc_html__('Error while saving the data', 'leyka'),
			'error_message' => esc_html__('Error!', 'leyka'),
            'default_image_message' => esc_html__('Default', 'leyka'),
			'disconnect_stats' => esc_html__('Disconnect statistics', 'leyka'),
            'confirm_delete_comment' => esc_html__('Delete comment?', 'leyka'),
            'first_donation_date_incomplete_message' => esc_html__('To correctly search for "First Payment Date", select the range of their two dates.', 'leyka'),
            'last_donation_date_incomplete_message' => esc_html__('To correctly search for "Last Payment Date", select the range of their two dates.', 'leyka'),
        ));

        if(isset($_GET['page']) && $_GET['page'] === 'leyka') {

            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'assets/js/Chart.v2.8.0.min.js', $dependencies, LEYKA_VERSION, true);

        }

		leyka_localize_rich_html_text_tags();

        // Campaign editing page:
		if($screen->post_type === Leyka_Campaign_Management::$post_type && $screen->base === 'post' && (!$screen->action || $screen->action === 'add')) {

            $dependencies[] = $this->_load_data_tables();

            if(function_exists('wp_enqueue_code_editor')) { // The function is available in WP v4.9.0+
                wp_enqueue_code_editor(array('type' => 'text/css')); // Add the code editor lib
            }

        }

        $locale = get_locale();
        if($locale !== 'en_US') {
            wp_enqueue_script(
                'jquery-ui-datepicker-locale',
                LEYKA_PLUGIN_BASE_URL."js/jq-datepicker-locales/$locale.js",
                array('jquery-ui-datepicker'), LEYKA_VERSION, true
            );
        }

        // Donation editing page:
        if($screen->post_type === Leyka_Donation_Management::$post_type && $screen->base === 'post') {

            wp_enqueue_script(
                'leyka-admin-add-edit-donation',
                LEYKA_PLUGIN_BASE_URL.'js/admin-add-edit-donation.js',
                array('jquery-ui-datepicker-locale', 'jquery-ui-autocomplete'), LEYKA_VERSION, true
            );
            wp_localize_script('leyka-admin-add-edit-donation', 'leyka', $js_data + array(
                'add_donation_button_text' => __('Add the donation', 'leyka'),
                'field_required' => __('This field is required to be filled', 'leyka'),
                'campaign_required' => __('Selecting a campaign is required', 'leyka'),
                'email_invalid_msg' => __('You have entered an invalid email', 'leyka'),
                'amount_incorrect_msg' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
                'donation_source_required' => __('Please, set one of a payment methods or just type a few words to describe a source for this donation', 'leyka'),
            ));

            return; /** @todo Only for now. Need to transfer the code from /js/admin-add-edit-donation.js to the separate /src/js/admin/ script. */

        }


        $dependencies[] = 'jquery-ui-autocomplete';

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);
        wp_enqueue_script(
            'leyka-settings',
            LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
            $dependencies,
            LEYKA_VERSION,
            true
        );
        wp_localize_script('leyka-settings', 'leyka', $js_data);

	}

}