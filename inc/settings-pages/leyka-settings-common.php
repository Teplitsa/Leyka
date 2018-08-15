<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

//add_action('leyka_settings_beneficiary_submit', 'leyka_save_settings');
//add_action('leyka_settings_payment_submit', 'leyka_save_settings');
//add_action('leyka_settings_currency_submit', 'leyka_save_settings');
//add_action('leyka_settings_email_submit', 'leyka_save_settings');
//add_action('leyka_settings_view_submit', 'leyka_save_settings');
//add_action('leyka_settings_additional_submit', 'leyka_save_settings');

add_action('leyka_settings_submit', 'leyka_save_settings');

function leyka_save_settings($tab_name) {

    $options_names = array();
    foreach(leyka_opt_alloc()->get_tab_options($tab_name) as $entry) {

        if(is_array($entry)) {
            foreach($entry as $key => $option) {
                if($key == 'section') {
                    $options_names = array_merge($options_names, $option['options']);
                } else {
                    $options_names[] = $option;
                }
            }
        } else {
            $options_names[] = $entry;
        }

    }

    foreach($options_names as $name) {
        leyka_save_setting($name);
    }

}