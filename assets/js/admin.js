/** Gateways settings board */

// Yandex.Kassa settings:
jQuery(document).ready(function($){

    var $gateway_settings = $('#payment-settings-area').find('#gateway-yandex');

    if( !$gateway_settings.length ) {
        return;
    }

    var $yandex_new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$yandex_new_api_used.length ) {
        return;
    }

    $yandex_new_api_used.on('change.leyka', function(){

        if($(this).prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});
/** Common wizards functions */

// Help chat:
jQuery(document).ready(function($){
    
    var $chat = $('.help-chat'),
        $chatButton = $('.help-chat-button');

    if( !$chat.length ) {
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
        }, null).done(function(response) {
    
            if(response === '0') {
                showOKMessage();
                hideForm();
            } else {
                alert('Ошибка!');
                showForm();
            }

        }).fail(function() {
            showForm();
        }).always(function() {
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

// Expandable areas:
jQuery(document).ready(function($){
    $('.expandable-area .expand, .expandable-area .collapse').click(function(e){
        e.preventDefault();
        $(this).parent().toggleClass('collapsed');
    });
});

// Custom file input field:
jQuery(document).ready(function($){
    $('.settings-block.file .button').click(function(e){
        e.preventDefault();
        $(this).parent().find('input[type=file]').trigger('click');
    });
    
    $('.settings-block.file input[type=file]').change(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
    $('.settings-block.file input[type=file]').each(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
});


// Image modal:
jQuery(document).ready(function($){
    
    if(typeof($().easyModal) === 'undefined') {
        return;
    }

    $('.leyka-instructions-screen-full').easyModal({
        top: 100,
        autoOpen: false
    });

    $('.zoom-screen').on('click', function(e){

        e.preventDefault();
        $(this)
            .closest('.captioned-screen')
            .find('.leyka-instructions-screen-full')
            .css('display', 'block')
            .trigger('openModal');

    });
});

// Notification modal:
jQuery(document).ready(function($){

    if(typeof($().dialog) === 'undefined') {
        return;
    }

    $('.leyka-wizard-modal').dialog({
        dialogClass: 'wp-dialog leyka-wizard-modal',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: 'center',
            at: 'center',
            of: window
        },
        open: function(){
            var $modal = $(this);
            $('.ui-widget-overlay').bind('click', function(){
                $modal.dialog('close');
            });
        },
        create: function () {
            $('.ui-dialog-titlebar-close').addClass('ui-button');

            var $modal = $(this);
            $modal.find('.button-dialog-close').bind('click', function(){
                $modal.dialog('close');
            });
        }

    });

    $('#cp-documents-sent').dialog('open');

});  
// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $cp_error_message = $cp_payment_tryout_field.find('.field-errors'),
        $call_support_link = $cp_payment_tryout_field.find('.call-support');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $call_support_link.click(function(e){

        e.preventDefault();

        $('#leyka-help-chat-message').val(
            $('.current-wizard-title').val() + '\n'
            + 'Раздел: ' + $('.current-section-title').val() + '\n'
            + 'Шаг: ' + $('.current-step-title').val() + '\n\n'
            + 'Ошибка:\n'
            + $cp_error_message.text()
        );
        $('.help-chat-button').click();

    });

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

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
            description: 'Leyka - payment testing',
            amount: 1.0,
            currency: leyka_wizard_cp.main_currency,
            accountId: leyka_wizard_cp.test_donor_email,
            invoiceId: 'leyka-test-donation'
        }, function(options){ // success callback

            $cp_error_message.html('').hide();
            $call_support_link.hide();

            $payment_tryout_button
                .removeClass('not-tested').hide()
                .siblings('.result.ok').show();

            if( !$cp_payment_tryout_field.find('.do-payment.not-tested').length ) {
                $cp_payment_tryout_field.find('input[name="payment_tryout_completed"]').val(1);
            }

        }, function(reason, options){ // fail callback

            $call_support_link.show();

            $cp_error_message.html(leyka_wizard_cp.cp_donation_failure_reasons[reason] || reason).show();
            $cp_payment_tryout_field.find('.payment-tryout-comment').hide();

        });

    });

});
// CP payment tryout custom setting - END
// Campaign decoration custom setting:
jQuery(document).ready(function($){
    
    if( !$('#leyka-settings-form-cd-campaign_decoration').length ) {
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
    
    if(!$('.type-rich_html').length) {
        return;
    }
    
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

// Yandex Kassa shopPassword generator:
jQuery(document).ready(function($){

    var $genBtn = $('#yakassa-generate-shop-password');
    
    if(!$genBtn.length) {
        return;
    }
    
    var $stepSubmit = $('.step-submit');
    $stepSubmit.hide();
    
    $genBtn.click(function(){
        var password = makePassword(10);
        var $block = $genBtn.closest('.enum-separated-block');
        $genBtn.hide();
        $block.find('.caption').css('display', 'unset');
        $block.find('.body b').css('display', 'unset').text(password);
        $block.find('input[name=leyka_yandex_shop_password]').val(password);
        $stepSubmit.show();
    });

});
// Yandex Kassa shopPassword generator - END

// Yandex Kassa payment tryout:
jQuery(document).ready(function($){

    var $genBtn = $('#yakassa-make-live-payment');
    
    if(!$genBtn.length) {
        return;
    }
    
    var $loading = $('.yakassa-make-live-payment-loader');
    
    $genBtn.click(function(){
        
        $loading.show();
        $genBtn.prop('disabled', true);

        $.post(leyka.ajaxurl, leykaYakassaPaymentData, null, 'json')
            .done(function(json) {
                
                console.log(json);

                if(typeof json.status === 'undefined') {
                    
                    alert('Ошибка!');
                    
                } else if(json.status === 0 && json.payment_url) {
                    
                    window.location.href = json.payment_url;

                } else {
                    alert('Ошибка!');
                }

            }).fail(function(){
                alert('Ошибка!');
            }).always(function(){
                $loading.hide();
                $genBtn.prop('disabled', false);
            });
            
    });

});
// Yandex Kassa payment tryout - END

function makePassword(len) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789^#@_%$-";

  for (var i = 0; i < len; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
}