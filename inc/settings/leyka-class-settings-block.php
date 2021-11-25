<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Block class.
 **/

abstract class Leyka_Settings_Block {

    protected $_id;

    public function __construct(array $params = []) {

        if( !empty($params['id']) ) {
            $this->_id = trim($params['id']);
        }

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            default:
                return null;
        }
    }

    abstract public function get_content();
    abstract public function is_valid();
    abstract public function get_errors();
    abstract public function get_fields_values();

}

class Leyka_Text_Block extends Leyka_Settings_Block {

    protected $_text = '';
    protected $_template = null;

    public function __construct(array $params = []) {

        parent::__construct($params);
        
        if( !empty($params['text'] ) ) {
            $this->_text = $params['text'];
        }

        if( !empty($params['template']) ) {
            $this->_template = $params['template'];
        }
        
    }
    
    public function has_custom_templated() {
        return !empty($this->_template);
    }

    public function get_content() {
        return $this->_template ? $this->get_templated_content() : $this->_text;
    }
    
    protected function get_templated_content() {

        ob_start();

        $template_file = apply_filters(
            'leyka_text_field_template',
            LEYKA_PLUGIN_DIR."inc/settings-fields-templates/leyka-{$this->_template}.php",
            $this->_template
        );

        if(file_exists($template_file)) {
            require($template_file);
        } else {
            /** @todo Throw some Leyka_Exception */
        }

        return ob_get_clean();

    }

    public function is_valid() {
        return true;
    }

    public function get_errors() {
        return [];
    }

    public function get_fields_values() {
        return [];
    }

}

class Leyka_Subtitle_Block extends Leyka_Settings_Block {

    protected $_subtitle_text = '';

    public function __construct(array $params = []) {

        parent::__construct($params);

        if( !empty($params['text'] ) ) {
            $this->_subtitle_text = $params['text'];
        }

    }

    public function get_content() {
        return $this->_subtitle_text;
    }

    public function is_valid() {
        return true;
    }

    public function get_errors() {
        return [];
    }

    public function get_fields_values() {
        return [];
    }

}

class Leyka_Option_Block extends Leyka_Settings_Block {

    protected $_option_id = '';
    protected $_params = [];

    public function __construct(array $params = []) {

        parent::__construct($params);

        if(empty($params['option_id'])) {
            /** @todo Throw some Exception */
        } else if( !leyka_options()->option_exists($params['option_id']) ) {
            /** @todo Throw some Exception */
        }

        $this->_params = wp_parse_args($params, [
            'title' => null,
            'show_title' => true,
            'description' => null,
            'show_description' => true,
            'required' => null,
            'width' => 1.0,
        ]);

        $this->_option_id = $params['option_id'];

        if($this->title) {
            add_filter('leyka_option_title-'.$this->_option_id, function(){
                return $this->title;
            });
        }
        if( !is_null($this->required) ) {
            add_filter('leyka_option_required-'.$this->_option_id, function(){
                return $this->required;
            });
        }

    }

    public function __get($name) {

        switch($name) {
            case 'option_id': return $this->_option_id;
            case 'title': return empty($this->_params['title']) ? false : trim($this->_params['title']);
            case 'show_title': return !!$this->_params['show_title'];
            case 'description': return empty($this->_params['description']) ? false : trim($this->_params['description']);
            case 'show_description': return !!$this->_params['show_description'];
            case 'required': return is_null($this->_params['required']) ? null : !!$this->_params['required'];
            case 'width': return $this->_params['width'] && abs($this->_params['width']) <= 1.0 ?
                round(abs($this->_params['width']), 2) : 1.0;
            default: return parent::__get($name);
        }

    }

    public function get_content() {
        return $this->_option_id;
    }

    public function is_valid() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;

        return leyka_options()->is_valid($this->_option_id, $value);

    }

    public function get_errors() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;
        $errors = [];

        foreach(leyka_options()->get_validation_errors($this->_option_id, $value) as $error_message) {
            $errors[] = new WP_Error('option_invalid', $error_message);
        }

        return $errors ? [$this->_id => $errors,] : [];

    }

    /**
     * Get all options & values set on the step
     * @return array
     */
    public function get_fields_values() {
        return isset($_POST['leyka_'.$this->_option_id]) ?
            [$this->_option_id => $_POST['leyka_'.$this->_option_id]] : [];
    }

}

class Leyka_Container_Block extends Leyka_Settings_Block {

    protected $_blocks;
    protected $_entry_width = false;
    protected $_params = [];

    public function __construct(array $params = []) {

        parent::__construct($params);

        if( !empty($params['entry_width']) ) {

            $params['entry_width'] = (float)$params['entry_width'];
            if($params['entry_width'] > 0.0 && $params['entry_width'] <= 1.0) {
                $this->_entry_width = $params['entry_width'];
            }

        }

        if( !empty($params['entries']) && is_array($params['entries']) ) {

            foreach($params['entries'] as $block) {
                if( !is_a($block, 'Leyka_Settings_Block') ) {
                    /** @todo Throw some Exception */
                } else {
                    $this->_blocks[] = $block;
                }
            }

        }

        if( !empty($params['classes']) ) {
            $this->_params['classes'] = $params['classes'];
        }

    }

    public function __get($name) {

        switch($name) {
            case 'entry_width': return $this->_entry_width ?
                $this->_entry_width :
                (count($this->_blocks) ? round(1.0/count($this->_blocks), 1) : false);
            case 'class':
            case 'classes':
                return empty($this->_params['classes']) ? '' : $this->_params['classes'];
            default: return parent::__get($name);
        }

    }

