<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    protected $_id;

    protected function __construct() {

        $this->_load_scripts();
        $this->_set_attributes();

    }

    /** @var Leyka_Settings_Controller */
    protected $_controller;

    abstract protected function _set_attributes();

    /**
     * @param Leyka_Settings_Controller $controller
     * @return Leyka_Settings_Render
     */
    public function set_controller(Leyka_Settings_Controller $controller) {

        $this->_controller = $controller;

        return $this;

    }

    abstract public function render_content();

    abstract public function render_navigation_area();
    abstract public function render_main_area();

    abstract public function render_common_errors_area();

    abstract public function render_subtitle_block(Leyka_Subtitle_Block $block);
    abstract public function render_text_block(Leyka_Text_Block $block);
    abstract public function render_option_block(Leyka_Option_Block $block);
    abstract public function render_custom_setting_block(Leyka_Custom_Setting_Block $block);
    abstract public function render_container_block(Leyka_Container_Block $block);

    abstract public function render_hidden_fields();
    abstract public function render_submit_area();

    protected function _load_scripts() {
//        wp_enqueue_script('leyka-settings-XXX', 'some/URL', ['jquery',], LEYKA_VERSION, true);
//        wp_localize_script('leyka-settings-XXX', 'leyka-settings-XXX', []);
        /** WARNING: CSS files loaded here will appear only in page footer. */
        do_action('leyka_settings_render_enqueue_scripts', $this->_id);
    }

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'full_id': return $this->_id.'-'.$this->_controller->id;
            default:
                return null;
        }
    }

}