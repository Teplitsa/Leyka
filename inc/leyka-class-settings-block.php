<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Block class.
 **/

/** @todo Find some new name - closer to the reality. Step Content Row, mb */
abstract class Leyka_Settings_Block {

    protected $_id;

    public function __construct(array $params = array()) {

        if( !empty($params['id']) ) {
            $this->_id = trim($params['id']);
        }

    }

    abstract public function getContent();
    abstract public function isValid();
    abstract public function getErrors();

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

}

class Leyka_Option_Block extends Leyka_Settings_Block {

    protected $_option_id = '';

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if(empty($params['option_id'])) {
            /** @todo Throw some Exception */
        } else if( !leyka_options()->option_exists($params['option_id']) ) {
            /** @todo Throw some Exception */
        }

        $this->_option_id = $params['option_id'];

    }

    public function getContent() {
        return leyka_options()->get_info_of($this->_option_id);
    }

    public function isValid() {
        return leyka_options()->is_valid($this->_option_id);
    }

    public function getErrors() {
        return leyka_options()->get_validation_errors($this->_option_id);
    }

}

class Leyka_Container_Block extends Leyka_Settings_Block {

    protected $_blocks;

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if( !empty($params['subblocks']) && is_array($params['subblocks']) ) {

            foreach($params['subblocks'] as $block) {
                if( !is_a($block, 'Leyka_Settings_Block') ) {
                    /** @todo Throw some Exception */
                } else {
                    $this->_blocks[] = $block;
                }
            }

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

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $errors = array_merge($errors, $block->getErrors());
        }

        return $errors;

    }

}