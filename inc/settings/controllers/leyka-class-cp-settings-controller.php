<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Cp_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;
    protected $_cp_email = '';

    protected function _set_attributes() {

        $this->_id = 'cp';
        $this->_title = __('CloudPayments setup Wizard', 'leyka');
        $this->_cp_email = leyka_options()->opt('plugin_debug_mode') ? 'support@te-st.ru' : 'sales@cloudpayments.ru';

    }

    protected function _load_frontend_scripts() {

        wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments', [], false, true);

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', [], false, true);

        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');

        add_action('admin_enqueue_scripts', function(){

            wp_localize_script('leyka-settings', 'leyka_wizard_cp', [
                'cp_public_id' => leyka_options()->opt('cp_public_id'),
                'main_currency' => 'RUB',
                'test_donor_email' => get_option('admin_email'),
                'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
                'cp_not_set_up' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka'),
                'cp_donation_failure_reasons' => [
                    'User has cancelled' => __('You cancelled the payment', 'leyka'),
                ],
            ]);

        });

        parent::_load_frontend_scripts();

    }

    protected function _set_stages() {

        // The main CP settings stage:
        $stage = new Leyka_Settings_Stage('cp', 'CloudPayments');

        $section = new Leyka_Settings_Section('init',  $stage->id, __('CloudPayments', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('CloudPayments system is a multi-profile processing center for handling payments via Visa, MasterCard and MIR payment systems.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-payment-cards-icons',
            'template' => 'cp_payment_cards_icons',
        ]))->add_handler([$this, 'handle_first_step'])->add_to($stage);

        $section = new Leyka_Settings_Section('prepare_documents',  $stage->id, __('Preparing the documents', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('CloudPayments setup starts with documents preparation.<br>Download and fill the needed documents.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-prepare-documents',
            'template' => 'cp_prepare_documents',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section(
            'send_documents',
            $stage->id,
            __('Sending the documents', 'leyka'),
            ['next_label' => __('Send the email', 'leyka'), 'form_enctype' => 'multipart/form-data']
        );
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('<p>When the documents are prepared, they have to be sent to CloudPayments. The form below allows you to send the documents right from the website.</p>
<p>Also you may send the documents from your own email to the CloudPayments email: sales@cloudpayments.ru.</p>
<p>Please note that documents checkup may take until 3 working days.</p>
<p>If you have to close this page, we are going to remember the passed steps and you may always return here.</p>', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send_documents_file',
            'custom_setting_id' => 'send_documents_file',
            'field_type' => 'file',
            'data' => [
                'title' => __('Attach the Annex #1', 'leyka'),
                'required' => __('Select a file', 'leyka'),
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send_documents_to',
            'custom_setting_id' => 'send_documents_to',
            'field_type' => 'legend',
            'data' => [
                'title' => __('To whom', 'leyka'),
                'text' => $this->_cp_email,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send_documents_from',
            'custom_setting_id' => 'send_documents_from',
            'field_type' => 'text',
            'data' => [
                'title' => __('From whom', 'leyka'),
                'value' => get_option('admin_email'),
                'required' => true,
            ],
        ]))
        ->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send_documents_email_subject',
            'custom_setting_id' => 'send_documents_email_subject',
            'field_type' => 'text',
            'data' => [
                'title' => __('Email topic', 'leyka'),
                'value' => __('Please connect me to CloudPayments system', 'leyka'),
                'required' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send_documents_email_text',
            'custom_setting_id' => 'send_documents_email_text',
            'field_type' => 'textarea',
            'data' => [
                'title' => __('Email text', 'leyka'),
                'value' => __("Hello! We'd like to connect to CloudPayments. Our Annex #1 is filled and attached to the email.", 'leyka'),
                'required' => true,
            ],
        ]))->add_handler([$this, 'handle_send_documents'])->add_to($stage);

        $section = new Leyka_Settings_Section('signin_cp_account',  $stage->id, __('Login to your CloudPayments dashboard', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Use the login, password and a link to the dashboard from the CloudPayments email.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-account-setup-instructions',
            'template' => 'cp_account_setup_instructions',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section(
            'copy_key',
            $stage->id,
            __('Set up your Public ID and API password', 'leyka'),
            ['next_label' => __('Save & continue', 'leyka')]
        );
        $section->add_block(new Leyka_Text_Block([
            'id' => 'cp-settings-copy-text',
            'text' => __('Copy the public ID and the API password from your CloudPayments account, as in the screenshot below.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-copy-key',
            'template' => 'cp_copy_key_password',
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-settings-key-paste-text',
            'text' => __('Paste the copied public ID to the field below', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'cp_public_id',
            'option_id' => 'cp_public_id',
            'custom_setting_id' => 'cp_public_id',
            'required' => true,
            'field_type' => 'text',
            'show_title' => false,
            'show_description' => false,
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-settings-password-paste-text',
            'text' => __('Paste the copied API password to the field below', 'leyka'),
        ]))->add_block(new Leyka_Option_Block([
            'id' => 'cp_api_secret',
            'option_id' => 'cp_api_secret',
            'custom_setting_id' => 'cp_api_secret',
            'required' => true,
            'field_type' => 'text',
            'show_title' => false,
            'show_description' => false,
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('check_payment_request',  $stage->id, __('Add a request to check donations', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Copy the address:', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-check-payment-request',
            'template' => 'cp_check_payment_request',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('accepted_payment_notification',  $stage->id, __('Add a request to complete donations', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Copy the address:', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-accepted-payment-notification',
            'template' => 'cp_accepted_payment_notification',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('rejected_payment_notification',  $stage->id, __('Add a request to reject donations', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Copy the address:', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-rejected-payment-notification',
            'template' => 'cp_rejected_payment_notification',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('notification_email',  $stage->id, __('E-mail for successful donations notifications', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Copy the email:', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-notification-email',
            'template' => 'cp_notification_email',
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('cp_payment_tryout', $stage->id, __('Test donation', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-text-1',
            'text' => __("Let's see if donations work completely. We can do this by using the test bank card numbers and data below.", 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => ['payment_tryout_completed'],
            'rendering_type' => 'template',
            'data' => ['required' => __('You must make all of the test donations to proceed', 'leyka')],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section(
            'cp_going_live',
            $stage->id,
            __('Going live', 'leyka'),
            ['next_label' => __('Send & continue', 'leyka')]
        );
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('You made test donations successfully. To switch your payments to the "live" mode, you must send an email to CloudPayments support. The answer usually comes in during the following day.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'going_live_to',
            'custom_setting_id' => 'going_live_to',
            'field_type' => 'legend',
            'data' => ['title' => __('To whom', 'leyka'), 'text' => $this->_cp_email,],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'going_live_from',
            'custom_setting_id' => 'going_live_from',
            'field_type' => 'text',
            'data' => ['title' => __('From whom', 'leyka'), 'value' => get_option('admin_email'), 'required' => true,],
        ]))
        ->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'going_live_email_subject',
            'custom_setting_id' => 'going_live_email_subject',
            'field_type' => 'text',
            'data' => [
                'title' => __('Email topic', 'leyka'),
                'value' => sprintf(__('Please switch %s to the live mode', 'leyka'), preg_replace("/^http[s]?:\/\//", "", site_url())),
                'required' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'going_live_email_text',
            'custom_setting_id' => 'going_live_email_text',
            'field_type' => 'textarea',
            'data' => [
                'title' => __('Email text', 'leyka'),
                'value' => __("We checked everything. Test donations work normally. The site meets specifications. We are ready to receive money.\nThanks!", 'leyka'),
                'required' => true,
            ],
        ]))->add_handler([$this, 'handle_going_live'])->add_to($stage);

        $section = new Leyka_Settings_Section('cp_live_payment_tryout',  $stage->id, __('Live donation check', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Time to check that you really have switched into "live mode", and the real money payments would work properly.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'live-payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => ['payment_tryout_completed'],
            'rendering_type' => 'template',
            'data' => ['required' => __('Perform the payment to continue.', 'leyka'), 'is_live' => true]
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // The main CP settings stage - End

        // Final Stage:
        $stage = new Leyka_Settings_Stage('final', __('Finish', 'leyka'));

        $section = new Leyka_Settings_Section('cp_final', $stage->id, __('Congratulations!', 'leyka'), ['header_classes' => 'greater',]);
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('You have completed CloudPayments setup. Donations via Visa, MasterCard and MIR bank cards are available now.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'cp-final',
            'template' => 'cp_final',
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // Final Stage - End

    }

    protected function _init_navigation_data() {

        $this->_navigation_data = [
            [
                'stage_id' => 'cp',
                'title' => mb_strtoupper(__('CloudPayments', 'leyka')),
                'url' => '',
                'sections' => [
                    [
                        'section_id' => 'prepare_documents',
                        'title' => __('Preparing the documents', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'send_documents',
                        'title' => __('Sending the documents', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'signin_cp_account',
                        'title' => __('Logging in CloudPayments dashboard', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'copy_key',
                        'title' => __('Copy and save the connection settings', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'check_payment_request',
                        'title' => __('Request', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'accepted_payment_notification',
                        'title' => sprintf(__('Notification #%d', 'leyka'), 1),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'rejected_payment_notification',
                        'title' => sprintf(__('Notification #%d', 'leyka'), 2),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'notification_email',
                        'title' => __('Notifications email', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'cp_payment_tryout',
                        'title' => __('Test donation', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'cp_going_live',
                        'title' => __('Live mode', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'cp_live_payment_tryout',
                        'title' => __('Live payment testing', 'leyka'),
                        'url' => '',
                    ],
                    
                ],
            ],
            ['stage_id' => 'final', 'title' => __('Finish', 'leyka'), 'url' => '',],
        ];

    }

    protected function _get_section_navigation_position($section_full_id = false) {

        $section_full_id = $section_full_id ? trim(esc_attr($section_full_id)) : $this->get_current_section()->full_id;

        switch($section_full_id) {
            case 'cp-init': return 'cp'; break;
            default: return $section_full_id;
        }

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

        if($section->stage_id === 'cp' && $section->id === 'init') {
            $submit_settings['prev'] = false;   // I. e. the Wizard shouln't display the back link
        } else if($section->stage_id === 'final') {

            $submit_settings['next_label'] = __('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function handle_first_step(array $section_settings) {

        $gateway = leyka_get_gateway_by_id('cp');

        foreach($gateway->get_options_names() as $option_id) {
            leyka_save_option($option_id);
        }

        if($gateway->is_setup_complete()) {

            wp_redirect(admin_url('admin.php?page=leyka_settings&stage=payment&gateway=cp'));
            die();

        }

        return true;

    }

    public function handle_send_documents(array $section_settings) {
        error_log("run handle_send_documents");

        if(leyka_options()->opt('plugin_demo_mode')) { // Don't send emails to the gateway when in demo mode
            return true;
        }

        $errors = [];
        
        if( empty($_POST['leyka_send_documents_file']) ) {
            $errors[] = new WP_Error('application_file_not_selected', 'Файл не выбран!');
        }
        
        if( !$errors ) {

            $headers = [];
            $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_send_documents_from']);

            $attachments = [];
            
            $upload_dir = wp_get_upload_dir();
            $attachments[] = $upload_dir['path'] . $_POST['leyka_send_documents_file'];
            
            $res = wp_mail(
                $this->_cp_email,
                $_POST['leyka_send_documents_email_subject'],
                $_POST['leyka_send_documents_email_text'],
                $headers,
                $attachments
            );
            /** @todo Debug if( !$res ) { return [new WP_Error('email_not_sent', 'Email не отправлен!')]; } */
            
            $_SESSION['leyka-cp-notif-documents-sent'] = true;

        }

        return $errors ? : true;

    }
    
    public function handle_going_live(array $step_settings) {

        $available_pms = leyka_options()->opt('pm_available');
        $available_pms[] = 'cp-card';
        $available_pms = array_unique($available_pms);
        leyka_options()->opt('pm_available', $available_pms);

        $pm_order = [];
        foreach($available_pms as $pm_full_id) {
            if($pm_full_id) {
                $pm_order[] = "pm_order[]={$pm_full_id}";
            }
        }
        leyka_options()->opt('pm_order', implode('&', $pm_order));

        if(leyka_options()->opt('plugin_demo_mode')) { // Don't send emails to the gateway when in demo mode
            return true;
        }
        
        $headers = [];
        $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_going_live_from']);
        
        $res = wp_mail(
            $this->_cp_email,
            $_POST['leyka_going_live_email_subject'],
            $_POST['leyka_going_live_email_text'],
            $headers
        );
        /** @todo Debug if( !$res ) { return [new WP_Error('email_not_sent', 'Email не отправлен!')]; } */
        
        return true;
        
    }

}