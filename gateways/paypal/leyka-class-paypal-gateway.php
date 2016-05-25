<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Paypal_Gateway class
 */

class Leyka_Paypal_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'paypal';
        $this->_title = __('PayPal', 'leyka');
        $this->_docs_link = '';
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
                'title' => __('API username', 'leyka'),
                'description' => __('Please, enter your PayPal API username here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., testuser_api.ngo.ru', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_password' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('API password', 'leyka'),
                'description' => __('Please, enter your PayPal API password here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 123456_mypass_ngo.ru', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_signature' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('API signature', 'leyka'),
                'description' => __('Please, enter your PayPal API signature here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., AfCWxV27c7fd0v3HYyYRCpSSRl34ArUqPITpDcKswTlMg-j9m.AzS-17', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_test_mode' => array(
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

        if(empty($this->_payment_methods['paypal_card'])) {
            $this->_payment_methods['paypal_card'] = Leyka_Paypal_Card::get_instance();
        }
//        if(empty($this->_payment_methods['yandex_money'])) {
//            $this->_payment_methods['yandex_money'] = Leyka_Yandex_Money::get_instance();
//        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        switch($pm_id) {
            case 'paypal_card':
//            case 'paypal_pp':
                return leyka_options()->opt('paypal_test_mode') ?
                    'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
            default:
                return $current_url;
        }
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);

        $data = array(
            'USER' => leyka_options()->opt('paypal_api_username'),
            'PWD' => leyka_options()->opt('paypal_api_password'),
            'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
            'VERSION' => '120',
            'METHOD' => 'SetExpressCheckout',
            'LOCALECODE' => mb_strtoupper(get_bloginfo('language')), // RU, EN, etc
            'RETURNURL' => '???', // URL of the website to return after donor authorises himself in PayPal
            'CANCELURL' => '???', // ... and the URL to return after donor cancels the authorization in PayPal
            'PAYMENTREQUEST_0_NOTIFYURL' => home_url('leyka/service/'.$this->_id.'/response/'), // URL for success callback
            'PAYMENTREQUEST_0_INVNUM' => $donation_id,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_AMT' => $donation->amount,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $donation->currency == 'RUR' ? 'RUB' : $donation->currency, // RUB, USD, ...
            'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
            'PAYMENTREQUEST_0_DESC' => 'payment',
            'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
            'L_PAYMENTREQUEST_0_AMT0' => $donation->amount,
            'L_PAYMENTREQUEST_0_QTY0' => '1',
//            'LOGOIMG' => '',
            'NOSHIPPING' => '1',
        );

        return apply_filters('leyka_'.$this->_id.'_custom_submission_data', $data, $pm_id);
    }

    public function log_gateway_fields($donation_id) {
    }

    /** Wrapper method to answer checkOrder and paymentAviso service calls */
    private function _callback_answer($is_error = false, $callback_type = 'co', $message = '', $tech_message = '') {

        $is_error = !!$is_error;
        $tech_message = $tech_message ? $tech_message : $message;
        $callback_type = $callback_type == 'co' ? 'checkOrderResponse' : 'paymentAvisoResponse';

        if($is_error) {
            die('<?xml version="1.0" encoding="UTF-8"?>
<'.$callback_type.' performedDatetime="'.date(DATE_ATOM).'"
code="1000" invoiceId="'.$_POST['invoiceId'].'"
shopId="'.leyka_options()->opt('yandex_shop_id').'"
message="'.$message.'"
techMessage="'.$tech_message.'"/>');
        } else {
            die('<?xml version="1.0" encoding="UTF-8"?>
<'.$callback_type.' performedDatetime="'.date(DATE_ATOM).'"
code="0" invoiceId="'.$_POST['invoiceId'].'"
shopId="'.leyka_options()->opt('yandex_shop_id').'"/>');
        }
    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            case 'check_order': // Gateway test before the payment - to check if it's correct

                if($_POST['action'] != 'checkOrder') { // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'co', __('Wrong service operation', 'leyka'));
                }

                $_POST['orderNumber'] = (int)$_POST['orderNumber']; // Donation ID
                if( !$_POST['orderNumber'] ) {
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));
                }

                $donation = new Leyka_Donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount']) {
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));
                }

                $donation->add_gateway_response($_POST);

                $this->_callback_answer(); // OK for yandex.money payment
                break; // Not needed, just for my IDE could relax

            case 'payment_aviso':

                if($_POST['action'] != 'paymentAviso') { // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'pa', __('Wrong service operation', 'leyka'));
                }

                $_POST['orderNumber'] = (int)$_POST['orderNumber']; // Donation ID
                if( !$_POST['orderNumber'] ) {
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));
                }

                $donation = new Leyka_Donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount']) {
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));
                }

                if($donation->status != 'funded') {

                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';

                    if($donation->type == 'rebill' && !empty($_POST['invoiceId'])) {
                        $donation->recurring_id = (int)$_POST['invoiceId'];
                    }

                    Leyka_Donation_Management::send_all_emails($donation->id);
                }

				do_action('leyka_yandex_payment_aviso_success', $donation);

                $this->_callback_answer(0, 'pa'); // OK for yandex money payment
                break; // Not needed, just for my IDE could relax

            default:
        }
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) ) {
            return array();
        }

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

    public function do_recurring_donation(Leyka_Donation $init_recurring_donation) {

        if( !$init_recurring_donation->recurring_id ) {
            return false;
        }

        $new_recurring_donation_id = Leyka_Donation::add(array(
            'status' => 'submitted',
            'payment_type' => 'rebill',
            'purpose_text' => $init_recurring_donation->title,
            'campaign_id' => $init_recurring_donation->campaign_id,
            'payment_method_id' => $init_recurring_donation->pm_id,
            'gateway_id' => $init_recurring_donation->gateway_id,
            'donor_name' => $init_recurring_donation->donor_name,
            'donor_email' => $init_recurring_donation->donor_email,
            'amount' => $init_recurring_donation->amount,
            'currency' => $init_recurring_donation->currency,
            'init_recurring_donation' => $init_recurring_donation->id,
            'recurring_id' => $init_recurring_donation->recurring_id, // InvoiceId of the original donation in a subscription
//        '' => '',
        ));

        $certificate_path = leyka_options()->opt('yandex-yandex_card_certificate_path') ?
            WP_CONTENT_DIR.'/'.trim(leyka_options()->opt('yandex-yandex_card_certificate_path'), '/') : false;
        $certificate_private_key_path = leyka_options()->opt('yandex-yandex_card_private_key_path') ?
            WP_CONTENT_DIR.'/'.trim(leyka_options()->opt('yandex-yandex_card_private_key_path'), '/') : false;

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => leyka_options()->opt('yandex_test_mode') ?
                'https://penelope-demo.yamoney.ru/webservice/mws/api/repeatCardPayment' :
                'https://penelope.yamoney.ru/webservice/mws/api/repeatCardPayment',
            CURLOPT_PORT => leyka_options()->opt('yandex_test_mode') ? 8083 : 443,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'clientOrderId' => $new_recurring_donation_id,
                'invoiceId' => $init_recurring_donation->recurring_id,
                'orderNumber' => 'recurring-'.$init_recurring_donation->id.'-'.$new_recurring_donation_id,
                'amount' => $init_recurring_donation->amount,
            )),
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_SSLCERT => $certificate_path,
            CURLOPT_SSLKEY => $certificate_private_key_path,
            CURLOPT_SSLKEYPASSWD => leyka_options()->opt('yandex-yandex_card_private_key_password'),
        ));
        $answer = curl_exec($ch);

        $new_recurring_donation = new Leyka_Donation($new_recurring_donation_id);
        $res = false;

        if($answer) {

            $p = xml_parser_create();
            xml_parse_into_struct($p, $answer, $vals, $index);
            xml_parser_free($p);

            $new_recurring_donation->add_gateway_response($answer);

            if(isset($vals[0]['attributes']['STATUS']) && $vals[0]['attributes']['STATUS'] == 0) {

                $new_recurring_donation->status = 'funded';
                $res = $new_recurring_donation;
            }

        } else {
            $new_recurring_donation->add_gateway_response('Error '.curl_errno($ch).': '.curl_error($ch));
        }

        curl_close($ch);
        return $res;
    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);

            if($donation->type != 'rebill') {
                return;
            }?>

            <label><?php _e('Yandex.Money recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="yandex-recurring-id" name="yandex-recurring-id" placeholder="<?php _e('Enter Yandex.Money invoice ID', 'leyka');?>" value="<?php echo $donation->recurring_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->recurring_id;?></span>
                <?php }?>
            </div>

        <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <label for="yandex-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?></label>
            <div class="leyka-ddata-field">
                <input type="checkbox" id="yandex-recurring-is-active" name="yandex-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="yandex-recurring-id"><?php _e('Yandex.Money recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="yandex-recurring-id" name="yandex-recurring-id" placeholder="<?php _e('Enter Yandex.Money invoice ID', 'leyka');?>" value="">
            </div>
            <?php
        }
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'yandex_recurrent_id':
            case 'yandex_recurring_id':
            case 'yandex_invoice_id': return get_post_meta($donation->id, '_yandex_invoice_id', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'yandex_recurrent_id':
            case 'yandex_recurring_id':
            case 'yandex_invoice_id':
                return update_post_meta($donation->id, '_yandex_invoice_id', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['yandex-recurring-id']) && $donation->recurring_id != $_POST['yandex-recurring-id']) {
            $donation->recurring_id = $_POST['yandex-recurring-id'];
        }

        $_POST['yandex-recurring-is-active'] = !empty($_POST['yandex-recurring-is-active']);

        // Check if the value's different is inside the Leyka_Donation::__set():
        $donation->recurring_is_active = $_POST['yandex-recurring-is-active'];
    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['recurring_id']) ) {
            update_post_meta($donation_id, '_yandex_invoice_id', $donation_params['recurring_id']);
        }
    }
}


