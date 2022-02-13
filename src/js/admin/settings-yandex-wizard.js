// YooKassa shopPassword generator:
jQuery(document).ready(function($){

    var $genBtn = $('#yandex-generate-shop-password');
    
    if( !$genBtn.length ) {
        return;
    }
    
    var $stepSubmit = $('.step-submit');
    $stepSubmit.hide();
    
    $genBtn.click(function(){

        var password = leyka_make_password(10),
            $block = $genBtn.closest('.enum-separated-block');

        $genBtn.hide();
        $block.find('.caption').css('display', 'unset');
        $block.find('.body b').css('display', 'unset').text(password);
        $block.find('input[name=leyka_yandex_shop_password]').val(password);
        $stepSubmit.show();
        
        $(this).closest('.body').removeClass('no-password');

    });

});
// YooKassa shopPassword generator - END

// YooKassa payment tryout:
jQuery(document).ready(function($){

    let $gen_btn = $('#yandex-make-live-payment'),
        $loading = $('.yandex-make-live-payment-loader');

    if( !$gen_btn.length ) {
        return;
    }

    leykaYandexPaymentData.leyka_success_page_url = window.location.href;
    leykaYandexPaymentData.leyka_is_gateway_tryout = 1;

    $gen_btn.click(function(){

        $loading.show();
        $gen_btn.prop('disabled', true);

        $.post(leyka.ajaxurl, leykaYandexPaymentData, null, 'json')
            .done(function(json) {

                if(typeof json.status === 'undefined') {
                    alert('Ошибка!');
                } else if(json.status === 0 && json.payment_url) {
                    window.location.href = json.payment_url;
                } else {
                    alert('Ошибка!');
                }

            }).fail(function(){
                alert('Ошибка!');
            }).always(function(){
                $loading.hide();
                $gen_btn.prop('disabled', false);
            });
            
    });

});
// Yandex.Kassa payment tryout - END