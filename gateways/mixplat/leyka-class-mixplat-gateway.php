<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Mixplat_Gateway class
 */

class Leyka_Mixplat_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_submit_result = false;
    protected $_redirect_url = '';

    protected function _set_attributes() {

        $this->_id = 'mixplat';
        $this->_title = __('MIXPLAT', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('MIXPLAT allows a simple and safe way to pay for goods and services with your mobile phone by sending SMS.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/nastrojka-mixplat/';
        $this->_registration_link = '//mixplat.ru/#join';

        $this->_min_commission = 0.4;
        $this->_receiver_types = ['legal'];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            $this->_id.'_service_id' => [
                'type' => 'text',
                'title' => __('MIXPLAT Project ID', 'leyka'),
                'comment' => __('Enter your project ID. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '100359'),
            ],
            $this->_id.'_secret_key' => [
                'type' => 'text',
                'title' => __('MIXPLAT API key', 'leyka'),
                'comment' => __('Enter your API key. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'c23a4398db8ef7b3ae1f4b07aeeb7c54f8e3c7c9'),
            ],
            $this->_id.'_widget_key' => [
                'type' => 'text',
                'title' => __('MIXPLAT widget key', 'leyka'),
                'comment' => __('Enter your widget key. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '783d15a8-802d-42e7-ae20-42539f22f7c3'),
            ],
            $this->_id.'_test_mode' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ],
        ];

    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['bankcard'])) {
            $this->_payment_methods['bankcard'] = Leyka_Mixplat_Card::get_instance();
        }
        if(empty($this->_payment_methods['yandex'])) {
            $this->_payment_methods['yandex'] = Leyka_Mixplat_Yandex_Pay::get_instance();
        }
        if(empty($this->_payment_methods['sbp'])) {
            $this->_payment_methods['sbp'] = Leyka_Mixplat_SBP::get_instance();
        }
        if(empty($this->_payment_methods['mobile'])) {
            $this->_payment_methods['mobile'] = Leyka_Mixplat_Mobile::get_instance();
        }
        if(empty($this->_payment_methods['sms'])) {
            $this->_payment_methods['sms'] = Leyka_Mixplat_Text::get_instance();
        }

    }

    /**
     * A service method to get the gateway inner PM ID value by according Leyka pm_id, and vice versa.
     *
     * @param $pm_id string PM ID (either Leyka or the gateway system).
     * @return string|false A PM ID in the gateway/Leyka system, or false (if PM ID is unknown).
     */
    protected function _get_gateway_bt_id($bt_id) {

        $all_bt_ids = [
            'pay' => 'apple_pay',
            'yandex' => 'yandex_pay',
            'bankcard' => 'bank_card',
            'sbp' => 'bank_sbp',
        ];

        if(array_key_exists($bt_id, $all_bt_ids)) {
            return $all_bt_ids[$bt_id];
        } else if(in_array($bt_id, $all_bt_ids)) {
            return array_search($bt_id, $all_bt_ids);
        } else {
            return false;
        }

    }

    public function _get_gateway_pm_id($pm_id) {

        $all_pm_ids = [
            'mobile' => 'sms',
            'card' => 'bankcard',
            'bank' => 'sbp',
            'wallet' => 'bankcard',
        ];

        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        } else if(in_array($pm_id, $all_pm_ids)) {
            return array_search($pm_id, $all_pm_ids);
        } else {
            return false;
        }

    }

    public function localize_js_strings($js_data){
        return array_merge($js_data, [
            'phone_invalid' => __('Please, enter a phone number in a 7xxxxxxxxxx format.', 'leyka'),
        ]);
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Mixplat_Mobile::get_instance()->active) {

            wp_enqueue_script(
                'leyka-mixplat',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Mixplat_Gateway::get_instance()->id.'/js/leyka.mixplat.js',
                ['jquery', 'leyka-public'],
                LEYKA_VERSION,
                true
            );

        }

		wp_enqueue_script('leyka-mixplat-widget', 'https://cdn.mixplat.ru/widget/v3/widget.js', [], false, true);

        add_filter('leyka_js_localized_strings', [$this, 'localize_js_strings']);

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

        $phone = isset($form_data['leyka_donor_phone']) ? $form_data['leyka_donor_phone'] : false;
        $error = false;

        if($pm_id === 'mobile') {

            if( !$phone ) { // Check the phone field in the additional form fields list

                foreach(Leyka_Campaign::get_additional_fields_settings($donation->campaign_id) as $field_slug => $field) {

                    if( !empty($field['type']) && $field['type'] === 'phone' ) {

                        $phone = $form_data['leyka_'.$field_slug];
                        break;

                    }

                }

            }
            $phone = str_replace(['+', '(', ')', '-'], '', trim($phone));


            if(empty($phone)) {
                $error = new WP_Error('leyka_mixplat_phone_is_empty', __('Phone number is required.', 'leyka'));
            } else if( !leyka_validate_donor_phone($phone) ) {
                $error = new WP_Error('leyka_mixplat_phone_is_incorrect', __('Phone number is incorrect.', 'leyka'));
            }

            if($error) {

                leyka()->add_payment_form_error($error);

                return ['status' => 1, 'errors' => $error, 'message' => $error->get_error_message(),];

            }

            $phone = '7'.mb_substr(str_replace(['+', ' ', '-', '.'], '', $phone), -10);
            $donation->mixplat_phone = $phone;

        }

        // Donation amount limits checks:
        if($donation->amount > 600000) {
            $error = new WP_Error(
                'leyka_mixplat_max_donation_size_exceeded',
                sprintf(__('Maximum donation amount of %s %s exceeded', 'leyka'), leyka_format_amount(600000), $donation->currency_label)
            );
        }

        if($error) {

            leyka()->add_payment_form_error($error);
            $donation->status = 'failed';

            return ['status' => 1, 'errors' => $error, 'message' => $error->get_error_message(),];

        }

        $is_success = false;

        // Use only API v3 from now on:
        require_once LEYKA_PLUGIN_DIR.'gateways/mixplat/lib/autoload.php';

        $mixplat_conf = new \MixplatClient\Configuration();
        $mixplat_conf->projectId = (int)leyka_options()->opt($gateway_id.'_service_id');
        $mixplat_conf->apiKey = leyka_options()->opt($gateway_id.'_secret_key');
        $mixplat_conf->widgetKey = leyka_options()->opt($gateway_id.'_widget_key');

        $http_client = new \MixplatClient\HttpClient\SimpleHttpClient();
        $mixplat_client = new \MixplatClient\MixplatClient();
        $mixplat_client->setConfig($mixplat_conf);
        $mixplat_client->setHttpClient($http_client);

        if(in_array($pm_id, ['pay', 'yandex', 'bankcard', 'sbp'])) {

            $new_payment = new \MixplatClient\Method\CreatePaymentForm();
            $new_payment->paymentMethod = ($pm_id === 'sbp')? \MixplatClient\MixplatVars::PAYMENT_METHOD_BANK : \MixplatClient\MixplatVars::PAYMENT_METHOD_CARD;
            $new_payment->billingType = $this->_get_gateway_bt_id($pm_id);
            $new_payment->description = get_bloginfo('name').' '.$donation->payment_title;
            $new_payment->urlSuccess = leyka_get_success_page_url();
            $new_payment->urlFailure = leyka_get_failure_page_url();

            if($donation->type === 'rebill') {

            	$new_payment->recurrentPayment = 1;
            	$new_payment->merchantData = $donation_id;

            }

        } else { // Mobile or SMS payment

            $new_payment = new \MixplatClient\Method\CreatePayment();
            $new_payment->paymentMethod = \MixplatClient\MixplatVars::PAYMENT_METHOD_MOBILE;
            $new_payment->userPhone = $phone;

        }

        $new_payment->test = leyka_options()->opt($gateway_id.'_test_mode') ? 1 : 0;
        $new_payment->merchantPaymentId = (string)$donation_id;
        $new_payment->userEmail = $donation->donor_email;

        if($donation->donor_name) {
            $new_payment->userName = $donation->donor_name;
        }

        $new_payment->amount = (int)round((float)$donation->amount * 100);
        $new_payment->userFundraisingProgram = $donation->payment_title;
        $new_payment->userFundraisingCampaignId = $donation->campaign_id;

        $response = $mixplat_client->request($new_payment);
        $donation->add_gateway_response($response);

        if( !empty($response['result']) && $response['result'] === 'ok' ) {

            $donation->mixplat_payment_id = $response['payment_id'];
            $is_success = true;

        }

        if($is_success) {

            if(leyka()->template_is_deprecated($donation->campaign->template)) { // Old templates (Revo & earlier)

                wp_redirect( empty($response['redirect_url']) ? leyka_get_success_page_url() : $response['redirect_url'] );
                exit(0);

            } else { // New templates (Star & further)

                $this->_submit_result = 'success';
                $this->_redirect_url = empty($response['redirect_url']) ?
                    leyka_get_failure_page_url() : $response['redirect_url'];

            }

            return ['status' => 0];

        } else { // Donation failed

            if(leyka()->template_is_deprecated($donation->campaign->template)) { // Old templates (Revo & earlier)

                if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
                    wp_mail(
                        leyka_get_website_tech_support_email(),
                        __('MIXPLAT - payment callback error occured', 'leyka'),
                        sprintf(__('This message has been sent because a create_payment call to MIXPLAT payment system returned some error. The details of the call are below. Payment error code / text: %s / %s', 'leyka'), $response['result'], $response['message'])."\n\r\n\r"
                    );
                }

                wp_redirect(leyka_get_failure_page_url());
                exit(0);

            } else { // New templates (Star & further)

                $error_text = __('MIXPLAT - payment callback error occured', 'leyka')
                    .(empty($response['error_description']) ? '' : ': '.$response['error_description']);

                $error = new WP_Error('mixplat_error', $error_text);
                leyka()->add_payment_form_error($error);

                return ['status' => 1, 'errors' => $error, 'message' => $error->get_error_message(),];

            }

        }

    }

    public function submission_redirect_url($current_url, $pm_id) {

        return $pm_id === 'mobile' && $this->_submit_result === 'success' ?
            leyka_get_success_page_url() :
            ($pm_id === 'sms' ? $current_url : ($this->_redirect_url ? : ''));

    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return $pm_id === 'sms' ? $redirect_type : 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {
        return $form_data;
    }

    public function _handle_service_calls($call_type = '') {

        $json_string = file_get_contents('php://input');

        $response = [];
        try {

            $response = json_decode($json_string, true);
            $response = $response ? : $_POST;

        } catch(Exception $ex) {
            error_log($ex);
        }

        $message = '';
        $is_error = false;

        if(empty($response['request'])) {

            $message = __("This message was sent because a call to your MIXPLAT callback was made with an empty request parameter value. The details of the call are below.", 'leyka')."\n\r\n\r";
            $is_error = true;

        } else if( !in_array($response['request'], ['payment_status', 'campaigns_list']) ) {

            $message = sprintf(__("This message was sent because a call to your MIXPLAT callback was made with an unknown request parameter value. The details of the call are below. Request value: %s", 'leyka'), $response['request'])."\n\r\n\r";
            $is_error = true;

        }

        if( !$is_error && $response['request'] === 'payment_status' ) {

            foreach(['status', 'amount', 'signature',] as $param_name) { // Check for necessary params
                if( !array_key_exists($param_name, $response) ) {

                    $message = sprintf(__('This message has been sent because a call to your MIXPLAT callback was made without required parameters given. The details of the call are below. The callback type: %s. The parameter missing: %s', 'leyka'), $response['request'], $param_name)."\n\r\n\r";
                    $is_error = true;
                    break;

                }
            }

        }

        if( !$is_error ) { // Signature check

            $params_signature = md5($response['payment_id'].leyka_options()->opt('mixplat_secret_key'));
            $response['signature_calculated'] = $params_signature;

            if($params_signature != $response['signature']) {

                $message = sprintf(__('This message has been sent because a call to your MIXPLAT callback was made with invalid MIXPLAT signature. The details of the call are below. The callback type: %s. Signatures sent / calculated: %s / %s', 'leyka'), $response['request'], $response['signature'], $params_signature)."\n\r\n\r";
                $is_error = true;

            }

        }

        if($is_error) {

            if($response['merchant_payment_id']) {

                $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_payment_id']));
                $donation->status = 'failed';
                $donation->add_gateway_response($response);

            }

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message .= "CALLBACK TYPE: ".print_r(empty($response['request']) ? '-' : $response['request'], true)."\n\r\n\r";
                $message .= "THEIR POST:\n\r".print_r($_POST, true)."\n\r\n\r";
                $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
                $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";
                $message .= "THEIR JSON:\n\r".print_r($json_string, true)."\n\r\n\r";
                $message .= "THEIR JSON DECODED:\n\r".print_r(json_decode($json_string), true)."\n\r\n\r";

                wp_mail(leyka_get_website_tech_support_email(), __('MIXPLAT - payment callback error occured', 'leyka'), $message);

            }

            status_header(500);
            die('Payment callback error');

        }

        if($response['request'] === 'campaigns_list') {

            status_header(200);
            die( json_encode(leyka_get_campaigns_list()) );

        } else if($response['request'] === 'payment_status') {

            // An empty merchant_payment_id means externally initiated payment (rebill, sms):
            if(empty($response['merchant_payment_id']) && !empty($response['status']) && $response['status'] === 'success') {

                if( !empty($response['recurrent_id']) ) { // Recurring
                    $this->_handle_rebill_donation_callback($response);
                } else { // SMS payment
                    $this->_handle_sms_donation_callback($response);
                }

            } else if( !empty($response['status']) ) { // Any payment via website (mobile, card, etc.)

                $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_payment_id']));

                if( !$donation || !$donation->id ) {

                    if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                        $message .= "CALLBACK TYPE: ".print_r(empty($response['request']) ? '-' : $response['request'], true)."\n\r\n\r";
                        $message .= "THEIR POST:\n\r".print_r($_POST, true)."\n\r\n\r";
                        $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
                        $message .= "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";
                        $message .= "THEIR JSON:\n\r".print_r($json_string, true)."\n\r\n\r";
                        $message .= "THEIR JSON DECODED:\n\r".print_r(json_decode($json_string), true)."\n\r\n\r";

                        wp_mail(
                            leyka_get_website_tech_support_email(),
                            __('MIXPLAT - payment callback error: unknown donation', 'leyka'),
                            $message
                        );

                    }

                    status_header(500);
                    die('Payment callback error');

                }

                $donation->add_gateway_response($response);
                
                $donation->mixplat_payment_id = $response['payment_id'];
                $new_payment_method_id = $response['payment_method'] ?
                    $this->_get_gateway_pm_id($response['payment_method']) : $donation->payment_method_id;

                switch($response['status']) {
                    case 'success':
                        $donation->status = 'funded';
                        $donation->payment_method_id = $new_payment_method_id;
												
                        Leyka_Donation_Management::send_all_emails($donation->id);
                        $this->_handle_ga_purchase_event($donation);

                        break;
                    case 'failure':
                        if($new_payment_method_id !== 'sbp') { // It's a QR code timeout, not fail

                            $donation->status = 'failed';
                            $donation->payment_method_id = $new_payment_method_id;

                        }
                        break;
                    default:
                }

            }

        }

        status_header(200);
        die(json_encode(['result' => 'ok']));

    }

    protected function _handle_sms_donation_callback($response) {

        $donation_id = Leyka_Donations::get_instance()->add([
            'gateway_id' => $this->_id,
            'mixplat_payment_id' => $response['payment_id'],
            'payment_method_id' => $this->_get_gateway_pm_id ( $response['payment_method'] ),
            'campaign_id' => empty($response['user_fundraising_campaign_id']) ?
                leyka_options()->opt('mixplat-sms_default_campaign_id') : $response['user_fundraising_campaign_id'],
            'status' => 'funded',
            'payment_type' => 'single',
            'amount' => $response['amount']/100.0,
            'amount_total' => 'auto',
            'currency' => empty($response['currency']) ?
                leyka_options()->opt('currency_main') : mb_strtolower($response['currency']),
            'mixplat_phone' => $response['user_phone'],
            'donor_name' => $response['user_name'],
            'donor_email' => $response['user_email'],
            'force_insert' => true, // SMS payments don't have Donor emails, so to avoid the error, insert a Donation forcefully
        ]);

        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $donation->add_gateway_response($response);

        $campaign = new Leyka_Campaign($donation->campaign_id);
        $campaign->update_total_funded_amount($donation);

        Leyka_Donation_Management::send_all_emails($donation->id);

        $this->_handle_ga_purchase_event($donation);

    }

    protected function _handle_rebill_donation_callback($response) {

        if( !empty($response['merchant_init_payment_id']) )	{
            $init_donation = Leyka_Donations::get_instance()->get($response['merchant_init_payment_id']);
        } else if( !empty($response['merchant_data']) ) {
            $init_donation = Leyka_Donations::get_instance()->get($response['merchant_data']);
        }

        if($init_donation) { // Rebill with information about init_donation

            $donation = Leyka_Donations::get_instance()->add_clone($init_donation, [
                    'mixplat_payment_id' => $response['payment_id'],
                    'status' => 'funded',
                    'payment_type' => 'rebill',
                    'init_recurring_donation' => $init_donation->id,
                    'amount' => $response['amount']/100.0,
                    'amount_total' => 'auto',
                    'currency' => empty($response['currency']) ?
                        leyka_options()->opt('currency_main') : mb_strtolower($response['currency']),
                    'date' => '' // don't copy the date
                ],
                ['recalculate_total_amount' => true,]
            );

        } else { // Rebill without information about init_donation

            $donation_id = Leyka_Donations::get_instance()->add([
                'gateway_id' => $this->_id,
                'mixplat_payment_id' => $response['payment_id'],
                'payment_method_id' => $this->_get_gateway_pm_id ( $response['payment_method'] ), # will be $response['billing_type'] in future
                'campaign_id' => empty($response['user_fundraising_campaign_id']) ? leyka_options()->opt('mixplat-sms_default_campaign_id') : $response['user_fundraising_campaign_id'],
                'status' => 'funded',
                'payment_type' => 'rebill',
                'amount' => $response['amount']/100.0,
                'amount_total' => 'auto',
                'currency' => empty($response['currency']) ? leyka_options()->opt('currency_main') : mb_strtolower($response['currency']),
                'mixplat_phone' => $response['user_phone'],
                'donor_name' => $response['user_name'] ? : ($response['merchant_fields']['user_name'] ? : 'anon'),
                'donor_email' => $response['user_email'] ? : ($response['merchant_fields']['user_email'] ? : 'no@email'),
                'force_insert' => true, // SMS payments don't have Donor emails, so to avoid the error, insert a Donation forcefully
            ]);

            $donation = Leyka_Donations::get_instance()->get($donation_id);

        }

        if(is_wp_error($donation)) {
            return false;
        }

        $donation->add_gateway_response($response);

        $campaign = new Leyka_Campaign($donation->campaign_id);
        $campaign->update_total_funded_amount($donation);

        do_action('leyka_new_rebill_donation_added', $donation);

        Leyka_Donation_Management::send_all_emails($donation->id);
        $this->_handle_ga_purchase_event($donation);

    }

    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) {
    	
        $recurring_cancel_link = 'https://my.donation.ru/?from=leyka&url='.LEYKA_PLUGIN_BASE_URL;

        $recurring_cancel_link .= '&amount='.$donation->amount;
        $recurring_cancel_link .= ($donation->mixplat_payment_id) ? '&payment_id='.$donation->mixplat_payment_id : '';
        $recurring_cancel_link .= ($donation->donor_email) ? '&email='.$donation->donor_email : '';
        $recurring_cancel_link .= ($donation->donor_name) ? '&name='.$donation->donor_name : '';
        $recurring_cancel_link .= ($donation->date_timestamp) ? '&date='.date('Y-m-d H:i:s',$donation->date_timestamp) : '';
        $recurring_cancel_link .= ($donation->id) ? '&leyka_donation_id='.$donation->id : '';
        $recurring_cancel_link .= ($donation->campaign_id) ? '&leyka_campaign_id='.$donation->campaign_id : '';
        $recurring_cancel_link .= ($donation->payment_method_id) ? '&leyka_payment_method_id='.$donation->payment_method_id : '';

        $campaign = new Leyka_Campaign($donation->campaign_id);
        if($campaign->id) {
            $recurring_cancel_link .= $campaign->url ? '&leyka_campaign_url='.$campaign->url : '';
        }

        if($donation->init_recurring_donation && !is_wp_error($donation->init_recurring_donation->mixplat_payment_id)) {
            $recurring_cancel_link .= $donation->init_recurring_donation->mixplat_payment_id ?
                '&init_payment_id='.$donation->init_recurring_donation->mixplat_payment_id : '';
        }

        wp_redirect($recurring_cancel_link);
        exit(0);

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

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('MIXPLAT payment ID:', 'leyka') => $this->_get_value_if_any($vars, 'payment_id'),
                __('Payments testing mode', 'leyka') => $this->_get_value_if_any($vars, 'test') ?
                    __('Yes', 'leyka') : __('No', 'leyka'),
                __('Operation result:', 'leyka') => $this->_get_value_if_any($vars, 'status_extended'),
                __('Payment method:', 'leyka') => $this->_get_value_if_any($vars, 'payment_method'),
                __('Card data:', 'leyka') => $this->_get_value_if_any($vars, 'card'),
                __('Mobile data:', 'leyka') => $this->_get_value_if_any($vars, 'mobile'),
            ],
            $donation
        );

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('Phone number', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

            <?php if($donation->type === 'correction') {?>
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

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'donor_phone':
            case 'mixplat_phone':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_leyka_mixplat_phone');
            case 'mixplat_payment_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_mixplat_payment_id');                
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'donor_phone':
            case 'mixplat_phone':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_leyka_mixplat_phone', $value);
            case 'mixplat_payment_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_mixplat_payment_id', $value);
            default:
                return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['mixplat-phone']) && $donation->mixplat_phone !== $_POST['mixplat-phone']) {
            $donation->mixplat_phone = $_POST['mixplat-phone'];
        }

        if(isset($_POST['mixplat-payment-id']) && $donation->mixplat_payment_id != $_POST['mixplat-payment-id']) {
            $donation->mixplat_payment_id = $_POST['mixplat-payment-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['mixplat_phone']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_leyka_mixplat_phone', $params['mixplat_phone']
            );
        }

        if( !empty($params['mixplat_payment_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_mixplat_payment_id', $params['mixplat_payment_id']
            );
        }

    }

}

