<?php
/**
 * @package Leyka
 * @subpackage Global and frontend modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

/** Add RUR currency support */
function leyka_add_rur_support($currencies){
    $currencies['RUR'] = __('Russian rouble (RUR)', 'leyka');
    return $currencies;
}
add_filter('edd_currencies', 'leyka_add_rur_support');

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

function leyka_report_views($labels){
    $labels['customers'] = __('Donors', 'leyka');
    return $labels;
}
add_filter('edd_report_views', 'leyka_report_views');

/** Setup Leyka post types */
function leyka_setup_post_types(){
    // Register donor recall post type:
    $recall_labels = array(
        'name' 				=> _x('User recalls', 'post type general name', 'leyka'),
        'singular_name' 	=> _x('Recall', 'post type singular name', 'leyka'),
        'add_new' 			=> __('Add New', 'leyka'),
        'add_new_item' 		=> __('Add New Recall', 'leyka'),
        'edit_item' 		=> __('Edit Recall', 'leyka'),
        'new_item' 			=> __('New Recall', 'leyka'),
        'all_items' 		=> __('All Recalls', 'leyka'),
        'view_item' 		=> __('View Recall', 'leyka'),
        'search_items' 		=> __('Search Recalls', 'leyka'),
        'not_found' 		=> __('No Recalls found', 'leyka'),
        'not_found_in_trash'=> __('No Recalls found in Trash', 'leyka'),
        'parent_item_colon' => '',
        'menu_name' 		=> __('Recall History', 'leyka')
    );

    $recall_args = array(
        'labels' 			=> apply_filters('leyka_recall_labels', $recall_labels),
        'public' 			=> false,
        'query_var' 		=> false,
        'rewrite' 			=> false,
        'capability_type' 	=> 'post',
        'supports' 			=> array('title'),
        'can_export'		=> false,
        'hierarchical'      => false,
    );
    register_post_type('leyka_recall', $recall_args);
}
add_action('init', 'leyka_setup_post_types', 1);

/** Customization of EDD' shop specific things to the donations concept */
function leyka_frontend_init(){
    if( !empty($_GET['leyka_action']) ) {
        do_action('leyka_'.$_GET['leyka_action'], $_GET);
    }
    if( !empty($_POST['leyka_action']) ) {
        do_action('leyka_'.$_POST['leyka_action'], $_POST);
    }

    remove_action('edd_after_download_content', 'edd_append_purchase_link', 10, 1);
    remove_action('edd_after_download_content', 'edd_show_has_purchased_item_message', 10, 1);

    // Show payment gateways icons with correct hrefs: 
    remove_action('edd_payment_mode_top', 'edd_show_payment_icons');
    remove_action('edd_before_purchase_form', 'edd_show_payment_icons');
    function leyka_show_correct_payment_icons(){
        global $edd_options;

        if(isset($edd_options['accepted_cards'])) {
            echo '<div class="edd-payment-icons">';
            foreach($edd_options['accepted_cards'] as $key => $card) {
                if(edd_string_is_image_url($key)) {
                    echo '<img class="payment-icon" src="'.$key . '"/>';
                } else {
                    echo '<img class="payment-icon" src="'.EDD_PLUGIN_URL.'includes/images/icons/'.strtolower( str_replace(' ', '', $key)).'.png"/>';
                }
            }
            echo '</div>';
        }
    }
    add_action('leyka_payment_mode_top', 'leyka_show_correct_payment_icons');

    /** Sets an error on checkout if no gateways are enabled. */
    function leyka_no_gateway_error(){
        $gateways = edd_get_enabled_payment_gateways();
        if( !$gateways )
            edd_set_error(
                'no_gateways',
                str_replace('%s', LEYKA_PLUGIN_TITLE, __('You must enable a payment gateway to use %s', 'leyka'))
            );
        else
            unset($_SESSION['edd-errors']['no_gateways']);
    }
    remove_action('init', 'edd_no_gateway_error');
    add_action('init', 'leyka_no_gateway_error');

    /** Adds a correct JS for agree to the terms module. */
    function leyka_agree_to_terms_js(){
        global $edd_options;

        if( !empty($edd_options['show_agree_to_terms']) ) {?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('body').on('click', '.edd_terms_links', function(e) {
                    //e.preventDefault();
                    $('#edd_terms').slideToggle();
                    $('.edd_terms_links').toggle();
                    return false;
                });
            });
        </script>
        <?php
        }
    }
    add_action('leyka_payment_mode_top', 'leyka_agree_to_terms_js');
}
add_action('init', 'leyka_frontend_init', 1);

/** Add a permanent "donor comments" field to all gateway payment forms */
function leyka_after_cc_form(){?>
<fieldset>
    <legend><?php _e('Payment additional info', 'leyka'); ?></legend>
    <p>
        <textarea rows="5" cols="10" name="donor_comments" id="leyka-donor-comment" class="edd-input" placeholder="<?php echo __('Type your comments, if needed', 'leyka');?>"></textarea>
        <label class="edd-label leyka-donor-comment-label" for="leyka-donor-comment">
            <?php _e('Your comment', 'leyka');?>
        </label>
        <span id="leyka-comment-symbols-remain">100</span>
    </p>
    <p>
        <label><input type="checkbox" name="leyka_send_donor_email_conf" value="1" checked="1" />&nbsp;<?php echo __('Send me an email confimation for my donation', 'leyka');?></label>
    </p>
</fieldset>
<?php }
add_action('edd_purchase_form_after_cc_form', 'leyka_after_cc_form', 5);

