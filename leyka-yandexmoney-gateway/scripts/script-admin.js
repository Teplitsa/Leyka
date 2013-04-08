(function($){

$(document).ready(function(){
    $('body').on('change.yamo_receiver_is_private', ':radio[id*=yamo_receiver_is_private]', function(e){
        var $this = $(this);
        if($this.val() == 1) {
            $('input[name*="yamo_legal_"]').parents('tr').hide();
            $('input[name*="yamo_private_"]').parents('tr').show();
        } else {
            $('input[name*="yamo_legal_"]').parents('tr').show();
            $('input[name*="yamo_private_"]').parents('tr').hide();
        }
    });

    // Initial fields state:
    var $receiver_type = $(':radio[id*=yamo_receiver_is_private]:checked');
    if($receiver_type.length == 0) {
        $('input[name*="yamo_legal_"]').parents('tr').hide();
        $('input[name*="yamo_private_"]').parents('tr').hide();
    } else if($receiver_type.val() == 1) {
        $('input[name*="yamo_legal_"]').parents('tr').hide();
        $('input[name*="yamo_private_"]').parents('tr').show();
    } else {
        $('input[name*="yamo_legal_"]').parents('tr').show();
        $('input[name*="yamo_private_"]').parents('tr').hide();
    }
});

})(jQuery);