<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Block class.
 **/

abstract class Leyka_Settings_Block {

    protected $_id;

    public function __construct(array $params = array()) {

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

    abstract public function getContent();
    abstract public function isValid();
    abstract public function getErrors();
    abstract public function getFieldsValues();

}

class Leyka_Text_Block extends Leyka_Settings_Block {

    protected $_text = '';

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if( !empty($params['text'] ) ) {
            $this->_text = $params['text'];
        }

    }

    public function getContent() {
        return $this->_text;
    }

    public function isValid() {
        return true;
    }

    public function getErrors() {
        return array();
    }

    public function getFieldsValues() {
        return array();
    }

}

class Leyka_Subtitle_Block extends Leyka_Settings_Block {

    protected $_subtitle_text = '';

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if( !empty($params['text'] ) ) {
            $this->_subtitle_text = $params['text'];
        }

    }

    public function getContent() {
        return $this->_subtitle_text;
    }

    public function isValid() {
        return true;
    }

    public function getErrors() {
        return array();
    }

    public function getFieldsValues() {
        return array();
    }

}

class Leyka_Option_Block extends Leyka_Settings_Block {

    protected $_option_id = '';
    protected $_params = array();

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if(empty($params['option_id'])) {
            /** @todo Throw some Exception */
        } else if( !leyka_options()->option_exists($params['option_id']) ) {
            /** @todo Throw some Exception */
        }

        $this->_params = wp_parse_args($params, array(
            'title' => null,
            'show_title' => true,
            'description' => null,
            'show_description' => true,
            'required' => null,
        ));

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
            default: return parent::__get($name);
        }

    }

    public function getContent() {
        return $this->_option_id;
    }

    public function isValid() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;

        return leyka_options()->is_valid($this->_option_id, $value);

    }

    public function getErrors() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;
        $errors = array();

        foreach(leyka_options()->get_validation_errors($this->_option_id, $value) as $error_message) {
            $errors[] = new WP_Error('option_invalid', $error_message);
        }

        return $errors ? array($this->_id => $errors) : array();

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {
        return isset($_POST['leyka_'.$this->_option_id]) ?
            array($this->_option_id => $_POST['leyka_'.$this->_option_id]) : array();
    }

}

class Leyka_Container_Block extends Leyka_Settings_Block {

    protected $_blocks;
    protected $_entry_width = false;

    public function __construct(array $params = array()) {

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

    }

    public function __get($name) {

        switch($name) {
            case 'entry_width': return $this->_entry_width ?
                $this->_entry_width :
                (count($this->_blocks) ? round(1.0/count($this->_blocks), 1) : false);
            default: return parent::__get($name);
        }

    }

    public function addBlock(Leyka_Settings_Block $block) {
        $this->_blocks[] = $block;
    }

    public function getContent() {
        return $this->_blocks;
    }

    public function isValid() {

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            if( !$block->isValid() ) {
                return false;
            }
        }

        return true;

    }

    public function getErrors() {

        $errors = array();

        foreach($this->_blocks as $sub_block) { /** @var $sub_block Leyka_Settings_Block */

            $sub_block_errors = $sub_block->getErrors();
            $errors = array_merge($errors, $sub_block_errors);

        }

        return $errors;

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {

        $fields_values = array();

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $fields_values = array_merge($fields_values, $block->getFieldsValues());
        }

        return $fields_values;

    }

}

class Leyka_Custom_Setting_Block extends Leyka_Settings_Block {

    protected $_setting_id = '';
    protected $_field_type = '';
    protected $_rendering_type = 'callback';
    protected $_field_data = array();
    protected $_fields_keys = array();

    public function __construct(array $params = array()) {

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
         * text, html, rich_html, select, radio, checkbox, multi_checkbox, custom_XXX.
         * If check is failed, throw some Exception.
         */
        $this->_field_type = $params['field_type'];
        $this->_rendering_type = $params['rendering_type'];

        $this->_field_data = empty($params['data']) ? array() : array($params['data']);
        $this->_fields_keys = empty($params['keys']) ? array() : array($params['keys']);

    }

    public function __get($name) {

        switch($name) {
            case 'custom_id':
            case 'custom_setting_id':
                return $this->_setting_id;
            default: return parent::__get($name);
        }

    }

    public function getContent() {

        if(stripos($this->_field_type, 'custom_') === false || $this->_rendering_type === 'callback') {
            // If the setting is either one of standard field types, or a custom one without template script,
            // render it setting via callback:
            do_action("leyka_render_{$this->_field_type}", $this->_setting_id, $this->_field_data);
        } else if($this->_rendering_type === 'template') {
            /** @todo Require some setting template file */
        }

    }

    public function isValid() {
        return apply_filters('leyka_custom_setting_valid-'.$this->_field_type, true, $this->_setting_id, $this->_field_data);
    }

    public function getErrors() {
        return apply_filters(
            'leyka_custom_setting_validation_errors-'.$this->_field_type,
            false,
            $this->_setting_id,
            $this->_field_data
        );
    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {

        $values = array();

        foreach($this->_fields_keys as $key) {
            if(isset($_POST['leyka_'.$key])) {
                $values[$key] = $_POST['leyka_'.$key];
            }
        }

        return $values;

    }

}