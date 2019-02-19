const {Browser, By, Key, until} = require("selenium-webdriver");

class InitWizardPage {

    constructor(driver) {

        this.driver = driver;
        this.url = 'http://leyka-test.kandinsky.tmweb.ru/wp-login.php?redirect_to=http%3A%2F%2Fleyka-test.kandinsky.tmweb.ru%2Fwp-admin%2Fadmin.php%3Fpage%3Dleyka_settings_new%26screen%3Dwizard-init%26reset%3D1&reauth=1';
        this.test_account_login = 'leyka_tester';
        this.test_account_pass = '19851308cdCDyENT,leyka-test';

        this.terms_placeholders = [
            '#SITE_NAME#', '#SITE_EMAIL#', '#ORG_NAME#', '#DONATION_ID#', '#DONATION_TYPE#', '#DONOR_NAME#', '#DONOR_EMAIL#',
            '#SUM#', '#PAYMENT_METHOD_NAME#', '#CAMPAIGN_NAME#', '#CAMPAIGN_TARGET#', '#PURPOSE#', '#DATE#',
        ];

        this.locators = {
            wp_login_form: By.id('loginform'),
            wp_login: By.id('user_login'),
            wp_pass: By.id('user_pass'),
            wp_login_submit: By.id('wp-submit'),
            navigation_area: By.css('.nav-area'),
            receiver_country_field: By.id('leyka_receiver_country-field'),
            receiver_type_field: function(type_value){
                return By.css('input[name="leyka_receiver_legal_type"][value="'+type_value+'"]');
            },
            placeholder_text_field: function(option_name){
                return By.css('.leyka_'+option_name+'-field');
            },
            sending_plugin_stats_agreement_field: function(type_value){
                return By.css('input[name="leyka_send_plugin_stats"][value="'+type_value+'"]');
            },
            wizard_step_title: By.css('.step-title h1'),
            wizard_step_submit: By.css('.step-submit input[name="leyka_settings_submit_init"]'),
            edit_slug_wrapper: By.css('.inline-edit-slug-form'),
            edit_slug_field: By.css('input.leyka-slug-field'),
            edit_slug_button_ok: By.css('.inline-edit-slug-form .inline-submit'),
            edit_slug_button_cancel: By.css('.inline-edit-slug-form .inline-reset'),
            campaign_shortcode: By.css('.leyka-wizard-copy2clipboard .leyka-current-value')
        };

    }

    async open() {

        await this.driver.get(this.url);
        await this.driver.manage().window().maximize();

    }

    async loginIntoWizardPage() {

        let wp_login_form = await this.driver.findElements(this.locators.wp_login_form);

        if(wp_login_form.length > 0) { // Login needed - checking

            let login_field = await this.driver.wait(until.elementLocated(this.locators.wp_login)),
                pass_field = await this.driver.wait(until.elementLocated(this.locators.wp_pass)),
                submit = await this.driver.wait(until.elementLocated(this.locators.wp_login_submit));

            await login_field.sendKeys(this.test_account_login);
            await this.driver.sleep(250);
            await pass_field.sendKeys(this.test_account_pass);
            await this.driver.sleep(250);
            await submit.click();
            await this.driver.sleep(250);

        }

    }

    async isNavigationAreaInState(section_name_to_check, step_name_to_check) {

        section_name_to_check = section_name_to_check.length ? section_name_to_check.toString() : '';
        step_name_to_check = step_name_to_check.length ? step_name_to_check.toString() : !!step_name_to_check;

        let section_status_class = step_name_to_check.length || !step_name_to_check ? '.active' : '.done',
            section_title_data_attr = section_name_to_check ? '[data-section-title="'+section_name_to_check+'"]' : '',
            active_section = await this.driver
            .findElement(this.locators.navigation_area)
            .findElements(By.css('.nav-section' + section_status_class + section_title_data_attr));

        if( !active_section.length && section_name_to_check.length ) {
            return false;
        } else {
            active_section = active_section[0];
        }

        let active_step = await active_section.findElements(By.css('.nav-steps .nav-step.active'));
        if( !step_name_to_check.length && !active_step.length ) { // Don't need to check
            return true;
        } else if( !active_step.length && step_name_to_check.length ) {
            return false;
        } else if(active_step.length && !step_name_to_check.length) {
            return false;
        } else {
            active_step = active_step[0];
        }

        let real_step_name = await active_step.getText();
        if(step_name_to_check.length && !real_step_name.includes(step_name_to_check)) {
            return false;
        }

        return true;

    }

