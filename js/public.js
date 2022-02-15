jQuery(document).ready(function($){

    // Auto-select the code to embed:
    $('.embed-code').on('focus.leyka keyup.leyka', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;

        if( !keycode || keycode == 9 ) { // Click or tab

            var $this = $(this);
            $this.select();

            $this.on('mouseup', function() { // Work around Chrome's little problem

                $this.off('mouseup');
                return false;

            });
        }
    });

    $('.read-only').on('keydown.leyka', function(e){ // Keep the iframe code from manual changing

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

    $('#embed_iframe_w, #embed_iframe_h').keydown(function(e){

        if(e.keyCode == 13) { // Enter pressed - do not let the form be submitted

            e.preventDefault();
            $(this).change();
            return;

        }

        if( // Allowed special keys
            $.inArray(e.keyCode, [46, 8, 9]) != -1 || // Backspace, delete, tab
            (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
            (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        ) {
            return; // Let it happen
        }

        if( !leyka_is_digit_key(e) ){
            e.preventDefault();
        }

    }).change(function(e){

        var $this = $(this),
            $embed_code = $('#campaign-embed-code'),
            $text = $($embed_code.text());

        $text.attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());
        $('.leyka-embed-preview iframe').attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());

        $embed_code.html($text.prop('outerHTML'));

    });

    function leyka_value_length_count(e){

        var $this = $(this),
            $wrapper = $(this).parents('.leyka-field:first'),
            length = $this.val().length,
            max_length = $this.data('max-length');

        if(typeof max_length == 'undefined' || max_length == 0) {
            return;
        }

        if(length >= max_length) {

            $wrapper.find('.donation-comment-current-length').html(length);
            if( // Allowed special keys
                $.inArray(e.keyCode, [46, 8, 9]) != -1 || // Backspace, delete, tab
                (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
                (e.keyCode == 88 && e.ctrlKey) || // Ctrl+X
                (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
                (e.keyCode == 86 && e.ctrlKey) || // Ctrl+V
                (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
            ) {
                $wrapper.find('.donation-comment-current-length').html(length);
            } else {
                e.preventDefault();
            }

        } else if(length < max_length) {
            $wrapper.find('.donation-comment-current-length').html(length);
        } else {
            e.preventDefault();
        }

    } //  input.leyka
    $(':input.leyka-donor-comment[data-max-length]').on('keydown.leyka keyup.leyka', leyka_value_length_count);

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
            $field = $form.find('.leyka_donation_currency:visible').find('option:selected');

        if($field.length) {
            currency = $field.val();
        } else {
            currency = $form.find('.leyka_donation_currency', '.currency:visible').val();
        }

        return currency;
    }

    // Toggles template behavior:
    $('.toggle.toggled').find('.leyka-toggle-area').css({display: 'block'});

    $('.leyka-toggle-trigger').on('click.leyka', function(){

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
    $('form.leyka-pm-form').on('click.leyka', 'input[name="leyka_donation_amount"]', function(){

        var $field = $(this),
            $form = $field.parents('form.leyka-pm-form');

        if($field.attr('type') == 'radio') {
            $form.find('input.donate_amount_flex:visible').val($field.val());
        } else if($field.hasClass('donate_amount_flex')) {
            $form.find('input[name="leyka_donation_amount"]:checked:visible').removeAttr('checked');
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
            pm_full_id = $(e.target).val(),
            amount_field_type = $form.find('input.leyka_amount_field_type').val();

        if(sum) {
            $form.data('amount-last-chosen', sum);
        } else {
            sum = $form.data('amount-last-chosen');
        }

        $form.find('.leyka-pm-selector .active').removeClass('active');
        $(e.target).parents('li:first').addClass('active');

        $form.find('.field-error').html('').hide(); // Hide the errors of current form before changing to a new one

        var $amount_field_new = $form.find('.pm-amount-field.'+pm_full_id),
            $amount_field_old = $form.find('.pm-amount-field:visible');

        // Form serialization includes all "leyka_donation_amount" fields, whether they are visible/disabled or not:
        $amount_field_old.hide();
        $amount_field_new.show();

        // Selected amount & currency synchronization:
        if(sum) {

            $amount_field_new.find('.'+curr+'.amount-variants-container')
                .find('input[name="leyka_donation_amount"][value="'+sum+'"]:radio')
                .attr('checked', 'checked');
            $amount_field_new.find('input.donate_amount_flex').val(sum);

        }

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

        $amount_field_new.find('.'+curr+'.amount-variants-container').find(':input:disabled').removeAttr('disabled');
        $amount_field_new.find('.currency').find(':input:disabled').removeAttr('disabled');
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
    $('select.leyka_donation_currency', '.amount-selector').on('change.leyka', function(e){

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
        $form.find('.amount-variants-container').hide().find('input').attr('disabled', 'disabled');
        $form.find('.amount-variants-container.'+currency).show().find('input').removeAttr('disabled');
    });

    function leyka_validate_required_field($form, $field) {

        var field_is_valid = true;

        if($field.attr('type') === 'checkbox') {

            var $required_checkbox_fields = $form.find('input[type="checkbox"].required:visible:not(:checked)');
            if( !$field.prop('checked') || $required_checkbox_fields.length ) {

                field_is_valid = false;
                $form.find('.'+$field.attr('name')+'-error').html(leyka.checkbox_check_required_msg).show();

            } else if( !$required_checkbox_fields.length ) {
                $form.find('.'+$field.attr('name')+'-error').html('').hide();
            }

        } else if($field.attr('type') === 'text' && $field.attr('name') !== 'leyka_donation_amount') {

            if( !$field.val().length ) {

                field_is_valid = false;
                $form.find('.'+$field.attr('name')+'-error').html(leyka.text_required_msg).show();

            } else {
                $form.find('.'+$field.attr('name')+'-error').html('').hide();
            }
        }

        return field_is_valid;

    }

    function leyka_validate_donation_form($form) {

        var is_valid = true;

        /** @var leyka object Localization strings */

        var pm_full_id = leyka_get_pm_full_id($form),
            amount_field_type = $form.find('input.leyka_amount_field_type').val(),
            $amount_flex_field = $form.find('input.donate_amount_flex:visible'),
            $amount_fixed_field = $form.find('input[name="leyka_donation_amount"]:checked:visible'),
            $amount_field = amount_field_type == 'flexible' ?
                $amount_flex_field : (
                amount_field_type == 'fixed' ?
                    $amount_fixed_field : ($amount_fixed_field.length ? $amount_fixed_field : $amount_flex_field)
            ),
            $error;

        $(':input:visible', $form).each(function(){

            var $field = $(this),
                field_is_valid = true;

            // Validate field requireness:
            if($field.hasClass('required') && !leyka_validate_required_field($form, $field)) {
                field_is_valid = false;
            }

            if(field_is_valid) { // Validate the other field requirements

                if($field.prop('type') === 'text' && $field.hasClass('email')) {

                    if( !$field.val().length ) {

                        field_is_valid = false;
                        $form.find('.'+$field.attr('name')+'-error').html(leyka.email_required_msg).show();

                    } else {

                        var value_tmp = $.trim($field.val());

                        if( !is_email(value_tmp) ) {

                            field_is_valid = false;
                            $form.find('.'+$field.attr('name')+'-error').html(leyka.email_invalid_msg).show();

                        } else {
                            $form.find('.'+$field.attr('name')+'-error').html('').hide();
                        }

                    }

                } else if($field.attr('type') === 'text' && $field.hasClass('non-email')) {

                    if( !$field.val().length ) {

                        field_is_valid = false;
                        $form.find('.'+$field.attr('name')+'-error').html(leyka.text_required_msg).show();

                    } else if(is_email($field.val())) {

                        field_is_valid = false;
                        $form.find('.'+$field.attr('name')+'-error').html(leyka.must_not_be_email_msg).show();

                    } else {
                        $form.find('.'+$field.attr('name')+'-error').html('').hide();
                    }

                } else if($field.data('max-length')) {

                    if($field.val().length > $field.data('max-length')) {

                        field_is_valid = false;
                        $form.find('.'+$field.attr('name')+'-error').html(leyka.value_too_long_msg).show();

                    } else {
                        $form.find('.'+$field.attr('name')+'-error').html('').hide();
                    }

                }
            }

            if( !field_is_valid ) {
                is_valid = false;
            }

        });

        var amount_is_valid = true;
        $error = $form.find('.leyka_donation_amount-error', '.leyka-pm-fields.'+pm_full_id);
        if( !$amount_field.val() || parseInt($amount_field.val()) <= 0 || isNaN($amount_field.val()) ) {

            amount_is_valid = is_valid = false;
            $error.html(leyka.correct_donation_amount_required_msg).show();

        } else {
            $error.html('').hide();
        }

        var currency = '',
            currency_label = '';

        if($('.leyka_donation_currency option').length) {

            currency = $form.find('.leyka_donation_currency option:selected').val();
            currency_label = $form.find('.leyka_donation_currency option:selected').data('currency-label');

        } else {

            currency = $form.find('.leyka_donation_currency').val();
            currency_label = $form.find('.leyka_donation_currency').data('currency-label');
        }

        var top_amount = parseInt($form.find('input[name="top_'+currency+'"]').val()),
            bottom_amount = parseInt($form.find('input[name="bottom_'+currency+'"]').val());

        if(amount_is_valid && $amount_field.val() > top_amount) {

            is_valid = false;
            $error.html(leyka.donation_amount_too_great_msg.replace('%s', top_amount+' '+currency_label)).show();

        } else if(amount_is_valid && $amount_field.val() < bottom_amount) {

            is_valid = false;
            $error.html(leyka.donation_amount_too_small_msg.replace('%s', bottom_amount+' '+currency_label)).show();

        } else if(amount_is_valid) {
            $error.html('').hide();
        }

        $form.trigger('validate-custom-form-fields.leyka', [$form]);
        if($form.data('custom-fields-are-invalid')) {

            is_valid = false;
            $form.removeData('custom-fields-are-invalid');
        }

        return is_valid;

    }

    $(document).on('submit.leyka', 'form.leyka-pm-form', function(e){

        var $form = $(this);

        if( !$form.hasClass('leyka-no-validation') && !leyka_validate_donation_form($form) ) {

            e.preventDefault();
            e.stopImmediatePropagation();

        } else {

            $(':input:visible', $form).each(function() {

                var $field = $(this);

                if($field.prop('type') === 'text' || $field.prop('type') === 'email') {
                    $field.val( $.trim($field.val()) );
                } else if($field.prop('tagName') === 'textarea') {
                    $field.text( $.trim($field.text()) );
                }

            });

            if(typeof ga === 'function') { // GA Events on form submit

                var label = 'undefined_payment_method',
                    action = 'undefined_campaign';

                if($form.find('[name="leyka_ga_payment_method"]').length) {
                    label = $form.find('[name="leyka_ga_payment_method"]').prop('value');
                }

                if($form.find('[name="leyka_ga_campaign_title"]').length) {
                    action = $form.find('[name="leyka_ga_campaign_title"]').prop('value');
                }

                ga('send', 'event', 'click_donation_button', action, label, 1);

            }

        }

    });

    // Terms of Agreement modal:
    $('.leyka-oferta-text').easyModal({
        top: 100,
        autoOpen: false,
        closeButtonClass: '.leyka-modal-close',
        onOpen: function() {
            $('html').addClass('leyka-js--open-modal');
        },
        onClose: function() {
            $('html').removeClass('leyka-js--open-modal');
        }
    });

    $('.leyka-legal-terms-trigger').on('click.leyka', function(e){

        e.preventDefault();
        $( $(this).data('terms-content') ).trigger('openModal');

    });

    // Donors list width detection:
    function leyka_widths() {
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

    leyka_widths();
    $(window).resize(function(){
        leyka_widths();
    });

    // Scroll:
    $('a.leyka-scroll').on('click.leyka', function(e){

        if( !$(this).parents('.leyka-campaign-card').length ) {

            e.preventDefault();
            var target_top = parseInt($("#leyka-payment-form").offset().top) - 50;

            $('html, body').animate({scrollTop:target_top}, 500);
        }
    });
});

/** Change "&lt;" to "<", "&rt;" to ">", etc. */
function leyka_decode_htmlentities(encoded_text) {

    var textArea = document.createElement('textarea');
    textArea.innerHTML = encoded_text;

    return textArea.value;
}

/** Get currenly selected PM's full ID */
function leyka_get_pm_full_id($form) {

    var template = leyka_get_template_id($form),
        pm_full_id = '';

    switch(template) {
        case 'radio': pm_full_id = $form.find('input[name="leyka_payment_method"]:checked').val(); break;
        case 'toggles': pm_full_id = $form.find('input[name="leyka_payment_method"]').val(); break;
        default:
    }

    return pm_full_id;
}

/** * @return mixed Form template id (if found) or false (if not found). */
function leyka_get_template_id($form) {

	var $tmp = $form.closest('#leyka-payment-form'),
		template_id = false;

	if($tmp.length) {
		template_id = $tmp.data('template');
	} else {

		$tmp = $form.closest('.leyka-pf');

		if($tmp.length) {
			template_id = $tmp.find('.leyka-inline-campaign-form').data('template');
		}

	}

	return template_id;
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

/** @var e JS keyup/keydown event */
function leyka_is_digit_key(e, numpad_allowed) {

    if(typeof numpad_allowed == 'undefined') {
        numpad_allowed = true;
    } else {
        numpad_allowed = !!numpad_allowed;
    }

	// Allowed special keys:
    if( // Backspace, delete, tab, enter:
		e.keyCode === 46 || e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 13 ||
		(e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
		(e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
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

function is_email(email) {
    return /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|expert|[a-z]+)|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i.test(leyka_translit(email));
}