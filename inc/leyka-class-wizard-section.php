<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Section class.
 **/

abstract class Leyka_Wizard_Section {

    protected $_id;
    protected $_title;
    protected $_steps;
    protected $_init_step = false;
    protected $_last_step = false;

//    protected $_current_step;

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard_Section
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
        $this->_set_steps();

        echo '<pre>'.print_r($this->_title.' instantiated!', 1).'</pre>';

    }

    abstract protected function _set_attributes();
    abstract protected function _set_steps();

    /**
     * @param $step mixed Either Leyka_Wizard_Step object ID or a whole object
     * @return Leyka_Wizard_Step
     */
    abstract protected function _get_next_step($step);

}

class Leyka_Init_ReceiverData_Section extends Leyka_Wizard_Section {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'rd';
        $this->_title = 'Section 1: Receiver data';

    }

    protected function _set_steps() {

//        $this->_init_step = 'Step 0'; /** @todo Real steps objects here */

        $this->_steps[] = Leyka_Init_AccountType_Step::get_instance();

//        $this->_init_step = 'Last step';

    }

    protected function _get_next_step($step) {
        /** @todo */
    }

}