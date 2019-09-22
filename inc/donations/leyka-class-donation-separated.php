<?php if( !defined('WPINC') ) die;

/** Separately stored donation class - the donation data is kept in the separated DB tables */
class Leyka_Donation_Separated extends Leyka_Donation_Base {

    public static function add(array $params = array()) {

        $params['force_insert'] = !empty($params['force_insert']);

        $params['amount'] = empty($params['amount']) ? leyka_pf_get_amount_value() : round((float)$params['amount'], 2);
        $params['amount'] = $params['amount'] ? $params['amount'] : 0.0;
        if( !$params['amount'] && !$params['force_insert'] ) {
            return new WP_Error('incorrect_amount_given', __('Incorrect amount given while adding a donation', 'leyka'));
        }

        $params['status'] = empty($params['status']) || !array_key_exists($params['status'], leyka_get_donation_status_list()) ?
            'submitted' : $params['status'];

        $params['donor_email'] = empty($params['donor_email']) ? leyka_pf_get_donor_email_value() : $params['donor_email'];
        if( !$params['force_insert'] && ( !$params['donor_email'] && !is_email($params['donor_email']) ) ) {
            return new WP_Error('incorrect_donor_email', __('Incorrect donor email given while adding a donation', 'leyka'));
        }

        $params['donor_name'] = empty($params['donor_name']) ? leyka_pf_get_donor_name_value() : $params['donor_name'];
        if($params['donor_name'] && !leyka_validate_donor_name($params['donor_name']) && !$params['force_insert']) {
            return new WP_Error('incorrect_donor_name', __('Incorrect donor name given while adding a donation', 'leyka'));
        } else if(is_email($params['donor_name'])) {
            $params['donor_name'] = apply_filters('leyka_donor_name_email_given', __('Anonymous', 'leyka'));
        }
        $params['donor_name'] = htmlentities($params['donor_name'], ENT_QUOTES, 'UTF-8');

        $params['campaign_id'] = empty($params['campaign_id']) ? leyka_pf_get_campaign_id_value() : (int)$params['campaign_id'];

        $params['payment_type'] =
            empty($params['payment_type']) || !array_key_exists($params['payment_type'], leyka_get_payment_types_data()) ?
                'single' : $params['payment_type'];

        $params['date_created'] = empty($params['date_created']) ? date('Y-m-d H:i:s') : $params['date_created'];

        if(empty($params['pm_id']) && empty($params['gateway_id'])) { // Try to get Gateway & PM from $_POST

            $pm_data = leyka_pf_get_payment_method_value();
            $params['pm_id'] = $pm_data['payment_method_id'];
            $params['gateway_id'] = $pm_data['gateway_id'];

        } else { // Get Gateway & PM data from $params

            $params['gateway_id'] = empty($params['gateway_id']) ? '' : $params['gateway_id'];
            if($params['gateway_id'] && !leyka_get_gateway_by_id($params['gateway_id'])) {
                $params['gateway_id'] = 'correction';
            }

            $params['pm_id'] = empty($params['pm_id']) ?
                (empty($params['payment_method_id']) ? '' : $params['payment_method_id']) :
                $params['pm_id'];

        }

        $pm_full_id = leyka_get_pm_by_id("{$params['gateway_id']}-{$params['pm_id']}", true);

        if( !$params['force_insert'] && (empty($params['gateway_id']) || empty($params['pm_id'])) ) {
            return new WP_Error('donation_addition_error', __('Gateway or PM ID is missing while adding a donation', 'leyka'));
        }

        if(
            !$params['force_insert']
            && $params['gateway_id'] !== 'correction'
            && !leyka_get_pm_by_id($params['gateway_id'].'-'.$params['pm_id'], true)
        ) {
            return new WP_Error('donation_addition_error', __('Incorrect gateway or PM ID given while adding a donation', 'leyka'));
        }

        $params['currency_id'] = empty($params['currency_id']) ?
            (empty($params['currency']) ? '' : mb_strtolower($params['currency'])) : mb_strtolower($params['currency_id']);
        $params['currency_id'] = $params['currency_id'] ? $params['currency_id'] : leyka_pf_get_currency_value();

        $params['currency_id'] = empty($params['currency_id']) || !leyka_get_currencies_data($params['currency_id']) ?
            'RUB' : mb_strtoupper($params['currency_id']);

        $params['amount_total'] = empty($params['amount_total']) ? $params['amount'] : round((float)$params['amount_total'], 2);
        if($pm_full_id && (empty($params['amount_total']) || $params['amount_total'] === 'auto')) {
            $params['amount_total'] = leyka_calculate_donation_total_amount(false, $params['amount'], $pm_full_id);
        }

        $params['amount_in_main_currency'] = empty($params['amount_in_main_currency']) ? $params['amount'] : round((float)$params['amount_in_main_currency'], 2);
        $params['amount_total_in_main_currency'] = empty($params['amount_total_in_main_currency']) ? $params['amount_total'] : round((float)$params['amount_total_in_main_currency'], 2);

        global $wpdb;
        $res = $wpdb->insert($wpdb->prefix.'leyka_donations', array(
            'campaign_id' => $params['campaign_id'],
            'status' => $params['status'],
            'payment_type' => $params['payment_type'],
            'date_created' => $params['date_created'],
            'gateway_id' => $params['gateway_id'],
            'pm_id' => $params['pm_id'],
            'currency_id' => $params['currency_id'],
            'amount' => $params['amount'],
            'amount_total' => $params['amount_total'],
            'amount_in_main_currency' => $params['amount_in_main_currency'],
            'amount_total_in_main_currency' => $params['amount_total_in_main_currency'],
            'donor_name' => $params['donor_name'],
            'donor_email' => $params['donor_email'],
        ), array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s',));

        if( !$res ) {
            return new WP_Error('donation_addition_error', __('Error while adding a donation', 'leyka'));
        } else {
            $donation_id = $wpdb->insert_id;
        }

        if( !empty($params['gateway_id']) ) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $donation_id, $params);
        }

