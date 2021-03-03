<?php if( !defined('WPINC') ) die;

// Sections:
add_action('leyka_render_section', 'leyka_render_section_area');
function leyka_render_section_area($section){?>

    <div class="leyka-options-section <?php echo $section['is_default_collapsed'] ? 'collapsed' : '';?> <?php echo empty($section['tabs']) ? '' : 'with-tabs';?>" id="<?php echo $section['name'];?>">
        <div class="header"><h3><?php echo esc_attr($section['title']);?></h3></div>
        <div class="content">

            <?php if( !empty($section['description']) ) {?>
                <div class="section-description"><?php echo $section['description'];?></div>
            <?php }

            if( !empty($section['content_area_render']) && function_exists($section['content_area_render']) ) {
                call_user_func($section['content_area_render'], $section);
            } else {
                foreach($section['options'] as $option) {
    
                    $option_info = leyka_options()->get_info_of($option);
                    do_action("leyka_render_{$option_info['type']}", $option, $option_info);
    
                }
            }

            if( !empty($section['is_separate_sections_forms']) ) {?>
                <p class="submit">
                    <input type="submit" name="leyka_settings_<?php echo $section['current_stage'];?>_submit" class="button-primary" <?php echo empty($section['action_button']['id']) ? '' : 'id="'.esc_attr($section['action_button']['id']).'"';?> value="<?php echo empty($section['action_button']['title']) ? __('Save', 'leyka') : $section['action_button']['title'];?>">
                </p>
            <?php }?>

        </div>
    </div>

<?php }

// Text fields:
add_action('leyka_render_text', 'leyka_render_text_field', 10, 2);
function leyka_render_text_field($option_id, $data){
    
    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-field-inner-wrapper leyka-text-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" data-field-title="<?php echo empty($data['title']) ? '' : $data['title'];?>">
        <label>

        <?php if(empty($data['hide_title'])) {?>

            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>

                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>

        <?php }?>

            <span class="field-component field">

                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>" class="<?php echo !empty($data['mask']) ?  'leyka-wizard-mask' : '';?>" <?php echo empty($data['is_read_only']) ? '' : 'readonly="readonly"';?>>

            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// Email text fields:
add_action('leyka_render_email', 'leyka_render_email_field', 10, 2);
function leyka_render_email_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-text-field-wrapper leyka-email-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label>

            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo (empty($data['required']) ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>"  class="<?php echo !empty($data['mask']) ? 'leyka-wizard-mask' : '';?>">
            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// File fields:
add_action('leyka_render_file', 'leyka_render_file_field', 10, 2);
function leyka_render_file_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $file_data = $data['value'] ? wp_get_attachment_metadata($data['value']) : array();?>

    <div class="leyka-file-field-wrapper <?php echo $option_id;?>-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" id="<?php echo $option_id;?>-upload">

        <?php if( !empty($data['title']) ) {?>
        <span class="field-component title">

            <?php echo $data['title'];

            if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
            <?php }?>

        </span>
        <?php }?>

        <div class="preview-wrapper">

            <div class="uploaded-file-preview" <?php echo $data['value'] ? '' : 'style="display: none;"';?>>

                <?php _e('Uploaded:', 'leyka');?>

                <span class="file-preview">
                <?php $upload_dir = wp_upload_dir();

                if( $data['value'] && file_exists($upload_dir['basedir'].'/'.ltrim($data['value'], '/')) ) {?>
                    <img src="<?php echo $upload_dir['baseurl'].'/'.ltrim($data['value'], '/');?>" alt="" class="leyka-upload-image-preview">
                <?php } else if($file_data && !empty($file_data['file'])) {
                    echo wp_basename($file_data['file']);
                }?>
                </span>

                <a href="#" class="delete-uploaded-file" title="<?php _e('Delete the uploaded file');?>"></a>

            </div>
            <div class="loading-indicator-wrap" style="display: none;">
                <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
            </div>

        </div>

        <label class="upload-field field-wrapper flex" data-upload-title="<?php echo empty($data['upload_title']) ? __('Select a file', 'leyka') : $data['upload_title'];?>" data-option-id="<?php echo $option_id;?>">

            <span class="field-component field">
                <input type="file" value="" <?php // echo empty($data['is_multiple']) ? '' : 'multiple';?> data-nonce="<?php echo wp_create_nonce('leyka-upload-'.$option_id);?>">
            </span>

            <span class="field-component label upload-picture" id="<?php echo $option_id;?>-upload-button">
                <?php echo empty($data['upload_label']) ? __('Upload', 'leyka') : $data['upload_label'];?>
            </span>

        <?php if( !empty($data['description']) ) {?>
            <span class="field-component help">
                <?php echo $data['description'];?>
            </span>
        <?php }?>

            <input type="hidden" class="leyka-upload-result" name="<?php echo $option_id;?>" value="<?php echo $data['value'];?>">

        </label>

    </div>

<?php }

