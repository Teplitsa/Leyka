<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Yandex_Gateway class
 */

class Leyka_Yandex_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_new_api_redirect_url = '';

    protected function __construct() {

        parent::__construct();

        if(leyka_options()->get_value('yandex_new_api') && !function_exists('yookassaSdkLoadClass')) {
            require_once LEYKA_PLUGIN_DIR.'gateways/yandex/lib/autoload.php';
        }

    }

    protected function _set_attributes() {

        $this->_id = 'yandex';
        $this->_title = __('YooKassa', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            /* translators: 1: URL, 2: Title. */
            sprintf(__('<a href="%1$s">%2$s</a> gateway allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the secure gateway webpage to enter your payment data and to confirm your payment.', 'leyka'), 'https://yookassa.ru/payments/', $this->_title),
            $this->_id
        );

        $this->_docs_link = '//leyka.org/docs/yandex-dengi/';
        $this->_registration_link = 'https://kassa.yandex.ru/joinups';
        $this->_has_wizard = false; // For now; the Wizard is outdated

        $this->_min_commission = 2.8;
        $this->_receiver_types = ['legal'];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }

        $this->_options = [
            $this->_id.'_new_api' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Use the new REST API', 'leyka'),
                'comment' => __('Check if your YooKassa connection uses REST API for payments.', 'leyka'),
                'short_format' => true,
            ],
            $this->_id.'_shop_id' => [
                'type' => 'text',
                'title' => __('ShopID', 'leyka'),
                'comment' => __('Please, enter your shopID here. It can be found in your gateway account or in the contract documents. Also you can ask your gateway-side manager for it.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12345'),
            ],
            $this->_id.'_scid' => [
                'type' => 'text',
                'title' => __('ScID', 'leyka'),
                'comment' => __('Please, enter your shop showcase ID (SCID) here. It can be found in your gateway account or in the contract documents. Also you can ask your gateway-side manager for it.', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12345'),
                'field_classes' => ['old-api',],
            ],
            $this->_id.'_shop_article_id' => [
                'type' => 'text',
                'title' => __('ShopArticleID', 'leyka'),
                'comment' => __('Please, enter your shop article ID here, if it exists. It can be found in your gateway account or in the contract documents. Also you can ask your gateway-side manager for it.', 'leyka'),
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12345'),
                'field_classes' => ['old-api',],
            ],
            $this->_id.'_shop_password' => [
                'type' => 'text',
                'title' => __('shopPassword', 'leyka'),
                'comment' => __("Please, enter a shopPassword parameter value that you filled in the gateway technical questionaire. If it's set, Leyka will perform MD5 hash checks of each incoming donation data integrity.", 'leyka'),
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1^2@3#&84nDsOmE5h1T'),
                'is_password' => true,
                'field_classes' => ['old-api',],
            ],
            $this->_id.'_secret_key' => [
                'type' => 'text',
                'title' => __('Secret key for API', 'leyka'),
                'comment' => __("Please, enter a secret key parameter value that you filled in the gateway technical questionaire. If it's set, Leyka will perform MD5 hash checks of each incoming donation data integrity. More information  <a href='https://yookassa.ru/docs/support/merchant/payments/implement/keys' target='_blank'>here</a>.", 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'test_OkT0flRaEnS0fWqMFZuTg01hu_8SxSkxZuAVIw7CMgB'),
                'is_password' => true,
                'field_classes' => ['new-api',],
            ],
            $this->_id.'_return_url' => [
                'type' => 'select',
                'default' => '0',
                'title' => __("Return page", 'leyka'),
                'description' => __('A page to return from the gateway process page', 'leyka'),
                'list_entries' => leyka_get_posts_list(['page', 'leyka_campaign']),
            ],
            $this->_id.'_test_mode' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
                'field_classes' => ['old-api',],
            ],
        ];

    }

    public function _set_donations_errors() {

        $this->_donations_errors_ids = [
            '3d_secure_failed' => 'L-7002', 'call_issuer' => 'L-9001', 'canceled_by_merchant' => 'L-4001',
            'card_expired' => 'L-7004', 'country_forbidden' => 'YK-6011', 'deal_expired' => 'YK-7006',
            'expired_on_capture' => 'YK-7007', 'expired_on_confirmation' => 'YK-7005', 'fraud_suspected' => 'L-5043',
            'general_decline' => 'L-4002', 'identification_required' => 'YK-6001', 'insufficient_funds' => 'L-7005',
            'internal_timeout' => 'YK-8002', 'invalid_card_number' => 'L-7003', 'invalid_csc' => 'L-7001',
            'issuer_unavailable' => 'L-5001', 'payment_method_limit_exceeded' => 'YK-7042',
            'payment_method_restricted' => 'L-6001', 'permission_revoked' => 'YK-7043',
            'unsupported_mobile_operator' => 'YK-7050',
        ];

        // Only Gateway-specific errors are initialized & added as objects here:
        Leyka_Donations_Errors::get_instance()->add_error(
            'YK-6001',
            __('The operations limit for YooMoney wallet is exceeded', 'leyka'), [
                'recommendation_admin' => __('Ask the donor to identify their YooMoney wallet, if possible, or to use another payment method.', 'leyka'),
                'recommendation_donor' => __('Please, identify your YooMoney wallet, if possible, or use another payment method.', 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-6011',
            __('Bank card payment is refused because of the country that issued the card', 'leyka'), [
                'recommendation_admin' => __("Ask the donor to use another payment method (i.e., another card). If this won't help, ask the donor to contact the bank that issued the card.", 'leyka'),
                'recommendation_donor' => __("Please, try to use another payment method (i.e., another card). If this won't help, report this issue to the bank that issued the card.", 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7005',
            __('The allowed time for the payment is expired', 'leyka'), [
            'description' => __("Donor waited for too long on the payment data entering step, so the payment wasn't completed in time allowed.", 'leyka'),
            'recommendation_admin' => __("Ask the donor to make a donation anew, but now finish it.", 'leyka'),
            'recommendation_donor' => __("You didn't confirm the payment in time, so it was was aborted. Your money are intact, but if you'd like to finish the donation, you should make it anew (and now complete it).", 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7006',
            __('The payment deal is expired', 'leyka')
        ) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7007',
            __('The payment time for a two-staged (captured) payment is expired', 'leyka')
        ) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7042',
            __('The operations limit for the bank card or the shop is exceeded', 'leyka'), [
                'recommendation_admin' => __("Ask the donor to use another payment method (i.e., another card). If this won't help, ask the donor to try to pay 1-2 days later. If the problem will persist, contact the gateway technical support.", 'leyka'),
                /* translators: %s: Support email. */
                'recommendation_donor' => sprintf(__("Please, try to use another payment method (i.e., another card). If it's not helping, try to pay 1-2 days later. If the problem still persists, ask the <a href='mailto:%s' target='_blank'>website administration</a> to report this to the gateway technical support.", 'leyka'), leyka_options()->opt('tech_support_email')),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-8002',
            __("Payment wasn't finished due to YooKassa internal timeout", 'leyka'), [
                'description' => __("Technical troubles on the YooKassa side - the payment handling couldn't be completed in time allowed for the operation.", 'leyka'),
                'recommendation_admin' => __("For some reason, YooKassa didn't process this payment in time allowed (30 seconds by default). Please, contact your YooKassa manager, and ask them for the reason. If it was temporary technical failure, contact the donor and ask him/her to make a donation anew.", 'leyka'),
                /* translators: %s: Support email. */
                'recommendation_donor' => sprintf(__("The payment gateway didn't process your payment in time, and the payment was aborted. Your money are intact, but if you'd like to finish the payment, you should make it anew. If the problem persists, please contact the <a href='mailto:%s' target='_blank'>website technical support</a> and tell them that you have a problem making a donation due to the gateway behavior.", 'leyka'), leyka_options()->opt('tech_support_email')),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7043',
            __("Can't make a rebill payment - the donor revoked the auto-payments permission", 'leyka'), [
                'description' => __('Unable to make a rebill auto-payment: the donor user has revoked permission for auto payments. If the donor wants to continue with the recurring subscription, they will need to create a new subscription and confirm the initial payment.', 'leyka'),
                'recommendation_admin' => __("Ask the donor to make a recurring donations subscription anew, as their current subscription can't proceed with its auto-payments and will be deactivated.", 'leyka'),
                'recommendation_donor' => __("Please, make a recurring donations subscription anew, as your current subscription can't proceed with its auto-payments and will be deactivated.", 'leyka'),
        ]) && Leyka_Donations_Errors::get_instance()->add_error(
            'YK-7050',
            __("Can't make a payment from the selected mobile operator", 'leyka'), [
                'description' => __("YooKassa doesn't support mobile payments from the mobile operator used.", 'leyka'),
                /* translators: %s: URL. */
                'recommendation_admin' => sprintf(__('Ask the donor to make a new mobile payment using one of the mobile operators supported by YooKassa (<a href="%s" target="_blank">operators list</a>).', 'leyka'), 'https://yookassa.ru/docs/support/payments/accept-methods#carrier-billing'),
                /* translators: %s: URL. */
                'recommendation_donor' => sprintf(__('Please, make a new mobile payment using one of the mobile operators supported by YooKassa (<a href="%s" target="_blank">operators list</a>).', 'leyka'), 'https://yookassa.ru/docs/support/payments/accept-methods#carrier-billing'),
        ]);

    }

    public function is_setup_complete($pm_id = false) {
        if(leyka_options()->opt('yandex_new_api')) {
            return leyka_options()->opt('yandex_shop_id') && leyka_options()->opt('yandex_secret_key');
        } else {
            return leyka_options()->opt('yandex_shop_id') && leyka_options()->opt('yandex_scid');
        }
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['yandex_all'])) {
            $this->_payment_methods['yandex_all'] = Leyka_Yandex_All::get_instance();
        }
        if(empty($this->_payment_methods['yandex_card'])) {
            $this->_payment_methods['yandex_card'] = Leyka_Yandex_Card::get_instance();
        }
        if(empty($this->_payment_methods['yandex_money'])) {
            $this->_payment_methods['yandex_money'] = Leyka_Yandex_Money::get_instance();
        }
        if(empty($this->_payment_methods['yandex_wm'])) {
            $this->_payment_methods['yandex_wm'] = Leyka_Yandex_Webmoney::get_instance();
        }
        if(empty($this->_payment_methods['yandex_sb'])) {
            $this->_payment_methods['yandex_sb'] = Leyka_Yandex_Sberbank_Online::get_instance();
        }
        if(empty($this->_payment_methods['yandex_ab'])) {
            $this->_payment_methods['yandex_ab'] = Leyka_Yandex_Alpha_Click::get_instance();
        }
        if(empty($this->_payment_methods['yandex_pb'])) {
            $this->_payment_methods['yandex_pb'] = Leyka_Yandex_Promvzyazbank::get_instance();
        }
        //TODO добавление связки с классом
        if(empty($this->_payment_methods['yandex_sbp'])) {
            $this->_payment_methods['yandex_sbp'] = Leyka_Yandex_SBP::get_instance();
        }
        //TODO конец

    }

    protected function _handle_donation_failure(Leyka_Donation_Base $donation, $gateway_response = false) {

        if($gateway_response) {
            $donation->add_gateway_response($gateway_response);
        }

        if($donation->status !== 'failed') {

            $donation->status = 'failed';
            Leyka_Donation_Management::send_error_notifications($donation); // Emails will be sent only if their options are on

        }

        if(
            is_a($gateway_response, 'YooKassa\Request\Payments\PaymentResponse')
            && $gateway_response->status === 'canceled'
            && !empty($gateway_response->cancellation_details)
            && !empty($gateway_response->cancellation_details->reason)
        ) {
            $donation->error_id = $this->get_donation_error_id($gateway_response->cancellation_details->reason);
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

        if(leyka_options()->opt('yandex_new_api')) {

//            if( !function_exists('yookassaSdkLoadClass') ) {
//                require_once LEYKA_PLUGIN_DIR.'gateways/yandex/lib/autoload.php';
//            }

            $client = new YooKassa\Client();
            $client->setAuth(leyka_options()->opt('yandex_shop_id'), leyka_options()->opt('yandex_secret_key'));

            try {

                $return_page_id = leyka_options()->opt('yandex_return_url');
                $home_url = home_url();
                $return_url = $return_page_id ? // Due to strange YooKassa SDK bug - it doesn't accept URLs w/o "/" at the end
                    get_post_permalink($return_page_id) :
                    (mb_substr($home_url, -1, 1) === '/' ? $home_url : $home_url.'/');

                $payment_data = [
                    'amount' => [
                        'value' => round($form_data['leyka_donation_amount'], 2),
                        'currency' => $form_data['leyka_donation_currency'],
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'return_url' => $return_url,
                    ],
                    'capture' => true, // Make payment at once, don't wait for shop confirmation
                    'description' => leyka_get_donation_gateway_description($donation, 128),
                    'metadata' => [
                        'donation_id' => $donation_id,
                        'donor_name' => $donation->donor_name,
                        'payment_title' => $donation->payment_title,
                        'email' => $donation->donor_email,
                        'cms_name' => 'Leyka',
                    ],
                    'save_payment_method' => !empty($form_data['leyka_recurring']),
                ];
                if($pm_id !== 'yandex_all') {
                    $payment_data['payment_method_data'] = ['type' => $this->_get_gateway_pm_id($pm_id),];
                }

                $payment = $client->createPayment(
                        apply_filters('leyka_yandex_custom_payment_data', $payment_data, $pm_id, $donation_id),
                        uniqid('', true)
                );

                $donation->add_gateway_response($payment); // On callback the response will be re-written

                if(is_object($payment) && !empty($payment->id)) {
                    $donation->yandex_invoice_id = $payment->id;
                }

                $this->_new_api_redirect_url = $payment->confirmation->confirmation_url;

            } catch(Exception $ex) {

                $donation->add_gateway_response($ex);
                /* translators: 1: Message, 2: Support email. */
                leyka()->add_payment_form_error( new WP_Error('leyka_donation_error', sprintf(__('Error while processing the payment: %1$s. Your money will remain intact. Please report to the <a href="mailto:%2$s" target="_blank">website tech support</a>.', 'leyka'), $ex->getMessage(), leyka_get_website_tech_support_email())) );
                return;

            }

        } else { // Old API - for backward compatibility

            if(
                $pm_id === 'yandex_sb'
                && $form_data['leyka_donation_currency'] === 'rub'
                && $form_data['leyka_donation_amount'] < 10.0
            ) {

                leyka()->add_payment_form_error(new WP_Error('leyka_donation_amount_too_small', __('The amount of donations via Sberbank Online should be at least 10 RUB.', 'leyka')));
                return;

            }

        }

    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return leyka_options()->opt('yandex_new_api') ? 'redirect' : $redirect_type;
    }

    public function submission_redirect_url($current_url, $pm_id) {

        if(leyka_options()->opt('yandex_new_api')) {
            return $this->_new_api_redirect_url;
        }

        switch($pm_id) {
            case 'yandex_all':
            case 'yandex_money':
            case 'yandex_card':
            case 'yandex_wm':
            case 'yandex_sb':
            case 'yandex_ab':
            case 'yandex_pb':
                return 'https://yoomoney.ru/eshop.xml';
            default:
                return $current_url;
        }

    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        // New (REST) API doesn't require the data to be sent with redirect:
//        if(leyka_options()->opt('yandex_new_api')) {
//            return apply_filters('leyka_yandex_custom_submission_data', [], $pm_id);
//        }
        // ... but we'll send the data anyway - some Leyka installations are using the AJAX response to hook in the analytics

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $data = [
            'scid' => leyka_options()->opt('yandex_scid'),
            'shopId' => leyka_options()->opt('yandex_shop_id'),
            'sum' => $donation->amount,
            'customerNumber' => $donation->donor_email,
            'orderNumber' => $donation_id,
            'orderDetails' => $donation->payment_title." (# $donation_id)",
            'orderDescription' => $donation->payment_title." (# $donation_id)",
            'paymentType' => apply_filters('leyka_yandex_custom_payment_type', $this->_get_gateway_pm_id($pm_id), $pm_id),
            'shopSuccessURL' => leyka_get_success_page_url(),
            'shopFailURL' => leyka_get_failure_page_url(),
            'cps_email' => $donation->donor_email,
            'cms_name' => 'wp-leyka', // Service parameter, added by Yandex request
        ];
        if(leyka_options()->opt('yandex_shop_article_id')) {
            $data['shopArticleId'] = leyka_options()->opt('yandex_shop_article_id');
        }
        if( !empty($_POST['leyka_recurring']) ) {
            $data['rebillingOn'] = 'true';
        }

        return apply_filters('leyka_yandex_custom_submission_data', $data, $pm_id, $donation);

    }

    /** Wrapper method to answer the old API callbacks - checkOrder and paymentAviso type calls */
    private function _callback_answer($is_error = false, $callback_type = 'co', $message = '', $tech_message = '') {

        $is_error = !!$is_error;
        $tech_message = $tech_message ? : $message;
        $callback_type = $callback_type === 'co' ? 'checkOrderResponse' : 'paymentAvisoResponse';
        $invoice_id = isset( $_POST['invoiceId'] ) ? $_POST['invoiceId'] : '';
        if($is_error) {
            die('<?xml version="1.0" encoding="UTF-8"?><'.esc_attr($callback_type).' performedDatetime="'.esc_attr(gmdate(DATE_ATOM)).'"
code="1000" invoiceId="' . esc_attr( $invoice_id ) . '" shopId="'.esc_attr(leyka_options()->opt('yandex_shop_id')).'" message="'.esc_attr($message).'"
techMessage="'.esc_attr($tech_message).'"/>');
        }

        die('<?xml version="1.0" encoding="UTF-8"?><'.esc_attr($callback_type).' performedDatetime="'.esc_attr(gmdate(DATE_ATOM)).'" code="0" invoiceId="' . esc_attr( $invoice_id ) . '" shopId="'.esc_attr(leyka_options()->opt('yandex_shop_id')).'"/>');

    }

    public function _handle_service_calls($call_type = '') {
        switch($call_type) {

            // New (REST) API callbacks:
            case 'process':
            case 'response':
            case 'notify':

//                if( !function_exists('yookassaSdkLoadClass') ) {
//                    require_once LEYKA_PLUGIN_DIR.'gateways/yandex/lib/autoload.php';
//                }

                $notification = json_decode(file_get_contents('php://input'), true);

                if(empty($notification['event'])) {
                    /** @todo Process the error somehow */
                    exit(500);
                }

                try {

                    switch($notification['event']) {
                        case YooKassa\Model\NotificationEventType::PAYMENT_SUCCEEDED:
                            $notification = new YooKassa\Model\Notification\NotificationSucceeded($notification);
                            break;
                        case YooKassa\Model\NotificationEventType::PAYMENT_CANCELED:
                            $notification = new YooKassa\Model\Notification\NotificationCanceled($notification);
                            break;
                        default:
                            $notification = new YooKassa\Model\Notification\NotificationWaitingForCapture($notification);
                    }

                } catch (Exception $e) {
                    /** @todo Process the error somehow */
                    exit(500);
                }

                $payment = $notification->getObject();
                $donation = Leyka_Donations::get_instance()->get($payment->metadata->donation_id);

                if( !$donation ) {
                    /** @todo Process the error somehow */
                    exit(500);
                }

                if(is_object($payment) && !empty($payment->id)) {
                    $donation->yandex_invoice_id = $payment->id;
                }
                $donation->add_gateway_response($payment);

                switch($payment->status) {
                    case 'succeeded':
                        $donation->status = 'funded';
                        Leyka_Donation_Management::send_all_emails($donation->id);

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

                        break;
                    case 'canceled':
                        $this->_handle_donation_failure($donation);
                        break;
                    case 'refund.succeeded':
                        $donation->status = 'refunded';
                        break;
                    default: // Other possible gateway-side payment statuses: 'pending', 'waiting_for_capture'
                }

                if($donation->type === 'rebill') {
                    do_action('leyka_new_rebill_donation_added', $donation);
                }

                exit(200);

            // Old API (MWS) callbacks:
            case 'check_order':

                if($_POST['action'] != 'checkOrder') { // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'co', __('Wrong service operation', 'leyka'));
                }

                $_POST['orderNumber'] = $this->_validate_order_number($_POST['orderNumber']);

                if( !$_POST['orderNumber'] ) {
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));
                }

                $donation = Leyka_Donations::get_instance()->get_donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount']) {
                    $this->_callback_answer(1, 'co', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));
                }

                $donation->add_gateway_response($_POST);

                $this->_callback_answer(); // OK for Yandex.Kassa payment
                break; // Not needed, just for my IDE could relax

            case 'payment_aviso':

                if($_POST['action'] != 'paymentAviso') { // Payment isn't correct, we're not allowing it
                    $this->_callback_answer(1, 'pa', __('Wrong service operation', 'leyka'));
                }

                $_POST['orderNumber'] = $this->_validate_order_number($_POST['orderNumber']);

                if( !$_POST['orderNumber'] ) {
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('OrderNumber is not set', 'leyka'));
                }

                $donation = Leyka_Donations::get_instance()->get_donation($_POST['orderNumber']);

                if($donation->sum != $_POST['orderSumAmount']) {
                    $this->_callback_answer(1, 'pa', __('Sorry, there is some tech error on our side. Your payment will be cancelled.', 'leyka'), __('Donation sum is unmatched', 'leyka'));
                }

                if($donation->status !== 'funded') {

                    $donation->add_gateway_response($_POST);
                    $donation->status = 'funded';

                    // Change PM if needed. Mostly for Smart Payments:
                    if($_POST['paymentType'] != $this->_get_gateway_pm_id($donation->pm_id)) {
                        $donation->pm_id = $this->_get_gateway_pm_id($_POST['paymentType']);
                    }

                    if($donation->type === 'rebill' && !empty($_POST['invoiceId'])) {
                        $donation->yandex_recurring_id = $_POST['invoiceId'];
                    }

                    Leyka_Donation_Management::send_all_emails($donation);

                    if( // GUA direct integration - "purchase" event:
                        leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
                        && leyka_options()->opt('gtm_ua_tracking_id')
                        && in_array('purchase', leyka_options()->opt('gtm_ua_enchanced_events'))
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

                do_action('leyka_yandex_payment_aviso_success', $donation);

                if($donation->type === 'rebill') {
                    do_action('leyka_new_rebill_donation_added', $donation);
                }

                $this->_callback_answer(0, 'pa'); // OK for yandex money payment
                break; // Not needed, just for my IDE could relax

            default:
                $this->_callback_answer(1, 'unknown', __('Unknown service operation', 'leyka'), 'Unknown callback type: '.$call_type);
        }
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $response = is_object($donation->gateway_response) || is_array($donation->gateway_response) ?
            serialize($donation->gateway_response) : $donation->gateway_response;

        if(mb_stristr($response, 'YandexCheckout') || mb_stristr($response, 'YooKassa')) { // New API

            $response = maybe_unserialize($response);

            if(is_a($response, '__PHP_Incomplete_Class')) { // Normally it doesn't happen - just in case
                return [];
            }

            if(
                is_a($response, 'YooKassa\Request\Payments\PaymentResponse')
                || is_a($response, 'YooKassa\Request\Payments\CreatePaymentResponse')
            ) { // Payment proceeded normally
                $response = [
                    __('YooKassa payment ID:', 'leyka') => $response->id,
                    __('YooKassa payment status:', 'leyka') => $response->status,
                    __('Payment is done:', 'leyka') => !!$response->paid ? __('Yes', 'leyka') : __('No', 'leyka'),
                    __('Amount:', 'leyka') => round($response->amount->value, 2).' '
                        .leyka_get_currency_label($response->amount->currency),
                    __('Created at:', 'leyka') => leyka_get_i18n_datetime(strtotime($response->created_at->date)),
                    __('Captured at:', 'leyka') => empty($response->captured_at->date) ?
                        __('No', 'leyka') : leyka_get_i18n_datetime(strtotime($response->captured_at->date)),
                    __('Description:', 'leyka') => $response->description,
                    __('Payment method:', 'leyka') => empty($response->payment_method->title) ?
                        (empty($response->payment_method->type) ? __('No', 'leyka') : $response->payment_method->type) :
                        $response->payment_method->title,
//                    __('Is test payment:', 'leyka') => !!$response->test ? __('Yes') : __('No'),
                ];
            } else if(is_a($response, 'Exception')) { // Exceptions were thrown
                $response = [
                    __('Failure type:', 'leyka') => empty($response->type) ? __('unknown', 'leyka') : $response->type,
                    __('Failure code:', 'leyka') => $response->getCode(),
                    __('Failure message:', 'leyka') => $response->getMessage(),
                ];
            }

        } else { // Old API

            $response = is_array($donation->gateway_response) ?
                $donation->gateway_response : maybe_unserialize($donation->gateway_response);

            if( !$response ) {
                $response = [];
            } else if( !is_array($response) ) {
                $response = ['' => ucfirst($response)];
            }

            $response = [
                __('Last response operation:', 'leyka') => empty($response['action']) ?
                    __('Unknown', 'leyka') :
                    ($response['action'] == 'checkOrder' ? __('Donation confirmation', 'leyka') : __('Donation approval notice', 'leyka')),
                __('Gateway invoice ID:', 'leyka') => empty($response['invoiceId']) ? '' : $response['invoiceId'],
                __('Full donation amount:', 'leyka') => empty($response['orderSumAmount']) ?
                    '' : (float)$response['orderSumAmount'].' '.$donation->currency_label,
                __('Donation amount after gateway commission:', 'leyka') => empty($response['shopSumAmount']) ?
                    '' : (float)$response['shopSumAmount'].' '.$donation->currency_label,
                __('Gateway donor ID:', 'leyka') => empty($response['customerNumber']) ? '' : $response['customerNumber'],
                __('Response date:', 'leyka') => empty($response['requestDatetime']) ?
                    '' : gmdate('d.m.Y, H:i:s', strtotime($response['requestDatetime'])),
            ];

        }

        return apply_filters('leyka_donation_gateway_response', $response, $donation);

    }

    // The default implementations are in use:
//    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation_Base $donation) { }
//    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) { }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->yandex_recurring_id ) {
            return false;
        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'date' => false, // Use current date for rebill Donations
                'payment_type' => 'rebill',
                'init_recurring_donation' => $init_recurring_donation->id,
                'yandex_recurring_id' => $init_recurring_donation->yandex_recurring_id,
            ],
            ['recalculate_total_amount' => true,]
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        if(leyka_get_pm_commission($new_recurring_donation->pm_full_id) > 0.0) {
            $new_recurring_donation->amount_total = leyka_calculate_donation_total_amount($new_recurring_donation);
        }

        if(leyka_options()->opt('yandex_new_api')) {

//            if( !function_exists('yookassaSdkLoadClass') ) {
//                require_once LEYKA_PLUGIN_DIR.'gateways/yandex/lib/autoload.php';
//            }

            $client = new YooKassa\Client();
            $client->setAuth(leyka_options()->opt('yandex_shop_id'), leyka_options()->opt('yandex_secret_key'));

            try {

                $payment = $client->createPayment(
                    [
                        'amount' => [
                            'value' => round($new_recurring_donation->amount, 2),
                            'currency' => $new_recurring_donation->currency_id,
                        ],
                        'payment_method_id' => $init_recurring_donation->yandex_recurring_id,
                        'capture' => true,
                        'description' => leyka_get_donation_gateway_description($init_recurring_donation, 128),
                        'metadata' => [
                            'donation_id' => $new_recurring_donation->id,
                            'donor_name' => $new_recurring_donation->donor_name,
                            'payment_title' => $new_recurring_donation->payment_title,
                            'email' => $new_recurring_donation->donor_email,
                            'cms_name' => 'Leyka',
                        ],
                    ],
                    uniqid('', true)
                );

                $new_recurring_donation->add_gateway_response($payment); // On callback the response will be re-written
                $new_recurring_donation->yandex_recurring_id = $payment->id;

                if($payment->status === 'canceled') { // If rebill donation didn't succedded, there won't be callbacks
                    $this->_handle_donation_failure($new_recurring_donation, $payment);
                }

            } catch(Exception $ex) {
                $this->_handle_donation_failure($new_recurring_donation, $ex);
            }

        } else {

            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
            $ch = curl_init();
            $params = [
                CURLOPT_URL => 'https://shop.yookassa.ru/webservice/mws/api/repeatCardPayment',
                CURLOPT_PORT => leyka_options()->opt('yandex_test_mode') ? 8083 : 443,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'clientOrderId' => $new_recurring_donation->id,
                    'invoiceId' => $init_recurring_donation->yandex_recurring_id,
                    'orderNumber' => 'recurring-'.$init_recurring_donation->id.'-'.$new_recurring_donation->id,
                    'amount' => $init_recurring_donation->amount,
                ]),
                CURLOPT_VERBOSE => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FORBID_REUSE => true,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_SSLCERT => leyka_options()->opt('yandex-yandex_card_certificate_path') ?
                    WP_CONTENT_DIR.'/'.trim(leyka_options()->opt('yandex-yandex_card_certificate_path'), '/') : false,
                CURLOPT_SSLKEY => leyka_options()->opt('yandex-yandex_card_private_key_path') ?
                    WP_CONTENT_DIR.'/'.trim(leyka_options()->opt('yandex-yandex_card_private_key_path'), '/') : false,
                CURLOPT_SSLKEYPASSWD => leyka_options()->opt('yandex-yandex_card_private_key_password'),
            ];
            if(leyka_options()->opt('yandex_outer_ip_to_inner')) {
                $params[CURLOPT_INTERFACE] = gethostbyname(gethostname());
            }
            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
            curl_setopt_array($ch, $params);

            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
            $answer = curl_exec($ch);
            if($answer) {

                $p = xml_parser_create();
                xml_parse_into_struct($p, $answer, $vals, $index);
                xml_parser_free($p);

                $new_recurring_donation->add_gateway_response($answer);

                if(isset($vals[0]['attributes']['STATUS']) && $vals[0]['attributes']['STATUS'] == 0) {

                    // Recurring payment isn't funded here yet! Only its possibility is confirmed.
                    // To fund a payment, we should wait for a normal callbacks.

                } else { // Some error on payment test run

                    $error_num = empty($vals[0]['attributes']['error']) ? 'unknown' : $vals[0]['attributes']['error'];
                    $error_text = empty($vals[0]['attributes']['techMessage']) ?
                        __('Some error while repeatCardPayment call. Please ask your Yandex.Kassa manager for details.', 'leyka') : $vals[0]['attributes']['techMessage'];

                    $new_recurring_donation->add_gateway_response('Error '.$error_num.': '.$error_text);

                }

            } else {
                // phpcs:disable
                $new_recurring_donation->add_gateway_response('Error '.curl_errno($ch).': '.curl_error($ch));
                // phpcs:enable
            }

            // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
            curl_close($ch);

        }

        return $new_recurring_donation;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php esc_html_e('YooKassa invoice ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                <input type="text" id="yandex-invoice-id" name="yandex-invoice-id" placeholder="<?php esc_attr_e('Enter YooKassa invoice ID', 'leyka');?>" value="<?php echo esc_attr( $donation->yandex_invoice_id );?>">
                <?php } else {?>
                <span class="fake-input"><?php echo esc_html( $donation->yandex_invoice_id );?></span>
                <?php }?>
            </div>

        <?php if($donation->type !== 'rebill') {
                return;
            }

            $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">
                <label for="yandex-recurring-is-active"><?php esc_html_e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="checkbox" id="yandex-recurring-is-active" name="yandex-recurring-is-active" value="1" <?php checked( $init_recurring_donation->recurring_is_active, '1' );?>>
                </div>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="yandex-invoice-id"><?php esc_html_e('YooKassa invoice ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="yandex-invoice-id" name="yandex-invoice-id" placeholder="<?php esc_attr_e('Enter YooKassa invoice ID', 'leyka');?>" value="">
            </div>
            <?php
        }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'payment_id':
            case 'yandex_recurrent_id':
            case 'yandex_recurring_id':
            case 'yandex_invoice_id':
            case 'yandex_payment_id':
                return $donation->get_meta('yandex_invoice_id');
            default:
                return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {
        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'invoice_id':
            case 'payment_id':
            case 'yandex_recurrent_id':
            case 'yandex_recurring_id':
            case 'yandex_invoice_id':
            case 'yandex_payment_id':
                return $donation->set_meta('yandex_invoice_id', $value);
            default:
                return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['yandex-invoice-id']) && $donation->yandex_invoice_id != $_POST['yandex-invoice-id']) {
            $donation->yandex_invoice_id = $_POST['yandex-invoice-id'];
        }

        $donation->recurring_is_active = !empty($_POST['yandex-recurring-is-active']);

    }

    public function add_donation_specific_data($donation_id, array $params) {
        if( !empty($params['yandex_invoice_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'yandex_invoice_id', $params['yandex_invoice_id']);
        }
    }

    /**
     * A service method to get the gateway inner PM ID value by according Leyka pm_id, and vice versa.
     *
     * @param $pm_id string PM ID (either Leyka or the gateway system).
     * @return string|false A PM ID in the gateway/Leyka system, or false (if PM ID is unknown).
     */
    protected function _get_gateway_pm_id($pm_id) {

        $all_pm_ids = leyka_options()->opt('yandex_new_api') ? [
            'yandex_card' => 'bank_card',
            'yandex_money' => 'yoo_money',
            'yandex_wm' => 'webmoney',
            'yandex_sb' => 'sberbank',
            'yandex_ab' => 'alfabank',
            'yandex_pb' => 'psb',
            'yandex_sbp' => 'sbp', // TODO добавляю тип sbp
        ] : [
            'yandex_all' => '',
            'yandex_card' => 'AC',
            'yandex_money' => 'PC',
            'yandex_wm' => 'WM',
            'yandex_sb' => 'SB',
            'yandex_ab' => 'AB',
            'yandex_pb' => 'PB',
            'yandex_sbp' => 'sbp',// TODO добавляю тип sbp
        ];

        if(array_key_exists($pm_id, $all_pm_ids)) {
            return $all_pm_ids[$pm_id];
        }
        if(in_array($pm_id, $all_pm_ids)) {
            return array_search($pm_id, $all_pm_ids);
        }

        return false;

    }

    protected function _validate_order_number($order_number) {

        if( !absint($order_number) ) { // Recurring donation callback

            $order_number = explode('-', $order_number);
            if(count($order_number) === 3 && $order_number[0] === 'recurring' && absint($order_number[2])) {
                $order_number = absint($order_number[2]);
            } else { // Order number is wrong
                $order_number = false;
            }

        } else { // Single donation callback
            $order_number = absint($order_number);
        }

        return $order_number;

    }

    public function get_legacy_donation_error_id($error_id, Leyka_Donation_Base $donation) {

        if($error_id) {
            return $error_id;
        } else if($donation->status !== 'failed') {
            return false;
        }

        $gateway_response = $donation->gateway_response;
        if(
            is_a($gateway_response, 'YooKassa\Request\Payments\PaymentResponse')
            && $gateway_response->status === 'canceled'
            && !empty($gateway_response->cancellation_details)
            && !empty($gateway_response->cancellation_details->reason)
        ) {

            $error_id = $this->get_donation_error_id($gateway_response->cancellation_details->reason);

            if($error_id) {
                $this->error_id = $error_id;
            }

        }

        return $error_id;

    }

}


class Leyka_Yandex_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_all';
        $this->_gateway_id = 'yandex';
        $this->_category = 'misc';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Any payment method available for the donor and receiver. Donor will be redirected to the gateway payment page to choose specific payment method (instead of choosing it on the website form). More of YooKassa Smart payments <a href="https://yookassa.ru/developers/payments/smart-payment" target="_blank">here</a>.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Smart payment', 'leyka');
        $this->_label = __('Smart payment', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/yandex-money.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_card';
        $this->_gateway_id = 'yandex';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

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
            $this->full_id.'_certificate_path' => [
                'type' => 'text',
                'default' => '',
                'title' => __('Recurring payments certificate path', 'leyka'),
                'comment' => __("Please, enter the path to your SSL certificate given to you by the gateway. <strong>Warning!</strong> The path should include the certificate filename intself. Also it should be relative to wp-content directory.", 'leyka'),
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '/uploads/leyka/your-cert-file.cer'),
                'field_classes' => ['old-api'],
            ],
            $this->full_id.'_private_key_path' => [
                'type' => 'text',
                'default' => '',
                'title' => __("Recurring payments certificate private key path", 'leyka'),
                'comment' => __("Please, enter the path to your SSL certificate's private key given to you by the gateway.<li><li>The path should include the certificate filename intself.</li><li>The path should be relative to wp-content directory. </li></ul>", 'leyka'),
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '/uploads/leyka/your-private.key'),
                'field_classes' => ['old-api'],
            ],
            $this->full_id.'_private_key_password' => [
                'type' => 'text',
                'default' => '',
                'title' => __("Recurring payments certificate private key password", 'leyka'),
                'comment' => __("Please, enter a password for your SSL certificate's private key, if you set this password during the generation of your sertificate request file.", 'leyka'),
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'fW!^12@3#&8A4'),
                'is_password' => true,
                'field_classes' => ['old-api'],
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
        return !!leyka_options()->opt($this->full_id.'_rebilling_available') ? 'active' : false;
    }

}

