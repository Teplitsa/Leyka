<?php
/**
 * @package Leyka
 * @subpackage Shortcodes
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * Counter to show total donations number.
 *
 * @param $atts array Arguments of shortcode.
 * @param null $content A content enclosed in the shortcode.
 * @return string HTML of the shortcode widget.
 */
function leyka_total_donations_number($atts, $content = null){
    global $edd_options;

    $atts = shortcode_atts(array(
        'status' => 'publish',
    ), $atts);

    $html = '<div class="b-counter">';
    $donates_quantity = sprintf('%06d', leyka_get_donations_number($atts['status']));
    $html .= '<div><span class="b-counter-count">'.substr($donates_quantity, 0, -1).'<span style="color: #f7941d;">'
        .substr($donates_quantity, -1)
        .'</span></span><span class="b-counter-text"> '.__('collected', 'leyka').'</span></div></div>';

    return $html;
}
add_shortcode('donations_number', 'leyka_total_donations_number');

/** Page to list user recalls that comes with donations. */
function leyka_user_recalls_list($length = 20){
    $length = (int)$length > 0 ? (int)$length : 20;

    query_posts(array(
        'post_per_page' => $length,
        'post_type' => 'leyka_recall',
        'post_status' => 'publish'
    ));

    // The Loop
    while(have_posts()) {
        the_post();?>
    <li>
        <?php the_title();?>
        <br />
        <?php the_content();?>
        <div><?php
            $payment_metadata = get_post_meta(
                get_post_meta(get_the_ID(), '_leyka_payment_id' , true),
                '_edd_payment_meta',
                true
            );
            $donor_info = maybe_unserialize($payment_metadata['user_info']);
            echo $donor_info['first_name'].' '.$donor_info['last_name'].' | '.get_the_time('H:i, d.m.Y');
            ?></div>
    </li>
    <?php }

    // Reset Query
    wp_reset_query();
}
add_shortcode('recalls', 'leyka_user_recalls_list');

/**
 * Counter to show total amount of donations collected.
 *
 * @param $atts array Arguments of shortcode.
 * @param null $content A content enclosed in the shortcode.
 * @return string HTML of the shortcode widget.
 */
function leyka_funds_collected($atts, $content = null){
    global $edd_options;

    $atts = shortcode_atts(array(
//        'status' => 'publish',
//        'post_type' => 'edd_payment',
        'gateways' => '',
    ), $atts);

    $gateways_to_select = array();
    $available_gateways = array_keys(edd_get_enabled_payment_gateways());
    foreach(explode(',', $atts['gateways']) as $gateway) {
        if(in_array(trim($gateway), $available_gateways))
            $gateways_to_select[] = trim($gateway);
    }

    $atts = array('status' => 'publish', 'post_type' => 'edd_payment');
    if($gateways_to_select) {
        $gateway_sums = array();
        foreach($gateways_to_select as $gateway) {
            $atts['meta_query'] = array(
                array(
                    'key' => '_edd_payment_mode',
                    'value' => empty($edd_options['test_mode']) ? 'live' : 'test'
                ),
                array('key' => '_edd_payment_gateway', 'value' => $gateway,),  
            );

            // Count sum by current gateway:
            $gateway_sums[$gateway] = 0.0;
            foreach(get_posts($atts) as $donation) {
                $gateway_sums[$gateway] += get_post_meta($donation->ID, '_edd_payment_total', TRUE);
            }
        }

        // Count total sum collected:
        $sum = 0.0;
        foreach($gateway_sums as $gateway_sum) {
            $sum += $gateway_sum;
        }
    } else {
        $sum = 0.0;
        foreach(get_posts($atts) as $donation) {
            $sum += get_post_meta($donation->ID, '_edd_payment_total', TRUE);
        }
    }

    return edd_currency_filter($sum);
}
add_shortcode('funds_collected', 'leyka_funds_collected');

/**
 * Page to list all of the donation targets with an option to "quick" (1-click) donate to each.
 */
//add_shortcode('donates_cart_extra', function($atts, $content = null) {
//    extract(shortcode_atts(array(
//            'category'         => '',
//            'exclude_category' => '',
//            'tags'             => '',
//            'exclude_tags'     => '',
//            'relation'         => 'AND',
//            'number'           => 10,
//            'price'            => 'no',
//            'excerpt'          => 'yes',
//            'full_content'     => 'no',
//            'buy_button'       => 'yes',
//            'columns'          => 3,
//            'thumbnails'       => 'true',
//            'orderby'          => 'post_date',
//            'order'            => 'DESC'
//        ), $atts)
//    );
//    
//    /**
//     * @var $category
//     * @var $exclude_category
//     * @var $tags
//     * @var $exclude_tags
//     * @var $relation
//     * @var $number
//     * @var $price
//     * @var $excerpt
//     * @var $full_content
//     * @var $buy_button
//     * @var $columns
//     * @var $thumbnails
//     * @var $orderby
//     * @var $order
//     */
//
//    
//});