<?php if (!defined('WPINC')) {
    die;
}

class Leyka_Qiwi_Gateway_Helper {

    public static $map_status = array(
        'PAID' => 'funded',
        'WAITING' => 'submitted',
        'REJECTED' => 'failed',
        'PARTIAL' => 'refunded',
        'FULL' => 'refunded',
        'EXPIRED' => 'failed'
    );

    private $_key;

    public function __construct($key = false) {

        if( !$key ) {
            $this->_key = leyka_options()->opt('leyka_qiwi_secret_key');
        }

        add_action('funded_to_refunded', array($this, 'create_refund'), 10, 1);

    }

    public function create_refund(WP_Post $donation) {
        if(Leyka_Donation_Management::$post_type == $donation->post_type) {

            $donation = new Leyka_Donation($donation);
            $billId = get_post_meta($donation->id, '_leyka_donation_id_on_gateway_response', true);

            $this->refund($billId, $donation->amount);

        }
    }

    public function refund($billId, $amount) {

        $args = array(
            "amount" => array(
                "currency" => "RUB",
                "value" => intval($amount)
            )
        );

        $response = wp_remote_request(
            "https://api.qiwi.com/partner/bill/v1/bills/{$billId}/refunds/refund_{$billId}",
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

    public function create_bill($billId, $amount, $args = array()) {
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
                "customFields" => array()
            )
        );

        $args['amount']['value'] = $amount;

        $response = wp_remote_request(
            "https://api.qiwi.com/partner/bill/v1/bills/{$billId}",
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
        return str_replace(' ', 'T', date('Y-m-d H:i:s', $timestamp)).self::gtm_prefix();
    }

    private static function gtm_prefix() {

        $gmt = get_option('gmt_offset', 3);

        $zero = '';

        if (0 < $gmt) {
            $sign = "+";
        } else {
            $sign = "-";
        }

        $suffix = abs($gmt);

        $suffix = number_format($suffix, 2, ':', ' ');

        if (9 >= $gmt) {
            $zero = '0';
        }

        return $sign . $zero . $suffix;

    }

    public static function get_payment_id_by_response_data($billId) {

        global $wpdb;

        return $wpdb->get_var("
			SELECT post_id FROM 
			{$wpdb->postmeta}
			WHERE meta_key = '_leyka_donation_id_on_gateway_response'
			AND meta_value  = '{$billId}'
			LIMIT 1
		");

    }

}

new Leyka_Qiwi_Gateway_Helper();
