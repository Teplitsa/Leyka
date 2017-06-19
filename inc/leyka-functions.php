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

if( !function_exists('leyka_set_html_content_type') ) {
    function leyka_set_html_content_type() {
        return 'text/html';
    }
}

function leyka_current_user_has_role($role, $user_id = false) {

    $user = is_numeric($user_id) ? get_userdata( $user_id ) : wp_get_current_user();

    if( !$user )
        return false;

    return in_array($role, (array)$user->roles);
}

/**
 * @param $donation mixed
 * @return Leyka_Donation A donation object, if parameter is valid in one way or another; false otherwise.
 */
function leyka_get_validated_donation($donation) {

    if(is_int($donation) && (int)$donation > 0) {
        $donation = new Leyka_Donation((int)$donation);
    } elseif(is_a($donation, 'WP_Post')) {
        $donation = new Leyka_Donation($donation);
    } elseif( !is_a($donation, 'Leyka_Donation') ) {
        return false;
    }

    return $donation ? $donation : false;
}

/**
 * @param $campaign mixed
 * @return mixed A Leyka_Campaign instance if parameter is valid in one way or another; false otherwise.
 */
function leyka_get_validated_campaign($campaign) {

    if(is_int($campaign) && (int)$campaign > 0) {
        $campaign = new Leyka_Campaign((int)$campaign);
    } elseif(is_a($campaign, 'WP_Post')) {
        $campaign = new Leyka_Campaign($campaign);
    } elseif( !is_a($campaign, 'Leyka_Campaign') ) {
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

/** A callback for the default gateway select field. */
function leyka_get_gateways_pm_list() {

    $options = array();
    foreach(leyka_get_pm_list(null, false, false) as $pm) {
        $gateway_title = leyka_get_gateway_by_id($pm->gateway_id)->title;
        $options[$pm->full_id] = $pm->label_backend
            .($gateway_title == $pm->label_backend ? '' : ' ('.$gateway_title.')');
    }

    return $options;
}

function leyka_get_default_email_from() {

    $domain = explode('/', trim(str_replace('http://', '', home_url('', 'http')), '/'));
    return 'no_reply@'.$domain[0];
}

/** DM is for "donation manager" */
function leyka_get_default_dm_list() {
    return get_bloginfo('admin_email').',';
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

        $page = wp_insert_post(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => 'thank-you-for-your-donation',
            'post_title' => __('Thank you!', 'leyka'),
            'post_content' => __('Your donation completed. We are grateful for your help.', 'leyka'),
        ));

        do_action('leyka_default_success_page_created', $page);
    }

    if($page) {
        update_option('leyka_success_page', $page);
    }

    return $page ? $page : 0;
}

function leyka_get_success_page_url() {

    $url = leyka_options()->opt('success_page') ? get_permalink(leyka_options()->opt('success_page')) : home_url();

    if( !$url ) { // It can be in case when "last posts" is selected for homepage
        $url = home_url();
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

        $page = wp_insert_post(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => 'sorry-donation-failure',
            'post_title' => __('Payment failure', 'leyka'),
            'post_content' => __('We are deeply sorry, but for some technical reason we failed to receive your donation. Your money are intact. Please try again later!', 'leyka'),
        ));

        do_action('leyka_default_failure_page_created', $page);
    }

    if($page) {
        update_option('leyka_failure_page', $page);
    }

    return $page ? $page : 0;
}

