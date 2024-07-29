<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Robokassa_Gateway class
 */

class Leyka_Robokassa_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'robokassa';
        $this->_title = __('Robokassa', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            /* translators: 1: URL, 2: Title. */
            sprintf(__('<a href="%1$s">%2$s</a> gateway allows a simple and safe way to pay for goods and services with bank cards through internet. You will have to fill a payment form, you will be redirected to the secure gateway webpage to enter your payment data and to confirm your payment.', 'leyka'), '//www.robokassa.ru/ru/', $this->_title),
            $this->_id
        );

        $this->_docs_link = '//leyka.org/docs/podklyuchenie-robokassa/#robokassa-settings';
        $this->_registration_link = 'https://partner.robokassa.ru/reg/register';

        $this->_min_commission = 2.7;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'robokassa_shop_id' => [
                'type' => 'text',
                'title' => __('Shop ID', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop ID here. It can be found in your Robokassa control panel (shop technical settings).', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1234'),
            ],
            'robokassa_shop_password1' => [
                'type' => 'text',
                'title' => __('Shop password 1', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop password 1 here. It can be found in your Robokassa control panel (shop technical settings, field "password 1").', 'leyka'),
                'required' => true,
                'is_password' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12abc34+'),
            ],
            'robokassa_shop_password2' => [
                'type' => 'text',
                'title' => __('Shop password 2', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop password 2 here. It can be found in your Robokassa control panel (shop technical settings, field "password 2").', 'leyka'),
                'required' => true,
                'is_password' => true,
                /* translators: %s: Placeholder. */
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12abc34+'),
            ],
            'robokassa_test_mode' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ],
        ];

    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['BANKOCEAN2'])) {
            $this->_payment_methods['BANKOCEAN2'] = Leyka_Robokassa_Card::get_instance();
        }
        if(empty($this->_payment_methods['YandexMerchantOcean'])) {
            $this->_payment_methods['YandexMerchantOcean'] = Leyka_Robokassa_Yandex_Money::get_instance();
        }
        if(empty($this->_payment_methods['WMR'])) {
            $this->_payment_methods['WMR'] = Leyka_Robokassa_Webmoney::get_instance();
        }
        if(empty($this->_payment_methods['Qiwi30Ocean'])) {
            $this->_payment_methods['Qiwi30Ocean'] = Leyka_Robokassa_Qiwi::get_instance();
        }
        if(empty($this->_payment_methods['Other'])) {
            $this->_payment_methods['Other'] = Leyka_Robokassa_All::get_instance();
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
        return 'https://auth.robokassa.ru/Merchant/Index.aspx';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
            return $form_data; // It's not our PM
        }

        $donation = Leyka_Donations::get_instance()->get($donation_id);
	    $amount = number_format((float)$donation->amount, 2, '.', '');
        $hash = md5(leyka_options()->opt('robokassa_shop_id').":$amount:$donation_id:"
               .leyka_options()->opt('robokassa_shop_password1').':Shp_item=1');

        $pm_curr = $pm_id;
        switch($pm_id) {
            case 'WMR': $pm_curr .= 'M'; break;
            case 'Other': $pm_curr = ''; break;
            default: $pm_curr .= 'R';
        }

        $form_data = [
            'MrchLogin' => leyka_options()->opt('robokassa_shop_id'),
            'InvId' => $donation_id,
            'OutSum' => $amount,
            'Desc' => $donation->payment_title,
            'SignatureValue' => $hash,
            'Shp_item' => 1, // Maybe, not needed
            'IncCurrLabel' => $pm_curr, // Default PM + Currency. "R" for "RUB", as we'll always use RUB for now
            'Culture' => get_locale() == 'ru_RU' ? 'ru' : 'en',
            'Email' => $donation->donor_email
        ];

        if(leyka_options()->opt('robokassa_test_mode')) {
            $form_data['IsTest'] = 1;
        }

        if( !empty($_POST['leyka_recurring']) ) {
            $form_data['Recurring'] = 'true';
        }

		return $form_data;

    }

    // The default implementations are in use:
