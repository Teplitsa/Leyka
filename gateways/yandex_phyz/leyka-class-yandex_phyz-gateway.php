<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Yandex_phyz_Gateway class
 */

class Leyka_Yandex_Phyz_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {

        $this->_id = 'yandex_phyz';
        $this->_title = __('Yandex.Money for physical persons', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'yandex_money_account' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Yandex account ID', 'leyka'),
                'description' => __("Please, enter your Yandex.Money account ID here. It's else known as Yandex.wallet number.", 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 4100111111111111', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'yandex_money_secret' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Yandex account API secret', 'leyka'),
                'description' => __('Please, enter your Yandex.Money account API secret string here.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., QweR+1TYUIo/p2aS3DFgHJ4K5', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['yandex_phyz_card'])) {
            $this->_payment_methods['yandex_phyz_card'] = Leyka_Yandex_Phyz_Card::get_instance();
            $this->_payment_methods['yandex_phyz_card']->initialize_pm_options();
//            $this->_payment_methods['yandex_phyz_card']->save_settings();
        }

        if(empty($this->_payment_methods['yandex_phyz_money'])) {
            $this->_payment_methods['yandex_phyz_money'] = Leyka_Yandex_Phyz_Money::get_instance();
            $this->_payment_methods['yandex_phyz_money']->initialize_pm_options();
//            $this->_payment_methods['yandex_phyz_money']->save_settings();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        switch($pm_id) {
            case 'yandex_phyz_money':
            case 'yandex_phyz_card':
                return 'https://money.yandex.ru/quickpay/confirm.xml';
            default:
                return $current_url;
        }
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        switch($pm_id) { // PC - ЯД, AC - картой, GP - платеж по коду через терминал, MC - моб. платёж
            case 'yandex_phyz_money': $payment_type = 'PC'; break;
            case 'yandex_phyz_card': $payment_type = 'AC'; break;
//            case 'yandex_terminal': $payment_type = 'GP'; break;
//            case 'yandex_mobile': $payment_type = 'MC'; break;
            default:
                $payment_type = '';
        }

		$name = esc_attr(get_bloginfo('name').': Пожертвование');

