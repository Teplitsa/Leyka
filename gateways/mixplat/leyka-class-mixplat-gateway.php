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

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

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

        if(empty($this->_payment_methods['mobile'])) {
            $this->_payment_methods['mobile'] = Leyka_Mixplat_Mobile::get_instance();
        }
        if(empty($this->_payment_methods['sms'])) {
            $this->_payment_methods['sms'] = Leyka_Mixplat_Text::get_instance();
        }
    }

    protected function _get_currency_id($leyka_currency_id){

        $currencies = array('rur' => 'RUB', 'usd' => 'USD', 'eur' => 'EUR');

        return isset($currencies[$leyka_currency_id]) ? $currencies[$leyka_currency_id] : 'RUB';
    }

    public function localize_js_strings($js_data){
        return array_merge($js_data, array(
            'phone_invalid' => __('Please, enter a phone number in a 7xxxxxxxxxx format.', 'leyka'),
        ));
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Mixplat_Mobile::get_instance()->active) {

            wp_enqueue_script(
                'leyka-mixplat',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Mixplat_Gateway::get_instance()->id.'/js/leyka.mixplat.js',
                array('jquery', 'leyka-public'),
                LEYKA_VERSION,
                true
            );
        }

        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        if(empty($form_data['leyka_donor_phone'])) {

            $error = new WP_Error('leyka_mixplat_phone_is_empty', __('Valid phone number is required.', 'leyka'));
            leyka()->add_payment_form_error($error);

            return;
        }

        $phone = '7'.substr(str_replace(array('+', ' ', '-', '.'), '', trim($form_data['leyka_donor_phone'])), -10);

        $is_test = leyka_options()->opt('mixplat_test_mode') ? 1 : 0;

        $donation = new Leyka_Donation($donation_id);
        $amount = (int)round((float)$donation->amount * 100);
        $donation->mixplat_phone = $phone;
        $currency = $this->_get_currency_id($donation->currency);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://api.mixplat.com/mc/create_payment',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                'service_id' => leyka_options()->opt('mixplat_service_id'),
                'phone' => $phone,
                'amount' => $amount,
                'currency' => $currency,
                'external_id' => $donation_id,
                'test' => $is_test,
                'signature' => md5(
                    leyka_options()->opt('mixplat_service_id').$phone.$amount.$currency.$donation_id.$is_test.
                    leyka_options()->opt('mixplat_secret_key')
                ),
            )),
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
            } catch (Exception $ex) {
                error_log($ex);
            }
        }

        $is_success = false;
        if($json) {

            $donation->add_gateway_response($json);

            if($json['result'] == 'ok') {
                $is_success = true;
            }
        }

        if($is_success) {
            wp_redirect(leyka_get_success_page_url());
        } else {

            wp_mail(
                get_option('admin_email'),
                __('MIXPLAT - payment callback error occured', 'leyka'),
                sprintf(__("This message has been sent because a create_payment call to MIXPLAT payment system returned some error. The details of the call are below. Payment error code / text: %s / %s", 'leyka'), $json['result'], $json['message'])."\n\r\n\r"
            );

            wp_redirect(leyka_get_failure_page_url());
        }

        exit(0);
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data_vars; // It's not our PM
        }

		return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        $json_string = file_get_contents('php://input');

        $response = array();
        try {

            $response = json_decode($json_string, true);
            $response = $response ? $response : $_POST;

        } catch(Exception $ex) {
            error_log($ex);
        }

        $message = '';
        $is_error = false;

        if(empty($response['request'])) {

            $message = __("This message was sent because a call to your MIXPLAT callback was made with an empty request parameter value. The details of the call are below.", 'leyka')."\n\r\n\r";
            $is_error = true;

        } else if( !in_array($response['request'], array('check', 'status')) ) {

            $message = sprintf(__("This message was sent because a call to your MIXPLAT callback was made with an unknown request parameter value. The details of the call are below. Request value: %s", 'leyka'), $response['request'])."\n\r\n\r";
            $is_error = true;
        }

        if( !$is_error ) {

            if($response['request'] == 'check') { // Check request
                $nessessary_params = array(
                    'id', 'service_id', 'phone', 'date_created', 'amount', 'currency', 'text', 'signature',
                );
            } else { // Status request
                $nessessary_params = array(
                    'id', 'external_id', 'service_id', 'status', 'status_extended', 'phone', 'amount', 'amount_merchant',
                    'currency', 'test', 'signature',
                );
            }

            foreach($nessessary_params as $param_name) {
                if( !array_key_exists($param_name, $response) ) {

                    $message = sprintf(__("This message has been sent because a call to your MIXPLAT callback was made without required parameters given. The details of the call are below. The callback type: %s. The parameter missing: %s", 'leyka'), $response['request'], $param_name)."\n\r\n\r";
                    $is_error = true;
                    break;
                }
            }
        }

        if( !$is_error ) {

            if($response['request'] == 'check') { // Check request
                $params_signature = md5(
                    $response['id'].$response['service_id'].$response['phone'].$response['amount'].
                    leyka_options()->opt('mixplat_secret_key')
                );
            } else { // Status request
                $params_signature = md5(
                    $response['id'].$response['external_id'].$response['service_id'].$response['status'].
                    $response['status_extended'].$response['phone'].$response['amount'].$response['amount_merchant'].
                    $response['currency'].$response['test'].leyka_options()->opt('mixplat_secret_key')
                );
            }

            if($params_signature != $response['signature']) {

                $message = sprintf(__("This message has been sent because a call to your MIXPLAT callback was made with invalid MIXPLAT signature. The details of the call are below. The callback type: %s. Signatures sent / calculated: %s / %s", 'leyka'), $response['request'], $response['signature'], $params_signature)."\n\r\n\r";
                $is_error = true;
            }
        }

        if($is_error) {

            $message .= "CALLBACK TYPE: ".print_r($response['request'], true)."\n\r\n\r";
            $message .= "THEIR POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";
            $message .= "THEIR JSON:\n\r".print_r($json_string, true)."\n\r\n\r";
            $message .= "THEIR JSON DECODED:\n\r".print_r(json_decode($json_string), true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('MIXPLAT - payment callback error occured', 'leyka'), $message);
            status_header(200);

            die('Payment callback error');
        }

        if($response['request'] == 'status') { // Status request

            // SMS payment:
            if(empty($response['external_id']) && !empty($response['status']) && $response['status'] == 'success') {

                $response['currency'] = empty($response['currency']) ? 'rur' : trim($response['currency']);

                $donation_id = Leyka_Donation::add(array(
                    'gateway_id' => $this->_id,
                    'payment_method_id' => 'mobile',
                    'campaign_id' => leyka_options()->opt('mixplat-mobile_default_campaign_id'),
                    'status' => 'funded',
                    'payment_type' => 'single',
                    'amount' => $response['amount']/100.0,
                    'currency' => empty($response['currency']) ?
                        'rur' : ($response['currency'] == 'RUB' ? 'rur' : strtolower($response['currency'])),
                    'mixplat_phone' => $response['phone'],
                ));

//                $params = array(
//                    'response-currency-empty' => (int)empty($response['currency']),
//                    'response-currency-original' => $response['currency'],
//                    'currency-alt' => $response['currency'] == 'RUB' ? 'rur' : strtolower($response['currency']),
//                    'currency-total' => empty($response['currency']) ?
//                        'rur' :
//                        ($response['currency'] == 'RUB' ? 'rur' : strtolower($response['currency']))
//                );

                $donation = new Leyka_Donation($donation_id);
                $donation->add_gateway_response($response);

                Leyka_Donation_Management::send_all_emails($donation->id);

            } else if( !empty($response['status']) && $response['status'] == 'success' ) { // Mobile payment via website

                $donation = new Leyka_Donation((int)stripslashes($response['external_id']));
                if($donation && $donation->status != 'funded') {

                    $donation->status = 'funded';
                    Leyka_Donation_Management::send_all_emails($donation->id);
                }
            }

        }

        status_header(200);
		die(json_encode(array('result' => 'ok')));
    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) ) {
            return array();
        }

        return array(
            __('MIXPLAT payment ID:', 'leyka') => $this->_get_value_if_any($vars, 'id'),
            __('Operation result:', 'leyka') => $this->_get_value_if_any($vars, 'result'),
			__('Operator:', 'leyka') => $this->_get_value_if_any($vars, 'operator'),
			__('Error message:', 'leyka') => $this->_get_value_if_any($vars, 'message'),
        );		
    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);?>

            <label><?php _e('Phone number', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

            <?php if($donation->type == 'correction') {?>
                <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php _e('Enter a phone number', 'leyka');?>" value="<?php echo $donation->mixplat_phone;?>">
            <?php } else {?>
                <span class="fake-input"><?php echo $donation->mixplat_phone;?></span>
            <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="mixplat-phone"><?php _e('Phone number', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php _e('Enter a phone number', 'leyka');?>" value="">
            </div>
        <?php }
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'mixplat_phone': return get_post_meta($donation->id, '_leyka_mixplat_phone', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'mixplat_phone':
                return update_post_meta($donation->id, '_leyka_mixplat_phone', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['mixplat-phone']) && $donation->mixplat_phone != $_POST['mixplat-phone']) {
            $donation->mixplat_phone = $_POST['mixplat-phone'];
        }
    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['mixplat_phone']) ) {
            update_post_meta($donation_id, '_leyka_mixplat_phone', $donation_params['mixplat_phone']);
        }
    }
} // Gateway class end


