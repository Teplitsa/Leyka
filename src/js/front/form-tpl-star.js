/*
 * Star form template functionality and handlers
 */

(function($){

    function init() {
		
		bindEvents();
		
    }

    /* event handlers */
    function bindEvents() {

        bindModeEvents();
        bindAgreeEvents();
        bindSwiperEvents();
		bindAmountEvents();

    }
	
	function resize(e, el, k) {
        var val = $.trim(el.value);
        if(!val) {
            $(el).addClass('empty');
            
            if(!e || e.type == 'blur') {
                setAmountPlaceholder(el);
                val = $(el).attr('placeholder');
                $(el).siblings('.currency').hide();
                $(el).addClass('show-ph');
            }
            else if(e.type == 'focus') {
                $(el).siblings('.currency').show();
                $(el).removeClass('show-ph');
            }
        }
        else {
            $(el).removeClass('empty');
            $(el).removeClass('show-ph');
        }
        
        var newWidth = 0;
        try {
            var mel = document.createElement('canvas');
            var ctx = mel.getContext("2d");
            ctx.font = $(el).css('font-size') + " " + $(el).css('font-family');
            newWidth = ctx.measureText(val).width;
        }
        catch(ex) {
            newWidth = (val.length + 1) * k;
        }
        
        if(newWidth < 10) {
            newWidth = 10;
        }
        
        $(el).width(newWidth);
	}
    
    function setAmountPlaceholder(el) {
        if($(document).width() >= 640) {
            $(el).prop('placeholder', $(el).data('desktop-ph'));
        }
        else {
            $(el).prop('placeholder', $(el).data('mobile-ph'));
        }
    }
	
	function bindAmountEvents() {
		
		function resizable (el, factor) {
			var k = Number(factor) || 7.7;
			var e = 'keyup,keypress,focus,blur,change'.split(',');
			for(var i in e) {
				el.addEventListener(e[i], function(e){resize(e, el, k);}, false);
			}
			resize(null, el, k);
		}

		$('.donate_amount_flex').each(function(i, el) {
            if(parseInt($(this).css('font-size')) <= 16) {
                resizable(el, 7);
            }
            else {
                resizable(el, 11.1);
            }
            setAmountPlaceholder(el);
		});
	}

    function bindModeEvents() {

        $('.leyka-tpl-star-form .section__fields.periodicity').on('click', 'a', function(e){
			e.preventDefault();
			
			$(this).closest('.section__fields').find('a').removeClass('active');
			$(this).addClass('active');
        });
    }

    function bindSwiperEvents() {
        $('.leyka-tpl-star-form .star-swiper').on('click', '.swiper-item', function(e){
            $(this).siblings('.swiper-item.selected').removeClass('selected');
            $(this).addClass('selected');
            
            var $swiper = $(this).closest('.star-swiper');
            swipeList($swiper, $(this));
            toggleSwiperArrows($swiper);
        });
            
        $('.leyka-tpl-star-form .star-swiper').on('click', 'a.swiper-arrow', function(e){
			e.preventDefault();
			
			var $swiper = $(this).closest('.star-swiper');
            var $activeItem = $swiper.find('.swiper-item.selected');
			
			var $nextItem = null;
			if($(this).hasClass('swipe-right')) {
				$nextItem = $activeItem.next('.swiper-item');
			}
			else {
				$nextItem = $activeItem.prev('.swiper-item');
			}
			
			if(!$nextItem.length) {
				if($(this).hasClass('swipe-right')) {
					$nextItem = $swiper.find('.swiper-item').first();
				}
				else {
					$nextItem = $swiper.find('.swiper-item').last();
				}
			}

			if($nextItem.length) {
				$activeItem.removeClass('selected');
				$nextItem.addClass('selected');
			}
            
            swipeList($swiper, $nextItem);
            toggleSwiperArrows($swiper);
        });
        
        $('.star-swiper').each(function() {
            toggleSwiperArrows($(this));
        });
    }
    
    function swipeList($swiper, $activeItem) {
        
        var $list = $swiper.find('.swiper-list');
        var dif = $list.width() - $swiper.width();
        
        if(dif <= 0) {
            return;
        }
        
        var left = parseInt($list.css('left'));
        if($swiper.find('.swiper-item').first().hasClass('selected')) {
            left = 0;
        }
        else if($swiper.find('.swiper-item').last().hasClass('selected')) {
            left = -dif;
        }
        else {
            left = $swiper.width() / 2 - ($activeItem.offset().left - $list.offset().left) - $activeItem.width() / 2;
        }
        $list.css('left', left);
    }
    
    function toggleSwiperArrows($swiper) {
        if($swiper.find('.swiper-list').width() <= $swiper.width()) {
            return;
        }
        
        if($swiper.find('.swiper-item').first().hasClass('selected')) {
            $swiper.removeClass('show-left-arrow');
        }
        else {
            $swiper.addClass('show-left-arrow');
        }
        
        if($swiper.find('.swiper-item').last().hasClass('selected')) {
            $swiper.removeClass('show-right-arrow');
        }
        else {
            $swiper.addClass('show-right-arrow');
        }
    }
    
    function bindAgreeEvents() {
        bindOfertaEvents();
        bindPdEvents();
        
        // agree
        $('.leyka-tpl-star-form .donor__oferta').on('change.leyka', 'input:checkbox', function(){
            var $donorOferta = $(this).parent('.donor__oferta');
            
            if( $donorOferta.find('input:checkbox.required:not(:checked)').length ) {
                $donorOferta.addClass('invalid');
            } else {
                $donorOferta.removeClass('invalid');
            }
        });
    }
    
    function bindOfertaEvents() {
        
        $('.leyka-tpl-star-form .leyka-js-oferta-trigger').on('click.leyka', function(e){
            e.preventDefault();
            var $form = $(this).parents('.leyka-tpl-star-form');
            $form.addClass('leyka-pf--oferta-open');
            $form.find('.leyka-pf__agreement').css('top', getAgreeModalTop($form));
        });

        $('.leyka-tpl-star-form .leyka-pf__agreement.oferta .agreement__close').on('click.leyka', function(e){
            e.preventDefault();
            $(this).parents('.leyka-tpl-star-form').removeClass('leyka-pf--oferta-open');
        });
    }

    function bindPdEvents() {

        $('.leyka-tpl-star-form .leyka-js-pd-trigger').on('click.leyka', function(e){
            e.preventDefault();
            var $form = $(this).parents('.leyka-tpl-star-form');
            $form.addClass('leyka-pf--pd-open');
            $form.find('.leyka-pf__agreement').css('top', getAgreeModalTop($form));
        });

        $('.leyka-tpl-star-form .leyka-pf__agreement.pd .agreement__close').on('click.leyka', function(e){
            e.preventDefault();
            $(this).parents('.leyka-tpl-star-form').removeClass('leyka-pf--pd-open');

        });
    }
    
    function getAgreeModalTop($form) {
        
        var modalTop = $(window).scrollTop() - $form.offset().top;
        if(modalTop < 0) {
            modalTop = 0;
        }
        modalTop += 16;
        
        return modalTop + 'px';
    }

	init();

}( jQuery ));
