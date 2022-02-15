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

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-field-inner-wrapper leyka-text-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" data-field-title="<?php echo empty($data['title']) ? '' : esc_attr($data['title']);?>">
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

                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>" class="<?php echo empty($data['mask']) ?  '' : 'leyka-wizard-mask';?>" <?php echo empty($data['is_read_only']) ? '' : 'readonly="readonly"';?>>

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
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>" class="<?php echo empty($data['mask']) ? '' : 'leyka-wizard-mask';?>">
            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// General file upload fields:
add_action('leyka_render_file', 'leyka_render_file_field', 10, 2);
function leyka_render_file_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $file_data = $data['value'] ? wp_get_attachment_metadata($data['value']) : [];

    $upload_dir = wp_upload_dir();
    $file_exists = ( $data['value'] && file_exists($upload_dir['basedir'].'/'.ltrim($data['value'], '/')) )
        || ($file_data && !empty($file_data['file']));?>

    <div class="leyka-upload-field-wrapper leyka-file-field-wrapper <?php echo $option_id;?>-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" id="<?php echo $option_id;?>-upload">

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
                <?php if( $data['value'] && file_exists($upload_dir['basedir'].'/'.ltrim($data['value'], '/')) ) {?>
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

        <label class="upload-field file-upload-field field-wrapper flex" data-upload-title="<?php echo empty($data['upload_title']) ? __('Select a file', 'leyka') : $data['upload_title'];?>" data-option-id="<?php echo $option_id;?>" <?php echo $file_exists ? 'style="display:none;"' : '';?>>

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

// Media library file upload fields:
add_action('leyka_render_media_upload', 'leyka_render_media_upload_field', 10, 2);
function leyka_render_media_upload_field($option_id, $data){

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div class="leyka-upload-field-wrapper leyka-media-upload-field-wrapper <?php echo $option_id;?>-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>" id="<?php echo $option_id;?>-upload">

        <?php if( !empty($data['title']) ) {?>
            <span class="field-component title">

            <?php echo esc_html($data['title']);

            if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
            <?php }?>

        </span>
        <?php }

        $data['upload_files_type'] = empty($data['upload_files_type']) ? 'image' : esc_attr($data['upload_files_type']);
        if($data['value']) {

            $media_meta = wp_get_attachment_metadata($data['value']);
            $image_url = wp_get_attachment_image_url($data['value'], 'medium');

            if( !$media_meta ) {

                if( !$image_url || !mb_stristr($image_url, '.svg') ) {
                    $data['value'] = 0;
                }

            }

        } ?>

        <div class="preview-wrapper">

            <div class="uploaded-file-preview" <?php echo $data['value'] ? '' : 'style="display: none;"';?>>

                <span class="file-preview">
                <?php if($data['value'] && $data['upload_files_type'] === 'image') {?>

                    <img src="<?php echo wp_get_attachment_image_url($data['value'], 'medium');?>" alt="" class="leyka-upload-image-preview">

                <?php }?>
                </span>

                <a href="#" class="delete-uploaded-file" title="<?php _e('Delete the uploaded file');?>"></a>

            </div>

            <div class="loading-indicator-wrap" style="display: none;">
                <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
            </div>

        </div>

        <label class="upload-field media-upload-field field-wrapper flex" data-upload-title="<?php echo empty($data['upload_title']) ? __('Select a file', 'leyka') : $data['upload_title'];?>" data-upload-button-label="<?php echo empty($data['upload_button_label']) ? __('Use the media', 'leyka') : $data['upload_button_label'];?>" data-upload-is-multiple="<?php echo (int)!empty($data['is_multiple']);?>" data-upload-files-type="<?php echo empty($data['upload_files_type']) ? '' : $data['upload_files_type'];?>" data-option-id="<?php echo $option_id;?>" <?php echo $data['value'] ? 'style="display:none;"' : '';?>>

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

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
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
    $data['value'] = isset($data['value']) ? $data['value'] : ''; ?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-field-inner-wrapper leyka-number-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" data-field-title="<?php echo empty($data['title']) ? '' : esc_attr($data['title']);?>">
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

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

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

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

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
                    $data['list_entries'] = $data['list_entries'](); // Call the callback to create select field options
                }?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>">
                    <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                        <option value="<?php echo $value;?>" <?php echo $value == $data['value'] ? 'selected' : '';?> <?php echo is_array($label) && !empty($label['disabled']) ? 'disabled="disabled"' : '';?>>
                            <?php echo is_array($label) && !empty($label['option_label']) ? $label['option_label'] : $label;?>
                        </option>
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

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-multi-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>

                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>

                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>

            <span class="field-component field">

                <?php if(is_string($data['list_entries'])) {
                    $data['list_entries'] = $data['list_entries'](); // Call the callback to create select options
                }

                $data['value'] = empty($data['value']) ? // 'value' should be an array of 'list_entry' items values
                    (empty($data['default']) ? [] : maybe_unserialize($data['default'])) :
                    maybe_unserialize($data['value']);
                $data['value'] = is_array($data['value']) ? $data['value'] : [$data['value']];?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>[]" size="<?php echo empty($data['length']) ? 5 : absint($data['length']);?>" multiple="multiple">

                <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                    <option value="<?php echo $value;?>" <?php echo in_array($value, $data['value']) ? 'selected="selected"' : '';?>>
                        <?php echo esc_attr($label);?>
                    </option>

                <?php }?>
                </select>

            </span>

            <?php if( !empty($data['description']) ) {?>
                <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Static text fields:
add_action('leyka_render_static_text', 'leyka_render_static_text_field', 10, 2);
function leyka_render_static_text_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $content = '';

    $data['value'] = isset($data['value']) ? $data['value'] : '';

    if( !empty($data['is_html']) && $data['is_html'] === true ) {
        $content = $data['value'];
    } else if( !empty($data['is_file']) && $data['is_file'] === true ) {

        if(file_exists($data['value'])) {

            ob_start();

            include $data['value'];

            $content = ob_get_contents();

            ob_end_clean();

        }

    } else {
        $content = esc_attr($data['value']);
    }?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-textarea-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?> <?php echo isset($data['is_code_editor']) && $data['is_code_editor'] === 'css' ? 'css-editor' : '';?>">

        <span class="field-component title">
            <span class="text"><?php echo $data['title'];?></span>
            <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                <span class="field-q-tooltip"><?php echo $data['comment']?></span>
            </span>
            <?php }?>
        </span>

        <span class="field-component field">

            <span id="<?php echo $option_id.'-field';?>" class="<?php echo !empty($data['field_classes']) ? (is_array($data['field_classes']) ? implode(' ', $data['field_classes']) : $data['field_classes']) : '' ?>">
                <?php echo $content;?>
            </span>

        </span>

        <?php if( !empty($data['description']) ) {?>
        <span class="field-component help"><?php echo $data['description'];?></span>
        <?php }?>

    </div>

