<?php
/**
 * Leyka_Text_Gateway class
 */

class Leyka_Text_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_gateway_attributes() {

        $this->_id = 'text';
        $this->_title = __('Text information', 'leyka');
    }

    protected function _set_options_defaults() {

        if($this->_options) // Create Gateway options, if needed
            return;

        $this->_options = array();
    }

    protected function _initialize_pm_list() {

        // Instantiate and save each of PM objects, if needed:
        if(empty($this->_payment_methods['text_box'])) {
            $this->_payment_methods['text_box'] = Leyka_Text_Box::get_instance();
            $this->_payment_methods['text_box']->initialize_pm_options();
            $this->_payment_methods['text_box']->save_settings();
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

    /** @var $_instance Leyka_Yandex_Card */
    protected static $_instance = null;

    final protected function __clone() {}

    public final static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct(array $params = array()) {

        if(static::$_instance) /** We can't make a public __construct() to private */ {
            return static::$_instance;
        }

        $this->initialize_pm_options();

        $this->_id = empty($params['id']) ? 'text_box' : $params['id'];

        $this->_label = empty($params['label']) ? __('Additional ways to donate', 'leyka') : $params['label'];

        $this->_description = empty($params['desc']) ?
            leyka_options()->opt_safe('text_box_description') : $params['desc'];

        $this->_gateway_id = 'text';

//        $this->_active = !empty($params['active']) ? 1 : 0;
        $pm_available = leyka_options()->opt('pm_available');
        $this->_active = is_array($pm_available) ? (int)in_array($this->_gateway_id.'-'.$this->_id, $pm_available) : 0;

        $this->_support_global_fields = isset($params['has_global_fields']) ? $params['has_global_fields'] : false;

        $details = apply_filters('leyka_the_content', leyka_options()->opt_safe('text_box_details'));
        $this->_custom_fields = empty($params['custom_fields']) ? array('box_deatails' => $details) : (array)$params['custom_fields'];

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'gateways/text/icons/box.png',
        ));

        $this->_submit_label = empty($params['submit_label']) ?
            __('Donate', 'leyka') : $params['submit_label'];

        $this->_default_currency = empty($params['default_currency']) ?
            array('rur', 'usd', 'eur') : $params['default_currency'];

        $this->_default_currency = empty($params['default_currency']) ? 'rur' : $params['default_currency'];

        static::$_instance = $this;

        return static::$_instance;
    }

    protected function _set_pm_options_defaults() {

        if($this->_options)
            return;

        $this->_options = array(
            'text_box_description' => array(
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
                'required' => 0,
                'validation_rules' => array(), // List of regexp?..
            )
        );
    }
}

function leyka_add_gateway_text() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_gateway(Leyka_Text_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_text', 40);