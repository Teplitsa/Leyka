<?php if( !defined('WPINC') ) die;

class Leyka_Options_Controller extends Leyka_Singleton {

    protected static $_instance = null;
    protected static $_options_meta = [];

    protected $_options = [];
    protected static $_field_types = [
        'text', 'textarea', 'number', 'static_text', 'html', 'rich_html', 'select', 'multi_select', 'radio', 'checkbox',
        'multi_checkbox', 'legend', 'file', 'colorpicker', 'campaign_select', 'static_text',
    ];

    protected $_templates_common_options = [
        'donation_sum_field_type', 'recurring_donation_benefits_text', 'scale_widget_place', 'donation_submit_text',
        'donations_history_under_forms', 'show_success_widget_on_success', 'show_donation_comment_field',
        'donation_comment_max_length', 'show_campaign_sharing', 'show_failure_widget_on_failure', 'do_not_display_donation_form',
    ];

    protected $_template_options = [];

    protected function __construct() {

        require_once(LEYKA_PLUGIN_DIR.'inc/options-meta/leyka-class-options-meta-controller.php');

        $init_options_group = apply_filters('leyka_init_options_meta_group', ['main', 'templates',]);
        self::$_options_meta = apply_filters('leyka_init_options_meta',
            Leyka_Options_Meta_Controller::get_instance()->get_options_meta($init_options_group),
            $init_options_group
        );

        $this->_add_options_alt_ids();
        $this->_modify_options_values();

        $this->add_template_options();

    }

    /** Service function to add filters for options alternative IDs. */
    protected function _add_options_alt_ids() {
        add_filter('leyka_option_id-main_currency', function(){ // 'main_currency' was renamed to 'currency_main' in v3.11
            return 'currency_main';
        });
    }

    protected function _modify_options_values() {

        add_filter('leyka_option_value-commission', function($value){
            return $value ? : [];
        });

        // Additional Donation form fields Library:
        add_filter('leyka_option_value-additional_donation_form_fields_library', function($value){
            return is_array($value) ? $value : [];
        });

        add_filter('leyka_new_option_value-additional_donation_form_fields_library', function($option_value){
            return is_array($option_value) ? $option_value : [];
        });
        // Additional Donation form fields Library - END

        $currencies = array_keys(leyka_get_main_currencies_full_info());

        foreach($currencies as $currency_id) {

            add_filter('leyka_option_default-payments_single_amounts_options_'.$currency_id, function($option_value) use ($currency_id){
                return leyka_get_fixed_payments_amounts_options($currency_id);
            });

            add_filter('leyka_option_default-payments_recurring_amounts_options_'.$currency_id, function($option_value) use ($currency_id){
                return leyka_get_fixed_payments_amounts_options($currency_id);
            });

        }

        add_action('leyka_set_currency_main_option_value', function (){

            leyka_refresh_currencies_rates();
            leyka_actualize_campaigns_money_values();
            leyka_clear_dashboard_cache();

        });

        // If Country option value changes, clear active PM lists:
        add_action('leyka_set_receiver_country_option_value', function($option_value){

            self::set_option_value('pm_order', '');
            self::set_option_value('pm_available', '');

        });

    }

    protected function _get_filtered_option_id($option_id) {

        $option_id = apply_filters('leyka_option_id-'.$option_id, str_replace('leyka_', '', $option_id));

        return apply_filters('leyka_option_id', $option_id);

    }

    public function is_standard_field_type($type) {
        return in_array($type, self::$_field_types);
    }

    /**
     * A service method to retrieve a plugin option value when the plugin is just being initialized,
     * and it can't properly load options metadata yet.
     *
     * @param $option_id string
     * @package $use_option_value_filters boolean
     * @return mixed
     */
    public static function get_option_value($option_id, $use_option_value_filters = true) {;

        // Don't use $this->_get_filtered_option_id() here:
        $option_id = stripos($option_id, 'leyka_') === false ? 'leyka_'.$option_id : $option_id;
        $value = get_option($option_id);

        if($use_option_value_filters) {
            $value = apply_filters(
                'leyka_option_value-'.$option_id,
                apply_filters('leyka_option_value', $value, $option_id)
            );
        }

        return $value;

    }

