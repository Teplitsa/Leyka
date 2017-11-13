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

    leyka_remember_donation_data(array('donation_id' => $donation_id));

    if(empty($_POST['without_form_submission'])) {
        do_action('leyka_payment_form_submission-'.$pm[0], $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);
    }

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

    $donor_name = leyka_pf_get_donor_name_value();
    if($donor_name && !leyka_validate_donor_name($donor_name)) {

//        $error = new WP_Error('incorrect_donor_name', __('Incorrect donor name given while trying to add a donation', 'leyka'));
//        $this->add_payment_form_error($error);

    }

    $donor_email = leyka_pf_get_donor_email_value();
    if($donor_email && !leyka_validate_email($donor_email)) {

//        $error = new WP_Error('incorrect_donor_email', __('Incorrect donor email given while trying to add a donation', 'leyka'));
//        $this->add_payment_form_error($error);

    }

    $donation_id = leyka()->log_submission();

    leyka_remember_donation_data(array('donation_id' => $donation_id));

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


function leyka_process_success_form() {

    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_donor_subscription')) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong nonce in the submitted data', 'leyka'),
        )));
    } else if(empty($_POST['leyka_donation_id'])) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('No donation ID found in the submitted data', 'leyka'),
        )));
    }

    $donation = new Leyka_Donation((int)$_POST['leyka_donation_id']);
    if( !$donation ) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong donation ID in the submitted data', 'leyka'),
        )));
    }

    if(isset($_POST['leyka_donor_name']) && leyka_validate_donor_name($_POST['leyka_donor_name'])) {
        $donation->donor_name = $_POST['leyka_donor_name'];
    }

    if(isset($_POST['leyka_donor_email']) && leyka_validate_email($_POST['leyka_donor_email'])) {

        $donation->donor_email = $donation->donor_email ? $donation->donor_email : $_POST['leyka_donor_email'];
        $donation->donor_subscription_email = $_POST['leyka_donor_email'];
        $donation->donor_subscribed = $donation->campaign_id;

    }

    leyka_remembered_data('donation_id', false, true); // Delete the donor data cookie

    die(json_encode(array(
        'status' => 0,
    )));

}
add_action('wp_ajax_leyka_donor_subscription', 'leyka_process_success_form');
add_action('wp_ajax_nopriv_leyka_donor_subscription', 'leyka_process_success_form');