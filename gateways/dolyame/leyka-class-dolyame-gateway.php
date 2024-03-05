<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Dolyame_Gateway class
 */

class Leyka_Dolyame_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'dolyame';
        $this->_title = __('Dolyame', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            '',
            $this->_id
        );

        $this->_docs_link = '';
        $this->_registration_link = 'https://dolyame.ru/business/#formr';

        $this->_min_commission = 4.0;
        $this->_receiver_types = ['legal',];
        $this->_may_support_recurring = false;

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'dolyame_login' => [
                'type' => 'text',
                'title' => __('Login', 'leyka'),
                'comment' => '',
                'required' => true,
                'placeholder' => '',
            ],
            'dolyame_password' => [
                'type' => 'text',
                'title' => __('Password', 'leyka'),
                'comment' => '',
                'required' => true,
                'is_password' => true,
                'placeholder' => '',
            ],
            'dolyame_cert' => [
                'type' => 'textarea',
                'title' => __('Content certificate (.pem file)', 'leyka'),
                'comment' => __('Begin with -----BEGIN CERTIFICATE-----', 'leyka'),
                'required' => true,
            ],
            'dolyame_key' => [
                'type' => 'textarea',
                'title' => __('Content private.key file', 'leyka'),
                'comment' => __('Begin with -----BEGIN PRIVATE KEY-----', 'leyka'),
                'required' => true,
            ],
            'dolyame_prefix' => [
                'type' => 'text',
                'title' => __('Prefix', 'leyka'),
                'comment' => '',
                'required' => true,
                'placeholder' => '',
            ],
            'dolyame_request_handler' => [
                'type' => 'select',
                'title' => __('Request handler', 'leyka'),
                'comment' => __('Change it on error', 'leyka'),
                'default' => 'file',
                'required' => true,
                'list_entries' => ['file' => 'Stream', 'curl' => 'cURL',],
            ],
        ];

    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['dolyame'])) {
            $this->_payment_methods['dolyame'] = Leyka_Dolyame::get_instance();
        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = Leyka_Donations::get_instance()->get($donation_id);

        //$phone = isset($form_data['leyka_donor_phone']) ? $form_data['leyka_donor_phone'] : false;

        $prefix = leyka_options()->opt($this->_id.'_prefix');


        $data      = [
            'order'            => [
                'id'             => $prefix.$donation_id,
                'amount'         => $donation->amount,
                'prepaid_amount' => 0,
                'items'          => [
                    [
                        'name' => $donation->payment_title,
                        'quantity' => 1,
                        'price' => $donation->amount,
                    ]
                ],
            ],
            'client_info'      => [
                'first_name' => $donation->donor_name,
//                'phone'      => $phone,
                'email'      => $donation->donor_email,
            ],
            'notification_url' => site_url('/leyka/service/dolyame/response/'),
            'fail_url'         => leyka_get_failure_page_url(),
            'success_url'      => leyka_get_success_page_url(),
        ];

        $api = $this->init_api();
        try {
            $response = $api->create($data);
            $donation->add_gateway_response($response);
            $this->_submit_result = 'success';
            $this->_redirect_url = empty($response['link']) ?
                        leyka_get_failure_page_url() : $response['link'];
        } catch (\Exception $ex) {
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

    public function submission_redirect_url($current_url, $pm_id) {

        return $this->_redirect_url;
    }

    public function submission_redirect_type($redirect_type, $pm_id, $donation_id) {
        return 'redirect';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

		return $form_data;

    }


    public function _handle_service_calls($call_type = '') {

        $response = file_get_contents('php://input');
        if(!$response) {
            throw new Exception('Empty body');
        }
        $response = json_decode($response, true);
        if(!$response) {
            throw new Exception('Wrong body format');
        }
        $order_id = $response['id'];


        $info = $this->get_transaction_info($order_id);


        $prefix = leyka_options()->opt($this->_id.'_prefix');
        $donation_id = $order_id;
        if ($prefix) {
            $donation_id = substr($order_id,strlen($prefix));
        }

        $donation = Leyka_Donations::get_instance()->get(intval($donation_id));

        if (
            $info['status'] === 'waiting_for_commit'
            || $info['status'] === 'wait_for_commit'
        ) {
            $response = $this->commit_payment($donation);
            $donation->add_gateway_response($response);
            return true;
        }

        if ($info['status'] !== 'committed') {
            exit();
        }

        if($donation->status === 'funded') {
            exit();
        }


		$donation->add_gateway_response($response);
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
    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) )
            return [];

        return apply_filters(
            'leyka_donation_gateway_response',
            [
                __('Outcoming sum:', 'leyka') => $this->_get_value_if_any($vars, 'amount'),
                __('Invoice ID:', 'leyka') => $this->_get_value_if_any($vars, 'id'),
            ],
            $donation
        );

    }

    protected function init_api() {
        if (!class_exists("\\Dolyame\\Payment\\Client")) {
            require_once __DIR__.'/lib/Client.php';
        }
        $login = leyka_options()->opt($this->_id.'_login');
        $password = leyka_options()->opt($this->_id.'_password');
        $cert = leyka_options()->opt($this->_id.'_cert');
        $key = leyka_options()->opt($this->_id.'_key');
        $requestHander = leyka_options()->opt($this->_id.'_request_handler');

        $api = new Dolyame\Payment\Client($login, $password);
        $api->setCertPath($cert);
        $api->setKeyPath($key);
        if ($requestHander == 'file') {
            $api->useFileRequestHandler();
        }
        return $api;
    }

    protected function get_transaction_info($order_id) {

        $api = $this->init_api();
        $result = $api->info($order_id);
        return $result;
    }

    protected function commit_payment($donation){

        $data = [
            'amount'         => $donation->amount,
            'prepaid_amount' => 0,
            'items'          => [
                [
                    'name' => $donation->payment_title,
                    'quantity' => 1,
                    'price' => $donation->amount,
                ]
            ],
        ];

        $prefix = leyka_options()->opt($this->_id.'_prefix');

        $api = $this->init_api();
        $result = $api->commit($prefix.$donation->id, $data);
        return $result;
    }

}

class Leyka_Dolyame extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'dolyame';
        $this->_gateway_id = 'dolyame';
        $this->_category = 'misc';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Dolyame', 'leyka');
        $this->_label = __('Dolyame', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/dolyame.svg',

        ]);

        $this->_supported_currencies[] = 'rub';
        $this->_default_currency = 'rub';

    }

}

function leyka_add_gateway_dolyame() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_dolyame_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_dolyame');