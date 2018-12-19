<?php if( !defined('WPINC') ) { die; }

require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Web_Hook_Verification.php';
require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Web_Hook.php';
require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Helper.php';

/**
 * Leyka_Qiwi_Gateway class
 */
class Leyka_Qiwi_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_qiwi_response;
    protected $_qiwi_log = array();

    protected function _set_attributes() {

        $this->_id = 'qiwi';
        $this->_title = __('QIWI Kassa', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="http://qiwi.com/">qiwi</a> is a Designer IT-solutions for the e-commerce market. Every partner receives the most comprehensive set of key technical options allowing to create a customer-centric payment system on site or in mobile application. Partners are allowed to receive payments in roubles and in other world currencies.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/qiwi/';
        $this->_registration_link = 'https://kassa.qiwi.com/pay/';

        $this->_min_commission = 2.9;
        $this->_receiver_types = array('legal');

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'qiwi_public_key' => array(
                'type' => 'text',
                'title' => __('Public key', 'leyka'),
                'description' => __('Please, enter your public key.', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., XXXX', 'leyka'),
            ),
            'qiwi_secret_key' => array(
                'type' => 'text',
                'title' => __('Secret key', 'leyka'),
                'description' => __('Please, enter your secret key.', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., XXXX', 'leyka'),
            ),
        );

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);
        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        $description = $campaign->short_description;
        $amount = (int)$donation->amount;
        $bill = new Leyka_Qiwi_Gateway_Helper();

        $response = $bill->create_bill(
            $donation_id,
            $amount,
            array(
                'customer' => array(
                    'account' => $donation->__get('donor_name'),
                    'email' => $donation->__get('donor_email')
                ),
                'comment' => $description
            )
        );

        if (empty($response['body'])) {
            $error = new WP_Error(
                'gateway_settings_incorrect',
                __('The gateway you used has incorrect or missing settings', 'leyka')
            );
            leyka()->add_payment_form_error($error);
        }

        $this->_qiwi_response = json_decode(wp_remote_retrieve_body($response));

        return $this->_qiwi_response;

    }

    public function submission_redirect_url($current_url, $pm_id) {

        $url = add_query_arg(
            array('url' => urlencode($this->_qiwi_response->payUrl)),
            site_url('/leyka/service/qiwi/redirect/')
        );

        return $url;

    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);

        $this->_qiwi_log['QIWI_Form'] = $_POST;
        $this->_qiwi_log['QIWI_Response'] = $this->_qiwi_response;
        $invoiceTemplateID = $this->_qiwi_log['QIWI_Response']->billId;

        update_post_meta($donation_id, '_leyka_donation_id_on_gateway_response', $invoiceTemplateID);

        $donation->add_gateway_response($this->_qiwi_log);

        return $form_data_vars;

    }

    public function _handle_service_calls($call_type = '') {

        switch ($call_type) {
            case 'check_order':
            case 'notify':
            case 'process':
                do_action('leyka_qiwi_gateway_web_hook');
                break;
            case 'redirect':
                $url = add_query_arg(
                    array('successUrl' => urlencode(get_permalink(leyka_options()->opt('quittance_redirect_page')))),
                    urldecode($_GET['url'])
                );
                wp_redirect($url, 302);
                exit();
            default:
        }

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if(!$vars || !is_array($vars)) {
            return array();
        }

        return array(
            __('Operation date:', 'leyka') => isset($vars['QIWI_Response']->creationDateTime) ?
                $vars['QIWI_Response']->creationDateTime : '',
            __('Shop Account:', 'leyka') => isset($vars['QIWI_Response']->siteId) ? $vars['QIWI_Response']->siteId : '',
            __('Shop bill ID:', 'leyka') => isset($vars['QIWI_Response']->billId) ? $vars['QIWI_Response']->billId : '',
            __('Donation currency:', 'leyka') => isset($vars['QIWI_Response']->amount->currency) ?
                $vars['QIWI_Response']->amount->currency : '',
            __('Operation status:', 'leyka') => isset($vars['QIWI_Response']->status->value) ?
                $vars['QIWI_Response']->status->value : '',
            __('Donor name:', 'leyka') => isset($vars['QIWI_Form']['leyka_donor_name']) ?
                $vars['QIWI_Form']['leyka_donor_name'] : '',
            __('Form url:', 'leyka') => isset($vars['QIWI_Response']->payUrl) ? $vars['QIWI_Response']->payUrl : '',
        );

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Qiwi_Card::get_instance();
        }
    }

}

class Leyka_Qiwi_Card extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'qiwi';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="http://qiwi.com/">qiwi</a> is a Designer IT-solutions for the e-commerce market. Every partner receives the most comprehensive set of key technical options allowing to create a customer-centric payment system on site or in mobile application. Partners are allowed to receive payments in roubles and in other world currencies.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('QIWI Kassa smart payment', 'leyka');
        $this->_label = __('QIWI Kassa', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

function leyka_add_gateway_qiwi() {
    leyka_add_gateway(Leyka_Qiwi_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_qiwi');
