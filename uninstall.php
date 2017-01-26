<?php if( !defined('WP_UNINSTALL_PLUGIN') ) exit; // if uninstall.php is not called by WordPress, die
/**
 * Fired when the plugin is uninstalled.
 */

if( !get_option('leyka_delete_plugin_options') && !get_option('leyka_delete_plugin_data') ) {
    exit;
}

if(get_option('leyka_delete_plugin_data')) { // Completely remove all campaigns & donations data

    global $wpdb;

    $leyka_posts_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='leyka_campaign' OR post_type='leyka_donation'");
    foreach($leyka_posts_ids as $id) {
        wp_delete_post($id, true); // All revisions, post metas and comments will also be deleted
    }

}

if(get_option('leyka_delete_plugin_options')) {
    foreach(wp_load_alloptions() as $option => $value) {
        if(stristr($option, 'leyka_') !== false) {
            delete_option($option);
        }
    }
}