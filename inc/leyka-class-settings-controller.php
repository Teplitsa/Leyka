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

        add_action('leyka_settings_submit_'.$this->_id, array($this, 'handleSubmit'));

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
     * @return WP_Error An object with the list of errors
     */
    public function getComponentErrors($component_id = null) {
        return empty($component_id) ?
            $this->_component_errors :
            (empty($this->_component_errors[$component_id]) ? array() : $this->_component_errors[$component_id]);
    }

    protected function _handleSettingsSubmit() {
        if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) {
            do_action('leyka_settings_submit_'.$this->_id);
        }
    }

    abstract protected function _processSettingsValues();

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

    abstract public function getSubmitData($structure_element = null);

    abstract public function getNavigationData();

    abstract public function handleSubmit();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;
    protected $_storage_key = '';
    protected $_roadmap = array();
    protected $_roadmap_position = null;

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
        if(isset($_GET['reset'])) {

            $_SESSION[$this->_storage_key]['current_section'] = reset($this->_sections);
            $_SESSION[$this->_storage_key]['current_step'] = reset($this->_sections)->init_step;
            $_SESSION[$this->_storage_key]['activity'] = array();

        }
        // } Debug

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) {
            $this->_handleSettingsGoBack();
        } else {
            $this->_handleSettingsSubmit();
        }

        echo '<pre>Current activity: '.print_r($_SESSION[$this->_storage_key]['activity'], 1).'</pre>';

    }

    protected function _setCurrentStep(Leyka_Settings_Step $step) {

        $_SESSION[$this->_storage_key]['current_step'] = $step;

        $this->_setCurrentSection($this->_sections[$step->section_id]);

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

    protected function _processSettingsValues() {

        foreach($this->getCurrentStep()->getBlocks() as $block) {
            if(is_a($block, 'Leyka_Option_Block')) {
                leyka_options()->opt($block->option_id, $_POST['leyka_'.$block->option_id]);
            }
        }

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

    /** Navigation data incapsulation method - Wizards default implementation. */
    public function getNavigationData() {

        if( !$this->_sections ) {
            return array();
        }

        $nav_data = array();

        foreach($this->_sections as $section) { /** @var Leyka_Settings_Section $section */

            $steps = $section->getSteps();
            if( !$steps ) {
                continue;
            }

            $steps_data = array();
            $all_steps_completed = true;
            foreach($steps as $step) { /** @var Leyka_Settings_Step $step */

                $step_completed = isset($_SESSION[$this->_storage_key]['activity'][$step->full_id]);

                if($all_steps_completed && !$step_completed) {
                    $all_steps_completed = false;
                }

                $steps_data[] = array(
                    'roadmap_step_id' => $step->full_id,
                    'title' => $step->title,
                    'url' => $step_completed ? '&step='.$step->full_id : false,
                    'is_current' => $this->current_step->full_id === $step->full_id,
                    'is_completed' => $step_completed,
                );

            }

            $nav_data[] = array(
                'roadmap_section_id' => $section->id,
                'title' => $section->title,
                'url' => $all_steps_completed ? '&step='.$steps_data[0]['roadmap_step_id'] : false,
                'is_current' => $this->current_section_id === $section->id, // True if the current Step belongs to the Section
                'is_completed' => $all_steps_completed,
                'steps' => $steps_data,
            );

        }

        return $nav_data;

    }

    public function handleSubmit() {

//        echo '<pre>Current step: '.print_r($this->getCurrentStep()->full_id.' (is valid: '.$this->getCurrentStep()->isValid().')', 1).'</pre>';

        if( !$this->getCurrentStep()->isValid() ) {

            foreach($this->getCurrentStep()->getErrors() as $component_id => $error) {
                $this->_addComponentError($component_id, $error);
            }

            return;

        }

        $this->_addActivityEntry(); // Save the current step in the storage
        $this->_processSettingsValues();

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
     * Steps branching incapsulation method. By default, it's next step in _steps array.
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
        $section = new Leyka_Settings_Section('rd', 'Ваши данные');

        // 0-step:
        $step = new Leyka_Settings_Step('init',  $section->id, 'Приветствуем вас!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы установили плагин «Лейка», осталось его настроить. Мы проведём вас по всем шагам, поможем подсказками, а если нужна будет наша помощь, вы можете обратиться к нам через форму в правой части экрана.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_country',
            'option_id' => 'receiver_country',
        )));

        $section->addStep($step);

        // Receiver type step:
        $step = new Leyka_Settings_Step('receiver_type', $section->id, 'Получатель пожертвований');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы должны определить, от имени кого вы будете собирать пожертвования. Как НКО (некоммерческая организация) — юридическое лицо или как обычный гражданин — физическое лицо.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_type',
            'option_id' => 'receiver_legal_type',
            'show_title' => false,
        )));

        $section->addStep($step);

        // Legal receiver type - org. data step:
        $step = new Leyka_Settings_Step('receiver_legal_data', $section->id, 'Название организации');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Эти данные мы будем использовать для отчётных документов вашим донорам. Все данные вы сможете найти в учредительных документах.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_full_name',
            'option_id' => 'org_full_name',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_short_name',
            'option_id' => 'org_short_name',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_face_fio_ip',
            'option_id' => 'org_face_fio_ip',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_address',
            'option_id' => 'org_address',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_state_reg_number',
                    'option_id' => 'org_state_reg_number',
                    'show_description' => false,
                )),
                new Leyka_Option_Block(array(
                    'id' => 'org_kpp',
                    'option_id' => 'org_kpp',
                    'show_description' => false,
                )),
            ),
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-2',
            'entry_width' => 0.5,
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_inn',
                    'option_id' => 'org_inn',
                    'show_description' => false,
                )),
            ),
        )));

        $section->addStep($step);

        $this->_sections[$section->id] = $section;
        // Receiver's data Section - End

        // Campaign data Section:
