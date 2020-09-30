<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Webpay_Gateway class
 */

class Leyka_Webpay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'webpay';
        $this->_title = __('Webpay', 'leyka');

        $this->_docs_link = '//leyka.te-st.ru/docs/yandex-dengi/';
        $this->_registration_link = 'https://kassa.yandex.ru/joinups';
        $this->_has_wizard = true;

        $this->_description = apply_filters(
            'leyka_gateway_description',
            sprintf(__('%s allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="%s">payment gateway website</a> to enter your bank card data and to confirm your payment.', 'leyka'), $this->_title, $this->_registration_link),
            $this->_id
        );

        $this->_min_commission = 2.8;
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = false;
        $this->_countries = array('by',);

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = array(
            $this->_id.'_store_id' => array(
                'type' => 'text',
                'title' => __('Store ID', 'leyka'),
                'comment' => sprintf(__('Please, enter your %s here. It can be found in your contract with the gateway or (for most gateways) by asking your gateway connection manager for it.', 'leyka'), __('Store ID', 'leyka')),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '123456789'),
            ),
            $this->_id.'_secret_key' => array(
                'type' => 'text',
                'title' => __('Secret key', 'leyka'),
                'comment' => sprintf(__("Please, enter a %s parameter value from your %s account.", 'leyka'), mb_strtolower(__('Secret key', 'leyka')), $this->_title),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'OkT0flRaEnS0fWqMFZuTg01hu_8SxSkx'),
                'is_password' => true,
            ),
            $this->_id.'_test_mode' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ),
        );

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('webpay_store_id') && leyka_options()->opt('webstore_secret_key');
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['webpay_card'])) {
            $this->_payment_methods['webpay_card'] = Leyka_Webpay_Card::get_instance();
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);

//        if( !empty($form_data['leyka_recurring']) ) {
//
//            $donation->payment_type = 'rebill';
//            $donation->recurring_is_active = true; // So we could turn it on/off later
//
//        }

    }