class Leyka_Paypal_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'paypal_card';
        $this->_gateway_id = 'paypal';

        $this->_label_backend = __('Payment with Banking Card', 'leyka');
        $this->_label = __('Banking Card', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
        ));

        /** @todo Right now we can't use leyka_options()->opt() here because Gateway options are not included in options_meta ATM. Refactor this. */
        $this->_custom_fields = get_option('leyka_'.$this->full_id.'_rebilling_available', true) ?
            array(
                'recurring' => '<label class="checkbox"><span><input type="checkbox" id="leyka_'.$this->full_id.'_recurring" name="leyka_recurring" value="1"></span> '.__('Monthly recurring donations', 'leyka').'</label>'
            ) :
            array();

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_rebilling_available' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 0,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'description' => __('Check if Yandex.Money allows you to create recurrent subscriptions to do regular automatic payments.', 'leyka'),
                'required' => 0,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_certificate_path' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Yandex.Money recurring payments certificate path', 'leyka'),
                'description' => __("Please, enter the path to your SSL certificate given to you by Yandex.Money. <strong>Warning!</strong> The path should include the certificate's filename intself. Also it should be relative to wp-content directory.", 'leyka'),
                'placeholder' => __('For ex., /uploads/leyka/your-cert-file.cer', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_private_key_path' => array(
                'type' => 'text',
                'default' => '',
                'title' => __("Yandex.Money recurring payments certificate's private key path", 'leyka'),
                'description' => __("Please, enter the path to your SSL certificate's private key given to you by Yandex.Money.<li><li>The path should include the certificate's filename intself.</li><li>The path should be relative to wp-content directory. </li></ul>", 'leyka'),
                'placeholder' => __('For ex., /uploads/leyka/your-private.key', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_private_key_password' => array(
                'type' => 'text',
                'default' => '',
                'title' => __("Yandex.Money recurring payments certificate's private key password", 'leyka'),
                'description' => __("Please, enter a password for your SSL certificate's private key, if you set this password during the generation of your sertificate request file.", 'leyka'),
                'placeholder' => __('Ex., fW!^12@3#&8A4', 'leyka'),
                'is_password' => 1,
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_description' => array(
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

/*class Leyka_Paypal_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_money';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Virtual cash Yandex.money', 'leyka');
        $this->_label = __('Yandex.money', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
//            LEYKA_PLUGIN_BASE_URL.'gateways/quittance/icons/sber_s.png',
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
                'default' => __("Yandex.Money is a simple and safe payment system to pay for goods and services through internet. You will have to fill a payment form, you will be redirected to the <a href='https://money.yandex.ru/'>Yandex.Money website</a> to confirm your payment. If you haven't got a Yandex.Money account, you can create it there.", 'leyka'),
                'title' => __('Yandex.Money description', 'leyka'),
                'description' => __('Please, enter Yandex.Money payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Yandex_Webmoney extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_wm';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Virtual cash Webmoney', 'leyka');
        $this->_label = __('Webmoney', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
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
                'default' => __('<a href="http://www.webmoney.ru/">WebMoney Transfer</a> is an international financial transactions system and an invironment for a business in Internet, founded in 1988. Up until now, WebMoney clients counts at more than 25 million people around the world. WebMoney system includes a services to account and exchange funds, attract new funding, solve quarrels and make a safe deals.', 'leyka'),
                'title' => __('WebMoney description', 'leyka'),
                'description' => __('Please, enter WebMoney payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Yandex_Sberbank_Online extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_sb';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Sberbank Online invoicing', 'leyka');
        $this->_label = __('Sberbank Online', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)
//        $this->_description = leyka_options()->opt_safe('yandex_wm_description');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options){
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('<a href="https://online.sberbank.ru/CSAFront/index.do">Sberbank Online</a> is an Internet banking service of Sberbank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
                'title' => __('Sberbank Online description', 'leyka'),
                'description' => __('Please, enter Sberbank Online payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Yandex_Alpha_Click extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_ab';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Alpha-Click invoicing', 'leyka');
        $this->_label = __('Alpha-Click', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)
//        $this->_description = leyka_options()->opt_safe('yandex_wm_description');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options){
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('<a href="https://alfabank.ru/retail/internet/">Alfa-Click</a> is an Internet banking service of Alfa bank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
                'title' => __('Alpha-Click description', 'leyka'),
                'description' => __('Please, enter Alpha-Click payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Yandex_Promvzyazbank extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_pb';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Promsvyazbank invoicing', 'leyka');
        $this->_label = __('Promsvyazbank', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)
//        $this->_description = leyka_options()->opt_safe('yandex_wm_description');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options){
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('<a href="http://www.psbank.ru/Personal/Everyday/Remote/">PSB-Retail</a> is an Internet banking service of Promsvyazbank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
                'title' => __('Promsvyazbank description', 'leyka'),
                'description' => __('Please, enter Promsvyazbank payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}*/

function leyka_add_gateway_paypal() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Paypal_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_paypal');