function leyka_get_failure_page_url() {

    $url = leyka_options()->opt('failure_page') ? get_permalink(leyka_options()->opt('failure_page')) : home_url();

    if( !$url ) { // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    }

    return $url;

}

/** Get a list of donation form templates as an array. */
function leyka_get_form_templates_list() {

    $list = array();
    foreach(leyka()->get_templates() as $template) {

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

function leyka_get_currency_label($currency_code) {

    $currecies = leyka_get_currencies_data();

    return isset($currecies[$currency_code]['label']) ? $currecies[$currency_code]['label'] : false;
}


/**
 * Get possible leyka_donation post type's status list as an array.
 **/
function leyka_get_donation_status_list() {
    return leyka()->get_donation_statuses();
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

    return $campaign->target ? array(
        'amount' => $campaign->target,
        'currency' => 'rur', // Currently, target is always in RUR  
    ) : 0;

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

    return array(
        'amount' => $campaign->get_collected_amount(),
        'currency' => 'rur', // Currently, collections are all in RUR
    );
}


/**
 * Scale
 **/
function leyka_scale_compact($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }
        
    $target = (int)$campaign->target;
    $curr_label = leyka_get_currency_label('rur');
    $collected = $campaign->get_collected_amount();

    if($target <= 0) {
        return;
    }

    $percentage = round(($collected/$target)*100);
	if($percentage > 100)
		$percentage = 100;?>

<div class="leyka-scale-compact">
    <div class="leyka-scale-scale">
        <div class="target">
            <div style="width:<?php echo $percentage;?>%" class="collected">&nbsp;</div>
        </div>
    </div>
    <div class="leyka-scale-label">
    <?php $target_f = number_format($target, 0, '.', ' ');
    $collected_f = number_format($collected, 0, '.', ' ');

    if($collected == 0) {
        printf(__('Needed %s %s', 'leyka'), '<b>'.$target_f.'</b>', $curr_label);
    } else {
        printf(__('Collected %s of %s %s', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);
    }?>
    </div>
</div>
<?php
}

function leyka_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }

    $target = (int)$campaign->target;
    $curr_label = leyka_get_currency_label('rur');
    $collected = (int)$campaign->get_collected_amount();
   
    if($target == 0) {
        return;
    }
    
    $percentage = round(($collected/$target)*100);
	if($percentage > 100)
		$percentage = 100;?>

<div class="leyka-scale-ultra">
    <div class="leyka-scale-scale">
        <div class="target">
            <div style="width:<?php echo $percentage;?>%" class="collected">&nbsp;</div>
        </div>
    </div>
    <div class="leyka-scale-label"><span>
    <?php $target_f = number_format($target, 0, '.', ' ');
    $collected_f = number_format($collected, 0, '.', ' ');

    printf(_x('%s of %s %s', 'Label on ultra-compact scale', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);?>
    </span></div>
</div>
<?php  
}

function leyka_fake_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') ) {
        $campaign = new Leyka_Campaign($campaign);
    }

    $curr_label = leyka_get_currency_label('rur');
    $collected = number_format(intval($campaign->get_collected_amount()), 0, '.', ' ');?>

<div class="leyka-scale-ultra-fake">
    <div class="leyka-scale-scale">
        <div class="target"> </div>
    </div>
    <div class="leyka-scale-label"><span>
        <?php printf(_x('Collected: %s', 'Label on ultra-compact fake scale', 'leyka'), "<b>{$collected}</b> {$curr_label}");?>
    </span></div>
</div>
<?php
}

/** @return array An array of possible payment types with labels */
function leyka_get_payment_types_list() {

    return array(
        'single'     => __('Single', 'leyka'),
        'rebill'     => __('Recurrent (rebill)', 'leyka'),
        'correction' => __('Correction', 'leyka')
    );
}

