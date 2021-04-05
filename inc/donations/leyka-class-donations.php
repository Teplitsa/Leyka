<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations extends Leyka_Singleton {

    protected static $_instance = null;

    public static $use_leyka_object_cache = true; // A flag to turn on/off the Leyka donations object cache

    /** @var array An array of Leyka_Donation_Base objects cache */
    protected static $_objects = array();

    /**
     * @param $params array Isn't in use.
     * @return static
     */
    public static function get_instance(array $params = array()) {

        if(null === static::$_instance) {

            if(in_array(get_option('leyka_donations_storage_type'), array('sep', 'sep-incompleted'))) {

                require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-separated.php');
                static::$_instance = new Leyka_Donations_Separated();

            } else {

                require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-post.php');
                static::$_instance = new Leyka_Donations_Posts();

            }

        }

        return static::$_instance;

    }

    /**
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @param bool $update_cache
     * @return Leyka_Donation_Base|null
     */
    public function get_donation($donation, $update_cache = false) {

        $donation_id = $this->_get_donation_id($donation);
        if(self::$use_leyka_object_cache) {

            if(empty(self::$_objects[$donation_id]) || !!$update_cache) {
                self::$_objects[$donation_id] = $this->_get_donation($donation);
            }

            return self::$_objects[$donation_id];

        } else {
            return $this->_get_donation($donation);
        }

    }

    /**
     * @param int|WP_Post|Leyka_Donation_Base|object $donation
     * @return int|false
     */
    protected static function _get_donation_id($donation) {

        if((is_int($donation) || is_string($donation)) && absint($donation)) {
            return absint($donation);
        } else if(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            return $donation->ID;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            return $donation->id;
        } else if(is_object($donation) && isset($donation->ID) && isset($donation->campaign_id)) {
            return $donation->ID;
        } else {
            return false;
        }

    }

    /**
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @return Leyka_Donation_Base|null
     */
    abstract protected function _get_donation($donation);

    /**
     * @param $params array|int
     * @return array|Leyka_Donation_Base|boolean Either an array of Leyka_Donation_Base objects, or single object (if get_single param is set), or false if no donations found.
     */
    abstract public function get($params);

    /**
     * @param $params array
     * @return integer.
     */
    abstract public function get_count(array $params = array());

    /**
     * @param $params array
     * @param $return_object boolean True to return a Leyka_Donation_Base object, false to return just a new donation ID.
     * @return integer|WP_Error An ID of the new donation, WP_Error object if there was an error in the process
     */
    abstract public function add(array $params = array(), $return_object = false);

    /**
     * Helper to add a copy of given Donation.
     *
     * @param $original Leyka_Donation_Base
     * @param $params array An array of Donation params to rewrite in the clone.
     * @param $settings array Cloning operation settings array.
     * @return Leyka_Donation_Base|WP_Error A new Donation object or WP_Error object if there were some errors while adding it.
     */
    public function add_clone(Leyka_Donation_Base $original, array $params = array(), array $settings = array()) {

        $settings = array_merge(array('recalculate_total_amount' => false,), $settings);

        $new_donation_id = $this->add(array_merge(array(
            'date' => $original->date,
            'status' => $original->status,
            'payment_type' => $original->payment_type,
            'purpose_text' => $original->title,
            'campaign_id' => $original->campaign_id,
            'payment_method_id' => $original->pm_id,
            'gateway_id' => $original->gateway_id,
            'donor_name' => $original->donor_name,
            'donor_email' => $original->donor_email,
            'donor_user_id' => $original->donor_user_id,
            'amount' => $original->amount,
            'amount_total' => $original->amount_total,
            'currency' => $original->currency,
        ), $params));

        if(is_wp_error($new_donation_id)) {
            return $new_donation_id;
        }

        $new = Leyka_Donations::get_instance()->get($new_donation_id);

        if( // If the original donation was made before the commission was set, apply a commission to the cloned one
            $settings['recalculate_total_amount']
            && $original->amount == $original->amount_total
            && leyka_get_pm_commission($original->pm_full_id) > 0.0
        ) {
            $new->amount_total = leyka_calculate_donation_total_amount($new);
        }

        return $new;

    }

    abstract public function delete_donation($donation_id, $force_delete = false);

    protected function _get_multiple_filter_values($values, array $possible_values_list) {

        if( !$values ) {
            return null;
        }

        $values_to_filter = array();
        if(is_array($values)) { // Values as an array

            foreach($values as $value) {
                if($value && array_key_exists(trim($value), $possible_values_list)) {
                    $values_to_filter[] = trim($value);
                }
            }

        } else if(mb_stripos($values, ',') !== false) { // Comma-separated values list

            foreach(explode(',', $values) as $value) {
                if($value && array_key_exists(trim($value), $possible_values_list)) {
                    $values_to_filter[] = trim($value);
                }
            }

        } else if(array_key_exists(trim($values), $possible_values_list)) { // A single value
            $values_to_filter = array(trim($values));
        }

        return $values_to_filter;

    }

    abstract public function set_donation_meta($donation_id, $meta_key, $value);
    abstract public function get_donation_meta($donation_id, $meta_key);
    abstract public function get_donation_id_by_meta_value($meta_key, $value);

}

