<?php /** The procedure to send emails with some time passed after some event. */

require_once 'procedures-common.php';

if( !defined('WPINC') ) die;

// The procedure should be called no more than once per day:
if(get_transient('leyka_last_deferred_emails_date') === date('d.m.Y') && !leyka_options()->opt('plugin_debug_mode')) {
    return;
} else {
    set_transient('leyka_last_deferred_emails_date', date('d.m.Y'), 60*60*24);
}

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit', 268435456); // 256 Mb, just in case

// Cancelled recurring - Donors notifications:
if(leyka_options()->opt('send_recurring_canceling_donor_notification_email')) {

    $mailout_delay_days = absint(leyka_options()->opt('recurring_canceling_donor_notification_emails_defer_by'));
    $recurring_subscription_date_timestamp = strtotime("-$mailout_delay_days days");
    $recurring_subscription_date = date('Y-m-d', $recurring_subscription_date_timestamp);

    $recurring_subscriptions = get_posts([
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' => 'funded',
        'post_parent' => 0,
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'leyka_payment_type', 'value' => 'rebill',],
            ['key' => 'leyka_recurrents_cancel_date', 'value' => strtotime($recurring_subscription_date.' 00:00'), 'compare' => '>=',],
            ['key' => 'leyka_recurrents_cancel_date', 'value' => strtotime($recurring_subscription_date.' 23:59'), 'compare' => '<=',],
        ],
    ]);

    foreach($recurring_subscriptions as $recurring_subscription) {

        $recurring_subscription = new Leyka_Donation($recurring_subscription);

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        if( !$recurring_subscription || !$recurring_subscription->donor_email ) {
            continue;
        }

        // Check for the current subscription Donor's later active subscriptions to the current campaign:
        $later_recurring_subscriptions = new WP_Query([
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'post_parent' => 0,
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'donor_email', 'value' => $recurring_subscription->donor_email,],
                ['key' => 'leyka_payment_type', 'value' => 'rebill',],
                ['key' => 'leyka_campaign_id', 'value' => $recurring_subscription->campaign_id],
                ['key' => '_rebilling_is_active', 'value' => 1],
                [
                    'key' => 'leyka_recurrents_cancel_date',
                    'value' => date('Y-m-d H:i', $recurring_subscription_date_timestamp),
                    'compare' => '>',
                ],
            ],
        ]);

        if($later_recurring_subscriptions->found_posts) {
            continue;
        }
        // Check - END

        $campaign = new Leyka_Campaign($recurring_subscription->campaign_id);

        $email_placeholders = [
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_TECH_SUPPORT_EMAIL#',
            '#ORG_NAME#',
            '#DONATION_ID#',
            '#DONATION_TYPE#',
            '#DONOR_NAME#',
            '#DONOR_EMAIL#',
            '#DONOR_COMMENT#',
            '#PAYMENT_METHOD_NAME#',
            '#CAMPAIGN_NAME#',
            '#CAMPAIGN_URL#',
            '#PURPOSE#',
            '#CAMPAIGN_TARGET#',
            '#SUM#',
            '#DATE#',
            '#RECURRING_SUBSCRIPTION_CANCELLING_LINK#',
            '#DONOR_ACCOUNT_LOGIN_LINK#',
        ];
        $email_placeholder_values = [
            get_bloginfo('name'),
            get_bloginfo('admin_email'),
            leyka_options()->opt('tech_support_email'),
            leyka_options()->opt('org_full_name'),
            $recurring_subscription->id,
            leyka_get_payment_types_list($recurring_subscription->type),
            $recurring_subscription->donor_name ? $recurring_subscription->donor_name : __('dear donor', 'leyka'),
            $recurring_subscription->donor_email ? $recurring_subscription->donor_email : __('unknown email', 'leyka'),
            $recurring_subscription->donor_comment,
            $recurring_subscription->payment_method_label,
            $campaign->title,
            $campaign->url,
            $campaign->payment_title,
            $campaign->target,
            $recurring_subscription->amount.' '.$recurring_subscription->currency_label,
            $recurring_subscription->date,
            apply_filters(
                'leyka_'.$recurring_subscription->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
                $recurring_subscription
            ),
        ];

        // Donor account login link, if needed:
        if(leyka_options()->opt('donor_accounts_available')) {

            $donor_account_login_text = '';

            if($recurring_subscription->donor_account_error) { // Donor account wasn't created due to some error
                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), get_option('admin_email'));
            } else if($recurring_subscription->donor_account_id) {

                try {
                    $donor = new Leyka_Donor($recurring_subscription->donor_account_id);
                } catch(Exception $e) {
                    $donor = false;
                }

                $donor_account_login_text = $donor && $donor->account_activation_code ?
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$recurring_subscription->donor_account_id));

            }

            $email_placeholder_values[] = apply_filters(
                'leyka_email_donor_acccount_link',
                $donor_account_login_text,
                $recurring_subscription,
                $campaign
            );

        } else {
            $email_placeholder_values[] = ''; // Replace '#DONOR_ACCOUNT_LOGIN_LINK#' with empty string
        }

        $res = wp_mail(
            $recurring_subscription->donor_email,
            apply_filters(
                'leyka_email_recurring_canceling_donor_notification_title',
                leyka_options()->opt('recurring_canceling_donor_notification_emails_title'),
                $recurring_subscription, $campaign
            ),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters(
                    'leyka_email_recurring_canceling_donor_notification_text',
                    leyka_options()->opt('recurring_canceling_donor_notification_emails_text'),
                    $recurring_subscription, $campaign
                )
            )),
            ['From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $recurring_subscription,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',]
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

    }

}
// Cancelled recurring - Donors notifications - END