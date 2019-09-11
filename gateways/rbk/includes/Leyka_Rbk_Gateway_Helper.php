<?php  if( !defined('WPINC') ) die;
/**
 * Leyka_Rbk_Gateway_Helper class
 */

class Leyka_Rbk_Gateway_Helper {

    public function __construct() {
        add_action('funded_to_refunded', array($this, 'status_watcher'), 10, 1);
    }

    public function status_watcher($post) {
        if ('leyka_donation' == $post->post_type) {

            $donation = new Leyka_Donation($post->ID);
            $this->create_refund($donation);

        }
    }

    public function create_refund($donation) {

        $log = maybe_unserialize($donation->gateway_response);

        $invoice_id = $log['RBK_Hook_processed_data']['invoice']['id'];
        $payment_id = $log['RBK_Hook_processed_data']['payment']['id'];

        $args = array(
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
            'body' => json_encode(array(
                'amount' => 100 * (int)$donation->amount,
                'currency' => 'RUB',
                'reason' => 'Refunded donation'
            ))
        );

        return wp_remote_post(
            Leyka_Rbk_Gateway::RBK_API_HOST."/v2/processing/invoices/{$invoice_id}/payments/{$payment_id}/refunds",
            $args
        );

    }


}

function leyka_rbk_gateway_helper_init() {
    new Leyka_Rbk_Gateway_Helper();
}
add_action('admin_init', 'leyka_rbk_gateway_helper_init');