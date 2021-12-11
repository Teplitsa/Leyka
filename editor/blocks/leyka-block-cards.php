<?php if(!defined('WPINC') ) die;
/**
 * Gutenberg Block - Cards
 */

function leyka_block_cards_attributes(){

	$attributes = [
		'campaign' => ['type' => 'string', 'default' => leyka_block_get_recent_campaign_id(),],
		'template' => ['type' => 'string', 'default' => 'star',],
		'className' => ['type' => 'string', 'default' => '',],
		'anchor' => ['type' => 'string', 'default' => '',],
		'align' => ['type' => 'string', 'default' => 'wide',],
		'postsToShow' => ['type' => 'integer', 'default' => 2,],
		'columns' => ['type' => 'integer', 'default' => 2,],
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
		'queryInclude' => ['type' => 'array', 'default' => array(),],
		'queryExclude' => ['type' => 'array', 'default' => array(),],
		'queryOffset' => ['type' => 'string', 'default' => '',],
		'queryIsFinished' => ['type' => 'boolean', 'default' => true,],
		'queryOrderBy' => ['type' => 'string', 'default' => 'date',],
		'queryCampaignType' => ['type' => 'string', 'default' => 'all',],
	];

	foreach(leyka_block_color_vars('leyka/cards') as $slug => $label) {
		$attributes[$slug] = ['type' => 'string', 'default' => '',];
	}

	return $attributes;

}

/**
 * Register Block Type Leyka Cards
 */
register_block_type('leyka/cards', [
	'render_callback' => 'leyka_block_cards_render_callback',
	'attributes' => leyka_block_cards_attributes(),
]);

/**
 * Render Block Leyka Cards
 *
 * @param array $attr Block Attributes.
 */
function leyka_block_cards_render_callback( $attr, $content ) {

	$classes = ['block_class' => 'leyka-block-cards'];

	if ( isset( $attr['align'] ) && $attr['align'] ) {
		$classes['align'] = 'align' . $attr['align'];
	} else {
		$classes['align'] = 'alignnone';
	}

	if ( isset( $attr['className'] ) && $attr['className'] ) {
		$classes['class_name'] = $attr['className'];
	}

	$style_attr = [];

	$const_keys = array_keys(leyka_block_color_vars('leyka/cards'));
	$color_index = 0;
	foreach(leyka_block_colors('leyka/cards') as $slug => $label) {
		$const = $const_keys[$color_index];
		if( !empty($attr[$const]) ) {
			$style_attr['color_'.$slug] = $attr[$const];
		}
		$color_index++;
	}

	if($style_attr) {
		$classes[] = 'has-leyka-custom-colors';
	}

	// Id
	$attr_id = '';
	if ( isset( $attr['anchor'] ) && $attr['anchor'] ) {
		$attr_id = esc_attr( $attr['anchor'] ) . '"';
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
		'classes' => 'leyka-block-card',
	];

	if( $style_attr ) {
		$shortcode_attr_arr = array_merge($shortcode_attr_arr, $style_attr);
	}

	$block_style = '';

	// Columns
	if ( isset( $attr['columns'] ) && $attr['columns'] ) {
		$block_style .= '--leyka-grid-columns:' . esc_attr( $attr['columns'] ) . ';';
		$classes[] = 'leyka-grid-columns-' . esc_attr( $attr['columns'] );
	}

	// Title font size
	if ( isset( $attr['titleFontSize'] ) && $attr['titleFontSize'] ) {
		$block_style .= '--leyka-card-title-size:' . esc_attr( $attr['titleFontSize'] ) . ';';
	}

	// Description font size
	if ( isset( $attr['excerptFontSize'] ) && $attr['excerptFontSize'] ) {
		$block_style .= '--leyka-card-excerpt-size:' . esc_attr( $attr['excerptFontSize'] ) . ';';
	}

	$block_attr = 'class="' . implode(' ', $classes) . '"';

	if ( isset( $attr['anchor'] ) && $attr['anchor'] ) {
		$block_attr .= ' id="' . esc_attr( $attr['anchor'] ) . '"';
	}

	if ( $block_style ) {
		$block_attr .= ' style="' . $block_style . '"';
	}

	// Campaigns To Show
	$posts_to_show = 1;
	if ( isset( $attr['postsToShow'] ) ) {
		$posts_to_show = $attr['postsToShow'];
	}

	// Query Args
	$args = array(
		'post_type'      => 'leyka_campaign',
		'posts_per_page' => $posts_to_show,
	);

	// Include campaigns.
	if ( $attr['queryInclude'] ) {
		$post__in = array();
		foreach ( $attr['queryInclude'] as $page_title ) {
			$page_obj = get_page_by_title( $page_title, OBJECT, 'leyka_campaign' );
			$post__in[] = $page_obj->ID;
		}
		$args['post__in'] = $post__in;

		// Order by
		if ( $attr['queryOrderBy'] && 'date' !== $attr['queryOrderBy'] ) {
			$args['orderby'] = $attr['queryOrderBy'];
		}
	}

	// Exclude campaigns.
	if ( $attr['queryExclude'] ) {
		$post__not_in = array();
		foreach ( $attr['queryExclude'] as $page_title ) {
			$page_obj = get_page_by_title( $page_title, OBJECT, 'leyka_campaign' );
			$post__not_in[] = $page_obj->ID;
		}

		if ( isset( $args['post__in'] ) && $post__not_in ) {

			foreach ( $post__not_in as $id ) {
				$key = array_search( $id, $args['post__in'] );
				if ( isset( $args['post__in'][ $key ] ) ) {
					unset( $args['post__in'][ $key ] );
				}
			}
			
		} else {
			$args['post__not_in'] = $post__not_in;
		}
	}

	// Offset.
	if ( $attr['queryOffset'] ) {
		$args['offset'] = $attr['queryOffset'];
	}

	// Exclude finished campaigns.
	if ( false === $attr['queryIsFinished'] ) {
		$args['meta_query'][] = array(
			'key'     => 'is_finished',
			'value'   => 1,
			'compare' => '!=',
			'type' => 'NUMERIC',
		);
	}

	// Filte by campaign type.
	if ( 'all' !== $attr['queryCampaignType'] ) {
		$args['meta_query'][] = array(
			'key'     => 'campaign_type',
			'value'   => $attr['queryCampaignType'],
		);
	}

	/**
	 * Render Campaigns Cards
	 */
	$html = '';

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) :

		$html = '<div ' . $block_attr . '>';
		$html .= '<div class="leyka-block-cards-grid">';

		while ( $query->have_posts() ) : $query->the_post();

			$html .= '<div class="leyka-grid-item">';
			$html .= leyka_shortcode_campaign_card( $shortcode_attr_arr );
			$html .= '</div>';

		endwhile;

		$html .= '</div>';
		$html .= '</div>';

	else:

	endif;

	wp_reset_postdata();

	return $html;

}