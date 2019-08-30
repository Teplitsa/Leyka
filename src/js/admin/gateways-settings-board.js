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
        $ajax_loading = $pm_update_status.find('.leyka-loader'),
        $pm_list_empty_block = $('.pm-list-empty');

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

            leykaUpdatePmList($pm_order);

            if($pm_order.find('.pm-order:visible').length) {
                $pm_list_empty_block.hide();
            } else {
                $pm_list_empty_block.show();
            }

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
            if(keycode === 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

                e.preventDefault();
                $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

            }

        });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    $pm_available_list.change(function(){

        var $pm_available_checkbox = $(this);

        $('#pm-'+$pm_available_checkbox.prop('id')).toggle(); // Show/hide a PM settings
        $('#'+$pm_available_checkbox.prop('id')+'-commission-wrapper').toggle(); // Show/hide a PM commission field

        var $sortable_pm = $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]');

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

            var $pm_available_checkbox = $(this);

            $pm_available_checkbox.removeAttr('checked'); // Uncheck the active PM checkbox
            $('#pm-'+$pm_available_checkbox.prop('id')).hide(); // Hide a PM settings
            $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]').hide(); // Hide a PM sortable entry

        });

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

});

// Yandex.Kassa old/new API options:
jQuery(document).ready(function($){

    var $gateway_settings = $('.single-gateway-settings.gateway-yandex'),
        $new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        var $smart_payment_pm_field = $('.gateway-pm-list').find(':input.pm-available[value="yandex-yandex_all"]');

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

            if($smart_payment_pm_field.length) {

                if($smart_payment_pm_field.prop('checked')) {

                    $smart_payment_pm_field.prop('checked', false).change();
                    $new_api_used.data('yandex-all-pm-removed', true);

                }

                $('.settings-block#yandex-yandex_all').hide();

            }

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

            $('.settings-block#yandex-yandex_all').show();

            if($new_api_used.data('yandex-all-pm-removed')) {

                $new_api_used.data('yandex-all-pm-removed', false);
                $smart_payment_pm_field.prop('checked', true).change();

            }

        }

    }).change();

});

// PayPal old/new API options:
jQuery(document).ready(function($){

    var $gateway_settings = $('.single-gateway-settings.gateway-paypal'),
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

// Filter gateways:
jQuery(document).ready(function($){

    var $filter = $('.leyka-gateways-filter'),
        $gatewaysList = $('.gateways-cards-list'),
        gatewaysFilter = {};

    $filter.find('.filter-toggle').click(function(){
        $(this).closest('.filter-area').toggleClass('show');
    });

    $filter.find('.filter-category-show-filter').click(function(e){
        e.preventDefault();
        $(this).closest('.filter-area').toggleClass('show');
    });

    $filter.find('.filter-category-reset-filter').click(function(e){
        e.preventDefault();
        resetFilter();
    });

    $filter.find('.filter-category-item').click(function(e){
        e.preventDefault();
        toggleFilterItem($(this));
        applyFilter();
    });

    function resetFilter() {
        gatewaysFilter = {};
        $filter.find('.filter-category-item').removeClass('active');
        applyFilter();
    }

    function applyFilter() {
        if(Object.keys(gatewaysFilter).length) {
            $gatewaysList.find('.gateway-card').hide();
            $gatewaysList.find('.gateway-card.' + Object.keys(gatewaysFilter).join(".")).show();
        } else {
            $gatewaysList.find('.gateway-card').show();
        }
    }

    function toggleFilterItem($filterItem) {

        $filterItem.toggleClass('active');
        
        if($filterItem.hasClass('active')) {
            gatewaysFilter[$filterItem.data('category')] = true;
        } else {
            delete gatewaysFilter[$filterItem.data('category')];
        }

    }

});

// PM list scroll in gateways cards:
jQuery(document).ready(function($){

    var iconWidth = 40;

    if( !$('.gateways-cards-list').length ) {
        return;
    }

    function scrollPMIconsList($pmIconsList, moveStep) {

        var $movableWrapper = $pmIconsList.find('.pm-icons-wrapper');
        var $iconsContainer = $pmIconsList.find('.pm-icons');
        var $iconsScroll = $pmIconsList.find('.pm-icons-scroll');
        
        var currentLeftOffset = parseInt($.trim($movableWrapper.css('left').replace('px', '')));
        var newLeftOffset = currentLeftOffset - moveStep;
        
        if(newLeftOffset >= 0) {
            newLeftOffset = 0;
            $pmIconsList.find('.scroll-arrow.left').hide();
        } else {
            $pmIconsList.find('.scroll-arrow.left').show();
        }
        
        if($iconsContainer.width() + newLeftOffset <= $iconsScroll.width()) {
            newLeftOffset = -($iconsContainer.width() - $iconsScroll.width());
            $pmIconsList.find('.scroll-arrow.right').hide();
        } else {
            $pmIconsList.find('.scroll-arrow.right').show();
        }
        
        $movableWrapper.css('left', String(newLeftOffset) + 'px');

    }

    $('.gateway-card-supported-pm-list').each(function(){
        
        var $pmIconsList = $(this);
        
        $(this).find('.scroll-arrow').click(function(){
            if($(this).hasClass('left')) {
                scrollPMIconsList( $pmIconsList, -iconWidth );
            } else {
                scrollPMIconsList( $pmIconsList, iconWidth );
            }
        });
        
        var $iconsContainer = $pmIconsList.find('.pm-icons');
        var iconsWidth = iconWidth * $iconsContainer.find('img').length;
        
        if(iconsWidth > $pmIconsList.width()) {
            $pmIconsList.find('.scroll-arrow.right').show();
        }

    });
    
});
