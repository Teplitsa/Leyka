<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard class.
 **/

class Leyka_Wizard_Controller { // There will be 1 descendant class for each concrete wizard

    protected $_current_state; // A pointer on one particular step in the flow
    protected $_flow_scheme; // A multitude of states. A Composite obj, mb (to represent the tree stucture of the flow)

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard_Controller
     */
    public static function get_instance() {

        if( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;

    }



}

class Leyka_Wizard_State {

    protected $_step_id;
    protected $_nav_chain = array();
    protected $_options = array();

    protected static $_instance = null;

    /** @return Leyka_Wizard_State */
    public static function get_instance() {

        if( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;

    }

    protected function __construct() {
    }

    /** @param string $next_step_id */
    public function doStep($next_step_id = null) { // By default, go to the next step
        /** @todo */
    }

    /** @return string */
    public function getCurrentStepId() {
        return $this->_step_id;
    }

    /** @return array */
    public function getCurrentStepNavChain() {
        return $this->_nav_chain;
    }

    /** @return array */
    public function getCurrentStepOptions() {
        return $this->_options;
    }

}

class Leyka_Init_AccountType_Step extends Leyka_Wizard_State {

    protected function __construct() {

        $this->_step_id = '1.1';

        $this->_nav_chain = array(
            '1' => array( // 1st section
                'title' => esc_html__('Your data', 'leyka'),
                'steps' => array(
                    '1' => array('title' => esc_html__('Account type', 'leyka'), 'is_current' => true),
                    '2' => array('title' => esc_html__("Donations receiver's name", 'leyka'),),
                    '3' => array('title' => esc_html__('Bank details', 'leyka'),),
                    '4' => array('title' => esc_html__('Terms of service', 'leyka'),),
                    '5' => array('title' => esc_html__('Terms of personal data usage', 'leyka'),),
                )
            )
        );
        $this->_options = array(
            array( // First line
                'legal_entity_type',
            ),
            array( // Second line
                'country'
            )
        );

    }

}