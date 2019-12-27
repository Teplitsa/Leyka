<?php if( !defined('WPINC') ) die;
/** Donors list table class */

if( !class_exists('WP_List_Table') ) {
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class Leyka_Admin_Donors_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array('singular' => __('Donor', 'leyka'), 'plural' => __('Donors', 'leyka'), 'ajax' => true,));

        add_filter('leyka_admin_donors_list_filter', array($this, 'filter_donors'), 10, 2);
        add_action('pre_user_query', array($this, 'filter_donors_pre_user_query'));

        if( !empty($_REQUEST['donors-list-export']) ) {
            $this->_export_donors();
        }

    }

    /**
     * WP_User & user meta fields filtering.
     *
     * @param $donors_params array
     * @param $filter_type string
     * @return array|false An array of get_users() params, or false if the $filter_type is wrong
     */
    public function filter_donors(array $donors_params, $filter_type = '') {

        if($filter_type !== 'get_donors') {
            return false;
        }

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
            
            foreach($_REQUEST['gateways'] as $pm_full_id) {
                $gateways_meta_query[] = array(
                    'key' => 'leyka_donor_gateways',
                    'value' => esc_sql($pm_full_id),
                    'compare' => 'LIKE',
                );
            }

            $donors_params['meta_query'][] = $gateways_meta_query;

        }

        if( !empty($_REQUEST['campaigns']) && !empty($_REQUEST['campaigns'][0]) ) {

            $campaigns_meta_query = array('relation' => 'OR',);

            foreach($_REQUEST['campaigns'] as $campaign_id) {
                $campaigns_meta_query[] = array(
                    'key' => 'leyka_donor_campaigns',
                    'value' => 'i:'.absint($campaign_id).';', // A little freaky, we know, but it's the best we could think of
                    'compare' => 'LIKE',
                );
            }

            $donors_params['meta_query'][] = $campaigns_meta_query;

        }

        if( !empty($_REQUEST['first-donation-date']) ) {

            if(stripos($_REQUEST['first-donation-date'], ',') !== false) { // Dates period chosen

                $_REQUEST['first-donation-date'] = array_slice(explode(',', $_REQUEST['first-donation-date']), 0, 2);

                if(count($_REQUEST['first-donation-date']) === 2) { // The date is set as an interval

                    $_REQUEST['first-donation-date'][0] = strtotime($_REQUEST['first-donation-date'][0].' 00:00:00');
                    $_REQUEST['first-donation-date'][1] = strtotime($_REQUEST['first-donation-date'][1].' 23:59:59');

                    $donors_params['meta_query'][] = array(
                        'key' => 'leyka_donor_first_donation_date',
                        'value' => $_REQUEST['first-donation-date'],
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC',
                    );

                }

            } else { // Single date chosen
                $donors_params['meta_query'][] = array(
                    'key' => 'leyka_donor_first_donation_date',
                    'value' => array(
                        strtotime($_REQUEST['first-donation-date'].' 00:00:00'),
                        strtotime($_REQUEST['first-donation-date'].' 23:59:59'),
                    ),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC',
                );
            }

        }

        if( !empty($_REQUEST['last-donation-date']) ) {

            if(stripos($_REQUEST['last-donation-date'], ',') !== false) { // Dates period chosen

                $_REQUEST['last-donation-date'] = array_slice(explode(',', $_REQUEST['last-donation-date']), 0, 2);

                if(count($_REQUEST['last-donation-date']) === 2) { // The date is set as an interval

                    $_REQUEST['last-donation-date'][0] = strtotime($_REQUEST['last-donation-date'][0].' 00:00:00');
                    $_REQUEST['last-donation-date'][1] = strtotime($_REQUEST['last-donation-date'][1].' 23:59:59');

                    $donors_params['meta_query'][] = array(
                        'key' => 'leyka_donor_last_donation_date',
                        'value' => $_REQUEST['last-donation-date'],
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC',
                    );

                }

            } else { // Single date chosen
                $donors_params['meta_query'][] = array(
                    'key' => 'leyka_donor_last_donation_date',
                    'value' => array(
                        strtotime($_REQUEST['last-donation-date'].' 00:00:00'),
                        strtotime($_REQUEST['last-donation-date'].' 23:59:59'),
                    ),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC',
                );
            }

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
                $donors_params['order'] = isset($_REQUEST['order']) && $_REQUEST['order'] == 'asc' ? 'ASC' : 'DESC';
            }

        }

        return $donors_params;

    }

    /** Donors tags filter */
    public function filter_donors_pre_user_query(WP_User_Query $donor_users_query){

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
                Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME
            );

        }

    }

    /**
     * Retrieve donor’s data from the DB.
     *
     * @param int|false $per_page A number of lines per page, or false to not use pagination.
     * @param int $page_number A page number. Will not be in use if $per_page === false.
     *
     * @return mixed
     */
    public static function get_donors($per_page = false, $page_number = 1) {

        $donors_params = apply_filters(
            'leyka_admin_donors_list_filter', array(
                'role__in' => array(Leyka_Donor::DONOR_USER_ROLE,),
                'number' => $per_page ? absint($per_page) : -1,
                'paged' => $page_number ? absint($page_number) : false,
                'fields' => 'id',
            ),
            'get_donors'
        );

        $donors = array();
        foreach(get_users($donors_params) as $donor_user) {

            try {
                $donor = new Leyka_Donor($donor_user);
            } catch(Exception $e) {
            	continue;
            }

            $donor_data = array(
                'donor_id' => $donor->id,
                'donor_name' => $donor->name,
                'donor_email' => $donor->email,
                'first_donation' => $donor->first_donation_id,
                'last_donation' => $donor->last_donation_id,
                'donors_tags' => $donor->get_tags(),
            );

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
    public static function delete_donor($donor_user_id) {

        if(leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $donor_user_id)) {
            wp_delete_user(absint($donor_user_id));
        } else {

            $donor = get_user_by('id', $donor_user_id);
            $donor->remove_role(Leyka_Donor::DONOR_USER_ROLE);
            $donor->remove_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP);

        }

    }

    /**
     * @return null|string
     */
    public static function record_count() {

        $donors = new WP_User_Query(apply_filters('leyka_admin_donors_list_filter', array(
            'role__in' => array(Leyka_Donor::DONOR_USER_ROLE,),
            'number' => -1,
            'count_total' => true,
            'fields' => array('id',),
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
        return sprintf('<input type="checkbox" name="bulk[]" value="%s">', $item['donor_id']);
    }

    public function column_donor_type($item) {
        return isset($item['donor_type']) ? '<div class="'.$item['donor_type'].'"></div>' : '';
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
            .'<span class="donation-status '.$donation->status.' field-q"><span class="field-q-tooltip">'.esc_html__('Donation ' . $donation->status, 'leyka').'</span></span>'
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
        $admin_donor_page = admin_url('?page=leyka_donor_info&donor='.$item['donor_id']);

        $actions = array(
            'donor_page' => '<a href="'.$admin_donor_page.'">'.__('Edit').'</a>',
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&donor=%s&_wpnonce=%s">'.__('Delete', 'leyka').'</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                $item['donor_id'],
                wp_create_nonce('leyka_delete_donor')
            ),
        );

        return '<div class="donor-name"><a href="'.$admin_donor_page.'">'.$item['donor_name'].'</a></div>'
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
        foreach($item['donors_tags'] as $term) { /** @var $term WP_Term */
            $tags_list[] = '#<a href="?page='.esc_attr($_REQUEST['page']).'&donors-tags[0]='.$term->term_id.'">'.esc_html($term->name).'</a>';
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
     * @param array $item An array of DB data.
     * @return string
     */
    public function column_amount_donated($item) {
        return empty($item['amount_donated']) || $item['amount_donated'] == 0 ?
            '' : round($item['amount_donated'], 2).' '.leyka_get_currency_label('rur');
    }

    /**
     *  Associative array of columns.
     *
     * @return array
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox">',
            #'donor_id' => __('ID'),
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
        return array(
            'bulk-edit' => __('Edit'),
            'bulk-delete' => __('Delete'),
        );
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
        if($this->current_action() === 'delete') {

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

            foreach(esc_sql($_POST['bulk']) as $donor_id) {
                self::delete_donor($donor_id);
            }

        }

    }

    public function bulk_edit_fields() {?>

        <div id="leyka-donors-inline-edit-fields" style="display: none;" data-colspan="<?php echo count($this->get_columns());?>" data-bulk-edit-nonce="<?php echo wp_create_nonce('leyka-bulk-edit-donors');?>">

            <input type="text" name="donors-tags-input" class="leyka-donors-tags-selector leyka-selector" value="" placeholder="<?php _e('Donors tags', 'leyka');?>">

            <select class="leyka-donors-tags-select" name="donors-bulk-tags[]" multiple="multiple">
            </select>

            <div class="bulk-edit-submits">
                <button type="button" class="button cancel alignleft"><?php _e('Cancel');?></button>
                <button type="button" name="bulk_edit" id="bulk_edit" class="button button-primary alignright">
                    <?php _e('Update');?>
                </button>
            </div>

        </div>

    <?php }

    protected function _export_donors() {

        // Just in case that export will require some time:
        ini_set('max_execution_time', 99999);
        set_time_limit(99999);

        ob_start();

        $this->items = self::get_donors(false);

        add_filter('leyka_donors_export_line', 'leyka_prepare_data_line_for_export', 10, 2);

        ob_clean();

        header('Content-type: application/vnd.ms-excel');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: no-cache');

        header('Content-Disposition: attachment; filename="donors-'.date('d.m.Y-H.i.s').'.csv"');

        echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
            'UTF-8',
            apply_filters('leyka_donors_export_content_charset', 'windows-1251'),
            "sep=;\n".implode(';', apply_filters('leyka_donors_export_headers', array(
                'ID', 'Тип донора', 'Имя', 'Email', 'Дата первого пожертвования', 'Сумма первого пожертвования', 'Кампания первого пожертвования', 'Метки донора', 'Кампании', 'Платёжные операторы', 'Дата последнего пожертвования', 'Сумма последнего пожертвования', 'Кампания последнего пожертвования', 'Общая сумма пожертвований', 'Валюта',
            )))
        );

        foreach($this->items as $donor_data) {

            $first_donation = $donor_data['first_donation'] ? new Leyka_Donation($donor_data['first_donation']) : false;
            $last_donation = $donor_data['last_donation'] ? new Leyka_Donation($donor_data['last_donation']) : false;

            $donor_tags_list = array();
            if( !empty($donor_data['donors_tags']) ) {
                foreach($donor_data['donors_tags'] as $term) { /** @var $term WP_Term */
                    $donor_tags_list[] = esc_attr($term->name);
                }
            }
            $donor_tags_list = implode(', ', $donor_tags_list);

            $donor_campaigns_list = array();
            if( !empty($donor_data['campaigns']) ) {
                foreach($donor_data['campaigns'] as $campaign_id => $campaign_title) {
                    if($campaign_title) {
                        $donor_campaigns_list[] = $campaign_title;
                    }
                }
            }
            sort($donor_campaigns_list);
            $donor_campaigns_list = implode(', ', $donor_campaigns_list);

            $donor_gateways_list = array();
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

            echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
                'UTF-8',
                apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
                "\r\n".implode(';', apply_filters('leyka_donations_export_line', array(
                        $donor_data['donor_id'],
                        _x(mb_ucfirst($donor_data['donor_type']), "Donor's type", 'leyka'),
                        $donor_data['donor_name'],
                        $donor_data['donor_email'],
                        ($first_donation ? $first_donation->date : ''),
                        ($first_donation ? $first_donation->amount : ''),
                        ($first_donation ? $first_donation->campaign_title : ''),
                        $donor_tags_list,
                        $donor_campaigns_list,
                        $donor_gateways_list,
                        ($last_donation ? $last_donation->date : ''),
                        ($last_donation ? $last_donation->amount : ''),
                        ($last_donation ? $last_donation->campaign_title : ''),
                        $donor_data['amount_donated'],
                        leyka_get_currency_label(),
                    ), $donor_data)
                )
            );

        }

        die();

    }

}