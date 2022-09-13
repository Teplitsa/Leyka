<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Recurring_Stats_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = []) {

        $subscriptions_statuses_list = leyka_get_recurring_subscription_status_list();
        $subscriptions_stats['all'] = ['label' => __('All subscriptions', 'leyka'), 'count' => 0];

        foreach($subscriptions_statuses_list as $status_id => $status_name) {
            $subscriptions_stats[$status_id] = ['label' => $status_name, 'count' => 0];
        }

        $interval_dates = leyka_count_interval_dates($params['interval']);

        if($params['reset'] === true) {

            delete_transient('leyka_stats_donations_recurring_curr_'.$params['interval']);
            delete_transient('leyka_stats_donations_recurring_prev_'.$params['interval']);

            $curr_interval_data = false;
            $prev_interval_data = false;

        } else {
            $curr_interval_data = get_transient('leyka_stats_donations_recurring_curr_'.$params['interval']);
            $prev_interval_data = get_transient('leyka_stats_donations_recurring_prev_'.$params['interval']);
        }

        if($curr_interval_data === false) {

            global $wpdb;

            // Curr. interval recurring donations:
            $query = leyka_get_donations_storage_type() === 'post' ?
                // Post-based donations storage:
                "SELECT posts.ID, posts.post_parent, postmeta2.meta_value as rebilling_is_on, postmeta3.meta_value as subscription_status, postmeta4.meta_value as currency  
                FROM {$wpdb->prefix}posts as posts 
                    JOIN {$wpdb->prefix}postmeta as postmeta1 ON posts.ID = postmeta1.post_id
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta2 ON posts.ID = postmeta2.post_id and postmeta2.meta_key='_rebilling_is_active'
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta3 ON posts.ID = postmeta3.post_id and postmeta3.meta_key='leyka_recurring_subscription_status'
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta4 ON posts.ID = postmeta4.post_id and postmeta4.meta_key='leyka_donation_currency'
                WHERE posts.post_type='".Leyka_Donation_Management::$post_type."'
                AND posts.post_status='funded'
                AND posts.post_date >= '".$interval_dates["curr_interval_begin_date"]."'
                AND postmeta1.meta_key='leyka_payment_type'
                AND postmeta1.meta_value='rebill'" :
                // Separate donations storage:
                //TODO Vyacheslav - fix request for a separate donations storage
                "SELECT ID
                FROM {$wpdb->prefix}leyka_donations
                WHERE status='funded'
                AND date_created >= '".$interval_dates["curr_interval_begin_date"]."'
                AND payment_type='rebill'";

            $curr_recurring_donations = $wpdb->get_results($query, 'ARRAY_A');
            $curr_subscriptions = $subscriptions_stats;
            $curr_recurring_donations_ids = [];

            foreach($curr_recurring_donations as $curr_recurring_donation) {

                $curr_recurring_donations_ids[] = $curr_recurring_donation['ID'];

                if($curr_recurring_donation['post_parent'] === '0') {

                    $curr_subscriptions['all']['count']++;

                    if($curr_recurring_donation['subscription_status']) {
                        $curr_subscriptions[$curr_recurring_donation['subscription_status']]['count']++;
                    } else if($curr_recurring_donation['rebilling_is_on']) {
                        $curr_subscriptions['active']['count']++;
                    } else {
                        $curr_subscriptions['non-active']['count']++;
                    }

                }

            }

            $curr_recurring_amount = 0;
            if($curr_recurring_donations) {

                foreach($curr_recurring_donations as $curr_recurring_donation) {
                    $curr_recurring_donations_by_currency[strtolower($curr_recurring_donation['currency'])][] = $curr_recurring_donation['ID'];
                }

                foreach($curr_recurring_donations_by_currency as $currency => $donations) {

                    $query = leyka_get_donations_storage_type() === 'post' ?
                        // Post-based donations storage:
                        "SELECT SUM(meta_value)
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (" . implode(',', $donations) . ")
                        AND meta_key='leyka_donation_amount'" :
                        // Separate donations storage:
                        "SELECT SUM(amount)
                        FROM {$wpdb->prefix}leyka_donations
                        WHERE ID IN (" . implode(',', $donations) . ')';

                    $curr_recurring_amount = leyka_currency_convert($wpdb->get_var($query), $currency);

                }

            }

            $curr_interval_data = [
                'donations_count' => count($curr_recurring_donations_ids),
                'subscriptions' => $curr_subscriptions,
                'amount' => $curr_recurring_amount
            ];

            leyka_set_transient('leyka_stats_donations_recurring_curr_'.$params['interval'], $curr_interval_data);

        }

        if($prev_interval_data === false) {

            global $wpdb;

            // Prev. interval recurring donations:
            $query = leyka_get_donations_storage_type() === 'post' ?
                // Post-based donations storage:
                "SELECT posts.ID, posts.post_parent, postmeta2.meta_value as rebilling_is_on, postmeta3.meta_value as subscription_status, postmeta4.meta_value as currency  
                FROM {$wpdb->prefix}posts as posts 
                    JOIN {$wpdb->prefix}postmeta as postmeta1 ON posts.ID = postmeta1.post_id
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta2 ON posts.ID = postmeta2.post_id and postmeta2.meta_key='_rebilling_is_active'
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta3 ON posts.ID = postmeta3.post_id and postmeta3.meta_key='leyka_recurring_subscription_status'
                    LEFT JOIN {$wpdb->prefix}postmeta AS postmeta4 ON posts.ID = postmeta4.post_id and postmeta4.meta_key='leyka_donation_currency'
                WHERE posts.post_type='".Leyka_Donation_Management::$post_type."'
                AND posts.post_status='funded'
                AND posts.post_date BETWEEN '".$interval_dates["prev_interval_begin_date"]."' AND '".$interval_dates["curr_interval_begin_date"]."'
                AND postmeta1.meta_key='leyka_payment_type'
                AND postmeta1.meta_value='rebill'" :
                // Separate donations storage:
                //TODO Vyacheslav - fix request for a separate donations storage
                "SELECT ID
                FROM {$wpdb->prefix}leyka_donations
                WHERE status='funded'
                AND date_created BETWEEN '".$interval_dates["prev_interval_begin_date"]."' AND '".$interval_dates["curr_interval_begin_date"]."'
                AND payment_type='rebill'";

            $prev_recurring_donations = $wpdb->get_results($query, 'ARRAY_A');
            $prev_subscriptions = $subscriptions_stats;
            $prev_recurring_donations_ids = [];

            foreach($prev_recurring_donations as $prev_recurring_donation) {

                $prev_recurring_donations_ids[] = $prev_recurring_donation['ID'];

                if($prev_recurring_donation['post_parent'] === '0') {

                    $prev_subscriptions['all']['count']++;

                    if($prev_recurring_donation['subscription_status']) {
                        $prev_subscriptions[$prev_recurring_donation['subscription_status']]['count']++;
                    } else if($prev_recurring_donation['rebilling_is_on']) {
                        $prev_subscriptions['active']['count']++;
                    } else {
                        $prev_subscriptions['non-active']['count']++;
                    }

                }

            }

            $prev_recurring_amount = 0;
            if($prev_recurring_donations) {

                foreach($prev_recurring_donations as $prev_recurring_donation) {
                    $prev_recurring_donations_by_currency[strtolower($prev_recurring_donation['currency'])][] = $prev_recurring_donation['ID'];
                }

                foreach($prev_recurring_donations_by_currency as $currency => $donations) {

                    $query = leyka_get_donations_storage_type() === 'post' ?
                        // Post-based donations storage:
                        "SELECT SUM(meta_value)
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (" . implode(',', $donations) . ")
                        AND meta_key='leyka_donation_amount'" :
                        // Separate donations storage:
                        "SELECT SUM(amount)
                        FROM {$wpdb->prefix}leyka_donations
                        WHERE ID IN (" . implode(',', $donations) . ')';

                    $prev_recurring_amount = leyka_currency_convert($wpdb->get_var($query), $currency);

                }

            }

            $prev_interval_data = [
                'donations_count' => count($prev_recurring_donations_ids),
                'subscriptions' => $prev_subscriptions,
                'amount' => $prev_recurring_amount
            ];

            leyka_set_transient('leyka_stats_donations_recurring_prev_'.$params['interval'], $prev_interval_data, $interval_dates['curr_interval_end_date']);

        }

        $recurring_amount_delta = leyka_get_delta_percent($prev_interval_data['amount'], $curr_interval_data['amount']);

        // Donations avg amount:
        $prev_amount_avg = $prev_interval_data['amount'] && $prev_interval_data['donations_count'] ?
            round($prev_interval_data['amount']/$prev_interval_data['donations_count'], 2) : 0;
        $curr_amount_avg = $curr_interval_data['amount'] && $curr_interval_data['donations_count'] ?
            round($curr_interval_data['amount']/$curr_interval_data['donations_count'], 2) : 0;
        $donations_amount_avg_delta = leyka_get_delta_percent($prev_amount_avg, $curr_amount_avg);

        // Subscriptions count delta:
        foreach($curr_interval_data['subscriptions'] as $status_id => $status_data) {

            $delta = leyka_get_delta_percent($prev_interval_data['subscriptions'][$status_id]['count'], $curr_interval_data['subscriptions'][$status_id]['count']);
            $curr_interval_data['subscriptions'][$status_id]['delta_percent'] = $delta === NULL ? '—' : ($delta < 0 ? '' : '+').$delta.'%';

        }

        return [
            'recurring_donations_amount' => $curr_interval_data['amount'],
            'recurring_donations_amount_delta_percent' => $recurring_amount_delta === NULL ?
                '—' : ($recurring_amount_delta < 0 ? '' : '+').$recurring_amount_delta.'%',
            'donations_amount_avg' => $curr_amount_avg,
            'donations_amount_avg_delta_percent' => $donations_amount_avg_delta === NULL ?
                '—' : ($donations_amount_avg_delta < 0 ? '' : '+').$donations_amount_avg_delta.'%',
            'subscriptions_stats' => $curr_interval_data['subscriptions']
        ];

    }

}