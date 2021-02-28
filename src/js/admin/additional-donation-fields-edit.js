/** Additional donation fields settings JS */

jQuery(document).ready(function($){

    let $additional_fields_settings_wrapper = $('.leyka-admin .additional-fields-settings');
    if( !$additional_fields_settings_wrapper.length || !leyka_ui_widget_available('sortable') ) {
        return;
    }

    let $fields_wrapper = $('.leyka-main-additional-fields'),
        $field_template = $fields_wrapper.siblings('.field-template'),
        $add_field_button = $fields_wrapper.siblings('.add-common-field'),
        closed_boxes = typeof $.cookie('leyka-common-additional-fields-boxes-closed') === 'string' ?
            JSON.parse($.cookie('leyka-common-additional-fields-boxes-closed')) : [];

    if($.isArray(closed_boxes)) { // Close the item boxes needed
        $.each(closed_boxes, function(key, value){
            $fields_wrapper.find('#'+value).addClass('closed');
        });
    }

    $fields_wrapper.on('click.leyka', 'h2.hndle', function(e){

        let $this = $(this),
            $current_box = $this.parents('.field-box');

        $current_box.toggleClass('closed');

        // Save the open/closed state for all items boxes:
        let current_box_id = $current_box.prop('id'),
            current_box_index = $.inArray(current_box_id, closed_boxes);

        if(current_box_index === -1 && $current_box.hasClass('closed')) {
            closed_boxes.push(current_box_id);
        } else if(current_box_index !== -1 && !$current_box.hasClass('closed')) {
            closed_boxes.splice(current_box_index, 1);
        }

        $.cookie('leyka-common-additional-fields-boxes-closed', JSON.stringify(closed_boxes));

    });

    // Change field box title when field title value changes:
    $fields_wrapper.on('keyup.leyka change.leyka click.leyka', '[name="leyka_field_title"]', function(){

        let $field_title = $(this),
            $box_title = $field_title.parents('.multi-valued-item-box').find('h2.hndle .title');

        if($field_title.val().length) {
            $box_title.html($field_title.val());
        } else {
            $box_title.html($box_title.data('empty-box-title'));
        }

    });

    $fields_wrapper.sortable({
        placeholder: 'ui-state-highlight', // A class for dropping item placeholder
        update: function(event, ui){

            let additional_fields_options = [];
            $.each($fields_wrapper.sortable('toArray'), function(key, field_id){ // Value is a field ID (generated randomly)

                let field_options = {'id': field_id}; // Assoc. array key should be initialized explicitly

                $.each($fields_wrapper.find('#'+field_id).find(':input'), function(key, field_setting_input){

                    let $input = $(field_setting_input);

                    if($input.prop('type') === 'checkbox') {
                        field_options[ $input.prop('name').replace('leyka_field_', '') ] = $input.prop('checked');
                    } else {
                        field_options[ $input.prop('name').replace('leyka_field_', '') ] = $input.val();
                    }

                });

                additional_fields_options.push(field_options);

            });

            $fields_wrapper.siblings('input#leyka-common-additional-fields-options').val(
                encodeURIComponent(JSON.stringify(additional_fields_options))
            );

        }
    });

    // Delete item:
    $fields_wrapper.on('click.leyka', '.delete-additional-field', function(e){

        e.preventDefault();

        if($fields_wrapper.find('.field-box').length > 1) {

            $(this).parents('.field-box').remove();
            $fields_wrapper.sortable('option', 'update')();

        }

        let additional_fields_current_count = $fields_wrapper.find('.field-box').length;
        if(additional_fields_current_count <= 1) {
            $fields_wrapper.find('.delete-additional-field').addClass('inactive');
        }
        if(additional_fields_current_count < $fields_wrapper.data('max-additional-fields')) {
            $add_field_button.removeClass('inactive');
        }

    });

    // Add new item:
    $add_field_button.on('click.leyka', function(e){

        e.preventDefault();

        if($add_field_button.hasClass('inactive')) {
            return;
        }

        // Generate & set the new field ID:
        let new_additional_field_id = '';
        do {
            new_additional_field_id = leyka_get_random_string(4);
        } while($fields_wrapper.find('#field-'+new_additional_field_id).length);

        $field_template
            .clone()
            .appendTo($fields_wrapper)
            .removeClass('field-template')
            .prop('id', 'field-'+new_additional_field_id)
            .show();

        $fields_wrapper.sortable('option', 'update')();

        let additional_fields_current_count = $fields_wrapper.find('.field-box').length;

        if(additional_fields_current_count >= $fields_wrapper.data('max-additional-fields')) {
            $add_field_button.addClass('inactive');
        }

        if(additional_fields_current_count <= 1) { // When adding initial field box
            $fields_wrapper.find('.delete-additional-field').addClass('inactive');
        } else if(additional_fields_current_count > 1) {
            $fields_wrapper.find('.delete-additional-field').removeClass('inactive');
        }

    });

    if( !$fields_wrapper.find('.field-box').length ) { // No items added yet - add the first (empty) one
        $add_field_button.trigger('click.leyka');
    }

    // Pre-submit actions:
    $fields_wrapper.parents('form:first').on('submit.leyka', function(){

        $fields_wrapper.sortable('option', 'update')(); // Refresh the main items option value before submit

        // Validation:
        if( !leyka_all_fields_valid($fields_wrapper) ) {
            e.preventDefault();
        }

    });

});