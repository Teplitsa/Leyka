<?php
/**
 * Leyka_Chronopay_Gateway class
 */

class Leyka_Chronopay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {

        $this->_id = 'chronopay';
        $this->_title = __('Chronopay', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'chronopay_shared_sec' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Chronopay shared_sec', 'leyka'),
                'description' => __('Please, enter your Chronopay shared_sec value here. It can be found in your contract.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 4G0i8590sl5Da37I', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'chronopay_ip' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '159.255.220.140',
                'title' => __('Chronopay IP', 'leyka'),
                'description' => __('IP address to check for requests.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 159.255.220.140', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'chronopay_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => 1,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check if Chronopay integration is in test mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['chronopay_card'])) {
            $this->_payment_methods['chronopay_card'] = Leyka_Chronopay_Card::get_instance();
            $this->_payment_methods['chronopay_card']->initialize_pm_options();
//            $this->_payment_methods['chronopay_card']->save_settings();
        }
		
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

//        if($gateway_id != $this->_id || empty($this->_payment_methods[$pm_id]))
//            return;
    }

    public function submission_redirect_url($current_url, $pm_id) {	
	
        switch($pm_id) {
            case 'chronopay_card':
                $current_url =  leyka_options()->opt('chronopay_test_mode') ?
                    'https://payments.test.chronopay.com/' : 'https://payments.chronopay.com/';
                break;
        }

        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {
		
		if(false === strpos($pm_id, 'chronopay'))
			return $form_data_vars; //it's not our PM
			
        $donation = new Leyka_Donation($donation_id);
		
		$product_id_optname = $pm_id.'_product_id_'.$donation->currency; //product_id depends of PM and curr
		$chronopay_product_id = leyka_options()->opt($product_id_optname); 
		$sharedsec = leyka_options()->opt('chronopay_shared_sec');
		$price = number_format((float)$donation->amount, 2,'.','');
		$lang = 'ru_RU' == get_locale() ? 'ru' : 'en';
		$country = ($donation->currency == 'rur') ? 'RUS' : '';
			
        $form_data_vars =  array(
            'product_id' => $chronopay_product_id, 
			'product_price' => $price,
			'product_price_currency' => $this->_get_currency_id($donation->currency), 	
			'cs1'           => esc_attr($donation->title), // purpose of the donation
			'cs2'           => $donation_id, // payment id
			
			'cb_url'      => $this->_get_callback_service_url(), //url for gateway callbacks
			'cb_type'     => 'P',
			'success_url' => leyka_get_success_page_url(),
			'decline_url' => leyka_get_failure_page_url(),
			
			'sign'        => md5($chronopay_product_id.'-'.$price.'-'.$sharedsec),
			'language'    => $lang,			
			'email'       => $donation->donor_email			
        );
		
		if($country)
			$form_data_vars['country'] = $country;
			
		return $form_data_vars;
    }
	
	/* submission helpers */
	protected function _get_callback_service_url(){
		
		$path = 'leyka/service/'.$this->_id.'/response';
		return home_url($path);
	}
	
	protected function _get_currency_id($leyka_currency_id){
		
		$chronopay_currencies = array(
			'rur' => 'RUB',
			'usd' => 'USD',
			'eur' => 'EUR'
		);
		
		return isset($chronopay_currencies[$leyka_currency_id]) ? $chronopay_currencies[$leyka_currency_id] : 'RUB';		
	}
	
    public function log_gateway_fields($donation_id) {
        
    }

    public function _handle_service_calls($call_type = '') {
		
		$error = false;
		
		//test for IP
		if(empty($_SERVER['REMOTE_ADDR']) || trim(stripslashes($_SERVER['REMOTE_ADDR'])) != leyka_options()->opt('chronopay_ip'))
			$error = true;

		//test for sign
		$sharedsec = leyka_options()->opt('chronopay_shared_sec');;
		$customer_id = isset($_POST['customer_id'])? trim(stripslashes($_POST['customer_id'])) : '';
		$transaction_id = isset($_POST['transaction_id']) ? trim(stripslashes($_POST['transaction_id'])): '';
		$transaction_type = isset($_POST['transaction_type']) ? trim(stripslashes($_POST['transaction_type'])) : '';
		$total = isset($_POST['total']) ? trim(stripslashes($_POST['total'])) : '';		
		$sign = md5($sharedsec.$customer_id.$transaction_id.$transaction_type.$total);
		
		if(!isset($_POST['sign']) || $sign != trim(stripslashes($_POST['sign'])))
			$error = true;
		
		//security fail or not
		if($error == true){ //send notification on security fail			
			$admin_to = get_option('admin_email');
			$message = __("This message has been sent because a call to your ChronoPay function was made by a server that did not have the correct security key.  This could mean someone is trying to hack your payment site.  The details of the call are below.", 'leyka')."\n\r\n\r";
						
			$message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
			$message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
			$message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
			
			
			wp_mail($admin_to, __("ChronoPay Security Key Failed!", 'leyka'), $message);
			status_header( 200 );
			die();			
		}
		
		// store donation data		
		$donation = new Leyka_Donation(intval(trim(stripslashes($_POST['cs2']))));
		if($donation->status != 'funded') {
            $donation->add_gateway_response($_POST);
            $donation->set_status('funded');
        }
		
		// replay
		status_header(200);
		die();        
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();
        
        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) )
            return array();

        return array(
			__('Operatioin status', 'leyka') => $response_vars['transaction_type'],
			__('Transaction ID', 'leyka') => $response_vars['transaction_id'],
			__('Full donation amount:', 'leyka') => $response_vars['total'].' '.$donation->currency_label,
			__("Gateway's donor ID:", 'leyka') => $response_vars['customer_id'],
			__('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['date']))
        );		
    }
	
} //class end


class Leyka_Chronopay_Card extends Leyka_Payment_Method {

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

