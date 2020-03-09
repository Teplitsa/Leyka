/** Scripts **/
jQuery(document).ready(function($){

    var delayTriggerBox = $('#extension_engagement_banner_delay_type');

    if( delayTriggerBox.length > 0 ) {

        // init 
        var dtbSelection = delayTriggerBox
                    .find("input[name='leyka_engagement_banner_delay_type']:checked")
                    .val();

        if(dtbSelection == 'time') 
        {
            $('#extension_engagement_banner_time_amount').addClass('engb-active');
        }
        else 
        {
            $('#extension_engagement_banner_scroll_amount').addClass('engb-active');
        }

        // on change
        delayTriggerBox
            .find("input[name='leyka_engagement_banner_delay_type']")
            .on('change', function(e){

                var selection = $(this).val();

                $('.engb-active').removeClass('engb-active');

                if(selection == 'time') 
                {
                    $('#extension_engagement_banner_time_amount').addClass('engb-active');
                }
                else 
                {
                    $('#extension_engagement_banner_scroll_amount').addClass('engb-active');
                }
            });
    }

    var showTriggerBox = $('#extension_engagement_banner_show_on_pages');

    if(showTriggerBox.length > 0 ) {
        // init 
        var stbSelection = delayTriggerBox
                    .find("input[name='leyka_engagement_banner_show_on_pages']:checked")
                    .val();

        if( stbSelection == 'onlyhome') {
            $('#leyka_engagement_banner_show_on_home-show-field').attr('checked', 'checked');
            $('#extension_engagement_banner_show_on_home').find('input').attr('readonly', 'readonly');
            $('#extension_engagement_banner_show_on_home').addClass('readonly');
        }

        showTriggerBox
            .find("input[name='leyka_engagement_banner_show_on_pages']")
            .on('change', function(e){
                var selection = $(this).val();

                if(selection == 'onlyhome') 
                {
                    $('#leyka_engagement_banner_show_on_home-show-field').attr('checked', 'checked');
                    $('#extension_engagement_banner_show_on_home').find('input').attr('disabled',true);
                    $('#extension_engagement_banner_show_on_home').addClass('readonly');
                }
                else 
                {
                    $('#extension_engagement_banner_show_on_home').find('input').removeAttr('disabled');
                    $('#extension_engagement_banner_show_on_home').removeClass('readonly');
                }

            })
    }

    // multiselect 
    $('.engb-multiselect').select2({
        placeholder: engb.placeholder,
        multiple: true
    });
    
});