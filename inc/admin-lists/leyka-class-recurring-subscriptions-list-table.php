<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Recurring_Subscriptions_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Subscription', 'leyka'), 'plural' => __('Subscriptions', 'leyka'), 'ajax' => true,));

        add_filter('leyka_admin_recurring_subscriptions_list_filter', array($this, 'filter_recurring_subscriptions'), 10, 2);

        if( !empty($_REQUEST['subscriptions-list-export']) ) {
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

        $params['meta_query'] = empty($params['meta_query']) ? array() : $params['meta_query'];
        $params['date_query'] = empty($params['date_query']) ? array() : $params['date_query'];

        if( !empty($_REQUEST['subscription-status']) && $_REQUEST['subscription-status'] === 'active' ) {
            $params['meta_query'][] = array('key' => '_rebilling_is_active', 'value' => true,);
        } else if( !empty($_REQUEST['subscription-status']) && $_REQUEST['subscription-status'] === 'non-active' ) {
            $params['meta_query'][] = array('key' => '_rebilling_is_active', 'value' => false,);
        }

        if( !empty($_REQUEST['donor-name-email']) ) {

            $_REQUEST['donor-name-email'] = trim($_REQUEST['donor-name-email']);

            $params['meta_query'][] = array(
                'relation' => 'OR',
                array('key' => 'leyka_donor_name', 'value' => $_REQUEST['donor-name-email'], 'compare' => 'LIKE'),
                array('key' => 'leyka_donor_email', 'value' => $_REQUEST['donor-name-email'], 'compare' => 'LIKE'),
            );

        }

        if( !empty($_REQUEST['campaigns']) && !empty($_REQUEST['campaigns'][0]) ) {
            $params['meta_query'][] = array('key' => 'leyka_campaign_id', 'value' => $_REQUEST['campaigns'], 'compare' => 'IN',);
        }

        if( !empty($_REQUEST['first-donation-date']) ) {

            if(stripos($_REQUEST['first-donation-date'], '-') !== false) { // Dates period chosen

                $_REQUEST['first-donation-date'] = array_slice(explode('-', $_REQUEST['first-donation-date']), 0, 2);

                if(count($_REQUEST['first-donation-date']) === 2) { // The date is set as an interval

                    $_REQUEST['first-donation-date'][0] = trim($_REQUEST['first-donation-date'][0]).' 00:00:00';
                    $_REQUEST['first-donation-date'][1] = trim($_REQUEST['first-donation-date'][1]).' 23:59:59';

                    $params['date_query'][] = array(array(
                        'after' => $_REQUEST['first-donation-date'][0],
                        'before' => $_REQUEST['first-donation-date'][1],
                        'inclusive' => true,
                    ));

                }

            } else { // Single date chosen
                $params['date_query'][] = array(array(
                    'after' => trim($_REQUEST['first-donation-date']).' 00:00:00',
                    'before' => trim($_REQUEST['first-donation-date']).' 23:59:59',
                    'inclusive' => true,
                ));
            }

        }

        if( !empty($_REQUEST['gateways']) ) {
            $params['meta_query'][] = array('key' => 'leyka_gateway', 'value' => $_REQUEST['gateways'], 'compare' => 'IN',);
        }

        if(count($params['meta_query']) > 1) {
            $params['meta_query']['relation'] = 'AND';
        }

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
            'leyka_admin_recurring_subscriptions_list_filter', array_merge(array(
                'post_type' => Leyka_Donation_Management::$post_type,
                'post_status' => 'funded',
                'post_parent' => 0,
                'posts_per_page' => $per_page ? absint($per_page) : -1,
                'paged' => $page_number && $page_number > 1 ? absint($page_number) : 1,
                'meta_query' => array(
                    array('key' => 'leyka_payment_type', 'value' => 'rebill', 'compare' => '=',),
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
                array('key' => 'leyka_payment_type', 'value' => 'rebill', 'compare' => '=',),
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
                return '<a href="'.admin_url('post.php?post='.$item[$column_name].'&action=edit').'">'.$item[$column_name].'</a>';
            case 'status':
            case 'donor':
            case 'campaign':
            case 'first_donation':
            case 'next_donation':
            case 'donations_number':
            case 'gateway':
            case 'amount':
                return $item[$column_name];
            default: // Show the whole array for troubleshooting purposes
                return leyka_options()->opt('plugin_debug_mode') ? print_r($item, true) : '';
        }
    }

    public function column_status($item) {
        return empty($item['status']) ?
            '<span class="recurring-status not-active">'._x('Not active', '[recurring subscription]', 'leyka').'</span>' :
            '<span class="recurring-status active">'._x('Active', '[recurring subscription]', 'leyka').'</span>';
    }

    public function column_donor($item) {

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

        return '<div class="donor-name text-larger">'.$donor_name.'</div>'
            .'<div class="donor-email">'.$item['donor']['email'].'</div>';

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
            '' : leyka_amount_format(round($item['amount'], 2)).' '.leyka_get_currency_label('rur');
    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return array(
            'id' => __('ID'),
            'status' => __('Status', 'leyka'),
            'donor' => __('Donor', 'leyka'),
            'campaign' => __('Campaign', 'leyka'),
            'first_donation' => __('First donation', 'leyka'),
            'next_donation' => __('Next donation', 'leyka'),
            'donations_number' => __('Donations total', 'leyka'),
            'gateway' => __('Gateway', 'leyka'),
            'amount' => __('Donation amount', 'leyka'),
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

        ob_start();

        $this->items = $this->get_recurring_subscriptions(false);

        add_filter('leyka_recurring_subscriptions_export_line', 'leyka_prepare_data_line_for_export', 10, 2);

        ob_clean();

        header('Content-type: application/vnd.ms-excel');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: no-cache');

        header('Content-Disposition: attachment; filename="recurring_subscriptions-'.date('d.m.Y-H.i.s').'.csv"');

        echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
            'UTF-8',
            apply_filters('leyka_recurring_subscriptions_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
            "sep=;\n".implode(';', apply_filters('leyka_recurring_subscriptions_export_headers', array(
                'ID', 'Статус подписки', 'Имя донора', 'Email', 'Кампания', 'Дата первого пожертвования', 'Дата следующего пожертвования', 'Всего пожертвований', 'Платёжный оператор', 'Сумма подписки', 'Валюта',
            )))
        );

        $date_format = get_option('date_format');

        foreach($this->items as $item) {

            $pm = leyka_get_pm_by_id($item['gateway'], true);
            $gateway = leyka_get_gateway_by_id($pm->gateway_id);

            $currency = leyka_get_currency_label('rur');
            $currency_label_encoded = @iconv( // Sometimes currency sighs can't be encoded, so check for it
                'UTF-8',
                apply_filters('leyka_recurring_subscriptions_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
                $currency
            );
            $currency = $currency_label_encoded ? $currency : 'rur';

            echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
                'UTF-8',
                apply_filters('leyka_recurring_subscriptions_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
                "\r\n".implode(';', apply_filters('leyka_recurring_subscriptions_export_line', array(
                        $item['id'],
                        empty($item['status']) ? 'неактивна' : 'активна',
                        empty($item['donor']['name']) ? '' : $item['donor']['name'],
                        empty($item['donor']['email']) ? '' : $item['donor']['email'],
                        empty($item['campaign']['title']) ? 'Кампания #'.$item['campaign']['id'] : $item['campaign']['title'],
                        $item['first_donation']->date_label,
                        apply_filters(
                            'leyka_admin_donation_date',
                            date($date_format, $item['next_donation']),
                            $item['next_donation'], $date_format
                        ),
                        $item['donations_number'],
                        $gateway->label.', '.$pm->label,
                        empty($item['amount']) ? '' : leyka_amount_format(round($item['amount'], 2)),
                        $currency,
                    ), $item)
                )
            );

        }

        die();

    }

}