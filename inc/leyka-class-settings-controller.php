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

        $this->_storage_key = 'leyka-wizard_'.$this->_id;

        if( !$this->_sections ) {
            return;
        }

        if( !$this->current_section ) {
            $this->_setCurrentSection(reset($this->_sections));
        }

        if( !$this->current_step && $this->current_section ) {

            $init_step = $this->current_section->init_step;
            if($init_step) { /** @var $init_step Leyka_Settings_Step */
                $this->_setCurrentStep($init_step);
            }

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
            case 'current_step': return empty($_SESSION[$this->_storage_key]['current_step']) ?
                null : $_SESSION[$this->_storage_key]['current_step'];

            case 'current_step_id': return empty($_SESSION[$this->_storage_key]['current_step']) ?
                null : $this->current_step->id;

            case 'current_section': return empty($_SESSION[$this->_storage_key]['current_section']) ?
                null : $_SESSION[$this->_storage_key]['current_section'];

            case 'current_section_id': return empty($_SESSION[$this->_storage_key]['current_section']) ?
                null : $this->current_section->id;

            case 'next_step': return $this->_getNextStep();
            case 'next_step_id': return $this->next_step->id;
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
        /** @todo */
    }

    /**
     * Steps branching incapsulation method. The result must be filterable. By default, it's next step in _steps array.
     *
     * @param $step Leyka_Settings_Step
     * @param $data array
     * @return Leyka_Settings_Step
     */
    abstract protected function _getNextStep(Leyka_Settings_Step $step = null);

}

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'init';
        $this->_title = 'The Init wizard';

    }

    protected function _set_sections() {

        // Receiver's data Section:
        $section = new Leyka_Settings_Section('rd', 'Раздел 1: ваши данные');

        // 0-step:
        $step = new Leyka_Settings_Step('init',  $section->id, 'Шаг 0: вступительное сообщение');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro-text-1',
            'text' => 'Это будет первый абзац вступительного текста. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro-text-2',
            'text' => 'А это второй абзац вступительного текста начального шага начального же визарда. Путешествие в тысячу ли начинается с одного шага. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )));

        $section->addStep($step);

        // Receiver type step:
        $step = new Leyka_Settings_Step('account-type', $section->id, 'Шаг 1: тип получателя');
        $step->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-type', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_legal_type',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-country', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_country',
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Receiver's data Section - End

        // Campaign data Section:
        $section = new Leyka_Settings_Section('cd', 'Раздел 2: настройка кампании');

        $step = new Leyka_Settings_Step('init', $section->id, 'Шаг 0: настроим кампанию!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro-text-1',
            'text' => 'Осталось совсем немного и первая кампания будет запущена. Это будет первый абзац вступительного текста, и ещё немного о том, что дальше ещё нужно настроить платёжку.',
        )));

        $section->addStep($step);

        // Receiver type step:
        $step = new Leyka_Settings_Step('campaign-description',  $section->id, 'Шаг 1: описание вашей кампании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'campaign-desc-text',
            'text' => 'Текст, который идёт перед полями кампании на этом шаге. В нём может описываться, например, что вообще такое кампания.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-title',
            'option_id' => 'init-campaign-title',
            'option_data' => array(
                'type' => 'text',
                'title' => 'Название кампании',
//                'description' => '',
                'required' => 1,
                'placeholder' => 'Например, «На уставную деятельность организации»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-lead',
            'option_id' => 'init-campaign-lead',
            'option_data' => array(
                'type' => 'textarea',
                'title' => 'Краткое описание кампании',
                'required' => 0,
                'placeholder' => 'Например, «Ваше пожертвование пойдёт на выполнение уставной деятельности в текущем году.»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-target',
            'option_id' => 'init-campaign-target',
            'option_data' => array(
                'type' => 'number',
                'title' => 'Целевая сумма',
                'required' => 0,
                'min' => 0,
                'max' => 60000,
                'step' => 1,
            ),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'campaign-desc-finished',
            'text' => 'Текст, которым визард завершается. Здесь слова о том, что настройки выполнены, и вэлкам вам в дэшборд.',
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Campaign data Section - End

    }

    protected function _getNextStep(Leyka_Settings_Step $step = null) {
        return $step;
    }

}