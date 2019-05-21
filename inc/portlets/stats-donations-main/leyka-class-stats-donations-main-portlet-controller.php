<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Main_Stats_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data($params = array()) {

        $params['interval'] = empty($params['interval']) ? 'year' : $params['interval'];
        switch($params['interval']) {
            case 'year': $interval = '1 year'; break;
            case 'half-year': $interval = '6 month'; break;
            case 'quarter': $interval = '3 month'; break;
            case 'month': $interval = '1 month'; break;
            case 'week': $interval = '1 week'; break;
            default: $interval = '1 year';
        }
        $curr_interval_begin_date = date('Y-m-d', strtotime('-'.$interval));
        $prev_interval_begin_date = date('Y-m-d', strtotime('-'.$interval, strtotime('-'.$interval)));

        global $wpdb;

        $donations_post_type = Leyka_Donation_Management::$post_type;
        $prev_interval_donations = $wpdb->get_results(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$prev_interval_begin_date' AND post_date < '$curr_interval_begin_date'"
        );
        $curr_interval_donations = $wpdb->get_results(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$curr_interval_begin_date'"
        );

        // Donations (donors) count:
        $prev_donations_count = count($prev_interval_donations);
        $curr_donations_count = count($curr_interval_donations);
        $donations_count_delta = leyka_get_delta_percent($prev_donations_count, $curr_donations_count);

        // Donations amount & avg:
        $prev_amount = 0;
        if($prev_interval_donations) {

            $donations_ids = array();
            foreach($prev_interval_donations as $donation) {
                $donations_ids[] = $donation->ID;
            }
            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
            FROM {$wpdb->prefix}postmeta
            WHERE post_id IN (".implode(',', $donations_ids).")
            AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $prev_amount += $amount->amount;
            }

        }

        $curr_amount = 0;
        if($curr_interval_donations) {

            $donations_ids = array();
            foreach($curr_interval_donations as $donation) {
                $donations_ids[] = $donation->ID;
            }
            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
            FROM {$wpdb->prefix}postmeta
            WHERE post_id IN (".implode(',', $donations_ids).")
            AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $curr_amount += $amount->amount;
            }

        }

        $donations_amount_delta = leyka_get_delta_percent($prev_amount, $curr_amount);

        // Donations avg amount:
        $prev_amount_avg = $prev_amount ? round($prev_amount/$prev_donations_count, 2) : 0;
        $curr_amount_avg = $curr_amount ? round($curr_amount/$curr_donations_count, 2) : 0;
        $donations_amount_avg_delta = leyka_get_delta_percent($prev_amount_avg, $curr_amount_avg);

        return array(
            'donations_amount' => $curr_amount,
            'donations_amount_delta_percent' => ($donations_amount_delta < 0 ? '' : '+').$donations_amount_delta.'%',
            'donors_number' => $curr_donations_count,
            'donors_number_delta_percent' => ($donations_count_delta < 0 ? '' : '+').$donations_count_delta.'%',
            'donations_amount_avg' => $curr_amount_avg,
            'donations_amount_avg_delta_percent' => ($donations_amount_avg_delta < 0 ? '' : '+').$donations_amount_avg_delta.'%',
        );

    }

}