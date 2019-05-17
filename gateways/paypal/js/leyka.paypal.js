(function($){

    // $('script[src*="checkout.js"]').data('log-level', leyka.paypal_is_test_mode ? 'debug' : 'error');

	var $form,
		$errors;

	function addError($errors_block, error_html) {

		$errors_block.html(error_html).show();

		// Center the error block in the viewport
		$('html, body').animate({
			scrollTop: $errors_block.offset().top -
				($(window).height() - $errors_block.outerHeight()) / 2
		}, 250);

	}

	// If PayPal chosen, show its special submit instead of normal:
	$('.payment-opt__radio').change(function(){

		var $this = $(this);

		$form = $this.closest('.leyka-pf__form'); //('.leyka-pf');
		$errors = $form.siblings('.leyka-submit-errors');

		if($this.attr('value').indexOf('paypal') !== -1) {

			// For PayPal, disable Leyka form submits if form is valid:
			$this.data('processing', 'custom');

			$form.find('.leyka-paypal-form-submit').show();
			$form.find('input.leyka-default-submit').hide();

		} else {

			$this.data('processing', 'default');

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

    if( !$('.leyka-paypal-form-submit').length ) { // No Revo forms with PayPal on current page
        return;
    }

	var paypal_env_data = leyka.paypal_is_test_mode ?
			{sandbox: leyka.paypal_client_id, production: 'xxxxxxxxxx'} :
			{production: leyka.paypal_client_id, sandbox: 'xxxxxxxxxx'};

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

		client: paypal_env_data,

		commit: true, // Show a 'Pay Now' button

		validate: function(actions){

			if(typeof $form === 'undefined') {
				$form = $(this.outlet).closest('.leyka-pf__form');
			}

            togglePayPalSubmit(actions);

            $form.on('change.leyka', '.step--person :input', function(){

                $form.data('paypal-validation-allowed', true);
                togglePayPalSubmit(actions);

            });
		},

		onClick: function(){
            $form.find('.step--person :input:first').trigger('change.leyka');
		},

		payment: function(data, actions) {

            $form = $form.find('form.leyka-inline-campaign-form'); // Only for Revo template ATM

			var donation_amount = parseFloat($form.find('.amount__figure input').val()) + '.00',
				donation_currency = $form.find('input.leyka_donation_currency').val(),
				donor_info = {email: $form.find('input[name="leyka_donor_email"]').val()},
                tmp_donation_data = {action: 'leyka_ajax_get_gateway_redirect_data', without_form_submission: true},
                tmp_donation_data_array = $form.serializeArray(),
                new_donation_id = false;

            for(var i=0; i<tmp_donation_data_array.length; i++) {
                tmp_donation_data[tmp_donation_data_array[i].name] = tmp_donation_data_array[i].value;
            }

            /**
			 * @todo Do tests to wrap this ajax call in promise-returning function.
			 * See possible problem here: https://github.com/paypal/paypal-checkout/issues/494
			 * */
            $.ajax({
                type: 'post',
                url: leyka.ajaxurl,
                data: tmp_donation_data,
                async: false
            }).done(function(response){

                response = $.parseJSON(response);

                if( !response || typeof response.status === 'undefined' ) {

					addError($errors, leyka.ajax_wrong_server_response);
					/** @todo Return some [object] created by [actions]. API docs missing ATM */
                    return false;

                } else if(response.status !== 0 && typeof response.message !== 'undefined') {

					addError($errors, response.message);
					return false;

				} else if(typeof response.donation_id === 'undefined' || response.donation_id <= 0) {

					addError($errors, leyka.ajax_donation_not_created);
					return false;

				} else {
					new_donation_id = response.donation_id;
				}

                if($form.hasClass('leyka-revo-form')) { // Close the Revo popup
                    $form.closest('.leyka-pf').leykaForm('close');
                }

            });

            donation_currency = donation_currency === 'rur' ?
				'RUB' : donation_currency.toUpperCase();
            if(donation_currency === 'RUB') {
                donor_info.country_code = 'RU';
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
                        notify_url: leyka.paypal_ipn_callback_url,
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

		onAuthorize: function(data, actions){
			return actions.payment.execute().then(function(payment){ // Transaction success

				// Update the dateway related payment data in donation:
                $.ajax({
                    type: 'post',
                    url: leyka.paypal_donation_update_callback_url,
                    data: {
                        _wpnonce: $form.find('input[name="_wpnonce"]').val(),
                        donation_id: payment.transactions[0].invoice_number,
                        paypal_token: data.paymentToken,
                        paypal_payment_id: data.paymentID
                    },
                    async: false
                }).done(function(response){

					response = $.parseJSON(response);

					if( !response || typeof response.status === 'undefined' ) {

						addError($errors, leyka.ajax_wrong_server_response);
						/** @todo Return some [object] created by [actions]. API docs missing ATM */
						return false;

					} else if(response.status !== 0 && typeof response.message !== 'undefined') {

						addError($errors, response.message);
						return false;

					}

					actions.redirect(); // Redirect on a success page

                });
			}, function(){ // Transaction error

			});
		},

		/** Checkout process error (on PayPal side). */
		onError: function(error_code) {

		    if( !$errors || !$errors.length ) { // Initial button loading errors
		        // $errors = $('.leyka-submit-errors'); // To show error msg on all Revo forms of current page
                return;
            }

			if(
			    typeof leyka.paypal_donation_failure_reasons !== 'undefined'
                && typeof leyka.paypal_donation_failure_reasons[error_code] !== 'undefined'
            ) {
				addError($errors, leyka.paypal_donation_failure_reasons[error_code]);
			} else { // Unknown error on PayPal side
				addError($errors, leyka.paypal_payment_process_error.replace('%s', error_code));
			}
		},

		/**
		 * On PayPal window close. Mb, we should just reload the campaign page
		 * instead of failure page redirect.
		 * */
		onCancel: function(data, actions){
			window.location.href = '';
//			return actions.redirect();
		}

	}, '.leyka-paypal-form-submit');

}(jQuery));
