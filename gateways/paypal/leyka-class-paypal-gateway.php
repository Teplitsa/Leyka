<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Paypal_Gateway class
 */

class Leyka_Paypal_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'paypal';
        $this->_title = __('PayPal', 'leyka');
        $this->_docs_link = ''; /** @todo Add a link to the docs after it's ready */
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 10;

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = array(
            'paypal_api_username' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('PayPal API username', 'leyka'),
                'description' => '', //__('Please, enter your Yandex.Money shop ID here. It can be found in your Yandex contract.', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., your.name@yourmail.com', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_password' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('PayPal API password', 'leyka'),
                'description' => '', //__("Please, enter a secret word that you filled in Yandex.money' technical questionaire. If it's set, Leyka will perform MD5 hash checks of each incoming donation data integrity.", 'leyka'),
                'placeholder' => __('Ex., 1^2@3#&84nDsOmE5h1T', 'leyka'),
                'is_password' => true,
                'required' => false,
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_signature' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('PayPal API signature', 'leyka'),
                'description' => '', //__('Please, enter your Yandex.Money shop showcase ID (SCID) here. It can be found in your Yandex contract.', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., 1^2@3#&84nDsOmE5h1T', 'leyka'),
                'is_password' => true,
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['paypal_all'])) {
            $this->_payment_methods['paypal_all'] = Leyka_Paypal_All::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        leyka()->auto_redirect = false;

        $donation = new Leyka_Donation($donation_id);

        $campaign_post = get_post($donation->campaign_id);

        $data = apply_filters('leyka_paypal_submission_data', array(
            'USER' => leyka_options()->opt('paypal_api_username'),
            'PWD' => leyka_options()->opt('paypal_api_password'),
            'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
            'VERSION' => 120,
            'METHOD' => 'SetExpressCheckout',
            'EMAIL' => $donation->donor_email,
            'SOLUTIONTYPE' => 'Sole',
            'LANDINGPAGE' => 'Billing',
            'CHANNELTYPE' => 'Merchant',
            'USERSELECTEDFUNDINGSOURCE' => 'CreditCard', // WARNING: it may be a default PM! (CreditCard, QIWI, ELV)
            'BRANDNAME' => leyka_options()->opt('org_full_name'),
            'LOCALECODE' => 'RU',
            'RETURNURL' => home_url('leyka/service/'.$this->_id.'/process_payment/'),
            'CANCELURL' => leyka_get_failure_page_url(),
            'PAYMENTREQUEST_0_NOTIFYURL' => home_url('leyka/service/'.$this->_id.'/ipn/'),
            'PAYMENTREQUEST_0_INVNUM' => $donation_id,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_AMT' => $donation->amount,
            'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
            'PAYMENTREQUEST_0_DESC' => $donation->payment_title." (№ $donation_id)",
            'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
            'L_PAYMENTREQUEST_0_ITEMURL0' => get_permalink($campaign_post),
            'L_PAYMENTREQUEST_0_DESC0' => is_a($campaign_post, 'WP_Post') ? $campaign_post->post_excerpt : '',
            'L_PAYMENTREQUEST_0_AMT0' => $donation->amount,
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
            'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
//            'L_BILLINGAGREEMENTDESCRIPTION0' => 'Some blah-blah',
            'L_PAYMENTTYPE0' => 'InstantOnly',
//            'LOGOIMG' => 'https://sandbox.paypal.com/logo.png', // Logo in the cart page header, HTTPS only
            'NOSHIPPING' => 1,
//            '' => ,
        ), $pm_id, $donation_id, $form_data);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->submission_redirect_url('', $pm_id),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
        ));

        parse_str(curl_exec($ch), $result);
        curl_close($ch);

        if(isset($result['CORRELATIONID'])) {
            $donation->paypal_correlation_id = $result['CORRELATIONID'];
        }

        if(empty($result['ACK']) || $result['ACK'] != 'Success') {

            $donation->status = 'failed';

            wp_mail(
                get_option('admin_email'),
                __('PayPal - payment error occured', 'leyka'),
                sprintf(__("This message has been sent because a SetExpressCheckout request to PayPal payment system returned some error. The details of the request are below.\n\nPayment error code: %s\nPayment error message: %s\nPayment error description: %s", 'leyka'), $result['L_ERRORCODE0'], $result['L_SHORTMESSAGE0'], $result['L_LONGMESSAGE0'])."\n\r\n\r"
            );

            wp_redirect(leyka_get_failure_page_url());

        } elseif(empty($result['TOKEN'])) {

            $donation->status = 'failed';

            wp_mail(
                get_option('admin_email'),
                __('PayPal - payment error occured', 'leyka'),
                sprintf(__("This message has been sent because a SetExpressCheckout request to PayPal payment system returned without TOKEN param.\n\nFull PayPal response: %s", 'leyka'), '<pre>'.print_r($result, 1).'</pre>')."\n\r\n\r"
            );

            wp_redirect(leyka_get_failure_page_url());

        } else {

            $donation->paypal_token = $result['TOKEN'];

            $paypal_login_url = leyka_options()->opt('paypal_test_mode') ?
                'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$result['TOKEN'] :
                'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$result['TOKEN'];

            wp_redirect($paypal_login_url);

        }

        exit(0);

    }

    public function submission_redirect_url($current_url, $pm_id) {

        if(empty($current_url)) {
            $current_url = leyka_get_current_url();
        }

        switch($pm_id) {
            case 'paypal_all':
                return leyka_options()->opt('paypal_test_mode') ?
                    'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
            default:
                return $current_url;
        }

    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

//        $donation = new Leyka_Donation($donation_id);

//        $campaign_post = get_post($donation->campaign_id);
//
//        $data = array(
//            'USER' => leyka_options()->opt('paypal_api_username'),
//            'PWD' => leyka_options()->opt('paypal_api_password'),
//            'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
//            'VERSION' => 120,
//            'METHOD' => 'SetExpressCheckout',
//            'EMAIL' => $donation->donor_email,
//            'SOLUTIONTYPE' => 'Sole',
//            'LANDINGPAGE' => 'Billing',
//            'CHANNELTYPE' => 'Merchant',
//            'USERSELECTEDFUNDINGSOURCE' => 'CreditCard', // WARNING: it may be a default PM! (CreditCard, QIWI, ELV)
//            'BRANDNAME' => leyka_options()->opt('org_full_name'),
//            'LOCALECODE' => 'RU',
//            'RETURNURL' => home_url('leyka/service/'.$this->_id.'/process_payment/'),
//            'CANCELURL' => leyka_get_failure_page_url(),
//            'PAYMENTREQUEST_0_NOTIFYURL' => home_url('leyka/service/'.$this->_id.'/ipn/'),
//            'PAYMENTREQUEST_0_INVNUM' => $donation_id,
//            'PAYMENTREQUEST_0_CUSTOM' => 'some custom info', /** @todo Test if needed. */
//            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
//            'PAYMENTREQUEST_0_AMT' => $donation->amount,
//            'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
//            'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
//            'PAYMENTREQUEST_0_DESC' => $donation->payment_title." (№ $donation_id)",
//            'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
//            'L_PAYMENTREQUEST_0_ITEMURL0' => get_permalink($campaign_post),
//            'L_PAYMENTREQUEST_0_DESC0' => is_a($campaign_post, 'WP_Post') ? $campaign_post->post_excerpt : '',
//            'L_PAYMENTREQUEST_0_AMT0' => $donation->amount.' '.$donation->currency_label,
//            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
//            'L_PAYMENTREQUEST_0_QTY0' => 1, /** @todo The quantity of a digital goods. Test if needed. */
//            'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
//            'L_BILLINGAGREEMENTDESCRIPTION0' => 'Some blah-blah...',
//            'L_PAYMENTTYPE0' => 'InstantOnly',
////            'LOGOIMG' => 'https://sandbox.paypal.com/logo.png', // Logo in the cart page header, HTTPS only
//            'NOSHIPPING' => 1,
////            '' => ,
//        );

        return array();

    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {
        /** @todo IPN processing here */
    }

    /** Override the auto-submit setting to send manual requests to PayPal. */
    public function submission_auto_redirect($is_auto_redirect, $pm_id, $donation_id) {
        return false;
    }

    public function gateway_redirect_page_content($pm_id, $donation_id) {



    }

    public function enqueue_gateway_scripts() {

        if( !Leyka_Paypal_All::get_instance()->active ) {
            return;
        }

        // For donation redirect page:
        if(get_query_var('name') == 'leyka-process-donation') {
            wp_enqueue_script(
                'leyka-paypal-front',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Paypal_Gateway::get_instance()->id.'/js/leyka.paypal.js',
                array('jquery',),
                LEYKA_VERSION,
                true
            );
        }

//        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));

    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) ) {
            return array();
        }

        return $response_vars; //array(
