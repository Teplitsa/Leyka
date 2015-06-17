<?php
/**
 * Leyka_Text_Gateway class
 */

class Leyka_Text_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'text';
        $this->_title = __('Text information', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) { // Create Gateway options, if needed
            return;
        }
    }

    protected function _initialize_pm_list() {

        if(empty($this->_payment_methods['text_box'])) {
            $this->_payment_methods['text_box'] = Leyka_Text_Box::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
        remove_action('leyka_payment_form_submission-'.$this->id, array($this, 'process_form_default'), 100);
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {
        return $form_data_vars;
    }

    public function log_gateway_fields($donation_id) {
    }

    public function _handle_service_calls($call_type = '') {
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {
        return array();
    }

} // class end


class Leyka_Text_Box extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'text_box';
        $this->_gateway_id = 'text';

        $this->_label_backend = __('Additional ways to donate', 'leyka');
        $this->_label = __('Additional ways to donate', 'leyka');

        // The description won't be setted here - it requires the PM option being configured at this time (which is not)

        $this->_support_global_fields = false;

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/text/icons/box.png',
        ));

        $this->_supported_currencies[] = 'rur';

        $this->_default_currency = 'rur';
    }

    protected function _set_dynamic_attributes() {

        $this->_custom_fields = array(
            'box_details' => apply_filters('leyka_the_content', leyka_options()->opt_safe('text_box_details')),
        );
    }

    protected function _set_options_defaults() {

        if($this->_options){
            return;
        }

        $this->_options = array(
            $this->full_id.'_description' => array(
                'type' => 'html',
                'default' => __('With this ways you can make your donation.', 'leyka'),
                'title' => __('Comment', 'leyka'),
                'description' => __('Please, set a text of comment to describe an additional ways to donate.', 'leyka'),
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            ),
            'text_box_details' => array(
                'type' => 'html',
                'default' => '',
                'title' => __('Ways to donate', 'leyka'),
                'description' => __('Please, set a text to describe an additional ways to donate.', 'leyka'),
                'required' => 1,
                'validation_rules' => array(), // List of regexp?..
            )
        );
    }
}

function leyka_add_gateway_text() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Text_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_text');