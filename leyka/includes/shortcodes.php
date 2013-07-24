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

/** Show all available donates list. Replacement for [downloads] EDD shortcode. */
function leyka_donates(){
    echo do_shortcode('[downloads]');
}
add_shortcode('donates', 'leyka_donates');

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
function leyka_user_recalls_list($atts, $content = NULL){
    $atts = shortcode_atts(array(
        'length' => 20,
        'fields' => ''
    ), $atts);

    $atts['length'] = (int)$atts['length'] > 0 ? (int)$atts['length'] : 20;

    $possible_fields = array('title', 'text', 'date', 'sum', 'donates', 'author',);
    $atts['fields'] = empty($atts['fields']) ? $possible_fields : explode(',', $atts['fields']);
    foreach($atts['fields'] as $key => $field) {
        if( !in_array(trim($field), $possible_fields) )
            unset($atts['fields'][$key]);
    }

    $recalls = get_posts(array(
        'post_per_page' => $atts['length'],
        'post_type' => 'leyka_recall',
        'post_status' => 'publish'
    ));

    foreach($recalls as $recall) {?>
        <li>
            <?php if(in_array('title', $atts['fields'])) {?>
                <div><?php echo $recall->post_title;?></div>
            <?php }?>
            <br />
            <?php if(in_array('text', $atts['fields'])) {?>
                <div><?php echo $recall->post_content;?></div>
            <?php }?>
            <div>
                <?php $payment_id = get_post_meta($recall->ID, '_leyka_payment_id', TRUE);
                $payment_metadata = get_post_meta($payment_id, '_edd_payment_meta', TRUE);
                if( !$payment_metadata )
                    continue;
                if(in_array('sum', $atts['fields'])) {?>
                    <span><?php echo edd_currency_filter(get_post_meta($payment_id, '_edd_payment_total', TRUE));?></span>
                <?php }

                if(in_array('donates', $atts['fields'])) {
                    $donates = @maybe_unserialize($payment_metadata['downloads']);?>
                    <div>
                        <strong><?php _e('Donates', 'leyka');?>:</strong>
                        <ul>
                            <?php foreach($donates as $donate) {
                                $donate = get_post($donate['id']);?>
                                <li><a href="<?php echo get_permalink($donate->ID);?>"><?php echo $donate->post_title;?></a></li>
                            <?php }?>
                        </ul>
                    </div>
                <?php }

                if(in_array('author', $atts['fields'])) {
                    $donor_info = maybe_unserialize($payment_metadata['user_info']);?>
                    <span><?php echo $donor_info['first_name'];?></span>
                <?php }

                if(in_array('date', $atts['fields'])) {?>
                    <span><?php echo get_the_time('H:i, d.m.Y', $recall);?></span>
                <?php }?>
            </div>
        </li>
    <?php }
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
    global $edd_options, $edd_logs;

    $atts = shortcode_atts(array(
        'gateways' => '',
        'donates' => '',
//        'donates_ex' => '',
    ), $atts);

    $gateways_to_select = array();
    $available_gateways = array_keys(edd_get_enabled_payment_gateways());
    foreach(explode(',', $atts['gateways']) as $gateway) {
        if(in_array(trim($gateway), $available_gateways))
            $gateways_to_select[] = trim($gateway);
    }

    $donates_to_select = array();
    if($atts['donates']) {
        foreach(explode(',', $atts['donates']) as $donate_id) {
            if((int)$donate_id > 0)
                $donates_to_select[] = $donate_id;
        }
    }

//    $donates_to_exclude = array();
//    if($atts['donates_ex']) {
//        foreach(explode(',', $atts['donates_ex']) as $donate_id) {
//            if(in_array($donate_id, $donates_to_select))
//                unset($donates_to_select[array_search($donate_id, $donates_to_select)]);
//            else
//                $donates_to_exclude[] = $donate_id;
//        }
//    }

    $donations_to_select = array();
    foreach($donates_to_select as $donate_id) {
        $donations_entries = $edd_logs->get_connected_logs(array(
            'post_parent' => $donate_id,
            'log_type' => 'sale',
            'posts_per_page' => -1
        ));

        if(empty($donations_entries))
            continue;

        foreach($donations_entries as $log_entry) {
            $donations_to_select[] = get_post_meta($log_entry->ID, '_edd_log_payment_id', TRUE);
        }
    }

    $atts = array(
        'numberposts' => -1, // Selecting all donation posts, without paging
        'status' => 'publish',
        'post_type' => 'edd_payment',
        'post__in' => $donations_to_select,
//        'post__not_in' => $donates_to_exclude,
    );
    if($gateways_to_select) {
        $gateway_sums = array();
        foreach($gateways_to_select as $gateway) {
            $atts['meta_query'] = array(
                array(
                    'key' => '_edd_payment_mode',
                    'value' => empty($edd_options['test_mode']) ? 'live' : 'test'
                ),
                array('key' => '_edd_payment_gateway', 'value' => $gateway,)
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
        $atts['meta_query'] = array(array(
            'key' => '_edd_payment_mode',
            'value' => empty($edd_options['test_mode']) ? 'live' : 'test'
        ));

        $sum = 0.0;
        foreach(get_posts($atts) as $donation) {
            foreach(edd_get_payment_meta_cart_details($donation->ID) as $donate_in_cart) {
                if(in_array($donate_in_cart['id'], $donates_to_select))
                    $sum += $donate_in_cart['price'];
            }

            // $sum += get_post_meta($donation->ID, '_edd_payment_total', TRUE);
        }
    }

    return edd_currency_filter($sum);
}
add_shortcode('funds_collected', 'leyka_funds_collected');

/** Page to list all of the donation targets with an option to "quick" (1-click) donate to each. */
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