jQuery(document).ready(function($){
    $(document).on('submit.leyka', 'form.leyka-pm-form,form.leyka-revo-form', function(e){

        function addError($errors_block, error_html) {

            $errors_block.html(error_html).show();
            $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                scrollTop: $errors_block.offset().top - 35
            }, 250);

        }

        /** @var leyka object Localization strings */

        let $form = $(this),
            $errors = $('#leyka-submit-errors');

        // Selected PM don't belong to the CP gateway:

        let $pm_field = $form.find('input[name="leyka_payment_method"][value*="cp-"]'),
            gateway_is_chosen = $pm_field.prop('type') === 'hidden' ?
                $pm_field.val().indexOf('cp') >= 0 : !!$pm_field.prop('checked');

        if($pm_field.length <= 0 || !gateway_is_chosen) {
            return; /** @todo Add some error to the form! Or at least do some console.logging */
        }

        let $revo_redirect_step = $form.closest('.leyka-pf').find('.leyka-pf__redirect');
        if($revo_redirect_step.length) {
            $revo_redirect_step.addClass('leyka-pf__redirect--open');
        }

        if($form.data('submit-in-process')) {
            return;
        } else {
            $form.data('submit-in-process', 1);
        }

        // Donation form validation already passed in the main script (public.js)

        let is_recurring = $form.find('.leyka-recurring').prop('checked') ||
                           $form.find('.is-recurring-chosen').val() > 0; // For Revo template;

        const $_form = $form.clone(),
            currency = $('.section__fields.currencies a.active').data('currency');

        $_form.find(`.currency-tab:not(.currency-${currency})`).remove();

        let data_array = $_form.serializeArray(),
            data = {action: 'leyka_ajax_get_gateway_redirect_data'};

        for(let i = 0; i < data_array.length; i++) {
            data[data_array[i].name] = data_array[i].value;
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
            if( !response || typeof response.status === 'undefined' ) {

                addError($errors, leyka.ajax_wrong_server_response);
                return false;

            } else if(response.status !== 0 && typeof response.message !== 'undefined') {

                addError($errors, response.message);
                return false;

            } else if( !response.public_id ) {
                /** @todo Remove this check when more common gateways settings check will be added in leyka-ajax.php:leyka_submit_donation(). */

                addError($errors, leyka.cp_not_set_up);
                return false;

            }

            if(leyka.gtm_ga_eec_available) {

                window.dataLayer = window.dataLayer || [];

                dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
                dataLayer.push({
                    event: "begin_checkout",
                    ecommerce: {
                    items: [{
                        item_name: response.payment_title, // Name or ID is required.
                        item_id: response.donation_id,
                        price:response.amount,
                        index: 1,
                        quantity: 1
                    }]
                    }
                }); 
                
                console.log("action: begin_checkout")
            }

            let widget = new cp.CloudPayments(),
                data = {name: response.name, donor_name: response.name};

            if(response.additional_fields && !$.isEmptyObject(response.additional_fields)) {
                $.each(response.additional_fields, function(key, value){
                    data[key] = value;
                });
            }

            if(is_recurring) {
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
                // name: response.name,
                email: response.donor_email,
                data: data,
                configuration: {
                    common: {
                        successRedirectUrl: response.success_page,
                        failRedirectUrl: response.failure_page
                    }
                }
            }, function(options){ // success callback

                window.location.href = response.success_page;
                $errors.html('').hide();

            }, function(reason, options){ // fail callback

                if( !reason ) { // In some cases of Donor cancelling payment, reason === null
                    reason = 'User has cancelled';
                }

                addError($errors, leyka.cp_donation_failure_reasons[reason] || reason);

            });

            if($form.hasClass('leyka-revo-form')) {
                $form.closest('.leyka-pf').leykaForm('close');
            }

        });

    });

});