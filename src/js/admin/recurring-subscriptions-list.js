/** Recurring subscriptions list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'recurring-subscriptions-list-page' ) {
        return;
    }

	$.leyka_init_filter_datepicker($('input[name="first-donation-date"]'), {
	    warningMessage: leyka.first_donation_date_incomplete_message
	});

});