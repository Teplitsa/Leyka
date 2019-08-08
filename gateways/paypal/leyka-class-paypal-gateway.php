<?php //use PayPal\Api\Amount;
//use PayPal\Api\InputFields;
//use PayPal\Api\Payer;
//use PayPal\Api\PayerInfo;
//use PayPal\Api\Payment;
//use PayPal\Api\RedirectUrls;
//use PayPal\Api\Transaction;
//use PayPal\Api\WebProfile;
//use PayPal\Rest\ApiContext;

if( !defined('WPINC') ) die;
/**
 * Leyka_Paypal_Gateway class
 */

class Leyka_Paypal_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_new_api_redirect_url = '';

    const DONATION_WEB_EXPERIENCE_PROFILE_KEY = 'leyka_paypal__default_donation_web_experience_profile_id';
//    const RECURRING_BILLING_PLAN_KEY = 'leyka_paypal__default_recurring_billing_plan_id'; /** @todo Check if the a single plan for all recurring subscriptions will work out */

    protected function _set_attributes() {

        $this->_id = 'paypal';
        $this->_title = __('PayPal', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="https://www.paypal.com/" target="_blank">PayPal</a> is a worldwide online payments system that supports online money transfers and serves as an electronic alternative to traditional paper methods like checks and money orders. The company operates as a payment processor for online vendors, auction sites, and many other commercial and non-government users.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = 'https://leyka.te-st.ru/docs/nastrojka-paypal/';
        $this->_registration_link = '//mixplat.ru/#join';

        $this->_min_commission = 2.9;
        $this->_receiver_types = array('legal', 'physical');
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = array(
            'paypal_rest_api' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Use the PayPal REST API', 'leyka'),
                'comment' => __("Check if the gateway integration should use the new REST API. If haven't used PayPal to receive payments on this website earlier, you are recommended to check the box.", 'leyka'),
                'short_format' => true,
            ),
            'paypal_api_username' => array(
                'type' => 'text',
                'title' => __('PayPal API username', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'your.name@yourmail.com'),
                'field_classes' => array('old-api'),
            ),
            'paypal_api_password' => array(
                'type' => 'text',
                'title' => __('PayPal API password', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1^2@3#&84nDsOmE5h1T'),
                'is_password' => true,
                'field_classes' => array('old-api'),
            ),
            'paypal_api_signature' => array(
                'type' => 'text',
                'title' => __('PayPal API signature', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1^2@3#&84nDsOmE5h1T'),
                'is_password' => true,
                'field_classes' => array('old-api'),
            ),
            'paypal_client_id' => array(
	            'type' => 'text',
	            'title' => __('PayPal Client ID', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS'),
            ),
            'paypal_client_secret' => array(
                'type' => 'text',
                'title' => __('PayPal Client Secret', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL'),
                'is_password' => true,
            ),
            'paypal_test_mode' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
                'field_classes' => array('old-api'),
            ),
            'paypal_enable_recurring' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Enable monthly recurring payments', 'leyka'),
                'comment' => __('Check if you want to enable monthly recurring payments.', 'leyka'),
                'short_format' => true,
            ),
            'paypal_accept_verified_only' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Accept only verified payments', 'leyka'),
                'comment' => __('Check if you want to accept payments only from verified PayPal accounts.', 'leyka'),
                'short_format' => true,
                'field_classes' => array('old-api'),
            ),
            'paypal_keep_payment_logs' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Keep detailed logs of all PayPal service operations', 'leyka'),
                'comment' => __('Check if you want to keep detailed logs of all PayPal service operations for each incoming donation.', 'leyka'),
                'short_format' => true,
                'field_classes' => array('old-api'),
            ),
        );

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['paypal_all'])) {
            $this->_payment_methods['paypal_all'] = Leyka_Paypal_All::get_instance();
        }
    }

    protected function _get_gateway_pm_id($pm_id) {

        $all_pm_ids = array('paypal_all' => 'paypal',);

        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        } else if(in_array($pm_id, $all_pm_ids)) {
            return array_search($pm_id, $all_pm_ids);
        } else {
            return false;
        }

    }

    /**
     * A helper to create & return PayPal SDK Payer object.
     *
     * @param $donation Leyka_Donation
     * @return PayPal\Api\Payer
     */
    protected function _get_payer(Leyka_Donation $donation) {
        return new \PayPal\Api\Payer(array(
            'paymentMethod' => $this->_get_gateway_pm_id($donation->pm_id),
            'payerInfo' => new \PayPal\Api\PayerInfo(array(
                'email' => $donation->donor_email,
                'firstName' => $donation->donor_name,
//                'countryCode' => 'RU',
            ))
        ));
    }

    /**
     * Create & configure the PayPal web experience profile for donations, or return the profile if it exists.
     *
     * @param $api_context PayPal\Rest\ApiContext
     * @return string|false Donation web profile ID, or false if the profile can't be created or retreived.
     */
    protected function _get_donation_web_experience_profile_id(PayPal\Rest\ApiContext $api_context) {

        $web_experience_profile_id = get_option(static::DONATION_WEB_EXPERIENCE_PROFILE_KEY);
        if($web_experience_profile_id) {

            try {
                $web_experience_profile = \PayPal\Api\WebProfile::get($web_experience_profile_id, $api_context);
            } catch( \PayPal\Exception\PayPalConnectionException $ex ) {
                /** @todo Log the error somehow */
                $web_experience_profile = false;
            }

            if($web_experience_profile) {
                return $web_experience_profile_id;
            }

        }

        // Create the PayPal web experience profile to setup donor fields on the gateway side:
        $flow_config = new \PayPal\Api\FlowConfig();
        $flow_config
            ->setLandingPageType('Billing')
            ->setUserAction('commit')
            ->setReturnUriHttpMethod('GET');

        $presentation = new \PayPal\Api\Presentation();
        $presentation
//            ->setLogoImage(leyka_options()->opt('receiver_logo')) /** @todo A receiver logo field needed. */
            ->setBrandName(get_bloginfo('name')) // For now. There can also be a campaign title
            ->setReturnUrlLabel(__('Return', 'leyka'))
            ->setNoteToSellerLabel(__('Thanks!', 'leyka'));

        $gateway_side_input_fields = new \PayPal\Api\InputFields();
        $gateway_side_input_fields->setNoShipping(1)->setAddressOverride(0)->setAllowNote(false);

        $web_experience_profile = new \PayPal\Api\WebProfile();
        $web_experience_profile
            ->setName(__('Leyka Donation', 'leyka')) // ->setId('leyka_donation') isn't gonna work here :'\
            ->setFlowConfig($flow_config)
            ->setPresentation($presentation)
            ->setTemporary(false)
            ->setInputFields($gateway_side_input_fields);

        try {

            $web_experience_profile_resp = $web_experience_profile->create($api_context);
            update_option(static::DONATION_WEB_EXPERIENCE_PROFILE_KEY, $web_experience_profile_resp->getId());

        } catch( \PayPal\Exception\PayPalConnectionException $ex ) {
            return false;
        }

        return get_option(static::DONATION_WEB_EXPERIENCE_PROFILE_KEY);

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);

        $payment_description = $donation->payment_title." (№ $donation_id)";
        if(mb_strlen($payment_description) > 127) { // 127 chars length is a PayPal restriction
            $payment_description = sprintf(__('Donation № %d', 'leyka'), $donation_id);
        }

        if(leyka_options()->opt('paypal_rest_api')) { // Payment via REST API

            require_once LEYKA_PLUGIN_DIR.'gateways/paypal/lib/autoload.php';

            $api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(
                leyka_options()->opt('paypal_client_id'),
                leyka_options()->opt('paypal_client_secret')
            ));