    /**
     * A service method to set a plugin option value when the plugin is just being initialized,
     * and it can't properly load options metadata yet.
     *
     * @param $option_id string
     * @return bool
     */
    public static function set_option_value($option_id, $value) {

        $option_id = mb_stripos($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        $value = apply_filters('leyka_new_option_value', $value, $option_id);
        $value = apply_filters('leyka_new_option_value-'.str_replace('leyka_', '', $option_id), $value);

        $updated = update_option($option_id, $value);

        do_action('leyka_set_option_value', $option_id, $value);
        do_action("leyka_set_{$option_id}_option_value", $value);

        return $updated;

    }

    /**
     * A service function to initialize options group metadata if group-specific keywords found in option ID.
     *
     * @param string $option_id
     */
    protected function _initialize_options_group_meta($option_id) {

        $new_options_group_meta = [];

        if(stripos($option_id, 'org_') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('org'); 
        } else if(stripos($option_id, 'person_') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('person');
        } else if(stripos($option_id, 'payments') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('payments');
        } else if(stripos($option_id, 'currency') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('currency');
        } else if(stripos($option_id, 'cryptocurrencies') !== false) {
                $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('cryptocurrencies');
        } else if(stripos($option_id, 'email') !== false || stripos($option_id, 'notify') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('emails');
        } else if(
            stripos($option_id, 'template') !== false
            || stripos($option_id, 'display') !== false
            || stripos($option_id, 'show') !== false
            || stripos($option_id, 'widget') !== false
            || stripos($option_id, 'revo') !== false
        ) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('templates');
        } else if(stripos($option_id, '_ua') !== false || stripos($option_id, '_gtm') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('analytics');
        } else if(stripos($option_id, 'terms') !== false) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('terms');
        } else if(
            stripos($option_id, 'admin') !== false
            || stripos($option_id, 'plugin') !== false
            || stripos($option_id, 'paltform') !== false
        ) {
            $new_options_group_meta = Leyka_Options_Meta_Controller::get_instance()->get_options_meta('admin');
        }

