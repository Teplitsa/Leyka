jQuery(document).ready(function($){

    $(document).on('submit.leyka', 'form.leyka-pm-form', function(e){

        var $form = $(this);

        // Exclude the repeated submits:
        if($form.data('submit-in-process')) {
            return false;
        } else {
            $form.data('submit-in-process', 1);
        }

        // Donation form validation is already passed in the main script (public.js)

        /** @todo Refactor this! ATM the validation doesn't work at all */
        var $mixplat_phone_validation_error = $("#leyka_mixplat_phone_valid-error");
        var $mixplat_phone_field = $("#leyka_mixplat-sms_phone");

        $mixplat_phone_field.change(function(){

            if( !is_mixplat_phone_valid($(this).val()) ) {
                show_mixplat_phone_error();
            } else {
                hide_mixplat_phone_error();
            }
        });

        $mixplat_phone_field.blur(function(){

            if( !is_mixplat_phone_valid($(this).val()) ) {

                show_mixplat_phone_error();
                return false;

            } else {
                hide_mixplat_phone_error();
            }
        });

        function is_mixplat_phone_valid(val) {

            var is_valid = false;
            if(val) {
                val = val.replace(/[+. -]/, "");
                val = val.replace(/\s/, "");
                val = val.replace(/^7/, "");
                if(val.match(/^\d{10}$/)) {
                    is_valid = true;
                }
            }
            return is_valid;
        }

        function show_mixplat_phone_error() {

            $mixplat_phone_validation_error.show();
            $mixplat_phone_validation_error.css("color", "red");
            $mixplat_phone_field.focus();
        }

        function hide_mixplat_phone_error() {
            $mixplat_phone_validation_error.hide();
        }
        
    });
});