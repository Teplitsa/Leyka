/**
 * Admin JS - Campaign editing page
 **/

// jQuery(document).ready(function($){
//
//     /** Edit campaign page - donations data table: */
//     $('#donations-data-table').DataTable({
//         'lengthMenu': [[25, 50, 100, 200], [25, 50, 100, 200]],
//         language: {
//             processing:     leyka_dt.processing,
//             search:         leyka_dt.search,
//             lengthMenu:     leyka_dt.lengthMenu,
//             info:           leyka_dt.info,
//             infoEmpty:      leyka_dt.infoEmpty,
//             infoFiltered:   leyka_dt.infoFiltered,
//             infoPostFix:    leyka_dt.infoPostFix,
//             loadingRecords: leyka_dt.loadingRecords,
//             zeroRecords:    leyka_dt.zeroRecords,
//             emptyTable:     leyka_dt.emptyTable,
//             paginate: {
//                 first:    leyka_dt.paginate_first,
//                 previous: leyka_dt.paginate_previous,
//                 next:     leyka_dt.paginate_next,
//                 last:     leyka_dt.paginate_last
//             },
//             aria: {
//                 sortAscending:  leyka_dt.aria_sortAsc,
//                 sortDescending: leyka_dt.aria_sortDesc
//             }
//         }
//     });
//
//     // Recalculate total funded amount:
//     $('#recalculate_total_funded').click(function(e){
//
//         e.preventDefault();
//
//         var $link = $(this).attr('disabled', 'disabled'),
//             $indicator = $link.parent().find('#recalculate_total_funded_loader').show(),
//             $message = $link.parent().find('#recalculate_message').hide(),
//             $total_collected_field = $('#collected_target');
//
//         $.get(leyka.ajaxurl, {
//             campaign_id: $link.data('campaign-id'),
//             action: 'leyka_recalculate_total_funded_amount',
//             nonce: $link.data('nonce')
//         }, function(resp){
//
//             $link.removeAttr('disabled');
//             $indicator.hide();
//
//             if(parseFloat(resp) >= 0) {
//
//                 var old_value = parseFloat($total_collected_field.val());
//                 resp = parseFloat(resp);
//
//                 $total_collected_field.val(resp);
//                 if(old_value != resp) { // If recalculated sum is different than saved one, refresh the campaign edition page
//                     $('#publish').click();
//                 }
//
//             } else {
//                 $message.html(resp).show();
//             }
//         });
//     });
//
// });