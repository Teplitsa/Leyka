/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));

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

    /** @todo Check if it's still a needed feature */
    // Recalculate total funded amount:
    $('#recalculate_total_funded').click(function(e){

        e.preventDefault();

        var $link = $(this).attr('disabled', 'disabled'),
            $indicator = $link.parent().find('#recalculate_total_funded_loader').show(),
            $message = $link.parent().find('#recalculate_message').hide(),
            $total_collected_field = $('#collected_target');

        $.get(leyka.ajaxurl, {
            campaign_id: $link.data('campaign-id'),
            action: 'leyka_recalculate_total_funded_amount',
            nonce: $link.data('nonce')
        }, function(resp){

            $link.removeAttr('disabled');
            $indicator.hide();

            if(parseFloat(resp) >= 0) {

                var old_value = parseFloat($total_collected_field.val());
                resp = parseFloat(resp);

                $total_collected_field.val(resp);
                if(old_value !== resp) { // If recalculated sum is different than saved one, refresh the campaign edition page
                    $('#publish').click();
                }

            } else {
                $message.html(resp).show();
            }
        });
    });
    
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

});
// color calc
function leykaRgb2Hsl(r, g, b) {
    var d, h, l, max, min, s;

    r /= 255;
    g /= 255;
    b /= 255;

    max = Math.max(r, g, b);
    min = Math.min(r, g, b);

    h = 0;
    s = 0;
    l = (max + min) / 2;

    if (max === min) {
        h = s = 0;

    } else {
        d = max - min;

        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

        if(max == r) {
            h = (g - b) / d + (g < b ? 6 : 0);
        }
        else if(max == g) {
            h = (b - r) / d + 2;
        }
        else if(max == b) {
            h = (r - g) / d + 4;
        }

        h /= 6;
    }

    h = Math.floor(h * 360);
    s = Math.floor(s * 100);
    l = Math.floor(l * 100);

    return [h, s, l];
}

function leykaHex2Rgb (hex) {
    hex = hex.replace("#", "");

    var intColor = parseInt(hex, 16);
    var r = (intColor >> 16) & 255;
    var g = (intColor >> 8) & 255;
    var b = intColor & 255;

    return [r, g, b];
}

function leykaHsl2Rgb(h, s, l) {
    h /= 360
    s /= 100
    l /= 100

    var r, g, b;

    if(s == 0){
        r = g = b = l; // achromatic
    }else{
        var hue2rgb = function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function leykaHsl2Hex(hue, saturation, luminosity) {
  while (hue < 0) { hue += 360 }
  while (hue > 359) { hue -= 360 }

  var rgb = leykaHsl2Rgb(hue, saturation, luminosity);

  return '#' + rgb
    .map(function (n) {
      return (256 + n).toString(16).substr(-2)
    })
    .join('')
}

function leykaHex2Hsl(hexColor) {
    var rgb = leykaHex2Rgb(hexColor);
    return leykaRgb2Hsl(rgb[0], rgb[1], rgb[2]);
}

function leykaMainHslColor2Background(h, s, l) {
    if(l < 50) {
        l = 95;
    }
    else {
        l = 5;
    }
    return [h, s, l];
}

function leykaMainHslColor2Text(h, s, l) {
    if(l < 50) {
        l = 21;
    }
    else {
        l = 79;
    }
    s = 20;
    return [h, s, l];
}
// init "how to setup crom" modal
jQuery(document).ready(function($){

    if(typeof($().dialog) === 'undefined') {
        return;
    }

    $('.leyka-adb-modal').dialog({
        dialogClass: 'wp-dialog leyka-adb-modal',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: 'center top+25%',
            at: 'center top+25%',
            of: window
        },
        open: function(){
            var $modal = $(this);
            $('.ui-widget-overlay').bind('click', function(){
                $modal.dialog('close');
            });
        },
        create: function () {
            $('.ui-dialog-titlebar-close').addClass('ui-button');

            var $modal = $(this);
            $modal.find('.button-dialog-close').bind('click', function(){
                $modal.dialog('close');
            });
        }

    });

    $('.cron-setup-howto').on('click.leyka', function(e){
        e.preventDefault();
        $('#how-to-setup-cron').dialog('open');
    })

});

// init "stats invite"
jQuery(document).ready(function($){
    $('.send-plugin-stats-invite .send-plugin-usage-stats-y').on('click.leyka', function(e){
        e.preventDefault();

        let $button = $(this),
            $field_wrapper = $button.parents('.invite-link'),
            $loading = $field_wrapper.find('.loader-wrap');

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_usage_stats_y',
            nonce: $field_wrapper.find(':input[name="usage_stats_y"]').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');
        

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $loading.remove();
                        $indicatorWrap.find('.ok-icon').show();
                        setTimeout(function(){
                            $field_wrapper.closest('.send-plugin-stats-invite').fadeOut("slow");;
                        }, 1000);
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
            });
    });
});

// banner
jQuery(document).ready(function($){
    $('.banner-wrapper .close').on('click.leyka', function(e){
        e.preventDefault();

        $(this).closest('.banner-wrapper').remove();

        let ajax_params = {
            action: 'leyka_close_dashboard_banner'
        };

        $.post(leyka.ajaxurl, ajax_params, null, 'json');
    });
});

// Donation add/edit page:
jQuery(document).ready(function($){

    var $page_type = $('#originalaction'),
        $post_type = $('#post_type');

    if( !$page_type.length || $page_type.val() !== 'editpost' || !$post_type.length || $post_type.val() !== 'leyka_donation' ) {
        return;
    }

    var $donation_date = $('#donation-date-view').datepicker({
        changeMonth: true,
        changeYear: true,
        minDate: '-5Y',
        maxDate: '+1Y',
        dateFormat: 'dd.mm.yy',
        altField: '#donation-date',
        altFormat: 'yy-mm-dd'
    });

    // Validate add/edit donation form:
    $('form#post').submit(function(e){

        var $form = $(this),
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
        var amount_clear = parseFloat($field.val().replace(',', '.'));
        if( !$field.val() || amount_clear == 0 || isNaN(amount_clear) ) {

            // console.log( !$field.val(), parseFloat($field.val().replace(',', '.')), isNaN($field.val()))

            is_valid = false;
            $form.find('#donation_amount-error').html(leyka.amount_incorrect_msg).show();

        } else {
            $form.find('#donation_amount-error').html('').hide();
        }

        $field = $('#donation-pm');
        if($field.val() === 'custom')
            $field = $('#custom-payment-info');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#donation_pm-error').html(leyka.donation_source_required).show();
        } else
            $form.find('#donation_pm-error').html('').hide();

        $('#donation-date-field').val($.datepicker.formatDate('yy-mm-dd', $donation_date.datepicker('getDate')));

        if( !is_valid )
            e.preventDefault();
    });

    /** New donation page: */

    $('#donation-pm').change(function(){

        var $this = $(this);

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

        var $this = $(this),
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

        $(this).slideUp(100);
        $('#campaign-select-fields').slideDown(100);
        $('#campaign-field').removeAttr('disabled');

    });

    $('#cancel-campaign-select').click(function(e){

        e.preventDefault();

        $('#campaign-select-fields').slideUp(100);
        $('#campaign-field').attr('disabled', 'disabled');
        $('#campaign-select-trigger').slideDown(100);

    });

    $('.recurrent-cancel').click(function(e){
        e.preventDefault();

        var $this = $(this);

        $('#ajax-processing').fadeIn(100);
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

});
/** Donor's info page */
jQuery(document).ready(function($){

    var $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donor-info-page' ) {
        return;
    }

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {
        $('.leyka-data-table').DataTable({
            pageLength: 10,
            lengthChange: false,
            ordering:  false,
            searching: false,
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

// donor info
jQuery(document).ready(function($){
    $('.donor-add-description-link').click(function(e){
        e.preventDefault();
        $('.add-donor-description-form').toggle();
    });

    $('.add-donor-description-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form.closest('.donor-description'),
            $field = $form.find('textarea[name="donor-description"]'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$field.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_save_donor_description',
            nonce: $('#leyka_save_editable_str_nonce').val(),
            text: $field.val(),
            donor: $('#leyka_donor_id').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        setTimeout(function(){
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                            $fieldWrapper.find('.description-text').text(json.saved_text);
                            $fieldWrapper.find('.leyka-editable-str-field').text(json.saved_text);
                            $('.donor-add-description-wrapper').remove();
                            $('.donor-view-description-wrapper').show();
                        }, 1000);
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });    
});

// comments
function leykaSetCommentsListVisibilityState() {
    let $ = jQuery;

    if($('#leyka_donor_admin_comments table tbody tr').length > 1) {
        $('table.donor-comments').show();
        $('.no-comments').hide();
    }
    else {
        $('table.donor-comments').hide();
        $('.no-comments').show();
    }
}

jQuery(document).ready(function($){
    $('.add-donor-comment-link').click(function(e){
        e.preventDefault();

        var $form = $(this).parent().find('.new-donor-comment-form');
        $form.toggle();
        $form.find('.ok-icon').css('display', 'none');
    });

    $('#leyka_donor_admin_comments table').on('click', '.comment-icon-delete', function(e){
        e.preventDefault();

        if(!confirm(leyka.confirm_delete_comment)) {
            return;
        }

        let $button = $(this),
            $row = $(this).closest('tr'),
            $cell = $(this).closest('td'),
            $metabox = $(this).closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $cell.find('.loader-wrap'),
            comment_id = $button.data('comment-id'),
            donor_id = $('#leyka_donor_id').val();

        $button.hide();

        let ajax_params = {
            action: 'leyka_delete_donor_comment',
            nonce: $('input[name="leyka_delete_donor_comment_nonce"]').val(),
            comment_id: comment_id,
            donor: donor_id
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        $row.remove();
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                        }
                        else {
                            alert(leyka.error_message);
                        }
                        $button.show();
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.show();
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
            });
    });

    $('.new-donor-comment-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form,
            $commentField = $form.find('input[name="donor-comment"]'),
            $metabox = $form.closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$commentField.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_add_donor_comment',
            nonce: $('#leyka_add_donor_comment_nonce').val(),
            comment: $commentField.val(),
            donor: $('#leyka_donor_id').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        $commentField.val("");
                        setTimeout(function(){
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                        }, 1000);

                        var $trTemplate = $table.find('tbody tr:first'),
                            $tr = $trTemplate.clone(),
                            comment_html = json.comment_html;

                        $tr = $(comment_html);
                        $table.append($tr);

                        leykaBindEditableStrEvents($tr);
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });
});


// editable string
function leykaBindEditableStrEvents($container) {
    let $ = jQuery;

    $container.find('.leyka-editable-str-field').on('blur', function(e){
        if($(this).prop('readonly')) {
            return;
        }

        leykaSaveEditableStrAndCloseForm($(this));
    });

    $container.find('input.leyka-editable-str-field').keypress(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        if ( e.key === "Enter" ) {
            e.preventDefault();
            leykaSaveEditableStrAndCloseForm($(this));
        }    
    });

    $container.find('.leyka-editable-str-field').keydown(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        var $strField = $(this),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        if ( e.key === "Escape" || e.key === "Esc" ) {
            e.preventDefault();
            $strField.val($strResult.text());
            leykaSaveEditableStrAndCloseForm($strField);
        }    
    });

    $container.find('.leyka-editable-str-btn').click(function(e){
        e.preventDefault();

        var $btn = $(this),
            $strField = $('.leyka-editable-str-field#' + $btn.attr('str-field')),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        $strResult.hide();
        $strField.show().focus();
        $btn.hide();
        $strField.parent().find('.loading-indicator-wrap').show();
    });
}

function leykaSaveEditableStrAndCloseForm($strField) {
    let $ = jQuery;

    var $btn = $('.leyka-editable-str-btn#' + $strField.attr('str-btn')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

    var endEditCallback = function(){
        $strField.hide();
        $strResult.show();
        $btn.show();
        $strField.parent().find('.loading-indicator-wrap').hide();
        $strField.prop('readonly', false);
    };

    if($strField.val() != $strResult.text()) {
        leykaSaveEditableStr($strField, endEditCallback);
    }
    else {
        endEditCallback();
    }
}

function leykaSaveEditableStr($strField, saveCallback) {
    let $ = jQuery;

    var $button = $('.leyka-editable-str-link#' + $strField.attr('str-edit-link')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result')),
        $loading = $strField.parent().find('.loader-wrap'),
        $indicatorWrap = $loading.closest('.loading-indicator-wrap');

    let ajax_params = {
        action: $strField.attr('save-action'),
        nonce: $('#leyka_save_editable_str_nonce').val(),
        text: $strField.val(),
        text_item_id: $strField.attr('text-item-id'),
        donor: $('#leyka_donor_id').val()
    };
    
    $loading.css('display', 'block');
    $loading.find('.leyka-loader').css('display', 'block');
    $strField.prop('readonly', true);

    $.post(leyka.ajaxurl, ajax_params, null, 'json')
        .done(function(json){
            if(typeof json.status !== 'undefined') {
                if(json.status === 'ok') {
                    $indicatorWrap.find('.ok-icon').css('display', 'block');

                    if(json.saved_text) {
                        $strResult.text(json.saved_text);
                    }
                    else {
                        $strResult.text($strField.val());
                    }

                    setTimeout(function(){
                        $indicatorWrap.find('.ok-icon').fadeOut("slow", saveCallback);
                    }, 1000);
                }
                else {
                    if(json.message) {
                        alert(json.message);
                    }
                    else {
                        alert(leyka.error_message);
                    }
                    $strField.prop('readonly', false);
                }
                return;
            }
        })
        .fail(function(){
            alert(leyka.error_message);
            $strField.prop('readonly', false);
        })
        .always(function(){
            $loading.css('display', 'none');
            $loading.find('.leyka-loader').css('display', 'none');
        });

}

jQuery(document).ready(function($){
    leykaBindEditableStrEvents($(document));
});

// tags
jQuery(document).ready(function($){
    if(!$('#leyka_donor_tags').length) {
        return;
    }

    window.tagBox && window.tagBox.init();

    var saveDonorTagsTimeoutId = null;

    $("body").on('DOMSubtreeModified', ".tagchecklist", function() {
        console.log('tags list changed');

        if(saveDonorTagsTimeoutId) {
            clearTimeout(saveDonorTagsTimeoutId);
        }

        saveDonorTagsTimeoutId = setTimeout(function() {
            console.log('save tags list');

            let ajax_params = {
                action: 'leyka_save_donor_tags',
                nonce: $('#leyka_save_donor_tags_nonce').val(),
                tags: $('textarea[name="tax_input[donors_tag]"]').val(),
                donor: $('#leyka_donor_id').val()
            };
            
            $.post(leyka.ajaxurl, ajax_params, null, 'json')
                .done(function(json){
                    if(typeof json.status !== 'undefined') {
                        if(json.status === 'ok') {
                        }
                        else {
                            if(json.message) {
                                alert(json.message);
                            }
                            else {
                                alert(leyka.error_message);
                            }
                        }
                        return;
                    }
                })
                .fail(function(){
                    alert(leyka.error_message);
                })

            saveDonorTagsTimeoutId = null;
        }, 500);

    });

});


/** Donors list page */
jQuery(document).ready(function($){

	function leykaFillDatepickerInputPeriod(inst, extensionRange) {
		var inputText = extensionRange.startDateText;
		if(extensionRange.endDateText && extensionRange.endDateText != extensionRange.startDateText) {
			inputText += "," + extensionRange.endDateText;
		}
		$(inst.input).val(inputText);
	}

	function leykaInitFilterDatepicker($input, options) {

		$input.datepicker({
			range:'period',
			onSelect:function(dateText, inst, extensionRange){
				leykaFillDatepickerInputPeriod(inst, extensionRange);
			},

			beforeShow: function(input, inst) {
				let selectedDatesStr = $(input).val(),
					selectedDatesStrList = selectedDatesStr.split(","),
					selectedDates = [];

				for(let i in selectedDatesStrList) {
					if(selectedDatesStrList[i]) {

						let singleDate;
						try {
							singleDate = $.datepicker.parseDate($(input).datepicker('option', 'dateFormat'), selectedDatesStrList[i]);
						} catch {
							// console.log("parse date error: " + selectedDatesStrList[i])
							singleDate = new Date();
						}
						
						selectedDates.push(singleDate);
					}
				}

				$(inst.input).val(selectedDates[0]);
				$(inst.input).datepicker('setDate', selectedDates);
				setTimeout(function(){
					leykaFillDatepickerInputPeriod(inst, $(inst.dpDiv).data('datepickerExtensionRange'));
				});
				
			}
		});		

	}

	var selectorValues = [],
		selectedValues = [],
        $page_wrapper = $('.wrap');

    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donors-list-page' ) {
        return;
    }

    if(typeof $().selectmenu != 'undefined') {
        $('select[name="donor-type"]').selectmenu();
    }

	$('input[name="donor-name-email"]').autocomplete({
		source: leyka.ajaxurl + '?action=leyka_donors_autocomplete',
		minLength: 2,
		select: function( event, ui ) {
			// console.log( "Selected: " + ui.item.label + " ID: " + ui.item.value );
		}		
	});

	leykaInitFilterDatepicker($('input[name="first-donation-date"]'), {
	    warningMessage: leyka.first_donation_date_incomplete_message
	});
	leykaInitFilterDatepicker($('input[name="last-donation-date"]'), {
	    warningMessage: leyka.last_donation_date_incomplete_message
	});

	// Campaigns:
	selectedValues = [];
	$('#leyka-campaigns-select').find('option').each(function(){
		selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $("input.leyka-campaigns-selector").autocomplete({
        source: leyka.ajaxurl + '?action=leyka_campaigns_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			var $select = $('#leyka-campaigns-select');
			$select.html('');
			for(var val in selectedItems) {
				var $option = $('<option></option>')
					.val(val)
					.prop('selected', true);
				$select.append($option);
			}
		}        
    });

	// Gateways:
	selectorValues = [];
	selectedValues = [];
	$('#leyka-gateways-select').find('option').each(function(){
		selectorValues.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

    $("input.leyka-gateways-selector").autocomplete({
        source: selectorValues,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-gateways-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	// tags
	selectedValues = [];
	$('#leyka-donors-tags-select').find('option').each(function(){
		selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
	});

    $("input.leyka-donors-tags-selector").autocomplete({
        source: leyka.ajaxurl + '?action=leyka_donors_tags_autocomplete',
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			var $select = $('#leyka-donors-tags-select');
			$select.html('');
			for(var val in selectedItems) {
				var $option = $('<option></option>')
					.val(val)
					.prop('selected', true);
				$select.append($option);
			}
		}        
    });

	// payment status
	selectorValues = [];
	selectedValues = [];
	$('#leyka-payment-status-select').find('option').each(function(){
		selectorValues.push({label: $.trim($(this).text()), value: $(this).val()});
		if($(this).prop('selected')) {
			selectedValues.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
		}
	});

	var $leykaPaymentStatusAutocomplete = $('input.leyka-payment-status-selector').autocomplete({
        source: selectorValues,
        multiselect: true,
        search_on_focus: true,
        minLength: 0,
        pre_selected_values: selectedValues,
		leyka_select_callback: function( selectedItems ) {
			$('#leyka-payment-status-select').find('option').each(function(){
				$(this).prop('selected', selectedItems[$(this).val()] !== undefined);
			});
		}        
    });

	$('.reset-filters').click(function(e){
		e.preventDefault();

		$('input.leyka-payment-status-selector').autocomplete('reset');
		$('input.leyka-donors-tags-selector').autocomplete('reset');
		$('input.leyka-gateways-selector').autocomplete('reset');
		$('input.leyka-campaigns-selector').autocomplete('reset');

		$('input[name="donor-name-email"]').val('');
		$('select[name="donor-type"]').prop('selectedIndex', 0).selectmenu('refresh');

		$('input[name=first-donation-date]').val('');
		$('input[name=last-donation-date]').val('');
        $(this).closest('form.donors-list-controls').submit();

	});
});

/** Extension settings (edit page) JS. */

jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if( !$admin_page_wrapper.length || !$admin_page_wrapper.hasClass('extension-settings') ) {
        return;
    }

    $('.delete-extension-link').click(function(e){

        e.preventDefault();

        let $delete_link = $(this),
            $ajax_loading = $delete_link.find('.loading-indicator-wrap'),
            $error = $delete_link.siblings('.delete-extension-error');

        if(confirm(leyka.extension_deletion_confirm_text)) {

            $ajax_loading.show();
            $error.html('').hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_delete_extension',
                extension_id: $delete_link.data('extension-id'),
                nonce: $delete_link.data('nonce'),
            }, function(response){

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

                window.location.href = leyka.extensions_list_page_url+'&extension-deleted=1';

            }, 'json');

        }

    });

});

/** @todo After debugging, move all the following code to the Extension own JS: */
jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if(
        !$admin_page_wrapper.length
        || !$admin_page_wrapper.hasClass('extension-settings')
        || $admin_page_wrapper.data('leyka-extension-id') !== 'support_packages'
        || !leyka_ui_widget_available('sortable')
    ) {
        return;
    }

    let $mainColorInput = $('input[name="leyka_support_packages_main_color"]'),
        $backgroundColorInput = $('input[name="leyka_support_packages_background_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $captionColorInput = $('input[name="leyka_support_packages_caption_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker'),
        $textColorInput = $('input[name="leyka_support_packages_text_color"]')
            .closest('.field-component.field')
            .find('.leyka-setting-field.colorpicker');

    function leykaSetupGeneralColors(mainColorHex) {

        // console.log("mainColorHex:", mainColorHex);
        let mainColorHsl = leykaHex2Hsl(mainColorHex);
        // console.log("mainColorHsl:", mainColorHsl);

        let backgroundColorHsl = leykaMainHslColor2Background(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        // console.log("backgroundColorHsl:");
        // console.log(backgroundColorHsl);

        let backgroundColorHex = leykaHsl2Hex(backgroundColorHsl[0], backgroundColorHsl[1], backgroundColorHsl[2]);
        // console.log("backgroundColorHex:");
        // console.log(backgroundColorHex);
        $backgroundColorInput.wpColorPicker('color', backgroundColorHex);
        $captionColorInput.wpColorPicker('color', backgroundColorHex);

        let textColorHsl = leykaMainHslColor2Text(mainColorHsl[0], mainColorHsl[1], mainColorHsl[2]);
        // console.log("textColorHsl:");
        // console.log(textColorHsl);

        let textColorHex = leykaHsl2Hex(textColorHsl[0], textColorHsl[1], textColorHsl[2]);
        // console.log("textColorHex:");
        // console.log(textColorHex);
        $textColorInput.wpColorPicker('color', textColorHex);

    }

    $mainColorInput.on('change.leyka', function(){
        leykaSetupGeneralColors($(this).val());
    });

});

// Support packages extension - custom field:
jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if(
        !$admin_page_wrapper.length
        || !$admin_page_wrapper.hasClass('extension-settings')
        || $admin_page_wrapper.data('leyka-extension-id') !== 'support_packages'
        || !leyka_ui_widget_available('sortable')
    ) {
        return;
    }

    let $packages_wrapper = $('.leyka-main-support-packages'),
        $package_template = $packages_wrapper.siblings('.package-template'),
        $add_package_button = $packages_wrapper.siblings('.add-package'),
        closed_boxes = typeof $.cookie('leyka-support-packages-boxes-closed') === 'string' ?
            JSON.parse($.cookie('leyka-support-packages-boxes-closed')) : [];

    if($.isArray(closed_boxes)) { // Close the package boxes needed
        $.each(closed_boxes, function(key, value){
            $packages_wrapper.find('#'+value).addClass('closed');
        });
    }

    $packages_wrapper.on('click.leyka', 'h2.hndle', function(e){

        let $this = $(this),
            $current_box = $this.parents('.package-box');

        $current_box.toggleClass('closed');

        // Save the open/closed state for all packages boxes:
        let current_box_id = $current_box.prop('id'),
            current_box_index = $.inArray(current_box_id, closed_boxes);

        if(current_box_index === -1 && $current_box.hasClass('closed')) {
            closed_boxes.push(current_box_id);
        } else if(current_box_index !== -1 && !$current_box.hasClass('closed')) {
            closed_boxes.splice(current_box_index, 1);
        }

        $.cookie('leyka-support-packages-boxes-closed', JSON.stringify(closed_boxes));

    });

    $packages_wrapper.sortable({
        placeholder: 'ui-state-highlight', // A class for dropping item placeholder
        update: function(event, ui){

            let packages_options = [];
            $.each($packages_wrapper.sortable('toArray'), function(key, package_id){ // Value is a package ID

                let package_options = {'id': package_id}; // Assoc. array key should be initialized explicitly

                $.each($packages_wrapper.find('#'+package_id).find(':input').serializeArray(), function(key, package_field){
                    package_options[ package_field.name.replace('leyka_package_', '') ] = package_field.value;
                });

                packages_options.push(package_options);

            });

            $packages_wrapper.siblings('input#leyka-support-packages-options').val(
                encodeURIComponent(JSON.stringify(packages_options))
            );

        }
    });

    $packages_wrapper.on('click.leyka', '.delete-package', function(e){

        e.preventDefault();

        if($packages_wrapper.find('.package-box').length > 1) {

            $(this).parents('.package-box').remove();
            $packages_wrapper.sortable('option', 'update')();

        }

        let packages_current_count = $packages_wrapper.find('.package-box').length;
        if(packages_current_count <= 1) {
            $packages_wrapper.find('.delete-package').addClass('inactive');
        }
        if(packages_current_count < $packages_wrapper.data('max-packages')) {
            $add_package_button.removeClass('inactive');
        }

    });
    $add_package_button.on('click.leyka', function(e){

        e.preventDefault();

        if($add_package_button.hasClass('inactive')) {
            return;
        }

        // Generate & set the new package ID:
        let new_package_id = '';
        do {
            new_package_id = leyka_get_random_string(4);
        } while($packages_wrapper.find('#package-'+new_package_id).length);

        $package_template
            .clone()
            .appendTo($packages_wrapper)
            .removeClass('package-template')
            .prop('id', 'package-'+new_package_id)
            .show();

        $packages_wrapper.sortable('option', 'update')();

        let packages_current_count = $packages_wrapper.find('.package-box').length;

        if(packages_current_count >= $packages_wrapper.data('max-packages')) {
            $add_package_button.addClass('inactive');
        }

        if(packages_current_count <= 1) { // When adding initial package box
            $packages_wrapper.find('.delete-package').addClass('inactive');
        } else if(packages_current_count > 1) {
            $packages_wrapper.find('.delete-package').removeClass('inactive');
        }

    });

    if( !$packages_wrapper.find('.package-box').length ) { // No packages added yet - add the first (empty) one
        $add_package_button.trigger('click.leyka');
    }

    // Refresh the main packages option value before submit:
    $packages_wrapper.parents('.leyka-options-form').on('submit.leyka', function(){
        $packages_wrapper.sortable('option', 'update')();
    });

});
/** @todo Move to the Extension JS - END */
/** Feedback page */
jQuery(document).ready(function($){

    var $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'feedback-page' ) {
        return;
    }

    var $form = $('#feedback'),
        $loader = $('#feedback-loader'),
        $message_ok = $('#message-ok'),
        $message_error = $('#message-error');

    $form.submit(function(e){

        e.preventDefault();

        if( !validate_feedback_form($form) ) {
            return false;
        }

        $form.hide();
        $loader.show();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            topic: $form.find('#feedback-topic').val(),
            name: $form.find('#feedback-name').val(),
            email: $form.find('#feedback-email').val(),
            text: $form.find('#feedback-text').val(),
            nonce: $form.find('#nonce').val()
        }, function(response){

            $loader.hide();

            if(response === '0') {
                $message_ok.fadeIn(100);
            } else {
                $message_error.fadeIn(100);
            }

        });

        return true;

    });

    function validate_feedback_form($form) {

        var is_valid = true,
            $field = $form.find('#feedback-topic');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('#feedback-name');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('#feedback-email');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else if( !is_email($field.val()) ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('#feedback-text');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        return is_valid;

    }

});
/** Gateways settings board */

