<?php
/**
 * @package Leyka
 * @subpackage Locale
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** Set localization: */
// Set filter for plugin's languages directory
$plugin_lang_dir = dirname(LEYKA_PLUGIN_INNER_SHORT_NAME).'/languages/';
$plugin_lang_dir = apply_filters('leyka_languages_directory', $plugin_lang_dir);

// Traditional WordPress plugin locale filter
$locale = apply_filters('plugin_locale', get_locale(), 'leyka');
$mofile = sprintf('%1$s-%2$s.mo', 'leyka', $locale);

// Setup paths to current locale file
$mofile_local = $plugin_lang_dir.$mofile;
$mofile_global = WP_LANG_DIR.'/leyka/'.$mofile;

if(file_exists($mofile_global)) {
    // Look in global /wp-content/languages/edd folder
    load_textdomain('leyka', $mofile_global);
} elseif(file_exists(WP_PLUGIN_DIR.'/'.$mofile_local)) {
    // Look in local /wp-content/plugins/easy-digital-donates/languages/ folder
    load_textdomain('leyka', WP_PLUGIN_DIR.'/'.$mofile_local);
} else {
    // Load the default language files
    load_plugin_textdomain('leyka', false, $plugin_lang_dir);
}
/** Localization ended */

//function leyka_load_locale(){
    
//add_action('plugins_loaded', 'leyka_load_locale');