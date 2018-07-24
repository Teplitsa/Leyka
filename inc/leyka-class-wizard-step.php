<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Step class.
 **/

abstract class Leyka_Wizard_Step {

    protected $_id;
    protected $_title;

    /** A Composite blocks structure object */
    protected $_blocks;

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard_Step
     */
    public final static function get_instance() {

        if(null == static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;

    }

    final protected function __clone() {}

    protected function __construct() {

        $this->_set_attributes();
        $this->_set_blocks();

    }

    abstract protected function _set_attributes();
    abstract protected function _set_blocks();
    abstract protected function _processStep();

    /** @return Leyka_Blocks_Composite */
    public function getBlocks() {
        return $this->_blocks;
    }

    abstract public function stepIsValid();
    abstract public function getStepErrors();

}

class Leyka_Init_AccountType_Step extends Leyka_Wizard_Step {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'account-type';
        $this->_title = 'Step 1: Receiver account type';

    }

    protected function _set_blocks() {

        $this->_blocks[] = 'Block 1'; /** @todo Real steps objects here */

    }

    protected function _processStep() {
        /** @todo Save the step fields values */
    }

    public function stepIsValid() {
        /** @todo Do the step fields validation */
        return true;
    }

    public function getStepErrors() {
        /** @todo If step isn't valid, return an array with validation errors */
        return array();
    }

}