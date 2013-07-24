<?php
/**
 * @package Leyka
 * @subpackage Global and frontend modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * Changing labels.
 * From "downloads", "cart", ... -> "donates", "my donate list", etc.
 * NOTE: This function has been mostly transferred to admin-modifications.php, download labels filtering.
 */
function leyka_default_downloads_name($labels){
    $labels = array(
        'singular' => __('Donate', 'leyka'),
        'plural' => __('Donates', 'leyka')
    );
    return $labels;
}
add_filter('edd_default_downloads_name', 'leyka_default_downloads_name');

function leyka_empty_cart_message(){
    return __('Your "ready to donate" list is empty.', 'leyka');
}
add_filter('edd_empty_cart_message', 'leyka_empty_cart_message');

/** Change "Purchase" button text. */
function leyka_donate_submit_button($html){
    global $edd_options;

    $color = isset($edd_options['checkout_color']) ? $edd_options['checkout_color'] : 'gray';
    $style = isset($edd_options['button_style']) ? $edd_options['button_style'] : 'button';

    $complete_purchase = isset($edd_options['checkout_label']) && strlen(trim($edd_options['checkout_label'])) > 0 ? $edd_options['checkout_label'] : __('Make the donation', 'leyka');

    return '<input type="submit" disabled="disabled" class="edd-submit '.$color.' '.$style.'" id="edd-purchase-button" name="edd-purchase" value="'.$complete_purchase.'"/>';
}
add_filter('edd_checkout_button_purchase', 'leyka_donate_submit_button');

remove_action('edd_after_download_content', 'edd_append_purchase_link', 10, 1);
remove_action('edd_after_download_content', 'edd_show_has_purchased_item_message', 10, 1);

// Show payment gateways icons with correct hrefs: 
remove_action('edd_payment_mode_top', 'edd_show_payment_icons');
remove_action('edd_checkout_form_top', 'edd_show_payment_icons');
function leyka_show_correct_payment_icons(){
    global $edd_options;

    if(isset($edd_options['accepted_cards'])) {
        echo '<div class="edd-payment-icons">';
        foreach($edd_options['accepted_cards'] as $key => $card) {
            if(edd_string_is_image_url($key)) {
                echo '<span class="icon-holder"><img class="payment-icon" src="'.$key.'"/></span>';
            } else {
                echo '<span class="icon-holder"><img class="payment-icon" src="'.EDD_PLUGIN_URL.'includes/images/icons/'.strtolower(str_replace(' ', '', $key)).'.png"/></span>';
            }
        }
        echo '</div>';
    }
}
add_action('leyka_payment_mode_top', 'leyka_show_correct_payment_icons');

/** @todo Will work as gateway select when cart ajax refactoring will start. */
/*function leyka_payment_mode_select(){
    do_action('edd_payment_mode_top'); ?>
    <form id="edd_payment_mode" action="<?php echo edd_get_current_page_url();?>" method="GET">
        <fieldset id="edd_payment_mode_select">
            <?php do_action('edd_payment_mode_before_gateways');?>
            <p id="edd-payment-mode-wrap">
                <div class="leyka_gateways_">
                    <div id="leyka_gateways_list">
                        <?php $gateways = edd_get_enabled_payment_gateways();
                        foreach($gateways as $gateway_id => $gateway) {
                            $content = str_replace('"', "'", leyka_get_gateway_description($gateway_id));?>
                            <div class="gateways_list_entry">
                                <label id="<?php echo $gateway_id;?>-label">
                                    <input name="edd-gateway" type="radio" class="gateway-option" value="<?php echo $gateway_id;?>"
                                        <?php echo (isset($_GET['payment-mode']) && trim($_GET['payment-mode']) == $gateway_id ? 'checked' : '');?> />
                                    &nbsp;<?php echo $gateway['checkout_label'];?>
                                </label>
                                <?php if($content) {?>&nbsp;
                                    <div class="question-icon"
                                         data-placement="right"
                                         data-title="<?php echo $gateway['checkout_label'];?>"
                                         data-content="<?php echo $content;?>"
                                         data-html="true"
                                         data-trigger="hover"></div>
                                <?php }?>
                            </div>
                        <?php }?>
                    </div>
                    <?php do_action('leyka_payment_mode_top');?>
                </div>
            </p>
            <?php do_action('edd_payment_mode_after_gateways'); ?>
        </fieldset>
        <fieldset id="edd_payment_mode_submit" class="edd-no-js">
            <p id="edd-next-submit-wrap">
                <?php echo edd_checkout_button_next(); ?>
            </p>
        </fieldset>
    </form>
    <div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->
    <?php do_action('edd_payment_mode_bottom');
}*/
//remove_action('edd_payment_payment_mode_select', 'edd_payment_mode_select');
//add_action('edd_payment_payment_mode_select', 'leyka_payment_mode_select');

