jQuery(document).ready(function($){

    /** @var leyka object Localization strings */
    /** @todo Only for old templates - Revo & earlier. Remove this code when their support is finished. */
    $(document).on('validate-custom-form-fields.leyka', 'form.leyka-pm-form', function(e, $form){

        // Selected PM don't belong to the MIXPLAT gateway:
        if(leyka_get_pm_full_id($form).indexOf('mixplat') < 0) {
            return;
        }

        var $phone_field = $form.find(':input.phone-num.mixplat-phone'),
            $error = $form.find('.'+$phone_field.attr('name')+'-error'),
            phone_value = $phone_field.val();

        if($phone_field.length && phone_value.length) {

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

        // All standard fields validation is already passed in the main script.
    });
});