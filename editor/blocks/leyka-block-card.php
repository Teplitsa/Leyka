<?php if(!defined('WPINC') ) die;
/**
 * Gutenberg Block - Card
 */

function leyka_block_card_attributes(){

	$attributes = [
		'campaign' => ['type' => 'string', 'default' => leyka_block_get_recent_campaign_id(),],
		'template' => ['type' => 'string', 'default' => 'star',],
		'className' => ['type' => 'string', 'default' => '',],
		'anchor' => ['type' => 'string', 'default' => '',],
		'preview' => ['type' => 'boolean', 'default' => false,],
		'buttonText' => ['type' => 'string', 'default' => leyka_options()->opt_template('donation_submit_text'),],
		'showTitle' => ['type' => 'boolean', 'default' => true,],
		'showExcerpt' => ['type' => 'boolean', 'default' => false,],
		'showImage' => ['type' => 'boolean', 'default' => true,],
		'showButton' => ['type' => 'boolean', 'default' => true,],
		'showProgressbar' => ['type' => 'boolean', 'default' => true,],
		'showTargetAmount' => ['type' => 'boolean', 'default' => true,],
		'showCollectedAmount' => ['type' => 'boolean', 'default' => true,],
		'titleFontSize' => ['type' => 'string', 'default' => '',],
		'excerptFontSize' => ['type' => 'string', 'default' => '',],
	];

	foreach(leyka_block_color_vars('leyka/card') as $slug => $label) {
		$attributes[$slug] = ['type' => 'string', 'default' => '',];
	}

	return $attributes;

}

/**
 * Register Block Type Leyka Card
 */
register_block_type('leyka/card', [
	'render_callback' => 'leyka_block_card_render_callback',
	'attributes' => leyka_block_card_attributes(),
]);

/**
 * Render Block Leyka Card
 *
 * @param array $attr Block Attributes.
 */
function leyka_block_card_render_callback( $attr, $content ) {

	$classes = ['block_class' => 'wp-block-leyka-card leyka-block-card',];

	if( !empty($attr['className']) ) {
		$classes['class_name'] = $attr['className'];
	}

	$style_attr = [];

	$const_keys = array_keys(leyka_block_color_vars('leyka/card'));
	$color_index = 0;
	foreach(leyka_block_colors('leyka/card') as $slug => $label) {
		$const = $const_keys[$color_index];
		if( !empty($attr[$const]) ) {
			$style_attr['color_'.$slug] = $attr[$const];
		}
		$color_index++;
	}

	$block_style = '';

	// Title font size
	if ( isset( $attr['titleFontSize'] ) && $attr['titleFontSize'] ) {
		$block_style .= '--leyka-card-title-size:' . esc_attr( $attr['titleFontSize'] ) . ';';
	}

	// Description font size
	if ( isset( $attr['excerptFontSize'] ) && $attr['excerptFontSize'] ) {
		$block_style .= '--leyka-card-excerpt-size:' . esc_attr( $attr['excerptFontSize'] ) . ';';
	}

	if($style_attr) {
		$classes[] = 'has-leyka-custom-colors';
	}

	$shortcode_attr_arr = [
		'show_title' => isset($attr['showTitle']) ? $attr['showTitle'] : true,
		'show_excerpt' => isset($attr['showExcerpt']) ? $attr['showExcerpt'] : false,
		'show_image' => isset($attr['showImage']) ? $attr['showImage'] : true,
		'show_progressbar' => isset($attr['showProgressbar']) ? $attr['showProgressbar'] : true,
		'show_button' => isset($attr['showButton']) ? $attr['showButton'] : true,
		'show_target_amount' => isset($attr['showTargetAmount']) ? $attr['showTargetAmount'] : true,
		'show_collected_amount' => isset($attr['showCollectedAmount']) ? $attr['showCollectedAmount'] : true,
		'button_text' => isset($attr['buttonText']) ? $attr['buttonText'] : '',
		'classes' => implode(' ', $classes),
		'attr_id' => isset($attr['anchor']) ? $attr['anchor'] : '',
		'style' => $block_style,
	];

	/**
	 * Render Campaign Card
	 */
	$html = '';
	if( !empty($attr['campaign']) ) {

		$campaign = isset($attr['campaign']) ? $attr['campaign'] : ''; // Campaign ID

		if( !is_numeric($campaign) ) {

			$campaign_page = get_page_by_path($campaign, OBJECT, 'leyka_campaign');

			if($campaign_page) {
				$campaign = $campaign_page->ID;
			}

		}

		if($campaign) {
			$shortcode_attr_arr['campaign_id'] = $campaign;
		}

		if($style_attr) {
			$shortcode_attr_arr = array_merge($shortcode_attr_arr, $style_attr);
		}

		$html = leyka_shortcode_campaign_card( $shortcode_attr_arr );

	}

	return $html;

}