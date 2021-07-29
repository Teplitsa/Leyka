<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donation_Base {

    protected $_id;
    protected $_main_data;
    protected $_donation_meta = array();

    abstract public function __construct($donation);

    /** @todo Add the list of possible $field values */
    abstract public function __get($field);
    /** @todo Add the list of possible $field & $value values */
    abstract public function __set($field, $value);

    /**
     * @param $params array
     * @return int|WP_Error A new Donation ID or WP_Error.
     */
    public static function add(array $params = array()) { // Static method can't be abstract, so it's just empty
        return 0;
    }

    abstract public function add_gateway_response($response);

    /**
     * @return mixed Last date when status was changed to "funded" in sec, or false if donation was never funded.
     */
    abstract public function get_funded_date();

    /**
     * A wrapper to access gateway's method to get init recurring donation.
     * @param int|Leyka_Donation_Base $donation
     * @return Leyka_Donation_Base|false Donation object or false if param is wrong or nothing found.
     */
    public static function get_init_recurring_donation($donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);

        if($donation->type !== 'rebill') {
            return false;
        }

        return leyka_get_gateway_by_id($donation->gateway_id)->get_init_recurring_donation($donation);

    }

    /** Donation metadata get & set methods are public to use them in the "gateway-specific data" hooks. */
    abstract public function get_meta($meta_key);
    abstract public function set_meta($meta_name, $value);
    abstract public function delete_meta($meta_name);

    abstract public function delete($force = false);

}