        $donation_meta_fields = array();

        $params['payment_title'] = empty($params['purpose_text']) ?
            (empty($params['payment_title']) ? leyka_options()->opt('donation_purpose_text') : $params['payment_title']) :
            $params['purpose_text'];
        if($params['payment_title']) {
            $donation_meta_fields['payment_title'] = sanitize_title($params['payment_title']);
        }

        /** @todo Add the donor_user_id meta, if Donors management is on */
//        if(leyka_options()->opt('donors_management_available')) { // Use $params['donor_user_id'] }

        $params['donor_comment'] = empty($params['donor_comment']) ?
            leyka_pf_get_donor_comment_value() : $params['donor_comment'];
        if($params['donor_comment']) {
            $donation_meta_fields['donor_comment'] = sanitize_textarea_field($params['donor_comment']);
        }

        $params['init_recurring_donation_id'] = empty($params['init_recurring_donation']) ?
            (empty($params['init_recurring_donation']) ? false : absint($params['init_recurring_donation'])) :
            absint($params['init_recurring_donation_id']);
        if($params['init_recurring_donation_id']) {
            $donation_meta_fields['init_recurring_donation_id'] = $params['init_recurring_donation'];
        }

        $donation_meta_fields['status_log'] = array(array('date' => current_time('timestamp'), 'status' => $params['status']));

        if($params['payment_type'] === 'rebill' && empty($donation_meta_fields['init_recurring_donation_id'])) {
            $donation_meta_fields['recurring_active'] =
                !empty($params['rebilling_is_active']) ||
                !empty($params['rebilling_on']) ||
                !empty($params['recurring_active']) ||
                !empty($params['recurring_is_active']) ||
                !empty($params['recurring_on']);
        }

        if( !empty($params['recurring_cancelled']) ) {

            $params['recurring_cancelled'] = !empty($params['recurring_cancelled']);
            $donation_meta_fields['recurring_active'] = $params['recurring_cancelled'] ?
                0 : (int)!empty($donation_meta_fields['recurring_active']);

        }

        $params['recurring_cancel_date'] = empty($params['recurring_cancel_date']) ? 0 : $params['recurring_cancel_date'];
        if($params['payment_type'] === 'rebill') {
            if($params['recurrents_cancel_date']) {
                $donation_meta_fields['recurring_cancel_date'] = $params['recurring_cancel_date'];
            } else if(empty($params['recurring_cancel_date'])) {
                $donation_meta_fields['recurring_cancel_date'] = current_time('timestamp');
            }
        }

        foreach($donation_meta_fields as $key => $value) {

            $res = $wpdb->insert($wpdb->prefix.'leyka_donations_meta', array(
                'donation_id' => $donation_id,
                'meta_key' => $key,
                'meta_value' => is_object($value) || is_array($value) ? serialize($value) : $value,
            ), array('%d', '%s', '%s',));

            if( !$res ) {

                $wpdb->delete($wpdb->prefix.'leyka_donations', array('ID' => $donation_id), array('%d'));

                return new WP_Error('donation_addition_error', __('Error while adding a donation', 'leyka'));

            }

        }

