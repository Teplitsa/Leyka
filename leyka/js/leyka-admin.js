jQuery(document).ready(function($){
    // Temp. hide Donates -> Settings -> Taxes tab:
    $('a[href*="page=edd-settings&tab=taxes"]').hide();

    // Settings -> User recalls, link to open recall data editing area:
    $('body').on('click.inline-edit-recall', '.inline-edit-recall-link', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td'),
            $table = $cell.parents('#leyka-recalls-filter');

        $table.find('.inline-edit-recall').hide();
        $table.find('.recall_text').show();
        $table.find('.row-actions').show();

        $('.recall_text', $cell).toggle();
        $cell.find('.row-actions').toggle();
        $cell.find('.inline-edit-recall').toggle();
    });

    // Settings -> User recalls, button to close recall data editing area:
    $('body').on('click.inline-edit-recall', '.reset-recall', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td');

        $('.recall_text', $cell).show();
        $cell.find('.row-actions').show();
        $cell.find('.inline-edit-recall').hide();
    });

    // Settings -> User recalls, link to save the recall data:
    $('body').on('click.submit-recall', '.submit-recall', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td'),
            $edit_area = $('#edit-recall-'+$this.data('recall-id'), $cell),
            $params = $edit_area.find(':input').serializeArray(),
            $buttons = $this.parents('fieldset').find('.submit-recall, .reset-recall');

        $buttons.attr('disabled', 'disabled');
        $params.push({name: 'action', value: 'leyka-recall-edit'});
        $.post(ajaxurl, $params)
         .success(function(resp){
            resp = $.parseJSON(resp);
            if(resp.status == 'error') {
                $cell.find('.recall_edit_message').html( resp.message );
                return;
            } else if(resp.status == 'ok') {
                var $row = $cell.parents('tr');
                $row.find('td.column-text').find('.recall_text').html(resp.data.recall_text);
                $row.find('td.column-text').find('textarea[name="recall_text"]').text(resp.data.recall_text);
                $row.find('td.column-text').find('select[name="recall_status"]').val(resp.data.recall_status);
                $row.find('td.column-status').html(resp.data.recall_status_text);

                $buttons.removeAttr('disabled');
                $this.parents('fieldset').find('.reset-recall').click();
//                window.location.href = '';
            }
        }).error(function(){
            $('.recall_edit_message', $cell)
                .html(l10n.recall_editing_error)
                .fadeIn(200).fadeTo(7000, 1.0).fadeOut(200);
            $edit_area.toggle();
        });
    });

    // Settings -> User recalls, batch actions preprocessing
    $('body').on('submit.batch-submit-recalls', '#leyka-recalls-filter', function(e){
        $(this).find('.inline-edit-recall').find(':input').attr('disabled', 'disabled');
    });

    // Settings -> Payment history, divs to quickly toggle the payments statuses:
    $('.leyka_status_switch').iphoneStyle({
        checkedLabel: l10n.payment_status_switch_complete,
        uncheckedLabel: l10n.payment_status_switch_pending,
        onChange: function(element, is_checked){
            var $this = $(element),
                $indicator = $this.parents('td').find('.loading'),
                $message = $this.parents('td').find('.donation_switching_error');
            $indicator.show();
            $message.hide();
            $.post(ajaxurl, {
                'payment_id': $this.data('payment-id'),
                'new_status': $this.data('new-status'),
                'action': $this.data('action'),
                'leyka_nonce': $this.data('nonce')
            }, function(resp){
                resp = $.parseJSON(resp);
                $indicator.hide();
                if( !resp.hasOwnProperty('payment_status') || resp.status != 'ok' ) {
                    $message.fadeIn(200);
                    if(is_checked)
                        $this.click();
                } else {
                    $message.fadeOut(200);
                    $this.data('new-status', resp.payment_status == 'publish' ? 'pending' : 'publish');
//                    window.location.href = '';
                }
            });
        }
    });

    $('body').on('change.leyka_toggle_free_sum', '#leyka_any_sum_allowed', function(e){
        if($(this).attr('checked')) {
            $('#leyka_max_donation_sum_wrapper').show();
            if($('#edd_variable_pricing').attr('checked'))
                $('#edd_variable_pricing').click();
            $('#edd_regular_price_field').hide();
        } else {
            $('#leyka_max_donation_sum_wrapper').hide();
            $('#edd_regular_price_field').show();
        }
    }).on('change.edDonatesToggleVarPrice', '#edd_variable_pricing', function(e){
        if($(this).attr('checked')) {
            if($('#leyka_any_sum_allowed').attr('checked'))
                $('#leyka_any_sum_allowed').click();
        }
    });

    $('body').on('change.leyka_receiver_is_private', ':radio[id*=leyka_receiver_is_private]', function(e){
        var $this = $(this);
        if($this.val() == 1) {
            $('input[name*="leyka_receiver_legal_"]').parents('tr').hide();
            $('div[id*="leyka_receiver_private_"]').show();
        } else {
            $('input[name*="leyka_receiver_legal_"]').parents('tr').show();
            $('div[id*="leyka_receiver_private_"]').hide();
        }
    });

    // Initial fields state:
    var $receiver_type = $(':radio[id*=leyka_receiver_is_private]:checked');
    if($receiver_type.length == 0) {
        $('input[name*="leyka_receiver_legal_"]').parents('tr').hide();
        $('div[id*="leyka_receiver_private_"]').hide();
    } else if($receiver_type.val() == 1) {
        $('input[name*="leyka_receiver_legal_"]').parents('tr').hide();
        $('div[id*="leyka_receiver_private_"]').show();
    } else {
        $('div[id*="leyka_receiver_private_"]').hide();
    }
});