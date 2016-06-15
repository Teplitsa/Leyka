jQuery(document).ready(function($){

    window.paypalCheckoutReady = function(){
        paypal.checkout.setup(leyka.paypal_business_id, { /** @todo pp */
            environment: 'sandbox',
            locale: 'ru_RU', /** @todo pp */
            click: function(e){

                e.preventDefault();

                var form = $(e.target).closest('form')[0]; /** @todo pp */

                paypal.checkout.initXO();

                $.support.cors = true;
                $.ajax({
                    url: "http://166.78.8.98/cgi-bin/aries.cgi?sandbox=1&direct=1&returnurl=http://166.78.8.98/cgi-bin/return.htm&cancelurl=http://166.78.8.98/cgi-bin/cancel.htm",
                    type: "GET",
                    data: '&ajax=1&onlytoken=1',
                    async: true,
                    crossDomain: true,

                    //Load the minibrowser with the redirection url in the success handler
                    success: function(token){

                        var url = paypal.checkout.urlPrefix +token;
                        paypal.checkout.startFlow(url); // Loading Mini browser with redirect url, true for async AJAX calls
                    },
                    error: function(responseData, textStatus, errorThrown){

                        alert("Error in ajax post "+responseData.statusText);
                        paypal.checkout.closeFlow(); // Gracefully Close the minibrowser in case of AJAX errors
                    }
                });
            },
            condition: function(){
                return !!data.leyka_payment_method.indexOf('paypal') < 0;
            },
            buttons: [{container: 'input[type="leyka_donation_submit"]'} /*, { container: 't2' }*/] /** @todo pp */
        });
    };

    // $(document).on('click.leyka', 'input[name="leyka_donation_submit"]', function(e){

        // var $form = $(this).parents('form.leyka-pm-form:first');
        //
        // // Exclude the repeated submits:
        // if($form.data('submit-in-process')) {
        //     return false;
        // } else {
        //     $form.data('submit-in-process', 1);
        // }
        //
        // // Donation form validation is already passed in the main script (public.js)
        //
        // var data_array = $form.serializeArray(),
        //     data = {action: 'leyka_ajax_donation_submit'};
        //
        // for(var i=0; i<data_array.length; i++) {
        //     data[data_array[i].name] = data_array[i].value;
        // }

        // if(data.leyka_payment_method.indexOf('paypal') < 0) { // Selected PM don't belong to the CP gateway
        //     return;
        // }

        // e.preventDefault();


        // ------
        // $.ajax({
        //     type: 'post',
        //     url: leyka.ajaxurl,
        //     data: data,
        //     beforeSend: function(xhr){
        //         /** @todo Show some loader */
        //     }
        // }).done(function(response){
        //
        //     $form.data('submit-in-process', 0);
        //
        //     response = $.parseJSON(response);
        //     if( !response || !response.status ) {
        //
        //         /** @todo Show some error message on the form */
        //         return false;
        //
        //     } else if(response.status == 0 && response.message) {
        //
        //         /** @todo Show response.message on the form */
        //         return false;
        //
        //     } else if( !response.public_id ) {
        //
        //         /** @todo Show response.message on the form */
        //         return false;
        //     }
        //
        //     var widget = new cp.CloudPayments(),
        //         $errors = $('#leyka-submit-errors'),
        //         data = {};
        //
        //     if(is_recurrent) {
        //         data.cloudPayments = {recurrent: {interval: 'Month', period: 1}};
        //     }
        //
        //     widget.charge({
        //         publicId: response.public_id,
        //         description: response.payment_title,
        //         amount: parseFloat(response.amount),
        //         currency: response.currency,
        //         invoiceId: parseInt(response.donation_id),
        //         accountId: response.donor_email,
        //         data: data
        //     }, function(options){ // success callback
        //
        //         window.location.href = response.success_page;
        //         $errors.html('').hide();
        //
        //     }, function(reason, options){ // fail callback
        //
        //         $errors.html(reason).show();
        //         $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
        //             scrollTop: $errors.offset().top - 35
        //         }, 250);
        //     });
        // });
    // });
});