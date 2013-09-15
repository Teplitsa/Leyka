<?php
/**
 * @package Leyka
 * @subpackage Settings -> Emails tab modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Changes in on Settings->Emails admin section:
function leyka_emails_settings($settings){

    unset($settings['email_template']);

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
    $settings['purchase_receipt']['desc'] = __('Enter the text of email that will be sent to donor (if he opted to receive such email). HTML is accepted. Available template tags:', 'leyka').'<br/>'.
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

    $settings['disable_admin_notices']['name'] = __('Disable donations managers notifications', 'leyka');
    $settings['disable_admin_notices']['desc'] = __('Check if you do not want to receive emails when no donations are made.', 'leyka');

    $settings['sale_notification_header']['name'] = '<strong>' . __("Manager's notification", 'leyka');

    $settings['sale_notification_subject']['name'] = __("Donations manager's notification subject", 'leyka');
    $settings['sale_notification_subject']['desc'] = __("Enter the donations manager's notification email subject", 'leyka');
    $settings['sale_notification_subject']['std'] = __("New donation came", 'leyka');

    $settings['sale_notification']['name'] = __("Donations manager's notification template", 'leyka');
    $settings['sale_notification']['desc'] = __('Enter the text of email that will be sent to donations managers on donation request. HTML is accepted. Available template tags:', 'leyka').'<br/>'.
        '{download_list} - '.__('A list of donates given', 'leyka').'<br/>'.
        '{name} - '.__('The donor\'s name', 'leyka').'<br/>'.
        '{date} - '.__('The date of the donation', 'leyka').'<br/>'.
        '{price} - '.__('The total amount of the donation', 'leyka').'<br/>'.
        '{receipt_id} - '.__('The unique ID number for this donation', 'leyka').'<br/>'.
        '{payment_method} - '.__('The method of payment used for this donation', 'leyka').'<br/>'.
        '{sitename} - '.__('Your site name', 'edd');
    $settings['sale_notification']['std'] =  __('Hello!<br /><br />Recently, there has been a new donation on a {sitename}:<br />{download_list}<br />which totally cost {price}, by the {payment_method} gateway.<br /><br />Donate ID: {donate_id}, donation hashcode: {receipt_id} | {edit_url}<br /><br />{sitename}, {date}', 'leyka');
    $settings['admin_notice_emails']['desc'] = __("Email addresses of donation managers. One email per line.", 'leyka');

    return $settings;
}
add_filter('edd_settings_emails', 'leyka_emails_settings');

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
