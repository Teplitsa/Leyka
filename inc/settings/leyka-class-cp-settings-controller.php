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

        wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments', array(), false, true);

        wp_localize_script('leyka-admin', 'leyka_wizard_cp', array(
            'cp_public_id' => leyka_options()->opt('cp_public_id'),
            'main_currency' => 'RUB',
            'test_donor_email' => get_option('admin_email'),
            'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
            'cp_not_set_up' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka'),
            'cp_donation_failure_reasons' => array(
                'User has cancelled' => __('You cancelled the payment', 'leyka'),
            ),
        ));

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main CP settings section:
        $section = new Leyka_Settings_Section('cp', 'CloudPayments');

        $step = new Leyka_Settings_Step('init',  $section->id, 'CloudPayments');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Система CloudPayments является многопрофильным процессинговым центром для обработки платежей по банковским картам международных платежных систем Visa и MasterCard, а также по картам национальной платежной системы МИР.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'cp-payment-cards-icons',
            'custom_setting_id' => 'payment_cards_icons',
            'field_type' => 'custom_payment_cards_icons',
            'rendering_type' => 'template',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('prepare_documents',  $section->id, 'Подготовка документов');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Подключение к CloudPayments начинается с подготовки документов. Скачайте и заполните необходимые документы.',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('send_documents',  $section->id, 'Отправка документов', array('next_label' => 'Отправить письмо'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<p>После подготовки документов, их необходимо отправить в CloudPayments. Форма ниже позволит вам отправить пакет документов не уходя с сайта.</p>
<p>Вы также можете отправить эти документы из своей собственной почты на адрес: <a href="mailto:sales@cloudpayments.ru">sales@cloudpayments.ru</a>.</p>
<p>Обратите внимание, что проверка документов может занять до 3 рабочих дней.</p>
<p>Если вам нужно закрыть этот экран, мы запишем пройденные шаги и вы всегда сможете сюда вернуться.</p>',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('signin_cp_account',  $section->id, 'Войдите в личный кабинет CloudPayments');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Используйте для входа логин, пароль и ссылку на личный кабинет из письма от CloudPayments.',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('copy_key',  $section->id, 'Копируем ключ');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте номер Public ID из личного кабинета CloudPayments, как на скриншоте ниже',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('paste_key',  $section->id, 'Вставляем ключ в Лейку');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вставьте скопированный ключ в поле ниже',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('check_payment_request',  $section->id, 'Добавление запроса на проверку пожертвования');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Используйте для входа логин, пароль и ссылку на личный кабинет из письма от CloudPayments.',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('accepted_payment_notification',  $section->id, 'Добавление уведомления о принятом  пожертвовании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('rejected_payment_notification',  $section->id, 'Добавление уведомления об отклоненном пожертвовании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('notification_email',  $section->id, 'E-mail адрес для уведомлений об успешных пожертвованиях');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте e-mail ниже:',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('cp_payment_tryout', $section->id, 'Тестовое пожертвование');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-text-1',
            'text' => 'Давайте проверим, проходят ли пожертвования. Мы можем это сделать с помощью тестовых банковских карт, номера и данные которые находятся ниже.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => array('payment_tryout_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => 'Для продолжения необходимо выполнить все тестовые платежи'),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('cp_going_live',  $section->id, 'Переключение в боевой режим', array('next_label' => 'Отправить и продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно провели тестовое пожертвование. Для того, чтобы переключить ваш сайт в «боевой» режим, необходимо отправить письмо в службу поддержки CloudPayments. Ответы, как правило, приходит в течение суток.',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('cp_live_payment_tryout',  $section->id, 'Проверка настоящего пожертвования');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Пора проверить, что вас действительно переключили в «боевой режим», и реальные платежи будут проходить и зачисляться правильно.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'live-payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => array('payment_tryout_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => 'Для продолжения необходимо выполнить платёж.', 'is_live' => true)
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
            'next_label' => 'Продолжить',
            'next_url' => true,
            'prev' => 'Вернуться на предыдущий шаг',
        );

        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'cp' && $step->id === 'init') {
            $submit_settings['prev'] = false;   // I. e. the Wizard shouln't display the back link
        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

}