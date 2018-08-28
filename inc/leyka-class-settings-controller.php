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

    /** @return boolean */
    public function hasCommonErrors() {
        return !empty($this->getCommonErrors());
    }

    /** @return array Of errors */
    public function getCommonErrors() {
        return $this->_common_errors;
    }

    /** @return boolean */
    public function hasComponentErrors($component_id = null) {
        return !empty($this->getComponentErrors($component_id));
    }

    /**
     * @param string $component_id
     * @return array An array of WP_Error objects (each with one error message)
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

    abstract protected function _processSettingsValues(array $blocks = null);

    protected function _addCommonError(WP_Error $error) {
        $this->_common_errors[] = $error;
    }

    protected function _addComponentError($component_id, WP_Error $error) {
        if(empty($this->_component_errors[$component_id])) {
            $this->_component_errors[$component_id] = array($error);
        } else {
            $this->_component_errors[$component_id][] = $error;
        }
    }

    /** @return Leyka_Settings_Step */
    abstract public function getCurrentStep();

    /** @return Leyka_Settings_Section */
    abstract public function getCurrentSection();

    abstract public function getSubmitData($component = null);

    abstract public function getNavigationData();

    abstract public function handleSubmit();

}

abstract class Leyka_Wizard_Settings_Controller extends Leyka_Settings_Controller {

    protected static $_instance = null;

    protected $_storage_key = '';

    protected $_navigation_data = array();

    protected function __construct() {

        parent::__construct();

        $this->_storage_key = 'leyka-wizard_'.$this->_id;

        add_action('leyka_settings_wizard-'.$this->_id.'-_step_init', array($this, 'stepInit'));

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

        if( !$this->_navigation_data ) {
            $this->_initNavigationData();
        }

        // Debug {
        if(isset($_GET['reset'])) {

            $_SESSION[$this->_storage_key]['current_section'] = reset($this->_sections);
            $_SESSION[$this->_storage_key]['current_step'] = reset($this->_sections)->init_step;
            $_SESSION[$this->_storage_key]['activity'] = array();

        }
        // } Debug

        do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

        if( !empty($_POST['leyka_settings_prev_'.$this->_id]) ) { // Step page loading after returning from further Step
            $this->_handleSettingsGoBack();
        } else if( !empty($_POST['leyka_settings_submit_'.$this->_id]) ) { // Step page loading after previous Step submit
            $this->_handleSettingsSubmit();
        } //else { // Normal Step page loading
        //}

        if(isset($_GET['debug'])) {
            echo '<pre>The activity: '.print_r($_SESSION[$this->_storage_key]['activity'], 1).'</pre>';
        }

    }

    protected function _setCurrentStep(Leyka_Settings_Step $step) {

        $_SESSION[$this->_storage_key]['current_step'] = $step;

        return $this->_setCurrentSection($this->_sections[$step->section_id]);

    }

    protected function _setCurrentSection(Leyka_Settings_Section $section) {

        $_SESSION[$this->_storage_key]['current_section'] = $section;

        return $this;

    }

    protected function _setCurrentStepById($step_full_id) {

        if( !$step_full_id ) {
            return $this;
        }

        $step = $this->getComponentById($step_full_id);
        if( !$step ) {
            return $this;
        }

        return $this->_setCurrentStep($step)
            ->_setCurrentSection($this->getComponentById($step->section_id));

    }

    /**
     * @param $step_full_id string
     * @return boolean
     */
    protected function _isStepCompleted($step_full_id) {

        /** @todo Throw some Exception if the given Step doesn't exists. */
        $step_full_id = trim($step_full_id);
        return !empty($_SESSION[$this->_storage_key]['activity'][$step_full_id]);

    }

