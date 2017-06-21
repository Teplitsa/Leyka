<?php if( !defined('WPINC') ) die; // If this file is called directly, abort
/**
 * Plugin Name: Leyka
 * Plugin URI:  https://leyka.te-st.ru/
 * Description: The donations management system for your WP site
 * Version:     2.3
 * Author:      Teplitsa of social technologies
 * Author URI:  https://te-st.ru
 * Text Domain: leyka
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Contributors:
	Lev "ahaenor" Zvyagintsev (ahaenor@gmail.com)
	Anna "foralien" Ladoshkina (webdev@foralien.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)
    Marie Borisyonok (pro100mary@gmail.com)

 * License: GPLv2 or later
	Copyright (C) 2012-2017 by Teplitsa of Social Technologies (http://te-st.ru).

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
    define('LEYKA_VERSION', '2.3');
}

// Plugin base file:
if( !defined('LEYKA_PLUGIN_BASE_FILE') ) { // "leyka.php"
    define('LEYKA_PLUGIN_BASE_FILE', basename(__FILE__));
}

// Plugin base directory:
if( !defined('LEYKA_PLUGIN_DIR_NAME') ) { // Most commonly, "leyka"
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
if( !defined('LEYKA_PLUGIN_INNER_SHORT_NAME') ) { // "leyka/leyka.php"
    define('LEYKA_PLUGIN_INNER_SHORT_NAME', plugin_basename(__FILE__));
}

// Plugin support email:
if( !defined('LEYKA_SUPPORT_EMAIL') ) {
    define('LEYKA_SUPPORT_EMAIL', 'support@te-st.ru,sidorenko.a@gmail.com');
}

// Environment checks. If some failed, deactivate the plugin to save WP from possible crushes:
if( !defined('PHP_VERSION') || version_compare(PHP_VERSION, '5.3.0', '<') ) {

    echo '<div id="message" class="error"><p><strong>Внимание:</strong> версия PHP ниже <strong>5.3.0</strong>. Лейка нуждается в PHP хотя бы <strong>версии 5.3.0</strong>, чтобы работать корректно. Плагин будет деактивирован.<br /><br />Пожалуйста, направьте вашему хостинг-провайдеру запрос на повышение версии PHP для этого сайта.</p> <p><strong>Warning:</strong> your PHP version is below <strong>5.3.0</strong>. Leyka needs PHP <strong>v5.3.0</strong> or later to work. Plugin will be deactivated.<br /><br />Please contact your hosting provider to upgrade your PHP version.</p></div>';

    die();

}

require_once(LEYKA_PLUGIN_DIR.'inc/leyka-functions.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-options-controller.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-polylang.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-core.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-gateways-api.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-campaign.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-donation.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-payment-form.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-template-controller.php');
require_once(LEYKA_PLUGIN_DIR.'inc/leyka-ajax.php');
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