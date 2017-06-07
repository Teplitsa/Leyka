/*
 * Class to manipulate final page UI
 */

window.LeykaGUIFinal = function($) {
    this.$ = $;
    
    $('.leyka-pf__final-informyou .informyou-redirect-text').show();
};

window.LeykaGUIFinal.prototype = {
        
    bindEvents: function(){

        var self = this; var $ = self.$;

        function leyka_remembered_data(data_name) {
            return $.cookie(data_name) ? $.cookie(data_name) : ''; /** add local storage check... */
        }

        var $success_forms = $('.leyka-success-form'),
            donation_id = leyka_remembered_data('leyka_donation_id');

        if( !donation_id ) { // Hide the success form if there are no donation ID stored...
            // $success_forms.hide();
        } else { // ... or display them if there is one in the local storage
            $success_forms.each(function(index, element) {

                var $form = $(element),
                    $donation_id_field = $form.find('input[name="leyka_donation_id"]');

                if( !$donation_id_field.val() ) {

                    $donation_id_field.val(donation_id);
                    $form.show();

                }

            });
        }

        $success_forms.on('submit', function(e){

            e.preventDefault();

            if(self.validateForm(this)) {
                self.subscribeUser();
            }

        });

        $('.leyka-js-no-subscribe').on('click', function(e){
            
            e.preventDefault();

            $(this).closest('.leyka-final-subscribe-form').slideUp(100);

            var $thankyou_block = $('.leyka-pf__final-thankyou');

            $thankyou_block.find('.informyou-redirect-text').slideDown(100);
            self.runRedirectProcess($thankyou_block);

        });

    },

    validateForm: function($form){

        var self = this; var $ = self.$;

        $form = $($form); // Just in case
        var form_valid = false;

        $form.find(':input').each(function(){

            var $input = $(this),
                type = $input.attr('type'),
                name = $input.attr('name'),
                value = $input.val(),
                $error_message = $form.find('.'+name+'-error');

            if($.inArray(type, ['text', 'email']) == 1) {

                if($input.hasClass('required') && !value) {

                    $error_message.show();
                    $input.closest('.donor__textfield').addClass('invalid');

                } else if(type == 'email' && !is_email(value)) {

                    $error_message.show();
                    $input.closest('.donor__textfield').addClass('invalid');

                } else {

                    $error_message.hide();
                    $input.closest('.donor__textfield').removeClass('invalid');
                    form_valid = true;

                }

            }

        });

        return form_valid;
    },
    
    animateRedirectCountdown: function($container){

        var self = this; var $ = self.$;
        
        var $countdown_div = $container.find('.informyou-redirect-text .leyka-redirect-countdown'),
        countdown = $countdown_div.text();

        countdown = parseInt(countdown, 10);
        countdown -= 1;
        if(countdown <= 0) {
            clearInterval(self.countdownInterval);
        }
        $countdown_div.text(String(countdown));

    },

    runRedirectProcess: function($container) {

        var self = this; var $ = self.$;
        
        var ajax_url = leyka_get_ajax_url();
        
        setTimeout(function(){
            
            var redirect_url;

            if( !ajax_url ) {
                redirect_url = '/';
            }
            else {
                redirect_url = ajax_url.replace(/\/core\/wp-admin\/.*/, '');
                redirect_url = redirect_url.replace(/\/wp-admin\/.*/, '');
            }

            window.location.href = redirect_url;

        }, 4000);

        self.countdownInterval = setInterval(self.animateRedirectCountdown.bind(null, $container), 1000);

    },

    subscribeUser: function(){

        var self = this; var $ = self.$;

        $('.leyka-pf__final-thankyou').hide();

        var $informyou_block = $('.leyka-pf__final-informyou');

        $.post(
            leyka_get_ajax_url(),
            $('form.leyka-success-form').serializeArray(),
            'json'
        ).done(function(response){

            if(typeof response.status != 'undefined' && response.status != 0 && typeof response.message != 'undefined') {
                $('.leyka-pf__final-error-message').html(response.message).show();
            }

        }).always(function(){

            $informyou_block.show();
            self.runRedirectProcess($informyou_block);

        });

    }
};

jQuery(document).ready(function($){

    leykaGUIFinal = new LeykaGUIFinal($);
    leykaGUIFinal.bindEvents();

}); //jQuery