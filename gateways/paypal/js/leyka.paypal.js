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

            $form = $form.find('form.leyka-inline-campaign-form'); // Only for Revo template ATM

			var donation_amount = parseFloat($form.find('.amount__figure input').val()) + '.00',
				donation_currency = $form.find('input.leyka_donation_currency').val(),
				donor_info = {email: $form.find('input[name="leyka_donor_email"]').val()},
                tmp_donation_data = {action: 'leyka_ajax_donation_submit', without_form_submission: true},
                tmp_donation_data_array = $form.serializeArray(),
                new_donation_id = false;

            for(var i=0; i<tmp_donation_data_array.length; i++) {
                tmp_donation_data[tmp_donation_data_array[i].name] = tmp_donation_data_array[i].value;
            }

            console.log('Request...');
            $.ajax({
                type: 'post',
                url: leyka.ajaxurl,
                data: tmp_donation_data,
                async: false,
                beforeSend: function(xhr){
                    /** @todo Show some loader */
                }
            }).done(function(response){

                $form.data('submit-in-process', 0);

                response = $.parseJSON(response);
                if( !response || typeof response.status == 'undefined' ) { // Wrong answer from ajax handler
                    // addError($errors, leyka.cp_wrong_server_response);
                    return false;
                }

                new_donation_id = response.donation_id;

            });

            donation_currency = donation_currency === 'rur' ? 'RUB' : donation_currency.toUpperCase();
            if(donation_currency === 'RUB') {
                donor_info.country_code = 'RU';
            }

            if($form.hasClass('leyka-revo-form')) {
                $form.closest('.leyka-pf').leykaForm('close');
            }

            return actions.payment.create({
                payment: {
                    intent: 'sale',
                    payer: {
                        payment_method: 'paypal',
                        status: leyka.paypal_accept_verified_only ? 'VERIFIED' : 'UNVERIFIED'
                    },
                    transactions: [{
                        amount: {total: donation_amount, currency: donation_currency},
                        invoice_number: new_donation_id,
                        notify_url: leyka.paypal_callback_url,
                        description: $form.find('input[name="leyka_ga_campaign_title"]').val(),
                        payment_options: {
                            allowed_payment_method: 'INSTANT_FUNDING_SOURCE'
                        }
                    }],
                    redirect_urls: {
                        return_url: leyka.success_page_url,
                        cancel_url: leyka.failure_page_url
                    }
                },

                experience: {
                    input_fields: {
                        no_shipping: 1
                    }
                }
            });

		},

		onAuthorize: function(data, actions) {
            console.log('On Authorize:', data, actions)
			return actions.payment.execute().then(function(payment){

                console.log('payment:', payment)

				// The payment is complete!
				// You can now show a confirmation message to the customer
			});
		}

	}, '.leyka-paypal-form-submit');

}(jQuery));