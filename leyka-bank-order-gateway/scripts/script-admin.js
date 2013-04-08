jQuery(document).ready(function($){
    $('body').on('change.bankOrderDocument', 'input[name*=bank_order_document]', function(e){
        var $this = $(this);

        if($this.val() == 'default') {
            $('input[name*=bank_order_file]').parents('tr').hide();
            $('textarea[name*=bank_order_custom_html]').parents('tr').hide();
            $('input[name*=bank_order_ess_]').parents('tr').show();
        } else if($this.val() == 'file') {
            $('input[name*=bank_order_file]').parents('tr').show();
            $('textarea[name*=bank_order_custom_html]').parents('tr').hide();
            $('input[name*=bank_order_ess_]').parents('tr').hide();
        } else {
            $('input[name*=bank_order_file]').parents('tr').hide();
            $('textarea[name*=bank_order_custom_html]').parents('tr').show();
            $('input[name*=bank_order_ess_]').parents('tr').show();
        }
    });

    // Init fields state:
    var $bank_order_source = $(document).find(':radio[name*=bank_order_document]:checked').val();
    if($bank_order_source == '') {
        $('input[name*=bank_order_file]').parents('tr').hide();
        $('textarea[name*=bank_order_custom_html]').parents('tr').hide();
        $('input[name*=bank_order_ess_]').parents('tr').hide();
    } else if($bank_order_source == 'default') {
        $('input[name*=bank_order_file]').parents('tr').hide();
        $('textarea[name*=bank_order_custom_html]').parents('tr').hide();
        $('input[name*=bank_order_ess_]').parents('tr').show();
    } else if($bank_order_source == 'file') {
        $('input[name*=bank_order_file]').parents('tr').show();
        $('textarea[name*=bank_order_custom_html]').parents('tr').hide();
        $('input[name*=bank_order_ess_]').parents('tr').hide();
    } else {
        $('input[name*=bank_order_file]').parents('tr').hide();
        $('textarea[name*=bank_order_custom_html]').parents('tr').show();
        $('input[name*=bank_order_ess_]').parents('tr').show();
    }
});