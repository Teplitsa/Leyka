/*
 * Class to manipulate donation form from campaign carda
 */

window.LeykaGUIFinal = function($) {
    this.$ = $;
    
    var $try_again_block = $('.leyka-js-try-again-block');
    var campaign_url = $try_again_block.data('campaign-url');
    if(campaign_url) {
        $try_again_block.find('.leyka-js-try-again').prop('href', campaign_url);
    }
};

window.LeykaGUIFinal.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-no-subscribe').click(function(){
            $(this).closest('.leyka-final-subscribe-form').remove();
        });
        
        $(".thankyou-email-me-button a").click(function(e){
            e.preventDefault();
            self.subscribeUser();
        });
    },

    subscribeUser: function(){
        var self = this; var $ = self.$;
        
        $('.leyka-pf__final-thankyou').hide();
        $('.leyka-pf__final-informyou').show();
        
        var data = {action: 'leyka_ajax_submit_subscribe'};
        
        $.post(frontend.ajaxurl, data, null, 'json')
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