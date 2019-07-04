/** Donors list page */
jQuery(document).ready(function($){

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
			console.log( "Selected: " + ui.item.value + " ID: " + ui.item.id );
		}
	});

	$('input[name=first-donation-date]').datepicker();
	$('input[name=last-donation-date]').datepicker();
	//$('select[name="campaigns[]"]').chosen();
	//$('select[name=donation-status]').chosen();
	//$('select[name="donors-tags[]"]').chosen();
	//$('select[name="gateways[]"]').chosen();

});
