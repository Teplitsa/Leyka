/** Donor's account frontend */

jQuery(document).ready(function($){
    var $siteContent = $('#site_content');
    if(!$siteContent.length) {
		$siteContent = $('#content');
    }

    if(!$siteContent.length) {
		$siteContent = $('#site-content');
    }

    var $overlay = $('.leyka-ext-sp-activate-feature-overlay');
    var $sp = $('.leyka-ext-support-packages');

    if(!$sp.length) {
    	return;
    }

    if(!$sp.closest('body.page').length && !$sp.closest('body.single').length) {
        return;
    }

    if($overlay.length && $siteContent.length) {
        $siteContent.addClass('leyka-ext-sp-site-content');
        let $overlayFirst = $overlay.first();
        $overlayFirst.appendTo($siteContent);
        $overlayFirst.css('display', 'block');
        overlayMaxPart = 0.7;

        var paddingBottom = $overlayFirst.height();
        if($overlayFirst.height() > $siteContent.height() * overlayMaxPart) {
        	paddingBottom *= overlayMaxPart;
        }
        $siteContent.css('padding-bottom', paddingBottom + 'px');
    }

    function renderActivateButton($parentSp, $activePackage) {
    	var hasSelectedPackages = $parentSp.find('.leyka-ext-sp-card.active').length > 0;
    	var $btn = $parentSp.closest('.leyka-ext-sp-activate-feature').find('.leyka-ext-sp-subscribe-action');

    	if(hasSelectedPackages) {
    		$btn.addClass('active');
    	}
    	else {
    		$btn.removeClass('active');
    	}

    	var href = $btn.attr('href');
    	href = href.split('#')[0];
    	href += "#leyka-activate-package|";

    	if($activePackage) {
    		href += $activePackage.data('amount_needed');
    	}
    	console.log(href);
    	$btn.attr('href', href);
    }

    $('.leyka-ext-sp-subscribe-action').on('click', function(e){
		return $(this).hasClass('active');
    });

    $sp.on('click', '.leyka-activate-package-link', function(e) {
    	e.stopPropagation();
    	return true;
    });

    $sp.on('click', '.leyka-ext-sp-card', function(e) {
    	e.preventDefault();
    	var $parentSp = $(this).closest('.leyka-ext-support-packages');

    	if(!$parentSp.closest('.leyka-ext-sp-activate-feature').length) {
    		return;
    	}

    	$parentSp.find('.leyka-ext-sp-card').removeClass('active');
    	$(this).addClass('active');

    	renderActivateButton($parentSp, $(this));
    });

    if($sp.closest('.leyka-ext-sp-activate-feature').length) {
    	renderActivateButton($sp, null);
    }
});

function leyka_ext_sp_init_locked_content_icons($){
    $('.leyka-ext-sp-locked-content .entry-title').each(function(i, el){
        var $lockedIcon = $('<img />')
            .attr('src', leyka.ext_sp_article_locked_icon)
            .addClass('leyka-ext-sp-post-locked');

        $(this).append($lockedIcon);
    });
}

jQuery(window).load(function() {
    leyka_ext_sp_init_locked_content_icons(jQuery);
});