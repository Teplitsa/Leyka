<?php
/*
Plugin Name: Leyka Yandex.Money gateway
Plugin URI: http://leyka.te-st.ru/
Description: Gateway for Leyka donations management system which adds option for receiving donates using Yandex.Money payment service. Can only be used for receiving donations!
Version: 1.1
Author: Lev Zvyagincev aka Ahaenor
Author URI: ahaenor@gmail.com
License: GPLv2 or later

	Copyright (C) 2012-2013 by Teplitsa of Social Technologies (http://te-st.ru).

	GNU General Public License, Free Software Foundation <http://www.gnu.org/licenses/gpl-2.0.html>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

function leyka_yandex_money_plugins_loaded(){
    // Set filter for plugin's languages directory
    $plugin_lang_dir = dirname(plugin_basename(__FILE__)).'/languages/';
    $plugin_lang_dir = apply_filters('leyka_languages_directory', $plugin_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'leyka-quittance');
    $mofile = sprintf('%1$s-%2$s.mo', 'leyka-yandex-money', $locale);

    // Setup paths to current locale file
    $mofile_local = $plugin_lang_dir.$mofile;
    $mofile_global = WP_LANG_DIR.'/leyka-yandex-money/'.$mofile;

    if(file_exists($mofile_global)) {
        // Look in global /wp-content/languages/edd folder
        load_textdomain('leyka-yandex-money', $mofile_global);
    } elseif(file_exists(WP_PLUGIN_DIR.'/'.$mofile_local)) {
        // Look in local /wp-content/plugins/easy-digital-donates/languages/ folder
        load_textdomain('leyka-yandex-money', WP_PLUGIN_DIR.'/'.$mofile_local);
    } else {
        // Load the default language files
        load_plugin_textdomain('leyka-yandex-money', false, $plugin_lang_dir);
    }
}
add_action('plugins_loaded', 'leyka_yandex_money_plugins_loaded');

function leyka_yandex_money_admin_init(){
    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(__FILE__);
        echo __('<div id="message" class="error"><p><strong>Error:</strong> base donations plugin is missing or inactive. It is required for Yandex.money gateway module to work. Yandex.money plugin will be deactivated.</p></div>', 'leyka-yandex-money');
    }
}
add_action('admin_init', 'leyka_yandex_money_admin_init', 1);

/** Add Yandex.Money to the gateways list by filter hook. */
function leyka_yandex_gateways($options){
    $options['yandex'] = array(
        'admin_label' => __('Yandex.Money', 'leyka-yandex-money'),
        'checkout_label' => __('Yandex.Money', 'leyka-yandex-money')
    );
    return $options;
}
add_filter('edd_payment_gateways', 'leyka_yandex_gateways', 5);

/** Yandex checkout form, so user can fill gateway specific fields. */
//add_action('edd_yandex_cc_form', function(){
//});

/** Do some validation on our gateway specific fields if needed. */
//add_action('edd_checkout_error_checks', function($checkout_form_data){ 
//});

/** Do the gateway's data processing: redirect, saving data in DB, etc. */
function leyka_yandex_processing($payment_data){
    global $edd_options;

    leyka_insert_payment($payment_data); // Process the payment on our side

    if($edd_options['yamo_receiver_is_private'] == 0) { // Donations receiver is an organization
        header('location: https://money.yandex.ru/eshop.xml?scid='.$edd_options['yamo_legal_scid'].'&ShopID='.$edd_options['yamo_legal_shopid'].'&sum='.(int)$payment_data['price'].'&OrderDetails='.urlencode($payment_data['post_data']['donor_comments']));
    } else // Donations receiver is a physical person
        header('location: https://money.yandex.ru/direct-payment.xml?sum='.(int)$payment_data['price'].'&receiver='.trim($edd_options['yamo_private_id']).'&destination='.urlencode($payment_data['post_data']['yandex_comments']));

    flush();
}
add_action('edd_gateway_yandex', 'leyka_yandex_processing');

// Add settings link on plugin page:
function leyka_yandex_plugin_page_links($links){
    array_unshift(
        $links,
        '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#yandex_settings').'">'.__('Settings').'</a>'
    );
    return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'leyka_yandex_plugin_page_links');

