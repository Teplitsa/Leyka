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

/*
 * Class to manipulate donation form from bottom
 */

window.LeykaGUIBottom = function($) {
    this.$ = $;
}

window.LeykaGUIBottom.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-open-form-bottom').on('click', function(e){
            e.preventDefault();

            $(this).closest('.leyka-pf-bottom').leykaForm('openFromBottom');
        });
        
    }

}

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
/*
 * Class to manipulate donation form from campaign carda
 */

window.LeykaGUIFinal = function($) {
    this.$ = $;
};

window.LeykaGUIFinal.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-no-subscribe').click(function(){
            $(this).closest('.leyka-final-subscribe-form').remove();
        });
        
        $(".thankyou-email-me-button a").click(function(e){
            e.preventDefault();
            self.subscribeUser();
        });
    },

    subscribeUser: function(){
        var self = this; var $ = self.$;
        
        $('.leyka-pf__final-thankyou').hide();
        $('.leyka-pf__final-informyou').show();
        
        var data = {action: 'leyka_ajax_submit_subscribe'};
        
        $.post(frontend.ajaxurl, data, null, 'json')
        .done(function(json){
        })
        .fail(function(){
        })
        .always(function(){
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

(function($){

    var amountMin = 1, //temp - take it from options
        amountMax = 30000,
        amountIconMarks = [25, 50, 75],
        inputRangeWidth = 200,
        inputRangeButtonRadius = 14;

    var methods = {
        'defaults': {
            'color': 'green'
        },
        'open': open,
        'close': close,
        'openFromBottom': openFromBottom,
        'redirectForm': redirectForm,
        'init': init
    };

    function init(options) {

        setupAmount();
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
        bindHistoryEvents();
        bindSubmitPaymentFormEvent();

    }

    function bindSubmitPaymentFormEvent() {

        $('.leyka-pf__form').on('submit.leyka', 'form',  function(e){

            var $_form = $(this),
                pName = $_form.find('.donor__textfield--name input').val(),
                pEmail = $_form.find('.donor__textfield--email input').val(),
                amount = parseInt($_form.find('.amount__figure input').val()),
                agree = $_form.find('.donor__oferta input').val(),
                error = false;
            
            if(!$_form.find('.step.step--active').hasClass('step--person')) {
                if($_form.find('.step.step--active').hasClass('step--amount')) {
                    var $proceed_button = $_form.find('.step.step--amount .step__action--amount a');
                    if($proceed_button.length < 2) {
                        $proceed_button.click();
                    }
                }
                
                e.preventDefault();
                return false;
            }

            if(pName.length === 0){
                error = true;
                $_form.find('.donor__textfield--name').addClass('invalid');
            }

            if(pEmail.length === 0 || !is_email(pEmail)){
                error = true;
                $_form.find('.donor__textfield--email').addClass('invalid');
            }

            if(agree === 0){
                error = true;
                $_form.find('.donor__oferta').addClass('invalid');
            }

            if(!Number.isInteger(amount) || amount < amountMin || amount > amountMax){
                error = true;
                //what to do ????
                console.log('error amount');
            }

            if(!error){

                if($_form.find('input[name="leyka_payment_method"]:checked').data('processing') != 'default') {
                    return;
                }

                e.preventDefault();
                
                // open waiting
                var $redirect_step = $_form.parents('.leyka-pf').find('.leyka-pf__redirect'),
                    data_array = $_form.serializeArray(),
                    data = {action: 'leyka_ajax_get_gateway_redirect_data'};

                for(var i=0; i<data_array.length; i++) {
                    data[data_array[i].name] = data_array[i].value;
                }

                $redirect_step.addClass('leyka-pf__redirect--open');

                // Get gateway redirection form and submit it manually:
                $.post(leyka.ajaxurl, data).done(function(response){

                    response = $.parseJSON(response);
                    if( !response || typeof response.status == 'undefined' ) { // Wrong answer from ajax handler

                        // $errors.html(leyka.cp_wrong_server_response).show();
                        // $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                        //     scrollTop: $errors.offset().top - 35
                        // }, 250);

                        return false;

                    } else if(response.status != 0 && typeof response.message != 'undefined') {

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
                        if(field_name != 'payment_url') {
                            redirect_form_html += '<input type="hidden" name="'+field_name+'" value="'+value+'">';
                        }
                    });
                    redirect_form_html += '</form>';

                    $redirect_step.append(redirect_form_html);
                    $redirect_step.find('.leyka-auto-submit').submit();

                });

                // setTimeout(function() {
                //  $redirect_step.removeClass('leyka-pf__redirect--open');
                 //
                // }, 4500);

            } else { // Errors exist
                e.preventDefault(); // temp
            }

        });
    }
    
    function bindDonorStepEvents() {
        // validation
        
        $('.donor__textfield--name')
        .on('focus', 'input', function(){
            $(this).parents('.donor__textfield--name').removeClass('invalid').removeClass('valid').addClass('focus');

        })
        .on('blur', 'input', function(){
            $(this).parents('.donor__textfield--name').removeClass('focus');

            //validate
            var testVal = $(this).val();

            if(testVal.length > 0){
                $(this).parents('.donor__textfield--name').addClass('valid');
            }
            else {
                $(this).parents('.donor__textfield--name').addClass('invalid');
            }
        });

        $('.donor__textfield--email')
        .on('focus', 'input', function(){
            $(this).parents('.donor__textfield--email').removeClass('invalid').removeClass('valid').addClass('focus');
        })
        .on('blur', 'input', function(){
            $(this).parents('.donor__textfield--email').removeClass('focus');

            //validate
            var testVal = $(this).val();

            if(testVal.length > 0 && is_email(testVal)){
                $(this).parents('.donor__textfield--email').addClass('valid');
            }
            else {
                $(this).parents('.donor__textfield--email').addClass('invalid');
            }
        });
    }
    
    function bindOfertaEvents() {
        
        $('.leyka-js-oferta-trigger').on('click', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').addClass('leyka-pf--oferta-open');

        });

        $('.leyka-js-oferta-close').on('click', function(e){
            e.preventDefault();

            $(this).parents('.leyka-pf').find('.donor__oferta').removeClass('invalid').find('input').prop('checked', true);
            $(this).parents('.leyka-pf').removeClass('leyka-pf--oferta-open');

        });
        
        //agree
        $('.donor__oferta').on('change', 'input', function(){

            if($(this).prop('checked')) {
                $(this).parents('.donor__oferta').removeClass('invalid');
            }
            else {
                $(this).parents('.donor__oferta').addClass('invalid');
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
    
    /* go another step */
    function goAnotherStep($_link) {
        
        var target = $_link.attr('href'),
        $_form = $_link.parents('.leyka-pf');

        if(target == 'cards') {
            //reset choice for payment
            $_form.find('.payment-opt__radio').prop('checked', false);
        }

        $_form.find('.step').removeClass('step--active');
        $_form.find('.step--'+target).addClass('step--active');
        $_form.find('.leyka-pf__final-screen').removeClass('leyka-pf__final--open').removeClass('leyka-pf__final--open-half');
        
    }

    /* amount step */
    function setupAmount() {
        $('.amount__figure input.leyka_donation_amount').each(function(){
            var val = parseInt($(this).val());

            if(!Number.isInteger(val) || val < amountMin || val > amountMax){ //correct this
                val = 500;
            }

            $(this).val(val);
            $(this).parents('.step__fields').find('.amount_range').find('input').val(val);

            //sync with bottom
            var formId = $(this).closest('.leyka-pf').attr('id');
            $('div[data-target = "'+formId+'"]').find('input').val(val);
        });
    }

    function syncFigure() {
        var val = $(this).val();
        $(this).parents('.step__fields').find('.amount__figure').find('input.leyka_donation_amount').val(val);
        $(this).parents('.step__fields').removeClass('invalid');
    }

    function syncRange() {

        var $this = $(this),
            val = $this.val(),
            $form = $this.parents('.leyka-pf__form');

        $form.removeClass('invalid').find('.amount_range').find('input').val(val).change();

    }

    function getAmountPercent($rangeInput) {
        var val = $rangeInput.val();
        var min, max;

        try {
            min = parseInt($rangeInput.attr('min'));
            max = parseInt($rangeInput.attr('max'));
        }
        catch(e) {
            min = 0;
            max = 0;
        }

        var amountIconIndex = 1;
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
        var percent = getAmountPercent($(this));
        // console.log('Percents:', percent)
        var leftOffset = (inputRangeWidth - 2 * inputRangeButtonRadius) * percent / 100;
        $('.range-circle').css({'left': (leftOffset) + 'px'});
        $('.range-color-wrapper').width(leftOffset + inputRangeButtonRadius);
    }
    
    function setChosenAmount($_link) {
        
        var target = $_link.attr('href'),
            $_step = $_link.parents('.step'),
            $_form = $_link.parents('.leyka-pf__form'),
            amount = parseInt($_step.find('.amount__figure input').val());


        if(!Number.isInteger(amount) || amount < amountMin || amount > amountMax) { //correct this
            //invalid!!!
            $_step.find('.step__fields').addClass('invalid');
        }
        else {
            $_step.find('.step__fields').removeClass('invalid');

            //remember amount
            if($_link.hasClass('monthly')){
                $_step.find('input[name="monthly"]').val(1);
                $_form.find('.remembered-amount').text(amount);
                $_form.find('.remembered-monthly').show();

                //remember payment option
                $_form.find('.remembered-payment').parents('.leyka-js-another-step').attr('href', 'amount');
                $_form.find('.payment-opt__radio[value="bcard"]').prop('checked', true);
                var name = $_form.find('.payment-opt__radio[value="bcard"]').parents('.payment-opt').find('.payment-opt__label').text();
                $_form.find('.remembered-payment').text(name);
            }
            else {
                $_step.find('input[name="monthly"]').val(0);
                $_form.find('.remembered-amount').text(amount);
                $_form.find('.remembered-monthly').hide();
                $_form.find('.remembered-payment').parents('.leyka-js-another-step').attr('href', 'cards');

                //reset choice for payment
                $_form.find('.payment-opt__radio').prop('checked', false);
            }


            $_step.removeClass('step--active');
            $_form.find('.step--'+target).addClass('step--active');
        }
    }
    
    /** payment step **/
    function selectPaymentProvider($_opt) {

        var name = $_opt.parents('.payment-opt').find('.payment-opt__label').text(),
            $_step = $_opt.parents('.step'),
            $_form = $_opt.parents('.leyka-pf__form');

        //remember
        $_form.find('.remembered-payment').text(name);

        //move
        $_step.removeClass('step--active');
        
        var $step_static_step = $_form.find('.step--static.' + $_opt.val());
        if($step_static_step.length > 0) {
            $step_static_step.addClass('step--active');
        }
        else {
            $_form.find('.step--person').addClass('step--active');
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

        $('.amount_range input').change(); // Sync the coins picture with the amount

    }

    function openFromBottom() {

        var formId = $(this).attr('data-target'),
            amount = parseInt($(this).find('input').val()),
            form = $('#'+formId);

        //copy amount if it's correct
        if(Number.isInteger(amount) && amount >= amountMin && amount <= amountMax) {
            form.find('.amount__figure input.leyka_donation_amount').val(amount);
            form.find('.amount_range input').val(amount);
        }

        //reset active steps
        form.find('.step').removeClass('step--active');
        form.find('.step--amount').addClass('step--active');

        //open form
        form.addClass('leyka-pf--active');
    }

    function close() {

        var $pf = $(this);

        if($pf.hasClass('leyka-pf--oferta-open')) { // close only the Oferta terms window
            $pf.removeClass('leyka-pf--oferta-open');
        } else { // close module
            $pf.removeClass('leyka-pf--active');
        }
    }

    function redirectForm() {

        // var $form = $(this);
        // console.log($form.serializeArray());

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
   
        $('.leyka-js-open-form').on('click', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('open');

        });
   
        $('.leyka-js-close-form').on('click', function(e){

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

        function leyka_remembered_data(data_name) {

            return $.cookie(data_name) ? $.cookie(data_name) : ''; // add local storage check...

        }

        var $success_forms = $('.leyka-success-form'),
            donation_id = leyka_remembered_data('leyka_donation_id');
        console.log('Donation ID cookie:', donation_id)

        if( !donation_id ) { // Hide the success form if there are no donation ID stored...
            $success_forms.hide();
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

            var $this = $(this);

        });

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
        
        $('.amount__range_overlay').show();
        $('.amount__range_custom').show();

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
