<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donation_Base {

    protected $_id;

    /** @var WP_Post */
    protected $_main_data;

    protected $_donation_meta = array();

    abstract public function __construct($donation);

    /**
     * @param $params array
     * @return int|WP_Error A new Donation ID or WP_Error.
     */
    public static function add(array $params = array()) { // Static method can't be abstract, so it's just empty
        return 0;
    }

    /**
     * Helper to add a copy of given Donation.
     *
     * @param $original Leyka_Donation_Base
     * @param $params array An array of Donation params to rewrite in the clone.
     * @param $settings array Cloning operation settings array.
     * @return Leyka_Donation_Base|WP_Error A new Donation object or WP_Error object if there were some errors while adding it.
     */
    public static function add_clone(Leyka_Donation_Base $original, array $params = array(), array $settings = array()) {

        $settings = array_merge(array('recalculate_total_amount' => false,), $settings);

        $new_donation_id = static::add(array_merge(array(
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

    abstract public function add_gateway_response($response);

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

    /** Donation metadata get & set methods are public to use them in the "gateway-specific data" hooks. */
    abstract public function get_meta($meta_name);
    abstract public function set_meta($meta_name, $value);

    abstract public function delete($force = false);

}