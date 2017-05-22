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

			//open waiting
			$_form.parents('.leyka-pf').find('.leyka-pf__redirect').addClass('leyka-pf__redirect--open');

			setTimeout(function() {
				$_form.parents('.leyka-pf').find('.leyka-pf__redirect').removeClass('leyka-pf__redirect--open');
			}, 4500);

			e.preventDefault(); //temp
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
