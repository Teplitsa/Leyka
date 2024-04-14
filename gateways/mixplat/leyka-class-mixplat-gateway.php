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

        $this->_docs_link = '//leyka.org/docs/nastrojka-mixplat/';
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
                'description' => __('Project ID is shown on your <a href="http://stat.mixplat.ru/projects" target=_blank>MIXPLAT project settings page</a>', 'leyka'),
            ],
            $this->_id.'_widget_key' => [
                'type' => 'text',
                'title' => __('MIXPLAT widget key', 'leyka'),
                'comment' => __('Enter your widget key. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '783d15a8-802d-42e7-ae20-42539f22f7c3'),
            ],
            $this->_id.'_secret_key' => [
                'type' => 'text',
                'title' => __('MIXPLAT API key', 'leyka'),
                'comment' => __('Enter your API key. It can be found in your MIXPLAT project settings page on MIXPLAT site.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'c23a4398db8ef7b3ae1f4b07aeeb7c54f8e3c7c9'),
                'description' => __('API key and Widget key are shown on your <a href="http://stat.mixplat.ru/projects" target=_blank>MIXPLAT project settings page</a>', 'leyka'),
            ],
            $this->_id.'_test_mode' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('"Sandbox" mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode. Sometimes this mode is dubbed "sandbox".', 'leyka'),
                'short_format' => true,
            ],
            $this->_id.'_test_mode_help' => [
                'type' => 'static_text',
                'title' => __('Attention! Test mode on.', 'leyka'),
                'is_html' => true,
                'value' => '',
                'description' => __('Test mode is dedicated for initial setup only and must be switched off as soon as launch confirmation is received from your manager. There is no real charges during the test mode. For testing purposes you should use special test cards numbers:<li><b>4242424242424242</b> – all payments will be successful</li><li><b>5555555555554444</b> – all payments will be unsuccessful</li><li><b>2201382000000013</b> – payment status is random</li>Use any date and CVC code. Real cards give unsuccessful statuses while test mode is on.', 'leyka'),
                'field_classes' => ['test_mode'],
            ],
            $this->_id.'_split_enabled' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Donation split mode', 'leyka'),
                'comment' => __('When split mode is on, a certain percent from every donation is credited to special campaign, e.g. "Administrative expenses"', 'leyka'),
                'short_format' => true,
            ],
            $this->_id.'_split_help1' => [
                'type' => 'static_text',
                'title' => __('What does "donation split" mean:', 'leyka'),
                'is_html' => true,
                'value' => '',
                'description' => __('It means every donation is divided into two campaigns, e.g. 80% is credited to original campaign and 20% is credited to "Administrative expenses". Here you define percent to deduct and choose appropriate campaign to credit it on.', 'leyka'),
                'field_classes' => ['split'],
            ],
            $this->_id.'_split_percent' => [
                'type' => 'number',
                'title' => __('Split percent', 'leyka'),
                'default' => 20,
                'min' => 0.0,
                'max' => 50.0,
                'step' => 0.1,
                'comment' => __('Permissible range: from 0% to 50%.', 'leyka'),
                'length' => 6,
                'field_classes' => ['split'],
            ],
            $this->_id.'_split_campaign' => [
                'type' => 'select',
                'default' => leyka_get_campaigns_select_default(),
                'title' => __('Split to campaign', 'leyka'),
                'comment' => __('Choose campaign to be credited with percent from every transaction', 'leyka'),
                'description' => __('This campaign receives percent share deducted from the incoming donations.', 'leyka'),
                'list_entries' => leyka_get_campaigns_list(['orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => 1000,], true),
                'field_classes' => ['split'],
            ],

        ];

    }


    public function _set_donations_errors() {

      $this->_donations_errors_ids = [
        'failure_no_money' => 'L-7005',
        'failure_accept_timeout' => 'L-7002',
        'failure_other' => 'L-5003',
        'failure_gate_error' => 'L-4001',
        'failure_canceled_by_user' => 'L-4002',
        'failure_canceled_by_merchant' => 'L-4001',
        'failure_previous_payment' => 'L-4002',
        'failure_not_available' => 'L-5002',
        'failure_limits' => 'MP-7042',
        'failure_min_amount' => 'MP-7041',
        'failure_max_amount' => 'MP-7041',
        'failure_pending_timeout' => 'L-7002',
      ];

      Leyka_Donations_Errors::get_instance()->add_error(
            'MP-7002',
            __('3-D Secure authentication is unavailable', 'leyka'), [
                'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
                'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'MP-7041',
            __('The operation amount is too big or too small', 'leyka'), [
                'recommendation_admin' => __('Ask the donor to make a payment anew, with correct amount this time.', 'leyka'),
                'recommendation_donor' => __('Please, try to make a payment anew, with correct amount this time.', 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'MP-7042',
            __('The operation limit of bank card is exceeded', 'leyka'), [
                'recommendation_admin' => __('Ask the donor to use another bank card or another payment method.', 'leyka'),
                'recommendation_donor' => __('Please, use another bank card or payment method.', 'leyka'),
        ]);

    }


    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['bankcard'])) {
            $this->_payment_methods['bankcard'] = Leyka_Mixplat_Card::get_instance();
        }
        if(empty($this->_payment_methods['yandex'])) {
            $this->_payment_methods['yandex'] = Leyka_Mixplat_Yandex_Pay::get_instance();
        }
        if(empty($this->_payment_methods['mirpay'])) {
            $this->_payment_methods['mirpay'] = Leyka_Mixplat_MIR_Pay::get_instance();
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

    public function _mixplat_widget_mode($x) {
       $widgetKey = trim(leyka_options()->opt('mixplat_widget_key'));
       return ($widgetKey && strlen($widgetKey)>=32 ) ? true : false;
    }

    /**
     * A service method to get the gateway inner PM ID value by according Leyka pm_ids unknown).
     */

    public function _get_gateway_pm_id($pm_id) {
        $all_pm_ids = [
            'mobile' => 'sms',
            'card' => 'bankcard',
            'bank' => 'sbp',
        ];
        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        } else {
            return false;
        }
    }

    public function _set_gateway_pm_id($pm_id) {
        $all_pm_ids = [
            'mobile' => 'mobile',
            'bankcard' => 'card',
            'yandex' => 'card',
            'mirpay' => 'card',
            'pay' => 'card',
            'sbp' => 'bank',
        ];
        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        } else {
            return false;
        }
    }

    public function _set_gateway_bt_id($bt_id) {
        $all_bt_ids = [
            'mobile' => '',
            'bankcard' => 'credit_card',
            'yandex' => 'yandex_pay',
            'mirpay' => 'mir_pay',
            'pay' => 'apple_pay',
            'sbp' => '',
        ];
        if(array_key_exists($bt_id, $all_bt_ids)) {
            return $all_bt_ids[$bt_id];
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

        if(Leyka_Mixplat_Mobile::get_instance()->active || $this->_mixplat_widget_mode(1) ) {

            wp_enqueue_script( 'leyka-mixplat-widget', 'https://cdn.mixplat.ru/widget/v3/widget.js', [], LEYKA_VERSION.'.001', false);
            wp_enqueue_script( 'leyka-mixplat', LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Mixplat_Gateway::get_instance()->id.'/js/leyka.mixplat.js', ['jquery', 'leyka-mixplat-widget',], LEYKA_VERSION.'.001', false );

        }
                wp_enqueue_script('leyka-mixplat-wloader', sprintf('https://widgets.mixplat.ru/lMonitor/%d/monitor.js',(int)leyka_options()->opt('mixplat_service_id')), [], LEYKA_VERSION.'.001', false );
        add_filter('leyka_js_localized_strings', [$this, 'localize_js_strings']);
                add_filter('script_loader_tag', 'leyka_mixplat_wloader_async', 10, 3 );

    }
    

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later
        }

        if( $this->_mixplat_widget_mode(2) ) { // JS API

          return;

        } else { // Redirect API

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
            $new_payment->paymentMethod = $this->_set_gateway_pm_id($pm_id);
            $new_payment->billingType =   $this->_set_gateway_bt_id($pm_id);
            // get_bloginfo('name'). removed from the description
            $new_payment->description = $donation->payment_title;
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
        if(!empty($form_data['leyka_donor_comment'])) {
            $new_payment->userComment = $form_data['leyka_donor_comment'];
        }

        $new_payment->amount = (int)round((float)$donation->amount * 100);

        if( !empty($form_data['utm_medium']) )   { $new_payment->utmMedium = $form_data['utm_medium']; }
        if( !empty($form_data['utm_source']) )   { $new_payment->utmSource = $form_data['utm_source']; }
        if( !empty($form_data['utm_campaign']) ) { $new_payment->utmCampaign = $form_data['utm_campaign']; }
        if( !empty($form_data['utm_term']) )     { $new_payment->utmTerm = $form_data['utm_term']; }

        $new_payment->merchantCampaignId = $donation->campaign_id;

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

    }

    public function submission_redirect_url($current_url, $pm_id) {

        if( $this->_mixplat_widget_mode(3) ) { // JS API
           return '';
        }

        return $pm_id === 'mobile' && $this->_submit_result === 'success' ?
            leyka_get_success_page_url() :
            ($pm_id === 'sms' ? $current_url : ($this->_redirect_url ? : ''));

    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {

        if( $this->_mixplat_widget_mode(4) ) { // JS API
           return 'auto';
        }
        return $pm_id === 'sms' ? $redirect_type : 'redirect';
    }


    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        $phone = isset($form_data['leyka_donor_phone']) ? $form_data['leyka_donor_phone'] : '';
        $phone = str_replace(['+', '(', ')', '-'], '', trim($phone));

        $response = [
            'widget_key' => trim(leyka_options()->opt('mixplat_widget_key')),
            'merchant_payment_id' => (string)$donation_id,
            'test' => leyka_options()->opt('mixplat_test_mode') ? 1 : 0,
            'description' => $donation->payment_title,
            'amount' => (int)round((float)$donation->amount * 100),
            'currency' => 'RUB',
            'recurrent_payment' => 0,
            'user_name' => $donation->donor_name,
            'user_email' => $donation->donor_email,
            'payment_method' => $this->_set_gateway_pm_id($pm_id),
            'billing_type' => $this->_set_gateway_bt_id($pm_id),
            'merchant_campaign_id' => $donation->campaign_id,
            'url_success' => leyka_get_success_page_url(),
            'url_failure' => leyka_get_failure_page_url(),
        ];

        if( !empty($form_data['utm_medium']) ) {
            $response['utm_medium'] = $form_data['utm_medium'];
        }

        if( !empty($form_data['utm_source']) ) {
            $response['utm_source'] = $form_data['utm_source'];
        }

        if( !empty($form_data['utm_campaign']) ) {
            $response['utm_campaign'] = $form_data['utm_campaign'];
        }

        if( !empty($form_data['utm_term']) ) {
            $response['utm_term'] = $form_data['utm_term'];
        }

        if( !empty($form_data['leyka_recurring']) ) {
            $response['merchant_data'] = (string)$donation_id;
            $response['recurrent_payment'] = 1;
        }

        if( !empty($form_data['leyka_donor_comment']) ) {
            $response['user_comment'] = $form_data['leyka_donor_comment'];
        }

        if( $phone ) {
            $response['user_phone'] = $phone;
        }

        if($donation->additional_fields) {
            $response['merchant_fields'] = $donation->additional_fields;
        }

        return $response;
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
        $error_description = '';
        $json_items = [];
        $campaign_url = home_url();

        if(empty($response['request'])) {

          if( empty($_GET['d']) || empty($_GET['e']) ) {
             $is_error = true;
             $error_description = "Mandatory parameter is missing";
          }

          if( !$is_error ) {
            $donation = Leyka_Donations::get_instance()->get(absint($_GET['d']));
            $donor_email = $_GET['e'];

            if(!$donation || !$donation->id ) { // Unknown donation_id
               $is_error = true;
               $error_description = "Unknown donation ID";
             }
          }


          if( !$is_error ) {

            $campaign_url = get_the_permalink($donation->campaign_id);
            if($donor_email !== $donation->donor_email) {
               $is_error = true;
               $error_description = "Email mismatch";
            }

          }


          if( !$is_error ) {

            $widget_key = trim(leyka_options()->opt('mixplat_widget_key'));
            if(empty($widget_key)) {

               $is_error = true;
               $error_description = "widget_key not set";

            }

          }


          if( !$is_error ) {
            $output = '<html lang="ru"><head><meta charset="UTF-8"><title>'.$donation->payment_title.'</title><base target="_parent"><script src="//cdn.mixplat.ru/widget/v3/widget.js"></script>
</head><body style="margin:0px;padding:0px;overflow:hidden"><iframe src="'.$campaign_url.'" frameborder="0" scrolling="yes" seamless="seamless" style="display:block; width:100%; height:100vh;"></iframe><script type="text/javascript">
 document.addEventListener("DOMContentLoaded", function(){
  let options = {
    "widget_key": "'.$widget_key.'",
    "description": "'.$donation->payment_title.'",
    "amount": '.((int)round((float)$donation->amount * 100)).',
    "user_name": "'.$donation->donor_name.'",
    "user_email": "'.$donation->donor_email.'",
    "merchant_campaign_id": "'.$donation->campaign_id.'",
    "recurrent_payment": "'.(($donation->is_init_recurring_donation)?1:0).'",
    "utm_source": "mixplat_dobilling",
   }
   let M = new Mixplat(options);
   M.build();
   M.setSuccessCallback("'.leyka_get_success_page_url().'");
   M.setFailCallback("'.leyka_get_failure_page_url().'");
 });
</script></body></html>';
          }

          if($is_error) {

            header('Location: '.$campaign_url, true, 307);
            die();

          }

          status_header(200);
          die($output); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        } else if( !in_array($response['request'], ['payment_status', 'campaigns_list', 'refund_status', 'subscription_status', 'subscriptions_list' ]) ) {

            $message = sprintf(__("This message was sent because a call to your MIXPLAT callback was made with an unknown request parameter value. The details of the call are below. Request value: %s", 'leyka'), $response['request'])."\n\r\n\r";
            $is_error = true;
            $error_description = 'Unknown request value';
        }

        if( !$is_error && $response['request'] === 'payment_status' ) {

            foreach(['status', 'amount', 'signature',] as $param_name) { // Check for necessary params
                if( !array_key_exists($param_name, $response) ) {

                    $message = sprintf(__('This message has been sent because a call to your MIXPLAT callback was made without required parameters given. The details of the call are below. The callback type: %s. The parameter missing: %s', 'leyka'), $response['request'], $param_name)."\n\r\n\r";
                    $is_error = true;
                    $error_description = "Mandatory parameter $param_name is missing";
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
                $error_description = "Signature mismatch";
            }

        }


        if( $is_error ) {

            if( !empty($response['merchant_payment_id']) ) { // If existing donation - store response data

                $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_payment_id']));
                if($donation && $donation->id) {

                    $donation->status = 'failed';
                    $donation->add_gateway_response($response);

                }

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

            status_header(200);
            die(wp_json_encode(array_merge( ['result' => 'ok'], $error_description ? ['error_description' => $error_description ] : [], $json_items )));

        }

        if($response['request'] === 'refund_status') {

          // ToDo API sync
          if( empty($response['merchant_payment_id']) || !absint($response['merchant_payment_id']) ) {
             $is_error = true;
             $error_description = "Mandatory parameter merchant_payment_id is missing";
          }

          $status = !empty($response['status']) ? $response['status'] : '';
          if( !$status ) {
             $is_error = true;
             $error_description = "Mandatory parameter status is missing";
          }

          if(!$is_error) {
             $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_payment_id']));
             if( !$donation || !$donation->id ) { // Unknown donation_id
               $is_error = true;
               $error_description = "Unknown donation ID";
             }
          }

          if(!$is_error) {
             if(in_array($status, ['pending','success'] )) {
                $donation->status = 'refunded';
             } else if( $status === 'failure' ) {
                $is_error = true;
                $error_description = "Refund not succeeded, donation stays as is";
             } else {
                $is_error = true;
                $error_description = "Unknown status";
             }
          }

        } else if($response['request'] === 'subscription_status') {

          // ToDo API sync
          if( empty($response['merchant_init_payment_id']) || !absint($response['merchant_init_payment_id']) ) {
             $is_error = true;
             $error_description = "Mandatory parameter merchant_init_payment_id is missing";
          }

          $status = !empty($response['status']) ? $response['status'] : '';
          if( !$status ) {
             $is_error = true;
             $error_description = "Mandatory parameter status is missing";
          }

          if( !$is_error ) {

             $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_init_payment_id']));
             if( !$donation || !$donation->id ) { // Unknown donation_id
               $is_error = true;
               $error_description = "Unknown donation ID";
             }

          }

          if( !$is_error ) {

             if(in_array($status, ['stopped','suspended'] )) {
                $donation->recurring_is_active = false;
             } else if( $status === 'active' ) {
                $donation->recurring_is_active = true;
             } else {
                $is_error = true;
                $error_description = 'Unknown status';
             }

          }

        } else if($response['request'] === 'campaigns_list') {

            $campaigns_list = leyka_get_campaigns_list(['orderby' => 'id', 'order' => 'desc', 'posts_per_page' => 1000,], false);
            $cmp = [];
            foreach($campaigns_list as $campaign ) {

                $campaign = leyka_get_validated_campaign($campaign);
                if( !$campaign ) {
                    continue;
                }

                $cmp[] = [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'payment_title' => $campaign->payment_title,
                    'target' => $campaign->target,
                    'donations_types_available' => $campaign->donations_types_available,
                    'donations_type_default' => $campaign->donations_type_default,
                    'url' => $campaign->url,
                    'type' => $campaign->type,
                    'amount' => $campaign->total_funded,
                    'status' => $campaign->status,
                    'template' => $campaign->template,
                    'post_name' => $campaign->post_name,
                    'campaign_type' => $campaign->campaign_type,
                    'is_finished' => $campaign->is_finished,
                    'views_count' => $campaign->views_count,
                    'submits_count' => $campaign->submits_count,
                    'target_state' => $campaign->target_state,
                    'date_target_reached' => $campaign->date_target_reached,
                ];

            }

            $json_items['cms'] = 'leyka';
            $json_items['campaigns'] = $cmp;

        } else if($response['request'] === 'subscriptions_list') {

            $init_donations = Leyka_Donations::get_instance()->get(
                ['orderby' => 'id', 'order' => 'desc', 'get_all' => true, 'recurring_only_init' => true,]
            );
            $slist = [];

            foreach($init_donations as $init_donation) {

                try {
                    $donor = new Leyka_Donor(absint($init_donation->donor_id));
                } catch(Exception $e) {
                    $donor = false;
                }

                $item = [
                    'id' => $init_donation->id,
                    'status' => $init_donation->recurring_subscription_status,
                    'error' => $init_donation->recurring_subscription_error_id,
                    'user_id' => $init_donation->donor_id,
                    'user_name' => $init_donation->donor_name,
                    'user_email' => $init_donation->donor_email,
                    'campaign' => $init_donation->campaign_id,
                    'date' => $init_donation->date_timestamp,
                    'next_date' => $init_donation->update_next_recurring_date(),
                    'rebills' => $init_donation->successful_rebills_number + 1,
                    'pm' => $init_donation->pm_full_id,
                    'amount' => $init_donation->amount,
                    'amount_total' => $init_donation->amount_total,
                    'cancel_date' => $init_donation->recurring_cancel_date,
                ];

                if($donor) {

                    $item['donor_amount_total'] = $donor->amount_donated;
                    $item['donor_pay_total'] = $donor->get_donations_count();
                    $item['donor_first_date'] = $donor->first_donation_date_timestamp;
                    $item['donor_last_date'] = $donor->last_donation_date_timestamp;

                }

                $slist[] = $item;

            }

            $json_items['cms'] = 'leyka';
            $json_items['subscriptions'] = $slist;

        } else if($response['request'] === 'payment_status') {

            // An empty merchant_payment_id means externally initiated payment (rebill, sms):
            if( empty($response['merchant_payment_id']) ) {

               if( $response['status'] === 'success' ) {
                  $new_donation_id = $this->_handle_new_donation_callback($response);
                  if( $new_donation_id ) {
                    $json_items['new_donation_id'] = $new_donation_id;
                  } else {
                    $is_error = true;
                    $error_description = "Donation callback not handled";
                  }
               }

            } else { // Status for payments from the website (mobile, card, sbp, etc.)

                $donation = Leyka_Donations::get_instance()->get(absint($response['merchant_payment_id']));

                if( !$donation || !$donation->id ) { // Unknown donation_id, store it anyway
                    $is_error = true;
                    $error_description = "Unknown donation ID";
                    $new_donation_id = $this->_handle_new_donation_callback($response);
                    if( $new_donation_id ) {
                      $json_items['new_donation_id'] = $new_donation_id;
                    } else {
                      $error_description .= " and donation callback not handled";
                    }
                    status_header(200);
                    die(wp_json_encode(array_merge( ['result' => 'ok'], $error_description ? ['error_description' => $error_description ] : [], $json_items )));
                }

                $donation->add_gateway_response($response);

                if( !empty($response['payment_id']) ) {
                   $donation->mixplat_payment_id = $response['payment_id'];
                }
                if( !empty($response['recurrent_id']) ) {
                   $donation->mixplat_recurrent_id = $response['recurrent_id'];
                }

                if( !$donation->donor_email || !leyka_validate_email($donation->donor_email)) { // E.g. in case we have mobile subscription
                  $donor_email = ($donation->id)."@anonymous.user"; // Without email, we get error on status change
                }

                $new_payment_method_id = !empty($response['payment_method']) ? $this->_get_gateway_pm_id($response['payment_method']) : $donation->payment_method_id;

                if( (int)round((float)$donation->amount * 100) !== (int)$response['amount'] ) {
                  $donation->amount = (float)$response['amount']/100.0;
                  $json_items['new_donation_amount'] = (int)$response['amount'];
                }

                switch($response['status']) {

                    case 'success':
                        $donation->payment_method_id = $new_payment_method_id;
                        $donation->status = 'funded';
                        if( !$donation->mixplat_split_to ) {
                          $donation->mixplat_split_to = $this->_split_donation($donation);
                        }
                        Leyka_Donation_Management::send_all_emails($donation);
                        $this->_handle_ga_purchase_event($donation);
                        break;

                    case 'failure':
                        if($new_payment_method_id !== 'sbp') { // It's a QR code timeout, not fail
                            $donation->payment_method_id = $new_payment_method_id;
                            $donation->status = 'failed';
                            $donation->error_id = empty($response['status_extended']) ? 'L-4002' : $response['status_extended'];
                            Leyka_Donation_Management::send_error_notifications($donation);
                        }
                        break;

                    default:
                }

                if( $donation->mixplat_split_to ) {
                  $donation->amount_total = $this->_split_calculate_total_amount($donation->amount, $donation->pm_full_id, $donation->campaign_id);
                  $this->_split_set_status($donation);
                }

            }

        }

        status_header(200);
        die(wp_json_encode(array_merge(
            ['result' => 'ok'],
            $error_description ? ['error_description' => $error_description ] : [], $json_items
        )));

    }

    protected function _handle_new_donation_callback($response) {

       if( !empty($response['recurrent_id']) ) { // Recurring payment

          $init_donation = !empty($response['merchant_init_payment_id']) ?
              Leyka_Donations::get_instance()->get($response['merchant_init_payment_id']) :
              ( !empty($response['merchant_data']) ? Leyka_Donations::get_instance()->get($response['merchant_data']) : 0 );

          if($init_donation) { // Rebill with information about init_donation
            return $this->_handle_rebill_donation_callback($response,$init_donation);
          } else { // Rebill but without init_donation
            return $this->_handle_single_donation_callback($response);
          }

        } else { // Single payment
          return $this->_handle_single_donation_callback($response);
        }

    }

    // Split option divides donation by two in different campaigns only
    protected function _is_split_required($campaign_id = false) {
        return leyka_options()->opt('mixplat_split_enabled')
            && leyka_options()->opt('mixplat_split_percent')
            && leyka_options()->opt('mixplat_split_percent') > 0.1
            && ($campaign_id && $campaign_id !== leyka_options()->opt('mixplat_split_campaign'));
    }

    // Total amount will be deducted by the standard commission of pm_id as well as split_percent
    protected function _split_calculate_total_amount($amount = 0.0, $pm_full_id = '', $campaign_id = false) {

      $split_percent = $this->_is_split_required($campaign_id) ? leyka_options()->opt('mixplat_split_percent')/100.0 : 0.0;

      $commission = leyka_options()->opt('commission');
      $commission = empty($commission[$pm_full_id]) ? 0.0 : $commission[$pm_full_id]/100.0;

      return (($commission && $commission > 0.0) || ($split_percent && $split_percent > 0.0)) ?
          $amount - round($amount*$split_percent, 2) - round($amount*$commission, 2) : $amount;

    }


    protected function _split_donation($orig_donation = false) {

      if( !$orig_donation ) {
        return false;
      }

      if( !$this->_is_split_required($orig_donation->campaign_id) ) {
         return false;
      }

      $amount = round($orig_donation->amount * leyka_options()->opt('mixplat_split_percent')/100.0, 2);

      $donation = Leyka_Donations::get_instance()->add_clone($orig_donation, [
            'campaign_id' => leyka_options()->opt('mixplat_split_campaign'),
            'amount' => $amount,
            'amount_total' => $amount,
            'force_insert' => true, // SMS payments don't have Donor emails, so to avoid the error, insert a Donation forcefully
        ]);

        if(is_wp_error($donation)) {
            return false;
        }

        $donation->add_gateway_response([
            'payment_id' => $orig_donation->mixplat_payment_id,
            'mixplat_split_from' => $orig_donation->id
        ]);

        if($orig_donation->mixplat_phone) {
            $donation->mixplat_phone = $orig_donation->mixplat_phone;
        }
        if($orig_donation->mixplat_recurrent_id) {
            $donation->mixplat_recurrent_id = $orig_donation->mixplat_recurrent_id;
        }

        return $donation->id;

    }

    protected function _split_set_status($orig_donation = false) {

        if( !$orig_donation || !$orig_donation->mixplat_split_to ) {
          return;
        }

        $donation = Leyka_Donations::get_instance()->get(absint($orig_donation->mixplat_split_to));

        if(!$donation || !$donation->id ) {
          return;
        }

        $donation->status = $orig_donation->status;
        $amount = round($orig_donation->amount * leyka_options()->opt('mixplat_split_percent')/100.0, 2);
        $donation->amount = $amount;
        $donation->amount_total = $amount;
        $donation->campaign_id = leyka_options()->opt('mixplat_split_campaign');
        $donation->add_gateway_response(['payment_id'=>$orig_donation->mixplat_payment_id,'recurrent_id'=>$orig_donation->mixplat_recurrent_id,'mixplat_split_from'=>$orig_donation->id]);

    }

    protected function _handle_single_donation_callback($response) {

        $donor_phone = !empty($response['user_phone']) ? $response['user_phone'] : '';

        $donor_email = !empty($response['user_email']) ? $response['user_email'] : ( !empty($response['merchant_fields']['user_email']) ? $response['merchant_fields']['user_email'] : '' ); // ToDo: use only response['user_email']

        $final_status = $response['status']==='success' ? 'funded' : ( $response['status']==='failure' ? 'failed' : 'submitted' );

        if(!$donor_email || !leyka_validate_email($donor_email)) { // In case we have different rules for email validation
           $donor_email = '';
           $status = $final_status;
        }
        else {
           $status = 'submitted';
        }

        $donor_name = !empty($response['user_name']) ? $response['user_name'] : ( !empty($response['merchant_fields']['user_name']) ? $response['merchant_fields']['user_name'] : '' ); // ToDo: use only response['user_name']

        $campaign_id = !empty($response['merchant_campaign_id']) ? $response['merchant_campaign_id'] : '' ;
        if( !$campaign_id || !leyka_get_validated_campaign($campaign_id) ) {
           $campaign_id = leyka_options()->opt('mixplat-sms_default_campaign_id');
        }

        // amount, split
        $pm_id = $this->_get_gateway_pm_id($response['payment_method']);
        $amount = round($response['amount']/100.0, 2);
        $amount_total = $this->_split_calculate_total_amount($amount, 'mixplat-'.$pm_id, $campaign_id);

        $donation_id = Leyka_Donations::get_instance()->add([
            'gateway_id' => $this->_id,
            'mixplat_payment_id' => !empty($response['payment_id']) ? $response['payment_id'] : '',
            'mixplat_recurrent_id' => !empty($response['recurrent_id']) ? $response['recurrent_id'] : '',
            'payment_method_id' => $pm_id,
            'campaign_id' => $campaign_id,
            'status' => $status,
            'payment_type' => 'single',
            'amount' => $amount,
            'amount_total' => $amount_total,
            'currency' => empty($response['currency']) ? leyka_options()->opt('currency_main') : mb_strtolower($response['currency']),
            'mixplat_phone' => $donor_phone,
            'donor_name' => $donor_name,
            'donor_email' => $donor_email,
            'force_insert' => true, // SMS payments don't have Donor emails, so to avoid the error, insert a Donation forcefully
        ]);

        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $donation->add_gateway_response($response);

        if( $donor_email ) { // Updates donor's data
           $donation->status = $final_status;
        }

        $donation->mixplat_split_to = $this->_split_donation($donation);

        if($status === 'funded') {
          Leyka_Donation_Management::send_all_emails($donation);
          $this->_handle_ga_purchase_event($donation);
        } else if($status === 'failed') {
          $donation->error_id = empty($response['status_extended']) ? 'L-4002' : $response['status_extended'];
          Leyka_Donation_Management::send_error_notifications($donation);
        }

        return $donation->id;

    }

    protected function _handle_rebill_donation_callback($response,$init_donation) {

        $final_status = $response['status']==='success' ? 'funded' : ( $response['status']==='failure' ? 'failed' : 'submitted' );

        $donor_email = $init_donation->donor_email;
        if(!$donor_email || !leyka_validate_email($donor_email)) { // E.g. in case we have mobile subscription
           $donor_email = ($init_donation->id)."@anonymous.user"; // Without email we get clone error
           $status = $final_status;
        }
        else {
           $status = 'submitted';
        }

        $amount = round($response['amount']/100.0, 2);
        $amount_total = $this->_split_calculate_total_amount($amount, $init_donation->pm_full_id, $init_donation->campaign_id);

        $donation = Leyka_Donations::get_instance()->add_clone($init_donation, [
            'mixplat_payment_id' => !empty($response['payment_id']) ? $response['payment_id'] : '',
            'mixplat_recurrent_id' => !empty($response['recurrent_id']) ? $response['recurrent_id'] : '',
            'status' => $status,
            'payment_type' => 'rebill',
            'init_recurring_donation' => $init_donation->id,
            'amount' => $response['amount']/100.0,
            'amount_total' => $amount_total,
            'donor_email' => $donor_email,
            'currency' => empty($response['currency']) ? leyka_options()->opt('currency_main') : mb_strtolower($response['currency']),
            'date' => '' // don't copy the date
           ]
        );

        $init_donation->recurring_is_active = true;

        if(is_wp_error($donation)) {
            return false;
        }

        if( $init_donation->mixplat_phone ) {
          $donation->mixplat_phone = $init_donation->mixplat_phone;
        }

        $donation->add_gateway_response($response);

        if( $donor_email ) { // Updates donor's data
           $donation->status = $final_status;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);
        $campaign->update_total_funded_amount($donation);

        do_action('leyka_new_rebill_donation_added', $donation);

        $donation->mixplat_split_to = $this->_split_donation($donation);

        if( $donation->mixplat_split_to ) {
          $split_donation = Leyka_Donations::get_instance()->get(absint($donation->mixplat_split_to));
          $split_donation->init_recurring_donation_id = $init_donation->id;
          $split_donation->payment_type='rebill';
        }

        if($status === 'funded') {
          Leyka_Donation_Management::send_all_emails($donation);
          $this->_handle_ga_purchase_event($donation);
        } else if($status === 'failed') {
          $donation->error_id = empty($response['status_extended']) ? 'L-4002' : $response['status_extended'];
          Leyka_Donation_Management::send_error_notifications($donation);
        }

        return $donation->id;
    }


    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) {

        $recurring_cancel_link = 'https://my.donation.ru/?from=leyka&url='.LEYKA_PLUGIN_BASE_URL;

        $recurring_cancel_link .= '&amount='.$donation->amount;
        $recurring_cancel_link .= ($donation->mixplat_payment_id) ? '&payment_id='.$donation->mixplat_payment_id : '';
        $recurring_cancel_link .= ($donation->donor_email) ? '&email='.$donation->donor_email : '';
        $recurring_cancel_link .= ($donation->donor_name) ? '&name='.$donation->donor_name : '';
        $recurring_cancel_link .= ($donation->date_timestamp) ? '&date='.gmdate('Y-m-d H:i:s',$donation->date_timestamp) : '';
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


    public function cancel_recurring_subscription(Leyka_Donation_Base $donation) {

        if($donation->type !== 'rebill') {
            return new WP_Error('wrong_recurring_donation_to_cancel', __('Wrong donation given to cancel a recurring subscription.', 'leyka') );
        }

        if( !$donation->mixplat_recurrent_id ) {
            return new WP_Error('recurring_cancelling__no_subscription_id', sprintf(__('<strong>Error:</strong> unknown Subscription ID for donation #%d. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), $donation->id, leyka_get_website_tech_support_email()));
        }

        require_once LEYKA_PLUGIN_DIR.'gateways/mixplat/lib/autoload.php';
        $mixplat_conf = new \MixplatClient\Configuration();
        $mixplat_conf->apiKey = leyka_options()->opt('mixplat_secret_key');
        $http_client = new \MixplatClient\HttpClient\SimpleHttpClient();
        $mixplat_client = new \MixplatClient\MixplatClient();
        $mixplat_client->setConfig($mixplat_conf);
        $mixplat_client->setHttpClient($http_client);
        $request = new \MixplatClient\Method\StopSubscription();
        $request->recurrentId = $donation->mixplat_recurrent_id;
        $response = $mixplat_client->request($request);

        if( !empty($response['result']) && $response['result'] === 'ok' ) {

            $donation->recurring_is_active = false;

        } else { // Unsubscribe failed

            return new WP_Error('recurring_cancelling__cannot_cancel_recurring', sprintf(__('<strong>Error:</strong> we cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br><br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email()));

        }

        return true;
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

        $vars_final = [];

        if( !empty($vars['payment_id']) ) {
            $vars_final[__('MIXPLAT payment ID:', 'leyka')] = $vars['payment_id'];
        }

        if( !empty($vars['status_extended']) ) {
            $vars_final[__('Operation result:', 'leyka')] = $vars['status_extended'];
        }

        if( !empty($vars['payment_method']) ) {
            $vars_final[__('Payment method:', 'leyka')] = $vars['payment_method'];
        }

        if($this->_get_value_if_any($vars, 'status') === 'success') {
            $vars_final[__('Incoming sum:', 'leyka')] = ((float)$this->_get_value_if_any($vars, 'amount_merchant')/100.0)." ".$this->_get_value_if_any($vars, 'currency');
        }

        if( $donation->mixplat_split_to ) {
            $split_donation = Leyka_Donations::get_instance()->get(absint($donation->mixplat_split_to));
            if($split_donation && $split_donation->amount ) {
                $campaign = new Leyka_Campaign($split_donation->campaign_id);
                $vars_final[__('Split:', 'leyka')] = ''.$split_donation->amount.' '.$split_donation->currency.' '.__('transferred to', 'leyka').' <a href="?page=leyka_donation_info&donation='.$split_donation->id.'">#'.$split_donation->id.'</a> ('.$campaign->title.')';
            }
        }

        if( !empty($vars['mixplat_split_from']) ) {
            $split_donation = Leyka_Donations::get_instance()->get(absint($vars['mixplat_split_from']));
            if($split_donation && $split_donation->amount ) {
                $campaign = new Leyka_Campaign($split_donation->campaign_id);
                $vars_final[__('Split:', 'leyka')] = __('Original donation', 'leyka').' <a href="?page=leyka_donation_info&donation='.$split_donation->id.'">#'.$split_donation->id.'</a> ('.$campaign->title.')';
            }
        }

        if( !empty($vars['recurrent_id']) ) {
            $vars_final[__('Recurrent ID:', 'leyka')] = $vars['recurrent_id'];
        }

        if( $this->_get_value_if_any($vars, 'test') ) {
            $vars_final[__('Payments testing mode:', 'leyka')] = __('Yes', 'leyka');
        }

        if( !empty($vars['user_comment']) ) {
            $vars_final[__('User comment:', 'leyka')] = $vars['user_comment'];
        }

        if( !empty($vars['user_country']) ) {
            $vars_final[__('User country:', 'leyka')] = $vars['user_country'];
        }

        if( !empty($vars['user_region']) ) {
            $vars_final[__('User region:', 'leyka')] = $vars['user_region'];
        }

        if( !empty($vars['utm_medium']) ) {
            $vars_final[__('UTM medium:', 'leyka')] = $vars['utm_medium'];
        }

        if( !empty($vars['utm_source']) ) {
            $vars_final[__('UTM source:', 'leyka')] = $vars['utm_source'];
        }

        if( !empty($vars['utm_campaign']) ) {
            $vars_final[__('UTM campaign:', 'leyka')] = $vars['utm_campaign'];
        }

        if( !empty($vars['utm_term']) ) {
            $vars_final[__('UTM term:', 'leyka')] = $vars['utm_term'];
        }

        if( !empty($vars['payment_method']) ) {
            $vars_final[__('Payment method data:', 'leyka')] = $this->_get_value_if_any($vars, $vars['payment_method'] );
        }

        return apply_filters( 'leyka_donation_gateway_response', $vars_final, $donation );

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php esc_html_e('Phone number', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

            <?php if($donation->type === 'correction') {?>
                <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php esc_attr_e('Enter a phone number', 'leyka');?>" value="<?php echo esc_attr( $donation->mixplat_phone ); ?>">
            <?php } else {?>
                <span class="fake-input"><?php echo esc_html( $donation->mixplat_phone ); ?></span>
            <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="mixplat-phone"><?php esc_html_e('Phone number', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="mixplat-phone" name="mixplat-phone" placeholder="<?php esc_attr_e('Enter a phone number', 'leyka');?>" value="">
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
            case 'mixplat_recurrent_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_mixplat_recurrent_id');
            case 'mixplat_split_to':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_mixplat_split_to');
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
            case 'mixplat_recurrent_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_mixplat_recurrent_id', $value);
            case 'mixplat_split_to':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_mixplat_split_to', $value);
            default:
                return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['mixplat-phone']) && $donation->mixplat_phone !== $_POST['mixplat-phone']) {
            $donation->mixplat_phone = $_POST['mixplat-phone'];
        }

//        if(isset($_POST['mixplat-payment-id']) && $donation->mixplat_payment_id != $_POST['mixplat-payment-id']) {
//            $donation->mixplat_payment_id = $_POST['mixplat-payment-id'];
//        }

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['mixplat_phone']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id, '_leyka_mixplat_phone', $params['mixplat_phone']
            );
        }

//        if( !empty($params['mixplat_payment_id']) ) {
//            Leyka_Donations::get_instance()->set_donation_meta(
//                $donation_id, '_mixplat_payment_id', $params['mixplat_payment_id']
//            );
//        }

    }

}

class Leyka_Mixplat_Mobile extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_processing_type($value = false) {
      if($value) {
       $this->_processing_type=$value;
      }
      return $this->_processing_type;
    }

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

        $this->_custom_fields = [ /** Only for old templates - Revo & earlier. Remove it when old templates support is finished. */
            'mixplat_phone' => apply_filters('leyka_donor_phone_field_html', '<label class="input req"><input id="leyka_'.$this->full_id.'_phone" class="required phone-num mixplat-phone" type="text" value="" name="leyka_donor_phone" placeholder="'.__('Your phone number in the 7xxxxxxxxxx format', 'leyka').'" maxlength="11">
</label>
<p class="field-comment">'.__('We will use this phone number to make a mobile payment', 'leyka').'</p>
<p class="leyka_donor_phone-error field-error"></p>', $this),
        ];

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';
        $this->_processing_type = 'custom-process-submit-event';
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
            $this->full_id.'_sms_help1' => [
                'type' => 'static_text',
                'title' => __('Payments via SMS', 'leyka'),
                'is_html' => true,
                'value' => '',
                'description' => __('In order to accept SMS payments you should choose and register the keyword and shortnumber in your MIXPLAT account, as well as compose description text for the donors how to send SMS, including your keyword. Keyword registration is provided free of charge on your <a href="http://stat.mixplat.ru/sn" target=_blank>MIXPLAT account page</a>.', 'leyka'),
            ],
            $this->full_id.'_default_campaign_id' => [
                'type' => 'select',
                'default' => leyka_get_campaigns_select_default(),
                'title' => __('Campaign for SMS payments', 'leyka'),
                'comment' => __('Select a campaign to which SMS payments will be related by default.', 'leyka'),
                'list_entries' => leyka_get_campaigns_list(['orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => 1000,], true),
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
        echo wp_kses_post( apply_filters('leyka_the_content', leyka_options()->opt_safe($this->full_id.'_details')) );
    }

}

class Leyka_Mixplat_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_processing_type($value = false) {
      if($value) {
       $this->_processing_type=$value;
      }
      return $this->_processing_type;
    }

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
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-unionpay.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies = ['rub', ];
        $this->_default_currency = 'rub';
        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

class Leyka_Mixplat_Yandex_Pay extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_processing_type($value = false) {
      if($value) {
       $this->_processing_type=$value;
      }
      return $this->_processing_type;
    }

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
        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

class Leyka_Mixplat_MIR_Pay extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_processing_type($value = false) {
      if($value) {
       $this->_processing_type=$value;
      }
      return $this->_processing_type;
    }

    public function _set_attributes() {

        $this->_id = 'mirpay';
        $this->_gateway_id = 'mixplat';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Enable MIR Pay payment method', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('MIR Pay', 'leyka');
        $this->_label = __('MIR Pay', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/mir-pay.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';
        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

class Leyka_Mixplat_SBP extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_processing_type($value = false) {
      if($value) {
       $this->_processing_type=$value;
      }
      return $this->_processing_type;
    }

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
        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

function leyka_mixplat_wloader_async($tag, $handle, $src) {
        if ($handle === 'leyka-mixplat-wloader') {      // Check that 'async' isn't already declared.
        if (stripos($tag, 'async') === FALSE) {         // Insert the 'async="async"' attribute and value.
            $tag = str_replace(' src', ' async="async" src', $tag);
        }
    }
        return $tag;
}

function leyka_mixplat_set_widget_mode() {
    $widgetKey = trim(leyka_options()->opt('mixplat_widget_key'));
    if(!isset($widgetKey) || strlen($widgetKey)<32)
    {
       Leyka_Mixplat_Card::get_instance()->_set_processing_type('default');
       Leyka_Mixplat_Yandex_Pay::get_instance()->_set_processing_type('default');
       Leyka_Mixplat_MIR_Pay::get_instance()->_set_processing_type('default');
       Leyka_Mixplat_SBP::get_instance()->_set_processing_type('default');
       Leyka_Mixplat_Mobile::get_instance()->_set_processing_type('default');
    }
}

function leyka_add_gateway_mixplat() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Mixplat_Gateway::get_instance());
    leyka_mixplat_set_widget_mode();
}

add_action('leyka_init_actions', 'leyka_add_gateway_mixplat');
