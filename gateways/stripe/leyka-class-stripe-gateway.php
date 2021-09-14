<?php if( !defined('WPINC') ) die;
/**
 * Leyka_CP_Gateway class
 */

class Leyka_Stripe_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_api_redirect_url;

    protected function _set_attributes() {

        $this->_id = 'stripe';
        $this->_title = __('Stripe', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="//stripe.com/">Stripe</a> is a technology company that builds economic infrastructure for the internet. Businesses of every size—from new startups to public companies—use our software to accept payments and manage their businesses online.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = 'https://stripe.com/docs';
        $this->_registration_link = '//dashboard.stripe.com/register';
        $this->_has_wizard = false;

        $this->_min_commission = '2.2%';
        $this->_receiver_types = array('legal');
        $this->_may_support_recurring = true;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'stripe_key_public' => array(
                'type' => 'text',
                'title' => __('Public key', 'leyka'),
                'comment' => __('Please, enter your Stripe public key here. It can be found in your Stripe control panel ("API keys" section).', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'pk_test_51IybR4JyYVP3cRIfBBSIGvoolI...'),
            ),
            'stripe_key_secret' => array(
                'type' => 'text',
                'title' => __('Secret key', 'leyka'),
                'comment' => __('Please, enter your Stripe secret key here. It can be found in your Stripe control panel ("API keys" section).', 'leyka'),
                'is_password' => true,
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'sk_test_51IybR4JyYVP3cRIf5zbSzovieA...'),
            ),
            'stripe_product_id' => array(
                'type' => 'text',
                'title' => __('Product ID', 'leyka'),
                'comment' => __('Please, enter your Stripe "Donation" product ID here. It can be found in your Stripe personal account ("Products" section).', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'prod_K8PufqAVP7Z2SG'),
            ),
            'stripe_webhooks_key' => array(
                'type' => 'text',
                'title' => __('Webhooks secret key', 'leyka'),
                'comment' => __('Please, enter your Stripe webhooks signing secret key here. It can be found in your Stripe control panel ("Webhooks" section).', 'leyka'),
                'is_password' => true,
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'whsec_f0ZTQyaYaSpWMK3npRxAfLP2MAgkWifl'),
            ),
            'stripe_webhooks_ips' => array(
                'type' => 'text',
                'title' => __('Webhooks IPs', 'leyka'),
                'comment' => __('Comma-separated callback requests IP list. Leave empty to disable the check.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '3.18.12.63, 3.130.192.231, 13.235.14.237'),
                'default' => '3.18.12.63, 3.130.192.231, 13.235.14.237, 13.235.122.149, 18.211.135.69, 35.154.171.200, 
                            52.15.183.38, 54.88.130.119, 54.88.130.237, 54.187.174.169, 54.187.205.235, 54.187.216.72',
            ),
        );

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('stripe_key_public')
            && leyka_options()->opt('stripe_key_secret')
            && leyka_options()->opt('stripe_product_id')
            && leyka_options()->opt('stripe_webhooks_key');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Stripe_Card::get_instance();
        }
    }

    public function localize_js_strings(array $js_data) {
        return $js_data;
    }

    public function enqueue_gateway_scripts() {
        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        require_once LEYKA_PLUGIN_DIR.'gateways/stripe/lib/init.php';

        $compaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        if( !empty($form_data['leyka_recurring']) ) {
            $donation->payment_type = 'rebill';
        }
        $description = (
            !empty($form_data['leyka_recurring']) ? _x('[RS]', 'For "recurring subscription"', 'leyka').' ' : ''
            )
            .$donation->payment_title." (№ $donation_id); {$donation->donor_name}; {$donation->donor_email}";

        \Stripe\Stripe::setApiKey(leyka_options()->opt('stripe_key_secret'));

        $checkout_session_data = [
            'line_items' => [[
                'price_data' => [
                    'unit_amount' => $form_data['leyka_donation_amount']*100,
                    'currency' => $form_data['leyka_donation_currency'] === 'rur' ? 'rub' : $form_data['leyka_donation_currency'],
                    'product' => leyka_options()->opt('stripe_product_id')
                ],
                'quantity' => 1
            ]],
            'payment_method_types' => [
                'card',
            ],
            'mode' => empty($form_data['leyka_recurring']) ? 'payment' : 'subscription',
            'success_url' => leyka_get_success_page_url(),
            'cancel_url' => $compaign->url,
            'metadata' => [
                'donation_id' => $donation_id
            ]
        ];

        if (!empty($form_data['leyka_donor_email'])){
            $checkout_session_data['customer_email'] = $form_data['leyka_donor_email'];
        }

        if (empty($form_data['leyka_recurring'])) {
            $checkout_session_data['payment_intent_data'] = [
                'description' => $description,
                'metadata' => [
                    'donation_id' => $donation_id
                ]
            ];
        }
        else {

            $checkout_session_data['line_items'][0]['price_data']['recurring'] = [
                //'interval' => 'month'
                'interval' => 'day'
            ];
            $checkout_session_data['subscription_data'] = [
                'metadata' => [
                    'description' => $description,
                    'donation_id' => $donation_id
                ]
            ];

        }

        $checkout_session = \Stripe\Checkout\Session::create($checkout_session_data);

        $this->_api_redirect_url = $checkout_session->url;

    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return 'redirect';
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $this->_api_redirect_url;
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {
        return [];
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if(!$donation->gateway_response) {
            return [];
        }

        $vars = json_decode($donation->gateway_response, true);
        if( !$vars || !is_array($vars) ) {
            return [];
        }

        $vars_final = [
            __('PaymentIntent ID:', 'leyka') => $vars['id'],
            __('Amount:', 'leyka') => $vars['amount']/100,
            __('Currency:', 'leyka') => $vars['currency'],
            __('Amount received:', 'leyka') => $vars['amount_received']/100,
            __('Donor name:', 'leyka') => $vars['charges']['data'][0]['billing_details']['name'],
            __('Donor email:', 'leyka') => $vars['charges']['data'][0]['billing_details']['email'],
            __('Donation description:', 'leyka') => $vars['charges']['data'][0]['description'],
            __('Status code:', 'leyka') => $vars['status']
        ];

        if($vars['status'] === 'requires_payment_method') {
            $vars_final[__('Donation failure reason:', 'leyka')] = $vars['charges']['data'][0]['failure_message'];
        }

        if ( !empty($vars['object'])
            && $vars['object'] === 'checkout.session'
            && !empty($vars['mode'])
            && $vars['mode'] === 'subscription'){
            $vars_final[__('Subscription ID:', 'leyka')] = $vars['subscription'];
        }

        if(!empty($vars['refunded']) && $vars['refunded'] === true){
            $vars_final[__('Refund reason:', 'leyka')] = $vars['refunds']['data'][0]['reason'];
        }

        return $vars_final;

    }

    /* Check if callback is sent from correct IP. */
    protected function _is_callback_caller_correct() {

        if( !leyka_options()->opt('stripe_webhooks_ips') ) { // The caller IP check is off
            return true;
        }

        $stripe_ips_allowed = array_map(
            function($ip) { return trim(stripslashes($ip)); },
            explode(',', leyka_options()->opt('stripe_webhooks_ips'))
        );

        if( !$stripe_ips_allowed ) {
            return true;
        }

        $client_ip = leyka_get_client_ip();

        foreach($stripe_ips_allowed as $ip_or_cidr) {

            if( // Check if caller IP is in CIDR range
                mb_strpos($ip_or_cidr, '/')
                && (is_ip_in_range($_SERVER['REMOTE_ADDR'], $ip_or_cidr) || is_ip_in_range($client_ip, $ip_or_cidr))
            ) {
                return true;
            } else if($client_ip == $ip_or_cidr) { // Simple IP check
                return true;
            }

        }

        return false;

    }

    public function _handle_service_calls($call_type = '') {

        // Test for gateway's IP:
        if( !$this->_is_callback_caller_correct() ) {

            $client_ip = leyka_get_client_ip();

            $message = __("This message has been sent because a call to your Stripe function was made from an IP that did not match with the one in your Stripe gateway setting. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r".
                "POST:\n\r".print_r($_POST, true)."\n\r\n\r".
                "GET:\n\r".print_r($_GET, true)."\n\r\n\r".
                "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r".
                "IP:\n\r".print_r($client_ip, true)."\n\r\n\r".
                "Stripe IP setting value:\n\r".print_r(leyka_options()->opt('stripe_webhooks_ips'),true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Stripe IP check failed!', 'leyka'), $message);
            status_header(200);
            die(json_encode(array(
                'code' => '13',
                'reason' => sprintf(
                    'Unknown callback sender IP: %s (IPs permitted: %s)',
                    $client_ip, str_replace(',', ', ', leyka_options()->opt('stripe_webhooks_ips'))
                )
            )));

        }

        require_once LEYKA_PLUGIN_DIR.'gateways/stripe/lib/init.php';

        $payload = @file_get_contents('php://input');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $_SERVER['HTTP_STRIPE_SIGNATURE'], leyka_options()->opt('stripe_webhooks_key')
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            exit();
        }

        $response_data = $event->data->object;

        if ( !empty($response_data->metadata->donation_id) ){

            $donation_id = $response_data->metadata->donation_id;
            $donation = Leyka_Donations::get_instance()->get_donation((int)$donation_id);

        }

        switch($event->type) {
            case 'invoice.paid':
                $init_donation_id = $response_data->lines->data[0]->metadata->donation_id;
                $init_recurring_donation = Leyka_Donations::get_instance()->get_donation((int)$init_donation_id);

                // Rebill
                if ($response_data->billing_reason === 'subscription_cycle'){
                    //Check if donation was already created for this invoice
                    $donation_id = Leyka_Donations::get_instance()->get_donation_id_by_meta_value(
                        'stripe_invoice_id',
                        $response_data->id
                    );

                    if (!$donation_id) {

                        $new_recurring_donation = Leyka_Donations::get_instance()->add_clone(
                            $init_recurring_donation,
                            array(
                                'status' => 'funded',
                                'payment_type' => 'rebill',
                                'init_recurring_donation' => $init_recurring_donation->id,
                                'stripe_invoice_id' => $response_data->id,
                                'stripe_paymentintent_id' => $response_data->payment_intent,
                                'stripe_subscription_id' => $response_data->subscription
                            ),
                            array('recalculate_total_amount' => true)
                        );

                        if(is_wp_error($new_recurring_donation)) {
                            return false;
                        }

                        if(leyka_get_pm_commission($new_recurring_donation->pm_full_id) > 0.0) {
                            $new_recurring_donation->amount_total = leyka_calculate_donation_total_amount($new_recurring_donation);
                        }

                    }

                }
                //Init payment
                elseif ($response_data->billing_reason === 'subscription_create') {

                    $init_recurring_donation->type = 'rebill';
                    $init_recurring_donation->status = 'funded';
                    $init_recurring_donation->recurring_is_active = true;
                    $init_recurring_donation->stripe_subscription_id = $response_data->subscription;
                    $init_recurring_donation->stripe_invoice_id = $response_data->id;
                    $init_recurring_donation->stripe_paymentintent_id = $response_data->payment_intent;

                }
                
                break;

            case 'invoice.payment_failed':
                break;

            case 'charge.refunded':
                $donation->status = 'refunded';
                break;

            case 'payment_intent.succeeded':
                //Single payment
                if (!$response_data->invoice){

                    $donation->status = 'funded';
                    $donation->stripe_paymentintent_id = $response_data->id;

                }
                //Rebill
                else {

                    $donation_id = Leyka_Donations::get_instance()->get_donation_id_by_meta_value(
                        'stripe_paymentintent_id',
                        $response_data->id
                    );
                    $donation = Leyka_Donations::get_instance()->get_donation((int)$donation_id);
                    
                }

                $donation->add_gateway_response(json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                Leyka_Donation_Management::send_all_emails($donation);

                break;

            case 'payment_intent.payment_failed':
                $donation->status = 'failed';

                if (!empty($donation->init_recurring_donation)) {
                    $init_donation_id = $donation->init_recurring_donation;
                    $init_recurring_donation = Leyka_Donations::get_instance()->get_donation((int)$init_donation_id);
                    $init_recurring_donation->recurring_is_active = false;
                }

                if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
                    Leyka_Donation_Management::send_error_notifications($donation);
                }

                break;

            case 'customer.subscription.deleted':
                $donation_id = Leyka_Donations::get_instance()->get_donation_id_by_meta_value(
                    'stripe_subscription_id',
                    $response_data->id
                );
                $donation = Leyka_Donations::get_instance()->get_donation((int)$donation_id);
                $init_donation_id = $donation->init_recurring_donation;
                $init_recurring_donation = Leyka_Donations::get_instance()->get_donation((int)$init_donation_id);
                $init_recurring_donation->recurring_is_active = false;

                break;
        }

        exit(200);

    }

    public function cancel_recurring_subscription(Leyka_Donation_Base $donation) {

        if( !$donation->recurring_is_active ) {
            return true;
        }

        if($donation->type !== 'rebill') {
            return new WP_Error(
                'wrong_recurring_donation_to_cancel',
                __('Wrong donation given to cancel a recurring subscription.', 'leyka')
            );
        }

        if( !$donation->stripe_subscription_id ) {
            return new WP_Error('stripe_no_subscription_id', sprintf(__('<strong>Error:</strong> unknown Subscription ID for donation #%d. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>We are very sorry for inconvenience.', 'leyka'), $donation->id, leyka_get_website_tech_support_email()));
        }

        require_once LEYKA_PLUGIN_DIR.'gateways/stripe/lib/init.php';

        \Stripe\Stripe::setApiKey(leyka_options()->opt('stripe_key_secret'));

        try {

            $subscription = \Stripe\Subscription::retrieve($donation->stripe_subscription_id);
            $subscription->cancel();

        }
        catch (\Stripe\Exception\ApiErrorException $e){
            return new WP_Error('stripe_wrong_request_answer', sprintf(__('<strong>Error:</strong> the recurring subsciption cancelling request returned unexpected result. We cannot cancel the recurring subscription automatically.<br><br>Please, email abount this to the <a href="mailto:%s" target="_blank">website tech. support</a>.<br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email()));
        }

        $donation->recurring_is_active = false;

        return true;

    }

    public function cancel_recurring_subscription_by_link(Leyka_Donation_Base $donation) {

        if($donation->type !== 'rebill' || !$donation->recurring_is_active) {
            die();
        }

        header('Content-type: text/html; charset=utf-8');

        $recurring_cancelling_result = $this->cancel_recurring_subscription($donation);

        if($recurring_cancelling_result === true) {
            die(__('Recurring subscription cancelled successfully.', 'leyka'));
        } else if(is_wp_error($recurring_cancelling_result)) {
            die($recurring_cancelling_result->get_error_message());
        } else {
            die( sprintf(__('Error while trying to cancel the recurring subscription.<br><br>Please, email abount this to the <a href="%s" target="_blank">website tech. support</a>.<br>We are very sorry for inconvenience.', 'leyka'), leyka_get_website_tech_support_email()) );
        }

    }

    public function display_donation_specific_data_fields($donation = false) {

        if($donation) { // Edit donation page displayed

            $donation = Leyka_Donations::get_instance()->get_donation($donation);?>

            <label><?php _e('Stripe payment intent ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="stripe-paymentintent-id" name="stripe-paymentintent-id" placeholder="<?php _e('Enter Stripe payment intent ID', 'leyka');?>" value="<?php echo $donation->stripe_paymentintent_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->stripe_paymentintent_id;?></span>
                <?php }?>
            </div>

            <?php if($donation->type !== 'rebill') {
                return;
            }?>

            <label><?php _e('Stripe subscription ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="stripe-subscription-id" name="stripe-subscription-id" placeholder="<?php _e('Enter Stripe subscription ID', 'leyka');?>" value="<?php echo $donation->stripe_subscription_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->stripe_subscription_id;?></span>
                <?php }?>
            </div>

            <label><?php _e('Stripe invoice ID', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
                <?php if($donation->type === 'correction') {?>
                    <input type="text" id="stripe-invoice-id" name="stripe-invoice-id" placeholder="<?php _e('Enter Stripe invoice ID', 'leyka');?>" value="<?php echo $donation->stripe_invoice_id;?>">
                <?php } else {?>
                    <span class="fake-input"><?php echo $donation->stripe_invoice_id;?></span>
                <?php }?>
            </div>

            <?php $init_recurring_donation = $donation->init_recurring_donation;?>

            <div class="recurring-is-active-field">

                <label><?php _e('Recurring subscription is active', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <?php echo $init_recurring_donation->recurring_is_active ? __('yes', 'leyka') : __('no', 'leyka');

                    if( !$init_recurring_donation->recurring_is_active && $init_recurring_donation->recurring_cancel_date ) {
                        echo ' ('.sprintf(__('canceled on %s', 'leyka'), date(get_option('date_format').', '.get_option('time_format'), $init_recurring_donation->recurring_cancel_date)).')';
                    }?>
                </div>

            </div>

        <?php } else { // New donation page displayed ?>

            <label for="stripe-paymentintent-id"><?php _e('Stripe payment intent ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="stripe-paymentintent-id" name="stripe-paymentintent-id" placeholder="<?php _e('Enter Stripe payment intent ID', 'leyka');?>" value="">
            </div>

            <label for="stripe-subscription-id"><?php _e('Stripe subscription ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="stripe-subscription-id" name="stripe-subscription-id" placeholder="<?php _e('Enter Stripe subscription ID', 'leyka');?>" value="">
            </div>

            <label for="stripe-invoice-id"><?php _e('Stripe invoice ID', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="stripe-invoice-id" name="stripe-invoice-id" placeholder="<?php _e('Enter Stripe invoice ID', 'leyka');?>" value="">
            </div>

        <?php }

    }

    public function get_specific_data_value($value, $field_name, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'stripe_subscription_id':
                return $donation->get_meta('stripe_subscription_id');
            case 'stripe_paymentintent_id':
                return $donation->get_meta('stripe_paymentintent_id');
            case 'stripe_invoice_id':
                return $donation->get_meta('stripe_invoice_id');
            default:
                return $value;
        }

    }

    public function set_specific_data_value($field_name, $value, Leyka_Donation_Base $donation) {

        switch($field_name) {
            case 'stripe_subscription_id':
                return $donation->set_meta('stripe_subscription_id', $value);
            case 'stripe_paymentintent_id':
                return $donation->set_meta('stripe_paymentintent_id', $value);
            case 'stripe_invoice_id':
                return $donation->set_meta('stripe_invoice_id', $value);
            default:
                return false;
        }

    }

    public function save_donation_specific_data(Leyka_Donation_Base $donation) {

        if(isset($_POST['stripe-subscription-id']) && $donation->stripe_subscription_id != $_POST['stripe-subscription-id']) {
            $donation->stripe_subscription_id = $_POST['stripe-subscription-id'];
        }

        if(isset($_POST['stripe-paymentintent-id']) && $donation->stripe_paymentintent_id != $_POST['stripe-paymentintent-id']) {
            $donation->stripe_paymentintent_id = $_POST['stripe-paymentintent-id'];
        }

        if(isset($_POST['stripe-invoice-id']) && $donation->stripe_invoice_id != $_POST['stripe-invoice-id']) {
            $donation->stripe_invoice_id = $_POST['stripe-invoice-id'];
        }

    }

    public function add_donation_specific_data($donation_id, array $params) {

        if( !empty($params['stripe_subscription_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'stripe_subscription_id', $params['stripe_subscription_id']);
        }

        if( !empty($params['stripe_paymentintent_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'stripe_paymentintent_id', $params['stripe_paymentintent_id']);
        }

        if( !empty($params['stripe_invoice_id']) ) {
            Leyka_Donations::get_instance()->set_donation_meta($donation_id, 'stripe_invoice_id', $params['stripe_invoice_id']);
        }

    }

}

class Leyka_Stripe_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'stripe';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            'Stripe Credit Card',
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

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

    public function has_recurring_support() {
        return 'passive';
    }

}

function leyka_add_gateway_stripe() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Stripe_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_stripe');