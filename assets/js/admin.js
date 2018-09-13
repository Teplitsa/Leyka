jQuery(document).ready(function($){
    // console.log('Wizard here!');
});
// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $cp_error_message = $cp_payment_tryout_field.find('.field-errors');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

        console.log($payment_tryout_button, 'Testing passed:', $payment_tryout_button.data('is-testing-passed'));

        if($payment_tryout_button.data('submit-in-process')) {
            return;
        } else {
            $payment_tryout_button.data('submit-in-process', 1);
        }

        // Do a test donation:
        $payment_tryout_button.data('submit-in-process', 0);

        if( !leyka_wizard_cp.cp_public_id ) {

            $cp_error_message.html(leyka_wizard_cp.cp_not_set_up).show();
            return false;

        }

        var widget = new cp.CloudPayments();
        widget.charge({
            language: 'ru-RU',
            publicId: leyka_wizard_cp.cp_public_id,
            description: 'Leyka - test payment',
            amount: 1.0,
            currency: leyka_wizard_cp.main_currency,
            accountId: 'test-donor-email@test.ru',
            invoiceId: 'leyka-test-donation'
        }, function(options){ // success callback

            $cp_error_message.html('').hide();
            $payment_tryout_button
                .removeClass('not-tested').hide()
                .siblings('.result.ok').show();

            if( !$cp_payment_tryout_field.find('.do-payment.not-tested').length ) {
                $cp_payment_tryout_field.find('input[name="payment_tryout_completed"]').val(1);
            }

        }, function(reason, options){ // fail callback
            $cp_error_message.html(leyka_wizard_cp.cp_donation_failure_reasons[reason] || reason).show();
        });

    });

});
// CP payment tryout custom setting - END
// Campaign decoration custom setting:
jQuery(document).ready(function($){
    
    if( !$('#leyka-settings-form-cd-campaign_decoration').length) {
        return;
    }
    
    var campaignAttachmentId = 0;
    var $decorationControlsWrap = $('#campaign-decoration');
    var $previewFrame = $('#leyka-preview-frame');
    var $previewIframe = $previewFrame.find('iframe');
    var $loading = $decorationControlsWrap.find('#campaign-decoration-loading');
    var campaignId = $decorationControlsWrap.find('#leyka-decor-campaign-id').val();
    var $selectTemplateControl = $('#leyka_campaign_template-field');
    
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
    
    function reloadPreviewFrame() {
        //$previewIframe.get(0).contentWindow.location.reload(true);
        var previewLocation = $previewIframe.get(0).contentWindow.location;
        var href = previewLocation.href;
        href = href.replace(/&rand=.*/, '');
        href += '&rand=' + Math.random();
        previewLocation.href = href;
    }
    
    $previewIframe.on('load', function(){
        $previewIframe.height($previewIframe.contents().find('body').height() + 10);
    });

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
                    
                    reloadPreviewFrame();
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
    
    $selectTemplateControl.on('change', function(){
        
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
                
                reloadPreviewFrame();
                //setFrameClass();
            })
            .fail(function() {
                alert('Ошибка!');
            })
            .always(function() {
                hideLoading();
                enableForm();
            });            
            
    });
    
    function setFrameClass() {
        $selectTemplateControl.find('option').each(function(i, el){
            $previewFrame.removeClass($(el).val());
        });
        $previewFrame.addClass($selectTemplateControl.val());
    }

    // move next button
    $('.step-submit').insertBefore($('#campaign-decoration-loading'));

});

// Edit permalink:
jQuery(document).ready(function($){

    var $edit_permalink_wrap = $('.leyka-campaign-permalink'),
        $edit_link = $edit_permalink_wrap.find('.inline-edit-slug'),
        $current_slug = $edit_permalink_wrap.find('.current-slug'),
        $edit_form = $edit_permalink_wrap.find('.inline-edit-slug-form'),
        $slug_field = $edit_form.find('.leyka-slug-field'),
        $loading = $edit_permalink_wrap.find('.edit-permalink-loading');

    $edit_link.on('click.leyka', function(e){

        e.preventDefault();

        $current_slug.hide();
        $edit_link.hide();
        $edit_form.show();

    });

    $edit_permalink_wrap.find('.slug-submit-buttons')
        .on('click.leyka', '.inline-reset', function(e){

            e.preventDefault();

            $edit_form.hide();
            $slug_field.val($edit_form.data('slug-original'));

            $edit_link.show();
            $current_slug.show();

        })
        .on('click.leyka', '.inline-submit', function(e){

            e.preventDefault();

            $loading.show();
            $edit_form.hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_edit_campaign_slug',
                campaign_id: $edit_form.data('campaign-id'),
                slug: $slug_field.val(),
                nonce: $edit_form.data('nonce')
            }, null, 'json')
                .done(function(json) {

                    if(typeof json.status === 'undefined') {
                        alert('Ошибка!');
                    } else if(json.status === 'ok' && typeof json.slug !== 'undefined') {

                        $slug_field.val(json.slug);
                        $edit_form.data('slug-original', json.slug);
                        $current_slug.text(json.slug);

                    } else {
                        alert('Ошибка!');
                    }

                }).fail(function(){
                    alert('Ошибка!');
                }).always(function(){

                    $loading.hide();
                    $edit_link.show();
                    $current_slug.show();

                });

        });

});

