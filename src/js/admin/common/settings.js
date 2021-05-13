/** Common settings functions */

jQuery(document).ready(function($){

    const $body = $('body');

    // Datepicker fields for admin lists filters:
    jQuery.leyka_fill_datepicker_input_period = function leyka_fill_datepicker_input_period(inst, extension_range) {

        let input_text = extension_range.startDateText;
        if(extension_range.endDateText && extension_range.endDateText !== extension_range.startDateText) {
            input_text += ' - '+extension_range.endDateText;
        }
        $(inst.input).val(input_text);

    };

    jQuery.leyka_init_filter_datepicker = function leyka_init_filter_datepicker($input, options) {

        $input.datepicker({
            range: 'period',
            onSelect:function(dateText, inst, extensionRange){
                $.leyka_fill_datepicker_input_period(inst, extensionRange);
            },

            beforeShow: function(input, inst) {
                let selectedDatesStr = $(input).val(),
                    selectedDatesStrList = selectedDatesStr.split(' - '),
                    selectedDates = [];

                for(let i in selectedDatesStrList) {
                    if(selectedDatesStrList[i]) {

                        let singleDate;
                        try {
                            singleDate = $.datepicker.parseDate($(input).datepicker('option', 'dateFormat'), selectedDatesStrList[i]);
                        } catch {
                            singleDate = new Date();
                        }

                        selectedDates.push(singleDate);
                    }
                }

                $(inst.input).val(selectedDates[0]);
                $(inst.input).datepicker('setDate', selectedDates);

                setTimeout(function(){
                    $.leyka_fill_datepicker_input_period(inst, $(inst.dpDiv).data('datepickerExtensionRange'));
                });

            }
        });

    };
    // Datepicker fields for admin lists filters - END

    if(leyka_ui_widget_available('accordion')) {
        $('.ui-accordion').each(function(){

            let $this = $(this),
                widget_options = {heightStyle: 'content',};

            $this.accordion(widget_options);

        });
    }

    if(leyka_ui_widget_available('wpColorPicker', $.wp)) {
        $('.leyka-setting-field.colorpicker').wpColorPicker({ // Colorpicker fields
            change: function(e, ui) {
                $(e.target).parents('.field').find('.leyka-colorpicker-value').val(ui.color.toString()).change();
            }
        });
    }

    if(leyka_ui_widget_available('selectmenu')) {
        $('.leyka-select-menu').selectmenu();
    }

    if(leyka_ui_widget_available('autocomplete')) {

        // $body.find('.leyka-autocomplete').each(function(){

        //     let $field = $(this),
        //         source_type = $field.data('leyka-autocomplete-source-type'),
        //         pre_selected_values = $field.data('leyka-autocomplete-pre-selected-values'),
        //         params = {source: false};
        //
        //     if(source_type === 'url') { // data-leyka-autocomplete-source is an URL for values ajax requests
        //
        //         params.source = $field.data('leyka-autocomplete-source');
        //
        //         if( !params.source || !params.source.length ) {
        //             return;
        //         }
        //
        //     } else if(source_type === 'select-field') { // data-leyka-autocomplete-source is an ID for select field with values
        //
        //         let $source_select = $($field.data('leyka-autocomplete-source')).hide(); // Hide the select field just in case
        //
        //         if( !$source_select.length ) {
        //             return;
        //         }
        //
        //         params.source = [];
        //
        //         $source_select.find('option').each(function(){
        //
        //             let $option = $(this);
        //
        //             params.source.push({label: $.trim($option.text()), value: $option.val()});
        //
        //         });
        //
        //         if( !params.source.length ) {
        //             return;
        //         }
        //
        //     }
        //
        //     if(pre_selected_values && pre_selected_values === 'from-source-field' && source_type === 'select-field') {
        //
        //     } else if(pre_selected_values) {
        //
        //         let $values_select = $(pre_selected_values);
        //     }
        //
        //
        //
        //     if( !!$field.data('leyka-autocomplete-multiselect') ) {
        //         params.multiselect = true;
        //     }
        //     if( !!$field.data('leyka-autocomplete-min-length') ) {
        //         params.minLength = $field.data('leyka-autocomplete-min-length');
        //     }
        //     if( !!$field.data('leyka-autocomplete-search-on-focus') ) {
        //         params.search_on_focus = true;
        //     }
        //
            /** @todo Finish the universalization for autocomplete fields. We need an API for params: source, pre_selected_values, leyka_select_callback */
        //
        //     $field.autocomplete(params);
        //
        // });

    }

    // Support metaboxes ONLY where needed (else there are metabox handling errors on the wrong pages):
    $('input.leyka-support-metabox-area').each(function(){
        leyka_support_metaboxes($(this).val());
    });

    // Custom CSS editor fields:
    let $css_editor = $('.css-editor-field'),
        editor = {};

    if(leyka_ui_widget_available('codeEditor', wp) && $css_editor.length) {

        let editor_settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editor_settings.codemirror = _.extend(
            {},
            editor_settings.codemirror, {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css',
            });
        editor = wp.codeEditor.initialize($css_editor, editor_settings);

        $css_editor.data('code-editor-object', editor);

        $('.css-editor-reset-value').on('click.leyka', function(e){ // Additional CSS value reset

            e.preventDefault();

            let $this = $(this),
                $css_editor_field = $this.siblings('.css-editor-field'),
                template_id = $this
                    .parents('.campaign-css')
                    .siblings('.campaign-template')
                        .find('[name="campaign_template"]').val(),
                original_value = $this.siblings('.css-editor-'+template_id+'-original-value').val();

            $css_editor_field.val(original_value);
            editor.codemirror.getDoc().setValue(original_value);

        });

    }
    // Custom CSS editor fields - END

    // Ajax file upload fields support:
    $body.on('click.leyka', '.upload-field input[type="file"]', function(e){ // Just to be sure that the input will be called
        e.stopPropagation();
    }).on('change.leyka', '.upload-field input[type="file"]', function(e){

        if( !e.target.files ) {
            return;
        }

        let $file_input = $(this),
            $field_wrapper = $file_input.parents('.leyka-file-field-wrapper'),
            option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $ajax_loading = $field_wrapper.find('.loading-indicator-wrap'),
            $error = $field_wrapper.siblings('.field-errors'),
            $main_field = $field_wrapper.find('input.leyka-upload-result'),
            data = new FormData(); // Need to use a FormData object here instead of a generic object

    // console.log('File:', $file_input, 'Wrapper:', $field_wrapper);

        data.append('action', 'leyka_files_upload');
        data.append('option_id', option_id);
        data.append('nonce', $file_input.data('nonce'));
        data.append('files', []);

        $.each(e.target.files, function(key, value){
            data.append('files', value);
        });

        $ajax_loading.show();
        $error.html('').hide();

        $.ajax({
            url: leyka.ajaxurl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(response){

                $ajax_loading.hide();

                if(
                    typeof response === 'undefined'
                    || typeof response.status === 'undefined'
                    || (response.status !== 0 && typeof response.message === 'undefined')
                ) {
                    return $error.html(leyka.common_error_message).show();
                } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                    return $error.html(response.message).show();
                }

                let preview_html = response.type.includes('image/') ?
                    '<img class="leyka-upload-image-preview" src="'+response.url+'" alt="">' : response.filename;

                $file_preview.show().find('.file-preview').html(preview_html);

                $main_field.val(response.path); // Option value will keep the file relative path in WP uploads dir

            },
            error: function(){

                $ajax_loading.hide();
                $error.html(leyka.common_error_message).show();

            }
        });

    });

    $body.on('click.leyka', '.leyka-file-field-wrapper .delete-uploaded-file', function(e){ // Mark uploaded file to be removed

        e.preventDefault();

        let $delete_link = $(this),
            $field_wrapper = $delete_link.parents('.leyka-file-field-wrapper'),
            // option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result');

        $file_preview.hide().find('.file-preview').html('');
        $main_field.val('');

    });
    // Ajax file upload fields - END

    // Campaigns select comboboxes fields:
    $body.find('.leyka-campaign-select-field-wrapper').each(function(){

        let $field_wrapper = $(this),
            $text_search_sub_field = $field_wrapper.find('input.leyka-campaign-selector'),
            $value_sub_vield = $field_wrapper.find('input.campaign-id');

        $text_search_sub_field.autocomplete({
            minLength: 0,
            focus: function(event, ui){

                $text_search_sub_field.val(ui.item.label);
                return false;

            },
            change: function(event, ui){
                if( !$text_search_sub_field.val() ) {
                    $value_sub_vield.val('');
                }
            },
            close: function(event, ui){
                if( !$text_search_sub_field.val() ) {
                    $value_sub_vield.val('');
                }
            },
            select: function(event, ui){

                $text_search_sub_field.val(ui.item.label);
                $value_sub_vield.val(ui.item.value);

                return false;

            },
            source: function(request, response) {

                let term = request.term,
                    cache = $text_search_sub_field.data('cache') ? $text_search_sub_field.data('cache') : [];

                if(term in cache) {

                    response(cache[term]);
                    return;

                }

                request.action = 'leyka_get_campaigns_list';
                request.nonce = $text_search_sub_field.data('nonce');

                $.getJSON(leyka.ajaxurl, request, function(data){

                    var cache = $text_search_sub_field.data('cache') ? $text_search_sub_field.data('cache') : [];

                    cache[term] = data;
                    response(data);

                });

            }
        }).on('focus.leyka', function(e){
            if($value_sub_vield.val() == 0) {
                $(this).autocomplete('search', '');
            }
        });

        $text_search_sub_field.data('ui-autocomplete')._renderItem = function(ul, item){
            return $('<li>')
                .append(
                    '<a>'+item.label+(item.label === item.payment_title ? '' : '<div>'+item.payment_title+'</div></a>')
                )
                .appendTo(ul);
        };

    });
    // Campaigns select comboboxes fields - END

    // Expandable options sections (portlets only):
    /** @todo Remove this completely when all portlets are converted to metaboxes */
    $('.leyka-options-section .header h3').click(function(e){

        e.preventDefault();

        $(this).closest('.leyka-options-section').toggleClass('collapsed');

    });

    // Delete fields comments:
    // $('.leyka-admin .leyka-options-section .field-component.help').contents().filter(function(){
    //     return this.nodeType === 1 || this.nodeType === 3; // 1 is for links, 3 - for plain text
    // }).remove();

    // Connect to stats:
    if($('#leyka_send_plugin_stats-y-field').prop('checked')) {

        $('.leyka-options-section#stats_connections')
            .find('.submit input')
            .removeClass('button-primary')
            .addClass('disconnect-stats')
            .val(leyka.disconnect_stats);

    }

    $('#connect-stats-button').click(function(){
        if($(this).hasClass('disconnect-stats')) {
            $('#leyka_send_plugin_stats-n-field').prop('checked', true);
        } else {
            $('#leyka_send_plugin_stats-y-field').prop('checked', true);
        }
    });

    // Section tabs:
    $('.section-tab-nav-item').click(function(e){

        e.preventDefault();

        let $tabs = $(this).closest('.section-tabs-wrapper');

        $tabs.find('.section-tab-nav-item').removeClass('active');
        $tabs.find('.section-tab-content').removeClass('active');

        $(this).addClass('active');
        $tabs.find('.section-tab-content.tab-' + $(this).data('target')).addClass('active');

    });

    // Screenshots nav:
    $('.tab-screenshot-nav img').click(function(e){

        e.preventDefault();

        let $currentScreenshots = $(this).closest('.tab-screenshots'),
            $currentVisibleScreenshot = $currentScreenshots.find('.tab-screenshot-item.active'),
            $nextScreenshot = null;

        if($(this).closest('.tab-screenshot-nav').hasClass('left')) {
            $nextScreenshot = $currentVisibleScreenshot.prev();
            if(!$nextScreenshot.hasClass('tab-screenshot-item')) {
                $nextScreenshot = $currentScreenshots.find('.tab-screenshot-item').last();
            }
        } else {
            $nextScreenshot = $currentVisibleScreenshot.next();
            if(!$nextScreenshot.hasClass('tab-screenshot-item')) {
                $nextScreenshot = $currentScreenshots.find('.tab-screenshot-item').first();
            }
        }

        if($nextScreenshot) {
            $currentVisibleScreenshot.removeClass('active');
            $nextScreenshot.addClass('active');
        }

    });

    $('[name*="show_donation_comment_field"]').on('change.leyka', function(){

        var $this = $(this),
            checkbox_id = $this.attr('id'),
            length_field_wrapper_id = checkbox_id.replace('_show_donation_comment_field-field', '_donation_comment_max_length-wrapper');

        if($this.prop('checked')) {
            $('#'+length_field_wrapper_id).show();
        } else {
            $('#'+length_field_wrapper_id).hide();
        }

    }).change();

    // Manual emails sending:
    $('.send-donor-thanks').click(function(e){

        e.preventDefault();

        var $this = $(this),
            $wrap = $this.parent(),
            donation_id = $wrap.data('donation-id');

        $this.fadeOut(100, function(){
            $this.html('<img src="'+leyka.ajax_loader_url+'" alt="">').fadeIn(100);
        });

        $wrap.load(leyka.ajaxurl, {
            action: 'leyka_send_donor_email',
            nonce: $wrap.find('#_leyka_donor_email_nonce').val(),
            donation_id: donation_id
        });

    });

    // Exchange places of donations Export and Filter buttons:
    $('.wrap a.page-title-action').after($('.donations-export-form').detach());

    // Tooltips:
    var $tooltips = $('.has-tooltip');
    if($tooltips.length && typeof $().tooltip !== 'undefined' ) {
        $tooltips.tooltip();
    }

    // Campaign selection fields:
    /** @todo Change this old campaigns select field code (pure jq-ui-autocomplete-based) to the new code (select + autocomplete, like on the Donors list page filters). */
    var $campaign_select = $('#campaign-select');
    if($campaign_select.length && typeof $().autocomplete !== 'undefined') {

        $campaign_select.keyup(function(){
            if( !$(this).val() ) {
                $('#campaign-id').val('');
                $('#new-donation-purpose').html('');
            }
        });

        $campaign_select.autocomplete({
            minLength: 1,
            focus: function(event, ui){
                $campaign_select.val(ui.item.label);
                $('#new-donation-purpose').html(ui.item.payment_title);

                return false;
            },
            change: function(event, ui){
                if( !$campaign_select.val() ) {
                    $('#campaign-id').val('');
                    $('#new-donation-purpose').html('');
                }
            },
            close: function(event, ui){
                if( !$campaign_select.val() ) {
                    $('#campaign-id').val('');
                    $('#new-donation-purpose').html('');
                }
            },
            select: function(event, ui){
                $campaign_select.val(ui.item.label);
                $('#campaign-id').val(ui.item.value);
                $('#new-donation-purpose').html(ui.item.payment_title);
                return false;
            },
            source: function(request, response) {
                var term = request.term,
                    cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];

                if(term in cache) {
                    response(cache[term]);
                    return;
                }

                request.action = 'leyka_get_campaigns_list';
                request.nonce = $campaign_select.data('nonce');

                $.getJSON(leyka.ajaxurl, request, function(data, status, xhr){

                    var cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];

                    cache[term] = data;
                    response(data);
                });
            }
        });

        $campaign_select.data('ui-autocomplete')._renderItem = function(ul, item){
            return $('<li>')
                .append(
                    '<a>'+item.label+(item.label == item.payment_title ? '' : '<div>'+item.payment_title+'</div></a>')
                )
                .appendTo(ul);
        };

    }

    // Multi-valued item complex fields:
    $('.leyka-main-multi-items').each(function(index, outer_items_wrapper){

        let $items_wrapper = $(outer_items_wrapper),
            $item_template = $items_wrapper.siblings('.item-template'),
            $add_item_button = $items_wrapper.siblings('.add-item'),
            items_cookie_name = $items_wrapper.data('items-cookie-name'),
            closed_boxes = typeof $.cookie(items_cookie_name) === 'string' ?
                JSON.parse($.cookie(items_cookie_name)) : [];

        if($.isArray(closed_boxes)) { // Close the item boxes needed
            $.each(closed_boxes, function(key, value){
                $items_wrapper.find('#'+value).addClass('closed');
            });
        }

        $items_wrapper.sortable({
            placeholder: 'ui-state-highlight', // A class for dropping item placeholder
            update: function(event, ui){

                let items_options = [];
                $.each($items_wrapper.sortable('toArray'), function(key, item_id){ // Value is an item ID (generated randomly)

                    let item_options = {'id': item_id}; // Assoc. array key should be initialized explicitly

                    $.each($items_wrapper.find('#'+item_id).find(':input'), function(key, item_setting_input){

                        let $input = $(item_setting_input),
                            input_name = $input.prop('name')
                                .replace($items_wrapper.data('item-inputs-names-prefix'), '')
                                .replace('[]', '');

                        if($input.prop('type') === 'checkbox') {
                            item_options[input_name] = $input.prop('checked');
                        } else {
                            item_options[input_name] = $input.val();
                        }

                    });

                    items_options.push(item_options);

                });

                $items_wrapper.siblings('input.leyka-items-options').val(
                    encodeURIComponent(JSON.stringify(items_options))
                );

            }
        });

        $items_wrapper.on('click.leyka', '.item-box-title .title', function(e){

            let $this = $(this),
                $current_box = $this.parents('.multi-valued-item-box');

            $current_box.toggleClass('closed');

            // Save the open/closed state for all items boxes:
            let current_box_id = $current_box.prop('id'),
                current_box_index = $.inArray(current_box_id, closed_boxes);

            if(current_box_index === -1 && $current_box.hasClass('closed')) {
                closed_boxes.push(current_box_id);
            } else if(current_box_index !== -1 && !$current_box.hasClass('closed')) {
                closed_boxes.splice(current_box_index, 1);
            }

            $.cookie(items_cookie_name, JSON.stringify(closed_boxes));

        });

        $items_wrapper.on('click.leyka', '.delete-item', function(e){

            e.preventDefault();

            if($items_wrapper.find('.multi-valued-item-box').length > $items_wrapper.data('min-items')) {

                $(this).parents('.multi-valued-item-box').remove();
                $items_wrapper.sortable('option', 'update')();

            }

            let items_current_count = $items_wrapper.find('.multi-valued-item-box').length;
            if($items_wrapper.data('min-items') && items_current_count <= $items_wrapper.data('min-items')) {
                $items_wrapper.find('.delete-item').addClass('inactive');
            }
            if(items_current_count < $items_wrapper.data('max-items')) {
                $add_item_button.removeClass('inactive');
            }

        });

        $add_item_button.on('click.leyka', function(e){

            e.preventDefault();

            if($add_item_button.hasClass('inactive')) {
                return;
            }

            // Generate & set the new item ID:
            let new_item_id = '';
            do {
                new_item_id = leyka_get_random_string(4);
            } while($items_wrapper.find('#item-'+new_item_id).length);

            $item_template
                .clone()
                .appendTo($items_wrapper)
                .removeClass('item-template')
                .prop('id', 'item-'+new_item_id)
                .show();

            $items_wrapper.sortable('option', 'update')();

            let items_current_count = $items_wrapper.find('.multi-valued-item-box').length;

            if($items_wrapper.data('max-items') && items_current_count >= $items_wrapper.data('max-items')) {
                $add_item_button.addClass('inactive');
            }

            if(items_current_count <= 1) { // When adding initial item
                $items_wrapper.find('.delete-item').addClass('inactive');
            } else if(items_current_count > 1) {
                $items_wrapper.find('.delete-item').removeClass('inactive');
            }

        });

        // No items added yet - add the first (empty) one:
        if($items_wrapper.data('show-new-item-if-empty') && !$items_wrapper.find('.multi-valued-item-box').length) {
            $add_item_button.trigger('click.leyka');
        }

        // Refresh the main items option value before submit:
        $items_wrapper.parents('form:first').on('submit.leyka', function(e){
            $items_wrapper.sortable('option', 'update')();
        });

    });
    // Multi-valued item complex fields - END

    // Donors management & Donors' accounts fields logical link:
    $('input[name="leyka_donor_accounts_available"]').change(function(){

        let $accounts_available_field = $(this),
            $donors_management_available_field = $('input[name="leyka_donor_management_available"]');

        if($accounts_available_field.prop('checked')) {
            $donors_management_available_field
                .prop('checked', 'checked')
                .prop('disabled', 'disabled')
                .parents('.field-component').addClass('disabled');
        } else {
            $donors_management_available_field
                .prop('disabled', false)
                .parents('.field-component').removeClass('disabled');
        }

    }).change();

});