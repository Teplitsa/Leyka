<?php
/**
 * @package Leyka
 * @subpackage Admin new/edit donate page modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Add new donate title placeholder:
function leyka_change_default_title($title){
    $screen = get_current_screen();
    if($screen->post_type == 'download')
        $title = __('Enter donate title here', 'leyka');
    return $title;
}
remove_filter('enter_title_here', 'edd_change_default_title');
add_filter('enter_title_here', 'leyka_change_default_title');

// Donate data blocks (metaboxes) list:
function leyka_donate_meta_boxes(){
    remove_meta_box('postimagediv', 'download', 'side'); // Post image metabox isn't needed
    remove_meta_box('postexcerpt', 'download', 'normal'); // Post excerpt metabox isn't needed

    add_meta_box('downloadinformation', __('Donate configuration', 'leyka'), 'edd_render_download_meta_box', 'download', 'normal', 'high');
    add_meta_box('edd_product_notes', __('Donate notes', 'leyka'), 'edd_render_product_notes_meta_box', 'download', 'normal', 'default');
    add_meta_box('leyka_donate_stats', __('Donate stats', 'leyka'), 'leyka_render_stats_meta_box', 'download', 'side', 'high');
    add_meta_box('leyka_donation_log', __('Donation log', 'leyka'), 'leyka_render_donation_log_meta_box', 'download', 'normal', 'default');
}
remove_action('add_meta_boxes', 'edd_add_download_meta_box');
add_action('add_meta_boxes', 'leyka_donate_meta_boxes');

// Donate notes metabox:
function leyka_render_product_notes_field($donate_id){
    global $edd_options;
    $donate_notes = edd_get_product_notes($donate_id);?>
<textarea rows="1" cols="40" class="large-texarea" name="edd_product_notes" id="edd_product_notes"><?php echo esc_textarea($donate_notes);?></textarea>
<p><?php _e('Special notes or instructions for this donate. These notes will be added to the thanking email sended to the donor.', 'leyka');?></p>
<?php
}
remove_action('edd_product_notes_meta_box_fields', 'edd_render_product_notes_field');
add_action('edd_product_notes_meta_box_fields', 'leyka_render_product_notes_field');

// Render donate stats metabox:
function leyka_render_stats_meta_box(){
    global $post;?>

<table class="form-table">
    <tr>
        <th style="width:60%;"><?php _e('Donations number', 'leyka');?>:</th>
        <td class="edd_download_stats"><?php echo edd_get_download_sales_stats($post->ID);?></td>
    </tr>
    <tr>
        <th style="width:60%;"><?php _e('Amount collected', 'leyka');?>:</th>
        <td class="edd_download_stats"><?php echo edd_currency_filter(edd_get_download_earnings_stats($post->ID));?></td>
    </tr>
    <?php do_action('edd_stats_meta_box');?>
</table>
<?php }

// Render donations log metabox:
function leyka_render_donation_log_meta_box(){
    global $post;

    $per_page = 10;

    if(isset($_GET['edd_sales_log_page'])) {
        $page = (int)$_GET['edd_sales_log_page'];
        $offset = $per_page*($page - 1);
        $donations_log = edd_get_download_sales_log($post->ID, true, $per_page, $offset);
    } else {
        $page = 1;
        $donations_log = edd_get_download_sales_log($post->ID, false);
    }?>

<table class="form-table">
    <tr>
        <th style="width:20%"><strong><?php _e('Donations log', 'leyka')?></strong></th>
        <td colspan="4" class="edd_download_stats">
            <?php _e('Each donation for this donate target is listed below.', 'leyka');?>
        </td>
    </tr>
    <?php if($donations_log['sales']) {
    foreach($donations_log['sales'] as $donation) {
        if($donation['user_info']['id'] != 0) {
            $user_data = get_userdata($donation['user_info']['id']);
            $name = $user_data->display_name;
        } else {
            $name = $donation['user_info']['first_name'].' '.$donation['user_info']['last_name'];
        }?>
        <tr>
            <td class="edd_download_sales_log">
                <strong><?php _e('Date');?>:</strong> <?php echo $donation['date'];?>
            </td>
            <td class="edd_download_sales_log">
                <strong><?php _e('Donor', 'leyka');?>:</strong> <?php echo $name;?>
            </td>
            <td colspan="3" class="edd_download_sales_log">
                <strong><?php _e('Donation ID', 'leyka');?>:</strong>
                <a href="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history&purchase_id='.$donation['payment_id'].'&edd-action=edit-payment');?>"><?php echo $donation['payment_id'];?></a>
            </td>
        </tr>
        <?php } // endforeach
    do_action('edd_purchase_log_meta_box');
} else {?>
    <tr><td colspan="2" class="edd_download_sales_log"><?php _e('No donations yet', 'leyka');?></td></tr>
    <?php }?>
</table>
<?php
    $total_log_entries = $donations_log['number'];
    $total_pages = ceil($total_log_entries / $per_page);

    if($total_pages > 1) {?>
    <div class="tablenav">
        <div class="tablenav-pages alignright">
            <?php $base = 'post.php?post='.$post->ID.'&action=edit%_%';
            echo paginate_links(array(
                'base' => $base,
                'format' => '&edd_sales_log_page=%#%',
                'prev_text' => '&laquo; '.__('Previous', 'edd'),
                'next_text' => __('Next', 'edd').' &raquo;',
                'total' => $total_pages,
                'current' => $page,
                'end_size' => 1,
                'mid_size' => 5,
                'add_fragment' => '#edd_purchase_log'
            ));?>
        </div>
    </div><!--end .tablenav-->
<?php }
}

// Donate configuration block content:
function leyka_meta_box_fields($post_id){
    global $edd_options;

    $price = edd_get_download_price($post_id);
    $variable_pricing = edd_has_variable_prices($post_id);
    $any_sum_allowed = leyka_is_any_sum_allowed($post_id);
    $prices = edd_get_variable_prices($post_id);

    $price_display = $variable_pricing || $any_sum_allowed ? ' style="display:none;"' : '';
    $variable_display = $variable_pricing ? '' : ' style="display:none;"';?>

<p><strong><?php _e('Pricing Options:', 'leyka');?></strong></p>

<p>
    <label for="edd_variable_pricing">
        <input type="checkbox" name="_variable_pricing" id="edd_variable_pricing" value="1" <?php checked( 1, $variable_pricing ); ?> />
        <?php _e('Enable variable pricing', 'leyka'); ?>
    </label>
</p>

<div id="edd_regular_price_field" class="edd_pricing_fields" <?php echo $price_display;?>>
<?php if( !isset($edd_options['currency_position']) || $edd_options['currency_position'] == 'before' ) {
    echo edd_currency_filter('');?>
    <input type="text" name="edd_price" id="edd_price" value="<?php echo $price ? esc_attr(edd_format_amount($price)) : '';?>" size="30" style="width:80px;" maxlength="30" placeholder="9.99"/>
<?php } else { ?>
    <input type="text" name="edd_price" id="edd_price" value="<?php echo $price ? esc_attr(edd_format_amount($price)) : '';?>" size="30" maxlength="30" style="width:80px;" placeholder="9.99"/>
    <?php echo edd_currency_filter('');
}

do_action('edd_price_field', $post_id);?>
</div>

<div id="edd_variable_price_fields" class="edd_pricing_fields" <?php echo $variable_display;?> >
    <input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>

    <div id="edd_price_fields" class="edd_meta_table_wrap">
        <table class="widefat" width="100%" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th><?php _e('Option Name', 'leyka'); ?></th>
                <th style="width: 90px"><?php _e('Price', 'leyka'); ?></th>
                <?php do_action('edd_download_price_table_head', $post_id);?>
                <th style="width: 2%"></th>
            </tr>
            </thead>
            <tbody>
                <?php
                if ( !empty($prices) ) :
                    foreach($prices as $key => $value) :
                        $name   = isset( $prices[ $key ]['name'] ) ? $prices[ $key ]['name'] : '';
                        $amount = isset( $prices[ $key ]['amount'] ) ? $prices[ $key ]['amount'] : '';

                        $args = apply_filters( 'edd_price_row_args', compact( 'name', 'amount' ) );
                        ?>
                    <tr class="edd_variable_prices_wrapper">
                        <?php do_action( 'edd_render_price_row', $key, $args, $post_id ); ?>
                    </tr>
                        <?php
                    endforeach;
                else :
                    ?>
                <tr class="edd_variable_prices_wrapper">
                    <?php do_action( 'edd_render_price_row', 0, array(), $post_id ); ?>
                </tr>
                    <?php endif; ?>

            <tr>
                <td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
                    <a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e('Add New Price', 'leyka'); ?></a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php /** Additional fields on the new/edit donate admin form: */
    $max_donation_sum = leyka_get_max_free_donation_sum($post_id);
    $min_donation_sum = leyka_get_min_free_donation_sum($post_id);?>
