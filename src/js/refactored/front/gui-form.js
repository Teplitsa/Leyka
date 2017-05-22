window.LeykaGUIForm = function($) {
    this.$ = $;
}

window.LeykaGUIForm.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.amount__figure')
            .on('focus', 'input', function(){
    
                $(this).parents('.amount__figure').addClass('focus');
            })
            .on('blur', 'input', function(){
    
                $(this).parents('.amount__figure').removeClass('focus');
            });        
    }

}

jQuery(document).ready(function($){
    
    leykaGUIForm = new LeykaGUIForm($);
    leykaGUIForm.bindEvents();
    
}); //jQuery
