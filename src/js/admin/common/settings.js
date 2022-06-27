/** Common settings functions */

jQuery(document).ready(function($){

    const $body = $('body');

    // Normal datepicker fields:
    $('input.leyka-datepicker').each(function(){

        let $date_field = $(this);

        $date_field.datepicker({
            changeMonth: true,
            changeYear: true,
            minDate: $date_field.data('min-date') ? $date_field.data('min-date') : '',
            maxDate: $date_field.data('max-date') ? $date_field.data('max-date') : '',
            dateFormat: $date_field.data('date-format') ? $date_field.data('date-format') : 'dd.mm.yy',
            altField: $date_field.data('alt-field') ? $date_field.data('alt-field') : '', // Alt field jQuery selector here
            altFormat: $date_field.data('alt-format') ? $date_field.data('alt-format') : 'yy-mm-dd',
        });

    });
    // Normal datepicker fields - END

    // Ranged (optionally) datepicker fields for admin lists filters:
    jQuery.leyka_fill_datepicker_input_period = function leyka_fill_datepicker_input_period(inst, extension_range) {

        let input_text = extension_range.startDateText;
        if(extension_range.endDateText && extension_range.endDateText !== extension_range.startDateText) {
            input_text += ' - '+extension_range.endDateText;
        }
        $(inst.input).val(input_text);

    };

    jQuery.leyka_admin_filter_datepicker_ranged = function($input /*, options*/){

        $input.datepicker({
            range: 'period',
            onSelect:function(dateText, inst, extensionRange){
                $.leyka_fill_datepicker_input_period(inst, extensionRange);
            },

            beforeShow: function(input, instance) {

                let selectedDatesStr = $(input).val(),
                    selectedDatesStrList = selectedDatesStr.split(' - '),
                    selectedDates = [];

                for(let i in selectedDatesStrList) {

                    if(selectedDatesStrList[i]) {

                        let singleDate;
                        try {
                            singleDate = $.datepicker
                                .parseDate($(input).datepicker('option', 'dateFormat'), selectedDatesStrList[i]);
                        } catch {
                            singleDate = new Date();
                        }

                        selectedDates.push(singleDate);

                    }

                }

                $(instance.input).val(selectedDates[0]);
                $(instance.input).datepicker('setDate', selectedDates);

                setTimeout(function(){
                    $.leyka_fill_datepicker_input_period(instance, $(instance.dpDiv).data('datepickerExtensionRange'));
                });

            }
        });

    };
    // Ranged (optionally) datepicker fields for admin lists filters - END

    // Ranged datepicker fields (for admin list filters mostly):
    $.leyka_admin_filter_datepicker_ranged($('input.datepicker-ranged-selector'), {
        warningMessage: leyka.first_donation_date_incomplete_message
    });
    // Ranged datepicker fields - END

    // Campaigns autocomplete select:
    jQuery.leyka_admin_campaigns_select = function($text_selector_field /*, options*/){

        $text_selector_field = $($text_selector_field);

        let $list_select_field = $text_selector_field.siblings('.leyka-campaigns-select'),
            is_multiple_values = !!$list_select_field.prop('multiple'),
            selected_values = [];

        if(is_multiple_values) {
            $list_select_field.find('option').each(function(){

                let $this = $(this);
                selected_values.push({item: {label: $.trim($this.text()), value: $this.val()}});

            });
        }

        let autocomplete_settings = {
            source: leyka.ajaxurl+'?action=leyka_campaigns_autocomplete',
            multiselect: is_multiple_values,
            minLength: 0,
            search_on_focus: true,
        };

        if(is_multiple_values) {

            autocomplete_settings.pre_selected_values = selected_values;
            autocomplete_settings.leyka_select_callback = function(selected_items){

                $list_select_field.html('');

                for(let value in selected_items) {
                    $('<option></option>').val(value).prop('selected', true).appendTo($list_select_field);
                }

            }

        } else {
            autocomplete_settings.select = function(e, ui){

                this.value = ui.item.label;
                $list_select_field.val(ui.item.value);

                if($list_select_field.data('campaign-payment-title-selector')) {
                    $($list_select_field.data('campaign-payment-title-selector')).html(ui.item.payment_title);
                }

                return false;

            };
        }

        $text_selector_field.autocomplete(autocomplete_settings);

    };

    // Campaign(s) select fields (for admin list filters mostly):
    $('input.leyka-campaigns-selector:not(.leyka-js-dont-initialize-common-widget)').each(function(){
        $.leyka_admin_campaigns_select($(this));
    });
    // Campaign(s) select fields  - END

    // Donor's name/email field:
    $('input.leyka-donor-name-email-selector').each(function(){

        let $field = $(this);

        $field.autocomplete({ /** @todo Add nonce to the query */
            source: leyka.ajaxurl+'?action=leyka_donors_autocomplete'
                +($field.data('search-donors-in') ? '&type='+$field.data('search-donors-in') : ''),
            minLength: 0,
            search_on_focus: true
        });

    });
    // Donor's name/email field - END

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

        if(leyka_is_gutenberg_active()) {

            wp.data.subscribe(function(){ // Obtain the CodeMirror instance, then manually copy editor content into it's textarea
                $css_editor.next('.CodeMirror').get(0).CodeMirror.save();
            });

        }

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
    $body.on('click.leyka', '.file-upload-field input[type="file"]', function(e){ // Just to be sure that the input will be called
        e.stopPropagation();
    }).on('change.leyka', '.file-upload-field input[type="file"]', function(e){

        if( !e.target.files ) {
            return;
        }

        let $file_input = $(this),
            $field_wrapper = $file_input.parents('.leyka-file-field-wrapper'),
            $upload_button_wrapper = $field_wrapper.find('.upload-field'),
            option_id = $upload_button_wrapper.data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $ajax_loading = $field_wrapper.find('.loading-indicator-wrap'),
            $error = $field_wrapper.siblings('.field-errors'),
            $main_field = $field_wrapper.find('input.leyka-upload-result'),
            data = new FormData(); // Need to use a FormData object here instead of a generic object

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
                $upload_button_wrapper.hide(); // Hide the "upload" button when picture is uploaded

                $main_field.val(response.path); // Option value will keep the file relative path in WP uploads dir

            },
            error: function(){

                $ajax_loading.hide();
                $error.html(leyka.common_error_message).show();

            }
        });

    });

    $body.on('click.leyka', '.leyka-upload-field-wrapper .delete-uploaded-file', function(e){ // Mark uploaded file to be removed

        e.preventDefault();

        let $delete_link = $(this),
            $field_wrapper = $delete_link.parents('.leyka-upload-field-wrapper'),
            // option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result');

        $file_preview.hide().find('.file-preview').html('');
        $field_wrapper.find('.upload-field').show(); // Show the "upload" button when uploaded picture is deleted
        $main_field.val('');

    });
    // Ajax file upload fields - END

    // Media library upload fields:
    $body.on('click.leyka', '.media-upload-field', function(e){

        e.preventDefault();

        let $field = $(this),
            $field_wrapper = $field.parents('.leyka-media-upload-field-wrapper'),
            // option_id = $upload_button_wrapper.data('option-id'),
            $preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result'),
            media_uploader = wp.media({
                title: $field.data('upload-title') ?
                    $field.data('upload-title') : leyka.media_upload_title,
                button: {
                    text: $field.data('upload-button-label') ? $field.data('upload-button-label') : leyka.media_upload_button_label,
                },
                library: {type: $field.data('upload-files-type') ? $field.data('upload-files-type') : 'image'},
                multiple: $field.data('upload-is-multiple') ? !!$field.data('upload-is-multiple') : false
            }).on('select', function(){ // It's a wp.media event, so dont't use "select.leyka" events types

                let attachment = media_uploader.state().get('selection').first().toJSON();

                $preview
                    .show()
                    .find('.file-preview')
                    .html('<img class="leyka-upload-image-preview" src="'+attachment.url+'" alt="">');

                $field.hide(); // Hide the "upload" button when picture is uploaded

                $main_field.val(attachment.id);

            }).open();

    });
    // Media library upload fields - END

    // Expandable options sections (portlets only):
    /** @todo Remove this completely when all portlets are converted to metaboxes */
    $('.leyka-options-section .header h3').click(function(e){

        e.preventDefault();

        $(this).closest('.leyka-options-section').toggleClass('collapsed');

    });

    // "Copy 2 clipboard" universal feature:
    function leyka_copy_text2clipboard(text2copy) {

        let $copy_buffer_input = $('<input>').appendTo('body');

        $copy_buffer_input.val(text2copy).select();
        document.execCommand('copy');
        $copy_buffer_input.remove();

    }

    function leyka_add_copy_controls($text2copy_container) {

        let $copy_link = $('<span>');
        $copy_link
            .addClass('copy-control')
            .addClass('copy-link')
            .text(leyka.copy2clipboard_text)
            .appendTo($text2copy_container);

        let $copy_done = $('<span>');
        $copy_done
            .addClass('copy-control')
            .addClass('copy-done')
            .text(leyka.copied2clipboard_text)
            .appendTo($text2copy_container);

    }

    function leyka_collect_text2copy($copy_link) {

        let $wrapper = $copy_link.parents('.leyka-copy-on-click'),
            $text = $wrapper.find('.copy-content');

        if($text.length) {
            return $.trim($text.text());
        }

        $wrapper = $wrapper.clone();
        $wrapper.find('.copy-link, .copy-done').remove();

        let $inner_control = $wrapper.find('input[type="text"], input[type="color"], input[type="date"], input[type="datetime-local"], input[type="month"], input[type="email"], input[type="number"], input[type="search"], input[type="range"], input[type="search"], input[type="tel"], input[type="time"], input[type="url"], input[type="week"], textarea');

        return $.trim($inner_control.length ? $inner_control.val() : $wrapper.text());

    }

    $('.leyka-copy-on-click').each(function(){

        let $wrapper = $(this),
            tmp_content = $wrapper.text();

        $wrapper.text('').append('&nbsp;<span class="copy-content">' + tmp_content + '</span>');

        leyka_add_copy_controls($wrapper);

    });

    $('.copy-link').on('click.leyka', function(e){

        e.preventDefault();

        let $copy_link = $(this);

        leyka_copy_text2clipboard(leyka_collect_text2copy($copy_link));

        $copy_link.fadeOut(function(){

            $copy_link.siblings('.copy-done').show();

            setTimeout(function(){
                $copy_link.siblings('.copy-done').hide();
                $copy_link.show();
            }, 2000);

        });

    });
    // "Copy 2 clipboard" universal feature - END

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

        let $this = $(this),
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

        let $this = $(this),
            $wrap = $this.parent(),
            donation_id = $wrap.data('donation-id');

        $this.fadeOut(100, function(){
            $this.html('<img src="'+leyka.ajax_loader_url+'" alt="">').fadeIn(100);
        });

        $wrap.load(leyka.ajaxurl, {
            action: 'leyka_send_donor_email',
            nonce: $wrap.data('nonce'),
            donation_id: donation_id
        });

    });

    // Tooltips:
    let $tooltips = $body.find('.has-tooltip');

    $.widget('custom.leyka_admin_tooltip', $.ui.tooltip, {
        _init: function(){

            this._super(); // Parent _init() method call, just in case

            let $tooltip_element = $(this.element),
                options = {
                    classes: {
                        'ui-tooltip':
                            ($tooltip_element.hasClass('leyka-tooltip-wide') ? 'leyka-tooltip-wide' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-x-wide') ? 'leyka-tooltip-x-wide' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-white') ? 'leyka-tooltip-white' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-align-left') ? 'leyka-tooltip-align-left' : '')+' '
                            +$tooltip_element.data('tooltip-additional-classes')
                    },
                    content: function(){

                        let $element = $(this),
                            tooltip_content = $element.siblings('.leyka-tooltip-content:first').html();

                        // console.log(this, 'Inner tooltip content:', $element.siblings('.leyka-tooltip-content:first'))

                        return tooltip_content ? tooltip_content : $element.prop('title');

                    },
                    // position: {my: 'left top + 15', at: 'left bottom', collision: 'flipfit'} // Default position settings
                    position: {my: 'left top + 0', at: 'center', collision: 'flipfit'}
                };

            if($tooltip_element.hasClass('leyka-tooltip-on-click')) { // Tooltips on click
                options.items = '.has-tooltip.tooltip-opened';
            }

            this.option(options); // Redefine options, set them to Leyka setup

        }
    });

    if($tooltips.length && typeof $().tooltip !== 'undefined') {

        // Init all tooltips on initial page rendering:
        $tooltips.each(function(i, element){
            $(element).leyka_admin_tooltip();
        });

        // Tooltips on click:
        let $tooltips_on_click = $('.has-tooltip.leyka-tooltip-on-click');

        $tooltips_on_click.on('click.leyka', function(){ // Tooltips on click - open

            let $element = $(this);
            // if($element.hasClass('leyka-tooltip-on-click')) {

            if($element.hasClass('tooltip-opened')) { // Tootips on click - hide
                $element.leyka_admin_tooltip('close').removeClass('tooltip-opened');
            } else {
                $element.addClass('tooltip-opened').leyka_admin_tooltip('open'); //.mouseenter();
            }
            // }

        }).on('mouseout.leyka', function(e){ // Prevent mouseout and other related events from firing their handlers
            e.stopImmediatePropagation();
        });

        // Close opened tooltip when clicked elsewhere:
        $body.on('click.leyka', function(e){

            if($tooltips_on_click.length) {

                $tooltips_on_click.filter('.tooltip-opened').each(function(i, element){
                    if(element !== e.target) {
                        $(element).leyka_admin_tooltip('close').removeClass('tooltip-opened');
                    }
                });

            }

        });
        // Tooltips on click - END

    }

    // "Hidden" tooltips on click:
    // if(typeof $().tooltip !== 'undefined') {
    //     $body.on('click.leyka', '.has-tooltip.leyka-tooltip-on-click.leyka-inner-tooltip', function(e){
    //
    //         e.preventDefault();
    //         e.stopImmediatePropagation();
    //
    //         let $element = $(this);
    //         // console.log('HERE:', this, $element.siblings('.leyka-tooltip-content'))
    //
    //         $element.leyka_admin_tooltip({
    //             content: $element.siblings('.leyka-tooltip-content:first').html()
    //         });
    //         $element.addClass('tooltip-opened').leyka_admin_tooltip('open');
    //
    //     });
    // }
    // "Hidden" tooltips on click - END

    // Tooltips - END

    // Multi-valued item complex fields:
    $('.leyka-main-multi-items').each(function(index, outer_items_wrapper){

        let $items_wrapper = $(outer_items_wrapper),
            $item_template = $items_wrapper.siblings('.item-template'),
            $add_item_button = $items_wrapper.siblings('.add-item'),
            items_cookie_name = $items_wrapper.data('items-cookie-name'),
            closed_boxes = typeof $.cookie(items_cookie_name) === 'string' ? JSON.parse($.cookie(items_cookie_name)) : [];

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

                    if( !item_id.length ) {
                        return;
                    }

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

                $items_wrapper.siblings('input.leyka-items-options').val( encodeURIComponent(JSON.stringify(items_options)) );

            }
        });

        $items_wrapper.on('click.leyka', '.item-box-title', function(e){

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

            if($items_wrapper.find('#item-'+new_item_id)) {

                $items_wrapper.sortable('option', 'update')();

                const $new_item = $('#item-'+new_item_id);

                if($new_item && $new_item.hasClass('payment-amount-option')) {

                    const payment_type = $new_item.hasClass('payment_single') ? 'single' : 'recurring';

                    $new_item.find('input').each((idx, payment_amount_option_input) => {

                        if($(payment_amount_option_input).prop('id').indexOf('_amount_') !== -1) {

                            $(payment_amount_option_input)
                                .prop('id', 'leyka_payment_'+payment_type+'_amount_'+new_item_id+'-field')
                                .prop('name', 'leyka_payment_'+payment_type+'_amount_'+new_item_id);

                        } else if($(payment_amount_option_input).prop('id').indexOf('_description_') !== -1) {

                            $(payment_amount_option_input)
                                .prop('id', 'leyka_payment_'+payment_type+'_description_'+new_item_id+'-field')
                                .prop('name', 'leyka_payment_'+payment_type+'_description_'+new_item_id);

                        };

                    })

                }

            }

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
        function leyka_pre_submit_multi_items(e) {

            let items_options = [];
            $.each($items_wrapper.sortable('toArray'), function(key, item_id){ // Value is an item ID (generated randomly)

                if( !item_id.length ) {
                    return;
                }

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

                if ($items_wrapper.hasClass('leyka-main-payments-amounts')) {

                    const item_pure_id = item_id.replace('item-','');
                    let skip = false;

                    items_options.forEach((other_item_option, idx) => {

                        const other_item_pure_id = other_item_option.id.replace('item-','');

                        if ((('leyka_payment_single_amount_'+item_pure_id in item_options) &&
                            (item_options['leyka_payment_single_amount_'+item_pure_id] == other_item_option['leyka_payment_single_amount_'+other_item_pure_id]) &&
                            (item_options['leyka_payment_single_description_'+item_pure_id] == other_item_option['leyka_payment_single_description_'+other_item_pure_id])) ||
                            (('leyka_payment_recurring_amount_'+item_pure_id in item_options) &&
                            (item_options['leyka_payment_recurring_amount_'+item_pure_id] == other_item_option['leyka_payment_recurring_amount_'+other_item_pure_id]) &&
                            (item_options['leyka_payment_recurring_description_'+item_pure_id] == other_item_option['leyka_payment_recurring_description_'+other_item_pure_id]))
                        ) {
                            skip = true;
                        }

                    })

                    if (skip) {
                        $('#'+item_id).remove();
                        return;
                    }

                }

                items_options.push(item_options);

            });

            $items_wrapper.sortable('option', 'update')();

        }

        if(leyka_is_gutenberg_active()) { // Post edit page - Gutenberg mode

            // Trigger the final multi-items values updating ONLY before main saving submit:
            // (Note: in Gutenberg there are also non-main saves - each metabox is also saved individually, via AJAX)
            const unsubscribe = wp.data.subscribe(function(){

                let code_editor = wp.data.select('core/editor');

                if (code_editor.isSavingPost() && !code_editor.isAutosavingPost() && code_editor.didPostSaveRequestSucceed()) {

                    unsubscribe(); // To avoid muliple calls on ajax savings

                    leyka_pre_submit_multi_items();

                }

            });

        } else { // Post edit page (classic editor), general admin pages
            $items_wrapper.parents('form:first').on('submit.leyka', leyka_pre_submit_multi_items);
        }
        // Refresh the main items option value before submit - END

        // Campaigns select fields:

        // Init all existing campaigns list fields on page load:
        $items_wrapper.find('input.leyka-campaigns-selector').each(function(){
            $.leyka_admin_campaigns_select($(this));
        });

        // Init campaign list for a new additional field:
        $add_item_button.on('click.leyka', function(){

            $.leyka_admin_campaigns_select(
                $items_wrapper
                    .find('.multi-valued-item-box:last-child .autocomplete-select[name="campaigns\[\]"]')
                    .siblings('input.leyka-campaigns-selector')
            );

            $.leyka_admin_campaigns_select(
                $items_wrapper
                    .find('.multi-valued-item-box:last-child .autocomplete-select[name="campaigns_exceptions\[\]"]')
                    .siblings('input.leyka-campaigns-selector')
            );

        });

        // Hide/show the campaigns list field when "for all Ccampaigns" checkbox is checked/unchecked:
        $items_wrapper.on('change.leyka', '.item-for-all-campaigns input:checkbox', function(){

            let $checkbox = $(this),
                $campaigns_list_field_wrapper = $checkbox.parents('.single-line').siblings('.single-line.campaigns-list-select'),
                $campaigns_exceptions_list_field_wrapper = $checkbox
                    .parents('.single-line')
                    .siblings('.single-line.campaigns-exceptions-list-select');

            if($checkbox.prop('checked')) {

                $campaigns_list_field_wrapper.hide();
                $campaigns_exceptions_list_field_wrapper.show();

            } else {

                $campaigns_list_field_wrapper.show();
                $campaigns_exceptions_list_field_wrapper.hide();

            }

        });
        // Hide/show the campaigns list field - END

        // Campaigns select fields - END

        // TODO - Temporary solution. Need to check save for multi-item fields (campaign page)
        $items_wrapper.on('change.leyka', '.payment-amount-option-description input, .payment-amount-option-amount input', function(){
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