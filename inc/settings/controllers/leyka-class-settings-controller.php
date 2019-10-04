<?php if( !defined('WPINC') ) die;
/**
 * Leyka settings controller - the root class.
 **/

abstract class Leyka_Settings_Controller extends Leyka_Singleton { // Each descendant is a concrete wizard

    protected $_id;
    protected $_title;
    protected $_common_errors = array();
    protected $_component_errors = array();

    /** @var $_sections array of Leyka_Wizard_Section objects */
    protected $_sections;

    protected static $_instance = null;

    public static function get_controller($controller_id) {

        $controller_id = trim($controller_id);

        switch($controller_id) {
            case 'init': return Leyka_Init_Wizard_Settings_Controller::get_instance();
            case 'cp': return Leyka_Cp_Wizard_Settings_Controller::get_instance();
            case 'yandex': return Leyka_Yandex_Wizard_Settings_Controller::get_instance();
            default: /** @throw some Exception */ return false;
        }

    }

    protected function __construct() {

        $this->_load_frontend_scripts();
        $this->_set_attributes();
        $this->_set_sections();

        add_action('leyka_settings_submit_'.$this->_id, array($this, 'handle_submit'));

    }

    protected function _load_frontend_scripts() {

        add_action('admin_enqueue_scripts', function(){

            wp_localize_script('leyka-settings', 'leyka_wizard_common', array(
                'copy2clipboard' => esc_html__('Copy', 'leyka'),
                'copy2clipboard_done' => esc_html__('Copied to the clipboard!', 'leyka'),
            ));

        }, 11);

        do_action('leyka_settings_controller_enqueue_scripts', $this->id);

    }

    abstract protected function _set_attributes();
    abstract protected function _set_sections();

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

    abstract protected function _process_settings_values(array $blocks = null);

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

    /** @return Leyka_Settings_Step */
    abstract public function get_current_step();

    /** @return Leyka_Settings_Section */
    abstract public function get_current_section();

    abstract public function get_submit_data($component = null);

    abstract public function get_navigation_data();

    abstract public function handle_submit();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;

    protected $_activity = array('history' => array(), 'current_step' => false, 'current_section' => false,);
    protected $_navigation_data = array();

