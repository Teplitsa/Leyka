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


function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
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
	$('.inpage-card__toggle-excerpt-links').on('click', 'a', function(){
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

        var self = this; var $ = self.$;

        $form = $($form); // Just in case
        var form_valid = false;

        $form.find(':input').each(function(){

            var $input = $(this),
                type = $input.attr('type'),
                name = $input.attr('name'),
                value = $input.val(),
                $error_message = $form.find('.'+name+'-error');

            if($.inArray(type, ['text', 'email']) == 1) {

                if($input.hasClass('required') && !value) {

                    $error_message.show();
                    $input.closest('.donor__textfield').addClass('invalid');

                } else if(type == 'email' && !is_email(value)) {

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
			pEmail = $_form.find('.donor__textfield--email input').val(),
			$amount_field = $_form.find('.amount__figure input.leyka_donation_amount'),
			amount = parseInt($amount_field.val().replace(/\s/g, '')),
			$comment_filed = $_form.find(':input.leyka-donor-comment'),
			$agree_terms = $_form.find('.donor__oferta input[name="leyka_agree"]'),
			$agree_pd = $_form.find('.donor__oferta input[name="leyka_agree_pd"]');

		if($_form.find('.donor__textfield--name input').val().length === 0) {

            is_valid = false;
			$_form.find('.donor__textfield--name').addClass('invalid');

		}

		if(pEmail.length === 0 || !is_email(pEmail)) {

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

		if(
			amount <= 0 ||
			amount < $amount_field.data('min-value') ||
			amount > $amount_field.data('max-value')
		) {
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
                $active_step = $_form.find('.step.step--active');

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

            if(leykaValidateForm($_form)) { // Form is valid

				var $pm_selected = $_form.find('input[name="leyka_payment_method"]:checked');

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

            } else { // Errors exist

                e.preventDefault();
                e.stopPropagation();

            }

        });
    }

    function bindDonorStepEvents() {

        $('.donor__textfield--name').on('focus', 'input', function(){
            $(this).parents('.donor__textfield--name').removeClass('invalid').removeClass('valid').addClass('focus');
        }).on('blur', ':input', function(){

            // validate
            var $this = $(this),
                testVal = $this.val();

            $this.parents('.donor__textfield--name').removeClass('focus');

            if(testVal.length > 0){
                $this.parents('.donor__textfield--name').addClass('valid');
            } else {
                $this.parents('.donor__textfield--name').addClass('invalid');
            }

        });

        $('.donor__textfield--email').on('focus', 'input', function(){
            $(this).parents('.donor__textfield--email').removeClass('invalid').removeClass('valid').addClass('focus');
        }).on('blur', ':input', function(){

            // validate
            var $this = $(this),
                testVal = $this.val();

            $this.parents('.donor__textfield--email').removeClass('focus');

            if(testVal.length > 0 && is_email(testVal)){
                $this.parents('.donor__textfield--email').addClass('valid');
            } else {
                $this.parents('.donor__textfield--email').addClass('invalid');
            }

        });

        $('.donor__textfield--comment').on('focus', ':input', function(){
            $(this).parents('.donor__textfield--comment').removeClass('invalid').removeClass('valid').addClass('focus');
        }).on('blur', ':input', function(){

                // validate
                var $this = $(this),
                    testVal = $this.val();

                $this.parents('.donor__textfield--comment').removeClass('focus');

                if(testVal.length && $this.data('max-length') && testVal.length > $this.data('max-length')) {
                    $this.parents('.donor__textfield--comment').addClass('invalid');
                } else {
                    $this.parents('.donor__textfield--comment').addClass('valid');
                }
            });

    }

    function bindOfertaEvents() {

        $('.leyka-js-oferta-trigger').on('click.leyka', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').addClass('leyka-pf--oferta-open');

        });

        $('.leyka-js-oferta-close').on('click.leyka', function(e){
            e.preventDefault();

            $(this)
                .parents('.leyka-pf').find('.donor__oferta')
                .removeClass('invalid').find('input[name="leyka_agree"]')
                .prop('checked', true);

            $(this).parents('.leyka-pf').removeClass('leyka-pf--oferta-open');

        });

        // agree
        $('.donor__oferta').on('change.leyka', 'input:checkbox', function(){

            if( $('.donor__oferta').find('input:checkbox.required:not(:checked)').length ) {
                $(this).parents('.donor__oferta').addClass('invalid');
            } else {
                $(this).parents('.donor__oferta').removeClass('invalid');
            }

        });
    }

    function bindPdEvents() {

        $('.leyka-js-pd-trigger').on('click.leyka', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').addClass('leyka-pf--pd-open');

        });

        $('.leyka-js-pd-close').on('click.leyka', function(e){
            e.preventDefault();

            $(this)
                .parents('.leyka-pf').find('.donor__oferta')
                .removeClass('invalid').find('input[name="leyka_agree_pd"]')
                .prop('checked', true);

            $(this).parents('.leyka-pf').removeClass('leyka-pf--pd-open');

        });

        // agree
        $('.donor__oferta').on('change.leyka', 'input:checkbox', function(){

            if( $('.donor__oferta').find('input:checkbox.required:not(:checked)').length ) {
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
        var parts = hash.split('|');
        
        if(parts.length > 0) {
            var form_id = parts[0];
            var $_form = $('#' + form_id);
            
            if($_form.length > 0) {
                $_form.leykaForm('open');
                
                for(var i in parts) {
                    var part = parts[i];
                    self.handleFinalScreenParams($_form, part);
                }
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
