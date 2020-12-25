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

        var self = this,
            $ = self.$;

        $('.leyka-pf').leykaForm();

    },

    inpageCardColumns: function() {

        var self = this,
            $ = self.$,
            $form = $('.leyka-pf');

        $form.each(function(){

            let $this = $(this),
                current_width = $('.leyka-pf').width(),
                max_width = $this.data('card-2column-breakpoint-width') ? $this.data('card-2column-breakpoint-width') : 600;

            if(current_width >= max_width) {
                $this.addClass('card-2col');
            } else {
                $this.removeClass('card-2col');
            }

        });
    },
    
    setupCustomRangeControl: function() {

        let self = this; var $ = self.$;

        $('.amount__range_overlay').addClass('amount__range_custom--visible');
        $('.amount__range_custom').addClass('amount__range_custom--visible');

    },
    
    handleHashChange: function(){

        let self = this,
            $ = self.$,
            hash = window.location.hash.substr(1);

        if(hash.indexOf('leyka-activate-package|') > -1) {
            self.handleHashActivatePackageChange(hash);
        } else if(hash) {

            let parts = hash.split('|');

            if(parts.length > 0) {

                let form_id = parts[0];

                if(form_id) {

                    if(form_id.includes("!/")){ // If site navigation works via "#!" in URLs
                        return false;
                    }

                    let $_form = $('#' + form_id);
                    
                    if($_form.length > 0) {

                        $_form.leykaForm('open');

                        for(let i in parts) {
                            self.handleFinalScreenParams($_form, parts[i]);
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
    
    handleFinalScreenParams: function($_form, part){
        if(part.search(/final-open/) > -1) {

            $_form
                .find('.leyka-pf__final-screen')
                .removeClass('leyka-pf__final--open')
                .removeClass('leyka-pf__final--open-half');

            let final_parts = part.split('_');
            try {

                let $final_screen = $_form.find('.leyka-pf__final-screen.leyka-pf__final-' + final_parts[1]);

                $final_screen.addClass('leyka-pf__final--open');
                if(final_parts[2]) {
                    $final_screen.addClass('leyka-pf__final--open-half');
                }

            } catch(ex) {
            }

        }
    }
}

jQuery(document).ready(function($){

    leykaPageMain = new LeykaPageMain($);
    
}); //jQuery
