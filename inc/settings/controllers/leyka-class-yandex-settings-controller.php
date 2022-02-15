<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Yandex_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'yandex';
        $this->_title = __('Yandex.Kassa setup Wizard', 'leyka');

    }

    protected function _load_frontend_scripts() {

        wp_enqueue_script('leyka-easy-modal', LEYKA_PLUGIN_BASE_URL.'js/jquery.easyModal.min.js', [], false, true);

        wp_localize_script('leyka-settings', 'leyka_wizard_yandex', []);

        parent::_load_frontend_scripts();

    }

    protected function _set_stages() {

        // The main Yandex Kassa settings section:
        $stage = new Leyka_Settings_Stage('yandex', __('Yandex.Kassa', 'leyka'));

        $section = new Leyka_Settings_Section('init',  $stage->id, __('Yandex.Kassa', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('Accepting payments with Visa, Mastercard, Maestro, MIR bank cards and others. Commission of 2.8% for payments with bank cards without paying for connection and no monthly fee. Yandex.Kassa for entrepreneurs and legal entities. Details <a rel="nofollow" href="https://kassa.yandex.ru/fees/" target="_blank">online</a>.', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'yandex-payment-cards-icons',
            'template' => 'yandex_payment_cards_icons',
        ]))->add_handler([$this, 'handle_first_step'])->add_to($stage);

        $section = new Leyka_Settings_Section('start_connection',  $stage->id, __('Start of the connection', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'start-connection-intro',
            'text' => __('In this section, you are going to fill general information fields about the organization that wishes to collect. Yandex.Kassa needs these data for a decision on cooperation with you.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'start-connection-follow-link',
            'custom_setting_id' => 'yandex_start_connection_follow_link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Open the address', 'leyka'),
                'value_url' => 'https://kassa.yandex.ru/joinups'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'start-connection-copy-org-inn',
            'custom_setting_id' => 'yandex_start_connection_copy_org_inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'keys' => ['leyka_org_inn'],
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Copy your organization Individual Taxpayer Number', 'leyka'),
                'option_id' => 'org_inn',
                'option_title' => __('Paste your organization Individual Taxpayer Number', 'leyka'),
                'option_comment' => __('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'copy2clipboard' => true,
                'required' => true,
            ]
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'start-connection-fill-inn',
            'custom_setting_id' => 'yandex_start_connection-fill-inn',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Insert the INN in the form and click <strong>"Continue"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_start_connection-inn-input.png'
            ],
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        $section = new Leyka_Settings_Section('general_info',  $stage->id, __('Filling the general information', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'general-info-intro',
            'text' => __('In this section, you are going to fill general information about organization that wishes to connect. Yandex.Kassa needs these data for a decision on cooperation with you.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-click-fill',
            'custom_setting_id' => 'yandex_general_info_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Fill"</strong> button', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info-click-fill.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-click-general-info',
            'custom_setting_id' => 'yandex_general_info-click_general_info',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"General"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-fill-form',
            'custom_setting_id' => 'yandex_general_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Fill in the form using the guidelines below', 'leyka'),
                'screenshot' => 'yandex/yandex_general_info-fill-form.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-site-address',
            'custom_setting_id' => 'yandex_general_info_site_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Website address:', 'leyka'),
                'value_text' => preg_replace("/^http[s]?:\/\//", "", site_url()),
                'copy2clipboard' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-turnover',
            'custom_setting_id' => 'yandex_general_info_turnover',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>Approximate monthly online payments amount</strong>', 'leyka'),
                'text' => __('If you find it hard to assess the amount, then select <strong>"Up to 1 million RUB"</strong>.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-licence-required',
            'custom_setting_id' => 'yandex_general_info-licence-required',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>Subject to mandatory licensing</strong>', 'leyka'),
                'text' => __('Skip this paragraph if you do not have licensed activity. In most cases, non-profit organizations do not need a license.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-has-benificiar-owner',
            'custom_setting_id' => 'yandex_general_info_has_benificiar_owner',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>The organization has beneficial owner</strong>', 'leyka'),
                'text' => __('If you are a non-profit organization, then leave empty, since non-profit organizations do not have beneficial owners. If this is your case, choose <strong>"Other"</strong> and manually write <strong>"non-profit organization"</strong>.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-has-benificiars',
            'custom_setting_id' => 'yandex_general_info-has-benificiars',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<b>There are beneficiaries</b>', 'leyka'),
                'text' => __('By default, the flag is removed from the field - skip this paragraph.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-not-bankrupt',
            'custom_setting_id' => 'yandex_general_info_not_bankrupt',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>I confirm the absence of the legal proceedings on bankruptcy</strong>.', 'leyka'),
                'text' => __('Check the field', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-funds-origin',
            'custom_setting_id' => 'yandex_general_info_funds_origin',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>Funds source</strong>', 'leyka'),
                'text' => __('Select the item <strong>"Other"</strong> and in the box appeared write <strong>"donations"</strong>.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-reputation',
            'custom_setting_id' => 'yandex_general_info_reputation',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('<strong>Business reputation</strong>', 'leyka'),
                'text' => __('Select the first or second paragraph, whichever is best for you.', 'leyka')
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'general-info-click-save',
            'custom_setting_id' => 'yandex_general_info_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('After filling, click <strong>"Save"</strong>.', 'leyka')
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('contact_info',  $stage->id, __('Filling contact information', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'contact-info-intro',
            'text' => __('In this section we fill contact information of your organization.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'contact-info-click',
            'custom_setting_id' => 'yandex_contact_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Contact"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_contact_info-click.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'contact-info-add-contacts',
            'custom_setting_id' => 'yandex_contact_info_add_contacts',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('(optional) Add those who are involved in connecting and working with Yandex.Kassa (for example, a programmer or accountant). After filling, click the <strong>"Save"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_contact_info-save.png'
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('gos_reg',  $stage->id, __('State registration information', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'gos-reg-intro',
            'text' => __('In this section we fill state registration information of your organization.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'gos-reg-click',
            'custom_setting_id' => 'yandex_gos_reg_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"State registration"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_gos_reg-click.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'gos-reg-copy-address',
            'custom_setting_id' => 'yandex_gos_reg_copy_address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => ['leyka_org_address'],
            'data' => [
                'caption' => __('Copy and paste the organization registration address in the form below', 'leyka'),
                'option_id' => 'org_address',
                'option_title' => __('Paste the organization registration address', 'leyka'),
                'option_comment' => __('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'gos-reg-fill-address_screenshot',
            'custom_setting_id' => 'yandex_gos_reg_fill_address_screenshot',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => [
                'screenshot' => 'yandex/yandex_gos_reg-fill-address.png',
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'gos-reg-fill-address',
            'custom_setting_id' => 'yandex_gos_reg-fill-address',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('Fill in your organization actual address. If the actual address matches the address registration, paste this address again. After filling, click <strong>"Save"</strong>.', 'leyka')
            ],
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        $section = new Leyka_Settings_Section('bank_account',  $stage->id, __('Bank account', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'bank-account-intro',
            'text' => __('In this section we fill your organization bank essentials data.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'bank-account-click',
            'custom_setting_id' => 'yandex_bank_account_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Bank account"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_bank_account-click.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'bank-account-copy-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_copy_bank_bic',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => ['leyka_org_bank_bic'],
            'data' => [
                'caption' => __('Copy the BIC of your organization bank and insert into the field', 'leyka'),
                'option_id' => 'org_bank_bic',
                'option_title' => __('Insert the BIC of your organization bank', 'leyka'),
                'option_comment' => __('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'bank-account-fill-bank-bic',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_bic',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => [
                'screenshot' => 'yandex/yandex_bank_account-fill-bank-bic.png',
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'bank-account-fill-bank-account',
            'custom_setting_id' => 'yandex_bank_account_fill_bank_account',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => ['leyka_org_bank_account'],
            'data' => [
                'caption' => __('Copy the account number of your organization and paste it into the form:', 'leyka'),
                'option_id' => 'org_bank_account',
                'option_title' => __('Insert the bank account number of your organization', 'leyka'),
                'option_comment' => __('This data is not in the plugin settings. Fill them here, please - they still come in handy', 'leyka'),
                'show_text_if_set' => true,
                'required' => true,
                'copy2clipboard' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'bank-account-click-save',
            'custom_setting_id' => 'yandex_bank_account_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('After filling, click <strong>"Save"</strong>', 'leyka'),
            ],
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        $section = new Leyka_Settings_Section('boss_info',  $stage->id, __('Filling the organization head person data', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'boss-info-intro',
            'text' => __('In this section we fill your organization head person data.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'boss-info-click',
            'custom_setting_id' => 'yandex_boss_info_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Head person data"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_boss_info-click.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'boss-info-fill-form',
            'custom_setting_id' => 'yandex_boss_info_fill_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('At this point you should have your head person\'s passport scans. Enter all the necessary data. After filling, click <strong>"Save"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_boss_info-fill-form.png'
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('upload_documents',  $stage->id, __('Uploading the documents', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'upload-documents-intro',
            'text' => __('In this section we will upload your organization documents.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'upload-documents-click',
            'custom_setting_id' => 'yandex_upload_documents_click',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Upload documents"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_upload_documents-click.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'upload-documents-add-file',
            'custom_setting_id' => 'yandex_upload_documents_add_files',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('In turn, upload the documents. Click the <strong>"Select a file"</strong> button and add needed files.', 'leyka'),
                'screenshot' => 'yandex/yandex_upload_documents-add-file.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'upload-documents-explain',
            'custom_setting_id' => 'yandex_upload_documents_explain',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('<p>If you are representing a registered non-profit organization, load the scan of the certificate of the Ministry of Justice registration in the "Other Documents" field.</p><p>If you don\'t have such document, skip this step </p>', 'leyka'),
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'upload-documents-click-save',
            'custom_setting_id' => 'yandex_upload_documents_click_save',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('After filling, click <strong>"Save"</strong>', 'leyka'),
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('send_form',  $stage->id, __('Sending the organization profile to check', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'send-form-intro',
            'text' => __('Sending the necessary data to Yandex.Kassa', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send-form-send-form',
            'custom_setting_id' => 'yandex_send_form_send_form',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => ['caption' => __('When all data fields are filled, click <strong>"Send profile"</strong>', 'leyka'),],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send-form',
            'custom_setting_id' => 'yandex_send_form_',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('<p>The process of checking the profile takes about 2-3 business days. You will receive notification email upon the completion of the inspection. In addition, you can learn about the inspection completion in your <a href="https://kassa.yandex.ru/" target="_blank">Yandex.Kassa Dashboard</a></p><p>Now you can get out of this setup Wizard. We will remember where you stopped.</p>', 'leyka'),
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('sign_documents',  $stage->id, __('Signing the documents', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'sign-documents-intro',
            'text' => __('Yandex.Kassa manager will check the profile and create an application for a connection, which will be available for download in your Yandex.Kassa Dashboard. The application shall be deemed as your contract with Yandex.Kassa.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'sign-documents-download',
            'custom_setting_id' => 'yandex_sign_documents_download',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => ['caption' => __('Download the documents from Yandex.Kassa Dashboard', 'leyka'),],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'sign-documents-upload',
            'custom_setting_id' => 'yandex_sign_documents_upload',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'text' => __('<p>Print all pages of the application. On the last application page, specify the signature date, get a sign from the organization head and put the organization seal</p><p>Download the scans of all the application pages in the Members area of Yandex.Kassa Dashboard</p>', 'leyka'),
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('settings', $stage->id, __('Filling in the Settings area', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'settings-intro',
            'text' => __('We came to the technical settings of the Yandex.Kassa & Leyka connection.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'settings-click-fill',
            'custom_setting_id' => 'yandex_settings_click_fill',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Click the <strong>"Fill"</strong> item', 'leyka'),
                'screenshot' => 'yandex/yandex_settings-click.png',
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'settings-payment-module',
            'custom_setting_id' => 'yandex_settings_payment_module',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Select <strong>"payment module"</strong> by clicking on the circle in front of the item, then click <strong>"Continue"</strong>', 'leyka'),
                'screenshot' => 'yandex/yandex_settings-payment-module.png',
            ],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('parameters',  $stage->id, __('Filling in the Parameters area', 'leyka'));
        $section->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'parameters',
            'custom_setting_id' => 'yandex_parameters',
            'field_type' => 'custom_yandex_parameters',
            'keys' => ['leyka_yandex_shop_password'],
            'rendering_type' => 'template',
        ]))->add_handler([$this, 'handle_save_options'])->add_to($stage);

        $section = new Leyka_Settings_Section('online_kassa',  $stage->id, __('Filling in the "Online cashbox" area', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'online-kassa-intro',
            'text' => __('NGOs do not have to use "online cashboxes", so click <strong>"Self"</strong> and then click <strong>"Send"</strong>.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'online-kassa-send',
            'custom_setting_id' => 'yandex_online_kassa_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => ['screenshot' => 'yandex/yandex_online_kassa.png',],
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section('send2check',  $stage->id, __('Sending the data to check', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'send2check-intro',
            'text' => __('This process usually takes 2-3 business days. Once your data are validated, you should receive an email notification.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'send2check-send',
            'custom_setting_id' => 'yandex_send2check_send',
            'field_type' => 'custom_yandex_screenshot',
            'rendering_type' => 'template',
            'data' => ['screenshot' => 'yandex/yandex_send2check.png',],
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'send2check-outro',
            'text' => __('You can exit the setup Wizard for now - Leyka is going to remember where you stopped.', 'leyka'),
        ]))->add_to($stage);

        $section = new Leyka_Settings_Section(
            'fill_leyka_data',
            $stage->id,
            __('Filling in the settings in Leyka', 'leyka'),
            ['next_label' => __('Save & continue', 'leyka')]
        );
        $section->add_block(new Leyka_Text_Block([
            'id' => 'fill-leyka-data-intro',
            'text' => __('We are close to the finish! It is necessary to complete the technical connection between Leyka and Yandex.Kassa.', 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'fill-leyka-data-follow-link',
            'custom_setting_id' => 'yandex_fill_leyka_data-follow-link',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Log in to <a href="https://kassa.yandex.ru/joinups" target="_blank">Yandex.Kassa Dashboard</a>', 'leyka'),
                'value_url' => ''
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'fill-leyka-data-copy-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Copy the <strong>"ShopID"</strong> value', 'leyka'),
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-shop-id.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'fill-leyka-data-paste-shop-id',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-shop-id',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => ['leyka_yandex_shop_id'],
            'data' => [
                'option_id' => 'yandex_shop_id',
                'option_title' => __('Paste the value in the field', 'leyka'),
                'option_placeholder' => 'Ваш ShopID',
                'required' => true,
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'fill-leyka-data-copy-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-copy-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'data' => [
                'caption' => __('Copy the <strong>"Secret key"</strong> value', 'leyka'),
                'screenshot' => 'yandex/yandex_fill_leyka_data-copy-secret-key.png'
            ],
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'fill-leyka-data-paste-secret-key',
            'custom_setting_id' => 'yandex_fill_leyka_data-paste-secret-key',
            'field_type' => 'custom_yandex_enumerated_block',
            'rendering_type' => 'template',
            'keys' => ['leyka_yandex_secret_key'],
            'data' => [
                'option_id' => 'yandex_secret_key',
                'option_title' => __('Paste the value in the field', 'leyka'),
                'option_placeholder' => __('Secret key', 'leyka'),
                'required' => true,
            ],
        ]))->add_handler([$this, 'handle_save_leyka_data'])->add_to($stage);

        $section = new Leyka_Settings_Section('test_payment',  $stage->id, __('Checking the "almost real" donation', 'leyka'));
        $section->add_block(new Leyka_Text_Block([
            'id' => 'test-payment-intro',
            'text' => __("Let's check Yandex.Kassa work by donating 1 RUB to ourselves. After the donation, the funds will be credited to the account specified in Yandex.Kassa earlier within 1 business day", 'leyka'),
        ]))->add_block(new Leyka_Custom_Setting_Block([
            'id' => 'test-payment',
            'custom_setting_id' => 'yandex_test_payment',
            'field_type' => 'custom_yandex_test_payment',
            'keys' => ['payment_completed'],
            'rendering_type' => 'template',
            'data' => ['required' => __('To continue, you must make a donation.', 'leyka')]
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;

        // Final Section:
        $stage = new Leyka_Settings_Stage('final', __('Finish', 'leyka'));

        $section = new Leyka_Settings_Section('yandex_final', $stage->id, __('Congratulations!', 'leyka'), ['header_classes' => 'greater',]);
        $section->add_block(new Leyka_Text_Block([
            'id' => 'section-intro-text',
            'text' => __('<p>You have successfully set up Yandex.Kassa gateway. Donations by bank cards, Yandex.Money, and other means now available.</p><p>Test the donations yourself and share your campaign with friends: ask them to make a donation to you.</p>', 'leyka'),
        ]))->add_block(new Leyka_Text_Block([
            'id' => 'yandex-final',
            'template' => 'yandex_final',
        ]))->add_to($stage);

        $this->_stages[$stage->id] = $stage;
        // Final Section - End

    }

    protected function _init_navigation_data() {

        $this->_navigation_data = [
            [
                'stage_id' => 'yandex',
                'title' => __('Yandex.Kassa', 'leyka'),
                'url' => '',
                'sections' => [
                    [
                        'section_id' => 'start_connection',
                        'title' => __('Start of the connection', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'general_info',
                        'title' => __('General information', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'contact_info',
                        'title' => __('Contact information', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'gos_reg',
                        'title' => __('State registration', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'bank_account',
                        'title' => __('Bank account', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'boss_info',
                        'title' => __('Head person data', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'upload_documents',
                        'title' => __('Uploading the documents', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'send_form',
                        'title' => esc_html_x('Sending out the profile', 'Short version', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'sign_documents',
                        'title' => __('Signing the documents', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'settings',
                        'title' => __('Settings', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'parameters',
                        'title' => __('Parameters', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'online_kassa',
                        'title' => __('Online cashbox', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'send2check',
                        'title' => __('Profile checking', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'fill_leyka_data',
                        'title' => __('Leyka setup', 'leyka'),
                        'url' => '',
                    ],
                    [
                        'section_id' => 'test_payment',
                        'title' => __('Live payment testing', 'leyka'),
                        'url' => '',
                    ],
                ],
            ],
            [
                'stage_id' => 'final',
                'title' => __('Finish', 'leyka'),
                'url' => '',
            ],
        ];

    }

    protected function _get_section_navigation_position($section_full_id = false) {

        $section_full_id = $section_full_id ? trim(esc_attr($section_full_id)) : $this->get_current_section()->full_id;

        switch($section_full_id) {
            case 'yandex-init': return 'yandex'; break;
            default: return $section_full_id;
        }

    }

    public function get_submit_data($component = null) {

        $section = $component && is_a($component, 'Leyka_Settings_Section') ? $component : $this->current_section;
        $submit_settings = [
            'next_label' => __('Continue', 'leyka'),
            'next_url' => true,
            'prev' => __('Go back to the previous step', 'leyka'),
        ];

        if($section->next_label) {
            $submit_settings['next_label'] = $section->next_label;
        }

        if($section->stage_id === 'yandex' && $section->id === 'init') {
            $submit_settings['prev'] = false;   // The Wizard shouldn't display the back link
        } else if($section->stage_id === 'final') {

            $submit_settings['next_label'] = __('Go to the Dashboard', 'leyka');
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function handle_save_options(array $section_settings) {

        $errors = [];

        foreach($section_settings as $option_id => $value) {
            leyka_save_option(preg_replace('/^leyka_/', '', $option_id));
        }

        return !empty($errors) ? $errors : true;

    }

    public function handle_first_step(array $section_settings) {

        $gateway = leyka_get_gateway_by_id('yandex');

        foreach($gateway->get_options_names() as $option_id) {
            leyka_save_option($option_id);
        }

        if($gateway->is_setup_complete()) {

            wp_redirect(admin_url('admin.php?page=leyka_settings&stage=payment&gateway=yandex'));
            die();

        }

        return true;

    }

    public function handle_save_leyka_data(array $section_settings) {

        if($this->handle_save_options($section_settings) === true) {

            $available_pm_list = leyka_options()->opt('pm_available');
            $available_pm_list[] = 'yandex-yandex_card';
            $available_pm_list[] = 'yandex-yandex_money';
            $available_pm_list[] = 'yandex-yandex_all';
            $available_pm_list = array_unique($available_pm_list);

            leyka_options()->opt('pm_available', $available_pm_list);

            $pm_order = [];
            foreach($available_pm_list as $pm_full_id) {
                if($pm_full_id) {
                    $pm_order[] = "pm_order[]={$pm_full_id}";
                }
            }

            leyka_options()->opt('pm_order', implode('&', $pm_order));

        }

    }

}