<?php /**
 * The conversion procedure of donation data in the DB: from WP_Posts table to the separate one.
 */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

global $wpdb;

$procedure_options = leyka_procedures_get_procedure_options(array(
    'migrate_donors' => false, /** @todo Migrate as donation_meta "donor_user_id" */
    'delete_old_donations' => false,
    'pre_clear_sep_storage' => false,
    'limit' => false,
));

//update_option('leyka_donations_storage_type', 'post'); // TMP, for debugging only

$time_start = microtime(true);

leyka_procedures_print('Memory before anything: '.memory_get_usage(true));

if($procedure_options['pre_clear_sep_storage']) {

    $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}leyka_donations_meta");
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}leyka_donations");
    $wpdb->query('SET FOREIGN_KEY_CHECKS=1;');

    update_option('leyka_donations_storage_last_post2sep_id', 0);

}

$query = $wpdb->prepare(
    "SELECT {$wpdb->prefix}posts.ID as ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type=%s ORDER BY ID",
    Leyka_Donation_Management::$post_type
);
if($procedure_options['limit'] > 0) {
    $query .= ' LIMIT 0,'.absint($procedure_options['limit']);
}

$donations_ids = $wpdb->get_col($query);
$total_donations = count($donations_ids);

leyka_procedures_print('Total donations: '.print_r($total_donations, 1));

$process_completed_totally = true;
$donations_processed = 0;

foreach($donations_ids as $donation_id) {

    leyka_procedures_print("Processing the donation #{$donations_processed}/{$total_donations}... ", 0);

    if($donation_id <= get_option('leyka_donations_storage_last_post2sep_id')) {

        leyka_procedures_print("donation #{$donation_id} already migrated - skipping it. ");
        continue;

    }

    if(leyka_change_donation_storage_type_post2sep($donation_id)) {

        $donations_processed++;
        update_option('leyka_donations_storage_last_post2sep_id', $donation_id);

        leyka_procedures_print("donation inserted (".round($donations_processed*100.0/$total_donations, 3)."% finished).");

    } else {
        $process_completed_totally = $process_completed_totally & false;
    }

}

if($process_completed_totally) {
    update_option('leyka_donations_storage_type', 'sep');
} else {
    update_option('leyka_donations_storage_type', 'sep-incompleted');
}

leyka_procedures_print('Donations transferring finished.');

leyka_procedures_print('Memory '.memory_get_usage(true));

$total_time_sec = microtime(true) - $time_start;
$total_time_min = intval($total_time_sec/60);

leyka_procedures_print('Total execution time: '.($total_time_min ? "$total_time_min min, ".round($total_time_sec % $total_time_min, 2).' sec' : round($total_time_sec, 2)." sec"));

/**
 * Convert the donation from Post-based to separate-storage-based.
 *
 * @param $donation_id integer
 * @return boolean
 */
