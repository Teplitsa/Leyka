<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donation_Base {

    protected $_id;

    /** @var WP_Post */
    protected $_main_data;

    protected $_donation_meta = array();

    abstract public function __construct($donation);

    public static function add(array $params = array()) { // static method can't be abstract
    }

    abstract public function add_gateway_response($resp_text);

    public function get_specific_data_admin_fields() {

        $data_fields = leyka_get_gateway_by_id($this->gateway_id)->get_specific_data_admin_fields($this->id);

        return $data_fields ? $data_fields : array();

    }

    /**
     * @return mixed Last date when status was changed to "funded" in sec, or false if donation was never funded.
     */
    abstract public function get_funded_date();

    /**
     * A wrapper to access gateway's method to get init recurrent donation.
     * @param mixed $donation
     * @return mixed Leyka_Donation or false if param is wrong or nothing foundd.
     */
    public static function getInitRecurringDonation($donation) {

        $donation = leyka_get_validated_donation($donation);

        if($donation->type !== 'rebill') {
            return false;
        }

        return leyka_get_gateway_by_id($donation->gateway_id)->get_init_recurrent_donation($donation);

    }
    /** @deprecated */
    public static function get_init_recurrent_donation($donation) {
        return static::getInitRecurringDonation($donation);
    }

    abstract public function delete($force = false);

}