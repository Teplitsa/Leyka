/*
 * Class to manipulate donation form from campaign carda
 */

window.LeykaGUICard = function($) {
    this.$ = $;
};

window.LeykaGUICard.prototype = {

    bindEvents: function() {
        var self = this; var $ = self.$;
    }

};

jQuery(document).ready(function($){

    leykaGUICard = new LeykaGUICard($);
    leykaGUICard.bindEvents();

}); //jQuery