function leyka_change_donation_storage_type_post2sep($donation_id) {

    global $wpdb, $procedure_options;

    // 1. Main donation data insertion:

    $query = $wpdb->prepare(
        "SELECT 
            {$wpdb->prefix}posts.`ID`, {$wpdb->prefix}posts.`post_title`, {$wpdb->prefix}posts.`post_status`,
            {$wpdb->prefix}posts.`post_date`, {$wpdb->prefix}posts.`post_parent`, {$wpdb->prefix}posts.`post_author` 
        FROM {$wpdb->prefix}posts 
        WHERE {$wpdb->prefix}posts.`post_type`=%s AND {$wpdb->prefix}posts.`ID`=%d",
        Leyka_Donation_Management::$post_type,
        $donation_id
    );
    $donation_post_data = $wpdb->get_row($query, ARRAY_A);

    $err_log_fp = fopen('error.log', 'a+');

    if( !$donation_post_data ) {

        leyka_procedures_print("the donation is not found.");
        return false;

    }

    // Donation metas: status_log, donor_email_date, donor_comment, donor_subscribed, donor_subscription_email, manangers_emails_date, gateway_response, init_recurring_donation_id, recurring_active, recurring_cancel_date, + ALL EXISTING CUSTOM METAS

    $query = "INSERT INTO {$wpdb->prefix}leyka_donations (`ID`, `campaign_id`, `status`, `payment_type`, `date_created`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`,`donor_name`, `donor_email`) VALUES ";

    $donation_post_meta = $wpdb->get_results($wpdb->prepare("SELECT `meta_key`,`meta_value` FROM {$wpdb->prefix}postmeta WHERE `post_id`=%d", $donation_post_data['ID']), ARRAY_A);

    if( !$donation_post_meta ) {

        fputs($err_log_fp, "{$donation_post_data['ID']} - no donation meta found.\n");
        return false;

    }

    foreach($donation_post_meta as $key => $meta) { // Clean up meta fields names

        $donation_post_meta[ str_replace(array('leyka_', '_leyka_'), '', $meta['meta_key']) ] = $meta['meta_value'];
        unset($donation_post_meta[$key]);

    }

    if(empty($donation_post_meta['campaign_id'])) {

        fputs($err_log_fp, "{$donation_post_data['ID']} - no campaign_id meta.\n");
        return false;

    } else if(empty($donation_post_meta['donation_amount'])) {

        fputs($err_log_fp, "{$donation_post_data['ID']} - no donation_amount meta.\n");
        return false;

    }

    $donation_post_meta['donor_name'] = empty($donation_post_meta['donor_name']) ? '' : $donation_post_meta['donor_name'];
    $donation_post_meta['donor_email'] = empty($donation_post_meta['donor_email']) ? '' : $donation_post_meta['donor_email'];

    $donation_post_meta['payment_type'] = empty($donation_post_meta['payment_type']) ? 'single' : $donation_post_meta['payment_type'];
    $donation_post_meta['gateway'] = empty($donation_post_meta['gateway']) ? "'correction'" : "'".$donation_post_meta['gateway']."'";
    $donation_post_meta['donation_currency'] = empty($donation_post_meta['donation_currency']) ?
        'RUB' : ($donation_post_meta['donation_currency'] == 'rur' ? 'RUB' : $donation_post_meta['donation_currency']);
    $donation_post_meta['donation_amount'] = $donation_post_meta['donation_amount'] > 0 ? round($donation_post_meta['donation_amount'], 2) : 0.0;
    $donation_post_meta['donation_amount_total'] = empty($donation_post_meta['donation_amount_total']) ?
        $donation_post_meta['donation_amount'] : round($donation_post_meta['donation_amount_total'], 2);
    $donation_post_meta['main_curr_amount'] = empty($donation_post_meta['main_curr_amount']) ?
        $donation_post_meta['donation_amount'] : round($donation_post_meta['main_curr_amount'], 2);
    $donation_post_meta['main_curr_amount_total'] = empty($donation_post_meta['main_curr_amount_total']) ?
        $donation_post_meta['donation_amount_total'] : round($donation_post_meta['main_curr_amount_total'], 2);

    $query_values = "\n({$donation_post_data['ID']},{$donation_post_meta['campaign_id']},'{$donation_post_data['post_status']}','{$donation_post_meta['payment_type']}','{$donation_post_data['post_date']}',{$donation_post_meta['gateway']},'{$donation_post_meta['payment_method']}','{$donation_post_meta['donation_currency']}',{$donation_post_meta['donation_amount']},{$donation_post_meta['donation_amount_total']},{$donation_post_meta['main_curr_amount']},{$donation_post_meta['main_curr_amount_total']},'{$donation_post_meta['donor_name']}','{$donation_post_meta['donor_email']}')";

    $donation_post_data['payment_type'] = $donation_post_meta['payment_type']; // Save the payment type for further usage

    // From now on, these data fields are not metas anymore, but main object attributes:
    foreach(array('campaign_id','payment_type','gateway','payment_method','donation_currency','donation_amount','donation_amount_total','main_curr_amount','main_curr_amount_total','donor_name','donor_email',) as $key) {
        unset($donation_post_meta[$key]);
    }

    $query_values = rtrim($query.$query_values, ',');
    if($wpdb->query($query_values) === false) {

        ob_start();
        echo "ERROR migrating donation ID={$donation_id}: ".$query_values."\n\n";
        fputs($err_log_fp, 'ERROR: '.ob_get_clean());
        fclose($err_log_fp);

        return false;

    }
    // 1. Main donation data insertion - DONE

    // 2. Donation metas insertion:

    $donation_post_meta['payment_title'] = $donation_post_data['post_title'];

//    if($procedure_options['migrate_donors']) { /** @todo Donors migration for sep-based storage - testing needed. */
//        if( !empty($donation_post_data['post_author']) ) {
//            $donation_post_meta['donor_user_id'] = $donation_post_data['post_author'];
//        }
//    }

    if(isset($donation_post_meta['_status_log'])) {

        $last_date_funded = 0;
        foreach((array)maybe_unserialize($donation_post_meta['_status_log']) as $status_change) {

            if(empty($status_change['status']) || empty($status_change['date'])) {
                break;
            }

            if($status_change['status'] === 'funded' && $status_change['date'] > $last_date_funded) {
                $last_date_funded = $status_change['date'];
            }

        }

        if($last_date_funded) {
            $donation_post_meta['date_funded'] = $last_date_funded;
        }

    }

    if( !empty($donation_post_meta['rebilling_is_active']) ) { // Rename the "rebilling_is_active" meta to "recurring_active"

        // WARNING: ATM this field works only for active recurring scheme gateways (Yandex.Kassa)
        unset($donation_post_meta['rebilling_is_active']);
        $donation_post_meta['recurring_active'] = 1;

    }

    // Find and save $donation_post_meta['init_recurring_donation_id'] for each "rebill"-type donation
    if($donation_post_data['payment_type'] === 'rebill') {

        $old_ver_donation = new Leyka_Donation($donation_post_data['ID']);
        $init_recurring_donation = Leyka_Donation::get_init_recurring_donation($old_ver_donation);

        $donation_post_meta['init_recurring_donation_id'] = $init_recurring_donation ? $init_recurring_donation->id : 0;

    }

    // Don't insert empty meta fields:
    if(empty($donation_post_meta['recurrents_cancel_date'])) {
        unset($donation_post_meta['recurrents_cancel_date']);
    } else { // Rename "recurrents_cancel_date" meta to "recurring_cancel_date"

        $donation_post_meta['recurring_cancel_date'] = $donation_post_meta['recurrents_cancel_date'];
        unset($donation_post_meta['recurrents_cancel_date']);

    }
    if(empty($donation_post_meta['_donor_email_date'])) {
        unset($donation_post_meta['_donor_email_date']);
    }
    if(empty($donation_post_meta['_managers_emails_date'])) {
        unset($donation_post_meta['_managers_emails_date']);
    }
    unset( // Don't transfer the unneeded post meta
        $donation_post_meta['_edit_last'], $donation_post_meta['_edit_lock'], $donation_post_meta['_gapp_post_views'],
        $donation_post_meta['_wp_desired_post_slug'], $donation_post_meta['_wp_trash_meta_status'],
        $donation_post_meta['_wp_trash_meta_time'], $donation_post_meta['_yoast_wpseo_content_score'],
        $donation_post_meta['_yoast_wpseo_is_cornerstone'], $donation_post_meta['_count_view'], $donation_post_meta['views'],
        $donation_post_meta['_is_subscribed']
    );

    foreach($donation_post_meta as $key => $value) {

        if(stripos($key, '_') === 0) {
            $key = mb_substr($key, 1);
        }

        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}leyka_donations_meta (`donation_id`,`meta_key`,`meta_value`) VALUES (%d, %s, %s)", $donation_post_data['ID'], $key, $value);

        if($wpdb->query($query) === false) {

            ob_start();
            echo "META ERROR: ".$query."\n\n";
            fputs($err_log_fp, "META ERROR: ".ob_get_clean());
            fclose($err_log_fp);

        }

    }

    fclose($err_log_fp);

    // 2. Donation metas insertion - DONE

    // 3. Delete the old (post-stored) donation
    if($procedure_options['delete_old_donations']) {
        wp_delete_post($donation_id, true);
    }

    return true;

}