<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Init_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'init';
        $this->_title = esc_html__('Leyka setup Wizard', 'leyka');

        $options = array(
            'org_actual_address' => array(
                'type' => 'textarea',
                'title' => esc_html__('Organization actual address', 'leyka'),
            ),
            'org_actual_address_differs' => array(
                'type' => 'checkbox',
                'title' => esc_html__('The actual address is different from the legal', 'leyka'),
            ),
        );

        foreach($options as $option_name => $params) {
            if( !leyka_options()->option_exists($option_name) ) {
                leyka_options()->add_option($option_name, $params['type'], $params);
            }
        }

    }

    protected function _loadCssJs() {

        // ...
        parent::_loadCssJs();

    }

    protected function _setSections() {

        // Receiver's data Section:
        $section = new Leyka_Settings_Section('rd', esc_html__('Your data', 'leyka'));

        // 0-step:
        $step = new Leyka_Settings_Step('init',  $section->id, esc_html__('Hello!', 'leyka'), array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__("You installed the Leyka plugin, all that's left is to set it up. We will guide you through all the steps and help with tips.", 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_country',
            'option_id' => 'receiver_country',
        )))->addTo($section);

        // Receiver type step:
        $step = new Leyka_Settings_Step('receiver_type', $section->id, esc_html__('Donations receiver', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('You need to determine on whose behalf you will collect donations. As NGOs (non-profit organization) - a legal entity, or as an ordinary citizen - a physical person. Grassroots initiatives will be easier to collect on behalf of the individual (but remember about taxes). That being said, legal entities will have more opportunities to collect.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver_type',
            'option_id' => 'receiver_legal_type',
            'show_title' => false,
        )))->addTo($section);

        // Legal receiver type - org. data step:
        $step = new Leyka_Settings_Step('receiver_legal_data', $section->id, esc_html__('Organization data', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('These data we will use for the templates of Terms of service contracts and accounting documents for your donors. All data can be found in the founding documents of your organization.', 'leyka'),
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
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'org_address',
            'option_id' => 'org_address',
            'custom_setting_id' => 'org_address',
            'field_type' => 'textarea',
            'data' => array(
                'title' => esc_html__('The organization official address', 'leyka'),
                'keys' => array('org_address',),
                'value' => leyka_options()->opt('org_address'),
                'required' => esc_html__('The field value is required', 'leyka'),
            ),
            'show_description' => false,
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'org_actual_address_differs',
            'custom_setting_id' => 'org_actual_address_differs',
            'field_type' => 'checkbox',
            'data' => array(
                'title' => esc_html__('The actual address is different from the legal', 'leyka'),
                'keys' => array(),
                'field_classes' => array('single-control'),
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'org_actual_address',
            'option_id' => 'org_actual_address',
            'custom_setting_id' => 'org_actual_address',
            'field_type' => 'textarea',
            'data' => array(
                'title' => '',
                'value' => leyka_options()->opt('org_actual_address'),
                'keys' => array('org_actual_address',),
            ),
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
            'text' => esc_html__('Contact person', 'leyka'),
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
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        // Physical receiver type - person's data step:
        $step = new Leyka_Settings_Step('receiver_physical_data', $section->id, esc_html__('Your data', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('We will use these data for accounting documents to your donors.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_full_name',
            'option_id' => 'person_full_name',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_email',
            'option_id' => 'tech_support_email',
            'title' => esc_html__('Contact email', 'leyka'),
            'required' => true,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_address',
            'option_id' => 'person_address',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_inn',
            'option_id' => 'person_inn',
        )))->addTo($section);

        // Legal receiver type - org. bank essentials step:
        $step = new Leyka_Settings_Step('receiver_legal_bank_essentials', $section->id, esc_html__('Bank essentials', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('These data needed for accounting documents, as well as to use bank order donations.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'org_bank_name',
            'option_id' => 'org_bank_name',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-2',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_bank_account',
                    'option_id' => 'org_bank_account',
                    'show_description' => false,
                )),
            ),
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-3',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_bank_corr_account',
                    'option_id' => 'org_bank_corr_account',
                    'show_description' => false,
                )),
            ),
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'org_bank_bic',
                    'option_id' => 'org_bank_bic',
                    'show_description' => false,
                )),
            ),
        )))->addTo($section);

        // Physical receiver type - person's bank essentials step:
        $step = new Leyka_Settings_Step('receiver_physical_bank_essentials', $section->id, esc_html__('Bank essentials', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('These data needed for accounting documents, as well as to use bank order donations.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_bank_name',
            'option_id' => 'person_bank_name',
            'show_description' => false,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_bank_account',
            'option_id' => 'person_bank_account',
            'show_description' => false,
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'person_bank_corr_account',
            'option_id' => 'person_bank_corr_account',
            'show_description' => false,
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-1',
            'entries' => array(
                new Leyka_Option_Block(array(
                    'id' => 'person_bank_bic',
                    'option_id' => 'person_bank_bic',
                    'show_description' => false,
                )),
            ),
        )))->addTo($section);

        // Legal receiver type - Terms of service step:
        $step = new Leyka_Settings_Step('receiver_legal_terms_of_service', $section->id, esc_html__('Terms of service', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('To comply with all the formalities you need to provide a Terms of service document to conclude a donation agreement. We have prepared a template option - please check it out. If necessary, adjust the document text. Text parts highlighted in blue are replaced automatically, but you can also change them. After completion of all of the changes, click "Save & continue".', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'terms_of_service_text',
        )))->addTo($section);

        // Physical receiver type - Terms of service step:
        $step = new Leyka_Settings_Step('receiver_physical_terms_of_service', $section->id, esc_html__('Terms of service', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('To comply with all the formalities you need to provide a Terms of service document to conclude a donation agreement. We have prepared a template option - please check it out. If necessary, adjust the document text. Text parts highlighted in blue are replaced automatically, but you can also change them. After completion of all of the changes, click "Save & continue".', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'terms_of_service_text',
            'option_id' => 'person_terms_of_service_text',
        )))->addTo($section);

        // Legal receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_legal_pd_terms', $section->id, esc_html__('Terms of personal data usage', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => esc_html__('As part of your fundraising you will collect the donors personal data. "Consent to the processing of personal data" - binding instrument on the federal law FZ-152. We have prepared the text of the agreement template, you can edit it to your needs. Text parts highlighted in blue are replaced automatically, but you can also change them. All personal data is stored on your site and will not be sent anywhere.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'pd_terms_text',
        )))->addTo($section);

        // Physical receiver type - personal data terms step:
        $step = new Leyka_Settings_Step('receiver_physical_pd_terms', $section->id, esc_html__('Terms of personal data usage', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => sprintf(__('WARNING! We strongly recommend you to revise this Terms text and fill the field with your own value according to the organization personal data policy. Read more about it: %s', 'leyka'), leyka_get_pd_usage_info_links()),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => esc_html__('As part of your fundraising you will collect the donors personal data. "Consent to the processing of personal data" - binding instrument on the federal law FZ-152. We have prepared the text of the agreement template, you can edit it to your needs. Text parts highlighted in blue are replaced automatically, but you can also change them. All personal data is stored on your site and will not be sent anywhere.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'pd_terms_text',
            'option_id' => 'person_pd_terms_text',
        )))->addTo($section);

        // Section final (outro) step:
        $step = new Leyka_Settings_Step('final', $section->id, esc_html__('Good job!', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('You have successfully filled your data and can now proceed to the next step.', 'leyka'),
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Receiver data Section - End

        // Diagnostic data Section:
        $section = new Leyka_Settings_Section('dd', esc_html__('Diagnostic data', 'leyka'));

        // The plugin usage stats collection step:
        $step = new Leyka_Settings_Step('plugin_stats', $section->id, esc_html__('Diagnostic data', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => __('We ask you to confirm your agreement to send <strong>technical data</strong> to us, Teplitsa of Social technologies. It will allow us to consistently improve the plugin work as well as help you quickly resolve technical issues with it. These data will be used only by plugin developers and will not be shared with any third party.', 'leyka'),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'send_plugin_stats',
            'option_id' => 'send_plugin_stats',
            'show_title' => false,
        )))->addHandler(array($this, 'handlePluginStatsStep'))
            ->addTo($section);

        // The plugin usage stats collection - accepted:
        $step = new Leyka_Settings_Step('plugin_stats_accepted', $section->id, esc_html__('Thank you!', 'leyka'), array('next_label' => esc_html__('Continue', 'leyka')));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__("Thanks a lot! Your data will help us very much. Now, let's get up and running your first fundraising campaign.", 'leyka'),
        )))->addTo($section);

        // The plugin usage stats collection - refused:
        $step = new Leyka_Settings_Step('plugin_stats_refused', $section->id, esc_html__('Ehh...', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__("A pity that you have decided not to share data. If you change your mind, you may change these settings in the plugin settings. Let's get up and running your first campaign fundraising.", 'leyka'),
        )))->addTo($section);

        $this->_sections[$section->id] = $section;

        // Campaign data Section:
        $section = new Leyka_Settings_Section('cd', esc_html__('Campaign setup', 'leyka'));

        $init_campaign = get_transient('leyka_init_campaign_id') ?
            new Leyka_Campaign(get_transient('leyka_init_campaign_id')) : false;

        $step = new Leyka_Settings_Step('campaign_description', $section->id, esc_html__('Your campaign description', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Leyka fundraising is carried out using one or more campaigns, each is characterized by a title, a brief description, an image and a target amount. Set up your first campaign.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign_title',
            'custom_setting_id' => 'campaign_title',
            'field_type' => 'text',
            'data' => array(
                'title' => esc_html__('Campaign title', 'leyka'),
                'required' => true,
                'placeholder' => esc_html__('E.g., "For the authorized activities of the organization"', 'leyka'),
                'value' => $init_campaign ? $init_campaign->title : '',
                'comment' => esc_html__('A brief and clear description of a purpose for which funds are collected.', 'leyka'),
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-lead',
            'custom_setting_id' => 'campaign_short_description',
            'field_type' => 'textarea',
            'data' => array(
                'title' => esc_html_x('Short description', '[of a campaign]', 'leyka'),
                'value' => $init_campaign ? $init_campaign->short_description : '',
                'comment' => esc_html__('A brief description of the campaign concisely explains why donors should donate.', 'leyka'),
            ),
        )))->addBlock(new Leyka_Container_Block(array(
            'id' => 'complex-row-2',
            'entry_width' => 0.5,
            'entries' => array(
                new Leyka_Custom_Setting_Block(array(
                    'id' => 'campaign-target',
                    'custom_setting_id' => 'campaign_target',
                    'field_type' => 'text',
                    'data' => array(
                        'title' => esc_html__('Target amount', 'leyka'),
                        'min' => 0,
                        'step' => 0.01,
                        'value' => $init_campaign && $init_campaign->target ? $init_campaign->target : '',
                        'show_description' => false,
                        'placeholder' => esc_html__('Leave empty, if the target amount is unlimited', 'leyka'),
                        'mask' => "'alias': 'numeric', 'groupSeparator': ' ', 'autoGroup': true, 'allowMinus': false, 'rightAlign': false, 'removeMaskOnSubmit': true",
                    ),
                )),
            )
        )))->addHandler(array($this, 'handleCampaignDescriptionStep'))
            ->addTo($section);

        $step = new Leyka_Settings_Step('campaign_decoration', $section->id, esc_html__('Campaign decoration', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Select the campaign main photo and one of the possible donation forms templates. Both are very important for campaign perception by donors, and therefore to its success.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-decoration',
            'custom_setting_id' => 'campaign_decoration',
            'field_type' => 'custom_campaign_view',
            'keys' => array('campaign_thumbnail', 'campaign_template',),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleCampaignDecorationStep'))
            ->addTo($section);

        $step = new Leyka_Settings_Step('donors_communication', $section->id, esc_html__('Thanks to donor', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-1',
            'text' => esc_html__('Once a donor has made his donation, it is considered a good practive to show him a page with words of gratitude and to send an thanking email. Below you can edit what the donor will receive in such email. The "Sender" field means "from whom" (most of the time it is a name of your organization). The "Sender e-mail" field means a return address that donor will see. Finally, you can verbalise your sincere gratitude to your donor in the text of an email. Note the special tags you can use for automatic substitution.', 'leyka'),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text-2',
            'text' => esc_html__('Later, in the plugin Settings, you can change the text on the "Thank you" page, which is displayed after the successful completion of donation.', 'leyka'),
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

        $this->_sections[$section->id] = $section;
        // Campaign settings Section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', esc_html__('Setup completed', 'leyka'));

        $step = new Leyka_Settings_Step('campaign_completed', $section->id, esc_html__('The campaign is set up', 'leyka'));
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'campaign-completed',
            'custom_setting_id' => 'campaign_completed',
            'field_type' => 'custom_campaign_completed',
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleCampaignCompletedStep'))
        ->addTo($section);

//        $step = new Leyka_Settings_Step('init', $section->id, esc_html__('Congratulations!', 'leyka'), array('header_classes' => 'greater',));
//        $step->addBlock(new Leyka_Text_Block(array(
//            'id' => 'step-intro-text',
//            'text' => esc_html__('You have successfully completed the Leyka setup Wizard.', 'leyka'),
//        )))->addTo($section);
//
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
                //$next_step_full_id = $step_from->section_id.'-campaign_completed';
                $next_step_full_id = 'final-campaign_completed';
            }

        }
        else if($step_from->section_id === 'final') { // Final Section
            if($step_from->id === 'campaign_completed') {
                $next_step_full_id = 'final-campaign_completed'; // $next_step_full_id = 'final-init';

            }            
            //$next_step_full_id = true;
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
                'title' => esc_html__('Your data', 'leyka'),
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'receiver_type',
                        'title' => esc_html__('Donations receiver', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_data',
                        'title' => esc_html__('Your data', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_bank_essentials',
                        'title' => esc_html__('Bank essentials', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_terms_of_service',
                        'title' => esc_html__('Terms of service', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'receiver_pd_terms',
                        'title' => esc_html__('Personal data', 'leyka'),
                        'url' => '',
                    ),
                ),
            ),
            array(
                'section_id' => 'dd',
                'title' => esc_html__('Diagnostic data', 'leyka'),
                'url' => '',
            ),
            array(
                'section_id' => 'cd',
                'title' => esc_html__('Campaign setup', 'leyka'),
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'campaign_description',
                        'title' => esc_html__('Main data', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'campaign_decoration',
                        'title' => esc_html__('Campaign decoration', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'donors_communication',
                        'title' => esc_html__('Thanks to donor', 'leyka'),
                        'url' => '',
                    ),
                ),
            ),
            array(
                'section_id' => 'final',
                'title' => esc_html__('Setup completed', 'leyka'),
                'url' => '',
            ),
        );

    }

    protected function _getStepNavigationPosition($step_full_id = false) {

        $step_full_id = $step_full_id ? trim(esc_attr($step_full_id)) : $this->getCurrentStep()->full_id;

        switch($step_full_id) {
            case 'rd-init': return 'rd';
            case 'rd-receiver_type': return $step_full_id;
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
                return $step_full_id;
            case 'cd-campaign_completed':
                return 'cd--';
            case 'final-campaign_completed':
                return $step_full_id;
            case 'final-init': return 'final--';
            default: return false;
        }

    }

    public function getSubmitData($component = null) {

        $step = $component && is_a($component, 'Leyka_Settings_Step') ? $component : $this->current_step;
        $submit_settings = array(
            'next_label' => esc_html__('Save & continue', 'leyka'),
            'next_url' => true,
            'prev' => esc_html__('Back to the previous step', 'leyka'),
        );

        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'rd' && $step->id === 'init') {

            $submit_settings['next_label'] = esc_html__("Let's go!", 'leyka');
            $submit_settings['prev'] = false; // Means that the Wizard shouln't display the back link

        } else if($step->section_id === 'dd' && in_array($step->id, array('plugin_stats_accepted', 'plugin_stats_refused',))) {

            $submit_settings['additional_label'] = esc_html__('Go to the Dashboard', 'leyka');
            $submit_settings['additional_url'] = admin_url('admin.php?page=leyka');

        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = esc_html__('Go to the Dashboard', 'leyka');
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

    public function handlePluginStatsStep(array $step_settings) {

        if(empty($step_settings['send_plugin_stats'])) {
            return false;
        }

        update_option('leyka_plugin_stats_option_needs_sync', time());
        $stats_option_synch_res = leyka_sync_plugin_stats_option();

        if(is_wp_error($stats_option_synch_res)) {
            return $stats_option_synch_res;
        } else {
            return delete_option('leyka_plugin_stats_option_needs_sync')
                && update_option('leyka_plugin_stats_option_sync_done', time());
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
            return new WP_Error('init_campaign_insertion_error', esc_html__('Error while creating the campaign', 'leyka'));
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
            return new WP_Error('wrong_init_campaign_id', esc_html__('Campaign ID is wrong or missing', 'leyka'));
        }

        if(
            $campaign->post_type !== 'publish' &&
            is_wp_error(wp_update_post(array('ID' => $campaign_id, 'post_status' => 'publish')))
        ) {
            return new WP_Error('init_campaign_publishing_error', esc_html__('Error when publishing the campaign', 'leyka'));
        }

        return $errors ? $errors : true;

    }

    public function handleCampaignCompletedStep(array $step_settings) {

        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = array();

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', esc_html__('Campaign ID is wrong or missing', 'leyka'));
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

    public function handleSaveOptions(array $step_settings) {

        $errors = array();

        foreach($step_settings as $option_id => $value) {
            leyka_save_option(preg_replace("/^leyka_/", "", $option_id));
        }

        return !empty($errors) ? $errors : true;

    }

}
