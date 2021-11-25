<?php if( !defined('WPINC') ) die;
/**
 * Leyka Gutenberg Blocks Category
 *
 * @param array $block_categories Block categories.
 */
function leyka_block_categories_all($block_categories, $editor_context) {

	array_push($block_categories, ['slug'  => 'leyka', 'title' => esc_html__( 'Leyka', 'leyka' ),]);

	return $block_categories;

}
add_filter('block_categories_all', 'leyka_block_categories_all', 10, 2);