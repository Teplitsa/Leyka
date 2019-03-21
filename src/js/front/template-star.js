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
        if($(document).width() >= 640) {
            $(el).prop('placeholder', $(el).data('desktop-ph'));
        }
        else {
            $(el).prop('placeholder', $(el).data('mobile-ph'));
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
            $(this).parent().removeClass('focus');
            if(!$.trim($(this).val())) {
                $(this).parent().addClass('empty');
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
            
            setupPeriodicity($(this).closest('form.leyka-pm-form'));
        });
        
        $('.leyka-tpl-star-form form.leyka-pm-form').each(function(){
            setupPeriodicity($(this));
        });
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
        
        var $swiper = $_form.find('.payments-grid .star-swiper');
        var $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)').first();
        if($activeItem.length == 0) {
            $swiper.find('.swiper-item:not(.disabled)').first().addClass('selected');
            $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)').first();
            $activeItem.find('input[type=radio]').prop('checked', true);
        }
        $swiper.find('.swiper-item:not(.disabled)').last().css('margin-right', '0px');
        
        var $list = $swiper.find('.swiper-list');
        $list.css('width', '');
        
        swipeList($swiper, $activeItem);
        toggleSwiperArrows($swiper);
        checkFormFillCompletion($swiper.closest('form.leyka-pm-form'));
    }

    function bindSwiperEvents() {
        $('.leyka-tpl-star-form .star-swiper').on('click', '.swiper-item', function(){
            $(this).siblings('.swiper-item.selected').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type=radio]').prop('checked', true).change();
            
            var $swiper = $(this).closest('.star-swiper');
            swipeList($swiper, $(this));
            toggleSwiperArrows($swiper);
            
            if($(this).hasClass('flex-amount-item')) {
                $(this).find('input[type=number]').focus();
                $(this).addClass('focus').removeClass('empty');
            }
            
            if($swiper.hasClass('amount__figure')) {
                setAmountInputValue($(this).closest('.leyka-tpl-star-form'), getAmountValueFromControl($(this)));
            }
            checkFormFillCompletion($swiper.closest('form.leyka-pm-form'));
        });
        
        $('.leyka-tpl-star-form .star-swiper .swiper-item.selected').find('input[type=radio]').prop('checked', true).change();
            
        $('.leyka-tpl-star-form .star-swiper').on('click', 'a.swiper-arrow', function(e){
			e.preventDefault();
			
			var $swiper = $(this).closest('.star-swiper');
            var $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)');
			
			var $nextItem = null;
			if($(this).hasClass('swipe-right')) {
				$nextItem = $activeItem.next('.swiper-item:not(.disabled)');
			}
			else {
				$nextItem = $activeItem.prev('.swiper-item:not(.disabled)');
			}
			
			if(!$nextItem.length) {
				if($(this).hasClass('swipe-right')) {
					$nextItem = $swiper.find('.swiper-item:not(.disabled)').first();
				}
				else {
					$nextItem = $swiper.find('.swiper-item:not(.disabled)').last();
				}
			}

			if($nextItem.length) {
				$activeItem.removeClass('selected');
				$nextItem.addClass('selected');
                $nextItem.find('input[type=radio]').prop('checked', true).change();
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
        }
        
        $list.animate({
            'left': left
        });
    }
    
    function toggleSwiperArrows($swiper) {
        var $list = $swiper.find('.swiper-list');
        
        if($list.find('.swiper-item:not(.disabled)').length <= 1) {
            console.log('hide arrows - one item');
            $swiper.addClass('only-one-item');
        }
        else {
            $swiper.removeClass('only-one-item');
        }
        
        if($list.width() <= $swiper.width()) {
            console.log('hide arrows - short list');
            $swiper.removeClass('show-left-arrow');
            $swiper.removeClass('show-right-arrow');
            $list.width($swiper.width());
            $list.css('left', 0);
            return;
        }
        
        if($swiper.find('.swiper-item:not(.disabled)').first().hasClass('selected')) {
            $swiper.removeClass('show-left-arrow');
        }
        else {
            $swiper.addClass('show-left-arrow');
        }
        
        if($swiper.find('.swiper-item:not(.disabled)').last().hasClass('selected')) {
            $swiper.removeClass('show-right-arrow');
        }
        else {
            $swiper.addClass('show-right-arrow');
        }
    }
    
    // agree functions
    function bindAgreeEvents() {
        bindOfertaEvents();
        bindPdEvents();
        
        // agree
        $('.leyka-tpl-star-form .donor__oferta').on('change.leyka', 'input:checkbox', function(){
            var $donorOferta = $(this).closest('.donor__oferta');
            
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
            var $form = $(this).parents('.leyka-tpl-star-form');
            $form.addClass('leyka-pf--oferta-open');
            $form.find('.leyka-pf__agreement').css('top', getAgreeModalTop($form));
            $([document.documentElement, document.body]).animate({
                scrollTop: $form.offset().top - 64
            });
        });

        $('.leyka-tpl-star-form .leyka-pf__agreement.oferta .agreement__close').on('click.leyka', function(e){
            e.preventDefault();
            $(this).parents('.leyka-tpl-star-form').removeClass('leyka-pf--oferta-open');
        });
    }

    function bindPdEvents() {

        $('.leyka-tpl-star-form .leyka-js-pd-trigger').on('click.leyka', function(e){
            e.preventDefault();
            var $form = $(this).parents('.leyka-tpl-star-form');
            $form.addClass('leyka-pf--pd-open');
            $form.find('.leyka-pf__agreement').css('top', getAgreeModalTop($form));
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
        var modalTop = -32;
        return modalTop + 'px';
    }
    
    function bindSubmitPaymentFormEvent() {

        $('.leyka-tpl-star-form').on('submit.leyka', 'form', function(e){

            var $_form = $(this);

			e.preventDefault();

            if(leykaValidateForm($_form)) { // Form is valid

				var $pm_selected = $_form.find('input[name="leyka_payment_method"]:checked');
                
                if($pm_selected.data('processing') !== 'default') {

					if($pm_selected.data('processing') !== 'custom-process-submit-event') {
						e.stopPropagation();
					}
                    return;

                }

                // Open "waiting" form section:
                var $redirect_section = $_form.closest('.leyka-pf').find('.leyka-pf__redirect'),
                    data_array = $_form.serializeArray(),
                    data = {action: 'leyka_ajax_get_gateway_redirect_data'};

                for(var i=0; i<data_array.length; i++) {
                    data[data_array[i].name] = data_array[i].value;
                }

                if($pm_selected.data('ajax-without-form-submission')) {
                	data['without_form_submission'] = true;
				}

                $redirect_section.addClass('leyka-pf__redirect--open');

                // Get gateway redirection form and submit it manually:
                $.post(leyka_get_ajax_url(), data).done(function(response){

                    response = $.parseJSON(response);

					// Wrong answer from ajax handler:
                    if( !response || typeof response.status === 'undefined' ) {
                        return false;

                    } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                        return false;

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
                        window.location.href = $redirect_section.find('.leyka-auto-submit').prop('action');
                    }

                });

            } else { // Errors exist

                e.preventDefault();
                e.stopPropagation();

            }

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
            
            $(this).find('input.payment-opt__radio').change(function(){
                toggleStaticPMForm($_form);
            });
        });
    }
    
    function toggleStaticPMForm($_form) {
        var $pmRadio = $_form.find('.payment-opt.swiper-item.selected input.payment-opt__radio');
        
        if($pmRadio.data('processing') == 'static') {
            $_form.find('.section--static.' + $pmRadio.val()).show();
            $_form.find('.section--person').hide();
        }
        else {
            $_form.find('.section--static').hide();
            $_form.find('.section--person').show();
        }
    }

	init();

}( jQuery ));
