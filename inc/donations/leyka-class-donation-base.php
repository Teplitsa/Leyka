<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donation_Base {

    protected $_id;

    /** @var WP_Post */
    protected $_main_data;

    protected $_donation_meta = array();

    abstract public function __construct($donation);

    public static function add(array $params = array()) { // Static method can't be abstract, so it's just empty
    }

    abstract public function add_gateway_response($resp_text);

    public function get_specific_data_admin_fields() {

        $data_fields = array();

        $gateway = leyka_get_gateway_by_id($this->gateway_id);
        if($gateway) { /** @var $gateway Leyka_Gateway */
            $data_fields = $gateway->get_specific_data_admin_fields($this->id);
        }

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
    public static function get_init_recurring_donation($donation) {

        $donation = leyka_get_validated_donation($donation);

        if($donation->type !== 'rebill') {
            return false;
        }

        return leyka_get_gateway_by_id($donation->gateway_id)->get_init_recurring_donation($donation);

    }

    abstract public function delete($force = false);

}