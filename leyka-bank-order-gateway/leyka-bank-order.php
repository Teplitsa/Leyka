<?php
/*
Plugin Name: Leyka Bank Order gateway
Plugin URI: http://leyka.te-st.ru/
Description: Gateway for Leyka donations management system which adds option for donating using bank order or custom  requisites.
Version: 1.2
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
}
add_action('plugins_loaded', 'leyka_bank_order_plugins_loaded');

function leyka_bank_order_admin_init(){
    // Base Leyka isn't defined, deactivate this plugin:
    if( !defined('LEYKA_VERSION') ) {
        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(__FILE__);
        echo __('<div id="message" class="error"><p><strong>Error:</strong> base donations plugin is missing or inactive. It is required for Bank order gateway module to work. Bank Order gateway will be deactivated.</p></div>', 'leyka-bank-order');
    }
}
add_action('admin_init', 'leyka_bank_order_admin_init', 1);

/** Add Quittance payment to the gateways list by filter hook. */
function leyka_bank_order_gateways($options){
    $options['bank_order'] = array(
        'admin_label' => __('Bank order payment', 'leyka-bank-order'),
        'checkout_label' => __('Manual (bank order) payment', 'leyka-bank-order')
    );
    return $options;
}
add_filter('edd_payment_gateways', 'leyka_bank_order_gateways', 5);

/** Bank order specific checkout fields. */
function leyka_bank_order_fields(){?>
    <fieldset class="field-single" id="edd-second-name-wrap">
        <input class="edd-input" type="text" name="edd_second" placeholder="<?php _e('Your second name', 'leyka-bank-order');?>" id="edd-second" value="" />
    </fieldset>

    <fieldset class="field-single" id="edd-last-name-wrap">
        <input class="edd-input" type="text" name="edd_last" placeholder="<?php _e('Your last name', 'leyka-bank-order');?>" id="edd-last" value="" />
    </fieldset>
<?php }
add_action('edd_bank_order_cc_form', 'leyka_bank_order_fields');

/** Do some validation on our gateway specific fields if needed. */
//    add_action('edd_checkout_error_checks', function($checkout_form_data){
//    });

