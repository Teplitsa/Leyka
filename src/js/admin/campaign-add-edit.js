// Campaign add/edit page:
jQuery(document).ready(function($){

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

    // Additional CSS value reset:
    $('.css-editor-reset-value').on('click.leyka', function(e){

        e.preventDefault();

        let $this = $(this),
            $css_editor_field = $this.siblings('.css-editor-field'),
            original_value = $this.siblings('.css-editor-original-value').val();

        $css_editor_field.val(original_value);
        editor.codemirror.getDoc().setValue(original_value);

    });

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

    // Custom CSS editor:
    let $css_editor = $('.css-editor-field');
    let editor = {};
    if($css_editor.length) {

        let editor_settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editor_settings.codemirror = _.extend({
            },
            editor_settings.codemirror, {
            indentUnit: 2,
            tabSize: 2,
            mode: 'css',
        });
        editor = wp.codeEditor.initialize($css_editor, editor_settings);
    }

    // campaign cover type
    $('#campaign-cover-type input[type=radio]').change(function(){
    	if($(this).prop('checked')) {
    		if($(this).val() == 'color') {
    			$('#campaign-cover-bg-color').show();
    			$('#upload-campaign-cover-image').hide();
    		}
    		else {
    			$('#campaign-cover-bg-color').hide();
    			$('#upload-campaign-cover-image').show();
    		}
    	}
    });
    $('#campaign-cover-type input[type=radio]:checked').change();
    
    // reset uploaded image to default
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

        console.log(ajax_params);
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

});