class Leyka_Donations_Posts extends Leyka_Donations {

    protected static $_instance = null;

    protected function _get_donation($donation) {

        $donation = new Leyka_Donation_Post($donation);

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    protected function _get_query(array $params = array()) {

        $query_params = array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'cache_results' => defined('WP_CACHE') ? WP_CACHE : true,
            'update_post_meta_cache' => defined('WP_CACHE') ? WP_CACHE : true,
            'update_post_term_cache' => defined('WP_CACHE') ? WP_CACHE : true,
        );

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? array() : $params['meta'];

        if( !empty($params['status']) ) {

            $values_list = $this->_get_multiple_filter_values($params['status'], leyka_get_donation_status_list());

            if($values_list) {
                $query_params['post_status'] = $values_list;
            }

        }

        if( !empty($params['results_limit']) && absint($params['results_limit']) ) {
            $query_params['posts_per_page'] = absint($params['results_limit']);
        }

        if( !empty($params['get_single']) ) {
            $query_params['posts_per_page'] = 1;
        } else if( !empty($params['page']) && absint($params['results_limit']) > 1 ) {
            $query_params['page'] = absint($params['page']);
        }

        if(isset($params['year_month']) && absint($params['year_month'])) {
            $query_params['m'] = absint($params['year_month']);
        }

        if(isset($params['day']) && (int)$params['day'] >= 1 && (int)$params['day'] <= 31) {
            $query_params['day'] = (int)$params['day'];
        }

        if( !empty(trim($params['search_string'])) ) {
            $query_params['s'] = trim($params['search_string']);
        }

        if( !empty($params['recurring_only_init']) ) {

            $query_params['post_parent'] = 0;
            $params['payment_type'] = 'rebill';

        }

        if(isset($params['recurring_active'])) {

            $params['meta'][] = array('key' => '_rebilling_is_active', 'value' => !!$params['recurring_active'] ? '1' : '0');
            $params['payment_type'] = 'rebill';

        }

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $params['meta'][] = array('key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '>');
            } else if($params['amount_filter'] === 'only-') {
                $params['meta'][] = array('key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '<');
            } else if(stripos($params['amount_filter'], '>=') !== false) {
                $params['meta'][] = array(
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('>=', '', $params['amount_filter']),
                    'compare' => '>=',
                );
            } else if(stripos($params['amount_filter'], '<=') !== false) {
                $params['meta'][] = array(
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('<=', '', $params['amount_filter']),
                    'compare' => '<=',
                );
            }

        }

        if( !empty($params['campaign_id']) ) {
            $params['meta'][] = array('key' => 'leyka_campaign_id', 'value' => (int)$params['campaign_id']);
        }

        if( !empty($params['payment_type']) ) {

            $values_list = $this->_get_multiple_filter_values($params['payment_type'], leyka_get_payment_types_list());

            if($values_list) {
                $params['meta'][] = array('key' => 'leyka_payment_type', 'value' => (array)$values_list, 'compare' => 'IN');
            }

        }

        if( !empty($params['gateway_pm']) ) {

            if(strpos($params['gateway_pm'], 'gateway__') !== false) {
                $params['meta'][] = array(
                    'key' => 'leyka_gateway',
                    'value' => str_replace('gateway__', '', $params['gateway_pm'])
                );
            } else if(strpos($params['gateway_pm'], 'pm__') !== false) {
                $params['meta'][] = array(
                    'key' => 'leyka_payment_method',
                    'value' => str_replace('pm__', '', $params['gateway_pm'])
                );
            }

        }

        if( !empty($params['gateway_id']) && leyka_get_gateway_by_id($params['gateway_id']) ) {
            $params['meta'][] = array('key' => 'leyka_gateway', 'value' => $params['gateway_id']);
        }

        if( !empty($params['pm_id']) && leyka_get_pm_by_id($params['pm_id']) ) {
            $params['meta'][] = array('key' => 'leyka_payment_method', 'value' => $params['pm_id']);
        }

        if( !empty($params['pm_full_id']) && leyka_get_pm_by_id($params['pm_full_id'], true) ) {

            $params['pm_full_id'] = explode('-', $params['pm_full_id']);
            $params['meta'][] = array('key' => 'leyka_gateway', 'value' => $params['pm_full_id'][0]);
            $params['meta'][] = array('key' => 'leyka_payment_method', 'value' => $params['pm_full_id'][1]);

        }

        if(count($params['meta']) > 1) {

            if( empty($params['meta']['relation']) || !in_array($params['meta']['relation'], array('AND', 'OR')) ) {
                $params['meta']['relation'] = 'AND';
            }

            $query_params['meta_query'] = $params['meta'];

        }

        if( !empty($params['order']) && in_array($params['order'], array('asc', 'desc',)) ) {
            $query_params['order'] = mb_strtoupper($params['order']);
        }

        if( !empty($params['orderby']) ) {
            switch($params['orderby']) {
                case 'ID': $query_params['orderby'] = 'ID'; break;
                case 'date': $query_params['orderby'] = 'date'; break;
                case 'amount':
                    $query_params['meta_key'] = 'leyka_donation_amount';
                    $query_params['orderby'] = 'meta_value_num';
                    break;
                case 'status': // Post status ordering is handled manually in the get() method.
                default:
            }
        }

        return new WP_Query($query_params);

    }

