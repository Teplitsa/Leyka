<?php if( !defined('WPINC') ) die;

/** * Leyka Donor Classes */

class Leyka_Donor {

	private $_id;

    /** @var WP_User */
	private $_user;

    private $_meta = array();

    public static $user_role = 'donor';

    protected static function _is_donor(WP_User $donor_user) {
        return in_array(static::$user_role, (array)$donor_user->roles);
    }

    public function __construct($donor_user) {

        if(is_int($donor_user) && absint($donor_user) > 0) {

            $donor_user = (int)$donor_user;
            $this->_user = get_user_by('id', $donor_user);

            if( !$this->_user || !self::_is_donor($this->_user) ) {
                return false;
            }

            $this->_id = $this->_user->ID;

        } else if(is_string($donor_user) && $donor_user) {

            $donor_user = esc_sql($donor_user);
            $this->_user = get_user_by('email', $donor_user);

            if( !$this->_user || !self::_is_donor($this->_user) ) {
                return false;
            }

            $this->_id = $this->_user->ID;

        } else if(is_a($donor_user, 'WP_User')) { /** @var $donor_user WP_User */

            if( !self::_is_donor($donor_user) ) {
                return false;
            }

            $this->_user = $donor_user;
            $this->_id = $donor_user->ID;

        } else {
            return false;
        }

        if( !$this->_meta ) {

            $meta = get_user_meta($this->_id, '', true);

            $this->_meta = array(
                'name' => $this->_user->display_name,
                'email' => $this->_user->user_email,
                'description' => empty($meta['leyka_donor_description']) ? '' : $meta['leyka_donor_description'][0],
                'type' => empty($meta['leyka_donor_type']) ? 'single' : $meta['leyka_donor_type'][0],
                'first_donation_id' => empty($meta['leyka_donor_first_donation_id']) ?
                    0 : absint($meta['leyka_donor_first_donation_id'][0]),
                'first_donation_date' => empty($meta['leyka_donor_first_donation_date']) ?
                    0 : absint($meta['leyka_donor_first_donation_date'][0]),
                'last_donation_id' => empty($meta['leyka_donor_last_donation_id']) ?
                    0 : absint($meta['leyka_donor_last_donation_id'][0]),
                'last_donation_date' => empty($meta['leyka_donor_last_donation_date']) ?
                    0 : absint($meta['leyka_donor_last_donation_date'][0]),
                'campaigns' => empty($meta['leyka_donor_campaigns']) ?
                    array() : $meta['leyka_donor_campaigns'][0],
                'campaigns_news_subscriptions' => empty($meta['leyka_donor_campaigns_news_subscriptions']) ?
                    array() : $meta['leyka_donor_campaigns_news_subscriptions'][0],
                'gateways' => empty($meta['leyka_donor_gateways']) ? array() : $meta['leyka_donor_gateways'][0],
                'amount_donated' => empty($meta['leyka_amount_donated']) ? 0.0 : (float)$meta['leyka_amount_donated'][0],
//                '' => empty($meta['']) ? '' : $meta[''][0],
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

            case 'name': return $this->_meta['name'];

            case 'desc':
            case 'description':
                return $this->_meta['description'];

            case 'type':
            case 'donor_type':
                return $this->_meta['type'];

            case 'type_label':
            case 'donor_type_label':
                $labels = array(
                    'single' => _x('Single', "Donor's type", 'leyka'),
                    'regular' => _x('Regular', "Donor's type", 'leyka'),
                );
                return empty($this->_meta['type']) || empty($labels[$this->_meta['type']]) ? '' : $labels[$this->_meta['type']];

            case 'first_donation_id': return empty($this->_meta['first_donation_id']) ? false : $this->_meta['first_donation_id'];
            case 'first_donation':
                if( !$this->_meta['first_donation_id'] ) {
                    return false;
                }
                return new Leyka_Donation($this->_meta['first_donation_id']);

            case 'first_donation_date_timestamp': return $this->_meta['first_donation_date'];
            case 'first_donation_date':
            case 'first_donation_date_label':
                if( !$this->first_donation_date_timestamp ) {
                    return '';
                }

                $date_format = get_option('date_format');

                return apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $this->first_donation_date_timestamp),
                    $this->first_donation_date_timestamp, $date_format
                );

            case 'first_donation_date_time':
            case 'first_donation_date_time_label':
                if( !$this->first_donation_date_timestamp ) {
                    return '';
                }

                $date_format = get_option('date_format');
                $time_format = get_option('time_format');

                return apply_filters(
                    'leyka_admin_donation_date_time',
                    date("$date_format, $time_format", $this->first_donation_date_timestamp),
                    $this->first_donation_date_timestamp, $date_format, $time_format
                );

            // ...

//            case 'sum':
//            case 'amount':
//                return empty($this->_meta['amount']) ? 0.0 : $this->_meta['amount'];

            default:
                return apply_filters('leyka_donor_get_unknown_donation_field', null, $field, $this);
        }

    }

    public function __set($field, $value) {

        if( !$this->_id ) {
            return false;
        }

//        switch($field) {
//            case 'title':
//            case 'payment_title':
//            case 'purpose_text':
//                if($value != $this->_post_object->post_title) {
//                    wp_update_post(array('ID' => $this->_id, 'post_title' => $value));
//                    $this->_post_object->post_title = $value;
//                }
//                break;
//
//            case 'status':
//                if( !array_key_exists($value, leyka_get_donation_status_list()) || $value == $this->status ) {
//                    return false;
//                }
//
//                wp_update_post(array('ID' => $this->_id, 'post_status' => $value));
//                $this->_post_object->post_status = $value;
//
//                $status_log = get_post_meta($this->_id, '_status_log', true);
//                if($status_log && is_array($status_log)) {
//	                $status_log[] = array('date' => current_time('timestamp'), 'status' => $value);
//                } else {
//                    $status_log = array(array('date' => current_time('timestamp'), 'status' => $value));
//                }
//
//                update_post_meta($this->_id, '_status_log', $status_log);
//                $this->_meta['status_log'] = $status_log;
//
//                break;
//
//            case 'date':
//                wp_update_post(array('ID' => $this->_id, 'post_date' => $value));
//                break;
//            case 'date_timestamp':
//                wp_update_post(array('ID' => $this->_id, 'post_date' => date('Y-m-d H:i:s', $value)));
//                break;
//
//            case 'donor_name':
//                update_post_meta($this->_id, 'leyka_donor_name', $value);
//                $this->_meta['donor_name'] = $value;
//                break;
//            case 'donor_email':
//                update_post_meta($this->_id, 'leyka_donor_email', $value);
//                $this->_meta['donor_email'] = $value;
//                break;
//            case 'donor_comment':
//                $value = sanitize_textarea_field($value);
//                update_post_meta($this->_id, 'leyka_donor_comment', $value);
//                $this->_meta['donor_comment'] = $value;
//                break;
//
//            case 'donor_user_id':
//            case 'donor_account_id':
//
//                $value = absint($value);
//
//                $this->_post_object->post_author = $value;
//                wp_update_post(array('ID' => $this->id, 'post_author' => $value));
//                break;
//
//            case 'donor_account':
//                if(is_wp_error($value)) {
//
//                    $this->_meta['donor_account_error'] = $value;
//                    update_post_meta($this->_id, 'leyka_donor_account', $value);
//
//                } else if(absint($value)) {
//
//                    $value = absint($value);
//
//                    $this->_post_object->post_author = $value;
//                    wp_update_post(array('ID' => $this->id, 'post_author' => $value));
//
//                }
//                break;
//
//            case 'sum':
//            case 'amount':
//            case 'donation_amount':
//
//                $value = (float)$value;
//
//                update_post_meta($this->_id, 'leyka_donation_amount', $value);
//                $this->_meta['amount'] = $value;
//
//                do_action('leyka_donation_amount_changed', $this->_id, $value);
//                break;
//
//            case 'sum_total':
//            case 'amount_total':
//            case 'total_sum':
//            case 'total_amount':
//            case 'donation_amount_total':
//
//                $value = (float)$value;
//
//                update_post_meta($this->_id, 'leyka_donation_amount_total', $value);
//                $this->_meta['amount_total'] = $value;
//
//                do_action('leyka_donation_total_amount_changed', $this->_id, $value);
//                break;
//
//            case 'currency':
//            case 'donation_currency':
//                update_post_meta($this->_id, 'leyka_donation_currency', $value);
//                $this->_meta['currency'] = $value;
//                break;
//
//            case 'gw_id':
//            case 'gateway_id':
//                update_post_meta($this->_id, 'leyka_gateway', $value);
//                $this->_meta['gateway'] = $value;
//                break;
//
//            case 'pm':
//            case 'pm_id':
//            case 'payment_method_id':
//
//                update_post_meta($this->_id, 'leyka_payment_method', $value);
//                $this->_meta['payment_method'] = $value;
//
//                do_action('leyka_donation_pm_changed', $this->_id, $value, $this->gateway_id);
//                break;
//
//            case 'type':
//            case 'payment_type':
//                $value = in_array($value, array_keys(leyka_get_payment_types_list())) ? $value : 'single';
//                update_post_meta($this->_id, 'leyka_payment_type', $value);
//                $this->_meta['payment_type'] = $value;
//                break;
//
//            case 'campaign':
//            case 'campaign_id':
//
//                $value = (int)$value > 0 ? (int)$value : $this->campaign_id;
//
//                update_post_meta($this->_id, 'leyka_campaign_id', $value);
//                $this->_meta['campaign_id'] = $value;
//
//                do_action('leyka_donation_campaign_changed', $this->_id, $value);
//
//                break;
//
//            case 'is_subscribed':
//            case 'donor_subscribed':
//
//                $value = $value === true || (int)$value > 0 ? $value : false;
//
//                update_post_meta($this->_id, 'leyka_donor_subscribed', $value);
//                $this->_meta['donor_subscribed'] = $value;
//                break;
//
//            case 'subscription_email':
//            case 'donor_subscription_email':
//
//                $value = leyka_validate_email($value) ? $value : $this->donor_email;
//
//                update_post_meta($this->_id, 'leyka_donor_subscription_email', $value);
//                $this->_meta['donor_subscription_email'] = $value;
//                break;
//
//            case 'init_recurring_payment':
//            case 'init_recurring_payment_id':
//            case 'init_recurring_donation':
//            case 'init_recurring_donation_id':
//                $value = (int)$value;
//                if($value > 0 && $value != $this->_post_object->post_parent) {
//                    wp_update_post(array('ID' => $this->_id, 'post_parent' => $value));
//                    $this->_post_object->post_parent = $value;
//                }
//                break;
//
//            case 'rebilling_on':
//            case 'rebilling_is_on':
//            case 'recurring_on':
//            case 'recurring_is_on':
//            case 'rebilling_is_active':
//            case 'recurring_is_active':
//                $value = !!$value;
//                if($this->type !== 'rebill') {
//                    break;
//                }
//
//                $init_recurring_donation = $this->init_recurring_donation;
//                if($init_recurring_donation->recurring_is_active != $value) {
//
//                    update_post_meta($init_recurring_donation->id, '_rebilling_is_active', $value);
//                    $this->_meta['rebilling_is_active'] = $value;
//
//                    do_action('leyka_donation_recurring_activity_changed', $this->_id, $value);
//
//                }
//
//                if($value) {
//
//                    $this->_meta['recurrents_cancelled'] = false;
//                    $this->_meta['recurrents_cancel_date'] = 0;
//                    update_post_meta($this->_id, 'leyka_recurrents_cancelled', false);
//                    update_post_meta($this->_id, 'leyka_recurrents_cancel_date', 0);
//
//                } else {
//
//                    $this->_meta['recurrents_cancelled'] = true;
//                    $this->_meta['recurrents_cancel_date'] = current_time('timestamp');
//                    update_post_meta($this->_id, 'leyka_recurrents_cancelled', true);
//                    update_post_meta($this->_id, 'leyka_recurrents_cancel_date', $this->_meta['recurrents_cancel_date']);
//
//                }
//                break;
//
//            case 'cancel_recurring_requested':
//                update_post_meta($this->_id, 'leyka_cancel_recurring_requested', !!$value);
//                break;
//
//            default:
//                do_action('leyka_'.$this->gateway_id.'_set_unknown_donation_field', $field, $value, $this);
//        }

        return true;

    }

    public function delete() {
        wp_delete_user($this->_id);
    }

}