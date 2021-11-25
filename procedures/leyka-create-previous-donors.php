<?php /** The default procedure to create Donors users from old Donations. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

if( !leyka_options()->opt('donor_management_available') ) {
    die;
}

$procedure_options = leyka_procedures_get_procedure_options([
    'limit' => false,
//    'update_current_donations' => true, // Update current Donations donor ID field. UPD: IT'S ALREADY DONE IN Leyka_Donor::calculate_donor_metadata()
    'process_from_start' => false, // If true, all Donations will be processed anew; if false, only Donations w/o Donor ID set
    'process_only_funded' => true,
    'debug_profiling' => false, // Display time to complete each sub-operation
]);

$procedure_options['limit'] = empty($procedure_options['limit']) || !absint($procedure_options['limit']) ?
    0 : absint($procedure_options['limit']);

$donations = Leyka_Donations::get_instance()->get([ // Get Donations w/o Donor assigned
    'status' => !!$procedure_options['process_only_funded'] ? 'funded' : false,
    'results_limit' => $procedure_options['limit'],
    'donor_id' => !!$procedure_options['process_from_start'] ? false : 0, // "donor_id => false" means no filtering by donor_id
]);

foreach($donations as $donation) {

//    echo '<pre>Processing the donation: '.print_r($donation->id, 1).'</pre>';

    $donor_user_id = Leyka_Donor::create_donor_from_donation($donation, false);

//    echo '<pre>Donor user created: '.print_r($donor_user_id.' (email: '.$donation->donor_email.')', 1).'</pre>';

    if(is_wp_error($donor_user_id)) {
        /** @todo Log the Donor creation error, then continue */
        continue;
    }

    try {

//        $donor_metadata_params = ['process_only_funded_donations' => $procedure_options['process_only_funded']];
        Leyka_Donor::calculate_donor_metadata(new Leyka_Donor($donor_user_id));

//        echo '<pre>Donor metadata calculated for: '.print_r($donor_user_id, 1).'</pre>';

    } catch(Exception $e) {
        /** @todo Log the Donor instancing error, then continue */
        continue;
    }

//    if( !$procedure_options['update_current_donations'] ) {
//        continue;
//    }

    // Update all existing Donations with Donor's email, but w/o Donor ID. Set their Donor ID to proper value:
//    $donor_donations_ids = [];
//    $donor_donations_ids = Leyka_Donations::get_instance()->get([
//        'status' => $procedure_options['process_only_funded'] ? 'funded' : '',
//        'donor_id' => 0,
//        'donor_email' => $donation->donor_email,
//        'get_all' => true,
//        'get_ids_only' => true,
//    ]);
//
//    echo '<pre>Donor donations IDs: '.print_r($donor_donations_ids, 1).'</pre>';
//
//    if( !$donor_donations_ids || count($donor_donations_ids) === 1 ) { // Only original Donation, processed currently
//        continue;
//    }
//
//    global $wpdb;
//
//    if(leyka_get_donations_storage_type() == 'sep') {
//
//        $update_query = $wpdb->prepare(
//            "UPDATE {$wpdb->prefix}leyka_donations SET donor_user_id = %d WHERE ID IN (".implode(',', $donor_donations_ids).")",
//            $donor_user_id
//        );
//
//    } else {
//
//        $update_query = $wpdb->prepare(
//            "UPDATE {$wpdb->prefix}posts SET post_author = %d WHERE ID IN (".implode(',', $donor_donations_ids).")",
//            $donor_user_id
//        );
//
//    }
//
//    echo '<pre>Update query: '.print_r($update_query, 1).'</pre>';
//
//    $rows_updated = $wpdb->query($update_query);
//
//    echo '<pre>Donations updated: '.print_r($rows_updated, 1).'</pre>';

    // Update all existing Donations with Donor's email - END

}