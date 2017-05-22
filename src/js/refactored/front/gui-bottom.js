window.LeykaGUIBottom = function($) {
    this.$ = $;
}

window.LeykaGUIBottom.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
        
        $('.leyka-js-open-form-bottom').on('click', function(e){
            e.preventDefault();

            $(this).closest('.leyka-pf-bottom').leykaForm('openFromBottom');
        });
        
    }

}

jQuery(document).ready(function($){
    
    leykaGUIBottom = new LeykaGUIBottom($);
    leykaGUIBottom.bindEvents();
    
}); //jQuery