/** Remove EDD's default checkout fields. */
function leyka_default_user_info_fields(){
    if(is_user_logged_in()) {
        $user_data = get_userdata(get_current_user_id());
    }?>

    <div class="fields-title">
        <?php echo apply_filters('edd_checkout_personal_info_text', __('Personal Info', 'edd'));?>
    </div>
    
    <?php do_action('edd_purchase_form_before_email');?>
    
    <fieldset id="edd-email-wrap" class="field-single with-question">    
        <input class="edd-input required" type="email" name="edd_email" placeholder="<?php _e( 'Email address', 'edd');?> *" id="edd-email" value="<?php echo is_user_logged_in() ? $user_data->user_email : '';?>"/>
        
        <div class="question-icon for-inputs"
         data-placement="right"
         data-title="<?php _e('Email address', 'edd');?>"
         data-content="<?php _e('We will send the donation success notice to this address.', 'leyka');?>"
         data-html="true"
         data-trigger="hover"></div>
    </fieldset>
    
    <?php do_action('edd_purchase_form_after_email');?>
    
    <fieldset id="edd-first-name-wrap" class="field-single with-question">    
        <input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e('Your name', 'leyka');?> *" id="edd-first" value="<?php echo is_user_logged_in() ? $user_data->first_name : '';?>" />
        <div class="question-icon for-inputs"
         data-placement="right"
         data-title="<?php _e('Your name', 'leyka');?>"
         data-content="<?php _e('We will use this to personalize your account experience.', 'leyka');?>"
         data-html="true"
         data-trigger="hover"></div>
    </fieldset>
    
    <?php do_action('edd_purchase_form_user_info');?>

<?php }
remove_action('edd_purchase_form_after_user_info', 'edd_user_info_fields');
add_action('edd_purchase_form_after_user_info', 'leyka_default_user_info_fields');

/** Sets an error on checkout if no gateways are enabled. */
function leyka_no_gateway_error(){
    $gateways = edd_get_enabled_payment_gateways();
    if( !$gateways )
        edd_set_error(
            'no_gateways',
            str_replace('%s', LEYKA_PLUGIN_TITLE, __('You must enable a payment gateway to use %s', 'leyka'))
        );
    elseif( !empty($_SESSION['edd-errors']['no_gateways']) )
        unset($_SESSION['edd-errors']['no_gateways']);
}
remove_action('init', 'edd_no_gateway_error');
add_action('init', 'leyka_no_gateway_error');

/** Adds a correct JS for agree to the terms module. */
function leyka_terms_agreement(){
    global $edd_options;
    if( !empty($edd_options['show_agree_to_terms']) ) {?>
        <fieldset id="edd_terms_agreement" class="single-field">
            <div id="edd_terms" style="display:none;">
                <?php do_action('edd_before_terms');
                echo str_replace(array(
                        '#LEGAL_NAME#',
                        '#LEGAL_FACE#',
                        '#LEGAL_FACE_RP#',
                        '#LEGAL_FACE_POSITION#',
                        '#LEGAL_STATE_REG_NUMBER#',
                        '#LEGAL_KPP#',
                        '#LEGAL_ADDRESS#',
                        '#LEGAL_BANK_ESSENTIALS#',
                    ), array(
                        $edd_options['leyka_receiver_legal_name'],
                        $edd_options['leyka_receiver_legal_face'],
                        $edd_options['leyka_receiver_legal_face_rp'],
                        $edd_options['leyka_receiver_legal_face_position'],
                        $edd_options['leyka_receiver_legal_state_reg_number'],
                        $edd_options['leyka_receiver_legal_kpp'],
                        $edd_options['leyka_receiver_legal_address'],
                        $edd_options['leyka_receiver_legal_bank_essentials']
                    ),
                    wpautop($edd_options['agree_text'])
                );
                do_action('edd_after_terms');?>
            </div>
            
            <label id="edd_agree_to_terms_label">
                <input name="edd_agree_to_terms" class="required" type="checkbox" id="edd_agree_to_terms" value="1"/>
                <span>
                    <?php echo sprintf('<a href="#" class="edd_terms_links">'.( !empty($edd_options['agree_label']) ? $edd_options['agree_label'] : __('I agree to the terms of the donation service', 'leyka') ).'</a>');?>
                </span>
            </label>
            
        </fieldset>
    <?php
    }
}
remove_action('edd_purchase_form_after_cc_form', 'edd_terms_agreement', 999);
add_action('edd_purchase_form_after_cc_form', 'leyka_terms_agreement');

