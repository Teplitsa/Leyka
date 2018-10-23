<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

global $wpdb;

try {

    include('cli_common.php');

    $time_start = microtime(true);

	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));

    Leyka_Procedure_Tmp::insert_data_to_prototype();

	fwrite(STDOUT, "All done\n\n");
	fwrite(STDOUT, 'Memory '.memory_get_usage(true).chr(10));
	fwrite(STDOUT, 'Total execution time in seconds: ' . (microtime(true) - $time_start).chr(10).chr(10));

}
catch (TstNotCLIRunException $ex) {
	echo $ex->getMessage() . "\n";
}
catch (TstCLIHostNotSetException $ex) {
	echo $ex->getMessage() . "\n";
}
catch (Exception $ex) {
	echo $ex;
}

class Leyka_Procedure_Tmp {

//    public static function transfer_campaign($old_campaign_id) {
//
//        global $wpdb;
//
//        $old_campaign = $wpdb->get_row("select * from {$wpdb->prefix}posts where ID=".$old_campaign_id, ARRAY_A);
//        unset($old_campaign['ID']);
//        $old_campaign['post_title'] = $wpdb->esc_like($old_campaign['post_title']);
//
//        $campaign_exists = $wpdb->get_var("select ID from milodev_posts where guid = '{$old_campaign['guid']}'");
//        if($campaign_exists) {
//            echo '<pre>Campaign exists: '.print_r($campaign_exists, 1).'</pre>'."\n";
//            $new_campaign_id = $campaign_exists;
//        } else {
//
//            $new_campaign_id = $wpdb->insert('milodev_posts', $old_campaign);
//            if( !$new_campaign_id ) {
//                echo '<pre>ERROR: CAMPAIGN WAS NOT INSERTED - '.print_r($old_campaign_id, 1).'</pre>'."\n";
//                return false;
//            } else {
//                echo '<pre>'.print_r('Campaign inserted: '.$old_campaign_id.'. New ID: '.$wpdb->insert_id, 1).'</pre>'."\n";
//            }
//
//        }
//
//        return $new_campaign_id;
//
//    }

//    public static function transfer_donation($old_donation_id) {
//
//        global $wpdb;
//
//        $old_donation = $wpdb->get_row("select * from {$wpdb->prefix}posts where ID=".$old_donation_id, ARRAY_A);
//        unset($old_donation['ID']);
//        $old_donation['post_title'] = $wpdb->esc_like($old_donation['post_title']);
//
//        // Entering to the Prototype DB:
//        $donation_exists = $wpdb->get_var("select ID from milodev_posts where guid='{$old_donation['guid']}' AND post_date='{$old_donation['post_date']}'");
//        if($donation_exists) {
//            echo '<pre>Donation exists: '.print_r($donation_exists, 1).'</pre>'."\n";
//            $new_donation_id = $donation_exists;
//        } else {
//
//            $new_donation_id = $wpdb->insert('milodev_posts', $old_donation);
//            if( !$new_donation_id ) {
//                echo '<pre>ERROR: DONATION WAS NOT INSERTED - '.print_r($old_donation_id, 1).'</pre>'."\n";
//                return false;
//            } else {
//                echo '<pre>'.print_r('Donation inserted: '.$old_donation_id.'. New ID: '.$wpdb->insert_id, 1).'</pre>'."\n";
//            }
//
//        }
//
//        return $new_donation_id;
//
//    }

    public static function transfer_post_meta($old_meta, $new_post_id) {

        global $wpdb;

        $new_meta_id = $wpdb->get_var("select meta_id from milodev_postmeta where post_id=$new_post_id and meta_key='{$old_meta['meta_key']}' and meta_value='{$old_meta['meta_value']}'");
        if( !$new_meta_id ) {

            $new_meta = $old_meta;
            unset($new_meta['meta_id']);
            $new_meta['post_id'] = $new_post_id;

            $new_meta_id = $wpdb->insert('milodev_postmeta', $new_meta);
            if( !$new_meta_id ) {
                echo '<pre>ERROR: POST META WAS NOT INSERTED - '.print_r($old_meta['meta_id'], 1).'</pre>'."\n";
                return false;
            } else {
                echo '<pre>'.print_r('Post meta inserted: '.$old_meta['meta_id'].'. New ID: '.$wpdb->insert_id, 1).'</pre>'."\n";
            }

        } else {
            echo '<pre>Post meta exists: '.print_r($old_meta['meta_key'], 1).'</pre>'."\n";
        }

        return $new_meta_id;

    }

    public static function transfer_post($old_post_id, $post_type_name = false) {

        global $wpdb;

        $post_type_name = $post_type_name ? $post_type_name : 'POST';

        $old_post = $wpdb->get_row("select * from {$wpdb->prefix}posts where ID=".$old_post_id, ARRAY_A);
        unset($old_post['ID']);
        $old_post['post_title'] = $wpdb->esc_like($old_post['post_title']);

        $post_exists = $wpdb->get_var("select ID from milodev_posts where guid='{$old_post['guid']}' and post_date='{$old_post['post_date']}'");
        if($post_exists) {
            echo '<pre>'.$post_type_name.' already exists: '.print_r($post_exists, 1).'</pre>'."\n";
            $new_post_id = $post_exists;
        } else {

            $new_post_id = $wpdb->insert('milodev_posts', $old_post);
            if( !$new_post_id ) {
                echo '<pre>ERROR: '.$post_type_name.' WAS NOT INSERTED - '.print_r($old_post_id, 1).'</pre>'."\n";
                return false;
            } else {
                echo '<pre>'.print_r($post_type_name.' inserted: '.$old_post_id.'. New ID: '.$wpdb->insert_id, 1).'</pre>'."\n";
            }

        }

        return $new_post_id;

    }

