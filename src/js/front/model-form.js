/*
 * Donation form inner functionality and handlers
 */

(function($){

    var amountMin = 1, //temp - take it from options
        amountMax = 30000,
        amountIconMarks = [25, 50, 75],
        inputRangeWidth = 200,
        inputRangeButtonRadius = 17;

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
        bindHistoryEvents();
        bindSubmitPaymentFormEvent();

    }
    
    function validateForm($_form) {
        
        var error_struct = {}, 
        pName = $_form.find('.donor__textfield--name input').val(),
        pEmail = $_form.find('.donor__textfield--email input').val(),
        amount = parseInt($_form.find('.amount__figure input').val()),
        $agree = $_form.find('.donor__oferta input');
        
        if(pName.length === 0) {

            error_struct['name'] = true;
            $_form.find('.donor__textfield--name').addClass('invalid');

        }

        if(pEmail.length === 0 || !is_email(pEmail)) {

            error_struct['email'] = true;
            $_form.find('.donor__textfield--email').addClass('invalid');

        }

        if($agree.length && !$agree.prop('checked')) {

            error_struct['agree'] = true;
            $_form.find('.donor__oferta').addClass('invalid');

        }

        if( !Number.isInteger(amount) || amount < amountMin || amount > amountMax ) {
            error_struct['amount'] = true;
        }

        return Object.keys(error_struct).length ? error_struct : false;
    }

    function bindSubmitPaymentFormEvent() {

        $('.leyka-pf__form').on('submit.leyka', 'form',  function(e){

            var $_form = $(this),
                $active_step = $_form.find('.step.step--active');

            if( !$active_step.hasClass('step--person') ) { // Do not validate + submit if donor's data step not reached yet

                if($active_step.hasClass('step--amount')) {

                    var $proceed_button = $_form.find('.step.step--amount .step__action--amount a');
                    if($proceed_button.length < 2) {
                        $proceed_button.click();
                    }

                }

                e.preventDefault();
                return false;

            }

            if( !validateForm($_form) ) { // Form is valid

                if($_form.find('input[name="leyka_payment_method"]:checked').data('processing') != 'default') {
                    return;
                }

                e.preventDefault();

                // Open waiting:
                var $redirect_step = $_form.closest('.leyka-pf').find('.leyka-pf__redirect'),
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

            } else { // Errors exist

                e.preventDefault();
                e.stopPropagation();

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

        if(target == 'cards') {
            $_form.find('.payment-opt__radio').prop('checked', false); // Reset a chosen PM
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
        
        $form.find('.step--amount .step__fields').removeClass('invalid')
        $form.find('.amount_range').find('input').val(val).trigger('change', {'skipSyncFigure': true} );

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
            amount = parseInt($_step.find('.amount__figure input').val());


        if( !Number.isInteger(amount) || amount < amountMin || amount > amountMax ) { // Correct this
            $_step.find('.step__fields').addClass('invalid');
        } else {

            $_step.find('.step__fields').removeClass('invalid');

            if($_link.hasClass('monthly')) {

                $_step.find('input.is-recurring-chosen').val(1);
                $_form.find('.remembered-amount').text(amount);
                $_form.find('.remembered-monthly').show();

                var $recurring_option = $_form.find('.payment-opt__radio[data-has-recurring="1"]:first'),
                    $remembered_pm = $_form.find('.remembered-payment');

                $remembered_pm.closest('.leyka-js-another-step').attr('href', 'amount'); // Remember payment option
                $recurring_option.attr('checked', true);
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