//            $api_context->setConfig(array('mode' => leyka_options()->opt('paypal_test_mode') ? 'sandbox' : 'live'));

            if(empty($form_data['leyka_recurring'])) { // Single donation

                // Set transaction object:
                $transaction = new \PayPal\Api\Transaction(array(
                    'amount' => new \PayPal\Api\Amount(array('total' => $donation->amount, 'currency' => 'RUB',)),
                    'description' => $payment_description,
                ));

                // Create a full payment object:
                $payment = new \PayPal\Api\Payment();
                $payment
                    ->setIntent('sale')
                    ->setPayer($this->_get_payer($donation))
                    ->setRedirectUrls(new \PayPal\Api\RedirectUrls(array( // Set redirect URLs
                        'returnUrl' => home_url('/leyka/service/paypal/process-donation'),
                        'cancelUrl' => home_url('/leyka/service/paypal/cancel-donation'),
                    )))
                    ->setTransactions(array($transaction));

                $web_experience_profile_id = $this->_get_donation_web_experience_profile_id($api_context);
                if($web_experience_profile_id) {
                    $payment->setExperienceProfileId($web_experience_profile_id);
                }

                try { // Create payment with valid API context

                    $payment->create($api_context);
                    $donation->paypal_payment_id = $payment->getId();
                    $donation->paypal_token = $payment->getToken();

                    $this->_new_api_redirect_url = $payment->getApprovalLink(); // PayPal redirect URL for the donor

                } catch(Exception $ex) {

                    $donation->add_gateway_response($ex);

                    leyka()->add_payment_form_error(new WP_Error(
                        $this->_id.'-'.$ex->getCode(),
                        sprintf(__('Error: %s', 'leyka'), $ex->getMessage())
                    ));

                }

            } else { // Init recurring donation

                $donation->payment_type = 'rebill';

                // 1. Create a "billing plan" (BP) for the recurring subscription.
                // BP defines details for subscription payments, like their amount and frequency:

                $recurring_amount = new \PayPal\Api\Currency(array('value' => $donation->amount, 'currency' => 'RUB',));

                // "Payment definition" - the 1st part of the recurring subscription settings:
                $payment_definition = new \PayPal\Api\PaymentDefinition(array(
                    'name' => __('Monthly recurring donations', 'leyka'),
                    'type' => 'REGULAR',
                    'frequency' => 'MONTH', // DAY|WEEK|MONTH
                    'frequencyInterval' => '1',
                    'amount' => $recurring_amount, // Only for recurring payments
                ));

                // "Merchant preferences" - the 2nd part of the recurring subscription settings:
                $merchant_preferences = new \PayPal\Api\MerchantPreferences(array(
                    'returnUrl' => home_url('/leyka/service/paypal/process-init-recurring'),
                    'cancelUrl' => home_url('/leyka/service/paypal/cancel-init-recurring'),
                    'autoBillAmount' => 'YES',
                    'initialFailAmountAction' => 'CANCEL',
                    'maxFailAttempts' => '0', // Infinite number of attempts
                    'setupFee' => $recurring_amount, // Only for initial payment
                ));

                $plan = new \PayPal\Api\Plan(array( // Create a subscription billing plan
                    'name' => __('Recurring donations', 'leyka'),
                    'description' => __('Leyka - a monthly recurring donations subscription', 'leyka'),
                    'type' => 'INFINITE',
                    'paymentDefinitions' => array($payment_definition),
                    'merchantPreferences' => $merchant_preferences,
                ));

                try {

                    $plan_created = $plan->create($api_context);

                    // We cannot create an active billing plan, so activate the plan manually:
                    $patch_request = new \PayPal\Api\PatchRequest();
                    $patch_request->addPatch(new \PayPal\Api\Patch(array(
                        'op' => 'replace',
                        'path' => '/',
                        'value' => new \PayPal\Common\PayPalModel('{"state":"ACTIVE"}')
                    )));

                    $plan_created->update($patch_request, $api_context);
                    $plan = \PayPal\Api\Plan::get($plan_created->getId(), $api_context);

                    if($plan->getState() !== 'ACTIVE') {
                        throw new Exception(
                            __('Cannot activate PayPal billing plan.', 'leyka').' '.
                            sprintf(__('Please contact the <a href="%s" target="_blank">website tech. support</a>', leyka_options()->opt('tech_support_email')), 'leyka')
                        );
                    }

                } catch(Exception $ex) {

                    $donation->add_gateway_response($ex);

                    leyka()->add_payment_form_error(new WP_Error(
                        $this->_id.'-'.$ex->getCode(),
                        sprintf(__('Error: %s', 'leyka'), $ex->getMessage().'<pre>'.print_r($ex, 1).'</pre>')
                    ));

                }

                // 2. Subscription BP created - create a "billing agreement" (BA) for it.
                // BA aggregates data on payer (donor) and subscription "runtime" params.
                // BA also manages initial donation for subscription and activates it:

                $agreement = new \PayPal\Api\Agreement(array(
                    'name' => sprintf(__('%s - recurring donations', 'leyka'), $donation->payment_title),
                    'description' => __('Recurring donations', 'leyka'),
                    'startDate' => date(DATE_ISO8601, strtotime('+1 hour')), // Required & should be greater than current date
                    'plan' => new \PayPal\Api\Plan(array('id' => $plan->getId())),
                    'payer' => $this->_get_payer($donation),
                ));

                try {

                    // PayPal redirect URL for the donor:
                    $redirect_link = $agreement->create($api_context)->getApprovalLink();
                    $payment_token = end(explode('&token=', $redirect_link));

                    if( !$payment_token ) {
                        throw new Exception(
                            __("The needed payment parameter wasn't generated.", 'leyka').' '.
                            sprintf(__('Please contact the <a href="%s" target="_blank">website tech. support</a>', leyka_options()->opt('tech_support_email')), 'leyka')
                        );
                    }

                    $donation->paypal_billing_plan_id = $plan->getId(); // Save the donation plan ID to identify later
                    $donation->paypal_token = $payment_token; // Save the donation plan ID to identify later

                    $this->_new_api_redirect_url = $redirect_link;

                } catch(Exception $ex) {

                    $donation->add_gateway_response($ex);

                    leyka()->add_payment_form_error(new WP_Error(
                        $this->_id.'-'.$ex->getCode(),
                        sprintf(__('Error: %s', 'leyka'), $ex->getMessage())
                    ));

                }

                // 2.5. BA is created, but it's not executed yet. Right now it doesn't even have an ID.
                // BA will be executed on the "process-init-recurring" callback procedure.

            }

            return;

        }

        // (Old) Express Checkout payment:

        $campaign_post = get_post($donation->campaign_id);
        $donation->payment_type = empty($_POST['leyka_recurring']) ? 'single' : 'rebill';

        if($donation->payment_type === 'rebill') {
            $data = apply_filters('leyka_paypal_submission_data', array(
                'USER' => leyka_options()->opt('paypal_api_username'),
                'PWD' => leyka_options()->opt('paypal_api_password'),
                'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                'VERSION' => 204,
                'METHOD' => 'SetExpressCheckout',
                'EMAIL' => $donation->donor_email,
                // 'USERSELECTEDFUNDINGSOURCE' => 'CreditCard', // WARNING: it may be a default PM! (CreditCard, QIWI, ELV)
                'BRANDNAME' => html_entity_decode(leyka_options()->opt('org_full_name'), ENT_COMPAT, 'UTF-8'),
                'LOCALECODE' => 'RU',
                'RETURNURL' => apply_filters(
                    'leyka_paypal_process_payment_callback_url',
                    home_url('?p=leyka/service/'.$this->_id.'/process_payment/')
                ),
                'CANCELURL' => leyka_get_failure_page_url($donation->campaign_id),
                'PAYMENTREQUEST_0_NOTIFYURL' => apply_filters(
                    'leyka_paypal_ipn_callback_url',
                    home_url('?p=leyka/service/'.$this->_id.'/ipn/')
                ),
                'PAYMENTREQUEST_0_INVNUM' => $donation_id,
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_0_AMT' => $donation->amount,
                'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
                'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
                'PAYMENTREQUEST_0_DESC' => $payment_description,
                'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
                'L_PAYMENTREQUEST_0_ITEMURL0' => get_permalink($campaign_post),
                'L_PAYMENTREQUEST_0_DESC0' => $payment_description,
                'L_PAYMENTREQUEST_0_AMT0' => $donation->amount,
                'NOSHIPPING' => 1,
                'L_BILLINGTYPE0'=>'RecurringPayments',
                'L_BILLINGAGREEMENTDESCRIPTION0'=>$payment_description
//                'LOGOIMG' => 'https://sandbox.paypal.com/logo.png',
//                'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
//                'L_BILLINGAGREEMENTDESCRIPTION0' => 'Recurring Donations', // WARNING: if L_BILLINGTYPE0 is set, it is necessary
                /** @todo // Logo in the cart page header, HTTPS only. Add the gateway parameter for it. */
            ), $pm_id, $donation_id, $form_data);
        } else {
            $data = apply_filters('leyka_paypal_submission_data', array(
                'USER' => leyka_options()->opt('paypal_api_username'),
                'PWD' => leyka_options()->opt('paypal_api_password'),
                'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                'VERSION' => 204,
                'METHOD' => 'SetExpressCheckout',
                'EMAIL' => $donation->donor_email,
                // 'SOLUTIONTYPE' => 'Sole',
                // 'LANDINGPAGE' => 'Billing',
                // 'CHANNELTYPE' => 'Merchant',
                // 'USERSELECTEDFUNDINGSOURCE' => 'CreditCard', // WARNING: it may be a default PM! (CreditCard, QIWI, ELV)
                'BRANDNAME' => html_entity_decode(leyka_options()->opt('org_full_name'), ENT_COMPAT, 'UTF-8'),
                'LOCALECODE' => 'RU',
                'RETURNURL' => apply_filters(
                    'leyka_paypal_recurring_process_payment_callback_url',
                    home_url('?p=leyka/service/'.$this->_id.'/process_payment/')
                ),
                'CANCELURL' => leyka_get_failure_page_url($donation->campaign_id),
                'PAYMENTREQUEST_0_NOTIFYURL' => apply_filters(
                    'leyka_paypal_recurring_ipn_callback_url',
                    home_url('?p=leyka/service/'.$this->_id.'/ipn/')
                ),
                'PAYMENTREQUEST_0_INVNUM' => $donation_id,
                'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                'PAYMENTREQUEST_0_AMT' => $donation->amount,
                'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
                'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
                'PAYMENTREQUEST_0_DESC' => $payment_description,
                'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
                'L_PAYMENTREQUEST_0_ITEMURL0' => get_permalink($campaign_post),
                'L_PAYMENTREQUEST_0_DESC0' => $payment_description,
                'L_PAYMENTREQUEST_0_AMT0' => $donation->amount,
                'NOSHIPPING' => 1,
//                'LOGOIMG' => 'https://sandbox.paypal.com/logo.png',
//                'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
//                'L_BILLINGAGREEMENTDESCRIPTION0' => 'Recurring Donations', // WARNING: if L_BILLINGTYPE0 is set, it is necessary
                /** @todo // Logo in the cart page header, HTTPS only. Add the gateway parameter for it. */
            ), $pm_id, $donation_id, $form_data);
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->submission_redirect_url('', $pm_id),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
        ));

        if( !$result_str = curl_exec($ch) ) {
            $this->_donation_error(
                __('PayPal - payment error occured', 'leyka'),
                sprintf(__("SetExpressCheckout request to PayPal system couldn't be made due to some error.\n\nThe error: %s", 'leyka'), curl_error($ch).' ('.curl_errno($ch).')'),
                $donation,
                'SetEC',
                $data
            );
        }

        parse_str($result_str, $result);
        curl_close($ch);

        if(isset($result['CORRELATIONID'])) {
            $donation->paypal_correlation_id = $result['CORRELATIONID'];
        }

        if(empty($result['ACK']) || $result['ACK'] != 'Success') {
            $this->_donation_error(
                __('PayPal - payment error occured', 'leyka'),
                sprintf(__("SetExpressCheckout request to PayPal system returned some error. The details of the request are below.\n\nPayment error code: %s\nPayment error message: %s\nPayment error description: %s", 'leyka'), $result['L_ERRORCODE0'], $result['L_SHORTMESSAGE0'], $result['L_LONGMESSAGE0']),
                $donation,
                'SetEC',
                $data
            );
        } elseif(empty($result['TOKEN'])) {
            $this->_donation_error(
                __('PayPal - payment error occured', 'leyka'),
                sprintf(__("SetExpressCheckout request to PayPal system returned without TOKEN param.\n\nFull PayPal response: %s", 'leyka'), print_r($result, 1)),
                $donation,
                'SetEC',
                $data
            );
        } else {

            $donation->paypal_token = $result['TOKEN'];
            $this->_add_to_payment_log($donation, 'SetEC', $data, $result);

            $paypal_login_url = leyka_options()->opt('paypal_test_mode') ?
                'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$result['TOKEN'] :
                'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$result['TOKEN'];

            wp_redirect($paypal_login_url);
            exit(0);

        }

    }

    public function submission_redirect_url($current_url, $pm_id) {

        if(leyka_options()->opt('paypal_rest_api')) {
            return $this->_new_api_redirect_url;
        }

        if(empty($current_url)) {
            $current_url = leyka_get_current_url();
        }

        switch($pm_id) {
            case 'paypal_all':
                return leyka_options()->opt('paypal_test_mode') ?
                    'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
            default:
                return $current_url;
        }

    }

    // We don't need a redirection form vars on PayPal, so this filter method is empty:
    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {
        return array();
    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            // (New) REST API "callbacks":
            case 'process-donation': // Complete the payment

                require LEYKA_PLUGIN_DIR.'gateways/paypal/lib/autoload.php';

                $api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(
                    leyka_options()->opt('paypal_client_id'),
                    leyka_options()->opt('paypal_client_secret')
                ));

                $_GET['paymentId'] = esc_sql($_GET['paymentId']);
                $_GET['PayerID'] = esc_sql($_GET['PayerID']);

                if(empty($_GET['paymentId']) || empty($_GET['PayerID'])) {
                    $this->_donation_error(__("PayPal callback error: required parameters weren't given", 'leyka'));
                }

                $donation = $this->_get_donation_by('paypal_payment_id', $_GET['paymentId']);
                if( !$donation ) {
                    $this->_donation_error(
                        __("PayPal callback error: donation wasn't found", 'leyka'),
                        sprintf(__('The donation was not found by the following PayPal data: %s (value given: %s), %s (value given: %s).', 'leyka'), 'paymentId', $_GET['paymentId'], 'PayerID', $_GET['PayerID'])
                    );
                }

                try {

                    $payment = \PayPal\Api\Payment::get($_GET['paymentId'], $api_context);
                    $execution = new \PayPal\Api\PaymentExecution(array('payerId' => $_GET['PayerID'],));

                    /** @var PayPal\Api\Payment $result */
                    $result = $payment->execute($execution, $api_context);
                    if($result->getState() === 'approved') {

                        if($donation->status !== 'funded') {

                            $donation->status = 'funded';
                            $donation->add_gateway_response($result);

                            Leyka_Donation_Management::send_all_emails($donation->id);

                        }

                        wp_redirect(leyka_get_success_page_url());
                        exit;

                    } else if($result->getState() === 'failed') {
                        $this->_donation_error(
                            __('PayPal donation finished with error', 'leyka'),
                            '',
                            $donation,
                            'process-donation',
                            array('paymentId' => $_GET['paymentId'], 'PayerID' => $_GET['PayerID'],)
                        );
                    }

                } catch( \PayPal\Exception\PayPalConnectionException $ex ) {
                    $this->_donation_error(
                        __('PayPal donation execution resulted with error', 'leyka'),
                        sprintf(__('Error %s: %s', 'leyka'), $this->_id.'-'.$ex->getCode(), $ex->getMessage()),
                        $donation,
                        'process-donation',
                        $ex
                    );
                } /*catch(Exception $ex) {
                    $this->_donation_error(
                        __('PayPal donation execution resulted with error', 'leyka'),
                        sprintf(__('Error %s: %s', 'leyka'), $this->_id.'-'.$ex->getCode(), $ex->getMessage()),
                        $donation,
                        'process-donation',
                        $ex
                    );
                }*/
                break;

            case 'cancel-donation': // Payment was cancelled by Donor on the gateway side

                $redirect_url = home_url();

                if( !empty($_GET['token']) ) {

                    $donation = $this->_get_donation_by('paypal_token', esc_sql($_GET['token']));
                    if( $donation && $donation->campaign_id ) {

                        $donation->add_gateway_response(__('The donation was cancelled by the donor', 'leyka'));
                        $redirect_url = get_permalink($donation->campaign_id);

                    }

                }

                wp_redirect($redirect_url);
                exit;

            case 'process-init-recurring':

                require LEYKA_PLUGIN_DIR.'gateways/paypal/lib/autoload.php';

                // 3. Execute the Billing Agreement (BA) using the Token sent by PayPal.
                // When BA is executed, get it's Donation by the Billing Plan ID saved earlier, and turn it to "funded".

                $_GET['token'] = esc_sql($_GET['token']);

                if(empty($_GET['token'])) {
                    $this->_donation_error(__("PayPal callback error: required parameters weren't given", 'leyka'));
                }

                $api_context = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(
                    leyka_options()->opt('paypal_client_id'),
                    leyka_options()->opt('paypal_client_secret')
                ));
                // $api_context->setConfig(array('mode' => leyka_options()->opt('paypal_test_mode') ? 'sandbox' : 'live'));

                try {

                    $agreement = new \PayPal\Api\Agreement();
                    $agreement->execute($_GET['token'], $api_context); // Execute the BA
                    $agreement = \PayPal\Api\Agreement::get($agreement->getId(), $api_context); // Final BA check

                    $agreement_id = $agreement->getId();

                    if($agreement_id) {

                        $donation = $this->_get_donation_by('paypal_token', $_GET['token']);

                        if( !$donation ) {
                            $this->_donation_error(
                                __("PayPal callback error: can't find the initial recurring donation to activate", 'leyka'),
                                sprintf(
                                    __("Payment token: %s\n\nBilling Agreement ID: %s\n\n", 'leyka'),
                                    $_GET['token'],
                                    $agreement_id
                                )
                            );
                        }

                        echo '<pre>Agreement ID: '.print_r($agreement_id, 1).'</pre>';
                        echo '<pre>'.print_r('Init recurring donation found: '.$donation->id, 1).'</pre>';

                        $donation->paypal_billing_agreement_id = $agreement_id;
                        $donation->paypal_payment_token = $_GET['token'];

                        if(/*$agreement->getState() == 'Active' &&*/ $donation->status !== 'funded') {

//                            $donation->status = 'funded'; /** @todo IT'S BETTER TO MAKE SUBSCRIPTION "FUNDED" IN THE WEBHOOK CALLBACK HANDLING */

                            $donation->recurring_is_active = true;
                            $donation->add_gateway_response($agreement);

                            Leyka_Donation_Management::send_all_emails($donation->id);

                        }

//                        wp_redirect(leyka_get_success_page_url());
                        exit;

                    } else {
                        $this->_donation_error(__("PayPal callback error: the recurring subscription billing agreement final check wasn't passed.", 'leyka'));
                    }

                } catch(Exception $ex) {
                    $this->_donation_error(__("PayPal callback error: billing agreement for the recurring subscription wasn't executed.", 'leyka'));
                }

                break;

            // Classic (ExpressCheckout) API callbacks:
            case 'process_payment': // Do a payment itself

                if(empty($_GET['token']) || empty($_GET['PayerID'])) {
                    return false;
                }

                $donation = $this->_get_donation_by('paypal_token', $_GET['token']);

                if( !$donation ) {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("Process_payment callback request to Leyka system was made with an unknown token parameter.\n\nToken given by PayPal system: %s", 'leyka'), $_GET['token'])
                    );
                }

                // 1. GetExpressCheckoutDetails call:

                $data = apply_filters('leyka_paypal_get_ec_details_data', array(
                    'USER' => leyka_options()->opt('paypal_api_username'),
                    'PWD' => leyka_options()->opt('paypal_api_password'),
                    'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                    'VERSION' => 204,
                    'METHOD' => 'GetExpressCheckoutDetails',
                    'TOKEN' => $_GET['token'],
                ), $donation);

                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $this->submission_redirect_url('', 'paypal_all'), // "paypal_all" is a PM id
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_VERBOSE => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 60,
                ));

                if( !$result_str = curl_exec($ch) ) {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("GetExpressCheckoutDetails request to PayPal system couldn't be made due to some error.\n\nThe error: %s", 'leyka'), curl_error($ch).' ('.curl_errno($ch).')'),
                        $donation,
                        'GetECDetails',
                        $data
                    );
                }

                parse_str($result_str, $result);
                curl_close($ch);

                if(empty($result['ACK']) || $result['ACK'] != 'Success') {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("GetExpressCheckoutDetails request to PayPal system returned without success status.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                        $donation,
                        'GetECDetails',
                        $data
                    );
                }

                // Filter out payments from unverified accounts, if needed:
                if(
                    !leyka_options()->opt('paypal_test_mode') &&
                    leyka_options()->opt('paypal_accept_verified_only') &&
                    (empty($result['PAYERSTATUS']) || $result['PAYERSTATUS'] != 'verified')
                ) { // We don't accept payments from unverified
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("GetExpressCheckoutDetails request to PayPal system returned without verified payer status.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                        $donation,
                        'GetECDetails',
                        $data
                    );
                }

                // $result['COUNTRYCODE'] can be checked too, if needed...

                $this->_add_to_payment_log($donation, 'GetECDetails', $data, $result);
                $donation->paypal_payer_id = $_GET['PayerID'];

                // 2. DoExpressCheckoutPayment call:

                $payment_description = $donation->payment_title." (№ {$donation->id})";
                if(strlen($payment_description) > 127) { // 127 chars length is a restriction from PayPal
                    $payment_description = sprintf(__('Donation № %d', 'leyka'), $donation->id);
                }

                $campaign_post = get_post($donation->campaign_id);

                if($donation->payment_type === 'rebill') {

                    $data = apply_filters('leyka_paypal_do_ec_payment_data', array(
                        'USER' => leyka_options()->opt('paypal_api_username'),
                        'PWD' => leyka_options()->opt('paypal_api_password'),
                        'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                        'VERSION' => '204',
                        'METHOD' => 'CreateRecurringPaymentsProfile',
                        'TOKEN' => $_GET['token'],
                        'PAYERID' => $_GET['PayerID'],
                        'AMT' => $donation->amount,
                        'CURRENCYCODE' => 'RUB',
                        'DESC' => $payment_description,
                        'BILLINGPERIOD' => 'Month',
                        'BILLINGFREQUENCY' => '1',
                        'PROFILESTARTDATE' => Date(DateTime::ISO8601, strtotime("+1 Month")),
                    ), $donation);

                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $this->submission_redirect_url('', 'paypal_all'), // "paypal_all" is a PM id
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => http_build_query($data),
                        CURLOPT_VERBOSE => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => 60,
                    ));

                    if( !$result_str = curl_exec($ch) ) {
                        $this->_donation_error(
                            __('PayPal - CreateRecurringPaymentsProfile error occured', 'leyka'),
                            sprintf(__("CreateRecurringPaymentsProfile request to PayPal system couldn't be made due to some error.\n\nThe error: %s", 'leyka'), curl_error($ch).' ('.curl_errno($ch).')'),
                            $donation,
                            'DoECPayment',
                            $data
                        );
                    }

                    parse_str($result_str, $result);
                    curl_close($ch);

                    if(empty($result['ACK']) || $result['ACK'] != 'Success') {
                        $this->_donation_error(
                            __('PayPal - CreateRecurringPaymentsProfile error occured', 'leyka'),
                            sprintf(__("CreateRecurringPaymentsProfile request to PayPal system returned without success status.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                            $donation,
                            'DoECPayment',
                            $data
                        );
                    }

                    if(empty($result['PROFILESTATUS']) || $result['PROFILESTATUS'] != 'ActiveProfile') {
                        $this->_donation_error(
                            __('PayPal - CreateRecurringPaymentsProfile error occured', 'leyka'),
                            sprintf(__("CreateRecurringPaymentsProfile request to PayPal system reported about the error: a RecurringPaymentsProfile status is not ActiveProfile.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                            $donation,
                            'DoECPayment',
                            $data
                        );
                    }

                }

                $data = apply_filters('leyka_paypal_do_ec_payment_data', array(
                    'USER' => leyka_options()->opt('paypal_api_username'),
                    'PWD' => leyka_options()->opt('paypal_api_password'),
                    'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                    'VERSION' => 204,
                    'METHOD' => 'DoExpressCheckoutPayment',
                    'TOKEN' => $_GET['token'],
                    'PAYERID' => $_GET['PayerID'],
                    'PAYMENTREQUEST_0_NOTIFYURL' => home_url('?p=leyka/service/'.$this->_id.'/ipn/'),
                    'PAYMENTREQUEST_0_INVNUM' => $donation->id,
                    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                    'PAYMENTREQUEST_0_AMT' => $donation->amount,
                    'PAYMENTREQUEST_0_ITEMAMT' => $donation->amount,
                    'PAYMENTREQUEST_0_CURRENCYCODE' => 'RUB',
                    'PAYMENTREQUEST_0_DESC' => $payment_description,
                    'L_PAYMENTREQUEST_0_NAME0' => $donation->payment_title,
                    'L_PAYMENTREQUEST_0_ITEMURL0' => get_permalink($campaign_post),
                    'L_PAYMENTREQUEST_0_DESC0' => $payment_description,
                    'L_PAYMENTREQUEST_0_AMT0' => $donation->amount,
                    'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => 'Digital',
                    'NOSHIPPING' => 1,
                ), $donation);

                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $this->submission_redirect_url('', 'paypal_all'), // "paypal_all" is a PM id
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($data),
                    CURLOPT_VERBOSE => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 60,
                ));

                if( !$result_str = curl_exec($ch) ) {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("DoExpressCheckoutPayment request to PayPal system couldn't be made due to some error.\n\nThe error: %s", 'leyka'), curl_error($ch).' ('.curl_errno($ch).')'),
                        $donation,
                        'DoECPayment',
                        $data
                    );
                }

                parse_str($result_str, $result);
                curl_close($ch);

                if(empty($result['ACK']) || $result['ACK'] != 'Success') {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("DoECPayment request to PayPal system returned without success status.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                        $donation,
                        'DoECPayment',
                        $data
                    );
                }

                if(empty($result['PAYMENTINFO_0_PAYMENTSTATUS'])) {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("DoECPayment request to PayPal system reported about some transaction error: a payment status is empty.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                        $donation,
                        'DoECPayment',
                        $data
                    );
                }

                // IPR checks, if needed:
                if(
                    $result['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Pending' &&
                    $result['PAYMENTINFO_0_PENDINGREASON'] == 'PaymentReview'
                ) {

                    $donation->add_gateway_response($result);
                    $this->_add_to_payment_log($donation, 'DoECPayment', $data, $result);
                    wp_redirect(leyka_get_success_page_url($donation->campaign_id));

                    // Do not fund a donation here! Wait for it's approval and IPN callback

                } else if($result['PAYMENTINFO_0_PAYMENTSTATUS'] != 'Completed') {
                    $this->_donation_error(
                        __('PayPal - payment error occured', 'leyka'),
                        sprintf(__("DoECPayment request to PayPal system reported about some transaction error: a payment status isn't 'Completed'.\n\nThe request result: %s", 'leyka'), print_r($result, 1)),
                        $donation,
                        'DoECPayment',
                        $data
                    );
                } else {

                    $donation->status = 'funded';
                    $donation->add_gateway_response($result);
                    Leyka_Donation_Management::send_all_emails($donation->id);

                    $this->_add_to_payment_log($donation, 'DoECPayment', $data, $result);
                    wp_redirect(leyka_get_success_page_url($donation->campaign_id));

                }

                break;

            // Classic (ExpressCheckout) API callbacks:
            case 'ipn': // Instant payment notifications processing: confirm the payment

                require_once 'leyka-paypal-tools-ipn-verificator.php';

                // Reply with an empty 200 response to indicate to paypal that the IPN was received correctly:
                header('HTTP/1.1 200 OK');

                // Verify an IPN:
                $ipn = new PayPalIPN(leyka_options()->opt('paypal_test_mode'));
                $result = $ipn->verifyIPN();
                if($result !== true) {
                    $this->_donation_error(
                        __('PayPal - IPN processing error occured', 'leyka'),
                        __("Leyka reported about an IPN processing error. The details are below:\n\n", 'leyka').$result,
                        NULL, 'IPN', $_POST, '', false
                    );
                }

                // Donation ID. If missing, it may be a wrong IPN call:
                if(empty($_POST['invoice']) || (int)$_POST['invoice'] <= 0) {
                    exit(0);
                }

                if(empty($_POST['txn_id'])) { // IPN transaction ID
                    $this->_donation_error(
                        __('PayPal - IPN processing error occured', 'leyka'),
                        __("Leyka reported about an IPN processing error: the 'txn_id' parameter is missing.\n\nIPN POST data: %s", 'leyka').print_r($_POST, 1),
                        NULL, 'IPN', $_POST, '', false
                    );
                }

                $donation = new Leyka_Donation((int)$_POST['invoice']);
                $_POST['txn_id'] = (int)$_POST['txn_id'];

                // This IPN was already processed:
                if($donation->last_ipn_transaction_id && $donation->last_ipn_transaction_id == $_POST['txn_id'] ) {
                    exit(0);
                }

                $donation->last_ipn_transaction_id = $_POST['txn_id'];

                if( !empty($_POST['payer_id']) ) {
                    $donation->paypal_payer_id = esc_attr($_POST['payer_id']);
                }

                if(
                    !$donation->donor_name
                    && ( !empty($_POST['first_name']) || !empty($_POST['last_name']) )
                ) {
                    $donation->donor_name = $_POST['first_name'].' '.$_POST['last_name'];
                }

                if( !empty($_POST['payment_status']) && $_POST['payment_status'] == 'Completed' ) {

                    if(
                        !leyka_options()->opt('paypal_accept_verified_only')
                        || $_POST['payer_status'] == 'verified'
                    ) {

                        $donation->status = 'funded';
                        $donation->add_gateway_response($_POST);
                        $this->_add_to_payment_log($donation, 'IPN', $_POST);

                        Leyka_Donation_Management::send_all_emails($donation->id);

                    }

                } else if(
                    !empty($_POST['payment_status'])
                    && in_array($_POST['payment_status'], array('Pending', 'In-Progress'))
                ) {

                    $donation->status = 'submitted';
                    $donation->add_gateway_response($_POST);
                    $this->_add_to_payment_log($donation, 'IPN', $_POST);

                } else if(
                    !empty($_POST['payment_status'])
                    && in_array($_POST['payment_status'], array('Refunded', 'Reversed'))
                ) {

                    $donation->status = 'refunded';
                    $donation->add_gateway_response($_POST);
                    $this->_add_to_payment_log($donation, 'IPN', $_POST);

                } else {

                    $donation->status = 'failed';
                    $donation->add_gateway_response($_POST);
                    $this->_add_to_payment_log($donation, 'IPN', $_POST);

                }

                break;

            case 'donation_update':

                if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_payment_form')) {
                    die(json_encode(array(
                        'status' => 1,
                        'message' => __('Wrong nonce in submitted form data', 'leyka'),
                    )));
                }

                $_POST['donation_id'] = (int)$_POST['donation_id'];

                if( !$_POST['donation_id']) {
                    die(json_encode(array(
                        'status' => 1,
                        'message' => __('No donation ID in submitted payment data', 'leyka'),
                    )));
                }

                $donation = new Leyka_Donation($_POST['donation_id']);

                if( !$donation ) {
                    die(json_encode(array(
                        'status' => 1,
                        'message' => __('Wrong donation ID in submitted payment data', 'leyka'),
                    )));
                }

                if($donation->gateway_id !== $this->_id) {
                    die(json_encode(array(
                        'status' => 1,
                        'message' => __('Wrong gateway in submitted payment data', 'leyka'),
                    )));
                }

                if( $_POST['paypal_token'] && !$donation->paypal_token ) {
                    $donation->paypal_token = esc_attr($_POST['paypal_token']);
                }

                if( $_POST['paypal_payment_id'] && !$donation->paypal_payment_id ) {
                    $donation->paypal_payment_id = esc_attr($_POST['paypal_payment_id']);
                }

                die(json_encode(array('status' => 0,)));

                break;

            default:

        }

        exit(0);

    }

    // Override the auto-submit setting to send manual requests to PayPal:
