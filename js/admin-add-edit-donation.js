/**
 * Admin JS - Donation adding/editing editing pages
 **/

jQuery(document).ready(function($){

    var $donation_date = $('#donation-date-view').datepicker({
        changeMonth: true,
        changeYear: true,
        minDate: '-5Y',
        maxDate: '+1Y',
        dateFormat: 'dd.mm.yy',
        altField: '#donation-date',
        altFormat: 'yy-mm-dd'
    });

    // Validate add/edit donation form:
    $('form#post').submit(function(e){

//        if( !$('#payment-type-hidden').length ) // Donation edit page, we're not validating it
//            return;

        var $form = $(this),
            is_valid = true,
            $field = $('#campaign-id');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#campaign_id-error').html(leyka.campaign_required).show();

        } else
            $form.find('#campaign_id-error').html('').hide();

        $field = $('#donor-email');
        if($field.val() && !is_email($field.val())) {

            is_valid = false;
            $form.find('#donor_email-error').html(leyka.email_invalid).show();

        } else
            $form.find('#donor_email-error').html('').hide();

        $field = $('#donation-amount');
        if( !$field.val() || parseInt($field.val()) == 0 || isNaN($field.val()) ) {

            is_valid = false;
            $form.find('#donation_amount-error').html(leyka.amount_incorrect).show();

        } else
            $form.find('#donation_amount-error').html('').hide();

        $field = $('#donation-pm');
        if($field.val() == 'custom')
            $field = $('#custom-payment-info');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#donation_pm-error').html(leyka.donation_source_required).show();
        } else
            $form.find('#donation_pm-error').html('').hide();

        $('#donation-date-field').val($.datepicker.formatDate('yy-mm-dd', $donation_date.datepicker('getDate')));

        if( !is_valid )
            e.preventDefault();
    });

    /** New donation page: */

    $('#donation-pm').change(function(){

        var $this = $(this);
        if($this.val() == 'custom')
            $('#custom-payment-info').show();
        else {

            $('#custom-payment-info').hide();

            if($this.val().split('-')[0] == 'chronopay')
                $('#chronopay-fields').show();
            else
                $('#chronopay-fields').hide();
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

    $('#campaign-select-trigger').click(function(e){
        e.preventDefault();

        $(this).slideUp(100);
        $('#campaign-select-fields').slideDown(100);
        $('#campaign-field').removeAttr('disabled');
    });

    $('#cancel-campaign-select').click(function(e){
        e.preventDefault();

        $('#campaign-select-fields').slideUp(100);
        $('#campaign-field').attr('disabled', 'disabled');
        $('#campaign-select-trigger').slideDown(100);
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