<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Demirbank_Gateway class
 */

class Leyka_Demirbank_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function __construct() {

        parent::__construct();

        $this->_enque_success_page_scripts();
        $this->_set_success_page_content();
        $this->_set_ajax_actions();

    }

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
            ],
            'demirbank_3d_post_url' => [
                'type' => 'text',
                'title' => __('3D Post URL', 'leyka'),
                'comment' => __('Please, enter Demirbank 3D Post URL here.', 'leyka'),
                'required' => true
            ],
            'demirbank_support_email' => [
                'type' => 'text',
                'title' => __('Support email'),
                'comment' => __('Support email address to display into the card-check.', 'leyka'),
                'required' => true
            ]
        ];

    }

    public function is_setup_complete($pm_id = false) {
        return leyka_options()->opt('demirbank_client_id')
            && leyka_options()->opt('demirbank_store_key')
            && leyka_options()->opt('demirbank_3d_post_url')
            && leyka_options()->opt('demirbank_support_email');
    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['card'])) {
            $this->_payment_methods['card'] = Leyka_Demirbank_Card::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) { }

    public function submission_redirect_url($current_url, $pm_id) {
        return leyka_options()->opt('demirbank_3d_post_url');
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {

        $currency_id = !empty($_POST['leyka_donation_currency']) ?
            $_POST['leyka_donation_currency'] : leyka_get_main_currency();
        $currency_data = leyka_get_currencies_full_info($currency_id);

        $data = [
            'clientid' => str_replace(' ', '', leyka_options()->opt('demirbank_client_id')),
            'oid' => '',
            'amount' => $form_data['leyka_donation_amount'],
            'islemtipi' => 'Auth',
            'taksit' => '',
            'storetype' => '3D_PAY_HOSTING ',
            'okUrl' => leyka_get_success_page_url(),
            'failUrl' => leyka_get_failure_page_url(),
            'callbackUrl' => get_site_url().'/leyka/service/'.$this->_id.'/',
            'trantype'=> 'Auth',
            'instalment' => '',
            'rnd' => microtime(),
            'lang' => 'ru',
            'currency' => $currency_data['iso_code'],
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

        /** @todo Return this check when the Gateway checks their hash forming. ATM it doesn't match their own dev. manual data */
//        if( !$this->_is_callback_hash_correct($_POST) ) {
//            $message = __("This message has been sent because a call to your Demirbank callback function was made with wrong hash parameter. This could mean someone is trying to hack your payment website. The details of the call are below.", 'leyka')."\n\r\n\r".
//                "POST:\n\r".print_r($_POST, true)."\n\r\n\r".
//                "SERVER:\n\r".print_r($_SERVER, true)."\n\r\n\r";
//
//            wp_mail(get_option('admin_email'), __('Demirbank callback hash check failed!', 'leyka'), $message);
//        }

        if (empty($_POST['donation_id'])) {
            exit(200);
        }

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

    protected function _set_success_page_content() {

        add_filter('the_content', function($content){

            global $wp_query;

            if(
                empty($_POST)
                || empty($_POST['donation_id'])
                || (empty($wp_query) && empty($wp_query->post))
                || url_to_postid(leyka_get_success_page_url()) !== $wp_query->post->ID
            ) {
                return $content;
            }

            $donation = Leyka_Donations::get_instance()->get_donation((int)$_POST['donation_id']);

            if( !$donation ) {
                return $content;
            }

            $card_check_text_html = $this->_get_card_check_text($donation);

            $card_check_tools_html = str_replace(
                [
                    '#SAVE_TEXT#',
                    '#SEND_TEXT#',
                    '#DONATION_ID#',
                    '#CARD_CHECK_SENT_TEXT#',
                    '#OK_TEXT#'
                ],
                [
                    __('Save', 'leyka'),
                    __('Send', 'leyka'),
                    $donation->id,
                    sprintf(__("Card-check has been sent to <b> %s </b>", 'leyka'), $donation->donor_email),
                    __("OK", 'leyka'),
                ],
                file_get_contents(LEYKA_PLUGIN_DIR.'gateways/demirbank/templates/parts/card_check_tools.html')
            );

            return $content.str_replace(
                [
                    '#CARD_CHECK_TEXT_TMPL#',
                    '#CARD_CHECK_TOOLS_TMPL#'
                ],
                [
                    $card_check_text_html,
                    $card_check_tools_html
                ],
                file_get_contents(LEYKA_PLUGIN_DIR.'gateways/demirbank/templates/card_check.html')
            );

        });

    }

    protected function _enque_success_page_scripts() {

        add_action( 'wp_enqueue_scripts', function () {

            if( is_page('thank-you-for-your-donation') === true && empty($_POST) === false ) {

                wp_enqueue_style(
                    'demirbank-card-check',
                    LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Demirbank_Gateway::get_instance()->id.'/css/leyka.demirbank.card_check.css'
                );

                wp_enqueue_script(
                    'leyka-demirbank-card-check',
                    LEYKA_PLUGIN_BASE_URL.'gateways/'.Leyka_Demirbank_Gateway::get_instance()->id.'/js/leyka.demirbank.card_check.js',
                    [],
                    LEYKA_VERSION,
                    true
                );

            }

        });

    }

    protected function _get_card_check_text($donation) {

        $vars = json_decode($donation->gateway_response, true);
        $campaign_id = $donation->campaign_id;
        $campaign = new Leyka_Campaign($campaign_id);

        return str_replace(
            [
                '#CARD_CHECK_TEXT#',
                '#ORDER_INFO_TEXT#',
                '#ORDER_ID#',
                '#ORDER_DATE#',
                '#ITEM#',
                '#PRICE#',
                '#PAYMENT_INFO_TEXT#',
                '#CARD_BRAND#',
                '#CARD_NUMBER#',
                '#AUTHORISATION_CODE#',
                '#MERCHANT_INFO_TEXT#',
                '#MERCHANT_NAME#',
                '#SUPPORT_EMAIL#'
            ],
            [
                __("Card-check", 'leyka'),
                __("ORDER INFO", 'leyka'),
                sprintf(__("<b>Order #:</b> %s", 'leyka'), $vars['ReturnOid']),
                sprintf(__("<b>Order placed:</b> %s", 'leyka'), date('d.m.Y H:i', strtotime($vars['EXTRA_TRXDATE']))),
                sprintf(__("<b>Donation purpose:</b> %s", 'leyka'), $campaign->payment_title),
                sprintf(__("<b>Price:</b> %s %s", 'leyka'), $vars['amount'], $donation->currency_label),
                __("PAYMENT INFO", 'leyka'),
                sprintf(__("<b>Card brand:</b> %s", 'leyka'), $vars['EXTRA_CARDBRAND']),
                sprintf(__("<b>Card last four digits:</b> %s", 'leyka'), substr($vars['MaskedPan'], strlen($vars['MaskedPan'])-4, 4)),
                sprintf(__("<b>Authorisation code:</b> %s", 'leyka'), $vars['AuthCode']),
                __("MERCHANT INFO", 'leyka'),
                sprintf(__("<b>Merchant name:</b> %s", 'leyka'), $_SERVER['HTTP_HOST']),
                sprintf(__("<b>Support email:</b> %s", 'leyka'), leyka_options()->opt('demirbank_support_email'))
            ],
            file_get_contents(LEYKA_PLUGIN_DIR.'gateways/demirbank/templates/parts/card_check_text.html')
        );

    }

    public function send_card_check_email($donation_id) {

        if( !$donation_id && empty($_POST) ) {
            return false;
        }

        $donation_id = $donation_id ? $donation_id : $_POST['donation_id'];
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        echo wp_mail(
            $donation->donor_email,
            __('Card-check', 'leyka'),
            $this->_get_card_check_text($donation),
            [
                'From: '.$_SERVER['HTTP_HOST'].' <'.leyka_options()->opt('demirbank_support_email').'>',
                'content-type: text/html'
            ]
        );

        wp_die();

    }

    protected function _set_ajax_actions() {

        add_action( 'wp_ajax_send-card-check', [$this, 'send_card_check_email']);
        add_action( 'wp_ajax_nopriv_send-card-check', [$this, 'send_card_check_email']);

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
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-elkart.svg',
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