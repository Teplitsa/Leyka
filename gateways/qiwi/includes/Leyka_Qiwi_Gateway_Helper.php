<?php if (!defined('WPINC')) { die; }

class Leyka_Qiwi_Gateway_Helper {

    public static $map_status = [
        'PAID' => 'funded',
        'WAITING' => 'submitted',
        'REJECTED' => 'failed',
        'PARTIAL' => 'refunded',
        'FULL' => 'refunded',
        'EXPIRED' => 'failed'
    ];

    public function __construct() {
        /** @todo Refunds handling is commented out - further debugging/testing needed. WTF is $this->salt ??? */
//        add_action('leyka_donation_status_funded_to_refunded', [$this, 'create_refund'], 10);
    }

    public function create_refund(Leyka_Donation_Base $donation) {
        $this->refund($donation->qiwi_donation_id_on_gateway_response, $donation->amount);
    }

    public function refund($bill_id, $amount) {

        $bill_id .= "-{$this->salt}";

        return wp_remote_request(
            "https://api.qiwi.com/partner/bill/v1/bills/{$bill_id}/refunds/refund_{$bill_id}",
            [
                'method' => 'PUT',
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.leyka_options()->opt('leyka_qiwi_secret_key'),
                ],
                'body' => json_encode(
                    ['amount' => ['currency' => 'RUB', 'value' => intval($amount),]],
                    JSON_FORCE_OBJECT
                )
            ]
        );

    }

    public function create_bill($bill_id, $amount, $args = []) {

        $amount = number_format(intval($amount), 2, '.', '');

        $args = wp_parse_args(
            $args,
            [
                'amount' => ['currency' => 'RUB', 'value' => $amount,],
                'comment' => '',
                'expirationDateTime' => self::_format_date(strtotime('+1 day', current_time('timestamp', 1))),
                'customer' => [],
                'customFields' => ['apiClient' => 'WordPress Leyka', 'apiClientVersion' => LEYKA_VERSION,],
            ]
        );

        $args['amount']['value'] = $amount;

        return wp_remote_request(
            'https://api.qiwi.com/partner/bill/v1/bills/'.$bill_id."-{$this->salt}",
            [
                'method' => 'PUT',
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.leyka_options()->opt('leyka_qiwi_secret_key'),
                ],
                'body' => json_encode($args, JSON_FORCE_OBJECT)
            ]
        );

    }

    /**
     * @param $timestamp integer UNIX timestamp.
     * @return string
     */
    protected static function _format_date($timestamp) {
        return str_replace(' ', 'T', date('Y-m-d H:i:s', $timestamp)).self::_gtm_prefix();
    }

    protected static function _gtm_prefix() {

        $gmt = get_option('gmt_offset', 3);

        return ($gmt > 0 ? '+' : '-') . ($gmt <= 9 ? '0' : '') . (number_format(abs($gmt), 2, ':', ' '));

    }

    public static function get_payment_id_by_response_data($bill_id) {
        return Leyka_Donations::get_instance()->get_donation_id_by_meta_value('_leyka_donation_id_on_gateway_response', $bill_id);
    }

}

new Leyka_Qiwi_Gateway_Helper();