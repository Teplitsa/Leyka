<?php if( !defined('WPINC') ) die;
/**
 * Leyka template shortcodes - the new set (v3.6+)
 *
 **/

/** Donations amount collected displaying */
add_shortcode('leyka_sum', 'leyka_shortcode_amount_collected');
add_shortcode('leyka_amount_collected', 'leyka_shortcode_amount_collected');
function leyka_shortcode_amount_collected($atts) {

    $atts = shortcode_atts(array(
        // Possible values: 'all'/0/false for all campaigns, 'current' for current campaign, int for campaign with ID given:
        'campaign_id' => 'current',
        'total_funded' => 0, // True/1 to use the "amount_total" field in counting, false/0 to use the "amount" field.
        'recurring' => 0, // True/1 to count only active recurring subscriptions amount, false/0 otherwise
        'classes' => '', // HTML classes for the shortcode wrapper
    ), $atts);

    $amount_collected = 0.0;
    $donation_params = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'nopaging' => true,
        'post_status' => 'funded',
    );

    if($atts['campaign_id']) {

        $atts['campaign_id'] = $atts['campaign_id'] === 'current' ?
            get_the_ID() : ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

        if($atts['campaign_id']) {
            $donation_params['meta_query'][] = array('key' => 'leyka_campaign_id', 'value' => esc_sql($atts['campaign_id']));
        }

    }
    if($atts['recurring']) {

        $donation_params['post_parent'] = 0;
        $donation_params['meta_query'][] = array('key' => 'leyka_payment_type', 'value' => 'rebill',);

    }

    foreach(get_posts($donation_params) as $donation) {

        $donation = new Leyka_Donation($donation);

        $amount_collected += $atts['total_funded'] ? $donation->amount_total : $donation->amount;

    }

    return apply_filters(
        'leyka_shortcode_amount_collected',
        '<span class="leyka-shortcode amount-collected '.($atts['classes'] ? esc_attr($atts['classes']) : '').'">'.$amount_collected.'</span>',
        $amount_collected,
        $atts
    );

}

/** Donations collected count displaying */
add_shortcode('leyka_donations_count', 'leyka_shortcode_donations_count');
function leyka_shortcode_donations_count($atts) {

    $atts = shortcode_atts(array(
        // Possible values: 'all'/0/false to count funded donations for all campaigns,
        // 'current' for current campaign,
        // int for campaign with ID given:
        'campaign_id' => 'current',
        'recurring' => 0, // True/1 to count only active recurring subscriptions, false/0 otherwise
        'classes' => '', // HTML classes for the shortcode wrapper
    ), $atts);

    $donation_params = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'nopaging' => true,
        'post_status' => 'funded',
    );

    if($atts['campaign_id']) {

        $atts['campaign_id'] = $atts['campaign_id'] === 'current' ?
            get_the_ID() : ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

        if($atts['campaign_id']) {
            $donation_params['meta_query'][] = array('key' => 'leyka_campaign_id', 'value' => esc_sql($atts['campaign_id']));
        }

    }
    if($atts['recurring']) {

        $donation_params['post_parent'] = 0;
        $donation_params['meta_query'][] = array('key' => 'leyka_payment_type', 'value' => 'rebill',);

    }

    $query = new WP_Query($donation_params);

    return apply_filters(
        'leyka_shortcode_donations_count',
        '<span class="leyka-shortcode donations-count '.($atts['classes'] ? esc_attr($atts['classes']) : '').'">'.$query->found_posts.'</span>',
        $query->found_posts,
        $atts
    );

}

/** Donations collected count displaying */
add_shortcode('leyka_donors_count', 'leyka_shortcode_donors_count');
function leyka_shortcode_donors_count($atts) {

    $atts = shortcode_atts(array(
        // Possible values: 'all'/0/false to count funded donations for all campaigns,
        // 'current' for current campaign,
        // int for campaign with ID given:
        'campaign_id' => 'current',
        'recurring' => 0, // True/1 to count only active recurring subscriptions, false/0 otherwise
        'classes' => '', // HTML classes for the shortcode wrapper
    ), $atts);

    $donors_params = array('role__in' => array(Leyka_Donor::DONOR_USER_ROLE,), 'number' => -1, 'fields' => 'id',);

    if($atts['campaign_id']) {

        $atts['campaign_id'] = $atts['campaign_id'] === 'current' ?
            get_the_ID() : ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

        if($atts['campaign_id']) {
            $donors_params['meta_query'][] = array(
                'key' => 'leyka_donor_campaigns',
                'value' => 'i:'.absint($atts['campaign_id']).';', // A little freaky, I know, but it's the best we could think of
                'compare' => 'LIKE',
            );
        }

    }
    if($atts['recurring']) {
        $donors_params['meta_query'][] = array('key' => 'leyka_donor_type', 'value' => 'regular',);
    }

    $query = new WP_User_Query($donors_params);
    $donors_count = $query->get_total();

    return apply_filters(
        'leyka_shortcode_donors_count',
        '<span class="leyka-shortcode donors-count '.($atts['classes'] ? esc_attr($atts['classes']) : '').'">'.$donors_count.'</span>',
        $donors_count,
        $atts
    );

}
