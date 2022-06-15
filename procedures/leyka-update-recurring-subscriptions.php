<?php /** Procedure to update recurring subscription statuses */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

// The method should be called no more than once per day:
if(get_transient('leyka_last_update_recurring_subscriptions_status_date') === date('d.m.Y') && !leyka_options()->opt('plugin_debug_mode')) {
    return;
} else {
    set_transient('leyka_last_update_recurring_subscriptions_status_date', date('d.m.Y'), 60*60*24);
}

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 536870912); // 512 Mb, just in case

leyka_update_recurring_subscriptions_statuses();