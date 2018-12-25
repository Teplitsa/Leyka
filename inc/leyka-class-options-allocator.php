<?php if( !defined('WPINC') ) die;

class Leyka_Options_Allocator extends Leyka_Singleton {

    protected static $_instance = null;
    protected $_tabs = array();

    protected function __construct() {
        $this->_tabs = apply_filters('leyka_settings_tabs', array(
            'payment'     => __('Payment options', 'leyka'),
            'beneficiary' => __('My data', 'leyka'),
            'view'        => __('Campaign view', 'leyka'),
            'email'       => __('Notifications', 'leyka'),
            'technical'   => __('Tech settings', 'leyka'),
            'additional'  => __('Misc', 'leyka'),
            //'currency'    => __('Currency', 'leyka'),
            //'commission'  => __('Commission', 'leyka'),
        ));
    }

    public function get_tabs() {
        return $this->_tabs;
    }

    public function get_tab_options($tab_name) {

        if(empty($this->_tabs[$tab_name])) {
            return false;
        }

        $options_allocated = array();
        switch($tab_name) {
            case 'beneficiary':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'beneficiary_org_name',
                        'title' => __("Organization's official name and contacts", 'leyka'),
                        'description' => __('These data we will use for reporting documents to your donors. All data can be found in documents', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'org_full_name', 'org_face_fio_ip', 'org_face_fio_rp', 'org_face_position', 'org_address',
                            'org_state_reg_number', 'org_kpp', 'org_inn', 
                        )
                    )),
                    array('section' => array(
                        'name' => 'beneficiary_person_name',
                        'title' => __("Your data", 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'person_full_name', 'person_address', 'person_inn', 
                        )
                    )),
                    array('section' => array(
                        'name' => 'org_bank_essentials',
                        'title' => __("Organization's bank essentials", 'leyka'),
                        'description' => __('Data needed for accounting documents, as well as to connect the payment with receipt', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'org_bank_name', 'org_bank_account', 'org_bank_corr_account',
                            'org_bank_bic',
                        )
                    )),
                    array('section' => array(
                        'name' => 'person_bank_essentials',
                        'title' => __("Person bank essentials", 'leyka'),
                        'description' => __('Data needed for accounting documents, as well as to connect the payment with receipt', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'person_bank_name', 'person_bank_account', 'person_bank_corr_account',
                            'person_bank_bic',
                        )
                    )),
                    array('section' => array(
                        'name' => 'terms_of_service',
                        'title' => __('Offer', 'leyka'),
                        'description' => __('To comply with all the formalities, you need to provide an offer to conclude a donation agreement. We have prepared a template option. Please check.', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'terms_of_service_text', 'agree_to_terms_link_action',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'person_terms_of_service',
                        'title' => __('Offer', 'leyka'),
                        'description' => __('To comply with all the formalities, you need to provide an offer to conclude a donation agreement. We have prepared a template option. Please check.', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'person_terms_of_service_text', 'agree_to_terms_link_action',
                        )
                    ),),
                    
                    array('section' => array(
                        'name' => 'terms_of_pd',
                        'title' => __('Agreement on personal data', 'leyka'),
                        'description' => __('<ul><li>In the framework of fundraising you will collect the personal data of recipients of donations.</li>
<li>"Consent to data processing" - binding instrument on the law FZ-152.</li>
<li>We have prepared the text of the agreement template, but you can edit it to your needs.</li>
<li>All personal data is stored on your site and will not be sent.</li></ul>', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'pd_terms_text', 'agree_to_pd_terms_link_action',
                        )
                    )),
                    array('section' => array(
                        'name' => 'change_receiver_legal_type',
                        'title' => __('Change of ownership form', 'leyka'),
                        'description' => __('<span class="attention">WARNING!</span> These actions may affect the performance of the plugin.', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'receiver_legal_type',
                        )
                    )),
                );
                break;

            case 'payment': break; // Custom settings page templates used

            case 'currency':
                $options_allocated = array(
                );
                break;

