<?php
/**
 * Leyka_Text_Gateway class
 */

class Leyka_Text_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'text';
        $this->_title = __('Text information', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('You can set a custom text information to display as one of the payment methods for donors.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '';
        $this->_registration_link = '';

        $this->_min_commission = 0;
        $this->_receiver_types = ['legal', 'physical',];

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['text_box'])) {
            $this->_payment_methods['text_box'] = Leyka_Text_Box::get_instance();
        }
    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {
        remove_action('leyka_payment_form_submission-'.$this->id, [$this, 'process_form_default'], 100);
    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }

    public function submission_form_data($form_data, $pm_id, $donation_id) {
        return $form_data;
    }

    public function get_gateway_response_formatted(Leyka_Donation_Base $donation) {
        return apply_filters('leyka_donation_gateway_response', [], $donation);
    }

}


class Leyka_Text_Box extends Leyka_Payment_Method {

    protected static $_instance = null;

    public function _set_attributes() {

        $this->_id = 'text_box';
        $this->_gateway_id = 'text';
        $this->_category = 'misc';

        $this->_description = apply_filters('leyka_pm_description', '', $this->_id, $this->_gateway_id, $this->_category);

        $this->_label_backend = __('Additional ways to donate', 'leyka');
        $this->_label = __('Additional ways to donate', 'leyka');

        $this->_support_global_fields = false;

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_'.$this->_id, [
            LEYKA_PLUGIN_BASE_URL.'gateways/text/icons/pm-text.svg',
        ]);

        $this->_supported_currencies = ['rub', 'uah', 'byn',];
        $this->_default_currency = 'rub';

        $this->_processing_type = 'static'; // We should display custom data instead of the donors' data & submit step

    }

    protected function _set_dynamic_attributes() {
        $this->_custom_fields = [
            'box_details' => apply_filters('leyka_the_content', leyka_options()->opt_safe('text_box_details')),
        ];
    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = [
            $this->full_id.'_description' => [
                'type' => 'html',
                'default' => __('With this ways you can make your donation.', 'leyka'),
                'title' => __('Comment', 'leyka'),
                'comment' => __('Please, set a text of comment to describe an additional ways to donate.', 'leyka'),
                'required' => false,
            ],
            'text_box_details' => [
                'type' => 'html',
                'title' => __('Ways to donate', 'leyka'),
                'description' => __('Please, set a text to describe an additional ways to donate.', 'leyka'),
                'required' => true,
            ]
        ];

    }

    public function display_static_data() {
        echo wp_kses_post(apply_filters('leyka_the_content', leyka_options()->opt_safe('text_box_details')));
    }

}

function leyka_add_gateway_text() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka_add_gateway(Leyka_Text_Gateway::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_gateway_text');

// Remove Text PM from payment forms if the text isn't set:
function leyka_remove_text_pm_if_empty($pm_list) {

    if(is_admin()) {
        return $pm_list;
    }

    foreach($pm_list as $index => $pm) { /** @var $pm Leyka_Payment_Method */
        if($pm->gateway_id === 'text' && empty($pm->custom_fields['box_details'])) {
            unset($pm_list[$index]);
        }
    }

    return $pm_list;

}
add_filter('leyka_active_pm_list', 'leyka_remove_text_pm_if_empty');