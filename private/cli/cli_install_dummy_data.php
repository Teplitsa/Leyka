<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

try {
    $time_start = microtime(true);
	include('cli_common.php');
	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));
	
	LeykaDummyData::install_settings();
	fwrite(STDOUT, "Settings installed\n");
	
	LeykaDummyData::install_payment_methods();
	fwrite(STDOUT, "Payment methods installed\n");
	
	LeykaDummyData::install_campaigns_with_donations();
	fwrite(STDOUT, "Campaigns with donations installed\n");
	
	LeykaDummyData::reset_default_pages();
	fwrite(STDOUT, "Accessory pages reset to default\n");
	
	
	fwrite(STDOUT, "done\n\n");
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

class LeykaDummyData {
    
    public static function install_settings() {
        # NGO data
        update_option('leyka_org_full_name', 'Фонд помощи бездомным животным "Общий Барсик"');
        update_option('leyka_org_face_fio_ip', 'Котов Аристарх Евграфович');
        update_option('leyka_org_face_fio_rp', 'Собакин Евлампий Мстиславович');
        update_option('leyka_org_face_position', 'Директор');
        update_option('leyka_org_address', '127001, Россия, Москва, ул. Ленина, д.1, оф.5');
    
        # reg and bank account
        update_option('leyka_org_state_reg_number', '1134567890123');
        update_option('leyka_org_kpp', '223456789');
        update_option('leyka_org_inn', '333456789012');
        update_option('leyka_org_bank_account', '44445678901234567890');
        update_option('leyka_org_bank_name', 'МЯО Звербанк');
        update_option('leyka_org_bank_bic', '555556789');
        update_option('leyka_org_bank_corr_account', '66666678901234567890');
    
        //     update_option('', '');
    }
    
    public static function install_payment_methods() {
        $available_pms = array(
            'yandex-yandex_money', 'mixplat-sms', 'quittance-bank_order', 'text-text_box'
        );
        update_option('leyka_pm_available', $available_pms);
    }
    
    public static function install_campaigns_with_donations() {
        global $wpdb;
        
        $campaigns_data = array(
            array('name' => 'build-house-for-pets', 'title' => 'Строим жилье для питомцев', 'target' => 27000.0),
            array('name' => 'buy-food-for-kittens', 'title' => 'Покупаем еду для котят', 'target' => 15000.0),
            array('name' => 'treat-pets', 'title' => 'Лечим больных животных', 'target' => 800.0),
        );
        
        foreach($campaigns_data as $campaign_data) {
            
            $campaign_post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", Leyka_Campaign_Management::$post_type, $campaign_data['name']));
            if($campaign_post) {
                $campaign_post = new WP_Post( $campaign_post );
                $campaign = new Leyka_Campaign($campaign_post);
                
                self::delete_campaign_donations($campaign);
                $campaign->delete(True);
            }
            
            $campaign_id = wp_insert_post(array(
                'post_type' => Leyka_Campaign_Management::$post_type,
                'post_status' => 'publish',
                'post_title' => $campaign_data['title'],
                'post_name' => $campaign_data['name'],
                'post_parent' => 0,
            ));
            
            update_post_meta($campaign_id, 'campaign_target', $campaign_data['target']);
            $campaign = new Leyka_Campaign($campaign_id);
            
            self::install_campaign_donations($campaign);
            $campaign->refresh_target_state();
        }
    }
    
    public static function install_campaign_donations($campaign) {
        
        $donations_data = array(
            array('gateway_id' => 'yandex', 'payment_method_id' => 'yandex_money', 'donor_name' => 'Мартынов Семен Семенович', 'donor_email' => 'test@ngo2.ru', 'amount' => 150.0),
            array('gateway_id' => 'mixplat', 'payment_method_id' => 'sms', 'donor_name' => 'Коровин Остап Рудольфович', 'donor_email' => 'test@ngo2.ru', 'amount' => 30.0),
            array('gateway_id' => 'quittance', 'payment_method_id' => 'bank_order', 'donor_name' => 'Быков Иван Иванович', 'donor_email' => 'test@ngo2.ru', 'amount' => 420.0),
            array('gateway_id' => 'text', 'payment_method_id' => 'text_box', 'donor_name' => 'Лось Вениамин Робертович', 'donor_email' => 'test@ngo2.ru', 'amount' => 210.0),
        );
        
        foreach($donations_data as $donation_data) {
            $donation_id = Leyka_Donation::add(array(
                'gateway_id' => $donation_data['gateway_id'],
                'payment_method_id' => $donation_data['payment_method_id'],
                'campaign_id' => $campaign->ID,
                'purpose_text' => $campaign->title,
                'status' => 'funded',
                'payment_type' => 'single',
                'amount' => $donation_data['amount'],
                'currency' => 'rur',
                'donor_name' => $donation_data['donor_name'],
                'donor_email' => $donation_data['donor_email'],
            ));
            
            $donation = new Leyka_Donation($donation_id);
            $campaign->update_total_funded_amount($donation);
        }
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