        return $donation_id;

    }

    public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && (int)$donation > 0) {
            $this->_id = (int)$donation;
        } else if(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            $this->_id = $donation->ID;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            $this->_id = $donation->id;
        } else {
            return false; /** @todo Throw an Ex? */
        }

        global $wpdb;
        $this->_main_data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}leyka_donations` WHERE `ID`=%d LIMIT 0,1", $this->_id)
        );

        if( !$this->_main_data ) {
            return false;
        }

        return $this;

    }

    /**
     * @param $field string
     * @return mixed
     */
    public function __get($field) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {
            case 'id':
            case 'ID':
                return $this->_id;

            case 'campaign_id':
                return $this->_main_data->campaign_id;
            case 'campaign':
                return $this->_main_data->campaign_id ? new Leyka_Campaign($this->_main_data->campaign_id) : false;

            case 'campaign_title':
                $campaign = $this->campaign;
                return $campaign ? $campaign->title : $this->payment_title;

            case 'title':
            case 'name':

            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                return $this->get_meta('payment_title');

            case 'status':
                return $this->_main_data->status;
            case 'status_label':
                return leyka()->get_donation_status_info($this->_main_data->status, 'label');

            case 'status_desc':
            case 'status_description':
                return leyka()->get_donation_status_info($this->_main_data->status, 'description');

            case 'status_log':
                return $this->get_meta('status_log');

            case 'date':
            case 'date_label':
                $date_format = get_option('date_format');
                $donation_timestamp = $this->date_timestamp;

                return apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $donation_timestamp),
                    $donation_timestamp, $date_format
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

            case 'date_timestamp': return strtotime($this->_main_data->date_created);

            case 'date_funded':
            case 'date_funded_label':
            case 'funded_date':
            case 'funded_date_label':
                return $this->get_meta('date_funded') ? date(get_option('date_format'), $this->get_meta('date_funded')) : false;

            case 'date_funded_timestamp':
            case 'funded_date_timestamp':
                return $this->date_funded ? strtotime($this->date_funded) : false;

            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                return $this->_main_data->pm_id ? $this->_main_data->pm_id : false;

            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                return $this->_main_data->gateway_id ? $this->_main_data->gateway_id : false;

            case 'pm_full_id':
                return $this->_main_data->gateway_id && $this->_main_data->pm_id ?
                    $this->_main_data->gateway_id.'-'.$this->_main_data->pm_id : '';

            case 'gateway_label':

                if(empty($this->_main_data->gateway_id)) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->_main_data->gateway_id);

                return $gateway ? $gateway->label : __('Unknown gateway', 'leyka');

            case 'pm_label':
            case 'payment_method_label':
                $pm = leyka_get_pm_by_id($this->_main_data->pm_id);

                return ($pm ? $pm->label : __('Unknown payment method', 'leyka'));

            case 'currency':
            case 'currency_id':
            case 'currency_code':
                return $this->_main_data->currency_id;

            case 'currency_label':
                return leyka_get_currency_label($this->_main_data->currency_id);

            case 'sum':
            case 'amount':
                return $this->_main_data->amount ? $this->_main_data->amount : 0.0;

            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':
                return $this->_main_data->amount_total ? $this->_main_data->amount_total : $this->amount;

            case 'main_curr_amount':
            case 'main_currency_amount':
            case 'amount_equiv':
                return $this->_main_data->amount_in_main_currency ? $this->_main_data->amount_in_main_currency : $this->amount;

            case 'donor_name':
                return $this->_main_data->donor_name;
            case 'donor_email':
                return $this->_main_data->donor_email;
            case 'donor_comment':
                return $this->get_meta('donor_comment');
            case 'donor_email_date':
                return $this->get_meta('donor_email_date');
            case 'managers_emails_date':
                return $this->get_meta('managers_emails_date');

            case 'is_subscribed':
            case 'donor_subscribed':
                return $this->get_meta('donor_subscribed');

            case 'subscription_email':
            case 'donor_subscription_email':
                return $this->get_meta('donor_subscription_email') ?
                    $this->get_meta('donor_subscription_email') :
                    ($this->get_meta('donor_email') ? $this->get_meta('donor_email') : '');

//            case 'donor_user_id':
//            case 'donor_account_id':
//                return isset($this->_main_data->post_author) ? (int)$this->_main_data->post_author : false;
//
//            case 'donor_user_error':
//            case 'donor_account_error':
//                $donor_account_error = isset($this->_donation_meta['donor_account_error']) ?
//                    maybe_unserialize($this->_donation_meta['donor_account_error']) : false;
//                return $donor_account_error && is_wp_error($donor_account_error) ? $donor_account_error : false;

            case 'gateway_response':
                return $this->get_meta('gateway_response');
            case 'gateway_response_formatted':
                return $this->gateway_id ?
                    leyka_get_gateway_by_id($this->gateway_id)->get_gateway_response_formatted($this) : array();

            case 'type':
            case 'payment_type':
                return $this->_main_data->payment_type;

            case 'type_label':
            case 'payment_type_label':
                return leyka_get_payment_types_data($this->payment_type);

            case 'type_desc':
            case 'payment_type_desc':
            case 'type_description':
            case 'payment_type_description':
                return leyka_get_donation_type_description($this->type);

            case 'init_recurring_donation_id':
                return $this->payment_type === 'rebill' && $this->get_meta('init_recurring_donation_id') ?
                    $this->get_meta('init_recurring_donation_id') : false;

            case 'init_recurring_donation':
                if($this->payment_type !== 'rebill') {
                    return false;
                } else if($this->init_recurring_donation_id) {
                    return Leyka_Donations::get_instance()->get_donation($this->init_recurring_donation_id);
                } else {
                    return $this;
                }

            case 'is_init_recurring_donation':
                return $this->init_recurring_payment_id === $this->id;

            case 'cancel_recurring_requested':
            case 'recurring_cancelling_requested':
                return $this->payment_type === 'rebill' ? $this->get_meta('cancel_recurring_requested') : false;

            case 'recurring_active':
            case 'recurring_subscription_is_active':
            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'rebilling_is_active':
            case 'recurring_is_active':
                if($this->payment_type !== 'rebill') {
                    return false;
                }

                $init_recurring_donation = $this->init_recurring_donation;

                return $init_recurring_donation ? $init_recurring_donation->_get_meta('recurring_active') : NULL;

            case 'recurring_canceled':
                return !$this->recurring_active;

            case 'recurrents_cancel_date':
            case 'recurring_cancel_date':
                return $this->payment_type === 'rebill' ? $this->get_meta('recurring_cancel_date') : NULL;

            default: /** @todo WARNING! Gateways methods for this action now should use Leyka_Donations::get_donation_field(). */
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
                return $this->set_meta('payment_title', $value);

            case 'status':
                if(
                    !array_key_exists($value, leyka_get_donation_status_list())
                    || $this->status === $this->status
                    || !$this->_set_data('status', $value)
                ) {
                    return false;
                }

                $status_log = $this->get_meta('status_log');
                if($status_log && is_array($status_log)) {
                    $status_log[] = array('date' => current_time('timestamp'), 'status' => $value);
                } else {
                    $status_log = array(array('date' => current_time('timestamp'), 'status' => $value));
                }

                return $this->set_meta('status_log', $status_log);

            case 'date':
                return $this->_set_data('date_created', $value);
            case 'date_timestamp':
                return $this->_set_data('date_created', date('Y-m-d H:i:s', (int)$value));

            case 'donor_name':
            case 'donor_email':
                return $this->_set_data($field, $value);
            case 'donor_comment':
                return $this->_set_data($field, sanitize_textarea_field($value));

//            case 'donor_user_id':
//            case 'donor_account_id':
//                return $this->_set_meta($field, absint($value));

//            case 'donor_account':
//                if(is_wp_error($value)) {
//                    return $this->_set_meta('donor_account_error', $value);
//                } else if(absint($value)) {
//                    return $this->donor_user_id = absint($value);
//                }
//                break;

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
            case 'amount_equiv':
                return $this->_set_data('amount_in_main_currency', (float)$value);

            case 'currency':
            case 'currency_id':
            case 'donation_currency':
            case 'donation_currency_id':
                return leyka_get_currencies_data($value) ? $this->_set_data('currency_id', $value) : false;

            case 'gw_id':
            case 'gateway_id':
                return leyka_get_gateway_by_id($value) ? $this->_set_data('gateway_id', $value) : false;

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':
                return leyka_get_pm_by_id($value) ? $this->_set_data('pm_id', $value) : false;

            case 'type':
            case 'payment_type':
                if( !leyka_get_payment_types_data($value) ) {
                    return false;
                }
                return $this->_set_data('payment_type', $value);

            case 'campaign':
            case 'campaign_id':
                do_action('leyka_donation_campaign_changed', $this->_id, $value);
                return absint($value) ? $this->_set_data('campaign_id', absint($value)) : false;

            case 'is_subscribed':
            case 'donor_subscribed':
                return $this->set_meta('donor_subscribed', !!$value);

            case 'subscription_email':
            case 'donor_subscription_email':
                return $this->set_meta('donor_subscription_email', leyka_validate_email($value) ? $value : $this->donor_email);

            case 'init_recurring_donation_id':
                return $this->payment_type === 'rebill' || absint($value) ?
                    $this->set_meta('init_recurring_donation_id', absint($value)) : false;

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

                    $init_recurring_donation->_set_meta('recurring_active', $value);
                    $this->_donation_meta['recurring_active'] = $value;

                    do_action('leyka_donation_recurring_activity_changed', $this->_id, true);

                }

                $curr_time = current_time('timestamp');
                return
                    $init_recurring_donation->_set_meta('recurring_cancel_date', $value ? 0 : $curr_time)
                    && $this->set_meta('recurring_cancel_date', $value ? 0 : $curr_time);

            case 'cancel_recurring_requested':
                $this->set_meta('cancel_recurring_requested', !!$value);
                break;

            default: /** @todo WARNING! This action methods in gateways should use the Leyka_Donations::set_donation_data(). */
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function get_meta($meta_name) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) { /** @todo Throw an Ex? */
            return NULL;
        }

        if( !isset($this->_donation_meta[$meta_name]) ) {

            global $wpdb;
            $query = $wpdb->prepare("SELECT `meta_value` FROM `{$wpdb->prefix}leyka_donations_meta` WHERE `donation_id`=%d AND `meta_key`=%s LIMIT 0,1", $this->_id, $meta_name);
            $this->_donation_meta[$meta_name] = maybe_unserialize($wpdb->get_var($query));

        }

        return $this->_donation_meta[$meta_name];

    }

    public function set_meta($meta_name, $value) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) { /** @todo Throw an Ex? */
            return false;
        }

        $value = is_array($value) || is_object($value) ? serialize($value) : trim($value);

        global $wpdb;

        if( // Meta already exists, update it
            isset($this->_donation_meta[$meta_name]) ||
            $wpdb->get_var($wpdb->prepare("SELECT `meta_id` FROM `$wpdb->prefix`leyka_donations_meta WHERE `donation_id`=%d AND `meta_key`=%s LIMIT 0,1", $this->_id, $meta_name))
        ) {

            $res = $wpdb->update(
                $wpdb->prefix.'leyka_donations_meta',
                array('meta_value' => $value),
                array('donation_id' => $this->_id, 'meta_key' => $meta_name),
                array('%s'),
                array('%d', '%s')
            );

        } else { // Meta is not inserted yet

            $res = $wpdb->insert(
                $wpdb->prefix.'leyka_donations_meta',
                array('donation_id' => $this->_id, 'meta_key' => $meta_name, 'meta_value' => $value),
                array('%d', '%s', '%s')
            );

        }

        if($res) {
            $this->_donation_meta[$meta_name] = $value;
        } else {
            return false;
        }

        return true;

    }

    protected function _set_data($data_name, $value) {

        $data_name = trim($data_name);
        if( !$data_name ) { /** @todo Throw an Ex? */
            return false;
        }

        $value = is_array($value) || is_object($value) ? serialize($value) : trim($value);

        if( !isset($this->_main_data->$data_name) ) {
            /** @todo Throw some Ex? */
        } else {

            if($this->_main_data->$data_name != $value) {

                global $wpdb;
                $res = $wpdb->update(
                    $wpdb->prefix.'leyka_donations',
                    array($data_name => $value),
                    array('ID' => $this->_id),
                    array('%s'),
                    array('%d')
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
            return false; /** @todo Throw an Ex? */
        }

        global $wpdb;

//        if( !!$force ) {
        return !(
            $wpdb->delete($wpdb->prefix.'leyka_donations_meta', array('donation_id' => $this->_id), array('%d')) === false
            || $wpdb->delete($wpdb->prefix.'leyka_donations', array('ID' => $this->_id), array('%d')) === false
        );
//        } else { } /** @todo Implement $force == false */

    }

}