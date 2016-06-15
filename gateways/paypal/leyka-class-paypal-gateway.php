<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Paypal_Gateway class
 */

class Leyka_Paypal_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'paypal';
        $this->_title = __('PayPal', 'leyka');
        $this->_docs_link = '';
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 30;
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'paypal_api_username' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('API username', 'leyka'),
                'description' => __('Please, enter your PayPal API username here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., testuser_api.ngo.ru', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_password' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('API password', 'leyka'),
                'description' => __('Please, enter your PayPal API password here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., 123456_mypass_ngo.ru', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_api_signature' => array(
                'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => '',
                'title' => __('API signature', 'leyka'),
                'description' => __('Please, enter your PayPal API signature here. It can be found in your PayPal control panel.', 'leyka'),
                'required' => 1,
                'placeholder' => __('Ex., AfCWxV27c7fd0v3HYyYRCpSSRl34ArUqPITpDcKswTlMg-j9m.AzS-17', 'leyka'),
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
            'paypal_test_mode' => array(
                'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
                'value' => '',
                'default' => 1,
                'title' => __('Payments testing mode', 'leyka'),
                'description' => __('Check if PayPal business account is in testing (sandbox) mode.', 'leyka'),
                'required' => false,
                'placeholder' => '',
                'list_entries' => array(), // For select, radio & checkbox fields
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['all'])) {
            $this->_payment_methods['all'] = Leyka_Paypal_All::get_instance();
        }
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Paypal_All::get_instance()->active) {

            wp_enqueue_script('leyka-paypal-widget', 'https://www.paypalobjects.com/api/checkout.js');
            wp_enqueue_script(
                'leyka-paypal',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Paypal_Gateway::get_instance()->id.'/js/leyka.paypal.js',
                array('jquery', 'leyka-paypal-widget', 'leyka-public'),
                LEYKA_VERSION,
                true
            );
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

//        $donation = new Leyka_Donation($donation_id);
//
//        if( !empty($form_data['leyka_recurring']) ) {
//            $donation->payment_type = 'rebill';
//        }
    }

    public function submission_redirect_url($current_url, $pm_id) {

        return leyka_options()->opt('paypal_test_mode') ?
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
//            'public_id' => trim(leyka_options()->opt('cp_public_id')),
//            'donation_id' => $donation_id,
//            'amount' => number_format((float)$donation->amount, 2, '.', ''),
//            'currency' => $cp_currency,
//            'payment_title' => $donation->payment_title,
//            'donor_email' => $donation->donor_email,
//            'success_page' => get_permalink(leyka_options()->opt('success_page')),
//            'failure_page' => get_permalink(leyka_options()->opt('failure_page')),
        );

        return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

//            case 'check': // Check if payment is correct
//
//                // InvoiceId - leyka donation ID, SubscriptionId - CP recurring subscription ID
//                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
//                    die(json_encode(array('code' => '10')));
//                }
//
//                if(empty($_POST['Amount']) || (float)$_POST['Amount'] <= 0 || empty($_POST['Currency'])) {
//                    die(json_encode(array(
//                        'code' => '11',
//                        'reason' => sprintf(
//                            'Amount or Currency in POST are empty. Amount: %s, Currency: %s',
//                            $_POST['Amount'], $_POST['Currency']
//                        )
//                    )));
//                }
//
//                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation
//
//                    if( !$this->get_init_recurrent_donation($_POST['SubscriptionId']) ) {
//                        die(json_encode(array(
//                            'code' => '11',
//                            'reason' => sprintf(
//                                'Init recurring payment is not found. POST SubscriptionId: %s',
//                                $_POST['SubscriptionId']
//                            )
//                        )));
//                    }
//
//                } else { // Single or init recurring donation
//
//                    $donation = new Leyka_Donation((int)$_POST['InvoiceId']);
//                    $donation->add_gateway_response($_POST);
//
//                    switch($_POST['Currency']) {
//                        case 'RUB': $_POST['Currency'] = 'rur'; break;
//                        case 'USD': $_POST['Currency'] = 'usd'; break;
//                        case 'EUR': $_POST['Currency'] = 'eur'; break;
//                        default:
//                    }
//
//                    if($donation->sum != $_POST['Amount'] || $donation->currency != $_POST['Currency']) {
//                        die(json_encode(array(
//                            'code' => '11',
//                            'reason' => sprintf(
//                                'Amount of original data and POST are mismatching. Original: %.2f %s, POST: %.2f %s',
//                                $donation->sum, $donation->currency, $_POST['Amount'], $_POST['Currency']
//                            )
//                        )));
//                    }
//                }
//
//                die(json_encode(array('code' => '0'))); // Payment check passed

            case 'complete':
            case 'success':
            case 'fail':

                // InvoiceId - leyka donation ID, SubscriptionId - CP recurring subscription ID
