<?php if (!defined('WPINC')) die;

/**
 * Leyka_Cryptomus_Gateway class
 */
class Leyka_Cryptomus_Gateway extends Leyka_Gateway
{
    const CRYPTOMUS_ERROR_STATUSES = [
        'fail',
        'system_fail',
        'wrong_amount',
        'cancel',
    ];

    const CRYPTOMUS_PENDING_STATUSES = [
        'process',
        'check',
    ];

    const PAID_STATUSES = [
        'paid',
        'paid_over',
    ];

    protected static $_instance;

    private $api_redirect_url;

    protected function __construct()
    {
        parent::__construct();
        require_once LEYKA_PLUGIN_DIR . 'gateways/cryptomus/lib/Payment.php';
    }

    protected function _set_attributes()
    {

        $this->_id = 'cryptomus';
        $this->_title = __('Cryptomus', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('https://cryptomus.com/', 'leyka'),
            $this->_id
        );

        $this->_docs_link = 'https://doc.cryptomus.com/personal';
        $this->_registration_link = 'https://app.cryptomus.com/signup';
        $this->_has_wizard = false;

        $this->_min_commission = '1';
        $this->_receiver_types = ['legal'];
        $this->_may_support_recurring = false;

    }

    protected function _set_options_defaults()
    {

        if ($this->_options) {
            return;
        }

        $this->_options = [
            $this->_id . '_payment_key' => [
                'type' => 'text',
                'title' => __('Payment API-key', 'leyka'),
                /* translators: %s: Label. */
                'comment' => __('You can find the API key in the settings of your personal account. Read more https://cryptomus.com/plugins', 'leyka'),
                'required' => true,
            ],
            $this->_id . '_merchant_uuid' => [
                'type' => 'text',
                'title' => __('Merchant UUID', 'leyka'),
                /* translators: 1: Label, 2: Title. */
                'comment' => __('You can find the UUID in the settings of your personal account. Read more https://cryptomus.com/plugins', 'leyka'),
                'required' => true,
                /* translators: %s: Placeholder. */
                'is_password' => true,
            ],
            $this->_id . '_lifetime' => [
                'type' => 'number',
                'title' => __('Invoice Lifetime (hours)', 'leyka'),
                /* translators: %s: Label. */
                'required' => false,
                'default' => 1
            ]
        ];

    }

    public function is_setup_complete($pm_id = false)
    {
        return leyka_options()->opt($this->_id . '_payment_key')
            && leyka_options()->opt($this->_id . '_merchant_uuid');
    }