//            __('Last response operation:', 'leyka') => $action_label,
//            __('Gateway invoice ID:', 'leyka') => $response_vars['invoiceId'],
//            __('Full donation amount:', 'leyka') =>
//                (float)$response_vars['orderSumAmount'].' '.$donation->currency_label,
//            __('Donation amount after gateway commission:', 'leyka') =>
//                (float)$response_vars['shopSumAmount'].' '.$donation->currency_label,
//            __("Gateway's donor ID:", 'leyka') => $response_vars['customerNumber'],
//            __('Response date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($response_vars['requestDatetime'])),
        //);
    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);?>

            <label><?php _e('PayPal token', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="paypal-token" name="paypal-token" placeholder="<?php _e('Enter PayPal token', 'leyka');?>" value="<?php echo $donation->paypal_token;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->paypal_token;?></span>
                <?php }?>
            </div>

            <label><?php _e('PayPal correlation ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                    <input type="text" id="paypal-correlation-id" name="paypal-token" placeholder="<?php _e('Enter PayPal correlation ID', 'leyka');?>" value="<?php echo $donation->paypal_correlation_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->paypal_correlation_id;?></span>
                <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="paypal-token"><?php _e('PayPal token', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="paypal-token" name="paypal-token" placeholder="<?php _e('Enter PayPal token', 'leyka');?>" value="">
            </div>

            <label for="paypal-correlation-id"><?php _e('PayPal correlation ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="paypal-correlation-id" name="paypal-correlation-id" placeholder="<?php _e('Enter PayPal correlation ID', 'leyka');?>" value="">
            </div>
        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'paypal_token':
            case 'pp_token': return get_post_meta($donation->id, '_paypal_token', true);
            case 'paypal_correlation_id':
            case 'pp_correlation_id': return get_post_meta($donation->id, '_paypal_correlation_id', true);
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'paypal_token':
            case 'pp_token': update_post_meta($donation->id, '_paypal_token', $value); break;
            case 'paypal_correlation_id':
            case 'pp_correlation_id': update_post_meta($donation->id, '_paypal_correlation_id', $value); break;
            default:
        }

    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['paypal-token']) && $donation->paypal_token != $_POST['paypal-token']) {
            $donation->paypal_token = $_POST['paypal-token'];
        }

        if(isset($_POST['paypal-correlation-id']) && $donation->paypal_token != $_POST['paypal-correlation-id']) {
            $donation->paypal_correlation_id = $_POST['paypal-correlation-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['paypal_correlation_id']) ) {
            update_post_meta($donation_id, '_paypal_correlation_id', $donation_params['paypal_correlation_id']);
        }

        if( !empty($donation_params['paypal_correlation_id']) ) {
            update_post_meta($donation_id, '_paypal_correlation_id', $donation_params['paypal_correlation_id']);
        }

    }

}


class Leyka_Paypal_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'paypal_all';
        $this->_gateway_id = 'paypal';

        $this->_label_backend = __('PayPal - any payment method available', 'leyka');
        $this->_label = __('PayPal', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/paypal/icons/paypal-frontend.png',
        ));

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
                'default' => __('PayPal allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://www.paypal.com/">PayPal website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Yandex Smart Payment description', 'leyka'),
                'description' => __('Please, enter PayPal payment service description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );

    }

}

function leyka_add_gateway_paypal() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Paypal_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_paypal');