<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Sberbank_Gateway class
 */

class Leyka_Sberbank_Gateway extends Leyka_Gateway {

    protected static $_instance; // Gateway is always a singleton

    protected function _set_options_defaults() {
        $this->_options = array(
            'login' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Sberbank payment gateway login', 'leyka'),
                'required' => 1,
                'placeholder' => __('Sberbank payment gateway login', 'leyka'),
            ),
            'password' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Sberbank payment gateway password', 'leyka'),
                'required' => 1,
                'placeholder' => __('Sberbank payment gateway password', 'leyka'),
            ),
        );
    }
    
    protected function _set_attributes() {

        $this->_id = 'sberbank';
        $this->_title = __('Sberbank', 'leyka');
        $this->_docs_link = 'http://nashideti.org/';
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods[Leyka_Sberbank_Acquiring::PAYMENT_METHOD_ID])) {
            $this->_payment_methods[Leyka_Sberbank_Acquiring::PAYMENT_METHOD_ID] = Leyka_Sberbank_Acquiring::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

    }

    /** Quittance don't use any specific redirects, so this method is empty. */
    public function submission_redirect_url($current_url, $pm_id) {
        return home_url('/leyka-process-donation');
    }
    
    /** Quittance don't have some form data to send to the gateway site */
    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {
        return $form_data_vars;
    }

    /** Quittance don't have any of gateway service calls */
    public function _handle_service_calls($call_type = '') {}

    public function get_gateway_response_formatted(Leyka_Donation $donation) {
        return array();
    }

    /** Quittance don't use any specific fields, so this method is empty. */
    public function log_gateway_fields($donation_id) {
    }
}

class Leyka_Sberbank_Acquiring extends Leyka_Payment_Method {

    const PAYMENT_METHOD_ID = 'sberbank_acquiring';

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = self::PAYMENT_METHOD_ID;
        $this->_gateway_id = 'sberbank';

        $this->_label_backend = __('Bank order sberbank', 'leyka');
        $this->_label = __('Bank order sberbank', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/sberbank/icons/sber_s.png',
        ));

        $this->_submit_label = __('Get bank order sberbank', 'leyka');

        $this->_supported_currencies = array('rur');

        $this->_default_currency = 'rur';

        $this->_ajax_without_form_submission = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('Bank order payment allows you to make a donation through any bank. You can print out a bank order paper and bring it to the bank to make a payment.', 'leyka'),
                'title' => __('Bank order payment description', 'leyka'),
                'description' => __('Please, enter Bank order description that will be shown to the donor when this payment method will be selected to make a donation.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(),
            ),
        );
    }

}

function leyka_add_gateway_sberbank() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Sberbank_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_sberbank');