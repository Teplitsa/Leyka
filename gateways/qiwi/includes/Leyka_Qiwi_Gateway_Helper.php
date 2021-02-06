<?php if (!defined('WPINC')) {
    die;
}

class Leyka_Qiwi_Gateway_Helper {

    private $_key;

    public static $map_status = array(
        'PAID' => 'funded',
        'WAITING' => 'submitted',
        'REJECTED' => 'failed',
        'PARTIAL' => 'refunded',
        'FULL' => 'refunded',
        'EXPIRED' => 'failed'
    );

    public function __construct($key = false) {

        if( !$key ) {
            $this->_key = leyka_options()->opt('leyka_qiwi_secret_key');
        }

        add_action('funded_to_refunded', array($this, 'create_refund'), 10, 1);

    }

    public function create_refund(WP_Post $donation) {

        if(Leyka_Donation_Management::$post_type == $donation->post_type) {

            $donation = new Leyka_Donation($donation);
            $bill_id = get_post_meta(
                $donation->id . "-{$this->salt}",
                '_leyka_donation_id_on_gateway_response',
                true
            );
            $this->refund($bill_id, $donation->amount);

        }

    }

    public function refund($bill_id, $amount) {

        $bill_id = $bill_id."-{$this->salt}";
        $args = array(
            "amount" => array(
                "currency" => "RUB",
                "value" => intval($amount)
            )
        );

        $response = wp_remote_request(
            "https://api.qiwi.com/partner/bill/v1/bills/{$bill_id}/refunds/refund_{$bill_id}",
            array(
                'method' => 'PUT',
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->_key}",
                ),
                'body' => json_encode($args, JSON_FORCE_OBJECT)
            )
        );

        return $response;

    }

    public function create_bill($bill_id, $amount, $args = array()) {

        $bill_id = $bill_id."-{$this->salt}";
        $amount = intval($amount);
        $amount = number_format($amount, 2, '.', '');
        $args = wp_parse_args(
            $args,
            array(
                "amount" => array(
                    "currency" => "RUB",
                    "value" => $amount
                ),
                "comment" => "",
                "expirationDateTime" => self::date_formatter(strtotime('+1 day', current_time('timestamp', 1))),
                "customer" => array(),
                "customFields" => array(
                    "apiClient" => 'WordPress Leyka',
                    "apiClientVersion" => LEYKA_VERSION
                )
            )
        );

        $args['amount']['value'] = $amount;

        $response = wp_remote_request(
            "https://api.qiwi.com/partner/bill/v1/bills/{$bill_id}",
            array(
                'method' => 'PUT',
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->_key}",
                ),
                'body' => json_encode($args, JSON_FORCE_OBJECT)
            )
        );

        return $response;

    }

    /**
     * @param $timestamp integer UNIX timestamp.
     * @return string
     */
    public static function date_formatter($timestamp) {
        return str_replace(' ', 'T', date('Y-m-d H:i:s', $timestamp)) . self::gtm_prefix();
    }

    private static function gtm_prefix() {

        $gmt = get_option('gmt_offset', 3);

        return ($gmt > 0 ? '+' : '-') . ($gmt <= 9 ? '0' : '') . (number_format(abs($gmt), 2, ':', ' '));

    }

    public static function get_payment_id_by_response_data($bill_id) {

        global $wpdb;

        return $wpdb->get_var(
            "SELECT post_id FROM 
			{$wpdb->postmeta}
			WHERE meta_key = '_leyka_donation_id_on_gateway_response'
			AND meta_value  = '{$bill_id}'
			LIMIT 1");

    }

}

new Leyka_Qiwi_Gateway_Helper();