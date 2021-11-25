<?php if( !defined('WPINC') ) die;
/**
 * Leyka Need Help Template code to load during page initialization.
 **/

// The Different elements order for Campaigns:
if( !is_admin() && is_main_query() && is_singular(Leyka_Campaign_Management::$post_type) ) {

    remove_filter('the_content', 'leyka_print_donation_elements');

    if( !leyka_options()->opt_template('do_not_display_donation_form') ) {
        add_filter('the_content', 'leyka_need_help_template_campaign_page');
    }

}

function leyka_need_help_template_campaign_page($content) {

    if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
        return $content;
    }

    return leyka_payment_form_screen(['id' => get_queried_object_id(), 'template' => 'need-help'])
        .$content;

}