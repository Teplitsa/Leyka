<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

try {
    $time_start = microtime(true);
	include('cli_common.php');
    include('random_payments_data.php');
    include('dummy_data_utils.php');
    
	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));

	global $LEYKA_TEST_DATA;
    
    $options = getopt("", array('ngo:'));
    $ngo_name = isset($options['ngo']) ? $options['ngo'] : '';
    
    if(empty($ngo_name)) {
        throw new Exception("ngo_name must be defined");
    }
    
    $installer = new LeykaRandomPaymentsInstaller($LEYKA_TEST_DATA);
    $installation_result = $installer->install_data($ngo_name);
    
    if(is_wp_error($installation_result)) {
        fwrite(STDOUT, "ERROR: " . $installation_result->get_error_message());
    }
    
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
	echo $ex . "\n";
}

class LeykaRandomPaymentsInstaller {
    
    private $ngo_name = "";
    
    private $test_data = array();
    private $config = array();
    private $ngo_data = array();
    private $donors_data = array();
    private $gateways_payment_methods = array();
    private $gateways_options = array();
    
    private $installed_campaigns = array();
    private $installed_gateways = array();
    
    public function __construct($test_data) {
        $this->test_data = $test_data;
    }
    
    public function install_data($ngo_name) {
        
        if(empty($this->test_data['ngos'][$ngo_name])) {
            return new WP_Error( 'broke', sprintf("UNKNOWN NGO: %s", $ngo_name ));
        }

        if(empty($this->test_data['campaigns'][$ngo_name])) {
            return new WP_Error( 'broke', sprintf("NGO campaigns missing: %s", $ngo_name ));
        }

        if(empty($this->test_data['config'][$ngo_name])) {
            return new WP_Error( 'broke', sprintf("NGO config missing: %s", $ngo_name ));
        }

        # set installer data
        $this->ngo_name = $ngo_name;
        $this->config = $this->test_data['config'][$ngo_name];
        $this->ngo_data  = $this->test_data['ngos'][$ngo_name];
        $this->campaigns_data  = $this->test_data['campaigns'][$ngo_name];
        $this->donors_data  = $this->test_data['donors'];
        $this->gateways_payment_methods  = $this->test_data['payment_methods'];
        $this->gateways_options  = $this->test_data['gateways_options'];
        
        # run installation 
        $this->install_settings();
        fwrite(STDOUT, "Settings installed\n");
    
        $this->install_payment_methods();
        fwrite(STDOUT, "Payment methods installed\n");
        //fwrite(STDOUT, sprintf("gateways: %s\n", print_r($this->installed_gateways, true)));
        
        $this->install_campaigns();
        fwrite(STDOUT, "Campaigns installed\n");
        
        $this->install_donations();
        fwrite(STDOUT, "Donations installed\n");
        
        LeykaDummyDataUtils::reset_default_pages();
        fwrite(STDOUT, "Accessory pages reset to default\n");
    }

    private function install_settings() {
        foreach($this->ngo_data as $meta_key => $meta_value) {
            update_option($meta_key, $meta_value);
        }
    }

    private function install_payment_methods() {
        $gw_list = array_keys($this->gateways_payment_methods);
        $installed_gateways = array();
        $installed_payment_methods = array();
        
        foreach($this->gateways_options as $gw_id => $meta_dict) {
            foreach($meta_dict as $option_name => $option_value) {
                delete_option($option_name);
            }
        }
        
        for($i = 0; $i < $this->config['pm_count']; $i++) {
            $gw_id = $gw_list[random_int(0, count($gw_list) - 1)];
            $pm_id = $this->gateways_payment_methods[$gw_id][random_int(0, count($this->gateways_payment_methods[$gw_id]) - 1)];
            
            if(empty($installed_gateways[$gw_id])) {
                $installed_gateways[$gw_id] = array();
            }
            $installed_gateways[$gw_id][$pm_id] = true;
            $installed_payment_methods[$pm_id] = true;
        }
        
        foreach($installed_gateways as $gw_id => $pm_dict) {
            $installed_gateways[$gw_id] = array_keys($pm_dict);
        }
        $installed_payment_methods = array_keys($installed_payment_methods);
        
        $this->installed_gateways = $installed_gateways;
        
        update_option('leyka_pm_available', $installed_payment_methods);
        
        foreach($this->gateways_options as $gw_id => $meta_dict) {
            if(empty($this->installed_gateways[$gw_id])) {
                continue;
            }
            
            foreach($meta_dict as $option_name => $option_value) {
                update_option($option_name, $option_value);
            }
        }
        
    }

