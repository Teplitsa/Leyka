<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donations_List_Table extends WP_List_Table {

    protected static $_items_count = NULL;

    public function __construct() {

        parent::__construct(['singular' => __('Donation', 'leyka'), 'plural' => __('Donations', 'leyka'), 'ajax' => true,]);

        add_filter('default_hidden_columns', [$this, 'get_default_hidden_columns'], 10);

        add_filter('leyka_admin_donations_list_filter', [$this, 'filter_items'], 10, 2);

        if( !empty($_REQUEST['donations-list-export']) ) {
            $this->_export();
        }

    }

    /**
     * @param $donations_params array
     * @param $filter_type string
     * @return array|false An array of get_users() params, or false if the $filter_type is wrong
     */
    public function filter_items(array $donations_params, $filter_type = '') {

        if( !empty($_GET['type']) && in_array($_GET['type'], array_keys(leyka_get_payment_types_list())) ) {
            $donations_params['payment_type'] = $_GET['type'];
        }
        if( !empty($_GET['status']) && in_array($_GET['status'], array_keys(leyka_get_donation_status_list())) ) {
            $donations_params['status'] = $_GET['status'];
        }
        if( !empty($_GET['date-from']) && strtotime($_GET['date-from']) ) {
            $donations_params['date_from'] = $_GET['date-from'];
        }
        if( !empty($_GET['date-to']) && strtotime($_GET['date-to']) ) {
            $donations_params['date_to'] = $_GET['date-to'];
        }
        if( !empty($_GET['campaigns']) ) {
            if(is_array($_GET['campaigns'])) {
                $donations_params['campaign_id'] = array_filter($_GET['campaigns'], function($value){ return absint($value); });
            } else if(absint($_GET['campaigns'])) {
                $donations_params['campaign_id'] = absint($_GET['campaigns']);
            }

        }
        if( !empty($_GET['gateway-pm']) ) {
            $donations_params['gateway_pm'] = $_GET['gateway-pm'];
        }
        if(isset($_GET['donor-name-email'])) {
            $donations_params['donor_name_email'] = $_GET['donor-name-email'];
        }
        if(isset($_GET['donor_subscribed']) && $_GET['donor_subscribed'] != '-') {
            $donations_params['donor_subscribed'] = !!$_GET['donor_subscribed'];
        }
        if( !empty($_GET['orderby']) ) {

            $donations_params['orderby'] = $_GET['orderby'];
            $donations_params['order'] = empty($_GET['order']) || !in_array($_GET['order'], ['asc', 'desc']) ?
                'DESC' : mb_strtoupper($_GET['order']);

        }

        return $donations_params;

    }

    /**
     * Retrieve items data from the DB. Items are Donations here.
     *
     * @param int $per_page
     * @param int $page_number
     * @return mixed
     */
    protected static function _get_items($per_page, $page_number = 1) {

        $params = ['orderby' => 'id', 'order' => 'desc',];
        if(empty($per_page)) {
            $params['get_all'] = true;
        } else {
            $params = $params + ['results_limit' => absint($per_page), 'page' => absint($page_number),];
        }

        return Leyka_Donations::get_instance()->get(apply_filters('leyka_admin_donations_list_filter', $params, 'get_donations'));

    }

    /**
     * Delete a donation record.
     *
     * @param int $donation_id Donation ID
     */
    protected static function _delete_item($donation_id) {
        Leyka_Donations::get_instance()->delete_donation(absint($donation_id));
    }

    /**
     * @return int
     */
    public static function get_items_count() {

        if(self::$_items_count === NULL) {
            self::$_items_count = Leyka_Donations::get_instance()->get_count(
                apply_filters('leyka_admin_donations_list_filter', [], 'get_donations_count')
            );
        }

        return self::$_items_count;

    }

    /** Text displayed when no data is available. */
    public function no_items() {
        _e('No donations avaliable.', 'leyka');
    }

    /**
     *  An associative array of columns.
     *
     * @return array
     */
    public function get_columns() {

        $columns = [
            'cb' => '<input type="checkbox">',
            'id' => __('ID'),
            'payment_type' => __('Type', 'leyka'),
            'campaign' => __('Campaign', 'leyka'),
            'donor' => __('Donor', 'leyka'),
            'amount' => leyka_options()->opt('admin_donations_list_display') === 'amount-column' ?
                __('Amount / Without commission', 'leyka') : __('Amount', 'leyka'),
            'date' => __('Date', 'leyka'),
            'gateway_pm' => __('Payment method', 'leyka'),
            'emails' => __('Donor email', 'leyka'),
        ];

        if(leyka_options()->opt('admin_donations_list_display') === 'separate-column') {
            $columns['amount_total'] = __('Total amount', 'leyka');
        }

        $columns['donor_subscription'] = __('Donor subscription', 'leyka');
        $columns['donor_comment'] = __('Donor comment', 'leyka');

        // Additional fields columns:
        foreach(leyka_options()->opt('additional_donation_form_fields_library') as $field_id => $field_settings) {
            $columns['additional_field-'.$field_id] = $field_settings['title'];
        }

        return apply_filters('leyka_admin_donations_columns_names', $columns);

    }

    public function get_default_hidden_columns($hidden) {
        return array_merge($hidden, ['donor_subscription', 'donor_comment',]);
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'id' => ['id', true],
            'payment_type' => ['payment_type', true],
            'campaign' => ['campaign_id', true],
            'donor' => ['donor_name'],
            'amount' => ['amount', true],
            'date' => ['date', true],
//            'gateway_pm' => ['gateway_pm', true],
//            'status' => ['status'],
        ];
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param Leyka_Donation_Base $donation
     * @param string $column_id
     * @return mixed
     */
    public function column_default($donation, $column_id) {

        switch($column_id) {
            case 'id':
                return apply_filters('leyka_admin_donation_id_column_content', $donation->id, $donation);
            case 'payment_type':
                return apply_filters(
                    'leyka_admin_donation_type_column_content',
                    '<i class="icon-payment-type icon-'.($donation->is_init_recurring_donation ? 'rebill-init' : $donation->payment_type).' has-tooltip" title="'.$donation->payment_type_label.'"></i>',
                    $donation
                );
            case 'donor_comment':
                return apply_filters('leyka_admin_donation_donor_comment_column_content', $donation->donor_comment, $donation);
            case 'date':
                return apply_filters(
                    'leyka_admin_donation_date_column_content',
                    $donation->date_label.'<br>'.$donation->time_label,
                    $donation
                );
            default:

                if(mb_stristr($column_id, 'additional_field-') !== false) { // "For all campaigns" additional field column

                    $field_id = str_replace('additional_field-', '', $column_id);

                    if(is_array($donation->additional_fields) && !empty($donation->additional_fields[$field_id])) {
                        return apply_filters(
                            'leyka_admin_donation_additional_field_column_content',
                            $donation->additional_fields[$field_id],
                            $donation
                        );
                    }

                } else {
                    return apply_filters("leyka_admin_donation_{$column_id}_column_content", '', $donation);
                }
        }

        return '';

    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $donation
     * @return string
     */
    public function column_cb($donation) { /** @var $donation Leyka_Donation_Base */
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s">', $donation->id);
    }

    public function column_campaign($donation) { /** @var $donation Leyka_Donation_Base */

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $column_content = '<div class="donation-campaign"><a href="'.Leyka_Donation_Management::get_donation_edit_link($donation).'">'.$campaign->title.'</a></div>'
            .$this->row_actions([
                'donation_page' => '<a href="'.Leyka_Donation_Management::get_donation_edit_link($donation).'">'.__('Edit').'</a>',
                'delete' => '<a href="'.Leyka_Donation_Management::get_donation_delete_link($donation).'">'.__('Delete').'</a>',
            ]);

        return apply_filters('leyka_admin_donation_campaign_column_content', $column_content, $donation);

    }

    public function column_donor($donation) { /** @var $donation Leyka_Donation_Base */

        $donor_data_html = apply_filters(
            'leyka_admin_donation_donor_column_content',
            '<div class="donor-name">'
                .(leyka_options()->opt('donor_management_available') && $donation->donor_id ? '<a href="'.admin_url('?page=leyka_donor_info&donor='.$donation->donor_id).'">' : '')
                .$donation->donor_name
                .(leyka_options()->opt('donor_management_available') ? '</a>' : '')
            .'</div>'
            .'<div class="donor-email">'.$donation->donor_email.'</div>',
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

        return '<div class="leyka-donor-data-cell-wrapper">'
                .'<div class="leyka-donor-data-additional">'
                    .'<i class="icon-donor-more-data has-tooltip leyka-tooltip-on-click leyka-tooltip-wide leyka-tooltip-white" data-tooltip-additional-classes="leyka-admin-tooltip-donor-more-data"></i>'
                        .'<span class="leyka-tooltip-content">'
                            .apply_filters(
                                'leyka_admin_donation_donor_column_additional_data_full_html',
                                $donor_additional_data_html,
                                $donation
                            )
                        .'</span>'
                    .'</div>'
                .'<div class="leyka-donor-data-main">'.$donor_data_html.'</div>'
            .'</div>';

    }

    public function column_amount($donation) { /** @var $donation Leyka_Donation_Base */

        if(leyka_options()->opt('admin_donations_list_amount_display') == 'amount-column') {
            $amount = $donation->amount == $donation->amount_total ?
                $donation->amount_formatted.'&nbsp;'.$donation->currency_label :
                $donation->amount_formatted.'&nbsp;'.$donation->currency_label
                    .'<span class="amount-total"> / '
                        .$donation->amount_total_formatted.'&nbsp;'.$donation->currency_label
                    .'</span>';
        } else {
            $amount = $donation->amount_formatted.'&nbsp;'.$donation->currency_label;
        }

        $column_content = '<span class="leyka-amount '.apply_filters('leyka_admin_donation_amount_column_css', ($donation->amount < 0.0 ? 'leyka-amount-negative' : '')).'">'
            .'<i class="icon-leyka-donation-status icon-'.$donation->status.' has-tooltip leyka-tooltip-align-left" title=""></i>'
            .'<span class="leyka-tooltip-content">'
                .apply_filters(
                    'leyka_admin_donations_list_donation_status_tooltip_content',
                    '<strong>'.$donation->status_label.':</strong> '.mb_lcfirst($donation->status_description),
                    $donation
                )
            .'</span>'
            .'<span class="leyka-amount-and-status">'
                .'<div class="leyka-amount-itself">'.$amount.'</div>'
                .'<div class="leyka-donation-status-label label-'.$donation->status.'">'.$donation->status_label.'</div>'
            .'</span>
        </span>';

        return apply_filters('leyka_admin_donation_amount_column_content', $column_content, $donation);

    }

    public function column_amount_total($donation) { /** @var $donation Leyka_Donation_Base */

        $column_content = '<span class="'.apply_filters('leyka_admin_donation_amount_total_column_css', $donation->amount_total < 0 ? 'amount-negative' : 'amount').'">'
            .apply_filters('leyka_admin_donation_amount_total_column_content', $donation->amount_total.'&nbsp;'.$donation->currency_label, $donation)
            .'</span>';

        return apply_filters('leyka_admin_donation_amount_total_column_content', $column_content, $donation);

    }

    public function column_gateway_pm($donation) { /** @var $donation Leyka_Donation_Base */

        $gateway_label = $donation->gateway_id && $donation->gateway_id !== 'correction' ?
            $donation->gateway_label : __('Custom payment info', 'leyka');
        $pm_label = $donation->gateway_id && $donation->gateway_id !== 'correction' ? $donation->pm_label : $donation->pm;
        $gateway = $donation->gateway_id ? leyka_get_gateway_by_id($donation->gateway_id) : false;

        return apply_filters(
            'leyka_admin_donation_gateway_pm_column_content',
            "<div class='leyka-gateway-name'>"
                .($gateway ? "<img src='".$gateway->icon_url."' alt='$gateway_label'>" : '')
                ."$gateway_label,
            </div>
            <div class='leyka-pm-name'>$pm_label</div>",
            $donation
        );

    }

    public function column_emails($donation) { /** @var $donation Leyka_Donation_Base */

        if($donation->donor_email_date) {
            $column_content = str_replace(
                '%s',
                '<time>'.date(get_option('date_format').', H:i</time>', $donation->donor_email_date).'</time>',
                __('Sent at %s', 'leyka')
            );
        } else {
            $column_content = '<div class="leyka-no-donor-thanks" data-donation-id="'.$donation->id.'" data-nonce="'.wp_create_nonce('leyka_donor_email').'">'
                .sprintf(__('Not sent %s', 'leyka'), '<a class="send-donor-thanks" href="#">'.__('Send it', 'leyka').'</a>')
            .'</div>';
        }

        return apply_filters('leyka_admin_donation_emails_column_content', $column_content, $donation);

    }

    public function column_donor_subscription($donation) { /** @var $donation Leyka_Donation_Base */

        if($donation->donor_subscribed == 1) { // true|1|'1' - all news
            $column_content = '<div class="donor-subscription-status total">'.__('Full subscription', 'leyka').'</div>';
        } else if($donation->donor_subscribed > 0) { // Other positive integer (campaign ID) - only news for given campaign
            $column_content = '<div class="donor-subscription-status on-campaign">'
                .sprintf(__('On <a href="%s">campaign</a> news', 'leyka'), admin_url('post.php?post='.$donation->campaign_id.'&action=edit'))
            .'</div>';
        } else {
            $column_content = '<div class="donor-subscription-status none">'.__('None', 'leyka').'</div>';
        }

        return apply_filters('leyka_admin_donation_donor_subscription_column_content', $column_content, $donation);

    }

    /**
     * Table filters panel.
     *
     * @param string $which "top" fro the upper panel or "bottom" for footer.
     */
    protected function extra_tablenav($which) { // The table filters are external - no need for them here
    }

    protected function get_views() {

        $base_page_url = admin_url('admin.php?page=leyka_donations');
        $links = ['all' => '<a href="'.$base_page_url.'">'.__('All').'</a>',];

        foreach(leyka_get_donation_status_list(false) as $status => $label) { /** @todo Remove "false" when "trash" Donation status will be in use */
            $links[$status] = '<a href="'.$base_page_url.'&status='.$status.'" class="'.(isset($_GET['status']) && $_GET['status'] === $status ? 'current' : '').'">'.$label.'</a>';
        }

        return $links;

    }

    /**
     * Data query, filtering, sorting & pagination handler.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('donations_per_page');

        $this->set_pagination_args(['total_items' => self::get_items_count(), 'per_page' => $per_page,]);

        $this->items = self::_get_items($per_page, $this->get_pagenum());

    }

    protected function display_tablenav($which) {

        if($which === 'top') {
            wp_nonce_field('bulk-'.$this->_args['plural'], '_wpnonce', false);
        }?>

        <div class="tablenav <?php echo esc_attr($which); ?>">

        <?php if($this->has_items()) { ?>
            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions($which); ?>
            </div>
        <?php }

        $this->extra_tablenav($which);
        $this->pagination($which);?>

            <br class="clear">
        </div>

    <?php }

    /**
     * @return array
     */
    public function get_bulk_actions() {
        return ['bulk-delete' => __('Delete'),];
    }

    public function process_bulk_action() {

        if($this->current_action() === 'delete') { // Single donation deletion

            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donation') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {
                self::_delete_item(absint($_GET['donation']));
            }

        }

        if( // Bulk donations deletion
            (isset($_REQUEST['action']) && $_REQUEST['action'] === 'bulk-delete')
            || (isset($_REQUEST['action2']) && $_REQUEST['action2'] === 'bulk-delete')
        ) {

            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'bulk-'.$this->_args['plural']) ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            }

            foreach(esc_sql($_REQUEST['bulk-delete']) as $donation_id) {
                self::_delete_item($donation_id);
            }

        }

    }

    protected function _export() {

        // Just in case that export will require some time:
        ini_set('max_execution_time', 99999);
        set_time_limit(99999);

        ob_start();

        $this->items = apply_filters('leyka_donations_pre_export', self::_get_items(false));

        ob_clean();

        $columns = apply_filters('leyka_donations_export_headers', [
            'ID', 'Имя донора', 'Email', 'Тип платежа', 'Плат. оператор', 'Способ платежа', 'Полная сумма', 'Итоговая сумма', 'Валюта', 'Дата пожертвования', 'Статус', 'Кампания', 'Назначение', 'Подписка на рассылку', 'Email подписки', 'Комментарий'
        ]);

        $rows = [];
        foreach($this->items as $donation) { /** @var $donation Leyka_Donation_Base */

            $campaign = $donation->campaign;

            $currency = $donation->currency_label;
//            $currency_label_encoded = @iconv( // Sometimes currency sighs can't be encoded, so check for it
//                'UTF-8',
//                apply_filters('leyka_donations_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
//                $currency
//            );
//            $currency = $currency_label_encoded ? $currency : $donation->currency_id;

            $donor_subscription = 'Нет';
            if($donation->donor_subscribed === true) {
                $donor_subscription = 'Полная';
            } else if($donation->donor_subscribed > 0) {
                $donor_subscription = 'О кампании «'.$campaign->title.'»';
            }

            $rows[] = apply_filters(
                'leyka_donations_export_line',
                [
                    $donation->id,
                    $donation->donor_name,
                    $donation->donor_email,
                    $donation->payment_type_label,
                    $donation->gateway_label,
                    $donation->payment_method_label,
                    str_replace('.', ',', $donation->amount),
                    str_replace('.', ',', $donation->amount_total),
                    $currency,
                    $donation->date_time_label,
                    $donation->status_label,
                    $campaign->title,
                    $campaign->payment_title,
                    $donor_subscription,
                    $donation->donor_subscription_email,
                    $donation->donor_comment,
                ],
                $donation
            );

        }

        leyka_generate_csv('donations-'.date('d.m.Y-H.i.s'), $rows, $columns); // It will exit automatically

    }

}