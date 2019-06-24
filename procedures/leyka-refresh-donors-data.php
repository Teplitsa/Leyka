<?php /**
 * The default procedure of doing all active recurring donations of the currenct date.
 */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

if( !leyka_options()->opt('donor_management_available') ) {
    die;
}

foreach(get_users(array('role__in' => array('donor_regular', 'donor_single'), 'number' => -1, )) as $donor_user) {
    leyka_calculate_donor_metadata($donor_user);
}