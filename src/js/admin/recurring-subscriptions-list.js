/** Recurring subscriptions list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'recurring-subscriptions-list-page' ) {
        return;
    }

	let selector_values = [],
		selected_values = [];

	$('input[name="donor-name-email"]').autocomplete({
		source: leyka.ajaxurl+'?action=leyka_donors_autocomplete',
		minLength: 2
	});

	$.leyka_init_filter_datepicker($('input[name="first-donation-date"]'), {
	    warningMessage: leyka.first_donation_date_incomplete_message
	});

	// Campaigns:
	selected_values = [];
	$('#leyka-campaigns-select').find('option').each(function(){
		selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $('input.leyka-campaigns-selector').autocomplete({
        source: leyka.ajaxurl+'?action=leyka_campaigns_autocomplete',
        multiselect: true,
        minLength: 0,
        search_on_focus: true,
        pre_selected_values: selected_values,
		leyka_select_callback: function(selected_items){

			let $select = $('#leyka-campaigns-select');
			$select.html('');
			for(let val in selected_items) {

				let $option = $('<option></option>').val(val).prop('selected', true);
				$select.append($option);

			}

		}
    });

	// Gateways:
	selector_values = [];
	selected_values = [];
	$('#leyka-gateways-select').find('option').each(function(){

	    let $this = $(this);

		selector_values.push({label: $.trim($this.text()), value: $this.val()});
		if($this.prop('selected')) {
			selected_values.push({item: {label: $.trim($this.text()), value: $this.val()}});
		}

	});

    $('input.leyka-gateways-selector').autocomplete({
        source: selector_values,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selected_values,
		leyka_select_callback: function(selected_items){
			$('#leyka-gateways-select').find('option').each(function(){
				$(this).prop('selected', selected_items[$(this).val()] !== undefined);
			});
		}
    });

});