jQuery(document).ready(function($){

    /** @var leyka object Localization strings */
    /** @todo Only for old templates - Revo & earlier. Remove this code when their support is finished. */
    $(document).on('validate-custom-form-fields.leyka', 'form.leyka-pm-form', function(e, $form){

     console.log('DBG validate-custom-form start');
        // Selected PM don't belong to the MIXPLAT gateway:
        if(leyka_get_pm_full_id($form).indexOf('mixplat') < 0) {
            return;
        }

        var $phone_field = $form.find(':input.phone-num.mixplat-phone'),
            $error = $form.find('.'+$phone_field.attr('name')+'-error'),
            phone_value = $phone_field.val();

        if($phone_field.length && phone_value.length) {

            phone_value = phone_value.replace(/[+. -]/, '');
            phone_value = phone_value.replace(/\s/, '');
            phone_value = phone_value.replace(/^7/, '');

            if( !phone_value.match(/^\d{10}$/) ) {

                $error.html(leyka.phone_invalid).show();
                $phone_field.focus();
                $form.data('custom-fields-are-invalid', 1);

            } else {
                $error.hide();
            }
        }

     console.log('DBG validate-custom-form end');
        // All standard fields validation is already passed in the main script.
    });


    $(document).on('submit.leyka', 'form.leyka-pm-form,form.leyka-revo-form', function(e){

     console.log('DBG submit.leyka start');

        function addError($errors_block, error_html) {
            $errors_block.html(error_html).show();
            $('html, body').animate({ // 35px is a height of the WP admin bar (just in case)
                scrollTop: $errors_block.offset().top - 35
            }, 250);
        }

				function decodeHtmlEntities(text) {
    				const textArea = document.createElement('textarea');
				    textArea.innerHTML = text;
    				return textArea.value;
				}

        /** @var leyka object Localization strings */

        var $form = $(this),
            $form_wrapper = $form.parents('.leyka-payment-form'),
            $errors = $('#leyka-submit-errors');

				var gateway_is_chosen=false;
				var gateway_found=false;
				var processing_type='default';
				
				$form.find('input[name="leyka_payment_method"][value*="mixplat-"]').each(function(){
         gateway_found=true;
         let chosen = $(this).prop('type') === 'hidden' ? $(this).val().indexOf('mixplat') >= 0 : !!$(this).prop('checked');
         if(chosen) { gateway_is_chosen=true; processing_type=$(this).attr('data-processing'); }
         console.log('DBG',$(this).val(),chosen,processing_type);
        });

				if(!gateway_found) {
        		console.log("DBG mixplat !gateway_found");
            return;
        }
        
        if(!gateway_is_chosen) {
        		console.log("DBG mixplat !gateway_is_chosen");
            return;
        }

        if(processing_type==='default') {
        		console.log("DBG default processing type");
            return;
        }

        let $revo_redirect_step = $form.closest('.leyka-pf').find('.leyka-pf__redirect');
        if($revo_redirect_step.length) {
            $revo_redirect_step.addClass('leyka-pf__redirect--open');
        }        
        

        if($form.data('submit-in-process')) {
        	  console.log('DBG submit.leyka error submit-in-process');
            return;
        } else {
        	  console.log('DBG submit-in-process set');
        	  let url = new URL(window.location.href);
        	  let utm_source = url.searchParams.get('utm_source'), utm_medium = url.searchParams.get('utm_medium'), utm_campaign = url.searchParams.get('utm_campaign'), utm_term = url.searchParams.get('utm_term');
        	  if(utm_source != null) { $form.append('<input type="hidden" name="utm_source" value="' +utm_source+ '">'); }
					  if(utm_medium != null) { $form.append('<input type="hidden" name="utm_medium" value="' +utm_medium+ '">'); }
					  if(utm_campaign != null) { $form.append('<input type="hidden" name="utm_campaign" value="' +utm_campaign+ '">'); }
					  if(utm_term != null) { $form.append('<input type="hidden" name="utm_term" value="' +utm_term+ '">'); }
            $form.data('submit-in-process', 1);
        }



        // Donation form validation already passed in the main script (public.js)

        let is_recurring = $form.find('.leyka-recurring').prop('checked') ||
                           $form.find('.is-recurring-chosen').val() > 0, // For Revo template
            data_array = $form.serializeArray(),
            data = {action: 'leyka_ajax_get_gateway_redirect_data'};

        for(let i = 0; i < data_array.length; i++) {
            data[data_array[i].name] = data_array[i].value;
        }

        e.preventDefault();


        console.log('DBG leyka.form end');


		 $.ajax({
            type: 'post',
            url: leyka.ajaxurl,
            data: data,
            beforeSend: function(xhr){
                /** @todo Show some loader */
            }
        }).done(function(response){

            console.log('DBG leyka.post');
            $form.data('submit-in-process', 0);

            response = $.parseJSON(response);

            if( !response || typeof response.status === 'undefined' ) {

                addError($errors, leyka.ajax_wrong_server_response);
                return false;

            } else if(response.status !== 0 && typeof response.message !== 'undefined') {

                addError($errors, response.message);
                return false;

            }


            if(leyka.gtm_ga_eec_available) {

                window.dataLayer = window.dataLayer || [];

                
                dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
                dataLayer.push({
                    event: "begin_checkout",
                    ecommerce: {
                    items: [{
                        item_name: response.decription, // Name or ID is required.
                        item_id: response.merchant_payment_id,
                        price:response.amount,
                        index: 1,
                        quantity: 1
                    }]
                    }
                }); 
                
                console.log("action: begin_checkout")

            }

					let options = {
						 'widget_key': response.widget_key,
						 'merchant_payment_id': response.merchant_payment_id,
						 'merchant_fields': {},
						 'test': response.test,
						 'description': decodeHtmlEntities(response.description),
						 'currency': response.currency,
						 'amount': response.amount,

						 'user_email': response.user_email,
						 'recurrent_payment': response.recurrent_payment,
						 'payment_method': response.payment_method,
					 }

					 if(typeof response.utm_source !== 'undefined') { options['utm_source']=response.utm_source; }
					 if(typeof response.utm_medium !== 'undefined') { options['utm_medium']=response.utm_medium; }
					 if(typeof response.utm_campaign !== 'undefined') { options['utm_campaign']=response.utm_campaign; }
					 if(typeof response.utm_term !== 'undefined') { options['utm_term']=response.utm_term; }
					 if(typeof response.merchant_campaign_id !== 'undefined') { options['merchant_campaign_id']=response.merchant_campaign_id; }
					 if(typeof response.billing_type !== 'undefined') { options['billing_type']=response.billing_type; }
					 if(typeof response.user_name !== 'undefined') { options['user_name']=response.user_name; }
					 if(typeof response.user_phone !== 'undefined') { options['user_phone']=response.user_phone; }
					 if(typeof response.user_comment !== 'undefined') { options['user_comment']=response.user_comment; }
					 if(typeof response.merchant_data !== 'undefined') { options['merchant_data']=response.merchant_data; }
           if(is_recurring) { options['recurrent_payment'] = 1; }

           if(response.merchant_fields && !$.isEmptyObject(response.merchant_fields)) {
                $.each(response.merchant_fields, function(key, value){
                    options['merchant_fields'][key]=value;
                });
           }

           $.each(options, function(key, value){ console.log('DBG',key,':',value); });

            let M = new Mixplat(options);
            M.build();
            M.setSuccessCallback(function(o, i){
                console.log('DBG ' + new Date().toISOString() + ' success');
                console.log('DBG REDIRECT ' + response.url_success);
                window.location.href = response.url_success;
            });
            M.setFailCallback(function(o, i){
                console.log('DBG ' + new Date().toISOString() + ' fail');
                console.log('DBG NO REDIRECT ' + response.url_failure);
            });
            M.setProcessCallback(function(o, i){
                console.log('DBG ' + new Date().toISOString() + ' pending');
            });

            M.setEndCallback(function(o, i){
                console.log('DBG ' + new Date().toISOString() + ' end');
            });

            M.setCloseCallback(function(o, i){
                console.log('DBG ' + new Date().toISOString() + ' close');
            });

     });

    });

});
