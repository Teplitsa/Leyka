<?php /** The default procedure of Donor Metadata Cache total refresh. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

if( !leyka_options()->opt('donor_management_available') ) {
    die;
}

foreach(get_users(['role__in' => [Leyka_Donor::DONOR_USER_ROLE,], 'number' => -1,]) as $donor_user) {

    try {
        Leyka_Donor::calculate_donor_metadata(new Leyka_Donor($donor_user));
    } catch(Exception $ex) {
        continue;
    }

}