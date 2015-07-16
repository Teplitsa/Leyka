/**
 * Admin JS - Campaign editing page
 **/

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
    $('.embed-code').on('focus keyup', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;

        if(keycode == 9 || !keycode) { // Tab or click

            var $this = $(this);
            $this.select();

            // Work around Chrome's little problem:
            $this.on('mouseup', function() {
                $this.off('mouseup');
                return false;
            });
        }
    });

    var $embed_code = $('#campaign-embed-code');

    $embed_code.keydown(function(e) { // Keep the iframe code from manual changing

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

    $('#embed_iframe_w, #embed_iframe_h').keydown(function(e) {

        if(e.keyCode == 13) { // Enter pressed - do not let the form be submitted
            e.preventDefault();
            return;
        }

        if( // Allowed special keys
            $.inArray(e.keyCode, [46, 8, 9]) != -1 || // Backspace, delete, tab
            (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
            (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
        ) {
            return; // Let it happen
        }

        if((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

    }).change(function(e){

        var $this = $(this),
            $text = $($embed_code.text());

        $text.attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());
        $('.leyka-embed-preview iframe').attr($this.attr('id') == 'embed_iframe_w' ? 'width' : 'height', $this.val());

        $embed_code.html($text.prop('outerHTML'));
    });
});