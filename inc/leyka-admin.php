<?php
/**
 * Leyka Admin setup
 **/

class Leyka_Admin_Setup {

	private static $instance = null;

	private $_options_capability = 'manage_options';
//	private $_manager_role = 'editor';

	private function __construct() {
		// Add the options page and menu item.
		add_action('admin_menu', array($this, 'admin_menu_setup'), 9);
			
		// Load admin style sheet and JavaScript.
		add_action('admin_enqueue_scripts', array($this, 'enqueue_cssjs'));

        /** Reorder Leyka submenu */
        add_filter('custom_menu_order', array($this, 'reorder_submenu'));

        /** Custom update message */
//        add_action(
//            'in_plugin_update_message-'.LEYKA_PLUGIN_DIR_NAME.'/'.LEYKA_PLUGIN_BASE_FILE,
//            function($plugin_data, $r){

//                echo '<pre>' . print_r($plugin_data, TRUE) . '</pre>';
//                echo '<pre>' . print_r($r, TRUE) . '</pre>';
//
//                echo 'Hello World';

//        }, 10, 2);
    }

    function reorder_submenu($menu_order) {
        global $submenu;

        if(current_user_can($this->_options_capability)) {

            usort($submenu['leyka'], function($a, $b){

//                if($b[0] == __('Donations', 'leyka'))
//                    echo '<pre>' . print_r(1111111111, 1) . '</pre>';
//                echo '<pre>' . print_r($b, 1) . '</pre>';

                if($a[0] == __('Dashboard', 'leyka'))
                    return -1;
                if($b[0] == __('Dashboard', 'leyka'))
                    return 1;

                if($a[0] == __('Settings', 'leyka'))
                    return 1;
                if($b[0] == __('Settings', 'leyka'))
                    return -1;

                if($a[0] == __('Donations', 'leyka'))
                    return 1;
                if($b[0] == __('Donations', 'leyka'))
                    return $a[0] == __('Dashboard', 'leyka') ? -1 : 1;

                if($a[0] == __('New campaign', 'leyka'))
                    return $b[0] == __('Settings', 'leyka') ? -1 : 1;

//                echo '<pre>' . print_r($a, 1) . '</pre>';
//                echo '<pre>' . print_r($b, 1) . '</pre>';
            });

            $new_order = $submenu['leyka'];
        } else {

            /** Remove Settings from plugin submenu: */
            $new_order = array_filter($submenu['leyka'], function($menu_item){
                return $menu_item[0] != __('Settings', 'leyka');
            });
        }

        $submenu['leyka'] = $new_order;

        return $menu_order;
    }

    /*
    function display_custom_quickedit_donation($column_name, $post_type) {
        if($post_type != 'leyka_donation')
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

	public static function get_instance() {

		// If the single instance hasn't been set, set it now:
		if( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	/** Admin Menu **/
	function admin_menu_setup() {

		// Leyka menu root
		add_menu_page(__('Leyka Dashboard', 'leyka'), __('Leyka', 'leyka'), $this->_options_capability, 'leyka', array($this, 'dashboard_screen'));

		// Dashboard
		add_submenu_page('leyka', __('Leyka Dashboard', 'leyka'), __('Dashboard', 'leyka'), $this->_options_capability, 'leyka', array($this, 'dashboard_screen'));
		
		// Settings
		add_submenu_page('leyka', __('Leyka Settings', 'leyka'), __('Settings', 'leyka'), $this->_options_capability, 'leyka_settings', array($this, 'settings_screen'));
				
    }

