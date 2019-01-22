const {Browser, By, Key, until} = require("selenium-webdriver");

class InitWizardPage {

    constructor(driver) {

        this.driver = driver;
        this.url = 'http://leyka-test.kandinsky.tmweb.ru/wp-login.php?redirect_to=http%3A%2F%2Fleyka-test.kandinsky.tmweb.ru%2Fwp-admin%2Fadmin.php%3Fpage%3Dleyka_settings_new%26screen%3Dwizard-init%26reset%3D1&reauth=1';
        this.locators = {
            receiverCountryField: By.id('leyka_receiver_country-field'),
            wizardStepSubmit: By.css('.step-submit input[name="leyka_settings_submit_init"]'),
        };

    }

    open() {
        this.driver.get(this.url);
    }

    async selectReceiverCountry(country_value) {

        await this.driver.findElement(this.locators.receiverCountryField)
            .findElement(By.css('option[value="' + country_value + '"]'))
            .click();

    }

    async submit() {
        await this.driver.findElement(this.locators.wizardStepSubmit).click();
    }

}

module.exports = InitWizardPage;