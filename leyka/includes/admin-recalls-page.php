<?php
/**
 * @package Leyka
 * @subpackage Admin recalls list page
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * Recall Columns.
 * Defines the custom columns and their order.
 */
function leyka_recall_posts_columns($recall_columns){
    $recall_columns = array(
        'cb' => '<input type="checkbox" />',
        'recall_title' => __('Title'),
        'text' => __('Recall text', 'leyka'),
        'donor' => __('Recall author (donor)', 'leyka'),
        'gateway' => __('Gateway', 'leyka'),
        'date' => __('Date', 'edd')
    );
    return $recall_columns;
}
add_filter('manage_leyka_recall_posts_columns', 'leyka_recall_posts_columns');

/** User recalls table columns. Defines the custom columns and their order. */
//function leyka_edit_recall_columns($recall_columns){
//    $recall_columns = array(
//        'cb' => '<input type="checkbox"/>',
//        'text' => __('Recall text', 'leyka'),
//        'date' => __('Date', 'leyka')
//    );
//    return $recall_columns;
//}
//add_filter('manage_edit-recall_columns', 'leyka_edit_recall_columns');

/** Render the recall custom columns content. */
function leyka_manage_posts_custom_column($column_name, $post_id){
    if(get_post_type($post_id) == 'leyka_recall') {
        $payment = get_post($post_id);
        $payment_id = reset(get_post_meta($post_id, '_leyka_payment_id'));
        $payment_meta = get_post_meta($payment_id, '_edd_payment_meta', true);
        $user_info = maybe_unserialize($payment_meta['user_info']);
        switch($column_name) {
            case 'recall_title':
                echo '<strong>'.$payment->post_title.'</strong>';?>
            <span id="recall-status-<?php echo $post_id;?>"><?php _post_states($payment);?></span>
            <?php break;
            case 'text':?>
            <div class="recall_text"><?php echo strip_tags($payment->post_content);?></div>
            <div id="actions-recall-<?php echo $post_id;?>">
                <a class="inline-edit-recall" data-recall-id="<?php echo $post_id;?>" href="#"><?php _e('Quick&nbsp;Edit');?></a> |
                <a class="submitdelete" title="<?php echo esc_attr(__('Move this item to the Trash'));?>" href="<?php echo get_delete_post_link($post_id);?>"><?php _e('Trash');?></a>
            </div>
            <div class="recall_edit_message"></div>

            <div id="edit-recall-<?php echo $post_id;?>" style="display:none;">
                <fieldset>
                    <legend><?php echo __('Edit user recall #', 'leyka').$post_id;?></legend>
                    <input type="hidden" name="leyka_nonce" value="<?php echo wp_create_nonce('leyka-edit-recall');?>" />
                    <input type="hidden" name="recall_id" value="<?php echo $post_id;?>" />
                    <input type="hidden" name="action" value="leyka-recall-edit" />
                    <label><?php _e('Status');?>:
                        <select name="recall_status">
                            <option value="publish" <?php echo ($payment->post_status == 'publish' ? 'selected' : '');?>><?php _e('Publish');?></option>
                            <option value="trash" <?php echo ($payment->post_status == 'trash' ? 'selected' : '');?>><?php _e('Trash');?></option>
                            <option value="draft" <?php echo ($payment->post_status == 'draft' ? 'selected' : '');?>><?php _e('Draft');?></option>
                            <option value="pending" <?php echo ($payment->post_status == 'pending' ? 'selected' : '');?>><?php _e('Pending');?></option>
                        </select>
                    </label>
                    <br />
                    <label><?php _e('Recall text', 'leyka');?>:
                        <textarea name="recall_text" rows="3" cols="20"><?php echo strip_tags($payment->post_content);?></textarea>
                    </label>
                    <br />
                    <br />
                    <input type="submit" class="submit-recall" data-recall-id="<?php echo $post_id;?>" value="OK" /> | <input class="reset-recall" data-recall-id="<?php echo $post_id;?>" type="reset" value="<?php _e('Cancel');?>">
                </fieldset>
            </div>
            <?php break;
            case 'donor':
                echo $user_info['first_name'].' '.$user_info['last_name'];
                break;
            case 'gateway':
                $gateway = edd_get_payment_gateway($payment_id);
                echo $gateway ? edd_get_gateway_admin_label($gateway) : '';
                break;
        }
    }
}
add_action('manage_posts_custom_column', 'leyka_manage_posts_custom_column', 10, 2);

/** Sortable Recall Columns - set the sortable columns content. */
function leyka_recall_sortable_columns($columns){
    $columns['date'] = 'date';
//    $columns['gateway'] = 'gateway';
//    $columns['donor'] = 'donor';
    $columns['recall_title'] = 'post_title';

    return $columns;
}
add_filter('manage_edit-leyka_recall_sortable_columns', 'leyka_recall_sortable_columns');