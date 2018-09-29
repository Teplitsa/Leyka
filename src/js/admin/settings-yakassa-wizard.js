// Yandex Kassa shopPassword generator:
jQuery(document).ready(function($){

    var $genBtn = $('#yakassa-generate-shop-password');
    
    if(!$genBtn.length) {
        return;
    }
    
    var $stepSubmit = $('.step-submit');
    $stepSubmit.hide();
    
    $genBtn.click(function(){
        var password = makePassword(10);
        var $block = $genBtn.closest('.enum-separated-block');
        $genBtn.hide();
        $block.find('.caption').css('display', 'unset');
        $block.find('.body b').css('display', 'unset').text(password);
        $block.find('input[name=leyka_yandex_shop_password]').val(password);
        $stepSubmit.show();
    });

});
// Yandex Kassa shopPassword generator - END