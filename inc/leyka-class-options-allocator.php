<?php
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
            'payment' => __('Payment Options', 'leyka'),
            'currency' => __('Currency Options', 'leyka'),
            'email' => __('Email Options', 'leyka'),
            'additional' => __('Additional Options', 'leyka'),
        ));
    }

    public function get_tabs() {
        return $this->_tabs;
    }

    public function get_tab_options($tab_name) {
        if(empty($this->_tabs[$tab_name]))
            return false;

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
                        'options' => array('pm_available', /*'default_pm', 'donation_purpose_text',*/)
                    ),),
                );
                break;

            case 'currency':
                $options_allocated = array(
//                    'currency_position',
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
                        'options' => array('email_thanks_title', 'email_thanks_text',)
                    ),),
                    array('section' => array(
                        'name' => 'email_notify',
                        'title' => __('Website personnel notifications options', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            'notify_donations_managers', 'donations_managers_emails', 'email_notification_title',
                            'email_notification_text',
                        )
                    ),),
                );
                break;

            case 'additional':
                $options_allocated = array(
                    array('section' => array(
                        'name' => 'template_options',
                        'title' => __('Donation forms template', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'donation_form_template', 'donation_sum_field_type', 'donation_form_mode'
                        )
                    ),),
                    array('section' => array(
                        'name' => 'terms_of_service',
                        'title' => __('Terms of donation service options', 'leyka'),
                        'is_default_collapsed' => false,
                        'options' => array(
                            'argee_to_terms_needed', 'agree_to_terms_text', 'terms_of_service_text',
                        )
                    ),),
                    array('section' => array(
                        'name' => 'misc',
                        'title' => __('Miscellaneous', 'leyka'),
                        'is_default_collapsed' => true,
                        'options' => array(
                            /*'test_mode_on',*/ 'success_page', 'failure_page', /*'default_donation_status', 
                            'donate_submit_text',*/
                        )
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