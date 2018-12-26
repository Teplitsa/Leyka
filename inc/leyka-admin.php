<?php if( !defined('WPINC') ) die;

/**
 * Leyka Admin setup
 **/

class Leyka_Admin_Setup extends Leyka_Singleton {

	protected static $_instance = null;

	protected function __construct() {

		add_action('admin_menu', array($this, 'adminMenuSetup'), 9);

		add_action('admin_enqueue_scripts', array($this, 'loadFrontendScripts'));

        add_action('admin_init', array($this, 'preAdminActions'));

        add_action('wp_ajax_leyka_send_feedback', array($this, 'ajaxSendFeedback'));

        add_filter('plugin_row_meta', array($this, 'setPluginMeta'), 10, 2);

		add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, array($this, 'addPluginsListLinks'));

        // Metaboxes support where it is needed:
        add_action('leyka_pre_settings_actions', array($this, 'fullMetaboxesSupport'));
        add_action('leyka_dashboard_actions', array($this, 'fullMetaboxesSupport'));

    }

    public function setPluginMeta($links, $file) {

        if($file == LEYKA_PLUGIN_INNER_SHORT_NAME) {
            $links[] = '<a href="https://github.com/Teplitsa/Leyka/">GitHub</a>';
        }

        return $links;

    }

    // A little function to support the full abilities of the metaboxes on any plugin's page:
    public function fullMetaboxesSupport($current_stage = false) {?>

        <!-- Metaboxes reordering and folding support -->
        <form style="display:none" method="get" action="#">
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
        </form>
    <?php }

    public function preAdminActions() {

        function leykaAdminTitle($admin_title) {

            if(isset($_GET['page']) && $_GET['page'] === 'leyka_settings_new' && isset($_GET['screen'])) {

                $screen_full_id = explode('-', $_GET['screen']);

                // $screen_full_id[0] - view type (e.g. 'wizard' or 'control_panel')
                // $screen_full_id[1] - settings area given (e.g. 'init').

                require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php');

                $admin_title = get_bloginfo('name')
                    .' &#8212; '
                    .Leyka_Settings_Factory::get_instance()->getController($screen_full_id[1])->title;

            }

            return $admin_title;

        }
        add_filter('admin_title', 'leykaAdminTitle');

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

            function leykaAdminNotice_v3_update() {?>

                <div id="message" class="updated leyka-message">
                    <a class="leyka-message-close notice-dismiss" href="<?php echo esc_url(wp_nonce_url(remove_query_arg('leyka_reset_msg', add_query_arg('leyka-hide-notice', 'v3_update')), 'leyka_hide_notice_nonce', '_leyka_notice_nonce'));?>">
                        <?php esc_html_e('Dismiss', 'leyka');?>
                    </a>
                    <p><?php printf(esc_html__('Hello! Thank you for updating Leyka plugin to the 3rd version. Please read about all new features %shere%s.', 'leyka'), '<a href="//te-st.ru/2018/12/18/leyka-3-update/" target="_blank">', '</a>');?></p>
                </div>
            <?php
            }
            add_action('admin_notices', 'leykaAdminNotice_v3_update');

        }

    }

	/** Admin Menu **/
    public function adminMenuSetup() {

        // Leyka menu root:
        add_menu_page(__('Leyka Dashboard', 'leyka'), __('Leyka', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboardScreen'));

        add_submenu_page('leyka', __('Leyka Dashboard', 'leyka'), __('Dashboard', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboardScreen'));

        add_submenu_page('leyka', __('Donations', 'leyka'), __('Donations', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Donation_Management::$post_type);

        add_submenu_page('leyka', __('New correctional donation', 'leyka'), _x('Add new', 'donation', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Donation_Management::$post_type);

        add_submenu_page('leyka', __('Campaigns', 'leyka'), __('Campaigns', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Campaign_Management::$post_type);

        add_submenu_page('leyka', __('New campaign', 'leyka'), _x('Add new', 'campaign', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Campaign_Management::$post_type);

        add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), 'leyka_manage_options', 'leyka_settings', array($this, 'settingsScreen'));

        add_submenu_page('leyka', __('Connect to us', 'leyka'), __('Feedback', 'leyka'), 'leyka_manage_donations', 'leyka_feedback', array($this, 'feedbackScreen'));

        // Wizards pages group:
        add_submenu_page(NULL, 'Some Leyka Wizard', 'Some Leyka Wizard', 'leyka_manage_options', 'leyka_settings_new', array($this, 'settingsNewScreen'));

        do_action('leyka_admin_menu_setup');

        global $submenu;

        if( !empty($submenu['leyka']) ) {
            $submenu['leyka'] = apply_filters('leyka_admin_menu_order', $submenu['leyka']);
		}

    }

	/** Settings link in plugin list table **/
	public function addPluginsListLinks($links) {

		$links[] = '<a href="'.admin_url('admin.php?page=leyka_settings').'">'.__( 'Settings', 'leyka').'</a>';

		return $links;

	}

	/** Displaying dashboard **/
	public function dashboardScreen() {

		if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('Sorry, but you do not have permissions to access this page.', 'leyka'));
        }

		do_action('leyka_dashboard_actions'); // Collapsible

		add_meta_box('leyka_guide', __('First steps', 'leyka'), array($this, 'guideMetaboxScreen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_status', __('Settings', 'leyka'), array($this, 'statusMetaboxScreen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_history', __('Recent donations', 'leyka'), array($this, 'historyMetaboxScreen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_campaigns', __('Recent campaings', 'leyka'), array($this, 'campaignsMetaboxScreen'), 'toplevel_page_leyka', 'normal');?>

		<div class="wrap">
            <h2><?php _e('Leyka Dashboard', 'leyka');?></h2>
            <div class="metabox-holder" id="leyka-widgets">
                <div class="postbox-container" id="postbox-container-1">
                    <?php do_meta_boxes('toplevel_page_leyka', 'normal', null);?>
                </div>
                <div class="postbox-container" id="postbox-container-2">
                    <?php $this->dashboardSidebarScreen();?>
                </div>
		</div>
	<?php
	}

    public function guideMetaboxScreen() {

		$row['step_1'] = array(
			'txt'    => __('Fill in information about your organisation', 'leyka'),
			'action' => leyka_are_settings_complete('beneficiary') ? false : admin_url('admin.php?page=leyka_settings&stage=beneficiary'),
			'docs'   => 'https://leyka.te-st.ru/docs/nastrojka-lejki/'
		);
		$row['step_2'] = array(
			'txt'    => __('Set up at least one payment gateway - bank order, for example', 'leyka'),
			'action' => leyka_is_min_payment_settings_complete() ?
                false : admin_url('admin.php?page=leyka_settings&stage=payment'),
			'docs'   => 'https://leyka.te-st.ru/docs/nastrojka-lejki-vkladka-2-platezhnye-optsii/'
		);
		$row['step_3'] = array(
			'txt'    => __('Create and publsih your first campaign', 'leyka'),
			'action' => leyka_is_campaign_published() ?
                false : admin_url('post-new.php?post_type='.Leyka_Campaign_Management::$post_type),
			'docs'   => 'https://leyka.te-st.ru/docs/sozdanie-kampanii/'
		);

		if(current_theme_supports('widgets')) {
			$row['step_4'] = array(
				'txt'    => __('Display campaign and donation information on your site using widgets', 'leyka'),
				'action' => leyka_is_widget_active() ? false : admin_url('widgets.php'),
				'docs'   => 'https://leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/'
			);
		} elseif(current_theme_supports('menus')) {
			$row['step_4'] = array(
				'txt'    => __('Display campaign\'s link on your site using menus', 'leyka'),
				'action' => leyka_is_campaign_link_in_menu() ? false : admin_url('nav-menus.php'),
				'docs'   => 'https://leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/'
			);
		}?>

	<table class="leyka-guide-table">		
		<tbody>
		<?php $count = 0;
			foreach($row as $key => $obj) { $count++;?>

			<tr class="<?php echo esc_attr($key);?>">
				<td class="count"><?php echo $count;?>.</td>
				<td class="step"><?php echo $obj['txt'];?></td>
				<?php if($obj['action']) {?>
				<td class="action"><a href="<?php echo esc_url($obj['action']);?>"><?php _e('Set up', 'leyka');?></a></td>
				<td class="docs"><a href="<?php echo esc_url($obj['docs']);?>" title="<?php esc_attr_e('Additional information on the plugin website', 'leyka');?>" target="_blank"><span class="dashicons dashicons-editor-help"></span></a></td>
				<?php } else {?>
				<td class="action complete"><span><?php _e('Complete', 'leyka');?></span></td>
				<?php }?>
			</tr>
		<?php }?>
		</tbody>
	</table>
    <?php
    }

	public function statusMetaboxScreen(){

		$tabs = Leyka_Options_Allocator::get_instance()->get_tabs();
		if($tabs) {?>

		<table class="leyka-widget-table status">
		<thead>
			<tr>
			<th class="type"><?php _e('Settings type', 'leyka');?></th>			
			<th class="status">&nbsp;</th>
			<th class="details">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
			foreach($tabs as $id => $label) {
				$url = admin_url("admin.php?page=leyka_settings&stage=$id");
				$description = apply_filters('leyka_settings_tabs_description',
					array(
						'beneficiary' => __('Banking and legal information about your organisation', 'leyka'),
						'payment' => __('Payment method\' settings for all you payment forms', 'leyka'),
						'currency' => __('Selection of currencies supported in the system', 'leyka'),
						'email' => __('Gratification email to donor and staff notification notices', 'leyka'),
						'view' => __('Settings for frontend elements, like donation form templates', 'leyka'),
						'additional' => __('Various template tweaks (advanced)', 'leyka'),
					)
				);?>

			<tr>
				<td><?php echo $label;?></td>
				<td><em><?php echo empty($description[$id]) ? '-' : $description[$id];?></em></td>
				<td><a href="<?php echo $url;?>"><?php _e('Edit', 'leyka');?></a></td>
			</tr>

		<?php }?>
		</tbody>
		</table>		
		<?php }

	}

	public function historyMetaboxScreen() {
		
		$query = new WP_Query(array(
			'post_type' => Leyka_Donation_Management::$post_type,
			'post_status' => 'any',
			'posts_per_page' => 5,
		));

		if($query->have_posts()) {?>

		<table class="leyka-widget-table history">
		<thead>
			<tr>
                <th class="date"><?php _e('Date', 'leyka');?></th>
                <th class="title"><?php _e('Purpose', 'leyka');?></th>
                <th class="donor"><?php _e('Donor', 'leyka');?></th>
                <th class="amount"><?php _e('Amount', 'leyka');?></th>
                <th class="status"><?php _e('Status', 'leyka');?></th>
                <th class="details">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($query->posts as $cp) {

            $donation = new Leyka_Donation($cp);
            $url = get_edit_post_link($donation->ID);?>

            <tr>
                <td><?php echo $donation->date;?></td>
                <td><?php echo $donation->title;?></td>
                <td>
                    <?php echo ($donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka'))
                        .($donation->donor_email ? ' ('.$donation->donor_email.')' : '');?>
                </td>
                <td><?php echo $donation->amount.' '.$donation->currency_label;?></td>
                <td><?php echo $donation->status_label;?></td>
                <td><a href="<?php echo esc_url($url); ?>"><?php _e('Details', 'leyka'); ?></a></td>
            </tr>

		<?php }?>

		</tbody>
		</table>

		<?php } else {?>
			<p class="empty-notice"><?php _e('You have not received any donations yet', 'leyka');?></p>
		<?php }

	}

	public function campaignsMetaboxScreen() {
		
		$query = new WP_Query(array(
			'post_type' => Leyka_Campaign_Management::$post_type,
			'post_status' => 'any',
			'posts_per_page' => 5,
		));
		
		if($query->have_posts()) {?>

		<table class="leyka-widget-table campaigns">
		<thead>
			<tr>
			<th class="title"><?php _e('Title', 'leyka');?></th>
			<th class="payment"><?php _e('Purpose', 'leyka');?></th>			
			<th class="details">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
			foreach($query->posts as $cp){
				$camp = new Leyka_Campaign($cp);
				$url = get_edit_post_link($camp->ID);?>

		<tr>
			<td><?php echo $camp->title;?></td>
			<td><?php echo $camp->payment_title; ?></td>			
			<td><a href="<?php echo esc_url($url); ?>"><?php _e('Edit', 'leyka'); ?></a></td>
		</tr>
		<?php }?>
		</tbody>
		</table>

		<?php } else {
			$url = admin_url('post-new.php?post_type=leyka_campaign');?>

			<p class="empty-notice"><?php _e('You don\'t have any campaign yet.', 'leyka');?> <a href="<?php echo esc_url($url); ?>"><?php _e('Create first one', 'leyka'); ?></a></p>
		<?php
		}
	}

	public function dashboardSidebarScreen() {?>

		<div id="leyka-card">
            <h2><i></i><?php _e('Leyka', 'leyka');?></h2>
            <p><?php _e('Leyka is a simple donations management system', 'leyka');?></p>
            <p>
                <?php _e('Developed by <a href="http://te-st.ru/" target="_blank" rel="noopener noreferrer">Teplitsa of social technologies</a>', 'leyka');?>
            </p>
            <p class="te-st">
                <img src="//leyka.te-st.ru/wp-content/uploads/assets/tst-logo.svg" onerror="this.onerror=null;this.src='//leyka.te-st.ru/wp-content/uploads/assets/tst-logo.png'">
            </p>
            <ul class="leyka-ref-links">
                <li><a href="https://leyka.te-st.ru" target='_blank' rel="noopener noreferrer"><?php _e('Plugin website', 'leyka');?></a></li>
                <li><a href="https://leyka.te-st.ru/instruction/" target='_blank' rel="noopener noreferrer"><?php _e('Documentation', 'leyka');?></a></li>
                <li><a href="<?php echo admin_url('admin.php?page=leyka_feedback');?>"><?php _e('Ask a question', 'leyka');?></a></li>
                <li><a href="https://github.com/Teplitsa/Leyka/issues/new" target='_blank' rel="noopener noreferrer"><?php _e('Create issue at GitHub', 'leyka');?></a></li>
            </ul>
		</div>

	<?php leyka_itv_info_widget();
	}
	
	public function isV3SettingsPage($stage) {
		return in_array($stage, array('payment', 'email', 'beneficiary', 'technical', 'view', 'additional'));
	}

	public function isSeparateFormsStage($stage) {
		return in_array($stage, array('email', 'beneficiary', 'technical', 'view', 'additional'));
	}

	/** Displaying settings **/
	public function settingsScreen() {

		if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        $current_stage = $this->get_current_settings_tab();
		$is_separate_sections_forms = $this->isSeparateFormsStage($current_stage);

		require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php'); // Basic Controller class
        require_once(LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-admin-template-tags.php');

		do_action('leyka_pre_settings_actions', $current_stage);

        // Process settings change:
	    if( (!empty($_POST["leyka_settings_{$current_stage}_submit"])
			|| !empty($_POST["leyka_settings_stage-{$current_stage}_submit"]))
	        /** @todo Find what's wrong with the nonce check below: */
//	        && wp_verify_nonce('_leyka_nonce', "leyka_settings_{$current_stage}")
        ) {
			do_action('leyka_settings_submit', $current_stage);
		}?>

		<div class="wrap leyka-admin leyka-settings-page">

		    <h1><?php esc_html_e('Leyka settings', 'leyka');?></h1>

            <h2 class="nav-tab-wrapper"><?php echo $this->settingsTabsMenu();?></h2>

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

						if($is_separate_sections_forms) { ?>
                        </form>
						<?php }
                    }

                    do_action("leyka_settings_post_{$current_stage}_fields");?>

                    <?php if(!$is_separate_sections_forms) {?>
					<p class="submit">
                        <input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php _e('Save settings', 'leyka');?>" class="button-primary">
                    </p>
					<?php }
                }?>

				<?php if(!$is_separate_sections_forms) {?>
                </form>
				<?php }?>
            </div>
			
			<?php include(LEYKA_PLUGIN_DIR.'inc/settings-fields-templates/leyka-helpchat.php');?>

		</div>

	<?php
	}

	public function settingsNewScreen() {

	    if(empty($_GET['screen']) || count(explode('-', $_GET['screen'])) < 2) {

	        $this->settingsScreen();
	        return;

	    }

	    $screen_full_id = explode('-', $_GET['screen']);

	    // Normally, we'd constuct settings view based on
	    // - view type ([0], e.g. 'wizard' or 'control_panel')
	    // - settings area given ([1], e.g. 'init').

        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-factory.php');

        try {

            Leyka_Settings_Factory::get_instance()->getRender($screen_full_id[0])
                ->setController(Leyka_Settings_Factory::get_instance()->getController($screen_full_id[1]))
                ->renderPage();

        } catch(Exception $ex) {
            echo '<pre>'.print_r('Settings page error (code '.$ex->getCode().'): '.$ex->getMessage(), 1).'</pre>';
        }

	}

	public function get_default_settings_tab() {
		return apply_filters('leyka_default_settings_tab', 'payment');
	}

	public function get_current_settings_tab() {
		return empty($_GET['stage']) ? $this->get_default_settings_tab() : trim($_GET['stage']);
	}

	public function settingsTabsMenu() {

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
    public function feedbackScreen() {

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
				<img id="feedback-loader" style="display: none;" src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif';?>" />
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
			<div class="feedback-sidebar"><?php leyka_itv_info_widget();?></div>
		</div>
		
	</div>
    <?php }

    /** Feedback page processing */
    public function ajaxSendFeedback() {

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
	
	/** CSS/JS **/
	public function loadFrontendScripts() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', array(), LEYKA_VERSION);

		$screen = get_current_screen();
		if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
			return;
        }

        // Base admin area js/css:
        $leyka_admin_new = (isset($_GET['screen']) && count(explode('-', $_GET['screen'])) >= 2) // New settings pages (from v3.0)
            || (
                isset($_GET['page'])
                && $_GET['page'] === 'leyka_settings'
                && (empty($_GET['stage']) || $this->isV3SettingsPage($_GET['stage']))
                && empty($_GET['old'])
            );

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
            'email_invalid' => __('You have entered an invalid email', 'leyka'),
            'common_error_message' => esc_html__('Error while saving the data', 'leyka'),
			'error_message' => esc_html__('Error!', 'leyka'),
			'disconnect_stats' => esc_html__('Disconnect statistics', 'leyka'),
        ));

        if($leyka_admin_new) {

            wp_enqueue_script('leyka-settings', LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js', array('jquery',), LEYKA_VERSION, true);
            wp_localize_script('leyka-settings', 'leyka', $js_data);

        } else {

            wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'js/admin.js', $dependencies, LEYKA_VERSION, true);
			wp_enqueue_script('leyka-admin-helpchat', LEYKA_PLUGIN_BASE_URL.'js/settings-helpchat.js', $dependencies, LEYKA_VERSION, true);
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
                'email_invalid' => __('You have entered an invalid email', 'leyka'),
                'amount_incorrect' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
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

            <a href="<?php echo get_edit_post_link($campaign->id);?>" class="inline-action inline-edit-slug">Редактировать</a>

            <span class="inline-edit-slug-form" data-slug-original="<?php echo $campaign_permalink_parts[1];?>" data-campaign-id="<?php echo $campaign->id;?>" data-nonce="<?php echo wp_create_nonce('leyka-edit-campaign-slug');?>" style="display: none;">
                <input type="text" class="leyka-slug-field inline-input" value="<?php echo $campaign_permalink_parts[1];?>">
                <span class="slug-submit-buttons">
                    <button class="inline-submit"><?php esc_html_e('OK');?></button>
                    <button class="inline-reset"><?php esc_html_e('Cancel');?></button>
                </span>
            </span>

        <?php } else {?>

            <span class="base-url"><?php echo $campaign_permalink_full;?></span>
            <a href="<?php echo admin_url('options-permalink.php');?>" class="permalink-action" target="_blank">Включить постоянные ссылки</a>

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
		leyka_sync_plugin_stats_option();
	}
	add_action("leyka_after_save_option-send_plugin_stats", "leyka_sync_plugin_stats_option_action", 10, 2);
}