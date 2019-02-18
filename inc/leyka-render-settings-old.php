<?php if( !defined('WPINC') ) die;

// Sections:
add_action('leyka_render_section', 'leyka_render_section_area');
function leyka_render_section_area($section){?>

    <div class="leyka-options-section <?php echo $section['is_default_collapsed'] ? 'collapsed' : '';?> <?php echo !empty($section['tabs']) ? 'with-tabs' : '';?>" id="<?php echo $section['name'];?>">
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

            if( !empty($section['is_separate_sections_forms']) ) { ?>
                <p class="submit">
                    <input type="submit" name="leyka_settings_<?php echo $section['current_stage'];?>_submit" class="button-primary" <?php if(!empty($section['action_button']['id'])) { printf(' id="%s" ', $section['action_button']['id']); } ?>" value="<?php echo !empty($section['action_button']['title']) ? $section['action_button']['title'] : __('Save', 'leyka');?>">
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-text-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo (empty($data['required']) ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>"  class="<?php echo !empty($data['mask']) ?  'leyka-wizard-mask' : '';?>">
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-text-field-wrapper leyka-email-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo (empty($data['required']) ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>"  class="<?php echo !empty($data['mask']) ?  'leyka-wizard-mask' : '';?>">
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
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-file-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component field">
                <input type="file" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="">
                <span class="chosen-file"> </span>
                <input type="button" href="#" class="button" value="<?php echo $data['title'];?>">
            </span>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>
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
                <input type="number" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" <?php echo empty($data['length']) ? '' : 'maxlength="'.(int)$data['length'].'"';?> <?php echo isset($data['max']) ? 'max="'.(int)$data['max'].'"' : '';?> <?php echo isset($data['min']) ? 'min="'.(int)$data['min'].'"' : '';?> <?php echo empty($data['step']) ? '' : 'step="'.(int)$data['step'].'"';?>>
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
        <label for="<?php echo $option_id.'-field';?>">

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

                <input type="checkbox" id="<?php echo esc_attr($option_id.'-field');?>" name="<?php echo esc_attr($option_id);?>" value="1" <?php echo !empty($data['value']) && (int)$data['value'] >= 1 ? 'checked' : '';?>>&nbsp;

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

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id; ?>

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
                    <?php echo esc_attr($label);?>
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
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

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
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-textarea-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

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
                <textarea id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" rows="" cols=""><?php echo esc_attr($data['value']);?></textarea>
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
//                'dfw' => true,
            ));

            if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// [Special field] Gateways commission options:
add_action('leyka_render_custom_gateways_commission', 'leyka_render_gateways_commission_field', 10, 2);
function leyka_render_gateways_commission_field($option_name, $data){

    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;
    $option_value = leyka_options()->opt('commission');?>

    <div id="<?php echo $option_name.'-wrapper';?>" class="leyka-gateways-commission-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

    <?php foreach(leyka_get_gateways() as $gateway) { /** @var Leyka_Gateway $gateway */?>

        <div class="leyka-commission-container">

            <h4><?php echo $gateway->name;?></h4>

            <div class="leyka-commission-pm-list-container">
            <?php foreach($gateway->get_payment_methods() as $pm) {?>

                <span class="leyka-commission-pm">
                    <label>
                        <input type="number" class="leyka-commission-field" name="leyka_commission[<?php echo $pm->full_id;?>]" value="<?php echo isset($option_value[$pm->full_id]) ? (float)$option_value[$pm->full_id] : '0.0';?>" step="0.01" min="0.0" max="100.0" id="leyka_commission_<?php echo $pm->full_id;?>" pattern="[0-9]+(,[0-9]+)?">
                    %</label>
                    <label class="leyka-pm-label" for="leyka_commission_<?php echo $pm->full_id;?>">
                        <?php echo $pm->name;?>
                    </label>
                </span>

            <?php }?>
            </div>

        </div>

    <?php }?>

    </div>

<?php }
// [Special field] Gateways commission options - END

function leyka_render_tabbed_section_options_area($section) {
    $default_active_tab_index = 0;
    
    if(!empty($section['tabs'])) {
        ?>
        <div class="section-tabs-wrapper">
        <div class="section-tab-nav">
            
        <?php
        $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {
            ?>
            <a class="section-tab-nav-item <?php echo $counter === $default_active_tab_index ? 'active' : '';?>" href="#" data-target="<?php echo $tab_name;?>"><?php echo $tab['title'];?></a>
            <?php
            $counter += 1;
        }?>
        
        </div>
        <?php
        
        $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {
            ?>
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
                    <?php } ?>
                </div>

                <?php if(!empty($tab['screenshots'])) {?>
                <div class="tab-screenshots">
                    
                    <div class="tab-screenshot-nav left <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/icon-gallery-nav-arrow-left.svg';?>" />
                    </div>
                
                    <?php
                    $counter = 0;
                    foreach($tab['screenshots'] as $screenshot) {?>
                    
                    <div class="tab-screenshot-item <?php echo !$counter ? 'active' : '';?>">
                        <div class="captioned-screen">
                            <div class="screen-wrapper">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/theme-screenshots/' . $screenshot;?>" class="leyka-instructions-screen" />
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/icon-zoom-screen.svg';?>" class="zoom-screen" alt="">
                            </div>
                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/theme-screenshots/' . $screenshot;?>" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;" />
                        </div>
                    </div>
                    
                    <?php
                        $counter += 1;
                    } ?>
                    
                    <div class="tab-screenshot-nav right <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/icon-gallery-nav-arrow-right.svg';?>" />
                    </div>
                </div>
                <?php }?>
                
            </div>
            <?php
            $counter += 1;
        }?>
        
        </div><!-- end section-tabs-wrapper -->
        <?php
    }
}
