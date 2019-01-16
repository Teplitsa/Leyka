<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

//add_action('leyka_settings_beneficiary_submit', 'leyka_save_settings');
//add_action('leyka_settings_currency_submit', 'leyka_save_settings');
//add_action('leyka_settings_email_submit', 'leyka_save_settings');
//add_action('leyka_settings_view_submit', 'leyka_save_settings');
//add_action('leyka_settings_additional_submit', 'leyka_save_settings');

add_action('leyka_settings_submit', 'leyka_save_settings');

function leyka_save_settings($tab_name) {

    $options_names = array();
    foreach(leyka_opt_alloc()->getTabOptions($tab_name) as $entry) {

        if(is_array($entry)) {
            
            foreach($entry as $key => $option) {
                if($key === 'section') {
                    
                    // tabbed section
                    if(isset($option['tabs'])) {
                        
                        foreach($option['tabs'] as $tab_name => $tab) {
                            foreach($tab['sections'] as $tab_section_name => $tab_section) {
                                foreach($tab_section['options'] as $tab_section_option) {
                                    
                                    $prefix = leyka_options()->get_common_option_prefix($tab_section_option, $tab_name);
                                    if($prefix) {
                                        $tab_section_option = leyka_options()->get_common_option_full_name($prefix, $tab_section_option);
                                    }
                                    
                                    $options_names[] = $tab_section_option;
                                }
                            }
                        }
                        
                    }
                    else {
                        $options_names = array_merge($options_names, $option['options']);
                    }
                    
                } else {
                    $options_names[] = $option;
                }
            }
            
        } else {
            $options_names[] = $entry;
        }

    }

    foreach($options_names as $name) {
        leyka_save_option($name);
    }

}

add_action('leyka_settings_payment_submit', function(){
    leyka_save_option('commission');
});