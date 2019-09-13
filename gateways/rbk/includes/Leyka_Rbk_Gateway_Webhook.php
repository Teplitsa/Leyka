<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Rbk_Gateway_Webhook class
 */

/** @TODO ATM this file is not included, all it's functions are merged in the main gateway class. REMOVE THE FILE when debugging is finished. */

class Leyka_Rbk_Gateway_Webhook {

    public static function run() {
        if(false !== get_option('leyka_rbk_api_web_hook_key', false)) {
            add_action('Leyka_Rbk_Gateway_Webhook', array(__CLASS__, 'hook'));
        }
    }

    public static function hook() {

        $data = file_get_contents('php://input');
        $check = Leyka_Rbk_Gateway_Webhook_Verification::verify_header_signature($data);

        if(is_wp_error($check)) {
            wp_die($check->get_error_message());
        }

        $hook_data = json_decode($data, true);
        if('PaymentRefunded' == $hook_data['eventType']) {
            self::handle_donation_status_change($hook_data);
        } else if('InvoicePaid' == $hook_data['eventType']) {
            self::handle_donation_status_change($hook_data);
        } else if('PaymentProcessed' == $hook_data['eventType']) {
            self::handle_payment_processed($hook_data);
        } else if(in_array($hook_data['eventType'], array('PaymentFailed', 'InvoiceCancelled', 'PaymentCancelled'))) {
            self::handle_donation_failed($hook_data);
        }

    }

    public static function handle_donation_failed($data) {

        $donation_id = self::get_donation_by_invoice_id($data['invoice']['id']);
        $donation = new Leyka_Donation($donation_id);

        $log = $donation->gateway_response;
        $log = maybe_unserialize($log);

        $log['RBK_Hook_failed_data'] = $data;

        $donation->add_gateway_response($log);

        return wp_update_post(array('ID' => $donation_id, 'post_status' => 'failed'));

    }

    public static function get_donation_by_invoice_id($invoice_id) {

        global $wpdb;
        return $wpdb->get_var( // '_leyka_donation_id_on_gateway_response'
            "SELECT `post_id` FROM 
			{$wpdb->postmeta}
			WHERE `meta_key` = '_leyka_rbk_invoice_id'
			AND `meta_value`  = '$invoice_id'
			LIMIT 1"
        );

    }

    public static function handle_donation_status_change($data) {

        $map_status = array('InvoicePaid' => 'funded', 'PaymentRefunded' => 'refunded',);
        $donation_id = self::get_donation_by_invoice_id($data['invoice']['id']);

        if(is_numeric($donation_id) && array_key_exists($data['eventType'], $map_status)) {

            $donation = new Leyka_Donation($donation_id);
            $donation->status = $map_status[$data['eventType']];

        }

    }

    public static function handle_payment_processed($data) {

        // Log the webhook request content:
        $donation_id = self::get_donation_by_invoice_id($data['invoice']['id']);
        $donation = new Leyka_Donation($donation_id);

        $log = maybe_unserialize($donation->gateway_response);
        $log['RBK_Hook_processed_data'] = $data;

        $donation->add_gateway_response($log);

        // Capture invoice:
        $invoice_id = $log['RBK_Hook_processed_data']['invoice']['id'];
        $payment_id = $log['RBK_Hook_processed_data']['payment']['id'];

        return wp_remote_post(
            Leyka_Rbk_Gateway::RBK_API_HOST."/v2/processing/invoices/{$invoice_id}/payments/{$payment_id}/capture",
            array(
                'timeout' => 30,
                'redirection' => 10,
                'blocking' => true,
                'httpversion' => '1.1',
                'headers' => array(
                    'X-Request-ID' => uniqid(),
                    'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                    'Content-type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json'
                ),
                'body' => json_encode(array('reason' => 'Donation auto capture',))
            )
        );

    }

}

Leyka_Rbk_Gateway_Webhook::run();