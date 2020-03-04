/** Modules (Gateways & Extensions) settings board common JS. */

// Filter an extension cards list:
jQuery(document).ready(function($){

    let $filter = $('.leyka-modules-filter'),
        $extensions_list = $('.modules-cards-list'),
        extensions_filter = {};

    $filter.find('.filter-toggle').click(function(){
        $(this).closest('.filter-area').toggleClass('show');
    });

    $filter.find('.filter-category-show-filter').click(function(e){

        e.preventDefault();

        $(this).closest('.filter-area').toggleClass('show');

    });

    $filter.find('.filter-category-reset-filter').click(function(e){

        e.preventDefault();

        reset_filter();

    });

    $filter.find('.filter-category-item').click(function(e){

        e.preventDefault();

        toggle_filter_item($(this));
        apply_filter();

    });

    function reset_filter() {

        extensions_filter = {};

        $filter.find('.filter-category-item').removeClass('active');
        apply_filter();

    }

    function apply_filter() {
        if(Object.keys(extensions_filter).length) {

            $extensions_list.find('.module-card').hide();
            $extensions_list.find('.module-card.' + Object.keys(extensions_filter).join('.')).show();

        } else {
            $extensions_list.find('.module-card').show();
        }
    }

    function toggle_filter_item($filter_item) {

        $filter_item.toggleClass('active');

        if($filter_item.hasClass('active')) {
            extensions_filter[$filter_item.data('category')] = true;
        } else {
            delete extensions_filter[$filter_item.data('category')];
        }

    }

});