<?php if( !defined('WPINC') ) die;
/**
 * Leyka options controller - the base class.
 **/

class Leyka_Extension_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;

    /** @var $_stages array of Leyka_Settings_Section objects */
    protected $_stages;

    /** @var Leyka_Extension */
    protected $_extension;
    protected $_options;

    public static function get_controller($controller_id) {
        return self::get_instance();
    }

    protected function __construct(array $params = []) {

        if( !empty($params['extension']) && is_a($params['extension'], 'Leyka_Extension') ) {

            $this->_extension = $params['extension'];
            $this->_options = $this->_extension->get_options_data();

        }

        $this->_id = 'extension';

        $this->_load_frontend_scripts();
        $this->_set_attributes();
        $this->_set_stages();

        // Triggered on any try to activate/deactivate an Extension:
        add_action('leyka_settings_submit_'.$this->_id, [$this, 'handle_submit']);
        add_action('leyka_activate_extension_'.$this->_extension->id, [$this, 'handle_activation']);
        add_action('leyka_deactivate_extension_'.$this->_extension->id, [$this, 'handle_deactivation']);
        add_filter('leyka_extension_validate_activation', [$this->_extension, 'validate_activation']);

        // Triggered if Extension is really activated/deactivated (all checks are passed):
        add_action('leyka_extension_activation', [$this->_extension, 'activate']);
        add_action('leyka_extension_deactivation', [$this->_extension, 'deactivate']);

        $this->_handle_settings_submit();

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

        $stage = new Leyka_Settings_Stage($this->_id, ''); // There is only one stage by default

        $section_check = reset($this->_options); // If there are no section set in options, add the default "main options" one
        if(empty($section_check['section'])) {
            $default_section = new Leyka_Settings_Section('main_options', $stage->id, __('Main options', 'leyka'));
        }

        foreach($this->_options as $option_id => $params) { // Create Settings Sections & Blocks from the options

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

        } // Settings Blocks added

        if( !empty($default_section) ) { // No section set in options - add the default "main options" one
            $default_section->add_to($stage);
        }

        $this->_stages[$stage->id] = $stage;

    }

    public function __get($name) {
        switch($name) {
            case 'extension':
                return $this->_extension;
            default:
                return parent::__get($name);
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
        } else if( !empty($_POST['leyka_activate_extension'])  && $this->_extension ) {
            do_action('leyka_activate_extension_'.$this->_extension->id);
        } else if( !empty($_POST['leyka_deactivate_extension']) && $this->_extension ) {
            do_action('leyka_deactivate_extension_'.$this->_extension->id);
        }

    }

    protected function _process_settings_values(array $blocks = null) {

        if( !$blocks ) {

            $blocks = [];

            foreach($this->get_current_stage()->get_sections() as $section) { /** @var $section Leyka_Settings_Section */
                $blocks = array_merge($blocks, $section->get_blocks());
            }

        }

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

    /**
     * All Options Sections are disaplayed on one page, so the method isn't needed.
     *
     * @return Leyka_Settings_Section
     */
    public function get_current_section() {

        $sections = $this->get_current_stage()->get_sections();
        return reset($sections);

    }

    /**
     * There are only one Stage for Options disaplyed, so just return it.
     *
     * @return Leyka_Settings_Stage
     */
    public function get_current_stage() {
        return reset($this->_stages);
    }

    public function get_submit_data($component = null) {
        return [
            'activation_status' => $this->_extension->activation_status,
            'activation_button_label' => leyka_get_extension_activation_button_label($this->_extension),
        ];
    }

    public function get_navigation_data() {
    }

    public function handle_submit() {

        $this->_process_settings_values(); // Save all valid options on all current stage

        if( !$this->get_current_section()->is_valid() ) {

            foreach($this->get_current_section()->get_errors() as $component_id => $errors) {
                foreach($errors as $error) {
                    $this->_add_component_error($component_id, $error);
                }
            }

            return;

        }

        // Here we'd check validity of stage settings as a whole

    }

    public function handle_activation() {

//        $this->handle_submit(); // Mb, we shouldn't auto-save Extension settings before activation
        // UPD: We certainly shouldn't. It leads to some errors if Extension has a custom multi-valued items field with Campaigns related subfields

        $extension_activation_valid = $this->_extension->activation_valid(); // Let Extension check it's all green for activation
        if($extension_activation_valid !== true) {

            if(is_wp_error($extension_activation_valid)) { /** @var $extension_activation_valid WP_Error */
                $this->_add_common_error($extension_activation_valid);
            } else if(is_array($extension_activation_valid)) {
                foreach($extension_activation_valid as $error) {
                    $this->_add_common_error($error);
                }
            }

        }

        if($this->has_common_errors() || $this->has_component_errors()) {
            return false; //$this->_add_common_error(new WP_Error('activation_failed', __('Cannot activate the extension - settings errors found.', 'leyka')));
        }

        $extensions_active = leyka_options()->opt('extensions_active');
        if( !in_array($this->_extension->id, $extensions_active) ) {

            $extensions_active[] = $this->_extension->id;
            leyka_options()->opt('extensions_active', $extensions_active);

            do_action('leyka_extension_activation');

        }

        return NULL;

    }

    public function handle_deactivation() {

        $extensions_active = leyka_options()->opt('extensions_active');
        $index_to_remove = array_search($this->_extension->id, $extensions_active);

        if($index_to_remove !== false) {
            unset($extensions_active[$index_to_remove]);
        } else {
            /** @todo Trying to deactivate an Extension that's not found in 'extensions_active' option value - throw some Ex? */
        }

        leyka_options()->opt('extensions_active', $extensions_active);

        do_action('leyka_extension_deactivation');

    }

    /**
     * A recursive method to create Leyka_Settings_Block object from given option data.
     *
     * @param $option_id string An ID of an option/container to add to the section as a block.
     * @param $params array
     * @return Leyka_Settings_Block
     * @throws
     */
    protected function _create_settings_block_from_option($option_id, $params) {

        if( !$option_id || empty($params['type']) ) {
            throw new Exception(__("Can't create Settings Block - no option ID given", 'leyka'), 530);
        }

        if($params['type'] === 'container' && !empty($params['entries'])) {

            $settings_block = new Leyka_Container_Block(['id' => $this->_id.'_container-'.random_int(0, 1000)]);
            if( !empty($params['classes']) ) {
                $settings_block->classes = $params['classes'];
            }

            foreach($params['entries'] as $inner_option_id => $inner_params) {

                try {
                    $settings_block->add_block($this->_create_settings_block_from_option($inner_option_id, $inner_params));
                } catch(Exception $ex) {
                    // ...
                }

            }

        } else if(stristr($params['type'], 'custom_') !== false) {

            $settings_block = new Leyka_Custom_Setting_Block([
                'id' => $this->_id.'_'.$option_id,
                'custom_setting_id' => $option_id,
                'field_type' => $params['type'],
                'rendering_type' => apply_filters('leyka_custom_setting_rendering_type', 'callback', $option_id),
            ]);

            if( !leyka_options()->option_exists($option_id) ) {
                leyka_options()->add_option($option_id, $params['type'], $params);
            }

        } else {

            $block_params = ['id' => $this->_id.'_'.$option_id, 'option_id' => $option_id];
            if( !empty($params['width']) && $params['width'] > 0 && $params['width'] < 1.0 ) {
                $block_params['width'] = $params['width'];
            }

            $settings_block = new Leyka_Option_Block($block_params);

            if( !leyka_options()->option_exists($option_id) ) {
                leyka_options()->add_option($option_id, $params['type'], $params);
            }

        }

        return $settings_block;

    }

}