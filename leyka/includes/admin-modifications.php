<?php
/**
 * @package Leyka
 * @subpackage Admin modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

function leyka_plugins_loaded(){
    /** Set localization: */
    // Set filter for plugin's languages directory
    $plugin_lang_dir = dirname(LEYKA_PLUGIN_INNER_SHORT_NAME).'/languages/';
    $plugin_lang_dir = apply_filters('leyka_languages_directory', $plugin_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'leyka');
    $mofile = sprintf('%1$s-%2$s.mo', 'leyka', $locale);

    // Setup paths to current locale file
    $mofile_local = $plugin_lang_dir.$mofile;
    $mofile_global = WP_LANG_DIR.'/leyka/'.$mofile;

    if(file_exists($mofile_global)) {
        // Look in global /wp-content/languages/edd folder
        load_textdomain('leyka', $mofile_global);
    } elseif(file_exists(WP_PLUGIN_DIR.'/'.$mofile_local)) {
        // Look in local /wp-content/plugins/easy-digital-donates/languages/ folder
        load_textdomain('leyka', WP_PLUGIN_DIR.'/'.$mofile_local);
    } else {
        // Load the default language files
        load_plugin_textdomain('leyka', false, $plugin_lang_dir);
    }
    /** Localization ended */

    function leyka_admin_menu(){
        global $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_recalls_page, $edd_upgrades_screen, $edd_system_info_page;

        require_once EDD_PLUGIN_DIR.'includes/admin/system-info.php';

        // Payment history page handling function is changed due to UI reasons.
        // Also, Discounts page is removed:
        $edd_payments_page = add_submenu_page('edit.php?post_type=download', __('Donations history', 'leyka'), __('Donations history', 'leyka'), 'manage_options', 'edd-payment-history', 'leyka_donations_history_page');
        $edd_recalls_page = add_submenu_page('edit.php?post_type=download', __('Donor recalls', 'leyka'), __('Donor recalls', 'leyka'), 'manage_options', 'edit.php?post_type=leyka_recall');
        $edd_reports_page = add_submenu_page('edit.php?post_type=download', __('Earnings and Sales Reports', 'edd'), __('Reports', 'edd'), 'manage_options', 'edd-reports', 'leyka_reports_page');
        $edd_settings_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download Settings', 'edd'), __('Settings', 'edd'), 'manage_options', 'edd-settings', 'edd_options_page');
        $edd_system_info_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download System Info', 'edd' ), __('System Info', 'edd'), 'manage_options', 'edd-system-info', 'edd_system_info');
        // Add-ons page removed until further testing for their compatibility with Leyka:
//        $edd_add_ons_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download Add Ons', 'edd'), __('Add Ons', 'edd'), 'manage_options', 'edd-addons', 'edd_add_ons_page');
    }
    remove_action('admin_menu', 'edd_add_options_link', 10);
    add_action('admin_menu', 'leyka_admin_menu');
}
add_action('plugins_loaded', 'leyka_plugins_loaded');

function leyka_init(){
    /** Check if base EDD is active at the moment */
    if( !leyka_is_edd_active() ) {
        echo __('<div id="message" class="error"><strong>Error:</strong> Easy Digital Downloads plugin is missing or inactive. It is required for donates module to work. Base donations plugin will be deactivated.</div>', 'leyka');
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(LEYKA_PLUGIN_INNER_NAME);
        return;
    }

    /** Global changes */
    // Change some Download post type labels to the Donate labels
    function leyka_donate_labels($labels){
        return array(
            'name' 				 => __('Donates', 'leyka'),
            'singular_name' 	 => __('Donate', 'leyka'),
            'add_new' 			 => __('Add new', 'leyka'),
            'add_new_item' 		 => __('Add new %1$s', 'leyka'),
            'edit_item' 		 => __('Edit %1$s', 'leyka'),
            'new_item' 			 => __('New %1$s', 'leyka'),
            'all_items' 		 => __('All %2$s', 'leyka'),
            'view_item' 		 => __('View %1$s', 'leyka'),
            'search_items' 		 => __('Search %2$s', 'leyka'),
            'not_found' 		 => __('No %2$s found', 'leyka'),
            'not_found_in_trash' => __('No %2$s found in Trash', 'leyka'),
            'parent_item_colon'  => '',
            'menu_name' 		 => __('%2$s', 'leyka')
        );
    }
    add_filter('edd_download_labels', 'leyka_donate_labels');

    // Remove categories and tags as a whole. Must be done in the "init" hook with priority below 10
    remove_action('init', 'edd_setup_download_taxonomies');
    remove_action('restrict_manage_posts', 'edd_add_download_filters', 100);

    /** @todo After the 1.0 release, check if code below can be moved to the "admin_init" or "plugins_loaded" hooks. */
    /** Plugins list page */
    // Add settings link on plugin page
    function leyka_plugin_page_links($links){
        array_unshift(
            $links,
            '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings').'">'.__('Settings').'</a>'
        );
        return $links;
    }
    add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, 'leyka_plugin_page_links');

    // Hide original EDD for fool protection reasons
    function leyka_plugins_list($wp_plugins_list){
        unset($wp_plugins_list['easy-digital-downloads/easy-digital-downloads.php']);
        return $wp_plugins_list;
    }
    add_filter('all_plugins', 'leyka_plugins_list');

    // Disable auto-updates for original EDD.
    // Mostly to exclude EDD from "plugins-need-to-be-updated" counter and from core updates page.
    function leyka_update_plugins_list($value){
        unset($value->response['easy-digital-downloads/easy-digital-downloads.php']);
        return $value;
    }
    add_filter('site_transient_update_plugins', 'leyka_update_plugins_list');

    // Remove EDD upgrade notices:
    remove_action('admin_notices', 'edd_show_upgrade_notices');

    // Check if Leyka custom templates are in place, show a warning if it's not:
    $current_theme_dir = get_template_directory();
    if( !is_dir($current_theme_dir.'/edd_templates') ) {
        // Copy all EDD templates into the current WP theme folder:
        $success = mkdir($current_theme_dir.'/edd_templates');
        if($success) {
            $templates = scandir(LEYKA_PLUGIN_DIR.'/edd_templates');
            if($templates){
                foreach($templates as $file){
                    if($file == '.' || $file == '..')
                        continue;
                    $success = copy(
                        LEYKA_PLUGIN_DIR.'/edd_templates/'.$file,
                        $current_theme_dir.'/edd_templates'.$file
                    );
                    if( !$success ) {
                        @rmdir($current_theme_dir.'/edd_templates');
                        break;
                    }
                }
            }
        }

        if( !$success ) {
            function leyka_templates_admin_notices(){?>
            <div class="error">
                <p><?php echo __("<b>Warning:</b> there's no edd_templates subdirectory in the current theme folder.                                     <br /><br />
                          To fix this, please copy «edd_templates» directory from Leyka plugin folder to your current theme folder.", 'leyka');?></p>
            </div>
            <?php }
            add_action('admin_notices', 'leyka_templates_admin_notices');
        }
    }
}
add_action('init', 'leyka_init', 1);

