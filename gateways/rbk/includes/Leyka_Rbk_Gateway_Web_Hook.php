<?php

class Leyka_Rbk_Gateway_Web_Hook
{
    public static $rbk_host = 'https://api.rbk.money';

    public static function run()
    {
        if (false !== get_option('leyka_rbk_api_web_hook_key', false)) {
            add_action('leyka_rbk_gateway_web_hook', array(__CLASS__, 'hook'));
        }
    }

    public static function hook()
    {
        $data = file_get_contents('php://input');
        Leyka_Rbk_Gateway_Web_Hook_Verification::verify_header_signature($data);

        $hook_data = json_decode($data, true);

        if ('PaymentRefunded' == $hook_data['eventType']) {
            self::change_donation_status($hook_data);
        } else if ('InvoicePaid' == $hook_data['eventType']) {
            self::change_donation_status($hook_data);
        } else if ('PaymentProcessed' == $hook_data['eventType']) {
            self::processed_log($hook_data);
            self::invoice_capture($hook_data);
        } else if (in_array($hook_data['eventType'], array('PaymentFailed', 'InvoiceCancelled', 'PaymentCancelled'))) {
            self::donation_failed($hook_data);
        }

        die();
    }

    public static function donation_failed($data)
    {
        $donation_id = self::get_payment_id_by_response_data($data);
        $donation = new Leyka_Donation($donation_id);
        $log = $donation->__get('gateway_response');
        $log = maybe_unserialize($log);
        $log['RBK_Hook_failed_data'] = $data;
        $donation->add_gateway_response($log);

        return wp_update_post(array('ID' => $donation_id, 'post_status' => 'failed'));
    }

    public static function processed_log($data)
    {
        $donation_id = self::get_payment_id_by_response_data($data);
        $donation = new Leyka_Donation($donation_id);
        $log = $donation->__get('gateway_response');
        $log = maybe_unserialize($log);
        $log['RBK_Hook_processed_data'] = $data;
        $donation->add_gateway_response($log);
    }

    public static function get_payment_id_by_response_data($data)
    {
        global $wpdb;
        return $wpdb->get_var("
			SELECT post_id FROM 
			{$wpdb->postmeta}
			WHERE meta_key = '_leyka_donation_id_on_gateway_response'
			AND meta_value  = '{$data['invoice']['id']}'
			LIMIT 1
		");
    }

    public static function invoice_capture($data)
    {
        $donation_id = self::get_payment_id_by_response_data($data);
        $donation = new Leyka_Donation($donation_id);
        $log = maybe_unserialize($donation->__get('gateway_response'));
        $api_key = leyka_options()->opt('leyka_rbk_api_key');
        $invoice_id = $log['RBK_Hook_processed_data']['invoice']['id'];
        $payment_id = $log['RBK_Hook_processed_data']['payment']['id'];

        $url = self::$rbk_host . "/v2/processing/invoices/{$invoice_id}/payments/{$payment_id}/capture";
        $args = array(
            'timeout' => 30,
            'redirection' => 10,
            'blocking' => true,
            'httpversion' => '1.1',
            'headers' => array(
                'X-Request-ID' => uniqid(),
                'Authorization' => "Bearer {$api_key}",
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json'
            ),
            'body' => json_encode(array(
                'reason' => 'Donation auto capture'
            ))
        );
        return wp_remote_post($url, $args);
    }

    public static function change_donation_status($data)
    {
        global $wpdb;

        $map_status = array(
            'InvoicePaid' => 'funded',
            'PaymentRefunded' => 'refunded'
        );

        $donation_id = $wpdb->get_var("
			SELECT post_id FROM 
			{$wpdb->postmeta}
			WHERE meta_key = '_leyka_donation_id_on_gateway_response'
			AND meta_value  = '{$data['invoice']['id']}'
			LIMIT 1
		");

        if (is_numeric($donation_id) && array_key_exists($data['eventType'], $map_status)) {
            $status = $map_status[$data['eventType']];
            wp_update_post(array('ID' => $donation_id, 'post_status' => $status));
        }
    }

}

Leyka_Rbk_Gateway_Web_Hook::run();
