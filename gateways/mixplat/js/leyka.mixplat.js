jQuery(document).ready(function($){

    /** @var leyka object Localization strings */
    $(document).on('validate-custom-form-fields.leyka', 'form.leyka-pm-form', function(e, $form){

        var $phone_field = $form.find(':input.phone-num.mixplat-phone'),
            $error = $form.find('.'+$phone_field.attr('name')+'-error'),
            phone_value = $phone_field.val();

        if(phone_value.length) {

            phone_value = phone_value.replace(/[+. -]/, '');
            phone_value = phone_value.replace(/\s/, '');
            phone_value = phone_value.replace(/^7/, '');

            if( !phone_value.match(/^\d{10}$/) ) {

                $error.html(leyka.phone_invalid).show();
                $phone_field.focus();
                $form.data('custom-fields-are-invalid', 1);

            } else {
                $error.hide();
            }
        }

        // All standard fields validation is already passed in the main script (public.js)...
    });
});