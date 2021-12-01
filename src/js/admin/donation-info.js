/** Admin JS - Donation adding/editing pages **/
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donation-info-page' ) {
        return;
    }

    // Validate add/edit donation form:
    $('form#post').submit(function(e){

        let $form = $(this),
            is_valid = true,
            $field = $('#campaign-id');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#campaign_id-error').html(leyka.campaign_required).show();

        } else {
            $form.find('#campaign_id-error').html('').hide();
        }

        $field = $('#donor-email');
        if($field.val() && !is_email($field.val())) {

            is_valid = false;
            $form.find('#donor_email-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#donor_email-error').html('').hide();
        }

        $field = $('#donation-amount');
        let amount_clear = parseFloat($field.val().replace(',', '.'));
        if( !$field.val() || amount_clear === 0.0 || isNaN(amount_clear) ) {

            is_valid = false;
            $form.find('#donation_amount-error').html(leyka.amount_incorrect_msg).show();

        } else {
            $form.find('#donation_amount-error').html('').hide();
        }

        $field = $('#donation-pm');
        if($field.val() === 'custom') {
            $field = $('#custom-payment-info');
        }
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#donation_pm-error').html(leyka.donation_source_required).show();
        } else {
            $form.find('#donation_pm-error').html('').hide();
        }

        if( !is_valid ) {
            e.preventDefault();
        }

    });

    /** New donation page: */
    $('#donation-pm').change(function(){

        let $this = $(this);

        if($this.val() === 'custom') {
            $('#custom-payment-info').show();
        } else {

            $('#custom-payment-info').hide();

            var gateway_id = $this.val().split('-')[0];

            $('.gateway-fields').hide();
            $('#'+gateway_id+'-fields').show();
        }
    }).keyup(function(e){
        $(this).trigger('change');
    });

    /** Edit donation page: */
    $('#donation-status-log-toggle').click(function(e){

        e.preventDefault();

        $('#donation-status-log').slideToggle(100);

    });

    $('input[name*=leyka_pm_available]').change(function(){

        let $this = $(this),
            pm = $this.val();

        pm = pm.split('-')[1];
        if($this.attr('checked')) {
            $('[id*=leyka_'+pm+']').slideDown(50);
        } else {
            $('[id*=leyka_'+pm+']').slideUp(50);
        }

    }).each(function(){
        $(this).change();
    });

    $('#campaign-select-trigger').click(function(e){

        e.preventDefault();

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title.data('campaign-payment-title-previous', $campaign_payment_title.text());

        $(this).slideUp(100);
        $('#campaign-select-fields').slideDown(100);
        $('#campaign-field').removeAttr('disabled');

    });

    $('#cancel-campaign-select').click(function(e){

        e.preventDefault();

        $('#campaign-select-fields').slideUp(100);
        $('#campaign-field').attr('disabled', 'disabled');
        $('#campaign-select-trigger').slideDown(100);

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title
            .text($campaign_payment_title.data('campaign-payment-title-previous'))
            .removeData('campaign-payment-title-previous');

    });

    $('.recurrent-cancel').click(function(e){

        e.preventDefault();

        $('#ajax-processing').fadeIn(100);

        let $this = $(this);
        $this.fadeOut(100);

        // Do a recurrent donations cancelling procedure:
        $.post(leyka.ajaxurl, {
            action: 'leyka_cancel_recurrents',
            nonce: $this.data('nonce'),
            donation_id: $this.data('donation-id')
        }, function(response){
            $('#ajax-processing').fadeOut(100);
            response = $.parseJSON(response);

            if(response.status == 0) {

                $('#ajax-response').html('<div class="error-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeIn(100);

            } else if(response.status == 1) {

                $('#ajax-response').html('<div class="success-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeOut(100);

            }
        });

    });

    $('#recurrent-cancel-retry').click(function(e){

        e.preventDefault();

        $('.recurrent-cancel').click();

    });

    // Recurring subscriptions Donations list data table:
    let $data_table = $('.leyka-data-table.recurring-subscription-donations-table');

    if($data_table.length && typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        $data_table.DataTable({

            ordering:  false, /** @todo Add ordering to the table & it's AJAX query */
            searching: false,
            processing: true,
            serverSide: true,

            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_recurring_subscription_donations';
                    data.recurring_subscription_id = $data_table.data('init-recurring-donation-id');
                }
            },

            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                            +donation_id
                            +'</a>';
                    },
                },
                {
                    data: 'donor',
                    className: 'column-donor',
                    render: function(donor, type, row_data){
                        return '<div class="donor-name">'
                            +(donor.id ? '<a href="'+leyka.admin_url+'?page=leyka_donor_info&donor='+donor.id+'">' : '')
                            +donor.name
                            +(donor.id ? '</a>' : '')
                            +'</div>'
                            +'<div class="donor-email">'+donor.email+'</div>';
                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data_amount, type, row_data){

                        let amount_html = data_amount.amount === data_amount.total ?
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label :
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label
                            +'<span class="amount-total"> / '
                            +data_amount.total_formatted+'&nbsp;'+data_amount.currency_label
                            +'</span>';

                        return '<span class="leyka-amount '+(data_amount.amount < 0.0 ? 'leyka-amount-negative' : '')+'">'
                            +'<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title="'+row_data.status.description+'"></i>'
                            +'<span class="leyka-amount-and-status">'
                            +'<div class="leyka-amount-itself">'+amount_html+'</div>'
                            +'<div class="leyka-donation-status-label label-'+row_data.status.id+'">'+row_data.status.label+'</div>'
                            +'</span>'
                            +'</span>';

                    }
                },
                {data: 'date', className: 'column-date',},
                {
                    data: 'gateway_pm',
                    className: 'column-gateway_pm data-gateway_pm',
                    render: function(gateway_pm, type, row_data){

                        return '<div class="leyka-gateway-name">'
                            +'<img src="'+gateway_pm.gateway_icon_url+'" alt="'+gateway_pm.gateway_label+'">'
                            +gateway_pm.gateway_label+','
                            +'</div>'
                            +'<div class="leyka-pm-name">'+gateway_pm.pm_label+'</div>';

                    }
                },
            ],

            rowCallback: function(row, data){ // After the data loaded from server, but before row is rendered in the table
                $(row)
                    .addClass('leyka-donations-table-row')
                    .addClass('leyka-recurring-subscription-donations')
                    .find('.has-tooltip').leyka_admin_tooltip();
            },

            lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],

            language: {
                processing:     leyka_dt.processing,
                search:         leyka_dt.search,
                lengthMenu:     leyka_dt.lengthMenu,
                info:           leyka_dt.info,
                infoEmpty:      leyka_dt.infoEmpty,
                infoFiltered:   leyka_dt.infoFiltered,
                infoPostFix:    leyka_dt.infoPostFix,
                loadingRecords: leyka_dt.loadingRecords,
                zeroRecords:    leyka_dt.zeroRecords,
                emptyTable:     leyka_dt.emptyTable,
                paginate: {
                    first:    leyka_dt.paginate_first,
                    previous: leyka_dt.paginate_previous,
                    next:     leyka_dt.paginate_next,
                    last:     leyka_dt.paginate_last
                },
                aria: {
                    sortAscending:  leyka_dt.aria_sortAsc,
                    sortDescending: leyka_dt.aria_sortDesc
                }
            }

        });

    }

});