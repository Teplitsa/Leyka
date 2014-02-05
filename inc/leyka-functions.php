<?
/**
 * Leyka Functions
 **/

function leyka_current_user_has_role($role, $user_id = false) {

    $user = is_numeric($user_id) ? get_userdata( $user_id ) : wp_get_current_user();

    if( !$user )
        return false;

    return in_array($role, (array)$user->roles);
}

/** Get WP pages list as an array. Used mainly to form a dropdowns. */
function leyka_get_pages_list() {

    $query = new WP_Query(array('post_type' => 'page', 'posts_per_page' => -1));

    $pages = array(0 => __('Website main page', 'leyka'),);
    foreach($query->get_posts() as $page) {
        $pages[$page->ID] = $page->post_title;
    }

    return $pages;
}

/** A callback for the default gateway select field. */
function leyka_get_gateways_pm_list() {

    $options = array();
    foreach(leyka_get_pm_list() as $pm) {
        $gateway_title = leyka_get_gateway_by_id($pm->gateway_id)->title;
        $options[$pm->full_id] = $pm->label.($gateway_title == $pm->label ? '' : ' ('.$gateway_title.')');
    }

    return $options;
}

function leyka_get_default_email_from() {
    return 'donations@'.trim(str_replace('http://', '', home_url('', 'http')), '/');
}

/** DM is for "donation manager" */
function leyka_get_default_dm_list() {
    return get_bloginfo('admin_email').',';
}

function leyka_get_default_success_page() {

    $query = new WP_Query(array('post_type' => 'page', 'name' => 'thank-you-for-your-donation', 'posts_per_page' => 1));
    $query = $query->get_posts();

    return $query ? reset($query)->ID : 0;
}

function leyka_get_success_page_url() {

    $url = leyka_options()->opt('success_page') ?
        get_permalink(leyka_options()->opt('success_page')) : site_url();
    
    if( !$url ) // It can be in case when "last posts" is selected for homepage
        $url = site_url();
    
    return $url;
}

function leyka_get_default_failure_page() {

    $query = new WP_Query(array('post_type' => 'page', 'name' => 'sorry-donation-failure', 'posts_per_page' => 1));
    $query = $query->get_posts();

    return $query ? reset($query)->ID : 0;
}

function leyka_get_failure_page_url() {

    $url = leyka_options()->opt('failure_page') ?
        get_permalink(leyka_options()->opt('failure_page')) : site_url();

    if( !$url ) // It can be in case when "last posts" is selected for homepage
        $url = site_url();
    
    return $url;
}

/** Get a list of donation form templates as an array. */
function leyka_get_form_templates_list() {

    $list = array();
    foreach(leyka()->get_templates() as $template) {

//        $template_id = str_replace('.php', '', end(explode('-', $template['basename'])));
        $name = $template['name'] == __($template['name'], 'leyka') ?
            $template['name'] : __($template['name'], 'leyka');
        $description = $template['description'] == __($template['description'], 'leyka') ?
            $template['description'] : __($template['description'], 'leyka');

        $list[$template['id']] = $name.' ('.mb_strtolower($description).')';
    }

    return $list;
}

function leyka_get_active_currencies() {
    return array(
        'rur' => array(
            'label' => leyka_options()->opt('currency_rur_label'),
            'top' => leyka_options()->opt('currency_rur_max_sum'),
            'bottom' => leyka_options()->opt('currency_rur_min_sum'),
            'amount_settings' => array(
                'flexible' => leyka_options()->opt('currency_rur_flexible_default_amount'),
                'fixed' => leyka_options()->opt('currency_rur_fixed_amounts')
            ),
        ),
        'usd' => array(
            'label' => leyka_options()->opt('currency_usd_label'),
            'top' => leyka_options()->opt('currency_usd_max_sum'),
            'bottom' => leyka_options()->opt('currency_usd_min_sum'),
            'amount_settings' => array(
                'flexible' => leyka_options()->opt('currency_usd_flexible_default_amount'),
                'fixed' => leyka_options()->opt('currency_usd_fixed_amounts')
            ),
        ),
        'eur' => array(
            'label' => leyka_options()->opt('currency_eur_label'),
            'top' => leyka_options()->opt('currency_eur_max_sum'),
            'bottom' => leyka_options()->opt('currency_eur_min_sum'),
            'amount_settings' => array(
                'flexible' => leyka_options()->opt('currency_eur_flexible_default_amount'),
                'fixed' => leyka_options()->opt('currency_eur_fixed_amounts')
            ),
        ),
    );
}

function leyka_get_currency_data($currency_code) {

    $currecies = leyka_get_active_currencies();

    return isset($currecies[$currency_code]) ? $currecies[$currency_code] : false;
}

/** Get possible leyka_donation post type's status list as an array. */
function leyka_get_donation_status_list() {
    return apply_filters('leyka_donation_statuses', array(
        'submitted' => _x('Submitted', '«Submitted» donation status', 'leyka'),
        'funded' => _x('Funded', '«Completed» donation status', 'leyka'),
        'refunded' => _x('Refunded', '«Refunded» donation status', 'leyka'),
        'failed' => _x('Failed', '«Failed» donation status', 'leyka'),
        'trash' => _x('Trash', '«Deleted» donation status', 'leyka'),
    ));
}