// Payment settings page:
jQuery(document).ready(function($){

    if( !$('#payment-settings-area-new.stage-payment').length ) {
        return;
    }

    let $pm_available_list = $('.pm-available'),
        $pm_order = $('#pm-order-settings'),
        $pm_update_status = $('.pm-update-status'),
        $ok_message = $pm_update_status.find('.ok-message'),
        $error_message = $pm_update_status.find('.error-message'),
        $ajax_loading = $pm_update_status.find('.leyka-loader'),
        $pm_list_empty_block = $('.pm-list-empty');

    $pm_update_status.find('.result').hide();

    function leyka_update_pm_list($pm_order) {

        let params = {
            action: 'leyka_update_pm_list',
            pm_order: $pm_order.data('pm-order'),
            pm_labels: {},
            nonce: $pm_order.data('nonce')
        };

        $pm_order.find('input.pm-label-field.submitable').each(function(){
            params.pm_labels[$(this).prop('name')] = $(this).val();
        });

        $ok_message.hide();
        $error_message.hide();
        $ajax_loading.show();

        $.post(leyka.ajaxurl, params, null, 'json')
            .done(function(json){

                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    $ok_message.hide();
                    $error_message.html(typeof json.message === 'undefined' ? leyka.common_error_message : json.message).show();

                    return;

                }

                $ok_message.show();
                $error_message.html('').hide();

            })
            .fail(function(){
                $error_message.html(leyka.common_error_message).show();
            })
            .always(function(){
                $ajax_loading.hide();
            });

    }

    // PM reordering:
    $pm_order
        .sortable({placeholder: '', items: '> li:visible'})
        .on('sortupdate', function(event){

            $pm_order.data('pm-order',
                $(this).sortable('serialize', {key: 'pm_order[]', attribute: 'data-pm-id', expression: /(.+)/})
            );

            leyka_update_pm_list($pm_order);

            if($pm_order.find('.pm-order:visible').length) {
                $pm_list_empty_block.hide();
            } else {
                $pm_list_empty_block.show();
            }

        }).on('click', '.pm-deactivate', function(e){ // PM deactivation

            e.preventDefault();

            var $pm_sortable_item = $(this).parents('li:first');

            $pm_sortable_item.hide(); // Remove a sortable block from the PM order settings
            $pm_available_list.filter('#'+$pm_sortable_item.data('pm-id')).removeAttr('checked');

            $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

        }).on('click', '.pm-change-label', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first');

            $wrapper.find('.pm-control').hide();
            $wrapper.find('.pm-label').hide();
            $wrapper.find('.pm-label-fields').show();

        }).on('click', '.new-pm-label-ok,.new-pm-label-cancel', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first'),
                $pm_label_wrapper = $wrapper.find('.pm-label'),
                new_pm_label = $wrapper.find('input[id*="pm_label"]').val();

            if($this.hasClass('new-pm-label-ok') && $pm_label_wrapper.text() !== new_pm_label) {

                $pm_label_wrapper.text(new_pm_label);
                $wrapper.find('input.pm-label-field').val(new_pm_label);

                leyka_update_pm_list($pm_order);

            } else {
                $wrapper.find('input[id*="pm_label"]').val($pm_label_wrapper.text());
            }

            $pm_label_wrapper.show();
            $wrapper.find('.pm-label-fields').hide();
            $wrapper.find('.pm-control').show();

        }).on('keydown', 'input[id*="pm_label"]', function(e){

            let keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if(keycode === 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

                e.preventDefault();
                $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

            }

        });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    $pm_available_list.change(function(){

        var $pm_available_checkbox = $(this);

        $('#pm-'+$pm_available_checkbox.prop('id')).toggle(); // Show/hide a PM settings
        $('#'+$pm_available_checkbox.prop('id')+'-commission-wrapper').toggle(); // Show/hide a PM commission field

        var $sortable_pm = $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]');

        // Add/remove a sortable block from the PM order settings:
        if($pm_available_checkbox.prop('checked') && $sortable_pm.length) {
            $sortable_pm.show();
        } else {
            $sortable_pm.hide();
        }

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

    $pm_list_empty_block.on('click.leyka', function(e){

        $pm_list_empty_block.addClass('comment-displayed').find('.pm-list-empty-base-content').hide();
        $pm_list_empty_block.find('.pm-list-empty-comment').show();

    });

    $('.gateway-turn-off').click(function(e){

        e.preventDefault();

        // Emulate a change() checkboxes event manually, to lessen the ajax requests to update the PM order:
        $pm_available_list.filter(':checked').each(function(){

            let $pm_available_checkbox = $(this);

            $pm_available_checkbox.removeAttr('checked'); // Uncheck the active PM checkbox
            $('#pm-'+$pm_available_checkbox.prop('id')).hide(); // Hide a PM settings
            $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]').hide(); // Hide a PM sortable entry

        });

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

});

// Yandex.Kassa old/new API options:
jQuery(document).ready(function($){

    var $gateway_settings = $('.single-gateway-settings.gateway-yandex'),
        $new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        var $smart_payment_pm_field = $('.gateway-pm-list').find(':input.pm-available[value="yandex-yandex_all"]');

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

            if($smart_payment_pm_field.length) {

                if($smart_payment_pm_field.prop('checked')) {

                    $smart_payment_pm_field.prop('checked', false).change();
                    $new_api_used.data('yandex-all-pm-removed', true);

                }

                $('.settings-block#yandex-yandex_all').hide();

            }

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

            $('.settings-block#yandex-yandex_all').show();

            if($new_api_used.data('yandex-all-pm-removed')) {

                $new_api_used.data('yandex-all-pm-removed', false);
                $smart_payment_pm_field.prop('checked', true).change();

            }

        }

    }).change();

});

// PayPal old/new API options:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-paypal'),
        $new_api_used = $gateway_settings.find('input[name="leyka_paypal_rest_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});

// PM list scroll in gateways cards:
jQuery(document).ready(function($){

    let icon_width = 40;

    if( !$('.gateways-cards-list').length ) {
        return;
    }

    function scroll_pm_icons_list($pm_icons_list, move_step) {

        let $movable_wrapper = $pm_icons_list.find('.pm-icons-wrapper'),
            $icons_container = $pm_icons_list.find('.pm-icons'),
            $icons_scroll = $pm_icons_list.find('.pm-icons-scroll'),
            current_left_offset = parseInt($.trim($movable_wrapper.css('left').replace('px', ''))),
            new_left_offset = current_left_offset - move_step;
        
        if(new_left_offset >= 0) {

            new_left_offset = 0;
            $pm_icons_list.find('.scroll-arrow.left').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.left').show();
        }
        
        if($icons_container.width() + new_left_offset <= $icons_scroll.width()) {

            new_left_offset = -($icons_container.width() - $icons_scroll.width());
            $pm_icons_list.find('.scroll-arrow.right').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }
        
        $movable_wrapper.css('left', String(new_left_offset) + 'px');

    }

    $('.gateway-card-supported-pm-list').each(function(){
        
        let $pm_icons_list = $(this);
        
        $(this).find('.scroll-arrow').click(function(){
            if($(this).hasClass('left')) {
                scroll_pm_icons_list( $pm_icons_list, -icon_width );
            } else {
                scroll_pm_icons_list( $pm_icons_list, icon_width );
            }
        });
        
        let $icons_container = $pm_icons_list.find('.pm-icons'),
            icons_width = icon_width * $icons_container.find('img').length;
        
        if(icons_width > $pm_icons_list.width()) {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }

    });
    
});

if(jQuery.ui.autocomplete) {
	let $ = jQuery;

	$.widget("ui.autocomplete", $.ui.autocomplete, {
	    options : $.extend({}, this.options, {
	        multiselect: false,
	        search_on_focus: false,
	        leyka_select_callback: false,
	        pre_selected_values: []
	    }),
	    _create: function(){
	        this._super();

	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
	        	self.options['position'] = { my: "left-5px top+6px", at: "left bottom", collision: "none" };

	            self.selectedItems = {};           

	            self.placeholder = $("<div></div>")
	            	.addClass('placeholder')
	            	.text(self.element.prop('placeholder'));

            	self.element.prop('placeholder', '')

	            self.multiselect = $("<div></div>")
	                .addClass("ui-autocomplete-multiselect ui-state-default ui-widget")
	                .css("width", self.element.width())
	                .insertBefore(self.element)
	                .append(self.placeholder)
	                .append(self.element)
	                .bind("click.autocomplete", function(){
	                	self.placeholder.hide();
	                    self.element.css('display', 'block');
	                	self.element.show();
	                    self.element.focus();
	                });

	            var fontSize = parseInt(self.element.css("fontSize"), 10);
	            function autoSize(e){
	                var $this = $(this);
	                $this.width(1).width(this.scrollWidth+fontSize-1);
	            };

	            var kc = $.ui.keyCode;
	            self.element.bind({
	                "keydown.autocomplete": function(e){
	                    if ((this.value === "") && (e.keyCode == kc.BACKSPACE)) {
	                        var prev = self.element.prev();
	                        delete self.selectedItems[prev.text()];
	                        prev.remove();
	                    }
	                },
	                "focus.autocomplete": function(){
	                	if(o.search_on_focus && this.value === "") {
	                		self.search("");
	                	}
	                	else {
							self.multiselect.addClass("ui-state-active");
	                	}
	                },
	                "blur.autocomplete": function(){
	                	self.multiselect.removeClass("ui-state-active");
	                	if(self.multiselect.find('.ui-autocomplete-multiselect-item').length == 0) {
	                    	self.placeholder.show();
	                    	self.element.hide();
	                    }
	                },

	                "keypress.autocomplete change.autocomplete focus.autocomplete blur.autocomplete": autoSize
	            }).trigger("change");

	            o.select = o.select || function(e, ui) {
	            	if(typeof(self.selectedItems[ui.item.value]) !== "undefined") {
	            		return false;
	            	}

	                $("<div></div>")
	                    .addClass("ui-autocomplete-multiselect-item")
	                    .text(ui.item.label)
	                    .data('value', ui.item.value)
	                    .append(
	                        $("<span></span>")
	                            .addClass("ui-icon ui-icon-close")
	                            .click(function(clickEvent){
	                                var item = $(this).parent();
	                                delete self.selectedItems[item.data('value')];
	                                item.remove();

	                                if(jQuery.isEmptyObject(self.selectedItems)) {
				                    	self.placeholder.show();
				                    	self.element.hide();
				                    }

	                                o.leyka_select_callback(self.selectedItems);

	                                if(clickEvent) {
	                                	clickEvent.stopPropagation();
	                            	}
	                            })
	                    )
	                    .insertBefore(self.element);
	                
	                //self.selectedItems[ui.item.label] = ui.item;
	                self.selectedItems[ui.item.value] = ui.item;
	                self._value("");
	                o.leyka_select_callback(self.selectedItems);
	                return false;
	            }

                if(o.pre_selected_values.length) {
                	$.each(o.pre_selected_values, function(index, el){
                		o.select(null, el);
	                });
                	self.placeholder.hide();
                    self.element.css('display', 'block');
                	self.element.show();
                }
	            
	            /*self.options.open = function(e, ui) {
	                var pos = self.multiselect.position();
	                pos.top += self.multiselect.height();
	                self.menu.element.position(pos);
	            }*/
	        }

	        return this;
	    },
	    reset: function(){
	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
	        	self.selectedItems = [];
	        	self.element.parent().find('.ui-autocomplete-multiselect-item').remove();
            	self.placeholder.show();
            	self.element.hide();

            	o.leyka_select_callback(self.selectedItems);
	    	}
	    }
	});	
}

/* jQuery ui-datepicker extension */

/**
 *
 * https://gist.github.com/Artemeey/8bacd37964a8069a2eeee8c9b0bd2e44/
 *
 * Version: 1.0 (15.06.2016)
 * Requires: jQuery v1.8+
 * Requires: jQuery-UI v1.10+
 *
 * Copyright (c) 2016 Artemeey
 * Under MIT and GPL licenses:
 *  http://www.opensource.org/licenses/mit-license.php
 *  http://www.gnu.org/licenses/gpl.html
 *
 * sample:
 * $('.datepicker').datepicker({
		range:'period', // 'period' or 'multiple'
		onSelect:function(dateText, inst, extensionRange){
			// range - new argument!
			switch(inst.settings.range){
				case 'period':
					console.log(extensionRange.startDateText);
					console.log(extensionRange.endDateText);
					console.log(extensionRange.startDate);
					console.log(extensionRange.endDate);
					break;
				case 'multiple':
					console.log(extensionRange.dates); // object, width UTC-TIME keys
					console.log(extensionRange.datesText); // object, width UTC-TIME keys
					break;
			}
		}
	});
 *
 * extension styles:
 * .selected
 * .selected-start
 * .selected-end
 * .first-of-month
 * .last-of-month
 *
 */
if(jQuery.datepicker) {
	let $ = jQuery;

$.datepicker._get_original = $.datepicker._get;
$.datepicker._get = function(inst, name){
	var func = $.datepicker._get_original(inst, name);

	var range = inst.settings['range'];
	if(!range) return func;

	var that = this;

	switch(range){
		case 'period':
		case 'multiple':
			var datepickerExtension = $(this.dpDiv).data('datepickerExtensionRange');
			if(!datepickerExtension){
				datepickerExtension = new _datepickerExtension();
				$(this.dpDiv).data('datepickerExtensionRange', datepickerExtension);
			}
			datepickerExtension.range = range;
			datepickerExtension.range_multiple_max = inst.settings['range_multiple_max'] || 0;

			switch(name){
				case 'onSelect':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(dateText, inst){
						datepickerExtension.onSelect(dateText, inst);
						func_original(dateText, inst, datepickerExtension);

						 // hide fix
						that._datepickerShowing = false;
						setTimeout(function(){
							that._updateDatepicker(inst);
							that._datepickerShowing = true;
						});

						console.log('onSelect...');
						datepickerExtension.setClassActive(inst);
					};

					break;
				case 'beforeShowDay':
					var func_original = func;
					if(!func_original) func_original = function(){ return [true, '']; };

					func = function(date){
						var state = func_original(date);
						state = datepickerExtension.fillDay(date, state);

						return state;
					};

					break;
				case 'beforeShow':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(input, inst){
						func_original(input, inst);

						datepickerExtension.setClassActive(inst);
					};

					break;
				case 'onChangeMonthYear':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(year, month, inst){
						func_original(year, month, inst);

						datepickerExtension.setClassActive(inst);
					};

					break;
			}
			break;
	}

	return func;
};

$.datepicker._setDate_original = $.datepicker._setDate;
$.datepicker._setDate = function(inst, date, noChange){
	var range = inst.settings['range'];
	if(!range) return $.datepicker._setDate_original(inst, date, noChange);

	var datepickerExtension = this.dpDiv.data('datepickerExtensionRange');
	if(!datepickerExtension) return $.datepicker._setDate_original(inst, date, noChange);

	switch(range){
		case 'period':
			if(!(typeof(date) == 'object' && date.length != undefined)){ date = [date, date]; }

			datepickerExtension.step = 0;

			$.datepicker._setDate_original(inst, date[0], noChange);
			datepickerExtension.startDate = this._getDate(inst);
			datepickerExtension.startDateText = this._formatDate(inst);

			if(!date[1]) {
				date[1] = date[0];
			}
			$.datepicker._setDate_original(inst, date[1], noChange);
			datepickerExtension.endDate = this._getDate(inst);
			datepickerExtension.endDateText = this._formatDate(inst);

			datepickerExtension.setClassActive(inst);

			break;
		case 'multiple':
			if(!(typeof(date) == 'object' && date.length != undefined)){ date = [date]; }

			datepickerExtension.dates = [];
			datepickerExtension.datesText = [];

			var that = this;
			$.map(date, function(date_i){
				$.datepicker._setDate_original(inst, date_i, noChange);
				datepickerExtension.dates.push(that._getDate(inst));
				datepickerExtension.datesText.push(that._formatDate(inst));
			});

			datepickerExtension.setClassActive(inst);

			break;
	}
};

var _datepickerExtension = function(){
	this.range = false,
	this.range_multiple_max = 0,
	this.step = 0,
	this.dates = [],
	this.datesText = [],
	this.startDate = null,
	this.endDate = null,
	this.startDateText = '',
	this.endDateText = '',
	this.onSelect = function(dateText, inst){
		switch(this.range){
			case 'period': return this.onSelectPeriod(dateText, inst); break;
			case 'multiple': return this.onSelectMultiple(dateText, inst); break;
		}
	},
	this.onSelectPeriod = function(dateText, inst){
		this.step++;
		this.step %= 2;

		if(this.step){
			// выбирается первая дата
			this.startDate = this.getSelectedDate(inst);
			this.endDate = this.startDate;

			this.startDateText = dateText;
			this.endDateText = this.startDateText;
		}else{
			// выбирается вторая дата
			this.endDate = this.getSelectedDate(inst);
			this.endDateText = dateText;

			if(this.startDate.getTime() > this.endDate.getTime()){
				this.endDate = this.startDate;
				this.startDate = this.getSelectedDate(inst);

				this.endDateText = this.startDateText;
				this.startDateText = dateText;
			}
		}
	},
	this.onSelectMultiple = function(dateText, inst){
		var date = this.getSelectedDate(inst);

		var index = -1;
		$.map(this.dates, function(date_i, index_date){
			if(date_i.getTime() == date.getTime()) index = index_date;
		});
		var indexText = $.inArray(dateText, this.datesText);

		if(index != -1) this.dates.splice(index, 1);
		else this.dates.push(date);

		if(indexText != -1) this.datesText.splice(indexText, 1);
		else this.datesText.push(dateText);

		if(this.range_multiple_max && this.dates.length > this.range_multiple_max){
			this.dates.splice(0, 1);
			this.datesText.splice(0, 1);
		}
	},
	this.fillDay = function(date, state){
		var _class = state[1];

		if(date.getDate() == 1) _class += ' first-of-month';
		if(date.getDate() == new Date(date.getFullYear(), date.getMonth()+1, 0).getDate()) _class += ' last-of-month';

		state[1] = _class.trim();

		switch(this.range){
			case 'period': return this.fillDayPeriod(date, state); break;
			case 'multiple': return this.fillDayMultiple(date, state); break;
		}
	},
	this.fillDayPeriod = function(date, state){
		if(!this.startDate || !this.endDate) return state;

		var _class = state[1];

		if(date >= this.startDate && date <= this.endDate) _class += ' selected';
		if(date.getTime() == this.startDate.getTime()) _class += ' selected-start';
		if(date.getTime() == this.endDate.getTime()) _class += ' selected-end';

		state[1] = _class.trim();

		return state;
	},
	this.fillDayMultiple = function(date, state){
		var _class = state[1];

		var date_is_selected = false;
		$.map(this.dates, function(date_i){
			if(date_i.getTime() == date.getTime()) date_is_selected = true;
		});
		if(date_is_selected) _class += ' selected selected-start selected-end';

		state[1] = _class.trim();

		return state;
	},
	this.getSelectedDate = function(inst){
		return new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
	};
	this.setClassActive = function(inst){
		var that = this;
		setTimeout(function(){
			$('td.selected > *', inst.dpDiv).addClass('ui-state-active');
			if(that.range == 'multiple') $('td:not(.selected)', inst.dpDiv).removeClass('ui-datepicker-current-day').children().removeClass('ui-state-active');
		});
	};
}; 	

}
/*!
 * jquery.inputmask.bundle.js
 * https://github.com/RobinHerbots/Inputmask
 * Copyright (c) 2010 - 2018 Robin Herbots
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
 * Version: 4.0.4
 */

