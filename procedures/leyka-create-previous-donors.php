<?php /** The default procedure to create Donors users from old Donations. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

if( !leyka_options()->opt('donor_management_available') ) {
    die;
}

//$_REQUEST['number_from'] = empty($_REQUEST['number_from']) || !absint($_REQUEST['number_from']) ?
//    0 : absint($_REQUEST['number_from']);
$_REQUEST['number_to_process'] =  empty($_REQUEST['number_to_process']) || !absint($_REQUEST['number_to_process']) ?
    -1 : absint($_REQUEST['number_to_process']);

$donor_donations = get_posts(array( // Get donations by donor
    'post_type' => Leyka_Donation_Management::$post_type,
    'post_status' => 'funded',
    'posts_per_page' => $_REQUEST['number_to_process'],
    'author__in' => array(0), // Donations w/o Donors assigned
));

//echo '<pre>Total: '.print_r(count($donor_donations), 1).'</pre>';
foreach($donor_donations as $donation) {

    $donation = new Leyka_Donation($donation);

    echo '<pre>'.print_r('Processing the donation: '.$donation->id, 1).'</pre>';

    $donor_user_id = leyka_create_donor_user($donation, 'donor');
    if(is_wp_error($donor_user_id)) {
        /** @todo Log the user creation error, then continue */
        echo '<pre>Error while creating a user: '.print_r($donor_user_id, 1).'</pre>';
        continue;
    } else {
        leyka_calculate_donor_metadata(get_user_by('id', $donor_user_id));
        echo '<pre>'.print_r('Account created: '.$donor_user_id.' created, metadata calculated', 1).'</pre>';
    }

//    echo '<pre>'.print_r($donation_post->ID.' - '.get_post_meta($donation_post->ID, 'leyka_donation_amount', true).' - '.$donation_post->post_author, 1).'</pre>';
}