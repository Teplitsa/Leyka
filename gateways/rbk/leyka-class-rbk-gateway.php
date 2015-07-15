<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Rbk_Gateway class
 */

class Leyka_Rbk_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'rbk';
        $this->_title = __('RBK Money', 'leyka');
        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-robokassa/#rbk-settings';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 50;
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'rbk_eshop_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('RBK Money eshopId', 'leyka'),
                'description' => __('Please, enter your eshopId value here. It can be found in your contract with RBK Money or in your control panel there.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 1234', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'rbk_eshop_account' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('RBK Money Eshop Account', 'leyka'),
                'description' => __('Please, enter your Eshop Account value here. It can be found in your RBK Money control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., RU123456789', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'rbk_use_hash' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => false,
                'default' => false,
                'title' => __('Use hash to enforce payments safety', 'leyka'),
                'description' => __("Please, check this to sign your donation data with hashes to improve safety of your donations through RBK. IMPORTANT: all hash settings must be concured with their counterparts in your RBK Shop Settings.", 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'rbk_hash_type' => array(
                'type' => 'select',
                'default' => 'md5',
                'title' => __('Hash type', 'leyka'),
                'description' => __('Select a hash algorythm for all checking data integrity uses.', 'leyka'),
                'required' => 0, // 1 if field is required, 0 otherwise
                'placeholder' => '', // For text fields
                'length' => '', // For text fields
                'list_entries' => array('md5' => 'MD5', 'sha512' => 'SHA512',),
                'validation_rules' => array(), // List of regexp?..
            ),
            'rbk_secret_key' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('RBK Money secret key', 'leyka'),
                'description' => __('Please, enter your secret key value here. It can be found in your RBK Money Shop Settings.', 'leyka'),
                'required' => 0,
                'placeholder' => __('Ex., fW!^12@3#&8A4', 'leyka'),
                'is_password' => true,
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['bankcard'])) {
            $this->_payment_methods['bankcard'] = Leyka_Rbk_Card::get_instance();
        }
        if(empty($this->_payment_methods['rbkmoney'])) {
            $this->_payment_methods['rbkmoney'] = Leyka_Rbk_Money::get_instance();
        }
        if(empty($this->_payment_methods['rbk_all'])) {
            $this->_payment_methods['rbk_all'] = Leyka_Rbk_All::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        return 'https://rbkmoney.ru/acceptpurchase.aspx';
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) )
			return $form_data_vars; //it's not our PM

        $donation = new Leyka_Donation($donation_id);
	    $amount = number_format((float)$donation->amount, 2, '.', '');
        $currency = mb_strtoupper($donation->currency);

        $form_data_vars =  array(
            'eshopId' => leyka_options()->opt('rbk_eshop_id'),
            'orderId' => $donation_id,
            'direct' => 'false', // All sum is required to complete the donation
            'serviceName' => $donation->payment_title,
            'recipientAmount' => $amount,
            'recipientCurrency' => $currency,
            'user_email' => $donation->donor_email,
            'successUrl' => leyka_get_success_page_url(),
            'failUrl' => leyka_get_failure_page_url(),
            'language' => get_locale() == 'ru_RU' ? 'ru' : 'en',
            'preference' => $pm_id == 'rbk_all' ? '' : $pm_id,
//            '' => '',
        );

        if(leyka_options()->opt('rbk_use_hash')) {

            $form_data_vars['hash'] = hash(
                leyka_options()->opt('rbk_hash_type'), // Hash algorithm is md5 or sha512
                implode('::', array(
                    leyka_options()->opt('rbk_eshop_id'),
                    $amount,
                    $currency,
                    $donation->donor_email,
                    $donation->payment_title,
                    $donation_id,
                    '', // There is no user fields on donation form
                    leyka_options()->opt('rbk_secret_key'),
                ))
            );
        }

		return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        if(empty($_POST['orderId'])) {

            $message = __("This message has been sent because a call to your RBK Money callback was made without orderId parameter given. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('RBK Money - orderId missing!', 'leyka'), $message);
            status_header(200);
            die();
        }

        $donation = new Leyka_Donation((int)stripslashes($_POST['orderId']));

        if( !$donation ) {
            status_header(200);
            die();
        }

		// Test for e-sign:
        if(leyka_options()->opt('rbk_use_hash')) {

            $sign = hash(
                leyka_options()->opt('rbk_hash_type'), // Hash algorythm is md5 or sha512
                implode('::', array(
                    leyka_options()->opt('rbk_eshop_id'),
                    $_POST['orderId'], // Donation ID
                    $_POST['serviceName'], // Payment title / donation purpose
                    leyka_options()->opt('rbk_eshop_account'),
                    $donation->amount,
                    mb_strtoupper($donation->currency),
                    $_POST['paymentStatus'],
                    $_POST['userName'],
                    $donation->donor_email,
                    $_POST['paymentData'],
                    leyka_options()->opt('rbk_secret_key'),
                ))
            );

            if(empty($_POST['hash']) || $sign != trim(mb_strtolower($_POST['hash']))) {

                $message = __("This message has been sent because a call to your RBK Money callback was called with wrong data hash. This could mean someone is trying to hack your payment site. The details of the call are below.", 'leyka')."\n\r\n\r";

                $message .= "POST:\n\r".print_r($_POST,true)."\n\r\n\r";
                $message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
                $message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";

                wp_mail(get_option('admin_email'), __('RBK Money hash check failed!', 'leyka'), $message);
                status_header(200);
                die();
            }
        }

        // Single payment:
        switch($_POST['paymentStatus']) {
            case 4: $new_status = 'failed'; break;
            case 5: $new_status = 'funded'; break;
            default:
                $new_status = 'submitted';
        }

        if($donation->status != $new_status) {

            $donation->add_gateway_response($_POST);
            $donation->status = $new_status;

            if( !$donation->donor_email && !empty($_POST['userEmail']) )
                $donation->donor_email = $_POST['userEmail'];
            if( !$donation->donor_name && !empty($_POST['userName']) )
                $donation->donor_name = $_POST['userName'];

            Leyka_Donation_Management::send_all_emails($donation->id);
        }

		status_header(200);
		die();
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
            __('Operation date:', 'leyka') => $this->_get_value_if_any($vars, 'paymentData', date('d.m.Y, H:i:s', strtotime($vars['paymentData']))),
            __('Invoice ID:', 'leyka') => $this->_get_value_if_any($vars, 'invoiceId'),
			__("RBK Money payment ID:", 'leyka') => $this->_get_value_if_any($vars, 'paymentId'),
			__('Shop Account:', 'leyka') => $this->_get_value_if_any($vars, 'eshopAccount'),
			__('Full donation amount:', 'leyka') => $this->_get_value_if_any($vars, 'purchaseAmount'),
			__('Recipient amount:', 'leyka') => $this->_get_value_if_any($vars, 'recipientAmount'),
			__('Total recieved amount:', 'leyka') => $this->_get_value_if_any($vars, 'merchantPaymentAmount'),
			__('Donation currency:', 'leyka') => $this->_get_value_if_any($vars, 'recipientCurrency'),
			__('Payment method selected:', 'leyka') => $this->_get_value_if_any($vars, 'paymentMethod'), /** @todo Use PM label */
            __('Operation status:', 'leyka') => $this->_get_value_if_any($vars, 'paymentStatus'),
			__('Donor name:', 'leyka') => $this->_get_value_if_any($vars, 'userName'),
			__("Payment decline reason (if it's declined):", 'leyka') => $this->_get_value_if_any($vars, 'declineMessage'),
			__("Payment decline code (if it's declined):", 'leyka') => $this->_get_value_if_any($vars, 'declineCode'),
        );		
    }
} // Gateway class end


class Leyka_Rbk_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'bankcard';
        $this->_gateway_id = 'rbk';

        $this->_label_backend = __('Payment with Banking Card', 'leyka');
        $this->_label = __('Banking Card', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/master.png',
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
                'default' => __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('RBK Money bank card payment description', 'leyka'),
                'description' => __('Please, enter RBK Money gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Rbk_Money extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'rbkmoney';
        $this->_gateway_id = 'rbk';

        $this->_label_backend = __('Payment with RBK Money', 'leyka');
        $this->_label = __('RBK Money', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/rbk.png',
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
                'default' => __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('RBK Money payment description', 'leyka'),
                'description' => __('Please, enter RBK Money gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

class Leyka_Rbk_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'rbk_all';
        $this->_gateway_id = 'rbk';

        $this->_label_backend = __('Use any RBK Money payment method available', 'leyka');
        $this->_label = __('RBK Money (any)', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/master.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/rbk/icons/rbk.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options){
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
                'title' => __('RBK Money all possible payment types description', 'leyka'),
                'description' => __('Please, enter RBK Money gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_rbk() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Rbk_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_rbk');