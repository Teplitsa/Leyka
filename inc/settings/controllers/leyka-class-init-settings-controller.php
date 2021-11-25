<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'init';
        $this->_title = __('Leyka setup Wizard', 'leyka');

        $options = [
            'org_actual_address' => [
                'type' => 'textarea',
                'title' => __('Organization actual address', 'leyka'),
            ],
            'org_actual_address_differs' => [
                'type' => 'checkbox',
                'title' => __('The actual address is different from the legal', 'leyka'),
            ],
        ];

        foreach($options as $option_name => $params) {
            if( !leyka_options()->option_exists($option_name) ) {
                leyka_options()->add_option($option_name, $params['type'], $params);
            }
        }

    }

    protected function _load_frontend_scripts() {
        parent::_load_frontend_scripts();
    }

    protected function _set_stages() {

        // Receiver's data Section:
        $stage = new Leyka_Settings_Stage('rd', __('Your data', 'leyka'));

        // 0-step:
        $section = new Leyka_Settings_Section('init',  $stage->id, __('Hello!', 'leyka'), ['header_classes' => 'greater',]);
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __("You installed the Leyka plugin, all that's left is to set it up. We will guide you through all the steps and help with tips.", 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'receiver_country',
            'option_id' => 'receiver_country',
        ]))->add_handler([$this, 'handle_init_section'])
            ->add_to($stage);

        // Receiver type step:
        $section = new Leyka_Settings_Section('receiver_type', $stage->id, __('Donations receiver', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('You need to determine on whose behalf you will collect donations. As NGOs (non-profit organization) - a legal entity, or as an ordinary citizen - a physical person. Grassroots initiatives will be easier to collect on behalf of the individual (but remember about taxes). That being said, legal entities will have more opportunities to collect.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'receiver_type',
            'option_id' => 'receiver_legal_type',
            'show_title' => false,
        ]))->add_to($stage);

        // Legal receiver type - org. data step:
        $section = new Leyka_Settings_Section('receiver_legal_data', $stage->id, __('Organization data', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('These data we will use for the templates of Terms of service contracts and accounting documents for your donors. All data can be found in the founding documents of your organization.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'org_full_name',
            'option_id' => 'org_full_name',
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'org_short_name',
            'option_id' => 'org_short_name',
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-1',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_face_position',
                    'option_id' => 'org_face_position',
                    'show_description' => false,
                ]),
                new Leyka_Option_Block([
                    'id' => 'org_face_fio_ip',
                    'option_id' => 'org_face_fio_ip',
                ]),
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'org_address',
            'option_id' => 'org_address',
            'custom_setting_id' => 'org_address',
            'field_type' => 'textarea',
            'data' => [
                'title' => __('The organization official address', 'leyka'),
                'keys' => ['org_address',],
                'value' => leyka_options()->opt('org_address'),
                'required' => __('The field value is required', 'leyka'),
            ],
            'show_description' => false,
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'org_actual_address_differs',
            'custom_setting_id' => 'org_actual_address_differs',
            'field_type' => 'checkbox',
            'data' => [
                'title' => __('The actual address is different from the legal', 'leyka'),
                'keys' => [],
                'field_classes' => ['single-control'],
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'org_actual_address',
            'option_id' => 'org_actual_address',
            'custom_setting_id' => 'org_actual_address',
            'field_type' => 'textarea',
            'data' => [
                'title' => '',
                'value' => leyka_options()->opt('org_actual_address'),
                'keys' => ['org_actual_address',],
            ],
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-2',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_state_reg_number',
                    'option_id' => 'org_state_reg_number',
                    'show_description' => false,
                ]),
                new Leyka_Option_Block([
                    'id' => 'org_kpp',
                    'option_id' => 'org_kpp',
                    'show_description' => false,
                ]),
            ],
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-3',
            'entry_width' => 0.5,
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_inn',
                    'option_id' => 'org_inn',
                    'show_description' => false,
                ]),
            ],
        ]))->add_block(new Leyka_Subtitle_Block([
            'id' => 'contact_person_data',
            'text' => __('Contact person', 'leyka'),
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-4',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_contact_person_name',
                    'option_id' => 'org_contact_person_name',
                ]),
                new Leyka_Option_Block([
                    'id' => 'org_contact_email',
                    'option_id' => 'tech_support_email',
//                    'show_description' => false,
                ]),
            ],
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        // Physical receiver type - person's data step:
        $section = new Leyka_Settings_Section('receiver_physical_data', $stage->id, __('Your data', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('We will use these data for accounting documents to your donors.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_full_name',
            'option_id' => 'person_full_name',
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_email',
            'option_id' => 'tech_support_email',
            'title' => __('Contact email', 'leyka'),
            'required' => true,
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_address',
            'option_id' => 'person_address',
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_inn',
            'option_id' => 'person_inn',
        ]))->add_to($stage);

        // Legal receiver type - org. bank essentials step:
        $section = new Leyka_Settings_Section('receiver_legal_bank_essentials', $stage->id, __('Bank essentials', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('These data needed for accounting documents, as well as to use bank order donations.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'org_bank_name',
            'option_id' => 'org_bank_name',
            'show_description' => false,
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-2',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_bank_account',
                    'option_id' => 'org_bank_account',
                    'show_description' => false,
                ]),
            ],
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-3',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_bank_corr_account',
                    'option_id' => 'org_bank_corr_account',
                    'show_description' => false,
                ]),
            ],
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-1',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'org_bank_bic',
                    'option_id' => 'org_bank_bic',
                    'show_description' => false,
                ]),
            ],
        ]))->add_to($stage);

        // Physical receiver type - person's bank essentials step:
        $section = new Leyka_Settings_Section('receiver_physical_bank_essentials', $stage->id, __('Bank essentials', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('These data needed for accounting documents, as well as to use bank order donations.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_bank_name',
            'option_id' => 'person_bank_name',
            'show_description' => false,
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_bank_account',
            'option_id' => 'person_bank_account',
            'show_description' => false,
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'person_bank_corr_account',
            'option_id' => 'person_bank_corr_account',
            'show_description' => false,
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-1',
            'entries' => [
                new Leyka_Option_Block([
                    'id' => 'person_bank_bic',
                    'option_id' => 'person_bank_bic',
                    'show_description' => false,
                ]),
            ],
        ]))->add_to($stage);

        // Legal receiver type - Terms of service step:
        $section = new Leyka_Settings_Section('receiver_legal_terms_of_service', $stage->id, __('Terms of service', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('To comply with all the formalities you need to provide a Terms of service document to conclude a donation agreement. We have prepared a template option - please check it out. If necessary, adjust the document text. Text parts highlighted in blue are replaced automatically, but you can also change them. After completion of all of the changes, click "Save & continue".', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'terms_of_service_text',
            'option_id' => 'terms_of_service_text',
        ]))->add_to($stage);

        // Physical receiver type - Terms of service step:
        $section = new Leyka_Settings_Section('receiver_physical_terms_of_service', $stage->id, __('Terms of service', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('To comply with all the formalities you need to provide a Terms of service document to conclude a donation agreement. We have prepared a template option - please check it out. If necessary, adjust the document text. Text parts highlighted in blue are replaced automatically, but you can also change them. After completion of all of the changes, click "Save & continue".', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'terms_of_service_text',
            'option_id' => 'person_terms_of_service_text',
        ]))->add_to($stage);

        // Legal receiver type - personal data terms step:
        $section = new Leyka_Settings_Section('receiver_legal_pd_terms', $stage->id, __('Terms of personal data usage', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-2',
            'text' => __('As part of your fundraising you will collect the donors personal data. "Consent to the processing of personal data" - binding instrument on the federal law FZ-152. We have prepared the text of the agreement template, you can edit it to your needs. Text parts highlighted in blue are replaced automatically, but you can also change them. All personal data is stored on your site and will not be sent anywhere.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'pd_terms_text',
            'option_id' => 'pd_terms_text',
        ]))->add_to($stage);

        // Physical receiver type - personal data terms step:
        $section = new Leyka_Settings_Section('receiver_physical_pd_terms', $stage->id, __('Terms of personal data usage', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-2',
            'text' => __('As part of your fundraising you will collect the donors personal data. "Consent to the processing of personal data" - binding instrument on the federal law FZ-152. We have prepared the text of the agreement template, you can edit it to your needs. Text parts highlighted in blue are replaced automatically, but you can also change them. All personal data is stored on your site and will not be sent anywhere.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'pd_terms_text',
            'option_id' => 'person_pd_terms_text',
        ]))->add_to($stage);

        // Section final (outro) step:
        $section = new Leyka_Settings_Section('final', $stage->id, __('Good job!', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('You have successfully filled your data and can now proceed to the next step.', 'leyka'),
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // Receiver data Section - End

        // Diagnostic data Section:
        $stage = new Leyka_Settings_Stage('dd', __('Diagnostic data', 'leyka'));

        // The plugin usage stats collection step:
        $section = new Leyka_Settings_Section('plugin_stats', $stage->id, __('Diagnostic data', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('We ask you to confirm your agreement to send <strong>technical data</strong> to us, Teplitsa of Social technologies. It will allow us to consistently improve the plugin work as well as help you quickly resolve technical issues with it. These data will be used only by plugin developers and will not be shared with any third party.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'send_plugin_stats',
            'option_id' => 'send_plugin_stats',
            'show_title' => false,
        ]))->add_handler([$this, 'handle_plugin_stats_section'])
            ->add_to($stage);

        // The plugin usage stats collection - accepted:
        $section = new Leyka_Settings_Section('plugin_stats_accepted', $stage->id, __('Thank you!', 'leyka'), ['next_label' => __('Continue', 'leyka')]);
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __("Thanks a lot! Your data will help us very much. Now, let's get up and running your first fundraising campaign.", 'leyka'),
        ]))->add_to($stage);

        // The plugin usage stats collection - refused:
        $section = new Leyka_Settings_Section('plugin_stats_refused', $stage->id, __('Ehh...', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __("A pity that you have decided not to share data. If you change your mind, you may change these settings in the plugin settings. Let's get up and running your first campaign fundraising.", 'leyka'),
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;

        // Campaign data Section:
        $stage = new Leyka_Settings_Stage('cd', __('Campaign setup', 'leyka'));

        $init_campaign = get_transient('leyka_init_campaign_id') ?
            new Leyka_Campaign(get_transient('leyka_init_campaign_id')) : false;

        $section = new Leyka_Settings_Section('campaign_description', $stage->id, __('Your campaign description', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('Leyka fundraising is carried out using one or more campaigns, each is characterized by a title, a brief description, an image and a target amount. Set up your first campaign.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'campaign_title',
            'custom_setting_id' => 'campaign_title',
            'field_type' => 'text',
            'data' => [
                'title' => __('Campaign title', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., "For the authorized activities of the organization"', 'leyka'),
                'value' => $init_campaign ? $init_campaign->title : '',
                'comment' => __('A brief and clear description of a purpose for which funds are collected.', 'leyka'),
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'campaign-lead',
            'custom_setting_id' => 'campaign_short_description',
            'field_type' => 'textarea',
            'data' => [
                'title' => esc_html_x('Short description', '[of a campaign]', 'leyka'),
                'value' => $init_campaign ? $init_campaign->short_description : '',
                'comment' => __('A brief description of the campaign concisely explains why donors should donate.', 'leyka'),
            ],
        ]))->add_block(new Leyka_Container_Block([
            'id' => 'complex-row-2',
            'entry_width' => 0.5,
            'entries' => [
                new Leyka_Custom_Setting_Block([
                    'id' => 'campaign-target',
                    'custom_setting_id' => 'campaign_target',
                    'field_type' => 'text',
                    'data' => [
                        'title' => __('Target amount', 'leyka'),
                        'min' => 0,
                        'step' => 0.01,
                        'value' => $init_campaign && $init_campaign->target ? $init_campaign->target : '',
                        'show_description' => false,
                        'placeholder' => __('Leave empty, if the target amount is unlimited', 'leyka'),
                        'mask' => "'alias': 'numeric', 'groupSeparator': ' ', 'autoGroup': true, 'allowMinus': false, 'rightAlign': false, 'removeMaskOnSubmit': true",
                    ],
                ]),
            ]
        ]))->add_handler([$this, 'handle_campaign_description_section'])
            ->add_to($stage);

        $section = new Leyka_Settings_Section('campaign_decoration', $stage->id, __('Campaign decoration', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('Select the campaign main photo and one of the possible donation forms templates. Both are very important for campaign perception by donors, and therefore to its success.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'campaign-decoration',
            'custom_setting_id' => 'campaign_decoration',
            'field_type' => 'custom_campaign_view',
            'keys' => ['campaign_thumbnail', 'campaign_template',],
            'rendering_type' => 'template',
        ]))->add_handler([$this, 'handle_campaign_decoration_section'])
            ->add_to($stage);

        $section = new Leyka_Settings_Section('donors_communication', $stage->id, __('Thanks to donor', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-1',
            'text' => __('Thank your donor. Below are settings of thankful email, that would be sent to every donor once donation is complete.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text-2',
            'text' => __('Later, in the plugin Settings, you can change the text on the "Thank you" page, which is displayed after the successful completion of donation.', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'email_from_name',
            'option_id' => 'email_from_name',
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'email_from',
            'option_id' => 'email_from',
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'email_thanks_text',
            'option_id' => 'email_thanks_text',
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // Campaign settings Section - End

        // Final Section:
        $stage = new Leyka_Settings_Stage('final', __('Setup completed', 'leyka'));

        $section = new Leyka_Settings_Section('campaign_completed', $stage->id, __('The campaign is set up', 'leyka'));
        $section->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'campaign-completed',
            'custom_setting_id' => 'campaign_completed',
            'field_type' => 'custom_campaign_completed',
            'rendering_type' => 'template',
        ]))->add_handler([$this, 'handle_campaign_completed_section'])
            ->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // Final Section - End

    }

    protected function _get_next_section_id(Leyka_Settings_Section $section_from = null, $return_full_id = true) {

        $section_from = $section_from && is_a($section_from, 'Leyka_Settings_Section') ? $section_from : $this->current_section;
        $next_section_full_id = false;

        /** @todo To many if-elses sucks - try some wrapping pattern here */
        if($section_from->stage_id === 'rd') {

            if($section_from->id === 'init') {
                $next_section_full_id = $section_from->stage_id.'-receiver_type';
            } else if($section_from->id === 'receiver_type') {

                $next_section_full_id = $this->_get_setting_value('receiver_legal_type') === 'legal' ?
                    $section_from->stage_id.'-receiver_legal_data' :
                    $section_from->stage_id.'-receiver_physical_data';

            } else if($section_from->id === 'receiver_legal_data') {
                $next_section_full_id = $section_from->stage_id.'-receiver_legal_bank_essentials';
            } else if($section_from->id === 'receiver_physical_data') {
                $next_section_full_id = $section_from->stage_id.'-receiver_physical_bank_essentials';
            } else if(stripos($section_from->id, 'bank_essentials')) {

                $next_section_full_id = $this->_get_setting_value('receiver_legal_type') === 'legal' ?
                    $section_from->stage_id.'-receiver_legal_terms_of_service' :
                    $section_from->stage_id.'-receiver_physical_terms_of_service';

            } else if(stripos($section_from->id, 'terms_of_service')) {

                $next_section_full_id = $this->_get_setting_value('receiver_legal_type') === 'legal' ?
                    $section_from->stage_id.'-receiver_legal_pd_terms' :
                    $section_from->stage_id.'-receiver_physical_pd_terms';

            } else if(stripos($section_from->id, 'pd_terms')) {
                $next_section_full_id = $section_from->stage_id.'-final';
            } else if($section_from->id === 'final') {
                $next_section_full_id = 'dd-plugin_stats';
            }

        } else if($section_from->stage_id === 'dd') {

            if($section_from->id === 'plugin_stats') {

                $next_section_full_id = $this->_get_setting_value('send_plugin_stats') === 'n' ?
                    $section_from->stage_id.'-plugin_stats_refused' :
                    $section_from->stage_id.'-plugin_stats_accepted';

            } else {
                $next_section_full_id = 'cd-campaign_description';
            }

        } else if($section_from->stage_id === 'cd') {

            if($section_from->id === 'campaign_description') {
                $next_section_full_id = $section_from->stage_id.'-campaign_decoration';
            } else if($section_from->id === 'campaign_decoration') {
                $next_section_full_id = $section_from->stage_id.'-donors_communication';
            } else if($section_from->id === 'donors_communication') {
                $next_section_full_id = 'final-campaign_completed';
            }

        } else if($section_from->stage_id === 'final') { // Final Section
            if($section_from->id === 'campaign_completed') {
                $next_section_full_id = 'final-campaign_completed'; // $next_section_full_id = 'final-init';

            }            
            //$next_section_full_id = true;
        }

        if( !!$return_full_id || !is_string($next_section_full_id) ) {
            return $next_section_full_id;
        } else {

            $next_section_full_id = explode('-', $next_section_full_id);

            return array_pop($next_section_full_id);

        }

    }

    protected function _init_navigation_data() {

        $this->_navigation_data = [
            [
                'stage_id' => 'rd',
                'title' => __('Your data', 'leyka'),
                'url' => '',
                'sections' => [
                    [
                        'section_id' => 'receiver_type',
                        'title' => __('Donations receiver', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'receiver_data',
                        'title' => __('Your data', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'receiver_bank_essentials',
                        'title' => __('Bank essentials', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'receiver_terms_of_service',
                        'title' => __('Terms of service', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'receiver_pd_terms',
                        'title' => __('Personal data', 'leyka'),
                        'url' => '',
                    ],
                ],
            ],
            [
                'stage_id' => 'dd',
                'title' => __('Diagnostic data', 'leyka'),
                'url' => '',
            ],
            [
                'stage_id' => 'cd',
                'title' => __('Campaign setup', 'leyka'),
                'url' => '',
                'sections' => [
                    [
                        'section_id' => 'campaign_description',
                        'title' => __('Main data', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'campaign_decoration',
                        'title' => __('Campaign decoration', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'donors_communication',
                        'title' => __('Thanks to donor', 'leyka'),
                        'url' => '',
                    ],
                ],
            ],
            [
                'stage_id' => 'final',
                'title' => __('Setup completed', 'leyka'),
                'url' => '',
            ],
        ];

    }

    protected function _get_section_navigation_position($section_full_id = false) {

        $section_full_id = $section_full_id ? trim(esc_attr($section_full_id)) : $this->get_current_section()->full_id;

        switch($section_full_id) {
            case 'rd-init': return 'rd';
            case 'rd-receiver_type': return $section_full_id;
            case 'rd-receiver_legal_data':
            case 'rd-receiver_physical_data':
                return 'rd-receiver_data';
            case 'rd-receiver_legal_bank_essentials':
            case 'rd-receiver_physical_bank_essentials':
                return 'rd-receiver_bank_essentials';
            case 'rd-receiver_legal_terms_of_service':
            case 'rd-receiver_physical_terms_of_service':
                return 'rd-receiver_terms_of_service';
            case 'rd-receiver_legal_pd_terms':
            case 'rd-receiver_physical_pd_terms':
                return 'rd-receiver_pd_terms';
            case 'rd-final': return 'rd--';
            case 'dd-plugin_stats': return 'dd';
            case 'dd-plugin_stats_accepted':
            case 'dd-plugin_stats_refused':
                return 'dd--';
            case 'cd-campaign_description':
            case 'cd-campaign_decoration':
            case 'cd-donors_communication':
                return $section_full_id;
            case 'cd-campaign_completed':
                return 'cd--';
            case 'final-campaign_completed':
                return $section_full_id;
            case 'final-init': return 'final--';
            default: return false;
        }

    }

    public function get_submit_data($component = null) {

        $section = $component && is_a($component, 'Leyka_Settings_Section') ? $component : $this->current_section;
        $submit_settings = [
            'next_label' => __('Save & continue', 'leyka'),
            'next_url' => true,
            'prev' => __('Back to the previous step', 'leyka'),
        ];

        if($section->next_label) {
            $submit_settings['next_label'] = $section->next_label;
        }

        if($section->stage_id === 'rd' && $section->id === 'init') {

            $submit_settings['next_label'] = __("Let's go!", 'leyka');
            $submit_settings['prev'] = false; // Means that the Wizard shouldn't display the back link

        } else if($section->stage_id === 'dd' && in_array($section->id, ['plugin_stats_accepted', 'plugin_stats_refused',])) {

            $submit_settings['additional_label'] = __('Go to the Dashboard', 'leyka');
            $submit_settings['additional_url'] = admin_url('admin.php?page=leyka');

        } else if($section->stage_id === 'final') {

            $submit_settings['next_label'] = __('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function section_init() {

        // Receiver type Section prerequisites - show "legal" receiver type only if receiver country is set:
        if($this->_get_setting_value('receiver_country') === '-') {
            add_filter('leyka_option_info-receiver_legal_type', function($option_data){

                unset($option_data['list_entries']['legal']);

                return $option_data;

            });
        }

        // If init campaign is not set or deleted on the campaign decoration step, return to the campaign data step:
        if($this->get_current_section()->id === 'campaign_decoration') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handle_settings_go_back('cd-campaign_description');
            }

        } else if($this->get_current_section()->id === 'campaign_completed') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handle_settings_go_back('cd-campaign_description');
            }

            $empty_bank_essentials_options = leyka_get_empty_bank_essentials_options();
            if($empty_bank_essentials_options) { // Show the fields
                foreach($empty_bank_essentials_options as $option_id) {
                    $this->get_current_section()->add_block(new Leyka_Option_Block([
                        'id' => $option_id,
                        'option_id' => $option_id,
                    ]));
                }
            } else { // Enable the Quittance PM if there are no other active ones

                $pm_data = leyka_options()->opt('pm_available');
                $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

                if( !$pm_data ) {

                    $pm_data[] = $quittance_pm_full_id;
                    leyka_options()->opt('pm_available', $pm_data);

                    $pm_order = [];
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

    public function handle_plugin_stats_section(array $section_settings) {

        if(empty($section_settings['send_plugin_stats'])) {
            return false;
        }

        update_option('leyka_plugin_stats_option_needs_sync', time());
        $stats_option_synch_res = leyka_sync_plugin_stats_option();

        if( !leyka_options()->opt('plugin_stats_sync_enabled') ) {
            return true;
        } else if(is_wp_error($stats_option_synch_res) && leyka_options()->opt('plugin_debug_mode')) {
            // DO NOT return WP_Error in production!
            return $stats_option_synch_res; // We should save the option and go to the next step anyway
        } else {
            return delete_option('leyka_plugin_stats_option_needs_sync')
                && update_option('leyka_plugin_stats_option_sync_done', time());
        }

    }

    public function handle_campaign_description_section(array $section_settings) {

        $init_campaign_params = [
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_title' => trim(esc_attr(wp_strip_all_tags($section_settings['campaign_title']))),
            'post_excerpt' => trim(esc_textarea($section_settings['campaign_short_description'])),
            'post_content' => '',
        ];

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
            return new WP_Error('init_campaign_insertion_error', __('Error while creating the campaign', 'leyka'));
        }

        update_post_meta($campaign_id, 'campaign_target', (float)$section_settings['campaign_target']);

        if( !$existing_campaign_id ) {

            $this->_add_history_entry(['campaign_id' => $campaign_id]);
            set_transient('leyka_init_campaign_id', $campaign_id);

        }

        return true;

    }

    public function handle_campaign_decoration_section(array $section_settings) {

        // Publish the init campaign:
        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = [];

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', __('Campaign ID is wrong or missing', 'leyka'));
        }

        if(
            $campaign->post_type !== 'publish' &&
            is_wp_error(wp_update_post(['ID' => $campaign_id, 'post_status' => 'publish']))
        ) {
            return new WP_Error('init_campaign_publishing_error', __('Error when publishing the campaign', 'leyka'));
        }

        return $errors ? $errors : true;

    }

    public function handle_init_section(array $section_settings) {

        $section_settings['receiver_country'] = empty($section_settings['receiver_country'])
            || $section_settings['receiver_country'] === '-' ?
            'ru' : $section_settings['receiver_country'];

        switch($section_settings['receiver_country']) {
            case 'ua':

                leyka_options()->opt('currency_main', 'uah');
                leyka_options()->opt('receiver_legal_type', 'legal');
                wp_redirect(admin_url('admin.php?page=leyka_settings&stage=beneficiary'));
                exit;

            case 'by':

                leyka_options()->opt('currency_main', 'byn');
                leyka_options()->opt('receiver_legal_type', 'legal');
                wp_redirect(admin_url('admin.php?page=leyka_settings&stage=beneficiary'));
                exit;

            case 'ru':
            default:
        }

        return true;

    }

    public function handle_campaign_completed_section(array $section_settings) {

        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = [];

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', __('Campaign ID is wrong or missing', 'leyka'));
        }

        // Enable the Quittance PM, if all the needed fields are filled:
        if(leyka_are_bank_essentials_set()) {

            $pm_data = leyka_options()->opt('pm_available');
            $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

            if( !in_array($quittance_pm_full_id, $pm_data) ) {

                $pm_data[] = $quittance_pm_full_id;
                leyka_options()->opt('pm_available', $pm_data);

                $pm_order = [];
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

    public function handle_save_options(array $section_settings) {

        $errors = [];

        foreach($section_settings as $option_id => $value) {
            leyka_save_option(preg_replace("/^leyka_/", "", $option_id));
        }

        return $errors ? $errors : true;

    }

}