(function(modules){var installedModules={};function __webpack_require__(moduleId){if(installedModules[moduleId]){return installedModules[moduleId].exports}var module=installedModules[moduleId]={i:moduleId,l:false,exports:{}};modules[moduleId].call(module.exports,module,module.exports,__webpack_require__);module.l=true;return module.exports}__webpack_require__.m=modules;__webpack_require__.c=installedModules;__webpack_require__.d=function(exports,name,getter){if(!__webpack_require__.o(exports,name)){Object.defineProperty(exports,name,{enumerable:true,get:getter})}};__webpack_require__.r=function(exports){if(typeof Symbol!=="undefined"&&Symbol.toStringTag){Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"})}Object.defineProperty(exports,"__esModule",{value:true})};__webpack_require__.t=function(value,mode){if(mode&1)value=__webpack_require__(value);if(mode&8)return value;if(mode&4&&typeof value==="object"&&value&&value.__esModule)return value;var ns=Object.create(null);__webpack_require__.r(ns);Object.defineProperty(ns,"default",{enumerable:true,value:value});if(mode&2&&typeof value!="string")for(var key in value)__webpack_require__.d(ns,key,function(key){return value[key]}.bind(null,key));return ns};__webpack_require__.n=function(module){var getter=module&&module.__esModule?function getDefault(){return module["default"]}:function getModuleExports(){return module};__webpack_require__.d(getter,"a",getter);return getter};__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property)};__webpack_require__.p="";return __webpack_require__(__webpack_require__.s=0)})([function(module,exports,__webpack_require__){"use strict";__webpack_require__(1);__webpack_require__(6);__webpack_require__(7);var _inputmask=__webpack_require__(2);var _inputmask2=_interopRequireDefault(_inputmask);var _inputmask3=__webpack_require__(3);var _inputmask4=_interopRequireDefault(_inputmask3);var _jquery=__webpack_require__(4);var _jquery2=_interopRequireDefault(_jquery);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}if(_inputmask4.default===_jquery2.default){__webpack_require__(8)}window.Inputmask=_inputmask2.default},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(2)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function(Inputmask){Inputmask.extendDefinitions({A:{validator:"[A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",casing:"upper"},"&":{validator:"[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",casing:"upper"},"#":{validator:"[0-9A-Fa-f]",casing:"upper"}});Inputmask.extendAliases({cssunit:{regex:"[+-]?[0-9]+\\.?([0-9]+)?(px|em|rem|ex|%|in|cm|mm|pt|pc)"},url:{regex:"(https?|ftp)//.*",autoUnmask:false},ip:{mask:"i[i[i]].i[i[i]].i[i[i]].i[i[i]]",definitions:{i:{validator:function validator(chrs,maskset,pos,strict,opts){if(pos-1>-1&&maskset.buffer[pos-1]!=="."){chrs=maskset.buffer[pos-1]+chrs;if(pos-2>-1&&maskset.buffer[pos-2]!=="."){chrs=maskset.buffer[pos-2]+chrs}else chrs="0"+chrs}else chrs="00"+chrs;return new RegExp("25[0-5]|2[0-4][0-9]|[01][0-9][0-9]").test(chrs)}}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return maskedValue},inputmode:"numeric"},email:{mask:"*{1,64}[.*{1,64}][.*{1,64}][.*{1,63}]@-{1,63}.-{1,63}[.-{1,63}][.-{1,63}]",greedy:false,casing:"lower",onBeforePaste:function onBeforePaste(pastedValue,opts){pastedValue=pastedValue.toLowerCase();return pastedValue.replace("mailto:","")},definitions:{"*":{validator:"[0-9\uff11-\uff19A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5!#$%&'*+/=?^_`{|}~-]"},"-":{validator:"[0-9A-Za-z-]"}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return maskedValue},inputmode:"email"},mac:{mask:"##:##:##:##:##:##"},vin:{mask:"V{13}9{4}",definitions:{V:{validator:"[A-HJ-NPR-Za-hj-npr-z\\d]",casing:"upper"}},clearIncomplete:true,autoUnmask:true}});return Inputmask})},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(3),__webpack_require__(5)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function($,window,undefined){var document=window.document,ua=navigator.userAgent,ie=ua.indexOf("MSIE ")>0||ua.indexOf("Trident/")>0,mobile=isInputEventSupported("touchstart"),iemobile=/iemobile/i.test(ua),iphone=/iphone/i.test(ua)&&!iemobile;function Inputmask(alias,options,internal){if(!(this instanceof Inputmask)){return new Inputmask(alias,options,internal)}this.el=undefined;this.events={};this.maskset=undefined;this.refreshValue=false;if(internal!==true){if($.isPlainObject(alias)){options=alias}else{options=options||{};if(alias)options.alias=alias}this.opts=$.extend(true,{},this.defaults,options);this.noMasksCache=options&&options.definitions!==undefined;this.userOptions=options||{};this.isRTL=this.opts.numericInput;resolveAlias(this.opts.alias,options,this.opts)}}Inputmask.prototype={dataAttribute:"data-inputmask",defaults:{placeholder:"_",optionalmarker:["[","]"],quantifiermarker:["{","}"],groupmarker:["(",")"],alternatormarker:"|",escapeChar:"\\",mask:null,regex:null,oncomplete:$.noop,onincomplete:$.noop,oncleared:$.noop,repeat:0,greedy:false,autoUnmask:false,removeMaskOnSubmit:false,clearMaskOnLostFocus:true,insertMode:true,clearIncomplete:false,alias:null,onKeyDown:$.noop,onBeforeMask:null,onBeforePaste:function onBeforePaste(pastedValue,opts){return $.isFunction(opts.onBeforeMask)?opts.onBeforeMask.call(this,pastedValue,opts):pastedValue},onBeforeWrite:null,onUnMask:null,showMaskOnFocus:true,showMaskOnHover:true,onKeyValidation:$.noop,skipOptionalPartCharacter:" ",numericInput:false,rightAlign:false,undoOnEscape:true,radixPoint:"",_radixDance:false,groupSeparator:"",keepStatic:null,positionCaretOnTab:true,tabThrough:false,supportsInputType:["text","tel","url","password","search"],ignorables:[8,9,13,19,27,33,34,35,36,37,38,39,40,45,46,93,112,113,114,115,116,117,118,119,120,121,122,123,0,229],isComplete:null,preValidation:null,postValidation:null,staticDefinitionSymbol:undefined,jitMasking:false,nullable:true,inputEventOnly:false,noValuePatching:false,positionCaretOnClick:"lvp",casing:null,inputmode:"verbatim",colorMask:false,disablePredictiveText:false,importDataAttributes:true,shiftPositions:true},definitions:{9:{validator:"[0-9\uff11-\uff19]",definitionSymbol:"*"},a:{validator:"[A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",definitionSymbol:"*"},"*":{validator:"[0-9\uff11-\uff19A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]"}},aliases:{},masksCache:{},mask:function mask(elems){var that=this;function importAttributeOptions(npt,opts,userOptions,dataAttribute){if(opts.importDataAttributes===true){var importOption=function importOption(option,optionData){optionData=optionData!==undefined?optionData:npt.getAttribute(dataAttribute+"-"+option);if(optionData!==null){if(typeof optionData==="string"){if(option.indexOf("on")===0)optionData=window[optionData];else if(optionData==="false")optionData=false;else if(optionData==="true")optionData=true}userOptions[option]=optionData}};var attrOptions=npt.getAttribute(dataAttribute),option,dataoptions,optionData,p;if(attrOptions&&attrOptions!==""){attrOptions=attrOptions.replace(/'/g,'"');dataoptions=JSON.parse("{"+attrOptions+"}")}if(dataoptions){optionData=undefined;for(p in dataoptions){if(p.toLowerCase()==="alias"){optionData=dataoptions[p];break}}}importOption("alias",optionData);if(userOptions.alias){resolveAlias(userOptions.alias,userOptions,opts)}for(option in opts){if(dataoptions){optionData=undefined;for(p in dataoptions){if(p.toLowerCase()===option.toLowerCase()){optionData=dataoptions[p];break}}}importOption(option,optionData)}}$.extend(true,opts,userOptions);if(npt.dir==="rtl"||opts.rightAlign){npt.style.textAlign="right"}if(npt.dir==="rtl"||opts.numericInput){npt.dir="ltr";npt.removeAttribute("dir");opts.isRTL=true}return Object.keys(userOptions).length}if(typeof elems==="string"){elems=document.getElementById(elems)||document.querySelectorAll(elems)}elems=elems.nodeName?[elems]:elems;$.each(elems,function(ndx,el){var scopedOpts=$.extend(true,{},that.opts);if(importAttributeOptions(el,scopedOpts,$.extend(true,{},that.userOptions),that.dataAttribute)){var maskset=generateMaskSet(scopedOpts,that.noMasksCache);if(maskset!==undefined){if(el.inputmask!==undefined){el.inputmask.opts.autoUnmask=true;el.inputmask.remove()}el.inputmask=new Inputmask(undefined,undefined,true);el.inputmask.opts=scopedOpts;el.inputmask.noMasksCache=that.noMasksCache;el.inputmask.userOptions=$.extend(true,{},that.userOptions);el.inputmask.isRTL=scopedOpts.isRTL||scopedOpts.numericInput;el.inputmask.el=el;el.inputmask.maskset=maskset;$.data(el,"_inputmask_opts",scopedOpts);maskScope.call(el.inputmask,{action:"mask"})}}});return elems&&elems[0]?elems[0].inputmask||this:this},option:function option(options,noremask){if(typeof options==="string"){return this.opts[options]}else if((typeof options==="undefined"?"undefined":_typeof(options))==="object"){$.extend(this.userOptions,options);if(this.el&&noremask!==true){this.mask(this.el)}return this}},unmaskedvalue:function unmaskedvalue(value){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"unmaskedvalue",value:value})},remove:function remove(){return maskScope.call(this,{action:"remove"})},getemptymask:function getemptymask(){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"getemptymask"})},hasMaskedValue:function hasMaskedValue(){return!this.opts.autoUnmask},isComplete:function isComplete(){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"isComplete"})},getmetadata:function getmetadata(){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"getmetadata"})},isValid:function isValid(value){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"isValid",value:value})},format:function format(value,metadata){this.maskset=this.maskset||generateMaskSet(this.opts,this.noMasksCache);return maskScope.call(this,{action:"format",value:value,metadata:metadata})},setValue:function setValue(value){if(this.el){$(this.el).trigger("setvalue",[value])}},analyseMask:function analyseMask(mask,regexMask,opts){var tokenizer=/(?:[?*+]|\{[0-9\+\*]+(?:,[0-9\+\*]*)?(?:\|[0-9\+\*]*)?\})|[^.?*+^${[]()|\\]+|./g,regexTokenizer=/\[\^?]?(?:[^\\\]]+|\\[\S\s]?)*]?|\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\]+|./g,escaped=false,currentToken=new MaskToken,match,m,openenings=[],maskTokens=[],openingToken,currentOpeningToken,alternator,lastMatch,groupToken;function MaskToken(isGroup,isOptional,isQuantifier,isAlternator){this.matches=[];this.openGroup=isGroup||false;this.alternatorGroup=false;this.isGroup=isGroup||false;this.isOptional=isOptional||false;this.isQuantifier=isQuantifier||false;this.isAlternator=isAlternator||false;this.quantifier={min:1,max:1}}function insertTestDefinition(mtoken,element,position){position=position!==undefined?position:mtoken.matches.length;var prevMatch=mtoken.matches[position-1];if(regexMask){if(element.indexOf("[")===0||escaped&&/\\d|\\s|\\w]/i.test(element)||element==="."){mtoken.matches.splice(position++,0,{fn:new RegExp(element,opts.casing?"i":""),optionality:false,newBlockMarker:prevMatch===undefined?"master":prevMatch.def!==element,casing:null,def:element,placeholder:undefined,nativeDef:element})}else{if(escaped)element=element[element.length-1];$.each(element.split(""),function(ndx,lmnt){prevMatch=mtoken.matches[position-1];mtoken.matches.splice(position++,0,{fn:null,optionality:false,newBlockMarker:prevMatch===undefined?"master":prevMatch.def!==lmnt&&prevMatch.fn!==null,casing:null,def:opts.staticDefinitionSymbol||lmnt,placeholder:opts.staticDefinitionSymbol!==undefined?lmnt:undefined,nativeDef:(escaped?"'":"")+lmnt})})}escaped=false}else{var maskdef=(opts.definitions?opts.definitions[element]:undefined)||Inputmask.prototype.definitions[element];if(maskdef&&!escaped){mtoken.matches.splice(position++,0,{fn:maskdef.validator?typeof maskdef.validator=="string"?new RegExp(maskdef.validator,opts.casing?"i":""):new function(){this.test=maskdef.validator}:new RegExp("."),optionality:false,newBlockMarker:prevMatch===undefined?"master":prevMatch.def!==(maskdef.definitionSymbol||element),casing:maskdef.casing,def:maskdef.definitionSymbol||element,placeholder:maskdef.placeholder,nativeDef:element})}else{mtoken.matches.splice(position++,0,{fn:null,optionality:false,newBlockMarker:prevMatch===undefined?"master":prevMatch.def!==element&&prevMatch.fn!==null,casing:null,def:opts.staticDefinitionSymbol||element,placeholder:opts.staticDefinitionSymbol!==undefined?element:undefined,nativeDef:(escaped?"'":"")+element});escaped=false}}}function verifyGroupMarker(maskToken){if(maskToken&&maskToken.matches){$.each(maskToken.matches,function(ndx,token){var nextToken=maskToken.matches[ndx+1];if((nextToken===undefined||nextToken.matches===undefined||nextToken.isQuantifier===false)&&token&&token.isGroup){token.isGroup=false;if(!regexMask){insertTestDefinition(token,opts.groupmarker[0],0);if(token.openGroup!==true){insertTestDefinition(token,opts.groupmarker[1])}}}verifyGroupMarker(token)})}}function defaultCase(){if(openenings.length>0){currentOpeningToken=openenings[openenings.length-1];insertTestDefinition(currentOpeningToken,m);if(currentOpeningToken.isAlternator){alternator=openenings.pop();for(var mndx=0;mndx<alternator.matches.length;mndx++){if(alternator.matches[mndx].isGroup)alternator.matches[mndx].isGroup=false}if(openenings.length>0){currentOpeningToken=openenings[openenings.length-1];currentOpeningToken.matches.push(alternator)}else{currentToken.matches.push(alternator)}}}else{insertTestDefinition(currentToken,m)}}function reverseTokens(maskToken){function reverseStatic(st){if(st===opts.optionalmarker[0])st=opts.optionalmarker[1];else if(st===opts.optionalmarker[1])st=opts.optionalmarker[0];else if(st===opts.groupmarker[0])st=opts.groupmarker[1];else if(st===opts.groupmarker[1])st=opts.groupmarker[0];return st}maskToken.matches=maskToken.matches.reverse();for(var match in maskToken.matches){if(maskToken.matches.hasOwnProperty(match)){var intMatch=parseInt(match);if(maskToken.matches[match].isQuantifier&&maskToken.matches[intMatch+1]&&maskToken.matches[intMatch+1].isGroup){var qt=maskToken.matches[match];maskToken.matches.splice(match,1);maskToken.matches.splice(intMatch+1,0,qt)}if(maskToken.matches[match].matches!==undefined){maskToken.matches[match]=reverseTokens(maskToken.matches[match])}else{maskToken.matches[match]=reverseStatic(maskToken.matches[match])}}}return maskToken}function groupify(matches){var groupToken=new MaskToken(true);groupToken.openGroup=false;groupToken.matches=matches;return groupToken}if(regexMask){opts.optionalmarker[0]=undefined;opts.optionalmarker[1]=undefined}while(match=regexMask?regexTokenizer.exec(mask):tokenizer.exec(mask)){m=match[0];if(regexMask){switch(m.charAt(0)){case"?":m="{0,1}";break;case"+":case"*":m="{"+m+"}";break}}if(escaped){defaultCase();continue}switch(m.charAt(0)){case"(?=":break;case"(?!":break;case"(?<=":break;case"(?<!":break;case opts.escapeChar:escaped=true;if(regexMask){defaultCase()}break;case opts.optionalmarker[1]:case opts.groupmarker[1]:openingToken=openenings.pop();openingToken.openGroup=false;if(openingToken!==undefined){if(openenings.length>0){currentOpeningToken=openenings[openenings.length-1];currentOpeningToken.matches.push(openingToken);if(currentOpeningToken.isAlternator){alternator=openenings.pop();for(var mndx=0;mndx<alternator.matches.length;mndx++){alternator.matches[mndx].isGroup=false;alternator.matches[mndx].alternatorGroup=false}if(openenings.length>0){currentOpeningToken=openenings[openenings.length-1];currentOpeningToken.matches.push(alternator)}else{currentToken.matches.push(alternator)}}}else{currentToken.matches.push(openingToken)}}else defaultCase();break;case opts.optionalmarker[0]:openenings.push(new MaskToken(false,true));break;case opts.groupmarker[0]:openenings.push(new MaskToken(true));break;case opts.quantifiermarker[0]:var quantifier=new MaskToken(false,false,true);m=m.replace(/[{}]/g,"");var mqj=m.split("|"),mq=mqj[0].split(","),mq0=isNaN(mq[0])?mq[0]:parseInt(mq[0]),mq1=mq.length===1?mq0:isNaN(mq[1])?mq[1]:parseInt(mq[1]);if(mq0==="*"||mq0==="+"){mq0=mq1==="*"?0:1}quantifier.quantifier={min:mq0,max:mq1,jit:mqj[1]};var matches=openenings.length>0?openenings[openenings.length-1].matches:currentToken.matches;match=matches.pop();if(match.isAlternator){matches.push(match);matches=match.matches;var groupToken=new MaskToken(true);var tmpMatch=matches.pop();matches.push(groupToken);matches=groupToken.matches;match=tmpMatch}if(!match.isGroup){match=groupify([match])}matches.push(match);matches.push(quantifier);break;case opts.alternatormarker:var groupQuantifier=function groupQuantifier(matches){var lastMatch=matches.pop();if(lastMatch.isQuantifier){lastMatch=groupify([matches.pop(),lastMatch])}return lastMatch};if(openenings.length>0){currentOpeningToken=openenings[openenings.length-1];var subToken=currentOpeningToken.matches[currentOpeningToken.matches.length-1];if(currentOpeningToken.openGroup&&(subToken.matches===undefined||subToken.isGroup===false&&subToken.isAlternator===false)){lastMatch=openenings.pop()}else{lastMatch=groupQuantifier(currentOpeningToken.matches)}}else{lastMatch=groupQuantifier(currentToken.matches)}if(lastMatch.isAlternator){openenings.push(lastMatch)}else{if(lastMatch.alternatorGroup){alternator=openenings.pop();lastMatch.alternatorGroup=false}else{alternator=new MaskToken(false,false,false,true)}alternator.matches.push(lastMatch);openenings.push(alternator);if(lastMatch.openGroup){lastMatch.openGroup=false;var alternatorGroup=new MaskToken(true);alternatorGroup.alternatorGroup=true;openenings.push(alternatorGroup)}}break;default:defaultCase()}}while(openenings.length>0){openingToken=openenings.pop();currentToken.matches.push(openingToken)}if(currentToken.matches.length>0){verifyGroupMarker(currentToken);maskTokens.push(currentToken)}if(opts.numericInput||opts.isRTL){reverseTokens(maskTokens[0])}return maskTokens}};Inputmask.extendDefaults=function(options){$.extend(true,Inputmask.prototype.defaults,options)};Inputmask.extendDefinitions=function(definition){$.extend(true,Inputmask.prototype.definitions,definition)};Inputmask.extendAliases=function(alias){$.extend(true,Inputmask.prototype.aliases,alias)};Inputmask.format=function(value,options,metadata){return Inputmask(options).format(value,metadata)};Inputmask.unmask=function(value,options){return Inputmask(options).unmaskedvalue(value)};Inputmask.isValid=function(value,options){return Inputmask(options).isValid(value)};Inputmask.remove=function(elems){if(typeof elems==="string"){elems=document.getElementById(elems)||document.querySelectorAll(elems)}elems=elems.nodeName?[elems]:elems;$.each(elems,function(ndx,el){if(el.inputmask)el.inputmask.remove()})};Inputmask.setValue=function(elems,value){if(typeof elems==="string"){elems=document.getElementById(elems)||document.querySelectorAll(elems)}elems=elems.nodeName?[elems]:elems;$.each(elems,function(ndx,el){if(el.inputmask)el.inputmask.setValue(value);else $(el).trigger("setvalue",[value])})};Inputmask.escapeRegex=function(str){var specials=["/",".","*","+","?","|","(",")","[","]","{","}","\\","$","^"];return str.replace(new RegExp("(\\"+specials.join("|\\")+")","gim"),"\\$1")};Inputmask.keyCode={BACKSPACE:8,BACKSPACE_SAFARI:127,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,PAGE_DOWN:34,PAGE_UP:33,RIGHT:39,SPACE:32,TAB:9,UP:38,X:88,CONTROL:17};Inputmask.dependencyLib=$;function resolveAlias(aliasStr,options,opts){var aliasDefinition=Inputmask.prototype.aliases[aliasStr];if(aliasDefinition){if(aliasDefinition.alias)resolveAlias(aliasDefinition.alias,undefined,opts);$.extend(true,opts,aliasDefinition);$.extend(true,opts,options);return true}else if(opts.mask===null){opts.mask=aliasStr}return false}function generateMaskSet(opts,nocache){function generateMask(mask,metadata,opts){var regexMask=false;if(mask===null||mask===""){regexMask=opts.regex!==null;if(regexMask){mask=opts.regex;mask=mask.replace(/^(\^)(.*)(\$)$/,"$2")}else{regexMask=true;mask=".*"}}if(mask.length===1&&opts.greedy===false&&opts.repeat!==0){opts.placeholder=""}if(opts.repeat>0||opts.repeat==="*"||opts.repeat==="+"){var repeatStart=opts.repeat==="*"?0:opts.repeat==="+"?1:opts.repeat;mask=opts.groupmarker[0]+mask+opts.groupmarker[1]+opts.quantifiermarker[0]+repeatStart+","+opts.repeat+opts.quantifiermarker[1]}var masksetDefinition,maskdefKey=regexMask?"regex_"+opts.regex:opts.numericInput?mask.split("").reverse().join(""):mask;if(Inputmask.prototype.masksCache[maskdefKey]===undefined||nocache===true){masksetDefinition={mask:mask,maskToken:Inputmask.prototype.analyseMask(mask,regexMask,opts),validPositions:{},_buffer:undefined,buffer:undefined,tests:{},excludes:{},metadata:metadata,maskLength:undefined,jitOffset:{}};if(nocache!==true){Inputmask.prototype.masksCache[maskdefKey]=masksetDefinition;masksetDefinition=$.extend(true,{},Inputmask.prototype.masksCache[maskdefKey])}}else masksetDefinition=$.extend(true,{},Inputmask.prototype.masksCache[maskdefKey]);return masksetDefinition}var ms;if($.isFunction(opts.mask)){opts.mask=opts.mask(opts)}if($.isArray(opts.mask)){if(opts.mask.length>1){if(opts.keepStatic===null){opts.keepStatic="auto";for(var i=0;i<opts.mask.length;i++){if(opts.mask[i].charAt(0)!==opts.mask[0].charAt(0)){opts.keepStatic=true;break}}}var altMask=opts.groupmarker[0];$.each(opts.isRTL?opts.mask.reverse():opts.mask,function(ndx,msk){if(altMask.length>1){altMask+=opts.groupmarker[1]+opts.alternatormarker+opts.groupmarker[0]}if(msk.mask!==undefined&&!$.isFunction(msk.mask)){altMask+=msk.mask}else{altMask+=msk}});altMask+=opts.groupmarker[1];return generateMask(altMask,opts.mask,opts)}else opts.mask=opts.mask.pop()}if(opts.mask&&opts.mask.mask!==undefined&&!$.isFunction(opts.mask.mask)){ms=generateMask(opts.mask.mask,opts.mask,opts)}else{ms=generateMask(opts.mask,opts.mask,opts)}return ms}function isInputEventSupported(eventName){var el=document.createElement("input"),evName="on"+eventName,isSupported=evName in el;if(!isSupported){el.setAttribute(evName,"return;");isSupported=typeof el[evName]==="function"}el=null;return isSupported}function maskScope(actionObj,maskset,opts){maskset=maskset||this.maskset;opts=opts||this.opts;var inputmask=this,el=this.el,isRTL=this.isRTL,undoValue,$el,skipKeyPressEvent=false,skipInputEvent=false,ignorable=false,maxLength,mouseEnter=false,colorMask,originalPlaceholder;function getMaskTemplate(baseOnInput,minimalPos,includeMode,noJit,clearOptionalTail){var greedy=opts.greedy;if(clearOptionalTail)opts.greedy=false;minimalPos=minimalPos||0;var maskTemplate=[],ndxIntlzr,pos=0,test,testPos,lvp=getLastValidPosition();do{if(baseOnInput===true&&getMaskSet().validPositions[pos]){testPos=clearOptionalTail&&getMaskSet().validPositions[pos].match.optionality===true&&getMaskSet().validPositions[pos+1]===undefined&&(getMaskSet().validPositions[pos].generatedInput===true||getMaskSet().validPositions[pos].input==opts.skipOptionalPartCharacter&&pos>0)?determineTestTemplate(pos,getTests(pos,ndxIntlzr,pos-1)):getMaskSet().validPositions[pos];test=testPos.match;ndxIntlzr=testPos.locator.slice();maskTemplate.push(includeMode===true?testPos.input:includeMode===false?test.nativeDef:getPlaceholder(pos,test))}else{testPos=getTestTemplate(pos,ndxIntlzr,pos-1);test=testPos.match;ndxIntlzr=testPos.locator.slice();var jitMasking=noJit===true?false:opts.jitMasking!==false?opts.jitMasking:test.jit;if(jitMasking===false||jitMasking===undefined||typeof jitMasking==="number"&&isFinite(jitMasking)&&jitMasking>pos){maskTemplate.push(includeMode===false?test.nativeDef:getPlaceholder(pos,test))}}if(opts.keepStatic==="auto"){if(test.newBlockMarker&&test.fn!==null){opts.keepStatic=pos-1}}pos++}while((maxLength===undefined||pos<maxLength)&&(test.fn!==null||test.def!=="")||minimalPos>pos);if(maskTemplate[maskTemplate.length-1]===""){maskTemplate.pop()}if(includeMode!==false||getMaskSet().maskLength===undefined)getMaskSet().maskLength=pos-1;opts.greedy=greedy;return maskTemplate}function getMaskSet(){return maskset}function resetMaskSet(soft){var maskset=getMaskSet();maskset.buffer=undefined;if(soft!==true){maskset.validPositions={};maskset.p=0}}function getLastValidPosition(closestTo,strict,validPositions){var before=-1,after=-1,valids=validPositions||getMaskSet().validPositions;if(closestTo===undefined)closestTo=-1;for(var posNdx in valids){var psNdx=parseInt(posNdx);if(valids[psNdx]&&(strict||valids[psNdx].generatedInput!==true)){if(psNdx<=closestTo)before=psNdx;if(psNdx>=closestTo)after=psNdx}}return before===-1||before==closestTo?after:after==-1?before:closestTo-before<after-closestTo?before:after}function getDecisionTaker(tst){var decisionTaker=tst.locator[tst.alternation];if(typeof decisionTaker=="string"&&decisionTaker.length>0){decisionTaker=decisionTaker.split(",")[0]}return decisionTaker!==undefined?decisionTaker.toString():""}function getLocator(tst,align){var locator=(tst.alternation!=undefined?tst.mloc[getDecisionTaker(tst)]:tst.locator).join("");if(locator!=="")while(locator.length<align){locator+="0"}return locator}function determineTestTemplate(pos,tests){pos=pos>0?pos-1:0;var altTest=getTest(pos),targetLocator=getLocator(altTest),tstLocator,closest,bestMatch;for(var ndx=0;ndx<tests.length;ndx++){var tst=tests[ndx];tstLocator=getLocator(tst,targetLocator.length);var distance=Math.abs(tstLocator-targetLocator);if(closest===undefined||tstLocator!==""&&distance<closest||bestMatch&&!opts.greedy&&bestMatch.match.optionality&&bestMatch.match.newBlockMarker==="master"&&(!tst.match.optionality||!tst.match.newBlockMarker)||bestMatch&&bestMatch.match.optionalQuantifier&&!tst.match.optionalQuantifier){closest=distance;bestMatch=tst}}return bestMatch}function getTestTemplate(pos,ndxIntlzr,tstPs){return getMaskSet().validPositions[pos]||determineTestTemplate(pos,getTests(pos,ndxIntlzr?ndxIntlzr.slice():ndxIntlzr,tstPs))}function getTest(pos,tests){if(getMaskSet().validPositions[pos]){return getMaskSet().validPositions[pos]}return(tests||getTests(pos))[0]}function positionCanMatchDefinition(pos,def){var valid=false,tests=getTests(pos);for(var tndx=0;tndx<tests.length;tndx++){if(tests[tndx].match&&tests[tndx].match.def===def){valid=true;break}}return valid}function getTests(pos,ndxIntlzr,tstPs){var maskTokens=getMaskSet().maskToken,testPos=ndxIntlzr?tstPs:0,ndxInitializer=ndxIntlzr?ndxIntlzr.slice():[0],matches=[],insertStop=false,latestMatch,cacheDependency=ndxIntlzr?ndxIntlzr.join(""):"";function resolveTestFromToken(maskToken,ndxInitializer,loopNdx,quantifierRecurse){function handleMatch(match,loopNdx,quantifierRecurse){function isFirstMatch(latestMatch,tokenGroup){var firstMatch=$.inArray(latestMatch,tokenGroup.matches)===0;if(!firstMatch){$.each(tokenGroup.matches,function(ndx,match){if(match.isQuantifier===true)firstMatch=isFirstMatch(latestMatch,tokenGroup.matches[ndx-1]);else if(match.hasOwnProperty("matches"))firstMatch=isFirstMatch(latestMatch,match);if(firstMatch)return false})}return firstMatch}function resolveNdxInitializer(pos,alternateNdx,targetAlternation){var bestMatch,indexPos;if(getMaskSet().tests[pos]||getMaskSet().validPositions[pos]){$.each(getMaskSet().tests[pos]||[getMaskSet().validPositions[pos]],function(ndx,lmnt){if(lmnt.mloc[alternateNdx]){bestMatch=lmnt;return false}var alternation=targetAlternation!==undefined?targetAlternation:lmnt.alternation,ndxPos=lmnt.locator[alternation]!==undefined?lmnt.locator[alternation].toString().indexOf(alternateNdx):-1;if((indexPos===undefined||ndxPos<indexPos)&&ndxPos!==-1){bestMatch=lmnt;indexPos=ndxPos}})}if(bestMatch){var bestMatchAltIndex=bestMatch.locator[bestMatch.alternation];var locator=bestMatch.mloc[alternateNdx]||bestMatch.mloc[bestMatchAltIndex]||bestMatch.locator;return locator.slice((targetAlternation!==undefined?targetAlternation:bestMatch.alternation)+1)}else{return targetAlternation!==undefined?resolveNdxInitializer(pos,alternateNdx):undefined}}function isSubsetOf(source,target){function expand(pattern){var expanded=[],start,end;for(var i=0,l=pattern.length;i<l;i++){if(pattern.charAt(i)==="-"){end=pattern.charCodeAt(i+1);while(++start<end){expanded.push(String.fromCharCode(start))}}else{start=pattern.charCodeAt(i);expanded.push(pattern.charAt(i))}}return expanded.join("")}if(opts.regex&&source.match.fn!==null&&target.match.fn!==null){return expand(target.match.def.replace(/[\[\]]/g,"")).indexOf(expand(source.match.def.replace(/[\[\]]/g,"")))!==-1}return source.match.def===target.match.nativeDef}function staticCanMatchDefinition(source,target){var sloc=source.locator.slice(source.alternation).join(""),tloc=target.locator.slice(target.alternation).join(""),canMatch=sloc==tloc;canMatch=canMatch&&source.match.fn===null&&target.match.fn!==null?target.match.fn.test(source.match.def,getMaskSet(),pos,false,opts,false):false;return canMatch}function setMergeLocators(targetMatch,altMatch){if(altMatch===undefined||targetMatch.alternation===altMatch.alternation&&targetMatch.locator[targetMatch.alternation].toString().indexOf(altMatch.locator[altMatch.alternation])===-1){targetMatch.mloc=targetMatch.mloc||{};var locNdx=targetMatch.locator[targetMatch.alternation];if(locNdx===undefined)targetMatch.alternation=undefined;else{if(typeof locNdx==="string")locNdx=locNdx.split(",")[0];if(targetMatch.mloc[locNdx]===undefined)targetMatch.mloc[locNdx]=targetMatch.locator.slice();if(altMatch!==undefined){for(var ndx in altMatch.mloc){if(typeof ndx==="string")ndx=ndx.split(",")[0];if(targetMatch.mloc[ndx]===undefined)targetMatch.mloc[ndx]=altMatch.mloc[ndx]}targetMatch.locator[targetMatch.alternation]=Object.keys(targetMatch.mloc).join(",")}return true}}return false}if(testPos>500&&quantifierRecurse!==undefined){throw"Inputmask: There is probably an error in your mask definition or in the code. Create an issue on github with an example of the mask you are using. "+getMaskSet().mask}if(testPos===pos&&match.matches===undefined){matches.push({match:match,locator:loopNdx.reverse(),cd:cacheDependency,mloc:{}});return true}else if(match.matches!==undefined){if(match.isGroup&&quantifierRecurse!==match){match=handleMatch(maskToken.matches[$.inArray(match,maskToken.matches)+1],loopNdx,quantifierRecurse);if(match)return true}else if(match.isOptional){var optionalToken=match;match=resolveTestFromToken(match,ndxInitializer,loopNdx,quantifierRecurse);if(match){$.each(matches,function(ndx,mtch){mtch.match.optionality=true});latestMatch=matches[matches.length-1].match;if(quantifierRecurse===undefined&&isFirstMatch(latestMatch,optionalToken)){insertStop=true;testPos=pos}else return true}}else if(match.isAlternator){var alternateToken=match,malternateMatches=[],maltMatches,currentMatches=matches.slice(),loopNdxCnt=loopNdx.length;var altIndex=ndxInitializer.length>0?ndxInitializer.shift():-1;if(altIndex===-1||typeof altIndex==="string"){var currentPos=testPos,ndxInitializerClone=ndxInitializer.slice(),altIndexArr=[],amndx;if(typeof altIndex=="string"){altIndexArr=altIndex.split(",")}else{for(amndx=0;amndx<alternateToken.matches.length;amndx++){altIndexArr.push(amndx.toString())}}if(getMaskSet().excludes[pos]){var altIndexArrClone=altIndexArr.slice();for(var i=0,el=getMaskSet().excludes[pos].length;i<el;i++){altIndexArr.splice(altIndexArr.indexOf(getMaskSet().excludes[pos][i].toString()),1)}if(altIndexArr.length===0){getMaskSet().excludes[pos]=undefined;altIndexArr=altIndexArrClone}}if(opts.keepStatic===true||isFinite(parseInt(opts.keepStatic))&&currentPos>=opts.keepStatic)altIndexArr=altIndexArr.slice(0,1);var unMatchedAlternation=false;for(var ndx=0;ndx<altIndexArr.length;ndx++){amndx=parseInt(altIndexArr[ndx]);matches=[];ndxInitializer=typeof altIndex==="string"?resolveNdxInitializer(testPos,amndx,loopNdxCnt)||ndxInitializerClone.slice():ndxInitializerClone.slice();if(alternateToken.matches[amndx]&&handleMatch(alternateToken.matches[amndx],[amndx].concat(loopNdx),quantifierRecurse))match=true;else if(ndx===0){unMatchedAlternation=true}maltMatches=matches.slice();testPos=currentPos;matches=[];for(var ndx1=0;ndx1<maltMatches.length;ndx1++){var altMatch=maltMatches[ndx1],dropMatch=false;altMatch.match.jit=altMatch.match.jit||unMatchedAlternation;altMatch.alternation=altMatch.alternation||loopNdxCnt;setMergeLocators(altMatch);for(var ndx2=0;ndx2<malternateMatches.length;ndx2++){var altMatch2=malternateMatches[ndx2];if(typeof altIndex!=="string"||altMatch.alternation!==undefined&&$.inArray(altMatch.locator[altMatch.alternation].toString(),altIndexArr)!==-1){if(altMatch.match.nativeDef===altMatch2.match.nativeDef){dropMatch=true;setMergeLocators(altMatch2,altMatch);break}else if(isSubsetOf(altMatch,altMatch2)){if(setMergeLocators(altMatch,altMatch2)){dropMatch=true;malternateMatches.splice(malternateMatches.indexOf(altMatch2),0,altMatch)}break}else if(isSubsetOf(altMatch2,altMatch)){setMergeLocators(altMatch2,altMatch);break}else if(staticCanMatchDefinition(altMatch,altMatch2)){if(setMergeLocators(altMatch,altMatch2)){dropMatch=true;malternateMatches.splice(malternateMatches.indexOf(altMatch2),0,altMatch)}break}}}if(!dropMatch){malternateMatches.push(altMatch)}}}matches=currentMatches.concat(malternateMatches);testPos=pos;insertStop=matches.length>0;match=malternateMatches.length>0;ndxInitializer=ndxInitializerClone.slice()}else match=handleMatch(alternateToken.matches[altIndex]||maskToken.matches[altIndex],[altIndex].concat(loopNdx),quantifierRecurse);if(match)return true}else if(match.isQuantifier&&quantifierRecurse!==maskToken.matches[$.inArray(match,maskToken.matches)-1]){var qt=match;for(var qndx=ndxInitializer.length>0?ndxInitializer.shift():0;qndx<(isNaN(qt.quantifier.max)?qndx+1:qt.quantifier.max)&&testPos<=pos;qndx++){var tokenGroup=maskToken.matches[$.inArray(qt,maskToken.matches)-1];match=handleMatch(tokenGroup,[qndx].concat(loopNdx),tokenGroup);if(match){latestMatch=matches[matches.length-1].match;latestMatch.optionalQuantifier=qndx>=qt.quantifier.min;latestMatch.jit=(qndx||1)*tokenGroup.matches.indexOf(latestMatch)>=qt.quantifier.jit;if(latestMatch.optionalQuantifier&&isFirstMatch(latestMatch,tokenGroup)){insertStop=true;testPos=pos;break}if(latestMatch.jit){getMaskSet().jitOffset[pos]=tokenGroup.matches.indexOf(latestMatch)}return true}}}else{match=resolveTestFromToken(match,ndxInitializer,loopNdx,quantifierRecurse);if(match)return true}}else{testPos++}}for(var tndx=ndxInitializer.length>0?ndxInitializer.shift():0;tndx<maskToken.matches.length;tndx++){if(maskToken.matches[tndx].isQuantifier!==true){var match=handleMatch(maskToken.matches[tndx],[tndx].concat(loopNdx),quantifierRecurse);if(match&&testPos===pos){return match}else if(testPos>pos){break}}}}function mergeLocators(pos,tests){var locator=[];if(!$.isArray(tests))tests=[tests];if(tests.length>0){if(tests[0].alternation===undefined){locator=determineTestTemplate(pos,tests.slice()).locator.slice();if(locator.length===0)locator=tests[0].locator.slice()}else{$.each(tests,function(ndx,tst){if(tst.def!==""){if(locator.length===0)locator=tst.locator.slice();else{for(var i=0;i<locator.length;i++){if(tst.locator[i]&&locator[i].toString().indexOf(tst.locator[i])===-1){locator[i]+=","+tst.locator[i]}}}}})}}return locator}if(pos>-1){if(ndxIntlzr===undefined){var previousPos=pos-1,test;while((test=getMaskSet().validPositions[previousPos]||getMaskSet().tests[previousPos])===undefined&&previousPos>-1){previousPos--}if(test!==undefined&&previousPos>-1){ndxInitializer=mergeLocators(previousPos,test);cacheDependency=ndxInitializer.join("");testPos=previousPos}}if(getMaskSet().tests[pos]&&getMaskSet().tests[pos][0].cd===cacheDependency){return getMaskSet().tests[pos]}for(var mtndx=ndxInitializer.shift();mtndx<maskTokens.length;mtndx++){var match=resolveTestFromToken(maskTokens[mtndx],ndxInitializer,[mtndx]);if(match&&testPos===pos||testPos>pos){break}}}if(matches.length===0||insertStop){matches.push({match:{fn:null,optionality:false,casing:null,def:"",placeholder:""},locator:[],mloc:{},cd:cacheDependency})}if(ndxIntlzr!==undefined&&getMaskSet().tests[pos]){return $.extend(true,[],matches)}getMaskSet().tests[pos]=$.extend(true,[],matches);return getMaskSet().tests[pos]}function getBufferTemplate(){if(getMaskSet()._buffer===undefined){getMaskSet()._buffer=getMaskTemplate(false,1);if(getMaskSet().buffer===undefined)getMaskSet().buffer=getMaskSet()._buffer.slice()}return getMaskSet()._buffer}function getBuffer(noCache){if(getMaskSet().buffer===undefined||noCache===true){getMaskSet().buffer=getMaskTemplate(true,getLastValidPosition(),true);if(getMaskSet()._buffer===undefined)getMaskSet()._buffer=getMaskSet().buffer.slice()}return getMaskSet().buffer}function refreshFromBuffer(start,end,buffer){var i,p;if(start===true){resetMaskSet();start=0;end=buffer.length}else{for(i=start;i<end;i++){delete getMaskSet().validPositions[i]}}p=start;for(i=start;i<end;i++){resetMaskSet(true);if(buffer[i]!==opts.skipOptionalPartCharacter){var valResult=isValid(p,buffer[i],true,true);if(valResult!==false){resetMaskSet(true);p=valResult.caret!==undefined?valResult.caret:valResult.pos+1}}}}function casing(elem,test,pos){switch(opts.casing||test.casing){case"upper":elem=elem.toUpperCase();break;case"lower":elem=elem.toLowerCase();break;case"title":var posBefore=getMaskSet().validPositions[pos-1];if(pos===0||posBefore&&posBefore.input===String.fromCharCode(Inputmask.keyCode.SPACE)){elem=elem.toUpperCase()}else{elem=elem.toLowerCase()}break;default:if($.isFunction(opts.casing)){var args=Array.prototype.slice.call(arguments);args.push(getMaskSet().validPositions);elem=opts.casing.apply(this,args)}}return elem}function checkAlternationMatch(altArr1,altArr2,na){var altArrC=opts.greedy?altArr2:altArr2.slice(0,1),isMatch=false,naArr=na!==undefined?na.split(","):[],naNdx;for(var i=0;i<naArr.length;i++){if((naNdx=altArr1.indexOf(naArr[i]))!==-1){altArr1.splice(naNdx,1)}}for(var alndx=0;alndx<altArr1.length;alndx++){if($.inArray(altArr1[alndx],altArrC)!==-1){isMatch=true;break}}return isMatch}function alternate(pos,c,strict,fromSetValid,rAltPos){var validPsClone=$.extend(true,{},getMaskSet().validPositions),lastAlt,alternation,isValidRslt=false,altPos,prevAltPos,i,validPos,decisionPos,lAltPos=rAltPos!==undefined?rAltPos:getLastValidPosition();if(lAltPos===-1&&rAltPos===undefined){lastAlt=0;prevAltPos=getTest(lastAlt);alternation=prevAltPos.alternation}else{for(;lAltPos>=0;lAltPos--){altPos=getMaskSet().validPositions[lAltPos];if(altPos&&altPos.alternation!==undefined){if(prevAltPos&&prevAltPos.locator[altPos.alternation]!==altPos.locator[altPos.alternation]){break}lastAlt=lAltPos;alternation=getMaskSet().validPositions[lastAlt].alternation;prevAltPos=altPos}}}if(alternation!==undefined){decisionPos=parseInt(lastAlt);getMaskSet().excludes[decisionPos]=getMaskSet().excludes[decisionPos]||[];if(pos!==true){getMaskSet().excludes[decisionPos].push(getDecisionTaker(prevAltPos))}var validInputsClone=[],staticInputsBeforePos=0;for(i=decisionPos;i<getLastValidPosition(undefined,true)+1;i++){validPos=getMaskSet().validPositions[i];if(validPos&&validPos.generatedInput!==true){validInputsClone.push(validPos.input)}else if(i<pos)staticInputsBeforePos++;delete getMaskSet().validPositions[i]}while(getMaskSet().excludes[decisionPos]&&getMaskSet().excludes[decisionPos].length<10){var posOffset=staticInputsBeforePos*-1,validInputs=validInputsClone.slice();getMaskSet().tests[decisionPos]=undefined;resetMaskSet(true);isValidRslt=true;while(validInputs.length>0){var input=validInputs.shift();if(!(isValidRslt=isValid(getLastValidPosition(undefined,true)+1,input,false,fromSetValid,true))){break}}if(isValidRslt&&c!==undefined){var targetLvp=getLastValidPosition(pos)+1;for(i=decisionPos;i<getLastValidPosition()+1;i++){validPos=getMaskSet().validPositions[i];if((validPos===undefined||validPos.match.fn==null)&&i<pos+posOffset){posOffset++}}pos=pos+posOffset;isValidRslt=isValid(pos>targetLvp?targetLvp:pos,c,strict,fromSetValid,true)}if(!isValidRslt){resetMaskSet();prevAltPos=getTest(decisionPos);getMaskSet().validPositions=$.extend(true,{},validPsClone);if(getMaskSet().excludes[decisionPos]){var decisionTaker=getDecisionTaker(prevAltPos);if(getMaskSet().excludes[decisionPos].indexOf(decisionTaker)!==-1){isValidRslt=alternate(pos,c,strict,fromSetValid,decisionPos-1);break}getMaskSet().excludes[decisionPos].push(decisionTaker);for(i=decisionPos;i<getLastValidPosition(undefined,true)+1;i++){delete getMaskSet().validPositions[i]}}else{isValidRslt=alternate(pos,c,strict,fromSetValid,decisionPos-1);break}}else break}}getMaskSet().excludes[decisionPos]=undefined;return isValidRslt}function isValid(pos,c,strict,fromSetValid,fromAlternate,validateOnly){function isSelection(posObj){return isRTL?posObj.begin-posObj.end>1||posObj.begin-posObj.end===1:posObj.end-posObj.begin>1||posObj.end-posObj.begin===1}strict=strict===true;var maskPos=pos;if(pos.begin!==undefined){maskPos=isRTL?pos.end:pos.begin}function _isValid(position,c,strict){var rslt=false;$.each(getTests(position),function(ndx,tst){var test=tst.match;getBuffer(true);rslt=test.fn!=null?test.fn.test(c,getMaskSet(),position,strict,opts,isSelection(pos)):(c===test.def||c===opts.skipOptionalPartCharacter)&&test.def!==""?{c:getPlaceholder(position,test,true)||test.def,pos:position}:false;if(rslt!==false){var elem=rslt.c!==undefined?rslt.c:c,validatedPos=position;elem=elem===opts.skipOptionalPartCharacter&&test.fn===null?getPlaceholder(position,test,true)||test.def:elem;if(rslt.remove!==undefined){if(!$.isArray(rslt.remove))rslt.remove=[rslt.remove];$.each(rslt.remove.sort(function(a,b){return b-a}),function(ndx,lmnt){revalidateMask({begin:lmnt,end:lmnt+1})})}if(rslt.insert!==undefined){if(!$.isArray(rslt.insert))rslt.insert=[rslt.insert];$.each(rslt.insert.sort(function(a,b){return a-b}),function(ndx,lmnt){isValid(lmnt.pos,lmnt.c,true,fromSetValid)})}if(rslt!==true&&rslt.pos!==undefined&&rslt.pos!==position){validatedPos=rslt.pos}if(rslt!==true&&rslt.pos===undefined&&rslt.c===undefined){return false}if(!revalidateMask(pos,$.extend({},tst,{input:casing(elem,test,validatedPos)}),fromSetValid,validatedPos)){rslt=false}return false}});return rslt}var result=true,positionsClone=$.extend(true,{},getMaskSet().validPositions);if($.isFunction(opts.preValidation)&&!strict&&fromSetValid!==true&&validateOnly!==true){result=opts.preValidation(getBuffer(),maskPos,c,isSelection(pos),opts,getMaskSet())}if(result===true){trackbackPositions(undefined,maskPos,true);if(maxLength===undefined||maskPos<maxLength){result=_isValid(maskPos,c,strict);if((!strict||fromSetValid===true)&&result===false&&validateOnly!==true){var currentPosValid=getMaskSet().validPositions[maskPos];if(currentPosValid&&currentPosValid.match.fn===null&&(currentPosValid.match.def===c||c===opts.skipOptionalPartCharacter)){result={caret:seekNext(maskPos)}}else{if((opts.insertMode||getMaskSet().validPositions[seekNext(maskPos)]===undefined)&&(!isMask(maskPos,true)||getMaskSet().jitOffset[maskPos])){if(getMaskSet().jitOffset[maskPos]&&getMaskSet().validPositions[seekNext(maskPos)]===undefined){result=isValid(maskPos+getMaskSet().jitOffset[maskPos],c,strict);if(result!==false)result.caret=maskPos}else for(var nPos=maskPos+1,snPos=seekNext(maskPos);nPos<=snPos;nPos++){result=_isValid(nPos,c,strict);if(result!==false){result=trackbackPositions(maskPos,result.pos!==undefined?result.pos:nPos)||result;maskPos=nPos;break}}}}}}if(result===false&&opts.keepStatic!==false&&(opts.regex==null||isComplete(getBuffer()))&&!strict&&fromAlternate!==true){result=alternate(maskPos,c,strict,fromSetValid)}if(result===true){result={pos:maskPos}}}if($.isFunction(opts.postValidation)&&result!==false&&!strict&&fromSetValid!==true&&validateOnly!==true){var postResult=opts.postValidation(getBuffer(true),pos.begin!==undefined?isRTL?pos.end:pos.begin:pos,result,opts);if(postResult!==undefined){if(postResult.refreshFromBuffer&&postResult.buffer){var refresh=postResult.refreshFromBuffer;refreshFromBuffer(refresh===true?refresh:refresh.start,refresh.end,postResult.buffer)}result=postResult===true?result:postResult}}if(result&&result.pos===undefined){result.pos=maskPos}if(result===false||validateOnly===true){resetMaskSet(true);getMaskSet().validPositions=$.extend(true,{},positionsClone)}return result}function trackbackPositions(originalPos,newPos,fillOnly){var result;if(originalPos===undefined){for(originalPos=newPos-1;originalPos>0;originalPos--){if(getMaskSet().validPositions[originalPos])break}}for(var ps=originalPos;ps<newPos;ps++){if(getMaskSet().validPositions[ps]===undefined&&!isMask(ps,true)){var vp=ps==0?getTest(ps):getMaskSet().validPositions[ps-1];if(vp){var tests=getTests(ps).slice();if(tests[tests.length-1].match.def==="")tests.pop();var bestMatch=determineTestTemplate(ps,tests);bestMatch=$.extend({},bestMatch,{input:getPlaceholder(ps,bestMatch.match,true)||bestMatch.match.def});bestMatch.generatedInput=true;revalidateMask(ps,bestMatch,true);if(fillOnly!==true){var cvpInput=getMaskSet().validPositions[newPos].input;getMaskSet().validPositions[newPos]=undefined;result=isValid(newPos,cvpInput,true,true)}}}}return result}function revalidateMask(pos,validTest,fromSetValid,validatedPos){function IsEnclosedStatic(pos,valids,selection){var posMatch=valids[pos];if(posMatch!==undefined&&(posMatch.match.fn===null&&posMatch.match.optionality!==true||posMatch.input===opts.radixPoint)){var prevMatch=selection.begin<=pos-1?valids[pos-1]&&valids[pos-1].match.fn===null&&valids[pos-1]:valids[pos-1],nextMatch=selection.end>pos+1?valids[pos+1]&&valids[pos+1].match.fn===null&&valids[pos+1]:valids[pos+1];return prevMatch&&nextMatch}return false}var begin=pos.begin!==undefined?pos.begin:pos,end=pos.end!==undefined?pos.end:pos;if(pos.begin>pos.end){begin=pos.end;end=pos.begin}validatedPos=validatedPos!==undefined?validatedPos:begin;if(begin!==end||opts.insertMode&&getMaskSet().validPositions[validatedPos]!==undefined&&fromSetValid===undefined){var positionsClone=$.extend(true,{},getMaskSet().validPositions),lvp=getLastValidPosition(undefined,true),i;getMaskSet().p=begin;for(i=lvp;i>=begin;i--){if(getMaskSet().validPositions[i]&&getMaskSet().validPositions[i].match.nativeDef==="+"){opts.isNegative=false}delete getMaskSet().validPositions[i]}var valid=true,j=validatedPos,vps=getMaskSet().validPositions,needsValidation=false,posMatch=j,i=j;if(validTest){getMaskSet().validPositions[validatedPos]=$.extend(true,{},validTest);posMatch++;j++;if(begin<end)i++}for(;i<=lvp;i++){var t=positionsClone[i];if(t!==undefined&&(i>=end||i>=begin&&t.generatedInput!==true&&IsEnclosedStatic(i,positionsClone,{begin:begin,end:end}))){while(getTest(posMatch).match.def!==""){if(needsValidation===false&&positionsClone[posMatch]&&positionsClone[posMatch].match.nativeDef===t.match.nativeDef){getMaskSet().validPositions[posMatch]=$.extend(true,{},positionsClone[posMatch]);getMaskSet().validPositions[posMatch].input=t.input;trackbackPositions(undefined,posMatch,true);j=posMatch+1;valid=true}else if(opts.shiftPositions&&positionCanMatchDefinition(posMatch,t.match.def)){var result=isValid(posMatch,t.input,true,true);valid=result!==false;j=result.caret||result.insert?getLastValidPosition():posMatch+1;needsValidation=true}else{valid=t.generatedInput===true||t.input===opts.radixPoint&&opts.numericInput===true}if(valid)break;if(!valid&&posMatch>end&&isMask(posMatch,true)&&(t.match.fn!==null||posMatch>getMaskSet().maskLength)){break}posMatch++}if(getTest(posMatch).match.def=="")valid=false;posMatch=j}if(!valid)break}if(!valid){getMaskSet().validPositions=$.extend(true,{},positionsClone);resetMaskSet(true);return false}}else if(validTest){getMaskSet().validPositions[validatedPos]=$.extend(true,{},validTest)}resetMaskSet(true);return true}function isMask(pos,strict){var test=getTestTemplate(pos).match;if(test.def==="")test=getTest(pos).match;if(test.fn!=null){return test.fn}if(strict!==true&&pos>-1){var tests=getTests(pos);return tests.length>1+(tests[tests.length-1].match.def===""?1:0)}return false}function seekNext(pos,newBlock){var position=pos+1;while(getTest(position).match.def!==""&&(newBlock===true&&(getTest(position).match.newBlockMarker!==true||!isMask(position))||newBlock!==true&&!isMask(position))){position++}return position}function seekPrevious(pos,newBlock){var position=pos,tests;if(position<=0)return 0;while(--position>0&&(newBlock===true&&getTest(position).match.newBlockMarker!==true||newBlock!==true&&!isMask(position)&&(tests=getTests(position),tests.length<2||tests.length===2&&tests[1].match.def===""))){}return position}function writeBuffer(input,buffer,caretPos,event,triggerEvents){if(event&&$.isFunction(opts.onBeforeWrite)){var result=opts.onBeforeWrite.call(inputmask,event,buffer,caretPos,opts);if(result){if(result.refreshFromBuffer){var refresh=result.refreshFromBuffer;refreshFromBuffer(refresh===true?refresh:refresh.start,refresh.end,result.buffer||buffer);buffer=getBuffer(true)}if(caretPos!==undefined)caretPos=result.caret!==undefined?result.caret:caretPos}}if(input!==undefined){input.inputmask._valueSet(buffer.join(""));if(caretPos!==undefined&&(event===undefined||event.type!=="blur")){caret(input,caretPos)}else renderColorMask(input,caretPos,buffer.length===0);if(triggerEvents===true){var $input=$(input),nptVal=input.inputmask._valueGet();skipInputEvent=true;$input.trigger("input");setTimeout(function(){if(nptVal===getBufferTemplate().join("")){$input.trigger("cleared")}else if(isComplete(buffer)===true){$input.trigger("complete")}},0)}}}function getPlaceholder(pos,test,returnPL){test=test||getTest(pos).match;if(test.placeholder!==undefined||returnPL===true){return $.isFunction(test.placeholder)?test.placeholder(opts):test.placeholder}else if(test.fn===null){if(pos>-1&&getMaskSet().validPositions[pos]===undefined){var tests=getTests(pos),staticAlternations=[],prevTest;if(tests.length>1+(tests[tests.length-1].match.def===""?1:0)){for(var i=0;i<tests.length;i++){if(tests[i].match.optionality!==true&&tests[i].match.optionalQuantifier!==true&&(tests[i].match.fn===null||prevTest===undefined||tests[i].match.fn.test(prevTest.match.def,getMaskSet(),pos,true,opts)!==false)){staticAlternations.push(tests[i]);if(tests[i].match.fn===null)prevTest=tests[i];if(staticAlternations.length>1){if(/[0-9a-bA-Z]/.test(staticAlternations[0].match.def)){return opts.placeholder.charAt(pos%opts.placeholder.length)}}}}}}return test.def}return opts.placeholder.charAt(pos%opts.placeholder.length)}function HandleNativePlaceholder(npt,value){if(ie){if(npt.inputmask._valueGet()!==value){var buffer=getBuffer().slice(),nptValue=npt.inputmask._valueGet();if(nptValue!==value){var lvp=getLastValidPosition();if(lvp===-1&&nptValue===getBufferTemplate().join("")){buffer=[]}else if(lvp!==-1){clearOptionalTail(buffer)}writeBuffer(npt,buffer)}}}else if(npt.placeholder!==value){npt.placeholder=value;if(npt.placeholder==="")npt.removeAttribute("placeholder")}}var EventRuler={on:function on(input,eventName,eventHandler){var ev=function ev(e){var that=this;if(that.inputmask===undefined&&this.nodeName!=="FORM"){var imOpts=$.data(that,"_inputmask_opts");if(imOpts)new Inputmask(imOpts).mask(that);else EventRuler.off(that)}else if(e.type!=="setvalue"&&this.nodeName!=="FORM"&&(that.disabled||that.readOnly&&!(e.type==="keydown"&&e.ctrlKey&&e.keyCode===67||opts.tabThrough===false&&e.keyCode===Inputmask.keyCode.TAB))){e.preventDefault()}else{switch(e.type){case"input":if(skipInputEvent===true){skipInputEvent=false;return e.preventDefault()}if(mobile){var args=arguments;setTimeout(function(){eventHandler.apply(that,args);caret(that,that.inputmask.caretPos,undefined,true)},0);return false}break;case"keydown":skipKeyPressEvent=false;skipInputEvent=false;break;case"keypress":if(skipKeyPressEvent===true){return e.preventDefault()}skipKeyPressEvent=true;break;case"click":if(iemobile||iphone){var args=arguments;setTimeout(function(){eventHandler.apply(that,args)},0);return false}break}var returnVal=eventHandler.apply(that,arguments);if(returnVal===false){e.preventDefault();e.stopPropagation()}return returnVal}};input.inputmask.events[eventName]=input.inputmask.events[eventName]||[];input.inputmask.events[eventName].push(ev);if($.inArray(eventName,["submit","reset"])!==-1){if(input.form!==null)$(input.form).on(eventName,ev)}else{$(input).on(eventName,ev)}},off:function off(input,event){if(input.inputmask&&input.inputmask.events){var events;if(event){events=[];events[event]=input.inputmask.events[event]}else{events=input.inputmask.events}$.each(events,function(eventName,evArr){while(evArr.length>0){var ev=evArr.pop();if($.inArray(eventName,["submit","reset"])!==-1){if(input.form!==null)$(input.form).off(eventName,ev)}else{$(input).off(eventName,ev)}}delete input.inputmask.events[eventName]})}}};var EventHandlers={keydownEvent:function keydownEvent(e){var input=this,$input=$(input),k=e.keyCode,pos=caret(input);if(k===Inputmask.keyCode.BACKSPACE||k===Inputmask.keyCode.DELETE||iphone&&k===Inputmask.keyCode.BACKSPACE_SAFARI||e.ctrlKey&&k===Inputmask.keyCode.X&&!isInputEventSupported("cut")){e.preventDefault();handleRemove(input,k,pos);writeBuffer(input,getBuffer(true),getMaskSet().p,e,input.inputmask._valueGet()!==getBuffer().join(""))}else if(k===Inputmask.keyCode.END||k===Inputmask.keyCode.PAGE_DOWN){e.preventDefault();var caretPos=seekNext(getLastValidPosition());caret(input,e.shiftKey?pos.begin:caretPos,caretPos,true)}else if(k===Inputmask.keyCode.HOME&&!e.shiftKey||k===Inputmask.keyCode.PAGE_UP){e.preventDefault();caret(input,0,e.shiftKey?pos.begin:0,true)}else if((opts.undoOnEscape&&k===Inputmask.keyCode.ESCAPE||k===90&&e.ctrlKey)&&e.altKey!==true){checkVal(input,true,false,undoValue.split(""));$input.trigger("click")}else if(k===Inputmask.keyCode.INSERT&&!(e.shiftKey||e.ctrlKey)){opts.insertMode=!opts.insertMode;input.setAttribute("im-insert",opts.insertMode)}else if(opts.tabThrough===true&&k===Inputmask.keyCode.TAB){if(e.shiftKey===true){if(getTest(pos.begin).match.fn===null){pos.begin=seekNext(pos.begin)}pos.end=seekPrevious(pos.begin,true);pos.begin=seekPrevious(pos.end,true)}else{pos.begin=seekNext(pos.begin,true);pos.end=seekNext(pos.begin,true);if(pos.end<getMaskSet().maskLength)pos.end--}if(pos.begin<getMaskSet().maskLength){e.preventDefault();caret(input,pos.begin,pos.end)}}opts.onKeyDown.call(this,e,getBuffer(),caret(input).begin,opts);ignorable=$.inArray(k,opts.ignorables)!==-1},keypressEvent:function keypressEvent(e,checkval,writeOut,strict,ndx){var input=this,$input=$(input),k=e.which||e.charCode||e.keyCode;if(checkval!==true&&!(e.ctrlKey&&e.altKey)&&(e.ctrlKey||e.metaKey||ignorable)){if(k===Inputmask.keyCode.ENTER&&undoValue!==getBuffer().join("")){undoValue=getBuffer().join("");setTimeout(function(){$input.trigger("change")},0)}return true}else{if(k){if(k===46&&e.shiftKey===false&&opts.radixPoint!=="")k=opts.radixPoint.charCodeAt(0);var pos=checkval?{begin:ndx,end:ndx}:caret(input),forwardPosition,c=String.fromCharCode(k),offset=0;if(opts._radixDance&&opts.numericInput){var caretPos=getBuffer().indexOf(opts.radixPoint.charAt(0))+1;if(pos.begin<=caretPos){if(k===opts.radixPoint.charCodeAt(0))offset=1;pos.begin-=1;pos.end-=1}}getMaskSet().writeOutBuffer=true;var valResult=isValid(pos,c,strict);if(valResult!==false){resetMaskSet(true);forwardPosition=valResult.caret!==undefined?valResult.caret:seekNext(valResult.pos.begin?valResult.pos.begin:valResult.pos);getMaskSet().p=forwardPosition}forwardPosition=(opts.numericInput&&valResult.caret===undefined?seekPrevious(forwardPosition):forwardPosition)+offset;if(writeOut!==false){setTimeout(function(){opts.onKeyValidation.call(input,k,valResult,opts)},0);if(getMaskSet().writeOutBuffer&&valResult!==false){var buffer=getBuffer();writeBuffer(input,buffer,forwardPosition,e,checkval!==true)}}e.preventDefault();if(checkval){if(valResult!==false)valResult.forwardPosition=forwardPosition;return valResult}}}},pasteEvent:function pasteEvent(e){var input=this,ev=e.originalEvent||e,$input=$(input),inputValue=input.inputmask._valueGet(true),caretPos=caret(input),tempValue;if(isRTL){tempValue=caretPos.end;caretPos.end=caretPos.begin;caretPos.begin=tempValue}var valueBeforeCaret=inputValue.substr(0,caretPos.begin),valueAfterCaret=inputValue.substr(caretPos.end,inputValue.length);if(valueBeforeCaret===(isRTL?getBufferTemplate().reverse():getBufferTemplate()).slice(0,caretPos.begin).join(""))valueBeforeCaret="";if(valueAfterCaret===(isRTL?getBufferTemplate().reverse():getBufferTemplate()).slice(caretPos.end).join(""))valueAfterCaret="";if(window.clipboardData&&window.clipboardData.getData){inputValue=valueBeforeCaret+window.clipboardData.getData("Text")+valueAfterCaret}else if(ev.clipboardData&&ev.clipboardData.getData){inputValue=valueBeforeCaret+ev.clipboardData.getData("text/plain")+valueAfterCaret}else return true;var pasteValue=inputValue;if($.isFunction(opts.onBeforePaste)){pasteValue=opts.onBeforePaste.call(inputmask,inputValue,opts);if(pasteValue===false){return e.preventDefault()}if(!pasteValue){pasteValue=inputValue}}checkVal(input,false,false,pasteValue.toString().split(""));writeBuffer(input,getBuffer(),seekNext(getLastValidPosition()),e,undoValue!==getBuffer().join(""));return e.preventDefault()},inputFallBackEvent:function inputFallBackEvent(e){function radixPointHandler(input,inputValue,caretPos){if(inputValue.charAt(caretPos.begin-1)==="."&&opts.radixPoint!==""){inputValue=inputValue.split("");inputValue[caretPos.begin-1]=opts.radixPoint.charAt(0);inputValue=inputValue.join("")}return inputValue}function ieMobileHandler(input,inputValue,caretPos){if(iemobile){var inputChar=inputValue.replace(getBuffer().join(""),"");if(inputChar.length===1){var iv=inputValue.split("");iv.splice(caretPos.begin,0,inputChar);inputValue=iv.join("")}}return inputValue}var input=this,inputValue=input.inputmask._valueGet();if(getBuffer().join("")!==inputValue){var caretPos=caret(input);inputValue=radixPointHandler(input,inputValue,caretPos);inputValue=ieMobileHandler(input,inputValue,caretPos);if(getBuffer().join("")!==inputValue){var buffer=getBuffer().join(""),offset=!opts.numericInput&&inputValue.length>buffer.length?-1:0,frontPart=inputValue.substr(0,caretPos.begin),backPart=inputValue.substr(caretPos.begin),frontBufferPart=buffer.substr(0,caretPos.begin+offset),backBufferPart=buffer.substr(caretPos.begin+offset);var selection=caretPos,entries="",isEntry=false;if(frontPart!==frontBufferPart){var fpl=(isEntry=frontPart.length>=frontBufferPart.length)?frontPart.length:frontBufferPart.length,i;for(i=0;frontPart.charAt(i)===frontBufferPart.charAt(i)&&i<fpl;i++){}if(isEntry){selection.begin=i-offset;entries+=frontPart.slice(i,selection.end)}}if(backPart!==backBufferPart){if(backPart.length>backBufferPart.length){entries+=backPart.slice(0,1)}else{if(backPart.length<backBufferPart.length){selection.end+=backBufferPart.length-backPart.length;if(!isEntry&&opts.radixPoint!==""&&backPart===""&&frontPart.charAt(selection.begin+offset-1)===opts.radixPoint){selection.begin--;entries=opts.radixPoint}}}}writeBuffer(input,getBuffer(),{begin:selection.begin+offset,end:selection.end+offset});if(entries.length>0){$.each(entries.split(""),function(ndx,entry){var keypress=new $.Event("keypress");keypress.which=entry.charCodeAt(0);ignorable=false;EventHandlers.keypressEvent.call(input,keypress)})}else{if(selection.begin===selection.end-1){selection.begin=seekPrevious(selection.begin+1);if(selection.begin===selection.end-1){caret(input,selection.begin)}else{caret(input,selection.begin,selection.end)}}var keydown=new $.Event("keydown");keydown.keyCode=opts.numericInput?Inputmask.keyCode.BACKSPACE:Inputmask.keyCode.DELETE;EventHandlers.keydownEvent.call(input,keydown)}e.preventDefault()}}},beforeInputEvent:function beforeInputEvent(e){if(e.cancelable){var input=this;switch(e.inputType){case"insertText":$.each(e.data.split(""),function(ndx,entry){var keypress=new $.Event("keypress");keypress.which=entry.charCodeAt(0);ignorable=false;EventHandlers.keypressEvent.call(input,keypress)});return e.preventDefault();case"deleteContentBackward":var keydown=new $.Event("keydown");keydown.keyCode=Inputmask.keyCode.BACKSPACE;EventHandlers.keydownEvent.call(input,keydown);return e.preventDefault();case"deleteContentForward":var keydown=new $.Event("keydown");keydown.keyCode=Inputmask.keyCode.DELETE;EventHandlers.keydownEvent.call(input,keydown);return e.preventDefault()}}},setValueEvent:function setValueEvent(e){this.inputmask.refreshValue=false;var input=this,value=e&&e.detail?e.detail[0]:arguments[1],value=value||input.inputmask._valueGet(true);if($.isFunction(opts.onBeforeMask))value=opts.onBeforeMask.call(inputmask,value,opts)||value;value=value.split("");checkVal(input,true,false,value);undoValue=getBuffer().join("");if((opts.clearMaskOnLostFocus||opts.clearIncomplete)&&input.inputmask._valueGet()===getBufferTemplate().join("")){input.inputmask._valueSet("")}},focusEvent:function focusEvent(e){var input=this,nptValue=input.inputmask._valueGet();if(opts.showMaskOnFocus){if(nptValue!==getBuffer().join("")){writeBuffer(input,getBuffer(),seekNext(getLastValidPosition()))}else if(mouseEnter===false){caret(input,seekNext(getLastValidPosition()))}}if(opts.positionCaretOnTab===true&&mouseEnter===false){EventHandlers.clickEvent.apply(input,[e,true])}undoValue=getBuffer().join("")},mouseleaveEvent:function mouseleaveEvent(e){var input=this;mouseEnter=false;if(opts.clearMaskOnLostFocus&&document.activeElement!==input){HandleNativePlaceholder(input,originalPlaceholder)}},clickEvent:function clickEvent(e,tabbed){function doRadixFocus(clickPos){if(opts.radixPoint!==""){var vps=getMaskSet().validPositions;if(vps[clickPos]===undefined||vps[clickPos].input===getPlaceholder(clickPos)){if(clickPos<seekNext(-1))return true;var radixPos=$.inArray(opts.radixPoint,getBuffer());if(radixPos!==-1){for(var vp in vps){if(radixPos<vp&&vps[vp].input!==getPlaceholder(vp)){return false}}return true}}}return false}var input=this;setTimeout(function(){if(document.activeElement===input){var selectedCaret=caret(input);if(tabbed){if(isRTL){selectedCaret.end=selectedCaret.begin}else{selectedCaret.begin=selectedCaret.end}}if(selectedCaret.begin===selectedCaret.end){switch(opts.positionCaretOnClick){case"none":break;case"select":caret(input,0,getBuffer().length);break;case"ignore":caret(input,seekNext(getLastValidPosition()));break;case"radixFocus":if(doRadixFocus(selectedCaret.begin)){var radixPos=getBuffer().join("").indexOf(opts.radixPoint);caret(input,opts.numericInput?seekNext(radixPos):radixPos);break}default:var clickPosition=selectedCaret.begin,lvclickPosition=getLastValidPosition(clickPosition,true),lastPosition=seekNext(lvclickPosition);if(clickPosition<lastPosition){caret(input,!isMask(clickPosition,true)&&!isMask(clickPosition-1,true)?seekNext(clickPosition):clickPosition)}else{var lvp=getMaskSet().validPositions[lvclickPosition],tt=getTestTemplate(lastPosition,lvp?lvp.match.locator:undefined,lvp),placeholder=getPlaceholder(lastPosition,tt.match);if(placeholder!==""&&getBuffer()[lastPosition]!==placeholder&&tt.match.optionalQuantifier!==true&&tt.match.newBlockMarker!==true||!isMask(lastPosition,opts.keepStatic)&&tt.match.def===placeholder){var newPos=seekNext(lastPosition);if(clickPosition>=newPos||clickPosition===lastPosition){lastPosition=newPos}}caret(input,lastPosition)}break}}}},0)},cutEvent:function cutEvent(e){var input=this,$input=$(input),pos=caret(input),ev=e.originalEvent||e;var clipboardData=window.clipboardData||ev.clipboardData,clipData=isRTL?getBuffer().slice(pos.end,pos.begin):getBuffer().slice(pos.begin,pos.end);clipboardData.setData("text",isRTL?clipData.reverse().join(""):clipData.join(""));if(document.execCommand)document.execCommand("copy");handleRemove(input,Inputmask.keyCode.DELETE,pos);writeBuffer(input,getBuffer(),getMaskSet().p,e,undoValue!==getBuffer().join(""))},blurEvent:function blurEvent(e){var $input=$(this),input=this;if(input.inputmask){HandleNativePlaceholder(input,originalPlaceholder);var nptValue=input.inputmask._valueGet(),buffer=getBuffer().slice();if(nptValue!==""||colorMask!==undefined){if(opts.clearMaskOnLostFocus){if(getLastValidPosition()===-1&&nptValue===getBufferTemplate().join("")){buffer=[]}else{clearOptionalTail(buffer)}}if(isComplete(buffer)===false){setTimeout(function(){$input.trigger("incomplete")},0);if(opts.clearIncomplete){resetMaskSet();if(opts.clearMaskOnLostFocus){buffer=[]}else{buffer=getBufferTemplate().slice()}}}writeBuffer(input,buffer,undefined,e)}if(undoValue!==getBuffer().join("")){undoValue=buffer.join("");$input.trigger("change")}}},mouseenterEvent:function mouseenterEvent(e){var input=this;mouseEnter=true;if(document.activeElement!==input&&opts.showMaskOnHover){HandleNativePlaceholder(input,(isRTL?getBuffer().slice().reverse():getBuffer()).join(""))}},submitEvent:function submitEvent(e){if(undoValue!==getBuffer().join("")){$el.trigger("change")}if(opts.clearMaskOnLostFocus&&getLastValidPosition()===-1&&el.inputmask._valueGet&&el.inputmask._valueGet()===getBufferTemplate().join("")){el.inputmask._valueSet("")}if(opts.clearIncomplete&&isComplete(getBuffer())===false){el.inputmask._valueSet("")}if(opts.removeMaskOnSubmit){el.inputmask._valueSet(el.inputmask.unmaskedvalue(),true);setTimeout(function(){writeBuffer(el,getBuffer())},0)}},resetEvent:function resetEvent(e){el.inputmask.refreshValue=true;setTimeout(function(){$el.trigger("setvalue")},0)}};function checkVal(input,writeOut,strict,nptvl,initiatingEvent){var inputmask=this||input.inputmask,inputValue=nptvl.slice(),charCodes="",initialNdx=-1,result=undefined;function isTemplateMatch(ndx,charCodes){var charCodeNdx=getMaskTemplate(true,0,false).slice(ndx,seekNext(ndx)).join("").replace(/'/g,"").indexOf(charCodes);return charCodeNdx!==-1&&!isMask(ndx)&&(getTest(ndx).match.nativeDef===charCodes.charAt(0)||getTest(ndx).match.fn===null&&getTest(ndx).match.nativeDef==="'"+charCodes.charAt(0)||getTest(ndx).match.nativeDef===" "&&(getTest(ndx+1).match.nativeDef===charCodes.charAt(0)||getTest(ndx+1).match.fn===null&&getTest(ndx+1).match.nativeDef==="'"+charCodes.charAt(0)))}resetMaskSet();if(!strict&&opts.autoUnmask!==true){var staticInput=getBufferTemplate().slice(0,seekNext(-1)).join(""),matches=inputValue.join("").match(new RegExp("^"+Inputmask.escapeRegex(staticInput),"g"));if(matches&&matches.length>0){inputValue.splice(0,matches.length*staticInput.length);initialNdx=seekNext(initialNdx)}}else{initialNdx=seekNext(initialNdx)}if(initialNdx===-1){getMaskSet().p=seekNext(initialNdx);initialNdx=0}else getMaskSet().p=initialNdx;inputmask.caretPos={begin:initialNdx};$.each(inputValue,function(ndx,charCode){if(charCode!==undefined){if(getMaskSet().validPositions[ndx]===undefined&&inputValue[ndx]===getPlaceholder(ndx)&&isMask(ndx,true)&&isValid(ndx,inputValue[ndx],true,undefined,undefined,true)===false){getMaskSet().p++}else{var keypress=new $.Event("_checkval");keypress.which=charCode.charCodeAt(0);charCodes+=charCode;var lvp=getLastValidPosition(undefined,true);if(!isTemplateMatch(initialNdx,charCodes)){result=EventHandlers.keypressEvent.call(input,keypress,true,false,strict,inputmask.caretPos.begin);if(result){initialNdx=inputmask.caretPos.begin+1;charCodes=""}}else{result=EventHandlers.keypressEvent.call(input,keypress,true,false,strict,lvp+1)}if(result){writeBuffer(undefined,getBuffer(),result.forwardPosition,keypress,false);inputmask.caretPos={begin:result.forwardPosition,end:result.forwardPosition}}}}});if(writeOut)writeBuffer(input,getBuffer(),result?result.forwardPosition:undefined,initiatingEvent||new $.Event("checkval"),initiatingEvent&&initiatingEvent.type==="input")}function unmaskedvalue(input){if(input){if(input.inputmask===undefined){return input.value}if(input.inputmask&&input.inputmask.refreshValue){EventHandlers.setValueEvent.call(input)}}var umValue=[],vps=getMaskSet().validPositions;for(var pndx in vps){if(vps[pndx].match&&vps[pndx].match.fn!=null){umValue.push(vps[pndx].input)}}var unmaskedValue=umValue.length===0?"":(isRTL?umValue.reverse():umValue).join("");if($.isFunction(opts.onUnMask)){var bufferValue=(isRTL?getBuffer().slice().reverse():getBuffer()).join("");unmaskedValue=opts.onUnMask.call(inputmask,bufferValue,unmaskedValue,opts)}return unmaskedValue}function caret(input,begin,end,notranslate){function translatePosition(pos){if(isRTL&&typeof pos==="number"&&(!opts.greedy||opts.placeholder!=="")&&el){pos=el.inputmask._valueGet().length-pos}return pos}var range;if(begin!==undefined){if($.isArray(begin)){end=isRTL?begin[0]:begin[1];begin=isRTL?begin[1]:begin[0]}if(begin.begin!==undefined){end=isRTL?begin.begin:begin.end;begin=isRTL?begin.end:begin.begin}if(typeof begin==="number"){begin=notranslate?begin:translatePosition(begin);end=notranslate?end:translatePosition(end);end=typeof end=="number"?end:begin;var scrollCalc=parseInt(((input.ownerDocument.defaultView||window).getComputedStyle?(input.ownerDocument.defaultView||window).getComputedStyle(input,null):input.currentStyle).fontSize)*end;input.scrollLeft=scrollCalc>input.scrollWidth?scrollCalc:0;input.inputmask.caretPos={begin:begin,end:end};if(input===document.activeElement){if("selectionStart"in input){input.selectionStart=begin;input.selectionEnd=end}else if(window.getSelection){range=document.createRange();if(input.firstChild===undefined||input.firstChild===null){var textNode=document.createTextNode("");input.appendChild(textNode)}range.setStart(input.firstChild,begin<input.inputmask._valueGet().length?begin:input.inputmask._valueGet().length);range.setEnd(input.firstChild,end<input.inputmask._valueGet().length?end:input.inputmask._valueGet().length);range.collapse(true);var sel=window.getSelection();sel.removeAllRanges();sel.addRange(range)}else if(input.createTextRange){range=input.createTextRange();range.collapse(true);range.moveEnd("character",end);range.moveStart("character",begin);range.select()}renderColorMask(input,{begin:begin,end:end})}}}else{if("selectionStart"in input){begin=input.selectionStart;end=input.selectionEnd}else if(window.getSelection){range=window.getSelection().getRangeAt(0);if(range.commonAncestorContainer.parentNode===input||range.commonAncestorContainer===input){begin=range.startOffset;end=range.endOffset}}else if(document.selection&&document.selection.createRange){range=document.selection.createRange();begin=0-range.duplicate().moveStart("character",-input.inputmask._valueGet().length);end=begin+range.text.length}return{begin:notranslate?begin:translatePosition(begin),end:notranslate?end:translatePosition(end)}}}function determineLastRequiredPosition(returnDefinition){var buffer=getMaskTemplate(true,getLastValidPosition(),true,true),bl=buffer.length,pos,lvp=getLastValidPosition(),positions={},lvTest=getMaskSet().validPositions[lvp],ndxIntlzr=lvTest!==undefined?lvTest.locator.slice():undefined,testPos;for(pos=lvp+1;pos<buffer.length;pos++){testPos=getTestTemplate(pos,ndxIntlzr,pos-1);ndxIntlzr=testPos.locator.slice();positions[pos]=$.extend(true,{},testPos)}var lvTestAlt=lvTest&&lvTest.alternation!==undefined?lvTest.locator[lvTest.alternation]:undefined;for(pos=bl-1;pos>lvp;pos--){testPos=positions[pos];if((testPos.match.optionality||testPos.match.optionalQuantifier&&testPos.match.newBlockMarker||lvTestAlt&&(lvTestAlt!==positions[pos].locator[lvTest.alternation]&&testPos.match.fn!=null||testPos.match.fn===null&&testPos.locator[lvTest.alternation]&&checkAlternationMatch(testPos.locator[lvTest.alternation].toString().split(","),lvTestAlt.toString().split(","))&&getTests(pos)[0].def!==""))&&buffer[pos]===getPlaceholder(pos,testPos.match)){bl--}else break}return returnDefinition?{l:bl,def:positions[bl]?positions[bl].match:undefined}:bl}function clearOptionalTail(buffer){buffer.length=0;var template=getMaskTemplate(true,0,true,undefined,true),lmnt,validPos;while(lmnt=template.shift(),lmnt!==undefined){buffer.push(lmnt)}return buffer}function isComplete(buffer){if($.isFunction(opts.isComplete))return opts.isComplete(buffer,opts);if(opts.repeat==="*")return undefined;var complete=false,lrp=determineLastRequiredPosition(true),aml=seekPrevious(lrp.l);if(lrp.def===undefined||lrp.def.newBlockMarker||lrp.def.optionality||lrp.def.optionalQuantifier){complete=true;for(var i=0;i<=aml;i++){var test=getTestTemplate(i).match;if(test.fn!==null&&getMaskSet().validPositions[i]===undefined&&test.optionality!==true&&test.optionalQuantifier!==true||test.fn===null&&buffer[i]!==getPlaceholder(i,test)){complete=false;break}}}return complete}function handleRemove(input,k,pos,strict,fromIsValid){if(opts.numericInput||isRTL){if(k===Inputmask.keyCode.BACKSPACE){k=Inputmask.keyCode.DELETE}else if(k===Inputmask.keyCode.DELETE){k=Inputmask.keyCode.BACKSPACE}if(isRTL){var pend=pos.end;pos.end=pos.begin;pos.begin=pend}}if(k===Inputmask.keyCode.BACKSPACE&&pos.end-pos.begin<1){pos.begin=seekPrevious(pos.begin);if(getMaskSet().validPositions[pos.begin]!==undefined&&getMaskSet().validPositions[pos.begin].input===opts.groupSeparator){pos.begin--}}else if(k===Inputmask.keyCode.DELETE&&pos.begin===pos.end){pos.end=isMask(pos.end,true)&&getMaskSet().validPositions[pos.end]&&getMaskSet().validPositions[pos.end].input!==opts.radixPoint?pos.end+1:seekNext(pos.end)+1;if(getMaskSet().validPositions[pos.begin]!==undefined&&getMaskSet().validPositions[pos.begin].input===opts.groupSeparator){pos.end++}}revalidateMask(pos);if(strict!==true&&opts.keepStatic!==false||opts.regex!==null){var result=alternate(true);if(result){var newPos=result.caret!==undefined?result.caret:result.pos?seekNext(result.pos.begin?result.pos.begin:result.pos):getLastValidPosition(-1,true);if(k!==Inputmask.keyCode.DELETE||pos.begin>newPos){pos.begin==newPos}}}var lvp=getLastValidPosition(pos.begin,true);if(lvp<pos.begin||pos.begin===-1){getMaskSet().p=seekNext(lvp)}else if(strict!==true){getMaskSet().p=pos.begin;if(fromIsValid!==true){while(getMaskSet().p<lvp&&getMaskSet().validPositions[getMaskSet().p]===undefined){getMaskSet().p++}}}}function initializeColorMask(input){var computedStyle=(input.ownerDocument.defaultView||window).getComputedStyle(input,null);function findCaretPos(clientx){var e=document.createElement("span"),caretPos;for(var style in computedStyle){if(isNaN(style)&&style.indexOf("font")!==-1){e.style[style]=computedStyle[style]}}e.style.textTransform=computedStyle.textTransform;e.style.letterSpacing=computedStyle.letterSpacing;e.style.position="absolute";e.style.height="auto";e.style.width="auto";e.style.visibility="hidden";e.style.whiteSpace="nowrap";document.body.appendChild(e);var inputText=input.inputmask._valueGet(),previousWidth=0,itl;for(caretPos=0,itl=inputText.length;caretPos<=itl;caretPos++){e.innerHTML+=inputText.charAt(caretPos)||"_";if(e.offsetWidth>=clientx){var offset1=clientx-previousWidth;var offset2=e.offsetWidth-clientx;e.innerHTML=inputText.charAt(caretPos);offset1-=e.offsetWidth/3;caretPos=offset1<offset2?caretPos-1:caretPos;break}previousWidth=e.offsetWidth}document.body.removeChild(e);return caretPos}var template=document.createElement("div");template.style.width=computedStyle.width;template.style.textAlign=computedStyle.textAlign;colorMask=document.createElement("div");input.inputmask.colorMask=colorMask;colorMask.className="im-colormask";input.parentNode.insertBefore(colorMask,input);input.parentNode.removeChild(input);colorMask.appendChild(input);colorMask.appendChild(template);input.style.left=template.offsetLeft+"px";$(colorMask).on("mouseleave",function(e){return EventHandlers.mouseleaveEvent.call(input,[e])});$(colorMask).on("mouseenter",function(e){return EventHandlers.mouseenterEvent.call(input,[e])});$(colorMask).on("click",function(e){caret(input,findCaretPos(e.clientX));return EventHandlers.clickEvent.call(input,[e])})}Inputmask.prototype.positionColorMask=function(input,template){input.style.left=template.offsetLeft+"px"};function renderColorMask(input,caretPos,clear){var maskTemplate=[],isStatic=false,test,testPos,ndxIntlzr,pos=0;function setEntry(entry){if(entry===undefined)entry="";if(!isStatic&&(test.fn===null||testPos.input===undefined)){isStatic=true;maskTemplate.push("<span class='im-static'>"+entry)}else if(isStatic&&(test.fn!==null&&testPos.input!==undefined||test.def==="")){isStatic=false;var mtl=maskTemplate.length;maskTemplate[mtl-1]=maskTemplate[mtl-1]+"</span>";maskTemplate.push(entry)}else maskTemplate.push(entry)}function setCaret(){if(document.activeElement===input){maskTemplate.splice(caretPos.begin,0,caretPos.begin===caretPos.end||caretPos.end>getMaskSet().maskLength?'<mark class="im-caret" style="border-right-width: 1px;border-right-style: solid;">':'<mark class="im-caret-select">');maskTemplate.splice(caretPos.end+1,0,"</mark>")}}if(colorMask!==undefined){var buffer=getBuffer();if(caretPos===undefined){caretPos=caret(input)}else if(caretPos.begin===undefined){caretPos={begin:caretPos,end:caretPos}}if(clear!==true){var lvp=getLastValidPosition();do{if(getMaskSet().validPositions[pos]){testPos=getMaskSet().validPositions[pos];test=testPos.match;ndxIntlzr=testPos.locator.slice();setEntry(buffer[pos])}else{testPos=getTestTemplate(pos,ndxIntlzr,pos-1);test=testPos.match;ndxIntlzr=testPos.locator.slice();if(opts.jitMasking===false||pos<lvp||typeof opts.jitMasking==="number"&&isFinite(opts.jitMasking)&&opts.jitMasking>pos){setEntry(getPlaceholder(pos,test))}else isStatic=false}pos++}while((maxLength===undefined||pos<maxLength)&&(test.fn!==null||test.def!=="")||lvp>pos||isStatic);if(isStatic)setEntry();setCaret()}var template=colorMask.getElementsByTagName("div")[0];template.innerHTML=maskTemplate.join("");input.inputmask.positionColorMask(input,template)}}function mask(elem){function isElementTypeSupported(input,opts){function patchValueProperty(npt){var valueGet;var valueSet;function patchValhook(type){if($.valHooks&&($.valHooks[type]===undefined||$.valHooks[type].inputmaskpatch!==true)){var valhookGet=$.valHooks[type]&&$.valHooks[type].get?$.valHooks[type].get:function(elem){return elem.value};var valhookSet=$.valHooks[type]&&$.valHooks[type].set?$.valHooks[type].set:function(elem,value){elem.value=value;return elem};$.valHooks[type]={get:function get(elem){if(elem.inputmask){if(elem.inputmask.opts.autoUnmask){return elem.inputmask.unmaskedvalue()}else{var result=valhookGet(elem);return getLastValidPosition(undefined,undefined,elem.inputmask.maskset.validPositions)!==-1||opts.nullable!==true?result:""}}else return valhookGet(elem)},set:function set(elem,value){var $elem=$(elem),result;result=valhookSet(elem,value);if(elem.inputmask){$elem.trigger("setvalue",[value])}return result},inputmaskpatch:true}}}function getter(){if(this.inputmask){return this.inputmask.opts.autoUnmask?this.inputmask.unmaskedvalue():getLastValidPosition()!==-1||opts.nullable!==true?document.activeElement===this&&opts.clearMaskOnLostFocus?(isRTL?clearOptionalTail(getBuffer().slice()).reverse():clearOptionalTail(getBuffer().slice())).join(""):valueGet.call(this):""}else return valueGet.call(this)}function setter(value){valueSet.call(this,value);if(this.inputmask){$(this).trigger("setvalue",[value])}}function installNativeValueSetFallback(npt){EventRuler.on(npt,"mouseenter",function(event){var $input=$(this),input=this,value=input.inputmask._valueGet();if(value!==getBuffer().join("")){$input.trigger("setvalue")}})}if(!npt.inputmask.__valueGet){if(opts.noValuePatching!==true){if(Object.getOwnPropertyDescriptor){if(typeof Object.getPrototypeOf!=="function"){Object.getPrototypeOf=_typeof("test".__proto__)==="object"?function(object){return object.__proto__}:function(object){return object.constructor.prototype}}var valueProperty=Object.getPrototypeOf?Object.getOwnPropertyDescriptor(Object.getPrototypeOf(npt),"value"):undefined;if(valueProperty&&valueProperty.get&&valueProperty.set){valueGet=valueProperty.get;valueSet=valueProperty.set;Object.defineProperty(npt,"value",{get:getter,set:setter,configurable:true})}else if(npt.tagName!=="INPUT"){valueGet=function valueGet(){return this.textContent};valueSet=function valueSet(value){this.textContent=value};Object.defineProperty(npt,"value",{get:getter,set:setter,configurable:true})}}else if(document.__lookupGetter__&&npt.__lookupGetter__("value")){valueGet=npt.__lookupGetter__("value");valueSet=npt.__lookupSetter__("value");npt.__defineGetter__("value",getter);npt.__defineSetter__("value",setter)}npt.inputmask.__valueGet=valueGet;npt.inputmask.__valueSet=valueSet}npt.inputmask._valueGet=function(overruleRTL){return isRTL&&overruleRTL!==true?valueGet.call(this.el).split("").reverse().join(""):valueGet.call(this.el)};npt.inputmask._valueSet=function(value,overruleRTL){valueSet.call(this.el,value===null||value===undefined?"":overruleRTL!==true&&isRTL?value.split("").reverse().join(""):value)};if(valueGet===undefined){valueGet=function valueGet(){return this.value};valueSet=function valueSet(value){this.value=value};patchValhook(npt.type);installNativeValueSetFallback(npt)}}}var elementType=input.getAttribute("type");var isSupported=input.tagName==="INPUT"&&$.inArray(elementType,opts.supportsInputType)!==-1||input.isContentEditable||input.tagName==="TEXTAREA";if(!isSupported){if(input.tagName==="INPUT"){var el=document.createElement("input");el.setAttribute("type",elementType);isSupported=el.type==="text";el=null}else isSupported="partial"}if(isSupported!==false){patchValueProperty(input)}else input.inputmask=undefined;return isSupported}EventRuler.off(elem);var isSupported=isElementTypeSupported(elem,opts);if(isSupported!==false){el=elem;$el=$(el);originalPlaceholder=el.placeholder;maxLength=el!==undefined?el.maxLength:undefined;if(maxLength===-1)maxLength=undefined;if(opts.colorMask===true){initializeColorMask(el)}if(mobile){if("inputmode"in el){el.inputmode=opts.inputmode;el.setAttribute("inputmode",opts.inputmode)}if(opts.disablePredictiveText===true){if("autocorrect"in el){el.autocorrect=false}else{if(opts.colorMask!==true){initializeColorMask(el)}el.type="password"}}}if(isSupported===true){el.setAttribute("im-insert",opts.insertMode);EventRuler.on(el,"submit",EventHandlers.submitEvent);EventRuler.on(el,"reset",EventHandlers.resetEvent);EventRuler.on(el,"blur",EventHandlers.blurEvent);EventRuler.on(el,"focus",EventHandlers.focusEvent);if(opts.colorMask!==true){EventRuler.on(el,"click",EventHandlers.clickEvent);EventRuler.on(el,"mouseleave",EventHandlers.mouseleaveEvent);EventRuler.on(el,"mouseenter",EventHandlers.mouseenterEvent)}EventRuler.on(el,"paste",EventHandlers.pasteEvent);EventRuler.on(el,"cut",EventHandlers.cutEvent);EventRuler.on(el,"complete",opts.oncomplete);EventRuler.on(el,"incomplete",opts.onincomplete);EventRuler.on(el,"cleared",opts.oncleared);if(!mobile&&opts.inputEventOnly!==true){EventRuler.on(el,"keydown",EventHandlers.keydownEvent);EventRuler.on(el,"keypress",EventHandlers.keypressEvent)}else{el.removeAttribute("maxLength")}EventRuler.on(el,"input",EventHandlers.inputFallBackEvent);EventRuler.on(el,"beforeinput",EventHandlers.beforeInputEvent)}EventRuler.on(el,"setvalue",EventHandlers.setValueEvent);undoValue=getBufferTemplate().join("");if(el.inputmask._valueGet(true)!==""||opts.clearMaskOnLostFocus===false||document.activeElement===el){var initialValue=$.isFunction(opts.onBeforeMask)?opts.onBeforeMask.call(inputmask,el.inputmask._valueGet(true),opts)||el.inputmask._valueGet(true):el.inputmask._valueGet(true);if(initialValue!=="")checkVal(el,true,false,initialValue.split(""));var buffer=getBuffer().slice();undoValue=buffer.join("");if(isComplete(buffer)===false){if(opts.clearIncomplete){resetMaskSet()}}if(opts.clearMaskOnLostFocus&&document.activeElement!==el){if(getLastValidPosition()===-1){buffer=[]}else{clearOptionalTail(buffer)}}if(opts.clearMaskOnLostFocus===false||opts.showMaskOnFocus&&document.activeElement===el||el.inputmask._valueGet(true)!=="")writeBuffer(el,buffer);if(document.activeElement===el){caret(el,seekNext(getLastValidPosition()))}}}}var valueBuffer;if(actionObj!==undefined){switch(actionObj.action){case"isComplete":el=actionObj.el;return isComplete(getBuffer());case"unmaskedvalue":if(el===undefined||actionObj.value!==undefined){valueBuffer=actionObj.value;valueBuffer=($.isFunction(opts.onBeforeMask)?opts.onBeforeMask.call(inputmask,valueBuffer,opts)||valueBuffer:valueBuffer).split("");checkVal.call(this,undefined,false,false,valueBuffer);if($.isFunction(opts.onBeforeWrite))opts.onBeforeWrite.call(inputmask,undefined,getBuffer(),0,opts)}return unmaskedvalue(el);case"mask":mask(el);break;case"format":valueBuffer=($.isFunction(opts.onBeforeMask)?opts.onBeforeMask.call(inputmask,actionObj.value,opts)||actionObj.value:actionObj.value).split("");checkVal.call(this,undefined,true,false,valueBuffer);if(actionObj.metadata){return{value:isRTL?getBuffer().slice().reverse().join(""):getBuffer().join(""),metadata:maskScope.call(this,{action:"getmetadata"},maskset,opts)}}return isRTL?getBuffer().slice().reverse().join(""):getBuffer().join("");case"isValid":if(actionObj.value){valueBuffer=actionObj.value.split("");checkVal.call(this,undefined,true,true,valueBuffer)}else{actionObj.value=getBuffer().join("")}var buffer=getBuffer();var rl=determineLastRequiredPosition(),lmib=buffer.length-1;for(;lmib>rl;lmib--){if(isMask(lmib))break}buffer.splice(rl,lmib+1-rl);return isComplete(buffer)&&actionObj.value===getBuffer().join("");case"getemptymask":return getBufferTemplate().join("");case"remove":if(el&&el.inputmask){$.data(el,"_inputmask_opts",null);$el=$(el);el.inputmask._valueSet(opts.autoUnmask?unmaskedvalue(el):el.inputmask._valueGet(true));EventRuler.off(el);if(el.inputmask.colorMask){colorMask=el.inputmask.colorMask;colorMask.removeChild(el);colorMask.parentNode.insertBefore(el,colorMask);colorMask.parentNode.removeChild(colorMask)}var valueProperty;if(Object.getOwnPropertyDescriptor&&Object.getPrototypeOf){valueProperty=Object.getOwnPropertyDescriptor(Object.getPrototypeOf(el),"value");if(valueProperty){if(el.inputmask.__valueGet){Object.defineProperty(el,"value",{get:el.inputmask.__valueGet,set:el.inputmask.__valueSet,configurable:true})}}}else if(document.__lookupGetter__&&el.__lookupGetter__("value")){if(el.inputmask.__valueGet){el.__defineGetter__("value",el.inputmask.__valueGet);el.__defineSetter__("value",el.inputmask.__valueSet)}}el.inputmask=undefined}return el;break;case"getmetadata":if($.isArray(maskset.metadata)){var maskTarget=getMaskTemplate(true,0,false).join("");$.each(maskset.metadata,function(ndx,mtdt){if(mtdt.mask===maskTarget){maskTarget=mtdt;return false}});return maskTarget}return maskset.metadata}}}return Inputmask})},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(4)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function($){return $})},function(module,exports){module.exports=jQuery},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};if(true)!(__WEBPACK_AMD_DEFINE_RESULT__=function(){return typeof window!=="undefined"?window:new(eval("require('jsdom').JSDOM"))("").window}.call(exports,__webpack_require__,exports,module),__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__));else{}},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(2)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function(Inputmask){var $=Inputmask.dependencyLib;var formatCode={d:["[1-9]|[12][0-9]|3[01]",Date.prototype.setDate,"day",Date.prototype.getDate],dd:["0[1-9]|[12][0-9]|3[01]",Date.prototype.setDate,"day",function(){return pad(Date.prototype.getDate.call(this),2)}],ddd:[""],dddd:[""],m:["[1-9]|1[012]",Date.prototype.setMonth,"month",function(){return Date.prototype.getMonth.call(this)+1}],mm:["0[1-9]|1[012]",Date.prototype.setMonth,"month",function(){return pad(Date.prototype.getMonth.call(this)+1,2)}],mmm:[""],mmmm:[""],yy:["[0-9]{2}",Date.prototype.setFullYear,"year",function(){return pad(Date.prototype.getFullYear.call(this),2)}],yyyy:["[0-9]{4}",Date.prototype.setFullYear,"year",function(){return pad(Date.prototype.getFullYear.call(this),4)}],h:["[1-9]|1[0-2]",Date.prototype.setHours,"hours",Date.prototype.getHours],hh:["0[1-9]|1[0-2]",Date.prototype.setHours,"hours",function(){return pad(Date.prototype.getHours.call(this),2)}],hhh:["[0-9]+",Date.prototype.setHours,"hours",Date.prototype.getHours],H:["1?[0-9]|2[0-3]",Date.prototype.setHours,"hours",Date.prototype.getHours],HH:["[01][0-9]|2[0-3]",Date.prototype.setHours,"hours",function(){return pad(Date.prototype.getHours.call(this),2)}],HHH:["[0-9]+",Date.prototype.setHours,"hours",Date.prototype.getHours],M:["[1-5]?[0-9]",Date.prototype.setMinutes,"minutes",Date.prototype.getMinutes],MM:["[0-5][0-9]",Date.prototype.setMinutes,"minutes",function(){return pad(Date.prototype.getMinutes.call(this),2)}],s:["[1-5]?[0-9]",Date.prototype.setSeconds,"seconds",Date.prototype.getSeconds],ss:["[0-5][0-9]",Date.prototype.setSeconds,"seconds",function(){return pad(Date.prototype.getSeconds.call(this),2)}],l:["[0-9]{3}",Date.prototype.setMilliseconds,"milliseconds",function(){return pad(Date.prototype.getMilliseconds.call(this),3)}],L:["[0-9]{2}",Date.prototype.setMilliseconds,"milliseconds",function(){return pad(Date.prototype.getMilliseconds.call(this),2)}],t:["[ap]"],tt:["[ap]m"],T:["[AP]"],TT:["[AP]M"],Z:[""],o:[""],S:[""]},formatAlias={isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"};function getTokenizer(opts){if(!opts.tokenizer){var tokens=[];for(var ndx in formatCode){if(tokens.indexOf(ndx[0])===-1)tokens.push(ndx[0])}opts.tokenizer="("+tokens.join("+|")+")+?|.";opts.tokenizer=new RegExp(opts.tokenizer,"g")}return opts.tokenizer}function isValidDate(dateParts,currentResult){return!isFinite(dateParts.rawday)||dateParts.day=="29"&&!isFinite(dateParts.rawyear)||new Date(dateParts.date.getFullYear(),isFinite(dateParts.rawmonth)?dateParts.month:dateParts.date.getMonth()+1,0).getDate()>=dateParts.day?currentResult:false}function isDateInRange(dateParts,opts){var result=true;if(opts.min){if(dateParts["rawyear"]){var rawYear=dateParts["rawyear"].replace(/[^0-9]/g,""),minYear=opts.min.year.substr(0,rawYear.length);result=minYear<=rawYear}if(dateParts["year"]===dateParts["rawyear"]){if(opts.min.date.getTime()===opts.min.date.getTime()){result=opts.min.date.getTime()<=dateParts.date.getTime()}}}if(result&&opts.max&&opts.max.date.getTime()===opts.max.date.getTime()){result=opts.max.date.getTime()>=dateParts.date.getTime()}return result}function parse(format,dateObjValue,opts,raw){var mask="",match;while(match=getTokenizer(opts).exec(format)){if(dateObjValue===undefined){if(formatCode[match[0]]){mask+="("+formatCode[match[0]][0]+")"}else{switch(match[0]){case"[":mask+="(";break;case"]":mask+=")?";break;default:mask+=Inputmask.escapeRegex(match[0])}}}else{if(formatCode[match[0]]){if(raw!==true&&formatCode[match[0]][3]){var getFn=formatCode[match[0]][3];mask+=getFn.call(dateObjValue.date)}else if(formatCode[match[0]][2])mask+=dateObjValue["raw"+formatCode[match[0]][2]];else mask+=match[0]}else mask+=match[0]}}return mask}function pad(val,len){val=String(val);len=len||2;while(val.length<len){val="0"+val}return val}function analyseMask(maskString,format,opts){var dateObj={date:new Date(1,0,1)},targetProp,mask=maskString,match,dateOperation,targetValidator;function extendProperty(value){var correctedValue=value.replace(/[^0-9]/g,"0");if(correctedValue!=value){var enteredPart=value.replace(/[^0-9]/g,""),min=(opts.min&&opts.min[targetProp]||value).toString(),max=(opts.max&&opts.max[targetProp]||value).toString();correctedValue=enteredPart+(enteredPart<min.slice(0,enteredPart.length)?min.slice(enteredPart.length):enteredPart>max.slice(0,enteredPart.length)?max.slice(enteredPart.length):correctedValue.toString().slice(enteredPart.length))}return correctedValue}function setValue(dateObj,value,opts){dateObj[targetProp]=extendProperty(value);dateObj["raw"+targetProp]=value;if(dateOperation!==undefined)dateOperation.call(dateObj.date,targetProp=="month"?parseInt(dateObj[targetProp])-1:dateObj[targetProp])}if(typeof mask==="string"){while(match=getTokenizer(opts).exec(format)){var value=mask.slice(0,match[0].length);if(formatCode.hasOwnProperty(match[0])){targetValidator=formatCode[match[0]][0];targetProp=formatCode[match[0]][2];dateOperation=formatCode[match[0]][1];setValue(dateObj,value,opts)}mask=mask.slice(value.length)}return dateObj}else if(mask&&(typeof mask==="undefined"?"undefined":_typeof(mask))==="object"&&mask.hasOwnProperty("date")){return mask}return undefined}Inputmask.extendAliases({datetime:{mask:function mask(opts){formatCode.S=opts.i18n.ordinalSuffix.join("|");opts.inputFormat=formatAlias[opts.inputFormat]||opts.inputFormat;opts.displayFormat=formatAlias[opts.displayFormat]||opts.displayFormat||opts.inputFormat;opts.outputFormat=formatAlias[opts.outputFormat]||opts.outputFormat||opts.inputFormat;opts.placeholder=opts.placeholder!==""?opts.placeholder:opts.inputFormat.replace(/[\[\]]/,"");opts.regex=parse(opts.inputFormat,undefined,opts);return null},placeholder:"",inputFormat:"isoDateTime",displayFormat:undefined,outputFormat:undefined,min:null,max:null,i18n:{dayNames:["Mon","Tue","Wed","Thu","Fri","Sat","Sun","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"],ordinalSuffix:["st","nd","rd","th"]},postValidation:function postValidation(buffer,pos,currentResult,opts){opts.min=analyseMask(opts.min,opts.inputFormat,opts);opts.max=analyseMask(opts.max,opts.inputFormat,opts);var result=currentResult,dateParts=analyseMask(buffer.join(""),opts.inputFormat,opts);if(result&&dateParts.date.getTime()===dateParts.date.getTime()){result=isValidDate(dateParts,result);result=result&&isDateInRange(dateParts,opts)}if(pos&&result&&currentResult.pos!==pos){return{buffer:parse(opts.inputFormat,dateParts,opts),refreshFromBuffer:{start:pos,end:currentResult.pos}}}return result},onKeyDown:function onKeyDown(e,buffer,caretPos,opts){var input=this;if(e.ctrlKey&&e.keyCode===Inputmask.keyCode.RIGHT){var today=new Date,match,date="";while(match=getTokenizer(opts).exec(opts.inputFormat)){if(match[0].charAt(0)==="d"){date+=pad(today.getDate(),match[0].length)}else if(match[0].charAt(0)==="m"){date+=pad(today.getMonth()+1,match[0].length)}else if(match[0]==="yyyy"){date+=today.getFullYear().toString()}else if(match[0].charAt(0)==="y"){date+=pad(today.getYear(),match[0].length)}}input.inputmask._valueSet(date);$(input).trigger("setvalue")}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return parse(opts.outputFormat,analyseMask(maskedValue,opts.inputFormat,opts),opts,true)},casing:function casing(elem,test,pos,validPositions){if(test.nativeDef.indexOf("[ap]")==0)return elem.toLowerCase();if(test.nativeDef.indexOf("[AP]")==0)return elem.toUpperCase();return elem},insertMode:false,shiftPositions:false}});return Inputmask})},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(2)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function(Inputmask){var $=Inputmask.dependencyLib;function autoEscape(txt,opts){var escapedTxt="";for(var i=0;i<txt.length;i++){if(Inputmask.prototype.definitions[txt.charAt(i)]||opts.definitions[txt.charAt(i)]||opts.optionalmarker.start===txt.charAt(i)||opts.optionalmarker.end===txt.charAt(i)||opts.quantifiermarker.start===txt.charAt(i)||opts.quantifiermarker.end===txt.charAt(i)||opts.groupmarker.start===txt.charAt(i)||opts.groupmarker.end===txt.charAt(i)||opts.alternatormarker===txt.charAt(i)){escapedTxt+="\\"+txt.charAt(i)}else escapedTxt+=txt.charAt(i)}return escapedTxt}function alignDigits(buffer,digits,opts){if(digits>0){var radixPosition=$.inArray(opts.radixPoint,buffer);if(radixPosition===-1){buffer.push(opts.radixPoint);radixPosition=buffer.length-1}for(var i=1;i<=digits;i++){buffer[radixPosition+i]=buffer[radixPosition+i]||"0"}}return buffer}Inputmask.extendAliases({numeric:{mask:function mask(opts){if(opts.repeat!==0&&isNaN(opts.integerDigits)){opts.integerDigits=opts.repeat}opts.repeat=0;if(opts.groupSeparator===opts.radixPoint&&opts.digits&&opts.digits!=="0"){if(opts.radixPoint==="."){opts.groupSeparator=","}else if(opts.radixPoint===","){opts.groupSeparator="."}else opts.groupSeparator=""}if(opts.groupSeparator===" "){opts.skipOptionalPartCharacter=undefined}opts.autoGroup=opts.autoGroup&&opts.groupSeparator!=="";if(opts.autoGroup){if(typeof opts.groupSize=="string"&&isFinite(opts.groupSize))opts.groupSize=parseInt(opts.groupSize);if(isFinite(opts.integerDigits)){var seps=Math.floor(opts.integerDigits/opts.groupSize);var mod=opts.integerDigits%opts.groupSize;opts.integerDigits=parseInt(opts.integerDigits)+(mod===0?seps-1:seps);if(opts.integerDigits<1){opts.integerDigits="*"}}}if(opts.placeholder.length>1){opts.placeholder=opts.placeholder.charAt(0)}if(opts.positionCaretOnClick==="radixFocus"&&opts.placeholder===""&&opts.integerOptional===false){opts.positionCaretOnClick="lvp"}opts.definitions[";"]=opts.definitions["~"];opts.definitions[";"].definitionSymbol="~";if(opts.numericInput===true){opts.positionCaretOnClick=opts.positionCaretOnClick==="radixFocus"?"lvp":opts.positionCaretOnClick;opts.digitsOptional=false;if(isNaN(opts.digits))opts.digits=2;opts.decimalProtect=false}var mask="[+]";mask+=autoEscape(opts.prefix,opts);if(opts.integerOptional===true){mask+="~{1,"+opts.integerDigits+"}"}else mask+="~{"+opts.integerDigits+"}";if(opts.digits!==undefined){var radixDef=opts.decimalProtect?":":opts.radixPoint;var dq=opts.digits.toString().split(",");if(isFinite(dq[0])&&dq[1]&&isFinite(dq[1])){mask+=radixDef+";{"+opts.digits+"}"}else if(isNaN(opts.digits)||parseInt(opts.digits)>0){if(opts.digitsOptional){mask+="["+radixDef+";{1,"+opts.digits+"}]"}else mask+=radixDef+";{"+opts.digits+"}"}}mask+=autoEscape(opts.suffix,opts);mask+="[-]";opts.greedy=false;return mask},placeholder:"",greedy:false,digits:"*",digitsOptional:true,enforceDigitsOnBlur:false,radixPoint:".",positionCaretOnClick:"radixFocus",groupSize:3,groupSeparator:"",autoGroup:false,allowMinus:true,negationSymbol:{front:"-",back:""},integerDigits:"+",integerOptional:true,prefix:"",suffix:"",rightAlign:true,decimalProtect:true,min:null,max:null,step:1,insertMode:true,autoUnmask:false,unmaskAsNumber:false,inputType:"text",inputmode:"numeric",preValidation:function preValidation(buffer,pos,c,isSelection,opts,maskset){if(c==="-"||c===opts.negationSymbol.front){if(opts.allowMinus!==true)return false;opts.isNegative=opts.isNegative===undefined?true:!opts.isNegative;if(buffer.join("")==="")return true;return{caret:maskset.validPositions[pos]?pos:undefined,dopost:true}}if(isSelection===false&&c===opts.radixPoint&&opts.digits!==undefined&&(isNaN(opts.digits)||parseInt(opts.digits)>0)){var radixPos=$.inArray(opts.radixPoint,buffer);if(radixPos!==-1&&maskset.validPositions[radixPos]!==undefined){if(opts.numericInput===true){return pos===radixPos}return{caret:radixPos+1}}}return true},postValidation:function postValidation(buffer,pos,currentResult,opts){function buildPostMask(buffer,opts){var postMask="";postMask+="("+opts.groupSeparator+"*{"+opts.groupSize+"}){*}";if(opts.radixPoint!==""){var radixSplit=buffer.join("").split(opts.radixPoint);if(radixSplit[1]){postMask+=opts.radixPoint+"*{"+radixSplit[1].match(/^\d*\??\d*/)[0].length+"}"}}return postMask}var suffix=opts.suffix.split(""),prefix=opts.prefix.split("");if(currentResult.pos===undefined&&currentResult.caret!==undefined&&currentResult.dopost!==true)return currentResult;var caretPos=currentResult.caret!==undefined?currentResult.caret:currentResult.pos;var maskedValue=buffer.slice();if(opts.numericInput){caretPos=maskedValue.length-caretPos-1;maskedValue=maskedValue.reverse()}var charAtPos=maskedValue[caretPos];if(charAtPos===opts.groupSeparator){caretPos+=1;charAtPos=maskedValue[caretPos]}if(caretPos===maskedValue.length-opts.suffix.length-1&&charAtPos===opts.radixPoint)return currentResult;if(charAtPos!==undefined){if(charAtPos!==opts.radixPoint&&charAtPos!==opts.negationSymbol.front&&charAtPos!==opts.negationSymbol.back){maskedValue[caretPos]="?";if(opts.prefix.length>0&&caretPos>=(opts.isNegative===false?1:0)&&caretPos<opts.prefix.length-1+(opts.isNegative===false?1:0)){prefix[caretPos-(opts.isNegative===false?1:0)]="?"}else if(opts.suffix.length>0&&caretPos>=maskedValue.length-opts.suffix.length-(opts.isNegative===false?1:0)){suffix[caretPos-(maskedValue.length-opts.suffix.length-(opts.isNegative===false?1:0))]="?"}}}prefix=prefix.join("");suffix=suffix.join("");var processValue=maskedValue.join("").replace(prefix,"");processValue=processValue.replace(suffix,"");processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator),"g"),"");processValue=processValue.replace(new RegExp("[-"+Inputmask.escapeRegex(opts.negationSymbol.front)+"]","g"),"");processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.negationSymbol.back)+"$"),"");if(isNaN(opts.placeholder)){processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.placeholder),"g"),"")}if(processValue.length>1&&processValue.indexOf(opts.radixPoint)!==1){if(charAtPos==="0"){processValue=processValue.replace(/^\?/g,"")}processValue=processValue.replace(/^0/g,"")}if(processValue.charAt(0)===opts.radixPoint&&opts.radixPoint!==""&&opts.numericInput!==true){processValue="0"+processValue}if(processValue!==""){processValue=processValue.split("");if((!opts.digitsOptional||opts.enforceDigitsOnBlur&&currentResult.event==="blur")&&isFinite(opts.digits)){var radixPosition=$.inArray(opts.radixPoint,processValue);var rpb=$.inArray(opts.radixPoint,maskedValue);if(radixPosition===-1){processValue.push(opts.radixPoint);radixPosition=processValue.length-1}for(var i=1;i<=opts.digits;i++){if((!opts.digitsOptional||opts.enforceDigitsOnBlur&&currentResult.event==="blur")&&(processValue[radixPosition+i]===undefined||processValue[radixPosition+i]===opts.placeholder.charAt(0))){processValue[radixPosition+i]=currentResult.placeholder||opts.placeholder.charAt(0)}else if(rpb!==-1&&maskedValue[rpb+i]!==undefined){processValue[radixPosition+i]=processValue[radixPosition+i]||maskedValue[rpb+i]}}}if(opts.autoGroup===true&&opts.groupSeparator!==""&&(charAtPos!==opts.radixPoint||currentResult.pos!==undefined||currentResult.dopost)){var addRadix=processValue[processValue.length-1]===opts.radixPoint&&currentResult.c===opts.radixPoint;processValue=Inputmask(buildPostMask(processValue,opts),{numericInput:true,jitMasking:true,definitions:{"*":{validator:"[0-9?]",cardinality:1}}}).format(processValue.join(""));if(addRadix)processValue+=opts.radixPoint;if(processValue.charAt(0)===opts.groupSeparator){processValue.substr(1)}}else processValue=processValue.join("")}if(opts.isNegative&&currentResult.event==="blur"){opts.isNegative=processValue!=="0"}processValue=prefix+processValue;processValue+=suffix;if(opts.isNegative){processValue=opts.negationSymbol.front+processValue;processValue+=opts.negationSymbol.back}processValue=processValue.split("");if(charAtPos!==undefined){if(charAtPos!==opts.radixPoint&&charAtPos!==opts.negationSymbol.front&&charAtPos!==opts.negationSymbol.back){caretPos=$.inArray("?",processValue);if(caretPos>-1){processValue[caretPos]=charAtPos}else caretPos=currentResult.caret||0}else if(charAtPos===opts.radixPoint||charAtPos===opts.negationSymbol.front||charAtPos===opts.negationSymbol.back){var newCaretPos=$.inArray(charAtPos,processValue);if(newCaretPos!==-1)caretPos=newCaretPos}}if(opts.numericInput){caretPos=processValue.length-caretPos-1;processValue=processValue.reverse()}var rslt={caret:(charAtPos===undefined||currentResult.pos!==undefined)&&caretPos!==undefined?caretPos+(opts.numericInput?-1:1):caretPos,buffer:processValue,refreshFromBuffer:currentResult.dopost||buffer.join("")!==processValue.join("")};return rslt.refreshFromBuffer?rslt:currentResult},onBeforeWrite:function onBeforeWrite(e,buffer,caretPos,opts){function parseMinMaxOptions(opts){if(opts.parseMinMaxOptions===undefined){if(opts.min!==null){opts.min=opts.min.toString().replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator),"g"),"");if(opts.radixPoint===",")opts.min=opts.min.replace(opts.radixPoint,".");opts.min=isFinite(opts.min)?parseFloat(opts.min):NaN;if(isNaN(opts.min))opts.min=Number.MIN_VALUE}if(opts.max!==null){opts.max=opts.max.toString().replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator),"g"),"");if(opts.radixPoint===",")opts.max=opts.max.replace(opts.radixPoint,".");opts.max=isFinite(opts.max)?parseFloat(opts.max):NaN;if(isNaN(opts.max))opts.max=Number.MAX_VALUE}opts.parseMinMaxOptions="done"}}if(e){switch(e.type){case"keydown":return opts.postValidation(buffer,caretPos,{caret:caretPos,dopost:true},opts);case"blur":case"checkval":var unmasked;parseMinMaxOptions(opts);if(opts.min!==null||opts.max!==null){unmasked=opts.onUnMask(buffer.join(""),undefined,$.extend({},opts,{unmaskAsNumber:true}));if(opts.min!==null&&unmasked<opts.min){opts.isNegative=opts.min<0;return opts.postValidation(opts.min.toString().replace(".",opts.radixPoint).split(""),caretPos,{caret:caretPos,dopost:true,placeholder:"0"},opts)}else if(opts.max!==null&&unmasked>opts.max){opts.isNegative=opts.max<0;return opts.postValidation(opts.max.toString().replace(".",opts.radixPoint).split(""),caretPos,{caret:caretPos,dopost:true,placeholder:"0"},opts)}}return opts.postValidation(buffer,caretPos,{caret:caretPos,placeholder:"0",event:"blur"},opts);case"_checkval":return{caret:caretPos};default:break}}},regex:{integerPart:function integerPart(opts,emptyCheck){return emptyCheck?new RegExp("["+Inputmask.escapeRegex(opts.negationSymbol.front)+"+]?"):new RegExp("["+Inputmask.escapeRegex(opts.negationSymbol.front)+"+]?\\d+")},integerNPart:function integerNPart(opts){return new RegExp("[\\d"+Inputmask.escapeRegex(opts.groupSeparator)+Inputmask.escapeRegex(opts.placeholder.charAt(0))+"]+")}},definitions:{"~":{validator:function validator(chrs,maskset,pos,strict,opts,isSelection){var isValid,l;if(chrs==="k"||chrs==="m"){isValid={insert:[],c:0};for(var i=0,l=chrs==="k"?2:5;i<l;i++){isValid.insert.push({pos:pos+i,c:0})}isValid.pos=pos+l;return isValid}isValid=strict?new RegExp("[0-9"+Inputmask.escapeRegex(opts.groupSeparator)+"]").test(chrs):new RegExp("[0-9]").test(chrs);if(isValid===true){if(opts.numericInput!==true&&maskset.validPositions[pos]!==undefined&&maskset.validPositions[pos].match.def==="~"&&!isSelection){var processValue=maskset.buffer.join("");processValue=processValue.replace(new RegExp("[-"+Inputmask.escapeRegex(opts.negationSymbol.front)+"]","g"),"");processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.negationSymbol.back)+"$"),"");var pvRadixSplit=processValue.split(opts.radixPoint);if(pvRadixSplit.length>1){pvRadixSplit[1]=pvRadixSplit[1].replace(/0/g,opts.placeholder.charAt(0))}if(pvRadixSplit[0]==="0"){pvRadixSplit[0]=pvRadixSplit[0].replace(/0/g,opts.placeholder.charAt(0))}processValue=pvRadixSplit[0]+opts.radixPoint+pvRadixSplit[1]||"";var bufferTemplate=maskset._buffer.join("");if(processValue===opts.radixPoint){processValue=bufferTemplate}while(processValue.match(Inputmask.escapeRegex(bufferTemplate)+"$")===null){bufferTemplate=bufferTemplate.slice(1)}processValue=processValue.replace(bufferTemplate,"");processValue=processValue.split("");if(processValue[pos]===undefined){isValid={pos:pos,remove:pos}}else{isValid={pos:pos}}}}else if(!strict&&chrs===opts.radixPoint&&maskset.validPositions[pos-1]===undefined){isValid={insert:{pos:pos,c:0},pos:pos+1}}return isValid},cardinality:1},"+":{validator:function validator(chrs,maskset,pos,strict,opts){return opts.allowMinus&&(chrs==="-"||chrs===opts.negationSymbol.front)},cardinality:1,placeholder:""},"-":{validator:function validator(chrs,maskset,pos,strict,opts){return opts.allowMinus&&chrs===opts.negationSymbol.back},cardinality:1,placeholder:""},":":{validator:function validator(chrs,maskset,pos,strict,opts){var radix="["+Inputmask.escapeRegex(opts.radixPoint)+"]";var isValid=new RegExp(radix).test(chrs);if(isValid&&maskset.validPositions[pos]&&maskset.validPositions[pos].match.placeholder===opts.radixPoint){isValid={caret:pos+1}}return isValid},cardinality:1,placeholder:function placeholder(opts){return opts.radixPoint}}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){if(unmaskedValue===""&&opts.nullable===true){return unmaskedValue}var processValue=maskedValue.replace(opts.prefix,"");processValue=processValue.replace(opts.suffix,"");processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator),"g"),"");if(opts.placeholder.charAt(0)!==""){processValue=processValue.replace(new RegExp(opts.placeholder.charAt(0),"g"),"0")}if(opts.unmaskAsNumber){if(opts.radixPoint!==""&&processValue.indexOf(opts.radixPoint)!==-1)processValue=processValue.replace(Inputmask.escapeRegex.call(this,opts.radixPoint),".");processValue=processValue.replace(new RegExp("^"+Inputmask.escapeRegex(opts.negationSymbol.front)),"-");processValue=processValue.replace(new RegExp(Inputmask.escapeRegex(opts.negationSymbol.back)+"$"),"");return Number(processValue)}return processValue},isComplete:function isComplete(buffer,opts){var maskedValue=(opts.numericInput?buffer.slice().reverse():buffer).join("");maskedValue=maskedValue.replace(new RegExp("^"+Inputmask.escapeRegex(opts.negationSymbol.front)),"-");maskedValue=maskedValue.replace(new RegExp(Inputmask.escapeRegex(opts.negationSymbol.back)+"$"),"");maskedValue=maskedValue.replace(opts.prefix,"");maskedValue=maskedValue.replace(opts.suffix,"");maskedValue=maskedValue.replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator)+"([0-9]{3})","g"),"$1");if(opts.radixPoint===",")maskedValue=maskedValue.replace(Inputmask.escapeRegex(opts.radixPoint),".");return isFinite(maskedValue)},onBeforeMask:function onBeforeMask(initialValue,opts){opts.isNegative=undefined;var radixPoint=opts.radixPoint||",";if((typeof initialValue=="number"||opts.inputType==="number")&&radixPoint!==""){initialValue=initialValue.toString().replace(".",radixPoint)}var valueParts=initialValue.split(radixPoint),integerPart=valueParts[0].replace(/[^\-0-9]/g,""),decimalPart=valueParts.length>1?valueParts[1].replace(/[^0-9]/g,""):"";initialValue=integerPart+(decimalPart!==""?radixPoint+decimalPart:decimalPart);var digits=0;if(radixPoint!==""){digits=decimalPart.length;if(decimalPart!==""){var digitsFactor=Math.pow(10,digits||1);if(isFinite(opts.digits)){digits=parseInt(opts.digits);digitsFactor=Math.pow(10,digits)}initialValue=initialValue.replace(Inputmask.escapeRegex(radixPoint),".");if(isFinite(initialValue))initialValue=Math.round(parseFloat(initialValue)*digitsFactor)/digitsFactor;initialValue=initialValue.toString().replace(".",radixPoint)}}if(opts.digits===0&&initialValue.indexOf(Inputmask.escapeRegex(radixPoint))!==-1){initialValue=initialValue.substring(0,initialValue.indexOf(Inputmask.escapeRegex(radixPoint)))}return alignDigits(initialValue.toString().split(""),digits,opts).join("")},onKeyDown:function onKeyDown(e,buffer,caretPos,opts){var $input=$(this);if(e.ctrlKey){switch(e.keyCode){case Inputmask.keyCode.UP:$input.val(parseFloat(this.inputmask.unmaskedvalue())+parseInt(opts.step));$input.trigger("setvalue");break;case Inputmask.keyCode.DOWN:$input.val(parseFloat(this.inputmask.unmaskedvalue())-parseInt(opts.step));$input.trigger("setvalue");break}}}},currency:{prefix:"$ ",groupSeparator:",",alias:"numeric",placeholder:"0",autoGroup:true,digits:2,digitsOptional:false,clearMaskOnLostFocus:false},decimal:{alias:"numeric"},integer:{alias:"numeric",digits:0,radixPoint:""},percentage:{alias:"numeric",digits:2,digitsOptional:true,radixPoint:".",placeholder:"0",autoGroup:false,min:0,max:100,suffix:" %",allowMinus:false}});return Inputmask})},function(module,exports,__webpack_require__){"use strict";var __WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_ARRAY__,__WEBPACK_AMD_DEFINE_RESULT__;var _typeof=typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"?function(obj){return typeof obj}:function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj};(function(factory){if(true){!(__WEBPACK_AMD_DEFINE_ARRAY__=[__webpack_require__(4),__webpack_require__(2)],__WEBPACK_AMD_DEFINE_FACTORY__=factory,__WEBPACK_AMD_DEFINE_RESULT__=typeof __WEBPACK_AMD_DEFINE_FACTORY__==="function"?__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports,__WEBPACK_AMD_DEFINE_ARRAY__):__WEBPACK_AMD_DEFINE_FACTORY__,__WEBPACK_AMD_DEFINE_RESULT__!==undefined&&(module.exports=__WEBPACK_AMD_DEFINE_RESULT__))}else{}})(function($,Inputmask){if($.fn.inputmask===undefined){$.fn.inputmask=function(fn,options){var nptmask,input=this[0];if(options===undefined)options={};if(typeof fn==="string"){switch(fn){case"unmaskedvalue":return input&&input.inputmask?input.inputmask.unmaskedvalue():$(input).val();case"remove":return this.each(function(){if(this.inputmask)this.inputmask.remove()});case"getemptymask":return input&&input.inputmask?input.inputmask.getemptymask():"";case"hasMaskedValue":return input&&input.inputmask?input.inputmask.hasMaskedValue():false;case"isComplete":return input&&input.inputmask?input.inputmask.isComplete():true;case"getmetadata":return input&&input.inputmask?input.inputmask.getmetadata():undefined;case"setvalue":Inputmask.setValue(input,options);break;case"option":if(typeof options==="string"){if(input&&input.inputmask!==undefined){return input.inputmask.option(options)}}else{return this.each(function(){if(this.inputmask!==undefined){return this.inputmask.option(options)}})}break;default:options.alias=fn;nptmask=new Inputmask(options);return this.each(function(){nptmask.mask(this)})}}else if(Array.isArray(fn)){options.alias=fn;nptmask=new Inputmask(options);return this.each(function(){nptmask.mask(this)})}else if((typeof fn==="undefined"?"undefined":_typeof(fn))=="object"){nptmask=new Inputmask(fn);if(fn.mask===undefined&&fn.alias===undefined){return this.each(function(){if(this.inputmask!==undefined){return this.inputmask.option(fn)}else nptmask.mask(this)})}else{return this.each(function(){nptmask.mask(this)})}}else if(fn===undefined){return this.each(function(){nptmask=new Inputmask(options);nptmask.mask(this)})}}}return $.fn.inputmask})}]);

/*!
 * bindings/inputmask.binding.js
 * https://github.com/RobinHerbots/Inputmask
 * Copyright (c) 2010 - 2018 Robin Herbots
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
 * Version: 4.0.4
 */

(function(factory) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery", "../inputmask", "../global/window" ], factory);
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"), require("../inputmask"), require("../global/window"));
    } else {
        factory(jQuery, window.Inputmask, window);
    }
})(function($, Inputmask, window) {
    $(window.document).ajaxComplete(function(event, xmlHttpRequest, ajaxOptions) {
        if ($.inArray("html", ajaxOptions.dataTypes) !== -1) {
            $(".inputmask, [data-inputmask], [data-inputmask-mask], [data-inputmask-alias]").each(function(ndx, lmnt) {
                if (lmnt.inputmask === undefined) {
                    Inputmask().mask(lmnt);
                }
            });
        }
    }).ready(function() {
        $(".inputmask, [data-inputmask], [data-inputmask-mask], [data-inputmask-alias]").each(function(ndx, lmnt) {
            if (lmnt.inputmask === undefined) {
                Inputmask().mask(lmnt);
            }
        });
    });
});
/** Modules (Gateways & Extensions) settings board common JS. */

// Filter an extension cards list:
jQuery(document).ready(function($){

    let $filter = $('.leyka-modules-filter'),
        $extensions_list = $('.modules-cards-list'),
        extensions_filter = {};

    $filter.find('.filter-toggle').click(function(){
        $(this).closest('.filter-area').toggleClass('show');
    });

    $filter.find('.filter-category-show-filter').click(function(e){

        e.preventDefault();

        $(this).closest('.filter-area').toggleClass('show');

    });

    $filter.find('.filter-category-reset-filter').click(function(e){

        e.preventDefault();

        reset_filter();

    });

    $filter.find('.filter-category-item').click(function(e){

        e.preventDefault();

        toggle_filter_item($(this));
        apply_filter();

    });

    function reset_filter() {

        extensions_filter = {};

        $filter.find('.filter-category-item').removeClass('active');
        apply_filter();

    }

    function apply_filter() {
        if(Object.keys(extensions_filter).length) {

            $extensions_list.find('.module-card').hide();
            $extensions_list.find('.module-card.' + Object.keys(extensions_filter).join('.')).show();

        } else {
            $extensions_list.find('.module-card').show();
        }
    }

    function toggle_filter_item($filter_item) {

        $filter_item.toggleClass('active');

        if($filter_item.hasClass('active')) {
            extensions_filter[$filter_item.data('category')] = true;
        } else {
            delete extensions_filter[$filter_item.data('category')];
        }

    }

});
/** Common settings functions */

jQuery(document).ready(function($){

    const $body = $('body');

    if(leyka_ui_widget_available('accordion')) {
        $('.ui-accordion').accordion({
            heightStyle: 'content',
            // collapsible: true, active: false
        });
    }

    // if(typeof $.wp === 'object' && typeof $.wp.wpColorPicker !== 'undefined') {
    if(leyka_ui_widget_available('wpColorPicker', $.wp)) {
        $('.leyka-setting-field.colorpicker').wpColorPicker({ // Colorpicker fields
            change: function (e, ui) {
                $(e.target).parents('.field').find('.leyka-colorpicker-value').val(ui.color.toString()).change();
            }
        });
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
            option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result');

        $file_preview.hide().find('.file-preview').html('');
        $main_field.val('');

    });

    // Expandable options sections (portlets only):
    /** @todo Remove this completely when all portlets are converted to metaboxes */
    $('.leyka-options-section .header h3').click(function(e){

        e.preventDefault();

        $(this).closest('.leyka-options-section').toggleClass('collapsed');

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

    // var $campaign_select = $('#campaign-select');
    // if($campaign_select.length && typeof $().autocomplete !== 'undefined') {
    //
    //     $campaign_select.keyup(function(){
    //         if( !$(this).val() ) {
    //             $('#campaign-id').val('');
    //             $('#new-donation-purpose').html('');
    //         }
    //     });
    //
    //     $campaign_select.autocomplete({
    //         minLength: 1,
    //         focus: function(event, ui){
    //             $campaign_select.val(ui.item.label);
    //             $('#new-donation-purpose').html(ui.item.payment_title);
    //
    //             return false;
    //         },
    //         change: function(event, ui){
    //             if( !$campaign_select.val() ) {
    //                 $('#campaign-id').val('');
    //                 $('#new-donation-purpose').html('');
    //             }
    //         },
    //         close: function(event, ui){
    //             if( !$campaign_select.val() ) {
    //                 $('#campaign-id').val('');
    //                 $('#new-donation-purpose').html('');
    //             }
    //         },
    //         select: function(event, ui){
    //             $campaign_select.val(ui.item.label);
    //             $('#campaign-id').val(ui.item.value);
    //             $('#new-donation-purpose').html(ui.item.payment_title);
    //             return false;
    //         },
    //         source: function(request, response) {
    //             var term = request.term,
    //                 cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];
    //
    //             if(term in cache) {
    //                 response(cache[term]);
    //                 return;
    //             }
    //
    //             request.action = 'leyka_get_campaigns_list';
    //             request.nonce = $campaign_select.data('nonce');
    //
    //             $.getJSON(leyka.ajaxurl, request, function(data, status, xhr){
    //
    //                 var cache = $campaign_select.data('cache') ? $campaign_select.data('cache') : [];
    //
    //                 cache[term] = data;
    //                 response(data);
    //             });
    //         }
    //     });
    //
    //     $campaign_select.data('ui-autocomplete')._renderItem = function(ul, item){
    //         return $('<li>')
    //             .append(
    //                 '<a>'+item.label+(item.label == item.payment_title ? '' : '<div>'+item.payment_title+'</div></a>')
    //             )
    //             .appendTo(ul);
    //     };
    //
    // }

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
/** Common wizards functions */

// Expandable areas:
jQuery(document).ready(function($){
    $('.expandable-area .expand, .expandable-area .collapse').click(function(e){
        e.preventDefault();
        $(this).parent().toggleClass('collapsed');
    });
});

// Custom file input field:
jQuery(document).ready(function($){
    $('.settings-block.file .button').click(function(e){
        e.preventDefault();
        $(this).parent().find('input[type=file]').trigger('click');
    });
    
    $('.settings-block.file input[type=file]').change(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
    $('.settings-block.file input[type=file]').each(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
});


// Image modal:
jQuery(document).ready(function($){
    
    if(typeof($().easyModal) === 'undefined') {
        return;
    }

    $('.leyka-instructions-screen-full').easyModal({
        top: 100,
        autoOpen: false
    });

    $('.zoom-screen').on('click.leyka', function(e){

        e.preventDefault();
        $(this)
            .closest('.captioned-screen')
            .find('.leyka-instructions-screen-full')
            .css('display', 'block')
            .trigger('openModal');

    });

});

// Notification modal:
jQuery(document).ready(function($){

    if(typeof($().dialog) === 'undefined') {
        return;
    }

    $('.leyka-wizard-modal').dialog({
        dialogClass: 'wp-dialog leyka-wizard-modal',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: 'center',
            at: 'center',
            of: window
        },
        open: function(){
            var $modal = $(this);
            $('.ui-widget-overlay').bind('click', function(){
                $modal.dialog('close');
            });
        },
        create: function () {
            $('.ui-dialog-titlebar-close').addClass('ui-button');

            var $modal = $(this);
            $modal.find('.button-dialog-close').bind('click', function(){
                $modal.dialog('close');
            });
        }

    });

    $('#cp-documents-sent').dialog('open');

});

// copy2clipboard
jQuery(document).ready(function($){
    
    function copyText2Clipboard(copyText) {
        var $copyBufferInput = $('<input>');
        $("body").append($copyBufferInput);
        $copyBufferInput.val(copyText).select();
        document.execCommand("copy");
        $copyBufferInput.remove();
    }
    
    function collectText2Copy($copyLink) {
        var $clone = $copyLink.parent().clone();
        $clone.find('.copy-link').remove();
        $clone.find('.copy-done').remove();
        
        var text = '';
        var $innerControl = $clone.find('input[type=text], input[type=color], input[type=date], input[type=datetime-local], input[type=month], input[type=email], input[type=number], input[type=search], input[type=range], input[type=search], input[type=tel], input[type=time], input[type=url], input[type=week], textarea');
        
        if($innerControl.length > 0) {
            text = $innerControl.val();
        }
        else {
            text = $clone.text();
        }
        
        return $.trim(text);
    }
    
    function addCopyControls($copyContainer) {
        
        var $copyLink = $('<span>');
        $copyLink.addClass('copy-control');
        $copyLink.addClass('copy-link');
        $copyLink.text(leyka_wizard_common.copy2clipboard);
        $copyContainer.append($copyLink);
        
        var $copyDone = $('<span>');
        $copyDone.addClass('copy-control');
        $copyDone.addClass('copy-done');
        $copyDone.text(leyka_wizard_common.copy2clipboard_done);
        $copyContainer.append($copyDone);
        
    }
    
    $('.leyka-wizard-copy2clipboard').each(function(){
        
        var $formFieldInside = $(this).find('.field-component.field');
        
        if($formFieldInside.length) {
            $(this).removeClass('leyka-wizard-copy2clipboard');
            $formFieldInside.addClass('leyka-wizard-copy2clipboard');
            addCopyControls($formFieldInside);
        }
        else {
            addCopyControls($(this));
        }
        
        $(this).find('.copy-link').click(function(){
            
            var $copyLink = $(this);
            
            var copyText = collectText2Copy($copyLink);
            copyText2Clipboard(copyText);
            
            $copyLink.fadeOut(function(){
                $copyLink.siblings('.copy-done').show();
                
                setTimeout(function(){
                    $copyLink.siblings('.copy-done').hide();
                    $copyLink.show();
                }, 2000);
            });
            
        });
    });
});
// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $cp_error_message = $cp_payment_tryout_field.find('.field-errors'),
        $call_support_link = $cp_payment_tryout_field.find('.call-support');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $call_support_link.click(function(e){

        e.preventDefault();

        $('#leyka-help-chat-message').val(
            $('.current-wizard-title').val() + '\n'
            + 'Раздел: ' + $('.current-section-title').val() + '\n'
            + 'Шаг: ' + $('.current-step-title').val() + '\n\n'
            + 'Ошибка:\n'
            + $cp_error_message.text()
        );
        $('.help-chat-button').click();

    });

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

        if($payment_tryout_button.data('submit-in-process')) {
            return;
        } else {
            $payment_tryout_button.data('submit-in-process', 1);
        }

        // Do a test donation:
        $payment_tryout_button.data('submit-in-process', 0);

        if( !leyka_wizard_cp.cp_public_id ) {

            $cp_error_message.html(leyka_wizard_cp.cp_not_set_up).show();
            return false;

        }

        var widget = new cp.CloudPayments();
        widget.charge({
            language: 'ru-RU',
            publicId: leyka_wizard_cp.cp_public_id,
            description: 'Leyka - payment testing',
            amount: 1.0,
            currency: leyka_wizard_cp.main_currency,
            accountId: leyka_wizard_cp.test_donor_email,
            invoiceId: 'leyka-test-donation'
        }, function(options){ // success callback

            $cp_error_message.html('').hide();
            $call_support_link.hide();

            $payment_tryout_button
                .removeClass('not-tested').hide()
                .siblings('.result.ok').show();

            if( !$cp_payment_tryout_field.find('.do-payment.not-tested').length ) {
                $cp_payment_tryout_field.find('input[name="payment_tryout_completed"]').val(1);
            }

        }, function(reason, options){ // fail callback

            $call_support_link.show();

            $cp_error_message.html(leyka_wizard_cp.cp_donation_failure_reasons[reason] || reason).show();
            $cp_payment_tryout_field.find('.payment-tryout-comment').hide();

        });

    });

});
// CP payment tryout custom setting - END

// Help chat:
jQuery(document).ready(function($){
    
    var $chat = $('.help-chat'),
        $chatButton = $('.help-chat-button');

    if( !$chat.length ) {
        return;
    }

    var $loading = $chat.find('.leyka-loader');

    function disableForm() {
        $chat.find('input[type=text]').prop('disabled', true);
        $chat.find('textarea').prop('disabled', true);
        $chat.find('.button').hide();
    }
    
    function enableForm() {
        $chat.find('input[type=text]').prop('disabled', false);
        $chat.find('textarea').prop('disabled', false);
        $chat.find('.button').show();
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function showOKMessage() {
        $chat.find('.ok-message').show();
        $chat.removeClass('fix-height');
    }

    function hideOKMessage() {
        $chat.find('.ok-message').hide();
        $chat.addClass('fix-height');
    }
    
    function showForm() {
        $chat.find('.form').show();
    }

    function hideForm() {
        $chat.find('.form').hide();
    }

    function validateForm() {
        return true;
    }
    
    function showHelpChat() {
        $chatButton.hide();
        $chat.show();
    }
    
    function hideHelpChat() {
        $chat.hide();
        $chatButton.show();
    }

    $chat.find('.form').submit(function(e) {
        e.preventDefault();
        
        if(!validateForm()) {
            return;
        }

        //hideErrors();
        hideForm();
        showLoading();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            name: $chat.find('#leyka-help-chat-name').val(),
            topic: "Сообщение из формы обратной связи Лейки",
            email: $chat.find('#leyka-help-chat-email').val(),
            text: $chat.find('#leyka-help-chat-message').val(),
            nonce: $chat.find('#leyka_feedback_sending_nonce').val()
        }, null).done(function(response) {
    
            if(response === '0') {
                showOKMessage();
                hideForm();
            } else {
                alert('Ошибка!');
                showForm();
            }

        }).fail(function() {
            showForm();
        }).always(function() {
            hideLoading();
        });
            
    });
    
    $chatButton.click(function(e){
        e.preventDefault();
        showHelpChat();
        hideOKMessage();
        showForm();
    });

    $chat.find('.close').click(function(e){
        e.preventDefault();
        hideHelpChat();
        hideForm();
        showOKMessage();
    });
    
});


// Campaign decoration custom setting:
jQuery(document).ready(function($){
    
    if( !$('#leyka-settings-form-cd-campaign_decoration').length ) {
        return;
    }

    var campaignAttachmentId = 0;
    var $decorationControlsWrap = $('#campaign-decoration');
    var $previewFrame = $('#leyka-preview-frame');
    var $previewIframe = $previewFrame.find('iframe');
    var $loading = $decorationControlsWrap.find('#campaign-decoration-loading');
    var campaignId = $decorationControlsWrap.find('#leyka-decor-campaign-id').val();
    var $selectTemplateControl = $('#leyka_campaign_template-field');
    
    function disableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', true);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', true);
    }
    
    function enableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', false);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', false);
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function reloadPreviewFrame() {
        //$previewIframe.get(0).contentWindow.location.reload(true);
        var previewLocation = $previewIframe.get(0).contentWindow.location;
        var href = previewLocation.href;
        href = href.replace(/&rand=.*/, '');
        href += '&rand=' + Math.random();
        previewLocation.href = href;
    }
    
    $previewIframe.on('load', function(){
        $previewIframe.height($previewIframe.contents().find('body').height() + 10);
        $previewIframe.contents().find('body').addClass('wizard-init-campaign-preview');
    });

    $('#campaign_photo-upload-button').on('click.leyka', function(){

        var frame = wp.media({
            title: 'Выбор фотографии кампании',
            multiple: false
        });
        
        frame.on('select', function(){

            var attachment = frame.state().get('selection').first().toJSON();

            if( !attachment.id ) {
                return;
            }

            disableForm();
            showLoading();
            
            $('#leyka-campaign_thumnail').val(attachment.id);
            
            $.post(leyka.ajaxurl, {
                action: 'leyka_set_campaign_photo',
                attachment_id: attachment.id,
                campaign_id: campaignId,
                nonce: $decorationControlsWrap.find('#set-campaign-photo-nonce').val()
            }, null, 'json')
                .done(function(json) {
        
                    if(typeof json.status !== 'undefined' && json.status === 'error') {
                        alert('Ошибка!');
                        return;
                    }
                    
                    reloadPreviewFrame();
                })
                .fail(function() {
                    alert('Ошибка!');
                })
                .always(function() {
                    hideLoading();
                    enableForm();
                });

        });

        frame.open();

    });
    
    $selectTemplateControl.on('change', function(){
        
        disableForm();
        showLoading();
        
        var template = $(this).val();
        $('#leyka-campaign_template').val(template);
        
        $.post(leyka.ajaxurl, {
            action: 'leyka_set_campaign_template',
            campaign_id: campaignId,
            template: template,
            nonce: $decorationControlsWrap.find('#set-campaign-template-nonce').val()
        }, null, 'json')
            .done(function(json) {
    
                if(typeof json.status !== 'undefined' && json.status === 'error') {
                    alert('Ошибка!');
                    return;
                }
                
                reloadPreviewFrame();
                //setFrameClass();
            })
            .fail(function() {
                alert('Ошибка!');
            })
            .always(function() {
                hideLoading();
                enableForm();
            });            
            
    });
    
    function setFrameClass() {
        $selectTemplateControl.find('option').each(function(i, el){
            $previewFrame.removeClass($(el).val());
        });
        $previewFrame.addClass($selectTemplateControl.val());
    }

    // move next button
    $('.step-submit').insertBefore($('#campaign-decoration-loading'));

});

