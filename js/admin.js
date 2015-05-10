/**
 * Admin JS
 **/

jQuery(document).ready(function($){

    /** Plugin metaboxes rendering: */
    function leyka_support_metaboxes(metabox_area) {

        $('.if-js-closed').removeClass('if-js-closed').addClass('closed'); // close postboxes that should be closed
        postboxes.add_postbox_toggles(metabox_area);
    }

    // Leyka desktop page:
    if($('body').hasClass('toplevel_page_leyka')) {
        leyka_support_metaboxes('toplevel_page_leyka');
    }

    // Payment settings page:
    if($('#payment-settings-area').length) {

        leyka_support_metaboxes('leyka_settings_payment');

        var $gateways_accordion = $('#pm-settings-wrapper');
        $gateways_accordion.accordion({
            heightStyle: 'content',
            header: '.gateway-settings > h3'
        });

        /** Gateways & PM folding on click by the active PM checkboxes. Also PM ordering */
        var $pm_order = $('#pm-order-settings').sortable({
            placeholder: '',
            update: function(event){

                $('input[name="leyka_pm_order"]').val( $(this).sortable('serialize', {
                    key: 'pm_order[]', attribute: 'data-pm-id', expression: /(.+)/
                }) );
            }
        });

        $('.pm-active').click(function(){

            var $this = $(this),
                $gateway_metabox = $this.parents('.postbox'),
                gateway_id = $gateway_metabox.attr('id').replace('leyka_payment_settings_gateway_', ''),
                $gateway_settings = $('#gateway-'+gateway_id);

            // Show/hide a PM settings:
            $('#pm-'+$this.attr('id')).toggle();

            // Add/remove a sortable block from the PM order settings:
            if($this.attr('checked')) {
                $pm_order.append('<li data-pm-id="'+$this.attr('id')+'" class="pm-order">'+$this.data('pm-label')+'</li>');
            } else {
                $('.pm-order[data-pm-id="'+$this.attr('id')+'"]').remove();
            }
            $pm_order.sortable('refresh').sortable('refreshPositions');

            // Show/hide a whole gateway settings if there are no PMs from it selected:
            if( !$gateway_metabox.find('input:checked').length ) {

                $gateway_settings.hide();
                $gateways_accordion.accordion('refresh');

            } else if( !$gateway_settings.is(':visible') ) {

                $gateway_settings.show();
                $gateways_accordion.accordion('refresh');

                $('.pm-order[data-pm-id='+$this.attr('id')+']').show();
                $pm_order.sortable('refresh').sortable('refreshPositions');
            }
        });
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
    $('.wrap h2 a').after($('.donations-export-form').detach());

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

    $('#leyka_donation_form_mode-field').change(function(e){
        if($(this).attr('checked'))
            $('#leyka_scale_widget_place-wrapper, #leyka_donations_history_under_forms-wrapper')
                .find(':input').removeAttr('disabled');
        else
            $('#leyka_scale_widget_place-wrapper, #leyka_donations_history_under_forms-wrapper')
                .find(':input').attr('disabled', 'disabled');
    });

    $('#leyka_auto_refresh_currency_rates-field').change(function(e){
        if($(this).attr('checked')) {
            $('#leyka_currency_rur2usd-wrapper, #leyka_currency_rur2eur-wrapper')
                .find(':input').attr('disabled', 'disabled');
        } else {
            $('#leyka_currency_rur2usd-wrapper, #leyka_currency_rur2eur-wrapper')
                .find(':input').removeAttr('disabled');
        }
    }).change();

    /** Feedback form */
    var $form = $('#feedback'),
        $loader = $('#feedback-loader'),
        $message_ok = $('#message-ok'),
        $message_error = $('#message-error');

    $form.submit(function(e){

        e.preventDefault();

        if( !validate_feedback_form() )
            return false;

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
            $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid).show();

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

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}