/** Additional donation form fields settings JS */

jQuery(document).ready(function($){

    let $additional_fields_settings_wrapper = $('.leyka-admin .additional-fields-settings');
    if( !$additional_fields_settings_wrapper.length || !leyka_ui_widget_available('sortable') ) {
        return;
    }

    let $items_wrapper = $additional_fields_settings_wrapper.find('.leyka-main-multi-items');
        // $add_item_button = $items_wrapper.siblings('.add-item');

    // Change field box title when field title value changes:
    $items_wrapper.on('keyup.leyka change.leyka click.leyka', '[name="leyka_field_title"]', function(){

        let $field_title = $(this),
            $box_title = $field_title.parents('.multi-valued-item-box').find('h2.hndle .title');

        if($field_title.val().length) {
            $box_title.html($field_title.val());
        } else {
            $box_title.html($box_title.data('empty-box-title'));
        }

    });

    // Display/hide the phone field note if field type is changed to/from "phone":
    $items_wrapper.on('change.leyka', '[name="leyka_field_type"]', function(e){

        let $type_field = $(this),
            $phone_note = $type_field.parents('.box-content').find('.phone-field-note');

        if($type_field.val() === 'phone') {
            $phone_note.show();
        } else {
            $phone_note.hide();
        }

    });

    // Pre-submit actions:
    $items_wrapper.parents('form:first').on('submit.leyka', function(e){

        // Validation:
        if( !leyka_all_fields_valid($items_wrapper) ) {
            e.preventDefault();
        }

    });

    // Validate the multi-blocked complex field:
    function leyka_all_fields_valid($fields_wrapper) {

        let fields_valid = true;

        $fields_wrapper.find('.multi-valued-item-box').each(function(index, item_box){

            let $fields_box = $(item_box),
                $box_errors_list = $fields_box.find('.notes-and-errors');

            $box_errors_list.find('.error').remove();

            let $field = $fields_box.find('[name="leyka_field_type"]'),
                $field_outer_wrapper = $field.parents('.option-block');

            $field_outer_wrapper.removeClass('has-errors');

            // Field type isn't selected:
            if( !$field.val().length || $field.val() === '-' ) {

                fields_valid = false;
                $field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

            $field = $fields_box.find('[name="leyka_field_title"]');
            $field_outer_wrapper = $field.parents('.option-block').removeClass('has-errors');

            // Field title isn't entered:
            if( !$field.val().length ) {

                fields_valid = false;
                $field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

        });

        return fields_valid;

    }

});