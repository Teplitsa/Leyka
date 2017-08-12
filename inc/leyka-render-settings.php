<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

/** Sections */
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

/** Text fields */
add_action('leyka_render_text', 'leyka_render_text_field', 10, 2);
function leyka_render_text_field($option_name, $data){

    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper'?>">
        <label for="<?php echo $option_name.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" id="<?php echo $option_name.'-field';?>" name="<?php echo $option_name;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo esc_attr($data['placeholder']);?>" maxlength="<?php echo $data['length'];?>" />
            </span>
            <span class="field-component help"><?php echo esc_attr($data['description']);?></span>
        </label>
    </div>
<?php }

// Checkbox fields:
add_action('leyka_render_checkbox', 'leyka_render_checkbox_field', 10, 2);
function leyka_render_checkbox_field($option_name, $data){
    
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name; //var_dump($data);?>

    <div id="<?php echo $option_name.'-wrapper'?>">
        <label for="<?php echo $option_name.'-field';?>">
            <span class="field-component title"><?php echo $data['title'];?></span>
            <span class="field-component field"><input type="checkbox" id="<?php echo $option_name.'-field';?>" name="<?php echo $option_name;?>" value="1" <?php echo intval($data['value']) >= 1 ? 'checked' : '';?> />&nbsp;
            <?php echo esc_attr($data['description']);?></span>
        </label>
    </div>
<?php }

/** Multicheckbox fields */
add_action('leyka_render_multi_checkbox', 'leyka_render_multi_checkboxes_fields', 10, 2);
function leyka_render_multi_checkboxes_fields($option_name, $data){
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name; ?>

    <div id="<?php echo $option_name.'-wrapper';?>">
        <span class="field-component title">
            <?php echo $data['title'];?>
            <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
        </span>
        
        <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $label) {?>
                <label for="<?php echo $option_name.'-'.$value.'-field';?>">
                    <input type="checkbox" id="<?php echo $option_name.'-'.$value.'-field';?>" name="<?php echo $option_name;?>[]" value="<?php echo $value;?>" <?php echo in_array($value, $data['value']) ? 'checked' : '';?> />
                    &nbsp;<?php echo esc_attr($label);?>
                </label>                
            <?php }?>
        </span>
    </div>
<?php }

/** Radio fields */
add_action('leyka_render_radio', 'leyka_render_radio_fields', 10, 2);
function leyka_render_radio_fields($option_name, $data){
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper';?>">
        <span class="field-component title">
            <?php echo $data['title'];?>
            <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
        </span>
        
        <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $label) {?>
                <label for="<?php echo $value.'-field';?>">
                    <input type="radio" id="<?php echo $value.'-field';?>" name="<?php echo $option_name;?>" value="<?php echo $value;?>" <?php echo $data['value'] == $value ? 'checked' : '';?> />
                    &nbsp;<?php echo esc_attr($label);?>
                </label>
            <?php }?>
        </span>
    </div>
<?php }

/** Select fields */
add_action('leyka_render_select', 'leyka_render_select_field', 10, 2);
function leyka_render_select_field($option_name, $data) {
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper';?>">
        <label for="<?php echo $option_name.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            
            <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
            }?>

            <select id="<?php echo $option_name.'-field';?>" name="<?php echo $option_name;?>">
                <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                    <option value="<?php echo $value;?>" <?php echo $value == $data['value'] ? 'selected' : '';?>><?php echo esc_attr($label);?></option>
                <?php }?>
            </select>
            </span>
        </label>
    </div>
<?php }

/** Multi-select fields */
add_action('leyka_render_multi_select', 'leyka_render_multi_select_field', 10, 2);
function leyka_render_multi_select_field($option_name, $data) {
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper';?>">
        <label for="<?php echo $option_name.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            
            <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
            }?>

            <select id="<?php echo $option_name.'-field';?>" name="<?php echo $option_name;?>" size="<?php echo $data['length'];?>">
                <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                    <option value="<?php echo $value;?>"><?php echo esc_attr($label);?></option>
                <?php }?>
            </select>
            </span>
        </label>
    </div>
<?php }