/** Add gateway options. */
function leyka_yandex_options($options){
    array_push(
        $options,
        array(
            'id' => 'yamo_settings',
            'name' => '<h4 id="yandex_settings">'.__('Yandex.Money Settings', 'leyka-yandex-money').'</h4>',
            'desc' => __('Configure the Yandex.Money settings', 'leyka-yandex-money'),
            'type' => 'header'
        ),
        array(
            'id' => 'yamo_receiver_is_private',
            'name' => __('Donations receiver', 'leyka'), // We can take this lines from base plugin l10n
            'desc' => '',
            'type' => 'radio',
            'options' => array('1' => __('A private person', 'leyka'), '0' => __('A legal entity', 'leyka'))
        ),
        array(
            'id' => 'yamo_private_id',
            'name' => __('Yandex.Money ID or email', 'leyka-yandex-money'),
            'desc' => __('Enter your Yandex.Money ID or yandex.ru email', 'leyka-yandex-money'),
            'type' => 'text',
            'size' => 'regular'
        ),
        array(
            'id' => 'yamo_legal_scid',
            'name' => __('Yandex.Money SCID', 'leyka-yandex-money'),
            'desc' => __('Enter your Yandex.Money shop showcase ID (can be found in your Yandex contract)', 'leyka-yandex-money'),
            'type' => 'text',
            'size' => 'regular'
        ),
        array(
            'id' => 'yamo_legal_shopid',
            'name' => __('Yandex.Money shop ID', 'leyka-yandex-money'),
            'desc' => __('Enter your Yandex.Money shop ID (can be found in your Yandex contract)', 'leyka-yandex-money'),
            'type' => 'text',
            'size' => 'regular'
        ),
        array(
            'id' => 'yamo_desc',
            'name' => __('Yandex.Money gateway description', 'leyka-yandex-money'),
            'desc' => __('Enter Yandex.Money gateway description that will be shown to the donor when this gateway will be selected for use', 'leyka-yandex-money'),
            'type' => 'rich_editor',
            'std' => 'Яндекс.Деньги — доступный и безопасный способ платить за товары и услуги через интернет. Заполнив форму оплаты на нашем сайте, вы будете перенаправлены на сайт Яндекс.Денег, где сможете завершить платеж. Если у вас нет счета в Яндекс.Деньгах, его нужно открыть на <a href="https://money.yandex.ru/">сайте платежной системы</a>.',
        )
    );
    return $options;
}
add_filter('edd_settings_gateways', 'leyka_yandex_options');

/**
 * Check if nessesary plugin's fields are filled.
 *
 * @todo Once EDD will have an appropriate API for validation of it's settings, all manual WP options manupulations will have to be removed, in favor of correct setting validation in callbacks.
 */
function leyka_yandex_validate_fields(){
    global $edd_options;

    $validation_passed = FALSE;
    if( !isset($edd_options['yamo_receiver_is_private']) ) {
        add_settings_error(
            'yamo_receiver_is_private',
            'yandex-receiver-missing',
            sprintf(
                __('Error: selecting the receiver type is required for Yandex.Money gateway. Please, configure it at %s', 'leyka-yandex-money'),
                '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#yandex_settings').'">'.__('Yandex gateway settings', 'leyka-yandex-money').'</a>'
            )
        );
        settings_errors('yamo_receiver_is_private');
    } else if($edd_options['yamo_receiver_is_private'] == 1 && empty($edd_options['yamo_private_id'])) {
        add_settings_error(
            'yamo_private_id',
            'yandex-id-missing',
            sprintf(
                __('Error: Yandex id/email are required. Please, configure it at %s', 'leyka-yandex-money'),
                '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#yandex_settings').'">'.__('Yandex gateway settings', 'leyka-yandex-money').'</a>'
            )
        );
        settings_errors('yamo_private_id');
    } else if(
        $edd_options['yamo_receiver_is_private'] == 0 &&
        (empty($edd_options['yamo_legal_scid']) || empty($edd_options['yamo_legal_shopid']))
    ) {
        add_settings_error(
            'yamo_legal_scid',
            'yandex-scid-missing',
            sprintf(
                __('Error: both Yandex scid and shop ID are required. Please, configure them at %s', 'leyka-yandex-money'),
            '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#yandex_settings').'">'.__('Yandex gateway settings', 'leyka-yandex-money').'</a>'
            )
        );
        settings_errors('yamo_legal_scid');
    } else
        $validation_passed = TRUE;

    if( !$validation_passed ) {
        // Turn off Yandex gateway. Direct settings manipulation:
        $gateways_options = get_option('edd_settings_gateways');
        if( !empty($gateways_options['gateways']) && !empty($gateways_options['gateways']['yandex']) )
            unset($gateways_options['gateways']['yandex']);
        update_option('edd_settings_gateways', $gateways_options);
        if( !empty($edd_options['gateways']) && !empty($edd_options['gateways']['yandex']) )
            unset($edd_options['gateways']['yandex']);
        // Direct settings manipulation END
    }
}
add_action('admin_notices', 'leyka_yandex_validate_fields');

/** Add icons option to the icons list. */
function leyka_yandex_icons($icons){
    $subplugin_url = rtrim(WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)), '/').'/';

    $icons[$subplugin_url.'icons/yamo.png'] = __('Yandex.Money', 'leyka-yandex-money');

    return $icons;
}
add_filter('edd_accepted_payment_icons', 'leyka_yandex_icons');

// Enqueue backend javascript:
function leyka_yamo_admin_scripts() {
    if(file_exists(dirname(__FILE__).'/scripts/script-admin.js')) {
        if(function_exists('plugins_url')) {
            wp_enqueue_script(
                'leyka-yamo-script-admin',
                plugins_url('/scripts/script-admin.js', __FILE__),
                array('jquery'), '1.0', TRUE
            );
        } else {
            wp_enqueue_script(
                'leyka-yamo-script-admin',
                dirname(__FILE__).'/scripts/script-admin.js',
                array('jquery'), '1.0', TRUE
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'leyka_yamo_admin_scripts');
