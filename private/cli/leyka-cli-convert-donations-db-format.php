<?php
/* Service actions */
set_time_limit(0);
ini_set('memory_limit','1024M');

try {

    include('cli_common.php');

    $time_start = microtime(true);

	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));

	Leyka_Procedure_Convert_Donations_Format::convert();
	fwrite(STDOUT, "Donations transferring finished\n");

	fwrite(STDOUT, "All done\n\n");
	fwrite(STDOUT, 'Memory '.memory_get_usage(true).chr(10));

	$total_time_sec = microtime(true) - $time_start;
	$total_time_min = intval($total_time_sec/60);
	fwrite(STDOUT, 'Total execution time: '.($total_time_min ? "$total_time_min min, ".($total_time_sec % $total_time_min)." sec" : $total_time_sec." sec")."\n\n");

}
catch (TstNotCLIRunException $ex) {
	echo $ex->getMessage() . "\n";
}
catch (TstCLIHostNotSetException $ex) {
	echo $ex->getMessage() . "\n";
}
catch (Exception $ex) {
	echo $ex;
}

class Leyka_Procedure_Convert_Donations_Format {

    public static function convert() {

        global $wpdb;

        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type=%s", Leyka_Donation_Management::$post_type);
//        $query .= " LIMIT 0,10000"; // For debugging only

        $donations_ids = $wpdb->get_col($query);
        $total_donations = count($donations_ids);

        echo '<pre>Total donations: '.print_r($total_donations, 1).'</pre>'."\n";
        ob_flush();

        $process_completed_totally = true;
        $donations_processed = 0;

        foreach($donations_ids as $donation_id) {

            echo "Processing the donation #{$donations_processed}/{$total_donations}... ";
            ob_flush();

            if(self::processDonation($donation_id)) {

                $donations_processed++;
                echo "donation inserted (".round($donations_processed*100.0/$total_donations, 3)."% finished).\n";
                ob_flush();

            } else {
                $process_completed_totally = false;
            }

        }

        if($process_completed_totally) {
            update_option('leyka_donations_storage_type', 'sep');
        } else {
            update_option('leyka_donations_storage_type', 'sep-incompleted');
        }

    }

    public static function processDonation($donation_id) {

        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT `ID`,`post_title`,`post_status`,`post_date`,`post_parent` FROM {$wpdb->prefix}posts WHERE `post_type`=%s AND ID=%d",
            Leyka_Donation_Management::$post_type, $donation_id
        );
        $donation_post_data = $wpdb->get_row($query, ARRAY_A);

        $err_log_fp = fopen('error.log', 'a+');

        if( !$donation_post_data ) {

            echo "the donation is not found.\n";
            return false;

        }

         // Donation metas: status_log, donor_email_date, donor_comment, donor_subscribed, donor_subscription_email, manangers_emails_date, gateway_response, init_recurring_donation_id, recurring_active, recurring_cancel_date, + ALL EXISTING CUSTOM METAS

        $query = "INSERT INTO {$wpdb->prefix}leyka_donations (`ID`, `campaign_id`, `status`, `payment_type`, `date_created`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`, `donor_name`, `donor_email`) VALUES ";

        $donation_post_meta = $wpdb->get_results($wpdb->prepare("SELECT `meta_key`,`meta_value` FROM {$wpdb->prefix}postmeta WHERE `post_id`=%d", $donation_post_data['ID']), ARRAY_A);

        if( !$donation_post_meta ) {

            fputs($err_log_fp, "{$donation_post_data['ID']} - no donation meta.\n");
            return false;

        }

        foreach($donation_post_meta as $key => $meta) { // Clean up meta fields names

            $donation_post_meta[ str_replace(array('leyka_', '_leyka_'), '', $meta['meta_key']) ] = $meta['meta_value'];
            unset($donation_post_meta[$key]);

        }

        if(empty($donation_post_meta['campaign_id'])) {

            fputs($err_log_fp, "{$donation_post_data['ID']} - no campaign_id meta.\n");
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

        // Save the init recurring donations links as meta field:
        if($donation_post_meta['payment_type'] === 'rebill' && $donation_post_data['post_parent']) {
            $donation_post_meta['init_recurring_donation_id'] = $donation_post_data['post_parent'];
        }

        // From now on, these data fields are not metas anymore, but main object attributes:
        foreach(array('campaign_id','payment_type','gateway','payment_method','donation_currency','donation_amount','donation_amount_total','main_curr_amount','main_curr_amount_total','donor_name','donor_email',) as $key) {
            unset($donation_post_meta[$key]);
        }

        $query_values = rtrim($query.$query_values, ',');
        if($wpdb->query($query_values) === false) {

            ob_start();
            echo "ERROR: ".$query_values."\n\n";
            fputs($err_log_fp, "ERROR: ".ob_get_clean());
            fclose($err_log_fp);

            return false;

        }
        // Main donation inserted

        // Donation meta insertion:
        $donation_post_meta['payment_title'] = $donation_post_data['post_title'];

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

            // WARNING: ATM this field works only for active recurring scheme gateways
            unset($donation_post_meta['rebilling_is_active']);
            $donation_post_meta['recurring_active'] = 1;

        }

        /** @todo Find and save $donation_post_meta['init_recurring_donation_id'] for each "rebill"-type donation */
        // $donation_post_meta['init_recurring_donation_id'] = Leyka_Donation::get_init_recurrent_donation($donation_post_data['ID']);

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
        return true;

    }

}