// Auto-copy campaign shortcode:
jQuery(document).ready(function($){

    var $shortcode_field_wrap = $('.leyka-campaign-shortcode-field'),
        $copy_shortcode_link = $shortcode_field_wrap.siblings('.inline-copy-shortcode'),
        $current_shortcode = $shortcode_field_wrap.siblings('.leyka-current-value');

    $copy_shortcode_link.on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.hide();
        $current_shortcode.hide();
        $shortcode_field_wrap.show();

    });

    $shortcode_field_wrap.find('.inline-reset').on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.show();
        $current_shortcode.show();
        $shortcode_field_wrap.hide();

    });

});

// Highlighted keys in rich edit
jQuery(document).ready(function($){
    
    var isInitEditDocsDone = false;
    var isEditContentLoadDone = false;
    var isEditFieldTouched = false;
    var originalDocHTML = null;
    var $frameBody = null;
    var isSkipDOMSubtreeModified = false;
    var keysValues = [];
    
    function showRestoreOriginalDocHTMLLink() {
        
        var $link = $('.wp-editor-wrap').closest('.option-block').find('.restore-original-doc');
        
        if(!$link.length) {
            
            $link = $('<a>Вернуть первоначальный текст</a>')
                .attr('href', '#')
                .addClass("inner")
                .addClass("restore-original-doc");
            
            $('.wp-editor-wrap').append($link);
        }
        
        $link.unbind('click');
        $link.click(restoreOriginalDocHTML);
        $link.show();
        
    }
    
    function restoreOriginalDocHTML() {
        
        if(originalDocHTML) {
            $frameBody.html(originalDocHTML);
        }
        
        $('.wp-editor-wrap').closest('.option-block').find('.restore-original-doc').hide();
        replaceKeysWithHTML();
        handleChangeEvents();
        $('.wp-editor-wrap').closest('.option-block').find('.restore-original-doc').hide(); // hack for FF
        
        return false;
    }
    
    function replaceKeysValues(keysValues) {
        for(var i in keysValues[0]) {
            var limit = 100;
            while($frameBody.html().search(keysValues[0][i]) > -1 && limit > 0) {
                limit -= 1;
                var $replacement = $("<span>");
                $replacement.addClass("leyka-doc-key-wrap");
                $replacement.addClass("leyka-doc-key");
                $replacement.attr('data-key', keysValues[0][i].replace("#", "+"));
                $replacement.attr('data-original-value', keysValues[1][i]);
                $replacement.html(keysValues[1][i]);
                $frameBody.html( $frameBody.html().replace(keysValues[0][i], "<span id='key-replacement'> </span>") );
                $frameBody.find('#key-replacement').replaceWith($replacement);
            }
        }
    }
    
    function replaceKeysWithHTML() {
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        
        originalDocHTML = $frameBody.html();
        
        if($('#pd_terms_text').length > 0) {
            keysValues = leykaWizard.pdKeys;
        }
        else {
            keysValues = leykaWizard.termsKeys;
        }
        
        replaceKeysValues(keysValues);
        
        //$frameBody.find(".leyka-doc-key").each(function(){
        //    $(this).data('original-value', $(this).text());
        //});

    }
    
    function handleChangeEvents() {
        
        $frameBody.unbind("click");
        $frameBody.on('click', function(){
            isEditFieldTouched = true;
        });
        
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.bind("DOMSubtreeModified", function(){
            
            if(!isEditContentLoadDone || !originalDocHTML || !isEditFieldTouched) {
                return;
            }
        
            showRestoreOriginalDocHTMLLink();
        });
        
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").bind("DOMSubtreeModified", function(){
            $(this).removeClass("leyka-doc-key");
            if($(this).text() == $(this).data('original-value') && !isSkipDOMSubtreeModified) {
                $(this).addClass("leyka-doc-key");
                isSkipDOMSubtreeModified = true;
            }
            else {
                isSkipDOMSubtreeModified = false;
            }
        });
        
    }
    
    function initEditDocs($iframe) {
        if(isInitEditDocsDone) {
            console.log('initEditDocs already done');
            return;
        }
        isInitEditDocsDone = true;
        console.log('initEditDocs...');
        
        var $frameDocument = $iframe.contents();
        
        $frameDocument.find('body').bind("DOMSubtreeModified", function(){
            if($frameDocument.find('body p').length > 0) {
                if(isEditContentLoadDone) {
                    return;
                }
                isEditContentLoadDone = true;
                
                $frameBody = $frameDocument.find('body');
                restoreOriginalDocHTML();
            }
        });
        
    }
    
    function tryInitEditDocs($tinyMCEContainer) {
        //console.log('changed');
        
        var $iframe = $tinyMCEContainer.find('iframe');
        if($iframe.length) {
            $iframe.on('load', function(){
                initEditDocs($(this));
            });
        }
    }
    
    $('.step-next.button').click(function(e){
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find('.leyka-doc-key-wrap').each(function(index, el){
            if($(el).hasClass('leyka-doc-key')) {
                $(el).replaceWith($(el).data('key').replace("+", "#"));
            }
            else {
                $(el).replaceWith($(el).html());
            }
        });
        //e.preventDefault();
    });
    
    $('.wp-editor-container').bind("DOMSubtreeModified", function(){
        tryInitEditDocs($(this));
    });
    tryInitEditDocs($('.wp-editor-container'));
    
});