function leyka_terms_add_closing_button(){?>
    <div id="edd_show_terms">
        <a href="#" class="edd_terms_links"><i class="icon-close">x</i> <?php _e('Hide Terms', 'edd'); ?></a>
    </div>
<?php }
add_action('edd_before_terms', 'leyka_terms_add_closing_button');

/** Adds a correct JS for agree to the terms module. */
remove_action('edd_checkout_form_top', 'edd_agree_to_terms_js');
function leyka_agree_to_terms_js(){
    global $edd_options;

    if( !empty($edd_options['show_agree_to_terms']) ) {?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('body').on('click', '.edd_terms_links', function(e) {
                e.preventDefault();
                // $('#edd_terms').toggle();
                if($('#edd_terms').hasClass('show')) {
                    $('#edd_terms').removeClass('show').css('top', '-100%');
                } else {
                    $('#edd_terms').addClass('show').css('top', '10%');
                }

                return false;
            });
        });
    </script>
    <?php
    }
}
add_action('edd_checkout_form_top', 'leyka_agree_to_terms_js'); // On checkout form
add_action('leyka_payment_mode_top', 'leyka_agree_to_terms_js'); // On single donates pages

/** Rename the labels in the "final checkout button" fieldset. */
function leyka_checkout_final_total($is_on_checkout = TRUE) {
    if( !$is_on_checkout )
        return;?>
<fieldset id="edd_purchase_final_total">
    <p id="edd_final_total_wrap">
        <strong><?php _e('Total donation:', 'leyka');?></strong>
        <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_amount(false);?>" data-total="<?php echo edd_get_cart_amount(true, true);?>"><?php edd_cart_total();?></span>
    </p>
</fieldset>
<?php
}
remove_action('edd_purchase_form_before_submit', 'edd_checkout_final_total', 999);
add_action('edd_purchase_form_before_submit', 'leyka_checkout_final_total', 999);

//function leyka_frontend_init(){
//}
//add_action('init', 'leyka_frontend_init', 1);

/** Add a permanent "donor comments" field to all gateway payment forms. */
function leyka_after_cc_form(){?>

    <fieldset id="leyka-send-donor-email-wrap" class="field-single">
        <label id="leyka_send_donor_email_">
            <input type="checkbox" name="leyka_send_donor_email_conf" value="1" checked="1" />&nbsp;<span><?php echo __('Send me an email confimation for my donation', 'leyka');?></span>
        </label>
    </fieldset>

    <div class="fields-title"><?php _e('Payment additional info', 'leyka'); ?></div>
    
    <fieldset class="field-single">
        <textarea rows="5" cols="20" name="donor_comments" id="leyka-donor-comment" class="edd-input" placeholder="<?php echo __('Type your comments, if needed', 'leyka');?>"></textarea>
        <div class="field-help">
            <label class="edd-label leyka-donor-comment-label" for="leyka-donor-comment">
             <?php _e('Symbols remain:', 'leyka');?>
            </label>
            <span id="leyka-comment-symbols-remain">100</span>
        </div>
    </fieldset>
    

<?php }
add_action('edd_purchase_form_after_cc_form', 'leyka_after_cc_form', 5);

