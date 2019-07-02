<?php if( !defined('WPINC') ) die;

/** * Leyka Donor Classes */

class Leyka_Donor {

	private $_id;

    /** @var WP_User */
	private $_user;

    private $_meta = array();

    public static $user_role = 'donor';

    public static function add(array $params) {

        if(empty($params['donor_email'])) {
            return false;
        }
        if(empty($params['donor_name'])) {

            $params['donor_name'] = explode('@', $params['donor_email']);
            $params['donor_name'] = count($params['donor_name']) > 0 ? $params['donor_name'][0] : __('Anonymous donor', 'leyka');

        }
        $params['donor_has_account_access'] = !empty($params['donor_has_account_access']);

        $donor_user = get_user_by('email', $params['donor_email']);

        if($donor_user && is_a($donor_user, 'WP_User')) { // Donor user already exists

            $donor_user_id = $donor_user->ID;
            $donor_user->add_role('donor');

        } else { // Create a new Donor user

            $donor_user_id = wp_insert_user(array(
                'user_email' => $params['donor_email'],
                'user_login' => $params['donor_email'],
                'user_pass' => wp_generate_password(16, true, false),
                'display_name' => $params['donor_name'],
                'show_admin_bar_front' => false,
                'role' => 'donor',
            ));

            if($params['donor_has_account_access']) {
                update_user_meta($donor_user_id, 'leyka_account_activation_code', wp_generate_password(60, false, false));
            }

            $donor_user = get_user_by('id', $donor_user_id);

        }

        if($params['donor_has_account_access']) {
            $donor_user->add_cap('donor_account_access');
        } else {
            $donor_user->remove_cap('donor_account_access');
        }

        return $donor_user_id;

    }

    protected static function _is_donor(WP_User $donor_user) {
        return in_array(static::$user_role, (array)$donor_user->roles);
    }

    public function __construct($donor_user) {

        if(is_int($donor_user) && absint($donor_user) > 0) {

            $donor_user = (int)$donor_user;
            $this->_user = get_user_by('id', $donor_user);

            if( !$this->_user || !self::_is_donor($this->_user) ) {
                throw new Exception(__('Incorrect Donor identification data', 'leyka'));
            }

            $this->_id = $this->_user->ID;

        } else if(is_string($donor_user) && $donor_user) {

            $donor_user = esc_sql($donor_user);
            $this->_user = get_user_by('email', $donor_user);

            if( !$this->_user || !self::_is_donor($this->_user) ) {
                throw new Exception(__('Incorrect Donor identification data', 'leyka'));
            }

            $this->_id = $this->_user->ID;

        } else if(is_a($donor_user, 'WP_User')) { /** @var $donor_user WP_User */

            if( !self::_is_donor($donor_user) ) {
                throw new Exception(__('Incorrect Donor identification data', 'leyka'));
            }

            $this->_user = $donor_user;
            $this->_id = $donor_user->ID;

        } else {
            throw new Exception(__('Incorrect Donor identification data', 'leyka'));
        }

        if( !$this->_meta ) {

            $meta = get_user_meta($this->_id, '', true);

            $this->_meta = array(
                'account_activation_code' => empty($meta['leyka_account_activation_code']) ?
                    '' : $meta['leyka_account_activation_code'][0],
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
            );

        }

	}

