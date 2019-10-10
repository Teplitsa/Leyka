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
            (get_post() && get_post()->post_type === Leyka_Campaign_Management::$post_type ? get_the_ID() : false) :
            ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

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
        $atts,
        $amount_collected
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
            (get_post() && get_post()->post_type === Leyka_Campaign_Management::$post_type ? get_the_ID() : false) :
            ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

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
        $atts,
        $query->found_posts
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

    $donors_params = array(
        'role__in' => array(Leyka_Donor::DONOR_USER_ROLE,),
        'number' => -1,
        'fields' => 'id',
        'meta_query' => array(),
    );

    if($atts['campaign_id']) {

        $atts['campaign_id'] = $atts['campaign_id'] === 'current' ?
            (get_post() && get_post()->post_type === Leyka_Campaign_Management::$post_type ? get_the_ID() : false) :
            ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

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
        $atts,
        $donors_count
    );

}

add_shortcode('leyka_donations_list', 'leyka_shortcode_donations_list');
function leyka_shortcode_donations_list($atts) {

    $atts = shortcode_atts(array(
        // Possible values: 'all'/0/false to count funded donations for all campaigns,
        // 'current' for current campaign,
        // int for campaign with ID given:
        'campaign_id' => 'current',
        'recurring' => 0, // True/1 to count only active recurring subscriptions, false/0 otherwise
        'header_text' => apply_filters('leyka_shortcode_donations_list_header', __('Donations history', 'leyka'), $atts),
        'show_name' => 1,
        'show_date' => 1,
        'show_time' => 1,
        'show_campaign' => 0,
        'show_amount' => 1,
        // Possible values: // 0/false/'none' | 'display-total' | 'display-total-only'
        'show_total_amount_as' => leyka_options()->opt('widgets_total_amount_usage'),
//        'show_purpose' => 1,
//        'show_comment' => 1,
        'show_type_text' => 1,
        'show_type_icon' => 1,
        'length' => isset($atts['num']) ? absint($atts['num']) : leyka_get_donations_list_per_page(),
        'classes' => '', // HTML classes for the shortcode wrapper
    ), $atts);

    $donations_params = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' => 'funded',
        'posts_per_page' => absint($atts['length']),
        'meta_query' => array(),
    );

    if($atts['campaign_id']) {

        $atts['campaign_id'] = $atts['campaign_id'] === 'current' ?
            (get_post() && get_post()->post_type === Leyka_Campaign_Management::$post_type ? get_the_ID() : false) :
            ($atts['campaign_id'] === 'all' ? false : absint($atts['campaign_id']));

        if($atts['campaign_id']) {
            $donations_params['meta_query'][] = array('key' => 'leyka_campaign_id', 'value' => absint($atts['campaign_id']),);
        }

    }
    if($atts['recurring']) {
        $donations_params['post_parent'] = 0;
    }

    $table_columns = array();
    if($atts['show_name']) {
        $table_columns['donation_donor_name'] = _x('Name', "Donation donor's name, in one word", 'leyka');
    }
    if($atts['show_date']) {
        $table_columns['donation_date'] = $atts['show_time'] ? __('Date / time', 'leyka') : __('Date', 'leyka');
    }
    if($atts['show_type_text']) {
        $table_columns['donation_type'] = __('Payment type', 'leyka');
    }
    if($atts['show_amount']) {
        $table_columns['donation_amount'] = __('Amount', 'leyka');
    }
    $atts['show_total_amount_as'] = $atts['show_total_amount_as'] === 'none' ? false : $atts['show_total_amount_as'];

    $table_lines = array();
    foreach(get_posts($donations_params) as $donation) {

        $donation = new Leyka_Donation($donation);

        $line = array();
        if($atts['show_name']) {
            $line['donation_donor_name'] = $donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka');
        }
        if($atts['show_date']) {
            $line['donation_date'] = $donation->date_time_label;
        }
        if($atts['show_type_text']) {
            $line['donation_type'] = $donation->type_label;
        }
        if($atts['show_amount']) {

            if($atts['show_total_amount_as'] === 'display-total-only') {
                $line['donation_amount'] = $donation->amount_total.' '.$donation->currency_label;
            } else if($atts['show_total_amount_as'] === 'display-total' && $donation->amount !== $donation->amount_total) {
                $line['donation_amount'] = $line['donation_amount'] =
                    '<span class="amount">'.$donation->amount.' '.$donation->currency_label.'</span>'
                    .' / <span class="amount-total">'.$donation->amount_total.' '.$donation->currency_label.'</span>';
            } else {
                $line['donation_amount'] = $donation->amount.' '.$donation->currency_label;
            }

            if($atts['show_type_icon']) {
                $line['donation_amount'] = '<img class="donation-type-icon" src="'.LEYKA_PLUGIN_BASE_URL.'/img/dashboard/icon-donation-type-'.$donation->type.'.svg" alt="">'
                    .$line['donation_amount'];
            }

        }

        $table_lines[] = $line;

    }

    ob_start();?>

    <div class="leyka-shortcode donations-list '<?php echo $atts['classes'] ? esc_attr($atts['classes']) : '';?>">

        <table class="donations-list-table">

        <?php if($atts['header_text']) {?>
            <caption><?php echo esc_html($atts['header_text']);?></caption>
        <?php }

        if($table_columns) {?>

            <thead>
                <tr>
                <?php foreach($table_columns as $column_id => $column_title) {?>
                    <th class="list-column column-<?php echo $column_id;?>"><?php echo $column_title;?></th>
                <?php }?>
                </tr>
            </thead>

        <?php }

        if( !$table_lines ) {?>
            <td colspan="<?php echo count($table_columns);?>"><?php _e('No donations yet', 'leyka');?></td>
        <?php } else {?>

            <tbody>
            <?php foreach($table_lines as $line) {?>

                <tr>
                <?php foreach($table_columns as $column_id => $column_title) {
                    if(isset($line[$column_id])) {?>
                    <td class="list-line field-<?php echo $column_id;?>"><?php echo $line[$column_id];?></td>
                    <?php }
                }?>
                </tr>

            <?php }?>
            </tbody>
        <?php }?>

        </table>

    </div>

<?php return apply_filters('leyka_shortcode_donations_list', ob_get_clean(), $atts, $table_columns, $table_lines);

}