<?php }

// Textarea fields:
add_action('leyka_render_textarea', 'leyka_render_textarea_field', 10, 2);
function leyka_render_textarea_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;

    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $data['is_code_editor'] = empty($data['is_code_editor']) || !in_array($data['is_code_editor'], ['css']) ?
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

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
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

            <?php wp_editor(html_entity_decode(stripslashes($data['value'])), $option_id.'-field-'.leyka_get_random_string(4), [
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => false,
                'textarea_rows' => 3,
                'teeny' => true, // For little-functioned HTML editor
//                'dfw' => true,
            ]);?>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Rich HTML fields:
add_action('leyka_render_rich_html', 'leyka_render_rich_html_field', 10, 2);
function leyka_render_rich_html_field($option_id, $data){

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
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

            <?php wp_editor($data['value'], $option_id.'-field', [
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => true,
                'teeny' => true, // For rich HTML editor
            ]);

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

    $data = wp_parse_args($data, [
        'placeholder' => __('Select a campaign', 'leyka'),
        'default' => '',
//        'multiple' => false, /** Multi-selects are not supported yet */
        'required' => false,
    ]);

    $data['value'] = empty($data['value']) ?
        (empty($data['default']) ? false : absint($data['default'])) :
        absint($data['value']);
//    $data['value'] = empty($data['value']) ? ($data['multiple'] ? [] : false) : $data['value'];

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

/**
 * @param $section
 * @param $forced_data array Values from this array will be added to options data sent to the rendering function (with a replacement upon collisions)
 * @return void
 */
function leyka_render_tabbed_section_options_area($section, $forced_data = []) {

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

                                if( !empty($forced_data[$option]) && is_array($forced_data[$option]) ) {
                                    $option_info = array_merge($option_info, $forced_data[$option]);
                                }

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

function leyka_multi_valued_item_campaign_subfields_html(array $placeholders = []) {

    $placeholders = wp_parse_args($placeholders, [
        'item_campaigns_field_title' => __('Campaigns that will use the item', 'leyka'),
        'item_campaigns_field_placeholder' => __('Campaigns list', 'leyka'),
        'item_campaigns' => [],

        'item_for_all_campaigns_field_title' => __('The item is for all campaigns by default', 'leyka'),
        'item_for_all_campaigns' => false,

        'item_campaigns_exceptions_field_title' => __('Campaigns that will NOT use the item', 'leyka'),
        'item_campaigns_exceptions_field_placeholder' => __('Campaigns list', 'leyka'),
        'item_campaigns_exceptions' => [],
    ]);?>

    <div class="single-line campaigns-list-select" <?php echo !!$placeholders['item_for_all_campaigns'] ? 'style="display:none;"' : '';?>>

        <div class="option-block type-multiselect">

            <div class="leyka-multiselect-field-wrapper">

                <label>
                    <span class="field-component title"><?php echo esc_html($placeholders['item_campaigns_field_title']);?></span>
                </label>

                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input leyka-js-dont-initialize-common-widget" value="" placeholder="<?php echo esc_attr($placeholders['item_campaigns_field_placeholder']);?>">

                <select class="leyka-campaigns-select autocomplete-select" name="campaigns[]" multiple="multiple">

                    <?php $campaigns = $placeholders['item_campaigns'] ?
                        leyka_get_campaigns_list(['include' => $placeholders['item_campaigns'], 'posts_per_page' => 20,]) : [];

                    foreach($campaigns as $campaign_id => $campaign_title) {?>

                        <option value="<?php echo $campaign_id;?>" <?php echo is_array($placeholders['item_campaigns']) && in_array($campaign_id, $placeholders['item_campaigns']) ? 'selected="selected"' : '';?>>
                            <?php echo $campaign_title;?>
                        </option>

                    <?php }?>

                </select>

            </div>

            <div class="field-errors"></div>

        </div>

    </div>

    <div class="single-line">
        <div class="option-block type-checkbox">
            <div class="leyka-checkbox-field-wrapper">
                <?php leyka_render_checkbox_field('item_for_all_campaigns', [
                    'title' => $placeholders['item_for_all_campaigns_field_title'],
                    'value' => !!$placeholders['item_for_all_campaigns'],
                    'short_format' => true,
                    'field_classes' => ['item-for-all-campaigns',],
                ]);?>
            </div>
            <div class="field-errors"></div>
        </div>
    </div>

    <div class="single-line campaigns-exceptions-list-select" <?php echo !!$placeholders['item_for_all_campaigns'] ? '' : 'style="display:none;"';?>>
        <div class="option-block type-multiselect">

            <div class="leyka-multiselect-field-wrapper">

                <label>
                    <span class="field-component title">
                        <?php echo esc_html($placeholders['item_campaigns_exceptions_field_title']);?>
                    </span>
                </label>

                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input leyka-js-dont-initialize-common-widget" value="" placeholder="<?php echo esc_attr($placeholders['item_campaigns_exceptions_field_placeholder']);?>">

                <select class="leyka-campaigns-select autocomplete-select" name="campaigns_exceptions[]" multiple="multiple">

                    <?php $campaigns = $placeholders['item_campaigns_exceptions'] ?
                        leyka_get_campaigns_list([
                            'include' => $placeholders['item_campaigns_exceptions'], 'posts_per_page' => 20,
                        ]) : [];

                    foreach($campaigns as $campaign_id => $campaign_title) {?>

                        <option value="<?php echo $campaign_id;?>" <?php echo is_array($placeholders['item_campaigns']) && in_array($campaign_id, $placeholders['item_campaigns']) ? 'selected="selected"' : '';?>>
                            <?php echo $campaign_title;?>
                        </option>

                    <?php }?>

                </select>

            </div>

            <div class="field-errors"></div>

        </div>
    </div>

<?php }

// [Special field] Common additional donation form fields option:
function leyka_additional_form_field_main_subfields_html(array $placeholders = []) {

    $placeholders = wp_parse_args($placeholders, [
        'id' => '',
        'box_title' => __('New field', 'leyka'),
        'type' => '-',
        'title' => '',
        'is_required' => false,
    ]);?>

    <div class="single-line">

        <div class="option-block type-select">
            <div class="leyka-select-field-wrapper">
                <?php leyka_render_select_field('field_type', [
                    'title' => __('Field type', 'leyka'),
                    'value' => $placeholders['type'] ? : '-',
                    'required' => true,
                    'list_entries' => [
                        '-' => __('Select field type', 'leyka'),
                        'text' => __('Text', 'leyka'),
                        'phone' => __('Telephone number', 'leyka'),
                        'date' => __('Date', 'leyka'),
//                        'textarea' => __('Multi-lined text', 'leyka'),
//                        'select' => __('Dropdown', 'leyka'),
//                        'checkbox' => __('Check (yes/no)', 'leyka'),
                    ],
                ]);?>
            </div>
            <div class="field-errors"></div>
        </div>

        <div class="option-block type-text">
            <div class="leyka-text-field-wrapper">
                <?php leyka_render_text_field('field_title', [
                    'title' => __('Field title', 'leyka'),
                    'required' => true,
                    'placeholder' => __('E.g., "Your mobile phone"', 'leyka'),
                    'value' => $placeholders['title'],
                ]);?>
            </div>
            <div class="field-errors"></div>
        </div>

    </div>

    <div class="single-line">
        <div class="leyka-text-field-wrapper leyka-field-wide">
            <?php leyka_render_text_field('field_description', [
                'title' => __('Description text', 'leyka'),
                'placeholder' => __('E.g., "We are going to send you an SMS"', 'leyka'),
                'value' => empty($placeholders['description']) ? '' : $placeholders['description'],
            ]);?>
        </div>
        <div class="field-errors"></div>
    </div>

    <div class="single-line">
        <div class="option-block type-checkbox">
            <div class="leyka-checkbox-field-wrapper">
                <?php leyka_render_checkbox_field('field_is_required', [
                    'title' => __('The field is required on donation form', 'leyka'),
                    'value' => !!$placeholders['is_required'],
                    'short_format' => true,
                ]);?>
            </div>
            <div class="field-errors"></div>
        </div>
    </div>

<?php }

add_action('leyka_render_custom_additional_fields_library', 'leyka_render_additional_fields_library_settings', 10, 2);
function leyka_render_additional_fields_library_settings($option_id, $data = []){

    function leyka_additional_form_field_html($is_template = false, $placeholders = []) {

        $placeholders = wp_parse_args($placeholders, [
            'id' => '',
            'box_title' => __('New field', 'leyka'),
            'type' => '-',
            'title' => '',
            'is_required' => false,
            'campaigns' => [],
            'for_all_campaigns' => false,
            'campaigns_exceptions' => [],
        ]);

        $_COOKIE['leyka-additional-fields-boxes-closed'] = empty($_COOKIE['leyka-additional-fields-boxes-closed']) ?
            [] : json_decode(stripslashes('[\"someline\"]'));?>

        <div id="<?php echo $placeholders['id'] ? $placeholders['id'] : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?> <?php echo !$is_template && !empty($_COOKIE['leyka-additional-fields-boxes-closed']) && !empty($placeholders['id']) && in_array($placeholders['id'], $_COOKIE['leyka-additional-fields-boxes-closed']) ? 'closed' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

            <h3 class="item-box-title ui-sortable-handle">

                <span class="draggable"></span>

                <span class="title" data-empty-box-title="<?php _e('New field', 'leyka');?>">
                    <?php echo esc_html($placeholders['box_title']);?>
                </span>

            </h3>

            <div class="box-content">

                <?php leyka_additional_form_field_main_subfields_html($placeholders);

                leyka_multi_valued_item_campaign_subfields_html([
                    'item_campaigns' => $placeholders['campaigns'],
                    'item_campaigns_field_title' => __('Campaigns that will use the field', 'leyka'),

                    'item_for_all_campaigns' => !!$placeholders['for_all_campaigns'],
                    'item_for_all_campaigns_field_title' => __('The field is for all campaigns by default', 'leyka'),

                    'item_campaigns_exceptions' => $placeholders['campaigns_exceptions'],
                    'item_campaigns_exceptions_field_title' => __('Campaigns that will NOT use the field', 'leyka'),
                ]);?>

                <ul class="notes-and-errors">
                    <li class="any-field-note"><?php _e('When you edit a field, you will change it for all campaigns that use it', 'leyka');?></li>
                    <li class="phone-field-note" <?php echo $placeholders['type'] === 'phone' ? '' : 'style="display: none;"'?>><?php _e("Don't forget to put a point for processing telephone numbers to your Personal data usage terms", 'leyka');?></li>
                </ul>

                <div class="box-footer">
                    <div class="delete-additional-field delete-item"><?php _e('Delete the field', 'leyka');?></div>
                </div>

            </div>

        </div>

    <?php }

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data = $data ? : leyka_options()->get_info_of($option_id);?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

        <div class="leyka-main-multi-items leyka-main-additional-fields" data-max-items="" data-min-items="0" data-items-cookie-name="leyka-additional-fields-boxes-closed" data-item-inputs-names-prefix="leyka_field_" data-show-new-item-if-empty="1">

            <?php $data['value'] = empty($data['value']) || !is_array($data['value']) ?
                leyka_options()->opt('additional_donation_form_fields_library') :
                $data['value'];

            // Display existing common fields (the assoc. array keys order is important):
            if($data['value'] && is_array($data['value'])) {
                foreach($data['value'] as $field_id => $field_options) {
                    leyka_additional_form_field_html(false, [
                        'id' => $field_id,
                        'box_title' => $field_options['title'],
                        'type' => $field_options['type'],
                        'title' => $field_options['title'],
                        'description' => empty($field_options['description']) ? '' : $field_options['description'],
                        'is_required' => $field_options['is_required'],
                        'campaigns' => empty($field_options['campaigns']) ? [] : $field_options['campaigns'],
                        'for_all_campaigns' => !empty($field_options['for_all_campaigns']),
                        'campaigns_exceptions' => empty($field_options['campaigns_exceptions']) ?
                            [] : $field_options['campaigns_exceptions'],
                    ]);
                }
            }?>

        </div>

        <?php leyka_additional_form_field_html(true); // Additional field box template ?>

        <div class="add-field add-item bottom"><?php _e('Add field', 'leyka');?></div>

        <input type="hidden" class="leyka-items-options" name="leyka_additional_donation_form_fields_library" value="">

    </div>

<?php
}

add_action('leyka_save_custom_option-additional_donation_form_fields_library', 'leyka_save_additional_fields_library_settings');
function leyka_save_additional_fields_library_settings() {

    $_POST['leyka_additional_donation_form_fields_library'] = json_decode(
        urldecode($_POST['leyka_additional_donation_form_fields_library'])
    );
    $result = [];

    foreach($_POST['leyka_additional_donation_form_fields_library'] as $field) {

        $field->id = mb_stripos($field->id, 'item-') === false || empty($field->title) ?
            $field->id :
            trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($field->title))), '-');

        $result[$field->id] = [
            'type' => $field->type,
            'title' => $field->title,
            'description' => $field->description,
            'is_required' => !empty($field->is_required),
            'campaigns' => $field->campaigns,
            'for_all_campaigns' => $field->leyka_item_for_all_campaigns,
            'campaigns_exceptions' => $field->campaigns_exceptions,
        ];

        if($field->campaigns && !$field->leyka_item_for_all_campaigns) {
            foreach($field->campaigns as $campaign_id) { // Add Field 2 Campaign links to selected Campaigns

                $campaign = new Leyka_Campaign($campaign_id);
                $campaign_additional_fields = $campaign->additional_fields_settings;

                if(in_array($field->id, $campaign_additional_fields)) { // Field is already in Campaign fields list
                    continue;
                }

                $campaign_additional_fields[] = $field->id;
                $campaign->additional_fields_settings = $campaign_additional_fields;

            }
        }

    }

    leyka_options()->opt('additional_donation_form_fields_library', $result);

}
// [Special field] Common additional donation form fields option - END

