/*
 * Class to manipulate donation form from bottom
 */

window.LeykaGUIBottom = function($) {
    this.$ = $;
};

window.LeykaGUIBottom.prototype = {

    bindEvents: function() {

        var self = this; var $ = self.$;

        $('.leyka-js-open-form-bottom').on('click', function(e){

            e.preventDefault();

            var $this = $(this),
                $bottomForm = $this.closest('.leyka-pf-bottom');

            $('#'+$bottomForm.attr('data-target'))
                .find('.amount__figure input.leyka_donation_amount')
                .val( $this.parents('.leyka-pf-bottom').find('input[name="leyka_temp_amount"]').val() );

            /** @todo Sync the amount value & the range control. */
            $bottomForm.leykaForm('openFromBottom');

        });

    }

};

jQuery(document).ready(function($){
    
    leykaGUIBottom = new LeykaGUIBottom($);
    leykaGUIBottom.bindEvents();
    
}); //jQuery
