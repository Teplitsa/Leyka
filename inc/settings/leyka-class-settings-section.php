<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Section class.
 **/

class Leyka_Settings_Section {

    protected $_id;
    protected $_stage_id;
    protected $_title = '';
    protected $_params = [];
    protected $_handler = false;

    protected $_blocks;

    public function __construct($id, $stage_id, $title = '', array $params = []) {

        $this->_id = trim($id);
        $this->_stage_id = trim($stage_id);
        $this->_title = trim($title);

        $this->_params = wp_parse_args($params, [
            'header_classes' => '',
            'form_enctype' => '',
        ]);

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            case 'stage_id':
                return $this->_stage_id;
            case 'full_id':
                return $this->_stage_id.'-'.$this->_id;
            case 'title':
                return $this->_title;
            case 'blocks':
                return $this->_blocks ? : [];
            case 'handler':
                return $this->_handler;
            default:
                return isset($this->_params[$name]) ? $this->_params[$name] : null; // Throw some Exception?
        }
    }

    public function add_to(Leyka_Settings_Stage $section) {

        $section->add_section($this);

        return $this;

    }

    /**
     * @param $handler mixed Either function name (string) of the function itself (callback)
     * @return $this
     */
    public function add_handler($handler) {

        if(is_callable($handler)) {
            $this->_handler = $handler;
        } else {
            /** @todo Throw an Exception here */
        }

        return $this;

    }

    /** @return mixed */
    public function get_handler() {
        return $this->_handler === false ? null : $this->_handler;
    }

    public function has_handler() {
        return !!$this->_handler;
    }

    public function add_block(Leyka_Settings_Block $block) {

        $this->_blocks[$block->id] = $block;

        return $this;

    }

    /** @return array */
    public function get_blocks() {
        return $this->blocks;
    }

    public function is_valid() {

        foreach($this->blocks as $block) { /** @var $block Leyka_Settings_Block */
            if( !$block->is_valid() ) {
                return false;
            }
        }

        return true;

    }

    /**
     * @return array An array of (block_id => an array of WP_Error objects, with one field error in each)
     */
    public function get_errors() {

        $errors = [];

        foreach($this->blocks as $block) { /** @var $block Leyka_Settings_Block */
            $errors = array_merge($errors, $block->get_errors());
        }

        return $errors;

    }

    /**
     * Get all options & values set on the step
     * @return array
     */
    public function get_fields_values() {

        $fields = [];

        foreach($this->blocks as $block) { /** @var $block Leyka_Settings_Block */
            $fields = array_merge($fields, $block->get_fields_values());
        }

        return $fields;

    }

}