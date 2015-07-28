jQuery(document).ready(function($){

    // Auto-select the code to embed:
    $('.embed-code').on('focus keyup', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;

        if(keycode == 9 || !keycode) { // Tab or click

            var $this = $(this);
            $this.select();

            // Work around Chrome's little problem:
            $this.on('mouseup', function() {
                $this.off('mouseup');
                return false;
            });
        }
    });

    var $embed_code = $('#campaign-embed-code');

    $embed_code.keydown(function(e) { // Keep the iframe code from manual changing

        if( // Allowed special keys
            e.keyCode == 9 || // Tab
                (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
                (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
                (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
            ) {
            return; // Let it happen
        }

        e.preventDefault();
    });

    $('#embed_iframe_w, #embed_iframe_h').keydown(function(e) {

        if(e.keyCode == 13) { // Enter pressed - do not let the form be submitted

            e.preventDefault();
            return;
        }

        if( // Allowed special keys
            $.inArray(e.keyCode, [46, 8, 9]) != -1 || // Backspace, delete, tab
                (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
                (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
                (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
            ) {
            return; // Let it happen
        }

        if((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

    }).change(function(e){

        var $this = $(this),
            $text = $($embed_code.text());

        $text.attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());
        $('.leyka-embed-preview iframe').attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());

        $embed_code.html($text.prop('outerHTML'));
    });

    function validate_donation_form($form) {

        var is_valid = true;

        /** @var leyka object Localization strings */

        var $amount_is_flex = $form.find('#donate_amount_flex').length > 0,
            $amount_field = $amount_is_flex ?
                $form.find('input[name="leyka_donation_amount"]') :
                $form.find('input[name="leyka_donation_amount"]:checked');

        if( !$amount_field.val() || parseInt($amount_field.val()) <= 0 || isNaN($amount_field.val()) ) {

            is_valid = false;
            $form.find('#leyka_donation_amount-error').html(leyka.correct_donation_amount_required).show();

        } else {

            $form.find('#leyka_donation_amount-error').html('').hide();
            if($amount_is_flex) {
                $amount_field.val(parseInt($amount_field.val()));
            }
        }

        var $currency = '',
            $currency_label = '';

        if($('.leyka_donation_currency option').length) {

            $currency = $form.find('.leyka_donation_currency option:selected').val();
            $currency_label = $form.find('.leyka_donation_currency option:selected').data('currency-label');

        } else {

            $currency = $form.find('.leyka_donation_currency').val();
            $currency_label = $form.find('.leyka_donation_currency').data('currency-label');

        }

        var $top_amount = parseInt($form.find('input[name="top_'+$currency+'"]').val()),
            $bottom_amount = parseInt($form.find('input[name="bottom_'+$currency+'"]').val());

        if(is_valid && $amount_field.val() > $top_amount) {

            is_valid = false;
            $form.find('#leyka_donation_amount-error').html(
                leyka.donation_amount_too_great.replace('%s', $top_amount+' '+$currency_label)
            ).show();

        } else if(is_valid && $amount_field.val() < $bottom_amount) {

            is_valid = false;
            $form.find('#leyka_donation_amount-error').html(
                leyka.donation_amount_too_small.replace('%s', $bottom_amount+' '+$currency_label)
            ).show();

        } else if(is_valid) {
            $form.find('#leyka_donation_amount-error').html('').hide();
        }

        $('.required', $form).each(function(){

            var $field = $(this);

            if($field.attr('type') == 'checkbox') {

                if( !$field.prop('checked') ) {

                    is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.checkbox_check_required).show();

                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text' && $field.hasClass('email')) {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.email_required).show();

                } else {

                    if( !is_email($field.val()) ) {

                        is_valid = false;
                        $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid).show();

                    } else {
                        $form.find('#'+$field.attr('id')+'-error').html('').hide();
                    }
                }

            } else if($field.attr('type') == 'text' && $field.attr('name') == 'leyka_donation_amount') {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.text_required).show();

                } else if(parseInt($field.val()) <= 0 || isNaN($field.val())) {

                    is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.amount_incorrect).show();

                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text') {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.text_required).show();

                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }
            }
        });

        return is_valid;
    }

    $(document).on('submit', 'form.leyka-pm-form', function(e){

        var $form = $(this);

        if( !validate_donation_form($form) ) {

            e.preventDefault();
            e.stopImmediatePropagation();

        } else {

			if(typeof ga == 'function') { //GA Events on form submit
				
				var label = 'undefined_payment_method',
					action = 'undefined_campaign';

				if($form.find('[name="leyka_ga_payment_method"]').length) {
					label = $form.find('[name="leyka_ga_payment_method"]').attr('value');
				}

				if($form.find('[name="leyka_ga_campaign_title"]').length) {
					action = $form.find('[name="leyka_ga_campaign_title"]').attr('value');
				}

				ga('send', 'event', 'click_donation_button', action, label, 1);				
			}
		}
    });

	/* toggles */
	$('.toggle.toggled').find('.toggle-area').css({display: 'block'});

    $('.toggle-trigger').on('click', function(e){
        
        var $this = $(this);

        if($this.hasClass('toggle-inactive'))
            return;

        var toggleCont = $(this).parents('.toggle');

		if(toggleCont.hasClass('toggled')) {
			toggleCont.removeClass('toggled');
			toggleCont.find('.toggle-area').slideUp('normal', function(){
                toggleCont.find('.leyka-pm-form .field-error').hide();
            });
		} else {
            $this.parents('#leyka-payment-form').find('.leyka-payment-option.toggled .toggle-trigger').click();
			toggleCont.addClass('toggled');
			toggleCont.find('.toggle-area').slideDown('normal');
		}

    });

    /** Switches of curr: */
    var template = $('input[name="leyka_template_id"]').val();
	$('.amount-selector').on('change', 'select.leyka_donation_currency', function(e){
	
		var curr = $(this).find('option:selected').val(),
			curr_pm = $('#pm-selector').find('input:checked').val();

        $('.amount-variants-container:visible').hide();
        $('.amount-variants-container.'+curr).show();

        if(template == 'radios') {

            $.ajax({
                type: 'post',
                url: leyka.ajaxurl,
                data: {
                    action: 'leyka_currency_choice',
                    currency: curr,
                    current_pm: curr_pm,
                    campaign: $('input[name="leyka_campaign_id"]').val(),
                    _leyka_ajax_nonce: $('#_wpnonce').val()
                },
                beforeSend: function(xhr){
                    // loaders
                }
            }).done(function(response){

                $('#leyka-currency-data').html(response);
                $('#pm-selector').on('change', 'input', function(e){
                    leyka_pm_data(e, this);
                });
            });
        }
	});
	
    /* Switches of PM for Radios template */
	$('#pm-selector').on('change', 'input', function(e){
		leyka_pm_data(e, this);
	});
	
	
	function leyka_pm_data(e, field){

        var $form = $(field).parents('form:first'),
            curr = $('option:selected', '.leyka_donation_currency').val();

        if( !curr ) {
            curr = $('.leyka_donation_currency').val();
        }

        var form_data = {
            action: 'leyka_payment_method',
            pm_id: $(e.target).attr('data-pm_id'),
            currency: curr,
            _leyka_ajax_nonce: $('#_wpnonce').val(),
            user_name: $form.find('#leyka_donor_name').val(),
            user_email: $form.find('#leyka_donor_email').val()
        };
        var lang = $form.find('input[name="cur_lang"]').val();
        if(lang) {
            form_data.lang = lang;
        }

		$.ajax({
            type: 'POST',
            url: leyka.ajaxurl,
            data: form_data,
            beforeSend: function(xhr){
                // Loaders:
                $('#leyka-pm-data').addClass('loading');
                $('#pm-selector').find('li').removeClass('active');
                $(e.target).parents('li').addClass('active');
            }
		}).done(function(response){

            response = $.parseJSON(response);
			$('#leyka-pm-data').removeClass('loading').html(response.pm);
            $('.currency').html(response.currency);
		});
	}

	/** Oferta modal **/
	$(document).on('click', '.leyka-legal-confirmation-trigger', function(e){

        e.preventDefault();

        if( !$(this).data('modal-ready') ) {
            $(this)
                .data('modal-ready', 1)
                .leanModal({top: 100, overlay: 0.5, closeButton: '.leyka-modal-close'})
                .click();
        }
    });

    /** Allow modal window closing on Esc */
    $(document).keyup(function(event){
        if(event.keyCode == 27)
            $('#lean_overlay').click();
    });
	
	
	/** Donors list width detection **/
	function leykaWidths() {
		$('.leyka-donors-list').each(function(){
		
			var w = $(this).width();
			if (parseInt(w) > 400) {
				$(this).addClass('wide');
			}
			else {
				$(this).removeClass('wide');
			}
		});
		
		$('.leyka-scale').each(function(){
		
			var $this = $(this),
                w = $(this).width();

			if(parseInt(w) > 500) {
				$this.addClass('wide');
			} else {
				$this.removeClass('wide');
			}
		});
		
		$('.leyka-campaign-card').each(function(){
			var w = $(this).width();

			if (parseInt(w) > 500) {
				$(this).addClass('wide');
			} else {
				$(this).removeClass('wide');
			}
		});
		
		$('.leyka-campaign-list-item.has-thumb').each(function(){
			var w = $(this).width();

			if (parseInt(w) < 280) {
				$(this).addClass('narrow');
			} else {
				$(this).removeClass('narrow');
			}
		});
	}
	
	leykaWidths();
	$(window).resize(function(){
		leykaWidths();
	});
	
	// Scroll:
	$('a.leyka-scroll').on('click', function(e){

        if( !$(this).parents('.leyka-campaign-card').length ) {

            e.preventDefault();
            var target_top = parseInt($("#leyka-payment-form").offset().top) -50;
            //var target_top = target_offset.top;

            $('html, body').animate({scrollTop:target_top}, 500);
        }
	});
	
	
	
});

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}