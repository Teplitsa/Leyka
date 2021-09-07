/** Donors list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donors-list-page' ) {
        return;
    }

	let selected_values = [];

	// Tags autocomplete:
	$('.leyka-donors-tags-select').each(function(){

	    let $select_field = $(this);

        selected_values = [];
	    $select_field.find('option').each(function(){
            selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
        });

        $select_field.siblings('input.leyka-donors-tags-selector').autocomplete({
            source: leyka.ajaxurl+'?action=leyka_donors_tags_autocomplete',
            multiselect: true,
            search_on_focus: true,
            minLength: 0,
            pre_selected_values: selected_values,
            leyka_select_callback: function(selected_items){

                $select_field.html('');
                for(let val in selected_items) {
                    $select_field.append( $('<option></option>').val(val).prop('selected', true) );
                }

            }
        });

    });

	// Donors inline edit:
    let $donors_table_body = $('#the-list'),
        $inline_edit_fields = $('#leyka-donors-inline-edit-fields'),
        $form = $donors_table_body.parents('form'),
        columns_number = $inline_edit_fields.data('colspan'),
        $inline_edit_row = $donors_table_body.find('#leyka-inline-edit-row');

    $form.on('submit.leyka', function(e){

        if(
            $form.find(':input[name="action"]').val() === 'bulk-edit'
            || $form.find(':input[name="action2"]').val() === 'bulk-edit'
        ) {

            e.preventDefault();

            if($form.find('input[name="bulk[]"]:checked').length) { // Display the bulk edit fields only if some donors checked

                if( !$inline_edit_row.length ) {

                    $donors_table_body
                        .prepend($('<tr id="leyka-inline-edit-row"><td colspan="'+columns_number+'"></td></tr>'))
                        .find('#leyka-inline-edit-row td')
                        .append($inline_edit_fields.show());

                    $inline_edit_row = $donors_table_body.find('#leyka-inline-edit-row');

                }

                $inline_edit_row.show();
                $form.find('#bulk-action-selector-top').get(0).scrollIntoView(); // Scroll the bulk edit form into view

            }

        }

    });

    $inline_edit_fields.on('click.leyka', '.cancel', function(e){ // Bulk edit cancel

        e.preventDefault();

        $inline_edit_row.hide();

    }).on('click.leyka', '#bulk-edit', function(e){

        e.preventDefault();

        let $submit_button = $(this).prop('disabled', 'disabled'),
            params = $inline_edit_row.find(':input').serializeArray(),
            $message = $inline_edit_fields.find('.result').html('').hide(); // .error-message

        params.push(
            {name: 'action', value: 'leyka_bulk_edit_donors'},
            {name: 'nonce', value: $inline_edit_fields.data('bulk-edit-nonce'),}
        );

        $donors_table_body.find('input[name="bulk[]"]:checked').each(function(){
            params.push({name: 'donors[]', value: $(this).val()});
        });

        $.post(leyka.ajaxurl, params, null, 'json')
            .done(function(json) {

                if(json.status === 'ok') {
                    setTimeout(function(){ window.location.reload(); }, 1000);
                } else if(json.status === 'error' && json.message) { // Show error message returned
                    $message.html(json.message).show();
                } else { // Show the generic error message
                    $message.html($message.data('default-error-text')).show();
                }

            }).fail(function(){ // Show the generic error message
            $message.html($message.data('default-error-text')).show();
        }).always(function(){
            // $loading.remove();
            $submit_button.prop('disabled', false);
        });

    })

});
/** Donors list page - END */