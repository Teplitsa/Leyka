<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard class.
 **/

abstract class Leyka_Settings_Controller extends Leyka_Singleton { // Each descendant is a concrete wizard

    protected $_id;
    protected $_title;
    protected $_common_errors = array();
    protected $_component_errors = array();

    /** @var $_sections array of Leyka_Wizard_Section objects */
    protected $_sections;

    protected static $_instance = null;

    protected function __construct() {

        $this->_setAttributes();
        $this->_setSections();

        add_action('leyka_settings_submit', array($this, 'handleSubmit'));

    }

    abstract protected function _setAttributes();
    abstract protected function _setSections();

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'title': return $this->_title;
            default: return null;
        }
    }

    /** @return array Of errors */
    public function getCommonErrors() {
        return $this->_common_errors;
    }

    /**
     * @param string $component_id
     * @return array Of errors
     */
    public function getComponentErrors($component_id = null) {
        return empty($component_id) ?
            $this->_component_errors :
            (empty($this->_component_errors[$component_id]) ? array() : $this->_component_errors[$component_id]);
    }

    protected function _handleSettingsSubmit() {

        if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) {
            do_action('leyka_settings_submit', $this->_id);
        }

    }

    protected function _addCommonError(WP_Error $error) {
        $this->_common_errors[] = $error;
    }

    protected function _addComponentError($component_id, WP_Error $error) {
        $this->_component_errors[$component_id] = $error;
    }

    /** @return Leyka_Settings_Step */
    abstract public function getCurrentStep();

    /** @return Leyka_Settings_Section */
    abstract public function getCurrentSection();

    abstract public function getSubmitSettings($structure_element = null);

    abstract public function handleSubmit();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;
    protected $_storage_key = '';

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

        // Debug {
//        if(isset($_GET['reset'])) {
//
//            $_SESSION[$this->_storage_key]['current_section'] = reset($this->_sections);
//            $_SESSION[$this->_storage_key]['current_step'] = reset($this->_sections)->init_step;
//            $_SESSION[$this->_storage_key]['activity'] = array();
//
//        }
        // } Debug

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) {
            $this->_handleSettingsGoBack();
        } else {
            $this->_handleSettingsSubmit();
        }

