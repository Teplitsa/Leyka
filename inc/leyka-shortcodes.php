<?php if( !defined('WPINC') ) die;
/**
 * Leyka template shortcodes
 *
 **/


/**
 * Scale shortcode
 **/

add_shortcode('leyka_scale', 'leyka_scale_screen');
function leyka_scale_screen($atts) {

    $atts = shortcode_atts([
        'id' => 0,
        'show_button' => 0,
    ], $atts);

    $campaign = $atts['id'] > 0 ? get_post($atts['id']) : get_post();

    if( !$campaign || $campaign->post_type != Leyka_Campaign_Management::$post_type ) { // Wrong campaign data
        return '';
    }

    return '<div id="'.esc_attr('leyka_scale_standalone-'.uniqid()).'">'.leyka_get_scale($campaign, $atts).'</div>';

}

function leyka_get_scale($campaign = null, $args = []) {

    $args = wp_parse_args($args, [
        'show_button' => 0,
        'embed_mode' => 0
    ]);

    $campaign = leyka_get_validated_campaign($campaign);
    if( !$campaign ) {
        return '';
    }

    $css_class = 'leyka-scale';
    if($args['show_button'] == 1 && !$campaign->target ) {
        $css_class .= ' has-button-alone';
    } else if($args['show_button'] == 1) {
        $css_class .= ' has-button';
    }

    ob_start();?>

    <div class="<?php echo esc_attr($css_class);?>">
        <?php leyka_scale_compact($campaign);?>
        <?php if($args['show_button'] == 1 && !$campaign->is_finished) {?>
            <div class="leyka-scale-button">
                <a href="<?php echo esc_attr(trailingslashit(get_permalink($campaign->ID))).'#leyka-payment-form';?>" <?php echo wp_kses_post( $campaign->ID === get_the_ID() ? 'class="leyka-scroll"' : '');?> <?php echo wp_kses_post( $args['embed_mode'] === 1 ? 'target="_blank"' : '');?>>
                    <?php echo esc_attr(leyka_get_scale_button_label());?>
                </a>
            </div>
        <?php }?>
    </div>
    <?php $out = ob_get_clean();

    return apply_filters('leyka_scale_html', $out, $campaign, $args);

}

function leyka_get_scale_button_label(){
    return apply_filters('leyka_scale_button_label', _x('Support', '«Support» label at scale button', 'leyka'));
}


/**
 * Campaign card shortcode
 **/

add_shortcode('leyka_campaign_card', 'leyka_campaign_card_screen' );
function leyka_campaign_card_screen($atts) {

    $atts = shortcode_atts([
        'id' => 0,
        'show_title' => 1,
        'show_thumb' => 1,
        'show_excerpt' => 1,
        'show_scale' => 1,
        'show_finished' => 1,
        'show_button' => 1,
    ], $atts);

    $campaign_post = $atts['id'] > 0 ? get_post($atts['id']) : get_post();

    if( !is_a($campaign_post, 'WP_Post') || $campaign_post->post_type !== Leyka_Campaign_Management::$post_type ) {
        return is_super_admin() ? leyka_get_wrong_campaign_message($campaign_post) : '';
    }

    $campaign = new Leyka_Campaign($campaign_post);
    $campaign->increase_views_counter();

    return '<div id="'.esc_attr('leyka_campaign_card_standalone-'.uniqid()).'">'
        .leyka_get_campaign_card($campaign_post, $atts).'</div>';

}