    public function __set($name, $value) {
        switch($name) {
            case 'class':
            case 'classes':
                $this->_params['classes'] = $value;
        }
    }

    public function add_block(Leyka_Settings_Block $block) {
        $this->_blocks[] = $block;
    }

    public function get_content() {
        return $this->_blocks;
    }

    public function is_valid() {

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            if( !$block->is_valid() ) {
                return false;
            }
        }

        return true;

    }

    public function get_errors() {

        $errors = [];

        foreach($this->_blocks as $sub_block) { /** @var $sub_block Leyka_Settings_Block */
            $errors = array_merge($errors, $sub_block->get_errors());
        }

        return $errors;

    }

    /**
     * Get all options & values set on the step
     * @return array
     */
    public function get_fields_values() {

        $fields_values = [];

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $fields_values = array_merge($fields_values, $block->get_fields_values());
        }

        return $fields_values;

    }

}

class Leyka_Custom_Setting_Block extends Leyka_Settings_Block {

    protected $_setting_id = '';
    protected $_field_type = '';
    protected $_rendering_type = 'callback';
    protected $_field_data = [];
    protected $_fields_keys = [];

    public function __construct(array $params = []) {

        parent::__construct($params);

        if(empty($params['custom_setting_id'])) {
            /** @todo Throw some Exception */
        }
        if(empty($params['field_type'])) {
            /** @todo Throw some Exception */
        }

        $this->_setting_id = $params['custom_setting_id'];

        /**
         * @todo Add a check for possible field type:
         * text, textarea, html, rich_html, select, radio, checkbox, multi_checkbox, custom_XXX.
         * If check is failed, throw some Exception.
         */
        $this->_field_type = $params['field_type'];
        $this->_rendering_type = empty($params['rendering_type']) ? 'callback' : $params['rendering_type'];

        $this->_field_data = empty($params['data']) ? [] : (array)$params['data'];
        $this->_fields_keys = empty($params['keys']) || !is_array($params['keys']) ? [$this->_setting_id] : $params['keys'];
        
    }

    public function __get($name) {

        switch($name) {
            case 'setting_id':
            case 'custom_setting_id':
                return $this->_setting_id;
            case 'field_type':
                return $this->_field_type;
            case 'field_data':
                return $this->_field_data;
            case 'is_standard_field_type':
                return leyka_options()->is_standard_field_type($this->_field_type);
            default: return parent::__get($name);
        }

    }

    public function get_content() {

        ob_start();

        if($this->is_standard_field_type || $this->_rendering_type === 'callback') {
            // If the setting is either one of standard field types, or a custom one without template script,
            // render it setting via callback:
            do_action("leyka_render_{$this->_field_type}", $this->_setting_id, $this->_field_data);
        } else if($this->_rendering_type === 'template') {

            $field_type = str_replace('custom_', '', $this->_field_type);
            $template_file = apply_filters(
                'leyka_setting_field_template-'.$field_type,
                LEYKA_PLUGIN_DIR."inc/settings-fields-templates/leyka-{$field_type}.php",
                $this->_setting_id,
                $this->_field_data,
                $this->_fields_keys
            );

            if(file_exists($template_file)) {
                require($template_file);
            } else {
                /** @todo Throw some Leyka_Exception */
            }

        }

        return ob_get_clean();

    }

    public function is_valid() {

        $is_valid = true;

        if( !empty($this->_field_data['required']) ) {
            foreach($this->_fields_keys as $key) {

                // in new style upload file already uploaded and its path passed in $_POST
//                 if($this->_field_type === 'file') {
//                     $is_valid = $this->is_file_field_valid();
//                 } else 
                if(empty($_POST[ $this->is_standard_field_type ? 'leyka_'.$key : $key ])) {
                    $is_valid = false;
                }

                if( !$is_valid ) {
                    break;
                }

            }
        }

        return apply_filters(
            'leyka_custom_setting_valid-'.$this->_field_type,
            $is_valid,
            $this->_setting_id,
            $this->_field_data,
            $this->_fields_keys
        );

    }
    
    public function is_file_field_valid() {

        if( !isset($_FILES['leyka_'.$this->_setting_id ]) ) {
            return false;
        }

        $file = $_FILES['leyka_'.$this->_setting_id];

        return $file && empty($file['error']) && !empty($file['size']);

    }

    public function get_errors() {

        $errors = [];

        if( !empty($this->_field_data['required']) ) {

            $error_text = $this->_field_data['required'] === true ?
                __('The field value is required', 'leyka') : __($this->_field_data['required']);

            foreach($this->_fields_keys as $key) {
                if($this->_field_type === 'file' && !$this->is_file_field_valid()) {
                    $errors[] = new WP_Error('option_invalid', $error_text);
                } else if(empty($_POST[ $this->is_standard_field_type ? 'leyka_'.$key : $key ])) {
                    $errors[] = new WP_Error('option_invalid', $error_text);
                }
            }

        }

        $errors = $errors ? [$this->_id => $errors] : [];

        return apply_filters(
            'leyka_custom_setting_validation_errors-'.$this->_field_type,
            $errors,
            $this->_setting_id,
            $this->_field_data,
            $this->_fields_keys
        );

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function get_fields_values() {

        $values = [];

        foreach($this->_fields_keys as $key) {
            if(isset($_POST[ $this->is_standard_field_type ? 'leyka_'.$key : $key ])) {
                $values[$key] = $_POST[ $this->is_standard_field_type ? 'leyka_'.$key : $key ];
            }
        }

        return $values;

    }

}