        $this->_id = empty($params['id']) ? 'chronopay_card' : $params['id'];

        $this->_label = empty($params['label']) ? __('Payment with Banking Card', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('chronopay_card_description') : $params['desc'];

        $this->_gateway_id = 'chronopay';
        
        $this->_active = isset($params['active']) ? 1 : 0;
//        $this->_active = (int)in_array($this->_gateway_id.'-'.$this->_id, leyka_options()->opt('pm_available'));

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];
				
        $this->_icons = apply_filters('leyka_payment_method_icons', array(
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/master.png',
        ), $this->_id);

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {
            
            if(leyka_options()->opt('chronopay_card_product_id_rur'))
                $this->_supported_currencies[] = 'rur';
            if(leyka_options()->opt('chronopay_card_product_id_usd'))
                $this->_supported_currencies[] = 'usd';
            if(leyka_options()->opt('chronopay_card_product_id_eur'))
                $this->_supported_currencies[] = 'eur';

        } else
            $this->_supported_currencies = $params['currencies'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

//        echo '<pre>Here: ' . print_r(leyka_options()->opt('chronopay_card_product_id_rur'), 1) . '</pre>';

        //add_action('leyka_service_call-'.$this->_id, 'leyka_yandex_handle_service_call'); //AL: WTF ?

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'chronopay_card_description' => array(
                'type' => 'html',
                'default' => __('Chronopay allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="http://www.chronopay.com/ru/">Chronopay</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Chronopay bank card payment description', 'leyka'),
                'description' => __('Please, enter Chronopay gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
			'chronopay_card_product_id_rur' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Chronopay product_id for RUR', 'leyka'),
                'description' => __('Please, enter Chronopay product_id for RUR currency.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
			'chronopay_card_product_id_usd' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Chronopay product_id for USD', 'leyka'),
                'description' => __('Please, enter Chronopay product_id for USD currency.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
			'chronopay_card_product_id_eur' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Chronopay product_id for EUR', 'leyka'),
                'description' => __('Please, enter Chronopay product_id for EUR currency.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

//    public function modify_options_values() {
//
//        $this->_description = leyka_options()->opt_safe($this->_id.'_description');
//
//        $this->_supported_currencies = array();
//        if(leyka_options()->opt('chronopay_card_product_id_rur'))
//            $this->_supported_currencies[] = 'rur';
//        if(leyka_options()->opt('chronopay_card_product_id_usd'))
//            $this->_supported_currencies[] = 'usd';
//        if(leyka_options()->opt('chronopay_card_product_id_eur'))
//            $this->_supported_currencies[] = 'eur';
//    }
}

//add_action('leyka_add_gateway', function(){
leyka()->add_gateway(Leyka_Chronopay_Gateway::get_instance());	
//}, 11);