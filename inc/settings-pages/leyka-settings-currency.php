<?php if( !defined('WPINC') ) die; // If this file is called directly, abort



add_action('leyka_settings_pre_currency_fields', function(){
//    echo '<pre>'.print_r(leyka_options()->get_values(), TRUE).'</pre>';
});

add_action('leyka_settings_currency_submit', function(){
    
});