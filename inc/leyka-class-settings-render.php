<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    protected function __construct(array $params = array()) {
    }

    abstract public function renderStep(Leyka_Wizard_Step $step);

    abstract public function renderTextBlock(Leyka_Text_Block $block);
    abstract public function renderOptionBlock(Leyka_Option_Block $block);
    abstract public function renderContainerBlock(Leyka_Container_Block $block);
    abstract public function renderNavBlock(array $sections, Leyka_Wizard_Step $current_step);

    abstract public function renderNavChain(array $sections, Leyka_Wizard_Step $current_step);

}

class Leyka_Wizard_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

    public function renderNavChain(array $sections, Leyka_Wizard_Step $current_step) {
        // TODO: Implement renderNavChain() method.
    }

    public function renderNavBlock(array $sections, Leyka_Wizard_Step $current_step) {
        // TODO: Implement renderNavBlock() method.
    }

    public function renderStep(Leyka_Wizard_Step $step) {
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