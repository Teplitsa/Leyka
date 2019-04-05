<?php /** Leyka - common utility functions for procedures running. */

function leyka_get_wp_core_path() {

    $current_script_dir = dirname(__FILE__);
    do {
        if(file_exists($current_script_dir.'/wp-load.php')) {
            return $current_script_dir;
        }
    } while($current_script_dir = realpath("$current_script_dir/.."));

    return null;

}

require_once leyka_get_wp_core_path().'/wp-load.php';

add_filter('wp_using_themes', function($use_themes){
    return false;
}, 1000);