/** Textarea fields */
add_action('leyka_render_textarea', 'leyka_render_textarea_field', 10, 2);
function leyka_render_textarea_field($option_name, $data){ 
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper'?>">
        <label for="<?php echo $option_name.'-field';?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            <span class="field-component field">
                <textarea id="<?php echo $option_name.'-field';?>" name="<?php echo $option_name;?>" rows="" cols="">
                    <?php echo esc_attr($data['value']);?>
                </textarea>
            </span>
            <span class="field-component help"><?php echo $data['description'];?></span>
        </label>
    </div>
<?php }

/** Simple HTML fields */
add_action('leyka_render_html', 'leyka_render_html_field', 10, 2);
function leyka_render_html_field($option_name, $data){ 
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name; ?>

    <div id="<?php echo $option_name.'-wrapper';?>">
        <label for="<?php echo $option_name;?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            <?php wp_editor(html_entity_decode(stripslashes($data['value'])), $option_name.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_name,
                'tinymce' => false,
                'textarea_rows' => 3,
                'teeny' => true, // For little-functioned HTML editor
//                    'dfw' => true,
            ));?>
            <span class="field-component help"><?php echo $data['description'];?></span>
        </label>
    </div>
<?php }

/** Rich HTML fields */
add_action('leyka_render_rich_html', 'leyka_render_rich_html_field', 10, 2);
function leyka_render_rich_html_field($option_name, $data){
    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;?>

    <div id="<?php echo $option_name.'-wrapper'?>">
        <label for="<?php echo $option_name;?>">
            <span class="field-component title">
                <?php echo $data['title'];?>
                <?php echo $data['required'] ? '<span class="required">*</span>' : '';?>
            </span>
            <?php wp_editor(esc_attr($data['value']), $option_name.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_name,
                'tinymce' => false,
                'teeny' => false, // For rich HTML editor
//                    'dfw' => true,
            ));?>
            <span class="field-component help"><?php echo $data['description'];?></span>
        </label>
    </div>
<?php }

/** Special field: gateways commission options */
add_action('leyka_render_custom_gateways_commission', 'leyka_render_gateways_commission_field', 10, 2);
function leyka_render_gateways_commission_field($option_name, $data){

    $option_name = stristr($option_name, 'leyka_') ? $option_name : 'leyka_'.$option_name;
    $option_value = leyka_options()->opt('commission');?>

    <div id="<?php echo $option_name.'-wrapper';?>">

    <?php foreach(leyka_get_gateways() as $gateway) { /** @var Leyka_Gateway $gateway */?>

        <div style="display: flex;">
            <h4><?php echo $gateway->name;?></h4>
            <div style="display: flex;">
        <?php foreach($gateway->get_payment_methods() as $pm) {?>

                <span>
                    <input type="number" name="leyka_commission[<?php echo $pm->full_id;?>]" value="<?php echo isset($option_value[$pm->full_id]) ? (float)$option_value[$pm->full_id] : '0.0';?>" step="0.01" min="0.0" max="100.0" id="leyka_commission_<?php echo $pm->full_id;?>"> %
                    <label style="display: block" for="leyka_commission_<?php echo $pm->full_id;?>"><?php echo $pm->name;?></label>
                </span>

        <?php }?>
            </div>
        </div>

    <?php }?>

    </div>

<?php }

add_action('leyka_save_custom_setting_commission', 'leyka_save_custom_setting_commission');
function leyka_save_custom_setting_commission($option_value) {

    foreach($option_value as $pm_full_id => $commission) {

        $commission = trim($commission);
        $commission = (float)str_replace(',', '.', $commission);

        $option_value[$pm_full_id] = $commission < 0.0 ? -$commission : $commission;

    }

    $option_value = maybe_serialize($option_value);
    if($option_value != leyka_options()->opt('commission')) {
        leyka_options()->opt('commission', $option_value);
    }

}

add_filter('leyka_option_value', 'leyka_get_commission_values', 10, 2);
function leyka_get_commission_values($value, $option_name) {
    return $option_name == 'commission' ? maybe_unserialize($value) : $value;
}

/** Special field: gateways commission options - END */