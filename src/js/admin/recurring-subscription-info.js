/** Admin JS - Recurring Subscription Info page **/
jQuery(document).ready(function($){

    LeykaStateControl.initVisibilityControlButtons();

    // Recurring subscriptions Donations list data table:
    let $data_table = $('.leyka-data-table.recurring-subscription-donations-table');

    if($data_table.length && typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        $data_table.DataTable({

            ordering:  false, /** @todo Add ordering to the table & it's AJAX query */
            searching: false,
            processing: true,
            serverSide: true,
            paging: false,
            info: false,

            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_recurring_subscription_donations';
                    data.recurring_subscription_id = $data_table.data('init-recurring-donation-id');
                    data.length = 5;
                    data.draw = 5;
                }
            },

            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<div>' +
                            '<div>'+donation_id+'</div>' +
                            '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                            +leyka_dt.toPayment
                            +'</a>'
                            +'</div>';

                    },
                },
                {
                    data: 'type',
                    className: 'column-type data-type',
                    render: function(type){
                        return '<i class="icon-payment-type icon-'+type.name+' has-tooltip" title="'+type.label+'"></i>';
                    },
                },
                {
                    data: 'donor',
                    className: 'column-donor',
                    render: function(donor, type, row_data){
                        return '<div class="donor-name">'
                            +(donor.id ? '<a href="'+leyka.admin_url+'admin.php?page=leyka_donor_info&donor='+donor.id+'">' : '')
                            +donor.name
                            +(donor.id ? '</a>' : '')
                            +'</div>'
                            +'<div class="donor-email">'+donor.email+'</div>';
                    }
                },
                {
                    data: 'date',
                    className: 'column-date',
                    render: function (date) {
                        return date.date_label+'<br>'+date.time_label;
                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data_amount, type, row_data){

                        const amount_html = data_amount.amount === data_amount.total ?
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label :
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label
                            +'<div class="amount-total">'
                            +data_amount.total_formatted+'&nbsp;'+data_amount.currency_label
                            +'</div>';

                        const tooltip_html = row_data.status === 'failed' ?
                            '<strong>Error '+row_data.status.error.id+'</strong>: '+row_data.status.error.name
                            +'<p><a class="leyka-tooltip-error-content-more leyka-inner-tooltip leyka-tooltip-x-wide leyka-tooltip-white" title="" href="#">'
                            +'More info'+ //TODO: Перевод
                            +'</a></p>'
                            +'<div class="error-full-info-tooltip-content">'+row_data.status.error.full_info+'</div>' :
                            '<strong>'+row_data.status.label+':</strong> '+row_data.status.description;

                        return '<span class="leyka-amount '+(data_amount.amount < 0.0 ? 'leyka-amount-negative' : '')+'">'
                            // +'<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title="'+row_data.status.description+'"></i>'
                            +'<span class="leyka-amount-and-status">'
                            +'<div class="leyka-amount-itself" title="">'+amount_html+'</div>'
                            +'<div class="leyka-donation-status-label label-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left leyka-tooltip-on-click" title="">'+row_data.status.label+'</div>'
                            +'<span class="leyka-tooltip-content">'+tooltip_html+'</span></span></span>';

                    }
                },
                {
                    data: 'gateway_pm',
                    className: 'column-gateway_pm data-gateway_pm',
                    render: function(gateway_pm, type, row_data){

                        return '<span class="leyka-gateway-pm has-tooltip leyka-tooltip-align-left" title="'+gateway_pm.gateway.label+' / '+gateway_pm.pm.label+'">'
                            +'<div class="leyka-gateway-name">'
                            +(gateway_pm.gateway.icon_url !== '' ?
                                '<img src="'+gateway_pm.gateway.icon_url+'" alt="'+gateway_pm.gateway.label+'">' :
                                '<img src="'+gateway_pm.leyka_plugin_base_url+'/img/pm-icons/custom-payment-info.svg" alt="'+gateway_pm.gateway.label+'">')
                            +'</div>'
                            +'<div class="leyka-pm-name">'
                            +(gateway_pm.pm.label !== '' ? '<img src="'+gateway_pm.pm.admin_icon_url+'" alt="'+gateway_pm.pm.label+'">' : '')
                            +'</div></span>';

                    }
                },
                {
                    data: 'donor',
                    className: 'column-donor_message',
                    render: function(donor, donation_id) {
                        if(donor.email_date !== '') {
                            return '<div class="donor has-thanks">'+
                                '<span class="donation-email-status">'+leyka_dt.sent+'</span>'+
                                '<span class="donation-email-date">'+donor.email_date+'</span>'+
                            '</div>';
                        } else {
                            return '<div className="leyka-no-donor-thanks donor no-thanks" data-donation-id="'+donation_id+'" data-nonce="'+donor.wp_nonce+'">'+
                                '<span className="donation-email-status">'+leyka_dt.notSent+'</span>'+
                                '<span className="donation-email-action send-donor-thanks">'+leyka_dt.sendItNow+'</span>'+
                            '</div>';
                        }
                    }
                }
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

    $('.leyka-subscription-status.recurring-is-active-field input[type="checkbox"]').on('change', function() {

        $.post(
            ajaxurl,
            {
                name: 'action',
                action: 'leyka_cancel_recurring_by_manager',
                donation_id: $(this).data('donation-id'),
                state: $(this).is(':checked')
            },
            null,
            'json'
        ).done(function(response){
            if(response.status==='ok') {
                location.reload();
            }
        });
    })

});