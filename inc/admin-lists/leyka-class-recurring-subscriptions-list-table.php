<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Recurring_Subscriptions_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Subscription', 'leyka'), 'plural' => __('Subscriptions', 'leyka'), 'ajax' => true,));

        add_filter('leyka_admin_recurring_subscriptions_list_filter', array($this, 'filter_recurring_subscriptions'), 10, 2);

        if( !empty($_REQUEST['recurring-subscriptions-list-export']) ) {
            $this->_export_recurring_subscriptions();
        }

    }

    /**
     * Recurring subscriptions fields filtering.
     *
     * @param $params array
     * @param $filter_type string
     * @return array|false An array of params, or false if the $filter_type is wrong
     */
    public function filter_recurring_subscriptions(array $params, $filter_type = '') {

//        if($filter_type !== 'get_donors') {
//            return false;
//        }

//        $params['meta_query'] = array();
//        if( !empty($_REQUEST['donor-type']) ) {
//            $params['meta_query'][] = array('key' => 'leyka_donor_type', 'value' => esc_sql($_REQUEST['donor-type']),);
//        }
//
//        if( !empty($_REQUEST['donor-name-email']) ) {
//
//            $_REQUEST['donor-name-email'] = trim($_REQUEST['donor-name-email']);
//
//            if($_REQUEST['donor-name-email']) {
//
//                $params['search'] = '*'.esc_sql($_REQUEST['donor-name-email']).'*';
//                $params['search_columns'] = array('ID', 'display_name', 'user_email',);
//
//            }
//
//        }
//
//        if( !empty($_REQUEST['gateways']) ) {
//
//            $gateways_meta_query = array('relation' => 'OR',);
//
//            foreach($_REQUEST['gateways'] as $pm_full_id) {
//                $gateways_meta_query[] = array(
//                    'key' => 'leyka_donor_gateways',
//                    'value' => esc_sql($pm_full_id),
//                    'compare' => 'LIKE',
//                );
//            }
//
//            $params['meta_query'][] = $gateways_meta_query;
//
//        }
//
//        if( !empty($_REQUEST['campaigns']) && !empty($_REQUEST['campaigns'][0]) ) {
//
//            $campaigns_meta_query = array('relation' => 'OR',);
//
//            foreach($_REQUEST['campaigns'] as $campaign_id) {
//                $campaigns_meta_query[] = array(
//                    'key' => 'leyka_donor_campaigns',
//                    'value' => 'i:'.absint($campaign_id).';', // A little freaky, we know, but it's the best we could think of
//                    'compare' => 'LIKE',
//                );
//            }
//
//            $params['meta_query'][] = $campaigns_meta_query;
//
//        }
//
//        if( !empty($_REQUEST['first-donation-date']) ) {
//
//            if(stripos($_REQUEST['first-donation-date'], ',') !== false) { // Dates period chosen
//
//                $_REQUEST['first-donation-date'] = array_slice(explode(',', $_REQUEST['first-donation-date']), 0, 2);
//
//                if(count($_REQUEST['first-donation-date']) === 2) { // The date is set as an interval
//
//                    $_REQUEST['first-donation-date'][0] = strtotime($_REQUEST['first-donation-date'][0].' 00:00:00');
//                    $_REQUEST['first-donation-date'][1] = strtotime($_REQUEST['first-donation-date'][1].' 23:59:59');
//
//                    $params['meta_query'][] = array(
//                        'key' => 'leyka_donor_first_donation_date',
//                        'value' => $_REQUEST['first-donation-date'],
//                        'compare' => 'BETWEEN',
//                        'type' => 'NUMERIC',
//                    );
//
//                }
//
//            } else { // Single date chosen
//                $params['meta_query'][] = array(
//                    'key' => 'leyka_donor_first_donation_date',
//                    'value' => array(
//                        strtotime($_REQUEST['first-donation-date'].' 00:00:00'),
//                        strtotime($_REQUEST['first-donation-date'].' 23:59:59'),
//                    ),
//                    'compare' => 'BETWEEN',
//                    'type' => 'NUMERIC',
//                );
//            }
//
//        }
//
//        if( !empty($_REQUEST['last-donation-date']) ) {
//
//            if(stripos($_REQUEST['last-donation-date'], ',') !== false) { // Dates period chosen
//
//                $_REQUEST['last-donation-date'] = array_slice(explode(',', $_REQUEST['last-donation-date']), 0, 2);
//
//                if(count($_REQUEST['last-donation-date']) === 2) { // The date is set as an interval
//
//                    $_REQUEST['last-donation-date'][0] = strtotime($_REQUEST['last-donation-date'][0].' 00:00:00');
//                    $_REQUEST['last-donation-date'][1] = strtotime($_REQUEST['last-donation-date'][1].' 23:59:59');
//
//                    $params['meta_query'][] = array(
//                        'key' => 'leyka_donor_last_donation_date',
//                        'value' => $_REQUEST['last-donation-date'],
//                        'compare' => 'BETWEEN',
//                        'type' => 'NUMERIC',
//                    );
//
//                }
//
//            } else { // Single date chosen
//                $params['meta_query'][] = array(
//                    'key' => 'leyka_donor_last_donation_date',
//                    'value' => array(
//                        strtotime($_REQUEST['last-donation-date'].' 00:00:00'),
//                        strtotime($_REQUEST['last-donation-date'].' 23:59:59'),
//                    ),
//                    'compare' => 'BETWEEN',
//                    'type' => 'NUMERIC',
//                );
//            }
//
//        }
//
//        if(count($params['meta_query']) > 1) {
//            $params['meta_query']['relation'] = 'AND';
//        }
//
//        // Ordering:
//        if(isset($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'], $this->get_sortable_columns())) {
//
//            switch($_REQUEST['orderby']) {
//                case 'donor_id': $params['orderby'] = 'ID'; break;
//                case 'donor_type':
//                    $params['meta_key'] = 'leyka_donor_type';
//                    $params['orderby'] = 'meta_value';
//                    break;
//                case 'donor_name':
//                    $params['orderby'] = 'display_name'; break;
//                case 'first_donation':
//                    $params['meta_key'] = 'leyka_donor_first_donation_date';
//                    $params['orderby'] = 'meta_value_num';
//                    break;
//                case 'last_donation':
//                    $params['meta_key'] = 'leyka_donor_last_donation_date';
//                    $params['orderby'] = 'meta_value_num';
//                    break;
//                case 'amount_donated':
//                    $params['meta_key'] = 'leyka_amount_donated';
//                    $params['orderby'] = 'meta_value_num';
//                break;
//                default:
//            }
//
//            if($params['orderby']) {
//                $params['order'] = isset($_REQUEST['order']) && $_REQUEST['order'] == 'asc' ? 'ASC' : 'DESC';
//            }
//
//        }

        return $params;

    }

    /**
     * Retrieve recurring subscriptions data from the DB.
     *
     * @param int|false $per_page A number of lines per page, or false to not use pagination.
     * @param int $page_number A page number. Will not be in use if $per_page === false.
     *
     * @return mixed
     */
    public function get_recurring_subscriptions($per_page = false, $page_number = 1) {

        // Ordering:
        $order_params = array();
        if(isset($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'], $this->get_sortable_columns())) {

            switch($_REQUEST['orderby']) {
                case 'id': $order_params['orderby'] = 'ID'; break;
                case 'status':
                    $order_params['meta_key'] = '_rebilling_is_active';
                    $order_params['orderby'] = 'meta_value_num';
                    break;
                case 'donor':
                    $order_params['meta_key'] = 'leyka_donor_name';
                    $order_params['orderby'] = 'meta_value';
                    break;
                case 'first_donation':
                    $order_params['orderby'] = 'date'; break;
                case 'amount':
                    $order_params['meta_key'] = 'leyka_donation_amount';
                    $order_params['orderby'] = 'meta_value_num';
                    break;
                default:
            }

            if($order_params['orderby']) {
                $order_params['order'] = isset($_REQUEST['order']) && $_REQUEST['order'] == 'asc' ? 'ASC' : 'DESC';
            }

        }

        $params = apply_filters(
            'leyka_admin_donors_list_filter', array_merge(array(
                'post_type' => Leyka_Donation_Management::$post_type,
                'post_status' => 'funded',
                'post_parent' => 0,
                'posts_per_page' => $per_page ? absint($per_page) : -1,
                'paged' => $page_number && $page_number > 1 ? absint($page_number) : 1,
                'meta_query' => array(
                    'meta_query' => array(
                        array('key' => 'leyka_payment_type', 'value' => 'rebill', 'compare' => '=',),
                    ),
                ),
            ), $order_params),
            'get_recurring_subscriptions'
        );

        $subscriptions = array();
        foreach(get_posts($params) as $subscription) {

            $subscription = new Leyka_Donation($subscription);

            $subscription_day_num = (int)date('j', $subscription->date_timestamp);
            $next_donation_timestamp = $subscription_day_num > (int)date('j') ?
                strtotime(date('d', $subscription->date_timestamp).'.'.date('m.Y')) : // Current month, closest date
                strtotime('+1 month', $subscription->date_timestamp); // Next month

            $donations_number = new WP_Query(array(
                'post_type' => Leyka_Donation_Management::$post_type,
                'post_status' => 'funded',
                'post_parent' => $subscription->id,
                'posts_per_page' => -1,
                'meta_query' => array(
                    'meta_query' => array(
                        array('key' => 'leyka_payment_type', 'value' => 'rebill', 'compare' => '=',),
                    ),
                ),
            ));

            $subscription_data = array(
                'id' => $subscription->id,
                'status' => $subscription->recurring_on,
                'donor' => array(
                    'id' => $subscription->donor_id,
                    'name' => $subscription->donor_name,
                    'email' => $subscription->donor_email,
                ),
                'campaign' => array('id' => $subscription->campaign_id, 'title' => $subscription->campaign_title,),
                'first_donation' => $subscription,
                'next_donation' => $next_donation_timestamp,
                'donations_number' => $donations_number->found_posts + 1, // Init donation included
                'gateway' => $subscription->pm_full_id,
                'amount' => $subscription->amount,
            );

            $subscriptions[] = $subscription_data;

        }

        return $subscriptions;

    }

    /**
     * @return null|string
     */
    public static function record_count() {

        $subscriptions = new WP_Query(apply_filters('leyka_admin_recurring_subscriptions_list_filter', array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'post_parent' => 0,
            'posts_per_page' => -1,
            'meta_query' => array(
                'meta_query' => array(
                    array('key' => 'leyka_payment_type', 'value' => 'rebill', 'compare' => '=',),
                ),
            ),
        ), 'get_recurring_subscriptions_total_count'));

        return $subscriptions->found_posts;

    }

    /** Text displayed when no recurring subscriptions data is available. */
    public function no_items() {
        _e('No recurring subscriptions avaliable.', 'leyka');
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'status':
            case 'donor':
            case 'campaign':
            case 'first_donation':
            case 'next_donation':
            case 'donations_number':
            case 'gateway':
            case 'amount':
                return $item[$column_name];
            default:
                return LEYKA_DEBUG ? print_r($item, true) : ''; // Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     * @return string
     */
//    public function column_cb($item) {
//        return sprintf('<input type="checkbox" name="bulk[]" value="%s">', $item['donor_id']);
//    }

    public function column_status($item) {
        return empty($item['status']) ?
            _x('Not active', 'For recurring subscription', 'leyka') :
            _x('Active', 'For recurring subscription', 'leyka');
    }

    public function column_donor($item) { // https://leyka.ngo2.ru/wp-admin/18

        if(empty($item['donor']['id'])) {
            $donor_name = $item['donor']['name'];
        } else {

            try {

                $donor = new Leyka_Donor($item['donor']['id']);

                $donor_name = '<a href="'.admin_url('?page=leyka_donor_info&donor='.$item['donor']['id']).'">'
                    .$donor->name.'</a>';

            } catch(Exception $exception) {
                $donor_name = $item['donor']['name'];
            }

        }

        return '<div class="">'.$donor_name.'</div><div class="">'.$item['donor']['email'].'</div>';

    }

    public function column_campaign($item) {
        return empty($item['campaign']) ? '' : $item['campaign']['title']; // $item['campaign']['id'] for the link
    }

    public function column_first_donation($item) {

        if(empty($item['first_donation']) || !is_a($item['first_donation'], 'Leyka_Donation')) {
            return '';
        }

        return $item['first_donation']->date_label;

    }

    public function column_next_donation($item) {

        if(empty($item['next_donation'])) {
            return '';
        } else if(empty($item['status'])) {
            return __('The subscription is not active', 'leyka');
        }

        $subscription = new Leyka_Donation($item['id']);

        $subscription_day_num = (int)date('j', $subscription->date_timestamp);
        $current_month_max_day = (int)date('t');
        $next_month_max_day = (int)date('t', strtotime('01.'.date('m.Y').' +1 month'));
        $current_day = (int)date('j');

        if($subscription_day_num > $current_day) { // Current month, closest day
            $next_donation_timestamp = $subscription_day_num >= $current_month_max_day ? // Current month is too short?
                strtotime($current_month_max_day.'.'.date('m.Y')) : // Last day of current month
                strtotime(date('d', $subscription->date_timestamp).'.'.date('m.Y')); // Current month, closest day
        } else { // Next month
            $next_donation_timestamp = $subscription_day_num >= $next_month_max_day ? // Next month is too short?
            strtotime($next_month_max_day.'.'.date('m.Y')) : // Last day of next month
            strtotime(date('d', $subscription->date_timestamp).'.'.date('m.Y').' +1 month'); // Next month, same day
        }

        return date(get_option('date_format'), $next_donation_timestamp);

    }

    public function column_donations_number($item) {
        return empty($item['donations_number']) ? '' : absint($item['donations_number']);
    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_gateway($item) {

        if(empty($item['gateway'])) {
            return '';
        }

        $pm = leyka_get_pm_by_id($item['gateway'], true);
        $gateway = leyka_get_gateway_by_id($pm->gateway_id);

        return $gateway->label.', '.$pm->label;

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_amount($item) {
        return empty($item['amount']) || $item['amount'] == 0 ?
            '' : round($item['amount'], 2).' '.leyka_get_currency_label('rur');
    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return array(
//            'cb' => '<input type="checkbox">',
            'id' => __('ID'),
            'status' => __('Status', 'leyka'),
            'donor' => __('Donor', 'leyka'),
            'campaign' => __('Campaign', 'leyka'),
            'first_donation' => __('First donation', 'leyka'),
            'next_donation' => __('Next donation', 'leyka'),
            'donations_number' => __('Donations total', 'leyka'),
            'gateway' => __('Gateway', 'leyka'),
            'amount' => __('Amount donated', 'leyka'),
        );
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return array(
            'id' => array('id', true),
            'status' => array('status', true),
            'donor' => array('donor', false),
            'first_donation' => array('first_donation', true),
//            'next_donation' => array('next_donation', true),
            'amount' => array('amount', true),
        );
    }

    /**
     * @return array
     */
    public function get_bulk_actions() {
        return array(/*'bulk-edit' => __('Edit'), 'bulk-delete' => __('Delete'),*/);
    }

    /**
     * Data query, filtering, sorting & pagination handler.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('recurring_subscriptions_per_page');

        $this->set_pagination_args(array('total_items' => self::record_count(), 'per_page' => $per_page,));
        $this->items = $this->get_recurring_subscriptions($per_page, $this->get_pagenum());

    }

    public function process_bulk_action() {

        // Single donor deletion:
//        if($this->current_action() === 'delete') {
//
//            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donor') ) {
//                die(__("You don't have permissions for this operation.", 'leyka'));
//            } else {
//                self::delete_donor(absint($_GET['donor']));
//            }
//
//        }
//
//        // Bulk donors deletion:
//        if(
//            (isset($_POST['action']) && $_POST['action'] === 'bulk-delete')
//            || (isset($_POST['action2']) && $_POST['action2'] === 'bulk-delete')
//        ) {
//
//            foreach(esc_sql($_POST['bulk']) as $donor_id) {
//                self::delete_donor($donor_id);
//            }
//
//        }

    }

    public function bulk_edit_fields() { /*?>

        <div id="leyka-donors-inline-edit-fields" class="leyka-inline-edit-fields leyka-donors-inline-edit-fields" style="display: none;" data-colspan="<?php echo count($this->get_columns());?>" data-bulk-edit-nonce="<?php echo wp_create_nonce('leyka-bulk-edit-donors');?>">

            <div class="inline-edit-field">
                <input type="text" name="donors-tags-input" class="leyka-donors-tags-selector leyka-selector" value="" placeholder="<?php _e('Donors tags', 'leyka');?>">
                <select class="leyka-donors-tags-select autocomplete-select" name="donors-bulk-tags[]" multiple="multiple"></select>
            </div>

            <div class="inline-edit-field">
                <select name="bulk-edit-action">
                    <option value="add"><?php _e('Add tags', 'leyka');?></option>
                    <option value="remove"><?php _e('Remove tags', 'leyka');?></option>
                    <option value="replace"><?php _e('Replace tags', 'leyka');?></option>
                </select>
            </div>

            <div class="inline-edit-submits">
                <button type="submit" name="bulk-edit" id="bulk-edit" class="button-primary-small"><?php _e('Update');?></button>
                <button class="cancel button-secondary-small"><?php _e('Cancel');?></button>
            </div>

            <div class="result error-message" style="display:none;" data-default-error-text="<?php _e('Error while editing donors', 'leyka');?>"></div>

        </div>

    <?php */ }

    protected function _export_recurring_subscriptions() {

        // Just in case that export will require some time:
        ini_set('max_execution_time', 99999);
        set_time_limit(99999);

//        ob_start();
//
//        $this->items = self::get_donors(false);
//
//        add_filter('leyka_donors_export_line', 'leyka_prepare_data_line_for_export', 10, 2);
//
//        ob_clean();
//
//        header('Content-type: application/vnd.ms-excel');
//        header('Content-Transfer-Encoding: binary');
//        header('Expires: 0');
//        header('Pragma: no-cache');
//
//        header('Content-Disposition: attachment; filename="donors-'.date('d.m.Y-H.i.s').'.csv"');
//
//        echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
//            'UTF-8',
//            apply_filters('leyka_donors_export_content_charset', 'windows-1251'),
//            "sep=;\n".implode(';', apply_filters('leyka_donors_export_headers', array(
//                'ID', 'Тип донора', 'Имя', 'Email', 'Дата первого пожертвования', 'Сумма первого пожертвования', 'Кампания первого пожертвования', 'Метки донора', 'Кампании', 'Платёжные операторы', 'Дата последнего пожертвования', 'Сумма последнего пожертвования', 'Кампания последнего пожертвования', 'Общая сумма пожертвований', 'Валюта',
//            )))
//        );
//
//        foreach($this->items as $donor_data) {
//
//            $first_donation = $donor_data['first_donation'] ? new Leyka_Donation($donor_data['first_donation']) : false;
//            $last_donation = $donor_data['last_donation'] ? new Leyka_Donation($donor_data['last_donation']) : false;
//
//            $donor_tags_list = array();
//            if( !empty($donor_data['donors_tags']) ) {
//                foreach($donor_data['donors_tags'] as $term) { /** @var $term WP_Term */
//                    $donor_tags_list[] = esc_attr($term->name);
//                }
//            }
//            $donor_tags_list = implode(', ', $donor_tags_list);
//
//            $donor_campaigns_list = array();
//            if( !empty($donor_data['campaigns']) ) {
//                foreach($donor_data['campaigns'] as $campaign_id => $campaign_title) {
//                    if($campaign_title) {
//                        $donor_campaigns_list[] = $campaign_title;
//                    }
//                }
//            }
//            sort($donor_campaigns_list);
//            $donor_campaigns_list = implode(', ', $donor_campaigns_list);
//
//            $donor_gateways_list = array();
//            if( !empty($donor_data['gateways']) ) {
//                foreach($donor_data['gateways'] as $gateway_id) {
//
//                    $gateway = leyka_get_gateway_by_id($gateway_id);
//                    if($gateway) {
//                        $donor_gateways_list[] = esc_attr($gateway->label);
//                    }
//
//                }
//            }
//            sort($donor_gateways_list);
//            $donor_gateways_list = implode(', ', $donor_gateways_list);
//
//            echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
//                'UTF-8',
//                apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
//                "\r\n".implode(';', apply_filters('leyka_donations_export_line', array(
//                        $donor_data['donor_id'],
//                        _x(mb_ucfirst($donor_data['donor_type']), "Donor's type", 'leyka'),
//                        $donor_data['donor_name'],
//                        $donor_data['donor_email'],
//                        ($first_donation ? $first_donation->date : ''),
//                        ($first_donation ? $first_donation->amount : ''),
//                        ($first_donation ? $first_donation->campaign_title : ''),
//                        $donor_tags_list,
//                        $donor_campaigns_list,
//                        $donor_gateways_list,
//                        ($last_donation ? $last_donation->date : ''),
//                        ($last_donation ? $last_donation->amount : ''),
//                        ($last_donation ? $last_donation->campaign_title : ''),
//                        $donor_data['amount_donated'],
//                        leyka_get_currency_label(),
//                    ), $donor_data)
//                )
//            );
//
//        }

        die();

    }

}