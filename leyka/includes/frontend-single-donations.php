<?php
/**
 * @package Leyka
 * @subpackage Single donations page code
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * Process single donate page - add donation form fields
 * (this form handles in "edd_single_donate" action hook).
 */
function leyka_after_download_content($donate_id){
    if(edd_item_in_cart($donate_id)) { // Temporarily remove single donation from cart, if needed
        // Save the cart state before user entered the single donate page:
        $cart_data = edd_get_cart_contents();
        edd_empty_cart();
    }

//    if(edd_item_in_cart($donate_id))
//        edd_remove_from_cart(edd_get_item_position_in_cart($donate_id));
    ?>
<div id="leyka-single-form-wrapper">
    <form id="leyka-single-form" method="post" action="#">
    <div class="single-form-head">
        
    <?php if(edd_has_variable_prices($donate_id)): ?>    
        <div class="leyka_variable_amount_ complete amount-form">
        <?php  edd_purchase_variable_pricing($donate_id, TRUE); ?>
        </div>
    <?php elseif(leyka_is_any_sum_allowed($donate_id)):  ?>
        <div class="leyka_free_donate_amount_ complete amount-form">
        <?php   do_action('leyka_single_donate_pre_free_field', $donate_id);
                do_action('leyka_free_amount_field', $donate_id);
                do_action('leyka_single_donate_post_free_field', $donate_id); ?>
        </div>        
    <?php else: ?>
        <div class="leyka_single_static_amount_ complete amount-form">
            <?php echo apply_filters('leyka_single_static_amount', edd_price($donate_id, FALSE)); ?>
        </div>
    <?php endif; ?>
    
    </div>
        <?php edd_print_errors();?>
    <div class="cf">
        <div class="leyka_gateways_ form-triger">
            <div id="leyka_gateways_list" class="gateways-list">
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
        <div id="leyka_form_resp" class="form-wrapper"></div>
        <div id="leyka_client_errors"></div>
        <input type="hidden" id="leyka_donate_id" name="donate_id" value="<?php echo $donate_id;?>" />
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka-single-donate-nonce');?>" />
    </div>
    <div id="donation_submit_message">
        <p><?php _e('You\'ll be redirected to the security payment page.', 'leyka');?></p>
    </div>
    </form>
    
    <div id="leyka-copy">
        <?php  $link = "<a href='http://leyka.te-st.ru'>".__('Leyka', 'leyka')."</a>"; ?>
        <p><?php printf(__('Proudly powered by %s', 'leyka'), $link);?></a></p>
    </div>
</div>
<?php
if( !empty($cart_data) )
    leyka_restore_cart_state($cart_data);
}
add_action('edd_after_download_content', 'leyka_after_download_content', 9);

/** Process the single donate form data */
function leyka_process_single_donation(){
    if(is_admin()) // no need to run on admin
        return;

    // verify the nonce for this action:
    if( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka-single-donate-nonce') )
        return;
    if( !isset($_POST['donate_id']) )
        return;

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