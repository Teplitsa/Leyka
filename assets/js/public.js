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

window.LeykaGUIBottom = function($) {
    this.$ = $;
}

window.LeykaGUIBottom.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-open-form-bottom').on('click', function(e){
            e.preventDefault();

            $(this).closest('.leyka-pf-bottom').leykaForm('openFromBottom');
        });
        
    }

}

jQuery(document).ready(function($){
    
    leykaGUIBottom = new LeykaGUIBottom($);
    leykaGUIBottom.bindEvents();
    
}); //jQuery

window.LeykaGUICard = function($) {
    this.$ = $;
};

window.LeykaGUICard.prototype = {

    bindEvents: function() {
        var self = this; var $ = self.$;
    }

};

jQuery(document).ready(function($){

    leykaGUICard = new LeykaGUICard($);
    leykaGUICard.bindEvents();

}); //jQuery
(function($){

    var amountMin = 1, //temp - take it from options
        amountMax = 30000,
        amountIconMarks = [25, 50, 75],
        inputRangeWidth = 200,
        inputRangeButtonRadius = 14;

    var methods = {
        'defaults': {
            'color': 'green'
        },
        'open': open,
        'close': close,
        'openFromBottom': openFromBottom,
        'redirectForm': redirectForm,
        'init': init
    };

    function init(options) {
        initAmountSync();
        bindEvents();
    }

    /* amount sync */
    function initAmountSync() {
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

    function syncFigure() {
        var val = $(this).val();
        $(this).parents('.step__fields').find('.amount__figure').find('input.leyka_donation_amount').val(val);
        $(this).parents('.step__fields').removeClass('invalid');
    }

    function syncRange() {

        var $this = $(this),
            val = $this.val(),
            $form = $this.parents('.leyka-pf__form');

        $form.removeClass('invalid').find('.amount_range').find('input').val(val).change();

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

        var amountIconIndex = 1;
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
        var percent = getAmountPercent($(this));
        // console.log('Percents:', percent)
        var leftOffset = (inputRangeWidth - 2 * inputRangeButtonRadius) * percent / 100;
        $('.range-circle').css({'left': (leftOffset) + 'px'});
        $('.range-color-wrapper').width(leftOffset + inputRangeButtonRadius);
    }

    /* event handlers */
    function bindEvents() {

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

    /* open/close form */
    function open() {
        $(this).addClass('leyka-pf--active');
        $('.amount_range input').change(); // sync coins pic
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

        if($pf.hasClass('leyka-pf--oferta-open')){ //close only oferta
            $pf.removeClass('leyka-pf--oferta-open');

        }
        else { //close module
            $pf.removeClass('leyka-pf--active');

        }
    }

    function redirectForm() {

        var $form = $(this);
        console.log($form);

    }

    $.fn.leykaForm = function(methodOrOptions) {
        if ( methods[methodOrOptions] ) {
            return methods[ methodOrOptions ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist on jQuery.leykaForm' );
        }    
    }
    
}( jQuery ));

window.LeykaPageMain = function($) {
    this.$ = $;
    this.setupNoScroll();
    this.initForms();
}

window.LeykaPageMain.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
   
        $('.leyka-js-open-form').on('click', function(e){
            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('open');
        });
   
        $('.leyka-js-close-form').on('click', function(e){
            e.preventDefault();
   
            $(this).closest('.leyka-pf').leykaForm('close');
        });
    },

    setupNoScroll: function() {
        var self = this; var $ = self.$;
        
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
    },
    
    initForms: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-pf').leykaForm();
    }
}

jQuery(document).ready(function($){

    leykaPageMain = new LeykaPageMain($);
    leykaPageMain.bindEvents();
    
}); //jQuery


function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}

//polyfill for unsupported Number.isInteger
//https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger
Number.isInteger = Number.isInteger || function(value) {
    return typeof value === "number" &&
           isFinite(value) &&
           Math.floor(value) === value;
};
