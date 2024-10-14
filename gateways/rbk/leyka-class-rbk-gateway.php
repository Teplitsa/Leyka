<?php if( !defined('WPINC') ) { die; }
/**
 * Leyka_Rbk_Gateway class
 */

require_once LEYKA_PLUGIN_DIR.'gateways/rbk/includes/Leyka_Rbk_Gateway_Webhook_Verification.php';
require_once LEYKA_PLUGIN_DIR.'gateways/rbk/includes/Leyka_Rbk_Gateway_Helper.php';

class Leyka_Rbk_Gateway extends Leyka_Gateway {

    protected static $_instance;

    const RBK_API_HOST = 'https://api.rbk.money';
    const RBK_API_PATH = '/v2/processing/invoices';

    protected $_rbk_response;
    protected $_rbk_log = [];

    protected function _set_attributes() {

        $this->_id = 'rbk';
        $this->_title = __('RBK Money', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.org/docs/podklyuchenie-rbk/';
        $this->_registration_link = '//auth.rbk.money/auth/realms/external/login-actions/registration?client_id=koffing';

        $this->_min_commission = 2.9;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'rbk_shop_id' => [
                'type' => 'text',
                'title' => __('RBK Money shopID', 'leyka'),
                'comment' => __('Please, enter your shopID value here. It can be found in your contract with RBK Money or in your control panel there.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1234'),
            ],
            'rbk_api_key' => [
                'type' => 'textarea',
                'title' => __('RBK Money apiKey', 'leyka'),
                'comment' => __('Please, enter your apiKey value here. It can be found in your RBK Money control panel.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'RU123456789'),
            ],
            'rbk_api_web_hook_key' => [
                'type' => 'textarea',
                'title' => __('RBK Money webhook public key', 'leyka'),
                'comment' => __('Please, enter your webhook public key value here.', 'leyka'),
                'required' => true,
                'placeholder' => __('-----BEGIN PUBLIC KEY----- ...', 'leyka'),
            ],
            'rbk_keep_payment_logs' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Keep detailed logs of all gateway service operations', 'leyka'),
                'comment' => __('Check if you want to keep detailed logs of all gateway service operations for each incoming donation.', 'leyka'),
                'short_format' => true,
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

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['bankcard'])) {
            $this->_payment_methods['bankcard'] = Leyka_Rbk_Card::get_instance();
        }
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Rbk_Card::get_instance()->active) {

            wp_enqueue_script('leyka-rbk-checkout', 'https://checkout.rbk.money/checkout.js', [], LEYKA_VERSION, true);
            wp_enqueue_script(
                'leyka-rbk',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Rbk_Gateway::get_instance()->id.'/js/leyka.rbk.js',
                ['jquery', 'leyka-rbk-checkout',],
                LEYKA_VERSION,
                true
            );

        }

    }

    protected function _get_donation_by_invoice_id($invoice_id) {
        return Leyka_Donations::get_instance()->get_donation_id_by_meta_value('rbk_invoice_id', $invoice_id);
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_active = true;

        }

        // 1. Create an invoice:
        $api_request_url = self::RBK_API_HOST.self::RBK_API_PATH;
        $args = [
            'timeout' => 30,
            'redirection' => 10,
            'blocking' => true,
            'httpversion' => '1.1',
            'headers' => [
                'X-Request-ID' => uniqid(),
                'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'shopID' => leyka_options()->opt('leyka_rbk_shop_id'),
                'amount' => 100 * (int)$donation->amount, // Amount in minor currency units (like cent or kopeyka). Must be int
                'currency' => 'RUB',
                /* translators: %s: Payment title. */
                'product' => sprintf(__('%s - recurring donation','leyka'), $donation->payment_title),
                'dueDate' => gmdate( 'Y-m-d\TH:i:s\Z', strtotime('+2 minute', current_time('timestamp', 1)) ),
                'metadata' => ['donation_id' => $donation_id,],
            ])
        ];

        if(leyka_options()->opt('rbk_keep_payment_logs')) {
            $this->_rbk_log['RBK_Request'] = ['url' => $api_request_url, 'params' => $args,];
        }

        $this->_rbk_response = json_decode(wp_remote_retrieve_body(wp_remote_post($api_request_url, $args)));

        // 2. Create a payment for the invoice - will be done on the frontend, by the RBK Checkout widget

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return '';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        if( !array_key_exists($pm_id, $this->_payment_methods) ) {
            return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return ['status' => 1, 'message' => $donation_id->get_error_message()];
        } else if( !$donation_id ) {
            return ['status' => 1, 'message' => __('The donation was not created due to error.', 'leyka')];
        }

        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        $invoice_access_token = $this->_rbk_response['invoiceAccessToken']['payload'];
        $donation->rbk_invoice_id = $this->_rbk_response['invoice']['id'];

        if(leyka_options()->opt('rbk_keep_payment_logs')) {

            $this->_rbk_log['RBK_Response'] = (array)$this->_rbk_response;
            $donation->add_gateway_response($this->_rbk_log);

        } else {
            $donation->add_gateway_response((array)$this->_rbk_response);
        }

        return [
            'invoice_id' => $this->_rbk_response['invoice']['id'],
            'invoice_access_token' => $invoice_access_token,
            'is_recurring' => !empty($form_data['leyka_recurring']),
            'amount' => $donation->amount, // For GA EEC, "eec.add" event
            /* translators: %s: Donation id. */
            'name' => sprintf(__('Donation #%s', 'leyka'), $donation_id),
            'description' => esc_attr($campaign->payment_title),
            'donor_email' => $donation->donor_email,
            'default_pm' => 'bankCard',
            'success_page' => leyka_get_success_page_url(),
            'pre_submit_step' => '<div class="leyka-rbk-final-submit-buttons">
                <button class="rbk-final-submit-button">'.
                /* translators: %s: Dotante amount and currency label. */
                sprintf(__('Donate %s', 'leyka'), $donation->amount.' '.$donation->currency_label) .'</button>
                <button class="rbk-final-cancel-button">'.__('Cancel', 'leyka').'</button>
            </div>'
        ];

    }

    public function _handle_service_calls($call_type = '') {
        // Callback URLs are: some-website.org/leyka/service/rbk/process/
        // Request content should contain "eventType" field.
        // Possible field values: InvoicePaid, PaymentRefunded, PaymentProcessed, PaymentFailed, InvoiceCancelled, PaymentCancelled

        $data = file_get_contents('php://input');
        $check = Leyka_Rbk_Gateway_Webhook_Verification::verify_header_signature($data);
        $data = json_decode($data, true);

        if(is_wp_error($check)) {
            wp_die( wp_kses_post( $check->get_error_message() ) );
        } else if(empty($data['eventType']) || !is_string($data['eventType'])) {
            wp_die(esc_html__('Webhook error: eventType field is not found or have incorrect value', 'leyka'));
        }

        switch($data['eventType']) {
            case 'InvoicePaid':
            case 'PaymentRefunded':
            case 'PaymentFailed':
            case 'InvoiceCancelled':
            case 'PaymentCancelled':
                $this->_handle_webhook_donation_status_change($data);
                break;
            case 'PaymentProcessed':
                $this->_handle_payment_processed($data);
                break;
            default:
        }

    }

    protected function _handle_webhook_donation_status_change($data) {

        if( !is_array($data) || empty($data['invoice']['id']) || empty($data['eventType']) ) {
            return false; // Mb, return WP_Error?
        }

        $map_status = [
            'InvoicePaid' => 'funded',
            'PaymentRefunded' => 'refunded',
            'PaymentFailed' => 'failed',
            'InvoiceCancelled' => 'failed',
            'PaymentCancelled' => 'failed',
        ];
        $donation_id = $this->_get_donation_by_invoice_id($data['invoice']['id']);
        $donation_status = empty($map_status[ $data['eventType'] ]) ? false : $map_status[ $data['eventType'] ];

        if( !$donation_status ) {
            return false; // Mb, return WP_Error?
        }

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        $donation->status = $map_status[ $data['eventType'] ];

        if($donation->status === 'failed') { // Emails will be sent only if respective options are on
            Leyka_Donation_Management::send_error_notifications($donation);
        }

        // Log webhook response:
        $data_to_log = $data;
        if(leyka_options()->opt('rbk_keep_payment_logs')) {

            $data_to_log = $donation->gateway_response;
            $data_to_log['RBK_Hook_data'] = $data;

        }

        // No emails for non-init recurring donations - the active recurring procedure do mailouts for them:
        if($donation->status === 'funded' && ($donation->type === 'single' || $donation->is_init_recurring_donation)) {
            Leyka_Donation_Management::send_all_emails($donation);
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

        if($donation->type === 'rebill') {
            do_action('leyka_new_rebill_donation_added', $donation);
        }

        return $donation->add_gateway_response($data_to_log);

    }

    protected function _handle_payment_processed($data) {

        // Log the webhook request content:
        $donation_id = $this->_get_donation_by_invoice_id($data['invoice']['id']);
        $donation = Leyka_Donations::get_instance()->get($donation_id);

        $data_to_log = $data;
        if(leyka_options()->opt('rbk_keep_payment_logs')) {

            $data_to_log = $donation->gateway_response;
            $data_to_log['RBK_Hook_processed_data'] = $data;

        }

        $donation->add_gateway_response($data_to_log);

        $donation->rbk_payment_id = $data['payment']['id']; // ATM the invoice ID already saved in the donation

        // Capture the invoice:
        return wp_remote_post(
            self::RBK_API_HOST.self::RBK_API_PATH."/{$data['invoice']['id']}/payments/{$data['payment']['id']}/capture",
            [
                'timeout' => 30,
                'redirection' => 10,
                'blocking' => true,
                'httpversion' => '1.1',
                'headers' => [
                    'X-Request-ID' => uniqid(),
                    'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                    'Content-type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json'
                ],
                'body' => wp_json_encode(['reason' => 'Donation auto capture',])
            ]
        );

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = $donation->gateway_response;
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        $vars = $vars[array_key_last($vars)];

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('Invoice ID:', 'leyka') => $vars['id'],
                __('Operation date:', 'leyka') => gmdate('d.m.Y, H:i:s', strtotime($vars['createdAt'])),
                __('Operation status:', 'leyka') => $vars['status'],
                __('Full donation amount:', 'leyka') => $vars['amount'] / 100,
                __('Donation currency:', 'leyka') => $vars['currency'],
                __('Shop Account:', 'leyka') => $vars['shopID'],
            ],
            $donation
        );

    }

    // The default implementations are in use:
