<?php if( !defined('WPINC') ) die;
/**
 * Leyka Wizard Settings Render class.
 **/

class Leyka_Wizard_Render extends Leyka_Settings_Render {

    protected static $_instance = null;
    
    protected function _setAttributes() {
        $this->_id = 'wizard';
    }

    public function renderPage() {?>

        <div class="leyka-admin leyka-wizard wizard-<?php echo $this->_controller->id?> step-<?php echo $this->_controller->getCurrentStep()->id?>">
            <div class="nav-area">
                <?php $this->renderNavigationArea();?>
            </div>
            <div class="main-area">
                <?php $this->renderMainArea();?>
            </div>
        </div>

    <?php }

    public function renderCommonErrorsArea() {
        foreach($this->_controller->getCommonErrors() as $error) { /** @var WP_Error $error */ ?>
            <span><?php echo $error->get_error_message();?></span>
        <?php }
    }
    
    public function renderJSData() {
        leyka_localize_rich_html_text_tags();
    }

    public function renderMainArea() {
        
        $this->renderJSData();

        $current_step = $this->_controller->getCurrentStep();?>

        <div class="step-title">
            <h1 id="step-title-<?php echo $current_step->full_id?>" class="<?php echo $current_step->header_classes ? esc_attr($current_step->header_classes) : '';?>">
                <?php echo $current_step->title;?>
            </h1>
        </div>

        <input type="hidden" class="current-wizard-title" value="<?php echo $this->_controller->title;?>">
        <input type="hidden" class="current-section-title" value="<?php echo $this->_controller->getCurrentSection()->title;?>">
        <input type="hidden" class="current-step-title" value="<?php echo $current_step->title;?>">

        <div class="step-common-errors <?php echo $this->_controller->hasCommonErrors() ? 'has-errors' : '';?>">
            <?php $this->renderCommonErrorsArea();?>
        </div>

        <form id="leyka-settings-form-<?php echo $current_step->full_id;?>" <?php if($current_step->form_enctype):?>enctype="<?php echo $current_step->form_enctype?>"<?php endif?> class="leyka-settings-form leyka-wizard-step" method="post" action="<?php echo admin_url('admin.php?page=leyka_settings_new&screen='.$this->full_id);?>">
            <div class="step-content">
            <?php foreach($current_step->getBlocks() as $block) { /** @var $block Leyka_Settings_Block */

            /** @todo If-else here sucks. Make it a Factory Method */

                if(is_a($block, 'Leyka_Container_Block')) { /** @var $block Leyka_Container_Block */
                    $this->renderContainerBlock($block);
                } else if(is_a($block, 'Leyka_Text_Block')) { /** @var $block Leyka_Text_Block */
                    $this->renderTextBlock($block);
                } else if(is_a($block, 'Leyka_Subtitle_Block')) { /** @var $block Leyka_Subtitle_Block */
                    $this->renderSubtitleBlock($block);
                } else if(is_a($block, 'Leyka_Custom_Setting_Block')) { /** @var $block Leyka_Custom_Setting_Block */
                    $this->renderCustomSettingBlock($block);
                } else if(is_a($block, 'Leyka_Option_Block')) { /** @var $block Leyka_Option_Block */
                    $this->renderOptionBlock($block);
                }

            }?>
            </div>

            <?php $this->renderHiddenFields();?>

            <div class="step-submit">
            <?php $this->renderSubmitArea();?>
            </div>
        </form>

        <?php echo $this->renderHelpChat();?>

    <?php }

    public function renderHelpChat() {
        include(LEYKA_PLUGIN_DIR.'inc/settings-fields-templates/leyka-helpchat.php');
    }

    public function renderHiddenFields() {
    }

    public function renderSubmitArea() {

        $submits = $this->_controller->getSubmitData();?>

        <?php if($submits['next_url'] === true) {?>

        <input type="submit" class="step-next button button-primary" name="leyka_settings_submit_<?php echo $this->_controller->id;?>" value="<?php echo esc_attr($submits['next_label']);?>">

        <?php } else if(is_string($submits['next_url'])) {?>

        <a href="<?php echo esc_url($submits['next_url']);?>" class="wizard-custom-link">
            <?php echo esc_html($submits['next_label']);?>
        </a>

        <?php }

        if( !empty($submits['additional_label']) && !empty($submits['additional_url']) ) {?>
            <a href="<?php echo esc_url($submits['additional_url']);?>">
                <?php echo esc_html($submits['additional_label']);?>
            </a>
        <?php }?>

        <br>

        <?php if( !empty($submits['prev']) ) {?>
        <div class="sec-action">
            <input type="submit" class="step-prev link-sec" name="leyka_settings_prev_<?php echo $this->_controller->id;?>" value="<?php echo esc_attr($submits['prev']);?>">
        </div>
        <?php }?>

    <?php }

