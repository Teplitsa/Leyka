<?php if (!defined('WPINC')) {
    die;
}

class Leyka_Qiwi_Gateway_Web_Hook {

    public static function run() {
        add_action('leyka_qiwi_gateway_web_hook', [__CLASS__, 'hook']);
    }

    public static function hook() {

        $data_decode = json_decode(file_get_contents('php://input'), true);

        $signature_correct = Leyka_Qiwi_Gateway_Web_Hook_Verification::check_notification_signature(
            $_SERVER['HTTP_X_API_SIGNATURE_SHA256'],
            $data_decode,
            leyka_options()->opt('qiwi_secret_key')
        );

        if($signature_correct) {

            $bill_id = $data_decode['bill']['billId'];
            $status = $data_decode['bill']['status']['value'];

            $donation_id = Leyka_Qiwi_Gateway_Helper::get_payment_id_by_response_data($bill_id);
            $donation = Leyka_Donations::get_instance()->get($donation_id);

            $donation->status = Leyka_Qiwi_Gateway_Helper::$map_status[$status];

            header('Content-Type: application/json');
            status_header(200);

            if($donation->status == Leyka_Qiwi_Gateway_Helper::$map_status[$status]) {

                if( // GUA direct integration - "purchase" event:
                    $donation->status === 'funded'
                    && leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
                    && leyka_options()->opt('gtm_ua_tracking_id')
                    && in_array('purchase', leyka_options()->opt('gtm_ua_enchanced_events'))
                    // We should send data to GA only for single or init recurring donations:
                    && ($donation->type === 'single' || $donation->is_init_recurring_donation)
                ) {

                    require_once LEYKA_PLUGIN_DIR.'vendor/autoload.php';

                    $analytics = new TheIconic\Tracking\GoogleAnalytics\Analytics(true);
                    $analytics // Main params:
                    ->setProtocolVersion('1')
                        ->setTrackingId(leyka_options()->opt('gtm_ua_tracking_id'))
                        ->setClientId($donation->ga_client_id ? $donation->ga_client_id : leyka_gua_get_client_id())
                        // Transaction params:
                        ->setTransactionId($donation->id)
                        ->setAffiliation(get_bloginfo('name'))
                        ->setRevenue($donation->amount)
                        ->addProduct([ // Donation params
                            'name' => $donation->payment_title,
                            'price' => $donation->amount,
                            'brand' => get_bloginfo('name'), // Mb, it won't work with it
                            'category' => $donation->type_label, // Mb, it won't work with it
                            'quantity' => 1,
                        ])
                        ->setProductActionToPurchase()
                        ->setEventCategory('Checkout')
                        ->setEventAction('Purchase')
                        ->sendEvent();

                }
                // GUA direct integration - "purchase" event END

                if($donation->status === 'funded') {
                    Leyka_Donation_Management::send_all_emails($donation);
                }

                echo wp_json_encode(['error' => 0], JSON_FORCE_OBJECT);

            } else {
                echo wp_json_encode(['error' => 1], JSON_FORCE_OBJECT);
            }

        }

        die;

    }

}

Leyka_Qiwi_Gateway_Web_Hook::run();