<?php if( !defined('WPINC') ) die;

/**
 * Leyka Admin setup
 **/

class Leyka_Admin_Setup {

	private static $_instance = null;

    public static function get_instance() {

        if( !self::$_instance ) { // If the single instance hasn't been set, set it now
            self::$_instance = new self;
        }

        return self::$_instance;
    }

	private function __construct() {

		add_action('admin_menu', array($this, 'admin_menu_setup'), 9); // Add the options page and menu item

		add_action('admin_enqueue_scripts', array($this, 'enqueue_cssjs')); // Load admin style sheet and JavaScript

        /** Remove needless metaboxes */
        add_action('admin_init', array($this, 'remove_seo')); // Remove needless columns and metaboxes

        add_action('wp_ajax_leyka_send_feedback', array($this, 'ajax_send_feedback'));

        add_filter('plugin_row_meta', array($this, 'set_plugin_meta'), 10, 2);

        // Link in plugin actions:
		add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, array($this, 'add_settings_link'));

        // Metaboxes support where it is needed:
        add_action('leyka_pre_settings_actions', array($this, 'leyka_metaboxes_full_support'));
        add_action('leyka_dashboard_actions', array($this, 'leyka_metaboxes_full_support'));
    }

    public function set_plugin_meta($links, $file) {

        if($file == LEYKA_PLUGIN_INNER_SHORT_NAME) {
            $links[] = '<a href="https://github.com/Teplitsa/Leyka/">GitHub</a>';
        }

        return $links;
    }

    // A little function to support the full abilities of the metaboxes on any plugin's page:
    public function leyka_metaboxes_full_support($current_stage = false) {?>

        <!-- Metaboxes reordering and folding support -->
        <form style="display:none" method="get" action="#">
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
        </form>
    <?php }

    public function remove_seo() {

        // WordPress SEO by Yoast's metabox on donation editing page:
        if( !empty($GLOBALS['wpseo_metabox']) ) {

            $seo_titles_options = get_option('wpseo_titles');
            $seo_titles_options['hideeditbox-leyka_donation'] = true;

            update_option('wpseo_titles', $seo_titles_options);
        }
    }

    /*
    function display_custom_quickedit_donation($column_name, $post_type) {
        if($post_type != Leyka_Donation_Management::$post_type)
            return;

        static $printNonce = TRUE;
        if ( $printNonce ) {
            $printNonce = FALSE;
            wp_nonce_field( plugin_basename( __FILE__ ), 'book_edit_nonce' );
        }

        ?>
        <fieldset class="inline-edit-col-right inline-edit-book">
            <div class="inline-edit-col column-<?php echo $column_name;?>">
                <label class="inline-edit-group">
                <?php
                    switch($column_name) {
                        case 'donor':
                            ?><span class="title">Donor</span><input name="donor" /><?php
                            break;
//                        case '':
//                            ?><!--<span class="title">In Print</span><input name="inprint" type="checkbox" />--><?php
//                            break;
                    }
                ?>
                </label>
            </div>
        </fieldset>
    <?php
    }
    */

	/** Admin Menu **/
    public function admin_menu_setup() {

        // Leyka menu root:
        add_menu_page(__('Leyka Dashboard', 'leyka'), __('Leyka', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        // Dashboard:
        add_submenu_page('leyka', __('Leyka Dashboard', 'leyka'), __('Dashboard', 'leyka'), 'leyka_manage_donations', 'leyka', array($this, 'dashboard_screen'));

        // Donations:
        add_submenu_page('leyka', __('Donations', 'leyka'), __('Donations', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Donation_Management::$post_type);

        // Campigns:
        add_submenu_page('leyka', __('All Campaigns', 'leyka'), __('All Campaigns', 'leyka'), 'leyka_manage_donations', 'edit.php?post_type='.Leyka_Campaign_Management::$post_type);

        // New campaign:
        add_submenu_page('leyka', __('New campaign', 'leyka'), __('New campaign', 'leyka'), 'leyka_manage_donations', 'post-new.php?post_type='.Leyka_Campaign_Management::$post_type);

        // Settings:
        add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), 'leyka_manage_options', 'leyka_settings', array($this, 'settings_screen'));

        // Feedback:
        add_submenu_page('leyka', __('Connect to us', 'leyka'), __('Feedback', 'leyka'), 'leyka_manage_donations', 'leyka_feedback', array($this, 'feedback_screen'));

        do_action('leyka_admin_menu_setup');

        global $submenu;

        if( !empty($submenu['leyka']) )
            $submenu['leyka'] = apply_filters('leyka_admin_menu_order', $submenu['leyka']);
    }
	
	/** Settings link in plugin list table **/
	public function add_settings_link( $links ) {
		$settings_link = '<a href="'.admin_url('admin.php?page=leyka_settings').'">' . __( 'Settings', 'leyka' ) . '</a>';
		array_push( $links, $settings_link );
		
		return $links;
	}

	/** Displaying dashboard **/
	public function dashboard_screen() {

		if( !current_user_can('leyka_manage_donations') ) {
            wp_die(__('Sorry, but you do not have permissions to access this page.', 'leyka'));
        }

		do_action('leyka_dashboard_actions'); // Collapsible

		add_meta_box('leyka_guide', __('First steps', 'leyka'), array($this, 'guide_metabox_screen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_status', __('Settings', 'leyka'), array($this, 'status_metabox_screen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_history', __('Recent donations', 'leyka'), array($this, 'history_metabox_screen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_campaigns', __('Recent campaings', 'leyka'), array($this, 'campaigns_metabox_screen'), 'toplevel_page_leyka', 'normal');?>

		<div class="wrap">
            <h2><?php _e('Leyka Dashboard', 'leyka');?></h2>

            <div class="metabox-holder" id="leyka-widgets">
                <div class="postbox-container" id="postbox-container-1">
                    <?php do_meta_boxes('toplevel_page_leyka', 'normal', null);?>
                </div>
                <div class="postbox-container" id="postbox-container-2">
                    <?php $this->dashboard_sidebar_screen();?>
                </div>
		</div><!-- close .wrap -->
	<?php
	}

    public function guide_metabox_screen() {

		// Content:
		$row['step_1'] = array(
			'txt'    => __('Fill in information about your organisation', 'leyka'),
			'action' => leyka_settings_complete('beneficiary') ? false : admin_url('admin.php?page=leyka_settings'),
			'docs'   => 'https://leyka.te-st.ru/docs/nastrojka-lejki/'
		);
		$row['step_2'] = array(
			'txt'    => __('Set up at least one payment gateway - bank order, for example', 'leyka'),
			'action' => leyka_min_payment_settings_complete() ?
                false : admin_url('admin.php?page=leyka_settings&stage=payment'),
			'docs'   => 'https://leyka.te-st.ru/docs/nastrojka-lejki-vkladka-2-platezhnye-optsii/'
		);
		$row['step_3'] = array(
			'txt'    => __('Create and publsih your first campaign', 'leyka'),
			'action' => leyka_campaign_published() ?
                false : admin_url('post-new.php?post_type='.Leyka_Campaign_Management::$post_type),
			'docs'   => 'https://leyka.te-st.ru/docs/sozdanie-kampanii/'
		);

		if(current_theme_supports('widgets')) {
			$row['step_4'] = array(
				'txt'    => __('Display campaign and donation information on your site using widgets', 'leyka'),
				'action' => admin_url('widgets.php'),
				'docs'   => 'https://leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/'
			);
		} elseif(current_theme_supports('menus')) {
			$row['step_4'] = array(
				'txt'    => __('Display campaign\'s link on your site using menus', 'leyka'),
				'action' => admin_url('nav-menus.php'),
				'docs'   => 'https://leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/'
			);
		}?>
	<table class="leyka-guide-table">		
		<tbody>
		<?php
			$count = 0;
			foreach($row as $key => $obj){
				$count++;
			?>
			<tr class="<?php echo esc_attr($key);?>">
				<td class="count"><?php echo $count;?>.</td>
				<td class="step"><?php echo $obj['txt'];?></td>
				<?php if($obj['action']) { ?>
					<td class="action"><a href="<?php echo esc_url($obj['action']);?>"><?php _e('Set up', 'leyka');?></a></td>
					<td class="docs"><a href="<?php echo esc_url($obj['docs']);?>" title="<?php esc_attr_e('Additional information on the plugin website', 'leyka');?>" target="_blank"><span class="dashicons dashicons-editor-help"></span></a></td>
				<?php } else { ?>
					<td class="action complete"><span><?php _e('Complete', 'leyka');?></span></td>
				<?php } ?>
				
			</tr>
			<?php
			}
		?>
		</tbody>
	</table>
    <?php
    }

	public function status_metabox_screen(){
		
		$tabs = Leyka_Options_Allocator::instance()->get_tabs();
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
	
	public function history_metabox_screen() {
		
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
		<?php
			foreach($query->posts as $cp) {
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

	public function campaigns_metabox_screen() {
		
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

	public function dashboard_sidebar_screen() {?>

		<div id="leyka-card">
            <h2><i></i><?php _e('Leyka', 'leyka');?></h2>
            <p><?php _e('Leyka is a simple donations management system', 'leyka');?></p>
            <p>
                <?php _e('Developed by <a href="http://te-st.ru/" target="_blank">Teplitsa of social technologies</a>', 'leyka');?>
            </p>
            <p class="te-st">
                <img src="http://leyka.te-st.ru/wp-content/uploads/assets/tst-logo.svg" onerror="this.onerror=null;this.src='http://leyka.te-st.ru/wp-content/uploads/assets/tst-logo.png'">
            </p>
            <ul class="leyka-ref-links">
                <li><a href="https://leyka.te-st.ru" target='_blank'><?php _e('Plugin website', 'leyka');?></a></li>
                <li><a href="https://leyka.te-st.ru/instruction/" target='_blank'><?php _e('Documentation', 'leyka');?></a></li>
                <li><a href="<?php echo admin_url('admin.php?page=leyka_feedback');?>"><?php _e('Ask a question', 'leyka');?></a></li>
                <li><a href="https://github.com/Teplitsa/Leyka/issues/new" target='_blank'><?php _e('Create issue at GitHub', 'leyka');?></a></li>
            </ul>
		</div>

	<?php leyka_itv_info_widget();
	}

	/** Displaying settings **/
	public function settings_screen() {

		/* Capability test */
		if( !current_user_can('leyka_manage_options') ) {
            wp_die(__('You do not have permissions to access this page.', 'leyka'));
        }

        $current_stage = $this->get_current_settings_tab();

        require(LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php');

        /* Page actions */
		do_action('leyka_pre_settings_actions', $current_stage);

        /** Process settings change */
	    if(
            !empty($_POST["leyka_settings_{$current_stage}_submit"]) /*&&
            wp_verify_nonce('_leyka_nonce', "leyka_settings_{$current_stage}")*/
        ) {
			do_action("leyka_settings_{$current_stage}_submit", $current_stage);
		}?>

		<div class="wrap">

		<h2 class="nav-tab-wrapper"><?php echo $this->settings_tabs_menu();?></h2>

		<div id="tab-container">
			<form method="post" action="<?php echo admin_url(add_query_arg('stage', $current_stage, 'admin.php?page=leyka_settings'));?>" id="leyka-settings-form">

            <?php wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');

            if(file_exists(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-$current_stage.php")) {
                require(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-$current_stage.php");
            } else {

                do_action("leyka_settings_pre_{$current_stage}_fields");

                foreach(leyka_opt_alloc()->get_tab_options($current_stage) as $option) { // Render each option/section

                    if(is_array($option) && !empty($option['section'])) {
                        do_action('leyka_render_section', $option['section']);
                    } else { // is this case possible?

                        $option_info = leyka_options()->get_info_of($option);
                        do_action("leyka_render_{$option_info['type']}", $option, $option_info);
                    }
                }

                do_action("leyka_settings_post_{$current_stage}_fields");?>

                <p class="submit">
                    <input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php _e('Save settings', 'leyka'); ?>" class="button-primary" />
                </p>
            <?php }?>

			</form>
<!--            --><?php //do_action("leyka_settings_post_{$current_stage}_form");?>
		</div>

		</div><!-- close .wrap -->
	<?php 
	}

	public function get_default_settings_tab() {
		return apply_filters('leyka_default_settings_tab', 'beneficiary');
	}
	
	public function get_current_settings_tab() {
		return empty($_GET['stage']) ? $this->get_default_settings_tab() : trim($_GET['stage']);
	}

    /** Settings tabs menu **/
	public function settings_tabs_menu(){

		$tabs = Leyka_Options_Allocator::instance()->get_tabs();
		$default_tab = $this->get_default_settings_tab();
		$current_tab = $this->get_current_settings_tab();
		$base_url = 'admin.php?page=leyka_settings';
		$out = '';

		foreach($tabs as $id => $label) {

			$css = ($current_tab == $id) ? 'nav-tab nav-tab-active' : 'nav-tab';
			$url = ($default_tab == $id) ? $base_url : add_query_arg('stage', $id, $base_url);
			
			$out .= "<a href='{$url}' class='{$css}'>{$label}</a>";		
		}

		return $out;
	}

    /** Displaying feedback **/
    public function feedback_screen(){

        if( !current_user_can('leyka_manage_donations') )
            wp_die(__('You do not have permissions to access this page.', 'leyka'));

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
    public function ajax_send_feedback() {

        if( !wp_verify_nonce($_POST['nonce'], 'leyka_feedback_sending') ) {
            die('1');
        }

        $_POST['topic'] = htmlentities(trim($_POST['topic']), ENT_COMPAT, 'UTF-8');
        $_POST['name'] = htmlentities(trim($_POST['name']), ENT_COMPAT, 'UTF-8');
        $_POST['email'] = htmlentities(trim($_POST['email']), ENT_COMPAT, 'UTF-8');
        $_POST['text'] = htmlentities(trim($_POST['text']), ENT_COMPAT, 'UTF-8');

        if(
            !$_POST['name'] || !$_POST['email'] || !$_POST['text'] ||
            !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
        ) {
            die('2');
        }

        if( !function_exists('set_html_content_type') ) {
            function set_html_content_type(){ return 'text/html'; }
        }
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $res = true;
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
                <strong>Браузер пользователя:</strong> %s",
                    $_POST['topic'], $_POST['name'], $_POST['email'], nl2br($_POST['text']),
                    home_url(), get_bloginfo('name'), $_SERVER['SERVER_ADDR'],
                    get_bloginfo('version'), LEYKA_VERSION, get_bloginfo('admin_email'),
                    get_bloginfo('language'), get_bloginfo('charset'),
                    $_SERVER['SERVER_SOFTWARE'], $_SERVER['HTTP_USER_AGENT']
                ),
                array('From: '.get_bloginfo('name').' <no_reply@leyka.te-st.ru>',)
            );
        }

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'set_html_content_type');

        die($res ? '0' : '3');
    }

	/** CSS/JS **/		
	public function enqueue_cssjs() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', array(), LEYKA_VERSION);

		$screen = get_current_screen();
		if(false === strpos($screen->base, 'leyka') && false === strpos($screen->id, 'leyka'))
			return;

        // Base admin area js/css:
        wp_enqueue_style('leyka-admin', LEYKA_PLUGIN_BASE_URL.'css/admin.css', array(), LEYKA_VERSION);

        $current_screen = get_current_screen();
        $dependencies = array('jquery',);

        if($current_screen->id == 'toplevel_page_leyka') {
            $dependencies[] = 'postbox';
        }
        if(stristr($current_screen->id, '_page_leyka_settings') !== false) {

            $dependencies[] = 'postbox';
            $dependencies[] = 'jquery-ui-accordion';
            $dependencies[] = 'jquery-ui-sortable';

            wp_enqueue_script(
                'leyka-sticky',
                LEYKA_PLUGIN_BASE_URL.'js/jquery.sticky.js',
                $dependencies,
                LEYKA_VERSION, true
            );
            $dependencies[] = 'leyka-sticky';
        }
        if($current_screen->post_type == Leyka_Donation_Management::$post_type) {
            $dependencies[] = 'jquery-ui-autocomplete';
        }

        wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'js/admin.js', $dependencies, LEYKA_VERSION, true);

        $js_local = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajax_loader_url' => LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif',
            'field_required' => __('This field is required to be filled', 'leyka'),
            'email_invalid' => __('You have entered an invalid email', 'leyka'),
//            '' => __('', 'leyka'),
        );
        wp_localize_script('leyka-admin', 'leyka', $js_local);

        // Campaign editing page:
        if($screen->post_type == Leyka_Campaign_Management::$post_type && $screen->base == 'post' && !$screen->action) {

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
            wp_localize_script('leyka-admin-edit-campaign', 'leyka_dt', $js_local + array(
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
        if($screen->post_type == Leyka_Donation_Management::$post_type && $screen->base == 'post') {

            $locale = get_locale();
            if($locale != 'en_US')
                wp_enqueue_script(
                    'jquery-ui-datepicker-locale',
                    LEYKA_PLUGIN_BASE_URL."js/jq-datepicker-locales/$locale.js",
                    array('jquery-ui-datepicker'), LEYKA_VERSION, true
                );

            wp_enqueue_script(
                'leyka-admin-add-edit-donation',
                LEYKA_PLUGIN_BASE_URL.'js/admin-add-edit-donation.js',
                array('jquery-ui-datepicker-locale'), LEYKA_VERSION, true
            );
            wp_localize_script('leyka-admin-add-edit-donation', 'leyka', $js_local + array(
                'add_donation_button_text' => __('Add the donation', 'leyka'),
                'field_required' => __('This field is required to be filled', 'leyka'),
                'campaign_required' => __('Selecting a campaign is required', 'leyka'),
                'email_invalid' => __('You have entered an invalid email', 'leyka'),
                'amount_incorrect' => __('The amount must be filled with non-zero, non-negative number', 'leyka'),
                'donation_source_required' => __('Please, set one of a payment methods or just type a few words to describe a source for this donation', 'leyka'),
//            '' => __('', 'leyka'),
            ));
        }
	}
} //class end