<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Liqpay_Gateway class
 */

include ('api/liqpay.php');
 
class Leyka_Liqpay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {
        $this->_id = 'liqpay';
        $this->_title = __('Liqpay', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'liqpay_public_key' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Liqpay public key', 'leyka'),
                'description' => __('Public key (API v. 3.0) supplied with Liqpay merchant account', 'leyka'),
                'required' => 1,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'liqpay_private_key' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('Liqpay private key', 'leyka'),
                'description' => __('Private key (API v. 3.0) supplied with Liqpay merchant account', 'leyka'),
                'required' => 1,
                'placeholder' => '',
				'is_password' => true,
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
			'liqpay_sandbox' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => false,
                'default' => false,
                'title' => __('Sandbox mode', 'leyka'),
                'description' => __("Check to enable sandbox (development mode). Payments will not be charged.", 'leyka'),
                'required' => 1,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
			'liqpay_description' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => false,
                'default' => false,
                'title' => __('Default payment description', 'leyka'),
                'description' => __("Fill to add default description to Liqpay payments", 'leyka'),
                'required' => 0,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['liqpay'])) {
            $this->_payment_methods['liqpay'] = Leyka_Liqpay::get_instance();
            $this->_payment_methods['liqpay']->initialize_pm_options();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
	
    }

    public function submission_redirect_url($current_url, $pm_id) {	
        return 'https://www.liqpay.com/api/checkout';
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) )
			return $form_data_vars; //it's not our PM

        $donation = new Leyka_Donation($donation_id);
	    $amount = number_format((float)$donation->amount, 2, '.', '');
        $currency = mb_strtoupper($donation->currency);
		
		$public = leyka_options()->opt('liqpay_public_key');
		$private = leyka_options()->opt('liqpay_private_key');
		
		if($currency == 'RUR')
			$currency = 'RUB';
			
        $form_data_vars =  array(
			'version' 					=> 3,
            'public_key' 				=> leyka_options()->opt('liqpay_public_key'),
			'amount' 					=> $amount,
            'currency' 					=> $currency,
			'description' 				=> $donation->payment_title,
            'order_id' 					=> $donation_id,
            'recurringbytoken' 			=> 0,
            'type' 						=> 'buy', // All sum is required to complete the donation
			'pay_way' 					=> 'card,liqpay,delayed,privat24',
			'server_url' 				=> home_url('leyka/service/'.$this->_id.'/response/'),
            'language' 					=> get_locale() == 'ru_RU' ? 'ru' : 'en',
			'sandbox' 					=> leyka_options()->opt('liqpay_sandbox') ? 1 : 0,
//            '' => '',
        );

		$submission = array();
		
		//check params
		
		$submission['data'] = base64_encode(json_encode($form_data_vars));
		
		try {
		
			$api = new Liqpay($public, $private);
			$submission['signature'] = $api->cnb_signature($form_data_vars);
		
		} catch (Exception $e) {
			
		}
		
		return $submission;
    }

    public function log_gateway_fields($donation_id) {
	
    }

    public function _handle_service_calls($call_type = '') {
	
	
		//decode response
		$data = json_decode(base64_decode($_POST['data']));
		$data = (array)$data;
		$private = leyka_options()->opt('liqpay_private_key');
		$signature = base64_encode( sha1( $private . $_POST['data'] . $private, 1 ));
		
        if($signature != $_POST['signature']) {

            $message = __("This message has been sent because a call to your Liqpay callback was made with wrong signature. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_DATA:\n\r".print_r($data,true)."\n\r\n\r";
            $message .= "THEIR_SIGNATURE:\n\r".print_r($signature,true)."\n\r\n\r";
			
            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
			print 'Signature mismatch!<br/>';
			print_r($data);
            wp_mail(get_option('admin_email'), __('Leyka: Liqpay signature mismatch!', 'leyka'), $message);
            status_header(200);
            die();
        }

        $donation = new Leyka_Donation($data['order_id']);

        // Single payment:
        switch($data['status']) {
            case 'failure': 
				$new_status = 'failed'; 
				break;
            case 'success':
			case 'sandbox': 
				$new_status = 'funded'; 
				break;
            case 'reversed': 
				$new_status = 'refunded'; 
				break;
            default:
                $new_status = 'submitted';
				break;
        }

        if($donation->status != $new_status) 
		{
			
			$donation->add_gateway_response($data);
			$donation->status = $new_status;
			Leyka_Donation_Management::send_all_emails($donation->id);
			
        }

		status_header(200);
		die();
    }

    protected function _get_value_if_any($arr, $key, $val = false) {

        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

        $vars = $donation->gateway_response;
        if( !$vars || !is_array($vars) )
            return array();

        return array(
            __('Operation date:', 'leyka') => $this->_get_value_if_any($donation->gateway_response, 'operation_date', date('d.m.Y, H:i:s', $donation->gateway_response['operation_date'])),
			__('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'transaction_id'),
            __('Order ID:', 'leyka') => $this->_get_value_if_any($vars, 'order_id'),
			__('Public Key:', 'leyka') => $this->_get_value_if_any($vars, 'public_key'),
			__('Amount:', 'leyka') => $this->_get_value_if_any($vars, 'amount'),
			__('Donation currency:', 'leyka') => $this->_get_value_if_any($vars, 'currency'),
			__('Description:', 'leyka') => $this->_get_value_if_any($vars, 'description'),
            __('Operation status:', 'leyka') => $this->_get_value_if_any($vars, 'status'),
			__('Sender phone:', 'leyka') => $this->_get_value_if_any($vars, 'sender_phone'),
			__("Payment type", 'leyka') => $this->_get_value_if_any($vars, 'type'),
        );		
    }
} // Gateway class end


class Leyka_Liqpay extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Liqpay */
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

        $this->_id = empty($params['id']) ? 'liqpay' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Liqpay Liq&Buy v. 3.0', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Liqpay', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('liqpay_description') : $params['desc'];

        $this->_gateway_id = 'liqpay';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/liqpay/icons/liqpay.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/liqpay/icons/privat.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'uah';
            $this->_supported_currencies[] = 'usd';
            $this->_supported_currencies[] = 'eur';
			$this->_supported_currencies[] = 'rur';

        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'liqpay_description' => array(
                'type' => 'html',
                'default' => __('Liqpay allows to make payments via bank card, Liqpay wallet, Privat24 and delayed payments through TCOs', 'leyka'),
                'title' => __('Liqpay payment description', 'leyka'),
                'description' => __('Please, enter Liqpay gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_liqpay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Liqpay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_liqpay', 11);
