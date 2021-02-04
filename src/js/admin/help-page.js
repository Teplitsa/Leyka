/** Help page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || !$page_wrapper.hasClass('leyka-help-page') ) {
        return;
    }

    let $form = $('#feedback'),
        $loader = $('#feedback-loader'),
        $message_ok = $('#message-ok'),
        $message_error = $('#message-error');

    $form.submit(function(e){

        e.preventDefault();

        if( !validate_feedback_form($form) ) {
            console.log('Not valid!');
            return false;
        }

        $form.hide();
        $loader.show();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            topic: $form.find('input[name="leyka_feedback_topic"]').val(),
            name: $form.find('input[name="leyka_feedback_name"]').val(),
            email: $form.find('input[name="leyka_feedback_email"]').val(),
            text: $form.find('textarea[name="leyka_feedback_text"]').val(),
            nonce: $form.find('#nonce').val()
        }, function(response){

            $loader.hide();

            if(response === '0') {
                $message_ok.fadeIn(100);
            } else {
                $message_error.fadeIn(100);
            }

        });

        return true;

    });

    function validate_feedback_form($form) {

        let is_valid = true,
            $field = $form.find('input[name="leyka_feedback_name"]');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('input[name="leyka_feedback_email"]');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else if( !is_email($field.val()) ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('textarea[name="leyka_feedback_text"]');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        return is_valid;

    }

});