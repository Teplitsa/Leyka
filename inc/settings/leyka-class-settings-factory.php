<?php if( !defined('WPINC') ) die;
/**
 * Leyka settings controller - the root class.
 **/

class Leyka_Settings_Factory extends Leyka_Singleton { // Each descendant is a concrete wizard

    protected static $_instance = null;

    protected function __construct() {

        // Basic Controller class:
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/controllers/leyka-class-settings-controller.php');

        // Controller components classes:
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-admin-template-tags.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-block.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-step.php');
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/leyka-class-settings-section.php');

        // Basic Render class:
        require_once(LEYKA_PLUGIN_DIR.'inc/settings/renders/leyka-class-settings-render.php');

    }

    /**
     * @param string $controller_id
     * @return Leyka_Settings_Controller
     * @throws Exception With codes 500-509
     */
    public function getController($controller_id) {

        $controller_id = trim(esc_attr($controller_id));

        // Concrete Controller class:
        $file_path = LEYKA_PLUGIN_DIR.'inc/settings/controllers/leyka-class-'.mb_strtolower($controller_id)
            .'-settings-controller.php';

        if(file_exists($file_path)) {
            require_once($file_path);
        } else {
            throw new Exception(
                sprintf(
                    __("Settings Factory error: Can't find Settings Render script by given render ID (%s, %s)"),
                    $controller_id, $file_path
                ), 500
            );
        }

        // Require the needed Settings Controller script...
        switch($controller_id) {
            case 'init': return Leyka_Init_Wizard_Settings_Controller::getInstance();
            case 'cp': return Leyka_Cp_Wizard_Settings_Controller::getInstance();
            case 'yandex': return Leyka_Yandex_Wizard_Settings_Controller::getInstance();
            default: throw new Exception(
                sprintf(__('Settings Factory error: wrong Settings Controller ID given (%s)'), $controller_id), 501
            );
        }

    }

    /**
     * @param string $render_id
     * @return Leyka_Settings_Render
     * @throws Exception With codes 510-519
     */
    public function getRender($render_id) {

        $render_id = trim(esc_attr($render_id));

        // Concrete Render class:
        $file_path = LEYKA_PLUGIN_DIR.'inc/settings/renders/leyka-class-'.mb_strtolower($render_id).'-settings-render.php';

        if(file_exists($file_path)) {
            require_once($file_path);
        } else {
            throw new Exception(
                sprintf(
                    __("Settings Factory error: Can't find Settings Render script by given render ID (%s, %s)"),
                    $render_id, $file_path
                ), 510
            );
        }

        switch($render_id) {
            case 'wizard': return Leyka_Wizard_Render::getInstance();
            default: throw new Exception(
                sprintf(__('Settings Factory error: wrong Settings Render ID given (%s)'), $render_id), 511
            );
        }

    }

}