    protected function _getSettingValue($setting_name = null) {

        if($setting_name) {

            foreach($_SESSION[$this->_storage_key]['activity'] as $step_full_id => $step_settings) {
                if(isset($step_settings[$setting_name])) {
                    return $step_settings[$setting_name];
                }
            }

            return null;

        } else {

            $res = array();
            foreach($_SESSION[$this->_storage_key]['activity'] as $step_full_id => $step_settings) {
                $res = array_merge($res, $step_settings);
            }

            return $res;

        }

    }

    protected function _addActivityEntry(array $data = array(), $step_full_id = false) {

        $data = empty($data) ? $this->getCurrentStep()->getFieldsValues() : $data;
        $step_full_id = !$step_full_id ? $this->getCurrentStep()->full_id : trim($step_full_id);

        if(empty($_SESSION[$this->_storage_key]['activity'][$step_full_id])) {
            $_SESSION[$this->_storage_key]['activity'][$step_full_id] = $data;
        } else {
            $_SESSION[$this->_storage_key]['activity'][$step_full_id] =
                $_SESSION[$this->_storage_key]['activity'][$step_full_id] + $data;
        }

        return $this;

    }

    protected function _processSettingsValues(array $blocks = null) {

        $blocks = $blocks ? $blocks : $this->getCurrentStep()->getBlocks();

        foreach($blocks as $block) { /** @var $block Leyka_Settings_Block */
            if(is_a($block, 'Leyka_Option_Block') && $block->isValid()) {
                leyka_save_option($block->option_id);
            } else if(is_a($block, 'Leyka_Custom_Setting_Block')) {
                do_action("leyka_save_custom_option-{$block->setting_id}");
            } else if(is_a($block, 'Leyka_Container_Block')) {
                $this->_processSettingsValues($block->getContent());
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

    /** The default implementation: the Wizard navigation roadmap created from existing Sections & Steps */
    protected function _initNavigationData() {

        if( !$this->_sections ) {
            return;
        }

        $this->_navigation_data = array();

        foreach($this->_sections as $section) { /** @var Leyka_Settings_Section $section */

            $steps = $section->getSteps();
            if( !$steps ) {
                continue;
            }

            $steps_data = array();
            $all_steps_completed = true;
            foreach($steps as $step) { /** @var Leyka_Settings_Step $step */

                $step_completed = $this->_isStepCompleted($step->full_id);

                if($all_steps_completed && !$step_completed) {
                    $all_steps_completed = false;
                }

                $steps_data[] = array(
                    'step_id' => $step->full_id,
                    'title' => $step->title,
                    'url' => $step_completed ? '&step='.$step->full_id : false,
                    'is_current' => $this->current_step->full_id === $step->full_id,
                    'is_completed' => $step_completed,
                );

            }

            $this->_navigation_data[] = array(
                'section_id' => $section->id,
                'title' => $section->title,
                'url' => $all_steps_completed ? '&step='.$steps_data[0]['step_id'] : false,
                'is_current' => $this->current_section_id === $section->id, // True if the current Step belongs to the Section
                'is_completed' => $all_steps_completed,
                'steps' => $steps_data,
            );

        }

    }

    /**
     * @param $navigation_data array
     * @param $navigation_position string
     * @return array
     */
    protected function _processNavigationData($navigation_position = null, array $navigation_data = null) {

        $navigation_data = empty($navigation_data) ? $this->_navigation_data : $navigation_data;
        $navigation_position = empty($navigation_position) ?
            $this->current_step_full_id : trim($navigation_position);

        foreach($navigation_data as $section_index => &$section) {

            $navigation_position_parts = explode('-', $navigation_position);

            if($section['section_id'] === $navigation_position_parts[0]) {

                if(count($navigation_position_parts) === 1) {

                    $navigation_data[$section_index]['is_current'] = true;
                    break;

                }

            }

            foreach(empty($section['steps']) ? array() : $section['steps'] as $step_index => $step) {

                if($navigation_position === $section['section_id'].'-'.$step['step_id']) {

                    $navigation_data[$section_index]['steps'][$step_index]['is_current'] = true;
                    $navigation_data[$section_index]['is_current'] = true;

                    break 2;

                } else {
                    $navigation_data[$section_index]['steps'][$step_index]['is_completed'] = true;
                }

            }

            $navigation_data[$section_index]['is_completed'] = true;

            if($navigation_position === $section['section_id'].'--') {
                break;
            }

        }

        return $navigation_data;

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

    /**
     * Navigation data incapsulation method - Wizards default implementation.
     * @return array
     */
    public function getNavigationData() {
        return $this->_navigation_data;
    }

    public function handleSubmit() {

        $this->_processSettingsValues(); // Save all valid options on current step

        if( !$this->getCurrentStep()->isValid() ) {

            foreach($this->getCurrentStep()->getErrors() as $component_id => $errors) {
                foreach($errors as $error) {
                    $this->_addComponentError($component_id, $error);
                }
            }

            return;

        }

        // Whole step settings handling:
        $settings_entered = $this->getCurrentStep()->getFieldsValues();

        if($this->getCurrentStep()->hasHandler()) {
            call_user_func($this->getCurrentStep()->getHandler(), $settings_entered);
        }

        do_action("leyka_process_step_settings-{$this->getCurrentStep()->full_id}", $settings_entered);


        $this->_addActivityEntry(); // Save the step data in the storage

        // Proceed to the next step:
        $next_step_full_id = $this->_getNextStepId();
        if($next_step_full_id && $next_step_full_id !== true) {

            $step = $this->getComponentById($this->_getNextStepId());
            if( !$step ) { /** @todo Process the error somehow */
                return;
            }

            do_action('leyka_settings_wizard-'.$this->_id.'-_step_init');

            $this->_setCurrentStep($step);

        }

    }

    public function stepInit() {
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
        $step = new Leyka_Settings_Step('init',  $section->id, 'Приветствуем вас!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы установили плагин «Лейка», осталось его настроить. Мы проведём вас по всем шагам, поможем подсказками.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_country',
            'option_id' => 'receiver_country',
        )))->addTo($section);

        // Receiver type step:
        $step = new Leyka_Settings_Step('receiver_type', $section->id, 'Получатель пожертвований');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы должны определить, от имени кого вы будете собирать пожертвования. Как НКО (некоммерческая организация) — юридическое лицо или как обычный гражданин — физическое лицо.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_type',
            'option_id' => 'receiver_legal_type',
            'show_title' => false,
        )))->addTo($section);

