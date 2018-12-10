<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Yandex_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'yandex';
        $this->_title = esc_attr__('Yandex.Kassa setup Wizard', 'leyka');

    }

    protected function _loadCssJs() {

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL.'js/jquery.easyModal.min.js', array(), false, true);

        wp_localize_script('leyka-settings', 'leyka_wizard_yandex', array());

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main Yandex Kassa settings section:
        $section = new Leyka_Settings_Section('yandex', esc_html__('Yandex.Kassa', 'leyka'));

        $step = new Leyka_Settings_Step('init',  $section->id, esc_html__('Yandex.Kassa', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => __('Accepting payments with Visa, Mastercard, Maestro, MIR bank cards and others. Commission of 2.8% for payments with bank cards without paying for connection and no monthly fee. Yandex.Kassa for entrepreneurs and legal entities. Details <a rel="nofollow" href="https://kassa.yandex.ru/fees/" target="_blank">online</a>.', 'leyka'),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yandex-payment-cards-icons',
            'template' => 'yandex_payment_cards_icons',
        )))->addTo($section);

        $step = new Leyka_Settings_Step('start_connection',  $section->id, esc_html__('Start of the connection', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'start-connection-intro',
            'text' => esc_html__('In this section, you are going to fill general information fields about the organization that wishes to collect. Yandex.Kassa needs these data for a decision on cooperation with you.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-follow-link',
            'custom_setting_id' => 'yandex_start_connection_follow_link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Open the address', 'leyka'),
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-copy-org-inn',
            'custom_setting_id' => 'yandex_start_connection_copy_org_inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'keys' => array('leyka_org_inn'),
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Copy your organization Individual Taxpayer Number', 'leyka'),
                'option_id' => 'org_inn',
                'option_title' => esc_html__('Paste your organization Individual Taxpayer Number', 'leyka'),
                'option_comment' => esc_html__('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'copy2clipboard' => true,
                'required' => true,
            )
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'start-connection-fill-inn',
            'custom_setting_id' => 'yandex_start_connection-fill-inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Insert the INN in the form and click <strong>"Continue"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_start_connection-inn-input.png'
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        $step = new Leyka_Settings_Step('general_info',  $section->id, esc_html__('Filling the general information', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'general-info-intro',
            'text' => esc_html__('In this section, you are going to fill general information about organization that wishes to connect. Yandex.Kassa needs these data for a decision on cooperation with you.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-fill',
            'custom_setting_id' => 'yandex_general_info_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Fill"</strong> button', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info-click-fill.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-general-info',
            'custom_setting_id' => 'yandex_general_info-click_general_info',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"General"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-fill-form',
            'custom_setting_id' => 'yandex_general_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Fill in the form using the guidelines below', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info-fill-form.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-site-address',
            'custom_setting_id' => 'yandex_general_info_site_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Website address:', 'leyka'),
                'value_text' => preg_replace("/^http[s]?:\/\//", "", site_url()),
                'copy2clipboard' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-turnover',
            'custom_setting_id' => 'yandex_general_info_turnover',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>Approximate monthly online payments amount</strong>', 'leyka'),
                'text' => __('If you find it hard to assess the amount, then select <strong>"Up to 1 million RUB"</strong>.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-licence-required',
            'custom_setting_id' => 'yandex_general_info-licence-required',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>Subject to mandatory licensing</strong>', 'leyka'),
                'text' => esc_html__('Skip this paragraph if you do not have licensed activity. In most cases, non-profit organizations do not need a license.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-has-benificiar-owner',
            'custom_setting_id' => 'yandex_general_info_has_benificiar_owner',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>The organization has beneficial owner</strong>', 'leyka'),
                'text' => __('If you are a non-profit organization, then leave empty, since non-profit organizations do not have beneficial owners. If this is your case, choose <strong>"Other"</strong> and manually write <strong>"non-profit organization"</strong>.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-has-benificiars',
            'custom_setting_id' => 'yandex_general_info-has-benificiars',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<b>There are beneficiaries</b>', 'leyka'),
                'text' => esc_html__('By default, the flag is removed from the field - skip this paragraph.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-not-bankrupt',
            'custom_setting_id' => 'yandex_general_info_not_bankrupt',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>I confirm the absence of the legal proceedings on bankruptcy</strong>.', 'leyka'),
                'text' => esc_html__('Check the field', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-funds-origin',
            'custom_setting_id' => 'yandex_general_info_funds_origin',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>Funds source</strong>', 'leyka'),
                'text' => __('Select the item <strong>"Other"</strong> and in the box appeared write <strong>"donations"</strong>.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-reputation',
            'custom_setting_id' => 'yandex_general_info_reputation',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('<strong>Business reputation</strong>', 'leyka'),
                'text' => esc_html__('Select the first or second paragraph, whichever is best for you.', 'leyka')
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'general-info-click-save',
            'custom_setting_id' => 'yandex_general_info_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('After filling, click <strong>"Save"</strong>.', 'leyka')
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('contact_info',  $section->id, esc_html__('Filling contact information', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'contact-info-intro',
            'text' => esc_html__('In this section we fill contact information of your organization.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'contact-info-click',
            'custom_setting_id' => 'yandex_contact_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Contact"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_contact_info-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'contact-info-add-contacts',
            'custom_setting_id' => 'yandex_contact_info_add_contacts',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('(optional) Add those who are involved in connecting and working with Yandex.Kassa (for example, a programmer or accountant). After filling, click the <strong>"Save"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_contact_info-save.png'
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('gos_reg',  $section->id, esc_html__('State registration information', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'gos-reg-intro',
            'text' => esc_html__('In this section we fill state registration information of your organization.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-click',
            'custom_setting_id' => 'yandex_gos_reg_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"State registration"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_gos_reg-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-copy-address',
            'custom_setting_id' => 'yandex_gos_reg_copy_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_address'),
            'data' => array(
                'caption' => esc_html__('Copy and paste the organization registration address in the form below', 'leyka'),
                'option_id' => 'org_address',
                'option_title' => esc_html__('Paste the organization registration address', 'leyka'),
                'option_comment' => esc_html__('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-fill-address_screenshot',
            'custom_setting_id' => 'yandex_gos_reg_fill_address_screenshot',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_gos_reg-fill-address.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'gos-reg-fill-address',
            'custom_setting_id' => 'yandex_gos_reg-fill-address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('Fill in your organization actual address. If the actual address matches the address registration, paste this address again. After filling, click <strong>"Save"</strong>.', 'leyka')
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        $step = new Leyka_Settings_Step('bank_account',  $section->id, esc_html__('Bank account', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'bank-account-intro',
            'text' => esc_html__('In this section we fill your organization bank essentials data.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-click',
            'custom_setting_id' => 'yandex_bank_account_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Bank account"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_bank_account-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-copy-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_copy_bank_bic',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_bank_bic'),
            'data' => array(
                'caption' => esc_html__('Copy the BIC of your organization bank and insert into the field', 'leyka'),
                'option_id' => 'org_bank_bic',
                'option_title' => esc_html__('Insert the BIC of your organization bank', 'leyka'),
                'option_comment' => esc_html__('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-fill-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_bic',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_bank_account-fill-bank-bic.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-fill-bank-account',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_account',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_org_bank_account'),
            'data' => array(
                'caption' => esc_html__('Copy the account number of your organization and paste it into the form:', 'leyka'),
                'option_id' => 'org_bank_account',
                'option_title' => esc_html__('Insert the bank account number of your organization', 'leyka'),
                'option_comment' => esc_html__('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'bank-account-click-save',
            'custom_setting_id' => 'yandex_bank_account_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('After filling, click <strong>"Save"</strong>', 'leyka'),
            ),
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        $step = new Leyka_Settings_Step('boss_info',  $section->id, esc_html__('Filling the organization head person data', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'boss-info-intro',
            'text' => esc_html__('In this section we fill your organization head person data.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'boss-info-click',
            'custom_setting_id' => 'yandex_boss_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Head person data"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_boss_info-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'boss-info-fill-form',
            'custom_setting_id' => 'yandex_boss_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('At this point you should have your head person\'s passport scans. Enter all the necessary data. After filling, click <strong>"Save"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_boss_info-fill-form.png'
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('upload_documents',  $section->id, esc_html__('Uploading the documents', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'upload-documents-intro',
            'text' => esc_html__('In this section we will upload your organization documents.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-click',
            'custom_setting_id' => 'yandex_upload_documents_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Upload documents"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_upload_documents-click.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-add-file',
            'custom_setting_id' => 'yandex_upload_documents_add_files',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('In turn, upload the documents. Click the <strong>"Select a file"</strong> button and add needed files.', 'leyka'),
                'screenshot' => 'yandex/yandex_upload_documents-add-file.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-explain',
            'custom_setting_id' => 'yandex_upload_documents_explain',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('<p>If you are representing a registered non-profit organization, load the scan of the certificate of the Ministry of Justice registration in the "Other Documents" field.</p><p>If you don\'t have such document, skip this step </p>', 'leyka'),
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'upload-documents-click-save',
            'custom_setting_id' => 'yandex_upload_documents_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('After filling, click <strong>"Save"</strong>', 'leyka'),
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('send_form',  $section->id, esc_html__('Sending the organization profile to check', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send-form-intro',
            'text' => esc_html__('Sending the necessary data to Yandex.Kassa', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send-form-send-form',
            'custom_setting_id' => 'yandex_send_form_send_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('When all data fields are filled, click <strong>"Send profile"</strong>', 'leyka'),
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send-form',
            'custom_setting_id' => 'yandex_send_form_',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('<p>The process of checking the profile takes about 2-3 business days. You will receive notification email upon the completion of the inspection. In addition, you can learn about the inspection completion in your <a href="https://kassa.yandex.ru/" target="_blank">Yandex.Kassa Dashboard</a></p><p>Now you can get out of this setup Wizard. We will remember where you stopped.</p>', 'leyka'),
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('sign_documents',  $section->id, esc_html__('Signing the documents', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'sign-documents-intro',
            'text' => esc_html__('Yandex.Kassa manager will check the profile and create an application for a connection, which will be available for download in your Yandex.Kassa Dashboard. The application shall be deemed as your contract with Yandex.Kassa.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'sign-documents-download',
            'custom_setting_id' => 'yandex_sign_documents_download',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Download the documents from Yandex.Kassa Dashboard', 'leyka'),
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'sign-documents-upload',
            'custom_setting_id' => 'yandex_sign_documents_upload',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'text' => __('<p>Print all pages of the application. On the last application page, specify the signature date, get a sign from the organization head and put the organization seal</p><p>Download the scans of all the application pages in the Members area of Yandex.Kassa Dashboard</p>', 'leyka'),
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('settings', $section->id, esc_html__('Filling in the Settings area', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'settings-intro',
            'text' => esc_html__('We came to the technical settings of the Yandex.Kassa & Leyka connection.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'settings-click-fill',
            'custom_setting_id' => 'yandex_settings_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Click the <strong>"Fill"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_settings-click.png',
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'settings-payment-module',
            'custom_setting_id' => 'yandex_settings_payment_module',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Select <strong>"payment module"</strong> by clicking on the circle in front of the item, then click <strong>"Continue"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_settings-payment-module.png',
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('parameters',  $section->id, esc_html__('Filling in the Parameters area', 'leyka'));
        $step->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'parameters',
            'custom_setting_id' => 'yandex_parameters',
            'field_type' => 'custom_yandex_parameters',
            'keys' => array('leyka_yandex_shop_password'),
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handleSaveOptions'))->addTo($section);

        $step = new Leyka_Settings_Step('online_kassa',  $section->id, esc_html__('Filling in the "Online cashbox" area', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'online-kassa-intro',
            'text' => __('NGOs do not have to use "online cashboxes", so click <strong>"Self"</strong> and then click <strong>"Send"</strong>.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'online-kassa-send',
            'custom_setting_id' => 'yandex_online_kassa_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_online_kassa.png'
            ),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('send2check',  $section->id, esc_html__('Sending the data to check', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'send2check-intro',
            'text' => esc_html__('This process usually takes 2-3 business days. Once your data are validated, you should receive an email notification.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'send2check-send',
            'custom_setting_id' => 'yandex_send2check_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => array(
                'screenshot' => 'yandex/yandex_send2check.png',
            ),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'send2check-outro',
            'text' => esc_html__('You can exit the setup Wizard for now - Leyka is going to remember where you stopped.', 'leyka'),
        )))->addTo($section);

        $step = new Leyka_Settings_Step('fill_leyka_data',  $section->id, esc_html__('Filling in the settings in Leyka', 'leyka'), array('next_label' => esc_html__('Save & continue', 'leyka')));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'fill-leyka-data-intro',
            'text' => esc_html__('We are close to the finish! It is necessary to complete the technical connection between Leyka and Yandex.Kassa.', 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-follow-link',
            'custom_setting_id' => 'yandex_fill_leyka_data-follow-link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => esc_html__('Log in to Yandex.Kassa Dashboard', 'leyka'),
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Copy the <strong>"ShopID"</strong> value', 'leyka'),
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-shop-id.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_yandex_shop_id'),
            'data' => array(
                'option_id' => 'yandex_shop_id',
                'option_title' => esc_html__('Paste the value in the field', 'leyka'),
                'option_placeholder' => 'Ваш ShopID',
                'required' => true,
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-copy-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => array(
                'caption' => __('Copy the <strong>"Secret key"</strong> value', 'leyka'),
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-secret-key.png'
            ),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'fill-leyka-data-paste-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => array('leyka_yandex_secret_key'),
            'data' => array(
                'option_id' => 'yandex_secret_key',
                'option_title' => esc_html__('Paste the value in the field', 'leyka'),
                'option_placeholder' => esc_html__('Secret key', 'leyka'),
                'required' => true,
            ),
        )))->addHandler(array($this, 'handleSaveLeykaData'))->addTo($section);

        $step = new Leyka_Settings_Step('test_payment',  $section->id, esc_html__('Checking the "almost real" donation', 'leyka'));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'test-payment-intro',
            'text' => esc_html__("Let's check Yandex.Kassa work by donating 1 RUB to ourselves. After the donation, the funds will be credited to the account specified in Yandex.Kassa earlier within 1 business day", 'leyka'),
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'test-payment',
            'custom_setting_id' => 'yandex_test_payment',
            'field_type' => 'custom_yandex_test_payment',
            'keys' => array('payment_completed'),
            'rendering_type' => 'template',
            'data' => array('required' => esc_html__('To continue, you must make a donation.', 'leyka'))
        )))->addHandler(array($this, 'handleFinalTest'))->addTo($section);

        $this->_sections[$section->id] = $section;

        // Final Section:
        $section = new Leyka_Settings_Section('final', esc_html__('Finish', 'leyka'));

        $step = new Leyka_Settings_Step('yandex_final', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => __('<p>You have successfully set up Yandex.Kassa gateway. Donations by bank cards, Yandex.Money, and other means now available.</p><p>Test the donations yourself and share your campaign with friends: ask them to make a donation to you.</p>', 'leyka'),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'yandex-final',
            'template' => 'yandex_final',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    protected function _initNavigationData() {

        $this->_navigation_data = array(
            array(
                'section_id' => 'yandex',
                'title' => esc_html__('Yandex.Kassa', 'leyka'),
                'url' => '',
                'steps' => array(
                    array(
                        'step_id' => 'start_connection',
                        'title' => esc_html__('Start of the connection', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'general_info',
                        'title' => esc_html__('General information', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'contact_info',
                        'title' => esc_html__('Contact information', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'gos_reg',
                        'title' => esc_html__('State registration', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'bank_account',
                        'title' => esc_html__('Bank account', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'boss_info',
                        'title' => esc_html__('Head person data', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'upload_documents',
                        'title' => esc_html__('Uploading the documents', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send_form',
                        'title' => esc_html_x('Sending out the profile', 'Short version', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'sign_documents',
                        'title' => esc_html__('Signing the documents', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'settings',
                        'title' => esc_html__('Settings', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'parameters',
                        'title' => esc_html__('Parameters', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'online_kassa',
                        'title' => esc_html__('Online cashbox', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'send2check',
                        'title' => esc_html__('Profile checking', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'fill_leyka_data',
                        'title' => esc_html__('Leyka setup', 'leyka'),
                        'url' => '',
                    ),
                    array(
                        'step_id' => 'test_payment',
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

    protected function _getStepNavigationPosition($step_full_id = false) {

        $step_full_id = $step_full_id ? trim(esc_attr($step_full_id)) : $this->getCurrentStep()->full_id;

        switch($step_full_id) {
            case 'yandex-init': return 'yandex'; break;
            default: return $step_full_id;
        }

    }

    public function getSubmitData($component = null) {

        $step = $component && is_a($component, 'Leyka_Settings_Step') ? $component : $this->current_step;
        $submit_settings = array(
            'next_label' => esc_html__('Continue', 'leyka'),
            'next_url' => true,
            'prev' => esc_html__('Go back to the previous step', 'leyka'),
        );

        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'yandex' && $step->id === 'init') {
            $submit_settings['prev'] = false;   // I. e. the Wizard shouln't display the back link
        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = esc_html__('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function handleSaveOptions(array $step_settings) {

        $errors = array();

        foreach($step_settings as $option_id => $value) {
            leyka_save_option(preg_replace("/^leyka_/", "", $option_id));
        }

        return !empty($errors) ? $errors : true;

    }

    public function handleSaveLeykaData(array $step_settings) {

        if($this->handleSaveOptions($step_settings) === true) {

            $available_pms = leyka_options()->opt('pm_available');
            $available_pms[] = 'yandex-yandex_card';
            $available_pms[] = 'yandex-yandex_money';
            $available_pms[] = 'yandex-yandex_all';
            $available_pms = array_unique($available_pms);
            leyka_options()->opt('pm_available', $available_pms);

            $pm_order = array();
            foreach($available_pms as $pm_full_id) {
                if($pm_full_id) {
                    $pm_order[] = "pm_order[]={$pm_full_id}";
                }
            }
            leyka_options()->opt('pm_order', implode('&', $pm_order));

        }

    }

    public function handleFinalTest() {
    }

}