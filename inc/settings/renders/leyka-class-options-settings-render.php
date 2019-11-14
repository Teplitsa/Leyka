<?php if( !defined('WPINC') ) die;
/**
 * Leyka Options Render class.
 */

class Leyka_Options_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

//    protected $_params = array();

    protected function _set_attributes() {
        $this->_id = 'options';
    }

    /** The main content layout wrapper method. */
    public function render_content() {?>

        <div class="leyka-options main-area">
            <?php $this->render_main_area();?>
        </div>

    <?php }

    public function render_common_errors_area() {
        foreach($this->_controller->get_common_errors() as $error) { /** @var WP_Error $error */ ?>
            <span><?php echo $error->get_error_message();?></span>
        <?php }
    }
    
    public function render_js_data() {
//        wp_localize_script('leyka-settings', 'metabox_areas', array())
    }

    public function render_main_area() {

        $this->render_js_data();
        $this->_add_sections_metaboxes();?>

<!--        <input type="hidden" class="current-wizard-title" value="--><?php //echo $this->_controller->title;?><!--">-->
<!--        <input type="hidden" class="current-section-title" value="--><?php //echo $this->_controller->get_current_stage()->title;?><!--">-->
<!--        <input type="hidden" class="current-step-title" value="--><?php //echo $current_section->title;?><!--">-->

        <div class="common-errors <?php echo $this->_controller->has_common_errors() ? 'has-errors' : '';?>">
            <?php $this->render_common_errors_area();?>
        </div>

        <?php // $stage = $this->_controller->get_current_stage();

        do_meta_boxes($this->_controller->id.'-options_main_area', 'normal', null);

//        foreach($stage->get_sections() as $section) {
//
//        }?>

<!--        <form id="leyka-options-form" --><?php //echo $current_section->form_enctype ? 'echtype="'.$current_section->form_enctype.'"' : '';?><!-- class="leyka-options-form" method="post" action="--><?php //echo leyka_get_current_url();?><!--">-->
<!--            <div class="options-form-content">-->
<!--            --><?php //foreach($current_section->get_blocks() as $block) { /** @var $block Leyka_Settings_Block */
//
//                if(is_a($block, 'Leyka_Container_Block')) { /** @var $block Leyka_Container_Block */
//                    $this->render_container_block($block);
//                } else if(is_a($block, 'Leyka_Text_Block')) { /** @var $block Leyka_Text_Block */
//                    $this->render_text_block($block);
//                } else if(is_a($block, 'Leyka_Subtitle_Block')) { /** @var $block Leyka_Subtitle_Block */
//                    $this->render_subtitle_block($block);
//                } else if(is_a($block, 'Leyka_Custom_Setting_Block')) { /** @var $block Leyka_Custom_Setting_Block */
//                    $this->render_custom_setting_block($block);
//                } else if(is_a($block, 'Leyka_Option_Block')) { /** @var $block Leyka_Option_Block */
//                    $this->render_option_block($block);
//                }
//
//            }?>
<!--            </div>-->
<!---->
<!--            --><?php //$this->render_hidden_fields();?>
<!---->
<!--            <div class="options-submit">--><?php //$this->render_submit_area();?><!--</div>-->
<!---->
<!--        </form>-->
        
        <?php echo $this->render_footer();?>

    <?php }

    public function render_footer() {
//        leyka_show_admin_footer();
    }
    
    public function render_hidden_fields() {
    }

    public function render_submit_area() {

        $submits = $this->_controller->get_submit_data();?>

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

    public function render_navigation_area() {
    }

    public function render_container_block(Leyka_Container_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block container-block">

            <?php $entry_width = $block->entry_width ? (100.0*($block->entry_width - 0.06 * $block->entry_width)).'%' : false;

            foreach($block->get_content() as $sub_block) { // $sub_block_index => $sub_block ?>

                <div class="container-entry" <?php echo $entry_width ? 'style="flex-basis: '.$entry_width.';"' : '';?>>

                <?php if(is_a($sub_block, 'Leyka_Text_Block')) { /** @var $sub_block Leyka_Text_Block */
                    $this->render_text_block($sub_block);
                } else if(is_a($sub_block, 'Leyka_Custom_Setting_Block')) { /** @var $sub_block Leyka_Custom_Setting_Block */
                    $this->render_custom_setting_block($sub_block);
                } else if(is_a($sub_block, 'Leyka_Option_Block')) { /** @var $sub_block Leyka_Option_Block */
                    $this->render_option_block($sub_block);
                }?>

                </div>

            <?php }?>

        </div>

    <?php }

    public function render_subtitle_block(Leyka_Subtitle_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block subtitle-block">
            <h2><?php echo $block->get_content();?></h2>
        </div>

    <?php }

    public function render_text_block(Leyka_Text_Block $block) {

        $content = $block->get_content();?>

        <div id="<?php echo $block->id;?>" class="settings-block text-block">
            
            <?php if($block->has_custom_templated() || preg_match("/<p>/", $content)):?>
                <?php echo $content?>
            <?php else: ?>
                <p><?php echo $content?></p>
            <?php endif?>
        </div>

    <?php }

    public function render_option_block(Leyka_Option_Block $block) {

        // leyka_support_metaboxes('dashboard_page_leyka_donor_info'); // JS

        $option_info = leyka_options()->get_info_of($block->get_content());?>

        <div id="<?php echo $block->id;?>" class="settings-block option-block type-<?php echo $option_info['type']?> <?php echo $block->show_title ? '' : 'option-title-hidden';?> <?php echo $block->show_description ? '' : 'option-description-hidden';?> <?php echo $this->_controller->has_component_errors($block->id) ? 'has-errors' : '';?>">

            <?php do_action("leyka_render_{$option_info['type']}", $block->get_content(), $option_info);?>

            <div class="field-errors <?php echo $this->_controller->has_component_errors($block->id) ? 'has-errors' : '';?>">
                <?php foreach($this->_controller->get_component_errors($block->id) as $error) { /** @var $error WP_Error */?>
                    <span><?php echo $error->get_error_message();?></span>
                <?php }?>
            </div>

        </div>

    <?php }

    public function render_custom_setting_block(Leyka_Custom_Setting_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block custom-block <?php echo $block->is_standard_field_type ? 'option-block' : '';?> <?php echo $this->_controller->has_component_errors($block->id) ? 'has-errors' : '';?> <?php echo $block->field_type;?>">

            <?php echo $block->get_content();?>
            <div class="field-errors <?php echo $this->_controller->has_component_errors($block->id) ? 'has-errors' : '';?>">
                <?php foreach($this->_controller->get_component_errors($block->id) as $error) { /** @var $error WP_Error */?>
                    <span><?php echo $error->get_error_message();?></span>
                <?php }?>
            </div>

        </div>

    <?php }

    public function render_section_metabox($post, $args) {

        $section = $args['args']; /** @var $section Leyka_Settings_Section */

        foreach($section->get_blocks() as $block) { /** @var $block Leyka_Settings_Block */

            if(is_a($block, 'Leyka_Container_Block')) { /** @var $block Leyka_Container_Block */
                $this->render_container_block($block);
            } else if(is_a($block, 'Leyka_Text_Block')) { /** @var $block Leyka_Text_Block */
                $this->render_text_block($block);
            } else if(is_a($block, 'Leyka_Subtitle_Block')) { /** @var $block Leyka_Subtitle_Block */
                $this->render_subtitle_block($block);
            } else if(is_a($block, 'Leyka_Custom_Setting_Block')) { /** @var $block Leyka_Custom_Setting_Block */
                $this->render_custom_setting_block($block);
            } else if(is_a($block, 'Leyka_Option_Block')) { /** @var $block Leyka_Option_Block */
                $this->render_option_block($block);
            }

        }

    }

    protected function _add_sections_metaboxes() {

        foreach($this->_controller->get_current_stage()->get_sections() as $section) {
            add_meta_box(
                'leyka_'.$section->id,
                $section->title,
                array($this, 'render_section_metabox'),
                $this->_controller->id.'-options_main_area',
                'normal',
                'default',
                $section // We may pass several args to the metabox callback - just use an array here
            );
        }

    }

}