/** Do the gateway's data processing: redirect, saving data in DB, etc. */
function leyka_bank_order_processing($payment_data){
    global $edd_options;

    // Redirect to quittance page to print it out:
    $donation_id = leyka_insert_payment($payment_data); // Process the payment on our side

    if($edd_options['bank_order_document'] == 'file') {
        header('location: '.$edd_options['bank_order_file']); // Send a payment quittance to browser
//                header('location: '.home_url());
        die(); // Just in case
    }

    header('Content-type: text/html; charset=utf-8');

    $html = $edd_options['bank_order_document'] == 'default' ?
        file_get_contents(dirname(__FILE__).'/standard_bank_order.php') :
        $edd_options['bank_order_custom_html'];

    $payer_full_name = trim($payment_data['user_info']['first_name']);
    $last_name = trim($payment_data['user_info']['last_name']); 
    if($last_name) {
        $payer_full_name = $last_name.'&nbsp;'.$payer_full_name;
        $second_name = trim($payment_data['post_data']['edd_second']);
        if($second_name)
            $payer_full_name .= '&nbsp;'.$second_name;
    }

    $edd_options['bank_order_ess_donation_purpose'] = empty($edd_options['bank_order_ess_donation_purpose']) ?
        '' : str_replace('#DONATION_ID#', $donation_id, $edd_options['bank_order_ess_donation_purpose']);
    $payment_purpose = empty($edd_options['bank_order_ess_add_donor_comment']) ?
        $edd_options['bank_order_ess_donation_purpose'] :
        (
            empty($payment_data['post_data']['donor_comments']) ?
                $edd_options['bank_order_ess_donation_purpose'] :
                rtrim($edd_options['bank_order_ess_donation_purpose'], '.').': '
                .mb_strtolower($payment_data['post_data']['donor_comments']) 
        );

    $html = str_replace(array(
            '#RECEIVER_NAME#',
            '#PAYER_NAME#',
            '111111111',
            '#RECEIVER_BANK_NAME#',
            '#SUM#',
            '#PAYMENT_COMMENT#',
        ),
        array(
            $edd_options['bank_order_ess_name'],
            $payer_full_name,
            $edd_options['bank_order_ess_kpp'],
            $edd_options['bank_order_ess_bank_name'],
            $payment_data['price'],
            $payment_purpose
        ),
        $html);
    for($i=0; $i<10; $i++) {
        $digit = isset($edd_options['bank_order_ess_inn']) ?
            $edd_options['bank_order_ess_inn'][$i] : ' ';
        $html = str_replace("#INN_$i#", $digit, $html);
    }
    for($i=0; $i<20; $i++) {
        $digit = isset($edd_options['bank_order_ess_account'][$i]) ?
            $edd_options['bank_order_ess_account'][$i] : ' ';
        $html = str_replace("#ACC_$i#", $digit, $html);
    }
    for($i=0; $i<10; $i++) {
        $digit = isset($edd_options['bank_order_ess_bik'][$i]) ?
            $edd_options['bank_order_ess_bik'][$i] : ' ';
        $html = str_replace("#BIK_$i#", $digit, $html);
    }
    for($i=0; $i<20; $i++) {
        $digit = isset($edd_options['bank_order_ess_corr_account'][$i]) ?
            $edd_options['bank_order_ess_corr_account'][$i] : ' ';
        $html = str_replace("#CORR_$i#", $digit, $html);
    }
    echo $html;
    flush();
    die();
}
add_action('edd_gateway_bank_order', 'leyka_bank_order_processing');

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
    global $edd_options;

    array_push(
        $options,
        array(
            'id' => 'bank_order_settings',
            'name' => '<h4 id="bank_order_settings">'.__('Bank order gateway settings', 'leyka-bank-order').'</h4>',
            'desc' => __('Configure the bank order gateway settings', 'leyka-bank-order'),
            'type' => 'header'
        ),
        array(
            'id' => 'bank_order_document',
            'name' => __('What should be used as bank order blank?', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'radio',
            'options' => array(
                'default' => __('Standard bank order blank', 'leyka-bank-order'),
                'file' => __('Following document file', 'leyka-bank-order'),
                'custom' => __('Manual bank order blank settings', 'leyka-bank-order')
            )
        ),
        array(
            'id' => 'bank_order_file',
            'name' => __('Payment order template file', 'leyka-bank-order'),
            'desc' => __('File will be used as a quittance. No bank essentials values will be replaced.', 'leyka-bank-order'),
            'std' => __('Path to the template file', 'leyka-bank-order'),
            'type' => 'upload'
        ),
        array(
            'id' => 'bank_order_custom_html',
            'name' => __('Bank payment quittance blank (HTML code)', 'leyka-bank-order'),
            'desc' => __('Enter bank payment quittance blank HTML code, please. You can easily get it <a href="http://quittance.ru/form-pd4.php">here</a>.', 'leyka-bank-order'),
            'type' => 'rich_editor',
        ),
        array(
            'id' => 'bank_order_ess_name',
            'name' => __('Payment receiver\'s name', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text',
            'std' => (empty($edd_options['leyka_receiver_is_private'])
                && !empty($edd_options['leyka_receiver_legal_name']) ?
                $edd_options['leyka_receiver_legal_name'] : ''),
        ),
        array(
            'id' => 'bank_order_ess_inn',
            'name' => __('Payment receiver\'s INN number', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text'
        ),
        array(
            'id' => 'bank_order_ess_kpp',
            'name' => __('Payment receiver\'s KPP number', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text',
            'std' => (empty($edd_options['leyka_receiver_is_private'])
                && !empty($edd_options['leyka_receiver_legal_kpp']) ?
                $edd_options['leyka_receiver_legal_kpp'] : ''),
        ),
        array(
            'id' => 'bank_order_ess_account',
            'name' => __('Payment receiver\'s bank account number', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text'
        ),
        array(
            'id' => 'bank_order_ess_bank_name',
            'name' => __('Payment receiver\'s bank name', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text'
        ),
        array(
            'id' => 'bank_order_ess_bik',
            'name' => __('Payment receiver\'s BIK code', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text'
        ),
        array(
            'id' => 'bank_order_ess_corr_account',
            'name' => __('Payment receiver\'s correspondent account number', 'leyka-bank-order'),
            'desc' => '',
            'type' => 'text'
        ),
        array(
            'id' => 'bank_order_ess_donation_purpose',
            'name' => __('Payment purpose text in the bank order', 'leyka-bank-order'),
            'desc' => __('A text for payment purpose field in the bank order. If contains #DONATION_ID#, this placeholder will be replaced with numeric donation ID.', 'leyka-bank-order'),
            'type' => 'text',
            'std' => __('Charity donation №#DONATION_ID#', 'leyka-bank-order'),
        ),
        array(
            'id' => 'bank_order_ess_add_donor_comment',
            'name' => __('Add donor comments to bank order', 'leyka-bank-order'),
            'desc' => __('If checked, the donor comments will be added to bank order blank (in payment purpose field)', 'leyka-bank-order'),
            'type' => 'checkbox'
        ),
        array(
            'id' => 'bank_order_desc',
            'name' => __('Manual bank payment description', 'leyka-bank-order'),
            'desc' => __('Enter your manual payment description that will be shown to the donor when this gateway will be selected for use', 'leyka-bank-order'),
            'type' => 'rich_editor',
            'std' => __('Bank payment essential elements', 'leyka-bank-order'),
        )
    );
    return $options;
}
add_filter('edd_settings_gateways', 'leyka_bank_order_options');

/**
 * Check if nessesary plugin's fields are filled.
 *
 * @todo Once EDD will have an appropriate API for validation of it's settings, all manual WP options manupulations will have to be removed, in favor of correct setting validation in callbacks.
 */
function leyka_bank_order_validate_fields(){
    global $edd_options;

    if( !empty($edd_options['gateways']['bank_order']) && empty($edd_options['bank_order_document']) ) {
        // Direct settings manipulation - turn off bank order gateway:
        $gateways_options = get_option('edd_settings_gateways');
        unset($gateways_options['gateways']['bank_order']);
        update_option('edd_settings_gateways', $gateways_options);
        unset($edd_options['gateways']['bank_order']);
        // Direct settings manipulation END

        add_settings_error('bank_order_document', 'bank-order-missing', __('Error: you should set the bank order quittance source to use bank order gateway.', 'leyka'));
    }

    settings_errors('bank_order_document');
}
add_action('admin_notices', 'leyka_bank_order_validate_fields');

/** Add icons option to the icons list */
function leyka_bank_order_icons($icons){
    $subplugin_url = rtrim(WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)), '/').'/';

    $icons[$subplugin_url.'icons/sber.png'] = __('Sberbank', 'leyka-bank-order');

    return $icons;
}
add_filter('edd_accepted_payment_icons', 'leyka_bank_order_icons');

// Enqueue backend javascript:
function leyka_bank_order_admin_scripts() {
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
add_action('admin_enqueue_scripts', 'leyka_bank_order_admin_scripts');
