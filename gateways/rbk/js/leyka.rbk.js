var $ = jQuery.noConflict();
var leyka_rbk_gateway = {
    run: function () {
        $(window).on('leyka_submission_form_data', function (type, response) {
            $('.leyka-pf--active').removeClass('leyka-pf--active');
            eval(response.script);
        });
    }
};


$(document).ready(function () {
    leyka_rbk_gateway.run();
});