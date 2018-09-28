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