//        $section = new Leyka_Settings_Section('cd', 'Раздел 2: настройка кампании');
//
//        $step = new Leyka_Settings_Step('init', $section->id, 'Шаг 0: настроим кампанию!');
//        $step->addBlock(new Leyka_Text_Block(array(
//            'id' => 'intro_text_1',
//            'text' => 'Осталось совсем немного и первая кампания будет запущена. Это будет первый абзац вступительного текста, и ещё немного о том, что дальше ещё нужно настроить платёжку.',
//        )));
//
//        $section->addStep($step);
//
//        $step = new Leyka_Settings_Step('campaign_description',  $section->id, 'Шаг 1: описание вашей кампании');
//        $step->addBlock(new Leyka_Text_Block(array(
//            'id' => 'campaign_desc_text',
//            'text' => 'Текст, который идёт перед полями кампании на этом шаге. В нём может описываться, например, что вообще такое кампания.',
//        )))->addBlock(new Leyka_Custom_Option_Block(array(
//            'id' => 'campaign_title',
//            'option_id' => 'init-campaign-title',
//            'option_data' => array(
//                'type' => 'text',
//                'title' => 'Название кампании',
////                'description' => '',
//                'required' => 1,
//                'placeholder' => 'Например, «На уставную деятельность организации»',
//            ),
//        )))->addBlock(new Leyka_Custom_Option_Block(array(
//            'id' => 'campaign_lead',
//            'option_id' => 'init-campaign-lead',
//            'option_data' => array(
//                'type' => 'textarea',
//                'title' => 'Краткое описание кампании',
//                'required' => 0,
//                'placeholder' => 'Например, «Ваше пожертвование пойдёт на выполнение уставной деятельности в текущем году.»',
//            ),
//        )))->addBlock(new Leyka_Custom_Option_Block(array(
//            'id' => 'campaign_target',
//            'option_id' => 'init-campaign-target',
//            'option_data' => array(
//                'type' => 'number',
//                'title' => 'Целевая сумма',
//                'required' => 0,
//                'min' => 0,
//                'max' => 60000,
//                'step' => 1,
//            ),
//        )));
//
//        $section->addStep($step);
//
//        $this->_sections[$section->id] = $section;
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
                $next_step_full_id = $step_from->section_id.'-receiver_type';
            } else if($step_from->id === 'receiver_type') {
                $next_step_full_id =
                    $_SESSION[$this->_storage_key]['activity'][$step_from->section_id.'-receiver_type']['receiver_legal_type'] === 'legal' ? $step_from->section_id.'-receiver_legal_data' : $step_from->section_id.'-receiver_physical_data';
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

    public function getSubmitData($structure_element = null) {

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

    public function getNavigationData() {

//        if() {
//
//        }

    }

}