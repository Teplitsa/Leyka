<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations_Factory extends Leyka_Singleton {

    protected static $_instance = null;

    /** @todo Factory must be a donations objects data storage (an object cache pattern). */

    /**
     * @return static
     */
    public static function get_instance() {

        if(null === static::$_instance) {

            if(in_array(get_option('leyka_donations_storage_type'), array('sep', 'sep-incompleted'))) {

                require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-separated.php');
                static::$_instance = new Leyka_Separated_Donations_Factory();

            } else {

                require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-post.php');
                static::$_instance = new Leyka_Posts_Donations_Factory();

            }

        }

        return static::$_instance;

    }

    /**
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @return Leyka_Donation|null
     */
    abstract public function get_donation($donation);

    /**
     * @param int|WP_Post|Leyka_Donation_Base $donation
     * @param string $data_field
     * @return mixed
     */
    public function get_donation_field($donation, $data_field) {

        $donation = $this->get_donation($donation);

        return $donation ? $donation->$data_field : null;

    }

    /**
     * @param int|WP_Post|Leyka_Donation $donation
     * @param string $data_field
     * @param string $data_value
     * @return mixed
     */
    public function set_donation_field($donation, $data_field, $data_value) {

        $data_field = trim($data_field);
        $donation = $this->get_donation($donation);

        return $donation ? ($donation->$data_field = $data_value) : false;

    }

    /**
     * @param $params array
     * @return array|Leyka_Donation_Base|boolean Either an array of Leyka_Donation_Base objects, or single object (if get_single param is set), or false if no donations found.
     */
    abstract public function get_donations(array $params = array());

    /**
     * @param $params array
     * @return integer|WP_Error An ID of the new donation, WP_Error object if there was an error in the process
     */
    abstract public function add_donation(array $params = array());

    protected function _getMultipleFilterValues($values, array $possible_values_list) {

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

class Leyka_Posts_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function get_donation($donation) {

        $donation = new Leyka_Donation_Post($donation);

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    public function get_donations(array $params = array()) {

        $query = new WP_Query(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));

        if( !empty($params['status']) ) {

            $values_list = $this->_getMultipleFilterValues($params['status'], leyka_get_donation_status_list());

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

        $meta_query = array('relation' => 'AND');

        if(isset($params['recurring_active'])) {

            $meta_query[] = array('key' => '_rebilling_is_active', 'value' => !!$params['recurring_active'] ? '1' : '0');
            $params['payment_type'] = 'rebill';

        }

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $meta_query[] = array('key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '>');
            } else if($params['amount_filter'] === 'only-') {
                $meta_query[] = array('key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '<');
            } else if(stripos($params['amount_filter'], '>=') !== false) {
                $meta_query[] = array(
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('>=', '', $params['amount_filter']),
                    'compare' => '>=',
                );
            } else if(stripos($params['amount_filter'], '<=') !== false) {
                $meta_query[] = array(
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('<=', '', $params['amount_filter']),
                    'compare' => '<=',
                );
            }

        }

        if( !empty($params['campaign_id']) ) {
            $meta_query[] = array('key' => 'leyka_campaign_id', 'value' => (int)$params['campaign_id']);
        }

        if( !empty($params['payment_type']) ) {

            $values_list = $this->_getMultipleFilterValues($params['payment_type'], leyka_get_payment_types_list());

            if($values_list) {
                $meta_query[] = array('key' => 'leyka_payment_type', 'value' => (array)$values_list, 'compare' => 'IN');
            }

        }

        if( !empty($params['gateway_pm']) ) {

            if(strpos($params['gateway_pm'], 'gateway__') !== false) {
                $meta_query[] = array(
                    'key' => 'leyka_gateway',
                    'value' => str_replace('gateway__', '', $params['gateway_pm'])
                );
            } else if(strpos($params['gateway_pm'], 'pm__') !== false) {
                $meta_query[] = array(
                    'key' => 'leyka_payment_method',
                    'value' => str_replace('pm__', '', $params['gateway_pm'])
                );
            }

        }

        if( !empty($params['gateway_id']) && leyka_get_gateway_by_id($params['gateway_id']) ) {
            $meta_query[] = array('key' => 'leyka_gateway', 'value' => $params['gateway_id']);
        }

        if( !empty($params['pm_id']) && leyka_get_pm_by_id($params['pm_id']) ) {
            $meta_query[] = array('key' => 'leyka_payment_method', 'value' => $params['pm_id']);
        }

        if( !empty($params['pm_full_id']) && leyka_get_pm_by_id($params['pm_full_id'], true) ) {

            $params['pm_full_id'] = explode('-', $params['pm_full_id']);
            $meta_query[] = array('key' => 'leyka_gateway', 'value' => $params['pm_full_id'][0]);
            $meta_query[] = array('key' => 'leyka_payment_method', 'value' => $params['pm_full_id'][1]);

        }

        // Custom donations meta filter. E.g. 'custom_meta__cp_transaction_id' => '12345':
        foreach($params as $param_name => $value) {
            if(stripos($param_name, 'custom_meta_') !== false) {
                $meta_query[] = array('key' => trim(str_replace('custom_meta_', '', $param_name)), 'value' => $value,);
            }
        }

        if(count($meta_query) > 1) {
            $query->set('meta_query', $meta_query);
        }

        $status_filter = function(){ return 'post_status ASC'; }; // For status ordering

        if( !empty($params['orderby']) ) {
            switch($params['orderby']) {
                case 'ID': $query->set('orderby', 'ID'); break;
                case 'date': $query->set('orderby', 'date'); break;
                case 'amount':
                    $query->set('meta_key', 'leyka_donation_amount');
                    $query->set('orderby', 'meta_value_num');
                    break;
                case 'status':
                    add_filter('posts_orderby', $status_filter);
                    $query->set('suppress_filters', false);
                    break;
                default:
            }
        }
        if( !empty($params['order']) && in_array($params['order'], array('asc', 'desc',)) ) {
            $query->set('order', mb_strtoupper($params['order']));
        }

        $res = $query->get_posts();

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

        return $res ? $res : false;

    }

    public function add_donation(array $params = array()) {
        return Leyka_Donation_Post::add($params);
    }

}

class Leyka_Separated_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function get_donation($donation) {

        $donation = new Leyka_Donation_Separated($donation);

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    public function get_donations(array $params = array()) {

        global $wpdb;

        $where = array();
        $limit = '';
        $orderby = '';
        $joins = array();
        $join_meta = false;

        /** @todo $params['search_string'] */

        $params['strict'] = isset($params['strict']) ? !!$params['strict'] : true;

        if( !empty($params['recurring_only_init']) ) {

            $params['payment_type'] = 'rebill';
            $join_meta = true;

            $where[] = $wpdb->prepare("(d_meta.`meta_key` = %s AND d_meta.meta_value = %d)", 'init_recurring_donation_id', 0);

        }

        if( !empty($params['recurring_active']) ) {

            $params['payment_type'] = 'rebill';
            $join_meta = true;

            $where[] = $wpdb->prepare("(d_meta.`meta_key` = %s AND d_meta.meta_value = %d)", 'recurring_active', 1);

        }

        foreach($params as $key => $value) {

            if(stripos($key, 'custom_meta_') === false) {
                continue;
            }

            $join_meta = true;

            $where[] = $wpdb->prepare(
                "(d_meta.`meta_key` = %s AND d_meta.meta_value = %s)",
                str_replace('custom_meta_', '', $key), $value
            );

        }

        if( !empty($params['status']) ) {

            $values_list = $this->_getMultipleFilterValues($params['status'], leyka_get_donation_status_list());

            if($values_list) {

                $where_status = "d.`status` IN (";
                foreach($values_list as $status) {
                    $where_status .= '%s,';
                }
                $where_status = rtrim($where_status, ',').')';

                $where['status'] = $wpdb->prepare($where_status, $values_list);

            }

        }

        if( !empty($params['payment_type']) ) {

            $values_list = $this->_getMultipleFilterValues($params['payment_type'], leyka_get_payment_types_list());

            if($values_list) {

                $where_payment_type = "d.`payment_type` IN (";
                foreach($values_list as $type) {
                    $where_payment_type .= '%s,';
                }
                $where_payment_type = rtrim($where_payment_type, ',').')';

                $where['payment_type'] = $wpdb->prepare($where_payment_type, $values_list);

            }

        }

        if( !empty($params['campaign_id']) ) {
            $where['campaign_id'] = $wpdb->prepare("d.`campaign_id` = %d", (int)$params['campaign_id']);
        }

        if( !empty($params['get_single']) ) {
            $limit = ' LIMIT 0,1';
        } else if( !empty($params['page']) && (int)$params['page'] > 0 && (int)$params['results_limit'] > 0 ) {
            $limit = ' LIMIT '.((int)$params['page']*(int)$params['results_limit']).','.(int)$params['results_limit'];
        } else if( !empty($params['results_limit']) && (int)$params['results_limit'] > 0 ) {
            $limit = ' LIMIT 0,'.(int)$params['results_limit'];
        }

        if(isset($params['year_month']) && (int)$params['year_month'] > 0 && mb_strlen($params['year_month']) >= 6) {

            $year = mb_substr($params['year_month'], 0, 4);
            $month = mb_substr($params['year_month'], 4, 2);

            try {

                $date = new DateTime("$year-$month-01");
                $where['date_ctreated'] = $wpdb->prepare(
                    '(d.`date_created` >= %s AND d.`date_created` <= %s)',
                    $date->format('Y-m-01'), $date->format('Y-m-t')
                );

            } catch(Exception $ex) {
                // ...
            }

        }

        if(isset($params['day']) && (int)$params['day'] >= 1 && (int)$params['day'] <= 31) {
            $where['day_created'] = $wpdb->prepare("DAYOFMONTH(d.`date_created`) = %d", (int)$params['day']);
        }

        if( !empty($params['gateway_pm']) ) {

            if(strpos($params['gateway_pm'], 'gateway__') !== false) {

                $params['gateway_pm'] = str_replace('gateway__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['gateway_id'] = $wpdb->prepare("d.`gateway_id` = %s", $params['gateway_pm']);
                }

            } else if(strpos($params['gateway_pm'], 'pm__') !== false) {

                $params['gateway_pm'] = str_replace('pm__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_pm_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $where['pm_id'] = $wpdb->prepare("d.`pm_id` = %s", $params['gateway_pm']);
                }

            }

        }

        if( !empty($params['gateway_id']) ) {
            if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_id'])) || !$params['strict'] ) {
                $where['gateway_id'] = $wpdb->prepare("d.`gateway_id` = %s", $params['gateway_id']);
            }
        }

        if( !empty($params['pm_id']) ) {
            if( ($params['strict'] && leyka_get_pm_by_id($params['pm_id'])) || !$params['strict'] ) {
                $where['pm_id'] = $wpdb->prepare("d.`pm_id` = %s", $params['pm_id']);
            }
        }

        if( !empty($params['pm_full_id']) ) {
            if( ($params['strict'] && leyka_get_pm_by_id($params['pm_full_id'], true)) || !$params['strict'] ) {

                $params['pm_full_id'] = explode('-', $params['pm_full_id']);
                $where['gateway_id'] = $wpdb->prepare("d.`gateway_id` = %s", $params['pm_full_id'][0]);
                $where['pm_id'] = $wpdb->prepare("d.`pm_id` = %s", $params['pm_full_id'][1]);

            }
        }

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $where['amount'] = "d.`amount` > 0.0";
            } else if($params['amount_filter'] === 'only-') {
                $where['amount'] = "d.`amount` < 0.0";
            } else if(stripos($params['amount_filter'], '>=') !== false) {
                $where['amount'] = $wpdb->prepare("d.`amount` >= %f", round(str_replace('>=', '', $params['amount_filter']), 2));
            } else if(stripos($params['amount_filter'], '<=') !== false) {
                $where['amount'] = $wpdb->prepare("d.`amount` <= %f", round(str_replace('<=', '', $params['amount_filter']), 2));
            }

        }

        if( !empty($params['orderby']) && in_array($params['orderby'], array('ID', 'date', 'amount', 'status')) ) {

            $params['orderby'] = $params['orderby'] === 'date' ? 'd.`date_created`' : 'd.`'.$params['orderby'].'`';
            $params['order'] = empty($params['order']) || !in_array($params['order'], array('asc', 'desc')) ?
                'ASC' : mb_strtoupper($params['order']);

            $orderby = " ORDER BY {$params['orderby']} {$params['order']}";

        }

        if($join_meta) {
            $joins['donations_meta'] = " JOIN `{$wpdb->prefix}leyka_donations_meta` d_meta ON d.`ID` = d_meta.`donation_id`";
        }
        $joins = $joins ? implode(' ', $joins) : '';
        $where = $where ? ' WHERE '.implode(' AND ', $where) : '';
        $limit = $limit ? $limit : '';

        $donations = array();
        $query = $wpdb->prepare("SELECT d.`ID` FROM {$wpdb->prefix}leyka_donations d $joins $where $orderby $limit", array());
//        echo '<pre>'.print_r($query, 1).'</pre>';

        foreach($wpdb->get_col($query) as $donation) {
            $donations[] = $this->get_donation($donation->ID);
        }

        return $donations;

    }

    public function add_donation(array $params = array()) {
        return Leyka_Donation_Separated::add($params);
    }

}