<?php if( !defined('WPINC') ) die;
/**
 * Leyka Gutenberg Blocks
 */

function leyka_register_gutenberg_blocks() {

	require_once LEYKA_PLUGIN_DIR . 'editor/blocks/leyka-block-form.php';
	require_once LEYKA_PLUGIN_DIR . 'editor/blocks/leyka-block-card.php';
	require_once LEYKA_PLUGIN_DIR . 'editor/blocks/leyka-block-cards.php';

}
add_action('init', 'leyka_register_gutenberg_blocks');