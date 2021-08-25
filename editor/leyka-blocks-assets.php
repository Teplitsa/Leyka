<?php if( !defined('WPINC') ) die;
/**
 * Leyka Blocks Assets
 */

/**
 * Enqueue scripts for editor
 */
function leyka_enqueue_block_editor_assets() {

	$dependencies = array(
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
	);

	wp_enqueue_script( 'leyka-blocks', LEYKA_PLUGIN_BASE_URL . 'assets/js/blocks.js', $dependencies, filemtime( LEYKA_PLUGIN_DIR . 'assets/js/blocks.js' ) );

	wp_register_style( 'leyka-revo-plugin-styles', LEYKA_PLUGIN_BASE_URL . 'assets/css/public.css', array(), LEYKA_VERSION );

	wp_enqueue_style( 'leyka-editor-styles', LEYKA_PLUGIN_BASE_URL . 'assets/css/editor-style.css', array( 'leyka-revo-plugin-styles' ), LEYKA_VERSION );

	$leykaBlock = array();

	// Get campaigns.
	$campaign_args = array(
		'post_type'      => 'leyka_campaign',
		'posts_per_page' => -1,
	);
	$campaigns = get_posts( $campaign_args );

	if ( $campaigns ) {
		$leykaBlock['campaigns'][] = array(
			'value'    => '',
			'label'    => esc_html__( 'Select campaing', 'leyka' ),
			'disabled' => true,
		);
		foreach( $campaigns as $campaign ) {
			$leykaBlock['campaigns'][] = array(
				'label' => $campaign->post_title,
				'value' => $campaign->ID,
			);
		}
	} else {
		$leykaBlock['campaigns'][] = array(
			'value'    => '',
			'label'    => esc_html__( 'No campaings', 'leyka' ),
			'disabled' => true,
			'selected' => true,
		);
	}

	$leykaBlock['blocks'] = array(
		// Internationalization.
		'i18n' => array(
			'settings'            => esc_html__( 'Settings', 'leyka' ),
			'campaign'            => esc_html__( 'Campaign', 'leyka' ),
			'color'               => esc_html__( 'Colors', 'leyka' ),
			'typography'          => esc_html__( 'Typography', 'leyka' ),
			'reset'               => esc_html__( 'Reset', 'leyka' ),
			'template'            => esc_html__( 'Template', 'leyka' ),
			'star'                => esc_html__( 'Star', 'leyka' ),
			'needHelp'            => esc_html__( 'Need help', 'leyka' ),
			'buttonText'          => esc_html__( 'Button Text', 'leyka' ),
			'donate'              => esc_html__( 'Donate', 'leyka' ),
			'showTitle'           => esc_html__( 'Show Title', 'leyka' ),
			'showImage'           => esc_html__( 'Show Image', 'leyka' ),
			'showButton'          => esc_html__( 'Show Button', 'leyka' ),
			'showProgressbar'     => esc_html__( 'Show Progressbar', 'leyka' ),
			'showTargetAmount'    => esc_html__( 'Show Target Amount', 'leyka' ),
			'showCollectedAmount' => esc_html__( 'Show Collected Amount', 'leyka' ),
		),
		// Variables for block leyka/form.
		'form' => array(
			'title'       => esc_html__( 'Collecting donations', 'leyka' ),
			'description' => esc_html__( 'Donation form', 'leyka' ),
			'colors'      => array(
				'star'      => leyka_block_color_vars( 'leyka/form', 'star' ),
				'need-help' => leyka_block_color_vars( 'leyka/form', 'need-help' ),
			),
			'font-size' => leyka_block_font_size_vars( 'leyka/form', 'default' ),
		),
		// Variables for block leyka/card.
		'card' => array(
			'title'       => esc_html__( 'Leyka: Campaign Card', 'leyka' ),
			'description' => esc_html__( 'Campaign informer with configurable elements', 'leyka' ),
			'colors'      => leyka_block_color_vars( 'leyka/card' ),
		),
	);

	// Variables for blocks.
	wp_localize_script( 'leyka-blocks', 'leykaBlock', $leykaBlock );

}
add_action( 'enqueue_block_editor_assets', 'leyka_enqueue_block_editor_assets' );
