<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'init';
        $this->_title = 'Мастер настройки Лейки';

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
            'text' => 'Вы должны определить, от имени кого вы будете собирать пожертвования. Как НКО (некоммерческая организация) — юридическое лицо или как обычный гражданин — физическое лицо. Низовым инициативам будет удобнее собирать от имени физического лица (помните о налогах). При этом у юридических лиц больше возможностей для сбора.',
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
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_face_position',
                    'option_id' => 'org_face_position',
                    'show_description' => false,
                )),
                new Leyka_Option_Block(array(
                    'id' => 'org_face_fio_ip',
                    'option_id' => 'org_face_fio_ip',
                )),
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_address',
            'option_id' => 'org_address',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-2',
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
            'id' => 'complex-row-3',
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
            'id' => 'complex-row-4',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_contact_person_name',
                    'option_id' => 'org_contact_person_name',
                )),
                new Leyka_Option_Block(array(
                    'id' => 'org_contact_email',
                    'option_id' => 'tech_support_email',
//                    'show_description' => false,
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
            'text' => 'Для соблюдения всех формальных процедур вам необходимо предоставить оферту о заключении договора пожертвования. Мы подготовили для вас шаблонный вариант. Пожалуйста, проверьте. При необходимости, скорректируйте текст оферты. Текст, выделенный синим, подставлен автоматически, но вы также можете его поменять. После завершения всех правок нажмите «Сохранить и продолжить».',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'terms_of_service_text',
        )))->addTo($section);

        // Physical receiver type - Terms of service step:
        $step = new Leyka_Settings_Step('receiver_physical_terms_of_service', $section->id, 'Оферта');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Для соблюдения всех формальных процедур вам необходимо предоставить оферту о заключении договора пожертвования. Мы подготовили для вас шаблонный вариант. Пожалуйста, проверьте. При необходимости, скорректируйте текст оферты. Текст, выделенный синим, подставлен автоматически, но вы также можете его поменять. После завершения всех правок нажмите «Сохранить и продолжить».',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'person_terms_of_service_text',
        )))->addTo($section);

        // Legal receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_legal_pd_terms', $section->id, 'Соглашение о персональных данных');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => 'В рамках сбора пожертвований вы будете собирать персональные данные доноров. «Согласие на обработку персональных данных» — обязательный документ по закону ФЗ-152. Мы подготовили шаблон текста соглашения, вы можете отредактировать его под ваши требования. Текст, выделенный синим, подставлен автоматически, но вы также можете его поменять. Все персональные данные хранятся на вашем сайте и никуда не отправляются.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'pd_terms_text',
        )))->addTo($section);

        // Physical receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_physical_pd_terms', $section->id, 'Соглашение о персональных данных');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => 'В рамках сбора пожертвований вы будете собирать персональные данные доноров. «Согласие на обработку персональных данных» — обязательный документ по закону ФЗ-152. Мы подготовили шаблон текста соглашения, вы можете отредактировать его под ваши требования. Текст, выделенный синим, подставлен автоматически, но вы также можете его поменять. Все персональные данные хранятся на вашем сайте и никуда не отправляются.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'person_pd_terms_text',
        )))->addTo($section);

        // Section final (outro) step:
        $step = new Leyka_Settings_Step('final', $section->id, 'Хорошая работа!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Вы успешно заполнили свои данные и теперь можете перейти к следующему этапу.',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Receiver data Section - End

        // Diagnostic data Section:
        $section = new Leyka_Settings_Section('dd', 'Диагностические данные');

        // The plugin usage stats collection step:
        $step = new Leyka_Settings_Step('plugin_stats', $section->id, 'Диагностические данные');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Мы просим вас подтвердить согласие на отправку <strong>технических данных</strong>  к нам, в Теплицу, что
позволит нам последовательно совершенствовать работу плагина, а также помочь быстрее разрешать технические проблемы работы с Лейкой, если таковые возникнут, у конкретных пользователей. Эти данные будут использоваться только разработчиками плагина и не будут передаваться третьим лицам.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'send_plugin_stats',
            'option_id' => 'send_plugin_stats',
            'show_title' => false,
        )))->addTo($section);

        // The plugin usage stats collection - accepted:
        $step = new Leyka_Settings_Step('plugin_stats_accepted', $section->id, 'Спасибо!', array('next_label' => 'Продолжить'));
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
            'text' => 'Сбор пожертвований в Лейке осуществляется в рамках одной или нескольких кампаний,
