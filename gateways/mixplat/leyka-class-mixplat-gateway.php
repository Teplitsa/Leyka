<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Mixplat_Gateway class
 */

class Leyka_Mixplat_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'mixplat';
        $this->_title = __('MIXPLAT', 'leyka');
        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-mixplat/';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 60;
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'mixplat_service_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('MIXPLAT project ID', 'leyka'),
                'description' => __('Please, enter your MIXPLAT project ID here. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 100359', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'mixplat_secret_key' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('MIXPLAT project secret key', 'leyka'),
                'description' => __('Please, enter your MIXPLAT project secret key here. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., c23a4398db8ef7b3ae1f4b07aeeb7c54f8e3c7c9', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'mixplat_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check to run MIXPLAT in testing mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['sms'])) {
            $this->_payment_methods['sms'] = Leyka_Mixplat::get_instance();
        }
    }

    private function get_valid_phone($phone) {
        $phone = str_replace('+', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace('.', '', $phone);
        $phone = trim($phone);
        $phone = substr($phone, -10);
        $phone = '7' . $phone;
        return $phone;
    }
    
    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
        
        $service_id = leyka_options()->opt('mixplat_service_id');
        $secret_key = leyka_options()->opt('mixplat_secret_key');
        $is_test = leyka_options()->opt('mixplat_test_mode') ? 1 : 0;
        
        $donation = new Leyka_Donation($donation_id);
        $amount = (int)round((float)$donation->amount * 100);
        $phone = !empty($form_data['mixplat-phone']) ? $this->get_valid_phone($form_data['mixplat-phone']) : '';
        $donation->mixplat_phone = $phone;
        $currency = 'RUB';
        
        $str_to_hash = '' . $service_id . $phone . $amount . $currency . $donation_id . $is_test . $secret_key;
        $signature = md5($str_to_hash);
        
        $form_data_vars =  array(
            'service_id' => $service_id,
            'phone' => $phone,
            'amount' => $amount,
            'currency' => $currency,
            'external_id' => $donation_id,
            'test' => $is_test,
            'signature' => $signature,
//             '' => '',
        );
        
        $json_string = json_encode($form_data_vars);
        
        $answer = false;
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://api.mixplat.com/mc/create_payment',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_string,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
        ));
        $answer = curl_exec($ch);
        curl_close($ch);
        
        $json = null;
        if($answer) {
            try {
                $json = json_decode($answer, true);
            }
            catch (Exception $ex) {
                error_log($ex);
            }
        }
        
        $is_success = false;
        if($json) {
            $donation->add_gateway_response($json);
            
            $result = $json['result'];
            $payment_id = $json['id'];
            if($result == 'ok') {
                $is_success = true;
            }
        }
        
        if($is_success) {
            wp_redirect(leyka_get_success_page_url());
        }
        else {
            wp_redirect(leyka_get_failure_page_url());
        }
        
        exit(0);
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) )
			return $form_data_vars; //it's not our PM

		return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {
        
        $json_string = file_get_contents('php://input');
        
        $json = [];
        try {
            $json = json_decode($json_string, true);
        }
        catch(Exception $ex) {
            error_log($ex);
        }
        
        $message = '';
        $is_error = false;
        if(empty($json['id']) || empty($json['external_id']) || empty($json['service_id'])
            || empty($json['status']) || empty($json['status_extended']) || empty($json['phone']) || empty($json['amount']) || empty($json['amount_merchant'])
            || empty($json['currency']) || empty($json['test']) || empty($json['signature'])
        ) {
            $message = __("This message has been sent because a call to your MIXPLAT callback was made without required parameters given. The details of the call are below.", 'leyka')."\n\r\n\r";
            $is_error = true;
        }
        else {
            
            $secret_key = leyka_options()->opt('mixplat_secret_key');
            $str_to_hash = $json['id'] . $json['external_id'] . $json['service_id'] . $json['status'] . $json['status_extended'] . $json['phone'] .
                            $json['amount'] . $json['amount_merchant'] . $json['currency'] . $json['test'] . $secret_key;
            $params_signature = md5($str_to_hash);
            
            if($params_signature != $json['signature']) {
                $message = __("This message has been sent because a call to your MIXPLAT callback was made with invalid MIXPLAT signature. The details of the call are below.", 'leyka')."\n\r\n\r";
                $is_error = true;
            }
        }
        
        if($is_error) {
            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
            $message .= "THEIR_JSON:\n\r".print_r($json_string,true)."\n\r\n\r";
            
            wp_mail(get_option('admin_email'), __('MIXPLAT - external_id missing!', 'leyka'), $message);
            status_header(200);
            
            die('Invalid status data!');
        }

        $donation = new Leyka_Donation((int)stripslashes($json['external_id']));
        if( $donation ) {
            if($donation->status != 'funded') {
                $donation->status = 'funded';
                Leyka_Donation_Management::send_all_emails($donation->id);
            }
        }

        $ok_result = array('result' => 'ok');
        status_header(200);
		die(json_encode($ok_result));
    }

    protected function _get_value_if_any($arr, $key, $val = false) {

        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) )
            return array();

        return array(
            __('MIXPLAT payment ID:', 'leyka') => $this->_get_value_if_any($vars, 'id'),
            __('MIXPLAT operation result:', 'leyka') => $this->_get_value_if_any($vars, 'result'),
			__("MIXPLAT operator:", 'leyka') => $this->_get_value_if_any($vars, 'operator'),
			__('MIXPLAT error message:', 'leyka') => $this->_get_value_if_any($vars, 'message'),
        );		
    }
    
    public function display_donation_specific_data_fields($donation = false) {
    
        if($donation) { // Edit donation page displayed
    
                $donation = leyka_get_validated_donation($donation);?>
    
                <label><?php _e('MIXPLAT phone', 'leyka');?>:</label>
    			<div class="leyka-ddata-field">
    
                <?php if($donation->type == 'correction') {?>
                    <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php _e('Enter phone', 'leyka');?>" value="<?php echo $donation->mixplat_phone;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->mixplat_phone;?></span>
                <?php }?>
                </div>
    
            <?php } else { // New donation page displayed ?>
    
                <label for="mixplat-phone"><?php _e('MIXPLAT phone', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php _e('Enter phone', 'leyka');?>" value="" />
                </div>
            <?php
            }
    }
    
    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'mixplat_phone': return get_post_meta($donation->id, '_mixplat_phone', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'mixplat_phone':
                return update_post_meta($donation->id, '_mixplat_phone', $value);
            default: return false;
        }
    }
        
    public function save_donation_specific_data(Leyka_Donation $donation) {
    
        if(
                isset($_POST['mixplat-phone']) &&
                $donation->mixplat_phone != $_POST['mixplat-phone']
        ) {
            $donation->mixplat_phone = $_POST['mixplat-phone'];
        }
    }
    
    public function add_donation_specific_data($donation_id, array $donation_params) {
    
        if( !empty($donation_params['mixplat_phone']) ) {
            update_post_meta($donation_id, '_mixplat_phone', $donation_params['mixplat_phone']);
        }
    }
} // Gateway class end