    public function get($params) {

        if(is_int($params) && absint($params)) { // Int given - return the single Donation
            return $this->get_donation($params);
        }

        // Array fiven - return a Donations selection:

        $donations_query = $this->_get_query($params);

        $status_filter = function(){ return 'post_status ASC'; }; // For status ordering

        if( !empty($params['orderby']) && $params['orderby'] === 'status' ) {

            add_filter('posts_orderby', $status_filter);
            $donations_query->set('suppress_filters', false);

        }

        $res = $donations_query->get_posts();

        if( !empty($params['orderby']) && $params['orderby'] === 'status' ) {
            remove_filter('posts_orderby', $status_filter);
        }

        if( !empty($params['get_single']) && $res ) {
            $res = $this->get_donation($res[0]);
        } else {
            foreach($res as $key => $donation_post) { /** @var $donation_post WP_Post */
                $res[$key] = $this->get_donation($donation_post);
            }
        }

        return empty($params['get_single']) ? $res : reset($res);

    }

    public function get_count(array $params = array()) {
        return $this->_get_query($params)->found_posts;
    }

    public function add(array $params = array(), $return_object = false) {

        $donation = Leyka_Donation_Post::add($params);

        return $return_object && !is_wp_error($donation) ? new Leyka_Donation_Post($donation) : $donation;

    }

    public function delete_donation($donation_id, $force_delete = false) {
        return !!wp_delete_post(absint($donation_id), !!$force_delete);
    }

