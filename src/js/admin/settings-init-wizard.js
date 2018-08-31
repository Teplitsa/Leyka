jQuery(document).ready(function($){

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