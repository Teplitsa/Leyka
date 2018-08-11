<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    protected $_id;

    protected function __construct() {

        $this->_loadCssJs();
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

    abstract public function renderStep();

    abstract public function renderCommonErrorsArea();

    abstract public function renderTextBlock(Leyka_Text_Block $block);
    abstract public function renderOptionBlock(Leyka_Option_Block $block);
    abstract public function renderContainerBlock(Leyka_Container_Block $block);

    abstract public function renderHiddenFields();
    abstract public function renderSubmitArea();

    protected function _loadCssJs() {

        wp_enqueue_script(
            'leyka-settings-render',
            LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
            array('jquery',),
            LEYKA_VERSION,
            true
        );
//        add_action('wp_enqueue_scripts', array($this, 'localize_scripts')); // wp_footer

        wp_enqueue_style(
            'leyka-settings',
            LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css',
            array(),
            LEYKA_VERSION
        );

        do_action('leyka_settings_enqueue_scripts');

    }

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'full_id': return $this->_id.'-'.$this->_controller->id;
            default:
                return null;
        }
    }

//    abstract public function renderNavChain(array $sections, Leyka_Wizard_Step $current_step);

}

class Leyka_Wizard_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

    protected function _setAttributes() {
        $this->_id = 'wizard';
    }

    public function renderPage() {?>

        <div class="leyka-wizard">
            <div class="nav-area">
                <?php $this->renderNavigationArea();?>
            </div>
            <div class="main-area">
                <?php $this->renderMainArea();?>
            </div>
        </div>

    <?php }

    public function renderCommonErrorsArea() {
        foreach($this->_controller->getCommonErrors() as $error) { /** @var WP_Error $error */
            echo '<pre>'.print_r($error->get_error_message(), 1).'</pre>';
        }
    }

    public function renderMainArea() {

        $current_step = $this->_controller->getCurrentStep();?>

        <div class="step-title"><h2><?php echo $current_step->title;?></h2></div>

        <div class="step-common-errors">
            <?php $this->renderCommonErrorsArea();?>
        </div>

        <form id="leyka-settings-form-<?php echo $current_step->full_id;?>" class="leyka-settings-form leyka-wizard-step" method="post" action="<?php echo admin_url('admin.php?page=leyka_settings_new&screen='.$this->full_id);?>">
            <div class="step-content">
            <?php foreach($current_step->getBlocks() as $block) { /** @var $block Leyka_Settings_Block */

            /** @todo If-else here sucks. Make it a Factory Method */

                if(is_a($block, 'Leyka_Container_Block')) {

                    echo '<pre>Container:'.print_r($block, 1).'</pre>';

                } else if(is_a($block, 'Leyka_Text_Block')) {?>

                <div class="settings-block text-block"><p><?php echo $block->getContent();?></p></div>

                <?php } else if(is_a($block, 'Leyka_Option_Block')) {

                    $option_info = leyka_options()->get_info_of($block->getContent());?>

                <div class="settings-block option-block">
                    <?php do_action("leyka_render_{$option_info['type']}", $block->getContent(), $option_info);?>
                    <div class="field-errors">
                    <?php $block_errors = $this->_controller->getComponentErrors($block->id);
                    if($block_errors) {
                        foreach($block_errors->get_error_messages() as $error_message) {
                            echo '<p>'.$error_message.'</p>';
                        }
                    }?>
                    </div>
                </div>

                <?php }

            }?>
            </div>

            <?php $this->renderHiddenFields();?>

            <div class="step-submit">
            <?php $this->renderSubmitArea();?>
            </div>
        </form>

    <?php }

    public function renderHiddenFields() {?>

<!--        <input type="hidden" name="next-step-full-id" value="--><?php //echo $this->_controller->next_step_full_id;?><!--">-->

    <?php }

    public function renderSubmitArea() {

        $submits = $this->_controller->getSubmitSettings();?>

        <?php if($submits['next_url'] === true && $this->_controller->next_step_full_id !== true) {?>

        <input type="submit" class="step-next" name="leyka_settings_submit_<?php echo $this->_controller->id;?>" value="<?php echo $submits['next_label'];?>">

        <?php } else if($this->_controller->next_step_full_id === true && is_string($submits['next_url'])) {?>

        <a href="<?php echo esc_url($submits['next_url']);?>" class="wizard-custom-link"><?php echo $submits['next_label'];?></a>

        <?php }?>

        <br>

        <?php if( !empty($submits['prev']) ) {?>
        <input type="submit" class="step-prev" name="leyka_settings_prev_<?php echo $this->_controller->id;?>" value="<?php echo $submits['prev'];?>">
        <?php }?>

    <?php }

    public function renderNavigationArea() {
        echo 'Nav Chain here somewhere';
    }

    public function renderNavChain() {
        // TODO: Implement renderNavChain() method.
    }

    public function renderStep() {
        // TODO: Implement renderStep() method.
    }

    public function renderContainerBlock(Leyka_Container_Block $block) {
        // TODO: Implement renderContainerBlock() method.
    }

    public function renderTextBlock(Leyka_Text_Block $block) {
        // TODO: Implement renderTextBlock() method.
    }

    public function renderOptionBlock(Leyka_Option_Block $block) {
        // TODO: Implement renderOptionBlock() method.
    }

}