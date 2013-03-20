<?php
/**
 * @package Leyka
 * @subpackage Template tags and utility functions
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

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
 * Get total number of donations.
 *
 * @param string $status Can be "publish", "pending", "refunded".
 * @return float
 */
function leyka_get_donations_number($status = 'publish')
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

/**  Get max sum available for free donation. */
function leyka_get_max_free_donation_sum($donate_id)
{
    if($donate_id <= 0 || !leyka_is_any_sum_allowed($donate_id))
        return FALSE;
    return (float)get_post_meta($donate_id, 'leyka_max_donation_sum', TRUE);
}

/**  Get min sum available for free donation. */
function leyka_get_min_free_donation_sum($donate_id)
{
    if($donate_id <= 0 || !leyka_is_any_sum_allowed($donate_id))
        return FALSE;
    return (float)get_post_meta($donate_id, 'leyka_min_donation_sum', TRUE);
}

/**  Get gateway description text, if exists. Otherwise returns FALSE. */
function leyka_get_gateway_description($gateway_id)
{
    if(empty($gateway_id))
        return FALSE;

    global $edd_options;
    return empty($edd_options[$gateway_id.'_desc']) ? FALSE : $edd_options[$gateway_id.'_desc'];
}