/**
 * Extended "payment history" page - now it's donations history.
 * It uses Leyka_Donations_History_Table class instead of native EDD class to render page content.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function leyka_donations_history_page(){
    global $edd_options;

    if(isset($_GET['edd-action']) && $_GET['edd-action'] == 'edit-payment') {
        include_once(LEYKA_PLUGIN_DIR.'/includes/admin/edit-payment.php');
    } else {
        include_once(LEYKA_PLUGIN_DIR.'/includes/admin/class-payments-table.php');
        $donations_table = new Leyka_Donations_History_Table();
        $donations_table->prepare_items();?>
    <div class="wrap">
        <h2><?php _e('Donations history', 'leyka');?></h2>
        <?php do_action('edd_payments_page_top');?>
        <form id="edd-payments-filter" method="get" action="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history');?>">
            <?php $donations_table->search_box(__('Search', 'leyka'), 'edd-payments');?>

            <input type="hidden" name="post_type" value="download" />
            <input type="hidden" name="page" value="edd-payment-history" />
            <?php 
                $donations_table->views();
                $donations_table->display();
            ?>
        </form>
        <?php do_action('edd_payments_page_bottom');?>
    </div>
    <?php
    }
}

/** Renders the Reports page. */
function leyka_reports_page(){
    global $edd_options;

    $current_page = admin_url('edit.php?post_type=download&page=edd-reports');
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'reports';?>
<div class="wrap">
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo add_query_arg(array('tab' => 'reports', 'settings-updated' => false), $current_page);?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : '';?>"><?php _e('Reports', 'edd');?></a>
        <a href="<?php echo add_query_arg(array('tab' => 'export', 'settings-updated' => false), $current_page);?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : '';?>"><?php _e('Export', 'edd');?></a>
        <?php /** @todo Uncomment this logs lab when start the task 415 */ ?>
<!--        <a href="--><?php //echo add_query_arg(array('tab' => 'logs', 'settings-updated' => false), $current_page);?><!--" class="nav-tab --><?php //echo $active_tab == 'logs' ? 'nav-tab-active' : '';?><!--">--><?php //_e('Logs', 'edd');?><!--</a>-->
        <?php do_action('edd_reports_tabs');?>
    </h2>

    <?php
    do_action('edd_reports_page_top');
    do_action('edd_reports_tab_'.$active_tab);
    do_action('edd_reports_page_bottom');
    ?>
</div><!-- .wrap -->
<?php
}

/** Updates payment status when clicking on "toggle status" switch */
function leyka_toggle_payment_status(){
    if(empty($_POST['payment_id']) || (int)$_POST['payment_id'] < 0)
        return;
    $_POST['payment_id'] = (int)$_POST['payment_id'];
    $payment = get_post($_POST['payment_id']);
    if( $payment->post_type != 'edd_payment'
        || !current_user_can('edit_post', $_POST['payment_id'])
        || !wp_verify_nonce($_POST['leyka_nonce'], 'leyka-toggle-payment-status') )
        die( json_encode(array('status' => 'error', 'message' => __('Permissions denied', 'leyka'))) );

    $_POST['new_status'] = $_POST['new_status'] === 'publish' ? 'publish' : 'pending';
    global $wpdb;
    // Not using edd_update_payment_status, because it unnessesarily triggers EDD hook that sends email to the donor and Payments Admin:
    $wpdb->update(
        $wpdb->posts,
        array('post_status' => $_POST['new_status']),
        array('ID' => $_POST['payment_id'])
    );
    die( json_encode(array(
        'status' => 'ok',
        'payment_status' => $_POST['new_status'],
    )) );
}
add_action('wp_ajax_leyka-toggle-payment-status', 'leyka_toggle_payment_status');

