/*
 * Common functionaly for every page with Leyka donation forms
 */

window.LeykaPageMain = function($) {
    var self = this; self.$ = $;
    
    self.setupNoScroll();
    self.initForms();
    self.inpageCardColumns();
    self.setupCustomRangeControl();
    
    self.bindEvents();
    
    self.handleHashChange();
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
        
        $(window).on('hashchange', function() {
            self.handleHashChange();
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
    
    handleHashChange: function() {
        var self = this; var $ = self.$;
        
        var hash = window.location.hash.substr(1);
        var parts = hash.split('|');
        
        if(parts.length > 0) {
            var form_id = parts[0];
            var $_form = $('#' + form_id);
            
            if($_form.length > 0) {
                $_form.leykaForm('open');
                
                for(var i in parts) {
                    var part = parts[i];
                    self.handleFinalScreenParams($_form, part);
                }
            }
        }
    },
    
    handleFinalScreenParams: function($_form, part) {
        if(part.search(/final-open/) > -1) {
            $_form.find('.leyka-pf__final-screen').removeClass('leyka-pf__final--open').removeClass('leyka-pf__final--open-half');
            var final_parts = part.split('_');
            try {
                var $final_screen = $_form.find('.leyka-pf__final-screen.leyka-pf__final-' + final_parts[1]);
                $final_screen.addClass('leyka-pf__final--open');
                if(final_parts[2]) {
                    $final_screen.addClass('leyka-pf__final--open-half');
                }
            }
            catch(ex) {
            }
        }
    }
}

jQuery(document).ready(function($){

    leykaPageMain = new LeykaPageMain($);
    
}); //jQuery
