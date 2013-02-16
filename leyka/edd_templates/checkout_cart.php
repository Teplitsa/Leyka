<?php
/**
 * @package Leyka
 * @subpackage Customized EDD templates.
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */
global $post;?>
<table id="edd_checkout_cart" <?php if(edd_is_ajax_enabled()) { echo 'class="ajaxed"'; } ?>>
	<thead>
		<tr class="edd_cart_header_row">
			<?php do_action('edd_checkout_table_header_first'); ?>
			<th class="edd_cart_item_name"><?php _e('Item Name', 'edd'); ?></th>
			<th class="edd_cart_item_price"><?php _e('Item Price', 'edd'); ?></th>
			<th class="edd_cart_actions"><?php _e('Actions', 'edd'); ?></th>
			<?php do_action('edd_checkout_table_header_last'); ?>
		</tr>
	</thead>
	<tbody>
		<?php $cart_items = edd_get_cart_contents();?>
		<?php if($cart_items) {?>
			<?php foreach($cart_items as $key => $item) {?>
				<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr($item['id']);?>">
					<?php do_action('edd_checkout_table_body_first', $item['id']);?>
					<td class="edd_cart_item_name">
                    <?php
                        if(current_theme_supports('post-thumbnails') && has_post_thumbnail($item['id'])) {
                            echo '<div class="edd_cart_item_image">'
                                 .get_the_post_thumbnail($item['id'], apply_filters('edd_checkout_image_size', array(25, 25)))
                                 .'</div>';
                        }

                        $item_title = get_the_title($item['id']);
                        if(leyka_is_any_sum_allowed($item['id'])) { // Donate has a free price
                            echo '<span class="edd_checkout_cart_item_title">'.esc_html($item_title).'</span>';
                        } else {
                            if(edd_has_variable_prices($item['id']) && !empty($item['options']) ) {
                                $item_title .= ' - '.edd_get_price_name($item['id'], $item['options']);
                            }
                            echo '<span class="edd_checkout_cart_item_title">'.esc_html($item_title).'</span>';
                        }
                    ?>
					</td>
					<td class="edd_cart_item_price">
                    <?php
                        if(leyka_is_any_sum_allowed($item['id'])) {
                            echo empty($item['options']['sum']) ?
                                edd_currency_filter(leyka_get_min_free_donation_sum($item['id'])) :
                                edd_currency_filter($item['options']['sum']);?>
                        <?php } else {
                            echo edd_cart_item_price($item['id'], $item['options']);
                        }?>
                    </td>
					<td class="edd_cart_actions"><a href="<?php echo esc_url(edd_remove_item_url($key, $post)); ?>"><?php _e('remove', 'edd');?></a></td>
					<?php do_action('edd_checkout_table_body_last', $item);?>
				</tr>
			<?php }?>
		<?php } else { ?>
			<tr class="edd_cart_item">
				<td colspan="3"  class="edd_cart_item_empty"><?php do_action('edd_empty_cart');?></td>
			</tr>
		<?php }?>
	</tbody>
	<tfoot>
		<?php if(edd_use_taxes()) : ?>
		<tr class="edd_cart_footer_row edd_cart_subtotal_row"<?php if(edd_local_taxes_only()) echo ' style="display:none;"'; ?>>
			<?php do_action('edd_checkout_table_subtotal_first'); ?>
			<th colspan="3" class="edd_cart_subtotal">
				<?php _e('Subtotal', 'edd'); ?>:&nbsp;<span class="edd_cart_subtotal"><?php echo edd_cart_subtotal(); ?></span>
			</th>
			<?php do_action('edd_checkout_table_subtotal_last'); ?>
		</tr>
		<tr class="edd_cart_footer_row edd_cart_tax_row"<?php if(edd_local_taxes_only()) echo ' style="display:none;"'; ?>>
			<?php do_action('edd_checkout_table_tax_first'); ?>
			<th colspan="3" class="edd_cart_tax">
				<?php _e('Tax', 'edd'); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo edd_get_cart_tax(); ?>"><?php echo esc_html(edd_cart_tax()); ?></span>
			</th>
			<?php do_action('edd_checkout_table_tax_last'); ?>
		</tr>
		<?php endif; ?>
		<tr class="edd_cart_footer_row">
			<?php do_action('edd_checkout_table_footer_first');?>
			<th colspan="3" class="edd_cart_total"><?php _e('Total', 'edd');?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_amount(false); ?>" data-total="<?php echo edd_get_cart_amount(true, true); ?>"><?php edd_cart_total(); ?></span></th>
			<?php do_action('edd_checkout_table_footer_last'); ?>
		</tr>
	</tfoot>
</table>