// Edit permalink:
jQuery(document).ready(function($){

    var $edit_permalink_wrap = $('.leyka-campaign-permalink'),
        $edit_link = $edit_permalink_wrap.find('.inline-edit-slug'),
        $current_slug = $edit_permalink_wrap.find('.current-slug'),
        $edit_form = $edit_permalink_wrap.find('.inline-edit-slug-form'),
        $slug_field = $edit_form.find('.leyka-slug-field'),
        $loading = $edit_permalink_wrap.find('.edit-permalink-loading');

    $edit_link.on('click.leyka', function(e){

        e.preventDefault();

        $current_slug.hide();
        $edit_link.hide();
        $edit_form.show();

    });

    $edit_permalink_wrap.find('.slug-submit-buttons')
        .on('click.leyka', '.inline-reset', function(e){

            e.preventDefault();

            $edit_form.hide();
            $slug_field.val($edit_form.data('slug-original'));

            $edit_link.show();
            $current_slug.show();

        })
        .on('click.leyka', '.inline-submit', function(e){

            e.preventDefault();

            $loading.show();
            $edit_form.hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_edit_campaign_slug',
                campaign_id: $edit_form.data('campaign-id'),
                slug: $slug_field.val(),
                nonce: $edit_form.data('nonce')
            }, null, 'json')
                .done(function(json) {

                    if(typeof json.status === 'undefined') {
                        alert('Ошибка!');
                    } else if(json.status === 'ok' && typeof json.slug !== 'undefined') {

                        $slug_field.val(json.slug);
                        $edit_form.data('slug-original', json.slug);
                        $current_slug.text(json.slug);

                    } else {
                        alert('Ошибка!');
                    }

                }).fail(function(){
                    alert('Ошибка!');
                }).always(function(){

                    $loading.hide();
                    $edit_link.show();
                    $current_slug.show();

                });

        });

});