//    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
//        return 'redirect';
//    }

    public function submission_redirect_url($current_url, $pm_id) {
        return leyka_options()->opt('webpay_test_mode') ? 'https://securesandbox.webpay.by/' : 'https://payment.webpay.by/';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);

        $seed = time();
        $is_test_mode = leyka_options()->opt('webpay_test_mode') ? '1' : '0';
        $currency_id = mb_strtoupper(leyka_options()->opt('currency_main'));

        $data = array(
            '*scart' => '',
            'wsb_storeid' => leyka_options()->opt('webpay_store_id'),
            'wsb_store' => __('Leyka', 'leyka').' - '.(leyka_options()->opt('webpay_test_mode') ? __('test donation', 'leyka') : mb_strtolower(__('Donation', 'leyka'))),
            'wsb_order_num' => $donation->id,
            'wsb_currency_id' => $currency_id,
            'wsb_version' => '2',
//            'wsb_language_id' => '',
            'wsb_seed' => $seed,
            'wsb_signature' => sha1(
                $seed
                .leyka_options()->opt('webpay_store_id')
                .$donation->id
                .$is_test_mode
                .$currency_id
                .$donation->amount
                .leyka_options()->opt('webpay_secret_key')
            ),
            'wsb_return_url' => leyka_get_success_page_url(),
            'wsb_cancel_return_url' => leyka_get_failure_page_url(),
            'wsb_notify_url' => site_url('/leyka/service/webpay/process/'), // Callback URL
            'wsb_test' => $is_test_mode,
            'wsb_customer_name' => $form_data['leyka_donor_name'],
//            'wsb_customer_address' => '',
            'wsb_email' => $form_data['leyka_donor_email'],
            'wsb_invoice_item_name[0]' => $donation->payment_title,
            'wsb_invoice_item_quantity[0]' => 1,
            'wsb_invoice_item_price[0]' => $donation->amount,
            'wsb_total' => $donation->amount,
            'wsb_order_tag' => $donation->type === 'rebill' ? __('Recurring subscription', 'leyka') : __('Single', 'leyka'),
        );

        return apply_filters('leyka_webpay_custom_submission_data', $data, $pm_id);

    }

    public function _handle_service_calls($call_type = '') {

//        $donation = new Leyka_Donation($payment->metadata->donation_id);
//        $donation->add_gateway_response($payment);

//        $donation->status = 'funded';
//        Leyka_Donation_Management::send_all_emails($donation->id);

//        $donation->status = 'failed';
//        if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
//            Leyka_Donation_Management::send_error_notifications($donation);
//        }

//        $donation->status = 'refunded';

    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        require_once LEYKA_PLUGIN_DIR.'gateways/yandex/lib/autoload.php';

//        $response = is_object($donation->gateway_response) || is_array($donation->gateway_response) ?
//            serialize($donation->gateway_response) : $donation->gateway_response;

//        $response = maybe_unserialize($donation->gateway_response);
//        if( !$response ) {
//            $response = array();
//        } else if( !is_array($response) ) {
//            $response = array('' => ucfirst($response));
//        }

        $response = array(
//            __('Last response operation:', 'leyka') => empty($response['action']) ?
//                __('Unknown', 'leyka') :
//                ($response['action'] == 'checkOrder' ? __('Donation confirmation', 'leyka') : __('Donation approval notice', 'leyka')),
//            __('Gateway invoice ID:', 'leyka') => empty($response['invoiceId']) ? '' : $response['invoiceId'],
//            __('Full donation amount:', 'leyka') => empty($response['orderSumAmount']) ?
//                '' : (float)$response['orderSumAmount'].' '.$donation->currency_label,
//            __('Donation amount after gateway commission:', 'leyka') => empty($response['shopSumAmount']) ?
//                '' : (float)$response['shopSumAmount'].' '.$donation->currency_label,
//            __('Gateway donor ID:', 'leyka') => empty($response['customerNumber']) ? '' : $response['customerNumber'],
//            __('Response date:', 'leyka') => empty($response['requestDatetime']) ?
//                '' : date('d.m.Y, H:i:s', strtotime($response['requestDatetime'])),
        );

        return $response;

    }

    /*
    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);

            if($donation->type !== 'rebill') {
                return;
            }?>

            <label><?php _e('Yandex.Kassa recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="yandex-recurring-id" name="yandex-recurring-id" placeholder="<?php _e('Enter Yandex.Kassa invoice ID', 'leyka');?>" value="<?php echo $donation->recurring_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->recurring_id;?></span>
                <?php }?>
            </div>

        <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">
                <label for="yandex-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="checkbox" id="yandex-recurring-is-active" name="yandex-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
                </div>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="yandex-recurring-id"><?php _e('Yandex.Kassa recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="yandex-recurring-id" name="yandex-recurring-id" placeholder="<?php _e('Enter Yandex.Kassa invoice ID', 'leyka');?>" value="">
            </div>
            <?php
        }

    }
    */

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {
        switch($field_name) {
//            case 'recurring_id':
//            case 'recurrent_id':
//            case 'invoice_id':
//            case 'payment_id':
//            case 'webpay_recurrent_id':
//            case 'webpay_recurring_id':
//            case 'webpay_invoice_id':
//            case 'webpay_payment_id':
//                return get_post_meta($donation->id, '_webpay_invoice_id', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {
        switch($field_name) {
//            case 'recurring_id':
//            case 'recurrent_id':
//            case 'invoice_id':
//            case 'payment_id':
//            case 'webpay_recurrent_id':
//            case 'webpay_recurring_id':
//            case 'webpay_invoice_id':
//            case 'webpay_payment_id':
//                return update_post_meta($donation->id, '_webpay_invoice_id', $value);
            default: return false;
        }
    }

//    public function save_donation_specific_data(Leyka_Donation $donation) {
//
//        if(isset($_POST['webpay-recurring-id']) && $donation->recurring_id != $_POST['webpay-recurring-id']) {
//            $donation->recurring_id = $_POST['webpay-recurring-id'];
//        }
//
//        $donation->recurring_is_active = !empty($_POST['webpay-recurring-is-active']);
//
//    }

//    public function add_donation_specific_data($donation_id, array $donation_params) {
//        if( !empty($donation_params['recurring_id']) ) {
//            update_post_meta($donation_id, '_webpay_invoice_id', $donation_params['recurring_id']);
//        }
//    }

}


class Leyka_Webpay_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'webpay_card';
        $this->_gateway_id = 'webpay';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Yandex.Kassa allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://money.yandex.ru/">Yandex.Kassa website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'byn';
        $this->_default_currency = 'byn';

    }

    protected function _set_options_defaults() {

//        if($this->_options) {
//            return;
//        }

    }

    public function has_recurring_support() {
        return false; //!!leyka_options()->opt($this->full_id.'_rebilling_available');
    }

}

function leyka_add_gateway_webpay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Webpay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_webpay');