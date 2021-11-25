/** Common JS for Extension settings (Extensiton edit page). */

jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if( !$admin_page_wrapper.length || !$admin_page_wrapper.hasClass('extension-settings') ) {
        return;
    }

    $('.delete-extension-link').click(function(e){

        e.preventDefault();

        let $delete_link = $(this),
            $ajax_loading = $delete_link.find('.loading-indicator-wrap'),
            $error = $delete_link.siblings('.delete-extension-error');

        if(confirm(leyka.extension_deletion_confirm_text)) {

            $ajax_loading.show();
            $error.html('').hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_delete_extension',
                extension_id: $delete_link.data('extension-id'),
                nonce: $delete_link.data('nonce'),
            }, function(response){

                $ajax_loading.hide();
                if(
                    typeof response === 'undefined'
                    || typeof response.status === 'undefined'
                    || (response.status !== 0 && typeof response.message === 'undefined')
                ) {
                    return $error.html(leyka.common_error_message).show();
                } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                    return $error.html(response.message).show();
                }

                window.location.href = leyka.extensions_list_page_url+'&extension-deleted=1';

            }, 'json');

        }

    });

});