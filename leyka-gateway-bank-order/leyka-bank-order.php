<?php
/*
Plugin Name: Leyka Bank Order gateway
Plugin URI: http://leyka.te-st.ru/
Description: Gateway for Leyka donations management system which adds option for donating using bank order or custom  requisites.
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

function leyka_bank_order_plugins_loaded()
{
    // Set filter for plugin's languages directory
    $plugin_lang_dir = dirname(plugin_basename(__FILE__)).'/languages/';
    $plugin_lang_dir = apply_filters('leyka_languages_directory', $plugin_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'leyka-bank-order');
    $mofile = sprintf('%1$s-%2$s.mo', 'leyka-bank-order', $locale);

    // Setup paths to current locale file
    $mofile_local = $plugin_lang_dir.$mofile;
    $mofile_global = WP_LANG_DIR.'/leyka-bank-order/'.$mofile;

    if(file_exists($mofile_global)) {
        // Look in global /wp-content/languages/edd folder
        load_textdomain('leyka-bank-order', $mofile_global);
    } elseif(file_exists(WP_PLUGIN_DIR.'/'.$mofile_local)) {
        // Look in local /wp-content/plugins/easy-digital-donates/languages/ folder
        load_textdomain('leyka-bank-order', WP_PLUGIN_DIR.'/'.$mofile_local);
    } else {
        // Load the default language files
        load_plugin_textdomain('leyka-bank-order', false, $plugin_lang_dir);
    }

    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        @deactivate_plugins(__FILE__);
    }
}
add_action('plugins_loaded', 'leyka_bank_order_plugins_loaded');

function leyka_bank_order_init(){
    /** Add Quittance payment to the gateways list by filter hook */
    function leyka_bank_order_gateways($options){
        $options['bank_order'] = array(
            'admin_label' => __('Bank order payment', 'leyka-bank-order'),
            'checkout_label' => __('Manual (bank order) payment', 'leyka-bank-order')
        );
        return $options;
    }
    add_filter('edd_payment_gateways', 'leyka_bank_order_gateways', 5);

    /**
     * Quittance checkout form - not needed.
     */
//    add_action('edd_bank_order_cc_form', function(){
//    });

    /**
     * Do some validation on our gateway specific fields if needed.
     */
//    add_action('edd_checkout_error_checks', function($checkout_form_data){
//        
//    });

    /** Do the gateway's data processing: redirect, saving data in DB, etc. */
    function leyka_bank_order_processing($payment_data){
        global $edd_options;

        if(
            (
                empty($edd_options['bank_order_use_file']) &&
                empty($edd_options['bank_order_html']) &&
                empty($edd_options['bank_order_html_default'])
            )
            || ($edd_options['bank_order_use_file'] && empty($edd_options['bank_order_file']))
        ) {
            edd_set_error('bank_order_is_missing', __('the quittance blank for bank payment has not been set. Please, report it to developer of this site.', 'leyka-bank-order'));
        } else { // Success, redirect to quittance page to print it out:
            leyka_insert_payment($payment_data); // Process the payment on our side

            if($edd_options['bank_order_use_file']) {
                header('location: '.$edd_options['bank_order_file']); // Send a payment quittance to browser
//                header('location: '.home_url());
                die(); // Just in case
            }

            header('Content-type: text/html; charset=utf-8');

            $html = $edd_options['bank_order_html_default'] ?
                file_get_contents(dirname(__FILE__).'/standard_bank_order.php') :
                $edd_options['bank_order_html'];

            $html = str_replace(array(
                    '#RECEIVER_NAME#',
                    '111111111',
                    '#RECEIVER_BANK_NAME#',
                    '#SUM#',
                    '#PAYMENT_COMMENT#',
                ),
                array(
                    $edd_options['bank_order_receiver_name'],
                    $edd_options['bank_order_receiver_kpp'],
                    $edd_options['bank_order_receiver_bank_name'],
                    $payment_data['price'],
                    $payment_data['post_data']['donor_comments'],
                ),
                $html);
            for($i=0; $i<10; $i++) {
                $digit = isset($edd_options['bank_order_receiver_inn']) ?
                    $edd_options['bank_order_receiver_inn'][$i] : ' ';
                $html = str_replace("#INN_$i#", $digit, $html);
            }
            for($i=0; $i<20; $i++) {
                $digit = isset($edd_options['bank_order_receiver_account'][$i]) ?
                    $edd_options['bank_order_receiver_account'][$i] : ' ';
                $html = str_replace("#ACC_$i#", $digit, $html);
            }
            for($i=0; $i<10; $i++) {
                $digit = isset($edd_options['bank_order_receiver_bik'][$i]) ?
                    $edd_options['bank_order_receiver_bik'][$i] : ' ';
                $html = str_replace("#BIK_$i#", $digit, $html);
            }
            for($i=0; $i<20; $i++) {
                $digit = isset($edd_options['bank_order_receiver_corr_account'][$i]) ?
                    $edd_options['bank_order_receiver_corr_account'][$i] : ' ';
                $html = str_replace("#CORR_$i#", $digit, $html);
            }
            echo $html;
            flush();
            die();
        }
    }
    add_action('edd_gateway_bank_order', 'leyka_bank_order_processing');
}
add_action('init', 'leyka_bank_order_init', 1);

