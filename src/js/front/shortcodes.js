/** Shortcodes frontend */

var leyka; // L10n lines

jQuery(document).ready(function($){

    // Supporters list shortcode:
    $('.leyka-js-supporters-list-more').on('click.leyka', function(e){

        e.preventDefault();

        var $more_names_link = $(this),
            $names_list_wrapper = $more_names_link.parents('.list-content').find('.supporters-names-wrapper'),
            $more_names_wrapper = $more_names_link.parents('.list-content').find('.supporters-list-more-wrapper'),
            names_remain_list = $more_names_link.data('names-remain').length ?
                $more_names_link.data('names-remain').split(';') : [],
            names_loads_remain_number = parseInt($more_names_link.data('loads-remain')),
            $names_remain_number_wrapper = $more_names_link.find('.leyka-names-remain-number'),
            names_remain_number = parseInt($names_remain_number_wrapper.text()),
            names_per_load = $more_names_link.data('names-per-load'),
            names_to_append = [];

        if( !names_loads_remain_number || !names_remain_list.length ) {
            return;
        }

        for(var i=0; i < names_per_load && names_remain_list.length > 0; i++) {
            names_to_append.push(names_remain_list.shift());
        }

        if(names_to_append.length) {
            $names_list_wrapper.append(', ').append(names_to_append.join(', '));
        }
        names_loads_remain_number -= 1;
        names_remain_number -= names_per_load; //(names_remain_number >= names_per_load ? names_per_load : 1);

        $more_names_link.data('names-remain', names_remain_list.join(';'));
        $more_names_link.data('loads-remain', names_loads_remain_number);

        if(names_remain_number <= 0) {
            $more_names_wrapper.hide();
        } else {
            $names_remain_number_wrapper.html(names_remain_number); // Update the remain names number
        }

        if( !names_loads_remain_number || !names_remain_list.length ) { // If no more names or loads, remove the link
            $more_names_link.replaceWith($more_names_link.text());
        }

    });

});