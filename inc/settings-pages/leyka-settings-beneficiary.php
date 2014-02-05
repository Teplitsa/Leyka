<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

// Add fields to the Beneficiary options tab:
add_action('leyka_settings_pre_beneficiary_fields', function(){
//    echo '<pre>'.print_r(leyka_options()->get_values(), TRUE).'</pre>';
});

add_action('leyka_settings_beneficiary_submit', function(){
//    echo '<pre>'.print_r($_POST, TRUE).'</pre>';
//
//    foreach(leyka_opt_alloc()->get_tab_options('beneficiary') as $option) {
//        
//        if( !empty($option['section']) ) {
//            
//            foreach($option['section']['options'] as $option_name) {
//                
//                echo '<pre>' . print_r($option_name, TRUE) . '</pre>';
//
//                if(leyka_options()->is_required($option_name) && empty($_POST['leyka_'.$option_name])) {
//                    echo '<pre>' . print_r($option_name.' - empty', TRUE) . '</pre>';
//                }
//            }
//        } else {

//            echo '<pre>' . print_r($option_name, TRUE) . '</pre>';
//
//            if(leyka_options()->is_required($option_name) && empty($_POST['leyka_'.$option_name])) {
//                echo '<pre>' . print_r($option_name.' - empty', TRUE) . '</pre>';
//            }
//        }
//    }

});