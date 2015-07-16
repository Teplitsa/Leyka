jQuery(document).ready(function($){

    $(document).on('submit', 'form.leyka-pm-form', function(e){

        // Donation form validation is already passed in the main script (public.js)

        var $form = $(this),
            is_recurrent = $form.find('#leyka_cp-card_recurring').attr('checked'),
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

            response = $.parseJSON(response);
            if( !response || !response.status ) {

                /** @todo Show some error message on the form */
                return false;

            } else if(response.status == 0 && response.message) {

                /** @todo Show response.message on the form */
                return false;

            } else if( !response.public_id ) {

                /** @todo Show response.message on the form */
                return false;
            }

            var widget = new cp.CloudPayments(),
                $errors = $('#leyka-submit-errors'),
                data = {};

            if(is_recurrent) {
                data.cloudPayments = {recurrent: {interval: 'Month', period: 1}};
            }

            widget.charge({
                publicId: response.public_id,
                description: response.payment_title,
                amount: parseFloat(response.amount),
                currency: response.currency,
                invoiceId: parseInt(response.donation_id),
                accountId: response.donor_email,
                data: data
            }, function(options){ // success callback

                window.location.href = response.success_page;
                $errors.html('').hide();

            }, function(reason, options){ // fail callback

                $errors.html(reason).show();
                $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                    scrollTop: $errors.offset().top - 35
                }, 250);
            });
        });
    });
});