<?php if( !defined('WPINC') ) die;
/**
 * Gutenberg Block - Form
 */

function leyka_block_form_attributes(){

	$attributes = [
		'campaign' => ['type' => 'string', 'default' => leyka_block_get_recent_campaign_id(),],
		'template' => ['type' => 'string', 'default' => 'star',],
		'className' => ['type' => 'string', 'default' => '',],
		'preview' => ['type' => 'boolean', 'default' => false,],
		'anchor' => ['type' => 'string', 'default' => '',],
	];

	// Set color attributes for the Star template:
	foreach(leyka_block_color_vars('leyka/form', 'star') as $slug => $label) {
		$attributes[$slug] = ['type' => 'string', 'default' => '',];
	}

	// Set color attributes for the Need Help template:
	foreach (leyka_block_color_vars('leyka/form', 'need-help') as $slug => $label) {
		$attributes[$slug] = ['type' => 'string', 'default' => '',];
	}

	// Set font size attributes:
	foreach(leyka_block_font_size_vars('leyka/form', 'default') as $slug => $value) {
		$attributes[$slug] = ['type' => 'string', 'default' => $value['default'],];
	}

	return $attributes;

}

/**
 * Register Block Type Leyka Form
 */
register_block_type('leyka/form', [
	'render_callback' => 'leyka_block_form_render_callback',
	'attributes' => leyka_block_form_attributes(),
]);

/**
 * Render Block Leyka Form
 *
 * @param array $attr Block Attributes.
 */
function leyka_block_form_render_callback($attr, $content) {

	$template = $attr['template'];

	// Classes
	$classes = ['block_class' => 'wp-block-leyka-form', 'leyka_class' => 'leyka-block-form-'.$template,];

	if( !empty($attr['className']) ) {
		$classes['class_name'] = $attr['className'];
	}

	$block_style = '';
	$css_var_prefix = '--leyka';

	if($template === 'need-help') {
		$css_var_prefix .= '-need-help';
	}

	// Color CSS Variables:
	$color_keys = array_keys(leyka_block_color_vars('leyka/form', $template));
	$color_index = 0;

	foreach(leyka_block_colors('leyka/form', $template) as $slug => $label) {

		$const = $color_keys[$color_index];
		if( !empty($attr[$const]) ) {
			$block_style .= $css_var_prefix.'-color-'.$slug.':'.$attr[$const].';';
		}

		$color_index++;

	}

	// Font Size CSS Variables:
	$font_keys = array_keys(leyka_block_font_size_vars('leyka/form', $template));
	$font_index = 0;

	foreach(leyka_block_font_sizes('leyka/form', $template) as $slug => $label) {

		$const = $font_keys[$font_index];
		if ( !empty($attr[$const]) ) {
			$block_style .= $css_var_prefix.'-font-size-'.$slug.':'.$attr[ $const ].';';
		}

		$font_index++;

	}

	if($block_style) {
		$classes[] = 'has-leyka-custom-colors';
	}

	// Block attributes.
	$block_attr = 'class="' . implode(' ', $classes) . '"';

	// Id
	if (isset( $attr['anchor'] ) && $attr['anchor']) {
		$block_attr .= ' id="' . esc_attr( $attr['anchor'] ) . '"';
	}

	if ($block_style) {
		$block_attr .= ' style="' . $block_style . '"';
	}

	// Render a Campaign form with shortcode [leyka_campaign_form]:
	$html = '';
	if( !empty($attr['campaign']) ) {

		$campaign = $attr['campaign'];
		if( !is_numeric($campaign) ) {

			$campaign_page = get_page_by_path($campaign, OBJECT, 'leyka_campaign');

			if($campaign_page) {
				$campaign = $campaign_page->ID;
			}

		}

		$shortcode_attr_arr = ['id' => $campaign, 'template' => $template,];
		$shortcode_attr = '';

		$html = '<div ' . $block_attr . '>';

			foreach($shortcode_attr_arr as $key => $value) {
				$shortcode_attr .= ' '.$key.'="'.$value.'"';
			}

			$html .= do_shortcode('[leyka_campaign_form'.$shortcode_attr.']');

		$html .= '</div>';

	}

	return $html;

}