// [Special field] Payments amounts options:
function leyka_payments_amounts_options_html($is_template = false, array $placeholders = []) { ?>

    <div id="<?php echo 'item-'.$placeholders['amount_option_id'];?>" class="multi-valued-item-box payment-amount-option payment_<?php echo $placeholders['payment_type']; ?> field-box <?php echo $is_template ? 'item-template' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

        <h3 class="item-box-title ui-sortable-handle">

            <span class="draggable"></span>

            <span class="title" data-empty-box-title="<?php _e('New field', 'leyka');?>">
                <?php echo esc_html($placeholders['box_title']);?>
            </span>

        </h3>

        <div class="box-content">

            <div class="single-line">

                <div class="option-block type-text">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_number_field('payment_'.$placeholders['payment_type'].'_amount_'.$placeholders['amount_option_id'], [
                            'amount_option_id' => $placeholders['amount_option_id'],
                            'title' => __('Amount', 'leyka')." (".$placeholders['currency_label'].")",
                            'required' => true,
                            'value' => $placeholders['amount'],
                            'field_classes' => ['payment-amount-option-amount'],
                            'min' => $placeholders['currency_min_sum'],
                            'max' => $placeholders['currency_max_sum']
                        ]);?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <div class="option-block type-text">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('payment_'.$placeholders['payment_type'].'_description_'.$placeholders['amount_option_id'], [
                            'amount_option_id' => $placeholders['amount_option_id'],
                            'title' => __('Description', 'leyka'),
                            'required' => false,
                            'placeholder' => __('E.g., "For small expenses"', 'leyka'),
                            'value' => $placeholders['description'],
                            'field_classes' => ['payment-amount-option-description']
                        ]);?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <div class="option-block type-text">
                    <div class="leyka-field-wrapper">
                        <div class="delete-additional-field delete-item"><?php _e('Delete', 'leyka');?></div>
                    </div>
                </div>

            </div>

        </div>

    </div>

<?php }