//    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation_Base $donation) { }
//    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) { }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->rbk_invoice_id || !$init_recurring_donation->rbk_payment_id ) {
            return false;
        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'init_recurring_donation' => $init_recurring_donation->id,
                'rbk_invoice_id' => false,
                'rbk_payment_id' => false,
                'date' => '' // don't copy the date
            ],
            ['recalculate_total_amount' => true,]
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        // 1. Create a new invoice:
        $api_request_url = self::RBK_API_HOST.self::RBK_API_PATH;
        $args = [
            'timeout' => 30,
            'redirection' => 10,
            'blocking' => true,
            'httpversion' => '1.1',
            'headers' => [
                'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                'Cache-Control' => 'no-cache',
                'Content-type' => 'application/json; charset=utf-8',
                'X-Request-ID' => uniqid(),
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'shopID' => leyka_options()->opt('leyka_rbk_shop_id'),
                'dueDate' => gmdate( 'Y-m-d\TH:i:s\Z', strtotime('+2 minute', current_time('timestamp', 1)) ),
                'amount' => 100 * (int)$new_recurring_donation->amount, // Amount in minor currency units. Must be int
                'currency' => 'RUB',
                /* translators: %s: Payment title. */
                'product' => sprintf(__('%s - non-initial recurring donation','leyka'), $new_recurring_donation->payment_title),
                'metadata' => ['donation_id' => $new_recurring_donation->id,],
            ])
        ];

        if(leyka_options()->opt('rbk_keep_payment_logs')) {
            $this->_rbk_log['RBK_Request'] = ['url' => $api_request_url, 'params' => $args,];
        }

        $this->_rbk_response = json_decode(wp_remote_retrieve_body(wp_remote_post($api_request_url, $args)), true);

        if(empty($this->_rbk_response['invoice']['id']) || empty($this->_rbk_response['invoiceAccessToken']['payload'])) {

            $new_recurring_donation->add_gateway_response($this->_rbk_response);
            return false;

        }

        $new_recurring_donation->rbk_invoice_id = $this->_rbk_response['invoice']['id'];

        // 2. Create a payment for the invoice:
        $api_request_url = self::RBK_API_HOST.self::RBK_API_PATH."/{$this->_rbk_response['invoice']['id']}/payments";
        $args = [
            'timeout' => 30,
            'redirection' => 10,
            'blocking' => true,
            'httpversion' => '1.1',
            'headers' => [
                'Authorization' => 'Bearer '.$this->_rbk_response['invoiceAccessToken']['payload'],
                'Cache-Control' => 'no-cache',
                'Content-type' => 'application/json; charset=utf-8',
                'X-Request-ID' => uniqid(),
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'flow' => ['type' => 'PaymentFlowInstant',],
                'payer' => [
                    'payerType' => 'RecurrentPayer',
                    'recurrentParentPayment' => [
                        'invoiceID' => $init_recurring_donation->rbk_invoice_id,
                        'paymentID' => $init_recurring_donation->rbk_payment_id,
                    ],
                    'contactInfo' => ['email' => $new_recurring_donation->donor_email,],
                ]
            ])
        ];

        if(leyka_options()->opt('rbk_keep_payment_logs')) {
            $this->_rbk_log['RBK_Request'] = ['url' => $api_request_url, 'params' => $args,];
        }

        $this->_rbk_response = json_decode(wp_remote_retrieve_body(wp_remote_post($api_request_url, $args)), true);

        if(leyka_options()->opt('rbk_keep_payment_logs')) {
            $this->_rbk_log['RBK_Request_processed_data'] = $this->_rbk_response;
        }

        // Save the gateway response finally:
        if(leyka_options()->opt('rbk_keep_payment_logs')) {

            $gateway_response = $new_recurring_donation->gateway_response;
            $new_recurring_donation->add_gateway_response(array_merge(
                $gateway_response ? (array)$gateway_response : [], $this->_rbk_log
            ));

        } else {
            $new_recurring_donation->add_gateway_response($this->_rbk_response);
        }

        return $new_recurring_donation;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php esc_html_e('RBK Money invoice ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>
                <input type="text" id="rbk-invoice-id" name="rbk-invoice-id" placeholder="<?php esc_attr_e('Enter RBK Money invoice ID', 'leyka');?>" value="<?php echo esc_attr( $donation->rbk_invoice_id );?>">
            <?php } else {?>
                <span class="fake-input"><?php echo esc_html( $donation->rbk_invoice_id );?></span>
            <?php }?>
            </div>

            <label><?php esc_html_e('RBK Money payment ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>
                <input type="text" id="rbk-payment-id" name="rbk-payment-id" placeholder="<?php esc_attr_e('Enter RBK Money payment ID', 'leyka');?>" value="<?php echo esc_attr( $donation->rbk_payment_id );?>">
            <?php } else {?>
                <span class="fake-input"><?php echo esc_html( $donation->rbk_payment_id );?></span>
            <?php }?>
            </div>

            <?php if($donation->type === 'rebill') {

                $init_recurring_donation = $donation->init_recurring_donation; ?>

                <div class="recurring-is-active-field">
                    <label for="rbk-recurring-is-active"><?php esc_html_e('Recurring subscription is active', 'leyka'); ?>:</label>
                    <div class="leyka-ddata-field">
                        <input type="checkbox" id="rbk-recurring-is-active" name="rbk-recurring-is-active" value="1" <?php echo wp_kses_post( $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '' ); ?>>
                    </div>
                </div>

                <?php if( !$donation->is_init_recurring_donation) {?>

                <label><?php esc_html_e('Initial recurring invoice ID', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <?php if($donation->type === 'correction') {?>
                    <input type="text" id="rbk-init-invoice-id" name="rbk-init-invoice-id"
                           placeholder="<?php esc_attr_e('Enter RBK Money initial recurring invoice ID', 'leyka');?>"
                           value="<?php echo esc_attr( $init_recurring_donation->rbk_invoice_id ); ?>">
                    <?php } else {?>
                    <span class="fake-input"><?php echo esc_html( $init_recurring_donation->rbk_invoice_id );?></span>
                    <?php }?>
                </div>

                <br>

                <label><?php esc_html_e('Initial recurring payment ID', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <?php if($donation->type === 'correction') {?>
                        <input type="text" id="rbk-init-payment-id" name="rbk-init-payment-id"
                               placeholder="<?php esc_attr_e('Enter RBK Money initial recurring payment ID', 'leyka'); ?>"
                               value="<?php echo esc_attr( $init_recurring_donation->rbk_payment_id ); ?>">
                    <?php } else {?>
                    <span class="fake-input"><?php echo esc_html( $init_recurring_donation->rbk_payment_id ); ?></span>
                    <?php }?>
                </div>

            <?php }

            }

        } else { // New donation page displayed ?>

        <label for="rbk-invoice-id"><?php esc_html_e('RBK Money invoice ID', 'leyka');?>:</label>
        <div class="leyka-ddata-field">
            <input type="text" id="rbk-invoice-id" name="rbk-invoice-id" placeholder="<?php esc_attr_e('Enter RBK Money invoice ID', 'leyka');?>" value="">
        </div>

        <label for="rbk-payment-id"><?php esc_html_e('RBK Money payment ID', 'leyka');?>:</label>
        <div class="leyka-ddata-field">
            <input type="text" id="rbk-payment-id" name="rbk-payment-id" placeholder="<?php esc_attr_e('Enter RBK Money payment ID', 'leyka');?>" value="">
        </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'invoice_id':
            case 'rbk_invoice_id':
                return $donation->get_meta('rbk_invoice_id');
            case 'payment_id':
            case 'rbk_payment_id':
                return $donation->get_meta('rbk_payment_id');
            default:
                return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'invoice_id':
            case 'rbk_invoice_id':
                return $donation->set_meta('rbk_invoice_id', $value);
            case 'payment_id':
            case 'rbk_payment_id':
                return $donation->set_meta('rbk_payment_id', $value);
            default:
                return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['rbk-invoice-id']) && $donation->rbk_invoice_id != $_POST['rbk-invoice-id']) {
            $donation->rbk_invoice_id = $_POST['rbk-invoice-id'];
        }

        if(isset($_POST['rbk-payment-id']) && $donation->rbk_payment_id != $_POST['rbk-payment-id']) {
            $donation->rbk_payment_id = $_POST['rbk-payment-id'];
        }

        $donation->recurring_is_active = !empty($_POST['rbk-recurring-is-active']);

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['rbk_invoice_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'rbk_invoice_id', $params['rbk_invoice_id']);
        }

        if( !empty($params['rbk_payment_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'rbk_payment_id', $params['rbk_payment_id']);
        }

    }

}


class Leyka_Rbk_Card extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'bankcard';
        $this->_gateway_id = 'rbk';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Bank card (RBK Money)', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_' . $this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'active';
    }

}

function leyka_add_gateway_rbk() {
    leyka_add_gateway(Leyka_Rbk_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_rbk');