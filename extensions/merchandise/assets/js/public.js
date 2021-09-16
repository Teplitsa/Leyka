jQuery(document).ready(function($){

    if( !$('.merchandise-grid').length ) {
        return;
    }


    $('input.leyka_donation_amount').on('change.leyka', function(){
        leyka_setup_merchandise($(this).closest('form.leyka-pm-form'));
    });

    function leyka_setup_merchandise($_form) { // For a given form: show merchandise items available, hide all non-available ones

        let current_amount = $_form.find('input.leyka_donation_amount').val(),
            $merchandise_form_section = $_form.find('.section--merchandise'),
            $merchandise_selected = $merchandise_form_section.find('input[name="leyka_donation_merchandise_id"]').val('');

        let merch_items_amounts = [];
        $merchandise_form_section.find('.merchandise-grid .merchandise-item')
            .addClass('disabled') // First, hide all merchandise items...
            .each(function(){

                let $item = $(this);
                merch_items_amounts.push({'id': $item.data('merchandise-id'), 'amount': $item.data('donation-amount-needed')});

            });

        merch_items_amounts.sort(function(a, b){ // Order merchandise items (from larger to lesser)
            return a.amount < b.amount ? 1 : (a.amount > b.amount ? -1 : 0);
        });

        // ... then display only the first one that fits, starting from max possible needed amount:
        for(let i in merch_items_amounts) {

            if(merch_items_amounts[i].amount <= current_amount) {

                $merchandise_form_section
                    .find('.merchandise-item[data-merchandise-id="'+merch_items_amounts[i].id+'"]')
                    .removeClass('disabled');

                $merchandise_selected.val(merch_items_amounts[i].id);

                break;

            }

        }

        if($merchandise_form_section.find('.merchandise-item:not(.disabled)').length) {
            $merchandise_form_section.show();
            // leyka_setup_merchandise_swiper_width($_form);
        } else {
            $merchandise_form_section.hide();
        }

    }

    $('form.leyka-pm-form').each(function(){ // Setup merchandise on initial page load
        leyka_setup_merchandise($(this));
    });

});