// Help chat
jQuery(document).ready(function($){
    
    var $chat = $('.help-chat');
    var $chatButton = $('.help-chat-button');
    
    if( !$chat.length) {
        return;
    }
    
    var $loading = $chat.find('.leyka-loader');
    
    function disableForm() {
        $chat.find('input[type=text]').prop('disabled', true);
        $chat.find('textarea').prop('disabled', true);
        $chat.find('.button').hide();
    }
    
    function enableForm() {
        $chat.find('input[type=text]').prop('disabled', false);
        $chat.find('textarea').prop('disabled', false);
        $chat.find('.button').show();
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function showOKMessage() {
        $chat.find('.ok-message').show();
        $chat.removeClass('fix-height');
    }

    function hideOKMessage() {
        $chat.find('.ok-message').hide();
        $chat.addClass('fix-height');
    }
    
    function showForm() {
        $chat.find('.form').show();
    }

    function hideForm() {
        $chat.find('.form').hide();
    }

    function validateForm() {
        return true;
    }
    
    function showHelpChat() {
        $chatButton.hide();
        $chat.show();
    }
    
    function hideHelpChat() {
        $chat.hide();
        $chatButton.show();
    }

    $chat.find('.form').submit(function(e) {
        e.preventDefault();
        
        if(!validateForm()) {
            return;
        }
        
        //hideErrors();
        hideForm();
        showLoading();
            
        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            name: $chat.find('#leyka-help-chat-name').val(),
            topic: "Сообщение из формы обратной связи Лейки",
            email: $chat.find('#leyka-help-chat-email').val(),
            text: $chat.find('#leyka-help-chat-message').val(),
            nonce: $chat.find('#leyka_feedback_sending_nonce').val()
        }, null)
            .done(function(response) {
    
                if(response == '0') {
                    showOKMessage();
                    hideForm();
                }
                else {
                    alert('Ошибка!');
                    showForm();
                }
                
            })
            .fail(function() {
                alert('Ошибка!');
                showForm();
            })
            .always(function() {
                hideLoading();
            });
            
    });
    
    $chatButton.click(function(e){
        e.preventDefault();
        showHelpChat();
        hideOKMessage();
        showForm();
    });

    $chat.find('.close').click(function(e){
        e.preventDefault();
        hideHelpChat();
        hideForm();
        showOKMessage();
    });
    
});

// show-hide available tags
jQuery(document).ready(function($){
    $('.hide-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $('.show-available-tags').show();
        $('.placeholders-help').hide();
    });
    
    $('.show-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $('.hide-available-tags').show();
        $('.placeholders-help').show();
    });
});
