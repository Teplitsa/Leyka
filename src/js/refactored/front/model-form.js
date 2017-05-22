(function( $ ) {
    
    var amountMin = 1, //temp - take it from options
        amountMax = 30000;
    
    var methods = {
        'defaults': {
            'color': 'green'
        },
        'open': open,
        'close': close,
        'openFromBottom': openFromBottom,
        'init': init
    };
    
    function init(options) {
        initAmountSync();
        bindEvents();
    }
    
    /* amount sync */
    function initAmountSync() {
        $('.amount__figure input').each(function(){
            var val = parseInt($(this).val());

            if(!Number.isInteger(val) || val < amountMin || val > amountMax){ //correct this
                val = 500;
            }

            $(this).val(val);
            $(this).parents('.step__fields').find('.amount_range').find('input').val(val);

            //sync with bottom
            var formId = $(this).closest('.leyka-pf').attr('id');
            $('div[data-target = "'+formId+'"]').find('input').val(val);
        });
    }
    
    function syncFigure() {
        var val = $(this).val();
        $(this).parents('.step__fields').find('.amount__figure').find('input').val(val);
        $(this).parents('.step__fields').removeClass('invalid');
    }
    
    function syncRange() {
        var val = $(this).val();
        $(this).parents('.step__fields').find('.amount_range').find('input').val(val);
        $(this).parents('.step__fields').removeClass('invalid');
    }
    
    /* event handlers */
    function bindEvents() {
        //sync of amount field
        $('.amount_range input').on('input change', syncFigure);
        $('.amount__figure input').on('input change', syncRange);
    }
    
    /* open/close form */
    function open() {
        $(this).addClass('leyka-pf--active');
    }
    
    function openFromBottom() {
        
        var formId = $(this).attr('data-target'),
            amount = parseInt($(this).find('input').val()),
            form = $('#'+formId);
        
        //copy amount if it's correct
        if(Number.isInteger(amount) && amount >= amountMin && amount <= amountMax) {
            form.find('.amount__figure input').val(amount);
            form.find('.amount_range input').val(amount);
        }

        //reset active steps
        form.find('.step').removeClass('step--active');
        form.find('.step--amount').addClass('step--active');

        //open form
        form.addClass('leyka-pf--active');
    }

    function close() {
        
        var pf = $(this);

        if(pf.hasClass('leyka-pf--oferta-open')){ //close only oferta
            pf.removeClass('leyka-pf--oferta-open');

        }
        else { //close module
            pf.removeClass('leyka-pf--active');

        }
    }

    $.fn.leykaForm = function(methodOrOptions) {
        if ( methods[methodOrOptions] ) {
            return methods[ methodOrOptions ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist on jQuery.leykaForm' );
        }    
    }
    
}( jQuery ));