каждая из которых характеризуется наименованием, кратким описанием, изображением и целевой суммой. Настройте вашу первую кампанию.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign_title',
            'custom_setting_id' => 'campaign_title',
            'field_type' => 'text',
            'data' => array(
                'title' => 'Название кампании',
                'required' => true,
                'placeholder' => 'Например, «На уставную деятельность организации»',
                'value' => $init_campaign ? $init_campaign->title : '',
                'comment' => 'Краткое и прозрачное описание того, для чего собираются средства.',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-lead',
            'custom_setting_id' => 'campaign_short_description',
            'field_type' => 'textarea',
            'data' => array(
                'title' => 'Краткое описание',
                'value' => $init_campaign ? $init_campaign->short_description : '',
                'comment' => 'В кратком описании кампании в сжатой форме рассказывается, почему стоит пожертвовать.',
            ),
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-2',
            'entry_width' => 0.5,
            'entries' => array(
                new Leyka_Custom_Setting_Block(array(
                    'id' => 'campaign-target',
                    'custom_setting_id' => 'campaign_target',
                    'field_type' => 'number',
                    'data' => array(
                        'title' => 'Целевая сумма',
                        'min' => 0,
                        'step' => 0.01,
                        'value' => $init_campaign && $init_campaign->target ? $init_campaign->target : '',
                        'show_description' => false,
                        'placeholder' => 'Например, 100000',
                        'description' => 'Оставьте пустым, если нет ограничений по целевой сумме',
//                        'comment' => 'Комментарий к целевой сумме кампании',
                    ),
                )),
            )
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

        $step = new Leyka_Settings_Step('donors_communication', $section->id, 'Благодарность донору');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => 'После того, как донор внёс своё пожертвование, хорошим тоном считается показать ему страницу с благодарностью и отправить письмо. Ниже вы можете отредактировать то, что донор получит в своем почтовом ящике. Поле «Отправитель» обозначает, от кого придет письмо донору. Как правило, указывается название вашей организации. Поле «E-mail отправителя» обозначает, какой обратный адрес увидит донор. Наконец, в тексте письма вы можете сами составить адрес вашей искренней благодарности дарителю. Обратите внимание, что в вашем распоряжении специальные тэги для автоматической подстановки.',
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
        )))->addHandler(array($this, 'handleCampaignCompletedStep'))
            ->addTo($section);

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
                #$next_step_full_id = 'final-init';
                $next_step_full_id = 'cd-campaign_completed';
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
                        'title' => 'Благодарность донору',
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
                $navigation_position = 'rd-receiver_pd_terms';
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

        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

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

        // Receiver type Step prerequisites - show "legal" receiver type only if receiver country is set:
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
                $this->_handleSettingsGoBack('cd-campaign_description');
            }

        } else if($this->getCurrentStep()->id === 'campaign_completed') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handleSettingsGoBack('cd-campaign_description');
            }

            $empty_bank_essentials_options = leyka_get_empty_bank_essentials_options();
            if($empty_bank_essentials_options) { // Show the fields
                foreach($empty_bank_essentials_options as $option_id) {
                    $this->getCurrentStep()->addBlock(new Leyka_Option_Block(array(
                        'id' => $option_id,
                        'option_id' => $option_id,
                    )));
                }
            } else { // Enable the Quittance PM

                $pm_data = leyka_options()->opt('pm_available');
                $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

                if( !in_array($quittance_pm_full_id, $pm_data) ) {

                    $pm_data[] = $quittance_pm_full_id;
                    leyka_options()->opt('pm_available', $pm_data);

                    $pm_order = array();
                    foreach($pm_data as $pm_full_id) {
                        if($pm_full_id) {
                            $pm_order[] = "pm_order[]={$pm_full_id}";
                        }
                    }

                    leyka_options()->opt('pm_order', implode('&', $pm_order));

                }

            }

        }

    }

    public function handleCampaignDescriptionStep(array $step_settings) {

        $init_campaign_params = array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_title' => trim(esc_attr(wp_strip_all_tags($step_settings['campaign_title']))),
            'post_excerpt' => trim(esc_textarea($step_settings['campaign_short_description'])),
            'post_content' => '',
        );

        $existing_campaign_id = get_transient('leyka_init_campaign_id');
        if($existing_campaign_id) {

            if(get_post($existing_campaign_id)) {
                $init_campaign_params['ID'] = $existing_campaign_id;
            } else {

                $existing_campaign_id = false;
                delete_transient('leyka_init_campaign_id');

            }

        }

        $campaign_id = wp_insert_post($init_campaign_params, true);

        if(is_wp_error($campaign_id)) {
            return new WP_Error('init_campaign_insertion_error', 'Ошибка при создании кампании');
        }

        update_post_meta($campaign_id, 'campaign_target', (float)$step_settings['campaign_target']);

        if( !$existing_campaign_id ) {

            $this->_addHistoryEntry(array('campaign_id' => $campaign_id));
            set_transient('leyka_init_campaign_id', $campaign_id);

        }

        return true;

    }

    public function handleCampaignDecorationStep(array $step_settings) {

        // Publish the init campaign:
        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = array();

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', 'ID кампании неправильный или отсутствует');
        }

        if(
            $campaign->post_type !== 'publish' &&
            is_wp_error(wp_update_post(array('ID' => $campaign_id, 'post_status' => 'publish')))
        ) {
            return new WP_Error('init_campaign_publishing_error', 'Ошибка при публикации кампании');
        }

        return $errors ? $errors : true;

    }

    public function handleCampaignCompletedStep(array $step_settings) {

        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = array();

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', 'ID кампании неправильный или отсутствует');
        }

        // Enable the Quittance PM, if all the needed fields are filled:
        if(leyka_are_bank_essentials_set()) {

            $pm_data = leyka_options()->opt('pm_available');
            $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

            if( !in_array($quittance_pm_full_id, $pm_data) ) {

                $pm_data[] = $quittance_pm_full_id;
                leyka_options()->opt('pm_available', $pm_data);

                $pm_order = array();
                foreach($pm_data as $pm_full_id) {
                    if($pm_full_id) {
                        $pm_order[] = "pm_order[]={$pm_full_id}";
                    }
                }

                leyka_options()->opt('pm_order', implode('&', $pm_order));

            }

        }

        return $errors ? $errors : true;

    }

}
