/* Scripts */
jQuery(document).ready(function($){
    
	var amountMin = 1, //temp - take it from options
		amountMax = 30000; //temp - take it from options

	/** amount step **/

	//select amount
	$('.leyka-js-amount').on('click', function(e){

		e.preventDefault();

		var $_link = $(this),
			target = $_link.attr('href'),
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

	});

	$('.leyka-js-another-step').on('click', function(e){
		e.preventDefault();

		var $_link = $(this),
			target = $_link.attr('href'),
			$_form = $_link.parents('.leyka-pf__form');

			if(target == 'cards') {
				//reset choice for payment
				$_form.find('.payment-opt__radio').prop('checked', false);
			}

			$_link.parents('.step').removeClass('step--active');
			$_form.find('.step--'+target).addClass('step--active');
	});

	/** payment step **/
	$('.payment-opt__radio').change(function(){

		var	$_opt = $(this),
			name = $_opt.parents('.payment-opt').find('.payment-opt__label').text(),
			$_step = $_opt.parents('.step'),
			$_form = $_opt.parents('.leyka-pf__form');

		//remember
		$_form.find('.remembered-payment').text(name);

		//move
		$_step.removeClass('step--active');
		$_form.find('.step--person').addClass('step--active');

	});

	/** person step **/

	//reset fields
	$('.donor__textfield--name').removeClass('invalid').removeClass('valid');
	$('.donor__textfield--email').removeClass('invalid').removeClass('valid');
	$('.donor__oferta').removeClass('invalid').removeClass('valid');


	//inline validation
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

	//agree
	$('.donor__oferta').on('change', 'input', function(){

		if($(this).prop('checked')) {
			$(this).parents('.donor__oferta').removeClass('invalid');
		}
		else {
			$(this).parents('.donor__oferta').addClass('invalid');
		}
	});


	//next
	$('.leyka-pf__form').on('submit', 'form',  function(e){


		var	$_form = $(this),
			pName = $_form.find('.donor__textfield--name input').val(),
			pEmail = $_form.find('.donor__textfield--email input').val(),
			amount = parseInt($_form.find('.amount__figure input').val()),
			agree = $_form.find('.donor__oferta input').val(),
			error = false;

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
			// 	$redirect_step.removeClass('leyka-pf__redirect--open');
             //
			// }, 4500);
		}
		else {
			e.preventDefault(); //temp
		}
	});

	/** oferta **/
	$('.leyka-js-oferta-trigger').on('click', function(e){
		e.preventDefault();

		$(this).parents('.leyka-pf').addClass('leyka-pf--oferta-open');

	});

	$('.leyka-js-oferta-close').on('click', function(e){
		e.preventDefault();

		$(this).parents('.leyka-pf').find('.donor__oferta').removeClass('invalid').find('input').prop('checked', true);
		$(this).parents('.leyka-pf').removeClass('leyka-pf--oferta-open');

	});

	/* history */
	$('.leyka-js-history-close').on('click', function(e){
		e.preventDefault();

		$(this).parents('.leyka-pf--history-open').removeClass('leyka-pf--history-open');
	});

	$('.leyka-js-history-more').on('click', function(e){
		e.preventDefault();
		$(this).parents('.leyka-pf, .leyka-pf-bottom').addClass('leyka-pf--history-open');
	});

	/* resize event */
	function leyka_inpage_card_columns() {

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
	}

	leyka_inpage_card_columns();

	//resize event
	$(window).resize(function(){
		leyka_inpage_card_columns();
	});

}); //jQuery
