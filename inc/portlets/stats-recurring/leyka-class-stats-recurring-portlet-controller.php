<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Recurring_Stats_Portlet_Controller extends Leyka_Portlet_Controller {

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
        $curr_interval_begin_date = date('Y-m-d', strtotime('-'.$interval));
        $prev_interval_begin_date = date('Y-m-d', strtotime('-'.$interval, strtotime('-'.$interval)));

        global $wpdb;

        $donations_post_type = Leyka_Donation_Management::$post_type;

        // Prev. interval recurring donations:
        $prev_recurring_donations = $wpdb->get_col(
            "SELECT {$wpdb->prefix}posts.ID
            FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
            WHERE {$wpdb->prefix}posts.post_type='{$donations_post_type}'
            AND {$wpdb->prefix}posts.post_status='funded'
            AND {$wpdb->prefix}posts.post_date BETWEEN '$prev_interval_begin_date' AND '$curr_interval_begin_date'
            AND {$wpdb->prefix}postmeta.meta_key='leyka_payment_type'
            AND {$wpdb->prefix}postmeta.meta_value='rebill'"
        );
        $prev_recurring_amount = 0;
        if($prev_recurring_donations) {

            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
                FROM {$wpdb->prefix}postmeta
                WHERE post_id IN (".implode(',', $prev_recurring_donations).")
                AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $prev_recurring_amount += $amount->amount;
            }

        }

        // Curr. interval recurring donations:
        $curr_recurring_donations = $wpdb->get_col(
            "SELECT {$wpdb->prefix}posts.ID
            FROM {$wpdb->prefix}posts JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
            WHERE {$wpdb->prefix}posts.post_type='{$donations_post_type}'
            AND {$wpdb->prefix}posts.post_status='funded'
            AND {$wpdb->prefix}posts.post_date >= '$curr_interval_begin_date'
            AND {$wpdb->prefix}postmeta.meta_key='leyka_payment_type'
            AND {$wpdb->prefix}postmeta.meta_value='rebill'"
        );

        $curr_recurring_amount = 0;
        if($curr_recurring_donations) {

            $donations_amounts = $wpdb->get_results(
                "SELECT meta_value AS amount
                FROM {$wpdb->prefix}postmeta
                WHERE post_id IN (".implode(',', $curr_recurring_donations).")
                AND meta_key='leyka_donation_amount'"
            );

            foreach($donations_amounts as $amount) {
                $curr_recurring_amount += $amount->amount;
            }

        }

        $recurring_amount_delta = leyka_get_delta_percent($prev_recurring_amount, $curr_recurring_amount);

        // Recurring & non-recurring donations count:
        $curr_recurring_donations_count = count($curr_recurring_donations);
        $curr_all_donations_count = $wpdb->get_var(
            "SELECT COUNT({$wpdb->prefix}posts.ID)
            FROM {$wpdb->prefix}posts
            WHERE {$wpdb->prefix}posts.post_type='{$donations_post_type}'
            AND {$wpdb->prefix}posts.post_status='funded'
            AND {$wpdb->prefix}posts.post_date >= '$curr_interval_begin_date'"
        );

        return array(
            'recurring_donations_amount' => $curr_recurring_amount,
            'recurring_donations_amount_delta_percent' => ($recurring_amount_delta < 0 ? '' : '+').$recurring_amount_delta.'%',
            'recurring_donations_number' => $curr_recurring_donations_count,
            'all_donations_number' => $curr_all_donations_count,
            'recurring_donations_number_percent' => $curr_all_donations_count ?
                ($curr_recurring_donations_count*100.0/$curr_all_donations_count).'%' : '0%',
        );

    }

}