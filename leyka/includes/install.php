<?php
/**
 * @package Leyka
 * @subpackage Install routines
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

// Activation routine:
function leyka_activation()
{
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

    // Check if original EDD exists:
    if( !file_exists(WP_PLUGIN_DIR.'/easy-digital-downloads/easy-digital-downloads.php') ) { // Base EDD is not found, fatal
        echo __('<div id="message" class="error"><strong>Original EDD plugin is not found.</strong> Please, try to download and activate it before activating Leyka.</div>', 'leyka');
    }

    global $edd_options;

    /** Set default Email settings. */
    // Direct settings manipulation:
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
    // Direct settings manipulation END


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