// Remove default address and bank card fields from checkout form:
remove_action('edd_after_cc_fields', 'edd_default_cc_address_fields');
remove_action('edd_cc_form', 'edd_get_cc_form');

/** Show free donation amount field in the donate form */
function leyka_free_amount_field($donate_id){
    if( !leyka_is_any_sum_allowed($donate_id) )
        return;

    if(edd_item_in_cart($donate_id)) {
        foreach(edd_get_cart_contents() as $item) {
            if($item['id'] == $donate_id) {
                echo edd_currency_filter($item['options']['sum']);
                break;
            }
        }
    } else {?>
    
        <label for="leyka_free_donate_amount"><?php _e('Specify amount of your donation', 'leyka');?></label>
        <input type="text" name="leyka_free_donate_amount" id="free_donate_amount_<?php echo $donate_id;?>" value="<?php echo leyka_get_min_free_donation_sum($donate_id);?>" maxlength="30" />&nbsp;<?php echo edd_currency_filter('');?>
    
    <?php
    }
}
add_action('leyka_free_amount_field', 'leyka_free_amount_field');

function leyka_constant_amount_field($donate_id) {
    if(edd_has_variable_prices($donate_id) || leyka_is_any_sum_allowed($donate_id))
        return;?>
    <div>
    <?php echo edd_currency_filter(edd_get_download_price($donate_id));?>
    </div>
<?php }
add_action('edd_purchase_link_top', 'leyka_constant_amount_field');

/** Process donate mini-forms for all donate types in the donates list ([downloads] shortcode). */
function leyka_donate_payment_form($purchase_form, $args){
    global $edd_options, $post;

    if ( !isset($edd_options['purchase_page']) || $edd_options['purchase_page'] == 0 ) {
        edd_set_error('set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd'), admin_url('edit.php?post_type=download&page=edd-settings')));
        edd_print_errors();
        return false;
    }

    $defaults = array(
        'download_id' => $post->ID,
        'text'        => isset($edd_options['add_to_cart_text']) && $edd_options['add_to_cart_text']  != '' ? $edd_options['add_to_cart_text'] : __('Purchase', 'edd'),
        'style'       => isset($edd_options['button_style'])? $edd_options['button_style'] : 'button',
        'color'       => isset($edd_options[ 'checkout_color']) ? $edd_options['checkout_color'] : 'blue',
        'class'       => 'edd-submit'
    );

    $args = wp_parse_args($args, $defaults);
    $args['donate_id'] = $args['download_id'];
    unset($args['download_id']);
    
    $is_any_sum = leyka_is_any_sum_allowed($args['donate_id']);

    if(edd_item_in_cart($args['donate_id'])) {
        $button_display = 'style="display:none;"';
        $checkout_display = '';
    } else {
        $button_display = '';
        $checkout_display = 'style="display:none;"';
    }

    if( !$is_any_sum ) {
        $variable_pricing = edd_has_variable_prices($args['donate_id']);
        $data_variable = $variable_pricing ? ' data-variable-price=yes' : ' data-variable-price=no';
        $type = edd_single_price_option_mode( $args['donate_id'] ) ? 'data-price-mode=multi' : 'data-price-mode=single';
//        if($args['price'] && !$variable_pricing) {
//            $args['text'] = edd_currency_filter(edd_get_download_price($args['donate_id'])).'&nbsp;&ndash;&nbsp;'.$args['text'];
//        }
    }

    ob_start();?>
