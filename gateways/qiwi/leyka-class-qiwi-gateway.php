<?php if( !defined('WPINC') ) { die; }

require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Web_Hook_Verification.php';
require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Web_Hook.php';
require_once LEYKA_PLUGIN_DIR.'gateways/qiwi/includes/Leyka_Qiwi_Gateway_Helper.php';

/** Leyka_Qiwi_Gateway class */
class Leyka_Qiwi_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_qiwi_response;
    protected $_qiwi_log = [];

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
        $this->_receiver_types = ['legal'];

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Qiwi_Card::get_instance();
        }
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'qiwi_public_key' => [
                'type' => 'text',
                'title' => __('Public key', 'leyka'),
                'description' => __('Please, enter your public key.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'XXXX')
            ],
            'qiwi_secret_key' => [
                'type' => 'text',
                'title' => __('Secret key', 'leyka'),
                'description' => __('Please, enter your secret key.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '2tbq1WQvsgQeziGY9vTLe1vDZNg7tmCyJb4Lh6STQOkQKrPCc6qrUiKEDZAj12heiD1GQX8jTnjMxLpMcSZuGZP7xbMpj6EiqTf6VeAEzGMeaskfzVSw13AeqyeqR'),
            ],
        ];

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        $description = $campaign->short_description;
        $amount = absint($donation->amount);

        $bill = new Leyka_Qiwi_Gateway_Helper();

        $response = $bill->create_bill(
            $donation_id,
            $amount,
            [
                'customer' => ['account' => $donation->donor_name, 'email' => $donation->donor_email,],
                'comment' => $description
            ]
        );

        if(empty($response['body'])) {
            leyka()->add_payment_form_error(new WP_Error(
                'gateway_settings_incorrect',
                __('The gateway you used has incorrect or missing settings', 'leyka')
            ));
        }

        $this->_qiwi_response = json_decode(wp_remote_retrieve_body($response));

        return $this->_qiwi_response;

    }

    public function submission_redirect_url($current_url, $pm_id) {

        return add_query_arg(
            ['url' => urlencode($this->_qiwi_response->payUrl)],
            site_url('/leyka/service/qiwi/redirect/')
        );

    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        $this->_qiwi_log['QIWI_Form'] = $_POST;
        $this->_qiwi_log['QIWI_Response'] = $this->_qiwi_response;

        $donation->qiwi_donation_id_on_gateway_response = $this->_qiwi_log['QIWI_Response']->billId;

        $donation->add_gateway_response($this->_qiwi_log);

        return $form_data;

    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {
            case 'check_order':
            case 'notify':
            case 'process':
                do_action('leyka_qiwi_gateway_web_hook');
                break;
            case 'redirect':
                $url = add_query_arg(
                    ['successUrl' => urlencode(get_permalink(leyka_options()->opt('quittance_redirect_page')))],
                    urldecode($_GET['url'])
                );
                wp_redirect($url, 302);
                exit();
            default:
        }

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if(!$vars || !is_array($vars)) {
            return [];
        }

        return apply_filters(
            'leyka_donation_gateway_response',
            [
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
            ],
            $donation
        );

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'qiwi_donation_id_on_gateway_response':
                return $donation->get_meta('_leyka_donation_id_on_gateway_response');
            default:
                return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'qiwi_donation_id_on_gateway_response':
                return $donation->set_meta('_leyka_donation_id_on_gateway_response', $value);
            default:
                return false;
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

function leyka_add_gateway_qiwi() {
    leyka_add_gateway(Leyka_Qiwi_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_qiwi');
