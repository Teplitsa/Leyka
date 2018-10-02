<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Yandex_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;
    
    protected function _setAttributes() {

        $this->_id = 'yandex';
        $this->_title = 'Мастер подключения Яндекс Кассе';

    }

    protected function _loadCssJs() {

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);
        
        wp_localize_script('leyka-admin', 'leyka_wizard_yandex', array(
        ));

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main Yandex Kassa settings section:
        $section = new Leyka_Settings_Section('yandex', 'Яндекс Касса');

        // init
        $step = new Leyka_Settings_Step('init',  $section->id, 'Яндекс Касса');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Приём электронных платежей. Платежи с банковских карт Mastercard, Maestro, Visa, «Мир» и другие способы.  Касса подходит для ИП и юрлиц, работает в России и за её пределами.',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yandex-payment-cards-icons',
            'template' => 'yandex_payment_cards_icons',
        )))->addTo($section);

        // start_connection
        $step = new Leyka_Settings_Step('start_connection',  $section->id, 'Начало подключения');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'start-connection-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-follow-link',
            'custom_setting_id' => 'yandex_start_connection_follow_link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Перейдите по адресу',
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-copy-org-inn',
            'custom_setting_id' => 'yandex_start_connection_copy_org_inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'keys' => array('leyka_org_inn'),
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скопируйте ИНН вашей организации',
                'option_id' => 'org_inn',
                'option_title' => 'Вставьте ИНН вашей организации',
                'option_comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся',
                'show_text_if_set' => true,
                'required' => true,
            )
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-fill-inn',
            'custom_setting_id' => 'yandex_start_connection-fill-inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Вставьте в форму ИНН и нажмите кнопку <b>«Продолжить»</b>',
                'screenshot' => 'yandex/yandex_start_connection-inn-input.png'
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        // general_info
        $step = new Leyka_Settings_Step('general_info',  $section->id, 'Заполняем общие сведения');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'general-info-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-fill',
            'custom_setting_id' => 'yandex_general_info_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на кнопку <b>«Заполнить»</b>',
                'screenshot' => 'yandex/yandex_general_info-click-fill.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-general-info',
            'custom_setting_id' => 'yandex_general_info-click_general_info',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Общие сведения»</b>',
                'screenshot' => 'yandex/yandex_general_info.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-fill-form',
            'custom_setting_id' => 'yandex_general_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Заполните поля формы используя рекомендации ниже',
                'screenshot' => 'yandex/yandex_general_info-fill-form.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-site-address',
            'custom_setting_id' => 'yandex_general_info_site_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Адрес сайта',
                'value_text' => preg_replace("/^http[s]?:\/\//", "", site_url())
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-turnover',
            'custom_setting_id' => 'yandex_general_info_turnover',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Примерный оборот онлайн-платежей в месяц</b>',
                'text' => 'Если вам тяжело оценить оборот, то выберите <b>«До 1 млн Р»</b>'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-licence-required',
            'custom_setting_id' => 'yandex_general_info-licence-required',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Подлежит обязательному лицензированию</b>',
                'text' => 'Пропускаем этот пункт, если у вас отсутствует лицензируемая деятельность'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-has-benificiar-owner',
            'custom_setting_id' => 'yandex_general_info_has_benificiar_owner',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>У организации есть бенефициарный владелец</b>',
                'text' => 'Не устанавливать признак того, что у организации есть бенефициарный владелец, в поле о причине отсутсвтвия бенефициарного владельца либо выбрать <b>«Юрлицо – госучреждение»</b>, либо выбрать <b>«Другое»</b> и вручную написать <b>«Благотворительная организация»</b>'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-has-benificiars',
            'custom_setting_id' => 'yandex_general_info-has-benificiars',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Есть выгодоприобретатели</b>',
                'text' => 'По умолчанию флаг с поля снят, пропускаем этот пункт'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-not-bankrupt',
            'custom_setting_id' => 'yandex_general_info_not_bankrupt',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Подтверждаю отсутствие производства по делу о несостоятельности (банкротстве)</b>',
                'text' => 'Ставим галочку'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-funds-origin',
            'custom_setting_id' => 'yandex_general_info_funds_origin',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Происхождение средств</b>',
                'text' => 'Выбираем пункт <b>«Другое»</b> и в появившемся поле пишем <b>«Пожертвования»</b>'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-reputation',
            'custom_setting_id' => 'yandex_general_info_reputation',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => '<b>Деловая репутация</b>',
                'text' => 'Выбираем первый или второй пункт, который вам больше всего подходит'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-save',
            'custom_setting_id' => 'yandex_general_info_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => 'После заполнения, нажмите кнопку <b>«Сохранить»</b>'
            ),
        )))->addTo($section);
        
        // contact_info
        $step = new Leyka_Settings_Step('contact_info',  $section->id, 'Заполняем Контактную информацию');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'contact-info-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'contact-info-click',
            'custom_setting_id' => 'yandex_contact_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Контактная информация»</b>',
                'screenshot' => 'yandex/yandex_contact_info-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'contact-info-add-contacts',
            'custom_setting_id' => 'yandex_contact_info_add_contacts',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Добавьте тех, кто имеет отношение к подключению и работе с Яндекс.Кассой и после заполнения, нажмите кнопку <b>«Сохранить»</b>',
                'screenshot' => 'yandex/yandex_contact_info-save.png'
            ),
        )))->addTo($section);

        // gos_reg
        $step = new Leyka_Settings_Step('gos_reg',  $section->id, 'Сведения о государственной регистрации');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'gos-reg-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-click',
            'custom_setting_id' => 'yandex_gos_reg_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Государственная регистрация»</b>',
                'screenshot' => 'yandex/yandex_gos_reg-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-copy-address',
            'custom_setting_id' => 'yandex_gos_reg_copy_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_address'),
            'data' => array(
                'caption' => 'Скопируйте адрес регистрации и вставьте в форму',
                'option_id' => 'org_address',
                'option_title' => 'Вставьте адрес регистрации вашей организации',
                'option_comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся',
                'show_text_if_set' => true,
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-fill-address_screenshot',
            'custom_setting_id' => 'yandex_gos_reg_fill_address_screenshot',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_gos_reg-fill-address.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-fill-address',
            'custom_setting_id' => 'yandex_gos_reg-fill-address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => 'Заполните поле фактического адреса, где находится ваша организация. Если фактический адрес совпадает с адресом регистрации, вставьте это адрес еще раз. После заполнения, нажмите кнопку <b>«Сохранить»</b>'
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        // bank_account
        $step = new Leyka_Settings_Step('bank_account',  $section->id, 'Банковский счет');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'bank-account-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-click',
            'custom_setting_id' => 'yandex_bank_account_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Банковский счет»</b>',
                'screenshot' => 'yandex/yandex_bank_account-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-copy-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_copy_bank_bic',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_bank_bic'),
            'data' => array(
                'caption' => 'Скопируйте БИК банка вашей организации и вставьте в форму',
                'option_id' => 'org_bank_bic',
                'option_title' => 'Вставьте БИК банка вашей организации',
                'option_comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся',
                'show_text_if_set' => true,
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-fill-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_bic',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_bank_account-fill-bank-bic.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-fill-bank-account',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_account',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_bank_account'),
            'data' => array(
                'caption' => 'Скопируйте номер расчетного счета и вставьте в форму:',
                'option_id' => 'org_bank_account',
                'option_title' => 'Вставьте номер расчетного вашей организации',
                'option_comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся',
                'show_text_if_set' => true,
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-click-save',
            'custom_setting_id' => 'yandex_bank_account_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => 'После заполнения, нажмите кнопку <b>«Сохранить»</b>',
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        // boss_info
        $step = new Leyka_Settings_Step('boss_info',  $section->id, 'Заполняем Данные руководителя');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'boss-info-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'boss-info-click',
            'custom_setting_id' => 'yandex_boss_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Данные руководителя»</b>',
                'screenshot' => 'yandex/yandex_boss_info-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'boss-info-fill-form',
            'custom_setting_id' => 'yandex_boss_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'У вас на руках сканы паспорта вашего руководителя. Введите все необходимые данные.  После заполнения, нажмите кнопку <b>«Сохранить»</b>',
                'screenshot' => 'yandex/yandex_boss_info-fill-form.png'
            ),
        )))->addTo($section);
        
        // upload_documents
        $step = new Leyka_Settings_Step('upload_documents',  $section->id, 'Загрузка документов');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'upload-documents-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-click',
            'custom_setting_id' => 'yandex_upload_documents_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Загрузка документов»</b>',
                'screenshot' => 'yandex/yandex_upload_documents-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-add-file',
            'custom_setting_id' => 'yandex_upload_documents_add_files',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'По очереди загрузите документы. Нажимайте на кнопку <b>«Выбрать файл»</b> и добавляйте файлы.',
                'screenshot' => 'yandex/yandex_upload_documents-add-file.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-explain',
            'custom_setting_id' => 'yandex_upload_documents_explain',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => '
                    <p>Если вы являетесь зарегистрированным НКО, то в поле «Другие документы» загрузите скан свидетельства о регистрации в министерстве юстиции РФ.</p>
                    <p>Если такого документа у вас нет, пропустите этот пункт</p>',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-click-save',
            'custom_setting_id' => 'yandex_upload_documents_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => 'После заполнения, нажмите кнопку <b>«Сохранить»</b>',
            ),
        )))->addTo($section);
        
        // send_form
        $step = new Leyka_Settings_Step('send_form',  $section->id, 'Отправляем анкету');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send-form-intro',
            'text' => 'В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send-form-send-form',
            'custom_setting_id' => 'yandex_send_form_send_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Все данные Анкеты заполнены. Нажмите на кнопку <b>«Отправить анкету»</b>',
                'screenshot' => 'yandex/yandex_send_form.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send-form',
            'custom_setting_id' => 'yandex_send_form_',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => '
                    <p>Процес проверки занимает 2-3 рабочих дня. Вам придет уведомление на почту о завершении проверки или вы можете узнать о завершении проверки <a href="https://kassa.yandex.ru/" target="_blank">в личном кабинете на Яндекс Кассы</a></p>
                    <p>Сейчас можно выйти из нашего мастера установки. Мы запомним, где вы прервали процесс.</p>',
            ),
        )))->addTo($section);

        // sign_documents
        $step = new Leyka_Settings_Step('sign_documents',  $section->id, 'Подписываем документы');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'sign-documents-intro',
            'text' => 'Менеджер кассы проверяет анкету и формирует заявление, которое станет доступным для скачивания в личном кабинете.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'sign-documents-download',
            'custom_setting_id' => 'yandex_sign_documents_download',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скачайте документы из кабинета Яндекс.Кассы',
                'screenshot' => 'yandex/yandex_sign_documents.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'sign-documents-upload',
            'custom_setting_id' => 'yandex_sign_documents_upload',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => '
                    <p>Распечатайте все листы и на последнем листе документа указажите дату подписи, подпишите у руководителя и поставьте печать организации</p>
                    <p>Загрузите сканы всех листов документа в личный кабинет Яндекс.Кассы</p>',
            ),
        )))->addTo($section);
        
        // settings
        $step = new Leyka_Settings_Step('settings',  $section->id, 'Заполняем раздел Настройки');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'settings-intro',
            'text' => 'Переходим к техническому подключению Яндекс Кассы к Лейке.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'settings-click-fill',
            'custom_setting_id' => 'yandex_settings_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Кликните на пункт <b>«Заполнить»</b>',
                'screenshot' => 'yandex/yandex_settings-click.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'settings-payment-module',
            'custom_setting_id' => 'yandex_settings_payment_module',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Выберите пункт <b>«Платежный модуль»</b> кликнув на кружок напротив пункта и нажмите кнопку <b>«Продолжить»</b>',
                'screenshot' => 'yandex/yandex_settings-payment-module.png',
            ),
        )))->addTo($section);
        
        // parameters
        $step = new Leyka_Settings_Step('parameters',  $section->id, 'Заполняем раздел Параметры');
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'parameters',
            'custom_setting_id' => 'yandex_parameters',
            'field_type' => 'custom_yandex_parameters',
            'keys' => array('leyka_yandex_shop_password'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);
        
        // online_kassa
        $step = new Leyka_Settings_Step('online_kassa',  $section->id, 'Заполняем раздел Он-лайн касса');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'online-kassa-intro',
            'text' => 'НКО не нужно использовать он-лайн кассу, поэтому выбираем пункт <b>«Самостоятельно»</b> и нажмите кнопку <b>«Отправить»</b>',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'online-kassa-send',
            'custom_setting_id' => 'yandex_online_kassa_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_online_kassa.png'
            ),
        )))->addTo($section);
        
        // send2check
        $step = new Leyka_Settings_Step('send2check',  $section->id, 'Отправляем данные на проверку');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send2check-intro',
            'text' => 'Обычно этот процес занимает 2-3 рабочих дня. Вам придет уведомление на почту о завершении проверки.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send2check-send',
            'custom_setting_id' => 'yandex_send2check_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_send2check.png',
            ),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'send2check-outro',
            'text' => 'Вы можете выйти из мастера установки – мы запомним, где вы прервали процесс.',
        )))->addTo($section);
        
        // fill_leyka_data
        $step = new Leyka_Settings_Step('fill_leyka_data',  $section->id, 'Заполняем данные в Лейке', array('next_label' => 'Сохранить и продолжить'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'fill-leyka-data-intro',
            'text' => 'Переходим к техническому подключению Яндекс Кассы к Лейке.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-follow-link',
            'custom_setting_id' => 'yandex_fill_leyka_data-follow-link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Перейдите по адресу',
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скопируйте параметр <b>«ShopID»</b>',
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-shop-id.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_yandex_shop_id'),
            'data' => array(
                'option_id' => 'yandex_shop_id',
                'option_title' => 'Вставьте параметр в поле',
                'option_placeholder' => 'Ваш ShopID',
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => 'Скопируйте параметр <b>«Секретный ключ»</b>',
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-secret-key.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_yandex_secret_key'),
            'data' => array(
                'option_id' => 'yandex_secret_key',
                'option_title' => 'Вставьте параметр в поле',
                'option_placeholder' => 'Секретный ключ',
                'required' => true,
            ),
        )))->addHandler(array($this, 'handleSaveLeykaData'))->addTo($section);

        // test_payment
        $step = new Leyka_Settings_Step('test_payment',  $section->id, 'Проверка настоящего пожертвования');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'test-payment-intro',
            'text' => 'Давайте проверим работу Яндекс Кассы заплатив небольшую сумму сами себе. После проведения платежи деньги будут зачислены на расчетный счет, указанный ранее в Яндекс Кассе в течение 1 банковского дня',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'test-payment',
            'custom_setting_id' => 'yandex_test_payment',
            'field_type' => 'custom_yandex_test_payment',
            'keys' => array('payment_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => 'Для продолжения необходимо выполнить платёж.')
        )))->addHandler(array($this, 'handleFinalTest'))->addTo($section);

        $this->_sections[$section->id] = $section;
        
        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение');

        $step = new Leyka_Settings_Step('yandex_final', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '
<p>Вы подключили Яндекс Деньги. Стали доступны платежи с помощью банковских карт, Яндекс.Деньги, Сбербанк Онлайн (интернет-банк Сбербанка), Альфа-Клик (интернет-банк Альфа-Банка), криптограмма Apple Pay, криптограмма Google Pay, QIWI Кошелек, Webmoney, баланс мобильного телефона</p>
<p>Поделитесь последней вашей кампанией с друзьями и попросите их отправить пожертвование. Так вы сможете протестировать новый метод оплаты</p>',
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yandex-final',
            'template' => 'yandex_final',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _initNavigationData() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'yandex',
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
            case 'yandex-init': $navigation_position = 'yandex'; break;
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

        if($step->section_id === 'yandex' && $step->id === 'init') {
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
            leyka_save_option(preg_replace("/^leyka_/", "", $option_id));
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