<?php if( !defined('WPINC') ) die;
/**
 * Leyka Revo Template code to load during page initialization.
 **/

// Revo campaigns have different elements order:
add_action('pre_get_posts', function(WP_Query $query){

    if( // Can't use $query->is_singular(Leyka_Campaign_Management::$post_type) here,
        // because there is no $query->get_queried_object_id() value at this point
        $query->is_main_query()
        && $query->is_singular()
        && $query->get('post_type') == Leyka_Campaign_Management::$post_type
        && $query->get('name')
    ) {

        remove_filter('the_content', 'leyka_print_donation_elements');

        if( !leyka_options()->opt_template('do_not_display_donation_form') ) {
            add_filter('the_content', 'leyka_revo_template_campaign_page');
        }

    }

}, 11);

function leyka_revo_template_campaign_page($content) {

    if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
        return $content;
    }

    $campaign_id = get_queried_object_id();

    $before = leyka_inline_campaign(['id' => $campaign_id, 'template' => 'revo']);
    $after = leyka_inline_campaign_small($campaign_id);

    return $before.$content.$after;

}