    protected function _initialize_pm_list()
    {
        if (empty($this->_payment_methods['cryptomus'])) {
            $this->_payment_methods['cryptomus'] = Leyka_Cryptomus::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data)
    {
        $currency_id = !empty($_POST['leyka_donation_currency']) ?
            $_POST['leyka_donation_currency'] : leyka_get_main_currency();
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $apiKey = str_replace(' ', '', leyka_options()->opt($this->_id . '_payment_key'));
        $merchantId = str_replace(' ', '', leyka_options()->opt($this->_id . '_merchant_uuid'));

        $paymentData = [
            'amount' => $form_data['leyka_donation_amount'],
            'currency' => $currency_id,
            'merchant' => $merchantId,
            'order_id' => 'leyka_' . $donation_id,
            'url_return' => get_site_url() . '/leyka/service/' . $this->_id . '/',
            'url_callback' => get_site_url() . '/leyka/service/' . $this->_id . '/',
            'lifetime' => (int)leyka_options()->opt($this->_id . '_lifetime') * 3600,
            'plugin_name' => 'leyka',
        ];

        try {
            $paymentService = new \Payment($apiKey, $merchantId);
            $response = $paymentService->create($paymentData);
            $donation->add_gateway_response($response);
            $this->api_redirect_url = $response['url'];
        } catch (\RequestBuilderException $exception) {
            $donation->add_gateway_response($exception->getMessage());

            leyka()->add_payment_form_error(new WP_Error(
                'leyka_donation_error',
                /* translators: 1: Message, 2: Email. */
                sprintf(
                /* translators: 1: Message, 2: Support email. */
                    __('Error while processing the payment: %1$s. Your money will remain intact. Please report to the <a href="mailto:%2$s" target="_blank">website tech support</a>.', 'leyka'),
                    $exception->getMessage(),
                    leyka_get_website_tech_support_email()
                )));
        }

        return $paymentData;
    }

    public function submission_redirect_url($current_url, $pm_id)
    {
        return $this->api_redirect_url;
    }


    public function submission_redirect_type($redirect_type, $pm_id, $donation_id)
    {
        return 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id)
    {
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        $currency_id = !empty($_POST['leyka_donation_currency']) ?
            $_POST['leyka_donation_currency'] : leyka_get_main_currency();

        $data = [
            'amount' => $form_data['leyka_donation_amount'],
            'currency' => $currency_id,
            'order_id' => 'layka_' . $donation_id,
            'url_return' => site_url(),
            'url_callback' => get_site_url() . '/leyka/service/' . $this->_id . '/',
            'lifetime' => (int)leyka_options()->opt($this->_id . '_lifetime') * 3600,
            'plugin_name' => 'leyka',
        ];

        return apply_filters('leyka_cryptomus_custom_submission_data', $data, $pm_id, $donation);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation)
    {
        if (!$donation->gateway_response) {
            return [];
        }

        $vars = $donation->gateway_response;

        if (!$vars || !is_array($vars)) {
            return [];
        }

        $vars_final[__('UUID:', 'leyka')] = $vars['uuid'];
        $vars_final[__('Order:', 'leyka')] = $vars['order_id'];
        $vars_final[__('Status:', 'leyka')] = $vars['status'];

        return $vars_final;
    }

    protected function _is_callback_hash_correct($data)
    {
        $apiKey = str_replace(' ', '', leyka_options()->opt($this->_id . '_payment_key'));

        if (!$apiKey) {
            return false;
        }

        $signature = $data['sign'];
        if (!$signature) {
            return false;
        }

        unset($data['sign']);

        $hash = md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)) . $apiKey);
        if (!hash_equals($hash, $signature)) {
            return false;
        }

        return true;

    }

    public function _handle_service_calls($call_type = '')
    {
        $notification = json_decode(file_get_contents('php://input'), true);

        if (empty($notification['uuid'])) {
            exit(500);
        }

        if (!$this->_is_callback_hash_correct($notification)) {
            exit(500);
        }

        if (preg_match('/^leyka_(\d+)$/', $notification['order_id'], $matches)) {
            $orderId = $matches[1];
        } else {
            exit(500);
        }

        $donation = Leyka_Donations::get_instance()->get($orderId);

        if( !$donation ) {
            /** @todo Process the error somehow */
            exit(500);
        }
        $donation->add_gateway_response($notification);

        $cryptomusOrderStatus = $notification['status'];

        $orderStatus = null;
        if (in_array($cryptomusOrderStatus, self::CRYPTOMUS_ERROR_STATUSES)) {
            Leyka_Donation_Management::send_error_notifications($donation);
        } elseif (in_array($cryptomusOrderStatus, self::CRYPTOMUS_PENDING_STATUSES)) {
            // nothing to do
        } elseif (in_array($cryptomusOrderStatus, self::PAID_STATUSES)) {
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
        }
        exit('ok');
    }

}

class Leyka_Cryptomus extends Leyka_Payment_Method
{

    protected static $_instance = null;

    public function _set_attributes()
    {

        $this->_id = 'cryptomus';
        $this->_gateway_id = 'cryptomus';
        $this->_category = 'digital_currencies';

        $this->_description = apply_filters(
            'leyka_pm_description',
            '',
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Cryptocurrency', 'leyka');
        $this->_label = __('Cryptomus', 'leyka');
        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'gateways/'.$this->_id.'/icons/cryptomus_front.svg',
        ]);

        $this->_supported_currencies = ['eur', 'usd', 'rub', 'uah', 'kgs', 'byn', 'gbp', 'jpy', 'aud', 'cad', 'chf', 'nzd', 'cny', 'hkd', 'sgd', 'inr'];
        $this->_default_currency = 'usd';

    }

}

function leyka_add_gateway_cryptomus()
{ // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Cryptomus_Gateway::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_gateway_cryptomus');
