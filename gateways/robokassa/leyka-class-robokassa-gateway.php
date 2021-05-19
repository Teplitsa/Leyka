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
            __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-robokassa/#robokassa-settings';
        $this->_registration_link = 'https://partner.robokassa.ru/reg/register';

        $this->_min_commission = 3.7;
        $this->_receiver_types = array('legal');

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'robokassa_shop_id' => array(
                'type' => 'text',
                'title' => __('Shop ID', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop ID here. It can be found in your Robokassa control panel (shop technical settings).', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1234'),
            ),
            'robokassa_shop_password1' => array(
                'type' => 'text',
                'title' => __('Shop password 1', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop password 1 here. It can be found in your Robokassa control panel (shop technical settings, field "password 1").', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12abc34+'),
            ),
            'robokassa_shop_password2' => array(
                'type' => 'text',
                'title' => __('Shop password 2', 'leyka'),
                'comment' => __('Please, enter your Robokassa shop password 2 here. It can be found in your Robokassa control panel (shop technical settings, field "password 2").', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '12abc34+'),
            ),
            'robokassa_test_mode' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ),
        );

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

        $donation = new Leyka_Donation($donation_id);

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

        $donation = new Leyka_Donation($donation_id);
	    $amount = number_format((float)$donation->amount, 2, '.', '');
        $hash = md5(leyka_options()->opt('robokassa_shop_id').":$amount:$donation_id:"
               .leyka_options()->opt('robokassa_shop_password1').':Shp_item=1');

        $pm_curr = $pm_id;
        switch($pm_id) {
            case 'WMR': $pm_curr .= 'M'; break;
            case 'Other': $pm_curr = ''; break;
            default: $pm_curr .= 'R';
        }

        $form_data = array(
            'MrchLogin' => leyka_options()->opt('robokassa_shop_id'),
            'InvId' => $donation_id,
            'OutSum' => $amount,
            'Desc' => $donation->payment_title,
            'SignatureValue' => $hash,
            'Shp_item' => 1, // Maybe, not needed
            'IncCurrLabel' => $pm_curr, // Default PM + Currency. "R" for "RUR", as we'll always use RUR for now
            'Culture' => get_locale() == 'ru_RU' ? 'ru' : 'en',
        );

        if(leyka_options()->opt('robokassa_test_mode')) {
            $form_data['IsTest'] = 1;
        }

        if( !empty($_POST['leyka_recurring']) ) {
            $form_data['Recurring'] = 'true';
        }

		return $form_data;

    }

    public function do_recurring_donation(Leyka_Donation $init_recurring_donation) {

        $new_recurring_donation = Leyka_Donation::add_clone(
            $init_recurring_donation,
            array(
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'init_recurring_donation' => $init_recurring_donation->id,
            ),
            array('recalculate_total_amount' => true,)
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $amount = number_format((float)$new_recurring_donation->amount, 2, '.', '');
        $hash = md5(leyka_options()->opt('robokassa_shop_id').":$amount:{$new_recurring_donation->id}:"
            .leyka_options()->opt('robokassa_shop_password1'));

        $ch = curl_init();
        $params = array(
            CURLOPT_URL => 'https://auth.robokassa.ru/Merchant/Recurring',
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'MerchantLogin' => leyka_options()->opt('robokassa_shop_id'),
                'InvoiceID' => $new_recurring_donation->id,
                'PreviousInvoiceID' => $init_recurring_donation->id,
                'Description' => $init_recurring_donation->payment_title,
                'SignatureValue' => $hash,
                'OutSum' => $amount,
            )),
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        );
        curl_setopt_array($ch, $params);

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
            $new_recurring_donation->add_gateway_response('Error '.curl_errno($ch).': '.curl_error($ch));
        }

        curl_close($ch);

        return $new_recurring_donation;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation);

            if($donation->type !== 'rebill') {
                return;
            }?>

            <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">
                <label for="robokassa-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="checkbox" id="robokassa-recurring-is-active" name="robokassa-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
                </div>
            </div>

        <?php }

    }

    public function save_donation_specific_data(Leyka_Donation $donation) {
        $donation->recurring_is_active = !empty($_POST['robokasssa-recurring-is-active']);
    }

    public function _handle_service_calls($call_type = '') {

        if(empty($_REQUEST['InvId'])) {

            $message = __("This message has been sent because a call to your Robokassa callback (Result URL) was made without InvId parameter given. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Robokassa - InvId missing!', 'leyka'), $message);
            status_header(200);
            die();

        }

        $donation = new Leyka_Donation(absint($_REQUEST['InvId']));

		// Test for e-sign. Values from Robokassa must be used:

        $sign = mb_strtoupper(md5("{$_REQUEST['OutSum']}:{$_REQUEST['InvId']}:"
            .leyka_options()->opt('robokassa_shop_password2')
            .($donation->type == 'single' || $donation->is_init_recurring_donation ? ':Shp_item=1' : '')
        ));

        if(empty($_REQUEST['SignatureValue']) || mb_strtoupper($_REQUEST['SignatureValue']) != $sign) {

            $message = __("This message has been sent because a call to your Robokassa callback was called with wrong digital signature. This could mean someone is trying to hack your payment website. The details of the call are below:", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
            $message .= "Signature from request:\n\r".print_r($_REQUEST['SignatureValue'], true)."\n\r\n\r";
            $message .= "Signature calculated:\n\r".print_r($sign, true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Robokassa digital signature check failed!', 'leyka'), $message);

            $donation->status = 'failed';

            $_REQUEST['failure_reason'] = __('Robokassa digital signature check failed!', 'leyka').
                'Signature (from callback request / calculated): '.$_REQUEST['SignatureValue'].' / '.$sign;

            $donation->add_gateway_response($_REQUEST);

            die();

        }

        // Single payment:
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
                    ->addProduct(array( // Donation params
                        'name' => $donation->payment_title,
                        'price' => $donation->amount,
                        'brand' => get_bloginfo('name'), // Mb, it won't work with it
                        'category' => $donation->type_label, // Mb, it won't work with it
                        'quantity' => 1,
                    ))
                    ->setProductActionToPurchase()
                    ->setEventCategory('Checkout')
                    ->setEventAction('Purchase')
                    ->sendEvent();

            }
            // GUA direct integration - "purchase" event END

            die('OK'.$_REQUEST['InvId']);

        } else {
            die();
        }

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) )
            return array();

        return array(
            __('Outcoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'OutSum', !empty($vars['OutSum']) ? round($vars['OutSum'], 2) : false),
            __('Incoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'IncSum', !empty($vars['IncSum']) ? round($vars['IncSum'], 2) : false),
            __('Invoice ID:', 'leyka') => $this->_get_value_if_any($vars, 'InvId'),
            __('Signature value (sent from Robokassa):', 'leyka') => $this->_get_value_if_any($vars, 'SignatureValue'),
            __('Payment method:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentMethod'),
            __('Robokassa currency label:', 'leyka') => $this->_get_value_if_any($vars, 'IncCurrLabel'),
        );

    }

}

