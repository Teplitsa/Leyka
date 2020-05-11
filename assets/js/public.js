/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));

/** Common utilities & tools */
function is_email(value) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(value);
}

function is_phone_number(value) {
    return /^[0-9\+\-\. ]{10,}$/.test(value);
}

function leyka_get_ajax_url() {
    return typeof leyka != 'undefined' ? leyka.ajaxurl : typeof frontend != 'undefined' ? frontend.ajaxurl : '/';
}

//polyfill for unsupported Number.isInteger
//https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger
Number.isInteger = Number.isInteger || function(value) {
    return typeof value === "number" &&
           isFinite(value) &&
           Math.floor(value) === value;
};

/** @var e JS keyup/keydown event */
function leyka_is_digit_key(e, numpad_allowed) {

    if(typeof numpad_allowed == 'undefined') {
        numpad_allowed = true;
    } else {
        numpad_allowed = !!numpad_allowed;
    }

    if( // Allowed special keys
        e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13 || // Backspace, delete, tab, enter
        (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    ) {
        return true;
    }

    if(numpad_allowed) {
        if( !e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57 ) {
            return true;
        } else {
            return e.keyCode >= 96 && e.keyCode <= 105;
        }
    } else {
        return !(e.shiftKey || e.keyCode < 48 || e.keyCode > 57);
    }

}

/** @var e JS keyup/keydown event */
function leyka_is_special_key(e) {

    // Allowed special keys
    return (
        e.keyCode === 9 || // Tab
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    );
}

function leyka_validate_donor_name(name_string) {
    return !name_string.match(/[!@#$%^&*()+=\[\]{};:"\\|,<>\/?]/);
}

function leyka_empty(mixed_var) {

    var undefined,
        empty_values = [undefined, null, false, 0, '', '0'];

    for(var i = 0; i < empty_values.length; i++) {
        if(mixed_var === empty_values[i]) {
            return true;
        }
    }

    if(typeof mixed_var === 'object') {

        for(var key in mixed_var) {
            if(mixed_var.hasOwnProperty(key)) {
                return false;
            }
        }

        return true;

    }

    return false;

}
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
            $donations_list = $('.donations-history'),
            current_page = $donations_list.data('donations-current-page'),
            total_pages = $donations_list.data('donations-total-pages'),
            params = {
                donor_id: $donations_list.data('donor-id'),
                page: current_page + 1,
                nonce: $load_more.siblings(':input[name="nonce"]').val(),
                action: 'leyka_get_donations_history_page',
            };

        $ajax_indicator.show();
        $load_more.hide();

        $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

            $ajax_indicator.hide();

            if(response.status === 'ok') {

                if(response.items_html) {
                    $donations_list.append(response.items_html);
                }

                if(current_page + 1 < total_pages) {

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
        
        if( !$form.find('input[name="leyka_cancel_subscription_reason[]"]:checked').length ) {

            $form.find('.leyka-star-field-error-frame .choose-reason').show();
            form_is_valid = false;

        } else if( $form.find('input[name="leyka_cancel_subscription_reason[]"][value="other"]:checked').length && !$.trim($form.find('textarea[name="leyka_donor_custom_reason"]').val()) ) {

            $form.find('.leyka-star-field-error-frame .give-details').show();
            form_is_valid = false;

        }
        
        return form_is_valid;
        
    }
	
    var $forms = $('.leyka-unsubscribe-campains-forms').first();
    
	$forms.find('.action-disconnect').on('click.leyka', function(e){
		e.preventDefault();
    	$forms.find('form.leyka-screen-form').css('display', 'none');
    	$forms.find('form.leyka-cancel-subscription-form').css('display', 'flex');
        $forms.find('form.leyka-cancel-subscription-form input[name=leyka_campaign_id]').val($(this).data('campaign-id'));
        $forms.find('form.leyka-cancel-subscription-form input[name=leyka_donation_id]').val($(this).data('donation-id'));
    	$forms.find('form.leyka-cancel-subscription-form input[name=leyka_campaign_permalink]').val($(this).attr('href'));
	});
	
	$forms.find('input[name="leyka_cancel_subscription_reason[]"]').on('change.leyka', function(e){
		if($(this).val() == 'other') {
			if($(this).prop('checked')) {
				$forms.find('.unsubscribe-comment').show();
			}
			else {
				$forms.find('.unsubscribe-comment').hide();
			}
		}
	});
	
	if($forms.find('input[name="leyka_cancel_subscription_reason[]"][value="other"]:checked').length) {
		$forms.find('.unsubscribe-comment').show();
	}
	else {
		$forms.find('.unsubscribe-comment textarea').val('');
	}
	
    $forms.find('.leyka-do-not-unsubscribe').on('click.leyka', function(e){
    	e.preventDefault();
    	$forms.find('form.leyka-screen-form').css('display', 'none');
    	$forms.find('form.leyka-unsubscribe-campains-form').css('display', 'block');
    });

    $forms.find('form.leyka-cancel-subscription-form').on('submit.leyka', function(e){
		e.preventDefault();
		
        var $form = $(this);
        
        if(leyka_validate_unsubscribe_form($form)) {
	    	$forms.find('form.leyka-screen-form').css('display', 'none');
	    	if($form.find('input[name="leyka_cancel_subscription_reason[]"][value="uncomfortable_pm"]:checked, input[name="leyka_cancel_subscription_reason[]"][value="too_much"]:checked').length) {
	    		$forms.find('form.leyka-confirm-go-resubscribe-form').css('display', 'block');
	    	}
	    	else {
		    	$forms.find('form.leyka-confirm-unsubscribe-request-form').css('display', 'block');
	    	}
        }
	});

    $forms.find('form.leyka-confirm-unsubscribe-request-form').on('submit.leyka', function(e){
        e.preventDefault();
        leykaCancelSubscription($(this));
    });    

    $forms.find('form.leyka-confirm-go-resubscribe-form').on('submit.leyka', function(e){
        e.preventDefault();
        leykaCancelSubscription($(this));
    });    
    
    function leykaCancelSubscription($form) {

        let $cancelling_form = $form.siblings('form.leyka-cancel-subscription-form'),
	        params = $cancelling_form.serializeArray(),
	        $message = $form.find('.form-message'),
	        $ajax_indicator = $form.find('.form-ajax-indicator'),
	        $submit = $form.find('.confirm-unsubscribe-submit');

	    params.push({name: 'action', value: 'leyka_cancel_recurring'});

	    $ajax_indicator.show();
	    $message.hide();
	    $submit.hide();
	
	    $.post(leyka_get_ajax_url(), params, null, 'json').done(function(response){

	        $ajax_indicator.hide();
	        response.message = response.message.length ? response.message : leyka.default_error_msg;

	        if(response.status === 'ok') {

	        	$(':input', $cancelling_form)
                    .not(':button, :submit, :reset, :hidden')
                    .val('')
                    .removeAttr('checked')
                    .removeAttr('selected');

                $forms.find('form.leyka-screen-form').css('display', 'none');

                let $back_to_account_block = $forms.find('.leyka-back-to-account');

                $back_to_account_block.css('display', 'block');

                $message = $back_to_account_block.find('.form-message');

	            $message.removeClass('error-message').addClass('success-message');

	            if(typeof response.redirect_to !== 'undefined' && response.redirect_to.length) {
                    setTimeout(function(){
                        window.location.href = response.redirect_to;
                    }, 5000);
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
	            .html(leyka.error_while_unsibscribe_msg)
	            .show();
	
	        $submit.show();
	
	    });
	
    }
	
});


jQuery(function($){
	
    $('.donor__textfield--pass').on('focus.leyka', 'input', function(){
        $(this).parents('.donor__textfield--pass').removeClass('invalid').removeClass('valid').addClass('focus');
    }).on('blur', ':input', function(){

        // validate
        var $this = $(this),
            testVal = $this.val();

        $this.parents('.donor__textfield--pass').removeClass('focus');

        if(testVal.length > 0){
            $this.parents('.donor__textfield--pass').addClass('valid');
        } else {
            $this.parents('.donor__textfield--pass').addClass('invalid');
        }

    });

    $('.donor__textfield--pass2').on('focus.leyka', 'input', function(){
        $(this).parents('.donor__textfield--pass2').removeClass('invalid').removeClass('valid').addClass('focus');
    }).on('blur', ':input', function(){

        // validate
        var $this = $(this),
            testVal = $this.val();

        $this.parents('.donor__textfield--pass2').removeClass('focus');

        if(testVal.length > 0){
            $this.parents('.donor__textfield--pass2').addClass('valid');
        } else {
            $this.parents('.donor__textfield--pass2').addClass('invalid');
        }

    });
    
});
/*
 * Class to manipulate donation form from bottom
 */

window.LeykaGUIBottom = function($) {
    this.$ = $;
};

window.LeykaGUIBottom.prototype = {

    bindEvents: function() {

        var self = this; var $ = self.$;

        $('.leyka-js-open-form-bottom').on('click', function(e){

            e.preventDefault();

            var $this = $(this),
                $bottomForm = $this.closest('.leyka-pf-bottom');

            $('#'+$bottomForm.attr('data-target'))
                .find('.amount__figure input.leyka_donation_amount')
                .val( $this.parents('.leyka-pf-bottom').find('input[name="leyka_temp_amount"]').val() );

            /** @todo Sync the amount value & the range control. */
            $bottomForm.leykaForm('openFromBottom');

        });

    }

};

jQuery(document).ready(function($){
    
    leykaGUIBottom = new LeykaGUIBottom($);
    leykaGUIBottom.bindEvents();
    
}); //jQuery

/*
 * Class to manipulate donation form from campaign carda
 */

window.LeykaGUICard = function($) {
    this.$ = $;
};

window.LeykaGUICard.prototype = {

    bindEvents: function() {
        var self = this; var $ = self.$;
    }

};

jQuery(document).ready(function($){

    leykaGUICard = new LeykaGUICard($);
    leykaGUICard.bindEvents();

}); //jQuery

jQuery(document).ready(function($){
	$('.inpage-card__toggle-excerpt-links').on('click', 'a', function(e){
        e.preventDefault();
		//console.log($(this).closest('.inpage-card__excerpt'));
		$(this).closest('.inpage-card__excerpt').toggleClass('expand');
	});
});
/*
 * Class to manipulate final page UI
 */

window.LeykaGUIFinal = function($) {
    this.$ = $;
    
    $('.leyka-pf__final-informyou .informyou-redirect-text').show();
};

window.LeykaGUIFinal.prototype = {
        
    bindEvents: function(){

        var self = this; var $ = self.$;

        function leyka_remembered_data(data_name, data_value, data_delete) {

            if(data_value.length) {
                return $.cookie(data_name, data_value);
            } else if(data_delete) {
                return $.cookie(data_name, '');
            } else {
                return $.cookie(data_name) ? $.cookie(data_name) : '';
                /** add local storage check... */
            }
        }

        var $success_forms = $('.leyka-success-form'),
            donation_id = leyka_remembered_data('leyka_donation_id', '', false);

        if( !donation_id ) { // Hide the success form if there are no donation ID stored...
            // $success_forms.hide();
        } else { // ... or display them if there is one in the local storage
            $success_forms.each(function(index, element) {

                var $form = $(element),
                    $donation_id_field = $form.find('input[name="leyka_donation_id"]');

                if( !$donation_id_field.val() ) {

                    $donation_id_field.val(donation_id);
                    $form.show();

                }

            });
        }

        $success_forms.on('submit', function(e){

            e.preventDefault();

            if(self.validateForm(this)) {
                self.subscribeUser();
            }

        });

        $('.leyka-js-no-subscribe').on('click', function(e){
            
            e.preventDefault();

            $(this).closest('.leyka-final-subscribe-form').slideUp(100);

            var $thankyou_block = $('.leyka-pf__final-thankyou');

            $thankyou_block.find('.informyou-redirect-text').slideDown(100);
            self.runRedirectProcess($thankyou_block);

        });

    },

    /** Subscription form validation */
    validateForm: function($form){

        var self = this,
            $ = self.$,
            form_valid = false;

        $form = $($form); // Just in case

        $form.find(':input').each(function(){

            var $input = $(this),
                type = $input.attr('type'),
                name = $input.attr('name'),
                value = $.trim($input.val()),
                $error_message = $form.find('.'+name+'-error');

            if($.inArray(type, ['text', 'email']) == 1) {

                if($input.hasClass('required') && !value) {

                    $error_message.show();
                    $input.closest('.donor__textfield').addClass('invalid');

                } else if(type === 'email' && !is_email(value)) {

                    $error_message.show();
                    $input.closest('.donor__textfield').addClass('invalid');

                } else {

                    $error_message.hide();
                    $input.closest('.donor__textfield').removeClass('invalid');
                    form_valid = true;

                }

            }

        });

        return form_valid;

    },
    
    animateRedirectCountdown: function($container){

        var self = this; var $ = self.$;
        
        var $countdown_div = $container.find('.informyou-redirect-text .leyka-redirect-countdown'),
        countdown = $countdown_div.text();

        countdown = parseInt(countdown, 10);
        countdown -= 1;
        if(countdown <= 0) {
            clearInterval(self.countdownInterval);
        }
        $countdown_div.text(String(countdown));

    },

    runRedirectProcess: function($container) {

        var self = this; var $ = self.$;
        
        var ajax_url = leyka_get_ajax_url();
        
        setTimeout(function(){
            
            var redirect_url;

            if( !ajax_url ) {
                redirect_url = '/';
            }
            else {
                redirect_url = ajax_url.replace(/\/core\/wp-admin\/.*/, '');
                redirect_url = redirect_url.replace(/\/wp-admin\/.*/, '');
            }

            window.location.href = redirect_url;

        }, 4000);

        self.countdownInterval = setInterval(self.animateRedirectCountdown.bind(null, $container), 1000);

    },

    subscribeUser: function(){

        var self = this; var $ = self.$;

        var $informyou_block = $('.leyka-pf__final-informyou');

        $.post(
            leyka_get_ajax_url(),
            $('form.leyka-success-form').serializeArray(),
            'json'
        ).done(function(response){

            if(typeof response.status != 'undefined' && response.status != 0 && typeof response.message != 'undefined') {
                $('.leyka-pf__final-error-message').html(response.message).show();
            }

            // leyka_remembered_data('leyka_donation_id', '', true); // Delete the donor data

            $informyou_block.show();
            self.runRedirectProcess($informyou_block);

        }).always(function(){

            $('.leyka-pf__final-thankyou').hide();

        });

    }
};

jQuery(document).ready(function($){

    leykaGUIFinal = new LeykaGUIFinal($);
    leykaGUIFinal.bindEvents();

}); //jQuery
/*
 * Donation form inner functionality and handlers
 */

var leykaValidateForm,
	leyka;

(function($){

	var amountIconMarks = [25, 50, 75],
		inputRangeWidth = 200,
		inputRangeButtonRadius = 17;

	leykaValidateForm = function($_form){

		var is_valid = true,
			email = $.trim($_form.find('.donor__textfield--email input').val()),
			$amount_field = $_form.find('.amount__figure input.leyka_donation_amount'),
			amount = parseInt($amount_field.val().replace(/\s/g, '')),
			$comment_filed = $_form.find(':input.leyka-donor-comment'),
			$agree_terms = $_form.find('.donor__oferta input[name="leyka_agree"]'),
			$agree_pd = $_form.find('.donor__oferta input[name="leyka_agree_pd"]'),
            $current_field = $_form.find('.donor__textfield--name input');

		if($current_field.val().length === 0 || !leyka_validate_donor_name($current_field.val())) {

            is_valid = false;
			$_form.find('.donor__textfield--name').addClass('invalid');

		}

		if(email.length === 0 || !is_email(email)) {

            is_valid = false;
			$_form.find('.donor__textfield--email').addClass('invalid');

		}

		if(
			$comment_filed.length &&
			$comment_filed.data('max-length') &&
			$comment_filed.val().length > $comment_filed.data('max-length')
		) {

            is_valid = false;
			$_form.find('.donor__textfield--comment').addClass('invalid');

		}

		if(
			($agree_terms.length && !$agree_terms.prop('checked')) ||
			($agree_pd.length && !$agree_pd.prop('checked'))
		) {

            is_valid = false;
			$_form.find('.donor__oferta').addClass('invalid');

		}

		if(amount <= 0 || amount < $amount_field.data('min-value') || amount > $amount_field.data('max-value')) {
            is_valid = false;
		}

		return is_valid;

	};

    var methods = {
        'defaults': {
            'color': 'green'
        },
        'open': open,
        'close': close,
        'openFromBottom': openFromBottom,
        // 'redirectForm': redirectForm,
        'init': init
    };

    function init(options) {

        setupAmount();
        setupPaymentsGrid();
        setupDonorForm();
        bindEvents();

    }

    /* event handlers */
    function bindEvents() {

        bindNavigationEvents();
        bindAmountStepEvents();
        bindPaymentStepEvents();
        bindDonorStepEvents();
        bindOfertaEvents();
        bindPdEvents();
        bindHistoryEvents();
        bindSubmitPaymentFormEvent();

    }

    function bindSubmitPaymentFormEvent() {

        $('.leyka-pf__form').on('submit.leyka', 'form.leyka-revo-form', function(e){

            var $_form = $(this),
                $active_step = $_form.find('.step.step--active'),
                $pm_selected = $_form.find('input[name="leyka_payment_method"]:checked');

            if( !$active_step.hasClass('step--person') ) { // Do not validate + submit if donor's data step not reached yet

                if($active_step.hasClass('step--amount')) {

                    var $proceed_button = $_form.find('.step.step--amount .step__action--amount a');
                    if($proceed_button.length < 2) {
                        $proceed_button.click();
                    }

                }

                return false;

            }

			e.preventDefault();

            if( !leykaValidateForm($_form) ) { // Form errors exist

                e.preventDefault();
                e.stopPropagation();
                return;

            }

            if($pm_selected.data('processing') !== 'default') {

                if($pm_selected.data('processing') !== 'custom-process-submit-event') {
                    e.stopPropagation();
                }
                return;

            }

            // Open "waiting" form step:
            var $redirect_step = $_form.closest('.leyka-pf').find('.leyka-pf__redirect'),
                data_array = $_form.serializeArray(),
                data = {action: 'leyka_ajax_get_gateway_redirect_data'};

            for(var i=0; i<data_array.length; i++) {
                data[data_array[i].name] = data_array[i].value;
            }

            if($pm_selected.data('ajax-without-form-submission')) {
                data['without_form_submission'] = true;
            }

            $redirect_step.addClass('leyka-pf__redirect--open');

            // Get gateway redirection form and submit it manually:
            $.post(leyka_get_ajax_url(), data).done(function(response){

                response = $.parseJSON(response);

                // Wrong answer from ajax handler:
                if( !response || typeof response.status === 'undefined' ) {

//                         $errors.html(leyka.ajax_wrong_server_response).show();
//                         $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
//                             scrollTop: $errors.offset().top - 35
//                         }, 250);

                    return false;

                } else if(response.status !== 0 && typeof response.message !== 'undefined') {

                    // $errors.html(response.message).show();
                    // $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                    //     scrollTop: $errors.offset().top - 35
                    // }, 250);

                    return false;

                } else if( !response.payment_url ) {

                    // $errors.html(leyka.cp_not_set_up).show();
                    // $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                    //     scrollTop: $errors.offset().top - 35
                    // }, 250);

                    return false;

                }

                var redirect_form_html = '<form class="leyka-auto-submit" action="'+response.payment_url+'" method="post">';

                $.each(response, function(field_name, value){
                    if(field_name !== 'payment_url') {
                        redirect_form_html += '<input type="hidden" name="'+field_name+'" value="'+value+'">';
                    }
                });
                redirect_form_html += '</form>';

                $redirect_step.append(redirect_form_html);

                if(typeof response.submission_redirect_type === 'undefined' || response.submission_redirect_type === 'auto') {
                    $redirect_step.find('.leyka-auto-submit').submit();
                } else if(response.submission_redirect_type === 'redirect') {
                    window.location.href = $redirect_step.find('.leyka-auto-submit').prop('action');
                }

            });

        });

    }

    function bindDonorStepEvents() {

        $('.donor__textfield').on('focus.leyka', ':input', function(){
            $(this).parents('.donor__textfield').removeClass('invalid').removeClass('valid').addClass('focus');
        }).on('blur.leyka', ':input', function(){

            var $this = $(this),
                $field_wrapper = $this.parents('.donor__textfield'),
                test_val = $this.val();

            $field_wrapper.removeClass('focus');

            // Validate:
            if($field_wrapper.hasClass('donor__textfield--name')) {

                if(test_val.length > 0) {
                    $field_wrapper.addClass('valid');
                } else {
                    $field_wrapper.addClass('invalid');
                }

            } else if($field_wrapper.hasClass('donor__textfield--email')) {

                if(test_val.length > 0 && is_email(test_val)){
                    $field_wrapper.addClass('valid');
                } else {
                    $field_wrapper.addClass('invalid');
                }

            } else if($field_wrapper.hasClass('donor__textfield--phone')) {

                if(test_val.length > 0 && is_phone_number(test_val)){
                    $field_wrapper.addClass('valid');
                } else {
                    $field_wrapper.addClass('invalid');
                }

            } else if($field_wrapper.hasClass('donor__textfield--comment')) {

                if(test_val.length && $this.data('max-length') && test_val.length > $this.data('max-length')) {
                    $field_wrapper.addClass('invalid');
                } else {
                    $field_wrapper.addClass('valid');
                }

            }

        });

        // $('.donor__textfield--name')/*.on('focus.leyka', ':input', function(){
        //     $(this).parents('.donor__textfield--name').removeClass('invalid').removeClass('valid').addClass('focus');
        // })*/.on('blur', ':input', function(){
        //
        //     // validate
        //     var $this = $(this),
        //         testVal = $this.val();
        //
        //     $this.parents('.donor__textfield--name').removeClass('focus');
        //
        //     if(testVal.length > 0){
        //         $this.parents('.donor__textfield--name').addClass('valid');
        //     } else {
        //         $this.parents('.donor__textfield--name').addClass('invalid');
        //     }
        //
        // });
        //
        // $('.donor__textfield--email')/*.on('focus.leyka', ':input', function(){
        //     $(this).parents('.donor__textfield--email').removeClass('invalid').removeClass('valid').addClass('focus');
        // })*/.on('blur', ':input', function(){
        //
        //     // Validate:
        //     var $this = $(this),
        //         test_val = $.trim($this.val());
        //
        //     $this.parents('.donor__textfield--email').removeClass('focus');
        //
        //     if(test_val.length > 0 && is_email(test_val)){
        //         $this.parents('.donor__textfield--email').addClass('valid');
        //     } else {
        //         $this.parents('.donor__textfield--email').addClass('invalid');
        //     }
        //
        // });
        //
        // $('.donor__textfield--comment')/*.on('focus.leyka', ':input', function(){
        //     $(this).parents('.donor__textfield--comment').removeClass('invalid').removeClass('valid').addClass('focus');
        // })*/.on('blur', ':input', function(){
        //
        //         // validate
        //         var $this = $(this),
        //             testVal = $.trim($this.val());
        //
        //         $this.parents('.donor__textfield--comment').removeClass('focus');
        //
        //         if(testVal.length && $this.data('max-length') && testVal.length > $this.data('max-length')) {
        //             $this.parents('.donor__textfield--comment').addClass('invalid');
        //         } else {
        //             $this.parents('.donor__textfield--comment').addClass('valid');
        //         }
        //     });

    }

    function bindOfertaEvents() {

        $('.leyka-pf-revo .leyka-js-oferta-trigger').on('click.leyka', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').addClass('leyka-pf--oferta-open');

        });

        $('.leyka-pf-revo .leyka-js-oferta-close').on('click.leyka', function(e){
            e.preventDefault();

            $(this)
                .parents('.leyka-pf').find('.donor__oferta')
                .removeClass('invalid').find('input[name="leyka_agree"]')
                .prop('checked', true);

            $(this).parents('.leyka-pf').removeClass('leyka-pf--oferta-open');

        });

        // agree
        $('.leyka-pf-revo .donor__oferta').on('change.leyka', 'input:checkbox', function(){

            if( $(this).parents('.donor__oferta').find('input:checkbox.required:not(:checked)').length ) {
                $(this).parents('.donor__oferta').addClass('invalid');
            } else {
                $(this).parents('.donor__oferta').removeClass('invalid');
            }

        });
    }

    function bindPdEvents() {

        $('.leyka-pf-revo .leyka-js-pd-trigger').on('click.leyka', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').addClass('leyka-pf--pd-open');

        });

        $('.leyka-pf-revo .leyka-js-pd-close').on('click.leyka', function(e){
            e.preventDefault();

            $(this)
                .parents('.leyka-pf').find('.donor__oferta')
                .removeClass('invalid').find('input[name="leyka_agree_pd"]')
                .prop('checked', true);

            $(this).parents('.leyka-pf').removeClass('leyka-pf--pd-open');

        });

        // agree
        $('.leyka-pf-revo .donor__oferta').on('change.leyka', 'input:checkbox', function(){

            if( $(this).parents('.donor__oferta').find('input:checkbox.required:not(:checked)').length ) {
                $(this).parents('.donor__oferta').addClass('invalid');
            } else {
                $(this).parents('.donor__oferta').removeClass('invalid');
            }
        });
    }

    function bindHistoryEvents() {

        $('.leyka-js-history-close').on('click', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf--history-open').removeClass('leyka-pf--history-open');
        });

        $('.leyka-js-history-more').on('click', function(e){
            e.preventDefault();
            $(this).parents('.leyka-pf, .leyka-pf-bottom').addClass('leyka-pf--history-open');
        });

    }

    function bindPaymentStepEvents() {
        $('.payment-opt__radio').change(function(){
            selectPaymentProvider($(this));
        });
    }

    function bindNavigationEvents() {
        
        $('.leyka-js-another-step').on('click', function(e){
            e.preventDefault();
            goAnotherStep($(this));
        });
        
        $('.leyka-js-complete-donation').click(function(){
            $(this).closest('.leyka-pf').leykaForm('close');
        });

        //if it's should be here
        $('.leyka-submit-errors').on('click', function(e){

            e.preventDefault();

            var $this = $(this);

            $this.hide();
            goFirstStep();

        });

    }

    function bindAmountStepEvents() {

        $('.leyka-js-amount').on('click', function(e){
            e.preventDefault();
            setChosenAmount($(this));
        });

        var $amount_range = $('.amount_range').find('input'),
        $amount_figure = $('.amount__figure').find('input.leyka_donation_amount');

        // Sync of amount field
        $amount_range.on('change input', syncFigure);
        $amount_figure.on('change input', syncRange);
        $amount_range.on('change input', syncAmountIcon);
        $amount_range.on('change input', syncCustomRangeInput);

        $amount_figure
            .on('focus', function(){
                $(this).parents('.amount__figure').addClass('focus');
            })
            .on('blur', function(){
                $(this).parents('.amount__figure').removeClass('focus');
            });

    }

    function goFirstStep() {

        var $_form = $(this).closest('.leyka-pf');

        $_form.find('.payment-opt__radio').prop('checked', false); // Reset a chosen PM

        $_form.find('.step').removeClass('step--active');
        $_form.find('.step:first').addClass('step--active');
        $_form.find('.leyka-pf__redirect').removeClass('leyka-pf__redirect--open');

    }

    function goAnotherStep($_link) {

        var target = $_link.attr('href'),
        $_form = $_link.closest('.leyka-pf');

        if(target === 'cards') {
            $_form.find('.payment-opt__radio').prop('checked', false); // Reset a chosen PM
        }

        $_form.find('.step').removeClass('step--active');
        $_form.find('.step--'+target).addClass('step--active');
        $_form.find('.leyka-pf__final-screen').removeClass('leyka-pf__final--open').removeClass('leyka-pf__final--open-half');

    }

    function setupAmount() {
        $('.amount__figure input.leyka_donation_amount').each(function(){

            var $this = $(this),
				$amount_range = $this.parents('.step__fields').find('.amount_range input'),
				value = parseInt($(this).val());

            if(
            	!Number.isInteger(value) ||
				value < $amount_range.attr('min') ||
				value > $amount_range.attr('max')
			) {
                value = $amount_range.data('default-value');
            }

			$this.val(value);
            $amount_range.val(value);

            // Sync with bottom
            var formId = $this.closest('.leyka-pf').attr('id');
            $('div[data-target = "'+formId+'"]').find('input').val(value);
        });
    }

    function syncFigure(event, options) {
        var val = $(this).val();
        
        if(options && options['skipSyncFigure']) {
            // skip sync figure after range change trigger
        }
        else {
            $(this).parents('.step__fields').find('.amount__figure').find('input.leyka_donation_amount').val(val);
            $(this).parents('.step__fields').removeClass('invalid');
        }
    }

    function syncRange() {

        var $this = $(this),
            val = $this.val(),
            $form = $this.parents('.leyka-pf__form');

        if(!val) {
            val = 0;
        }
        
        $form.find('.step--amount .step__fields').removeClass('invalid');
        $form.find('.amount_range input').val(val).trigger('change', {'skipSyncFigure': true} );

    }

    function getAmountPercent($rangeInput) {
        var val = $rangeInput.val();
        var min, max;

        try {
            min = parseInt($rangeInput.attr('min'));
            max = parseInt($rangeInput.attr('max'));
        } catch(e) {
            min = 0;
            max = 0;
        }

        var percent = 0;
        if(max) {
            percent = 100 * (val - min) / (max - min);
        }
        return percent;
    }

    function syncAmountIcon() {
        var percent = getAmountPercent($(this));

        var amountIconIndex = 1;
        for(var i in amountIconMarks) {
            rangePercent = amountIconMarks[i];
            if(percent >= rangePercent) {
                amountIconIndex = parseInt(i) + 2;
            }
        }

        var $svgIcon = $('.amount__icon .svg-icon');

        // set icon class
        $svgIcon.find('use').attr("xlink:href", "#icon-money-size" + amountIconIndex);

        // set size class
        $svgIcon.addClass('icon-money-size' + amountIconIndex);
        if(amountIconIndex != 1) {
            $svgIcon.removeClass('icon-money-size1')
        }
        for(var i in amountIconMarks) {
            var size = parseInt(i) + 2;
            if(amountIconIndex != size) {
                $svgIcon.removeClass('icon-money-size' + size);
            }
        }
    }

    function syncCustomRangeInput() {

        var percent = getAmountPercent($(this)),
            leftOffset = (inputRangeWidth - 2 * inputRangeButtonRadius) * percent / 100;

        $('.range-circle').css({'left': (leftOffset) + 'px'});
        $('.range-color-wrapper').width(leftOffset + inputRangeButtonRadius);

    }

    function setChosenAmount($_link) {

        var target = $_link.attr('href'),
            $_step = $_link.parents('.step'),
            $_form = $_link.parents('.leyka-pf__form'),
			$amount_field = $_step.find('.amount__figure input.leyka_donation_amount'),
			amount = parseInt($amount_field.val());

        if(
        	!Number.isInteger(amount) ||
			amount < $amount_field.data('min-value') ||
			amount > $amount_field.data('max-value')
		) {
            $_step.find('.step__fields').addClass('invalid');
        } else {

            $_step.find('.step__fields').removeClass('invalid');

            if($_link.hasClass('monthly')) {

                $_step.find('input.is-recurring-chosen').val(1);
                $_form.find('.remembered-amount').text(amount);
                $_form.find('.remembered-monthly').show();

                var $recurring_option = $_form.find('.payment-opt__radio[data-has-recurring="1"]:first'),
                    $remembered_pm = $_form.find('.remembered-payment');

				// Remember payment option
                $remembered_pm.closest('.leyka-js-another-step').attr('href', 'amount');
                $recurring_option.prop('checked', true);
                $remembered_pm.text($recurring_option.closest('.payment-opt').find('.payment-opt__label').text());

            } else {

                $_step.find('input.is-recurring-chosen').val(0);
                $_form.find('.remembered-amount').text(amount);
                $_form.find('.remembered-monthly').hide();
                $_form.find('.remembered-payment').parents('.leyka-js-another-step').attr('href', 'cards');

                $_form.find('.payment-opt__radio').prop('checked', false); // Reset payment option

            }

            $_step.removeClass('step--active');
            $_form.find('.step--'+target).addClass('step--active');
        }
    }

    /** payment step **/
    function setupPaymentsGrid() {
        
        var $pg = $('.payments-grid');
        if( $pg.find('.payment-opt').length <= 4 ) {
            $pg.css('overflow-y', 'hidden');
        }
        
    }
    
    function selectPaymentProvider($_opt) {

        var name = $_opt.parents('.payment-opt').find('.payment-opt__label').text(),
            $_step = $_opt.parents('.step'),
            $_form = $_opt.parents('.leyka-pf__form');

        $('html').addClass('leyka-js--open-modal');

        $_form.find('.remembered-payment').text(name);

        $_step.removeClass('step--active');

        var $step_static_step = $_form.find('.step--static.' + $_opt.val());
        if($step_static_step.length > 0) {
            $step_static_step.addClass('step--active');
        } else {

            $_form.find('.step--person').addClass('step--active');
            $('html').removeClass('leyka-js--open-modal');

        }

    }

    /* donor step */
    function setupDonorForm() {
        $('.donor__textfield--name').removeClass('invalid').removeClass('valid');
        $('.donor__textfield--email').removeClass('invalid').removeClass('valid');
        $('.donor__oferta').removeClass('invalid').removeClass('valid');
    }

    /* open/close form */
    function open() {

        var $this = $(this);

        /** @todo For the forms caching task */
        // $this.find('.leyka-pf__form').append($('#'+$this.data('form-id'))); // Get form HTML from cache

        $this.addClass('leyka-pf--active'); // Open the popup
	    $('.leyka-js').addClass('leyka-js--open-modal');

        $('.amount_range input').change(); // Sync the coins picture with the amount

    }

    function openFromBottom() {

        var formId = $(this).attr('data-target'),
            $form = $('#'+formId),
			$amount_field = $form.find('.amount__figure input'),
			amount = parseInt($amount_field.val());

        //copy amount if it's correct
        if(
        	Number.isInteger(amount) &&
			amount >= $amount_field.attr('min') &&
			amount <= $amount_field.attr('max')
		) {
			$form.find('.amount__figure input.leyka_donation_amount').val(amount);
			$form.find('.amount_range input').val(amount);
        }

        // Reset active steps
		$form.find('.step').removeClass('step--active');
		$form.find('.step--amount').addClass('step--active');

		// Open a form
		$form.addClass('leyka-pf--active');
    }

    function close() {

        var $pf = $(this);

        if($pf.hasClass('leyka-pf--oferta-open')) { // close only the Oferta terms window
            $pf.removeClass('leyka-pf--oferta-open');
        } else if($pf.hasClass('leyka-pf--pd-open')) { // close only the PD terms window
            $pf.removeClass('leyka-pf--pd-open');
        } else { // close module
            $pf.removeClass('leyka-pf--active');
	        $('.leyka-js').removeClass('leyka-js--open-modal');
        }
    }

    $.fn.leykaForm = function(methodOrOptions) {
        if(methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method '+methodOrOptions+' does not exist on jQuery.leykaForm');
        }
    }

}( jQuery ));

/*
 * Common functionaly for every page with Leyka donation forms
 */

window.LeykaPageMain = function($) {
    var self = this; self.$ = $;
    
    self.setupNoScroll();
    self.initForms();
    self.inpageCardColumns();
    self.setupCustomRangeControl();
    
    self.bindEvents();
    
    self.handleHashChange();
}

window.LeykaPageMain.prototype = {
        
    bindEvents: function(){

        var self = this; var $ = self.$;
   
        $('.leyka-js-open-form').on('click.leyka', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('open');

        });

        $('.leyka-js-close-form').on('click.leyka', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('close');

        });

        $(window).resize(function(){
            self.inpageCardColumns();
        });

        $(window).on('hashchange', function() {
            self.handleHashChange();
        });

    },

    setupNoScroll: function() {

        var self = this; var $ = self.$;
        var position = $(window).scrollTop();

        $(window).scroll(function(){

            var scroll = $(window).scrollTop();

            if($('.leyka-pf').hasClass('leyka-pf--active')){
                $(window).scrollTop(position);
            } else {
                position = scroll;
            }

        });
    },

    initForms: function() {

        var self = this; var $ = self.$;

        $('.leyka-pf').leykaForm();

        /** Leyka success widget behavior - BEGIN */

        // var $success_forms = $('.leyka-success-form'),
        //     donation_id = leyka_remembered_data('leyka_donation_id');
        //
        // if( !donation_id ) { // Hide the success form if there are no donation ID stored...
        //     // $success_forms.hide();
        // } else { // ... or display them if there is one in the local storage
        //     $success_forms.each(function(index, element) {
        //
        //         var $form = $(element),
        //             $donation_id_field = $form.find('input[name="leyka_donation_id"]');
        //
        //         if( !$donation_id_field.val() ) {
        //
        //             $donation_id_field.val(donation_id);
        //             $form.show();
        //
        //         }
        //
        //     });
        // }
        //
        // $success_forms.on('submit', function(e){
        //
        //     e.preventDefault();
        //
        //     var $this = $(this);
        //
        // });

        /** Leyka success widget behavior - END */

    },
    
    inpageCardColumns: function() {
        var self = this; var $ = self.$;
        
        var form = $('.leyka-pf');
        form.each(function(){
            var w = $('.leyka-pf').width();

            if(w >= 600) {
                $(this).addClass('card-2col');
            }
            else{
                $(this).removeClass('card-2col');
            }
        });
    },
    
    setupCustomRangeControl: function() {

        var self = this; var $ = self.$;
        
//        $('.amount__range_overlay').show();
//        $('.amount__range_custom').show();
        $('.amount__range_overlay').addClass('amount__range_custom--visible');
        $('.amount__range_custom').addClass('amount__range_custom--visible');
    },
    
    handleHashChange: function() {
        var self = this; var $ = self.$;
        
        var hash = window.location.hash.substr(1);

        if(hash.indexOf('leyka-activate-package|') > -1) {
            self.handleHashActivatePackageChange(hash);
        }
        else if(hash) {
            var parts = hash.split('|');
            if(parts.length > 0) {
                var form_id = parts[0];
                
                if(form_id) {
                    var $_form = $('.leyka-pf#' + form_id);
                    
                    if($_form.length > 0) {
                        $_form.leykaForm('open');
                        
                        for(var i in parts) {
                            var part = parts[i];
                            self.handleFinalScreenParams($_form, part);
                        }
                    }
                }
            }
        }
    },

    handleHashActivatePackageChange: function(hash) {
        var self = this; var $ = self.$;

        var $leykaForm = $('.leyka-pm-form').first();
        $leykaForm.find('.section__fields.periodicity a[data-periodicity="monthly"]').trigger('click');

        var parts = hash.split('|');
        if(parts.length > 1) {
            var amount_needed = parseInt(parts[1]);
            var $selectedSum = null;

            $leykaForm.find('.amount__figure .swiper-item').each(function(i, el){
                if(parseInt($(el).data('value')) >= amount_needed) {
                    $selectedSum = $(el);
                    return false;
                }
            });

            if(!$selectedSum) {
                $selectedSum = $leykaForm.find('.swiper-item.flex-amount-item');
                $selectedSum.find('input[name="donate_amount_flex"]').val(amount_needed);
            }

            if($selectedSum) {
                $selectedSum.trigger('click');
            }
        }
    },
    
    handleFinalScreenParams: function($_form, part) {
        if(part.search(/final-open/) > -1) {
            $_form.find('.leyka-pf__final-screen').removeClass('leyka-pf__final--open').removeClass('leyka-pf__final--open-half');
            var final_parts = part.split('_');
            try {
                var $final_screen = $_form.find('.leyka-pf__final-screen.leyka-pf__final-' + final_parts[1]);
                $final_screen.addClass('leyka-pf__final--open');
                if(final_parts[2]) {
                    $final_screen.addClass('leyka-pf__final--open-half');
                }
            }
            catch(ex) {
            }
        }
    }
}

