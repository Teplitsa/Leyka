<?php
/**
 * @package Leyka
 * @subpackage Plugins list page modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Add settings link on plugin page
function leyka_plugin_page_links($links){
    array_unshift(
        $links,
        '<a href="'.admin_url('edit.php?post_type=download&page=edd-settings').'">'.__('Settings').'</a>'
    );
    return $links;
}
add_filter('plugin_action_links_'.LEYKA_PLUGIN_INNER_SHORT_NAME, 'leyka_plugin_page_links');

// Hide original EDD for fool protection reasons
function leyka_plugins_list($wp_plugins_list){
    unset($wp_plugins_list['easy-digital-downloads/easy-digital-downloads.php']);
    return $wp_plugins_list;
}
add_filter('all_plugins', 'leyka_plugins_list');

/** 
 * Disable auto-updates for original EDD, if needed.
 * Mostly to exclude EDD from "plugins-need-to-be-updated" counter and from core updates page.
 */
$latest_edd_version = get_latest_edd_version();
if($latest_edd_version > LATEST_SUPPORTED_EDD_VERSION || $latest_edd_version == (float)EDD_VERSION) {
    function leyka_update_plugins_list($value){
        if( !empty($value->response) )
            unset($value->response['easy-digital-downloads/easy-digital-downloads.php']);
        return $value;
    }
    add_filter('site_transient_update_plugins', 'leyka_update_plugins_list');
}

// Remove EDD upgrade notices:
remove_action('admin_notices', 'edd_show_upgrade_notices');