jQuery(document).ready(function($){

    $(document).on('submit.leyka', 'form.leyka-pm-form,form.leyka-revo-form', function(e){

        function addError($errors_block, error_html) {

            $errors_block.html(error_html).show();
            $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                scrollTop: $errors_block.offset().top - 35
            }, 250);

        }

        /** @var leyka object Localization strings */

        var $form = $(this),
            $errors = $('#leyka-submit-errors');

        var $revo_redirect_step = $form.closest('.leyka-pf').find('.leyka-pf__redirect');
        if($revo_redirect_step.length) {
            $revo_redirect_step.addClass('leyka-pf__redirect--open');
        }

        if($form.data('submit-in-process')) {
            return false;
        } else {
            $form.data('submit-in-process', 1);
        }

        // Donation form validation already passed in the main script (public.js)

        var is_recurrent = $form.find('.leyka_recurring').attr('checked') ||
                           $form.find('.is-recurring-chosen').val() > 0, // For Revo template
            data_array = $form.serializeArray(),
            data = {action: 'leyka_ajax_donation_submit'};

        for(var i=0; i<data_array.length; i++) {
            data[data_array[i].name] = data_array[i].value;
        }

        if(data.leyka_payment_method.indexOf('cp') < 0) { // Selected PM don't belong to the CP gateway
            return;
        }

        e.preventDefault();

        $.ajax({
            type: 'post',
            url: leyka.ajaxurl,
            data: data,
            beforeSend: function(xhr){
                /** @todo Show some loader */
            }
        }).done(function(response){

            $form.data('submit-in-process', 0);

            response = $.parseJSON(response);
            if( !response || typeof response.status == 'undefined' ) { // Wrong answer from ajax handler

                addError($errors, leyka.cp_wrong_server_response);
                return false;

            } else if(response.status != 0 && typeof response.message != 'undefined') {

                addError($errors, response.message);
                return false;

            } else if( !response.public_id ) {

                addError($errors, leyka.cp_not_set_up);
                return false;

            }

            var widget = new cp.CloudPayments(),
                data = {};

            if(is_recurrent) {
                data.cloudPayments = {recurrent: {interval: 'Month', period: 1}};
            }

            if($revo_redirect_step.length) {
                $revo_redirect_step.removeClass('leyka-pf__redirect--open');
            }

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

                window.location.href = response.success_page;
                $errors.html('').hide();

            }, function(reason, options){ // fail callback
                addError($errors, leyka.cp_donation_failure_reasons[reason]);
            });

            if($form.hasClass('leyka-revo-form')) {
                $form.closest('.leyka-pf').leykaForm('close');
            }

        });

    });

});