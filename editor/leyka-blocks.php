<?php if( !defined('WPINC') ) die;
/**
 * Gutenberg Blocks
 */
function leyka_register_blocks() {
	require_once LEYKA_PLUGIN_DIR . 'editor/blocks/leyka-block-form.php';
	require_once LEYKA_PLUGIN_DIR . 'editor/blocks/leyka-block-card.php';
}
add_action( 'init', 'leyka_register_blocks' );
