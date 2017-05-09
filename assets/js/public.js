/* Scripts */
jQuery(document).ready(function($){

	var scrollPos = $(window).scrollTop();

	$('.leyka-js-open-form').on('click', function(e){
		e.preventDefault();

		$(this).parents('.leyka-pf').addClass('leyka-pf--active');

	});

	$('.leyka-js-close-form').on('click', function(e){
		e.preventDefault();

		$(this).parents('.leyka-pf').removeClass('leyka-pf--active');
	});


	//no scroll when form is open
	var position = $(window).scrollTop();
	$(window).scroll(function(){

		var scroll = $(window).scrollTop();

		if($('.leyka-pf').hasClass('leyka-pf--active')){
			$(window).scrollTop(position);
		}
		else {
			position = scroll;
		}
	});

	/** amount step **/
	//init sync
	$('.amount__figure input').each(function(){
		var val = parseInt($(this).val());

		if(val < 1 || val > 30000){ //correct this
			val = 500;
		}

		$(this).val(val);
		$(this).parents('.step__fields').find('.amount_range').find('input').val(val);
	});


	//sync of amount field
	$('.amount_range input').on('input change', function(){
		var val = $(this).val();
		//console.log(val);
		$(this).parents('.step__fields').find('.amount__figure').find('input').val(val);
		$(this).parents('.step__fields').removeClass('invalid');
	});

	$('.amount__figure input').on('input change', function(){
		var val = $(this).val();
		console.log(val);
		$(this).parents('.step__fields').find('.amount_range').find('input').val(val);
		$(this).parents('.step__fields').removeClass('invalid');
	});


	//select amount
	$('.leyka-js-amount').on('click', function(e){

		e.preventDefault();

		var $_link = $(this),
			target = $_link.attr('href'),
			$_step = $_link.parents('.step'),
			$_form = $_link.parents('.leyka-pf__form'),
			amount = parseInt($_step.find('.amount__figure input').val());

			console.log(amount);
			if(amount < 1 || amount > 30000) { //correct this
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
					$_form.find('.remembered-payment').parents('leyka-js-another-step').attr('href', 'amount');
					$_form.find('.payment-opt__radio[value="bcard"]').attr('checked', 'checked');
				}
				else {
					$_step.find('input[name="monthly"]').val(0);
					$_form.find('.remembered-amount').text(amount);
					$_form.find('.remembered-monthly').hide();
					$_form.find('.remembered-payment').parents('leyka-js-another-step').attr('href', 'cards');

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

			$_link.parents('.step').removeClass('step--active');
			$_form.find('.step--'+target).addClass('step--active');
	});
}); //jQuery