        // Legal receiver type - org. data step:
        $step = new Leyka_Settings_Step('receiver_legal_data', $section->id, 'Данные организации');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Эти данные мы будем использовать для шаблонов договоров и отчётных документов вашим донорам. Все данные вы сможете найти в учредительных документах вашей организации.',
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
        )))->addBlock(new Leyka_Subtitle_Block(array(
            'id' => 'contact_person_data',
            'text' => 'Контактное лицо',
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-3',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_contact_person_name',
                    'option_id' => 'org_contact_person_name',
                )),
                new Leyka_Option_Block(array(
                    'id' => 'org_contact_email',
                    'option_id' => 'tech_support_email',
                    'show_description' => false,
                )),
            ),
        )))->addTo($section);

        // Physical receiver type - person's data step:
        $step = new Leyka_Settings_Step('receiver_physical_data', $section->id, 'Ваши данные');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Эти данные мы будем использовать для отчётных документов вашим донорам.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_full_name',
            'option_id' => 'person_full_name',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_email',
            'option_id' => 'tech_support_email',
            'title' => 'Email для связи', // __('Your email', 'leyka')
            'required' => true,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_address',
            'option_id' => 'person_address',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_inn',
            'option_id' => 'person_inn',
        )))->addTo($section);

        // Legal receiver type - org. bank essentials step:
        $step = new Leyka_Settings_Step('receiver_legal_bank_essentials', $section->id, 'Банковские реквизиты');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Данные понадобятся для отчётных документов, а также для подключения оплаты с помощью бумажной банковской квитанции.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_bank_name',
            'option_id' => 'org_bank_name',
            'show_description' => false,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_bank_account',
            'option_id' => 'org_bank_account',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_bank_bic',
                    'option_id' => 'org_bank_bic',
                    'show_description' => false,
                )),
                new Leyka_Option_Block(array(
                    'id' => 'org_bank_corr_account',
                    'option_id' => 'org_bank_corr_account',
                    'show_description' => false,
                )),
            ),
        )))->addTo($section);

        // Physical receiver type - person's bank essentials step:
        $step = new Leyka_Settings_Step('receiver_physical_bank_essentials', $section->id, 'Банковские реквизиты');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Данные понадобятся для отчётных документов, а также для подключения оплаты с помощью бумажной банковской квитанции.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_bank_name',
            'option_id' => 'person_bank_name',
            'show_description' => false,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_bank_account',
            'option_id' => 'person_bank_account',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'person_bank_bic',
                    'option_id' => 'person_bank_bic',
                    'show_description' => false,
                )),
                new Leyka_Option_Block(array(
                    'id' => 'person_bank_corr_account',
                    'option_id' => 'person_bank_corr_account',
                    'show_description' => false,
                )),
            ),
        )))->addTo($section);

        // Legal receiver type - Terms of service step:
        $step = new Leyka_Settings_Step('receiver_legal_terms_of_service', $section->id, 'Оферта');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Для соблюдения всех формальных процедур вам необходимо предоставить оферту о заключении договора пожертвования. Мы подготовили для вас шаблонный вариант. Пожалуйста, проверьте.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'terms_of_service_text',
        )))->addTo($section);

        // Physical receiver type - Terms of service step:
        $step = new Leyka_Settings_Step('receiver_physical_terms_of_service', $section->id, 'Оферта');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Для соблюдения всех формальных процедур вам необходимо предоставить оферту о заключении договора пожертвования. Мы подготовили для вас шаблонный вариант. Пожалуйста, проверьте.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'person_terms_of_service_text',
        )))->addTo($section);

        // Legal receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_legal_pd_terms', $section->id, 'Соглашение о персональных данных');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<ul>
