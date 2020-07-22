<?php if( !defined('WPINC') ) die;

class Leyka_By_Options_Allocator extends Leyka_Ru_Options_Allocator {

    protected static $_instance;

    protected function _get_main_currency_options_tabs() {

        $main_currency_id = leyka_options()->opt_safe('currency_main');
        $main_currency_info = leyka_get_currencies_full_info($main_currency_id);

        return array(
            $main_currency_id.'_currency' => array(
                'title' => $main_currency_info['title'],
                'sections' => array(
                    array(
                        'title' => '',
                        'options' => array(
                            "currency_{$main_currency_id}_label", "currency_{$main_currency_id}_min_sum",
                            "currency_{$main_currency_id}_max_sum", "currency_{$main_currency_id}_flexible_default_amount",
                            "currency_{$main_currency_id}_fixed_amounts",
                        ),
                    ),
                ),
        ),);
    }

    public function get_beneficiary_options() {
        return array(
            array('section' => array(
                'name' => 'receiver_country',
                'title' => __('Country', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('receiver_country', 'currency_main',),
            ),),
            array('section' => array(
                'name' => 'beneficiary_org_name',
                'title' => __('Organization official name and contacts', 'leyka'),
                'description' => __('These data we will use for reporting documents to your donors. All data can be found in documents', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    'org_full_name', 'org_short_name', 'org_face_fio_ip', 'org_face_position', 'org_address',
                    'org_state_reg_number', 'org_kpp', 'org_inn',
                )
            )),
            array('section' => array(
                'name' => 'beneficiary_person_name',
                'title' => __('Your data', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('person_full_name', 'person_address', 'person_inn',)
            )),
            array('section' => array(
                'name' => 'org_bank_essentials',
                'title' => __('Organization bank essentials', 'leyka'),
                'description' => __('Data needed for accounting documents, as well as to connect the payment with receipt', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('org_bank_name', 'org_bank_account', 'org_bank_corr_account', 'org_bank_bic',)
            )),
            array('section' => array(
                'name' => 'person_bank_essentials',
                'title' => __("Person's bank essentials", 'leyka'),
                'description' => __('Data needed for accounting documents, as well as to connect the payment with receipt', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    'person_bank_name', 'person_bank_account', 'person_bank_corr_account', 'person_bank_bic',
                )
            )),
            array('section' => array(
                'name' => 'terms_of_service',
                'title' => __('Offer', 'leyka'),
                'description' => __('To comply with all the formalities, you need to provide an offer to conclude a donation agreement. We have prepared a template option. Please check.', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('terms_of_service_text', 'agree_to_terms_link_action',)
            ),),
            array('section' => array(
                'name' => 'person_terms_of_service',
                'title' => __('Offer', 'leyka'),
                'description' => __('To comply with all the formalities, you need to provide an offer to conclude a donation agreement. We have prepared a template option. Please check.', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('person_terms_of_service_text', 'agree_to_terms_link_action',)
            ),),
            array('section' => array(
                'name' => 'terms_of_pd',
                'title' => __('Agreement on personal data', 'leyka'),
                'description' => __('<ul><li>In the framework of fundraising you will collect the personal data of recipients of donations.</li>
<li>"Consent to data processing" - binding instrument on the law FZ-152.</li>
<li>We have prepared the text of the agreement template, but you can edit it to your needs.</li>
<li>All personal data is stored on your site and will not be sent.</li></ul>', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('pd_terms_text', 'agree_to_pd_terms_link_action',)
            )),
            array('section' => array(
                'name' => 'change_receiver_legal_type',
                'title' => __('Change of ownership form', 'leyka'),
                'description' => __('<span class="attention">WARNING!</span> These actions may affect the performance of the plugin.', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array('receiver_legal_type',)
            )),
        );
    }

}