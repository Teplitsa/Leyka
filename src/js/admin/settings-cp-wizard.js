// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $step_submit = $('.step-submit input.step-next');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    // $step_submit
    //     .removeClass('button-primary').addClass('button-secondary')
    //     .data('original-submit-text', $step_submit.val())
    //     .val('Начать тестовое пожертвование');

});