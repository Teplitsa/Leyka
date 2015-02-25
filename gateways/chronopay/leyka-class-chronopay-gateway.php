<?php if( !defined('WPINC') ) die;
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
                'is_password' => true,
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
        }
        if(empty($this->_payment_methods['chronopay_card_rebill'])) {
            $this->_payment_methods['chronopay_card_rebill'] = Leyka_Chronopay_Card_Rebill::get_instance();
            $this->_payment_methods['chronopay_card_rebill']->initialize_pm_options();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {	
	
        switch($pm_id) {
            case 'chronopay_card':
            case 'chronopay_card_rebill':
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

		$chronopay_product_id = leyka_options()->opt($pm_id.'_product_id_'.$donation->currency);
		$sharedsec = leyka_options()->opt('chronopay_shared_sec');
		$price = number_format((float)$donation->amount, 2,'.','');

//        if(empty($form_data_vars['cur_lang']))
        $lang = get_locale() == 'ru_RU' ? 'ru' : 'en';
//        else
//            $lang = $form_data_vars['cur_lang'];

		$country = ($donation->currency == 'rur') ? 'RUS' : '';
			
        $form_data_vars =  array(
            'product_id' => $chronopay_product_id, 
			'product_price' => $price,
			'product_price_currency' => $this->_get_currency_id($donation->currency), 	
			'cs1' => esc_attr($donation->title), // Purpose of the donation
			'cs2' => $donation_id, // Payment ID

			'cb_url' => home_url('leyka/service/'.$this->_id.'/response/'), // URL for the gateway callbacks
			'cb_type' => 'P',
			'success_url' => leyka_get_success_page_url(),
			'decline_url' => leyka_get_failure_page_url(),

			'sign' => md5($chronopay_product_id.'-'.$price.'-'.$sharedsec),
			'language' => $lang,
			'email' => $donation->donor_email,
        );

		if($country)
			$form_data_vars['country'] = $country;

		return $form_data_vars;
    }

	protected function _get_currency_id($leyka_currency_id){
		
		$chronopay_currencies = array(
			'rur' => 'RUB',
			'usd' => 'USD',
			'eur' => 'EUR'
		);
		
		return isset($chronopay_currencies[$leyka_currency_id]) ?
            $chronopay_currencies[$leyka_currency_id] : 'RUB';
	}
	
    public function log_gateway_fields($donation_id) {

        $donation = new Leyka_Donation($donation_id);
        if($donation->payment_method_id == 'chronopay_card_rebill')
            $donation->payment_type = 'rebill';
        else if($donation->payment_method_id == 'chronopay_card')
            $donation->payment_type = 'single';
    }

    public function _handle_service_calls($call_type = '') {
		
		$error = false;
		
		// Test for gateway's IP:
		if(
            empty($_SERVER['REMOTE_ADDR']) ||
            trim(stripslashes($_SERVER['REMOTE_ADDR'])) != leyka_options()->opt('chronopay_ip')
        )
            $error = true;

        // Security fail or not:
        if($error == true) { // Send notification on security fail
            $admin_to = get_option('admin_email');
            $message = __("This message has been sent because a call to your ChronoPay function was made from an IP that did not match with the one in your Chronopay gateway setting. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
            $message .= "THEIR_IP:\n\r".print_r($_SERVER['REMOTE_ADDR'],true)."\n\r\n\r";
            $message .= "Chronopay IP setting value:\n\r".print_r(leyka_options()->opt('chronopay_ip'),true)."\n\r\n\r";

            wp_mail($admin_to, __('Chronopay IP check failed!', 'leyka'), $message);
            status_header(200);
            die();
        }

		// Test for e-sign:
		$sharedsec = leyka_options()->opt('chronopay_shared_sec');
		$customer_id = isset($_POST['customer_id'])? trim(stripslashes($_POST['customer_id'])) : '';
		$transaction_id = isset($_POST['transaction_id']) ? trim(stripslashes($_POST['transaction_id'])): '';
		$transaction_type = isset($_POST['transaction_type']) ? trim(stripslashes($_POST['transaction_type'])) : '';
		$total = isset($_POST['total']) ? trim(stripslashes($_POST['total'])) : '';		
		$sign = md5($sharedsec.$customer_id.$transaction_id.$transaction_type.$total);

		if(empty($_POST['sign']) || $sign != trim(stripslashes($_POST['sign'])))
			$error = true;

		// Security fail or not:
		if($error == true) { // Send notification on security fail
			$admin_to = get_option('admin_email');
			$message = __("This message has been sent because a call to your ChronoPay function was made by a server that did not have the correct security key.  This could mean someone is trying to hack your payment site.  The details of the call are below.", 'leyka')."\n\r\n\r";
						
			$message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
			$message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
			$message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";

			wp_mail($admin_to, __('Chronopay security key check failed!', 'leyka'), $message);
			status_header(200);
			die();
		}

        $donation_id = (int)stripslashes($_POST['cs2']);
        $donation = new Leyka_Donation($donation_id);

        if(strtolower($_POST['currency']) == 'rub')
            $currency_string = 'rur';
//        else if() $currency_string = 'usd';
        else {

            $admin_to = get_option('admin_email');
            $message = __("This message has been sent because a call to your ChronoPay callbacks URL was made with a currency parameter (POST['currency']) that Leyka is unknown of. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";

            wp_mail($admin_to, __('Chronopay gives unknown currency parameter!', 'leyka'), $message);
            status_header(200);
            die();
        }

        // Store donation data - rebill payment:
        if($_POST['product_id'] == leyka_options()->opt('chronopay_card_rebill_product_id_'.$currency_string)) {

            if($transaction_type == 'Purchase') { // Initial rebill payment

                if($donation->status != 'funded') {
                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';
                    $donation->type = 'rebill';

                    if( !$donation->donor_email && !empty($_POST['email']) )
                        $donation->donor_email = $_POST['email'];

                    Leyka_Donation_Management::send_all_emails($donation->id);

                    // Save donor's customer_id parameter to link this donation to all others in this recurrent chain:
                    $donation->chronopay_customer_id = $customer_id;
                }

            } else if($transaction_type == 'Rebill') { // Rebill payment

                $init_recurrent_payment = Leyka_Donation::get_init_recurrent_payment($customer_id);

                $donation_id = Leyka_Donation::add(array(
                    'status' => 'funded',
                    'payment_type' => 'rebill',
                    'purpose_text' => $init_recurrent_payment->title,
                    'campaign_id' => $init_recurrent_payment->campaign_id,
                    'payment_method_id' => $init_recurrent_payment->pm_id,
                    'gateway_id' => $init_recurrent_payment->gateway_id,
                    'donor_name' => $init_recurrent_payment->donor_name,
                    'donor_email' => $init_recurrent_payment->donor_email,
                    'amount' => $init_recurrent_payment->amount,
                    'currency' => $init_recurrent_payment->currency,

                    'chronopay_customer_id' => $customer_id,
//                    '' => '',
                ));

                Leyka_Donation_Management::send_all_emails($donation_id);
            }

        } else { // Single payment. For now, processing is just like initial rebills

            if($donation->status != 'funded') {
                $donation->add_gateway_response($_POST);
                $donation->status = 'funded';
                if( !$donation->donor_email && !empty($_POST['email']) )
                    $donation->donor_email = $_POST['email'];

                Leyka_Donation_Management::send_all_emails($donation->id);

                // Save donor's customer_id parameter.. just because we're scrupulous 0:)
                $donation->chronopay_customer_id = $customer_id;
            }
        }

		status_header(200);
		die();
    }

    public function cancel_recurrents(Leyka_Donation $donation) {

        $ch = curl_init();

        $product_id = leyka_options()->opt($donation->payment_method_id.'_product_id_'.$donation->currency);
        $hash = md5(leyka_options()->opt('chronopay_shared_sec').'-7-'.$product_id);

        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://gate.chronopay.com/',
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POSTFIELDS => "<request>
                <Opcode>7</Opcode>
                <hash>$hash</hash>
                <Customer>{$donation->chronopay_customer_id}</Customer>
                <Product>$product_id</Product>
            </request>",
        ));

        $result = curl_exec($ch);
        if($result === false) {

            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            die( json_encode(array('status' => 0, 'message' => $error." ($errno)")) );

        } else {

            $donation->add_gateway_response($result);

            $p = xml_parser_create();
            $response_xml = array();
            xml_parse_into_struct($p, $result, $response_xml);
            xml_parser_free($p);

            $response_ok = false;
            $response_text = '';
            $response_code = 0;
            foreach($response_xml as $index => $tag) {

                if(strtolower($tag['tag']) == 'code' && $tag['type'] == 'complete') {
                    $response_ok = $tag['value'] == '000';
                    if( !$response_ok ) {
                        $response_code = $tag['value'];
                        $response_text = $response_xml[$index+1]['value'];
                    }

                    break;
                }
            }

            curl_close($ch);
            if($response_ok) {

                // Save the fact that recurrents has been cancelled:
                $init_recurrent_donation = Leyka_Donation::get_init_recurrent_payment($donation->chronopay_customer_id);
                $init_recurrent_donation->recurrents_cancelled = true;

                die( json_encode(array('status' => 1, 'message' => __('Recurrent donations cancelled.', 'leyka'))) );

            } else
                die( json_encode(array('status' => 0, 'message' => sprintf(__('Error on the gateway side: %s', 'leyka'), $response_text." (code $response_code)"))) );

        }
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) )
            return array();

        return array(
			__('Operation status:', 'leyka') => $response_vars['transaction_type'],
			__('Transaction ID:', 'leyka') => $response_vars['transaction_id'],
			__('Full donation amount:', 'leyka') => $response_vars['total'].' '.$donation->currency_label,
			__("Gateway's donor ID:", 'leyka') => $response_vars['customer_id'],
			__('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['date']))
        );		
    }
} // gateway class end


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

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Banking Card', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Banking Card', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('chronopay_card_description') : $params['desc'];

        $this->_gateway_id = 'chronopay';

        $this->_active = isset($params['active']) ? 1 : 0;
