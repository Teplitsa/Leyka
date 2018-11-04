<?php
require_once __DIR__ . '/../../www/vendor/autoload.php';

use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;

if (!defined('WPINC')) die;

/**
 * Leyka_Sberbank_Gateway class
 */
class Leyka_Sberbank_Gateway extends Leyka_Gateway
{

    protected static $_instance; // Gateway is always a singleton

    /** @var string */
    private $redirectUrl;

    protected function _set_options_defaults()
    {
        $this->_options = array(
            'sberbank-acquiring-login' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Sberbank payment gateway login', 'leyka'),
                'required' => 1,
                'placeholder' => __('Sberbank payment gateway login', 'leyka'),
            ),
            'sberbank-acquiring-password' => array(
                'type' => 'text',
                'default' => '',
                'title' => __('Sberbank payment gateway password', 'leyka'),
                'required' => 1,
                'placeholder' => __('Sberbank payment gateway password', 'leyka'),
            ),
        );
    }

    protected function _set_attributes()
    {
        $this->_id = 'sberbank';
        $this->_title = __('Sberbank', 'leyka');
        $this->_docs_link = 'http://nashideti.org/';
    }

    protected function _initialize_pm_list()
    {
        if (empty($this->_payment_methods[Leyka_Sberbank_Acquiring::PAYMENT_METHOD_ID])) {
            $this->_payment_methods[Leyka_Sberbank_Acquiring::PAYMENT_METHOD_ID] = Leyka_Sberbank_Acquiring::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data)
    {
        $username = leyka_options()->opt('sberbank-acquiring-login');
        $password = leyka_options()->opt('sberbank-acquiring-password');
        $donation = new Leyka_Donation($donation_id);
        $orderAmount = $form_data['leyka_donation_amount'];
        $client = new Client(
            [
                'userName' => $username,
                'password' => $password,
                'apiUri' => Client::API_URI_TEST,
            ]
        );
        $result = $client->registerOrder(
            $donation_id,
            $orderAmount * 100,
            leyka_get_success_page_url(),
            [
                'currency' => Currency::RUB,
                'failUrl' => leyka_get_failure_page_url(),
                'merchantOrderNumber' => $donation_id
            ]
        );
        if (!isset($result['errorCode'])) {
            $donation->add_gateway_response(serialize($result));
            $this->redirectUrl = $result['formUrl'];
            $this->orderId = $result['orderId'];
//            header('Location: ' . $this->redirectUrl);
//            exit();
        }
    }

    public function submission_redirect_url($current_url, $pm_id)
    {
        return $this->redirectUrl;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id)
    {
        $donation = new Leyka_Donation($donation_id);
        $post = get_post($donation_id);
//        return [
//            'userName' => leyka_options()->opt('sberbank-acquiring-login'),
//            'password' => leyka_options()->opt('sberbank-acquiring-password'),
//            'currency' => 643, // rur
//            'orderNumber' => $donation_id,
//            'amount' => $donation->amount * 100,
//            'description' => $post->post_title,
//            'returnUrl' => leyka_get_success_page_url(),
//            'failUrl' => leyka_get_failure_page_url(),
//        ];
        return [
            'mdOrder' => $this->orderId,
            'http_method' => 'get'
        ];
    }

    public function _handle_service_calls($call_type = '')
    {
        if (isset($_GET['operation']) && $_GET['operation'] === 'deposited') {
            if (isset($_GET['status']) && (int)$_GET['status'] === 1) {
                $donation = new Leyka_Donation($_GET['orderNumber']);
                $donation->add_gateway_response($_POST);
                if ($donation->status !== 'funded') {
                    $donation->status = 'funded';
                }
            }
        }
        file_put_contents(__DIR__ . '/log', var_export($_POST, true));
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation)
    {
        return array(
            __('Response:', 'leyka') => unserialize($donation->gateway_response)
        );
    }

    public function log_gateway_fields($donation_id)
    {
    }

}

class Leyka_Sberbank_Acquiring extends Leyka_Payment_Method
{

    const PAYMENT_METHOD_ID = 'sberbank_acquiring';

    protected static $_instance;

    protected function _set_attributes()
    {
        $this->_id = self::PAYMENT_METHOD_ID;
        $this->_gateway_id = 'sberbank';
        $this->_label_backend = __('Sberbank acquiring', 'leyka');
        $this->_label = __('Sberbank acquiring', 'leyka');
        $this->_icons = apply_filters(
            'leyka_icons_' . $this->_gateway_id . '_' . $this->_id, array(
                LEYKA_PLUGIN_BASE_URL . 'gateways/sberbank/icons/sber_s.png',
            )
        );
        $this->_submit_label = __('Sberbank acquiring', 'leyka');
        $this->_supported_currencies = array('rur');
        $this->_default_currency = 'rur';
        $this->_ajax_without_form_submission = false;
    }

    protected function _set_options_defaults()
    {
        if ($this->_options) {
            return;
        }
        $this->_options = array();
    }

}

function leyka_add_gateway_sberbank()
{ // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Sberbank_Gateway::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_gateway_sberbank');