/** Admin JS - Donation adding/editing pages **/
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donation-info-page' ) {
        return;
    }

    // Validate add/edit donation form:
    $('form#post').submit(function(e){

        let $form = $(this),
            is_valid = true,
            $field = $('#campaign-id');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#campaign_id-error').html(leyka.campaign_required).show();

        } else {
            $form.find('#campaign_id-error').html('').hide();
        }

        $field = $('#donor-email');
        if($field.val() && !is_email($field.val())) {

            is_valid = false;
            $form.find('#donor_email-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#donor_email-error').html('').hide();
        }

        $field = $('#donation-amount');
        let amount_clear = parseFloat($field.val().replace(',', '.'));
        if( !$field.val() || amount_clear === 0.0 || isNaN(amount_clear) ) {

            is_valid = false;
            $form.find('#donation_amount-error').html(leyka.amount_incorrect_msg).show();

        } else {
            $form.find('#donation_amount-error').html('').hide();
        }

        $field = $('#donation-pm');
        if($field.val() === 'custom') {
            $field = $('#custom-payment-info');
        }
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#donation_pm-error').html(leyka.donation_source_required).show();
        } else {
            $form.find('#donation_pm-error').html('').hide();
        }

        if( !is_valid ) {
            e.preventDefault();
        }

    });

    /** New donation page: */
    $('#donation-pm').change(function(){

        let $this = $(this);

        if($this.val() === 'custom') {
            $('#custom-payment-info').show();
        } else {

            $('#custom-payment-info').hide();

            var gateway_id = $this.val().split('-')[0];

            $('.gateway-fields').hide();
            $('#'+gateway_id+'-fields').show();
        }
    }).keyup(function(e){
        $(this).trigger('change');
    });

    /** Edit donation page: */
    $('#donation-status-log-toggle').click(function(e){

        e.preventDefault();

        $('#donation-status-log').slideToggle(100);

    });

    $('input[name*=leyka_pm_available]').change(function(){

        let $this = $(this),
            pm = $this.val();

        pm = pm.split('-')[1];
        if($this.attr('checked')) {
            $('[id*=leyka_'+pm+']').slideDown(50);
        } else {
            $('[id*=leyka_'+pm+']').slideUp(50);
        }

    }).each(function(){
        $(this).change();
    });

    $('#campaign-select-trigger').click(function(e){

        e.preventDefault();

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title.data('campaign-payment-title-previous', $campaign_payment_title.text());

        $(this).slideUp(100);
        $('#campaign-select-fields').slideDown(100);
        $('#campaign-field').removeAttr('disabled');

    });

    $('#cancel-campaign-select').click(function(e){

        e.preventDefault();

        $('#campaign-select-fields').slideUp(100);
        $('#campaign-field').attr('disabled', 'disabled');
        $('#campaign-select-trigger').slideDown(100);

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title
            .text($campaign_payment_title.data('campaign-payment-title-previous'))
            .removeData('campaign-payment-title-previous');

    });

    $('.recurrent-cancel').click(function(e){

        e.preventDefault();

        $('#ajax-processing').fadeIn(100);

        let $this = $(this);
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

    LeykaDOMControl.initVisibilityControlButtons();

});