// Remove default address and bank card fields from checkout form:
remove_action('edd_after_cc_fields', 'edd_default_cc_address_fields');
remove_action('edd_cc_form', 'edd_get_cc_form');

/**
 * Process single donate page - add donation form fields
 * (this form handles in "edd_single_donate" action hook).
 */
function leyka_after_download_content($donate_id){
if(edd_item_in_cart($donate_id))
    edd_remove_from_cart(edd_get_item_position_in_cart($donate_id));
?>
<div id="leyka-single-form-wrapper">
    <form id="leyka-single-form" method="post" action="#">
        <?php if(edd_has_variable_prices($donate_id))
        edd_purchase_variable_pricing($donate_id);
    elseif(leyka_is_any_sum_allowed($donate_id)) {
        do_action('leyka_single_donate_pre_free_field', $donate_id);
        do_action('leyka_free_amount_field', $donate_id);
        do_action('leyka_single_donate_post_free_field', $donate_id);
    } else {
        echo apply_filters('leyka_single_static_amount', edd_price($donate_id, FALSE));
    }?>
        <br /><br />
        <?php edd_print_errors();?>
        <span class="">
            <?php do_action('leyka_payment_mode_top');?>
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
        </span>
        <span id="leyka_form_resp"></span>
        <span id="leyka_client_errors"></span>
        <input type="hidden" id="leyka_donate_id" name="donate_id" value="<?php echo $donate_id;?>" />
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka-single-donate-nonce');?>" />
    </form>
</div>
<?php }
add_action('edd_after_download_content', 'leyka_after_download_content', 9);

/** Process the single donate form data */
function leyka_process_single_donation(){
    if(is_admin()) // no need to run on admin
        return;

    // verify the nonce for this action:
    if( !isset($_POST['nonce'])
        || !wp_verify_nonce($_POST['nonce'], 'leyka-single-donate-nonce') )
        return;
    if( !isset($_POST['donate_id']) )
        return;

//    global $edd_options;

    edd_clear_errors();

    // validate the form $_POST data:
    $valid_data = edd_purchase_form_validate_fields();

    // allow themes and plugins to hook to errors:
    do_action('edd_checkout_error_checks', $_POST);

    // check errors:
    $errors = edd_get_errors();
    if($errors && !is_array($errors))
        edd_clear_errors();
    if($errors !== false) {
        // we have errors, send back to single donate page:
        leyka_send_back_to_single_donate((int)$_POST['donate_id'], $valid_data['gateway']);
        exit;
    }

    // check user:
    if(false === $user = edd_get_purchase_form_user($valid_data)) {
        // something went wrong when collecting data, send back to checkout:
        leyka_send_back_to_single_donate((int)$_POST['donate_id'], $valid_data['gateway']);
        exit;
    }

    edd_empty_cart();
    if(edd_has_variable_prices($_POST['donate_id'])) {
        edd_add_to_cart((int)$_POST['donate_id'], array('price_id' => $_POST['edd_options']['price_id']));
    } elseif(leyka_is_any_sum_allowed($_POST['donate_id'])) {
        edd_add_to_cart(
            (int)$_POST['donate_id'],
            array('sum' => (float)$_POST['leyka_free_donate_amount'], 'is_free_sum' => 1)
        );
    } else {
        edd_add_to_cart((int)$_POST['donate_id']);
    }

    // setup user information:
    $user_info = array(
        'id' => $user['user_id'],
        'email' => $user['user_email'],
        'first_name' => $user['user_first'],
        'last_name' => $user['user_last'],
        'discount' => $valid_data['discount']
    );

    // setup donation info:
    $donation_data = array(
        'downloads' => edd_get_cart_contents(),
        'price' => edd_get_cart_amount(),
        'purchase_key' => strtolower(md5(uniqid())), // random key
        'user_email' => $user['user_email'],
        'date' => date('Y-m-d H:i:s'),
        'user_info' => $user_info,
        'post_data' => $_POST,
        'cart_details' => edd_get_cart_content_details(),
        'gateway' => $valid_data['gateway'],
        'single_donate_id' => (int)$_POST['donate_id'],
//        'card_info' => $valid_data['cc_info'],
    );

    // add the user data for hooks:
    $valid_data['user'] = $user;

    // allow themes and plugins to hook before the gateway:
    do_action('edd_checkout_before_gateway', $_POST, $user_info, $valid_data);

    // allow the purchase data to be modified before it is sent to the gateway:
    $donation_data = apply_filters(
        'edd_purchase_data_before_gateway',
        $donation_data,
        $valid_data
    );

    // if the total amount in the cart is 0, send to the manual gateway. This emulates a free amount donation:
    if($donation_data['price'] <= 0) {
        // Price is zero somehow - error, send back to single donate page:
        edd_set_error('zero_price', __('Sorry, the amount of your donation is 0 somehow.', 'leyka'));
        leyka_send_back_to_single_donate((int)$_POST['donate_id'], $valid_data['gateway']);
        exit;
    }

    // used for showing download links to non logged-in users after purchase,
    // and for other plugins that needs purchase data:
    edd_set_purchase_session($donation_data);

    // send info to the gateway for payment processing:
    edd_send_to_gateway($valid_data['gateway'], $donation_data);
}
add_action('edd_single_donate', 'leyka_process_single_donation');

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
    <input type="text" name="leyka_free_donate_amount" id="free_donate_amount_<?php echo $donate_id;?>" value="<?php echo leyka_get_min_free_donation_sum($donate_id);?>" maxlength="30" />&nbsp;<?php echo edd_currency_filter('');?>
    <?php
    }
}
add_action('leyka_free_amount_field', 'leyka_free_amount_field');

