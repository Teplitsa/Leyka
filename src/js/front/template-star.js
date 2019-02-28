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
        bindSwiperEvents();
		bindAmountEvents();

    }
	
	function resize(el, k) {
		console.log(el.value);
		el.style.width = ((el.value.length+1) * k) + 'px';
	}
	
	function bindAmountEvents() {
		
		function resizable (el, factor) {
			var k = Number(factor) || 7.7;
			var e = 'keyup,keypress,focus,blur,change'.split(',');
			for(var i in e) {
				el.addEventListener(e[i], resize, false);
			}
			resize(el, k);
		}

		$('.donate_amount_flex').each(function(i, el){
			resizable(el, 12);
		});
	}

    function bindModeEvents() {

        $('.leyka-pf__form .step__fields.periodicity').on('click', 'a', function(e){
			e.preventDefault();
			
			$(this).closest('.step__fields').find('a').removeClass('active');
			$(this).addClass('active');
        });
    }

    function bindSwiperEvents() {
        $('.leyka-pf .star-swiper').on('click', 'a.swiper-arrow', function(e){
			e.preventDefault();
			
			var $activeItem = $(this).closest('.star-swiper').find('.swiper-item.active');
			
			var $nextItem = null;
			if($(this).hasClass('swipe-right')) {
				$nextItem = $activeItem.next('.swiper-item');
			}
			else {
				$nextItem = $activeItem.prev('.swiper-item');
			}
			
			if(!$nextItem.length) {
				if($(this).hasClass('swipe-right')) {
					$nextItem = $(this).closest('.star-swiper').find('.swiper-item').first();
				}
				else {
					$nextItem = $(this).closest('.star-swiper').find('.swiper-item').last();
				}
			}
			
			if($nextItem.length) {
				$activeItem.removeClass('active');
				$nextItem.addClass('active');
			}
        });
    }

	init();

}( jQuery ));
