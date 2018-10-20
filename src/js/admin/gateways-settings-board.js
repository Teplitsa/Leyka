/** Gateways settings board */

// Payment settings page:
jQuery(document).ready(function($){

    if( !$('#payment-settings-area-new.stage-payment').length ) {
        return;
    }

    function leykaUpdatePmList($pm_order) {

        $.post(leyka.ajaxurl, {
            action: 'leyka_update_pm_order',
            pm_order: $pm_order_field.val(),
            nonce: $pm_order_field.data('nonce')
        }, null, 'json')
            .done(function(json) {

                if(typeof json.status !== 'undefined' && json.status === 'error') {
                    // alert('Ошибка!');
                    return;
                }

                alert('ok!');
                // show success div

            })
            .fail(function() {
                alert('Ошибка!');
            })
            .always(function() {
                // hideLoading();
                // enableForm();
            });

    }

    // var $gateways_accordion = $('#pm-settings-wrapper');
    // $gateways_accordion.accordion({
    //     heightStyle: 'content',
    //     header: '.gateway-settings > h3',
    //     collapsible: true,
    //     activate: function(event, ui){
    //
    //         var $header_clicked = $(this).find('.ui-state-active');
    //         if($header_clicked.length) {
    //             $('html, body').animate({ // 35px is a height of the WP admin bar:
    //                 scrollTop: $header_clicked.parent().offset().top - 35
    //             }, 250);
    //         }
    //     }
    // });
    //
    // $gateways_accordion.find('.doc-link').click(function(e){
    //     e.stopImmediatePropagation(); // Do not toggle the accordion panel when clicking on the docs link
    // });

    // PM reordering:
    var $pm_order = $('#pm-order-settings').sortable({placeholder: '', items: '> li:visible'});
    $pm_order.on('sortupdate', function(event){

        var $pm_order_field = $('input#pm-order-field');

        $pm_order_field.val(
            $(this).sortable('serialize', {key: 'pm_order[]', attribute: 'data-pm-id', expression: /(.+)/})
        );

        // disableForm();
        // showLoading();

        leykaUpdatePmList($pm_order_field);

    });

    // PM renaming & deactivation:
    $pm_order.on('click', '.pm-deactivate', function(e){

        // ...

        /** @todo AJAX to update PM list & labels */

    }).on('click', '.pm-change-label', function(e){

        e.preventDefault();

        var $this = $(this),
            $wrapper = $this.parents('li:first');

        $this.hide();
        $wrapper.find('.pm-label').hide();
        $wrapper.find('.pm-label-fields').show();

    }).on('click', '.new-pm-label-ok,.new-pm-label-cancel', function(e){

        e.preventDefault();

        var $this = $(this),
            $wrapper = $this.parents('li:first'),
            $pm_label_wrapper = $wrapper.find('.pm-label'),
            new_pm_label = $wrapper.find('input[id*="pm_label"]').val();

        if($this.hasClass('new-pm-label-ok')) {

            $pm_label_wrapper.text(new_pm_label);
            $wrapper.find('input.pm-label-field').val(new_pm_label);

            /** @todo AJAX to update PM list & labels */

        } else {
            $wrapper.find('input[id*="pm_label"]').val($pm_label_wrapper.text());
        }

        $pm_label_wrapper.show();
        $wrapper.find('.pm-label-fields').hide();
        $wrapper.find('.pm-change-label').show();

    }).on('keydown', 'input[id*="pm_label"]', function(e){

        var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        if(keycode == 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

            e.preventDefault();
            $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

        }

    });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    // $('.pm-active').click(function(){
    //
    //     var $this = $(this),
    //         $gateway_metabox = $this.parents('.postbox'),
    //         gateway_id = $gateway_metabox.attr('id').replace('leyka_payment_settings_gateway_', ''),
    //         $gateway_settings = $('#gateway-'+gateway_id);
    //
    //     // Show/hide a PM settings:
    //     $('#pm-'+$this.attr('id')).toggle();
    //
    //     var $sortable_pm = $('.pm-order[data-pm-id="'+$this.attr('id')+'"]');
    //
    //     // Add/remove a sortable block from the PM order settings:
    //     if($this.attr('checked')) {
    //
    //         if($sortable_pm.length) {
    //             $sortable_pm.show();
    //         } else {
    //
    //             $sortable_pm = $("<div />").append($pm_order.find('.pm-order[data-pm-id="#FID#"]').clone()).html()
    //                 .replace(/#FID#/g, $this.attr('id'))
    //                 .replace(/#L#/g, $this.data('pm-label'))
    //                 .replace(/#LB#/g, $this.data('pm-label-backend'));
    //             $sortable_pm = $($sortable_pm).removeAttr('style');
    //
    //             $pm_order.append($sortable_pm);
    //         }
    //     } else {
    //         $sortable_pm.hide();
    //     }
    //     $pm_order.sortable('refresh').sortable('refreshPositions');
    //     $pm_order.trigger('sortupdate');
    //
    //     // Show/hide a whole gateway settings if there are no PMs from it selected:
    //     if( !$gateway_metabox.find('input:checked').length ) {
    //
    //         $gateway_settings.hide();
    //         $gateways_accordion.accordion('refresh');
    //
    //     } else if( !$gateway_settings.is(':visible') ) {
    //
    //         $gateway_settings.show();
    //         $gateways_accordion.accordion('refresh');
    //
    //         $sortable_pm.show();
    //         $pm_order.sortable('refresh').sortable('refreshPositions');
    //         $pm_order.trigger('sortupdate');
    //     }
    // });

});

// Yandex.Kassa settings:
jQuery(document).ready(function($){

    var $gateway_settings = $('#payment-settings-area').find('#gateway-yandex');

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