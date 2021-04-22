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
            altField: $date_field.data('alt-field-selector') ? $($date_field.data('alt-field-selector')) : '',
            altFormat: $date_field.data('date-alt-format') ? $date_field.data('date-alt-format') : 'yy-mm-dd',
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

    jQuery.leyka_admin_filter_datepicker_ranged = function($input, options){

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

    // Campaign(s) select fields (for admin list filters mostly):
    $('input.leyka-campaigns-selector').each(function(){

        let $text_selector_field = $(this),
            $list_select_field = $text_selector_field.siblings('.leyka-campaigns-select'),
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

    // Upload l10n:
    $('#upload-l10n-button').click(function(){

        let $btn = $(this),
            $loading = $('<span class="leyka-loader xs"></span>'),
            actionData = {action: 'leyka_upload_l10n'};

        $btn.parent().append($loading);
        $btn.prop('disabled', true);
        $btn.closest('.content').find('.field-errors').removeClass('has-errors').find('span').empty();
        $btn.closest('.content').find('.field-success').hide();

        $.post(leyka.ajaxurl, actionData, null, 'json')
            .done(function(json) {

                if(json.status === 'ok') {
                    $btn.closest('.content').find('.field-success').show();
                    setTimeout(function(){
                        location.reload();
                    }, 500);
                } else if(json.status === 'error' && json.message) {
                    $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html(json.message);
                } else {
                    $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html(leyka.error_message);
                }

            }).fail(function(){
            $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html(leyka.error_message);
        }).always(function(){
            $loading.remove();
            $btn.prop('disabled', false);
        });

    });

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
                            +($tooltip_element.hasClass('leyka-tooltip-white') ? 'leyka-tooltip-white' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-align-left') ? 'leyka-tooltip-align-left' : '')+' '
                            +$tooltip_element.data('tooltip-additional-classes')
                    },
                    content: function(){

                        let $element = $(this),
                            tooltip_content = $element.siblings('.leyka-tooltip-content').html();

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

    if($tooltips.length && typeof $().tooltip !== 'undefined' ) {

        // Init all tooltips on initial page rendering:
        $tooltips.each(function(i, element){
            $(element).leyka_admin_tooltip();
        });

        // Tooltips on click:
        let $tooltips_on_click = $('.has-tooltip.leyka-tooltip-on-click');

        $tooltips_on_click.on('click.leyka', function(){ // Tooltips on click - open

            let $element = $(this);
            if($element.hasClass('leyka-tooltip-on-click')) {

                if($element.hasClass('tooltip-opened')) { // Tootips on click - hide
                    $element.leyka_admin_tooltip('close').removeClass('tooltip-opened');
                } else {
                    $element.addClass('tooltip-opened').leyka_admin_tooltip('open'); //.mouseenter();
                }
            }

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
    // Tooltips - END

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