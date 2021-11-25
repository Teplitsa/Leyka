<?php /**
 * The conversion procedure of donation data in the DB: from WP_Posts table to the separate one.
 */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

global $wpdb;

$procedure_options = leyka_procedures_get_procedure_options([
    'delete_old_donations' => false,
    'pre_clear_sep_storage' => false,
    'only_funded' => false,
    'limit' => false,
    'debug_profiling' => false, // Display time to complete each sub-operation
]);

//update_option('leyka_donations_storage_type', 'post'); // TODO TMP, for debugging only

function leyka_get_memory_formatted($bytes) {

    $mbytes = $bytes % (1024*1024) ? intval($bytes/(1024*1024)) : 0;
    $bytes -= $mbytes*1024*1024;

    $kbytes = $bytes % 1024 ? intval($bytes/1024) : 0;
    $bytes -= $kbytes*1024;

    return ($mbytes ? $mbytes.'M ' : '').($kbytes ? $kbytes.'K ' : '').$bytes;

}

$time_start = microtime(true);

leyka_procedures_print('Memory before anything: '.leyka_get_memory_formatted(memory_get_usage()).' / '.ini_get('memory_limit'));

if($procedure_options['pre_clear_sep_storage']) {

    $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}leyka_donations_meta");
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}leyka_donations");
    $wpdb->query('SET FOREIGN_KEY_CHECKS=1;');

    update_option('leyka_donations_storage_last_post2sep_id', 0);

}

$query = $wpdb->prepare(
    "SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type=%s ".($procedure_options['only_funded'] ? "AND {$wpdb->prefix}posts.post_status='funded'" : '')." ORDER BY {$wpdb->prefix}posts.ID",
    Leyka_Donation_Management::$post_type
);
if($procedure_options['limit'] > 0) {
    $query .= ' LIMIT 0,'.absint($procedure_options['limit']);
}

$donations_ids = $wpdb->get_col($query);
$total_donations = count($donations_ids);

$recurring_subscriptions = $wpdb->get_results("SELECT {$wpdb->prefix}posts.ID, {$wpdb->prefix}postmeta.meta_value FROM `mlsd_posts` JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.id={$wpdb->prefix}postmeta.post_id WHERE ".($procedure_options['only_funded'] ? "{$wpdb->prefix}posts.post_status='funded' AND" : '')." {$wpdb->prefix}postmeta.meta_key='_chronopay_customer_id' GROUP BY {$wpdb->prefix}postmeta.meta_value ORDER BY {$wpdb->prefix}postmeta.meta_value, {$wpdb->prefix}posts.ID");
foreach($recurring_subscriptions as $key => $values) {

    $recurring_subscriptions[$values->meta_value] = $values->ID;
    unset($recurring_subscriptions[$key]);

}

//leyka_procedures_print('Memory after getting donations IDs: '.get_memory_formatted(memory_get_usage()));

leyka_procedures_print('Converting non-recurring donations (total: '.print_r($total_donations, 1).')');

$process_completed_totally = true;
$donations_processed = 0;

foreach($donations_ids as $donation_id) {

//    leyka_procedures_print('Memory before donation ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

    leyka_procedures_print("Processing the donation #{$donations_processed}/{$total_donations}... ", 0);

//    if($donation_id <= get_option('leyka_donations_storage_last_post2sep_id')) {
    if($wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->prefix}leyka_donations WHERE ID=".$donation_id)) {

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

//    leyka_procedures_print('Memory after donation ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

}

// Delete all non-converted donations, if needed:
if($procedure_options['only_funded'] && $procedure_options['delete_old_donations']) {

    leyka_procedures_print("Deleting non-converted donations...");

    $donations_ids = $wpdb->get_col($wpdb->prepare("SELECT {$wpdb->prefix}posts.ID as ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type=%s AND post_status != %s", Leyka_Donation_Management::$post_type, 'funded'));

    $total_donations = count($donations_ids);
    $donations_processed = 0;

    foreach($donations_ids as $donation_id) {

        $wpdb->delete($wpdb->postmeta, ['post_id' => $donation_id], ['%d']);
        $wpdb->delete($wpdb->posts, ['ID' => $donation_id], ['%d']);

        $donations_processed++;

        leyka_procedures_print("Donation deleted (".round($donations_processed*100.0/$total_donations, 3)."% finished).");

    }

    leyka_procedures_print("All non-converted donations deleted.");

}

