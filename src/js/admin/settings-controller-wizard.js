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

    $('.zoom-screen').on('click', function(e){

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