// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $cp_error_message = $cp_payment_tryout_field.find('.field-errors'),
        $call_support_link = $cp_payment_tryout_field.find('.call-support');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $call_support_link.click(function(e){

        e.preventDefault();

        $('#leyka-help-chat-message').val(
            $('.current-wizard-title').val() + '\n'
            + 'Раздел: ' + $('.current-section-title').val() + '\n'
            + 'Шаг: ' + $('.current-step-title').val() + '\n\n'
            + 'Ошибка:\n'
            + $cp_error_message.text()
        );
        $('.help-chat-button').click();

    });

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

        if($payment_tryout_button.data('submit-in-process')) {
            return;
        } else {
            $payment_tryout_button.data('submit-in-process', 1);
        }

        // Do a test donation:
        $payment_tryout_button.data('submit-in-process', 0);

        if( !leyka_wizard_cp.cp_public_id ) {

            $cp_error_message.html(leyka_wizard_cp.cp_not_set_up).show();
            return false;

        }

        var widget = new cp.CloudPayments();
        widget.charge({
            language: 'ru-RU',
            publicId: leyka_wizard_cp.cp_public_id,
            description: 'Leyka - payment testing',
            amount: 1.0,
            currency: leyka_wizard_cp.main_currency,
            accountId: leyka_wizard_cp.test_donor_email,
            invoiceId: 'leyka-test-donation'
        }, function(options){ // success callback

            $cp_error_message.html('').hide();
            $call_support_link.hide();

            $payment_tryout_button
                .removeClass('not-tested').hide()
                .siblings('.result.ok').show();

            if( !$cp_payment_tryout_field.find('.do-payment.not-tested').length ) {
                $cp_payment_tryout_field.find('input[name="payment_tryout_completed"]').val(1);
            }

        }, function(reason, options){ // fail callback

            $call_support_link.show();

            $cp_error_message.html(leyka_wizard_cp.cp_donation_failure_reasons[reason] || reason).show();
            $cp_payment_tryout_field.find('.payment-tryout-comment').hide();

        });

    });

});
// CP payment tryout custom setting - END