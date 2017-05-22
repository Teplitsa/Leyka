window.LeykaPageMain = function($) {
    this.$ = $;
    this.setupNoScroll();
    this.initForms();
}

window.LeykaPageMain.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
   
        $('.leyka-js-open-form').on('click', function(e){
            e.preventDefault();
            
            $(this).closest('.leyka-pf').leykaForm('open');
        });
   
        $('.leyka-js-close-form').on('click', function(e){
            e.preventDefault();
   
            $(this).closest('.leyka-pf').leykaForm('close');
        });
    },

    setupNoScroll: function() {
        var self = this; var $ = self.$;
        
        var position = $(window).scrollTop();
        $(window).scroll(function(){

            var scroll = $(window).scrollTop();

            if($('.leyka-pf').hasClass('leyka-pf--active')){
                $(window).scrollTop(position);
            }
            else {
                position = scroll;
            }
        });
    },
    
    initForms: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-pf').leykaForm();
    }
}

jQuery(document).ready(function($){
    
    leykaPageMain = new LeykaPageMain($);
    leykaPageMain.bindEvents();
    
}); //jQuery