//                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
//                    die(json_encode(array('code' => '10')));
//                }

//                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation
//
//                    $donation = $this->get_donation_by_transaction_id($_POST['TransactionId']);
//
//                    $init_recurrent_payment = $this->get_init_recurrent_donation($_POST['SubscriptionId']);
//
//                    $donation->init_recurring_donation_id = $init_recurrent_payment->id;
//                    $donation->payment_title = $init_recurrent_payment->title;
//                    $donation->campaign_id = $init_recurrent_payment->campaign_id;
//                    $donation->payment_method_id = $init_recurrent_payment->pm_id;
//                    $donation->gateway_id = $init_recurrent_payment->gateway_id;
//                    $donation->donor_name = $init_recurrent_payment->donor_name;
//                    $donation->donor_email = $init_recurrent_payment->donor_email;
//                    $donation->amount = $init_recurrent_payment->amount;
//                    $donation->currency = $init_recurrent_payment->currency;
//
//                } else { // Single or init recurring donation
//                    $donation = new Leyka_Donation((int)$_POST['InvoiceId']);
//                }
//
//                if( !empty($_POST['SubscriptionId']) ) {
//
//                    $donation->payment_type = 'rebill';
//                    $donation->recurring_id = $_POST['SubscriptionId'];
//                }
//
//                $donation->add_gateway_response($_POST);
//
//                if($call_type == 'complete') {
//
//                    Leyka_Donation_Management::send_all_emails($donation->id);
//                    $donation->status = 'funded';
//
//                } else {
//                    $donation->status = 'failed';
//                }
//
//                die(json_encode(array('code' => '0'))); // Payment completed / fail registered

            default:
        }
    }

    /**
     * It is possible for PayPal to call a callback several times for one donation.
     * This donation must be created only once and then updated. It can be identified with PayPal token (unique payment ID).
     *
     * @param $paypal_payment_token string
     * @return Leyka_Donation
     */
    public function get_donation_by_token($paypal_payment_token) {

        $donation = get_posts(array( // Get init recurrent payment with customer_id given
            'posts_per_page' => 1,
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'meta_query' => array(
                'RELATION' => 'AND',
                array(
                    'key'     => '_paypal_token',
                    'value'   => $paypal_payment_token,
                    'compare' => '=',
                ),
            ),
            'orderby' => 'date',
            'order' => 'ASC',
        ));

        if(count($donation)) {
            $donation = new Leyka_Donation($donation[0]->ID);
        } else {
            $donation = new Leyka_Donation(Leyka_Donation::add(array(
                'status' => 'submitted',
                'paypal_token' => $paypal_payment_token,
            )));
        }

        return $donation;
    }

//    public function get_init_recurrent_donation($recurring) {

