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

        // Selected PM don't belong to the RBK gateway:

        var $pm_field = $form.find('input[name="leyka_payment_method"][value*="rbk-"]'),
            gateway_is_chosen = $pm_field.prop('type') === 'hidden' ?
                $pm_field.val().indexOf('rbk') >= 0 : !!$pm_field.prop('checked');

        if($pm_field.length <= 0 || !gateway_is_chosen) {
            return; /** @todo Add some error to the form! Or at least do some console.logging */
        }

        var $revo_redirect_step = $form.closest('.leyka-pf').find('.leyka-pf__redirect');
        if($revo_redirect_step.length) {
            $revo_redirect_step.addClass('leyka-pf__redirect--open');
        }

        if($form.data('submit-in-process')) {
            return;
        } else {
            $form.data('submit-in-process', 1);
        }

        // Donation form validation already passed in the main script (public.js)

        var /*is_recurring = $form.find('.leyka-recurring').prop('checked') ||
                $form.find('.is-recurring-chosen').val() > 0,*/ // For Revo template
            data_array = $form.serializeArray(),
            data = {action: 'leyka_ajax_get_gateway_redirect_data'};

        for(var i=0; i<data_array.length; i++) {
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

            }

            if(leyka.gtm_ga_eec_available) {

                window.dataLayer = window.dataLayer || [];

                dataLayer.push({
                    'event': 'eec.add',
                    'ecommerce': {
                        // 'currencyCode': response.currency, // For some reason it doesn't work
                        'add': {
                            'products': [{
                                'name': response.description,
                                'id': response.donation_id,
                                'price': response.amount,
                                'quantity': 1
                            }]
                        }
                    }
                });

            }

            var checkout = RbkmoneyCheckout.configure({
                invoiceID: response.invoice_id,
                invoiceAccessToken: response.invoice_access_token,
                name: response.name,
                description: response.description,
                email: response.donor_email,
                initialPaymentMethod: 'bankCard',
                paymentFlowHold: true,
                holdExpiration: 'capture',
                opened: function(){},
                closed: function(){
                    return window.history.back();
                },
                finished: function(){
                    return window.location.href = response.success_page;
                }
            });

            checkout.open();

            window.addEventListener('popstate', function() {
                checkout.close();
            });

        });

    });

});