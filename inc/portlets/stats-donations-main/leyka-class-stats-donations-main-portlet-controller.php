<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Main_Stats_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = []) {

        $interval_dates = leyka_count_interval_dates($params['interval']);

        if($params['reset'] === true) {

            delete_transient('leyka_stats_donations_main_curr_'.$params['interval']);
            delete_transient('leyka_stats_donations_main_prev_'.$params['interval']);
            delete_transient('leyka_dashboard_data_cache_timestamp_'.$params['interval']);

            $curr_interval_data = false;
            $prev_interval_data = false;

        } else {
            $curr_interval_data = get_transient('leyka_stats_donations_main_curr_'.$params['interval']);
            $prev_interval_data = get_transient('leyka_stats_donations_main_prev_'.$params['interval']);
        }

        if($curr_interval_data === false) {

            global $wpdb;

            $curr_interval_donations = [];

            if (leyka_get_donations_storage_type() === 'post') { // Post-based donations storage

                $donations_post_type = Leyka_Donation_Management::$post_type;

                $curr_interval_donations = $wpdb->get_col(
                    "SELECT ID
                    FROM {$wpdb->prefix}posts
                    WHERE post_type='{$donations_post_type}'
                    AND post_status='funded'
                    AND post_date >= '" . $interval_dates["curr_interval_begin_date"] . "'"
                );

                // Donors (unique donors' emails) count:
                $curr_donors_count = $curr_interval_donations ? count($wpdb->get_col(
                    "SELECT DISTINCT {$wpdb->prefix}postmeta.meta_value
                    FROM {$wpdb->prefix}postmeta
                    WHERE {$wpdb->prefix}postmeta.post_id IN (" . implode(',', $curr_interval_donations) . ")
                    AND {$wpdb->prefix}postmeta.meta_key='leyka_donor_email'"
                )) : 0;

            } else { // Separate donations storage

                $donors_emails = [];

                $tmp = $wpdb->get_results(
                    "SELECT ID, donor_email
                    FROM {$wpdb->prefix}leyka_donations
                    WHERE status='funded'
                    AND date_created >= '" . $interval_dates["curr_interval_begin_date"] . "'"
                );
                foreach ($tmp as $line) {

                    $curr_interval_donations[] = $line->ID;
                    $donors_emails[] = $line->donor_email;

                }
                $curr_donors_count = count(array_unique($donors_emails));

            }

            // Donations amount & avg:
            $curr_amount = 0;
            if($curr_interval_donations) {

                $query = leyka_get_donations_storage_type() === 'post' ?
                    // Post-based donations storage:
                    "SELECT SUM(meta_value)
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (" . implode(',', $curr_interval_donations) . ")
                        AND meta_key='leyka_donation_amount'" :
                    // Separate donations storage:
                    "SELECT SUM(amount)
                        FROM {$wpdb->prefix}leyka_donations
                        WHERE ID IN (" . implode(',', $curr_interval_donations) . ')';

                $curr_amount += $wpdb->get_var($query);

            }

            $curr_interval_data = [
                'donations_count' => count($curr_interval_donations),
                'donors_count' => $curr_donors_count,
                'amount' => $curr_amount
            ];

            leyka_set_transient('leyka_stats_donations_main_curr_'.$params['interval'], $curr_interval_data);
            leyka_set_transient('leyka_dashboard_data_cache_timestamp_'.$params['interval'], time());

        }

        if($prev_interval_data === false) {

            global $wpdb;

            $prev_interval_donations = [];

            if(leyka_get_donations_storage_type() === 'post') { // Post-based donations storage

                $donations_post_type = Leyka_Donation_Management::$post_type;
                $prev_interval_donations = $wpdb->get_col(
                    "SELECT ID
                    FROM {$wpdb->prefix}posts
                    WHERE post_type='{$donations_post_type}'
                    AND post_status='funded'
                    AND post_date >= '" . $interval_dates["prev_interval_begin_date"] . "' AND post_date < '" . $interval_dates["curr_interval_begin_date"] . "'"
                );

                // Donors (unique donors' emails) count:
                $prev_donors_count = $prev_interval_donations ? count($wpdb->get_col(
                    "SELECT DISTINCT {$wpdb->prefix}postmeta.meta_value
                    FROM {$wpdb->prefix}postmeta
                    WHERE {$wpdb->prefix}postmeta.post_id IN (" . implode(',', $prev_interval_donations) . ")
                    AND {$wpdb->prefix}postmeta.meta_key='leyka_donor_email'"
                )) : 0;

            } else { // Separate donations storage

                $donors_emails = [];

                $tmp = $wpdb->get_results(
                    "SELECT ID, donor_email
                    FROM {$wpdb->prefix}leyka_donations
                    WHERE status='funded'
                    AND date_created >= '" . $interval_dates["prev_interval_begin_date"] . "' AND date_created < '" . $interval_dates["curr_interval_begin_date"] . "'"
                );
                foreach ($tmp as $line) {

                    $prev_interval_donations[] = $line->ID;
                    $donors_emails[] = $line->donor_email;

                }
                $prev_donors_count = count(array_unique($donors_emails));

            }

            // Donations amount & avg:
            $prev_amount = 0;
            if($prev_interval_donations) {

                $query = leyka_get_donations_storage_type() === 'post' ?
                    // Post-based donations storage:
                    "SELECT SUM(meta_value) AS amount
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (" . implode(',', $prev_interval_donations) . ")
                        AND meta_key='leyka_donation_amount'" :
                    // Separate donations storage:
                    "SELECT SUM(amount)
                        FROM {$wpdb->prefix}leyka_donations
                        WHERE ID IN (" . implode(',', $prev_interval_donations) . ')';

                $prev_amount += $wpdb->get_var($query);

            }

            $prev_interval_data = [
                'donations_count' => count($prev_interval_donations),
                'donors_count' => $prev_donors_count,
                'amount' => $prev_amount
            ];

            leyka_set_transient('leyka_stats_donations_main_prev_'.$params['interval'], $prev_interval_data, $interval_dates['curr_interval_end_date']);

        }

        $donors_count_delta = leyka_get_delta_percent($prev_interval_data['donors_count'], $curr_interval_data['donors_count']);
        $donations_amount_delta = leyka_get_delta_percent($prev_interval_data['amount'], $curr_interval_data['amount']);

        //ltv
        $prev_ltv = $prev_interval_data['amount'] && $prev_interval_data['donors_count'] ?
            round($prev_interval_data['amount'] / $prev_interval_data['donors_count'], 2) : 0;
        $curr_ltv = $curr_interval_data['amount'] && $curr_interval_data['donors_count'] ?
            round($curr_interval_data['amount'] / $curr_interval_data['donors_count'], 2) : 0;
        $ltv_delta = leyka_get_delta_percent($prev_ltv, $curr_ltv);
        // Donations avg amount:
        $prev_amount_avg = $prev_interval_data['amount'] && $prev_interval_data['donations_count'] ?
            round($prev_interval_data['amount'] / $prev_interval_data['donations_count'], 2) : 0;
        $curr_amount_avg = $curr_interval_data['amount'] && $curr_interval_data['donations_count'] ?
            round($curr_interval_data['amount'] / $curr_interval_data['donations_count'], 2) : 0;
        $donations_amount_avg_delta = leyka_get_delta_percent($prev_amount_avg, $curr_amount_avg);

        return [
            'donations_amount' => $curr_interval_data['amount'],
            'donations_amount_delta_percent' =>
                $donations_amount_delta === NULL ? '—' : ($donations_amount_delta < 0 ? '' : '+') . $donations_amount_delta . '%',
            'donors_number' => $curr_interval_data['donors_count'],
            'donors_number_delta_percent' =>
                $donors_count_delta === NULL ? '—' : ($donors_count_delta < 0 ? '' : '+') . $donors_count_delta . '%',
            'ltv' => $curr_ltv,
            'ltv_delta_percent' => $ltv_delta === NULL ?
                '—' : ($ltv_delta < 0 ? '' : '+') . $ltv_delta . '%',
            'donations_amount_avg' => $curr_amount_avg,
            'donations_amount_avg_delta_percent' =>
                $donations_amount_avg_delta === NULL ?
                    '—' : ($donations_amount_avg_delta < 0 ? '' : '+') . $donations_amount_avg_delta . '%',
        ];

    }

}