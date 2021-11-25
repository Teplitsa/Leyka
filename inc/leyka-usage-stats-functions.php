<?php if( !defined('WPINC') ) die;

/** Save the basic site data in the plugin stats DB */
function leyka_sync_plugin_stats_option() {

    if( !leyka_options()->opt('plugin_stats_sync_enabled') ) { // Don't try to sync the plugin stats at all
        return true;
    }

    $stats_server_base_url = leyka_options()->opt('plugin_debug_mode') ?
        rtrim(LEYKA_USAGE_STATS_DEV_SERVER_URL, '/') : rtrim(LEYKA_USAGE_STATS_PROD_SERVER_URL, '/');

    $leyka_installation_id = (int)get_option('leyka_installation_id');

    if($leyka_installation_id) { // Update the installation (activate/deactivate)

        require_once LEYKA_PLUGIN_DIR.'bin/sodium-compat.phar';

        if( !function_exists('Sodium\hex2bin') ) {
            return new WP_Error(
                'plugin_stats_sync_error',
                __('Plugin stats sync error: Sodium syphering module is not included', 'leyka')
            );
        }

        $sipher_public_key = get_option('leyka_stats_sipher_public_key');
        $params = [
            'stats_active' => (int)(leyka()->opt('send_plugin_stats') === 'y'),
            'installation_url' => home_url(), // Just in case
            'installation_id' => $leyka_installation_id,
        ];

        if($sipher_public_key) {

            $sipher_public_key = \Sodium\hex2bin($sipher_public_key);

            foreach($params as $key => $value) {

                if($key === 'installation_id') {
                    continue;
                }

                $params[$key] = \Sodium\crypto_box_seal((string)$value, $sipher_public_key);

            }

        }

    } else { // Add new installation
        $params = [
            'stats_active' => true,
            'installation_url' => home_url(),
            'plugin_install_date' => get_option('leyka_plugin_install_date'),
            'collect_stats_from_date' => time(),
            'stats_collection_active' => true,
        ];
    }

    $response = wp_remote_post($stats_server_base_url.'/sync-installation.php', [
        'timeout' => 10, // Max request time in seconds
        'redirection' => 3, // A number of max times for request redirects
        'httpversion' => '1.1',
        'blocking' => true, // True for sync request, false otherwise
        'body' => $params,
        'headers' => [
            'Authorization' =>
                'Basic '.base64_encode(leyka_options()->opt('plugin_debug_mode') ? 'test:testhouse' : 'leyka:kopeyka'),
            'Expect' => '',
        ],
    ]);

    if(is_wp_error($response)) {
        return new WP_Error(
            'init_plugin_stats_error',
            sprintf(__('Error while saving the plugin usage data: %s', 'leyka'), $response->get_error_message())
        );
    } else if(empty($response['response']['code']) || $response['response']['code'] != 200) {

        $error_message = sprintf(
            __('Error while saving the plugin usage data: %s', 'leyka'),
            empty($response['response']['code']) ?
                __("the response code & status data weren't received", 'leyka') :
                sprintf(__('code %d', 'leyka'), $response['response']['code']).(empty($response['response']['message']) ? '' : ' ('.$response['response']['message'].(empty($response['body']) ? '' : ' - '.trim($response['body'], '.')).')')
        );

        return new WP_Error('init_plugin_stats_error', $error_message);

    } else {

        if(empty($response['body'])) {
            return new WP_Error(
                'plugin_stats_not_saved',
                sprintf(__("The plugin stats collection status wasn't saved :( Please send a message about it to the <a href='mailto:%s' target='_blank'>plugin tech support</a>", LEYKA_SUPPORT_EMAIL), 'leyka')
            );
        }

        $response = json_decode($response['body'], true);
        if(
            empty($response['installation_id']) || (int)$response['installation_id'] <= 0
            || ( !$leyka_installation_id && empty($response['public_key']) )
        ) {
            return new WP_Error(
                'plugin_stats_not_saved',
                sprintf(__("The plugin stats collection status wasn't saved :( Please send a message about it to the <a href='mailto:%s' target='_blank'>plugin tech support</a>", LEYKA_SUPPORT_EMAIL), 'leyka')
            );
        }

        return update_option('leyka_installation_id', (int)$response['installation_id'])
            && update_option('leyka_stats_sipher_public_key', $response['public_key']);

    }

}