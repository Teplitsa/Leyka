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

    // Auto-select the code to embed:
    $('.campaign-embed-code').on('focus keyup', function(e){

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
});