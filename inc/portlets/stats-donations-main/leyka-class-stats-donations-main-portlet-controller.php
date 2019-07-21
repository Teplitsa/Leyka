<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Main_Stats_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = array()) {

        $params['interval'] = empty($params['interval']) ? 'year' : $params['interval'];
        switch($params['interval']) {
            case 'year': $interval = '1 year'; break;
            case 'half-year': $interval = '6 month'; break;
            case 'quarter': $interval = '3 month'; break;
            case 'month': $interval = '1 month'; break;
            case 'week': $interval = '1 week'; break;
            default: $interval = '1 year';
        }
        $curr_interval_begin_date = date('Y-m-d 23:59:59', strtotime('-'.$interval));
        $prev_interval_begin_date = date('Y-m-d 23:59:59', strtotime('-'.$interval, strtotime('-'.$interval)));

        global $wpdb;

        $donations_post_type = Leyka_Donation_Management::$post_type;
        $prev_interval_donations = $wpdb->get_col(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$prev_interval_begin_date' AND post_date < '$curr_interval_begin_date'"
        );
        $curr_interval_donations = $wpdb->get_col(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$curr_interval_begin_date'"
        );

        // Donors (unique donors' emails) count:
        $prev_donors_count = $prev_interval_donations ? count($wpdb->get_col(
            "SELECT DISTINCT {$wpdb->prefix}postmeta.meta_value
            FROM {$wpdb->prefix}postmeta
            WHERE {$wpdb->prefix}postmeta.post_id IN (".implode(',', $prev_interval_donations).")
            AND {$wpdb->prefix}postmeta.meta_key='leyka_donor_email'"
        )) : 0;

        $curr_donors_count = $curr_interval_donations ? count($wpdb->get_col(
            "SELECT DISTINCT {$wpdb->prefix}postmeta.meta_value
            FROM {$wpdb->prefix}postmeta
            WHERE {$wpdb->prefix}postmeta.post_id IN (".implode(',', $curr_interval_donations).")
            AND {$wpdb->prefix}postmeta.meta_key='leyka_donor_email'"
        )) : 0;

        $donors_count_delta = leyka_get_delta_percent($prev_donors_count, $curr_donors_count);

        // Donations amount & avg:
        $prev_amount = 0;
        if($prev_interval_donations) {

            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
            FROM {$wpdb->prefix}postmeta
            WHERE post_id IN (".implode(',', $prev_interval_donations).")
            AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $prev_amount += $amount->amount;
            }

        }

        $curr_amount = 0;
        if($curr_interval_donations) {

            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
                FROM {$wpdb->prefix}postmeta
                WHERE post_id IN (".implode(',', $curr_interval_donations).")
                AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $curr_amount += $amount->amount;
            }

        }

        $donations_amount_delta = leyka_get_delta_percent($prev_amount, $curr_amount);

        // Donations avg amount:
        $prev_amount_avg = $prev_amount ? round($prev_amount/count($prev_interval_donations), 2) : 0;
        $curr_amount_avg = $curr_amount ? round($curr_amount/count($curr_interval_donations), 2) : 0;
        $donations_amount_avg_delta = leyka_get_delta_percent($prev_amount_avg, $curr_amount_avg);

        return array(
            'donations_amount' => $curr_amount,
            'donations_amount_delta_percent' =>
                $donations_amount_delta === NULL ? '—' : ($donations_amount_delta < 0 ? '' : '+').$donations_amount_delta.'%',
            'donors_number' => $curr_donors_count,
            'donors_number_delta_percent' =>
                $donors_count_delta === NULL ? '—' : ($donors_count_delta < 0 ? '' : '+').$donors_count_delta.'%',
            'donations_amount_avg' => $curr_amount_avg,
            'donations_amount_avg_delta_percent' =>
                $donations_amount_avg_delta === NULL ?
                    '—' : ($donations_amount_avg_delta < 0 ? '' : '+').$donations_amount_avg_delta.'%',
        );

    }

}