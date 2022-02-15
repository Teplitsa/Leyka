<?php if( !defined('WPINC') ) die;
/**
 * Leyka Need Help Template code to load during page initialization.
 **/

// The Different elements order for Campaigns:
add_action('pre_get_posts', function(WP_Query $query){

    if( // Can't use $query->is_singular(Leyka_Campaign_Management::$post_type) here,
        // because there is no $query->get_queried_object_id() value at this point
        $query->is_main_query()
        && $query->is_singular()
        && $query->get('post_type') == Leyka_Campaign_Management::$post_type
        && $query->get('name')
    ) {

        remove_filter('the_content', 'leyka_print_donation_elements');

        if( !leyka_options()->opt_template('do_not_display_donation_form')) {
            add_filter('the_content', 'leyka_need_help_template_campaign_page');
        }

    }

}, 11);

function leyka_need_help_template_campaign_page($content) {

    if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
        return $content;
    }

    $campaign = new Leyka_Campaign(get_queried_object_id());
    $form_html = leyka_payment_form_screen(['id' => $campaign->id, 'template' => 'need-help']);

    return $campaign->form_content_position === 'before-content' ? $form_html.$content : $content.$form_html;

}