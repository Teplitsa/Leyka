<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Dynamics_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = []) {

        $params['interval'] = empty($params['interval']) ? 'year' : $params['interval'];
        switch($params['interval']) {

            case 'days_180':
            case 'this_half_year':

                $sub_interval = 'month';
                $interval_length = 6;
                break;

            case 'days_90':
            case 'this_quarter':

                $sub_interval = 'week';
                $interval_length = 12;
                break;

            case 'days_30':
            case 'this_month':

                $sub_interval = 'week';
                $interval_length = 4;
                break;

            case 'days_7':
            case 'this_week':

                $sub_interval = 'day';
                $interval_length = 7;
                break;

            case 'this_year':
            case 'days_365':
            default:
                $sub_interval = 'month';
                $interval_length = 12;
        }

        global $wpdb;

        $result = [];
        $labels = [];
        $interval_dates = leyka_count_interval_dates($params['interval']);

        if($params['reset'] === true) {

            delete_transient('leyka_stats_donations_dynamics_'.$params['interval']);

            $intervals_data = false;

        } else {
            $intervals_data = get_transient('leyka_stats_donations_dynamics_'.$params['interval']);
        }

        if($intervals_data === false) {

            for($sub_interval_index = 0; $sub_interval_index < $interval_length; $sub_interval_index++) {

                $sub_interval_end_date = date('Y-m-d 23:59:59', strtotime(' -'.$sub_interval_index.' '.$sub_interval));

                if($sub_interval_end_date <= $interval_dates['curr_interval_begin_date']) {
                    continue;
                }

                $sub_interval_begin_date = date('Y-m-d 23:59:59', strtotime(' -'.($sub_interval_index + 1).' '.$sub_interval));
                $sub_interval_begin_date = $sub_interval_begin_date < $interval_dates['curr_interval_begin_date'] ?
                    $interval_dates['curr_interval_begin_date'] : $sub_interval_begin_date;

                if(leyka_get_donations_storage_type() === 'post') { // Post-based donations storage:

                    $donations_post_type = Leyka_Donation_Management::$post_type;

                    $donations_data_raw = $wpdb->get_results(
                        "SELECT t1.post_id, t1.meta_value FROM {$wpdb->prefix}postmeta t1
                        WHERE t1.meta_key='leyka_donation_currency' AND t1.post_id IN (
                            SELECT t2.ID 
                            FROM {$wpdb->prefix}posts t2
                            WHERE t2.post_type='{$donations_post_type}' AND t2.post_status='funded' AND t2.post_date BETWEEN '$sub_interval_begin_date' AND '$sub_interval_end_date')",
                            'ARRAY_A'
                    );

                    $donations_data = [];
                    foreach($donations_data_raw as $donation_data_raw) {
                        $donations_data[$donation_data_raw['post_id']] = strtolower($donation_data_raw['meta_value']);
                    }

                    $donations = array_keys($donations_data);

                    $amount = 0;
                    if($donations) {

                        $donations_by_currency = [];

                        foreach($donations_data as $donation_id => $donation_currency) {
                            $donations_by_currency[$donation_currency][] = $donation_id;
                        }

                        foreach($donations_by_currency as $currency => $donations) {

                            $query = "SELECT SUM(meta_value)
                                FROM {$wpdb->prefix}postmeta
                                WHERE post_id IN (" . implode(',', $donations) . ")
                                AND meta_key='leyka_donation_amount'";

                            $amount += leyka_currency_convert($wpdb->get_var($query), $currency);

                        }

                    }

                } else { // Separate donations storage:

                    $query = "SELECT SUM(amount)
                        FROM {$wpdb->prefix}leyka_donations
                        WHERE status='funded'
                            AND date_created BETWEEN '$sub_interval_begin_date' AND '$sub_interval_end_date'";

                    $amount = $wpdb->get_var($query);

                }

                $result[] = ['x' => date('d.m.Y', strtotime($sub_interval_end_date)), 'y' => $amount,];

                if($sub_interval === 'month') {
                    $labels[] = date('m.y', strtotime($sub_interval_end_date));
                } else if($sub_interval === 'week') {
                    $labels[] = date('d.m.y', strtotime($sub_interval_end_date));
                } else {
                    $labels[] = date('d.m', strtotime($sub_interval_end_date));
                }

            }

            $intervals_data = [
                'result' => $result,
                'labels' => $labels
            ];

            leyka_set_transient('leyka_stats_donations_dynamics_'.$params['interval'], $intervals_data);

        }

        return ['data' => array_reverse($intervals_data['result']), 'labels' => array_reverse($intervals_data['labels'])];

    }

}