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

/**
 * Insert new payment and correspondent recall, or redirect back if needed.
 */
function leyka_insert_payment($payment_data = array(), $settings = array())
{
    if( !$payment_data )
        return FALSE;
    
    // Default operation settings:
    $settings = $settings + array('add_recall' => TRUE,);

    global $edd_options;

    // Process the payment on our side:
    // Create the record for pending payment
    $payment_id = edd_insert_payment(array(
        'price' => $payment_data['price'],
        'date' => $payment_data['date'],
        'user_email' => $payment_data['user_email'],
        'purchase_key' => $payment_data['purchase_key'],
        'currency' => $edd_options['currency'],
        'downloads' => $payment_data['downloads'],
        'user_info' => $payment_data['user_info'],
        'cart_details' => $payment_data['cart_details'],
        'status' => $edd_options['leyka_payments_default_status']
    ));

    if($payment_id) {
        if($payment_data['post_data']['donor_comments'] && !empty($settings['add_recall'])) {
            $recall = leyka_insert_recall(array(
                'post_content' => $payment_data['post_data']['donor_comments'],
                'post_type' => 'leyka_recall',
                'post_status' => $edd_options['leyka_recalls_default_status'],
                'post_title' => 'title',
            ));
            if($recall) {
                // Update the title and slug:
                leyka_update_recall($recall, array(
                    'post_title' => __('Recall', 'leyka').' #'.$recall,
                    'post_name' => __('recall', 'leyka').'-'.$recall,
                ));
                // Update recall metadata:
                update_post_meta($recall, '_leyka_payment_id', $payment_id);
            }
        }
        if( !empty($payment_data['post_data']['leyka_send_donor_email_conf']) )
            edd_email_purchase_receipt($payment_id, FALSE);
        if(empty($payment_data['amount']))
            $payment_data = edd_get_payment_meta($payment_id);
        edd_admin_email_notice($payment_id, $payment_data);
        edd_empty_cart();
    } else {
        // if errors are present, send the user back to the purchase page so they can be corrected
        if(empty($payment_data['single_donate_id']))
            edd_send_back_to_checkout('?payment-mode='.$payment_data['post_data']['edd-gateway']);
        else
            leyka_send_back_to_single_donate(
                $payment_data['single_donate_id'], $payment_data['post_data']['edd-gateway']
            );
    }
}