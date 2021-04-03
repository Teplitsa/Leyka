<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donations_List_Table extends WP_List_Table {

    protected static $_records_count = NULL;

    public function __construct() {

        parent::__construct(array(
            'singular' => 'donation',
            'plural' => 'donations',
            'ajax' => true,
        ));

        add_filter('leyka_admin_donations_list_filter', array($this, 'filter_donations'), 10, 2);

    }

    /**
     * @param $donations_params array
     * @param $filter_type string
     * @return array|false An array of get_users() params, or false if the $filter_type is wrong
     */
    public function filter_donations(array $donations_params, $filter_type = '') {

        if( !empty($_GET['status']) && in_array($_GET['status'], array_keys(leyka_get_donation_status_list())) ) {
            $donations_params['status'] = $_GET['status'];
        }
        if( !empty($_GET['date-from']) && strtotime($_GET['date-from']) ) {
            $donations_params['date_from'] = $_GET['date-from'];
        }
        if( !empty($_GET['date-to']) && strtotime($_GET['date-to']) ) {
            $donations_params['date_to'] = $_GET['date-to'];
        }
        if( !empty($_GET['payment_type']) && in_array($_GET['payment_type'], array_keys(leyka_get_payment_types_list())) ) {
            $donations_params['payment_type'] = $_GET['payment_type'];
        }
        if( !empty($_GET['gateway_pm']) ) {
            $donations_params['gateway_pm'] = $_GET['gateway_pm'];
        }
        if( !empty($_GET['campaign']) && absint($_GET['campaign']) ) {
            $donations_params['campaign_id'] = absint($_GET['campaign']);
        }
        if(isset($_GET['donor_subscribed']) && $_GET['donor_subscribed'] != '-') {
            $donations_params['donor_subscribed'] = !!$_GET['donor_subscribed'];
        }
        if( !empty($_GET['orderby']) ) {

            $donations_params['orderby'] = $_GET['orderby'];
            $donations_params['order'] = empty($_GET['order']) || !in_array($_GET['order'], array('asc', 'desc')) ?
                'DESC' : mb_strtoupper($_GET['order']);

        }

        return $donations_params;

    }

    /**
     * Retrieve donor’s data from the DB.
     *
     * @param int $per_page
     * @param int $page_number
     * @return mixed
     */
    public static function get_donations($per_page, $page_number = 1) {

        return Leyka_Donations::get_instance()->get(
            apply_filters(
                'leyka_admin_donations_list_filter',
                array(
                    'results_limit' => absint($per_page),
                    'page' => absint($page_number),
                    'orderby' => 'id',
                    'order' => 'desc',
                ),
                'get_donations'
            )
        );

    }

    /**
     * Delete a donation record.
     *
     * @param int $donation_id Donation ID
     */
    public static function delete_donation($donation_id) {
        Leyka_Donations::get_instance()->delete_donation(absint($donation_id));
    }

    /**
     * @return int
     */
    public static function record_count() {

        if(self::$_records_count === NULL) {
            self::$_records_count = Leyka_Donations::get_instance()->get_count(
                apply_filters('leyka_admin_donations_list_filter', array(), 'get_donations_count')
            );
        }

        return self::$_records_count;

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

        $columns = array(
            'cb' => '<input type="checkbox">',
            'donation_id' => __('ID'),
            'campaign' => __('Campaign', 'leyka'),
            'donor' => __('Donor', 'leyka'),
            'amount' => __('Amount', 'leyka'),
            'gateway_pm' => __('Payment method', 'leyka'),
            'date' => __('Donation date', 'leyka'),
            'status' => __('Status', 'leyka'),
            'payment_type' => __('Payment type', 'leyka'),
            'emails' => __('Email', 'leyka'),
            'donor_subscription' => __('Donor subscription', 'leyka'),
        );

        if(leyka_options()->opt('admin_donations_list_display') === 'separate-column') {
            $columns['amount_total'] = __('Total amount', 'leyka');
        }

        return apply_filters('leyka_admin_donations_columns_names', $columns);

    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return array(
            'donation_id' => array('donation_id', true),
            'campaign' => array('campaign_id', true),
            'donor' => array('donor_name'),
            'amount' => array('amount', true),
            'date' => array('date', true),
            'payment_type' => array('payment_type', true),
//            'gateway_pm' => array('gateway_pm', true),
            'status' => array('status'),
        );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $donation
     * @param string $column_name
     * @return mixed
     */
    public function column_default($donation, $column_name) { /** @var $donation Leyka_Donation_Base */
        switch ($column_name) {
            case 'donation_id': return $donation->id;
            case 'payment_type':
                return apply_filters(
                    'leyka_admin_donation_type_column_content',
                    '<i class="'.esc_attr($donation->payment_type).'">'.$donation->payment_type_label.'</i>',
                    $donation
                );
            case 'donor_comment':
                return apply_filters('leyka_admin_donation_donor_comment_column_content', $donation->donor_comment, $donation);
            case 'date':
                return $donation->date_time_label;
            case 'donor_subscription':
                return $donation->donor_subscription;
            default:
                return LEYKA_DEBUG ? print_r($donation, true) : ''; // Show the whole array for troubleshooting purposes
        }
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
            .'<div class="donation-email">'.$donation->donor_email.'</div>'
            .$this->row_actions(array(
                'donation_page' => '<a href="'.Leyka_Donation_Management::get_donation_edit_link($donation).'">'.__('Edit').'</a>',
                'delete' => '<a href="'.Leyka_Donation_Management::get_donation_delete_link($donation).'">'.__('Delete').'</a>',
            ));

        return apply_filters('leyka_admin_donation_campaign_column_content', $column_content, $donation);

    }

    public function column_donor($donation) { /** @var $donation Leyka_Donation_Base */
        return apply_filters(
            'leyka_admin_donation_donor_column_content',
            '<div class="donor-name">'.$donation->donor_name.'</div><div class="donor-email">'.$donation->donor_email.'</div>',
            $donation
        );
    }

    public function column_amount($donation) { /** @var $donation Leyka_Donation_Base */

        if(leyka_options()->opt('admin_donations_list_display') === 'amount-column') {
            $amount = $donation->amount == $donation->amount_total ?
                $donation->amount :
                $donation->amount
                .'<span class="amount-total"> / '.$donation->amount_total.'</span>';
        } else {
            $amount = $donation->amount;
        }

        $column_content = '<span class="'.apply_filters('leyka_admin_donation_amount_column_css', ($donation->sum < 0 ? 'amount-negative' : 'amount')).'">'
            .apply_filters('leyka_admin_donation_amount_column_content', $amount.'&nbsp;'.$donation->currency_label, $donation)
            .'</span>';

        return apply_filters('leyka_admin_donation_amount_column_content', $column_content, $donation);

    }

    public function column_amount_total($donation) { /** @var $donation Leyka_Donation_Base */

        $column_content = '<span class="'.apply_filters('leyka_admin_donation_amount_total_column_css', $donation->amount_total < 0 ? 'amount-negative' : 'amount').'">'
            .apply_filters('leyka_admin_donation_amount_total_column_content', $donation->amount_total.'&nbsp;'.$donation->currency_label, $donation)
            .'</span>';

        return apply_filters('leyka_admin_donation_amount_total_column_content', $column_content, $donation);

    }

    public function column_gateway_pm($donation) { /** @var $donation Leyka_Donation_Base */

        $gateway_label = $donation->gateway_id == 'correction' ? __('Custom payment info', 'leyka') : $donation->gateway_label;
        $pm_label = $donation->gateway_id == 'correction' ? $donation->pm : $donation->pm_label;

        return apply_filters(
            'leyka_admin_donation_gateway_pm_column_content',
            "<b>{$donation->payment_type_label}:</b> $pm_label <small>/ $gateway_label</small>",
            $donation
        );

    }

    public function column_status($donation) { /** @var $donation Leyka_Donation_Base */

        $status_info = leyka()->get_donation_status_info($donation->status);
        if( !$status_info ) { // Unknown status
            return '';
        }

        return apply_filters(
            'leyka_admin_donation_gateway_pm_column_content',
            '<i class="'.esc_attr($donation->status).'">'
                .$status_info['label'].'</i>&nbsp;<span class="dashicons dashicons-editor-help has-tooltip" title="'.$status_info['description'].'"></span>',
            $donation
        );

    }

    public function column_emails($donation) { /** @var $donation Leyka_Donation_Base */

        if($donation->donor_email_date){
            $column_content = str_replace(
                '%s',
                '<time>'.date(get_option('date_format').', H:i</time>', $donation->donor_email_date).'</time>',
                __('Sent at %s', 'leyka')
            );
        } else {
            $column_content = '<div class="leyka-no-donor-thanks" data-donation-id="'.$donation->id.'" data-nonce="'.wp_create_nonce('leyka_donor_email').'">'
                .sprintf(__('Not sent %s', 'leyka'), '<div class="send-donor-thanks">'.__('(send it now)', 'leyka').'</div>')
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

        return apply_filters('leyka_admin_donation_donor_subscribed_column_content', $column_content, $donation);

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
        $links = array('all' => '<a href="'.$base_page_url.'">'.__('All').'</a>');

        foreach(leyka_get_donation_status_list(false) as $status => $label) { /** @todo Remove "false" when "trash" status is in use */
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

        $this->set_pagination_args(array('total_items' => self::record_count(), 'per_page' => $per_page,));

        $this->items = self::get_donations($per_page, $this->get_pagenum());

    }

    protected function display_tablenav($which) {

        if($which === 'top') {
            wp_nonce_field('bulk-'.$this->_args['plural'], '_wpnonce', false);
        }?>

        <div class="tablenav <?php echo esc_attr( $which ); ?>">

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
        return array('bulk-delete' => __('Delete'));
    }

    public function process_bulk_action() {

        if($this->current_action() === 'delete') { // Single donation deletion

            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donation') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {
                self::delete_donation(absint($_GET['donation']));
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
                self::delete_donation($donation_id);
            }

        }

    }

}