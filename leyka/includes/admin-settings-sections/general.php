<?php
/**
 * @package Leyka
 * @subpackage Settings -> General tab modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Changes in on Settings->General admin section:
function leyka_general_settings($settings){
    unset(
        $settings['tracking_settings'], $settings['presstrends'],
        /** @todo Check this API options later, maybe they will be usable */
        $settings['api_settings'], $settings['api_allow_user_keys']
    );

    $settings['purchase_page']['name'] = __('Donations checkout page', 'leyka');
    $settings['purchase_page']['desc'] = __('This is the page where users will select the gateway to make their donations', 'leyka');

    $settings['success_page']['desc'] = __('This is the page where users will be redirected after successful donations', 'leyka');

    $settings['failure_page']['name'] = __('This is the page where users will be redirected after failed donations', 'leyka');
    $settings['failure_page']['desc'] = __('Donations failure page', 'leyka');

    array_push(
        $settings,
        array(
            'id' => 'default_status_options',
            'name' => '<strong>'.__('Default status options', 'leyka').'</strong>',
            'desc' => __('Configure the default status options', 'leyka'),
            'type' => 'header'
        ), array(
            'id' => 'leyka_payments_default_status',
            'name' => __('Payments default status', 'leyka'),
            'desc' => __('Deafult status for newly created donation payments', 'leyka'),
            'type' => 'select',
            'options' => array(
                'pending' => __('Pending'),
                'publish' => __('Publish'),
                'failed' => __('Failed', 'edd'),
                'revoked' => __('Revoked', 'edd'),
            )
        ), array(
            'id' => 'leyka_recalls_default_status',
            'name' => __("Donor's recalls default status", 'leyka'),
            'desc' => __('Deafult status for newly created donor recalls', 'leyka'),
            'type' => 'select',
            'options' => array(
                'pending' => __('Pending'),
                'draft' => __('Draft'),
                'publish' => __('Publish')
            )
        ));
    return $settings;
}
add_filter('edd_settings_general', 'leyka_general_settings');