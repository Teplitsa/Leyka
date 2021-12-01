<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations extends Leyka_Singleton {

    protected static $_instance = null;

    /** @var array An array of Leyka_Donation_Base objects cache */
    protected static $_objects = [];

    /**
     * @param $params array Isn't in use.
     * @return static
     */
    public static function get_instance(array $params = []) {

        if(null === static::$_instance) {

            if(in_array(get_option('leyka_donations_storage_type'), ['sep', 'sep-incompleted'])) {
                static::$_instance = new Leyka_Donations_Separated();
            } else {
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
        return $this->_get_donation($donation);
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
    abstract public function get_count(array $params = []);

    /**
     * @param $params array
     * @param $return_object boolean True to return a Leyka_Donation_Base object, false to return just a new donation ID.
     * @return integer|WP_Error An ID of the new donation, WP_Error object if there was an error in the process
     */
    abstract public function add(array $params = [], $return_object = false);

    /**
     * Helper to add a copy of given Donation.
     *
     * @param $original Leyka_Donation_Base
     * @param $params array An array of Donation params to rewrite in the clone.
     * @param $settings array Cloning operation settings array.
     * @return Leyka_Donation_Base|WP_Error A new Donation object or WP_Error object if there were some errors while adding it.
     */
    public function add_clone(Leyka_Donation_Base $original, array $params = [], array $settings = []) {

        $settings = array_merge(['recalculate_total_amount' => false,], $settings);

        $new_donation_id = $this->add(array_merge([
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
        ], $params));

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

        $values_to_filter = [];
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
            $values_to_filter = [trim($values)];
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

        try{
            $donation = new Leyka_Donation_Post($donation);
        } catch(Exception $ex) {
            $donation = false;
        }

        return is_a($donation, 'Leyka_Donation_Base') && $donation->id ? $donation : false;

    }

    protected function _get_query(array $params = []) {

        $query_params = [
            'post_type' => Leyka_Donation_Management::$post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'cache_results' => defined('WP_CACHE') ? WP_CACHE : true,
            'update_post_meta_cache' => defined('WP_CACHE') ? WP_CACHE : true,
            'update_post_term_cache' => defined('WP_CACHE') ? WP_CACHE : true,
        ];

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? [] : $params['meta'];

        $params['strict'] = isset($params['strict']) ? !!$params['strict'] : true;

        // Donation ID filtering:
        $params['donation_id'] = empty($params['donations_ids']) ?
            (empty($params['donation_id']) ? [] : $params['donation_id']) :
            $params['donations_ids'];

        if($params['donation_id']) {

            $params['donation_id'] = is_array($params['donation_id']) ? $params['donation_id'] : [$params['donation_id']];

            $query_params['post__in'] = array_filter($params['donation_id'], function($donation_id){
                return absint($donation_id);
            });

        }
        // Donation ID filtering - END

        // Status filtering:
        if( !empty($params['status']) ) {

            $values_list = $this->_get_multiple_filter_values($params['status'], leyka_get_donation_status_list());

            if($values_list) {
                $query_params['post_status'] = $values_list;
            }

        }
        // Status filtering - END

        // Donor user ID filtering:
        $params['donor_id'] = empty($params['donors_ids']) ?
            (empty($params['donor_id']) ? [] : $params['donor_id']) :
            $params['donors_ids'];

        if($params['donor_id']) {

            $params['donor_id'] = is_array($params['donor_id']) ? $params['donor_id'] : [$params['donor_id']];

            $query_params['author__in'] = array_filter($params['donor_id'], function($donor_id){ return absint($donor_id); });

        }
        // Donor user ID filtering - END

        // Donor name & email filtering:
        if( !empty($params['donor_name_email']) ) {

            if(mb_stristr($params['donor_name_email'], '%') === false) {

                $params['meta'][] = [
                    'relation' => 'OR',
                    ['key' => 'leyka_donor_name', 'value' => $params['donor_name_email'],],
                    ['key' => 'leyka_donor_email', 'value' => $params['donor_name_email'],],
                ];

            } else {

                $params['donor_name_email'] = trim($params['donor_name_email'], '%');

                $params['meta'][] = [
                    'relation' => 'OR',
                    ['key' => 'leyka_donor_name', 'value' => $params['donor_name_email'], 'compare' => 'LIKE',],
                    ['key' => 'leyka_donor_email', 'value' => $params['donor_name_email'], 'compare' => 'LIKE',],
                ];

            }

        }

        if( !empty($params['donor_email']) && mb_stristr($params['donor_email'], '@') ) {
            $params['meta'][] = ['key' => 'leyka_donor_email', 'value' => $params['donor_email']];
        }
        // Donor name & email filtering - END

        // Results limiting:
        if( !empty($params['results_limit']) && absint($params['results_limit']) ) {
            $query_params['posts_per_page'] = absint($params['results_limit']);
        }

        if( !empty($params['get_single']) ) {
            $query_params['posts_per_page'] = 1;
        } else if( !empty($params['page']) && absint($params['results_limit']) > 1 ) {
            $query_params['paged'] = absint($params['page']);
        }
        // Results limiting - END

        // Donation date filtering:
        if(isset($params['year_month']) && absint($params['year_month'])) {
            $query_params['m'] = absint($params['year_month']);
        }

        if(isset($params['day']) && (int)$params['day'] >= 1 && (int)$params['day'] <= 31) {
            $query_params['day'] = (int)$params['day'];
        }

        $query_params['date_query'] = isset($params['date_query']) && is_array($params['date_query']) ?
            $params['date_query'] : [];

        if(isset($params['date_from'])) {

            $date = DateTime::createFromFormat('d.m.Y', $params['date_from']);
            $date = $date ? $date : DateTime::createFromFormat('Y-m-d', $params['date_from']);

            if($date) {
                $query_params['date_query'][] = ['after' => $date->format('Y-m-d 00:00:00'), 'inclusive' => true,];
            }

        }
        if(isset($params['date_to'])) {

            $date = DateTime::createFromFormat('d.m.Y', $params['date_to']);
            $date = $date ? $date : DateTime::createFromFormat('Y-m-d', $params['date_to']);

            if($date) {
                $query_params['date_query'][] = ['before' => $date->format('Y-m-d 23:59:59'), 'inclusive' => true,];
            }

        }
        // Donation date filtering - END

        if( !empty($params['recurring_only_init']) ) {

            $query_params['post_parent'] = 0;
            $params['payment_type'] = 'rebill';

        }

        if(isset($params['recurring_active'])) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = ['key' => '_rebilling_is_active', 'value' => !!$params['recurring_active']];

        }

        if(isset($params['recurring_rebills_of']) && absint($params['recurring_rebills_of'])) {

            $params['payment_type'] = 'rebill';
            $query_params['post_parent'] = absint($params['recurring_rebills_of']);

        }

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $params['meta'][] = ['key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '>'];
            } else if($params['amount_filter'] === 'only-') {
                $params['meta'][] = ['key' => 'leyka_donation_amount', 'value' => 0, 'compare' => '<'];
            } else if(mb_stripos($params['amount_filter'], '>=') !== false) {
                $params['meta'][] = [
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('>=', '', $params['amount_filter']),
                    'compare' => '>=',
                ];
            } else if(mb_stripos($params['amount_filter'], '<=') !== false) {
                $params['meta'][] = [
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('<=', '', $params['amount_filter']),
                    'compare' => '<=',
                ];
            }

        }

        // Campaign ID filtering:
        $params['campaign_id'] = empty($params['campaigns_ids']) ?
            (empty($params['campaign_id']) ? [] : $params['campaign_id']) :
            $params['campaigns_ids'];

        if($params['campaign_id']) {

            $params['campaign_id'] = is_array($params['campaign_id']) ? $params['campaign_id'] : [$params['campaign_id']];
            $params['campaign_id'] = array_filter($params['campaign_id'], function($campaign_id){ return absint($campaign_id); });

            if($params['campaign_id']) {
                $params['meta'][] = ['key' => 'leyka_campaign_id', 'value' => $params['campaign_id'], 'compare' => 'IN',];
            }

        }
        // Campaign ID filtering - END

        if( !empty($params['payment_type']) ) {

            if(is_array($params['payment_type']) && in_array('rebill-init', $params['payment_type'])) {

                unset($params['payment_type'][ array_search('rebill-init', $params['payment_type']) ]);

                if( !in_array('rebill', $params['payment_type']) ) {
                    $params['payment_type'][] = 'rebill';
                }

            } else if($params['payment_type'] == 'rebill-init') {

                $query_params['post_parent'] = 0;
                $params['payment_type'] = 'rebill';

            }

            $values_list = $this->_get_multiple_filter_values($params['payment_type'], leyka_get_payment_types_list());

            if($values_list) {
                $params['meta'][] = ['key' => 'leyka_payment_type', 'value' => (array)$values_list, 'compare' => 'IN'];
            }

        }

        // Gateway & PM filtering:
        if( !empty($params['gateway_pm']) ) {

            if(mb_stripos($params['gateway_pm'], 'gateway__') !== false) {

                $params['gateway_pm'] = str_replace('gateway__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $params['gateway_id'] = $params['gateway_pm'];
                }

            } else if(mb_stripos($params['gateway_pm'], 'pm__') !== false) {

                $params['gateway_pm'] = str_replace('pm__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_pm_by_id($params['gateway_pm'])) || !$params['strict'] ) {
                    $params['pm_id'] = $params['gateway_pm'];
                }

            } else if(mb_stripos($params['gateway_pm'], '-') !== false) { // PM full ID given
                $params['pm_full_id'] = $params['gateway_pm'];
            } else {
                $params['gateway_id'] = $params['gateway_pm'];
            }

        }

        if( !empty($params['gateway_id']) ) {

            $params['gateway_id'] = is_array($params['gateway_id']) ? $params['gateway_id'] : [$params['gateway_id']];
            $values_list = [];

            foreach($params['gateway_id'] as $gateway_id) {
                if(($params['strict'] && leyka_get_gateway_by_id($gateway_id)) || !$params['strict']) {
                    $values_list[] = $gateway_id;
                }
            }

            if($values_list) {
                $params['meta'][] = ['key' => 'leyka_gateway', 'value' => (array)$values_list, 'compare' => 'IN'];
            }

        }

        if( !empty($params['pm_id']) ) {

            $params['pm_id'] = is_array($params['pm_id']) ? $params['pm_id'] : [$params['pm_id']];
            $values_list = [];

            foreach($params['pm_id'] as $pm_id) {
                if(($params['strict'] && leyka_get_pm_by_id($pm_id)) || !$params['strict']) {
                    $values_list[] = $pm_id;
                }
            }

            if($values_list) {
                $params['meta'][] = ['key' => 'leyka_payment_method', 'value' => $values_list, 'compare' => 'IN'];
            }

        }

        if(empty($params['gateway_id']) && empty($params['pm_id']) && !empty($params['pm_full_id']) ) {

            $params['pm_full_id'] = is_array($params['pm_full_id']) ? $params['pm_full_id'] : [$params['pm_full_id']];
            $values_list = ['relation' => 'OR',];

            foreach($params['pm_full_id'] as $pm_full_id) {

                if(($params['strict'] && leyka_get_pm_by_id($pm_full_id, true)) || !$params['strict']) {

                    $pm_full_id = explode('-', $pm_full_id);
                    if(count($pm_full_id) !== 2) {
                        continue;
                    }

                    $values_list[] = [
                        'relation' => 'AND',
                        ['key' => 'leyka_gateway', 'value' => $pm_full_id[0],],
                        ['key' => 'leyka_payment_method', 'value' => $pm_full_id[1],],
                    ];

                }

                if(count($values_list) > 1) {
                    $params['meta'][] = $values_list;
                }

            }

        }
        // Gateway & PM filtering - END

        if(isset($params['donor_subscribed'])) {
            $params['meta'][] = $params['donor_subscribed'] ?
                ['key' => 'leyka_donor_subscribed', 'compare' => '>=', 'value' => 1,] :
                ['key' => 'leyka_donor_subscribed', 'compare' => 'NOT EXISTS'];
        }

        if(count($params['meta'])) {

            if( empty($params['meta']['relation']) || !in_array($params['meta']['relation'], ['AND', 'OR']) ) {
                $params['meta']['relation'] = 'AND';
            }

            $query_params['meta_query'] = $params['meta'];

        }

        $query_params['order'] = empty($params['order']) || !in_array($params['order'], ['asc', 'ASC', 'desc', 'DESC',]) ?
            'DESC' : mb_strtoupper($params['order']);

        $params['orderby'] = empty($params['orderby']) ? 'date' : $params['orderby'];

        switch($params['orderby']) {
            case 'id':
            case 'ID':
            case 'donation_id':
                $query_params['orderby'] = 'ID'; break;
            case 'campaign_id':
                $query_params['meta_key'] = 'leyka_campaign_id';
                $query_params['orderby'] = 'meta_value_num';
                break;
            case 'status': break; // Post status ordering is handled manually in the get() method.
            case 'date':
            case 'date_created':
                $query_params['orderby'] = 'date'; break;
            case 'gateway':
            case 'gateway_id':
                $query_params['meta_key'] = 'leyka_gateway';
                $query_params['orderby'] = 'meta_value';
                break;
            case 'pm':
            case 'pm_id':
            case 'payment_method':
                $query_params['meta_key'] = 'leyka_payment_method';
                $query_params['orderby'] = 'meta_value';
                break;
            case 'amount':
                $query_params['meta_key'] = 'leyka_donation_amount';
                $query_params['orderby'] = 'meta_value_num';
                break;
            case 'donor_name':
                $query_params['meta_key'] = 'leyka_donor_name';
                $query_params['orderby'] = 'meta_value';
                break;
            case 'donor_email':
                $query_params['meta_key'] = 'leyka_donor_email';
                $query_params['orderby'] = 'meta_value';
                break;
            case 'type':
            case 'payment_type':
                $query_params['meta_key'] = 'leyka_payment_type';
                $query_params['orderby'] = 'meta_value';
                break;

            default:
        }

        // TODO: При очень большом кол-ве записей о пожертвованиях WP_Query отдает ошибку если
        //  $query_params['posts_per_page'] === -1. Разобраться ограничение это WP, баг или бутылочное горлышко
        //  производительности серверов.
        $query_params['posts_per_page'] = $query_params['posts_per_page'] === -1 ? 9999 : $query_params['posts_per_page'];

        return new WP_Query($query_params);

    }

    public function get($params) {

        if((is_int($params) || is_string($params)) && absint($params)) { // Int given - return the single Donation
            return $this->get_donation(absint($params));
        }

        // Array given - return a Donations selection:

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

        return $res;

    }

    public function get_count(array $params = []) {
        return $this->_get_query($params)->found_posts;
    }

    public function add(array $params = [], $return_object = false) {

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
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s LIMIT 1",
            [$meta_key, $value]
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

    protected function _get_date_query_parts($date_params) {

        $date_query = new Leyka_Donations_Date_Query($date_params);

        return $date_query->get_sql();

    }

    protected function _get_query_parts(array $params = []) {

        global $wpdb;

        $query = ['fields' => '', 'from' => '', 'joins' => '', 'where' => '', 'orderby' => '', 'limit' => '',];

        $joins = [];
        $where = [];

        $params['meta'] = empty($params['meta']) || !is_array($params['meta']) ? [] : $params['meta'];

        $params['strict'] = isset($params['strict']) ? !!$params['strict'] : true;

        // Donor name & email filtering:
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

        if( !empty($params['donor_email']) && mb_stristr($params['donor_email'], '@') ) {

            $where['donor_email'] = $wpdb->prepare(
                "({$wpdb->prefix}leyka_donations.donor_email = %s)",
                trim($params['donor_email'])
            );

        }
        // Donor name & email filtering - END

        // Donation ID filtering:
        $params['donation_id'] = isset($params['donations_ids']) ?
            $params['donations_ids'] : (isset($params['donation_id']) ? $params['donation_id'] : false);

        if($params['donation_id'] !== false) {

            $params['donation_id'] = is_array($params['donation_id']) ? $params['donation_id'] : [$params['donation_id']];
            $params['donation_id'] = array_filter($params['donation_id'], function($donation_id){
                return absint($donation_id);
            });

            $where['donation_id'] = "{$wpdb->prefix}leyka_donations.ID IN (".implode(',', $params['donation_id']).")";

        }
        // Donation ID filtering - END

        // Donor ID filtering:
        $params['donor_id'] = isset($params['donors_ids']) ?
            $params['donors_ids'] : (isset($params['donor_id']) ? $params['donor_id'] : false);

        if($params['donor_id'] !== false) {

            $params['donor_id'] = is_array($params['donor_id']) ? $params['donor_id'] : [$params['donor_id']];
            $params['donor_id'] = array_filter($params['donor_id'], function($donor_id){
                return $donor_id === 0 ? true : absint($donor_id); // donor_id === 0 is possible (for Donations w/o Donors)
            });

            $where['donor_id'] = "{$wpdb->prefix}leyka_donations.donor_user_id IN (".implode(',', $params['donor_id']).")";

        }
        // Donor ID filtering - END

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

        // Recurring Donations filtering:
        if( !empty($params['recurring_only_init']) ) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = ['key' => 'init_recurring_donation_id', 'value' => 0,];

        }

        if(isset($params['recurring_active']) ) {

            $params['payment_type'] = 'rebill';

            if($params['recurring_active']) {
                $params['meta'][] = ['key' => 'recurring_active', 'value' => 1,];
            } else {
                $params['meta'][] = [
                    'relation' => 'OR',
                    ['key' => 'recurring_active', 'compare' => 'NOT EXISTS',],
                    ['key' => 'recurring_active', 'value' => false,]
                ];
            }

        }

        if(isset($params['recurring_rebills_of']) && absint($params['recurring_rebills_of'])) {

            $params['payment_type'] = 'rebill';
            $params['meta'][] = ['key' => 'init_recurring_donation_id', 'value' => absint($params['recurring_rebills_of']),];

        }
        // Recurring Donations filtering - END

        // Payment type filtering:
        if( !empty($params['payment_type']) ) {

            if(is_array($params['payment_type']) && in_array('rebill-init', $params['payment_type'])) {

                unset($params['payment_type'][ array_search('rebill-init', $params['payment_type']) ]);

                if( !in_array('rebill', $params['payment_type']) ) {
                    $params['payment_type'][] = 'rebill';
                }

            } else if($params['payment_type'] == 'rebill-init') {

                $params['payment_type'] = 'rebill';
                $params['meta'][] = ['key' => 'init_recurring_donation_id', 'value' => 0,];

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

        } // Payment type filtering - END

        // Campaign ID filtering:
        $params['campaign_id'] = empty($params['campaigns_ids']) ?
            (empty($params['campaign_id']) ? [] : $params['campaign_id']) :
            $params['campaigns_ids'];

        if($params['campaign_id']) {

            $params['campaign_id'] = is_array($params['campaign_id']) ? $params['campaign_id'] : [$params['campaign_id']];
            $params['campaign_id'] = array_filter($params['campaign_id'], function($campaign_id){ return absint($campaign_id); });

            $where['campaign_id'] = "{$wpdb->prefix}leyka_donations.campaign_id IN (".implode(',', $params['campaign_id']).")";

        }
        // Campaign ID filtering - END

        $params['results_limit'] = empty($params['results_limit']) ?
            20 : ($params['results_limit'] > 0 ? (int)$params['results_limit'] : 20);

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

            $where['day_created'] = $wpdb->prepare(
                "DAYOFMONTH({$wpdb->prefix}leyka_donations.date_created) = %d", absint($params['day'])
            );

        }

        if( !empty($params['date_query']) ) { // Traditional (a la WP_Query) 'date_query' filter

            $date_query_where_part = $this->_get_date_query_parts($params['date_query']);

            if($date_query_where_part) {
                $where[] = substr_replace($date_query_where_part, '', 0, mb_strlen(' AND '));
            }

        }

        // Gateway & PM filtering:
        if( !empty($params['gateway_pm']) ) {

            if(mb_stripos($params['gateway_pm'], 'gateway__') !== false) {

                $params['gateway_pm'] = str_replace('gateway__', '', $params['gateway_pm']);
                if( ($params['strict'] && leyka_get_gateway_by_id($params['gateway_pm'])) || !$params['strict'] ) {

                    $where['gateway_id'] = $wpdb->prepare(
                        "{$wpdb->prefix}leyka_donations.gateway_id = %s", $params['gateway_pm']
                    );

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

            $params['gateway_id'] = is_array($params['gateway_id']) ? $params['gateway_id'] : [$params['gateway_id']];
            $query_params = [];

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

            $params['pm_id'] = is_array($params['pm_id']) ? $params['pm_id'] : [$params['pm_id']];
            $query_params = [];

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

            $params['pm_full_id'] = is_array($params['pm_full_id']) ? $params['pm_full_id'] : [$params['pm_full_id']];
            $query_params = [];

            foreach($params['pm_full_id'] as $pm_full_id) {
                if(($params['strict'] && leyka_get_pm_by_id($pm_full_id, true)) || !$params['strict']) {

                    $pm_full_id = explode('-', $pm_full_id);
                    if(count($pm_full_id) !== 2) {
                        continue;
                    }

                    $query_params[] = $wpdb->prepare(
                        "({$wpdb->prefix}leyka_donations.gateway_id = %s AND {$wpdb->prefix}leyka_donations.pm_id = %s)", $pm_full_id[0],
                        $pm_full_id[1]
                    );

                }
            }

            if(count($query_params)) {
                $where['pm_full_id'] = '('.implode(' OR ', $query_params).')';
            }

        }
        // Gateway & PM filtering - END

        if( !empty($params['amount_filter']) ) {

            $params['amount_filter'] = trim($params['amount_filter']);

            if($params['amount_filter'] === 'only+') {
                $where['amount'] = "{$wpdb->prefix}leyka_donations.amount > 0.0";
            } else if($params['amount_filter'] === 'only-') {
                $where['amount'] = "{$wpdb->prefix}leyka_donations.amount < 0.0";
            } else if(stripos($params['amount_filter'], '>=') !== false) {
                $where['amount'] = $wpdb->prepare(
                    "{$wpdb->prefix}leyka_donations.amount >= %f", round(str_replace('>=', '', $params['amount_filter']), 2)
                );
            } else if(stripos($params['amount_filter'], '<=') !== false) {
                $where['amount'] = $wpdb->prepare(
                    "{$wpdb->prefix}leyka_donations.amount <= %f", round(str_replace('<=', '', $params['amount_filter']), 2)
                );
            }

        }

        $params['order'] = empty($params['order']) || !in_array($params['order'], ['asc', 'ASC', 'desc', 'DESC',]) ?
            'DESC' : mb_strtoupper($params['order']);

        if(empty($params['orderby'])) {
            $params['orderby'] = 'date';
        }
        if($this->_is_orderable_by($params['orderby'])) {

            switch($params['orderby']) {
                case 'donation_id': $params['orderby'] = "{$wpdb->prefix}leyka_donations.ID"; break;
                case 'gateway': $params['orderby'] = "{$wpdb->prefix}leyka_donations.gateway_id"; break;
                case 'pm':
                case 'payment_method':
                    $params['orderby'] = "{$wpdb->prefix}leyka_donations.pm_id"; break;
                case 'date': $params['orderby'] = "{$wpdb->prefix}leyka_donations.date_created"; break;
                case 'type': $params['orderby'] = "{$wpdb->prefix}leyka_donations.payment_type"; break;
                default: $params['orderby'] = "{$wpdb->prefix}leyka_donations.".$params['orderby']; break;
            }

            $query['orderby'] = " ORDER BY {$params['orderby']} {$params['order']}";

        }

        if(isset($params['donor_subscribed'])) {
            $params['meta'][] = $params['donor_subscribed'] ?
                ['key' => 'donor_subscribed', 'compare' => '>=', 'value' => 1] :
                ['key' => 'donor_subscribed', 'compare' => 'NOT EXISTS'];
        }

        if($params['meta']) {

            $date_query_where_part = $this->_get_meta_query_parts($params['meta']);

            if($date_query_where_part['join'] && $date_query_where_part['where']) {

                $joins[] = $date_query_where_part['join'];
                $where[] = substr_replace($date_query_where_part['where'], '', 0, mb_strlen(' AND '));

            }

        }

        $query['fields'] = "{$wpdb->prefix}leyka_donations.".(empty($params['get_ids_only']) ? '*' : 'ID');
        $query['joins'] = $joins ? implode(' ', $joins) : '';
        $query['where'] = $where ? ' WHERE '.implode(' AND ', $where) : '';
        $query['limit'] = $limit ? : '';

        return $query;

    }

    public function get($params) {

        if((is_int($params) || is_string($params)) && absint($params)) { // Int given - return the single Donation
            return $this->get_donation(absint($params));
        }

        // Array given - return a Donations selection:

        global $wpdb;

        $query = $this->_get_query_parts($params);

        $donations = [];
        $query = "SELECT {$query['fields']} FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']} {$query['orderby']} {$query['limit']}";

        if(empty($params['get_ids_only'])) {
            foreach($wpdb->get_results($query) as $donation) {
                $donations[] = $this->get_donation($donation);
            }
        } else {
            $donations = $wpdb->get_results($query);
        }

        return empty($params['get_single']) ? $donations : reset($donations);

    }

    public function get_count(array $params = []) {

        global $wpdb;

        $query = $this->_get_query_parts($params);
        $query = "SELECT COUNT({$wpdb->prefix}leyka_donations.ID) FROM {$wpdb->prefix}leyka_donations {$query['joins']} {$query['where']}";

        return absint($wpdb->get_var($query));

    }

    protected function _is_orderable_by($param_name) {
        return in_array(
            mb_strtolower($param_name),
            ['id', 'donation_id', 'campaign_id', 'status', 'date', 'date_created', 'gateway', 'gateway_id', 'pm', 'pm_id', 'payment_method', 'amount', 'donor_name', 'donor_email', 'type', 'payment_type',]
        );
    }

    public function add(array $params = [], $return_object = false) {

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
            $wpdb->delete($wpdb->prefix.'leyka_donations_meta', ['donation_id' => $donation_id], ['%d']) === false
            || $wpdb->delete($wpdb->prefix.'leyka_donations', ['ID' => $donation_id], ['%d']) === false
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
            [$donation_id, $meta_key]
        ));

        if($meta_id) { // Meta exists
            return !!$wpdb->update(
                $wpdb->prefix.'leyka_donations_meta', ['meta_value' => trim($value)],
                ['meta_id' => $meta_id],
                ['%s'], ['%d']
            );
        } else {
            return !!$wpdb->insert(
                $wpdb->prefix.'leyka_donations_meta',
                ['donation_id' => $donation_id, 'meta_key' => $meta_key, 'meta_value' => $value],
                ['%d', '%s', '%s']
            );
        }

    }

    public function get_donation_meta($donation_id, $meta_key) {

        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}leyka_donations_meta WHERE donation_id=%d AND meta_key=%s LIMIT 0,1",
            [$donation_id, $meta_key]
        ));

    }

    public function get_donation_id_by_meta_value($meta_key, $value) {

        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT donation_id FROM {$wpdb->prefix}leyka_donations_meta WHERE meta_key=%s AND meta_value=%s LIMIT 1",
            [$meta_key, $value]
        ));

    }

}