            case 'email':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'email_from',
                        'title' => __('All emails options', 'leyka'),
                        'description' => __('After the donor has made his donation, it is considered good form to show him the page with Gratitude and send a letter', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array('email_from_name', 'email_from',)
                    ),),
                    array('section' => array(
                        'name' => 'email_thanks',
                        'title' => __('Grateful emails options', 'leyka'),
                        'description' => __('Dispatched after making a one-time donation.', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'email_thanks_title', 'email_thanks_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'email_recurring_init_thanks',
                        'title' => __('Recurring init grateful emails options', 'leyka'),
                        'description' => __('Dispatched after the activation of the recurrent subscriptions', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'email_recurring_init_thanks_title', 'email_recurring_init_thanks_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'email_recurring_payment_thanks',
                        'title' => __('Recurring payment grateful emails options', 'leyka'),
                        'description' => __('Dispatched after each recurrent payment', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'email_recurring_ongoing_thanks_title', 'email_recurring_ongoing_thanks_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'email_campaign_target_reaching',
                        'title' => __('Campaign target reaching emails options', 'leyka'),
                        'description' => __('After the completion of the campaign, sent to all donors', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'send_donor_emails_on_campaign_target_reaching', 'email_campaign_target_reaching_title',
                            'email_campaign_target_reaching_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'notify_staff',
                        'title' => __('Staff notifications options', 'leyka'),
                        'description' => __('Once a donor has made his donation, considered good form to show him a page of thanks and send a letter', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'notify_donations_managers', 'notify_managers_on_recurrents', 'donations_managers_emails',
                            'email_notification_title', 'email_notification_text',
                        )
                    ),),
                );
                break;

            case 'view':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'global_campaign_templates_options',
                        'title' => __('Campaign page template', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'donation_form_template', 'donation_sum_field_type', 'donation_form_mode',
                            'scale_widget_place', 'donations_history_under_forms', 'show_campaign_sharing',
                            'show_success_widget_on_success', 'show_failure_widget_on_failure',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'revo_template_options',
                        'title' => __('Revo template', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'revo_template_slider_max_sum', 'revo_template_show_thumbnail', 'revo_donation_complete_button_text',
                            'revo_template_show_donors_list',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'donation_form_options',
                        'title' => __('Donation form options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'donation_submit_text', 'show_donation_comment_field', 'donation_comment_max_length',
//                            'show_donation_comments_in_frontend',
                        )
                    ),),
                    
                    // currency
                    array('section' => array(
                        'name' => 'currency_rates',
                        'title' => __('Currency rates options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'auto_refresh_currency_rates', 'currency_rur2usd', 'currency_rur2eur',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'rur_currency',
                        'title' => __('RUR currency options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'currency_rur_label', 'currency_rur_min_sum', 'currency_rur_max_sum', 
                            'currency_rur_flexible_default_amount', 'currency_rur_fixed_amounts',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'usd_currency',
                        'title' => __('USD currency options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'currency_usd_label', 'currency_usd_min_sum', 'currency_usd_max_sum',
                            'currency_usd_flexible_default_amount', 'currency_usd_fixed_amounts',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'eur_currency',
                        'title' => __('EUR currency options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'currency_eur_label', 'currency_eur_min_sum', 'currency_eur_max_sum',
                            'currency_eur_flexible_default_amount', 'currency_eur_fixed_amounts',
                        )
                    ),),
                    
                );
                break;

            case 'commission':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'payment_operators_commission_options',
                        'title' => __('Payments operators commission', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'commission',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'donations_total_amount_usage_options',
                        'title' => __('Total amount usage', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'admin_donations_list_display', 'widgets_total_amount_usage', /*'archive_page_total_amount_usage',*/
                        )
                    ),),
                );
                break;
            
            case 'technical':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'technical_support',
                        'title' => __('Technical support', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'tech_support_email',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'upload_l10n',
                        'title' => __('Load translation', 'leyka'),
                        'is_default_collapsed' => false,
                        'action_button' => array( 'title' => __('Download', 'leyka'), 'id' => 'upload-l10n-button'),
                        'options' => array(
                            'lang2upload',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'stats_connections',
                        'title' => __('Statistics connection', 'leyka'),
                        'description' => __('Connect to statistics to send plugin data to us, Teplitsa of Social technologies. It will allow us to consistently improve the plugin work as well as help you quickly resolve technical issues with it. These data will be used only by plugin developers and will not be shared with any third party.', 'leyka'),
                        'is_default_collapsed' => false,
                        'action_button' => array( 'title' => __('Connect statistics', 'leyka'), 'id' => 'connect-stats-button'),
                        'options' => array(
                            'send_plugin_stats',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'plugin_deletion',
                        'title' => __('Deleting pluging data', 'leyka'),
                        'description' => __('<span class="attention">ATTENTION!</span> Action when removing a plugin', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array('delete_plugin_options', 'delete_plugin_data',)
                    ),),
                );
                break;
            
            case 'additional':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'terms_of_service',
                        'title' => __('Terms of donation service options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'agree_to_terms_needed', 'agree_to_terms_text_text_part', 'agree_to_terms_text_link_part',
                            'terms_agreed_by_default', 'terms_of_service_page',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'terms_of_pd',
                        'title' => __('Terms of personal data usage options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'agree_to_pd_terms_needed', 'agree_to_pd_terms_text_text_part',
                            'agree_to_pd_terms_text_link_part', 'pd_terms_agreed_by_default', 'pd_terms_page',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'misc',
                        'title' => __('Additional', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'send_donor_thanking_emails',
                            'success_page', 'failure_page', 'load_scripts_if_need', 'donors_data_editable', 'revo_thankyou_text',
                            'revo_thankyou_email_result_text'
                        )
                    ),),
                );
                break;

            default:
        }

        return apply_filters("leyka_{$tab_name}_options_allocation", $options_allocated);

    }
}

/** @return Leyka_Options_Allocator */
function leyka_opt_alloc() {
    return Leyka_Options_Allocator::get_instance();
}