    public static function insert_data_to_prototype() {

        global $wpdb;

        // Open campaigns:
        $campaigns = get_posts(array( // Old DB campaigns
            'post_type' => Leyka_Campaign_Management::$post_type,
            'meta_query' => array('relation' => 'AND', array('key' => 'is_finished', 'value' => 0,)),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));
        echo 'Opened campaigns: '.count($campaigns)."\n\n";
        ob_flush();

        foreach($campaigns as $old_campaign_id) {

            $new_campaign_id = self::transfer_post($old_campaign_id, 'CAMPAIGN');

            if( !$new_campaign_id ) {
                break;
            }

            $campaign_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_campaign_id, ARRAY_A);
            foreach($campaign_meta as $old_meta) {

                $new_meta_id = self::transfer_post_meta($old_meta, $new_campaign_id);
                if( !$new_meta_id ) {
                    break;
                }

            }

            $donations = get_posts(array( // Old DB donations
                'post_type' => Leyka_Donation_Management::$post_type,
                'meta_query' => array('relation' => 'AND', array('key' => 'leyka_campaign_id', 'value' => $old_campaign_id,)),
                'post_status' => 'funded',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ));
            foreach($donations as $old_donation_id) {

                $new_donation_id = self::transfer_post($old_donation_id, 'DONATION');

                if( !$new_donation_id ) {
                    break;
                }

                $donation_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_donation_id, ARRAY_A);
                foreach($donation_meta as $old_meta) {

                    $new_meta_id = self::transfer_post_meta($old_meta, $new_donation_id);
                    if( !$new_meta_id ) {
                        break;
                    }

                }

            }

            $old_campaign_news = get_field('campaign_news', $old_campaign_id);
            if($old_campaign_news) {
                foreach($old_campaign_news as $old_news_item) {

                    if( !is_int($old_news_item) && !is_a($old_news_item, 'WP_Post') ) {
                        continue;
                    }

                    $old_news_item_id = is_int($old_news_item) ? $old_news_item : get_post($old_news_item)->ID;
                    $new_news_item_id = self::transfer_post($old_news_item_id, 'NEWS ITEM');

                    if( !$new_news_item_id ) {
                        break;
                    }

                    $old_news_item_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_news_item_id, ARRAY_A);
                    foreach($old_news_item_meta as $old_meta) {

                        $new_meta_id = self::transfer_post_meta($old_meta, $new_news_item_id);
                        if( !$new_meta_id ) {
                            break;
                        }

                    }

                }
            }

            ob_flush();

        }

        // Closed campaigns:
        $campaigns = get_posts(array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'is_finished', 'value' => 1,),
                array('key' => 'campaign_news', 'compare' => 'LIKE', 'value' => '{'),
            ),
            'post_status' => 'publish',
            'posts_per_page' => 75,
            'fields' => 'ids',
        ));
        echo 'Closed campaigns: '.count($campaigns)."\n\n";
        ob_flush();

//        foreach($campaigns as $old_campaign_id) {
//
//            $new_campaign_id = self::transfer_post($old_campaign_id, 'CAMPAIGN');
//
//            if( !$new_campaign_id ) {
//                break;
//            }
//
//            $campaign_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_campaign_id, ARRAY_A);
//            foreach($campaign_meta as $old_meta) {
//
//                $new_meta_id = self::transfer_post_meta($old_meta, $new_campaign_id);
//                if( !$new_meta_id ) {
//                    break;
//                }
//
//            }
//
//            $donations = get_posts(array( // Old DB donations
//                'post_type' => Leyka_Donation_Management::$post_type,
//                'meta_query' => array('relation' => 'AND', array('key' => 'leyka_campaign_id', 'value' => $old_campaign_id,)),
//                'post_status' => 'funded',
//                'posts_per_page' => -1,
//                'fields' => 'ids',
//            ));
//            foreach($donations as $old_donation_id) {
//
//                $new_donation_id = self::transfer_post($old_donation_id, 'DONATION');
//
//                if( !$new_donation_id ) {
//                    break;
//                }
//
//                $donation_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_donation_id, ARRAY_A);
//                foreach($donation_meta as $old_meta) {
//
//                    $new_meta_id = self::transfer_post_meta($old_meta, $new_donation_id);
//                    if( !$new_meta_id ) {
//                        break;
//                    }
//
//                }
//
//            }
//
//            $old_campaign_news = get_field('campaign_news', $old_campaign_id);
//            if($old_campaign_news) {
//                foreach($old_campaign_news as $old_news_item) {
//
//                    if( !is_int($old_news_item) && !is_a($old_news_item, 'WP_Post') ) {
//                        continue;
//                    }
//
//                    $old_news_item_id = is_int($old_news_item) ? $old_news_item : get_post($old_news_item)->ID;
//                    $new_news_item_id = self::transfer_post($old_news_item_id, 'NEWS ITEM');
//
//                    if( !$new_news_item_id ) {
//                        break;
//                    }
//
//                    $old_news_item_meta = $wpdb->get_results("select * from mlsd_postmeta where post_id=".$old_news_item_id, ARRAY_A);
//                    foreach($old_news_item_meta as $old_meta) {
//
//                        $new_meta_id = self::transfer_post_meta($old_meta, $new_news_item_id);
//                        if( !$new_meta_id ) {
//                            break;
//                        }
//
//                    }
//
//                }
//            }
//
//            ob_flush();
//
//        }

    }

}
