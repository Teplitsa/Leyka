var leyka_single_nonce;
var leyka_free_nonce;
var edd_scripts;
jQuery(document).ready(function($){
    /** Gateways descriptions in the tooltips */
    $('#leyka_gateways_list').find('.question-icon').popover({delay: {show: 0, hide: 1000}});
    $(document).ajaxSuccess(function(){
        $('.question-icon').popover({delay: {show: 0, hide: 1000}}); // Some descriptions in the tooltips
    });
    
    /** Symbols counter for user comments field */
    $('#leyka_form_resp, #edd_purchase_form_wrap').on('keyup focus', '#leyka-donor-comment', function(){
        $src = $(this);
        var chars = $src.val().length;
        if(chars > 100) {
            $src.val($src.val().substr(0, 100));
            chars = 100;
        }
        $('#leyka-comment-symbols-remain').html(100 - chars);
    });

    /** Single donation form */
    var $form = $('#leyka-single-form');
    // send Add to Cart request, then redirect to the cart page
    $form.on('change.leykaSingle', 'input[name="edd-gateway"]', function(e){
        var $this = $(this); // input field

        if($this.val() == '0')
            return false;

        $('#leyka_form_resp').html('<img src="'+edd_scripts.ajax_loader+'"/>');
        $.get(
            edd_scripts.ajaxurl,
            {action: 'leyka-get-gateway-fields', 'payment-mode': $this.val(), 'nonce': leyka_single_nonce},
            function(resp){
                $form.find('#leyka_form_resp').html(resp);
        });
    }).on('submit.leykaSingle', '', function(e){
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
        $('input[name="edd-gateway"]:checked', $form).trigger('change.leykaSingle');

    /** Quick Add-to-cart field */
    $('#leyka_quick_add_to_cart_wrapper').on(
        'submit.leykaQuickAddToCart',
        '#leyka_quick_add_to_cart_form',
        function(e){
            e.preventDefault();

            $form = $(this);

            var params = {action: 'edd_add_to_cart', nonce: edd_scripts.ajax_nonce},
                item_val = $form.find('#leyka_quick_add_donate').val();
            if(item_val.search('_') != -1) {
                item_val = item_val.split('_');
                params.download_id = item_val[0];
                params.price_ids = [item_val[1]];
            } else {
                params.download_id = item_val;
                params.price_ids = ['false'];
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

            $form.find(':submit').attr('disabled', 'disabled');
            $.post(edd_scripts.ajaxurl, params, function(resp){
                window.location.href = edd_scripts.checkout_page;
            });
    }).on(
        'change.leykaQuickAddToCart',
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

    /** Free donate form */
    $('.edd_free_donate_form').on('submit.leykaFreeAddToCart', '', function(e){
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
    $('#leyka_gateways_list input, #leyka_gateways_list label').click(function(){
        $('.gateways_list_entry.active').removeClass('active');
        $(this).parents('.gateways_list_entry').addClass('active');
        if($('#leyka-single-form').hasClass('complete')) return true;
        $('#leyka-single-form').addClass('complete');
    })
    $('#edd_agree_to_terms').live("change",function(){
        if(this.checked) {
            $('#leyka_form_resp').addClass('complete');
        } else {
            $('#leyka_form_resp').removeClass('complete');
        }
    })
});