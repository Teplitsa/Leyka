/** Donor's account frontend */

var leyka; // L10n lines

jQuery(document).ready(function($){

    // Account activation & password setup:
    function validate_activation_form($form) {

        let field_name = 'leyka_donor_pass',
            $field = $form.find(':input[name="'+field_name+'"]'),
            form_is_valid = true;

        if( !$field.length ) {

            $form.find('.'+field_name+'-error').text('Enter your password'); /** @todo Localize the line */
            form_is_valid = false;

        }

        field_name = 'leyka_donor_pass2';
        $field = $form.find(':input[name="'+field_name+'"]');

        if( !$field.length ) {

            /** @todo Localize the line */
            $form.find('.'+field_name+'-error').text('The password should be like the one in the first field');
            form_is_valid = false;

        }

        return form_is_valid;

    }

    let $form = $('.account-activation');

    $form.on('submit.leyka', function(e){

        e.preventDefault();

        if(validate_activation_form($form)) {

            let params = $form.serializeArray();
            params.push({name: 'action', value: 'leyka_setup_donor_password'});

            $.post(leyka_get_ajax_url(), params).done(function(response){
                // console.log(response)
            });

        }

    });

});