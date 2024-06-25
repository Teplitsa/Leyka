// MIXPLAT Wizard
if(document.querySelector('#leyka-settings-form-rd-sms'))
document.querySelector('#leyka-settings-form-rd-sms').classList.add("active")

jQuery("#schack").prop("checked", true)

jQuery("#leyka_mixplat_prod_mode-field").prop("checked", true)

jQuery("#schack").change(function() {
if(this.checked) {
    document.querySelector('#leyka-settings-form-rd-sms').classList.add("active")
}else{
    document.querySelector('#leyka-settings-form-rd-sms').classList.remove("active")
}
});

if(document.querySelector('#leyka-settings-form-rd-testpay')) {

jQuery("#leyka_mixplat_prod_mode-field").change(function() {
if(!this.checked) {
    jQuery("#leyka_mixplat_test_mode-field").prop("checked", true)
    document.querySelector('#more-testinfo').classList.add("active")
}else{
    jQuery("#leyka_mixplat_test_mode-field").prop("checked", false)
    document.querySelector('#more-testinfo').classList.remove("active")
}
});

jQuery("#leyka_mixplat_test_mode-field").change(function() {
if(this.checked) {
    jQuery("#leyka_mixplat_prod_mode-field").prop("checked", false)
    document.querySelector('#more-testinfo').classList.add("active")
}else{
    jQuery("#leyka_mixplat_prod_mode-field").prop("checked", true)
    document.querySelector('#more-testinfo').classList.remove("active")
}
});
}
// MIXPLAT Wizard - END
