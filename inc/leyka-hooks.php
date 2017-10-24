<?php if( !defined('WPINC') ) die;
/** Different service hooks functions */

/** Terms text filters */
function leyka_terms_of_service_text($text) {
    return wpautop(str_replace(
        array(
            '#LEGAL_NAME#',
            '#LEGAL_FACE#',
            '#LEGAL_FACE_RP#',
            '#LEGAL_FACE_POSITION#',
            '#LEGAL_ADDRESS#',
            '#STATE_REG_NUMBER#',
            '#KPP#',
            '#INN#',
            '#BANK_ACCOUNT#',
            '#BANK_NAME#',
            '#BANK_BIC#',
            '#BANK_CORR_ACCOUNT#',
        ),
        array(
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_face_fio_ip'),
            leyka_options()->opt('org_face_fio_rp'),
            leyka_options()->opt('org_face_position'),
            leyka_options()->opt('org_address'),
            leyka_options()->opt('org_state_reg_number'),
            leyka_options()->opt('org_kpp'),
            leyka_options()->opt('org_inn'),
            leyka_options()->opt('org_bank_account'),
            leyka_options()->opt('org_bank_name'),
            leyka_options()->opt('org_bank_bic'),
            leyka_options()->opt('org_bank_corr_account'),
        ),
        $text
    ));
}
add_filter('leyka_terms_of_service_text', 'leyka_terms_of_service_text');

function leyka_service_terms_page_text($page_content) {
    return leyka_options()->opt('terms_of_service_page') && is_page(leyka_options()->opt('terms_of_service_page')) ?
        apply_filters('leyka_terms_of_service_text', do_shortcode($page_content)) : $page_content;
}
add_filter('the_content', 'leyka_service_terms_page_text');

function leyka_terms_of_pd_usage_text($text) {
    return wpautop(str_replace(
        array(
            '#LEGAL_NAME#',
            '#LEGAL_ADDRESS#',
            '#SITE_URL#',
            '#PD_TERMS_PAGE_URL#',
            '#ADMIN_EMAIL#',
        ),
        array(
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_address'),
            home_url(),
            leyka_get_pd_terms_page_url(),
            get_option('admin_email'),
        ),
        $text
    ));
}
add_filter('leyka_terms_of_pd_usage_text', 'leyka_terms_of_pd_usage_text');

function leyka_terms_of_pd_usage_page_text($page_content) {
    return leyka_options()->opt('pd_terms_page') && is_page(leyka_options()->opt('pd_terms_page')) ?
        apply_filters('leyka_terms_of_pd_usage_text', do_shortcode($page_content)) : $page_content;
}
add_filter('the_content', 'leyka_terms_of_pd_usage_page_text');