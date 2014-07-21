<?php if( !defined('WPINC') ) die;

// Polylang  plugin compatibility:
if(defined('POLYLANG_VERSION') && function_exists('pll_register_string')) {

    add_action('pll_language_defined', function($slug, $cur_lang){

//    load_plugin_textdomain('leyka', FALSE, plugin_basename(LEYKA_PLUGIN_DIR).'/lang/');

        add_filter('leyka_default_success_page_query', function($params){

            if(empty($params['lang']))
                $params['lang'] = pll_current_language();

            return $params;
        });

        add_filter('leyka_default_failure_page_query', function($params){

            if(empty($params['lang']))
                $params['lang'] = pll_current_language();

            return $params;
        });

        add_filter('leyka_pages_list_query', function($params){

            if(empty($params['lang']))
                $params['lang'] = pll_current_language();

            return $params;
        });

        if($slug != 'en') {
            load_textdomain('leyka', LEYKA_PLUGIN_DIR."lang/leyka-{$cur_lang->locale}.mo");
        }

        do_action('leyka_init_actions');

        add_filter('leyka_option_value', function($value, $option_name){

            if($option_name == 'success_page' || $option_name == 'failure_page') {

                $localized_page_id = pll_get_post($value); // Get ID of localized pages instead of originally set

                return $localized_page_id ? $localized_page_id : $value;
            }

            return pll__($value);
        }, 10, 2);

        // Register user-defined strings:
        foreach(leyka_options()->get_options_names() as $option) {

            $option_data = leyka_options()->get_info_of($option);

            if($option_data['type'] == 'text')
                pll_register_string($option_data['title'], $option_data['value'], 'leyka');

            elseif(
                $option_data['type'] == 'textarea'
             || $option_data['type'] == 'html'
             || $option_data['type'] == 'rich_html'
            )
                pll_register_string($option_data['title'], leyka_options()->opt($option), 'leyka', true);
        }

        add_filter('leyka_pm_description', function($description, $pm_id){
            return pll__($description);
        }, 10, 2);

    }, 10, 2);

} else {

    load_plugin_textdomain('leyka', FALSE, plugin_basename(LEYKA_PLUGIN_DIR).'/lang/');

    add_action('init', function(){

        do_action('leyka_init_actions');
    }, 11);
}