/** Process free priced donate amount in the donates list ([downloads] shortcode) */
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

    if( !leyka_is_any_sum_allowed($args['donate_id']) )
        return $purchase_form;

    if(edd_item_in_cart($args['donate_id'])) {
        $button_display   = 'style="display:none;"';
        $checkout_display = '';
    } else {
        $button_display   = '';
        $checkout_display = 'style="display:none;"';
    }

    ob_start();?>
<form id="edd_purchase_<?php echo $args['donate_id']; ?>" class="edd_free_donate_form" method="post" action="#">
    <?php do_action('leyka_free_amount_field', $args['donate_id']);?>

    <div class="edd_purchase_submit_wrapper">
        <input type="submit" class="leyka-free-add-to-cart <?php echo implode(' ', array($args['style'], $args['color'], trim( $args['class'])));?>" name="leyka_donate" value="<?php echo __('Make a donation with this sum', 'leyka');?>" <?php echo $button_display;?> />
        <a href="<?php echo edd_get_checkout_uri();?>" class="<?php echo esc_attr('edd_go_to_checkout');?> <?php echo implode(' ', array( $args['style'], $args['color'], trim($args['class'])));?>" <?php echo $checkout_display;?>><?php echo __('Checkout', 'edd');?></a>

        <span class="edd-cart-ajax-alert">
            <img src="<?php echo esc_url(EDD_PLUGIN_URL.'includes/images/loading.gif');?>" class="edd-cart-ajax" style="display:none;" />
            <span class="edd-cart-added-alert" style="display:none;">&mdash;<?php _e('Item successfully added to your cart.', 'edd');?></span>
        </span>
    </div><!--end .edd_purchase_submit_wrapper-->

    <input type="hidden" class="donate_id" value="<?php echo (int)$args['donate_id'];?>" />
    <input type="hidden" class="action" value="leyka-free-donate-add-to-cart" />

    <?php do_action('edd_purchase_link_end', $args['donate_id']);?>

</form><!--end #edd_purchase_<?php echo esc_attr($args['download_id']);?>-->
<?php
    return apply_filters('leyka_free_donate_form', ob_get_clean(), $args);
}
add_filter('edd_purchase_download_form', 'leyka_donate_payment_form', 10, 2);

function leyka_free_donate_add_to_cart(){
    // verify the nonce for this action:
    if( !isset($_POST['nonce'])
        || !wp_verify_nonce($_POST['nonce'], 'leyka-free-add-to-cart-nonce') )
        return;
    if( !isset($_POST['donate_id']) || empty($_POST['sum']) || $_POST['sum'] <= 0 )
        die(json_encode(array('status' => 'error', 'message' => __('The required parameters are not set', 'leyka'))));

    edd_clear_errors();

    if(edd_item_in_cart($_POST['donate_id']))
        die('incart');
    $options = array('sum' => (float)$_POST['sum'], 'is_free_sum' => 1);
    edd_add_to_cart($_POST['donate_id'], $options);
    die('ok');
}
add_action('wp_ajax_leyka-free-donate-add-to-cart', 'leyka_free_donate_add_to_cart');

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
 * and this params were added only to correct the total cart price calculations by EDD core.
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

/**
 * Extend the cart with the field to quickly add the donation.
 */
function ed_doanates_before_checkout(){?>
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
            <!--        <input type="hidden" id="leyka_free_donate_nonce" value="--><?//=wp_create_nonce('leyka-free-add-to-cart-nonce');?><!--" />-->
            <input type="submit" name="leyka_quick_add_donate_submit" value="<?php echo __('Add to cart', 'edd');?>" />
            <?php }?>
    </form>
</div>
<?php }
add_action('edd_before_checkout_cart', 'ed_doanates_before_checkout');

/**
 * On the empty cart we'll show not only the "empty cart" message, but a field to quick add the donation.
 */
function leyka_empty_cart(){
    do_action('edd_before_checkout_cart');
}
add_action('edd_empty_cart', 'leyka_empty_cart');

/**
 * Include all JS and JS vars needed.
 */
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
}
add_action('wp_enqueue_scripts', 'leyka_scripts', 11);