    public function get_donation_meta($donation_id, $meta_key) {
        return get_post_meta($donation_id, $meta_key, true);
    }

    public function set_donation_meta($donation_id, $meta_key, $value) {
        return !!update_post_meta($donation_id, trim($meta_key), $value);
    }

    public function get_donation_id_by_meta_value($meta_key, $value) {

        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='%s' AND meta_value='%s' LIMIT 1",
            array($meta_key, $value)
        ));

    }

}

class Leyka_Donations_Separated extends Leyka_Donations {

    protected static $_instance = null;

    protected function _get_donation($donation) {

        try {
            if( !is_a($donation, 'Leyka_Donation_Separated') ) { // Don't make a new instance if argument is already a Sep-typed
                $donation = new Leyka_Donation_Separated($donation);
            }
        } catch(Exception $ex) {
            return false;
        }

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    protected function _get_meta_query_parts($meta_params) {

        $meta_query_obj = new Leyka_Donations_Meta_Query($meta_params);

        return $meta_query_obj->get_sql();

    }

    protected function _get_query_parts(array $params = array()) {

        global $wpdb;

        $query = array('fields' => '', 'from' => '', 'joins' => '', 'where' => '', 'orderby' => '', 'limit' => '',);

        $joins = array();
        $where = array();

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? array() : $params['meta'];

        /** @todo Implement $params['search_string'] handling. */

        $params['strict'] = isset($params['strict']) ? !!$params['strict'] : true;

        if( !empty($params['donor_name_email']) ) {

            if(mb_stristr($params['donor_name_email'], '%') === false) {
                $where['donor_name_email'] = $wpdb->prepare(
                    "({$wpdb->prefix}leyka_donations.donor_name = %s OR {$wpdb->prefix}leyka_donations.donor_email = %s)",
                    $params['donor_name_email'], $params['donor_name_email']
                );
            } else {

                $params['donor_name_email'] = trim($params['donor_name_email'], '%');

                $where['donor_name_email'] = $wpdb->prepare(
                    "({$wpdb->prefix}leyka_donations.donor_name LIKE %s OR {$wpdb->prefix}leyka_donations.donor_email LIKE %s)",
                    '%'.$params['donor_name_email'].'%', '%'.$params['donor_name_email'].'%'
                );

            }

        }

        if( !empty($params['status']) ) {

            $values_list = $this->_get_multiple_filter_values($params['status'], leyka_get_donation_status_list());

            if($values_list) {

                $where_status = "{$wpdb->prefix}leyka_donations.status IN (";
                foreach($values_list as $status) {
                    $where_status .= '%s,';
                }
                $where_status = rtrim($where_status, ',').')';

                $where['status'] = $wpdb->prepare($where_status, $values_list);

            }

        }

        if( !empty($params['recurring_only_init']) ) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = array('key' => 'init_recurring_donation_id', 'value' => 0,);

        }

        if( !empty($params['recurring_active']) ) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = array('key' => 'recurring_active', 'value' => 1,);

        }

        if( !empty($params['payment_type']) ) {

            if(is_array($params['payment_type']) && in_array('rebill-init', $params['payment_type'])) {

                unset($params['payment_type'][ array_search('rebill-init', $params['payment_type']) ]);

                if( !in_array('rebill', $params['payment_type']) ) {
                    $params['payment_type'][] = 'rebill';
                }

            } else if($params['payment_type'] == 'rebill-init') {

                $params['payment_type'] = 'rebill';
                $params['meta'][] = array('key' => 'init_recurring_donation_id', 'value' => 0,);

            }

            $values_list = $this->_get_multiple_filter_values($params['payment_type'], leyka_get_payment_types_list());

            if($values_list) {

                $where_payment_type = "{$wpdb->prefix}leyka_donations.payment_type IN (";
                foreach($values_list as $type) {
                    $where_payment_type .= '%s,';
                }
                $where_payment_type = rtrim($where_payment_type, ',').')';

                $where['payment_type'] = $wpdb->prepare($where_payment_type, $values_list);

            }

        }