	/** Displaying dashboard **/
	function dashboard_screen(){

//		if( !leyka_current_user_has_role($this->_manager_role) )
//            wp_die(__('Sorry, but you do not have permissions to access this page.', 'leyka'));

		/* page actions */
		do_action('leyka_dashboard_actions');
		
		$page_slug = 'leyka';
		$page_title = __('Leyka Dashboard', 'leyka');
		$parent_slug = 'admin.php';
		$faction = "{$parent_slug}?page={$page_slug}";
		
		/* @to-do: make metaboxes collapsable */
		add_meta_box('leyka_status', __('Leyka\'s settings', 'leyka'), array($this, 'status_metabox_screen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_history', __('Recent donations', 'leyka'), array($this, 'history_metabox_screen'), 'toplevel_page_leyka', 'normal');
		add_meta_box('leyka_campaigns', __('Recent campaings', 'leyka'), array($this, 'campaigns_metabox_screen'), 'toplevel_page_leyka', 'normal');
	?>
	
		<div class="wrap">
		<h2><?php echo $page_title;?></h2>
			
		<div class="metabox-holder" id="leyka-widgets">
		<div class="postbox-container" id="postbox-container-1">
			<?php //var_dump(get_current_screen());
			do_meta_boxes('toplevel_page_leyka', 'normal', 'leyka_status'); ?> 
		</div>
		<div class="postbox-container" id="postbox-container-2">
			<?php $this->dashboard_sidebar_screen();?>
		</div>
		</div><!-- close .wrap -->
	<?php
	}
	
	function status_metabox_screen(){
		
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
			foreach($tabs as $id => $label){
				$url = admin_url("admin.php?page=leyka_settings&stage=".$id);
				$description = apply_filters('leyka_settings_tabs_description',
					array(
						'beneficiary' => __('Banking and legal information about your organisation', 'leyka'),
						'payment' => __('Payment method\' settings for all you payment forms', 'leyka'),
						'currency' => __('Selection of currencies supported in the system', 'leyka'),
						'email' => __('Gratification email to donor and staff notification notices', 'leyka'),
						'additional' => __('Various template tweaks (advanced)', 'leyka'),
					)
				);?>

			<tr>
				<td><?php echo $label; ?></td>
				<td><em><?php echo (isset($description[$id])) ? $description[$id] : '-'; ?></em></td>
				<td><a href="<?php echo $url;?>"><?php _e('Edit', 'leyka');?></a></td>
			</tr>

		<?php }?>
		</tbody>
		</table>		
		<?php	
		}
	}
	
	function history_metabox_screen() {
		
		$query = new WP_Query(array(
			'post_type' => 'leyka_donation',
			'post_status' => 'any',
			'posts_per_page' => 5
		));
		
		if($query->have_posts()){
		?>
		<table class="leyka-widget-table history">
		<thead>
			<tr>
			<th class="title"><?php _e('Date', 'leyka');?></th>
			<th class="title"><?php _e('Purpose', 'leyka');?></th>
			<th class="donor"><?php _e('Donor', 'leyka');?></th>
			<th class="amount"><?php _e('Amount', 'leyka');?></th>
			<th class="details">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
			foreach($query->posts as $cp){
				$donation = new Leyka_Donation($cp);
				$url = get_edit_post_link($donation->ID);
		?>
		<tr>
			<td><?php echo $donation->date;?></td>
			<td><?php echo $donation->title;?></td>
			<td><?php echo $donation->donor_name.' ('.$donation->donor_email.')'; ?></td>
			<td><?php echo $donation->amount.' '.$donation->currency_label;?></td>
			<td><a href="<?php echo esc_url($url); ?>"><?php _e('Details', 'leyka'); ?></a></td>
		</tr>
		<?php	
			}
		?>
		</tbody>
		</table>
		<?php
		}
		else {
		?>
			<p class="empty-notice"><?php _e('You have not received any donations yet', 'leyka');?></p>
		<?php
		}
	}
	
