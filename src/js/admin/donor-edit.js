/** Donor's info page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donor-info-page' ) {
        return;
    }

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        let $data_table = $('.leyka-data-table');
        $data_table.DataTable({
            'processing': true,
            'serverSide': true,
            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_donor_donations';
                    data.donor_id = $data_table.data('donor-id');
                }
            },
            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                                +donation_id
                            +'</a>';
                    },
                },
                {
                    data: 'payment_type',
                    className: 'column-donation_type',
                    render: function(data){
                        return '<i class="icon-payment-type icon-'+data.id+' has-tooltip" title="'+data.label+'"></i>';
                    },
                },
                {data: 'date', className: 'column-date',},
                {
                    data: 'campaign',
                    className: 'column-campaign data-campaign leyka-donation-info-wrapper',
                    render: function(data, type, row_data){

                        return '<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title=""></i>'
                            +'<span class="leyka-tooltip-content">'
                                +'<strong>'+row_data.status.label+':</strong> '+lcfirst(row_data.status.description)
                            +'</span>'
                            +'<div class="leyka-donation-additional-data">'
                                +'<div class="first-sub-row">'+row_data.campaign_title+'</div>'
                                +'<div class="second-sub-row">'
                                    +(row_data.gateway_pm.gateway_icon_url ? '<img src="'+row_data.gateway_pm.gateway_icon_url+'" alt="'+row_data.gateway_pm.gateway_label+'">' : '')
                                    +row_data.gateway_pm.gateway_label+', '+row_data.gateway_pm.pm_label
                                +'</div>'
                            +'</div>';

                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data, type, row_data){
                        return data.amount_formatted+'&nbsp;'+data.currency_label
                            +'<span class="amount-total"> / '+data.amount_total_formatted+'&nbsp;'+data.currency_label+'</span>';
                    }
                },
            ],
            rowCallback: function(row, data){ // After the data loaded from server, but before row is rendered in the table
                $(row)
                    .addClass('leyka-donations-table-row')
                    .addClass(data.payment_type.type_id === 'correction' ? 'leyka-donation-row-correction' : '')
                    .find('.has-tooltip').leyka_admin_tooltip();
            },

            pageLength: 10,
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
    // Donations list data table - END

});

// Donor info
jQuery(document).ready(function($){

    $('.donor-add-description-link').click(function(e){
        e.preventDefault();
        $('.add-donor-description-form').toggle();
    });

    $('.add-donor-description-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form.closest('.donor-description'),
            $field = $form.find('textarea[name="donor-description"]'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$field.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_save_donor_description',
            nonce: $('#leyka_save_editable_str_nonce').val(),
            text: $field.val(),
            donor: $('#leyka_donor_id').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        setTimeout(function(){
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                            $fieldWrapper.find('.description-text').text(json.saved_text);
                            $fieldWrapper.find('.leyka-editable-str-field').text(json.saved_text);
                            $('.donor-add-description-wrapper').remove();
                            $('.donor-view-description-wrapper').show();
                        }, 1000);
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });    
});

// comments
function leykaSetCommentsListVisibilityState() {
    let $ = jQuery;

    if($('#leyka_donor_admin_comments table tbody tr').length > 1) {
        $('table.donor-comments').show();
        $('.no-comments').hide();
    }
    else {
        $('table.donor-comments').hide();
        $('.no-comments').show();
    }
}

jQuery(document).ready(function($){
    $('.add-donor-comment-link').click(function(e){
        e.preventDefault();

        var $form = $(this).parent().find('.new-donor-comment-form');
        $form.toggle();
        $form.find('.ok-icon').css('display', 'none');
    });

    $('#leyka_donor_admin_comments table').on('click', '.comment-icon-delete', function(e){
        e.preventDefault();

        if(!confirm(leyka.confirm_delete_comment)) {
            return;
        }

        let $button = $(this),
            $row = $(this).closest('tr'),
            $cell = $(this).closest('td'),
            $metabox = $(this).closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $cell.find('.loader-wrap'),
            comment_id = $button.data('comment-id'),
            donor_id = $('#leyka_donor_id').val();

        $button.hide();

        let ajax_params = {
            action: 'leyka_delete_donor_comment',
            nonce: $('input[name="leyka_delete_donor_comment_nonce"]').val(),
            comment_id: comment_id,
            donor: donor_id
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        $row.remove();
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                        }
                        else {
                            alert(leyka.error_message);
                        }
                        $button.show();
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.show();
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
            });
    });

    $('.new-donor-comment-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form,
            $commentField = $form.find('input[name="donor-comment"]'),
            $metabox = $form.closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$commentField.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_add_donor_comment',
            nonce: $('#leyka_add_donor_comment_nonce').val(),
            comment: $commentField.val(),
            donor: $('#leyka_donor_id').val()
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
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                        }, 1000);

                        var $trTemplate = $table.find('tbody tr:first'),
                            $tr = $trTemplate.clone(),
                            comment_html = json.comment_html;

                        $tr = $(comment_html);
                        $table.append($tr);

                        leykaBindEditableStrEvents($tr);
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });
});


// editable string
function leykaBindEditableStrEvents($container) {
    let $ = jQuery;

    $container.find('.leyka-editable-str-field').on('blur', function(e){
        if($(this).prop('readonly')) {
            return;
        }

        leykaSaveEditableStrAndCloseForm($(this));
    });

    $container.find('input.leyka-editable-str-field').keypress(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        if ( e.key === "Enter" ) {
            e.preventDefault();
            leykaSaveEditableStrAndCloseForm($(this));
        }    
    });

    $container.find('.leyka-editable-str-field').keydown(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        var $strField = $(this),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        if ( e.key === "Escape" || e.key === "Esc" ) {
            e.preventDefault();
            $strField.val($strResult.text());
            leykaSaveEditableStrAndCloseForm($strField);
        }    
    });

    $container.find('.leyka-editable-str-btn').click(function(e){
        e.preventDefault();

        var $btn = $(this),
            $strField = $('.leyka-editable-str-field#' + $btn.attr('str-field')),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        $strResult.hide();
        $strField.show().focus();
        $btn.hide();
        $strField.parent().find('.loading-indicator-wrap').show();
    });
}

function leykaSaveEditableStrAndCloseForm($strField) {
    let $ = jQuery;

    var $btn = $('.leyka-editable-str-btn#' + $strField.attr('str-btn')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

    var endEditCallback = function(){
        $strField.hide();
        $strResult.show();
        $btn.show();
        $strField.parent().find('.loading-indicator-wrap').hide();
        $strField.prop('readonly', false);
    };

    if($strField.val() != $strResult.text()) {
        leykaSaveEditableStr($strField, endEditCallback);
    }
    else {
        endEditCallback();
    }
}

function leykaSaveEditableStr($strField, saveCallback) {
    let $ = jQuery;

    var $button = $('.leyka-editable-str-link#' + $strField.attr('str-edit-link')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result')),
        $loading = $strField.parent().find('.loader-wrap'),
        $indicatorWrap = $loading.closest('.loading-indicator-wrap');

    let ajax_params = {
        action: $strField.attr('save-action'),
        nonce: $('#leyka_save_editable_str_nonce').val(),
        text: $strField.val(),
        text_item_id: $strField.attr('text-item-id'),
        donor: $('#leyka_donor_id').val()
    };
    
    $loading.css('display', 'block');
    $loading.find('.leyka-loader').css('display', 'block');
    $strField.prop('readonly', true);

    $.post(leyka.ajaxurl, ajax_params, null, 'json')
        .done(function(json){
            if(typeof json.status !== 'undefined') {
                if(json.status === 'ok') {
                    $indicatorWrap.find('.ok-icon').css('display', 'block');

                    if(json.saved_text) {
                        $strResult.text(json.saved_text);
                    }
                    else {
                        $strResult.text($strField.val());
                    }

                    setTimeout(function(){
                        $indicatorWrap.find('.ok-icon').fadeOut("slow", saveCallback);
                    }, 1000);
                }
                else {
                    if(json.message) {
                        alert(json.message);
                    }
                    else {
                        alert(leyka.error_message);
                    }
                    $strField.prop('readonly', false);
                }
                return;
            }
        })
        .fail(function(){
            alert(leyka.error_message);
            $strField.prop('readonly', false);
        })
        .always(function(){
            $loading.css('display', 'none');
            $loading.find('.leyka-loader').css('display', 'none');
        });

}

jQuery(document).ready(function($){
    leykaBindEditableStrEvents($(document));
});

// tags
jQuery(document).ready(function($){
    if(!$('#leyka_donor_tags').length) {
        return;
    }

    window.tagBox && window.tagBox.init();

    var saveDonorTagsTimeoutId = null;

    $("body").on('DOMSubtreeModified', ".tagchecklist", function() {

        if(saveDonorTagsTimeoutId) {
            clearTimeout(saveDonorTagsTimeoutId);
        }

        saveDonorTagsTimeoutId = setTimeout(function() {

            let ajax_params = {
                action: 'leyka_save_donor_tags',
                nonce: $('#leyka_save_donor_tags_nonce').val(),
                tags: $('textarea[name="tax_input[donors_tag]"]').val(),
                donor: $('#leyka_donor_id').val()
            };
            
            $.post(leyka.ajaxurl, ajax_params, null, 'json')
                .done(function(json){
                    if(typeof json.status !== 'undefined') {
                        if(json.status === 'ok') {
                        }
                        else {
                            if(json.message) {
                                alert(json.message);
                            }
                            else {
                                alert(leyka.error_message);
                            }
                        }
                        return;
                    }
                })
                .fail(function(){
                    alert(leyka.error_message);
                })

            saveDonorTagsTimeoutId = null;
        }, 500);

    });

});