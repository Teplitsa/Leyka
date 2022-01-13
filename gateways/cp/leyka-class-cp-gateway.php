<?php if( !defined('WPINC') ) die;
/**
 * Leyka_CP_Gateway class
 */

class Leyka_CP_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'cp';
        $this->_title = __('CloudPayments', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="//cloudpayments.ru/">CloudPayments</a> is a Designer IT-solutions for the e-commerce market. Every partner receives the most comprehensive set of key technical options allowing to create a customer-centric payment system on site or in mobile application. Partners are allowed to receive payments in roubles and in other world currencies.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-cloudpayments/';
        $this->_registration_link = '//cloudpayments.ru/connection';
        $this->_has_wizard = true;

        $this->_min_commission = 2.8;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'cp_public_id' => [
                'type' => 'text',
                'title' => __('Public ID', 'leyka'),
                'comment' => __('Please, enter your CloudPayments public ID here. It can be found in your CloudPayments control panel.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'pk_c5fcan980a7c38418932y476g4931'),
            ],
            'cp_api_secret' => [
                'type' => 'text',
                'title' => __('API password', 'leyka'),
                'comment' => __('Please, enter your CloudPayments API password. It can be found in your CloudPayments control panel.', 'leyka'),
                'is_password' => true,
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '26128731fgc9fbdjc6c210dkbn5q14eu'),
            ],
            'cp_ip' => [
                'type' => 'text',
                'title' => __('CloudPayments IP', 'leyka'),
                'comment' => __('Comma-separated callback requests IP list. Leave empty to disable the check.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '130.193.70.192, 185.98.85.109, 87.251.91.160/27, 185.98.81.0/28'),
                'default' => '130.193.70.192, 185.98.85.109, 87.251.91.160/27, 185.98.81.0/28',
            ],
        ];

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('cp_public_id') && leyka_options()->opt('cp_api_secret');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_CP_Card::get_instance();
        }
    }

    public function localize_js_strings(array $js_data) {
        return array_merge($js_data, [
            'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
            'cp_not_set_up' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka'),
            'cp_donation_failure_reasons' => [
                'User has cancelled' => __('You cancelled the payment', 'leyka'),
            ],
        ]);
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_CP_Card::get_instance()->active) {

            $leyka_main_js_handle = wp_script_is('leyka-public') ? 'leyka-public' : 'leyka-new-templates-public';

            wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments.js', [], false, true);
            wp_enqueue_script(
                'leyka-cp',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_CP_Gateway::get_instance()->id.'/js/leyka.cp.js',
                ['jquery', 'leyka-cp-widget', $leyka_main_js_handle,],
                LEYKA_VERSION.'.001',
                true
            );

        }

        add_filter('leyka_js_localized_strings', [$this, 'localize_js_strings']);

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
        }

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return ''; // CP doesn't use redirection on payment
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return ['status' => 1, 'message' => $donation_id->get_error_message()];
        } else if( !$donation_id ) {
            return ['status' => 1, 'message' => __('The donation was not created due to error.', 'leyka')];
        } else if( !leyka_options()->opt('cp_public_id') ) {
            return [
                'status' => 1,
                'message' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka')
            ];
        }

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $cp_currency = 'RUB';
        switch($_POST['leyka_donation_currency']) {
            case 'usd': $cp_currency = 'USD'; break;
            case 'eur': $cp_currency = 'EUR'; break;
            default:
        }

        return [
            'public_id' => trim(leyka_options()->opt('cp_public_id')),
            'donation_id' => $donation_id,
            'amount' => number_format(floatval($donation->amount), 2, '.', ''),
            'currency' => $cp_currency,
            'payment_title' => $donation->payment_title,
            'name' => $donation->donor_name,
            'donor_email' => $donation->donor_email,
            'success_page' => leyka_get_success_page_url(),
            'failure_page' => leyka_get_failure_page_url(),
        ];

    }

    /* Check if callback is sent from correct IP. */
    protected function _is_callback_caller_correct() {

        if( !leyka_options()->opt('cp_ip') ) { // The caller IP check is off
            return true;
        }

        $cp_ips_allowed = array_map(
            function($ip) { return trim(stripslashes($ip)); },
            explode(',', leyka_options()->opt('cp_ip'))
        );

        if( !$cp_ips_allowed ) {
            return true;
        }

        $client_ip = leyka_get_client_ip();

        foreach($cp_ips_allowed as $ip_or_cidr) {

            if( // Check if caller IP is in CIDR range
                strpos($ip_or_cidr, '/')
                && (is_ip_in_range($_SERVER['REMOTE_ADDR'], $ip_or_cidr) || is_ip_in_range($client_ip, $ip_or_cidr))
            ) {
                return true;
            } else if($client_ip == $ip_or_cidr) { // Simple IP check
                return true;
            }

        }

        return false;

    }

    public function _handle_service_calls($call_type = '') {

        // Test for gateway's IP:
        if( !$this->_is_callback_caller_correct() ) {

            $client_ip = leyka_get_client_ip();

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message = __("This message has been sent because a call to your CloudPayments function was made from an IP that did not match with the one in your CloudPayments gateway setting. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r".
                    "POST:\n\r".print_r($_POST, true)."\n\r\n\r".
                    "GET:\n\r".print_r($_GET, true)."\n\r\n\r".
                    "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r".
                    "IP:\n\r".print_r($client_ip, true)."\n\r\n\r".
                    "CloudPayments IP setting value:\n\r".print_r(leyka_options()->opt('cp_ip'),true)."\n\r\n\r";

                wp_mail(leyka_get_website_tech_support_email(), __('CloudPayments IP check failed!', 'leyka'), $message);

            }

            status_header(200);
            die(json_encode([
                'code' => '13',
                'reason' => sprintf(
                    'Unknown callback sender IP: %s (IPs permitted: %s)',
                    $client_ip, str_replace(',', ', ', leyka_options()->opt('cp_ip'))
                )
            ]));

        }

        switch($call_type) {

            case 'check': // Check if payment is correct

                // InvoiceId - leyka donation ID, SubscriptionId - CP recurring subscription ID:
                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
                    die(json_encode(['code' => '10',]));
                }

                if(empty($_POST['Amount']) || (float)$_POST['Amount'] <= 0 || empty($_POST['Currency'])) {
                    die(json_encode([
                        'code' => '11',
                        'reason' => sprintf(
                            __('Amount or Currency in POST are empty. Amount: %s, Currency: %s', 'leyka'),
                            $_POST['Amount'], $_POST['Currency']
                        )
                    ]));
                }

                // Single or init recurring donation:
                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation

                    $init_recurring_donation = $this->get_init_recurring_donation($_POST['SubscriptionId']);

                    if( !$init_recurring_donation || !$init_recurring_donation->id || is_wp_error($init_recurring_donation) ) {
                        die(json_encode([
                            'code' => '11',
                            'reason' => sprintf(
                                __('Init recurring payment is not found. POST SubscriptionId: %s', 'leyka'),
                                $_POST['SubscriptionId']
                            )
                        ]));
                    }

                } else if($_POST['InvoiceId'] !== 'leyka-test-donation') {

                    $donation = Leyka_Donations::get_instance()->get_donation(absint($_POST['InvoiceId']));
                    $donation->add_gateway_response($_POST);

                    $_POST['Currency'] = mb_strtoupper($_POST['Currency']);

                    if($donation->sum != $_POST['Amount'] || mb_strtoupper($donation->currency_id) != $_POST['Currency']) {
                        die(json_encode([
                            'code' => '11',
                            'reason' => sprintf(
                                __('Amount of original data and POST are mismatching. Orig.: %.2f %s, POST: %.2f %s', 'leyka'),
                                $donation->sum, $donation->currency_id, $_POST['Amount'], $_POST['Currency']
                            )
                        ]));
                    }

                    if( !empty($_POST['TransactionId']) ) { // Unique transaction ID in the CP system
                        $donation->cp_transaction_id = $_POST['TransactionId'];
                    }

                }

                die(json_encode(['code' => '0',])); // Payment check passed

            case 'complete':
            case 'fail':

                // InvoiceId - Leyka donation ID, SubscriptionId - CP recurring subscription ID
                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
                    die(json_encode(['code' => '0',]));
                }

                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation

                    $donation = $this->_get_donation_by_transaction_id($_POST['TransactionId']);

                    if( !$donation || !$donation->id || is_wp_error($donation) ) {
                        /** @todo Send some email to the admin */
                        die(json_encode(['code' => '0',]));
                    }

                    $init_recurring_donation = $this->get_init_recurring_donation($_POST['SubscriptionId']);

                    if( !$init_recurring_donation || !$init_recurring_donation->id || is_wp_error($init_recurring_donation) ) {

                        $donation->payment_type = 'rebill';
                        $donation->status = 'failed';
                        $donation->add_gateway_response($_POST);

                        // Emails will be sent only if respective options are on:
                        Leyka_Donation_Management::send_error_notifications($donation);

                        die(json_encode(['code' => '0',]));

                    }

                    $donation->payment_type = 'rebill';
                    $donation->init_recurring_donation_id = $init_recurring_donation->id;
                    $donation->payment_title = $init_recurring_donation->title;
                    $donation->campaign_id = $init_recurring_donation->campaign_id;
                    $donation->payment_method_id = $init_recurring_donation->pm_id;
                    $donation->gateway_id = $init_recurring_donation->gateway_id;
                    $donation->donor_name = $init_recurring_donation->donor_name;
                    $donation->donor_email = $init_recurring_donation->donor_email;
                    $donation->donor_user_id = $init_recurring_donation->donor_user_id;
                    $donation->amount = $init_recurring_donation->amount;
                    $donation->currency_id = $init_recurring_donation->currency_id;

                    if(leyka_get_pm_commission($donation->pm_full_id) > 0.0) {
                        $donation->amount_total = leyka_calculate_donation_total_amount($donation);
                    }

                } else { // Single or init recurring donation
                    $donation = Leyka_Donations::get_instance()->get(absint($_POST['InvoiceId']));
                }

                if( !empty($_POST['SubscriptionId']) ) {

                    $donation->cp_recurring_id = $_POST['SubscriptionId'];

                    if( !empty($_POST['InvoiceId']) ) { // Add recurring activity meta only for init recurring donations
                        $donation->recurring_is_active = true;
                    }

                }

                if( !empty($_POST['TransactionId']) && !$donation->cp_transaction_id ) {
                    $donation->cp_transaction_id = $_POST['TransactionId'];
                }

                $donation->add_gateway_response($_POST);

                if($call_type === 'complete') {

                    $donation->status = 'funded';
                    Leyka_Donation_Management::send_all_emails($donation);

                    // For the cases when CP recurring subscription is cancelled on Leyka, but is in fact active in CP
                    // (so it's a successful rebill callback) - fix subscription activity in Leyka:
                    if($donation->payment_type === 'rebill' && !$donation->recurring_is_active) {
                        $donation->recurring_is_active = true;
                    }

                    if( // GUA direct integration - "purchase" event:
                        leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
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
                            ->setClientId($donation->ga_client_id ? : leyka_gua_get_client_id())
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

                } else {

                    $donation->status = 'failed';

                    // Emails will be sent only if respective options are on:
                    Leyka_Donation_Management::send_error_notifications($donation);

                }

                die(json_encode(['code' => '0',])); // Payment completed / fail registered

            case 'recurring_change':
            case 'recurrent_change':

                if( !empty($_POST['Id']) ) { // Recurring subscription ID in the CP system

	                $_POST['Id'] = trim($_POST['Id']);
	                $init_recurring_donation = $this->get_init_recurring_donation($_POST['Id']);

                    if($init_recurring_donation && $init_recurring_donation->recurring_is_active) {

                        if( !empty($_POST['Status']) ) {

                            if(in_array($_POST['Status'], ['Cancelled', 'Rejected', /*'Expired'*/]))  {

                                $init_recurring_donation->recurring_is_active = false;
//                                do_action('leyka_cp_cancel_recurring_subscription', $init_recurring_donation);

                            }
                        }
                    }

                }

                die(json_encode(['code' => '0',]));

            default:
        }

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

        $recurring_manual_cancel_link = 'https://my.cloudpayments.ru/ru/unsubscribe';

        if( !$donation->recurring_id ) {
            return new WP_Error('cp_no_subscription_id', sprintf(__('<strong>Error:</strong> unknown Subscription ID for donation #%d. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), $donation->id, leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $response = wp_remote_post('https://api.cloudpayments.ru/subscriptions/cancel', [
            'method' => 'POST',
            'blocking' => true,
            'timeout' => 10,
            'redirection' => 5,
            'headers' => [
                'Authorization' => 'Basic '.base64_encode(
                    leyka_options()->opt('cp_public_id').':'.leyka_options()->opt('cp_api_secret')
                ),
                'Content-type' => 'application/json',
            ],
            'body' => json_encode(['Id' => $donation->cp_recurring_id]),
        ]);

        if(empty($response['body'])) {
            return new WP_Error('cp_wrong_request_answer', sprintf(__('<strong>Error:</strong> the recurring subsciption cancelling request returned unexpected result. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $response['body'] = json_decode($response['body']);
        if(empty($response['body']->Success) || $response['body']->Success != 'true') {
            return new WP_Error('cp_cannot_cancel_recurring', sprintf(__('<strong>Error:</strong> we cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link));
        }

        $donation->recurring_is_active = false;

        return true;

    }

    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) {

        if($donation->type !== 'rebill' || !$donation->recurring_is_active) {
            if( !empty($_POST['Id']) ) {
                die(json_encode(['code' => '0',]));
            } else {
                die();
            }
        }

        header('Content-type: text/html; charset=utf-8');

        $recurring_cancelling_result = $this->cancel_recurring_subscription($donation);
        $recurring_manual_cancel_link = 'https://my.cloudpayments.ru/ru/unsubscribe';

        if($recurring_cancelling_result === true) {
            die(__('Recurring subscription cancelled successfully.', 'leyka'));
        } else if(is_wp_error($recurring_cancelling_result)) {
            die($recurring_cancelling_result->get_error_message());
        } else {
            die( sprintf(__('Error while trying to cancel the recurring subscription.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>Also you may <a href="%s">cancel your recurring donations manually</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email(), $recurring_manual_cancel_link) );
        }

    }

    /**
     * It is possible for CP to call a callback several times for one donation.
     * This donation must be created only once and then updated. It can be identified with CP transaction id.
     *
     * @param $cp_transaction_id integer
     * @return Leyka_Donation_Base
     */
    protected function _get_donation_by_transaction_id($cp_transaction_id) {

        $donation = Leyka_Donations::get_instance()->get([
            'meta' => [['key' => 'cp_transaction_id', 'value' => $cp_transaction_id,],],
            'get_single' => true,
        ]);

        if( !$donation ) {
            $donation = Leyka_Donations::get_instance()->add([
                'status' => 'submitted',
                'gateway_id' => 'cp',
                'payment_method_id' => 'card',
                'cp_transaction_id' => $cp_transaction_id,
                'force_insert' => true, // Turn off donation fields validation checks
            ], true);
        }

        return $donation;

    }

    public function get_init_recurring_donation($recurring) {

        if(is_a($recurring, 'Leyka_Donation_Base')) {
            $recurring = $recurring->cp_recurring_id;
        }
        if( !$recurring ) {
            return false;
        }

        return Leyka_Donations::get_instance()->get([
            'recurring_only_init' => true,
            'get_single' => true,
            'meta' => [['key' => 'cp_recurring_id', 'value' => $recurring,]],
            'orderby' => 'id',
            'order' => 'asc',
        ]);

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
            __('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'TransactionId'),
            __('Outcoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'Amount'),
            __('Outcoming currency:', 'leyka') => $this->_get_value_if_any($vars, 'Currency'),
            __('Incoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentAmount'),
            __('Incoming currency:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentCurrency'),
            __('Donor name:', 'leyka') => $this->_get_value_if_any($vars, 'Name'),
            __('Donor email:', 'leyka') => $this->_get_value_if_any($vars, 'Email'),
            __('Callback time:', 'leyka') => $this->_get_value_if_any($vars, 'DateTime'),
            __('Donor IP:', 'leyka') => $this->_get_value_if_any($vars, 'IpAddress'),
            __('Donation description:', 'leyka') => $this->_get_value_if_any($vars, 'Description'),
            __('Is test donation:', 'leyka') => $this->_get_value_if_any($vars, 'TestMode'),
            __('Invoice status:', 'leyka') => $this->_get_value_if_any($vars, 'Status'),
        ];

        if( !empty($vars['reason']) ) {
            $vars_final[__('Donation failure reason:', 'leyka')] = $vars['reason'];
        }
        if( !empty($vars['SubscriptionId']) ) {
            $vars_final[__('Recurrent subscription ID:', 'leyka')] = $this->_get_value_if_any($vars, 'SubscriptionId');
        }
        if( !empty($vars['StatusCode']) ) {
            $vars_final[__('Invoice status code:', 'leyka')] = $this->_get_value_if_any($vars, 'StatusCode');
        }

        return $vars_final;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('CloudPayments transaction ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="cp-transaction-id" name="cp-transaction-id" placeholder="<?php _e('Enter CloudPayments transaction ID', 'leyka');?>" value="<?php echo $donation->cp_transaction_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->cp_transaction_id;?></span>
                <?php }?>
            </div>

            <?php if($donation->type !== 'rebill') {
                return;
            }?>

            <label><?php _e('CloudPayments subscription ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="cp-recurring-id" name="cp-recurring-id" placeholder="<?php _e('Enter CloudPayments subscription ID', 'leyka');?>" value="<?php echo $donation->cp_recurring_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->cp_recurring_id;?></span>
                <?php }?>
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

            <label for="cp-transaction-id"><?php _e('CloudPayments transaction ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="cp-transaction-id" name="cp-transaction-id" placeholder="<?php _e('Enter CloudPayments transaction ID', 'leyka');?>" value="">
            </div>

            <label for="cp-recurring-id"><?php _e('CloudPayments subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="cp-recurring-id" name="cp-recurring-id" placeholder="<?php _e('Enter CloudPayments subscription ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'cp_recurring_id':
            case 'cp_recurrent_id':
                return $donation->get_meta('cp_recurring_id');
            case 'transaction_id':
            case 'invoice_id':
            case 'cp_transaction_id':
            case 'cp_invoice_id':
                return $donation->get_meta('cp_transaction_id');
            default:
                return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'cp_recurring_id':
            case 'cp_recurrent_id':
                return $donation->set_meta('cp_recurring_id', $value);
            case 'transaction_id':
            case 'invoice_id':
            case 'cp_transaction_id':
            case 'cp_invoice_id':
                return $donation->set_meta('cp_transaction_id', $value);
            default:
                return false;
        }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['cp-recurring-id']) && $donation->recurring_id != $_POST['cp-recurring-id']) {
            $donation->cp_recurring_id = $_POST['cp-recurring-id'];
        }

        if(isset($_POST['cp-transaction-id']) && $donation->transaction_id != $_POST['cp-transaction-id']) {
            $donation->cp_transaction_id = $_POST['cp-transaction-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['cp_recurring_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'cp_recurring_id', $params['cp_recurring_id']);
        }

        if( !empty($params['cp_transaction_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'cp_transaction_id', $params['cp_transaction_id']);
        }

    }

}

class Leyka_CP_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'cp';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="//cloudpayments.ru/">CloudPayments</a> is a Designer IT-solutions for the e-commerce market. Every partner receives the most comprehensive set of key technical options allowing to create a customer-centric payment system on site or in mobile application. Partners are allowed to receive payments in roubles and in other world currencies.', 'leyka'),
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

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

function leyka_add_gateway_cp() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_CP_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_cp');
