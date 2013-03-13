jQuery(document).ready(function($){
    // Temp. hide Donates -> Settings -> Taxes tab:
    $('a[href*="page=edd-settings&tab=taxes"]').hide();

    $('body').on('click.inline-edit-recall', '.inline-edit-recall', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td'),
            $recall_text = $('.recall_text', $cell),
            $actions_area = $('#actions-recall-'+$this.data('recall-id'), $cell),
            $edit_area = $('#edit-recall-'+$this.data('recall-id'), $cell);

        $recall_text.toggle();
        $actions_area.toggle();
        $edit_area.toggle();
    });

    // Settings -> User recalls, link to save the recall data:
    $('body').on('click.submit-recall', '.submit-recall', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td'),
            $recall_text = $('.recall_text', $cell),
            $actions_area = $('#actions-recall-'+$this.data('recall-id'), $cell),
            $edit_area = $('#edit-recall-'+$this.data('recall-id'), $cell);

        $.post(ajaxurl, $edit_area.find(':input').serialize())
         .success(function(resp){
            resp = $.parseJSON(resp);
            if(resp.status == 'error') {
                $cell.find('.recall_edit_message').html( resp.message );
                return;
            } else if(resp.status == 'ok') {
                window.location.href = '';
            }
        }).error(function(){
            $('.recall_edit_message', $cell)
                .html(l10n.recall_editing_error)
                .fadeIn(200).fadeTo(7000, 1.0).fadeOut(200);
            $edit_area.toggle();
        });
    });

    // Settings -> User recalls, link to close the recall data editing area:
    $('body').on('click.reset-recall', '.reset-recall', function(e){
        e.preventDefault();
        var $this = $(this),
            $cell = $this.parents('td'),
            $recall_text = $('.recall_text', $cell),
            $actions_area = $('#actions-recall-'+$this.data('recall-id'), $cell),
            $edit_area = $('#edit-recall-'+$this.data('recall-id'), $cell);

        $recall_text.toggle();
        $actions_area.toggle();
        $edit_area.toggle();
    });

    // Settings -> Payment history, divs to quickly toggle the payments statuses:
    $('.leyka_status_switch').iphoneStyle({
        checkedLabel: l10n.payment_status_switch_complete,
        uncheckedLabel: l10n.payment_status_switch_pending,
        onChange: function(element, is_checked){
            var $this = $(element);
            $.post(ajaxurl, {
                'payment_id': $this.data('payment-id'),
                'new_status': $this.data('new-status'),
                'action': $this.data('action'),
                'leyka_nonce': $this.data('nonce')
            }, function(resp){
                resp = $.parseJSON(resp);
                if(resp.status == 'ok') {
                    window.location.href = '';
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
    $('#leyka_any_sum_allowed').change(); // Initial setup of price fields
});