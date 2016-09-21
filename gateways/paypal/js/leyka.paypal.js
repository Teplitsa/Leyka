jQuery(document).ready(function($){

    var $form = $('#leyka-auto-submit'),
        $errors = $('#leyka-submit-errors');

    // Send SetEC PayPal request:
    $.ajax({
        type: 'get',
        url: $form.attr('action'),
        data: $form.serialize(),
        beforeSend: function(xhr){
            /** @todo Show some loader pic */
        }
    }).done(function(response){

        response = $.parseJSON(response);
        if( !response || typeof response.status == 'undefined' ) { // Wrong answer from ajax handler

            $errors.html(leyka.paypal_setec_requeest_error).show();

            return false;

        }

        console.log('SetEC succeded:', response);

    });
});