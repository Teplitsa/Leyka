<?php if( !defined('WPINC') ) die;

/** Separately stored donation class - the donation data is kept in the separated DB tables */
class Leyka_Donation_Separated extends Leyka_Donation_Base {

    public static function add(array $params = []) {

        $params = self::_handle_new_donation_params($params); // New Donation params pre-handling

        if(is_wp_error($params)) {
            return $params;
        }

        $params['date_created'] = $params['date_created'] ? : current_time('mysql');

        $params['currency_id'] = empty($params['currency_id']) ?
            (empty($params['currency']) ? $params['currency'] : false) : $params['currency_id'];
        $params['currency_id'] = $params['currency_id'] && leyka_get_currencies_full_info($params['currency_id']) ?
            $params['currency_id'] : leyka_get_country_currency();

        $new_donation_data = [
            'status' => $params['status'],
            'payment_type' => $params['payment_type'],
            'date_created' => $params['date_created'],
            'gateway_id' => $params['gateway_id'] ? $params['gateway_id'] : '',
            'pm_id' => $params['pm_id'] ? : '',
            'currency_id' => $params['currency_id'],
            'amount' => $params['amount'],
            'amount_total' => $params['amount_total'],
            'amount_in_main_currency' => $params['amount_in_main_currency'],
            'amount_total_in_main_currency' => $params['amount_total_in_main_currency'],
            'donor_name' => $params['donor_name'] ? $params['donor_name'] : '',
            'donor_email' => $params['donor_email'] ? $params['donor_email'] : '',
        ];
        $new_donation_data_placeholders = ['%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s',];

        if($params['campaign_id']) { // Due to campaign_id field foreign key constraint

            $new_donation_data['campaign_id'] = $params['campaign_id'];
            $new_donation_data_placeholders[] = '%d';

        }

        global $wpdb;

        $res = $wpdb->insert($wpdb->prefix.'leyka_donations', $new_donation_data, $new_donation_data_placeholders);

        if($res) {
            $donation_id = $wpdb->insert_id;
        } else {
            return new WP_Error(
                'donation_addition_error',
                __('Error while adding a donation', 'leyka'),
                ['error_details' => $wpdb->last_error]
            );
        }

        if($params['gateway_id']) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $donation_id, $params);
        }

        $donation_meta_fields = [
            'payment_title' => $params['payment_title'],
            '_status_log' => [['date' => current_time('timestamp'), 'status' => $params['status']]],
        ];

        if($params['donor_comment']) {
            $donation_meta_fields['donor_comment'] = $params['donor_comment'];
        }

        if(leyka_options()->opt('donors_management_available') || leyka_options()->opt('donor_accounts_available')) {
            $donation_meta_fields['donor_user_id'] = $params['donor_user_id'];
        }

        if($params['payment_type'] === 'rebill') {
            $donation_meta_fields['init_recurring_donation_id'] = $params['init_recurring_donation'];
        }

        if($params['payment_type'] === 'rebill' && !$params['init_recurring_donation']) { // Init recurring donations only

            $donation_meta_fields['recurring_active'] =
                !empty($params['rebilling_is_active']) ||
                !empty($params['rebilling_on']) ||
                !empty($params['recurring_active']) ||
                !empty($params['recurring_is_active']) ||
                !empty($params['recurring_on']);

            if($params['recurring_cancelled']) {

                $donation_meta_fields['recurring_active'] = 0;
                $donation_meta_fields['recurring_cancel_date'] = $params['recurring_cancel_date'] ?
                    : current_time('timestamp');

            }

            if($donation_meta_fields['recurring_active']) {
                do_action('leyka_donation_recurring_activity_changed', $donation_id, $donation_meta_fields['recurring_active']);
            }

            if($params['recurring_cancel_date'] && empty($donation_meta_fields['recurring_cancel_date'])) {
                $donation_meta_fields['recurring_cancel_date'] = $params['recurring_cancel_date'];
            }

            if($params['recurring_cancel_requested']) {
                $donation_meta_fields['cancel_recurring_requested'] = $params['recurring_cancel_requested'];
            }

            if($params['recurring_cancel_reason']) {
                $donation_meta_fields['recurring_cancel_reason'] = $params['recurring_cancel_reason'];
            }

        }

        if($params['donor_subscribed']) {
            $donation_meta_fields['donor_subscribed'] = $params['donor_subscribed'];
        }

        if($params['ga_client_id']) {
            $donation_meta_fields['ga_client_id'] = $params['ga_client_id'];
        }

        $donation_meta_fields = apply_filters(
            "leyka_{$params['gateway_id']}_new_donation_specific_data",
            $donation_meta_fields,
            $donation_id,
            $params
        );
        $donation_meta_fields = apply_filters('leyka_new_donation_specific_data', $donation_meta_fields, $donation_id, $params);

        foreach($donation_meta_fields as $key => $value) {

            $res = $wpdb->insert($wpdb->prefix.'leyka_donations_meta', [
                'donation_id' => $donation_id,
                'meta_key' => $key,
                'meta_value' => is_object($value) || is_array($value) ? serialize($value) : $value,
            ], ['%d', '%s', '%s',]);

            if( !$res ) {

                $wpdb->delete($wpdb->prefix.'leyka_donations', ['ID' => $donation_id], ['%d']);

                return new WP_Error(
                    'donation_addition_error',
                    __('Error while adding a donation', 'leyka'),
                    ['donation_meta_not_inserted' => ['key' => $key, 'value' => $value]]
                );

            }

        }

        Leyka_Donation_Management::get_instance()->donation_status_changed($params['status'], 'new', new self($donation_id));

        return $donation_id;

    }

    public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && absint($donation)) {
            $this->_id = absint($donation);
        } else if(is_a($donation, 'WP_Post')) {

            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                throw new Exception(sprintf(__('Wrong post type for donation ("%s" given)', 'leyka'), $donation->post_type));
            }

            $this->_id = $donation->ID;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            $this->_id = $donation->id;
        } else if(is_object($donation) && isset($donation->ID) && isset($donation->campaign_id)) { // Donations table row object

            $this->_id = $donation->ID;
            $this->_main_data = $donation;
            unset($this->_main_data->ID);

            return;

        } else {
            throw new Exception(__('Incorrect argument for donation initialization in the DB', 'leyka'));
        }

        global $wpdb;
        $this->_main_data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}leyka_donations` WHERE `ID`=%d LIMIT 0,1", $this->_id)
        );

        if( !$this->_main_data ) {
            throw new Exception(sprintf(__('No donation #%s in the DB', 'leyka'), $this->_id));
        }

        do_action('leyka_donation_constructor_meta', [], $this->_id);

        $this->_donation_meta = apply_filters('leyka_donation_constructor_meta', [], $this->_id);

    }

    /**
     * @param $field string
     * @return mixed
     */
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
                $value = $this->_main_data->campaign_id;
                break;
            case 'campaign':
                $value = $this->_main_data->campaign_id ? new Leyka_Campaign($this->_main_data->campaign_id) : false;
                break;

            case 'campaign_title':

                $campaign = $this->campaign;
                $value = $campaign ? strip_tags($campaign->title) : strip_tags($this->payment_title);
                break;

            case 'title':
            case 'name':

                $campaign = $this->campaign;
                $value = $campaign ? $campaign->title : '';
                break;

            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                $value = $this->get_meta('payment_title') ? $this->get_meta('payment_title') : $this->campaign->payment_title;
                break;

            case 'status':
                $value = $this->_main_data->status;
                break;
            case 'status_label':
                $value = Leyka::get_donation_status_info($this->_main_data->status, 'label');
                break;

            case 'status_desc':
            case 'status_description':
                $value = Leyka::get_donation_status_info($this->_main_data->status, 'description');
                break;

            case 'status_desc_for_donor':
            case 'status_desc_for_donors':
            case 'status_description_for_donor':
            case 'status_description_for_donors':
                $value = Leyka::get_donation_status_info($this->_main_data->post_status, 'description_for_donors');
                break;

            case 'status_log':
                $value = $this->get_meta('_status_log');
                break;

            case 'error_id':
            case 'payment_error_id':
            case 'gateway_error_id':
            case 'donation_error_id':

                $error_id = false;
                if($this->status === 'failed' && $this->get_meta('error_id')) {
                    $error_id = $this->get_meta('error_id');
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
                $value = strtotime($this->_main_data->date_created);
                break;

            case 'date_funded':
            case 'date_funded_label':
            case 'funded_date':
            case 'funded_date_label':
                $value = $this->get_meta('date_funded') ? date(get_option('date_format'), $this->get_meta('date_funded')) : false;
                break;

            case 'date_funded_timestamp':
            case 'funded_date_timestamp':
                $value = $this->date_funded ? strtotime($this->date_funded) : false;
                break;

            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                $value = $this->_main_data->pm_id ? : false;
                break;

            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                $value = $this->_main_data->gateway_id ? : false;
                break;

            case 'pm_full_id':

                $value = $this->_main_data->gateway_id && $this->_main_data->pm_id ?
                    $this->_main_data->gateway_id.'-'.$this->_main_data->pm_id : '';
                break;

            case 'gw_label':
            case 'gateway_label':

                if(empty($this->_main_data->gateway_id)) {

                    $value = __('Unknown gateway', 'leyka');
                    break;

                }

                $gateway = leyka_get_gateway_by_id($this->_main_data->gateway_id);

                $value = $gateway ? $gateway->label : __('Unknown gateway', 'leyka');
                break;

            case 'pm_label':
            case 'payment_method_label':

                $pm = leyka_get_pm_by_id($this->_main_data->gateway_id.'-'.$this->_main_data->pm_id, true);
                $value = $pm ? $pm->label : __('Unknown payment method', 'leyka');
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

                if($this->_main_data->currency_id == 'rur') { // Update the old RUR currency ID

                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix.'leyka_donations',
                        ['currency_id' => 'rub',],
                        ['ID' => $this->_id, 'currency_id' => 'rur',]
                    );

                    $this->_main_data->currency_id = 'rub';

                }
                $value = $this->_main_data->currency_id;
                break;

            case 'currency_label':
                $value = leyka_get_currency_label($this->_main_data->currency_id);
                break;

            case 'sum':
            case 'amount':
                $value = $this->_main_data->amount ? : 0.0;
                break;
            case 'sum_formatted':
            case 'amount_formatted':
                $value = leyka_format_amount(round($this->amount, 2));
                break;

            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':
                $value = $this->_main_data->amount_total ? : $this->amount;
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
                $value = $this->_main_data->amount_in_main_currency ? : $this->amount;
                break;

            case 'donor_name':
                $value = stripslashes($this->_main_data->donor_name);
                break;
            case 'donor_email':
                $value = $this->_main_data->donor_email;
                break;
            case 'donor_phone':
                $value = leyka_get_donor_phone($this->_id);
                break;
            case 'donor_comment':
                $value = $this->get_meta('donor_comment');
                break;

            case 'additional_fields':
            case 'donor_additional_fields':
            case 'donation_additional_fields':

                $donation_additional_fields = $this->get_meta('additional_fields');
                $value = $donation_additional_fields && is_array($donation_additional_fields) ? $donation_additional_fields : [];
                break;

            case 'donor_email_date':
                $value = $this->get_meta('donor_email_date');
                break;
            case 'managers_emails_date':
                $value = $this->get_meta('managers_emails_date');
                break;

            case 'is_subscribed':
            case 'donor_subscribed':
                $value = $this->get_meta('donor_subscribed');
                break;

            case 'subscription_email':
            case 'donor_subscription_email':
                $value = $this->get_meta('donor_subscription_email') ? : ($this->donor_email ? : '');
                break;

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':
                $value = isset($this->_main_data->donor_user_id) ? absint($this->_main_data->donor_user_id) : false;
                break;

            case 'donor_user_error':
            case 'donor_account_error':

                $donor_account_error = isset($this->_donation_meta['donor_account_error']) ?
                    maybe_unserialize($this->_donation_meta['donor_account_error']) : false;
                $value = $donor_account_error && is_wp_error($donor_account_error) ? $donor_account_error : false;
                break;

            case 'gateway_response':
                $value = $this->get_meta('gateway_response');
                break;

            case 'gateway_response_formatted':

                $value = $this->gateway_id && $this->gateway_id !== 'correction' ?
                    leyka_get_gateway_by_id($this->gateway_id)->get_gateway_response_formatted($this) : [];
                break;

            case 'type':
            case 'payment_type':
            case 'donation_type':
                $value = $this->_main_data->payment_type;
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
                $value = leyka_get_donation_type_description($this->type);
                break;

            case 'init_recurring_donation_id':

                if($this->payment_type !== 'rebill') {
                    break;
                }

                $init_recurring_donation_id = $this->get_meta('init_recurring_donation_id');

                $value = $init_recurring_donation_id && $init_recurring_donation_id != $this->id ?
                    $init_recurring_donation_id : $this->id;
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

                $value = false;
                if( !$this->is_init_recurring_donation ) {
                    break;
                }

                if($this->get_meta('recurring_funded_rebills_number') === false) { // The rebills cache is empty
                    $this->update_recurring_funded_rebills_number(); // ... so recalculate the funded rebills number
                }

                $value = absint($this->_donation_meta['recurring_funded_rebills_number']);
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

                $value = $init_recurring_donation ? $init_recurring_donation->get_meta('recurring_active') : NULL;
                break;

            case 'recurring_canceled':
            case 'recurrents_canceled':
                $value = !$this->recurring_active;
                break;

            case 'recurring_cancel_date':
            case 'recurrents_cancel_date':
                $value = $this->payment_type === 'rebill' ? $this->get_meta('recurring_cancel_date') : NULL;
                break;

            case 'cancel_recurring_requested':
            case 'cancelling_recurring_requested':
            case 'recurring_cancel_requested':
            case 'recurring_cancelling_requested':
                $value = $this->payment_type === 'rebill' ? $this->get_meta('cancel_recurring_requested') : false;
                break;

            case 'recurring_cancel_reason':
            case 'recurring_cancelling_reason':
                $value = $this->payment_type === 'rebill' ? $this->get_meta('recurring_cancel_reason') : false;
                break;

            case 'ga_client_id':
            case 'gua_client_id':
                $value = $this->get_meta('ga_client_id');
                break;

            default:
                $value = apply_filters('leyka_get_unknown_donation_field', null, $field, $this);
                $value = apply_filters('leyka_'.$this->gateway_id.'_get_unknown_donation_field', $value, $field, $this);
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
                return $this->set_meta('payment_title', $value);

            case 'status':
            case 'donation_status':

                $old_status = $this->status;
                if(
                    !array_key_exists($value, leyka_get_donation_status_list())
                    || $old_status === $value
                    || !$this->_set_data('status', $value)
                ) {
                    return false;
                }

                Leyka_Donation_Management::get_instance()->donation_status_changed($value, $old_status, $this);
                do_action('leyka_donation_status_'.$old_status.'_to_'.$value, $this);

                if($value === 'funded' || $old_status === 'funded') {
                    do_action('leyka_donation_funded_status_changed', $this->id, $old_status, $value);
                }

                $status_log = $this->get_meta('_status_log');
                if($status_log && is_array($status_log)) {
                    $status_log[] = ['date' => current_time('timestamp'), 'status' => $value];
                } else {
                    $status_log = [['date' => current_time('timestamp'), 'status' => $value]];
                }

                return $this->set_meta('_status_log', $status_log);

            case 'error_id':
            case 'payment_error_id':
            case 'gateway_error_id':
            case 'donation_error_id':

                if($this->status !== 'failed') {
                    $this->status = 'failed';
                }

                $this->set_meta('error_id', $value);
                break;

            case 'date':
                return $this->_set_data('date_created', $value);
            case 'date_timestamp':
                return $this->_set_data('date_created', date('Y-m-d H:i:s', (int)$value));

            case 'donor_name':
            case 'donor_email':
                return $this->_set_data($field, $value);
            case 'donor_comment':
                return $this->_set_data($field, sanitize_textarea_field($value));

            case 'additional_fields':
            case 'donor_additional_fields':
            case 'donation_additional_fields':
                if($value == $this->additional_fields || !is_array($value)) {
                    return false;
                }
                array_walk($value, function( &$value ){ $value = trim($value); });
                return $this->set_meta('additional_fields', $value);

            case 'donor_email_date':
                $this->set_meta('donor_email_date', absint($value));
                break;

            case 'donor_id':
            case 'donor_user_id':
            case 'donor_account_id':
                return $this->_set_data('donor_user_id', absint($value));

            case 'donor_account':
                if(is_wp_error($value)) {
                    return $this->set_meta('donor_account_error', $value);
                } else {
                    return $this->donor_user_id = absint($value);
                }

            case 'sum':
            case 'amount':
            case 'donation_amount':
                do_action('leyka_donation_amount_changed', $this->_id, $value);
                return $this->_set_data('amount', round($value, 2));

            case 'sum_total':
            case 'amount_total':
            case 'total_sum':
            case 'total_amount':
            case 'donation_amount_total':
                do_action('leyka_donation_total_amount_changed', $this->_id, $value);
                return $this->_set_data('amount_total', round($value, 2));

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':
                return $this->_set_data('amount_in_main_currency', (float)$value);

            case 'currency':
            case 'currency_id':
            case 'donation_currency':
            case 'donation_currency_id':
                return $this->currency_id !== $value
                    && leyka_get_currencies_data($value)
                    && $this->_set_data('currency_id', $value);

            case 'gw_id':
            case 'gateway_id':
                if($value && ($this->gateway_id === $value || !leyka_get_gateway_by_id($value))) {
                    return false;
                }
                return $this->_set_data('gateway_id', $value);

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':

                if($this->pm_id === $value) { // Don't check for leyka_get_pm_by_id() here, as pm_id may be custom payment info
                    return false;
                }

                do_action('leyka_donation_pm_changed', $this->_id, $value, $this->gateway_id);

                return $this->_set_data('pm_id', $value);

            case 'type':
            case 'payment_type':

                if($this->payment_type === $value || !leyka_get_payment_types_list($value)) {
                    return false;
                }

                $old_value = $this->payment_type;

                if($this->_set_data('payment_type', $value) && ($old_value === 'rebill' || $value === 'rebill')) {

                    if ( !$this->is_init_recurring_donation ) {
                        $this->update_recurring_funded_rebills_number($old_value === 'rebill' ? 'remove' : 'add');
                    }

                    if($value === 'rebill') {
                        $this->set_meta('init_recurring_donation_id', 0);
                    } else {
                        $this->delete_meta('init_recurring_donation_id');
                    }

                }
                return true;

            case 'campaign':
            case 'campaign_id':

                $value = absint($value);
                if($this->campaign_id === $value) {
                    return false;
                }

                do_action('leyka_donation_campaign_changed', $this->_id, $value);
                return $value && $this->_set_data('campaign_id', $value);

            case 'is_subscribed':
            case 'donor_subscribed':

                $value = !!$value;
                if($this->donor_subscribed === $value) {
                    return false;
                }

                return $this->set_meta('donor_subscribed', !!$value);

            case 'subscription_email':
            case 'donor_subscription_email':

                if($this->donor_subscription_email === $value) {
                    return false;
                }

                return $this->set_meta('donor_subscription_email', leyka_validate_email($value) ? $value : $this->donor_email);

            case 'init_recurring_donation_id':

                $value = absint($value);
                if($this->init_recurring_donation_id === $value || $this->payment_type !== 'rebill') {
                    return false;
                }

                return $this->set_meta('init_recurring_donation_id', $value);

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

                    $init_recurring_donation->set_meta('recurring_active', $value);
                    $this->_donation_meta['recurring_active'] = $value;

                    do_action('leyka_donation_recurring_activity_changed', $this->_id, $value);

                }

                $curr_time = current_time('timestamp');
                return
                    $init_recurring_donation->set_meta('recurring_cancel_date', $value ? 0 : $curr_time)
                    && $this->set_meta('recurring_cancel_date', $value ? 0 : $curr_time);

            case 'cancel_recurring_requested':
            case 'cancelling_recurring_requested':
            case 'recurring_cancel_requested':
            case 'recurring_cancelling_requested':
                $this->set_meta('cancel_recurring_requested', !!$value);
                break;
            case 'cancel_recurring_reason':
                $this->set_meta('recurring_cancel_reason', trim($value));
                break;

            case 'ga_client_id':
            case 'gua_client_id':
                $this->set_meta('ga_client_id', trim($value));
                break;

            default:
                do_action('leyka_set_unknown_donation_field', $field, $value, $this);
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function get_meta($meta_key) {

        $meta_key = trim($meta_key);
        if( !$meta_key ) {
            return NULL;
        }

        if( !array_key_exists($meta_key, $this->_donation_meta) ) { // May be NULL, so we can't use isset() or empty() here

            global $wpdb;

            $query = $wpdb->prepare("SELECT `meta_value` FROM `{$wpdb->prefix}leyka_donations_meta` WHERE `donation_id`=%d AND `meta_key`=%s LIMIT 0,1", $this->_id, $meta_key);
            $result = $wpdb->get_var($query);

            $this->_donation_meta[$meta_key] = maybe_unserialize($result);

        }

        return $this->_donation_meta[$meta_key];

    }

    public function set_meta($meta_name, $value) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) {
            return false;
        }

        $value_serialized = false;
        if(is_array($value) || is_object($value)) {

            $value_serialized = true;
            $value = serialize($value);

        } else {
            $value = trim($value);
        }

        global $wpdb;

        if( // Meta already exists, update it
            isset($this->_donation_meta[$meta_name])
            || $wpdb->get_var($wpdb->prepare("SELECT `meta_id` FROM `{$wpdb->prefix}leyka_donations_meta` WHERE `donation_id`=%d AND `meta_key`=%s LIMIT 0,1", $this->_id, $meta_name))
        ) {

            $res = $wpdb->update(
                $wpdb->prefix.'leyka_donations_meta',
                ['meta_value' => $value],
                ['donation_id' => $this->_id, 'meta_key' => $meta_name],
                ['%s'],
                ['%d', '%s']
            );

        } else { // Meta is not inserted yet

            $res = $wpdb->insert(
                $wpdb->prefix.'leyka_donations_meta',
                ['donation_id' => $this->_id, 'meta_key' => $meta_name, 'meta_value' => $value],
                ['%d', '%s', '%s']
            );

        }

        if($res) {
            $this->_donation_meta[$meta_name] = $value_serialized ? maybe_unserialize($value) : $value;
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

        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix.'leyka_donations_meta',
            ['donation_id' => $this->_id, 'meta_key' => $meta_name],
            ['%d', '%s']
        ) !== false;

    }

    protected function _set_data($data_name, $value) {

        $data_name = trim($data_name);
        if( !$data_name ) {
            return false;
        }

        $value = is_array($value) || is_object($value) ? serialize($value) : trim($value);

        if( !property_exists($this->_main_data, $data_name) ) {
            /** @todo Throw some Ex? */
        } else {

            if($this->_main_data->$data_name != $value) {

                global $wpdb;
                $res = $wpdb->update(
                    $wpdb->prefix.'leyka_donations',
                    [$data_name => $value],
                    ['ID' => $this->_id],
                    ['%s'],
                    ['%d']
                );

                if($res) {
                    $this->_main_data->$data_name = $value;
                } else {
                    return false;
                }

            }

        }

        return true;

    }

    public function add_gateway_response($response) {
        return $this->set_meta('gateway_response', $response);
    }

    public function get_funded_date() {
        return $this->get_meta('date_funded');
    }

    public function delete($force = false) {

        if( !$this->_id ) {
            return false;
        }

        Leyka_Donation_Management::get_instance()->donation_status_changed('trash', $this->status, $this);

        global $wpdb;

//        if( !!$force ) {
        $res = !(
            $wpdb->delete($wpdb->prefix.'leyka_donations_meta', ['donation_id' => $this->_id], ['%d']) === false
            || $wpdb->delete($wpdb->prefix.'leyka_donations', ['ID' => $this->_id], ['%d']) === false
        );
//        } else { } /** @todo Implement $force == false */

        return $res;

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

            $rebills_number = $this->get_meta('recurring_funded_rebills_number');
            $rebills_number = $rebills_number ? absint($rebills_number) + 1 : 1;

        } else if(in_array($action, ['remove', '-'])) {

            $rebills_number = $this->get_meta('recurring_funded_rebills_number');
            $rebills_number = $rebills_number > 0 ? $rebills_number - 1 : 0;

        } else { // Total recalculation

            $rebills_number = Leyka_Donations::get_instance()->get_count([
                'status' => 'funded',
                'recurring_rebills_of' => $this->id,
            ]);

        }

        return $init_recurring_donation->set_meta('recurring_funded_rebills_number', $rebills_number);

    }


    //TODO Vyacheslav - add code to function
    public function update_recurring_subscription_status() {

    }

    //TODO Vyacheslav - add code to function
    public function update_next_recurring_date() {

    }

}