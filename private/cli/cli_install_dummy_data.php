<?php
/* Service actions */
set_time_limit (0);
ini_set('memory_limit','512M');

try {
    $time_start = microtime(true);

	include('cli_common.php');
    include('dummy_data_utils.php');

	fwrite(STDOUT, 'Memory before anything: '.memory_get_usage(true).chr(10).chr(10));

	$leyka_dummy_data = new LeykaDummyData();

	$leyka_dummy_data->install_settings();
	fwrite(STDOUT, "Settings installed\n");

    $leyka_dummy_data->install_payment_methods();
	fwrite(STDOUT, "Payment methods installed\n");

    $leyka_dummy_data->install_campaigns_with_donations();
	fwrite(STDOUT, "Campaigns with donations installed\n");

	LeykaDummyDataUtils::reset_default_pages();
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

    public $vars = [];

    public function __construct() {
        $this->_update_dummy_data_settings();
    }

    public function install_settings() {

        $settings_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/leyka_settings.json');
        $settings = json_decode($settings_raw, true);

        foreach($settings as $setting) {

            $value = $setting['translate'] === true ? __($setting['value'], 'leyka') : $setting['value'];
            update_option($setting['title'], $value);

        }

    }

    public function install_payment_methods() {

        $available_pms_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/payment_methods.json');
        $available_pms_array = json_decode($available_pms_raw, true);

        if(sizeof($available_pms_array) > 0) {

            foreach($available_pms_array as $pm) {
                $available_pms[] = $pm['gateway_id']."-".$pm['title'];
            }

            update_option('leyka_pm_available', $available_pms);

        }

    }

    public function install_campaigns_with_donations() {

        global $wpdb;

        $campaigns_data_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/campaigns.json');
        $campaigns_data = json_decode($campaigns_data_raw, true);
        $uploads = wp_upload_dir();

        foreach($campaigns_data as $campaign_data) {

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

            $payments_per_compaign = round((int)$this->vars['donations_count']['value'] / sizeof($campaign_data));
            $this->install_campaign_donations($campaign, $payments_per_compaign);
            $campaign->update_total_funded_amount();
            $campaign->refresh_target_state();

			//finished campaign
			if($campaign_data['finished'] === true) {
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
        }
    }

    public function install_campaign_donations(Leyka_Campaign $campaign, $payments_count) {

        $donors_constructor_data_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/donors_constructor.json');
        $donors_constructor_data = json_decode($donors_constructor_data_raw, true);

        $available_pms_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/payment_methods.json');
        $available_pms_array = json_decode($available_pms_raw, true);

        if(sizeof($available_pms_array) > 0) {
            foreach($available_pms_array as $pm) {
                $available_pms[$pm['gateway_id']] = $pm['gateway_id']."-".$pm['title'];
            }
        }

        $init_rebills = [];

        for($i = 0; $i < $payments_count; $i++ ) {

            $gateway_id = $this->_get_proportion_part_title($this->vars['gates_usage_proportions']['value']);
            $payment_method_id = $available_pms[$gateway_id];
            $donor_name =
                $donors_constructor_data['first_names'][rand(0, sizeof($donors_constructor_data['first_names'])-1)]." ".
                $donors_constructor_data['patronymics'][rand(0, sizeof($donors_constructor_data['patronymics'])-1)]." ".
                $donors_constructor_data['last_names'][rand(0, sizeof($donors_constructor_data['last_names'])-1)];
            $donor_email = $donors_constructor_data['emails'][rand(0, sizeof($donors_constructor_data['emails'])-1)];
            $status = $this->_get_proportion_part_title($this->vars['donations_statuses_proportions']['value']);
            $payment_type = $i === 0 ?
                'single' : $this->_get_proportion_part_title($this->vars['donations_types_proportions']['value']);

            $donation_data = [
                'gateway_id' => $gateway_id,
                'payment_method_id' => $payment_method_id,
                'campaign_id' => $campaign->ID,
                'purpose_text' => $campaign->title,
                'status' => $status,
                'payment_type' => $payment_type,
                'amount' => round(rand(10, 1000), -1),
                'currency' => 'rub',
                'donor_name' => $donor_name,
                'donor_email' => $donor_email,
                'is_test_mode' => true
            ];

            if($payment_type === 'rebill') {

                $donation_data['recurring_is_active'] = true;

                if(rand(1, 5) > 1 && sizeof($init_rebills) > 0) { // non-init rebill
                    $donation_data['init_recurring_donation'] = $init_rebills[rand(0, sizeof($init_rebills))];
                }
            }

            $donation_id = Leyka_Donations::get_instance()->add($donation_data);

            if($payment_type === 'rebill' && $status === 'funded' && empty($donation_data['init_recurring_donation'])) {
                $init_rebills[] = $donation_id;
            }

        }

    }

    protected static function _get_proportion_part_title($proportions) {

        $rnd = rand(1, 100);
        $min = null;
        $max = null;

        foreach($proportions as $proportion_title => $proportion_value) {
            $min = $min ? $max : 0;
            $max = $max ? $max + $proportion_value : $proportion_value;

            if($rnd > $min && $rnd <= $max) {
                return $proportion_title;
            }
        }

    }

    protected function _update_dummy_data_settings() {

        $def_vars_raw = file_get_contents(LEYKA_PLUGIN_DIR.'private/cli/dummy_data/variables.json');
        $def_vars = json_decode($def_vars_raw, true);

        foreach($def_vars as $def_var_title => $def_var_value) {

            $message = "\nВведите через запятую ".$def_var_value['description'].":\n\n";

            if(is_array($def_var_value['value'])) {
                $idx = 1;

                foreach($def_var_value['value'] as $part_title => $part_value) {
                    $message .= "\t${idx} цифра - % ${part_title}\n";
                    $idx++;
                }

                $message .= "\n\tПо умолчанию - ".implode(',', $def_var_value['value'])."\n";
            } else {
                $message .= "\tПо умолчанию - ".$def_var_value['value']."\n";
            }

            fwrite(STDOUT, $message);

            $this->vars[$def_var_title] = [
                'description' => $def_var_value['description'],
                'value' => LeykaDummyDataUtils::ask_settings_variable_update($def_var_value['value'])
            ];

            if($this->vars[$def_var_title]['value'] === $def_var_value['value']) {

                fwrite(STDOUT,PHP_EOL.'Ошибка ввода! Взяты дефолтные значения. '.PHP_EOL);

                if (is_array($this->vars[$def_var_title]['value'])) {
                    fwrite(
                        STDOUT,
                        PHP_EOL.ucfirst($def_var_value['description']).": ".implode(',', $this->vars[$def_var_title]['value']).PHP_EOL.PHP_EOL
                    );
                } else {
                    fwrite(
                        STDOUT,
                        PHP_EOL.ucfirst($def_var_value['description']).": ".$this->vars[$def_var_title]['value'].PHP_EOL.PHP_EOL
                    );
                }

            }

        }

    }

}