class Leyka_Mixplat_Mobile extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'mobile';
        $this->_gateway_id = 'mixplat';

        $this->_label_backend = __('Mobile payment', 'leyka');
        $this->_label = __('Mobile payment', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/mixplat/icons/sms.png',
        ));

        $this->_custom_fields = array(
            'mixplat_phone' => apply_filters('leyka_donor_phone_field_html', '<label class="input req"><input id="leyka_'.$this->full_id.'_phone" class="required phone-num mixplat-phone" type="text" value="" name="leyka_donor_phone" placeholder="'.__('Your phone number in the 7xxxxxxxxxx format', 'leyka').'" maxlength="11">
</label>
<p class="field-comment">'.__('We will use this phone number to make a mobile payment', 'leyka').'</p>
<p class="leyka_donor_phone-error field-error"></p>', $this),
        );

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_default_campaign_id' => array(
                'type' => 'select',
                'default' => leyka_get_campaigns_select_default(),
                'title' => __('Campaign for SMS payments', 'leyka'),
                'description' => __('Select a campaign to which SMS payments will be related by default.', 'leyka'),
                'required' => 0,
                'placeholder' => '', // For text fields
                'length' => '', // For text fields
                'list_entries' => 'leyka_get_campaigns_list',
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('MIXPLAT allows a simple and safe way to pay for goods and services with your mobile phone by sending SMS.', 'leyka'),
                'title' => __('Mobile payment description', 'leyka'),
                'description' => __('Please, enter MIXPLAT gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_details' => array(
                'type' => 'html',
                'default' => '',
                'title' => __('Ways to donate via mobile payments', 'leyka'),
                'description' => __('Please, set a text to describe a donation via mobile payments.', 'leyka'),
                'required' => 1,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Mixplat_Text extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'sms';
        $this->_gateway_id = 'mixplat';

        $this->_label_backend = __('Payments via SMS', 'leyka');
        $this->_label = __('Payments via SMS', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_support_global_fields = false;

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/mixplat/icons/sms.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_dynamic_attributes() {
        $this->_custom_fields = array(
            'sms_details' => apply_filters('leyka_the_content', leyka_options()->opt_safe($this->full_id.'_details')),
        );
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => '',
                'title' => __('Comment to the message of donations via SMS', 'leyka'),
                'description' => __('Please, set a text of payments via SMS description.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_details' => array(
                'type' => 'html',
                'default' => __('You can make a donation by sending an SMS on the number XXXX.', 'leyka'),
                'title' => __('Ways to donate via SMS', 'leyka'),
                'description' => __('Please, set a text to describe a donation via SMS.', 'leyka'),
                'required' => 1,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_mixplat() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Mixplat_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_mixplat');