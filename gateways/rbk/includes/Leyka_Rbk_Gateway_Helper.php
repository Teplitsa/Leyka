<?php

class Leyka_Rbk_Gateway_Helper
{

    public function __construct()
    {
        add_action('funded_to_refunded', array($this, 'status_watcher'), 10, 1);
    }

    public function status_watcher($post)
    {
        if ('leyka_donation' == $post->post_type) {
            $donation = new Leyka_Donation($post->ID);
            $this->create_refund($donation);
        }
    }

    public function create_refund($donation)
    {
        $log = maybe_unserialize($donation->__get('gateway_response'));
        $api_key = leyka_options()->opt('leyka_rbk_api_key');
        $invoice_id = $log['RBK_Hook_processed_data']['invoice']['id'];
        $payment_id = $log['RBK_Hook_processed_data']['payment']['id'];

        $url = Leyka_Rbk_Gateway_Web_Hook::$rbk_host . "/v2/processing/invoices/{$invoice_id}/payments/{$payment_id}/refunds";
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
                'amount' => $donation->__get('amount') * 100,
                'currency' => 'RUB',
                'reason' => 'Refunded donation'
            ))
        );

        $request = wp_remote_post($url, $args);

        return $request;
    }


}

function Leyka_Rbk_Gateway_Helper_init()
{
    new Leyka_Rbk_Gateway_Helper();
}

add_action('admin_init', 'Leyka_Rbk_Gateway_Helper_init');
