<?php
/*
Plugin Name: Leyka
Plugin URI: http://leyka.te-st.ru/
Description: This plugin creates a donations management system on your WP site. This plugin is based on Easy Digital Downloads plugin (by Pippin Williamson).
Version: 1.0
Author: Lev Zvyagincev aka Ahaenor
Author URI: ahaenor@gmail.com
Contributors: 
	Denis Kulandin aka VaultDweller <kulandin_ET_SIGN_te-st.ru>
Text Domain: leyka
Domain Path: languages
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

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Plugin version
if( !defined('LEYKA_VERSION') ) {
    define('LEYKA_VERSION', '0.1');
}
// Plugin URL
if( !defined('LEYKA_PLUGIN_BASE_URL') ) {
    define('LEYKA_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
}
// Plugin DIR
if( !defined('LEYKA_PLUGIN_DIR') ) {
    define('LEYKA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
// Plugin inner name: full dir path + plugin main script filename.
if( !defined('LEYKA_PLUGIN_INNER_NAME') ) {
    define('LEYKA_PLUGIN_INNER_NAME', LEYKA_PLUGIN_DIR.basename(__FILE__));
}
// Plugin inner shortname: plugin dirname + plugin main script filename.
if( !defined('LEYKA_PLUGIN_INNER_SHORT_NAME') ) {
    define('LEYKA_PLUGIN_INNER_SHORT_NAME', plugin_basename(__FILE__));
}

if( !empty($edd_options['test_mode']) ) {
    @error_reporting(E_ALL);
    @ini_set('display_errors', 'stdout');
}

require LEYKA_PLUGIN_DIR.'/includes/locale.php';

if( !defined('EDD_VERSION') ) { // EDD is not active, show error and fall back
    function leyka_edd_not_found(){
        echo __('<div id="message" class="error"><p><strong>Error:</strong> Easy Digital Downloads plugin is missing or inactive. It is required for donates module to work. Base donations plugin will be deactivated.</p></div>', 'leyka');

        if( !function_exists('deactivate_plugins') )
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(LEYKA_PLUGIN_INNER_NAME);
    }
    add_action('admin_notices', 'leyka_edd_not_found');
} else { // EDD is active, load Leyka normally
    // Plugin official name
    if( !defined('LEYKA_PLUGIN_TITLE') ) {
        define('LEYKA_PLUGIN_TITLE', __('Leyka', 'leyka'));
    }

    require LEYKA_PLUGIN_DIR.'/includes/install.php';
    require LEYKA_PLUGIN_DIR.'/includes/post-types.php';
    require LEYKA_PLUGIN_DIR.'/includes/functions.php';
    require LEYKA_PLUGIN_DIR.'/includes/template-tags.php';
    require LEYKA_PLUGIN_DIR.'/includes/shortcodes.php';
    //require LEYKA_PLUGIN_DIR.'/includes/widgets.php';
    require LEYKA_PLUGIN_DIR.'/includes/frontend.php';
    require LEYKA_PLUGIN_DIR.'/includes/frontend-single-donations.php';
    require LEYKA_PLUGIN_DIR.'/includes/ajax.php';
    
    require LEYKA_PLUGIN_DIR.'/includes/admin-global.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-plugins-list-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-donations-history-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-recalls-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-reports-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-donates-list-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-edit-donate-page.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-settings-sections/emails.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-settings-sections/gateways.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-settings-sections/general.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-settings-sections/misc.php';
    require LEYKA_PLUGIN_DIR.'/includes/admin-settings-sections/taxes.php';
}