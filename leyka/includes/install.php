<?php
/**
 * @package Leyka
 * @subpackage Install routines
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

// Activation routine:
function leyka_activation()
{
    // Check if original EDD exists:
    if( !file_exists(WP_PLUGIN_DIR.'/easy-digital-downloads/easy-digital-downloads.php') ) { // Base EDD is not found, fatal
        echo __('<div id="message" class="error"><strong>Original EDD plugin is not found.</strong> Please, try to download and activate it before activating Leyka.</div>', 'leyka');
//        exit;
    }
//    elseif( !is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') ) {
//        activate_plugin(WP_PLUGIN_DIR.'/easy-digital-downloads/easy-digital-downloads.php');
//        if($res !== NULL) { // Can't activate base EDD for some reason, fatal
//            echo __('<div id="message" class="error"><strong>Error while activating the original EDD plugin.</strong> EDD plugin hadn\'t been activated automatically. Please, activate in manually and then activate this plugin again.</div>', 'leyka');
////            exit;
//        }
//    }
}
register_activation_hook(LEYKA_PLUGIN_INNER_SHORT_NAME, 'leyka_activation');

// Deactivation routine:
//register_deactivation_hook(LEYKA_PLUGIN_INNER_SHORT_NAME, function(){
//    if(is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
//        deactivate_plugins(EDD_PLUGIN_FILE);
//    }
//});