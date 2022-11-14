<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Recurring_Subscriptions_List_Table extends WP_List_Table {

    protected static $_items_count = NULL;

    public function __construct() {

        parent::__construct([
            'singular' => __('Subscription', 'leyka'),
            'plural' => __('Subscriptions', 'leyka'),
            'ajax' => true,
        ]);

        add_filter('leyka_admin_recurring_subscriptions_list_filter', [$this, 'filter_items'], 10, 2);

        if( !empty($_GET['subscriptions-list-export']) ) {
            $this->_export();
        }

        if( !empty($_GET['subscriptions-update-all-statuses']) ) {
            leyka_update_recurring_subscriptions_statuses(true);
            unset($_GET['subscriptions-update-all-statuses']);
        }

    }

    /**
     * Recurring subscriptions fields filtering.
     *
     * @param $params array
     * @param $filter_type string
     * @return array|false An array of params, or false if the $filter_type is wrong
     */
    public function filter_items(array $params, $filter_type = '') {

        $params['recurring_only_init'] = true;
        $params['status'] = ['funded'];

        if( !empty($_GET['get-params']) ) {
            parse_str($_GET['get-params'], $_GET);
        }

        if( !empty($_GET['recurring_subscription_status']) ) {
            $params['recurring_subscription_status'] = $_GET['recurring_subscription_status'];
        }

        if( !empty($_GET['donor-name-email']) ) {
            $params['donor_name_email'] = $_GET['donor-name-email'];
        }

        if( !empty($_GET['campaigns']) ) {
            if(is_array($_GET['campaigns'])) {
                $params['campaign_id'] = array_filter($_GET['campaigns'], function($value){ return absint($value); });
            } else if(absint($_GET['campaigns'])) {
                $params['campaign_id'] = absint($_GET['campaigns']);
            }

        }

        if( !empty($_GET['first-date']) ) {

            if(is_string($_GET['first-date']) && mb_stripos($_GET['first-date'], '-') !== false) { // Dates period chosen as a str

                $_GET['first-date'] = array_slice(explode('-', $_GET['first-date']), 0, 2);

                if(count($_GET['first-date']) === 2) { // The date is set as an interval

                    $params['date_from'] = trim($_GET['first-date'][0]).' 00:00:00';
                    $params['date_to'] = trim($_GET['first-date'][1]).' 23:59:59';

                }

            } else if(is_array($_GET['first-date']) && count($_GET['first-date']) === 2) { // Dates period chosen as an array

                $params['date_from'] = trim($_GET['first-date'][0]).' 00:00:00';
                $params['date_to'] = trim($_GET['first-date'][1]).' 23:59:59';

            } else { // Single date chosen

                $params['date_from'] = trim($_GET['first-date']).' 00:00:00';
                $params['date_to'] = trim($_GET['first-date']).' 23:59:59';

            }

        }

        if( !empty($_GET['gateway']) ) {
            $params['gateway_id'] = $_GET['gateway'];
        }

        if( !empty($_GET['day']) ) {
            if(abs((int)$_GET['day']) > 0 && abs((int)$_GET['day']) < 31) {
                $params['day'] = abs((int)$_GET['day']);
            }
        }

        if($filter_type) { // If filter type is set, the filtering is not just to get items from DB - so ordering won't be needed
            return $params;
        }

        if( !empty($_GET['orderby']) ) {

            $params['orderby'] = $_GET['orderby'];
            $params['order'] = empty($_GET['order']) || !in_array($_GET['order'], ['asc', 'desc']) ?
                'DESC' : mb_strtoupper($_GET['order']);

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
    protected function _get_items($per_page = false, $page_number = 1) {

        $params = ['orderby' => 'id', 'order' => 'desc',];
        if(empty($per_page)) {
            $params['get_all'] = true;
        } else {
            $params = $params + ['results_limit' => absint($per_page), 'page' => absint($page_number),];
        }

        $init_recurring_donations = Leyka_Donations::get_instance()->get(
            apply_filters('leyka_admin_recurring_subscriptions_list_filter', $params)
        );

        $items_data = [];
        foreach($init_recurring_donations as $init_donation) {

            $item = [
                'id' => $init_donation->id,
                'recurring_subscription_status' => $init_donation->recurring_subscription_status,
                'recurring_subscription_error_id' => $init_donation->recurring_subscription_error_id,
                'donor' => [
                    'id' => $init_donation->donor_id,
                    'name' => $init_donation->donor_name,
                    'email' => $init_donation->donor_email,
                ],
                'campaign' => [
                    'id' => $init_donation->campaign_id,
                    'title' => $init_donation->campaign_title,
                ],
                'first_donation' => $init_donation,
                'next_donation' => $init_donation->next_recurring_date_timestamp,
                'donations_number' => $init_donation->successful_rebills_number + 1, // Init donation included
                'gateway_pm' => $init_donation->pm_full_id,
                'amount' => $init_donation->amount,
                'amount_formatted' => $init_donation->amount_formatted,
            ];

            $items_data[] = $item;

        }

        return $items_data;

    }

    /**
     * @return null|string
     */
    public static function get_items_count() {

        if(self::$_items_count === NULL) {
            self::$_items_count = Leyka_Donations::get_instance()->get_count(apply_filters(
                'leyka_admin_recurring_subscriptions_list_filter',
                [],
                'get_recurring_subscriptions_total_count'
            ));
        }

        return self::$_items_count;

    }

    /** Text displayed when no recurring subscriptions data is available. */
    public function no_items() {
        _e('No recurring subscriptions avaliable.', 'leyka');
    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return apply_filters('leyka_admin_recurring_subscriptions_list_columns', [
            'donation_id' => __('ID'),
            'campaign' => __('Campaign', 'leyka'),
            'donor' => __('Donor', 'leyka'),
            'first_donation' => __('First payment', 'leyka'),
            'next_donation' => _x('Next', 'Recurring subscriptions list page: table column', 'leyka'),
            'donations_number' => __('Payments total', 'leyka'),
            'amount' => __('Amount', 'leyka'),
            'gateway_pm' => _x('Method', 'Recurring subscriptions list page: table column', 'leyka')
        ]);
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return apply_filters('leyka_admin_recurring_subscriptions_list_sortable_columns', [
            'donation_id' => ['donation_id', true],
            'donor' => ['donor_name', false],
            'first_donation' => ['first_donation', true],
            'donations_number' => ['donations_number', true],
            'amount' => ['amount', true],
            'gateway_pm' => ['gateway_pm', true]
        ]);
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
            case 'donation_id':

                if($item['recurring_subscription_status'] === 'problematic') {

                    if(in_array($item['recurring_subscription_error_id'], ['L-2001', 'L-1023'])) {

                        $recurring_error = Leyka_Donations_Errors::get_instance()->get_error_by_id($item['recurring_subscription_error_id']);
                        $recurring_error_subscription = __($recurring_error->description, 'leyka');
                        $recurring_error_link_label = __('To the subscription page', 'leyka');
                        $recurring_error_link = admin_url('?page=leyka_recurring_subscription_info&donation='.$item['id']);

                    } else {

                        $recurring_error_subscription = __('The last attempt to make a donation by this subscription ended with an error. Please check the error description and the fixing recommendations on the donation page.', 'leyka');
                        $subscription_last_payment = Leyka_Donations::get_instance()->get([
                            'recurring_rebills_of' => $item['id'],
                            'get_single' => true,
                            'orderby' => ['date_timestamp' => 'DESC']
                        ]);
                        $recurring_error_link_label = __('To the donation page', 'leyka');
                        $recurring_error_link = admin_url('?page=leyka_donation_info&donation='.$subscription_last_payment->id);

                    }

                }

                $content = '<div class="leyka-content-wrapper">
                    <span>'.$item['id'].'</span>
                    <a href="'.admin_url('admin.php?page=leyka_recurring_subscription_info&donation='.$item['id']).'" class="donation-edit-link">'.__('Details', 'leyka').'</a>
                    <span class="leyka-'.($item['recurring_subscription_status']).'">'
                        ._x(ucfirst($item['recurring_subscription_status']), 'Recurring subscription status, singular (like [subscription is] "Active/Non-active/Problematic")', 'leyka') /** @todo Fix this ambiguous l10n string formulation! */
                    .'</span>'.
                    ($item['recurring_subscription_status'] !== 'problematic' ? '' : '<div class="problematic-subscription-alert leyka-subscription-error leyka-hidden">
                        <img class="leyka-button-close" src="'.LEYKA_PLUGIN_BASE_URL.'img/star-icon-close.svg">
                        <div class="leyka-error-title">
                            <img src="'.LEYKA_PLUGIN_BASE_URL.'img/icon-alert-red.svg">
                            <span>'.__('Problematic subscription', 'leyka').'</span>
                        </div>
                        <div class="leyka-error-description-wrapper">
                            <div class="leyka-error-description">'.$recurring_error_subscription.'</div>
                            <a class="leyka-error-link" href="'.$recurring_error_link.'">'.$recurring_error_link_label.'</a>
                            <div class="leyka-button-ok">'._x('OK','Problematic subscription alert OK button', 'leyka').'</div>
                        </div>
                    </div>').'
                </div>';

                return apply_filters('leyka_admin_recurring_subscription_donation_id_column_content', $content, $item['first_donation']);

            default: // Show the whole item array for troubleshooting purposes
                return apply_filters("leyka_admin_recurring_subscription_{$column_name}_column_content", '', $item);
        }
    }

    public function column_donor($item) {

        $donation = Leyka_Donations::get_instance()->get_donation($item['id']);
        $donor_phone = leyka_get_donor_phone($donation);

        $donor_data_html = apply_filters(
            'leyka_admin_recurring_subscription_donor_column_content',
            '<div class="donor-name">'
                .(leyka_options()->opt('donor_management_available') && $donation->donor_id ? '<a href="'.admin_url('admin.php?page=leyka_donor_info&donor='.$donation->donor_id).'">' : '')
                .$donation->donor_name
                .(leyka_options()->opt('donor_management_available') ? '</a>' : '')
            .'</div>'
            .'<div class="donor-additional-data donor-email">'.$donation->donor_email.'</div>'
            .($donor_phone ? '<div class="donor-additional-data donor-phone">'.$donor_phone.'</div>' : ''),
            $donation
        );

        $donor_additional_data_html = '<ul>';

        if($donation->payment_type === 'rebill') {
            $donor_additional_data_html .= '<li>
                <span class="leyka-li-title">'.__('Recurring is active', 'leyka').':</span>
                <span class="leyka-li-value">'.($donation->recurring_active ? __('yes', 'leyka') : __('no', 'leyka')).'</span>
            </li>';
        }

        $donor_additional_data_html .= '<li>
                <span class="leyka-li-title">'.__('Email', 'leyka').':</span>
                <span class="leyka-li-value">'.($donation->donor_email_date ? sprintf(__('Sent on %s', 'leyka'), date(get_option('date_format').', H:i</time>', $donation->donor_email_date)) : __('no', 'leyka')).'</span>
            </li>
        
            <li>
                <span class="leyka-li-title">'.__('Email subscription', 'leyka').':</span>
                <span class="leyka-li-value">'.($donation->donor_subscribed ? __('yes', 'leyka') : __('no', 'leyka')).'</span>
            </li>
        
            <li>
                <span class="leyka-li-title">'._x('Comment', "Donor's comment. Should be short.", 'leyka').':</span>
                <span class="leyka-li-value">'.($donation->donor_comment ? mb_ucfirst($donation->donor_comment) : __('no', 'leyka')).'</span>
            </li>';
        $donor_additional_data_html = apply_filters(
            'leyka_admin_donation_donor_column_additional_data_list_content_html',
            $donor_additional_data_html,
            $donation
        );
        $donor_additional_data_html .= '</ul>';

        return '<div class="leyka-donor-data-cell-wrapper">
                <div class="leyka-donor-data-additional">
                    <i class="icon-donor-more-data has-tooltip leyka-tooltip-on-click leyka-tooltip-wide leyka-tooltip-white" data-tooltip-additional-classes="leyka-admin-tooltip-donor-more-data"></i>
                    <span class="leyka-tooltip-content">'
                        .apply_filters(
                            'leyka_admin_recurring_subscription_donor_column_additional_data',
                            $donor_additional_data_html,
                            $item
                        )
                    .'</span>
                </div>
                <div class="leyka-donor-data-main">'.$donor_data_html.'</div>
            </div>';

    }

    public function column_campaign($item) {

        $campaign_title_stripped = leyka_strip_string_by_words($item['campaign']['title'], 30);
        $is_title_stripped = $campaign_title_stripped !== $item['campaign']['title'];

        $content = '<div class="donation-campaign '.($is_title_stripped ? 'has-tooltip' : '').'" '.($is_title_stripped ? 'title="'.esc_attr($item['campaign']['title']).'"' : '').'>
                <a href="'.admin_url('post.php?post='.$item['campaign']['id'].'&action=edit').'">'
            .($is_title_stripped ? $campaign_title_stripped.'&nbsp;&mldr;' : $item['campaign']['title'])
            .'</a>
            </div>';

        return apply_filters('leyka_admin_recurring_subscription_campaign_column_content', $content, $item);

    }

    public function column_first_donation($item) {

        if(empty($item['first_donation']) || !is_a($item['first_donation'], 'Leyka_Donation_Base')) {
            return '';
        }

        return apply_filters(
            'leyka_admin_recurring_subscription_first_donation_column_content',
            $item['first_donation']->date_label.'<br>'.$item['first_donation']->time_label.'<a href="'.admin_url('admin.php?page=leyka_donation_info&donation='.$item['first_donation']->id).'"><img src="'.LEYKA_PLUGIN_BASE_URL.'/img/icon-arrow-left.svg"></a>',
            $item
        );

    }

    public function column_next_donation($item) {

        if(empty($item['next_donation'])) {
            return '';
        } else if($item['recurring_subscription_status'] === 'non-active') {
            return '<span class="leyka-recurring-not-active">'.__('The subscription is not active', 'leyka').'</span>';
        }

        return apply_filters(
            'leyka_admin_recurring_subscription_next_donation_column_content',
            date(get_option('date_format'), $item['next_donation']),
            $item
        );

    }

    public function column_donations_number($item) {
        return apply_filters(
            'leyka_admin_recurring_subscription_donations_number_column_content',
            empty($item['donations_number']) ? '' : absint($item['donations_number']),
            $item
        );
    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_gateway_pm($item) {

        $donation = Leyka_Donations::get_instance()->get_donation($item['id']);

        $gateway = $donation->gateway_id ? leyka_get_gateway_by_id($donation->gateway_id) : false;
        $pm = $donation->gateway_id && $donation->gateway_id !== 'correction' ?
            leyka_get_pm_by_id($donation->pm_full_id, true) : $donation->pm_id;

        $gateway_label = $donation->gateway_id && $donation->gateway_id !== 'correction' ?
            $gateway->label : __('Custom payment info', 'leyka');
        $pm_label = is_a($pm, 'Leyka_Payment_Method') ? $donation->pm_label : $donation->pm;

        return apply_filters(
            'leyka_admin_recurring_subscription_gateway_pm_column_content',
            "<span class='leyka-gateway-pm has-tooltip leyka-tooltip-align-left' title='".$gateway_label.' / '.$pm_label."'>
                <div class='leyka-gateway-name'>"
            .($gateway ?
                '<img src="'.$gateway->icon_url.'" alt="'.$gateway_label.'">' :
                '<img src="'.LEYKA_PLUGIN_BASE_URL.'/img/pm-icons/custom-payment-info.svg" alt="'.$pm.'">')
            ."</div>
                <div class='leyka-pm-name'>"
            .(is_a($pm, 'Leyka_Payment_Method') ? "<img src='".$pm->admin_icon_url."' alt='$pm_label'>" : '')
            ."</div>
            </span>",
            $donation
        );

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_amount($item) {

        $amount = $item['amount'] == $item['first_donation']->amount_total ?
            $item['amount_formatted'].'&nbsp;'.$item['first_donation']->currency_label :
            $item['amount_formatted'].'&nbsp;'.$item['first_donation']->currency_label
            .'<div class="amount-total">'
            .$item['first_donation']->amount_total_formatted.'&nbsp;'.$item['first_donation']->currency_label
            .'</div>';

        $donation = Leyka_Donations::get_instance()->get_donation($item['id']);

        /* if($donation->status === 'failed') {

            $error = $donation->error;
            $error = is_a($error, 'Leyka_Donation_Error') ?
                $error : Leyka_Donations_Errors::get_instance()->get_error_by_id(false);

            $tooltip_content = '<strong>'.sprintf(__('Error %s', 'leyka'), $error->id).'</strong>: '.mb_lcfirst($error->name)
                .'<p><a class="leyka-tooltip-error-content-more leyka-inner-tooltip leyka-tooltip-x-wide leyka-tooltip-white" title="" href="#">'
                .__('More info', 'leyka')
                .'</a></p>'
                .'<div class="error-full-info-tooltip-content">'.leyka_show_donation_error_full_info($error, true).'</div>';

        } else {
            $tooltip_content = '<strong>'.$donation->status_label.':</strong> '.mb_lcfirst($donation->status_description);
        } */

        $column_content = '<span class="leyka-amount '.apply_filters('leyka_admin_recurring_subscription_amount_column_css', ($donation->amount < 0.0 ? 'leyka-amount-negative' : '')).'">'
            .'<span class="leyka-amount-and-status">'
            .'<div class="leyka-amount-itself" title="">'
            .$amount
            .'</div>'
            .'</span>'
            /* .'<div class="leyka-donation-status-label label-'.$donation->status.' has-tooltip leyka-tooltip-align-left leyka-tooltip-on-click" title="">'
            .__(Leyka::get_donation_status_info($donation->status, 'short_label'), 'leyka')
            .'</div>'
            .'<span class="leyka-tooltip-content">'
            .apply_filters(
                'leyka_admin_recurring_subscriptions_list_donation_status_tooltip_content',
                $tooltip_content,
                $item
            )
            .'</span>'
            .'</span>' */
            ;

        return apply_filters('leyka_admin_recurring_subscription_amount_column_content', $column_content, $donation);

    }

    /**
     * @return array
     */
    public function get_bulk_actions() {
        return [/*'bulk-edit' => __('Edit'), 'bulk-delete' => __('Delete'),*/];
    }

    /**
     * Data query, filtering, sorting & pagination handler.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

//        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('recurring_subscriptions_per_page');

        $this->set_pagination_args(['total_items' => self::get_items_count(), 'per_page' => $per_page,]);
        $this->items = $this->_get_items($per_page, $this->get_pagenum());

    }

    protected function display_tablenav( $which ) {

        if($which === 'top') {
            wp_nonce_field('bulk-'.$this->_args['plural']);
        }?>

        <div class="leyka-admin-tablenav <?php echo esc_attr($which);?>">

            <?php if($which === 'top') {

                $subscriptions_statuses_list = leyka_get_recurring_subscription_status_list();
                $subscriptions_stats['all'] = 0;

                foreach(array_keys($subscriptions_statuses_list) as $status_id) {
                    $subscriptions_stats[$status_id] = 0;
                }

                $params = apply_filters('leyka_admin_recurring_subscriptions_list_filter', []);
                unset($params['recurring_subscription_status']);

                $subscriptions = Leyka_Donations::get_instance()->get($params);

                foreach($subscriptions as $subscription) {

                    $subscriptions_stats['all']++;
                    $subscriptions_stats[$subscription->recurring_subscription_status]++;

                };

                $filter_value = isset($_GET['recurring_subscription_status']) ?
                    esc_attr($_GET['recurring_subscription_status']) : false;
                $other_filters_values_string = '';

                foreach($_GET as $param_name => $param_value) {

                    if($param_name === 'first-date' && is_array($param_value)) {
                        $param_value = $param_value[0].'-'.$param_value[1];
                    }

                    $other_filters_values_string .= $other_filters_values_string === '' ? '' : '&';
                    $other_filters_values_string .= in_array($param_name, ['recurring_subscription_status', 'paged']) ?
                        '' : $param_name.'='.$param_value;

                }?>

                <div class="admin-list-filters leyka-filter-buttons">

                        <a class="leyka-filter-button leyka-subscriptions-all <?php echo !$filter_value ? 'leyka-active' : ''; ?>" href="?<?php echo $other_filters_values_string; ?>">
                            <?php echo __('All subscriptions', 'leyka').' ('.$subscriptions_stats['all'].')';?>
                        </a>

                        <a class="leyka-filter-button leyka-subscriptions-active <?php echo $filter_value == 'active' ? 'leyka-active' : '';?>" href="?<?php echo $other_filters_values_string; ?>&recurring_subscription_status=active">
                            <?php echo _x('Only active', 'Multiple case', 'leyka').' ('.$subscriptions_stats['active'].')';?>
                        </a>

                        <a class="leyka-filter-button leyka-subscriptions-problematic <?php echo $filter_value == 'problematic' ? 'leyka-active' : '';?>" href="?<?php echo $other_filters_values_string; ?>&recurring_subscription_status=problematic">
                            <?php echo _x('Only problematic', 'Multiple case', 'leyka').' ('.$subscriptions_stats['problematic'].')';?>
                        </a>

                        <a class="leyka-filter-button leyka-subscriptions-non-active <?php echo $filter_value == 'non-active' ? 'leyka-active' : '';?>" href="?<?php echo $other_filters_values_string; ?>&recurring_subscription_status=non-active">
                            <?php echo _x('Only not active', 'Multiple case', 'leyka').' ('.$subscriptions_stats['non-active'].')';?>
                        </a>

                </div>

                <?php $this->extra_tablenav($which);
                $this->pagination($which);

            } ?>

        </div>

        <?php

    }

    public function single_row($item) {

        echo '<tr class="leyka-'.($item['recurring_subscription_status']).'">';
        $this->single_row_columns($item);
        echo '</tr>';

    }

//    public function process_bulk_action() {
//    }

//    public function bulk_edit_fields() {
//    }

    protected function _export() {

        // Just in case that export will require some time:
        ini_set('max_execution_time', 99999);
        set_time_limit(99999);

        ob_start();

        $this->items = $this->_get_items();

        ob_clean();

        $columns = apply_filters('leyka_recurring_subscriptions_export_headers', [
            'ID', 'Статус подписки', 'Имя донора', 'Email', 'Кампания', 'Дата первого пожертвования', 'Дата следующего пожертвования', 'Всего пожертвований', 'Платёжный оператор', 'Сумма подписки', 'Валюта',
        ]);

        $date_format = get_option('date_format');

        $rows = [];
        foreach($this->items as $item) {

            $pm = leyka_get_pm_by_id($item['gateway_pm'], true);
            $gateway = leyka_get_gateway_by_id($pm->gateway_id);

            $currency = $item['first_donation']->currency_label;
//            $currency_label_encoded = @iconv( // Sometimes currency sighs can't be encoded, so check for it
//                'UTF-8',
//                apply_filters('leyka_recurring_subscriptions_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
//                $currency
//            );
//            $currency = $currency_label_encoded ? $currency : $item['first_donation']->currency;

            $rows[] = apply_filters(
                'leyka_recurring_subscriptions_export_line',
                [
                    $item['id'],
                    $item['recurring_subscription_status'],
                    empty($item['donor']['name']) ? '' : $item['donor']['name'],
                    empty($item['donor']['email']) ? '' : $item['donor']['email'],
                    empty($item['campaign']['title']) ? 'Кампания #'.$item['campaign']['id'] : $item['campaign']['title'],
                    $item['first_donation']->date_time_label,
                    apply_filters(
                        'leyka_admin_donation_date',
                        date($date_format, $item['next_donation']),
                        $item['next_donation'], $date_format
                    ),
                    $item['donations_number'],
                    $gateway->label.', '.$pm->label,
                    str_replace('.', ',', $item['first_donation']->amount),
                    $currency,
                ],
                $item
            );

        }

        leyka_generate_csv('recurring_subscriptions-'.date('d.m.Y-H.i.s'), $rows, $columns); // It will exit automatically

    }

}