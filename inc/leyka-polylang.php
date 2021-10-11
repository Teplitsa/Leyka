<?php if( !defined('WPINC') ) die;

// Polylang  plugin compatibility:
if(defined('POLYLANG_VERSION')) {

    function leyka_pll_do_localization($slug, PLL_Language $cur_lang){

        load_plugin_textdomain('leyka', false, apply_filters('leyka_l10n_mo_folder', WP_LANG_DIR.'/plugins'));

        // Localize options values:
        function leyka_localize_option_value($value, $option_name) {

            if(in_array($option_name, ['success_page', 'failure_page', 'terms_of_service_page', 'pd_terms_page',])) {

                // Get ID of a localized page instead of originally set:
                $localized_page_id = empty($_POST['cur_lang']) ?
                    pll_get_post($value) : pll_get_post($value, $_POST['cur_lang']);

                return $localized_page_id ? $localized_page_id : $value;

            }

            if(in_array(leyka_options()->get_type_of($option_name), ['text', 'textarea', 'html', 'rich_html',])) {
                $value = pll__($value);
            }

            return $value;

        }
        add_filter('leyka_option_value', 'leyka_localize_option_value', 10, 2);

        // Now donations can return their language (a language of their respective campaigns):
        function leyka_localize_unknown_donation_field($value, $field, Leyka_Donation_Base $donation) {

            if($field == 'lang' || $field == 'campaign_lang') {

                global $polylang;
                return $polylang->model->get_post_language($donation->campaign_id)->slug;
            }

            return $value;

        }
        add_filter('leyka_get_unknown_donation_field', 'leyka_localize_unknown_donation_field', 10, 4);

        // Now campaigns can return their language:
        add_filter('leyka_get_unknown_campaign_field', 'leyka_localize_unknown_campaign_field', 10, 4);
        function leyka_localize_unknown_campaign_field($value, $field, Leyka_Campaign $campaign) {

            if($field == 'lang' || $field == 'campaign_lang') {

                global $polylang;
                return $polylang->model->get_post_language($campaign->id)->slug;

            }

            return $value;

        }

        // To make frontend ajax calls localized:
        function leyka_localize_hidden_form_fields($fields) {

            if(empty($fields['cur_lang'])) {
                $fields['cur_lang'] = pll_current_language();
            }

            if(empty($fields['cur_locale'])) {
                $fields['cur_locale'] = get_locale();
            }

            return $fields;

        }
        add_filter('leyka_hidden_donation_form_fields', 'leyka_localize_hidden_form_fields');

        function leyka_localize_gateway_redirect_page() {

            load_plugin_textdomain('leyka', false, apply_filters('leyka_l10n_mo_folder', WP_LANG_DIR.'/plugins'));

            function leyka_get_current_locale($locale){
                return $_POST['cur_locale'];
            }
            add_filter('locale', 'leyka_get_current_locale');

        }
        add_action('leyka_init_gateway_redirect_page', 'leyka_localize_gateway_redirect_page');

    }
    add_action('pll_language_defined', 'leyka_pll_do_localization', 10, 2);

    // If Polylang is active, we need to init all options at once (to localize them).
    // By default, only 'main' & 'templates' opt. groups initialized. Polylang needs all Leyka options to l10n them properly:
    add_filter('leyka_init_options_meta_group', function($initialize_options_meta_group){
        return 'all';
    });

    function leyka_localize_options() {

        if( !function_exists('pll_register_string') ) {
            return;
        }

        // All localization filters are in places, now create all gateways:
        do_action('leyka_init_actions');

        // Register user-defined strings:
        foreach(leyka_options()->get_options_names() as $option_id) {

            $option_data = leyka_options()->get_info_of($option_id);
            $value = leyka_options()->opt($option_id);

            if( !$value && !empty($option_data['default']) ) {
                $value = $option_data['default'];
            }

            if(is_numeric($value)) { // Don't localize the numeric values
                continue;
            }

            if($option_data['type'] == 'text') {
                pll_register_string($option_data['title'], $value, 'leyka');
            } else if(in_array($option_data['type'], ['textarea', 'html', 'rich_html',])) {
                pll_register_string(
                    $option_data['title'],
                    $value,
                    'leyka',
                    true
                );
            }

        }

    }
    add_action('init', 'leyka_localize_options');

    // Fallback to native WP language if Polylang doesn't have languages set up:
    function leyka_pll_languages_not_set(){

        global $polylang;
        if(empty($polylang) || !pll_languages_list()) {

            function leyka_pll_admin_notices_error(){
                echo '<div class="error">
                    <p>'.sprintf(__("<strong>Leyka warning!</strong> Polylang plugin doesn't have any languages installed. Leyka may work strangely due to that. Please go to the <a href='%s'>languages settings page</a> and add at least one language.", 'leyka'), site_url('/wp-admin/options-general.php?page=mlang')).'</p>
                </div>';
            }
            add_action('admin_notices', 'leyka_pll_admin_notices_error');

            load_plugin_textdomain('leyka', false, apply_filters('leyka_l10n_mo_folder', WP_LANG_DIR.'/plugins'));

            do_action('leyka_init_actions');

        } else {

            if(is_admin() && !did_action('leyka_init_actions')) {

                do_action('leyka_init_actions');

                if(count(pll_languages_list()) > 1) {
                    foreach(leyka_options()->get_options_names() as $option) { // Register user-defined strings

                        $option_data = leyka_options()->get_info_of($option);

                        if($option_data['type'] == 'text') {
                            pll_register_string($option_data['title'], $option_data['value'], 'leyka');
                        } elseif(in_array($option_data['type'], ['textarea', 'html', 'rich_html'])) {
                            pll_register_string($option_data['title'], leyka_options()->opt($option), 'leyka', true);
                        }

                    }
                }

            }

//            add_action('leyka_default_success_page_created', function($page_id){
//                /** @todo Get localized strings from PL and update success page params */
//            });

            // leyka_donation post type must not be included - there's no need to translate it:
            $leyka_post_types = [Leyka_Campaign_Management::$post_type];

            if($leyka_post_types != $polylang->options['post_types']) {

                $polylang->options['post_types'] = $polylang->options['post_types'] + $leyka_post_types;
                update_option('polylang', $polylang->options);

            }

        }

    }
    add_action('init', 'leyka_pll_languages_not_set');

} else {

    function leyka_init_actions(){
        do_action('leyka_init_actions');
    }
    add_action('init', 'leyka_init_actions', 11);

}