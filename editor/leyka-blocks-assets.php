<?php if(!defined('WPINC')) die;
/**
 * Leyka Gutenberg Blocks Assets
 */

/**
 * Enqueue scripts for editor
 */
function leyka_enqueue_block_editor_assets() {

	$dependencies = [
		'wp-blocks',
		'wp-plugins',
		'wp-element',
		'wp-components',
		'wp-editor',
		'wp-block-editor',
		'wp-edit-post',
		'wp-data',
		'wp-core-data',
		'wp-compose',
		'wp-hooks',
		'wp-server-side-render',
		'wp-i18n'
	];

	wp_enqueue_script(
	    'leyka-blocks',
        LEYKA_PLUGIN_BASE_URL.'assets/js/blocks.js',
        $dependencies,
        filemtime(LEYKA_PLUGIN_DIR.'assets/js/blocks.js')
    );

	wp_register_style('leyka-new-templates-styles', LEYKA_PLUGIN_BASE_URL.'assets/css/public.css', [], LEYKA_VERSION);
	wp_enqueue_style(
	    'leyka-editor-styles',
        LEYKA_PLUGIN_BASE_URL.'assets/css/editor-style.css',
        ['leyka-new-templates-styles'],
        LEYKA_VERSION
    );

	$leyka_block = [];
	$campaigns = get_posts(['post_type' => 'leyka_campaign', 'posts_per_page' => -1,]);

	if($campaigns) {

		$leyka_block['campaigns'][] = [
			'value' => '',
			'label' => __('Select campaing', 'leyka'),
			'disabled' => true,
		];

		foreach($campaigns as $campaign) {
			$leyka_block['campaigns'][] = ['label' => $campaign->post_title, 'value' => $campaign->ID,];
		}

	} else {
		$leyka_block['campaigns'][] = [
			'value' => '',
			'label' => __('No campaings', 'leyka'),
			'disabled' => true,
			'selected' => true,
		];
	}

	$leyka_block['blocks'] = [
		'i18n' => [
			'settings'            => __('Settings', 'leyka'),
			'campaign'            => __('Campaign', 'leyka'),
			'color'               => __('Colors', 'leyka'),
			'typography'          => __('Typography', 'leyka'),
			'reset'               => __('Reset', 'leyka'),
			'template'            => __('Template', 'leyka'),
			'star'                => __('Star', 'leyka'),
			'needHelp'            => __('Need help', 'leyka'),
			'buttonText'          => __('Button Text', 'leyka'),
			'donate'              => __('Donate', 'leyka'),
			'showTitle'           => __('Show Title', 'leyka'),
			'showImage'           => __('Show Image', 'leyka'),
			'showButton'          => __('Show Button', 'leyka'),
			'showProgressbar'     => __('Show Progressbar', 'leyka'),
			'showTargetAmount'    => __('Show Target Amount', 'leyka'),
			'showCollectedAmount' => __('Show Collected Amount', 'leyka'),
		],
		// Variables for block leyka/form.
		'form' => [
			'title' => __('Collecting donations', 'leyka'),
			'description' => __('Donation form', 'leyka'),
			'colors' => [
				'star' => leyka_block_color_vars('leyka/form', 'star'),
				'need-help' => leyka_block_color_vars('leyka/form', 'need-help'),
			],
			'font-size' => leyka_block_font_size_vars('leyka/form', 'default'),
		],
		// Variables for block leyka/card.
		'card' => [
			'title' => __('Leyka: Campaign Card', 'leyka'),
			'description' => __('Campaign informer with configurable elements', 'leyka'),
			'colors' => leyka_block_color_vars('leyka/card'),
		],
	];

	wp_localize_script('leyka-blocks', 'leykaBlock', $leyka_block); // Variables for blocks

}
add_action('enqueue_block_editor_assets', 'leyka_enqueue_block_editor_assets');