    public function __get($field) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {
            case 'id':
            case 'ID':
                return $this->_id;

            case 'login':
            case 'user_login':
            case 'donor_login':
                return $this->_user->user_login;

            case 'account_activation_code': return $this->_meta['account_activation_code'];

            case 'name':
            case 'donor_name':
                return $this->_meta['name'];

            case 'email':
            case 'donor_email':
                return $this->_meta['email'];

            case 'has_account_access':
                return $this->_user->has_cap('donor_account_access');

            case 'desc':
            case 'donor_desc':
            case 'description':
            case 'donor_description':
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

            case 'last_donation_date_timestamp': return $this->_meta['last_donation_date'];
            case 'last_donation_date':
            case 'last_donation_date_label':
                if( !$this->last_donation_date_timestamp ) {
                    return '';
                }

                $date_format = get_option('date_format');

                return apply_filters(
                    'leyka_admin_donation_date',
                    date($date_format, $this->last_donation_date_timestamp),
                    $this->last_donation_date_timestamp, $date_format
                );

            case 'last_donation_date_time':
            case 'last_donation_date_time_label':
                if( !$this->last_donation_date_timestamp ) {
                    return '';
                }

                $date_format = get_option('date_format');
                $time_format = get_option('time_format');

                return apply_filters(
                    'leyka_admin_donation_date_time',
                    date("$date_format, $time_format", $this->last_donation_date_timestamp),
                    $this->last_donation_date_timestamp, $date_format, $time_format
                );

            case 'campaigns': return apply_filters('leyka_admin_donor_campaigns', $this->_meta['campaigns']);

            case 'subscribed':
            case 'campaigns_news':
            case 'news_subscriptions':
            case 'campaigns_news_subscriptions':
                return apply_filters(
                    'leyka_admin_donor_campaigns_news_subscriptions',
                    $this->_meta['campaigns_news_subscriptions']
                );

            case 'gateways': return apply_filters('leyka_admin_donor_gateways', $this->_meta['gateways']);

            case 'sum_donated':
            case 'amount_donated':
                return $this->_meta['amount_donated'];

            default:
                return apply_filters('leyka_get_unknown_donor_field', null, $field, $this);
        }

    }

    public function __set($field, $value) {

        if( !$this->_id ) {
            return false;
        }

        switch($field) {

            case 'account_activation_code':
                $res = update_user_meta($this->_id, 'leyka_account_activation_code', $value);
                break;

            case 'name':
            case 'donor_name':
                if($value === $this->name) {
                    return true;
                }
                $this->_meta['name'] = esc_sql($value);
                $res = wp_update_user(array('ID' => $this->_id, 'display_name' => $this->_meta['name']));
                break;

            case 'email':
            case 'donor_email':
                if($value === $this->email || !is_email($value)) {
                    return true;
                }
                $this->_meta['email'] = esc_sql($value);
                $res = wp_update_user(array('ID' => $this->_id, 'user_email' => $this->_meta['email']));
                break;

            case 'desc':
            case 'donor_desc':
            case 'description':
            case 'donor_description':
                if($value === $this->description) {
                    return true;
                }
                $this->_meta['description'] = esc_sql($value);
                $res = update_user_meta($this->_id, 'leyka_donor_description', $value);
                break;

            case 'type':
            case 'donor_type':
                if($value === $this->type) {
                    return true;
                }
                $this->_meta['type'] = esc_sql($value);
                $res = update_user_meta($this->_id, 'leyka_donor_type', $value);
                break;

            case 'first_donation_id':
            case 'first_donation':
                if($value === $this->first_donation_id) {
                    return true;
                }
                $this->_meta['first_donation_id'] = absint($value);
                $res = empty($this->_meta['first_donation_id']) ?
                    delete_user_meta($this->_id, 'leyka_donor_first_donation_id') :
                    update_user_meta($this->_id, 'leyka_donor_first_donation_id', $value);
                break;

            case 'first_donation_date_timestamp':
                if($value === $this->first_donation_date) {
                    return true;
                }
                $this->_meta['first_donation_date'] = absint($value);
                $res = empty($this->_meta['first_donation_date']) ?
                    delete_user_meta($this->_id, 'leyka_donor_first_donation_date') :    
                    update_user_meta($this->_id, 'leyka_donor_first_donation_date', $value);
                break;

            case 'last_donation_id':
            case 'last_donation':
                if($value === $this->last_donation_id) {
                    return true;
                }
                $this->_meta['last_donation_id'] = absint($value);
                $res = empty($this->_meta['last_donation_id']) ?
                    delete_user_meta($this->_id, 'leyka_donor_last_donation_id') :
                    update_user_meta($this->_id, 'leyka_donor_last_donation_id', $value);
                break;

            case 'last_donation_date_timestamp':
                if($value === $this->last_donation_date) {
                    return true;
                }
                $this->_meta['last_donation_date'] = absint($value);
                $res = empty($this->_meta['last_donation_date']) ?
                    delete_user_meta($this->_id, 'leyka_donor_last_donation_date') :
                    update_user_meta($this->_id, 'leyka_donor_last_donation_date', $value);
                break;

            case 'campaigns':
                if( !is_array($value) || $value == $this->campaigns ) {
                    return true;
                }
                $this->_meta['campaigns'] = $value;
                $res = update_user_meta($this->_id, 'leyka_donor_campaigns', $value);
                break;

            case 'subscribed':
            case 'campaigns_news':
            case 'news_subscriptions':
            case 'campaigns_news_subscriptions':
                if( !is_array($value) || $value == $this->campaigns ) {
                    return true;
                }
                $this->_meta['campaigns_news_subscriptions'] = $value;
                $res = update_user_meta($this->_id, 'leyka_donor_campaigns_news_subscriptions', $value);
                break;

            case 'gateways':
                if( !is_array($value) || $value == $this->campaigns ) {
                    return true;
                }
                $this->_meta['gateways'] = $value;
                $res = update_user_meta($this->_id, 'leyka_donor_gateways', $value);
                break;

            case 'sum_donated':
            case 'amount_donated':
                if($value == $this->amount_donated) {
                    return true;
                }
                $this->_meta['amount_donated'] = (float)$value;
                $res = update_user_meta($this->_id, 'leyka_amount_donated', $value);
                break;

            case 'pass':
            case 'password':
            case 'donor_password':
            case 'account_password':
                $value = trim($value);
                $res = wp_update_user(array('ID' => $this->_id, 'user_pass' => esc_sql($value)));
                break;

            default:
                do_action('leyka_set_unknown_donor_field', $field, $value, $this);
                $res = true;

        }

        return !!$res && !is_wp_error($res);

    }

    function get_init_recurring_donations($only_active = true, $show_cancel_requested = true) {

        if( !$this->_id || !$this->email ) {
            return array();
        }

        $meta_params = array(
            'relation' => 'AND',
            array('key' => 'leyka_payment_type', 'value' => 'rebill'),
            array(
                'relation' => 'OR',
                array('key' => 'leyka_donor_email', 'value' => $this->email),
                array('key' => 'leyka_donor_account', 'value' => $this->_id),
            ),
        );

        if($only_active) {
            $meta_params[] = array(
                'relation' => 'OR',
                array('key' => 'leyka_recurrents_cancelled', 'value' => false),
                array('key' => 'leyka_recurrents_cancelled', 'compare' => 'NOT EXISTS'),
            );
        }

        if( !$show_cancel_requested ) {
            $meta_params[] = array(
                'relation' => 'OR',
                array('key' => 'leyka_cancel_recurring_requested', 'value' => false),
                array('key' => 'leyka_cancel_recurring_requested', 'compare' => 'NOT EXISTS'),
            );
        }

        $recurring_subscriptions = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'post_parent' => 0,
            'meta_query' => $meta_params,
            'posts_per_page' => -1,
        ));

        if( !$recurring_subscriptions ) {
            return array();
        }

        foreach($recurring_subscriptions as &$init_donation) { /** @var $init_donation WP_Post */
            $init_donation = new Leyka_Donation($init_donation);
        }

        return $recurring_subscriptions;

    }

    function get_donations($page_number = false) {

        $page_number = (int)$page_number > 0 ? (int)$page_number : 1;

        if( !$this->_id || !$this->email ) {
            return array();
        }

        $donations = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => array('funded', 'refunded', 'failed'),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array('key' => 'leyka_payment_type', 'value' => 'single'),
                    array('key' => 'leyka_payment_type', 'value' => 'rebill'),
                ),
                array(
                    'relation' => 'OR',
                    array('key' => 'leyka_donor_email', 'value' => $this->email),
                    array('key' => 'leyka_donor_account', 'value' => $this->_id),
                ),
            ),
            'posts_per_page' => LEYKA_DONOR_ACCOUNT_DONATIONS_PER_PAGE,
            'paged' => $page_number,
        ));

        if( !$donations ) {
            return array();
        }

        foreach($donations as &$donation) { /** @var $donation WP_Post */
            $donation = new Leyka_Donation($donation);
        }

        return $donations;

    }

    function get_donations_count() {

        if( !$this->_id || !$this->email ) {
            return array();
        }

        $donations = new WP_Query(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => array('funded', 'refunded', 'failed'),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array('key' => 'leyka_payment_type', 'value' => 'single'),
                    array('key' => 'leyka_payment_type', 'value' => 'rebill'),
                ),
                array(
                    'relation' => 'OR',
                    array('key' => 'leyka_donor_email', 'value' => $this->email),
                    array('key' => 'leyka_donor_account', 'value' => $this->_id),
                ),
            ),
            'posts_per_page' => -1,
        ));

        return $donations->found_posts;

    }

    public function delete() {
        wp_delete_user($this->_id);
    }

    public function login($password, $remember = true) {

        if( !$this->_id || !$this->_user ) {
            return false;
        }

        $password = trim($password);

        $res = wp_signon(array(
            'user_login' => $this->_user->user_login,
            'user_password' => $password,
            'remember' => !!$remember,
        ));

        return is_wp_error($res) ? $res : true;

    }

    public function get_password_reset_key() {

        if( !$this->_user ) {
            return false;
        }

        return get_password_reset_key($this->_user);

    }

}