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
                'donation_type' => ['id' => $donation->type, 'label' => $donation->donation_type_label],
                'donor' => [
                    'id' => $donation->donor_id ? $donation->donor_id : 0,
                    'name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                    'phone' => $donation->donor_phone
                ],
                'date_time' => $donation->date_time_label,
                'campaign_title' => $donation->campaign_title,
                'campaign_id' => $donation->campaign_id,
                'status' => [
                    'id' => $donation->status,
                    'label' => $donation->status_label,
                    'description' => $donation->status_description
                ],
                'amount' => $donation->amount,
                'total_amount' => $donation->total_amount,
                'currency' => $donation->currency_label,
                'gateway' =>['label' => $donation->gateway_label, 'icon' => $donation->gateway_icon],
                'payment_method' => [
                    'label' => $donation->payment_method_label,
                    'main_icon_url' => $donation->payment_method_main_icon_url,
                    'category_label' => $donation->payment_method_category_label,
                    'category_icon' => $donation->payment_method_category_icon
                ],
            ];
        }

        return $result;

    }

}