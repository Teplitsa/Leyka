window.LeykaPageMain = function($) {
    var self = this; self.$ = $;
    
    self.setupNoScroll();
    self.initForms();
    self.inpageCardColumns();
    self.setupCustomRangeControl();
    
    self.bindEvents();
    
    self.showTargetForm();
}

window.LeykaPageMain.prototype = {
        
    bindEvents: function(){

        var self = this; var $ = self.$;
   
        $('.leyka-js-open-form').on('click', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('open');

        });
   
        $('.leyka-js-close-form').on('click', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('close');

        });
        
        $(window).resize(function(){
            self.inpageCardColumns();
        });

    },

    setupNoScroll: function() {

        var self = this; var $ = self.$;
        var position = $(window).scrollTop();

        $(window).scroll(function(){

            var scroll = $(window).scrollTop();

            if($('.leyka-pf').hasClass('leyka-pf--active')){
                $(window).scrollTop(position);
            } else {
                position = scroll;
            }

        });
    },
    
    initForms: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-pf').leykaForm();
    },
    
    inpageCardColumns: function() {
        var self = this; var $ = self.$;
        
        var form = $('.leyka-pf');
        form.each(function(){
            var w = $('.leyka-pf').width();

            if(w >= 600) {
                $(this).addClass('card-2col');
            }
            else{
                $(this).removeClass('card-2col');
            }
        });
    },
    
    setupCustomRangeControl: function() {
        var self = this; var $ = self.$;
        
        $('.amount__range_overlay').show();
        $('.amount__range_custom').show();
    },
    
    showTargetForm: function() {
        var self = this; var $ = self.$;
        
        var hash = window.location.hash.substr(1);
        var $_form = $('#' + hash);
        if($_form.length > 0) {
            $_form.leykaForm('open');
        }
    }
    
}

jQuery(document).ready(function($){

    leykaPageMain = new LeykaPageMain($);
    
}); //jQuery
