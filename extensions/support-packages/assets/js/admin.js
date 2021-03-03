/** Support Packages extension - admin JS */

jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if(
        !$admin_page_wrapper.length
        || !$admin_page_wrapper.hasClass('extension-settings')
        || $admin_page_wrapper.data('leyka-extension-id') !== 'support_packages'
    ) {
        return;
    }

    let LEYKA_EXT_AUTO_CALC_COLORS = false,
        $mainColorInput = $admin_page_wrapper.find('.extension-color-options input[name$="_main_color"]'),
        $backgroundColorInput = $admin_page_wrapper.find('.extension-color-options input[name$="_background_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $captionColorInput = $admin_page_wrapper.find('.extension-color-options input[name$="_caption_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $textColorInput = $admin_page_wrapper.find('.extension-color-options input[name$="_text_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker');

    $mainColorInput.closest('.field-component')
        .find('.leyka-setting-field.colorpicker').data('stored-color', $mainColorInput.val());
    $backgroundColorInput.data('stored-color', $backgroundColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());
    $captionColorInput.data('stored-color', $captionColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());
    $textColorInput.data('stored-color', $textColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());

    function leykaSetupGeneralColors(mainColorHex) {

        let mainColorHsl = leykaHex2Hsl(mainColorHex),
            backgroundColorHsl = leykaMainHslColor2Background(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]),
            backgroundColorHex = leykaHsl2Hex(backgroundColorHsl[0], backgroundColorHsl[1], backgroundColorHsl[2]);

        LEYKA_EXT_AUTO_CALC_COLORS = true;

        if( !$backgroundColorInput.data('changed') ) {
            $backgroundColorInput.wpColorPicker('color', backgroundColorHex);
        }

        if( !$captionColorInput.data('changed') ) {
            $captionColorInput.wpColorPicker('color', backgroundColorHex);
        }

        let textColorHsl = leykaMainHslColor2Text(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]),
            textColorHex = leykaHsl2Hex(textColorHsl[0], textColorHsl[1], textColorHsl[2]);

        if( !$textColorInput.data('changed') ) {
            $textColorInput.wpColorPicker('color', textColorHex);
        }

        LEYKA_EXT_AUTO_CALC_COLORS = false;

    }

    $mainColorInput.on('change.leyka', function(){
        leykaSetupGeneralColors($(this).val());
    });

    $backgroundColorInput.closest('.field-component').find('.leyka-colorpicker-value').on('change.leyka', function(){
        if(!LEYKA_EXT_AUTO_CALC_COLORS) {
            $(this).closest('.field-component').find('.leyka-setting-field.colorpicker').data('changed', '1');
        }
    });

    $captionColorInput.closest('.field-component').find('.leyka-colorpicker-value').on('change.leyka', function(){
        if(!LEYKA_EXT_AUTO_CALC_COLORS) {
            $(this).closest('.field-component').find('.leyka-setting-field.colorpicker').data('changed', '1');
        }
    });

    $textColorInput.closest('.field-component').find('.leyka-colorpicker-value').on('change.leyka', function(){
        if(!LEYKA_EXT_AUTO_CALC_COLORS) {
            $(this).closest('.field-component').find('.leyka-setting-field.colorpicker').data('changed', '1');
        }
    });

    let $colorOptionsBlock = $('.settings-block.extension-color-options');
    $colorOptionsBlock.append( $('<div class="color-actions"><a href="#" class="reset-colors"><span>'+leyka.extension_colors_reset+'</span></a><a href="#" class="unlock-changes"><span>'+leyka.extension_colors_make_change+'</span></a></div>') );

    $colorOptionsBlock.find('.leyka-colorpicker-field-wrapper').each(function(){
        $(this).append('<div class="leyka-colorpicker-field-overlay"></div>');
    });

    $colorOptionsBlock.on('click', '.unlock-changes', function(e){
        e.preventDefault();
        $colorOptionsBlock.toggleClass('changes-unlocked');
    });

    $colorOptionsBlock.on('click', '.reset-colors', function(e){
        e.preventDefault();

        $backgroundColorInput.data('changed', '');
        $captionColorInput.data('changed', '');
        $textColorInput.data('changed', '');

        $mainColorInput.change();

    });

    $colorOptionsBlock.on('click', 'leyka-colorpicker-field-overlay', function(e){
        e.stopPropagation();
    });

});