function leyka_admin_init(){
    /** Add icon option to the icons list. */
    function leyka_icons($icons){
        // Remove default EDD's Visa and Mastercard icons - they don't satisfy Visa & MC logos Terms Of Use:
        unset($icons['visa'], $icons['mastercard'], $icons['paypal']);

        $icons = array_merge( // To add this icons options in the beginning of the list, not in the end
            array(
                // Visa:
                LEYKA_PLUGIN_BASE_URL.'images/icons/visa_s.png' => __('Visa small (105x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/visa_m.png' => __('Visa medium (159x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/visa_b.png' => __('Visa big (248x80 px)', 'leyka'),

                // Verified By Visa:
                LEYKA_PLUGIN_BASE_URL.'images/icons/vbv_s.png' => __('Verified By Visa small (61x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/vbv_m.png' => __('Verified By Visa medium (101x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/vbv_b.png' => __('Verified By Visa big (164x80 px)', 'leyka'),

                // Mastercard:
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_s.png' => __('Mastercard small (55x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_m.png' => __('Mastercard medium (82x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_b.png' => __('Mastercard big (127x80 px)', 'leyka'),

                // Mastercard Secure Code:
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_sc_s.png' => __('Mastercard Secure Code small (64x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_sc_m.png' => __('Mastercard Secure Code medium (94x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/mc_sc_b.png' => __('Mastercard Secure Code big (150x80 px)', 'leyka'),

                // JCB:
                LEYKA_PLUGIN_BASE_URL.'images/icons/jcb_s.png' => __('JCB small (45x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/jcb_m.png' => __('JCB medium (65x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/jcb_b.png' => __('JCB big (103x80 px)', 'leyka'),

                // Paypal (EDD integrated):
                LEYKA_PLUGIN_BASE_URL.'images/icons/paypal_s.png' => __('Paypal small (55x35 px)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/paypal_m.png' => __('Paypal medium (81x51 px) (recommended)', 'leyka'),
                LEYKA_PLUGIN_BASE_URL.'images/icons/paypal_b.png' => __('Paypal big (127x80 px)', 'leyka'),
            ),
            $icons
        );

        return $icons;
    }
    add_filter('edd_accepted_payment_icons', 'leyka_icons');

    /** Main donate list page */
    // Remove some columns from main donates list table
    function leyka_donate_columns($donate_columns){
        $donate_columns = array(
            'cb' => '<input type="checkbox"/>',
            'title' => __('Donate name', 'leyka'),
            'price' => __('Donate size', 'leyka'),
            'sales' => __('Donations number', 'leyka'),
            'earnings' => __('Amount collected', 'leyka'),
            'date' => __('Created on', 'leyka')
        );
        return $donate_columns;
    }
    add_filter('manage_edit-download_columns', 'leyka_donate_columns');

    // Render values in "donation sum" column
    function leyka_render_donate_columns($column_name, $post_id){
        if(get_post_type($post_id) == 'download') {
            global $edd_options;

            switch($column_name) {
                case 'price':
                    if(leyka_is_any_sum_allowed($post_id))
                        echo str_replace(
                            array('#MIN_SUM#', '#MAX_SUM#', '#CURRENCY#'),
                            array(
                                leyka_get_min_free_donation_sum($post_id),
                                leyka_get_max_free_donation_sum($post_id),
                                edd_currency_filter('')
                            ),
                            __('#MIN_SUM# #CURRENCY# - #MAX_SUM# #CURRENCY# (donation sum is defined by donors)', 'leyka')
                        );
                    else if(edd_has_variable_prices($post_id))
                        echo __('A few variants of possible donation sum', 'leyka');
                    else
                        echo edd_price($post_id, false)
                            .'<input type="hidden" class="downloadprice-'.$post_id.'" value="'.edd_get_download_price($post_id).'" />';
                    break;
            }
        }
    }
    remove_action('manage_posts_custom_column', 'edd_render_download_columns', 10);
    add_action('manage_posts_custom_column', 'leyka_render_donate_columns', 10, 2);

    function leyka_price_field_quick_edit($column_name, $post_type){
        if($column_name != 'price' || $post_type != 'download')
            return;?>
    <fieldset class="inline-edit-col-left">
        <div id="edd-download-data" class="inline-edit-col">
            <h4><?php echo __('Donate configuration', 'leyka');?></h4>
            <label>
                <span class="title"><?php _e('Price', 'leyka');?></span>
				<span class="input-text-wrap">
					<input type="text" name="_edd_regprice" class="text regprice" />
				</span>
            </label>
            <br class="clear" />
        </div>
    </fieldset>
    <?php
    }
    add_action('quick_edit_custom_box', 'edd_price_field_quick_edit', 10);
    add_action('quick_edit_custom_box', 'leyka_price_field_quick_edit', 10, 2);

    /** New/edit donate page */
    // Add new donate title placeholder
    function leyka_change_default_title($title){
        $screen = get_current_screen();
        if($screen->post_type == 'download')
            $title = __('Enter donate title here', 'leyka');
        return $title;
    }
    remove_filter('enter_title_here', 'edd_change_default_title');
    add_filter('enter_title_here', 'leyka_change_default_title');

    // Donate data blocks (metaboxes) list:
    function leyka_donate_meta_boxes(){
        remove_meta_box('postimagediv', 'download', 'side'); // Post image metabox isn't needed
        remove_meta_box('postexcerpt', 'download', 'normal'); // Post excerpt metabox isn't needed

        add_meta_box('downloadinformation', __('Donate configuration', 'leyka'), 'edd_render_download_meta_box', 'download', 'normal', 'high');
        add_meta_box('edd_product_notes', __('Donate notes', 'leyka'), 'edd_render_product_notes_meta_box', 'download', 'normal', 'default');
        add_meta_box('leyka_donate_stats', __('Donate stats', 'leyka'), 'leyka_render_stats_meta_box', 'download', 'side', 'high');
        add_meta_box('leyka_donation_log', __('Donation log', 'leyka'), 'leyka_render_donation_log_meta_box', 'download', 'normal', 'default');
    }
    remove_action('add_meta_boxes', 'edd_add_download_meta_box');
    add_action('add_meta_boxes', 'leyka_donate_meta_boxes');

    // Donate notes metabox:
    function leyka_render_product_notes_field($donate_id){
        global $edd_options;
        $donate_notes = edd_get_product_notes($donate_id);?>
    <textarea rows="1" cols="40" class="large-texarea" name="edd_product_notes" id="edd_product_notes"><?php echo esc_textarea($donate_notes);?></textarea>
    <p><?php _e('Special notes or instructions for this donate. These notes will be added to the thanking email sended to the donor.', 'leyka');?></p>
    <?php
    }
    remove_action('edd_product_notes_meta_box_fields', 'edd_render_product_notes_field');
    add_action('edd_product_notes_meta_box_fields', 'leyka_render_product_notes_field');

    // Render donate stats metabox:
    function leyka_render_stats_meta_box(){
        global $post;?>

    <table class="form-table">
        <tr>
            <th style="width:60%;"><?php _e('Donations number', 'leyka');?>:</th>
            <td class="edd_download_stats"><?php echo edd_get_download_sales_stats($post->ID);?></td>
        </tr>
        <tr>
            <th style="width:60%;"><?php _e('Amount collected', 'leyka');?>:</th>
            <td class="edd_download_stats"><?php echo edd_currency_filter(edd_get_download_earnings_stats($post->ID));?></td>
        </tr>
        <?php do_action('edd_stats_meta_box');?>
    </table>
    <?php }

    // Render donations log metabox:
    function leyka_render_donation_log_meta_box(){
        global $post;

        $per_page = 10;

        if(isset($_GET['edd_sales_log_page'])) {
            $page = (int)$_GET['edd_sales_log_page'];
            $offset = $per_page*($page - 1);
            $donations_log = edd_get_download_sales_log($post->ID, true, $per_page, $offset);
        } else {
            $page = 1;
            $donations_log = edd_get_download_sales_log($post->ID, false);
        }?>

    <table class="form-table">
        <tr>
            <th style="width:20%"><strong><?php _e('Donations log', 'leyka')?></strong></th>
            <td colspan="4" class="edd_download_stats">
                <?php _e('Each donation for this donate target is listed below.', 'leyka');?>
            </td>
        </tr>
        <?php if($donations_log['sales']) {
            foreach($donations_log['sales'] as $donation) {
                if($donation['user_info']['id'] != 0) {
                    $user_data = get_userdata($donation['user_info']['id']);
                    $name = $user_data->display_name;
                } else {
                    $name = $donation['user_info']['first_name'].' '.$donation['user_info']['last_name'];
                }?>
        <tr>
            <td class="edd_download_sales_log">
                <strong><?php _e('Date');?>:</strong> <?php echo $donation['date'];?>
            </td>
            <td class="edd_download_sales_log">
                <strong><?php _e('Donor', 'leyka');?>:</strong> <?php echo $name;?>
            </td>
            <td colspan="3" class="edd_download_sales_log">
                <strong><?php _e('Donation ID', 'leyka');?>:</strong> 
                <a href="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history&purchase_id='.$donation['payment_id'].'&edd-action=edit-payment');?>"><?php echo $donation['payment_id'];?></a>
            </td>
        </tr>
        <?php } // endforeach
            do_action('edd_purchase_log_meta_box');
        } else {?>
        <tr><td colspan="2" class="edd_download_sales_log"><?php _e('No donations yet', 'leyka');?></td></tr>
        <?php }?>
    </table>
    <?php
        $total_log_entries = $donations_log['number'];
        $total_pages = ceil($total_log_entries / $per_page);

        if($total_pages > 1) {?>
            <div class="tablenav">
                <div class="tablenav-pages alignright">
            <?php $base = 'post.php?post='.$post->ID.'&action=edit%_%';
            echo paginate_links(array(
                'base' => $base,
                'format' => '&edd_sales_log_page=%#%',
                'prev_text' => '&laquo; '.__('Previous', 'edd'),
                'next_text' => __('Next', 'edd').' &raquo;',
                'total' => $total_pages,
                'current' => $page,
                'end_size' => 1,
                'mid_size' => 5,
                'add_fragment' => '#edd_purchase_log'
            ));?>
                </div>
            </div><!--end .tablenav-->
        <?php }
    }

    // Donate configuration block content:
    function leyka_meta_box_fields($post_id){
        global $edd_options;

        $price 				= edd_get_download_price( $post_id );
        $variable_pricing 	= edd_has_variable_prices( $post_id );
        $prices 			= edd_get_variable_prices( $post_id );

        $price_display    	= $variable_pricing ? ' style="display:none;"' : '';
        $variable_display 	= $variable_pricing ? '' : ' style="display:none;"';
        ?>

    <p>
        <strong><?php _e('Pricing Options:', 'leyka');?></strong>
    </p>

    <p>
        <label for="edd_variable_pricing">
            <input type="checkbox" name="_variable_pricing" id="edd_variable_pricing" value="1" <?php checked( 1, $variable_pricing ); ?> />
            <?php _e('Enable variable pricing', 'leyka'); ?>
        </label>
    </p>

    <div id="edd_regular_price_field" class="edd_pricing_fields" <?php echo $price_display; ?>>
        <?php if( !isset( $edd_options['currency_position'] ) || $edd_options['currency_position'] == 'before' ) : ?>
        <?php echo edd_currency_filter(''); ?><input type="text" name="edd_price" id="edd_price" value="<?php echo isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : '';?>" size="30" style="width:80px;" maxlength="30" placeholder="9.99"/>
        <?php else : ?>
        <input type="text" name="edd_price" id="edd_price" value="<?php echo isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : ''; ?>" size="30" maxlength="30" style="width:80px;" placeholder="9.99"/><?php echo edd_currency_filter(''); ?>
        <?php endif; ?>

        <?php do_action('edd_price_field', $post_id); ?>
    </div>

    <div id="edd_variable_price_fields" class="edd_pricing_fields" <?php echo $variable_display; ?>>
        <input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>

        <div id="edd_price_fields" class="edd_meta_table_wrap">
            <table class="widefat" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th><?php _e('Option Name', 'leyka'); ?></th>
                    <th style="width: 90px"><?php _e('Price', 'leyka'); ?></th>
                    <?php do_action('edd_download_price_table_head', $post_id);?>
                    <th style="width: 2%"></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    if ( !empty($prices) ) :
                        foreach($prices as $key => $value) :
                            $name   = isset( $prices[ $key ]['name'] ) ? $prices[ $key ]['name'] : '';
                            $amount = isset( $prices[ $key ]['amount'] ) ? $prices[ $key ]['amount'] : '';

                            $args = apply_filters( 'edd_price_row_args', compact( 'name', 'amount' ) );
                            ?>
                        <tr class="edd_variable_prices_wrapper">
                            <?php do_action( 'edd_render_price_row', $key, $args, $post_id ); ?>
                        </tr>
                            <?php
                        endforeach;
                    else :
                        ?>
                    <tr class="edd_variable_prices_wrapper">
                        <?php do_action( 'edd_render_price_row', 0, array(), $post_id ); ?>
                    </tr>
                        <?php endif; ?>

                <tr>
                    <td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
                        <a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e('Add New Price', 'leyka'); ?></a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php /** Additional fields on the new/edit donate admin form: */ 
        $max_donation_sum = leyka_get_max_free_donation_sum($post_id);
        $min_donation_sum = leyka_get_min_free_donation_sum($post_id);
        $any_sum_allowed = leyka_is_any_sum_allowed($post_id);
    ?>
    <p>
        <label>
            <input type="checkbox" name="leyka_any_sum_allowed" id="leyka_any_sum_allowed" value="1" <?php echo ($any_sum_allowed ? 'checked' : '');?> />
            &nbsp;<?php _e('Any price can be donated (free choice of the donor)', 'leyka');?>
            <div id="leyka_max_donation_sum_wrapper" style="display:<?php echo ($any_sum_allowed ? 'block' : 'none');?>">
                <label>
                    <input type="text" name="leyka_min_donation_sum" value="<?php echo ($min_donation_sum ? $min_donation_sum : 10.0);?>" maxlength="30" />
                    &nbsp;<?php echo sprintf(__('Minimum donation amount, %s', 'leyka'), edd_currency_filter(''));?>
                </label>
                <br />
                <label>
                    <input type="text" name="leyka_max_donation_sum" value="<?php echo ($max_donation_sum ? $max_donation_sum : 30000.0);?>" maxlength="30" />
                    &nbsp;<?php echo sprintf(__('Maximum donation amount, %s', 'leyka'), edd_currency_filter(''));?>
                </label>
            </div>
        </label>
    </p>
    <?php
    }
    remove_all_actions('edd_meta_box_fields', 1);
    add_action('edd_meta_box_fields', 'leyka_meta_box_fields');

    // Process additional fields of the new/edit donate admin form:
    function leyka_metabox_fields_save($fields){
        return array_merge($fields, array(
            'leyka_any_sum_allowed',
            'leyka_min_donation_sum',
            'leyka_max_donation_sum'
        ));
    }
    add_filter('edd_metabox_fields_save', 'leyka_metabox_fields_save');

    // Max donation amount field pre-saving check:
    function leyka_save_metabox_max_sum($value){
        return (float)$value > 0.0 ? (float)$value : 30000.0;
    }
    add_filter('edd_metabox_save_leyka_max_donation_sum', 'leyka_save_metabox_max_sum');

    /**
     * Changes in the Donates -> Statistics page.
     * 
     * Changes in the Donates -> Statistics -> Stats tab page:
     */
    // Common stats fields:
    function leyka_stats_views($views){
        $views['earnings'] = __('Incoming funds', 'leyka');
        return $views;
    }
    add_filter('edd_report_views', 'leyka_stats_views');

    // Stats -> Reports -> Incoming funds report:
    function leyka_reports_earnings(){?>
    <div class="tablenav top">
        <div class="alignleft actions"><?php edd_report_views();?></div>
    </div>
    <?php
        leyka_reports_graph();
    }
    remove_action('edd_reports_view_earnings', 'edd_reports_earnings');
    add_action('edd_reports_view_earnings', 'leyka_reports_earnings');
    
    // Render the incoming funds view:
    function leyka_reports_graph(){
        $dates = edd_get_report_dates(); // Retrieve the queried dates

        // Determine graph options
        switch( $dates['range'] ) :
            case 'today' :
                $time_format 	= '%d/%b';
                $tick_size		= 'hour';
                $day_by_day		= true;
                break;
            case 'last_year' :
                $time_format 	= '%b';
                $tick_size		= 'month';
                $day_by_day		= false;
                break;
            case 'this_year' :
                $time_format 	= '%b';
                $tick_size		= 'month';
                $day_by_day		= false;
                break;
            case 'last_quarter' :
                $time_format	= '%b';
                $tick_size		= 'month';
                $day_by_day 	= false;
                break;
            case 'this_quarter' :
                $time_format	= '%b';
                $tick_size		= 'month';
                $day_by_day 	= false;
                break;
            case 'other' :
                if( ( $dates['m_end'] - $dates['m_start'] ) >= 2 ) {
                    $time_format	= '%b';
                    $tick_size		= 'month';
                    $day_by_day 	= false;
                } else {
                    $time_format 	= '%d/%b';
                    $tick_size		= 'day';
                    $day_by_day 	= true;
                }
                break;
            default:
                $time_format 	= '%d/%b'; 	// Show days by default
                $tick_size		= 'day'; 	// Default graph interval
                $day_by_day 	= true;
                break;
        endswitch;

        $time_format 	= apply_filters( 'edd_graph_timeformat', $time_format );
        $tick_size 		= apply_filters( 'edd_graph_ticksize', $tick_size );
        $totals 		= (float) 0.00; // Total earnings for time period shown
        $sales_totals   = 0;            // Total sales for time period shown

        ob_start(); ?>
    <script type="text/javascript">
        jQuery( document ).ready( function($) {
            $.plot(
                    $("#edd_monthly_stats"),
                    [{
                        data: [
                            <?php

                            if( $dates['range'] == 'today' ) {
                                // Hour by hour
                                $hour  = 1;
                                $month = date( 'n' );
                                while ( $hour <= 23 ) :
                                    $sales = edd_get_sales_by_date( $dates['day'], $month, $dates['year'], $hour );
                                    $sales_totals += $sales;
                                    $date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ); ?>
                                    [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                    <?php
                                    $hour++;
                                endwhile;

                            } elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {

                                //Day by day
                                $day     = $dates['day'];
                                $day_end = $dates['day_end'];
                                $month   = $dates['m_start'];
                                while ( $day <= $day_end ) :
                                    $sales = edd_get_sales_by_date( $day, $month, $dates['year'] );
                                    $sales_totals += $sales;
                                    $date = mktime( 0, 0, 0, $month, $day, $dates['year'] ); ?>
                                    [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                    <?php
                                    $day++;
                                endwhile;

                            } else {

                                $i = $dates['m_start'];
                                while ( $i <= $dates['m_end'] ) :
                                    if ( $day_by_day ) :
                                        $num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
                                        $d 				= 1;
                                        while ( $d <= $num_of_days ) :
                                            $sales = edd_get_sales_by_date( $d, $i, $dates['year'] );
                                            $sales_totals += $sales;
                                            $date = mktime( 0, 0, 0, $i, $d, $dates['year'] ); ?>
                                            [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                            <?php
                                            $d++;
                                        endwhile;
                                    else :
                                        $date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
                                        ?>
                                        [<?php echo $date * 1000; ?>, <?php echo edd_get_sales_by_date( null, $i, $dates['year'] ); ?>],
                                        <?php
                                    endif;
                                    $i++;
                                endwhile;

                            }

                            ?>
                        ],
                        yaxis: 2,
                        label: "<?php _e('Donations number', 'leyka');?>",
                        id: 'sales'
                    },
                        {
                            data: [
                                <?php

                                if( $dates['range'] == 'today' ) {

                                    // Hour by hour
                                    $hour  = 1;
                                    $month = date( 'n' );
                                    while ( $hour <= 23 ) :
                                        $earnings = edd_get_earnings_by_date( $dates['day'], $month, $dates['year'], $hour );
                                        $totals += $earnings;
                                        $date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ); ?>
                                        [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                        <?php
                                        $hour++;
                                    endwhile;

                                } elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

                                    //Day by day
                                    $day     = $dates['day'];
                                    $day_end = $dates['day_end'];
                                    $month   = $dates['m_start'];
                                    while ( $day <= $day_end ) :
                                        $earnings = edd_get_earnings_by_date( $day, $month, $dates['year'] );
                                        $totals += $earnings;
                                        $date = mktime( 0, 0, 0, $month, $day, $dates['year'] ); ?>
                                        [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                        <?php
                                        $day++;
                                    endwhile;

                                } else {

                                    $i = $dates['m_start'];
                                    while ( $i <= $dates['m_end'] ) :
                                        if ( $day_by_day ) :
                                            $num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
                                            $d 				= 1;
                                            while ( $d <= $num_of_days ) :
                                                $date = mktime( 0, 0, 0, $i, $d, $dates['year'] );
                                                $earnings = edd_get_earnings_by_date( $d, $i, $dates['year'] );
                                                $totals += $earnings; ?>
                                                [<?php echo $date * 1000; ?>, <?php echo $earnings ?>],
                                                <?php $d++; endwhile;
                                        else :
                                            $date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
                                            $earnings = edd_get_earnings_by_date( null, $i, $dates['year'] );
                                            $totals += $earnings;
                                            ?>
                                            [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                            <?php
                                        endif;
                                        $i++;
                                    endwhile;

                                }

                                ?>
                            ],
                            label: "<?php _e('Incoming funds', 'leyka');?>",
                            id: 'earnings'
                        }],
                    {
                        series: {
                            lines: { show: true },
                            points: { show: true }
                        },
                        grid: {
                            show: true,
                            aboveData: false,
                            color: '#ccc',
                            backgroundColor: '#fff',
                            borderWidth: 2,
                            borderColor: '#ccc',
                            clickable: false,
                            hoverable: true
                        },
                        xaxis: {
                            mode: "time",
                            timeFormat: "<?php echo $time_format; ?>",
                            minTickSize: [1, "<?php echo $tick_size; ?>"]
                        },
                        yaxis: [
                            { min: 0, tickSize: 1, tickDecimals: 2 },
                            { min: 0, tickDecimals: 0 }
                        ]

                    });

            function edd_flot_tooltip(x, y, contents) {
                $('<div id="edd-flot-tooltip">' + contents + '</div>').css( {
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 5,
                    border: '1px solid #fdd',
                    padding: '2px',
                    'background-color': '#fee',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            var previousPoint = null;
            $("#edd_monthly_stats").bind("plothover", function (event, pos, item) {
                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));
                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;
                        $("#edd-flot-tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);
                        if( item.series.id == 'earnings' ) {
                            if( edd_vars.currency_pos == 'before' ) {
                                edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + edd_vars.currency_sign + y );
                            } else {
                                edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + edd_vars.currency_sign );
                            }
                        } else {
                            edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y.replace( '.00', '' ) );
                        }
                    }
                } else {
                    $("#edd-flot-tooltip").remove();
                    previousPoint = null;
                }
            });
        });
    </script>

    <div class="metabox-holder" style="padding-top: 0;">
        <div class="postbox">
            <h3><span><?php _e('Incoming funds over time', 'leyka'); ?></span></h3>

            <div class="inside">
                <?php edd_reports_graph_controls(); ?>
                <div id="edd_monthly_stats" style="height: 300px;"></div>
                <p id="edd_graph_totals"><strong><?php _e('Total incoming funds for period shown:', 'leyka'); echo ' '.edd_currency_filter(edd_format_amount($totals));?></strong></p>
                <p id="edd_graph_totals"><strong><?php _e('Total donations maked for period shown:', 'leyka'); echo ' '.$sales_totals;?></strong></p>
            </div>
        </div>
    </div>
    <?php
        echo ob_get_clean();
    }

    // Stats -> Reports -> Donates view:
    function leyka_reports_donates_table(){
        require_once(LEYKA_PLUGIN_DIR.'includes/admin/class-donate-reports-table.php');

        $donate_reports_table = new Leyka_Donate_Reports_Table();
        $donate_reports_table->prepare_items();
        $donate_reports_table->display();
    }
    remove_action('edd_reports_view_downloads', 'edd_reports_downloads_table');
    add_action('edd_reports_view_downloads', 'leyka_reports_donates_table');

    // Stats -> Reports -> Donors view:
    function leyka_reports_donors_table(){
        require_once(LEYKA_PLUGIN_DIR.'includes/admin/class-donor-reports-table.php');

        $donors_table = new Leyka_Donor_Reports_Table();
        $donors_table->prepare_items();
        $donors_table->display();
    }
    remove_action('edd_reports_view_customers', 'edd_reports_customers_table');
    add_action('edd_reports_view_customers', 'leyka_reports_donors_table');

    /** Changes in Stats -> Export. */ 
    function leyka_reports_tab_export(){?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php _e('Export PDF of donations maked and funds received', 'leyka'); ?></span></h3>
                    <div class="inside">
                        <p><?php _e('Download a PDF file of donations maked and funds received for all donates for the current year.', 'leyka' ); ?> <?php _e('Date range reports will be coming soon.', 'edd');?></p>
                        <p><a class="button" href="<?php echo wp_nonce_url( add_query_arg(array('edd-action' => 'generate_pdf')), 'edd_generate_pdf'); ?>"><?php _e('Generate PDF', 'edd');?></a></p>
                    </div><!-- .inside -->
                </div><!-- .postbox -->

                <div class="postbox">
                    <h3><span><?php _e('Export donations history', 'leyka'); ?></span></h3>
                    <div class="inside">
                        <p><?php _e('Download a CSV of all payments recorded.', 'edd');?></p>
                        <p><a class="button" href="<?php echo wp_nonce_url(add_query_arg(array('edd-action' => 'payment_export')), 'edd_payments_export');?>"><?php _e('Generate CSV', 'edd');?></a>
                        </p>
                    </div><!-- .inside -->
                </div><!-- .postbox -->

                <div class="postbox">
                    <h3><span><?php _e('Export donors in CSV', 'leyka');?></span></h3>
                    <div class="inside">
                        <p><?php _e('Download a CSV file of all donors emails. This export includes donation numbers and amounts for each donor.', 'leyka');?></p>
                        <p><a class="button" href="<?php echo wp_nonce_url(add_query_arg(array('edd-action' => 'email_export')), 'edd_email_export');?>"><?php _e('Generate CSV', 'edd');?></a></p>
                    </div><!-- .inside -->
                </div><!-- .postbox -->
            </div><!-- .post-body-content -->
        </div><!-- .post-body -->
    </div><!-- .metabox-holder -->
    <?php
    }
    remove_action('edd_reports_tab_export', 'edd_reports_tab_export');
    add_action('edd_reports_tab_export', 'leyka_reports_tab_export');

    /** Changes in the Stats -> Logs page. */
    // Common log views:
    function leyka_log_views($views){
        unset($views['sales']); // "Sales" view is almost identical to "file_downloads" view
        /**
         * @todo Add to file_downloads view the "donation sum" column. Remove "File" column. Make the table content appear!
         */
        $views['file_downloads'] = __('Donations', 'leyka');

        return $views;
    }
    add_filter('edd_log_views', 'leyka_log_views');

    /** Changes in the Donates->Settings sections */
    // Changes in on Settings->General admin section:
    function leyka_general_settings($settings){
        unset($settings['tracking_settings'], $settings['presstrends']);

        $settings['purchase_page']['name'] = __('Donations checkout page', 'leyka');
        $settings['purchase_page']['desc'] = __('This is the page where users will select the gateway to make their donations', 'leyka');

        $settings['success_page']['desc'] = __('This is the page where users will be redirected after successful donations', 'leyka');

        $settings['failure_page']['name'] = __('This is the page where users will be redirected after failed donations', 'leyka');
        $settings['failure_page']['desc'] = __('Donations failure page', 'leyka');

        array_push(
            $settings,
            array(
                'id' => 'default_status_options',
                'name' => '<strong>'.__('Default status options', 'leyka').'</strong>',
                'desc' => __('Configure the default status options', 'leyka'),
                'type' => 'header'
            ), array(
                'id' => 'leyka_payments_default_status',
                'name' => __('Payments default status', 'leyka'),
                'desc' => __('Deafult status for newly created donation payments', 'leyka'),
                'type' => 'select',
                'options' => edd_get_payment_statuses()
            ), array(
                'id' => 'leyka_recalls_default_status',
                'name' => __("Donor's recalls default status", 'leyka'),
                'desc' => __('Deafult status for newly created donor recalls', 'leyka'),
                'type' => 'select',
                'options' => array(
                    'pending' => __('Pending'),
                    'draft' => __('Draft'),
                    'publish' => __('Publish')
                )
            ));
        return $settings;
    }
    add_filter('edd_settings_general', 'leyka_general_settings');

    // Changes in on Settings->Gateways admin section:
    function leyka_gateways_options($settings){
        global $edd_options;
        if(empty($edd_options['gateways']['paypal'])) {
            unset(
            $settings['paypal'], $settings['paypal_email'], $settings['paypal_page_style'],
            $settings['paypal_alternate_verification'], $settings['disable_paypal_verification']
            );
        }
        return $settings;
    }
    add_filter('edd_settings_gateways', 'leyka_gateways_options');

    // Changes in on Settings->Emails admin section:
    function leyka_emails_settings($settings){
        $settings['from_name']['desc'] = __('The name donations thanking emails are said to come from. This should probably be your site or NGO name.', 'leyka');
        $from_name = get_bloginfo('name');
        if( !$from_name )
            $from_name = trim(str_replace(array('http://', 'https://'), array('', ''), get_bloginfo('wpurl')), '/');
        $settings['from_name']['std'] = $from_name;

        $settings['from_email']['desc'] = __('Email to send donations thanking emails from. This will act as the "from" and "reply-to" address.', 'leyka');
        $settings['from_email']['std'] = get_bloginfo('admin_email');
        
        $settings['purchase_subject']['name'] = __('Donations thanking email subject', 'leyka');
        $settings['purchase_subject']['desc'] = __('Enter the subject line for the donations thanking email', 'leyka');
        $settings['purchase_subject']['std'] = __('Thank you for your donation!', 'leyka');

        $settings['purchase_receipt']['name'] = __('Donation thanking email template', 'leyka');
        $settings['purchase_receipt']['desc'] = __('Enter the email that is sent to donations managers after completing a purchase. HTML is accepted. Available template tags:', 'leyka').'<br/>'.
            '{download_list} - '.__('A list of donates given', 'leyka').'<br/>'.
            '{name} - '.__('The donor\'s name', 'leyka').'<br/>'.
            '{date} - '.__('The date of the donation', 'leyka').'<br/>'.
            '{price} - '.__('The total amount of the donation', 'leyka').'<br/>'.
            '{receipt_id} - '.__('The unique ID number for this donation', 'leyka').'<br/>'.
            '{payment_method} - '.__('The method of payment used for this donation', 'leyka').'<br/>'.
            '{sitename} - '.__('Your site name', 'edd');
        $settings['purchase_receipt']['std'] = __('Hello, {name}!<br /><br />You have chosed to make the following donations:<br />{download_list}<br />which totally cost {price}, by the {payment_method} gateway.<br /><br />Sincerely thank you, {sitename}, {date}', 'leyka');
        $settings['admin_notice_emails']['name'] = __("Donations manager's emails", 'leyka');
        $settings['admin_notice_emails']['std'] = get_bloginfo('admin_email');

        array_push(
            $settings,
            array(
                'id' => 'admin_notifications_subject',
                'name' => __("Donations manager's notification subject", 'leyka'),
                'desc' => __("Enter the donations manager's notification email subject", 'leyka'),
                'type' => 'text',
                'std' => __('New donation came', 'leyka')
            ),
            array(
                'id' => 'admin_donates_email_text',
                'name' => __("Donations manager's notification template", 'leyka'),
                'desc' => __('Enter the email that is sent to donations managers after completing a purchase. HTML is accepted. Available template tags:', 'leyka').'<br/>'.
                    '{download_list} - '.__('A list of donates given', 'leyka').'<br/>'.
                    '{date} - '.__('The date of the donation', 'leyka').'<br/>'.
                    '{price} - '.__('The total amount of the donation', 'leyka').'<br/>'.
                    '{receipt_id} - '.__('The unique ID number for this donation', 'leyka').'<br/>'.
                    '{donate_id} - '.__("The ID number for donation's purpose item", 'leyka').'<br/>'.
                    '{edit_url} - '.__("The URL of the admin page where donation status can be changed", 'leyka').'<br/>'.
                    '{payment_method} - '.__('The method of payment used for this donation', 'leyka').'<br/>'.
                    '{sitename} - '.__('Your site name', 'edd'),
                'type' => 'rich_editor',
                'std' => __('Hello!<br /><br />Recently, there has been a new donation on a {sitename}:<br />{download_list}<br />which totally cost {price}, by the {payment_method} gateway.<br /><br />Donate ID: {donate_id}, donation hashcode: {receipt_id} | {edit_url}<br /><br />{sitename}, {date}', 'leyka'),
            )
        );
        return $settings;
    }
    add_filter('edd_settings_emails', 'leyka_emails_settings');

    // Changes in Settings->Taxes admin section: taxes tab is temp. removed
    function leyka_taxes_settings($settings){
        return array();
    }
    add_filter('edd_settings_taxes', 'leyka_taxes_settings');

    // Changes in the Settings->Misc admin section:
    function leyka_misc_settings($settings){
        unset(
            $settings['live_cc_validation'], $settings['logged_in_only'], $settings['show_register_form'],
            $settings['download_link_expiration'], $settings['disable_redownload']
        );

        $settings['redirect_on_add']['desc'] = __('Redirect to the checkout after adding the donation to the cart.', 'leyka');
        $settings['show_agree_to_terms']['desc'] = __('Show agreement to the terms checkbox. It will have to be checked to make a donation.', 'leyka');
        $settings['agree_label']['std'] = __('I agree to the terms of donation making service.', 'leyka');

        $settings['agree_text']['std'] = '
    1. Предмет договора*
    1. Благотворитель безвозмездно передает, а Благополучатель принимает товарно-материальные ценности и денежные средства для Целевого использования.

2. Условия выполнения договора
2.1. Благотворитель:
2.1.1. Производит целевое пожертвование в адрес Благополучателя в согласованном размере путем передачи товарно-материальных ценностей и денежных средств посредством их вручения, символической передачи.

2.2. Благополучатель:
2.2.1. Благополучатель в праве в любое время до передачи ему пожертвования от него отказаться. Отказ Благополучателя от пожертвования должен быть совершен в письменной форме. В этом случае договор оказания благотворительной помощи считается расторгнутым с момента получения Благотворителем отказа.

Благополучатель пожертвования обязуется использовать денежные средства, полученные по настоящему Договору, строго по целевому назначению в течение одного года с момента их поступления на расчетный счет Получателя пожертвования.

3. Ответственность Сторон, разрешение споров.
3.1. За неисполнение или ненадлежащее исполнение своих обязательств Стороны несут ответственность в соответствии с законодательством Российской Федерации.
3.2. Все споры и разногласия, возникающие в ходе исполнения настоящего Договора, Стороны будут стремиться решать путем переговоров.
3.3. Споры и разногласия, не разрешенные путем переговоров, подлежат разрешению в соответствии с действующим законодательством Российской Федерации.

4. Срок действия договора и прочие условия.
4.1. При выполнении пожертвования средствами web-сайта, отметка о согласии с условиями пожертвования подразумевает принятие условий публичной оферты.
4.2. Вопросы, не урегулированные настоящим Договором, регулируются действующим законодательством Российской Федерации.
4.3. Настоящий Договор составлен в 2-х подлинных экземплярах, имеющих одинаковую юридическую силу.

5. Адреса и реквизиты сторон'; //__('', 'leyka');

        $settings['checkout_label']['name'] = __('A text on a button to complete a donation', 'leyka');
        $settings['checkout_label']['desc'] = __('A text on a button to complete a donation.', 'leyka');
        $settings['checkout_label']['std'] = __('Make the donations', 'leyka');

        $settings['add_to_cart_text']['name'] = __('A text on "add to cart" button', 'leyka');
        $settings['add_to_cart_text']['std'] = __('Add donation to cart', 'leyka');

        return $settings;
    }
    add_filter('edd_settings_misc', 'leyka_misc_settings');

    /** Common admin notices: */
    function leyka_admin_messages() {
        global $typenow, $edd_options;

        if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'payment_deleted' && current_user_can('view_shop_reports')) {
            add_settings_error('edd-notices', 'leyka-donation-deleted', __('The donations has been deleted.', 'leyka'), 'updated');
        }

        if(isset($_GET['edd-message']) && $_GET['edd-message'] == 'email_sent' && current_user_can('view_shop_reports')) {
            add_settings_error('edd-notices', 'leyka-donation-sent', __('The donation notice has been resent.', 'leyka'), 'updated');
        }

        if(isset($_GET['page']) && $_GET['page'] == 'edd-payment-history' && current_user_can('view_shop_reports') && edd_is_test_mode()) {
            add_settings_error('edd-notices', 'leyka-donation-sent', sprintf(__('Note: test mode is enabled, only test donation payments are shown below. %sSettings%s.', 'leyka'), '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings').'">', '</a>'), 'updated');
        }

        if(
            (empty($edd_options['purchase_page']) || get_post_status($edd_options['purchase_page']) ==  'trash')
         && current_user_can('edit_pages')
        ) {
            add_settings_error('edd-notices', 'set-checkout', sprintf( __('No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'leyka' ), admin_url('edit.php?post_type=download&page=edd-settings')));
        }

        settings_errors('edd-notices');
    }
    remove_action('admin_notices', 'edd_admin_messages');
    add_action('admin_notices', 'leyka_admin_messages');
}
add_action('admin_init', 'leyka_admin_init', 1);

/**
 * Recall Columns
 *
 * Defines the custom columns and their order.
 * @access      private
 */
function leyka_recall_posts_columns($recall_columns){
    $recall_columns = array(
        'cb' => '<input type="checkbox"/>',
        'recall_title' => __('Title'),
        'text' => __('Recall text', 'leyka'),
        'donor' => __('Recall author (donor)', 'leyka'),
        'gateway' => __('Gateway', 'leyka'),
        'date' => __('Date', 'edd')
    );
    return $recall_columns;
}
add_filter('manage_leyka_recall_posts_columns', 'leyka_recall_posts_columns');

/**
 * Render the recall custom columns content.
 */
function leyka_manage_posts_custom_column($column_name, $post_id){
    if(get_post_type($post_id) == 'leyka_recall') {
        $payment = get_post($post_id);
        $payment_id = reset(get_post_meta($post_id, '_leyka_payment_id'));
        $payment_meta = get_post_meta($payment_id, '_edd_payment_meta', true);
        $user_info = maybe_unserialize($payment_meta['user_info']);
        switch($column_name) {
            case 'recall_title':
                echo '<strong>'.$payment->post_title.'</strong>';?>
            <span id="recall-status-<?php echo $post_id;?>"><?php _post_states($payment);?></span>
            <?php break;
            case 'text':?>
            <div class="recall_text"><?php echo strip_tags($payment->post_content);?></div>
            <div id="actions-recall-<?php echo $post_id;?>">
                <a class="inline-edit-recall" data-recall-id="<?php echo $post_id;?>" href="#"><?php _e('Quick&nbsp;Edit');?></a> |
                <a class="submitdelete" title="<?php echo esc_attr(__('Move this item to the Trash'));?>" href="<?php echo get_delete_post_link($post_id);?>"><?php _e('Trash');?></a>
            </div>
            <div class="recall_edit_message"></div>

            <div id="edit-recall-<?php echo $post_id;?>" style="display:none;">
                <fieldset>
                    <legend><?php echo __('Edit user recall #', 'leyka').$post_id;?></legend>
                    <input type="hidden" name="leyka_nonce" value="<?php echo wp_create_nonce('leyka-edit-recall');?>" />
                    <input type="hidden" name="recall_id" value="<?php echo $post_id;?>" />
                    <input type="hidden" name="action" value="leyka-recall-edit" />
                    <label><?php _e('Status');?>:
                        <select name="recall_status">
                            <option value="publish" <?php echo ($payment->post_status == 'publish' ? 'selected' : '');?>><?php _e('Publish');?></option>
                            <option value="trash" <?php echo ($payment->post_status == 'trash' ? 'selected' : '');?>><?php _e('Trash');?></option>
                            <option value="draft" <?php echo ($payment->post_status == 'draft' ? 'selected' : '');?>><?php _e('Draft');?></option>
                            <option value="pending" <?php echo ($payment->post_status == 'pending' ? 'selected' : '');?>><?php _e('Pending');?></option>
                        </select>
                    </label>
                    <br />
                    <label><?php _e('Recall text', 'leyka');?>:
                        <textarea name="recall_text" rows="3" cols="20"><?php echo strip_tags($payment->post_content);?></textarea>
                    </label>
                    <br />
                    <br />
                    <input type="submit" class="submit-recall" data-recall-id="<?php echo $post_id;?>" value="OK" /> | <input class="reset-recall" data-recall-id="<?php echo $post_id;?>" type="reset" value="<?php _e('Cancel');?>">
                </fieldset>
            </div>
            <?php break;
            case 'donor':
                echo $user_info['first_name'].' '.$user_info['last_name'];
                break;
            case 'gateway':
                $gateway = edd_get_payment_gateway($payment_id);
                echo $gateway ? edd_get_gateway_admin_label($gateway) : '';
                break;
        }
    }
}
add_action('manage_posts_custom_column', 'leyka_manage_posts_custom_column', 10, 2);

/**
 * Sortable Recall Columns - set the sortable columns content.
 */
function leyka_recall_sortable_columns($columns){
    $columns['date'] = 'date';
//    $columns['gateway'] = 'gateway';
//    $columns['donor'] = 'donor';
    $columns['recall_title'] = 'post_title';

    return $columns;
}
add_filter('manage_edit-leyka_recall_sortable_columns', 'leyka_recall_sortable_columns');

/**
 * Updates recall text and status when saving it while editing.
 */
function leyka_recall_edit(){
    if(empty($_POST['recall_id']) || (int)$_POST['recall_id'] < 0)
        return;
    $_POST['recall_id'] = (int)$_POST['recall_id'];
    $payment = get_post($_POST['recall_id']);
    if( $payment->post_type != 'leyka_recall'
        || !current_user_can('edit_post', $_POST['recall_id'])
        || !wp_verify_nonce($_POST['leyka_nonce'], 'leyka-edit-recall') )
        die( json_encode(array('status' => 'error', 'message' => __('Permissions denied!', 'leyka'))) );

    global $wpdb;
    $_POST['recall_text'] = esc_html(stripslashes($_POST['recall_text']));
    $wpdb->update( // Not using wp_update_post inside the "save_post" action due to endless loops
        $wpdb->posts,
        array(
            'post_content' => $_POST['recall_text'],
            'post_status' => $_POST['recall_status'],
        ),
        array('ID' => $_POST['recall_id'])
    );
//    $payment = get_post($_POST['recall_id']);
    die( json_encode(array(
        'status' => 'ok',
        'data' => array(
            'recall_status_text' => __( ucfirst($_POST['recall_status']) ),
            'recall_status' => $_POST['recall_status'],
            'recall_text' => $_POST['recall_text'],
        ),
    )) );
}
add_action('wp_ajax_leyka-recall-edit', 'leyka_recall_edit');

/** Process ajax request to get gateway specific fields. */
function leyka_get_gateway_fields(){
    // Verify the nonce for this action:
    if ( !isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka-single-donate-nonce') )
        return;

    do_action('edd_purchase_form_top');

    if(edd_can_checkout()) {
        if(isset($edd_options['show_register_form']) && !is_user_logged_in() && !isset($_GET['login'])) {?>
        <div id="edd_checkout_login_register">
            <?php do_action('edd_purchase_form_register_fields');?>
        </div>
        <?php } elseif(isset($edd_options['show_register_form']) && !is_user_logged_in() && isset($_GET['login'])) {?>
        <div id="edd_checkout_login_register">
            <?php do_action('edd_purchase_form_login_fields');?>
        </div>
        <?php }
        if(( !isset($_GET['login']) && is_user_logged_in()) || !isset($edd_options['show_register_form'])) {
            do_action('edd_purchase_form_after_user_info');
        }

        do_action('edd_purchase_form_before_cc_form');

        $payment_mode = edd_get_chosen_gateway();

        // load the credit card form and allow gateways to load their own if they wish
        if(has_action('edd_'.$payment_mode.'_cc_form')) {
            do_action('edd_'.$payment_mode.'_cc_form');
        } else {
            do_action('edd_cc_form');
        }

        // Remove the default EDD hidden fields:
        remove_action('edd_purchase_form_after_cc_form', 'edd_checkout_submit', 100);

        do_action('edd_purchase_form_after_cc_form');?>

    <fieldset id="edd_purchase_submit">
        <p>
            <?php do_action('edd_purchase_form_before_submit');
            if(is_user_logged_in()) {?>
            <input type="hidden" name="edd-user-id" value="<?php echo get_current_user_id();?>"/>
            <?php }?>
            <input type="hidden" name="edd_action" value="single_donate" />
            <input type="hidden" name="edd-gateway" value="<?php echo edd_get_chosen_gateway();?>" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka-single-donate-nonce');?>" />

            <?php echo edd_checkout_button_purchase();?>

            <?php do_action('edd_purchase_form_after_submit');?>
        </p>
    </fieldset>
    <?php } else {
        // can't checkout
        do_action('edd_purchase_form_no_access');
    }

    do_action('edd_purchase_form_bottom');
    die();
}
add_action('wp_ajax_leyka-get-gateway-fields', 'leyka_get_gateway_fields');
add_action('wp_ajax_nopriv_leyka-get-gateway-fields', 'leyka_get_gateway_fields');

/** Change "Purchase" button text. */
function leyka_donate_submit_button($html){
    global $edd_options;

    $color = isset($edd_options['checkout_color']) ? $edd_options['checkout_color'] : 'gray';
    $style = isset($edd_options['button_style']) ? $edd_options['button_style'] : 'button';

    $complete_purchase = isset($edd_options['checkout_label']) && strlen(trim($edd_options['checkout_label'])) > 0 ? $edd_options['checkout_label'] : __('Make the donation', 'leyka');
    
    return '<input type="submit" class="edd-submit '.$color.' '.$style.'" id="edd-purchase-button" name="edd-purchase" value="'.$complete_purchase.'"/>';
}
add_filter('edd_checkout_button_purchase', 'leyka_donate_submit_button');

/** Process admin placeholders in admin email notification text. */
function leyka_admin_donation_notification($admin_message, $payment_id, $payment_data){
    global $edd_options;
    if(empty($payment_data['amount'])) // Some payment metadata is missing, add it to the existing data
        $payment_data = $payment_data + edd_get_payment_meta($payment_id);
    
    if(empty($edd_options['admin_donates_email_text'])) // To avoid unneeded php notices about missing var
        return '';

    $admin_message = str_replace(
        array('{donate_id}', '{edit_url}',),
        array(
            $payment_id,
            '<a href="'.site_url("/wp-admin/edit.php?post_type=download&page=edd-payment-history").'">'
            .__('Activate the donation', 'leyka').'</a>',
        ),
        nl2br($edd_options['admin_donates_email_text'])
    );
    return edd_email_template_tags($admin_message, $payment_data, $payment_id);
}
add_filter('edd_admin_purchase_notification', 'leyka_admin_donation_notification', 10, 3);

/** Add admin email notification subject. */
function leyka_admin_donation_notification_subject($payment_id, $payment_data){
    global $edd_options;
    return empty($edd_options['admin_notifications_subject']) ?
        __('New donation payment', 'leyka') :
        $edd_options['admin_notifications_subject'];
}
add_filter('edd_admin_purchase_notification_subject', 'leyka_admin_donation_notification_subject', 10, 2);

/** Add correct html-friendly headers to admin notification. */
function leyka_admin_donation_notification_headers($dummy_arr, $payment_id, $payment_data){
    global $edd_options;

    $from_name = isset( $edd_options['from_name'] ) ? $edd_options['from_name'] : get_bloginfo('name');
    $from_email = isset( $edd_options['from_email'] ) ? $edd_options['from_email'] : get_option('admin_email');
    $headers = "From: ".stripslashes_deep( html_entity_decode($from_name, ENT_COMPAT, 'UTF-8'))." <$from_email>\r\n"
        ."Reply-To: ".$from_email."\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: text/html; charset=utf-8\r\n";
    return $headers;
}
add_filter('edd_admin_purchase_notification_headers', 'leyka_admin_donation_notification_headers', 10, 3);

function leyka_admin_scripts($hook){
//    if($hook != 'edit.php')
//        return;
    wp_enqueue_script('leyka-admin-jq-plugins', LEYKA_PLUGIN_BASE_URL.'js/jq-plugins-admin.js', array('jquery'), LEYKA_VERSION);
    wp_enqueue_script('leyka-admin', LEYKA_PLUGIN_BASE_URL.'js/leyka-admin.js', array('jquery', 'leyka-admin-jq-plugins'), LEYKA_VERSION);
    wp_localize_script('leyka-admin', 'l10n', array(
        'ajax_loader' => EDD_PLUGIN_URL.'assets/images/loading.gif', // Placed in l10n just for convenience
        'recall_editing_error' => __('Error while editing the recall! Please try again later or e-mail the support team to fix it.', 'leyka'),
        'payment_status_switch_pending' => __('PENDING', 'leyka'),
        'payment_status_switch_complete' => __('COMPLETE', 'leyka'),
    ));

    wp_register_style('leyka-admin-styles', LEYKA_PLUGIN_BASE_URL.'styles/style-admin.css');
    wp_enqueue_style('leyka-admin-styles');
}
add_action('admin_enqueue_scripts', 'leyka_admin_scripts');