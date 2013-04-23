<?php
/**
 * @package Leyka
 * @subpackage Ajax-called functions
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** Frontend ajax functions: */
// Process ajax request to get gateway specific fields:
function leyka_get_gateway_fields(){
    // Verify the nonce for this action:
    if ( !isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka-single-donate-nonce') )
        return;

    do_action('edd_purchase_form_top');

    if(edd_can_checkout()) {
        if(isset($edd_options['show_register_form']) && !is_user_logged_in() && !isset($_GET['login'])) {?>
        <div id="edd_checkout_login_register">
            <?php do_action('edd_purchase_form_register_fields');?>
        </div>
        <?php } elseif(isset($edd_options['show_register_form']) && !is_user_logged_in() && isset($_GET['login'])) {?>
        <div id="edd_checkout_login_register">
            <?php do_action('edd_purchase_form_login_fields');?>
        </div>
        <?php }
        if(( !isset($_GET['login']) && is_user_logged_in()) || !isset($edd_options['show_register_form'])) {
            do_action('edd_purchase_form_after_user_info');
        }

        do_action('edd_purchase_form_before_cc_form');

        $payment_mode = edd_get_chosen_gateway();

        // load the credit card form and allow gateways to load their own if they wish
        if(has_action('edd_'.$payment_mode.'_cc_form')) {
            do_action('edd_'.$payment_mode.'_cc_form');
        } else {
            do_action('edd_cc_form');
        }

        // Remove the default EDD hidden fields:
        remove_action('edd_purchase_form_after_cc_form', 'edd_checkout_submit', 9999);

        do_action('edd_purchase_form_after_cc_form');?>

    <fieldset id="edd_purchase_submit">
        <p>
            <?php do_action('edd_purchase_form_before_submit', edd_is_checkout());
            if(is_user_logged_in()) {?>
                <input type="hidden" name="edd-user-id" value="<?php echo get_current_user_id();?>"/>
            <?php }?>
            <input type="hidden" name="edd_action" value="single_donate" />
            <input type="hidden" name="edd-gateway" value="<?php echo edd_get_chosen_gateway();?>" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka-single-donate-nonce');?>" />

            <?php echo edd_checkout_button_purchase();?>

            <?php do_action('edd_purchase_form_after_submit');?>
        </p>
    </fieldset>
    <?php } else {
        // can't checkout
        do_action('edd_purchase_form_no_access');
    }

    do_action('edd_purchase_form_bottom');
    die();
}
add_action('wp_ajax_leyka-get-gateway-fields', 'leyka_get_gateway_fields');
add_action('wp_ajax_nopriv_leyka-get-gateway-fields', 'leyka_get_gateway_fields');

// Add to cart a free-sized donation:
function leyka_free_donate_add_to_cart(){
    // verify the nonce for this action:
    if(
        !isset($_POST['nonce'])
        || !wp_verify_nonce($_POST['nonce'], 'leyka-free-add-to-cart-nonce')
    )
        return;
    if( !isset($_POST['donate_id']) || empty($_POST['sum']) || $_POST['sum'] <= 0 )
        die(json_encode(array('status' => 'error', 'message' => __('The required parameters are not set', 'leyka'))));

    edd_clear_errors();

    if(edd_item_in_cart($_POST['donate_id']))
        die('incart');
    edd_add_to_cart($_POST['donate_id'], array('sum' => (float)$_POST['sum'], 'is_free_sum' => 1));
    die('ok');
}
add_action('wp_ajax_leyka-free-donate-add-to-cart', 'leyka_free_donate_add_to_cart');
add_action('wp_ajax_nopriv_leyka-free-donate-add-to-cart', 'leyka_free_donate_add_to_cart');

/** Admin ajax functions. */
// Update recall text and status when saving it while editing:
function leyka_recall_edit(){
    if(empty($_POST['recall_id']) || (int)$_POST['recall_id'] < 0)
        return;
    $_POST['recall_id'] = (int)$_POST['recall_id'];
    if(get_post($_POST['recall_id'])->post_type != 'leyka_recall'
        || !current_user_can('edit_post', $_POST['recall_id'])
        || !wp_verify_nonce($_POST['leyka_nonce'], 'leyka-edit-recall'))
        die( json_encode(array('status' => 'error', 'message' => __('Permissions denied!', 'leyka'))) );

    global $wpdb;
    $_POST['recall_text'] = esc_html(stripslashes($_POST['recall_text']));
    $wpdb->update( // Not using wp_update_post inside the "save_post" action due to endless loops
        $wpdb->posts,
        array(
            'post_content' => $_POST['recall_text'],
            'post_status' => $_POST['recall_status'],
        ),
        array('ID' => $_POST['recall_id'])
    );

    die( json_encode(array(
        'status' => 'ok',
        'data' => array(
            'recall_status_text' => __(ucfirst($_POST['recall_status'])),
            'recall_status' => $_POST['recall_status'],
            'recall_text' => $_POST['recall_text'],
        ),
    )) );
}
add_action('wp_ajax_leyka-recall-edit', 'leyka_recall_edit');

// Update payment status when clicking on "toggle status" switch:
function leyka_toggle_payment_status(){
    if(empty($_POST['payment_id']) || (int)$_POST['payment_id'] < 0)
        return;
    $_POST['payment_id'] = (int)$_POST['payment_id'];
    $payment = get_post($_POST['payment_id']);
    if( $payment->post_type != 'edd_payment'
        || !current_user_can('edit_post', $_POST['payment_id'])
        || !wp_verify_nonce($_POST['leyka_nonce'], 'leyka-toggle-payment-status') )
        die( json_encode(array('status' => 'error', 'message' => __('Permissions denied', 'leyka'))) );

    $_POST['new_status'] = $_POST['new_status'] === 'publish' ? 'publish' : 'pending';
//    global $wpdb;
    // Not using edd_update_payment_status, because it unnessesarily triggers EDD hook that sends email to the donor and Payments Admin:
//    $wpdb->update(
//        $wpdb->posts,
//        array('post_status' => $_POST['new_status']),
//        array('ID' => $_POST['payment_id'])
//    );

    // We mustn't send another email notifications:
    remove_action('edd_update_payment_status', 'edd_trigger_purchase_receipt', 10);

    edd_update_payment_status($_POST['payment_id'], $_POST['new_status']);
    die( json_encode(array(
        'status' => 'ok',
        'payment_status' => $_POST['new_status'],
    )) );

    // Return the notifications sending action:
    add_action('edd_update_payment_status', 'edd_trigger_purchase_receipt', 10, 3);
}
add_action('wp_ajax_leyka-toggle-payment-status', 'leyka_toggle_payment_status');