<?php
/**
 * @package Leyka
 * @subpackage Custom admin tables classes
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Load WP_List_Table if not loaded
if( !class_exists('WP_List_Table') )
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';

class Leyka_Recalls_Table extends WP_List_Table {

	var $per_page = 30;
	var $total_count;
	var $complete_count;
	var $pending_count;

	function __construct(){
		global $status, $page;

		// Set parent defaults
		parent::__construct(array(
			'singular'  => _x('Recall', 'post type singular name', 'leyka'),
			'plural'    => _x('User recalls', 'post type general name', 'leyka'),
			'ajax'      => true // does this table support ajax?
		));

		$this->get_recall_counts();
	}

	/** Show the search field. */
	function search_box($text, $input_id) {
		if(empty($_REQUEST['s']) && !$this->has_items())
			return;

		$input_id = $input_id.'-search-input';
		if( !empty($_REQUEST['orderby']) )
			echo '<input type="hidden" name="orderby" value="'.esc_attr($_REQUEST['orderby']).'" />';
		if( !empty($_REQUEST['order']) )
			echo '<input type="hidden" name="order" value="'.esc_attr($_REQUEST['order']).'" />';?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id;?>"><?php echo $text;?>:</label>
			<input type="search" id="<?php echo $input_id;?>" name="s" value="<?php _admin_search_query();?>" />
			<?php submit_button($text, 'button', false, false, array('ID' => 'search-submit'));?>
		</p>
<?php
	}

	/** Retrieve the view types. */
	function get_views() {
        $base           = admin_url('edit.php?post_type=download&page=leyka-recalls');
        $current        = isset($_GET['status']) ? $_GET['status'] : '';
        $total_count    = '&nbsp;<span class="count">('.$this->total_count.')</span>';
        $complete_count = '&nbsp;<span class="count">('.$this->complete_count.')</span>';
        $pending_count  = '&nbsp;<span class="count">('.$this->pending_count.')</span>';

		$views = array(
			'all' => sprintf(
                '<a href="%s"%s>%s</a>',
                remove_query_arg('status', $base),
                $current === 'all' || $current == '' ? ' class="current"' : '',
                __('All', 'edd').$total_count
            ),
			'publish' => sprintf(
                '<a href="%s"%s>%s</a>',
                add_query_arg('status', 'publish', $base),
                $current === 'publish' ? ' class="current"' : '',
                __('Completed', 'edd').$complete_count
            ),
			'pending' => sprintf(
                '<a href="%s"%s>%s</a>',
                add_query_arg('status', 'pending', $base),
                $current === 'pending' ? ' class="current"' : '',
                __('Pending', 'edd').$pending_count
            ),
		);
		return $views;
	}

	/** Retrieve the table columns. */
	function get_columns() { 
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'text' => __('Recall text', 'leyka'),
            'donor' => __('Recall author (donor)', 'leyka'),
            'gateway' => __('Gateway', 'leyka'),
            'date' => __('Date', 'edd'),
            'status' => __('Status', 'edd')
        );
	}

	/** Retrieve the table's sortable columns. */
	function get_sortable_columns() {
		return array(
			'ID'    => array('ID', true),
			'date' 	=> array('date', false),
			'title' => array('post_title', false)
		);
	}

	/** Render most columns. */
	function column_default($item, $column_name) {
		switch($column_name) {	
			case 'title':
				return $item['title'];
			case 'date':
				return date_i18n(get_option('date_format'), strtotime($item[$column_name]));
            case 'donor':
                return $item['donor'] ?
                    $item['donor']['first_name']
                    .(empty($item['donor']['email']) ? '' : ' ('.$item['donor']['email'].')') : '';
            case 'status':
                switch($item['status']) {
                    case 'publish': return __('Publish');
                    case 'draft': return __('Draft');
                    case 'trash': return __('Trash');
                    case 'pending':
                    default:
                        return __('Pending');
                }
			default:
				return $item[$column_name];
		}
	}

    /** Render the checkbox column. */
    function column_cb($item) {
        return '<input type="checkbox" name="recalls[]" value="'.$item['ID'].'" />';
    }

    /** Render recall text column. */
    function column_text($item) {?>
    <div class="recall_text"><?php echo strip_tags($item['text']);?></div>

    <div class="recall_edit_message"></div>

    <div id="edit-recall-<?php echo $item['ID'];?>" class="inline-edit-recall" style="display:none;">
        <fieldset>
            <legend><?php echo __('Edit user recall #', 'leyka').$item['ID'];?></legend>
            <input type="hidden" name="leyka_nonce" value="<?php echo wp_create_nonce('leyka-edit-recall');?>" />
            <input type="hidden" name="recall_id" value="<?php echo $item['ID'];?>" />
<!--            <input type="hidden" name="action" value="leyka-recall-edit" />-->
            <label><?php _e('Status');?>:
                <select name="recall_status">
                    <option value="publish" <?php echo ($item['status'] == 'publish' ? 'selected' : '');?>><?php _e('Publish');?></option>
                    <option value="trash" <?php echo ($item['status'] == 'trash' ? 'selected' : '');?>><?php _e('Trash');?></option>
                    <option value="draft" <?php echo ($item['status'] == 'draft' ? 'selected' : '');?>><?php _e('Draft');?></option>
                    <option value="pending" <?php echo ($item['status'] == 'pending' ? 'selected' : '');?>><?php _e('Pending');?></option>
                </select>
            </label>
            <br />
            <label><?php _e('Recall text', 'leyka');?>:
                <textarea name="recall_text" rows="3" cols="20"><?php echo strip_tags($item['text']);?></textarea>
            </label>
            <br />
            <br />
            <input type="submit" class="submit-recall" data-recall-id="<?php echo $item['ID'];?>" value="OK" /> | <input class="reset-recall" data-recall-id="<?php echo $item['ID'];?>" type="reset" value="<?php _e('Cancel');?>">
        </fieldset>
    </div>
    <?php
        $row_actions = array(
            'edit' => '<a class="inline-edit-recall-link" data-recall-id="'.$item['ID'].'" href="#">'.__('Edit This').'</a>',
            'delete' => '<a class="submitdelete" title="'.esc_attr(__('Move this item to the Trash')).'" href="'.get_delete_post_link($item['ID']).'">'.__('Trash').'</a>',
        );

        echo $this->row_actions(apply_filters('leyka_recall_row_actions', $row_actions, $item));
    }

	/** Retrieve the bulk actions */
	function get_bulk_actions() { 
        return array(
            'delete' => __('Delete', 'edd'),
            'activate' => __('Activate', 'leyka'),
            'deactivate' => __('Deactivate', 'leyka'),
        );
    }

    /** Process the bulk actions */
    function process_bulk_action() {
        $ids = isset($_GET['recalls']) ? $_GET['recalls'] : FALSE;

        if( !is_array($ids) )
            $ids = array($ids);

        foreach($ids as $id) {
            switch($this->current_action()) { // Detect when a bulk action is being triggered...
                case 'delete':
                    wp_delete_post($id);
                    break;
                case 'activate':
                    wp_update_post(array('ID' => $id, 'post_status' => 'publish'));
                    break;
                case 'deactivate':
                    wp_update_post(array('ID' => $id, 'post_status' => 'pending'));
                    break;
                default:
            }
        }
    }

    /** Retrieve the recall counts */
	function get_recall_counts() {
        $this->complete_count = count(get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'leyka_recall',
            'number' => -1
        )));
        
        $this->pending_count = count(get_posts(array(
            'post_status' => 'pending',
            'post_type' => 'leyka_recall',
            'number' => -1
        )));
        
        $this->total_count = $this->complete_count + $this->pending_count;
	}

	/** Retrieve all payment data */
	function recalls_data() {
		$recalls = get_posts(array(
			'number' => $this->per_page,
			'page' => isset($_GET['paged']) ? $_GET['paged'] : 1, 
			'orderby' => isset($_GET['orderby']) ? $_GET['orderby'] : 'ID', 
			'order' => isset($_GET['order']) ? $_GET['order'] : 'DESC', 
			'author' => isset($_GET['user']) ? $_GET['user'] : NULL, 
			'post_status' => isset($_GET['status']) ? $_GET['status'] : 'any',
            'post_type' => 'leyka_recall',
			's' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : NULL
		));

        $recalls_data = array();
        $gateways = edd_get_enabled_payment_gateways();
        foreach($recalls as $recall) {
            $donation_id = get_post_meta($recall->ID, '_leyka_payment_id', TRUE);
            $donation_data = maybe_unserialize(get_post_meta($donation_id, '_edd_payment_meta', TRUE));
            if(empty($donation_data['user_info']))
                $donation_data = FALSE;
            else {
                
                $donation_data = unserialize(str_replace(array("'",), array("\'",), $donation_data['user_info']));
                $donation_data['first_name'] = stripslashes($donation_data['first_name']);
            }

            $gateway = get_post_meta($donation_id, '_edd_payment_gateway', TRUE);

            $recalls_data[] = array(
                'ID' => $recall->ID,
                'title' => $recall->post_title,
                'text' => $recall->post_content,
                'date' => $recall->date,
                'status' => $recall->post_status,
                'donor' => $donation_data,
                'gateway' => $gateways[$gateway]['admin_label'],
            );
        }

		return $recalls_data;
	}

	/** Setup the final data for the table. */
	function prepare_items() {
		$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

		$this->process_bulk_action();

		$data = $this->recalls_data();

		$status = isset($_GET['status']) ? $_GET['status'] : 'any';
		switch($status) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args(array(
            'total_items' => $total_items, // Calculate the total number of items
            'per_page'    => $this->per_page, // Determine how many items to show on a page
            'total_pages' => ceil($total_items/$this->per_page) // Calculate the total number of pages
        ));
	}  
}