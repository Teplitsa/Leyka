/**
 * easyModal.js v1.3.2
 * A minimal jQuery modal that works with your CSS.
 * Author: Flavius Matis - http://flaviusmatis.github.com/
 * URL: https://github.com/flaviusmatis/easyModal.js
 *
 * Copyright 2012, Flavius Matis
 * Released under the MIT license.
 * http://flaviusmatis.github.com/license.html
 */

(function(d,e){var c=function(a,d,b){var c;return function(){var e=this,f=arguments;c?clearTimeout(c):b&&a.apply(e,f);c=setTimeout(function(){b||a.apply(e,f);c=null},d||100)}};jQuery.fn[e]=function(a){return a?this.bind("resize",c(a)):this.trigger(e)}})(jQuery,"smartModalResize");
(function(d){var e={init:function(c){c=d.extend({top:"auto",left:"auto",autoOpen:!1,overlayOpacity:.5,overlayColor:"#000",overlayClose:!0,overlayParent:"body",closeOnEscape:!0,closeButtonClass:".close",transitionIn:"",transitionOut:"",onOpen:!1,onClose:!1,zIndex:function(){return function(a){return-Infinity===a?0:a+1}(Math.max.apply(Math,d.makeArray(d("*").map(function(){return d(this).css("z-index")}).filter(function(){return d.isNumeric(this)}).map(function(){return parseInt(this,10)}))))},updateZIndexOnOpen:!0,
    hasVariableWidth:!1},c);return this.each(function(){var a=c,e=d('<div class="lean-overlay"></div>'),b=d(this);e.css({display:"none",position:"fixed","z-index":a.updateZIndexOnOpen?0:a.zIndex(),top:0,left:0,height:"100%",width:"100%",background:a.overlayColor,opacity:a.overlayOpacity,overflow:"auto"}).appendTo(a.overlayParent);b.css({display:"none",position:"fixed","z-index":a.updateZIndexOnOpen?0:a.zIndex()+1,left:-1<parseInt(a.left,10)?a.left+"px":"50%",top:-1<parseInt(a.top,10)?a.top+"px":"50%"});
    b.bind("openModal",function(){var c=a.updateZIndexOnOpen?a.zIndex():parseInt(e.css("z-index"),10),d=c+1;""!==a.transitionIn&&""!==a.transitionOut&&b.removeClass(a.transitionOut).addClass(a.transitionIn);b.css({display:"block","margin-left":(-1<parseInt(a.left,10)?0:-(b.outerWidth()/2))+"px","margin-top":(-1<parseInt(a.top,10)?0:-(b.outerHeight()/2))+"px","z-index":d});e.css({"z-index":c,display:"block"});if(a.onOpen&&"function"===typeof a.onOpen)a.onOpen(b[0])});b.bind("closeModal",function(){""!==
    a.transitionIn&&""!==a.transitionOut?(b.removeClass(a.transitionIn).addClass(a.transitionOut),b.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",function(){b.css("display","none");e.css("display","none")})):(b.css("display","none"),e.css("display","none"));if(a.onClose&&"function"===typeof a.onClose)a.onClose(b[0])});e.click(function(){a.overlayClose&&b.trigger("closeModal")});d(document).keydown(function(c){a.closeOnEscape&&27===c.keyCode&&b.trigger("closeModal")});
    d(window).smartModalResize(function(){a.hasVariableWidth&&b.css({"margin-left":(-1<parseInt(a.left,10)?0:-(b.outerWidth()/2))+"px","margin-top":(-1<parseInt(a.top,10)?0:-(b.outerHeight()/2))+"px"})});b.on("click",a.closeButtonClass,function(a){b.trigger("closeModal");a.preventDefault()});a.autoOpen&&b.trigger("openModal")})}};d.fn.easyModal=function(c){if(e[c])return e[c].apply(this,Array.prototype.slice.call(arguments,1));if("object"===typeof c||!c)return e.init.apply(this,arguments);d.error("Method "+
    c+" does not exist on jQuery.easyModal")}})(jQuery);