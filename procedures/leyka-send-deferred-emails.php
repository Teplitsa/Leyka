<?php /** The procedure to send emails with some time passed after some event. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

// The method should be called no more than once per day:
//if(get_transient('leyka_last_active_recurring_date') === date('d.m.Y') && !LEYKA_DEBUG) {
//    return;
//} else {
//    set_transient('leyka_last_active_recurring_date', date('d.m.Y'), 60*60*24);
//}

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

