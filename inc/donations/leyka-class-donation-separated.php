<?php if( !defined('WPINC') ) die;

/** Separately stored donation class - the donation data is kept in the separated DB tables */
class Leyka_Donation_Separated extends Leyka_Donation_Base {

    public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && (int)$donation > 0) {
            $this->_id = (int)$donation;
        } else if(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            $this->_id = $donation->ID;

        } else {
            return false; /** @todo Throw an Ex? */
        }

        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}leyka_donations` WHERE `ID`=%d LIMIT 0,1", $this->_id);
        $this->_post_object = $wpdb->get_row($query);

        if( !$this->_post_object ) {
            return false;
        }

        return $this;

    }

    protected function _getMeta($meta_name) {

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

    protected function _setMeta($meta_name, $value) {

        $meta_name = trim($meta_name);
        if( !$meta_name ) { /** @todo Throw an Ex? */
            return;
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

            return true;

        } else {
            return false;
        }

    }

    protected function _setData($data_name, $value) {

        $data_name = trim($data_name);
        if( !$data_name ) { /** @todo Throw an Ex? */
            return NULL;
        }

        $value = is_array($value) || is_object($value) ? serialize($value) : trim($value);

        if( !isset($this->_post_object->$data_name) ) {
            /** @todo Throw some Ex? */
        } else {

            if($this->_post_object->$data_name != $value) {

                global $wpdb;
                $res = $wpdb->update(
                    $wpdb->prefix.'leyka_donations',
                    array($data_name => $value),
                    array('ID' => $this->_id),
                    array('%s'),
                    array('%d')
                );

                if($res) {
                    $this->_post_object->$data_name = $value;
                } else {
                    return false;
                }

            }

        }

        return true;

    }

    public static function add(array $params = array()) {

        $params['campaign_id'] = empty($params['campaign_id']) ? leyka_pf_get_campaign_id_value() : (int)$params['campaign_id'];

        $params['status'] = empty($params['status']) || !array_key_exists($params['status'], leyka_get_donation_status_list()) ?
            'submitted' : $params['status'];

        $params['payment_type'] =
            empty($params['payment_type']) || !array_key_exists($params['payment_type'], leyka_get_payment_types_list()) ?
                'single' : $params['payment_type'];

        $params['date_created'] = empty($params['date_created']) ? date('Y-m-d H:i:s') : $params['date_created'];

        $pm_data = leyka_pf_get_payment_method_value();
        if( !$pm_data ) {

            $params['gateway_id'] = empty($pm_data['gateway_id']) ?
                (empty($params['gateway_id']) ? '' : $params['gateway_id']) :
                $pm_data['gateway_id'];
            if($params['gateway_id'] && !leyka_get_gateway_by_id($params['gateway_id'])) {
                $params['gateway_id'] = 'correction';
            }

            $params['pm_id'] = empty($pm_data['payment_method_id']) ?
                (empty($params['pm_id']) ?
                    (empty($params['payment_method_id']) ? '' : $params['payment_method_id']) :
                    $params['pm_id']) :
                $pm_data['payment_method_id'];
            if($pm_data['pm_id'] && !leyka_get_pm_by_id($params['pm_id']) && $params['gateway_id'] !== 'correction') {
                /** @todo Throw an Ex? */
            }

        }

        $params['currency_id'] = trim(mb_strtolower($params['currency_id']));
        $params['currency_id'] = empty($params['currency_id']) || !leyka_get_currencies_data() ?
            'RUB' : mb_strtoupper($params['currency_id']);

        $params['amount'] = empty($params['amount']) ? leyka_pf_get_amount_value() : round((float)$params['amount'], 2);
        if( !$params['amount'] ) {
            return new WP_Error('incorrect_amount_given', __('Empty or incorrect amount given while trying to add a donation', 'leyka'));
        }

        $params['amount_total'] = empty($params['amount_total']) ? $params['amount'] : round((float)$params['amount_total'], 2);
        $params['amount_in_main_currency'] = empty($params['amount_in_main_currency']) ? $params['amount'] : round((float)$params['amount_in_main_currency'], 2);
        $params['amount_total_in_main_currency'] = empty($params['amount_total_in_main_currency']) ? $params['amount_total'] : round((float)$params['amount_total_in_main_currency'], 2);

        $params['donor_name'] = empty($params['donor_name']) ? leyka_pf_get_donor_name_value() : trim($params['donor_name']);
        if($params['donor_name'] && !leyka_validate_donor_name($params['donor_name'])) {
            return new WP_Error('incorrect_donor_name', __('Incorrect donor name given while trying to add a donation', 'leyka'));
        } else if(is_email($params['donor_name'])) {
            $params['donor_name'] = apply_filters('leyka_donor_name_email_given', __('Anonymous', 'leyka'));
        }
        $params['donor_name'] = htmlentities($params['donor_name'], ENT_QUOTES, 'UTF-8');


        $params['donor_email'] = empty($params['donor_email']) ? leyka_pf_get_donor_email_value() : $params['donor_email'];
        if($params['donor_email'] && !is_email($params['donor_email'])) {
            return new WP_Error('incorrect_donor_email', __('Incorrect donor email given while trying to add a donation', 'leyka'));
        }

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
            return new WP_Error('donation_addition_error', __('Error while trying to add a new donation', 'leyka'));
        } else {
            $donation_id = $wpdb->insert_id;
        }

        if( !empty($params['gateway_id']) ) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $donation_id, $params);
        }

        $donation_meta_fields = array();

        $params['donor_comment'] = empty($params['donor_comment']) ?
            leyka_pf_get_donor_comment_value() : $params['donor_comment'];
        if($params['donor_comment']) {
            $donation_meta_fields['donor_comment'] = sanitize_textarea_field($params['donor_comment']);
        }

        $params['init_recurring_donation'] = empty($params['init_recurring_donation']) ?
            false : (
            is_int($params['init_recurring_donation']) && $params['init_recurring_donation'] > 0 ?
                (int)$params['init_recurring_donation'] :
                (is_object($params['init_recurring_donation']) ? $params['init_recurring_donation']->ID : false)
            );
        if( !empty($params['init_recurring_donation_id']) && (int)$params['init_recurring_donation_id'] > 0 ) {
            $params['init_recurring_donation'] = (int)$params['init_recurring_donation_id'];
        }
        if($params['init_recurring_donation']) {
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
        $params['recurring_cancelled'] = !empty($params['recurrents_cancelled']) || !empty($params['recurring_cancelled']);
        $donation_meta_fields['recurring_active'] = $params['recurring_cancelled'] ?
            0 : (int)$donation_meta_fields['recurring_active'];

        $params['recurring_cancel_date'] = empty($params['recurring_cancel_date']) ?
            (empty($params['recurrents_cancel_date']) ? 0 : $params['recurrents_cancel_date']) :
            $params['recurring_cancel_date'];

        if($params['payment_type'] === 'rebill' && $params['recurrents_cancel_date']) {
            $donation_meta_fields['recurring_cancel_date'] = $params['recurring_cancel_date'];
        } else if($params['payment_type'] === 'rebill' && empty($params['recurring_cancel_date'])) {
            $donation_meta_fields['recurring_cancel_date'] = current_time('timestamp');
        }

        return $donation_id;

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
            case 'campaign_id':
                return $this->_post_object->campaign_id;
            case 'id':
            case 'ID': return $this->_id;
            case 'title':
            case 'name':
            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                return $this->_getMeta('payment_title');
            case 'status': return $this->_post_object->status;
            case 'status_label':
                $statuses = leyka_get_donation_status_list();
                return $statuses[$this->_post_object->status];
            case 'status_log':
                return $this->_getMeta('status_log');
            case 'date':
            case 'date_label':
                $date_format = get_option('date_format');
                $donation_timestamp = strtotime($this->_post_object->date_created);
                return apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $donation_timestamp),
                    $donation_timestamp, $date_format, get_option('time_format')
                );
            case 'date_timestamp': return strtotime($this->_post_object->date_created);
            case 'date_funded':
            case 'date_funded_label':
            case 'funded_date':
            case 'funded_date_label':
                return $this->_getMeta('date_funded') ? date(get_option('date_format'), $this->_getMeta('date_funded')) : false;
            case 'date_funded_timestamp':
            case 'funded_date_timestamp':
                return $this->date_funded ? strtotime($this->date_funded) : false;
            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                return $this->_post_object->pm_id ? $this->_post_object->pm_id : false;
            case 'pm_full_id':
                return $this->_post_object->gateway_id && $this->_post_object->pm_id ?
                    $this->_post_object->gateway_id.'-'.$this->_post_object->pm_id : '';
            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                return $this->_post_object->gateway_id ? false : $this->_post_object->gateway_id;
            case 'gateway_label':

                if(empty($this->_post_object->gateway_id)) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->_post_object->gateway_id);

                return $gateway ? $gateway->label : __('Unknown gateway', 'leyka');

            case 'pm_label':
            case 'payment_method_label':
                $pm = leyka_get_pm_by_id($this->_post_object->pm_id);

                return ($pm ? $pm->label : __('Unknown payment method', 'leyka'));

            case 'currency':
            case 'currency_code':
            case 'currency_id':
                return $this->_post_object->currency_id;
            case 'currency_label':
                return leyka_get_currency_label($this->_post_object->currency_id);

            case 'sum':
            case 'amount':
                return $this->_post_object->amount ? $this->_post_object->amount : 0.0;
            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':
                return $this->_post_object->amount_total ? $this->_post_object->amount_total : $this->amount;

            case 'main_curr_amount':
            case 'amount_equiv':
                return $this->_post_object->amount_in_main_currency ?
                    $this->_post_object->amount_in_main_currency : $this->amount;

            case 'donor_name':
                return $this->_post_object->donor_name;
            case 'donor_email':
                return $this->_post_object->donor_email;
            case 'donor_email_date':
                return $this->_getMeta('donor_email_date');
            case 'donor_comment':
                return $this->_getMeta('donor_comment');
            case 'managers_emails_date':
                return $this->_getMeta('managers_emails_date');

            case 'is_subscribed':
            case 'donor_subscribed':
                return $this->_getMeta('donor_subscribed');
            case 'subscription_email':
            case 'donor_subscription_email':
                return $this->_getMeta('donor_subscription_email') ?
                    $this->_getMeta('donor_subscription_email') :
                    ($this->_getMeta('donor_email') ? $this->_getMeta('donor_email') : '');

            case 'gateway_response':
                return $this->_getMeta('gateway_response');
            case 'gateway_response_formatted':
                return $this->gateway_id ?
                    leyka_get_gateway_by_id($this->gateway_id)->get_gateway_response_formatted($this) : array();

            case 'type':
            case 'payment_type': return $this->_post_object->payment_type;

            case 'type_label':
            case 'payment_type_label': return __($this->payment_type, 'leyka');

            case 'init_recurring_payment_id':
            case 'init_recurring_donation_id':
                return $this->payment_type === 'rebill' && $this->_getMeta('init_recurring_donation_id') ?
                    $this->_getMeta('init_recurring_donation_id') : false;
            case 'init_recurring_payment':
            case 'init_recurring_donation':
                if($this->payment_type !== 'rebill') {
                    return false;
                } else if($this->init_recurring_donation_id) {
                    return new Leyka_Donation_Separated($this->init_recurring_donation_id);
                } else {
                    return $this;
                }
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

                return $init_recurring_donation ? $init_recurring_donation->_getMeta('recurring_active') : NULL;

            case 'recurrents_cancelled':
            case 'recurring_canceled':
                return !$this->recurring_active;

            case 'recurrents_cancel_date':
            case 'recurring_cancel_date':
                return $this->payment_type === 'rebill' ? $this->_getMeta('recurring_cancel_date') : NULL;
            default: /** @todo WARNING! All gateways methods for this action now should use D-Factory::getDonationData(). */
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
                return $this->_setMeta('payment_title', $value);

            case 'status':
                if(
                    !array_key_exists($value, leyka_get_donation_status_list())
                    || $this->status === $this->status
                    || !$this->_setData('status', $value)
                ) {
                    return false;
                }

                $status_log = $this->_getMeta('status_log');
                if($status_log && is_array($status_log)) {
                    $status_log[] = array('date' => current_time('timestamp'), 'status' => $value);
                } else {
                    $status_log = array(array('date' => current_time('timestamp'), 'status' => $value));
                }

                return $this->_setMeta('status_log', $status_log);

            case 'date':
                return $this->_setData('date_created', $value);
            case 'date_timestamp':
                return $this->_setData('date_created', date('Y-m-d H:i:s', (int)$value));

            case 'donor_name':
            case 'donor_email':
                return $this->_setData($field, $value);

            case 'donor_comment':
                return $this->_setData($field, sanitize_textarea_field($value));

            case 'sum':
            case 'amount':
            case 'donation_amount':
                return $this->_setData('amount', (float)$value);

            case 'sum_total':
            case 'amount_total':
            case 'total_sum':
            case 'total_amount':
            case 'donation_amount_total':
                return $this->_setData('amount_total', (float)$value);

            case 'main_curr_amount':
            case 'amount_equiv':
                return $this->_setData('amount_in_main_currency', (float)$value);

            case 'currency':
            case 'donation_currency':
                return leyka_get_currencies_data($value) ? $this->_setData('currency_id', $value) : false;

            case 'gw_id':
            case 'gateway_id':
                return leyka_get_gateway_by_id($value) ? $this->_setData('gateway_id', $value) : false;

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':
                return leyka_get_pm_by_id($value) ? $this->_setData('pm_id', $value) : false;

            case 'type':
            case 'payment_type':
                return $this->_setData(
                    'payment_type', in_array($value, array_keys(leyka_get_payment_types_list())) ? $value : 'single'
                );

            case 'campaign':
            case 'campaign_id':
                return (int)$value > 0 ? $this->_setData('campaign_id', (int)$value) : false;

            case 'is_subscribed':
            case 'donor_subscribed':
                return $this->_setMeta('donor_subscribed', !!$value);

            case 'subscription_email':
            case 'donor_subscription_email':
                return $this->_setMeta('donor_subscription_email', leyka_validate_email($value) ? $value : $this->donor_email);

            case 'init_recurring_payment':
            case 'init_recurring_payment_id':
            case 'init_recurring_donation':
            case 'init_recurring_donation_id':
                return $this->payment_type === 'rebill' || (int)$value > 0 ?
                    $this->_setMeta('init_recurring_donation_id', (int)$value) : false;

            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'recurring_active':
            case 'rebilling_is_active':
            case 'recurring_is_active':
            case 'recurring_subscription_is_active':
                $value = !!$value;

                $init_recurring_donation = $this->init_recurring_donation;
                if( !$init_recurring_donation ) {
                    return false;
                }

                if($init_recurring_donation->recurring_is_active != $value) {

                    $init_recurring_donation->_setMeta('recurring_active', $value);
                    $this->_donation_meta['recurring_active'] = $value;

                }

                $curr_time = current_time('timestamp');
                $init_recurring_donation->_setMeta('recurring_cancel_date', $value ? 0 : $curr_time);
                $this->_donation_meta['recurring_cancel_date'] = $value ? 0 : $curr_time;

                return true;

            default: /** @todo WARNING! This action methods in gateways now should use the D-Factory method setDonationData(). */
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function add_gateway_response($resp_text) {
        $this->_setMeta('gateway_response', $resp_text);
    }

    public function get_funded_date() {
        return $this->_getMeta('date_funded');
    }

    public function delete($force = false) {

        if( !$this->_id ) {
            return false; /** @todo Throw an Ex? */
        }

        global $wpdb;
        if(
            !$wpdb->delete($wpdb->prefix.'leyka_donations_meta', array('donation_id' => $this->_id), array('%d')) ||
            !$wpdb->delete($wpdb->prefix.'leyka_donations', array('ID' => $this->_id), array('%d'))
        ) {
            return false; /** @todo Throw an Ex? */
        }

        return true;

    }

}