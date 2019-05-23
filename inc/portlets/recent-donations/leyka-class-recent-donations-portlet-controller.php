<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

class Leyka_Recent_Donations_Portlet_Controller extends Leyka_Portlet_Controller {

    protected static $_instance;

    public function get_template_data(array $params = array()) {

        $params['number'] = empty($params['number']) || (int)$params['number'] <= 0 ? 5 : $params['number'];

        $result = array();
        $donations = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => array('submitted', 'funded', 'failed',),
            'posts_per_page' => $params['number'],
        ));

        foreach($donations as $donation) {

            $donation = new Leyka_Donation($donation);
            $result[] = array(
                'id' => $donation->id,
                'type' => $donation->type,
                'donor_name' => $donation->donor_name,
                'donor_email' => $donation->donor_email,
                'date_time' => $donation->date_time_label,
                'campaign_title' => $donation->campaign_title,
                'campaign_id' => $donation->campaign_id,
                'status' => $donation->status,
                'amount' => $donation->amount,
                'currency' => $donation->currency_label,
            );
        }

        return $result;

    }

}