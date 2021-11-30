jQuery(document).ready(function($){

    if( !$('.merchandise-grid').length ) {
        return;
    }

    function leyka_create_merchandise_slider($slider_ul) {

        return $slider_ul.lightSlider({
            item: 1,
            adaptiveHeight: true,
            pager: false,
            gallery: false,
            prevHtml: '<div class="swiper-arrow swipe-left"></div>',
            nextHtml: '<div class="swiper-arrow swipe-right"></div>',
            onAfterSlide: function($slider){
                $slider
                    .parents('form.leyka-pm-form')
                    .find('input[name="leyka_donation_merchandise_id"]')
                    .val($slider.find('.merchandise-item.active').data('merchandise-id'));
            },
        });

    }

    // For a given form: show Merchandise items available, hide all non-available ones:
    function leyka_setup_merchandise_slider($_form, $merchandise_slider) {

        let current_donation_amount = $_form.find('input.leyka_donation_amount').val(),
            $merchandise_wrapper = $_form.find('.section--merchandise'),
            $merchandise_hidden_slides = $merchandise_wrapper.find('.merchandise-swiper-not-usable-slides'),
            $merchandise_item_selected = $merchandise_wrapper.find('input[name="leyka_donation_merchandise_id"]').val('');

        // 1. Hide all current Merchandise items slides from slider:
        $merchandise_slider.find('.merchandise-item').remove();

        // 2. Sort Merchandise items by amount:
        let merch_items_amounts = [];
        $merchandise_hidden_slides.find('.merchandise-item')
            .each(function(){

                let $item = $(this);
                merch_items_amounts.push({'id': $item.data('merchandise-id'), 'amount': $item.data('donation-amount-needed')});

            });

        merch_items_amounts.sort(function(a, b){ // Order merchandise items (from larger to lesser)
            return a.amount < b.amount ? 1 : (a.amount > b.amount ? -1 : 0);
        });

        // 3. Fill the slider anew, with slides that fit the current Donation amount, starting from max possible needed amount:
        for(let i in merch_items_amounts) {

            if(merch_items_amounts[i].amount <= current_donation_amount) {

                $merchandise_hidden_slides
                    .find('.merchandise-item[data-merchandise-id="'+merch_items_amounts[i].id+'"]')
                    .clone()
                    .appendTo($merchandise_slider);

            }

        }

        // 4. If there are no slides for current Donation amount, hide all Merchandise form section:
        if($merchandise_slider.find('.merchandise-item').length) {
            $merchandise_wrapper.show();
        } else {

            $merchandise_item_selected.val('');
            $merchandise_wrapper.hide();
            return;

        }

        // 5. Re-initialise the slider:
        $merchandise_slider.refresh();
        $merchandise_slider.goToSlide(0);

        // 6. Set the currently chosen Merchandise item value:
        $merchandise_item_selected.val($merchandise_slider.find('.merchandise-item.active').data('merchandise-id'));

    }

    $('form.leyka-pm-form').each(function(){ // Setup merchandise on initial page load

        let $donation_form = $(this),
            $merchandise_slider = leyka_create_merchandise_slider($donation_form.find('ul.merchandise-swiper'));

        leyka_setup_merchandise_slider($donation_form, $merchandise_slider);

        $donation_form.find('input.leyka_donation_amount').on('change.leyka', function(){
            leyka_setup_merchandise_slider($donation_form, $merchandise_slider);
        });

    });

});