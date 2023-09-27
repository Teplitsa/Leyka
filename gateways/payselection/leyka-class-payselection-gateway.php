<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Payselection_Gateway class
 */

class Leyka_Payselection_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_ps_method;

    protected function __construct() {

        parent::__construct();
        require_once LEYKA_PLUGIN_DIR.'gateways/payselection/lib/Payselection_Merchant_Api.php';
        $this->_ps_method = empty(leyka_options()->opt('payselection_method')) ||  leyka_options()->opt('payselection_method') !== 'redirect' ? 'widget' : 'redirect';
    }

    protected function _set_attributes() {

        $this->_id = 'payselection';
        $this->_title = __('Payselection', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="https://payselection.com/">Payselection</a> is a universal payment solution for online business.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-payselection/';
        $this->_registration_link = 'https://merchant.payselection.com/login/';
        $this->_has_wizard = false;

        $this->_min_commission = 2.8;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = true;
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'payselection_method' => [
                'type' => 'select',
                'title' => __('Widget or Redirect', 'leyka'),
                'default' => 'widget',
                'list_entries' => [
                    'widget' => __('Widget', 'leyka'),
                    'redirect' => __('Redirect', 'leyka'),
                ],
            ],
            'payselection_webhook' => [
                'type' => 'static_text',
                'title' => __('Webhook URL', 'leyka'),
                'value' => home_url('/leyka/service/payselection/response'),
            ],
            'payselection_host' => [
                'type' => 'text',
                'title' => __('API host', 'leyka'),
                'comment' => __('API hostname.', 'leyka'),
                'required' => true,
                'default' => 'https://gw.payselection.com',
            ],
            'payselection_create_host' => [
                'type' => 'text',
                'title' => __('Create Payment host', 'leyka'),
                'comment' => __('Hostname for create payment.', 'leyka'),
                'required' => true,
                'default' => 'https://webform.payselection.com',
            ],
            'payselection_site_id' => [
                'type' => 'text',
                'title' => __('Site ID', 'leyka'),
                'comment' => __('Your site ID on Payselection.', 'leyka'),
                'required' => true,
                'default' => '',
            ],
            'payselection_key' => [
                'type' => 'text',
                'title' => __('Secret Key', 'leyka'),
                'comment' => __('Your Key on Payselection.', 'leyka'),
                'is_password' => true,
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'tMZZQyyzY4NV9Cft'),
            ],
            'payselection_widget_url' => [
                'type' => 'text',
                'title' => __('Widget URL', 'leyka'),
                'default' => 'https://widget.payselection.com/lib/pay-widget.js',
                'required' => true,
            ],
            'payselection_widget_key' => [
                'type' => 'text',
                'title' => __('Public Key', 'leyka'),
                'comment' => __('Your Widget Key on Payselection.', 'leyka'),
                'required' => true,
                'default' => '',
            ],
            'payselection_language' => [
                'type' => 'select',
                'title' => __('Language', 'leyka'),
                'default' => 'en',
                'list_entries' => [
                    'ru' => __('Russian', 'leyka'),
                    'en' => __('English', 'leyka'),
                ],
            ],
            'payselection_receipt' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Fiscalization', 'leyka'),
                'comment' => __('If this option is enabled order receipts will be created and sent to your customer and to the revenue service via Payselection.', 'leyka'),
                'short_format' => true,
            ],
            'site_ip' => [
                'type' => 'static_text',
                'title' => __('Site IP', 'leyka'),
                'is_html' => true,
                'value' => $this->get_site_ip_content(),
            ],
        ];
    }

    public function get_site_ip_content() {

        $response = wp_remote_get('https://api.ipify.org/');

        if ( is_array($response) && !is_wp_error($response) ) {
            return '<div>'.$response['body'].'</div>';
        }

        return false;

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('payselection_host')
            && leyka_options()->opt('payselection_create_host')
            && leyka_options()->opt('payselection_site_id')
            && leyka_options()->opt('payselection_key');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Payselection_Card::get_instance();
        }
    }

    public function localize_js_strings(array $js_data) {
        return array_merge($js_data, [
            'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
            'payselection_not_set_up' => __('Error in Payselection settings. Please report to the website tech support.', 'leyka'),
            'payselection_error' => __('Payselection Error:', 'leyka'). ' ',
        ]);
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Payselection_Card::get_instance()->active) {

            $leyka_main_js_handle = wp_script_is('leyka-public') ? 'leyka-public' : 'leyka-new-templates-public';
            $leyka_widget_js_handle = 'widget' === $this->_ps_method ? $leyka_main_js_handle.' leyka-payselection-widget' : $leyka_main_js_handle;

            if ('widget' === $this->_ps_method) {
                wp_enqueue_script('leyka-payselection-widget', leyka_options()->opt('payselection_widget_url'), [], false, true);
            }
            wp_enqueue_script(
                'leyka-payselection',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Payselection_Gateway::get_instance()->id.'/js/leyka.payselection.js',
                ['jquery', $leyka_main_js_handle],
                LEYKA_VERSION.'.001',
                true
            );

        }

        add_filter('leyka_js_localized_strings', [$this, 'localize_js_strings']);

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
        }
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return ''; // Payselection use custom redirection url on payment via ajax
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return ['status' => 1, 'message' => $donation_id->get_error_message()];
        } else if( !$donation_id ) {
            return ['status' => 1, 'message' => __('The donation was not created due to error.', 'leyka')];
        } else if( !leyka_options()->opt('payselection_site_id') ) {
            return [
                'status' => 1,
                'message' => __('Error in Payselection settings. Please report to the website tech support.', 'leyka')
            ];
        } 

        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $currency = !empty($_POST['leyka_donation_currency']) ?
            strtoupper($_POST['leyka_donation_currency']) : strtoupper($this->get_supported_currencies()[0]);

        $response = [
            'payselection_method' => $this->_ps_method,
            'site_id' => trim(leyka_options()->opt('payselection_site_id')),
            'widget_key' => trim(leyka_options()->opt('payselection_widget_key')),
            'donation_id' => $donation_id,
            'amount' => number_format(floatval($donation->amount), 2, '.', ''),
            'currency' => $currency,
            'payment_title' => esc_html($donation->payment_title),
            'success_page' => leyka_get_success_page_url(),
            'failure_page' => leyka_get_failure_page_url(),
        ];

        $extraData = [
            'WebhookUrl'    => home_url('/leyka/service/payselection/response'),
            'SuccessUrl'    => leyka_get_success_page_url(),
            'CancelUrl'     => esc_url($campaign->url),
            'DeclineUrl'    => esc_url($campaign->url),
            'FailUrl'       => leyka_get_failure_page_url(),
        ];

        $response['request'] = [
            'MetaData' => [
                'PaymentType' => 'Pay',
            ],
            'PaymentRequest' => [
                'OrderId' => implode('-',[$donation_id, leyka_options()->opt('payselection_site_id'), time()]),
                'Amount' => number_format(floatval($donation->amount), 2, '.', ''),
                'Currency' => $currency,
                'Description' => leyka_get_donation_gateway_description($donation, 250),
                'PaymentMethod' => 'Card',
                'RebillFlag' => !empty($form_data['leyka_recurring']) ? true : false,
                'ExtraData' => $extraData,
            ],
            'CustomerInfo' => [
                'Email' => $donation->donor_email,
                'Language' => !empty(leyka_options()->opt('payselection_language')) ? leyka_options()->opt('payselection_language') : 'en',
                'Name' => $donation->donor_name,
            ],
        ];

        if (leyka_options()->opt('payselection_receipt')) {
            $response['request']['ReceiptData'] = $this->_get_payselection_receipt($donation, __('Donation', 'leyka'));
        }

        $api = new \Payselection_Merchant_Api(
            leyka_options()->opt('payselection_site_id'),
            leyka_options()->opt('payselection_key'),
            leyka_options()->opt('payselection_host'),
            leyka_options()->opt('payselection_create_host')
        );
        $payment_create_request = $api->getPaymentLink($response['request']);
        $response['payselection_redirect_url'] = !is_wp_error($payment_create_request) ? $payment_create_request : '';
        $response['payselection_redirect_error'] = is_wp_error($payment_create_request) ? $payment_create_request->get_error_message() : '';

        if ('widget' === $this->_ps_method) {
            $response['request']['MetaData']['Initiator']  = 'Widget';
        }

        return $response;

    }

    protected function _handle_callback_error($response, $error_message = '', Leyka_Donation_Base $donation = null) {

        if($donation) {

            $error_code = '';
    
            if (is_wp_error($response)) {
                $error_response['failure_reason'] = $error_message;
                $donation->add_gateway_response($error_response);
                $error_code = $response->get_error_code();
            } else {
                $response['failure_reason'] = $error_message;
                $donation->add_gateway_response($response);
            }

            $donation->status = 'failed';
    
            if ($donation->type === 'rebill' && 
                ($donation->is_init_recurring_donation || $error_code === 'RecurrentInactiveError')) {

                $donation->recurring_is_active = false;
            }
    
        }
    
    }

    public function _handle_service_calls($call_type = '') {
        // Callback URLs are: some-website.org/leyka/service/payselection/response/
        // Request content should contain "Event" field.
        // Possible field values: Payment, Fail, Refund

        $data = file_get_contents('php://input');

        $check = \Payselection_Merchant_Api::verify_header_signature($data, leyka_options()->opt('payselection_site_id'), leyka_options()->opt('payselection_key'));

        $response = [];
        try {
            $response = json_decode($data, true);

        } catch(\Exception $ex) {
            error_log($ex);
        }

        $donation_string = explode('-', $response['OrderId']);

        $donation = Leyka_Donations::get_instance()->get_donation((int)$donation_string[0]);

        if(empty($response['Event']) || !is_string($response['Event'])) {
            $this->_handle_callback_error($response, __('Webhook error: Event field is not found or have incorrect value', 'leyka'), $donation);
            wp_die(__('Webhook error: Event field is not found or have incorrect value', 'leyka'));
        }

        if (is_wp_error($check)) {

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message = sprintf(__('This message has been sent because %s The details of the call are below:', 'leyka'), $check->get_error_message())."\n\r\n\r"
                .esc_html($check->get_error_message())."\n\r\n\r"
                ."POST:\n\r".print_r($_POST, true)."\n\r\n\r"
                ."GET:\n\r".print_r($_GET, true)."\n\r\n\r"
                ."SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";

                wp_mail(
                    leyka_get_website_tech_support_email(),
                    __('Payselection callback error.', 'leyka'),
                    $message
                );

            }

            $this->_handle_callback_error($response, $check->get_error_message(), $donation);

            die();

        } 

        if( !$donation ) {

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message = __("This message has been sent because a call to your Payselection callbacks URL was made with a donation ID parameter that Leyka is unknown of. The details of the call are below.", 'leyka')."\n\r\n\r";

                $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
                $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
                $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";
                $message .= "Donation ID: ".$_POST['cs2']."\n\r\n\r";

                wp_mail(
                    leyka_get_website_tech_support_email(),
                    __('Payselection gives unknown donation ID parameter!', 'leyka'),
                    $message
                );

            }

            die();

        }

        switch($response['Event']) {
            case 'Fail': 
                $new_status = 'failed'; 
                // Emails will be sent only if respective options are on:
                Leyka_Donation_Management::send_error_notifications($donation);
                break;
            case 'Payment': 
                $new_status = 'funded'; 
                
                if ($donation->type === 'rebill') {
                    if (!empty($response['RebillId'])) {
                        if (empty($donation->payselection_recurring_id) || !$donation->recurring_is_active) {
                            $donation->recurring_is_active = true;
                        }
                    } else {
                        if ($donation->is_init_recurring_donation) {
                            $donation->type = 'single';
                        }
                    }
                }
                break;
            case 'Refund': 
                $new_status = 'refunded'; 
                break;
            default:
        }

        $donation->add_gateway_response($response);

        if (!empty($response['TransactionId'])) {
            $donation->payselection_transaction_id = esc_sql($response['TransactionId']);
        }

        if (!empty($response['RebillId'])) {
            $donation->payselection_recurring_id = esc_sql($response['RebillId']);
        }

        if (!empty($new_status) && $donation->status !== $new_status) {

            $donation->status = $new_status;

            if($donation->status === 'funded') {
                Leyka_Donation_Management::send_all_emails($donation);
            }

        }

        $this->_handle_ga_purchase_event($donation);

        if ($donation->type === 'rebill') {
            do_action('leyka_new_rebill_donation_added', $donation);
        }

        exit(200);

    }

    protected function _handle_ga_purchase_event(Leyka_Donation_Base $donation) {

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

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->payselection_recurring_id) {
            return false;
        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'amount_total' => 'auto',
                'init_recurring_donation' => $init_recurring_donation->id,
                'date' => '' // don't copy the date
            ],
            ['recalculate_total_amount' => true,]
        );

        $new_recurring_donation->payselection_recurring_id = esc_sql($init_recurring_donation->payselection_recurring_id);

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $api = new \Payselection_Merchant_Api(
            leyka_options()->opt('payselection_site_id'),
            leyka_options()->opt('payselection_key'),
            leyka_options()->opt('payselection_host'),
            leyka_options()->opt('payselection_create_host')
        );
        $data = [
            'OrderId' => implode('-',[$new_recurring_donation->id, leyka_options()->opt('payselection_site_id'), time()]),
            'Amount' => number_format(floatval($new_recurring_donation->amount), 2, '.', ''),
            'Currency' => $new_recurring_donation->currency,
            'RebillId' => $new_recurring_donation->payselection_recurring_id,
            'PayOnlyFlag' => true,
            'WebhookUrl' => home_url('/leyka/service/payselection/response'),
            
        ];
        
        if (leyka_options()->opt('payselection_receipt')) {
            $data['ReceiptData'] = $this->_get_payselection_receipt($new_recurring_donation, __('Donation rebill', 'leyka'));
        }

        $response = $api->rebill($data);

        if (is_wp_error($response)) {
            $this->_handle_callback_error($response, sprintf(__("Rebilling request to the Payselection couldn't be made due to some error.\n\nThe error: %s", 'leyka'), $response->get_error_message()), $new_recurring_donation);
            Leyka_Donation_Management::send_error_notifications($new_recurring_donation); // Emails will be sent only if respective options are on
            return false;
        }

        $new_recurring_donation->add_gateway_response($response);

        return $new_recurring_donation;

    }

    public function cancel_recurring_subscription(Leyka_Donation_Base $donation) {

        if( !$donation->recurring_is_active ) {
            return true;
        }

        if($donation->type !== 'rebill') {
            return new WP_Error(
                'wrong_recurring_donation_to_cancel',
                __('Wrong donation given to cancel a recurring subscription.', 'leyka')
            );
        }

        if( !$donation->payselection_recurring_id ) {
            return new WP_Error('payselection_no_subscription_id', sprintf(__('<strong>Error:</strong> unknown Subscription ID for donation #%d. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>We are very sorry for inconvenience.', 'leyka'), $donation->id, leyka_get_website_tech_support_email()));
        }

        $api = new \Payselection_Merchant_Api(
            leyka_options()->opt('payselection_site_id'),
            leyka_options()->opt('payselection_key'),
            leyka_options()->opt('payselection_host'),
            leyka_options()->opt('payselection_create_host')
        );
        $response = $api->unsubscribe(['RebillId' => $donation->payselection_recurring_id]);
        if (is_wp_error($response)) {
            if ($response->get_error_code() === 'RecurrentStatusError') {
                $donation->recurring_is_active = false;
                return new WP_Error(
                    'payselection_error_cancel_subscription',
                    sprintf(__('The recurring subsciption cancelling request returned error: %s', 'leyka'), $response->get_error_message())
                );
            }
            return new WP_Error(
                'payselection_error_cancel_subscription',
                sprintf(__('The recurring subsciption cancelling request returned unexpected result. We cannot cancel the recurring subscription automatically. Error: %s', 'leyka'), $response->get_error_code())
            );
        }

        if ($response['TransactionState'] === 'false' && $response['Error']['Code'] !== 'RecurrentStatusError') {
            return new WP_Error(
                'payselection_error_false_subscription',
                sprintf(__('The recurring subsciption cancelling request returned unexpected result. We cannot cancel the recurring subscription automatically. Error: %s', 'leyka'), $response['Error']['Description']())
            );
        }
        $donation->recurring_is_active = false;

        return true;

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) { 
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        $vars_final = [
            __('Event:', 'leyka') => $this->_get_value_if_any($vars, 'Event'),
            __('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'TransactionId'),
            __('Amount:', 'leyka') => $this->_get_value_if_any($vars, 'Amount'),
            __('Currency:', 'leyka') => $this->_get_value_if_any($vars, 'Currency'),
            __('Callback time:', 'leyka') => $this->_get_value_if_any($vars, 'DateTime'),
            __('Donation description:', 'leyka') => $this->_get_value_if_any($vars, 'Description'),
        ];

        if ($donation->status === 'refund' && !empty($vars['RemainingAmount'])) {
            $vars_final[__('Remaining amount:', 'leyka')] = $vars['RemainingAmount'];
        }

        if ($donation->status === 'failed' && !empty($vars['ErrorMessage'])) {
            $vars_final[__('Donation failure reason(Payselection):', 'leyka')] = $this->_get_value_if_any($vars, 'ErrorMessage');
        }

        if ($donation->status === 'failed' && !empty($vars['failure_reason'])) {
            $vars_final[__('Donation failure reason(Leyka):', 'leyka')] = $this->_get_value_if_any($vars, 'failure_reason');
        }

        if ($donation->type === 'rebill' && !empty($vars['RebillId'])) {
            $vars_final[__('Recurrent subscription ID:', 'leyka')] = $this->_get_value_if_any($vars, 'RebillId');
        }

        return $vars_final;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <div class="leyka-ddata-string">

                <label><?php _e('Payselection transaction ID', 'leyka');?>:</label>

                <div class="leyka-ddata-field">

                    <?php if($donation->type === 'correction') {?>
                        <input type="text" id="payselection-transaction-id" name="payselection-transaction-id" placeholder="<?php _e('Enter Payselection transaction ID', 'leyka');?>" value="<?php echo $donation->payselection_transaction_id;?>">
                    <?php } else {?>
                        <span class="fake-input"><?php echo $donation->payselection_transaction_id;?></span>
                    <?php }?>
                </div>

            </div>


            <?php if($donation->type !== 'rebill') {
                return;
            }?>

            <div class="leyka-ddata-string">

                <label><?php _e('Payselection subscription ID', 'leyka');?>:</label>

                <div class="leyka-ddata-field">

                    <?php if($donation->type === 'correction') {?>
                        <input type="text" id="payselection-recurring-id" name="payselection-recurring-id" placeholder="<?php _e('Enter Payselection subscription ID', 'leyka');?>" value="<?php echo $donation->payselection_recurring_id;?>">
                    <?php } else {?>
                        <span class="fake-input"><?php echo $donation->payselection_recurring_id;?></span>
                    <?php }?>
                </div>

            </div>

            <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">

                <label><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <?php echo $init_recurring_donation->recurring_is_active ? __('yes', 'leyka') : __('no', 'leyka');

                    if( !$init_recurring_donation->recurring_is_active && $init_recurring_donation->recurring_cancel_date ) {
                    echo ' ('.sprintf(__('canceled on %s', 'leyka'), date(get_option('date_format').', '.get_option('time_format'), $init_recurring_donation->recurring_cancel_date)).')';
                    }?>
                </div>

            </div>

        <?php } else { // New donation page displayed ?>

            <label for="payselection-transaction-id"><?php _e('Payselection transaction ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="payselection-transaction-id" name="payselection-transaction-id" placeholder="<?php _e('Enter Payselection transaction ID', 'leyka');?>" value="">
            </div>

            <label for="payselection-recurring-id"><?php _e('Payselection subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="payselection-recurring-id" name="payselection-recurring-id" placeholder="<?php _e('Enter Payselection subscription ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'payselection_recurring_id':
                return $donation->get_meta('payselection_recurring_id');
            case 'payselection_transaction_id':
                return $donation->get_meta('payselection_transaction_id');
            default:
                return false;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'payselection_recurring_id':
                return $donation->set_meta('payselection_recurring_id', $value);
            case 'payselection_transaction_id':
                return $donation->set_meta('payselection_transaction_id', $value);
            default:
                return false;
        }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['payselection-recurring-id']) && $donation->payselection_recurring_id != $_POST['payselection-recurring-id']) {
            $donation->payselection_recurring_id = $_POST['payselection-recurring-id'];
        }

        if(isset($_POST['payselection-transaction-id']) && $donation->payselection_transaction_id != $_POST['payselection-transaction-id']) {
            $donation->payselection_transaction_id = $_POST['payselection-transaction-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['payselection_recurring_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'payselection_recurring_id', $params['payselection_recurring_id']);
        }

        if( !empty($params['payselection_transaction_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'payselection_transaction_id', $params['payselection_transaction_id']);
        }

    }

    protected function _get_payselection_receipt(Leyka_Donation_Base $donation, $title = '') {
        return [
            'timestamp' => date('d.m.Y H:i:s'),
            'external_id' => implode('-',[$donation->id, time()]),
            'receipt' => [
                'client' => [
                    'name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                ],
                'company' => [
                    'inn' => leyka_options()->opt('leyka_org_inn'),
                    'payment_address' => leyka_options()->opt('leyka_org_address'),
                ],
                'items' => [
                    [
                        'name' => $title,
                        'price' => (float) number_format(floatval($donation->amount), 2, '.', ''),
                        'quantity' => 1,
                        'sum' => (float) number_format(floatval($donation->amount), 2, '.', ''),
                        'payment_method' => 'full_prepayment',
                        'payment_object'=> 'commodity',
                        'vat' => [
                            'type' => 'none',
                        ]
                    ]
                ],
                'payments' => [
                    [
                        'type' => 1,
                        'sum' => (float) number_format(floatval($donation->amount), 2, '.', ''),
                    ]
                ],
                'total' => (float) number_format(floatval($donation->amount), 2, '.', ''),
            ],
        ];
    }

}

class Leyka_Payselection_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'payselection';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="https://payselection.com/">Payselection</a> is a universal payment solution for online business.', 'leyka'),
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
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ]);

        $this->_supported_currencies = ['rub', 'eur', 'usd', 'kgs'];
        $this->_default_currency = 'rub';

        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

function leyka_add_gateway_payselection() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Payselection_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_payselection');
