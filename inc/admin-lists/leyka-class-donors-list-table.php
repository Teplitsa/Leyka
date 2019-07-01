<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donors_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Donor', 'leyka'), 'plural' => __('Donors', 'leyka'), 'ajax' => true,));

        add_filter('leyka_admin_donors_list_filter', array($this, 'filter_donors'));

    }

    public function filter_donors(array $donors_params) {

        $donors_params['meta_query'] = array();
        if( !empty($_REQUEST['donor-type']) ) {
            $donors_params['meta_query'][] = array('key' => 'leyka_donor_type', 'value' => esc_sql($_REQUEST['donor-type']),);
        }

        if( !empty($_REQUEST['donor-name-email']) ) {

            $_REQUEST['donor-name-email'] = trim($_REQUEST['donor-name-email']);

            if($_REQUEST['donor-name-email']) {

                $donors_params['search'] = '*'.esc_sql($_REQUEST['donor-name-email']).'*';
                $donors_params['search_columns'] = array('ID', 'display_name', 'user_email',);

            }

        }

        if( !empty($_REQUEST['gateways']) ) {

            $gateways_meta_query = array('relation' => 'OR',);

            foreach($_REQUEST['gateways'] as $gateway_id) {
                $gateways_meta_query[] = array(
                    'key' => 'leyka_donor_gateways',
                    'value' => esc_sql($gateway_id),
                    'compare' => 'LIKE',
                );
            }

            $donors_params['meta_query'][] = $gateways_meta_query;

        }

        if(count($donors_params['meta_query']) > 1) {
            $donors_params['meta_query']['relation'] = 'AND';
        }

        // Ordering:
        if(isset($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'], $this->get_sortable_columns())) {

            switch($_REQUEST['orderby']) {
                case 'donor_id': $donors_params['orderby'] = 'ID'; break;
                case 'donor_type':
                    $donors_params['meta_key'] = 'leyka_donor_type';
                    $donors_params['orderby'] = 'meta_value';
                    break;
                case 'donor_name':
                    $donors_params['orderby'] = 'display_name'; break;
                case 'first_donation': /** @todo Testing needed (after date fields are finished) */
                    $donors_params['meta_key'] = 'leyka_donor_first_donation_date';
                    $donors_params['orderby'] = 'meta_value_num';
                    break;
                case 'last_donation': /** @todo Testing needed (after date fields are finished) */
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
                $donors_params['order'] = isset($_REQUEST['order']) && $_REQUEST['order'] == 'asc' ? 'ASC' : 'DESC';
            }

        }

        return $donors_params;

    }

    /**
     * Retrieve donor’s data from the DB.
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_donors($per_page, $page_number = 1) {

        $donors_params = apply_filters('leyka_admin_donors_list_filter', array(
            'role__in' => array('donor',),
            'number' => absint($per_page),
            'paged' => absint($page_number),
            'fields' => array('ID', 'user_email', 'display_name',),
        ), 'get_donors');

        // Donors tags filter:
        add_action('pre_user_query', function(WP_User_Query $donor_users_query){

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

                $donor_users_query->query_where .= $wpdb->prepare(
                    " AND {$wpdb->users}.ID IN (
                        SELECT {$wpdb->term_relationships}.object_id
                        FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
                        WHERE {$wpdb->term_taxonomy}.term_id IN (".implode(',', $_REQUEST['donors-tags']).") AND {$wpdb->term_taxonomy}.taxonomy = %s
                    )",
                    LEYKA_DONORS_TAGS_TAXONOMY_NAME
                );

            }

        });
        // Donors tags filter - END

        $donors = array();
        foreach(get_users($donors_params) as $donor_user) {

            $donor_data = array(
                'donor_id' => $donor_user->ID,
                'donor_name' => $donor_user->display_name,
                'donor_email' => $donor_user->user_email,
                'first_donation' => get_user_meta($donor_user->ID, 'leyka_donor_first_donation_id', true),
                'last_donation' => get_user_meta($donor_user->ID, 'leyka_donor_last_donation_id', true),
                'donors_tags' => wp_get_object_terms($donor_user->ID, LEYKA_DONORS_TAGS_TAXONOMY_NAME),
            );

            $donor_data['donor_type'] = get_user_meta($donor_user->ID, 'leyka_donor_type', true);
            $donor_data['campaigns'] = get_user_meta($donor_user->ID, 'leyka_donor_campaigns', true);
            $donor_data['gateways'] = get_user_meta($donor_user->ID, 'leyka_donor_gateways', true);

            $donor_data['amount_donated'] = get_user_meta($donor_user->ID, 'leyka_amount_donated', true);
            $donor_data['amount_donated'] = $donor_data['amount_donated'] ?
                $donor_data['amount_donated'].' '.leyka_get_currency_label('rur') : '';

            $donors[] = $donor_data;

        }

        return $donors;

    }

    /**
     * Delete a donor record.
     *
     * @param int $donor_id Donor ID
     */
    public static function delete_donor($donor_id) {
        wp_delete_user(absint($donor_id));
    }

    /**
     * @return null|string
     */
    public static function record_count() {

        $donors = new WP_User_Query(apply_filters('leyka_admin_donors_list_filter', array(
            'role__in' => array('donor',),
            'number' => -1,
            'count_total' => true,
            'fields' => array('ID',),
        ), 'get_donors_total_count'));

        return $donors->get_total();

    }

    /** Text displayed when no donors data is available. */
    public function no_items() {
        _e('No donors avaliable.', 'leyka');
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
            case 'donor_id':
            case 'donor_name':
            case 'first_donation':
            case 'campaigns':
            case 'last_donation':
            case 'donors_tags':
            case 'gateways':
            case 'amount_donated':
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
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s">', $item['donor_id']);
    }

    public function column_donor_type($item) {
        return isset($item['donor_type']) ? '<div class="'.$item['donor_type'].'">'.$item['donor_type'].'</div>' : '';
    }

    public function column_first_donation($item) {

        if(empty($item['first_donation'])) {
            return '';
        }

        $donation = new Leyka_Donation($item['first_donation']);

        return $donation && is_a($donation, 'Leyka_Donation') ? $donation->date : '';

    }

    public function column_last_donation($item) {

        if(empty($item['last_donation'])) {
            return '';
        }

        $donation = new Leyka_Donation($item['last_donation']);

        return '<div class="leyka-donation-info-wrapper">'
            .'<div class="first-sub-row">'.$donation->date.'</div>'
            .'<div class="second-sub-row">'.$donation->amount.'&nbsp;'.$donation->currency_label.',&nbsp;«'.$donation->campaign_title.'»</div>'
            .'</div>';

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_donor_name($item) {

        $item['donor_id'] = absint($item['donor_id']);

        $actions = array(
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&donor=%s&_wpnonce=%s">'.__('Delete', 'leyka').'</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                $item['donor_id'],
                wp_create_nonce('leyka_delete_donor')
            )
        );

        return '<div class="donor-name"><a href="'.admin_url('?page=leyka_donor_info&donor='.$item['donor_id']).'">'.$item['donor_name'].'</a></div>'
            .'<div class="donor-email">'.$item['donor_email'].'</div>'
            .$this->row_actions($actions);

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_campaigns($item) {

        if(empty($item['campaigns']) || !is_array($item['campaigns'])) {
            return '';
        }

        $campaigns_list = array();
        foreach($item['campaigns'] as $campaign_id => $campaign_title) {
            if($campaign_title) {
                $campaigns_list[] = '«'.$campaign_title.'»';
            }
        }

        sort($campaigns_list);

        return '<div class="leyka-admin-shortened-text">'.implode(', ', $campaigns_list).'</div>';

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_donors_tags($item) {

        if(empty($item['donors_tags']) || !is_array($item['donors_tags'])) {
            return '';
        }

        $tags_list = array();
        foreach($item['donors_tags'] as $term) {
            $tags_list[] = '#<a href="#">'.esc_html($term->name).'</a>';
        }

        return '<div class="leyka-donors-tags-list">'.implode(', ', $tags_list).'</div>';

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_gateways($item) {

        if(empty($item['gateways']) || !is_array($item['gateways'])) {
            return '';
        }

        $gateways_list = array();
        foreach($item['gateways'] as $gateway_id) {

            $gateway = leyka_get_gateway_by_id($gateway_id);
            if($gateway) {
                $gateways_list[] = esc_html($gateway->label);
            }

        }

        sort($gateways_list);

        return '<div class="leyka-gateways-list">'.implode(', ', $gateways_list).'</div>';

    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox">',
            'donor_id' => __('ID'),
            'donor_type' => _x('Type', "Donor's type", 'leyka'),
            'donor_name' => __("Donor's name", 'leyka'),
            'first_donation' => __('First donation', 'leyka'),
            'campaigns' => __('Campaigns list', 'leyka'),
            'donors_tags' => __("Donors' tags", 'leyka'),
            'gateways' => __('Gateway', 'leyka'),
            'last_donation' => __('Last donation', 'leyka'),
            'amount_donated' => __('Amount donated', 'leyka'),
        );
    }

    /**
     * @return array
     */
    public function get_sortable_columns() {
        return array(
            'donor_id' => array('donor_id', true),
            'donor_type' => array('donor_type', true),
            'donor_name' => array('donor_name', false),
            'first_donation' => array('first_donation', true),
            'last_donation' => array('last_donation', true),
            'amount_donated' => array('amount_donated', true),
        );
    }

    /**
     * @return array
     */
    public function get_bulk_actions() {
        return array('bulk-delete' => __('Delete'));
    }

    /**
     * Data query, filtering, sorting & pagination handler.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('donors_per_page');

        $this->set_pagination_args(array('total_items' => self::record_count(), 'per_page' => $per_page,));
        $this->items = self::get_donors($per_page, $this->get_pagenum());

    }

    public function process_bulk_action() {

        // Single donor deletion:
        if('delete' === $this->current_action()) {

            if( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donor') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {
                self::delete_donor(absint($_GET['donor']));
            }

        }

        // Bulk donors deletion:
        if(
            (isset($_POST['action']) && $_POST['action'] === 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] === 'bulk-delete')
        ) {

            foreach(esc_sql($_POST['bulk-delete']) as $donor_id) {
                self::delete_donor($donor_id);
            }

        }

    }

}