<?php
/**
 * @package Leyka
 * @subpackage Global admin panel modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Do actions, if given:
if( !empty($_GET['leyka_action']) ) {
    do_action('leyka_'.$_GET['leyka_action'], $_GET);
}
if( !empty($_POST['leyka_action']) ) {
    do_action('leyka_'.$_POST['leyka_action'], $_POST);
}

// Add RUR currency support:
function leyka_add_rur_support($currencies){
    $currencies['р.'] = __('Russian rouble (RUR)', 'leyka'); // We have our code in UTF, so we _can_ make it ^^
    return $currencies;
}
add_filter('edd_currencies', 'leyka_add_rur_support');

// Remove categories and tags as a whole. Must be done in the "init" hook with priority below 10
remove_action('init', 'edd_setup_download_taxonomies');
remove_action('restrict_manage_posts', 'edd_add_download_filters', 100);

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
                $success = @copy(
                    LEYKA_PLUGIN_DIR.'edd_templates/'.$file,
                    $current_theme_dir.'/edd_templates/'.$file
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
            <p><?php echo __("<b>Warning:</b> there's no edd_templates subdirectory in the current theme folder.<br /><br />To fix this, please copy «edd_templates» directory from Leyka plugin folder to your current theme folder.", 'leyka');?></p>
        </div>
        <?php }
        add_action('admin_notices', 'leyka_templates_admin_notices');
    }
}

function leyka_admin_menu(){
    global $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_add_ons_page, $edd_recalls_page, $edd_upgrades_screen, $edd_system_info_page;

    require_once EDD_PLUGIN_DIR.'includes/admin/system-info.php';

    // Payment history page handling function is changed due to UI reasons.
    // Also, Discounts page is removed:
    $edd_payments_page = add_submenu_page('edit.php?post_type=download', __('Donations history', 'leyka'), __('Donations history', 'leyka'), 'manage_options', 'edd-payment-history', 'leyka_donations_history_page');
    $edd_recalls_page = add_submenu_page('edit.php?post_type=download', __('Donor recalls', 'leyka'), __('Donor recalls', 'leyka'), 'manage_options', 'leyka-recalls', 'leyka_recalls_page');
    $edd_reports_page = add_submenu_page('edit.php?post_type=download', __('Donations reports', 'leyka'), __('Reports', 'leyka'), 'manage_options', 'edd-reports', 'leyka_reports_page');
    $edd_settings_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download Settings', 'edd'), __('Settings', 'edd'), 'manage_options', 'edd-settings', 'edd_options_page');
    $edd_system_info_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download System Info', 'edd' ), __('System Info', 'edd'), 'manage_options', 'edd-system-info', 'edd_system_info');
    // Add-ons page removed until further testing for their compatibility with Leyka:
//    $edd_add_ons_page = add_submenu_page('edit.php?post_type=download', __('Easy Digital Download Add Ons', 'edd'), __('Add Ons', 'edd'), 'manage_options', 'edd-addons', 'edd_add_ons_page');
}
remove_action('admin_menu', 'edd_add_options_link', 10);
add_action('admin_menu', 'leyka_admin_menu');

/** Common admin notices: */
function leyka_admin_messages(){
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

    if( !isset($edd_options['leyka_receiver_is_private']) ) {
        add_settings_error('edd-notices', 'set-receiver-type', sprintf( __('You have not set your donations receiver options. Visit <a href="%s">settings</a> to configure them.', 'leyka' ), admin_url('edit.php?post_type=download&page=edd-settings&tab=misc')));
    } else if(
        $edd_options['leyka_receiver_is_private'] == 0 &&
        (
            empty($edd_options['leyka_receiver_legal_name']) ||
            empty($edd_options['leyka_receiver_legal_face']) ||
            empty($edd_options['leyka_receiver_legal_face_rp']) ||
            empty($edd_options['leyka_receiver_legal_face_position']) ||
            empty($edd_options['leyka_receiver_legal_state_reg_number']) ||
            empty($edd_options['leyka_receiver_legal_kpp']) ||
            empty($edd_options['leyka_receiver_legal_address']) ||
            empty($edd_options['leyka_receiver_legal_bank_essentials'])
        )
    ) {
        add_settings_error('edd-notices', 'set-receiver-type-settings', sprintf( __('Some of your donations receiver options are not set. All of them are required. Visit <a href="%s">settings</a> to configure them.', 'leyka' ), admin_url('edit.php?post_type=download&page=edd-settings&tab=misc')));
    }

    settings_errors('edd-notices');
}
remove_action('admin_notices', 'edd_admin_messages');
add_action('admin_notices', 'leyka_admin_messages');

/** On each new donation payment we'll insert donor recall and send email notices. */
function leyka_on_donation_insert($payment_id, $payment_data){
    global $edd_options;

    if($payment_data['post_data']['donor_comments']) { // Insert new donor recall, if needed
        $recall_id = wp_insert_post(array(
            'post_content' => $payment_data['post_data']['donor_comments'],
            'post_type' => 'leyka_recall',
            'post_status' => $edd_options['leyka_recalls_default_status'],
            'post_title' => 'title',
        ));
        if($recall_id) {
            leyka_update_recall($recall_id, array( // Update recall's title and slug
                'post_title' => __('Recall', 'leyka').' #'.$recall_id,
                'post_name' => __('recall', 'leyka').'-'.$recall_id,
            ));
            update_post_meta($recall_id, '_leyka_payment_id', $payment_id); // Update recall metadata
        }
    }

    if( !empty($payment_data['post_data']['leyka_send_donor_email_conf']) )
        edd_email_purchase_receipt($payment_id, FALSE);
    if(empty($payment_data['amount']))
        $payment_data = edd_get_payment_meta($payment_id);
    edd_admin_email_notice($payment_id, $payment_data);
    edd_empty_cart();
}
add_action('edd_insert_payment', 'leyka_on_donation_insert', 10, 2);

/** Updating donations log entries when donation status is set to something else than "publish". */
function leyka_update_donation_status($donation_id, $new_status, $old_status){
    if($new_status == $old_status)
        return;
    if($new_status == 'publish' || $new_status == 'complete')
        return;

    $donates = edd_get_payment_meta_downloads($donation_id);
    if(is_array($donates)) {
        foreach($donates as $donate) { // Update sale counts and earnings for all purchased products
            edd_undo_purchase($donate['id'], $donation_id);
        }
    }

    global $edd_logs;
    $edd_logs->delete_logs( // Remove related donation log entries
        NULL,
        'sale',
        array(array('key' => '_edd_log_payment_id', 'value' => $donation_id))
    );
}
add_action('edd_before_payment_status_change', 'leyka_update_donation_status', 10, 3);

// Add JS and CSS to admin area:
function leyka_admin_scripts($hook){
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