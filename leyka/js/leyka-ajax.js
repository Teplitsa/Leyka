var leyka_gateway_fields_url;
var leyka_single_nonce;
var leyka_free_nonce;
var edd_scripts;
jQuery(document).ready(function($){
    var $form = $('#leyka-single-form');
    // send Add to Cart request, then redirect to the cart page
    $form.on('change.edDonatesSingle', 'input[name="edd-gateway"]', function(e){
        var $this = $(this); // input field

        if($this.val() == '0')
            return false;

        $('#leyka_form_resp').html('<img src="'+edd_scripts.ajax_loader+'"/>');
        $.get(
            leyka_gateway_fields_url,
            {'payment-mode': $this.val(), 'nonce': leyka_single_nonce},
            function(resp){
                $form.find('#leyka_form_resp').html(resp);
        });
    }).on('submit.edDonatesSingle', '', function(e){
        var $free_sum_field = $('input[name="leyka_free_donate_amount"]'),
            $agree_to_terms = $('#edd_agree_to_terms');
        if($agree_to_terms.length && !$agree_to_terms.attr('checked')) {
            $form.find('#leyka_client_errors').html(l10n.error_single_donate_must_agree_to_terms).show();
            e.preventDefault();
            return;
        }
        if($free_sum_field.length) {
            if( !parseFloat($free_sum_field.val()) || parseFloat($free_sum_field.val()) <= 0 ) {
                $form.find('#leyka_client_errors').html(l10n.error_single_donate_free_sum_incorrect).show();
                e.preventDefault();
                return;
            }
        }
    });

    if($form.find('input[name="edd-gateway"]').val() != '0')
        $('input[name="edd-gateway"]:checked', $form).trigger('change.edDonatesSingle');

    $('#leyka_quick_add_to_cart_wrapper').on(
        'submit.edDonatesQuickAddToCart',
        '#leyka_quick_add_to_cart_form',
        function(e){
            e.preventDefault();

            $form = $(this);

            var params = {action: 'edd_add_to_cart', nonce: edd_scripts.ajax_nonce},
                item_val = $form.find('#leyka_quick_add_donate').val();
            if(item_val.search('_') != -1) {
                item_val = item_val.split('_');
                params.download_id = item_val[0];
                params.price_id = item_val[1];
            } else {
                params.download_id = item_val;
                params.price_id = 'false';
            }
            
            if($form.find('#leyka_quick_add_donate option:selected').hasClass('any-sum')) {
                var free_sum_val = parseInt($form.find('#leyka_quick_free_sum').val());
                if( !free_sum_val || free_sum_val <= 0 ) {
                    $form.find('#leyka_quick_free_sum').addClass('error').focus();
                    return;
                } else {
                    params.action = 'leyka-free-donate-add-to-cart';
                    params.nonce = leyka_free_nonce;
                    params.donate_id = item_val;
                    params.sum = free_sum_val;
                }
            }
            $.post(edd_scripts.ajaxurl, params, function(resp){
                window.location.href = edd_scripts.checkout_page;
            });
    }).on(
        'change.edDonatesQuickAddToCart',
        '#leyka_quick_add_donate',
        function(e){
            var $this = $(this),
                $free_price_block = $this.parents('#leyka_quick_add_to_cart_form')
                                         .find('#leyka_quick_free_sum_label');
            if($this.find('option:selected').hasClass('any-sum')) {
                $free_price_block.find('input').removeAttr('disabled');
                $free_price_block.show(100);
            } else {
                $free_price_block.hide(100);
                $free_price_block.find('input').attr('disabled', 'disabled'); // Just in case, to clear the post fields
            }
    }).find('#leyka_quick_add_donate').change(); // Hide/show free donation fields when page just loaded

    $('.edd_free_donate_form').on('submit.edDonatesFreeAddToCart', '', function(e){
        e.preventDefault();

        $form = $(this);

        var donate_id = $form.find('input.donate_id').val(),
            $ajax_loading = $form.find('.edd-cart-ajax'),
            $ajax_message = $form.find('.edd-cart-added-alert'),
            $donation_sum = $form.find('#free_donate_amount_'+donate_id).val();

        if( !$donation_sum )
            return;

        $ajax_loading.show();
        $.post(edd_scripts.ajaxurl, {
            action: $form.find('input.action').val(),
            nonce: leyka_free_nonce,
            donate_id: donate_id,
            sum: $donation_sum
        }, function(resp){
            $ajax_loading.hide();
            if(resp == 'ok') {
                $form.find('.leyka-free-add-to-cart').hide();
                $form.find('.edd_go_to_checkout').show();
                $ajax_message.show();
            }
        });
    });

    $('body').on('click.eddAddToCart', '.edd-add-to-cart', function(e){
        $(this).parents('form').find('.edd-simply-donate').hide();
    });
});