<?php if( !defined('WPINC') ) die;
/**
 * Leyka_CP_Gateway class
 */

class Leyka_Stripe_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected $_api_redirect_url;

    protected function _set_attributes() {

        $this->_id = 'stripe';
        $this->_title = 'Stripe';

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="//stripe.com/">Stripe</a> is a technology company that builds economic infrastructure for the internet. Businesses of every size—from new startups to public companies—use our software to accept payments and manage their businesses online.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-cloudpayments/';
        $this->_registration_link = '//dashboard.stripe.com/register';
        $this->_has_wizard = false;

        $this->_min_commission = 2.8;
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
            )
        );

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('stripe_key_public') && leyka_options()->opt('stripe_key_secret');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Stripe_Card::get_instance();
        }
    }

    public function localize_js_strings(array $js_data) {
        return array_merge($js_data, array(
            'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka')
        ));
    }

    public function enqueue_gateway_scripts() {

        /*
        if(Leyka_Stripe_Card::get_instance()->active) {

            wp_enqueue_script(
                'leyka-stripe',
                LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Stripe_Gateway::get_instance()->id.'/js/leyka.stripe.js',
                array('jquery', 'leyka-public'),
                LEYKA_VERSION.'.001',
                true
            );

        }
*/
        add_filter('leyka_js_localized_strings', array($this, 'localize_js_strings'));
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        require_once LEYKA_PLUGIN_DIR.'gateways/stripe/lib/init.php';

        $secret_key = leyka_options()->opt('stripe_key_secret');

        \Stripe\Stripe::setApiKey($secret_key);

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price' => 'price_1JU94LJyYVP3cRIfzx2gXIl8',
                'quantity' => 1,
            ]],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => leyka_get_success_page_url(),
            'cancel_url' => leyka_get_failure_page_url(),
        ]);

        $this->_api_redirect_url = $checkout_session->url;

        return [];

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $this->_api_redirect_url;
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {
        return [];
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {
        return [];
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

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

        $this->_processing_type = 'custom-process-submit-event';

    }

    public function has_recurring_support() {
        return false;
    }

}

function leyka_add_gateway_stripe() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Stripe_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_stripe');
