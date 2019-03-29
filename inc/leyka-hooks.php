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

/**
 * @param $classes array
 * @return array
 */
function leyka_star_body_classes($classes) {
    if(!empty(get_query_var('leyka-screen'))) {
        $classes[] = 'leyka-screen-' . get_query_var('leyka-screen');
    }
    
    $campaign_id = null;
    if( is_singular(Leyka_Campaign_Management::$post_type) ) {
        $campaign_id = get_post()->ID;
    }
    elseif(is_page(leyka_options()->opt('success_page')) || is_page(leyka_options()->opt('failure_page'))) {
        $campaign_id = leyka_campaign_id_from_query_arg();
    }
    
    if($campaign_id) {
        $campaign = leyka_get_validated_campaign($campaign_id);
        $campaign_type = get_post_meta($campaign_id, 'campaign_type', true);
        
        if($campaign && $campaign_type == 'persistent' && $campaign->template == 'star') {
            $pos = array_search('leyka_campaign-template-default', $classes);
            if($pos !== false) {
                array_splice($classes, $pos, 1);
            }
            
            $classes[] = 'leyka_campaign-template-persistent';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'leyka_star_body_classes');

/**
 * @param $request
 * @param $query WP_Query
 * @return mixed
 */
function leyka_star_suppress_main_query($request, $query){
    if($query->is_main_query() && get_query_var('leyka-screen')) {
        return false;
    } else {
        return $request;
    }

}
add_filter('posts_request', 'leyka_star_suppress_main_query', 10, 2);

/**
 * @param $title_parts array
 * @return array
 */
function leyka_set_donor_account_page_title($title_parts) {
    $leyka_screen = get_query_var('leyka-screen');
    
    if(empty($leyka_screen)) {
        return $title_parts;
    }
    
    switch($leyka_screen) {
        case 'account':
            $title_parts['title'] = esc_html__('Donor account', 'leyka');
            break;
        case 'login':
            $title_parts['title'] = esc_html__('Sign in donor account', 'leyka');
            break;
        case 'reset-password':
            $title_parts['title'] = esc_html__('Reset account password', 'leyka');
            break;
        case 'unsubscribe-campaigns':
            $title_parts['title'] = esc_html__('Unsubscribe persistent campaign', 'leyka');
            break;
        default:
    }
    
    return $title_parts;
}
add_filter('document_title_parts', 'leyka_set_donor_account_page_title', 9999, 1);

function leyka_yoast_seo_title_workaround($title) {
    $leyka_screen = get_query_var('leyka-screen');
    
    if(empty($leyka_screen)) {
        return $title;
    }
    
    return '';
}
add_filter('pre_get_document_title', 'leyka_yoast_seo_title_workaround', 999, 1);
