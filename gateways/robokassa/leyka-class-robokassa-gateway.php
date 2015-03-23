<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Robokassa_Gateway class
 */

class Leyka_Robokassa_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {

        $this->_id = 'robokassa';
        $this->_title = __('Robokassa', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'robokassa_shop_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Shop ID', 'leyka'),
                'description' => __('Please, enter your Robokassa shop ID here. It can be found in your Robokassa control panel (shop technical settings).', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., 1234', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'robokassa_shop_password1' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('Shop password 1', 'leyka'),
                'description' => __('Please, enter your Robokassa shop password 1 here. It can be found in your Robokassa control panel (shop technical settings, field "password 1").', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => __('Ex., 12abc34+', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'robokassa_shop_password2' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('Shop password 2', 'leyka'),
                'description' => __('Please, enter your Robokassa shop password 2 here. It can be found in your Robokassa control panel (shop technical settings, field "password 2").', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => __('Ex., 12abc34+', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'robokassa_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
                'title' => __('Shop testing mode', 'leyka'),
                'description' => __('Check if Robokassa shop is in testing mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['BANKOCEAN2'])) {
            $this->_payment_methods['BANKOCEAN2'] = Leyka_Robokassa_Card::get_instance();
            $this->_payment_methods['BANKOCEAN2']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['YandexMerchantOcean'])) {
            $this->_payment_methods['YandexMerchantOcean'] = Leyka_Robokassa_Yandex_Money::get_instance();
            $this->_payment_methods['YandexMerchantOcean']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['WMR'])) {
            $this->_payment_methods['WMR'] = Leyka_Robokassa_Webmoney::get_instance();
            $this->_payment_methods['WMR']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['Qiwi30Ocean'])) {
            $this->_payment_methods['Qiwi30Ocean'] = Leyka_Robokassa_Qiwi::get_instance();
            $this->_payment_methods['Qiwi30Ocean']->initialize_pm_options();
        }

        if(empty($this->_payment_methods['Other'])) {
            $this->_payment_methods['Other'] = Leyka_Robokassa_All::get_instance();
            $this->_payment_methods['Other']->initialize_pm_options();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        return leyka_options()->opt('robokassa_test_mode') ?
            'http://test.robokassa.ru/Index.aspx' : 'https://auth.robokassa.ru/Merchant/Index.aspx';
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) )
			return $form_data_vars; // It's not our PM

        $donation = new Leyka_Donation($donation_id);
	    $amount = number_format((float)$donation->amount, 2, '.', '');
//        $currency = mb_strtoupper($donation->currency);
        $hash = md5(leyka_options()->opt('robokassa_shop_id').":$amount:$donation_id:"
               .leyka_options()->opt('robokassa_shop_password1').":Shp_item=1");

        $pm_curr = $pm_id;
        switch($pm_id) {
            case 'WMR': $pm_curr .= 'M'; break;
            case 'Other': $pm_curr = ''; break;
            default: $pm_curr .= 'R';
        }

        $form_data_vars = array(
            'MrchLogin' => leyka_options()->opt('robokassa_shop_id'),
            'InvId' => $donation_id,
            'OutSum' => $amount,
            'Desc' => $donation->payment_title,
            'SignatureValue' => $hash,
            'Shp_item' => 1, // Maybe, not needed
            'IncCurrLabel' => $pm_curr, // Default PM + Currency. "R" for "RUR", as we'll always use RUR for now
            'Culture' => get_locale() == 'ru_RU' ? 'ru' : 'en',
        );

		return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
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

        $donation = new Leyka_Donation((int)$_REQUEST['InvId']);

		// Test for e-sign. Values from Robokassa must be used:

        $sign = strtoupper(md5("{$_REQUEST['OutSum']}:{$_REQUEST['InvId']}:".leyka_options()->opt('robokassa_shop_password2').":Shp_item=1"));
        if(empty($_REQUEST['SignatureValue']) || strtoupper($_REQUEST['SignatureValue']) != $sign) {

            $message = __("This message has been sent because a call to your Robokassa callback was called with wrong digital signature. This could mean someone is trying to hack your payment website. The details of the call are below:", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
            $message .= "Signature from request:\n\r".print_r($_REQUEST['SignatureValue'], true)."\n\r\n\r";
            $message .= "Signature calculated:\n\r".print_r($sign, true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Robokassa digital signature check failed!', 'leyka'), $message);
            die();
        }

        // Single payment:
        if($donation->status != 'funded') {

            $donation->add_gateway_response($_REQUEST);
            $donation->status = 'funded';

//            $currency_letter = substr($_REQUEST['IncCurrLabel'], -1);
            $_REQUEST['IncCurrLabel'] = empty($_REQUEST['IncCurrLabel']) ?
                '' : substr_replace($_REQUEST['IncCurrLabel'], '', -1);

            if(
                $donation->pm_id != $_REQUEST['IncCurrLabel'] &&
                array_key_exists($_REQUEST['IncCurrLabel'], $this->_payment_methods)
            ) {
                $donation->pm_id = $_REQUEST['IncCurrLabel'];
            }

            Leyka_Donation_Management::send_all_emails($donation->id);

            die('OK'.$_REQUEST['InvId']);

        } else {
            die();
        }
    }

    protected function _get_value_if_any($arr, $key, $val = false) {

        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response )
            return array();

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
} // Gateway class end


class Leyka_Robokassa_Card extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Robokassa_Card */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'BANKOCEAN2' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Banking Card', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Banking Card', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('robokassa_card_description') : $params['desc'];

        $this->_gateway_id = 'robokassa';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'rur';
        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'robokassa_card_description' => array(
                'type' => 'html',
                'default' => __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Robokassa bank card payment description', 'leyka'),
                'description' => __('Please, enter Robokassa gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Robokassa_Yandex_Money extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Robokassa_Yandex_Money */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'YandexMerchantOcean' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Yandex.Money', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Yandex.Money', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('robokassa_yandex_money_description') : $params['desc'];

        $this->_gateway_id = 'robokassa';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'rur';
        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'robokassa_yandex_money_description' => array(
                'type' => 'html',
                'default' => __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Robokassa Yandex.Money payment description', 'leyka'),
                'description' => __('Please, enter Robokassa gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Robokassa_Webmoney extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Robokassa_Webmoney */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'WMR' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Webmoney', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Webmoney', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('robokassa_webmoney_description') : $params['desc'];

        $this->_gateway_id = 'robokassa';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'rur';
        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'robokassa_webmoney_description' => array(
                'type' => 'html',
                'default' => __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Robokassa Webmoney payment description', 'leyka'),
                'description' => __('Please, enter Robokassa gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Robokassa_Qiwi extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Robokassa_Qiwi */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'Qiwi30Ocean' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Payment with Qiwi wallet', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Qiwi wallet', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('robokassa_qiwi_description') : $params['desc'];

        $this->_gateway_id = 'robokassa';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'rur';
        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'robokassa_qiwi_description' => array(
                'type' => 'html',
                'default' => __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Robokassa Qiwi wallet payment description', 'leyka'),
                'description' => __('Please, enter Robokassa gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}


class Leyka_Robokassa_All extends Leyka_Payment_Method {

    /** @var $_instance Leyka_Robokassa_All */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'Other' : $params['id'];

        $this->_label_backend = empty($params['label_backend']) ?
            __('Use any Robokassa payment method available', 'leyka') : $params['label_backend'];
        $this->_label = empty($params['label']) ? __('Robokassa (any)', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('robokassa_all_description') : $params['desc'];

        $this->_gateway_id = 'robokassa';

        $this->_active = isset($params['active']) ? 1 : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : true;

        $this->_custom_fields = empty($params['custom_fields']) ? array() : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/robokassa/icons/master.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        if(empty($params['currencies'])) {

            $this->_supported_currencies[] = 'rur';
        } else {
            $this->_supported_currencies = $params['currencies'];
        }

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'robokassa_all_description' => array(
                'type' => 'html',
                'default' => __('Robokassa system allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="http://www.robokassa.ru/ru/">Robokassa</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('Robokassa all possible payment types description', 'leyka'),
                'description' => __('Please, enter Robokassa gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_robokassa() {
    leyka()->add_gateway(Leyka_Robokassa_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_robokassa', 11);