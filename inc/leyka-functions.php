<?php if( !defined('WPINC') ) die;

/**
 * Leyka functions and template tags, irrelevant to a donation form.
 **/

if( !function_exists('mb_substr') ) {
    function mb_substr($str, $start, $length = null) {
        return substr($str, $start, $length);
    }
}

if( !function_exists('mb_ucfirst') ) {
    function mb_ucfirst($str) {
        return mb_strtoupper(mb_substr($str, 0, 1)).mb_substr($str, 1);
    }
}

if( !function_exists('mb_strtolower') ) {
    function mb_strtolower($str) {
        return strtolower($str);
    }
}

if( !function_exists('mb_strtoupper') ) {
    function mb_strtoupper($str) {
        return strtoupper($str);
    }
}

if( !function_exists('array_key_first') ) {
    function array_key_first(array $array) {

        foreach($array as $key => $unused) {
            return $key;
        }
        return NULL;

    }
}

if( !function_exists('array_key_last') ) {
    function array_key_last(array $array) {
        return $array ? key(array_slice($array, -1)) : null;
    }
}

if( !function_exists('leyka_is_phone_number') ) {
    function leyka_is_phone_number($value) {
        return preg_match('/^[0-9\+\-\. ]{10,}$/i', $value);
    }
}

if( !function_exists('leyka_strip_string_by_words') ) {
    function leyka_strip_string_by_words($string, $length = 350, $strip_tags_shortcodes = true) {

        if( !!$strip_tags_shortcodes ) {
            $string = strip_tags(strip_shortcodes($string));
        }

        if(mb_strlen($string) <= $length || stripos($string, ' ') === false) {
            return $string;
        }

        $characters_count = 0;
        $result_string = array();
        foreach(explode(' ', $string) as $word) {

            $characters_count += mb_strlen($word);
            if($characters_count <= $length) {
                $result_string[] = $word;
            } else {
                break;
            }

        }

        return implode(' ', $result_string);
    }
}

if( !function_exists('leyka_string_has_rus_chars')) {
    /**
     * @param $text string
     * @return boolean True if given string contains at least 1 cyrillic character, false otherwise.
     */
    function leyka_string_has_rus_chars($text) {
        return preg_match('/[А-Яа-яЁё]/u', $text);
    }
}

if( !function_exists('leyka_maybe_encode_hostname_to_punycode') ) {
    /**
     * @param $url string
     * @return string
     */
    function leyka_maybe_encode_hostname_to_punycode($url) {

        $hostname = explode('/', str_replace(array('http://', 'https://'), '', $url));
        $hostname = reset($hostname);

        if(leyka_string_has_rus_chars($hostname)) {

            require_once LEYKA_PLUGIN_DIR.'/lib/class-punycode.php';
            return str_replace($hostname, Punycode::encodeHostName($hostname), $url);

        } else {
            return $url;
        }

    }
}
// require_once LEYKA_PLUGIN_DIR.'/lib/class-punycode.php';

if( !function_exists('leyka_set_html_content_type') ) {
    function leyka_set_html_content_type() {
        return 'text/html';
    }
}

function leyka_user_has_role($role, $is_only_role = false, $user = false) {

    if($user && is_numeric($user)) {
        $user = get_userdata($user);
    } else if( !$user || !is_a($user, 'WP_User') ) {
        $user = wp_get_current_user();
    }

    if( !$user ) {
        return false;
    }

    return !!$is_only_role ?
        (array)$user->roles == array($role) :
        in_array($role, (array)$user->roles);

}

/**
 * @param $donation mixed
 * @return Leyka_Donation|false A donation object, if parameter is valid in one way or another; false otherwise.
 */
function leyka_get_validated_donation($donation) {

    if(is_numeric($donation) && (int)$donation > 0) {
        $donation = new Leyka_Donation((int)$donation);
    } else if(is_a($donation, 'WP_Post')) {
        $donation = new Leyka_Donation($donation);
    } elseif( !is_a($donation, 'Leyka_Donation') ) {
        return false;
    }

    return $donation ? $donation : false;

}

/**
 * @param $user int|string|WP_User|Leyka_Donor
 * @return WP_User|WP_Error
 */
function leyka_get_validated_user($user) {

    if(is_int($user) || is_string($user)) {

        if(absint($user) > 0) {
            $user = get_user_by('id', (int)$user);
        } else {
            $user = get_user_by('email', esc_sql($user));
        }

        if( !$user ) {
            return new WP_Error(__('Incorrect Donor identification data', 'leyka'));
        }

    } else if(is_a($user, 'Leyka_Donor')) {
        $user = get_user_by('id', $user->id);
    } else if( !is_a($user, 'WP_User') ) {
        return new WP_Error(__('Incorrect Donor identification data', 'leyka'));
    }

    return $user;
    
}

/**
 * @param $campaign mixed
 * @return Leyka_Campaign|false A Leyka_Campaign instance if parameter is valid in one way or another; false otherwise.
 */
function leyka_get_validated_campaign($campaign) {

    if(is_numeric($campaign) && (int)$campaign > 0) {
        $campaign = get_post((int)$campaign);
    }

    if(is_a($campaign, 'WP_Post') && $campaign->post_type === Leyka_Campaign_Management::$post_type) {
        $campaign = new Leyka_Campaign($campaign);
    } else if( !is_a($campaign, 'Leyka_Campaign') ) {
        return false;
    }

    return $campaign ? $campaign : false;

}

/** Get WP pages list as an array. Used mainly to form a dropdowns. */
function leyka_get_pages_list() {

    global $wpdb;

    $params = apply_filters('leyka_pages_list_query', array('post_status' => 'publish', 'post_type' => 'page'));
    foreach($params as $name => &$value) {
        $value = "`$name` = '$value'";
    }
    $res = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE ".implode(' AND ', $params));

    $pages = array(0 => __('Website main page', 'leyka'),);
    foreach($res as $page) {
        $pages[$page->ID] = $page->post_title;
    }

    return $pages;

}

/**
 * A callback for the default gateway select field.
 *
 * @param $gateway_id string|false
 * @return array
 */
function leyka_get_gateways_pm_list($gateway_id = false) {

    $options = array();
    foreach(leyka_get_pm_list(null, false, false) as $pm) {

        if($gateway_id && $pm->gateway_id !== $gateway_id) {
            continue;
        }

        $gateway_title = leyka_get_gateway_by_id($pm->gateway_id)->title;
        $options[$pm->full_id] = $pm->label_backend.($gateway_title == $pm->label_backend ? '' : ' ('.$gateway_title.')');

    }

    return $options;

}

function leyka_get_pd_usage_info_links() {
    return __('<a href="//te-st.ru/reports/personal-data-perm/" target="_blank" rel="noopener noreferrer">the Teplitsa article</a>.', 'leyka');
}

function leyka_get_default_email_from() {

    $domain = explode('/', trim(str_replace('http://', '', home_url('', 'http')), '/'));
    return 'no_reply@'.$domain[0];
}

/** DM is for "donation manager" */
function leyka_get_default_dm_list() {
    return get_bloginfo('admin_email').',';
}

function leyka_get_default_pd_terms_page() {

    $default_page = get_option('leyka_pd_terms_page');
    if($default_page) {
        return $default_page;
    }

    $page = get_posts(apply_filters('leyka_default_pd_terms_page_query', array(
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'post_type' => 'page',
        'post_name__in' => array('personal-data-usage-terms'),
        'posts_per_page' => 1,
    )));
    $page = reset($page);

    if($page) {

        if($page->post_status != 'publish') {
            wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish',));
        }

        $page = $page->ID;

    } else {

        // Can't use wp_insert_post due to some strange get_permastruct() notice, so insert the post manually:
        $page = leyka_manually_insert_page(array(
            'post_title' => leyka_tmp__('Terms of personal data usage'),
            'post_content' => leyka_tmp__('Terms of personal data usage full text. Use <br> for line-breaks.'),
            'post_name' => 'personal-data-usage-terms',
        ));
        if((int)$page > 0) {
            do_action('leyka_default_pd_terms_page_created', $page);
        }

    }

    if($page) {
        update_option('leyka_pd_terms_page', $page);
    }

    return $page ? $page : 0;

}

