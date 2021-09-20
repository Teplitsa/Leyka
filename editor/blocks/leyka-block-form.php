<?php
/**
 * Leyka Block Form
 *
 * @package Kandinsky
 */

/**
 * Block Type Leyka Form Attributes
 */
function leyka_block_form_attributes(){

	// Get campaigns.
	$campaign_args = array(
		'post_type'      => 'leyka_campaign',
		'posts_per_page' => -1,
	);
	$campaigns = get_posts( $campaign_args );

	$campaign_id = '';
	if ( $campaigns ) {
		$campaign_id = $campaigns[0]->ID;
	}

	$attributes = array(
		'campaign'         => array(
			'type'    => 'string',
			'default' => $campaign_id,
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
	);

	// Set color attributes for template star
	foreach ( leyka_block_color_vars( 'leyka/form', 'star' ) as $slug => $label ) {
		$attributes[ $slug ] = array(
			'type'    => 'string',
			'default' => '',
		);
	}

	// Set color attributes for template need-help
	foreach ( leyka_block_color_vars( 'leyka/form', 'need-help' ) as $slug => $label ) {
		$attributes[ $slug ] = array(
			'type'    => 'string',
			'default' => '',
		);
	}

	// Set font size attributes
	foreach ( leyka_block_font_size_vars( 'leyka/form', 'default' ) as $slug => $value ) {
		$attributes[ $slug ] = array(
			'type'    => 'string',
			'default' => $value['default'],
		);
	}

	return $attributes;
}

/**
 * Register Block Type Leyka Form
 */
register_block_type( 'leyka/form', array(
	'render_callback' => 'leyka_block_form_render_callback',
	'attributes'      => leyka_block_form_attributes(),
) );

/**
 * Render Block Leyka Form
 *
 * @param array $attr Block Attributes.
 */
function leyka_block_form_render_callback( $attr, $content ) {

	//Docs https://leyka.te-st.ru/docs/shortcodes-v-3-6/

	/* Get Template */
	$template = $attr['template'];

	// Classes
	$classes = array(
		'block_class' => 'wp-block-leyka-form',
		'leyka_class' => 'leyka-block-form-' . $template,
	);

	if ( isset( $attr['className'] ) && $attr['className'] ) {
		$classes['class_name'] = $attr['className'];
	}

	$style = '';

	$css_var_prefix = '--leyka';

	if ( 'need-help' === $template ) {
		$css_var_prefix = $css_var_prefix . '-need-help';
	}

	// Color CSS Variables
	$color_keys = array_keys( leyka_block_color_vars( 'leyka/form', $template ) );
	$color_index = 0;
	foreach ( leyka_block_colors( 'leyka/form', $template ) as $slug => $label ) {

		$const = $color_keys[ $color_index ];
		if ( isset( $attr[ $const ] ) && $attr[ $const ] ) {
			$style .= $css_var_prefix . '-color-' . $slug . ':' . $attr[ $const ] . ';';
		}
		$color_index++;
	}

	// Font Size CSS Variables
	$font_keys = array_keys( leyka_block_font_size_vars( 'leyka/form', $template ) );
	$font_index = 0;
	foreach ( leyka_block_font_sizes( 'leyka/form', $template ) as $slug => $label ) {

		$const = $font_keys[ $font_index ];
		if ( isset( $attr[ $const ] ) && $attr[ $const ] ) {
			$style .= $css_var_prefix . '-font-size-' . $slug . ':' . $attr[ $const ] . ';';
		}
		$font_index++;
	}

	if ( $style ) {
		$classes[] = 'has-leyka-custom-colors';
		$style = ' style="' . $style . '"';
	}

	/**
	 * Render Campaign Using shortcode [leyka_campaign_form]
	 */
	$html = '';
	if ( isset( $attr['campaign'] ) && $attr['campaign'] ) {

		$campaign = $attr['campaign'];
		if ( ! is_numeric( $campaign ) ) {

			$campaign_page = get_page_by_path( $campaign, OBJECT, 'leyka_campaign' );

			if ( $campaign_page ) {
				$campaign = $campaign_page->ID;
			}
		}

		$shortcode_attr_arr = array(
			'id'       => $campaign,
			'template' => $template,
		);
		$shortcode_attr     = '';

		$html .= '<div class="' . implode( ' ', $classes ) . '"' . $style . '>';

			foreach( $shortcode_attr_arr as $key => $value ) {
				$shortcode_attr .= ' ' . $key . '="' . $value . '"';
			}

			$schortcode = '[leyka_campaign_form' . $shortcode_attr . ']';

			$html .= do_shortcode( $schortcode );

		$html .= '</div>';
	}

	return $html;
}
