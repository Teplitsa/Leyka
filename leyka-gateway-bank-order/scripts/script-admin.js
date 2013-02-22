jQuery(document).ready(function($){
    var $use_file_checkbox = $('input:checkbox[id*="bank_order_use_file"]'),
        $file_field_block = $('input[id*="bank_order_file"]').parents('tr:first');

    $use_file_checkbox.change(function(){
        var $this = $(this);
        if($this.attr('checked')) {
            $('[id*="edd_settings_gateways\[bank_order_"]').attr('disabled', 'disabled');
            $file_field_block.find('[id*="bank_order_file"]').removeAttr('disabled');
            $use_file_checkbox.removeAttr('disabled');
            $file_field_block.show();
        } else {
            $('[id*="edd_settings_gateways\[bank_order_"]').removeAttr('disabled');
            $file_field_block.hide();
        }
    });
    if($use_file_checkbox.attr('checked'))
        $use_file_checkbox.change();
    else
        $file_field_block.hide();
});