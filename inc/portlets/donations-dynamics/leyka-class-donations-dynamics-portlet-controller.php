<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Donations_Dynamics_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = array()) {

        $params['interval'] = empty($params['interval']) ? 'year' : $params['interval'];
        switch($params['interval']) {
            case 'half-year':
//                $interval = '6 month';
                $sub_interval = 'month';
                $interval_length = 6;
//                $interval_date_format = 'd.m.Y';
                break;
            case 'quarter':
//                $interval = '3 month';
                $sub_interval = 'week';
                $interval_length = 12;
//                $interval_date_format = 'd.m.Y';
                break;
            case 'month':
//                $interval = '1 month';
                $sub_interval = 'week';
                $interval_length = 4;
//                $interval_date_format = 'd.m.Y';
                break;
            case 'week':
//                $interval = '1 week';
                $sub_interval = 'day';
                $interval_length = 7;
//                $interval_date_format = 'd.m.Y';
                break;
            case 'year':
            default:
//                $interval = '1 year';
                $sub_interval = 'month';
                $interval_length = 12;
//                $interval_date_format = 'd.m.Y';
        }

        global $wpdb;

        $donations_post_type = Leyka_Donation_Management::$post_type;
        $result = array();
        $labels = array();

        for($sub_interval_index = 0; $sub_interval_index < $interval_length; $sub_interval_index++) {

            $sub_interval_begin_date = date('Y-m-d H:i:s', strtotime(' -'.($sub_interval_index + 1).' '.$sub_interval));
            $sub_interval_end_date = date('Y-m-d H:i:s', strtotime(' -'.$sub_interval_index.' '.$sub_interval));

            $count = $wpdb->get_var(
                "SELECT COUNT({$wpdb->prefix}posts.ID)
                FROM {$wpdb->prefix}posts
                WHERE post_type='$donations_post_type'
                AND post_status='funded'
                AND post_date BETWEEN '$sub_interval_begin_date' AND '$sub_interval_end_date'"
            );

            $result[] = array('x' => date('d.m.Y', strtotime($sub_interval_end_date)), 'y' => $count,);
            if($sub_interval === 'month') {
                $labels[] = date('m.y', strtotime($sub_interval_end_date));
            }
            elseif($sub_interval === 'week') {
                $labels[] = date('d.m.y', strtotime($sub_interval_end_date));
            }
            else {
                $labels[] = date('d.m', strtotime($sub_interval_end_date));
            }

        }

        return array(
            'data' => array_reverse($result), // [{x:'25.11.2016', y:20}, {x:'25.12.2016', y:30}, ...]
            'labels' => array_reverse($labels), // [{x:'25.11.2016', y:20}, {x:'25.12.2016', y:30}, ...]
        );
    }

}