    async selectReceiverCountry(country_value) {

        await this.driver.findElement(this.locators.receiver_country_field)
            .findElement(By.css('option[value="' + country_value + '"]'))
            .click();

    }

    async selectReceiverType(type_value) {
        await this.driver
            .findElement(this.locators.receiver_type_field(type_value))
            .click();
    }

    async getCurrentStepTitle() {
        return await this.driver.findElement(this.locators.wizard_step_title).getText();
    }

    async submitStep() {
        await this.driver.findElement(this.locators.wizard_step_submit).click();
    }

    async unsetRequiredFields(fields_names) {

        if( !fields_names.length ) {
            return;
        }

        for(let i = 0; i < fields_names.length; i++) {

            let field = await this.driver.findElement(By.css('input[name="leyka_' + fields_names[i] + '"]'));
            await field.clear();

        }

    }

    async requiredFieldsErrorsShown(fields_names) {

        if( !fields_names.length ) {
            return;
        }

        let all_errors_shown = true;

        for(let i = 0; i < fields_names.length; i++) {

            let field_wrapper = await this.driver.findElement(By.id(fields_names[i]));

            let class_to_check = await field_wrapper.getAttribute('class');
            if( !class_to_check.includes('has-errors') ) {
                all_errors_shown = false;
                break;
            }

            let field_error = field_wrapper.findElement(By.css('.field-errors'));

            class_to_check = await field_error.getAttribute('class');
            if( !class_to_check.includes('has-errors') ) {
                all_errors_shown = false;
                break;
            }

            let error_inner_text = await field_error.getText();
            if( !error_inner_text.length ) {
                all_errors_shown = false;
                break;
            }

        }

        return all_errors_shown;

    }

    async checkFieldMask(field_name, value_incorrect, value_correct) {

        let field = await this.driver.findElement(By.css('input[name="leyka_' + field_name + '"]'));

        await field.clear();
        await this.setMaskedField(field, value_incorrect); // this.setTextField() doesn't work due to masked field behavior

        let value = await field.getAttribute('value');
        if(value.length) {
            return false;
        }

        await field.clear();
        await this.setMaskedField(field, value_correct);

        value = await field.getAttribute('value');

        return value.length && value === value_correct;

    }

    async setTextField(field_name, value) {

        if( !field_name || typeof value === 'undefined' ) {
            return;
        }

        let field = await this.driver.findElement(By.css('[name="leyka_'+field_name+'"]'));
        await field.clear();
        await field.sendKeys(value);

    }

    async setTextFields(fields) {

        if( !fields ) {
            return;
        }

        for(let field_name in fields) {
            await this.setTextField(field_name, fields[field_name]);
            await this.driver.sleep(100);
        }

    }

    /** In FF masked fields do not set correctly with just sendKeys() */
    async setMaskedField(field_web_element, value) {

        if(typeof field_web_element === 'string') {
            field_web_element = this.driver.findElement(By.css('[name="leyka_'+field_web_element+'"]'));
        }

        await this.driver.executeScript("arguments[0].value='" + value + "';", field_web_element);

    }

    async usePlaceholderFieldIframe(field_name) {

        let terms_field_iframe = await this.driver.findElement(By.id('leyka_' + field_name + '-field_ifr'));
        await this.driver.switchTo().frame(terms_field_iframe);
        await this.driver.sleep(250);

    }

    async useCustomIframe(iframe_locator) {

        let iframe = await this.driver.findElement(iframe_locator);
        await this.driver.switchTo().frame(iframe);
        await this.driver.sleep(250);

    }

    async useMainIframe() {
        await this.driver.switchTo().defaultContent();
    }

