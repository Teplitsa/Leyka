<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Yandex_phyz_Gateway class
 */

class Leyka_Yandex_Phyz_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'yandex_phyz';
        $this->_title = __('YooMoney for physical persons', 'leyka');
        $this->_docs_link = '//leyka.org/docs/podklyuchenie-yandeks-dengi-dlya-fizicheskih-lits/';

        $this->_min_commission = 2;
        $this->_receiver_types = ['physical'];

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = [
            'yandex_money_account' => [
                'type' => 'text',
                'title' => __('YooMoney account ID', 'leyka'),
                'comment' => __('Please, enter your YooMoney account ID here.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '4100111111111111'),
            ],
            'yandex_money_secret' => [
                'type' => 'text',
                'title' => __('YooMoney account API secret', 'leyka'),
                'comment' => __('Please, enter your YooMoney account API secret string here.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'QweR+1TYUIo/p2aS3DFgHJ4K5'),
            ],
        ];

    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['yandex_phyz_card'])) {
            $this->_payment_methods['yandex_phyz_card'] = Leyka_Yandex_Phyz_Card::get_instance();
        }
        if(empty($this->_payment_methods['yandex_phyz_money'])) {
            $this->_payment_methods['yandex_phyz_money'] = Leyka_Yandex_Phyz_Money::get_instance();
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        switch($pm_id) {
            case 'yandex_phyz_money':
            case 'yandex_phyz_card':
                return 'https://yoomoney.ru/quickpay/confirm.xml';
            default:
                return $current_url;
        }

    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        switch($pm_id) {
            case 'yandex_phyz_money': $payment_type = 'PC'; break;
            case 'yandex_phyz_card': $payment_type = 'AC'; break;
            default:
                $payment_type = '';
        }

		$name = apply_filters(
		    'leyka_yandex_phyz_custom_payment_comment', esc_attr(get_bloginfo('name').': '.__('donation', 'leyka'))
        );

        return [
            'receiver' => leyka_options()->opt('yandex_money_account'),
            'sum' => $donation->amount,
            'formcomment' => $name,
			'short-dest' => $name,
			'targets' => $campaign->payment_title ? esc_attr($campaign->payment_title) : $name,
			'quickpay-form' => 'donate',
            'label' => $donation_id,
            'paymentType' => $payment_type,
            'shopSuccessURL' => leyka_get_success_page_url(),
            'shopFailURL' => leyka_get_failure_page_url(),
            'cps_email' => $donation->donor_email,
        ];

    }

    // Wrapper method to answer the checkOrder service calls:
    private function _check_order_answer($is_error = false, $message = '', $tech_message = '') {

        $tech_message = $tech_message ? : $message;

        $_POST['operation_id'] = empty($_POST['operation_id']) ? 0 : (int)$_POST['operation_id'];

        $operation_id = $_POST['operation_id'];

        if( !!$is_error ) {
            die('<?xml version="1.0" encoding="UTF-8"?>
<checkOrderResponse performedDatetime="'.esc_attr(gmdate(DATE_ATOM)).'"
code="1000" operation_id="' . esc_attr( $operation_id ) . '"
account_id="'.esc_attr(leyka_options()->opt('yandex_money_account')).'"
message="'.esc_attr($message).'"
techMessage="'.esc_attr($tech_message).'"/>');
        } else {
            die('<?xml version="1.0" encoding="UTF-8"?>
<checkOrderResponse performedDatetime="'.esc_attr(gmdate(DATE_ATOM)).'"
code="0" operation_id="' . esc_attr( $operation_id ) . '"
account_id="'.esc_attr(leyka_options()->opt('yandex_money_account')).'"/>');
        }

    }

    public function _handle_service_calls($call_type = '') {

		error_log_yandex_phyz("\n\n---- $call_type (".gmdate('d.m.Y H:i:s').") ----\n\n".print_r($_REQUEST, true));

        $donation_id = empty($_POST['label']) ? 0 : absint($_POST['label']); // Donation ID
        $amount = empty($_POST['withdraw_amount']) ? 0.0 : (float)$_POST['withdraw_amount'];

        error_log_yandex_phyz("Label: $donation_id\n");
        error_log_yandex_phyz("Amount: $amount\n");

        if( !$donation_id ) {

            error_log_yandex_phyz("Donation ID is empty\n");
            return;

        } else if( !$amount ) {

            error_log_yandex_phyz("Donation amount is empty\n");
            return;

        }

        $donation = Leyka_Donations::get_instance()->get($donation_id);

//        error_log_yandex_phyz("Donation initialized\n");
//        error_log_yandex_phyz(print_r($donation, TRUE)."\n");

        $sha1 = sha1(implode('&', [
            isset($_POST['notification_type']) ? $_POST['notification_type'] : '',
            isset($_POST['operation_id']) ? $_POST['operation_id']: '',
            isset($_POST['amount']) ? $_POST['amount'] : '',
            isset($_POST['currency']) ? $_POST['currency'] : '',
            isset($_POST['datetime']) ? $_POST['datetime'] : '',
            isset($_POST['sender']) ? $_POST['sender'] : '',
            isset($_POST['codepro']) ? $_POST['codepro'] : '',
            leyka_options()->opt('yandex_money_secret'),
            $donation_id
        ]));

        $tmp = implode('&', [
            isset($_POST['notification_type']) ? $_POST['notification_type'] : '',
            isset($_POST['operation_id']) ? $_POST['operation_id']: '',
            isset($_POST['amount']) ? $_POST['amount'] : '',
            isset($_POST['currency']) ? $_POST['currency'] : '',
            isset($_POST['datetime']) ? $_POST['datetime'] : '',
            isset($_POST['sender']) ? $_POST['sender'] : '',
            isset($_POST['codepro']) ? $_POST['codepro'] : '',
            leyka_options()->opt('yandex_money_secret'),
            $donation_id
        ]);
        error_log_yandex_phyz("sha1 line: $tmp\n");
        error_log_yandex_phyz("sha1_calculated: $sha1 , sha1_received: {$_POST['sha1_hash']}\n");

        if(empty($_POST['sha1_hash']) || $sha1 != @$_POST['sha1_hash']) {

            error_log_yandex_phyz("Invalid response sha1_hash\n");
            $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Invalid response sha1_hash', 'leyka'));

        } else if($donation) {

            error_log_yandex_phyz("Donation is OK\n");
            error_log_yandex_phyz('$donation->sum = '.$donation->sum."\n");
            error_log_yandex_phyz('$donation->status = '.$donation->status."\n");

            if($donation->amount != $amount) {

                error_log_yandex_phyz("Donation amount doesn't match with amount given in the callback\n");
                $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));

            } else if($donation->status != 'funded') {

                if( !empty($_POST['notification_type']) ) { // Update a donation's actual PM, if needed

                    $actual_pm = $_POST['notification_type'] == 'card-incoming' ? 'yandex_phyz_card' : 'yandex_phyz_money';

                    if($donation->pm_id != $_POST['notification_type']) {
                        $donation->pm_id = $actual_pm;
                    }

                }

                $donation->add_gateway_response($_POST);
                $donation->status = 'funded';

                Leyka_Donation_Management::send_all_emails($donation->id);

                error_log_yandex_phyz("The donation #$donation_id is funded\n");

                if( // GUA direct integration - "purchase" event:
                    leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
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

            } else {
                error_log_yandex_phyz("The Donation #$donation_id is already funded\n");
            }

            $this->_check_order_answer();

        } else {

            error_log_yandex_phyz("There is no donation in Leyka DB\n");

            $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Unregistered donation ID', 'leyka'));

        }
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) ) {
            return [];
        }

		$payment_type = '';
		if($response_vars['notification_type'] == 'p2p-incoming') {
			$payment_type = __('Using YooMoney Account', 'leyka');
		} else if($response_vars['notification_type'] == 'card-incoming') {
			$payment_type = __('Using Banking Card', 'leyka');
		}

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('Last response operation:', 'leyka') => __('Donation confirmation', 'leyka'),
                __('YooMoney payment type:', 'leyka') => $payment_type,
                __('Gateway invoice ID:', 'leyka') => $response_vars['operation_id'],
                __('Full donation amount:', 'leyka') =>
                    (float)$response_vars['withdraw_amount'].' '.$donation->currency_label,
                __('Donation amount after gateway commission:', 'leyka') =>
                    (float)$response_vars['amount'].' '.$donation->currency_label,
                __("Gateway's donor ID:", 'leyka') => $response_vars['sender'],
                __('Response date:', 'leyka') => gmdate('d.m.Y, H:i:s', strtotime($response_vars['datetime'])),
            ],
            $donation
        );

    }

}


class Leyka_Yandex_Phyz_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_phyz_money';
        $this->_gateway_id = 'yandex_phyz';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __("Yandex.Money is a simple and safe payment system to pay for goods and services through internet. You will have to fill a payment form, you will be redirected to the <a href='https://money.yandex.ru/'>Yandex.Money website</a> to confirm your payment. If you haven't got a Yandex.Money account, you can create it there.", 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('YooMoney virtual cash', 'leyka');
        $this->_label = __('YooMoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/yandex-money.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}


class Leyka_Yandex_Phyz_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_phyz_card';
        $this->_gateway_id = 'yandex_phyz';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Yandex.Money allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://money.yandex.ru/">Yandex.Money website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label = __('Bank card', 'leyka');
        $this->_label_backend = $this->_label;

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

function error_log_yandex_phyz($string) {
	error_log($string, 3, WP_CONTENT_DIR.'/uploads/phyz-error.log');
}

function leyka_add_gateway_yandex_phyz() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Yandex_Phyz_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_yandex_phyz');