function leyka_get_campaign_card($campaign = null, $args = []) {

    $args = wp_parse_args($args, [
        'show_title' => 1,
        'show_thumb' => 1,
        'show_excerpt' => 1,
        'show_scale' => 1,
        'show_button' => 1,
        'show_finished' => 1,
        'increase_counters' => 0,
        'embed_mode' => 0,
    ]);

    if( !$campaign ) {
        $campaign = get_post();
    } else if(is_numeric($campaign)) {
        $campaign = get_post($campaign);
    }

    if(is_a($campaign, 'WP_Post') && $campaign->post_type === Leyka_Campaign_Management::$post_type) {

        $campaign_obj = $campaign;
        $campaign = leyka_get_validated_campaign($campaign);

    } else {
        return is_super_admin() ? leyka_get_wrong_campaign_message($campaign) : '';
    }

    if($campaign->is_finished && !$args['show_finished']) {
        return '';
    }

    $target = !!$args['embed_mode'] ? 'target="_blank"' : '';
    $thumbnail_size = apply_filters('leyka_campaign_card_thumbnail_size', 'post-thumbnail', $campaign_obj, $args);
    $css_class = apply_filters('leyka_campaign_card_class', 'leyka-campaign-card', $campaign_obj, $args);
    if($args['show_thumb'] == 1 && has_post_thumbnail($campaign_obj->ID)) {
        $css_class .= ' has-thumb';
    }

    ob_start();?>

    <div class="<?php echo esc_attr($css_class);?>">
        <?php if($args['show_thumb'] == 1 && has_post_thumbnail($campaign_obj->ID)) {?>
            <div class="lk-thumbnail">
                <a href="<?php echo esc_url(get_permalink($campaign_obj));?>" <?php echo wp_kses_post( $target );?>>
                    <?php echo get_the_post_thumbnail(
                        $campaign_obj->ID,
                        $thumbnail_size,
                        ['alt' => esc_attr(sprintf(__('Thumbnail for - %s', 'leyka'), $campaign_obj->post_title)),]
                    );?>
                </a>
            </div>
        <?php }?>

        <?php if($args['show_title'] == 1 || $args['show_excerpt'] == 1) {?>
            <div class="lk-info">
                <?php if($args['show_title'] == 1) {?>
                    <h4 class="lk-title"><a href="<?php echo esc_url(get_permalink($campaign_obj));?>" <?php echo wp_kses_post( $target );?>>
                            <?php echo esc_html( get_the_title($campaign_obj));?>
                        </a></h4>
                <?php }?>

                <?php if($args['show_excerpt'] == 1) {

                    add_filter('leyka_get_the_excerpt', 'wptexturize');
                    add_filter('leyka_get_the_excerpt', 'convert_smilies');
                    add_filter('leyka_get_the_excerpt', 'convert_chars');
                    add_filter('leyka_get_the_excerpt', 'wp_trim_excerpt');?>
                    <p>
                        <?php if(has_excerpt($campaign_obj->ID)) {
                            $text = $campaign_obj->post_excerpt;
                        } else {

                            $text = $campaign_obj->post_content ? $campaign_obj->post_content : ' '; // So wp_trim_excerpt work correctly
                            $text = leyka_strip_string_by_words($text, 200, true).(mb_strlen($text) > 200 ? '...' : '');

                        }
                        echo wp_kses_post(apply_filters('leyka_get_the_excerpt', $text, $campaign_obj));?>
                    </p>
                <?php }?>
            </div>
        <?php }

        if( !!$args['show_scale'] ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo leyka_get_scale($campaign_obj,	[
                'show_button' => $args['show_button'],
                'embed_mode' => $args['embed_mode'],
            ]);
        } else if( !!$args['show_button'] ) {

            $url = trailingslashit(get_permalink($campaign_obj->ID)).'#leyka-payment-form'.
                ( !!$args['increase_counters'] ? '?increase_counters=1' : '' );?>

            <div class="leyka-scale-button-alone">
                <a href="<?php echo esc_url( $url );?>" <?php echo wp_kses_post( $campaign_obj->ID === get_the_ID() ? 'class="leyka-scroll"' : '' );?> <?php echo wp_kses_post( $target );?>>
                    <?php echo esc_html(leyka_get_scale_button_label());?>
                </a>
            </div>

        <?php }?>
    </div>

    <?php $out = ob_get_clean();
    return apply_filters('leyka_campaign_card_html', $out, $campaign_obj, $args);

}


/**
 * Payment form shortcode
 **/
