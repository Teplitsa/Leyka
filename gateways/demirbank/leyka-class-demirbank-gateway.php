<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Demirbank_Gateway class
 */

class Leyka_Demirbank_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'demirbank';
        $this->_title = __('Demirbank', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('<a href="//demirbank.com/">Demirbank</a> is a technology company that builds economic infrastructure for the internet. Businesses of every size—from new startups to public companies—use our software to accept payments and manage their businesses online.', 'leyka'),
            $this->_id
        );

        // TODO Добавить ссылку на доки по подключению и ссылку регистрации
        //$this->_docs_link = 'https://leyka.te-st.ru/docs/podklyuchenie-demirbank/';
        //$this->_registration_link = '//dashboard.demirbank.com/register';
        $this->_has_wizard = false;

        $this->_min_commission = '2';
        $this->_receiver_types = ['legal'];
        $this->_may_support_recurring = false;
        $this->_countries = ['kg'];

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'demirbank_client_id' => [
                'type' => 'text',
                'title' => __('Client ID', 'leyka'),
                'comment' => __('Please, enter your Demirbank client (merchant) ID here.', 'leyka'),
                'required' => true
            ],
            'demirbank_store_key' => [
                'type' => 'text',
                'title' => __('Store key', 'leyka'),
                'comment' => __('Please, enter your Demirbank store key here.', 'leyka'),
                'is_password' => true,
                'required' => true
            ]
        ];

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('demirbank_client_id')
            && leyka_options()->opt('demirbank_store_key');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Demirbank_Card::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) { }

    public function submission_redirect_url($current_url, $pm_id) {
        return 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate';
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $data = [
            'clientid' => str_replace(' ', '', leyka_options()->opt('demirbank_client_id')),
            'oid' => '',
            'amount' => $form_data['leyka_donation_amount'],
            'islemtipi' => 'Auth',
            'taksit' => '',
            'storetype' => '3d_Pay_Hosting',
            'okUrl' => leyka_get_success_page_url(),
            'failUrl' => leyka_get_failure_page_url(),
            'callbackUrl' => get_site_url().'/leyka/service/'.$this->_id.'/',
            'trantype'=> 'Auth',
            'instalment' => '',
            'rnd' => microtime(),
            'lang' => 'en',
            'currency' => '417',
            'refreshtime' => 5
        ];
        $storekey = str_replace(' ', '', leyka_options()->opt('demirbank_store_key'));
        $hash_str = $data['clientid'].$data['oid'].$data['amount'].$data['okUrl'].$data['failUrl'] .$data['islemtipi'].$data['taksit'].$data['rnd'].$data['callbackUrl'].$storekey;
        $data['hash'] = base64_encode(pack('H*',sha1($hash_str)));

        return $data;

    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {

        if( !$donation->gateway_response ) {
            return [];
        }

        $vars = json_decode($donation->gateway_response, true);

        if( !$vars || !is_array($vars) ) {
            return [];
        }

        $vars_final[__('Transaction ID:', 'leyka')] = $vars['TransId'];
        $vars_final[__('Response:', 'leyka')] = $vars['Response'];
        $vars_final[__('Last event date:', 'leyka')] = date('d.m.Y H:i', strtotime($vars['EXTRA_TRXDATE']));

        if($vars['ProcReturnCode'] !== '00') {
            $vars_final[__('Donation failure reason:', 'leyka')] = $vars['ErrMsg'];
        }

        return $vars_final;

    }

    protected function _is_callback_hash_correct($request) {

        $hash_str = $request['clientid'].$request['oid'].$request['AuthCode'].$request['ProcReturnCode']
            .$request['Response'].$request['mdStatus'].$request['eci'].$request['cavv'].$request['md'].$request['rnd'];
        $hash = base64_encode(pack('H*',sha1($hash_str)));

        return $hash === $request['HASH'];

    }

    public function _handle_service_calls($call_type = '') {

        //TODO Вернуть проверку когда гейт проверит формирование своего хэша. Сейчас он не сходится с их же схемой.
        /*
        if(!$this->_is_callback_hash_correct($_POST)) {
            $message = __("This message has been sent because a call to your Demirbank callback function was made with wrong hash parameter. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r".
                "POST:\n\r".print_r($_POST, true)."\n\r\n\r".
                "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";

            wp_mail(get_option('admin_email'), __('Demirbank callback hash check failed!', 'leyka'), $message);
        }
        */

        $donation = Leyka_Donations::get_instance()->get_donation((int)$_POST['donation_id']);

        if($_POST['ProcReturnCode'] === '00') {

            $donation->status = 'funded';
            Leyka_Donation_Management::send_all_emails($donation);

        } else {
            $donation->status = 'failed';
        }

        $donation->add_gateway_response(json_encode($_POST));

        exit(200);
    }

}

class Leyka_Demirbank_Card extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'card';
        $this->_gateway_id = 'demirbank';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Bank card', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ]);

        $this->_supported_currencies[] = 'kgs';
        $this->_default_currency = 'kgs';

    }

    public function has_recurring_support() {
        return false;
    }

}

function leyka_add_gateway_demirbank() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Demirbank_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_demirbank');