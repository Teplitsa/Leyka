/**
 * Admin JS
 **/

jQuery(document).ready(function($){

	/* Donation purpose matbox ui */
	var amout_set = $('#amount-set');

	amout_set.find('#amount_type').on('change', function(e){
		
		var opt = $(this).find('option:selected').val(),
			variants = amout_set.find('.leyka-amount-option').find('.amount-variants');

		variants.filter(':visible').hide(function(){
			variants.find('input[type="text"]').val(''); //reset values
		});
		amout_set.find('.leyka-amount-option').find('#'+opt).fadeIn();
	});

	amout_set.find('#add_variant').on('click', function(e){
		
		e.preventDefault();
		var variant_wrap = amout_set.find('.amount-variants-range');
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
            $('#leyka_'+pm+'_description-wrapper').slideDown(50);
        else
            $('#leyka_'+pm+'_description-wrapper').slideUp(50);
    }).each(function(){
        $(this).change();
    });
});