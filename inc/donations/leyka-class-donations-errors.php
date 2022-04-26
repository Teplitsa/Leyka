<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donations Errors library.
 **/

class Leyka_Donations_Errors extends Leyka_Singleton {

    protected static $_instance = null;

    // A special Donation error ID for the defaultest of all default cases -
    // when literally nothing is known of error reason or circumstances:
    const UNKNOWN_ERROR_ID = 'L-0000';

    protected $_errors = [];
    protected $_all_errors_docs_link = '';

    protected function __construct() {

        $this->_all_errors_docs_link = apply_filters(
            'leyka_donations_errors_docs_link',
            'https://leyka.te-st.ru/docs/donations-errors/'
        );

        $this->add_error('L-1023', __('Leyka is unavailable', 'leyka'), [
            'description' => __("Leyka wasn't available at the moment of the transaction handling", 'leyka'),
        ]);
        $this->add_error('L-4001', __('Transaction was cancelled by the merchant side', 'leyka'));
        $this->add_error('L-4002', __('Transaction was declined', 'leyka'));
        $this->add_error('L-5001', __('Issuing bank for the card is not found or unavailable', 'leyka'));
        $this->add_error('L-5002', __('Issuing bank for the card refused to process the transaction', 'leyka'));
        $this->add_error('L-5003', __('Acquirer refused to make a transaction without a reason given', 'leyka'));
        $this->add_error('L-5043', __('Fraud suspicion', 'leyka'));
        $this->add_error('L-6001', __('Card is blocked (all operations are off limits)', 'leyka'));
        $this->add_error('L-7001', __("CVV/CVC code isn't correct", 'leyka'));
        $this->add_error('L-7002', __("3D Secure Authentication isn't passed", 'leyka'));
        $this->add_error('L-7003', __('Incorrect bank card number', 'leyka'));
        $this->add_error('L-7004', __('Card has expired, or its expiry date is incorrect', 'leyka'));
        $this->add_error('L-7005', __('Insufficient funds on bank card', 'leyka'));
        $this->add_error('L-7011', __('Card is lost', 'leyka'));
        $this->add_error('L-7021', __('Card has been reported as stolen', 'leyka'));
        $this->add_error('L-9001', __('Payment with selected method was refused without a reason given', 'leyka'));
        $this->add_error('L-9004', __('The network refused to make the transaction', 'leyka'));

        $this->_errors = apply_filters('leyka_donations_errors', $this->_errors);

    }

    public function __get($field) {

        switch($field) {
            case 'all_errors_docs_link':
                return $this->_all_errors_docs_link;
            default:
                return '';
        }

    }

    public function get_errors() {
        return $this->_errors;
    }

    /**
     * @param $error_id string Either Leyka system Donation error ID, or Gateway system error ID/code
     * @param string|Leyka_Gateway|false $gateway If Leyka_Gateway object given, it will be used for error ID search. If string given, it will be taken as a Gateway ID. Default value is false - to search in all Gatewqys.
     * @return Leyka_Donation_Error
     */
    public function get_error_by_id($error_id, $gateway = false) {

        $error_id = esc_attr($error_id);

        if(empty($this->_errors[$error_id])) { // Gateway error ID given - try to find & return the respective Leyka error object

            $leyka_error_id = false;

            if(is_a($gateway, 'Leyka_Gateway')) {
                $leyka_error_id = $gateway->get_donation_error_id($error_id);
            } else if($gateway && is_string($gateway)) { // Gateway ID given

                $gateway = leyka_get_gateway_by_id($gateway);
                if($gateway) {
                    $leyka_error_id = $gateway->get_donation_error_id($error_id);
                }

            } else { // Search in all Gateways

                foreach(leyka_get_gateways() as $gateway) { /** @var $gateway Leyka_Gateway */

                    $leyka_error_id = $gateway->get_donation_error_id($error_id);

                    if($leyka_error_id) {
                        break;
                    }

                }

            }

            if($leyka_error_id && $this->_errors[$leyka_error_id]) {
                return $this->_errors[$leyka_error_id];
            }

        } else { // Leyka error ID given - return the error object from the Library
            return $this->_errors[$error_id];
        }

        return new Leyka_Donation_Error(self::UNKNOWN_ERROR_ID, __('Unknown error', 'leyka'));

    }

    /**
     * @return boolean True if new error was successfully added, false otherwise.
     */
    public function add_error($leyka_error_id, $error_name, array $error_data = [], $rewrite_existing_error = false) {

        $leyka_error_id = esc_attr($leyka_error_id);
        $error_name = esc_attr($error_name);

        if( !$leyka_error_id || !$error_name ) {
            return false;
        }
        if( !empty($this->_errors[$leyka_error_id]) && !$rewrite_existing_error ) {
            return false;
        }

        $this->_errors[$leyka_error_id] = apply_filters(
            'leyka_donation_error_library_new_entry',
            new Leyka_Donation_Error($leyka_error_id, $error_name, [
                'description' => empty($error_data['description']) ? '' : esc_attr(trim($error_data['description'])),
                'recommendation_admin' => empty($error_data['recommendation_admin']) ?
                    '' : esc_attr(trim($error_data['recommendation_admin'])),
                'recommendation_donor' => empty($error_data['recommendation_donor']) ?
                    '' : esc_attr(trim($error_data['recommendation_donor'])),
                'docs_link' => empty($error_data['docs_link']) ? '' : esc_attr(trim($error_data['docs_link'])),
            ])
        );

        return true;

    }

}

class Leyka_Donation_Error {

    protected $_id; // String
    protected $_name; // String
    protected $_description = ''; // HTML
    protected $_recommendation_admin = ''; // HTML
    protected $_recommendation_donor = ''; // HTML
    protected $_docs_link = ''; // URL

    public function __construct($leyka_error_id, $error_name, array $params = []) {

        $this->_id = esc_attr($leyka_error_id);
        $this->_name = esc_attr($error_name);

        if( !empty($params['description']) ) {
            $this->_description = esc_html($params['description']);
        }
        if( !empty($params['recommendation_admin']) ) {
            $this->_recommendation_admin = esc_html($params['recommendation_admin']);
        }
        if( !empty($params['recommendation_donor']) ) {
            $this->_recommendation_donor = esc_html($params['recommendation_donor']);
        }
        if( !empty($params['docs_link']) ) {
            $this->_description = esc_url($params['docs_link']);
        }

    }

    public function __get($field) {

        switch($field) {
            case 'id':
            case 'ID':
            case 'leyka_id':
            case 'leyka_error_id':
                return $this->_id;

            case 'name':
            case 'title':
            case 'error_name':
            case 'error_title':
                return $this->_name;

            case 'desc':
            case 'description':
            case 'error_desc':
            case 'error_description':
                return $this->_description;

            case 'recommendation_admin':
            case 'recommendation_for_admin':
            case 'admin_recommendation':
                return $this->_recommendation_admin;

            case 'recommendation_donor':
            case 'recommendation_for_donor':
            case 'donor_recommendation':
                return $this->_recommendation_donor;

            case 'docs_link':
            case 'docs_url':
                return $this->_docs_link ? : Leyka_Donations_Errors::get_instance()->all_errors_docs_link.'#'.$this->_id;

            default:
                return '';
        }

    }

}