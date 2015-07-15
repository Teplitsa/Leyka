<?php if( !defined('WPINC') ) die;
/**
 * Leyka_Quittance_Gateway class
 */

class Leyka_Quittance_Gateway extends Leyka_Gateway {

    protected static $_instance; // Gateway is always a singleton

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array(
            'quittance_redirect_page' => array(
                'type' => 'select',
                'default' => leyka_get_default_success_page(),
                'title' => __('Page to redirect a donor after a donation', 'leyka'),
                'description' => __('Select a page for donor to redirect to after he has acquired a quittance.', 'leyka'),
                'required' => 0, // 1 if field is required, 0 otherwise
                'placeholder' => '', // For text fields
                'length' => '', // For text fields
                'list_entries' => leyka_get_pages_list(),
                'validation_rules' => array(), // List of regexp?..
            ),
        );
    }
    
    protected function _set_attributes() {

        $this->_id = 'quittance';
        $this->_title = __('Quittances', 'leyka');
        $this->_docs_link = '//leyka.te-st.ru/docs/nastrojka-lejki/';
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['bank_order'])) {
            $this->_payment_methods['bank_order'] = Leyka_Bank_Order::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        load_textdomain('leyka', LEYKA_PLUGIN_DIR.'lang/leyka-'.get_locale().'.mo'); // Localize a quittance first

        header('HTTP/1.1 200 OK');
        header('Content-Type: text/html; charset=utf-8');

        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        $quittance_html = str_replace(
            array(
                '#BACK_TO_DONATION_FORM_TEXT#',
                '#PRINT_THE_QUITTANCE_TEXT#',
                '#QUITTANCE_RECEIVED_TEXT#',
                '#SUCCESS_URL#',
                '#PAYMENT_COMMENT#',
                '#PAYER_NAME#',
                '#RECEIVER_NAME#',
                '#SUM#',
                '#INN#',
                '#KPP#',
                '#ACC#',
                '#RECEIVER_BANK_NAME#',
                '#BIC#',
                '#CORR#',
            ),
            array( // Form field values
                __('Return to the donation form', 'leyka'),
                __('Print the quittance', 'leyka'),
                __("OK, I've received the quittance", 'leyka'),
                get_permalink(leyka_options()->opt('quittance_redirect_page')),
                $campaign->payment_title." (№ $donation_id)",
                $form_data['leyka_donor_name'],
                leyka_options()->opt('org_full_name'),
                (int)$form_data['leyka_donation_amount'],
                leyka_options()->opt('org_inn'),
                leyka_options()->opt('org_kpp'),
                leyka_options()->opt('org_bank_account'),
                leyka_options()->opt('org_bank_name'),
                leyka_options()->opt('org_bank_bic'),
                leyka_options()->opt('org_bank_corr_account'),
            ),
            $this->_payment_methods[$pm_id]->get_quittance_html()
        );

        for($i=0; $i<10; $i++) {
            $quittance_html = str_replace("#INN_$i#", substr(leyka_options()->opt('org_inn'), $i, 1), $quittance_html);
        }
        for($i=0; $i<20; $i++) {
            $quittance_html = str_replace(
                "#ACC_$i#", substr(leyka_options()->opt('org_bank_account'), $i, 1), $quittance_html
            );
        }
        for($i=0; $i<9; $i++) {
            $quittance_html = str_replace(
                "#BIC_$i#", substr(leyka_options()->opt('org_bank_bic'), $i, 1), $quittance_html
            );
        }
        for($i=0; $i<20; $i++) {
            $quittance_html = str_replace(
                "#CORR_$i#", substr(leyka_options()->opt('org_bank_corr_account'), $i, 1), $quittance_html
            );
        }

        die($quittance_html);
    }

    /** Quittance don't use any specific redirects, so this method is empty. */
    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }
    
    /** Quittance don't have some form data to send to the gateway site */
    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {
        return $form_data_vars;
    }

    /** Quittance don't have any of gateway service calls */
    public function _handle_service_calls($call_type = '') {}

    public function get_gateway_response_formatted(Leyka_Donation $donation) {
        return array();
    }

    /** Quittance don't use any specific fields, so this method is empty. */
    public function log_gateway_fields($donation_id) {
    }
}

class Leyka_Bank_Order extends Leyka_Payment_Method {

    private $_quittance_html = '';

    protected static $_instance;

    protected function _set_attributes() {

        $this->_quittance_html = file_get_contents(LEYKA_PLUGIN_DIR.'gateways/quittance/bank_order.html');

        $this->_id = 'bank_order';
        $this->_gateway_id = 'quittance';

        $this->_label_backend = __('Bank order quittance', 'leyka');
        $this->_label = __('Bank order quittance', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/quittance/icons/sber_s.png',
        ));

        $this->_submit_label = __('Get bank order quittance', 'leyka');

        $this->_supported_currencies = array('rur');

        $this->_default_currency = 'rur';
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('Bank order payment allows you to make a donation through any bank. You can print out a bank order paper and bring it to the bank to make a payment.', 'leyka'),
                'title' => __('Bank order payment description', 'leyka'),
                'description' => __('Please, enter Bank order description that will be shown to the donor when this payment method will be selected to make a donation.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(),
            ),
        );
    }

    function get_quittance_html() {
        return $this->_quittance_html;
    }
}

function leyka_add_gateway_quittance() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Quittance_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_quittance');