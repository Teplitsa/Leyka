<?php if( !defined('WPINC') ) die;

class Leyka_Options_Allocator {

    private static $_instance = null;
    protected $_tabs = array();

    public static function instance() {

        if(empty(self::$_instance))
            self::$_instance = new self;

        return self::$_instance;
    }

    private function __construct() {

        $this->_tabs = apply_filters('leyka_settings_tabs', array(
            'beneficiary' => __('Beneficiary', 'leyka'),
            'payment'     => __('Payment options', 'leyka'),
            'currency'    => __('Currency', 'leyka'),
            'email'       => __('Email', 'leyka'),
            'view'        => __('View', 'leyka'),
            'commission'  => __('Commission', 'leyka'),
            'additional'  => __('Misc', 'leyka'),
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
                        'name' => 'org_name',
                        'title' => __("Organization's official name and contacts", 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'org_full_name', 'org_face_fio_ip', 'org_face_fio_rp', 'org_face_position', 'org_address',
                        )
                    )),
                    array('section' => array(
                        'name' => 'org_bank_essentials',
                        'title' => __("Organization's bank essentials", 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'org_state_reg_number', 'org_kpp', 'org_inn', 'org_bank_account', 'org_bank_name',
                            'org_bank_bic', 'org_bank_corr_account',
                        )
                    )),
                );
                break;

            case 'payment':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'payment_common',
                        'title' => __('Common options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array('pm_available', 'pm_order',)
                    ),),
                );
                break;

            case 'currency':
                $options_allocated = array(
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

            case 'email':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'email_from',
                        'title' => __('All emails options', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array('email_from_name', 'email_from',)
                    ),),
                    array('section' => array(
                        'name' => 'email_thanks',
                        'title' => __('Grateful emails options', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'send_donor_thanking_emails', 'email_thanks_title', 'email_thanks_text',
                            'email_recurring_init_thanks_title', 'email_recurring_init_thanks_text',
                            'email_recurring_ongoing_thanks_title', 'email_recurring_ongoing_thanks_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'email_campaign_target_reaching',
                        'title' => __('Campaign target reaching emails options', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'send_donor_emails_on_campaign_target_reaching', 'email_campaign_target_reaching_title',
                            'email_campaign_target_reaching_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'email_notify',
                        'title' => __('Website personnel notifications options', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'notify_donations_managers', 'notify_managers_on_recurrents', 'donations_managers_emails',
                            'email_notification_title', 'email_notification_text', 'tech_support_email',
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
                        )
                    ),),
                    array('section' => array(
                        'name' => 'donation_form_options',
                        'title' => __('Donation form options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'donation_submit_text', 'show_donation_comment_field', 'donation_comment_max_length',
                            'show_donation_comments_in_frontend',
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

            case 'additional':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'terms_of_service',
                        'title' => __('Terms of donation service options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'agree_to_terms_needed', 'agree_to_terms_text_text_part', 'agree_to_terms_text_link_part',
                            'terms_of_service_text', 'terms_agreed_by_default',
                            'agree_to_terms_link_action', 'terms_of_service_page',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'terms_of_pd',
                        'title' => __('Terms of personal data usage options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'agree_to_pd_terms_needed', 'agree_to_pd_terms_text_text_part',
                            'agree_to_pd_terms_text_link_part', 'pd_terms_text', 'pd_terms_agreed_by_default',
                            'agree_to_pd_terms_link_action', 'pd_terms_page',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'misc',
                        'title' => __('Additional', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'success_page', 'failure_page', 'load_scripts_if_need', 'donors_data_editable', 'revo_thankyou_text',
                            'revo_thankyou_email_result_text'
                        )
                    ),),
                    array('section' => array(
                        'name' => 'plugin_deletion',
                        'title' => __('Plugin data deletion', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array('delete_plugin_options', 'delete_plugin_data',)
                    ),),
                );
                break;

            default:
        }

        return apply_filters("leyka_{$tab_name}_options_allocation", $options_allocated);

    }
}

function leyka_opt_alloc() {
    return Leyka_Options_Allocator::instance();
}