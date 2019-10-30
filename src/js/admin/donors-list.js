/** Donors list page */
jQuery(document).ready(function($){

	function leykaInitFilterDatepicker($input, options) {

		let selectedDatesStr = $input.val(),
			selectedDatesStrList = selectedDatesStr.split(","),
			selectedDates = [];
		for(let i in selectedDatesStrList) {
			if(selectedDatesStrList[i]) {
				var parts = selectedDatesStrList[i].split(".");
				selectedDates.push(new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10)));
			}
		}

		let $dp = $input.datepicker({
			range: true,
			onSelect: function(formattedDate, date, dp) {
				if(dp.selectedDates.length == 2) {
					$('#leyka-filter-warning').text('');
				}
			},
			onHide: function(dp, animationCompleted) {
				if(dp.selectedDates.length == 1) {
					$('#leyka-filter-warning').text(options.warningMessage);
				}
				else {
					$('#leyka-filter-warning').text('');
				}
			},
			onShow: function(dp, animationCompleted) {
				if(animationCompleted && dp.selectedDates.length == 2) {
					let $fristSelectedCell = $('.datepicker.active .datepicker--body .datepicker--cell.-selected-').first();
					$fristSelectedCell.addClass('-range-from-');

					let $beetweenDates = $fristSelectedCell.next();

					while($beetweenDates.length > 0) {

						if($beetweenDates.hasClass('-selected-')) {
							$beetweenDates.addClass('-range-to-')
							break;
						}

						$beetweenDates.addClass('-in-range-');
						$beetweenDates = $beetweenDates.next('.datepicker--cell');
					}
				}
			}
		}).data('datepicker');

		$dp.selectedDates = selectedDates;
		$dp.update();

	}

	var selectorValues = [],
		selectedValues = [],
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

	leykaInitFilterDatepicker($('input[name="first-donation-date"]'), {
	    warningMessage: leyka.first_donation_date_incomplete_message
	});
	leykaInitFilterDatepicker($('input[name="last-donation-date"]'), {
	    warningMessage: leyka.last_donation_date_incomplete_message
	});

	// Campaigns:
	selectedValues = [];
	$('#leyka-campaigns-select').find('option').each(function(){
		selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $("input.leyka-campaigns-selector").autocomplete({
        source: leyka.ajaxurl + '?action=leyka_campaigns_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
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
	selectorValues = [];
	selectedValues = [];
	$('#leyka-gateways-select').find('option').each(function(){
		selectorValues.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

    $("input.leyka-gateways-selector").autocomplete({
        source: selectorValues,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-gateways-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	// tags
	selectedValues = [];
	$('#leyka-donors-tags-select').find('option').each(function(){
		selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $("input.leyka-donors-tags-selector").autocomplete({
        source: leyka.ajaxurl + '?action=leyka_donors_tags_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
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
	selectorValues = [];
	selectedValues = [];
	$('#leyka-payment-status-select').find('option').each(function(){
		selectorValues.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

	var $leykaPaymentStatusAutocomplete = $('input.leyka-payment-status-selector').autocomplete({
        source: selectorValues,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-payment-status-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	$('.reset-filters').click(function(e){

		e.preventDefault();

		$('input.leyka-payment-status-selector').autocomplete('reset');
		$("input.leyka-donors-tags-selector").autocomplete('reset');
		$("input.leyka-gateways-selector").autocomplete('reset');
		$("input.leyka-campaigns-selector").autocomplete('reset');

		$('input[name="donor-name-email"]').val('');
		$('select[name="donor-type"]').prop('selectedIndex',0).selectmenu("refresh");

		let $dp = $('input[name=first-donation-date]').datepicker().data('datepicker');
		$dp.selectedDates = [];
		$dp.update();

		$dp = $('input[name="last-donation-date"]').datepicker().data('datepicker');
		$dp.selectedDates = [];
		$dp.update();

        $(this).closest('form.donors-list-controls').submit();

	});
});
