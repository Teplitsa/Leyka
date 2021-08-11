<?php if( !defined('WPINC') ) die;

/** Default donation class - WP_Post-based */
class Leyka_Donation_Post extends Leyka_Donation_Base {

    public static function add(array $params = array()) {

        $params = self::_handle_new_donation_params($params); // New Donation params pre-handling

        if(is_wp_error($params)) {
            return $params;
        }

        remove_all_actions('save_post_'.Leyka_Donation_Management::$post_type);

        $donation_params = array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => $params['status'],
            'post_title' => $params['payment_title'],
            'post_date' => $params['date_created'],
            'post_name' => uniqid('donation-', true), // For fast WP_Post creation when DB already has lots of donations
            'post_parent' => $params['init_recurring_donation'],
            'post_author' => $params['donor_user_id'],
        );

        $donation_id = wp_insert_post($donation_params, true);

        if(is_wp_error($donation_id)) {
            return $donation_id;
        }

        $params['currency_id'] = empty($params['currency_id']) ?
            (empty($params['currency']) ? $params['currency'] : false) : $params['currency_id'];
        $params['currency_id'] = $params['currency_id'] && leyka_get_currencies_full_info($params['currency_id']) ?
            $params['currency_id'] : leyka_get_country_currency();

        $donation_meta_fields = array(
            'leyka_donation_amount' => $params['amount'],
            'leyka_donation_currency' => $params['currency_id'],
            'leyka_payment_type' => $params['payment_type'],
            'leyka_donor_name' => $params['donor_name'],
            'leyka_donor_email' => $params['donor_email'],
            'leyka_gateway' => $params['gateway_id'],
            'leyka_payment_method' => $params['pm_id'],
            'leyka_campaign_id' => $params['campaign_id'],
//            '_leyka_donor_email_date' => 0, /** @todo Check if this lines are needed at all */
//            '_leyka_managers_emails_date' => 0,
            '_status_log' => array(array('date' => current_time('timestamp'), 'status' => $params['status'])),
        );

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
                    $params['recurring_cancel_date'] : current_time('timestamp');

            }

            if($donation_meta_fields['_rebilling_is_active']) {
                do_action('leyka_donation_recurring_activity_changed', $donation_id, $donation_meta_fields['recurring_active']);
            }

        }

        if($params['donor_subscribed']) {
            $donation_meta_fields['donor_subscribed'] = $params['donor_subscribed'];
        }

        /** @todo Check if it's needed (if there are other post_status changing based handlers, it won't) */
