<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Yandex_Gateway class
 */

class Leyka_Yandex_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {

        $this->_id = 'yandex';
        $this->_title = __('Yandex.Money', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'yandex_shop_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Yandex shopId', 'leyka'),
                'description' => __('Please, enter your Yandex.Money shop ID here. It can be found in your Yandex contract.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 12345', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'yandex_scid' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Yandex scid', 'leyka'),
                'description' => __('Please, enter your Yandex.Money shop showcase ID (SCID) here. It can be found in your Yandex contract.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 12345', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'yandex_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check if Yandex integration is in test mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['yandex_card'])) {
            $this->_payment_methods['yandex_card'] = Leyka_Yandex_Card::get_instance();
            $this->_payment_methods['yandex_card']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['yandex_money'])) {
            $this->_payment_methods['yandex_money'] = Leyka_Yandex_Money::get_instance();
            $this->_payment_methods['yandex_money']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['yandex_wm'])) {
            $this->_payment_methods['yandex_wm'] = Leyka_Yandex_Webmoney::get_instance();
            $this->_payment_methods['yandex_wm']->initialize_pm_options();
        }

        /** @todo Until this PM's API will be upgraded. */
//        if(empty($this->_payment_methods['yandex_money_quick'])) {
//            $this->_payment_methods['yandex_money_quick'] = Leyka_Yandex_Money_Quick::get_instance();
//            $this->_payment_methods['yandex_money_quick']->initialize_pm_options();
//        }

        /** @todo До получения возможности протестировать */
//        if(empty($this->_payment_methods['yandex_mobile'])) {
//            $this->_payment_methods['yandex_mobile'] = Leyka_Yandex_Mobile::get_instance();
//            $this->_payment_methods['yandex_mobile']->initialize_pm_options();
//        }

        //...
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        switch($pm_id) {
            case 'yandex_money':
            case 'yandex_card':
            case 'yandex_wm':
//            case 'yandex_terminal':
//            case 'yandex_mobile':
                return leyka_options()->opt('yandex_test_mode') ?
                    'https://demomoney.yandex.ru/eshop.xml' : 'https://money.yandex.ru/eshop.xml';
            case 'yandex_money_quick':
                return 'https://money.yandex.ru/quickpay/bankconfirm.xml';
            default:
                return $current_url;
        }
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);

        switch($pm_id) { // PC - Yandex.money, AC - bank card, WM - Webmoney, MC - mobile payments
            case 'yandex_money': $payment_type = 'PC'; break;
            case 'yandex_card': $payment_type = 'AC'; break;
            case 'yandex_wm': $payment_type = 'WM'; break;
//            case 'yandex_terminal': $payment_type = 'GP'; break;
//            case 'yandex_mobile': $payment_type = 'MC'; break;
            default:
                $payment_type = '';
        }

        return array(
            'scid' => leyka_options()->opt('yandex_scid'),
            'shopId' => leyka_options()->opt('yandex_shop_id'),
            'sum' => $donation->amount,
            'customerNumber' => $donation->donor_email,
            'orderNumber' => $donation_id,
            'paymentType' => $payment_type,
            'shopSuccessURL' => leyka_get_success_page_url(),
            'shopFailURL' => leyka_get_failure_page_url(),
            'cps_email' => $donation->donor_email,
//            '' => ,
        );
    }

    public function log_gateway_fields($donation_id) {
    }

    /** Wrapper method to answer checkOrder and paymentAviso service calls */
    private function _callback_answer($is_error = false, $callback_type = 'co', $message = '', $tech_message = '') {

        $is_error = !!$is_error;
        $tech_message = $tech_message ? $tech_message : $message;
        $callback_type = $callback_type == 'co' ? 'checkOrderResponse' : 'paymentAvisoResponse';

        if($is_error)
            die('<?xml version="1.0" encoding="UTF-8"?>
<'.$callback_type.' performedDatetime="'.date(DATE_ATOM).'"
code="1000" invoiceId="'.(int)$_POST['invoiceId'].'"
shopId="'.leyka_options()->opt('yandex_shop_id').'"
message="'.$message.'"
techMessage="'.$tech_message.'"/>');

        die('<?xml version="1.0" encoding="UTF-8"?>
<'.$callback_type.' performedDatetime="'.date(DATE_ATOM).'"
code="0" invoiceId="'.(int)$_POST['invoiceId'].'"
shopId="'.leyka_options()->opt('yandex_shop_id').'"/>');
    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            case 'check_order': // Gateway test before the payment - to check if it's correct

                if($_POST['action'] != 'checkOrder') // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'co', __('Wrong service operation', 'leyka'));

                $_POST['orderNumber'] = (int)$_POST['orderNumber']; // Donation ID
                if( !$_POST['orderNumber'] )
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));

                $donation = new Leyka_Donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount'])
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));

                $donation->add_gateway_response($_POST);

