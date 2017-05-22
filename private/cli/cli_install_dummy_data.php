<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

try {
    $time_start = microtime(true);
	include('cli_common.php');
	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));
	
	leyka_install_dummy_settings();
	fwrite(STDOUT, "Settings installed\n");
	
	leyka_install_dummy_campaigns();
	fwrite(STDOUT, "Campaigns installed\n");
	
// 	leyka_install_dummy_donations();
// 	fwrite(STDOUT, "Donations installed\n");
	
	leyka_reset_default_pages();
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

function leyka_install_dummy_settings() {
    # NGO data
    update_option('leyka_org_full_name', 'Фонд помощи бездомным животным "Общий Барсик"');
    update_option('leyka_org_face_fio_ip', 'Котов Аристарх Евграфович');
    update_option('leyka_org_face_fio_rp', 'Собакин Артур Мстиславович');
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

function leyka_install_dummy_campaigns() {
    global $wpdb;
    
    $campaign_name = 'build-house-for-pets';
    $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", Leyka_Campaign_Management::$post_type, $campaign_name));
    wp_delete_post( $post->ID, true );
    
    $campaign_id = wp_insert_post(array(
        'post_type' => Leyka_Campaign_Management::$post_type,
        'post_status' => 'publish',
        'post_title' => 'Строим жилье для питомцев',
        'post_name' => $campaign_name,
        'post_parent' => 0,
    ));
    
    update_post_meta($campaign_id, 'campaign_target', 27000.0);
    $campaign = new Leyka_Campaign($campaign_id);
    $campaign->refresh_target_state();
    
//     leyka_install_dummy_donations($campaign_id);
}

function leyka_install_dummy_donations($campaign_id) {
    $donation_id = Leyka_Donation::add(array(
        'gateway_id' => $this->_id,
        'payment_method_id' => 'mobile',
        'campaign_id' => $campaign_id,
        'status' => 'funded',
        'payment_type' => 'single',
        'amount' => 150.0,
        'currency' => 'rur',
    ));
}

function leyka_reset_default_pages() {
    leyka_get_default_success_page();
    leyka_get_default_failure_page();
}