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

    public static function getController($controller_id) {

        $controller_id = trim($controller_id);

        switch($controller_id) {
            case 'init': return Leyka_Init_Wizard_Settings_Controller::get_instance();
            case 'cp': return Leyka_Cp_Wizard_Settings_Controller::get_instance();
            case 'yandex': return Leyka_Yandex_Wizard_Settings_Controller::get_instance();
            default: /** @throw some Exception */ return false;
        }

    }

    protected function __construct() {

        $this->_loadCssJs();
        $this->_setAttributes();
        $this->_setSections();

        add_action('leyka_settings_submit_'.$this->_id, array($this, 'handleSubmit'));

    }

    protected function _loadCssJs() {
        do_action('leyka_settings_controller_enqueue_scripts', $this->id);
    }

    abstract protected function _setAttributes();
    abstract protected function _setSections();

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'title': return $this->_title;
            default: return null;
        }
    }

    /** @return boolean */
    public function hasCommonErrors() {
        return !empty($this->_common_errors);
    }

    /** @return array Of errors */
    public function getCommonErrors() {
        return $this->_common_errors;
    }

    /** @return boolean */
    public function hasComponentErrors($component_id = null) {
        return !empty($this->getComponentErrors($component_id));
    }

    /**
     * @param string $component_id
     * @return array An array of WP_Error objects (each with one error message)
     */
    public function getComponentErrors($component_id = null) {

        return empty($component_id) ?
            $this->_component_errors :
            (empty($this->_component_errors[$component_id]) ? array() : $this->_component_errors[$component_id]);

    }

    protected function _handleSettingsSubmit() {
        if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) {
            do_action('leyka_settings_submit_'.$this->_id);
        }
    }

    abstract protected function _processSettingsValues(array $blocks = null);

    protected function _addCommonError(WP_Error $error) {
        $this->_common_errors[] = $error;
    }

    protected function _addComponentError($component_id, WP_Error $error) {
        if(empty($this->_component_errors[$component_id])) {
            $this->_component_errors[$component_id] = array($error);
        } else {
            $this->_component_errors[$component_id][] = $error;
        }
    }

    /** @return Leyka_Settings_Step */
    abstract public function getCurrentStep();

    /** @return Leyka_Settings_Section */
    abstract public function getCurrentSection();

    abstract public function getSubmitData($component = null);

    abstract public function getNavigationData();

    abstract public function handleSubmit();

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

        add_action('leyka_settings_wizard-'.$this->_id.'-_step_init', array($this, 'stepInit'));

        if( !$this->_sections ) {
            return;
        }

        if( !empty($_GET['return_to'])) {
            $this->_setCurrentStepById(esc_attr($_GET['return_to']));
        }

        if( !$this->current_section ) {
            $this->_setCurrentSection(reset($this->_sections));
        }

        if( !$this->current_step && $this->current_section ) {

            $init_step = $this->current_section->init_step;
            if($init_step) { /** @var $init_step Leyka_Settings_Step */
                $this->_setCurrentStep($init_step);
            }

        }

        if( !$this->_navigation_data ) {
            $this->_initNavigationData();
        }

