require('chromedriver');
require('geckodriver');

const {Browser, By, Key, until} = require('selenium-webdriver');
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

            let wp_login_form = await driver.findElements(By.id('loginform'));

            if(wp_login_form.length > 0) { // Login needed - checking

                let login_field = await driver.wait(until.elementLocated(By.id('user_login'))),
                    pass_field = await driver.wait(until.elementLocated(By.id('user_pass'))),
                    submit = await driver.wait(until.elementLocated(By.id('wp-submit')));

                await login_field.sendKeys('leyka_tester');
                await pass_field.sendKeys('19851308cdCDyENT,leyka-test');
                await submit.click();

                let wizard_page = await driver.findElements(By.css('.wizard-init')); // await driver.wait(until.elementLocated()
                assert(wizard_page.length > 0);

            }

        });

        it('Country selection step', async function(){

            await page.selectReceiverCountry('ru');
            await page.submit();

            let receiver_type_step_title = await driver.findElements(By.id('step-title-rd-receiver_type'));
            assert(receiver_type_step_title.length > 0);

        });

        after(async function(){
            driver.quit();
        });



    });
});