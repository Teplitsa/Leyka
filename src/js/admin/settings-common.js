/** Common settings functions */

jQuery(document).ready(function($){

    $('.leyka-setting-field.colorpicker').wpColorPicker({ // Colorpicker fields
        change: function(e, ui) {
            $(e.target).parents('.field').find('.leyka-colorpicker-value').val(ui.color.toString());
        }
    });

    leyka_support_metaboxes('options-options_main_area'); // Support metaboxes

    // Custom CSS editor fields:
    let $css_editor = $('.css-editor-field'),
        editor = {};

    if(wp.codeEditor && $css_editor.length) {

        let editor_settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editor_settings.codemirror = _.extend(
            {},
            editor_settings.codemirror, {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css',
            });
        editor = wp.codeEditor.initialize($css_editor, editor_settings);

        $('.css-editor-reset-value').on('click.leyka', function(e){ // Additional CSS value reset

            e.preventDefault();

            let $this = $(this),
                $css_editor_field = $this.siblings('.css-editor-field'),
                original_value = $this.siblings('.css-editor-original-value').val();

            $css_editor_field.val(original_value);
            editor.codemirror.getDoc().setValue(original_value);

        });

    }
    // Custom CSS editor fields - END

    // Ajax file upload fields support:
    $('.upload-field input[type="file"]')
        .on('click.leyka', function(e){ // Just to be sure that the input will be called
            e.stopPropagation();
        }).on('change.leyka', function(e){

            if( !e.target.files ) {
                return;
            }

            let $file_input = $(this),
                $field_wrapper = $file_input.parents('.leyka-file-field-wrapper'),
                option_id = $field_wrapper.find('.upload-field').data('option-id'),
                $file_preview = $field_wrapper.find('.uploaded-file-preview'),
                $ajax_loading = $field_wrapper.find('.loading-indicator-wrap'),
                $error = $field_wrapper.siblings('.field-errors'),
                $main_field = $field_wrapper.find('input#leyka-upload-'+option_id),
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

    $('.leyka-file-field-wrapper .delete-uploaded-file').on('click.leyka', function(e){ // Mark uploaded file to be removed

        e.preventDefault();

        let $delete_link = $(this),
            $field_wrapper = $delete_link.parents('.leyka-file-field-wrapper'),
            option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input#leyka-upload-'+option_id);

        $file_preview.hide().find('.file-preview').html('');
        $main_field.val('');

    });

    // Expandable options sections (portlets only):
    $('.leyka-options-section .header h3').click(function(e){
        e.preventDefault();
        var $section = $(this).closest('.leyka-options-section');
        $section.toggleClass('collapsed');
    });

    // Delete fields comments:
    $('.leyka-admin .leyka-options-section .field-component.help').contents().filter(function(){
        return (this.nodeType === 3);
    }).remove();

    // Rules of the dependence of the set of fields on the legal type:
    if($('#change_receiver_legal_type').length) {

        leyka_toggle_sections_dependent_on_legal_type($('input[type=radio][name=leyka_receiver_legal_type]:checked').val());

        $('input[type="radio"][name="leyka_receiver_legal_type"]').change(function(){
            leyka_toggle_sections_dependent_on_legal_type(
                $('input[type="radio"][name="leyka_receiver_legal_type"]:checked').val()
            );
        });

        function leyka_toggle_sections_dependent_on_legal_type($val) {
            if($val === 'legal') {
                $('#person_terms_of_service').hide();
                $('#beneficiary_person_name').hide();
                $('#person_bank_essentials').hide();

                $('#terms_of_service').show();
                $('#beneficiary_org_name').show();
                $('#org_bank_essentials').show();
            } else {
                $('#person_terms_of_service').show();
                $('#beneficiary_person_name').show();
                $('#person_bank_essentials').show();

                $('#terms_of_service').hide();
                $('#beneficiary_org_name').hide();
                $('#org_bank_essentials').hide();
            }
        }

    }

    // Upload l10n:
    $('#upload-l10n-button').click(function(){

        var $btn = $(this),
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
        var $sectionWrapper = $('.leyka-options-section#stats_connections');
        $sectionWrapper.find('.submit input').removeClass('button-primary').addClass('disconnect-stats').val(leyka.disconnect_stats);
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
        var $tabs = $(this).closest('.section-tabs-wrapper');

        $tabs.find('.section-tab-nav-item').removeClass('active');
        $tabs.find('.section-tab-content').removeClass('active');

        $(this).addClass('active');
        $tabs.find('.section-tab-content.tab-' + $(this).data('target')).addClass('active');

    });

    // Screenshots nav:
    $('.tab-screenshot-nav img').click(function(e){

        e.preventDefault();

        var $currentScreenshots = $(this).closest('.tab-screenshots'),
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