function leyka_get_pd_terms_page_url() {

    $url = leyka_options()->opt('pd_terms_page') ? get_permalink(leyka_options()->opt('pd_terms_page')) : home_url();

    if( !$url ) { // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    }

    return $url;

}

function leyka_get_default_service_terms_page() {

    $default_page = get_option('leyka_terms_of_service_page');
    if($default_page) {
        return $default_page;
    }

    $page = get_posts(apply_filters('leyka_default_service_terms_page_query', array(
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'post_type' => 'page',
        'post_name__in' => array('donation-service-terms'),
        'posts_per_page' => 1,
    )));
    $page = reset($page);

    if($page) {

        if($page->post_status != 'publish') {
            wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish',));
        }

        $page = $page->ID;

    } else {

        // Can't use wp_insert_post due to some strange get_permastruct() notice, so insert the post manually:
        $page = leyka_manually_insert_page(array(
            'post_title' => leyka_tmp__('Terms of donation service'),
            'post_content' => leyka_tmp__('Terms of donation service text. Use <br /> for line-breaks, please.'),
            'post_name' => 'donation-service-terms',
        ));
        if((int)$page > 0) {
            do_action('leyka_default_terms_of_service_page_created', $page);
        }

    }

    if($page) {
        update_option('leyka_terms_of_service_page', $page);
    }

    return $page ? $page : 0;

}

function leyka_get_terms_of_service_page_url() {

    $url = leyka_options()->opt('terms_of_service_page') ?
        get_permalink(leyka_options()->opt('terms_of_service_page')) : home_url();

    if( !$url ) { // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    }

    return $url;

}

function leyka_get_terms_of_pd_usage_page_url() {

    $url = leyka_options()->opt('pd_terms_page') ?
        get_permalink(leyka_options()->opt('pd_terms_page')) : home_url();

    if( !$url ) { // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    }

    return $url;

}

function leyka_get_default_success_page() {

    $default_page = get_option('leyka_success_page');
    if($default_page) {
        return $default_page;
    }

    $page = get_posts(apply_filters('leyka_default_success_page_query', array(
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'post_type' => 'page',
        'post_name__in' => array('thank-you-for-your-donation'),
        'posts_per_page' => 1,
    )));
    $page = reset($page);

    if($page) {

        if($page->post_status != 'publish') {
            wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish',));
        }

        $page = $page->ID;

    } else {

        // Can't use wp_insert_post due to some strange get_permastruct() notice, so insert the post manually:
        $page = leyka_manually_insert_page(array(
            'post_title' => leyka_tmp__('Thank you!'),
            'post_content' => leyka_tmp__('Your donation completed. We are grateful for your help.'),
            'post_name' => 'thank-you-for-your-donation',
        ));
        if((int)$page > 0) {
            do_action('leyka_default_success_page_created', $page);
        }

    }

    if($page) {
        update_option('leyka_success_page', $page);
    }

    return $page ? $page : 0;

}

function leyka_get_success_page_url() {

    $url = leyka_options()->opt('success_page') ? get_permalink(leyka_options()->opt('success_page')) : home_url();
    $url = $url ? $url : home_url(); // The case when "last posts" is selected for homepage

    $leyka_template_data = leyka_get_current_template_data();
    if( !empty($leyka_template_data['id']) ) {
        leyka_remembered_data('template_id', $leyka_template_data['id']);
    }

    return $url;

}

function leyka_get_default_failure_page() {

    $default_page = get_option('leyka_failure_page');
    if($default_page) {
        return $default_page;
    }

    $page = get_posts(apply_filters('leyka_default_failure_page_query', array(
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'post_type' => 'page',
        'post_name__in' => array('sorry-donation-failure'),
        'posts_per_page' => 1,
    )));
    $page = reset($page);

    if($page) {

        if($page->post_status != 'publish') {
            wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish',));
        }

        $page = $page->ID;

    } else {

        // Can't use wp_insert_post due to some strange get_permastruct() notice, so insert the post manually:
        $page = leyka_manually_insert_page(array(
            'post_title' => leyka_tmp__('Payment failure'),
            'post_content' => leyka_tmp__('We are deeply sorry, but for some technical reason we failed to receive your donation. Your money are intact. Please try again later!'),
            'post_name' => 'sorry-donation-failure',
        ));
        if((int)$page > 0) {
            do_action('leyka_default_failure_page_created', $page);
        }

    }

    if($page) {
        update_option('leyka_failure_page', $page);
    }

    return $page ? $page : 0;

}

function leyka_get_failure_page_url() {

    $url = leyka_options()->opt('failure_page') ? get_permalink(leyka_options()->opt('failure_page')) : home_url();
    $url = $url ? $url : home_url(); // The case when "last posts" is selected for homepage

    $leyka_template_data = leyka_get_current_template_data();
    if( !empty($leyka_template_data['id']) ) {
        leyka_remembered_data('template_id', $leyka_template_data['id']);
    }
    
    return $url;

}

/**
 * Get a list of donation form templates as an array.
 *
 * @deprecated From v3.5, use leyka()->get_templates().
 */
function leyka_get_form_templates_list() {

    $list = array();
    foreach(leyka()->get_templates() as $template) {

        if( !LEYKA_DEBUG && !empty($template['debug_only']) ) {
            continue;
        }

        $name = $template['name'] == __($template['name'], 'leyka') ? $template['name'] : __($template['name'], 'leyka');
        $description = $template['description'] == __($template['description'], 'leyka') ?
            $template['description'] : __($template['description'], 'leyka');

        $list[$template['id']] = $name.' ('.mb_strtolower($description).')';

    }

    return $list;

}

function leyka_get_currencies_data($currency_id = false) {

    $currencies = array(
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
    $currencies['rub'] = $currencies['rur'];

    return $currency_id && !empty($currencies[$currency_id]) ? $currencies[$currency_id] : $currencies;

}

/** @deprecated Use leyka_get_currencies_data($currency_id) instead. */
function leyka_get_active_currencies($currency_id = false) {
    return leyka_get_currencies_data($currency_id);
}

function leyka_get_currency_data($currency_code) {

    $currecies = leyka_get_currencies_data();

    return isset($currecies[$currency_code]) ? $currecies[$currency_code] : false;
}

function leyka_get_currency_label($currency_code = false) {

    $currency_code = empty($currency_code) ? 'rur' : mb_strtolower($currency_code);
    $currencies = leyka_get_currencies_data();

    return isset($currencies[$currency_code]['label']) ? $currencies[$currency_code]['label'] : false;

}


/**
 * Get possible leyka_donation post type's status list as an array.
 *
 * @param $with_hidden boolean
 * @return array
 */
function leyka_get_donation_status_list($with_hidden = true) {
    return leyka()->get_donation_statuses($with_hidden);
}

function leyka_get_donation_status_description($status) {

    $status_descriptions = leyka()->get_donation_statuses_descriptions();
    return $status && isset($status_descriptions[$status]) ? $status_descriptions[$status] : '';

}

function leyka_get_donation_types() {
    return leyka()->get_donation_types();
}

function leyka_get_donation_type_description($type) {

    $type = $type === 'rebill' ? 'recurring' : $type;
    $types = leyka()->get_donation_types_descriptions();

    return $type && isset($types[$type]) ? $types[$type] : '';

}

function leyka_get_pm_categories_list() {
    return apply_filters('leyka_pm_categories', array(
        'bank_cards' => esc_attr__('Bank cards', 'leyka'),
        'digital_currencies' => esc_attr__('Digital currrencies', 'leyka'),
        'online_banking' => esc_attr__('Online banking', 'leyka'),
        'mobile_payments' => esc_attr__('Mobile payments', 'leyka'),
        'misc' => esc_attr__('Miscellaneous', 'leyka'),
        'offline' => esc_attr__('Offline', 'leyka'),
    ));
}

function leyka_get_pm_category_label($category_id) {

    $category_id = esc_attr(trim($category_id));
    $categories_list = leyka_get_pm_categories_list();

    return $category_id && !empty($categories_list[$category_id]) ? $categories_list[$category_id] : false;

}

/**
 * Gateways filter categories main source
 * @return array
 */
function leyka_get_gateways_filter_categories_list() {
    return apply_filters('leyka_gateways_filter_categories', array(
        'legal' => esc_attr__('Legal persons', 'leyka'),
        'physical' => esc_attr__('Physical persons', 'leyka'),
        'recurring' => mb_ucfirst(esc_html_x('recurring', 'a "recurring donations" in one word (like "recurrings")', 'leyka')),
    ));
}

function leyka_get_filter_category_label($category_id) {

    $category_id = esc_attr(trim($category_id));
    $categories_list = leyka_get_gateways_filter_categories_list();

    return $category_id && !empty($categories_list[$category_id]) ? $categories_list[$category_id] : false;

}

/**
 * Gateway activation status labels
 * @return string
 */
function leyka_get_gateway_activation_status_label($activation_status) {

    $activation_status_labels = array(
        'active' => __('Active', 'leyka'),
        'inactive' => __('Inactive', 'leyka'),
        'activating' => __('Connection is in process', 'leyka'),
    );

    return $activation_status && !empty($activation_status_labels[$activation_status]) ?
        $activation_status_labels[$activation_status] : false;

}

/**
 * @param string $wizard_id
 * @return bool
 */
function leyka_wizard_started($wizard_id) {

    try {
        $wizard_controller = Leyka_Settings_Factory::get_instance()->get_controller($wizard_id);
    } catch(Exception $e) {
        return false;
    }

    return count($wizard_controller->history) > 0;

}

/**
 * @param $extension_id string
 * @return Leyka_Extension|false An extension object or false if none found.
 */
function leyka_get_extension_by_id($extension_id) {
    return Leyka_Extension::get_by_id($extension_id);
}

/**
 * @param Leyka_Extension $extension
 * @return string
 */
function leyka_get_extension_settings_url(Leyka_Extension $extension) {
    return $extension->get_settings_url();
}

/**
 * @param Leyka_Extension $extension
 * @return string|false A Wizard suffix or false if wizard unavailable for given extension.
 */
function leyka_extension_setup_wizard(Leyka_Extension $extension) {
    return $extension->wizard_id;
}

/**
 * Gateway receiver description.
 *
 * @param $receiver_types array Receiver types array.
 * @return string
 */
function leyka_get_receiver_description($receiver_types) {

    $type = count($receiver_types) > 1 ? 'all' : $receiver_types[0];

    $labels = array(
        'all' => esc_attr__('Legal & physical persons allowed as a receiver.', 'leyka'),
        'legal' => esc_attr__('Only legal persons allowed as a receiver.', 'leyka'),
        'physical' => esc_attr__('Only physical persons allowed as a receiver.', 'leyka'),
    );

    return $type && !empty($labels[$type]) ? $labels[$type] : '';
    
}

/**
 * Get all possible campaign target states.
 **/
function leyka_get_campaign_target_states_list() {
    return leyka()->get_campaign_target_states();
}

/**
 * Get campaign target - template tag
 * 
 * @var $campaign integer Campaign ID.
 * @return mixed Array of campaign target info, false if wrong campaign ID given, or int 0 if a campaign doesn't have a target.
 */
function leyka_get_campaign_target($campaign) {

    $campaign = (int)$campaign;
    if($campaign <= 0) {
        return false;
    }

    $campaign = new Leyka_Campaign($campaign);
    if( !$campaign->id ) {
        return false;
    }

    // Currently, target is always in RUB:
    return $campaign->target ? array('amount' => $campaign->target, 'currency' => 'rur',) : 0;

}

/**
 * Get campaign collected amount - template tag
 * 
 * @var $campaign integer Campaign ID.
 * @return mixed Array of campaign collected amount info, or false if wrong campaign ID given.
 */
function leyka_get_campaign_collections($campaign) {

    $campaign = (int)$campaign;
    if($campaign <= 0) {
        return false;
    }

    $campaign = new Leyka_Campaign($campaign);
    if( !$campaign->id ) {
        return false;
    }

    // Currently, collections are all in RUB:
    return array('amount' => $campaign->total_funded, 'currency' => 'rur',);

}

/**
 * Scale
 **/
function leyka_scale_compact($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }

    $target = (float)$campaign->target;
    if($target <= 0.0) {
        return;
    }

    $curr_label = leyka_get_currency_label('rur');

    $percentage = round(($campaign->total_funded/$target)*100);
	if($percentage > 100) {
		$percentage = 100;
    }?>

<div class="leyka-scale-compact">
    <div class="leyka-scale-scale">
        <div class="target">
            <div style="width:<?php echo $percentage;?>%" class="collected">&nbsp;</div>
        </div>
    </div>
    <div class="leyka-scale-label">
    <?php $target_f = number_format($target, ($target - round($target) > 0.0 ? 2 : 0), '.', ' ');
    $collected_f = number_format($campaign->total_funded, ($campaign->total_funded - round($campaign->total_funded) > 0.0 ? 2 : 0), '.', ' ');

    if($campaign->total_funded == 0) {
        printf(esc_html__('Needed %s %s', 'leyka'), '<b>'.$target_f.'</b>', $curr_label);
    } else {
        printf(esc_html__('Collected %s of %s %s', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);
    }?>
    </div>
</div>
<?php
}

function leyka_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }

    $target = (float)$campaign->target;
    $curr_label = leyka_get_currency_label('rur');
   
    if($target == 0) {
        return;
    }
    
    $percentage = round(($campaign->total_funded/$target)*100);
	$percentage = $percentage > 100 ? 100 : $percentage;?>

<div class="leyka-scale-ultra">
    <div class="leyka-scale-scale">
        <div class="target">
            <div style="width:<?php echo $percentage;?>%" class="collected">&nbsp;</div>
        </div>
    </div>
    <div class="leyka-scale-label">
        <span>

        <?php $target_f = number_format($target, ($target - round($target) > 0.0 ? 2 : 0), '.', ' ');
        $collected_f = number_format($campaign->total_funded, ($campaign->total_funded - round($campaign->total_funded) > 0.0 ? 2 : 0), '.', ' ');

        printf(esc_html_x('%s of %s %s', 'Label on ultra-compact scale', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);?>

        </span>
    </div>
</div>
<?php  
}

function leyka_fake_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }

    $curr_label = leyka_get_currency_label('rur');
    $collected_f = number_format($campaign->total_funded, ($campaign->total_funded - round($campaign->total_funded) > 0.0 ? 2 : 0), '.', ' ');?>

<div class="leyka-scale-ultra-fake">
    <div class="leyka-scale-scale">
        <div class="target"> </div>
    </div>
    <div class="leyka-scale-label"><span>
        <?php printf(_x('Collected: %s', 'Label on ultra-compact fake scale', 'leyka'), "<b>{$collected_f}</b> {$curr_label}");?>
    </span></div>
</div>

<?php
}

/** @return array An array of possible payment types with labels */
function leyka_get_payment_types_list() {
    return array(
        'single'     => esc_attr__('Single', 'leyka'),
        'rebill'     => esc_attr__('Recurrent (rebill)', 'leyka'),
        'correction' => esc_attr__('Correction', 'leyka')
    );
}

function leyka_get_payment_type_label($type) {

    if( !$type ) {
        return false;
    }

    $types = leyka_get_payment_types_list();

    return in_array($type, array_keys($types)) ? $types[$type] : false;

}

/**
 * Service function to get an actual rates from cbr.ru
 * @return array An assoc array of currency_code => it's rate to RUR
 */
function leyka_get_actual_currency_rates() {

    $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d.m.Y');
    $currencies = array();

    if(class_exists('XMLReader')) {

        function leyka_xml2assoc(XMLReader $xml) {

            $tree = null;
            while($xml->read()) {

                switch($xml->nodeType) {

                    case XMLReader::END_ELEMENT: return $tree;
                    case XMLReader::ELEMENT:
                        $node = array('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : leyka_xml2assoc($xml));
                        if($xml->hasAttributes) {
                            while($xml->moveToNextAttribute()) {
                                $node['attributes'][$xml->name] = $xml->value;
                            }
                        }
                        $tree[] = $node;
                        break;
                    case XMLReader::TEXT:
                    case XMLReader::CDATA:
                        $tree .= $xml->value;
                }
            }

            return $tree;
        }

        $xml = new XMLReader();
        if( @$xml->open($url) ) {

            $currencies_tmp = leyka_xml2assoc($xml);
            $xml->close();

            if( !empty($currencies_tmp[0]) ) {
                foreach($currencies_tmp[0]['value'] as $currency) {

                    $currency = $currency['value']; // Just to shorten this things a bit

                    $code = $currency[1]['value']; // USD, EUR etc.
                    $rate = (float)str_replace(',', '.', $currency[4]['value']);
                    if($code == 'USD' || $code == 'EUR') {
                        $currencies[$code] = $rate;
                    }
                }
            }
        }

    } else if(class_exists('DOMDocument')) {

        $xml = new DOMDocument();
        if( @$xml->load($url) ) {

            foreach($xml->documentElement->getElementsByTagName('Valute') as $item) {

                /** @var $item DOMElement */

                $currency = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
                if($currency == 'USD' || $currency == 'EUR') {
                    $currencies[$currency] = (float)str_replace(
                        ',', '.',
                        $item->getElementsByTagName('Value')->item(0)->nodeValue
                    );
                }
            }
        }
    }

    return $currencies;

}

function leyka_are_settings_complete($settings_tab) {

    $settings_complete = true;
    $tab_options = leyka_opt_alloc()->get_tab_options($settings_tab); // Specially to support PHP strict standards

    $receiver_legal_type = leyka_options()->opt_safe('receiver_legal_type');
    $exclude_legal_type_fields_regex = array('legal' => '/^person_/', 'physical' => '/^org_/',);
    
    foreach($tab_options as $option_section) {
        foreach($option_section['section']['options'] as $option_name) {
            if(empty($exclude_legal_type_fields_regex[$receiver_legal_type]) || preg_match($exclude_legal_type_fields_regex[$receiver_legal_type], $option_name)) {
                continue;
            }
            
            if(!leyka_options()->opt_safe($option_name) && leyka_options()->is_required($option_name) ) {
                $settings_complete = false;
                break;
            }
        }
    }
    
    return $settings_complete;

}

function leyka_is_min_payment_settings_complete() {

    $pm_list = leyka_get_pm_list(true, false, false);
    if( !$pm_list ) {
        return false;
    }

    $gateway_options_valid = array(); // Array of already validated gateways

    foreach($pm_list as $pm) { /** @var $pm Leyka_Payment_Method */

        $gateway = leyka_get_gateway_by_id($pm->gateway_id);

        if( !$pm || !$gateway ) {
            continue;
        }

        $min_settings_complete = true;
        foreach($pm->get_pm_options_names() as $option_name) {

            if( !leyka_options()->is_valid($option_name) ) {

                $min_settings_complete = false;
                break;
            }
        }

        if( !isset($gateway_options_valid[$gateway->id]) ) {

            foreach($gateway->get_options_names() as $option_name) {
                if( !leyka_options()->is_valid($option_name) ) {

                    $gateway_options_valid[$gateway->id] = false;
                    break;
                }
            }

            if( !isset($gateway_options_valid[$gateway->id]) ) {
                $gateway_options_valid[$gateway->id] = true;
            }
        }

        if($min_settings_complete && !empty($gateway_options_valid[$gateway->id])) {
            return true;
        }
    }

    return false;

}

function leyka_is_campaign_published() {

    global $wpdb;

    return $wpdb->get_var("SELECT COUNT(*)
      FROM $wpdb->posts
      WHERE post_type='".Leyka_Campaign_Management::$post_type."' AND post_status = 'publish' LIMIT 0,1"
    ) > 0;

}

function leyka_get_campaigns_list($params = array(), $simple_format = true) {

    $campaigns = get_posts(array_merge(array(
        'post_type' => Leyka_Campaign_Management::$post_type,
        'posts_per_page' => -1,
    ), $params));

    if( !!$simple_format ) { // Array of WP_Post objects

        $list = array();
        foreach($campaigns as $campaign) {

            $campaign = new Leyka_Campaign($campaign);
            $list[$campaign->id] = $campaign->title;

        }

        return $list;

    } else { // Simple assoc. array of ID => title

        foreach($campaigns as $campaign) {
            $campaign->post_title = htmlentities($campaign->post_title, ENT_QUOTES, 'UTF-8');
        }

        return $campaigns;

    }

}

function leyka_get_campaigns_select_default() {

    $default_campaign = get_transient('leyka_default_campaign_id'); // Default campaign ID cache

    if( !$default_campaign ) {

        $default_campaign = array_keys(
            leyka_get_campaigns_list(array('orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => 1,), true)
        );
        set_transient('leyka_default_campaign_id', reset($default_campaign));

    }

    return $default_campaign;

}

/** Default campaign ID cache invalidation */
function leyka_flush_cache_default_campaign_id($new_status, $old_status, WP_Post $campaign) {

    if(
        $campaign->post_type !== Leyka_Campaign_Management::$post_type ||
        ($old_status !== 'publish'  &&  $new_status !== 'publish')
    ) {
        return;
    }

    delete_transient('leyka_default_campaign_id');

}
add_action('transition_post_status', 'leyka_flush_cache_default_campaign_id', 10, 3);

function leyka_is_widget_active() {

    // is_active_widget() is not working for some reason, so emulate it:
    foreach(wp_get_sidebars_widgets() as $sidebar => $widgets) {
        foreach((array)$widgets as $widget) {
            if(stristr($widget, 'leyka_') !== false) {
                return true;
            }
        }
    }

    return false;

}

/** @return boolean */
function leyka_are_bank_essentials_set() {

    if(leyka_options()->opt('receiver_legal_type') === 'legal') {
        return !!leyka_options()->opt('org_full_name')
            && !!leyka_options()->opt('org_inn')
            && !!leyka_options()->opt('org_kpp')
            && !!leyka_options()->opt('org_bank_account')
            && !!leyka_options()->opt('org_bank_name')
            && !!leyka_options()->opt('org_bank_bic')
            && !!leyka_options()->opt('org_bank_corr_account')
            && !!leyka_options()->opt('org_state_reg_number');
    } else {
        return !!leyka_options()->opt('person_full_name')
            && !!leyka_options()->opt('person_inn')
            && !!leyka_options()->opt('person_bank_name')
            && !!leyka_options()->opt('person_bank_account')
            && !!leyka_options()->opt('person_bank_bic')
            && !!leyka_options()->opt('person_bank_corr_account');
    }

}

function leyka_get_empty_bank_essentials_options() {

    if(leyka_are_bank_essentials_set()) {
        return array();
    }

    $bank_essentials_options = leyka_options()->opt('receiver_legal_type') === 'legal' ?
        array('org_full_name', 'org_inn', 'org_kpp', 'org_bank_account', 'org_bank_name', 'org_bank_bic', 'org_bank_corr_account', 'org_state_reg_number') :
        array('person_full_name', 'person_inn', 'person_bank_name', 'person_bank_account', 'person_bank_bic', 'person_bank_corr_account',);

    $result = array();
    foreach($bank_essentials_options as $option_id) {
        if( !leyka_options()->opt($option_id) ) {
            $result[] = $option_id;
        }
    }

    return $result;

}

function leyka_is_campaign_link_in_menu() {

//    foreach(get_registered_nav_menus() as $menu_id => $menu_name) {
//        wp_get_nav_menu_items($menu_id);
//    }

    return false;

}

function leyka_get_shortcodes() {

    global $shortcode_tags;

    $leyka_shortcodes = array();

    foreach($shortcode_tags as $shortcode_tag => $function_name) {
        if(stripos($shortcode_tag, 'leyka') !== false) {
            $leyka_shortcodes[] = $shortcode_tag;
        }
    }

    return $leyka_shortcodes;

}

/** @return boolean True if at least one Leyka form is currently on the screen, false otherwise */
function leyka_form_is_screening($widgets_also = true) {

    if( !leyka_options()->opt('load_scripts_if_need') || apply_filters('leyka_form_is_screening', false)) {
        return true;
    }

    $template = get_page_template_slug();

    $content_has_shortcode = false;
    if(get_post()) {
        foreach(leyka_get_shortcodes() as $shortcode_tag) {
            if(has_shortcode(get_post()->post_content, $shortcode_tag)) {

                $content_has_shortcode = true;
                break;

            }
        }
    }

    return leyka()->form_is_screening ||
        is_singular(Leyka_Campaign_Management::$post_type) ||
        stristr($template, 'home-campaign_one') !== false ||
        stripos($template, 'leyka') !== false ||
        $content_has_shortcode ||
        ( !!$widgets_also ? leyka_is_widget_active() : false );

}

function leyka_revo_template_displayed() {

    $revo_displayed = false;

    if(is_singular(Leyka_Campaign_Management::$post_type)) {

        $campaign = new Leyka_Campaign(get_post());
        if($campaign->template == 'default') {

            $leyka_template_data = leyka_get_current_template_data();
            $revo_displayed = $leyka_template_data['id'] == 'revo';

        } else {
            $revo_displayed = $campaign->template == 'revo';
        }

    } else if(get_post() && has_shortcode(get_post()->post_content, 'leyka_inline_campaign')) {
        $revo_displayed = true;
    }

    return apply_filters('leyka_revo_template_displayed', $revo_displayed);

}

function leyka_modern_template_displayed() {

    $modern_template_displayed = false;
    $modern_templates = array('revo', 'star');

    $post = get_post();

    if(get_query_var('leyka-screen')) {
        $modern_template_displayed = true;
    } else if(is_singular(Leyka_Campaign_Management::$post_type)) {

        $campaign = new Leyka_Campaign(get_post());
        if($campaign->template === 'default') {

            $leyka_template_data = leyka_get_current_template_data();
            $modern_template_displayed = in_array($leyka_template_data['id'], $modern_templates);

        } else {
            $modern_template_displayed = in_array($campaign->template, $modern_templates);
        }

    } else if($post) {

        $content_has_shortcodes = false;
        foreach(leyka_get_shortcodes() as $shortcode_tag) {
            if(has_shortcode(get_post()->post_content, $shortcode_tag)) {

                $content_has_shortcodes = true;
                break;

            }
        }

        if($content_has_shortcodes) {
            $modern_template_displayed = true;
        } else if(
            has_shortcode($post->post_content, 'leyka_campaign_form')
            || has_shortcode($post->post_content, 'leyka_payment_form')
        ) {

            if(preg_match_all( '/'.get_shortcode_regex().'/s', $post->post_content, $matches)) {

                $attr_id_match = array();                
                foreach($matches[2] as $key => $value) {
                    if(in_array($value, array('leyka_campaign_form', 'leyka_payment_form'))) {

                        $get = str_replace(" ", "&" , $matches[3][$key] );
                        parse_str($get, $atts);
                        
                        if(array_key_exists('id', $atts)) {

                            $campaign_id = preg_match_all("/(\d+)/", $atts['id'], $attr_id_match);
                            $campaign_id = isset($attr_id_match[1][0]) ? (int)$attr_id_match[1][0] : 0;

                            if( !$campaign_id ) {
                                continue;
                            }
                            
                            $campaign = new Leyka_Campaign($campaign_id);
                            if($campaign && in_array($campaign->template, $modern_templates)) {

                                $modern_template_displayed = true;
                                break;

                            }

                        }
                    }
                }

            }
            
        }
    }
    
    return apply_filters('leyka_modern_template_displayed', $modern_template_displayed);

}

function leyka_persistent_campaign_donated() {

    $result = is_page(leyka_options()->opt('success_page')) || is_page(leyka_options()->opt('failure_page'));
    
    if($result) {

        $donation_id = leyka_remembered_data('donation_id');
        $donation = $donation_id ? new Leyka_Donation($donation_id) : null;
        $campaign_id = $donation ? $donation->campaign_id : null;
        $campaign = $campaign_id ? new Leyka_Campaign($campaign_id) : null;
        
        $result = $campaign && $campaign->campaign_type === 'persistent' && $campaign->template == 'star';

    }
    
    return $result;

}

function leyka_success_widget_displayed() {
    return leyka_options()->opt_template('show_success_widget_on_success') && is_page(leyka_options()->opt('success_page'));
}

function leyka_failure_widget_displayed() {
    return leyka_options()->opt_template('show_failure_widget_on_failure') && is_page(leyka_options()->opt('failure_page'));
}

function leyka_format_amount($amount) {

    if((int)$amount >= 0) {
        $amount_is_float = (float)$amount - (int)$amount > 0;
    } else {
        return false;
    }

    return number_format((float)$amount, $amount_is_float ? 2 : 0, '.', ' ');

}

function leyka_validate_donor_name($name) {
    return $name ? !preg_match('/[^\\x{0410}-\\x{044F}\w\s\-_\'\.]/iu', $name) : true;
}

function leyka_validate_email($email) {
    return $email ? preg_match("/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|expert|[a-z]+)|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i", $email) : true;
}

/** @return string URL of a current page, according to permalinks stucture setting. */
function leyka_get_current_url() {

    global $wp;
    return add_query_arg($wp->query_string, '', home_url($wp->request));

}

// For some reason wp_validate_redirect() aren't get defined in WP 3.6.1, so define it if needed:
if( !function_exists('wp_validate_redirect') ) {
    function wp_validate_redirect($location, $default = '') {

        $location = trim($location);

        // browsers will assume 'http' is your protocol, and will obey a redirect to a URL starting with '//'
        if(substr($location, 0, 2) == '//') {
            $location = 'http:' . $location;
        }

        // In php 5 parse_url may fail if the URL query part contains http://, bug #38143
        $test = ($cut = strpos($location, '?')) ? substr($location, 0, $cut) : $location;

        $lp  = parse_url($test);

        // Give up if malformed URL
        if ( false === $lp )
            return $default;

        // Allow only http and https schemes. No data:, etc.
        if ( isset($lp['scheme']) && !('http' == $lp['scheme'] || 'https' == $lp['scheme']) )
            return $default;

        // Reject if scheme is set but host is not. This catches urls like https:host.com
        // for which parse_url does not set the host field:
        if ( isset($lp['scheme'])  && !isset($lp['host']) )
            return $default;

        $wpp = parse_url(home_url());
        $allowed_hosts = (array)apply_filters('allowed_redirect_hosts', array($wpp['host']), isset($lp['host']) ? $lp['host'] : '');

        if( isset($lp['host']) && ( !in_array($lp['host'], $allowed_hosts) && $lp['host'] != strtolower($wpp['host'])) ) {
            $location = $default;
        }

        return $location;

    }
}

if( !function_exists('leyka_get_client_ip') ) {

    function leyka_get_client_ip() {
        return getenv('HTTP_CLIENT_IP') ? :
            getenv('HTTP_X_FORWARDED_FOR') ? :
                getenv('HTTP_X_FORWARDED') ? :
                    getenv('HTTP_FORWARDED_FOR') ? :
                        getenv('HTTP_FORWARDED') ? :
                            getenv('REMOTE_ADDR');
    }

}

/**
 * @param $campaign_id int
 * @param $limit int|false False to get all donations (unlimited number).
 * @return array|false An array of Leyka_Donation objects, or false if wrong campaign ID given.
 */
function leyka_get_campaign_donations($campaign_id = false, $limit = false) {

    $campaign_id = $campaign_id ? absint($campaign_id) : false;
    $limit = (int)$limit > 0 ? (int)$limit : false;

    $params = array('post_type' => Leyka_Donation_Management::$post_type, 'post_status' => 'funded', 'meta_query' => array(),);
    if($campaign_id) {
        $params['meta_query'][] = array('key' => 'leyka_campaign_id', 'value' => $campaign_id, 'compare' => '=',);
    }

    if($limit) {
        $params['posts_per_page'] = $limit;
    } else {

        $params['posts_per_page'] = -1;
        $params['nopaging'] = true;

    }

    $donations = array();
    foreach(get_posts($params) as $donation) {
        $donations[] = new Leyka_Donation($donation);
    }

    return $donations;

}

function leyka_get_donations_archive_url($campaign_id = false) {

    if((int)$campaign_id > 0) {

        $campaign = get_post($campaign_id);

        $donations_permalink = trim(get_permalink($campaign_id), '/');
        if(strpos($donations_permalink, '?')) {
            $donations_permalink = home_url('?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter='.$campaign->post_name);
        } else {
            $donations_permalink = $donations_permalink.'/donations/';
        }

    } else {
        $donations_permalink = get_option('permalink-structure') ?
            home_url('/donations/') : home_url('?post_type='.Leyka_Donation_Management::$post_type);
    }

    return $donations_permalink;

}

function leyka_remembered_data($name, $value = null, $delete = false) {

    $name = stripos($name, 'leyka_') === false ? 'leyka_'.$name : $name;

    if($value) {
        return setcookie($name, trim($value), current_time('timestamp')+60*60, COOKIEPATH, COOKIE_DOMAIN, false);
    } else if( !!$delete ) {
        return setcookie($name, '', current_time('timestamp')-3600, COOKIEPATH, COOKIE_DOMAIN, false);
    } else {
        return empty($_COOKIE[$name]) ? '' : trim($_COOKIE[$name]);
    }

}

function leyka_calculate_donation_total_amount($donation = false, $amount = 0.0, $pm_full_id = '') {

    if($donation) {
        $donation = leyka_get_validated_donation($donation);
    }

    $amount = $amount ? $amount : ($donation ? $donation->amount : (float)$amount);
    $pm_full_id = $pm_full_id ? $pm_full_id : ($donation ? $donation->pm_full_id : false);

    if( !$amount || !$pm_full_id ) {
        return 0.0;
    }

    $commission = leyka_options()->opt('commission');
    $commission = empty($commission[$pm_full_id]) ?
        0.0 : $commission[$pm_full_id]/100.0;

    return $commission && $commission > 0.0 ? $amount - round($amount*$commission, 2) : $amount;

}

function leyka_get_pm_commission($pm_full_id) {

    $commission = leyka_options()->opt('commission');

    return empty($commission[$pm_full_id]) ? 0.0 : $commission[$pm_full_id]/100.0;

}

/**
 * A helper function to insert posts manually. Used only when wp_insert_post() leads to notices & fatal errors.
 *
 * @param $post_data array New page data.
 * @return integer|false
 */
function leyka_manually_insert_page(array $post_data) {

    global $wpdb;

    $post_date = current_time('mysql');
    $wpdb->insert($wpdb->prefix.'posts', array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $post_data['post_title'],
        'post_content' => $post_data['post_content'],
        'post_name' => $post_data['post_name'],
        'post_author' => get_current_user_id(),
        'post_excerpt' => '',
        'post_date' => $post_date,
        'post_date_gmt' => get_gmt_from_date($post_date),
        'post_modified' => $post_date,
        'post_modified_gmt' => get_gmt_from_date($post_date),
    ));

    return $wpdb->insert_id;

}

/** @return array An assoc array of all Leyka options from leyka-option-meta file and some environment data */
function leyka_get_env_and_options() {
    return array_merge(leyka_get_all_options(), leyka_get_env(), leyka_get_db_stats());
}

function humanaize_debug_data($debug_data) {

    $humanized_options = array();

    foreach($debug_data['options'] as $k => $v) {
        $option_info = leyka_options()->get_info_of($k);
        $option_title = empty($option_info['title']) || $option_info['title'] == $k ? $k : $option_info['title'];
        $humanized_options[$option_title] = $v;
    }
    $debug_data['options'] = $humanized_options;
    
    foreach(array_keys($debug_data['plugins']) as $status) {

        $humanized_options = array();
        
        foreach($debug_data['plugins'][$status] as $plugin) {
            $humanized_options[] = sprintf("%s %s", $plugin['name'], $plugin['ver']);
        }
        
        $debug_data['plugins'][$status] = $humanized_options;

    }
    
    return $debug_data;

}

function format_debug_data($list, $level = 0) {

    $fomatted_ret = '';
    
    if($level > 0) {
        ksort($list);
    }
    
    foreach($list as $k => $v) {
        $fomatted_ret .= str_repeat("    ", $level) . "<strong>$k:</strong> ";
        if(is_array($v)) {
            $fomatted_ret .= "\n" . format_debug_data($v, $level + 1) . ($level == 0 ? "\n" : "");
        }
        else {
            $fomatted_ret .= trim($v) . "\n";
        }
    }
    
    return $fomatted_ret;

}

/** @return array An assoc array of some db stats */
function leyka_get_db_stats() {

    global $wpdb;
    
    $query_time_start = microtime(true);

    $payments_count = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", Leyka_Donation_Management::$post_type)
    );

    $all_posts_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts");
    
    $db_stats = array(
        'db_stats' =>array(
            'all_posts_count' => $all_posts_count,
            'payments_count' => $payments_count,
            'query_exec_time' => sprintf("%.10f", microtime(true) - $query_time_start),
        ),
    );
    
    return $db_stats;

}

/** @return array An assoc array of some environment data */
function leyka_get_env() {

    if( !function_exists('get_plugins') ) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }

    global $wp_version;

    $res = array(
        'wp_core' => $wp_version,
        'env' => array('php_version' => phpversion(), 'php_extensions' => get_loaded_extensions()),
    );

    // Server data:
    $forbidden_data = array(
        'MIBDIRS', 'OPENSSL_CONF', 'HTTP_COOKIE', 'PATH', 'SystemRoot', 'COMSPEC', 'WINDIR', 'DOCUMENT_ROOT',
        'CONTEXT_DOCUMENT_ROOT', 'SCRIPT_FILENAME', 'APACHE_LOG_DIR', 'APACHE_RUN_GROUP', 'APACHE_RUN_USER', 'LANG', 'PWD',
        'APACHE_LOCK_DIR', 'APACHE_PID_FILE', 'APACHE_RUN_DIR', 'APACHE_CONFDIR', 'argc', 'argv', 'PHP_SELF', 'SCRIPT_NAME',
        'REDIRECT_URL', 'REMOTE_PORT', 'REQUEST_SCHEME', 'SERVER_PORT', 'SERVER_ADDR', 'SERVER_SIGNATURE', 'CONTENT_TYPE',
        'HTTP_ACCEPT', 'CONTENT_LENGTH', 'HTTP_CONNECTION', 'REQUEST_URI', 'REMOTE_ADDR',
    );
    foreach($_SERVER as $key => $value) {

        if(in_array($key, $forbidden_data)) {
            continue;
        }

        $res['env']['server_'.$key] = is_array($value) ? serialize($value) : strip_tags($value);

    }
    foreach($_ENV as $key => $value) {

        if(in_array($key, $forbidden_data)) {
            continue;
        }

        $res['env']['env_'.$key] = is_array($value) ? serialize($value) : strip_tags($value);

    }

    // WP core/Theme/plugins data:
    $res['plugins'] = array('active' => array(), 'inactive' => array(),);

    foreach(get_plugins() as $key => $plugin_data) {
        if(in_array($key, get_option('active_plugins'))) {
            $res['plugins']['active'][] = array('name' => $plugin_data['Name'], 'ver' => $plugin_data['Version']);
        } else {
            $res['plugins']['inactive'][] = array('name' => $plugin_data['Name'], 'ver' => $plugin_data['Version']);
        }
    }

    $theme = wp_get_theme();
    $res['theme'] = array(
        'name' => $theme->Name,
        'ver' => $theme->Version,
        'template' => $theme->template,
        'parent' => $theme->parent ?
            array('name' => $theme->Name, 'ver' => $theme->Version, 'template' => $theme->parent->template,) : array(),
    );

    return $res;

}

/** @return array An assoc array of Leyka options (from leyka-options-meta) & settings (other "leyka_something"-named options) */
function leyka_get_all_options() {

    $res = array('options' => array(), 'settings' => array());
    $leyka_options_keys = leyka_options()->get_options_names();

    $forbidden_options = array(
        'person_pd_terms_text', 'person_terms_of_service_text', 'pd_terms_text', 'terms_of_service_text', 'org_bank_account',
        'email_thanks_text', 'org_face_fio_ip', 'org_face_fio_rp', 'org_address', 'person_full_name', 'person_address',
        '_transient_leyka_wizards_activities', '_transient_leyka_default_campaign_id', 'permalinks_flushed', 'org_bank_name',
        'org_actual_address_differs', 'plugin_stats_option_sync_done', 'widget_leyka_donations_list', 'org_bank_bic', 'org_inn',
        'widget_leyka_campaigns_list', 'paypal_client_id', 'paypal_api_signature', 'paypal_api_password', 'paypal_api_username',
        'quittance_redirect_page', 'rbk_api_web_hook_key', 'rbk_api_key', 'rbk_shop_id', 'chronopay_ip', 'chronopay_shared_sec',
        'chronopay_use_payment_uniqueness_control', 'chronopay_card_rebill_product_id_eur', 'org_bank_corr_account', 'org_kpp',
        'chronopay_card_rebill_product_id_usd', 'chronopay_card_rebill_product_id_rur', 'yandex-yandex_card_private_key_password',
        'yandex-yandex_card_private_key_path', 'yandex-yandex_card_certificate_path', 'org_face_position', 'yandex_secret_key',
        'yandex_shop_password', 'yandex_shop_article_id', 'yandex_scid', 'yandex_shop_id', 'cp_ip', 'cp_public_id',
        'options:robokassa_shop_password2', 'robokassa_shop_password1', 'robokassa_shop_id', 'chronopay_card_product_id_rur',
        'chronopay_card_product_id_usd', 'chronopay_card_product_id_eur', 'text_box_details', 'yandex_money_account',
        'yandex_money_secret', 'mixplat-mobile_details', 'mixplat-sms_default_campaign_id', 'mixplat-sms_description',
        'mixplat-sms_details', 'mixplat_service_id', 'mixplat_secret_key', 'paymaster_merchant_id', 'paymaster_secret_word',
        'paymaster_hash_method', 'failure_page', 'success_page', 'pd_terms_page', 'terms_of_service_page',
    );

    foreach(wp_load_alloptions() as $name => $value) {

        $name_clear = strpos($name, 'leyka_') === 0 ? substr_replace($name, '', 0, strlen('leyka_')) : $name;

        if(in_array($name_clear, $forbidden_options) || preg_match("/^knd_val_hash_leyka_/", $name)) {
            continue;
        } else if(in_array($name_clear, $leyka_options_keys)) {
            $res['options'][$name_clear] = $value;
        } else if(stristr($name, 'leyka_') !== false && !preg_match('/^(leyka_)(.+)(_description)$/i', $name)) {
            $res['settings'][$name] = $value;
        }

    }

    return $res;

}

function leyka_is_tab_valid($tab_id) {

    $tab_options = Leyka_Options_Allocator::get_instance()->get_tab_options($tab_id);

    if( !$tab_options ) {
        return false;
    }

    foreach($tab_options as $key => $option_params) {

        if($key === 'section') {

            if( !empty($option_params['options']) ) { // Noramal section - validate all options
                foreach($option_params['options'] as $option_id) {
                    if( !leyka_options()->is_valid($option_id) ) {
                        return false;
                    }
                }
            } else if( !empty($option_params['tabs']) ) {

                foreach($option_params['tabs'] as $sub_tab_id => $sub_tab_content) {

                    if( !empty($sub_tab_content['sections']) ) {
                        foreach($sub_tab_content['sections'] as $sub_section) {
                            if( !empty($sub_section['options']) ) {
                                foreach($sub_section['options'] as $sub_section_option_id) {
                                    if( !leyka_options()->is_valid($sub_section_option_id) ) {
                                        return false;
                                    }
                                }
                            }
                        }
                    }

                }

            }

        } else if( !leyka_options()->is_valid($key) ) { // Validate single option
            return false;
        }

    }

    return true;

}

if( !function_exists('array_key_last') ) {
    function array_key_last($array) {

        if( !is_array($array) || empty($array) ) {
            return null;
        }

        return array_keys($array)[count($array) - 1];

    }
}

if( !function_exists('leyka_get_delta_percent') ) {
    function leyka_get_delta_percent($prev_value, $new_value, $handle_incomparabe_cases = true) {

        $handle_incomparabe_cases = !!$handle_incomparabe_cases;

        if( !$prev_value ) {
            $delta_percent = $handle_incomparabe_cases ? NULL : ($new_value ? 100.0 : 0);
        } else {
            $delta_percent = $handle_incomparabe_cases && !$new_value ?
                NULL : round(100.0*($new_value - $prev_value)/$prev_value, 2);
        }

        return $delta_percent;

    }
}

if( !function_exists('leyka_amount_format') ) {
    function leyka_amount_format($amount) {

        // Display amount decimal part only if there is one:
        $amount = round((float)$amount, 2);
        return (abs($amount) - abs((int)$amount) > 0) ? number_format_i18n($amount, 2) : number_format_i18n($amount);

    }
}

abstract class Leyka_Singleton {

    protected static $_instance = null;

    /**
     * @return static
     */
    public static function get_instance() {

        if(null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;

    }

    final protected function __clone() {}

    protected function __construct() {
    }

}

if( !function_exists('leyka_save_option') ) {
    function leyka_save_option($setting_id) {

        $option_type = leyka_options()->get_type_of($setting_id);

        if($option_type === 'checkbox') {
            leyka_options()->opt($setting_id, isset($_POST["leyka_$setting_id"]) ? 1 : 0);
        } elseif($option_type == 'multi_checkbox') {

            if(isset($_POST["leyka_$setting_id"]) && leyka_options()->opt($setting_id) !== $_POST["leyka_$setting_id"]) {
                leyka_options()->opt($setting_id, (array)$_POST["leyka_$setting_id"]);
            }

        } elseif($option_type === 'html' || $option_type === 'rich_html') {

            if(isset($_POST["leyka_$setting_id"]) && leyka_options()->opt($setting_id) !== $_POST["leyka_$setting_id"]) {
                leyka_options()->opt($setting_id, esc_attr(stripslashes($_POST["leyka_$setting_id"])));
            }

        } else if(stristr($option_type, 'custom_') !== false && isset($_POST["leyka_$setting_id"])) { // Custom field types
            do_action("leyka_save_custom_option-$setting_id", $_POST["leyka_$setting_id"]);
        } else if(isset($_POST["leyka_$setting_id"])) { // Simple field types

            $old_value = leyka_options()->opt($setting_id);
            if($old_value != $_POST["leyka_$setting_id"]) {
                leyka_options()->opt($setting_id, esc_attr(stripslashes($_POST["leyka_$setting_id"])));
            }

            do_action("leyka_after_save_option-$setting_id", $old_value, $_POST["leyka_$setting_id"]);

        }

    }
}

if( !function_exists('leyka_add_editor_css') ) {
	function leyka_add_editor_css() {
		add_editor_style( LEYKA_PLUGIN_BASE_URL.'assets/css/editor.css' );
	}
}
add_action( 'after_setup_theme', 'leyka_add_editor_css' );

if( !function_exists('leyka_get_l18n_date') ) {
    function leyka_get_i18n_date($timestamp) {
        return date_i18n(get_option('date_format'), (int)$timestamp);
    }
}
if( !function_exists('leyka_get_l18n_time') ) {
    function leyka_get_i18n_time($timestamp) {
        return date_i18n(get_option('time_format'), (int)$timestamp);
    }
}
if( !function_exists('leyka_get_l18n_datetime') ) {
    function leyka_get_i18n_datetime($timestamp) {
        return date_i18n(get_option('date_format').', '.get_option('time_format'), (int)$timestamp);
    }
}

// localize tags to replace in js
if( !function_exists('leyka_localize_rich_html_text_tags') ) {
    function leyka_localize_rich_html_text_tags() {
        $is_legal = leyka_options()->opt('receiver_legal_type') === 'legal';
        
        wp_localize_script( 'leyka-settings', 'leykaRichHTMLTags', array(
            'termsKeys' => array(
                array(
                    '#LEGAL_NAME#',
                    '#LEGAL_FACE#',
                    '#LEGAL_FACE_POSITION#',
                    '#LEGAL_ADDRESS#',
                    '#STATE_REG_NUMBER#',
                    '#KPP#',
                    '#INN#',
                    '#BANK_ACCOUNT#',
                    '#BANK_NAME#',
                    '#BANK_BIC#',
                    '#BANK_CORR_ACCOUNT#',
                    '#SITE_NAME#',
                    '#ORG_NAME#',
                ),
                array(
                    $is_legal ? leyka_options()->opt('org_full_name') : leyka_options()->opt('person_full_name'),
                    $is_legal ? leyka_options()->opt('org_face_fio_ip') : leyka_options()->opt('person_full_name'),
                    $is_legal ? leyka_options()->opt('org_face_position') : '',
                    $is_legal ? leyka_options()->opt('org_address') : leyka_options()->opt('person_address'),
                    $is_legal ? leyka_options()->opt('org_state_reg_number') : '',
                    $is_legal ? leyka_options()->opt('org_kpp') : '',
                    $is_legal ? leyka_options()->opt('org_inn') : leyka_options()->opt('person_inn'),
                    $is_legal ? leyka_options()->opt('org_bank_account') : leyka_options()->opt('person_bank_account'),
                    $is_legal ? leyka_options()->opt('org_bank_name') : leyka_options()->opt('person_bank_name'),
                    $is_legal ? leyka_options()->opt('org_bank_bic') : leyka_options()->opt('person_bank_bic'),
                    $is_legal ? leyka_options()->opt('org_bank_corr_account') : leyka_options()->opt('person_bank_corr_account'),
                    get_bloginfo('name'),
                    $is_legal ? leyka_options()->opt('org_full_name') : leyka_options()->opt('person_full_name'),
                ),
            ),
            'pdKeys' => array(
                array(
                    '#LEGAL_NAME#',
                    '#LEGAL_ADDRESS#',
                    '#SITE_URL#',
                    '#PD_TERMS_PAGE_URL#',
                    '#ADMIN_EMAIL#',
                ),
                array(
                    $is_legal ? leyka_options()->opt('org_full_name') : leyka_options()->opt('person_full_name'),
                    $is_legal ? leyka_options()->opt('org_address') : leyka_options()->opt('person_address'),
                    home_url(),
                    leyka_get_pd_terms_page_url(),
                    get_option('admin_email'),
                ),
            ),
        ));
    }
}

function leyka_is_donor_account() {

    if( !leyka()->opt('donor_accounts_available') ) {
        return false;
    }

    return stristr($_SERVER['REQUEST_URI'], 'donor-account') !== false;

}

function leyka_get_upload_max_filesize() {

    if(defined('WP_MEMORY_LIMIT')) {
        $max_filesize = WP_MEMORY_LIMIT;
    } else {
        $max_filesize = ini_get('upload_max_filesize');
    }

    return $max_filesize;

}

function leyka_use_leyka_campaign_template($template) {

    $campaign_id = null;

    if(is_singular(Leyka_Campaign_Management::$post_type)) {
        $campaign_id = get_post()->ID;
    } else if(is_page(leyka_options()->opt('success_page')) || is_page(leyka_options()->opt('failure_page'))) {

        $donation_id = leyka_remembered_data('donation_id');
        $donation = $donation_id ? new Leyka_Donation($donation_id) : null;
        $campaign_id = $donation ? $donation->campaign_id : null;

    }

    if($campaign_id) {

        $campaign = leyka_get_validated_campaign($campaign_id);
        if($campaign && $campaign->campaign_type === 'persistent' && $campaign->template === 'star') {
            $template = LEYKA_PLUGIN_DIR . 'templates/campaign/type-persistent.php';
        }

    }

    return $template;

}
add_filter('single_template', 'leyka_use_leyka_campaign_template', 10, 1);
add_filter('page_template', 'leyka_use_leyka_campaign_template', 10, 1);

function leyka_use_leyka_donations_list_template($archive_template) {

    $leyka_screen = get_query_var('leyka-screen');
    if(is_post_type_archive(Leyka_Donation_Management::$post_type)) {
        switch($leyka_screen) {
            case 'account':
                $archive_template = LEYKA_PLUGIN_DIR.'templates/account/account.php';
                break;
            case 'login':
                $archive_template = LEYKA_PLUGIN_DIR.'templates/account/login.php';
                break;
            case 'reset-password':
                $archive_template = LEYKA_PLUGIN_DIR.'templates/account/reset-password.php';
                break;
            case 'cancel-subscription':
                $archive_template = LEYKA_PLUGIN_DIR.'templates/account/cancel-subscription.php';
                break;
            default:
        }
    }

    return $archive_template;

}
add_filter('archive_template', 'leyka_use_leyka_donations_list_template');

function leyka_get_website_tech_support_email() {
    return leyka()->opt('tech_support_email') ? leyka()->opt('tech_support_email') : get_option('admin_email');
}

function leyka_get_cancel_subscription_reasons() {
    return array(
        'uncomfortable_pm' => esc_html__('Unconfortable payment method', 'leyka'),
        'too_much' => esc_html__('Too much donation', 'leyka'),
        'not_match' => esc_html__('Does not meet my interests', 'leyka'),
        'better_use' => esc_html__('I have found better use of money', 'leyka'),
        'other' => esc_html__('Other reason', 'leyka'),
    );
}

function get_donor_init_recurring_donation_for_campaign($donor_user, $campaign_id) {

    $donations = new WP_Query(array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' => 'funded',
        'post_parent' => 0,
        'meta_query' => array(
            'relation' => 'AND',
            array('key' => 'leyka_payment_type', 'value' => 'rebill'),
            array('key' => 'leyka_campaign_id', 'value' => $campaign_id),
            array(
                'relation' => 'OR',
                array('key' => 'leyka_recurrents_cancelled', 'value' => false),
                array('key' => 'leyka_recurrents_cancelled', 'compare' => 'NOT EXISTS'),
            ),
            array(
                'relation' => 'OR',
                array('key' => 'leyka_cancel_recurring_requested', 'value' => false),
                array('key' => 'leyka_cancel_recurring_requested', 'compare' => 'NOT EXISTS'),
            ),            
            array(
                'relation' => 'OR',
                array('key' => 'leyka_donor_email', 'value' => $donor_user->user_email),
                array('key' => 'leyka_donor_account', 'value' => $donor_user->ID),
            ),
        ),
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order'   => 'ASC',
    ));

    return $donations->have_posts() ? new Leyka_Donation($donations->posts[0]) : null;

}

function leyka_get_dm_list_or_alternatives() {

    $dm_list = array();

    foreach(explode(',', leyka_options()->opt('leyka_donations_managers_emails')) as $email) {
        if($email) {
            $dm_list[] = $email;
        }
    }
    
    if( !$dm_list ) {

        $alt_emails = array(leyka()->opt('tech_support_email'), get_bloginfo('admin_email'),);

        foreach($alt_emails as $alt_email) {

            $alt_email = trim($alt_email);
            if($alt_email) {
                $dm_list[] = $alt_email;
                break;
            }

        }

    }

    return $dm_list;

}

function leyka_cronjob_exists($command, $strict = false) {

    exec('crontab -l', $crontab);

    if(isset($crontab) && is_array($crontab)) {

        if( !!$strict ) {
            return in_array($command, $crontab);
        }

        foreach($crontab as $job) {
            if(stristr($job, $command) !== false) {
                return true;
            }
        }

    }

    return false;

}

function leyka_get_cronjobs_status() {

    $status = 'no-need';

    foreach(leyka_get_pm_list(true) as $pm) {
        if($pm->full_id === 'yandex-yandex_card' && leyka()->opt('yandex-yandex_card_rebilling_available')) {
            if(
                leyka_cronjob_exists(home_url('/leyka/service/do_recurring'))
                || leyka_cronjob_exists(home_url('/leyka/service/procedure/active-recurring'))
                || leyka_cronjob_exists(LEYKA_PLUGIN_DIR.'procedures/leyka-active-recurring.php')
            ) {
                $status = 'ok';
            } else {
                $status = 'not-set';
            }
        }
    }

    if($status === 'no-need' && leyka()->opt('send_donor_emails_on_campaign_target_reaching')) {
        if(
            leyka_cronjob_exists(home_url('/leyka/service/do_campaigns_targets_reaching_mailout'))
            || leyka_cronjob_exists(home_url('/leyka/service/procedure/campaigns-targets-reaching-mailout'))
            || leyka_cronjob_exists(LEYKA_PLUGIN_DIR.'procedures/leyka-campaigns-targets-reaching-mailout.php')
        ) {
            $status = 'ok';
        } else {
            $status = 'not-set';
        }
    }

    switch($status) {
        case 'ok': return array('status' => $status, 'title' => __('Connected', 'leyka'));
        case 'not-set': return array('status' => $status, 'title' => __('Setup needed', 'leyka'));
        case 'no-need':
        default:
            return array('status' => $status, 'title' => __('No need', 'leyka'));
    }

}

/** Service function to prepare a singular object data value for export as a CSV cell. */
function leyka_export_data_prepare($text) {
    return '"'.str_replace(array(';', '"'), array('', ''), $text).'"';
}

/** Service function to prepare some object data array for export as a CSV line. */
function leyka_prepare_data_line_for_export(array $line_data, $object_to_export) {

    foreach($line_data as &$data) {
        $data = leyka_export_data_prepare($data);
    }

    return $line_data;

}