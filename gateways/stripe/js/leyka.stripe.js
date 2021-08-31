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

        var $pm_field = $form.find('input[name="leyka_payment_method"][value*="stripe-"]'),
            gateway_is_chosen = $pm_field.prop('type') === 'hidden' ?
                $pm_field.val().indexOf('cp') >= 0 : !!$pm_field.prop('checked');

        if($pm_field.length <= 0 || !gateway_is_chosen) {
            return;
        }

        if($form.data('submit-in-process')) {
            return;
        } else {
            $form.data('submit-in-process', 1);
        }

        var data_array = $form.serializeArray(),
            data = {action: 'leyka_ajax_get_gateway_redirect_data'};

        for(var i = 0; i < data_array.length; i++) {
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

            $errors.html('').hide();

            window.location.href = response.payment_url;

        });

    });

});