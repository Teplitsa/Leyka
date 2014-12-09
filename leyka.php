<?php
/**
 * Plugin Name: Leyka
 * Plugin URI:  http://leyka.te-st.ru/
 * Description: The donations management system for your WP site
 * Version:     2.2.1
 * Author:      Lev Zvyagincev aka Ahaenor
 * Author URI:  ahaenor@gmail.com
 * Text Domain: leyka
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 * Contributors:	
	Anna Ladoshkina aka foralien (webdev@foralien.com)
	Denis Cherniatev (denis.cherniatev@gmail.com)	
	
 * License: GPLv2 or later
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

// If this file is called directly, abort.
if( !defined('WPINC') ) die;

// Leyka plugin version:
if( !defined('LEYKA_VERSION') )
    define('LEYKA_VERSION', '2.2.1');

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
if( !defined('LEYKA_PLUGIN_INNER_SHORT_NAME') )
    define('LEYKA_PLUGIN_INNER_SHORT_NAME', plugin_basename(__FILE__));

// Environment checks. If some failed, deactivate the plugin to save WP from possible crushes:
if( !defined('PHP_VERSION') || version_compare(PHP_VERSION, '5.3.0', '<') ) {

    echo __('<div id="message" class="error"><p><strong>Ошибка:</strong> версия PHP ниже <strong>5.3.0</strong>. Лейка нуждается в PHP хотя бы <strong>версии 5.3.0</strong>, чтобы работать корректно. Плагин будет деактивирован.<br /><br />Пожалуйста, направьте вашему хостинг-провайдеру запрос на повышение версии PHP для этого сайта.</p> <p><strong>Error:</strong> your PHP version is below <strong>5.3.0</strong>. Leyka needs PHP <strong>v5.3.0</strong> or later to work. Plugin will be deactivated.<br /><br />Please contact your hosting provider to upgrade your PHP version.</p></div>', 'leyka');

    die();
}

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

        if($gateway_id != '.' && $gateway_id != '..' && file_exists($file_addr))
			require_once($file_addr);
    }

    $gateways_dir->close();
}

// Activation/Deactivation:
register_activation_hook(__FILE__, array('Leyka', 'activate'));
register_deactivation_hook(__FILE__, array('Leyka', 'deactivate'));

leyka(); // Init