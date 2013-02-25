<?php
/*
Plugin Name: Leyka Yandex.Money gateway
Plugin URI: http://leyka.te-st.ru/
Description: Gateway for Leyka donations management system which adds option for receiving donates using Yandex.Money payment service. Can only be used for receiving donations!
Version: 1.0
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

    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        @deactivate_plugins(__FILE__);
    }
}
add_action('plugins_loaded', 'leyka_yandex_money_plugins_loaded');

function leyka_yandex_money_init(){
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
//    add_action('edd_yandex_cc_form', function(){
//    });

    /**
     * Do some validation on our gateway specific fields if needed.
     */
//    add_action('edd_checkout_error_checks', function($checkout_form_data){ 
//    });

    /** Do the gateway's data processing: redirect, saving data in DB, etc. */
    function leyka_yandex_processing($payment_data){
        global $edd_options;
        if(empty($edd_options['yamo_is_physical'])) { // Donations receiver is an NGO

            // Process the payment on our side:
            // Create the record for pending payment
            $payment = edd_insert_payment(array(
                'price' => $payment_data['price'],
                'date' => $payment_data['date'],
                'user_email' => $payment_data['user_email'],
                'purchase_key' => $payment_data['purchase_key'],
                'currency' => $edd_options['currency'],
                'downloads' => $payment_data['downloads'],
                'user_info' => $payment_data['user_info'],
                'cart_details' => $payment_data['cart_details'],
                'status' => $edd_options['leyka_payments_default_status']
            ));

            if($payment) {
                if($payment_data['post_data']['donor_comments']) {
                    $recall = leyka_insert_recall(array(
                        'post_content' => $payment_data['post_data']['donor_comments'],
                        'post_type' => 'leyka_recall',
                        'post_status' => $edd_options['leyka_recalls_default_status'],
                        'post_title' => 'title',
                    ));
                    if($recall) {
                        // Update the title and slug:
                        leyka_update_recall($recall, array(
                            'post_title' => __('Recall', 'leyka').' #'.$recall,
                            'post_name' => __('recall', 'leyka').'-'.$recall,
                        ));
                        // Update recall metadata:
                        update_post_meta($recall, '_leyka_payment_id', $payment);
                    }
                }

                edd_email_purchase_receipt($payment);
                edd_empty_cart();
            } else {
                // if errors are present, send the user back to the purchase page so they can be corrected:
                if(empty($payment_data['single_donate_id']))
                    edd_send_back_to_checkout('?payment-mode='.$payment_data['post_data']['edd-gateway']);
                else
                    leyka_send_back_to_single_donate(
                        $payment_data['single_donate_id'], $payment_data['post_data']['edd-gateway']
                    );
            }

            header('location: https://money.yandex.ru/eshop.xml?scid='.$edd_options['yamo_scid'].'&ShopID='.$edd_options['yamo_shopid'].'&sum='.(int)$payment_data['price'].'&OrderDetails='.urlencode($payment_data['post_data']['donor_comments']));
            flush();
        } else { // Donations receiver is a physical person

            if(empty($edd_options['yamo_id'])) {
                edd_set_error('yamo_id_is_missing', __('Error: donations receiver\'s Yandex.Money account number has not been set. Please, report it to him.', 'leyka-yandex-money'));
            } elseif( !ctype_digit($edd_options['yamo_id']) && !filter_var($edd_options['yamo_id'], FILTER_VALIDATE_EMAIL) ) {
                edd_set_error('yamo_id_is_invalid', __('Error: donations receiver\'s Yandex.Money account number/email is incorrect. Please, report it to him.', 'leyka-yandex-money'));
            } else { // Success, redirect to yandex.money to donate:
                leyka_insert_payment($payment_data); // Process the payment on our side

                header('location: https://money.yandex.ru/direct-payment.xml?sum='.(int)$payment_data['price'].'&receiver='.trim($edd_options['yamo_id']).'&destination='.urlencode($payment_data['post_data']['yandex_comments']));
                flush();
            }

        }
    }
    add_action('edd_gateway_yandex', 'leyka_yandex_processing');
}
add_action('init', 'leyka_yandex_money_init', 1);