//        echo '<pre>Current activity: '.print_r($_SESSION[$this->_storage_key]['activity'], 1).'</pre>';

    }

    protected function _setCurrentStep(Leyka_Settings_Step $step) {

        $_SESSION[$this->_storage_key]['current_step'] = $step;

        return $this;

    }

    protected function _setCurrentSection(Leyka_Settings_Section $section) {

        $_SESSION[$this->_storage_key]['current_section'] = $section;

        return $this;

    }

    protected function _setCurrentStepById($step_full_id) {

        if(empty($step_full_id)) {
            return false;
        }

        $step = $this->getComponentById($step_full_id);
        if( !$step ) {
            return false;
        }

        $this->_setCurrentStep($step)
            ->_setCurrentSection($this->getComponentById($step->section_id));

        return true;

    }

    protected function _addActivityEntry() {

        $_SESSION[$this->_storage_key]['activity'][$this->getCurrentStep()->full_id] = $this->getCurrentStep()->getFieldsValues();

        return $this;

    }

    protected function _handleSettingsGoBack() {

        $last_step_full_id = array_key_last($_SESSION[$this->_storage_key]['activity']);

        if($last_step_full_id) {
            $this->_setCurrentStepById($last_step_full_id);
        } else {
            $this->_setCurrentSection(reset($this->_sections))
                ->_setCurrentStep($this->getCurrentSection()->init_step);
        }

        array_pop($_SESSION[$this->_storage_key]['activity']);

        return $this;

    }

    public function __get($name) {
        switch($name) {
            case 'current_step':
                return empty($_SESSION[$this->_storage_key]['current_step']) ?
                    null : $_SESSION[$this->_storage_key]['current_step'];

            case 'current_step_id':
                return empty($_SESSION[$this->_storage_key]['current_step']) ?
                    null : $this->current_step->id;

            case 'current_section':
                return empty($_SESSION[$this->_storage_key]['current_section']) ?
                    null : $_SESSION[$this->_storage_key]['current_section'];

            case 'current_section_id':
                return empty($_SESSION[$this->_storage_key]['current_section']) ?
                    null : $this->current_section->id;

            case 'next_step_full_id':
                return $this->_getNextStepId();

            default:
                return parent::__get($name);
        }
    }

    public function getCurrentSection() {
        return $this->current_section;
    }

    public function getCurrentStep() {
        return $this->current_step;
    }

    /**
     * @param $component_id string
     * @param  $is_full_id boolean
     * @return mixed Leyka_Settings_Step, Leyka_Settings_Section or null, if given component ID wasn't found
     */
    public function getComponentById($component_id, $is_full_id = true) {

        if( !$is_full_id ) {

            $section = $this->getCurrentSection();
            $step_id = $component_id;

        } else {

            $component_id = explode('-', $component_id); // [0] is a Section ID, [1] is a Step ID

            if(count($component_id) < 2 && $component_id[0]) {
                return empty($this->_sections[ $component_id[0] ]) ? null : $this->_sections[ $component_id[0] ];
            }

            if(empty($this->_sections[$component_id[0]])) {
                return null;
            }

            $section = $this->_sections[$component_id[0]];
            $step_id = $component_id[1];

        }

        $step = $section->getStepById($step_id);

        return $step;

    }

    public function handleSubmit() {

//        echo '<pre>Current step: '.print_r($this->getCurrentStep()->full_id.' (is valid: '.$this->getCurrentStep()->isValid().')', 1).'</pre>';

        if( !$this->getCurrentStep()->isValid() ) {

            foreach($this->getCurrentStep()->getErrors() as $component_id => $error) {
                $this->_addComponentError($component_id, $error);
            }

            return;

        }

        // Save the current step in the storage:
        $this->_addActivityEntry();

        // Proceed to the next step:
        $next_step_full_id = $this->_getNextStepId();
        if($next_step_full_id && $next_step_full_id !== true) {

//            echo '<pre>Next step: '.print_r($next_step_full_id, 1).'</pre>';

            $step = $this->getComponentById($this->_getNextStepId());

            if( !$step ) { /** @todo Process the error somehow */
                return;
            }

            $this->_setCurrentStep($step);

        }

    }

    /**
     * Steps branching incapsulation method. The result must be filterable. By default, it's next step in _steps array.
     *
     * @param $step_from Leyka_Settings_Step
     * @param $return_full_id boolean
     * @return mixed Either next step ID, or false (if non-existent step given), or true (if last step given).
     */
    abstract protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true);

}

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'init';
        $this->_title = 'The Init wizard';

    }

    protected function _setSections() {

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
        $step = new Leyka_Settings_Step('account_type', $section->id, 'Шаг 1: тип получателя');
        $step->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_type', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_legal_type',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_country', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_country',
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Receiver's data Section - End

        // Campaign data Section:
        $section = new Leyka_Settings_Section('cd', 'Раздел 2: настройка кампании');

        $step = new Leyka_Settings_Step('init', $section->id, 'Шаг 0: настроим кампанию!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro_text_1',
            'text' => 'Осталось совсем немного и первая кампания будет запущена. Это будет первый абзац вступительного текста, и ещё немного о том, что дальше ещё нужно настроить платёжку.',
        )));

        $section->addStep($step);

        // Receiver type step:
        $step = new Leyka_Settings_Step('campaign_description',  $section->id, 'Шаг 1: описание вашей кампании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'campaign_desc_text',
            'text' => 'Текст, который идёт перед полями кампании на этом шаге. В нём может описываться, например, что вообще такое кампания.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign_title',
            'option_id' => 'init-campaign-title',
            'option_data' => array(
                'type' => 'text',
                'title' => 'Название кампании',
//                'description' => '',
                'required' => 1,
                'placeholder' => 'Например, «На уставную деятельность организации»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign_lead',
            'option_id' => 'init-campaign-lead',
            'option_data' => array(
                'type' => 'textarea',
                'title' => 'Краткое описание кампании',
                'required' => 0,
                'placeholder' => 'Например, «Ваше пожертвование пойдёт на выполнение уставной деятельности в текущем году.»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign_target',
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
            'id' => 'campaign_desc_finished',
            'text' => 'Текст, которым визард завершается. Здесь слова о том, что настройки выполнены, и вэлкам вам в дэшборд.',
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Campaign data Section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Раздел последний: все почти готово');

        $step = new Leyka_Settings_Step('init',  $section->id, 'Шаг последний: напутственное сообщение');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'outro-text-1',
            'text' => 'Это будет первый абзац прощального текста. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro-text-2',
            'text' => 'А это второй абзац прощального текста последнего шага начального визарда. Путешествие в тысячу ли начинается с одного шага. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true) {

        $step_from = $step_from && is_a($step_from, 'Leyka_Settings_Step') ? $step_from : $this->current_step;
        $next_step_full_id = false;

        if($step_from->section_id === 'rd') {

            if($step_from->id === 'init') {
                $next_step_full_id = $step_from->section_id.'-account_type';
            } else if($step_from->id === 'account_type') {
                $next_step_full_id = 'cd-init';
            }

        } else if($step_from->section_id === 'cd') {

            if($step_from->id === 'init') {
                $next_step_full_id = $step_from->section_id.'-campaign_description';
            } else if($step_from->id === 'campaign_description') {
                $next_step_full_id = 'final-init';
            }

        } else if($step_from->section_id === 'final') { // Final Section
            $next_step_full_id = true;
        }

        if( !!$return_full_id || !is_string($next_step_full_id) ) {
            return $next_step_full_id;
        } else {

            $next_step_full_id = explode('-', $next_step_full_id);

            return array_pop($next_step_full_id);

        }

    }

    public function getSubmitSettings($structure_element = null) {

        $step = $structure_element && is_a($structure_element, 'Leyka_Settings_Step') ? $structure_element : $this->current_step;
        $submit_settings = array(
            'next_label' => 'Продолжить',
            'next_url' => true,
            'prev' => 'Вернуться на предыдущий шаг',
        );

        if($step->section_id === 'rd') {

            if($step->id === 'init') {

                $submit_settings['next_label'] = 'Поехали!';
                $submit_settings['prev'] = false; // Means that Wizard shouln't display the back link

            }

        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

}