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

        require_once(LEYKA_PLUGIN_DIR.'inc/leyka-options-meta.php');
        global $options_meta;

        foreach($options_meta as $name => &$data) {

            $data['value'] = get_option("leyka_$name");       
                
            if($data['value'] === false) // Option is not set, use default value from meta
                $data['value'] = $data['default'];

            $this->_options[str_replace('leyka_', '', $name)] = $data;
            
        }

    }

    /** 
     * @param string $option_name
     * @return mixed
     */
    public function get_value($option_name) {
        $option_name = str_replace('leyka_', '', $option_name);
        if(empty($this->_options[$option_name]))
            return null;
       
        $value = $this->_options[$option_name]['value'];      
        
        if($this->_options[$option_name]['type'] == 'html' || $this->_options[$option_name]['type'] == 'rich_html'){
            $value = html_entity_decode(stripslashes($value));
        }
 

        return $value;
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

    public function add_option($name, $type, $params) {
        $name = stristr($name, 'leyka_') !== false ? $name : 'leyka_'.$name;

        if( !in_array($type, $this->_field_types) )
            return false;
        if( !empty($params['type']) ) // Just in case
            unset($params['type']);

        $value_saved = maybe_unserialize(get_option($name)); 
        
        if(empty($params['value']) && $value_saved !== false)
            $params['value'] = $value_saved;
        else if(empty($params['value']) && !empty($params['default']))
            $params['value'] = $params['default'];
        
        //hack for some strangely incorrect after-update behavior
        if(is_array($params['value']) && !empty($params['value']['value']))
            $params['value'] = $params['value']['value'];
        
        
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

        $option_added = $value_saved !== false ? true : add_option($name, $params['value']);

        if($option_added)
            $this->_options[ str_replace('leyka_', '', $name) ] = $params;

        return $option_added;
    }
    
    public function delete_option($name) {
        $name = stristr($name, 'leyka_') !== false ? $name : 'leyka_'.$name;

        $option_deleted = delete_option($name);
        
        if($option_deleted)
            unset($this->_options[ str_replace('leyka_', '', $name) ]);

        return $option_deleted;
    }

    public function option_exists($name) {
        $name = str_replace('leyka_', '', $name);

        //return !empty($this->_options[$name]); this cause problem for checkboxes = 0
        return isset($this->_options[$name]);
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

            $updated = update_option('leyka_'.$option_name, $option_value); 
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