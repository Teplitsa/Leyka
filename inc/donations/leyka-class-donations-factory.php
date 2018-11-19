<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations_Factory extends Leyka_Singleton {

    protected static $_instance = null;

    /** @todo Factory must be a donations objects data storage (an object cache pattern). */

    /**
     * @return static
     */
    public static function get_instance() {

        if(null === static::$_instance) {

            if( in_array(get_option('leyka_donations_storage_type'), array('sep', 'sep-incompleted')) ) {

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
     * @param int|WP_Post|Leyka_Donation $donation
     * @return Leyka_Donation|null
     */
    abstract public function getDonation($donation);

    /**
     * @param int|WP_Post|Leyka_Donation $donation
     * @param string $data_field
     * @return mixed
     */
    public function getDonationData($donation, $data_field) {

        $donation = $this->getDonation($donation);

        return $donation ? $donation->$data_field : null;

    }

    /**
     * @param int|WP_Post|Leyka_Donation $donation
     * @param string $data_field
     * @param string $data_value
     * @return mixed
     */
    abstract public function setDonationData($donation, $data_field, $data_value);

    /**
     * @param $params array
     * @return array Of Leyka_Donation objects
     */
    abstract public function getDonations(array $params = array());

    /**
     * @param $params array
     * @return integer|WP_Error An ID of the new donation, WP_Error object if there was an error in the process
     */
    abstract public function addDonation(array $params = array());

    protected function _getMultipleFilterValues($values, array $possible_values_list) {

        if(empty($values)) {
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
            $values_to_filter = trim($values);
        }

        return $values_to_filter;

    }

}

class Leyka_Posts_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function getDonation($donation) {

        $donation = new Leyka_Donation($donation);

        return is_a($donation, 'Leyka_Donation') && $donation->id ? $donation : false;

    }

    public function setDonationData($donation, $data_field, $data_value) {

        $data_field = trim($data_field);
        $donation = $this->getDonation($donation);

        return $donation ? ($donation->$data_field = $data_value) : false;

    }

    public function getDonations(array $params = array()) {

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

        if( !empty($params['page']) && (int)$params['posts_per_page'] > 1 ) {
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
            } else if(stripos($params['amount_filter'], '>=')) {
                $meta_query[] = array(
                    'key' => 'leyka_donation_amount',
                    'value' => (int)str_replace('>=', '', $params['amount_filter']),
                    'compare' => '>=',
                );
            } else if(stripos($params['amount_filter'], '<=')) {
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

            $meta_query[] = array('key' => 'leyka_gateway', 'value' => $params['gateway_id']);
            $meta_query[] = array('key' => 'leyka_payment_method', 'value' => $params['pm_id']);

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

    }

    public function addDonation(array $params = array()) {
        return Leyka_Donation::add($params);
    }

}

class Leyka_Separated_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function getDonation($donation) {

        $donation = new Leyka_Donation_Separated($donation);

        return is_a($donation, 'Leyka_Donation') && $donation->id ? $donation : false;

    }

    public function setDonationData($donation, $data_field, $data_value) {
        /** @todo Implement the method */
    }

    public function getDonations(array $params = array()) {
        /** @todo Implement the method */
    }

    public function addDonation(array $params = array()) {
        return Leyka_Donation_Separated::add($params);
    }

}