// Auto-copy campaign shortcode:
jQuery(document).ready(function($){

    var $shortcode_field_wrap = $('.leyka-campaign-shortcode-field'),
        $copy_shortcode_link = $shortcode_field_wrap.siblings('.inline-copy-shortcode'),
        $current_shortcode = $shortcode_field_wrap.siblings('.leyka-current-value');

    $copy_shortcode_link.on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.hide();
        $current_shortcode.hide();
        $shortcode_field_wrap.show();

    });

    $shortcode_field_wrap.find('.inline-reset').on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.show();
        $current_shortcode.show();
        $shortcode_field_wrap.hide();

    });

});

// Highlighted keys in rich edit
jQuery(document).ready(function($){
    
    $('.type-rich_html').each(function(){
        initRichHTMLTagsReplace($, $(this));
    });
    
});

function initRichHTMLTagsReplace($, $controlContainer) {

    var isInitEditDocsDone = false;
    var isEditContentLoadDone = false;
    var isEditFieldTouched = false;
    var originalDocHTML = null;
    var $frameBody = null;
    var isSkipDOMSubtreeModified = false;
    var keysValues = [];
    
    function showRestoreOriginalDocHTMLLink() {
        
        var $link = $controlContainer.find('.restore-original-doc');
        
        if(!$link.length) {
            
            $link = $('<a>Вернуть первоначальный текст</a>')
                .attr('href', '#')
                .addClass("inner")
                .addClass("restore-original-doc");
            
            $controlContainer.find('.wp-editor-wrap').append($link);
        }
        
        $link.unbind('click');
        $link.click(restoreOriginalDocHTML);
        $link.show();
        
    }
    
    function restoreOriginalDocHTML() {
        
        if(originalDocHTML) {
            $frameBody.html(originalDocHTML);
        }
        
        $controlContainer.find('.restore-original-doc').hide();
        replaceKeysWithHTML();
        handleChangeEvents();
        $controlContainer.find('.restore-original-doc').hide(); // hack for FF
        
        return false;
    }
    
    function replaceKeysValues(keysValues) {
        for(var i in keysValues[0]) {
            var limit = 100;
            while($frameBody.html().search(keysValues[0][i]) > -1 && limit > 0) {
                limit -= 1;
                var $replacement = $("<span>");
                $replacement.addClass("leyka-doc-key-wrap");
                $replacement.addClass("leyka-doc-key");
                $replacement.attr('data-key', keysValues[0][i].replace("#", "+"));
                $replacement.attr('data-original-value', keysValues[1][i]);
                $replacement.html(keysValues[1][i]);
                $frameBody.html( $frameBody.html().replace(keysValues[0][i], "<span id='key-replacement'> </span>") );
                $frameBody.find('#key-replacement').replaceWith($replacement);
            }
        }
    }
    
    function replaceKeysWithHTML() {
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        
        originalDocHTML = $frameBody.html();
        
        if($controlContainer.find('#leyka_pd_terms_text-field').length > 0 || $controlContainer.find('#leyka_person_pd_terms_text-field').length > 0) {
            keysValues = leykaRichHTMLTags.pdKeys;
        }
        else {
            keysValues = leykaRichHTMLTags.termsKeys;
        }
        
        replaceKeysValues(keysValues);
        
        //$frameBody.find(".leyka-doc-key").each(function(){
        //    $(this).data('original-value', $(this).text());
        //});

    }
    
    function handleChangeEvents() {
        
        $frameBody.unbind("click");
        $frameBody.on('click', function(){
            isEditFieldTouched = true;
        });
        
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.bind("DOMSubtreeModified", function(){
            
            if(!isEditContentLoadDone || !originalDocHTML || !isEditFieldTouched) {
                return;
            }
        
            showRestoreOriginalDocHTMLLink();
        });
        
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").bind("DOMSubtreeModified", function(){
            $(this).removeClass("leyka-doc-key");
            if($(this).text() == $(this).data('original-value') && !isSkipDOMSubtreeModified) {
                $(this).addClass("leyka-doc-key");
                isSkipDOMSubtreeModified = true;
            }
            else {
                isSkipDOMSubtreeModified = false;
            }
        });
        
    }
    
    function initEditDocs($iframe) {
        if(isInitEditDocsDone) {
            console.log('initEditDocs already done');
            return;
        }
        isInitEditDocsDone = true;
        console.log('initEditDocs...');
        
        var $frameDocument = $iframe.contents();
        
        $frameDocument.find('body').bind("DOMSubtreeModified", function(){
            if($frameDocument.find('body p').length > 0) {
                if(isEditContentLoadDone) {
                    return;
                }
                isEditContentLoadDone = true;
                
                $frameBody = $frameDocument.find('body');
                restoreOriginalDocHTML();
            }
        });
        
    }
    
    function tryInitEditDocs($tinyMCEContainer) {

        var $iframe = $tinyMCEContainer.find('iframe');
        if($iframe.length) {
            $iframe.on('load', function(){
                initEditDocs($(this));
            });
        }

    }
    
    $('.step-next.button, input[name=leyka_settings_beneficiary_submit], input[name=leyka_settings_email_submit]').click(function(e){
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find('.leyka-doc-key-wrap').each(function(index, el){
            if($(el).hasClass('leyka-doc-key')) {
                $(el).replaceWith($(el).data('key').replace("+", "#"));
            }
            else {
                $(el).replaceWith($(el).html());
            }
        });
        //e.preventDefault();
    });
    
    $controlContainer.find('.wp-editor-container').bind("DOMSubtreeModified", function(){
        tryInitEditDocs($(this));
    });
    tryInitEditDocs($controlContainer.find('.wp-editor-container'));
    
}


