<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Step class.
 **/

class Leyka_Wizard_Step {

    protected $_id;
    protected $_title;

    /** A Composite blocks structure object??? */
    protected $_blocks;

    public function __construct($id, $title = '' /*, array $params = array()*/) {

        $this->_id = trim($id);
        $this->_title = trim($title);

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            case 'title':
                return $this->_title;
            default:
                return false; // Throw some Exception?
        }
    }

    public function addBlock(Leyka_Wizard_Step_Block $block) {

        $this->_blocks[] = $block;

        return $this;

    }

    public function getBlocks() {
        return $this->_blocks;
    }

    public function isValid() {

        foreach($this->_blocks as $block) { /** @var $block Leyka_Wizard_Step_Block */
            if( !$block->isValid() ) {
                return false;
            }
        }

        return true;

    }

    public function getErrors() {

        $errors = array();

        foreach($this->_blocks as $block) { /** @var $block Leyka_Wizard_Step_Block */
            $errors = array_merge($errors, $block->getErrors());
        }

        return $errors;

    }

}