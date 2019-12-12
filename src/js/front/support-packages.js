/** Donor's account frontend */

jQuery(document).ready(function($){
    var $siteContent = $('#site_content');
    var $overlay = $('.leyka-ext-sp-activate-feature-overlay');

    if($overlay.length) {
        $siteContent.addClass('leyka-ext-sp-site-content');
        let $overlayFirst = $overlay.first();
        $overlayFirst.appendTo($siteContent);
        //$siteContent.css('padding-bottom', $overlayFirst.height() + 'px');
    }
});