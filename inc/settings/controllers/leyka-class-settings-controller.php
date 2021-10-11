<?php if( !defined('WPINC') ) die;
/**
 * Leyka settings controller - the root class.
 */

abstract class Leyka_Settings_Controller extends Leyka_Singleton { // Each descendant class is a specific wizard

    protected $_id;
    protected $_title;
    protected $_common_errors = [];
    protected $_component_errors = [];

    /** @var $_stages array of Leyka_Settings_Section objects */
    protected $_stages;

    protected static $_instance = null;

    public static function get_controller($controller_id) {

        $controller_id = trim($controller_id);

        switch($controller_id) {
            case 'init': return Leyka_Init_Wizard_Settings_Controller::get_instance();
            case 'cp': return Leyka_Cp_Wizard_Settings_Controller::get_instance();
            case 'yandex': return Leyka_Yandex_Wizard_Settings_Controller::get_instance();
            case 'extension': return Leyka_Extension_Settings_Controller::get_instance();
            default: /** @throw some Exception */ return false;
        }

    }

    protected function __construct() {

        $this->_load_frontend_scripts();
        $this->_set_attributes();
        $this->_set_stages();

        add_action('leyka_settings_submit_'.$this->_id, [$this, 'handle_submit']);

    }

    protected function _load_frontend_scripts() {

        add_action('admin_enqueue_scripts', function(){

            wp_localize_script('leyka-settings', 'leyka_wizard_common', [
                'copy2clipboard' => esc_html__('Copy', 'leyka'),
                'copy2clipboard_done' => esc_html__('Copied to the clipboard!', 'leyka'),
            ]);

        }, 11);

        do_action('leyka_settings_controller_enqueue_scripts', $this->id);

    }

