window.LeykaGUICard = function($) {
    this.$ = $;
}

window.LeykaGUICard.prototype = {
        
    bindEvents: function() {
        var self = this; var $ = self.$;
    }

}

jQuery(document).ready(function($){
    
    leykaGUICard = new LeykaGUICard($);
    leykaGUICard.bindEvents();
    
}); //jQuery
