<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donations Management class - admin elements.
 **/

class Leyka_Donation_Management extends Leyka_Singleton {

    protected static $_instance;

    public static $post_type = 'leyka_donation'; // For "post" Donations storage type only

    protected function __construct() {

        if(leyka_get_donations_storage_type() === 'post') {

            add_action('add_meta_boxes', [$this, 'add_metaboxes']); // Add Donation PT metaboxes
            add_action('transition_post_status',  [$this, 'donation_status_changed'], 10, 3);

            // If Donation is deleted permanently, trigger donation_status_changed() manually:
            add_action('before_delete_post', function($post_id, WP_Post $donation_post){

                if(is_a($donation_post, 'WP_Post') && $donation_post->post_type !== self::$post_type) {
                    return;
                }

                $this->donation_status_changed('deleted', $donation_post->post_status, $donation_post);

            }, 10, 2);

        }

        add_action('wp_ajax_leyka_send_donor_email', [$this, 'ajax_send_donor_email']);

        /** Donors data refresh actions */
        // If some funded Donation data are changed, order its Donor's data cache refreshing:
        function leyka_order_donation_to_refresh($donation_id) {

            $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
            if($donation && $donation->status === 'funded') {
                Leyka_Donor::order_donor_data_refreshing($donation_id);
            }

        }

        // Existing Donation status changed to/from "funded":
        add_action('leyka_donation_funded_status_changed', function($donation_id, $old_status, $new_status){
            if($old_status === 'funded' || $new_status === 'funded') {

                if(leyka_options()->opt('donor_management_available')) {

                    Leyka_Donor::create_donor_from_donation($donation_id);
                    Leyka_Donor::order_donor_data_refreshing($donation_id);

                }

            }
        }, 10, 3);

        add_action('leyka_new_donation_added', function($donation_id){

            $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
            if($donation && $donation->status === 'funded') {
                Leyka_Donor::create_donor_from_donation($donation_id);
            }

        }, 9);
        add_action('leyka_new_donation_added', 'leyka_order_donation_to_refresh');
        add_action('leyka_donation_recurring_activity_changed', 'leyka_order_donation_to_refresh');
        add_action('leyka_donation_amount_changed', 'leyka_order_donation_to_refresh');
        add_action('leyka_donation_total_amount_changed', 'leyka_order_donation_to_refresh');
        add_action('leyka_donation_pm_changed', 'leyka_order_donation_to_refresh');
        add_action('leyka_donation_campaign_changed', 'leyka_order_donation_to_refresh');
        /** Donors data refresh actions - END */

	}

    public function set_admin_messages($messages) {

        $messages[self::$post_type] = [
            0 => '', // Unused. Messages start at index 1.
            1 => __('Donation updated.', 'leyka'),
            2 => __('Field updated.', 'leyka'),
            3 => __('Field deleted.', 'leyka'),
            4 => __('Donation updated.', 'leyka'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ?
                sprintf(
                    __('Donation restored to revision from %s', 'leyka'),
                    wp_post_revision_title((int)$_GET['revision'], false)
                ) : false,
            6 => __('Donation published.', 'leyka'),
            7 => __('Donation saved.', 'leyka'),
            8 => __('Donation submitted.', 'leyka'),
            9 => sprintf(
                __('Donation scheduled for: <strong>%1$s</strong>.', 'leyka'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__( 'M j, Y @ G:i'), strtotime(get_post()->post_date))
            ),
            10 => __('Donation draft updated.', 'leyka'),
        ];

        return $messages;

    }

    public function donation_status_changed($new_status, $old_status, $donation = null) {
        // WARNING: don't use type hinting for $donation argument here (it may be WP_Post or Leyka_Donation_Base)

        if(
            $new_status === $old_status
            || !$donation
            || (is_a($donation, 'WP_Post') && $donation->post_type !== self::$post_type)
        ) {
            return;
        }

        $donation = Leyka_Donations::get_instance()->get_donation($donation);

        if($old_status === 'new' && !in_array($new_status, ['trash', 'deleted'])) {
            do_action('leyka_new_donation_added', $donation->id);
        }

        if($new_status === 'funded' || $old_status === 'funded') {

            do_action('leyka_donation_funded_status_changed', $donation->id, $old_status, $new_status);

            // Campaign total funded amount refresh:
            $campaign = new Leyka_Campaign($donation->campaign_id);
            $campaign->update_total_funded_amount($donation, $old_status === 'funded' ? 'remove' : 'add');

        }

    }

    public static function send_all_emails($donation, $send_to_managers = true) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);

        if( !$donation ) {
            return false;
        }

        if($donation->type === 'single' || $donation->type === 'correction') {
            Leyka_Donation_Management::send_donor_thanking_email_on_single($donation);
        } else if($donation->is_init_recurring_donation) { // Init recurring
            Leyka_Donation_Management::send_donor_thanking_email_on_recurring_init($donation);
        } else if($donation->type === 'rebill') { // Non-init recurring
            Leyka_Donation_Management::send_donor_thanking_email_on_recurring_ongoing($donation);
        }

        if( !!$send_to_managers && leyka()->opt('donations_managers_emails') ) {

            if(
                ($donation->payment_type === 'single' && leyka()->opt('notify_donations_managers')) ||
                ($donation->payment_type === 'rebill' && leyka()->opt('notify_managers_on_recurrents'))
            ) {
                Leyka_Donation_Management::send_managers_notifications($donation);
            }
        }

