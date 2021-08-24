<?php /** The procedure to prepare Donations data to be read by Google Data Studio (via MySQL data connector). */

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

require_once leyka_get_wp_core_path().'/wp-load.php';

add_filter('wp_using_themes', function($use_themes){
    return false;
}, 1000);

require_once LEYKA_PLUGIN_DIR.'/procedures/procedures-common.php';

if( !defined('WPINC') ) die;

global $wpdb;

if( !Leyka_Gds_Integration_Extension::get_instance()->_gds_data_table_exists() ) {
    Leyka_Gds_Integration_Extension::get_instance()->_gds_data_table_create();
}

if( // GDS data lines limit is exceeded, abort
    Leyka_Gds_Integration_Extension::get_instance()->get_donations_to_convert_count()
    > Leyka_Gds_Integration_Extension::get_instance()->get_max_gds_allowed_lines()
) {
    die;
}

set_transient('leyka_gds_integration_last_data_preparing_date', date('Y-m-d H:i:s'));

ini_set('max_execution_time', apply_filters('leyka_procedure_php_execution_time', 0, 'gds-integration-procedure'));
set_time_limit(apply_filters('leyka_procedure_php_execution_time', 0, 'gds-integration-procedure'));
ini_set('memory_limit', apply_filters('leyka_procedure_php_memory_limit', 268435456, 'gds-integration-procedure')); // 256 Mb

Leyka_Gds_Integration_Extension::get_instance()->_gds_data_table_clear();

foreach(Leyka_Gds_Integration_Extension::get_instance()->get_donations_to_convert() as $donation) {
    Leyka_Gds_Integration_Extension::get_instance()->_gds_data_table_insert($donation);
}