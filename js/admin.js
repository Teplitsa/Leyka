jQuery(document).ready(function($){

    // Plugin metaboxes rendering:
    function leyka_support_metaboxes(metabox_area) {

        $('.if-js-closed').removeClass('if-js-closed').addClass('closed'); // Close postboxes that should be closed
        postboxes.add_postbox_toggles(metabox_area);
    }

    // Auto-select the code to embed:
    $('.embed-code').on('focus.leyka keyup.leyka', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;

        if( !keycode || keycode == 9 ) { // Click or tab

            var $this = $(this);
            $this.select();

            $this.on('mouseup', function() { // Work around Chrome's little problem

                $this.off('mouseup');
                return false;

            });
        }
    });

    $('.read-only').on('keydown.leyka', function(e){ // Keep the field value from manual changing

        if( // Allowed special keys
        e.keyCode == 9 || // Tab
        (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        ) {
            return; // Let it happen
        }

        e.preventDefault();

    });

    var $body = $('body');

    if($body.hasClass('dashboard_page_leyka_donor_info')) { // Leyka Donor info page
        leyka_support_metaboxes('dashboard_page_leyka_donor_info');
    }

    /** Tooltips: */
    var $tooltips = $body.find('.has-tooltip');
    if($tooltips.length) {
        $tooltips.tooltip();
    }

    /** Manual emails sending: */
    $('.send-donor-thanks').click(function(e){

        e.preventDefault();

        var $this = $(this),
            $wrap = $this.parent(),
            donation_id = $wrap.data('donation-id');

        $this.fadeOut(100, function(){
            $this.html('<img src="'+leyka.ajax_loader_url+'" />').fadeIn(100);
        });

        $wrap.load(leyka.ajaxurl, {
            action: 'leyka_send_donor_email',
            nonce: $wrap.find('#_leyka_donor_email_nonce').val(),
            donation_id: donation_id
        });
    });

    // Exchange places of donations Export and Filter buttons:
    $('.wrap a.page-title-action').after($('.donations-export-form').detach());

    /** All campaign selection fields: */
    var $campaign_select = $('#campaign-select');
    if($campaign_select.length) {

        $campaign_select.keyup(function(){

            if( !$(this).val() ) {
                $('#campaign-id').val('');
                $('#new-donation-purpose').html('');
            }
        });
        $campaign_select.autocomplete({
            minLength: 1,
            focus: function(event, ui){
                $campaign_select.val(ui.item.label);
                $('#new-donation-purpose').html(ui.item.payment_title);

                return false;
            },
            change: function(event, ui){
                if( !$campaign_select.val() ) {
                    $('#campaign-id').val('');
                    $('#new-donation-purpose').html('');
                }
            },
            close: function(event, ui){
                if( !$campaign_select.val() ) {
                    $('#campaign-id').val('');
                    $('#new-donation-purpose').html('');
                }
            },
            select: function(event, ui){
                $campaign_select.val(ui.item.label);
                $('#campaign-id').val(ui.item.value);
                $('#new-donation-purpose').html(ui.item.payment_title);
                return false;
            },
            source: function(request, response) {
                var term = request.term,
                    cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];

                if(term in cache) {
                    response(cache[term]);
                    return;
                }

                request.action = 'leyka_get_campaigns_list';
                request.nonce = $campaign_select.data('nonce');

                $.getJSON(leyka.ajaxurl, request, function(data, status, xhr){

                    var cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];

                    cache[term] = data;
                    response(data);
                });
            }
        });

        $campaign_select.data('ui-autocomplete')._renderItem = function(ul, item){
            return $('<li>')
                .append(
                '<a>'+item.label+(item.label == item.payment_title ? '' : '<div>'+item.payment_title+'</div></a>')
            )
                .appendTo(ul);
        };
    }

    /** Feedback form */
    var $form = $('#feedback'),
        $loader = $('#feedback-loader'),
        $message_ok = $('#message-ok'),
        $message_error = $('#message-error');

    $form.submit(function(e){

        e.preventDefault();

        if( !validate_feedback_form() ) {
            return false;
        }

        $form.hide();
        $loader.show();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            topic: $form.find('#feedback-topic').val(),
            name: $form.find('#feedback-name').val(),
            email: $form.find('#feedback-email').val(),
            text: $form.find('#feedback-text').val(),
            nonce: $form.find('#nonce').val()
        }, function(response){

            $loader.hide();

            if(response && response == 0)
                $message_ok.fadeIn(100);
            else
                $message_error.fadeIn(100);
        });

        return true;
    });

    function validate_feedback_form() {

        var $form = $('#feedback'),
            is_valid = true,
            $field = $form.find('#feedback-topic');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else
            $form.find('#'+$field.attr('id')+'-error').html('').hide();

        $field = $form.find('#feedback-name');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else
            $form.find('#'+$field.attr('id')+'-error').html('').hide();

        $field = $form.find('#feedback-email');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else if( !is_email($field.val()) ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid_msg).show();

        } else
            $form.find('#'+$field.attr('id')+'-error').html('').hide();

        $field = $form.find('#feedback-text');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else
            $form.find('#'+$field.attr('id')+'-error').html('').hide();

        return is_valid;
    }
});

