<?php if( !defined('WPINC') ) die; // If this file is called directly, abort
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   leyka
 * @author      Lev Zvyagincev aka Ahaenor <ahaenor@gmail.com>
 * @license   GPL-2.0+
 * @link      https://leyka.te-st.ru
 * @copyright 2013-2014 Teplitsa of Social Technology (te-st.ru)
 */

// If uninstall, not called from WordPress, then exit
if( !defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

// Uninstall functionality here...