    protected function __construct() {

        parent::__construct();

        $wizards_activities = get_transient('leyka_wizards_activities');
        if( !empty($wizards_activities[$this->_id]) ) {
            $this->_activity = $wizards_activities[$this->_id];
        }

        add_action('leyka_settings_wizard-'.$this->_id.'-_step_init', array($this, 'step_init'));

        if( !$this->_sections ) {
            return;
        }

        if( !empty($_GET['return_to'])) { // Wizards active navigation

            $_GET['return_to'] = esc_attr($_GET['return_to']);
            $history = empty($this->_activity['history']) ? array() : $this->_activity['history'];

            foreach($history as $step_full_id => $step_history_entry) {

                $target_nav_position_parts = explode('-', $_GET['return_to']);

                if(count($target_nav_position_parts) > 1) { // Step nav. position given
                    $target_navigation_position = $_GET['return_to'];
                } else { // Section nav. position given - find the first of it's Steps

                    $history_entry_nav_position_parts = explode('-', $step_history_entry['navigation_position']);
                    if($history_entry_nav_position_parts[0] === $target_nav_position_parts[0]) {
                        $target_navigation_position = $step_history_entry['navigation_position'];
                    } else {
                        continue;
                    }

                }

                if($step_history_entry['navigation_position'] === $target_navigation_position) {

                    $this->_handleSettingsGoBack($step_full_id);
                    break;

                }

            }

        }

        if( !$this->current_section ) {
            $this->_setCurrentSection(reset($this->_sections));
        }

        if( !$this->current_step && $this->current_section ) {

            $init_step = $this->current_section->init_step;
            if($init_step) { /** @var $init_step Leyka_Settings_Step */
                $this->_set_current_step($init_step);
            }

        }

        if( !$this->_navigation_data ) {
            $this->_init_navigation_data();
        }

        // Debug {
        if(isset($_GET['reset'])) {
            $this->_resetActivity();
        }
        // } Debug

        do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) { // Step page loading after returning from further Step
            $this->_handleSettingsGoBack();
        } else if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) { // Step page loading after previous Step submit
            $this->_handle_settings_submit();
        } //else { // Normal Step page loading
        //}

        if(isset($_GET['debug'])) {
            echo '<pre>The activity: '.print_r($this->_activity, 1).'</pre>';
        }

    }

    public function __get($name) {
        switch($name) {
            case 'current_step':
                return empty($this->_activity['current_step']) ? null : $this->_activity['current_step'];

            case 'current_step_id':
                return empty($this->_activity['current_step']) ? null : $this->_activity['current_step']->id;

            case 'current_section':
                return empty($this->_activity['current_section']) ? null : $this->_activity['current_section'];

            case 'current_section_id':
                return empty($this->_activity['current_section']) ? null : $this->_activity['current_section']->id;

            case 'next_step_full_id':
                return $this->_get_next_step_id();
            
            case 'history':
                return empty($this->_activity['history']) ? array() : $this->_activity['history'];

            case 'navigation_data':
                return $this->_navigation_data;

            default:
                return parent::__get($name);
        }
    }

    public function __set($name, $value) {
        switch($name) {
            case 'current_step':
                $this->_set_current_step($value);
                break;
            case 'current_section':
                $this->_setCurrentSection($value);
                break;
            default:
        }
    }

    protected function _resetActivity() {

        $this->_activity = array('history' => array(), 'current_step' => false, 'current_section' => false,);
        $this->current_step = reset($this->_sections)->init_step;

        return $this->_saveActivity();

    }

    protected function _saveActivity() {

        $wizards_activities = get_transient('leyka_wizards_activities');
        $wizards_activities[$this->_id] = $this->_activity;

        set_transient('leyka_wizards_activities', $wizards_activities);

        return $this;

    }

    /** @return Leyka_Settings_Section */
    public function get_current_section() {
        return $this->current_section;
    }

    /** @return Leyka_Settings_Step */
    public function get_current_step() {
        return $this->current_step;
    }

    protected function _set_current_step(Leyka_Settings_Step $step) {

        $this->_activity['current_step'] = $step;

        return $this->_setCurrentSection($this->_sections[$step->section_id]); // Activity saved

    }

    protected function _setCurrentSection(Leyka_Settings_Section $section) {

        $this->_activity['current_section'] = $section;

        return $this->_saveActivity();

    }

    protected function _setCurrentStepById($step_full_id) {

        if( !$step_full_id ) {
            return $this;
        }

        $step = $this->get_component_by_id($step_full_id);
        if( !$step ) {
            return $this;
        }

        return $this->_set_current_step($step)
            ->_setCurrentSection($this->get_component_by_id($step->section_id));

    }

    /**
     * @param $step_full_id string
     * @return boolean
     */
    protected function _isStepCompleted($step_full_id) {

        /** @todo Throw some Exception if the given Step doesn't exists. */
        $step_full_id = trim($step_full_id);
        return !empty($this->_activity['history'][$step_full_id]);

    }

    protected function _get_setting_value($setting_name = null) {

        if($setting_name) {

            if(empty($this->_activity['history'])) {
                return null;
            }

            foreach($this->_activity['history'] as $step_full_id => $step_history_entry) {
                if(isset($step_history_entry['data'][$setting_name])) {
                    return $step_history_entry['data'][$setting_name];
                }
            }

            return null;

        } else {

            $res = array();
            foreach($this->_activity['history'] as $step_full_id => $step_history_entry) {
                $res = array_merge($res, $step_history_entry['data']);
            }

            return $res;

        }

    }

    protected function _add_history_entry(array $data = array(), $step_full_id = false) {

        $data = empty($data) ? $this->get_current_step()->get_fields_values() : $data;
        $step_full_id = !$step_full_id ? $this->get_current_step()->full_id : trim($step_full_id);

        if(empty($this->_activity['history'][$step_full_id])) {
            $this->_activity['history'][$step_full_id] = array(
                'navigation_position' => $this->_get_step_navigation_position($step_full_id),
                'data' => $data
            );
        } else {
            $this->_activity['history'][$step_full_id] = $this->_activity['history'][$step_full_id] + array(
                'navigation_position' => $this->_get_step_navigation_position($step_full_id),
                'data' => $data
            );
        }

        return $this->_saveActivity();

    }

    protected function _process_settings_values(array $blocks = null) {

        $blocks = $blocks ? $blocks : $this->get_current_step()->get_blocks();

        foreach($blocks as $block) { /** @var $block Leyka_Settings_Block */
            if(is_a($block, 'Leyka_Option_Block') && $block->is_valid()) {
                leyka_save_option($block->option_id);
            } else if(is_a($block, 'Leyka_Custom_Setting_Block')) {
                do_action("leyka_save_custom_option-{$block->setting_id}");
            } else if(is_a($block, 'Leyka_Container_Block')) {
                $this->_process_settings_values($block->get_content());
            }
        }

    }

    protected function _handleSettingsGoBack($step_full_id = false, $delete_history = true) {

        if( !$step_full_id ) {
            $step_full_id = array_key_last($this->_activity['history']);
        } else {

            $step_found = false;
            foreach($this->_activity['history'] as $passed_step_full_id => $data) {
                if($step_full_id === $passed_step_full_id) {

                    $step_found = true;
                    break;

                }
            }

            $step_full_id = $step_found ? $step_full_id : false;

        }

        if($step_full_id) {
            $this->_setCurrentStepById($step_full_id);
        } else {
            $this->_setCurrentSection(reset($this->_sections))
                ->_set_current_step($this->get_current_section()->init_step);
        }

        if( !!$delete_history ) { // Remove already passed keys from the Activity
            foreach(array_reverse($this->_activity['history'], true) as $passed_step_full_id => $data) {

                unset($this->_activity['history'][$passed_step_full_id]);

                if($passed_step_full_id === $step_full_id) {
                    break;
                }

            }
        }

        return $this->_saveActivity();

    }

    /** The default implementation: the Wizard navigation roadmap created from existing Sections & Steps */
    protected function _init_navigation_data() {

        if( !$this->_sections ) {
            return;
        }

        $this->_navigation_data = array();

        foreach($this->_sections as $section) { /** @var Leyka_Settings_Section $section */

            $steps = $section->getSteps();
            if( !$steps ) {
                continue;
            }

            $steps_data = array();
            $all_steps_completed = true;
            foreach($steps as $step) { /** @var Leyka_Settings_Step $step */

                $step_completed = $this->_isStepCompleted($step->full_id);

                if($all_steps_completed && !$step_completed) {
                    $all_steps_completed = false;
                }

                $steps_data[] = array(
                    'step_id' => $step->full_id,
                    'title' => $step->title,
                    'url' => false,
                    'is_current' => $this->current_step->full_id === $step->full_id,
                    'is_completed' => $step_completed,
                );

            }

            $this->_navigation_data[] = array(
                'section_id' => $section->id,
                'title' => $section->title,
                'url' => false,
                'is_current' => $this->current_section_id === $section->id, // True if the current Step belongs to the Section
                'is_completed' => $all_steps_completed,
                'steps' => $steps_data,
            );

        }

    }

    /** By default, each Step navigation position equals to it's full ID. */
    protected function _get_step_navigation_position($step_full_id = false) {
        return $step_full_id ? trim($step_full_id) : $this->get_current_step()->full_id;
    }

    /**
     * @param $navigation_data array
     * @param $navigation_position string
     * @return array
     */
    protected function _processNavigationData($navigation_position = null, array $navigation_data = null) {

        $navigation_data = empty($navigation_data) ? $this->_navigation_data : $navigation_data;
        $navigation_position = empty($navigation_position) ? $this->current_step_full_id : trim($navigation_position);

        foreach($navigation_data as $section_index => &$section) {

            $navigation_position_parts = explode('-', $navigation_position);

            if($section['section_id'] === $navigation_position_parts[0]) {

                if(count($navigation_position_parts) === 1) {

                    $navigation_data[$section_index]['is_current'] = true;
                    break;

                }

            }

            foreach(empty($section['steps']) ? array() : $section['steps'] as $step_index => $step) {

                $step_navigation_position = $section['section_id'].'-'.$step['step_id'];

                if($navigation_position === $step_navigation_position) {

                    $navigation_data[$section_index]['steps'][$step_index]['is_current'] = true;
                    $navigation_data[$section_index]['is_current'] = true;

                    break 2;

                } else {

                    $navigation_data[$section_index]['steps'][$step_index]['is_completed'] = true;
                    $navigation_data[$section_index]['steps'][$step_index]['url'] = add_query_arg(
                        'return_to', $step_navigation_position, remove_query_arg('return_to')
                    );

                }

            }

            $navigation_data[$section_index]['is_completed'] = true;
            $navigation_data[$section_index]['url'] = add_query_arg(
                'return_to', $section['section_id'], remove_query_arg('return_to')
            );

            if($navigation_position === $section['section_id'].'--') {
                break;
            }

        }

        return $navigation_data;

    }

    /**
     * Navigation data incapsulation method - Wizards default implementation.
     * @return array
     */
    public function get_navigation_data() {

        $navigation_position = $this->_get_step_navigation_position();

        return $navigation_position ?
            $this->_processNavigationData($navigation_position) :
            $this->_navigation_data;

    }

    /**
     * @param $component_id string
     * @param  $is_full_id boolean
     * @return mixed Leyka_Settings_Step, Leyka_Settings_Section or null, if given component ID wasn't found
     */
    public function get_component_by_id($component_id, $is_full_id = true) {

        if( !$is_full_id ) {

            $section = $this->get_current_section();
            $step_id = $component_id;

        } else {

            $component_id = explode('-', $component_id); // [0] is a Section ID, [1] is a Step ID

            if(count($component_id) < 2 && $component_id[0]) {
                return empty($this->_sections[ $component_id[0] ]) ? null : $this->_sections[ $component_id[0] ];
            }

            if(empty($this->_sections[$component_id[0]])) {
                return null;
            }

            $section = $this->_sections[$component_id[0]];
            $step_id = $component_id[1];

        }

        $step = $section->get_step_by_id($step_id);

        return $step;

    }

    public function handle_submit() {

        $this->_process_settings_values(); // Save all valid options on current step

        if( !$this->get_current_step()->is_valid() ) {

            foreach($this->get_current_step()->get_errors() as $component_id => $errors) {
                foreach($errors as $error) {
                    $this->_add_component_error($component_id, $error);
                }
            }

            return;

        }

        // Whole step settings handling:
        $settings_entered = $this->get_current_step()->get_fields_values();

        if($this->get_current_step()->has_handler()) {

            $result = call_user_func($this->get_current_step()->get_handler(), $settings_entered);

            if(is_array($result)) {
                foreach($result as $error) { /** @var $error WP_Error */
                    if(is_wp_error($error)) {
                        $this->_add_common_error($error);
                    }
                }
            } else if(is_wp_error($result)) {
                $this->_add_common_error($result);
            }

        }

        do_action("leyka_process_step_settings-{$this->get_current_step()->full_id}", $settings_entered);

        if($this->has_common_errors()) { // Stay on current step
            return;
        }

        $this->_add_history_entry(); // Save the step data in the storage

        // Proceed to the next step:
        $next_step_full_id = $this->_get_next_step_id();
        if($next_step_full_id && $next_step_full_id !== true) {

            $step = $this->get_component_by_id($this->_get_next_step_id());
            if( !$step ) {

                $this->_add_common_error(new WP_Error('next_step_not_found', esc_html__('The Wizard next step is not found', 'leyka')));
                return;

            }

            $this->_set_current_step($step);

            do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

        }

    }

    public function step_init() {
    }

    /**
     * Steps branching incapsulation method. By default, it's next step in _steps array.
     *
     * @param $step_from Leyka_Settings_Step
     * @param $return_full_id boolean
     * @return mixed Either next step ID, or false (if non-existent step given), or true (if last step given).
     */
    protected function _get_next_step_id(Leyka_Settings_Step $step_from = null, $return_full_id = true) {

        $step_from = $step_from && is_a($step_from, 'Leyka_Settings_Step') ? $step_from : $this->current_step;
        $section_from = $this->_sections[$step_from->section_id];

        $is_next_step_target = false;
        $next_step = null;

        foreach($section_from->steps as $step_id => $step) {

            if($is_next_step_target) {

                $next_step = $step;
                break;

            }

            if($step_from->section_id == $section_from->id && $step_id == $step_from->id) {
                $is_next_step_target = true;
            }

        }

        $next_section = null;

        if( !$next_step ) {

            $is_next_section_target = false;

            foreach($this->_sections as $section_id => $section) {

                if($is_next_section_target) {

                    $next_section = $section;
                    $next_step = $section->steps ? $section->steps[ array_key_first($section->steps) ] : false;
                    break;

                }

                if($section->id == $section_from->id) {
                    $is_next_section_target = true;
                }

            }

        }

        if( !$next_step ) {
            $next_step = $step_from;
        }

        if( !$next_section ) {
            $next_section = $section_from;
        }

//        echo '<pre>'.print_r($next_step, 1).'</pre>';
//        echo '<pre>'.print_r($next_section->steps, 1).'</pre>';

        return $next_section->id.'-'.$next_step->id;

    }

}