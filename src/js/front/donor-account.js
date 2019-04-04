/** Donor's account frontend */

var leyka; // L10n lines

jQuery(document).ready(function($){

    // Account activation & password setup:
    function leyka_validate_activation_form($form) {

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

    $('.leyka-account-pass-setup').on('submit.leyka', function(e){

        e.preventDefault();

        let $form = $(this);

        if(leyka_validate_activation_form($form)) {

            let params = $form.serializeArray(),
                $message = $form.find('.form-message'),
                $extra_links = $form.find('.leyka-extra-links'),
                $ajax_indicator = $form.find('.form-ajax-indicator'),
                $submit = $form.find('.activation-submit');

            params.push({name: 'action', value: 'leyka_setup_donor_password'});
            if($form.data('account-activation')) {
                params.push({name: 'auto-login', value: true});
            }

            $ajax_indicator.show();
            $message.hide();
            $submit.hide();

            $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

                $ajax_indicator.hide();
                if(response.status === 'ok') {

                    $message.removeClass('error-message').addClass('success-message');
                    $extra_links.hide();

                    if($form.data('account-activation')) {
                        setTimeout(function () {
                            window.location.href = leyka.homeurl + '/donor-account/';
                        }, 3000);
                    }

                } else if(response.message) {

                    $message.removeClass('success-message').addClass('error-message');
                    $submit.show();

                }

                $message.html(response.message).show();

            }).error(function(){

                $ajax_indicator.hide();
                $message
                    .removeClass('success-message').addClass('error-message')
                    .html('Error while setting up a password :( Please contact the website tech. support about that') /** @todo Localize the line */ // leyka.reset_donor_pass_ajax_error_msg
                    .show();

                $submit.show();

            });

        }

    });

    // Account login:
    function leyka_validate_login_form($form) {

        let $field1 = $form.find(':input[name="leyka_donor_email"]'),
            $field2 = $form.find(':input[name="leyka_donor_pass"]'),
            form_is_valid = true;

        if( !$field1.val().length ) {

            $form.find('.leyka_donor_email-error').text('Enter your email').show(); /** @todo Localize the line */
            form_is_valid = false;

        } else {
            $form.find('.leyka_donor_email-error').text('').hide();
        }

        if( !$field2.val().length ) {

            $form.find('.leyka_donor_pass-error').text('Enter your password').show(); /** @todo Localize the line */
            form_is_valid = false;

        } else {
            $form.find('.leyka_donor_pass-error').text('').hide();
        }

        return form_is_valid;

    }

    $('.leyka-account-login').on('submit.leyka', function(e){

        e.preventDefault();

        let $form = $(this);

        if(leyka_validate_login_form($form)) {

            let params = $form.serializeArray(),
                $message = $form.find('.form-message'),
                $extra_links = $form.find('.leyka-extra-links'),
                $ajax_indicator = $form.find('.form-ajax-indicator'),
                $submit = $form.find('.login-submit');

            params.push({name: 'action', value: 'leyka_donor_login'});

            $ajax_indicator.show();
            $message.hide();
            $submit.hide();

            $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

                $ajax_indicator.hide();
                response.message = response.message.length ? response.message : ''; // leyka.default_login_error_msg
                if(response.status === 'ok') {

                    $message.removeClass('error-message').addClass('success-message');
                    $extra_links.hide();

                    setTimeout(function(){
                        window.location.href = leyka.homeurl + '/donor-account/';
                    }, 3000);

                } else if(response.message) {

                    $message.removeClass('success-message').addClass('error-message');
                    $submit.show();

                }

                $message.html(response.message).show();

            }).error(function(){

                $ajax_indicator.hide();
                $message
                    .removeClass('success-message').addClass('error-message')
                    .html('Error while logging you in :( Please contact the website tech. support about that') /** @todo Localize the line */ // leyka.donor_login_ajax_error_msg
                    .show();

                $submit.show();

            });

        }

    });

    // Account password reset:
    function leyka_validate_password_reset_form($form) {

        let $field1 = $form.find(':input[name="leyka_donor_email"]'),
            form_is_valid = true;

        if( !$field1.val().length ) {

            $form.find('.leyka_donor_email-error').text('Enter your email').show(); /** @todo Localize the line */
            form_is_valid = false;

        } else if( !is_email($field1.val()) ) {

            $form.find('.leyka_donor_email-error').text('The email is incorrect').show(); /** @todo Localize the line */
            form_is_valid = false;

        } else {
            $form.find('.leyka_donor_email-error').text('').hide();
        }

        return form_is_valid;

    }

    $('.leyka-reset-password').on('submit.leyka', function(e){

        e.preventDefault();

        let $form = $(this);

        if(leyka_validate_password_reset_form($form)) {

            let params = $form.serializeArray(),
                $message = $form.find('.form-message'),
                $extra_links = $form.find('.leyka-extra-links'),
                $ajax_indicator = $form.find('.form-ajax-indicator'),
                $submit = $form.find('.password-reset-submit');

            params.push({name: 'action', value: 'leyka_donor_password_reset_request'});

            $ajax_indicator.show();
            $message.hide();
            $submit.hide();

            $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

                $ajax_indicator.hide();
                response.message = response.message.length ? response.message : ''; // leyka.default_login_error_msg
                if(response.status === 'ok') {

                    $message.removeClass('error-message').addClass('success-message');
                    $extra_links.hide();

                } else if(response.message) {

                    $message.removeClass('success-message').addClass('error-message');
                    $submit.show();

                }

                $message.html(response.message).show();

            }).error(function(){

                $ajax_indicator.hide();
                $message
                    .removeClass('success-message').addClass('error-message')
                    .html('Error while resetting your password :( Please contact the website tech. support about that') /** @todo Localize the line */ // leyka.donor_login_ajax_error_msg
                    .show();

                $submit.show();

            });

        }

    });

    // Donations history:
    $('.donations-history-more').click(function(e){

        e.preventDefault();

        let $load_more = $(this),
            $ajax_indicator = $load_more.siblings('.form-ajax-indicator'),
            $donations_list = $('.donations-history.items'),
            current_page = $donations_list.data('donations-current-page'),
            total_pages = $donations_list.data('donations-total-pages'),
            params = {
                page: current_page + 1,
                nonce: $load_more.siblings(':input[name="nonce"]'),
                action: 'leyka_get_donations_history_page',
            };

        $ajax_indicator.show();
        $load_more.hide();

        $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

            $ajax_indicator.hide();

            console.log(response);
            if(response.status === 'ok') {

                $.each(response.items, function(index, value){

                });

                if(current_page < total_pages) {

                    $donations_list.data('donations-current-page', current_page + 1);
                    $load_more.show();

                }

            } else { // Show some error message
                // $message.show();
            }

        }).error(function(){

            $ajax_indicator.hide();
            $load_more.show();

        });

    });
    
});

