// "How to setup cron" modal:
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
        
        $loading.show();
        // $loading.css('display', 'block');
        // $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {

                        $loading.closest('.loading-indicator-wrap').find('.ok-icon').show();
                        // var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $loading.remove();
                        // $indicatorWrap.find('.ok-icon').show();
                        setTimeout(function(){
                            $field_wrapper.closest('.send-plugin-stats-invite').fadeOut('slow');
                        }, 1000);

                    } else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        } else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.hide();
            });
    });

});

// banner
jQuery(document).ready(function($){
    $('.banner-wrapper .close').on('click.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        $this.closest('.banner-wrapper').remove();

        $.post(
            leyka.ajaxurl, {
                action: 'leyka_close_dashboard_banner',
                banner_id: $this.parents('.banner-inner').data('banner-id'),
                /** @todo Add nonce */
            },
            null, 'json'
        );

    });
});