<form id="edd_purchase_<?php echo $args['donate_id']; ?>" class="edd_free_donate_form" method="post" action="#">
    <?php if($is_any_sum) { // Donate is free-sized type
        do_action('leyka_free_amount_field', $args['donate_id']);?>

    <div class="edd_purchase_submit_wrapper">
        <input type="submit" class="leyka-free-add-to-cart <?php echo implode(' ', array($args['style'], $args['color'], trim($args['class'])));?>" name="leyka_donate" value="<?php echo __('Make a donation with this sum', 'leyka');?>" <?php echo $button_display;?> />
        <a href="<?php echo edd_get_checkout_uri();?>" class="<?php echo esc_attr('edd_go_to_checkout');?> <?php echo implode(' ', array($args['style'], $args['color'], trim($args['class'])));?>" <?php echo $checkout_display;?>><?php echo __('Checkout', 'edd');?></a>

        <span class="edd-cart-ajax-alert">
            <img src="<?php echo esc_url(EDD_PLUGIN_URL.'assets/images/loading.gif');?>" class="edd-cart-ajax" style="display:none;" />
            <span class="edd-cart-added-alert" style="display:none;">
                <?php printf(
                __('Donation successfully added to your %scart%s.', 'leyka'),
                '<a href="'.esc_url(edd_get_checkout_uri()).'" title="'.__('Go to Checkout','edd').'">',
                '</a>'
            );?>
            </span>
        </span>
    </div><!--end .edd_purchase_submit_wrapper-->
    
    <input type="hidden" class="donate_id" value="<?php echo (int)$args['donate_id'];?>" />
    <input type="hidden" class="action" value="leyka-free-donate-add-to-cart" />
    <?php } else { // Donate is constant- or variable sum type
        do_action('edd_purchase_link_top', $args['donate_id']);?>
    <div class="edd_purchase_submit_wrapper">
        <?php
        printf(
            '<input type="submit" class="edd-add-to-cart %1$s" name="edd_purchase_download" value="%2$s" data-action="edd_add_to_cart" data-download-id="%3$s" %4$s %5$s %6$s/>',
            implode(' ', array($args['style'], $args['color'], trim($args['class']))),
            esc_attr($args['text']),
            esc_attr($args['donate_id']),
            esc_attr($data_variable),
            esc_attr($type),
            $button_display
        );

        printf(
            '<a href="%1$s" class="%2$s %3$s" %4$s>'.__('Checkout', 'edd').'</a>',
            esc_url(edd_get_checkout_uri()),
            esc_attr('edd_go_to_checkout'),
            implode(' ', array($args['style'], $args['color'], trim($args['class']))),
            $checkout_display
        );

        if(edd_is_ajax_enabled()) {?>
    <span class="edd-cart-ajax-alert">
        <img alt="<?php _e('Loading', 'edd');?>" src="<?php echo esc_url(EDD_PLUGIN_URL.'assets/images/loading.gif'); ?>" class="edd-cart-ajax" style="display: none;" />
        <span class="edd-cart-added-alert" style="display: none;">&mdash;
            <?php printf(
                __('Donation successfully added to your %scart%s.', 'leyka'),
                '<a href="'.esc_url(edd_get_checkout_uri()).'" title="'.__('Go to Checkout','edd').'">',
                '</a>'
            );?>
        </span>
    </span>
        <?php }?>
    </div><!--end .edd_purchase_submit_wrapper-->

    <input type="hidden" name="download_id" value="<?php echo esc_attr($args['donate_id']);?>">
    <input type="hidden" name="edd_action" value="add_to_cart">
    <?php }
    do_action('edd_purchase_link_end', $args['donate_id']);?>
</form><!--end #edd_purchase_ID-->
<?php
    return apply_filters('leyka_free_donate_form', ob_get_clean(), $args);
}
add_filter('edd_purchase_download_form', 'leyka_donate_payment_form', 10, 2);

/**
 * Add a tmp price param contains the value donor inserted to the free donate,
 * so EDD could calculate total cart amounts properly.
 * This param will be deleted after the successful checkout. 
 */
function leyka_pre_add_to_cart($donate_id, $options){
    if(leyka_is_any_sum_allowed($donate_id))
        add_post_meta($donate_id, 'edd_price', $options['sum'], true)
            or update_post_meta($donate_id, 'edd_price', $options['sum']);
}
add_action('edd_pre_add_to_cart', 'leyka_pre_add_to_cart', 10, 2);

/**
 * Delete the price params of the free-priced donations in the cart, 'cause they mustn't be there by nature,
 * and this params was added only to correct the total cart price calculations by EDD core.
 * It's done in a filter instead of an action just because there weren't an appropriate actions in there.    
 */
function leyka_before_gateway($donation_data, $valid_data){
    foreach($donation_data['cart_details'] as $donation) {
        if(empty($donation['options']['is_free_sum']))
            continue;
        delete_post_meta($donation['id'], 'edd_price', $donation['options']['sum'], true);
    }

    return $donation_data;
}
add_filter('edd_purchase_data_before_gateway', 'leyka_before_gateway', 10, 2);

