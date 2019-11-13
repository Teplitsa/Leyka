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

        $section = reset($this->_options);
        if(empty($section['section'])) { // No section set in options - add the default "main options" one
            $section = new Leyka_Settings_Section('main_options', $stage->id, __('Main options', 'leyka'));
        }

        foreach($this->_options as $entry) {

            if( !empty($entry['section']) ) { // Handle the section blocks

                $section = new Leyka_Settings_Section($entry['section']['name'], $stage->id, $entry['section']['title']);

                if( !empty($entry['section']['options']) && is_array($entry['section']['options'])) {

                    foreach($entry['section']['options'] as $option_id => $params) {

                        $section->add_block(new Leyka_Option_Block(array(
                            'id' => $option_id,
//                            'option_id' => $option_id,
//                            'show_description' => false,
                        )));

//                        $stage->add_section($section)
                    }

                    $section->add_to($stage);

                }

            } else { // Handle the option or container,

            }

        }

        $section->add_to($stage);

        $this->_stages[$stage->id] = $stage;

    }

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'title': return $this->_title;
            default: return null;
        }
    }

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

    // Use this method to put given options Blocks in a row (i.e., make a Settings_Container of them).
    public function make_components_container(array $ids) {
        /** @todo Implement it */
    }

}