<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Yandex_Gateway class
 */

class Leyka_Yandex_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'yandex';
        $this->_title = __('Yandex.Money', 'leyka');
        $this->_docs_link = '//leyka.te-st.ru/docs/yandex-dengi/';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 10;

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

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
            'yandex_shop_article_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('Yandex ShopArticleId', 'leyka'),
                'description' => __('Please, enter your Yandex.Money shop article ID here, if it exists. It can be found in your Yandex contract, also you can ask your Yandex.money manager for it.', 'leyka'),
                'required' => 0,
                'placeholder' => __('Ex., 12345', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_secret_word' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Yandex.Money shop secret word', 'leyka'),
                'description' => __("Please, enter a secret word that you filled in Yandex.money' technical questionaire. If it's set, Leyka will perform MD5 hash checks of each incoming donation data integrity.", 'leyka'),
                'placeholder' => __('Ex., 1^2@3#&84nDsOmE5h1T', 'leyka'),
                'is_password' => 1,
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            'yandex_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
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

        if(empty($this->_payment_methods['yandex_all'])) {
            $this->_payment_methods['yandex_all'] = Leyka_Yandex_All::get_instance();
        }
        if(empty($this->_payment_methods['yandex_card'])) {
            $this->_payment_methods['yandex_card'] = Leyka_Yandex_Card::get_instance();
        }
        if(empty($this->_payment_methods['yandex_money'])) {
            $this->_payment_methods['yandex_money'] = Leyka_Yandex_Money::get_instance();
        }
        if(empty($this->_payment_methods['yandex_wm'])) {
            $this->_payment_methods['yandex_wm'] = Leyka_Yandex_Webmoney::get_instance();
        }
        if(empty($this->_payment_methods['yandex_sb'])) {
            $this->_payment_methods['yandex_sb'] = Leyka_Yandex_Sberbank_Online::get_instance();
        }
        if(empty($this->_payment_methods['yandex_ab'])) {
            $this->_payment_methods['yandex_ab'] = Leyka_Yandex_Alpha_Click::get_instance();
        }
        if(empty($this->_payment_methods['yandex_pb'])) {
            $this->_payment_methods['yandex_pb'] = Leyka_Yandex_Promvzyazbank::get_instance();
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
        if($pm_id == 'yandex_card' && !empty($form_data['leyka_recurring'])) {

            $donation = new Leyka_Donation($donation_id);

            $donation->payment_type = 'rebill';
            $donation->rebilling_is_active = true; // So we could turn it on/off

        } else if(
            $pm_id == 'yandex_sb' &&
            $form_data['leyka_donation_currency'] == 'rur' &&
            $form_data['leyka_donation_amount'] < 10.0
        ) {

            $error = new WP_Error('leyka_donation_amount_too_small', __('The amount of donations via Sberbank Online should be at least 10 RUR.', 'leyka'));
            leyka()->add_payment_form_error($error);

        }
    }

    public function submission_redirect_url($current_url, $pm_id) {
        switch($pm_id) {
            case 'yandex_all':
            case 'yandex_money':
            case 'yandex_card':
            case 'yandex_wm':
                return leyka_options()->opt('yandex_test_mode') ?
                    'https://demomoney.yandex.ru/eshop.xml' : 'https://money.yandex.ru/eshop.xml';
            case 'yandex_sb':
            case 'yandex_ab':
            case 'yandex_pb':
                return 'https://money.yandex.ru/eshop.xml';
            default:
                return $current_url;
        }
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);

        $payment_type = $this->_get_yandex_pm_id($pm_id);
        $payment_type = $payment_type ? $payment_type : apply_filters('leyka_yandex_custom_payment_type', '', $pm_id);

        $data = array(
            'scid' => leyka_options()->opt('yandex_scid'),
            'shopId' => leyka_options()->opt('yandex_shop_id'),
            'sum' => $donation->amount,
            'customerNumber' => $donation->donor_email,
            'orderNumber' => $donation_id,
            'orderDetails' => $donation->payment_title." (№ $donation_id)",
            'paymentType' => $payment_type,
            'shopSuccessURL' => leyka_get_success_page_url(),
            'shopFailURL' => leyka_get_failure_page_url(),
            'cps_email' => $donation->donor_email,
            'cms_name' => 'wp-leyka', // Service parameter, added by Yandex' request
//            '' => ,
        );
        if(leyka_options()->opt('yandex_shop_article_id')) {
            $data['shopArticleId'] = leyka_options()->opt('yandex_shop_article_id');
        }
        if( !empty($_POST['leyka_recurring']) ) {
            $data['rebillingOn'] = 'true';
        }

        return apply_filters('leyka_yandex_custom_submission_data', $data, $pm_id);

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

				if((int)$_POST['orderNumber'] <= 0) { // Recurring donation callback

					$_POST['orderNumber'] = explode('-', $_POST['orderNumber']);
                    if(
                        count($_POST['orderNumber']) == 3 &&
                        $_POST['orderNumber'][0] == 'recurring' &&
                        (int)$_POST['orderNumber'][2] > 0
                    ) {
                        $_POST['orderNumber'] = (int)$_POST['orderNumber'][2];
                    } else { // Order number is wrong
                        $_POST['orderNumber'] = false;
                    }

				} else { // Single donation callback
					$_POST['orderNumber'] = (int)$_POST['orderNumber'];
				}

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

                if((int)$_POST['orderNumber'] <= 0) { // Recurring donation callback

					$_POST['orderNumber'] = explode('-', $_POST['orderNumber']);
                    if(
                        count($_POST['orderNumber']) == 3 &&
                        $_POST['orderNumber'][0] == 'recurring' &&
                        (int)$_POST['orderNumber'][2] > 0
                    ) {
                        $_POST['orderNumber'] = (int)$_POST['orderNumber'][2];
                    } else { // Order number is wrong
                        $_POST['orderNumber'] = false;
                    }

				} else { // Single donation callback
					$_POST['orderNumber'] = (int)$_POST['orderNumber'];
				}

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

                    // Change PM if needed. Mostly for Smart Payments:
                    if($_POST['paymentType'] != $this->_get_yandex_pm_id($donation->pm_id)) {
                        $donation->pm_id = $this->_get_yandex_pm_id($_POST['paymentType']);
                    }

                    if($donation->type == 'rebill' && !empty($_POST['invoiceId'])) {
                        $donation->recurring_id = (int)$_POST['invoiceId'];
                    }

                    Leyka_Donation_Management::send_all_emails($donation->id);
                }

				do_action('leyka_yandex_payment_aviso_success', $donation);

                $this->_callback_answer(0, 'pa'); // OK for yandex money payment
                break; // Not needed, just for my IDE could relax

            default:
				$this->_callback_answer(1, 'unknown', __('Unknown service operation', 'leyka'), 'Unknown callback type: '.$call_type);
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

                // Recurring payment isn't funded here yet! Only its possibility is confirmed.
                // To fund a payment, we should wait for a normal callbacks.

                $res = $new_recurring_donation;

            } else { // Some error on payment test run

                $error_num = empty($vals[0]['attributes']['error']) ? 'unknown' : $vals[0]['attributes']['error'];
                $error_text = empty($vals[0]['attributes']['techMessage']) ?
                    __('Some error while repeatCardPayment call. Please ask your Yandex.Money manager for details.', 'leyka') : $vals[0]['attributes']['techMessage'];

                $new_recurring_donation->add_gateway_response('Error '.$error_num.': '.$error_text);

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

    /** A service method to get Yandex' paymentType values by according pm_ids, and vice versa. */
    protected function _get_yandex_pm_id($pm_id) {

        $all_pm_ids = array(
            'yandex_all' => '',
            'yandex_card' => 'AC',
            'yandex_money' => 'PC',
            'yandex_wm' => 'WM',
            'yandex_sb' => 'SB',
            'yandex_ab' => 'AB',
            'yandex_pb' => 'PB',
//            '' => '',
//            '' => '',
        );

        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        } else if(in_array($pm_id, $all_pm_ids)) {
            return array_search($pm_id, $all_pm_ids);
        } else {
            return false;
        }

    }

}