    async isPlaceholderFieldTextSet(field_name) {

        await this.usePlaceholderFieldIframe(field_name);

        let terms_text = await this.driver.findElement(this.locators.placeholder_text_field(field_name)).getText();

        await this.useMainIframe();

        return !!terms_text.length;

    }

    async isFieldTextWithPlaceholders(field_name, placeholders_to_check) {

        placeholders_to_check = typeof placeholders_to_check === 'undefined' ? [] : placeholders_to_check;

        await this.usePlaceholderFieldIframe(field_name);

        let field_value = await this.driver.findElement(this.locators.placeholder_text_field(field_name)).getText(),
            field_value_includes_placeholders = false;

        placeholders_to_check = placeholders_to_check.length ? placeholders_to_check : this.terms_placeholders;

        for(let i=0; i < placeholders_to_check.length; i++) {
            if(field_value.includes(placeholders_to_check[i])) {

                field_value_includes_placeholders = true;
                break;

            }
        }

        await this.useMainIframe();

        return field_value_includes_placeholders;

    }

    async unsetPlaceholderFieldText(field_name) {

        await this.usePlaceholderFieldIframe(field_name);

        let text_field_element = await this.driver.findElement(this.locators.placeholder_text_field(field_name));

        await this.driver.executeScript("var ele=arguments[0]; ele.innerHTML = '';", text_field_element);

        await this.useMainIframe();

    }

    async statsFieldAgreed() {

        let field_agreed = await this.driver
            .findElement(this.locators.sending_plugin_stats_agreement_field('y'))
            .getAttribute('checked');

        return !!field_agreed;

    }

    /** @param value string 'y' or 'n' */
    async selectStatsAgreement(value) {
        await this.driver
            .findElement(this.locators.sending_plugin_stats_agreement_field(value))
            .click();
    }

    async setFileUploadField(field_name, file_absolute_path) {

        await this.driver
            .findElement(By.id(field_name+'-upload-button'))
            .click();

        await this.driver
            .findElement(By.xpath("//input[starts-with(@id,'html5_')]"))
            .sendKeys(file_absolute_path);

        let upload_button = await this.driver.findElement(By.css('button.media-button-select'));

        await this.driver.wait(until.elementIsEnabled(upload_button));
        await upload_button.click();
        await this.driver.sleep(1000);

    }

    async checkCampaignCardPreview() {

        await this.useCustomIframe(By.css('#leyka-preview-frame iframe'));

        // Wait for the card to reload:
        await this.driver.wait(until.elementLocated(By.css('.inpage-card__thumb')), 5000);
        await this.driver.wait(until.elementLocated(By.css('.inpage-card_title')), 5000);

        let campaign_card_preview_ok = await this.checkCampaignCardDisplay();

        await this.useMainIframe();

        return campaign_card_preview_ok;

    }

    async checkCampaignCardDisplay() {

        let campaign_card_display_ok = true,
            card_thumb_url = await this.driver.findElement(By.css('.inpage-card__thumb')).getAttribute('style');
        if(
            !card_thumb_url.includes('http://leyka-test.kandinsky.tmweb.ru/wp-content/uploads/') ||
            !card_thumb_url.includes('leyka-campaign-thumb-example')
        ) {
            campaign_card_display_ok = false;
        }

        if(campaign_card_display_ok) {

            let card_title = await this.driver.findElement(By.css('.inpage-card_title')).getText();
            if( !card_title.includes('На уставную деятельность') ) {
                campaign_card_display_ok = false;
            }

        }

        if(campaign_card_display_ok) {

            let card_excerpt = await this.driver.findElement(By.css('.inpage-card__excerpt')).getText();
            if( !card_excerpt.includes('Краткое описание того, почему жертвовать нам важно и нужно') ) {
                campaign_card_display_ok = false;
            }

        }

        if(campaign_card_display_ok) {

            let card_target = await this.driver.findElement(By.css('.inpage-card_scale .info')).getText();
            if( !card_target.includes('50 000') ) {
                campaign_card_display_ok = false;
            }

        }

        return campaign_card_display_ok;

    }

