<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations extends Leyka_Singleton {

    protected static $_instance = null;

    /** @var array An array of Leyka_Donation_Base objects cache */
    protected static $_objects = array();

    /**
     * @return static
     */
    public static function get_instance() {

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
     * @return Leyka_Donation_Base|null
     */
    public function get_donation($donation) {

        $donation_id = $this->_get_donation_id($donation);
        if(empty(self::$_objects[$donation_id])) {
            self::$_objects[$donation_id] = $this->_get_donation($donation);
        }

        return self::$_objects[$donation_id];

    }

    /**
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @return int|false
     */
    protected static function _get_donation_id($donation) {

        if((is_int($donation) || is_string($donation)) && absint($donation) > 0) {
            return absint($donation);
        } else if(is_a($donation, 'WP_Post')) {

            /** @var $donation WP_Post */
            if($donation->post_type !== Leyka_Donation_Management::$post_type) {
                return false;
            }

            return $donation->ID;

        } else if(is_a($donation, 'Leyka_Donation_Base')) {
            return $donation->id;
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
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @param string $field_name
     * @return mixed
     */
//    public function get_donation_field($donation, $field_name) {
//
//        $donation = $this->get_donation($donation);
//
//        return $donation ? $donation->$field_name : null;
//
//    }
//
//    /**
//     * @param int|WP_Post|Leyka_Donation_Base $donation
//     * @param string $field_name
//     * @param string $field_value
//     * @return mixed
//     */
//    public function set_donation_field($donation, $field_name, $field_value) {
//
//        $field_name = trim($field_name);
//        $donation = $this->get_donation($donation);
//
//        return $donation ? ($donation->$field_name = $field_value) : false;
//
//    }

    /**
     * @param $params array
     * @return array|Leyka_Donation_Base|boolean Either an array of Leyka_Donation_Base objects, or single object (if get_single param is set), or false if no donations found.
     */
    abstract public function get(array $params = array());

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

        $new = Leyka_Donations::get_instance()->get_donation($new_donation_id);

        if( // If the original donation was made before the commission was set, apply a commission to the cloned one
            $settings['recalculate_total_amount']
            && $original->amount == $original->amount_total
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

        } else if(stripos($values, ',') !== false) { // Comma-separated values list

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

}

class Leyka_Donations_Posts extends Leyka_Donations {

    protected static $_instance = null;

    protected function _get_donation($donation) {

        $donation = new Leyka_Donation_Post($donation);

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    protected function _get_query(array $params = array()) {

        $query = new WP_Query(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? array() : $params['meta'];

        if( !empty($params['status']) ) {

            $values_list = $this->_get_multiple_filter_values($params['status'], leyka_get_donation_status_list());

            if($values_list) {
                $query->set('post_status', $values_list);
            }

        }

        if( !empty($params['results_limit']) && (int)$params['results_limit'] > 0 ) {
            $query->set('posts_per_page', (int)$params['results_limit']);
        }

        if( !empty($params['get_single']) ) {
            $query->set('posts_per_page', 1);
        } else if( !empty($params['page']) && (int)$params['results_limit'] > 1 ) {
            $query->set('page', (int)$params['page']);
        }

        if(isset($params['year_month']) && (int)$params['year_month'] > 0) {
            $query->set('m', (int)$params['year_month']);
        }

        if(isset($params['day']) && (int)$params['day'] >= 1 && (int)$params['day'] <= 31) {
            $query->set('day', (int)$params['day']);
        }

        if( !empty(trim($params['search_string'])) ) {
            $query->set('s', trim($params['search_string']));
        }

        if( !empty($params['recurring_only_init']) ) {

            $query->set('post_parent', 0);
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

            $values_list = $this->_get_multiple_filter_values($params['payment_type'], leyka_get_payment_types_data());

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

            $query->set('meta_query', $params['meta']);

        }

        if( !empty($params['order']) && in_array($params['order'], array('asc', 'desc',)) ) {
            $query->set('order', mb_strtoupper($params['order']));
        }

        if( !empty($params['orderby']) ) {
            switch($params['orderby']) {
                case 'ID': $query->set('orderby', 'ID'); break;
                case 'date': $query->set('orderby', 'date'); break;
                case 'amount':
                    $query->set('meta_key', 'leyka_donation_amount');
                    $query->set('orderby', 'meta_value_num');
                    break;
                case 'status': // Post status ordering is handled manually in the get() method.
                default:
            }
        }

        return $query;

    }

    public function get(array $params = array()) {

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

}

class Leyka_Donations_Separated extends Leyka_Donations {

    protected static $_instance = null;

    protected function _get_donation($donation) {

        $donation = new Leyka_Donation_Separated($donation);

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
        $limit = '';

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? array() : $params['meta'];

        /** @todo Implement $params['search_string'] handling. */

        $params['strict'] = isset($params['strict']) ? !!$params['strict'] : true;

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

        if( !empty($params['payment_type']) ) {

            $values_list = $this->_get_multiple_filter_values($params['payment_type'], leyka_get_payment_types_data());

            if($values_list) {

                $where_payment_type = "{$wpdb->prefix}leyka_donations.payment_type IN (";
                foreach($values_list as $type) {
                    $where_payment_type .= '%s,';
                }
                $where_payment_type = rtrim($where_payment_type, ',').')';

                $where['payment_type'] = $wpdb->prepare($where_payment_type, $values_list);

            }

        }

        if( !empty($params['campaign_id']) ) {
            $where['campaign_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.campaign_id = %d", absint($params['campaign_id']));
        } else if( !empty($params['campaigns_ids']) && is_array($params['campaigns_ids']) ) {

            $params['campaigns_ids'] = array_map(
                function($campaign_id){ return absint($campaign_id); },
                $params['campaigns_ids']
            );

            $where['campaign_id'] = '{$wpdb->prefix}leyka_donations.campaign_id IN ('.implode(',', $params['campaigns_ids']).')';

        }

        if( !empty($params['get_single']) ) {
            $limit = ' LIMIT 0,1';
        } else if( !empty($params['page']) && (int)$params['page'] > 0 && (int)$params['results_limit'] > 0 ) {
            $limit = ' LIMIT '.(($params['page']-1)*(int)$params['results_limit']).','.(int)$params['results_limit'];
        } else if( !empty($params['results_limit']) && (int)$params['results_limit'] > 0 ) {
            $limit = ' LIMIT 0,'.(int)$params['results_limit'];
        }

        if(isset($params['year_month']) && (int)$params['year_month'] > 0 && mb_strlen($params['year_month']) >= 6) {

            $year = mb_substr($params['year_month'], 0, 4);
            $month = mb_substr($params['year_month'], 4, 2);

            try {

                $date = new DateTime("$year-$month-01");
                $where['date_ctreated'] = $wpdb->prepare(
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

            if(strpos($params['gateway_pm'], 'gateway__') !== false) {

                $params['gateway_pm'] = str_replace('gateway__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['gateway_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.gateway_id = %s", $params['gateway_pm']);
                }

            } else if(strpos($params['gateway_pm'], 'pm__') !== false) {

                $params['gateway_pm'] = str_replace('pm__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_pm_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['pm_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.pm_id = %s", $params['gateway_pm']);
                }

            }

        }

        if( !empty($params['gateway_id']) ) {
            if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_id'])) || !$params['strict'] ) {
                $where['gateway_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.gateway_id = %s", $params['gateway_id']);
            }
        }

        if( !empty($params['pm_id']) ) {
            if( ($params['strict'] && leyka_get_pm_by_id($params['pm_id'])) || !$params['strict'] ) {
                $where['pm_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.pm_id = %s", $params['pm_id']);
            }
        }

        if( !empty($params['pm_full_id']) ) {
            if( ($params['strict'] && leyka_get_pm_by_id($params['pm_full_id'], true)) || !$params['strict'] ) {

                $params['pm_full_id'] = explode('-', $params['pm_full_id']);
                $where['gateway_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.gateway_id = %s", $params['pm_full_id'][0]);
                $where['pm_id'] = $wpdb->prepare("{$wpdb->prefix}leyka_donations.pm_id = %s", $params['pm_full_id'][1]);

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

        if( !empty($params['orderby']) && $this->_is_orderable_by($params['orderby']) ) {

            $params['orderby'] = $params['orderby'] === 'date' ? '{$wpdb->prefix}leyka_donations.date_created' : "{$wpdb->prefix}leyka_donations.".$params['orderby'];
            $params['order'] = empty($params['order']) || !in_array($params['order'], array('asc', 'desc')) ?
                'ASC' : mb_strtoupper($params['order']);

            $query['orderby'] = " ORDER BY {$params['orderby']} {$params['order']}";

        }

        if( !empty($params['recurring_only_init']) ) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = array('key' => 'init_recurring_donation_id', 'value' => 0,);

        }

        if( !empty($params['recurring_active']) ) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = array('key' => 'recurring_active', 'value' => 1,);

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

    public function get(array $params = array()) {

        global $wpdb;

        $query = $this->_get_query_parts($params);

        $donations = array();
        $query = "SELECT {$wpdb->prefix}leyka_donations.ID FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']} {$query['orderby']} {$query['limit']}";

        foreach($wpdb->get_col($query) as $donation_id) {
            $donations[] = $this->get_donation($donation_id);
        }

        return empty($params['get_single']) ? $donations : reset($donations);

    }

    public function get_count(array $params = array()) {

        global $wpdb;

        $query = $this->_get_query_parts($params);
        $query = "SELECT COUNT({$wpdb->prefix}leyka_donations.ID) FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']} {$query['orderby']} {$query['limit']}";

        return absint($wpdb->get_var($query));

    }

    protected function _is_orderable_by($param_name) {
        return in_array(mb_strtolower($param_name), array('id', 'campaign_id', 'status', 'date', 'date_created', 'gateway_id', 'pm_id', 'amount', 'donor_name', 'donor_email'));
    }

    public function add(array $params = array(), $return_object = false) {

        $donation = Leyka_Donation_Separated::add($params);

        return $return_object && !is_wp_error($donation) ? new Leyka_Donation_Separated($donation) : $donation;

    }

    public function delete_donation($donation_id, $force_delete = false) {

        global $wpdb;

        /** @todo Implement $force_delete == false. Keep the original donation status in meta, then update the donation status to "trash" */
//        if( !!$force_delete ) {
        return !!$wpdb->delete($wpdb->prefix.'leyka_donations', array('ID' => absint($donation_id)));
//        } else {
//            $wpdb->update();
//        }

    }

}