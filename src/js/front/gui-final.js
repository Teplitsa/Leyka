/*
 * Class to manipulate final page UI
 */

window.LeykaGUIFinal = function($) {
    this.$ = $;
    
    var $try_again_block = $('.leyka-js-try-again-block');
    var campaign_url = $try_again_block.data('campaign-url');
    if(campaign_url) {
        $try_again_block.find('.leyka-js-try-again').prop('href', campaign_url);
    }
    
    $('.leyka-pf__final-informyou .informyou-redirect-text').show();
};

window.LeykaGUIFinal.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-no-subscribe').click(function(){
            $(this).closest('.leyka-final-subscribe-form').remove();
            
            var $thankyou_block = $('.leyka-pf__final-thankyou');
            $thankyou_block.find('.informyou-redirect-text').show();
            self.runRedirectProcess($thankyou_block);
        });
        
        $(".thankyou-email-me-button a").click(function(e){
            e.preventDefault();
            self.subscribeUser();
        });
    },
    
    animateRedirectCountdown: function($container){
        var self = this; var $ = self.$;
        
        var countdown = $container.find('.informyou-redirect-text .leyka-redirect-countdown').text();
        countdown = parseInt(countdown, 10);
        countdown -= 1;
        if(countdown == 0) {
            clearInterval(self.countdownInterval);
        }
        $container.find('.informyou-redirect-text .leyka-redirect-countdown').text(String(countdown));
        
    },
    
    runRedirectProcess: function($container) {
        var self = this; var $ = self.$;
        
        var ajax_url = leyka_get_ajax_url();
        
        setTimeout(function(){
            
            var redirect_url;
            
            if(null == ajax_url) {
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
        
        var data = {action: 'leyka_ajax_submit_subscribe'};
        
        $.post(leyka_get_ajax_url(), data, null, 'json')
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