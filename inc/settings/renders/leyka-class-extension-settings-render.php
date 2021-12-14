<?php if( !defined('WPINC') ) die;
/**
 * Leyka Options Render class.
 */

class Leyka_Extension_Settings_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

//    protected $_params = [];

    protected function _set_attributes() {
        $this->_id = 'options';
    }

    /** The main content layout wrapper method. */
    public function render_content() {

        $extension = $this->_controller->extension;?>

        <form id="leyka-options-form-<?php echo $this->_controller->id;?>" class="leyka-options-form <?php echo $this->_controller->id;?>" method="post" action="">

            <div class="single-settings-header">

                <div class="header-left">

                    <h2 class="wp-heading-inline"><?php echo $extension->title;?></h2>

                    <div class="meta-data">

                        <div class="item activation-status">
                            <span class="item-name"><?php _e('Status:', 'leyka');?></span>
                            <span class="item-value status-label <?php echo $extension->activation_status;?>">
                            <?php echo mb_strtolower($extension->activation_status_label);?>
                        </span>
                        </div>
                        <div class="item extension-version">
                            <span class="item-name"><?php _e('Extension version:', 'leyka');?></span>
                            <span class="item-value"><?php echo $extension->version;?></span>
                        </div>
                        <div class="item leyka-version">
                            <span class="item-name"><?php _e('Leyka version:', 'leyka');?></span>
                            <span class="item-value"><?php echo LEYKA_VERSION;?></span>
                        </div>
                        <div class="item author">

                            <span class="item-name"><?php _e('Author:', 'leyka');?></span>
                            <span class="item-value">
                            <?php if($extension->author_url) {?>
                                <a href="<?php echo $extension->author_url;?>" target="_blank" class="outer-link">
                                <?php echo $extension->author_name;?>
                            </a>
                            <?php } else {
                                echo __('Author:', 'leyka').' '.$extension->author_name;
                            }?>
                        </span>

                        </div>

                    </div>

                    <div class="extension-description"><?php echo $extension->settings_description;?></div>

                    <div class="common-errors <?php echo $this->_controller->has_common_errors() ? 'has-errors' : '';?>">
                        <?php $this->render_common_errors_area();?>
                    </div>

                </div>

                <div class="header-right">

                    <div class="module-logo-wrapper">
                        <div class="module-logo extension-logo">
                            <img src="<?php echo $extension->logo_url;?>" class="module-logo-pic extension-logo-pic" alt="">
                        </div>
                    </div>

                    <div class="extension-main-cta">

                        <?php $submit_data = $this->_controller->get_submit_data();?>

                        <input type="submit" class="button <?php echo $submit_data['activation_status'] === 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $submit_data['activation_status'];?>" name="<?php echo $submit_data['activation_status'] === 'active' ? 'leyka_deactivate_'.$this->_controller->id : 'leyka_activate_'.$this->_controller->id;?>" value="<?php echo $submit_data['activation_button_label'];?>">

                    </div>

                </div>

            </div>

            <div id="poststuff">
                <div class="metabox-holder columns-2">

                    <div class="postbox-container column-main">

                        <div class="leyka-options main-area"><?php $this->render_main_area();?></div>

                    </div>

                    <div class="postbox-container column-sidebar">

                        <?php if($extension->screenshots) {?>
                        <div class="lightbox">
                            <?php foreach($extension->screenshots as $thumbnail_url => $full_url) {?>
                            <a href="<?php echo esc_url($full_url);?>" target="_blank">
                                <img src="<?php echo esc_url($thumbnail_url);?>" alt="">
                            </a>
                            <?php }?>
                        </div>
                        <?php }?>

                        <?php if($extension->setup_description) {?>
                            <div class="setup-description"><?php echo $extension->setup_description;?></div>
                        <?php }

                        if($extension->docs_url) {?>
                            <div class="setup-user-manual-link">
                                <a class="outer-link" href="<?php echo $extension->docs_url;?>" target="_blank">
                                    <?php _e('Detailed manual', 'leyka');?>
                                </a>
                            </div>
                        <?php }?>

                    </div>

                </div>
            </div>

            <?php $this->render_footer();?>

        </form>

    <?php }

    public function render_common_errors_area() {
        foreach($this->_controller->get_common_errors() as $error) { /** @var WP_Error $error */?>
            <span><?php echo $error->get_error_message();?></span>
        <?php }
    }

    public function render_js_data() {
//        wp_localize_script('leyka-settings', 'metabox_areas', [])
    }

    public function render_main_area() {

        $this->render_js_data();
        $this->_add_sections_metaboxes();?>

        <div class="options-form-content">

            <?php $metaboxes_area_id = $this->_controller->id.'-options_main_area';?>
            <input type="hidden" class="leyka-support-metabox-area" value="<?php echo $metaboxes_area_id;?>">

            <?php do_meta_boxes($metaboxes_area_id, 'normal', null);?>

        </div>

        <?php $this->render_hidden_fields();

    }

    public function render_footer() {
        $this->render_submit_area();
    }

    public function render_hidden_fields() {?>
        <input type="hidden" value="<?php echo $this->_controller->extension->id;?>" id="leyka_extension_id">
    <?php }

    public function render_submit_area() {

        $submit_data = $this->_controller->get_submit_data();?>

        <div class="options-form-submits">

            <div class="delete-extension-error error" style="display: none;"></div>

            <span class="buttons">

                <?php if($this->_controller->extension->get_options_data()) { // Show "Save" submit only if there are options ?>
                <input type="submit" class="button button-primary button-small save-settings" name="leyka_settings_submit_<?php echo $this->_controller->id;?>" value="<?php _e('Save', 'leyka');?>">
                <?php }?>

                <input type="submit" class="button <?php echo $submit_data['activation_status'] === 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $submit_data['activation_status'];?>" name="<?php echo $submit_data['activation_status'] === 'active' ? 'leyka_deactivate_'.$this->_controller->id : 'leyka_activate_'.$this->_controller->id;?>" value="<?php echo $submit_data['activation_button_label'];?>">
            </span>

        </div>

    <?php }

    public function render_navigation_area() {
    }

    public function render_container_block(Leyka_Container_Block $block) {?>

        <div id="<?php echo $block->id;?>" class="settings-block container-block <?php echo $block->classes ? (is_array($block->classes) ? implode(' ', $block->classes) : esc_attr($block->classes)) : '';?>">

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
            <?php echo $block->has_custom_templated() || preg_match('/<p>/', $content) ? $content : '<p>'.$content.'</p>';?>
        </div>

    <?php }

    public function render_option_block(Leyka_Option_Block $block) {

        $option_info = leyka_options()->get_info_of($block->get_content());?>

        <div id="<?php echo $block->id;?>" class="settings-block option-block type-<?php echo $option_info['type']?> <?php echo $block->show_title ? '' : 'option-title-hidden';?> <?php echo $block->show_description ? '' : 'option-description-hidden';?> <?php echo $this->_controller->has_component_errors($block->id) ? 'has-errors' : '';?>" style="<?php echo $block->width < 1.0 ? 'width:'.(100.0*$block->width).'%;' : '';?>">

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
            if($section->blocks) {
                add_meta_box(
                    'leyka_'.$section->id,
                    $section->title,
                    [$this, 'render_section_metabox'],
                    $this->_controller->id.'-options_main_area',
                    'normal',
                    'default',
                    $section // We may pass several args to the metabox callback - just use an array here
                );
            }
        }

    }

}