	function campaigns_metabox_screen(){
		
		$query = new WP_Query(array(
			'post_type' => 'leyka_campaign',
			'post_status' => 'any',
			'posts_per_page' => 5
		));
		
		if($query->have_posts()){
		?>
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
				$url = get_edit_post_link($camp->ID);
		?>
		<tr>
			<td><?php echo $camp->title;?></td>
			<td><?php echo $camp->payment_title; ?></td>			
			<td><a href="<?php echo esc_url($url); ?>"><?php _e('Edit', 'leyka'); ?></a></td>
		</tr>
		<?php	
			}
		?>
		</tbody>
		</table>
		<?php
		}
		else {
			$url = admin_url('post-new.php?post_type=leyka_campaign');
		?>
			<p class="empty-notice"><?php _e('You don\'t have any campaign yet.', 'leyka');?> <a href="<?php echo esc_url($url); ?>"><?php _e('Create first one', 'leyka'); ?></a></p>
		<?php
		}
	}
	
	function dashboard_sidebar_screen() {?>

		<div id="leyka-card">
		<h2><i></i><?php _e('Leyka', 'leyka');?></h2>
		<p><?php _e('Leyka is a simple donations management system', 'leyka');?></p>
		<p><?php _e('Developed by <a href="http://te-st.ru/" target="_blank">Teplitsa of social technologies</a>', 'leyka');?></p>
		<p class="te-st"><img src="http://te-st.ru/wp-content/uploads/white-logo-100x50.png" width="100" height="50" /></p>
		<ul class="leyka-ref-links">
			<li><a href="http://leyka.te-st.ru" target='_blank'><?php _e('Plugin website', 'leyka');?></a></li>
			<li><a href="http://leyka.te-st.ru/docs/" target='_blank'><?php _e('Documentation', 'leyka');?></a></li>
			<li><a href="http://leyka.te-st.ru/faq/" target='_blank'><?php _e('Ask a question', 'leyka');?></a></li>
		</ul>
		</div>
	<?php	
	}
	
	
	
	/** Displaying settings **/
	function settings_screen(){
		
		/* Capability test */
		if( !current_user_can($this->_options_capability) )
            wp_die(__('You do not have permissions to access this page.', 'leyka'));

        $current_stage = $this->get_current_settings_tab();

        require(LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-common.php');
        if(file_exists(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-$current_stage.php"))
            require(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-$current_stage.php");

		/* Page actions */
		do_action('leyka_pre_settings_actions');

		$page_slug = 'leyka_settings';
		$page_title = __('Leyka Settings', 'leyka');
		
		$faction = add_query_arg('stage', $current_stage, "admin.php?page={$page_slug}");

        /** Process settings change */
//        echo '<pre>'.print_r((int)!empty($_POST["leyka_settings_{$current_stage}_submit"]), TRUE).'</pre>';
//        echo '<pre>'.print_r((int)wp_verify_nonce('_leyka_nonce', 'test nonce'), TRUE).'</pre>';
//        echo '<pre>'.print_r($_POST, TRUE).'</pre>';
	    if( !empty($_POST["leyka_settings_{$current_stage}_submit"])){
//         && wp_verify_nonce('_leyka_nonce', "leyka_settings_{$current_stage}")
			do_action("leyka_settings_{$current_stage}_submit", $current_stage);
		}
            
	?>

		<div class="wrap">

		<h2 class="nav-tab-wrapper"><?php echo $this->settings_tabs_menu();?></h2>

		<div id="tab-container">
			<form method="post" action="<?php echo admin_url($faction); ?>" id="leyka-settings-form">

            <?php
				wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');

				do_action("leyka_settings_pre_{$current_stage}_fields");

                foreach(leyka_opt_alloc()->get_tab_options($current_stage) as $option) { // Render each option/section

                    if(is_array($option) && !empty($option['section'])) {
                        do_action('leyka_render_section', $option['section']);
						
                    } else { //is this case ever possible ?						
                        $option_info = leyka_options()->get_info_of($option);
                        do_action("leyka_render_{$option_info['type']}", $option, $option_info);
                    }

                }

                do_action("leyka_settings_post_{$current_stage}_fields");
			?>
			<p class="submit">
				<input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php _e('Save settings', 'leyka'); ?>" class="button-primary" />
			</p>
			
			</form>
		</div>

		</div><!-- close .wrap -->
	<?php 
	}

	function get_default_settings_tab(){
		
		return apply_filters('leyka_default_settings_tab', 'beneficiary');
	}
	
	function get_current_settings_tab() {
		
		return empty($_GET['stage']) ? $this->get_default_settings_tab() : trim($_GET['stage']);
	}

	
    /** Settings tabs menu **/
	function settings_tabs_menu(){
		
		$tabs = Leyka_Options_Allocator::instance()->get_tabs();
		$default_tab = $this->get_default_settings_tab();
		$current_tab = $this->get_current_settings_tab();
		$base_url = 'admin.php?page=leyka_settings';
		$out = '';
		
		foreach($tabs as $id => $label){
			
			$css = ($current_tab == $id) ? 'nav-tab nav-tab-active' : 'nav-tab';
			$url = ($default_tab == $id) ? $base_url : add_query_arg('stage', $id, $base_url);
			
			$out .= "<a href='{$url}' class='{$css}'>{$label}</a>";		
		}
		
		return $out;
	}

	/** CSS/JS **/		
	public function enqueue_cssjs() {

		wp_enqueue_style('leyka-icon', LEYKA_PLUGIN_BASE_URL.'css/admin-icon.css', array(), LEYKA_VERSION);

		$screen = get_current_screen();
		if(false === strpos($screen->base, 'leyka') && false === strpos($screen->id, 'leyka')) //load css/js on own pages only
			return;

		$css_deps = array();
		wp_enqueue_style('leyka-admin', LEYKA_PLUGIN_BASE_URL.'css/admin.css', $css_deps, LEYKA_VERSION);

		$js_deps = array('jquery');	
		wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'js/admin.js', $js_deps, LEYKA_VERSION, true);

        wp_localize_script('leyka-admin', 'leyka', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'ajax_loader_url' => LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif'
        ));
	}

} //class end