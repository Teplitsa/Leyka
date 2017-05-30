<?php if( !defined('WPINC') ) die;
/** Different ajax handler functions */

function leyka_submit_donation() {

    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_payment_form')) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong nonce in submitted form data', 'leyka'),
        )));
    }

    leyka()->clear_session_errors(); // Clear all previous submits errors, if there are some

    $pm = explode('-', $_POST['leyka_payment_method']);
    if( !$pm || count($pm) < 2 ) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong gateway or/and payment method in submitted form data', 'leyka'),
        )));
    }

    $donation_id = leyka()->log_submission();

    do_action('leyka_payment_form_submission-'.$pm[0], $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);

    $payment_vars = array('status' => $donation_id && !is_wp_error($donation_id) ? 0 : 1,);
    if($payment_vars['status'] == 0) {
        $payment_vars['donation_id'] = $donation_id;
    } else {
        $payment_vars['errors'] = $donation_id;
    }

    $payment_vars = array_merge(
        apply_filters('leyka_submission_form_data-'.$pm[0], $_POST, $pm[1], $donation_id),
        $payment_vars
    );

    die(json_encode($payment_vars));

}
add_action('wp_ajax_leyka_ajax_donation_submit', 'leyka_submit_donation');
add_action('wp_ajax_nopriv_leyka_ajax_donation_submit', 'leyka_submit_donation');

function leyka_ajax_get_campaigns_list() { // leyka_get_campaigns_list() is already taken

    if(empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka_get_campaigns_list_nonce')) {
        die(json_encode(array()));
    }

    $_REQUEST['term'] = empty($_REQUEST['term']) ? '' : trim($_REQUEST['term']);

    $campaigns = leyka_get_campaigns_list(array(
        'meta_query' => array(array(
            'key' => 'payment_title', 'value' => $_REQUEST['term'], 'compare' => 'LIKE', 'type' => 'CHAR',
        )),
    ), 0);

    $ids_found = array();
    $count = count($campaigns);
    for($i=0; $i<$count; $i++) {

        $ids_found[] = $campaigns[$i]->ID;
        $campaigns[$i] = array(
            'value' => $campaigns[$i]->ID,
            'label' => $campaigns[$i]->post_title,
            'payment_title' => get_post_meta($campaigns[$i]->ID, 'payment_title', true),
        );
    }

    foreach(leyka_get_campaigns_list(array('s' => $_REQUEST['term']), 0) as $campaign) { // Any criteria search - low priority
        if( !in_array($campaign->ID, $ids_found) ) {
            $campaigns[] = array(
                'value' => $campaign->ID,
                'label' => $campaign->post_title,
                'payment_title' => get_post_meta($campaign->ID, 'payment_title', true),
            );
        }
    }

    die(json_encode($campaigns));

}
add_action('wp_ajax_leyka_get_campaigns_list', 'leyka_ajax_get_campaigns_list');
add_action('wp_ajax_nopriv_leyka_get_campaigns_list', 'leyka_ajax_get_campaigns_list');

function leyka_recalculate_total_funded_action() {

    if( !wp_verify_nonce($_GET['nonce'], 'leyka_recalculate_total_funded_amount') ) {
        wp_die(__('Error: incorrect request parameters', 'leyka'));
    }

    if(empty($_GET['campaign_id'])) {
        wp_die(__('Error: campaign ID is missing', 'leyka'));
    }

    $campaign = new Leyka_Campaign((int)$_GET['campaign_id']);
    $campaign->update_total_funded_amount();

    wp_die($campaign->total_funded);

}
add_action('wp_ajax_leyka_recalculate_total_funded_amount', 'leyka_recalculate_total_funded_action');
add_action('wp_ajax_nopriv_leyka_recalculate_total_funded_amount', 'leyka_recalculate_total_funded_action');


function leyka_get_gateway_redirect_data() {

    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_payment_form')) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong nonce in submitted form data', 'leyka'),
        )));
    }

    leyka()->clear_session_errors(); // Clear all previous submits errors, if there are some

    $pm = explode('-', $_POST['leyka_payment_method']);
    if( !$pm || count($pm) < 2 ) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong gateway or/and payment method in submitted form data', 'leyka'),
        )));
    }

    $donation_id = leyka()->log_submission();

    do_action('leyka_payment_form_submission-'.$pm[0], $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);

    $payment_vars = array(
        'status' => $donation_id && !is_wp_error($donation_id) ? 0 : 1,
        'payment_url' => apply_filters('leyka_submission_redirect_url-'.$pm[0], '', $pm[1]),
    );
    if($payment_vars['status'] == 0) {
        $payment_vars['donation_id'] = $donation_id;
    } else {
        $payment_vars['errors'] = $donation_id;
    }

    $payment_vars = array_merge(
        apply_filters('leyka_submission_form_data-'.$pm[0], $_POST, $pm[1], $donation_id),
        $payment_vars
    );

    die(json_encode($payment_vars));

}
add_action('wp_ajax_leyka_ajax_get_gateway_redirect_data', 'leyka_get_gateway_redirect_data');
add_action('wp_ajax_nopriv_leyka_ajax_get_gateway_redirect_data', 'leyka_get_gateway_redirect_data');