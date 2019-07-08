/** Donor's info page */
jQuery(document).ready(function($){

    var $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donor-info-page' ) {
        return;
    }

    leyka_support_metaboxes('dashboard_page_leyka_donor_info');

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {
        $('.leyka-data-table').DataTable({
            pageLength: 3,
            lengthChange: false,
            ordering:  false,
            searching: false,
            language: {
                processing:     leyka_dt.processing,
                search:         leyka_dt.search,
                lengthMenu:     leyka_dt.lengthMenu,
                info:           leyka_dt.info,
                infoEmpty:      leyka_dt.infoEmpty,
                infoFiltered:   leyka_dt.infoFiltered,
                infoPostFix:    leyka_dt.infoPostFix,
                loadingRecords: leyka_dt.loadingRecords,
                zeroRecords:    leyka_dt.zeroRecords,
                emptyTable:     leyka_dt.emptyTable,
                paginate: {
                    first:    leyka_dt.paginate_first,
                    previous: leyka_dt.paginate_previous,
                    next:     leyka_dt.paginate_next,
                    last:     leyka_dt.paginate_last
                },
                aria: {
                    sortAscending:  leyka_dt.aria_sortAsc,
                    sortDescending: leyka_dt.aria_sortDesc
                }
            }
        });
    }

});

// comments
jQuery(document).ready(function($){
    $('.add-donor-comment-link').click(function(e){
        e.preventDefault();

        var $form = $(this).parent().find('.new-donor-comment-form');
        $form.toggle();
        $form.find('.ok-icon').css('display', 'none');
    });

    $('#leyka_donor_admin_comments table').on('click', '.comment-icon-delete', function(e){
        e.preventDefault();

        let $button = $(this),
            $row = $(this).closest('tr'),
            $metabox = $(this).closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $row.find('.loader-wrap');

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_delete_donor_comment'
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        $row.remove();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert('Ошибка!');
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert('Ошибка!');
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });

    $('.new-donor-comment-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $field_wrapper = $form,
            $commentField = $form.find('input[name="donor-comment"]'),
            $metabox = $form.closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $field_wrapper.find('.loader-wrap');

        if(!$commentField.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_add_donor_comment',
            nonce: $field_wrapper.find(':input[name="leyka_add_donor_comment"]').val(),
            comment: $commentField.val(),
            donor: $('.donor-data-edit').data('donor-id')
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        $commentField.val("");
                        setTimeout(function(){
                            $form.fadeOut("slow");
                        }, 1000);

                        var $trTemplate = $table.find('tbody tr:first'),
                            $tr = $trTemplate.clone(),
                            comment = json.comment;

                        $tr.removeClass('comment-id-0').addClass('comment-id-' + comment.id);
                        $tr.find('.donor-comment-date').html(comment.date);
                        $tr.find('.donor-comment-text').html(comment.text);
                        $tr.find('.donor-comment-author').html(comment.author);
                        $tr.find('*[data-comment-id=0]').data('data-comment-id', comment.id);
                        $table.append($tr);
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert('Ошибка!');
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert('Ошибка!');
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });
});