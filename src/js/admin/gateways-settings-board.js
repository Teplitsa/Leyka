/** Gateways settings board */

// Payment settings page:
jQuery(document).ready(function($){

    if( !$('#payment-settings-area-new.stage-payment').length ) {
        return;
    }

    let $pm_available_list = $('.pm-available'),
        $pm_order = $('#pm-order-settings'),
        $pm_update_status = $('.pm-update-status'),
        $ok_message = $pm_update_status.find('.ok-message'),
        $error_message = $pm_update_status.find('.error-message'),
        $ajax_loading = $pm_update_status.find('.leyka-loader'),
        $pm_list_empty_block = $('.pm-list-empty');

    $pm_update_status.find('.result').hide();

    function leyka_update_pm_list($pm_order) {

        let params = {
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
            .done(function(json){

                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    $ok_message.hide();
                    $error_message.html(typeof json.message === 'undefined' ? leyka.common_error_message : json.message).show();

                    return;

                }

                $ok_message.show();
                $error_message.html('').hide();

            })
            .fail(function(){
                $error_message.html(leyka.common_error_message).show();
            })
            .always(function(){
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

            leyka_update_pm_list($pm_order);

            if($pm_order.find('.pm-order:visible').length) {
                $pm_list_empty_block.hide();
            } else {
                $pm_list_empty_block.show();
            }

        }).on('click', '.pm-deactivate', function(e){ // PM deactivation

            e.preventDefault();

            let $pm_sortable_item = $(this).parents('li:first');

            $pm_sortable_item.hide(); // Remove a sortable block from the PM order settings
            $pm_available_list.filter('#'+$pm_sortable_item.data('pm-id')).removeAttr('checked');

            $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

        }).on('click', '.pm-change-label', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first');

            $wrapper.find('.pm-control').hide();
            $wrapper.find('.pm-label').hide();
            $wrapper.find('.pm-label-fields').show();

        }).on('click', '.new-pm-label-ok,.new-pm-label-cancel', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first'),
                $pm_label_wrapper = $wrapper.find('.pm-label'),
                new_pm_label = $wrapper.find('input[id*="pm_label"]').val();

            if($this.hasClass('new-pm-label-ok') && $pm_label_wrapper.text() !== new_pm_label) {

                $pm_label_wrapper.text(new_pm_label);
                $wrapper.find('input.pm-label-field').val(new_pm_label);

                leyka_update_pm_list($pm_order);

            } else {
                $wrapper.find('input[id*="pm_label"]').val($pm_label_wrapper.text());
            }

            $pm_label_wrapper.show();
            $wrapper.find('.pm-label-fields').hide();
            $wrapper.find('.pm-control').show();

        }).on('keydown', 'input[id*="pm_label"]', function(e){

            let keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if(keycode === 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

                e.preventDefault();
                $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

            }

        });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    $pm_available_list.change(function(){

        let $pm_available_checkbox = $(this);

        $('#pm-'+$pm_available_checkbox.prop('id')).toggle(); // Show/hide a PM settings
        $('#'+$pm_available_checkbox.prop('id')+'-commission-wrapper').toggle(); // Show/hide a PM commission field

        let $sortable_pm = $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]');

        // Add/remove a sortable block from the PM order settings:
        if($pm_available_checkbox.prop('checked') && $sortable_pm.length) {
            $sortable_pm.show();
        } else {
            $sortable_pm.hide();
        }

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

    $pm_list_empty_block.on('click.leyka', function(e){

        $pm_list_empty_block.addClass('comment-displayed').find('.pm-list-empty-base-content').hide();
        $pm_list_empty_block.find('.pm-list-empty-comment').show();

    });

    $('.gateway-turn-off').click(function(e){

        e.preventDefault();

        // Emulate a change() checkboxes event manually, to lessen the ajax requests to update the PM order:
        $pm_available_list.filter(':checked').each(function(){

            let $pm_available_checkbox = $(this);

            $pm_available_checkbox.removeAttr('checked'); // Uncheck the active PM checkbox
            $('#pm-'+$pm_available_checkbox.prop('id')).hide(); // Hide a PM settings
            $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]').hide(); // Hide a PM sortable entry

        });

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

});

// Active recurring Gateways CRON job setup "option":
jQuery(document).ready(function($){

    let $active_recurring_cron_setup_field = $('.single-gateway-settings .active-recurring-on');

    if( !$active_recurring_cron_setup_field.length ) {
        return;
    }

    let $gateway_settings_wrapper = $active_recurring_cron_setup_field.parents('.gateway-settings'),
        $recurring_on_field = $gateway_settings_wrapper.find('.active-recurring-available input');

    $recurring_on_field.on('change.leyka', function(){

        if($recurring_on_field.prop('checked')) {
            $active_recurring_cron_setup_field.show();
        } else {
            $active_recurring_cron_setup_field.hide();
        }

    }).change();

});

// Yandex.Kassa old/new API options:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-yandex'),
        $new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});

// PayPal old/new API options:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-paypal'),
        $new_api_used = $gateway_settings.find('input[name="leyka_paypal_rest_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});

// PM list scroll in gateways cards:
jQuery(document).ready(function($){

    let icon_width = 40;

    if( !$('.gateways-cards-list').length ) {
        return;
    }

    function scroll_pm_icons_list($pm_icons_list, move_step) {

        let $movable_wrapper = $pm_icons_list.find('.pm-icons-wrapper'),
            $icons_container = $pm_icons_list.find('.pm-icons'),
            $icons_scroll = $pm_icons_list.find('.pm-icons-scroll'),
            current_left_offset = parseInt($.trim($movable_wrapper.css('left').replace('px', ''))),
            new_left_offset = current_left_offset - move_step;
        
        if(new_left_offset >= 0) {

            new_left_offset = 0;
            $pm_icons_list.find('.scroll-arrow.left').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.left').show();
        }
        
        if($icons_container.width() + new_left_offset <= $icons_scroll.width()) {

            new_left_offset = -($icons_container.width() - $icons_scroll.width());
            $pm_icons_list.find('.scroll-arrow.right').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }
        
        $movable_wrapper.css('left', String(new_left_offset) + 'px');

    }

    $('.gateway-card-supported-pm-list').each(function(){
        
        let $pm_icons_list = $(this);
        
        $(this).find('.scroll-arrow').click(function(){
            if($(this).hasClass('left')) {
                scroll_pm_icons_list( $pm_icons_list, -icon_width );
            } else {
                scroll_pm_icons_list( $pm_icons_list, icon_width );
            }
        });
        
        let $icons_container = $pm_icons_list.find('.pm-icons'),
            icons_width = icon_width * $icons_container.find('img').length;
        
        if(icons_width > $pm_icons_list.width()) {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }

    });
    
});

// MIXPLAT custom settings:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-mixplat'),
        $split_used = $gateway_settings.find('input[name="leyka_mixplat_split_enabled"]');

    if( !$gateway_settings.length || !$split_used.length ) {
        return;
    }

    $split_used.on('change.leyka', function(){

        if($split_used.prop('checked')) {
            $gateway_settings.find('.split').show();
        } else {
            $gateway_settings.find('.split').hide();
        }

    }).change();

});

jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-mixplat'),
        $test_mode_used = $gateway_settings.find('input[name="leyka_mixplat_test_mode"]');

    if( !$gateway_settings.length || !$test_mode_used.length ) {
        return;
    }

    $test_mode_used.on('change.leyka', function(){

        if($test_mode_used.prop('checked')) {
            $gateway_settings.find('.test_mode').show();
        } else {
            $gateway_settings.find('.test_mode').hide();
        }

    }).change();

});
