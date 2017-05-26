(function( $ ) {
    
    var amountMin = 1, //temp - take it from options
        amountMax = 30000,
        amountIconMarks = [25, 50, 75],
        inputRangeWidth = 200,
        inputRangeButtonRadius = 14;
    
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
    
    function getAmountPercent($rangeInput) {
        var val = $rangeInput.val();
        var min, max;
        
        try {
            min = parseInt($rangeInput.attr('min'));
            max = parseInt($rangeInput.attr('max'));
        }
        catch(e) {
            min = 0;
            max = 0;
        }
        
        var amountIconIndex = 1;
        var percent = 0;
        if(max) {
            percent = 100 * (val - min) / (max - min);
        }
        return percent;
    }
    
    function syncAmountIcon() {
        var percent = getAmountPercent($(this));

        var amountIconIndex = 1;
        for(var i in amountIconMarks) {
            rangePercent = amountIconMarks[i];
            if(percent >= rangePercent) {
                amountIconIndex = parseInt(i) + 2;
            }
        }
        
        var $svgIcon = $('.amount__icon .svg-icon');
        
        // set icon class
        $svgIcon.find('use').attr("xlink:href", "#icon-money-size" + amountIconIndex);
        
        // set size class
        $svgIcon.addClass('icon-money-size' + amountIconIndex);
        if(amountIconIndex != 1) {
            $svgIcon.removeClass('icon-money-size1')
        }
        for(var i in amountIconMarks) {
            var size = parseInt(i) + 2;
            if(amountIconIndex != size) {
                $svgIcon.removeClass('icon-money-size' + size);
            }
        }
    }
    
    function syncCustomRangeInput() {
        var percent = getAmountPercent($(this));
        var leftOffset = (inputRangeWidth - 2 * inputRangeButtonRadius) * percent / 100;
        $('.range-circle').css({'left': (leftOffset) + 'px'});
        $('.range-color-wrapper').width(leftOffset + inputRangeButtonRadius);
    }
    
    /* event handlers */
    function bindEvents() {
        //sync of amount field
        $('.amount_range input').on('input change', syncFigure);
        $('.amount__figure input').on('input change', syncRange);
        $('.amount_range input').on('input change', syncAmountIcon);
        $('.amount_range input').on('input change', syncCustomRangeInput);
        
        $('.amount__figure')
        .on('focus', 'input', function(){
            $(this).parents('.amount__figure').addClass('focus');
        })
        .on('blur', 'input', function(){
            $(this).parents('.amount__figure').removeClass('focus');
        });
    }
    
    /* open/close form */
    function open() {
        $(this).addClass('leyka-pf--active');
        $('.amount_range input').change(); // sync coins pic
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
