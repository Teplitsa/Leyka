<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard class.
 **/

abstract class Leyka_Wizard { // Each descendant is a concrete wizard

    protected $_id;
    protected $_title;
    protected $_render;
    protected $_sections;

    protected $_current_section;
    protected $_current_step;

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard
     */
    public final static function get_instance() {

        if(null == static::$_instance) {

            static::$_instance = new static();
//            static::$_instance->_initialize_options();

        }

        return static::$_instance;

    }

    final protected function __clone() {}

    protected function __construct() {

        $this->_set_attributes();
        $this->_set_sections();

        echo '<pre>'.print_r($this->_title.' instantiated!', 1).'</pre>';

    }

    abstract protected function _set_attributes();
    abstract protected function _set_sections();

    /** @todo */
    protected function getNavChain() {
        return array();
    }

    /** @todo */
    protected function getCurrentStepBlocks() {
        return array();
    }

    /** @todo */
    protected function getCurrentStepId() {
        return 'some-id';
    }

    public function display() {

        if( !$this->_sections ) {
            return;
        }

        if( !$this->_current_section ) {
            // initialize current section with the first one...
        } else {
            echo '<pre>'.print_r(reset($this->_sections), 1).'</pre>';
        }

    }

    public function processStepSubmit() {

    }

//    public function getCurrentSection() {
//
//    }

}

class Leyka_Init_Wizard extends Leyka_Wizard {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'init';
        $this->_title = 'The Init wizard';

    }

    protected function _set_sections() {

        $this->_sections[] = Leyka_Init_ReceiverData_Section::get_instance();
//        $this->_sections[] = Leyka_Init_CampaignSettings_Section::get_instance();

    }

}