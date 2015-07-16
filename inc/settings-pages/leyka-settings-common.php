<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

add_action('leyka_settings_beneficiary_submit', 'leyka_save_settings');
add_action('leyka_settings_payment_submit', 'leyka_save_settings');
add_action('leyka_settings_currency_submit', 'leyka_save_settings');
add_action('leyka_settings_email_submit', 'leyka_save_settings');
add_action('leyka_settings_view_submit', 'leyka_save_settings');
add_action('leyka_settings_additional_submit', 'leyka_save_settings');

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

        $option_type = leyka_options()->get_type_of($name);

        if($option_type == 'checkbox') {
            leyka_options()->opt($name, isset($_POST["leyka_$name"]) ? 1 : 0);
        } elseif($option_type == 'multi_checkbox') {

            if(isset($_POST["leyka_$name"]) && leyka_options()->opt($name) != $_POST["leyka_$name"]) {
                leyka_options()->opt($name, (array)$_POST["leyka_$name"]);
            }

        } elseif($option_type == 'html' || $option_type == 'rich_html') {

            if(isset($_POST["leyka_$name"]) && leyka_options()->opt($name) != $_POST["leyka_$name"]) {
                leyka_options()->opt($name, esc_attr(stripslashes($_POST["leyka_$name"])));
            }

        } else {

            if(isset($_POST["leyka_$name"]) && leyka_options()->opt($name) != $_POST["leyka_$name"]) {
                leyka_options()->opt($name, esc_attr(stripslashes($_POST["leyka_$name"])));
            }
        }
    }
}