class Leyka_Yandex_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_money';
        $this->_gateway_id = 'yandex';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('YooMoney', 'leyka');
        $this->_label = __('YooMoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/yandex-money.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_Webmoney extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_wm';
        $this->_gateway_id = 'yandex';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="http://www.webmoney.ru/">WebMoney Transfer</a> is an international financial transactions system and an environment for a business in Internet, founded in 1988. Up until now, WebMoney clients counts at more than 25 million people around the world. WebMoney system includes a services to account and exchange funds, attract new funding, solve quarrels and make a safe deals.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Webmoney', 'leyka');
        $this->_label = __('Webmoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/webmoney.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_Sberbank_Online extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_sb';
        $this->_gateway_id = 'yandex';
        $this->_category = 'online_banking';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="https://online.sberbank.ru/CSAFront/index.do">Sberbank Online</a> is an Internet banking service of Sberbank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Sberbank Online invoicing', 'leyka');
        $this->_label = __('Sberbank Online', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/sber.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_Alpha_Click extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'yandex_ab';
        $this->_gateway_id = 'yandex';
        $this->_category = 'online_banking';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="https://alfabank.ru/retail/internet/">Alfa-Click</a> is an Internet banking service of Alfa bank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Alpha-Click invoicing', 'leyka');
        $this->_label = __('Alpha-Click', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/alfa-click.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_Promvzyazbank extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_pb';
        $this->_gateway_id = 'yandex';
        $this->_category = 'online_banking';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="http://www.psbank.ru/Personal/Everyday/Remote/">PSB-Retail</a> is an Internet banking service of Promsvyazbank. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Promsvyazbank invoicing', 'leyka');
        $this->_label = __('Promsvyazbank', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/promsvyazbank.svg',
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Yandex_SBP extends Leyka_Payment_Method { //TODO расширение от базового метода

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'yandex_sbp';
        $this->_gateway_id = 'yandex';
        $this->_category = 'online_banking';

        $this->_description = apply_filters(
            'leyka_pm_description',//TODO описание продумать
            __('<a href="https://api.yookassa.ru/v3/payments">SBP-YooKassa</a>. It allows you to make many banking operations at any moment without applying to the bank department, using your computer.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('СБП ЮКасса', 'leyka'); //TODO название в админке
        $this->_label = __('SBP YooKassa', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/sbp.svg', //TODO проверить есть ли файл
        ]);

        $this->_custom_fields = apply_filters('leyka_pm_custom_fields_'.$this->_gateway_id.'-'.$this->_id, []);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}
// TODO конец

function leyka_add_gateway_yandex() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Yandex_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_yandex');
