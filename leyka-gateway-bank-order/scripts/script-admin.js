jQuery(document).ready(function($){
    var $use_file_checkbox = $('input:checkbox[id*="quittance_use_file"]'),
        $file_field_block = $('input[id*="quittance_file"]').parents('tr:first');

    $use_file_checkbox.change(function(){
        var $this = $(this);
        if($this.attr('checked')) {
            $('[id*="edd_settings_gateways\[quittance_"]').attr('disabled', 'disabled');
            $file_field_block.find('[id*="quittance_file"]').removeAttr('disabled');
            $use_file_checkbox.removeAttr('disabled');
            $file_field_block.show();
        } else {
            $('[id*="edd_settings_gateways\[quittance_"]').removeAttr('disabled');
            $file_field_block.hide();
        }
    });
    if($use_file_checkbox.attr('checked'))
        $use_file_checkbox.change();
    else
        $file_field_block.hide();
});