//        Leyka_Donation_Management::get_instance()->donation_status_changed($params['status'], 'new', new self($donation_id));

        if($params['gateway_id']) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $donation_id, $params);
        }

        $donation_meta_fields = apply_filters(
            "leyka_{$params['gateway_id']}_new_donation_specific_data",
            $donation_meta_fields,
            $donation_id,
            $params
        );

        foreach($donation_meta_fields as $key => $value) {

            if( !add_post_meta($donation_id, $key, $value) ) {

                wp_delete_post($donation_id, true);

                return new WP_Error(
                    'donation_addition_error',
                    __('Error while adding a donation', 'leyka'),
                    array('donation_meta_not_inserted' => array('key' => $key, 'value' => $value))
                );

            }

        }

        return $donation_id;

    }

    /**
     * @deprecated Use self::get_init_recurring_donation($donation) instead.
     * @param mixed $donation
     * @return mixed Leyka_Donation or false if param is wrong or nothing found.
     */
    public static function get_init_recurrent_donation($donation) {
        return self::get_init_recurring_donation($donation);
    }

	public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && absint($donation)) {

            $donation = absint($donation);

            global $wpdb;
            $this->_main_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $donation));

            if( !$this->_main_data || $this->_main_data->post_type !== Leyka_Donation_Management::$post_type ) {
                return false;
            }

            $this->_id = $donation;

        } else if(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            $this->_id = $donation->ID;
            $this->_main_data = $donation;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            $this->_id = $donation->id;
        } else {
            return false;
        }

        if( !$this->_donation_meta ) {

            $meta = get_post_meta($this->_id, '', true);

            if( !empty($meta['leyka_campaign_id']) ) {

                // Don't use Leyka_Campaign here to avoid loop dependency:
                $campaign = get_post((int)$meta['leyka_campaign_id'][0]);
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

            $this->_donation_meta = array(
                'payment_title' => empty($payment_title) ? $this->_main_data->post_title : $payment_title,
                'payment_type' => empty($meta['leyka_payment_type']) ? 'single' : $meta['leyka_payment_type'][0],
                'payment_method' => empty($meta['leyka_payment_method']) ? '' : $meta['leyka_payment_method'][0],
                'gateway' => empty($meta['leyka_gateway']) ? '' : $meta['leyka_gateway'][0],
                'currency' => empty($meta['leyka_donation_currency']) ? 'rur' : $meta['leyka_donation_currency'][0],
                'amount' => $donation_amount,
                'amount_total' => $donation_amount_total,
                'main_curr_amount' => !empty($meta['leyka_main_curr_amount'][0]) ?
                    (float)$meta['leyka_main_curr_amount'][0] : $donation_amount,
                'donor_name' => empty($meta['leyka_donor_name']) ? '' : $meta['leyka_donor_name'][0],
                'donor_email' => empty($meta['leyka_donor_email']) ? '' : $meta['leyka_donor_email'][0],
                'donor_comment' => empty($meta['leyka_donor_comment']) ? '' : $meta['leyka_donor_comment'][0],
                'donor_subscription_email' => empty($meta['leyka_donor_subscription_email']) ?
                    '' : $meta['leyka_donor_subscription_email'][0],
                'donor_email_date' => empty($meta['_leyka_donor_email_date']) ?
                    '' : $meta['_leyka_donor_email_date'][0],
                'managers_emails_date' => empty($meta['_leyka_managers_emails_date']) ?
                    '' : $meta['_leyka_managers_emails_date'][0],
                'campaign_id' => empty($meta['leyka_campaign_id']) ? 0 : $meta['leyka_campaign_id'][0],
                'donor_subscribed' => empty($meta['leyka_donor_subscribed']) ?
                    false : $meta['leyka_donor_subscribed'][0],
                'donor_account_error' => empty($meta['leyka_donor_account_error']) ? '' : $meta['leyka_donor_account_error'][0],
                'status_log' => empty($meta['_status_log']) ? '' : maybe_unserialize($meta['_status_log'][0]),
                'gateway_response' => empty($meta['leyka_gateway_response']) ? '' : $meta['leyka_gateway_response'][0],

                'recurrents_cancelled' => isset($meta['leyka_recurrents_cancelled']) ?
                    $meta['leyka_recurrents_cancelled'][0] : false,
                'recurrents_cancel_date' => isset($meta['leyka_recurrents_cancel_date']) ?
                    $meta['leyka_recurrents_cancel_date'][0] : false,

                // For active schemes of recurring donations:
                'rebilling_is_active' => !empty($meta['_rebilling_is_active'][0]),
                'cancel_recurring_requested' => isset($meta['leyka_cancel_recurring_requested']) ?
                    $meta['leyka_cancel_recurring_requested'][0] : false,
            );
        }

        return $this;

	}

    public function __get($field) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {
            case 'id':
            case 'ID':
                return $this->_id;

            case 'campaign_id':
                return $this->_donation_meta['campaign_id'];
            case 'campaign':
                $campaign = new Leyka_Campaign($this->_donation_meta['campaign_id']);
                return $campaign ? $campaign : false;

            case 'campaign_title':
                $campaign = $this->campaign;
                return $campaign ? $campaign->title : $this->payment_title;

            case 'title':
            case 'name':
                return $this->_main_data->post_title;

            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                return $this->_donation_meta['payment_title'] ?
                    $this->_donation_meta['payment_title'] : $this->campaign->payment_title;

            case 'status':
                return $this->_main_data->post_status;
            case 'status_label':
                return leyka()->get_donation_status_info($this->_main_data->post_status, 'label');

            case 'status_desc':
            case 'status_description':
                return leyka()->get_donation_status_info($this->_main_data->post_status, 'description');

            case 'status_log':
                return $this->_donation_meta['status_log'];

            case 'date':
            case 'date_label':
                $date_format = get_option('date_format');
                $donation_timestamp = $this->date_timestamp;

                return apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $donation_timestamp),
                    $donation_timestamp, $date_format
                );
            case 'time':
            case 'time_label':
                $time_format = get_option('time_format');
                $donation_timestamp = $this->date_timestamp;

                return apply_filters(
                    'leyka_admin_donation_time',
                    date($time_format, $donation_timestamp),
                    $donation_timestamp, $time_format
                );
            case 'date_time':
            case 'date_time_label':
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $donation_timestamp = $this->date_timestamp;

                return apply_filters(
                    'leyka_admin_donation_date_time',
                    date("$date_format, $time_format", $donation_timestamp),
                    $donation_timestamp, $date_format, $time_format
                );

            case 'date_timestamp': return strtotime($this->_main_data->post_date);

            case 'date_funded':
            case 'date_funded_label':
            case 'funded_date':
            case 'funded_date_label':
                $date_funded = $this->get_funded_date();
                return $date_funded ? date(get_option('date_format'), $date_funded) : 0;

            case 'date_funded_timestamp':
            case 'funded_date_timestamp':
                return $this->date_funded ? strtotime($this->date_funded) : false;

            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                return $this->_donation_meta['payment_method'];

            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                return empty($this->_donation_meta['gateway']) ? '' : $this->_donation_meta['gateway'];

            case 'pm_full_id':
                return empty($this->_donation_meta['gateway']) || empty($this->_donation_meta['payment_method']) ?
                    '' : $this->_donation_meta['gateway'].'-'.$this->_donation_meta['payment_method'];

            case 'gw_label':
            case 'gateway_label':

                if(empty($this->_donation_meta['gateway'])) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->_donation_meta['gateway']);
                return $gateway ? $gateway->label : __('Unknown gateway', 'leyka');

            case 'pm_label':
            case 'payment_method_label':
                $pm = leyka_get_pm_by_id($this->_donation_meta['gateway'].'-'.$this->_donation_meta['payment_method'], true);
                return $pm ? $pm->label : __('Unknown payment method', 'leyka');

            case 'currency':
            case 'currency_id':
            case 'currency_code':
                return $this->_donation_meta['currency'];

            case 'currency_label':
                return leyka_get_currency_label($this->_donation_meta['currency']);

            case 'sum':
            case 'amount':
                return empty($this->_donation_meta['amount']) ? 0.0 : $this->_donation_meta['amount'];
            case 'sum_formatted':
            case 'amount_formatted':
                return leyka_format_amount(round($this->amount, 2));

            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':
                return empty($this->_donation_meta['amount_total']) ? $this->amount : $this->_donation_meta['amount_total'];
            case 'total_sum_formatted':
            case 'total_amount_formatted':
            case 'sum_total_formatted':
            case 'amount_total_formatted':
                return leyka_format_amount(round($this->amount_total, 2));

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':
                return $this->_donation_meta['main_curr_amount'];

            case 'donor_name':
                return stripslashes($this->_donation_meta['donor_name']);
            case 'donor_email':
                return $this->_donation_meta['donor_email'];
            case 'donor_comment':
                return empty($this->_donation_meta['donor_comment']) ? '' : $this->_donation_meta['donor_comment'];
            case 'donor_email_date':
                return $this->_donation_meta['donor_email_date'];
            case 'managers_emails_date':
                return $this->_donation_meta['managers_emails_date'];

            case 'is_subscribed':
            case 'donor_subscribed':
                return $this->_donation_meta['donor_subscribed'];

            case 'subscription_email':
            case 'donor_subscription_email':
                return $this->_donation_meta['donor_subscription_email'] ?
                    $this->_donation_meta['donor_subscription_email'] :
                    ($this->_donation_meta['donor_email'] ? $this->_donation_meta['donor_email'] : '');

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':
                return isset($this->_main_data->post_author) ? (int)$this->_main_data->post_author : false;

            case 'donor_user_error':
            case 'donor_account_error':
                $donor_account_error = isset($this->_donation_meta['donor_account_error']) ?
                    maybe_unserialize($this->_donation_meta['donor_account_error']) : false;
                return $donor_account_error && is_wp_error($donor_account_error) ? $donor_account_error : false;

            case 'gateway_response':
                return $this->_donation_meta['gateway_response'];
            case 'gateway_response_formatted':
                return $this->gateway_id && $this->gateway_id !== 'correction' ?
                    leyka_get_gateway_by_id($this->gateway_id)->get_gateway_response_formatted($this) : array();

            case 'type':
            case 'payment_type':
            case 'donation_type':
                return $this->_donation_meta['payment_type'];

            case 'type_label':
            case 'payment_type_label':
            case 'donation_type_label':
                return $this->is_init_recurring_donation ?
                    leyka_get_payment_types_list('rebill-init') : leyka_get_payment_types_list($this->payment_type);

            case 'type_desc':
            case 'payment_type_desc':
            case 'donation_type_desc':
            case 'type_description':
            case 'payment_type_description':
            case 'donation_type_description':
                return leyka_get_donation_type_description($this->payment_type);

            case 'init_recurring_donation_id':
                return $this->payment_type === 'rebill' ?
                    ($this->_main_data->post_parent ? $this->_main_data->post_parent : $this->_id) : false;

            case 'init_recurring_donation':
                if($this->payment_type !== 'rebill') {
                    return false;
                } else if($this->init_recurring_donation_id) {
                    return Leyka_Donations::get_instance()->get_donation($this->init_recurring_donation_id);
                } else {
                    return $this;
                }

            case 'is_init_recurring':
            case 'is_init_recurring_donation':
                return $this->type === 'rebill' && $this->init_recurring_donation_id === $this->id;

            case 'cancel_recurring_requested':
            case 'recurring_cancelling_requested':
                return $this->payment_type === 'rebill' ? $this->_donation_meta['cancel_recurring_requested'] : false;

            case 'recurring_active':
            case 'recurring_subscription_is_active':
            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'rebilling_is_active':
            case 'recurring_is_active':
                return $this->payment_type === 'rebill' ? !empty($this->_donation_meta['rebilling_is_active']) : NULL;

            case 'recurring_canceled':
                return !$this->recurring_active;

            case 'recurrents_cancel_date':
            case 'recurring_cancel_date':
                return $this->payment_type == 'rebill' ? !empty($this->_donation_meta['recurrents_cancel_date']) : NULL;

            default:
                return apply_filters('leyka_'.$this->gateway_id.'_get_unknown_donation_field', null, $field, $this);
        }

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

                $res = wp_update_post(array('ID' => $this->_id, 'post_title' => $value));
                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_title = $value;
                break;

            case 'status':
            case 'donation_status':

                if($value === $this->status || !array_key_exists($value, leyka_get_donation_status_list())) {
                    return false;
                }

                $res = wp_update_post(array('ID' => $this->_id, 'post_status' => $value));
                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $old_status = $this->_main_data->post_status;
                $this->_main_data->post_status = $value;

                do_action('leyka_donation_status_'.$old_status.'_to_'.$value);
                Leyka_Donation_Management::get_instance()->donation_status_changed($value, $old_status, $this);

                $status_log = get_post_meta($this->_id, '_status_log', true);
                if($status_log && is_array($status_log)) {
                    $status_log[] = array('date' => current_time('timestamp'), 'status' => $value);
                } else {
                    $status_log = array(array('date' => current_time('timestamp'), 'status' => $value));
                }

                update_post_meta($this->_id, '_status_log', $status_log);
                $this->_donation_meta['status_log'] = $status_log;
                break;

            case 'date':
                $res = wp_update_post(array('ID' => $this->_id, 'post_date' => $value));

                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_date = $value;
                break;
            case 'date_timestamp':
                $new_date = date('Y-m-d H:i:s', $value);
                $res = wp_update_post(array('ID' => $this->_id, 'post_date' => $new_date));

                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_date = $new_date;
                break;

            case 'donor_name':
                update_post_meta($this->_id, 'leyka_donor_name', $value);
                $this->_donation_meta['donor_name'] = $value;
                break;
            case 'donor_email':
                update_post_meta($this->_id, 'leyka_donor_email', $value);
                $this->_donation_meta['donor_email'] = $value;
                break;
            case 'donor_comment':
                $value = sanitize_textarea_field($value);
                update_post_meta($this->_id, 'leyka_donor_comment', $value);
                $this->_donation_meta['donor_comment'] = $value;
                break;

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':

                $value = absint($value);

                $res = wp_update_post(array('ID' => $this->id, 'post_author' => $value));
                if( !$res || is_wp_error($res) ) {
                    return false;
                }

                $this->_main_data->post_author = $value;
                break;

            case 'donor_account':
                if(is_wp_error($value)) {

                    $this->_donation_meta['donor_account_error'] = $value;
                    update_post_meta($this->_id, 'leyka_donor_account', $value);

                } else if(absint($value)) {

                    $value = absint($value);

                    $res = wp_update_post(array('ID' => $this->id, 'post_author' => $value));
                    if( !$res || is_wp_error($res) ) {
                        return false;
                    }

                    $this->_main_data->post_author = $value;

                }
                break;

            case 'sum':
            case 'amount':
            case 'donation_amount':

                $value = (float)$value;

                update_post_meta($this->_id, 'leyka_donation_amount', $value);
                $this->_donation_meta['amount'] = $value;

                do_action('leyka_donation_amount_changed', $this->_id, $value);
                break;

            case 'sum_total':
            case 'amount_total':
            case 'total_sum':
            case 'total_amount':
            case 'donation_amount_total':

                $value = (float)$value;

                update_post_meta($this->_id, 'leyka_donation_amount_total', $value);
                $this->_donation_meta['amount_total'] = $value;

                do_action('leyka_donation_total_amount_changed', $this->_id, $value);
                break;

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':
                return true; //$this->set_meta('amount_in_main_currency', (float)$value);

            case 'currency':
            case 'currency_id':
            case 'donation_currency':
            case 'donation_currency_id':
                if($this->currency_id === $value || !leyka_get_currencies_data($value)) {
                    return false;
                }

                update_post_meta($this->_id, 'leyka_donation_currency', $value);
                $this->_donation_meta['currency'] = $value;
                break;

            case 'gw_id':
            case 'gateway_id':
                if($value && ($this->gateway_id === $value || !leyka_get_gateway_by_id($value))) {
                    return false;
                }

                update_post_meta($this->_id, 'leyka_gateway', $value);
                $this->_donation_meta['gateway'] = $value;
                break;

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':

                if($this->pm_id === $value) { // Don't check for leyka_get_pm_by_id() here, as pm_id may be custom payment info
                    return false;
                }

                update_post_meta($this->_id, 'leyka_payment_method', $value);
                $this->_donation_meta['payment_method'] = $value;

                do_action('leyka_donation_pm_changed', $this->_id, $value, $this->gateway_id);
                break;

            case 'type':
            case 'payment_type':
                if($this->payment_type === $value || !leyka_get_payment_types_list($value)) {
                    return false;
                }

                update_post_meta($this->_id, 'leyka_payment_type', $value);
                $this->_donation_meta['payment_type'] = $value;
                break;

            case 'campaign':
            case 'campaign_id':

                $value = absint($value);
                if($this->campaign_id === $value) {
                    return false;
                }

                update_post_meta($this->_id, 'leyka_campaign_id', $value);
                $this->_donation_meta['campaign_id'] = $value;

                do_action('leyka_donation_campaign_changed', $this->_id, $value);

                break;

            case 'is_subscribed':
            case 'donor_subscribed':

                $value = !!$value;
                if($this->donor_subscribed === $value) {
                    return false;
                }

                update_post_meta($this->_id, 'leyka_donor_subscribed', $value);
                $this->_donation_meta['donor_subscribed'] = $value;
                break;

            case 'subscription_email':
            case 'donor_subscription_email':

                if($this->donor_subscription_email === $value) {
                    return false;
                }

                $value = leyka_validate_email($value) ? $value : $this->donor_email;

                update_post_meta($this->_id, 'leyka_donor_subscription_email', $value);
                $this->_donation_meta['donor_subscription_email'] = $value;
                break;

            case 'init_recurring_donation_id':

                $value = absint($value);
                if($value != $this->_main_data->post_parent && $this->payment_type === 'rebill') {

                    wp_update_post(array('ID' => $this->_id, 'post_parent' => $value));
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

                $init_recurring_donation = $this->init_recurring_donation;
                if( !$init_recurring_donation ) {
                    return false;
                }

                if($init_recurring_donation->recurring_is_active != $value) {

                    update_post_meta($init_recurring_donation->id, '_rebilling_is_active', $value);
                    $this->_donation_meta['rebilling_is_active'] = $value;

                    do_action('leyka_donation_recurring_activity_changed', $this->_id, $value);

                }

                if($value) {

                    $this->_donation_meta['recurrents_cancelled'] = false;
                    $this->_donation_meta['recurrents_cancel_date'] = 0;
                    update_post_meta($this->_id, 'leyka_recurrents_cancelled', false);
                    update_post_meta($this->_id, 'leyka_recurrents_cancel_date', 0);

                } else {

                    $this->_donation_meta['recurrents_cancelled'] = true;
                    $this->_donation_meta['recurrents_cancel_date'] = current_time('timestamp');
                    update_post_meta($this->_id, 'leyka_recurrents_cancelled', true);
                    update_post_meta($this->_id, 'leyka_recurrents_cancel_date', $this->_donation_meta['recurrents_cancel_date']);

                }
                break;

            case 'cancel_recurring_requested':
                update_post_meta($this->_id, 'leyka_cancel_recurring_requested', !!$value);
                break;

            default:
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function get_meta($meta_key) {

        $meta_key = trim($meta_key);
        if( !$meta_key ) { /** @todo Throw an Ex? */
            return NULL;
        }

        if( !isset($this->_donation_meta[$meta_key]) ) {
            $this->_donation_meta[$meta_key] = get_post_meta($this->_id, $meta_key, true);
        }

        return $this->_donation_meta[$meta_key];

    }

    public function set_meta($meta_name, $value) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) { /** @todo Throw an Ex? */
            return false;
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
        if( !$meta_name ) { /** @todo Throw an Ex? */
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

/** @todo Check if this code is needed */
//function leyka_cancel_recurrents_action() {
//
//    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_recurrent_cancel') || empty($_POST['donation_id'])) {
//        die('-1');
//    }
//
//    $_POST['donation_id'] = (int)$_POST['donation_id'];
//
//    $donation = new Leyka_Donation($_POST['donation_id']);
//    do_action('leyka_cancel_recurrents-'.$donation->gateway_id, $donation);
//
//}
//add_action('wp_ajax_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');
//add_action('wp_ajax_nopriv_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');

/**
 * Old donation class - a pseudonim of Leyka_Donation_Post, added for backward-compatibility.
 *
 * @deprecated Use Leyka_Donations_Factory::get_instance()->getDonation($donation) instead.
 */
class Leyka_Donation extends Leyka_Donation_Post {
}