// Legend fields:
add_action('leyka_render_legend', 'leyka_render_legend_field', 10, 2);
function leyka_render_legend_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-legend-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>
            <span class="field-component field">
                <?php echo $data['text'];?>
            </span>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>
        </label>
    </div>

<?php }

// Number fields:
add_action('leyka_render_number', 'leyka_render_number_field', 10, 2);
function leyka_render_number_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-number-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label>

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">
                <input type="number" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" <?php echo empty($data['length']) ? '' : 'maxlength="'.(int)$data['length'].'"';?> <?php echo isset($data['max']) ? 'max="'.(float)$data['max'].'"' : '';?> <?php echo isset($data['min']) ? 'min="'.(float)$data['min'].'"' : '';?> <?php echo empty($data['step']) ? '' : 'step="'.(float)$data['step'].'"';?>>
            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// Checkbox fields:
add_action('leyka_render_checkbox', 'leyka_render_checkbox_field', 10, 2);
function leyka_render_checkbox_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-checkbox-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label>

            <?php if(empty($data['short_format'])) {?>
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>

                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <?php }?>

            <span class="field-component field">

                <input type="checkbox" id="<?php echo esc_attr($option_id.'-field');?>" name="<?php echo esc_attr($option_id);?>" value="1" <?php echo !empty($data['value']) && absint($data['value']) ? 'checked' : '';?>>&nbsp;

            <?php if( !empty($data['short_format']) ) {

                echo $data['title'];

                if( !empty($data['comment'])) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }

            } else {
                echo empty($data['description']) ? '' : $data['description'];
            }?>
            </span>

        </label>
    </div>

<?php }

// Multicheckbox fields:
add_action('leyka_render_multi_checkbox', 'leyka_render_multi_checkboxes_fields', 10, 2);
function leyka_render_multi_checkboxes_fields($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-multi-checkboxes-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <span class="field-component title">

            <span class="text"><?php echo $data['title'];?></span>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
            <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
            <?php }?>

        </span>

        <span class="field-component field">

            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $label) {?>
                <label for="<?php echo $option_id.'-'.$value.'-field';?>">
                    <input type="checkbox" id="<?php echo $option_id.'-'.$value.'-field';?>" name="<?php echo $option_id;?>[]" value="<?php echo $value;?>" <?php echo in_array($value, $data['value']) ? 'checked' : '';?>>&nbsp;
                    <?php echo esc_html($label);?>
                </label>                
            <?php }?>
        </span>

    </div>

<?php }