    private function install_campaigns() {
        global $wpdb;
        
        $uploads = wp_upload_dir();
        
        $this->installed_campaigns = array();
        
        foreach($this->test_data['campaigns'] as $ngo => $campaigns) {
            if($ngo == $this->ngo_name) {
                continue;
            }
            
            foreach($campaigns as $campaign_data) {
                $campaign_post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", Leyka_Campaign_Management::$post_type, $campaign_data['name']));
                if($campaign_post) {
                    $campaign_post = new WP_Post($campaign_post);
                    $campaign = new Leyka_Campaign($campaign_post);
                    LeykaDummyDataUtils::delete_campaign_donations($campaign);
                    $campaign->delete(True);
                }
            }
        }

        foreach($this->campaigns_data as $campaign_data) {

            $campaign_post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s", Leyka_Campaign_Management::$post_type, $campaign_data['name']));
            if($campaign_post) {
                $campaign_post = new WP_Post($campaign_post);
                $campaign = new Leyka_Campaign($campaign_post);

                LeykaDummyDataUtils::delete_campaign_donations($campaign);
                $campaign->delete(True);
            }

            $campaign_id = wp_insert_post(array(
                'post_type' => Leyka_Campaign_Management::$post_type,
                'post_status' => 'publish',
                'post_title' => $campaign_data['title'],
                'post_name' => $campaign_data['name'],
                'post_content' => $campaign_data['content'],
                'post_parent' => 0,
            ));

            update_post_meta($campaign_id, 'campaign_target', $campaign_data['target']);
            update_post_meta($campaign_id, 'campaign_template', 'revo');
            $campaign = new Leyka_Campaign($campaign_id);

            $campaign->refresh_target_state();

			//finished campaign
			if(preg_match("/.*-done$/", $campaign->post_name)) {
				update_post_meta($campaign_id, 'is_finished', 1);
			}

            # add thumbnail
            if(isset($campaign_data['thumbnail'])) {
                $thumb_id = false;
                $file = $campaign_data['thumbnail'];
                $path = WP_CONTENT_DIR.'/plugins/leyka/private/res/'.$file;

                $test_path = $uploads['path'].'/'.$file;
                if(!file_exists($test_path)) {
                    $thumb_id = LeykaDummyDataUtils::upload_img_from_path($path);
                }
                else {
                    $a_url = $uploads['url'].'/'.$file;
                    $thumb_id = attachment_url_to_postid($a_url);
                }
                update_post_meta($campaign->ID, '_thumbnail_id', (int)$thumb_id);
            }
            
            $this->installed_campaigns[] = $campaign;
        }
    }

    private function install_donations() {
        for($i = 0; $i < $this->config['payments_count']; $i++) {
            $campaign = $this->get_random_installed_campaign();
            $gateway_id = $this->get_random_installed_gateway();
            $pm_id = $this->get_random_installed_pm($gateway_id);

            $donor = $this->get_random_donor();
            $amount = $this->get_random_amount();
            
            $donation_id = Leyka_Donation::add(array(
                'gateway_id' => $gateway_id,
                'payment_method_id' => $pm_id,
                'campaign_id' => $campaign->ID,
                'purpose_text' => $campaign->title,
                'status' => 'funded',
                'payment_type' => 'single',
                'amount' => $amount,
                'currency' => 'rur',
                'donor_name' => $donor['name'],
                'donor_email' => $donor['email'],
            ));
    
            $donation = new Leyka_Donation($donation_id);
            $campaign->update_total_funded_amount($donation);
        }
    }
    
    private function get_random_installed_campaign() {
        return $this->installed_campaigns[random_int(0, count($this->installed_campaigns) - 1)];
    }

    private function get_random_installed_gateway() {
        $gw_list = array_keys($this->installed_gateways);
        return $gw_list[random_int(0, count($gw_list) - 1)];
    }

    private function get_random_installed_pm($gw_id) {
        return $this->installed_gateways[$gw_id][random_int(0, count($this->installed_gateways[$gw_id]) - 1)];
    }

    private function get_random_donor() {
        return $this->test_data['donors'][random_int(0, count($this->test_data['donors']) - 1)];
    }

    private function get_random_amount() {
        $amount = array(random_int(10, 99), random_int(100, 999), random_int(1000, 10000));
        $amount = $amount[random_int(0, count($amount) - 1)];
        
        if($amount >= 1000) {
            $amount = 1000 * floor($amount / 1000);
        }
        elseif($amount >= 100) {
            $amount = 100 * floor($amount / 100);
        }
        else {
            $amount = 10 * floor($amount / 10);
        }
        
        return $amount;
    }
}
