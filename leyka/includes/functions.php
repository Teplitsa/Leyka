<?php
/**
 * @package Leyka
 * @subpackage Utility functions
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** For compatibility with PHP < 5.3. */
if( !function_exists('lcfirst') ) {
    function lcfirst($str)
    {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}

/** Get latest EDD version, or FALSE if it's not defined. */
function get_latest_edd_version()
{
    $version = 0;
    $lines = @file('http://plugins.svn.wordpress.org/easy-digital-downloads/trunk/readme.txt');
    foreach((array)$lines as $line) {
        if(stripos($line, 'Stable tag:') !== FALSE) {
            $version = trim(str_replace('Stable Tag:', '', $line));
            break;
        }
    }

    return (float)$version;
}

/** Wrapper to insert new payment and it's correspondent recall, or redirect back if needed. */
function leyka_insert_payment($payment_data = array())
{
    if( !$payment_data )
        return FALSE;

    global $edd_options;

    $payment_data['user_info']['last_name'] = ''; // We won't store the last name for privacy reasons

    // Process the payment on our side. Create the record for payment:
    $init_status = $edd_options['leyka_payments_default_status'] == 'publish' ?
                       'pending' : $edd_options['leyka_payments_default_status'];
    $payment_id = edd_insert_payment(array(
        'post_data' => $payment_data['post_data'],
        'price' => $payment_data['price'],
        'date' => $payment_data['date'],
        'user_email' => $payment_data['user_email'],
        'purchase_key' => $payment_data['purchase_key'],
        'currency' => $edd_options['currency'],
        'downloads' => $payment_data['downloads'],
        'user_info' => $payment_data['user_info'],
        'cart_details' => $payment_data['cart_details'],
        'status' => $init_status
    ));
    if($payment_id && $edd_options['leyka_payments_default_status'] == 'publish')
        edd_update_payment_status($payment_id, 'publish');

    if( !$payment_id ) {
        // If errors are present, send the user back to the donation page so they can be corrected:
        if(empty($payment_data['single_donate_id']))
            edd_send_back_to_checkout('?payment-mode='.$payment_data['post_data']['edd-gateway']);
        else
            leyka_send_back_to_single_donate(
                $payment_data['single_donate_id'],
                $payment_data['post_data']['edd-gateway']
            );
    }
}

/** Update a donor recall. */
function leyka_update_recall($recall_id, $recall_data, $return_wp_error = false)
{
    if((int)$recall_id <= 0)
        return false;
    $recall_data['ID'] = (int)$recall_id;
    return wp_update_post($recall_data, $return_wp_error);
}

/**
 * Check if any sum available for donation (free donating).
 *
 * @param $donate_id integer
 * @return boolean
 */
function leyka_is_any_sum_allowed($donate_id)
{
    if( !$donate_id || $donate_id <= 0)
        return FALSE;

    return !!get_post_meta($donate_id, 'leyka_any_sum_allowed', TRUE);
}

/**  Returns TRUE if original EDD plugin is active, FALSE otherwise. */
function leyka_is_edd_active()
{
    return in_array('easy-digital-downloads/easy-digital-downloads.php', (array)get_option('active_plugins', array()));
}

/**
 * Correct redirection to the page of single donate.
 * Used instead of edd_send_back_to_checkout() sometimes.
 *
 * @param $donate_id
 * @param bool $gateway_selected
 * @return bool
 */
function leyka_send_back_to_single_donate($donate_id, $gateway_selected = FALSE)
{
    $donate_id = (int)$donate_id;
    if($donate_id <= 0)
        return false;

    $permalink = get_permalink($donate_id);
    wp_redirect(
        $permalink.(strpos($permalink, '?') === FALSE ? '?' : '&')
            .($gateway_selected ? 'payment-mode='.trim($gateway_selected) : '')
    );
}

/**
 * Wrapper to restore previous cart state that has been saved before.
 * 
 * @param $cart_data array Cart state array, a result of the edd_get_cart_contents() function call.
 * @return bool TRUE if success, FALSE otherwise.
 */
function leyka_restore_cart_state($cart_data)
{
    if( !is_array($cart_data) )
        return FALSE;
    return EDD()->session->set('edd_cart', $cart_data);
}