//        if(is_a($recurring, 'Leyka_Donation')) {
//            $recurring = $recurring->recurring_id;
//        } elseif(empty($recurring)) {
//            return false;
//        }
//
//        $init_donation_post = array(); // get_posts(array( // Get init recurrent payment with customer_id given
//            'posts_per_page' => 1,
//            'post_type' => Leyka_Donation_Management::$post_type,
//            'post_status' => 'funded',
//            'post_parent' => 0,
//            'meta_query' => array(
//                'RELATION' => 'AND',
//                array(
//                    'key'     => '_cp_recurring_id',
//                    'value'   => $recurring,
//                    'compare' => '=',
//                ),
//                array(
//                    'key'     => 'leyka_payment_type',
//                    'value'   => 'rebill',
//                    'compare' => '=',
//                ),
//            ),
//            'orderby' => 'date',
//            'order' => 'ASC',
//        ));

//        return count($init_donation_post) ? new Leyka_Donation($init_donation_post[0]->ID) : false;
//    }

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

        $vars_final = $vars; //array(
//            __('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'TransactionId'),
//            __('Outcoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'Amount'),
//            __('Outcoming currency:', 'leyka') => $this->_get_value_if_any($vars, 'Currency'),
//            __('Incoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentAmount'),
//            __('Incoming currency:', 'leyka') => $this->_get_value_if_any($vars, 'PaymentCurrency'),
//            __('Donor name:', 'leyka') => $this->_get_value_if_any($vars, 'Name'),
//            __('Donor email:', 'leyka') => $this->_get_value_if_any($vars, 'Email'),
//            __('Callback time:', 'leyka') => $this->_get_value_if_any($vars, 'DateTime'),
//            __('Donor IP:', 'leyka') => $this->_get_value_if_any($vars, 'IpAddress'),
//            __('Donation description:', 'leyka') => $this->_get_value_if_any($vars, 'Description'),
//            __('Is test donation:', 'leyka') => $this->_get_value_if_any($vars, 'TestMode'),
//            __('Invoice status:', 'leyka') => $this->_get_value_if_any($vars, 'Status'),
//        );

        return $vars_final;
    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed


        } else { // New donation page displayed ?>

        <?php
        }
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'token':
            case 'transaction_id':
            case 'invoice_id':
            case 'paypal_token':
            case 'paypal_transaction_id':
            case 'paypal_invoice_id':
                return get_post_meta($donation->id, '_paypal_token', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'token':
            case 'transaction_id':
            case 'invoice_id':
            case 'paypal_token':
            case 'paypal_transaction_id':
            case 'paypal_invoice_id':
                return update_post_meta($donation->id, '_paypal_token', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

//        if(
//            isset($_POST['cp-recurring-id']) &&
//            $donation->recurring_id != $_POST['cp-recurring-id']
//        ) {
//            $donation->recurring_id = $_POST['cp-recurring-id'];
//        }
    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

//        if( !empty($donation_params['recurring_id']) ) {
//            update_post_meta($donation_id, '_cp_recurring_id', $donation_params['recurring_id']);
//        }
//        if( !empty($donation_params['transaction_id']) ) {
//            update_post_meta($donation_id, '_cp_transaction_id', $donation_params['transaction_id']);
//        }
    }
} // Gateway class end


class Leyka_Paypal_All extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'all';
        $this->_gateway_id = 'paypal';

        $this->_label_backend = __('Payment with PayPal', 'leyka');
        $this->_label = __('PayPal', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/cp/icons/visa.png',
            LEYKA_PLUGIN_BASE_URL.'gateways/cp/icons/master.png',
        ));

        $this->_custom_fields = array(
//            'recurring' => '<label class="checkbox"><span><input type="checkbox" id="leyka_'.$this->full_id.'_recurring" name="leyka_recurring" value="1"></span> '.__('Recurring donations', 'leyka').'</label>'
        );

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
                'default' => __('PayPal is an international payment system.', 'leyka'),
                'title' => __('PayPal payment description', 'leyka'),
                'description' => __('Please, enter PayPal gateway description that will be shown to the donor as he will select this payment method.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
}

function leyka_add_gateway_paypal() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Paypal_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_paypal');