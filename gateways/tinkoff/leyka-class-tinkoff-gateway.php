<?php if( !defined('WPINC') ) die;

class Leyka_Tinkoff_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_redirect_url = '';

    protected function _set_attributes() {

        $this->_id = 'tinkoff';
        $this->_title = __('Tinkoff', 'leyka');
        $this->_has_wizard = false;

        $this->_min_commission = 1;
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }


        $this->_options = array(
            $this->_id.'_terminal_key' => array(
                'type' => 'text',
                'title' => __('Terminal ID', 'leyka'),
                'comment' => __('The value may be acquired from the bank when Tinkoff payment terminal is created.', 'leyka'),
                'required' => true,
            ),
            $this->_id.'_password' => array(
                'type' => 'text',
                'is_password' => true,
                'title' => __('Password', 'leyka'),
                'comment' => __('The value may be acquired from the bank when Tinkoff payment terminal is created.', 'leyka'),
                'required' => true,
            ),
        );

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt($this->_id.'_terminal_key') && leyka_options()->opt($this->_id.'_password');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Tinkoff_Card::get_instance();
        }
    }

    protected function _handle_donation_failure(Leyka_Donation $donation, $gateway_response = false) {

        $donation->status = 'failed';

        if($gateway_response) {
            $donation->add_gateway_response($gateway_response);
        }

        if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
            Leyka_Donation_Management::send_error_notifications($donation);
        }

    }

    public function do_recurring_donation(Leyka_Donation $init_recurring_donation) {

        if( !$init_recurring_donation->tinkoff_rebill_id) {
            return false;
        }

        $new_recurring_donation = Leyka_Donation::add_clone(
            $init_recurring_donation,
            array(
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'amount_total' => 'auto',
                'init_recurring_donation' => $init_recurring_donation->id,
            ),
            array('recalculate_total_amount' => true,)
        );

        $new_recurring_donation->tinkoff_rebill_id = esc_sql($init_recurring_donation->tinkoff_rebill_id);

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $this->_require_lib();

        $api = new TinkoffMerchant(
            leyka_options()->opt($this->_id.'_terminal_key'),
            leyka_options()->opt($this->_id.'_password')
        );

        $api->init(array(
            'OrderId' => $new_recurring_donation->id,
            'Amount' => 100 * absint($new_recurring_donation->amount),
            'DATA' => array('Email' => $init_recurring_donation->donor_email,),
        ));

        if($api->error){
            $this->_handle_donation_failure($new_recurring_donation, $api);
        } else {

            $api->charge(array('RebillId' => $init_recurring_donation->tinkoff_rebill_id, 'PaymentId' => $api->paymentId,));

            if($api->error || $api->status === 'REJECTED') {
                $this->_handle_donation_failure($new_recurring_donation, $api);
            } else {

                $new_recurring_donation->status = 'funded';
                Leyka_Donation_Management::send_all_emails($new_recurring_donation->id);

            }

        }

        return $new_recurring_donation;

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

        $this->_require_lib();

        $api = new TinkoffMerchant(
            leyka_options()->opt($this->_id.'_terminal_key'),
            leyka_options()->opt($this->_id.'_password')
        );

        $params = array(
            'OrderId' => $donation_id,
            'Amount' => 100 * absint($donation->amount),
            'DATA' => array('Email' => $donation->donor_email,),
        );

        if($donation->type === 'rebill') {

            $params['Recurrent'] = 'Y';
            $params['CustomerKey'] = $donation->donor_email;

        }

        $api->init($params);

        if($api->error){

            leyka()->add_payment_form_error( new WP_Error('leyka_donation_error', sprintf(__('Error while processing the payment: %s. Your money will remain intact. Please report to the <a href="mailto:%s" target="_blank">website tech support</a>.', 'leyka'), $api->error, leyka_get_website_tech_support_email())) );
            return;

        } else {
            $this->_redirect_url = $api->paymentUrl;
        }

    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $vars = $donation->gateway_response;
        if( !$vars || !is_array($vars) ) {
            return array();
        }

        return array(
            __('Order ID:', 'leyka') => $vars['OrderId'],
            __('Payment ID:', 'leyka') => $vars['PaymentId'],
            __('Error Code:', 'leyka') => $vars['ErrorCode'],
        );

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $this->_redirect_url ? $this->_redirect_url : ''; // The Gateway receives redirection URL on payment
    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        if( !array_key_exists($pm_id, $this->_payment_methods) ) {
            return $form_data; // It's not our PM
        }

        return array();

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'tinkoff_payment_id':
                return get_post_meta($donation->id, '_leyka_tinkoff_payment_id', true);
            case 'tinkoff_rebill_id':
                return get_post_meta($donation->id, '_leyka_tinkoff_rebill_id', true);
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'tinkoff_payment_id':
                return update_post_meta($donation->id, '_leyka_tinkoff_payment_id', $value);
            case 'tinkoff_rebill_id':
                return update_post_meta($donation->id, '_leyka_tinkoff_rebill_id', $value);
            default: return false;
        }

    }

    public function _handle_service_calls($call_type = '') {

        $response = file_get_contents('php://input');
        if($response) {

            $response = json_decode($response, true);

            if($this->_check_result_response($response)) {

                $donation = new Leyka_Donation($response['OrderId']);

                switch($response['Status']) {
                    case 'CONFIRMED':
                        $donation->status = 'funded';
                        Leyka_Donation_Management::send_all_emails($donation->id);
                        break;

                    case 'REJECTED':
                    case 'AUTH_FAIL':
                    case 'REVERSED':
                        $donation->status = 'failed';
                        break;

                    case 'REFUNDED':
                        $donation->status = 'refunded';
                        break;

                    default:
                }

                if( !empty($response['PaymentId']) ) {
                    $donation->tinkoff_payment_id = esc_sql($response['PaymentId']);
                }

                if( !empty($response['RebillId']) ) {
                    $donation->tinkoff_rebill_id = esc_sql($response['RebillId']);
                }

                $donation->add_gateway_response($response);

            }

        }

        echo 'OK';

    }

    protected function _check_result_response($params = array()) {

        $prev_token = $params['Token'];

        $params['Success'] = (int)$params['Success'] > 0 ? (string)'true' : (string)'false';

        unset($params['Token'], $params['Receipt'], $params['Data']);

        $params['TerminalKey'] = leyka_options()->opt($this->_id.'_terminal_key');
        $params['Password'] = leyka_options()->opt($this->_id.'_password');

        ksort($params);

        return strcmp(mb_strtolower($prev_token), mb_strtolower(hash('sha256', implode('', $params)))) == 0;

    }

    protected function _require_lib() {
        require_once LEYKA_PLUGIN_DIR.'gateways/tinkoff/lib/TinkoffMerchantAPI.php';
    }

}

class Leyka_Tinkoff_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'tinkoff';
        $this->_category = 'bank_cards';

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_recurring_available' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'comment' => __('Check if TinkoffBank Acquiring allows you to create recurrent subscriptions to do regular automatic payments. WARNING: you should enable the Tinkoffbank auto-payments feature for test mode and for production mode separately.', 'leyka'),
                'short_format' => true,
            ),
        );

    }

    public function has_recurring_support() {
        return !!leyka_options()->opt($this->full_id.'_recurring_available');
    }

}

function leyka_add_gateway_tinkoff() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Tinkoff_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_tinkoff');