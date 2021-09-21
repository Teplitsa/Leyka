<?php if( !defined('WPINC') ) die;
/**
 * Leyka Blocks Functions
 *
 */

/**
 * Add scripts to page if post content has block leyka/form
 */
function leyka_block_modern_template_displayed( $modern_template_displayed ) {

	if ( ! is_singular() ) {
		return false;
	}

	if ( has_block( 'leyka/form', get_the_ID() ) ) {
		return true;
	}

	return $modern_template_displayed;
}
add_filter( 'leyka_modern_template_displayed', 'leyka_block_modern_template_displayed' );

/**
 * Convert slug to js camelcase vars
 */
function leyka_js_ucwords( $words, $lcfirst = true ) {
	$var = str_replace( '-', '', ucwords( $words, '-' ) );
	if ( ! $lcfirst ) {
		$var = lcfirst( $var );
	}
	return $var;
}

/**
 * Block Colors
 */
function leyka_block_colors( $block_name = null, $template = 'default' ) {

	$colors = array();

	if ( ! $block_name ) {
		return $colors;
	}

	if ( 'leyka/form' === $block_name ) {

		if ( 'star' === $template || 'default' === $template ) {
			$colors = array(
				'main'            => __( 'Active buttons & switches background color', 'leyka' ),
				'main-second'     => __( 'Controls borders color', 'leyka' ),
				'main-third'      => __( 'Selected payment method background color', 'leyka' ),
				'main-inactive'   => __( 'Inactive main submit background color', 'leyka' ),
				'text-light'      => __( 'Active buttons & switches text color', 'leyka' ),
				'error'           => __( 'Error messages text color', 'leyka' ),
				'gray-dark'       => __( 'The main text color (controls & content)', 'leyka' ),
				'gray-semi-dark'  => __( 'Single/recurring switch inactive variant text color', 'leyka' ),
				'gray'            => __( 'Form fields labels color', 'leyka' ),
				'gray-superlight' => __( 'Checkboxes & other fields borders color', 'leyka' ),
				'white'           => __( 'The main form background color', 'leyka' ),
				'gradient'        => __( 'Payment methods selector gradient color', 'leyka' ),
			);
		}

		if ( 'need-help' === $template ) {
			$colors = array(
				'main'                     => __( 'Active buttons & switches highlight color', 'leyka' ),
				'main-second'              => __( 'Secondary elements color', 'leyka' ),
				'main-inactive'            => __( 'The inactive elements color. Most of the times, the main color with lighter shade', 'leyka' ),
				'blocks-border'            => __( 'Form blocks border color', 'leyka' ),
				'blocks-background'        => __( 'Form blocks background color', 'leyka' ),
				'blocks-active-border'     => __( 'Form active blocks border color', 'leyka' ),
				'blocks-active-background' => __( 'Form active blocks background color', 'leyka' ),
				'error'                    => __( 'Form error messages color', 'leyka' ),
				'text'                     => __( 'Form text color', 'leyka' ),
				'text-superlight'          => __( 'Form text color, the most light shade', 'leyka' ),
				'text-dark-bg'             => __( 'Form text color, for elements with dark background', 'leyka' ),
			);
		}

	} else if ( 'leyka/card' === $block_name ) {

		if ( 'default' === $template ) {
			$colors = array(
				'title'       => __( 'Card title color', 'leyka' ),
				'background'  => __( 'Card background color', 'leyka' ),
				'button'      => __( 'Main CTA button background color', 'leyka' ),
				'fulfilled'   => __( 'Progressbar fulfilled part color', 'leyka' ),
				'unfulfilled' => __( 'Progressbar unfulfilled part color', 'leyka' ),
			);
		}
	}

	return $colors;
}

/**
 * Block Color Variables
 */
function leyka_block_color_vars( $block_name = 'leyka/form', $template = 'default' ) {

	$vars = array();

	foreach ( leyka_block_colors( $block_name, $template ) as $slug => $label ) {
		$slug = 'color' . leyka_js_ucwords( $slug );
		$vars[ $slug ] = $label;
	}

	return $vars;
}

/**
 * Block Font Sizes
 */
function leyka_block_font_sizes( $block_name = 'leyka/form', $template = 'default' ) {
	$font_sizes = array();
	if ( 'leyka/form' === $block_name ) {

		$font_sizes = array(
			'main' => array(
				'label' => esc_html__( 'Form text size', 'leyka' ),
				'default' => '16px',
			),
			'blocks-default' => array(
				'label' => esc_html__( 'Form blocks text size', 'leyka' ),
				'default' => '16px',
			),
			'amounts' => array(
				'label' => esc_html__( 'Donation amount blocks text size', 'leyka' ),
				'default' => '16px',
			),
			'pm-options' => array(
				'label' => esc_html__( 'Payment method blocks text size', 'leyka' ),
				'default' => '12px',
			),
			'donor-fields' => array(
				'label' => esc_html__( 'Donor data fields text size', 'leyka' ),
				'default' => '16px',
			),
			'submit' => array(
				'label' => esc_html__( 'Form submit text size', 'leyka' ),
				'default' => '16px',
			),
			'section-titles' => array(
				'label' => esc_html__( 'Form sections titles text size', 'leyka' ),
				'default' => '18px',
			),
		);

	}
	return $font_sizes;
}

/**
 * Block Font Sizes Variables
 */
function leyka_block_font_size_vars( $block_name = 'leyka/form', $template = 'default' ) {

	$vars = array();

	foreach ( leyka_block_font_sizes( $block_name, $template ) as $slug => $label ) {
		$slug = 'fontSize' . leyka_js_ucwords( $slug );
		$vars[ $slug ] = $label;
	}

	return $vars;
}

/**
 * Get All Campaigns.
 */
function leyka_block_get_campaigns() {
	// Get campaigns.
	$campaign_args = array(
		'post_type'      => 'leyka_campaign',
		'posts_per_page' => -1,
	);
	$campaigns = get_posts( $campaign_args );
	return $campaigns;
}

/**
 * Get The Last Campaigns.
 * 
 * @return last campaign id.
 */
function leyka_block_get_recent_campaign( $output = 'object' ) {
	// Get campaigns.
	$campaign_args = array(
		'post_type'      => 'leyka_campaign',
		'posts_per_page' => -1,
	);
	$campaigns   = leyka_block_get_campaigns();
	$recent_campaign = '';
	if ( $campaigns ) {
		if ( 'id' === $output ) {
			$recent_campaign = $campaigns[0]->ID;
		} else {
			$recent_campaign = $campaigns[0];
		}
		
	}
	return $recent_campaign;
}