function leyka_get_payment_type_label($type) {

    if(empty($type)) {
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
    $tab_options = leyka_opt_alloc()->get_tab_options($settings_tab); // Special 4 strict standards
    $option_section = reset($tab_options);

    foreach($option_section['section']['options'] as $option_name) {

        if( !leyka_options()->opt_safe($option_name) && leyka_options()->is_required($option_name) ) {

            $settings_complete = false;
            break;
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

function leyka_get_campaigns_select_options() {
    return leyka_get_campaigns_list(array('orderby' => 'title', 'order' => 'ASC'), true);
}

function leyka_get_campaigns_select_default() {

    $campaigns_ids = array_keys(leyka_get_campaigns_select_options());
    return reset($campaigns_ids);
}

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

function leyka_is_campaign_link_in_menu() {

//    foreach(get_registered_nav_menus() as $menu_id => $menu_name) {
//
//        echo '<pre>' . print_r($menu_id.' - '.$menu_name, 1) . '</pre>';
//        echo '<pre>' . print_r(wp_get_nav_menu_items($menu_id), 1) . '</pre>';
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

    if( !leyka_options()->opt('load_scripts_if_need') ) {
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

    $form_is_screening = leyka()->form_is_screening ||
        is_singular(Leyka_Campaign_Management::$post_type) ||
        stristr($template, 'home-campaign_one') !== false ||
        stripos($template, 'leyka') !== false ||
        $content_has_shortcode ||
        ( !!$widgets_also ? leyka_is_widget_active() : false ) ||
        apply_filters('leyka_form_is_screening', false);

    return $form_is_screening;

}

function leyka_revo_template_displayed() {

    $revo_displayed = false;

    if(is_singular(Leyka_Campaign_Management::$post_type)) {

        $campaign = new Leyka_Campaign(get_post());
        if($campaign->template == 'default') {
            $leyla_template_data = leyka_get_current_template_data();
            $revo_displayed = $leyla_template_data['id'] == 'revo';
        }
        else {
            $revo_displayed = $campaign->template == 'revo';
        }

    } else if(get_post() && has_shortcode(get_post()->post_content, 'leyka_inline_campaign')) {
        $revo_displayed = true;
        /** @todo Refactor this logic! Right now the check doesn't watch if shortcode uses Revo template or not */
    }

    return apply_filters('leyka_revo_template_displayed', $revo_displayed);

}

function leyka_success_widget_displayed() {
    return leyka_options()->opt('show_success_widget_on_success') && is_page(leyka_options()->opt('success_page'));
}

function leyka_failure_widget_displayed() {
    return leyka_options()->opt('show_failure_widget_on_failure') && is_page(leyka_options()->opt('failure_page'));
}

/** ITV info-widget **/
function leyka_itv_info_widget() {

    $locale = get_locale();
    if($locale != 'ru_RU') { // Only in Russian for now
        return;
    }

    $domain = parse_url(home_url());
    $itv_url = esc_url("https://itv.te-st.ru/?leyka=".$domain['host']);?>

	<div id="itv-card">
        <div class="itv-logo"><a href="<?php echo $itv_url;?>" target="_blank"><img src="<?php echo esc_url(LEYKA_PLUGIN_BASE_URL.'img/logo-itv.png');?>"></a></div>

        <p>Вам нужна помощь в настройке пожертвований или подключении к платежным системам? Опубликуйте задачу на платформе <a href="<?php echo $itv_url;?>" target="_blank">it-волонтер</a></p>

        <p><a href="<?php echo $itv_url;?>" target="_blank" class="button">Опубликовать задачу</a></p>
    </div>
<?php
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
    return $email ? filter_var($email, FILTER_VALIDATE_EMAIL) : true;
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

        // Reject if scheme is set but host is not. This catches urls like https:host.com for which parse_url does not set the host field.
        if ( isset($lp['scheme'])  && !isset($lp['host']) )
            return $default;

        $wpp = parse_url(home_url());

        $allowed_hosts = (array) apply_filters('allowed_redirect_hosts', array($wpp['host']), isset($lp['host']) ? $lp['host'] : '');

        if ( isset($lp['host']) && ( !in_array($lp['host'], $allowed_hosts) && $lp['host'] != strtolower($wpp['host'])) )
            $location = $default;

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

function leyka_get_campaign_donations($campaign, $limit = false) {

    $campaign = (int)$campaign;
    if($campaign <= 0) {
        return false;
    }

    $campaign = new Leyka_Campaign($campaign);
    if( !$campaign->id ) {
        return false;
    }

    $limit = (int)$limit > 0 ? (int)$limit : false;

    $params = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'nopaging' => true,
        'post_status' => 'funded',
        'meta_query' => array(
            array(
                'key' => 'leyka_campaign_id',
                'value' => $campaign->id,
                'compare' => '=',
            ),
        ),
    );

    if($limit) {

        unset($params['nopaging']);
        $params['posts_per_page'] = $limit;

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
        return setcookie($name, trim($value), time()+60*60, COOKIEPATH, COOKIE_DOMAIN, false);
    } else if( !!$delete ) {
        return setcookie($name, '', time()-3600, COOKIEPATH, COOKIE_DOMAIN, false);
    } else {
        return empty($_COOKIE[$name]) ? '' : trim($_COOKIE[$name]);
    }

}