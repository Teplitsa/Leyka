<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

// Sections:
add_action('leyka_render_section', 'leyka_render_section_area');
function leyka_render_section_area($section){?>

    <div class="leyka-options-section <?php echo $section['is_default_collapsed'] ? 'collapsed' : '';?>" id="<?php echo $section['name'];?>">
        <div class="header"><h3><?php echo esc_attr($section['title']);?></h3></div>
        <div class="content">
            <?php foreach($section['options'] as $option) {

                $option_info = leyka_options()->get_info_of($option);
                do_action("leyka_render_{$option_info['type']}", $option, $option_info);

            }?>
        </div>
    </div>
<?php }

// Text fields:
add_action('leyka_render_text', 'leyka_render_text_field', 10, 2);
function leyka_render_text_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if(!empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>
            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>">
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component field">
                <input type="file" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="" <?php echo empty($data['required']) ? '' : 'required';?>>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg">
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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
    
    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id; //var_dump($data);?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title"><?php echo $data['title'];?></span>
            <span class="field-component field">
                <input type="checkbox" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="1" <?php echo intval($data['value']) >= 1 ? 'checked' : '';?>>&nbsp;
            <?php echo $data['description'];?></span>
        </label>
    </div>
<?php }

// Multicheckbox fields:
add_action('leyka_render_multi_checkbox', 'leyka_render_multi_checkboxes_fields', 10, 2);
function leyka_render_multi_checkboxes_fields($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id; ?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <span class="field-component title">
            <?php echo $data['title'];?>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="field-radio <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <span class="field-component title">
            <?php echo $data['title'];?>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id;?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
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

    <div id="<?php echo $option_id.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id;?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
            </span>
            <?php wp_editor(str_replace("&lt;br&gt;", "\n", esc_attr($data['value'])), $option_id.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => true,
                'teeny' => true, // For rich HTML editor
//                    'dfw' => true,
            ));?>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>
        </label>
    </div>

<?php }

// Special field: gateways commission options:
add_action('leyka_render_custom_gateways_commission', 'leyka_render_gateways_commission_field', 10, 2);
function leyka_render_gateways_commission_field($option_name, $data){

    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;
    $option_value = leyka_options()->opt('commission');?>

    <div id="<?php echo $option_name.'-wrapper';?>" class="<?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

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
// Special field: gateways commission options - END