jQuery(document).ready(function($){

    leykaPageMain = new LeykaPageMain($);
    
}); //jQuery

/** Shortcodes frontend */

var leyka; // L10n lines

jQuery(document).ready(function($){

    // Supporters list shortcode:
    $('.leyka-js-supporters-list-more').on('click.leyka', function(e){

        e.preventDefault();

        var $more_names_link = $(this),
            $names_list_wrapper = $more_names_link.parents('.list-content').find('.supporters-names-wrapper'),
            $more_names_wrapper = $more_names_link.parents('.list-content').find('.supporters-list-more-wrapper'),
            names_remain_list = $more_names_link.data('names-remain').length ?
                $more_names_link.data('names-remain').split(';') : [],
            names_loads_remain_number = parseInt($more_names_link.data('loads-remain')),
            $names_remain_number_wrapper = $more_names_link.find('.leyka-names-remain-number'),
            names_remain_number = parseInt($names_remain_number_wrapper.text()),
            names_per_load = $more_names_link.data('names-per-load'),
            names_to_append = [];

        if( !names_loads_remain_number || !names_remain_list.length ) {
            return;
        }

        for(var i=0; i < names_per_load && names_remain_list.length > 0; i++) {
            names_to_append.push(names_remain_list.shift());
        }

        if(names_to_append.length) {
            $names_list_wrapper.append(', ').append(names_to_append.join(', '));
        }
        names_loads_remain_number -= 1;
        names_remain_number -= names_per_load; //(names_remain_number >= names_per_load ? names_per_load : 1);

        $more_names_link.data('names-remain', names_remain_list.join(';'));
        $more_names_link.data('loads-remain', names_loads_remain_number);

        if(names_remain_number <= 0) {
            $more_names_wrapper.hide();
        } else {
            $names_remain_number_wrapper.html(names_remain_number); // Update the remain names number
        }

        if( !names_loads_remain_number || !names_remain_list.length ) { // If no more names or loads, remove the link
            $more_names_link.replaceWith($more_names_link.text());
        }

    });

});
/** Donor's account frontend */