    public function renderNavigationArea() {

        $navigation_data = $this->_controller->getNavigationData();?>

        <div class="nav-chain">
            <div class="nav-line">

            <?php foreach($navigation_data as $section_index => $section) {?>

                <div class="nav-section <?php echo !empty($section['is_current']) ? 'active' : (empty($section['is_completed']) ? '' : 'done');?>" data-section-title="<?php echo esc_attr($section['title']);?>">

                    <div class="nav-section-title">

                        <?php if( !empty($section['is_completed']) ) {?>
                        <div class="nav-section-marker">
                            <a href="<?php echo $section['url'];?>">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-ok.svg" alt="">
                            </a>
                        </div>

                        <a href="<?php echo $section['url'];?>"><?php echo esc_html($section['title']);?></a>

                        <?php } else {?>

                        <div class="nav-section-marker">
                            <?php echo $section_index + 1;?>
                        </div>
                            <?php echo esc_html($section['title']);

                        }?>

                    </div>

                    <?php if(empty($section['is_completed']) && !empty($section['steps'])) {?>
                        <div class="nav-steps">

                        <?php foreach($section['steps'] as $step) {?>

                            <div class="nav-step <?php if( !empty($step['is_current']) ) {?>active<?php } else if( !empty($step['is_completed']) ) {?>done<?php }?>">

                            <?php if( !empty($step['is_completed']) ) {?>
                                <a href="<?php echo $step['url'];?>"><?php echo esc_html($step['title']);?></a>
                            <?php } else {
                                echo esc_html($step['title']);
                            }?>

                            </div>

                        <?php }?>

                        </div>
                    <?php }?>

                </div>

            <?php }?>

            </div>

        </div>

        <a href="<?php echo $this->getExitURL();?>" class="nav-section nav-exit">
            <div class="nav-section-title">
                <div class="nav-section-marker"></div>
                <?php _e('Exit installation', 'leyka');?>
            </div>
        </a>
        
        <div class="leyka-logo">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/nav-logo.svg" alt="">
        </div>

    <?php }

    public function renderContainerBlock(Leyka_Container_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block container-block">

            <?php $entry_width = $block->entry_width ? (100.0*($block->entry_width - 0.06 * $block->entry_width)).'%' : false;

            foreach($block->getContent() as $sub_block) { // $sub_block_index => $sub_block ?>

                <div class="container-entry" <?php echo $entry_width ? 'style="flex-basis: '.$entry_width.';"' : '';?>>

                <?php if(is_a($sub_block, 'Leyka_Text_Block')) { /** @var $sub_block Leyka_Text_Block */
                    $this->renderTextBlock($sub_block);
                } else if(is_a($sub_block, 'Leyka_Custom_Setting_Block')) { /** @var $sub_block Leyka_Custom_Setting_Block */
                    $this->renderCustomSettingBlock($sub_block);
                } else if(is_a($sub_block, 'Leyka_Option_Block')) { /** @var $sub_block Leyka_Option_Block */
                    $this->renderOptionBlock($sub_block);
                }?>

                </div>

            <?php }?>

        </div>

    <?php }

    public function renderSubtitleBlock(Leyka_Subtitle_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block subtitle-block">
            <h2><?php echo $block->getContent();?></h2>
        </div>

    <?php }

    public function renderTextBlock(Leyka_Text_Block $block) {

        $content = $block->getContent();?>

        <div id="<?php echo $block->id;?>" class="settings-block text-block">
            
            <?php if($block->hasCustomTemplated() || preg_match("/<p>/", $content)):?>
                <?php echo $content?>
            <?php else: ?>
                <p><?php echo $content?></p>
            <?php endif?>
        </div>

    <?php }

    public function renderOptionBlock(Leyka_Option_Block $block) {

        $option_info = leyka_options()->get_info_of($block->getContent());?>

        <div id="<?php echo $block->id;?>" class="settings-block option-block type-<?php echo $option_info['type']?> <?php echo $block->show_title ? '' : 'option-title-hidden';?> <?php echo $block->show_description ? '' : 'option-description-hidden';?> <?php echo $this->_controller->hasComponentErrors($block->id) ? 'has-errors' : '';?>">
            <?php do_action("leyka_render_{$option_info['type']}", $block->getContent(), $option_info);?>
            <div class="field-errors <?php echo $this->_controller->hasComponentErrors($block->id) ? 'has-errors' : '';?>">
                <?php foreach($this->_controller->getComponentErrors($block->id) as $error) { /** @var $error WP_Error */?>
                    <span><?php echo $error->get_error_message();?></span>
                <?php }?>
            </div>
        </div>

    <?php }

    public function renderCustomSettingBlock(Leyka_Custom_Setting_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block custom-block <?php echo $block->is_standard_field_type ? 'option-block' : '';?> <?php echo $this->_controller->hasComponentErrors($block->id) ? 'has-errors' : '';?> <?php echo $block->field_type;?>">

            <?php echo $block->getContent();?>
            <div class="field-errors <?php echo $this->_controller->hasComponentErrors($block->id) ? 'has-errors' : '';?>">
                <?php foreach($this->_controller->getComponentErrors($block->id) as $error) { /** @var $error WP_Error */?>
                    <span><?php echo $error->get_error_message();?></span>
                <?php }?>
            </div>

        </div>

    <?php }
    
    private function getExitURL() {
        
        if($this->_controller->id && $this->_controller->id != 'init') {
            $exit_url = admin_url('/admin.php?page=leyka_settings&stage=payment&gateway=' . $this->_controller->id);
        }
        else {
            $exit_url = admin_url('/admin.php?page=leyka_settings');
        }
        
        return $exit_url;
    }

}