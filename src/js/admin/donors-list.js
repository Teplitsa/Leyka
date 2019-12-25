/** Donors list page */
jQuery(document).ready(function($){

	function leyka_fill_datepicker_input_period(inst, extension_range) {

		let input_text = extension_range.startDateText;
		if(extension_range.endDateText && extension_range.endDateText != extension_range.startDateText) {
			input_text += ','+extension_range.endDateText;
		}
		$(inst.input).val(input_text);

	}

	function leyka_init_filter_datepicker($input, options) {

		$input.datepicker({
			range:'period',
			onSelect:function(dateText, inst, extensionRange){
				leyka_fill_datepicker_input_period(inst, extensionRange);
			},

			beforeShow: function(input, inst) {
				let selectedDatesStr = $(input).val(),
					selectedDatesStrList = selectedDatesStr.split(","),
					selectedDates = [];

				for(let i in selectedDatesStrList) {
					if(selectedDatesStrList[i]) {

						let singleDate;
						try {
							singleDate = $.datepicker.parseDate($(input).datepicker('option', 'dateFormat'), selectedDatesStrList[i]);
						} catch {
							// console.log("parse date error: " + selectedDatesStrList[i])
							singleDate = new Date();
						}
						
						selectedDates.push(singleDate);
					}
				}

				$(inst.input).val(selectedDates[0]);
				$(inst.input).datepicker('setDate', selectedDates);
				setTimeout(function(){
					leyka_fill_datepicker_input_period(inst, $(inst.dpDiv).data('datepickerExtensionRange'));
				});
				
			}
		});		

	}

	var selector_values = [],
		selected_values = [],
        $page_wrapper = $('.wrap');

    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donors-list-page' ) {
        return;
    }

    if(typeof $().selectmenu != 'undefined') {
        $('select[name="donor-type"]').selectmenu();
    }

	$('input[name="donor-name-email"]').autocomplete({
		source: leyka.ajaxurl + '?action=leyka_donors_autocomplete',
		minLength: 2,
		select: function( event, ui ) {
			// console.log( "Selected: " + ui.item.label + " ID: " + ui.item.value );
		}		
	});

	leyka_init_filter_datepicker($('input[name="first-donation-date"]'), {
	    warningMessage: leyka.first_donation_date_incomplete_message
	});
	leyka_init_filter_datepicker($('input[name="last-donation-date"]'), {
	    warningMessage: leyka.last_donation_date_incomplete_message
	});

	// Campaigns:
	selected_values = [];
	$('#leyka-campaigns-select').find('option').each(function(){
		selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $('input.leyka-campaigns-selector').autocomplete({
        source: leyka.ajaxurl + '?action=leyka_campaigns_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selected_values,
		leyka_select_callback: function( selectedItems ) {
			var $select = $('#leyka-campaigns-select');
			$select.html('');
			for(var val in selectedItems) {
				var $option = $('<option></option>')
					.val(val)
					.prop('selected', true);
				$select.append($option);
			}
		}        
    });

	// Gateways:
	selector_values = [];
	selected_values = [];
	$('#leyka-gateways-select').find('option').each(function(){
		selector_values.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

    $('input.leyka-gateways-selector').autocomplete({
        source: selector_values,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selected_values,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-gateways-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	// tags
	selected_values = [];
	$('#leyka-donors-tags-select').find('option').each(function(){
		selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $('input.leyka-donors-tags-selector').autocomplete({
        source: leyka.ajaxurl + '?action=leyka_donors_tags_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selected_values,
		leyka_select_callback: function( selectedItems ) {
			var $select = $('#leyka-donors-tags-select');
			$select.html('');
			for(var val in selectedItems) {
				var $option = $('<option></option>')
					.val(val)
					.prop('selected', true);
				$select.append($option);
			}
		}        
    });

	// payment status
	selector_values = [];
	selected_values = [];
	$('#leyka-payment-status-select').find('option').each(function(){
		selector_values.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

	var $leyka_payment_status_autocomplete = $('input.leyka-payment-status-selector').autocomplete({
        source: selector_values,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selected_values,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-payment-status-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	$('.reset-filters').click(function(e){

		e.preventDefault();

		$('input.leyka-payment-status-selector').autocomplete('reset');
		$('input.leyka-donors-tags-selector').autocomplete('reset');
		$('input.leyka-gateways-selector').autocomplete('reset');
		$('input.leyka-campaigns-selector').autocomplete('reset');

		$('input[name="donor-name-email"]').val('');
		$('select[name="donor-type"]').prop('selectedIndex', 0).selectmenu('refresh');

		$('input[name="first-donation-date"]').val('');
		$('input[name="last-donation-date"]').val('');
        $(this).closest('form.donors-list-controls').submit();

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

    }).on('click.leyka', '#bulk_edit', function(e){

        e.preventDefault();

        console.log('Submit the bulk edit'); /** @todo Implement the submit */

    })

});