jQuery(document).ready(function($){
    var $siteContent = $('#site_content');
    if(!$siteContent.length) {
		$siteContent = $('#content');
    }

    if(!$siteContent.length) {
		$siteContent = $('#site-content');
    }

    var $overlay = $('.leyka-ext-sp-activate-feature-overlay');
    var $sp = $('.leyka-ext-support-packages');

    if(!$sp.length) {
    	return;
    }

    if(!$sp.closest('body.page').length && !$sp.closest('body.single').length) {
        return;
    }

    if($overlay.length && $siteContent.length) {
        $siteContent.addClass('leyka-ext-sp-site-content');
        let $overlayFirst = $overlay.first();
        $overlayFirst.appendTo($siteContent);
        $overlayFirst.css('display', 'block');
        overlayMaxPart = 0.7;

        var paddingBottom = $overlayFirst.height();
        if($overlayFirst.height() > $siteContent.height() * overlayMaxPart) {
        	paddingBottom *= overlayMaxPart;
        }
        $siteContent.css('padding-bottom', paddingBottom + 'px');
    }

    function renderActivateButton($parentSp, $activePackage) {
    	var hasSelectedPackages = $parentSp.find('.leyka-ext-sp-card.active').length > 0;
    	var $btn = $parentSp.closest('.leyka-ext-sp-activate-feature').find('.leyka-ext-sp-subscribe-action');

    	if(hasSelectedPackages) {
    		$btn.addClass('active');
    	}
    	else {
    		$btn.removeClass('active');
    	}

    	var href = $btn.attr('href');
    	href = href.split('#')[0];
    	href += "#leyka-activate-package|";

    	if($activePackage) {
    		href += $activePackage.data('amount_needed');
    	}

    	$btn.attr('href', href);

    }

    $('.leyka-ext-sp-subscribe-action').on('click', function(e){
		return $(this).hasClass('active');
    });

    $sp.on('click', '.leyka-activate-package-link', function(e) {
    	e.stopPropagation();
    	return true;
    });

    $sp.on('click', '.leyka-ext-sp-card', function(e) {
    	e.preventDefault();
    	var $parentSp = $(this).closest('.leyka-ext-support-packages');

    	if(!$parentSp.closest('.leyka-ext-sp-activate-feature').length) {
    		return;
    	}

    	$parentSp.find('.leyka-ext-sp-card').removeClass('active');
    	$(this).addClass('active');

    	renderActivateButton($parentSp, $(this));
    });

    if($sp.closest('.leyka-ext-sp-activate-feature').length) {
    	renderActivateButton($sp, null);
    }
});

