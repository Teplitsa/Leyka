<?php /** Leyka - common utility functions for procedures running. */

if( !function_exists('leyka_get_wp_core_path') ) {
    function leyka_get_wp_core_path() {

        $current_script_dir = dirname(__FILE__);
        do {
            if(file_exists($current_script_dir.'/wp-config.php')) {

                require_once $current_script_dir.'/wp-config.php';

                return ABSPATH;

            }
        } while($current_script_dir = realpath("$current_script_dir/.."));

        return null;

    }
}

if( !defined('WP_USE_THEMES') ) {
	define('WP_USE_THEMES', false);
}

require_once leyka_get_wp_core_path().'/wp-load.php';

add_filter('wp_using_themes', function($use_themes){
    return false;
}, 1000);

/** @return boolean True if the script is being run from command line, false if it's being called from browser. */
function leyka_procedures_is_cli() {
    return php_sapi_name() === 'cli';
}

/**
 * @param $expression string
 * @param $add_new_line boolean
 */
function leyka_procedures_print($expression, $add_new_line = true) {

    if( !$expression ) {
        return;
    }

    $add_new_line = !!$add_new_line;

    if(leyka_procedures_is_cli()) {
        echo print_r($expression, 1).($add_new_line ? "\n" : '');

    } else {
        echo $add_new_line ? '<pre>'.print_r($expression, 1).'</pre>' : print_r($expression, 1);
    }

    @ob_flush();

}

/**
 * An utitlity function to parse & get procedure options array, irrelevant of procedure call type (CLI or HTTP).
 *
 * @param $default_options array
 * @return array
 */
function leyka_procedures_get_procedure_options(array $default_options = []) {

    $options_keys = [];
    foreach($default_options as $name => $value) {

        if(rtrim($name, ':') == $name) {
            $name .= ':';
        }

        $options_keys[] = $name;

    }

    return wp_parse_args(leyka_procedures_is_cli() ? getopt('', $options_keys) : $_GET, $default_options);

}

set_time_limit(0);
ini_set('memory_limit','1024M');