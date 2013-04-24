<?php
/*
Plugin Name: Leyka MOBI.Money gateway
Plugin URI: http://leyka.te-st.ru/
Description: Gateway for Leyka donations management system which adds option for receiving donates using MOBI.Money payment service. Can only be used for receiving donations!
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

function leyka_mobi_money_plugins_loaded(){
    // Set filter for plugin's languages directory
    $plugin_lang_dir = dirname(plugin_basename(__FILE__)).'/languages/';
    $plugin_lang_dir = apply_filters('leyka_languages_directory', $plugin_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'leyka-mobi-money');
    $mofile = sprintf('%1$s-%2$s.mo', 'leyka-mobi-money', $locale);

    // Setup paths to current locale file
    $mofile_local = $plugin_lang_dir.$mofile;
    $mofile_global = WP_LANG_DIR.'/leyka-mobi-money/'.$mofile;

    if(file_exists($mofile_global)) {
        // Look in global /wp-content/languages/edd folder
        load_textdomain('leyka-mobi-money', $mofile_global);
    } elseif(file_exists(WP_PLUGIN_DIR.'/'.$mofile_local)) {
        // Look in local /wp-content/plugins/easy-digital-donates/languages/ folder
        load_textdomain('leyka-mobi-money', WP_PLUGIN_DIR.'/'.$mofile_local);
    } else {
        // Load the default language files
        load_plugin_textdomain('leyka-mobi-money', false, $plugin_lang_dir);
    }
}
add_action('plugins_loaded', 'leyka_mobi_money_plugins_loaded');

function leyka_mobi_money_init(){
    /** Add MOBI.Money to the gateways list by filter hook */
    function edd_mobi_money_payment_gateways($options){
        $options['mobi_money'] = array(
            'admin_label' => __('MOBI.Money', 'leyka-mobi-money'),
            'checkout_label' => __('MOBI.Money', 'leyka-mobi-money')
        );
        return $options;
    }
    add_filter('edd_payment_gateways', 'edd_mobi_money_payment_gateways', 5);

    /** MOBI.money checkout form, so user can fill gateway specific fields */
//    add_action('edd_mobi_money_cc_form', function(){
//    });

    /** Do some validation on our gateway specific fields if needed. */
//    add_action('edd_checkout_error_checks', function($checkout_form_data){
//    });

    /** Do the gateway's data processing: redirect, saving data in DB, etc. */
    function leyka_mobi_money_processing($payment_data){
        global $edd_options;

        // Redirect to MOBI.Money to donate:
        leyka_insert_payment($payment_data); // Process the payment on our side
        header('location: https://pay.mobi-money.ru/'.$edd_options['mobi_money_shop_id'].'?sum='.$payment_data['price'].'&payerPhone='.(empty($payment_data['phone']) ? '' : $payment_data['phone']));
        flush();
    }
    add_action('edd_gateway_mobi_money', 'leyka_mobi_money_processing');
}
add_action('init', 'leyka_mobi_money_init', 1);

function leyka_mobi_money_admin_init(){
    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        function leyka_mobi_money_leyka_not_found(){
            echo __('<div id="message" class="error"><p><strong>Error:</strong> base donations plugin is missing or inactive. It is required for MOBI.Money gateway module to work. MOBI.Money plugin will be deactivated.</p></div>', 'leyka-mobi-money');

            if( !function_exists('deactivate_plugins') )
                require_once(ABSPATH.'wp-admin/includes/plugin.php');
            deactivate_plugins(__FILE__);
        }
        add_action('admin_notices', 'leyka_mobi_money_leyka_not_found');
    }
    
    // Add settings link on plugin page:
    function leyka_mobi_money_plugin_page_links($links){
        array_unshift(
            $links,
            '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings&tab=gateways#mobi_money_settings').'">'.__('Settings').'</a>'
        );
        return $links;
    }
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'leyka_mobi_money_plugin_page_links');

    function leyka_mobi_money_options($options){
        array_push(
            $options,
            array(
                'id' => 'mobi_money_settings',
                'name' => '<h4 id="mobi_money_settings">'.__('MOBI.Money Settings', 'leyka-mobi-money').'</h4>',
                'type' => 'header',
                'desc' => '',
            ),
            array(
                'id' => 'mobi_money_shop_id',
                'name' => __('MOBI.Money shop ID<span class="leyka-setting-required">*</span>', 'leyka-mobi-money'),
                'desc' => __('Enter your MOBI.Money shop ID', 'leyka-mobi-money'),
                'type' => 'text',
                'size' => 'regular'
            ),
            array(
                'id' => 'mobi_money_desc',
                'name' => __('MOBI.Money gateway description', 'leyka-mobi-money'),
                'desc' => __('Enter MOBI.Money gateway description that will be shown to the donor when this gateway will be selected for use', 'leyka-mobi-money'),
                'type' => 'rich_editor',
                /** @todo Add eng translation and corresp. localization of this descr. */
                'std' => '<strong><a href="https://www.mobi-money.ru/page/help/index">MOBI.Деньги</a></strong> — современная система электронных платежей, которая позволяет:
<ul>
	<li>оплачивать со счета мобильного телефона или с банковской карты самые разнообразные товары и услуги;</li>
	<li>экономить время: не нужно искать платежные терминалы и стоять в очередях;</li>
	<li>платить так, как вам удобно — с помощью SMS или через интернет.</li>
</ul>
Оплату можно произвести непосредственно с лицевого счета Вашего телефона, либо отправив SMS: {тэг оплаты} {Сумма} {номер}
Номера:
<ul>
	<li>3116 (для оплаты с Билайн),</li>
	<li>3116 (для оплаты с МТС)</li>
</ul>
Вместо значений в фигурных скобках подставьте нужное значение. Сами скобки писать не надо.

Стоимость SMS-сообщения для абонентов МТС определяется тарифным планом, для абонентов других операторов - бесплатно.'
            )
        );
        return $options;
    }
    add_filter('edd_settings_gateways', 'leyka_mobi_money_options');

    /**
     * Check if nessesary plugin's fields are filled.
     * 
     * @todo Once EDD will have an appropriate API for validation of it's settings, all manual WP options manupulations will have to be removed, in favor of correct setting validation in callbacks.  
     */
    function leyka_mobi_money_admin_messages(){
        global $edd_options;

        if( !empty($edd_options['gateways']['mobi_money']) && empty($edd_options['mobi_money_shop_id']) ) {
            // Direct settings manipulation:
            $gateways_options = get_option('edd_settings_gateways');
            unset($gateways_options['gateways']['mobi_money']);
            update_option('edd_settings_gateways', $gateways_options);
            unset($edd_options['gateways']['mobi_money']);
            // Direct settings manipulation END

            add_settings_error('mobi_money_shop_id', 'mobi-money-shop-id-missing', __('Error: MOBI.Money shop ID is required.', 'leyka'));
        }

        settings_errors('mobi_money_shop_id');
    }
    add_action('admin_notices', 'leyka_mobi_money_admin_messages');

    /** Add icons option to the icons list */
    function leyka_mobi_money_icons($icons){
        $subplugin_url = rtrim(WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)), '/').'/';

        $icons[$subplugin_url.'icons/mobi_money.png'] = __('MOBI.Money', 'leyka-mobi-money');

        return $icons;
    }
    add_filter('edd_accepted_payment_icons', 'leyka_mobi_money_icons');
}
add_action('admin_init', 'leyka_mobi_money_admin_init', 1);

// Install routine, if needed:
//register_activation_hook(__FILE__, function(){
//});
