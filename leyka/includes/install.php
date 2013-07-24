<?php
/**
 * @package Leyka
 * @subpackage Install routines
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Activation routine:
function leyka_activation()
{
    // Check if original EDD exists and active:
    if( !file_exists(WP_PLUGIN_DIR.'/easy-digital-downloads/easy-digital-downloads.php') ) {
        function leyka_edd_not_found(){
            echo __('<div id="message" class="error"><p><strong>Original EDD plugin is not found.</strong> Please, try to download and activate it before activating Leyka.</p></div>', 'leyka');

            if( !function_exists('deactivate_plugins') )
                require_once(ABSPATH.'wp-admin/includes/plugin.php');
            deactivate_plugins(LEYKA_PLUGIN_INNER_NAME);
        }
        add_action('admin_notice', 'leyka_activation_edd_not_found');

        return;
    }

    global $edd_options;

    /** Set default Email settings. */
    // Direct settings manipulation BEGINS
    $emails_options = get_option('edd_settings_emails');

    if(empty($emails_options['purchase_receipt']))
        $emails_options['purchase_receipt'] = __('Hello, {name}!<br /><br />You have chosed to make the following donations:<br />{download_list}<br />which totally cost {price}, by the {payment_method} gateway.<br /><br />Sincerely thank you, {sitename}, {date}', 'leyka');

    if(empty($emails_options['purchase_subject']))
        $emails_options['purchase_subject'] = __('Thank you for your donation!', 'leyka');

    if(empty($emails_options['from_name'])) {
        $from_name = get_bloginfo('name');
        if( !$from_name )
            $from_name = trim(str_replace(array('http://', 'https://'), array('', ''), get_bloginfo('wpurl')), '/');
        $emails_options['from_name'] = $from_name;
    }

    if(empty($emails_options['from_email']))
        $emails_options['from_email'] = get_bloginfo('admin_email');

    if(empty($emails_options['admin_notice_emails']))
        $emails_options['admin_notice_emails'] = get_bloginfo('admin_email');
    
    if(empty($emails_options['admin_notifications_subject']))
        $emails_options['admin_notifications_subject'] = __('New donation came', 'leyka');

    if(empty($emails_options['admin_donates_email_text']))
        $emails_options['admin_donates_email_text'] = __('Hello!<br /><br />Recently, there has been a new donation on a {sitename}:<br />{download_list}<br />which totally cost {price}, by the {payment_method} gateway.<br /><br />Donate ID: {donate_id}, donation hashcode: {receipt_id} | {edit_url}<br /><br />{sitename}, {date}', 'leyka');
    
    update_option('edd_settings_emails', $emails_options);
    // Direct settings manipulation ENDS

    add_option('leyka_just_activated', 1);

//    elseif( !is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') ) {
//        activate_plugin(WP_PLUGIN_DIR.'/easy-digital-downloads/easy-digital-downloads.php');
//        if($res !== NULL) { // Can't activate base EDD for some reason, fatal
//            echo __('<div id="message" class="error"><strong>Error while activating the original EDD plugin.</strong> EDD plugin hadn\'t been activated automatically. Please, activate in manually and then activate this plugin again.</div>', 'leyka');
////            exit;
//        }
//    }
}
register_activation_hook(LEYKA_PLUGIN_INNER_SHORT_NAME, 'leyka_activation');

// Deactivation routine:
//register_deactivation_hook(LEYKA_PLUGIN_INNER_SHORT_NAME, function(){
//    if(is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
//        deactivate_plugins(EDD_PLUGIN_FILE);
//    }
//});