class Leyka_Yandex_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_all';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Any Yandex.money payment method available', 'leyka');
        $this->_label = __('Yandex.money (any)', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
        ));

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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
                'default' => __('Yandex.Money allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://money.yandex.ru/">Yandex.Money website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Yandex Smart Payment description', 'leyka'),
                'description' => __('Please, enter Yandex.Money smart payment service description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );

    }

}

class Leyka_Yandex_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_card';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Payment with Banking Card', 'leyka');
        $this->_label = __('Banking Card', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
        ));

        /** @todo Right now we can't use leyka_options()->opt() here because Gateway options are not included in options_meta ATM. Refactor this. */
        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id,
            get_option('leyka_'.$this->full_id.'_rebilling_available', true) ?
                array(
                    'recurring' => '<label class="checkbox"><span><input type="checkbox" id="leyka_'.$this->full_id.'_recurring" name="leyka_recurring" value="1"></span> '.__('Monthly donations', 'leyka').'</label>'
                ) :
                array()
        );

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

class Leyka_Yandex_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_money';
        $this->_gateway_id = 'yandex';

        $this->_label_backend = __('Virtual cash Yandex.money', 'leyka');
        $this->_label = __('Yandex.money', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
        ));

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/webmoney.png',
        ));

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array());

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array());

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array());

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, array());

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

}

function leyka_add_gateway_yandex() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Yandex_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_yandex');