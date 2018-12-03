<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    protected $_id;

    protected function __construct() {

        $this->_loadScripts();
        $this->_setAttributes();

    }

    /** @var Leyka_Settings_Controller */
    protected $_controller;

    abstract protected function _setAttributes();

    /**
     * @param Leyka_Settings_Controller $controller
     * @return Leyka_Settings_Render
     */
    public function setController(Leyka_Settings_Controller $controller) {

        $this->_controller = $controller;

        return $this;

    }

    abstract public function renderPage();

    abstract public function renderNavigationArea();
    abstract public function renderMainArea();

    abstract public function renderCommonErrorsArea();

    abstract public function renderSubtitleBlock(Leyka_Subtitle_Block $block);
    abstract public function renderTextBlock(Leyka_Text_Block $block);
    abstract public function renderOptionBlock(Leyka_Option_Block $block);
    abstract public function renderCustomSettingBlock(Leyka_Custom_Setting_Block $block);
    abstract public function renderContainerBlock(Leyka_Container_Block $block);

    abstract public function renderHiddenFields();
    abstract public function renderSubmitArea();

    protected function _loadScripts() {
//        wp_enqueue_script('leyka-settings-XXX', 'some/URL', array('jquery',), LEYKA_VERSION, true);
//        wp_localize_script('leyka-settings-XXX', 'leyka-settings-XXX', array());
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