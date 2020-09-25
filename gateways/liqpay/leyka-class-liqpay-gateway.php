<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Liqpay_Gateway class
 */

include ('api/liqpay.php');

class Leyka_Liqpay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'liqpay';
        $this->_title = __('Liqpay', 'leyka');
        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('Liqpay system allows a simple and safe way to pay for goods and services.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = 'https://www.liqpay.ua/documentation/';
        $this->_registration_link = 'https://www.liqpay.ua/en/registration';

        $this->_min_commission = 2.75;
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = true;
        $this->_countries = array('ua',);

    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'liqpay_public_key' => array(
                'type' => 'text',
                'title' => __('Liqpay public key', 'leyka'),
                'comment' => __('Public key (API v. 3.0) supplied with Liqpay merchant account', 'leyka'),
                'required' => true,
                'placeholder' => '',
            ),
            'liqpay_private_key' => array(
                'type' => 'text',
                'title' => __('Liqpay private key', 'leyka'),
                'comment' => __('Private key (API v. 3.0) supplied with Liqpay merchant account', 'leyka'),
                'required' => true,
                'is_password' => true,
            ),
            'liqpay_test_mode' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ),
            'liqpay_enable_recurring' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Enable monthly recurring payments', 'leyka'),
                'comment' => __('Check if you want to enable monthly recurring payments.', 'leyka'),
                'short_format' => true,
            )
        );

    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Liqpay_Card::get_instance();
        }
        if(empty($this->_payment_methods['liqpay'])) {
            $this->_payment_methods['liqpay'] = Leyka_Liqpay::get_instance();
        }
        if(empty($this->_payment_methods['privat24'])) {
            $this->_payment_methods['privat24'] = Leyka_Liqpay_Privat24::get_instance();
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return 'https://www.liqpay.ua/api/3/checkout';
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {
        switch ($field_name) {
            case 'recurring_id':
                return get_post_meta($donation->id, '_liqpay_recurring_id', true);
            case 'card_token':
                return get_post_meta($donation->id, '_liqpay_card_token', true);
            case 'liqpay_customer_id':
                return get_post_meta($donation->id, '_liqpay_customer_id', true);
            case 'liqpay_transaction_id':
                return get_post_meta($donation->id, '_liqpay_transaction_id', true);
            case 'liqpay_order_id':
                return get_post_meta($donation->id, '_liqpay_order_id', true);
            default:
                return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {
        switch ($field_name) {
            case 'recurring_id':
                return update_post_meta($donation->id, '_liqpay_recurring_id', $value);
            case 'card_token':
                return update_post_meta($donation->id, '_liqpay_card_token', $value);
            case 'liqpay_customer_id':
                return update_post_meta($donation->id, '_liqpay_customer_id', $value);
            case 'liqpay_transaction_id':
                return update_post_meta($donation->id, '_liqpay_transaction_id', $value);
            case 'liqpay_order_id':
                return update_post_meta($donation->id, '_liqpay_order_id', $value);
            default:
                return false;
        }
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        if( !array_key_exists($pm_id, $this->_payment_methods) )
            return $form_data_vars; //it's not our PM

        $donation = new Leyka_Donation($donation_id);
        $amount = number_format((float)$donation->amount, 2, '.', '');
        $currency = mb_strtoupper($donation->currency);

        $public = leyka_options()->opt('liqpay_public_key');
        $private = leyka_options()->opt('liqpay_private_key');

        if ($currency === 'RUR') {
            $currency = 'RUB';
        }

        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
        }

        switch(get_locale()) {
            case 'ru_RU': $language = 'ru'; break;
            case 'uk': $language = 'uk'; break;
            default: $language = 'en'; break;
        }

        $form_data_vars =  array(
            'version' 					=> 3,
            'public_key' 				=> leyka_options()->opt('liqpay_public_key'),
            'action'                    => !empty($form_data_vars['leyka_recurring']) ? 'subscribe' : 'paydonate',
            'amount' 					=> $amount,
            'currency' 					=> $currency,
            'description' 				=> $donation->payment_title,
            'order_id' 					=> $donation_id,
            'subscribe'                 => !empty($form_data_vars['leyka_recurring']) ? 1 : 0,
            'subscribe_date_start'      => !empty($form_data_vars['leyka_recurring']) ? date("Y-m-d H:i:s") : '',
            'subscribe_periodicity'     => !empty($form_data_vars['leyka_recurring']) ? 'month' : '',
            'recurringbytoken' 			=> !empty($form_data_vars['leyka_recurring']) ? 1 : 0,
            'customer'                  => $donation->donor_name,
            'customer_user_id'           => $donation->donor_user_id,
            'paytypes' 					=> $pm_id == 'privat24' ? 'card,privat24' : $pm_id,
            'server_url'                => home_url('/leyka/service/'.$this->_id.'/response/'),
            'result_url'                => home_url('/leyka/service/'.$this->_id.'/response/'),
            'language' 					=> $language,
        );

        $submission = array();

        //check params
        $submission['data'] = base64_encode(json_encode($form_data_vars));

        try {

            $api = new Liqpay($public, $private);
            $submission['signature'] = $api->cnb_signature($form_data_vars);

        } catch(Exception $e) {
            /** @todo Handle Exceptions */
        }

        return $submission;

    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        // Decode a response:
        $data = json_decode(base64_decode($_POST['data']));
        $data = (array)$data;
        $private = leyka_options()->opt('liqpay_private_key');

        $signature = base64_encode(sha1($private.$_POST['data'].$private, true));

        if($signature != $_POST['signature']) {

            $message = __("This message has been sent because a call to your Liqpay callback was made with wrong signature. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_DATA:\n\r".print_r($data, true)."\n\r\n\r";
            $message .= "THEIR_SIGNATURE:\n\r".print_r($_POST['signature'], true)."\n\r\n\r";
            $message .= "CALCULATED_SIGNATURE:\n\r".print_r($signature, true)."\n\r\n\r";

            $message .= "THEIR_POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";

            wp_mail(leyka_get_website_tech_support_email(), __('Leyka: Liqpay signature mismatch!', 'leyka'), $message);

            status_header(200);
            die();

        }

        $redirect_url = leyka_get_success_page_url();
        $donation = new Leyka_Donation($data['order_id']);

        $data['currency'] = mb_strtolower($data['currency']);

        if( !empty($data['status']) && in_array($data['status'], array('failure', 'try_again')) ) { // Payment failed

            $data['status'] = 'failed';
            $new_status = 'failed';
            $redirect_url = leyka_get_failure_page_url();

        } else if( !empty($data['action']) && in_array($data['action'], array('subscribe', 'regular')) ) { // Recurring

            if( in_array($data['status'], array('subscribed', 'sandbox')) ) {

                if(time() - $donation->date_timestamp >= 60*60*24*3) { // More than 3 days passed, so it's a rebill callback

                    $donation = Leyka_Donation::add_clone(
                        $donation,
                        array(
                            'init_recurring_donation' => $donation->id,
                        ),
                        array('recalculate_total_amount' => true,)
                    );

                    if(is_wp_error($donation)) {
                        exit(200);
                    }

                } else { // Recurring subscription callback

                    $new_status = 'funded';
                    $donation->payment_type = 'rebill';

                }

                if( !empty($data['card_token']) ) {

                    $donation->recurring_id = $data['order_id'];
                    $donation->liqpay_order_id = $data['liqpay_order_id'];
                    $donation->card_token = $data['card_token'];

                }

            }

        } else if( !empty($data['action']) && $data['action'] === 'pay' ) { // Single donation

            if( !empty($data['liqpay_order_id']) ) {
                $donation->liqpay_order_id = $data['liqpay_order_id'];
            }

            switch($data['status']) {
                case 'success': $new_status = 'funded'; break;
                case 'reversed': $new_status = 'refunded'; break;
                default:
                    $new_status = 'submitted';
            }

        }

        $donation->add_gateway_response($data);

        if($donation->status !== $new_status) {

            $donation->status = $new_status;

            if($new_status === 'funded') {
                Leyka_Donation_Management::send_all_emails($donation->id);
            }

        }

        status_header(200);
        wp_redirect($redirect_url);
        die(0);

    }

    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation $donation) {

        $init_recurring_donation = Leyka_Donation::get_init_recurring_donation($donation);
        $cancelling_url = (get_option('permalink_structure') ?
                home_url("leyka/service/cancel_recurring/{$donation->id}") :
                home_url("?page=leyka/service/cancel_recurring/{$donation->id}"))
            .'/'.md5($donation->id.'_'.$init_recurring_donation->id.'_leyka_cancel_recurring_subscription');

        return sprintf(__('<a href="%s" target="_blank" rel="noopener noreferrer">click here</a>', 'leyka'), $cancelling_url);

    }

    public function cancel_recurring_subscription(Leyka_Donation $donation) {

        if($donation->type !== 'rebill') {
            return new WP_Error(
                'wrong_recurring_donation_to_cancel',
                __('Wrong donation given to cancel a recurring subscription.', 'leyka')
            );
        }

        $recurring_manual_cancel_link = 'https://www.liqpay.ua/api/request';

        if( !$donation->liqpay_order_id ) {
            return new WP_Error('cp_no_subscription_id', sprintf(__('<strong>Error:</strong> unknown Subscription ID for donation #%d. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), $donation->id, leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $api = new Liqpay(leyka_options()->opt('liqpay_public_key'), leyka_options()->opt('liqpay_private_key'));
        $response = $api->api('request', array(
            'action'     => 'unsubscribe',
            'version'    => 3,
            'public_key' => leyka_options()->opt('liqpay_public_key'),
            'order_id'   => $donation->id,
        ));

        if($response->status !== 'unsubscribed') {
            return new WP_Error('cp_cannot_cancel_recurring', sprintf(__('<strong>Error:</strong> we cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $donation->recurring_is_active = false;

        return true;

    }

    public function cancel_recurring_subscription_by_link(Leyka_Donation $donation) {

        if($donation->type !== 'rebill') {
            die();
        }

        header('Content-type: text/html; charset=utf-8');

        $recurring_cancelling_result = $this->cancel_recurring_subscription($donation);

        if ($recurring_cancelling_result === true) {
            die(__('Recurring subscription cancelled successfully.', 'leyka'));
        } else if (is_wp_error($recurring_cancelling_result)) {
            die($recurring_cancelling_result->get_error_message());
        } else {
            die(sprintf(__('Error while trying to cancel the recurring subscription.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email()));
        }

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

        $vars = $donation->gateway_response;
        if( !$vars || !is_array($vars) ) {
            return array();
        }

        return array(
            __('Operation date:', 'leyka') => empty($donation->gateway_response['operation_date']) ?
                __('none', 'leyka') :
                $this->_get_value_if_any(
                    $donation->gateway_response,
                    'operation_date',
                    date('d.m.Y, H:i:s', $donation->gateway_response['operation_date'])
                ),
            __('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'transaction_id'),
            __('Order ID:', 'leyka') => $this->_get_value_if_any($vars, 'order_id'),
            __('Payment method:', 'leyka') => $this->_get_value_if_any($vars, 'paytype'),
            __('Acquiring ID:', 'leyka') => $this->_get_value_if_any($vars, 'acq_id'),
            __('Gateway inner order ID:', 'leyka') => $this->_get_value_if_any($vars, 'liqpay_order_id'),
            __('Gateway commission (%):', 'leyka') => $this->_get_value_if_any($vars, 'receiver_commission'),
            __('Amount:', 'leyka') => $this->_get_value_if_any($vars, 'amount'),
            __('Donation currency:', 'leyka') => $this->_get_value_if_any($vars, 'currency'),
            __('Description:', 'leyka') => $this->_get_value_if_any($vars, 'description'),
            __('Donor IP:', 'leyka') => $this->_get_value_if_any($vars, 'ip'),
            __('Operation status:', 'leyka') => $this->_get_value_if_any($vars, 'status'),
            __('Sender phone:', 'leyka') => $this->_get_value_if_any($vars, 'sender_phone'),
            __('Payment action / type:', 'leyka') => $this->_get_value_if_any($vars, 'action').' / '
                .$this->_get_value_if_any($vars, 'type'),
            __('Public Key:', 'leyka') => $this->_get_value_if_any($vars, 'public_key'),
        );
    }

} // Gateway class end


class Leyka_Liqpay extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'liqpay';
        $this->_gateway_id = 'liqpay';
        $this->_category = 'misc';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Liqpay allows to make payments via bank card, Liqpay wallet, Privat24 and delayed payments through TCOs', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Liqpay wallet payment', 'leyka');
        $this->_label = __('Liqpay wallet payment', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/liqpay/icons/Liqpay_logo_full.svg',
        ));

        $this->_supported_currencies = array('rur', 'uah', 'usd', 'eur',);
        $this->_default_currency = 'uah';

    }

}

class Leyka_Liqpay_Card extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'liqpay';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Liqpay allows to make payments via bank card, Liqpay wallet, Privat24 and delayed payments through TCOs', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
        ));

        $this->_supported_currencies = array('rur', 'uah', 'usd', 'eur',);
        $this->_default_currency = 'uah';

    }

    public function has_recurring_support() { // Support recurring donations only if both single & recurring options set
        return !!leyka_options()->opt('liqpay_enable_recurring');
    }

}

class Leyka_Liqpay_Privat24 extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'privat24';
        $this->_gateway_id = 'liqpay';
        $this->_category = 'misc';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Liqpay allows to make payments via bank card, Liqpay wallet, Privat24 and delayed payments through TCOs', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Liqpay privat24 payment', 'leyka');
        $this->_label = __('Liqpay privat24 payment', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/liqpay/icons/privat_logo_short.svg',
        ));

        $this->_supported_currencies = array('rur', 'uah', 'usd', 'eur',);
        $this->_default_currency = 'uah';

    }

}

function leyka_add_gateway_liqpay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Liqpay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_liqpay', 11);