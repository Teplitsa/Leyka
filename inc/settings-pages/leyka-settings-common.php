<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

add_action('leyka_settings_submit', 'leyka_save_settings');

function leyka_save_settings($settings_stage_id) {

    if(empty($_POST['_leyka_nonce']) || !wp_verify_nonce($_POST['_leyka_nonce'], 'leyka_settings_'.$settings_stage_id)) {
        return;
    }

    $options_names = [];
    $submitted_options_section = !empty($_POST['leyka_options_section']) ? $_POST['leyka_options_section'] : null;

    foreach(leyka_opt_alloc()->get_tab_options($settings_stage_id) as $entry) {

        if(is_array($entry)) {
            
            foreach($entry as $key => $option) {

                if($key === 'section') {

                    if(isset($option['tabs'])) { // Section with tabs

                        foreach($option['tabs'] as $option_tab_name => $tab) {
                            foreach($tab['sections'] as $tab_section) {
                                foreach($tab_section['options'] as $tab_section_option) {

                                    if(leyka_options()->is_template_option($tab_section_option)) {
                                        $tab_section_option = leyka_options()->get_tab_option_full_name(
                                            $option_tab_name,
                                            $tab_section_option
                                        );
                                    }
                                    
                                    $options_names[] = $tab_section_option;

                                }
                            }
                        }

                    } else if($submitted_options_section) {
                        if($submitted_options_section == $option['name']) {
                            $options_names = array_merge($options_names, $option['options']);
                        }
                    } else if($settings_stage_id !== 'payment' || empty($_GET['gateway']) || $_GET['gateway'] === $option['name']) {
                        // For "Payment" settings area - if a gateway settings are being saved, save only this gateway options;
                        // for all other settings areas - save all the options allocated to the area.
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

    if(empty($_POST['_leyka_nonce']) || !wp_verify_nonce($_POST['_leyka_nonce'], 'leyka_settings_payment')) {
        return;
    }

    leyka_save_option('commission');

});