<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Cp_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;
    
    //protected static $cp_email = 'sales@cloudpayments.ru';
    protected static $cp_email = 'denis.cherniatev@gmail.com';

    protected function _setAttributes() {

        $this->_id = 'cp';
        $this->_title = 'Мастер подключения CloudPayments';

    }

    protected function _loadCssJs() {

        wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments', array(), false, true);
        
        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);
        
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

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
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-payment-cards-icons',
            'template' => 'cp_payment_cards_icons',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('prepare_documents',  $section->id, 'Подготовка документов');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Подключение к CloudPayments начинается с подготовки документов.<br />Скачайте и заполните необходимые документы.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-prepare-documents',
            'template' => 'cp_prepare_documents',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('send_documents',  $section->id, 'Отправка документов', array('next_label' => 'Отправить письмо', 'form_enctype' => 'multipart/form-data'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<p>После подготовки документов, их необходимо отправить в CloudPayments. Форма ниже позволит вам отправить пакет документов не уходя с сайта.</p>
<p>Вы также можете отправить эти документы из своей собственной почты на адрес: sales@cloudpayments.ru.</p>
<p>Обратите внимание, что проверка документов может занять до 3 рабочих дней.</p>
<p>Если вам нужно закрыть этот экран, мы запишем пройденные шаги и вы всегда сможете сюда вернуться.</p>',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_file',
            'custom_setting_id' => 'send_documents_file',
            'field_type' => 'file',
            'data' => array(
                'title' => 'Прикрепить Приложение 1',
                'required' => "Выберите файл",
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_to',
            'custom_setting_id' => 'send_documents_to',
            'field_type' => 'legend',
            'data' => array(
                'title' => 'Кому',
                'text' => self::$cp_email,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_from',
            'custom_setting_id' => 'send_documents_from',
            'field_type' => 'text',
            'data' => array(
                'title' => 'От кого',
                'value' => get_option('admin_email'),
                'required' => true,
            ),
        )))
        ->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_email_subject',
            'custom_setting_id' => 'send_documents_email_subject',
            'field_type' => 'text',
            'data' => array(
                'title' => 'Тема письма',
                'value' => 'Прошу подключить нас к вашей системе',
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_email_text',
            'custom_setting_id' => 'send_documents_email_text',
            'field_type' => 'textarea',
            'data' => array(
                'title' => 'Текст письма',
                'value' => 'Деньги переводятся в рублях на следующий рабочий день после совершения операции на счет юридического лица или ИП в любом российском банке за вычетом комиссии.',
                'required' => true,
            ),
        )))->addHandler(array($this, 'handleSendDocuments'))->addTo($section);

        $step = new Leyka_Settings_Step('signin_cp_account',  $section->id, 'Войдите в личный кабинет CloudPayments');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Используйте для входа логин, пароль и ссылку на личный кабинет из письма от CloudPayments.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-account-setup-instructions',
            'template' => 'cp_account_setup_instructions',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('copy_key',  $section->id, 'Копируем ключ');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте номер Public ID из личного кабинета CloudPayments, как на скриншоте ниже',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-copy-key',
            'template' => 'cp_copy_key',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('paste_key',  $section->id, 'Вставляем ключ в Лейку', array('next_label' => 'Продолжить и сохранить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вставьте скопированный ключ в поле ниже',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'cp_public_id',
            'option_id' => 'cp_public_id',
            'custom_setting_id' => 'cp_public_id',
            'required' => true,
            'field_type' => 'text',
            'show_title' => false,
            'show_description' => false,
        )))->addTo($section);

        $step = new Leyka_Settings_Step('check_payment_request',  $section->id, 'Добавление запроса на проверку пожертвования');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-check-payment-request',
            'template' => 'cp_check_payment_request',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('accepted_payment_notification',  $section->id, 'Добавление уведомления о принятом  пожертвовании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-accepted-payment-notification',
            'template' => 'cp_accepted_payment_notification',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('rejected_payment_notification',  $section->id, 'Добавление уведомления об отклоненном пожертвовании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте адрес:',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-rejected-payment-notification',
            'template' => 'cp_rejected_payment_notification',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('notification_email',  $section->id, 'E-mail адрес для уведомлений об успешных пожертвованиях');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Скопируйте e-mail ниже:',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-notification-email',
            'template' => 'cp_notification_email',
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
            //'data' => array('required' => 'Для продолжения необходимо выполнить все тестовые платежи'),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('cp_going_live',  $section->id, 'Переключение в боевой режим', array('next_label' => 'Отправить и продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно провели тестовое пожертвование. Для того, чтобы переключить ваш сайт в «боевой» режим, необходимо отправить письмо в службу поддержки CloudPayments. Ответы, как правило, приходит в течение суток.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_to',
            'custom_setting_id' => 'going_live_to',
            'field_type' => 'legend',
            'data' => array(
                'title' => 'Кому',
                'text' => self::$cp_email,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_from',
            'custom_setting_id' => 'going_live_from',
            'field_type' => 'text',
            'data' => array(
                'title' => 'От кого',
                'value' => get_option('admin_email'),
                'required' => true,
            ),
        )))
        ->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_email_subject',
            'custom_setting_id' => 'going_live_email_subject',
            'field_type' => 'text',
            'data' => array(
                'title' => 'Тема письма',
                'value' => sprintf('Прошу переключить %s в боевой режим', preg_replace("/^http[s]?:\/\//", "", site_url())),
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_email_text',
            'custom_setting_id' => 'going_live_email_text',
            'field_type' => 'textarea',
            'data' => array(
                'title' => 'Текст письма',
                'value' => "Я все проверил. Тестовые пожертвования проходят. Сайт соответствует техническим требованиям. Мы готовы принимать деньги.\nСпасибо!",
                'required' => true,
            ),
        )))->addHandler(array($this, 'handleGoingLive'))->addTo($section);

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
            //'data' => array('required' => 'Для продолжения необходимо выполнить платёж.', 'is_live' => true)
        )))->addTo($section);
            
        $this->_sections[$section->id] = $section;
        // The main CP settings section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение');

        $step = new Leyka_Settings_Step('cp_final', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы подключили CloudPayments. Стали доступны платежи с помощью платежных систем Visa и MasterCard.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'cp-final',
            'template' => 'cp_final',
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
                        'step_id' => 'cp_going_live',
                        'title' => 'Боевой режим',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_live_payment_tryout',
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
    
    public function handleSendDocuments(array $step_settings) {
        
        $errors = array();
        
        if(!isset($_FILES['leyka_send_documents_file'])) {
            $errors[] = new WP_Error('application_file_not_selected', 'Файл не выбран!');
        }
        
        $movefile = wp_handle_upload( $_FILES['leyka_send_documents_file'], array( 'test_form' => false ) );
        if(isset( $movefile['error'] ) ) {
            $errors[] = new WP_Error('application_file_upload_error', $movefile['error']);
        }
        
        if(!count($errors)) {
            $headers = array();
            $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_send_documents_from']);
            
            $attachments = array();
            $attachments[] = $movefile['file'];
            
            wp_mail( self::$cp_email, $_POST['leyka_send_documents_email_subject'], $_POST['leyka_send_documents_email_text'], $headers, $attachments );
            
            $_SESSION['leyka-cp-notif-documents-sent'] = true;
        }
        
        return !empty($errors) ? $errors : true;
    }
    
    public function handleGoingLive(array $step_settings) {
        
        $available_pms = leyka_options()->opt('pm_available');
        $available_pms[] = 'cp-card';
        $available_pms = array_unique($available_pms);
        leyka_options()->opt('pm_available', $available_pms);

        $pm_order = array();
        foreach($available_pms as $pm_full_id) {
            if($pm_full_id) {
                $pm_order[] = "pm_order[]={$pm_full_id}";
            }
        }
        leyka_options()->opt('pm_order', implode('&', $pm_order));
        
        $headers = array();
        $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_going_live_from']);
        
        wp_mail( self::$cp_email, $_POST['leyka_going_live_email_subject'], $_POST['leyka_going_live_email_text'], $headers );
        
        return true;
        
    }
    

}