class Leyka_Mixplat extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'sms';
        $this->_gateway_id = 'mixplat';

        $this->_label_backend = __('Payment with SMS', 'leyka');
        $this->_label = __('SMS', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/mixplat/icons/sms.png',
        ));
        
        $js_phone_validate ='
<script>
    jQuery(function($){
        var $mixplat_phone_validation_error = $("#leyka_mixplat_phone_valid-error");
        var $mixplat_phone_field = $("#leyka_mixplat-sms_phone");
        
        $mixplat_phone_field.change(function(){
            if(!is_mixplat_phone_valid($(this).val())) {
                show_mixplat_phone_error();
            }
            else {
                hide_mixplat_phone_error();
            }
        });
                        
        $mixplat_phone_field.blur(function(){
            if(!is_mixplat_phone_valid($(this).val())) {
                show_mixplat_phone_error();
                return false;
            }
            else {
                hide_mixplat_phone_error();
            }
        });
                        
        function is_mixplat_phone_valid(val) {
            var is_valid = false;
            if(val) {
                val = val.replace(/[+. -]/, "");
                val = val.replace(/\s/, "");
                val = val.replace(/^7/, "");
                if(val.match(/^\d{10}$/)) {
                    is_valid = true;
                }
            }
            return is_valid;
        }
                        
        function show_mixplat_phone_error() {
            $mixplat_phone_validation_error.show();
            $mixplat_phone_validation_error.css("color", "red");
            $mixplat_phone_field.focus();
        }
                        
        function hide_mixplat_phone_error() {
            $mixplat_phone_validation_error.hide();
        }
    });
</script>
';
        
        $this->_custom_fields = array(
            'mixplat_phone' => '<div class="rdc-textfield"><input id="leyka_'.$this->full_id.'_phone" class="required rdc-textfield__input" type="text" value="" name="mixplat-phone">
<label class="leyka-screen-reader-text rdc-textfield__label" for="leyka_'.$this->full_id.'_phone">'.__('Your phone number', 'leyka').'</label>
<span id="leyka_'.$this->full_id.'_phone-error" class="mixplat-phone-error field-error rdc-textfield__error"></span>
<span id="leyka_mixplat_phone_valid-error" style="display:none;top:75px;" class="field-error rdc-textfield__error">'.__('Phone number should be 7xxxxxxxxxx', 'leyka').'</span>
</div>' . $js_phone_validate
        );
        
        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('MIXPLAT allows a simple and safe way to pay for goods and services with your mobile phone by sending SMS.', 'leyka'),
                'title' => __('MIXPLAT payment description', 'leyka'),
                'description' => __('Please, enter MIXPLAT gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_mixplat() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Mixplat_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_mixplat');