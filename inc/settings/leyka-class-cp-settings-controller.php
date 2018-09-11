<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Cp_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'cp';
        $this->_title = 'Мастер подключения CloudPayments';

    }

    protected function _loadCssJs() {

//        wp_enqueue_script(
//            'leyka-settings',
//            LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
//            array('jquery',),
//            LEYKA_VERSION,
//            true
//        );
////        add_action('wp_enqueue_scripts', array($this, 'localize_scripts')); // wp_footer
//
//        wp_enqueue_style(
//            'leyka-settings',
//            LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css',
//            array(),
//            LEYKA_VERSION
//        );

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main CP settings section:
        $section = new Leyka_Settings_Section('cp', 'CloudPayments');
        
        // 0-step:
        $step = new Leyka_Settings_Step('init',  $section->id, 'CloudPayments');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Система CloudPayments является многопрофильным процессинговым центром для обработки платежей по банковским картам международных платежных систем Visa и MasterCard, а также по картам национальной платежной системы МИР.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'cp-payment-cards-icons',
            'field_type' => 'custom_cp-payment-cards-icons',
            'rendering_type' => 'template',
        )))->addTo($section);

        // prepare_documents step:
        $step = new Leyka_Settings_Step('prepare_documents',  $section->id, 'Подготовка документов', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Подключение к CloudPayments начинается с подготовки документов. Скачайте и заполните необходимые документы.',
        )))->addTo($section);

        // send_documents step:
        $step = new Leyka_Settings_Step('send_documents',  $section->id, 'Отправка документов', array('next_label' => 'Отправить письмо'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => wpautop('После подготовки документов, их необходимо отправить в CloudPayments. Форма ниже позволит вам отправить пакет документов не уходя с сайта.
                              
Вы также можете отправить эти документы из своей собственной почты на адрес: sales@cloudpayments.ru.

Обратите внимание, что проверка документов может занять до 3 рабочих дней.

Если вам нужно закрыть этот экран, мы запишем пройденные шаги и вы всегда сможете сюда вернуться.'),
        )))->addTo($section);

        // step:
        $step = new Leyka_Settings_Step('signin_cp_account',  $section->id, 'Войдите в личный кабинет CloudPayments', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Используйте для входа логин, пароль и ссылку на личный кабинет из письма от CloudPayments.',
        )))->addTo($section);
        
        // step
        $step = new Leyka_Settings_Step('copy_key',  $section->id, 'Копируем ключ', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте номер Public ID из личного кабинета CloudPayments, как на скриншоте ниже',
        )))->addTo($section);

        // step
        $step = new Leyka_Settings_Step('paste_key',  $section->id, 'Вставляем ключ в Лейку');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вставьте скопированный ключ в поле ниже',
        )))->addTo($section);

        // step:
        $step = new Leyka_Settings_Step('check_payment_request',  $section->id, 'Добавление запроса на проверку пожертвования', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Используйте для входа логин, пароль и ссылку на личный кабинет из письма от CloudPayments.',
        )))->addTo($section);

        // step
        $step = new Leyka_Settings_Step('accepted_payment_notification',  $section->id, 'Добавление уведомления о принятом  пожертвовании', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addTo($section);

        // step
        $step = new Leyka_Settings_Step('rejected_payment_notification',  $section->id, 'Добавление уведомления об отклоненном пожертвовании', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addTo($section);
        
        // step
        $step = new Leyka_Settings_Step('notification_email',  $section->id, 'E-mail адрес для уведомлений об успешных пожертвованиях', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте e-mail ниже:',
        )))->addTo($section);
        
        // Test payment tryout step:
        $step = new Leyka_Settings_Step('cp_payment_tryout', $section->id, 'Тестовое пожертвование');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Давайте проверим, проходят ли пожертвования. Мы можем это сделать с помощью тестовых банковских карт, номера и данные которые находятся ниже.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handlePaymentTryoutStep'))
            ->addTo($section);

        // step
        $step = new Leyka_Settings_Step('cp_payment_production',  $section->id, 'Переключение в боевой режим', array('next_label' => 'Отправить и продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно провели тестовое пожертвование. Для того, чтобы переключить ваш сайт в «боевой» режим, необходимо отправить письмо в службу поддержки CloudPayments. Ответы, как правило, приходит в течение суток.',
        )))->addTo($section);

        // step
        $step = new Leyka_Settings_Step('cp_payment_check',  $section->id, 'Проверка настоящего пожертвования', array('next_label' => 'Продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Пора отправить письмо, чтобы вас подключили к системе и вы могли бы собирать деньги. Ответ приходит обычно в течение суток.',
        )))->addTo($section);
            
        $this->_sections[$section->id] = $section;
        // The main CP settings section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение');

        $step = new Leyka_Settings_Step('init', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<p>Вы подключили CloudPayments. Стали доступны платежи с помощью платежных систем Visa и MasterCard.</p>
<p>Поделитесь вашей последней кампанией с друзьями и попросите их отправить пожертвование. Так вы сможете протестировать новый способ оплаты.</p>',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }
    
    /**
     * Steps branching incapsulation method. By default, it's next step in _steps array.
     * Probably should be moved into Leyka_Settings_Controller
     *
     * @param $step_from Leyka_Settings_Step
     * @param $return_full_id boolean
     * @return mixed Either next step ID, or false (if non-existent step given), or true (if last step given).
     */    
    protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true) {

        $step_from = $step_from && is_a($step_from, 'Leyka_Settings_Step') ? $step_from : $this->current_step;
        $next_step_full_id = false;
        $section_from = $this->_sections[$step_from->section_id];
        
        //printf("%s-%s<br />", $section_from->id, $step_from->id);
        
        $is_next_step_target = false;
        $next_step = null;
        
        foreach($section_from->steps as $step_id => $step) {
            if($is_next_step_target) {
                $next_step = $step;
                break;
            }
            
            if($step_from->section_id == $section_from->id && $step_id == $step_from->id) {
                $is_next_step_target = true;
            }
        }

        $next_section = null;
        
        if(!$next_step) {
            $is_next_section_target = false;
            foreach($this->_sections as $section_id => $section) {
                if($is_next_section_target) {
                    $next_section = $section;
                    $next_step = reset($section->steps);
                    break;
                }
                
                if($section->id == $section_from->id) {
                    $is_next_section_target = true;
                }
            }
        }
        
        if(!$next_step) {
            $next_step = $step_from;
        }
        
        if(!$next_section) {
            $next_section = $section_from;
        }
        
        $next_step_full_id = sprintf("%s-%s", $next_section->id, $next_step->id);
        //print_r($next_step_full_id);
        //exit();

        return $next_step_full_id;
    }
    
    
    protected function _initNavigationData() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'cp',
                'title' => 'CLOUDPAYMENTS',
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'prepare_documents',
                        'title' => 'Подготовка документов',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send_documents',
                        'title' => 'Отправка документов',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'signin_cp_account',
                        'title' => 'Вход в ЛК CloudPayments',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'copy_key',
                        'title' => 'Копируем ключ',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'paste_key',
                        'title' => 'Вставляем ключ',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'check_payment_request',
                        'title' => 'Запрос',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'accepted_payment_notification',
                        'title' => 'Уведомление 1',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'rejected_payment_notification',
                        'title' => 'Уведомление 2',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'notification_email',
                        'title' => 'E-mail адрес уведомлений',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_payment_tryout',
                        'title' => 'Тестовое пожертвование',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_payment_production',
                        'title' => 'Боевой режим',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_payment_check',
                        'title' => 'Проверка платежа',
                        'url' => '',
                    ),
                ),
            ),
            array(
                'section_id' => 'final',
                'title' => 'Завершение',
                'url' => '',
            ),
        );

    }

    
    public function getNavigationData() {

        $current_navigation_data = $this->_navigation_data;
        $current_step_full_id = $this->getCurrentStep()->full_id;
        
        //print_r($current_navigation_data);
        //print_r($current_step_full_id);

        switch($current_step_full_id) {
            case 'cp-init': $navigation_position = 'cp'; break;
            default: $navigation_position = $current_step_full_id;
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
        
        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'cp' && $step->id === 'init') {

            $submit_settings['next_label'] = 'Продолжить';
            $submit_settings['prev'] = false; // I. e. the Wizard shouln't display the back link

        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    
    public function handlePaymentTryoutStep(array $step_settings) {

        $errors = array();

        // ...

//        if( !$campaign_id || !$campaign ) {
//            return new WP_Error('wrong_init_campaign_id', 'ID кампании неправильный или отсутствует');
//        }

        return $errors ? $errors : true;

    }

}