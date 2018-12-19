<?php if( !defined('WPINC') ) die;

/** Default donation class - WP_Post-based */
class Leyka_Donation_Post extends Leyka_Donation_Base {

    public static function add(array $params = array()) {

        $amount = empty($params['amount']) ? leyka_pf_get_amount_value() : round((float)$params['amount'], 2);
        if( !$amount ) {
            return new WP_Error('incorrect_amount_given', __('Empty or incorrect amount given while trying to add a donation', 'leyka'));
        }

        $status = empty($params['status']) ? 'submitted' : $params['status'];

        $id = wp_insert_post(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => array_key_exists($status, leyka_get_donation_status_list()) ? $status : 'submitted',
            'post_title' => empty($params['purpose_text']) ?
                leyka_options()->opt('donation_purpose_text') : $params['purpose_text'],
            'post_parent' => empty($params['init_recurring_donation']) ? 0 : (int)$params['init_recurring_donation'],
        ));

        add_post_meta($id, 'leyka_donation_amount', (float)$amount);

        $value = empty($params['donor_name']) ? leyka_pf_get_donor_name_value() : trim($params['donor_name']);
        if($value && !leyka_validate_donor_name($value)) { // Validate donor's name

            wp_delete_post($id, true);
            return new WP_Error('incorrect_donor_name', __('Incorrect donor name given while trying to add a donation', 'leyka'));

        } else if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $value = apply_filters('leyka_donor_name_email_given', __('Anonymous', 'leyka'));
        }

        add_post_meta($id, 'leyka_donor_name', htmlentities($value, ENT_QUOTES, 'UTF-8'));

        $value = empty($params['donor_email']) ? leyka_pf_get_donor_email_value() : $params['donor_email'];
        if($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {

            wp_delete_post($id, true);
            return new WP_Error('incorrect_donor_email', __('Incorrect donor email given while trying to add a donation', 'leyka'));
        }
        add_post_meta($id, 'leyka_donor_email', $value);

        $value = empty($params['donor_comment']) ? leyka_pf_get_donor_comment_value() : $params['donor_comment'];
        if($value) {
            add_post_meta($id, 'leyka_donor_comment', sanitize_textarea_field($value));
        }

        $pm_data = leyka_pf_get_payment_method_value();
        $pm_data = $pm_data ?
            $pm_data :
            array(
                'payment_method_id' => empty($params['payment_method_id']) ? '' : $params['payment_method_id'],
                'gateway_id' => empty($params['gateway_id']) ? '' : $params['gateway_id'],
            );
        add_post_meta(
            $id, 'leyka_payment_method',
            empty($params['payment_method_id']) ? $pm_data['payment_method_id'] : $params['payment_method_id']
        );
        add_post_meta(
            $id, 'leyka_gateway',
            empty($params['gateway_id']) ? $pm_data['gateway_id'] : $params['gateway_id']
        );

        if(
            (empty($params['amount_total']) || $params['amount_total'] == 'auto') &&
            ( !empty($pm_data['payment_method_id']) && !empty($pm_data['gateway_id']) )
        ) {
            add_post_meta($id, 'leyka_donation_amount_total', leyka_calculate_donation_total_amount(false, $amount, "{$pm_data['gateway_id']}-{$pm_data['payment_method_id']}"));
        }

        $currency = empty($params['currency']) ? leyka_pf_get_currency_value() : strtolower($params['currency']);
        if( !$currency || !array_key_exists($currency, leyka_get_currencies_data()) ) {
            $currency = 'rur';
        }
        add_post_meta($id, 'leyka_donation_currency', $currency);

        $currency_rate = $currency == 'RUR' ? 1.0 : leyka_options()->opt('currency_rur2'.mb_strtolower($currency));
        if( !$currency_rate || (float)$currency_rate <= 0.0 ) {
            $currency_rate = 1.0;
        }

        add_post_meta($id, 'leyka_main_curr_amount', $currency == 'RUR' ? $amount : $amount*$currency_rate);

        add_post_meta(
            $id, 'leyka_campaign_id',
            empty($params['campaign_id']) ? leyka_pf_get_campaign_id_value() : $params['campaign_id']
        );

        if( !get_post_meta($id, '_leyka_donor_email_date', true) ) {
            add_post_meta($id, '_leyka_donor_email_date', 0);
        }
        if( !get_post_meta($id, '_leyka_managers_emails_date', true) ) {
            add_post_meta($id, '_leyka_managers_emails_date', 0);
        }

        add_post_meta($id, '_status_log', array(array('date' => current_time('timestamp'), 'status' => $status)));

        $params['payment_type'] = empty($params['payment_type']) || $params['payment_type'] == 'single' ?
            'single' :
            ($params['payment_type'] == 'rebill' ? 'rebill' : 'correction');
        add_post_meta($id, 'leyka_payment_type', $params['payment_type']);

        if( !empty($params['gateway_id']) ) {
            do_action("leyka_{$params['gateway_id']}_add_donation_specific_data", $id, $params);
        }

        if($params['payment_type'] == 'rebill' && empty($params['init_recurring_donation'])) {
            if(
                !empty($params['rebilling_is_active']) ||
                !empty($params['rebilling_on']) ||
                !empty($params['recurring_is_active']) ||
                !empty($params['recurring_on'])
            ) {
                add_post_meta($id, '_rebilling_is_active', true);
            }
        }

        if( !empty($params['recurrents_cancelled']) ) {
            add_post_meta($id, 'leyka_recurrents_cancelled', $params['recurrents_cancelled']);
        }

        if( !empty($params['recurrents_cancel_date']) ) {
            add_post_meta($id, 'leyka_recurrents_cancel_date', $params['recurrents_cancel_date']);
        } elseif( !empty($params['recurrents_cancelled']) && $params['recurrents_cancelled']) {
            add_post_meta($id, 'leyka_recurrents_cancel_date', current_time('timestamp'));
        } else {
            add_post_meta($id, 'leyka_recurrents_cancel_date', 0);
        }

        return $id;

    }

    public function __construct($donation) {

        if((is_int($donation) || is_string($donation)) && (int)$donation > 0) {

            $donation = (int)$donation;
            $this->_main_data = get_post($donation);

            if( !$this->_main_data || $this->_main_data->post_type !== Leyka_Donation_Management::$post_type ) {
                return false;
            }

            $this->_id = $donation;

        } elseif(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            $this->_id = $donation->ID;
            $this->_main_data = $donation;

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
                'status_log' => empty($meta['_status_log']) ? '' : maybe_unserialize($meta['_status_log'][0]),
                'gateway_response' => empty($meta['leyka_gateway_response']) ? '' : $meta['leyka_gateway_response'][0],

                'recurrents_cancelled' => isset($meta['leyka_recurrents_cancelled']) ?
                    $meta['leyka_recurrents_cancelled'][0] : false,
                'recurrents_cancel_date' => isset($meta['leyka_recurrents_cancel_date']) ?
                    $meta['leyka_recurrents_cancel_date'][0] : false,

                // For active schemes of recurring donations:
                'rebilling_is_active' => !empty($meta['_rebilling_is_active'][0]),
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
            case 'ID': return $this->_id;
            case 'title':
            case 'name': return $this->_main_data->post_title;
            case 'purpose':
            case 'purpose_text':
            case 'payment_title':
            case 'campaign_payment_title':
                return $this->_donation_meta['payment_title'];
            case 'status': return $this->_main_data->post_status;
            case 'status_label':
                $stati = leyka_get_donation_status_list();
                return $stati[$this->_main_data->post_status];
            case 'status_log':
                return $this->_donation_meta['status_log'];
            case 'date':
            case 'date_label':
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $donation_timestamp = $this->date_timestamp;

                return apply_filters(
                    'leyka_admin_donation_date',
                    date("$date_format, $time_format", $donation_timestamp),
                    $donation_timestamp, $date_format, $time_format
                );
            case 'date_timestamp': return strtotime($this->_main_data->post_date);
            case 'date_funded':
            case 'funded_date':
                $date_funded = $this->get_funded_date();
                return $date_funded ? date(get_option('date_format'), $date_funded) : 0;
            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                return $this->_donation_meta['payment_method'];
            case 'pm_full_id':
                return empty($this->_donation_meta['gateway']) || empty($this->_donation_meta['payment_method']) ?
                    '' : $this->_donation_meta['gateway'].'-'.$this->_donation_meta['payment_method'];
            case 'gateway':
            case 'gateway_id':
            case 'gw_id':
                return empty($this->_donation_meta['gateway']) ? '' : $this->_donation_meta['gateway'];
            case 'gateway_label':

                if(empty($this->_donation_meta['gateway'])) {
                    return __('Unknown gateway', 'leyka');
                }

                $gateway = leyka_get_gateway_by_id($this->_donation_meta['gateway']);
                return $gateway ? $gateway->label : __('Unknown gateway', 'leyka');

            case 'pm_label':
            case 'payment_method_label':
                $pm = leyka_get_pm_by_id($this->_donation_meta['payment_method']);
                return ($pm ? $pm->label : __('Unknown payment method', 'leyka'));
            case 'currency':
            case 'currency_code':
            case 'currency_id':
                return $this->_donation_meta['currency'];
            case 'currency_label':
                return leyka_options()->opt('leyka_currency_'.$this->_donation_meta['currency'].'_label');

            case 'sum':
            case 'amount':
                return empty($this->_donation_meta['amount']) ? 0.0 : $this->_donation_meta['amount'];
            case 'sum_total':
            case 'total_sum':
            case 'total_amount':
            case 'amount_total':
                return empty($this->_donation_meta['amount_total']) ? $this->amount : $this->_donation_meta['amount_total'];

            case 'main_curr_amount':
            case 'amount_equiv':
                return $this->_donation_meta['main_curr_amount'];

            case 'donor_name':
                return $this->_donation_meta['donor_name'];
            case 'donor_email':
                return $this->_donation_meta['donor_email'];
            case 'donor_email_date':
                return $this->_donation_meta['donor_email_date'];
            case 'donor_comment':
                return empty($this->_donation_meta['donor_comment']) ? '' : $this->_donation_meta['donor_comment'];
            case 'managers_emails_date':
                return $this->_donation_meta['managers_emails_date'];
            case 'campaign_id':
                return $this->_donation_meta['campaign_id'];

            case 'donor_subscribed':
                return $this->_donation_meta['donor_subscribed'];
            case 'donor_subscription_email':
                return $this->_donation_meta['donor_subscription_email'] ?
                    $this->_donation_meta['donor_subscription_email'] :
                    ($this->_donation_meta['donor_email'] ? $this->_donation_meta['donor_email'] : '');

            case 'gateway_response':
                return $this->_donation_meta['gateway_response'];
            case 'gateway_response_formatted':
                return $this->gateway ?
                    leyka_get_gateway_by_id($this->gateway)->get_gateway_response_formatted($this) : array();

            case 'type':
            case 'payment_type': return $this->_donation_meta['payment_type'];
            case 'type_label':
            case 'payment_type_label': return __($this->_donation_meta['payment_type'], 'leyka');

            case 'init_recurring_payment_id':
            case 'init_recurring_donation_id':
                return $this->payment_type == 'rebill' ?
                    ($this->_main_data->post_parent ? $this->_main_data->post_parent : $this->_id) : false;
            case 'init_recurring_payment':
            case 'init_recurring_donation':
                if($this->payment_type != 'rebill') {
                    return false;
                } else if($this->_main_data->post_parent) {
                    return new Leyka_Donation($this->_main_data->post_parent);
                } else {
                    return $this;
                }
            case 'recurring_subscription_is_active': // ATM, the attribute is only for active recurring scheme gateways
            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'rebilling_is_active':
            case 'recurring_is_active': $tmp = $this->payment_type == 'rebill' ?
                !empty($this->_donation_meta['rebilling_is_active']) : NULL;
                return $tmp;
            case 'recurrents_cancelled':
            case 'recurring_cancelled':
                /** @todo Implement this case */
            case 'recurrents_cancel_date':
            case 'recurring_cancel_date': $tmp = $this->payment_type == 'rebill' ?
                !empty($this->_donation_meta['recurrents_cancel_date']) : NULL;
                return $tmp;
            default:
                return apply_filters('leyka_'.$this->gateway_id.'_get_unknown_donation_field', null, $field, $this);
        }

    }

    public function __set($field, $value) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {
            case 'title':
            case 'payment_title':
            case 'purpose_text':
                if($value != $this->_main_data->post_title) {
                    wp_update_post(array('ID' => $this->_id, 'post_title' => $value));
                    $this->_main_data->post_title = $value;
                }
                break;

            case 'status':
                if( !array_key_exists($value, leyka_get_donation_status_list()) || $value == $this->status ) {
                    return false;
                }

                wp_update_post(array('ID' => $this->_id, 'post_status' => $value));
                $this->_main_data->post_status = $value;

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
                wp_update_post(array('ID' => $this->_id, 'post_date' => $value));
                break;
            case 'date_timestamp':
                wp_update_post(array('ID' => $this->_id, 'post_date' => date('Y-m-d H:i:s', $value)));
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

            case 'sum':
            case 'amount':
            case 'donation_amount':
                update_post_meta($this->_id, 'leyka_donation_amount', $value);
                $this->_donation_meta['amount'] = $value;
                break;

            case 'sum_total':
            case 'amount_total':
            case 'total_sum':
            case 'total_amount':
            case 'donation_amount_total':
                update_post_meta($this->_id, 'leyka_donation_amount_total', $value);
                $this->_donation_meta['amount_total'] = $value;
                break;

            case 'currency':
            case 'donation_currency':
                update_post_meta($this->_id, 'leyka_donation_currency', $value);
                $this->_donation_meta['currency'] = $value;
                break;

            case 'gw_id':
            case 'gateway_id':
                update_post_meta($this->_id, 'leyka_gateway', $value);
                $this->_donation_meta['gateway'] = $value;
                break;

            case 'pm':
            case 'pm_id':
            case 'payment_method_id':
                update_post_meta($this->_id, 'leyka_payment_method', $value);
                $this->_donation_meta['payment_method'] = $value;
                break;

            case 'type':
            case 'payment_type':
                $value = in_array($value, array_keys(leyka_get_payment_types_list())) ? $value : 'single';
                update_post_meta($this->_id, 'leyka_payment_type', $value);
                $this->_donation_meta['payment_type'] = $value;
                break;

            case 'campaign':
            case 'campaign_id':
                $value = (int)$value > 0 ? (int)$value : $this->campaign_id;
                update_post_meta($this->_id, 'leyka_campaign_id', $value);
                $this->_donation_meta['campaign_id'] = $value;
                break;

            case 'is_subscribed':
            case 'donor_subscribed':
                $value = $value === true || (int)$value > 0 ? $value : false;
                update_post_meta($this->_id, 'leyka_donor_subscribed', $value);
                $this->_donation_meta['donor_subscribed'] = $value;
                break;

            case 'subscription_email':
            case 'donor_subscription_email':
                $value = leyka_validate_email($value) ? $value : $this->donor_email;
                update_post_meta($this->_id, 'leyka_donor_subscription_email', $value);
                $this->_donation_meta['donor_subscription_email'] = $value;
                break;

            case 'init_recurring_payment':
            case 'init_recurring_payment_id':
            case 'init_recurring_donation':
            case 'init_recurring_donation_id':
                $value = (int)$value;
                if($value > 0 && $value != $this->_main_data->post_parent) {
                    wp_update_post(array('ID' => $this->_id, 'post_parent' => $value));
                    $this->_main_data->post_parent = $value;
                }
                break;

            case 'rebilling_on':
            case 'rebilling_is_on':
            case 'recurring_on':
            case 'recurring_is_on':
            case 'rebilling_is_active':
            case 'recurring_is_active':
                $value = !!$value;
                if($this->type !== 'rebill') {
                    break;
                }

                $init_recurring_donation = $this->init_recurring_donation;
                if($init_recurring_donation->recurring_is_active != $value) {

                    update_post_meta($init_recurring_donation->id, '_rebilling_is_active', $value);
                    $this->_donation_meta['rebilling_is_active'] = $value;

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

            default:
                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
        }

        return true;

    }

    public function add_gateway_response($resp_text) {

        $this->_donation_meta['gateway_response'] = $resp_text;

        update_post_meta($this->_id, 'leyka_gateway_response', $this->_donation_meta['gateway_response']);

    }

    public function get_funded_date() {

        $last_date_funded = 0;

        foreach((array)$this->status_log as $status_change) {
            if($status_change['status'] == 'funded' && $status_change['date'] > $last_date_funded) {
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