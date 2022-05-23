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

                var redirect_form_html = '<form class="leyka-auto-submit" action="'+response.payment_url+'" method="'+(response.payment_url.indexOf('?') === -1 ? 'post' : 'get')+'">';

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
            $(this).parents('.donor__textfield')
                .removeClass('invalid')
                .removeClass('valid')
                .addClass('focus');
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

                test_val = $this.inputmask.length ? $this.inputmask('unmaskedvalue') : test_val;

                if(test_val.length > 0 && is_phone_number(test_val)){
                    $field_wrapper.addClass('valid');
                } else {
                    $field_wrapper.addClass('invalid');
                }

            } else if($field_wrapper.hasClass('donor__textfield--date')) {

                if(test_val.length > 0 && is_date(test_val)){
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
        
        $('.leyka-js-another-step').on('click.leyka', function(e){
            e.preventDefault();
            goAnotherStep($(this));
        });
        
        $('.leyka-js-complete-donation').click(function(){
            $(this).closest('.leyka-pf').leykaForm('close');
        });

        $('.leyka-submit-errors').on('click.leyka', function(e){

            if($(e.target).is('a')) { // Click on links inside an error message shouldn't close it
                return;
            }

            e.preventDefault();

            $(this).hide();
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
        
        let $pg = $('.leyka-payment-form[data-template="star"] .payments-grid');
        if($pg.find('.payment-opt').length <= 4) {
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
