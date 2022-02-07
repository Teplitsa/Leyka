<?php if( !defined('WPINC') ) { die; }
/**
 * Leyka_Webpay_Gateway class
 */

class Leyka_Webpay_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'webpay';
        $this->_title = __('Webpay', 'leyka');

        $this->_docs_link = '//leyka.te-st.ru/docs/...'; /** @todo Add the links */
        $this->_registration_link = '//...';
        $this->_has_wizard = false;

        $this->_description = apply_filters(
            'leyka_gateway_description',
            sprintf(__('%s allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="%s">payment gateway website</a> to enter your bank card data and to confirm your payment.', 'leyka'), $this->_title, $this->_registration_link),
            $this->_id
        );

        $this->_min_commission = 2.8;
        $this->_receiver_types = ['legal'];
        $this->_may_support_recurring = true;
        $this->_countries = ['by',];

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = [
            $this->_id.'_store_id' => [
                'type' => 'text',
                'title' => __('Store ID', 'leyka'),
                'comment' => sprintf(__('Please, enter your %s here. It can be found in your contract with the gateway or (for most gateways) by asking your gateway connection manager for it.', 'leyka'), __('Store ID', 'leyka')),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '123456789'),
            ],
            $this->_id.'_secret_key' => [
                'type' => 'text',
                'title' => __('Secret key', 'leyka'),
                'comment' => sprintf(__("Please, enter a %s parameter value from your %s account.", 'leyka'), mb_strtolower(__('Secret key', 'leyka')), $this->_title),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'OkT0flRaEnS0fWqMFZuTg01hu_8SxSkx'),
                'is_password' => true,
            ],
            $this->_id.'_test_mode' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ],
            $this->_id.'_check_callbacks_signature' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Check payments notifications digital signatures', 'leyka'),
                'short_format' => true,
            ],
        ];

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

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return leyka_options()->opt('webpay_test_mode') ? 'https://securesandbox.webpay.by/' : 'https://payment.webpay.by/';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        $seed = time();
        $is_test_mode = leyka_options()->opt('webpay_test_mode') ? '1' : '0';
        $currency_id = mb_strtoupper(leyka_options()->opt('currency_main'));

        $data = [];

        if($donation->type === 'rebill') { // Init recurring donation

            $data['wsb_customer_id'] = $donation->id.'-'.mb_strtolower($donation->donor_email);
            $data['wsb_operation_type'] = 'recurring_bind';

            $signature = sha1(
                $seed.leyka_options()->opt($this->_id.'_store_id').$data['wsb_customer_id'].$donation->id.$is_test_mode
                .$currency_id.$donation->amount.$data['wsb_operation_type'].leyka_options()->opt($this->_id.'_secret_key')
            );

        } else { // Single donation
            $signature = sha1(
                $seed.leyka_options()->opt($this->_id.'_store_id').$donation->id.$is_test_mode.$currency_id.$donation->amount
                .leyka_options()->opt($this->_id.'_secret_key')
            );
        }

        $data = $data + [
            '*scart' => '',
            'wsb_storeid' => leyka_options()->opt($this->_id.'_store_id'),
            'wsb_store' => get_bloginfo('name'),
            'wsb_order_num' => $donation->id,
            'wsb_currency_id' => $currency_id,
            'wsb_version' => '2',
//            'wsb_language_id' => '',
            'wsb_seed' => $seed,
            'wsb_signature' => $signature,
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
        ];

        return apply_filters('leyka_'.$this->_id.'_custom_submission_data', $data, $pm_id);

    }

    protected function _handle_callback_error($error_message = '', Leyka_Donation_Base $donation = null) {

        echo sprintf(__('%s callback error: %s', 'leyka'), $this->_title, trim(esc_attr($error_message)));

        if($donation) {

            $_POST['failure_reason'] = $error_message;

            $donation->add_gateway_response($_POST);
            $donation->status = 'failed';

            if($donation->is_init_recurring_donation) {
                $donation->recurring_is_active = false;
            }

        }

        exit(500);

    }

    protected function _get_donation_status($status_number) {
        switch(absint($status_number)) {
            case 1: // Completed
            case 4: // Authorized
            case 6: // System
            case 10: // Recurring
                return 'funded';
            case 2: // Declined
            case 7: // Voided
            case 8: // Failed
            case 9: // Partial voided
                return 'failed';
            case 5: // Partial refunded
            case 11: // Refunded
                return 'refunded';
            case 3: // Pending
            default:
                return 'submitted';
        }
    }

    public function _handle_service_calls($call_type = '') {

        if( !$_POST || empty($_POST['site_order_id']) ) {
            $this->_handle_callback_error(__('No donation ID given', 'leyka'));
        }

        $donation = Leyka_Donations::get_instance()->get($_POST['site_order_id']);
        if( !$donation ) {
            $this->_handle_callback_error(sprintf(__('Unknown donation ID given: %s', 'leyka'), $_POST['site_order_id']));
        }

        if( !empty($_POST['order_id']) ) {
            $donation->webpay_order_id = esc_attr($_POST['order_id']);
        }
        if( !empty($_POST['transaction_id']) ) {
            $donation->webpay_transaction_id = esc_attr($_POST['transaction_id']);
        }
        if( !empty($_POST['rrn']) ) {
            $donation->webpay_rrn = esc_attr($_POST['rrn']);
        }
        if( !empty($_POST['approval']) ) {
            $donation->webpay_approval = esc_attr($_POST['approval']);
        }

        if($donation->payment_type === 'rebill') {

            if( !empty($_POST['customer_id']) && $donation->webpay_customer_id != $_POST['customer_id'] ) {
                $donation->webpay_customer_id = esc_attr($_POST['customer_id']);
            }
            if( !empty($_POST['recurring_token']) ) {
                $donation->webpay_recurring_token = esc_attr($_POST['recurring_token']);
            }
            if( !empty($_POST['offer_exp_date']) ) {
                $donation->webpay_card_expiring_date = strtotime($_POST['offer_exp_date']);
            }

        }

        if(leyka_options()->opt($this->_id.'_check_callbacks_signature')) {

            if(empty($_POST['wsb_signature'])) {
                $this->_handle_callback_error(__('Cannot check callback signature - no signature given', 'leyka'), $donation);
            }

            if($donation->is_init_recurring_donation) { // Recurring subscription
                $signature_calculated = md5(
                    $_POST['batch_timestamp'].$_POST['currency_id'].$_POST['amount'].$_POST['payment_method'].$_POST['order_id']
                    .$_POST['site_order_id'].$_POST['transaction_id'].$_POST['payment_type'].$_POST['rrn']
                    .$_POST['card'].$_POST['customer_id'].$_POST['operation_type'].$_POST['recurring_token']
                    .$_POST['offer_exp_date'].leyka_options()->opt($this->_id.'_secret_key')
                );
            } else if($donation->type === 'rebill') { // Non-init recurring donation
                $signature_calculated = md5(
                    $_POST['batch_timestamp'].$_POST['currency_id'].$_POST['amount'].$_POST['payment_method'].$_POST['order_id']
                    .$_POST['site_order_id'].$_POST['transaction_id'].$_POST['payment_type'].$_POST['rrn']
                    .$_POST['card'].$_POST['customer_id'].$_POST['operation_type'].$_POST['recurring_token']
                    .leyka_options()->opt($this->_id.'_secret_key')
                );
            } else { // Single donation
                $signature_calculated = md5(
                    $_POST['batch_timestamp'].$_POST['currency_id'].$_POST['amount'].$_POST['payment_method'].$_POST['order_id']
                    .$_POST['site_order_id'].$_POST['transaction_id'].$_POST['payment_type'].$_POST['rrn']
                    .(leyka_options()->opt($this->_id.'-webpay_card_rebilling_available') ? $_POST['card'] : '')
                    .leyka_options()->opt($this->_id.'_secret_key')
                );
            }

            if($signature_calculated != $_POST['wsb_signature']) {
                $this->_handle_callback_error(__('Callback signature check failed', 'leyka'), $donation);
            }

        }

        $donation->status = $this->_get_donation_status(absint($_POST['payment_type']));

        $donation->add_gateway_response($_POST);

        if($donation->status === 'funded') {
            Leyka_Donation_Management::send_all_emails($donation->id);
        } else if($donation->status === 'failed') { // Emails will be sent only if respective options are on
            Leyka_Donation_Management::send_error_notifications($donation);
        }

        if( // GUA direct integration - "purchase" event:
            $donation->status === 'funded'
            && leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
            && leyka_options()->opt('gtm_ua_tracking_id')
            && in_array('purchase', leyka_options()->opt('gtm_ua_enchanced_events'))
            // We should send data to GA only for single or init recurring donations:
            && ($donation->type === 'single' || $donation->is_init_recurring_donation)
        ) {

            require_once LEYKA_PLUGIN_DIR.'vendor/autoload.php';

            $analytics = new TheIconic\Tracking\GoogleAnalytics\Analytics(true);
            $analytics // Main params:
            ->setProtocolVersion('1')
                ->setTrackingId(leyka_options()->opt('gtm_ua_tracking_id'))
                ->setClientId($donation->ga_client_id ? $donation->ga_client_id : leyka_gua_get_client_id())
                // Transaction params:
                ->setTransactionId($donation->id)
                ->setAffiliation(get_bloginfo('name'))
                ->setRevenue($donation->amount)
                ->addProduct([ // Donation params
                    'name' => $donation->payment_title,
                    'price' => $donation->amount,
                    'brand' => get_bloginfo('name'), // Mb, it won't work with it
                    'category' => $donation->type_label, // Mb, it won't work with it
                    'quantity' => 1,
                ])
                ->setProductActionToPurchase()
                ->setEventCategory('Checkout')
                ->setEventAction('Purchase')
                ->sendEvent();

        }
        // GUA direct integration - "purchase" event END

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $response = maybe_unserialize($donation->gateway_response);
        if( !$response ) {
            $response = [];
        }

        $calculated = $response['batch_timestamp'].$response['currency_id'].$response['amount'].$response['payment_method'].$response['order_id']
            .$response['site_order_id'].$response['transaction_id'].$response['payment_type'].$response['rrn']
            .leyka_options()->opt($this->_id.'_secret_key');

        $response = [
            __('Callback received at:', 'leyka') => empty($response['batch_timestamp']) ?
                '-' : date_i18n(get_option('date_format').' '.get_option('time_format'), $response['batch_timestamp']),
            __('Amount:', 'leyka') => empty($response['amount']) ? '-' : $response['amount'],
            __('Currency ID:', 'leyka') => empty($response['currency_id']) ? '-' : $response['currency_id'],
            __('Payment method:', 'leyka') => empty($response['payment_method']) ? '-' : $response['payment_method'],
            __('Gateway order ID:', 'leyka') => empty($response['order_id']) ? '-' : $response['order_id'],
            __('Leyka donation ID:', 'leyka') => empty($response['site_order_id']) ? '-' : $response['site_order_id'],
            __('Transaction ID:', 'leyka') => empty($response['transaction_id']) ? '-' : $response['transaction_id'],
            __('Payment type:', 'leyka') => empty($response['payment_type']) ? '-' : $response['payment_type'],
            __('RRN:', 'leyka') => empty($response['rrn']) ? '-' : $response['rrn'],
            __('Digital signature:', 'leyka') => empty($response['wsb_signature']) ? '-' : $response['wsb_signature'],
            __('Calculated signature:', 'leyka') => md5(
                $response['batch_timestamp'].$response['currency_id'].$response['amount'].$response['payment_method'].$response['order_id']
                .$response['site_order_id'].$response['transaction_id'].$response['payment_type'].$response['rrn']
                .leyka_options()->opt($this->_id.'_secret_key')
            ),
            __('Action:', 'leyka') => isset($response['action']) ? $response['action'] : '-',
            __('RC', 'leyka') => empty($response['rc']) ? '-' : $response['rc'],
            __('Approval:', 'leyka') => empty($response['approval']) ? '-' : $response['approval'],
            __('Order Tag:', 'leyka') => empty($response['order_tag']) ? '-' : $response['order_tag'],
        ];

        if($donation->status === 'failed') {
            $response[__('Failure reason:')] = empty($donation->gateway_response['failure_reason']) ?
                '-' : $donation->gateway_response['failure_reason'];
        }

        if($donation->payment_type === 'rebill' && $donation->is_init_recurring_donation) {

            $response[__('Customer ID:', 'leyka')] = $donation->gateway_response['customer_id'];
            $response[__('Recurring token:', 'leyka')] = $donation->gateway_response['recurring_token'];
            $response[__('Bank card expiring date:', 'leyka')] = $donation->gateway_response['offer_exp_date'];

        }

        return apply_filters('leyka_donation_gateway_response', $response, $donation);

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('WebPay order ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="webpay-order-id" name="webpay-order-id" placeholder="<?php _e('Enter WebPay order ID', 'leyka');?>" value="<?php echo $donation->webpay_order_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->webpay_order_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('WebPay transaction ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="webpay-transaction-id" name="webpay-transaction-id" placeholder="<?php _e('Enter WebPay transaction ID', 'leyka');?>" value="<?php echo $donation->webpay_transaction_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->webpay_transaction_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('WebPay RRN', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="webpay-rrn" name="webpay-rrn" placeholder="<?php _e('Enter WebPay payment RRN', 'leyka');?>" value="<?php echo $donation->webpay_rrn;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->webpay_rrn;?></span>
                <?php }?>
            </div>

            <label><?php _e('WebPay approval', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="webpay-approval" name="webpay-approval" placeholder="<?php _e('Enter WebPay approval', 'leyka');?>" value="<?php echo $donation->webpay_approval;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->webpay_approval;?></span>
                <?php }?>
            </div>

            <?php if($donation->type === 'rebill') {

                $init_recurring_donation = $donation->init_recurring_donation;?>

                <div class="recurring-is-active-field">
                    <label for="webpay-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                    <div class="leyka-ddata-field">
                        <input type="checkbox" id="webpay-recurring-is-active" name="webpay-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
                    </div>
                </div>

                <label><?php _e('Customer ID', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <span class="fake-input"><?php echo $donation->webpay_customer_id;?></span>
                </div>

                <label><?php _e('WebPay recurring token', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <span class="fake-input"><?php echo $donation->webpay_recurring_token;?></span>
                </div>

                <label><?php _e('Bank card expiring date', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <span class="fake-input"><?php echo $donation->webpay_card_expiring_date ? date(get_option('date_format'), $donation->webpay_card_expiring_date) : '';?></span>
                </div>

            <?php }

        } else { // New donation page displayed ?>

            <label for="webpay-order-id"><?php _e('WebPay order ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="webpay-order-id" name="webpay-order-id" placeholder="<?php _e('Enter WebPay order ID', 'leyka');?>" value="">
            </div>

            <label for="webpay-transaction-id"><?php _e('WebPay transaction ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="webpay-transaction-id" name="webpay-transaction-id" placeholder="<?php _e('Enter WebPay transaction ID', 'leyka');?>" value="">
            </div>

            <label for="webpay-rrn"><?php _e('WebPay RRN', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="webpay-rrn" name="webpay-rrn" placeholder="<?php _e('Enter WebPay RRN', 'leyka');?>" value="">
            </div>

            <label for="webpay-transaction-id"><?php _e('WebPay approval', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="webpay-transaction-id" name="webpay-approval" placeholder="<?php _e('Enter WebPay approval', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'webpay_order_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_order_id');
            case 'webpay_transaction_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_transaction_id');
            case 'webpay_rrn':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_rrn');
            case 'webpay_approval':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_approval');
            case 'webpay_customer_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_customer_id');
            case 'webpay_recurring_token':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_recurring_token');
            case 'webpay_card_expiring_date':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_webpay_card_expiring_date');
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'webpay_order_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_order_id', $value);
            case 'webpay_transaction_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_transaction_id', $value);
            case 'webpay_rrn':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_rrn', $value);
            case 'webpay_approval':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_approval', $value);
            case 'webpay_customer_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_customer_id', $value);
            case 'webpay_recurring_token':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_recurring_token', $value);
            case 'webpay_card_expiring_date':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_webpay_card_expiring_date', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['webpay-order-id']) && $donation->webpay_order_id != $_POST['webpay-order-id']) {
            $donation->webpay_order_id = $_POST['webpay-order-id'];
        }
        if(isset($_POST['webpay-transaction-id']) && $donation->webpay_transaction_id != $_POST['webpay-transaction-id']) {
            $donation->webpay_transaction_id = $_POST['webpay-transaction-id'];
        }
        if(isset($_POST['webpay-rrn']) && $donation->webpay_rrn != $_POST['webpay-rrn']) {
            $donation->webpay_rrn = $_POST['webpay-rrn'];
        }
        if(isset($_POST['webpay-approval']) && $donation->webpay_approval != $_POST['webpay-approval']) {
            $donation->webpay_approval = $_POST['webpay-approval'];
        }
        if(isset($_POST['webpay-customer-id']) && $donation->webpay_customer_id != $_POST['webpay-customer-id']) {
            $donation->webpay_customer_id = $_POST['webpay-customer-id'];
        }
        if(isset($_POST['webpay-recurring-token']) && $donation->webpay_recurring_token != $_POST['webpay-recurring-token']) {
            $donation->webpay_recurring_token = $_POST['webpay-recurring-token'];
        }

        $donation->recurring_is_active = !empty($_POST['webpay-recurring-is-active']);

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['webpay_order_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, '_webpay_order_id', $params['webpay_order_id']);
        }
        if( !empty($params['webpay_transaction_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_webpay_transaction_id', $params['webpay_transaction_id']
            );
        }
        if( !empty($params['webpay_rrn']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, '_webpay_rrn', $params['webpay_rrn']);
        }
        if( !empty($params['webpay_approval']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, '_webpay_approval', $params['webpay_approval']);
        }
        if( !empty($params['webpay_customer_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_webpay_customer_id', $params['webpay_customer_id']
            );
        }
        if( !empty($params['webpay_recurring_token']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_webpay_recurring_token', $params['webpay_recurring_token']
            );
        }
        if( !empty($params['webpay_card_expiring_date']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_webpay_card_expiring_date', $params['webpay_card_expiring_date']
            );
        }

    }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->webpay_customer_id) {
            return false;
        }

        if( $init_recurring_donation->webpay_card_expiring_date < strtotime(date('Y-m-d')) ) { // The Donor's card is expired

            $init_recurring_donation->recurring_is_active = false;
            return false;

        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'init_recurring_donation' => $init_recurring_donation->id,
                'webpay_order_id' => $init_recurring_donation->webpay_order_id,
                'webpay_transaction_id' => $init_recurring_donation->webpay_transaction_id,
                'webpay_rrn' => $init_recurring_donation->webpay_rrn,
                'webpay_approval' => $init_recurring_donation->webpay_approval,
                'webpay_customer_id' => $init_recurring_donation->webpay_customer_id,
                'webpay_recurring_token' => $init_recurring_donation->webpay_recurring_token,
                'webpay_card_expiring_date' => $init_recurring_donation->webpay_card_expiring_date,
            ],
            ['recalculate_total_amount' => true,]
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $seed = time();
        $is_test_mode = leyka_options()->opt('webpay_test_mode') ? '1' : '0';
        $currency_id = mb_strtoupper(leyka_options()->opt('currency_main'));

        $signature = sha1(
            $seed.leyka_options()->opt($this->_id.'_store_id').$init_recurring_donation->webpay_customer_id
            .$new_recurring_donation->id.$is_test_mode.$currency_id.$new_recurring_donation->amount.'recurring_pay'
            .$init_recurring_donation->webpay_recurring_token.leyka_options()->opt($this->_id.'_secret_key')
        );

        $data = [
            '*scart' => '',
            'wsb_storeid' => leyka_options()->opt($this->_id.'_store_id'),
            'wsb_store' => __('Leyka', 'leyka').' - '.(
                leyka_options()->opt('webpay_test_mode') ?
                    _x('test', 'like in "test donation"', 'leyka').' '.mb_strtolower(__('Recurring donation', 'leyka')) :
                    mb_strtolower(__('Recurring donation', 'leyka'))
                ),
            'wsb_order_num' => $new_recurring_donation->id,
            'wsb_currency_id' => $currency_id,
            'wsb_version' => '2',
//            'wsb_language_id' => '',
            'wsb_seed' => $seed,
            'wsb_signature' => $signature,
            'wsb_return_url' => leyka_get_success_page_url(),
            'wsb_cancel_return_url' => leyka_get_failure_page_url(),
            'wsb_notify_url' => site_url('/leyka/service/webpay/process/'), // Callback URL
            'wsb_test' => $is_test_mode,
            'wsb_customer_name' => $init_recurring_donation->donor_name,
//            'wsb_customer_address' => '',
            'wsb_email' => $init_recurring_donation->donor_email,
            'wsb_invoice_item_name[0]' => $init_recurring_donation->payment_title,
            'wsb_invoice_item_quantity[0]' => 1,
            'wsb_invoice_item_price[0]' => $init_recurring_donation->amount,
            'wsb_total' => $init_recurring_donation->amount,
            'wsb_order_tag' => __('Recurring donation', 'leyka'),
            'wsb_customer_id' => $init_recurring_donation->webpay_customer_id,
            'wsb_operation_type' => 'recurring_pay',
            'wsb_recurring_token' => $init_recurring_donation->webpay_recurring_token,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim($this->submission_redirect_url('', ''), '/'),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
        ]);

        if( !$result = curl_exec($ch) ) {
            $this->_handle_callback_error(sprintf(__("Rebilling request to the WebPay system couldn't be made due to some error.\n\nThe error: %s", 'leyka'), curl_error($ch).' ('.curl_errno($ch).')'), $new_recurring_donation);
        }

        curl_close($ch);

        $result = json_decode($result, true);

        if(empty($result['payment_type']) || $this->_get_donation_status($result['payment_type']) === 'failed') {
            $new_recurring_donation->status = 'failed';
        }

        $new_recurring_donation->add_gateway_response($result);

        return $new_recurring_donation;

    }

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

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
        ]);

        $this->_supported_currencies[] = 'byn';
        $this->_default_currency = 'byn';

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            $this->full_id.'_rebilling_available' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'comment' => __('Check if the gateway allows you to create recurrent subscriptions to do regular automatic payments.', 'leyka'),
                'short_format' => true,
                'field_classes' => ['active-recurring-available',],
            ],
            'active_recurring_setup_help' => [
                'type' => 'static_text',
                'title' => __('The necessary Cron job setup', 'leyka'),
                'is_html' => true,
                'value' => leyka_get_active_recurring_setup_help_content(),
                'field_classes' => ['active-recurring-on'],
            ],
        ];

    }

    public function has_recurring_support() {
        return !!leyka_options()->opt($this->full_id.'_rebilling_available');
    }

}

function leyka_add_gateway_webpay() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Webpay_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_webpay');