//        $this->_active = (int)in_array($this->_gateway_id.'-'.$this->_id, leyka_options()->opt('pm_available'));

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/master.png',
        ));

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
}

class Leyka_Chronopay_Card_Rebill extends Leyka_Payment_Method {

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

        $this->_id = empty($params['id']) ? 'chronopay_card_rebill' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Rebilling payment with Banking Card', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Banking Card - monthly rebilling', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('chronopay_card_rebill_description') : $params['desc'];

        $this->_gateway_id = 'chronopay';

        $this->_active = isset($params['active']) ? 1 : 0;
//        $this->_active = (int)in_array($this->_gateway_id.'-'.$this->_id, leyka_options()->opt('pm_available'));

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/chronopay/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies']) && leyka_options()->opt('chronopay_card_rebill_product_id_rur'))
            $this->_supported_currencies[] = 'rur';
        else
            $this->_supported_currencies = empty($params['currencies']) ? array('rur') : $params['currencies'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'chronopay_card_rebill_description' => array(
                'type' => 'html',
                'default' => __('Chronopay allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="http://www.chronopay.com/ru/">Chronopay</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Chronopay bank card rebill payment description', 'leyka'),
                'description' => __('Please, enter Chronopay gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            'chronopay_card_rebill_product_id_rur' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Chronopay product_id for rebills in RUR', 'leyka'),
                'description' => __('Please, enter Chronopay product_id for rebills in RUR currency.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_chronopay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Chronopay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_chronopay', 11);