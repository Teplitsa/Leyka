<?php

class LeykaDummyDataUtils {

    public static function upload_img_from_path($path) {

        if(!$path || !file_exists($path))
            return false;

        $attachment_id = false;

        $file = file_get_contents($path);

        if($file) {

            $filename = basename($path);
            $upload_file = wp_upload_bits($filename, null, $file);

            if( !$upload_file['error'] ) {

                $wp_filetype = wp_check_filetype($filename, null );

                $attachment_title = preg_replace('/\.[^.]+$/', '', $filename);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_parent' => 0,
                    'post_title' => $attachment_title,
                    'post_name' => 'datt-' . sanitize_title( $attachment_title ),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0 );

                if( !is_wp_error($attachment_id) ) {

                    require_once(ABSPATH . "wp-admin" . '/includes/image.php');

                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                    wp_update_attachment_metadata( $attachment_id,  $attachment_data );

                }

            }

        }

        return $attachment_id;
    }
    
    public static function delete_campaign_donations(Leyka_Campaign $campaign) {

        $donations = $campaign->get_donations();

        foreach($donations as $donation) {
            $donation->delete(True);
        }

    }
    
    public static function reset_default_pages() {

        leyka_get_default_success_page();
        leyka_get_default_failure_page();

    }

    /**
     * Получает значение заданной переменной из ввода и проверяет.
     * Возвращает полученное значение если проверка успешна или переданное, если нет.
     */
    public static function ask_settings_variable_update($default_value) {

        $fd = fopen('php://stdin', 'r');

        $read = [$fd];
        $write = $except = [];

        if(stream_select($read, $write, $except, null)) {

            $result = explode(',', fgets($fd));

            if(sizeof($result) > 1) {

                if(sizeof($result) === sizeof($default_value) && array_sum($result) === 100) {

                    $result_item_index = 0;
                    $new_value = [];

                    foreach($default_value as $default_value_item_index => $default_value_item_data) {

                        $new_value[$default_value_item_index] = $result[$result_item_index];
                        $result_item_index++;

                    }

                    return $new_value;

                } else {
                    return $default_value;
                }

            } else {

                if ( !empty($result[0]) && $result[0] !== PHP_EOL ) {
                    return $result[0];
                } else {
                    return $default_value;
                }

            }

        }
    }

}