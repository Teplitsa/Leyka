<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    /** @var Leyka_Settings_Controller */
    protected $_controller;

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

    abstract public function renderTextBlock(Leyka_Text_Block $block);
    abstract public function renderOptionBlock(Leyka_Option_Block $block);
    abstract public function renderContainerBlock(Leyka_Container_Block $block);

    abstract public function renderSubmitArea();

//    abstract public function renderNavChain(array $sections, Leyka_Wizard_Step $current_step);

}

class Leyka_Wizard_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

    /**
     * @param Leyka_Settings_Controller $controller
     * @return Leyka_Settings_Render
     */
    public function setController(Leyka_Settings_Controller $controller) {

        $this->_controller = $controller;

        return $this;

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

    public function renderMainArea() {

        echo '<pre>'.print_r('Main area here', 1).'</pre>';
        $current_step = $this->_controller->getCurrentStep();?>

        <div class="step-title"><h2><?php echo $current_step->title;?></h2></div>

        <div class="step-content">
        <?php foreach($current_step->getBlocks() as $block) { /** @var $block Leyka_Settings_Block */

        /** @todo If-else here sucks. Make it a Factory Method */

            if(is_a($block, 'Leyka_Container_Block')) {

                echo '<pre>Container:'.print_r($block, 1).'</pre>';

            } else if(is_a($block, 'Leyka_Text_Block')) {?>

            <div class="settings-block text-block"><?php echo $block->getContent();?></div>

            <?php } else if(is_a($block, 'Leyka_Option_Block')) {

                echo '<pre>Option:'.print_r($block, 1).'</pre>';

            }

        }?>
        </div>

        <div class="step-submit">
        <?php $this->renderSubmitArea();?>
        </div>

    <?php }

    public function renderSubmitArea() {?>

        <div class="submit"></div>

    <?php }

    public function renderNavigationArea() {

        echo '<pre>'.print_r('Nav area here', 1).'</pre>';

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