// unsubscribe campaign
jQuery(function($){
	
    function leyka_validate_unsubscribe_form($form) {

        var form_is_valid = true;
        
        $form.find('.donor__textfield-error').hide();
        
        if( !$form.find('input[name="leyka_cancel_subscription_reason"]:checked').length ) {

            $form.find('.leyka-star-field-error-frame .choose-reason').show();
            form_is_valid = false;

        } else if( $form.find('input[name="leyka_cancel_subscription_reason"]:checked').val() == 'other' && !$.trim($form.find('textarea[name="leyka_donor_comment"]').val()) ) {

            $form.find('.leyka-star-field-error-frame .give-details').show();
            form_is_valid = false;

        }
        
        console.log(form_is_valid);
        
        if(!form_is_valid) {
        	$form.find('.leyka-star-field-error-frame').show();
        } else {
        	$form.find('.leyka-star-field-error-frame').hide();
        }

        return form_is_valid;

    }
	
    var $forms = $('.leyka-unsubscribe-campains-forms').first();
    
	$forms.find('.action-disconnect').on('click.leyka', function(e){
		e.preventDefault();
    	$forms.find('form.leyka-screen-form').css('display', 'none');
    	$forms.find('form.leyka-cancel-subscription-form').css('display', 'flex');
    	$forms.find('form.leyka-cancel-subscription-form input[name=leyka_campaign_id]').val($(this).data('campaign_id'));
	});
	
    $forms.find('.leyka-do-not-unsubscribe').on('click.leyka', function(e){
    	e.preventDefault();
    	$forms.find('form.leyka-screen-form').css('display', 'none');
    	$forms.find('form.leyka-unsubscribe-campains-form').css('display', 'block');
    });

    $forms.find('form.leyka-cancel-subscription-form').on('submit.leyka', function(e){
		e.preventDefault();
		
        var $form = $(this);
		
    	$forms.find('form.leyka-screen-form').css('display', 'none');
    	$forms.find('form.leyka-confirm-unsubscribe-request-form').css('display', 'block');
	});

    $forms.find('form.leyka-confirm-unsubscribe-request-form').on('submit.leyka', function(e){
        e.preventDefault();

        var $form = $(this),
        	$valueForm = $form.siblings('form.leyka-cancel-subscription-form');
		
        if(leyka_validate_unsubscribe_form($valueForm)) {

            let params = $form.serializeArray(),
                $message = $form.find('.form-message'),
                $ajax_indicator = $form.find('.leyka-form-spinner'),
                $submit = $form.find('.confirm-unsubscribe-submit');

            params.push({name: 'action', value: 'leyka_unsubscribe_persistent_campaign'});

            $ajax_indicator.show();
            $message.hide();
            $submit.hide();

            $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

                $ajax_indicator.hide();
                response.message = response.message.length ? response.message : leyka.default_error_msg;
                
                if(response.status === 'ok') {

                	$forms.find('form.leyka-screen-form').css('display', 'none');
                	$forms.find('form.leyka-unsubscribe-request-accepted-form').css('display', 'block');
                    $message.removeClass('error-message');

                } else if(response.message) {

                    $message.removeClass('success-message').addClass('error-message');
                    $submit.show();

                }

                $message.html(response.message).show();

            }).error(function(){

                $ajax_indicator.hide();
                $message
                    .removeClass('success-message').addClass('error-message')
                    .html(leyka.error_while_unsibscribe)
                    .show();

                $submit.show();

            });

        }

    });    
	
});