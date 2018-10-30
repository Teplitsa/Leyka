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
	fwrite(STDOUT, 'Total execution time in seconds: ' . (microtime(true) - $time_start).chr(10).chr(10));

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
        $donations_ids = $wpdb->get_col($query);
        $total_donations = count($donations_ids);

        echo '<pre>Total donations: '.print_r($total_donations, 1).'</pre>'."\n";
        ob_flush();

        $process_completed = true;
        $donations_processed = 0;

        foreach($donations_ids as $donation_id) {

            echo "Processing the donation #{$donations_processed}/{$total_donations}... ";
            ob_flush();

            if(self::processDonation($donation_id)) {

                $donations_processed++;
                echo "donation inserted (".($donations_processed*100.0/$total_donations)."% finished).\n";
                ob_flush();

            }

        }

        if($process_completed) {
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

        $query = "INSERT INTO {$wpdb->prefix}leyka_donations (`ID`, `campaign_id`, `status`, `status_log`, `payment_type`, `date_created`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`, `donor_name`, `donor_email`, `donor_email_date`, `donor_comment`, `donor_subscribed`, `donor_subscription_email`, `manangers_emails_date`, `gateway_response`, `init_recurring_donation_id`, `recurring_active`, `recurring_cancel_date`) VALUES ";

        $donation_post_meta = $wpdb->get_results($wpdb->prepare("SELECT `meta_key`,`meta_value` FROM {$wpdb->prefix}postmeta WHERE `post_id`=%d", $donation_post_data['ID']), ARRAY_A);

        if( !$donation_post_meta ) {

            fputs($err_log_fp, "{$donation_post_data['ID']} - no donation meta.\n");
            return false;

        }

        foreach($donation_post_meta as $key => $meta) {

            $donation_post_meta[ str_replace(array('leyka_', '_leyka_'), '', $meta['meta_key']) ] = $meta['meta_value'];
            unset($donation_post_meta[$key]);

        }

        if(empty($donation_post_meta['campaign_id'])) {

            fputs($err_log_fp, "{$donation_post_data['ID']} - no campaign_id meta.\n");
            return false;

        }

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
        $donation_post_meta['donor_email_date'] = empty($donation_post_meta['donor_email_date']) ? 'NULL' : "'".$donation_post_meta['donor_email_date']."'";
        $donation_post_meta['donor_comment'] = empty($donation_post_meta['donor_comment']) ? 'NULL' : "'".$donation_post_meta['donor_comment']."'";
        $donation_post_meta['donor_subscribed'] = empty($donation_post_meta['donor_subscribed']) ? 0 : 1;
        $donation_post_meta['donor_subscription_email'] = empty($donation_post_meta['donor_subscription_email']) ? 'NULL' : "'".$donation_post_meta['donor_subscription_email']."'";
        $donation_post_meta['managers_emails_date'] = empty($donation_post_meta['managers_emails_date']) ? 'NULL' : "'".$donation_post_meta['managers_emails_date']."'";
        $donation_post_meta['gateway_response'] = empty($donation_post_meta['gateway_response']) ? 'NULL' : "'".$donation_post_meta['gateway_response']."'";
        $donation_post_data['post_parent'] = empty($donation_post_data['post_parent']) ?
            'NULL' : $donation_post_meta['post_parent'];
        $donation_post_meta['recurring_active'] = empty($donation_post_meta['_rebilling_is_active']) ? 0 : 1;
        $donation_post_meta['recurrents_cancel_date'] = empty($donation_post_meta['recurrents_cancel_date']) ? 'NULL' : "'".$donation_post_meta['recurrents_cancel_date']."'";

        $query_values = "\n({$donation_post_data['ID']},{$donation_post_meta['campaign_id']},'{$donation_post_data['post_status']}','{$donation_post_meta['_status_log']}','{$donation_post_meta['payment_type']}','{$donation_post_data['post_date']}',{$donation_post_meta['gateway']},'{$donation_post_meta['payment_method']}','{$donation_post_meta['donation_currency']}',{$donation_post_meta['donation_amount']},{$donation_post_meta['donation_amount_total']},{$donation_post_meta['main_curr_amount']},{$donation_post_meta['main_curr_amount_total']},'{$donation_post_meta['donor_name']}','{$donation_post_meta['donor_email']}',{$donation_post_meta['donor_email_date']},{$donation_post_meta['donor_comment']},{$donation_post_meta['donor_subscribed']},{$donation_post_meta['donor_subscription_email']},{$donation_post_meta['managers_emails_date']},{$donation_post_meta['gateway_response']},{$donation_post_data['post_parent']},{$donation_post_meta['recurring_active']},{$donation_post_meta['recurrents_cancel_date']})";

        $query_values = rtrim($query.$query_values, ',');
        if($wpdb->query($query_values) === false) {

            ob_start();
            echo "ERROR: ".$query_values."\n\n";
            fputs($err_log_fp, "ERROR: ".ob_get_clean());
            fclose($err_log_fp);

            return false;

        } else {

            fclose($err_log_fp);
            return true;

        }

    }

}
