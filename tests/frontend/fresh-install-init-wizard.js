require('chromedriver');
require('geckodriver');

const {Browser, By, Key, until, Condition} = require('selenium-webdriver');
const {suite} = require('selenium-webdriver/testing');
const assert = require('assert');

const InitWizardPage = require('./pages/init-wizard.js');

suite(function(env) {

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

            let is_navigation_area_correct = await page.isNavigationAreaInState('ВАШИ ДАННЫЕ', false);
            assert(is_navigation_area_correct);

            await page.selectReceiverCountry('ru');
            await page.submitStep();

            let receiver_type_step_title = await driver.findElements(By.id('step-title-rd-receiver_type'));
            assert(receiver_type_step_title.length > 0);

        });

        it('Receiver type step - choosing "legal"', async function(){

            let is_navigation_area_correct = await page.isNavigationAreaInState('ВАШИ ДАННЫЕ', 'Получатель пожертвований');
            assert(is_navigation_area_correct);

            await page.selectReceiverType('legal');
            await page.submitStep();

        });

        // it('', async function(){
        //
        // });
        //
        // it('', async function(){
        //
        // });

        after(async function(){
            driver.quit();
        });



    });
});