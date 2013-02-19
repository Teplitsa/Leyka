<?php
/**
 * @package Leyka
 * @subpackage Service
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

$wp_config_dir = dirname(__FILE__);
do {
    $wp_config_dir = realpath("$wp_config_dir/..");
} while( !file_exists("$wp_config_dir/wp-config.php") );

require_once("$wp_config_dir/wp-config.php");

// no need to run on admin
if (is_admin())
    return;

// verify the nonce for this action
if ( !isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka-single-donate-nonce') )
    return;

do_action('edd_purchase_form_top');

if(edd_can_checkout()) {?>

<?php if(isset($edd_options['show_register_form']) && !is_user_logged_in() && !isset($_GET['login'])) {?>
    <div id="edd_checkout_login_register"><?php do_action('edd_purchase_form_register_fields');?></div>
    <?php } elseif(isset($edd_options['show_register_form']) && !is_user_logged_in() && isset($_GET['login'])) {?>
    <div id="edd_checkout_login_register"><?php do_action('edd_purchase_form_login_fields');?></div>
    <?php }?>

<?php if(( !isset($_GET['login']) && is_user_logged_in()) || !isset($edd_options['show_register_form'])) {
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
    remove_action('edd_purchase_form_after_cc_form', 'edd_checkout_submit', 100);

    do_action('edd_purchase_form_after_cc_form');?>

    <fieldset id="edd_purchase_submit">
        <p>
            <?php do_action('edd_purchase_form_before_submit');?>
    
            <?php if(is_user_logged_in()) {?>
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