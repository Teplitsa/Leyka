<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donors_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Donor', 'leyka'), 'plural' => __('Donors', 'leyka'), 'ajax' => true,));

        add_filter('leyka_admin_donors_list_filter', array($this, 'filter_donors'));
        add_filter('leyka_admin_donors_list_donations_filter', array($this, 'filter_donors_donations'));

    }

    public function filter_donors(array $donors_params) {

        if(isset($_REQUEST['donor-name-email'])) {
            // ...
        }

        return $donors_params;

    }

    public function filter_donors_donations(array $donations_params) {

        if(isset($_REQUEST['donor-type'])) {

            $_REQUEST['donor-type'] = esc_attr($_REQUEST['donor-type']);



        }

        return $donations_params;

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
            'role__in' => array('donor_single', 'donor_regular',),
            'number' => absint($per_page),
            'paged' => absint($page_number),
            'fields' => array('ID', 'user_email', 'display_name',),
        ));
        $donor_donations_params = apply_filters('leyka_admin_donors_list_donations_filter', array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => array('submitted', 'funded', 'refunded', 'failed',),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'desc',
        ));

        $donors = array();
        foreach(get_users($donors_params) as $donor_user) {

            $donor_data = array(
                'id' => $donor_user->ID,
                'donor_type' => 'single',
                'donor_name' => $donor_user->display_name,
                'donor_email' => $donor_user->user_email,
                'first_donation' => '',
                'last_donation' => false,
                'campaigns' => array(),
                'donors_tags' => array(),
                'gateways' => array(),
                'amount_donated' => 0.0,
            );

            $donor_donations_params['meta_query'] = array(
                'relation' => 'OR',
//                array('key' => 'leyka_donor_email', 'value' => $donor_data['donor_email'], 'compare' => '-'),
                array('key' => 'leyka_donor_account', 'value' => $donor_user->ID, 'compare' => '-'),
            );

            $donor_donations = get_posts($donor_donations_params); // Get donations by donor, ordered by date desc

            $donations_count = count($donor_donations);
            for($i = 0; $i < count($donor_donations); $i++) {

                $donation = new Leyka_Donation($donor_donations[$i]);

                if($donation->type === 'rebill' && $donation->status === 'funded') {
                    $donor_data['donor_type'] = 'regular';
                }

                if($i === 0) {
                    $donor_data['first_donation'] = $donation;
                } else if ($i === $donations_count - 1) {
                    $donor_data['last_donation'] = $donation;
                }

                if(empty($donor_data['campaigns']) || empty($donor_data['campaigns'][$donation->campaign_id])) {
                    $donor_data['campaigns'][$donation->campaign_id] = $donation->campaign_title;
                }

                $donor_data['donors_tags'] = wp_get_object_terms($donor_user->ID, LEYKA_DONORS_TAGS_TAXONOMY_NAME);

                if(empty($donor_data['gateways']) || !in_array($donation->gateway, $donor_data['gateways'])) {
                    $donor_data['gateways'][$donation->gateway] = $donation->gateway_label;
                }

                if($donation->status === 'funded') {
                    $donor_data['amount_donated'] = empty($donor_data['amount_donated']) ?
                        $donation->amount : $donor_data['amount_donated'] + $donation->amount;
                }

            }

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

        $donors = new WP_User_Query(array(
            'role__in' => array('donor_single', 'donor_regular',),
            'count_total' => true,
            /** @todo Apply donor table filters here! */
        ));

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
            case 'donor_type':
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
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s">', $item['id']);
    }

    public function column_first_donation($item) {
        return empty($item['first_donation']) ? '' : $item['first_donation']->date;
    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_donor_name($item) {

        $actions = array(
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&donor=%s&_wpnonce=%s">'.__('Delete', 'leyka').'</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                absint($item['id']),
                wp_create_nonce('leyka_delete_donor')
            )
        );

        return '<div class="donor-name">'.$item['donor_name'].'</div>'
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
            $campaigns_list[] = '«'.$campaign_title.'»';
        }

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
        foreach($item['gateways'] as $gateway_id => $gateway) {
            $gateways_list[] = esc_html($gateway);
        }

        return '<div class="leyka-gateways-list">'.implode(', ', $gateways_list).'</div>';

    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_last_donation($item) {

        if(empty($item['last_donation'])) {
            return '';
        }

        $donation = $item['last_donation'];

        return '<div class="leyka-last-donation-wrapper">'
            .'<div class="first-sub-row">'.$donation->status.' '.$donation->date.'</div>'
            .'<div class="second-sub-row">'.$donation->amount.' '.$donation->currency_label.', «'.$donation->campaign_title.'»</div>'
        .'</div>';

    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox">',
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
            'donor_type' => array('donor_type', true),
            'donor_name' => array('donor_name', false),
            'first_donation' => array('first_donation', true),
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

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_lines = self::record_count();

        $this->set_pagination_args(array('total_items' => $total_lines, 'per_page' => $per_page,));
        $this->items = self::get_donors($per_page, $current_page);

    }

    public function process_bulk_action() {

        // Single donor deletion:
        if('delete' === $this->current_action()) {

            if ( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donor') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {

                $this->delete_donor(absint($_GET['donor']));

                wp_redirect( esc_url(add_query_arg()) );
                exit;

            }

        }

        // Bulk donors deletion:
        if(
            (isset($_POST['action']) && $_POST['action'] === 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] === 'bulk-delete')
        ) {

            foreach(esc_sql($_POST['bulk-delete']) as $id) {
                $this->delete_donor($id);
            }

            wp_redirect( esc_url(add_query_arg()) );
            exit;

        }

    }

}