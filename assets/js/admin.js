// set 
jQuery(document).ready(function($){
    
    if( !$('#leyka-settings-form-cd-campaign_decoration').length) {
        return;
    }
    
    var campaignAttachmentId = 0;
    var $decorationControlsWrap = $('#campaign-decoration');
    var $previewIframe = $decorationControlsWrap.find('#leyka-preview-frame iframe');
    var $loading = $decorationControlsWrap.find('#campaign-decoration-loading');
    var campaignId = $decorationControlsWrap.find('#leyka-decor-campaign-id').val();
    
    function disableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', true);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', true);
    }
    
    function enableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', false);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', false);
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }

    $('#campaign_photo-upload-button').click(function(){
        
        var frame = wp.media({
            title: 'Выбор фотографии кампании',
            multiple: false
        });
        
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            //alert( attachment.id );
            
            if(attachment.id == campaignAttachmentId) {
                return;
            }
            
            disableForm();
            showLoading();
            
            $('#leyka-campaign_thumnail').val(attachment.id);
            
            $.post(leyka.ajaxurl, {
                action: 'leyka_set_campaign_photo',
                attachment_id: attachment.id,
                campaign_id: campaignId,
                nonce: $decorationControlsWrap.find('#set-campaign-photo-nonce').val()
            }, null, 'json')
                .done(function(json) {
        
                    if(typeof json.status !== 'undefined' && json.status === 'error') {
                        alert('Ошибка!');
                        return;
                    }
                    
                    $previewIframe.get(0).contentWindow.location.reload(true);
        
                })
                .fail(function() {
                    alert('Ошибка!');
                })
                .always(function() {
                    hideLoading();
                    enableForm();
                });            
            
            
        });

        frame.open();        
    });
    
    $('#leyka_campaign_template-field').on('change', function(){
        
        disableForm();
        showLoading();
        
        var template = $(this).val();
        $('#leyka-campaign_template').val(template);
        
        $.post(leyka.ajaxurl, {
            action: 'leyka_set_campaign_template',
            campaign_id: campaignId,
            template: template,
            nonce: $decorationControlsWrap.find('#set-campaign-template-nonce').val()
        }, null, 'json')
            .done(function(json) {
    
                if(typeof json.status !== 'undefined' && json.status === 'error') {
                    alert('Ошибка!');
                    return;
                }
                
                $previewIframe.get(0).contentWindow.location.reload(true);
    
            })
            .fail(function() {
                alert('Ошибка!');
            })
            .always(function() {
                hideLoading();
                enableForm();
            });            
            
    });
    
    // Edit permalink:
    function editPermalink() {

        var i, slug_value,
            $el, revert_e,
            c = 0,
            real_slug = $('#post_name'),
            revert_slug = real_slug.val(),
            permalink = $( '#sample-permalink' ),
            permalinkOrig = permalink.html(),
            permalinkInner = $( '#sample-permalink a' ).html(),
            buttons = $('#edit-slug-buttons'),
            buttonsOrig = buttons.html(),
            full = $('#editable-post-name-full');

        // Deal with Twemoji in the post-name.
        full.find( 'img' ).replaceWith( function() { return this.alt; } );
        full = full.html();

        permalink.html( permalinkInner );

        // Save current content to revert to when cancelling.
        $el = $( '#editable-post-name' );
        revert_e = $el.html();

        buttons.html( '<button type="button" class="save button button-small">' + 'OK' + '</button> <button type="button" class="cancel button-link">' + 'CANCEL' + '</button>' );

        // Save permalink changes.
        buttons.children( '.save' ).click( function() {
            var new_slug = $el.children( 'input' ).val();

            if ( new_slug == $('#editable-post-name-full').text() ) {
                buttons.children('.cancel').click();
                return;
            }

            $.post(
                ajaxurl,
                {
                    action: 'sample-permalink',
                    post_id: $('.leyka-campaign-completed').data('campaign-id'),
                    new_slug: new_slug,
                    new_title: $('#title').val(),
                    samplepermalinknonce: $('#samplepermalinknonce').val()
                },
                function(data) {
                    var box = $('#edit-slug-box');
                    box.html(data);
                    if (box.hasClass('hidden')) {
                        box.fadeIn('fast', function () {
                            box.removeClass('hidden');
                        });
                    }

                    buttons.html(buttonsOrig);
                    permalink.html(permalinkOrig);
                    real_slug.val(new_slug);
                    $( '.edit-slug' ).focus();
                    wp.a11y.speak( 'SAVED!' );
                }
            );
        });

        // Cancel editing of permalink.
        buttons.children( '.cancel' ).click( function() {
            $('#view-post-btn').show();
            $el.html(revert_e);
            buttons.html(buttonsOrig);
            permalink.html(permalinkOrig);
            real_slug.val(revert_slug);
            $( '.edit-slug' ).focus();
        });

        // If more than 1/4th of 'full' is '%', make it empty.
        for ( i = 0; i < full.length; ++i ) {
            if ( '%' == full.charAt(i) )
                c++;
        }
        slug_value = ( c > full.length / 4 ) ? '' : full;

        $el.html( '<input type="text" id="new-post-slug" value="' + slug_value + '" autocomplete="off" />' ).children( 'input' ).keydown( function( e ) {
            var key = e.which;
            // On [enter], just save the new slug, don't save the post.
            if ( 13 === key ) {
                e.preventDefault();
                buttons.children( '.save' ).click();
            }
            // On [esc] cancel the editing.
            if ( 27 === key ) {
                buttons.children( '.cancel' ).click();
            }
        } ).keyup( function() {
            real_slug.val( this.value );
        }).focus();

    }

    $('.settings-block.custom_campaign_completed').on('click', function(){
        editPermalink();
    });

});