//    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation_Base $donation) { }
//    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) { }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'init_recurring_donation' => $init_recurring_donation->id,
                'date' => '' // don't copy the date
            ],
            ['recalculate_total_amount' => true,]
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $amount = number_format((float)$new_recurring_donation->amount, 2, '.', '');
        $hash = md5(leyka_options()->opt('robokassa_shop_id').":$amount:{$new_recurring_donation->id}:"
            .leyka_options()->opt('robokassa_shop_password1'));

        // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
        $ch = curl_init();
        $params = [
            CURLOPT_URL => 'https://auth.robokassa.ru/Merchant/Recurring',
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'MerchantLogin' => leyka_options()->opt('robokassa_shop_id'),
                'InvoiceID' => $new_recurring_donation->id,
                'PreviousInvoiceID' => $init_recurring_donation->id,
                'Description' => $init_recurring_donation->payment_title,
                'SignatureValue' => $hash,
                'OutSum' => $amount,
            ]),
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ];
        // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
        curl_setopt_array($ch, $params);

        // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
        $answer = curl_exec($ch);
        if($answer) {

            $p = xml_parser_create();
            xml_parse_into_struct($p, $answer, $vals, $index);
            xml_parser_free($p);

            $new_recurring_donation->add_gateway_response($answer);

            if(mb_stristr($answer, 'ERROR') !== false) {
                $new_recurring_donation->status = 'failed';
            } //else if() { // The $answer mb 'OK', but we should wait for the callback
            //}

        } else {
            // phpcs:ignore
            $new_recurring_donation->add_gateway_response('Error '.curl_errno($ch).': '.curl_error($ch));
        }

        curl_close($ch); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

        do_action('leyka_new_rebill_donation_added', $new_recurring_donation);

        return $new_recurring_donation;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);

            if($donation->type !== 'rebill') {
                return;
            }?>

            <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">
                <label for="robokassa-recurring-is-active"><?php esc_html_e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="checkbox" id="robokassa-recurring-is-active" name="robokassa-recurring-is-active" value="1" <?php checked( $init_recurring_donation->recurring_is_active, '1' );?>>
                </div>
            </div>

        <?php }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {
        $donation->recurring_is_active = !empty($_POST['robokasssa-recurring-is-active']);
    }

    public function _handle_service_calls($call_type = '') {

        if(empty($_REQUEST['InvId'])) {

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message = __("This message has been sent because a call to your Robokassa callback (Result URL) was made without InvId parameter given. The details of the call are below.", 'leyka')."\n\r\n\r";

                $message .= "THEIR_POST:\n\r".print_r($_POST, true)."\n\r\n\r";
                $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
                $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER), true)."\n\r\n\r";

                wp_mail(leyka_get_website_tech_support_email(), __('Robokassa - InvId missing!', 'leyka'), $message);

            }

            status_header(200);
            die();

        }

        $donation = Leyka_Donations::get_instance()->get(absint($_REQUEST['InvId']));

		// Test for e-sign. Values from Robokassa must be used:

        $sign = mb_strtoupper(md5("{$_REQUEST['OutSum']}:{$_REQUEST['InvId']}:"
            .leyka_options()->opt('robokassa_shop_password2')
            .($donation->type == 'single' || $donation->is_init_recurring_donation ? ':Shp_item=1' : '')
        ));

        if(empty($_REQUEST['SignatureValue']) || mb_strtoupper($_REQUEST['SignatureValue']) != $sign) {

            $donation->status = 'failed';

            $_REQUEST['failure_reason'] = __('Robokassa digital signature check failed!', 'leyka').
                'Signature (from callback request / calculated): '.$_REQUEST['SignatureValue'].' / '.$sign;

            $donation->add_gateway_response($_REQUEST);

            if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {

                $message = __("This message has been sent because a call to your Robokassa callback was called with wrong digital signature. This could mean someone is trying to hack your payment website. The details of the call are below:", 'leyka')."\n\r\n\r";

                $message .= "POST:\n\r".print_r($_POST,true)."\n\r\n\r";
                $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
                $message .= "SERVER:\n\r".print_r(apply_filters('leyka_notification_server_data', $_SERVER),true)."\n\r\n\r";
                $message .= "Signature from request:\n\r".print_r($_REQUEST['SignatureValue'], true)."\n\r\n\r";
                $message .= "Signature calculated:\n\r".print_r($sign, true)."\n\r\n\r";

                wp_mail(leyka_get_website_tech_support_email(), __('Robokassa digital signature check failed!', 'leyka'), $message);

            }

            die();

        }

        if($donation->status != 'funded') {

            $donation->add_gateway_response($_REQUEST);
            $donation->status = 'funded';

            $_REQUEST['IncCurrLabel'] = empty($_REQUEST['IncCurrLabel']) ?
                '' : substr_replace($_REQUEST['IncCurrLabel'], '', -1);

            if(
                $donation->pm_id != $_REQUEST['IncCurrLabel'] &&
                array_key_exists($_REQUEST['IncCurrLabel'], $this->_payment_methods)
            ) {
                $donation->pm_id = $_REQUEST['IncCurrLabel'];
            }

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
            $inv_id = isset( $_REQUEST['InvId'] ) ? $_REQUEST['InvId'] : '';
            die( 'OK' . esc_html( $inv_id ) );

        } else {
            die();
        }

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) )
            return [];

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('Outcoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'OutSum', !empty($vars['OutSum']) ? round($vars['OutSum'], 2) : false),
                __('Incoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'IncSum', !empty($vars['IncSum']) ? round($vars['IncSum'], 2) : false),
                __('Invoice ID:', 'leyka') => $this->_get_value_if_any($vars, 'InvId'),
                __('Signature value (sent from Robokassa):', 'leyka') => $this->_get_value_if_any($vars, 'SignatureValue'),
                __('Payment method:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentMethod'),
                __('Robokassa currency label:', 'leyka') => $this->_get_value_if_any($vars, 'IncCurrLabel'),
            ],
            $donation
        );

    }

}

class Leyka_Robokassa_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'BANKOCEAN2';
        $this->_gateway_id = 'robokassa';
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

class Leyka_Robokassa_Yandex_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'YandexMerchantOcean';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('YooMoney', 'leyka');
        $this->_label = __('YooMoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/yandex-money.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Robokassa_Webmoney extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'WMR';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Webmoney', 'leyka');
        $this->_label = __('Webmoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/webmoney.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Robokassa_Qiwi extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'Qiwi30Ocean';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Qiwi wallet', 'leyka');
        $this->_label = __('Qiwi wallet', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/qiwi.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

class Leyka_Robokassa_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'Other';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'misc';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Robokassa (any)', 'leyka');
        $this->_label = __('Robokassa (any)', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/robokassa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

function leyka_add_gateway_robokassa() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Robokassa_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_robokassa');
