// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $step_submit = $('.step-submit input.step-next'),
        $cp_error_message = $cp_payment_tryout_field.find('.error-message');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

        if($payment_tryout_button.data('submit-in-process')) {
            return;
        } else {
            $payment_tryout_button.data('submit-in-process', 1);
        }

        // Do a test donation:
        $.ajax({
            type: 'post',
            url: leyka.ajaxurl,
            data: {
                // Test donation data here...
            }
        }).done(function(response){

            $payment_tryout_button.data('submit-in-process', 0);

            response = $.parseJSON(response);
            if( !response || typeof response.status === 'undefined' ) {

                $cp_error_message.html(leyka.ajax_wrong_server_response).show();
                return false;

            } else if(response.status !== 0 && typeof response.message !== 'undefined') {

                $cp_error_message.html(response.message).show();
                return false;

            } else if( !response.public_id ) {
                /** @todo Remove this check when more common gateways settings check will be added in leyka-ajax.php:leyka_submit_donation(). */

                $cp_error_message.html(leyka.cp_not_set_up).show();
                return false;

            }

            var widget = new cp.CloudPayments(),
                data = {};

            widget.charge({
                language: 'ru-RU',
                publicId: response.public_id,
                description: leyka_decode_htmlentities(response.payment_title),
                amount: parseFloat(response.amount),
                currency: response.currency,
                invoiceId: parseInt(response.donation_id),
                accountId: response.donor_email,
                data: data
            }, function(options){ // success callback
                $cp_error_message.html('').hide();
            }, function(reason, options){ // fail callback
                $cp_error_message.html(leyka.cp_donation_failure_reasons[reason] || reason).show();
            });

            // $payment_tryout_button.closest('.leyka-pf').leykaForm('close');

        });

    });

    // $step_submit
    //     .removeClass('button-primary').addClass('button-secondary')
    //     .data('original-submit-text', $step_submit.val())
    //     .val('Начать тестовое пожертвование');

});