//                set_transient('leyka_yandex_test_cho', '<pre>'.print_r($_POST, true).'</pre>', 60*60*24);

                $this->_callback_answer(); // OK for yandex money payment
                break; // Not needed, just so my IDE can relax

            case 'payment_aviso':

                if($_POST['action'] != 'paymentAviso') // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'pa', __('Wrong service operation', 'leyka'));

                $_POST['orderNumber'] = (int)$_POST['orderNumber']; // Donation ID
                if( !$_POST['orderNumber'] )
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));

                $donation = new Leyka_Donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount'])
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));

                if($donation->status != 'funded') {

                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';
                    Leyka_Donation_Management::send_all_emails($donation->id);
                }

//                set_transient('leyka_yandex_test_pa', '<pre>'.print_r($_POST, true).'</pre>', 60*60*24);
                $this->_callback_answer(0, 'pa'); // OK for yandex money payment
                break; // Not needed, just so my IDE can relax

            default:
        }
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();
        
        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) )
            return array();

        $action_label = $response_vars['action'] == 'checkOrder' ?
            __('Donation confirmation', 'leyka') : __('Donation approval notice', 'leyka');

        return array(
            __('Last response operation:', 'leyka') => $action_label,
            __('Gateway invoice ID:', 'leyka') => $response_vars['invoiceId'],
            __('Full donation amount:', 'leyka') =>
                (float)$response_vars['orderSumAmount'].' '.$donation->currency_label,
            __('Donation amount after gateway commission:', 'leyka') =>
                (float)$response_vars['shopSumAmount'].' '.$donation->currency_label,
            __("Gateway's donor ID:", 'leyka') => $response_vars['customerNumber'],
            __('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['requestDatetime'])),
        );
    }
}


class Leyka_Yandex_Money extends Leyka_Payment_Method {

    /** @var Leyka_Yandex_Money */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }
    
    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */
            return static::$_instance;

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'yandex_money' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Virtual cash Yandex.money', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Yandex.money', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('yandex_money_description') : $params['desc'];

        $this->_gateway_id = 'yandex';

        $this->_active = isset($params['active']) ? $params['active'] : true;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
//            LEYKA_PLUGIN_BASE_URL.'gateways/quittance/icons/sber_s.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        $this->_supported_currencies = empty($params['currencies']) ? array('rur',) : $params['currencies'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        //add_action('leyka_service_call-'.$this->_id, 'leyka_yandex_handle_service_call');

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'yandex_money_description' => array(
                'type' => 'html',
                'default' => __("Yandex.Money is a simple and safe payment system to pay for goods and services through internet. You will have to fill a payment form, you will be redirected to the <a href='https://money.yandex.ru/'>Yandex.Money website</a> to confirm your payment. If you haven't got a Yandex.Money account, you can create it there.", 'leyka'),
                'title' => __('Yandex.Money description', 'leyka'),
                'description' => __('Please, enter Yandex.Money payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}


class Leyka_Yandex_Card extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Yandex_Card */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'yandex_card' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Banking Card', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Banking Card', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('yandex_card_description') : $params['desc'];

        $this->_gateway_id = 'yandex';
        
        $this->_active = isset($params['active']) ? 1 : 0;
//        $this->_active = (int)in_array($this->_gateway_id.'-'.$this->_id, leyka_options()->opt('pm_available'));

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        $this->_supported_currencies = empty($params['currencies']) ? array('rur',) : $params['currencies'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        //add_action('leyka_service_call-'.$this->_id, 'leyka_yandex_handle_service_call');

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'yandex_card_description' => array(
                'type' => 'html',
                'default' => __('Yandex.Money allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://money.yandex.ru/">Yandex.Money website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Yandex bank card payment description', 'leyka'),
                'description' => __('Please, enter Yandex.Money bank cards payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Yandex_Webmoney extends Leyka_Payment_Method {

    /** @var Leyka_Yandex_Webmoney */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */
            return static::$_instance;

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'yandex_wm' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Virtual cash Webmoney', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Webmoney', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('yandex_wm_description') : $params['desc'];

        $this->_gateway_id = 'yandex';

        $this->_active = isset($params['active']) ? $params['active'] : true;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ? __('Donate', 'leyka') : $params['submit_label'];

        $this->_supported_currencies = empty($params['currencies']) ? array('rur',) : $params['currencies'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        //add_action('leyka_service_call-'.$this->_id, 'leyka_yandex_handle_service_call');

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'yandex_wm_description' => array(
                'type' => 'html',
                'default' => __('<a href="http://www.webmoney.ru/">WebMoney Transfer</a> is an international financial transactions system and an invironment for a business in Internet, founded in 1988. Up until now, WebMoney clients counts at more than 25 million people around the world. WebMoney system includes a services to account and exchange funds, attract new funding, solve quarrels and make a safe deals.', 'leyka'),
                'title' => __('WebMoney description', 'leyka'),
                'description' => __('Please, enter WebMoney payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_yandex() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Yandex_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_yandex', 5);