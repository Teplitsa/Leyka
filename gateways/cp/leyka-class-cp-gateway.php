<?php if( !defined('WPINC') ) die;
/**
 * Leyka_CP_Gateway class
 */

class Leyka_CP_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'cp';
        $this->_title = __('CloudPayments', 'leyka');
        $this->_admin_ui_column = 1;
        $this->_admin_ui_order = 30;
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
                'title' => __('Payments testing mode', 'leyka'),
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

        $donation = new Leyka_Donation($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
        }
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
            'public_id' => trim(leyka_options()->opt('cp_public_id')),
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

                // InvoiceId - leyka donation ID, SubscriptionId - CP recurring subscription ID
                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
                    die(json_encode(array('code' => '10')));
                }

                if(empty($_POST['Amount']) || (float)$_POST['Amount'] <= 0 || empty($_POST['Currency'])) {
                    die(json_encode(array(
                        'code' => '11',
                        'reason' => sprintf(
                            'Amount or Currency in POST are empty. Amount: %s, Currency: %s',
                            $_POST['Amount'], $_POST['Currency']
                        )
                    )));
                }

                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation

                    if( !$this->get_init_recurrent_donation($_POST['SubscriptionId']) ) {
                        die(json_encode(array(
                            'code' => '11',
                            'reason' => sprintf(
                                'Init recurring payment is not found. POST SubscriptionId: %s',
                                $_POST['SubscriptionId']
                            )
                        )));
                    }

                } else { // Single or init recurring donation

                    $donation = new Leyka_Donation((int)$_POST['InvoiceId']);
                    $donation->add_gateway_response($_POST);

                    switch($_POST['Currency']) {
                        case 'RUB': $_POST['Currency'] = 'rur'; break;
                        case 'USD': $_POST['Currency'] = 'usd'; break;
                        case 'EUR': $_POST['Currency'] = 'eur'; break;
                        default:
                    }

                    if($donation->sum != $_POST['Amount'] || $donation->currency != $_POST['Currency']) {
                        die(json_encode(array(
                            'code' => '11',
                            'reason' => sprintf(
                                'Amount of original data and POST are mismatching. Original: %.2f %s, POST: %.2f %s',
                                $donation->sum, $donation->currency, $_POST['Amount'], $_POST['Currency']
                            )
                        )));
                    }
                }

                die(json_encode(array('code' => '0'))); // Payment check passed

            case 'complete':
            case 'fail':

                // InvoiceId - leyka donation ID, SubscriptionId - CP recurring subscription ID
                if(empty($_POST['InvoiceId']) && empty($_POST['SubscriptionId'])) {
                    die(json_encode(array('code' => '10')));
                }

                if(empty($_POST['InvoiceId'])) { // Non-init recurring donation

                    $donation = $this->get_donation_by_transaction_id($_POST['TransactionId']);

                    $init_recurrent_payment = $this->get_init_recurrent_donation($_POST['SubscriptionId']);

                    $donation->init_recurring_donation_id = $init_recurrent_payment->id;
                    $donation->payment_title = $init_recurrent_payment->title;
                    $donation->campaign_id = $init_recurrent_payment->campaign_id;
                    $donation->payment_method_id = $init_recurrent_payment->pm_id;
                    $donation->gateway_id = $init_recurrent_payment->gateway_id;
                    $donation->donor_name = $init_recurrent_payment->donor_name;
                    $donation->donor_email = $init_recurrent_payment->donor_email;
                    $donation->amount = $init_recurrent_payment->amount;
                    $donation->currency = $init_recurrent_payment->currency;

                } else { // Single or init recurring donation
                    $donation = new Leyka_Donation((int)$_POST['InvoiceId']);
                }

                if( !empty($_POST['SubscriptionId']) ) {

                    $donation->payment_type = 'rebill';
                    $donation->recurring_id = $_POST['SubscriptionId'];
                }

                $donation->add_gateway_response($_POST);

                if($call_type == 'complete') {

                    Leyka_Donation_Management::send_all_emails($donation->id);
                    $donation->status = 'funded';

                } else {
                    $donation->status = 'failed';
                }

                die(json_encode(array('code' => '0'))); // Payment completed / fail registered

            default:
        }
    }

    /**
     * It is possible for CP to call a callback several times for one donation.
     * This donation must be created only once and then updated. It can be identified with CP transaction id.
     *
     * @param $cp_transaction_id integer
     * @return Leyka_Donation
     */
    public function get_donation_by_transaction_id($cp_transaction_id) {

        $donation = get_posts(array( // Get init recurrent payment with customer_id given
            'posts_per_page' => 1,
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'meta_query' => array(
                'RELATION' => 'AND',
                array(
                    'key'     => '_cp_transaction_id',
                    'value'   => $cp_transaction_id,
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
                'transaction_id' => $cp_transaction_id,
            )));
        }

        return $donation;
    }

    public function get_init_recurrent_donation($recurring) {

        if(is_a($recurring, 'Leyka_Donation')) {
            $recurring = $recurring->recurring_id;
        } elseif(empty($recurring)) {
            return false;
        }

        $init_donation_post = get_posts(array( // Get init recurrent payment with customer_id given
            'posts_per_page' => 1,
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'post_parent' => 0,
            'meta_query' => array(
                'RELATION' => 'AND',
                array(
                    'key'     => '_cp_recurring_id',
                    'value'   => $recurring,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'leyka_payment_type',
                    'value'   => 'rebill',
                    'compare' => '=',
                ),
            ),
            'orderby' => 'date',
            'order' => 'ASC',
        ));

        return count($init_donation_post) ? new Leyka_Donation($init_donation_post[0]->ID) : false;
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
            __('Transaction ID:', 'leyka') => $this->_get_value_if_any($vars, 'TransactionId'),
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
            $vars_final[__('Donation failure reason:', 'leyka')] = $vars['reason'];
        }
        if( !empty($vars['SubscriptionId']) ) {
            $vars_final[__('Recurrent subscription ID:', 'leyka')] = $this->_get_value_if_any($vars, 'SubscriptionId');
        }
        if( !empty($vars['StatusCode']) ) {
            $vars_final[__('Invoice status code:', 'leyka')] = $this->_get_value_if_any($vars, 'StatusCode');
        }

        return $vars_final;
    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = get_validated_donation($donation);?>

            <label><?php _e('CloudPayments subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                    <input type="text" id="cp-recurring-id" name="cp-recurring-id" placeholder="<?php _e('Enter CloudPayments subscription ID', 'leyka');?>" value="<?php echo $donation->recurring_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->recurring_id;?></span>
                <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="cp-recurring-id"><?php _e('CloudPayments subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="cp-recurring-id" name="cp-recurring-id" placeholder="<?php _e('Enter CloudPayments subscription ID', 'leyka');?>" value="" />
            </div>
        <?php
        }
    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'cp_recurring_id':
            case 'cp_recurrent_id': return get_post_meta($donation->id, '_cp_recurring_id', true);
            case 'transaction_id':
            case 'invoice_id':
            case 'cp_transaction_id':
            case 'cp_invoice_id': return get_post_meta($donation->id, '_cp_transaction_id', true);
            default: return $value;
        }
    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'recurring_id':
            case 'recurrent_id':
            case 'cp_recurring_id':
            case 'cp_recurrent_id':
                return update_post_meta($donation->id, '_cp_recurring_id', $value);
            case 'transaction_id':
            case 'invoice_id':
            case 'cp_transaction_id':
            case 'cp_invoice_id':
                return update_post_meta($donation->id, '_cp_transaction_id', $value);
            default: return false;
        }
    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(
            isset($_POST['cp-recurring-id']) &&
            $donation->recurring_id != $_POST['cp-recurring-id']
        ) {
            $donation->recurring_id = $_POST['cp-recurring-id'];
        }
    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['recurring_id']) ) {
            update_post_meta($donation_id, '_cp_recurring_id', $donation_params['recurring_id']);
        }
        if( !empty($donation_params['transaction_id']) ) {
            update_post_meta($donation_id, '_cp_transaction_id', $donation_params['transaction_id']);
        }
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

        $this->_custom_fields = array(
            'recurring' => '<label class="checkbox"><span><input type="checkbox" id="leyka_'.$this->full_id.'_recurring" name="leyka_recurring" value="1"></span> '.__('Recurring donations', 'leyka').'</label>'
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