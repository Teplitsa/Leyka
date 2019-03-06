<?php if( !defined('WPINC') ) die;

/**
 * The default procedure of mailout to all donors for campaigns with reached targets.
 */

if(leyka_options()->opt('send_donor_emails_on_campaign_target_reaching')) {

    $reached_targets_campaigns = get_posts(array(
        'post_type' => Leyka_Campaign_Management::$post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'post__in' => empty($_GET['mailout_campaign_id']) ? false : array((int)$_GET['mailout_campaign_id']),
        'meta_query' => array(
            'relation' => 'AND',
            array('key' => 'target_state', 'value' => 'is_reached'),
            array(
                'relation' => 'OR',
                array('key' => '_leyka_target_reaching_mailout_sent', 'value' => false),
                array('key' => '_leyka_target_reaching_mailout_sent', 'value' => '1', 'compare' => 'NOT EXISTS'),
            ),
        ),
    ));

    foreach($reached_targets_campaigns as $campaign) {

        $campaign = new Leyka_Campaign($campaign);
        $donations = $campaign->getDonations(array('funded'));

        $mailout_list = array();

        // Create the campaign mailout list:
        foreach($donations as $donation) {

            if( !$donation->donor_email ) {
                continue;
            }

            if(empty($mailout_list[$donation->donor_email])) {
                $mailout_list[$donation->donor_email] = array(
                    'donor_name' => $donation->donor_name,
                    'amount_donated_to_campaign' => $donation->amount,
                    'currency_donated_to_campaign' => $donation->currency_label,
                    'donations' => array(
                        array(
                            'amount' => $donation->amount,
                            'currency_label' => $donation->currency_label,
                            'gateway_label' => $donation->gateway_label,
                            'pm_label' => $donation->pm_label,
                            'date_label' => $donation->date_label,
                            'date' => $donation->date_timestamp,
                        )
                    )
                );
            } else {

                if(empty($mailout_list[$donation->donor_email]['donor_name']) && $donation->donor_name) {
                    $mailout_list[$donation->donor_email]['donor_name'] = $donation->donor_name;
                }

                $mailout_list[$donation->donor_email]['amount_donated_to_campaign'] += $donation->amount;
                $mailout_list[$donation->donor_email]['donations'][] = array(
                    'donation_id' => $donation->id,
                    'amount' => $donation->amount,
                    'currency_label' => $donation->currency_label,
                    'gateway_label' => $donation->gateway_label,
                    'pm_label' => $donation->pm_label,
                    'date_label' => $donation->date_label,
                    'date' => $donation->date_timestamp,
                );

            }

        }

//        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        // Do the campaign mailout:
        $mailout_succeeded = true;
        foreach($mailout_list as $donor_email => $donor_data) {

            $mailout_succeeded = $mailout_succeeded && wp_mail(
                $donor_email, // Email to
                apply_filters( // Email title
                    'leyka_email_campaign_target_reaching_title',
                    leyka_options()->opt('email_campaign_target_reaching_title'),
                    $donor_data,
                    $campaign
                ),
                wpautop(str_replace( // Email text
                    array(
                        '#SITE_NAME#',
                        '#SITE_EMAIL#',
                        '#ORG_NAME#',
                        '#DONOR_NAME#',
                        '#DONOR_EMAIL#',
                        '#SUM#',
                        '#CAMPAIGN_NAME#',
                        '#CAMPAIGN_TARGET#',
                        '#CAMPAIGN_PURPOSE#',
                    ),
                    array(
                        get_bloginfo('name'),
                        get_bloginfo('admin_email'),
                        leyka_options()->opt('org_full_name'),
                        $donor_data['donor_name'] ? $donor_data['donor_name'] : __('dear donor', 'leyka'),
                        $donor_email,
                        $donor_data['amount_donated_to_campaign'].' '.$donor_data['currency_donated_to_campaign'],
                        $campaign->title,
                        $campaign->target,
                        $campaign->payment_title,
                    ),
                    leyka_options()->opt('email_campaign_target_reaching_text')
                )),
                array(
                    'Content-Type: text/html; charset=UTF-8',
                    'From: '.apply_filters( // Email additional headers
                        'leyka_campaign_target_reaching_email_from_name',
                        leyka_options()->opt_safe('email_from_name'),
                        $donor_email,
                        $donor_data,
                        $campaign
                    ).' <'.leyka_options()->opt_safe('email_from').'>',
                )
            );

        }

        $campaign->target_reaching_mailout_sent = true;

        if( !$mailout_succeeded ) {
            $campaign->target_reaching_mailout_errors = true;
        }

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
//        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

    }

}