//    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
//        return false;
//    }

    public function enqueue_gateway_scripts() {

        if( !Leyka_Paypal_All::get_instance()->active ) {
            return;
        }

//        wp_enqueue_script('leyka-paypal-api', 'https://www.paypalobjects.com/api/checkout.min.js');

        $dependencies = array('jquery', /*'leyka-paypal-api',*/);

	    // If Revo template is in use:
	    if(leyka_revo_template_displayed() || leyka_success_widget_displayed() || leyka_failure_widget_displayed()) {
		    $dependencies[] = 'leyka-revo-public';
	    }

	    $dependencies[] = 'leyka-public';

        wp_enqueue_script(
            'leyka-paypal-front',
            LEYKA_PLUGIN_BASE_URL.'gateways/'.self::get_instance()->id.'/js/leyka.paypal.js',
	        $dependencies,
            LEYKA_VERSION,
            true
        );

	    add_filter('leyka_revo_template_final_submit', array($this, 'get_gateway_submit'));
	    add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));

    }

	public function localize_js_strings(array $js_data) {
		return array_merge($js_data, array(
			'paypal_locale' => get_locale(),
            'paypal_client_id' => leyka_options()->opt('paypal_client_id'),
            'paypal_is_test_mode' => !!leyka_options()->opt('paypal_test_mode'),
            'success_page_url' => leyka_get_success_page_url(),
            'failure_page_url' => leyka_get_failure_page_url(),
            'paypal_accept_verified_only' => !!leyka_options()->opt('paypal_accept_verified_only'),
            'paypal_ipn_callback_url' => get_option('permalink-structure') ?
                home_url('leyka/service/'.$this->_id.'/ipn/') : home_url('?p=leyka/service/'.$this->_id.'/ipn/'),
			'paypal_donation_update_callback_url' => get_option('permalink-structure') ?
                home_url('leyka/service/'.$this->_id.'/donation_update/') : home_url('?p=leyka/service/'.$this->_id.'/donation_update/'),
			'paypal_payment_process_error' => __('Error while processing the payment on PayPal side: %s. Your money will remain intact. Please report to the website tech support.', 'leyka'),
			'ajax_wrong_server_response' => __('Error in server response. Your money will remain intact. Please report to the website tech support.', 'leyka'),
			'ajax_donation_not_created' => __('Error while creating donation. Your money will remain intact. Please report to the website tech support.', 'leyka'),
			'paypal_donation_failure_reasons' => array(
			    'Error: Client ID not found for env: sandbox' => __("Either PayPal sandbox Client ID is wrong, or sandbox wasn't created.", 'leyka'),
            ),
		));
	}

    public function get_gateway_submit($default_submit) {
        return $default_submit.'<div class="leyka-paypal-form-submit" style="display: none;"></div>';
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $response_vars = maybe_unserialize($donation->gateway_response);
        if( !$response_vars || !is_array($response_vars) ) {
            return array();
        }

        return $response_vars;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);?>

            <label><?php _e('PayPal token', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="paypal-token" name="paypal-token" placeholder="<?php _e('Enter PayPal token', 'leyka');?>" value="<?php echo $donation->paypal_token;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->paypal_token;?></span>
                <?php }?>
            </div>

            <label><?php _e('PayPal correlation ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="paypal-correlation-id" name="paypal-correlation-id" placeholder="<?php _e('Enter PayPal correlation ID', 'leyka');?>" value="<?php echo $donation->paypal_correlation_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->paypal_correlation_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('PayPal payment ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="paypal-payment-id" name="paypal-payment-id" placeholder="<?php _e('Enter PayPal payment ID', 'leyka');?>" value="<?php echo $donation->paypal_payment_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->paypal_payment_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('PayPal Payer ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                <input type="text" id="paypal-payer-id" name="paypal-token" placeholder="<?php _e('Enter PayPal Payer ID', 'leyka');?>" value="<?php echo $donation->paypal_payer_id;?>">
                <?php } else {?>
                <span class="fake-input"><?php echo $donation->paypal_payer_id;?></span>
                <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="paypal-token"><?php _e('PayPal token', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="paypal-token" name="paypal-token" placeholder="<?php _e('Enter PayPal token', 'leyka');?>" value="">
            </div>

            <label for="paypal-correlation-id"><?php _e('PayPal correlation ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="paypal-correlation-id" name="paypal-correlation-id" placeholder="<?php _e('Enter PayPal correlation ID', 'leyka');?>" value="">
            </div>

            <label for="paypal-payer-id"><?php _e('PayPal Payer ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="paypal-payer-id" name="paypal-payer-id" placeholder="<?php _e('Enter PayPal Payer ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {
        switch($field_name) {
            case 'paypal_token':
            case 'pp_token': return get_post_meta($donation->id, '_paypal_token', true);
            case 'paypal_correlation_id':
            case 'pp_correlation_id': return get_post_meta($donation->id, '_paypal_correlation_id', true);
            case 'paypal_payment_id':
            case 'pp_payment_id': return get_post_meta($donation->id, '_paypal_payment_id', true);
            case 'paypal_payer_id':
            case 'pp_payer_id': return get_post_meta($donation->id, '_paypal_payer_id', true);
            case 'paypal_payment_history':
            case 'paypal_history':
            case 'pp_history':
            case 'paypal_payment_log':
            case 'paypal_log':
            case 'pp_log': return get_post_meta($donation->id, '_paypal_payment_log', true);
            case 'last_ipn_transaction_id': return get_post_meta($donation->id, '_paypal_ipn_txn_id', true);

            // Only for the REST API:
            case 'paypal_billing_plan_id': return get_post_meta($donation->id, '_paypal_billing_plan_id', true); break;
            case 'paypal_billing_agreement_id': return get_post_meta($donation->id, '_paypal_billing_agreement_id', true); break;

            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {
        switch($field_name) {
            case 'paypal_token':
            case 'paypal_payment_token':
            case 'pp_token':
            case 'pp_payment_token': update_post_meta($donation->id, '_paypal_token', $value); break;
            case 'paypal_correlation_id':
            case 'pp_correlation_id': update_post_meta($donation->id, '_paypal_correlation_id', $value); break;
            case 'paypal_payment_id':
            case 'pp_payment_id': update_post_meta($donation->id, '_paypal_payment_id', $value); break;
            case 'paypal_payer_id':
            case 'pp_payer_id': update_post_meta($donation->id, '_paypal_payer_id', $value); break;
            case 'paypal_payment_history':
            case 'paypal_history':
            case 'pp_history':
            case 'paypal_payment_log':
            case 'paypal_log':
            case 'pp_log': update_post_meta($donation->id, '_paypal_payment_log', $value); break;
            case 'last_ipn_transaction_id': update_post_meta($donation->id, '_paypal_ipn_txn_id', !!$value); break;

            // Only for the REST API:
            case 'paypal_billing_plan_id': update_post_meta($donation->id, '_paypal_billing_plan_id', $value); break;
            case 'paypal_billing_agreement_id': update_post_meta($donation->id, '_paypal_billing_agreement_id', $value); break;
            default:
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['paypal-token']) && $donation->paypal_token !== $_POST['paypal-token']) {
            $donation->paypal_token = $_POST['paypal-token'];
        }

        if(isset($_POST['paypal-correlation-id']) && $donation->paypal_token !== $_POST['paypal-correlation-id']) {
            $donation->paypal_correlation_id = $_POST['paypal-correlation-id'];
        }

        if(isset($_POST['paypal-payment-id']) && $donation->paypal_payment_id !== $_POST['paypal-payment-id']) {
            $donation->paypal_payment_id = $_POST['paypal-payment-id'];
        }

        if(isset($_POST['paypal-payer-id']) && $donation->paypal_token !== $_POST['paypal-payer-id']) {
            $donation->paypal_payer_id = $_POST['paypal-payer-id'];
        }

        // Only for the REST API:
        if(isset($_POST['paypal-billing-plan-id']) && $donation->paypal_billing_plan_id !== $_POST['paypal-billing-plan-id']) {
            $donation->paypal_billing_plan_id = $_POST['paypal-billing-plan-id'];
        }

        if(isset($_POST['paypal-billing-agreement-id']) && $donation->paypal_billing_agreement_id !== $_POST['paypal-billing-agreement-id']) {
            $donation->paypal_billing_agreement_id = $_POST['paypal-billing-agreement-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['paypal_payer_id']) ) {
            update_post_meta($donation_id, '_paypal_payer_id', $donation_params['paypal_payer_id']);
        }

        if( !empty($donation_params['paypal_payment_id']) ) {
            update_post_meta($donation_id, '_paypal_payment_id', $donation_params['paypal_payment_id']);
        }

        update_post_meta($donation_id, '_paypal_payment_log', array());
        update_post_meta($donation_id, '_paypal_ipn_txn_id', 0);

    }

    protected function _get_donation_by($paypal_field, $value) {

        switch($paypal_field) {
            case 'payment_id':
            case 'pp_payment_id':
            case 'paypal_payment_id': $paypal_field = '_paypal_payment_id'; break;
            case 'token':
            case 'pp_token':
            case 'pp_payment_token':
            case 'paypal_token':
            case 'paypal_payment_token': $paypal_field = '_paypal_token'; break;

            case 'paypal_billing_plan_id': $paypal_field = '_paypal_billing_plan_id'; break;
            case 'paypal_billing_agreement_id': $paypal_field = '_paypal_billing_agreement_id'; break;
            default:
                $paypal_field = false;
        }

        $value = esc_sql($value);
        if( !$paypal_field || !$value ) {
            return false;
        }

        $donation = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'nopaging' => true,
            'meta_query' => array(array('key' => $paypal_field, 'value' => $value,),),
        ));

        if($donation) {

            $donation = reset($donation);
            return new Leyka_Donation($donation);

        } else {
            return false;
        }

    }

    protected function _donation_error($title, $text = '', Leyka_Donation $donation = NULL, $operation_type = '', $data = array(), $new_status = 'failed', $do_redirect = true) {

        if($donation) {

            if(array_key_exists($new_status, leyka_get_donation_status_list())) {

                $donation->status = $new_status;

                if($data) {
                    $donation->add_gateway_response($data);
                }

            }

            if($operation_type) {
                $this->_add_to_payment_log($donation, $operation_type, (array)$data, empty($text) ? $title : $text);
            }

        }

        if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
            wp_mail(leyka_get_website_tech_support_email(), $title, $text ? $text."\n\r\n\r" : '');
        }

        if( !!$do_redirect ) {
            wp_redirect(leyka_get_failure_page_url());
        }

        exit(0);

    }

    protected function _add_to_payment_log(Leyka_Donation $donation, $op_type, $data, $result = '') {

        if( !leyka_options()->opt('paypal_keep_payment_logs') ) {
            return;
        }

        $log = (array)$donation->paypal_log;
        $log[] = array('date' => time(), 'operation' => $op_type, 'data' => $data, 'result' => esc_sql($result),);

        $donation->paypal_log = $log;

    }

}


class Leyka_Paypal_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'paypal_all';
        $this->_gateway_id = 'paypal';
        $this->_category = 'misc';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('PayPal allows a simple and safe way to pay for goods and services through internet. After filling a payment form, you will be redirected to the <a href="https://www.paypal.com/" target="_blank">PayPal website</a> to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('PayPal', 'leyka');
        $this->_label = __('PayPal', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'gateways/paypal/icons/paypal-frontend.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

    public function has_recurring_support() {
        return !!leyka_options()->opt('paypal_enable_recurring');
    }

}

function leyka_add_gateway_paypal() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Paypal_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_paypal');