add_action('leyka_render_custom_payments_amounts_options', 'leyka_render_custom_payments_amounts_options_settings', 10, 2);
function leyka_render_custom_payments_amounts_options_settings($option_id, $data = []){

    $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data = $data ? : leyka_options()->get_info_of($option_id);

    ?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

        <div class="leyka-main-multi-items leyka-main-payments-amounts" data-max-items="10" data-min-items="0" data-item-inputs-names-prefix="leyka_field_" data-show-new-item-if-empty="1">

            <?php

            $data['value'] = (empty($data['value']) || !is_array($data['value'])) && ( !empty($data['default']) && is_array($data['default'])) ?
                $data['default'] : $data['value'];

            $currency_id = leyka_options()->opt_safe("currency_main");
            $currency_label = leyka_options()->opt_safe("currency_{$currency_id}_label");
            $currency_min_sum = leyka_options()->opt_safe("currency_{$currency_id}_min_sum");
            $currency_max_sum = leyka_options()->opt_safe("currency_{$currency_id}_max_sum");

            if($data['value'] && is_array($data['value'])) {
                foreach($data['value'] as $amount_option_id => $amount_option_data) {
                    leyka_payments_amounts_options_html(false, [
                        'amount_option_id' => $amount_option_id,
                        'description' => empty($amount_option_data['description']) ? '' : $amount_option_data['description'],
                        'payment_type' => $data['payment_type'],
                        'amount' => $amount_option_data['amount'],
                        'currency_label' => $currency_label,
                        'currency_min_sum' => $currency_min_sum,
                        'currency_max_sum' => $currency_max_sum,
                        'box_title' => empty($amount_option_data['description']) ? __('Sum', 'leyka') : $amount_option_data['description']
                    ]);
                }
            } else {
                leyka_payments_amounts_options_html(false, [
                    'amount_option_id' => leyka_get_random_string(4),
                    'description' => __('For small expenses','leyka'),
                    'payment_type' => $data['payment_type'],
                    'amount' =>  $currency_min_sum,
                    'currency_label' => $currency_label,
                    'currency_min_sum' => $currency_min_sum,
                    'currency_max_sum' => $currency_max_sum,
                    'box_title' => __('For small expenses','leyka'),
                ]);
            }

            ?>

        </div>

        <?php leyka_payments_amounts_options_html(true, [
            'amount_option_id' => leyka_get_random_string(4),
            'description' => '',
            'payment_type' => $data['payment_type'],
            'amount' =>  $currency_min_sum,
            'currency_label' => $currency_label,
            'currency_min_sum' => $currency_min_sum,
            'currency_max_sum' => $currency_max_sum,
            'box_title' => __('New sum', 'leyka')
        ]); // Additional field box template ?>

        <div class="add-field add-item bottom"><?php _e('Add sum', 'leyka');?></div>

        <input type="hidden" class="leyka-items-options" name="<?php echo $option_id; ?>" value="">

    </div>

    <?php

}

