<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Sber_Gateway class
 */

class Leyka_Sber_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_redirect_url = '';

    protected function _set_attributes() {

        $this->_id = 'sber';
        $this->_title = __('Sberbank Acquiring', 'leyka');

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-sber-acquiring/';
        $this->_registration_link = '//www.sberbank.ru/ru/s_m_business/bankingservice/acquiring_total#application';
        $this->_has_wizard = false;

        $this->_min_commission = 1;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            $this->_id.'_api_login' => [
                'type' => 'text',
                'title' => __('API Login', 'leyka'),
                'comment' => __('Please, enter your Sberbank API login here. You should have received it from your Sberbank connection manager.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'somelogin_1-api'),
            ],
            $this->_id.'_api_password' => [
                'type' => 'text',
                'title' => __('API Password', 'leyka'),
                'comment' => __('Please, enter your Sberbank API password here. You should have received it from your Sberbank connection manager.', 'leyka'),
                'is_password' => true,
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '16918737fgc9fbdgc7c312dkmp7u27iu'),
            ],
            $this->_id.'_test_mode' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Payments testing mode', 'leyka'),
                'comment' => __('Check if the gateway integration is in test mode.', 'leyka'),
                'short_format' => true,
            ],
//            $this->_id.'_verify_checksum' => [
//                'type' => 'checkbox',
//                'default' => true,
//                'title' => __('Verify the callbacks with checksum', 'leyka'),
//                'comment' => __('Check if the gateway callbacks should be verified with checksums.', 'leyka'),
//                'short_format' => true,
//            ],
//            $this->_id.'_checksum_symmetic_token' => [
//                'type' => 'text',
//                'title' => __('A secret token for symmetric cryptography', 'leyka'),
//                'comment' => __('Please, enter your secret cryptographic token value. You should have received it from your Sberbank tech. support.', 'leyka'),
//                'is_password' => true,
//                'required' => false,
//                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'fkpmerpsh9hhlomngkq21cpstk'),
//            ],
        ];

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

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

        $this->_require_lib();

        $connection = ['currency' => Voronkovich\SberbankAcquiring\Currency::RUB,];

        $connection['userName'] = leyka_options()->opt($this->_id.'_api_login');
        $connection['password'] = leyka_options()->opt($this->_id.'_api_password');

        if(leyka_options()->opt($this->_id.'_test_mode')) {
            $connection['apiUri'] = Voronkovich\SberbankAcquiring\Client::API_URI_TEST;
        }

        try {

            $client = new Voronkovich\SberbankAcquiring\Client($connection);

            $result = $client->registerOrder($donation->id, 100*$donation->amount, leyka_get_success_page_url(), [
                'failUrl' => leyka_get_failure_page_url(),
                'clientId' => $donation->type === 'rebill' ? $donation->donor_email : '',
                'description' =>
                    (empty($form_data['leyka_recurring']) ? '' : _x('[RS]', 'For "recurring subscription"', 'leyka').' ')
                    .$donation->payment_title." (№ $donation_id)",
            ]);

            $donation->sber_order_id = empty($result['orderId']) ? '' : esc_sql($result['orderId']);
            $donation->add_gateway_response($result);

            $this->_redirect_url = $result['formUrl'];

        } catch(Exception $ex) {

            $donation->add_gateway_response($ex);

            leyka()->add_payment_form_error(new WP_Error(
                'leyka_donation_error',
                sprintf(
                    __('Error while processing the payment: %s. Your money will remain intact. Please report to the <a href="mailto:%s" target="_blank">website tech support</a>.', 'leyka'),
                    $ex->getMessage(),
                    leyka_get_website_tech_support_email()
                )
            ));

        }

    }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->sber_binding_id) {
            return false;
        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'amount_total' => 'auto',
                'init_recurring_donation' => $init_recurring_donation->id,
                'sber_binding_id' => $init_recurring_donation->sber_binding_id,
            ],
            ['recalculate_total_amount' => true,]
        );

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $this->_require_lib();

        try {

            $client = new Voronkovich\SberbankAcquiring\Client([
                'currency' => Voronkovich\SberbankAcquiring\Currency::RUB,
                'userName' => leyka_options()->opt($this->_id.'_api_login'),
                'password' => leyka_options()->opt($this->_id.'_api_password'),
                'apiUri' => leyka_options()->opt($this->_id.'_test_mode') ?
                    Voronkovich\SberbankAcquiring\Client::API_URI_TEST : Voronkovich\SberbankAcquiring\Client::API_URI,
            ]);

            $result = $client->registerOrder($new_recurring_donation->id, 100*$new_recurring_donation->amount, leyka_get_success_page_url(), [
                'failUrl' => leyka_get_failure_page_url(),
                'clientId' => $init_recurring_donation->donor_email,
                'bindingId' => $init_recurring_donation->sber_binding_id,
                'features' => 'AUTO_PAYMENT',
                'description' =>
                    _x('[RP]', 'For "recurring auto-payment"', 'leyka')
                    .$new_recurring_donation->payment_title." (№ {$new_recurring_donation->id})",
            ]);

            $new_recurring_donation->sber_order_id = empty($result['orderId']) ? '' : esc_sql($result['orderId']);
            $new_recurring_donation->sber_binding_id = $init_recurring_donation->sber_binding_id;
            $new_recurring_donation->sber_client_id = $init_recurring_donation->sber_client_id;

            $new_recurring_donation->add_gateway_response($result);

            if($new_recurring_donation->sber_order_id && $new_recurring_donation->sber_binding_id) {
                $client->paymentOrderBinding($new_recurring_donation->sber_order_id, $new_recurring_donation->sber_binding_id);
            }

        } catch(Exception $ex) {

            $new_recurring_donation->status = 'failed';
            $new_recurring_donation->add_gateway_response($ex);

            // Emails will be sent only if respective options are on:
            Leyka_Donation_Management::send_error_notifications($new_recurring_donation);

        }

        return $new_recurring_donation;

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $this->_redirect_url ? $this->_redirect_url : ''; // The Gateway receives redirection URL on payment
    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		if( !array_key_exists($pm_id, $this->_payment_methods) ) {
			return $form_data; // It's not our PM
        }

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */
            return ['status' => 1, 'message' => $donation_id->get_error_message()];
        } else if( !$donation_id ) {
            return ['status' => 1, 'message' => __('The donation was not created due to error.', 'leyka')];
        } else if( !$this->is_setup_complete() ) {
            return [
                'status' => 1,
                'message' => __('Error in the gateway settings. Please report to the website tech support.', 'leyka'),
            ];
        }

        return apply_filters('leyka_sber_custom_submission_data', [], $pm_id);

    }

    public function _handle_service_calls($call_type = '') {

        switch($call_type) {

            case 'process':
            case 'response':
            case 'notify':

                $donation = NULL;
                if( !empty($_REQUEST['orderNumber']) ) {
                    $donation = Leyka_Donations::get_instance()->get(absint($_REQUEST['orderNumber']));
                } else if( !empty($_REQUEST['mdOrder']) ) {
                    $donation = $this->get_donation_by_transaction_id(trim($_REQUEST['mdOrder']));
                }

                if( !$donation ) {
                    exit(500);
                }

                $donation->add_gateway_response($_REQUEST);

                if(empty($_REQUEST['status']) || empty($_REQUEST['operation'])) { // Operation failed

                    $donation->status = 'failed';
                    $donation->add_gateway_response($_REQUEST);

                    // Emails will be sent only if respective options are on:
                    Leyka_Donation_Management::send_error_notifications($donation);

                    exit(500);

                }

                switch($_REQUEST['operation']) {
                    case 'deposited':
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
                    case 'refunded':
                        $donation->status = 'refunded';
                        break;
                    default:
                }

                $donation->add_gateway_response($_REQUEST);

                if($donation->type === 'rebill') {

                    $this->_require_lib();

                    $client = new Voronkovich\SberbankAcquiring\Client([
                        'currency' => Voronkovich\SberbankAcquiring\Currency::RUB,
                        'userName' => leyka_options()->opt($this->_id.'_api_login'),
                        'password' => leyka_options()->opt($this->_id.'_api_password'),
                        'apiUri' => leyka_options()->opt($this->_id.'_test_mode') ?
                            Voronkovich\SberbankAcquiring\Client::API_URI_TEST : Voronkovich\SberbankAcquiring\Client::API_URI,
                    ]);

                    $result = $client->getOrderStatusExtended($donation->sber_order_id);
                    if( !empty($result['bindingInfo']) && !empty($result['bindingInfo']['bindingId']) ) {

                        $donation->sber_binding_id = esc_sql($result['bindingInfo']['bindingId']);
                        $donation->sber_client_id = empty($result['bindingInfo']['clientId']) ?
                            '' : esc_sql($result['bindingInfo']['clientId']);

                    } else { // The gateway didn't return needed values

                        $donation->recurring_is_active = false;

                        // Emails will be sent only if respective options are on:
                        Leyka_Donation_Management::send_error_notifications($donation);

                        exit(500);

                    }

                }

                exit(200);

            default:
                exit(500);
        }

    }

    /**
     * It is possible for the Gateway to call a callback several times for one donation.
     * This donation must be created only once and then updated. It can be identified with the Gateway inner order id.
     *
     * @param $sber_order_id mixed
     * @return Leyka_Donation_Base
     */
    public function get_donation_by_transaction_id($sber_order_id) {

        $donation = Leyka_Donations::get_instance()->get([ // Get init recurrent payment with Sberbank order_id given
            'get_single' => true,
            'meta' => [
                'RELATION' => 'AND',
                ['key' => '_leyka_sber_order_id', 'value' => $sber_order_id, 'compare' => '=',],
            ],
        ]);

        return $donation ? : null;

    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('Sberbank Order number:', 'leyka') => $this->_get_value_if_any($vars, 'mdOrder'),
                __('Leyka Order Number:', 'leyka') => $this->_get_value_if_any($vars, 'orderNumber'),
                __('Last operation:', 'leyka') => $this->_get_value_if_any($vars, 'operation'),
                __('Last operation status:', 'leyka') => $this->_get_value_if_any($vars, 'status'),
            ],
            $donation
        );

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('Sberbank order ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="sber-order-id" name="sber-order-id" placeholder="<?php _e('Enter a Sberbank order ID', 'leyka');?>" value="<?php echo $donation->sber_order_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->sber_order_id;?></span>
                <?php }?>
            </div>

            <?php if($donation->type === 'rebill') {

                $init_recurring_donation = $donation->init_recurring_donation;?>

                <div class="recurring-is-active-field">
                    <label for="sber-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                    <div class="leyka-ddata-field">
                        <input type="checkbox" id="sber-recurring-is-active" name="sber-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
                    </div>
                </div>

                <label><?php _e('Sberbank binding ID', 'leyka');?>:</label>
                <div class="leyka-ddata-field"><span class="fake-input"><?php echo $donation->sber_binding_id;?></span></div>

                <label><?php _e('Sberbank client ID', 'leyka');?>:</label>
                <div class="leyka-ddata-field"><span class="fake-input"><?php echo $donation->sber_client_id;?></span></div>

            <?php }?>

        <?php } else { // New donation page displayed ?>

            <label for="sber-order-id"><?php _e('Sberbank order ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="sber-order-id" name="sber-order-id" placeholder="<?php _e('Enter Sberbank order ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'order_id':
            case 'sber_order_id':
            case 'sber_acquiring_order_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_leyka_sber_order_id');
            case 'binding_id':
            case 'sber_binding_id':
            case 'sber_acquiring_binding_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_leyka_sber_binding_id');
            case 'client_id':
            case 'sber_client_id':
            case 'sber_acquiring_client_id':
                return Leyka_Donations::get_instance()->get_donation_meta($donation->id, '_leyka_sber_client_id');
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'order_id':
            case 'sber_order_id':
            case 'sber_acquiring_order_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_leyka_sber_order_id', $value);
            case 'binding_id':
            case 'sber_binding_id':
            case 'sber_acquiring_binding_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_leyka_sber_binding_id', $value);
            case 'client_id':
            case 'sber_client_id':
            case 'sber_acquiring_client_id':
                return Leyka_Donations::get_instance()->set_donation_meta($donation->id, '_leyka_sber_client_id', $value);
            default: return false;
        }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['sber-order-id']) && $donation->sber_order_id != $_POST['sber-order-id']) {
            $donation->sber_order_id = $_POST['sber-order-id'];
        }

        $donation->recurring_is_active = !empty($_POST['sber-recurring-is-active']);

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['sber_order_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta(
                $donation_id,
                '_leyka_sber_order_id',
                $params['sber_order_id']
            );
        }

    }

    protected function _require_lib() {

        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/Client.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/Currency.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/HttpClient/HttpClientInterface.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/HttpClient/CurlClient.php';
        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/OrderStatus.php';

        require_once LEYKA_PLUGIN_DIR.'gateways/sber/lib/Exception/SberbankAcquiringException.php';
        foreach(glob(LEYKA_PLUGIN_DIR.'gateways/sber/lib/Exception/*.php') as $filename) {
            require_once $filename;
        }

    }

}

class Leyka_Sber_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'sber';
        $this->_category = 'bank_cards';

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
            $this->full_id.'_recurring_available' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Monthly recurring subscriptions are available', 'leyka'),
                'comment' => __('Check if the gateway allows you to create recurrent subscriptions to do regular automatic payments.', 'leyka').' '.__('WARNING: you should enable the Sberbank auto-payments feature for test mode and for production mode separately.', 'leyka'),
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
        return !!leyka_options()->opt($this->full_id.'_recurring_available');
    }

}

function leyka_add_gateway_sber() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Sber_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_sber');