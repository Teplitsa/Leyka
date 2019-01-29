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
            terms_text_field: function(option_name){
                return By.css('.leyka_'+option_name+'-field');
            },
            sending_plugin_stats_agreement_field: function(type_value){
                return By.css('input[name="leyka_send_plugin_stats"][value="'+type_value+'"]');
            },
            wizard_step_title: By.css('.step-title h1'),
            wizard_step_submit: By.css('.step-submit input[name="leyka_settings_submit_init"]'),
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

        await field.sendKeys(value_incorrect);
        let value = await field.getAttribute('value');
        if(value.length) {
            return false;
        }

        await field.sendKeys(value_correct);
        value = await field.getAttribute('value');

        return value.length && value === value_correct;

    }

    async setTextFields(fields) {

        if( !fields ) {
            return;
        }

        for(let field_name in fields) {

            let field = await this.driver.findElement(By.css('[name="leyka_'+field_name+'"]'));
            await field.clear();
            await field.sendKeys(fields[field_name]);

        }

    }

    async useTermsTextFieldIframe(field_name) {

        let terms_field_iframe = await this.driver.findElement(By.id('leyka_' + field_name + '-field_ifr'));
        await this.driver.switchTo().frame(terms_field_iframe);

    }

    async useCustomIframe(iframe_locator) {

        let iframe = await this.driver.findElement(iframe_locator);
        await this.driver.switchTo().frame(iframe);

    }

    async useMainIframe() {
        await this.driver.switchTo().defaultContent();
    }

    async isTermsTextSet(field_name) {

        await this.useTermsTextFieldIframe(field_name);

        let terms_text = await this.driver.findElement(this.locators.terms_text_field(field_name)).getText();

        await this.useMainIframe();

        return !!terms_text.length;

    }

    async isTermsTextWithPlaceholders(field_name) {

        await this.useTermsTextFieldIframe(field_name);

        let terms_text = await this.driver.findElement(this.locators.terms_text_field(field_name)).getText(),
            terms_text_includes_placeholders = false;

        for(let i=0; i < this.terms_placeholders.length; i++) {
            if(terms_text.includes(this.terms_placeholders[i])) {

                terms_text_includes_placeholders = true;
                break;

            }
        }

        await this.useMainIframe();

        return terms_text_includes_placeholders;

    }

    async unsetTermsText(field_name) {

        await this.useTermsTextFieldIframe(field_name);

        let terms_text_element = await this.driver.findElement(this.locators.terms_text_field(field_name));

        await this.driver.executeScript("var ele=arguments[0]; ele.innerHTML = '';", terms_text_element);

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

    }

    async checkCampaignCardPreview() {

        let campaign_card_preview_ok = true;

        await this.useCustomIframe(By.css('#leyka-preview-frame iframe'));

        /** @todo Check the campaign card thumbnail, title & preview */

        await this.useMainIframe();

        return campaign_card_preview_ok;

    }

}

module.exports = InitWizardPage;