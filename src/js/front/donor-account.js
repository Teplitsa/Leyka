/** Donor's account frontend */

var leyka; // L10n lines

jQuery(document).ready(function($){

    // Account activation & password setup:
    function validate_activation_form($form) {

        let $field1 = $form.find(':input[name="leyka_donor_pass"]'),
            $field2 = $form.find(':input[name="leyka_donor_pass2"]'),
            form_is_valid = true;

        if( !$field1.val().length ) {

            $form.find('.leyka_donor_pass-error').text('Enter your password').show(); /** @todo Localize the line */
            form_is_valid = false;

        } else {
            $form.find('.leyka_donor_pass-error').text('').hide();
        }

        if($field1.val().length && $field1.val() !== $field2.val()) {

            /** @todo Localize the line */
            $form.find('.leyka_donor_pass2-error').text('The password should be like the one in the first field').show();
            form_is_valid = false;

        } else {
            $form.find('.leyka_donor_pass2-error').text('').hide();
        }

        return form_is_valid;

    }

    let $form = $('.account-activation');

    $form.on('submit.leyka', function(e){

        e.preventDefault();

        if(validate_activation_form($form)) {

            let params = $form.serializeArray(),
                $message = $form.find('.form-message'),
                $ajax_indicator = $form.find('.form-ajax-indicator');

            params.push({name: 'action', value: 'leyka_setup_donor_password'});

            $ajax_indicator.show();
            $message.hide();

            $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

                $ajax_indicator.hide();
                $message.removeClass('error-message').addClass('success-message').text(response.message).show();

                setTimeout(function(){
                    window.location.href = leyka.homeurl + '/donor-account/';
                }, 3000);

            }).error(function(){

                $ajax_indicator.hide();
                $message
                    .removeClass('success-message').addClass('error-message')
                    .text('Error while setting up a password :( Please contact the website tech. support about that') /** @todo Localize the line */ // leyka.reset_donor_pass_ajax_error_msg
                    .show();

            });

        }

    });

});