    async checkCampaignPermalinkDisplayed(campaign_slug) {

        if(typeof campaign_slug === 'undefined' || !campaign_slug.length) {
            return false;
        }

        let base_url = await this.driver.findElement(By.css('.leyka-campaign-permalink .base-url')).getText();
        if( !base_url.includes('campaign') ) {
            return false;
        }

        let permalink = await this.driver.findElement(By.css('.leyka-campaign-permalink .current-slug')).getText();
        return permalink === campaign_slug;

    }
    
    async openCampaignSlugEditForm() {

        let edit_slug_action = await this.driver.findElement(By.css('.inline-edit-slug')),
            is_displayed = await edit_slug_action.isDisplayed();

        if( !is_displayed ) {
            return;
        }

        await edit_slug_action.click();

    }

    async closeCampaignSlugEditForm(close_type) {

        close_type = typeof close_type === 'undefined' || !close_type.length ? 'cancel' : 'ok';

        if(close_type === 'ok') {
            await this.driver.findElement(this.locators.edit_slug_button_ok).click();
        } else {
            await this.driver.findElement(this.locators.edit_slug_button_cancel).click();
        }

    }

    async campaignSlugEditFormInState(state) {

        if(typeof state === 'undefined' || !state.length) {
            return false;
        }

        let real_state = await this.driver.findElement(By.css('.inline-edit-slug-form')).isDisplayed();

        if(state === 'opened') {
            return !!real_state;
        } else if(state === 'closed') {
            return !real_state;
        } else {
            return false;
        }

    }

    async changeCampaignSlugTo(value) {

        if(typeof value === 'undefined' || !value.length) {
            return false;
        }

        await this.openCampaignSlugEditForm();

        let slug_field = await this.driver.findElement(this.locators.edit_slug_field);
        await slug_field.clear();
        await slug_field.sendKeys(value);

        await this.closeCampaignSlugEditForm('ok');

    }

    async checkCampaignShortcode() {

        let campaign_id = await this.driver.findElement(this.locators.edit_slug_wrapper).getAttribute('data-campaign-id'),
            shortcode = await this.driver.findElement(this.locators.campaign_shortcode).getText();

        return shortcode === '[leyka_inline_campaign id="'+campaign_id+'"]';

    }

    async checkCampaignFrontPageLink() {

        let campaign_page_url = await this.driver
            .findElement(By.css('.init-final-step-go-campaign a.step-next'))
            .getAttribute('href');

        return campaign_page_url.includes('na-ustavnuyu-deyatelnost') && campaign_page_url.includes('campaign');

    }

    async openCampaignFrontPage() {

        await this.driver.findElement(By.css('.final-button .step-next')).click();

        this.browser_tabs = await this.driver.getAllWindowHandles();
        this.browser_main_tab = await this.driver.getWindowHandle();

        await this.driver.switchTo().window(this.browser_tabs[1]);

    }

    async closeCampaignFrontPage() {
        await this.driver.close();
    }

    async returnToMainPage() {
        await this.driver.switchTo().window(this.browser_main_tab);
    }

    async checkCampaignFrontPageTitle() {

        let campaign_page_title = await this.driver.getTitle();
        return !!campaign_page_title.includes('На уставную деятельность');

    }

    async checkCampaignFrontPageUrl() {

        let campaign_page_url = await this.driver.getCurrentUrl();
        return campaign_page_url.includes('na-ustavnuyu-deyatelnost') && campaign_page_url.includes('campaign');

    }

    async quitWizard() {
        await this.driver.findElement(By.css('.nav-exit')).click();
    }

    async checkDefaultSettingsPage() {

        let page_title = await this.driver.getTitle();
        if( !page_title.includes('Настройки') ) {
            return false;
        }

        let page_url = await this.driver.getCurrentUrl();
        if( !page_url.includes('admin.php?page=leyka_settings') ) {
            return false;
        }

        page_title = await this.driver.findElement(By.css('.leyka-settings-page h1')).getText();
        return !!page_title.includes('Настройки');

    }

}

module.exports = InitWizardPage;