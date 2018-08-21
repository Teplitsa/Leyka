<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Step class.
 **/

class Leyka_Settings_Step {

    protected $_id;
    protected $_section_id;
    protected $_title = '';
    protected $_params = array();

    /** A Composite blocks structure object??? */
    protected $_blocks;

    public function __construct($id, $section_id, $title = '', array $params = array()) {

        $this->_id = trim($id);
        $this->_section_id = trim($section_id);
        $this->_title = trim($title);

        $this->_params = $params + array(
            'header_classes' => '',
        );

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            case 'section_id':
                return $this->_section_id;
            case 'full_id':
                return $this->_section_id.'-'.$this->_id;
            case 'title':
                return $this->_title;
            case 'blocks':
                return $this->_blocks;
            default:
                return isset($this->_params[$name]) ? $this->_params[$name] : null; // Throw some Exception?
        }
    }

    public function addTo(Leyka_Settings_Section $section) {

        $section->addStep($this);

        return $this;

    }

    public function addBlock(Leyka_Settings_Block $block) {

        $this->_blocks[$block->id] = $block;

        return $this;

    }

    /** @return array */
    public function getBlocks() {
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

    /**
     * @return array An array of (block_id => an array of WP_Error objects, with one field error in each)
     */
    public function getErrors() {

        $errors = array();

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $errors = array_merge($errors, $block->getErrors());
        }

        return $errors;

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {

        $fields = array();

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $fields = array_merge($fields, $block->getFieldsValues());
        }

        return $fields;

    }

}