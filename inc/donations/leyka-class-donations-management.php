<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donation History
 **/
/** @todo Make this class ADMIN ONLY! Transfer all common elements outside. */

class Leyka_Donation_Management extends Leyka_Singleton {

    protected static $_instance;

    public static $post_type = 'leyka_donation';

    protected function __construct() {

        add_filter('post_row_actions', array($this, 'row_actions'), 10, 2);

        add_action('restrict_manage_posts', array($this, 'manage_filters'));
        add_action('pre_get_posts', array($this, 'do_filtering'));

        if(leyka_get_donations_storage_type() === 'post') {

            add_action('add_meta_boxes', array($this, 'add_metaboxes')); // Add Donation PT metaboxes
            add_action('add_meta_boxes', array($this, 'remove_metaboxes'), 100); // Remove unneeded metaboxes

            add_action('save_post_'.self::$post_type, array($this, 'save_donation_data'));

        }

		add_filter('manage_'.self::$post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.self::$post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);
        add_filter('manage_edit-'.self::$post_type.'_columns', array($this, 'remove_columns')); // Remove unneeded columns

        add_filter('manage_edit-'.self::$post_type.'_sortable_columns', array($this, 'manage_sortable_columns'));
        add_filter('request', array($this, 'do_column_sorting'));

        /** Donation status transitions */
        add_action('transition_post_status',  array($this, 'donation_status_changed'), 10, 3);

        add_action('wp_ajax_leyka_send_donor_email', array($this, 'ajax_send_donor_email'));

        // If some funded donation data are changed, order it's donor's data cache refreshing:
        function leyka_order_donation_to_refresh($donation_id) {

            $donation = new Leyka_Donation($donation_id);
            if($donation && $donation->status === 'funded') {
                Leyka_Donor::order_donor_data_refreshing($donation_id);
            }

        }

        // Existing donation status changed to/from "funded":
        add_action('leyka_donation_funded_status_changed', function($donation_id, $old_status, $new_status){
            if($old_status === 'funded' || $new_status === 'funded') {

                Leyka_Donor::create_donor_from_donation($donation_id);
                Leyka_Donor::order_donor_data_refreshing($donation_id);

            }
        }, 10, 3);

        add_action('leyka_new_donation_added', function($donation_id){

            $donation = new Leyka_Donation($donation_id);
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
        // Donors data refresh actions - end

	}

    public function set_admin_messages($messages) {

        $messages[self::$post_type] = array(
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
        );

        return $messages;

    }

    public function row_actions($actions, $donation) {

        $current_screen = get_current_screen();

        if( !$current_screen || !is_object($current_screen) || $current_screen->post_type != self::$post_type ) {
            return $actions;
        }

		if(current_user_can('edit_donation', $donation->ID)) {
			$actions['edit'] = '<a href="'.get_edit_post_link($donation->ID, 1).'">'.__('Edit').'</a>';
		}

        unset($actions['view']);
        unset($actions['inline hide-if-no-js']);

        return $actions;

    }

    public function donation_status_changed($new, $old, WP_Post $donation) {

        if($new === $old || $donation->post_type !== self::$post_type) {
            return;
        }

        $donation = new Leyka_Donation($donation);

        if($old === 'new' && $new !== 'trash') {
            do_action('leyka_new_donation_added', $donation->id);
        } else if($new === 'funded' || $old === 'funded') {

            do_action('leyka_donation_funded_status_changed', $donation->id, $old, $new);

            // Campaign total funded amount refresh:
            $campaign = new Leyka_Campaign($donation->campaign_id);
            $campaign->update_total_funded_amount($donation, $old === 'funded' ? 'remove' : 'add');

        }

    }

    public function manage_filters() {

        if(get_current_screen()->id !== 'edit-'.self::$post_type || !current_user_can('leyka_manage_donations')) {
            return;
        }?>

        <label for="payment-type-select"></label>
        <select id="payment-type-select" name="payment_type">
            <option value="" <?php echo empty($_GET['payment_type']) ? 'selected="selected"' : '';?>><?php _e('Select a payment type', 'leyka');?></option>

            <?php foreach(leyka_get_payment_types_data() as $payment_type => $label) {?>
                <option value="<?php echo $payment_type;?>" <?php echo !empty($_GET['payment_type']) && $_GET['payment_type'] == $payment_type ? 'selected="selected"' : '';?>><?php echo $label;?></option>
            <?php }?>
        </select>

        <label for="gateway-pm-select"></label>
        <select id="gateway-pm-select" name="gateway_pm">
            <option value="" <?php echo empty($_GET['gateway_pm']) ? '' : 'selected="selected"';?>><?php _e('Select a gateway or a payment method', 'leyka');?></option>

        <?php $gw_pm_list = array();
        foreach(leyka_get_gateways() as $gateway) {

            /** @var Leyka_Gateway $gateway */
            $pm_list = $gateway->get_payment_methods();
            if($pm_list)
                $gw_pm_list[] = array('gateway' => $gateway, 'pm_list' => $pm_list);
        }
        $gw_pm_list = apply_filters('leyka_donations_list_gw_pm_filter', $gw_pm_list);

        foreach($gw_pm_list as $element) {?>

            <option value="<?php echo 'gateway__'.$element['gateway']->id;?>" <?php echo !empty($_GET['gateway_pm']) && $_GET['gateway_pm'] == 'gateway__'.$element['gateway']->id ? 'selected="selected"' : '';?>><?php echo $element['gateway']->name;?></option>

            <?php foreach($element['pm_list'] as $pm) {?>
                <option value="<?php echo 'pm__'.$pm->id;?>" <?php echo !empty($_GET['gateway_pm']) && $_GET['gateway_pm'] == 'pm__'.$pm->id ? 'selected="selected"' : '';?>><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$pm->name;?></option>
            <?php }

        }?>

        </select>

        <?php $campaign_title = '';
        if( !empty($_GET['campaign']) && (int)$_GET['campaign'] > 0) {

            $campaign = get_post((int)$_GET['campaign']);
            if($campaign) {
                $campaign_title = $campaign->post_title;
            }

        }?>

        <label for="campaign-select"></label>
        <input id="campaign-select" type="text" data-nonce="<?php echo wp_create_nonce('leyka_get_campaigns_list_nonce');?>" placeholder="<?php _e('Select a campaign', 'leyka');?>" value="<?php echo $campaign_title;?>">
        <input id="campaign-id" type="hidden" name="campaign" value="<?php echo !empty($_GET['campaign']) ? (int)$_GET['campaign'] : '';?>">

        <label for="donor-subscribed-select"></label>
        <select id="donor-subscribed-select" name="donor_subscribed">
            <option value="-" <?php echo !isset($_GET['donor_subscribed']) ? 'selected="selected"' : '';?>><?php _e('Donors subscription', 'leyka');?></option>
            <option value="1" <?php echo isset($_GET['donor_subscribed']) && $_GET['donor_subscribed'] ? 'selected="selected"' : '';?>><?php _e('Subscribed donors', 'leyka');?></option>
            <option value="0" <?php echo isset($_GET['donor_subscribed']) && !$_GET['donor_subscribed'] ? 'selected="selected"' : '';?>><?php _e('Unsubscribed donors', 'leyka');?></option>
        </select>

    <?php }

    public function do_filtering(WP_Query $query) {

        if(is_admin() && $query->is_main_query() && get_current_screen()->id == 'edit-'.self::$post_type) {

            $meta_query = array('relation' => 'AND');

            if( !empty($_REQUEST['campaign']) ) {
                $meta_query[] = array('key' => 'leyka_campaign_id', 'value' => (int)$_REQUEST['campaign']);
            }

            if( !empty($_REQUEST['payment_type']) ) {
                $meta_query[] = array('key' => 'leyka_payment_type', 'value' => $_REQUEST['payment_type']);
            }

            if( !empty($_REQUEST['gateway_pm']) ) {
                if(strpos($_REQUEST['gateway_pm'], 'gateway__') !== false) {
                    $meta_query[] = array(
                        'key' => 'leyka_gateway', 'value' => str_replace('gateway__', '', $_REQUEST['gateway_pm'])
                    );
                } else if(strpos($_REQUEST['gateway_pm'], 'pm__') !== false) {
                    $meta_query[] = array(
                        'key' => 'leyka_payment_method', 'value' => str_replace('pm__', '', $_REQUEST['gateway_pm'])
                    );
                }
            }

            if( isset($_REQUEST['donor_subscribed']) && $_REQUEST['donor_subscribed'] !== '-' ) {
                $meta_query[] = array('key' => 'leyka_donor_subscribed', 'value' => !!$_REQUEST['donor_subscribed']);
            }

            if(count($meta_query) > 1) {
                $query->set('meta_query', $meta_query);
            }

        }

    }

    public static function send_all_emails($donation) {

        $donation = leyka_get_validated_donation($donation);

        if( !$donation ) {
            return false;
        }

        if(leyka()->opt('send_donor_thanking_emails')) {
            Leyka_Donation_Management::send_donor_thanking_email($donation);
        }

        if(leyka()->opt('donations_managers_emails')) {

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

        $donation = new Leyka_Donation((int)$_POST['donation_id']);

        if($donation && Leyka_Donation_Management::send_donor_thanking_email($donation)) {
            die(__('Grateful email has been sent to the donor', 'leyka'));
        } else {
            die(__("For some reason, we can't send this email right now :( Please, try again later.", 'leyka'));
        }

    }

    /**
     * Send a donor thanking email on single & initial recurring donation.
     *
     * @param $donation Leyka_Donation|integer|WP_Post
     * @return boolean
     */
    public static function send_donor_thanking_email($donation) {

        $donation = leyka_get_validated_donation($donation);

        $donor_email = $donation->donor_email;
        if( !$donor_email ) {
            $donor_email = leyka_pf_get_donor_email_value();
        }

        if( !$donation || !$donor_email || $donation->donor_email_date ) {
            return false;
        }

        if(
            ($donation->type === 'single' && !leyka_options()->opt('send_donor_thanking_emails'))
            || ($donation->type === 'rebill' && !leyka_options()->opt('send_donor_thanking_emails_on_recurring_init'))
        ) {
            return false;
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $email_title = $donation->type === 'rebill' ?
            ($donation->init_recurring_payment_id === $donation->id ?
                leyka_options()->opt('email_recurring_init_thanks_title') :
                leyka_options()->opt('email_recurring_ongoing_thanks_title')) :
            leyka_options()->opt('email_thanks_title');

        $email_text = $donation->type === 'rebill' ?
            ($donation->init_recurring_payment_id === $donation->id ?
                leyka_options()->opt('email_recurring_init_thanks_text') :
                leyka_options()->opt('email_recurring_ongoing_thanks_text')) :
            leyka_options()->opt('email_thanks_text');

        $email_placeholders = array(
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#ORG_NAME#',
            '#DONATION_ID#',
            '#DONATION_TYPE#',
            '#DONOR_NAME#',
            '#DONOR_EMAIL#',
            '#PAYMENT_METHOD_NAME#',
            '#CAMPAIGN_NAME#',
            '#PURPOSE#',
            '#CAMPAIGN_TARGET#',
            '#SUM#',
            '#DATE#',
            '#RECURRING_SUBSCRIPTION_CANCELLING_LINK#',
            '#DONOR_ACCOUNT_LOGIN_LINK#',
        );
        $email_placeholder_values = array(
            get_bloginfo('name'),
            get_bloginfo('admin_email'),
            leyka_options()->opt('org_full_name'),
            $donation->id,
            leyka_get_payment_types_data($donation->type),
            $donation->donor_name ? $donation->donor_name : __('dear donor', 'leyka'),
            $donation->donor_email ? $donation->donor_email : __('unknown email', 'leyka'),
            $donation->payment_method_label,
            $campaign->title,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_get_website_tech_support_email()),
                $donation
            ),
        );

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

        $res = wp_mail(
            $donor_email,
            apply_filters('leyka_email_thanks_title', $email_title, $donation, $campaign),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters('leyka_email_thanks_text', $email_text, $donation, $campaign)
            )),
            array('From: '.apply_filters(
                'leyka_email_from_name',
                leyka_options()->opt_safe('email_from_name'),
                $donation,
                $campaign
            ).' <'.leyka_options()->opt_safe('email_from').'>',)
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res &= update_post_meta($donation->id, '_leyka_donor_email_date', current_time('timestamp'));

        if( !$res ) {
            $res = get_post_meta($donation->id, '_leyka_donor_email_date', true) > 0;
        }

        return $res;

    }

    /** Send all emails in case of a recurring auto-payment */
    public static function send_all_recurring_emails($donation) {

        if( !leyka_options()->opt('send_donor_thanking_emails_on_recurring_ongoing') ) {
            return false;
        }

        $donation = leyka_get_validated_donation($donation);

        $donor_email = $donation->donor_email;
        if( !$donor_email ) {
            $donor_email = leyka_pf_get_donor_email_value();
        }

        if( !$donation || !$donor_email || $donation->type !== 'rebill' ) {
            return false;
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $email_placeholders = array(
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_TECH_SUPPORT_EMAIL#',
            '#ORG_NAME#',
            '#DONATION_ID#',
            '#DONATION_TYPE#',
            '#DONOR_NAME#',
            '#DONOR_EMAIL#',
            '#PAYMENT_METHOD_NAME#',
            '#CAMPAIGN_NAME#',
            '#PURPOSE#',
            '#CAMPAIGN_TARGET#',
            '#SUM#',
            '#DATE#',
            '#RECURRING_SUBSCRIPTION_CANCELLING_LINK#',
            '#DONOR_ACCOUNT_LOGIN_LINK#',
        );
        $email_placeholder_values = array(
            get_bloginfo('name'),
            get_bloginfo('admin_email'),
            leyka_options()->opt('tech_support_email'),
            leyka_options()->opt('org_full_name'),
            $donation->id,
            leyka_get_payment_types_data($donation->type),
            $donation->donor_name ? $donation->donor_name : __('dear donor', 'leyka'),
            $donation->donor_email ? $donation->donor_email : __('unknown email', 'leyka'),
            $donation->payment_method_label,
            $campaign->title,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(__('<a href="mailto:%s">write us a letter about it</a>', 'leyka'), leyka_options()->opt('tech_support_email')),
                $donation
            ),
        );

        // Donor account login link:
        if(leyka_options()->opt('donor_accounts_available')) {

            $donor_account_login_text = '';

            if($donation->donor_account_error) { // Donor account wasn't created due to some error
                $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), get_option('admin_email'));
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

        // Donor thanking email:
        $res = wp_mail(
            $donor_email,
            apply_filters(
                'leyka_email_thanks_recurring_ongoing_title',
                leyka_options()->opt('email_recurring_ongoing_thanks_title'),
                $donation, $campaign
            ),
            wpautop(str_replace(
                $email_placeholders,
                $email_placeholder_values,
                apply_filters(
                    'leyka_email_thanks_recurring_ongoing_text',
                    leyka_options()->opt('email_recurring_ongoing_thanks_text'),
                    $donation, $campaign
                )
            )),
            array('From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',)
        );

        if($res) {
            update_post_meta($donation->id, '_leyka_donor_email_date', current_time('timestamp'));
        }

        // Donations managers notifying emails:
        if(leyka_options()->opt('notify_managers_on_recurrents')) {
            $res &= Leyka_Donation_Management::send_managers_notifications($donation);
        }

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        return $res;

    }

    public static function send_managers_notifications($donation) {

        if( !leyka_options()->opt('notify_donations_managers') || !leyka_options()->opt('donations_managers_emails') ) {
            return false;
        }
        /** @todo Managers emails list should be made from 1. donations_managers_emails option value, 2. emails of all "donation managers" WP accounts. */

        $donation = leyka_get_validated_donation($donation);

        if( !$donation || $donation->managers_emails_date ) {
            return false;
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = true;
        foreach(explode(',', leyka_options()->opt('leyka_donations_managers_emails')) as $email) {

            $email = trim($email);

            if( !$email ) {
                continue;
            }

            $campaign = new Leyka_Campaign($donation->campaign_id);
            if( !wp_mail(
                $email,
                apply_filters(
                    'leyka_email_notification_title',
                    leyka_options()->opt('email_notification_title'),
                    $donation, $campaign
                ),
                wpautop(str_replace(
                    array(
                        '#SITE_NAME#',
                        '#ORG_NAME#',
                        '#DONATION_ID#',
                        '#DONATION_TYPE#',
                        '#DONOR_NAME#',
                        '#DONOR_EMAIL#',
                        '#PAYMENT_METHOD_NAME#',
                        '#CAMPAIGN_NAME#',
                        '#PURPOSE#',
                        '#CAMPAIGN_TARGET#',
                        '#SUM#',
                        '#DATE#',
                    ),
                    array(
                        get_bloginfo('name'),
                        leyka_options()->opt('org_full_name'),
                        $donation->id,
                        leyka_get_payment_types_data($donation->type),
                        $donation->donor_name ? $donation->donor_name : __('anonymous', 'leyka'),
                        $donation->donor_email ? $donation->donor_email : __('unknown email', 'leyka'),
                        $donation->payment_method_label,
                        $campaign->title,
                        $campaign->payment_title,
                        $campaign->target,
                        $donation->amount.' '.$donation->currency_label,
                        $donation->date,
                    ),
                    apply_filters(
                        'leyka_email_notification_text',
                        leyka_options()->opt('email_notification_text'),
                        $donation, $campaign
                    )
                )),
                array('From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',)
            ) ) {
                $res &= false;
            }
        }

        if($res) {
            update_post_meta($donation->id, '_leyka_managers_emails_date', current_time('timestamp'));
        }

        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');
        return true;
    }

    public function remove_metaboxes() {
        if(get_post_type() === Leyka_Donation_Management::$post_type) {
            remove_meta_box('wpseo_meta', Leyka_Donation_Management::$post_type, 'normal');
        }
    }

    public function remove_columns($columns) {

        unset(
            $columns['wpseo-score'], $columns['wpseo-score-readability'], $columns['wpseo-title'], $columns['wpseo-metadesc'],
            $columns['wpseo-focuskw'], $columns['wpseo-links'], $columns['wpseo-linked']
        );

        return $columns;

    }

    public function add_metaboxes() {

		remove_meta_box('submitdiv', self::$post_type, 'side'); // Remove default status/publish metabox

        $curr_page = get_current_screen();

        echo '<pre>'.print_r($curr_page, 1).'</pre>';

        if($curr_page->action === 'add') { // New donation page

            add_meta_box(self::$post_type.'_new_data', __('New donation data', 'leyka'), array(__CLASS__, 'new_donation_data_metabox'), self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_status', __('Donation status', 'leyka'), array(__CLASS__, 'donation_status_metabox'), self::$post_type, 'side', 'high');

        } else { // View/edit donation page

            add_meta_box(self::$post_type.'_data', __('Donation data', 'leyka'), array(__CLASS__, 'donation_data_metabox'), self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_status', __('Donation status', 'leyka'), array(__CLASS__, 'donation_status_metabox'), self::$post_type, 'side', 'high');
            add_meta_box(self::$post_type.'_emails_status', __('Emails status', 'leyka'), array(__CLASS__, 'emails_status_metabox'), self::$post_type, 'normal', 'high');
            add_meta_box(self::$post_type.'_gateway_response', __('Gateway responses text', 'leyka'), array(__CLASS__, 'gateway_response_metabox'), self::$post_type, 'normal', 'low');

        }

	}

    public static function new_donation_data_metabox() {

        $campaign_id = empty($_GET['campaign_id']) ? '' : (int)$_GET['campaign_id'];
        $campaign = new Leyka_Campaign($campaign_id);?>

	<fieldset class="leyka-set campaign">
		<legend><?php _e('Campaign Data', 'leyka');?></legend>

        <div class="leyka-ddata-string">
            <label for="campaign-select"><?php echo _x('Campaign', 'In subjective case', 'leyka');?>:</label>
			<div class="leyka-ddata-field">

				<input id="campaign-select" type="text" value="<?php echo $campaign_id ? $campaign->title : '';?>" data-nonce="<?php echo wp_create_nonce('leyka_get_campaigns_list_nonce');?>" placeholder="<?php _e('Select a campaign', 'leyka');?>">
				<input id="campaign-id" type="hidden" name="campaign-id" value="<?php echo $campaign_id;?>">
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

        <div class="leyka-ddata-string">
            <label for="donation-amount"><?php _e('Amount', 'leyka');?>:</label>

			<div class="leyka-ddata-field">
				<input type="text" id="donation-amount" name="donation-amount" placeholder="<?php _e('Enter the donation amount', 'leyka');?>" value=""> <?php echo leyka_options()->opt_safe('currency_rur_label');?><br>
				<small class="field-help howto">
                    <?php _e('Amount may be negative for correctional donations.', 'leyka');?>
                </small>
				<div id="donation_amount-error" class="field-error"></div>
			</div>
        </div>
        <div class="leyka-ddata-string">
            <label for="donation-amount"><?php _e('Total amount', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
                <input type="text" id="donation-amount-total" name="donation-amount-total" placeholder="<?php _e('Enter the donation total amount', 'leyka');?>" value=""> <?php echo leyka_options()->opt_safe('currency_rur_label');?><br>
                <small class="field-help howto">
                    <?php
                    /** @todo Add a checkbox here (unckecked by default) to calculate total amount
                     * based on current commission setting.
                     */
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

            <input type="text" id="custom-payment-info" name="custom-payment-info" placeholder="<?php _e('Enter some info about source of a new donation', 'leyka');?>" style="display: none;" value="">

            <div id="donation_pm-error" class="field-error"></div>
			</div>

        </div>

        <input type="hidden" id="payment-type-hidden" name="payment-type" value="correction">

        <?php /** @todo Maybe, display divs only for "active" gateways? I.e. those with currently active PMs. */
        foreach(leyka_get_gateways() as $gateway) {?>
            <div id="<?php echo $gateway->id;?>-fields" class="leyka-ddata-string gateway-fields" style="display: none;">
                <?php $gateway->display_donation_specific_data_fields();?>
            </div>
        <?php }?>

        <div class="leyka-ddata-string">
            <label for="donation-date-view"><?php _e('Donation date', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
                <input type="text" id="donation-date-view" value="<?php echo date(get_option('date_format'));?>" />
                <input type="hidden" id="donation-date" name="donation_date" value="<?php echo date('Y-m-d');?>" />
            </div>
        </div>

	</fieldset>
    <?php }

    public static function donation_data_metabox() {

        $donation_id = leyka_get_donations_storage_type() === 'post' ?
            (empty($_GET['post']) ? false : absint($_GET['post'])) :
            (empty($_GET['donation']) ? false : absint($_GET['donation']));

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);?>

	<fieldset class="leyka-set campaign">
		<legend><?php _e('Campaign Data', 'leyka');?></legend>

        <div class="leyka-ddata-string">
			<label><?php echo _x('Campaign', 'In subjective case', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
			<?php if($campaign->id && $campaign->status == 'publish') {?>
			<span class="text-line">
            <span class="campaign-name"><?php echo htmlentities($campaign->title, ENT_QUOTES, 'UTF-8');?></span> <span class="campaign-actions"><a href="<?php echo admin_url('/post.php?action=edit&post='.$campaign->id);?>"><?php _e('Edit campaign', 'leyka');?></a> <a href="<?php echo $campaign->url;?>" target="_blank" rel="noopener noreferrer"><?php _e('Preview campaign', 'leyka');?></a></span></span>

			<?php } else {
				echo '<span class="text-line">'.__('the campaign has been removed or drafted', 'leyka').'</span>';
			}?>
			</div>
		</div>

		<div class="leyka-ddata-string">
			<label><?php _e('Donation purpose', 'leyka');?>:</label>
			<div class="leyka-ddata-field"><span class="text-line">
			<?php echo $campaign->id ? $campaign->payment_title : $donation->title;?>
			</span></div>
        </div>

		<div class="set-action">
            <div id="campaign-select-trigger" class="button"><?php _e('Connect this donation to another campaign', 'leyka');?></div>

            <div id="campaign-select-fields" style="display: none;">
                <label for="campaign-select"></label>

                <input id="campaign-select"
                       type="text"
                       data-nonce="<?php echo wp_create_nonce('leyka_get_campaigns_list_nonce');?>"
                       placeholder="<?php _e('Select a campaign', 'leyka');?>"
                       value="<?php echo htmlentities($campaign->title, ENT_QUOTES, 'UTF-8');?>">
                <input id="campaign-id" type="hidden" name="campaign-id" value="<?php echo $campaign->id;?>">

                <div id="cancel-campaign-select" class="button"><?php _e('Cancel', 'leyka');?></div>
            </div>
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
            <label for="donation-amount"><?php _e('Total amount', 'leyka');?>:</label>

            <div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>
                <input type="text" id="donation-amount-total" name="donation-amount-total" placeholder="<?php _e('Enter the donation total amount', 'leyka');?>" value="<?php echo $donation->amount_total;?>"> <?php echo leyka_options()->opt_safe('currency_rur_label');?><br>
                <small class="field-help howto">
                    <?php
                    /** @todo Add a checkbox here (unckecked by default) to calculate total amount
                     * based on current commission setting.
                     */
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
                    <option value="custom" <?php echo $donation->gw_id == '' && $donation->pm_id ? 'selected="selected"' : '';?>><?php _e('Custom payment info', 'leyka');?></option>
                </select>

                <input type="text" id="custom-payment-info" name="custom-payment-info" placeholder="<?php _e('Enter the new donation source info', 'leyka');?>" <?php echo $donation->gw_id == '' && $donation->pm_id ? '' : 'style="display: none;"';?> value="<?php echo $donation->gw_id == '' ? $donation->pm_id : '';?>" />

            <?php } else {?>

                <span class="fake-input">
                <?php $pm = leyka_get_pm_by_id($donation->payment_method);
                $gateway = leyka_get_gateway_by_id($donation->gateway_id);

                echo ($pm ? $pm->label : __('Unknown payment method', 'leyka'))
                    .' ('.($gateway ? $gateway->label : __('unknown gateway', 'leyka')).')';?>
			    </span>
            <?php }?>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <?php $gateway = leyka_get_gateway_by_id($donation->gateway_id);
            if($gateway) {
                $gateway->display_donation_specific_data_fields($donation);
            }?>
        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Payment type', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
                <span class="fake-input"><?php echo $donation->type_label;?></span>
            </div>
        </div>

        <?php if($donation->init_recurring_donation_id) {?>
        <div class="leyka-ddata-string">
            <label><?php _e('Initial donation of the recurring subscription', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <a href="<?php echo admin_url('post.php?post='.$donation->init_recurring_donation_id.'&action=edit');?>">#<?php echo $donation->init_recurring_donation_id;?></a>
            </div>
        </div>
        <?php }?>

        <div class="leyka-ddata-string">
            <label for="donation-date-view"><?php _e('Donation date', 'leyka');?>:</label>
			<div class="leyka-ddata-field">
            <?php if($donation->type === 'correction') {?>

                <input type="text" id="donation-date-view" value="<?php echo date(get_option('date_format'), $donation->date_timestamp);?>">
                <input type="hidden" id="donation-date" name="donation_date" value="<?php echo date('Y-m-d', $donation->date_timestamp);?>">
            <?php } else {?>
                <span class="fake-input"><?php echo $donation->date;?></span>
            <?php }?>
            </div>
        </div>

        <div class="leyka-ddata-string">
            <label><?php _e('Donor subscription status', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <span class="fake-input">
                <?php $subscription_status = __('None', 'leyka');
                if($donation->donor_subscribed === true) {
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
	</fieldset>

	<?php }

    public static function donation_status_metabox() {

        $donation_id = leyka_get_donations_storage_type() === 'post' ?
            (empty($_GET['post']) ? false : absint($_GET['post'])) :
            (empty($_GET['donation']) ? false : absint($_GET['donation']));

        $donation = $donation_id ? Leyka_Donations::get_instance()->get_donation($donation_id) : false;

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
                        array('accesskey' => 'p', 'data-is-new-donation' => $is_adding_page ? 1 : 0)
                    );?>
				</div>

            <?php }?>

        </div>

        <div class="leyka-status-section log">
            <?php $status_log = $donation ? $donation->status_log : array();
            if($status_log) {?>

                <?php $last_status = end($status_log);
                echo str_replace(
                    array('%status', '%date'),
                    array('<i>'.self::get_status_labels($last_status['status']).'</i>', '<time>'.date(get_option('date_format').', H:i', $last_status['date']).'</time>'),
                    '<div class="leyka-ddata-string last-log">'.__('Last status change: to&nbsp;%status (at&nbsp;%date)', 'leyka').'</div>'
                );?>

                <div id="donation-status-log-toggle"><?php _e('Show/hide full status history', 'leyka');?></div>
                <ul id="donation-status-log" style="display: none;">
                    <?php for($i = 0; $i < count($status_log); $i++) {?>

                    <li>
                        <?php echo str_replace(
                            array('%status', '%date'),
                            array(
                                '<i>'.self::get_status_labels($status_log[$i]['status']).'</i>','<time>'.date(get_option('date_format').', '.get_option('time_format'),
                                    $status_log[$i]['date']).'</time>'
                            ),
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

        $donation_id = leyka_get_donations_storage_type() === 'post' ?
            (empty($_GET['post']) ? false : absint($_GET['post'])) :
            (empty($_GET['donation']) ? false : absint($_GET['donation']));

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);

        $donor_thanks_date = $donation->get_meta('donor_email_date');
        $manager_notification_date = $donation->get_meta('managers_emails_date');

		if($donor_thanks_date) {?>
			<div class="leyka-ddata-string donor has-thanks">
                <?php echo sprintf(
                    __('Grateful email to the donor has been sent (at %s)', 'leyka'),
                    '<time>'.date(get_option('date_format').', H:i</time>', $donor_thanks_date).'</time>'
                );?>
            </div>
		<?php } else {?>
			<div class="leyka-ddata-string donor no-thanks" data-donation-id="<?php echo $donation->id;?>">

				<?php echo sprintf(
                    __("Grateful email hasn't been sent %s", 'leyka'),
                    "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>"
                );
				wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true);?>

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

        $donation_id = leyka_get_donations_storage_type() === 'post' ?
            (empty($_GET['post']) ? false : absint($_GET['post'])) :
            (empty($_GET['donation']) ? false : absint($_GET['donation']));

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

	/**
     * Donations table columns.
     * @param array $columns An array of id => name pairs.
     * @return array
     */
	public function manage_columns_names($columns) {

		$unsort = $columns;
		$columns = array();

		if(isset($unsort['cb'])){

			$columns['cb'] = $unsort['cb'];
			unset($unsort['cb']);

		}

		$columns['ID'] = 'ID';

		if(isset($unsort['title'])) {

			$columns['title'] = _x('Campaign', 'In subjective case', 'leyka');
			unset($unsort['title']);

		}

        unset($unsort['date']);

		$columns['donor'] = __('Donor', 'leyka');
        if(leyka_options()->opt_template('show_donation_comment_field')) {
            $columns['donor_comment'] = __("Donor's comment", 'leyka');
        }

		$columns['amount'] = __('Amount', 'leyka');
        if(leyka_options()->opt('admin_donations_list_display') == 'separate-column') {
            $columns['amount_total'] = __('Total amount', 'leyka');
        }

		$columns['method'] = __('Method', 'leyka');
        $columns['donation_date'] = __('Donation date', 'leyka');
		$columns['status'] = __('Status', 'leyka');
		$columns['type'] = __('Payment type', 'leyka');
		$columns['emails'] = __('Letter', 'leyka');
		$columns['donor_subscribed'] = __('Donor subscription', 'leyka');

		if($unsort) {
			$columns = array_merge($columns, $unsort);
        }

		return apply_filters('leyka_admin_donations_columns_names', $columns);

	}

	public function manage_columns_content($column_name, $donation_id) {

		$donation = new Leyka_Donation($donation_id);

        switch($column_name) {
            case 'ID': echo $donation_id; break;
            case 'amount':
                if(leyka_options()->opt('admin_donations_list_display') == 'amount-column') {
                    $amount = $donation->amount == $donation->amount_total ?
                        $donation->amount :
                        $donation->amount
                        .'<span class="amount-total"> / '.$donation->amount_total.'</span>';
                } else {
                    $amount = $donation->amount;
                }

				echo '<span class="'.apply_filters('leyka_admin_donation_amount_column_css', ($donation->sum < 0 ? 'amount-negative' : 'amount')).'">'.
                    apply_filters('leyka_admin_donation_amount_column_content', $amount.'&nbsp;'.$donation->currency_label, $donation).
                '</span>';
                break;
            case 'amount_total':
                echo '<span class="'.apply_filters('leyka_admin_donation_amount_total_column_css', $donation->amount_total < 0 ? 'amount-negative' : 'amount').'">'.
                    apply_filters('leyka_admin_donation_amount_total_column_content', $donation->amount_total.'&nbsp;'.$donation->currency_label, $donation).
                    '</span>';
                break;
            case 'donor':
                echo apply_filters('leyka_admin_donation_donor_name_column_content', $donation->donor_name, $donation);
                break;
            case 'donor_comment':
                echo apply_filters('leyka_admin_donation_donor_comment_column_content', $donation->donor_comment, $donation);
                break;
            case 'method':
                $gateway_label = $donation->gateway_id ? $donation->gateway_label : __('Custom payment info', 'leyka');
                $pm_label = $donation->gateway_id ? $donation->pm_label : $donation->pm;
                echo "<b>{$donation->payment_type_label}:</b> $pm_label <small>/ $gateway_label</small>";
                break;
            case 'donation_date':
                echo $donation->date_time;
                break;
            case 'status':
                echo '<i class="'.esc_attr($donation->status).'">'
                    .$this->get_status_labels($donation->status).'</i>&nbsp;<span class="dashicons dashicons-editor-help has-tooltip" title="'.$this->get_status_descriptions($donation->status).'"></span>';
                break;
            case 'type':
                echo '<i class="'.esc_attr($donation->payment_type).'">'.$donation->payment_type_label.'</i>';
                break;
            case 'emails':
				if($donation->donor_email_date){
					echo str_replace(
						'%s',
						'<time>'.date(get_option('date_format').', H:i</time>', $donation->donor_email_date).'</time>',
						__('Sent at %s', 'leyka')
					);
				} else {?>

                <div class="leyka-no-donor-thanks" data-donation-id="<?php echo $donation_id;?>">
                    <?php echo sprintf(
                        __("Not sent %s", 'leyka'),
                        "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>"
                    );?>
                    <?php wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true); ?>
                </div>

				<?php }

                break;
            case 'donor_subscribed':
                if($donation->donor_subscribed === true) {?>

                <div class="donor-subscription-status total"><?php _e('Full subscription', 'leyka');?></div>

                <?php } else if($donation->donor_subscribed > 0) {?>

                <div class="donor-subscription-status on-campaign">
                    <?php printf(__('On <a href="%s">campaign</a> news', 'leyka'), admin_url('post.php?post='.$donation->campaign_id.'&action=edit'));?>
                </div>

                <?php } else {?>

                <div class="donor-subscription-status none"><?php _e('None', 'leyka');?></div>

                <?php }

                break;
            default:
        }

	}

    public function manage_sortable_columns($sortable_columns) {

        $sortable_columns['ID'] = 'ID';
        $sortable_columns['donation_date'] = 'donation_date';
        $sortable_columns['donor'] = 'donor_name';
        $sortable_columns['type'] = 'payment_type';
        $sortable_columns['method'] = 'leyka_payment_method';
//        $sortable_columns['status'] = 'donation_status'; // Apparently, WP can't sort posts by status

        return $sortable_columns;
    }

    public function do_column_sorting($vars) {

        if(empty($vars['orderby'])) {
            return $vars;
        }

        if($vars['orderby'] == 'donation_date') {
            $vars = array_merge($vars, array('orderby' => 'date',));
        } elseif($vars['orderby'] == 'donor_name') {
            $vars = array_merge($vars, array('meta_key' => 'leyka_donor_name', 'orderby' => 'meta_value',));
        } elseif($vars['orderby'] == 'payment_type') {
            $vars = array_merge($vars, array('meta_key' => 'leyka_payment_type', 'orderby' => 'meta_value',));
        }

        return $vars;
    }

    /** Donation data editing.
     * @param $donation_id int
     * @return int|false Edited donation ID or false (if editing is failed or impossible).
     * @deprecated Now all Donation data handling is on the Leyka_Admin
     */
    public function save_donation_data($donation_id) {

        // Maybe donation is being inserted trough API:
        if(empty($_POST['post_type']) || $_POST['post_type'] != Leyka_Donation_Management::$post_type) {
            return false;
        }

        if(
            empty($_POST['_donation_edit_nonce']) ||
            !wp_verify_nonce($_POST['_donation_edit_nonce'], 'donation_status_metabox')
        ) {
            return $donation_id;
        }

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $donation_id;
        }

        if( !current_user_can('edit_donation', $donation_id) ) {
            return $donation_id;
        }

        remove_action('save_post_'.self::$post_type, array($this, 'save_donation_data'));

        $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        $donation->donor_user_id = 0; // Or current user (admin) will become donation post_author

        if($donation->status != $_POST['donation_status']) {
            $donation->status = $_POST['donation_status'];
        }

        if(isset($_POST['donation-amount'])) {

            $_POST['donation-amount'] = round((float)str_replace(',', '.', $_POST['donation-amount']), 2);
            if((float)$donation->amount != $_POST['donation-amount']) {
                $donation->amount = $_POST['donation-amount'];
            }

        }

        if(isset($_POST['donation-amount-total'])) {

            $_POST['donation-amount-total'] = round((float)str_replace(
                array(',', ' '), array('.', ''),
                $_POST['donation-amount-total']
            ), 2);

            if((float)$donation->amount_total != $_POST['donation-amount-total']) {

                if($_POST['donation-amount-total'] <= 0.0 && $donation->amount > 0.0) {
                    $_POST['donation-amount-total'] = $donation->amount;
                }

                $old_amount = $donation->amount_total ? $donation->amount_total : $donation->amount;
                $donation->amount_total = $_POST['donation-amount-total'];

                // If we're adding a correctional donation, $donation->campaign_id == 0:
                if($donation->campaign_id && $donation->status == 'funded') {
                    $campaign->update_total_funded_amount($donation, 'update_sum', $old_amount);
                }

            }

        }

        if( !$donation->currency ) {
            $donation->currency = 'rur';
        }

        if(isset($_POST['campaign-id']) && $donation->campaign_id != (int)$_POST['campaign-id']) {

            // If we're adding a correctional donation, $donation->campaign_id == 0:
            if($donation->campaign_id && $donation->status == 'funded') {
                $campaign->update_total_funded_amount($donation, 'remove'); // Old campaign
            }

            $donation->campaign_id = (int)$_POST['campaign-id'];
            $campaign = new Leyka_Campaign($donation->campaign_id); // New campaign

            if($donation->status == 'funded') {
                $campaign->update_total_funded_amount($donation);
            }

        }

        // It's a new correction donation, set a title from it's campaign:
        $donation_title = $campaign->payment_title ?
            $campaign->payment_title :
            ($campaign->title ? $campaign->title : sprintf(__('Donation #%s', 'leyka'), $donation_id));

        if($donation->title !== $donation_title) {
            $donation->title = $donation_title;
        }

        if(
            isset($_POST['donor-name'])
            && $donation->donor_name != $_POST['donor-name']
            && leyka_validate_donor_name($_POST['donor-name'])
        ) {
            $donation->donor_name = sanitize_text_field($_POST['donor-name']);
        }

        if(
            isset($_POST['donor-email'])
            && $donation->donor_email !== $_POST['donor-email']
            && filter_var($_POST['donor-email'], FILTER_VALIDATE_EMAIL)
        ) {
            $donation->donor_email = sanitize_email($_POST['donor-email']);
        }

        if(isset($_POST['donor-comment']) && $donation->donor_comment != $_POST['donor-comment']) {
            $donation->donor_comment = sanitize_textarea_field($_POST['donor-comment']);
        }

        if(
            isset($_POST['donation-pm']) &&
            ($donation->pm != $_POST['donation-pm'] || $_POST['donation-pm'] == 'custom')
        ) {

            if($_POST['donation-pm'] === 'custom') {

                $donation->gateway_id = '';
                if($donation->pm_id !== $_POST['custom-payment-info']) {
                    $donation->pm_id = $_POST['custom-payment-info'];
                }

            } else {

                $parts = explode('-', $_POST['donation-pm']);
                $donation->gateway_id = $parts[0];
                $donation->pm = $parts[1];

            }
        }

        if(isset($_POST['donation_date']) && $donation->date_timestamp != strtotime($_POST['donation_date'])) {
            $donation->date = $_POST['donation_date'];
        }

        if(isset($_POST['payment-type']) && $donation->payment_type != $_POST['payment-type']) {
            $donation->payment_type = $_POST['payment-type'];
        }

        // Add donor ID for correction-typed donation:
        if(
            leyka_options()->opt('donor_management_available')
            && $donation->status === 'funded'
            && !$donation->donor_user_id
            && $donation->donor_email
        ) {

            try {

            	$donor = new Leyka_Donor($donation->donor_email);

                $donation->donor_user_id = $donor->id;
                Leyka_Donor::calculate_donor_metadata($donor);

            } catch(Exception $e) {
            	// ...
            }

        }

        do_action("leyka_{$donation->gateway_id}_save_donation_data", $donation);

        add_action('save_post_'.self::$post_type, array($this, 'save_donation_data'));

        return true;

    }

	/** Helpers **/

	public static function get_status_labels($status = false) {

        $labels = leyka()->get_donation_statuses();

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
        return leyka_get_donations_storage_type() === 'post' ?
            get_delete_post_link($donation->id) :
            admin_url('admin.php?page=leyka_donations&action=delete&donation='.$donation->id.'&_wpnonce='.wp_create_nonce('leyka_delete_donation'));
    }

    public static function get_donation_edit_link(Leyka_Donation_Base $donation) {
        return leyka_get_donations_storage_type() === 'post' ?
            admin_url('post.php?post='.$donation->id.'&action=edit') :
            admin_url('?page=leyka_donation_info&donation='.$donation->id);
    }

}

function leyka_donation_management() {
    return Leyka_Donation_Management::get_instance();
}

function leyka_cancel_recurrents_action() {

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_recurrent_cancel') || empty($_POST['donation_id'])) {
        die('-1');
    }

    $_POST['donation_id'] = (int)$_POST['donation_id'];

    $donation = new Leyka_Donation($_POST['donation_id']);
    do_action('leyka_cancel_recurrents-'.$donation->gateway_id, $donation);

}
//add_action('wp_ajax_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');
//add_action('wp_ajax_nopriv_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');