class Leyka_Mixplat_Mobile extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'mobile';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'mobile_payments';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Mobile payment is performed from user mobile account without sending SMS.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Mobile payment', 'leyka');
        $this->_label = __('Mobile payment', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-beeline.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-megafon.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-mts.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-yota.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-tele2.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-tinkoff.svg',
        ]);

        $this->_specific_fields = [[ // For the new templates - from Star & further
            'type' => 'phone',
            'required' => true,
        ]];

        $this->_custom_fields = [/** @todo Only for old templates - Revo & earlier. Remove it when old templates support is finished. */
            'mixplat_phone' => apply_filters('leyka_donor_phone_field_html', '<label class="input req"><input id="leyka_'.$this->full_id.'_phone" class="required phone-num mixplat-phone" type="text" value="" name="leyka_donor_phone" placeholder="'.__('Your phone number in the 7xxxxxxxxxx format', 'leyka').'" maxlength="11">
</label>
<p class="field-comment">'.__('We will use this phone number to make a mobile payment', 'leyka').'</p>
<p class="leyka_donor_phone-error field-error"></p>', $this),
        ];

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Mixplat_Text extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'sms';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'mobile_payments';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Payments via SMS are common way of collecting donations by sending SMS with keyword to short 4-digit number.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Payments via SMS', 'leyka');
        $this->_label = __('Payments via SMS', 'leyka');

        $this->_support_global_fields = false;

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-beeline.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-megafon.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-mts.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-yota.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-tele2.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mobile-tinkoff.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

        $this->_processing_type = 'static';

    }

    protected function _set_dynamic_attributes() {
        $this->_custom_fields = [
            'sms_details' => apply_filters('leyka_the_content', leyka_options()->opt_safe($this->full_id.'_details')),
        ];
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            $this->full_id.'_default_campaign_id' => [
                'type' => 'select',
                'default' => leyka_get_campaigns_select_default(),
                'title' => __('Campaign for SMS payments', 'leyka'),
                'comment' => __('Select a campaign to which SMS payments will be related by default.', 'leyka'),
                'list_entries' => 'leyka_get_campaigns_list',
            ],
            $this->full_id.'_details' => [
                'type' => 'html',
                'default' => __('Donate by sending SMS to short number 3434 with text XXXX and your donation amount.', 'leyka'),
                'title' => __('Text how to donate via SMS', 'leyka'),
                'comment' => __('Enter text describing donation via SMS. Change XXXX to your registered keyword in MIXPLAT system.', 'leyka'),
                'required' => true,
            ],
        ];

    }

    public function display_static_data() {
        echo apply_filters('leyka_the_content', leyka_options()->opt_safe($this->full_id.'_details'));
    }

}

class Leyka_Mixplat_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'bankcard';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Enable Bank Card payments', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
//            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
//            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-jcb.svg',
//            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-unionpay.svg',
        ]);

      	$this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

class Leyka_Mixplat_Yandex_Pay extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Enable Yandex Pay payment method', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Yandex Pay', 'leyka');
        $this->_label = __('Yandex Pay', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/yandex-pay.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

class Leyka_Mixplat_SBP extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'sbp';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'online_banking';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Enable SBP payment method', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('SBP', 'leyka');
        $this->_label = __('SBP', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/sbp.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

    /** @todo By the 22.07.2022, SBP recurring isn't debugged completely (on the Banks Clients side, not on the Leyka or Mixplat side) */
//    public function has_recurring_support() {
//        return 'passive';
//    }

}

function leyka_add_gateway_mixplat() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Mixplat_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_mixplat');