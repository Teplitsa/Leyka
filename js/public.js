jQuery(document).ready(function($){

    /** @var e JS keyup/keydown event */
    function leyka_is_special_key(e) {

        // Allowed special keys
        return (
            e.keyCode == 9 || // Tab
            (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
            (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
            (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        );
    }

    /** @var e JS keyup/keydown event */
    function leyka_is_digit_key(e, numpad_allowed) {

        if( // Allowed special keys
            $.inArray(e.keyCode, [46, 8, 9, 13]) != -1 || // Backspace, delete, tab, enter
            (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
            (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
            (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        ) {
            return true;
        }

        if(typeof numpad_allowed != 'undefined' && !!numpad_allowed) {
            return !((e.shiftKey || e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105));
        } else {
            return false;
        }
    }

    // Auto-select the code to embed:
    $('.embed-code').on('focus.leyka keyup.leyka', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;

        if( !keycode || keycode == 9 ) { // Click or tab

            var $this = $(this);
            $this.select();

            $this.on('mouseup', function(){ // Work around Chrome's little problem

                $this.off('mouseup');
                return false;
            });
        }
    });

    var $embed_code = $('#campaign-embed-code');

    $embed_code.keydown(function(e){ // Keep the iframe code from manual changing

        if(leyka_is_special_key(e)) {
            e.preventDefault();
        }
    });

    $('#embed_iframe_w, #embed_iframe_h').keydown(function(e){

        if(e.keyCode == 13) { // Enter pressed - do not let the form be submitted

            e.preventDefault();
            return;
        }

        if( !leyka_is_digit_key(e, true) ) {
            e.preventDefault();
        }

    }).change(function(){

        var $this = $(this),
            $text = $($embed_code.text());

        $text.attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());
        $('.leyka-embed-preview iframe').attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());

        $embed_code.html($text.prop('outerHTML'));
    });

    // Get donation amount currently selected on the given form:
    function leyka_get_donation_amount($form) {

        var amount = 0,
            $field = $form.find('input.donate_amount_flex:visible');

        if($field.length && $field.val() > 0) { // Flexible or mixed sum field type
            amount = $field.val();
        } else { // Fixed sum field type
            amount = $form.find('input[name="leyka_donation_amount"]:checked:visible').val();
        }

        return amount;
    }

    // Get donation currency currently selected on the given form:
    function leyka_get_donation_currency($form) {

        var currency = '',
            $field = $form.find('option:selected', '.leyka_donation_currency:visible');

        if($field.length) {
            currency = $field.val();
        } else {
            currency = $form.find('.leyka_donation_currency', '.currency:visible').val();
        }

        return currency;
    }

    // Get currenly selected PM's full ID:
    function leyka_get_pm_full_id($form) {

        var template = $form.parents('#leyka-payment-form').data('template'),
            pm_full_id = '';

        switch(template) {
            case 'radio': pm_full_id = $form.find('input[name="leyka_payment_method"]:checked').val(); break;
            case 'toggles': pm_full_id = $form.find('input[name="leyka_payment_method"]').val(); break;
            default:
        }

        return pm_full_id;
    }

    // Toggles template behavior:
    $('.toggle.toggled').find('.leyka-toggle-area').css({display: 'block'});

    $('.leyka-toggle-trigger').on('click', function(){

        var $this = $(this),
            toggleCont = $this.parents('.toggle');

        if($this.hasClass('toggle-inactive')) {
            return;
        }

        if(toggleCont.hasClass('toggled')) {

            toggleCont.removeClass('toggled');
            toggleCont.find('.leyka-toggle-area').slideUp('normal', function(){
                toggleCont.find('.leyka-pm-form .field-error').hide();
            });

        } else {

            $this.parents('#leyka-payment-form').find('.leyka-payment-option.toggled .leyka-toggle-trigger').click();
            toggleCont.addClass('toggled');
            toggleCont.find('.leyka-toggle-area').slideDown('normal');
        }
    });

    // Mixed sum fields behavior:
    $('form.leyka-pm-form')
        .on('click.leyka', 'input[name="leyka_donation_amount"]', function(){

            var $field = $(this),
                $form = $field.parents('form.leyka-pm-form');

            if($field.attr('type') == 'radio') {
                $form.find('input.donate_amount_flex').val($field.val());
            } else if($field.hasClass('donate_amount_flex')) {
                $form.find('input[name="leyka_donation_amount"]:checked').removeAttr('checked');
            }

        })
        .on('keydown.leyka', 'input.donate_amount_flex', function(e){

            if( !leyka_is_digit_key(e, true) ) {

                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });

    // PM switching on Radios template:
    $('.pm-selector').on('change.leyka', 'input', function(e){

        var $form = $(this).parents('form:first'),
            sum = leyka_get_donation_amount($form),
            curr = leyka_get_donation_currency($form),
            pm_full_id = $(e.target).val();

        if(sum) {
            $form.data('amount-last-chosen', sum);
        } else {
            sum = $form.data('amount-last-chosen');
        }

        $form.find('.leyka-pm-selector .active').removeClass('active');
        $(e.target).parents('li:first').addClass('active');

        $form.find('.field-error').html('').hide(); // Hide the errors of current form before changing to a new one

        var $amount_field_new = $form.find('.amount-selector > .pm-amount-field.'+pm_full_id); // Amount field

        $form.find('.amount-selector > .pm-amount-field:visible').hide();
        $amount_field_new.show();

        // Selected amount & currency synchronization:
        $amount_field_new.find('.'+curr+'.amount-variants-container')
            .find('input[name="leyka_donation_amount"][value="'+sum+'"]:radio')
            .attr('checked', 'checked');
        $amount_field_new.find('input.donate_amount_flex').val(sum);
        $form.find('select.leyka_donation_currency > option[value="'+curr+'"]').attr('selected', 'selected');

        $form.find('.leyka-hidden-fields > .pm-hidden-field:visible').hide();
        $form.find('.leyka-hidden-fields > .pm-hidden-field.'+pm_full_id).show();

        var $pm_fields_old = $form.find('.leyka-pm-fields:visible'),
            $pm_fields_new = $form.find('.leyka-pm-fields.'+pm_full_id),
            fields_vals = {}; // To populate form fields' values between different PMs

        $pm_fields_old.find('.leyka-user-data :input:visible:not(:button,:submit)').each(function(){

            var $this = $(this);
            fields_vals[$this.attr('name')] = $this.val();

        });

        $pm_fields_old.hide();
        $form.find('.leyka-pm-desc:visible').hide();

        // Prevent a submission of an inactive form's fields:
        $form.find('.amount-selector :input').attr('disabled', 'disabled');
        $form.find('.leyka-pm-fields :input').attr('disabled', 'disabled');

        $amount_field_new.find(':input:disabled').removeAttr('disabled');
        $pm_fields_new.find(':input:disabled').removeAttr('disabled');

        // Re-populate a form fields' values from a previous PM:
        $pm_fields_new.find('.leyka-user-data :input:not(:button,:submit)').each(function(){

            var $this = $(this);
            if( fields_vals[$this.attr('name')] ) {
                $this.val( fields_vals[$this.attr('name')] );
            }
        });
        $pm_fields_new.show();
        $form.find('.leyka-pm-desc.'+pm_full_id).show();
    })
    .find('input[name="leyka_payment_method"]:checked').change();

    // Switches of currency:
    $('.amount-selector').on('change', 'select.leyka_donation_currency', function(e){

        e.stopImmediatePropagation();

        var $this = $(this),
            $form = $this.parents('form:first'),
            $pm_variants = $form.find('.pm-selector').find('.leyka-pm-variant'),
            currency = $this.find('option:selected').val();

        $pm_variants.each(function(){ // Toggle PMs in their list

            var $this = $(this); // PM option line
            $this.find('input[data-curr-supported*='+currency+']').length ? $this.show() : $this.hide();
        });

        // Hide the errors of current amoount before changing to a new one:
        $form.find('.leyka_donation_amount-error.field-error').html('').hide();

        // Toggle the donation amount variants for a different currencies:
        $form.find('.amount-variants-container').hide();
        $form.find('.amount-variants-container.'+currency).show();
    });

    function leyka_validate_donation_form($form) {

        var is_valid = true;

        /** @var leyka object Localization strings */

        var pm_full_id = leyka_get_pm_full_id($form),
            amount_field_type = $form.find('.sum-field-type:visible').data('sum-field-type'),
            $amount_flex_field = $form.find('input.donate_amount_flex:visible'),
            $amount_fixed_field = $form.find('input[name="leyka_donation_amount"]:checked:visible'),
            $amount_field = amount_field_type == 'flex' ?
                $amount_flex_field : (
                    amount_field_type == 'fixed' ?
                        $amount_fixed_field : ($amount_fixed_field.length ? $amount_fixed_field : $amount_flex_field)
                ),
            $error;

        $error = $form.find('.leyka_donation_amount-error', '.leyka-pm-fields.'+pm_full_id);
        if( !$amount_field.val() || parseInt($amount_field.val()) <= 0 || isNaN($amount_field.val()) ) {

            is_valid = false;
            $error.html(leyka.correct_donation_amount_required).show();

        } else {
            $error.html('').hide();
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
            $error.html(leyka.donation_amount_too_great.replace('%s', $top_amount+' '+$currency_label)).show();

        } else if(is_valid && $amount_field.val() < $bottom_amount) {

            is_valid = false;
            $error.html(leyka.donation_amount_too_small.replace('%s', $bottom_amount+' '+$currency_label)).show();

        } else if(is_valid) {
            $error.html('').hide();
        }

        $('.required:visible', $form).each(function(){

            var $field = $(this);

            if($field.attr('type') == 'checkbox') {

                if( !$field.prop('checked') ) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.checkbox_check_required).show();

                } else {
                    $form.find('.'+$field.attr('name')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text' && $field.hasClass('email')) {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.email_required).show();

                } else {

                    if( !is_email($field.val()) ) {

                        is_valid = false;
                        $form.find('.'+$field.attr('name')+'-error').html(leyka.email_invalid).show();

                    } else {
                        $form.find('.'+$field.attr('name')+'-error').html('').hide();
                    }
                }

            } else if($field.attr('type') == 'text' && $field.attr('name') == 'leyka_donation_amount') {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.text_required).show();

                } else if(parseInt($field.val()) <= 0 || isNaN($field.val())) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.amount_incorrect).show();

                } else {
                    $form.find('.'+$field.attr('name')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text' && $field.hasClass('non-email')) {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.text_required).show();

                } else if(is_email($field.val())) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.must_not_be_email).show();

                } else {
                    $form.find('.'+$field.attr('name')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text') {

                if( !$field.val().length ) {

                    is_valid = false;
                    $form.find('.'+$field.attr('name')+'-error').html(leyka.text_required).show();

                } else {
                    $form.find('.'+$field.attr('name')+'-error').html('').hide();
                }
            }
        });

        return is_valid;
    }

    $(document).on('submit', 'form.leyka-pm-form', function(e){

        var $form = $(this);

        if( !leyka_validate_donation_form($form) ) {

            e.preventDefault();
            e.stopImmediatePropagation();

        } else {

			if(typeof ga == 'function') { // GA Events on form submit
				
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

	// Terms of Agreement modal:
	$(document).on('click', '.leyka-legal-confirmation-trigger', function(e){

        e.preventDefault();

        if( !$(this).data('modal-ready') ) {
            $(this)
                .data('modal-ready', 1)
                .leanModal({top: 100, overlay: 0.5, closeButton: '.leyka-modal-close'})
                .click();
        }
    });

    // Allow modal window closing on Esc:
    $(document).keyup(function(e){
        if(e.keyCode == 27) {
            $('#lean_overlay').click();
        }
    });

	// Donors list width detection:
	function leykaWidths() {
		$('.leyka-donors-list').each(function(){
		
			var w = $(this).width();
			if (parseInt(w) > 400) {
				$(this).addClass('wide');
			} else {
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

			if(parseInt(w) > 500) {
				$(this).addClass('wide');
			} else {
				$(this).removeClass('wide');
			}
		});
		
		$('.leyka-campaign-list-item.has-thumb').each(function(){

			var w = $(this).width();

			if(parseInt(w) < 280) {
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
            var target_top = parseInt($("#leyka-payment-form").offset().top) - 50;

            $('html, body').animate({scrollTop:target_top}, 500);
        }
	});
});

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}