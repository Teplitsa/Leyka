/**
 * Admin JS
 **/

jQuery(document).ready(function($){

	/* Donation purpose matbox ui */
	var amount_set = $('#amount-set');

	amount_set.find('#amount_type').on('change', function(e){
		
		var opt = $(this).find('option:selected').val(),
			variants = amount_set.find('.leyka-amount-option').find('.amount-variants');

		variants.filter(':visible').hide(function(){
			variants.find('input[type="text"]').val(''); //reset values
		});
		amount_set.find('.leyka-amount-option').find('#'+opt).fadeIn();
	});

	amount_set.find('#add_variant').on('click', function(e){
		
		e.preventDefault();
		var variant_wrap = amount_set.find('.amount-variants-range');
		$('<div><input type="text" name="amount_variants[]" value=""></div>').fadeIn().appendTo(variant_wrap);
	});

    $('#donation-status-log-toggle').click(function(e){
        e.preventDefault();

        $('#donation-status-log').slideToggle(100);
    });

    $('.send-donor-thanks').click(function(e){
        e.preventDefault();

        var $wrap = $(this).parent(),
            donation_id = $wrap.data('donation-id');
        
        $(this).fadeOut(100, function(){ $(this).html('<img src="'+leyka.ajax_loader_url+'" />').fadeIn(100); });

        $wrap.load(leyka.ajaxurl, {
            action: 'leyka_send_donor_email',
            nonce: $wrap.find('#_leyka_donor_email_nonce').val(),
            donation_id: donation_id
        });
    });

    $('input[name*=leyka_pm_available]').change(function(){
        var $this = $(this),
            pm = $this.val();

        pm = pm.split('-')[1];
        if($this.attr('checked'))
//            $('#leyka_'+pm+'_description-wrapper').slideDown(50);
            $('[id*=leyka_'+pm+']').slideDown(50);
        else
            $('[id*=leyka_'+pm+']').slideUp(50);
//            $('#leyka_'+pm+'_description-wrapper').slideUp(50);
    }).each(function(){
        $(this).change();
    });

    // If single donation page is opened, don't show "cancel this recurrents" metabox:
    $('#hide-recurrent-metabox').each(function(){
        $(this).parents('#leyka_donation_recurrent_cancel').hide();
    });

    $('.recurrent-cancel').click(function(e){
        e.preventDefault();

        var $this = $(this);

        $('#ajax-processing').fadeIn(100);
        $this.fadeOut(100);

        // Do a recurrent donations cancelling procedure:
        $.post(leyka.ajaxurl, {
            action: 'leyka_cancel_recurrents',
            nonce: $this.data('nonce'),
            donation_id: $this.data('donation-id')
        }, function(response){
            $('#ajax-processing').fadeOut(100);
            response = $.parseJSON(response);

            if(response.status == 0) {

                $('#ajax-response').html('<div class="error-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeIn(100);

            } else if(response.status == 1) {

                $('#ajax-response').html('<div class="success-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeOut(100);

            }
        });
    });

    $('#recurrent-cancel-retry').click(function(e){
        e.preventDefault();

        $('.recurrent-cancel').click();
    });
});