$main_currency_id = leyka_options()->opt_safe('currency_main');
add_action("leyka_save_custom_option-payments_single_amounts_options_".$main_currency_id, 'leyka_save_payments_amounts_options');
function leyka_save_payments_amounts_options() {

    function save_payment_amounts_options($amounts_options, $payment_type, $currency_id) {

        $result = [];

        foreach($amounts_options as $amount_option) {

            $amount_option_id = str_replace('item-', '', $amount_option['id']);

            $result[$amount_option_id] = [
                'amount' => $amount_option['leyka_payment_'.$payment_type.'_amount_'.$amount_option_id],
                'description' => wp_strip_all_tags($amount_option['leyka_payment_'.$payment_type.'_description_'.$amount_option_id], true),
            ];

        }

        leyka_options()->opt('payments_'.$payment_type.'_amounts_options_'.$currency_id, $result);

    }

    $currency_id = leyka_options()->opt_safe('currency_main');
    $payments_single_amounts_attr = 'leyka_payments_single_amounts_options_'.$currency_id;
    $payments_recurring_amounts_attr = 'leyka_payments_recurring_amounts_options_'.$currency_id;

    if(isset($_POST[$payments_single_amounts_attr])) {
        save_payment_amounts_options(json_decode(urldecode($_POST[$payments_single_amounts_attr]), true), 'single', $currency_id);
    }

    if(isset($_POST[$payments_recurring_amounts_attr])) {
        save_payment_amounts_options(json_decode(urldecode($_POST[$payments_recurring_amounts_attr]), true), 'recurring', $currency_id);
    }

}
// [Special field] Payments amounts options - END

