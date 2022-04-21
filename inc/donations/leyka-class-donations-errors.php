<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donations Errors library.
 **/

class Leyka_Donations_Errors extends Leyka_Singleton {

    protected static $_instance = null;

    protected $_errors = [];
    protected $_all_errors_docs_link = '';

    protected function __construct() {

        $this->_all_errors_docs_link = apply_filters(
            'leyka_donations_errors_docs_link',
            'https://leyka.te-st.ru/docs/donations-errors/'
        );

        $this->_errors = apply_filters(
            'leyka_donations_errors',
            [
                'L-1023' => [
                    'name' => __('Leyka is unavailable', 'leyka'),
                    'description' => __("Leyka wasn't available at the moment of the transaction handling", 'leyka'),
                    'recommendation_admin' => __('', 'leyka'), // "Свяжитесь с тех. поддержкой Лейки (чат в тг / почта / обратная форма, со ссылками) и сообщите о вашей проблеме, включая код этой ошибки и описание ситуации, в которой она возникла."
//                    'recommendation_donor' => __('', 'leyka'),
//                    'docs_link' => '', // Pass the docs link here, if it's unique.
                        // By default, single error docs link is $this->_all_errors_docs_link.'#'.mb_strtolower($error_id)
                ],
                'L-5001' => [
                    'name' => __('Issuing bank for the card is not found or unavailable', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-5002' => [
                    'name' => __('Issuing bank for the card refused to process the transaction', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-5043' => [
                    'name' => __('Fraud suspicion', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-7001' => [
                    'name' => __("CVV/CVC code isn't correct", 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-7002' => [
                    'name' => __("3D Secure Authentication isn't passed", 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-7003' => [
                    'name' => __('Incorrect bank card number', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-7004' => [
                    'name' => __('Card has expired, or its expiry date is incorrect', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-7005' => [
                    'name' => __('Insufficient funds on bank card', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-9001' => [
                    'name' => __('Unknown system error', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
                'L-9004' => [
                    'name' => __('The network refused to make the transaction', 'leyka'),
                    'description' => __('', 'leyka'),
                    'recommendation_admin' => __('', 'leyka'),
                ],
//                '' => [
//                    'name' => __('', 'leyka'),
//                    'description' => __('', 'leyka'),
//                    'recommendation_admin' => __('', 'leyka'),
//                ],
            ]
        );

    }

    public function get_errors() {
        return $this->_errors;
    }

    public function get_error_by_id($error_id) {

        $error_id = esc_attr(trim($error_id));

        return empty($this->_errors[$error_id]) ? false : $this->_errors[$error_id];

    }

    /**
     * @return boolean True if new error was successfully added, false otherwise.
     */
    public function add_error(array $error_data, $rewrite_existing_error = false) {

        if( !array_key_exists('id', $error_data) || !array_key_exists('name', $error_data) ) {
            return false;
        }
        if(array_key_exists($error_data['id'], $this->_errors) && !$rewrite_existing_error) {
            return false;
        }

        $this->_errors[$error_data['id']] = [
            'name' => esc_attr(trim($error_data['name'])),
            'description' => empty($error_data['description']) ? '' : esc_attr(trim($error_data['description'])),
            'recommendation_admin' => empty($error_data['recommendation_admin']) ?
                '' : esc_attr(trim($error_data['recommendation_admin'])),
            'recommendation_donor' => empty($error_data['recommendation_donor']) ?
                '' : esc_attr(trim($error_data['recommendation_donor'])),
            'docs_link' => empty($error_data['docs_link']) ? '' : esc_attr(trim($error_data['docs_link'])),
        ];

        return true;

    }

}