<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Recent_Donations_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = []) {

        $params['number'] = empty($params['number']) || (int)$params['number'] <= 0 ? 5 : $params['number'];
        $interval_dates = leyka_count_interval_dates($params['interval']);

        $result = [];
        $donations = Leyka_Donations::get_instance()->get([
            'status' => ['submitted', 'funded', 'failed',],
            'results_limit' => $params['number'],
            'date_from' => date('Y-m-d', strtotime($interval_dates["curr_interval_begin_date"])),
            'orderby' => 'donation_id',
            'order' => 'DESC',
        ]);

        foreach($donations as $donation) {
            $result[] = [
                'id' => $donation->id,
                'type' => $donation->type,
                'donor_id' => $donation->donor_id ? $donation->donor_id : 0,
                'donor_name' => $donation->donor_name,
                'donor_email' => $donation->donor_email,
                'date_time' => $donation->date_time_label,
                'campaign_title' => $donation->campaign_title,
                'campaign_id' => $donation->campaign_id,
                'status' => ['id' => $donation->status, 'label' => $donation->status_label,],
                'amount' => $donation->amount,
                'currency' => $donation->currency_label,
            ];
        }

        return $result;

    }

}