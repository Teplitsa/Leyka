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
   
        $('.leyka-js-open-form').on('click.leyka', function(e){

            e.preventDefault();
            $(this).closest('.leyka-pf').leykaForm('open');

        });

        $('.leyka-js-close-form').on('click.leyka', function(e){

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

        /** Leyka success widget behavior - BEGIN */

        // var $success_forms = $('.leyka-success-form'),
        //     donation_id = leyka_remembered_data('leyka_donation_id');
        //
        // if( !donation_id ) { // Hide the success form if there are no donation ID stored...
        //     // $success_forms.hide();
        // } else { // ... or display them if there is one in the local storage
        //     $success_forms.each(function(index, element) {
        //
        //         var $form = $(element),
        //             $donation_id_field = $form.find('input[name="leyka_donation_id"]');
        //
        //         if( !$donation_id_field.val() ) {
        //
        //             $donation_id_field.val(donation_id);
        //             $form.show();
        //
        //         }
        //
        //     });
        // }
        //
        // $success_forms.on('submit', function(e){
        //
        //     e.preventDefault();
        //
        //     var $this = $(this);
        //
        // });

        /** Leyka success widget behavior - END */

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
        
//        $('.amount__range_overlay').show();
//        $('.amount__range_custom').show();
        $('.amount__range_overlay').addClass('amount__range_custom--visible');
        $('.amount__range_custom').addClass('amount__range_custom--visible');
    },
    
    handleHashChange: function() {
        var self = this; var $ = self.$;
        
        var hash = window.location.hash.substr(1);

        if(hash.indexOf('leyka-activate-package|') > -1) {
            self.handleHashActivatePackageChange(hash);
        }
        else if(hash) {
            var parts = hash.split('|');
            if(parts.length > 0) {
                var form_id = parts[0];
                
                if(form_id) {
                    var $_form = $('.leyka-pf#' + form_id);
                    
                    if($_form.length > 0) {
                        $_form.leykaForm('open');
                        
                        for(var i in parts) {
                            var part = parts[i];
                            self.handleFinalScreenParams($_form, part);
                        }
                    }
                }
            }
        }
    },

    handleHashActivatePackageChange: function(hash) {
        var self = this; var $ = self.$;

        var $leykaForm = $('.leyka-pm-form').first();
        $leykaForm.find('.section__fields.periodicity a[data-periodicity="monthly"]').trigger('click');

        var parts = hash.split('|');
        if(parts.length > 1) {
            var amount_needed = parseInt(parts[1]);
            var $selectedSum = null;

            $leykaForm.find('.amount__figure .swiper-item').each(function(i, el){
                if(parseInt($(el).data('value')) >= amount_needed) {
                    $selectedSum = $(el);
                    return false;
                }
            });

            if(!$selectedSum) {
                $selectedSum = $leykaForm.find('.swiper-item.flex-amount-item');
                $selectedSum.find('input[name="donate_amount_flex"]').val(amount_needed);
            }

            if($selectedSum) {
                $selectedSum.trigger('click');
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