        return true;

    }

    /** Ajax handler method */
    public function ajax_send_donor_email() {

        if(empty($_POST['donation_id']) || !wp_verify_nonce($_POST['nonce'], 'leyka_donor_email')) {
            return;
        }

        $donation = Leyka_Donations::get_instance()->get_donation($_POST['donation_id']);

        if($donation && Leyka_Donation_Management::send_all_emails($donation, false)) {
            die(__('Grateful email has been sent to the donor', 'leyka'));
        } else {
            die(__("For some reason, we can't send this email right now :( Please, try again later.", 'leyka'));
        }

    }

    /**
     * Send a donor thanking email on single donation.
     *
     * @param $donation Leyka_Donation_Base|integer
     * @return boolean
     */
    public static function send_donor_thanking_email_on_single($donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);
        $donor_email = leyka_email_to_punycode($donation->donor_email);

        if( !$donor_email ) {
            $donor_email = leyka_pf_get_donor_email_value();
        }

        if( !$donation || !$donor_email || $donation->donor_email_date ) {
            return false;
        }

        if(
            ($donation->type !== 'single' && $donation->type !== 'correction')
            || !leyka_options()->opt('send_donor_thanking_emails')
        ) {
            return false;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $email_placeholders = [
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_URL#',
            '#SITE_TECH_SUPPORT_EMAIL#',
            '#ORG_NAME#',
            '#ORG_SHORT_NAME#',
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
            home_url(),
            leyka_options()->opt('tech_support_email'),
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_short_name'),
            $donation->id,
            $donation->type_label,
            $donation->donor_name ? : __('dear donor', 'leyka'),
            $donation->donor_email ? : __('unknown email', 'leyka'),
            $donation->donor_comment,
            $donation->payment_method_label,
            $campaign->title,
            $campaign->url,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
                $donation
            ),
        ];

        // Donor account login link:
        if(leyka_options()->opt('donor_accounts_available')) {

            $donor_account_login_text = '';

            if($donation->donor_account_error) { // Donor account wasn't created due to some error
                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), leyka_get_website_tech_support_email());
            } else if($donation->donor_account_id) {

                try {
                	$donor = new Leyka_Donor($donation->donor_account_id);
                } catch(Exception $e) {
                    $donor = false;
                }

                $donor_account_login_text = $donor && $donor->account_activation_code ?
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$donation->donor_account_id));

            }

            $email_placeholder_values[] = apply_filters(
                'leyka_email_donor_acccount_link',
                $donor_account_login_text,
                $donation,
                $campaign
            );

        } else {
            $email_placeholder_values[] = ''; // Replace '#DONOR_ACCOUNT_LOGIN_LINK#' with empty string
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = wp_mail(
            $donor_email,
            apply_filters('leyka_email_thanks_title', leyka_options()->opt('email_thanks_title'), $donation, $campaign),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters('leyka_email_thanks_text', leyka_options()->opt('email_thanks_text'), $donation, $campaign)
            )),
            [
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            ]
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        if($res) {
            $donation->donor_email_date = current_time('timestamp');
        }

        return $res && $donation->donor_email_date;

    }

    public static function send_donor_thanking_email_on_recurring_init($donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);
        $donor_email = leyka_email_to_punycode($donation->donor_email);

        if( !$donor_email ) {
            $donor_email = leyka_pf_get_donor_email_value();
        }

        if( !$donation || !$donor_email || $donation->donor_email_date ) {
            return false;
        }

        if( !$donation->is_init_recurring_donation || !leyka_options()->opt('send_donor_thanking_emails_on_recurring_init')) {
            return false;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $email_placeholders = [
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_URL#',
            '#SITE_TECH_SUPPORT_EMAIL#',
            '#ORG_NAME#',
            '#ORG_SHORT_NAME#',
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
            home_url(),
            leyka_options()->opt('tech_support_email'),
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_short_name'),
            $donation->id,
            $donation->type_label,
            $donation->donor_name ? : __('dear donor', 'leyka'),
            $donation->donor_email ? : __('unknown email', 'leyka'),
            $donation->donor_comment,
            $donation->payment_method_label,
            $campaign->title,
            $campaign->url,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
                $donation
            ),
        ];

        // Donor account login link:
        if(leyka_options()->opt('donor_accounts_available')) {

            $donor_account_login_text = '';

            if($donation->donor_account_error) { // Donor account wasn't created due to some error
                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), leyka_get_website_tech_support_email());
            } else if($donation->donor_account_id) {

                try {
                    $donor = new Leyka_Donor($donation->donor_account_id);
                } catch(Exception $e) {
                    $donor = false;
                }

                $donor_account_login_text = $donor && $donor->account_activation_code ?
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$donation->donor_account_id));

            }

            $email_placeholder_values[] = apply_filters(
                'leyka_email_donor_acccount_link',
                $donor_account_login_text,
                $donation,
                $campaign
            );

        } else {
            $email_placeholder_values[] = ''; // Replace '#DONOR_ACCOUNT_LOGIN_LINK#' with empty string
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = wp_mail(
            $donor_email,
            apply_filters(
                'leyka_email_thanks_title',
                leyka_options()->opt('email_recurring_init_thanks_title'),
                $donation,
                $campaign
            ),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters(
                    'leyka_email_thanks_text',
                    leyka_options()->opt('email_recurring_init_thanks_text'),
                    $donation,
                    $campaign
                )
            )),
            [
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            ]
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        if($res) {
            $donation->donor_email_date = current_time('timestamp');
        }

        return $res && $donation->donor_email_date;

    }

    public static function send_donor_thanking_email_on_recurring_ongoing($donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);
        $donor_email = leyka_email_to_punycode($donation->donor_email);

        if( !$donor_email ) {
            $donor_email = leyka_pf_get_donor_email_value();
        }

        if( !$donation || !$donor_email || $donation->donor_email_date ) {
            return false;
        }

        if(
            $donation->type !== 'rebill'
            || $donation->is_init_recurring_donation
            || !leyka_options()->opt('send_donor_thanking_emails_on_recurring_ongoing')
        ) {
            return false;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $email_placeholders = [
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_URL#',
            '#SITE_TECH_SUPPORT_EMAIL#',
            '#ORG_NAME#',
            '#ORG_SHORT_NAME#',
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
            home_url(),
            leyka_options()->opt('tech_support_email'),
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_short_name'),
            $donation->id,
            $donation->type_label,
            $donation->donor_name ? : __('dear donor', 'leyka'),
            $donation->donor_email ? : __('unknown email', 'leyka'),
            $donation->donor_comment,
            $donation->payment_method_label,
            $campaign->title,
            $campaign->url,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
                $donation
            ),
        ];

        // Donor account login link:
        if(leyka_options()->opt('donor_accounts_available')) {

            $donor_account_login_text = '';

            if($donation->donor_account_error) { // Donor account wasn't created due to some error
                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), leyka_get_website_tech_support_email());
            } else if($donation->donor_account_id) {

                try {
                    $donor = new Leyka_Donor($donation->donor_account_id);
                } catch(Exception $e) {
                    $donor = false;
                }

                $donor_account_login_text = $donor && $donor->account_activation_code ?
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$donation->donor_account_id));

            }

            $email_placeholder_values[] = apply_filters(
                'leyka_email_donor_acccount_link',
                $donor_account_login_text,
                $donation,
                $campaign
            );

        } else {
            $email_placeholder_values[] = ''; // Replace '#DONOR_ACCOUNT_LOGIN_LINK#' with empty string
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = wp_mail(
            $donor_email,
            apply_filters(
                'leyka_email_thanks_title',
                leyka_options()->opt('email_recurring_ongoing_thanks_title'),
                $donation,
                $campaign
            ),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters(
                    'leyka_email_thanks_text',
                    leyka_options()->opt('email_recurring_ongoing_thanks_text'),
                    $donation,
                    $campaign
                )
            )),
            [
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            ]
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        if($res) {
            $donation->donor_email_date = current_time('timestamp');
        }

        return $res && $donation->donor_email_date;

    }

    /** Send all emails in case of a recurring auto-payment */
