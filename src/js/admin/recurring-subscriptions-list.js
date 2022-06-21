/** Recurring subscriptions list page */
jQuery(document).ready(function($){

    LeykaStateControl.initVisibilityControlButtons();

    $('.problematic-subscription-alert .leyka-button-close, .problematic-subscription-alert .leyka-button-ok').on('click', () => {
        $('.problematic-subscription-alert').addClass('leyka-hidden');
    });

    $('.column-donation_id .leyka-content-wrapper .leyka-problematic').on('click', function() {
        $(this).parent().find('.problematic-subscription-alert').removeClass('leyka-hidden');
    });

    leyka_equlize_elements_width('.leyka-admin-tablenav.top .leyka-filter-button');

});