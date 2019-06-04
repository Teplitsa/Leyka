<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donors_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Donor', 'leyka'), 'plural' => __('Donors', 'leyka'), 'ajax' => true,));

    }

    /**
     * Retrieve donor’s data from the DB.
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_donors($per_page = 10, $page_number = 1) {

//        global $wpdb;
//
//        $sql = "SELECT * FROM {$wpdb->prefix}customers";
//
//        if ( ! empty( $_REQUEST['orderby'] ) ) {
//            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
//            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
//        }
//
//        $sql .= " LIMIT $per_page";
//
//        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = array(1, 2, 3);

        return $result;

    }

    /**
     * Delete a customer record.
     *
     * @param int $donor_id customer ID
     */
    public static function delete_donor($donor_id) {

//        global $wpdb;
//
//        $wpdb->delete("{$wpdb->prefix}customers", array('ID' => $donor_id), array('%d'));
    }

    /**
     * @return null|string
     */
    public static function record_count() {

//        global $wpdb;

//        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}customers";
//        $result = $wpdb->get_var( $sql );

        return 12345;

    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        _e('No customers avaliable.', 'leyka');
    }

    /**
     * @param array $item An array of DB data.
     * @return string
     */
    function column_name($item) {

        $title = '<strong>'.$item['name'].'</strong>';

        $actions = array(
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                absint($item['ID']),
                wp_create_nonce('leyka_delete_donor')
            )
        );

        return $title.$this->row_actions($actions);

    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ($column_name) {
            case 'donor_type':
            case 'donor_name':
            case 'first_donation':
            case 'donor_campaigns_list':
            case 'last_donation':
            case 'donors_tags':
            case 'gateways':
            case 'amount_donated':
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     * @return string
     */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s">', $item['ID']);
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
            'donor_campaigns_list' => __('Campaigns list', 'leyka'),
            'last_donation' => __('Last donation', 'leyka'),
            'donors_tags' => __("Donors' tags", 'leyka'),
            'gateways' => __('Gateway', 'leyka'),
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

        $per_page = $this->get_items_per_page('customers_per_page', 5);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $this->items = self::get_donors($per_page, $current_page);

    }

    public function process_bulk_action() {

        // Single donor deletion:
        if('delete' === $this->current_action()) {

            if ( !wp_verify_nonce(esc_attr($_REQUEST['_wpnonce']), 'leyka_delete_donor') ) {
                die(__("You don't have permissions for this operation.", 'leyka'));
            } else {

                self::delete_donor(absint($_GET['donor']));

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
                self::delete_donor($id);
            }

            wp_redirect( esc_url(add_query_arg()) );
            exit;

        }

    }

}