// Radio fields:
add_action('leyka_render_radio', 'leyka_render_radio_fields', 10, 2);
function leyka_render_radio_fields($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-radio-field-wrapper field-radio <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <span class="field-component title">
            <span class="text"><?php echo $data['title'];?></span>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
            <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
            <?php }?>
        </span>

        <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $value_data) {

                $field_id = $option_id.'-'.$value.'-field';?>

                <label for="<?php echo $field_id;?>">

                    <input type="radio" id="<?php echo $field_id;?>" name="<?php echo $option_id;?>" value="<?php echo $value;?>" <?php echo $data['value'] == $value ? 'checked' : '';?>>

                    <?php if(is_string($value_data)) {
                        echo esc_attr($value_data);
                    } else if(is_array($value_data) && array_key_exists('title', $value_data)) {

                        echo $value_data['title'];

                        if( !empty($value_data['description']) ) {?>
                        <span class="radio-entry-description"><?php echo $value_data['description']?></span>
                        <?php }

                        if( !empty($value_data['comment'])) {?>
                        <span class="field-q">
                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                            <span class="field-q-tooltip"><?php echo $value_data['comment']?></span>
                        </span>
                        <?php }

                    }?>

                </label>

            <?php }?>

        </span>

        <?php if( !empty($data['description']) ) {?>
        <div class="field-component help"><?php echo $data['description'];?></div>
        <?php }?>

    </div>

<?php }

// Select fields:
add_action('leyka_render_select', 'leyka_render_select_field', 10, 2);
function leyka_render_select_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-field-inner-wrapper leyka-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" data-field-title="<?php echo empty($data['title']) ? '' : $data['title'];?>">

        <label for="<?php echo $option_id.'-field';?>">

        <?php if(empty($data['hide_title'])) {?>
            <span class="field-component title">
                <span class="text"><?php echo empty($data['title']) ? '' : esc_html($data['title']);?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>
        <?php }?>

            <span class="field-component field">

                <?php if(is_string($data['list_entries'])) {
                    $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
                }?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>">
                    <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                        <option value="<?php echo $value;?>" <?php echo $value == $data['value'] ? 'selected' : '';?>><?php echo esc_attr($label);?></option>
                    <?php }?>
                </select>

            </span>

            <?php if( !empty($data['description']) ) {?>
            <div class="field-component help"><?php echo $data['description'];?></div>
            <?php }?>

        </label>

    </div>

<?php }

// Multi-select fields:
add_action('leyka_render_multi_select', 'leyka_render_multi_select_field', 10, 2);
function leyka_render_multi_select_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-multi-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
            }?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" size="<?php echo $data['length'];?>">
                    <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                        <option value="<?php echo $value;?>"><?php echo esc_attr($label);?></option>
                    <?php }?>
                </select>
            </span>

        </label>

    </div>

<?php }

// Textarea fields:
add_action('leyka_render_textarea', 'leyka_render_textarea_field', 10, 2);
function leyka_render_textarea_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;

    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $data['is_code_editor'] = empty($data['is_code_editor']) || !in_array($data['is_code_editor'], array('css')) ?
        false : $data['is_code_editor'];?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-textarea-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?> <?php echo $data['is_code_editor'] === 'css' ? 'css-editor' : '';?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">

                <textarea id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" rows="" cols="" class="<?php echo $data['is_code_editor'] === 'css' ? 'css-editor-field' : '';?>"><?php echo esc_attr($data['value']);?></textarea>

            <?php if($data['is_code_editor'] === 'css') {?>

                <div class="css-editor-reset-value"><?php _e('Return original styles', 'leyka');?></div>
                <input type="hidden" class="css-editor-original-value" value="<?php echo $data['default'];?>">

            <?php }?>

            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Simple HTML fields:
add_action('leyka_render_html', 'leyka_render_html_field', 10, 2);
function leyka_render_html_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-html-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id;?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <?php wp_editor(html_entity_decode(stripslashes($data['value'])), $option_id.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => false,
                'textarea_rows' => 3,
                'teeny' => true, // For little-functioned HTML editor
//                'dfw' => true,
            ));?>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Rich HTML fields:
add_action('leyka_render_rich_html', 'leyka_render_rich_html_field', 10, 2);
function leyka_render_rich_html_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-rich-html-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id;?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <?php wp_editor($data['value'], $option_id.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => true,
                'teeny' => true, // For rich HTML editor
            ));

            if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

