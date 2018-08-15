<?php if( !defined('WPINC') ) die;

class Leyka_Options_Controller {

    private static $_instance = null;
    protected static $_options_meta = array();

    protected $_options = array();
    protected $_field_types = array('text', 'html', 'rich_html', 'select', 'radio', 'checkbox', 'multi_checkbox');

    public static function instance() {

        if( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    protected function __construct() {
        require_once(LEYKA_PLUGIN_DIR.'inc/leyka-options-meta.php');
    }

    /**
     * A service method to retrieve the plugin option value when it's just being initialized, and can't do
     * proper options metadata loading yet.
     */
    public static function get_option_value($option_name) {

        $option_name = stristr($option_name, 'leyka_') !== false ? $option_name : 'leyka_'.$option_name;

        return apply_filters('leyka_option_value', get_option($option_name), $option_name);

    }

    /**
     * A service method to load the plugin option metadata to the controller's cache array.
     *
     * @param $option_name string
     * @param $load_value bool Whether to load the option value from the DB. Sometimes it's not needed.
     * @return bool True/false of the initailization.
     */
    protected function _intialize_option($option_name, $load_value = false) {

        $option_name = str_replace('leyka_', '', $option_name);

        if(empty(self::$_options_meta[$option_name]) && empty($this->_options[$option_name])) {

            do_action('leyka_add_custom_option', $option_name);

            if(empty($this->_options[$option_name])) {
                return false;
            }

        }

        if(empty($this->_options[$option_name])) {
            $this->_options[$option_name] = self::$_options_meta[$option_name];
        }

        if( !!$load_value ) {
            $this->_initialize_value($option_name);
        }

        return true;

    }

    /**
     * A service method to load the option value from the DB to the controller's cache array.
     * @param $option_name string
     */
    protected function _initialize_value($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        if( !isset($this->_options[$option_name]['value']) ) {

            $this->_options[$option_name]['value'] = get_option("leyka_$option_name");

            // Option is not set, use default value from meta:
            if($this->_options[$option_name]['value'] === false && !empty(self::$_options_meta[$option_name])) {
                $this->_options[$option_name]['value'] = empty(self::$_options_meta[$option_name]['default']) ?
                    '' : self::$_options_meta[$option_name]['default'];
            }
        }

        if(
            $this->_options[$option_name]['value'] && ($this->_options[$option_name]['type'] === 'html' ||
            $this->_options[$option_name]['type'] === 'rich_html')
        ) {

            $this->_options[$option_name]['value'] =
                is_array($this->_options[$option_name]['value']) &&
                isset($this->_options[$option_name]['value']['value']) ?
                    html_entity_decode(stripslashes($this->_options[$option_name]['value']['value'])) :
                    html_entity_decode(stripslashes((string)$this->_options[$option_name]['value']));
        }
    }

    public function get_options_names() {
        return array_unique(array_merge(array_keys(self::$_options_meta), array_keys($this->_options)));
    }

    /** 
     * @param string $option_name
     * @return mixed
     */
    public function get_value($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);
        if( !$this->_intialize_option($option_name, true) ) {
            return false;
        }

        if(in_array($this->_options[$option_name]['type'], array('text', 'html', 'rich_html'))) {
            $this->_options[$option_name]['value'] = trim($this->_options[$option_name]['value']);
        }

        return apply_filters('leyka_option_value', $this->_options[$option_name]['value'], $option_name);

    }

    public function add_option($name, $type, $params) {

        $name = stristr($name, 'leyka_') !== false ? $name : 'leyka_'.$name;

        if( !in_array($type, $this->_field_types) ) {
            return false;
        }
        if( !empty($params['type']) ) { // Just in case
            unset($params['type']);
        }

        $value_saved = maybe_unserialize(get_option($name));

        if(empty($params['value']) && $value_saved !== false) {
            $params['value'] = $value_saved;
        } else if(empty($params['value']) && !empty($params['default'])) {
            $params['value'] = $params['default'];
        }

        $params = array_merge(array(
            'type' => $type, // html, rich_html, select, radio, checkbox, multi_checkbox  
            'value' => '',
            'default' => '',
            'title' => $name,
            'description' => '',
            'required' => false,
            'placeholder' => '', // For text fields
            'length' => '', // For text fields
            'list_entries' => array(), // For select, radio & checkbox fields
            'validation_rules' => array(), // List of regexp?..
        ), $params);

        $option_added = $value_saved !== false ? true : add_option($name, $params['value']);

        if($option_added) {
            $this->_options[str_replace('leyka_', '', $name)] = $params;
        }

        return $option_added;

    }

    public function delete_option($name) {

        $name = stristr($name, 'leyka_') !== false ? $name : 'leyka_'.$name;

        $this->_intialize_option($name);

        $option_deleted = delete_option($name);

        if($option_deleted) {
            unset($this->_options[str_replace('leyka_', '', $name)]);
        }

        return $option_deleted;

    }

    public function option_exists($name) {

        $this->_intialize_option($name);

        return isset($this->_options[str_replace('leyka_', '', $name)]);

    }

    /** 
     * @param mixed $option_name Option name, or assoc array of (option_name => new_value) pairs.
     * @param mixed $option_value If $option_name is a string, it's the new value; otherwise not used.
     * @return bool
     */
    public function set_value($option_name, $option_value = null) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name, true);

