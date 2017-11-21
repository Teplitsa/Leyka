<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Paypal_Gateway class
 */

class Leyka_Paypal_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'paypal';
        $this->_title = __('PayPal', 'leyka');
        $this->_docs_link = 'https://leyka.te-st.ru/docs/nastrojka-paypal/';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 10;

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = array(
            'paypal_api_username' => array(
                'type' => 'text',
                'title' => __('PayPal API username', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., your.name@yourmail.com', 'leyka'),
            ),
            'paypal_api_password' => array(
                'type' => 'text',
                'title' => __('PayPal API password', 'leyka'),
                'placeholder' => __('Ex., 1^2@3#&84nDsOmE5h1T', 'leyka'),
                'is_password' => true,
                'required' => false,
            ),
            'paypal_api_signature' => array(
                'type' => 'text',
                'title' => __('PayPal API signature', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., 1^2@3#&84nDsOmE5h1T', 'leyka'),
                'is_password' => true,
            ),
            'paypal_test_mode' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'required' => false,
            ),
            'paypal_enable_recurring' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Enable monthly recurring payments', 'leyka'),
                'description' => __('Check if you want to enable monthly recurring payments.', 'leyka'),
                'required' => false,
            ),
            'paypal_accept_verified_only' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Accept only verified payments', 'leyka'),
                'description' => __('Check if you want to accept payments only from verified PayPal accounts.', 'leyka'),
                'required' => false,
            ),
            'paypal_keep_payment_logs' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Keep detailed logs of all PayPal service operations', 'leyka'),
                'description' => __('Check if you want to keep detailed logs of all PayPal service operations for each incoming donation.', 'leyka'),
                'required' => false,
            ),
        );

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['paypal_all'])) {
            $this->_payment_methods['paypal_all'] = Leyka_Paypal_All::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        leyka()->auto_redirect = false;

        $donation = new Leyka_Donation($donation_id);

        $campaign_post = get_post($donation->campaign_id);

        $payment_description = $donation->payment_title." (№ $donation_id)";
        if(strlen($payment_description) > 127) { // 127 chars length is a restriction from PayPal
            $payment_description = sprintf(__('Donation № %d', 'leyka'), $donation_id);
        }


        if(empty($_POST['leyka_recurring'])) { // Single donation
            $donation->payment_type = 'single';
        } else { // Recurring donation
            $donation->payment_type = 'rebill';
        }

        // $donation->payment_type = 'rebill';

        if ($donation->payment_type === 'rebill') {
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
              'RETURNURL' => home_url('?p=leyka/service/'.$this->_id.'/process_payment/'),
              'CANCELURL' => leyka_get_failure_page_url(),
              'PAYMENTREQUEST_0_NOTIFYURL' => home_url('?p=leyka/service/'.$this->_id.'/ipn/'),
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
  //            'LOGOIMG' => 'https://sandbox.paypal.com/logo.png',
  //            'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
  //            'L_BILLINGAGREEMENTDESCRIPTION0' => 'Recurring Donations', // WARNING: if L_BILLINGTYPE0 is set, it is necessary
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
              'RETURNURL' => home_url('?p=leyka/service/'.$this->_id.'/process_payment/'),
              'CANCELURL' => leyka_get_failure_page_url(),
              'PAYMENTREQUEST_0_NOTIFYURL' => home_url('?p=leyka/service/'.$this->_id.'/ipn/'),
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
  //            'LOGOIMG' => 'https://sandbox.paypal.com/logo.png',
  //            'L_BILLINGTYPE0' => 'MerchantInitiatedBilling', // WARNING: for recurring this will be "RecurringPayments"
  //            'L_BILLINGAGREEMENTDESCRIPTION0' => 'Recurring Donations', // WARNING: if L_BILLINGTYPE0 is set, it is necessary
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

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            case 'process_payment': // Do a payment itself

                if(empty($_GET['token']) || empty($_GET['PayerID'])) {
                    return false;
                }

                $donation = $this->_get_donation_by_token($_GET['token']);

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

              if ($donation->payment_type === 'rebill') {
                $data = apply_filters('leyka_paypal_do_ec_payment_data', array(
                    'USER' => leyka_options()->opt('paypal_api_username'),
                    'PWD' => leyka_options()->opt('paypal_api_password'),
                    'SIGNATURE' => leyka_options()->opt('paypal_api_signature'),
                    'VERSION' => 204,
                    'METHOD' => 'CreateRecurringPaymentsProfile',
                    'TOKEN' => $_GET['token'],
                    'PAYERID' => $_GET['PayerID'],
                    'AMT' => $donation->amount,
                    'CURRENCYCODE' => 'RUB',
                    'CURRENCYCODE' => 'RU',
                    'DESC' => $payment_description,
                    'BILLINGPERIOD' => 'Month',
                    'BILLINGFREQUENCY' => 12,
                    'PROFILESTARTDATE' => '2017-11-09T14:50:00Z',
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
                    'PAYMENTREQUEST_0_NOTIFYURL' => home_url('leyka/service/'.$this->_id.'/ipn/'),
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
                    wp_redirect(leyka_get_success_page_url());

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
                    wp_redirect(leyka_get_success_page_url());

                }

                break;

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
                if( !empty($_POST['payment_status']) && $_POST['payment_status'] == 'Completed' ) {

                    if( !leyka_options()->opt('paypal_accept_verified_only') || $_POST['payer_status'] == 'verified' ) {

                        $donation->status = 'funded';
                        $donation->add_gateway_response($_POST);
                        $this->_add_to_payment_log($donation, 'IPN', $_POST);

                        Leyka_Donation_Management::send_all_emails($donation->id);

                    }

                } elseif(
                    !empty($_POST['payment_status']) && in_array($_POST['payment_status'], array('Pending', 'In-Progress'))
                ) {

                    $donation->status = 'submitted';
                    $donation->add_gateway_response($_POST);
                    $this->_add_to_payment_log($donation, 'IPN', $_POST);

                } else if(
                    !empty($_POST['payment_status']) && in_array($_POST['payment_status'], array('Refunded', 'Reversed'))
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

            default:

        }

        exit(0);
    }

    /** Override the auto-submit setting to send manual requests to PayPal. */
    public function submission_auto_redirect($is_auto_redirect, $pm_id, $donation_id) {
        return false;
    }

    public function gateway_redirect_page_content($pm_id, $donation_id) {



    }

    public function enqueue_gateway_scripts() {

        if( !Leyka_Paypal_All::get_instance()->active ) {
            return;
        }

        // For donation redirect page:
        if(get_query_var('name') == 'leyka-process-donation') {
            wp_enqueue_script(
                'leyka-paypal-front',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Paypal_Gateway::get_instance()->id.'/js/leyka.paypal.js',
                array('jquery',),
                LEYKA_VERSION,
                true
            );
        }

//        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));

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

            $donation = leyka_get_validated_donation($donation);
//            echo '<pre>' . print_r($donation->paypal_log, 1) . '</pre>';?>

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
                    <input type="text" id="paypal-correlation-id" name="paypal-token" placeholder="<?php _e('Enter PayPal correlation ID', 'leyka');?>" value="<?php echo $donation->paypal_correlation_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->paypal_correlation_id;?></span>
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
            case 'paypal_payer_id':
            case 'pp_payer_id': return get_post_meta($donation->id, '_paypal_payer_id', true);
            case 'paypal_payment_history':
            case 'paypal_history':
            case 'pp_history':
            case 'paypal_payment_log':
            case 'paypal_log':
            case 'pp_log': return get_post_meta($donation->id, '_paypal_payment_log', true);
            case 'last_ipn_transaction_id': return get_post_meta($donation->id, '_paypal_ipn_txn_id', true);
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'paypal_token':
            case 'pp_token': update_post_meta($donation->id, '_paypal_token', $value); break;
            case 'paypal_correlation_id':
            case 'pp_correlation_id': update_post_meta($donation->id, '_paypal_correlation_id', $value); break;
            case 'paypal_payer_id':
            case 'pp_payer_id': update_post_meta($donation->id, '_paypal_payer_id', $value); break;
            case 'paypal_payment_history':
            case 'paypal_history':
            case 'pp_history':
            case 'paypal_payment_log':
            case 'paypal_log':
            case 'pp_log': update_post_meta($donation->id, '_paypal_payment_log', $value); break;
            case 'last_ipn_transaction_id': update_post_meta($donation->id, '_paypal_ipn_txn_id', !!$value); break;
            default:
        }

    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['paypal-token']) && $donation->paypal_token != $_POST['paypal-token']) {
            $donation->paypal_token = $_POST['paypal-token'];
        }

        if(isset($_POST['paypal-correlation-id']) && $donation->paypal_token != $_POST['paypal-correlation-id']) {
            $donation->paypal_correlation_id = $_POST['paypal-correlation-id'];
        }

        if(isset($_POST['paypal-payer-id']) && $donation->paypal_token != $_POST['paypal-payer-id']) {
            $donation->paypal_payer_id = $_POST['paypal-payer-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['paypal_payer_id']) ) {
            update_post_meta($donation_id, '_paypal_payer_id', $donation_params['paypal_payer_id']);
        }

        if( !empty($donation_params['paypal_payer_id']) ) {
            update_post_meta($donation_id, '_paypal_payer_id', $donation_params['paypal_payer_id']);
        }

        update_post_meta($donation_id, '_paypal_payment_log', array());
        update_post_meta($donation_id, '_paypal_ipn_txn_id', 0);

    }

    protected function _get_donation_by_token($paypal_token) {

        $paypal_token = trim($paypal_token);
        if( !$paypal_token ) {
            return false;
        }

        $donation = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'nopaging' => true,
            'meta_query' => array(array('key' => '_paypal_token', 'value' => $paypal_token,),),
        ));

        if($donation) {

            $donation = reset($donation);
            return new Leyka_Donation($donation);

        } else {
            return false;
        }

    }

    protected function _donation_error($title, $text, Leyka_Donation $donation = NULL, $operation_type = '', $data = array(), $new_status = 'failed', $do_redirect = true) {

        if($donation && array_key_exists($new_status, leyka_get_donation_status_list())) {
            $donation->status = $new_status;
        }

        if($donation && $operation_type && leyka_options()->opt('paypal_keep_payment_logs')) {
            $this->_add_to_payment_log($donation, $operation_type, (array)$data, $text);
        }

        wp_mail(get_option('admin_email'), $title, $text."\n\r\n\r");

        if( !!$do_redirect ) {
            wp_redirect(leyka_get_failure_page_url());
        }

        exit(0);

    }

    protected function _add_to_payment_log(Leyka_Donation $donation, $op_type, $data, $result = '') {

        $log = (array)$donation->paypal_log;
        array_push($log, array('date' => time(), 'operation' => $op_type, 'data' => $data, 'result' => $result,));
        $donation->paypal_log = $log;

    }

}


class Leyka_Paypal_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'paypal_all';
        $this->_gateway_id = 'paypal';

        $this->_label_backend = __('PayPal', 'leyka');
        $this->_label = __('PayPal', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/yandex/icons/master.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/paypal/icons/paypal-frontend.png',
        ));

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
                'default' => __('PayPal allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the <a href="https://www.paypal.com/">PayPal website</a> to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Yandex Smart Payment description', 'leyka'),
                'description' => __('Please, enter PayPal payment service description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );

    }

    public function has_recurring_support() {
        return !!leyka_options()->opt('paypal_enable_recurring');
        // return true;
    }

}

function leyka_add_gateway_paypal() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Paypal_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_paypal');
