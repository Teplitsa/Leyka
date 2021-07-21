<?php /** The procedure to prepare the Donations data to be read by Google Data Studio (via MySQL data connector). */

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

if( !$wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}leyka_gds_integration_donations_data'") ) {
    die; // Mb, create the Extensiton-specific DB table instead?
}

set_transient('leyka_gds_integration_last_data_preparing_date', date('Y-m-d H:i:s'));

ini_set('max_execution_time', apply_filters('leyka_procedure_php_execution_time', 0, 'gds-integration-procedure'));
set_time_limit(apply_filters('leyka_procedure_php_execution_time', 0, 'gds-integration-procedure'));
ini_set('memory_limit', apply_filters('leyka_procedure_php_memory_limit', 268435456, 'gds-integration-procedure')); // 256 Mb

switch(leyka_options()->opt('gds_integration_donations_date_period')) {
    case '2_months':
        $date_query = date('Y-m-d 00:00:00', strtotime('-2 month')); break;
    case '6_months':
        $date_query = date('Y-m-d 00:00:00', strtotime('-6 month')); break;
    case '1_year':
        $date_query = date('Y-m-d 00:00:00', strtotime('-1 year')); break;
    case 'all':
        $date_query = ''; break;
    case '2_years':
    default:
        $date_query = date('Y-m-d 00:00:00', strtotime('-2 year')); break;
}

$params = apply_filters('leyka_gds_integration_donation_query_params', array(
    'post_type' => Leyka_Donation_Management::$post_type,
    'nopaging' => true,
    'post_status' => 'any',
    'date_query' => $date_query,
));

$wpdb->query("TRUNCATE `{$wpdb->prefix}leyka_gds_integration_donations_data`");

foreach(get_posts($params) as $donation) {

    $donation = new Leyka_Donation($donation);

    $wpdb->insert(
        "{$wpdb->prefix}leyka_gds_integration_donations_data",
        array(
            'ID' => $donation->ID,
            'donation_date' => date('Y-m-d H:i:s', $donation->date_timestamp),
            'payment_type' => $donation->type_label,
            'gateway_title' => $donation->gateway_label,
            'pm_title' => $donation->pm_label,
            'currency_label' => $donation->currency_label,
            'amount' => $donation->amount,
            'amount_total' => $donation->amount_total,
            'status' => $donation->status_label,
            'campaign_title' => $donation->campaign_title,
            'donor_name' => $donation->donor_name,
            'donor_email' => $donation->donor_email,
            'donor_has_account' => absint($donation->donor_id) ? 1 : 0,
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%d',)
    );

}