<?php if( !defined('WPINC') ) die;

class Leyka_Options_Controller extends Leyka_Singleton {

    protected static $_instance = null;
    protected static $_options_meta = array();

    protected $_options = array();
    protected static $_field_types = array('text', 'textarea', 'number', 'html', 'rich_html', 'select', 'radio', 'checkbox', 'multi_checkbox', 'legend', 'file');

    protected function __construct() {
        require_once(LEYKA_PLUGIN_DIR.'inc/leyka-options-meta.php');
    }

    public function isStandardFieldType($type) {
        return in_array($type, self::$_field_types);
    }

    /**
     * A service method to retrieve the plugin option value when it's just being initialized, and can't do
     * proper options metadata loading yet.
     */
    public static function get_option_value($option_id) {

        $option_id = stristr($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        return apply_filters('leyka_option_value', get_option($option_id), $option_id);

    }

    /**
     * A service method to load the plugin option metadata to the controller's cache array.
     
     * 
*@param $option_id string
     * @param $load_value bool Whether to load the option value from the DB. Sometimes it's not needed.
     * @return bool True/false of the initailization.
     */
    protected function _intialize_option($option_id, $load_value = false) {

        $option_id = str_replace('leyka_', '', $option_id);

        if(empty(self::$_options_meta[$option_id]) && empty($this->_options[$option_id])) {

            do_action('leyka_add_custom_option', $option_id);

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
     * A service method to load the option value from the DB to the controller's cache array.
     * 
*@param $option_id string
     */
    protected function _initialize_value($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);

        if( !isset($this->_options[$option_id]['value']) ) {

            $this->_options[$option_id]['value'] = get_option("leyka_$option_id");

            // Option is not set, use default value from meta:
            if($this->_options[$option_id]['value'] === false && !empty(self::$_options_meta[$option_id])) {
                $this->_options[$option_id]['value'] = empty(self::$_options_meta[$option_id]['default']) ?
                    '' : self::$_options_meta[$option_id]['default'];
            }
        }

        if(
            $this->_options[$option_id]['value'] && ($this->_options[$option_id]['type'] === 'html' ||
            $this->_options[$option_id]['type'] === 'rich_html')
        ) {

            $this->_options[$option_id]['value'] =
                is_array($this->_options[$option_id]['value']) &&
                isset($this->_options[$option_id]['value']['value']) ?
                    html_entity_decode(stripslashes($this->_options[$option_id]['value']['value'])) :
                    html_entity_decode(stripslashes((string)$this->_options[$option_id]['value']));
        }
    }

    public function get_options_names() {
        return array_unique(array_merge(array_keys(self::$_options_meta), array_keys($this->_options)));
    }

    /** 
     * @param string $option_id
     * @return mixed
     */
    public function get_value($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);
        if( !$this->_intialize_option($option_id, true) ) {
            return null;
        }

        if(in_array($this->_options[$option_id]['type'], array('text', 'html', 'rich_html'))) {
            $this->_options[$option_id]['value'] = is_array($this->_options[$option_id]['value']) ?
                $this->_options[$option_id]['value'] :
                trim($this->_options[$option_id]['value']);
        }

        $this->_options[$option_id]['value'] = apply_filters(
            'leyka_option_value-'.$option_id,
            $this->_options[$option_id]['value']
        );

        return apply_filters('leyka_option_value', $this->_options[$option_id]['value'], $option_id);

    }

    public function add_option($option_id, $type, $params) {

        $option_id = stristr($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        if( !in_array($type, self::$_field_types) ) {
            return false;
        }
        if( !empty($params['type']) ) { // Just in case
            unset($params['type']);
        }

        $value_saved = maybe_unserialize(get_option($option_id));

        if(empty($params['value']) && $value_saved !== false) {
            $params['value'] = $value_saved;
        } else if(empty($params['value']) && !empty($params['default'])) {
            $params['value'] = $params['default'];
        }

        /** @var $params array Full option format description in the beginning of leyka-options-meta.php */
        $params = array_merge(array(
            'type' => $type,
            'value' => '',
            'default' => '',
            'title' => $option_id,
            'description' => '',
            'required' => false,
            'placeholder' => '',
            'comment' => '',
            'length' => '',
            'list_entries' => array(),
            'validation_rules' => array(),
        ), $params);

        $option_added = $value_saved !== false ? true : add_option($option_id, $params['value']);

        if($option_added) {
            $this->_options[str_replace('leyka_', '', $option_id)] = $params;
        }

        return $option_added;

    }

    public function delete_option($option_id) {

        $option_id = stristr($option_id, 'leyka_') !== false ? $option_id : 'leyka_'.$option_id;

        $this->_intialize_option($option_id);

        $option_deleted = delete_option($option_id);

        if($option_deleted) {
            unset($this->_options[str_replace('leyka_', '', $option_id)]);
        }

        return $option_deleted;

    }

    public function option_exists($name) {

        $this->_intialize_option($name);

        return isset($this->_options[str_replace('leyka_', '', $name)]);

    }

    /** 
     * @param mixed $option_id Option name, or assoc array of (option_name => new_value) pairs.
     * @param mixed $option_value If $option_name is a string, it's the new value; otherwise not used.
     * @return bool
     */
    public function set_value($option_id, $option_value = null) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id, true);

        if(in_array($this->_options[$option_id]['type'], array('text', 'html', 'rich_html'))) {
            $this->_options[$option_id]['value'] = trim($this->_options[$option_id]['value']);
        }

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

            return $updated;

        } else {
            return false;
        }

    }

    public function opt($option_id, $new_value = null) {
        return $new_value === null ? $this->get_value($option_id) : $this->set_value($option_id, $new_value);
    }

    public function opt_safe($option_name) {

        $value = $this->get_value($option_name); 

        return $value ? $value : $this->get_default_of($option_name);

    }

    /**
     * @param $option_id string
     * @param $value mixed
     * @return boolean True if given option value is valid, false otherwise (or if option doesn't exists).
     */
    protected function _validate_option($option_id, $value = null) {

        $option_id = str_replace('leyka_', '', $option_id);
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

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id, true);

        $validation_rules = empty($this->_options[$option_id]['validation_rules']) ?
            array() : $this->_options[$option_id]['validation_rules'];

        return apply_filters('leyka_option_validation_rules-'.$option_id, $validation_rules);

    }

    /**
     * @param $option_id string
     * @param $value mixed
     * @return array
     */
    public function get_validation_errors($option_id, $value = false) {

        $option_id = str_replace('leyka_', '', $option_id);
        $value = $value === false ? $this->opt_safe($option_id) : $value;

        if( !$this->option_exists($option_id)) {
            return array();
        }

        $errors = array();

        if($this->is_required($option_id) && !$value) {
            $errors[] = 'Значение поля обязательно'; //__('The field value is required', 'leyka');
        }

        foreach($this->get_validation_rules($option_id) as $rule_regexp => $rule_invalid_message) {
            if( !preg_match($rule_regexp, $value) ) {
                $errors[] = apply_filters('leyka_option_invalid_message', $rule_invalid_message, $rule_regexp, $option_id);
            }
        }

        return apply_filters('leyka_option_validation_errors-'.$option_id, $errors, $value);

    }

    public function get_info_of($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id, true);
        
        $this->_options[$option_id] = array(
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
        ) + $this->_options[$option_id];

        return apply_filters('leyka_option_info-'.$option_id, $this->_options[$option_id]);

    }

    public function get_default_of($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['default'];

    }

    public function get_type_of($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['type'];

    }

    public function is_required($option_id) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id);

        $option_data = $this->get_info_of($option_id);

        return $option_data['required'];

    }

    public function is_valid($option_id, $value = false) {

        $option_id = str_replace('leyka_', '', $option_id);

        $this->_intialize_option($option_id, true);
        $value = $value === false ? $this->opt_safe($option_id) : $value;
        $option_valid = !(
            ($this->is_required($option_id) && !$value) || ($value && !$this->_validate_option($option_id, $value))
        );

        return apply_filters('leyka_option_valid-'.$option_id, $option_valid, $value);

    }

}