add_shortcode('leyka_payment_form', 'leyka_payment_form_screen');
add_shortcode('leyka_donation_form', 'leyka_payment_form_screen');
add_shortcode('leyka_campaign_form', 'leyka_payment_form_screen');
function leyka_payment_form_screen($atts) {

    $atts = shortcode_atts(['id' => false, 'template' => null, 'show_finished' => 1], $atts);

    $campaign_id = !empty($atts['id']) ? (int)$atts['id'] : get_post()->ID;
    $campaign = leyka_get_validated_campaign($campaign_id);

    if( !$campaign ) {
        return is_super_admin() ? leyka_get_wrong_campaign_message($campaign) : '';
    } else if($campaign->is_finished && empty($atts['show_finished']) /*|| $campaign->status !== 'publish'*/ ) {
        return '';
    }

    if($campaign->template === 'revo') {
        return leyka_inline_campaign($atts);
    }

    return leyka_get_payment_form($campaign, $atts);

}

function leyka_get_payment_form($campaign = null, $args = []) {

    $args = wp_parse_args($args, ['template' => null,]);

    if( !$campaign ) {
        $campaign = get_post();
    } else {
        $campaign = leyka_get_validated_campaign($campaign);
    }

    $campaign->increase_views_counter();

    return get_leyka_payment_form_template_html($campaign, $args['template']);

}


/**
 * Donation tickers shortcode
 **/
add_shortcode('leyka_donors_list', 'leyka_donors_list_screen' );
function leyka_donors_list_screen($atts) {

    $atts = shortcode_atts([
        'id'           => 'all', // Could be also 0 ("obtain from context") or real campaign ID
        'num'          => leyka_get_donations_list_per_page(),
        'show_purpose' => 1,
        'show_name'    => 1,
        'show_date'    => 1,
    ], $atts);

    if($atts['id'] !== 'all') {
        $atts['id'] = (int)$atts['id'];
    }

    return leyka_get_donors_list($atts['id'], $atts);

}

function leyka_get_donations_list_per_page() {
    return apply_filters('leyka_donations_list_per_page', 10);
}

