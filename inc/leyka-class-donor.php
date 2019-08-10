<?php if( !defined('WPINC') ) die;

/** Leyka Donor Class */

class Leyka_Donor {

	protected $_id;

    /** @var WP_User */
	protected $_user;

    protected $_meta = array();

    const DONOR_USER_ROLE = 'donor';
    const DONOR_ACCOUNT_ACCESS_CAP = 'donor_account_access';
    const DONORS_TAGS_TAXONOMY_NAME = 'donors_tag';
    const DONOR_ACCOUNT_DONATIONS_PER_PAGE = 6; // For the frontend Donor's Account page

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
            $donor_user->add_role(Leyka_Donor::DONOR_USER_ROLE);

        } else { // Create a new Donor user

            $donor_user_id = wp_insert_user(array(
                'user_email' => $params['donor_email'],
                'user_login' => $params['donor_email'],
                'user_pass' => wp_generate_password(16, true, false),
                'display_name' => $params['donor_name'],
                'show_admin_bar_front' => false,
                'role' => Leyka_Donor::DONOR_USER_ROLE,
            ));

            if($params['donor_has_account_access']) {
                update_user_meta($donor_user_id, 'leyka_account_activation_code', wp_generate_password(60, false, false));
            }

            $donor_user = get_user_by('id', $donor_user_id);

        }

        self::update_account_access($donor_user, $params['donor_has_account_access']);

        return $donor_user_id;

    }

    public static function update_account_access($donor_user, $donor_has_account) {

        $donor_user = leyka_get_validated_user($donor_user);
        $donor_has_account = !!$donor_has_account;

        if( !self::user_is_donor($donor_user) ) {
            $donor_user->add_role(self::DONOR_USER_ROLE);
        }

        if($donor_has_account) {
            $donor_user->add_cap(self::DONOR_ACCOUNT_ACCESS_CAP);
        } else {
            $donor_user->remove_cap(self::DONOR_ACCOUNT_ACCESS_CAP);
        }

        return true;

    }

    /**
     * Create Donor account from donation, if it doesn't exist yet.
     *
     * @param $donation int|WP_Post|Leyka_Donation
     * @param $donor_has_account boolean|null Either true/false, or NULL to decide based on given donation type.
     * @return int|WP_Error New donor user ID or WP_Error object.
     */
    public static function create_donor_from_donation($donation, $donor_has_account = null) {

        $donation = leyka_get_validated_donation($donation);

        if( !$donation || !leyka()->opt('donor_management_available') ) {
            $donor_user_id = new WP_Error(
                'donor_account_not_created',
                __("Can't create donor user from donation", 'leyka'),
                $donation->id
            );
        } else {
            $donor_user_id = $donation->donor_user_id;
        }

        if($donor_has_account === null) {
            $donor_has_account = $donation->type === 'rebill' && leyka_options()->opt('donor_accounts_available');
        } else {
            $donor_has_account = !!$donor_has_account;
        }

        if( !$donor_user_id ) {

            $donor_user_id = Leyka_Donor::add(array(
                'donor_email' => $donation->donor_email,
                'donor_name' => $donation->donor_name,
                'donor_has_account_access' => $donor_has_account,
            ));

            $donation->donor_account = $donor_user_id; // Donor ID or WP_Error

        } else if(is_int($donor_user_id)) { // Add/remove Donor's account access capability, if needed
            Leyka_Donor::update_account_access($donor_user_id, $donor_has_account);
        }

        do_action('leyka_donor_account'.(is_wp_error($donor_user_id) ? '_not_' : '_').'created', $donor_user_id, $donation);

        return $donor_user_id;

    }

    public static function calculate_donor_metadata(Leyka_Donor $donor) {

        if( !$donor->id ) {
            return;
        }

        $donor_data = array(
            'donor_type' => 'single',
            'first_donation' => false,
            'last_donation' => false,
            'campaigns' => array(),
            'campaigns_news_subscriptions' => array(),
            'gateways' => array(),
            'amount_donated' => 0.0,
        );

        $donor_donations = get_posts(array( // Get donations by donor's email
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'posts_per_page' => -1,
//            'author' => $donor->id,
            'meta_query' => array(array('key' => 'leyka_donor_email', 'value' => $donor->email),),
            'orderby' => 'date',
            'order' => 'ASC',
        ));

        $donations_count = count($donor_donations);
        for($i = 0; $i < $donations_count; $i++) {

            $donation = new Leyka_Donation($donor_donations[$i]);

            $donation->donor_user_id = $donor->id;

            if($donation->is_init_recurring_donation && $donation->recurring_on) {
                $donor_data['donor_type'] = 'regular';
            }

            if($i === 0) {
                $donor_data['first_donation'] = $donation;
            }
            if($i === $donations_count - 1) {
                $donor_data['last_donation'] = $donation;
            }

            if(empty($donor_data['campaigns']) || empty($donor_data['campaigns'][$donation->campaign_id])) {
                $donor_data['campaigns'][$donation->campaign_id] = $donation->campaign_title;
            }
            if($donation->donor_subscribed) {
                if(
                    empty($donor_data['campaigns_news_subscriptions'])
                    || empty($donor_data['campaigns_news_subscriptions'][$donation->campaign_id])
                ) {
                    $donor_data['campaigns_news_subscriptions'][$donation->campaign_id] = $donation->campaign_title;
                }
            }

            if(empty($donor_data['gateways']) || !in_array($donation->gateway, $donor_data['gateways'])) {
                $donor_data['gateways'][] = $donation->gateway;
            }

            if($donation->status === 'funded') {
                $donor_data['amount_donated'] = empty($donor_data['amount_donated']) ?
                    $donation->amount : $donor_data['amount_donated'] + $donation->amount;
            }

        }

        if($donor_data['first_donation']) {

            $donor->first_donation_id = $donor_data['first_donation']->id;
            $donor->first_donation_date_timestamp = $donor_data['first_donation']->date_timestamp;

        } else {

            $donor->first_donation_id = 0;
            $donor->first_donation_date_timestamp = 0;

        }

        if($donor_data['last_donation']) {

            $donor->last_donation_id = $donor_data['last_donation']->id;
            $donor->last_donation_date_timestamp = $donor_data['last_donation']->date_timestamp;

        } else {

            $donor->last_donation_id = 0;
            $donor->last_donation_date_timestamp = 0;

        }

        $donor->type = $donor_data['donor_type'];
        $donor->campaigns = $donor_data['campaigns'];
        $donor->campaigns_news_subscriptions = $donor_data['campaigns_news_subscriptions'];
        $donor->gateways = $donor_data['gateways'];
        $donor->amount_donated = $donor_data['amount_donated'];

    }

    public static function order_donor_data_refreshing($donation_id) {

        $donation_id = absint($donation_id);

        if( !$donation_id || !leyka_options()->opt('donor_management_available') ) {
            return;
        }

        $donations_ordered = get_transient('leyka_donations2refresh_donor_data_cache');
        if( !$donations_ordered ) {
            $donations_ordered = array();
        }

        if(is_array($donations_ordered) && !in_array($donation_id, $donations_ordered)) {

            $donations_ordered[] = $donation_id;
            set_transient('leyka_donations2refresh_donor_data_cache', $donations_ordered);

        }

    }

    public static function user_is_donor(WP_User $donor_user) {
        return in_array(self::DONOR_USER_ROLE, (array)$donor_user->roles);
    }

    /**
     * @param $donor_user int|string|WP_User Either Donor's ID, email, or WP_User object.
     * @throws Exception
     */
    public function __construct($donor_user) {

        $donor_user = leyka_get_validated_user($donor_user);

        if(is_wp_error($donor_user)) {
            throw new Exception($donor_user->get_error_message());
        }

        $this->_id = $donor_user->ID;
        $this->_user = $donor_user;

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
                    array() : maybe_unserialize($meta['leyka_donor_campaigns'][0]),
                'campaigns_news_subscriptions' => empty($meta['leyka_donor_campaigns_news_subscriptions']) ?
                    array() : maybe_unserialize($meta['leyka_donor_campaigns_news_subscriptions'][0]),
                'gateways' => empty($meta['leyka_donor_gateways']) ?
                    array() : maybe_unserialize($meta['leyka_donor_gateways'][0]),
                'amount_donated' => empty($meta['leyka_amount_donated']) ? 0.0 : (float)$meta['leyka_amount_donated'][0],
                //'comments' => empty($meta['leyka_donor_comments']) ? array() : maybe_unserialize($meta['leyka_donor_comments']),
            );

            // Donor comments:
            $this->_meta['comments'] = array();

            if( !empty($meta['leyka_donor_comments']) ) {

                $this->_meta['comments'] = maybe_unserialize($meta['leyka_donor_comments'][0]);

                foreach($this->_meta['comments'] as &$comment) {
                    $comment = maybe_unserialize($comment);
                }

            }

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
                return $this->_user->has_cap(self::DONOR_ACCOUNT_ACCESS_CAP);

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

            case 'last_donation_id': return empty($this->_meta['last_donation_id']) ? false : $this->_meta['last_donation_id'];
            case 'last_donation':
                if( !$this->_meta['last_donation_id'] ) {
                    return false;
                }
                return new Leyka_Donation($this->_meta['last_donation_id']);                

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
                if( !is_array($value) || $value == $this->campaigns_news_subscriptions ) {
                    return true;
                }
                $this->_meta['campaigns_news_subscriptions'] = $value;
                $res = update_user_meta($this->_id, 'leyka_donor_campaigns_news_subscriptions', $value);
                break;

            case 'gateways':
                if( !is_array($value) || $value == $this->gateways ) {
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
            array('key' => 'leyka_donor_email', 'value' => $this->email),
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
            'author' => $this->_id,
            'posts_per_page' => static::DONOR_ACCOUNT_DONATIONS_PER_PAGE,
            'paged' => $page_number,
            'orderby' => 'date ID',
            'order' => 'DESC',
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
                array('key' => 'leyka_donor_email', 'value' => $this->email),
            ),
            'posts_per_page' => -1,
        ));

        return $donations->found_posts;

    }

    public function get_tags(array $params = array()) {
        return wp_get_object_terms($this->_id, static::DONORS_TAGS_TAXONOMY_NAME, $params);
    }

    public function comments_exist() {
        return !empty($this->_meta['comments']);
    }

    public function get_comments( /*array $params = array()*/ ) {

        if( !$this->comments_exist() ) {
            return array();
        }

        // Apply $params filters here

        return $this->_meta['comments'];

    }

    public function add_comment($text) {

        $text = empty($text) ? '' : trim($text);
        if( !$text ) {
            return false;
        }

        $new_comment_id = 1;
        while(array_key_exists($new_comment_id, $this->_meta['comments'])) {
            $new_comment_id += 1;
        }

        $this->_meta['comments'][$new_comment_id] = array(
            'date' => time(),
            'text' => esc_attr($text),
            'author_id' => get_current_user_id(),
            'author_name' => wp_get_current_user()->display_name,
        );

        return !!update_user_meta($this->_id, 'leyka_donor_comments', $this->_meta['comments']);

    }

    public function delete_comment($comment_id) {

        if( !array_key_exists($comment_id, $this->_meta['comments']) ) {
            return false;
        }

        unset($this->_meta['comments'][$comment_id]);
        return !!update_user_meta($this->_id, 'leyka_donor_comments', $this->_meta['comments']);

    }

    public function update_comment($comment_id, $comment_text) {
        
        if( !array_key_exists($comment_id, $this->_meta['comments']) ) {
            return false;
        }
        
        $this->_meta['comments'][$comment_id]['text'] = $comment_text;
        
        return !!update_user_meta($this->_id, 'leyka_donor_comments', $this->_meta['comments']);
        
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
        return $this->_user ? get_password_reset_key($this->_user) : false;
    }

}