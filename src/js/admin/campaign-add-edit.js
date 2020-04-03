// Campaign add/edit page:
jQuery(document).ready(function($){

    var $page_type = $('#originalaction'),
        $post_type = $('#post_type');

    if( !$page_type.length || $page_type.val() !== 'editpost' || !$post_type.length || $post_type.val() !== 'leyka_campaign' ) {
        return;
    }

    // Campaign type change:
    $(':input[name="campaign_type"]').on('change.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        if( !$this.prop('checked') ) {
            return;
        }

        let $persistent_campaign_fields = $('.persistent-campaign-field'),
            $temp_campaign_fields = $('.temporary-campaign-fields'),
            $form_template_field = $(':input[name="campaign_template"]');

        if($this.val() === 'persistent') {

            $persistent_campaign_fields.show();
            $temp_campaign_fields.hide();

            $form_template_field
                .data('prev-value', $form_template_field.val())
                .val('star')
                .prop('disabled', 'disabled');

        } else {

            $persistent_campaign_fields.hide();
            $temp_campaign_fields.show();

            if($form_template_field.data('prev-value')) {
                $form_template_field.val($form_template_field.data('prev-value'));
            }
            $form_template_field.removeProp('disabled');

        }

    }).change();
    
    // Donation types field change:
    let $donations_types_fields = $(':input[name="donations_type[]"]'),
        $default_donation_type_field_block = $('#donation-type-default');
    $donations_types_fields.on('change.leyka', function(e){

        e.preventDefault();

        let donations_types_selected = [];
        $donations_types_fields.filter(':checked').each(function(){
            donations_types_selected.push($(this).val());
        });

        if(donations_types_selected.length > 1) {
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
    // Form templates screens demo - end

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

            // disableForm(); /** @todo */
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
                        return;
                    }
                    else {
                    	$img_wrapper.find('.img-value').html('<img src="'+json.img_url+'" />');
                    	$img_wrapper.find('.reset-to-default').show();
                    }

                    // reloadPreviewFrame(); /** @todo */

                })
                .fail(function(){
                    alert('Ошибка!');
                })
                .always(function(){

                    $loading.hide();
                    // enableForm(); /** @todo */

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
    $('#campaign-cover-type input[type=radio]:checked').change();
    
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
                    return;
                }
                else {
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

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {
        $('.leyka-data-table').DataTable({
            'lengthMenu': [[25, 50, 100, 200], [25, 50, 100, 200]],
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

    // campaign template change
    $(':input[name="campaign_template"]').on('change.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        if($this.val() === 'star') {
    		$('#campaign-css').show();
        } else {
        	$('#campaign-css').hide();
        }

    }).change();

    // Support packages Extension - available campaign existence check:
    if(typeof($().dialog) === 'undefined') {
        return;
    }

    let $campaign_needed_field = $('input#leyka-campaign-needed-for-support-packages'),
        $modal = $('#leyka-campaign-needed-modal-content');

    if( !$modal.length ) {
        return;
    }

    $('form#post').submit(function(e){

        /** @todo Get $campaign_needed_field value via ajax, mb */
        if( !$campaign_needed_field.length || !parseInt($campaign_needed_field.val()) ) {
            return;
        }

        let $form = $(this),
            campaign_updated_status = $form.find('[name="post_status"]').val(),
            campaign_updated_type = $form.find('[name="campaign_type"]:checked').val(),
            campaign_updated_is_finished = $form.find('[name="is_finished"]').prop('checked');

        if(campaign_updated_status === 'publish' && !campaign_updated_is_finished && campaign_updated_type === 'persistent') {
            return;
        }

        // If the packages behavior selected, submit the campaign changes normally:
        if($modal.data('leyka-support-packages-campaign-behavior')) {
            return;
        }

        e.preventDefault();

        if($modal.data('leyka-dialog-initialized')) {
            $modal.dialog('open');
        } else {

            $modal.dialog({
                dialogClass: 'leyka-dialog',
                modal: true,
                draggable: false,
                width: 'auto',
                autoOpen: true,
                closeOnEscape: true,
                resizable: false,
                buttons: [{
                    'text': 'Close',
                    'class': 'button-secondary',
                    'click': function(){
                        $modal.dialog('close');
                    }
                }, {
                    'text': 'Apply',
                    'class': 'button-primary',
                    'click': function(){

                        let $extension_behavior = $modal.find('[name="support-packages-campaign-changed"]:checked');
                        $extension_behavior.appendTo($form);

                        $modal.find('[name="leyka_support_packages_campaign"]').appendTo($form);
                        $modal
                            .dialog('close')
                            .data('leyka-support-packages-campaign-behavior', $extension_behavior.val());

                        $form.submit();

                    }
                }],
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