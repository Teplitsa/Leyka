<?php if( !defined('WPINC') ) die;

/** Default donation class - WP_Post-based */
class Leyka_Donation_Post extends Leyka_Donation_Base {

    public static function add(array $params = []) {

        $params = self::_handle_new_donation_params($params); // New Donation params pre-handling

        if(is_wp_error($params)) {
            return $params;
        }

        $params['currency_id'] = empty($params['currency_id']) ?
            (empty($params['currency']) ? $params['currency'] : false) : $params['currency_id'];
        $params['currency_id'] = $params['currency_id'] && leyka_get_currencies_full_info($params['currency_id']) ?
            $params['currency_id'] : leyka_get_country_currency();

        $donation_meta_fields = [
            'leyka_donation_amount' => $params['amount'],
            'leyka_donation_currency' => $params['currency_id'],
            'leyka_donation_main_currency_id' => $params['main_currency_id'],
            'leyka_donation_main_currency_rate' => $params['main_currency_rate'],
            'leyka_donation_main_currency_amount' => $params['main_currency_amount'],
            'leyka_donation_main_currency_amount_total' => $params['main_currency_amount_total'],
            'leyka_payment_type' => $params['payment_type'],
            'leyka_donor_name' => $params['donor_name'],
            'leyka_donor_email' => $params['donor_email'],
            'leyka_gateway' => $params['gateway_id'],
            'leyka_payment_method' => $params['pm_id'],
            'leyka_campaign_id' => $params['campaign_id'],
//            '_leyka_donor_email_date' => 0, /** @todo Check if this lines are needed at all */
//            '_leyka_managers_emails_date' => 0,
            '_status_log' => [['date' => current_time('timestamp'), 'status' => $params['status']]],
        ];

        if($params['amount_total'] && $params['amount_total'] != $params['amount']) {
            $donation_meta_fields['leyka_donation_amount_total'] = $params['amount_total'];
        }

        if($params['main_currency_amount_total'] && $params['main_currency_amount_total'] != $params['main_currency_amount']) {
            $donation_meta_fields['leyka_donation_main_currency_amount_total'] = $params['main_currency_amount_total'];
        }

        if($params['additional_fields']) {
            $donation_meta_fields['leyka_additional_fields'] = $params['additional_fields'];
        }

        if($params['donor_comment']) {
            $donation_meta_fields['leyka_donor_comment'] = $params['donor_comment'];
        }

        if($params['payment_type'] === 'rebill' && !$params['init_recurring_donation']) { // Init recurring donations only

            $donation_meta_fields['_rebilling_is_active'] =
                !empty($params['rebilling_is_active']) ||
                !empty($params['rebilling_on']) ||
                !empty($params['recurring_active']) ||
                !empty($params['recurring_is_active']) ||
                !empty($params['recurring_on']);

            if($params['recurring_cancelled']) {

                $donation_meta_fields['_rebilling_is_active'] = 0;
                $donation_meta_fields['leyka_recurrents_cancelled'] = 1;

                $donation_meta_fields['leyka_recurrents_cancel_date'] = $params['recurring_cancel_date'] ?
                    : current_time('timestamp');

            }

            if($params['recurring_cancel_date'] && empty($donation_meta_fields['leyka_recurrents_cancel_date'])) {
                $donation_meta_fields['leyka_recurrents_cancel_date'] = $params['recurring_cancel_date'];
            }

            if($params['recurring_cancel_requested']) {
                $donation_meta_fields['cancel_recurring_requested'] = $params['recurring_cancel_requested'];
            }

            if( !empty($params['recurring_cancel_reason']) ) {
                $donation_meta_fields['leyka_recurring_cancel_reason'] = $params['recurring_cancel_reason'];
            }

            $donation_meta_fields['leyka_recurring_subscription_status'] = 'non-active';

            $donation_meta_fields['leyka_recurring_subscription_error_id'] = false;


        }

        if($params['donor_subscribed']) {
            $donation_meta_fields['donor_subscribed'] = $params['donor_subscribed'];
        }

        if($params['ga_client_id']) {
            $donation_meta_fields['leyka_ga_client_id'] = $params['ga_client_id'];
        }

        remove_all_actions('save_post_'.Leyka_Donation_Management::$post_type);

        $donation_id = wp_insert_post([
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => $params['status'],
            'post_title' => wp_strip_all_tags($params['payment_title']),
            'post_date' => $params['date_created'] ? : '',
            'post_name' => uniqid('donation-', true), // For fast WP_Post creation when DB already has lots of donations
            'post_parent' => $params['init_recurring_donation'],
            'post_author' => $params['donor_user_id'],
            'meta_input' => $donation_meta_fields,
        ], true);

        if(is_wp_error($donation_id)) {
            return $donation_id;
        }

        if($params['gateway_id']) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $donation_id, $params);
        }

        if(isset($donation_meta_fields['_rebilling_is_active'])) {
            do_action('leyka_donation_recurring_activity_changed', $donation_id, $donation_meta_fields['_rebilling_is_active']);
        }

        $donation_meta_fields = apply_filters(
            "leyka_{$params['gateway_id']}_new_donation_specific_data",
            [],
            $donation_id,
            $params
        );
        $donation_meta_fields = apply_filters('leyka_new_donation_specific_data', $donation_meta_fields, $donation_id, $params);

        foreach($donation_meta_fields as $key => $value) {
            update_post_meta($donation_id, $key, $value);
        }

        return $donation_id;

    }

    /**
     * @deprecated Use self::get_init_recurring_donation($donation) instead.
     * @param mixed $donation
     * @return Leyka_Donation_Base|false A Donation object found, or false if param is wrong or nothing found.
     */
    public static function get_init_recurrent_donation($donation) {
        return self::get_init_recurring_donation($donation);
    }

	public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && absint($donation)) {

            $donation = absint($donation);

            global $wpdb;
            $this->_main_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $donation));

            if( !$this->_main_data) {
                throw new Exception(
                    sprintf(__('No post found by ID while constructing a donation ("%s" given)', 'leyka'), $donation)
                );
            } else if($this->_main_data->post_type !== Leyka_Donation_Management::$post_type) {
                throw new Exception(
                    sprintf(__('Wrong post type for donation ("%s" given)', 'leyka'), $this->_main_data->post_type)
                );
            }

            $this->_id = $donation;

        } else if(is_a($donation, 'WP_Post')) {

            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                throw new Exception(sprintf(__('Wrong post type for donation ("%s" given)', 'leyka'), $donation->post_type));
            }

            $this->_id = $donation->ID;
            $this->_main_data = $donation;

        } else if(is_a($donation, 'Leyka_Donation_Post')) {

            $this->_id = $donation->_id;
            $this->_main_data = $donation->_main_data;
            $this->_donation_meta = $donation->_donation_meta;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            $this->_id = $donation->id;
        } else if( // Posts table row object
            is_object($donation)
            && !empty($donation->ID)
            && !empty($donation->post_type)
            && $donation->post_type === Leyka_Donation_Management::$post_type
        ) {

            $this->_id = absint($donation->ID);

            if(leyka_options()->opt('object_caching_compatibility_mode')) {

                global $wpdb;

                $this->_main_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $donation->ID
                ));

            } else {
                $this->_main_data = new WP_Post($donation);
            }

        } else {
            throw new Exception( sprintf(__('Unknown donation given: %s', 'leyka'), print_r($donation, 1)) );
        }

        if( !is_a($this->_main_data, 'WP_Post') ) {
            $this->_main_data = new WP_Post($this->_main_data);
        }

        if( !$this->_donation_meta ) {

            $meta = get_post_meta($this->_id, '', true);

            if( !empty($meta['leyka_campaign_id']) ) {

                // Don't use Leyka_Campaign here to avoid loop dependency:
                $campaign = get_post(absint($meta['leyka_campaign_id'][0]));
                $payment_title = '';

                if($campaign) {

                    $payment_title = get_post_meta($campaign->ID, 'payment_title', true);
                    if( !$payment_title ) {
                        $payment_title = $campaign->post_title;
                    }

                }

            }

            $donation_amount = empty($meta['leyka_donation_amount']) ? 0.0 : (float)$meta['leyka_donation_amount'][0];
            $donation_amount_total = empty($meta['leyka_donation_amount_total']) ?
                $donation_amount : (float)$meta['leyka_donation_amount_total'][0];
            $donation_currency = empty($meta['leyka_donation_currency']) ?
                leyka_options()->opt('currency_main') : $meta['leyka_donation_currency'][0];
            $main_currency = leyka_get_main_currency();
            $old_donation_main_currency = empty($meta['leyka_donation_main_currency_id']) ?
                $main_currency : (string)$meta['leyka_donation_main_currency_id'][0];
            $donation_main_currency_amount = $old_donation_main_currency === $main_currency
                && !empty($meta['leyka_donation_main_currency_amount']) ?
                round((float)$meta['leyka_donation_main_currency_amount'][0],2) :
                round(leyka_currency_convert($donation_amount, $donation_currency), 2);
            $donation_main_currency_amount_total = $old_donation_main_currency === $main_currency
            && !empty($meta['leyka_donation_main_currency_amount_total']) ?
                round((float)$meta['leyka_donation_main_currency_amount_total'][0],2) :
                round(leyka_currency_convert($donation_amount_total, $donation_currency),2);
            $donation_main_currency_rate = $old_donation_main_currency === $main_currency
                && !empty($meta['leyka_donation_main_currency_rate']) ?
                (float)$meta['leyka_donation_main_currency_rate'][0] :
                leyka_get_currency_rate($donation_currency);

            $this->set_meta('leyka_donation_main_currency_id', $main_currency);
            $this->set_meta('leyka_donation_main_currency_amount', $donation_main_currency_amount);
            $this->set_meta('leyka_donation_main_currency_amount_total', $donation_main_currency_amount_total);
            $this->set_meta('leyka_donation_main_currency_rate', $donation_main_currency_rate);

            do_action('leyka_donation_constructor_meta', $meta, $this->_id);

            $this->_donation_meta = apply_filters('leyka_donation_constructor_meta', [
                'payment_title' => empty($payment_title) ? $this->_main_data->post_title : $payment_title,
                'leyka_payment_type' => empty($meta['leyka_payment_type']) ? 'single' : $meta['leyka_payment_type'][0],
                'leyka_payment_method' => empty($meta['leyka_payment_method']) ? '' : $meta['leyka_payment_method'][0],
                'leyka_gateway' => empty($meta['leyka_gateway']) ? '' : $meta['leyka_gateway'][0],
                'leyka_donation_currency' => empty($meta['leyka_donation_currency']) ?
                    leyka_options()->opt('currency_main') : $meta['leyka_donation_currency'][0],
                'leyka_donation_amount' => $donation_amount,
                'leyka_donation_amount_total' => $donation_amount_total,
                'leyka_donation_main_currency_id' => $main_currency,
                'leyka_donation_main_currency_amount' => $donation_main_currency_amount,
                'leyka_donation_main_currency_amount_total' => $donation_main_currency_amount_total,
                'leyka_donation_main_currency_rate' => $donation_main_currency_rate,
                'leyka_donor_name' => empty($meta['leyka_donor_name']) ? '' : $meta['leyka_donor_name'][0],
                'leyka_donor_email' => empty($meta['leyka_donor_email']) ? '' : $meta['leyka_donor_email'][0],
                'leyka_donor_comment' => empty($meta['leyka_donor_comment']) ? '' : $meta['leyka_donor_comment'][0],
                'leyka_additional_fields' => empty($meta['leyka_additional_fields']) ?
                    [] : maybe_unserialize($meta['leyka_additional_fields'][0]),
                'leyka_donor_subscribed' => empty($meta['leyka_donor_subscribed']) ?
                    false : $meta['leyka_donor_subscribed'][0],
                'leyka_donor_subscription_email' => empty($meta['leyka_donor_subscription_email']) ?
                    '' : $meta['leyka_donor_subscription_email'][0],
                'leyka_donor_email_date' => empty($meta['leyka_donor_email_date']) ?
                    '' : $meta['leyka_donor_email_date'][0],
                '_leyka_managers_emails_date' => empty($meta['_leyka_managers_emails_date']) ?
                    '' : $meta['_leyka_managers_emails_date'][0],
                'leyka_campaign_id' => empty($meta['leyka_campaign_id']) ? 0 : $meta['leyka_campaign_id'][0],
                'leyka_donor_account_error' => empty($meta['leyka_donor_account_error']) ?
                    '' : $meta['leyka_donor_account_error'][0],
                '_status_log' => empty($meta['_status_log']) ? '' : maybe_unserialize($meta['_status_log'][0]),
                'leyka_error_id' => $this->_main_data->post_status !== 'failed' || empty($meta['leyka_error_id']) ?
                    '' : $meta['leyka_error_id'][0],
                'leyka_gateway_response' => empty($meta['leyka_gateway_response']) ? '' : $meta['leyka_gateway_response'][0],

                'leyka_recurring_funded_rebills_number' => isset($meta['leyka_recurring_funded_rebills_number'][0]) ?
                    absint($meta['leyka_recurring_funded_rebills_number'][0]) : false,

                'leyka_recurrents_cancelled' => isset($meta['leyka_recurrents_cancelled']) ?
                    $meta['leyka_recurrents_cancelled'][0] : false,
                'leyka_recurrents_cancel_date' => isset($meta['leyka_recurrents_cancel_date']) ?
                    $meta['leyka_recurrents_cancel_date'][0] : false,

                // For active schemes of recurring donations:
                '_rebilling_is_active' => !empty($meta['_rebilling_is_active'][0]),
                'leyka_cancel_recurring_requested' => isset($meta['leyka_cancel_recurring_requested']) ?
                    $meta['leyka_cancel_recurring_requested'][0] : false,
                'leyka_recurring_cancel_reason' => isset($meta['leyka_recurring_cancel_reason']) ?
                    $meta['leyka_recurring_cancel_reason'][0] : '',

                'leyka_recurring_subscription_status' => isset($meta['leyka_recurring_subscription_status']) ?
                    $meta['leyka_recurring_subscription_status'][0] :  (!empty($meta['_rebilling_is_active'][0]) ? 'active' : 'non-active'),
                'leyka_recurring_subscription_error_id' => isset($meta['leyka_recurring_subscription_error_id']) ?
                    $meta['leyka_recurring_subscription_error_id'][0] : false,
                'leyka_next_recurring_date_timestamp'=> isset($meta['leyka_next_recurring_date_timestamp']) ?
                    $meta['leyka_next_recurring_date_timestamp'][0] :  false,

                // For web-analytics services:
                'leyka_ga_client_id' => empty($meta['leyka_ga_client_id'][0]) ? false : $meta['leyka_ga_client_id'][0],
            ], $this->_id);
        }

	}

    public function __get($field) {

        if( !$this->_id ) {
            return false;
        }

        $value = false;

        switch($field) {
            case 'id':
            case 'ID':
                $value = $this->_id;
                break;

            case 'campaign_id':
                $value = $this->_donation_meta['leyka_campaign_id'];
                break;
            case 'campaign':

                $campaign = new Leyka_Campaign($this->_donation_meta['leyka_campaign_id']);
                $value = $campaign->id ? $campaign : false;
                break;

            case 'campaign_title':

                $campaign = $this->campaign;
                $value = $campaign ? strip_tags($campaign->title) : strip_tags($this->payment_title);
                break;

            case 'title':
            case 'name':
                $value = $this->_main_data->post_title;
                break;

            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                $value = $this->_donation_meta['payment_title'] ? : ($this->campaign_id ? $this->campaign->payment_title : '');
                break;

            case 'status':
                $value = $this->_main_data->post_status;
                break;
            case 'status_label':
                $value = Leyka::get_donation_status_info($this->_main_data->post_status, 'label');
                break;

            case 'status_desc':
            case 'status_description':
                $value = Leyka::get_donation_status_info($this->_main_data->post_status, 'description');
                break;
            case 'status_desc_for_donor':
            case 'status_desc_for_donors':
            case 'status_description_for_donor':
            case 'status_description_for_donors':
                $value = Leyka::get_donation_status_info($this->_main_data->post_status, 'description_for_donors');
                break;

            case 'status_log':
                $value = $this->_donation_meta['_status_log'];
                break;

            case 'error_id':
            case 'payment_error_id':
            case 'gateway_error_id':
            case 'donation_error_id':

                $error_id = false;
                if($this->status === 'failed' && !empty($this->_donation_meta['leyka_error_id'])) {
                    $error_id = $this->_donation_meta['leyka_error_id'];
                }

                $value = apply_filters('leyka_'.($this->gateway_id ? : '').'_get_donation_error_id', $error_id, $this);
                break;

            case 'error':
            case 'error_details':

                $value = $this->error_id ?
                    Leyka_Donations_Errors::get_instance()->get_error_by_id($this->error_id, $this->gateway_id) : false;
                break;

            case 'date':
            case 'date_label':

                $date_format = get_option('date_format');
                $donation_timestamp = $this->date_timestamp;

                $value = apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $donation_timestamp),
                    $donation_timestamp, $date_format
                );
                break;

            case 'time':
            case 'time_label':

                $time_format = get_option('time_format');
                $donation_timestamp = $this->date_timestamp;

                $value = apply_filters(
                    'leyka_admin_donation_time',
                    date($time_format, $donation_timestamp),
                    $donation_timestamp, $time_format
                );
                break;

            case 'date_time':
            case 'date_time_label':

                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $donation_timestamp = $this->date_timestamp;

                $value = apply_filters(
                    'leyka_admin_donation_date_time',
                    date("$date_format, $time_format", $donation_timestamp),
                    $donation_timestamp, $date_format, $time_format
                );
                break;

            case 'date_timestamp':
                $value = strtotime($this->_main_data->post_date);
                break;

            case 'date_funded':
            case 'date_funded_label':
            case 'funded_date':
            case 'funded_date_label':

                $date_funded = $this->get_funded_date();
                $value = $date_funded ? date(get_option('date_format'), $date_funded) : 0;
                break;

            case 'date_funded_timestamp':
            case 'funded_date_timestamp':
                $value = $this->date_funded ? strtotime($this->date_funded) : false;
                break;

            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                $value = $this->_donation_meta['leyka_payment_method'];
                break;

            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                $value = empty($this->_donation_meta['leyka_gateway']) ? '' : $this->_donation_meta['leyka_gateway'];
                break;

            case 'pm_full_id':
                $value = $this->gateway_id && $this->pm_id ? $this->gateway_id.'-'.$this->pm_id : '';
                break;

            case 'gw_label':
            case 'gateway_label':

                if(empty($this->_donation_meta['leyka_gateway'])) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->gateway_id);
                $value = $gateway ? $gateway->label : __('Unknown gateway', 'leyka');
                break;

            case 'gateway_icon':

                if(empty($this->_donation_meta['leyka_gateway'])) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->gateway_id);
                $value = $gateway ? $gateway->icon : __('Unknown gateway', 'leyka');
                break;

            case 'pm_label':
            case 'payment_method_label':

                $pm = leyka_get_pm_by_id($this->pm_full_id, true);
                $value = $pm ? $pm->label : __('Unknown payment method', 'leyka');
                break;

            case 'payment_method_main_icon_url':

                $pm = leyka_get_pm_by_id($this->pm_full_id, true);
                $value = $pm ? $pm->main_icon_url : __('Unknown payment method', 'leyka');
                break;

            case 'payment_method_category_label':

                $pm = leyka_get_pm_by_id($this->pm_full_id, true);
                $value = $pm ? $pm->category_label : __('Unknown payment method', 'leyka');
                break;

            case 'payment_method_category_icon':

                $pm = leyka_get_pm_by_id($this->pm_full_id, true);
                $value = $pm ? $pm->category_icon : __('Unknown payment method', 'leyka');
                break;

            case 'currency':
            case 'currency_id':
            case 'currency_code':

                if($this->_donation_meta['leyka_donation_currency'] == 'rur') { // Update the old RUR currency ID

                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix.'postmeta',
                        ['meta_value' => 'rub'],
                        ['post_id' => $this->_id, 'meta_key' => 'leyka_donation_currency', 'meta_value' => 'rur',]
                    );

                    $this->_donation_meta['leyka_donation_currency'] = 'rub';

                }
                $value = mb_strtoupper($this->_donation_meta['leyka_donation_currency']);
                break;

            case 'main_currency':
            case 'main_currency_id':

                $value = mb_strtoupper($this->_donation_meta['leyka_donation_main_currency_id']);
                break;

            case 'main_currency_rate':

                $value = $this->_donation_meta['leyka_donation_main_currency_rate'];
                break;

            case 'currency_label':
                $value = leyka_get_currency_label($this->currency_id);
                break;

            case 'sum':
            case 'amount':

                $value = empty($this->_donation_meta['leyka_donation_amount']) ?
                    0.0 : $this->_donation_meta['leyka_donation_amount'];
                break;

            case 'sum_formatted':
            case 'amount_formatted':
                $value = leyka_format_amount(round($this->amount, 2));
                break;

            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':

                $value = empty($this->_donation_meta['leyka_donation_amount_total']) ?
                    $this->amount : $this->_donation_meta['leyka_donation_amount_total'];
                break;

            case 'main_currency_total_amount':
            case 'main_currency_amount_total':

                if(empty($this->_donation_meta['leyka_donation_main_currency_amount_total'])) {
                    $value = $this->main_currency_amount;
                } else if(
                    leyka_get_main_currency(true) === $this->currency_id
                    && $this->amount_total !== $this->_donation_meta['leyka_donation_main_currency_amount_total']
                ) {

                    $this->main_currency_amount_total = $this->amount_total;
                    $value = $this->amount_total;

                } else {
                    $value = $this->_donation_meta['leyka_donation_main_currency_amount_total'];
                }
                break;

            case 'total_sum_formatted':
            case 'total_amount_formatted':
            case 'sum_total_formatted':
            case 'amount_total_formatted':
                $value = leyka_format_amount(round($this->amount_total, 2));
                break;

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':

                if(
                    leyka_get_main_currency(true) === $this->currency_id
                    && $this->amount !== $this->_donation_meta['leyka_donation_main_currency_amount']
                ) {

                    $this->main_currency_amount = $this->amount;
                    $value = $this->amount;

                } else {
                    $value = $this->_donation_meta['leyka_donation_main_currency_amount'];
                }
                break;

            case 'donor_name':
                $value = stripslashes($this->_donation_meta['leyka_donor_name']);
                break;
            case 'donor_email':
                $value = $this->_donation_meta['leyka_donor_email'];
                break;
            case 'donor_phone':
                $value = leyka_get_donor_phone($this->_id);
                break;
            case 'donor_comment':

                $value = empty($this->_donation_meta['leyka_donor_comment']) ?
                    '' : $this->_donation_meta['leyka_donor_comment'];
                break;

            case 'additional_fields':
            case 'donor_additional_fields':
            case 'donation_additional_fields':

                $value = empty($this->_donation_meta['leyka_additional_fields']) ?
                    [] : $this->_donation_meta['leyka_additional_fields'];
                break;

            case 'donor_email_date':
                $value = $this->_donation_meta['leyka_donor_email_date'];
                break;
            case 'managers_emails_date':
                $value = $this->_donation_meta['_leyka_managers_emails_date'];
                break;

            case 'is_subscribed':
            case 'donor_subscribed':
                $value = $this->_donation_meta['leyka_donor_subscribed'];
                break;

            case 'subscription_email':
            case 'donor_subscription_email':

                $value = $this->_donation_meta['leyka_donor_subscription_email'] ? :
                    ($this->_donation_meta['leyka_donor_email'] ? : '');
                break;

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':
                $value = isset($this->_main_data->post_author) ? (int)$this->_main_data->post_author : false;
                break;

            case 'donor_user_error':
            case 'donor_account_error':

                $donor_account_error = isset($this->_donation_meta['leyka_donor_account_error']) ?
                    maybe_unserialize($this->_donation_meta['leyka_donor_account_error']) : false;
                $value = $donor_account_error && is_wp_error($donor_account_error) ? $donor_account_error : false;
                break;

            case 'gateway_response':
                $value =  maybe_unserialize($this->_donation_meta['leyka_gateway_response']);
                break;
            case 'gateway_response_formatted':

                $value = $this->gateway_id && $this->gateway_id !== 'correction' ?
                    leyka_get_gateway_by_id($this->gateway_id)->get_gateway_response_formatted($this) : [];
                break;

            case 'type':
            case 'payment_type':
            case 'donation_type':
                $value = $this->_donation_meta['leyka_payment_type'];
                break;

            case 'type_label':
            case 'payment_type_label':
            case 'donation_type_label':

                $value = $this->is_init_recurring_donation ?
                    leyka_get_payment_types_list('rebill-init') : leyka_get_payment_types_list($this->payment_type);
                break;

            case 'type_desc':
            case 'payment_type_desc':
            case 'donation_type_desc':
            case 'type_description':
            case 'payment_type_description':
            case 'donation_type_description':
                $value = leyka_get_donation_type_description($this->payment_type);
                break;

            case 'init_recurring_donation_id':
                $value = $this->payment_type === 'rebill' ? ($this->_main_data->post_parent ? : $this->_id) : false;
                break;

            case 'init_recurring':
            case 'init_recurring_donation':

                if($this->payment_type !== 'rebill') {
                    break;
                }

                if($this->is_init_recurring_donation) {
                    $value = $this;
                } else if($this->init_recurring_donation_id) {

                    try {
                        $value = Leyka_Donations::get_instance()->get_donation($this->init_recurring_donation_id);
                    } catch(Exception $ex) {} // No init recurring donation in DB, for some reason

                }
                break;

            case 'is_init_recurring':
            case 'is_init_recurring_donation':
                $value = $this->type === 'rebill' && $this->init_recurring_donation_id === $this->id;
                break;

            case 'funded_rebills_number':
            case 'successful_rebills_number':
            case 'recurring_funded_rebills_number':
            case 'recurring_successful_rebills_number':

                if( !$this->is_init_recurring_donation ) {
                    break;
                }

                if($this->_donation_meta['leyka_recurring_funded_rebills_number'] === false) { // The rebills cache is empty
                    $this->update_recurring_funded_rebills_number(); // ... so recalculate the funded rebills number
                }

                $value = absint($this->_donation_meta['leyka_recurring_funded_rebills_number']);
                break;

            case 'recurring_active':
            case 'recurring_subscription_is_active':
            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'rebilling_is_active':
            case 'recurring_is_active':

                if($this->payment_type !== 'rebill') {
                    break;
                }

                $init_recurring_donation = $this->init_recurring_donation;
                $value = $init_recurring_donation ? $init_recurring_donation->get_meta('_rebilling_is_active') : NULL;
                break;

            case 'recurring_canceled':
            case 'recurrents_canceled':
                $value = $this->payment_type === 'rebill' ? !empty($this->_donation_meta['leyka_recurrents_cancelled']) : NULL;
                break;

            case 'recurrents_cancel_date':
            case 'recurring_cancel_date':
                $value = $this->payment_type == 'rebill' && !empty($this->_donation_meta['leyka_recurrents_cancel_date']) ?
                    $this->_donation_meta['leyka_recurrents_cancel_date'] : NULL;
                break;

            case 'cancel_recurring_requested':
            case 'cancelling_recurring_requested':
            case 'recurring_cancel_requested':
            case 'recurring_cancelling_requested':
                $value = $this->payment_type === 'rebill' && !!$this->_donation_meta['leyka_cancel_recurring_requested'];
                break;

            case 'recurring_cancel_reason':
            case 'recurring_cancelling_reason':
                $value = $this->payment_type === 'rebill' ? $this->_donation_meta['leyka_recurring_cancel_reason'] : false;
                break;

            case 'recurring_subscription_status':

                $init_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;
                $value = !empty($init_donation->_donation_meta['leyka_recurring_subscription_status']) ?
                    $init_donation->_donation_meta['leyka_recurring_subscription_status'] :
                    ($this->recurring_on ? 'active' : 'non-active');
                break;

            case 'recurring_subscription_error_id':

                $init_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;
                $value = $init_donation->_donation_meta['leyka_recurring_subscription_error_id'];
                break;

            case 'next_recurring_date_timestamp':

                $init_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;

                $value = !empty($init_donation->_donation_meta['leyka_next_recurring_date_timestamp']) ?
                    $init_donation->_donation_meta['leyka_next_recurring_date_timestamp'] :
                    $init_donation->update_next_recurring_date();
                break;

            case 'ga_client_id':
            case 'gua_client_id':

                $value = empty($this->_donation_meta['leyka_ga_client_id']) ?
                    NULL : $this->_donation_meta['leyka_ga_client_id'];
                break;

            default:
                $value = apply_filters('leyka_get_unknown_donation_field', null, $field, $this);
                $value = apply_filters('leyka_'.$this->gateway_id.'_get_unknown_donation_field', $value, $field, $this);
                break;
        }

        return apply_filters('leyka_get_donation_field', $value, $field, $this);

    }

    /**
     * @param $field string
     * @param $value mixed
     * @return boolean
     */
    public function __set($field, $value) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {
            case 'title':
            case 'payment_title':
            case 'purpose_text':

                if($value === $this->_main_data->post_title) {
                    return false;
                }

                // TODO Changing the post_title doesn't work - the payment_title ATM is always taken from the Campaign title. Fix it in the Leyka_Donation_Post constructor - the Campaign should be used only if Donation's post_title is empty
                global $wpdb;

                $update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_title=%s WHERE ID=%d", $value, $this->_id);
                $res = $wpdb->query($update_query);

                if( !$res ) {
                    return false;
                }

                $this->_main_data->post_title = $value;
                break;

            case 'status':
            case 'donation_status':

                if($value === $this->status || !array_key_exists($value, leyka_get_donation_status_list())) {
                    return false;
                }

                global $wpdb;

                $update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_status=%s WHERE ID=%d", $value, $this->_id);
                $res = $wpdb->query($update_query);

                if( !$res ) {
                    return false;
                }

                $old_status = $this->_main_data->post_status;
                $this->_main_data->post_status = $value;

                do_action('leyka_donation_status_'.$old_status.'_to_'.$value, $this);
                wp_transition_post_status($value, $old_status, $this->_main_data);

                if ($value === 'funded' || $old_status === 'funded') {
                    do_action('leyka_donation_funded_status_changed', $this->id, $old_status, $value);
                }

                $status_log = $this->get_meta('_status_log');
                if($status_log && is_array($status_log)) {
                    $status_log[] = ['date' => current_time('timestamp'), 'status' => $value,];
                } else {
                    $status_log = [['date' => current_time('timestamp'), 'status' => $value,]];
                }

                $this->set_meta('_status_log', $status_log);

                if($value !== 'failed') {
                    $this->delete_meta('leyka_error_id');
                }

                break;

            case 'error_id':
            case 'payment_error_id':
            case 'gateway_error_id':
            case 'donation_error_id':

                if($this->status !== 'failed') {
                    $this->status = 'failed';
                }

                $this->set_meta('leyka_error_id', $value);
                break;

            case 'date':

                $res = wp_update_post(['ID' => $this->_id, 'post_date' => $value,]);

                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_date = $value;
                break;

            case 'date_timestamp':

                $new_date = date('Y-m-d H:i:s', $value);
                $res = wp_update_post(['ID' => $this->_id, 'post_date' => $new_date,]);

                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_date = $new_date;
                break;

            case 'donor_name':
                $this->set_meta('leyka_donor_name', $value);
                break;
            case 'donor_email':
                $this->set_meta('leyka_donor_email', $value);
                break;
            case 'donor_comment':
                $this->set_meta('leyka_donor_comment', sanitize_textarea_field($value));
                break;

            case 'additional_fields':
            case 'donor_additional_fields':
            case 'donation_additional_fields':

                array_walk($value, function( &$value ){ $value = trim($value); });
                $this->set_meta('leyka_additional_fields', $value);
                break;

            case 'donor_email_date':
                $this->set_meta('leyka_donor_email_date', absint($value));
                break;

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':

                $value = absint($value);

                global $wpdb;
                $res = $wpdb->update($wpdb->prefix.'posts', ['post_author' => $value], ['ID' => $this->_id], ['%d'], ['%d']);
//                $res = wp_update_post(['ID' => $this->id, 'post_author' => $value,]);

                if($res === false) {
                    return false;
                }

                $this->_main_data->post_author = $value;
                break;

            case 'donor_account':

                if(is_wp_error($value)) {

                    $this->_donation_meta['donor_account_error'] = $value;
                    $this->set_meta('leyka_donor_account', $value);

                } else if(absint($value)) {
                    $this->donor_user_id = $value;
                }
                break;

            case 'sum':
            case 'amount':
            case 'donation_amount':

                $value = (float)$value;

                $this->set_meta('leyka_donation_amount', $value);

                do_action('leyka_donation_amount_changed', $this->_id, $value);
                break;

            case 'sum_total':
            case 'amount_total':
            case 'total_sum':
            case 'total_amount':
            case 'donation_amount_total':

                $value = (float)$value;

                $this->set_meta('leyka_donation_amount_total', $value);

                do_action('leyka_donation_total_amount_changed', $this->_id, $value);
                break;

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':
            case 'amount_curr_equiv':
            case 'amount_currency_equiv':

                $this->set_meta('leyka_donation_main_currency_amount', (float)$value);
                break;

            case 'currency':
            case 'currency_id':
            case 'donation_currency':
            case 'donation_currency_id':

                if($this->currency_id !== $value && leyka_get_currencies_data($value)) {
                    return false;
                }

                $this->set_meta('leyka_donation_currency', $value);
                break;

            case 'main_currency':
            case 'main_currency_id':

                $this->set_meta('leyka_donation_main_currency_id', (string)$value);
                break;

            case 'main_currency_rate':

                $this->set_meta('leyka_donation_main_currency_rate', (float)$value);
                break;

            case 'main_currency_amount_total':
            case 'donation_main_currency_amount_total':

                $value = (float)$value;

                $this->set_meta('leyka_donation_main_currency_amount_total', $value);

                do_action('leyka_donation_main_currency_amount_total_changed', $this->_id, $value);
                break;

            case 'gw_id':
            case 'gateway_id':
                if($value && ($this->gateway_id === $value || !leyka_get_gateway_by_id($value))) {
                    return false;
                }

                $this->set_meta('leyka_gateway', $value);
                break;

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':

                if($this->pm_id === $value) { // Don't check for leyka_get_pm_by_id() here, as pm_id may be custom payment info
                    return false;
                }

                $this->set_meta('leyka_payment_method', $value);

                do_action('leyka_donation_pm_changed', $this->_id, $value, $this->gateway_id);
                break;

            case 'type':
            case 'payment_type':

                if($this->payment_type === $value || !leyka_get_payment_types_list($value)) {
                    return false;
                }

                $old_value = $this->payment_type;

                if($this->set_meta('leyka_payment_type', $value) && ($old_value === 'rebill' || $value === 'rebill')
                    && !$this->is_init_recurring_donation ) {
                    $this->update_recurring_funded_rebills_number($old_value === 'rebill' ? 'remove' : 'add');
                }
                break;

            case 'campaign':
            case 'campaign_id':

                $value = absint($value);
                if($this->campaign_id === $value) {
                    return false;
                }

                $this->set_meta('leyka_campaign_id', $value);

                do_action('leyka_donation_campaign_changed', $this->_id, $value);
                break;

            case 'is_subscribed':
            case 'donor_subscribed':

                $value = !!$value;
                if($this->donor_subscribed === $value) {
                    return false;
                }

                $this->set_meta('leyka_donor_subscribed', $value);
                break;

            case 'subscription_email':
            case 'donor_subscription_email':

                if($this->donor_subscription_email === $value) {
                    return false;
                }

                $value = leyka_validate_email($value) ? $value : $this->donor_email;

                $this->set_meta('leyka_donor_subscription_email', $value);
                break;

            case 'init_recurring_donation_id':

                $value = absint($value);
                if($value != $this->_main_data->post_parent && $this->payment_type === 'rebill') {

                    wp_update_post(['ID' => $this->_id, 'post_parent' => $value,]);
                    $this->_main_data->post_parent = $value;

                }
                break;

            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'recurring_active':
            case 'rebilling_is_active':
            case 'recurring_is_active':
            case 'recurring_subscription_is_active':

                if($this->type !== 'rebill') {
                    break;
                }

                $value = !!$value;

                /** @var $init_recurring_donation Leyka_Donation_Base */
                $init_recurring_donation = $this->init_recurring_donation;
                if( !$init_recurring_donation ) {
                    return false;
                }

                if($init_recurring_donation->recurring_is_active != $value) {

                    $init_recurring_donation->set_meta('_rebilling_is_active', $value);

                    do_action('leyka_donation_recurring_activity_changed', $this->_id, $value);

                }

                if($value) {

                    $this->_donation_meta['recurrents_cancelled'] = false;
                    $this->_donation_meta['recurrents_cancel_date'] = 0;
                    $this->set_meta('leyka_recurrents_cancelled', false);
                    $this->set_meta('leyka_recurrents_cancel_date', 0);

                } else {

                    $this->set_meta('leyka_recurrents_cancelled', true);
                    $this->set_meta('leyka_recurrents_cancel_date', current_time('timestamp'));

                }
                break;

            case 'cancel_recurring_requested':
            case 'cancelling_recurring_requested':
            case 'recurring_cancel_requested':
            case 'recurring_cancelling_requested':
                $this->set_meta('leyka_cancel_recurring_requested', !!$value);
                break;
            case 'recurring_cancel_reason':
            case 'recurring_cancelling_reason':
                $this->set_meta('leyka_recurring_cancel_reason', trim($value));
                break;

            case 'recurring_subscription_status':

                if($this->type !== 'rebill') {
                    return false;
                }

                $init_recurring_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;

                if( !$init_recurring_donation ) {
                    return false;
                }

                $init_recurring_donation->set_meta('leyka_recurring_subscription_status', $value);

                break;

            case 'recurring_subscription_error_id':

                if($this->type !== 'rebill') {
                    return false;
                }

                $init_recurring_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;

                if( !$init_recurring_donation ) {
                    return false;
                }

                $init_recurring_donation->set_meta('leyka_recurring_subscription_error_id', $value);

                break;

            case 'next_recurring_date_timestamp':

                if($this->type !== 'rebill') {
                    return false;
                }

                $init_recurring_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;

                if( !$init_recurring_donation ) {
                    return false;
                }

                $init_recurring_donation->set_meta('leyka_next_recurring_date_timestamp', $value);

                break;

            case 'ga_client_id':
            case 'gua_client_id':
                $this->set_meta('leyka_ga_client_id', trim($value));
                break;

            default:
                do_action('leyka_set_unknown_donation_field', $field, $value, $this);
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function update_recurring_funded_rebills_number($action = '') {

        if( !$this->id || $this->type !== 'rebill' ) {
            return false;
        }

        if($action && !in_array($action, ['add', '+', 'remove', '-',])) {
            return false;
        }

        $init_recurring_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;
        if( !$init_recurring_donation ) {
            return false;
        }

        if(in_array($action, ['add', '+',])) {

            $rebills_number = $this->get_meta('leyka_recurring_funded_rebills_number');
            $rebills_number = $rebills_number ? absint($rebills_number) + 1 : 1;

        } else if(in_array($action, ['remove', '-'])) {

            $rebills_number = $this->get_meta('leyka_recurring_funded_rebills_number');
            $rebills_number = $rebills_number > 0 ? $rebills_number - 1 : 0;

        } else { // Total recalculation

            $rebills_number = Leyka_Donations::get_instance()->get_count([
                'status' => 'funded',
                'recurring_rebills_of' => $this->id,
            ]);

        }

        return $init_recurring_donation->set_meta('leyka_recurring_funded_rebills_number', $rebills_number);

    }

    // TODO Vyacheslav - consider adding a new "last_subscription_rebill" meta field to remove the parameter from this function
    public function update_recurring_subscription_status($is_new_rebill = false)
    {

        if ($this->payment_type !== 'rebill') {
            return;
        }

        $init_donation = $this->is_init_recurring_donation ? $this : $this->init_recurring_donation;

        if (!$init_donation->recurring_is_active) {

            $init_donation->recurring_subscription_status = 'non-active';
            $init_donation->recurring_subscription_error_id = false;
            $init_donation->leyka_next_recurring_date = false;

            return;

        }

        $payment_day = (int)date('j', $init_donation->date_timestamp);

        if (($is_new_rebill || $init_donation === $this) && date('n', $this->date_timestamp) === date('n')) {
            $rebill_this_month = $this;
        } else {

            $date_params = [
                'relation' => 'AND',
                [
                    'day' => [
                        $payment_day,
                        min($payment_day + 3, 31)
                    ],
                    'compare' => 'BETWEEN'
                ],
                ['month' => (int)date('n')],
                ['year' => (int)date('Y')]
            ];

            $rebill_this_month = Leyka_Donations::get_instance()->get([
                'recurring_rebills_of' => $init_donation->id,
                'get_single' => true,
                'date_query' => $date_params,
                'orderby' => ['date_timestamp' => 'DESC']
            ]);

        }

        $payment_day_passed = $payment_day > date('t') ? date('t') == (int)date('j') : $payment_day < (int)date('j');

        if ( !$rebill_this_month ) {

            if ( !$payment_day_passed && date('n', strtotime('-1 month')) === date('n', $init_donation->date_timestamp) ) {

                $init_donation->recurring_subscription_status = 'active';
                $init_donation->recurring_subscription_error_id = false;


            }  else {

                $init_donation->recurring_subscription_status = 'problematic';
                $init_donation->recurring_subscription_error_id = 'L-2001';

            }

        } else if($rebill_this_month->status === 'failed') {

            $init_donation->recurring_subscription_status = 'problematic';
            $init_donation->recurring_subscription_error_id = $rebill_this_month->error->id;

        } else if($rebill_this_month->status === 'funded') {

            $init_donation->recurring_subscription_status = 'active';
            $init_donation->recurring_subscription_error_id = false;

        }

        $init_donation->update_next_recurring_date();

    }

    public function update_next_recurring_date() {

        $init_donation = Leyka_Donations::get_instance()->get($this->init_recurring_donation_id);

        if($init_donation->recurring_on && $init_donation->recurring_subscription_status !== 'non-active') {

            $payment_day = min(date('t', strtotime('+1 month')), date('d', $init_donation->date_timestamp));
            $next_payment_date = strtotime(date('Y').'-'.date('m').'-'.$payment_day.(date('d') < $payment_day ? '' : ' +1 month'));
            $init_donation->next_recurring_date_timestamp = $next_payment_date;

            return $next_payment_date;

        }

        return false;

    }

    public function get_meta($meta_key) {

        $meta_key = trim($meta_key);
        if( !$meta_key ) {
            return NULL;
        }

        if( !isset($this->_donation_meta[$meta_key]) ) {
            $this->_donation_meta[$meta_key] = get_post_meta($this->_id, $meta_key, true);
        }

        return $this->_donation_meta[$meta_key];

    }

    public function set_meta($meta_name, $value) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) {
            return false;
        }

        if( !empty($this->_donation_meta[$meta_name]) && $this->_donation_meta[$meta_name] === $value ) {
            return true;
        }

        if(update_post_meta($this->_id, $meta_name, $value)) {
            $this->_donation_meta[$meta_name] = $value;
        } else {
            return false;
        }

        return true;

    }

    public function delete_meta($meta_name) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) {
            return false;
        }

        return delete_post_meta($this->_id, $meta_name);

    }

    public function add_gateway_response($response) {

        $this->_donation_meta['gateway_response'] = $response;
        return !!update_post_meta($this->_id, 'leyka_gateway_response', $this->_donation_meta['gateway_response']);

    }

    public function get_funded_date() {

        $last_date_funded = 0;

        foreach((array)$this->status_log as $status_change) {
            if($status_change['status'] === 'funded' && $status_change['date'] > $last_date_funded) {
                $last_date_funded = $status_change['date'];
            }
        }

        return $last_date_funded ? $last_date_funded : false;

    }

    public function delete($force = false) {
        wp_delete_post($this->_id, !!$force);
    }

}

/**
 * Old donation class - a pseudonim of Leyka_Donation_Post, added for backward-compatibility.
 *
 * @deprecated Use Leyka_Donations_Factory::get_instance()->getDonation($donation) instead.
 */
class Leyka_Donation extends Leyka_Donation_Post {
}