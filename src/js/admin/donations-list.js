/** Donations admin list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donations-list-page' ) {
        return;
    }

    let $admin_list_filters = $page_wrapper.find('form.donations-list-controls'),
        $filters_warning_message = $admin_list_filters.find('#leyka-filter-warning');

    $admin_list_filters.find('[name="donations-list-export"]').click(function(e){

        // Prevent export if no filters were chosed:
        let filters_values = $(this).parents('form').serializeArray(),
            filters_set = false;

        for(let i = 0; i < filters_values.length; i++) {

            if(filters_values[i].name !== 'page' && filters_values[i].value && filters_values[i].value !== '-') {
                filters_set = true;
                break;
            }

        }

        if(filters_set) {
            $filters_warning_message.html('').hide();
        } else {

            e.preventDefault();
            $filters_warning_message.html(leyka.no_filters_while_exporting_warning_message).show();

        }

    });

    $('body').on('click.leyka', '.leyka-tooltip-error-content-more', function(e){

        e.preventDefault();
        e.stopImmediatePropagation();

        let $link = $(this),
            $tooltip_wrapper = $link.parents('.ui-widget-content:first'),
            tooltip_content_extended = $link.siblings('.error-full-info-tooltip-content').html(),
            tooltip_content_default = $tooltip_wrapper.html();

        $tooltip_wrapper.addClass('leyka-tooltip-x-wide leyka-tooltip-white').html(tooltip_content_extended);

    });

});