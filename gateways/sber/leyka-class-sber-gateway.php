<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Sber_Gateway class
 */

class Leyka_Sber_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_redirect_url = '';

    protected function _set_attributes() {

        $this->_id = 'sber';
        $this->_title = __('Sberbank Aquiring', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="//www.sberbank.ru/ru/s_m_business/bankingservice/acquiring_total/">Sberbank Aquiring</a> description here.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-sber-aquiring/';
        $this->_registration_link = '//www.sberbank.ru/ru/s_m_business/bankingservice/acquiring_total#application';
        $this->_has_wizard = false;

        $this->_min_commission = 1;
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = false;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->_id.'_api_login' => array(
                'type' => 'text',
                'title' => __('API Login', 'leyka'),
                'comment' => __('Please, enter your Sberbank API login here. You should have received it from your Sberbank connection manager.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'somelogin_1-api'),
            ),
            $this->_id.'_api_password' => array(
                'type' => 'text',
                'title' => __('API Password', 'leyka'),
                'comment' => __('Please, enter your Sberbank API password here. You should have received it from your Sberbank connection manager.', 'leyka'),
                'is_password' => true,
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '16918737fgc9fbdgc7c312dkmp7u27iu'),
            ),
            $this->_id.'_api_token' => array(
                'type' => 'text',
                'title' => __('API token', 'leyka'),
                'comment' => __('Please, enter your Sberbank API token here. You may have received it from your Sberbank connection manager.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'c5fcan980a7c38418932y476g4931'),
            ),
            $this->_id.'_public_key' => array(
                'type' => 'textarea',
                'title' => __('Public key for callbacks checksum', 'leyka'),
                'comment' => __("Please, enter a public key text that you received from Sberbank Aquiring technical support. If it's set, Leyka will perform hash checks for each incoming donation data integrity. More information  <a href='https://securepayments.sberbank.ru/wiki/doku.php/integration:api:callback:start' target='_blank'>here</a>.", 'leyka'),
                'required' => true,
                'placeholder' => __('-----BEGIN CERTIFICATE----- ...', 'leyka'),
            ),
            $this->_id.'_test_mode' => array(
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ),
        );

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt($this->_id.'_api_token')
            || (leyka_options()->opt($this->_id.'_api_login') && leyka_options()->opt($this->_id.'_api_password'));
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Sber_Card::get_instance();
        }
    }

    public function localize_js_strings(array $js_data) {
        return array_merge($js_data, array(
            'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
            'gateway_not_set_up' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka'),
        ));
    }

    public function enqueue_gateway_scripts() {

//        if(Leyka_CP_Card::get_instance()->active) {
//
//            wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments', array(), false, true);
//            wp_enqueue_script(
//                'leyka-cp',
//                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_CP_Gateway::get_instance()->id.'/js/leyka.cp.js',
//                array('jquery', 'leyka-cp-widget', 'leyka-public'),
//                LEYKA_VERSION.'.001',
//                true
//            );
//
//        }
//
//        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);

        require_once LEYKA_PLUGIN_DIR.'gateways/sber_aquiring/lib/Client.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber_aquiring/lib/Currency.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber_aquiring/lib/HttpClient/HttpClientInterface.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber_aquiring/lib/HttpClient/CurlClient.php';
//        require_once LEYKA_PLUGIN_DIR.'gateways/sber_aquiring/lib/OrderStatus.php';

        $connection = array('currency' => Voronkovich\SberbankAcquiring\Currency::RUB,);

        if(leyka_options()->opt($this->_id.'_api_token')) {
            $connection['token'] = $this->_id.'_api_token';
        } else {

            $connection['userName'] = leyka_options()->opt($this->_id.'_api_login');
            $connection['password'] = leyka_options()->opt($this->_id.'_api_password');

        }

        if(leyka_options()->opt($this->_id.'_test_mode')) {
            $connection['apiUri'] = Voronkovich\SberbankAcquiring\Client::API_URI_TEST;
        }

        try {

            $client = new Voronkovich\SberbankAcquiring\Client($connection);

            $result = $client->registerOrder($donation->id, 100*$donation->amount, leyka_get_success_page_url(), array(
                'failUrl' => leyka_get_failure_page_url(),
//                'httpMethod' => 'GET',
            ));

            $donation->sber_order_id = empty($result['orderId']) ? '' : esc_sql($result['orderId']);
            $donation->add_gateway_response($result);

            $this->_redirect_url = $result['formUrl'];

        } catch(Exception $ex) {

            $donation->add_gateway_response($ex);

            leyka()->add_payment_form_error( new WP_Error('leyka_donation_error', sprintf(__('Error while processing the payment: %s. Your money will remain intact. Please report to the <a href="mailto:%s" target="_blank">website tech support</a>.', 'leyka'), $ex->getMessage(), leyka_get_website_tech_support_email())) );
            return;

        }

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $this->_redirect_url ? $this->_redirect_url : ''; // Sberbank Aquiring receives redirection URL on payment
    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return array('status' => 1, 'message' => $donation_id->get_error_message());
        } else if( !$donation_id ) {
            return array('status' => 1, 'message' => __('The donation was not created due to error.', 'leyka'));
        } else if( !$this->is_setup_complete() ) {
            return array(
                'status' => 1,
                'message' => __('Error in the gateway settings. Please report to the website tech support.', 'leyka'),
            );
        }

        return apply_filters('leyka_sber_aquiring_custom_submission_data', array(), $pm_id);

    }

    /** @todo */
    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            case 'process':
            case 'response':
            case 'notify':


            default:
                exit(500);
        }

    }

    /**
     * It is possible for the Gateway to call a callback several times for one donation.
     * This donation must be created only once and then updated. It can be identified with the Gateway inner order id.
     *
     * @param $sber_order_id mixed
     * @return Leyka_Donation
     */
    public function get_donation_by_transaction_id($sber_order_id) {

        $donation = get_posts(array( // Get init recurrent payment with customer_id given
            'posts_per_page' => 1,
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'meta_query' => array(
                'RELATION' => 'AND',
                array(
                    'key'     => '_leyka_sber_order_id',
                    'value'   => $sber_order_id,
                    'compare' => '=',
                ),
            ),
        ));

//        if(count($donation)) {
//            $donation = new Leyka_Donation($donation[0]->ID);
//        } else {
//            $donation = new Leyka_Donation(Leyka_Donation::add(array(
//                'status' => 'submitted',
//                'cp_transaction_id' => $sber_order_id,
//                'force_insert' => true, // Turn off donation fields validation checks
//            )));
//        }

        return count($donation) ? new Leyka_Donation($donation[0]->ID) : null;

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

        /** @todo */
        $vars_final = array(
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
        );

//        if( !empty($vars['reason']) ) {
//            $vars_final[__('Donation failure reason:', 'leyka')] = $vars['reason'];
//        }
//        if( !empty($vars['SubscriptionId']) ) {
//            $vars_final[__('Recurrent subscription ID:', 'leyka')] = $this->_get_value_if_any($vars, 'SubscriptionId');
//        }
//        if( !empty($vars['StatusCode']) ) {
//            $vars_final[__('Invoice status code:', 'leyka')] = $this->_get_value_if_any($vars, 'StatusCode');
//        }

        return $vars_final;

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = leyka_get_validated_donation($donation); ?>

            <label><?php _e('Sberbank order ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="sber-order-id" name="sber-order-id" placeholder="<?php _e('Enter a Sberbank order ID', 'leyka');?>" value="<?php echo $donation->sber_order_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->sber_order_id;?></span>
                <?php }?>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="sber-order-id"><?php _e('Sberbank order ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="sber-order-id" name="sber-order-id" placeholder="<?php _e('Enter Sberbank order ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation $donation) {

        switch($field_name) {
            case 'order_id':
            case 'sber_order_id':
            case 'sber_aquiring_order_id':
                return get_post_meta($donation->id, '_leyka_sber_order_id', true);
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation $donation) {

        switch($field_name) {
            case 'order_id':
            case 'sber_order_id':
            case 'sber_aquiring_order_id':
                return update_post_meta($donation->id, '_leyka_sber_order_id', $value);
            default: return false;
        }

    }

    public function save_donation_specific_data(Leyka_Donation $donation) {

        if(isset($_POST['sber-order-id']) && $donation->sber_order_id != $_POST['sber-order-id']) {
            $donation->sber_order_id = $_POST['sber-order-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $donation_params) {

        if( !empty($donation_params['sber_order_id']) ) {
            update_post_meta($donation_id, '_leyka_sber_order_id', $donation_params['sber_order_id']);
        }

    }

}

class Leyka_Sber_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'sber';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('<a href="//www.sberbank.ru/ru/s_m_business/bankingservice/acquiring_total/">Sberbank Aquiring</a> cards payment description here.', 'leyka'),
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

//        $this->_processing_type = 'custom-process-submit-event';

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_recurring_available' => array(
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'comment' => __('Check if Sberbank Aquiring allows you to create recurrent subscriptions to do regular automatic payments. WARNING: you should enable the Sberbank auto-payments feature for test mode and for production mode separately.', 'leyka'),
                'short_format' => true,
            ),
        );

    }

    public function has_recurring_support() {
        return !!leyka_options()->opt($this->full_id.'_recurring_available');
    }

}

function leyka_add_gateway_sber() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Sber_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_sber');