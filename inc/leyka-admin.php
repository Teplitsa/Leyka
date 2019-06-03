<?php if( !defined('WPINC') ) die;

/**
 * Leyka Admin setup
 **/

class Leyka_Admin_Setup extends Leyka_Singleton {

	protected static $_instance = null;

	protected function __construct() {

		add_action('admin_menu', array($this, 'admin_menu_setup'), 9);

		add_action('admin_enqueue_scripts', array($this, 'load_frontend_scripts'));

        add_action('admin_init', array($this, 'pre_admin_actions'));

        add_action('wp_ajax_leyka_send_feedback', array($this, 'ajax_send_feedback'));

        add_filter('plugin_row_meta', array($this, 'set_plugin_meta'), 10, 2);

		add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, array($this, 'add_plugins_list_links'));

		add_action('leyka_post_admin_actions', array($this, 'show_footer'));

		require_once LEYKA_PLUGIN_DIR.'/inc/leyka-class-portlet-controller.php';

    }

    public function set_plugin_meta($links, $file) {

        if($file == LEYKA_PLUGIN_INNER_SHORT_NAME) {
            $links[] = '<a href="https://github.com/Teplitsa/Leyka/">GitHub</a>';
        }

        return $links;

    }

    // A little function to support the full abilities of the metaboxes on any plugin's page:
    public function full_metaboxes_support($current_stage = false) {?>

        <!-- Metaboxes reordering and folding support -->
        <form style="display:none" method="get" action="#">
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

    }

	/** Admin Menu **/
    public function admin_menu_setup() {

        // Leyka menu root:
        add_menu_page(__('Leyka Dashboard', 'leyka'), __('Leyka', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        add_submenu_page('leyka', __('Leyka Dashboard', 'leyka'), __('Dashboard', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        add_submenu_page('leyka', __('Donations', 'leyka'), __('Donations', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Donation_Management::$post_type);

        add_submenu_page('leyka', __('New correctional donation', 'leyka'), _x('Add new', 'donation', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Donation_Management::$post_type);

        add_submenu_page('leyka', __('Campaigns', 'leyka'), __('Campaigns', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Campaign_Management::$post_type);

        add_submenu_page('leyka', __('New campaign', 'leyka'), _x('Add new', 'campaign', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Campaign_Management::$post_type);

        add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), 'leyka_manage_options', 'leyka_settings', array($this, 'settings_screen'));

        add_submenu_page('leyka', __('Connect to us', 'leyka'), __('Feedback', 'leyka'), 'leyka_manage_donations', 'leyka_feedback', array($this, 'feedback_screen'));

        // Wizards pages group:
        add_submenu_page(NULL, 'Leyka Wizard', 'Leyka Wizard', 'leyka_manage_options', 'leyka_settings_new', array($this, 'settings_new_screen'));

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

        do_action('leyka_pre_dashboard_actions');?>

		<div class="wrap leyka-admin leyka-dashboard-page">
		    <h1><?php _e('Leyka dashboard', 'leyka');?></h1>

            <?php if(leyka()->opt('send_plugin_stats') !== 'y') {?>
            <div class="send-plugin-stats-invite">
                <div class="invite-text">
                    <?php _e('Please, turn on the option to send anonymous plugin usage data to help us diagnose', 'leyka');?>
                </div>
                <div class="invite-link">
                    <button class="send-plugin-usage-stats-y"><?php _e('Allow usage statistics collection', 'leyka');?></button>
                </div>
            </div>
            <?php }?>

		    <div class="leyka-dashaboard-content">

		        <div class="main-col">

                    <?php if($this->has_banners('admin-dashboard', 'main')) {
                        $this->show_banner('admin-dashboard', 'main');
                    }

                    $_GET['interval'] = empty($_GET['interval']) ? 'year' : $_GET['interval'];
                    $current_url = admin_url('admin.php?page=leyka');?>

                    <div class="plugin-data-interval">
                        <a href="<?php echo add_query_arg('interval', 'year', $current_url);?>" class="<?php echo $_GET['interval'] === 'year' ? 'current-interval' : '';?>"><?php _e('Year', 'leyka');?></a>
                        <a href="<?php echo add_query_arg('interval', 'half-year', $current_url);?>" class="<?php echo $_GET['interval'] === 'half-year' ? 'current-interval' : '';?>"><?php _e('Half-year', 'leyka');?></a>
                        <a href="<?php echo add_query_arg('interval', 'quarter', $current_url);?>" class="<?php echo $_GET['interval'] === 'quarter' ? 'current-interval' : '';?>"><?php _e('Quarter', 'leyka');?></a>
                        <a href="<?php echo add_query_arg('interval', 'month', $current_url);?>" class="<?php echo $_GET['interval'] === 'month' ? 'current-interval' : '';?>"><?php _e('Month', 'leyka');?></a>
                        <a href="<?php echo add_query_arg('interval', 'week', $current_url);?>" class="<?php echo $_GET['interval'] === 'week' ? 'current-interval' : '';?>"><?php _e('Week', 'leyka');?></a>
                    </div>

                    <div class="leyka-dashboard-row">
                        <?php $this->show_admin_portlet('stats-donations-main', array('interval' => $_GET['interval']));
                        $this->show_admin_portlet('stats-recurring', array('interval' => $_GET['interval']));?>
                    </div>

                    <div class="leyka-dashboard-row">
                        <?php $this->show_admin_portlet('donations-dynamics', array('interval' => $_GET['interval']));?>
                    </div>

                    <div class="leyka-dashboard-row">
                        <?php $this->show_admin_portlet('recent-donations', array(
                            'interval' => $_GET['interval'],
                            'number' => 5,
                        ));?>
                    </div>

                </div>
                <div class="sidebar-col">
                    <?php $this->show_dashboard_sidebar();?>
                </div>
            </div>

        </div>

	<?php do_action('leyka_post_dashboard_actions');
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

	    <div class="leyka-admin-portlet">

            <div class="portlet-header">
                <img src="<?php echo home_url($portlet_data['thumbnail']);?>" alt="">
                <?php echo $portlet_data['title'];?>
            </div>

            <div class="portlet-content">
                <?php require_once($portlet_file);?>
            </div>

        </div>

    <?php
	}

	public function show_dashboard_sidebar() {

        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php');?>

		<div class="leyka-dashboard-sidebar-part">

            <div class="leyka-logo"><img src="" alt=""></div>

            <div class="leyka-description">
                <?php _e('Leyka is a simple donations collection & management system for your website', 'leyka'); // Р›РµР№РєР° - РїСЂРѕСЃС‚Р°СЏ СЃРёСЃС‚РµРјР° РґР»СЏ СЃР±РѕСЂР° Рё СѓРїСЂР°РІР»РµРЅРёСЏ РїРѕР¶РµСЂС‚РІРѕРІР°РЅРёСЏРјРё РЅР° РІР°С€РµРј СЃР°Р№С‚Рµ ?>
            </div>

            <div class="leyka-official-website">
                <a href="//leyka.te-st.ru/" target="_blank"><?php _e('Go to the plugin documentation', 'leyka');?></a>
            </div>

        </div>

        <?php $init_wizard_controller = Leyka_Settings_Factory::get_instance()->get_controller('init');
        $main_settings_steps = $init_wizard_controller->navigation_data[0]['section_id'] === 'rd' ?
            $init_wizard_controller->navigation_data[0]['steps'] : array();

        if($main_settings_steps) {?>
        <div class="leyka-dashboard-sidebar-part">

            <h3><?php _e('My data', 'leyka');?></h3>

            <div class="sidebar-part-content settings-state">
                <?php foreach($main_settings_steps as $step) {

                    $step_invalid_options = leyka_is_settings_step_valid($step['step_id']);?>

                    <div class="settings-step-set">
                        <div class="step-setup-status <?php echo !is_array($step_invalid_options) ? 'step-valid' : 'step-invalid';?>"></div>
                        <div class="step-title"><?php echo $step['title'];?></div>

                    <?php if(is_array($step_invalid_options)) {?>

                        <div class="step-invalid-options">

                        <?php if(count($step_invalid_options) <= 5) {
                            foreach($step_invalid_options as $option_id) { ?>
                                <div class="invalid-option">
                                    <?php echo leyka_options()->get_title_of($option_id); ?>
                                </div>
                            <?php }
                        } else {?>
                            <div class="invalid-option"><?php _e('Some option fields are not filled correctly', 'leyka');?></div>
                        <?php }?>

                        </div>
                        <?php }?>
                    </div>

                <?php }?>
            </div>

            <a href="<?php echo admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init');?>" class="init-wizard-link"><?php _e('To the step-by-step setup', 'leyka'); // РџРµСЂРµР№С‚Рё Рє РїРѕС€Р°РіРѕРІРѕР№ СѓСЃС‚Р°РЅРѕРІРєРµ ?></a>

        </div>
        <?php }?>

        <div class="leyka-dashboard-sidebar-part">

            <h3><?php  _e('Payment gateways', 'leyka');?></h3>

            <div class="sidebar-part-content gateways">

                <?php foreach(leyka()->get_gateways('activating') as $gateway) {?>
                <div class="gateway status-activating">
                    <div class="gateway-logo"><img src="<?php echo $gateway->icon_url;?>" alt=""></div>
                    <div class="gateway-data">
                        <div class="gateway-title"><?php echo $gateway->title;?></div>
                        <div class="gateway-activation-status"><?php _e('Activating', 'leyka');?></div>
                    </div>
                </div>
                <?php }?>

                <?php foreach(leyka()->get_gateways('active') as $gateway) {?>
                    <div class="gateway status-active">
                        <div class="gateway-logo"><img src="<?php echo $gateway->icon_url;?>" alt=""></div>
                        <div class="gateway-data">
                            <div class="gateway-title"><?php echo $gateway->title;?></div>
                            <div class="gateway-activation-status"><?php _e('Activating', 'leyka');?></div>
                        </div>
                    </div>
                <?php }?>

            </div>

            <div class="add-gateway-link">
                <a href="<?php echo admin_url('admin.php?page=leyka_settings');?>"><?php _e('Add gateway', 'leyka');?></a>
            </div>

        </div>

        <div class="leyka-dashboard-sidebar-part">

            <h3><?php  _e('Diagnostic data', 'leyka');?></h3>

            <div class="sidebar-part-content diagnostic-data">
                <div class="data-line"><?php echo __('Leyka:', 'leyka').' '.LEYKA_VERSION;?></div>
                <div class="data-line">
                    <?php $template = leyka()->get_template(leyka()->opt('donation_form_template'));
                    echo __('Default template:', 'leyka').' '.__($template['name'], 'leyka');?>
                </div>
                <div class="data-line php-actuality-status">

                    <?php if(version_compare(phpversion(), '5.6') == -1) {
                        $php_version_actuality = 'bad';
                    } else if(version_compare(phpversion(), '5.6') >= 0 && version_compare(phpversion(), '7.1') == -1) {
                        $php_version_actuality = 'average';
                    } else if(version_compare(phpversion(), '7.1') >= 0 && version_compare(phpversion(), '7.2') == -1) {
                        $php_version_actuality = 'good';
                    } else {
                        $php_version_actuality = 'excellent';
                    }?>

                    <div class="php-version <?php echo $php_version_actuality;?>"><?php echo 'PHP: '.phpversion();?></div>

                </div>
                <div class="data-line"><?php echo 'WordPress: '.get_bloginfo('version');?></div>

                <?php $cronjobs_status = leyka_get_cronjobs_status();?>
                <div class="data-line cron-state">
                    Cron: <span class="cron-state <?php echo $cronjobs_status['status'];?>"><?php echo mb_strtolower($cronjobs_status['title']);?></span>
                    <a href="#" class="cron-setup-howto"><?php _e('How to set it up?');?></a>
                </div>

                <div class="data-line">
                    <?php $protocol = parse_url(home_url(), PHP_URL_SCHEME);
                    echo __('Protocol:', 'leyka').' ';?>
                    <span class="protocol <?php echo $protocol == 'https' ? 'safe' : 'not-safe';?>"><?php echo mb_strtoupper($protocol);?></span>
                </div>
                <div class="data-line">

                    <?php $php_extensions_needed = array('curl', 'date', 'ereg', 'filter', 'ftp', 'gd', 'hash', 'iconv', 'json', 'libxml', 'mbstring', 'mysql', 'mysqli', 'openssl', 'pcre', 'simplexml', 'sockets', 'spl', 'tokenizer', 'xmlreader', 'xmlwriter', 'zlib',); // According to https://wordpress.stackexchange.com/questions/42098/what-are-php-extensions-and-libraries-wp-needs-and-or-uses
                    $php_extensions = get_loaded_extensions();

                    foreach($php_extensions_needed as &$extension_needed) {
                        $extension_needed = '<span class="php-ext '.(in_array($extension_needed, $php_extensions) ? '' : 'php-ext-missing').'">'.mb_strtolower($extension_needed).'</span>';
                    }

                    echo __('PHP modules:', 'leyka').' '.implode(', ', $php_extensions_needed);?>

                </div>
            </div>

        </div>

        <?php
	}

	public function has_banners($page = false, $location = false) {
	    return false; /** @todo Only for nooooow */
    }

    public function show_banner($page = false, $location = false) {
        return false; /** @todo Only for nooooow */
    }
	
	public function is_v3_settings_page($stage) {
		return in_array($stage, array('payment', 'email', 'beneficiary', 'technical', 'view', 'additional'));
	}

	public function is_separate_forms_stage($stage) {
		return in_array($stage, array('email', 'beneficiary', 'technical', 'view', 'additional'));
	}

	public function settings_screen() {

		if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        $current_stage = $this->get_current_settings_tab();
		$is_separate_sections_forms = $this->is_separate_forms_stage($current_stage);

		require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php'); // Basic Controller class
        require_once(LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-admin-template-tags.php');

		do_action('leyka_pre_settings_actions', $current_stage);

        // Process settings change:
	    if((
                !empty($_POST["leyka_settings_{$current_stage}_submit"])
                || !empty($_POST["leyka_settings_stage-{$current_stage}_submit"])
            )
	        /** @todo Find what's wrong with the nonce check. */
//	        && wp_verify_nonce('_leyka_nonce', "leyka_settings_{$current_stage}")
        ) {

			do_action('leyka_settings_submit', $current_stage);
			do_action("leyka_settings_{$current_stage}_submit");

		}?>

		<div class="wrap leyka-admin leyka-settings-page">

		    <h1><?php esc_html_e('Leyka settings', 'leyka');?></h1>

            <h2 class="nav-tab-wrapper"><?php echo $this->settings_tabs_menu();?></h2>

            <div id="tab-container">

                <?php $admin_page_args = array(
                    'stage' => $current_stage,
                    'gateway' => empty($_GET['gateway']) ? '' : $_GET['gateway']
                );
                $admin_page = 'admin.php?page=leyka_settings';
                foreach($admin_page_args as $arg_name => $value) {
                    if($value) {
                        $admin_page = add_query_arg($arg_name, $value, $admin_page);
                    }
                }

                if( !$is_separate_sections_forms ) {?>

                <form method="post" action="<?php echo admin_url($admin_page);?>" id="leyka-settings-form">
                <?php wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');

				}

                if(file_exists(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-{$current_stage}.php")) {
                    require_once(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-{$current_stage}.php");
                } else {

                    do_action("leyka_settings_pre_{$current_stage}_fields");

                    foreach(leyka_opt_alloc()->get_tab_options($current_stage) as $option) { // Render each option/section

						if($is_separate_sections_forms) {?>

                        <form method="post" action="<?php echo admin_url($admin_page);?>" id="leyka-settings-form">

							<?php if(isset($option['section']['name'])) {?>
							<input type="hidden" name="leyka_options_section" value="<?php echo $option['section']['name'];?>">
							<?php }?>

						<?php wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');
							do_action("leyka_settings_pre_{$current_stage}_fields");

						}

                        if(is_array($option) && !empty($option['section'])) {

							$option['section']['is_separate_sections_forms'] = $is_separate_sections_forms;
							$option['section']['current_stage'] = $current_stage;
                            do_action('leyka_render_section', $option['section']);

                        } else { // is this case even possible?

                            $option_info = leyka_options()->get_info_of($option);
                            do_action("leyka_render_{$option_info['type']}", $option, $option_info);

                        }

						if($is_separate_sections_forms) {?>
                        </form>
						<?php }

                    }

                    do_action("leyka_settings_post_{$current_stage}_fields");?>

                    <?php if(!$is_separate_sections_forms) {?>
					<p class="submit">
                        <input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php esc_html_e('Save settings', 'leyka');?>" class="button-primary">
                    </p>
					<?php }

                }?>

				<?php if( !$is_separate_sections_forms ) {?>
                </form>
				<?php }?>

            </div>

			<?php include(LEYKA_PLUGIN_DIR.'inc/settings-fields-templates/leyka-helpchat.php');?>

		</div>

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

    /** Displaying feedback **/
    public function feedback_screen() {

        if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
		}

        $user = wp_get_current_user();?>

	<div class="wrap">
		<h2><?php _e('Send us a feedback', 'leyka');?></h2>

		<div class="leyka-feedback-description">
			<p><?php _e('Found a bug? Need a feature?', 'leyka'); ?></p>
			<p><?php _e('Please, <a href="https://github.com/Teplitsa/Leyka/issues/new">create an issue on Github</a> or send us a message with the following form', 'leyka'); ?></p>
		</div>

		<div class="feedback-columns">
			<div class="leyka-feedback-form">
				<img id="feedback-loader" style="display: none;" src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif';?>" alt="">
				<form id="feedback" action="#" method="post">
					<fieldset class="leyka-ff-field">
						<label for="feedback-topic"><?php _e('Message topic:', 'leyka');?></label>
						<input id="feedback-topic" name="topic" placeholder="<?php _e('For ex., Paypal support needed', 'leyka');?>" class="regular-text">
						<div id="feedback-topic-error" class="leyka-ff-field-error" style="display: none;"></div>
					</fieldset>
					<fieldset class="leyka-ff-field">
						<label for="feedback-name"><?php _e("Your name (we'll use it to address you only):", 'leyka');?></label>
						<input id="feedback-name" name="name" placeholder="<?php _e('For ex., Leo', 'leyka');?>" value="<?php echo $user->display_name;?>" class="regular-text">
						<div id="feedback-name-error" class="leyka-ff-field-error" style="display: none;"></div>
					</fieldset>
					<fieldset class="leyka-ff-field">
						<label for="feedback-email"><?php _e('Your email:', 'leyka');?></label>
						<input id="feedback-email" name="email" placeholder="<?php _e('your@mailbox.com', 'leyka');?>" value="<?php echo $user->user_email;?>" class="regular-text">
						<div id="feedback-email-error" class="leyka-ff-field-error" style="display: none;"></div>
					</fieldset>
					<fieldset class="leyka-ff-field">
						<label for="feedback-text"><?php _e('Your message:', 'leyka');?></label>
						<textarea id="feedback-text" name="text" class="regular-text"></textarea>
						<div id="feedback-text-error" class="leyka-ff-field-error" style="display: none;" ></div>
					</fieldset>
					<fieldset class="leyka-ff-field leyka-submit">
						<input type="hidden" id="nonce" value="<?php echo wp_create_nonce('leyka_feedback_sending');?>">
						<input type="submit" class="button-primary" value="<?php _e('Submit');?>">
					</fieldset>
				</form>
				<div id="message-ok" class="leyka-ff-msg ok" style="display: none;">
					<p><?php _e('<strong>Thank you!</strong> Your message sended successfully. We will answer it soon - please await our response on the email you entered.', 'leyka');?></p>
				</div>
				<div id="message-error" class="leyka-ff-msg wrong" style="display: none;">
					<p><?php _e("Sorry, but the message can't be sended. Please check your mail server settings.", 'leyka');?></p>
				</div>
			</div>
			<div class="feedback-sidebar"></div>
		</div>
		
	</div>

    <?php do_action('leyka_post_admin_actions');

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
                    "Р”РѕР±СЂС‹Р№ РґРµРЅСЊ!<br><br>
                РџРѕСЃС‚СѓРїРёР»Р° РЅРѕРІР°СЏ РѕР±СЂР°С‚РЅР°СЏ СЃРІСЏР·СЊ РѕС‚ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ Р›РµР№РєРё.<br><br>
                <strong>РўРµРјР°:</strong> %s<br>
                <strong>Р�РјСЏ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ:</strong> %s<br>
                <strong>РџРѕС‡С‚Р° РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ:</strong> %s<br>
                <strong>РўРµРєСЃС‚ СЃРѕРѕР±С‰РµРЅРёСЏ:</strong><br>%s<br><br>
                ---------------- РўРµС…РЅРёС‡РµСЃРєРёРµ РґР°РЅРЅС‹Рµ СЃР°Р№С‚Р° РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ --------------<br><br>
                <strong>CР°Р№С‚ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ:</strong> <a href='%s'>%s</a> (IP: %s)<br>
                <strong>Р’РµСЂСЃРёСЏ WP:</strong> %s<br>
                <strong>Р’РµСЂСЃРёСЏ Р›РµР№РєРё:</strong> %s<br>
                <strong>РџР°СЂР°РјРµС‚СЂ admin_email:</strong> %s<br>
                <strong>РЇР·С‹Рє:</strong> %s (РєРѕРґРёСЂРѕРІРєР°: %s)<br>
                <strong>РџРћ РІРµР±-СЃРµСЂРІРµСЂР°:</strong> %s<br>
                <strong>Р‘СЂР°СѓР·РµСЂ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ:</strong> %s<br>
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

	public function load_frontend_scripts() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', array(), LEYKA_VERSION);
		
		wp_enqueue_style('leyka-admin-common', LEYKA_PLUGIN_BASE_URL.'assets/css/admin-common.css', array(), LEYKA_VERSION);

		$screen = get_current_screen();
		if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
			return;
        }

        // Base admin area js/css:
        $leyka_admin_new = (isset($_GET['screen']) && count(explode('-', $_GET['screen'])) >= 2) // New settings pages (from v3.0)
            || (
                isset($_GET['page'])
                && $_GET['page'] === 'leyka_settings'
                && (empty($_GET['stage']) || $this->is_v3_settings_page($_GET['stage']))
                && empty($_GET['old'])
            )
            || ($screen->post_type === Leyka_Campaign_Management::$post_type && $screen->base === 'post')
            || (isset($_GET['page']) && $_GET['page'] === 'leyka');
            
        $current_screen = get_current_screen();
        $dependencies = array('jquery',);

        if($leyka_admin_new) {
            wp_enqueue_style('leyka-settings', LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css', array(), LEYKA_VERSION);
        } else { // Old admin pages (before v3.0)
	        wp_enqueue_style('leyka-admin', LEYKA_PLUGIN_BASE_URL.'css/admin.css', array(), LEYKA_VERSION);
	    }

        if( !$leyka_admin_new && $current_screen->id === 'toplevel_page_leyka' ) {
            $dependencies[] = 'postbox';
        }
        if(stristr($current_screen->id, '_page_leyka_settings') !== false) {

            if( !$leyka_admin_new ) {
                $dependencies[] = 'postbox';
            }

            $dependencies[] = 'jquery-ui-accordion';
            $dependencies[] = 'jquery-ui-sortable';

            wp_enqueue_script('leyka-sticky', LEYKA_PLUGIN_BASE_URL.'js/jquery.sticky.js', $dependencies, LEYKA_VERSION, true);
            $dependencies[] = 'leyka-sticky';

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
			'disconnect_stats' => esc_html__('Disconnect statistics', 'leyka'),
        ));

		if($leyka_admin_new) {

			wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);
            wp_enqueue_script(
                'leyka-settings',
                LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
                array('jquery',),
                LEYKA_VERSION,
                true
            );
            wp_localize_script('leyka-settings', 'leyka', $js_data);

        } else {

            wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'js/admin.js', $dependencies, LEYKA_VERSION, true);
			wp_enqueue_script(
			    'leyka-admin-helpchat',
			    LEYKA_PLUGIN_BASE_URL.'js/settings-helpchat.js',
			    $dependencies,
			    LEYKA_VERSION,
			    true
            );
            wp_localize_script('leyka-admin', 'leyka', $js_data);

        }

		leyka_localize_rich_html_text_tags();

        // Campaign editing page:
        if($screen->post_type === Leyka_Campaign_Management::$post_type && $screen->base === 'post' && !$screen->action) {

            wp_enqueue_style('jquery-dataTables', LEYKA_PLUGIN_BASE_URL.'css/jquery.dataTables.css');
            wp_enqueue_script(
                'jquery-dataTables',
                LEYKA_PLUGIN_BASE_URL.'js/jquery.dataTables.min.js',
                array('jquery'),
                false,
                true
            );
            wp_enqueue_script(
                'leyka-admin-edit-campaign',
                LEYKA_PLUGIN_BASE_URL.'js/admin-edit-campaign.js',
                array('jquery-dataTables', 'jquery'), LEYKA_VERSION, true
            );
            wp_localize_script('leyka-admin-edit-campaign', 'leyka_dt', $js_data + array(
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

            wp_enqueue_code_editor(array('type' => 'text/css')); // Add the code editor lib

        }

        // Donation editing page:
        if($screen->post_type === Leyka_Donation_Management::$post_type && $screen->base === 'post') {

            $locale = get_locale();
            if($locale !== 'en_US') {
                wp_enqueue_script(
                    'jquery-ui-datepicker-locale',
                    LEYKA_PLUGIN_BASE_URL."js/jq-datepicker-locales/$locale.js",
                    array('jquery-ui-datepicker'), LEYKA_VERSION, true
                );
            }

            wp_enqueue_script(
                'leyka-admin-add-edit-donation',
                LEYKA_PLUGIN_BASE_URL.'js/admin-add-edit-donation.js',
                array('jquery-ui-datepicker-locale'), LEYKA_VERSION, true
            );
            wp_localize_script('leyka-admin-add-edit-donation', 'leyka', $js_data + array(
                'add_donation_button_text' => __('Add the donation', 'leyka'),
                'field_required' => __('This field is required to be filled', 'leyka'),
                'campaign_required' => __('Selecting a campaign is required', 'leyka'),
                'email_invalid_msg' => __('You have entered an invalid email', 'leyka'),
                'amount_incorrect_msg' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
                'donation_source_required' => __('Please, set one of a payment methods or just type a few words to describe a source for this donation', 'leyka'),
            ));

        }

	}

}

if( !function_exists('leyka_admin_get_slug_edit_field') ) {
    function leyka_admin_get_slug_edit_field($campaign) {

        $campaign = new Leyka_Campaign($campaign);
        if($campaign->id <= 0) {
            return '';
        }

        $permalinks_on = !!get_option('permalink_structure');

        $campaign_permalink_parts = get_sample_permalink($campaign->id); // [0] - current URL template, [1] - current slug
        $campaign_base_url = rtrim(str_replace('%pagename%', '', $campaign_permalink_parts[0]), '/');
        $campaign_permalink_full = str_replace('%pagename%', $campaign_permalink_parts[1], $campaign_permalink_parts[0]);

        ob_start();?>

        <div class="leyka-campaign-permalink">

        <?php if($permalinks_on) {?>

            <span class="leyka-current-value">
                <span class="base-url"><?php echo $campaign_base_url;?></span>/<span class="current-slug"><?php echo $campaign_permalink_parts[1];?></span>
            </span>

            <a href="<?php echo get_edit_post_link($campaign->id);?>" class="inline-action inline-edit-slug">Р РµРґР°РєС‚РёСЂРѕРІР°С‚СЊ</a>

            <span class="inline-edit-slug-form" data-slug-original="<?php echo $campaign_permalink_parts[1];?>" data-campaign-id="<?php echo $campaign->id;?>" data-nonce="<?php echo wp_create_nonce('leyka-edit-campaign-slug');?>" style="display: none;">
                <input type="text" class="leyka-slug-field inline-input" value="<?php echo $campaign_permalink_parts[1];?>">
                <span class="slug-submit-buttons">
                    <button class="inline-submit"><?php esc_html_e('OK');?></button>
                    <button class="inline-reset"><?php esc_html_e('Cancel');?></button>
                </span>
            </span>

        <?php } else {?>

            <span class="base-url"><?php echo $campaign_permalink_full;?></span>
            <a href="<?php echo admin_url('options-permalink.php');?>" class="permalink-action" target="_blank">Р’РєР»СЋС‡РёС‚СЊ РїРѕСЃС‚РѕСЏРЅРЅС‹Рµ СЃСЃС‹Р»РєРё</a>

        <?php }?>

            <div class="edit-permalink-loading">
                 <div class="loader-wrap">
                    <span class="leyka-loader xs"></span>
                 </div>
            </div>

        </div>

    <?php return ob_get_clean();

    }
}

if( !function_exists('leyka_admin_get_shortcode_field') ) {
    function leyka_admin_get_shortcode_field($campaign) {

        $campaign = new Leyka_Campaign($campaign);
        if($campaign->id <= 0) {
            return '';
        }

        $shortcode = Leyka_Campaign_Management::get_campaign_form_shortcode($campaign->id);
        ob_start();?>

        <span class="leyka-current-value"><?php echo esc_attr($shortcode);?></span>
        <span class="leyka-campaign-shortcode-field" style="display: none;">
            <input type="text" class="embed-code read-only campaign-shortcode inline-input" id="campaign-shortcode" value="<?php echo esc_attr($shortcode);?>">
            <button class="inline-reset"><?php esc_html_e('Cancel');?></button>
        </span>

    <?php return ob_get_clean();

    }
}

if( !function_exists('leyka_sync_plugin_stats_option_action') ) {
	function leyka_sync_plugin_stats_option_action($old_value, $new_value) {

	    if( !$old_value && $new_value === 'n' ) {
	        return;
	    }

		leyka_sync_plugin_stats_option();

	}
}
add_action('leyka_after_save_option-send_plugin_stats', 'leyka_sync_plugin_stats_option_action', 10, 2);

if( !function_exists('leyka_get_admin_footer') ) {
    function leyka_get_admin_footer($footer_class='', $old_footer_html='') {
        ob_start();
        ?>
        <div class="leyka-dashboard-footer leyka-admin-footer <?php echo $footer_class;?>">
        	<a href="https://te-st.ru/" class="te-st-logo">
        		<img  src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/logo-te-st-with-caption.svg" alt="<?php _e('te-st.ru', 'leyka');?>" />
        	</a>
        	<div class="links">
        		<div class="te-st-link">
        			<span><?php _e('Created by', 'leyka');?></span>
        			<a href="https://te-st.ru/"><?php _e('Teplitsa. Technologies for Social Good', 'leyka');?></a>
        		</div>
        		<div class="info-links">
                    <a href="https://leyka.te-st.ru/sla/" target="_blank"><?php _e('SLA', 'leyka');?></a>
                    <a href="https://github.com/Teplitsa/leyka/wiki" target="_blank"><?php _e('Documentation', 'leyka');?></a>
                    <a href="https://t.me/joinchat/BshvgVUqHJLyCNIXd6pZXQ" target="_blank"><?php _e('Developer chat', 'leyka');?></a>
        		</div>
        	</div>
        </div>
<?php
        return ob_get_clean() . $old_footer_html;
    }

}

if( !function_exists('leyka_show_admin_footer') ) {
    function leyka_show_admin_footer($old_footer_html='') {
        $footer_class = '';
        if(!empty($_GET['screen']) && strpos($_GET['screen'], 'wizard-') === 0) {
            $footer_class .= 'leyka-wizard-footer';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka_settings' && empty($_GET['screen'])) {
            $footer_class .= 'leyka-settings-footer';
        }
        
        echo leyka_get_admin_footer($footer_class, $old_footer_html);
    }
}

if( !function_exists('leyka_show_admin_footer_on_default_pages') ) {
    function leyka_show_admin_footer_on_default_pages($old_footer_html='') {
        $screen = get_current_screen();
        if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
            return $old_footer_html;
        }
        elseif( !empty($_GET['post_type']) && in_array($_GET['post_type'], array('leyka_donation', 'leyka_campaign')) ) {
            return leyka_get_admin_footer('', $old_footer_html);
        }
    }
	add_filter( 'admin_footer_text', 'leyka_show_admin_footer_on_default_pages', 20 );
}

if( !function_exists('leyka_admin_body_class') ) {
    function leyka_admin_body_class($classes) {
        $leyka_page_class = '';
        
        if(!empty($_GET['screen']) && strpos($_GET['screen'], 'wizard-') === 0) {
            $leyka_page_class .= 'leyka-admin-wizard';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka_settings' && empty($_GET['screen'])) {
            $leyka_page_class .= 'leyka-admin-settings';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka' && empty($_GET['screen'])) {
            $leyka_page_class .= 'leyka-admin-dashboard';
        }
        elseif( (!empty($_GET['post_type']) && in_array($_GET['post_type'], array('leyka_donation', 'leyka_campaign'))) 
            || (!empty($_GET['page']) && $_GET['page'] === 'leyka_feedback' && empty($_GET['screen']))) {
            $leyka_page_class .= 'leyka-admin-default';
        }
        
        return $classes . ' ' . $leyka_page_class . ' ';
    }
    add_filter( 'admin_body_class', 'leyka_admin_body_class', 20 );
}
