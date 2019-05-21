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
        $curr_interval_begin_date = strtotime('-'.$interval); // $curr_interval_end_date is now
        $prev_interval_begin_date = strtotime('-'.$interval, $curr_interval_begin_date); // $prev_interval_end_date is $curr_interval_begin_date

        global $wpdb;

        $donations_post_type = Leyka_Donation_Management::$post_type;
        $curr_interval_donations = $wpdb->get_results(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$curr_interval_begin_date'"
        );
        $curr_interval_donations_count = count($curr_interval_donations);

        $prev_interval_donations = $wpdb->get_results(
            "SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type='{$donations_post_type}'
            AND post_status='funded'
            AND post_date >= '$prev_interval_begin_date' AND post_date < $curr_interval_begin_date"
        );
        $prev_interval_donations_count = count($prev_interval_donations);

        if( !$curr_interval_donations_count ) {
            $donors_number_delta_percent = $prev_interval_donations_count ? -100.0 : 0;
        } else {
            $donors_number_delta_percent = round(100.0*($curr_interval_donations_count - $prev_interval_donations_count)/$curr_interval_donations_count, 2);
        }

        return array(
            'donations_amount' => 12345.67,
            'donations_amount_delta_percent' => -0.78,
            'donors_number' => $curr_interval_donations_count,
            'donors_number_delta_percent' => ($donors_number_delta_percent < 0 ? '-' : '+').$donors_number_delta_percent.'%',
            'donations_amount_avg' => 1234.5,
            'donations_amount_avg_delta_percent' => 24.89,
        );

    }

}