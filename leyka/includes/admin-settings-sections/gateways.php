<?php
/**
 * @package Leyka
 * @subpackage Settings -> Gateways tab modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Add icon option to the icons list:
function leyka_icons($icons){
    // Remove default EDD's Visa and Mastercard icons - they don't satisfy Visa & MC logos Terms Of Use:
    unset($icons['visa'], $icons['mastercard'], $icons['paypal']);

    $icons = array_merge( // To add this icons options in the beginning of the list, not in the end
        array(
            // Visa:
            LEYKA_PLUGIN_BASE_URL.'images/icons/visa.png' => __('Visa', 'leyka'),

            // Verified By Visa:
            LEYKA_PLUGIN_BASE_URL.'images/icons/vbv.png' => __('Verified By Visa', 'leyka'),

            // Mastercard:
            LEYKA_PLUGIN_BASE_URL.'images/icons/mc.png' => __('Mastercard', 'leyka'),

            // Mastercard Secure Code:
            LEYKA_PLUGIN_BASE_URL.'images/icons/mc_sc.png' => __('Mastercard Secure Code', 'leyka'),

            // JCB:
            LEYKA_PLUGIN_BASE_URL.'images/icons/jcb.png' => __('JCB', 'leyka'),

            // Paypal (EDD integrated):
            LEYKA_PLUGIN_BASE_URL.'images/icons/paypal.png' => __('PayPal', 'leyka'),
        ),
        $icons
    );

    return $icons;
}
add_filter('edd_accepted_payment_icons', 'leyka_icons');

// Changes in on Settings->Gateways admin section:
function leyka_gateways_options($settings){
    global $edd_options;
    if(empty($edd_options['gateways']['paypal'])) {
        unset(
            $settings['paypal'], $settings['paypal_email'], $settings['paypal_page_style'],
            $settings['paypal_alternate_verification'], $settings['disable_paypal_verification'],
            $settings['default_gateway']
        );
    }

    return $settings;
}
add_filter('edd_settings_gateways', 'leyka_gateways_options');