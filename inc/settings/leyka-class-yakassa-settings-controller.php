<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Yakassa_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;
    
    protected function _setAttributes() {

        $this->_id = 'yakassa';
        $this->_title = 'Мастер подключения Яндекс Кассе';

    }

    protected function _loadCssJs() {

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);
        
        wp_localize_script('leyka-admin', 'leyka_wizard_yakassa', array(
        ));

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main Yandex Kassa settings section:
        $section = new Leyka_Settings_Section('yakassa', 'Яндекс Касса');

        $step = new Leyka_Settings_Step('init',  $section->id, 'Яндекс Касса');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Приём электронных платежей. Платежи с банковских карт Mastercard, Maestro, Visa, «Мир» и другие способы.  Касса подходит для ИП и юрлиц, работает в России и за её пределами.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yakassa-payment-cards-icons',
            'template' => 'yakassa_payment_cards_icons',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('start_connection',  $section->id, 'Начало подключения');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection',
            'custom_setting_id' => 'yakassa_start_connection',
            'field_type' => 'custom_yakassa_start_connection',
            'keys' => array('org_inn'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        $step = new Leyka_Settings_Step('general_info',  $section->id, 'Заполняем общие сведения');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'general-info',
            'template' => 'yakassa_general_info',
        )))->addTo($section);
        
        $step = new Leyka_Settings_Step('contact_info',  $section->id, 'Заполняем Контактную информацию');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'contact-info',
            'template' => 'yakassa_contact_info',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('gos_reg',  $section->id, 'Сведения о государственной регистрации');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg',
            'custom_setting_id' => 'yakassa_gos_reg',
            'field_type' => 'custom_yakassa_gos_reg',
            'keys' => array('org_address'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        $step = new Leyka_Settings_Step('bank_account',  $section->id, 'Банковский счет');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account',
            'custom_setting_id' => 'yakassa_bank_account',
            'field_type' => 'custom_yakassa_bank_account',
            'keys' => array('org_bank_bic', 'org_bank_account'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        $step = new Leyka_Settings_Step('boss_info',  $section->id, 'Заполняем Данные руководителя');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'boss-info',
            'template' => 'yakassa_boss_info',
        )))->addTo($section);
        
        $step = new Leyka_Settings_Step('upload_documents',  $section->id, 'Заполняем Данные руководителя');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'upload-documents',
            'template' => 'yakassa_upload_documents',
        )))->addTo($section);
        
        $step = new Leyka_Settings_Step('send_form',  $section->id, 'Отправляем анкету');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send-form',
            'template' => 'yakassa_send_form',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('sign_documents',  $section->id, 'Подписываем документы');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'sign-documents',
            'template' => 'yakassa_sign_documents',
        )))->addTo($section);
        
        $step = new Leyka_Settings_Step('settings',  $section->id, 'Заполняем раздел Настройки');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'settings',
            'template' => 'yakassa_settings',
        )))->addTo($section);
        
        $step = new Leyka_Settings_Step('parameters',  $section->id, 'Заполняем раздел Параметры');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'parameters',
            'custom_setting_id' => 'yakassa_parameters',
            'field_type' => 'custom_yakassa_parameters',
            'keys' => array('yandex_shop_password'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        $step = new Leyka_Settings_Step('online_kassa',  $section->id, 'Заполняем раздел Он-лайн касса');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'online-kassa',
            'template' => 'yakassa_online_kassa',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        $step = new Leyka_Settings_Step('send2check',  $section->id, 'Отправляем данные на проверку');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send2check',
            'template' => 'yakassa_send2check',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        $step = new Leyka_Settings_Step('fill_leyka_data',  $section->id, 'Заполняем данные в Лейке', array('next_label' => 'Сохранить и продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'fill_leyka_data-intro',
            'text' => 'Переходим к техническому подключению Яндекс Кассы к Лейке.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-follow-link',
            'custom_setting_id' => 'yakassa_fill_leyka_data-follow-link',
            'field_type' => 'custom_yakassa_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Перейдите по адресу',
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-shop-id',
            'custom_setting_id' => 'yakassa_fill_leyka_data-copy-shop-id',
            'field_type' => 'custom_yakassa_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скопируйте параметр <b>«ShopID»</b>',
                'screenshot' => 'yakassa/yakassa_fill_leyka_data-copy-shop-id.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-shop-id',
            'custom_setting_id' => 'yakassa_fill_leyka_data-paste-shop-id',
            'field_type' => 'custom_yakassa_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'option_id' => 'yandex_shop_id',
                'option_title' => 'Вставьте параметр в поле',
                'option_placeholder' => 'Ваш ShopID',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-secret-key',
            'custom_setting_id' => 'yakassa_fill_leyka_data-copy-secret-key',
            'field_type' => 'custom_yakassa_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скопируйте параметр <b>«Секретный ключ»</b>',
                'screenshot' => 'yakassa/yakassa_fill_leyka_data-copy-secret-key.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-secret-key',
            'custom_setting_id' => 'yakassa_fill_leyka_data-paste-secret-key',
            'field_type' => 'custom_yakassa_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'option_id' => 'yandex_secret_key',
                'option_title' => 'Вставьте параметр в поле',
                'option_placeholder' => 'Секретный ключ',
            ),
        )))->addHandler(array($this, 'handleSaveLeykaData'))->addTo($section);

        $step = new Leyka_Settings_Step('test_payment',  $section->id, 'Проверка настоящего пожертвования');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'test-payment',
            'custom_setting_id' => 'yakassa_test_payment',
            'field_type' => 'custom_yakassa_test_payment',
            'keys' => array('payment_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => 'Для продолжения необходимо выполнить платёж.')
        )))->addHandler(array($this, 'handleFinalTest'))->addTo($section);

        $this->_sections[$section->id] = $section;
        
        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение');

        $step = new Leyka_Settings_Step('yakassa_final', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '
<p>Вы подключили Яндекс Деньги. Стали доступны платежи с помощью банковских карт, Яндекс.Деньги, Сбербанк Онлайн (интернет-банк Сбербанка), Альфа-Клик (интернет-банк Альфа-Банка), криптограмма Apple Pay, криптограмма Google Pay, QIWI Кошелек, Webmoney, баланс мобильного телефона</p>
<p>Поделитесь последней вашей кампанией с друзьями и попросите их отправить пожертвование. Так вы сможете протестировать новый метод оплаты</p>',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yakassa-final',
            'template' => 'yakassa_final',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _initNavigationData() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'yakassa',
                'title' => 'Яндекс Касса',
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'start_connection',
                        'title' => 'Начало подключения',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'general_info',
                        'title' => 'Общие сведния',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'contact_info',
                        'title' => 'Контактная информация',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'gos_reg',
                        'title' => 'Гос.регистрация',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'bank_account',
                        'title' => 'Банковский счет',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'boss_info',
                        'title' => 'Данные руководителя',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'upload_documents',
                        'title' => 'Загрузка документов',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send_form',
                        'title' => 'Отправляем анкету',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'sign_documents',
                        'title' => 'Подписываем документы',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'settings',
                        'title' => 'Настройки',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'parameters',
                        'title' => 'Параметры',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'online_kassa',
                        'title' => 'Он-лайн касса',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send2check',
                        'title' => 'Проверка',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'fill_leyka_data',
                        'title' => 'Данные в Лейке',
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'test_payment',
                        'title' => 'Тестируем платежи',
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
            case 'yakassa-init': $navigation_position = 'yakassa'; break;
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

        if($step->section_id === 'yakassa' && $step->id === 'init') {
            $submit_settings['prev'] = false;   // I. e. the Wizard shouln't display the back link
        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }
    
    public function handleSaveOptions(array $step_settings) {
        
        $errors = array();
        
        foreach($step_settings as $option_id => $value) {
            leyka_save_option($option_id);
        }
        
        return !empty($errors) ? $errors : true;
    
    }
    
    public function handleSaveLeykaData(array $step_settings) {
        
        if($this->handleSaveOptions($step_settings) === true) {

            $available_pms = leyka_options()->opt('pm_available');
            $available_pms[] = 'yandex-yandex_card';
            $available_pms[] = 'yandex-yandex_money';
            $available_pms[] = 'yandex-yandex_all';
            $available_pms = array_unique($available_pms);
            leyka_options()->opt('pm_available', $available_pms);
    
            $pm_order = array();
            foreach($available_pms as $pm_full_id) {
                if($pm_full_id) {
                    $pm_order[] = "pm_order[]={$pm_full_id}";
                }
            }
            leyka_options()->opt('pm_order', implode('&', $pm_order));        
            
        }
        
    }
    
    public function handleFinalTest() {
        
    }
    
}