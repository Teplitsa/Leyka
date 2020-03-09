/** Scripts **/
jQuery(document).ready(function($){

    // appearance 
    if( $('div.engb').length == 0 )
        return;

    // appearance settings 
    var engb = $('div.engb'),
        engbDelay = engb.data('delay');

    // show
    if(engbDelay.hasOwnProperty('time')) 
    {
        var engbTimeout = parseInt(engbDelay['time']);

        setTimeout( function() {
            engb.addClass('engb--visible');
        }, engbTimeout * 1000 );
        
    }
    else if(engbDelay.hasOwnProperty('scroll')) 
    {
    
        var engbScrollPercent = parseInt(engbDelay['scroll']);
            currentPageH = $('body').height(),
            windowH = $(window).height(),
            engbScroll = currentPageH * engbScrollPercent / 100;

        if( currentPageH < windowH *2 )
        {
            // fallback to time
            setTimeout( function() {
                engb.addClass('engb--visible');
            }, 20000 );
        }
        else
        {
            $(window).scroll(function(){

                if(engb.hasClass('engb--closed'))
                    return;

                if( $(window).scrollTop() > engbScroll ) {
                   engb.addClass('engb--visible');
                }
            });
        }
    }


    // hide
    engb.on('click', '.engb-close-trigger', function(e) {

        e.preventDefault();

        engb.removeClass('engb--visible');
        engb.addClass('engb--closed');

        var engbRemember = engb.data('remember_close');

        if( engbRemember.length == 0 || engbRemember == 'none' )
            return;

        // do remember here 
        if( engbRemember == 'session') 
        {
            $.cookie('leyka_engb_close', 1, { path: '/' });
        }
        else
        {  
            var time = 1;
            
            if(engbRemember == 'week' ) 
            {
                time = 7;
            }
            else if(engbRemember == 'forever' ) 
            {
                time = 1000;
            }

            $.cookie('leyka_engb_close', 1, { expires: time, path: '/' });
        }

    });

});
