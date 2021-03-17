<?php if (!defined('WPINC')) {
    die;
}

class Leyka_Qiwi_Gateway_Web_Hook {

    public static function run() {
        add_action('leyka_qiwi_gateway_web_hook', array(__CLASS__, 'hook'));
    }

    public static function hook() {

        $data_decode = json_decode(file_get_contents('php://input'), true);

        $check = Leyka_Qiwi_Gateway_Web_Hook_Verification::check_notification_signature(
            $_SERVER['HTTP_X_API_SIGNATURE_SHA256'],
            $data_decode,
            leyka_options()->opt('qiwi_secret_key')
        );

        if ($check) {

            $billId = $data_decode['bill']['billId'];
            $status = $data_decode['bill']['status']['value'];

            $donation_id = Leyka_Qiwi_Gateway_Helper::get_payment_id_by_response_data($billId);

            $out = wp_update_post(array(
                'ID' => $donation_id,
                'post_status' => Leyka_Qiwi_Gateway_Helper::$map_status[$status]
            ));

            header('Content-Type: application/json');
            status_header(200);

            if($out && !is_wp_error($out)) {
                echo wp_json_encode(array('error' => 0), JSON_FORCE_OBJECT);
            } else {
                echo wp_json_encode(array('error' => 1), JSON_FORCE_OBJECT);
            }

        }

        die;

    }

}

Leyka_Qiwi_Gateway_Web_Hook::run();