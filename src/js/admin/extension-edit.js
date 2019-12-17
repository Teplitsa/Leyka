/** Extension settings (edit page) JS. */

jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if( !$admin_page_wrapper.length || !$admin_page_wrapper.hasClass('extension-settings') ) {
        return;
    }

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

    let $admin_page_wrapper = $('.leyka-admin');
    if(
        !$admin_page_wrapper.length
        || !$admin_page_wrapper.hasClass('extension-settings')
        || $admin_page_wrapper.data('leyka-extension-id') !== 'support_packages'
        || !leyka_ui_widget_available('sortable')
    ) {
        return;
    }

    var LEYKA_EXT_AUTO_CALC_COLORS = false;
    var $mainColorInput = $('input[name="leyka_support_packages_main_color"]'),
        $backgroundColorInput = $('input[name="leyka_support_packages_background_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $captionColorInput = $('input[name="leyka_support_packages_caption_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $textColorInput = $('input[name="leyka_support_packages_text_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker');

    $mainColorInput.closest('.field-component').find('.leyka-setting-field.colorpicker').data('stored-color', $mainColorInput.val());
    $backgroundColorInput.data('stored-color', $backgroundColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());
    $captionColorInput.data('stored-color', $captionColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());
    $textColorInput.data('stored-color', $textColorInput.closest('.field-component').find('.leyka-colorpicker-value').val());

    function leykaSetupGeneralColors(mainColorHex) {
        let mainColorHsl = leykaHex2Hsl(mainColorHex);

        let backgroundColorHsl = leykaMainHslColor2Background(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        let backgroundColorHex = leykaHsl2Hex(backgroundColorHsl[0], backgroundColorHsl[1], backgroundColorHsl[2]);

        LEYKA_EXT_AUTO_CALC_COLORS = true;
        if(!$backgroundColorInput.data('changed')) {
            $backgroundColorInput.wpColorPicker('color', backgroundColorHex);
        }

        if(!$captionColorInput.data('changed')) {
            $captionColorInput.wpColorPicker('color', backgroundColorHex);
        }

        let textColorHsl = leykaMainHslColor2Text(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        let textColorHex = leykaHsl2Hex(textColorHsl[0], textColorHsl[1], textColorHsl[2]);
        
        if(!$textColorInput.data('changed')) {
            $textColorInput.wpColorPicker('color', textColorHex);
        }
        LEYKA_EXT_AUTO_CALC_COLORS = false;
    }

    $mainColorInput.on('change.leyka', function(){
        leykaSetupGeneralColors($(this).val());
    });

    console.log($backgroundColorInput);

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

    var $colorOptionsBlock = $('.settings-block.support-packages-color-options');
    var $colorActions = $('<div class="color-actions"><a href="#" class="reset-colors"><span>'+leyka.extension_colors_reset+'</span></a><a href="#" class="unlock-changes"><span>'+leyka.extension_colors_make_change+'</span></a></div>');
    $colorOptionsBlock.append($colorActions);

    $colorOptionsBlock.find('.leyka-colorpicker-field-wrapper').each(function(){
        $(this).append('<div class="leyka-colorpicker-field-overlay"/>');
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
        // $mainColorInputPicker = $mainColorInput.closest('.field-component').find('.leyka-setting-field.colorpicker');
        // $mainColorInputPicker.wpColorPicker('color', $mainColorInputPicker.data('stored-color'));
    });

    $colorOptionsBlock.on('click', 'leyka-colorpicker-field-overlay', function(e){
        e.stopPropagation();
    });

});

// Support packages extension - custom field:
jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if(
        !$admin_page_wrapper.length
        || !$admin_page_wrapper.hasClass('extension-settings')
        || $admin_page_wrapper.data('leyka-extension-id') !== 'support_packages'
        || !leyka_ui_widget_available('sortable')
    ) {
        return;
    }

    let $packages_wrapper = $('.leyka-main-support-packages'),
        $package_template = $packages_wrapper.siblings('.package-template'),
        $add_package_button = $packages_wrapper.siblings('.add-package'),
        closed_boxes = typeof $.cookie('leyka-support-packages-boxes-closed') === 'string' ?
            JSON.parse($.cookie('leyka-support-packages-boxes-closed')) : [];

    if($.isArray(closed_boxes)) { // Close the package boxes needed
        $.each(closed_boxes, function(key, value){
            $packages_wrapper.find('#'+value).addClass('closed');
        });
    }

    $packages_wrapper.on('click.leyka', 'h2.hndle', function(e){

        let $this = $(this),
            $current_box = $this.parents('.package-box');

        $current_box.toggleClass('closed');

        // Save the open/closed state for all packages boxes:
        let current_box_id = $current_box.prop('id'),
            current_box_index = $.inArray(current_box_id, closed_boxes);

        if(current_box_index === -1 && $current_box.hasClass('closed')) {
            closed_boxes.push(current_box_id);
        } else if(current_box_index !== -1 && !$current_box.hasClass('closed')) {
            closed_boxes.splice(current_box_index, 1);
        }

        $.cookie('leyka-support-packages-boxes-closed', JSON.stringify(closed_boxes));

    });

    $packages_wrapper.sortable({
        placeholder: 'ui-state-highlight', // A class for dropping item placeholder
        update: function(event, ui){

            let packages_options = [];
            $.each($packages_wrapper.sortable('toArray'), function(key, package_id){ // Value is a package ID

                let package_options = {'id': package_id}; // Assoc. array key should be initialized explicitly

                $.each($packages_wrapper.find('#'+package_id).find(':input').serializeArray(), function(key, package_field){
                    package_options[ package_field.name.replace('leyka_package_', '') ] = package_field.value;
                });

                packages_options.push(package_options);

            });

            $packages_wrapper.siblings('input#leyka-support-packages-options').val(
                encodeURIComponent(JSON.stringify(packages_options))
            );

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
        if(packages_current_count < $packages_wrapper.data('max-packages')) {
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

        if(packages_current_count >= $packages_wrapper.data('max-packages')) {
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

    // Refresh the main packages option value before submit:
    $packages_wrapper.parents('.leyka-options-form').on('submit.leyka', function(){
        $packages_wrapper.sortable('option', 'update')();
    });

});
/** @todo Move to the Extension JS - END */