<?php if( !defined('WP_UNINSTALL_PLUGIN') ) exit; // if uninstall.php is not called by WordPress, die
/**
 * Fired when the plugin is uninstalled.
 */

if(get_option('leyka_delete_plugin_data')) { // Completely remove all campaigns & donations data

    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $leyka_posts_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='leyka_campaign' OR post_type='leyka_donation'");
    foreach($leyka_posts_ids as $id) {
        wp_delete_post($id, true); // All revisions, post metas and comments will also be deleted
    }

    $success_page = get_post(get_option('leyka_success_page'));
    if(mb_stristr($success_page->post_name, 'thank-you-for-your-donation') !== false) {
        wp_delete_post($success_page->ID, true);
    }

    $failure_page = get_post(get_option('leyka_failure_page'));
    if(mb_stristr($failure_page->post_name, 'sorry-donation-failure') !== false) {
        wp_delete_post($failure_page->ID, true);
    }

    $pd_terms_page = get_post(get_option('leyka_pd_terms_page'));
    if(mb_stristr($pd_terms_page->post_name, 'personal-data-usage-terms') !== false) {
        wp_delete_post($pd_terms_page->ID, true);
    }

}

if(get_option('leyka_delete_plugin_options')) {
    foreach(wp_load_alloptions() as $option => $value) {
        if(mb_stristr($option, 'leyka_') !== false) {
            delete_option($option);
        }
    }
}

remove_role('donations_manager');
remove_role('donations_administrator');