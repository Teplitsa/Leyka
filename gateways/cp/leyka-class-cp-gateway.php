<?php if( !defined('WPINC') ) die;
/**
 * Leyka_CP_Gateway class
 */

class Leyka_CP_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'cp';
        $this->_title = __('CloudPayments', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'cp_public_id' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox  
                'value' => '',
                'default' => '',
                'title' => __('Public ID', 'leyka'),
                'description' => __('Please, enter your CloudPayments public ID here. It can be found in your CloudPayments control panel.', 'leyka'),
                'required' => true,
                'placeholder' => __('Ex., 1234', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'cp_ip' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '130.193.70.192',
                'title' => __('CloudPayments IP', 'leyka'),
                'description' => __('IP address to check for requests.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 130.193.70.192', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'cp_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
                'title' => __('Testing mode', 'leyka'),
                'description' => __('Check if CloudPayments shop account is in testing mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_CP_Card::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
    }

    public function submission_redirect_url($current_url, $pm_id) {

        // CP isn't using redirection to safely ask donor for his bank card data:
        return leyka_options()->opt('cp_test_mode') ?
            '' : '';
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data_vars; // It's not our PM
        }

        $donation = new Leyka_Donation($donation_id);

        $cp_currency = 'RUB';
        switch($_POST['leyka_donation_currency']) {
            case 'usd': $cp_currency = 'USD'; break;
            case 'eur': $cp_currency = 'EUR'; break;
            default:
        }

        $form_data_vars = array(
            'public_id' => leyka_options()->opt('cp_public_id'),
            'donation_id' => $donation_id,
            'amount' => number_format((float)$donation->amount, 2, '.', ''),
            'currency' => $cp_currency,
            'payment_title' => $donation->payment_title,
            'donor_email' => $donation->donor_email,
            'success_page' => get_permalink(leyka_options()->opt('success_page')),
            'failure_page' => get_permalink(leyka_options()->opt('failure_page')),
        );

		return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        // Test for gateway's IP:
        if(
            leyka_options()->opt('cp_ip') &&
            !in_array($_SERVER['REMOTE_ADDR'], explode(',', leyka_options()->opt('cp_ip')))
        ) { // Security fail

            $message = __("This message has been sent because a call to your CloudPayments function was made from an IP that did not match with the one in your CloudPayments gateway setting. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r";

            $message .= "POST:\n\r".print_r($_POST, true)."\n\r\n\r";
            $message .= "GET:\n\r".print_r($_GET, true)."\n\r\n\r";
            $message .= "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";
            $message .= "IP:\n\r".print_r($_SERVER['REMOTE_ADDR'], true)."\n\r\n\r";
            $message .= "CloudPayments IP setting value:\n\r".print_r(leyka_options()->opt('cp_ip'),true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('CloudPayments IP check failed!', 'leyka'), $message);
            status_header(200);
            die();
        }

        switch($call_type) {

            case 'check': // Check if payment is correct

                if(empty($_POST['InvoiceId']) || (int)$_POST['InvoiceId'] <= 0) { // Donation ID
                    die(json_encode(array('code' => '10')));
                }

                if(empty($_POST['Amount']) || (float)$_POST['Amount'] <= 0 || empty($_POST['Currency'])) {
                    die(json_encode(array('code' => '11')));
                }

                $donation = new Leyka_Donation((int)$_POST['InvoiceId']);
                $donation->add_gateway_response($_POST);

                switch($_POST['Currency']) {
                    case 'RUB': $_POST['Currency'] = 'rur'; break;
                    case 'USD': $_POST['Currency'] = 'usd'; break;
                    case 'EUR': $_POST['Currency'] = 'eur'; break;
                    default:
                }

                if($donation->sum != $_POST['Amount'] || $donation->currency != $_POST['Currency']) {
                    die(json_encode(array('code' => '11')));
                }

                die(json_encode(array('code' => '0'))); // Payment check passed

            case 'complete':

                if(empty($_POST['InvoiceId']) || (int)$_POST['InvoiceId'] <= 0) { // Donation ID
                    die(json_encode(array('code' => '10')));
                }

                $donation = new Leyka_Donation((int)$_POST['InvoiceId']);

                $donation->add_gateway_response($_POST);
                $donation->status = 'funded';
                Leyka_Donation_Management::send_all_emails($donation->id);

                die(json_encode(array('code' => '0'))); // Payment completed

            case 'fail':

                if(empty($_POST['InvoiceId']) || (int)$_POST['InvoiceId'] <= 0) { // Donation ID
                    die(json_encode(array('code' => '10')));
                }

                $donation = new Leyka_Donation((int)$_POST['InvoiceId']);

                $donation->add_gateway_response($_POST);
                $donation->status = 'failed';

                die(json_encode(array('code' => '0'))); // Payment failure registered

            default:
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
        if( !$vars || !is_array($vars) ) {
            return array();
        }

        $vars_final = array(
            __('Invoice ID:', 'leyka') => $this->_get_value_if_any($vars, 'TransactionId'),
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
        );

        if( !empty($vars['reason']) ) {
            $vars_final[__('Donation failure reason', 'leyka')] = $vars['reason'];
        }
        if( !empty($vars['SubscriptionId']) ) {
            $vars_final[__('Recurrent subscription ID:', 'leyka')] = $this->_get_value_if_any($vars, 'SubscriptionId');
        }
        if( !empty($vars['StatusCode']) ) {
            $vars_final[__('Invoice status code:', 'leyka')] = $this->_get_value_if_any($vars, 'StatusCode');
        }

        return $vars_final;
    }
} // Gateway class end


class Leyka_CP_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'cp';

        $this->_label_backend = __('Payment with Banking Card', 'leyka');
        $this->_label = __('Banking Card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/cp/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/cp/icons/master.png',
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
                'default' => __('<a href="//cloudpayments.ru/">CloudPayments</a> is a Designer IT-solutions for the e-commerce market. Every partner receives the most comprehensive set of key technical options allowing to create a customer-centric payment system on site or in mobile application. Partners are allowed to receive payments in roubles and in other world currencies.', 'leyka'),
                'title' => __('CloudPayments bank card payment description', 'leyka'),
                'description' => __('Please, enter CloudPayments gateway description that will be shown to the donor when this payment method will be selected for using.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_cp() {
    leyka()->add_gateway(Leyka_CP_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_cp');

add_action('wp_enqueue_scripts', 'leyka_enqueue_scripts_cp');
function leyka_enqueue_scripts_cp() {

    if(Leyka_CP_Card::get_instance()->active && leyka_form_is_screening()) {
        wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments');
        wp_enqueue_script(
            'leyka-cp',
            LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_CP_Gateway::get_instance()->id.'/js/leyka.cp.js',
            array('jquery', 'leyka-cp-widget', 'leyka-public'),
            LEYKA_VERSION,
            true
        );
    }
}