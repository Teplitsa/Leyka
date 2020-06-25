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
        $this->default_currency = 'uah';
        $this->currency_label = 'UAH';        
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

        // Instantiate and save each of PM objects, if needed:
        if (empty($this->_payment_methods['liqpay'])) {
            $this->_payment_methods['liqpay'] = Leyka_Liqpay::get_instance();
            $this->_payment_methods['card'] = Leyka_Liqpay_Card::get_instance();
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

        switch (get_locale()) {
            case 'ru_RU':
                $language = 'ru';
                break;
            case 'uk':
                $language = 'uk';
                break;
            default:
                $language = 'en';
                break;
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
		
		} catch (Exception $e) {
			
		}
		
		return $submission;
    }

    public function log_gateway_fields($donation_id) {
	
    }

    public function _handle_service_calls($call_type = '') {	
	
		// Decode response
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
            error_log('Data mismatch');
            error_log($data);
            wp_mail(get_option('admin_email'), __('Leyka: Liqpay signature mismatch!', 'leyka'), $message);
            status_header(200);
            die();
        }


        $redirect_url = leyka_get_success_page_url();

        $donation = new Leyka_Donation($data['order_id']);


        $data['currency'] = strtolower($data['currency']);
        if($data['currency'] == 'rub') {
            $currency_string = 'rur';
        } else if($data['currency'] == 'uah') {
            $currency_string = 'uah';
        }else if($data['currency'] == 'usd') {
            $currency_string = 'usd';
        } else if($data['currency'] == 'eur') {
            $currency_string = 'eur';
        }

        // Payment failed
        if (in_array($data['status'], ['failure', 'try_again'])) {
            $data['status'] = 'failed';
            $new_status = 'failed';
            $redirect_url = leyka_get_failure_page_url();
        }

        // Subscription
        elseif (in_array($data['action'], ['subscribe', 'regular'])) {
            if (in_array($data['status'], ['subscribed', 'sandbox'])) {
                $new_status = 'funded';
                $donation->payment_type = 'rebill';
                if (!empty($data['card_token'])) {
                    $donation->recurring_id = $data['order_id'];
                    $donation->liqpay_order_id = $data['liqpay_order_id'];
                    $donation->card_token = $data['card_token'];
                }
            }
        }

        // Single payment
        else {
            switch ($data['status']) {
                case 'reversed':
                    $new_status = 'refunded';
                    break;
                default:
                    $new_status = 'submitted';
                    break;
            }
        }

        $donation->add_gateway_response($data);

        if($donation->status !== $new_status) {
            $donation->status = $new_status;
            Leyka_Donation_Management::send_all_emails($donation->id);          
        }
       
        status_header(200);
        wp_redirect($redirect_url);
        die(0);
    }

    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation $donation) {

        $init_recurrent_donation = Leyka_Donation::get_init_recurring_donation($donation);
        $cancelling_url = (get_option('permalink_structure') ?
                home_url("leyka/service/cancel_recurring/{$donation->id}") :
                home_url("?page=leyka/service/cancel_recurring/{$donation->id}"))
            .'/'.md5($donation->id.'_'.$init_recurrent_donation->id.'_leyka_cancel_recurring_subscription');

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
        $public = leyka_options()->opt('liqpay_public_key');
		$private = leyka_options()->opt('liqpay_private_key');
        $api = new Liqpay($public, $private);
        $response = $api->api('request', array(
            'action'     => 'unsubscribe',
            'version'    => 3,
            'public_key' => $public,
            'order_id'   => $donation->id,
        ));

        if ($response->status !== 'unsubscribed') {
            return new WP_Error('cp_cannot_cancel_recurring', sprintf(__('<strong>Error:</strong> we cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $donation->recurring_is_active = false;

        return true;

    }

    public function cancel_recurring_subscription_by_link(Leyka_Donation $donation) {

        if ($donation->type !== 'rebill') {
            die();
        }

        header('Content-type: text/html; charset=utf-8');

        $recurring_cancelling_result = $this->cancel_recurring_subscription($donation);

        if ($recurring_cancelling_result === true) {
            die(__('Recurring subscription cancelled successfully.', 'leyka'));
        } else if (is_wp_error($recurring_cancelling_result)) {
            die($recurring_cancelling_result->get_error_message());
        } else {
            die(sprintf(__('Error while trying to cancel the recurring subscription.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

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

        $this->_supported_currencies[] = 'rur';
        $this->_supported_currencies[] = 'uah';
        $this->_supported_currencies[] = 'usd';
        $this->_supported_currencies[] = 'eur';
        $this->_default_currency = 'uah';
        $this->currency_label = 'UAH';

    }

    public function has_recurring_support() { // Support recurring donations only if both single & recurring options set
        return true;
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

        $this->_label_backend = __('Liqpay card payment', 'leyka');
        $this->_label = __('Liqpay card payment', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',            
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_supported_currencies[] = 'uah';
        $this->_supported_currencies[] = 'usd';
        $this->_supported_currencies[] = 'eur';
        $this->_default_currency = 'uah';
        $this->currency_label = 'UAH';

    }
    public function has_recurring_support() { // Support recurring donations only if both single & recurring options set
        return true;
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
           LEYKA_PLUGIN_BASE_URL.'gateways/liqpay/icons/privat_logo_full.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_supported_currencies[] = 'uah';
        $this->_supported_currencies[] = 'usd';
        $this->_supported_currencies[] = 'eur';
        $this->_default_currency = 'uah';
        $this->currency_label = 'UAH';

    }

    public function has_recurring_support() { // Support recurring donations only if both single & recurring options set
        return true;
    }
    
}

function leyka_add_gateway_liqpay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Liqpay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_liqpay', 11);