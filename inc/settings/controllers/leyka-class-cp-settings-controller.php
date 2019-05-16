<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Cp_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;
    protected $_cp_email = '';

    protected function _set_attributes() {

        $this->_id = 'cp';
        $this->_title = esc_attr__('CloudPayments setup Wizard', 'leyka');
        $this->_cp_email = defined('LEYKA_DEBUG') && LEYKA_DEBUG ? 'support@te-st.ru' : 'sales@cloudpayments.ru';

    }

    protected function _load_frontend_scripts() {

        wp_enqueue_script('leyka-cp-widget', 'https://widget.cloudpayments.ru/bundles/cloudpayments', array(), false, true);

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL . 'js/jquery.easyModal.min.js', array(), false, true);

        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

        add_action('admin_enqueue_scripts', function(){

            wp_localize_script('leyka-settings', 'leyka_wizard_cp', array(
                'cp_public_id' => leyka_options()->opt('cp_public_id'),
                'main_currency' => 'RUB',
                'test_donor_email' => get_option('admin_email'),
                'ajax_wrong_server_response' => __('Error in server response. Please report to the website tech support.', 'leyka'),
                'cp_not_set_up' => __('Error in CloudPayments settings. Please report to the website tech support.', 'leyka'),
                'cp_donation_failure_reasons' => array(
                    'User has cancelled' => __('You cancelled the payment', 'leyka'),
                ),
            ));

        });

        parent::_load_frontend_scripts();

    }

    protected function _set_sections() {

        // The main CP settings section:
        $section = new Leyka_Settings_Section('cp', 'CloudPayments');

        $step = new Leyka_Settings_Step('init',  $section->id, esc_html__('CloudPayments', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('CloudPayments system is a multi-profile processing center for handling payments via Visa, MasterCard and MIR payment systems.', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-payment-cards-icons',
            'template' => 'cp_payment_cards_icons',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('prepare_documents',  $section->id, esc_html__('Preparing the documents', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => __('CloudPayments setup starts with documents preparation.<br>Download and fill the needed documents.', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-prepare-documents',
            'template' => 'cp_prepare_documents',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('send_documents',  $section->id, esc_html__('Sending the documents', 'leyka'), array('next_label' => esc_html__('Send the email', 'leyka'), 'form_enctype' => 'multipart/form-data'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => __('<p>When the documents are prepared, they have to be sent to CloudPayments. The form below allows you to send the documents right from the website.</p>
<p>Also you may send the documents from your own email to the CloudPayments email: sales@cloudpayments.ru.</p>
<p>Please note that documents checkup may take until 3 working days.</p>
<p>If you have to close this page, we are going to remember the passed steps and you may always return here.</p>', 'leyka'),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_file',
            'custom_setting_id' => 'send_documents_file',
            'field_type' => 'file',
            'data' => array(
                'title' => esc_html__('Attach the Annex #1', 'leyka'),
                'required' => esc_html__('Select a file', 'leyka'),
            ),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_to',
            'custom_setting_id' => 'send_documents_to',
            'field_type' => 'legend',
            'data' => array(
                'title' => esc_html__('To whom', 'leyka'),
                'text' => $this->_cp_email,
            ),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_from',
            'custom_setting_id' => 'send_documents_from',
            'field_type' => 'text',
            'data' => array(
                'title' => esc_html__('From whom', 'leyka'),
                'value' => get_option('admin_email'),
                'required' => true,
            ),
        )))
        ->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_email_subject',
            'custom_setting_id' => 'send_documents_email_subject',
            'field_type' => 'text',
            'data' => array(
                'title' => esc_html__('Email topic', 'leyka'),
                'value' => esc_html__('Please connect me to CloudPayments system', 'leyka'),
                'required' => true,
            ),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'send_documents_email_text',
            'custom_setting_id' => 'send_documents_email_text',
            'field_type' => 'textarea',
            'data' => array(
                'title' => esc_html__('Email text', 'leyka'),
                'value' => esc_html__("Hello! We'd like to connect to CloudPayments. Our Annex #1 is filled and attached to the email.", 'leyka'),
                'required' => true,
            ),
        )))->add_handler(array($this, 'handleSendDocuments'))->add_to($section);

        $step = new Leyka_Settings_Step('signin_cp_account',  $section->id, esc_html__('Login to your CloudPayments dashboard', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Use the login, password and a link to the dashboard from the CloudPayments email.', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-account-setup-instructions',
            'template' => 'cp_account_setup_instructions',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('copy_key',  $section->id, esc_html__('Copy the ID', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Copy the public ID of your CloudPayments account, as in the screenshot below', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-copy-key',
            'template' => 'cp_copy_key',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('paste_key',  $section->id, esc_html__('Enter the ID to the plugin', 'leyka'), array('next_label' => esc_html__('Save & continue', 'leyka')));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Paste the copied public ID to the field below', 'leyka'),
        )))->add_block(new Leyka_Option_Block(array(
            'id' => 'cp_public_id',
            'option_id' => 'cp_public_id',
            'custom_setting_id' => 'cp_public_id',
            'required' => true,
            'field_type' => 'text',
            'show_title' => false,
            'show_description' => false,
        )))->add_to($section);

        $step = new Leyka_Settings_Step('check_payment_request',  $section->id, esc_html__('Add a request to check donations', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Copy the address:', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-check-payment-request',
            'template' => 'cp_check_payment_request',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('accepted_payment_notification',  $section->id, esc_html__('Add a request to complete donations', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Copy the address:', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-accepted-payment-notification',
            'template' => 'cp_accepted_payment_notification',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('rejected_payment_notification',  $section->id, esc_html__('Add a request to reject donations', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Copy the address:', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-rejected-payment-notification',
            'template' => 'cp_rejected_payment_notification',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('notification_email',  $section->id, esc_html__('E-mail for successful donations notifications', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Copy the email:', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-notification-email',
            'template' => 'cp_notification_email',
        )))->add_to($section);

        $step = new Leyka_Settings_Step('cp_payment_tryout', $section->id, esc_html__('Test donation', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-text-1',
            'text' => esc_html__("Let's see if donations work completely. We can do this by using the test bank card numbers and data below.", 'leyka'),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => array('payment_tryout_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => esc_html__('You must make all of the test donations to proceed', 'leyka')),
        )))->add_to($section);

        $step = new Leyka_Settings_Step('cp_going_live',  $section->id, esc_html__('Going live', 'leyka'), array('next_label' => esc_html__('Send & continue', 'leyka')));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('You made test donations successfully. To switch your payments to the "live" mode, you must send an email to CloudPayments support. The answer usually comes in during the following day.', 'leyka'),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_to',
            'custom_setting_id' => 'going_live_to',
            'field_type' => 'legend',
            'data' => array(
                'title' => esc_html__('To whom', 'leyka'),
                'text' => $this->_cp_email,
            ),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_from',
            'custom_setting_id' => 'going_live_from',
            'field_type' => 'text',
            'data' => array(
                'title' => esc_html__('From whom', 'leyka'),
                'value' => get_option('admin_email'),
                'required' => true,
            ),
        )))
        ->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_email_subject',
            'custom_setting_id' => 'going_live_email_subject',
            'field_type' => 'text',
            'data' => array(
                'title' => esc_html__('Email topic', 'leyka'),
                'value' => sprintf(esc_html__('Please switch %s to the live mode', 'leyka'), preg_replace("/^http[s]?:\/\//", "", site_url())),
                'required' => true,
            ),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'going_live_email_text',
            'custom_setting_id' => 'going_live_email_text',
            'field_type' => 'textarea',
            'data' => array(
                'title' => esc_html__('Email text', 'leyka'),
                'value' => esc_html__("We checked everything. Test donations work normally. The site meets specifications. We are ready to receive money.\nThanks!", 'leyka'),
                'required' => true,
            ),
        )))->add_handler(array($this, 'handleGoingLive'))->add_to($section);

        $step = new Leyka_Settings_Step('cp_live_payment_tryout',  $section->id, esc_html__('Live donation check', 'leyka'));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('Time to check that you really have switched into "live mode", and the real money payments would work properly.', 'leyka'),
        )))->add_block(new Leyka_Custom_Setting_Block(array(
            'id' => 'live-payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'keys' => array('payment_tryout_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => esc_html__('Perform the payment to continue.', 'leyka'), 'is_live' => true)
        )))->add_to($section);
            
        $this->_sections[$section->id] = $section;
        // The main CP settings section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', esc_html__('Finish', 'leyka'));

        $step = new Leyka_Settings_Step('cp_final', $section->id, esc_html__('Congratulations!', 'leyka'), array('header_classes' => 'greater',));
        $step->add_block(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => esc_html__('You have completed CloudPayments setup. Donations via Visa, MasterCard and MIR bank cards are available now.', 'leyka'),
        )))->add_block(new Leyka_Text_Block(array(
            'id' => 'cp-final',
            'template' => 'cp_final',
        )))->add_to($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _init_navigation_data() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'cp',
                'title' => mb_strtoupper(esc_html__('CloudPayments', 'leyka')),
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'prepare_documents',
                        'title' => esc_html__('Preparing the documents', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send_documents',
                        'title' => esc_html__('Sending the documents', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'signin_cp_account',
                        'title' => esc_html__('Logging in CloudPayments dashboard', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'copy_key',
                        'title' => esc_html__('Copy the connection ID', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'paste_key',
                        'title' => esc_html__('Saving the connection ID', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'check_payment_request',
                        'title' => esc_html__('Request', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'accepted_payment_notification',
                        'title' => sprintf(esc_html__('Notification #%d', 'leyka'), 1),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'rejected_payment_notification',
                        'title' => sprintf(esc_html__('Notification #%d', 'leyka'), 2),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'notification_email',
                        'title' => esc_html__('Notifications email', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_payment_tryout',
                        'title' => esc_html__('Test donation', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_going_live',
                        'title' => esc_html__('Live mode', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'cp_live_payment_tryout',
                        'title' => esc_html__('Live payment testing', 'leyka'),
                        'url' => '',
                    ),
                    
                ),
            ),
            array(
                'section_id' => 'final',
                'title' => esc_html__('Finish', 'leyka'),
                'url' => '',
            ),
        );

    }

    protected function _get_step_navigation_position($step_full_id = false) {

        $step_full_id = $step_full_id ? trim(esc_attr($step_full_id)) : $this->get_current_step()->full_id;

        switch($step_full_id) {
            case 'cp-init': return 'cp'; break;
            default: return $step_full_id;
        }

    }

    public function get_submit_data($component = null) {

        $step = $component && is_a($component, 'Leyka_Settings_Step') ? $component : $this->current_step;
        $submit_settings = array(
            'next_label' => esc_html__('Continue', 'leyka'),
            'next_url' => true,
            'prev' => esc_html__('Back to the previous step', 'leyka'),
        );

        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'cp' && $step->id === 'init') {
            $submit_settings['prev'] = false;   // I. e. the Wizard shouln't display the back link
        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = esc_html__('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function handleSendDocuments(array $step_settings) {

        if(leyka_options()->opt('plugin_demo_mode')) { // Don't send emails to the gateway when in demo mode
            return true;
        }

        $errors = array();
        
        if( !isset($_FILES['leyka_send_documents_file']) ) {
            $errors[] = new WP_Error('application_file_not_selected', 'Файл не выбран!');
        }
        
        $moved_file = wp_handle_upload( $_FILES['leyka_send_documents_file'], array( 'test_form' => false ) );
        if(isset($moved_file['error'])) {
            $errors[] = new WP_Error('application_file_upload_error', $moved_file['error']);
        }

        if( !$errors ) {

            $headers = array();
            $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_send_documents_from']);

            $attachments = array();
            $attachments[] = $moved_file['file'];
            
            $res = wp_mail(
                $this->_cp_email,
                $_POST['leyka_send_documents_email_subject'],
                $_POST['leyka_send_documents_email_text'],
                $headers,
                $attachments
            );
            /** @todo Debug if( !$res ) { return array(new WP_Error('email_not_sent', 'Email не отправлен!')); } */
            
            $_SESSION['leyka-cp-notif-documents-sent'] = true;

        }

        return $errors ? $errors : true;

    }
    
    public function handleGoingLive(array $step_settings) {

        $available_pms = leyka_options()->opt('pm_available');
        $available_pms[] = 'cp-card';
        $available_pms = array_unique($available_pms);
        leyka_options()->opt('pm_available', $available_pms);

        $pm_order = array();
        foreach($available_pms as $pm_full_id) {
            if($pm_full_id) {
                $pm_order[] = "pm_order[]={$pm_full_id}";
            }
        }
        leyka_options()->opt('pm_order', implode('&', $pm_order));

        if(leyka_options()->opt('plugin_demo_mode')) { // Don't send emails to the gateway when in demo mode
            return true;
        }
        
        $headers = array();
        $headers[] = sprintf('From: %s <%s>', get_bloginfo('name'), $_POST['leyka_going_live_from']);
        
        $res = wp_mail(
            $this->_cp_email,
            $_POST['leyka_going_live_email_subject'],
            $_POST['leyka_going_live_email_text'],
            $headers
        );
        /** @todo Debug if( !$res ) { return array(new WP_Error('email_not_sent', 'Email не отправлен!')); } */
        
        return true;
        
    }

}