class Leyka_Robokassa_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'BANKOCEAN2';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_rebilling_available' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'comment' => __('Check if the gateway allows you to create recurrent subscriptions to do regular automatic payments.', 'leyka'),
                'short_format' => true,
            ),
        );

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

        $this->_description = apply_filters(
            'leyka_pm_description',
            __("Yandex.Money is a simple and safe payment system to pay for goods and services through internet. You will have to fill a payment form, you will be redirected to the <a href='https://money.yandex.ru/'>Yandex.Money website</a> to confirm your payment. If you haven't aquired a Yandex.Money account yet, you can create it there.", 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Payment with Yandex.Money', 'leyka');
        $this->_label = __('Yandex.Money', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

class Leyka_Robokassa_Webmoney extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'WMR';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="http://www.webmoney.ru/">WebMoney Transfer</a> is an international financial transactions system and an environment for a business in Internet, founded in 1988. Up until now, WebMoney clients counts at more than 25 million people around the world. WebMoney system includes a services to account and exchange funds, attract new funding, solve quarrels and make a safe deals.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Payment with Webmoney', 'leyka');
        $this->_label = __('Webmoney', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/webmoney.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

class Leyka_Robokassa_Qiwi extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'Qiwi30Ocean';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Payment with Qiwi wallet', 'leyka');
        $this->_label = __('Qiwi wallet', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/qiwi.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

class Leyka_Robokassa_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'Other';
        $this->_gateway_id = 'robokassa';
        $this->_category = 'misc';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Use any Robokassa payment method available', 'leyka');
        $this->_label = __('Robokassa (any)', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

function leyka_add_gateway_robokassa() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Robokassa_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_robokassa');