// show-hide available tags
jQuery(document).ready(function($){
    $('.hide-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $(this).closest('.field-component').find('.show-available-tags').show();
        $(this).closest('.field-component').find('.placeholders-help').hide();
    });
    
    $('.show-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $(this).closest('.field-component').find('.hide-available-tags').show();
        $(this).closest('.field-component').find('.placeholders-help').show();
    });
});


// org actual address
jQuery(document).ready(function($){
    
    var $orgActualAddressInput = $('#leyka_org_actual_address-field');
    var $orgActualAddressCheckbox = $('#leyka_org_actual_address_differs-field');
    var $orgActualAddressWrapper = $('#leyka_org_actual_address-wrapper');

    var orgActualAddress = $orgActualAddressInput.val();
    orgActualAddress = $.trim(orgActualAddress);
    
    if(!orgActualAddress) {
        $orgActualAddressWrapper.hide();
        $orgActualAddressCheckbox.prop('checked', false);
    }
    else {
        $orgActualAddressCheckbox.prop('checked', true);
    }
    
    $orgActualAddressCheckbox.change(function(){
        if($(this).prop('checked')) {
            $orgActualAddressWrapper.show();
            $orgActualAddressInput.val(orgActualAddress);
        }
        else {
            $orgActualAddressWrapper.hide();
            orgActualAddress = $orgActualAddressInput.val();
            $orgActualAddressInput.val('');
        }
    });
    
});

