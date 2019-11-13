<?php if( !defined('WPINC') ) die;
/**
 * Leyka options controller - the base class.
 **/

class Leyka_Options_Settings_Controller extends Leyka_Settings_Controller {

    // ATM for ref (delete later):
//    protected $_id;
//    protected $_title;
//    protected $_common_errors = array();
//    protected $_component_errors = array();

    /** @var $_stages array of Leyka_Settings_Section objects */
    protected $_stages;

    protected $_options;

    protected static $_instance = null;

    public static function get_controller($controller_id) {

//        $controller_id = trim(esc_attr($controller_id));
        return self::get_instance();

    }

    protected function __construct() {

        $this->_id = 'options';

        $this->_load_frontend_scripts();
        $this->_set_attributes();
        $this->_set_stages();

        add_action('leyka_settings_submit_'.$this->_id, array($this, 'handle_submit'));

    }

    protected function _load_frontend_scripts() {
        parent::_load_frontend_scripts();
    }

    protected function _set_attributes() {
    }

    /**
     * The default behavior. Components are produced out of $this->_options:
     * Stages -> Sections, Portlets -> Steps, Options & layout -> Blocks.
     */
    protected function _set_stages() {

        if( !$this->_options || !is_array($this->_options) ) {
            return;
        }

        $stage = new Leyka_Settings_Stage($this->_id, ''); // There is only one stage by default

        $section_check = reset($this->_options);
        if(empty($section_check['section'])) { // No section set in options - add the default "main options" one
            $default_section = new Leyka_Settings_Section('main_options', $stage->id, __('Main options', 'leyka'));
        }

        foreach($this->_options as $option_id => $params) {

            if( !empty($params['section']) ) { // Handle the section blocks

                $section = new Leyka_Settings_Section($params['section']['name'], $stage->id, $params['section']['title']);

                if( !empty($params['section']['options']) && is_array($params['section']['options'])) {

                    foreach($params['section']['options'] as $inner_option_id => $inner_params) {

                        if(empty($inner_params['type'])) {
                            continue;
                        }

                        try {
                            $section->add_block($this->_create_settings_block_from_option($inner_option_id, $inner_params));
                        } catch(Exception $ex) {
                            // ...
                        }

                    }

                    $section->add_to($stage);

                }

            } else if( !empty($default_section) ) { // Handle the option or container
                try {
                    $default_section->add_block($this->_create_settings_block_from_option($option_id, $params));
                } catch(Exception $ex) {
                    // ...
                }
            }

        }

        if( !empty($default_section) ) { // No section set in options - add the default "main options" one
            $default_section->add_to($stage);
        }

        $this->_stages[$stage->id] = $stage;

    }

    // Mb, we won't need the getter
//    public function __get($name) {
//        switch($name) {
//            case 'id': return $this->_id;
//            case 'title': return $this->_title;
//            default: return null;
//        }
//    }

    /** @return boolean */
    public function has_common_errors() {
        return !!$this->_common_errors;
    }

    /** @return array Of errors */
    public function get_common_errors() {
        return $this->_common_errors;
    }

    /**
     * @param $component_id string
     * @return boolean
     */
    public function has_component_errors($component_id = null) {
        return !!$this->get_component_errors($component_id);
    }

    /**
     * @param $component_id string
     * @return array An array of WP_Error objects (each with one error message)
     */
    public function get_component_errors($component_id = null) {

        return empty($component_id) ?
            $this->_component_errors :
            (empty($this->_component_errors[$component_id]) ? array() : $this->_component_errors[$component_id]);

    }

    protected function _handle_settings_submit() {
        if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) {
            do_action('leyka_settings_submit_'.$this->_id);
        }
    }

    protected function _process_settings_values(array $blocks = null) {
        /** @todo Implement it */
    }

    protected function _add_common_error(WP_Error $error) {
        $this->_common_errors[] = $error;
    }

    protected function _add_component_error($component_id, WP_Error $error) {
        if(empty($this->_component_errors[$component_id])) {
            $this->_component_errors[$component_id] = array($error);
        } else {
            $this->_component_errors[$component_id][] = $error;
        }
    }

    /** @return Leyka_Settings_Section */
    public function get_current_section() {
        return; /** @todo Implement it */
    }

    /** @return Leyka_Settings_Stage */
    public function get_current_stage() {
        return; /** @todo Implement it */
    }

    public function get_submit_data($component = null) {
        /** @todo Implement it */
    }

    public function get_navigation_data() {
    }

    public function handle_submit() {
        /** @todo Implement it */
    }

    public function set_options_data(array $options = array()) {

        $this->_options = $options;
        $this->_set_stages();

        return $this;

    }

    /**
     * A recursive method to create Leyka_Settings_Block object from given option data.
     *
     * @param $option_id string An ID of an option/container to add to the section as a block.
     * @param $option_params array
     * @return Leyka_Settings_Block
     * @throws
     */
    protected function _create_settings_block_from_option($option_id, $option_params) {

        if( !$option_id || empty($option_params['type']) ) {
            throw new Exception(__('', 'leyka'), 0);
        }

        if($option_params['type'] === 'container' && !empty($option_params['entries'])) {

            $settings_block = new Leyka_Container_Block(); /** @todo Finish the block params */
            foreach($option_params['entries'] as $option_id => $params) {

                try {
                    $settings_block->add_block($this->_create_settings_block_from_option($option_id, $params));
                } catch(Exception $ex) {
                    // ...
                }

            }

        } /*else if(stristr($option_params['type'], 'custom_')) {
            $settings_block = new Leyka_Custom_Setting_Block();
        }*/ {
            $settings_block = new Leyka_Option_Block(); /** @todo Finish the block params */
        }

        return $settings_block;

    }

}