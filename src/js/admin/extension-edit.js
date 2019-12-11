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

/** @todo After debugging, move all the following code to the Extension own JS: */
jQuery(document).ready(function($){

    var $mainColorInput = $('input[name="leyka_support_packages_main_color"]');
    var $backgroundColorInput = $('input[name="leyka_support_packages_background_color"]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');
    var $captionColorInput = $('input[name="leyka_support_packages_caption_color"]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');
    var $textColorInput = $('input[name="leyka_support_packages_text_color"]').closest('.field-component.field').find('.leyka-setting-field.colorpicker');

    function leykaSetupGeneralColors(mainColorHex) {

        // console.log("mainColorHex:", mainColorHex);
        var mainColorHsl = leykaHex2Hsl(mainColorHex);
        // console.log("mainColorHsl:", mainColorHsl);

        var backgroundColorHsl = leykaMainHslColor2Background(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        // console.log("backgroundColorHsl:");
        // console.log(backgroundColorHsl);

        var backgroundColorHex = leykaHsl2Hex(backgroundColorHsl[0], backgroundColorHsl[1], backgroundColorHsl[2]);
        // console.log("backgroundColorHex:");
        // console.log(backgroundColorHex);
        $backgroundColorInput.wpColorPicker('color', backgroundColorHex);
        $captionColorInput.wpColorPicker('color', backgroundColorHex);

        var textColorHsl = leykaMainHslColor2Text(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        // console.log("textColorHsl:");
        // console.log(textColorHsl);

        var textColorHex = leykaHsl2Hex(textColorHsl[0], textColorHsl[1], textColorHsl[2]);
        // console.log("textColorHex:");
        // console.log(textColorHex);
        $textColorInput.wpColorPicker('color', textColorHex);

    }

    $mainColorInput.on('change', function(){
        leykaSetupGeneralColors($(this).val());
    });

});

// Support packages extension - custom field:
jQuery(document).ready(function($){

    let $packages_wrapper = $('.leyka-main-support-packages'),
        $package_template = $packages_wrapper.siblings('.package-template'),
        $add_package_button = $packages_wrapper.siblings('.add-package'),
        $order_field = $packages_wrapper.siblings('input[name="leyka_support_packages_order"]');

    $packages_wrapper.sortable({
        placeholder: 'ui-state-highlight', // A class for dropping item placeholder
        update: function(event, ui){
            console.log($packages_wrapper.sortable('toArray'));
            // TODO Update the order field & the super-option here
            // $order_field.val()
        }
    });

    $packages_wrapper.on('click.leyka', '.delete-package', function(e){

        e.preventDefault();

        if($packages_wrapper.find('.package-box').length > 1) {

            $(this).parents('.package-box').remove();
            $packages_wrapper.sortable('option', 'update')();

        }

        let packages_current_count = $packages_wrapper.find('.package-box').length;
        if(packages_current_count <= 1) {
            $packages_wrapper.find('.delete-package').addClass('inactive');
        }
        if(packages_current_count < 5) { /** @todo Get this hardcoded "5" value from the Extension var */
            $add_package_button.removeClass('inactive');
        }

    });
    $add_package_button.on('click.leyka', function(e){

        e.preventDefault();

        if($add_package_button.hasClass('inactive')) {
            return;
        }

        // Generate & set the new package ID:
        let new_package_id = '';
        do {
            new_package_id = leyka_get_random_string(4);
        } while($packages_wrapper.find('#package-'+new_package_id).length);

        $package_template
            .clone()
            .appendTo($packages_wrapper)
            .removeClass('package-template')
            .prop('id', 'package-'+new_package_id)
            .show();

        $packages_wrapper.sortable('option', 'update')();

        let packages_current_count = $packages_wrapper.find('.package-box').length;

        if(packages_current_count >= 5) { /** @todo Get this hardcoded "5" value from the Extension var */
            $add_package_button.addClass('inactive');
        }

        if(packages_current_count <= 1) { // When adding initial package box
            $packages_wrapper.find('.delete-package').addClass('inactive');
        } else if(packages_current_count > 1) {
            $packages_wrapper.find('.delete-package').removeClass('inactive');
        }

    });

    if( !$packages_wrapper.find('.package-box').length ) { // No packages added yet - add the first (empty) one
        $add_package_button.trigger('click.leyka');
    }

});
/** @todo Move to the Extension JS - END */