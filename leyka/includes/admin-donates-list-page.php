<?php
/**
 * @package Leyka
 * @subpackage Admin donates list page modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** Main donate list page */
// Remove some columns from main donates list table
function leyka_donate_columns($donate_columns){
    $donate_columns = array(
        'cb' => '<input type="checkbox"/>',
        'title' => __('Donate name', 'leyka'),
        'price' => __('Donate size', 'leyka'),
        'sales' => __('Donations number', 'leyka'),
        'earnings' => __('Amount collected', 'leyka'),
        'date' => __('Created on', 'leyka')
    );
    return $donate_columns;
}
add_filter('manage_edit-download_columns', 'leyka_donate_columns');

// Render values in "donation sum" column
function leyka_render_donate_columns($column_name, $post_id){
    if(get_post_type($post_id) == 'download') {
        global $edd_options;

        switch($column_name) {
            case 'price':
                if(leyka_is_any_sum_allowed($post_id))
                    echo str_replace(
                        array('#MIN_SUM#', '#MAX_SUM#', '#CURRENCY#'),
                        array(
                            leyka_get_min_free_donation_sum($post_id),
                            leyka_get_max_free_donation_sum($post_id),
                            edd_currency_filter('')
                        ),
                        __('#MIN_SUM# #CURRENCY# - #MAX_SUM# #CURRENCY# (donation sum is defined by donors)', 'leyka')
                    );
                else if(edd_has_variable_prices($post_id))
                    echo __('A few variants of possible donation sum', 'leyka');
                else
                    echo edd_price($post_id, false).'<input type="hidden" class="downloadprice-'.$post_id.'" value="'.edd_get_download_price($post_id).'" />';
                break;
            case 'sales':
                echo edd_get_download_sales_stats($post_id);
                break;
            case 'earnings':
                echo edd_currency_filter(edd_format_amount(edd_get_download_earnings_stats($post_id)));
                break;
        }
    }
}
remove_action('manage_posts_custom_column', 'edd_render_download_columns', 10);
add_action('manage_posts_custom_column', 'leyka_render_donate_columns', 10, 2);

function leyka_price_field_quick_edit($column_name, $post_type){
    if($column_name != 'price' || $post_type != 'download')
        return;?>
<fieldset class="inline-edit-col-left">
    <div id="edd-download-data" class="inline-edit-col">
        <h4><?php echo __('Donate configuration', 'leyka');?></h4>
        <label>
            <span class="title"><?php _e('Price', 'leyka');?></span>
				<span class="input-text-wrap">
					<input type="text" name="_edd_regprice" class="text regprice" />
				</span>
        </label>
        <br class="clear" />
    </div>
</fieldset>
<?php
}
remove_action('quick_edit_custom_box', 'edd_price_field_quick_edit', 10);
add_action('quick_edit_custom_box', 'leyka_price_field_quick_edit', 10, 2);