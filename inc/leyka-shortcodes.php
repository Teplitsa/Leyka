<?php if( !defined('WPINC') ) die;
/**
 * Leyka template shortcodes
 *
 **/


/**
 * Scale shortcode
 **/

add_shortcode('leyka_scale', 'leyka_scale_screen' );
function leyka_scale_screen($atts) {

    $a = shortcode_atts(array(
        'id'          => 0,
        'show_button' => 0,
    ), $atts);

    $campaign = $a['id'] > 0 ? get_post($a['id']) : get_post();

    if( !$campaign || $campaign->post_type != Leyka_Campaign_Management::$post_type ) { // Wrong campaign data
        return '';
    }

    return "<div id='".esc_attr('leyka_scale_standalone-'.uniqid())."'>".leyka_get_scale($campaign, $a)."</div>";
}

function leyka_get_scale($campaign = null, $args = array()) {

    $current_post = get_post();

    $defaults = array(
        'show_button' => 0,
        'embed_mode'  => 0
    );

    $args = wp_parse_args($args, $defaults);

    if( !$campaign ) {
        $campaign = $current_post;
    } elseif(is_int($campaign)) {
        $campaign = get_post($campaign);
    }

    if($campaign->post_type != Leyka_Campaign_Management::$post_type) { // Wrong campaign data
        return '';
    }

    $campaign = new Leyka_Campaign($campaign);

    $css_class = 'leyka-scale';
    if($args['show_button'] == 1 && (int)$campaign->target == 0) {
        $css_class .= ' has-button-alone';
    } elseif($args['show_button'] == 1) {
        $css_class .= ' has-button';
    }

    ob_start();?>

    <div class="<?php echo esc_attr($css_class);?>">
        <?php leyka_scale_compact($campaign);?>
        <?php if($args['show_button'] == 1 && !$campaign->is_finished) {?>
            <div class="leyka-scale-button">
                <a href='<?php echo trailingslashit(get_permalink($campaign->ID)).'#leyka-payment-form';?>' <?php echo $campaign->ID == $current_post->ID ? 'class="leyka-scroll"' : '';?><?php echo $args['embed_mode'] === 1 ? ' target="_blank"' : '';?>>
                    <?php echo leyka_get_scale_button_label();?>
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

    $a = shortcode_atts(array(
        'id' => 0,
        'show_title' => 1,
        'show_thumb' => 1,
        'show_excerpt' => 1,
        'show_scale' => 1,
        'show_button' => 1,
    ), $atts);

    $campaign_post = $a['id'] > 0 ? get_post($a['id']) : get_post();

    if($campaign_post->post_type != Leyka_Campaign_Management::$post_type) { // Wrong campaign data
        return '';
    }

    $campaign = new Leyka_Campaign($campaign_post);
    $campaign->increase_views_counter();

    return '<div id="'.esc_attr('leyka_campaign_card_standalone-'.uniqid()).'">'
    .leyka_get_campaign_card($campaign_post, $a).'</div>';
}

function leyka_get_campaign_card($campaign = null, $args = array()) {

    $current_post = get_post();

    $defaults = array(
        'show_title' => 1,
        'show_thumb' => 1,
        'show_excerpt' => 1,
        'show_scale' => 1,
        'show_button' => 1,
        'increase_counters' => 0,
        'embed_mode' => 0,
    );

    $args = wp_parse_args($args, $defaults);

    if( !$campaign ) {
        $campaign = $current_post;
    } elseif((int)$campaign > 0) {
        $campaign = get_post($campaign);
    } elseif( !is_a($campaign, 'WP_Post') ) {
        return false;
    }

    if($campaign->post_type != Leyka_Campaign_Management::$post_type) { // Wrong campaign data
        return '';
    }

    $target = $args['embed_mode'] == 1 ? ' target="_blank"' : '';
    $thumbnail_size = apply_filters('leyka_campaign_card_thumbnail_size', 'post-thumbnail', $campaign, $args);
    $css_class = apply_filters('leyka_campaign_card_class', 'leyka-campaign-card', $campaign, $args);
    if($args['show_thumb'] == 1 && has_post_thumbnail($campaign->ID)) {
        $css_class .= ' has-thumb';
    }

    ob_start(); // Do we have some content ?>

    <div class="<?php echo esc_attr($css_class);?>">
        <?php if($args['show_thumb'] == 1 && has_post_thumbnail($campaign->ID)) {?>
            <div class="lk-thumbnail">
                <a href="<?php echo get_permalink($campaign);?>"<?php echo $target;?>>
                    <?php echo get_the_post_thumbnail(
                        $campaign->ID,
                        $thumbnail_size,
                        array('alt' => esc_attr(sprintf(__('Thumbnail for - %s', 'leyka'), $campaign->post_title)),)
                    );?>
                </a>
            </div>
        <?php }?>

        <?php if($args['show_title'] == 1 || $args['show_excerpt'] == 1) {?>
            <div class="lk-info">
                <?php if($args['show_title'] == 1) {?>
                    <h4 class="lk-title"><a href="<?php echo get_permalink($campaign);?>"<?php echo $target;?>>
                            <?php echo get_the_title($campaign);?>
                        </a></h4>
                <?php }?>

                <?php if($args['show_excerpt'] == 1) {
                    // Default excerpt filters:
                    add_filter('leyka_get_the_excerpt', 'wptexturize');
                    add_filter('leyka_get_the_excerpt', 'convert_smilies');
                    add_filter('leyka_get_the_excerpt', 'convert_chars');
                    add_filter('leyka_get_the_excerpt', 'wp_trim_excerpt');?>
                    <p>
                        <?php if(has_excerpt($campaign->ID)) {
                            $text = $campaign->post_excerpt;
                        } else {

                            $text = $campaign->post_content ? $campaign->post_content : ' '; // So wp_trim_excerpt work correctly
                            $text = leyka_strip_string_by_words($text, 200, true).(mb_strlen($text) > 200 ? '...' : '');

                        }
                        echo apply_filters('leyka_get_the_excerpt', $text, $campaign);?>
                    </p>
                <?php }?>
            </div>
        <?php }?>

        <?php if($args['show_scale'] == 1) {

            echo leyka_get_scale($campaign,	array(
                'show_button' => $args['show_button'],
                'embed_mode' => $args['embed_mode']
            ));

        } elseif($args['show_button'] == 1 && !$campaign->is_finished) {

            $url = trailingslashit(get_permalink($campaign->ID)).'#leyka-payment-form'.
                ( !!$args['increase_counters'] ? '?increase_counters=1' : '' );?>

            <div class="leyka-scale-button-alone">
                <a href="<?php echo $url;?>" <?php echo $campaign->ID == $current_post->ID ? 'class="leyka-scroll"' : '';?>
                    <?php echo $target;?>><?php echo leyka_get_scale_button_label();?>
                </a>
            </div>

        <?php }?>
    </div>

    <?php $out = ob_get_clean();
    return apply_filters('leyka_campaign_card_html', $out, $campaign, $args);

}


/**
 * Payment form shortcode
 **/
add_shortcode('leyka_payment_form', 'leyka_payment_form_screen');
add_shortcode('leyka_campaign_form', 'leyka_payment_form_screen');
function leyka_payment_form_screen($atts) {

    $a = shortcode_atts(array(
        'id'          => 0,
        'template'    => null,
    ), $atts);

    $campaign = $a['id'] > 0 ? get_post($a['id']) : get_post();

    if( !$campaign || $campaign->post_type != Leyka_Campaign_Management::$post_type ) {
        return '';
    }

    return leyka_get_payment_form($campaign, $a);

}

function leyka_get_payment_form($campaign = null, $args = array()) {

    $defaults = array(
        'template'  => null, // Ex. "radios" or "toggles"
    );

    $args = wp_parse_args($args, $defaults);

    if( !$campaign ) {
        $campaign = get_post();
    } elseif(is_int($campaign)){
        $campaign = get_post($campaign);
    }

    if($campaign->post_type != Leyka_Campaign_Management::$post_type) {
        return '';
    }

    $campaign = new Leyka_Campaign($campaign);
    $campaign->increase_views_counter();

    return get_leyka_payment_form_template_html($campaign, $args['template']);

}


/**
 * Donation tickers shortcode
 **/
add_shortcode('leyka_donors_list', 'leyka_donors_list_screen' );
function leyka_donors_list_screen($atts) {

    $a = shortcode_atts( array(
        'id'           => 'all', //could be also 0 (obtained from context) or real ID
        'num'          => leyka_get_donors_list_per_page(),
        'show_purpose' => 1,
        'show_name'    => 1,
        'show_date'    => 1,
    ), $atts );

    if($a['id'] != 'all')
        $a['id'] = (int)$a['id'];

    return leyka_get_donors_list($a['id'], $a);

}

function leyka_get_donors_list_per_page() {
    return apply_filters('leyka_donors_list_per_page', 25);
}

function leyka_get_donors_list($campaign_id = 'all', $args = array()) {

    $defaults = array(
        'num'          => leyka_get_donors_list_per_page(),
        'show_purpose' => 1,
        'show_name'    => 1,
        'show_date'    => 1,
    );

    $args = wp_parse_args($args, $defaults);

    if($campaign_id === 0) {
        $campaign_id = get_post()->ID;
    }

    // Get donations: funded amount > 0
    $d_args = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' => 'funded',
        'posts_per_page' => $args['num'],
        'meta_query' => array(
            array(
                'key'     => 'leyka_donation_amount',
                'value'   => 0,
                'compare' => '!=',
                'type'    => 'NUMERIC'
            )
        )
    );

    if($campaign_id != 'all') {

        $d_args['meta_query']['relation'] = 'AND';
        $d_args['meta_query'][] = array(
            'key'   => 'leyka_campaign_id',
            'value' => $campaign_id
        );
    }

    $donations = get_posts($d_args);

    if( !$donations ) {
        return '';
    }

    ob_start();?>

    <div id="<?php echo esc_attr('leyka_donors_list-'.uniqid());?>" class="leyka-donors-list">
        <?php
        foreach($donations as $donation) {

            $donation = new Leyka_Donation($donation);

            $amount = number_format($donation->sum, 0, '.', ' ');

            $html = "<div class='ldl-item'>";
            $html .= "<div class='amount'>".apply_filters('leyka_donations_list_amount_content', $amount.' '.$donation->currency_label, $donation)."</div>";

            if($args['show_purpose'] == 1) {
                $html .= "<div class='purpose'><a href='".get_permalink($donation->campaign_id)."'>".$donation->campaign_payment_title."</a></div>";
            }

            $meta = array();
            if($args['show_name'] == 1) {

                $name = $donation->donor_name;
                $name = (!empty($name)) ? $name : __('Anonymous', 'leyka');
                $meta[] = '<span>'.$name.'</span>';
            }

            if($args['show_date'] == 1) {

                if($donation->type == 'correction') {

                    $time = date('H:i:s', $donation->date_timestamp) == '00:00:00' ? '' : date(' '.get_option('time_format'), $donation->date_timestamp);
                    $date = date(get_option('date_format').$time, $donation->date_timestamp);

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

            $html .= "</div>";

            echo apply_filters('leyka_donors_list_item_html', $html, $campaign_id, $args);
        }?>
    </div>

    <?php $out = ob_get_clean();
    return $out;

}

/**
 * Terms of Service shortcode
 **/
add_shortcode('leyka_service_terms_text', 'leyka_get_terms_text');
function leyka_get_terms_text() {
    return apply_filters('leyka_terms_of_service_text', leyka_options()->opt('terms_of_service_text'));
}

function leyka_get_campaign_supporters($campaign_id, $max_names = 5) {

    $donations = leyka_get_campaign_donations($campaign_id);
    $first_donors_names = array();
    foreach($donations as $donation) { /** @var $donation Leyka_Donation */

        if(
            $donation->donor_name &&
            !in_array($donation->donor_name, array(__('Anonymous', 'leyka'), 'Anonymous')) &&
            !in_array($donation->donor_name, $first_donors_names)
        ) {
            $first_donors_names[] = mb_ucfirst($donation->donor_name);
        }

        if(count($first_donors_names) >= (int)$max_names) {
            break;
        }

    }

    return array('supporters' => $first_donors_names, 'donations' => $donations);

}

add_shortcode('leyka_inline_campaign', 'leyka_inline_campaign');
function leyka_inline_campaign(array $attributes = array()) {

    $attributes = shortcode_atts(array(
        'id' => false,
        'template' => leyka_options()->opt('donation_form_template'),
        'show_thumbnail' => leyka_options()->opt('revo_template_show_thumbnail'),
    ), $attributes);

    $campaign_id = $attributes['id'] ? (int)$attributes['id'] : get_post()->ID;
    $campaign = leyka_get_validated_campaign($campaign_id);
    if( !$campaign ) {
        return '';
    }

    $template_id = $attributes['template'];
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

    $attributes['show_thumbnail'] = !!$attributes['show_thumbnail'];
    $thumb_url = $attributes['show_thumbnail'] ? get_the_post_thumbnail_url($campaign_id, 'post-thumbnail') : false;

    /** @todo For the forms caching task */
//    global $test; // USE A COLLECTION/FACTORY OBJECT INSTEAD OF GLOBAL!
//    $test = array();
//
//    if( empty($test[$campaign_id.'-'.$template_id]) ) {
//
//        ob_start();
//        require($template_file);
//        $out = ob_get_clean();
//
//        $test[$campaign_id.'-'.$template_id] = $out;
//
//    }

    ob_start();?>

    <div id="<?php echo leyka_pf_get_form_id($campaign_id);?>" class="leyka-pf <?php echo leyka_pf_get_form_auto_open_class($campaign_id);?>" data-form-id="<?php echo leyka_pf_get_form_id($campaign->id).'-revo-form';?>">
        <?php include(LEYKA_PLUGIN_DIR.'assets/svg/svg.svg');?>
        <div class="leyka-pf__overlay"></div>

        <div class="leyka-pf__module">
            <div class="leyka-pf__close leyka-js-close-form">x</div>
            <div class="leyka-pf__card inpage-card">

            <?php if($thumb_url) {?>
                <div class="inpage-card__thumbframe"><div class="inpage-card__thumb" style="background-image: url(<?php echo $thumb_url;?>);"></div></div>
            <?php }?>

                <div class="inpage-card__content">
                    <div class="inpage-card_title"><?php echo get_the_title($campaign_id);?></div>

					<div class="inpage-card_scale">
                    <?php $collected = leyka_get_campaign_collections($campaign_id);
						$target = leyka_get_campaign_target($campaign_id);

						if($target) { // Campaign target set

							$ready = isset($target['amount']) ?
                                round(100.0*$collected['amount']/$target['amount'], 1) : 0;
							$ready = $ready >= 100.0 ? 100.0 : $ready;?>

                        <div class="scale">
                            <div class="progress <?php echo $ready >= 100.0 ? 'fin' : '';?>" style="width:<?php echo $ready;?>%;"></div>
                        </div>

                        <div class="target">
                            <?php echo leyka_format_amount($collected['amount']);?>
                            <span class="curr-mark">
                                <?php echo leyka_options()->opt("currency_{$collected['currency']}_label");?>
                            </span>
                        </div>

                        <div class="info"><?php _e('collected of ', 'leyka');?>
                            <?php echo leyka_format_amount($target['amount']);?>
                            <span class="curr-mark">
                                <?php echo leyka_options()->opt("currency_{$target['currency']}_label");?>
                            </span>
                        </div>
					<?php } else {  // Campaign doesn't have a target sum  - display empty scale ?>
						<div class="scale"></div>
                    <?php }?>
					</div>

					<?php $supporters = leyka_get_campaign_supporters($campaign_id, 5); ?>
					<div class="inpage-card__note supporters">
					<?php if(count($supporters['supporters'])) {?>

                        <strong><?php _e('Supporters:', 'leyka');?></strong>

                        <?php if(count($supporters['donations']) <= count($supporters['supporters'])) { // Only names
                            echo implode(', ', array_slice($supporters['supporters'], 0, -1))
                                .' '.__('and', 'leyka').' '.end($supporters['supporters']);
                        } else { // Names and the number of the rest of donors

                            echo implode(', ', array_slice($supporters['supporters'], 0, -1)).' '.__('and', 'leyka');?>

                            <a href="#" class="leyka-js-history-more">
                                <?php echo sprintf(__('%d more', 'leyka'), count($supporters['donations']) - count($supporters['supporters']));?>
                            </a>

                        <?php }

					} else if( !$thumb_url ) {?>
                        <div class="no-supporters">
    					    <svg class="svg-icon pic-first-step"><use xlink:href="#pic-first-step" /></svg>
                            <div class="lets-do-first-step-text"><?php _e("Every campaign is a journey. Be the one to make the first step.", 'leyka');?></div>
                        </div>
                        <?php
					}?>
                    </div>

                    <div class="inpage-card__action">
					<?php if($campaign->is_finished) { ?>
						<div class="message-finished"><?php echo __('The fundraising campaign has been finished. Thank you for your support!', 'leyka');?></div>
					<?php } else { ?>
                        <button type="button" class="leyka-js-open-form">
                            <?php echo leyka_options()->opt('donation_submit_text');?>
                        </button>
					<?php } ?>
                    </div>

                </div>

				<?php if(count($supporters['donations']) > count($supporters['supporters'])) {?>

                <div class="inpage-card__history history">
                    <div class="history__close leyka-js-history-close">x</div>
                    <div class="history__title"><?php _e('We are grateful to', 'leyka');?></div>
                    <div class="history__list">
                        <div class="history__list-flow">
                        <?php foreach(leyka_get_campaign_donations($campaign_id) as $donation) {
                            /** @var $donation Leyka_Donation */?>

                            <div class="history__row">
                                <div class="history__cell h-amount">
                                    <?php echo leyka_format_amount($donation->sum);?>
                                    <span class="curr-mark">
                                        <?php echo leyka_options()->opt("currency_{$target['currency']}_label");?>
                                    </span>
                                </div>
                                <div class="history__cell h-name"><?php echo $donation->donor_name;?></div>
                                <div class="history__cell h-date"><?php echo $donation->date_label;?></div>
                            </div>

                        <?php }?>
                        </div>
                    </div>
                    <?php /** @todo Add normal donations history page template & return this link */
//                echo '<div class="history__action">
//                    <a href="'.leyka_get_donations_archive_url($campaign_id).'">'.__('Show all donors', 'leyka').'</a>
//                </div>';?>
                </div>

				<?php }?>
            </div>

            <div class="leyka-pf__form">
            <?php // Pass the curr. campaign to the template:
                Leyka_Revo_Template_Controller::get_instance()->current_campaign = $campaign;

                require($template_file); /** @todo For the forms caching task comment this require out */
            ?>
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
                        <div class="waiting__card-text"><?php echo apply_filters('leyka_short_gateway_redirect_message', __('Awaiting for the safe payment page redirection...', 'leyka'));?></div>
                    </div>
                </div>
            </div>

            <div class="leyka-pf__oferta oferta">
                <div class="oferta__frame">
                    <div class="oferta__flow">
                        <?php echo apply_filters('leyka_terms_of_service_text', do_shortcode(leyka_options()->opt('terms_of_service_text')));?>
                    </div>
                </div>
                <div class="oferta__action">
                    <a href="#" class="leyka-js-oferta-close">
                        <?php echo leyka_options()->opt('leyka_agree_to_terms_text_text_part').' '.leyka_options()->opt('leyka_agree_to_terms_text_link_part');?>
                    </a>
                </div>
            </div>

        </div><!-- columnt -->
    </div>

    <?php $out = ob_get_contents();
    ob_end_clean();

    return $out;

}

add_shortcode('leyka_inline_campaign_small', 'leyka_inline_campaign_small');
function leyka_inline_campaign_small($campaign_id) {


    $campaign = leyka_get_validated_campaign($campaign_id);
    if( !$campaign || $campaign->is_finished) {
        return '';
    }


    $currency_data = leyka_get_currencies_data(leyka_options()->opt('main_currency'));

    ob_start();?>

    <div data-target="<?php echo leyka_pf_get_form_id($campaign_id);?>" id="leyka-pf-bottom-<?php echo $campaign_id;?>" class="leyka-pf-bottom bottom-form">
        <div class="bottom-form__label"><?php _e('Make a donation', 'leyka');?></div>
        <div class="bottom-form__fields">
            <div class="bottom-form__field">
                <input type="text" value="<?php echo $currency_data['amount_settings']['flexible'];?>" name="leyka_temp_amount">
                <span class="curr-mark"><?php echo $currency_data['label'];?></span>
            </div>
            <div class="bottom-form__button">
                <button type="button" class="leyka-js-open-form-bottom"><?php echo leyka_options()->opt('donation_submit_text');?></button>
            </div>
        </div>

		<?php
			$supporters = leyka_get_campaign_supporters($campaign_id, 5);
			if(count($supporters['supporters'])) { // There is at least one donor ?>

			<div class="bottom-form__note supporters">
			<?php if(count($supporters['supporters'])) { // There is at least one donor ?>
                <strong><?php _e('Supporters:', 'leyka');?></strong>
            <?php }

            if(count($supporters['donations']) <= count($supporters['supporters'])) { // Only names in the list
                echo implode(', ', array_slice($supporters['supporters'], 0, -1))
                    .' '.__('and', 'leyka').' '.end($supporters['supporters']);
            } else { // Names list and the number of the rest of donors

                echo implode(', ', array_slice($supporters['supporters'], 0, -1)).' '.__('and', 'leyka');?>

                <a href="#" class="leyka-js-history-more">
                    <?php echo sprintf(__('%d more', 'leyka'), count($supporters['donations']) - count($supporters['supporters']));?>
                </a>

        <?php }?>
			</div>
		<?php }?>

		<?php if(count($supporters['donations']) > count($supporters['supporters'])) { ?>
        <div class="bottom-form__history history">
            <div class="history__close leyka-js-history-close">x</div>
            <div class="history__title"><?php _e('We are grateful to', 'leyka');?></div>
            <div class="history__list">
                <div class="history__list-flow">

                <?php foreach(leyka_get_campaign_donations($campaign_id) as $donation) {
                    /** @var $donation Leyka_Donation */?>

                    <div class="history__row">
                        <div class="history__cell h-amount">
                            <?php echo leyka_format_amount($donation->sum);?>
                            <span class="curr-mark"><?php echo $currency_data['label'];?></span>
                        </div>
                        <div class="history__cell h-name"><?php echo $donation->donor_name;?></div>
                        <div class="history__cell h-date"><?php echo $donation->date_label;?></div>
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

//add_action('wp_footer', function(){
//
//    global $test;
//    if(empty($test)) {
//        return;
//    }
//    foreach($test as $form_id => $form_html) {
//        echo $form_html;
//    }
//
//}, 100);