        $params['campaign_id'] = empty($params['campaigns_ids']) ?
            (empty($params['campaign_id']) ? array() : $params['campaign_id']) :
            $params['campaigns_ids'];

        if($params['campaign_id']) {

            $params['campaign_id'] = is_array($params['campaign_id']) ? $params['campaign_id'] : array($params['campaign_id']);
            $params['campaign_id'] = array_filter($params['campaign_id'], function($campaign_id){ return absint($campaign_id); });

            $where['campaign_id'] = "{$wpdb->prefix}leyka_donations.campaign_id IN (".implode(',', $params['campaign_id']).")";

        }

        $params['results_limit'] = empty($params['results_limit']) ? 20 : absint($params['results_limit']);

        if( !empty($params['get_single']) ) {
            $limit = ' LIMIT 0,1';
        } else if( !empty($params['get_all']) || !empty($params['nopaging']) ) {
            $limit = '';
        } else if( !empty($params['page']) && absint($params['page']) ) {
            $limit = ' LIMIT '.(($params['page']-1)*$params['results_limit']).','.$params['results_limit'];
        } else {
            $limit = ' LIMIT 0,'.$params['results_limit'];
        }

        if( !empty($params['date']) && strtotime($params['date']) ) {
            $where['date_created'] = $wpdb->prepare(
                "{$wpdb->prefix}leyka_donations.date_created = %s",
                date('Y-m-d', strtotime($params['date']))
            );
        }
        if( !empty($params['date_from']) && !empty($params['date_to']) ) {
            $where['date_created'] = $wpdb->prepare(
                "{$wpdb->prefix}leyka_donations.date_created >= %s AND {$wpdb->prefix}leyka_donations.date_created <= %s",
                date('Y-m-d 00:00:00', strtotime($params['date_from'])), date('Y-m-d 23:59:59', strtotime($params['date_to']))
            );
        } else if( !empty($params['date_from']) && strtotime($params['date_from']) ) {
            $where['date_created'] = $wpdb->prepare(
                "{$wpdb->prefix}leyka_donations.date_created >= %s",
                date('Y-m-d 00:00:00', strtotime($params['date_from']))
            );
        } else if( !empty($params['date_to']) && strtotime($params['date_to']) ) {
            $where['date_created'] = $wpdb->prepare(
                "{$wpdb->prefix}leyka_donations.date_created <= %s",
                date('Y-m-d 23:59:59', strtotime($params['date_to']))
            );
        }

        if(isset($params['year_month']) && (int)$params['year_month'] > 0 && mb_strlen($params['year_month']) >= 6) {

            $year = mb_substr($params['year_month'], 0, 4);
            $month = mb_substr($params['year_month'], 4, 2);

            try {

                $date = new DateTime("$year-$month-01");
                $where['date_created'] = $wpdb->prepare(
                    "({$wpdb->prefix}leyka_donations.date_created >= %s AND {$wpdb->prefix}leyka_donations.date_created <= %s)",
                    $date->format('Y-m-01'), $date->format('Y-m-t')
                );

            } catch(Exception $ex) {
                // ...
            }

        }

        if(isset($params['day']) && (int)$params['day'] >= 1 && (int)$params['day'] <= 31) {
            $where['day_created'] = $wpdb->prepare("DAYOFMONTH({$wpdb->prefix}leyka_donations.date_created) = %d", absint($params['day']));
        }

        if( !empty($params['gateway_pm']) ) {

            if(mb_stripos($params['gateway_pm'], 'gateway__') !== false) {

                $params['gateway_pm'] = str_replace('gateway__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['gateway_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.gateway_id = %s", $params['gateway_pm']);
                }

            } else if(mb_stripos($params['gateway_pm'], 'pm__') !== false) {

                $params['gateway_pm'] = str_replace('pm__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_pm_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['pm_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.pm_id = %s", $params['gateway_pm']);
                }

            } else if(mb_stripos($params['gateway_pm'], '-') !== false) { // PM full ID given
                $params['pm_full_id'] = $params['gateway_pm'];
            } else {
                $params['gateway_id'] = $params['gateway_pm'];
            }

        }

