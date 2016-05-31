<?php if( !defined('WPINC') ) die;

// Polylang  plugin compatibility:
if(defined('POLYLANG_VERSION')) {

    add_action('pll_language_defined', function($slug, PLL_Language $cur_lang){

//        if($slug != 'en') {
            load_textdomain('leyka', LEYKA_PLUGIN_DIR."lang/leyka-{$cur_lang->locale}.mo");
//        }

        // Localize options values:
        add_filter('leyka_option_value', function($value, $option_name){

            if($option_name == 'success_page' || $option_name == 'failure_page') {

                // Get ID of a localized page instead of originally set:
                $localized_page_id = empty($_POST['cur_lang']) ? pll_get_post($value) : pll_get_post($value, $_POST['cur_lang']);

                return $localized_page_id ? $localized_page_id : $value;
            }

            $type = leyka_options()->get_type_of($option_name);

            if($type == 'text' || $type == 'textarea' || $type == 'html' || $type == 'rich_html') {
                $value = pll__($value);
            }

            return $value;

        }, 10, 2);

        // Now donations can return their language (a language of their respective campaigns):
        add_filter('leyka_get_unknown_donation_field', function($value, $field, Leyka_Donation $donation){

            if($field == 'lang' || $field == 'campaign_lang') {

                global $polylang;
                return $polylang->model->get_post_language($donation->campaign_id)->slug;
            }

            return $value;

        }, 10, 4);

        // Now campaigns can return their language:
        add_filter('leyka_get_unknown_campaign_field', function($value, $field, Leyka_Campaign $campaign){

            if($field == 'lang' || $field == 'campaign_lang') {

                global $polylang;
                return $polylang->model->get_post_language($campaign->id)->slug;
            }

            return $value;

        }, 10, 4);

        // To make frontend ajax calls localized:
        add_filter('leyka_hidden_donation_form_fields', function($fields){

            if(empty($fields['cur_lang'])) {
                $fields['cur_lang'] = pll_current_language();
            }

            if(empty($fields['cur_locale'])) {
                $fields['cur_locale'] = get_locale();
            }

            return $fields;

        });

        add_action('leyka_init_gateway_redirect_page', function(){

            load_textdomain('leyka', LEYKA_PLUGIN_DIR."lang/leyka-{$_POST['cur_locale']}.mo");

            add_filter('locale', function($locale){
                return $_POST['cur_locale'];
            });

        });


        add_action('init', function(){

            // All localization filters are in places, now create all gateways:

            do_action('leyka_init_actions');

            // Register user-defined strings:
            foreach(leyka_options()->get_options_names() as $option) {

                $option_data = leyka_options()->get_info_of($option);

                if($option_data['type'] == 'text') {
                    pll_register_string($option_data['title'], $option_data['value'], 'leyka');
                } elseif(

                    $option_data['type'] == 'textarea' ||
                    $option_data['type'] == 'html' ||
                    $option_data['type'] == 'rich_html'
                ) {
                    pll_register_string($option_data['title'], leyka_options()->opt($option), 'leyka', true);
                }
            }

        }, 11);

    }, 10, 2);

    // Fallback to native WP language if Polylang doesn't have languages set up:
    add_action('init', function(){

        global $polylang;
        if(empty($polylang) || !pll_languages_list()) {

            add_action('admin_notices', function(){

                echo '<div class="error">
                    <p>'.sprintf(__("<strong>Leyka warning!</strong> Polylang plugin doesn't have any languages installed. Leyka may work strangely due to that. Please go to the <a href='%s'>languages settings page</a> and add at least one language.", 'leyka'), site_url('/wp-admin/options-general.php?page=mlang')).'</p>
                </div>';
            });

            $locale = get_locale();
            $locale = $locale ? $locale : 'ru_RU';

            load_textdomain('leyka', LEYKA_PLUGIN_DIR."lang/leyka-$locale.mo");

            do_action('leyka_init_actions');

        } else {

            if(is_admin() && !did_action('leyka_init_actions')) {

                do_action('leyka_init_actions');

                if(count(pll_languages_list()) > 1) {

                    // Register user-defined strings:
                    foreach(leyka_options()->get_options_names() as $option) {

                        $option_data = leyka_options()->get_info_of($option);

                        if($option_data['type'] == 'text') {
                            pll_register_string($option_data['title'], $option_data['value'], 'leyka');
                        } elseif(
                            $option_data['type'] == 'textarea'
                            || $option_data['type'] == 'html'
                            || $option_data['type'] == 'rich_html'
                        ) {
                            pll_register_string($option_data['title'], leyka_options()->opt($option), 'leyka', true);
                        }
                    }
                }
            }

            add_action('leyka_default_success_page_created', function($page_id){
                /** @todo Get localized strings from PL and update success page params */
            });

            // leyka_donation post type must not be included - there's no need to translate it:
            $leyka_post_types = array(Leyka_Campaign_Management::$post_type);

            if($leyka_post_types != $polylang->options['post_types']) {

                $polylang->options['post_types'] = $polylang->options['post_types'] + $leyka_post_types;
                update_option('polylang', $polylang->options);
            }
        }

    });

} else {

    load_plugin_textdomain('leyka', FALSE, plugin_basename(LEYKA_PLUGIN_DIR).'/lang/');

    add_action('init', function(){
        do_action('leyka_init_actions');
    }, 11);
}