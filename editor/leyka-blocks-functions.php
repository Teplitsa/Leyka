<?php if(!defined('WPINC')) die;
/**
 * Leyka Gutenberg Blocks Functions
 */

// Add scripts to a page if it's post_content has leyka/form block:
function leyka_block_modern_template_displayed($modern_template_displayed) {
    return is_singular() && has_block('leyka/form', get_the_ID()) ? true : $modern_template_displayed;
}
add_filter('leyka_modern_template_displayed', 'leyka_block_modern_template_displayed');

// Convert slug to js camelcase vars:
function leyka_js_ucwords($words, $lcfirst = true) {

	$var = str_replace('-', '', ucwords($words, '-'));

	return $lcfirst ? $var : mb_lcfirst($var);

}

// Block Colors:
function leyka_block_colors($block_name = null, $template = 'default') {

	$colors = [];

	if( !$block_name ) {
		return $colors;
	}

	if($block_name === 'leyka/form') {

		if($template === 'star' || $template === 'default') {
			$colors = [
				'main'            => __('Active buttons & switches background color', 'leyka'),
				'main-second'     => __('Controls borders color', 'leyka'),
				'main-third'      => __('Selected payment method background color', 'leyka'),
				'main-inactive'   => __('Inactive main submit background color', 'leyka'),
				'text-light'      => __('Active buttons & switches text color', 'leyka'),
				'error'           => __('Error messages text color', 'leyka'),
				'gray-dark'       => __('The main text color (controls & content)', 'leyka'),
				'gray-semi-dark'  => __('Single/recurring switch inactive variant text color', 'leyka'),
				'gray'            => __('Form fields labels color', 'leyka'),
				'gray-superlight' => __('Checkboxes & other fields borders color', 'leyka'),
				'white'           => __('The main form background color', 'leyka'),
				'gradient'        => __('Payment methods selector gradient color', 'leyka'),
			];
		}

		if($template === 'need-help') {
			$colors = [
				'main'                     => __('Active buttons & switches highlight color', 'leyka'),
				'main-second'              => __('Secondary elements color', 'leyka'),
				'main-inactive'            => __('The inactive elements color. Most of the times, the main color with lighter shade', 'leyka'),
				'blocks-border'            => __('Form blocks border color', 'leyka'),
				'blocks-background'        => __('Form blocks background color', 'leyka'),
				'blocks-active-border'     => __('Form active blocks border color', 'leyka'),
				'blocks-active-background' => __('Form active blocks background color', 'leyka'),
				'error'                    => __('Form error messages color', 'leyka'),
				'text'                     => __('Form text color', 'leyka'),
				'text-superlight'          => __('Form text color, the most light shade', 'leyka'),
				'text-dark-bg'             => __('Form text color, for elements with dark background', 'leyka'),
			];
		}

	} else if($block_name === 'leyka/card' || $block_name === 'leyka/cards') {

		if($template === 'default') {
			$colors = [
				'title'            => __('Card title color', 'leyka'),
				'excerpt'          => __('Card description color', 'leyka'),
				'background'       => __('Card background color', 'leyka'),
				'button'           => __('Main CTA button background color', 'leyka'),
				'fulfilled'        => __('Progressbar fulfilled part color', 'leyka'),
				'unfulfilled'      => __('Progressbar unfulfilled part color', 'leyka'),
				'target_amount'    => __('Target amount color', 'leyka'),
				'collected_amount' => __('Collected amount color', 'leyka'),
			];
		}

	}

	return $colors;

}

// Block Color Variables:
function leyka_block_color_vars($block_name = 'leyka/form', $template = 'default') {

	$vars = [];

	foreach(leyka_block_colors($block_name, $template) as $slug => $label) {
		$slug_parts = explode( '_', $slug );
		$slug = '';
		foreach( $slug_parts as $part ) {
			$slug .= leyka_js_ucwords($part);
		}
		$vars[ 'color'.$slug ] = $label;
	}

	return $vars;

}

// Block Font Sizes:
function leyka_block_font_sizes($block_name = 'leyka/form', $template = 'default') {

	$font_sizes = [];
	if($block_name === 'leyka/form') {

		$font_sizes = [
			'main' => ['label' => __('Form text size', 'leyka'), 'default' => '16px',],
			'blocks-default' => ['label' => __('Form blocks text size', 'leyka'), 'default' => '16px',],
			'amounts' => ['label' => __('Donation amount blocks text size', 'leyka'), 'default' => '16px',],
			'pm-options' => ['label' => __('Payment method blocks text size', 'leyka'), 'default' => '12px',],
			'donor-fields' => ['label' => __('Donor data fields text size', 'leyka'), 'default' => '16px',],
			'submit' => ['label' => __('Form submit text size', 'leyka'), 'default' => '16px',],
			'section-titles' => ['label' => __('Form sections titles text size', 'leyka'), 'default' => '18px',],
		];

	}

	return $font_sizes;

}

// Block Font Sizes Variables:
function leyka_block_font_size_vars($block_name = 'leyka/form', $template = 'default') {

	$vars = [];
	foreach(leyka_block_font_sizes($block_name, $template) as $slug => $label) {
		$vars[ 'fontSize'.leyka_js_ucwords($slug) ] = $label;
	}

	return $vars;

}

function leyka_block_get_campaigns() {
	return get_posts(['post_type' => 'leyka_campaign', 'posts_per_page' => -1,]);
}

/**
 * Get The last Campaign ID.
 *
 * @return integer Last Campaign ID.
 */
function leyka_block_get_recent_campaign_id() {

	$campaign = get_posts(['post_type' => 'leyka_campaign', 'posts_per_page' => 1,]);

	if( $campaign ) {
		$campaign_id = $campaign[0]->ID;
	} else {
		$campaign_id = '';
	}

	return $campaign_id;

}