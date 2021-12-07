// Campaign add/edit page:
jQuery(document).ready(function($){

    let $page_type = $('#originalaction'),
        $post_type = $('#post_type');

    if( !$page_type.length || $page_type.val() !== 'editpost' || !$post_type.length || $post_type.val() !== 'leyka_campaign' ) {
        return;
    }

    // "Daily rouble mode" change:
    let $daily_rouble_mode_wrapper = $('.daily-rouble-settings-wrapper'),
        $daily_rouble_mode = $daily_rouble_mode_wrapper.find('input#daily-rouble-mode-on'),
        $daily_rouble_settings_block = $daily_rouble_mode_wrapper.find('.daily-rouble-settings'),
        $default_donations_types_field_block = $('#donations-types'),
        $default_donation_type_field_block = $('#donation-type-default'),
        $campaign_template_field = $(':input[name="campaign_template"]');

    $daily_rouble_mode.change(function(){

        if($daily_rouble_mode.prop('checked')) {

            $default_donations_types_field_block.hide();
            $default_donation_type_field_block.hide();
            $daily_rouble_settings_block.show();

        } else {

            $default_donations_types_field_block.show();
            $default_donation_type_field_block.show();
            $daily_rouble_settings_block.hide();

        }

    }).change();
    // "Daily rouble mode" change - END

    // Campaign type change:
    $(':input[name="campaign_type"]').on('change.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        if( !$this.prop('checked') ) {
            return;
        }

        let $persistent_campaign_fields = $('.persistent-campaign-field'),
            $temp_campaign_fields = $('.temporary-campaign-fields');
            // $form_template_field = $(':input[name="campaign_template"]');

        if($this.val() === 'persistent') {

            $persistent_campaign_fields.show();
            $temp_campaign_fields.hide();

            // $form_template_field
            //     .data('prev-value', $form_template_field.val())
            //     .val('star')
            //     .prop('disabled', 'disabled');

        } else {

            $persistent_campaign_fields.hide();
            $temp_campaign_fields.show();

            // if($form_template_field.data('prev-value')) {
            //     $form_template_field.val($form_template_field.data('prev-value'));
            // }
            // $form_template_field.removeProp('disabled');

        }

    }).change();
    
    // Donation types field change:
    let $donations_types_fields = $(':input[name="donations_type[]"]');

    $donations_types_fields.on('change.leyka', function(e){

        e.preventDefault();

        let donations_types_selected = [];
        $donations_types_fields.filter(':checked').each(function(){
            donations_types_selected.push($(this).val());
        });

        if(donations_types_selected.length > 1 && !$daily_rouble_mode.prop('checked')) {
            $default_donation_type_field_block.show();
        } else {
            $default_donation_type_field_block.hide();
        }

    }).change();

    // Form templates screens demo:
    $('.form-template-screenshot').easyModal({
        top: 100,
        autoOpen: false
    });

    $('.form-template-demo').on('click.leyka', function(e){

        e.preventDefault();

        let $this = $(this), // Demo icon
            $template_field = $this.siblings(':input[name="campaign_template"]'),
            selected_template_id = $template_field.val() === 'default' ?
                $template_field.data('default-template-id'): $template_field.val();

        $this
            .find('.form-template-screenshot.'+selected_template_id)
            .css('display', 'block')
            .trigger('openModal');

    });
    // Form templates screens demo - END

    // Campaign cover upload field:
    $('.upload-photo', '.upload-attachment-field').on('click.leyka', function(e){

        e.preventDefault();

        let $upload_button = $(this),
            $field_wrapper = $upload_button.parents('.upload-photo-field'),
            $field_value = $field_wrapper.find(':input[name="'+$field_wrapper.data('field-name')+'"]'),
            $loading = $field_wrapper.find('.loading-indicator-wrap'),
            $img_wrapper = $upload_button.parents('.upload-photo-complex-field-wrapper').find('.set-page-img-control'),
            frame = wp.media({title: $field_wrapper.data('upload-title'), multiple: false});

        frame.on('select', function(){

            let attachment = frame.state().get('selection').first().toJSON();

            // disableForm();
            $loading.show();

            $field_value.val(attachment.id);

            let nonce_field_name = $field_wrapper.data('field-name').replace('_', '-') + '-nonce',
                ajax_params = {
                    action: $field_wrapper.data('ajax-action'),
                    field_name: $field_wrapper.data('field-name'),
                    attachment_id: attachment.id,
                    campaign_id: $field_wrapper.data('campaign-id'),
                    nonce: $field_wrapper.find(':input[name="'+nonce_field_name+'"]').val()
                };

            $.post(leyka.ajaxurl, ajax_params, null, 'json')
                .done(function(json){

                    if(typeof json.status !== 'undefined' && json.status === 'error') {
                        alert('Ошибка!');
                    } else {

                    	$img_wrapper.find('.img-value').html('<img src="'+json.img_url+'" alt="">');
                    	$img_wrapper.find('.reset-to-default').show();

                    }

                    // reloadPreviewFrame();

                })
                .fail(function(){
                    alert('Ошибка!');
                })
                .always(function(){

                    $loading.hide();
                    // enableForm();

                });

        });

        frame.open();

    });

    // Campaign cover type:
    $('#campaign-cover-type input[type="radio"]').change(function(){
    	if($(this).prop('checked')) {
    		if($(this).val() === 'color') {
    			$('#campaign-cover-bg-color').show();
    			$('#upload-campaign-cover-image').hide();
    		} else {
    			$('#campaign-cover-bg-color').hide();
    			$('#upload-campaign-cover-image').show();
    		}
    	}
    });
    $('#campaign-cover-type input[type="radio"]:checked').change();
    
    // Reset uploaded image to default:
    $('.set-page-img-control .reset-to-default').on('click.leyka', function(e){

        e.preventDefault();

        let $upload_button = $(this),
            $field_wrapper = $upload_button.parents('.set-page-img-control'),
            img_mission = $field_wrapper.data('mission'),
            $loading = $field_wrapper.find('.loading-indicator-wrap'),
        	nonce_field_name = 'reset-campaign-' + img_mission + '-nonce';
        
        let ajax_params = {
            action: 'leyka_reset_campaign_attachment',
            'img_mission': img_mission,
            campaign_id: $field_wrapper.data('campaign-id'),
            nonce: $field_wrapper.find(':input[name="'+nonce_field_name+'"]').val()
        };
        
        $field_wrapper.find('.reset-to-default').hide();
        $loading.show();

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    alert('Ошибка!');
                    $field_wrapper.find('.reset-to-default').show();

                } else {
                	$field_wrapper.find('.img-value').html(leyka.default_image_message);
                }

            })
            .fail(function(){
                alert('Ошибка!');
                $field_wrapper.find('.reset-to-default').show();
            })
            .always(function(){
                $loading.hide();
            });
    });

    // Recalculate total funded amount:
    $('#recalculate_total_funded').click(function(e){

        e.preventDefault();

        let $link = $(this).prop('disabled', 'disabled'),
            $indicator = $link.parent().find('#recalculate_total_funded_loader').show(),
            $message = $link.parent().find('#recalculate_message').hide(),
            $total_collected = $('#collected-amount-number');

        $.get(leyka.ajaxurl, {
            campaign_id: $link.data('campaign-id'),
            action: 'leyka_recalculate_total_funded_amount',
            nonce: $link.data('nonce')
        }, function(resp){

            $link.removeProp('disabled');
            $indicator.hide();

            if(parseFloat(resp) >= 0) {

                resp = parseFloat(resp);

                $total_collected.html(resp);

                // If recalculated sum is different than saved one, refresh the campaign edition page:
                if(parseFloat($total_collected.html()) !== resp) {
                    $('#publish').click();
                }

            } else {
                $message.html(resp).show();
            }

        });

    });
    // Recalculate total funded amount - END

    // Dynamic fields values length display in field description:
    $(':input[maxlength]').keyup(function(e){

        let $field = $(this),
            $description = $('[data-description-for="'+$field.prop('id')+'"]'),
            max_value_length = $field.prop('maxlength'),
            $current_value_length = $description.find('.leyka-field-current-value-length');

        if( !$description.length ) {
            return;
        }

        if($current_value_length.text() >= max_value_length && !leyka_is_special_key(e)) {
            e.preventDefault();
        } else {
            $current_value_length.text($field.val().length);
        }

    }).keyup();

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        let $data_table = $('.leyka-data-table.campaign-donations-table');
        $data_table.DataTable({
            ordering:  false, /** @todo Add ordering to the table & it's AJAX query */
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_campaign_donations';
                    data.campaign_id = $data_table.data('campaign-id');
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
                    data: 'payment_type',
                    className: 'column-donation_type',
                    render: function(payment_type){
                        return '<i class="icon-payment-type icon-'+payment_type.id+' has-tooltip" title="'+payment_type.label+'"></i>';
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

                        let amount_html = data_amount.amount == data_amount.total ?
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
                    .addClass(data.payment_type.type_id === 'correction' ? 'leyka-donation-row-correction' : '')
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

    // Campaign template change:
    $campaign_template_field.on('change.leyka', function(e){

        e.preventDefault();

        let $campaign_template_field = $(this),
            $css_editor_field = $('.css-editor-field'),
            template_selected = $campaign_template_field.val() === 'default' ?
                $campaign_template_field.data('default-template-id') : $campaign_template_field.val();

        if(template_selected === 'star' || template_selected === 'need-help') {

    		$('#campaign-css').show();

    		if(template_selected === 'need-help') { // Display/hide the "Daily rouble" form mode options

                $daily_rouble_mode_wrapper.show();

                if($daily_rouble_mode.prop('checked')) {

                    $default_donations_types_field_block.hide();
                    $default_donation_type_field_block.hide();

                }

            } else {

                $daily_rouble_mode_wrapper.hide();

                if($daily_rouble_mode.prop('checked')) {

                    $default_donations_types_field_block.show();
                    $default_donation_type_field_block.show();

                }

            }

    		// Set the template-specific default CSS editor value, if needed:
            if( !$css_editor_field.data('additional-css-used') ) {

                let original_value = $('.css-editor-'+$campaign_template_field.val()+'-original-value').val();

                $css_editor_field.val(original_value);
                $css_editor_field.data('code-editor-object').codemirror.getDoc().setValue(original_value);

            }

        } else {
        	$('#campaign-css').hide();
        }

    }).change();

    // Multi-valued items fields:
    $('.multi-valued-items-field-wrapper').each(function(){

        let $items_wrapper = $(this),
            $add_item_button = $items_wrapper.find('.add-item');

        // Each muli-valued item should be added to the Campaign form only once.
        // So if it's already added, hide it from the variants for a new Campaign item:
        function leyka_refresh_campaign_new_items_variants() {

            let $new_item_select = $items_wrapper.find('.leyka-campaign-item-add-wrapper select'),
                added_items_ids = [];

            $items_wrapper.find('.multi-valued-item-box:not([id*="item-"])').each(function(){
                added_items_ids.push($(this).prop('id'));
            });
            $new_item_select.each(function(){

                let selected_id = $(this).val();

                if(selected_id !== '-' && selected_id !== '+') {
                    added_items_ids.push(selected_id);
                }

            });

            $new_item_select.find('option').show(); // First, show all options (new items variants)...

            $(added_items_ids).each(function(){
                // ...Then hide options for fields that are already added to Campaign
                $new_item_select.find('option[value="'+this+'"]').hide();
            });

        }

        $add_item_button.on('click.leyka', function(e){

            e.preventDefault();

            if($add_item_button.hasClass('inactive')) {
                return;
            }

            leyka_refresh_campaign_new_items_variants();

            let $new_item_box_wrapper = $items_wrapper.find('.multi-valued-item-box:visible:last'),
                $new_item_subfields_wrapper = $new_item_box_wrapper.find('.leyka-campaign-new-item-subfields'),
                $add_campaign_item_select = $new_item_box_wrapper.find('.leyka-campaign-item-add-wrapper select');

            if($add_campaign_item_select.val() === '+') {
                $new_item_subfields_wrapper.show();
            } else {
                $new_item_subfields_wrapper.hide();
            }

        });

        $items_wrapper.find('.leyka-main-multi-items').on('click.leyka', '.delete-item', function(){
            leyka_refresh_campaign_new_items_variants();
        });

        $items_wrapper.on('change.leyka', '.leyka-campaign-item-add-wrapper select', function(){

            let $add_campaign_item_select = $(this),
                $new_item_box_wrapper = $add_campaign_item_select
                    .parents('.box-content')
                    .find('.leyka-campaign-new-item-subfields');

            if($add_campaign_item_select.val() === '+') {
                $new_item_box_wrapper.show();
            } else {
                $new_item_box_wrapper.hide();
            }

            leyka_refresh_campaign_new_items_variants();

        }).find('.leyka-campaign-item-add-wrapper select:visible').each(function(){

            // For the case when there are no fields in the Library, display the new field subfields right from the start:

            let $this = $(this);

            if($this.val() === '+') {
                $this.trigger('change.leyka');
            }

        });

    });
    // Multi-valued items fields - END

    /* Support packages Extension - available campaign existence check: */
    /** @todo Move all Support packages code here to the Extension */
    if(typeof($().dialog) === 'undefined') {
        return;
    }

    let $campaign_needed_field = $('input#leyka-campaign-needed-for-support-packages'),
        $modal = $('#leyka-campaign-needed-modal-content'),
        $form = $('form#post');

    if( !$modal.length ) {
        return;
    }

    function leyka_support_packages_campaign_deactivation_dialog($modal, retrigger_action){

        let $modal_fields = $modal.find('#leyka-support-packages-behavior-fields');

        if($modal.data('leyka-dialog-initialized')) {
            $modal.dialog('open');
        } else {

            $modal.dialog({
                dialogClass: 'leyka-dialog',
                modal: true,
                draggable: false,
                width: '600px',
                autoOpen: true,
                closeOnEscape: true,
                resizable: false,
                buttons: [{
                    'text': $modal.data('close-button-text'),
                    'class': 'button-secondary',
                    'click': function(){
                        $modal.dialog('close');
                    }
                }, {
                    'text': $modal.data('submit-button-text'),
                    'class': 'button-primary',
                    'click': function(){

                        let $extension_behavior = $modal.find('[name="support-packages-campaign-changed"]:checked'),
                            $loading = $modal.find('#leyka-loading'),
                            $message = $modal.find('#leyka-message');

                        if( !$extension_behavior.length ) {

                            $modal_fields.show();
                            $message
                                .removeClass('success-message')
                                .addClass('error-message')
                                .html($message.data('validation-error-message'))
                                .show();

                            return;

                        }

                        $message.hide();
                        $loading.show();
                        $modal_fields.hide();

                        $.post(leyka.ajaxurl, {
                            action: 'leyka_support_packages_set_no_campaign_behavior',
                            behavior: $extension_behavior.val(),
                            campaign_id: $modal.find('[name="leyka_support_packages_campaign"]').val(),
                            nonce: $modal.data('nonce'),
                        }, null, 'json')
                            .done(function(json){

                                if(typeof json.status !== 'undefined' && json.status !== 0) { // Server-side error

                                    $modal_fields.show();
                                    $message
                                        .removeClass('success-message')
                                        .addClass('error-message')
                                        .html($message.data('error-message'))
                                        .show();

                                } else {

                                    $modal.data('leyka-support-packages-campaign-checked', true);
                                    $message
                                        .removeClass('error-message')
                                        .addClass('success-message')
                                        .html($message.data('success-message'))
                                        .show();

                                    // $tager.trigger(e.type) doesn't work for the "delete post" link, so use passed function:
                                    if(typeof retrigger_action == 'function') {
                                        retrigger_action();
                                    }

                                }

                            })
                            .fail(function(){ // Ajax request failure

                                $modal_fields.show();
                                $message
                                    .removeClass('success-message')
                                    .addClass('error-message')
                                    .html($message.data('error-message'))
                                    .show();

                            })
                            .always(function(){
                                $loading.hide();
                            });

                    }
                }],
                // Make Dialog position fixed & fix the z-index issue:
                create: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                },
                resizeStart: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                },
                resizeStop: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                }
            });

            $modal.data('leyka-dialog-initialized', 1);

        }

    }

    $('.submitdelete.deletion').on('click.leyka', function(e){

        let $this = $(this),
            campaign_original_status = $form.find('#original_post_status').val(),
            campaign_updated_is_finished = $form.find('[name="is_finished"]').prop('checked');

        if(campaign_original_status !== 'publish' || campaign_updated_is_finished) {
            return;
        }

        // The Support packages check passed - submit the campaign changes normally:
        if($modal.data('leyka-support-packages-campaign-checked')) {
            return;
        }

        e.preventDefault();

        leyka_support_packages_campaign_deactivation_dialog($modal, function(){
            window.location.href = $this.attr('href');
        });

    });
    $form.on('submit.leyka', function(e){

        /** @todo Get $campaign_needed_field value via ajax, mb */
        if( !$campaign_needed_field.length || !parseInt($campaign_needed_field.val()) ) {
            return;
        }

        let $this = $(this),
            campaign_updated_status = $form.find('[name="post_status"]').val(),
            campaign_updated_is_finished = $form.find('[name="is_finished"]').prop('checked');

        // The campaign is published, so check won't be needed:
        if(campaign_updated_status === 'publish' && !campaign_updated_is_finished) {
            return;
        }

        // The Support packages check passed - submit the campaign changes normally:
        if($modal.data('leyka-support-packages-campaign-checked')) {
            return;
        }

        e.preventDefault();

        leyka_support_packages_campaign_deactivation_dialog($modal, function(){
            $this.trigger(e.type);
        });

    });

    $modal.on('change.leyka', '[name="support-packages-campaign-changed"]', function(){

        let $this = $(this),
            $new_campaign = $modal.find('.new-campaign');

        if($this.val() === 'another-campaign') {
            $new_campaign.show();
        } else {
            $new_campaign.hide();
        }

    });

});
/* Support packages Extension - available campaign existence check - END */