    abstract protected function _set_attributes();
    abstract protected function _set_stages();

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
            (empty($this->_component_errors[$component_id]) ? [] : $this->_component_errors[$component_id]);

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
            $this->_component_errors[$component_id] = [$error];
        } else {
            $this->_component_errors[$component_id][] = $error;
        }

    }

    /** @return Leyka_Settings_Section */
    abstract public function get_current_section();

    /** @return Leyka_Settings_Stage */
    abstract public function get_current_stage();

    abstract public function get_submit_data($component = null);

    abstract public function get_navigation_data();

    abstract public function handle_submit();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;

    protected $_activity = ['history' => [], 'current_section' => false, 'current_stage' => false,];
    protected $_navigation_data = [];

    protected function __construct() {

        parent::__construct();

        $wizards_activities = get_transient('leyka_wizards_activities');
        if( !empty($wizards_activities[$this->_id]) ) {
            $this->_activity = $wizards_activities[$this->_id];
        }

        add_action('leyka_settings_wizard-'.$this->_id.'-_section_init', [$this, 'section_init']);

        if( !$this->_stages ) {
            return;
        }

        if( !empty($_GET['return_to'])) { // Wizards active navigation

            $_GET['return_to'] = esc_attr($_GET['return_to']);
            $history = empty($this->_activity['history']) ? [] : $this->_activity['history'];

            foreach($history as $section_full_id => $section_history_entry) {

                $target_nav_position_parts = explode('-', $_GET['return_to']);

                if(count($target_nav_position_parts) > 1) { // Section nav. position given
                    $target_navigation_position = $_GET['return_to'];
                } else { // Section nav. position given - find the first of it's Sections

                    $history_entry_nav_position_parts = explode('-', $section_history_entry['navigation_position']);
                    if($history_entry_nav_position_parts[0] === $target_nav_position_parts[0]) {
                        $target_navigation_position = $section_history_entry['navigation_position'];
                    } else {
                        continue;
                    }

                }

                if($section_history_entry['navigation_position'] === $target_navigation_position) {

                    $this->_handle_settings_go_back($section_full_id);
                    break;

                }

            }

        }

        if( !$this->current_stage ) {
            $this->_set_current_stage(reset($this->_stages));
        }

        if( !$this->current_section && $this->current_stage ) {

            $init_section = $this->current_stage->init_section;
            if($init_section) { /** @var $init_section Leyka_Settings_Section */
                $this->_set_current_section($init_section);
            }

        }

        if( !$this->_navigation_data ) {
            $this->_init_navigation_data();
        }

        // Debug {
        if(isset($_GET['reset'])) {
            $this->_reset_activity();
        }
        // } Debug

        do_action('leyka_settings_wizard-'.$this->_id.'-_section_init');

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) { // Section page loading after returning from further Section
            $this->_handle_settings_go_back();
        } else if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) { // Section page loading after previous Section submit
            $this->_handle_settings_submit();
        } //else { // Normal Section page loading
        //}

        if(isset($_GET['debug'])) {
            echo '<pre>The activity: '.print_r($this->_activity, 1).'</pre>';
        }

    }

    public function __get($name) {
        switch($name) {
            case 'current_section':
                return empty($this->_activity['current_section']) ? null : $this->_activity['current_section'];

            case 'current_section_id':
                return empty($this->_activity['current_section']) ? null : $this->_activity['current_section']->id;

            case 'current_stage':
                return empty($this->_activity['current_stage']) ? null : $this->_activity['current_stage'];

            case 'current_stage_id':
                return empty($this->_activity['current_stage']) ? null : $this->_activity['current_stage']->id;

            case 'next_section_full_id':
                return $this->_get_next_section_id();
            
            case 'history':
                return empty($this->_activity['history']) ? [] : $this->_activity['history'];

            case 'navigation_data':
                return $this->_navigation_data;

            default:
                return parent::__get($name);
        }
    }

    public function __set($name, $value) {
        switch($name) {
            case 'current_section':
                $this->_set_current_section($value);
                break;
            case 'current_stage':
                $this->_set_current_stage($value);
                break;
            default:
        }
    }

    protected function _reset_activity() {

        $this->_activity = ['history' => [], 'current_stage' => false, 'current_section' => false,];
        $this->current_section = reset($this->_stages)->init_section;

        return $this->_save_activity();

    }

    protected function _save_activity() {

        $wizards_activities = get_transient('leyka_wizards_activities');
        $wizards_activities[$this->_id] = $this->_activity;

        set_transient('leyka_wizards_activities', $wizards_activities);

        return $this;

    }

    /** @return Leyka_Settings_Stage */
    public function get_current_stage() {
        return $this->current_stage;
    }

    /** @return Leyka_Settings_Section */
    public function get_current_section() {
        return $this->current_section;
    }

    protected function _set_current_section(Leyka_Settings_Section $section) {

        $this->_activity['current_section'] = $section;

        return $this->_set_current_stage($this->_stages[$section->stage_id]); // Activity saved

    }

    protected function _set_current_stage(Leyka_Settings_Stage $stage) {

        $this->_activity['current_stage'] = $stage;

        return $this->_save_activity();

    }

    protected function _set_current_section_by_id($section_full_id) {

        if( !$section_full_id ) {
            return $this;
        }

        $section = $this->get_component_by_id($section_full_id);
        if( !$section ) {
            return $this;
        }

        return $this->_set_current_section($section)->_set_current_stage($this->get_component_by_id($section->stage_id));

    }

    /**
     * @param $section_full_id string
     * @return boolean
     */
    protected function _is_section_completed($section_full_id) {

        /** @todo Throw some Exception if the given Section doesn't exists. */
        $section_full_id = trim($section_full_id);
        return !empty($this->_activity['history'][$section_full_id]);

    }

    protected function _get_setting_value($setting_name = null) {

        if($setting_name) {

            if(empty($this->_activity['history'])) {
                return null;
            }

            foreach($this->_activity['history'] as $section_full_id => $section_history_entry) {
                if(isset($section_history_entry['data'][$setting_name])) {
                    return $section_history_entry['data'][$setting_name];
                }
            }

            return null;

        } else {

            $res = [];
            foreach($this->_activity['history'] as $section_full_id => $section_history_entry) {
                $res = array_merge($res, $section_history_entry['data']);
            }

            return $res;

        }

    }

    protected function _add_history_entry(array $data = [], $section_full_id = false) {

        $data = empty($data) ? $this->get_current_section()->get_fields_values() : $data;
        $section_full_id = !$section_full_id ? $this->get_current_section()->full_id : trim($section_full_id);

        if(empty($this->_activity['history'][$section_full_id])) {
            $this->_activity['history'][$section_full_id] = [
                'navigation_position' => $this->_get_section_navigation_position($section_full_id),
                'data' => $data,
            ];
        } else {
            $this->_activity['history'][$section_full_id] = $this->_activity['history'][$section_full_id] + [
                'navigation_position' => $this->_get_section_navigation_position($section_full_id),
                'data' => $data,
            ];
        }

        return $this->_save_activity();

    }

    protected function _process_settings_values(array $blocks = null) {

        $blocks = $blocks ? $blocks : $this->get_current_section()->get_blocks();

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

    protected function _handle_settings_go_back($section_full_id = false, $delete_history = true) {

        if( !$section_full_id ) {
            $section_full_id = array_key_last($this->_activity['history']);
        } else {

            $section_found = false;
            foreach($this->_activity['history'] as $passed_section_full_id => $data) {
                if($section_full_id === $passed_section_full_id) {

                    $section_found = true;
                    break;

                }
            }

            $section_full_id = $section_found ? $section_full_id : false;

        }

        if($section_full_id) {
            $this->_set_current_section_by_id($section_full_id);
        } else {
            $this->_set_current_stage(reset($this->_stages))
                ->_set_current_section($this->get_current_stage()->init_section);
        }

        if( !!$delete_history ) { // Remove already passed keys from the Activity
            foreach(array_reverse($this->_activity['history'], true) as $passed_section_full_id => $data) {

                unset($this->_activity['history'][$passed_section_full_id]);

                if($passed_section_full_id === $section_full_id) {
                    break;
                }

            }
        }

        return $this->_save_activity();

    }

    /** The default implementation: the Wizard navigation roadmap created from existing Stages & Sections */
    protected function _init_navigation_data() {

        if( !$this->_stages ) {
            return;
        }

        $this->_navigation_data = [];

        foreach($this->_stages as $stage) { /** @var Leyka_Settings_Stage $stage */

            $sections = $stage->get_sections();
            if( !$sections ) {
                continue;
            }

            $sections_data = [];
            $all_sections_completed = true;
            foreach($sections as $section) { /** @var Leyka_Settings_Section $section */

                $section_completed = $this->_is_section_completed($section->full_id);

                if($all_sections_completed && !$section_completed) {
                    $all_sections_completed = false;
                }

                $sections_data[] = [
                    'section_id' => $section->full_id,
                    'title' => $section->title,
                    'url' => false,
                    'is_current' => $this->current_section->full_id === $section->full_id,
                    'is_completed' => $section_completed,
                ];

            }

            $this->_navigation_data[] = [
                'stage_id' => $stage->id,
                'title' => $stage->title,
                'url' => false,
                'is_current' => $this->current_stage_id === $stage->id, // True if the current Section belongs to the Stage
                'is_completed' => $all_sections_completed,
                'sections' => $sections_data,
            ];

        }

    }

    /** By default, each Section navigation position equals to it's full ID. */
    protected function _get_section_navigation_position($section_full_id = false) {
        return $section_full_id ? trim($section_full_id) : $this->get_current_section()->full_id;
    }

    /**
     * @param $navigation_data array
     * @param $navigation_position string
     * @return array
     */
    protected function _process_navigation_data($navigation_position = null, array $navigation_data = null) {

        $navigation_data = empty($navigation_data) ? $this->_navigation_data : $navigation_data;
        $navigation_position = empty($navigation_position) ? $this->current_section_full_id : trim($navigation_position);

        foreach($navigation_data as $stage_index => &$stage) {

            $navigation_position_parts = explode('-', $navigation_position);

            if($stage['stage_id'] === $navigation_position_parts[0]) {

                if(count($navigation_position_parts) === 1) {

                    $navigation_data[$stage_index]['is_current'] = true;
                    break;

                }

            }

            foreach(empty($stage['sections']) ? [] : $stage['sections'] as $section_index => $section) {

                $section_navigation_position = $stage['stage_id'].'-'.$section['section_id'];

                if($navigation_position === $section_navigation_position) {

                    $navigation_data[$stage_index]['sections'][$section_index]['is_current'] = true;
                    $navigation_data[$stage_index]['is_current'] = true;

                    break 2;

                } else {

                    $navigation_data[$stage_index]['sections'][$section_index]['is_completed'] = true;
                    $navigation_data[$stage_index]['sections'][$section_index]['url'] = add_query_arg(
                        'return_to', $section_navigation_position, remove_query_arg('return_to')
                    );

                }

            }

            $navigation_data[$stage_index]['is_completed'] = true;
            $navigation_data[$stage_index]['url'] = add_query_arg(
                'return_to', $stage['stage_id'], remove_query_arg('return_to')
            );

            if($navigation_position === $stage['stage_id'].'--') {
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

        $navigation_position = $this->_get_section_navigation_position();

        return $navigation_position ?
            $this->_process_navigation_data($navigation_position) :
            $this->_navigation_data;

    }

    /**
     * @param $component_id string
     * @param  $is_full_id boolean
     * @return mixed Leyka_Settings_Section, Leyka_Settings_Stage or null, if given component ID wasn't found
     */
    public function get_component_by_id($component_id, $is_full_id = true) {

        if( !$is_full_id ) {

            $stage = $this->get_current_stage();
            $stage_id = $component_id;

        } else {

            $component_id = explode('-', $component_id); // [0] is a Section ID, [1] is a Section ID

            if(count($component_id) < 2 && $component_id[0]) {
                return empty($this->_stages[ $component_id[0] ]) ? null : $this->_stages[ $component_id[0] ];
            }

            if(empty($this->_stages[$component_id[0]])) {
                return null;
            }

            $stage = $this->_stages[$component_id[0]];
            $stage_id = $component_id[1];

        }

        return $stage->get_section_by_id($stage_id);

    }

    public function handle_submit() {

        $this->_process_settings_values(); // Save all valid options on current section

        if( !$this->get_current_section()->is_valid() ) {

            foreach($this->get_current_section()->get_errors() as $component_id => $errors) {
                foreach($errors as $error) {
                    $this->_add_component_error($component_id, $error);
                }
            }

            return;

        }

        // Whole section settings handling:
        $settings_entered = $this->get_current_section()->get_fields_values();

        if($this->get_current_section()->has_handler()) {

            $result = call_user_func($this->get_current_section()->get_handler(), $settings_entered);

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

        do_action("leyka_process_section_settings-{$this->get_current_section()->full_id}", $settings_entered);

        if($this->has_common_errors()) { // Stay on current section
            return;
        }

        $this->_add_history_entry(); // Save the section data in the storage

        // Proceed to the next section:
        $next_section_full_id = $this->_get_next_section_id();
        if($next_section_full_id && $next_section_full_id !== true) {

            $section = $this->get_component_by_id($this->_get_next_section_id());
            if( !$section ) {

                $this->_add_common_error(new WP_Error('no_next_section', __('The next Wizard section is not found', 'leyka')));
                return;

            }

            $this->_set_current_section($section);

            do_action('leyka_settings_wizard-'.$this->_id.'-_section_init');

        }

    }

    public function section_init() {
    }

    /**
     * Sections branching incapsulation method. By default, it's next section in _sections array.
     *
     * @param $section_from Leyka_Settings_Section
     * @param $return_full_id boolean
     * @return mixed Either next section ID, or false (if non-existent section given), or true (if last section given).
     */
    protected function _get_next_section_id(Leyka_Settings_Section $section_from = null, $return_full_id = true) {

        $section_from = $section_from && is_a($section_from, 'Leyka_Settings_Section') ? $section_from : $this->current_section;
        $stage_from = $this->_stages[$section_from->stage_id];

        $is_next_section_target = false;
        $next_section = null;

        foreach($stage_from->sections as $section_id => $section) {

            if($is_next_section_target) {

                $next_section = $section;
                break;

            }

            if($section_from->stage_id == $stage_from->id && $section_id == $section_from->id) {
                $is_next_section_target = true;
            }

        }

        $next_stage = null;

        if( !$next_section ) {

            $is_next_stage_target = false;

            foreach($this->_stages as $stage_id => $stage) {

                if($is_next_stage_target) {

                    $next_stage = $stage;
                    $next_section = $stage->sections ? $stage->sections[ array_key_first($stage->sections) ] : false;
                    break;

                }

                if($stage->id == $stage_from->id) {
                    $is_next_stage_target = true;
                }

            }

        }

        if( !$next_section ) {
            $next_section = $section_from;
        }

        if( !$next_stage ) {
            $next_stage = $stage_from;
        }

        return $next_stage->id.'-'.$next_section->id;

    }

}