// Yandex.Kassa shopPassword generator:
jQuery(document).ready(function($){

    var $genBtn = $('#yandex-generate-shop-password');
    
    if( !$genBtn.length ) {
        return;
    }
    
    var $stepSubmit = $('.step-submit');
    $stepSubmit.hide();
    
    $genBtn.click(function(){

        var password = leyka_make_password(10),
            $block = $genBtn.closest('.enum-separated-block');

        $genBtn.hide();
        $block.find('.caption').css('display', 'unset');
        $block.find('.body b').css('display', 'unset').text(password);
        $block.find('input[name=leyka_yandex_shop_password]').val(password);
        $stepSubmit.show();
        
        $(this).closest('.body').removeClass('no-password');

    });

});
// Yandex.Kassa shopPassword generator - END

// Yandex.Kassa payment tryout:
jQuery(document).ready(function($){

    var $genBtn = $('#yandex-make-live-payment'),
        $loading = $('.yandex-make-live-payment-loader');

    if( !$genBtn.length ) {
        return;
    }

    leykaYandexPaymentData.leyka_success_page_url = window.location.href;

    $genBtn.click(function(){

        $loading.show();
        $genBtn.prop('disabled', true);

        $.post(leyka.ajaxurl, leykaYandexPaymentData, null, 'json')
            .done(function(json) {
                
                console.log(json);

                if(typeof json.status === 'undefined') {
                    alert('Ошибка!');
                } else if(json.status === 0 && json.payment_url) {
                    window.location.href = json.payment_url;
                } else {
                    alert('Ошибка!');
                }

            }).fail(function(){
                alert('Ошибка!');
            }).always(function(){
                $loading.hide();
                $genBtn.prop('disabled', false);
            });
            
    });

});
// Yandex.Kassa payment tryout - END
/** Admin utilities & tools */

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}

