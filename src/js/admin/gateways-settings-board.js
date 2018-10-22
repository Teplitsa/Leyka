/** Gateways settings board */

// Payment settings page:
jQuery(document).ready(function($){

    if( !$('#payment-settings-area-new.stage-payment').length ) {
        return;
    }

    var $pm_available_list = $('.pm-available'),
        $pm_order = $('#pm-order-settings'),
        $pm_update_status = $('.pm-update-status'),
        $ok_message = $pm_update_status.find('.ok-message'),
        $error_message = $pm_update_status.find('.error-message'),
        $ajax_loading = $pm_update_status.find('.leyka-loader');

    $pm_update_status.find('.result').hide();

    function leykaUpdatePmList($pm_order) {

        var params = {
            action: 'leyka_update_pm_list',
            pm_order: $pm_order.data('pm-order'),
            pm_labels: {},
            nonce: $pm_order.data('nonce')
        };

        $pm_order.find('input.pm-label-field.submitable').each(function(){
            params.pm_labels[$(this).prop('name')] = $(this).val();
        });

        $ok_message.hide();
        $error_message.hide();
        $ajax_loading.show();

        $.post(leyka.ajaxurl, params, null, 'json')
            .done(function(json) {

                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    $ok_message.hide();
                    $error_message.html(typeof json.message === 'undefined' ? leyka.common_error_message : json.message).show();

                    return;

                }

                $ok_message.show();
                $error_message.html('').hide();

            })
            .fail(function() {
                $error_message.html(leyka.common_error_message).show();
            })
            .always(function() {
                $ajax_loading.hide();
            });

    }

    // PM reordering:
    $pm_order
        .sortable({placeholder: '', items: '> li:visible'})
        .on('sortupdate', function(event){

            $pm_order.data('pm-order',
                $(this).sortable('serialize', {key: 'pm_order[]', attribute: 'data-pm-id', expression: /(.+)/})
            );

            leykaUpdatePmList($pm_order);

        }).on('click', '.pm-deactivate', function(e){ // PM deactivation

            e.preventDefault();

            var $pm_sortable_item = $(this).parents('li:first');

            $pm_sortable_item.hide(); // Remove a sortable block from the PM order settings
            $pm_available_list.filter('#'+$pm_sortable_item.data('pm-id')).removeAttr('checked');

            $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

        }).on('click', '.pm-change-label', function(e){

            e.preventDefault();

            var $this = $(this),
                $wrapper = $this.parents('li:first');

            $wrapper.find('.pm-control').hide();
            $wrapper.find('.pm-label').hide();
            $wrapper.find('.pm-label-fields').show();

        }).on('click', '.new-pm-label-ok,.new-pm-label-cancel', function(e){

            e.preventDefault();

            var $this = $(this),
                $wrapper = $this.parents('li:first'),
                $pm_label_wrapper = $wrapper.find('.pm-label'),
                new_pm_label = $wrapper.find('input[id*="pm_label"]').val();

            if($this.hasClass('new-pm-label-ok') && $pm_label_wrapper.text() !== new_pm_label) {

                $pm_label_wrapper.text(new_pm_label);
                $wrapper.find('input.pm-label-field').val(new_pm_label);

                leykaUpdatePmList($pm_order);

            } else {
                $wrapper.find('input[id*="pm_label"]').val($pm_label_wrapper.text());
            }

            $pm_label_wrapper.show();
            $wrapper.find('.pm-label-fields').hide();
            $wrapper.find('.pm-control').show();

        }).on('keydown', 'input[id*="pm_label"]', function(e){

            var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if(keycode == 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

                e.preventDefault();
                $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

            }

        });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    $pm_available_list.click(function(){

        var $pm_available_checkbox = $(this);

        // Show/hide a PM settings:
        $('#pm-'+$pm_available_checkbox.attr('id')).toggle();

        var $sortable_pm = $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]');

        // Add/remove a sortable block from the PM order settings:
        if($pm_available_checkbox.attr('checked') && $sortable_pm.length) {
            $sortable_pm.show();
        } else {
            $sortable_pm.hide();
        }

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

});

// Yandex.Kassa settings:
jQuery(document).ready(function($){

    var $gateway_settings = $('.single-gateway-settings.gateway-yandex');

    if( !$gateway_settings.length ) {
        return;
    }

    var $yandex_new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$yandex_new_api_used.length ) {
        return;
    }

    $yandex_new_api_used.on('change.leyka', function(){

        if($(this).prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});