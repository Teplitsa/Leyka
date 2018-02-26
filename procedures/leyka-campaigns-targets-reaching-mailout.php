<?php if( !defined('WPINC') ) die;

/**
 * The default procedure of mailout to all donors for campaigns with reached targets.
 */

/** @todo WARNING: do each campaign mailout only once!!! Use special meta for it ("target_reaching_mailout_sent"). */

if(leyka_options()->opt('send_donor_emails_on_campaign_target_reaching')) {

    $reached_targets_campaigns = get_posts(array(
        'post_type' => Leyka_Campaign_Management::$post_type,
//        'post_status' => 'publish', // By default, only published campaigns will be fetched
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array('key' => 'target_state', 'value' => 'is_reached'),
            array(
                'relation' => 'OR',
                array('key' => 'target_reaching_mailout_sent', 'value' => false),
                array('key' => 'target_reaching_mailout_sent', 'value' => '1', 'compare' => 'NOT EXISTS'),
            ),
        ),
    ));

    foreach($reached_targets_campaigns as $campaign) {

        $campaign = new Leyka_Campaign($campaign);
        $donations = $campaign->get_donations(array('funded'));

        foreach($donations as $donation) {
            echo '<pre>'.print_r($donation->id.' - '.$donation->amount_total.' ('.$donation->donor_email.')', 1).'</pre>';
        }

    }

}