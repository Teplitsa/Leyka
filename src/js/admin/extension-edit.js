/** Extension settings (edit page) JS. */

jQuery(document).ready(function($){

    $('.delete-extension-link').click(function(e){

        e.preventDefault();

        let $delete_link = $(this),
            $ajax_loading = $delete_link.find('.loading-indicator-wrap'),
            $error = $delete_link.siblings('.delete-extension-error');

        if(confirm(leyka.extension_deletion_confirm_text)) {

            $ajax_loading.show();
            $error.html('').hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_delete_extension',
                extension_id: $delete_link.data('extension-id'),
                nonce: $delete_link.data('nonce'),
            }, function(response){

                $ajax_loading.hide();
                if(
                    typeof response === 'undefined'
                    || typeof response.status === 'undefined'
                    || (response.status !== 0 && typeof response.message === 'undefined')
                ) {
                    return $error.html(leyka.common_error_message).show();
                } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                    return $error.html(response.message).show();
                }

                window.location.href = leyka.extensions_list_page_url+'&extension-deleted=1';

            }, 'json');

        }


    });

});

jQuery(document).ready(function($){
    var $mainColorInput = $('input[name=leyka_support_packages_main_color]');
    var $backgroundColorInput = $('input[name=leyka_support_packages_background_color]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');
    var $captionColorInput = $('input[name=leyka_support_packages_caption_color]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');
    var $textColorInput = $('input[name=leyka_support_packages_text_color]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');

    function leykaSetupGeneralColors(mainColorHex) {
        console.log("mainColorHex:", mainColorHex);
        var mainColorHsl = leykaHex2Hsl(mainColorHex);
        console.log("mainColorHsl:", mainColorHsl);

        var backgroundColorHsl = leykaMainHslColor2Background(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        console.log("backgroundColorHsl:");
        console.log(backgroundColorHsl);

        var backgroundColorHex = leykaHsl2Hex(backgroundColorHsl[0], backgroundColorHsl[1], backgroundColorHsl[2]);
        console.log("backgroundColorHex:");
        console.log(backgroundColorHex);
        $backgroundColorInput.wpColorPicker('color', backgroundColorHex);
        $captionColorInput.wpColorPicker('color', backgroundColorHex);

        var textColorHsl = leykaMainHslColor2Text(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        console.log("textColorHsl:");
        console.log(textColorHsl);

        var textColorHex = leykaHsl2Hex(textColorHsl[0], textColorHsl[1], textColorHsl[2]);
        console.log("textColorHex:");
        console.log(textColorHex);
        $textColorInput.wpColorPicker('color', textColorHex);
    }

    $mainColorInput.on('change', function(){
        leykaSetupGeneralColors($(this).val());
    });
});
