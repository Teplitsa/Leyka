/** Gateways settings board */

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