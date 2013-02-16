<?php
/**
 * @package Leyka
 * @subpackage Template tags and utility functions
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

/**
 * Get a number of donates.
 *
 * @param string $status Can be "publish", "pending", "refunded".
 * @return int
 */
function leyka_get_total_quantity($status = 'publish')
{
    $mode = edd_is_test_mode() ? 'test' : 'live';
    switch($status) {
        case 'pending':
        case 'incomplete':
        case 'incompleted':
            $status = 'pending';
            break;
        case 'refunded':
        case 'returned':
            $status = 'refunded';
            break;
        case 'complete':
        case 'completed':
        default:
            $status = 'publish';
            break;
    }
    $donations = get_posts(
        array(
            'numberposts' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_edd_payment_mode',
                    'value' => $mode
                )
            ),
            'post_type' => 'edd_payment',
            'post_status' => $status,
        )
    );

    return count($donations);
}

/**
 * Get total payments amount of donations.
 *
 * @param string $status Can be "publish", "pending", "refunded".
 * @return float
 */
function leyka_get_total_payments($status = 'publish')
{
    $mode = edd_is_test_mode() ? 'test' : 'live';
    switch($status) {
        case 'pending':
        case 'incomplete':
        case 'incompleted':
            $status = 'pending';
            break;
        case 'refunded':
        case 'returned':
            $status = 'refunded';
            break;
        case 'complete':
        case 'completed':
        default:
            $status = 'publish';
            break;
    }
    $donations = get_posts(
        array(
            'numberposts' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_edd_payment_mode',
                    'value' => $mode
                )
            ),
            'post_type' => 'edd_payment',
            'post_status' => $status,
        )
    );

    $payments_total = 0.0;
    foreach($donations as $donation) {
        $donation_sum = 0.0;
        foreach(get_post_meta($donation->ID, '_edd_payment_total') as $sum) {
            $donation_sum += $sum;
        }
        $payments_total += $donation_sum;
    }

    return (float)$payments_total;
}

/**
 * Add a donor recall.
 */
function leyka_insert_recall($recall_data, $return_wp_error = false)
{
    return wp_insert_post($recall_data, $return_wp_error);
}

/**
 * Add a donor recall.
 */
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

/**
 * Get max sum available for free donation.
 */
function leyka_get_max_free_donation_sum($donate_id)
{
    if($donate_id <= 0 || !leyka_is_any_sum_allowed($donate_id))
        return FALSE;
    return (float)get_post_meta($donate_id, 'leyka_max_donation_sum', TRUE);
}

/**
 * Get min sum available for free donation.
 */
function leyka_get_min_free_donation_sum($donate_id)
{
    if($donate_id <= 0 || !leyka_is_any_sum_allowed($donate_id))
        return FALSE;
    return (float)get_post_meta($donate_id, 'leyka_min_donation_sum', TRUE);
}

/**
 * Get gateway description text, if exists. Otherwise returns FALSE. 
 */
function leyka_get_gateway_description($gateway_id)
{
    if(empty($gateway_id))
        return FALSE;

    global $edd_options;
    return empty($edd_options[$gateway_id.'_desc']) ? FALSE : $edd_options[$gateway_id.'_desc'];
}

/**
 * Return TRUE if original EDD plugin is active, FALSE otherwise.
 */
function leyka_is_edd_active()
{
    return in_array('easy-digital-downloads/easy-digital-downloads.php', (array)get_option('active_plugins', array()));
}

/**
 * Utility function. Correct redirection to the page of single donate.
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