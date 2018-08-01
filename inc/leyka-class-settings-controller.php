<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard class.
 **/

abstract class Leyka_Settings_Controller extends Leyka_Singleton { // Each descendant is a concrete wizard

    protected $_id;
    protected $_title;

    /** @var $_sections array of Leyka_Wizard_Section objects */
    protected $_sections;

    /** @var $_current_section Leyka_Settings_Section */
//    protected $_current_section;

    /** @var $_current_step Leyka_Settings_Step */
//    protected $_current_step;

    protected static $_instance = null;

    protected function __construct() {

        $this->_set_attributes();
        $this->_set_sections();

    }

    abstract protected function _set_attributes();
    abstract protected function _set_sections();

    /** @todo */
//    protected function getNavChain() {
//        return array();
//    }
//
//    /** @todo */
//    protected function getCurrentStepBlocks() {
//        return array();
//    }

    /** @return Leyka_Settings_Step */
    abstract public function getCurrentStep();

    /** @return Leyka_Settings_Section */
    abstract public function getCurrentSection();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;
    protected $_storage_key = '';

    // Some methods to incapsulate $_SESSION or $_COOKIE access

    protected function __construct() {

        parent::__construct();

        if( !$this->_sections ) {
            return;
        }

        if( !$this->current_section ) {
            $this->_setCurrentSection(reset($this->_sections));
        }
        if( !$this->current_step ) {
            $this->_setCurrentStep($this->current_section->init_step);
        }

    }

    protected function _setCurrentStep(Leyka_Settings_Step $step) {
        $_SESSION[$this->_storage_key]['current_step'] = $step;
    }

    protected function _setCurrentSection(Leyka_Settings_Section $section) {
        $_SESSION[$this->_storage_key]['current_section'] = $section;
    }

    public function __get($name) {
        switch($name) {
            case 'current_step': return $_SESSION[$this->_storage_key]['current_step'];
            case 'current_section': return $_SESSION[$this->_storage_key]['current_section'];
            case 'next_step': return '';
            default:
                return null;
        }
    }

    public function getCurrentSection() {
        return $this->current_section;
    }

    public function getCurrentStep() {
        return $this->current_step;
    }

    public function processStepSubmit() {

    }

    /**
     * Steps branching incapsulation method. The result must be filterable. By default, it's next step in _steps array.
     *
     * @param $step Leyka_Settings_Step
     * @param $data array
     * @return Leyka_Settings_Step
     */
    abstract protected function _getNextStep(Leyka_Settings_Step $step = null, array $data = array());

}

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'init';
        $this->_title = 'The Init wizard';

    }

    protected function _set_sections() {

        $this->_sections[] = Leyka_Init_ReceiverData_Section::get_instance();
        $this->_sections[] = Leyka_Init_CampaignData_Section::get_instance();

    }

    protected function _getNextStep(Leyka_Settings_Step $step = null, array $data = array()) {
        return $step;
    }

}