function leyka_bank_order_admin_init(){
    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(__FILE__);
        echo __('<div id="message" class="error"><strong>Error:</strong> base donations plugin is missing or inactive. It is required for Bank order gateway module to work. Bank Order gateway will be deactivated.</div>', 'leyka-bank-order');
    }

    // Add settings link on plugin page:
    function leyka_bank_order_plugin_page_links($links){
        array_unshift(
            $links,
            '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#bank_order_settings').'">'.__('Settings').'</a>'
        );
        return $links;
    }
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'leyka_bank_order_plugin_page_links');

    function leyka_bank_order_options($options){
        array_push(
            $options,
            array(
                'id' => 'bank_order_settings',
                'name' => '<h4 id="bank_order_settings">'.__('Bank order gateway settings', 'leyka-bank-order').'</h4>',
                'desc' => __('Configure the bank order gateway settings', 'leyka-bank-order'),
                'type' => 'header'
            ),
            array(
                'id' => 'bank_order_receiver_name',
                'name' => __('Payment receiver\'s name', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_inn',
                'name' => __('Payment receiver\'s INN number', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_kpp',
                'name' => __('Payment receiver\'s KPP number', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_account',
                'name' => __('Payment receiver\'s bank account number', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_bank_name',
                'name' => __('Payment receiver\'s bank name', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_bik',
                'name' => __('Payment receiver\'s BIK code', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_receiver_corr_account',
                'name' => __('Payment receiver\'s correspondent account number', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'text'
            ),
            array(
                'id' => 'bank_order_html',
                'name' => __('Bank payment quittance blank (HTML code)', 'leyka-bank-order'),
                'desc' => __('Enter bank payment quittance blank HTML code, please. You can easily get it <a href="http://quittance.ru/form-pd4.php">here</a>.', 'leyka-bank-order'),
                'type' => 'rich_editor',
            ),
            array(
                'id' => 'bank_order_html_default',
                'name' => __('Use standard quittance blank instead of HTML code above', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'checkbox'
            ),
            array(
                'id' => 'bank_order_use_file',
                'name' => __('Use the following document file instead of configurations above', 'leyka-bank-order'),
                'desc' => '',
                'type' => 'checkbox'
            ),
            array(
                'id' => 'bank_order_file',
                'name' => __('Payment order template file', 'leyka-bank-order'),
                'desc' => __("File will be used as a quittance. No requisites' values will be replaced.", 'leyka-bank-order'),
                'std' => __('Path to the template file', 'leyka-bank-order'),
                'type' => 'upload'
            ),
            array(
                'id' => 'bank_order_desc',
                'name' => __('Manual bank payment description', 'leyka-bank-order'),
                'desc' => __('Enter your manual payment description that will be shown to the donor when this gateway will be selected for use', 'leyka-bank-order'),
                'type' => 'rich_editor',
            )
        );
        return $options;
    }
    add_filter('edd_settings_gateways', 'leyka_bank_order_options');

    /** Add icons option to the icons list */
    function leyka_bank_order_icons($icons){
        $subplugin_url = rtrim(WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)), '/').'/';

        $icons[$subplugin_url.'icons/sber_s.png'] = __('Sberbank small (169x35 px)', 'leyka-bank-order');
        $icons[$subplugin_url.'icons/sber_m.png'] = __('Sberbank medium (246x51 px) (recommended)', 'leyka-bank-order');
        $icons[$subplugin_url.'icons/sber_b.png'] = __('Sberbank big (386x80 px)', 'leyka-bank-order');

        return $icons;
    }
    add_filter('edd_accepted_payment_icons', 'leyka_bank_order_icons');

    // Enqueue backend javascript:
    if(file_exists(dirname(__FILE__).'/scripts/script-admin.js')) {
        if(function_exists('plugins_url')) {
            wp_enqueue_script(
                'leyka-bank-order-script-admin',
                plugins_url('/scripts/script-admin.js', __FILE__),
                array('jquery'), '1.0', TRUE
            );
        } else {
            wp_enqueue_script(
                'leyka-bank-order-script-admin',
                dirname(__FILE__).'/scripts/script-admin.js',
                array('jquery'), '1.0', TRUE
            );
        }
    }
}
add_action('admin_init', 'leyka_bank_order_admin_init', 1);