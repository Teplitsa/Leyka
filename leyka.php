<?php
/**
 * Plugin Name: Leyka
 * Plugin URI:  http://leyka.te-st.ru/
 * Description: The donations management system for your WP site
 * Version:     2.2.6
 * Author:      Lev Zvyagincev aka Ahaenor
 * Author URI:  ahaenor@gmail.com
 * Text Domain: leyka
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 * Contributors:	
	Anna Ladoshkina aka foralien (webdev@foralien.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)	
	
 * License: GPLv2 or later
	Copyright (C) 2012-2015 by Teplitsa of Social Technologies (http://te-st.ru).

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

// If this file is called directly, abort.
if( !defined('WPINC') ) die;

// Leyka plugin version:
if( !defined('LEYKA_VERSION') )
    define('LEYKA_VERSION', '2.2.6');

// Plugin base file:
if( !defined('LEYKA_PLUGIN_BASE_FILE') ) // "leyka.php"
    define('LEYKA_PLUGIN_BASE_FILE', basename(__FILE__));

// Plugin base directory:
if( !defined('LEYKA_PLUGIN_DIR_NAME') ) // Most commonly, "leyka"
    define('LEYKA_PLUGIN_DIR_NAME', basename(dirname(__FILE__)));

// Plugin URL:
if( !defined('LEYKA_PLUGIN_BASE_URL') )
    define('LEYKA_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));

// Plugin DIR, with trailing slash:
if( !defined('LEYKA_PLUGIN_DIR') )
    define('LEYKA_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Plugin ID:
if( !defined('LEYKA_PLUGIN_INNER_SHORT_NAME') ) // "leyka/leyka.php"
    define('LEYKA_PLUGIN_INNER_SHORT_NAME', plugin_basename(__FILE__));

// Plugin support email:
if( !defined('LEYKA_SUPPORT_EMAIL') )
    define('LEYKA_SUPPORT_EMAIL', 'support@te-st.ru,');

// Environment checks. If some failed, deactivate the plugin to save WP from possible crushes:
if( !defined('PHP_VERSION') || version_compare(PHP_VERSION, '5.3.0', '<') ) {

    echo '<div id="message" class="error"><p><strong>Внимание:</strong> версия PHP ниже <strong>5.3.0</strong>. Лейка нуждается в PHP хотя бы <strong>версии 5.3.0</strong>, чтобы работать корректно. Плагин будет деактивирован.<br /><br />Пожалуйста, направьте вашему хостинг-провайдеру запрос на повышение версии PHP для этого сайта.</p> <p><strong>Warning:</strong> your PHP version is below <strong>5.3.0</strong>. Leyka needs PHP <strong>v5.3.0</strong> or later to work. Plugin will be deactivated.<br /><br />Please contact your hosting provider to upgrade your PHP version.</p></div>';

    die();
}

// Custom activation errors handler:
//function leyka_handle_possible_errors($err_number, $err_str, $err_file, $err_line) {
//
//    if( !(error_reporting() & $err_number) ) { // This error code is not included in error_reporting
//        return;
//    }
//
//    _e("<strong>Warning!<strong> Some programming errors appear while Leyka activation. This is definitely a problem that needs to be reported to the <a href='mailto:support@te-st.ru'>plugin support</a> or its <a href='https://github.com/Teplitsa/Leyka/issues/new/'>Github page</a>. Please, send us the following text:<br /><br />", 'leyka');
//
//    switch($err_number) {
//        case E_USER_ERROR:
//            echo sprintf(__('<p><strong>ERROR %s (%s)</strong></p>: fatal error on line %s in file %s, PHP %s, OS %s.', 'leyka'), $err_number, $err_str, $err_line, $err_file, PHP_VERSION, PHP_OS);
////            exit(1);
//            break;
//
//        case E_USER_WARNING:
//            echo sprintf(__('<p><strong>WARNING %s</strong></p>: %s. Line %s in file %s, PHP %s, OS %s.', 'leyka'), $err_number, $err_str, $err_line, $err_file, PHP_VERSION, PHP_OS);
//            break;
//
//        case E_USER_NOTICE:
//            echo sprintf(__('<p><strong>NOTICE %s</strong></p>: %s. Line %s in file %s, PHP %s, OS %s.', 'leyka'), $err_number, $err_str, $err_line, $err_file, PHP_VERSION, PHP_OS);
//            break;
//
//        default:
//            echo sprintf(__('<p><strong>Unknown error %s</strong></p>: %s. Line %s in file %s, PHP %s, OS %s.', 'leyka'), $err_number, $err_str, $err_line, $err_file, PHP_VERSION, PHP_OS);
//            break;
//    }
//
//    return true; // Don't execute PHP internal error handler
//}
//set_error_handler('leyka_handle_possible_errors', E_ALL);

/** To avoid some strange bug, when WP functions like is_user_logged_in() are suddenly not found: */
if( !function_exists('is_user_logged_in') )
    require_once(ABSPATH.'wp-includes/pluggable.php');

require_once(LEYKA_PLUGIN_DIR.'inc/leyka-functions.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-polylang.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-options-controller.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-core.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-gateways-api.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-campaign.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-donation.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-payment-form.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-shortcodes.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-widgets.php');

/** Automatically include all sub-dirs of /leyka/gateways/ */
$gateways_dir = dir(LEYKA_PLUGIN_DIR.'gateways/');
if( !$gateways_dir ) {
    // ?..
} else {

    while(false !== ($gateway_id = $gateways_dir->read())) {

        $file_addr = LEYKA_PLUGIN_DIR."gateways/$gateway_id/leyka-class-$gateway_id-gateway.php";

        if($gateway_id != '.' && $gateway_id != '..' && file_exists($file_addr)) {
            require_once($file_addr);
        }
    }

    $gateways_dir->close();
}

register_activation_hook(__FILE__, array('Leyka', 'activate')); // Activation
add_action('plugins_loaded', array('Leyka', 'activate')); // Any update needed
register_deactivation_hook(__FILE__, array('Leyka', 'deactivate')); // Deactivate

leyka(); // All systems go

//restore_error_handler(); // Finally, restore errors handler to previous one