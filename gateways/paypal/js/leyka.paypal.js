//jQuery(document).ready(function($){
(function($){

	var $form;

	// If PayPal chosen, show its special submit instead of normal:
	$('.payment-opt__radio').change(function(){

		var $this = $(this);

		$form = $this.closest('.leyka-pf');

		if($this.attr('value').indexOf('paypal') !== -1) {
			$form.find('.leyka-paypal-form-submit').show();
			$form.find('input.leyka-default-submit').hide();
		} else {
			$form.find('.leyka-paypal-form-submit').hide();
			$form.find('input.leyka-default-submit').show();
		}

	});

//	function onChangeCheckbox(handler) {
//		document.querySelector('#check').addEventListener('change', handler);
//	}

//	function toggleValidationMessage() {
//		document.querySelector('#msg').style.display = (isValid() ? 'none' : 'block');
//	}

	function togglePayPalSubmit(event) {

		if( $form.data('paypal-validation-allowed') ) {
			return leykaValidateForm($form) ?
				   event.data.paypalSubmitActions.enable() :
				   event.data.paypalSubmitActions.disable();
		} else {
			return event.data.paypalSubmitActions.disable();
		}

	}

	paypal.Button.render({

		env: 'sandbox', // 'production'
		locale: leyka.paypal_locale,
		style: {
			color: 'blue',
			shape: 'pill', // 'rect',
			size: 'responsive',
			label: 'paypal',
			tagline: false,
			maxbuttons: 1,
			funding: {
				allowed: [paypal.FUNDING.CARD],
				disallowed: [paypal.FUNDING.CREDIT, paypal.FUNDING.ELV]
			}
		},

		client: {
			sandbox: 'ATdEeBNHoUPIE2l1XJY16iK_JzzwUciT-_0XFY-QUIbGXy3pZw76k7A8BJ4OYy7M77Ql-idSKcqEI6we',
			production: 'xxxxxxxxx'
		},

		commit: true, // Show a 'Pay Now' button

		validate: function(actions){

			if(typeof $form === 'undefined') {
				$form = $(this.outlet).closest('.leyka-pf__form');
			}

//			togglePayPalSubmit(actions, $form);

			$form.on(
				'change.leyka',
				'.step--person :input',
				{paypalSubmitActions: actions},
				togglePayPalSubmit
			);
		},

		onClick: function(){

//			var $form = $(this.outlet).closest('.leyka-pf__form');

			if( !$form.data('paypal-validation-allowed') ) {
				$form.data('paypal-validation-allowed', true);
			}

			$form.find('.step--person :input:first').trigger('change.leyka');

//			leykaValidateForm($form);
//
//			console.log(this, this.actions);

		},

		payment: function(data, actions) {

			return actions.payment.create({
				payment: {
					transactions: [
						{
							amount: { total: '1.00', currency: 'USD' }
						}
					]
				},

				experience: {
					input_fields: {
						no_shipping: 1
					}
				}
			});
		},

		onAuthorize: function(data, actions) {
			return actions.payment.execute().then(function(payment) {

				// The payment is complete!
				// You can now show a confirmation message to the customer
			});
		}

	}, '.leyka-paypal-form-submit');

}(jQuery));