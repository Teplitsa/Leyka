<?php if( !defined('WPINC') ) die;

abstract class Leyka_Donation_Base {

    protected $_id;
    protected $_main_data;
    protected $_donation_meta = [];

    abstract public function __construct($donation);

    /** @todo Add the list of possible $field values */
    abstract public function __get($field);
    /** @todo Add the list of possible $field & $value values */
    abstract public function __set($field, $value);

    /**
     * @param $params array
     * @return int|WP_Error A new Donation ID or WP_Error.
     */
    public static function add(array $params = []) { // Static method can't be abstract, so it's just empty
        return 0;
    }

    /**
     * New Donation params pre-handling.
     *
     * @param array $params
     * @return array|WP_Error
     */
    protected static function _handle_new_donation_params(array $params = []) {

        $params['force_insert'] = !empty($params['force_insert']);

        $params['amount'] = empty($params['amount']) ? leyka_pf_get_amount_value() : round((float)$params['amount'], 2);
        $params['amount'] = $params['amount'] ? $params['amount'] : 0.0;
        if( !$params['amount'] && !$params['force_insert'] ) {
            return new WP_Error('incorrect_amount_given', __('Empty or incorrect amount given while trying to add a donation', 'leyka'));
        }

        $params['status'] = empty($params['status']) || !array_key_exists($params['status'], leyka_get_donation_status_list()) ?
            'submitted' : $params['status'];

        $params['payment_type'] = empty($params['payment_type']) || !leyka_get_payment_types_list($params['payment_type']) ?
            'single' : $params['payment_type'];

        // Donor's name:
        $params['donor_name'] = empty($params['donor_name']) ? leyka_pf_get_donor_name_value() : $params['donor_name'];
        if(
            $params['donor_name']
            && !leyka_validate_donor_name($params['donor_name'])
            && !$params['force_insert']
            && $params['payment_type'] !== 'correction'
        ) {
            return new WP_Error('incorrect_donor_name', __('Incorrect donor name given while adding a donation', 'leyka'));
        } else if(is_email($params['donor_name'])) {
            $params['donor_name'] = apply_filters('leyka_donor_name_email_given', __('Anonymous', 'leyka'));
        } else if( !$params['donor_name'] ) {
            $params['donor_name'] = apply_filters('leyka_donor_name_none_given', __('Anonymous', 'leyka'));
        }
        $params['donor_name'] = htmlentities($params['donor_name'], ENT_QUOTES, 'UTF-8');
        // Donor's name - END

        // Donor's email:
        $params['donor_email'] = empty($params['donor_email']) ? leyka_pf_get_donor_email_value() : $params['donor_email'];
        $params['donor_email'] = trim($params['donor_email']);

        if(
            !$params['force_insert']
            && $params['payment_type'] !== 'correction'
            && ( !$params['donor_email'] || !leyka_is_email($params['donor_email']) )
        ) {
            return new WP_Error('incorrect_donor_email', __('Incorrect donor email given while adding a donation', 'leyka'));
        }
        // Donor's email - END

        $params['donor_comment'] = empty($params['donor_comment']) ?
            leyka_pf_get_donor_comment_value() : $params['donor_comment'];
        $params['donor_comment'] = sanitize_textarea_field($params['donor_comment']);

        $params['donor_subscribed'] = isset($params['donor_subscribed']) ?
            $params['donor_subscribed'] : leyka_pf_get_donor_subscribed_value();

        $params['campaign_id'] = empty($params['campaign_id']) ?
            leyka_pf_get_campaign_id_value() : absint($params['campaign_id']);

        $params['date_created'] = empty($params['date_created']) ? false : $params['date_created'];

        // Recurring-only params:
        $params['init_recurring_donation'] = empty($params['init_recurring_donation']) ?
            (empty($params['init_recurring_donation_id']) ? 0 : absint($params['init_recurring_donation_id'])) :
            absint($params['init_recurring_donation']);

        if($params['payment_type'] === 'rebill' && !$params['init_recurring_donation']) {

            $params['recurring_active'] =
                !empty($params['rebilling_is_active']) ||
                !empty($params['rebilling_on']) ||
                !empty($params['recurring_active']) ||
                !empty($params['recurring_is_active']) ||
                !empty($params['recurring_on']);

        }

        $params['recurring_cancelled'] = !empty($params['recurring_cancelled']);
        $params['recurring_cancel_date'] = empty($params['recurring_cancel_date']) ?
            (empty($params['recurrents_cancel_date']) ? 0 : $params['recurrents_cancel_date']) :
            $params['recurring_cancel_date'];

        $params['recurring_cancel_requested'] = isset($params['recurring_cancel_requested']) ?
            !!$params['recurring_cancel_requested'] :
            (isset($params['cancel_recurring_requested']) ? !!$params['cancel_recurring_requested'] : NULL);

        $params['recurring_cancel_reason'] = empty($params['recurring_cancel_reason']) ?
            (empty($params['recurrents_cancel_reason']) ? '' : trim($params['recurrents_cancel_reason'])) :
            trim($params['recurring_cancel_reason']);
        // Recurring-only params - END

        // Gateway & PM IDs:
        if(empty($params['pm_id']) && empty($params['gateway_id'])) { // Try to get Gateway & PM from $_POST

            $pm_data = leyka_pf_get_payment_method_value();
            $params['pm_id'] = $pm_data['payment_method_id'];
            $params['gateway_id'] = $pm_data['gateway_id'];

        } else { // Get Gateway & PM data from $params

            $params['gateway_id'] = empty($params['gateway_id']) ? '' : $params['gateway_id'];
            if( !$params['gateway_id'] || !leyka_get_gateway_by_id($params['gateway_id']) ) {
                $params['gateway_id'] = 'correction';
            }

            $params['pm_id'] = empty($params['pm_id']) ?
                (empty($params['payment_method_id']) ? '' : $params['payment_method_id']) :
                $params['pm_id'];

        }

        $pm_data = leyka_pf_get_payment_method_value();
        $pm_data = $pm_data ?
            $pm_data :
            [
                'payment_method_id' => empty($params['pm_id']) ?
                    (empty($params['payment_method_id']) ? '' : $params['payment_method_id']) :
                    $params['pm_id'],
                'gateway_id' => empty($params['gateway_id']) ? '' : $params['gateway_id'],
            ];

        $pm_full_id = $pm_data['gateway_id'].'-'.$pm_data['payment_method_id'];

        // Gateway ID may be empty (for custom payment info cases):
        if( !$params['force_insert'] && empty($pm_data['payment_method_id']) ) {
            return new WP_Error('donation_addition_error', __('Gateway or PM ID is missing while adding a donation', 'leyka'));
        }

        if( !$params['force_insert'] && $params['gateway_id'] !== 'correction' && !leyka_get_pm_by_id($pm_full_id, true)) {
            return new WP_Error('donation_addition_error', __('Incorrect gateway or PM ID given while adding a donation', 'leyka'));
        }
        // Gateway & PM IDs - END

        // Currency:
        $params['currency_id'] = empty($params['currency_id']) ?
            (empty($params['currency']) ? '' : mb_strtolower($params['currency'])) : mb_strtolower($params['currency_id']);
        $params['currency_id'] = $params['currency_id'] ? $params['currency_id'] : leyka_pf_get_currency_value();

        $params['currency_id'] = empty($params['currency_id']) || !leyka_get_currencies_data($params['currency_id']) ?
            'RUB' : mb_strtoupper($params['currency_id']);

        $currency_rate = $params['currency_id'] == 'RUB' ?
            1.0 : (float)leyka_options()->opt('currency_rur2'.mb_strtolower($params['currency_id']));
        if( !$currency_rate || $currency_rate <= 0.0 ) {
            $currency_rate = 1.0;
        }
        // Currency - END

        // Donation total amount (with commission subtracted):
        $params['amount_total'] = empty($params['amount_total']) || !is_numeric($params['amount_total']) ?
            'auto' : round((float)$params['amount_total'], 2);
        if(
            (empty($params['amount_total']) || $params['amount_total'] === 'auto')
            && ( !empty($pm_data['payment_method_id']) && !empty($pm_data['gateway_id']) )
        ) {
            $params['amount_total'] = leyka_calculate_donation_total_amount(false, $params['amount'], $pm_full_id);
        }

        $params['amount_in_main_currency'] = empty($params['amount_in_main_currency']) ?
            $params['amount']*$currency_rate : round((float)$params['amount_in_main_currency'], 2);
        $params['amount_total_in_main_currency'] = empty($params['amount_total_in_main_currency']) ?
            $params['amount_total']*$currency_rate : round((float)$params['amount_total_in_main_currency'], 2);
        // Donation total amount - END

        // Additional fields:
        $params['additional_fields'] = empty($params['additional_fields']) || !is_array($params['additional_fields']) ?
            [] : $params['additional_fields'];
        if($params['additional_fields']) {
            array_walk($params['additional_fields'], function( &$value ){ $value = trim($value); });
        }
        // Additional fields - END

        // Donor user ID:
        $params['donor_user_id'] = empty($params['donor_user_id']) ? 0 : absint($params['donor_user_id']);

        if(
            (leyka_options()->opt('donors_management_available') || leyka_options()->opt('donor_accounts_available'))
            && !$params['donor_user_id']
        ) {

            if(leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE)) { // Use the current user ID, if it has a donor role
                $params['donor_user_id'] = get_current_user_id();
            } else if($params['donor_email']) { // If given email belongs to a Donor account, use account ID

                $donor = get_user_by('email', $params['donor_email']);
                $params['donor_user_id'] = $donor && leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, false, $donor) ?
                    $donor->ID : 0;

            }

        }
        // Donor user ID - END

        $params['payment_title'] = empty($params['purpose_text']) ?
            (empty($params['payment_title']) ? leyka_options()->opt('donation_purpose_text') : $params['payment_title']) :
            $params['purpose_text'];
        $params['payment_title'] = esc_attr($params['payment_title']);

        // Web analytics - GA:
        $params['ga_client_id'] = empty($params['ga_client_id']) ? '' : trim($params['ga_client_id']);

        return $params;

    }

    abstract public function add_gateway_response($response);

    /**
     * @return mixed Last date when status was changed to "funded" in sec, or false if donation was never funded.
     */
    abstract public function get_funded_date();

    /**
     * A wrapper to access gateway's method to get init recurring donation.
     *
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