(function($){

    $('script[src*="checkout.js"]').data('log-level', leyka.paypal_is_test_mode ? 'debug' : 'error');

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

	function togglePayPalSubmit(actions) {

		if( $form.data('paypal-validation-allowed') ) {
			return leykaValidateForm($form) ? actions.enable() : actions.disable();
		} else {
			return actions.disable();
		}

	}

	paypal.Button.render({

		env: leyka.paypal_is_test_mode ? 'sandbox' : 'production',
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

            togglePayPalSubmit(actions);

            $form.on('change.leyka', '.step--person :input', function(){
                togglePayPalSubmit(actions);
            });
		},

		onClick: function(){

			if( !$form.data('paypal-validation-allowed') ) {
				$form.data('paypal-validation-allowed', true);
			}

            $form.find('.step--person :input:first').trigger('change.leyka');

		},

		payment: function(data, actions) {

			var donation_amount = parseFloat($form.find('.amount__figure input').val()),
				donation_currency = $form.find('input.leyka_donation_currency').val();

			donation_currency = donation_currency === 'rur' ? 'RUB' : donation_currency.toUpperCase();

			return actions.payment.create({
				payment: {
					transactions: [
						{amount: {total: donation_amount, currency: donation_currency}}
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