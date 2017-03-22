<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Uniteller_Gateway class
 */

class Leyka_Uniteller_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'uniteller';
        $this->_title = __('Uniteller', 'leyka');
        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-cloudpaiments/';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 30;
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'uniteller_Shop_IDP' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Uniteller eshopId', 'leyka'),
                'description' => __('Please, enter your eshopId value here.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 1234', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'uniteller_password' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('Uniteller secret key', 'leyka'),
                'description' => __('Please, enter your secret key value here.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., fW!^12@3#&8A4', 'leyka'),
                'is_password' => true,
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),

        );
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Uniteller_Card::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        if($pm_id == 'card' && !empty($form_data['leyka_recurring'])) {

            $donation = new Leyka_Donation($donation_id);

            $donation->payment_type = 'rebill';
            $donation->rebilling_is_active = true; // So we could turn it on/off

        }
    }

    public function submission_redirect_url($current_url, $pm_id) {

        $current_url = 'https://wpay.uniteller.ru/pay/';
        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);
        /*?><pre><?print_r($donation);?></pre><?*/
        //update_post_meta( $donation_id, 'recurring_id', $donation_id );
        $donation->recurring_id = $donation_id;

        switch($pm_id) { // PC - Yandex.money, AC - bank card, WM - Webmoney, MC - mobile payments
            //case 'yandex_money': $payment_type = 'PC'; break;
            case 'card': $payment_type = 'AC'; break;
            default:
                $payment_type = apply_filters('leyka_uniteller_custom_payment_type', '', $pm_id);
        }

        $data = array(
            'Shop_IDP' => leyka_options()->opt('uniteller_Shop_IDP'),
            'Order_IDP' => $donation_id,
            'Subtotal_P' => $donation->amount,
            'Signature' => strtoupper(md5(md5(leyka_options()->opt('uniteller_Shop_IDP')).'&'.md5($donation_id).'&'.md5($donation->amount).'&'.
                md5('').'&'.md5('').'&'.md5('').'&'.md5('').'&'.
                md5('').'&'.md5('').'&'.md5('').'&'.md5(leyka_options()->opt('uniteller_password')))),
            'Currency' => 'RUB',//$currency,
            'URL_RETURN_OK' => leyka_get_success_page_url(),
            'URL_RETURN_NO' => leyka_get_failure_page_url(),
        );

        if(leyka_options()->opt('uniteller_shop_article_id')) {
            $data['shopArticleId'] = leyka_options()->opt('uniteller_shop_article_id');
        }
        if( !empty($_POST['leyka_recurring']) ) {
            $data =  array(
                    'Shop_IDP' => leyka_options()->opt('uniteller_Shop_IDP'),
                    'Order_IDP' => $donation_id,
                    'Subtotal_P' => $donation->amount,
                    'Signature' => strtoupper(md5(md5(leyka_options()->opt('uniteller_Shop_IDP')).'&'.md5($donation_id).'&'.md5($donation->amount).'&'.
                        md5('').'&'.md5('').'&'.md5('').'&'.md5('').'&'.
                        md5('').'&'.md5('').'&'.md5('').'&'.md5(leyka_options()->opt('uniteller_password')))),
                    'Currency' => 'RUB',//$currency,
                    'URL_RETURN_OK' => leyka_get_success_page_url(),
                    'URL_RETURN_NO' => leyka_get_failure_page_url(),
                    'IsRecurrentStart' => '1',

            );
            $data['rebillingOn'] = 'true';

            $meta_values = get_post_meta($donation_id);
            ?><pre><?print_r($donation);?></pre><?
            ?><pre><?print_r($meta_values);?></pre><?
            //die();
            //set_specific_data_value('recurring_id', $donation_id, $donation);
        }

        return apply_filters('leyka_uniteller_custom_submission_data', $data, $pm_id);
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
shopId="'.leyka_options()->opt('uniteller_shop_id').'"
message="'.$message.'"
techMessage="'.$tech_message.'"/>');
        } else {
            die('<?xml version="1.0" encoding="UTF-8"?>
<'.$callback_type.' performedDatetime="'.date(DATE_ATOM).'"
code="0" invoiceId="'.$_POST['invoiceId'].'"
shopId="'.leyka_options()->opt('uniteller_shop_id').'"/>');
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

                //$donation->add_gateway_response($_POST);
                if($donation->status != 'funded') {
                    echo "!FUNDED";

                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';

                    if($donation->type == 'rebill'/* && !empty($_POST['invoiceId'])*/) {
                        $donation->recurring_id = $donation->id;
                        print_r($donation);
                        die();
                    }

                    Leyka_Donation_Management::send_all_emails($donation->id);
                }

                $this->_callback_answer(); // OK for yandex.money payment
                break; // Not needed, just for my IDE could relax

            default:
        }
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {
        ?><pre><?print_r($donation->gateway_response);?></pre><?

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
        echo('WORK!<br>');
        if( !$init_recurring_donation->recurring_id ) {
            echo('!$init_recurring_donation->recurring_id');
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


        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://wpay.uniteller.ru/recurrent/',
            //CURLOPT_PORT => leyka_options()->opt('yandex_test_mode') ? 8083 : 443,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'Order_IDP' => $new_recurring_donation_id,
                'Parent_Order_IDP' => $init_recurring_donation->recurring_id,
                'Shop_IDP' => leyka_options()->opt('uniteller_Shop_IDP'),
                //'orderNumber' => 'recurring-'.$init_recurring_donation->id.'-'.$new_recurring_donation_id,
                'Subtotal_P' => $init_recurring_donation->amount,
                'Signature' => strtoupper(md5(md5(leyka_options()->opt('uniteller_Shop_IDP')).'&'.md5($new_recurring_donation_id).'&'.md5($init_recurring_donation->amount).'&'.
                        md5($init_recurring_donation->recurring_id).'&'.md5(leyka_options()->opt('uniteller_password')))),
            )),
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            /*CURLOPT_SSLCERT => $certificate_path,
            CURLOPT_SSLKEY => $certificate_private_key_path,
            CURLOPT_SSLKEYPASSWD => leyka_options()->opt('yandex-yandex_card_private_key_password'),*/
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
            /*?><pre><?print_r($donation);?></pre><?*/

            if($donation->type != 'rebill') {
                return;
            }?>

            <label><?php _e('Uniteller recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="uniteller-recurring-id" name="uniteller-recurring-id" placeholder="<?php _e('Enter Uniteller invoice ID', 'leyka');?>" value="<?php echo $donation->recurring_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->recurring_id;?></span>
                <?php }?>
            </div>

        <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <label for="uniteller-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?></label>
            <div class="leyka-ddata-field">
                <input type="checkbox" id="uniteller-recurring-is-active" name="uniteller-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
            </div>
            <?/*<pre><?print_r($donation);?></pre>*/?>

        <?php } else { // New donation page displayed ?>

            <label for="uniteller-recurring-id"><?php _e('Uniteller recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="uniteller-recurring-id" name="uniteller-recurring-id" placeholder="<?php _e('Enter Uniteller invoice ID', 'leyka');?>" value="">
            </div>
            <?php
        }
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'uniteller_recurrent_id':
            case 'uniteller_recurring_id':
            case 'uniteller_invoice_id': return get_post_meta($donation->id, '_uniteller_invoice_id', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'uniteller_recurrent_id':
            case 'uniteller_recurring_id':
            case 'uniteller_invoice_id':
                return update_post_meta($donation->id, '_uniteller_invoice_id', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {
        if(isset($_POST['uniteller-recurring-id']) && $donation->recurring_id != $_POST['uniteller-recurring-id']) {
            $donation->recurring_id = $_POST['uniteller-recurring-id'];
        }

        $_POST['uniteller-recurring-is-active'] = !empty($_POST['uniteller-recurring-is-active']);

        // Check if the value's different is inside the Leyka_Donation::__set():
        $donation->recurring_is_active = $_POST['uniteller-recurring-is-active'];
        $donation->recurring_id = $donation->id;
    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['recurring_id']) ) {
            update_post_meta($donation_id, '_uniteller_invoice_id', $donation_params['recurring_id']);
        }
    }
}


class Leyka_Uniteller_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'uniteller';

        $this->_label_backend = __('Payment with Banking Card', 'leyka');
        $this->_label = __('Banking Card', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
//            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/yandex_money_s.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/uniteller/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/uniteller/icons/master.png',
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
                'description' => __('Check if Uniteller allows you to create recurrent subscriptions to do regular automatic payments.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('Uniteller allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the Uniteller website to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Uniteller card payment description', 'leyka'),
                'description' => __('Please, enter Uniteller bank cards payment description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_uniteller() {
    leyka()->add_gateway(Leyka_Uniteller_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_uniteller');