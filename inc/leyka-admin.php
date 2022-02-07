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
	/** @var WP_List_Table */
	protected $_recurring_subscriptions_list_table = null;

	protected function __construct() {

        require_once ABSPATH.'wp-admin/includes/meta-boxes.php';
	    require_once LEYKA_PLUGIN_DIR.'inc/leyka-admin-functions.php';
        require_once LEYKA_PLUGIN_DIR.'inc/settings/leyka-admin-template-tags.php';
        require_once LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php';

        add_filter('admin_body_class', [$this, 'leyka_admin_body_class_setup'], 1000); // To apply CSS in needed places

		add_action('admin_menu', [$this, 'admin_menu_setup'], 9);

		if(leyka_options()->opt('donor_management_available')) { // Save Donors pages custom options
            add_filter('set-screen-option', function($status, $option, $value) {

                if($option === 'donors_per_page' && absint($value)) {
                    update_user_option(get_current_user_id(), 'donors_per_page', absint($value));
                }

                return $value;

            }, 10, 3);
        }
		if(leyka_get_donations_storage_type() === 'sep') { // Items per page for Sep-stored Donations
            add_filter('set-screen-option', function($status, $option, $value) {

                if($option === 'donations_per_page' && absint($value)) {
                    update_user_option(get_current_user_id(), 'donations_per_page', absint($value));
                }

                return $value;

            }, 10, 3);
        }

		add_action('admin_enqueue_scripts', [$this, 'load_frontend_scripts']);

        add_action('admin_init', [$this, 'pre_admin_actions']);

        add_action('wp_ajax_leyka_send_feedback', [$this, 'ajax_send_feedback']);

        add_filter('plugin_row_meta', [$this, 'set_plugin_meta'], 10, 2);

		add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, [$this, 'add_plugins_list_links']);

        // Metaboxes support where it is needed:
        add_action('leyka_pre_help_actions', [$this, 'full_metaboxes_support']);
        add_action('leyka_pre_donor_info_actions', [$this, 'full_metaboxes_support']);
        add_action('leyka_pre_donation_info_actions', [$this, 'full_metaboxes_support']);
        add_action('leyka_pre_extension_settings_actions', [$this, 'full_metaboxes_support']);

        add_action('leyka_pre_donation_info_actions', [$this, 'handle_donation_info_submit']);

		add_action('leyka_post_admin_actions', [$this, 'show_footer']);

		// Donors' tags on the user profile page:
        if(leyka_options()->opt('donor_management_available') || leyka_options()->opt('donor_accounts_available')) {

            add_action('show_user_profile', [$this, 'show_user_profile_donor_fields']);
            add_action('edit_user_profile', [$this, 'show_user_profile_donor_fields']);

        }

        add_action('personal_options_update', [$this, 'save_user_profile_donor_fields']);
        add_action('edit_user_profile_update', [$this, 'save_user_profile_donor_fields']);

        // If template is disabled, remove its options:
        add_filter('leyka_view_options_allocation', function($options_allocated){

            if( !empty($options_allocated[0]['section']['tabs']) ) {
                foreach($options_allocated[0]['section']['tabs'] as $tab_id => $tab_options) {
                    if(stristr($tab_id, 'template_options_') !== false) {

                        $template_id = str_replace('template_options_', '', $tab_id);
                        if(leyka()->template_is_disabled($template_id)) {
                            unset($options_allocated[0]['section']['tabs'][$tab_id]);
                        }

                    }
                }
            }

            return $options_allocated;

        });

		require_once LEYKA_PLUGIN_DIR.'/inc/leyka-class-portlet-controller.php'; // Portlet controller API

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

    /** Support the full Metaboxes features: */
    public function full_metaboxes_support($current_stage = false) {?>

        <form style="display:none;" method="get" action="#">
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);?>
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);?>
        </form>

    <?php }

    public function pre_admin_actions() {

        function leyka_admin_title($admin_title) {

            if(isset($_GET['page']) && $_GET['page'] === 'leyka_settings_new' && isset($_GET['screen'])) {

                $screen_full_id = explode('-', $_GET['screen']);

                // $screen_full_id[0] - view type (e.g. 'wizard' or 'control_panel')
                // $screen_full_id[1] - settings area given (e.g. 'init').

                require_once LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php';

                $admin_title = get_bloginfo('name')
                    .' &#8212; '
                    .Leyka_Settings_Factory::get_instance()->get_controller($screen_full_id[1])->title;

            } else if(isset($_GET['page']) && $_GET['page'] === 'leyka_donor_info' && !empty($_GET['donor'])) {

                try {
                    $donor = new Leyka_Donor(absint($_GET['donor']));
                } catch(Exception $e) {
                    return $admin_title;
                }

                $admin_title = sprintf(__('Leyka: Donor %s', 'leyka'), $donor->name).' &lsaquo; '.get_bloginfo('name');

            } else if(isset($_GET['page']) && $_GET['page'] === 'leyka_extension_settings' && isset($_GET['extension'])) {

                $extension = leyka_get_extension_by_id($_GET['extension']);

                $admin_title = ($extension ?
                        sprintf(__('Leyka: %s', 'leyka'), $extension->title) :
                        __('Leyka: unknown extension', 'leyka')
                    )
                    .' &lsaquo; '.get_bloginfo('name');

            } else if(isset($_GET['page']) && $_GET['page'] == 'leyka_donation_info' && isset($_GET['donation'])) {

                if( !absint($_GET['donation']) ) { // New Donation tmp page
                    $admin_title = __('New correctional donation', 'leyka').' &lsaquo; '.get_bloginfo('name');
                } else { // Edit Donation page:

                    $donation = Leyka_Donations::get_instance()->get(absint($_GET['donation']));

                    if( !$donation ) {

                        wp_redirect(admin_url('admin.php?page=leyka_donations'));
                        exit;

                    }

                    $admin_title = sprintf(__('%s: donation #%s profile', 'leyka'), __('Leyka', 'leyka'), $donation->id)
                        .' &lsaquo; '.get_bloginfo('name');

                }

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

        add_filter('leyka_admin_portlet_title', function($portlet_title, $portlet_id){
            return $portlet_id === 'donations-dynamics' ? $portlet_title.',&nbsp;'.leyka_get_currency_label() : $portlet_title;
        }, 10, 2);

        // Add donor account column to the admin Users list if needed:
        if(get_option('leyka_donor_management_available')) {

            add_filter('manage_users_columns', function($column){

                $column['donor_account_available'] = __("Donor's info", 'leyka');
                return $column;

            });

            add_filter('manage_users_custom_column', function($value, $column_name, $user_id) {

                if($column_name === 'donor_account_available') {

                    if(leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, false, $user_id)) {

                        $donor_user = new Leyka_Donor($user_id);
                        $donor_info_page_link = '<a href="'.admin_url('?page=leyka_donor_info&donor='.$user_id).'">'.__('Info', 'leyka').'</a>';

                        return ($donor_user->has_account_access ? __('yes', 'leyka') : __('no', 'leyka'))
                            .' | '.$donor_info_page_link;

                    } else {
                        return __('not a donor', 'leyka');
                    }

                } else {
                    return $value;
                }

            }, 10, 3);

        }

    }

    function leyka_admin_body_class_setup($classes) {

        $leyka_page_class = '';

        if( !empty($_GET['screen']) && strpos($_GET['screen'], 'wizard-') === 0 ) {
            $leyka_page_class .= 'leyka-admin-wizard';
        } else if( !empty($_GET['page']) && $_GET['page'] == 'leyka_settings' && empty($_GET['screen']) ) {
            $leyka_page_class .= 'leyka-admin-settings';
        } else if( !empty($_GET['page']) && $_GET['page'] == 'leyka' && empty($_GET['screen']) ) {
            $leyka_page_class .= 'leyka-admin-dashboard';
        } else if( !empty($_GET['page']) && $_GET['page'] == 'leyka_donations' ) { // [Sep-D] Donations list page
            $leyka_page_class .= 'leyka-admin-donations-list';
        } else if( !empty($_GET['page']) && $_GET['page'] == 'leyka_donation_info' ) { // [Sep-D] Donation Add/Edit page

            $leyka_page_class .= 'leyka-admin-donation-info '
                .(empty($_GET['donation']) ? 'leyka-donation-add' : 'leyka-donation-edit');

        } else if( !empty($_GET['page']) && $_GET['page'] == 'leyka_donors' && empty($_GET['screen']) ) {
            $leyka_page_class .= 'leyka-admin-donors-list';
        } else if(
            ( !empty($_GET['post_type']) && $_GET['post_type'] == 'leyka_campaign' )
            || ( !empty($_GET['page']) && $_GET['page'] === 'leyka_feedback' && empty($_GET['screen']))
            || ( !empty($_GET['page']) && $_GET['page'] === 'leyka_donors' && empty($_GET['screen']) )
        ) {
            $leyka_page_class .= 'leyka-admin-default';
        }

        return $classes.' '.$leyka_page_class.' ';

    }

    public function admin_menu_setup() {

        // Leyka menu root:
        add_menu_page(
            __('Leyka Dashboard', 'leyka'),
            __('Leyka', 'leyka'),
            'leyka_manage_donations',
            'leyka',
            [$this, 'dashboard_screen']
        );

        add_submenu_page(
            'leyka',
            __('Leyka Dashboard', 'leyka'),
            __('Dashboard', 'leyka'),
            'leyka_manage_donations',
            'leyka',
            [$this, 'dashboard_screen']
        );

        add_submenu_page(
            'leyka',
            __('Campaigns', 'leyka'),
            __('Campaigns', 'leyka').'<a class="leyka-add-new dashicons dashicons-plus-alt" href="'.admin_url('/post-new.php?post_type='.Leyka_Campaign_Management::$post_type).'"></a>',
            'leyka_manage_donations',
            'edit.php?post_type='.Leyka_Campaign_Management::$post_type
        );

        // Donations admin list page:
        $hook = add_submenu_page(
            'leyka',
            __('Donations', 'leyka'),
            __('Donations', 'leyka').'<a class="leyka-add-new dashicons dashicons-plus-alt" href="'.admin_url('/admin.php?page=leyka_donation_info').'"></a>',
            'leyka_manage_donations',
            'leyka_donations',
            [$this, 'donations_list_screen']
        );
        add_action("load-$hook", [$this, 'donations_list_screen_options']);

        // Recurring subscriptions list page:
        $hook = add_submenu_page(
            'leyka',
            __('Recurring subscriptions', 'leyka'),
            __('Recurring subscriptions', 'leyka'),
            'leyka_manage_donations',
            'leyka_recurring_subscriptions',
            [$this, 'recurring_subscriptions_list_screen']
        );
        add_action("load-$hook", [$this, 'recurring_subscriptions_list_screen_options']);

        if(leyka_options()->opt('donor_management_available')) {

            // Donors list page:
            $hook = add_submenu_page(
                'leyka',
                __('Donors', 'leyka'),
                __('Donors', 'leyka'),
                'leyka_manage_donations',
                'leyka_donors',
                [$this, 'donors_list_screen']
            );
            add_action("load-$hook", [$this, 'donors_list_screen_options']);

            // Donors tags page:
            $taxonomy = get_taxonomy(Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME);

            add_submenu_page(
                '',
                esc_attr($taxonomy->labels->menu_name),
                esc_attr($taxonomy->labels->menu_name),
                $taxonomy->cap->manage_terms,
                'edit-tags.php?taxonomy='.$taxonomy->name
            );

        }

        add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), 'leyka_manage_options', 'leyka_settings', [$this, 'settings_screen']);

        add_submenu_page('leyka', __('Help', 'leyka'), __('Help', 'leyka'), 'leyka_manage_donations', 'leyka_help', [$this, 'help_screen']);

        // Fake pages:
        add_submenu_page(NULL, __('New correctional donation', 'leyka'), _x('Add new', '[donation]', 'leyka'), 'leyka_manage_donations', 'leyka_donation_info', [$this, 'donation_info_screen']);

        add_submenu_page(NULL, 'Leyka Wizard', 'Leyka Wizard', 'leyka_manage_options', 'leyka_settings_new', [$this, 'settings_new_screen']);

        add_submenu_page(NULL, "Donor's info", "Donor's info", 'leyka_manage_options', 'leyka_donor_info', [$this, 'donor_info_screen']);

        add_submenu_page(NULL, 'Extension settings', 'Extension settings', 'leyka_manage_options', 'leyka_extension_settings', [$this, 'leyka_extension_settings_screen']);

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

		function leyka_dashboard_portlets_row_content($dashboard_row_id) {

		    switch($dashboard_row_id) {
                case 'donations-stats':
                    Leyka_Admin_Setup::get_instance()->show_admin_portlet(
                        'stats-donations-main', ['interval' => $_GET['interval']]
                    );
                    Leyka_Admin_Setup::get_instance()->show_admin_portlet(
                        'stats-recurring', ['interval' => $_GET['interval']]
                    );
                    break;
                case 'donations-dynamics':
                    Leyka_Admin_Setup::get_instance()->show_admin_portlet(
                        'donations-dynamics', ['interval' => $_GET['interval']]
                    );
                    break;
                case 'recent-donations':
                    Leyka_Admin_Setup::get_instance()->show_admin_portlet(
                        'recent-donations', ['interval' => $_GET['interval'], 'number' => 5,]
                    );
                    break;
                default:
            }

        }
        add_action('leyka_admin_dashboard_portlets_row', 'leyka_dashboard_portlets_row_content', 10, 2);

		$this->_show_admin_template('dashboard-page');

		do_action('leyka_post_dashboard_actions');
        do_action('leyka_post_admin_actions');

	}

	public function show_admin_portlet($portlet_id, array $params = []) {

	    $portlet_file = LEYKA_PLUGIN_DIR.'/inc/portlets/leyka-'.$portlet_id.'.php';
	    if( !file_exists($portlet_file) ) {
	        return;
        }

	    $controller_file = LEYKA_PLUGIN_DIR.'/inc/portlets/'.$portlet_id.'/leyka-class-'.$portlet_id.'-portlet-controller.php';
	    if(file_exists($controller_file)) {
            require_once $controller_file;
        }

	    $portlet_data = get_file_data($portlet_file, [
            'name' => 'Leyka Portlet',
            'description' => 'Description',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'thumbnail' => 'Thumbnail',
        ]);

	    $portlet_data['title'] = empty($params['title']) ? esc_attr__($portlet_data['title'], 'leyka') : $params['title'];
	    $portlet_data['subtitle'] = empty($params['subtitle']) ?
            esc_attr__($portlet_data['subtitle'], 'leyka') : $params['subtitle'];
	    $portlet_data['thumbnail'] = empty($params['thumbnail']) ? $portlet_data['thumbnail'] : $params['thumbnail'];?>

	    <div class="leyka-admin-portlet portlet-<?php echo esc_attr($portlet_id);?>">

            <div class="portlet-header">

                <img src="<?php echo esc_url(LEYKA_PLUGIN_BASE_URL.trim($portlet_data['thumbnail'], '/'));?>" alt="">

                <div class="portlet-title">

                <?php if( !empty($portlet_data['subtitle'])) {?>
                    <h3><?php echo apply_filters('leyka_admin_portlet_subtitle', $portlet_data['subtitle'], $portlet_id);?></h3>
                <?php }?>

                    <h2><?php echo apply_filters('leyka_admin_portlet_title', $portlet_data['title'], $portlet_id);?></h2>

                </div>
            </div>

            <div class="portlet-content"><?php require $portlet_file;?></div>

        </div>

    <?php
	}

	public function has_banners($page = false, $location = false) {
	    return !get_user_meta(get_current_user_id(), 'leyka_dashboard_banner_closed-grade_plugin', true);
    }

    public function show_banner($page = false, $location = false) {?>
    
        <div class="banner-wrapper">
        	<div class="banner-inner" data-banner-id="grade_plugin">

                <a href="https://wordpress.org/support/plugin/leyka/reviews/#new-post" class="banner" target="_blank">
                    <div class="banner-text">
                        <div class="banner-subtitle"><?php _e('Like Leyka?', 'leyka');?></div>
                        <div class="banner-title"><?php _e('Give a mark to the plugin', 'leyka');?></div>
                    </div>
                    <div class="banner-thumbnail">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'/img/dashboard/banner-grade-plugin.svg';?>" alt="">
                    </div>
            	</a>

                <a class="close" href="#" title="<?php _e('Close permanently', 'leyka');?>"></a>

            </div>
        </div>

    <?php
    }

    /** @todo Check if it's needed */
	public function is_separate_forms_stage($stage) {
		return in_array($stage, ['email', 'beneficiary', 'technical', 'view', 'additional', 'extensions',]);
	}

	/** (Separate stored) Donations related methods: */
    public function donations_list_screen_options() {

        add_screen_option('per_page', [
            'label' => __('Donations per page', 'leyka'),
            'default' => 20,
            'option' => 'donations_per_page',
        ]);

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

    }

    public function donation_info_screen() {

        do_action('leyka_pre_donation_info_actions'); // Add collapsible to metaboxes

        // Add all metaboxes:
        if(empty($_GET['donation']) || !absint($_GET['donation'])) { // New Donation page

            add_meta_box(
                'leyka_donation_data',
                __('Donation data', 'leyka'),
                ['Leyka_Donation_Management', 'new_donation_data_metabox'],
                'dashboard_page_leyka_donation_info',
                'normal',
                'high'
            );
            add_meta_box(
                'leyka_donation_status',
                __('Donation status', 'leyka'),
                ['Leyka_Donation_Management', 'donation_status_metabox'],
                'dashboard_page_leyka_donation_info',
                'side',
                'high'
            );

        } else { // Edit Donation page

            add_meta_box(
                'leyka_donation_data',
                __('Donation data', 'leyka'),
                ['Leyka_Donation_Management', 'donation_data_metabox'],
                'dashboard_page_leyka_donation_info',
                'normal',
                'high'
            );
            add_meta_box(
                'leyka_donation_status',
                __('Donation status', 'leyka'),
                ['Leyka_Donation_Management', 'donation_status_metabox'],
                'dashboard_page_leyka_donation_info',
                'side',
                'high'
            );
            add_meta_box(
                'leyka_donation_emails_status',
                __('Emails status', 'leyka'),
                ['Leyka_Donation_Management', 'emails_status_metabox'],
                'dashboard_page_leyka_donation_info',
                'normal',
                'high'
            );
            add_meta_box(
                'leyka_donation_gateway_response',
                __('Gateway responses text', 'leyka'),
                ['Leyka_Donation_Management', 'gateway_response_metabox'],
                'dashboard_page_leyka_donation_info',
                'normal',
                'low'
            );

            $donation = Leyka_Donations::get_instance()->get(absint($_GET['donation']));
            if($donation->is_init_recurring_donation) {

                add_meta_box(
                    'leyka_donation_recurring_subscription_rebills',
                    __('Recurring donations of this subscription', 'leyka'),
                    ['Leyka_Donation_Management', 'subscription_resurring_donations_metabox'],
                    'dashboard_page_leyka_donation_info',
                    'normal',
                    'low'
                );

            }

        }

        $this->_show_admin_template('donation-info-page');

        do_action('leyka_post_donation_info_actions');
        do_action('leyka_post_admin_actions');

    }

    /**
     * A TMP method to handle Donation settings update.
     *
     * @todo WARNING! After Donation_Settings_Controller/Render creation, this handling should be there.
     */
    public function handle_donation_info_submit() {

	    if(empty($_GET['page']) || $_GET['page'] !== 'leyka_donation_info' || empty($_POST['_wpnonce'])) {
	        return;
        } else if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'edit-donation')) {
	        return; // Add some error msg, mb...
        } else if( !current_user_can('leyka_manage_donations') ) {
            return; // Add some error msg, mb...
        }

	    if( !empty($_GET['donation']) && absint($_GET['donation']) ) {

            $donation = Leyka_Donations::get_instance()->get(absint($_GET['donation']));
            $this->_handle_donation_edit($donation);

        } else {
            $this->_handle_donation_add();
        }

    }

    protected function _handle_donation_edit(Leyka_Donation_Base $donation) {

        $campaign = new Leyka_Campaign($donation->campaign_id);

        if( !$donation->payment_title && $campaign->payment_title ) {
            $donation->payment_title = $campaign->payment_title;
        }

        if(isset($_POST['donation-amount'])) {

            $_POST['donation-amount'] = round((float)str_replace(',', '.', $_POST['donation-amount']), 2);
            if((float)$donation->amount != $_POST['donation-amount']) {
                $donation->amount = $_POST['donation-amount'];
            }

        }

        if(isset($_POST['donation-amount-total'])) {

            $_POST['donation-amount-total'] = round((float)str_replace(
                [',', ' '], ['.', ''],
                $_POST['donation-amount-total']
            ), 2);

            if((float)$donation->amount_total != $_POST['donation-amount-total']) {

                if($_POST['donation-amount-total'] <= 0.0 && $donation->amount > 0.0) {
                    $_POST['donation-amount-total'] = $donation->amount;
                }

                $old_amount = $donation->amount_total ? $donation->amount_total : $donation->amount;
                $donation->amount_total = $_POST['donation-amount-total'];

                // If we're adding a correctional donation, then $donation->campaign_id == 0:
                if($donation->campaign_id && $donation->status == 'funded') {
                    $campaign->update_total_funded_amount($donation, 'update_sum', $old_amount);
                }

            }

        }

        if( !$donation->currency ) {
            $donation->currency = leyka_options()->opt('currency_main');
        }

        if(isset($_POST['campaign-id']) && $donation->campaign_id != absint($_POST['campaign-id'])) {

            // If we're adding a correctional donation, $donation->campaign_id == 0:
            if($donation->campaign_id && $donation->status == 'funded') {
                $campaign->update_total_funded_amount($donation, 'remove'); // Old campaign
            }

            $donation->campaign_id = absint($_POST['campaign-id']);
            $campaign = new Leyka_Campaign($donation->campaign_id); // New campaign

            if($donation->status === 'funded') {
                $campaign->update_total_funded_amount($donation);
            }

        }

        $donation_title = $campaign->payment_title ?
            $campaign->payment_title :
            ($campaign->title ? $campaign->title : sprintf(__('Donation #%s', 'leyka'), $donation->id));
        if($donation->title !== $donation_title) {
            $donation->title = $donation_title;
        }

        if(isset($_POST['donation-pm']) && ($donation->pm != $_POST['donation-pm'] || $_POST['donation-pm'] === 'custom')) {

            if($_POST['donation-pm'] === 'custom') {

                $donation->gateway_id = '';
                $_POST['custom-payment-info'] = mb_substr($_POST['custom-payment-info'], 0, 255);

                if($donation->pm_id !== $_POST['custom-payment-info']) {
                    $donation->pm_id = $_POST['custom-payment-info'];
                }

            } else {

                $parts = explode('-', $_POST['donation-pm']);
                $donation->gateway_id = $parts[0];
                $donation->pm = $parts[1];

            }

        }

        if(isset($_POST['donation_date']) && $donation->date_timestamp != strtotime($_POST['donation_date'])) {
            $donation->date_timestamp = strtotime($_POST['donation_date']);
        }

        if(isset($_POST['payment-type']) && $donation->payment_type != $_POST['payment-type']) {
            $donation->payment_type = $_POST['payment-type'];
        }

        if(
            isset($_POST['donor-name'])
            && $donation->donor_name !== $_POST['donor-name']
            && leyka_validate_donor_name($_POST['donor-name'])
        ) {
            $donation->donor_name = sanitize_text_field($_POST['donor-name']);
        }

        if(isset($_POST['donor-email']) && $donation->donor_email !== $_POST['donor-email'] && is_email($_POST['donor-email'])) {
            $donation->donor_email = sanitize_email($_POST['donor-email']);
        }

        if(isset($_POST['donor-comment']) && $donation->donor_comment !== $_POST['donor-comment']) {
            $donation->donor_comment = sanitize_textarea_field($_POST['donor-comment']);
        }

        // Add donor ID for correction-typed donation:
        if(
            leyka_options()->opt('donor_management_available')
            && $donation->status === 'funded'
            && !$donation->donor_user_id
            && $donation->donor_email
        ) {

            try {

                $donor = new Leyka_Donor($donation->donor_email);

                $donation->donor_user_id = $donor->id;
                Leyka_Donor::calculate_donor_metadata($donor);

            } catch(Exception $e) {
                // ...
            }

        }

        // Donation status change should be last - else donation_status_change action won't work correctly:
        if($donation->status !== $_POST['donation_status']) {
            $donation->status = $_POST['donation_status'];
        }

        do_action("leyka_{$donation->gateway_id}_edit_donation_data", $donation);
        do_action("leyka_{$donation->gateway_id}_save_donation_data", $donation);

    }

    protected function _handle_donation_add() {

        $gateway_pm = empty($_POST['donation-pm']) || $_POST['donation-pm'] === 'custom' ?
            'custom' : leyka_get_pm_by_id($_POST['donation-pm'], true);
        $gateway_id = $gateway_pm === 'custom' ? '' : $gateway_pm->gateway_id;
        $pm_id = $gateway_pm === 'custom' ? mb_substr(esc_html($_POST['custom-payment-info']), 0, 255) : $gateway_pm->id;
        $campaign = new Leyka_Campaign(absint($_POST['campaign-id']));

        $new_donation_params = [
            'payment_type' => empty($_POST['payment-type']) ? 'correction' : $_POST['payment-type'],
            'campaign_id' => $campaign->id,
            'payment_title' => $campaign->payment_title,
            'status' => $_POST['donation_status'],
            'amount' => round($_POST['donation-amount'], 2),
            'currency_id' => empty($_POST['donation-currency']) || !leyka_get_currencies_full_info($_POST['donation-currency']) ?
                leyka_get_country_currency() : $_POST['donation-currency'],
            'gateway_id' => $gateway_id,
            'pm_id' => $pm_id,
            'donor_name' => $_POST['donor-name'],
            'donor_email' => $_POST['donor-email'],
            'donor_comment' => empty($_POST['donor-comment']) ? '' : $_POST['donor-comment'],
            'date_created' => $_POST['donation_date'].' '.date('H:i:s'),
        ];
        if( !empty($_POST['donation-amount-total']) && $_POST['donation-amount-total'] !== $_POST['donation-amount'] ) {
            $new_donation_params['amount_total'] = round($_POST['donation-amount-total'], 2);
        }

        $donation_id = Leyka_Donations::get_instance()->add($new_donation_params + $_POST);
        if(is_wp_error($donation_id)) {

            $_SESSION['leyka_new_donation_error'] = $donation_id; /** @todo Change it when using Donation Add/Edit Controller */
            do_action('leyka_add_donation_failed', $donation_id);

        } else {

            $donation = Leyka_Donations::get_instance()->get($donation_id);

            do_action("leyka_{$donation->gateway_id}_add_donation_data", $donation);
            do_action("leyka_{$donation->gateway_id}_save_donation_data", $donation);

            // WARNING: we can't use wp_redirect here due to admin headers already sent:
            $new_donation_edit_url = admin_url('admin.php?page=leyka_donation_info&donation='.$donation_id.'&msg=ok');?>

            <div id="message" class="updated notice notice-success">
                <p><?php echo sprintf(__("Donation added. If you are not redirected to it's edit page, <a href='%s'>click here</a>.", 'leyka'), $new_donation_edit_url);?></p>
            </div>

            <script type="text/javascript">window.location.href="<?php echo $new_donation_edit_url;?>";</script>

        <?php }

    }
    /** Donations related methods - END */

    /** Donors related methods: */
    public function donors_list_screen_options() {

        add_screen_option('per_page', [
            'label' => __('Donors per page', 'leyka'),
            'default' => 20,
            'option' => 'donors_per_page',
        ]);

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

    public function donor_info_screen() {

        do_action('leyka_pre_donor_info_actions'); // Add collapsible to metaboxes

        // Add Donor metaboxes:
        add_meta_box(
            'leyka_donor_info',
            __("Donor's data", 'leyka'),
            [$this, 'donor_data_metabox'],
            'dashboard_page_leyka_donor_info',
            'normal'
        );
        add_meta_box(
            'leyka_donor_admin_comments',
            __('Comments', 'leyka'),
            [$this, 'donor_comments_metabox'],
            'dashboard_page_leyka_donor_info',
            'normal'
        );
        add_meta_box(
            'leyka_donor_tags',
            __('Tags'),
            [$this, 'donor_tags_metabox'],
            'dashboard_page_leyka_donor_info',
            'normal'
        );
        add_meta_box(
            'leyka_donor_donations',
            __('Donations', 'leyka'),
            [$this, 'donor_donations_metabox'],
            'dashboard_page_leyka_donor_info',
            'normal'
        );

        $this->_show_admin_template('donor-info-page');

        do_action('leyka_post_donor_info_actions');
        do_action('leyka_post_admin_actions');

    }

    /**
     * Display Donor related fields on the User profile admin page.
     *
     * @param $donor_user WP_User
     */
    public function show_user_profile_donor_fields(WP_User $donor_user) {

        if( !current_user_can('administrator') ) {
            return;
        }

        if( !leyka_options()->opt('donor_management_available') && !leyka_options()->opt('donor_accounts_available') ) {
            return;
        }?>

        <table class="form-table">

            <tr>
                <th>
                    <label for="leyka-donors-tags-field"><?php _e('Donor tags', 'leyka');?></label>
                </th>
                <td>
                    <?php $all_donors_tags = get_terms([
                        'taxonomy' => Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                        'hide_empty' => false,
                    ]);

                    $donor_user_tags = wp_get_object_terms(
                        $donor_user->ID,
                        Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                        ['fields' => 'ids']
                    );

                    if($all_donors_tags) {?>

                    <select id="leyka-donors-tags-field" multiple="multiple" name="leyka_donor_tags[]">
                        <?php foreach($all_donors_tags as $donor_tag) {?>
                            <option value="<?php echo esc_attr($donor_tag->term_id);?>" <?php echo in_array($donor_tag->term_id, $donor_user_tags) ? 'selected="selected"' : '';?>>
                                <?php echo esc_html($donor_tag->name);?>
                            </option>
                        <?php }?>

                    </select>

                    <?php } else {
                        _e('No Donor tags added yet.', 'leyka');
                    }?>
                </td>
            </tr>

            <?php if(leyka_options()->opt('donor_accounts_available')) {?>

            <tr>
                <th>
                    <label for="leyka-donor-account-access"><?php _e('Donor account available', 'leyka');?></label>
                </th>
                <td>
                    <input type="checkbox" id="leyka-donor-account-access" name="leyka_donor_account_available" value="1" <?php echo $donor_user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP) ? 'checked="checked"' : '';?>>
                </td>
            </tr>

            <?php }?>

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

        if( !current_user_can('administrator') || !is_array($_POST['leyka_donor_tags']) ) {
            return false;
        }

        $donor_user = new WP_User($donor_user_id);

        if(leyka_options()->opt('donor_accounts_available')) {

            if($_POST['leyka_donor_account_available']) {
                $donor_user->add_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP);
            } else {
                $donor_user->remove_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP);
            }

        }

        $_POST['leyka_donor_tags'] = empty($_POST['leyka_donor_tags']) ? [] : $_POST['leyka_donor_tags'];

        array_walk($_POST['leyka_donor_tags'], function( &$value ){
            $value = (int)$value;
        });

        return !is_wp_error(wp_set_object_terms(
            $donor_user_id,
            $_POST['leyka_donor_tags'],
            Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME
        ));

    }

    public function recurring_subscriptions_list_screen_options() {

        add_screen_option('per_page', [
            'label' => __('Subscriptions per page', 'leyka'),
            'default' => 20,
            'option' => 'recurring_subscriptions_per_page',
        ]);

        require_once LEYKA_PLUGIN_DIR.'inc/admin-lists/leyka-class-recurring-subscriptions-list-table.php';

        $this->_recurring_subscriptions_list_table = new Leyka_Admin_Recurring_Subscriptions_List_Table();

    }

    public function recurring_subscriptions_list_screen() {

        if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        do_action('leyka_pre_recurring_subscriptions_list_actions');

        $this->_show_admin_template('recurring-subscriptions-list-page');

        do_action('leyka_post_recurring_subscriptions_list_actions');
        do_action('leyka_post_admin_actions');

    }
    /** Donors related methods - END */

	public function settings_screen() {

		if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        $current_stage = $this->get_current_settings_tab();

		require_once LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php'; // Basic Controllers Factory class
        require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php';

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

	    $this->_show_admin_template('settings-page');

	    do_action('leyka_post_settings_actions');
        do_action('leyka_post_admin_actions');

	}

	/** Settings factory-controlled display. */
	public function settings_new_screen() {

	    if(empty($_GET['screen']) || count(explode('-', $_GET['screen'])) < 2) {

	        $this->settings_screen();
	        return;

	    }

	    $screen_full_id = explode('-', $_GET['screen']);

	    // Normally, we'd constuct settings view based on
	    // - view type ([0], e.g. 'wizard' or 'options')
	    // - settings area given ([1], e.g. 'init').

        try {

            Leyka_Settings_Factory::get_instance()
                ->get_render($screen_full_id[0])
                ->set_controller(Leyka_Settings_Factory::get_instance()->get_controller($screen_full_id[1]))
                ->render_content();

        } catch(Exception $ex) {
            echo '<pre>'.sprintf(__('Settings display error (code %s): %s', 'leyka'), $ex->getCode(), $ex->getMessage()).'</pre>';
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
		foreach(leyka_opt_alloc()->get_tabs() as $tab_id => $tab_label) {
			$out .= '<a href="'
			    .($this->get_default_settings_tab() === $tab_id ? $base_url : add_query_arg('stage', $tab_id, $base_url))
			    .'" class="'.($this->get_current_settings_tab() === $tab_id ? 'nav-tab nav-tab-active' : 'nav-tab').'">'
			    .$tab_label.'</a>';
		}

		$out = apply_filters('leyka_admin_settings_tabs_menu', $out); // To add some new tabs to the nav menu

		$out .= '<a href="'.admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init').'" class="init-wizard-tab"></a>';

		return $out;

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

//    public function extensions_screen() {
//
//        if( !current_user_can('leyka_manage_options') ) {
//            wp_die(__('You do not have permissions to access this page.', 'leyka'));
//		}
//
//        do_action('leyka_pre_extensions_actions');
//
//        $this->_show_admin_template('extensions-list-page');
//
//        do_action('leyka_post_extensions_actions');
//        do_action('leyka_post_admin_actions');
//
//    }

    public function leyka_extension_settings_screen() {

        if( !current_user_can('leyka_manage_options') && empty($_GET['extension']) ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
		}

        do_action('leyka_pre_extension_settings_actions');

        $this->_show_admin_template('extension-settings-page');

        do_action('leyka_post_extension_settings_actions');
        do_action('leyka_post_admin_actions');

    }

    public function help_screen() {

        if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
		}

        do_action('leyka_pre_help_actions');

        add_meta_box(
            'leyka_docs_info',
            __('Leyka documentation', 'leyka'),
            function(){ $this->_show_admin_template('metabox-docs-info'); },
            'dashboard_page_leyka_help',
            'normal'
        );
        // Dark background for the metabox:
        add_filter('postbox_classes_dashboard_page_leyka_help_leyka_docs_info', function($classes){
            return ['leyka-metabox-dark'];
        });

        add_meta_box(
            'leyka_feedback',
            __('Ask a question', 'leyka'),
            function(){
                $this->_show_admin_template('metabox-feedback');
            },
            'dashboard_page_leyka_help',
            'lower'
        );
        // Dark background for the metabox:
        add_filter('postbox_classes_dashboard_page_leyka_help_leyka_feedback', function($classes){
            return ['leyka-metabox-dark'];
        });

        $this->_show_admin_template('help-page');

        do_action('leyka_post_help_actions');
        do_action('leyka_post_admin_actions');

    }

    /** @todo Move the method to the leyka-ajax.php */
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
            if( !$email || !is_email($email) ) {
                continue;
            }

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
				<pre>%s</pre>",
                    $_POST['topic'], $_POST['name'], $_POST['email'], nl2br($_POST['text']),
                    home_url(), get_bloginfo('name'), $_SERVER['SERVER_ADDR'],
                    get_bloginfo('version'), LEYKA_VERSION, get_bloginfo('admin_email'),
                    get_bloginfo('language'), get_bloginfo('charset'),
                    $_SERVER['SERVER_SOFTWARE'], $_SERVER['HTTP_USER_AGENT'],
					$site_env
                ),
                ['From: '.$_POST['name'].' <'.$_POST['email'].'>', 'Return-path: '.$_POST['email'],]
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
        wp_enqueue_script('jquery-dataTables', LEYKA_PLUGIN_BASE_URL.'js/jquery.dataTables.min.js', ['jquery'], false, true);

        wp_localize_script('jquery-dataTables', 'leyka_dt', [
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
        ]);

        return 'jquery-dataTables';

    }

	public function load_frontend_scripts() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', [], LEYKA_VERSION);
		wp_enqueue_style(
		    'leyka-admin-everywhere',
            LEYKA_PLUGIN_BASE_URL.'assets/css/admin-everywhere.css',
            [],
            LEYKA_VERSION
        );

		$screen = get_current_screen();
		if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
			return;
        }

        // Base admin area js/css:
        $current_screen = get_current_screen();
        $dependencies = ['jquery',];

        wp_enqueue_style(
            'jqueryui',
            'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css',
            false,
            null
        );

        wp_enqueue_style('leyka-settings', LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css', ['jqueryui'], LEYKA_VERSION);

        // Colorpicker fields support:
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        if(function_exists('wp_enqueue_code_editor')) { // The function is available in WP v4.9.0+
            wp_enqueue_code_editor(['type' => 'text/css', 'codemirror' => ['autoRefresh' => true,]]); // Add the code editor lib
        }

        $dependencies[] = 'jquery-ui-tooltip'; // For elements tooltips everywhere

        // WP admin metaboxes support:
        $dependencies[] = 'postbox';
        $dependencies[] = 'jquery-ui-accordion';
        $dependencies[] = 'jquery-ui-sortable';
        $dependencies[] = 'jquery-ui-selectmenu';
        $dependencies[] = 'tags-box';

        if(in_array($current_screen->id, ['admin_page_leyka_donation_info', 'dashboard_page_leyka_donor_info',])) {
            $dependencies[] = $this->_load_data_tables();
        }

        // Settings pages:
        if(mb_stristr($current_screen->id, '_page_leyka_settings') !== false) {

            $dependencies[] = 'jquery-ui-sortable';

            wp_enqueue_script('leyka-sticky', LEYKA_PLUGIN_BASE_URL.'js/jquery.sticky.js', $dependencies, LEYKA_VERSION, true);
            $dependencies[] = 'leyka-sticky';

        }

        if($current_screen->post_type === Leyka_Donation_Management::$post_type) {
            $dependencies[] = 'jquery-ui-autocomplete';
        }

        $js_data = apply_filters('leyka_admin_js_localized_strings', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajax_loader_url' => LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif',
            'homeurl' => home_url('/'),
            'admin_url' => admin_url(),
            'plugin_url' => LEYKA_PLUGIN_BASE_URL,
            'field_required' => __('This field is required to be filled', 'leyka'),
            'field_x_required'=> __('%s value is mandatory', 'leyka'),
            'email_invalid_msg' => __('You have entered an invalid email', 'leyka'),
            'common_error_message' => __('Error while saving the data', 'leyka'),
            'no_filters_while_exporting_warning_message' => __('Choose some filters values before exporting, please!', 'leyka'),
			'error_message' => __('Error!', 'leyka'),
            'default_image_message' => __('Default', 'leyka'),
			'disconnect_stats' => __('Disconnect statistics', 'leyka'),
            'confirm_delete_comment' => __('Delete comment?', 'leyka'),
            'first_donation_date_incomplete_message' => __('To correctly search for "First Payment Date", select the range of their two dates.', 'leyka'),
            'last_donation_date_incomplete_message' => __('To correctly search for "Last Payment Date", select the range of their two dates.', 'leyka'),
            'extension_deletion_confirm_text' => __('Are you sure you want to remove the extension completely?', 'leyka'),
            'extensions_list_page_url' => admin_url('admin.php?page=leyka_extensions'),
            'extension_colors_reset' => __('Reset settings', 'leyka'),
            'extension_colors_make_change' => __('Make changes', 'leyka'),
        ]);

        if(isset($_GET['page']) && $_GET['page'] === 'leyka') {

            $dependencies[] = 'jquery-ui-dialog';

            wp_enqueue_script(
                'leyka-admin',
                LEYKA_PLUGIN_BASE_URL.'assets/js/Chart.v2.8.0.min.js',
                $dependencies,
                LEYKA_VERSION,
                true
            );

        }

		leyka_localize_rich_html_text_tags();

        // Campaign edit page:
		if(
		    $screen->post_type === Leyka_Campaign_Management::$post_type
            && $screen->base === 'post'
            && ( !$screen->action || $screen->action === 'add' )
        ) {

            $dependencies[] = $this->_load_data_tables();

            if(function_exists('wp_enqueue_code_editor')) { // The function is available in WP v4.9.0+
                wp_enqueue_code_editor(['type' => 'text/css', 'codemirror' => ['autoRefresh' => true,]]); // Add the code editor lib
            }

            wp_enqueue_script('jquery-ui-dialog');

        }

        $locale = get_locale();
        wp_enqueue_script(
            'jquery-ui-datepicker-locale',
            LEYKA_PLUGIN_BASE_URL."js/jq-datepicker-locales/$locale.js",
            ['jquery-ui-datepicker'], LEYKA_VERSION, true
        );

        // Donation info/edit page:
        if(
            ($screen->post_type === Leyka_Donation_Management::$post_type && $screen->base === 'post') // Post-based Donations
            || (isset($_GET['page']) && $_GET['page'] == 'leyka_donation_info') // Sep-based Donations
        ) {

            $dependencies[] = 'jquery-ui-datepicker-locale';

            $js_data = $js_data + [
                'add_donation_button_text' => __('Add the donation', 'leyka'),
                'field_required' => __('This field is required to be filled', 'leyka'),
                'campaign_required' => __('Selecting a campaign is required', 'leyka'),
                'email_invalid_msg' => __('You have entered an invalid email', 'leyka'),
                'amount_incorrect_msg' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
                'donation_source_required' => __('Please, set one of a payment methods or just type a few words to describe a source for this donation', 'leyka'),
            ];

        }

        $dependencies[] = 'jquery-ui-autocomplete';

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL.'js/jquery.easyModal.min.js', [], false, true);

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