/**
 * @return Leyka_Options_Controller
 */
function leyka_options() {
    return Leyka_Options_Controller::get_instance();
}

/** Special field: gateway commission options */
add_filter('leyka_option_value-commission', 'leyka_get_commission_values');
function leyka_get_commission_values($value) {
    return maybe_unserialize($value);
}

add_action('leyka_save_custom_option-commission', 'leyka_save_custom_option_commission');
function leyka_save_custom_option_commission($option_value) {

    foreach($option_value as $pm_full_id => $commission) {

        $commission = trim($commission);
        $commission = (float)str_replace(',', '.', $commission);

        $option_value[$pm_full_id] = $commission < 0.0 ? -$commission : $commission;

    }

    if($option_value != leyka_options()->opt('commission')) {
        leyka_options()->opt('commission', $option_value);
    }

}
/** Special field: gateway commission options - END */

/** Save the basic site data in the plugin stats DB */
function leyka_sync_plugin_stats_option() {

    $stats_server_base_url = defined('WP_DEBUG') && WP_DEBUG ?
        rtrim(LEYKA_USAGE_STATS_DEV_SERVER_URL, '/') : rtrim(LEYKA_USAGE_STATS_PROD_SERVER_URL, '/');

    if(get_option('leyka_installation_id') > 0) { // Update the installation (activate/deactivate)
        $params = array(
            'stats_active' => !!leyka_options()->opt('send_plugin_stats'),
            'installation_id' => (int)get_option('leyka_installation_id'),
        );
    } else { // Add new installation
        $params = array(
            'stats_active' => true,
            'installation_url' => home_url(),
            'plugin_install_date' => get_option('leyka_plugin_install_date'),
            'collect_stats_from_date' => time(),
            'stats_collection_active' => true,
        );
    }

    $response = wp_remote_post($stats_server_base_url.'/sync-installation.php', array(
        'timeout' => 10, // Max request time in seconds
        'redirection' => 3, // A number of max times for request redirects
        'httpversion' => '1.1',
        'blocking' => true, // True for sync request, false otherwise
        'body' => $params,
    ));

    if(is_wp_error($response)) {
        return new WP_Error(
            'init_plugin_stats_error',
            sprintf('Ошибка при сохранении данных о сборе статистики использования: %s', $response->get_error_message())
        );
    } else if(empty($response['response']['code']) || $response['response']['code'] != 200) {

        $error_message = sprintf(
            'Ошибка при сохранении данных о сборе статистики использования: %s',
            empty($response['response']['code']) ?
                'данные о коде и статусе ответа не получены' :
                'код '.$response['response']['code'].(empty($response['response']['message']) ? '' : ' ('.$response['response']['message'].')')
        );

        return new WP_Error('init_plugin_stats_error', $error_message);

    } else if(empty($response['body'])) {
        return new WP_Error(
            'plugin_stats_not_saved',
            sprintf(__("The plugin stats collection status wasn't saved :( Please send a message about it to the <a href='mailto:".LEYKA_SUPPORT_EMAIL."' target='_blank'>plugin tech support</a>"), 'leyka')
        );
    } else {

        $response = maybe_unserialize($response['body']);
        if(empty($response['installation_id']) || (int)$response['installation_id'] <= 0) {
            return new WP_Error(
                'plugin_stats_not_saved',
                sprintf(__("The plugin stats collection status wasn't saved :( Please send a message about it to the <a href='mailto:".LEYKA_SUPPORT_EMAIL."' target='_blank'>plugin tech support</a>"), 'leyka')
            );
        }

        return update_option('leyka_installation_id', (int)$response['installation_id']);

    }

}