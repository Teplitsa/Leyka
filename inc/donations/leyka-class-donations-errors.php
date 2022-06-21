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
            'description' => __("Leyka wasn't available at the moment of the transaction handling.", 'leyka'),
            'recommendation_admin' => sprintf(__("Contact the Leyka plugin technical support team via <a href='mailto:%s'>%s</a> email. It's important to attach error screenshots and description, how the error appeared.", 'leyka'), 'help@te-st.ru', 'help@te-st.ru'),
            'recommendation_donor' => sprintf(__('Please, try to pay 1-2 days later. If the problem persists then, ask the <a href="mailto:%s" target="_blank">website administration</a> to report this to the gateway technical support.', 'leyka'), leyka_options()->opt('tech_support_email')),
        ]);
        $this->add_error('L-2001', __('No recurring payments on subscription date', 'leyka'), [
            'description' => __('Leyka received no data about recurring payments made on subscription date this month.', 'leyka'),
            'recommendation_admin' => __("Please check if the payment is registered in the gateway system. If it is, then check why Leyka didn't received it's data (most likely you need to set up callbacks settings correctly). If the payment isn't registered in the gateway system, then check if the recurring donations cron-job is set up properly.", 'leyka'),
            'recommendation_donor' => '',
        ]);
        $this->add_error('L-4001', __('Transaction was cancelled by the merchant side', 'leyka'), [
            'description' => __('The transaction request was received by the gateway system, but then it was rejected by the merchant (that is, the website with Leyka). The reason for this behavior is most likely due to technical problems on the website.', 'leyka'),
            'recommendation_admin' => sprintf(__('Turn the WordPress errors logging on (<a href="%s" target="_blank">user manual</a>), then make a test donation via the gateway used in the problematic case earlier.', 'leyka'), 'https://wordpress.org/support/article/debugging-in-wordpress/'),
        ]);
        $this->add_error('L-4002', __('Transaction was declined', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-5001', __('Acquirer bank for the card is not found or unavailable', 'leyka'), [
            'recommendation_admin' => __("Ask the donor to use another payment method (i.e., another card). If this won't help, ask the donor to try to pay 1-2 days later. If the problem will persist, contact the gateway technical support.", 'leyka'),
            'recommendation_donor' => __("Please, try to use another payment method (i.e., another card). If this won't help, please, try to pay 1-2 days later. If the problem will persist, ask the website administration to report this to the gateway technical support.", 'leyka'),
        ]);
        $this->add_error('L-5002', __('Acquirer bank refused to process the transaction', 'leyka'), [
            'recommendation_admin' => __("Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.", 'leyka'),
            'recommendation_donor' => __("Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.", 'leyka'),
        ]);
        $this->add_error('L-5003', __('Acquirer bank refused to make a transaction without a reason given', 'leyka'), [
            'description' => __('Possible causes of the problem:<br>
<ul>
    <li>incorrect CVV code on Mastercard cards;</li>
    <li>internal restrictions of the bank that issued the card;</li>
    <li>the card is blocked or not yet activated;</li>
    <li>Internet payments are not enabled on the card or 3DS is not connected.</li>
</ul>', 'leyka'),
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-5043', __('Fraud suspicion', 'leyka'), [
            'description' => __('The transaction was refused by the acquirer bank due to fraud suspicion.', 'leyka'),
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-6001', __('Card is blocked (all operations are off limits)', 'leyka'), [
            'description' => __('All payments for the card used for the payment are restricted (i.e., the card may be lost).', 'leyka'),
            'recommendation_admin' => __("Ask the donor to use another payment method (i.e., another card). If this won't help, ask the donor to contact the bank that issued the card.", 'leyka'),
            'recommendation_donor' => __("Please, try to use another payment method (i.e., another card). If this won't help, report this issue to the bank that issued the card.", 'leyka'),
        ]);
        $this->add_error('L-7001', __("CVV/CVC code isn't correct", 'leyka'), [
            'recommendation_admin' => __('Ask the donor to check if the card data were entered correctly, or to try to use another card.', 'leyka'),
            'recommendation_donor' => __('Check if the card data were entered correctly, or try to use another card.', 'leyka'),
        ]);
        $this->add_error('L-7002', __("3D Secure Authentication isn't passed", 'leyka'), [
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-7003', __('Incorrect bank card number', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to check if the card data were entered correctly, or to try to use another card.', 'leyka'),
            'recommendation_donor' => __('Check if the card data were entered correctly, or try to use another card.', 'leyka'),
        ]);
        $this->add_error('L-7004', __('Card has expired, or its expiry date is incorrect', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to check if the card data were entered correctly, or to try to use another card.', 'leyka'),
            'recommendation_donor' => __('Check if the card data were entered correctly, or try to use another card.', 'leyka'),
        ]);
        $this->add_error('L-7005', __('Insufficient funds on the bank card used', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to top up their bank card account, or to try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, top up your bank card account, or to try to use another bank card, or use another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-7011', __('The bank card is lost', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-7021', __('Card has been reported as stolen', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
            'recommendation_donor' => __('Please, report the issue to the bank that issued the card, or try to use another bank card, or another payment method altogether.', 'leyka'),
        ]);
        $this->add_error('L-7022', __("Payer's bank card or e-wallet is blocked due to its security breach", 'leyka'), [
            'description' => __('The payment instrument was blocked due to digital security reasons. I.e., bank card may be lost, or e-wallet may be hacked by cyber criminal.', 'leyka'),
            'recommendation_admin' => __('Ask the donor to use another bank card or payment method.', 'leyka'),
            'recommendation_donor' => __('Please, use another bank card or payment method.', 'leyka'),
        ]);
        $this->add_error('L-9001', __('Payment with selected method was refused without a reason given', 'leyka'), [
            'description' => __('Payment with selected method was refused without any given reason. Possible reasons are errors on the side of network or acquirer bank.', 'leyka'),
            'recommendation_admin' => __("Ask the donor to check if the entered card data were correct, or to use another card. If this won't help, ask the donor to contact the bank or organization that issued the payment instrument used (for example, the bank card with which the payment was made).", 'leyka'),
            'recommendation_donor' => __("Please, check if the entered card data were entered correctly, or try to use another card. If this won't help, please, contact the bank or organization that issued the payment instrument used (for example, the bank card with which you tried to pay).", 'leyka'),
        ]);
        $this->add_error('L-9004', __('The network refused to make the transaction', 'leyka'), [
            'recommendation_admin' => __('Ask the donor to check if the card data were entered correctly, or to try to use another card.', 'leyka'),
            'recommendation_donor' => __('Check if the card data were entered correctly, or try to use another card.', 'leyka'),
        ]);

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
    public function add_error($leyka_error_id, $error_name, array $params = [], $rewrite_existing_error = false) {

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
                'description' => empty($params['description']) ? '' : trim($params['description']),
                'recommendation_admin' => empty($params['recommendation_admin']) ? '' : trim($params['recommendation_admin']),
                'recommendation_donor' => empty($params['recommendation_donor']) ? '' : trim($params['recommendation_donor']),
                'docs_link' => empty($params['docs_link']) ? '' : trim($params['docs_link']),
                'error_data' => empty($params['error_data']) || !is_array($params['error_data']) ? [] : $params['error_data'],
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
    protected $_error_data = [];

    public function __construct($leyka_error_id, $error_name, array $params = []) {

        $this->_id = esc_attr($leyka_error_id);
        $this->_name = esc_attr($error_name);

        if( !empty($params['description']) ) {
            $this->_description = $params['description'];
        }
        if( !empty($params['recommendation_admin']) ) {
            $this->_recommendation_admin = $params['recommendation_admin'];
        }
        if( !empty($params['recommendation_donor']) ) {
            $this->_recommendation_donor = $params['recommendation_donor'];
        }
        if( !empty($params['docs_link']) ) {
            $this->_docs_link = esc_url($params['docs_link']);
        }
        if( !empty($params['error_data']) && is_array($params['error_data']) ) {
            $this->_error_data = $params['error_data'];
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

            case 'error_data':
                return $this->_error_data;

            default:
                return '';

        }

    }

}