<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donors_List_Table extends WP_List_Table {

    protected static $_items_count = NULL;

    public function __construct() {

        parent::__construct(['singular' => __('Donor', 'leyka'), 'plural' => __('Donors', 'leyka'), 'ajax' => true,]);

        add_filter('leyka_admin_donors_list_filter', [$this, 'filter_items'], 10, 2);
        add_action('pre_user_query', [$this, 'filter_donors_pre_user_query']);

        if( !empty($_REQUEST['donors-list-export']) ) {
            $this->_export();
        }

    }

    /**
     * WP_User & user meta fields filtering.
     *
     * @param $donors_params array
     * @param $filter_type string
     * @return array|false An array of get_users() params, or false if the $filter_type is wrong
     */
    public function filter_items(array $donors_params, $filter_type = '') {

        $donors_params['meta_query'] = [];

        if( !empty($_GET['donor-type']) ) {
            if($_GET['donor-type'] == 'regular') {
                $donors_params['meta_query'][] = ['key' => 'leyka_donor_type', 'value' => esc_sql($_GET['donor-type']),];
            } else {

                // If Donor user is of single type, he hasn't any "leyka_donor_type" usermeta.
                // So if this filter clause will slow down the query, comment out the line of comparation with "single":
                $donors_params['meta_query'][] = [
                    'relation' => 'OR',
                    ['key' => 'leyka_donor_type', 'value' => 'single',],
                    ['key' => 'leyka_donor_type', 'compare' => 'NOT EXISTS',]
                ];

            }
        }

        if( !empty($_GET['donor-name-email']) ) {

            $_GET['donor-name-email'] = trim($_GET['donor-name-email']);

            if($_GET['donor-name-email']) {

                $donors_params['search'] = '*'.esc_sql($_GET['donor-name-email']).'*';
                $donors_params['search_columns'] = ['ID', 'display_name', 'user_email',];

            }

        }

        if( !empty($_GET['first-date']) ) {

            if(is_string($_GET['first-date']) && mb_stripos($_GET['first-date'], '-') !== false) { // Dates period chosen as a str

                $filter_date = array_slice(explode('-', $_GET['first-date']), 0, 2);

                if(count($filter_date) === 2) { // The date is set as an interval

                    $filter_date[0] = strtotime(trim($filter_date[0]).' 00:00:00');
                    $filter_date[1] = strtotime(trim($filter_date[1]).' 23:59:59');

                }

            } else if(is_array($_GET['first-date']) && count($_GET['first-date']) === 2) { // Dates period chosen as an array

                $filter_date = $_GET['first-date'];

                $filter_date[0] = strtotime(trim($filter_date[0]).' 00:00:00');
                $filter_date[1] = strtotime(trim($filter_date[1]).' 23:59:59');

            } else {
                $filter_date = trim($_GET['first-date']);
            }

            $donors_params['meta_query'][] = [
                'key' => 'leyka_donor_first_donation_date',
                'value' => is_array($filter_date) ?
                    $filter_date : // Date interval
                    [strtotime($filter_date.' 00:00:00'), strtotime($filter_date.' 23:59:59')], // Single date
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];

        }

        if( !empty($_GET['last-date']) ) {

            if(is_string($_GET['last-date']) && mb_stripos($_GET['last-date'], '-') !== false) { // Dates period chosen as a str

                $filter_date = array_slice(explode('-', $_GET['last-date']), 0, 2);

                if(count($filter_date) === 2) { // The date is set as an interval

                    $filter_date[0] = strtotime(trim($filter_date[0]).' 00:00:00');
                    $filter_date[1] = strtotime(trim($filter_date[1]).' 23:59:59');

                }

            } else if(is_array($_GET['last-date']) && count($_GET['last-date']) === 2) { // Dates period chosen as an array

                $filter_date = $_GET['last-date'];

                $filter_date[0] = strtotime(trim($filter_date[0]).' 00:00:00');
                $filter_date[1] = strtotime(trim($filter_date[1]).' 23:59:59');

            } else {
                $filter_date = trim($_GET['last-date']);
            }

            $donors_params['meta_query'][] = [
                'key' => 'leyka_donor_last_donation_date',
                'value' => is_array($filter_date) ?
                    $filter_date : // Date interval
                    [strtotime($filter_date.' 00:00:00'), strtotime($filter_date.' 23:59:59')], // Single date
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];

        }

        if( !empty($_GET['campaigns']) && !empty($_GET['campaigns'][0]) ) {

            $campaigns_meta_query = ['relation' => 'OR',];

            foreach($_GET['campaigns'] as $campaign_id) {
                $campaigns_meta_query[] = [
                    'key' => 'leyka_donor_campaigns',
                    'value' => 'i:'.absint($campaign_id).';', // A little freaky, I know, but it's the best I could think of
                    'compare' => 'LIKE',
                ];
            }

            $donors_params['meta_query'][] = $campaigns_meta_query;

        }

        if( !empty($_GET['gateway']) && leyka_get_gateway_by_id($_GET['gateway']) ) {

            $donors_params['meta_query'][] = [
                'key' => 'leyka_donor_gateways',
                'value' => esc_sql($_GET['gateway']),
                'compare' => 'LIKE',
            ];

        }

        if(count($donors_params['meta_query']) > 1) {
            $donors_params['meta_query']['relation'] = 'AND';
        }

        if($filter_type) { // If filter type is set, the filtering is not just to get items from DB - so ordering won't be needed
            return $donors_params;
        }

        // Ordering:
        if(isset($_GET['orderby']) && array_key_exists($_GET['orderby'], $this->get_sortable_columns())) {

            switch($_GET['orderby']) {
                case 'id': $donors_params['orderby'] = 'ID'; break;
//                case 'donor_type': /** @todo ATM, there are no meta value for "single" Donor type. It messes up the ordering. */
//                    $donors_params['meta_key'] = 'leyka_donor_type';
//                    $donors_params['orderby'] = 'meta_value';
//                    break;
                case 'donor':
                    $donors_params['orderby'] = 'display_name'; break;
                case 'first_donation':
                    $donors_params['meta_key'] = 'leyka_donor_first_donation_date';
                    $donors_params['orderby'] = 'meta_value_num';
                    break;
                case 'last_donation':
                    $donors_params['meta_key'] = 'leyka_donor_last_donation_date';
                    $donors_params['orderby'] = 'meta_value_num';
                    break;
                case 'amount_donated':
                    $donors_params['meta_key'] = 'leyka_amount_donated';
                    $donors_params['orderby'] = 'meta_value_num';
                break;
                default:
            }

            if($donors_params['orderby']) {
                $donors_params['order'] = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
            }

        }

        return $donors_params;

    }

    public function filter_donors_pre_user_query(WP_User_Query $donors_query){

        // Donors tags filter:
        if( !empty($_REQUEST['donors-tags']) ) {

            $_REQUEST['donors-tags'] = (array)$_REQUEST['donors-tags'];

            array_walk($_REQUEST['donors-tags'], function($value, $key){ // Remove empty values from filter

                $value = absint($value);

                if( !$value ) {
                    unset($_REQUEST['donors-tags'][$key]);
                }

            });

        }

        if( !empty($_REQUEST['donors-tags']) ) {

            global $wpdb;

            $donors_query->query_where .= $wpdb->prepare(
                " AND {$wpdb->users}.ID IN (
                        SELECT {$wpdb->term_relationships}.object_id
                        FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
                        WHERE {$wpdb->term_taxonomy}.term_id IN (".implode(',', $_REQUEST['donors-tags']).") AND {$wpdb->term_taxonomy}.taxonomy = %s
                    )",
                Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME
            );

        }

        // "Only Donors with cancelled recurring subscriptions" filter:
//        if( !empty($_REQUEST['leyka_donors-cancelled']) ) {
//
//            $cancelled_recurring_subscriptions = get_posts([
//                'post_type' => Leyka_Donation_Management::$post_type,
//                'post_status' => 'funded',
//                'posts_per_page' => -1,
//                'post_parent' => 0,
//                'meta_query' => [
//                    'relation' => 'AND',
//                    ['key' => 'leyka_payment_type', 'value' => 'rebill',],
//                    ['key' => '_rebilling_is_active', 'value' => 1, 'compare' => '!='],
//                ],
//            ]);
//
//            $cancelled_donors_ids = [];
//            foreach($cancelled_recurring_subscriptions as $donation) {
//
//                $donation = new Leyka_Donation($donation);
//                $cancelled_donors_ids[] = $donation->donor_user_id;
//
//            }
//
//            if( !$cancelled_donors_ids ) {
//                $cancelled_donors_ids[] = 0;
//            }
//
//            global $wpdb;
//            $donors_query->query_where .= " AND {$wpdb->users}.ID IN (".implode(',', array_unique($cancelled_donors_ids)).")";
//
//        }

    }

    /**
     * Retrieve donor’s data from the DB.
     *
     * @param int|false $per_page A number of lines per page, or false to not use pagination.
     * @param int $page_number A page number. Will not be in use if $per_page === false.
     *
     * @return array
     */
    protected static function _get_items($per_page = false, $page_number = 1) {

        $donors_params = apply_filters('leyka_admin_donors_list_filter', [
            'role__in' => [Leyka_Donor::DONOR_USER_ROLE,],
            'number' => $per_page ? absint($per_page) : -1,
            'paged' => $page_number ? absint($page_number) : false,
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        $donors = [];
        foreach(get_users($donors_params) as $donor_user) {

            try {
                $donor = new Leyka_Donor($donor_user);
            } catch(Exception $e) {
            	continue;
            }

            $donor_data = [
                'donor_id' => $donor->id,
                'donor_name' => $donor->name,
                'donor_email' => $donor->email,
                'donor' => $donor,
                'first_donation' => $donor->first_donation_id,
                'last_donation' => $donor->last_donation_id,
                'donors_tags' => $donor->get_tags(),
            ];

            $donor_data['donor_type'] = $donor->type;
            $donor_data['campaigns'] = $donor->campaigns;
            $donor_data['gateways'] = $donor->gateways;
            $donor_data['amount_donated'] = $donor->amount_donated;

            $donors[] = $donor_data;

        }

        return $donors;

    }

    /**
     * Delete a donor record.
     *
     * @param int $donor_user_id Donor ID
     */
    protected static function _delete_item($donor_user_id) {

        if(leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $donor_user_id)) {
            wp_delete_user(absint($donor_user_id));
        } else {

            $donor = get_user_by('id', $donor_user_id);
            $donor->remove_role(Leyka_Donor::DONOR_USER_ROLE);
            $donor->remove_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP);

        }

    }

    /** * @return integer */
    public static function get_items_count() {

        if(self::$_items_count === NULL) {

            $donors = new WP_User_Query(apply_filters(
                'leyka_admin_donors_list_filter',
                [
                    'role__in' => [Leyka_Donor::DONOR_USER_ROLE,],
                    'number' => -1,
                    'count_total' => true,
                    'fields' => ['id',],
                ],
                'get_donors_total_count'
            ));

            self::$_items_count = $donors->get_total();

        }

        return self::$_items_count;

    }

    /** Text displayed when no donors data is available. */
    public function no_items() {
        _e('No donors avaliable.', 'leyka');
    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return [
            'cb' => '<input type="checkbox">',
            'id' => __('ID', 'leyka'),
            'donor_type' => _x('Type', "Donor's type", 'leyka'),
            'donor' => __("Donor's name", 'leyka'),
            'first_donation' => __('First donation', 'leyka'),
            'campaigns' => __('Campaigns list', 'leyka'),
            'donors_tags' => __("Donors' tags", 'leyka'),
            'gateways' => __('Gateway', 'leyka'),
            'last_donation' => __('Last donation', 'leyka'),
            'amount_donated' => __('Amount donated', 'leyka'),
        ];
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'id' => ['id', true],
            /** @todo ATM, there are no meta value for "single" Donor type. It messes up the ordering. */
//            'donor_type' => ['donor_type', true],
            'donor' => ['donor', false],
            'first_donation' => ['first_donation', true],
            'last_donation' => ['last_donation', true],
            'amount_donated' => ['amount_donated', true],
        ];
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
                return apply_filters('leyka_admin_donor_id_column_content', $item['donor_id'], $item);
            case 'donor':
            case 'first_donation':
            case 'campaigns':
            case 'last_donation':
            case 'donors_tags':
            case 'gateways':
            case 'amount_donated':
                return $item[$column_name];
            default: // Show the whole array for troubleshooting purposes
                return leyka_options()->opt('plugin_debug_mode') ?
                    '<pre>'.print_r($item, true).'</pre>' : // Show the whole array for troubleshooting purposes
                    apply_filters("leyka_admin_donor_{$column_name}_column_content", '', $item);
        }
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     * @return string
     */
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk[]" value="%s">', $item['donor_id']);
    }

    public function column_donor_type($item) {
        return apply_filters(
            'leyka_admin_donor_type_column_content',
            '<i class="icon-donor-type icon-'.$item['donor_type'].' has-tooltip" title="'._x(mb_ucfirst($item['donor_type']), 'Donor type name', 'leyka').'"></i>',
            $item
        );
    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_donor($item) {

        $item['donor_id'] = absint($item['donor_id']);
        $admin_donor_page = admin_url('?page=leyka_donor_info&donor='.$item['donor_id']);

        $donor_data_html = apply_filters(
            'leyka_admin_donor_donor_column_content',
            '<div class="donor-name"><a href="'.$admin_donor_page.'">'.$item['donor_name'].'</a></div>'
            .'<div class="donor-email">'.$item['donor_email'].'</div>',
            $item
        );

        $donor_description = $item['donor']->description ? mb_ucfirst($item['donor']->description) : __('no', 'leyka');
        $donor_last_comment = $item['donor']->get_comments();
        $donor_last_comment = $donor_last_comment ? array_pop($donor_last_comment)['text'] : __('no', 'leyka');

        $donor_additional_data_html = '<ul>
        <li>
            <span class="leyka-li-title">'.__('Description', 'leyka').':</span>
            <span class="leyka-li-value">'.$donor_description.'</span>
        </li>
        <li>
            <span class="leyka-li-title">'.__('Last comment', 'leyka').':</span>
            <span class="leyka-li-value">'.esc_html($donor_last_comment).'</span>
        </li>';
        $donor_additional_data_html .= '</ul>';

        $column_content = (
            $donor_additional_data_html ?
                '<div class="leyka-donor-data-additional">'
                    .'<i class="icon-donor-more-data has-tooltip leyka-tooltip-on-click leyka-tooltip-wide leyka-tooltip-white" data-tooltip-additional-classes="leyka-admin-tooltip-donor-more-data"></i>'
                    .'<span class="leyka-tooltip-content">'
                        .apply_filters('leyka_admin_donor_donor_column_additional_data', $donor_additional_data_html, $item)
                    .'</span>'
                .'</div>' : ''
            )
            .'<div class="leyka-donor-data-main">'
                .$donor_data_html
                .$this->row_actions([
                    'donor_page' => '<a href="'.$admin_donor_page.'">'.__('Edit').'</a>',
                    'delete' => sprintf(
                        '<a href="?page=%s&action=%s&donor=%s&_wpnonce=%s">'.__('Delete', 'leyka').'</a>',
                        esc_attr($_GET['page']),
                        'delete',
                        $item['donor_id'],
                        wp_create_nonce('leyka_delete_donor')
                    ),
                ])
            .'</div>';

        return apply_filters('leyka_admin_donor_data_column_content', $column_content, $item);

    }

    public function column_first_donation($item) {

        if(empty($item['first_donation'])) {
            return '';
        }

        try{
            $donation = Leyka_Donations::get_instance()->get($item['first_donation']);
        } catch(Exception $ex) { // Donor's last Donation isn't found by ID, try to find it anew

            $donation = Leyka_Donations::get_instance()->get([
                'donor_id' => $item['donor_id'],
                'status' => 'funded',
                'order' => 'ASC',
                'get_single' => true,
            ]);

            if($donation) {

                $donor = new Leyka_Donor($item['donor_id']);
                $donor->first_donation_id = $donation->id;
                $donor->first_donation_date_timestamp = $donation->date_timestamp;

            }

        }

        return apply_filters(
            'leyka_admin_donor_first_donation_column_content',
            $donation ? $donation->date_label.'<br>'.$donation->time_label : '',
            $item,
            $donation
        );

    }

    public function column_donor_additional_fields($item) {

//        $fields_settings = Leyka_Campaign::get_additional_fields_settings($donation->campaign_id);
        $html = '';
//
//        if($donation->additional_fields) {
//
//            $html .= '<ul class="'.apply_filters('leyka_admin_donation_additional_fields_column_css', '').'">';
//            foreach($donation->additional_fields as $field_id => $field_value) {
//
//                $html .= '<li>'
//                    .(isset($fields_settings[$field_id]) ? $fields_settings[$field_id]['title'] : $field_id)
//                    .(empty($fields_settings[$field_id]['is_required']) ? '' : '<span class="required">*</span>')
//                    .': '.(esc_html($field_value))
//                    .'</li>';
//
//            }
//            $html .= '</ul>';
//
//        }

        return apply_filters('leyka_admin_donor_additional_fields_column_content', $html);

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_campaigns($item) {

        if(empty($item['campaigns']) || !is_array($item['campaigns'])) {
            return '';
        }

        $campaigns_list = [];
        foreach($item['campaigns'] as $campaign_id => $campaign_title) {
            if($campaign_title) {
                $campaigns_list[] = '«'.ltrim(rtrim($campaign_title, '»'), '«').'»';
            }
        }

        sort($campaigns_list);

        $first_campaigns_for_list = array_splice($campaigns_list, 0, 3, []);

        $column_content = '<div class="leyka-admin-shortened-text">'
            .implode(', ', $first_campaigns_for_list)
            .($campaigns_list ?
                '<div class="leyka-more-campaigns">'.sprintf(__('+ %d more', 'leyka'), count($campaigns_list)).'</div>' : '')
            .'</div>';

        return apply_filters('leyka_admin_donor_campaigns_column_content', $column_content, $item);

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_donors_tags($item) {

        if(empty($item['donors_tags']) || !is_array($item['donors_tags'])) {
            return '';
        }

        $tags_list = [];
        foreach($item['donors_tags'] as $term) { /** @var $term WP_Term */
            $tags_list[] = '#<a href="?page='.esc_attr($_REQUEST['page']).'&donors-tags[0]='.$term->term_id.'">'.esc_html($term->name).'</a>';
        }

        return apply_filters(
            'leyka_admin_donor_tags_column_content',
            '<div class="leyka-donors-tags-list">'.implode(', ', $tags_list).'</div>',
            $item['donors_tags'],
            $item
        );

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_gateways($item) {

        if(empty($item['gateways']) || !is_array($item['gateways'])) {
            return '';
        }

        $gateways_list = [];
        foreach($item['gateways'] as $gateway_id) {

            $gateway = leyka_get_gateway_by_id($gateway_id);
            if($gateway) {
                $gateways_list[$gateway_id] = $gateway;
            }

        }
        sort($gateways_list);

        $column_content = '';

        foreach($gateways_list as $gateway) {
            $column_content .= '<li>'.
                "<div class='leyka-gateway-name'>"
                    .($gateway ? "<img src='".$gateway->icon_url."' alt='{$gateway->label}'>" : '')
                    .$gateway->label
                ."</div>"
            .'</li>';
        }

        return apply_filters(
            'leyka_admin_donor_gateways_column_content',
            '<ul class="leyka-gateways-list">'.$column_content.'</ul>',
            $item
        );

    }

    public function column_last_donation($item) {

        if(empty($item['last_donation'])) {
            return '';
        }

        try{
            $donation = Leyka_Donations::get_instance()->get($item['last_donation']);
        } catch(Exception $ex) { // Donor's last Donation isn't found by ID, try to find it anew

            $donation = Leyka_Donations::get_instance()->get([
                'donor_id' => $item['donor_id'],
                'status' => 'funded',
                'order' => 'DESC',
                'get_single' => true,
            ]);

            if($donation) {

                $donor = new Leyka_Donor($item['donor_id']);
                $donor->last_donation_id = $donation->id;
                $donor->last_donation_date_timestamp = $donation->date_timestamp;

            }

        }

        if($donation) {

            $column_content = '<i class="icon-leyka-donation-status icon-'.$donation->status.' has-tooltip leyka-tooltip-align-left" title=""></i>'
                .'<span class="leyka-tooltip-content">'
                    .apply_filters(
                        'leyka_admin_donors_list_donation_status_tooltip_content',
                        '<strong>'.$donation->status_label.':</strong> '.mb_lcfirst($donation->status_description),
                        $donation
                    )
                .'</span>'
                .'<div class="first-sub-row">'
                    .'<span class="leyka-donation-amount">'.leyka_format_amount($donation->amount).'&nbsp;'.$donation->currency_label.',</span>'
                    .'<span class="leyka-donation-date">'.$donation->date_time_label.'</span>'
                .'</div>'
                .'<div class="second-sub-row">«'.$donation->campaign_title.'»</div>';

        } else {
            $column_content = '';
        }

        return apply_filters('leyka_admin_donor_last_donation_column_content', $column_content, $item, $donation);

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_amount_donated($item) {
        return apply_filters(
            'leyka_admin_donor_amount_donated_column_content',
            empty($item['amount_donated']) ? '' : leyka_format_amount($item['amount_donated']).'&nbsp;'.leyka_get_currency_label(),
            $item['amount_donated'],
            $item
        );
    }

    /**
     * @return array
     */
    public function get_bulk_actions() {
        return ['bulk-edit' => __('Edit'), 'bulk-delete' => __('Delete'),];
    }

    /**
     * Data query, filtering, sorting & pagination handler.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('donors_per_page');

        $this->set_pagination_args(['total_items' => self::get_items_count(), 'per_page' => $per_page,]);

        $this->items = self::_get_items($per_page, $this->get_pagenum());

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

    public function process_bulk_action() {

        // Single donor deletion:
        if($this->current_action() === 'delete') {

            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donor') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {
                self::_delete_item(absint($_GET['donor']));
            }

        }

        // Bulk donors deletion:
        if(
            (isset($_POST['action']) && $_POST['action'] === 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] === 'bulk-delete')
        ) {

            foreach(esc_sql($_POST['bulk']) as $donor_id) {
                self::_delete_item($donor_id);
            }

        }

    }

    public function bulk_edit_fields() {?>

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

    <?php }

    protected function _export() {

        // Just in case that export will require some time:
        ini_set('max_execution_time', 99999);
        set_time_limit(99999);

        ob_start();

        $this->items = self::_get_items();

        ob_clean();

        $columns = apply_filters('leyka_donors_export_headers', [
            'ID', 'Тип донора', 'Имя', 'Email', 'Дата первого пожертвования', 'Сумма первого пожертвования', 'Кампания первого пожертвования', 'Метки донора', 'Кампании', 'Платёжные операторы', 'Дата последнего пожертвования', 'Сумма последнего пожертвования', 'Кампания последнего пожертвования', 'Общая сумма пожертвований', 'Валюта',
        ]);

        $rows = [];
        foreach($this->items as $donor_data) {

            $first_donation = $donor_data['first_donation'] ?
                Leyka_Donations::get_instance()->get($donor_data['first_donation']) : false;
            $last_donation = $donor_data['last_donation'] ?
                Leyka_Donations::get_instance()->get($donor_data['last_donation']) : false;

            $donor_tags_list = [];
            if( !empty($donor_data['donors_tags']) ) {
                foreach($donor_data['donors_tags'] as $term) { /** @var $term WP_Term */
                    $donor_tags_list[] = esc_attr($term->name);
                }
            }
            $donor_tags_list = implode(', ', $donor_tags_list);

            $donor_campaigns_list = [];
            if( !empty($donor_data['campaigns']) ) {
                foreach($donor_data['campaigns'] as $campaign_id => $campaign_title) {
                    if($campaign_title) {
                        $donor_campaigns_list[] = $campaign_title;
                    }
                }
            }
            sort($donor_campaigns_list);
            $donor_campaigns_list = implode(', ', $donor_campaigns_list);

            $donor_gateways_list = [];
            if( !empty($donor_data['gateways']) ) {
                foreach($donor_data['gateways'] as $gateway_id) {

                    $gateway = leyka_get_gateway_by_id($gateway_id);
                    if($gateway) {
                        $donor_gateways_list[] = esc_attr($gateway->label);
                    }

                }
            }
            sort($donor_gateways_list);
            $donor_gateways_list = implode(', ', $donor_gateways_list);

            $currency = leyka_get_currency_label();
//            $currency_label_encoded = @iconv( // Sometimes currency sighs symbols can't be encoded, so check for it
//                'UTF-8',
//                apply_filters('leyka_donations_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
//                $currency
//            );
//            $currency = $currency_label_encoded ? $currency : leyka_options()->opt_safe('currency_main');

            $donors_types_labels = leyka_get_donor_types();

            $rows[] = apply_filters('leyka_donors_export_line', [
                $donor_data['donor_id'],
                mb_strtolower($donors_types_labels[$donor_data['donor_type']]),
                $donor_data['donor_name'],
                $donor_data['donor_email'],
                ($first_donation ? $first_donation->date_time_label : ''),
                ($first_donation ? str_replace('.', ',', $first_donation->amount) : ''),
                ($first_donation ? $first_donation->campaign_title : ''),
                $donor_tags_list,
                $donor_campaigns_list,
                $donor_gateways_list,
                ($last_donation ? $last_donation->date_time_label : ''),
                ($last_donation ? str_replace('.', ',', $last_donation->amount) : ''),
                ($last_donation ? $last_donation->campaign_title : ''),
                $donor_data['amount_donated'],
                $currency,
            ], $donor_data);

        }

        leyka_generate_csv('donors-'.date('d.m.Y-H.i.s'), $rows, $columns); // It will exit automatically

    }

}