add_action('leyka_render_colorpicker', 'leyka_render_colorpicker_field', 10, 2);
function leyka_render_colorpicker_field($option_id, $data) {

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-colorpicker-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>

            <?php if( !empty($data['description']) ) {?>
                <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

            <span class="field-component field">
                <input type="text" id="<?php echo $option_id.'-field';?>" class="leyka-setting-field colorpicker" value="<?php echo esc_attr($data['value']);?>" data-default-color="<?php echo empty($data['default']) ? '' : esc_attr($data['default']);?>">
                <input type="hidden" class="leyka-colorpicker-value" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>">
            </span>

        </label>
    </div>

<?php }

add_action('leyka_render_campaign_select', 'leyka_render_campaign_select_field', 10, 2);
function leyka_render_campaign_select_field($option_id, $data) {

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;

    $data = wp_parse_args($data, array(
        'placeholder' => __('Select a campaign', 'leyka'),
        'default' => '',
//        'multiple' => false, /** Multi-selects are not supported yet */
        'required' => false,
    ));

    $data['value'] = empty($data['value']) ?
        (empty($data['default']) ? false : absint($data['default'])) :
        absint($data['value']);
//    $data['value'] = empty($data['value']) ? ($data['multiple'] ? array() : false) : $data['value'];

    $campaign_title = '';
    if($data['value'] && absint($data['value'])) {

        $campaign = get_post(absint($data['value']));
        if($campaign) {
            $campaign_title = $campaign->post_title;
        }

    }?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-campaign-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>" data-multiple="<?php echo (int)!empty($data['multiple']);?>" data-required-min="<?php echo absint($data['required']);?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">

                <span class="text"><?php echo empty($data['title']) ? '' : esc_html($data['title']);?></span>

                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>

            <?php if( !empty($data['description']) ) {?>
                <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

            <input type="text" id="<?php echo $option_id.'-field';?>" class="leyka-campaign-selector" data-nonce="<?php echo wp_create_nonce('leyka_get_campaigns_list_nonce');?>" placeholder="<?php echo $data['placeholder'];?>" value="<?php echo $campaign_title;?>">
            <input class="campaign-id" type="hidden" name="<?php echo $option_id;?>" value="<?php echo $data['value'];?>">

        </label>

    </div>

<?php }

function leyka_render_tabbed_section_options_area($section) {

    $default_active_tab_index = 0;
    
    if( !empty($section['tabs']) ) {?>

        <div class="section-tabs-wrapper">
        <div class="section-tab-nav">
            
        <?php
        $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {?>

            <a class="section-tab-nav-item <?php echo $counter === $default_active_tab_index ? 'active' : '';?>" href="#" data-target="<?php echo $tab_name;?>"><?php echo $tab['title'];?></a>

            <?php $counter += 1;

        }?>
        
        </div>

        <?php $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {?>

            <div class="section-tab-content tab-<?php echo $tab_name;?> <?php echo $counter === $default_active_tab_index ? 'active' : '';?> <?php echo !empty($tab['screenshots']) ? 'with-sidebar' : '';?>">
                <div class="tab-content-options-wrapper">
                    <?php foreach($tab['sections'] as $tab_section) { ?>
                        <div class="tab-section-options">
                            
                            <?php if(!empty($tab_section['title'])) { ?>
                            <div class="field-component title tab-section-options-title">
                                <?php echo $tab_section['title'];?>
                            </div>
                            <?php } ?>
                            
                            <?php foreach($tab_section['options'] as $option) {
                                if(leyka_options()->is_template_option($option)) {
                                    $option = leyka_options()->get_tab_option_full_name($tab_name, $option);
                                }
                                
                                $option_info = leyka_options()->get_info_of($option);
                                do_action("leyka_render_{$option_info['type']}", $option, $option_info);
                            }?>
                        
                        </div>
                    <?php }?>
                </div>

                <?php if( !empty($tab['screenshots']) ) {?>
                <div class="tab-screenshots">
                    
                    <div class="tab-screenshot-nav left <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/icon-gallery-nav-arrow-left.svg';?>" alt="">
                    </div>

                    <?php $counter = 0;
                    foreach($tab['screenshots'] as $screenshot) {?>

                    <div class="tab-screenshot-item <?php echo !$counter ? 'active' : '';?>">
                        <div class="captioned-screen">

                            <div class="screen-wrapper">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/theme-screenshots/'.$screenshot;?>" class="leyka-instructions-screen" alt="">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/icon-zoom-screen.svg';?>" class="zoom-screen" alt="">
                            </div>

                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/theme-screenshots/'.$screenshot;?>" class="leyka-instructions-screen-full" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;" alt="">

                        </div>
                    </div>

                    <?php $counter += 1;

                    }?>

                    <div class="tab-screenshot-nav right <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/icon-gallery-nav-arrow-right.svg';?>" alt="">
                    </div>

                </div>
                <?php }?>

            </div>

            <?php $counter += 1;

        }?>
        
        </div>
        <?php
    }
}

// [Special field] Common additional donation form fields option:
add_action('leyka_render_custom_additional_fields', 'leyka_render_additional_fields_settings', 10, 2);
function leyka_render_additional_fields_settings($option_id, $data){

    function leyka_additional_form_field_html($is_template = false, $placeholders = array()) {

        $placeholders = wp_parse_args($placeholders, array(
            'id' => '',
            'box_title' => __('New field', 'leyka'),
            'type' => '-',
            'title' => '',
            'slug' => '',
            'is_required' => false,
//            'for_to_all_campaigns' => false,
        ));

        $_COOKIE['leyka-common-additional-fields-boxes-closed'] = empty($_COOKIE['leyka-common-additional-fields-boxes-closed']) ?
            array() : json_decode(stripslashes('[\"someline\"]'));?>

        <div id="<?php echo $placeholders['id'] ? 'item-'.$placeholders['id'] : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?> <?php echo !$is_template && !empty($_COOKIE['leyka-additional-fields-boxes-closed']) && !empty($placeholders['id']) && in_array($placeholders['id'], $_COOKIE['leyka-additional-fields-boxes-closed']) ? 'closed' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

            <h3 class="item-box-title ui-sortable-handle">
                <span class="draggable"></span>
                <span class="title" data-empty-box-title="<?php _e('New field', 'leyka');?>">
                    <?php echo esc_html($placeholders['box_title']);?>
                </span>
            </h3>

            <div class="box-content">

                <div class="single-line">

                    <div class="option-block type-select">
                        <div class="leyka-select-field-wrapper">
                            <?php leyka_render_select_field('field_type', array(
                                'title' => __('Field type', 'leyka'),
                                'hide_title' => true,
                                'type' => 'select',
                                'value' => $placeholders['type'] ? $placeholders['type'] : '-',
                                'list_entries' => array(
                                    '-' => __('Select field type', 'leyka'),
                                    'phone' => __('Telephone number', 'leyka'),
                                    'date' => __('Date', 'leyka'),
//                                    'text' => __('Text', 'leyka'),
//                                    'textarea' => __('Multi-lined text', 'leyka'),
//                                    'select' => __('Dropdown', 'leyka'),
//                                    'checkbox' => __('Check (yes/no)', 'leyka'),
                                ),
                            ));?>
                        </div>
                        <div class="field-errors"></div>
                    </div>

                    <div class="option-block type-text">
                        <div class="leyka-text-field-wrapper">
                            <?php leyka_render_text_field('field_title', array(
                                'title' => __('Field title', 'leyka'),
                                'hide_title' => true,
                                'type' => 'text',
                                'placeholder' => __('Field title', 'leyka'),
                                'value' => $placeholders['title'],
                            ));?>
                        </div>
                        <div class="field-errors"></div>
                    </div>

                </div>

                <div class="single-line">
                    <div class="option-block type-checkbox">
                        <div class="leyka-checkbox-field-wrapper">
                            <?php leyka_render_checkbox_field('field_is_required', array(
                                'title' => __('The field is required on donation form', 'leyka'),
                                'type' => 'checkbox',
                                'value' => !!$placeholders['is_required'],
                                'short_format' => true,
                            ));?>
                        </div>
                        <div class="field-errors"></div>
                    </div>
                </div>

                <ul class="notes-and-errors">
                    <li><?php _e("Don't forget to put a point for processing telephone numbers to your Personal data usage terms", 'leyka');?></li>
                </ul>

                <div class="box-footer">
                    <div class="delete-additional-field delete-item"><?php _e('Delete the field', 'leyka');?></div>
                </div>

            </div>

        </div>

    <?php }

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

        <div class="leyka-main-multi-items leyka-main-additional-fields" data-max-items="<?php echo 5; //Leyka_Support_Packages_Extension::MAX_PACKAGES_NUMBER;?>" data-min-items="0" data-items-cookie-name="leyka-common-additional-fields-boxes-closed" data-item-inputs-names-prefix="leyka_field_">

            <?php $data['value'] = empty($data['value']) || !is_array($data['value']) ?
                leyka_options()->opt('common_additional_fields') :
                $data['value'];

            // Display existing common fields (the assoc. array keys order is important):
            if($data['value'] && is_array($data['value'])) {
                foreach($data['value'] as $field_id => $field_options) {
                    leyka_additional_form_field_html(false, array(
                        'id' => $field_id,
                        'box_title' => $field_options['title'],
                        'type' => $field_options['type'],
                        'title' => $field_options['title'],
                        'is_required' => $field_options['is_required'],
                    ));
                }
            }?>

        </div>

        <?php leyka_additional_form_field_html(true); // Additional field box template ?>

        <div class="add-field add-item bottom"><?php _e('Add field', 'leyka');?></div>

        <input type="hidden" class="leyka-items-options" name="leyka_common_additional_fields" value="">

    </div>

<?php
}

add_action('leyka_save_custom_option-common_additional_fields', 'leyka_save_common_additional_fields_settings');
function leyka_save_common_additional_fields_settings() {

    $_POST['leyka_common_additional_fields'] = json_decode(urldecode($_POST['leyka_common_additional_fields']));
    $result = array();

    foreach($_POST['leyka_common_additional_fields'] as $field) {

        $field->id = mb_stristr($field->id, 'item-') === false || empty($field->title) ?
            $field->id :
            trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($field->title))), '-');

        $result[$field->id] = array(
            'type' => $field->type,
            'title' => $field->title,
            'is_required' => !empty($field->is_required),
        );

    }

    leyka_options()->opt('common_additional_fields', $result);