function leyka_yandex_money_admin_init(){
    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(__FILE__);
        echo __('<div id="message" class="error"><strong>Error:</strong> base donations plugin is missing or inactive. It is required for Yandex.money gateway module to work. Yandex.money plugin will be deactivated.</div>', 'leyka-yandex-money');
    }

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
                'id' => 'yamo_is_physical',
                'name' => __("I'm a person, not a company", 'leyka-yandex-money'),
                'desc' => __('Check is you are a physical person (not company or foundation). Settings for physical person will be used, and for juristical person if unchecked.', 'leyka-yandex-money'),
                'type' => 'checkbox',
            ),
            array(
                'id' => 'yamo_physical_options_header',
                'name' => '<h5>'.__('Use this settings if you are physical person', 'leyka-yandex-money').'</h5>',
                'type' => 'header',
                'desc' => '',
            ),
            array(
                'id' => 'yamo_id',
                'name' => __('Yandex.Money ID or email', 'leyka-yandex-money'),
                'desc' => __('Enter your Yandex.Money ID or yandex.ru email', 'leyka-yandex-money'),
                'type' => 'text',
                'size' => 'regular'
            ),
            array(
                'id' => 'yamo_juristic_options_header',
                'name' => '<h5>'.__('Use this settings if you are some organization', 'leyka-yandex-money').'</h5>',
                'desc' => '',
                'type' => 'header'
            ),
            array(
                'id' => 'yamo_scid',
                'name' => __('Yandex.Money SCID', 'leyka-yandex-money'),
                'desc' => __('Enter your Yandex.Money shop showcase ID (can be found in your Yandex contract)', 'leyka-yandex-money'),
                'type' => 'text',
                'size' => 'regular'
            ),
            array(
                'id' => 'yamo_shopid',
                'name' => __('Yandex.Money shop ID', 'leyka-yandex-money'),
                'desc' => __('Enter your Yandex.Money shop ID (can be found in your Yandex contract)', 'leyka-yandex-money'),
                'type' => 'text',
                'size' => 'regular'
            ),
            array(
                'id' => 'yandex_desc',
                'name' => __('Yandex.Money gateway description', 'leyka-yandex-money'),
                'desc' => __('Enter Yandex.Money gateway description that will be shown to the donor when this gateway will be selected for use', 'leyka-yandex-money'),
                'type' => 'rich_editor',
                'std' => 'Яндекс.Деньги — доступный и безопасный способ платить за товары и услуги через интернет. Заполнив форму оплаты на нашем сайте, вы будете перенаправлены на сайт Яндекс.Денег, где сможете завершить платеж. Если у вас нет счета в Яндекс.Деньгах, его нужно открыть на <a href="https://money.yandex.ru/">сайте платежной системы</a>.',
            )
        );
        return $options;
    }
    add_filter('edd_settings_gateways', 'leyka_yandex_options');

    /** Add icons option to the icons list. */
    function leyka_yandex_icons($icons){
        $subplugin_url = rtrim(WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)), '/').'/';

        $icons[$subplugin_url.'icons/yamo_s.png'] = __('Yandex money small (81x35 px)', 'leyka-yandex-money');
        $icons[$subplugin_url.'icons/yamo_m.png'] = __('Yandex money medium (118x51 px) (recommended)', 'leyka-yandex-money');
        $icons[$subplugin_url.'icons/yamo_b.png'] = __('Yandex money big (185x80 px)', 'leyka-yandex-money');

        return $icons;
    }
    add_filter('edd_accepted_payment_icons', 'leyka_yandex_icons');
}
add_action('admin_init', 'leyka_yandex_money_admin_init', 1);
