jQuery(document).ready(function($){

    // Merchandise Extension settings page:
    if( !$('#leyka_merchandise-merchandise_library').length ) {
        return;
    }

    let $items_wrapper = $('.multi-valued-items-field-wrapper');

    // Pre-submit actions:
    $items_wrapper.parents('form:first').on('submit.leyka', function(e){

        // Validation:
        if( !leyka_all_item_sub_fields_valid($items_wrapper) ) {
            e.preventDefault();
        }

    });

    // Validate the multi-valued items complex field:
    function leyka_all_item_sub_fields_valid($fields_wrapper) {

        let all_fields_valid = true;

        $fields_wrapper.find('.multi-valued-item-box:not(.item-template)').each(function(index, item_box){

            let $item_box = $(item_box),
                $box_errors_list = $item_box.find('.notes-and-errors');

            $box_errors_list.find('.error').remove();

            let $item_sub_field = $item_box.find('[name="leyka_merchandise_title"]'),
                $item_sub_field_outer_wrapper = $item_sub_field.parents('.option-block').removeClass('has-errors');

            // Merch title is empty:
            if( !$item_sub_field.val().length ) {

                all_fields_valid = false;
                $item_sub_field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $item_sub_field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

            $item_sub_field = $item_box.find('[name="leyka_merchandise_donation_amount_needed"]');
            $item_sub_field_outer_wrapper = $item_sub_field.parents('.option-block').removeClass('has-errors');

            // Merchandise minimal amount is empty or non-positive:
            if( !$item_sub_field.val().length || $item_sub_field.val() <= 0 ) {

                all_fields_valid = false;
                $item_sub_field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $item_sub_field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

        });

        return all_fields_valid;

    }

});