        if( !empty($params['gateway_id']) ) {

            $params['gateway_id'] = is_array($params['gateway_id']) ? $params['gateway_id'] : array($params['gateway_id']);
            $query_params = array();

            foreach($params['gateway_id'] as $gateway_id) {
                if(($params['strict'] && leyka_get_gateway_by_id($gateway_id)) || !$params['strict']) {
                    $query_params[] = $gateway_id;
                }
            }

            if(count($query_params)) {
                $where['gateway_id'] = "{$wpdb->prefix}leyka_donations.gateway_id IN ('".implode("','", $query_params)."')";
            }

        }

        if( !empty($params['pm_id']) ) {

            $params['pm_id'] = is_array($params['pm_id']) ? $params['pm_id'] : array($params['pm_id']);
            $query_params = array();

            foreach($params['pm_id'] as $pm_id) {
                if(($params['strict'] && leyka_get_pm_by_id($pm_id)) || !$params['strict']) {
                    $query_params[] = $pm_id;
                }
            }

            if(count($query_params)) {
                $where['pm_id'] = "{$wpdb->prefix}leyka_donations.pm_id IN ('".implode("','", $query_params)."')";
            }

        }

        if(empty($params['gateway_id']) && empty($params['pm_id']) && !empty($params['pm_full_id']) ) {

            $params['pm_full_id'] = is_array($params['pm_full_id']) ? $params['pm_full_id'] : array($params['pm_full_id']);
            $query_params = array();

            foreach($params['pm_full_id'] as $pm_full_id) {
                if(($params['strict'] && leyka_get_pm_by_id($pm_full_id, true)) || !$params['strict']) {

                    $pm_full_id = explode('-', $pm_full_id);
                    if(count($pm_full_id) !== 2) {
                        continue;
                    }

                    $query_params[] = $wpdb->prepare("({$wpdb->prefix}leyka_donations.gateway_id = %s AND {$wpdb->prefix}leyka_donations.pm_id = %s)", $pm_full_id[0], $pm_full_id[1]);

                }
            }

            if(count($query_params)) {
                $where['pm_full_id'] = '('.implode(' OR ', $query_params).')';
            }

        }

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $where['amount'] = "{$wpdb->prefix}leyka_donations.amount > 0.0";
            } else if($params['amount_filter'] === 'only-') {
                $where['amount'] = "{$wpdb->prefix}leyka_donations.amount < 0.0";
            } else if(stripos($params['amount_filter'], '>=') !== false) {
                $where['amount'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.amount >= %f", round(str_replace('>=', '', $params['amount_filter']), 2));
            } else if(stripos($params['amount_filter'], '<=') !== false) {
                $where['amount'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.amount <= %f", round(str_replace('<=', '', $params['amount_filter']), 2));
            }

        }

        $params['order'] = empty($params['order']) || !in_array($params['order'], array('asc', 'desc',)) ?
            'DESC' : mb_strtoupper($params['order']);

        if(empty($params['orderby'])) {
            $params['orderby'] = 'date';
        }
        if($this->_is_orderable_by($params['orderby'])) {

            switch($params['orderby']) {
                case 'donation_id': $params['orderby'] = "{$wpdb->prefix}leyka_donations.ID"; break;
                case 'date': $params['orderby'] = "{$wpdb->prefix}leyka_donations.date_created"; break;
                default: $params['orderby'] = "{$wpdb->prefix}leyka_donations.".$params['orderby']; break;
            }

            $query['orderby'] = " ORDER BY {$params['orderby']} {$params['order']}";

        }

        if(isset($params['donor_subscribed'])) {
            $params['meta'][] = $params['donor_subscribed'] ?
                array('key' => 'donor_subscribed', 'compare' => '>=', 'value' => 1) :
                array('key' => 'donor_subscribed', 'compare' => 'NOT EXISTS');
        }

        if($params['meta']) {

            $meta_query_parts = $this->_get_meta_query_parts($params['meta']);

            if($meta_query_parts['join'] && $meta_query_parts['where']) {

                $joins[] = $meta_query_parts['join'];
                $where[] = substr_replace($meta_query_parts['where'], '', 0, mb_strlen(' AND '));

            }

        }

        $query['joins'] = $joins ? implode(' ', $joins) : '';
        $query['where'] = $where ? ' WHERE '.implode(' AND ', $where) : '';
        $query['limit'] = $limit ? $limit : '';

        return $query;

    }

    public function get($params) {

        if(is_int($params) && absint($params)) { // Int given - return the single Donation
            return $this->get_donation($params);
        }

        // Array given - return a Donations selection:

        global $wpdb;

        $query = $this->_get_query_parts($params);

        $donations = array();
        $query = "SELECT {$wpdb->prefix}leyka_donations.* FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']} {$query['orderby']} {$query['limit']}";

        foreach($wpdb->get_results($query) as $donation) {
            $donations[] = $this->get_donation($donation);
        }

        return empty($params['get_single']) ? $donations : reset($donations);

    }

    public function get_count(array $params = array()) {

        global $wpdb;

        $query = $this->_get_query_parts($params);
        $query = "SELECT COUNT({$wpdb->prefix}leyka_donations.ID) FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']}";

        return absint($wpdb->get_var($query));

    }

    protected function _is_orderable_by($param_name) {
        return in_array(mb_strtolower($param_name), array('id', 'donation_id', 'campaign_id', 'status', 'date', 'date_created', 'gateway_id', 'pm_id', 'amount', 'donor_name', 'donor_email', 'payment_type',));
    }

    public function add(array $params = array(), $return_object = false) {

        $donation = Leyka_Donation_Separated::add($params);

        if ($return_object && !is_wp_error($donation)) {
            try {
                $donation = new Leyka_Donation_Separated($donation);
            } catch(Exception $ex) {
                return false;
            }
        }

        return $donation;

    }

    public function delete_donation($donation_id, $force_delete = false) {

        $donation = $this->get_donation($donation_id);

        if($donation) {
            Leyka_Donation_Management::get_instance()->donation_status_changed('trash', $donation->status, $donation);
        }

        global $wpdb;

        /** @todo Implement $force_delete == false. Keep the original donation status in meta, then update the donation status to "trash" */
//        if( !!$force_delete ) {
        $res = !(
            $wpdb->delete($wpdb->prefix.'leyka_donations_meta', array('donation_id' => $donation_id), array('%d')) === false
            || $wpdb->delete($wpdb->prefix.'leyka_donations', array('ID' => $donation_id), array('%d')) === false
        );
//        } else {
//            $wpdb->update();
//        }

        return $res;

    }

    public function set_donation_meta($donation_id, $meta_key, $value) {

        global $wpdb;

        $meta_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->prefix}leyka_donations_meta WHERE donation_id=%d AND meta_key=%s LIMIT 0,1",
            array($donation_id, $meta_key)
        ));

        if($meta_id) { // Meta exists
            return !!$wpdb->update(
                $wpdb->prefix.'leyka_donations_meta', array('meta_value' => trim($value)),
                array('meta_id' => $meta_id),
                array('%s'), array('%d')
            );
        } else {
            return !!$wpdb->insert(
                $wpdb->prefix.'leyka_donations_meta',
                array('donation_id' => $donation_id, 'meta_key' => $meta_key, 'meta_value' => $value),
                array('%d', '%s', '%s')
            );
        }

    }

    public function get_donation_meta($donation_id, $meta_key) {

        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}leyka_donations_meta WHERE donation_id=%d AND meta_key=%s LIMIT 0,1",
            array($donation_id, $meta_key)
        ));

    }

    public function get_donation_id_by_meta_value($meta_key, $value) {

        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT donation_id FROM {$wpdb->prefix}leyka_donations_meta WHERE meta_key='%s' AND meta_value='%s' LIMIT 1",
            array($meta_key, $value)
        ));

    }

}