//    die('<pre>Result: '.print_r(leyka_options()->opt('common_additional_fields'), 1).'</pre>');

}
// [Special field] Common additional donation form fields option - END

// [Special field] Support packages extension - packages list field:
/** @todo Move to somewhere in the Support_Packages Extension */
add_action('leyka_render_custom_support_packages_settings', 'leyka_render_support_packages_settings', 10, 2);
function leyka_render_support_packages_settings($option_id, $data){

    function leyka_support_package_html($is_template = false, $placeholders = array()) {

        $placeholders = wp_parse_args($placeholders, array(
            'id' => '',
            'box_title' => __('New reward', 'leyka'),
            'package_title' => '',
            'amount_needed' => 0,
            'package_icon' => '',
        ));

        $_COOKIE['leyka-support-packages-boxes-closed'] = empty($_COOKIE['leyka-support-packages-boxes-closed']) ?
            array() : json_decode(stripslashes('[\"someline\"]'));?>

        <div id="<?php echo $placeholders['id'] ? 'item-'.$placeholders['id'] : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box package-box <?php echo $is_template ? 'item-template' : '';?> <?php echo !$is_template && !empty($_COOKIE['leyka-support-packages-boxes-closed']) && !empty($placeholders['id']) && in_array($placeholders['id'], $_COOKIE['leyka-support-packages-boxes-closed']) ? 'closed' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

            <h3 class="item-box-title ui-sortable-handle">
                <span class="draggable"></span>
                <span class="title"><?php echo esc_html($placeholders['box_title']);?></span>
            </h3>

            <div class="box-content">

                <div class="option-block type-text">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('package_title', array(
                            'title' => __('Reward title', 'leyka'),
                            'placeholder' => __('E.g., "Golden support level"', 'leyka'),
                            'required' => true,
                            'value' => $placeholders['package_title'],
                        ));?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <?php if($placeholders['id']) {?>
                <div class="option-block type-text-readonly">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('package_id', array(
                            'title' => __('Package ID', 'leyka'),
                            'value' => $placeholders['id'],
                            'is_read_only' => true,
                        ));?>
                    </div>
                </div>
                <?php }?>

                <div class="option-block type-number">
                    <div class="leyka-number-field-wrapper">
                        <?php leyka_render_number_field('package_amount_needed', array(
                            'title' => sprintf(__('Donations amount needed, %s', 'leyka'), leyka_get_currency_label()),
                            'placeholder' => '500',
                            'required' => true,
                            'value' => $placeholders['amount_needed'],
                        ));?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <div class="settings-block option-block type-file">
                    <?php leyka_render_file_field('package_icon', array(
                        'upload_label' => __('Load icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
                        'required' => true,
                        'value' => $placeholders['package_icon'],
                    ));?>
                    <div class="field-errors"></div>
                </div>

                <div class="box-footer">
                    <div class="delete-item delete-package"><?php _e('Delete the reward', 'leyka');?></div>
                </div>

            </div>

        </div>

    <?php }

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

        <div class="leyka-main-multi-items leyka-main-support-packages" data-max-items="<?php echo Leyka_Support_Packages_Extension::MAX_PACKAGES_NUMBER;?>" data-min-items="1" data-items-cookie-name="leyka-support-packages-boxes-closed" data-item-inputs-names-prefix="leyka_package_">

        <?php $data['value'] = empty($data['value']) || !is_array($data['value']) ?
            leyka_options()->opt('custom_support_packages_settings') :
            $data['value'];

        if($data['value'] && is_array($data['value'])) { // Display existing packages (the assoc. array keys order is important)
            foreach($data['value'] as $package_id => $options) {
                leyka_support_package_html(false, array(
                    'id' => $package_id,
                    'box_title' => $options['title'],
                    'package_title' => $options['title'],
                    'amount_needed' => $options['amount_needed'],
                    'package_icon' => $options['icon'],
                ));
            }
        }?>

        </div>

        <?php leyka_support_package_html(true); // Package box template ?>

        <div class="add-item bottom"><?php _e('Add reward', 'leyka');?></div>

        <input type="hidden" class="leyka-items-options" name="leyka_support_packages" value="">

    </div>

<?php }

add_action('leyka_save_custom_option-custom_support_packages_settings', 'leyka_save_support_packages_settings');
function leyka_save_support_packages_settings() {

    $_POST['leyka_support_packages'] = json_decode(urldecode($_POST['leyka_support_packages']));
    $result = array();

    foreach($_POST['leyka_support_packages'] as $package) {

        $package->id = stristr($package->id, 'item-') === false || empty($package->title) ?
            $package->id :
            trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($package->title))), '-');

        $result[$package->id] = array(
            'title' => $package->title,
            'amount_needed' => $package->amount_needed,
            'icon' => $package->icon,
        );

    }

    leyka_options()->opt('custom_support_packages_settings', $result);

}
// [Special field] Support packages extension - packages list field - END