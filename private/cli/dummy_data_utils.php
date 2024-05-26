<?php

class LeykaDummyDataUtils {
    public static function upload_img_from_path($path) {

        if(!$path || !file_exists($path))
            return false;

            $attachment_id = false;
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $file = file_get_contents($path);

            if($file){
                $filename = basename($path);
                $upload_file = wp_upload_bits($filename, null, $file);

                if (!$upload_file['error']) {
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

                    if (!is_wp_error($attachment_id)) {
                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                        wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                    }
                }

            }

            return $attachment_id;
    }
    
    public static function delete_campaign_donations($campaign) {
        $donations = $campaign->get_donations();
        foreach($donations as $donation) {
            $donation->delete(True);
        }
    }
    
    public static function reset_default_pages() {
        leyka_get_default_success_page();
        leyka_get_default_failure_page();
    }
}