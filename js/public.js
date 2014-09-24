jQuery(document).ready(function($){

    $(document).on('submit', 'form.leyka-pm-form', function(e){
        var $form = $(this),
            $is_valid = true;

        var $amount_is_flex = $form.find('#donate_amount_flex').length > 0,
            $amount_field = $amount_is_flex ?
                $form.find('input[name="leyka_donation_amount"]') :
                $form.find('input[name="leyka_donation_amount"]:checked');

        if( !$amount_field.val() || parseInt($amount_field.val()) <= 0 || isNaN($amount_field.val()) ) {
            $is_valid = false;
            $form.find('#leyka_donation_amount-error').html(leyka.correct_donation_amount_required).show();
        } else {
            $form.find('#leyka_donation_amount-error').html('').hide();
            if($amount_is_flex)
                $amount_field.val( parseInt($amount_field.val()) );
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

        if($is_valid && $amount_field.val() > $top_amount) {

            $is_valid = false;
            $form.find('#leyka_donation_amount-error').html(
                leyka.donation_amount_too_great.replace('%s', $top_amount+' '+$currency_label)
            ).show();

        } else if($is_valid && $amount_field.val() < $bottom_amount) {
            
            $is_valid = false;
            $form.find('#leyka_donation_amount-error').html(
                leyka.donation_amount_too_small.replace('%s', $bottom_amount+' '+$currency_label)
            ).show();

        } else if($is_valid)
            $form.find('#leyka_donation_amount-error').html('').hide();

        $('.required', this).each(function(){
            var $field = $(this);

            if($field.attr('type') == 'checkbox') {

                if( !$field.attr('checked') ) {
                    $is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.checkbox_check_required).show();
                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text' && $field.hasClass('email')) {

                if( !$field.val().length ) {
                    $is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.email_required).show();
                } else {
//                    var regexp = new RegExp(leyka.email_regexp, 'i');
//                    console.log(regexp.test($field.val()));

                    if( !is_email($field.val()) ) {
                        $is_valid = false;
                        $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid).show();
                    } else {
                        $form.find('#'+$field.attr('id')+'-error').html('').hide();
                    }
                }

            } else if($field.attr('type') == 'text' && $field.attr('name') == 'leyka_donation_amount') {

                if( !$field.val().length ) {
                    $is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.text_required).show();
                } else if(parseInt($field.val()) <= 0 || isNaN($field.val())) {
                    $is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.amount_incorrect).show();
                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }

            } else if($field.attr('type') == 'text') {

                if( !$field.val().length ) {
                    $is_valid = false;
                    $form.find('#'+$field.attr('id')+'-error').html(leyka.text_required).show();
                } else {
                    $form.find('#'+$field.attr('id')+'-error').html('').hide();
                }

            }
        });

        if( !$is_valid )
            e.preventDefault();
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
                    //loaders
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

        if( !curr )
            curr = $('.leyka_donation_currency').val();

        var form_data = {
            action: 'leyka_payment_method',
            pm_id: $(e.target).attr('data-pm_id'),
            currency: curr,
            _leyka_ajax_nonce: $('#_wpnonce').val(),
            user_name: $form.find('#leyka_donor_name').val(),
            user_email: $form.find('#leyka_donor_email').val()
        };
        var lang = $form.find('input[name="cur_lang"]').val();
        if(lang)
            form_data.lang = lang;

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
});

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}