/** Extend the cart with the field to quickly add the donation. */
function leyka_before_checkout(){?>
<div id="leyka_quick_add_to_cart_wrapper">
    <form id="leyka_quick_add_to_cart_form" method="post" action="#">
        <?php $donates = get_posts(array('post_type' => 'download', 'post_status' => 'publish'));
        $donates_to_add = array();
        foreach((array)$donates as $donate) {
            if(edd_item_in_cart($donate->ID))
                continue;
            else
                $donates_to_add[] = $donate;
        }

        if($donates_to_add) {?>
            <select name="leyka_quick_add_donate" id="leyka_quick_add_donate">
                <?php foreach($donates_to_add as $donate) {
                if(edd_has_variable_prices($donate->ID)) {
                    $price_options = edd_get_variable_prices($donate->ID);?>
                    <optgroup label="<?php echo $donate->post_title;?>">
                        <?php foreach($price_options as $key => $price) {?>
                        <option value="<?php echo $donate->ID.'_'.$key;?>">
                            <?php echo esc_html($price['name'].' - '.edd_currency_filter($price['amount']));?>
                        </option>
                        <?php }?>
                    </optgroup>
                    <?php } else if(leyka_is_any_sum_allowed($donate->ID)) {?>
                    <option value="<?php echo $donate->ID;?>" class="any-sum" data-min-price="<?php echo leyka_get_min_free_donation_sum($donate->ID);?>"><?php echo $donate->post_title.' - '.__('Any sum', 'leyka');?></option>
                    <?php } else {?>
                    <option value="<?php echo $donate->ID;?>"><?php echo $donate->post_title.' - '.edd_currency_filter(edd_get_download_price($donate->ID));?></option>
                    <?php }?>
                <?php }?>
            </select>
            &nbsp;&nbsp;
            <label id="leyka_quick_free_sum_label">
                <?php echo __('Insert the sum of your donation', 'leyka');?>
                <input type="text" size="4" id="leyka_quick_free_sum" class="edd-input" name="leyka_quick_free_sum" value="" />&nbsp;<?php echo edd_currency_filter('');?>
            </label>
            <input type="submit" name="leyka_quick_add_donate_submit" value="<?php echo __('Add to cart', 'leyka');?>" />
            <?php }?>
    </form>
</div>
<?php }
add_action('edd_before_checkout_cart', 'leyka_before_checkout');

/** Show "quick add" button on the empty cart. */
function leyka_empty_cart(){
//    do_action('edd_before_checkout_cart');
}
add_action('edd_empty_cart', 'leyka_empty_cart');

/** Include all JS and CSS that frontend needed. */
function leyka_scripts(){?>
<script type="text/javascript">
    leyka_single_nonce = '<?php echo wp_create_nonce('leyka-single-donate-nonce');?>';
    leyka_free_nonce = '<?php echo wp_create_nonce('leyka-free-add-to-cart-nonce');?>';
</script>
<?php
    wp_enqueue_script('leyka-frontend-jq-plugins', LEYKA_PLUGIN_BASE_URL.'js/jq-plugins-frontend.js', array('jquery'), LEYKA_VERSION);
    wp_enqueue_script('leyka-frontend', LEYKA_PLUGIN_BASE_URL.'js/leyka-frontend.js', array('jquery', 'leyka-frontend-jq-plugins'), LEYKA_VERSION);
    wp_localize_script('leyka-frontend', 'l10n', array(
        'error_single_donate_free_sum_incorrect' => __('Sorry, the donation amount is incorrect', 'leyka'),
        'error_single_donate_must_agree_to_terms' => __('Sorry, you must agree to the donation terms first', 'leyka'),
    ));

    global $edd_options;

    if( !empty($edd_options['disable_styles']) )
        return;

    //wp_register_style('leyka-styles', LEYKA_PLUGIN_BASE_URL.'styles/style.css');
    wp_enqueue_style('leyka-styles', LEYKA_PLUGIN_BASE_URL.'styles/style-front.css');
}
add_action('wp_enqueue_scripts', 'leyka_scripts', 11);