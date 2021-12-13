jQuery(document).ready(function($){

    let $items_wrapper = $('.leyka-campaign-merchandise-wrapper'); // Campaign edit page, the Merchandise Extension is on
    if( !$items_wrapper.length ) { // Merchandise Extension settings page
        $items_wrapper = $('.leyka-leyka_merchandise_library-field-wrapper');
    }
    if( !$items_wrapper.length ) {
        return;
    }

    let wp_adminbar_height = $('#wpadminbar').height(); // For validation service purposes

    // Pre-submit actions:
    if(leyka_is_gutenberg_active()) { // Gutenberg is on for Campaign edit page

        /** @todo Add validation for Gutenberg mode (reference: https://bdwm.be/gutenberg-how-to-prevent-post-from-being-saved/) */

    } else { // Gutenberg is off

        $items_wrapper.parents('form:first').on('submit.leyka', function(e){

            if( !leyka_all_item_sub_fields_valid($items_wrapper) ) { // Validation
                e.preventDefault();
            }

        });

    }

    // Validate the multi-valued items complex field:
    function leyka_all_item_sub_fields_valid($items_wrapper) {

        let all_fields_valid = true;

        $items_wrapper.find('.multi-valued-item-box:not(.item-template)').each(function(index, item_box){

            let $item_box = $(item_box),
                $box_errors_list = $item_box.find('.notes-and-errors');

            $box_errors_list.find('.error').remove();

            let $item_sub_field = $item_box.find('[name="leyka_merchandise_title"]:visible'),
                $item_sub_field_outer_wrapper = $item_sub_field.parents('.option-block').removeClass('has-errors');

            // Merchandise title is empty:
            if($item_sub_field.length && !$item_sub_field.val().length) {

                console.log($item_sub_field)

                all_fields_valid = false;

                $item_sub_field_outer_wrapper.addClass('has-errors');
                $(window).scrollTop($item_box.offset().top - wp_adminbar_height);

                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $item_sub_field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

            $item_sub_field = $item_box.find('[name="leyka_merchandise_donation_amount_needed"]:visible');
            $item_sub_field_outer_wrapper = $item_sub_field.parents('.option-block').removeClass('has-errors');

            // Merchandise minimal amount is empty or non-positive:
            if($item_sub_field.length && !$item_sub_field.val().length || $item_sub_field.val() <= 0) {

                all_fields_valid = false;

                $item_sub_field_outer_wrapper.addClass('has-errors');
                $(window).scrollTop($item_box.offset().top - wp_adminbar_height);

                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $item_sub_field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

        });

        return all_fields_valid;

    }

});