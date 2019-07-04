/** Admin utilities & tools */

function make_password(len) {

    var text = '',
        possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#_%$-";

    for(var i = 0; i < len; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;

}

// Plugin metaboxes rendering:
function leyka_support_metaboxes(metabox_area) {

    jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed'); // Close postboxes that should be closed
    postboxes.add_postbox_toggles(metabox_area);

}