        self::$_options_meta = array_merge(self::$_options_meta, $new_options_group_meta);

    }

    /**
     * A service method to load the plugin option metadata to the controller's cache array.
     * 
     * @param $option_id string
     * @param $load_value bool Whether to load the option value from the DB. Sometimes it's not needed.
     * @return bool True/false of the initailization.
     */
    protected function _intialize_option($option_id, $load_value = false) {

        $option_id = $this->_get_filtered_option_id($option_id);

        if(empty(self::$_options_meta[$option_id])) { // Initialize option group metadata, if needed
            $this->_initialize_options_group_meta($option_id);
        }

        if(empty(self::$_options_meta[$option_id]) && empty($this->_options[$option_id])) {

            do_action('leyka_add_custom_option', $option_id, $this);

            if(empty($this->_options[$option_id])) {
                return false;
            }

        }

        if(empty($this->_options[$option_id])) {
            $this->_options[$option_id] = self::$_options_meta[$option_id];
        }

        if( !!$load_value ) {
            $this->_initialize_value($option_id);
        }

        return true;

    }

    /**
     * A service method to load option value from DB to the controller cache array.
     *
     * @param $option_id string
     */
    protected function _initialize_value($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        if( !isset($this->_options[$option_id]['value']) ) {

            $this->_options[$option_id]['value'] = get_option("leyka_$option_id");

            // Option is not set, use default value from options meta:
            if($this->_options[$option_id]['value'] === false && !empty(self::$_options_meta[$option_id])) {
                $this->_options[$option_id]['value'] = empty(self::$_options_meta[$option_id]['default']) ?
                    '' : self::$_options_meta[$option_id]['default'];
            }

        }

        if(
            $this->_options[$option_id]['value']
            && ($this->_options[$option_id]['type'] === 'html' || $this->_options[$option_id]['type'] === 'rich_html')
        ) {

            $this->_options[$option_id]['value'] =
                is_array($this->_options[$option_id]['value']) &&
                isset($this->_options[$option_id]['value']['value']) ?
                    html_entity_decode(stripslashes($this->_options[$option_id]['value']['value'])) :
                    html_entity_decode(stripslashes((string)$this->_options[$option_id]['value']));

        }

    }

    /**
     * Get all currently initialized options names/IDs as array.
     *
     * @return array
     */
    public function get_options_names() {
        return array_unique(array_merge(array_keys(self::$_options_meta), array_keys($this->_options)));
    }

    /** 
     * @param string $option_id
     * @return mixed
     */
    public function get_value($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        if( !$this->_intialize_option($option_id, true) ) {
            return null;
        }

        if(in_array($this->_options[$option_id]['type'], ['text', 'html', 'rich_html'])) {

            $this->_options[$option_id]['value'] = is_array($this->_options[$option_id]['value']) ?
                $this->_options[$option_id]['value'] :
                trim($this->_options[$option_id]['value']);

        } else if(stripos($this->_options[$option_id]['type'], 'multi_') !== false && !$this->_options[$option_id]['value']) {
            $this->_options[$option_id]['value'] = [];
        }

        $this->_options[$option_id]['value'] = apply_filters(
            'leyka_option_value-'.$option_id,
            apply_filters('leyka_option_value', $this->_options[$option_id]['value'], $option_id)
        );

        return $this->_options[$option_id]['value'];

    }

    public function add_option($option_id, $type, $params) {

        $option_id = stripos($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        if( !in_array($type, self::$_field_types) && stripos($type, 'custom_') === false ) {
            return false;
        }

        if( !empty($params['type']) ) { // Just in case
            unset($params['type']);
        }

        $value_saved = $type === 'static_text' ? trim($params['value']) : maybe_unserialize(get_option($option_id));

        if(empty($params['value']) && $value_saved !== false) {
            $params['value'] = $value_saved;
        } else if(empty($params['value']) && !empty($params['default'])) {
            $params['value'] = $params['default'];
        }

        /** @var $params array Full option format description in the beginning of leyka-options-meta.php */
        $params = array_merge([
            'type' => $type,
            'value' => '',
            'default' => '',
            'title' => $option_id,
            'description' => '',
            'required' => false,
            'placeholder' => '',
            'comment' => '',
            'length' => '',
            'list_entries' => [],
            'validation_rules' => [],
        ], $params);

        $option_added = $value_saved !== false ? true : add_option($option_id, $params['value']);

        if($option_added) {
            $this->_options[str_replace('leyka_', '', $option_id)] = $params;
        }

        return $option_added;

    }

    public function delete_option($option_id) {

        $option_id = stripos($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        $this->_intialize_option($option_id);

        $option_deleted = delete_option($option_id);

        if($option_deleted) {
            unset($this->_options[str_replace('leyka_', '', $option_id)]);
        }

        return $option_deleted;

    }

    public function option_exists($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id);

        return isset($this->_options[$option_id]);

    }

    /** 
     * @param mixed $option_id Option name, or assoc array of (option_name => new_value) pairs.
     * @param mixed $option_value If $option_name is a string, it's the new value; otherwise not used.
     * @return bool
     */
    public function set_value($option_id, $option_value = null) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id, true);

        if(in_array($this->_options[$option_id]['type'], ['text', 'html', 'rich_html'])) {
            $this->_options[$option_id]['value'] = trim($this->_options[$option_id]['value']);
        }

        $option_value = apply_filters('leyka_new_option_value', $option_value, $option_id);

        if(
            $this->option_exists($option_id) &&
            $this->_options[$option_id]['value'] !== $option_value &&
            $this->_validate_option($option_id, $option_value)
        ) {

            $old_value = $this->_options[$option_id]['value']; // Rollback to it if option update fails
            $this->_options[$option_id]['value'] = $option_value;

            $updated = update_option('leyka_'.$option_id, $option_value);
            if( !$updated ) {
                $this->_options[$option_id]['value'] = $old_value;
            }

            do_action('leyka_set_option_value', $option_id, $option_value);
            do_action("leyka_set_{$option_id}_option_value", $option_value);

            return $updated;

        } else {
            return false;
        }

    }

    public function opt_template($option_id, $template_id = false) {

        $option_id = $this->_get_filtered_option_id($option_id);
        
        $value = false;
        if(leyka_options()->is_template_option($option_id)) {

            $template_id = $template_id ? : leyka_remembered_data('template_id');
            $template_id = $template_id === 'default' ? $this->get_value('donation_form_template') : $template_id;

            if( !$template_id ) {

                $current_template_data = leyka_get_current_template_data();
                $template_id = empty($current_template_data['id']) ?
                    $this->get_value('donation_form_template') : $current_template_data['id'];

            }

            if($template_id) {
                $value = leyka_options()->get_template_option($option_id, $template_id);
            }

        }

        return $value === false ? $this->opt_safe($option_id) : $value;

    }

    public function opt($option_id, $new_value = null) {
        return $new_value === null ? $this->get_value($option_id) : $this->set_value($option_id, $new_value);
    }

    public function opt_safe($option_id) {

        $value = $this->get_value($option_id);

        return $value ? : $this->get_default_of($option_id);

    }

    /**
     * @param $option_id string
     * @param $value mixed
     * @return boolean True if given option value is valid, false otherwise (or if option doesn't exists).
     */
    protected function _validate_option($option_id, $value = null) {

        $option_id = $this->_get_filtered_option_id($option_id);
        $value = $value === NULL ? $this->get_value($option_id) : $value;

        foreach($this->get_validation_rules($option_id) as $rule_regexp => $rule_invalid_message) {
            if( !preg_match($rule_regexp, $value) ) {
                return false;
            }
        }

        return true;

    }

    /**
     * @param $option_id string
     * @return array An array of option validation rules.
     */
    public function get_validation_rules($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id, true);

        $validation_rules = empty($this->_options[$option_id]['validation_rules']) ?
            [] : $this->_options[$option_id]['validation_rules'];

        return apply_filters('leyka_option_validation_rules-'.$option_id, $validation_rules);

    }

    /**
     * @param $option_id string
     * @param $value mixed
     * @return array
     */
    public function get_validation_errors($option_id, $value = false) {

        $option_id = $this->_get_filtered_option_id($option_id);
        $value = $value === false ? $this->opt_safe($option_id) : $value;

        if( !$this->option_exists($option_id)) {
            return [];
        }

        $errors = [];

        if($this->is_required($option_id) && !$value) {
            $errors[] = __('The field value is required', 'leyka');
        }

        foreach($this->get_validation_rules($option_id) as $rule_regexp => $rule_invalid_message) {
            if( !preg_match($rule_regexp, $value) ) {
                $errors[] = apply_filters('leyka_option_invalid_message', $rule_invalid_message, $rule_regexp, $option_id);
            }
        }

        return apply_filters('leyka_option_validation_errors-'.$option_id, $errors, $value);

    }

    public function get_info_of($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id, true);

        $filtered_option_metadata = [
            'title' => apply_filters('leyka_option_title-'.$option_id, $this->_options[$option_id]['title']),
            'type' => apply_filters('leyka_option_type-'.$option_id, $this->_options[$option_id]['type']),
            'required' => apply_filters(
                'leyka_option_required-'.$option_id,
                empty($this->_options[$option_id]['required']) ? false : !!$this->_options[$option_id]['required']
            ),
            'default' => apply_filters(
                'leyka_option_default-'.$option_id,
                empty($option_data['default']) ? '' : $option_data['default']
            ),
        ];

        $this->_options[$option_id] = array_merge($this->_options[$option_id], $filtered_option_metadata);

        return apply_filters('leyka_option_info-'.$option_id, $this->_options[$option_id]);

    }

    public function get_title_of($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id, true);

        return apply_filters('leyka_option_title-'.$option_id, $this->_options[$option_id]['title']);

    }

    public function get_default_of($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['default'];

    }

    public function get_type_of($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['type'];

    }

    public function is_required($option_id) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['required'];

    }

    public function is_valid($option_id, $value = false) {

        $option_id = $this->_get_filtered_option_id($option_id);

        $this->_intialize_option($option_id, true);
        $value = $value === false ? $this->opt_safe($option_id) : $value;
        $option_valid = !(
            ($this->is_required($option_id) && !$value) || ($value && !$this->_validate_option($option_id, $value))
        );

        return apply_filters('leyka_option_valid-'.$option_id, $option_valid, $value);

    }
    
    /**
     * @return array
     */
    public function get_all_options_keys() {
        return array_keys(self::$_options_meta);
    }

    /**
     * @param $option_name
     * @return bool
     */
    public function is_template_option($option_name) {

        foreach($this->_template_options as $prefix => $options) {
            if(in_array($option_name, $options)) {
                return true;
            }
        }

        return false;

    }

    /**
     * @return string
     */
    public function get_tab_option_full_name($prefix, $option) {
        return $prefix.'_'.$option;
    }

    /**
     * @param $template_id string
     * @return string
     */
    protected function _get_template_options_prefix($template_id) {
        return 'template_options_'.$template_id;
    }

    public function add_template_options() {

        // Initialize the template options array (must be [template_id] => [] for each template ):
        $custom_templates = glob(get_template_directory().'/leyka-template-*.php');
        $custom_templates = $custom_templates ? $custom_templates : [];

        $this->_template_options = apply_filters(
            'leyka_templates_list',
            array_merge($custom_templates, glob(LEYKA_PLUGIN_DIR.'templates/leyka-template-*.php'))
        );

        if( !$this->_template_options ) {
            $this->_template_options = [];
        }

        foreach($this->_template_options as $key => $template_file_addr) {

            $template_id = str_replace(['leyka-template-', '.php'], '', basename($template_file_addr));
            $this->_template_options[$template_id] = [];

            unset($this->_template_options[$key]);

        }

        // Fill the templates options array:
        foreach($this->_template_options as $template_id => $options) {

            $this->_template_options[$template_id] = array_merge($options, $this->_templates_common_options);

            foreach($this->_template_options[$template_id] as $option) {

                $tab_option_full_name = $this->get_tab_option_full_name(
                    $this->_get_template_options_prefix($template_id),
                    $option
                );

                self::$_options_meta[$tab_option_full_name] = self::$_options_meta[$option];
                $this->_intialize_option($tab_option_full_name);

            }

        }

    }

    /**
     * @param $common_option
     * @param $template_id
     * @return mixed
     */
    public function get_template_option($common_option, $template_id) {

        $option = $this->get_tab_option_full_name($this->_get_template_options_prefix($template_id), $common_option);
        $value = self::get_option_value($option);

        if($value === false) {
            foreach($this->_template_options as $template_id => $options) {

                $prefix = $this->_get_template_options_prefix($template_id);

                if(stripos($option, $prefix) === 0) {
                    $value = $this->opt_safe(str_replace($prefix.'_', '', $option));
                }

            }
        }

        return $value;

    }

    /**
     * Test if given multi-checkbox option has given value checked.
     *
     * @param $option_id string An ID of option to check. Must have "multi_checkbox" type.
     * @param $value_to_check string Option value to check.
     * @return boolean|null Returns true if given value is checked, false if not, or NULL if wrong option ID given.
     */
    public function is_multi_value_checked($option_id, $value_to_check) {

        $option_id = $this->_get_filtered_option_id($option_id);

        if( !$this->option_exists($option_id) || $this->get_type_of($option_id) !== 'multi_checkbox' ) {
            return NULL;
        }

        $check_list = $this->opt($option_id);

        return is_array($check_list) && in_array($value_to_check, $check_list);

    }

}

/**
 * @return Leyka_Options_Controller
 */
function leyka_options() {
    return Leyka_Options_Controller::get_instance();
}