/**
 * Admin JS - Campaign editing page
 **/
jQuery(document).ready(function($){

    /** Edit campaign page - donations data table: */
    if($.DataTable) {
        // console.log();
    }
    console.log('HERE:', $.DataTable)
    $('#donations-data-table').DataTable({
        'lengthMenu': [[25, 50, 100, 200], [25, 50, 100, 200]],
        language: {
            processing:     leyka_dt.processing,
            search:         leyka_dt.search,
            lengthMenu:     leyka_dt.lengthMenu,
            info:           leyka_dt.info,
            infoEmpty:      leyka_dt.infoEmpty,
            infoFiltered:   leyka_dt.infoFiltered,
            infoPostFix:    leyka_dt.infoPostFix,
            loadingRecords: leyka_dt.loadingRecords,
            zeroRecords:    leyka_dt.zeroRecords,
            emptyTable:     leyka_dt.emptyTable,
            paginate: {
                first:    leyka_dt.paginate_first,
                previous: leyka_dt.paginate_previous,
                next:     leyka_dt.paginate_next,
                last:     leyka_dt.paginate_last
            },
            aria: {
                sortAscending:  leyka_dt.aria_sortAsc,
                sortDescending: leyka_dt.aria_sortDesc
            }
        }
    });

    // Recalculate total funded amount:
    $('#recalculate_total_funded').on('click', function(e){

        e.preventDefault();

        var $link = $(this).attr('disabled', 'disabled'),
            $indicator = $link.parent().find('#recalculate_total_funded_loader').show(),
            $message = $link.parent().find('#recalculate_message').hide(),
            $total_collected_field = $('#collected_target');

        $.get(leyka.ajaxurl, {
            campaign_id: $link.data('campaign-id'),
            action: 'leyka_recalculate_total_funded_amount',
            nonce: $link.data('nonce')
        }, function(resp){

            $link.removeAttr('disabled');
            $indicator.hide();

            if(parseFloat(resp) >= 0) {

                var old_value = parseFloat($total_collected_field.val());
                resp = parseFloat(resp);

                $total_collected_field.val(resp);
                if(old_value != resp) { // If recalculated sum is different than saved one, refresh the campaign edition page
                    $('#publish').click();
                }

            } else {
                $message.html(resp).show();
            }

        });

    });

});

/** @var e JS keyup/keydown event */
function leyka_is_digit_key(e, numpad_allowed) {

    if(typeof numpad_allowed == 'undefined') {
        numpad_allowed = true;
    } else {
        numpad_allowed = !!numpad_allowed;
    }

    if( // Allowed special keys
    e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13 || // Backspace, delete, tab, enter
    (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
    (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
    (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    ) {
        return true;
    }

    if(numpad_allowed) {
        if( !e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57 ) {
            return true;
        } else {
            return e.keyCode >= 96 && e.keyCode <= 105;
        }
    } else {
        return !(e.shiftKey || e.keyCode < 48 || e.keyCode > 57);
    }

}

/** @var e JS keyup/keydown event */
function leyka_is_special_key(e) {

    // Allowed special keys
    return (
        e.keyCode === 9 || // Tab
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    );
}

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}
