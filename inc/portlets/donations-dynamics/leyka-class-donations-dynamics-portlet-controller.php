<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Dynamics_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = []) {

        $params['interval'] = empty($params['interval']) ? 'year' : $params['interval'];
        switch($params['interval']) {
            case 'half-year':
                $sub_interval = 'month';
                $interval_length = 6;
                break;
            case 'quarter':
                $sub_interval = 'week';
                $interval_length = 12;
                break;
            case 'month':
                $sub_interval = 'week';
                $interval_length = 4;
                break;
            case 'week':
                $sub_interval = 'day';
                $interval_length = 7;
                break;
            case 'year':
            default:
                $sub_interval = 'month';
                $interval_length = 12;
        }

        global $wpdb;

        $result = [];
        $labels = [];

        for($sub_interval_index = 0; $sub_interval_index < $interval_length; $sub_interval_index++) {

            $sub_interval_begin_date = date('Y-m-d 23:59:59', strtotime(' -'.($sub_interval_index + 1).' '.$sub_interval));
            $sub_interval_end_date = date('Y-m-d 23:59:59', strtotime(' -'.$sub_interval_index.' '.$sub_interval));

            $query = leyka_get_donations_storage_type() === 'post' ?
                // Post-based donations storage:
                "SELECT SUM({$wpdb->prefix}postmeta.meta_value)
                    FROM {$wpdb->prefix}postmeta
                    WHERE {$wpdb->prefix}postmeta.meta_key='leyka_donation_amount'
                    AND {$wpdb->prefix}postmeta.post_id IN (
                        SELECT {$wpdb->prefix}posts.ID 
                            FROM {$wpdb->posts} WHERE post_type='".Leyka_Donation_Management::$post_type."'
                            AND post_status='funded'
                            AND post_date BETWEEN '$sub_interval_begin_date' AND '$sub_interval_end_date'
                    )" :
                // Separate donations storage:
                "SELECT SUM(amount)
                FROM {$wpdb->prefix}leyka_donations
                WHERE status='funded'
                    AND date_created BETWEEN '$sub_interval_begin_date' AND '$sub_interval_end_date'";

            $amount = $wpdb->get_var($query);

            $result[] = ['x' => date('d.m.Y', strtotime($sub_interval_end_date)), 'y' => $amount,];
            if($sub_interval === 'month') {
                $labels[] = date('m.y', strtotime($sub_interval_end_date));
            } else if($sub_interval === 'week') {
                $labels[] = date('d.m.y', strtotime($sub_interval_end_date));
            } else {
                $labels[] = date('d.m', strtotime($sub_interval_end_date));
            }

        }

        return ['data' => array_reverse($result), 'labels' => array_reverse($labels),];

    }

}