//polyfill for unsupported Number.isInteger
//https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger
Number.isInteger = Number.isInteger || function(value) {
    return typeof value === "number" &&
        isFinite(value) &&
        Math.floor(value) === value;
};

/** @var e JS keyup/keydown event */
function leyka_is_digit_key(e, numpad_allowed) {

    if(typeof numpad_allowed == 'undefined') {
        numpad_allowed = true;
    } else {
        numpad_allowed = !!numpad_allowed;
    }

    if( // Allowed special keys
        e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13 || // Backspace, delete, tab, enter
        (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    ) {
        return true;
    }

    if(numpad_allowed) {
        if( !e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57 ) {
            return true;
        } else {
            return e.keyCode >= 96 && e.keyCode <= 105;
        }
    } else {
        return !(e.shiftKey || e.keyCode < 48 || e.keyCode > 57);
    }

}

/** @var e JS keyup/keydown event */
function leyka_is_special_key(e) {

    return ( // Allowed special keys
        e.keyCode === 9 || // Tab
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    );

}

function leyka_make_password(pass_length) {

    let text = '',
        possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for(let i = 0; i < parseInt(pass_length); i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;

}

/** Get random latin-numeric string with given length. */
function leyka_get_random_string(length = 6) {
    return Array(length + 1).join((Math.random().toString(36)+'00000000000000000').slice(2, 18)).slice(0, length);
}

function leyka_validate_donor_name(name_string) {
    return !name_string.match(/[ !@#$%^&*()+=\[\]{};:"\\|,<>\/?]/);
}

// Plugin metaboxes rendering:
function leyka_support_metaboxes(metabox_area) {

    if(typeof postboxes === 'undefined') {
        console.log('Leyka error: trying to support metaboxes for "'+metabox_area+'" area, but there are no "postboxes" var.');
        return false;
    }

    // console.log('Metaboxes supported for: ', metabox_area);

    jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed'); // Close postboxes that should be closed
    postboxes.add_postbox_toggles(metabox_area);

}

/** Check if UI widget is available. Widget is looked in $.ui by default. */
function leyka_ui_widget_available(widget = '', object = null) {

    if(object === null && typeof jQuery.ui !== 'undefined') {
        object = jQuery.ui;
    } else if(object === null || typeof object !== 'object') {
        return false;
    }

    return widget.length ? typeof typeof object[widget] !== 'undefined' : typeof object !== 'undefined';

}