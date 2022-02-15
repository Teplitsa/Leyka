<?php if( !defined('WPINC') ) die;

class Leyka_Tinkoff_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_redirect_url = '';

    protected function _set_attributes() {

        $this->_id = 'tinkoff';
        $this->_title = __('Tinkoff', 'leyka');
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
            $this->_id.'_terminal_key' => [
                'type' => 'text',
                'title' => __('Terminal ID', 'leyka'),
                'comment' => __('The value may be acquired from the bank when Tinkoff payment terminal is created.', 'leyka'),
                'required' => true,
            ],
            $this->_id.'_password' => [
                'type' => 'text',
                'is_password' => true,
                'title' => __('Password', 'leyka'),
                'comment' => __('The value may be acquired from the bank when Tinkoff payment terminal is created.', 'leyka'),
                'required' => true,
            ],
        ];

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt($this->_id.'_terminal_key') && leyka_options()->opt($this->_id.'_password');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Tinkoff_Card::get_instance();
        }
    }

    protected function _handle_donation_failure(Leyka_Donation_Base $donation, $gateway_response = false) {

        $donation->status = 'failed';

        if($gateway_response) {
            $donation->add_gateway_response($gateway_response);
        }

        Leyka_Donation_Management::send_error_notifications($donation); // Emails will be sent only if respective options are on

    }

    public function do_recurring_donation(Leyka_Donation_Base $init_recurring_donation) {

        if( !$init_recurring_donation->tinkoff_rebill_id) {
            return false;
        }

        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
            $init_recurring_donation,
            [
                'status' => 'submitted',
                'payment_type' => 'rebill',
                'amount_total' => 'auto',
                'init_recurring_donation' => $init_recurring_donation->id,
            ],
            ['recalculate_total_amount' => true,]
        );

        $new_recurring_donation->tinkoff_rebill_id = esc_sql($init_recurring_donation->tinkoff_rebill_id);

        if(is_wp_error($new_recurring_donation)) {
            return false;
        }

        $this->_require_lib();

        $api = new TinkoffMerchant(
            leyka_options()->opt($this->_id.'_terminal_key'),
            leyka_options()->opt($this->_id.'_password')
        );

        $api->init([
            'OrderId' => $new_recurring_donation->id,
            'Amount' => 100 * absint($new_recurring_donation->amount),
            'DATA' => ['Email' => $init_recurring_donation->donor_email,],
        ]);

        if($api->error){
            $this->_handle_donation_failure($new_recurring_donation, $api);
        } else {

            $api->charge(['RebillId' => $init_recurring_donation->tinkoff_rebill_id, 'PaymentId' => $api->paymentId,]);

            if($api->error || $api->status === 'REJECTED') {
                $this->_handle_donation_failure($new_recurring_donation, $api);
            } else {

                $new_recurring_donation->status = 'funded';
                Leyka_Donation_Management::send_all_emails($new_recurring_donation->id);

            }

        }

        return $new_recurring_donation;

    }

    // The default implementations are in use:
