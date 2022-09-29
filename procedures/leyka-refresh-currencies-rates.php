<?php /** Procedure to refresh currencies rates. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

if( !leyka_options()->opt('currencies_rates_auto_refresh') ) {
    die;
}

leyka_refresh_currencies_rates();