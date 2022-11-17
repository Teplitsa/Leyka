/*
 * Star form template functionality and handlers
 */

jQuery(document).ready(function($){

    function init() {
        assignSizeClasses();
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

        // Window resize events:
        $(window).on('resize.leyka', function(){
            assignSizeClasses();
            setupSwiperWidth($('.leyka-pf-need-help'));
        }).resize();

    }

    function assignSizeClasses() {

        let $form = $('.leyka-pf-need-help'),
            form_parent_width = Math.ceil($form.parent().width());

        $form.removeClass('leyka-width-small leyka-width-medium leyka-width-large leyka-width-exlarge leyka-width-xxlarge');

        if(form_parent_width <= (320 + 20 - 1)) {
            $form.addClass('leyka-width-small');
        } else if(form_parent_width <= (600 + 20 - 1)) {
            $form.addClass('leyka-width-medium');
        } else if(form_parent_width <= (760 + 20 - 1)) {
            $form.addClass('leyka-width-large');
        } else if(form_parent_width <= (1020 + 20 - 1)) {
            $form.addClass('leyka-width-exlarge');
        } else {
            $form.addClass('leyka-width-xxlarge');
        }

    }
	
	function resize(e, element, k) {

        let val = $.trim(element.value);

        if( !val ) {

            $(element).addClass('empty');
            
            if( !e || e.type === 'blur' ) {
                setAmountPlaceholder(element);
                val = $(element).attr('placeholder');
                $(element).siblings('.currency').hide();
                $(element).addClass('show-ph');
            } else if(e.type === 'focus') {
                $(element).siblings('.currency').show();
                $(element).removeClass('show-ph');
            }

        } else {
            $(element).removeClass('empty');
            $(element).removeClass('show-ph');
        }
        
        setAmountInputValue($(element).closest('.leyka-tpl-star-form'), $(element).val());

	}
    
    function setAmountPlaceholder(element) {
        if(isMobileScreen()) {
            $(element).prop('placeholder', $(element).data('mobile-ph'));
        } else {
            $(element).prop('placeholder', $(element).data('desktop-ph'));
        }
    }
	
	function bindAmountEvents() {
		
		function resizable(el, factor) {

			let k = Number(factor) || 7.7,
                e = 'keyup,keypress,focus,blur,change'.split(',');

			for(let i in e) {
				el.addEventListener(e[i], function(e){resize(e, el, k);}, false);
			}

			resize(null, el, k);

		}

		$('.donate_amount_flex').each(function(i, element) {

            if(parseInt($(this).css('font-size')) <= 16) {
                resizable(element, 7);
            } else {
                resizable(element, 11.1);
            }

            setAmountPlaceholder(element);

		});
        
        $('.leyka-tpl-star-form .amount__figure .swiper-item.selected').each(function(i, element){
            setAmountInputValue($(element).closest('.leyka-tpl-star-form'), getAmountValueFromControl($(element)));
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
            if( !$.trim($(el).val()) ) {
                $(el).parent().addClass('empty');
            }
        });
	}
    
    function getAmountValueFromControl($element) {

        let $predefined_amount = $element.find('span.amount'),
            val = '';

        if($predefined_amount.length > 0) {

            let $daily_rouble_comment = $element.parents('.star-swiper').find('.daily-rouble-comment'),
                $daily_rouble_amount = $daily_rouble_comment.find('.daily-rouble-amount')

            val = $daily_rouble_comment.length ? $daily_rouble_amount.text() : $element.find('span.amount').text();

        } else {
            val = $element.find('input.donate_amount_flex').val();
        }

        return parseFloat(val.replace(/[^0-9]+/g, ''));

    }

    function setAmountInputValue($form, amount) {

        amount = typeof amount === 'string' ? parseFloat(amount.replace(/[^0-9]+/g, '')) : amount;

        let $amount_field = $form.find('input.leyka_donation_amount'),
            previous_value = parseFloat($amount_field.val());

        if(amount !== previous_value) {
            $amount_field.val(amount).trigger('change');
        }

    }

    function showAmountOptionsByPaymentType($form, payment_type) {

        const currency = $('.section__fields.currencies a.active').data('currency'),
            $currency_tab = $(`.currency-tab.currency-${currency}`),
            $swiper_first_item = $currency_tab.find(`.section--amount .swiper-item[data-payment-type="${payment_type}"]`).first();

        $currency_tab.find(`.section--amount .swiper-item`).removeClass('selected');
        $currency_tab.find(`.section--amount .swiper-item:not(.flex-amount-item)`).css('display', 'none');
        $currency_tab.find(`.section--amount .swiper-item[data-payment-type="${payment_type}"]`).css('display', 'block');

        $swiper_first_item.first().addClass('selected');

        setAmountInputValue($form, $swiper_first_item.find('.amount').text());
        showSelectedAmountDescription($form, $swiper_first_item.data('payment-amount-option-id'));

    }

    function showSelectedAmountDescription($form, amount_option_id) {

        $form.find('.section__fields.amount-description').css('display', 'none');

        let $amount_option_description = $form.find(`.section__fields.amount-description span[data-payment-amount-option-id = "${amount_option_id}"]`);

        if(amount_option_id && $amount_option_description && $amount_option_description.text() !== '') {

            $form.find('.section__fields.amount-description').css('display', 'flex');
            $form.find('.section__fields.amount-description span').css('display', 'none');

            $amount_option_description.css('display', 'block');

        }

    }

    function bindModeEvents() {

        $('.leyka-tpl-star-form .section__fields.periodicity').on('click.leyka', 'a', function(e){

			e.preventDefault();

			let $this = $(this),
                $_form = $(this).closest('form.leyka-pm-form');

			$this.closest('.section__fields').find('a').removeClass('active');
			$this.addClass('active');

            setupPeriodicity($_form);
            setupCurrencies($_form);
            setupSwiperWidth($_form);

            const amount_mode = $('.section--amount .section__fields.amount').data('amount-mode');
            const payment_type = $this.data('periodicity') === 'once' ? 'single' : 'recurring';

            if(amount_mode === 'flexible') {
                setAmountInputValue($_form, $('.flex-amount-item input').val());
            } else {
                showAmountOptionsByPaymentType($_form, payment_type);
            }

        });

        $('.leyka-tpl-star-form .section__fields.currencies').on('click.leyka', 'a', function(e){

            e.preventDefault();

            let $this = $(this),
                $_form = $(this).closest('form.leyka-pm-form'),
                currency = $(this).data('currency'),
                $currency_tab = $_form.find(`.currency-tab.currency-${currency}`);

            $this.closest('.section__fields').find('a').removeClass('active');
            $this.addClass('active');

            $_form.find('.currency-tab').addClass('leyka-hidden');
            $currency_tab.removeClass('leyka-hidden').show();
            setupSwiperWidth($currency_tab);

            if(currency !== 'crypto') {
                $_form.find('.section--person').removeClass('leyka-hidden');
            } else {
                $_form.find('.section--person').addClass('leyka-hidden');
            }

            const amount_mode = $('.section--amount .section__fields.amount').data('amount-mode');
            const payment_type = $('.section__fields.periodicity a.active').data('periodicity') === 'once' ?
                'single' : 'recurring';

            if(amount_mode === 'flexible') {
                setAmountInputValue($_form, $('.flex-amount-item input').val());
            } else {
                showAmountOptionsByPaymentType($_form, payment_type);
            }

            $currency_tab.find('.payments-grid .swiper-item.selected .payment-opt__button').click();

        });

        $('.leyka-tpl-star-form .currency-tab .leyka-button-copy').on('click.leyka', function(e){

            const $btn = $(this);

            $btn.css('pointer-events', 'none');

            const $tmp = $("<textarea>");

            $('body').append($tmp);

            $tmp.val($btn.parent('.leyka-cryptocurrency-data-wrapper').find('.leyka-cryptocurrency-link').text())
                .select();

            document.execCommand('copy');

            $tmp.remove();

            const $btn_img = $btn.find('img'),
                $btn_span = $btn.find('span'),
                $wallet_link = $btn.parents('.leyka-cryptocurrency-data-wrapper').find('.leyka-cryptocurrency-link');

            $btn_img.addClass('leyka-hidden');

            $wallet_link.css('opacity', 0.3);

            $btn_span
                .animate({ marginRight: '0'}, 200)
                .delay(1200)
                .animate({ marginRight: '-135px'}, 200, function(){

                    $btn_img.removeClass('leyka-hidden');
                    $btn.css('pointer-events', 'all');
                    $wallet_link.css('opacity', 1);

                });

        });

        $('.leyka-tpl-star-form form.leyka-pm-form').each(function(){

            setupPeriodicity($(this));
            setupCurrencies($(this));
            setupSwiperWidth($(this));

            const $_form = $(this).closest('form.leyka-pm-form');
            const amount_mode = $('.section--amount .section__fields.amount').data('amount-mode');
            const payment_type = $('.section__fields.periodicity a.active').data('periodicity') === 'once' ? 'single' : 'recurring';

            if(amount_mode === 'flexible') {
                setAmountInputValue($_form, $('.flex-amount-item input').val());
            } else {
                showAmountOptionsByPaymentType($_form, payment_type);
            }

        });
    }

    function setupSwiperWidth($_form) {

        // Amount swiper setup:
        $_form.find('.amount__figure.star-swiper .swiper-list .swiper-item').last().css('margin-right', '0px');

        // PM swiper setup:
        let $swiper = $_form.find('.payments-grid .star-swiper'),
            $list = $swiper.find('.swiper-list'), // $list is empty in full-list width mode
            $active_item = $swiper.find('.swiper-item.selected:not(.disabled)').first();

        if( !$active_item.length ) {

            $swiper.find('.swiper-item:not(.disabled)').first().addClass('selected');
            $active_item = $swiper.find('.swiper-item.selected:not(.disabled)').first();
            $active_item.find('input[type=radio]').prop('checked', true).change();

        }

        $list.find('.swiper-item:not(.disabled)').css('margin-right', '16px');
        $list.find('.swiper-item:not(.disabled)').last().css('margin-right', '0px');        
        $list.css('width', '100%');

        // Fixed max width must work both in swiper and full width mode, so use $swiper instead of $list:
        let max_width = $swiper.closest('.leyka-payment-form').width();

        if($swiper.find('.full-list').length) {

            max_width = $_form.width() > 502 ? (max_width - 60) / 2 : max_width - 60;
            $swiper.find('.payment-opt__label').css('max-width', max_width);
            $swiper.find('.payment-opt__icon').css('max-width', max_width);

            if($_form.width() < 220) {

                $swiper.find('.payment-opt').css('flex-basis', $_form.width());
                $_form.find('.donor-field').css('flex-basis', $_form.width());

            }

        } else {

            max_width -= 184;
            $swiper.find('.payment-opt__label').css('max-width', max_width);
            $swiper.find('.payment-opt__icon').css('max-width', max_width);

            $swiper.find('.swiper-item').each(function(i, item){

                $(item).css(
                    'min-width',
                    Math.min(max_width, Math.max(
                        $(item).find('.payment-opt__label').width(),
                        $(item).find('.pm-icon').length * 40) // Max width of PM icon
                    ) + 64
                );

            });

            // Fix for FF and Safari:
            let $active_pm_item = $swiper.find('.swiper-item:not(.disabled)');
            if($active_pm_item.length <= 1) {
                $active_pm_item.css('width', '100%');
            } else {
                $active_pm_item.css('width', 'auto');
            }

        }
        
        toggleSwiperArrows($swiper);
        swipeList($swiper, $active_item);

        setupSinglePMBlock($_form);

    }

    function setupSinglePMBlock($_form) {

        $_form = $_form.hasClass('currency-tab') ? $_form.parents('form.leyka-pm-form') : $_form;

        let $available_pm_blocks = $_form.find('.currency-tab:visible').find('.payments-grid .swiper-item:not(.disabled)'),
            $single_pm_icon_block = $_form.find('.single-pm-icon'),
            $pm_form_section = $_form.find('.currency-tab:visible').find('.section--cards');

        if($available_pm_blocks.length === 1) {

            $single_pm_icon_block.html($available_pm_blocks.find('.payment-opt__icon').html()).show();
            $pm_form_section.hide();

        } else {

            $single_pm_icon_block.hide();
            $pm_form_section.show();

        }

    }

    function setupPeriodicity($_form) {

        let is_recurring = false,
            $active_periodicity_tab = $_form.find('.section__fields.periodicity a.active');

        if($active_periodicity_tab.length) {
            is_recurring = $active_periodicity_tab.data('periodicity') === 'monthly';
        } else {
            is_recurring = parseInt($_form.find('input.is-recurring-chosen').val()) === 1;
        }

        $_form.find('.section__fields.periodicity a').removeClass('active');

        if(is_recurring) {

            $_form.find('.section__fields.periodicity a[data-periodicity="monthly"]').addClass('active');
            $_form.find('input.is-recurring-chosen').val('1').trigger('change');
            $_form.find('.payments-grid .swiper-item').each(function(i, element){

                let $swiper_item = $(element);

                if($swiper_item.find('input[data-has-recurring="0"]').length > 0) {
                    $swiper_item
                        .addClass('disabled')
                        .removeClass('selected')
                        .find('input[type="radio"]')
                            .prop('checked', false);
                }

            });

        } else {

            $_form.find('.section__fields.periodicity a[data-periodicity="once"]').addClass('active');
            $_form.find('input.is-recurring-chosen').val('0').trigger('change');
            $_form.find('.payments-grid .swiper-item').each(function(i, element){

                if($(element).find('input[data-has-recurring="0"]').length > 0) {
                    $(element).removeClass('disabled');
                }

            });

        }

        setupSinglePMBlock($_form);
        checkFormFillCompletion($_form);

    }

    function setupCurrencies($form) {

        const $section_currencies = $form.find('.section.section--currencies'),
            periodicity = $form.find('.section__fields.periodicity a.active').data('periodicity'),
            main_currency = $section_currencies.data('main-currency'),
            currencies_count = parseInt($section_currencies.data('currencies-count'), 10),
            is_crypto_enabled = $section_currencies.data('is-crypto-enabled');

        if(currencies_count > 1 || is_crypto_enabled == 1) {
            $section_currencies.removeClass('leyka-hidden');
        }

        if(periodicity === 'once') {
            $section_currencies.find('a[data-currency="crypto"]').removeClass('leyka-hidden');
        } else {
            $section_currencies.find('a[data-currency="crypto"]').addClass('leyka-hidden');
        }

        $form.find('.section__fields.currencies a').removeClass('active');
        $form.find('.currency-tab').addClass('leyka-hidden');
        $form.find(`.section__fields.currencies a[data-currency="${main_currency}"]`).addClass('active');
        $form.find(`.currency-tab.currency-${main_currency}`).removeClass('leyka-hidden');

    }

    function bindSwiperEvents() {

        let $swiper = $('.leyka-tpl-star-form .star-swiper');

        $swiper.on('click.leyka', '.swiper-item', function(e){

            let $this = $(this),
                $swiper = $this.closest('.star-swiper'),
                $daily_rouble_comment = $this.parents('.star-swiper').find('.daily-rouble-comment'),
                $daily_rouble_amount = $daily_rouble_comment.find('.daily-rouble-amount'),
                $form = $this.closest('.leyka-tpl-star-form');

        	if($this.hasClass('selected')) {
        		return;
        	}

            $this.siblings('.swiper-item.selected').removeClass('selected');
            $this.addClass('selected');
            $this.find('input[type="radio"]').prop('checked', true).change();

            if($daily_rouble_comment.length) {

                let $submit = $swiper.parents('.leyka-pm-form').find('.donor__submit input[type="submit"]'),
                    first_amount_block_offset = $swiper.find('.swiper-item:first-child').offset().left,
                    $selected_amount_block = $swiper.find('.swiper-item.selected'),
                    comment_arrow_offset = $selected_amount_block.offset().left
                        - first_amount_block_offset
                        + $selected_amount_block.outerWidth()/3,
                    monthly_amount_formatted = (30 * parseInt($this.data('value'))).format();

                $daily_rouble_amount.text(monthly_amount_formatted);
                $submit.val( $submit.data('submit-text-template').replace('#DAILY_ROUBLE_AMOUNT#', monthly_amount_formatted) );

                // Move the "daily rouble" comment arrow to the selected amount block:
                let style = document.createElement('style');
                style.innerHTML = '.amount__figure.star-swiper .daily-rouble-comment:before {left: '+comment_arrow_offset+'px !important;}';
                document.head.appendChild(style);

            }

            if($this.parents('.section--amount').length > 0) {
                showSelectedAmountDescription($form, $this.data('payment-amount-option-id'));
            }

            swipeList($swiper, $this);
            toggleSwiperArrows($swiper);

            if($this.hasClass('flex-amount-item')) {
                $this.find('input[type="number"]').focus();
                $this.addClass('focus').removeClass('empty');
            }
            
            if($swiper.hasClass('amount__figure')) {
                setAmountInputValue($form, getAmountValueFromControl($this));
            }

            checkFormFillCompletion($form);

        });

        const currency = $('.section__fields.currencies a.active').data('currency'),
            $currency_tab = $(`.currency-tab.currency-${currency}`),
            payment_type = $('.section__fields.periodicity a.active').data('periodicity') === 'once' ? 'single' : 'recurring',
            $swiper_amount_first_item = $currency_tab
                .find(`.section--amount .swiper-item[data-payment-type="${payment_type}"]`)
                    .first(),
            $swiper_cards_first_item = $currency_tab.find(`.section--cards .swiper-item`).first();

        $swiper_amount_first_item.click();
        $swiper_cards_first_item.click();

        $currency_tab.find('.star-swiper .swiper-item.selected')
            .find('input[type="radio"]')
                .prop('checked', true)
                .change();

        $swiper.on('click.leyka', 'a.swiper-arrow', function(e){

            e.preventDefault();

			let $this = $(this),
                $swiper = $this.closest('.star-swiper'),
                $activeItem = $swiper.find('.swiper-item.selected:not(.disabled)'),
                $nextItem = null;

			if($this.hasClass('swipe-right')) {
				$nextItem = $activeItem.nextAll('.swiper-item:not(.disabled)').first();
			} else {
				$nextItem = $activeItem.prevAll('.swiper-item:not(.disabled)').first();
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

        let $list = $swiper.find('.swiper-list');

        if( !$list.length ) {
            return;
        }

        $list.stop(true, true);

        let dif = $list.width() - $swiper.width();
        if(dif <= 0) {
            $list.width($swiper.width());
            $list.css('left', 0);
            return;
        }

        // var left = parseInt($list.css('left'));
        if($swiper.find('.swiper-item:not(.disabled)').first().hasClass('selected')) {
            left = 0;
        } else if($swiper.find('.swiper-item:not(.disabled)').last().hasClass('selected')) {
            left = -dif;
        } else {
            left = $swiper.width() / 2 - ($activeItem.offset().left - $list.offset().left) - $activeItem.width() / 2;
            left -= parseInt($activeItem.css('margin-right')) * 1.5; //24; // minus margin * 1.5
        }
        
        $list.animate({'left': left});

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
                $currency_tab = $form.find('.currency-tab:not(.leyka-hidden)'),
                $errors = $form.parents('.leyka-payment-form').siblings('.leyka-submit-errors'),
                $pm_selected = $form.find('input[name="leyka_payment_method"]:checked'),
                currency = $('.section__fields.currencies a.active').data('currency');

            $form.find(`.currency-tab:not(.currency-${currency})`).remove();

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

            let $_form = $(this);

            toggleStaticPMForm($_form);
            togglePmSpecialFields($_form);

            $(this).find('input.payment-opt__radio').change(function(){

                if($(this).prop('checked')) {

                    toggleStaticPMForm($_form);
                    togglePmSpecialFields($_form);

                }

            });

        });

        const currency = $('.section__fields.currencies a.active').data('currency'),
            $currency_tab = $(`.currency-tab.currency-${currency}`);

        $currency_tab.find('.payments-grid .swiper-item.selected').each(function(i, el){
            $(this).click();
        });

    }
    
    function toggleStaticPMForm($form) {

        var $pmRadio = $form.find('input[name="leyka_payment_method"]:checked');

        if($pmRadio.data('processing') === 'static') {

            $form.find('.section--static').hide();
            $form.find('.section--person').hide();
            $form.find('.section--static.' + $pmRadio.val()).show();

        } else {

            $form.find('.section--static').hide();
            $form.find('.section--person').show();

        }

    }

    function togglePmSpecialFields($form) {

        let $pm_radio = $form.find('input[name="leyka_payment_method"]:checked');

        $form.find('.special-field').hide();
        $form.find('.special-field.'+$pm_radio.val()).show();

    }

    function isMobileScreen() {
        return $(document).width() < 640;
    }

	init();

});
