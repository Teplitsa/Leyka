
// Help chat:
jQuery(document).ready(function($){

    let $chat = $('.help-chat'),
        $chat_button = $('.help-chat-button'),
        $loading = $chat.find('.leyka-loader');

    if( !$chat.length ) {
        return;
    }

    // function disable_form() {
    //     $chat.find('input[type=text]').prop('disabled', true);
    //     $chat.find('textarea').prop('disabled', true);
    //     $chat.find('.button').hide();
    // }
    //
    // function enable_form() {
    //     $chat.find('input[type=text]').prop('disabled', false);
    //     $chat.find('textarea').prop('disabled', false);
    //     $chat.find('.button').show();
    // }
    
    function show_loading() {
        $loading.show();
    }

    function hide_loading() {
        $loading.hide();
    }
    
    function show_ok_message() {
        $chat.find('.ok-message').show();
        $chat.removeClass('fix-height');
    }

    function hide_ok_message() {
        $chat.find('.ok-message').hide();
        $chat.addClass('fix-height');
    }
    
    function show_form() {
        $chat.find('.form').show();
    }

    function hide_form() {
        $chat.find('.form').hide();
    }

    function validate_form() {
        return true;
    }
    
    function show_help_chat() {
        $chat_button.hide();
        $chat.show();
    }
    
    function hide_help_chat() {
        $chat.hide();
        $chat_button.show();
    }

    $chat.find('.form').submit(function(e){

        e.preventDefault();
        
        if(!validate_form()) {
            return;
        }

        hide_form();
        show_loading();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            name: $chat.find('#leyka-help-chat-name').val(),
            topic: "Сообщение из формы обратной связи Лейки",
            email: $chat.find('#leyka-help-chat-email').val(),
            text: $chat.find('#leyka-help-chat-message').val(),
            nonce: $chat.find('#leyka_feedback_sending_nonce').val()
        }, null).done(function(response) {
    
            if(response === '0') {
                show_ok_message();
                hide_form();
            } else {
                alert('Ошибка!');
                show_form();
            }

        }).fail(function() {
            show_form();
        }).always(function() {
            hide_loading();
        });
            
    });
    
    $chat_button.click(function(e){

        e.preventDefault();

        show_help_chat();
        hide_ok_message();
        show_form();

    });

    $chat.find('.close').click(function(e){

        e.preventDefault();

        hide_help_chat();
        hide_form();
        show_ok_message();

    });
    
});