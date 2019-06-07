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
            $loading = $field_wrapper.find('.leyka-loader');

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_usage_stats_y',
            nonce: $field_wrapper.find(':input[name="usage_stats_y"]').val()
        };
        
        $loading.css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $loading.closest('.loader-wrap').remove();
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
                            alert('Ошибка!');
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert('Ошибка!');
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
            });
    });
});