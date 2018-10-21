<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

define('DATA_CHUNK_SIZE', 100);

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

        $query = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE post_type=%s", Leyka_Donation_Management::$post_type);
        $total_donations = $wpdb->get_var($query);

        $total_chunks = ceil($total_donations/DATA_CHUNK_SIZE);
        $chunk_percents = round(100/$total_chunks, 2);

        echo '<pre>Total donations: '.print_r($total_donations, 1).'</pre>'."\n";
        echo '<pre>Total chunks: '.print_r($total_chunks, 1).'</pre>'."\n";
        echo '<pre>Chunk percents: '.print_r($chunk_percents, 1).'</pre>'."\n\n";
        ob_flush();

        for($i=0; $i < $total_chunks; $i++) {
            self::processChunk($i, $chunk_percents);
        }

    }

    public static function processChunk($chunk_number, $chunk_percents) {

        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT `ID`,`post_title`,`post_status`,`post_date`,`post_parent` FROM {$wpdb->prefix}posts WHERE `post_type`=%s ORDER BY `ID` LIMIT %d,%d",
            Leyka_Donation_Management::$post_type,
            $chunk_number*DATA_CHUNK_SIZE,
            DATA_CHUNK_SIZE
        );
        $chunk_donations = $wpdb->get_results($query, ARRAY_A);

        if($chunk_donations) {

            echo 'Processing the chunk #'.$chunk_number.'... ';
            ob_flush();

            $query = "INSERT INTO {$wpdb->prefix}leyka_donations (`ID`, `campaign_id`, `status`, `status_log`, `payment_type`, `date_created`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`, `donor_name`, `donor_email`, `donor_email_date`, `donor_comment`, `donor_subscribed`, `donor_subscription_email`, `manangers_emails_date`, `gateway_response`, `init_recurring_donation_id`, `recurring_active`, `recurring_cancel_date`) VALUES ";

            foreach($chunk_donations as $donation_post_data) {

                $donation_post_meta = $wpdb->get_results($wpdb->prepare("SELECT `meta_key`,`meta_value` FROM {$wpdb->prefix}postmeta WHERE `post_id`=%d", $donation_post_data['ID']), ARRAY_A);

                if( !$donation_post_meta ) {
                    continue;
                }

                foreach($donation_post_meta as $key => $meta) {

                    $donation_post_meta[ str_replace(array('leyka_', '_leyka_'), '', $meta['meta_key']) ] = $meta['meta_value'];
                    unset($donation_post_meta[$key]);

                }

                if(empty($donation_post_meta['campaign_id'])) {
                    continue;
                }

                $donation_post_meta['payment_type'] = empty($donation_post_meta['payment_type']) ? 'single' : $donation_post_meta['payment_type'];
                $donation_post_meta['gateway'] = empty($donation_post_meta['gateway']) ? 'NULL' : "'".$donation_post_meta['gateway']."'";
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


                $query .= "\n({$donation_post_data['ID']},{$donation_post_meta['campaign_id']},'{$donation_post_data['post_status']}','{$donation_post_meta['_status_log']}','{$donation_post_meta['payment_type']}','{$donation_post_data['post_date']}',{$donation_post_meta['gateway']},'{$donation_post_meta['payment_method']}','{$donation_post_meta['donation_currency']}',{$donation_post_meta['donation_amount']},{$donation_post_meta['donation_amount_total']},{$donation_post_meta['main_curr_amount']},{$donation_post_meta['main_curr_amount_total']},'{$donation_post_meta['donor_name']}','{$donation_post_meta['donor_email']}',{$donation_post_meta['donor_email_date']},{$donation_post_meta['donor_comment']},{$donation_post_meta['donor_subscribed']},{$donation_post_meta['donor_subscription_email']},{$donation_post_meta['managers_emails_date']},{$donation_post_meta['gateway_response']},{$donation_post_data['post_parent']},{$donation_post_meta['recurring_active']},{$donation_post_meta['recurrents_cancel_date']}),";

            }

            $query = rtrim($query, ',');
            $res = $wpdb->query($query);

            if($res) {
                echo "chunk #$chunk_number inserted (".($chunk_number*$chunk_percents)."% finished).\n\n";
            } else {
                $wpdb->print_error();
            }
            ob_flush();

        }

//        echo '<pre>'.print_r($chunk_number.' ('.$chunk_number*$chunk_percents.'%). Donations: '.count($chunk_donations), 1).'</pre>'."\n\n";
//        ob_flush();

    }

}
