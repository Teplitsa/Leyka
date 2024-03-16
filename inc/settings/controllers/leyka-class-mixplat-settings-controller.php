<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Mixplat_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'mixplat';
        $this->_title = __('Leyka setup Wizard', 'leyka');

        $options = [
            'org_actual_address' => [
                'type' => 'textarea',
                'title' => __('Organization actual address', 'leyka'),
            ],
            'org_actual_address_differs' => [
                'type' => 'checkbox',
                'title' => __('The actual address is different from the legal', 'leyka'),
            ],
        ];

        foreach($options as $option_name => $params) {
            if( !leyka_options()->option_exists($option_name) ) {
                leyka_options()->add_option($option_name, $params['type'], $params);
            }
        }

    }

    protected function _load_frontend_scripts() {
        parent::_load_frontend_scripts();
    }

    protected function _set_stages() {

        // Receiver's data Section:
        $stage = new Leyka_Settings_Stage('rd', __('Your data', 'leyka'));

        // 0-step:
        $section = new Leyka_Settings_Section('init',  $stage->id, __('MIXPLAT', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __('Mixplat Processing is a system that allows NGOs to accept donations using all types of bank cards, quick payment systems, as well as via SMS sent to a four—digit number.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'mixplat-payment-cards-icons',
            'template' => 'mixplat_payment_cards_icons',
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        
        //Подача заявки
        $section = new Leyka_Settings_Section('application_submission', $stage->id, __('Registration', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'step-intro-text',
            'text' => __("<p>If you are not working with Mixplat yet, register on the website <a href='https://mixplat.ru' target='_blank'>mixplat.ru</a> and set up your first project in the system. If you are already registered in the Mixplat, re-registration is not required.</p><p>To register on the website <a href='https://mixplat.ru' target='_blank'>mixplat.ru</a> click on the <a href='https://mixplat.ru/register/' target='_blank'>link</a>. After that, fill out the contact form to receive the login and password from the account.</p>", 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'mixplat_registration',
            'template' => 'mixplat_registration',
        ]))->add_to($stage);


        //Вход в ЛК Mixplat
        $section = new Leyka_Settings_Section('mixplat_login', $stage->id, __('Sign in donor account', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'mixplat_login',
            'template' => 'mixplat_login'
        ]))->add_to($stage);
        
        //Сохранение настроек
        $section = new Leyka_Settings_Section('save_company', $stage->id, __('Saving settings', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'save_company',
            'text' => __("After the moderation is completed, the status of your project will change to &quot;Active&quot;.", 'leyka')
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'mixplat_save_company',
            'template' => 'mixplat_save_company'
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'mixplat_service_id',
            'option_id' => 'mixplat_service_id',
            'custom_setting_id' => 'mixplat_service_id',
            'required' => true,
            'field_type' => 'text',
            'show_title' => true,
            'show_description' => true,
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'mixplat_widget_key',
            'option_id' => 'mixplat_widget_key',
            'custom_setting_id' => 'mixplat_widget_key',
            'required' => true,
            'field_type' => 'text',
            'show_title' => true,
            'show_description' => true,
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'mixplat_secret_key',
            'option_id' => 'mixplat_secret_key',
            'custom_setting_id' => 'mixplat_secret_key',
            'required' => true,
            'field_type' => 'text',
            'show_title' => true,
            'show_description' => true,
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        //Запрос
        $section = new Leyka_Settings_Section('save_query', $stage->id, __('Getting statuses', 'leyka'));
        $section->add_block(new Leyka_Text_Block([ 
            'id' => 'mixplat_save_query',
            'template' => 'mixplat_save_query' 
        ]))->add_to($stage);
       
        //sms
        $section = new Leyka_Settings_Section('sms', $stage->id, __('Keyword by SMS', 'leyka'));
        $section->add_block(new Leyka_Text_Block([ 
            'id' => 'mixplat_sms',
            'template' => 'mixplat_sms' 
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'leyka_mixplat-sms_default_campaign_id',
            'class' => 'show-sms',
            'option_id' => 'leyka_mixplat-sms_default_campaign_id',
            'custom_setting_id' => 'leyka_mixplat-sms_default_campaign_id',
            'required' => true,
            'show_title' => true,
            'show_description' => true,
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'save_company',
            'text' => __("<p>Change the text of the donor's hint by replacing XXXX in the text with your keyword:</p>",'leyka')
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'leyka_mixplat-sms_details',
            'option_id' => 'leyka_mixplat-sms_details',
            'custom_setting_id' => 'leyka_mixplat-sms_details',
            'required' => true,
            'show_title' => true,
            'show_description' => true,
        ]))->add_handler([$this, 'handle_save_options_sms'])->add_to($stage);
        
 
        //testpay
        $section = new Leyka_Settings_Section('testpay', $stage->id, __('Test payment', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'testcheck',
            'template' => 'mixplat_testcheck'
        ]))->add_block(new Leyka_Text_Block([ 
            'id' => 'mixplat_test_pay',
            'template' => 'mixplat_test_pay' 
        ]))->add_handler([$this, 'handle_save_testcheck'])->add_to($stage);
 

        
        // Final Stage: 
        $section = new Leyka_Settings_Section('final', $stage->id, __('Congratulations!', 'leyka'), ['header_classes' => 'greater',]);
        $section->add_block(new Leyka_Text_Block([
            'id' => 'mixplat-final',
            'template' => 'mixplat_final',
        ]))->add_to($stage);
    
        
        

        

        $this->_stages[$stage->id] = $stage;
        // Final Section - End

    }

    protected function _get_next_section_id(Leyka_Settings_Section $section_from = null, $return_full_id = true) {

        $section_from = $section_from && is_a($section_from, 'Leyka_Settings_Section') ? $section_from : $this->current_section;
        $next_section_full_id = false;

        /** @todo To many if-elses sucks - try some wrapping pattern here */
        if($section_from->stage_id === 'rd') {
            switch($section_from->id){
                case "init": 
                    $next_section_full_id = $section_from->stage_id.'-application_submission';
                    break;
                case "application_submission":
                    $next_section_full_id = $section_from->stage_id.'-mixplat_login';
                    break;
                case "mixplat_login":
                    $next_section_full_id = $section_from->stage_id.'-save_company';
                    break;
                case "save_company":
                    $next_section_full_id = $section_from->stage_id.'-save_query';
                    break;
                case "save_query":
                    $next_section_full_id = $section_from->stage_id.'-sms';
                    break;
                case "sms":
                    $next_section_full_id = $section_from->stage_id.'-testpay';
                    break;
                case "testpay":
                    $next_section_full_id = $section_from->stage_id.'-final';
                    break;
            } 
        } 
        
        if($section_from->stage_id === 'final') { // Final Section
            if($section_from->id === 'campaign_completed') {
                $next_section_full_id = 'final-campaign_completed'; // $next_section_full_id = 'final-init';
            }            
        }

        if( !!$return_full_id || !is_string($next_section_full_id) ) {
            return $next_section_full_id;
        } else {

            $next_section_full_id = explode('-', $next_section_full_id);

            return array_pop($next_section_full_id);

        }

    }

    protected function _init_navigation_data() {

        $this->_navigation_data = [
            [
                'stage_id' => 'rd',
                'title' => 'МИКСПЛАТ',
                'url' => '',
                'sections' => [
                    [
                        'section_id' => 'application_submission',
                        'title' => __('Registration', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'mixplat_login',
                        'title' => __('Sign in donor account', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'save_company',
                        'title' => __('Saving settings', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'save_query',
                        'title' => __('Getting statuses', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'sms',
                        'title' => __('Keyword by SMS', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'testpay',
                        'title' => __('Test payment', 'leyka'),
                        'url' => '',
                    ],
                ],
            ], 
            [
                'stage_id' => 'final',
                'title' => __('Setup completed', 'leyka'),
                'url' => '',
            ],
        ];

    }

    protected function _get_section_navigation_position($section_full_id = false) {
        $section_full_id = $section_full_id ? trim(esc_attr($section_full_id)) : $this->get_current_section()->full_id;
        return $section_full_id;
    }

    public function get_submit_data($component = null) {

        $section = $component && is_a($component, 'Leyka_Settings_Section') ? $component : $this->current_section;
        $submit_settings = [
            'next_label' => __('Continue', 'leyka'),
            'next_url' => true,
            'prev' => __('Back to the previous step', 'leyka'),
        ];

        if($section->next_label) {
            $submit_settings['next_label'] = $section->next_label;
        }

        if($section->stage_id === 'rd' && $section->id === 'init') {

            $submit_settings['next_label'] = __("Let's go!", 'leyka');
            $submit_settings['prev'] = false; // Means that the Wizard shouldn't display the back link

        }

        if($section->title == 'Поздравляем!') {
            $submit_settings['next_label'] = __('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function section_init() {

        // Receiver type Section prerequisites - show "legal" receiver type only if receiver country is set:
        if($this->_get_setting_value('receiver_country') === '-') {
            add_filter('leyka_option_info-receiver_legal_type', function($option_data){

                unset($option_data['list_entries']['legal']);

                return $option_data;

            });
        }

        // If init campaign is not set or deleted on the campaign decoration step, return to the campaign data step:
        if($this->get_current_section()->id === 'campaign_decoration') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handle_settings_go_back('cd-campaign_description');
            }

        } else if($this->get_current_section()->id === 'campaign_completed') {

            $init_campaign_id = get_transient('leyka_init_campaign_id');
            $init_campaign = get_post($init_campaign_id);

            if( !$init_campaign_id || !$init_campaign ) {
                $this->_handle_settings_go_back('cd-campaign_description');
            }

            $empty_bank_essentials_options = leyka_get_empty_bank_essentials_options();
            if($empty_bank_essentials_options) { // Show the fields
                foreach($empty_bank_essentials_options as $option_id) {
                    $this->get_current_section()->add_block(new Leyka_Option_Block([
                        'id' => $option_id,
                        'option_id' => $option_id,
                    ]));
                }
            } else { // Enable the Quittance PM if there are no other active ones

                $pm_data = leyka_options()->opt('pm_available');
                $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

                if( !$pm_data ) {

                    $pm_data[] = $quittance_pm_full_id;
                    leyka_options()->opt('pm_available', $pm_data);

                    $pm_order = [];
                    foreach($pm_data as $pm_full_id) {
                        if($pm_full_id) {
                            $pm_order[] = "pm_order[]={$pm_full_id}";
                        }
                    }

                    leyka_options()->opt('pm_order', implode('&', $pm_order));

                }

            }

        }

    }

    public function handle_plugin_stats_section(array $section_settings) {

        if(empty($section_settings['send_plugin_stats'])) {
            return false;
        }

        update_option('leyka_plugin_stats_option_needs_sync', time());
        $stats_option_synch_res = leyka_sync_plugin_stats_option();

        if( !leyka_options()->opt('plugin_stats_sync_enabled') ) {
            return true;
        } else if(is_wp_error($stats_option_synch_res) && leyka_options()->opt('plugin_debug_mode')) {
            // DO NOT return WP_Error in production!
            return $stats_option_synch_res; // We should save the option and go to the next step anyway
        } else {
            return delete_option('leyka_plugin_stats_option_needs_sync')
                && update_option('leyka_plugin_stats_option_sync_done', time());
        }

    }

    public function handle_campaign_description_section(array $section_settings) {

        $init_campaign_params = [
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_title' => trim(esc_attr(wp_strip_all_tags($section_settings['campaign_title']))),
            'post_excerpt' => trim(esc_textarea($section_settings['campaign_short_description'])),
            'post_content' => '',
        ];

        $existing_campaign_id = get_transient('leyka_init_campaign_id');
        if($existing_campaign_id) {

            if(get_post($existing_campaign_id)) {
                $init_campaign_params['ID'] = $existing_campaign_id;
            } else {

                $existing_campaign_id = false;
                delete_transient('leyka_init_campaign_id');

            }

        }

        $campaign_id = wp_insert_post($init_campaign_params, true);

        if(is_wp_error($campaign_id)) {
            return new WP_Error('init_campaign_insertion_error', __('Error while creating the campaign', 'leyka'));
        }

        update_post_meta($campaign_id, 'campaign_target', (float)$section_settings['campaign_target']);

        if( !$existing_campaign_id ) {

            $this->_add_history_entry(['campaign_id' => $campaign_id]);
            set_transient('leyka_init_campaign_id', $campaign_id);

        }

        return true;

    }

    public function handle_campaign_decoration_section(array $section_settings) {

        // Publish the init campaign:
        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = [];

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', __('Campaign ID is wrong or missing', 'leyka'));
        }

        if(
            $campaign->post_type !== 'publish' &&
            is_wp_error(wp_update_post(['ID' => $campaign_id, 'post_status' => 'publish']))
        ) {
            return new WP_Error('init_campaign_publishing_error', __('Error when publishing the campaign', 'leyka'));
        }

        return $errors ? $errors : true;

    }

    public function handle_init_section(array $section_settings) {

        $section_settings['receiver_country'] = empty($section_settings['receiver_country'])
            || $section_settings['receiver_country'] === '-' ?
            'ru' : $section_settings['receiver_country'];

        if($section_settings['receiver_country'] !== 'ru') {
            leyka_options()->opt('receiver_legal_type', 'legal');
        }

        leyka_refresh_currencies_rates();

        return true;

    }

    public function handle_campaign_completed_section(array $section_settings) {

        $campaign_id = get_transient('leyka_init_campaign_id');
        $campaign = get_post($campaign_id);
        $errors = [];

        if( !$campaign_id || !$campaign ) {
            return new WP_Error('wrong_init_campaign_id', __('Campaign ID is wrong or missing', 'leyka'));
        }

        // Enable the Quittance PM, if all the needed fields are filled:
        if(leyka_are_bank_essentials_set()) {

            $pm_data = leyka_options()->opt('pm_available');
            $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;

            if( !in_array($quittance_pm_full_id, $pm_data) ) {

                $pm_data[] = $quittance_pm_full_id;
                leyka_options()->opt('pm_available', $pm_data);

                $pm_order = [];
                foreach($pm_data as $pm_full_id) {
                    if($pm_full_id) {
                        $pm_order[] = "pm_order[]={$pm_full_id}";
                    }
                }

                leyka_options()->opt('pm_order', implode('&', $pm_order));

            }

        }

        return $errors ? $errors : true;

    }


    public function handle_save_testcheck(array $section_settings) {
        leyka_save_option('mixplat_test_mode');
        return true;
    }
    
    public function handle_save_options_sms(array $section_settings) {
        leyka_save_option('mixplat-sms_details');
        leyka_save_option('mixplat-sms_default_campaign_id');
        return true;
    }

    public function handle_save_options(array $section_settings) {
        $gateway = leyka_get_gateway_by_id('mixplat');
        foreach($gateway->get_options_names() as $option_id) {
            leyka_save_option($option_id);
        }
        return true;
    }
}