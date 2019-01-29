require('chromedriver');
// require('geckodriver');

const {Browser, By, Key, until, Condition} = require('selenium-webdriver');
const {suite} = require('selenium-webdriver/testing');
const assert = require('assert');

const InitWizardPage = require('./pages/init-wizard.js');

suite(function(env){

    describe('[Leyka] Fresh - Init Wizard', async function(){

        let driver, page;

        this.timeout(10000);

        before(async function(){

            driver = await env.builder().build();
            page = new InitWizardPage(driver);
            await page.open();

        });

        it('Admin logged in', async function(){

            await page.loginIntoWizardPage();

            let wizard_page = await driver.findElements(By.css('.wizard-init'));
            assert(wizard_page.length > 0);

        });

        it('Country selection step', async function(){

            await testNavigationAreaState('Ваши данные', false);

            await page.selectReceiverCountry('ru');
            await page.submitStep();

            let receiver_type_step_title = await driver.findElements(By.id('step-title-rd-receiver_type'));
            assert(receiver_type_step_title.length > 0);

        });

        it('Receiver type step - choosing "legal"', async function(){

            await testNavigationAreaState('Ваши данные', 'Получатель пожертвований');

            await page.selectReceiverType('legal');
            await page.submitStep();

        });

        it('Legal receiver data step - required fields validation testing', async function(){

            await testNavigationAreaState('Ваши данные', 'Ваши данные');

            let required_fields_to_check = ['org_full_name', 'org_face_fio_ip', 'org_state_reg_number'];

            await page.unsetRequiredFields(required_fields_to_check);
            await page.submitStep();

            let error_messages_shown = await page.requiredFieldsErrorsShown(required_fields_to_check);
            assert(error_messages_shown);

        });

        /** @todo Add this test after adding email format validation to the Wizards fields */
        // it('Legal receiver data step - email field validation testing', async function(){
        //
        //     let email_field_name = 'tech_support_email';
        //
        //     await page.setTextFieldValue(email_field_name, 'not#an-email');
        //     await page.submitStep();
        //
        //     let error_messages_shown = await page.emailFieldErrorShown(email_field_name);
        //     assert(error_messages_shown);
        //
        // });

        it('Legal receiver data step - OGRN masked field validation testing', async function(){

            let field_mask_correct = await page.checkFieldMask('org_state_reg_number', 'not#an-ogrn', '1234567890123');
            assert(field_mask_correct);

            await page.setTextFields({
                'org_full_name': 'Фонд помощи бездомным животным "Ак-Барсик"',
                'org_short_name': 'Фонд "Ак-Барсик"',
                'org_face_position': 'Директор',
                'org_face_fio_ip': 'Котов-Пёсов Аристарх Евграфович',
                'org_address': 'Москва, ул. Добра и Правды, д. 666, офис 13',
                'org_state_reg_number': '1023400056789',
                'org_kpp': '780302015',
                'org_inn': '4283256127',
                'org_contact_person_name': 'Иван Петрович Сидоров',
                'tech_support_email': 'support@ak.barsik'
            });

            await page.submitStep();

        });

        it('Legal receiver bank essentials step - required fields validation testing', async function(){

            await testNavigationAreaState('Ваши данные', 'Банковские реквизиты');

            let required_fields_to_check = ['org_bank_name', 'org_bank_account', 'org_bank_corr_account', 'org_bank_bic'];

            await page.unsetRequiredFields(required_fields_to_check);
            await page.submitStep();

            let error_messages_shown = await page.requiredFieldsErrorsShown(required_fields_to_check);
            assert(error_messages_shown);

        });

        it('Legal receiver bank essentials step - bank account number masked field validation testing', async function(){

            let field_mask_correct = await page.checkFieldMask('org_bank_account', 'not#an-account-number', '40123840529627089012');
            assert(field_mask_correct);

            await page.setTextFields({
                'org_bank_name': 'Первый кредитный банк',
                'org_bank_account': '40123840529627089012',
                'org_bank_corr_account': '30101810270902010595',
                'org_bank_bic': '044180293'
            });

            await page.submitStep();

        });

        it('Oferta step - field testing', async function(){

            await testNavigationAreaState('Ваши данные', 'Оферта');

            let terms_option_id = 'terms_of_service_text';

            // Initial field value test:
            let terms_text_set = await page.isTermsTextSet(terms_option_id);
            assert(terms_text_set);

            let terms_text_includes_placeholders = await page.isTermsTextWithPlaceholders(terms_option_id);
            assert( !terms_text_includes_placeholders );

            await page.unsetTermsText(terms_option_id);

            await page.submitStep();

            let error_messages_shown = await page.requiredFieldsErrorsShown([terms_option_id]);
            assert(error_messages_shown);
            // Initial test finished

            // Re-test field value (after the incorrect submit):
            terms_text_set = await page.isTermsTextSet(terms_option_id);
            assert(terms_text_set);

            terms_text_includes_placeholders = await page.isTermsTextWithPlaceholders(terms_option_id);
            assert( !terms_text_includes_placeholders );
            // Re-test finished

            await page.submitStep();

        });

        it('Personal data step - terms field testing', async function(){

            await testNavigationAreaState('Ваши данные', 'Персональные данные');

            let terms_option_id = 'pd_terms_text';

            // Initial field value test:
            let terms_text_set = await page.isTermsTextSet(terms_option_id);
            assert(terms_text_set);

            let terms_text_includes_placeholders = await page.isTermsTextWithPlaceholders(terms_option_id);
            assert( !terms_text_includes_placeholders );

            await page.unsetTermsText(terms_option_id);

            await page.submitStep();

            let error_messages_shown = await page.requiredFieldsErrorsShown([terms_option_id]);
            assert(error_messages_shown);
            // Initial test finished

            // Re-test field value (after the incorrect submit):
            terms_text_set = await page.isTermsTextSet(terms_option_id);
            assert(terms_text_set);

            terms_text_includes_placeholders = await page.isTermsTextWithPlaceholders(terms_option_id);
            assert( !terms_text_includes_placeholders );
            // Re-test finished

            await page.submitStep();

        });

        it('"Your data" section - finishing step', async function(){

            await testNavigationAreaState('Ваши данные', true);
            await page.submitStep();

        });

        it('Diagnostic data agreement step - choosing "agree"', async function(){

            await testNavigationAreaState('Диагностические данные', false);

            let send_stats_is_agreed = page.statsFieldAgreed();
            assert(send_stats_is_agreed);

            await page.selectStatsAgreement('y');
            await page.submitStep();

        });

        it('Diagnostic data agreement step - "agree" chose', async function(){

            await testNavigationAreaState('Диагностические данные', true);

            let current_step_title = await page.getCurrentStepTitle();
            assert(current_step_title.includes('Спасибо'));

            await page.submitStep();

        });

        it('Main campaign settings step - required fields validation testing', async function(){

            await testNavigationAreaState('Настройка кампании', 'Основные сведения');

            let required_fields_to_check = ['campaign_title'];

            await page.unsetRequiredFields(required_fields_to_check);
            await page.submitStep();

            let error_messages_shown = await page.requiredFieldsErrorsShown(required_fields_to_check);
            assert(error_messages_shown);

            await page.setTextFields({
                'campaign_title': 'На уставную деятельность',
                'campaign_short_description': 'Краткое описание того, почему жертвовать нам важно и нужно, и на что мы потратим пожертвования. Можно одно-два предложения.',
                'campaign_target': '50000'
            });

            await page.submitStep();

        });

        it('Campaign decoration step - thumbnail uploading field testing', async function(){

            await testNavigationAreaState('Настройка кампании', 'Оформление кампании');

            await page.setFileUploadField('campaign_photo', 'D:\\downloads\\browsers\\leyka-campaign-thumb-example.jpg');

            let campaign_preview_correct = page.checkCampaignCardPreview();
            assert(campaign_preview_correct);

            await page.submitStep();

        });

        // it('', async function(){
        //
        // });

        after(async function(){
            // await driver.quit();
        });

        async function testNavigationAreaState(section_name, step_name) {

            let is_navigation_area_correct = await page.isNavigationAreaInState(section_name, step_name);
            assert(is_navigation_area_correct);

        }

    });
});