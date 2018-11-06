<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donations_Factory extends Leyka_Singleton {

    protected static $_instance = null;

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
     * @param $params array
     * @return array Of Leyka_Donation instances
     */
    abstract public function getDonations(array $params = array());

}

class Leyka_Posts_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function getDonation($donation) {
        return new Leyka_Donation($donation);
    }

    public function getDonationData($donation, $data_field) {

    }

    public function getDonations(array $params = array()) {

    }

}

class Leyka_Separated_Donations_Factory extends Leyka_Donations_Factory {

    protected static $_instance = null;

    public function getDonation($donation) {

    }

    public function getDonationData($donation, $data_field) {

    }

    public function getDonations(array $params = array()) {

    }

}