//        echo '<pre>Constructor: '.print_r($this->_navigation_data[0], 1).'</pre>';

        // Debug {
        if(isset($_GET['reset'])) {
            $this->_resetActivity();
        }
        // } Debug

        do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) { // Step page loading after returning from further Step
            $this->_handleSettingsGoBack();
        } else if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) { // Step page loading after previous Step submit
            $this->_handleSettingsSubmit();
        } //else { // Normal Step page loading
        //}

        echo '<pre>'.print_r($this->_activity['history'], 1).'</pre>';

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
                return $this->_getNextStepId();

            default:
                return parent::__get($name);
        }
    }

    public function __set($name, $value) {
        switch($name) {
            case 'current_step':
                $this->_setCurrentStep($value);
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
    public function getCurrentSection() {
        return $this->current_section;
    }

    /** @return Leyka_Settings_Step */
    public function getCurrentStep() {
        return $this->current_step;
    }

    protected function _setCurrentStep(Leyka_Settings_Step $step) {

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

        $step = $this->getComponentById($step_full_id);
        if( !$step ) {
            return $this;
        }

        return $this->_setCurrentStep($step)
            ->_setCurrentSection($this->getComponentById($step->section_id));

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

    protected function _getSettingValue($setting_name = null) {

        if($setting_name) {

            if( !empty($this->_activity['history']) ) {
                foreach($this->_activity['history'] as $step_full_id => $step_settings) {
                    if(isset($step_settings[$setting_name])) {
                        return $step_settings[$setting_name];
                    }
                }
            }

            return null;

        } else {

            $res = array();
            foreach($this->_activity['history'] as $step_full_id => $step_settings) {
                $res = array_merge($res, $step_settings);
            }

            return $res;

        }

    }

    protected function _addHistoryEntry(array $data = array(), $step_full_id = false) {

        $data = empty($data) ? $this->getCurrentStep()->getFieldsValues() : $data;
        $step_full_id = !$step_full_id ? $this->getCurrentStep()->full_id : trim($step_full_id);

        if(empty($this->_activity['history'][$step_full_id])) {
            $this->_activity['history'][$step_full_id] = array(
                'navigation_position' => $this->_getStepNavigationPosition($step_full_id),
                'data' => $data
            );
        } else {
            $this->_activity['history'][$step_full_id] = $this->_activity['history'][$step_full_id] + array(
                'navigation_position' => $this->_getStepNavigationPosition($step_full_id),
                'data' => $data
            );
        }

        return $this->_saveActivity();

    }

    protected function _processSettingsValues(array $blocks = null) {

        $blocks = $blocks ? $blocks : $this->getCurrentStep()->getBlocks();

        foreach($blocks as $block) { /** @var $block Leyka_Settings_Block */
            if(is_a($block, 'Leyka_Option_Block') && $block->isValid()) {
                leyka_save_option($block->option_id);
            } else if(is_a($block, 'Leyka_Custom_Setting_Block')) {
                do_action("leyka_save_custom_option-{$block->setting_id}");
            } else if(is_a($block, 'Leyka_Container_Block')) {
                $this->_processSettingsValues($block->getContent());
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
                ->_setCurrentStep($this->getCurrentSection()->init_step);
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
    protected function _initNavigationData() {

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
    protected function _getStepNavigationPosition($step_full_id = false) {
        return $step_full_id ? trim($step_full_id) : $this->getCurrentStep()->full_id;
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

                if($navigation_position === $section['section_id'].'-'.$step['step_id']) {

                    $navigation_data[$section_index]['steps'][$step_index]['is_current'] = true;
                    $navigation_data[$section_index]['is_current'] = true;

                    break 2;

                } else {

                    $navigation_data[$section_index]['steps'][$step_index]['is_completed'] = true;
                    foreach(empty($this->_activity['history']) ? array() : $this->_activity['history'] as $step_full_id => $data) {
//                        if($data['navigation'])
                    }

                }

            }

            $navigation_data[$section_index]['is_completed'] = true;

            if($navigation_position === $section['section_id'].'--') {
                break;
            }

        }

        return $navigation_data;

    }

    protected function _saveStepNavigationPosition() {

        foreach($this->_navigation_data as $section_index => &$section) {

            if($section['section_id'] !== $this->getCurrentSection()->id) {
                continue;
            }

            /**
             * @todo If current section is completed, save it's URL?
             * Mb, we shouldn't do it - the active navigation make sense only for steps, but not for sections.
             */

            foreach(empty($section['steps']) ? array() : $section['steps'] as $step_index => &$step) {
                if($step['step_id'] === $this->getCurrentStep()->id) {

                    $step['url'] = add_query_arg(
                        'return_to', $section['section_id'].'-'.$step['step_id'], remove_query_arg('return_to')
                    );

                }
            }

        }

    }

    /**
     * @param $component_id string
     * @param  $is_full_id boolean
     * @return mixed Leyka_Settings_Step, Leyka_Settings_Section or null, if given component ID wasn't found
     */
    public function getComponentById($component_id, $is_full_id = true) {

        if( !$is_full_id ) {

            $section = $this->getCurrentSection();
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

        $step = $section->getStepById($step_id);

        return $step;

    }

    /**
     * Navigation data incapsulation method - Wizards default implementation.
     * @return array
     */
    public function getNavigationData() {
        return $this->_navigation_data;
    }

    public function handleSubmit() {

        $this->_processSettingsValues(); // Save all valid options on current step

        if( !$this->getCurrentStep()->isValid() ) {

            foreach($this->getCurrentStep()->getErrors() as $component_id => $errors) {
                foreach($errors as $error) {
                    $this->_addComponentError($component_id, $error);
                }
            }

            return;

        }

        // Whole step settings handling:
        $settings_entered = $this->getCurrentStep()->getFieldsValues();

        if($this->getCurrentStep()->hasHandler()) {

            $result = call_user_func($this->getCurrentStep()->getHandler(), $settings_entered);

            if(is_array($result)) {
                foreach($result as $error) { /** @var $error WP_Error */
                    if(is_wp_error($error)) {
                        $this->_addCommonError($error);
                    }
                }
            } else if(is_wp_error($result)) {
                $this->_addCommonError($result);
            }

        }

        do_action("leyka_process_step_settings-{$this->getCurrentStep()->full_id}", $settings_entered);

        if($this->hasCommonErrors()) { // Stay on current step
            return;
        }

        $this->_addHistoryEntry(); // Save the step data in the storage
        $this->_saveStepNavigationPosition();

        echo '<pre>Submit: '.print_r($this->_navigation_data[0], 1).'</pre>';

        // Proceed to the next step:
        $next_step_full_id = $this->_getNextStepId();
        if($next_step_full_id && $next_step_full_id !== true) {

            $step = $this->getComponentById($this->_getNextStepId());
            if( !$step ) {

                $this->_addCommonError(new WP_Error('next_step_not_found', 'Следующий шаг мастера не найден'));
                return;

            }

            $this->_setCurrentStep($step);

            do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

        }

    }

    public function stepInit() {
    }

    /**
     * Steps branching incapsulation method. By default, it's next step in _steps array.
     *
     * @param $step_from Leyka_Settings_Step
     * @param $return_full_id boolean
     * @return mixed Either next step ID, or false (if non-existent step given), or true (if last step given).
     */
    protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true) {

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
                    $next_step = reset($section->steps);
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

        return $next_section->id.'-'.$next_step->id;

    }

}