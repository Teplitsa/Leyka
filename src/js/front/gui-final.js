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
            self.subscribeUser();

        });

        $('.leyka-js-no-subscribe').on('click', function(e){
            
            e.preventDefault();

            $(this).closest('.leyka-final-subscribe-form').slideUp(100);

            var $thankyou_block = $('.leyka-pf__final-thankyou');

            $thankyou_block.find('.informyou-redirect-text').slideDown(100);
            self.runRedirectProcess($thankyou_block);

        });

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
        $informyou_block.show();

        self.runRedirectProcess($informyou_block);

        var data = $(this).find('form.leyka-success-form').serializeArray();
        
        $.post(leyka_get_ajax_url(), data, 'json')
            .done(function(json){
            })
            .fail(function(){
            })
            .always(function(){
            });

    }
};

jQuery(document).ready(function($){

    leykaGUIFinal = new LeykaGUIFinal($);
    leykaGUIFinal.bindEvents();

}); //jQuery