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
        $this->_may_support_recurring = false;

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
        $description = (
            !empty($form_data['leyka_recurring']) ? _x('[RS]', 'For "recurring subscription"', 'leyka').' ' : ''
            )
            .$donation->payment_title." (№ $donation_id); {$donation->donor_name}; {$donation->donor_email}";

        \Stripe\Stripe::setApiKey(leyka_options()->opt('stripe_key_secret'));

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price_data' => [
                    'unit_amount' => $form_data['leyka_donation_amount']*100,
                    'currency' => $form_data['leyka_donation_currency'] === 'rur' ? 'rub' : $form_data['leyka_donation_currency'],
                    'product' => leyka_options()->opt('stripe_product_id')
                ],
                'quantity' => 1,
            ]],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => leyka_get_success_page_url(),
            'cancel_url' => $compaign->url,
            'payment_intent_data' => [
                'description' => $description,
                'metadata' => [
                    'donation_id' => $donation_id
                ]
            ]
        ]);

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

        if($vars['refunded'] === true){
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

        $payment_intent = $event->data->object;
        $donation = Leyka_Donations::get_instance()->get_donation((int)$payment_intent->metadata->donation_id);
        $donation->add_gateway_response($payment_intent->toJSON());

        switch($event->type) {
            case 'charge.refunded':
                $donation->status = 'refunded';
                break;

            case 'payment_intent.payment_failed':
                $donation->status = 'failed';

                if(leyka_options()->opt('notify_tech_support_on_failed_donations')) {
                    Leyka_Donation_Management::send_error_notifications($donation);
                }

                break;

            case 'payment_intent.succeeded':
                $donation->status = 'funded';

                Leyka_Donation_Management::send_all_emails($donation->id);

                break;
        }

        exit(200);

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
        return false;
    }

}

function leyka_add_gateway_stripe() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Stripe_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_stripe');