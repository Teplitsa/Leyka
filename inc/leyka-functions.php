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
function get_validated_donation($donation) {

    if(is_int($donation) && (int)$donation > 0) {
        $donation = new Leyka_Donation((int)$donation);
    } elseif(is_a($donation, 'WP_Post')) {
        $donation = new Leyka_Donation($donation);
    } elseif( !is_a($donation, 'Leyka_Donation') ) {
        return false;
    }

    return $donation ? $donation : false;
}

/** Get WP pages list as an array. Used mainly to form a dropdowns. */
function leyka_get_pages_list() {

    $query = new WP_Query(apply_filters('leyka_pages_list_query', array(
        'post_type' => 'page',
        'posts_per_page' => -1,
    )));

    $pages = array(0 => __('Website main page', 'leyka'),);
    foreach($query->get_posts() as $page) {
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
    if($default_page)
        return $default_page;

    $page = new WP_Query(apply_filters('leyka_default_success_page_query', array(
        'post_type' => 'page',
//        'lang' => 'ru',
        'pagename' => 'thank-you-for-your-donation',
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    )));
    $page = $page->get_queried_object();

    if($page) {

        if($page->post_status != 'publish')
            wp_update_post(array(
                'ID' => $page->ID,
                'post_status' => 'publish',
            ));

        $page = $page->ID;
    } else {

        $page = wp_insert_post(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => 'thank-you-for-your-donation',
            'post_title' => __('Your donation is completed!', 'leyka'),
            'post_content' => __('We heartly thank you for your help!', 'leyka'),
//                '' => __('', 'leyka'),
        ));

        do_action('leyka_default_success_page_created', $page);
    }

    return $page ? $page : 0;
}

function leyka_get_success_page_url() {

    $url = leyka_options()->opt('success_page') ?
        get_permalink(leyka_options()->opt('success_page')) : home_url();

    if( !$url ) // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    
    return $url;
}

function leyka_get_default_failure_page() {

    $default_page = get_option('leyka_failure_page');
    if($default_page)
        return $default_page;

    $page = new WP_Query(apply_filters('leyka_default_failure_page_query', array(
        'post_type' => 'page',
//        'lang' => 'ru',
        'pagename' => 'sorry-donation-failure',
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'private', 'future', 'inherit', 'trash'),
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    )));
    $page = $page->get_queried_object();

    if($page) {

        if($page->post_status != 'publish')
            wp_update_post(array(
                'ID' => $page->ID,
                'post_status' => 'publish',
            ));

        $page = $page->ID;
    } else {

        $page = wp_insert_post(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => 'sorry-donation-failure',
            'post_title' => __('Your donation failed', 'leyka'),
            'post_content' => __('We are deeply sorry, but for some technical reason we failed to receive your donation. Your money are intact. Please try again later!', 'leyka'),
        ));

        do_action('leyka_default_failure_page_created', $page);
    }

    return $page ? $page : 0;
}