//    public function get_recurring_subscription_cancelling_link($link_text, Leyka_Donation_Base $donation) { }
//    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) { }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        if( !empty($form_data['leyka_recurring']) ) {

            $donation->payment_type = 'rebill';
            $donation->recurring_is_active = true; // So we could turn it on/off later

        }

        $this->_require_lib();

        $api = new TinkoffMerchant(
            leyka_options()->opt($this->_id.'_terminal_key'),
            leyka_options()->opt($this->_id.'_password')
        );

        $params = [
            'OrderId' => $donation_id,
            'Amount' => 100 * absint($donation->amount),
            'DATA' => ['Email' => $donation->donor_email,],
        ];

        if($donation->type === 'rebill') {

            $params['Recurrent'] = 'Y';
            $params['CustomerKey'] = $donation->donor_email;

        }

        $api->init($params);

        if($api->error){

            leyka()->add_payment_form_error( new WP_Error('leyka_donation_error', sprintf(__('Error while processing the payment: %s. Your money will remain intact. Please report to the <a href="mailto:%s" target="_blank">website tech support</a>.', 'leyka'), $api->error, leyka_get_website_tech_support_email())) );
            return;

        } else {
            $this->_redirect_url = $api->paymentUrl;
        }

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = $donation->gateway_response;
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        return [
            __('Terminal key:', 'leyka') => $vars['TerminalKey'],
            __('Payment succeeded:', 'leyka') => !empty($vars['Success']) ? __('yes', 'leyka') : __('no', 'leyka'),
            __('Last payment status:', 'leyka') => $vars['Status'],
            __('Order ID:', 'leyka') => $vars['OrderId'],
            __('Payment ID:', 'leyka') => $vars['PaymentId'],
            __('Bank card ID:', 'leyka') => $vars['CardId'],
            __('Bank card number:', 'leyka') => $vars['Pan'],
            __('Bank card expiring date:', 'leyka') => $vars['ExpDate'],
            __('Payment error code:', 'leyka') => empty($vars['ErrorCode']) ? __('no', 'leyka') : $vars['ErrorCode'],
        ];

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

        return [];

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'tinkoff_payment_id':
                return $donation->get_meta('_leyka_tinkoff_payment_id');
            case 'tinkoff_rebill_id':
            case 'tinkoff_recurring_id':
                return $donation->get_meta('_leyka_tinkoff_rebill_id');
            default: return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'tinkoff_payment_id':
                return $donation->set_meta('_leyka_tinkoff_payment_id', $value);
            case 'tinkoff_rebill_id':
            case 'tinkoff_recurring_id':
                return $donation->set_meta('_leyka_tinkoff_rebill_id', $value);
            default: return false;
        }

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('Tinkoff payment ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                    <input type="text" id="tinkoff-payment-id" name="tinkoff-payment-id" value="<?php echo $donation->tinkoff_payment_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->tinkoff_payment_id;?></span>
                <?php }?>
            </div>

            <?php if($donation->type !== 'rebill') {
                return;
            }?>

            <label><?php _e('Tinkoff recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">

                <?php if($donation->type == 'correction') {?>
                    <input type="text" id="tinkoff-recurring-id" name="tinkoff-recurring-id" value="<?php echo $donation->tinkoff_recurring_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->tinkoff_recurring_id;?></span>
                <?php }?>
            </div>

            <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">
                <label for="tinkoff-recurring-is-active"><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <input type="checkbox" id="tinkoff-recurring-is-active" name="tinkoff-recurring-is-active" value="1" <?php echo $init_recurring_donation->recurring_is_active ? 'checked="checked"' : '';?>>
                </div>
            </div>

        <?php } else { // New donation page displayed ?>

            <label for="tinkoff-recurring-id"><?php _e('Tinkoff recurring subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="tinkoff-recurring-id" name="tinkoff-recurring-id" placeholder="<?php _e('Enter Tinkoff recurring ID', 'leyka');?>" value="">
            </div>

            <?php }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['tinkoff-payment-id']) && $donation->tinkoff_payment_id != $_POST['tinkoff-payment-id']) {
            $donation->tinkoff_payment_id = $_POST['tinkoff-payment-id'];
        }

        if(isset($_POST['tinkoff-recurring-id']) && $donation->tinkoff_recurring_id != $_POST['tinkoff-recurring-id']) {
            $donation->tinkoff_recurring_id = $_POST['tinkoff-recurring-id'];
        }

        $donation->recurring_is_active = !empty($_POST['tinkoff-recurring-is-active']);

    }

    public function _handle_service_calls($call_type = '') {

        $response = file_get_contents('php://input');
        if($response) {

            $response = json_decode($response, true);

            if($this->_check_result_response($response)) {

                $donation = Leyka_Donations::get_instance()->get_donation($response['OrderId']);

                switch($response['Status']) {
                    case 'CONFIRMED':
                        $donation->status = 'funded';
                        Leyka_Donation_Management::send_all_emails($donation->id);
                        break;

                    case 'REJECTED':
                    case 'AUTH_FAIL':
                    case 'REVERSED':
                        $donation->status = 'failed';
                        break;

                    case 'REFUNDED':
                        $donation->status = 'refunded';
                        break;

                    default:
                }

                if( !empty($response['PaymentId']) ) {
                    $donation->tinkoff_payment_id = esc_sql($response['PaymentId']);
                }

                if( !empty($response['RebillId']) ) {
                    $donation->tinkoff_rebill_id = esc_sql($response['RebillId']);
                }

                $donation->add_gateway_response($response);

            }

            if( // GUA direct integration - "purchase" event:
                $donation->status === 'funded'
                && leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
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

        }

        echo 'OK';

    }

    protected function _check_result_response($params = []) {

        $prev_token = $params['Token'];

        $params['Success'] = (int)$params['Success'] > 0 ? (string)'true' : (string)'false';

        unset($params['Token'], $params['Receipt'], $params['Data']);

        $params['TerminalKey'] = leyka_options()->opt($this->_id.'_terminal_key');
        $params['Password'] = leyka_options()->opt($this->_id.'_password');

        ksort($params);

        return strcmp(mb_strtolower($prev_token), mb_strtolower(hash('sha256', implode('', $params)))) == 0;

    }

    protected function _require_lib() {
        require_once LEYKA_PLUGIN_DIR.'gateways/tinkoff/lib/TinkoffMerchantAPI.php';
    }

}

class Leyka_Tinkoff_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'tinkoff';
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
                'comment' => __('Check if TinkoffBank Acquiring allows you to create recurrent subscriptions to do regular automatic payments. WARNING: you should enable the Tinkoffbank auto-payments feature for test mode and for production mode separately.', 'leyka'),
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

function leyka_add_gateway_tinkoff() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Tinkoff_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_tinkoff');