//    public static function send_all_recurring_emails($donation) {
//
//        if( !leyka_options()->opt('send_donor_thanking_emails_on_recurring_ongoing') ) {
//            return false;
//        }
//
//        $donation = Leyka_Donations::get_instance()->get_donation($donation);
//
//        $donor_email = $donation->donor_email;
//        if( !$donor_email ) {
//            $donor_email = leyka_pf_get_donor_email_value();
//        }
//
//        if( !$donation || !$donor_email || $donation->type !== 'rebill' ) {
//            return false;
//        }
//
//        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');
//
//        $campaign = new Leyka_Campaign($donation->campaign_id);
//
//        $email_placeholders = [
//            '#SITE_NAME#',
//            '#SITE_EMAIL#',
//            '#SITE_URL#',
//            '#SITE_TECH_SUPPORT_EMAIL#',
//            '#ORG_NAME#',
//            '#ORG_SHORT_NAME#',
//            '#DONATION_ID#',
//            '#DONATION_TYPE#',
//            '#DONOR_NAME#',
//            '#DONOR_EMAIL#',
//            '#DONOR_COMMENT#',
//            '#PAYMENT_METHOD_NAME#',
//            '#CAMPAIGN_NAME#',
//            '#CAMPAIGN_URL#',
//            '#PURPOSE#',
//            '#CAMPAIGN_TARGET#',
//            '#SUM#',
//            '#DATE#',
//            '#RECURRING_SUBSCRIPTION_CANCELLING_LINK#',
//            '#DONOR_ACCOUNT_LOGIN_LINK#',
//        ];
//        $email_placeholder_values = [
//            get_bloginfo('name'),
//            get_bloginfo('admin_email'),
//            home_url(),
//            leyka_options()->opt('tech_support_email'),
//            leyka_options()->opt('org_full_name'),
//            leyka_options()->opt('org_short_name'),
//            $donation->id,
//            $donation->type_label,
//            $donation->donor_name ? : __('dear donor', 'leyka'),
//            $donation->donor_email ? : __('unknown email', 'leyka'),
//            $donation->donor_comment,
//            $donation->payment_method_label,
//            $campaign->title,
//            $campaign->url,
//            $campaign->payment_title,
//            $campaign->target,
//            $donation->amount.' '.$donation->currency_label,
//            $donation->date,
//            apply_filters(
//                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
//                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
//                $donation
//            ),
//        ];
//
//        // Donor account login link:
//        if(leyka_options()->opt('donor_accounts_available')) {
//
//            $donor_account_login_text = '';
//
//            if($donation->donor_account_error) { // Donor account wasn't created due to some error
//                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), get_option('admin_email'));
//            } else if($donation->donor_account_id) {
//
//                try {
//                    $donor = new Leyka_Donor($donation->donor_account_id);
//                } catch(Exception $e) {
//                    $donor = false;
//                }
//
//                $donor_account_login_text = $donor && $donor->account_activation_code ?
//                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
//                    sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$donation->donor_account_id));
//
//            }
//
//            $email_placeholder_values[] = apply_filters(
//                'leyka_email_donor_acccount_link',
//                $donor_account_login_text,
//                $donation,
//                $campaign
//            );
//
//        } else {
//            $email_placeholder_values[] = ''; // Replace '#DONOR_ACCOUNT_LOGIN_LINK#' with empty string
//        }
//
//        // Donor thanking email:
//        $res = wp_mail(
//            $donor_email,
//            apply_filters(
//                'leyka_email_thanks_recurring_ongoing_title',
//                leyka_options()->opt('email_recurring_ongoing_thanks_title'),
//                $donation, $campaign
//            ),
//            wpautop(str_replace(
//                $email_placeholders,
//                $email_placeholder_values,
//                apply_filters(
//                    'leyka_email_thanks_recurring_ongoing_text',
//                    leyka_options()->opt('email_recurring_ongoing_thanks_text'),
//                    $donation, $campaign
//                )
//            )),
//            [
//                'From: '.apply_filters(
//                    'leyka_email_from_name',
//                    leyka_options()->opt_safe('email_from_name'),
//                    $donation,
//                    $campaign
//                ).' <'.leyka_options()->opt_safe('email_from').'>',
//            ]
//        );
//
//        if($res) {
//            $donation->donor_email_date = current_time('timestamp');
//        }
//
//        // Donations managers notifying emails:
//        if(leyka_options()->opt('notify_managers_on_recurrents')) {
//            $res &= Leyka_Donation_Management::send_managers_notifications($donation);
//        }
//
//        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
//        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');
//
//        return $res;
//
//    }

    /**
     * @param $donation Leyka_Donation_Base|integer
     * @return bool
     */
    public static function send_managers_notifications($donation) {

        if( !leyka_options()->opt('notify_donations_managers') || !leyka_options()->opt('donations_managers_emails') ) {
            return false;
        }
        /** @todo Managers emails list should be made from 1. donations_managers_emails option value, 2. emails of all "donation managers" WP accounts. */

        $donation = Leyka_Donations::get_instance()->get_donation($donation);

        if( !$donation || $donation->managers_emails_date ) {
            return false;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $placeholders = apply_filters(
            'leyka_email_manager_notification_placeholders', [
                '#SITE_NAME#',
                '#ORG_NAME#',
                '#DONATION_ID#',
                '#DONATION_TYPE#',
                '#DONOR_NAME#',
                '#DONOR_EMAIL#',
                '#DONOR_COMMENT#',
                '#PAYMENT_METHOD_NAME#',
                '#CAMPAIGN_NAME#',
                '#PURPOSE#',
                '#CAMPAIGN_TARGET#',
                '#SUM#',
                '#DATE#',
            ],
            $donation
        );
        $placeholders_values = apply_filters(
            'leyka_email_manager_notification_placeholders_values', [
                get_bloginfo('name'),
                leyka_options()->opt('org_full_name'),
                $donation->id,
                leyka_get_payment_types_list($donation->type),
                $donation->donor_name ? : __('anonymous', 'leyka'),
                $donation->donor_email ? : __('unknown email', 'leyka'),
                $donation->donor_comment,
                $donation->payment_method_label,
                $campaign->title,
                $campaign->payment_title,
                $campaign->target,
                $donation->amount.' '.$donation->currency_label,
                $donation->date,
            ],
            $placeholders,
            $donation
        );

        $email_title = apply_filters(
            'leyka_email_notification_title',
            leyka_options()->opt('email_notification_title'),
            $donation, $campaign
        );
        $email_content = wpautop(str_replace(
            $placeholders,
            $placeholders_values,
            apply_filters(
                'leyka_email_notification_text',
                leyka_options()->opt('email_notification_text'),
                $donation, $campaign
            )
        ));
        $email_headers = [
            'From: '.apply_filters(
                'leyka_email_from_name',
                leyka_options()->opt_safe('email_from_name'),
                $donation,
                $campaign
            ).' <'.leyka_options()->opt_safe('email_from').'>',
        ];

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = true;
        foreach(explode(',', leyka_options()->opt('leyka_donations_managers_emails')) as $email) {

            $email = leyka_email_to_punycode(trim($email));

            if( !$email ) {
                continue;
            }

            if( !wp_mail($email, $email_title, $email_content, $email_headers) ) {
                $res &= false;
            }

        }

        if($res) {
            $donation->set_meta('managers_emails_date', current_time('timestamp'));
        }

        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');
        return true;

    }

    public static function send_error_notifications($donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);

        if( !$donation ) {
            return false;
        }

        $res = true;

        if(leyka_options()->opt('notify_tech_support_on_failed_donations')) { // Notification to Donations managers, if needed

            if($donation->managers_emails_date) {
                return false;
            }

            add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

            $campaign = new Leyka_Campaign($donation->campaign_id);
            $res &= wp_mail(
                leyka_get_website_tech_support_email(),
                apply_filters(
                    'leyka_error_email_notification_title',
                    __('Donation error occured', 'leyka'),
                    $donation, $campaign
                ),
                wpautop(str_replace(
                    [
                        '#SITE_NAME#',
                        '#SITE_URL#',
                        '#ORG_NAME#',
                        '#ORG_SHORT_NAME#',
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
                    ],
                    [
                        get_bloginfo('name'),
                        home_url(),
                        leyka_options()->opt('org_full_name'),
                        leyka_options()->opt('org_short_name'),
                        $donation->id,
                        leyka_get_payment_types_list($donation->type),
                        $donation->donor_name ? : __('anonymous', 'leyka'),
                        $donation->donor_email ? : __('unknown email', 'leyka'),
                        $donation->donor_comment,
                        $donation->payment_method_label,
                        $campaign->title,
                        $campaign->url,
                        $campaign->payment_title,
                        $campaign->target,
                        $donation->amount.' '.$donation->currency_label,
                        $donation->date,
                    ],
                    apply_filters(
                        'leyka_error_email_notification_text',
                        sprintf(__("Hello!\n\nDonation failure detected on the #SITE_NAME# website.\n\nCampaign: #CAMPAIGN_NAME#\nAmount: #SUM#\nPayment method: #PAYMENT_METHOD_NAME#\nType: #DONATION_TYPE#\n\nYou may revise the donation <a href='%s' target='_blank'>here</a>.\n\nYour Leyka", 'leyka'), admin_url('admin.php?page=leyka_donation_info&donation='.$donation->id)),
                        $donation, $campaign
                    )
                )),
                [
                    'From: '.apply_filters(
                        'leyka_email_from_name',
                        leyka_options()->opt_safe('email_from_name'),
                        $donation,
                        $campaign
                    ).' <'.leyka_email_to_punycode(leyka_options()->opt_safe('email_from')).'>',
                ]
            );

            remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        }

        if(leyka_options()->opt('notify_donors_on_failed_donations') && $donation->donor_email) { // Notify Donor, if needed

            add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

            $campaign = new Leyka_Campaign($donation->campaign_id);
            $res &= wp_mail(
                leyka_email_to_punycode($donation->donor_email),
                apply_filters(
                    'leyka_error_donor_email_notification_title',
                    leyka_options()->opt('donation_error_donor_notification_title'),
                    $donation, $campaign
                ),
                wpautop(str_replace(
                    [
                        '#SITE_NAME#',
                        '#SITE_URL#',
                        '#ORG_NAME#',
                        '#ORG_SHORT_NAME#',
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
                    ],
                    [
                        get_bloginfo('name'),
                        home_url(),
                        leyka_options()->opt('org_full_name'),
                        leyka_options()->opt('org_short_name'),
                        $donation->id,
                        leyka_get_payment_types_list($donation->type),
                        $donation->donor_name ? : __('anonymous', 'leyka'),
                        $donation->donor_email ? : __('unknown email', 'leyka'),
                        $donation->donor_comment,
                        $donation->payment_method_label,
                        $campaign->title,
                        $campaign->url,
                        $campaign->payment_title,
                        $campaign->target,
                        $donation->amount.' '.$donation->currency_label,
                        $donation->date,
                    ],
                    apply_filters(
                        'leyka_error_donor_email_notification_text',
                        leyka_options()->opt('donation_error_donor_notification_text'),
                        $donation, $campaign
                    )
                )),
                [
                    'From: '.apply_filters(
                        'leyka_email_from_name',
                        leyka_options()->opt_safe('email_from_name'),
                        $donation,
                        $campaign
                    ).' <'.leyka_email_to_punycode(leyka_options()->opt_safe('email_from')).'>',
                ]
            );

            remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        }

        return $res;

    }

    public function add_metaboxes() {

		remove_meta_box('submitdiv', self::$post_type, 'side'); // Remove default status/publish metabox

        $curr_page = get_current_screen();

        if($curr_page->action === 'add') { // New donation page

            add_meta_box(self::$post_type.'_new_data', __('New donation data', 'leyka'), [__CLASS__, 'new_donation_data_metabox'], self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_status', __('Donation status', 'leyka'), [__CLASS__, 'donation_status_metabox'], self::$post_type, 'side', 'high');

        } else { // View/edit donation page

            add_meta_box(self::$post_type.'_data', __('Donation data', 'leyka'), [__CLASS__, 'donation_data_metabox'], self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_status', __('Donation status', 'leyka'), [__CLASS__, 'donation_status_metabox'], self::$post_type, 'side', 'high');
            add_meta_box(self::$post_type.'_emails_status', __('Emails status', 'leyka'), [__CLASS__, 'emails_status_metabox'], self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_gateway_response', __('Gateway responses text', 'leyka'), [__CLASS__, 'gateway_response_metabox'], self::$post_type, 'normal', 'low');

        }

	}

    public static function new_donation_data_metabox() {

        $campaign_id = empty($_GET['campaign_id']) ? '' : absint($_GET['campaign_id']);
        $campaign = new Leyka_Campaign($campaign_id);?>

	<fieldset class="leyka-set campaign">
		<legend><?php _e('Campaign Data', 'leyka');?></legend>

        <div class="leyka-ddata-string">
            <label for="campaign-select"><?php echo _x('Campaign', 'In subjective case', 'leyka');?>:</label>
			<div class="leyka-ddata-field">

                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input" value="<?php echo $campaign_id ? $campaign->title : '';?>" placeholder="<?php _e('Select a campaign', 'leyka');?>">
                <input type="hidden" id="campaign-id" class="leyka-campaigns-select autocomplete-select" name="campaign-id" value="<?php echo $campaign_id;?>" data-campaign-payment-title-selector="#new-donation-purpose">

				<div id="campaign_id-error" class="field-error"></div>

			</div>
        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Donation purpose', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
				<div id="new-donation-purpose" class="text-line"><?php echo $campaign_id ? $campaign->payment_title : '';?></div>
			</div>
        </div>

	</fieldset>

	<fieldset class="leyka-set donor">
		<legend><?php _e('Donor Data', 'leyka');?></legend>

        <div class="leyka-ddata-string">
            <label for="donor-name"><?php _e('Name', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <input type="text" id="donor-name" name="donor-name" placeholder="<?php _e("Enter donor's name, or leave it empty for anonymous donation", 'leyka');?>" value="">
			</div>
		</div>

		<div class="leyka-ddata-string">
            <label for="donor-email"><?php _e('Email', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
                <input type="text" id="donor-email" name="donor-email" placeholder="<?php _e("Enter donor's email", 'leyka');?>" value="">
                <div id="donor_email-error" class="field-error"></div>
            </div>
        </div>

        <?php if(leyka_options()->opt_template('show_donation_comment_field')) {?>
        <div class="leyka-ddata-string">
            <label for="donor-comment"><?php _e("Donor's comment", 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <textarea type="text" id="donor-comment" name="donor-comment"></textarea>
                <div id="donor_comment-error" class="field-error"></div>
            </div>
        </div>
        <?php }?>
	</fieldset>

	<fieldset class="leyka-set donation">

		<legend><?php _e('Donation Data', 'leyka');?></legend>

        <?php $main_currency_id = leyka_get_country_currency();?>

        <div class="leyka-ddata-string">
            <label for="donation-amount"><?php _e('Amount', 'leyka');?>:</label>

			<div class="leyka-ddata-field">

				<input type="text" id="donation-amount" name="donation-amount" placeholder="<?php _e('Enter the donation amount', 'leyka');?>" value=""> <?php echo leyka_get_currency_label();?><br>
                <input type="hidden" id="donation-currency" name="donation-currency" value="<?php echo $main_currency_id;?>">

				<small class="field-help howto">
                    <?php _e('Amount may be negative for correctional donations.', 'leyka');?>
                </small>

				<div id="donation_amount-error" class="field-error"></div>

			</div>
        </div>
        <div class="leyka-ddata-string">
            <label for="donation-amount"><?php _e('Total amount', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
                <input type="text" id="donation-amount-total" name="donation-amount-total" placeholder="<?php _e('Enter the donation total amount', 'leyka');?>" value=""> <?php echo leyka_get_currency_label();?><br>
                <small class="field-help howto">
                    <?php
                    /** @todo Add a checkbox here (unckecked by default) to calculate total amount based on current commission. */
                    _e('Leave empty to make the total amount value equal to the amount value.', 'leyka');?>
                </small>
                <div id="donation_amount_total-error" class="field-error"></div>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <label for="donation-pm"><?php _e('Payment method', 'leyka');?>:</label>

			<div class="leyka-ddata-field">
            <select id="donation-pm" name="donation-pm">
                <option value="" selected="selected"><?php _e('Select a payment method', 'leyka');?></option>
                <?php foreach(leyka_get_gateways() as $gateway) {

                    /** @var Leyka_Gateway $gateway */
                    $pm_list = $gateway->get_payment_methods();
                    if($pm_list) {?>

                        <optgroup label="<?php echo $gateway->name;?>">

                        <?php foreach($pm_list as $pm) {?>
                            <option value="<?php echo $pm->full_id;?>"><?php echo $pm->name;?></option>
                        <?php }?>
                        </optgroup>

                    <?php }?>

                <?php }?>
                <option value="custom"><?php _e('Custom payment info', 'leyka');?></option>
            </select>

            <input type="text" id="custom-payment-info" name="custom-payment-info" placeholder="<?php _e('Enter some info about source of a new donation', 'leyka');?>" style="display: none;" value="" maxlength="255">

            <div id="donation_pm-error" class="field-error"></div>
			</div>

        </div>

        <input type="hidden" id="payment-type-hidden" name="payment-type" value="correction">

        <?php foreach(leyka_get_gateways() as $gateway) {?>
            <div id="<?php echo $gateway->id;?>-fields" class="leyka-ddata-string gateway-fields" style="display: none;">
                <?php $gateway->display_donation_specific_data_fields();?>
            </div>
        <?php }?>

        <div class="leyka-ddata-string">
            <label for="donation-date-view"><?php _e('Donation date', 'leyka');?>:</label>

            <div class="leyka-ddata-field">

                <input type="text" id="donation-date-view" class="leyka-datepicker" value="<?php echo date(get_option('date_format'));?>" data-min-date="-5Y" data-max-date="+1Y" data-alt-field="#donation-date" data-alt-format="yy-mm-dd">
                <input type="hidden" id="donation-date" name="donation_date" value="<?php echo date('Y-m-d');?>">

            </div>
        </div>

	</fieldset>

    <?php }

    public static function donation_data_metabox() {

        $donation_id = empty($_GET['donation']) ? false : absint($_GET['donation']);
        $donation = Leyka_Donations::get_instance()->get($donation_id);

        $campaign = new Leyka_Campaign($donation->campaign_id);?>

	<fieldset class="leyka-set campaign">
		<legend><?php _e('Campaign Data', 'leyka');?></legend>

        <div class="leyka-ddata-string">

			<label><?php echo _x('Campaign', 'In subjective case', 'leyka');?>:</label>
			<div class="leyka-ddata-field">

			<?php if($campaign->id && $campaign->status == 'publish') {?>

			<span class="text-line">
                <span class="campaign-name"><?php echo htmlentities($campaign->title, ENT_QUOTES, 'UTF-8');?></span>
                <span class="campaign-actions">
                    <a href="<?php echo admin_url('/post.php?action=edit&post='.$campaign->id);?>"><?php _e('Edit campaign', 'leyka');?></a>
                    <a href="<?php echo $campaign->url;?>" target="_blank" rel="noopener noreferrer"><?php _e('Preview campaign', 'leyka');?></a>
                </span>
            </span>

			<?php } else {
				echo '<span class="text-line">'.__('the campaign has been removed or drafted', 'leyka').'</span>';
			}?>

			</div>
		</div>

		<div class="leyka-ddata-string">

			<label><?php _e('Donation purpose', 'leyka');?>:</label>

			<div class="leyka-ddata-field">
                <span id="campaign-payment-title" class="text-line">
                    <?php echo $campaign->id ? $campaign->payment_title : $donation->title;?>
                </span>
            </div>

        </div>

		<div class="set-action">

            <div id="campaign-select-trigger" class="button"><?php _e('Connect this donation to another campaign', 'leyka');?></div>

            <div id="campaign-select-fields" style="display: none;">

                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input" value="<?php echo $campaign->title;?>" placeholder="<?php _e('Select a campaign', 'leyka');?>">
                <input type="hidden" id="campaign-id" class="leyka-campaigns-select autocomplete-select" name="campaign-id" value="<?php echo $campaign->id;?>" data-campaign-payment-title-selector="#campaign-payment-title">

                <div id="cancel-campaign-select" class="button"><?php _e('Cancel', 'leyka');?></div>
            </div>

            <div id="campaign_id-error" class="field-error"></div>

		</div>

	</fieldset>

	<fieldset class="leyka-set donor">
		<legend><?php _e('Donor Data', 'leyka');?></legend>

		<div class="leyka-ddata-string">
            <label for="donor-name"><?php _e('Name', 'leyka');?>:</label>
			<div class="leyka-ddata-field">

            <?php if($donation->type === 'correction' || leyka_options()->opt('donors_data_editable')) {?>
                <input type="text" id="donor-name" name="donor-name" placeholder="<?php _e("Enter donor's name, or leave it empty for anonymous donation", 'leyka');?>" value="<?php echo $donation->donor_name;?>">
            <?php } else {?>
                <span class="fake-input">
                    <?php echo $donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka');?>
                </span>
            <?php }?>

            </div>
        </div>

		<div class="leyka-ddata-string">
            <label for="donor-email"><?php _e('Email', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
            <?php if($donation->type === 'correction' || leyka_options()->opt('donors_data_editable')) {?>

                <input type="text" id="donor-email" name="donor-email" placeholder="<?php _e("Enter donor's email", 'leyka');?>" value="<?php echo $donation->donor_email;?>">
                <div id="donor_email-error" class="field-error"></div>

            <?php } else {?>

                <span class="fake-input">
                    <?php echo $donation->donor_email ? htmlentities($donation->donor_email, ENT_QUOTES, 'UTF-8') : '&ndash;';?>
                </span>
            <?php }?>
            </div>
        </div>

        <?php if($donation->type !== 'correction') { // Additional fields

            foreach(leyka_options()->opt('additional_donation_form_fields_library') as $field_id => $field_settings) {

                if(is_array($donation->additional_fields) && !empty($donation->additional_fields[$field_id])) {?>

                    <div class="leyka-ddata-string">

                        <label for="donor-<?php echo $field_id;?>"><?php echo $field_settings['title'];?>:</label>

                        <div class="leyka-ddata-field"><span class="fake-input">
                            <?php echo apply_filters(
                                'leyka_admin_donation_info_additional_field_content',
                                $donation->additional_fields[$field_id],
                                $field_id,
                                $donation
                            );?>
                        </span></div>

                    </div>

                <?php }?>

            <?php }

        }?>

        <?php if(leyka_options()->opt_template('show_donation_comment_field') || $donation->donor_comment) {?>
        <div class="leyka-ddata-string">
            <label for="donor-comment"><?php _e('Comment', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
            <?php if(
                leyka_options()->opt_template('show_donation_comment_field') &&
                ($donation->type === 'correction' || leyka_options()->opt('donors_data_editable'))
            ) {?>

                <textarea id="donor-comment" name="donor-comment"><?php echo $donation->donor_comment;?></textarea>
                <div id="donor_comment-error" class="field-error"></div>

            <?php } else {?>
                <span class="fake-input"><?php echo esc_html($donation->donor_comment);?></span>
            <?php }?>
            </div>
        </div>
        <?php }?>
	</fieldset>

	<fieldset class="leyka-set donation">

		<legend><?php _e('Donation Data', 'leyka');?></legend>

        <?php do_action('leyka_donation_info_data_pre_content', $donation);?>

        <div class="leyka-ddata-string">
            <label><?php _e('Amount', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>

                <input type="text" id="donation-amount" name="donation-amount" placeholder="<?php _e("Enter donation amount", 'leyka');?>" value="<?php echo $donation->amount;?>"> <?php echo $donation->currency_label;?>

                <div id="donation_amount-error" class="field-error"></div>

            <?php } else {?>

                <span class="fake-input">
                    <?php echo $donation->amount ? $donation->amount.' '.$donation->currency_label : '';?>
                </span>

            <?php }?>
            </div>
        </div>
        <div class="leyka-ddata-string">
            <label for="donation-amount-total"><?php _e('Total amount', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>

                <input type="text" id="donation-amount-total" name="donation-amount-total" placeholder="<?php _e('Enter the donation total amount', 'leyka');?>" value="<?php echo $donation->amount_total;?>"> <?php echo leyka_get_currency_label();?><br>

                <small class="field-help howto">
                    <?php
                    /** @todo Add a checkbox here (unckecked by default) to calculate total amount based on current commission. */
                    _e('Leave empty to make the total amount value equal to the amount value.', 'leyka');?>
                </small>

                <div id="donation_amount_total-error" class="field-error"></div>

            <?php } else {?>

                <span class="fake-input">
                    <?php echo $donation->amount_total ? $donation->amount_total.' '.$donation->currency_label : '';?>
                </span>

            <?php }?>
            </div>
        </div>

        <div class="leyka-ddata-string">

            <label><?php _e('Payment method', 'leyka');?>:</label>

			<div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>

                <select id="donation-pm" name="donation-pm">

                    <option value="" selected="selected"><?php _e('Select a payment method', 'leyka');?></option>

                    <?php foreach(leyka_get_gateways() as $gateway) {

                        /** @var Leyka_Gateway $gateway */
                        $pm_list = $gateway->get_payment_methods();
                        if($pm_list) {?>

                            <optgroup label="<?php echo $gateway->name;?>">
                            <?php foreach($pm_list as $pm) {?>
                                <option value="<?php echo $pm->full_id;?>" <?php echo $donation->gateway_id === $gateway->id && $donation->pm_id === $pm->id ? 'selected="selected"' : '';?>><?php echo $pm->name;?></option>
                            <?php }?>
                            </optgroup>

                        <?php }?>

                    <?php }?>

                    <option value="custom" <?php echo ($donation->gw_id == 'correction' || !$donation->gw_id) && $donation->pm_id ? 'selected="selected"' : '';?>><?php _e('Custom payment info', 'leyka');?></option>

                </select>

                <input type="text" id="custom-payment-info" name="custom-payment-info" placeholder="<?php _e('Enter the donation source info', 'leyka');?>" <?php echo ($donation->gw_id == 'correction' || !$donation->gw_id) && $donation->pm_id ? '' : 'style="display: none;"';?> value="<?php echo $donation->gw_id == 'correction' || !$donation->gw_id ? $donation->pm_id : '';?>">

            <?php } else {?>

                <span class="fake-input">
                <?php $pm = leyka_get_pm_by_id($donation->pm_full_id, true);
                $gateway = leyka_get_gateway_by_id($donation->gateway_id);

                echo ($pm ? $pm->label : __('Unknown payment method', 'leyka'))
                    .' ('.($gateway ? $gateway->label : __('unknown gateway', 'leyka')).')';?>
			    </span>
            <?php }?>
            </div>

        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Payment type', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <span class="fake-input"><?php echo $donation->type_label;?></span>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <?php $gateway = leyka_get_gateway_by_id($donation->gateway_id);
            if($gateway) {
                $gateway->display_donation_specific_data_fields($donation);
            }?>
        </div>

        <div class="leyka-ddata-string">

            <?php if($donation->is_init_recurring_donation) {?>

                <label><?php _e('Initial donation of the recurring subscription', 'leyka');?>:</label>
                <div class="leyka-ddata-field"><?php echo 'текущее';?></div>

            <?php } else if($donation->init_recurring_donation_id) {?>

                <label><?php _e('Initial donation of the recurring subscription', 'leyka');?>:</label>
                <div class="leyka-ddata-field">
                    <a href="<?php echo admin_url('admin.php?page=leyka_donation_info&donation='.$donation->init_recurring_donation_id);?>">
                        #<?php echo $donation->init_recurring_donation_id;?>
                    </a>
                </div>

            <?php }?>

        </div>

        <div class="leyka-ddata-string">
            <label for="donation-date-view"><?php _e('Donation date', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>

                <input type="text" id="donation-date-view" class="leyka-datepicker" value="<?php echo $donation->date_label;?>" data-min-date="-5Y" data-max-date="+1Y" data-alt-field="#donation-date" data-alt-format="yy-mm-dd">
                <input type="hidden" id="donation-date" name="donation_date" value="<?php echo date('Y-m-d', $donation->date_timestamp);?>">

            <?php } else {?>
                <span class="fake-input"><?php echo $donation->date_time_label;?></span>
            <?php }?>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Donor subscription status', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <span class="fake-input">
                <?php $subscription_status = __('None', 'leyka');
                if($donation->donor_subscribed === true || $donation->donor_subscribed == 1) {
                    $subscription_status = __('Full subscription', 'leyka');
                } else if($donation->donor_subscribed > 0) {
                    $subscription_status = sprintf(__('On <a href="%s">campaign</a> news', 'leyka'), admin_url('post.php?post='.$donation->campaign_id.'&action=edit'));
                }

                echo $subscription_status;?>
                </span>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Donor subscription email', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <span class="fake-input"><?php echo $donation->donor_subscription_email ? $donation->donor_subscription_email : __('none');?></span>
            </div>
        </div>

        <?php do_action('leyka_donation_info_data_post_content', $donation);?>

	</fieldset>

	<?php }

    public static function donation_status_metabox() {

        $donation_id = empty($_GET['donation']) ? false : absint($_GET['donation']);
        $donation = $donation_id ? Leyka_Donations::get_instance()->get($donation_id) : false;

        wp_nonce_field('donation_status_metabox', '_donation_edit_nonce');

        $is_adding_page = empty($_GET['donation']) || !absint($_GET['donation']);?>

        <div class="leyka-status-section select">
            <label for="donation-status"><?php _e('Status', 'leyka');?></label>
            <select id="donation-status" name="donation_status">
                <?php foreach(leyka_get_donation_status_list() as $status => $label) {
                    if($status === 'trash') {
                        continue;
                    }?>
                <option value="<?php echo $status;?>" <?php echo ($donation && $donation->status === $status) || ($is_adding_page && $status === 'funded') ? 'selected' : '';?>>
                    <?php echo $label;?>
                </option>
                <?php }?>
            </select>
		</div>

        <div class="leyka-status-section actions">

            <?php if( !$is_adding_page ) {?>

				<div class="delete-action">
                    <a class="submitdelete deletion" href="<?php echo self::get_donation_delete_link($donation);?>"><?php echo !EMPTY_TRASH_DAYS ? __('Delete Permanently') : __('Move to Trash');?></a>
				</div>

            <?php }?>

            <?php if(current_user_can('leyka_manage_donations')) {?>

				<div class="save-action">
			        <input name="original_funded" type="hidden" id="original_funded" value="<?php _e('Update', 'leyka');?>">
                    <?php submit_button(
                        $is_adding_page ? __('Add the donation', 'leyka') : __('Update', 'leyka'),
                        'primary button-large', 'funded', false,
                        ['accesskey' => 'p', 'data-is-new-donation' => $is_adding_page ? 1 : 0]
                    );?>
				</div>

            <?php }?>

        </div>

        <div class="leyka-status-section log">
            <?php $status_log = $donation ? $donation->status_log : [];
            if($status_log) {?>

                <?php $last_status = end($status_log);
                echo str_replace(
                    ['%status', '%date'],
                    ['<i>'.self::get_status_labels($last_status['status']).'</i>', '<time>'.date(get_option('date_format').', H:i', $last_status['date']).'</time>'],
                    '<div class="leyka-ddata-string last-log">'.__('Last status change: to&nbsp;%status (at&nbsp;%date)', 'leyka').'</div>'
                );?>

                <div id="donation-status-log-toggle"><?php _e('Show/hide full status history', 'leyka');?></div>
                <ul id="donation-status-log" style="display: none;">
                    <?php for($i = 0; $i < count($status_log); $i++) {?>

                    <li>
                        <?php echo str_replace(
                            ['%status', '%date'],
                            [
                                '<i>'.self::get_status_labels($status_log[$i]['status']).'</i>','<time>'.date(get_option('date_format').', '.get_option('time_format'),
                                    $status_log[$i]['date']).'</time>'
                            ],
                            __('%date - %status', 'leyka')
                        );?>
                    </li>

                    <?php }?>
                </ul>

            <?php } else {?>
                <div class="leyka-ddata-string last-log"><?php _e('Last status change: none logged', 'leyka');?></div>
            <?php }?>

        </div>

	<?php }

    public static function emails_status_metabox() {

        $donation_id = empty($_GET['donation']) ? false : absint($_GET['donation']);
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $donor_thanks_date = $donation->donor_email_date;
        $manager_notification_date = $donation->get_meta('managers_emails_date');

		if($donation->donor_email_date) {?>
			<div class="leyka-ddata-string donor has-thanks">
                <?php echo sprintf(
                    __('Grateful email to the donor has been sent (at %s)', 'leyka'),
                    '<time>'.date(get_option('date_format').', H:i</time>', $donation->donor_email_date).'</time>'
                );?>
            </div>
		<?php } else {?>
			<div class="leyka-ddata-string donor no-thanks" data-donation-id="<?php echo $donation->id;?>" data-nonce="<?php echo wp_create_nonce('leyka_donor_email');?>">
				<?php echo sprintf(__("Grateful email hasn't been sent %s", 'leyka'), "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>");?>
			</div>
		<?php }

        echo $manager_notification_date ?
            str_replace(
                '%s',
                '<time>'.date(get_option('date_format').', H:i', $manager_notification_date).'</time>',
                __('Donation managers notifications has been sended (at %s)', 'leyka')
            ) :
            '<div class="leyka-ddata-string manager no-thanks">'.__("Donation managers' notification emails hasn't been sent", 'leyka').'</div>';

    }

    /**
     * @param $donation WP_Post
     */
    public static function gateway_response_metabox() {

        $donation_id = empty($_GET['donation']) ? false : absint($_GET['donation']);
        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);?>

        <div>
            <?php if( !$donation->gateway_response_formatted ) {
                _e('No gateway response has been received', 'leyka');
            } else {

                foreach($donation->gateway_response_formatted as $name => $value) {?>

                <div class="leyka-ddata-string">
                    <span class="label"><?php echo rtrim(mb_ucfirst($name), ':');?>:</span>
                    <?php if(is_array($value)) {?>
                        <ul class="leyka-sub-values-list">
                        <?php foreach($value as $key => $sub_value) {

                            if(is_array($sub_value) || is_object($sub_value)) {
                                continue;
                            }?>

                            <li><?php echo mb_ucfirst($key).': '.$sub_value;?></li>

                        <?php }?>
                        </ul>
                    <?php } else {
                        echo $value;
                    }?>
                </div>

            <?php }

            }?>
        </div>

    <?php }

    public static function subscription_resurring_donations_metabox() {

        $donation_id = empty($_GET['donation']) ? false : absint($_GET['donation']);?>

        <table id="donations-data-table" class="leyka-data-table leyka-donations-list recurring-subscription-donations-table" data-init-recurring-donation-id="<?php echo $donation_id;?>">

            <thead>
                <tr>
                    <td><?php _e('ID', 'leyka');?></td>
                    <td><?php _e('Donor', 'leyka');?></td>
                    <td><?php _e('Amount', 'leyka');?></td>
                    <td><?php _e('Date', 'leyka');?></td>
                    <td><?php _e('Payment method', 'leyka');?></td>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td><?php _e('ID', 'leyka');?></td>
                    <td><?php _e('Donor', 'leyka');?></td>
                    <td><?php _e('Amount', 'leyka');?></td>
                    <td><?php _e('Date', 'leyka');?></td>
                    <td><?php _e('Payment method', 'leyka');?></td>
                </tr>
            </tfoot>

            <tbody><?php // All table data will be received via AJAX ?></tbody>

        </table>
        <?php
    }

	/** Helpers **/

	public static function get_status_labels($status = false) {

        $labels = Leyka::get_donation_statuses();

        if(empty($status)) {
		    return $labels;
        } else if($status == 'publish') {
            return $labels['funded'];
        } else {
		    return empty($labels[$status]) ? false : $labels[$status];
        }

	}

	public static function get_status_descriptions($status = false) {

        $descriptions = leyka()->get_donation_statuses_descriptions();

        if(empty($status)) {
		    return $descriptions;
        } else if($status == 'publish') {
            return $descriptions['funded'];
        } else {
		    return empty($descriptions[$status]) ? false : $descriptions[$status];
        }

	}

    public static function get_donation_delete_link(Leyka_Donation_Base $donation) {
        return admin_url('admin.php?page=leyka_donations&action=delete&donation='.$donation->id.'&_wpnonce='.wp_create_nonce('leyka_delete_donation'));
    }

    public static function get_donation_edit_link(Leyka_Donation_Base $donation) {
        return admin_url('admin.php?page=leyka_donation_info&donation='.$donation->id);
    }

}

function leyka_donation_management() {
    return Leyka_Donation_Management::get_instance();
}
add_action('admin_init', 'leyka_donation_management');

//function leyka_cancel_recurrents_action() {
//
//    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_recurrent_cancel') || empty($_POST['donation_id'])) {
//        die('-1');
//    }
//
//    $donation = Leyka_Donations::get_instance()->get_donation($_POST['donation_id']);
//    do_action('leyka_cancel_recurrents-'.$donation->gateway_id, $donation);
//
//}
//add_action('wp_ajax_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');
//add_action('wp_ajax_nopriv_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');