<li>В рамках сбора пожертвований вы будете собирать персональные данные доноров.</li>
<li>«Согласие на обработку персональных данных» — обязательный документ по закону ФЗ-152.</li>
<li>Мы подготовили шаблон текста соглашения, вы можете отредактировать его под ваши требования.</li>
<li>Все персональные данные хранятся на вашем сайте и никуда не отправляются.</li>
</ul>',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'pd_terms_text',
        )))->addTo($section);

        // Physical receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_physical_pd_terms', $section->id, 'Соглашение о персональных данных');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<ul>
<li>В рамках сбора пожертвований вы будете собирать персональные данные доноров.</li>
<li>«Согласие на обработку персональных данных» — обязательный документ по закону ФЗ-152.</li>
<li>Мы подготовили шаблон текста соглашения, вы можете отредактировать его под ваши требования.</li>
<li>Все персональные данные хранятся на вашем сайте и никуда не отправляются.</li>
</ul>',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'person_pd_terms_text',
        )))->addTo($section);

        // Section final (outro) step:
        $step = new Leyka_Settings_Step('final', $section->id, 'Хорошая работа!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно заполнили свои данные. Продолжим?',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Receiver data Section - End

        // Diagnostic data Section:
        $section = new Leyka_Settings_Section('dd', 'Диагностические данные');

        // The plugin usage stats collection step:
        $step = new Leyka_Settings_Step('plugin_stats', $section->id, 'Диагностические данные');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Мы просим вас подтвердить согласие на отправку <strong>анонимных данных</strong> о пожертвованиях и технических данных к нам, в Теплицу. Это позволит нам улучшить работу плагина. Эти данные будут использоваться только нами.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'send_plugin_stats',
            'option_id' => 'send_plugin_stats',
            'show_title' => false,
        )))->addTo($section);

        // The plugin usage stats collection - accepted:
        $step = new Leyka_Settings_Step('plugin_stats_accepted', $section->id, 'Спасибо!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Спасибо! Ваши данные очень нам помогут! Теперь, давайте настроим и запустим вашу первую кампанию по сбору средств.',
        )))->addTo($section);

        // The plugin usage stats collection - refused:
        $step = new Leyka_Settings_Step('plugin_stats_refused', $section->id, 'Эхх...');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Жаль, что вы решили не делиться данными. Если вы передумаете, то изменить эти настройки вы сможете в разделе «Настройки». Давайте настроим и запустим вашу первую кампанию по сбору средств.',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;

        // Campaign data Section:
        $section = new Leyka_Settings_Section('cd', 'Настройка кампании');

        $init_campaign = get_transient('leyka_init_campaign_id') ?
            new Leyka_Campaign(get_transient('leyka_init_campaign_id')) : false;

        $step = new Leyka_Settings_Step('campaign_description', $section->id, 'Описание вашей кампании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<ul>
<li>Кампания — это ключевое понятие Лейки.</li>
<li>У кампаний по сбору пожертвований есть цель и, как правило, целевая сумма.</li>
<li>Лейка позволяет создавать ежемесячные платежи, и чуть позже мы покажем, как их настраивать. Но, для начала, давайте создадим простую кампанию.</li>
</ul>',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign_title',
            'custom_setting_id' => 'campaign_title',
            'field_type' => 'text',
            'data' => array(
                'title' => 'Название кампании',
                'required' => true,
                'placeholder' => 'Например, «На уставную деятельность организации»',
                'value' => $init_campaign ? $init_campaign->title : '',
//                'description' => '',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-lead',
            'custom_setting_id' => 'campaign_short_description',
            'field_type' => 'textarea',
            'data' => array(
                'title' => 'Краткое описание',
                'value' => $init_campaign ? $init_campaign->short_description : '',
//                'description' => '',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-target',
            'custom_setting_id' => 'campaign_target',
            'field_type' => 'number',
            'data' => array(
                'title' => 'Целевая сумма (Укажите только цифру. Например, «10000»). ',
                'min' => 0,
                'step' => 0.01,
                'value' => $init_campaign ? $init_campaign->target : '',
//                'validation_rules' => array()
            ),
        )))->addHandler(array($this, 'handleCampaignDescriptionStep'))
            ->addTo($section);

        $step = new Leyka_Settings_Step('campaign_decoration', $section->id, 'Оформление кампании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Выберите главное фото кампании и один из возможных шаблонов формы для пожертвования. И то, и другое очень важно для восприятия кампании донорами, и, следовательно, для её успешности.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-decoration',
            'custom_setting_id' => 'campaign_decoration',
            'field_type' => 'custom_campaign_view',
            'keys' => array('campaign_thumbnail', 'campaign_template',),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleCampaignDecorationStep'))
            ->addTo($section);

        $step = new Leyka_Settings_Step('donors_communication', $section->id, 'Коммуникация с донором');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => 'После того, как донор внёс своё пожертвование, хорошим тоном считается показать ему страницу с благодарностью и отправить письмо.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => 'Позже, в разделе «Настройки», вы сможете изменить текст страницы «Спасибо», которая показывается донору после успешного совершения пожертвования.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'email-from-name',
            'option_id' => 'email_from_name',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'email-from',
            'option_id' => 'email_from',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'email-thanks-text',
            'option_id' => 'email_thanks_text',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('campaign_completed', $section->id, 'Кампания настроена');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-completed',
            'custom_setting_id' => 'campaign_completed',
            'field_type' => 'custom_campaign_completed',
            'rendering_type' => 'template',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Campaign settings Section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение настройки');

        $step = new Leyka_Settings_Step('init', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно завершили тестовый мастер установки «Лейки».',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true) {

        $step_from = $step_from && is_a($step_from, 'Leyka_Settings_Step') ? $step_from : $this->current_step;
        $next_step_full_id = false;

        /** @todo To many if-elses sucks - try some wrapping pattern here */
        if($step_from->section_id === 'rd') {

            if($step_from->id === 'init') {
                $next_step_full_id = $step_from->section_id.'-receiver_type';
            } else if($step_from->id === 'receiver_type') {

                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
                    $step_from->section_id.'-receiver_legal_data' :
                    $step_from->section_id.'-receiver_physical_data';

            } else if($step_from->id === 'receiver_legal_data') {
                $next_step_full_id = $step_from->section_id.'-receiver_legal_bank_essentials';
            } else if($step_from->id === 'receiver_physical_data') {
                $next_step_full_id = $step_from->section_id.'-receiver_physical_bank_essentials';
            } else if(stripos($step_from->id, 'bank_essentials')) {

                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
                    $step_from->section_id.'-receiver_legal_terms_of_service' :
                    $step_from->section_id.'-receiver_physical_terms_of_service';

            } else if(stripos($step_from->id, 'terms_of_service')) {

                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
                    $step_from->section_id.'-receiver_legal_pd_terms' :
                    $step_from->section_id.'-receiver_physical_pd_terms';

            } else if(stripos($step_from->id, 'pd_terms')) {
                $next_step_full_id = $step_from->section_id.'-final';
            } else if($step_from->id === 'final') {
                $next_step_full_id = 'dd-plugin_stats';
            }

        } else if($step_from->section_id === 'dd') {

            if($step_from->id === 'plugin_stats') {

                $next_step_full_id = $this->_getSettingValue('send_plugin_stats') === 'n' ?
                    $step_from->section_id.'-plugin_stats_refused' :
                    $step_from->section_id.'-plugin_stats_accepted';

            } else {
                $next_step_full_id = 'cd-campaign_description';
            }

        } else if($step_from->section_id === 'cd') {

            if($step_from->id === 'campaign_description') {
                $next_step_full_id = $step_from->section_id.'-campaign_decoration';
            } else if($step_from->id === 'campaign_decoration') {
                $next_step_full_id = $step_from->section_id.'-donors_communication';
            } else if($step_from->id === 'donors_communication') {
                $next_step_full_id = $step_from->section_id.'-campaign_completed';
            } else if($step_from->id === 'campaign_completed') {
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

    protected function _initNavigationData() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'rd',
                'title' => 'Ваши данные',
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'receiver_type',
                        'title' => 'Получатель пожертвований',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_data',
                        'title' => 'Ваши данные',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_bank_essentials',
                        'title' => 'Банковские реквизиты',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_terms_of_service',
                        'title' => 'Оферта',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_pd_terms',
                        'title' => 'Персональные данные',
                        'url' => '',
                    ),
                ),
            ),
            array(
                'section_id' => 'dd',
                'title' => 'Диагностические данные',
                'url' => '',
            ),
            array(
                'section_id' => 'cd',
                'title' => 'Настройка кампании',
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'campaign_description',
                        'title' => 'Основные данные',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'campaign_decoration',
                        'title' => 'Оформление кампании',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'donors_communication',
                        'title' => 'Коммуникация с донором',
                        'url' => '',
                    ),
                ),
            ),
            array(
                'section_id' => 'final',
                'title' => 'Завершение настройки',
                'url' => '',
            ),
        );

    }

    public function getNavigationData() {

        $current_navigation_data = $this->_navigation_data;
        $current_step_full_id = $this->getCurrentStep()->full_id;

        switch($current_step_full_id) {
            case 'rd-init': $navigation_position = 'rd'; break;
            case 'rd-receiver_type': $navigation_position = $current_step_full_id; break;
            case 'rd-receiver_legal_data':
            case 'rd-receiver_physical_data':
                $navigation_position = 'rd-receiver_data';
                break;
            case 'rd-receiver_legal_bank_essentials':
            case 'rd-receiver_physical_bank_essentials':
                $navigation_position = 'rd-receiver_bank_essentials';
                break;
            case 'rd-receiver_legal_terms_of_service':
            case 'rd-receiver_physical_terms_of_service':
                $navigation_position = 'rd-receiver_terms_of_service';
                break;
            case 'rd-receiver_legal_pd_terms':
            case 'rd-receiver_physical_pd_terms':
                $navigation_position = 'rd-receiver_terms_of_service';
                break;
            case 'rd-final': $navigation_position = 'rd--'; break;
            case 'dd-plugin_stats': $navigation_position = 'dd'; break;
            case 'dd-plugin_stats_accepted':
            case 'dd-plugin_stats_refused':
                $navigation_position = 'dd--';
                break;
            case 'cd-campaign_description':
            case 'cd-campaign_decoration':
            case 'cd-donors_communication':
                $navigation_position = $current_step_full_id; break;
            case 'cd-campaign_completed':
                $navigation_position = 'cd--'; break;
            case 'final-init': $navigation_position = 'final--'; break;
            default: $navigation_position = false;
        }

        return $navigation_position ?
            $this->_processNavigationData($navigation_position) :
            $current_navigation_data;

    }

    public function getSubmitData($component = null) {

        $step = $component && is_a($component, 'Leyka_Settings_Step') ? $component : $this->current_step;
        $submit_settings = array(
            'next_label' => 'Сохранить и продолжить',
            'next_url' => true,
            'prev' => 'Вернуться на предыдущий шаг',
        );

        if($step->section_id === 'rd' && $step->id === 'init') {

            $submit_settings['next_label'] = 'Поехали!';
            $submit_settings['prev'] = false; // Means that Wizard shouln't display the back link

        } else if($step->section_id === 'dd' && in_array($step->id, array('plugin_stats_accepted', 'plugin_stats_refused',))) {

            $submit_settings['additional_label'] = 'Перейти в Панель управления';
            $submit_settings['additional_url'] = admin_url('admin.php?page=leyka');

        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function stepInit() {

        // Steps prerequisites:
        // Show "legal" receiver type only if receiver country is set:
        if($this->_getSettingValue('receiver_country') === '-') {
            add_filter('leyka_option_info-receiver_legal_type', function($option_data){

                unset($option_data['list_entries']['legal']);

                return $option_data;

            });
        }

        // If init campaign is not set or deleted on the campaign decoration step, return to the campaign data step:
        if($this->getCurrentStep()->id === 'campaign_decoration') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handleSettingsGoBack();
            }

        }

    }

    public function handleCampaignDescriptionStep(array $step_settings) {

        $init_campaign_params = array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_title' => trim(esc_attr(wp_strip_all_tags($step_settings['campaign_title']))),
            'post_excerpt' => trim(esc_textarea($step_settings['campaign_short_description'])),
//            'post_status' => 'publish',
            'post_content' => '',
        );

        $existing_campaign_id = get_transient('leyka_init_campaign_id');
        if($existing_campaign_id) {
            $init_campaign_params['ID'] = $existing_campaign_id;
        }

        $campaign_id = wp_insert_post($init_campaign_params, true);

        if(is_wp_error($campaign_id)) {
            // ...
            return;
        }

        update_post_meta($campaign_id, 'campaign_target', (float)$step_settings['campaign_target']);

        if( !$existing_campaign_id ) {

            $this->_addActivityEntry(array('campaign_id' => $campaign_id));
            set_transient('leyka_init_campaign_id', $campaign_id);

        }

    }

    public function handleCampaignDecorationStep(array $step_settings) {

        $init_campaign_id = get_transient('leyka_init_campaign_id');
        $init_campaign = get_post($init_campaign_id);

        // Step custom field processing here...

    }

}
