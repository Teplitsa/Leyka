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

jQuery(document).ready(function($){
	$('.inpage-card__toggle-excerpt-links').on('click', 'a', function(e){
        e.preventDefault();
		//console.log($(this).closest('.inpage-card__excerpt'));
		$(this).closest('.inpage-card__excerpt').toggleClass('expand');
	});
});