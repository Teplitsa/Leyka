<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

/**
 * Plugin Name: Leyka
 * Plugin URI:  https://leyka.org/
 * Description: Donations management system for your WP site. For NGOs and social projects.
 * Version:     3.31.7
 * Author:      Teplitsa of social technologies
 * Author URI:  https://te-st.org
 * Text Domain: leyka
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Contributors:
	Lev "ahaenor" Zvyagintsev (ahaenor@gmail.com)
	Anna "foralien" Ladoshkina (webdev@foralien.com)
    Vyacheslav Oleinik (oleinik.v89@gmail.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)
    Ion Burdianov (burdianov@gmail.com)
    Marie Borisyonok (pro100mary@gmail.com)

 * License: GPLv2 or later
	Copyright (C) 2012-2023 by Teplitsa of Social Technologies (https://te-st.org).

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

// Leyka plugin version:
if( !defined('LEYKA_VERSION') ) {
    define('LEYKA_VERSION', '3.31.7');
}

// Plugin base file:
if( !defined('LEYKA_PLUGIN_BASE_FILE') ) {
    define('LEYKA_PLUGIN_BASE_FILE', basename(__FILE__));
}

// Plugin base directory:
if( !defined('LEYKA_PLUGIN_DIR_NAME') ) {
    define('LEYKA_PLUGIN_DIR_NAME', basename(dirname(__FILE__)));
}

// Plugin URL:
if( !defined('LEYKA_PLUGIN_BASE_URL') ) {
    define('LEYKA_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
}

// Plugin DIR, with trailing slash:
if( !defined('LEYKA_PLUGIN_DIR') ) {
    define('LEYKA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Plugin ID:
if( !defined('LEYKA_PLUGIN_INNER_SHORT_NAME') ) { // 'leyka/leyka.php'
    define('LEYKA_PLUGIN_INNER_SHORT_NAME', plugin_basename(__FILE__));
}

// Plugin support email:
if( !defined('LEYKA_SUPPORT_EMAIL') ) {
    define('LEYKA_SUPPORT_EMAIL', 'help@te-st.org,sidorenko.a@gmail.com');
}

if( !defined('LEYKA_DEBUG') ) {
    define('LEYKA_DEBUG', 'inherit');
}

if( !defined('LEYKA_USAGE_STATS_DEV_SERVER_URL') ) {
    define('LEYKA_USAGE_STATS_DEV_SERVER_URL', 'https://ngo2.ru/leyka-usage-stats/');
}

if( !defined('LEYKA_USAGE_STATS_PROD_SERVER_URL') ) {
    define('LEYKA_USAGE_STATS_PROD_SERVER_URL', 'https://ls.te-st.org/');
}

if(get_locale() === 'ru_RU') {
    load_textdomain('leyka', dirname(realpath(__FILE__)).'/languages/leyka-ru_RU.mo'); // Load the lang. pack included
}
load_plugin_textdomain('leyka', false, basename(dirname(__FILE__)).'/languages/'); // Load the lang. pack by priority

require_once(LEYKA_PLUGIN_DIR.'inc/leyka-tmp-translations.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-functions.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-options-controller.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-updates.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-polylang.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-core.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-gateways-api.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-extensions-api.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-campaign.php');

require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-base.php');
// For old Leyka custom code that uses direct Leyka_Donation class instancing,
// we should require the class code here, right in the beginning:
if(in_array(get_option('leyka_donations_storage_type'), ['sep', 'sep-incompleted'])) {
    require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-separated.php');
} else {
    require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-post.php');
}
require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donations-management.php');
require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donations.php');
require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donations-errors.php');

require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-donor.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-payment-form.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-template-controller.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-ajax.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-shortcodes.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-widgets.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-hooks.php');

if(version_compare( $GLOBALS['wp_version'], '5.8', '>=' )) {
    require_once(LEYKA_PLUGIN_DIR.'editor/leyka-editor.php');
}

// Automatically include all sub-dirs of /leyka/gateways/ :
$gateways_dir = dir(LEYKA_PLUGIN_DIR.'gateways/');
if($gateways_dir) {

    while(false !== ($gateway_id = $gateways_dir->read())) {

        if(in_array($gateway_id, array('.', '..', 'index.php'))) {
            continue;
        }

        $file_addr = LEYKA_PLUGIN_DIR . 'gateways/' . $gateway_id . '/leyka-class-' . $gateway_id;

        if ( file_exists( $file_addr . '-gateway.php' ) ) {
            require_once( $file_addr . '-gateway.php' );
        }

    }

    $gateways_dir->close();

}

// Automatically include all sub-dirs of /leyka/extensions/ :
$extensions_dir = dir(LEYKA_PLUGIN_DIR.'extensions/');
if($extensions_dir) {

    while(false !== ($extension_id = $extensions_dir->read())) {

        if(in_array($extension_id, array('.', '..', 'index.php'))) {
            continue;
        }

        $file_addr = LEYKA_PLUGIN_DIR."extensions/$extension_id/leyka-class-{$extension_id}-extension.php";

        if(file_exists($file_addr)) {
            require_once($file_addr);
        }

    }

    $extensions_dir->close();

}

if(leyka_options()->opt('donor_accounts_available')) {
    require_once(LEYKA_PLUGIN_DIR.'templates/account/template-tags.php');
}

if( !function_exists('leyka_load_plugin_textdomain') ) {
    function leyka_load_plugin_textdomain() {
        load_plugin_textdomain('leyka', false, basename(dirname(__FILE__)).'/languages/');
    }
}
add_action('plugins_loaded', 'leyka_load_plugin_textdomain');

register_activation_hook(__FILE__, ['Leyka', 'activate']); // Activation
register_deactivation_hook(__FILE__, ['Leyka', 'deactivate']); // Deactivate

add_action('init', function(){
    leyka_handle_plugin_update(); // Only if needed
});

leyka(); // All systems, go