<p>
    <label>
        <input type="checkbox" name="leyka_any_sum_allowed" id="leyka_any_sum_allowed" value="1" <?php echo ($any_sum_allowed ? 'checked' : '');?> />
        &nbsp;<?php _e('Any price can be donated (free choice of the donor)', 'leyka');?>
        <div id="leyka_max_donation_sum_wrapper" style="display:<?php echo ($any_sum_allowed ? 'block' : 'none');?>">
            <label>
                <input type="text" name="leyka_min_donation_sum" value="<?php echo ($min_donation_sum ? $min_donation_sum : 10.0);?>" maxlength="30" />
                &nbsp;<?php echo sprintf(__('Minimum donation amount, %s', 'leyka'), edd_currency_filter(''));?>
            </label>
            <br />
            <label>
                <input type="text" name="leyka_max_donation_sum" value="<?php echo ($max_donation_sum ? $max_donation_sum : 30000.0);?>" maxlength="30" />
                &nbsp;<?php echo sprintf(__('Maximum donation amount, %s', 'leyka'), edd_currency_filter(''));?>
            </label>
        </div>
    </label>
</p>
<?php
}
remove_all_actions('edd_meta_box_fields', 1);
add_action('edd_meta_box_fields', 'leyka_meta_box_fields');

// Process additional fields of the new/edit donate admin form:
function leyka_metabox_fields_save($fields){
    return array_merge($fields, array(
        'leyka_any_sum_allowed',
        'leyka_min_donation_sum',
        'leyka_max_donation_sum'
    ));
}
add_filter('edd_metabox_fields_save', 'leyka_metabox_fields_save');

// Max donation amount field pre-saving check:
function leyka_save_metabox_max_sum($value){
    return (float)$value > 0.0 ? (float)$value : 30000.0;
}
add_filter('edd_metabox_save_leyka_max_donation_sum', 'leyka_save_metabox_max_sum');