<?php  if( !defined('WPINC') ) die;
/**
 * Leyka_Rbk_Gateway_Helper class
 */

class Leyka_Rbk_Gateway_Helper {

    public function __construct() {

        $gateway = leyka_get_gateway_by_id('rbk');
        if($gateway && $gateway->get_activation_status() === 'active') {
            add_action('leyka_donation_status_funded_to_refunded', [$this, 'status_watcher'], 10);
        }

    }

    public function status_watcher(Leyka_Donation_Base $donation) {
        $this->create_refund($donation);
    }

    public function create_refund(Leyka_Donation_Base $donation) {

        $log = maybe_unserialize($donation->gateway_response);

        $invoice_id = $log['RBK_Hook_processed_data']['invoice']['id'];
        $payment_id = $log['RBK_Hook_processed_data']['payment']['id'];

        return wp_remote_post(
            Leyka_Rbk_Gateway::RBK_API_HOST."/v2/processing/invoices/{$invoice_id}/payments/{$payment_id}/refunds",
            [
                'timeout' => 30,
                'redirection' => 10,
                'blocking' => true,
                'httpversion' => '1.1',
                'headers' => [
                    'X-Request-ID' => uniqid(),
                    'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                    'Content-type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode([
                    'amount' => 100 * (int)$donation->amount,
                    'currency' => 'RUB',
                    'reason' => __('Refunded donation', 'leyka'),
                ])
            ]
        );

    }

}

function leyka_rbk_gateway_helper_init() {
    new Leyka_Rbk_Gateway_Helper();
}
add_action('admin_init', 'leyka_rbk_gateway_helper_init');