function leyka_get_failure_page_url() {

    $url = leyka_options()->opt('failure_page') ?
        get_permalink(leyka_options()->opt('failure_page')) : home_url();

    if( !$url ) // It can be in case when "last posts" is selected for homepage
        $url = home_url();
    
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

function leyka_get_currency_label($currency_code) {

    $currecies = leyka_get_active_currencies();

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
 * @return mixed Array of campaign target info, or false if wrong campaign ID given.
 */
function leyka_get_campaign_target($campaign) {

    $campaign = (int)$campaign;
    if($campaign <= 0)
        return false;

    $campaign = new Leyka_Campaign($campaign);
    if( !$campaign->id )
        return false;

    return array(
        'amount' => $campaign->target,
        'currency' => 'rur', // Currently, target is always in RUR  
    );
}

/**
 * Get campaign collected amount - template tag
 * 
 * @var $campaign integer Campaign ID.
 * @return mixed Array of campaign collected amount info, or false if wrong campaign ID given.
 */
function leyka_get_campaign_collections($campaign) {

    $campaign = (int)$campaign;
    if($campaign <= 0)
        return false;

    $campaign = new Leyka_Campaign($campaign);
    if( !$campaign->id )
        return false;

    return array(
        'amount' => $campaign->get_collected_amount(),
        'currency' => 'rur', // Currently, collections are all in RUR
    );
}


/**
 * Scale
 **/

function leyka_scale_compact($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') )
        $campaign = new Leyka_Campaign($campaign);
        
    $target = intval($campaign->target);
    $curr_label = leyka_get_currency_label('rur');
    $collected = intval($campaign->get_collected_amount());
   
    if($target == 0)
        return;
    
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
    <?php
        $target_f = number_format($target, 0, '.', ' ');
        $collected_f = number_format($collected, 0, '.', ' ');
        
        if($collected == 0){
            printf(__('Needed %s %s', 'leyka'), '<b>'.$target_f.'</b>', $curr_label);
        }
        else {
            printf(__('Collected %s of %s %s', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);
        }
    ?>    
    </div>
</div>
<?php  
}

function leyka_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') )
        $campaign = new Leyka_Campaign($campaign);
        
    $target = intval($campaign->target);
    $curr_label = leyka_get_currency_label('rur');
    $collected = intval($campaign->get_collected_amount());
   
    if($target == 0)
        return;
    
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
    <?php
        $target_f = number_format($target, 0, '.', ' ');
        $collected_f = number_format($collected, 0, '.', ' ');
                
        printf(_x('%s of %s %s', 'Label on ultra-compact scale', 'leyka'), '<b>'.$collected_f.'</b>', '<b>'.$target_f.'</b>', $curr_label);
        
    ?>    
    </span></div>
</div>
<?php  
}

function leyka_fake_scale_ultra($campaign) {
    
    if( !is_a($campaign, 'Leyka_Campaign') )
        $campaign = new Leyka_Campaign($campaign);

    $curr_label = leyka_get_currency_label('rur');
    $collected = number_format(intval($campaign->get_collected_amount()), 0, '.', ' ');?>

<div class="leyka-scale-ultra-fake">
    <div class="leyka-scale-scale">
        <div class="target"> </div>
    </div>
    <div class="leyka-scale-label"><span>
        <?php  printf(_x('Collected: %s', 'Label on ultra-compact fake scale', 'leyka'), "<b>{$collected}</b> {$curr_label}"); ?>
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

    if(empty($type))
        return false;

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

function leyka_settings_complete($settings_tab) {

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

function leyka_min_payment_settings_complete() {

    $pm_list = leyka_get_pm_list(true);
    if( !$pm_list ) {
        return false;
    }

    $gateway_options_valid = array(); // Array of already validated gateways

    foreach(leyka_options()->opt('pm_available') as $pm_full_id) { // Full ID is "gateway_id-pm_id"

        $pm = leyka_get_pm_by_id($pm_full_id, true);
        $pm_full_id = explode('-', $pm_full_id);
        $gateway = leyka_get_gateway_by_id(reset($pm_full_id));

        if( !$pm || !$gateway ) {
            return false;
        }

        foreach($pm->get_pm_options_names() as $option_name) {

            if( !leyka_options()->is_valid($option_name) ) {
                return false;
            }
        }

        if(empty($gateway_options_valid[$gateway->id])) {

            foreach($gateway->get_options_names() as $option_name) {

                if( !leyka_options()->is_valid($option_name) ) {
                    return false;
                }
            }

            $gateway_options_valid[$gateway->id] = true;
        }
    }

    return true;
}

function leyka_campaign_published() {
    return count(get_posts(array('post_type' => Leyka_Campaign_Management::$post_type, 'posts_per_page' => 1))) > 0;
}

/** @return boolean True if at least one Leyka form is currently on the screen, false otherwise */
function leyka_form_is_screening() {

    return
        is_singular(Leyka_Campaign_Management::$post_type) ||
        (is_front_page() && stristr(get_page_template_slug(), 'home-campaign_one') !== false);
}

/** ITV info-widget **/
function leyka_itv_info_widget(){
	//only in Russian as for now
    $locale = get_locale();
    
    if($locale != 'ru_RU')
        return;
    
    
    $src = LEYKA_PLUGIN_BASE_URL.'img/logo-itv.png';
    $domain = parse_url(home_url()); 
    $itv_url = "https://itv.te-st.ru/?leyka=".$domain['host'];
?>
	<div id="itv-card">
        <div class="itv-logo"><a href="<?php echo esc_url($itv_url);?>" target="_blank"><img src="<?php echo esc_url($src);?>"></a></div>
        
        <p>Вам нужна помощь в настройке пожертвований или подключении к платежным системам? Опубликуйте задачу на платформе <a href="<?php echo esc_url($itv_url);?>" target="_blank">it-волонтер</a></p>
                
        <p><a href="<?php echo esc_url($itv_url);?>" target="_blank" class="button">Опубликовать задачу</a></p>
    </div>
<?php
}