function leyka_get_donors_list($campaign_id = 'all', $args = []) {

    $args = wp_parse_args($args, [
        'num' => leyka_get_donations_list_per_page(),
        'show_purpose' => 1,
        'show_name' => 1,
        'show_date' => 1,
        'show_donation_comments' => false, // leyka_options()->opt('show_donation_comments_in_frontend'),
    ]);

    if($campaign_id === 0 && get_post()->post_type === Leyka_Campaign_Management::$post_type) {
        $campaign_id = get_post()->ID;
    }

    // Get donations: funded amount > 0
    $donations_params = ['status' => 'funded', 'results_limit' => $args['num'], 'amount_filter' => 'only+',];

    if($campaign_id && $campaign_id !== 'all') {
        $donations_params['campaign_id'] = $campaign_id;
    }

    $donations = Leyka_Donations::get_instance()->get($donations_params);

    if( !$donations ) {
        return '';
    }

    ob_start();?>

    <div id="<?php echo esc_attr('leyka_donors_list-'.uniqid());?>" class="leyka-donors-list">
    <?php foreach($donations as $donation) {

        if(leyka_options()->opt('widgets_total_amount_usage') === 'display-total') {
            $amount = $donation->amount_formatted.'<span class="amount-total"> / '.$donation->amount_total_formatted.'</span>';
        } else if(leyka_options()->opt('widgets_total_amount_usage') == 'display-total-only') {
            $amount = $donation->amount_total_formatted;
        } else {
            $amount = $donation->amount_formatted;
        }

        $html = "<div class='ldl-item'>";
        $html .= "<div class='amount'>".apply_filters('leyka_donations_list_amount_content', $amount.' '.$donation->currency_label, $donation)."</div>";

        if($args['show_purpose'] && $donation->campaign_id) {
            $html .= "<div class='purpose'><a href='".get_permalink($donation->campaign_id)."'>".$donation->campaign_payment_title."</a></div>";
        }

        $meta = [];

        if($args['show_name']) {
            $meta[] = '<span>'.($donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka')).'</span>';
        }

        if($args['show_date']) {

            if($donation->type === 'correction') {

                $time = gmdate('H:i:s', $donation->date_timestamp) == '00:00:00' ? '' : gmdate(' '.get_option('time_format'), $donation->date_timestamp);
                $date = gmdate(get_option('date_format').$time, $donation->date_timestamp);

            } else {
                $date = $donation->date_funded;
            }

            if($date) {
                $meta[] = '<time>'.$date.'</time>';
            }

        }

        if($meta) {
            $html .= apply_filters(
                'leyka_donations_list_meta_content',
                "<div class='meta'>".implode(' / ', $meta)."</div>",
                $donation
            );
        }

        if($args['show_donation_comments'] && $donation->donor_comment) {
            $html .= '<div class="donor-comment">'.apply_filters('leyka_donors_list_comment', $donation->donor_comment).'</div>';
        }

        $html .= "</div>";

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo apply_filters('leyka_donors_list_item_html', $html, $campaign_id, $args);

    }?>
    </div>

    <?php return ob_get_clean();

}

/** Terms of Service shortcode. */
add_shortcode('leyka_service_terms_text', 'leyka_get_terms_text'); // The function is defined in the functions.php

function leyka_get_wrong_campaign_message($campaign) {
    return apply_filters('leyka_wrong_campaign_shortcode_message', '<div class="leyka-nopm-error leyka-form-level-error">'.__('Campaign shortcode error: wrong campaign given.', 'leyka').'</div>', $campaign);
}

function leyka_get_campaign_supporters($campaign_id = false, $max_names = 5) {

    $campaign_id = $campaign_id ? absint($campaign_id) : false;

    $donations = [];
    $first_donors_names = [];

    foreach(leyka_get_campaign_donations($campaign_id) as $donation) { /** @var $donation Leyka_Donation_Base */

        if(
            $donation->donor_name &&
            !in_array($donation->donor_name, [__('Anonymous', 'leyka'), 'Anonymous']) &&
            !in_array($donation->donor_name, $first_donors_names)
        ) {

            if(count($first_donors_names) < absint($max_names)) {
                $first_donors_names[] = $donation->donor_name;
            }

            $donations[] = $donation;

        }

    }

    return ['supporters' => $first_donors_names, 'donations' => $donations,];

}

function leyka_get_campaign_supporters_names($campaign_id = false, $max_names = 5) {

    $campaign_id = $campaign_id ? absint($campaign_id) : false;
    $max_names = absint($max_names) ? absint($max_names) : false;

    global $wpdb;

    $query_joins = "LEFT JOIN {$wpdb->prefix}posts p ON p.ID = meta.post_id";
    $query_where = "meta.meta_key = 'leyka_donor_name' AND p.post_status = 'funded'";
    if($campaign_id) {

        $query_joins .= " LEFT JOIN {$wpdb->prefix}postmeta meta_1 ON p.ID = meta_1.post_id";
        $query_where .= " AND meta_1.meta_key = 'leyka_campaign_id' AND meta_1.meta_value = $campaign_id";

    }

    $first_donors_names_total = $wpdb->get_var(
        "SELECT COUNT(DISTINCT meta.meta_value) FROM {$wpdb->prefix}postmeta meta $query_joins WHERE $query_where"
    );
    $first_donors_names = $wpdb->get_col(
        "SELECT DISTINCT meta.meta_value FROM {$wpdb->prefix}postmeta meta $query_joins WHERE $query_where ORDER BY p.ID DESC ".($max_names ? 'LIMIT 0,'.(5*$max_names) : '')
    );

    return [
        'names' => array_slice($first_donors_names, 0, $max_names), // First $max_names of donors' names
        'names_remain' => array_slice($first_donors_names, $max_names), // The remains of the names, no more than 4*$max_names
        'total' => $first_donors_names_total,
    ];

}

add_shortcode('leyka_inline_campaign', 'leyka_inline_campaign');
function leyka_inline_campaign(array $atts = []) {

    $atts = shortcode_atts([
        'id' => false,
        'template' => 'revo', // ATM this shortcode is only for Revo
        'show_thumbnail' => leyka_options()->opt('revo_template_show_thumbnail'),
        'show_finished' => true,
        'show_preview' => true,
    ], $atts);

    $campaign_id = $atts['id'] ? absint($atts['id']) : get_post()->ID;
    $campaign = leyka_get_validated_campaign($campaign_id);

    if( !$campaign ) {
        return is_super_admin() ? leyka_get_wrong_campaign_message($campaign) : '';
    } else if($campaign->is_finished && !$atts['show_finished']) {
        return '';
    }

    if($campaign->template !== 'revo') {

        $atts['template'] = $campaign->template;

        return leyka_payment_form_screen($atts);

    }

    $template_id = 'revo'; // $atts['template']; // ATM this shortcode is only for Revo. WARNING: ATM it should be set explicitly here 'cause of the default "template" attr gets overriden by an empty value given in the function arg.
    $template_subdir = LEYKA_PLUGIN_DIR.'templates/leyka-'.$template_id;
    $template_file = LEYKA_PLUGIN_DIR.'templates/leyka-template-'.$template_id.'.php';

    if($template_id && file_exists($template_subdir) && file_exists($template_file)) {
        foreach(glob($template_subdir.'/leyka-'.$template_id.'-*.php') as $file) {
            if(stristr($file, 'leyka-'.$template_id.'-init.php') === false) {
                require_once($file);
            }
        }
    } else {
        return false;
    }

    $atts['show_thumbnail'] = !!$atts['show_thumbnail'];
    $thumb_url = $atts['show_thumbnail'] ? get_the_post_thumbnail_url($campaign_id, 'post-thumbnail') : false;

    ob_start();?>

    <div id="<?php echo esc_attr(leyka_pf_get_form_id($campaign_id));?>" class="leyka-pf leyka-pf-<?php echo esc_attr( $template_id );?> <?php echo esc_attr(leyka_pf_get_form_auto_open_class($campaign_id));?> <?php if($atts['show_preview']):?>show-preview<?php endif?>" data-form-id="<?php echo esc_attr(leyka_pf_get_form_id($campaign->id)).'-revo-form';?>" data-leyka-ver="<?php echo esc_attr(Leyka_Payment_Form::get_plugin_ver_for_atts());?>">

        <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo leyka_get_svg( LEYKA_PLUGIN_DIR . 'assets/svg/svg.svg' );
        ?>

        <div class="leyka-pf__overlay"></div>

        <div class="leyka-pf__module <?php echo leyka_options()->opt_template('show_donation_comment_field') ? 'leyka-with-comment' : '';?>">
            <div class="leyka-pf__close leyka-js-close-form">x</div>
            <div class="leyka-pf__card inpage-card">

            <?php if($thumb_url) {?>
                <div class="inpage-card__thumbframe"><div class="inpage-card__thumb" style="background-image: url(<?php echo esc_url( $thumb_url );?>);"></div></div>
            <?php }?>

                <div class="inpage-card__content">

                    <div class="inpage-card_title"><?php echo esc_html(get_the_title($campaign_id));?></div>

                    <?php if($atts['show_preview'] && $campaign->post_excerpt) {?>
                    <div class="inpage-card__excerpt">
                        <?php echo wp_kses_post( $campaign->post_excerpt ); ?>
                        <div class="inpage-card__toggle-excerpt-links">
                            <a href="#" class="inpage-card__expand-excerpt"><?php esc_html_e('More', 'leyka');?></a>
                            <a href="#" class="inpage-card__collapse-excerpt"><?php esc_html_e('Hide', 'leyka');?></a>
                        </div>
                    </div>
                    <?php }?>

					<div class="inpage-card_scale">

                    <?php $collected = leyka_get_campaign_collections($campaign_id);
						$target = leyka_get_campaign_target($campaign_id);

						if($target) { // Campaign target set

							$ready = isset($target['amount']) ? round(100.0*$collected['amount']/$target['amount'], 1) : 0;
							$ready = $ready >= 100.0 ? 100.0 : $ready;?>

                        <div class="scale">
                            <div class="progress <?php echo esc_attr( $ready >= 100.0 ? 'fin' : '' );?>" style="width:<?php echo esc_attr( $ready ); ?>%;"></div>
                        </div>

                        <div class="target">
                        <?php if($ready > 0) {?>
                            <?php echo wp_kses_post(leyka_format_amount($collected['amount']));?>
                            <span class="curr-mark">
                                <?php echo esc_html(leyka_options()->opt("currency_{$collected['currency']}_label"));?>
                            </span>
                        <?php } else {?>
                            <span><?php esc_html_e('Support', 'leyka');?></span>
                        <?php }?>
                        </div>

                        <div class="info">
                            <?php if ( $atts['show_preview'] ) {
                                esc_html_e('Amount needed', 'leyka');
                            } else {
                                esc_html_e( 'collected of ', 'leyka');
                            }?>
                            <?php echo esc_html(leyka_format_amount($target['amount']));?>
                            <span class="curr-mark">
                                <?php echo esc_html(leyka_options()->opt("currency_{$target['currency']}_label"));?>
                            </span>
                        </div>
					<?php } else {  // Campaign doesn't have a target sum  - display empty scale ?>

						<div class="scale hide-scale"></div>
                        
                        <div class="target">
                            <?php echo esc_html(leyka_format_amount($collected['amount']));?>
                            <span class="curr-mark">
                                <?php echo esc_html(leyka_options()->opt("currency_{$collected['currency']}_label"));?>
                            </span>
                            <span class="info"><?php esc_html_e('collected', 'leyka');?></span>
                        </div>

                    <?php }?>
					</div>

					<?php $supporters = leyka_options()->opt('revo_template_show_donors_list') ?
                        leyka_get_campaign_supporters($campaign_id, 5) : ['donations' => [], 'supporters' => [],];?>

					<div class="supporter-and-button">

                        <div class="inpage-card__note supporters">
                        <?php if(count($supporters['supporters'])) {

                            array_walk($supporters['supporters'], function(&$value) { // Capitalize donors' names
                                $value = mb_ucfirst($value);
                            });?>

                            <strong><?php esc_html_e('Supporters', 'leyka');?>:</strong>

                            <?php if(count($supporters['donations']) <= count($supporters['supporters'])) { // Only names

                                echo count($supporters['supporters']) === 1 ?
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    reset($supporters['supporters']) :
                                    esc_attr(implode(', ', array_slice($supporters['supporters'], 0, -1))).' '.esc_html__('and', 'leyka').' '.esc_html(end($supporters['supporters']));
                            } else { // Names and the number of the rest of donors

                                echo esc_html(implode(', ', array_slice($supporters['supporters'], 0, -1)).' '.__('and', 'leyka'));?>

                                <a href="#" class="leyka-js-history-more">
                                    <?php echo sprintf(esc_html__('%d more', 'leyka'), count($supporters['donations']) - count($supporters['supporters']));?>
                                </a>

                            <?php }

                        } else if( !$thumb_url ) {?>
                            <div class="no-supporters">
                                <svg class="svg-icon pic-first-step"><use xlink:href="#pic-first-step" /></svg>
                                <div class="lets-do-first-step-text"><?php esc_html_e('Every campaign is a journey. Be the one to make the first step.', 'leyka');?></div>
                            </div>
                        <?php }?>
                        </div>

                        <div class="inpage-card__action">
                        <?php if($campaign->is_finished) {?>
                            <div class="message-finished"><?php esc_html_e('The fundraising campaign has been finished. Thank you for your support!', 'leyka');?></div>
                        <?php } else { ?>
                            <button type="button" class="leyka-js-open-form">
                                <?php echo esc_html(leyka_options()->opt_template('donation_submit_text'));?>
                            </button>
                        <?php } ?>
                        </div>
                    
                    </div>

                </div>

				<?php if(count($supporters['donations']) > count($supporters['supporters'])) {?>

                <div class="inpage-card__history history">
                    <div class="history__close leyka-js-history-close">x</div>
                    <div class="history__title"><?php esc_html_e('We are grateful to', 'leyka');?></div>
                    <div class="history__list">
                        <div class="history__list-flow">
                        <?php foreach(leyka_get_campaign_donations($campaign_id) as $donation) {
                            /** @var $donation Leyka_Donation */?>

                            <div class="history__row">
                                <div class="history__cell h-amount">
                                    <?php if(leyka_options()->opt('widgets_total_amount_usage') == 'display-total') {
                                         echo wp_kses_post( $donation->amount == $donation->amount_total ?
                                             leyka_format_amount($donation->amount) :
                                             leyka_format_amount($donation->amount).'<span class="amount-total"> / '.leyka_format_amount($donation->amount_total).'</span>' );

                                    } else if(leyka_options()->opt('widgets_total_amount_usage') == 'display-total-only') {
                                        echo esc_html(leyka_format_amount($donation->amount_total));
                                    } else {
                                        echo esc_html(leyka_format_amount($donation->amount));
                                    }?>
                                    <span class="curr-mark">
                                        <?php echo esc_html(leyka_options()->opt("currency_{$target['currency']}_label"));?>
                                    </span>
                                </div>
                                <div class="history__cell h-name"><?php echo esc_html( $donation->donor_name );?></div>
                                <div class="history__cell h-date"><?php echo esc_html( $donation->date_label );?></div>
                            </div>

                        <?php }?>
                        </div>
                    </div>
                </div>

				<?php }?>
            </div>

            <div class="leyka-pf__form leyka-payment-form <?php echo leyka_options()->opt_template('show_donation_comment_field') ? 'leyka-with-comment' : '';?>">
            <?php require($template_file); /** @todo For the forms caching task - remove this require line */ ?>
            </div>

            <?php leyka_pf_submission_errors();?>

            <div class="leyka-pf__redirect">
                <div class="waiting">
                    <div class="waiting__card">
                        <div class="loading">
                            <div class="spinner">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                        <div class="waiting__card-text"><?php echo esc_html(apply_filters('leyka_short_gateway_redirect_message', __('Awaiting for the safe payment page redirection...', 'leyka')));?></div>
                    </div>
                </div>
            </div>

            <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
            <div class="leyka-pf__oferta oferta">
                <div class="oferta__frame">
                    <div class="oferta__flow"><?php echo wp_kses_post(leyka_get_terms_text());?></div>
                </div>
                <div class="oferta__action">
                    <a href="#" class="leyka-js-oferta-close">
                        <?php echo esc_html(leyka_options()->opt('leyka_agree_to_terms_text_text_part').' '.leyka_options()->opt('leyka_agree_to_terms_text_link_part'));?>
                    </a>
                </div>
            </div>
            <?php }?>

            <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {?>
            <div class="leyka-pf__pd pd">
                <div class="pd__frame">
                    <div class="pd__flow"><?php echo wp_kses_post(leyka_get_pd_terms_text());?></div>
                </div>
                <div class="pd__action">
                    <a href="#" class="leyka-js-pd-close">
                        <?php echo esc_html(leyka_options()->opt('agree_to_pd_terms_text_text_part').' '.leyka_options()->opt('agree_to_pd_terms_text_link_part'));?>
                    </a>
                </div>
            </div>
            <?php }?>

        </div>
    </div>

    <?php $out = ob_get_contents();
    ob_end_clean();

    return $out;

}

