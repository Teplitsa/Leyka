<?php
/**
 * @package Leyka
 * @subpackage Shortcodes
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

/** Register CSS needed for shortcodes, widgets and other visual components */
function leyka_shortcodes_scripts(){
    // Respects SSL, Style.css is relative to the current file:
    wp_register_style('leyka-styles', LEYKA_PLUGIN_BASE_URL.'styles/style.css');
    wp_enqueue_style('leyka-styles');
}
add_action('wp_enqueue_scripts', 'leyka_shortcodes_scripts');

/**
 * Donations total amount counter.
 *
 * @param $atts An arguments of shortcode.
 * @param null $content A content enclosed in the shortcode.
 * @return string HTML of the shortcode widget.
 */
function leyka_total_amount_counter($atts, $content = null){
    global $edd_options;

    $atts = shortcode_atts(array(
        'status' => 'publish',
    ), $atts);

    $html = '<div class="b-counter">';
    $donates_quantity = sprintf('%06d', leyka_get_total_payments($atts['status']));
    $html .= '<div><span class="b-counter-count">'.substr($donates_quantity, 0, -1).'<span style="color: #f7941d;">'
        .substr($donates_quantity, -1)
        .'</span></span><span class="b-counter-text"> '.__('collected', 'leyka').'</span></div></div>';

    return $html;
}
add_shortcode('total_payments', 'leyka_total_amount_counter');

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