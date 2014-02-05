<?php
class Leyka_Options_Controller {
    private static $_instance = null;
    protected $_options = array();
    protected $_field_types = array('text', 'html', 'rich_html', 'select', 'radio', 'checkbox', 'multi_checkbox');

    public static function instance() {
        if(empty(self::$_instance))
            self::$_instance = new self;

        return self::$_instance;
    }

    private function __construct() {

        if( !empty($_GET['leyka_core_refresh_options']) )
            delete_option('leyka_options_installed');

        if( !get_option('leyka_options_installed') ) { // Clear plugin intallation, set all options to their defaults
            
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-defaults.php');
            global $default_option_values;

            $all_options_installed = true;
            foreach($default_option_values as $name => $data) {

                if( !empty($_GET['leyka_core_refresh_options']) ) // Remove options just in case
                    delete_option("leyka_$name");
                elseif(get_option("leyka_$name") !== false) // Option already installed, skip it
                    continue;

                $data['value'] = !empty($data['default']) && empty($data['value']) ? $data['default'] : $data['value'];
                $all_options_installed &= add_option("leyka_$name", $data);
            }

            if($all_options_installed)
                update_option('leyka_options_installed', 1);
        }

        // Read cur option values here, and fill $_options:
        $modules_active = get_option('leyka_modules');
        if($modules_active)
            $this->_options['modules'] = $modules_active;
        else {
            update_option('leyka_modules', array('leyka'));
            $this->_options['modules'] = array('leyka');
        }

        foreach(wp_load_alloptions() as $name => $data) {
            $matched_substr = stristr($name, 'leyka_');
            
            if( !$matched_substr || $name == 'leyka_modules' || $name == 'leyka_options_installed' )
                continue;
            else {
                $data = maybe_unserialize($data);
                $this->_options[str_replace('leyka_', '', $name)] = $data;
            }
        }

//        echo '<pre>'.print_r(wp_load_alloptions(), TRUE).'</pre>';
//        echo '<pre>'.print_r($this->_options, TRUE).'</pre>';
    }

    /** 
     * @param string $option_name
     * @return mixed
     */
    public function get_value($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);

        return empty($this->_options[$option_name]) ? null : (
            $this->_options[$option_name]['type'] == 'html' || $this->_options[$option_name]['type'] == 'rich_html' ?
                html_entity_decode(stripslashes($this->_options[$option_name]['value'])) : $this->_options[$option_name]['value']
        );
    }

    /**
     * @param string $section
     * @return array
     */
    public function get_values($section = '') {
        if(empty($section)) {
            $values = array();
            foreach($this->_options as $name => $data) {
                $values[$name] = $data['value'];
            }

            return $values;
        }

        if( !in_array($section, $this->_options['modules']) )
            return false;

        $section_opts = array();
        foreach($this->_options as $name => $data) {
            if(stristr($name, $section.'_'))
                $section_opts[str_replace($section.'_', '', $name)] = $data['value'];
        }

        return $section_opts;
    }

    public function create_option($name, $type, $params) {
        $name = stristr($name, 'leyka_') ? $name : 'leyka_'.$name;

        if( !in_array($type, $this->_field_types) )
            return false;
        if( !empty($params['type']) ) // Just in case
            unset($params['type']);

        if(empty($params['value']) && !empty($params['default']))
            $params['value'] = $params['default'];

        $params = array_merge(array(
            'type' => $type, // html, rich_html, select, radio, checkbox, multi_checkbox  
            'value' => '',
            'default' => '',
            'title' => $name,
            'description' => '',
            'required' => 0, // 1 if field is required, 0 otherwise
            'placeholder' => '', // For text fields
            'length' => '', // For text fields
            'list_entries' => array(), // For select, radio & checkbox fields
            'validation_rules' => array(), // List of regexp?..
        ), $params);

        $option_created = add_option($name, $params);

        if($option_created)
            $this->_options[ str_replace('leyka_', '', $name) ] = $params;

        return $option_created;
    }
    
    public function delete_option($name) {
        $name = stristr($name, 'leyka_') ? $name : 'leyka_'.$name;

        $option_deleted = delete_option($name);
        
        if($option_deleted)
            unset($this->_options[ str_replace('leyka_', '', $name) ]);

        return $option_deleted;
    }

    public function option_exists($name) {
        $name = str_replace('leyka_', '', $name);

        return !empty($this->_options[$name]);
    }

    /** 
     * @param mixed $option_name Option name, or assoc array of (option_name => new_value) pairs.
     * @param mixed $option_value If $option_name is a string, it's the new value; otherwise not used.
     * @return bool
     */
    public function set_value($option_name, $option_value = null) {
        // Check if option exists. If not, do nothing and return false:
        if($this->option_exists($option_name) && $this->_validate_option($option_name, $option_value)) {

            $old_value = $this->_options[$option_name]['value']; // Rollback to it if option update fails
            $this->_options[$option_name]['value'] = $option_value;

            $updated = update_option('leyka_'.$option_name, $this->_options[$option_name]);
            if( !$updated )
                $this->_options[$option_name]['value'] = $old_value;

            return $updated;
        } else
            return false;
    }

    public function opt($option_name, $new_value = null) {
        return $new_value === null ?
            $this->get_value($option_name) : $this->set_value($option_name, $new_value);
    }

    public function opt_safe($option_name) {
        return $this->get_value($option_name) ? $this->get_value($option_name) : $this->get_default_of($option_name);
    }

    protected function _validate_option($option_name, $value) {
        $option_name = str_replace('leyka_', '', $option_name);
        // use the $this->_options[$option_name]['validation_rules'], luke
        return true;
    }

    public function get_default_of($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);

        if(empty($this->_options[$option_name]))
            return false;
        else
            return empty($this->_options[$option_name]['default']) ? '' : $this->_options[$option_name]['default'];
    }

    public function get_info_of($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);

        return empty($this->_options[$option_name]) ? false : $this->_options[$option_name];
    }

    public function get_type_of($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);

        return empty($this->_options[$option_name]) ? false : $this->_options[$option_name]['type'];
    } 

//    public function set_default_of($option_name, $new_value) {
//        return true;
//    }

    public function is_required($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);

        if(empty($this->_options[$option_name]))
            return false;
        else
            return $this->_options[$option_name]['required'] >= 1 ? 1 : 0;
    }
}

/**
 * @return Leyka_Options_Controller
 */
function leyka_options() {
    return Leyka_Options_Controller::instance();
}