        return array(
            'receiver' => leyka_options()->opt('yandex_money_account'),
            'sum' => $donation->amount,
            'formcomment' => $name,
			'short-dest' => $name,
			'targets' => esc_attr($campaign->payment_title),
			'quickpay-form' => 'donate',
            'label' => $donation_id,
            'paymentType' => $payment_type,
            'shopSuccessURL' => leyka_get_success_page_url(),
            'shopFailURL' => leyka_get_failure_page_url(),
            'cps_email' => $donation->donor_email,
//            '' => ,
        );
    }

    public function log_gateway_fields($donation_id) {
    }

    /** Wrapper method to answer the checkOrder service calls */
    private function _check_order_answer($is_error = false, $message = '', $tech_message = '') {

        $is_error = !!$is_error;
        $tech_message = $tech_message ? $tech_message : $message;

        if($is_error)
            die('<?xml version="1.0" encoding="UTF-8"?>
<checkOrderResponse performedDatetime="'.date(DATE_ATOM).'"
code="1000" operation_id="'.(int)@$_POST['operation_id'].'"
account_id="'.leyka_options()->opt('yandex_money_account').'"
message="'.$message.'"
techMessage="'.$tech_message.'"/>');

        die('<?xml version="1.0" encoding="UTF-8"?>
<checkOrderResponse performedDatetime="'.date(DATE_ATOM).'"
code="0" operation_id="'.(int)@$_POST['operation_id'].'"
account_id="'.leyka_options()->opt('yandex_money_account').'"/>');
    }

    public function _handle_service_calls($call_type = '') {
	
		error_log_yandex_phyz("\n\n---- $call_type ----\n\n".print_r($_REQUEST, true));

        $label = (int)@$_POST['label']; // Donation ID
        $amount = @$_POST['withdraw_amount'];

        error_log_yandex_phyz("Label=$label\n");
        error_log_yandex_phyz("Amount=$amount\n");

        if( !$label ) {
            error_log_yandex_phyz("Label is empty\n");
            return;
        }

        $donation = new Leyka_Donation($label);
        error_log_yandex_phyz("Donation initialized\n");
        error_log_yandex_phyz(print_r($donation, TRUE) . "\n");

        $params_to_sha1 = implode('&', array(
            @$_POST['notification_type'],
            @$_POST['operation_id'],
            @$_POST['amount'],
            @$_POST['currency'],
            @$_POST['datetime'],
            @$_POST['sender'],
            @$_POST['codepro'],
            leyka_options()->opt('yandex_money_secret'),
            @$_POST['label']
        ));
        error_log_yandex_phyz("Params_to_sha1=$params_to_sha1\n");
        $sha1 = sha1($params_to_sha1);
        error_log_yandex_phyz("sha1=$sha1\n");

        if($sha1 != @$_POST['sha1_hash']) {

            error_log_yandex_phyz("Invalid response sha1_hash\n");
            $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Invalid response sha1_hash', 'leyka'));
        } elseif($donation) {

            error_log_yandex_phyz("Donation OK\n");
            error_log_yandex_phyz('$donation->sum='.$donation->sum."\n");
            error_log_yandex_phyz('$donation->status='.$donation->status."\n");

            if($donation->sum != $amount) {

                error_log_yandex_phyz("Donation sum is unmatched\n");
                $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));

            } elseif($donation->status != 'funded') {

                error_log_yandex_phyz("Donation is funded\n");

                if( !empty($_POST['notification_type']) ) { // Update a donation's actual PM, if needed

                    $actual_pm = $_POST['notification_type'] == 'card-incoming' ?
                        'yandex_phyz_card' : 'yandex_phyz_money';

                    if($donation->pm_id != $_POST['notification_type'])
                        $donation->pm_id = $actual_pm;
                }

                $donation->add_gateway_response($_POST);
                $donation->status = 'funded';
                Leyka_Donation_Management::send_all_emails($donation->id);

            } else {
                error_log_yandex_phyz("Already funded\n");
            }

            $this->_check_order_answer();

        } else {

            error_log_yandex_phyz("There is no donation in Leyka DB\n");
            $this->_check_order_answer(1, __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Unregistered donation ID', 'leyka'));
        }
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) )
            return array();

		$payment_type = '';
		if($response_vars['notification_type'] == 'p2p-incoming') {
			$payment_type = __('Using Yandex.Money Account', 'leyka');
		} elseif($response_vars['notification_type'] == 'card-incoming') {
			$payment_type = __('Using Banking Card', 'leyka');
		}

        return array(
            __('Last response operation:', 'leyka') => __('Donation confirmation', 'leyka'),
            __('Yandex payment type:', 'leyka') => $payment_type,
            __('Gateway invoice ID:', 'leyka') => $response_vars['operation_id'],
            __('Full donation amount:', 'leyka') =>
                (float)$response_vars['withdraw_amount'].' '.$donation->currency_label,
            __('Donation amount after gateway commission:', 'leyka') =>
                (float)$response_vars['amount'].' '.$donation->currency_label,
            __("Gateway's donor ID:", 'leyka') => $response_vars['sender'],
            __('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['datetime'])),
        );
    }
}


class Leyka_Yandex_Phyz_Money extends Leyka_Payment_Method {

    /** @var Leyka_Yandex_phyz_Money */
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

        $this->_id = empty($params['id']) ? 'yandex_phyz_money' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Virtual cash Yandex.Money', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Virtual cash Yandex.Money', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('yandex_phyz_money_description') : $params['desc'];

        $this->_gateway_id = 'yandex_phyz';

        $this->_active = isset($params['active']) ? $params['active'] : true;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex_phyz/icons/yandex_phyz_money_s.png',
//            LEYKA_PLUGIN_BASE_URL.'gateways/quittance/icons/sber_s.png',
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
            'yandex_phyz_money_description' => array(
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


class Leyka_Yandex_Phyz_Card extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Yandex_phyz_Card */
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
		 
        $this->_id = empty($params['id']) ? 'yandex_phyz_card' : $params['id'];

        $this->_label = empty($params['label']) ? __('Payment with Banking Card Yandex', 'leyka') : $params['label'];

//        echo '<pre>2: ' . print_r(leyka_options()->opt_safe('yandex_phyz_card_description'), 1) . '</pre>';
        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('yandex_phyz_card_description') : $params['desc'];

        $this->_gateway_id = 'yandex_phyz';

        $this->_active = isset($params['active']) ? 1 : 0;
//        $this->_active = (int)in_array($this->_gateway_id.'-'.$this->_id, leyka_options()->opt('pm_available'));

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex_phyz/icons/yandex_phyz_money_s.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex_phyz/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex_phyz/icons/master.png',
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
            'yandex_phyz_card_description' => array(
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

function error_log_yandex_phyz($string) {
//	return;
	error_log($string, 3, WP_CONTENT_DIR.'/uploads/phyz-error.log');
}

function leyka_add_gateway_yandex_phyz() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Yandex_Phyz_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_yandex_phyz', 25);