add_shortcode('leyka_inline_campaign_small', 'leyka_inline_campaign_small');
function leyka_inline_campaign_small($atts) {

    $atts = shortcode_atts(['id' => false,], $atts);

    $campaign_id = $atts['id'] ? absint($atts['id']) : get_post()->ID;
    $campaign = leyka_get_validated_campaign($campaign_id);

    if( !$campaign ) {
        return is_super_admin() ? leyka_get_wrong_campaign_message($campaign) : '';
    } else if($campaign->is_finished) {
        return '';
    }

    if(mb_strlen(wp_strip_all_tags($campaign->content)) < 1500) {
        return '';
    }

    $currency_data = leyka_get_currencies_data(leyka_options()->opt('currency_main'));

    ob_start();?>

    <div data-target="<?php echo esc_attr(leyka_pf_get_form_id($campaign_id));?>" id="leyka-pf-bottom-<?php echo esc_attr( $campaign_id );?>" class="leyka-pf-bottom bottom-form">
        <div class="bottom-form__label"><?php esc_html_e('Make a donation', 'leyka');?></div>
        <div class="bottom-form__fields">
            <div class="bottom-form__field">
                <input type="text" value="<?php echo esc_attr( $currency_data['amount_settings']['flexible'] );?>" name="leyka_temp_amount">
                <span class="curr-mark"><?php echo esc_attr( $currency_data['label'] );?></span>
            </div>
            <div class="bottom-form__button">
                <button type="button" class="leyka-js-open-form-bottom"><?php echo esc_html(leyka_options()->opt_template('donation_submit_text') );?></button>
            </div>
        </div>

		<?php $supporters = leyka_options()->opt('revo_template_show_donors_list') ?
                leyka_get_campaign_supporters($campaign_id, 5) : ['donations' => [], 'supporters' => [],];

			if(count($supporters['supporters'])) { // There is at least one donor ?>

			<div class="bottom-form__note supporters">
            <strong><?php esc_html_e('Supporters:', 'leyka');?></strong>

            <?php if(count($supporters['donations']) <= count($supporters['supporters'])) { // Only names in the list
                echo wp_kses_post(count($supporters['supporters']) == 1 ?
                    $supporters['supporters'][0] :
                    implode(', ', array_slice($supporters['supporters'], 0, -1)).' '.__('and', 'leyka').' '.
                    end($supporters['supporters']));
            } else { // Names list and the number of the rest of donors
                echo esc_html(implode(', ', array_slice($supporters['supporters'], 0, -1)).' '.__('and', 'leyka'));?>

                <a href="#" class="leyka-js-history-more">
                    <?php echo esc_html(sprintf(__('%d more', 'leyka'), count($supporters['donations']) - count($supporters['supporters'])));?>
                </a>

            <?php }?>
			</div>
		<?php }?>

		<?php if(count($supporters['donations']) > count($supporters['supporters'])) { ?>
        <div class="bottom-form__history history">
            <div class="history__close leyka-js-history-close">x</div>
            <div class="history__title"><?php esc_html_e('We are grateful to', 'leyka');?></div>
            <div class="history__list">
                <div class="history__list-flow">

                <?php foreach(leyka_get_campaign_donations($campaign_id) as $donation) {
                    /** @var $donation Leyka_Donation */?>

                    <div class="history__row">
                        <div class="history__cell h-amount">
                            <?php if(leyka_options()->opt('widgets_total_amount_usage') == 'display-total') {
                                echo wp_kses_post( $donation->amount == $donation->amount_total ?
                                    leyka_format_amount($donation->amount) :
                                    leyka_format_amount($donation->amount).'<span class="amount-total"> / '.leyka_format_amount($donation->amount_total).'</span>' );

                            } else if(leyka_options()->opt('widgets_total_amount_usage') == 'display-total-only') {
                                echo esc_html(leyka_format_amount($donation->amount_total));
                            } else {
                                echo esc_html(leyka_format_amount($donation->amount));
                            }?>
                            <span class="curr-mark"><?php echo esc_html( $currency_data['label'] );?></span>
                        </div>
                        <div class="history__cell h-name"><?php echo esc_html( $donation->donor_name );?></div>
                        <div class="history__cell h-date"><?php echo esc_html( $donation->date_label );?></div>
                    </div>

                <?php }?>

                </div>
            </div>
            <?php /** @todo Add normal donations history page template & return this link */
//            echo '<div class="history__action">
//                <a href="'.leyka_get_donations_archive_url($campaign_id).'">'.__('Show all donors', 'leyka').'</a>
//            </div>';?>
        </div>
		<?php } ?>
    </div>
    <?php

    $out = ob_get_contents();
    ob_end_clean();

    return $out;

}

/** @todo For the forms caching task: */
//add_action('wp_footer', function(){
//
//    global $test; // USE A COLLECTION/FACTORY OBJECT INSTEAD OF GLOBAL!
//    if(empty($test)) {
//        return;
//    }
//    foreach($test as $form_id => $form_html) {
//        echo wp_kses_post( $form_html );
//    }
//
//}, 100);

require_once LEYKA_PLUGIN_DIR.'inc/leyka-shortcodes-new.php';
