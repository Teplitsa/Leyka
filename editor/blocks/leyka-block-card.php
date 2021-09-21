<?php
/**
 * Leyka Block Card
 */

/**
 * Block Type Leyka Card Attributes
 */
function leyka_block_card_attributes(){
	$attributes = array(
		'campaign'         => array(
			'type'    => 'string',
			'default' => leyka_block_get_recent_campaign( 'id' ),
		),
		'template'         => array(
			'type'    => 'string',
			'default' => 'star',
		),
		'className'       => array(
			'type'    => 'string',
			'default' => '',
		),
		'preview'     => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'buttonText'     => array(
			'type'    => 'string',
			'default' => leyka_options()->opt_template( 'donation_submit_text' ),
		),
		'showTitle'     => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'showImage'     => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'showButton'    => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'showProgressbar'    => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'showTargetAmount'    => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'showCollectedAmount'    => array(
			'type'    => 'boolean',
			'default' => true,
		),
	);

	foreach ( leyka_block_color_vars( 'leyka/card' ) as $slug => $label ) {
		$attributes[ $slug ] = array(
			'type'    => 'string',
			'default' => '',
		);
	}

	return $attributes;
}

/**
 * Register Block Type Leyka Card
 */
register_block_type( 'leyka/card', array(
	'render_callback' => 'leyka_block_card_render_callback',
	'attributes'      => leyka_block_card_attributes(),
) );

/**
 * Render Block Leyka Card
 *
 * @param array $attr Block Attributes.
 */
function leyka_block_card_render_callback( $attr, $content ) {

	// Classes
	$classes = array(
		'block_class' => 'wp-block-leyka-card',
	);

	if ( isset( $attr['className'] ) && $attr['className'] ) {
		$classes['class_name'] = $attr['className'];
	}

	$style_attr = array();

	$const_keys = array_keys( leyka_block_color_vars( 'leyka/card' ) );
	$color_index = 0;
	foreach ( leyka_block_colors( 'leyka/card' ) as $slug => $label ) {

		$const = $const_keys[ $color_index ];
		if ( isset( $attr[ $const ] ) && $attr[ $const ] ) {
			$style_attr['color_' . $slug ] = $attr[ $const ];
		}
		$color_index++;
	}

	if ( $style_attr ) {
		$classes[] = 'has-leyka-custom-colors';
	}

	$shortcode_attr_arr = array(
		'show_title'            => isset( $attr['showTitle'] ) ? $attr['showTitle'] : true,
		'show_image'            => isset( $attr['showImage'] ) ? $attr['showImage'] : true,
		'show_progressbar'      => isset( $attr['showProgressbar'] ) ? $attr['showProgressbar'] : true,
		'show_button'           => isset( $attr['showButton'] ) ? $attr['showButton'] : true,
		'show_target_amount'    => isset( $attr['showTargetAmount'] ) ? $attr['showTargetAmount'] : true,
		'show_collected_amount' => isset( $attr['showCollectedAmount'] ) ? $attr['showCollectedAmount'] : true,
		'button_text'           => isset( $attr['buttonText'] ) ? $attr['buttonText'] : '',
		'classes'               => implode( ' ', $classes ),
	);
	$shortcode_attr     = '';

	/**
	 * Render Campaign Card Using shortcode [leyka_bar]
	 */
	$html = '';
	if ( isset( $attr['campaign'] ) && $attr['campaign'] ) {

		// Campaign id
		$campaign = isset( $attr['campaign'] ) ? $attr['campaign'] : '';

		if ( ! is_numeric( $campaign ) ) {

			$campaign_page = get_page_by_path( $campaign, OBJECT, 'leyka_campaign' );

			if ( $campaign_page ) {
				$campaign = $campaign_page->ID;
			}
		}

		if ( $campaign ) {
			$shortcode_attr_arr['campaign_id'] = $campaign;
		}

		if ( $style_attr ) {
			$shortcode_attr_arr = array_merge( $shortcode_attr_arr, $style_attr );
		}

		foreach( $shortcode_attr_arr as $key => $value ) {
			$shortcode_attr .= ' ' . $key . '="' . $value . '"';
		}

		$schortcode = '[leyka_bar' . $shortcode_attr . ']';
		$html .= do_shortcode( $schortcode );

	}

	return $html;
}
