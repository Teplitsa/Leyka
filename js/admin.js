/**
 * Admin JS
 **/

jQuery(document).ready(function($){

    // Exchange places of donations Export and Filter buttons:
//    $('#post-query-submit').after($('.donations-export-form').detach());

    $('.wrap h2 a').after($('.donations-export-form').detach());

    /** All campaign selection fields: */

    var $campaign_select = $('#campaign-select');
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
    if($campaign_select.length) {
        $campaign_select.data('ui-autocomplete')._renderItem = function(ul, item){
            return $('<li>')
                .append(
                    '<a>'+item.label+(item.label == item.payment_title ? '' : '<div>'+item.payment_title+'</div></a>')
                )
                .appendTo(ul);
        };
    }

    $('#leyka_donation_form_mode-field').change(function(e){
        if($(this).attr('checked'))
            $('#leyka_scale_widget_place-wrapper, #leyka_donations_history_under_forms-wrapper')
                .find(':input').removeAttr('disabled');
        else
            $('#leyka_scale_widget_place-wrapper, #leyka_donations_history_under_forms-wrapper')
                .find(':input').attr('disabled', 'disabled');
    });
});

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}