//if($process_completed_totally) { // TODO TMP, 4 dbg only
//    update_option('leyka_donations_storage_type', 'sep');
//} else {
//    update_option('leyka_donations_storage_type', 'sep-incompleted');
//}
update_option('leyka_donations_storage_type', 'sep'); // TODO TMP, 4 dbg only

leyka_procedures_print('Donations transferring finished.');

leyka_procedures_print('Memory '.leyka_get_memory_formatted(memory_get_usage()));

$total_time_sec = microtime(true) - $time_start;
$total_time_min = intval($total_time_sec/60);

leyka_procedures_print('Total execution time: '.($total_time_min ? "$total_time_min min, ".round($total_time_sec % $total_time_min, 2).' sec' : round($total_time_sec, 2).' sec'));

/**
 * Convert the donation from Post-based to separate-storage-based.
 *
 * @param $donation_id integer
 * @return boolean
 */
function leyka_change_donation_storage_type_post2sep($donation_id) {

    global $wpdb, $procedure_options, $recurring_subscriptions;

    // 1. Main donation data insertion:
//    leyka_procedures_print('Memory before processing ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));
    $donation_time_current_sec = $donation_time_total_sec = microtime(true);

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

    if($procedure_options['debug_profiling']) {

        leyka_procedures_print('Getting the initial d post: '.(microtime(true) - $donation_time_current_sec));
        $donation_time_current_sec = microtime(true);

    }

    $err_log_fp = fopen('error.log', 'a+');

    if( !$donation_post_data ) {

        leyka_procedures_print('the donation is not found.');
        return false;

    }

    // Donation metas: status_log, donor_email_date, donor_comment, donor_subscribed, donor_subscription_email, manangers_emails_date, gateway_response, init_recurring_donation_id, recurring_active, recurring_cancel_date, + ALL EXISTING CUSTOM METAS

    $query = "INSERT INTO {$wpdb->prefix}leyka_donations (`ID`, `campaign_id`, `status`, `payment_type`, `date_created`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`,`donor_name`, `donor_email`, `donor_user_id`) VALUES ";

    $donation_post_meta = $wpdb->get_results($wpdb->prepare("SELECT `meta_key`,`meta_value` FROM {$wpdb->prefix}postmeta WHERE `post_id`=%d", $donation_post_data['ID']), ARRAY_A);

    if($procedure_options['debug_profiling']) {

        leyka_procedures_print('Getting the initial d post metas: '.(microtime(true) - $donation_time_current_sec));
        $donation_time_current_sec = microtime(true);

    }

    if( !$donation_post_meta ) {

        fputs($err_log_fp, "{$donation_post_data['ID']} - no donation meta found.\n");
        return false;

    }

    foreach($donation_post_meta as $key => $meta) { // Clean up meta fields names

        $donation_post_meta[ str_replace(['leyka_', '_leyka_'], '', $meta['meta_key']) ] = $meta['meta_value'];
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

    $query_values = "\n({$donation_post_data['ID']},{$donation_post_meta['campaign_id']},'{$donation_post_data['post_status']}','{$donation_post_meta['payment_type']}','{$donation_post_data['post_date']}',{$donation_post_meta['gateway']},'{$donation_post_meta['payment_method']}','{$donation_post_meta['donation_currency']}',{$donation_post_meta['donation_amount']},{$donation_post_meta['donation_amount_total']},{$donation_post_meta['main_curr_amount']},{$donation_post_meta['main_curr_amount_total']},'{$donation_post_meta['donor_name']}','{$donation_post_meta['donor_email']}','{$donation_post_data['post_author']}')";

    $donation_post_data['payment_type'] = $donation_post_meta['payment_type']; // Save the payment type for further usage

    // From now on, these data fields are not metas anymore, but main object attributes:
    foreach(['campaign_id','payment_type','gateway','payment_method','donation_currency','donation_amount','donation_amount_total','main_curr_amount','main_curr_amount_total','donor_name','donor_email',] as $key) {
        unset($donation_post_meta[$key]);
    }

    $query_values = rtrim($query.$query_values, ',');
    if($wpdb->query($query_values) === false) {

        ob_start();
        echo "ERROR migrating donation ID={$donation_id}: ".$query_values."\n\n";
        fputs($err_log_fp, ob_get_clean());
        fclose($err_log_fp);

        return false;

    }

    if($procedure_options['debug_profiling']) {

        leyka_procedures_print('Inserting the d sep main data: '.(microtime(true) - $donation_time_current_sec));
        $donation_time_current_sec = microtime(true);

    }

//    leyka_procedures_print('Memory after inserting main data for ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));
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

//        leyka_procedures_print('Memory before recurring d ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

        $old_ver_donation = new Leyka_Donation_Post($donation_post_data['ID']);

//        leyka_procedures_print('The d ('.$donation_post_data['ID'].') is recurring - getting old post instance: '.(microtime(true) - $donation_time_current_sec));
//        $donation_time_current_sec = microtime(true);

//        leyka_procedures_print('Memory after getting old ver. recurring d ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

        $customer_id = $old_ver_donation->chronopay_customer_id;

//        leyka_procedures_print("D ID: {$donation_post_data['ID']}, customer ID: ".$customer_id.", init D ID: ".(empty($recurring_subscriptions[$customer_id]) ? '-' : $recurring_subscriptions[$customer_id]));

        if($customer_id && !empty($recurring_subscriptions[$customer_id])) { // Init recurring donation ID found
            $donation_post_meta['init_recurring_donation_id'] = $recurring_subscriptions[$customer_id];
        } else {

            $donation_post_meta['init_recurring_donation_id'] = 0;

            ob_start();
            echo "META ERROR: can't find init recurring donation for Chronopay Customer ID {$customer_id}\n\n";
            fputs($err_log_fp, ob_get_clean());

        }

//        leyka_procedures_print('The d is recurring - getting init recurring d post instance: '.(microtime(true) - $donation_time_current_sec));
//        $donation_time_current_sec = microtime(true);

//        leyka_procedures_print('Memory after getting init recurring d ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

//        leyka_procedures_print('Memory after recurring donation ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

    }

    if( !empty($donation_post_meta['_is_subscribed']) ) { // Rename the donations meta from miloserdie.ru format to Leyka one
        $donation_post_meta['donor_subscribed'] = 1;
    }

    // Don't insert empty meta fields:
    if(empty($donation_post_meta['recurrents_cancel_date'])) {
        unset($donation_post_meta['recurrents_cancel_date']);
    } else { // Rename "recurrents_cancel_date" meta to "recurring_cancel_date"

        $donation_post_meta['recurring_cancel_date'] = $donation_post_meta['recurrents_cancel_date'];
        unset($donation_post_meta['recurrents_cancel_date']);

    }
    if(empty($donation_post_meta['donor_email_date'])) {
        unset($donation_post_meta['donor_email_date']);
    }
    if(empty($donation_post_meta['managers_emails_date'])) {
        unset($donation_post_meta['managers_emails_date']);
    }

    // Don't transfer the unneeded post meta:
    foreach(['_edit_last', '_edit_lock', '_gapp_post_views', '_wp_desired_post_slug', '_wp_trash_meta_status', '_wp_trash_meta_time', '_yoast_wpseo_content_score', '_yoast_wpseo_is_cornerstone', '_count_view', 'views', '_is_subscribed'] as $key) {
        unset($donation_post_meta[$key]);
    }

    if($donation_post_meta) {

        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}leyka_donations_meta (`donation_id`,`meta_key`,`meta_value`) VALUES ");

        foreach($donation_post_meta as $key => $value) {
            $query .= $wpdb->prepare("(%d, %s, %s),", $donation_post_data['ID'], $key, $value);
        }

        $query = rtrim($query, ',');
        if($wpdb->query($query) === false) {

            ob_start();
            echo "META ERROR: ".$query."\n\n";
            fputs($err_log_fp, ob_get_clean());

        }

    }

    if($procedure_options['debug_profiling']) {

        leyka_procedures_print('Inserting the d sep metas: '.(microtime(true) - $donation_time_current_sec));
        $donation_time_current_sec = microtime(true);

    }

    fclose($err_log_fp);
//    leyka_procedures_print('Memory after processing ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));

    // 2. Donation metas insertion - DONE

    // 3. Delete the old (post-stored) donation
    if($procedure_options['delete_old_donations']) {

//        wp_delete_post($donation_id, true);
        $wpdb->delete($wpdb->postmeta, ['post_id' => $donation_post_data['ID']], ['%d']);
        $wpdb->delete($wpdb->posts, ['ID' => $donation_post_data['ID']], ['%d']);

//        leyka_procedures_print('Memory after deleting original ID='.$donation_id.': '.get_memory_formatted(memory_get_usage()));
        if($procedure_options['debug_profiling']) {
            leyka_procedures_print('Deleting the old d post: '.(microtime(true) - $donation_time_current_sec));
            leyka_procedures_print('D conversion total time: '.(microtime(true) - $donation_time_total_sec));
        }

    }

    return true;

}