        if(in_array($this->_options[$option_name]['type'], array('text', 'html', 'rich_html'))) {
            $this->_options[$option_name]['value'] = trim($this->_options[$option_name]['value']);
        }

        if(
            $this->option_exists($option_name) &&
            $this->_options[$option_name]['value'] !== $option_value &&
            $this->_validate_option($option_name, $option_value)
        ) {

            $old_value = $this->_options[$option_name]['value']; // Rollback to it if option update fails
            $this->_options[$option_name]['value'] = $option_value;

            $updated = update_option('leyka_'.$option_name, $option_value);
            if( !$updated ) {
                $this->_options[$option_name]['value'] = $old_value;
            }

            return $updated;

        } else {
            return false;
        }

    }

    public function opt($option_name, $new_value = null) {
        return $new_value === null ? $this->get_value($option_name) : $this->set_value($option_name, $new_value);
    }

    public function opt_safe($option_name) {

        $value = $this->get_value($option_name); 

        return $value ? $value : $this->get_default_of($option_name);

    }

    /**
     * @param $option_name string
     * @param $value mixed
     * @return boolean True if given option value is valid, false otherwise (or if option doesn't exists).
     */
    protected function _validate_option($option_name, $value = null) {

        $option_name = str_replace('leyka_', '', $option_name);
        $value = $value === NULL ? $this->get_value($option_name) : $value;

        foreach($this->get_validation_rules($option_name) as $rule_regexp => $rule_invalid_message) {
            if( !preg_match($rule_regexp, $value) ) {
                return false;
            }
        }

        return true;

    }

    /**
     * @param $option_name string
     * @return array An array of option validation rules.
     */
    public function get_validation_rules($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name, true);

        return empty($this->_options[$option_name]['validation_rules']) ?
            array() : apply_filters('leyka_option_validation_rules',  $option_name);

    }

    /**
     * @param $option_name string
     * @param $value mixed
     * @return array
     */
    public function get_validation_errors($option_name, $value = false) {

        $option_name = str_replace('leyka_', '', $option_name);
        $value = $value === false ? $this->opt_safe($option_name) : $value;

        if( !$this->option_exists($option_name)) {
            return array();
        }

        $errors = array();

        if($this->is_required($option_name) && !$value) {
            $errors[] = __('The field value is required', 'leyka');
        }

        foreach($this->get_validation_rules($option_name) as $rule_regexp => $rule_invalid_message) {
            if( !preg_match($rule_regexp, $value) ) {
                $errors[] = apply_filters('leyka_option_invalid_message', $rule_invalid_message, $rule_regexp, $option_name);
            }
        }

        return $errors;

    }

    public function get_default_of($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name);

        if(empty($this->_options[$option_name]) || empty($this->_options[$option_name]['default'])) {
            return false;
        } else {
            return empty($this->_options[$option_name]['default']) ? '' : $this->_options[$option_name]['default'];
        }

    }

    public function get_info_of($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name, true);

        return $this->_options[$option_name];

    }

    public function get_type_of($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name);

        return $this->_options[$option_name]['type'];
    }

    public function is_required($option_name) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name);

        return empty($this->_options[$option_name]) ? false : !empty($this->_options[$option_name]['required']);

    }

    public function is_valid($option_name, $value = false) {

        $option_name = str_replace('leyka_', '', $option_name);

        $this->_intialize_option($option_name, true);
        $value = $value === false ? $this->opt_safe($option_name) : $value;

        return !(($this->is_required($option_name) && !$value) || ($value && !$this->_validate_option($option_name, $value)));

    }

}

/**
 * @return Leyka_Options_Controller
 */
function leyka_options() {
    return Leyka_Options_Controller::instance();
}

add_filter('leyka_option_value', 'leyka_filter_option_values', 10, 2);
function leyka_filter_option_values($value, $option_name) {
    return $option_name == 'commission' ? maybe_unserialize($value) : $value;
}

/** Special field: gateway commission options */
add_action('leyka_save_custom_setting_commission', 'leyka_save_custom_setting_commission');
function leyka_save_custom_setting_commission($option_value) {

    foreach($option_value as $pm_full_id => $commission) {

        $commission = trim($commission);
        $commission = (float)str_replace(',', '.', $commission);

        $option_value[$pm_full_id] = $commission < 0.0 ? -$commission : $commission;

    }

    if($option_value != leyka_options()->opt('commission')) {
        leyka_options()->opt('commission', $option_value);
    }

}

add_filter('leyka_option_value', 'leyka_get_commission_values', 10, 2);
function leyka_get_commission_values($value, $option_name) {
    return $option_name == 'commission' ? maybe_unserialize($value) : $value;
}
/** Special field: gateway commission options - END */