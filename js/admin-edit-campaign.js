/**
 * Admin JS - Campaign editing page
 **/

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

jQuery(document).ready(function($){

    /** Edit campaign page - donations data table: */
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

    $('input[name="embed-type"]').click(function(){

        $('.embed-area').hide();
        $('#embed-'+$(this).val()).show();

    });

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

    $('.read-only').on('keydown.leyka', function(e){ // Keep the iframe code from manual changing

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

    $('#embed_iframe_w, #embed_iframe_h').keydown(function(e){

        if(e.keyCode == 13) { // Enter pressed - do not let the form be submitted

            e.preventDefault();
            $(this).change();
            return;

        }

        if( // Allowed special keys
            $.inArray(e.keyCode, [46, 8, 9]) != -1 || // Backspace, delete, tab
            (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
            (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        ) {
            return; // Let it happen
        }

        if( !leyka_is_digit_key(e) ){
            e.preventDefault();
        }

    }).change(function(e){

        var $this = $(this),
            $embed_code = $('#campaign-embed-code'),
            $text = $($embed_code.text());

        $text.attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());
        $('.leyka-embed-preview iframe').attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());

        $embed_code.html($text.prop('outerHTML'));

    });

    // Recalculate total funded amount:
    $('#recalculate_total_funded').click(function(e){

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