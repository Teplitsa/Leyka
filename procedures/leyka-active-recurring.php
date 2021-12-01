<?php /** The default procedure of doing all active recurring donations of the currenct date. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

// The method should be called no more than once per day:
if(get_transient('leyka_last_active_recurring_date') === date('d.m.Y') && !leyka_options()->opt('plugin_debug_mode')) {
    return;
} else {
    set_transient('leyka_last_active_recurring_date', date('d.m.Y'), 60*60*24);
}

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 536870912); // 512 Mb, just in case

// Get all active initial donations for the recurring subscriptions:
$current_day = (int)date('j');
$max_days_in_month = (int)date('t');
$current_day_param = ['relation' => 'AND',];
if( !leyka_options()->opt('plugin_debug_mode') ) { // In production mode, rebill only subscriptions older than 1 full day
    $current_day_param[] = ['before' => '-1 day'];
}
$current_day_param[] = $max_days_in_month < 31 && $max_days_in_month === $current_day ? // Last day of short month
    [['day' => $current_day, 'compare' => '>='], ['day' => 31, 'compare' => '<=']] :
    [['day' => (int)date('j')]];

$init_recurring_donations = Leyka_Donations::get_instance()->get([
    'status' => 'funded',
    'recurring_only_init' => true,
    'recurring_active' => true,
    'pm_full_id' => array_keys(leyka_get_active_recurring_pm_list()),
    'get_all' => true,
    'date_query' => $current_day_param,
]);

foreach($init_recurring_donations as $init_recurring_donation) {

    $gateway = leyka_get_gateway_by_id($init_recurring_donation->gateway_id);
    if( !$gateway ) {
        continue;
    }

    // In production mode, check if there have already been rebills for current recurring subscription in current month:
    if( !leyka_options()->opt('plugin_debug_mode') ) {

        $rebill_for_current_month_exists = Leyka_Donations::get_instance()->get_count([
            'status' => 'funded',
            'recurring_rebills_of' => $init_recurring_donation->id,
            'year_month' => date('Ym'), // YYYYMM, e.g. 202105
            'get_single' => true,
        ]);

        if($rebill_for_current_month_exists) {
            continue;
        }

    }

    // All checks are passed, make a new rebill for current recurring subscription:
    $new_recurring_donation = $gateway->do_recurring_donation($init_recurring_donation);
    if( !$new_recurring_donation || is_wp_error($new_recurring_donation) ) {
        /** @todo Log & handle error */
    } else {
    }

}