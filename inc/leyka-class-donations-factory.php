<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations_Factory extends Leyka_Singleton {

    protected static $_instance = null;

    /** @todo Make the factory a donations data storage (an object cache pattern). */

    /**
     * @return static
     */
    public static function get_instance() {

        if(null === static::$_instance) {

            if( in_array(get_option('leyka_donations_storage_type'), array('sep', 'sep-incompleted')) ) {
                static::$_instance = new Leyka_Separated_Donations_Factory();
            } else {
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
    abstract public function getDonationData($donation, $data_field);

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

}

class Leyka_Posts_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function getDonation($donation) {

        $donation = new Leyka_Donation($donation);

        return is_a($donation, 'Leyka_Donation') && $donation->id ? $donation : false;

    }

    public function getDonationData($donation, $data_field) {

        $donation = $this->getDonation($donation);

        return $donation ? $donation->$data_field : null;

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

            $donations_statuses = leyka_get_donation_status_list();

            if(is_array($params['status'])) { // Statuses list as an array

                $statuses_to_filter = array();
                foreach($params['status'] as $status) {
                    if($status && array_key_exists(trim($status), $donations_statuses)) {
                        $statuses_to_filter[] = trim($status);
                    }
                }

                if($statuses_to_filter) {
                    $query->set('post_status', $statuses_to_filter);
                }

            } else if(array_key_exists(trim($params['status']), $donations_statuses)) { // A single status
                $query->set('post_status', trim($params['status']));
            } else if(stripos($params['status'], ',') !== false) { // Comma-separated statuses list

                $params['status'] = explode(',', $params['status']);
                $statuses_to_filter = array();
                foreach($params['status'] as $status) {
                    if($status && array_key_exists(trim($status), $donations_statuses)) {
                        $statuses_to_filter[] = trim($status);
                    }
                }

                if($statuses_to_filter) {
                    $query->set('post_status', $statuses_to_filter);
                }

            }

        }

        if( !empty($params['posts_per_page']) ) {

        }
        if( !empty($params['page']) ) {

        }
        if(isset($params['recurring'])) {
            // 'only_init', 'only_non_init',
            // use 'post_parent' => 0,
        }
        if(isset($params['recurring_active'])) {
            // true/false, default NULL
            // use array('key' => '_rebilling_is_active', 'value' => '1', 'compare' => '=',),
        }
        if(isset($params['month'])) {
            // use 'm' => $_GET['month-year']
        }
        if(isset($params['day'])) {
            // use 'day' => (int)date('j')
        }
        if(isset($params['search_string'])) {
            // use 's' => $_GET['search_string']
        }

        $meta_query = array('relation' => 'AND');

        if( !empty($params['amount_filter']) ) {
            // 'only+', 'only-', '>=SOME_AMOUNT'
        }

        if( !empty($params['campaign_id']) ) {
            $meta_query[] = array('key' => 'leyka_campaign_id', 'value' => (int)$params['campaign_id']);
        }

        if( !empty($params['payment_type']) && array_key_exists($params['payment_type'], leyka_get_payment_types_list()) ) {
            $meta_query[] = array('key' => 'leyka_payment_type', 'value' => $params['payment_type']);
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

        /** @todo $parameters:
         * array('key' => '_cp_transaction_id', 'value'   => $cp_transaction_id, 'compare' => '=',), // Custom meta
         */

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

    public function getDonationData($donation, $data_field) {
        /** @todo Implement the method */
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