function leyka_ext_sp_init_locked_content_icons($){

    let $titles = $('.leyka-ext-sp-locked-content .entry-title');

    if($titles.length) {
        $titles.each(function(i, el){
            $(this).append($('<img>')
                .attr('src', leyka.ext_sp_article_locked_icon)
                .addClass('leyka-ext-sp-post-locked'));
        });
    }

}

jQuery(document).ready(function($){
    leyka_ext_sp_init_locked_content_icons(jQuery);
});
/*
 * Star form template functionality and handlers
 */

(function($){

    function init() {
		bindEvents();
    }

    /* event handlers */
    function bindEvents() {
        bindModeEvents();
        bindAgreeEvents();
        bindSwiperEvents();
        bindAmountEvents();
        bindDonorDataEvents();
        bindSubmitPaymentFormEvent();
        bindPMEvents();
    }
	
	function resize(e, el, k) {
        var val = $.trim(el.value);
        
        if(!val) {
            $(el).addClass('empty');
            
            if(!e || e.type == 'blur') {
                setAmountPlaceholder(el);
                val = $(el).attr('placeholder');
                $(el).siblings('.currency').hide();
                $(el).addClass('show-ph');
            }
            else if(e.type == 'focus') {
                $(el).siblings('.currency').show();
                $(el).removeClass('show-ph');
            }
        }
        else {
            $(el).removeClass('empty');
            $(el).removeClass('show-ph');
        }
        
        setAmountInputValue($(el).closest('.leyka-tpl-star-form'), $(el).val());
	}
    
    function setAmountPlaceholder(el) {
        if(isMobileScreen()) {
            $(el).prop('placeholder', $(el).data('mobile-ph'));
        }
        else {
            $(el).prop('placeholder', $(el).data('desktop-ph'));
        }
    }
	
	function bindAmountEvents() {
		
		function resizable (el, factor) {
			var k = Number(factor) || 7.7;
			var e = 'keyup,keypress,focus,blur,change'.split(',');
			for(var i in e) {
				el.addEventListener(e[i], function(e){resize(e, el, k);}, false);
			}
			resize(null, el, k);
		}

		$('.donate_amount_flex').each(function(i, el) {
            if(parseInt($(this).css('font-size')) <= 16) {
                resizable(el, 7);
            }
            else {
                resizable(el, 11.1);
            }
            setAmountPlaceholder(el);
		});
        
        $('.leyka-tpl-star-form .amount__figure .swiper-item.selected').each(function(i, el){
            setAmountInputValue($(el).closest('.leyka-tpl-star-form'), getAmountValueFromControl($(el)));
        });
        
        $('.leyka-tpl-star-form .flex-amount-item').on('blur', 'input', function(){
            $(this).closest('.swiper-item').removeClass('focus');
            if(!$.trim($(this).val())) {
                $(this).closest('.swiper-item').addClass('empty');
            }
        });
        
        var keypressTimeout = null;
        $('.leyka-tpl-star-form .flex-amount-item').on('keyup', 'input', function(){
            var $_form = $(this).closest('form.leyka-pm-form');
            
            if(keypressTimeout) {
                clearTimeout(keypressTimeout);
                keypressTimeout = null;
            }
            
            if(!keypressTimeout) {
                keypressTimeout = setTimeout(function(){
                    checkFormFillCompletion($_form);
                    keypressTimeout = null;
                }, 500);
            }
        });
        
        $('.leyka-tpl-star-form .flex-amount-item input').each(function(i, el){
            if(!$.trim($(el).val())) {
                $(el).parent().addClass('empty');
            }
        });
	}
    
    function getAmountValueFromControl($el) {
        var $predefinedAmount = $el.find('span.amount');
        var val = '';
        
        if($predefinedAmount.length > 0) {
            val = $el.find('span.amount').text();
        }
        else {
            val = $el.find('input.donate_amount_flex').val();
        }
        
        return val;
    }
    
    function setAmountInputValue($form, amount) {
        $form.find('input.leyka_donation_amount').val(amount);
    }

    function bindModeEvents() {

        $('.leyka-tpl-star-form .section__fields.periodicity').on('click', 'a', function(e){
			e.preventDefault();
			
			$(this).closest('.section__fields').find('a').removeClass('active');
			$(this).addClass('active');
            
            var $_form = $(this).closest('form.leyka-pm-form');
            setupPeriodicity($_form);
            setupSwiperWidth($_form);
        });
        
        $('.leyka-tpl-star-form form.leyka-pm-form').each(function(){
            setupPeriodicity($(this));
            setupSwiperWidth($(this));
        });
    }

    function setupSwiperWidth($_form) {
        // amount swiper setup
        $('.amount__figure.star-swiper .swiper-list .swiper-item').last().css('margin-right', '0px');
        
        // pm swiper setup
        var $swiper = $_form.find('.payments-grid .star-swiper');
        // $list is empty in full-list width mode
        var $list = $swiper.find('.swiper-list');

        var $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)').first();
        if($activeItem.length == 0) {
            $swiper.find('.swiper-item:not(.disabled)').first().addClass('selected');
            $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)').first();
            $activeItem.find('input[type=radio]').prop('checked', true).change();
        }

        $list.find('.swiper-item:not(.disabled)').css('margin-right', '16px');
        $list.find('.swiper-item:not(.disabled)').last().css('margin-right', '0px');        
        $list.css('width', '100%');

        // fix max width must work in swiper and full width mode, so use $swiper insted $list
        var maxWidth = $swiper.closest('.leyka-payment-form').width();

        if($swiper.find('.full-list').length) {
            maxWidth -= 60;
            $swiper.find('.payment-opt__label').css('max-width', maxWidth);
            $swiper.find('.payment-opt__icon').css('max-width', maxWidth);
            //$list.find('.swiper-item').css('min-width', maxWidth);
        }
        else {
            maxWidth -= 184;
            $swiper.find('.payment-opt__label').css('max-width', maxWidth);
            $swiper.find('.payment-opt__icon').css('max-width', maxWidth);

            $swiper.find('.swiper-item').each(function(i, item){
                var w1 = $(item).find('.payment-opt__label').width();
                var w2 = $(item).find('.pm-icon').length * 40; // max width of pm icon
                $(item).css('min-width', Math.min(maxWidth, Math.max(w1, w2)) + 64);
            });

            // fix for FF and Safari
            var $activePMItem = $swiper.find('.swiper-item:not(.disabled)');
            if($activePMItem.length <= 1) {
                $activePMItem.css('width', '100%');
            }
            else {
                $activePMItem.css('width', 'auto');
            }
        }
        
        toggleSwiperArrows($swiper);
        swipeList($swiper, $activeItem);
    }
    
    function setupPeriodicity($_form) {
        var isRecurring = false;
        var $activePeriodicityTab = $_form.find('.section__fields.periodicity a.active');

        if($activePeriodicityTab.length) {
            isRecurring = $activePeriodicityTab.data('periodicity') == 'monthly';
        }
        else {
            isRecurring = parseInt($_form.find('input.is-recurring-chosen').val()) == 1;
        }
        
        $_form.find('.section__fields.periodicity a').removeClass('active');
        if(isRecurring) {
            $_form.find('.section__fields.periodicity a[data-periodicity=monthly]').addClass('active');
            $_form.find('input.is-recurring-chosen').val("1");
            $_form.find('.payments-grid .swiper-item').each(function(i, el){
                if($(el).find('input[data-has-recurring=0]').length > 0) {
                    $(el).addClass('disabled').removeClass('selected');
                    $(el).find('input[type=radio]').prop('checked', false);
                }
            });
        }
        else {
            $_form.find('.section__fields.periodicity a[data-periodicity=once]').addClass('active');
            $_form.find('input.is-recurring-chosen').val("0");
            $_form.find('.payments-grid .swiper-item').each(function(i, el){
                if($(el).find('input[data-has-recurring=0]').length > 0) {
                    $(el).removeClass('disabled');
                }
            });
        }
        
        checkFormFillCompletion($_form);
    }

    function bindSwiperEvents() {
        $('.leyka-tpl-star-form .star-swiper').on('click', '.swiper-item', function(e){

            var $this = $(this);

        	if($this.hasClass('selected')) {
        		return;
        	}

            $this.siblings('.swiper-item.selected').removeClass('selected');
            $this.addClass('selected');
            $this.find('input[type="radio"]').prop('checked', true).change();

            var $swiper = $this.closest('.star-swiper');
            swipeList($swiper, $this);
            toggleSwiperArrows($swiper);

            if($this.hasClass('flex-amount-item')) {
                $this.find('input[type="number"]').focus();
                $this.addClass('focus').removeClass('empty');
            }
            
            if($swiper.hasClass('amount__figure')) {
                setAmountInputValue($this.closest('.leyka-tpl-star-form'), getAmountValueFromControl($this));
            }

            checkFormFillCompletion($swiper.closest('form.leyka-pm-form'));

        });

        $('.leyka-tpl-star-form .star-swiper .swiper-item:first').click();

        $('.leyka-tpl-star-form .star-swiper .swiper-item.selected')
            .find('input[type="radio"]')
                .prop('checked', true)
                .change();

        $('.leyka-tpl-star-form .star-swiper').on('click', 'a.swiper-arrow', function(e){

            e.preventDefault();

			var $this = $(this),
                $swiper = $this.closest('.star-swiper'),
                $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)'),
                $nextItem = null;

			if($this.hasClass('swipe-right')) {
				$nextItem = $activeItem.next('.swiper-item:not(.disabled)');
			} else {
				$nextItem = $activeItem.prev('.swiper-item:not(.disabled)');
			}

			if( !$nextItem.length ) {
				if($this.hasClass('swipe-right')) {
					$nextItem = $swiper.find('.swiper-item:not(.disabled)').first();
				} else {
					$nextItem = $swiper.find('.swiper-item:not(.disabled)').last();
				}
			}

			if($nextItem.length) {
				$activeItem.removeClass('selected');
				$nextItem.addClass('selected');
                $nextItem.find('input[type="radio"]').prop('checked', true).change();
			}

            swipeList($swiper, $nextItem);
            toggleSwiperArrows($swiper);

            if($nextItem.hasClass('flex-amount-item')) {
                $nextItem.find('input[type=number]').focus();
                $nextItem.addClass('focus').removeClass('empty');
            }

            if($swiper.hasClass('amount__figure')) {
                setAmountInputValue($nextItem.closest('.leyka-tpl-star-form'), getAmountValueFromControl($nextItem));
            }

            checkFormFillCompletion($swiper.closest('form.leyka-pm-form'));

        });
        
        $('.star-swiper').each(function() {
            toggleSwiperArrows($(this));
        });
    }
    
    function swipeList($swiper, $activeItem) {
        var $list = $swiper.find('.swiper-list');
        $list.stop( true, true )
        
        var dif = $list.width() - $swiper.width();
        if(dif <= 0) {
            $list.width($swiper.width());
            $list.css('left', 0);
            return;
        }
        
        var left = parseInt($list.css('left'));
        if($swiper.find('.swiper-item:not(.disabled)').first().hasClass('selected')) {
            left = 0;
        }
        else if($swiper.find('.swiper-item:not(.disabled)').last().hasClass('selected')) {
            left = -dif;
        }
        else {
            left = $swiper.width() / 2 - ($activeItem.offset().left - $list.offset().left) - $activeItem.width() / 2;
            left -= 24; // minus margin * 1.5
        }
        
        $list.animate({
            'left': left
        });
    }
    
    function toggleSwiperArrows($swiper) {

        let $list = $swiper.find('.swiper-list'),
            listWidth = 0;

        if(isMobileScreen()) {
            $list.width($swiper.width());
        } else {
            $list.find('.swiper-item:not(.disabled)').each(function(){
                listWidth += $(this).outerWidth(true);
            });
            $list.width(listWidth);
        }

        if($list.find('.swiper-item:not(.disabled)').length <= 1) {
            $swiper.addClass('only-one-item');
        } else {
            $swiper.removeClass('only-one-item');
        }

        if($list.width() <= $swiper.width()) {

            $swiper
                .removeClass('show-left-arrow')
                .removeClass('show-right-arrow');

            $list.width($swiper.width()).css('left', 0);

            return;

        }

        if($swiper.find('.swiper-item:not(.disabled)').first().hasClass('selected')) {
            $swiper.removeClass('show-left-arrow');
        } else {
            $swiper.addClass('show-left-arrow');
        }

        if($swiper.find('.swiper-item:not(.disabled)').last().hasClass('selected')) {
            $swiper.removeClass('show-right-arrow');
        } else {
            $swiper.addClass('show-right-arrow');
        }

    }
    
    // agree functions
    function bindAgreeEvents() {

        bindOfertaEvents();
        bindPdEvents();
        
        // agree
        $('.leyka-tpl-star-form .donor__oferta').on('change.leyka', 'input:checkbox', function(){

            let $donorOferta = $(this).closest('.donor__oferta');
            
            if( $donorOferta.find('input:checkbox.required:not(:checked)').length ) {
                $donorOferta.addClass('invalid');
            } else {
                $donorOferta.removeClass('invalid');
            }
            
            checkFormFillCompletion($(this).closest('form.leyka-pm-form'));

        });

    }
    
    function bindOfertaEvents() {
        
        $('.leyka-tpl-star-form .leyka-js-oferta-trigger').on('click.leyka', function(e){

            e.preventDefault();

            let $form = $(this).parents('.leyka-tpl-star-form');
            $form
                .addClass('leyka-pf--oferta-open')
                .find('.leyka-pf__agreement')
                    .css('top', getAgreeModalTop($form));

            // $([document.documentElement, document.body]).animate({
            //     scrollTop: $form.offset().top - 64
            // });

        });

        $('.leyka-tpl-star-form .leyka-pf__agreement.oferta .agreement__close').on('click.leyka', function(e){

            e.preventDefault();

            $(this).parents('.leyka-tpl-star-form').removeClass('leyka-pf--oferta-open');

        });
    }

    function bindPdEvents() {

        $('.leyka-tpl-star-form .leyka-js-pd-trigger').on('click.leyka', function(e){

            e.preventDefault();

            let $form = $(this).parents('.leyka-tpl-star-form');
            $form
                .addClass('leyka-pf--pd-open')
                .find('.leyka-pf__agreement')
                    .css('top', getAgreeModalTop($form));

            $([document.documentElement, document.body]).animate({
                scrollTop: $form.offset().top - 64
            });

        });

        $('.leyka-tpl-star-form .leyka-pf__agreement.pd .agreement__close').on('click.leyka', function(e){

            e.preventDefault();

            $(this).parents('.leyka-tpl-star-form').removeClass('leyka-pf--pd-open');

        });
    }

    function getAgreeModalTop($form) {

        let $wp_admin_bar = $('#wpadminbar');

        return ($wp_admin_bar.length ? $wp_admin_bar.height() : 32) + 'px';

    }

    function addError($errors_block, error_html) {

        if( !$errors_block.length || !error_html.length ) {
            return true;
        }

        $errors_block.html(error_html).show();

        // Center the error block in the viewport
        $('html, body').animate({
            scrollTop: $errors_block.offset().top - ($(window).height() - $errors_block.outerHeight()) / 2
        }, 250);

        return false;

    }

    function bindSubmitPaymentFormEvent() {

        $('.leyka-tpl-star-form').on('submit.leyka', 'form.leyka-pm-form', function(e){

            var $form = $(this),
                $errors = $form.parents('.leyka-payment-form').siblings('.leyka-submit-errors'),
                $pm_selected = $form.find('input[name="leyka_payment_method"]:checked');

			e.preventDefault();

            if( !leykaValidateForm($form) ) { // Form errors exist

                e.preventDefault();
                e.stopPropagation();
                return;

            }

            if($pm_selected.data('processing') !== 'default') {

                if($pm_selected.data('processing') !== 'custom-process-submit-event') {
                    e.stopPropagation();
                }
                return;

            }

            // Open "waiting" form section:
            var $redirect_section = $form.closest('.leyka-pf').find('.leyka-pf__redirect'),
                data_array = $form.serializeArray(),
                data = {action: 'leyka_ajax_get_gateway_redirect_data'};

            for(var i = 0; i < data_array.length; i++) {
                data[data_array[i].name] = data_array[i].value;
            }

            if($pm_selected.data('ajax-without-form-submission')) {
                data['without_form_submission'] = true;
            }

            // Get gateway redirection form and submit it manually:
            $.post(leyka_get_ajax_url(), data).done(function(response){

                response = $.parseJSON(response);

                // Wrong answer from ajax handler:
                if( !response || typeof response.status === 'undefined' ) {
                    return false;
                } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                    return addError($errors, response.message);
                } else if( !response.payment_url ) {
                    return false;
                }

                var redirect_form_html = '<form class="leyka-auto-submit" action="'+response.payment_url+'" method="post">';

                $.each(response, function(field_name, value){
                    if(field_name !== 'payment_url') {
                        redirect_form_html += '<input type="hidden" name="'+field_name+'" value="'+value+'">';
                    }
                });
                redirect_form_html += '</form>';

                $redirect_section.append(redirect_form_html);

                if(typeof response.submission_redirect_type === 'undefined' || response.submission_redirect_type === 'auto') {
                    $redirect_section.find('.leyka-auto-submit').submit();
                } else if(response.submission_redirect_type === 'redirect') {
                    window.location.href = $redirect_section.find('.leyka-auto-submit').attr('action'); // Don't use prop() here
                }

            });

        });

    }
    
    function bindDonorDataEvents() {
        var keypressTimeout = null;
        $('.leyka-tpl-star-form .donor__textfield').on('keyup', 'input,textarea', function(){
            var $_form = $(this).closest('form.leyka-pm-form');
            
            if(keypressTimeout) {
                clearTimeout(keypressTimeout);
                keypressTimeout = null;
            }
            
            if(!keypressTimeout) {
                keypressTimeout = setTimeout(function(){
                    checkFormFillCompletion($_form);
                    keypressTimeout = null;
                }, 500);
            }
        });
    }
    
    function checkFormFillCompletion($_form) {
        $_form.find('input[type=submit]').prop('disabled', !isFormFill($_form));
    }
    
    function isFormFill($_form) {
        
		var is_filled = true,
			email = $.trim($_form.find('.donor__textfield--email input').val()),
			$amount_field = $_form.find('.amount__figure input.leyka_donation_amount'),
			amount = parseInt($amount_field.val().replace(/\s/g, '')),
			$agree_terms = $_form.find('.donor__oferta input[name="leyka_agree"]'),
			$agree_pd = $_form.find('.donor__oferta input[name="leyka_agree_pd"]');

		if($_form.find('.donor__textfield--name input').val().length === 0) {
            is_filled = false;
		}

		if(email.length === 0) {
            is_filled = false;
		}

		if(
			($agree_terms.length && !$agree_terms.prop('checked')) ||
			($agree_pd.length && !$agree_pd.prop('checked'))
		) {
            is_filled = false;
		}

		if(amount <= 0) {
            is_filled = false;
		}
        
        return is_filled;
    }
    
    function bindPMEvents() {
        $('.leyka-tpl-star-form form.leyka-pm-form').each(function(){

            var $_form = $(this);

            toggleStaticPMForm($_form);
            togglePmSpecialFields($_form);

            $(this).find('input.payment-opt__radio').change(function(){
                if($(this).prop('checked')) {

                    toggleStaticPMForm($_form);
                    togglePmSpecialFields($_form);

                }
            });
        });

        $('.leyka-tpl-star-form .payments-grid .swiper-item.selected').each(function(i, el){
            $(this).click();
        });
    }
    
    function toggleStaticPMForm($form) {

        var $pmRadio = $form.find('input[name="leyka_payment_method"]:checked');

        if($pmRadio.data('processing') === 'static') {
            $form.find('.section--static.' + $pmRadio.val()).show();
            $form.find('.section--person').hide();
        } else {
            $form.find('.section--static').hide();
            $form.find('.section--person').show();
        }

    }

    function togglePmSpecialFields($form) {

        var $pm_radio = $form.find('input[name="leyka_payment_method"]:checked');

        $form.find('.special-field').hide();
        $form.find('.special-field.'+$pm_radio.val()).show();

    }

    function isMobileScreen() {
        return $(document).width() < 640;
    }

	init();

}( jQuery ));
