<?php if( !defined('WPINC') ) { die; }
/**
 * Leyka_Quittance_Gateway class
 */

class Leyka_Quittance_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            'quittance_redirect_page' => [
                'type' => 'select',
                'default' => leyka_get_default_success_page(),
                'title' => __('Page to redirect a donor after a donation', 'leyka'),
                'comment' => __('Select a page for donor to redirect to after he has acquired a quittance.', 'leyka'),
                'list_entries' => leyka_get_posts_list(['page']),
            ],
        ];

    }

    protected function _set_attributes() {

        $this->_id = 'quittance';
        $this->_title = __('Quittances', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('Bank order payment allows you to make a donation through any bank. You can print out a bank order paper and bring it to the bank to make a payment.', 'leyka'),
            $this->_id
        );

//        $this->_docs_link = '//leyka.org/docs/nastrojka-lejki/'; // No manual for the Quittance ATM
        $this->_registration_link = '';

        $this->_min_commission = 2.1;
        $this->_receiver_types = ['legal',];

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['bank_order'])) {
            $this->_payment_methods['bank_order'] = Leyka_Bank_Order::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        if(leyka()->payment_form_has_errors()) {
            return;
        }

        load_textdomain('leyka', LEYKA_PLUGIN_DIR.'lang/leyka-'.get_locale().'.mo'); // Localize a quittance first

        header('HTTP/1.1 200 OK');
        header('Content-Type: text/html; charset=utf-8');

        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);
        leyka_remembered_data('template_id', $campaign->template);

        $quittance_html = str_replace(
            apply_filters('leyka_quittance_placeholders_list', [
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
            ], $pm_id, $donation_id, $form_data),
            apply_filters('leyka_quittance_placeholders_values', [ // Form field values
                '#BACK_TO_DONATION_FORM_TEXT#' => __('Return to the donation form', 'leyka'),
                '#PRINT_THE_QUITTANCE_TEXT#' => __('Print the quittance', 'leyka'),
                '#QUITTANCE_RECEIVED_TEXT#' => __("OK, I've received the quittance", 'leyka'),
                '#SUCCESS_URL#' => get_permalink(leyka_options()->opt('quittance_redirect_page')),
                '#PAYMENT_COMMENT#' => $campaign->payment_title." (№ $donation_id)",
                '#PAYER_NAME#' => $form_data['leyka_donor_name'],
                '#RECEIVER_NAME#' => leyka_options()->opt('org_full_name'),
                '#SUM#' => (int)$form_data['leyka_donation_amount'],
                '#INN#' => leyka_options()->opt('org_inn'),
                '#KPP#' => leyka_options()->opt('org_kpp'),
                '#ACC#' => leyka_options()->opt('org_bank_account'),
                '#RECEIVER_BANK_NAME#' => leyka_options()->opt('org_bank_name'),
                '#BIC#' => leyka_options()->opt('org_bank_bic'),
                '#CORR#' => leyka_options()->opt('org_bank_corr_account'),
            ], $pm_id, $donation_id, $form_data),
            $this->_payment_methods[$pm_id]->get_quittance_html()
        );

        for($i = 0; $i < 10; $i++) {
            $quittance_html = str_replace("#INN_$i#", mb_substr(leyka_options()->opt('org_inn'), $i, 1), $quittance_html);
        }
        for($i = 0; $i < 20; $i++) {
            $quittance_html = str_replace(
                "#ACC_$i#", mb_substr(leyka_options()->opt('org_bank_account'), $i, 1), $quittance_html
            );
        }
        for($i = 0; $i<9; $i++) {
            $quittance_html = str_replace(
                "#BIC_$i#", mb_substr(leyka_options()->opt('org_bank_bic'), $i, 1), $quittance_html
            );
        }
        for($i = 0; $i < 20; $i++) {
            $quittance_html = str_replace(
                "#CORR_$i#", mb_substr(leyka_options()->opt('org_bank_corr_account'), $i, 1), $quittance_html
            );
        }

        do_action('leyka_before_quittance_output', $pm_id, $donation_id, $form_data);

        die( wp_kses_post( $quittance_html ) );

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return get_option('permalink_structure') ? home_url('/leyka-process-donation') : home_url('?page=leyka-process-donation');
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {
        return $form_data;
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {
        return [];
    }

}

class Leyka_Bank_Order extends Leyka_Payment_Method {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'bank_order';
        $this->_gateway_id = 'quittance';
        $this->_category = 'offline';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('Bank order payment allows you to make a donation through any bank. You can print out a bank order paper and bring it to the bank to make a payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank order quittance', 'leyka');
        $this->_label = __('Bank order quittance', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/sber.svg',
        ]);

        $this->_submit_label = __('Get bank order quittance', 'leyka');

        $this->_supported_currencies = ['rub',];
        $this->_default_currency = 'rub';

        $this->_ajax_without_form_submission = true;

    }

    public function get_quittance_html() {
        return leyka_get_file_content( LEYKA_PLUGIN_DIR . 'gateways/quittance/bank_order.html' );
    }

}

function leyka_add_gateway_quittance() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Quittance_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_quittance');