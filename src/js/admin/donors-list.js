/** Donors list page */
jQuery(document).ready(function($){

	var selectorValues = [],
		selectedValues = [];

    var $page_wrapper = $('.wrap');
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
			console.log( "Selected: " + ui.item.label + " ID: " + ui.item.value );
		}		
	});

	$('input[name=first-donation-date]').datepicker({
		range:'period'
	});

	$('input[name=last-donation-date]').datepicker({
		range:'period'
	});

	// campaigns
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

	// gateways
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
		$('input[name=first-donation-date]').datepicker('setDate', null);
		$('input[name=last-donation-date]').datepicker('setDate', null);

		//var $form = $(this).closest('form');
		//$form[0].reset();
	});
});