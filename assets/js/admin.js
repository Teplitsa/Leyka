/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));

/*!
 * dist/jquery.inputmask.min
 * https://github.com/RobinHerbots/Inputmask
 * Copyright (c) 2010 - 2021 Robin Herbots
 * Licensed under the MIT license
 * Version: 5.0.6-beta.40
 */
!function webpackUniversalModuleDefinition(root,factory){if("object"==typeof exports&&"object"==typeof module)module.exports=factory(require("jquery"));else if("function"==typeof define&&define.amd)define(["jquery"],factory);else{var a="object"==typeof exports?factory(require("jquery")):factory(root.jQuery);for(var i in a)("object"==typeof exports?exports:root)[i]=a[i]}}(this,function(__WEBPACK_EXTERNAL_MODULE__10__){return modules=[function(module){module.exports=JSON.parse('{"BACKSPACE":8,"BACKSPACE_SAFARI":127,"DELETE":46,"DOWN":40,"END":35,"ENTER":13,"ESCAPE":27,"HOME":36,"INSERT":45,"LEFT":37,"PAGE_DOWN":34,"PAGE_UP":33,"RIGHT":39,"SPACE":32,"TAB":9,"UP":38,"X":88,"CONTROL":17,"KEY_229":229}')},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.caret=caret,exports.determineLastRequiredPosition=determineLastRequiredPosition,exports.determineNewCaretPosition=determineNewCaretPosition,exports.getBuffer=getBuffer,exports.getBufferTemplate=getBufferTemplate,exports.getLastValidPosition=getLastValidPosition,exports.isMask=isMask,exports.resetMaskSet=resetMaskSet,exports.seekNext=seekNext,exports.seekPrevious=seekPrevious,exports.translatePosition=translatePosition;var _validationTests=__webpack_require__(3),_validation=__webpack_require__(4);function caret(input,begin,end,notranslate,isDelete){var inputmask=this,opts=this.opts,range;if(void 0===begin)return"selectionStart"in input&&"selectionEnd"in input?(begin=input.selectionStart,end=input.selectionEnd):window.getSelection?(range=window.getSelection().getRangeAt(0),range.commonAncestorContainer.parentNode!==input&&range.commonAncestorContainer!==input||(begin=range.startOffset,end=range.endOffset)):document.selection&&document.selection.createRange&&(range=document.selection.createRange(),begin=0-range.duplicate().moveStart("character",-input.inputmask._valueGet().length),end=begin+range.text.length),{begin:notranslate?begin:translatePosition.call(this,begin),end:notranslate?end:translatePosition.call(this,end)};if(Array.isArray(begin)&&(end=this.isRTL?begin[0]:begin[1],begin=this.isRTL?begin[1]:begin[0]),void 0!==begin.begin&&(end=this.isRTL?begin.begin:begin.end,begin=this.isRTL?begin.end:begin.begin),"number"==typeof begin){begin=notranslate?begin:translatePosition.call(this,begin),end=notranslate?end:translatePosition.call(this,end),end="number"==typeof end?end:begin;var scrollCalc=parseInt(((input.ownerDocument.defaultView||window).getComputedStyle?(input.ownerDocument.defaultView||window).getComputedStyle(input,null):input.currentStyle).fontSize)*end;if(input.scrollLeft=scrollCalc>input.scrollWidth?scrollCalc:0,input.inputmask.caretPos={begin:begin,end:end},opts.insertModeVisual&&!1===opts.insertMode&&begin===end&&(isDelete||end++),input===(input.inputmask.shadowRoot||input.ownerDocument).activeElement)if("setSelectionRange"in input)input.setSelectionRange(begin,end);else if(window.getSelection){if(range=document.createRange(),void 0===input.firstChild||null===input.firstChild){var textNode=document.createTextNode("");input.appendChild(textNode)}range.setStart(input.firstChild,begin<input.inputmask._valueGet().length?begin:input.inputmask._valueGet().length),range.setEnd(input.firstChild,end<input.inputmask._valueGet().length?end:input.inputmask._valueGet().length),range.collapse(!0);var sel=window.getSelection();sel.removeAllRanges(),sel.addRange(range)}else input.createTextRange&&(range=input.createTextRange(),range.collapse(!0),range.moveEnd("character",end),range.moveStart("character",begin),range.select())}}function determineLastRequiredPosition(returnDefinition){var inputmask=this,maskset=this.maskset,$=this.dependencyLib,buffer=_validationTests.getMaskTemplate.call(this,!0,getLastValidPosition.call(this),!0,!0),bl=buffer.length,pos,lvp=getLastValidPosition.call(this),positions={},lvTest=maskset.validPositions[lvp],ndxIntlzr=void 0!==lvTest?lvTest.locator.slice():void 0,testPos;for(pos=lvp+1;pos<buffer.length;pos++)testPos=_validationTests.getTestTemplate.call(this,pos,ndxIntlzr,pos-1),ndxIntlzr=testPos.locator.slice(),positions[pos]=$.extend(!0,{},testPos);var lvTestAlt=lvTest&&void 0!==lvTest.alternation?lvTest.locator[lvTest.alternation]:void 0;for(pos=bl-1;lvp<pos&&(testPos=positions[pos],(testPos.match.optionality||testPos.match.optionalQuantifier&&testPos.match.newBlockMarker||lvTestAlt&&(lvTestAlt!==positions[pos].locator[lvTest.alternation]&&1!=testPos.match.static||!0===testPos.match.static&&testPos.locator[lvTest.alternation]&&_validation.checkAlternationMatch.call(this,testPos.locator[lvTest.alternation].toString().split(","),lvTestAlt.toString().split(","))&&""!==_validationTests.getTests.call(this,pos)[0].def))&&buffer[pos]===_validationTests.getPlaceholder.call(this,pos,testPos.match));pos--)bl--;return returnDefinition?{l:bl,def:positions[bl]?positions[bl].match:void 0}:bl}function determineNewCaretPosition(selectedCaret,tabbed,positionCaretOnClick){var inputmask=this,maskset=this.maskset,opts=this.opts;function doRadixFocus(clickPos){if(""!==opts.radixPoint&&0!==opts.digits){var vps=maskset.validPositions;if(void 0===vps[clickPos]||vps[clickPos].input===_validationTests.getPlaceholder.call(inputmask,clickPos)){if(clickPos<seekNext.call(inputmask,-1))return!0;var radixPos=getBuffer.call(inputmask).indexOf(opts.radixPoint);if(-1!==radixPos){for(var vp in vps)if(vps[vp]&&radixPos<vp&&vps[vp].input!==_validationTests.getPlaceholder.call(inputmask,vp))return!1;return!0}}}return!1}if(tabbed&&(inputmask.isRTL?selectedCaret.end=selectedCaret.begin:selectedCaret.begin=selectedCaret.end),selectedCaret.begin===selectedCaret.end){switch(positionCaretOnClick=positionCaretOnClick||opts.positionCaretOnClick,positionCaretOnClick){case"none":break;case"select":selectedCaret={begin:0,end:getBuffer.call(inputmask).length};break;case"ignore":selectedCaret.end=selectedCaret.begin=seekNext.call(inputmask,getLastValidPosition.call(inputmask));break;case"radixFocus":if(doRadixFocus(selectedCaret.begin)){var radixPos=getBuffer.call(inputmask).join("").indexOf(opts.radixPoint);selectedCaret.end=selectedCaret.begin=opts.numericInput?seekNext.call(inputmask,radixPos):radixPos;break}default:var clickPosition=selectedCaret.begin,lvclickPosition=getLastValidPosition.call(inputmask,clickPosition,!0),lastPosition=seekNext.call(inputmask,-1!==lvclickPosition||isMask.call(inputmask,0)?lvclickPosition:-1);if(clickPosition<=lastPosition)selectedCaret.end=selectedCaret.begin=isMask.call(inputmask,clickPosition,!1,!0)?clickPosition:seekNext.call(inputmask,clickPosition);else{var lvp=maskset.validPositions[lvclickPosition],tt=_validationTests.getTestTemplate.call(inputmask,lastPosition,lvp?lvp.match.locator:void 0,lvp),placeholder=_validationTests.getPlaceholder.call(inputmask,lastPosition,tt.match);if(""!==placeholder&&getBuffer.call(inputmask)[lastPosition]!==placeholder&&!0!==tt.match.optionalQuantifier&&!0!==tt.match.newBlockMarker||!isMask.call(inputmask,lastPosition,opts.keepStatic,!0)&&tt.match.def===placeholder){var newPos=seekNext.call(inputmask,lastPosition);(newPos<=clickPosition||clickPosition===lastPosition)&&(lastPosition=newPos)}selectedCaret.end=selectedCaret.begin=lastPosition}}return selectedCaret}}function getBuffer(noCache){var inputmask=this,maskset=this.maskset;return void 0!==maskset.buffer&&!0!==noCache||(maskset.buffer=_validationTests.getMaskTemplate.call(this,!0,getLastValidPosition.call(this),!0),void 0===maskset._buffer&&(maskset._buffer=maskset.buffer.slice())),maskset.buffer}function getBufferTemplate(){var inputmask=this,maskset=this.maskset;return void 0===maskset._buffer&&(maskset._buffer=_validationTests.getMaskTemplate.call(this,!1,1),void 0===maskset.buffer&&(maskset.buffer=maskset._buffer.slice())),maskset._buffer}function getLastValidPosition(closestTo,strict,validPositions){var maskset=this.maskset,before=-1,after=-1,valids=validPositions||maskset.validPositions;for(var posNdx in void 0===closestTo&&(closestTo=-1),valids){var psNdx=parseInt(posNdx);valids[psNdx]&&(strict||!0!==valids[psNdx].generatedInput)&&(psNdx<=closestTo&&(before=psNdx),closestTo<=psNdx&&(after=psNdx))}return-1===before||before==closestTo?after:-1==after?before:closestTo-before<after-closestTo?before:after}function isMask(pos,strict,fuzzy){var inputmask=this,maskset=this.maskset,test=_validationTests.getTestTemplate.call(this,pos).match;if(""===test.def&&(test=_validationTests.getTest.call(this,pos).match),!0!==test.static)return test.fn;if(!0===fuzzy&&void 0!==maskset.validPositions[pos]&&!0!==maskset.validPositions[pos].generatedInput)return!0;if(!0!==strict&&-1<pos){if(fuzzy){var tests=_validationTests.getTests.call(this,pos);return tests.length>1+(""===tests[tests.length-1].match.def?1:0)}var testTemplate=_validationTests.determineTestTemplate.call(this,pos,_validationTests.getTests.call(this,pos)),testPlaceHolder=_validationTests.getPlaceholder.call(this,pos,testTemplate.match);return testTemplate.match.def!==testPlaceHolder}return!1}function resetMaskSet(soft){var maskset=this.maskset;maskset.buffer=void 0,!0!==soft&&(maskset.validPositions={},maskset.p=0)}function seekNext(pos,newBlock,fuzzy){var inputmask=this;void 0===fuzzy&&(fuzzy=!0);for(var position=pos+1;""!==_validationTests.getTest.call(this,position).match.def&&(!0===newBlock&&(!0!==_validationTests.getTest.call(this,position).match.newBlockMarker||!isMask.call(this,position,void 0,!0))||!0!==newBlock&&!isMask.call(this,position,void 0,fuzzy));)position++;return position}function seekPrevious(pos,newBlock){var inputmask=this,position=pos-1;if(pos<=0)return 0;for(;0<position&&(!0===newBlock&&(!0!==_validationTests.getTest.call(this,position).match.newBlockMarker||!isMask.call(this,position,void 0,!0))||!0!==newBlock&&!isMask.call(this,position,void 0,!0));)position--;return position}function translatePosition(pos){var inputmask=this,opts=this.opts,el=this.el;return!this.isRTL||"number"!=typeof pos||opts.greedy&&""===opts.placeholder||!el||(pos=Math.abs(this._valueGet().length-pos)),pos}},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0,__webpack_require__(16),__webpack_require__(17);var _mask=__webpack_require__(18),_inputmask=_interopRequireDefault(__webpack_require__(12)),_window=_interopRequireDefault(__webpack_require__(8)),_maskLexer=__webpack_require__(19),_validationTests=__webpack_require__(3),_positioning=__webpack_require__(1),_validation=__webpack_require__(4),_inputHandling=__webpack_require__(5),_eventruler=__webpack_require__(11),_definitions=_interopRequireDefault(__webpack_require__(21)),_defaults=_interopRequireDefault(__webpack_require__(22)),_canUseDOM=_interopRequireDefault(__webpack_require__(9));function _typeof(obj){return _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function _typeof(obj){return typeof obj}:function _typeof(obj){return obj&&"function"==typeof Symbol&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj},_typeof(obj)}function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var document=_window.default.document,dataKey="_inputmask_opts";function Inputmask(alias,options,internal){if(_canUseDOM.default){if(!(this instanceof Inputmask))return new Inputmask(alias,options,internal);this.dependencyLib=_inputmask.default,this.el=void 0,this.events={},this.maskset=void 0,!0!==internal&&("[object Object]"===Object.prototype.toString.call(alias)?options=alias:(options=options||{},alias&&(options.alias=alias)),this.opts=_inputmask.default.extend(!0,{},this.defaults,options),this.noMasksCache=options&&void 0!==options.definitions,this.userOptions=options||{},resolveAlias(this.opts.alias,options,this.opts)),this.refreshValue=!1,this.undoValue=void 0,this.$el=void 0,this.skipKeyPressEvent=!1,this.skipInputEvent=!1,this.validationEvent=!1,this.ignorable=!1,this.maxLength,this.mouseEnter=!1,this.originalPlaceholder=void 0,this.isComposing=!1}}function resolveAlias(aliasStr,options,opts){var aliasDefinition=Inputmask.prototype.aliases[aliasStr];return aliasDefinition?(aliasDefinition.alias&&resolveAlias(aliasDefinition.alias,void 0,opts),_inputmask.default.extend(!0,opts,aliasDefinition),_inputmask.default.extend(!0,opts,options),!0):(null===opts.mask&&(opts.mask=aliasStr),!1)}function importAttributeOptions(npt,opts,userOptions,dataAttribute){function importOption(option,optionData){var attrOption=""===dataAttribute?option:dataAttribute+"-"+option;optionData=void 0!==optionData?optionData:npt.getAttribute(attrOption),null!==optionData&&("string"==typeof optionData&&(0===option.indexOf("on")?optionData=_window.default[optionData]:"false"===optionData?optionData=!1:"true"===optionData&&(optionData=!0)),userOptions[option]=optionData)}if(!0===opts.importDataAttributes){var attrOptions=npt.getAttribute(dataAttribute),option,dataoptions,optionData,p;if(attrOptions&&""!==attrOptions&&(attrOptions=attrOptions.replace(/'/g,'"'),dataoptions=JSON.parse("{"+attrOptions+"}")),dataoptions)for(p in optionData=void 0,dataoptions)if("alias"===p.toLowerCase()){optionData=dataoptions[p];break}for(option in importOption("alias",optionData),userOptions.alias&&resolveAlias(userOptions.alias,userOptions,opts),opts){if(dataoptions)for(p in optionData=void 0,dataoptions)if(p.toLowerCase()===option.toLowerCase()){optionData=dataoptions[p];break}importOption(option,optionData)}}return _inputmask.default.extend(!0,opts,userOptions),"rtl"!==npt.dir&&!opts.rightAlign||(npt.style.textAlign="right"),"rtl"!==npt.dir&&!opts.numericInput||(npt.dir="ltr",npt.removeAttribute("dir"),opts.isRTL=!0),Object.keys(userOptions).length}Inputmask.prototype={dataAttribute:"data-inputmask",defaults:_defaults.default,definitions:_definitions.default,aliases:{},masksCache:{},get isRTL(){return this.opts.isRTL||this.opts.numericInput},mask:function mask(elems){var that=this;return"string"==typeof elems&&(elems=document.getElementById(elems)||document.querySelectorAll(elems)),elems=elems.nodeName?[elems]:Array.isArray(elems)?elems:Array.from(elems),elems.forEach(function(el,ndx){var scopedOpts=_inputmask.default.extend(!0,{},that.opts);if(importAttributeOptions(el,scopedOpts,_inputmask.default.extend(!0,{},that.userOptions),that.dataAttribute)){var maskset=(0,_maskLexer.generateMaskSet)(scopedOpts,that.noMasksCache);void 0!==maskset&&(void 0!==el.inputmask&&(el.inputmask.opts.autoUnmask=!0,el.inputmask.remove()),el.inputmask=new Inputmask(void 0,void 0,!0),el.inputmask.opts=scopedOpts,el.inputmask.noMasksCache=that.noMasksCache,el.inputmask.userOptions=_inputmask.default.extend(!0,{},that.userOptions),el.inputmask.el=el,el.inputmask.$el=(0,_inputmask.default)(el),el.inputmask.maskset=maskset,_inputmask.default.data(el,dataKey,that.userOptions),_mask.mask.call(el.inputmask))}}),elems&&elems[0]&&elems[0].inputmask||this},option:function option(options,noremask){return"string"==typeof options?this.opts[options]:"object"===_typeof(options)?(_inputmask.default.extend(this.userOptions,options),this.el&&!0!==noremask&&this.mask(this.el),this):void 0},unmaskedvalue:function unmaskedvalue(value){if(this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache),void 0===this.el||void 0!==value){var valueBuffer=("function"==typeof this.opts.onBeforeMask&&this.opts.onBeforeMask.call(this,value,this.opts)||value).split("");_inputHandling.checkVal.call(this,void 0,!1,!1,valueBuffer),"function"==typeof this.opts.onBeforeWrite&&this.opts.onBeforeWrite.call(this,void 0,_positioning.getBuffer.call(this),0,this.opts)}return _inputHandling.unmaskedvalue.call(this,this.el)},remove:function remove(){if(this.el){_inputmask.default.data(this.el,dataKey,null);var cv=this.opts.autoUnmask?(0,_inputHandling.unmaskedvalue)(this.el):this._valueGet(this.opts.autoUnmask),valueProperty;cv!==_positioning.getBufferTemplate.call(this).join("")?this._valueSet(cv,this.opts.autoUnmask):this._valueSet(""),_eventruler.EventRuler.off(this.el),Object.getOwnPropertyDescriptor&&Object.getPrototypeOf?(valueProperty=Object.getOwnPropertyDescriptor(Object.getPrototypeOf(this.el),"value"),valueProperty&&this.__valueGet&&Object.defineProperty(this.el,"value",{get:this.__valueGet,set:this.__valueSet,configurable:!0})):document.__lookupGetter__&&this.el.__lookupGetter__("value")&&this.__valueGet&&(this.el.__defineGetter__("value",this.__valueGet),this.el.__defineSetter__("value",this.__valueSet)),this.el.inputmask=void 0}return this.el},getemptymask:function getemptymask(){return this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache),_positioning.getBufferTemplate.call(this).join("")},hasMaskedValue:function hasMaskedValue(){return!this.opts.autoUnmask},isComplete:function isComplete(){return this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache),_validation.isComplete.call(this,_positioning.getBuffer.call(this))},getmetadata:function getmetadata(){if(this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache),Array.isArray(this.maskset.metadata)){var maskTarget=_validationTests.getMaskTemplate.call(this,!0,0,!1).join("");return this.maskset.metadata.forEach(function(mtdt){return mtdt.mask!==maskTarget||(maskTarget=mtdt,!1)}),maskTarget}return this.maskset.metadata},isValid:function isValid(value){if(this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache),value){var valueBuffer=("function"==typeof this.opts.onBeforeMask&&this.opts.onBeforeMask.call(this,value,this.opts)||value).split("");_inputHandling.checkVal.call(this,void 0,!0,!1,valueBuffer)}else value=this.isRTL?_positioning.getBuffer.call(this).slice().reverse().join(""):_positioning.getBuffer.call(this).join("");for(var buffer=_positioning.getBuffer.call(this),rl=_positioning.determineLastRequiredPosition.call(this),lmib=buffer.length-1;rl<lmib&&!_positioning.isMask.call(this,lmib);lmib--);return buffer.splice(rl,lmib+1-rl),_validation.isComplete.call(this,buffer)&&value===(this.isRTL?_positioning.getBuffer.call(this).slice().reverse().join(""):_positioning.getBuffer.call(this).join(""))},format:function format(value,metadata){this.maskset=this.maskset||(0,_maskLexer.generateMaskSet)(this.opts,this.noMasksCache);var valueBuffer=("function"==typeof this.opts.onBeforeMask&&this.opts.onBeforeMask.call(this,value,this.opts)||value).split("");_inputHandling.checkVal.call(this,void 0,!0,!1,valueBuffer);var formattedValue=this.isRTL?_positioning.getBuffer.call(this).slice().reverse().join(""):_positioning.getBuffer.call(this).join("");return metadata?{value:formattedValue,metadata:this.getmetadata()}:formattedValue},setValue:function setValue(value){this.el&&(0,_inputmask.default)(this.el).trigger("setvalue",[value])},analyseMask:_maskLexer.analyseMask},Inputmask.extendDefaults=function(options){_inputmask.default.extend(!0,Inputmask.prototype.defaults,options)},Inputmask.extendDefinitions=function(definition){_inputmask.default.extend(!0,Inputmask.prototype.definitions,definition)},Inputmask.extendAliases=function(alias){_inputmask.default.extend(!0,Inputmask.prototype.aliases,alias)},Inputmask.format=function(value,options,metadata){return Inputmask(options).format(value,metadata)},Inputmask.unmask=function(value,options){return Inputmask(options).unmaskedvalue(value)},Inputmask.isValid=function(value,options){return Inputmask(options).isValid(value)},Inputmask.remove=function(elems){"string"==typeof elems&&(elems=document.getElementById(elems)||document.querySelectorAll(elems)),elems=elems.nodeName?[elems]:elems,elems.forEach(function(el){el.inputmask&&el.inputmask.remove()})},Inputmask.setValue=function(elems,value){"string"==typeof elems&&(elems=document.getElementById(elems)||document.querySelectorAll(elems)),elems=elems.nodeName?[elems]:elems,elems.forEach(function(el){el.inputmask?el.inputmask.setValue(value):(0,_inputmask.default)(el).trigger("setvalue",[value])})},Inputmask.dependencyLib=_inputmask.default,_window.default.Inputmask=Inputmask;var _default=Inputmask;exports.default=_default},function(module,exports,__webpack_require__){"use strict";function getLocator(tst,align){var locator=(null!=tst.alternation?tst.mloc[getDecisionTaker(tst)]:tst.locator).join("");if(""!==locator)for(;locator.length<align;)locator+="0";return locator}function getDecisionTaker(tst){var decisionTaker=tst.locator[tst.alternation];return"string"==typeof decisionTaker&&0<decisionTaker.length&&(decisionTaker=decisionTaker.split(",")[0]),void 0!==decisionTaker?decisionTaker.toString():""}function getPlaceholder(pos,test,returnPL){var inputmask=this,opts=this.opts,maskset=this.maskset;if(test=test||getTest.call(this,pos).match,void 0!==test.placeholder||!0===returnPL)return"function"==typeof test.placeholder?test.placeholder(opts):test.placeholder;if(!0!==test.static)return opts.placeholder.charAt(pos%opts.placeholder.length);if(-1<pos&&void 0===maskset.validPositions[pos]){var tests=getTests.call(this,pos),staticAlternations=[],prevTest;if(tests.length>1+(""===tests[tests.length-1].match.def?1:0))for(var i=0;i<tests.length;i++)if(""!==tests[i].match.def&&!0!==tests[i].match.optionality&&!0!==tests[i].match.optionalQuantifier&&(!0===tests[i].match.static||void 0===prevTest||!1!==tests[i].match.fn.test(prevTest.match.def,maskset,pos,!0,opts))&&(staticAlternations.push(tests[i]),!0===tests[i].match.static&&(prevTest=tests[i]),1<staticAlternations.length&&/[0-9a-bA-Z]/.test(staticAlternations[0].match.def)))return opts.placeholder.charAt(pos%opts.placeholder.length)}return test.def}function getMaskTemplate(baseOnInput,minimalPos,includeMode,noJit,clearOptionalTail){var inputmask=this,opts=this.opts,maskset=this.maskset,greedy=opts.greedy;clearOptionalTail&&(opts.greedy=!1),minimalPos=minimalPos||0;var maskTemplate=[],ndxIntlzr,pos=0,test,testPos,jitRenderStatic;do{if(!0===baseOnInput&&maskset.validPositions[pos])testPos=clearOptionalTail&&!0===maskset.validPositions[pos].match.optionality&&void 0===maskset.validPositions[pos+1]&&(!0===maskset.validPositions[pos].generatedInput||maskset.validPositions[pos].input==opts.skipOptionalPartCharacter&&0<pos)?determineTestTemplate.call(this,pos,getTests.call(this,pos,ndxIntlzr,pos-1)):maskset.validPositions[pos],test=testPos.match,ndxIntlzr=testPos.locator.slice(),maskTemplate.push(!0===includeMode?testPos.input:!1===includeMode?test.nativeDef:getPlaceholder.call(this,pos,test));else{testPos=getTestTemplate.call(this,pos,ndxIntlzr,pos-1),test=testPos.match,ndxIntlzr=testPos.locator.slice();var jitMasking=!0!==noJit&&(!1!==opts.jitMasking?opts.jitMasking:test.jit);jitRenderStatic=(jitRenderStatic&&test.static&&test.def!==opts.groupSeparator&&null===test.fn||maskset.validPositions[pos-1]&&test.static&&test.def!==opts.groupSeparator&&null===test.fn)&&maskset.tests[pos]&&1===maskset.tests[pos].length,jitRenderStatic||!1===jitMasking||void 0===jitMasking||"number"==typeof jitMasking&&isFinite(jitMasking)&&pos<jitMasking?maskTemplate.push(!1===includeMode?test.nativeDef:getPlaceholder.call(this,pos,test)):jitRenderStatic=!1}pos++}while((void 0===this.maxLength||pos<this.maxLength)&&(!0!==test.static||""!==test.def)||pos<minimalPos);return""===maskTemplate[maskTemplate.length-1]&&maskTemplate.pop(),!1===includeMode&&void 0!==maskset.maskLength||(maskset.maskLength=pos-1),opts.greedy=greedy,maskTemplate}function getTestTemplate(pos,ndxIntlzr,tstPs){var inputmask=this,maskset=this.maskset;return maskset.validPositions[pos]||determineTestTemplate.call(this,pos,getTests.call(this,pos,ndxIntlzr?ndxIntlzr.slice():ndxIntlzr,tstPs))}function determineTestTemplate(pos,tests){var inputmask=this,opts=this.opts;pos=0<pos?pos-1:0;for(var altTest=getTest.call(this,pos),targetLocator=getLocator(altTest),tstLocator,closest,bestMatch,ndx=0;ndx<tests.length;ndx++){var tst=tests[ndx];tstLocator=getLocator(tst,targetLocator.length);var distance=Math.abs(tstLocator-targetLocator);(void 0===closest||""!==tstLocator&&distance<closest||bestMatch&&!opts.greedy&&bestMatch.match.optionality&&"master"===bestMatch.match.newBlockMarker&&(!tst.match.optionality||!tst.match.newBlockMarker)||bestMatch&&bestMatch.match.optionalQuantifier&&!tst.match.optionalQuantifier)&&(closest=distance,bestMatch=tst)}return bestMatch}function getTest(pos,tests){var inputmask=this,maskset=this.maskset;return maskset.validPositions[pos]?maskset.validPositions[pos]:(tests||getTests.call(this,pos))[0]}function isSubsetOf(source,target,opts){function expand(pattern){for(var expanded=[],start=-1,end,i=0,l=pattern.length;i<l;i++)if("-"===pattern.charAt(i))for(end=pattern.charCodeAt(i+1);++start<end;)expanded.push(String.fromCharCode(start));else start=pattern.charCodeAt(i),expanded.push(pattern.charAt(i));return expanded.join("")}return source.match.def===target.match.nativeDef||!(!(opts.regex||source.match.fn instanceof RegExp&&target.match.fn instanceof RegExp)||!0===source.match.static||!0===target.match.static)&&-1!==expand(target.match.fn.toString().replace(/[[\]/]/g,"")).indexOf(expand(source.match.fn.toString().replace(/[[\]/]/g,"")))}function getTests(pos,ndxIntlzr,tstPs){var inputmask=this,$=this.dependencyLib,maskset=this.maskset,opts=this.opts,el=this.el,maskTokens=maskset.maskToken,testPos=ndxIntlzr?tstPs:0,ndxInitializer=ndxIntlzr?ndxIntlzr.slice():[0],matches=[],insertStop=!1,latestMatch,cacheDependency=ndxIntlzr?ndxIntlzr.join(""):"";function resolveTestFromToken(maskToken,ndxInitializer,loopNdx,quantifierRecurse){function handleMatch(match,loopNdx,quantifierRecurse){function isFirstMatch(latestMatch,tokenGroup){var firstMatch=0===tokenGroup.matches.indexOf(latestMatch);return firstMatch||tokenGroup.matches.every(function(match,ndx){return!0===match.isQuantifier?firstMatch=isFirstMatch(latestMatch,tokenGroup.matches[ndx-1]):Object.prototype.hasOwnProperty.call(match,"matches")&&(firstMatch=isFirstMatch(latestMatch,match)),!firstMatch}),firstMatch}function resolveNdxInitializer(pos,alternateNdx,targetAlternation){var bestMatch,indexPos;if((maskset.tests[pos]||maskset.validPositions[pos])&&(maskset.tests[pos]||[maskset.validPositions[pos]]).every(function(lmnt,ndx){if(lmnt.mloc[alternateNdx])return bestMatch=lmnt,!1;var alternation=void 0!==targetAlternation?targetAlternation:lmnt.alternation,ndxPos=void 0!==lmnt.locator[alternation]?lmnt.locator[alternation].toString().indexOf(alternateNdx):-1;return(void 0===indexPos||ndxPos<indexPos)&&-1!==ndxPos&&(bestMatch=lmnt,indexPos=ndxPos),!0}),bestMatch){var bestMatchAltIndex=bestMatch.locator[bestMatch.alternation],locator=bestMatch.mloc[alternateNdx]||bestMatch.mloc[bestMatchAltIndex]||bestMatch.locator;return locator.slice((void 0!==targetAlternation?targetAlternation:bestMatch.alternation)+1)}return void 0!==targetAlternation?resolveNdxInitializer(pos,alternateNdx):void 0}function staticCanMatchDefinition(source,target){return!0===source.match.static&&!0!==target.match.static&&target.match.fn.test(source.match.def,maskset,pos,!1,opts,!1)}function setMergeLocators(targetMatch,altMatch){var alternationNdx=targetMatch.alternation,shouldMerge=void 0===altMatch||alternationNdx===altMatch.alternation&&-1===targetMatch.locator[alternationNdx].toString().indexOf(altMatch.locator[alternationNdx]);if(!shouldMerge&&alternationNdx>altMatch.alternation)for(var i=altMatch.alternation;i<alternationNdx;i++)if(targetMatch.locator[i]!==altMatch.locator[i]){alternationNdx=i,shouldMerge=!0;break}if(shouldMerge){targetMatch.mloc=targetMatch.mloc||{};var locNdx=targetMatch.locator[alternationNdx];if(void 0!==locNdx){if("string"==typeof locNdx&&(locNdx=locNdx.split(",")[0]),void 0===targetMatch.mloc[locNdx]&&(targetMatch.mloc[locNdx]=targetMatch.locator.slice()),void 0!==altMatch){for(var ndx in altMatch.mloc)"string"==typeof ndx&&(ndx=ndx.split(",")[0]),void 0===targetMatch.mloc[ndx]&&(targetMatch.mloc[ndx]=altMatch.mloc[ndx]);targetMatch.locator[alternationNdx]=Object.keys(targetMatch.mloc).join(",")}return!0}targetMatch.alternation=void 0}return!1}function isSameLevel(targetMatch,altMatch){if(targetMatch.locator.length!==altMatch.locator.length)return!1;for(var locNdx=targetMatch.alternation+1;locNdx<targetMatch.locator.length;locNdx++)if(targetMatch.locator[locNdx]!==altMatch.locator[locNdx])return!1;return!0}if(testPos>pos+opts._maxTestPos)throw"Inputmask: There is probably an error in your mask definition or in the code. Create an issue on github with an example of the mask you are using. "+maskset.mask;if(testPos===pos&&void 0===match.matches)return matches.push({match:match,locator:loopNdx.reverse(),cd:cacheDependency,mloc:{}}),!0;if(void 0!==match.matches){if(match.isGroup&&quantifierRecurse!==match){if(match=handleMatch(maskToken.matches[maskToken.matches.indexOf(match)+1],loopNdx,quantifierRecurse),match)return!0}else if(match.isOptional){var optionalToken=match,mtchsNdx=matches.length;if(match=resolveTestFromToken(match,ndxInitializer,loopNdx,quantifierRecurse),match){if(matches.forEach(function(mtch,ndx){mtchsNdx<=ndx&&(mtch.match.optionality=!0)}),latestMatch=matches[matches.length-1].match,void 0!==quantifierRecurse||!isFirstMatch(latestMatch,optionalToken))return!0;insertStop=!0,testPos=pos}}else if(match.isAlternator){var alternateToken=match,malternateMatches=[],maltMatches,currentMatches=matches.slice(),loopNdxCnt=loopNdx.length,unMatchedAlternation=!1,altIndex=0<ndxInitializer.length?ndxInitializer.shift():-1;if(-1===altIndex||"string"==typeof altIndex){var currentPos=testPos,ndxInitializerClone=ndxInitializer.slice(),altIndexArr=[],amndx;if("string"==typeof altIndex)altIndexArr=altIndex.split(",");else for(amndx=0;amndx<alternateToken.matches.length;amndx++)altIndexArr.push(amndx.toString());if(void 0!==maskset.excludes[pos]){for(var altIndexArrClone=altIndexArr.slice(),i=0,exl=maskset.excludes[pos].length;i<exl;i++){var excludeSet=maskset.excludes[pos][i].toString().split(":");loopNdx.length==excludeSet[1]&&altIndexArr.splice(altIndexArr.indexOf(excludeSet[0]),1)}0===altIndexArr.length&&(delete maskset.excludes[pos],altIndexArr=altIndexArrClone)}(!0===opts.keepStatic||isFinite(parseInt(opts.keepStatic))&&currentPos>=opts.keepStatic)&&(altIndexArr=altIndexArr.slice(0,1));for(var ndx=0;ndx<altIndexArr.length;ndx++){amndx=parseInt(altIndexArr[ndx]),matches=[],ndxInitializer="string"==typeof altIndex&&resolveNdxInitializer(testPos,amndx,loopNdxCnt)||ndxInitializerClone.slice();var tokenMatch=alternateToken.matches[amndx];if(tokenMatch&&handleMatch(tokenMatch,[amndx].concat(loopNdx),quantifierRecurse))match=!0;else if(0===ndx&&(unMatchedAlternation=!0),tokenMatch&&tokenMatch.matches&&tokenMatch.matches.length>alternateToken.matches[0].matches.length)break;maltMatches=matches.slice(),testPos=currentPos,matches=[];for(var ndx1=0;ndx1<maltMatches.length;ndx1++){var altMatch=maltMatches[ndx1],dropMatch=!1;altMatch.match.jit=altMatch.match.jit||unMatchedAlternation,altMatch.alternation=altMatch.alternation||loopNdxCnt,setMergeLocators(altMatch);for(var ndx2=0;ndx2<malternateMatches.length;ndx2++){var altMatch2=malternateMatches[ndx2];if("string"!=typeof altIndex||void 0!==altMatch.alternation&&altIndexArr.includes(altMatch.locator[altMatch.alternation].toString())){if(altMatch.match.nativeDef===altMatch2.match.nativeDef){dropMatch=!0,setMergeLocators(altMatch2,altMatch);break}if(isSubsetOf(altMatch,altMatch2,opts)){setMergeLocators(altMatch,altMatch2)&&(dropMatch=!0,malternateMatches.splice(malternateMatches.indexOf(altMatch2),0,altMatch));break}if(isSubsetOf(altMatch2,altMatch,opts)){setMergeLocators(altMatch2,altMatch);break}if(staticCanMatchDefinition(altMatch,altMatch2)){isSameLevel(altMatch,altMatch2)||void 0!==el.inputmask.userOptions.keepStatic?setMergeLocators(altMatch,altMatch2)&&(dropMatch=!0,malternateMatches.splice(malternateMatches.indexOf(altMatch2),0,altMatch)):opts.keepStatic=!0;break}}}dropMatch||malternateMatches.push(altMatch)}}matches=currentMatches.concat(malternateMatches),testPos=pos,insertStop=0<matches.length,match=0<malternateMatches.length,ndxInitializer=ndxInitializerClone.slice()}else match=handleMatch(alternateToken.matches[altIndex]||maskToken.matches[altIndex],[altIndex].concat(loopNdx),quantifierRecurse);if(match)return!0}else if(match.isQuantifier&&quantifierRecurse!==maskToken.matches[maskToken.matches.indexOf(match)-1])for(var qt=match,qndx=0<ndxInitializer.length?ndxInitializer.shift():0;qndx<(isNaN(qt.quantifier.max)?qndx+1:qt.quantifier.max)&&testPos<=pos;qndx++){var tokenGroup=maskToken.matches[maskToken.matches.indexOf(qt)-1];if(match=handleMatch(tokenGroup,[qndx].concat(loopNdx),tokenGroup),match){if(latestMatch=matches[matches.length-1].match,latestMatch.optionalQuantifier=qndx>=qt.quantifier.min,latestMatch.jit=(qndx||1)*tokenGroup.matches.indexOf(latestMatch)>=qt.quantifier.jit,latestMatch.optionalQuantifier&&isFirstMatch(latestMatch,tokenGroup)){insertStop=!0,testPos=pos;break}return latestMatch.jit&&(maskset.jitOffset[pos]=tokenGroup.matches.length-tokenGroup.matches.indexOf(latestMatch)),!0}}else if(match=resolveTestFromToken(match,ndxInitializer,loopNdx,quantifierRecurse),match)return!0}else testPos++}for(var tndx=0<ndxInitializer.length?ndxInitializer.shift():0;tndx<maskToken.matches.length;tndx++)if(!0!==maskToken.matches[tndx].isQuantifier){var match=handleMatch(maskToken.matches[tndx],[tndx].concat(loopNdx),quantifierRecurse);if(match&&testPos===pos)return match;if(pos<testPos)break}}function mergeLocators(pos,tests){var locator=[],alternation;return Array.isArray(tests)||(tests=[tests]),0<tests.length&&(void 0===tests[0].alternation||!0===opts.keepStatic?(locator=determineTestTemplate.call(inputmask,pos,tests.slice()).locator.slice(),0===locator.length&&(locator=tests[0].locator.slice())):tests.forEach(function(tst){""!==tst.def&&(0===locator.length?(alternation=tst.alternation,locator=tst.locator.slice()):tst.locator[alternation]&&-1===locator[alternation].toString().indexOf(tst.locator[alternation])&&(locator[alternation]+=","+tst.locator[alternation]))})),locator}if(-1<pos&&(void 0===inputmask.maxLength||pos<inputmask.maxLength)){if(void 0===ndxIntlzr){for(var previousPos=pos-1,test;void 0===(test=maskset.validPositions[previousPos]||maskset.tests[previousPos])&&-1<previousPos;)previousPos--;void 0!==test&&-1<previousPos&&(ndxInitializer=mergeLocators(previousPos,test),cacheDependency=ndxInitializer.join(""),testPos=previousPos)}if(maskset.tests[pos]&&maskset.tests[pos][0].cd===cacheDependency)return maskset.tests[pos];for(var mtndx=ndxInitializer.shift();mtndx<maskTokens.length;mtndx++){var match=resolveTestFromToken(maskTokens[mtndx],ndxInitializer,[mtndx]);if(match&&testPos===pos||pos<testPos)break}}return 0!==matches.length&&!insertStop||matches.push({match:{fn:null,static:!0,optionality:!1,casing:null,def:"",placeholder:""},locator:[],mloc:{},cd:cacheDependency}),void 0!==ndxIntlzr&&maskset.tests[pos]?$.extend(!0,[],matches):(maskset.tests[pos]=$.extend(!0,[],matches),maskset.tests[pos])}Object.defineProperty(exports,"__esModule",{value:!0}),exports.determineTestTemplate=determineTestTemplate,exports.getDecisionTaker=getDecisionTaker,exports.getMaskTemplate=getMaskTemplate,exports.getPlaceholder=getPlaceholder,exports.getTest=getTest,exports.getTests=getTests,exports.getTestTemplate=getTestTemplate,exports.isSubsetOf=isSubsetOf},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.alternate=alternate,exports.checkAlternationMatch=checkAlternationMatch,exports.isComplete=isComplete,exports.isValid=isValid,exports.refreshFromBuffer=refreshFromBuffer,exports.revalidateMask=revalidateMask,exports.handleRemove=handleRemove;var _validationTests=__webpack_require__(3),_keycode=_interopRequireDefault(__webpack_require__(0)),_positioning=__webpack_require__(1),_eventhandlers=__webpack_require__(6);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}function alternate(maskPos,c,strict,fromIsValid,rAltPos,selection){var inputmask=this,$=this.dependencyLib,opts=this.opts,maskset=this.maskset,validPsClone=$.extend(!0,{},maskset.validPositions),tstClone=$.extend(!0,{},maskset.tests),lastAlt,alternation,isValidRslt=!1,returnRslt=!1,altPos,prevAltPos,i,validPos,decisionPos,lAltPos=void 0!==rAltPos?rAltPos:_positioning.getLastValidPosition.call(this),nextPos,input,begin,end;if(selection&&(begin=selection.begin,end=selection.end,selection.begin>selection.end&&(begin=selection.end,end=selection.begin)),-1===lAltPos&&void 0===rAltPos)lastAlt=0,prevAltPos=_validationTests.getTest.call(this,lastAlt),alternation=prevAltPos.alternation;else for(;0<=lAltPos;lAltPos--)if(altPos=maskset.validPositions[lAltPos],altPos&&void 0!==altPos.alternation){if(prevAltPos&&prevAltPos.locator[altPos.alternation]!==altPos.locator[altPos.alternation])break;lastAlt=lAltPos,alternation=maskset.validPositions[lastAlt].alternation,prevAltPos=altPos}if(void 0!==alternation){decisionPos=parseInt(lastAlt),maskset.excludes[decisionPos]=maskset.excludes[decisionPos]||[],!0!==maskPos&&maskset.excludes[decisionPos].push((0,_validationTests.getDecisionTaker)(prevAltPos)+":"+prevAltPos.alternation);var validInputs=[],resultPos=-1;for(i=decisionPos;i<_positioning.getLastValidPosition.call(this,void 0,!0)+1;i++)-1===resultPos&&maskPos<=i&&void 0!==c&&(validInputs.push(c),resultPos=validInputs.length-1),validPos=maskset.validPositions[i],validPos&&!0!==validPos.generatedInput&&(void 0===selection||i<begin||end<=i)&&validInputs.push(validPos.input),delete maskset.validPositions[i];for(-1===resultPos&&void 0!==c&&(validInputs.push(c),resultPos=validInputs.length-1);void 0!==maskset.excludes[decisionPos]&&maskset.excludes[decisionPos].length<10;){for(maskset.tests={},_positioning.resetMaskSet.call(this,!0),isValidRslt=!0,i=0;i<validInputs.length&&(nextPos=isValidRslt.caret||_positioning.getLastValidPosition.call(this,void 0,!0)+1,input=validInputs[i],isValidRslt=isValid.call(this,nextPos,input,!1,fromIsValid,!0));i++)i===resultPos&&(returnRslt=isValidRslt),1==maskPos&&isValidRslt&&(returnRslt={caretPos:i});if(isValidRslt)break;if(_positioning.resetMaskSet.call(this),prevAltPos=_validationTests.getTest.call(this,decisionPos),maskset.validPositions=$.extend(!0,{},validPsClone),maskset.tests=$.extend(!0,{},tstClone),!maskset.excludes[decisionPos]){returnRslt=alternate.call(this,maskPos,c,strict,fromIsValid,decisionPos-1,selection);break}var decisionTaker=(0,_validationTests.getDecisionTaker)(prevAltPos);if(-1!==maskset.excludes[decisionPos].indexOf(decisionTaker+":"+prevAltPos.alternation)){returnRslt=alternate.call(this,maskPos,c,strict,fromIsValid,decisionPos-1,selection);break}for(maskset.excludes[decisionPos].push(decisionTaker+":"+prevAltPos.alternation),i=decisionPos;i<_positioning.getLastValidPosition.call(this,void 0,!0)+1;i++)delete maskset.validPositions[i]}}return returnRslt&&!1===opts.keepStatic||delete maskset.excludes[decisionPos],returnRslt}function casing(elem,test,pos){var opts=this.opts,maskset=this.maskset;switch(opts.casing||test.casing){case"upper":elem=elem.toUpperCase();break;case"lower":elem=elem.toLowerCase();break;case"title":var posBefore=maskset.validPositions[pos-1];elem=0===pos||posBefore&&posBefore.input===String.fromCharCode(_keycode.default.SPACE)?elem.toUpperCase():elem.toLowerCase();break;default:if("function"==typeof opts.casing){var args=Array.prototype.slice.call(arguments);args.push(maskset.validPositions),elem=opts.casing.apply(this,args)}}return elem}function checkAlternationMatch(altArr1,altArr2,na){for(var opts=this.opts,altArrC=opts.greedy?altArr2:altArr2.slice(0,1),isMatch=!1,naArr=void 0!==na?na.split(","):[],naNdx,i=0;i<naArr.length;i++)-1!==(naNdx=altArr1.indexOf(naArr[i]))&&altArr1.splice(naNdx,1);for(var alndx=0;alndx<altArr1.length;alndx++)if(altArrC.includes(altArr1[alndx])){isMatch=!0;break}return isMatch}function handleRemove(input,k,pos,strict,fromIsValid){var inputmask=this,maskset=this.maskset,opts=this.opts;if((opts.numericInput||this.isRTL)&&(k===_keycode.default.BACKSPACE?k=_keycode.default.DELETE:k===_keycode.default.DELETE&&(k=_keycode.default.BACKSPACE),this.isRTL)){var pend=pos.end;pos.end=pos.begin,pos.begin=pend}var lvp=_positioning.getLastValidPosition.call(this,void 0,!0),offset;if(pos.end>=_positioning.getBuffer.call(this).length&&lvp>=pos.end&&(pos.end=lvp+1),k===_keycode.default.BACKSPACE?pos.end-pos.begin<1&&(pos.begin=_positioning.seekPrevious.call(this,pos.begin)):k===_keycode.default.DELETE&&pos.begin===pos.end&&(pos.end=_positioning.isMask.call(this,pos.end,!0,!0)?pos.end+1:_positioning.seekNext.call(this,pos.end)+1),!1!==(offset=revalidateMask.call(this,pos))){if(!0!==strict&&!1!==opts.keepStatic||null!==opts.regex&&-1!==_validationTests.getTest.call(this,pos.begin).match.def.indexOf("|")){var result=alternate.call(this,!0);if(result){var newPos=void 0!==result.caret?result.caret:result.pos?_positioning.seekNext.call(this,result.pos.begin?result.pos.begin:result.pos):_positioning.getLastValidPosition.call(this,-1,!0);(k!==_keycode.default.DELETE||pos.begin>newPos)&&pos.begin}}!0!==strict&&(maskset.p=k===_keycode.default.DELETE?pos.begin+offset:pos.begin)}}function isComplete(buffer){var inputmask=this,opts=this.opts,maskset=this.maskset;if("function"==typeof opts.isComplete)return opts.isComplete(buffer,opts);if("*"!==opts.repeat){var complete=!1,lrp=_positioning.determineLastRequiredPosition.call(this,!0),aml=_positioning.seekPrevious.call(this,lrp.l);if(void 0===lrp.def||lrp.def.newBlockMarker||lrp.def.optionality||lrp.def.optionalQuantifier){complete=!0;for(var i=0;i<=aml;i++){var test=_validationTests.getTestTemplate.call(this,i).match;if(!0!==test.static&&void 0===maskset.validPositions[i]&&!0!==test.optionality&&!0!==test.optionalQuantifier||!0===test.static&&buffer[i]!==_validationTests.getPlaceholder.call(this,i,test)){complete=!1;break}}}return complete}}function isValid(pos,c,strict,fromIsValid,fromAlternate,validateOnly,fromCheckval){var inputmask=this,$=this.dependencyLib,opts=this.opts,maskset=inputmask.maskset;function isSelection(posObj){return inputmask.isRTL?1<posObj.begin-posObj.end||posObj.begin-posObj.end==1:1<posObj.end-posObj.begin||posObj.end-posObj.begin==1}strict=!0===strict;var maskPos=pos;function processCommandObject(commandObj){if(void 0!==commandObj){if(void 0!==commandObj.remove&&(Array.isArray(commandObj.remove)||(commandObj.remove=[commandObj.remove]),commandObj.remove.sort(function(a,b){return b.pos-a.pos}).forEach(function(lmnt){revalidateMask.call(inputmask,{begin:lmnt,end:lmnt+1})}),commandObj.remove=void 0),void 0!==commandObj.insert&&(Array.isArray(commandObj.insert)||(commandObj.insert=[commandObj.insert]),commandObj.insert.sort(function(a,b){return a.pos-b.pos}).forEach(function(lmnt){""!==lmnt.c&&isValid.call(inputmask,lmnt.pos,lmnt.c,void 0===lmnt.strict||lmnt.strict,void 0!==lmnt.fromIsValid?lmnt.fromIsValid:fromIsValid)}),commandObj.insert=void 0),commandObj.refreshFromBuffer&&commandObj.buffer){var refresh=commandObj.refreshFromBuffer;refreshFromBuffer.call(inputmask,!0===refresh?refresh:refresh.start,refresh.end,commandObj.buffer),commandObj.refreshFromBuffer=void 0}void 0!==commandObj.rewritePosition&&(maskPos=commandObj.rewritePosition,commandObj=!0)}return commandObj}function _isValid(position,c,strict){var rslt=!1;return _validationTests.getTests.call(inputmask,position).every(function(tst,ndx){var test=tst.match;if(_positioning.getBuffer.call(inputmask,!0),rslt=null!=test.fn?test.fn.test(c,maskset,position,strict,opts,isSelection(pos)):(c===test.def||c===opts.skipOptionalPartCharacter)&&""!==test.def&&{c:_validationTests.getPlaceholder.call(inputmask,position,test,!0)||test.def,pos:position},!1===rslt)return!0;var elem=void 0!==rslt.c?rslt.c:c,validatedPos=position;return elem=elem===opts.skipOptionalPartCharacter&&!0===test.static?_validationTests.getPlaceholder.call(inputmask,position,test,!0)||test.def:elem,rslt=processCommandObject(rslt),!0!==rslt&&void 0!==rslt.pos&&rslt.pos!==position&&(validatedPos=rslt.pos),!0!==rslt&&void 0===rslt.pos&&void 0===rslt.c||!1===revalidateMask.call(inputmask,pos,$.extend({},tst,{input:casing.call(inputmask,elem,test,validatedPos)}),fromIsValid,validatedPos)&&(rslt=!1),!1}),rslt}void 0!==pos.begin&&(maskPos=inputmask.isRTL?pos.end:pos.begin);var result=!0,positionsClone=$.extend(!0,{},maskset.validPositions);if(!1===opts.keepStatic&&void 0!==maskset.excludes[maskPos]&&!0!==fromAlternate&&!0!==fromIsValid)for(var i=maskPos;i<(inputmask.isRTL?pos.begin:pos.end);i++)void 0!==maskset.excludes[i]&&(maskset.excludes[i]=void 0,delete maskset.tests[i]);if("function"==typeof opts.preValidation&&!0!==fromIsValid&&!0!==validateOnly&&(result=opts.preValidation.call(inputmask,_positioning.getBuffer.call(inputmask),maskPos,c,isSelection(pos),opts,maskset,pos,strict||fromAlternate),result=processCommandObject(result)),!0===result){if(void 0===inputmask.maxLength||maskPos<_positioning.translatePosition.call(inputmask,inputmask.maxLength)){if(result=_isValid(maskPos,c,strict),(!strict||!0===fromIsValid)&&!1===result&&!0!==validateOnly){var currentPosValid=maskset.validPositions[maskPos];if(!currentPosValid||!0!==currentPosValid.match.static||currentPosValid.match.def!==c&&c!==opts.skipOptionalPartCharacter){if(opts.insertMode||void 0===maskset.validPositions[_positioning.seekNext.call(inputmask,maskPos)]||pos.end>maskPos){var skip=!1;if(maskset.jitOffset[maskPos]&&void 0===maskset.validPositions[_positioning.seekNext.call(inputmask,maskPos)]&&(result=isValid.call(inputmask,maskPos+maskset.jitOffset[maskPos],c,!0),!1!==result&&(!0!==fromAlternate&&(result.caret=maskPos),skip=!0)),pos.end>maskPos&&(maskset.validPositions[maskPos]=void 0),!skip&&!_positioning.isMask.call(inputmask,maskPos,opts.keepStatic&&0===maskPos))for(var nPos=maskPos+1,snPos=_positioning.seekNext.call(inputmask,maskPos,!1,0!==maskPos);nPos<=snPos;nPos++)if(result=_isValid(nPos,c,strict),!1!==result){result=trackbackPositions.call(inputmask,maskPos,void 0!==result.pos?result.pos:nPos)||result,maskPos=nPos;break}}}else result={caret:_positioning.seekNext.call(inputmask,maskPos)}}}else result=!1;!1!==result||!opts.keepStatic||!isComplete.call(inputmask,_positioning.getBuffer.call(inputmask))&&0!==maskPos||strict||!0===fromAlternate?isSelection(pos)&&maskset.tests[maskPos]&&1<maskset.tests[maskPos].length&&opts.keepStatic&&!strict&&!0!==fromAlternate&&(result=alternate.call(inputmask,!0)):result=alternate.call(inputmask,maskPos,c,strict,fromIsValid,void 0,pos),!0===result&&(result={pos:maskPos})}if("function"==typeof opts.postValidation&&!0!==fromIsValid&&!0!==validateOnly){var postResult=opts.postValidation.call(inputmask,_positioning.getBuffer.call(inputmask,!0),void 0!==pos.begin?inputmask.isRTL?pos.end:pos.begin:pos,c,result,opts,maskset,strict,fromCheckval);void 0!==postResult&&(result=!0===postResult?result:postResult)}result&&void 0===result.pos&&(result.pos=maskPos),!1===result||!0===validateOnly?(_positioning.resetMaskSet.call(inputmask,!0),maskset.validPositions=$.extend(!0,{},positionsClone)):trackbackPositions.call(inputmask,void 0,maskPos,!0);var endResult=processCommandObject(result);return endResult}function positionCanMatchDefinition(pos,testDefinition,opts){for(var inputmask=this,maskset=this.maskset,valid=!1,tests=_validationTests.getTests.call(this,pos),tndx=0;tndx<tests.length;tndx++){if(tests[tndx].match&&(tests[tndx].match.nativeDef===testDefinition.match[opts.shiftPositions?"def":"nativeDef"]&&(!opts.shiftPositions||!testDefinition.match.static)||tests[tndx].match.nativeDef===testDefinition.match.nativeDef||opts.regex&&!tests[tndx].match.static&&tests[tndx].match.fn.test(testDefinition.input))){valid=!0;break}if(tests[tndx].match&&tests[tndx].match.def===testDefinition.match.nativeDef){valid=void 0;break}}return!1===valid&&void 0!==maskset.jitOffset[pos]&&(valid=positionCanMatchDefinition.call(this,pos+maskset.jitOffset[pos],testDefinition,opts)),valid}function refreshFromBuffer(start,end,buffer){var inputmask=this,maskset=this.maskset,opts=this.opts,$=this.dependencyLib,i,p,skipOptionalPartCharacter=opts.skipOptionalPartCharacter,bffr=this.isRTL?buffer.slice().reverse():buffer;if(opts.skipOptionalPartCharacter="",!0===start)_positioning.resetMaskSet.call(this),maskset.tests={},start=0,end=buffer.length,p=_positioning.determineNewCaretPosition.call(this,{begin:0,end:0},!1).begin;else{for(i=start;i<end;i++)delete maskset.validPositions[i];p=start}var keypress=new $.Event("keypress");for(i=start;i<end;i++){keypress.which=bffr[i].toString().charCodeAt(0),this.ignorable=!1;var valResult=_eventhandlers.EventHandlers.keypressEvent.call(this,keypress,!0,!1,!1,p);!1!==valResult&&(p=valResult.forwardPosition)}opts.skipOptionalPartCharacter=skipOptionalPartCharacter}function trackbackPositions(originalPos,newPos,fillOnly){var inputmask=this,maskset=this.maskset,$=this.dependencyLib;if(void 0===originalPos)for(originalPos=newPos-1;0<originalPos&&!maskset.validPositions[originalPos];originalPos--);for(var ps=originalPos;ps<newPos;ps++)if(void 0===maskset.validPositions[ps]&&!_positioning.isMask.call(this,ps,!1)){var vp=0==ps?_validationTests.getTest.call(this,ps):maskset.validPositions[ps-1];if(vp){var tests=_validationTests.getTests.call(this,ps).slice();""===tests[tests.length-1].match.def&&tests.pop();var bestMatch=_validationTests.determineTestTemplate.call(this,ps,tests),np;if(bestMatch&&(!0!==bestMatch.match.jit||"master"===bestMatch.match.newBlockMarker&&(np=maskset.validPositions[ps+1])&&!0===np.match.optionalQuantifier)&&(bestMatch=$.extend({},bestMatch,{input:_validationTests.getPlaceholder.call(this,ps,bestMatch.match,!0)||bestMatch.match.def}),bestMatch.generatedInput=!0,revalidateMask.call(this,ps,bestMatch,!0),!0!==fillOnly)){var cvpInput=maskset.validPositions[newPos].input;return maskset.validPositions[newPos]=void 0,isValid.call(this,newPos,cvpInput,!0,!0)}}}}function revalidateMask(pos,validTest,fromIsValid,validatedPos){var inputmask=this,maskset=this.maskset,opts=this.opts,$=this.dependencyLib;function IsEnclosedStatic(pos,valids,selection){var posMatch=valids[pos];if(void 0===posMatch||!0!==posMatch.match.static||!0===posMatch.match.optionality||void 0!==valids[0]&&void 0!==valids[0].alternation)return!1;var prevMatch=selection.begin<=pos-1?valids[pos-1]&&!0===valids[pos-1].match.static&&valids[pos-1]:valids[pos-1],nextMatch=selection.end>pos+1?valids[pos+1]&&!0===valids[pos+1].match.static&&valids[pos+1]:valids[pos+1];return prevMatch&&nextMatch}var offset=0,begin=void 0!==pos.begin?pos.begin:pos,end=void 0!==pos.end?pos.end:pos;if(pos.begin>pos.end&&(begin=pos.end,end=pos.begin),validatedPos=void 0!==validatedPos?validatedPos:begin,begin!==end||opts.insertMode&&void 0!==maskset.validPositions[validatedPos]&&void 0===fromIsValid||void 0===validTest){var positionsClone=$.extend(!0,{},maskset.validPositions),lvp=_positioning.getLastValidPosition.call(this,void 0,!0),i;for(maskset.p=begin,i=lvp;begin<=i;i--)delete maskset.validPositions[i],void 0===validTest&&delete maskset.tests[i+1];var valid=!0,j=validatedPos,posMatch=j,t,canMatch;for(validTest&&(maskset.validPositions[validatedPos]=$.extend(!0,{},validTest),posMatch++,j++),i=validTest?end:end-1;i<=lvp;i++){if(void 0!==(t=positionsClone[i])&&!0!==t.generatedInput&&(end<=i||begin<=i&&IsEnclosedStatic(i,positionsClone,{begin:begin,end:end}))){for(;""!==_validationTests.getTest.call(this,posMatch).match.def;){if(!1!==(canMatch=positionCanMatchDefinition.call(this,posMatch,t,opts))||"+"===t.match.def){"+"===t.match.def&&_positioning.getBuffer.call(this,!0);var result=isValid.call(this,posMatch,t.input,"+"!==t.match.def,"+"!==t.match.def);if(valid=!1!==result,j=(result.pos||posMatch)+1,!valid&&canMatch)break}else valid=!1;if(valid){void 0===validTest&&t.match.static&&i===pos.begin&&offset++;break}if(!valid&&posMatch>maskset.maskLength)break;posMatch++}""==_validationTests.getTest.call(this,posMatch).match.def&&(valid=!1),posMatch=j}if(!valid)break}if(!valid)return maskset.validPositions=$.extend(!0,{},positionsClone),_positioning.resetMaskSet.call(this,!0),!1}else validTest&&_validationTests.getTest.call(this,validatedPos).match.cd===validTest.match.cd&&(maskset.validPositions[validatedPos]=$.extend(!0,{},validTest));return _positioning.resetMaskSet.call(this,!0),offset}},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.applyInputValue=applyInputValue,exports.clearOptionalTail=clearOptionalTail,exports.checkVal=checkVal,exports.HandleNativePlaceholder=HandleNativePlaceholder,exports.unmaskedvalue=unmaskedvalue,exports.writeBuffer=writeBuffer;var _keycode=_interopRequireDefault(__webpack_require__(0)),_validationTests=__webpack_require__(3),_positioning=__webpack_require__(1),_validation=__webpack_require__(4),_environment=__webpack_require__(7),_eventhandlers=__webpack_require__(6);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}function applyInputValue(input,value){var inputmask=input?input.inputmask:this,opts=inputmask.opts;input.inputmask.refreshValue=!1,"function"==typeof opts.onBeforeMask&&(value=opts.onBeforeMask.call(inputmask,value,opts)||value),value=value.toString().split(""),checkVal(input,!0,!1,value),inputmask.undoValue=_positioning.getBuffer.call(inputmask).join(""),(opts.clearMaskOnLostFocus||opts.clearIncomplete)&&input.inputmask._valueGet()===_positioning.getBufferTemplate.call(inputmask).join("")&&-1===_positioning.getLastValidPosition.call(inputmask)&&input.inputmask._valueSet("")}function clearOptionalTail(buffer){var inputmask=this;buffer.length=0;for(var template=_validationTests.getMaskTemplate.call(this,!0,0,!0,void 0,!0),lmnt;void 0!==(lmnt=template.shift());)buffer.push(lmnt);return buffer}function checkVal(input,writeOut,strict,nptvl,initiatingEvent){var inputmask=input?input.inputmask:this,maskset=inputmask.maskset,opts=inputmask.opts,$=inputmask.dependencyLib,inputValue=nptvl.slice(),charCodes="",initialNdx=-1,result=void 0,skipOptionalPartCharacter=opts.skipOptionalPartCharacter;function isTemplateMatch(ndx,charCodes){for(var targetTemplate=_validationTests.getMaskTemplate.call(inputmask,!0,0).slice(ndx,_positioning.seekNext.call(inputmask,ndx,!1,!1)).join("").replace(/'/g,""),charCodeNdx=targetTemplate.indexOf(charCodes);0<charCodeNdx&&" "===targetTemplate[charCodeNdx-1];)charCodeNdx--;var match=0===charCodeNdx&&!_positioning.isMask.call(inputmask,ndx)&&(_validationTests.getTest.call(inputmask,ndx).match.nativeDef===charCodes.charAt(0)||!0===_validationTests.getTest.call(inputmask,ndx).match.static&&_validationTests.getTest.call(inputmask,ndx).match.nativeDef==="'"+charCodes.charAt(0)||" "===_validationTests.getTest.call(inputmask,ndx).match.nativeDef&&(_validationTests.getTest.call(inputmask,ndx+1).match.nativeDef===charCodes.charAt(0)||!0===_validationTests.getTest.call(inputmask,ndx+1).match.static&&_validationTests.getTest.call(inputmask,ndx+1).match.nativeDef==="'"+charCodes.charAt(0)));if(!match&&0<charCodeNdx&&!_positioning.isMask.call(inputmask,ndx,!1,!0)){var nextPos=_positioning.seekNext.call(inputmask,ndx);inputmask.caretPos.begin<nextPos&&(inputmask.caretPos={begin:nextPos})}return match}opts.skipOptionalPartCharacter="",_positioning.resetMaskSet.call(inputmask),maskset.tests={},initialNdx=opts.radixPoint?_positioning.determineNewCaretPosition.call(inputmask,{begin:0,end:0},!1,!1===opts.__financeInput?"radixFocus":void 0).begin:0,maskset.p=initialNdx,inputmask.caretPos={begin:initialNdx};var staticMatches=[],prevCaretPos=inputmask.caretPos;if(inputValue.forEach(function(charCode,ndx){if(void 0!==charCode){var keypress=new $.Event("_checkval");keypress.which=charCode.toString().charCodeAt(0),charCodes+=charCode;var lvp=_positioning.getLastValidPosition.call(inputmask,void 0,!0);isTemplateMatch(initialNdx,charCodes)?result=_eventhandlers.EventHandlers.keypressEvent.call(inputmask,keypress,!0,!1,strict,lvp+1):(result=_eventhandlers.EventHandlers.keypressEvent.call(inputmask,keypress,!0,!1,strict,inputmask.caretPos.begin),result&&(initialNdx=inputmask.caretPos.begin+1,charCodes="")),result?(void 0!==result.pos&&maskset.validPositions[result.pos]&&!0===maskset.validPositions[result.pos].match.static&&void 0===maskset.validPositions[result.pos].alternation&&(staticMatches.push(result.pos),inputmask.isRTL||(result.forwardPosition=result.pos+1)),writeBuffer.call(inputmask,void 0,_positioning.getBuffer.call(inputmask),result.forwardPosition,keypress,!1),inputmask.caretPos={begin:result.forwardPosition,end:result.forwardPosition},prevCaretPos=inputmask.caretPos):void 0===maskset.validPositions[ndx]&&inputValue[ndx]===_validationTests.getPlaceholder.call(inputmask,ndx)&&_positioning.isMask.call(inputmask,ndx,!0)?inputmask.caretPos.begin++:inputmask.caretPos=prevCaretPos}}),0<staticMatches.length){var sndx,validPos,nextValid=_positioning.seekNext.call(inputmask,-1,void 0,!1);if(!_validation.isComplete.call(inputmask,_positioning.getBuffer.call(inputmask))&&staticMatches.length<=nextValid||_validation.isComplete.call(inputmask,_positioning.getBuffer.call(inputmask))&&0<staticMatches.length&&staticMatches.length!==nextValid&&0===staticMatches[0])for(var nextSndx=nextValid;void 0!==(sndx=staticMatches.shift());){var keypress=new $.Event("_checkval");if(validPos=maskset.validPositions[sndx],validPos.generatedInput=!0,keypress.which=validPos.input.charCodeAt(0),result=_eventhandlers.EventHandlers.keypressEvent.call(inputmask,keypress,!0,!1,strict,nextSndx),result&&void 0!==result.pos&&result.pos!==sndx&&maskset.validPositions[result.pos]&&!0===maskset.validPositions[result.pos].match.static)staticMatches.push(result.pos);else if(!result)break;nextSndx++}}writeOut&&writeBuffer.call(inputmask,input,_positioning.getBuffer.call(inputmask),result?result.forwardPosition:inputmask.caretPos.begin,initiatingEvent||new $.Event("checkval"),initiatingEvent&&"input"===initiatingEvent.type&&inputmask.undoValue!==_positioning.getBuffer.call(inputmask).join("")),opts.skipOptionalPartCharacter=skipOptionalPartCharacter}function HandleNativePlaceholder(npt,value){var inputmask=npt?npt.inputmask:this;if(_environment.ie){if(npt.inputmask._valueGet()!==value&&(npt.placeholder!==value||""===npt.placeholder)){var buffer=_positioning.getBuffer.call(inputmask).slice(),nptValue=npt.inputmask._valueGet();if(nptValue!==value){var lvp=_positioning.getLastValidPosition.call(inputmask);-1===lvp&&nptValue===_positioning.getBufferTemplate.call(inputmask).join("")?buffer=[]:-1!==lvp&&clearOptionalTail.call(inputmask,buffer),writeBuffer(npt,buffer)}}}else npt.placeholder!==value&&(npt.placeholder=value,""===npt.placeholder&&npt.removeAttribute("placeholder"))}function unmaskedvalue(input){var inputmask=input?input.inputmask:this,opts=inputmask.opts,maskset=inputmask.maskset;if(input){if(void 0===input.inputmask)return input.value;input.inputmask&&input.inputmask.refreshValue&&applyInputValue(input,input.inputmask._valueGet(!0))}var umValue=[],vps=maskset.validPositions;for(var pndx in vps)vps[pndx]&&vps[pndx].match&&(1!=vps[pndx].match.static||Array.isArray(maskset.metadata)&&!0!==vps[pndx].generatedInput)&&umValue.push(vps[pndx].input);var unmaskedValue=0===umValue.length?"":(inputmask.isRTL?umValue.reverse():umValue).join("");if("function"==typeof opts.onUnMask){var bufferValue=(inputmask.isRTL?_positioning.getBuffer.call(inputmask).slice().reverse():_positioning.getBuffer.call(inputmask)).join("");unmaskedValue=opts.onUnMask.call(inputmask,bufferValue,unmaskedValue,opts)}return unmaskedValue}function writeBuffer(input,buffer,caretPos,event,triggerEvents){var inputmask=input?input.inputmask:this,opts=inputmask.opts,$=inputmask.dependencyLib;if(event&&"function"==typeof opts.onBeforeWrite){var result=opts.onBeforeWrite.call(inputmask,event,buffer,caretPos,opts);if(result){if(result.refreshFromBuffer){var refresh=result.refreshFromBuffer;_validation.refreshFromBuffer.call(inputmask,!0===refresh?refresh:refresh.start,refresh.end,result.buffer||buffer),buffer=_positioning.getBuffer.call(inputmask,!0)}void 0!==caretPos&&(caretPos=void 0!==result.caret?result.caret:caretPos)}}if(void 0!==input&&(input.inputmask._valueSet(buffer.join("")),void 0===caretPos||void 0!==event&&"blur"===event.type||_positioning.caret.call(inputmask,input,caretPos,void 0,void 0,void 0!==event&&"keydown"===event.type&&(event.keyCode===_keycode.default.DELETE||event.keyCode===_keycode.default.BACKSPACE)),!0===triggerEvents)){var $input=$(input),nptVal=input.inputmask._valueGet();input.inputmask.skipInputEvent=!0,$input.trigger("input"),setTimeout(function(){nptVal===_positioning.getBufferTemplate.call(inputmask).join("")?$input.trigger("cleared"):!0===_validation.isComplete.call(inputmask,buffer)&&$input.trigger("complete")},0)}}},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.EventHandlers=void 0;var _positioning=__webpack_require__(1),_keycode=_interopRequireDefault(__webpack_require__(0)),_environment=__webpack_require__(7),_validation=__webpack_require__(4),_inputHandling=__webpack_require__(5),_validationTests=__webpack_require__(3);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var EventHandlers={keydownEvent:function keydownEvent(e){var inputmask=this.inputmask,opts=inputmask.opts,$=inputmask.dependencyLib,maskset=inputmask.maskset,input=this,$input=$(input),k=e.keyCode,pos=_positioning.caret.call(inputmask,input),kdResult=opts.onKeyDown.call(this,e,_positioning.getBuffer.call(inputmask),pos,opts);if(void 0!==kdResult)return kdResult;if(k===_keycode.default.BACKSPACE||k===_keycode.default.DELETE||_environment.iphone&&k===_keycode.default.BACKSPACE_SAFARI||e.ctrlKey&&k===_keycode.default.X&&!("oncut"in input))e.preventDefault(),_validation.handleRemove.call(inputmask,input,k,pos),(0,_inputHandling.writeBuffer)(input,_positioning.getBuffer.call(inputmask,!0),maskset.p,e,input.inputmask._valueGet()!==_positioning.getBuffer.call(inputmask).join(""));else if(k===_keycode.default.END||k===_keycode.default.PAGE_DOWN){e.preventDefault();var caretPos=_positioning.seekNext.call(inputmask,_positioning.getLastValidPosition.call(inputmask));_positioning.caret.call(inputmask,input,e.shiftKey?pos.begin:caretPos,caretPos,!0)}else k===_keycode.default.HOME&&!e.shiftKey||k===_keycode.default.PAGE_UP?(e.preventDefault(),_positioning.caret.call(inputmask,input,0,e.shiftKey?pos.begin:0,!0)):(opts.undoOnEscape&&k===_keycode.default.ESCAPE||90===k&&e.ctrlKey)&&!0!==e.altKey?((0,_inputHandling.checkVal)(input,!0,!1,inputmask.undoValue.split("")),$input.trigger("click")):!0===opts.tabThrough&&k===_keycode.default.TAB?!0===e.shiftKey?(pos.end=_positioning.seekPrevious.call(inputmask,pos.end,!0),!0===_validationTests.getTest.call(inputmask,pos.end-1).match.static&&pos.end--,pos.begin=_positioning.seekPrevious.call(inputmask,pos.end,!0),0<=pos.begin&&0<pos.end&&(e.preventDefault(),_positioning.caret.call(inputmask,input,pos.begin,pos.end))):(pos.begin=_positioning.seekNext.call(inputmask,pos.begin,!0),pos.end=_positioning.seekNext.call(inputmask,pos.begin,!0),pos.end<maskset.maskLength&&pos.end--,pos.begin<=maskset.maskLength&&(e.preventDefault(),_positioning.caret.call(inputmask,input,pos.begin,pos.end))):e.shiftKey||opts.insertModeVisual&&!1===opts.insertMode&&(k===_keycode.default.RIGHT?setTimeout(function(){var caretPos=_positioning.caret.call(inputmask,input);_positioning.caret.call(inputmask,input,caretPos.begin)},0):k===_keycode.default.LEFT&&setTimeout(function(){var caretPos_begin=_positioning.translatePosition.call(inputmask,input.inputmask.caretPos.begin),caretPos_end=_positioning.translatePosition.call(inputmask,input.inputmask.caretPos.end);inputmask.isRTL?_positioning.caret.call(inputmask,input,caretPos_begin+(caretPos_begin===maskset.maskLength?0:1)):_positioning.caret.call(inputmask,input,caretPos_begin-(0===caretPos_begin?0:1))},0));inputmask.ignorable=opts.ignorables.includes(k)},keypressEvent:function keypressEvent(e,checkval,writeOut,strict,ndx){var inputmask=this.inputmask||this,opts=inputmask.opts,$=inputmask.dependencyLib,maskset=inputmask.maskset,input=inputmask.el,$input=$(input),k=e.which||e.charCode||e.keyCode;if(!(!0===checkval||e.ctrlKey&&e.altKey)&&(e.ctrlKey||e.metaKey||inputmask.ignorable))return k===_keycode.default.ENTER&&inputmask.undoValue!==_positioning.getBuffer.call(inputmask).join("")&&(inputmask.undoValue=_positioning.getBuffer.call(inputmask).join(""),setTimeout(function(){$input.trigger("change")},0)),inputmask.skipInputEvent=!0,!0;if(k){44!==k&&46!==k||3!==e.location||""===opts.radixPoint||(k=opts.radixPoint.charCodeAt(0));var pos=checkval?{begin:ndx,end:ndx}:_positioning.caret.call(inputmask,input),forwardPosition,c=String.fromCharCode(k);maskset.writeOutBuffer=!0;var valResult=_validation.isValid.call(inputmask,pos,c,strict,void 0,void 0,void 0,checkval);if(!1!==valResult&&(_positioning.resetMaskSet.call(inputmask,!0),forwardPosition=void 0!==valResult.caret?valResult.caret:_positioning.seekNext.call(inputmask,valResult.pos.begin?valResult.pos.begin:valResult.pos),maskset.p=forwardPosition),forwardPosition=opts.numericInput&&void 0===valResult.caret?_positioning.seekPrevious.call(inputmask,forwardPosition):forwardPosition,!1!==writeOut&&(setTimeout(function(){opts.onKeyValidation.call(input,k,valResult)},0),maskset.writeOutBuffer&&!1!==valResult)){var buffer=_positioning.getBuffer.call(inputmask);(0,_inputHandling.writeBuffer)(input,buffer,forwardPosition,e,!0!==checkval)}if(e.preventDefault(),checkval)return!1!==valResult&&(valResult.forwardPosition=forwardPosition),valResult}},keyupEvent:function keyupEvent(e){var inputmask=this.inputmask;!inputmask.isComposing||e.keyCode!==_keycode.default.KEY_229&&e.keyCode!==_keycode.default.ENTER||inputmask.$el.trigger("input")},pasteEvent:function pasteEvent(e){var inputmask=this.inputmask,opts=inputmask.opts,input=this,inputValue=inputmask._valueGet(!0),caretPos=_positioning.caret.call(inputmask,this),tempValue;inputmask.isRTL&&(tempValue=caretPos.end,caretPos.end=caretPos.begin,caretPos.begin=tempValue);var valueBeforeCaret=inputValue.substr(0,caretPos.begin),valueAfterCaret=inputValue.substr(caretPos.end,inputValue.length);if(valueBeforeCaret==(inputmask.isRTL?_positioning.getBufferTemplate.call(inputmask).slice().reverse():_positioning.getBufferTemplate.call(inputmask)).slice(0,caretPos.begin).join("")&&(valueBeforeCaret=""),valueAfterCaret==(inputmask.isRTL?_positioning.getBufferTemplate.call(inputmask).slice().reverse():_positioning.getBufferTemplate.call(inputmask)).slice(caretPos.end).join("")&&(valueAfterCaret=""),window.clipboardData&&window.clipboardData.getData)inputValue=valueBeforeCaret+window.clipboardData.getData("Text")+valueAfterCaret;else{if(!e.clipboardData||!e.clipboardData.getData)return!0;inputValue=valueBeforeCaret+e.clipboardData.getData("text/plain")+valueAfterCaret}var pasteValue=inputValue;if("function"==typeof opts.onBeforePaste){if(pasteValue=opts.onBeforePaste.call(inputmask,inputValue,opts),!1===pasteValue)return e.preventDefault();pasteValue=pasteValue||inputValue}return(0,_inputHandling.checkVal)(this,!0,!1,pasteValue.toString().split(""),e),e.preventDefault()},inputFallBackEvent:function inputFallBackEvent(e){var inputmask=this.inputmask,opts=inputmask.opts,$=inputmask.dependencyLib;function ieMobileHandler(input,inputValue,caretPos){if(_environment.iemobile){var inputChar=inputValue.replace(_positioning.getBuffer.call(inputmask).join(""),"");if(1===inputChar.length){var iv=inputValue.split("");iv.splice(caretPos.begin,0,inputChar),inputValue=iv.join("")}}return inputValue}function analyseChanges(inputValue,buffer,caretPos){for(var frontPart=inputValue.substr(0,caretPos.begin).split(""),backPart=inputValue.substr(caretPos.begin).split(""),frontBufferPart=buffer.substr(0,caretPos.begin).split(""),backBufferPart=buffer.substr(caretPos.begin).split(""),fpl=frontPart.length>=frontBufferPart.length?frontPart.length:frontBufferPart.length,bpl=backPart.length>=backBufferPart.length?backPart.length:backBufferPart.length,bl,i,action="",data=[],marker="~",placeholder;frontPart.length<fpl;)frontPart.push("~");for(;frontBufferPart.length<fpl;)frontBufferPart.push("~");for(;backPart.length<bpl;)backPart.unshift("~");for(;backBufferPart.length<bpl;)backBufferPart.unshift("~");var newBuffer=frontPart.concat(backPart),oldBuffer=frontBufferPart.concat(backBufferPart);for(i=0,bl=newBuffer.length;i<bl;i++)switch(placeholder=_validationTests.getPlaceholder.call(inputmask,_positioning.translatePosition.call(inputmask,i)),action){case"insertText":oldBuffer[i-1]===newBuffer[i]&&caretPos.begin==newBuffer.length-1&&data.push(newBuffer[i]),i=bl;break;case"insertReplacementText":"~"===newBuffer[i]?caretPos.end++:i=bl;break;case"deleteContentBackward":"~"===newBuffer[i]?caretPos.end++:i=bl;break;default:newBuffer[i]!==oldBuffer[i]&&("~"!==newBuffer[i+1]&&newBuffer[i+1]!==placeholder&&void 0!==newBuffer[i+1]||(oldBuffer[i]!==placeholder||"~"!==oldBuffer[i+1])&&"~"!==oldBuffer[i]?"~"===oldBuffer[i+1]&&oldBuffer[i]===newBuffer[i+1]?(action="insertText",data.push(newBuffer[i]),caretPos.begin--,caretPos.end--):newBuffer[i]!==placeholder&&"~"!==newBuffer[i]&&("~"===newBuffer[i+1]||oldBuffer[i]!==newBuffer[i]&&oldBuffer[i+1]===newBuffer[i+1])?(action="insertReplacementText",data.push(newBuffer[i]),caretPos.begin--):"~"===newBuffer[i]?(action="deleteContentBackward",!_positioning.isMask.call(inputmask,_positioning.translatePosition.call(inputmask,i),!0)&&oldBuffer[i]!==opts.radixPoint||caretPos.end++):i=bl:(action="insertText",data.push(newBuffer[i]),caretPos.begin--,caretPos.end--));break}return{action:action,data:data,caret:caretPos}}var input=this,inputValue=input.inputmask._valueGet(!0),buffer=(inputmask.isRTL?_positioning.getBuffer.call(inputmask).slice().reverse():_positioning.getBuffer.call(inputmask)).join(""),caretPos=_positioning.caret.call(inputmask,input,void 0,void 0,!0);if(buffer!==inputValue){inputValue=ieMobileHandler(input,inputValue,caretPos);var changes=analyseChanges(inputValue,buffer,caretPos);switch((input.inputmask.shadowRoot||input.ownerDocument).activeElement!==input&&input.focus(),(0,_inputHandling.writeBuffer)(input,_positioning.getBuffer.call(inputmask)),_positioning.caret.call(inputmask,input,caretPos.begin,caretPos.end,!0),changes.action){case"insertText":case"insertReplacementText":changes.data.forEach(function(entry,ndx){var keypress=new $.Event("keypress");keypress.which=entry.charCodeAt(0),inputmask.ignorable=!1,EventHandlers.keypressEvent.call(input,keypress)}),setTimeout(function(){inputmask.$el.trigger("keyup")},0);break;case"deleteContentBackward":var keydown=new $.Event("keydown");keydown.keyCode=_keycode.default.BACKSPACE,EventHandlers.keydownEvent.call(input,keydown);break;default:(0,_inputHandling.applyInputValue)(input,inputValue);break}e.preventDefault()}},compositionendEvent:function compositionendEvent(e){var inputmask=this.inputmask;inputmask.isComposing=!1,inputmask.$el.trigger("input")},setValueEvent:function setValueEvent(e,argument_1,argument_2){var inputmask=this.inputmask,input=this,value=e&&e.detail?e.detail[0]:argument_1;void 0===value&&(value=this.inputmask._valueGet(!0)),(0,_inputHandling.applyInputValue)(this,value),(e.detail&&void 0!==e.detail[1]||void 0!==argument_2)&&_positioning.caret.call(inputmask,this,e.detail?e.detail[1]:argument_2)},focusEvent:function focusEvent(e){var inputmask=this.inputmask,opts=inputmask.opts,input=this,nptValue=this.inputmask._valueGet();opts.showMaskOnFocus&&nptValue!==_positioning.getBuffer.call(inputmask).join("")&&(0,_inputHandling.writeBuffer)(this,_positioning.getBuffer.call(inputmask),_positioning.seekNext.call(inputmask,_positioning.getLastValidPosition.call(inputmask))),!0!==opts.positionCaretOnTab||!1!==inputmask.mouseEnter||_validation.isComplete.call(inputmask,_positioning.getBuffer.call(inputmask))&&-1!==_positioning.getLastValidPosition.call(inputmask)||EventHandlers.clickEvent.apply(this,[e,!0]),inputmask.undoValue=_positioning.getBuffer.call(inputmask).join("")},invalidEvent:function invalidEvent(e){this.inputmask.validationEvent=!0},mouseleaveEvent:function mouseleaveEvent(){var inputmask=this.inputmask,opts=inputmask.opts,input=this;inputmask.mouseEnter=!1,opts.clearMaskOnLostFocus&&(this.inputmask.shadowRoot||this.ownerDocument).activeElement!==this&&(0,_inputHandling.HandleNativePlaceholder)(this,inputmask.originalPlaceholder)},clickEvent:function clickEvent(e,tabbed){var inputmask=this.inputmask,input=this;if((this.inputmask.shadowRoot||this.ownerDocument).activeElement===this){var newCaretPosition=_positioning.determineNewCaretPosition.call(inputmask,_positioning.caret.call(inputmask,this),tabbed);void 0!==newCaretPosition&&_positioning.caret.call(inputmask,this,newCaretPosition)}},cutEvent:function cutEvent(e){var inputmask=this.inputmask,maskset=inputmask.maskset,input=this,pos=_positioning.caret.call(inputmask,this),clipboardData=window.clipboardData||e.clipboardData,clipData=inputmask.isRTL?_positioning.getBuffer.call(inputmask).slice(pos.end,pos.begin):_positioning.getBuffer.call(inputmask).slice(pos.begin,pos.end);clipboardData.setData("text",inputmask.isRTL?clipData.reverse().join(""):clipData.join("")),document.execCommand&&document.execCommand("copy"),_validation.handleRemove.call(inputmask,this,_keycode.default.DELETE,pos),(0,_inputHandling.writeBuffer)(this,_positioning.getBuffer.call(inputmask),maskset.p,e,inputmask.undoValue!==_positioning.getBuffer.call(inputmask).join(""))},blurEvent:function blurEvent(e){var inputmask=this.inputmask,opts=inputmask.opts,$=inputmask.dependencyLib,$input=$(this),input=this;if(this.inputmask){(0,_inputHandling.HandleNativePlaceholder)(this,inputmask.originalPlaceholder);var nptValue=this.inputmask._valueGet(),buffer=_positioning.getBuffer.call(inputmask).slice();""!==nptValue&&(opts.clearMaskOnLostFocus&&(-1===_positioning.getLastValidPosition.call(inputmask)&&nptValue===_positioning.getBufferTemplate.call(inputmask).join("")?buffer=[]:_inputHandling.clearOptionalTail.call(inputmask,buffer)),!1===_validation.isComplete.call(inputmask,buffer)&&(setTimeout(function(){$input.trigger("incomplete")},0),opts.clearIncomplete&&(_positioning.resetMaskSet.call(inputmask),buffer=opts.clearMaskOnLostFocus?[]:_positioning.getBufferTemplate.call(inputmask).slice())),(0,_inputHandling.writeBuffer)(this,buffer,void 0,e)),inputmask.undoValue!==_positioning.getBuffer.call(inputmask).join("")&&(inputmask.undoValue=_positioning.getBuffer.call(inputmask).join(""),$input.trigger("change"))}},mouseenterEvent:function mouseenterEvent(){var inputmask=this.inputmask,opts=inputmask.opts,input=this;if(inputmask.mouseEnter=!0,(this.inputmask.shadowRoot||this.ownerDocument).activeElement!==this){var bufferTemplate=(inputmask.isRTL?_positioning.getBufferTemplate.call(inputmask).slice().reverse():_positioning.getBufferTemplate.call(inputmask)).join("");inputmask.placeholder!==bufferTemplate&&this.placeholder!==inputmask.originalPlaceholder&&(inputmask.originalPlaceholder=this.placeholder),opts.showMaskOnHover&&(0,_inputHandling.HandleNativePlaceholder)(this,bufferTemplate)}},submitEvent:function submitEvent(){var inputmask=this.inputmask,opts=inputmask.opts;inputmask.undoValue!==_positioning.getBuffer.call(inputmask).join("")&&inputmask.$el.trigger("change"),opts.clearMaskOnLostFocus&&-1===_positioning.getLastValidPosition.call(inputmask)&&inputmask._valueGet&&inputmask._valueGet()===_positioning.getBufferTemplate.call(inputmask).join("")&&inputmask._valueSet(""),opts.clearIncomplete&&!1===_validation.isComplete.call(inputmask,_positioning.getBuffer.call(inputmask))&&inputmask._valueSet(""),opts.removeMaskOnSubmit&&(inputmask._valueSet(inputmask.unmaskedvalue(),!0),setTimeout(function(){(0,_inputHandling.writeBuffer)(inputmask.el,_positioning.getBuffer.call(inputmask))},0))},resetEvent:function resetEvent(){var inputmask=this.inputmask;inputmask.refreshValue=!0,setTimeout(function(){(0,_inputHandling.applyInputValue)(inputmask.el,inputmask._valueGet(!0))},0)}};exports.EventHandlers=EventHandlers},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.iphone=exports.iemobile=exports.mobile=exports.ie=exports.ua=void 0;var _window=_interopRequireDefault(__webpack_require__(8));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var ua=_window.default.navigator&&_window.default.navigator.userAgent||"",ie=0<ua.indexOf("MSIE ")||0<ua.indexOf("Trident/"),mobile="ontouchstart"in _window.default,iemobile=/iemobile/i.test(ua),iphone=/iphone/i.test(ua)&&!iemobile;exports.iphone=iphone,exports.iemobile=iemobile,exports.mobile=mobile,exports.ie=ie,exports.ua=ua},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _canUseDOM=_interopRequireDefault(__webpack_require__(9));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var _default=_canUseDOM.default?window:{};exports.default=_default},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var canUseDOM=!("undefined"==typeof window||!window.document||!window.document.createElement),_default=canUseDOM;exports.default=_default},function(module,exports){module.exports=__WEBPACK_EXTERNAL_MODULE__10__},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.EventRuler=void 0;var _inputmask=_interopRequireDefault(__webpack_require__(2)),_keycode=_interopRequireDefault(__webpack_require__(0)),_positioning=__webpack_require__(1),_inputHandling=__webpack_require__(5);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var EventRuler={on:function on(input,eventName,eventHandler){var $=input.inputmask.dependencyLib,ev=function ev(e){e.originalEvent&&(e=e.originalEvent||e,arguments[0]=e);var that=this,args,inputmask=that.inputmask,opts=inputmask?inputmask.opts:void 0;if(void 0===inputmask&&"FORM"!==this.nodeName){var imOpts=$.data(that,"_inputmask_opts");$(that).off(),imOpts&&new _inputmask.default(imOpts).mask(that)}else{if(["submit","reset","setvalue"].includes(e.type)||"FORM"===this.nodeName||!(that.disabled||that.readOnly&&!("keydown"===e.type&&e.ctrlKey&&67===e.keyCode||!1===opts.tabThrough&&e.keyCode===_keycode.default.TAB))){switch(e.type){case"input":if(!0===inputmask.skipInputEvent||e.inputType&&"insertCompositionText"===e.inputType)return inputmask.skipInputEvent=!1,e.preventDefault();break;case"keydown":inputmask.skipKeyPressEvent=!1,inputmask.skipInputEvent=inputmask.isComposing=e.keyCode===_keycode.default.KEY_229;break;case"keyup":case"compositionend":inputmask.isComposing&&(inputmask.skipInputEvent=!1);break;case"keypress":if(!0===inputmask.skipKeyPressEvent)return e.preventDefault();inputmask.skipKeyPressEvent=!0;break;case"click":case"focus":return inputmask.validationEvent?(inputmask.validationEvent=!1,input.blur(),(0,_inputHandling.HandleNativePlaceholder)(input,(inputmask.isRTL?_positioning.getBufferTemplate.call(inputmask).slice().reverse():_positioning.getBufferTemplate.call(inputmask)).join("")),setTimeout(function(){input.focus()},3e3)):(args=arguments,setTimeout(function(){input.inputmask&&eventHandler.apply(that,args)},0)),!1}var returnVal=eventHandler.apply(that,arguments);return!1===returnVal&&(e.preventDefault(),e.stopPropagation()),returnVal}e.preventDefault()}};["submit","reset"].includes(eventName)?(ev=ev.bind(input),null!==input.form&&$(input.form).on(eventName,ev)):$(input).on(eventName,ev),input.inputmask.events[eventName]=input.inputmask.events[eventName]||[],input.inputmask.events[eventName].push(ev)},off:function off(input,event){if(input.inputmask&&input.inputmask.events){var $=input.inputmask.dependencyLib,events=input.inputmask.events;for(var eventName in event&&(events=[],events[event]=input.inputmask.events[event]),events){for(var evArr=events[eventName];0<evArr.length;){var ev=evArr.pop();["submit","reset"].includes(eventName)?null!==input.form&&$(input.form).off(eventName,ev):$(input).off(eventName,ev)}delete input.inputmask.events[eventName]}}}};exports.EventRuler=EventRuler},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _jquery=_interopRequireDefault(__webpack_require__(10));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}if(void 0===_jquery.default)throw"jQuery not loaded!";var _default=_jquery.default;exports.default=_default},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=_default;var escapeRegexRegex=new RegExp("(\\"+["/",".","*","+","?","|","(",")","[","]","{","}","\\","$","^"].join("|\\")+")","gim");function _default(str){return str.replace(escapeRegexRegex,"\\$1")}},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0,__webpack_require__(15),__webpack_require__(23),__webpack_require__(24),__webpack_require__(25);var _inputmask2=_interopRequireDefault(__webpack_require__(2));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var _default=_inputmask2.default;exports.default=_default},function(module,exports,__webpack_require__){"use strict";var _inputmask=_interopRequireDefault(__webpack_require__(2)),_positioning=__webpack_require__(1),_validationTests=__webpack_require__(3);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}_inputmask.default.extendDefinitions({A:{validator:"[A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",casing:"upper"},"&":{validator:"[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",casing:"upper"},"#":{validator:"[0-9A-Fa-f]",casing:"upper"}});var ipValidatorRegex=new RegExp("25[0-5]|2[0-4][0-9]|[01][0-9][0-9]");function ipValidator(chrs,maskset,pos,strict,opts){return chrs=-1<pos-1&&"."!==maskset.buffer[pos-1]?(chrs=maskset.buffer[pos-1]+chrs,-1<pos-2&&"."!==maskset.buffer[pos-2]?maskset.buffer[pos-2]+chrs:"0"+chrs):"00"+chrs,ipValidatorRegex.test(chrs)}_inputmask.default.extendAliases({cssunit:{regex:"[+-]?[0-9]+\\.?([0-9]+)?(px|em|rem|ex|%|in|cm|mm|pt|pc)"},url:{regex:"(https?|ftp)://.*",autoUnmask:!1,keepStatic:!1,tabThrough:!0},ip:{mask:"i[i[i]].j[j[j]].k[k[k]].l[l[l]]",definitions:{i:{validator:ipValidator},j:{validator:ipValidator},k:{validator:ipValidator},l:{validator:ipValidator}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return maskedValue},inputmode:"numeric"},email:{mask:"*{1,64}[.*{1,64}][.*{1,64}][.*{1,63}]@-{1,63}.-{1,63}[.-{1,63}][.-{1,63}]",greedy:!1,casing:"lower",onBeforePaste:function onBeforePaste(pastedValue,opts){return pastedValue=pastedValue.toLowerCase(),pastedValue.replace("mailto:","")},definitions:{"*":{validator:"[0-9\uff11-\uff19A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5!#$%&'*+/=?^_`{|}~-]"},"-":{validator:"[0-9A-Za-z-]"}},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return maskedValue},inputmode:"email"},mac:{mask:"##:##:##:##:##:##"},vin:{mask:"V{13}9{4}",definitions:{V:{validator:"[A-HJ-NPR-Za-hj-npr-z\\d]",casing:"upper"}},clearIncomplete:!0,autoUnmask:!0},ssn:{mask:"999-99-9999",postValidation:function postValidation(buffer,pos,c,currentResult,opts,maskset,strict){var bffr=_validationTests.getMaskTemplate.call(this,!0,_positioning.getLastValidPosition.call(this),!0,!0);return/^(?!219-09-9999|078-05-1120)(?!666|000|9.{2}).{3}-(?!00).{2}-(?!0{4}).{4}$/.test(bffr.join(""))}}})},function(module,exports,__webpack_require__){"use strict";function _typeof(obj){return _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function _typeof(obj){return typeof obj}:function _typeof(obj){return obj&&"function"==typeof Symbol&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj},_typeof(obj)}"function"!=typeof Object.getPrototypeOf&&(Object.getPrototypeOf="object"===_typeof("test".__proto__)?function(object){return object.__proto__}:function(object){return object.constructor.prototype})},function(module,exports,__webpack_require__){"use strict";Array.prototype.includes||Object.defineProperty(Array.prototype,"includes",{value:function value(searchElement,fromIndex){if(null==this)throw new TypeError('"this" is null or not defined');var o=Object(this),len=o.length>>>0;if(0==len)return!1;for(var n=0|fromIndex,k=Math.max(0<=n?n:len-Math.abs(n),0);k<len;){if(o[k]===searchElement)return!0;k++}return!1}})},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.mask=mask;var _keycode=_interopRequireDefault(__webpack_require__(0)),_positioning=__webpack_require__(1),_inputHandling=__webpack_require__(5),_eventruler=__webpack_require__(11),_environment=__webpack_require__(7),_validation=__webpack_require__(4),_eventhandlers=__webpack_require__(6);function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}function mask(){var inputmask=this,opts=this.opts,el=this.el,$=this.dependencyLib;function isElementTypeSupported(input,opts){function patchValueProperty(npt){var valueGet,valueSet;function patchValhook(type){if($.valHooks&&(void 0===$.valHooks[type]||!0!==$.valHooks[type].inputmaskpatch)){var valhookGet=$.valHooks[type]&&$.valHooks[type].get?$.valHooks[type].get:function(elem){return elem.value},valhookSet=$.valHooks[type]&&$.valHooks[type].set?$.valHooks[type].set:function(elem,value){return elem.value=value,elem};$.valHooks[type]={get:function get(elem){if(elem.inputmask){if(elem.inputmask.opts.autoUnmask)return elem.inputmask.unmaskedvalue();var result=valhookGet(elem);return-1!==_positioning.getLastValidPosition.call(inputmask,void 0,void 0,elem.inputmask.maskset.validPositions)||!0!==opts.nullable?result:""}return valhookGet(elem)},set:function set(elem,value){var result=valhookSet(elem,value);return elem.inputmask&&(0,_inputHandling.applyInputValue)(elem,value),result},inputmaskpatch:!0}}}function getter(){return this.inputmask?this.inputmask.opts.autoUnmask?this.inputmask.unmaskedvalue():-1!==_positioning.getLastValidPosition.call(inputmask)||!0!==opts.nullable?(this.inputmask.shadowRoot||this.ownerDocument).activeElement===this&&opts.clearMaskOnLostFocus?(inputmask.isRTL?_inputHandling.clearOptionalTail.call(inputmask,_positioning.getBuffer.call(inputmask).slice()).reverse():_inputHandling.clearOptionalTail.call(inputmask,_positioning.getBuffer.call(inputmask).slice())).join(""):valueGet.call(this):"":valueGet.call(this)}function setter(value){valueSet.call(this,value),this.inputmask&&(0,_inputHandling.applyInputValue)(this,value)}function installNativeValueSetFallback(npt){_eventruler.EventRuler.on(npt,"mouseenter",function(){var input=this,value=this.inputmask._valueGet(!0);value!==(inputmask.isRTL?_positioning.getBuffer.call(inputmask).reverse():_positioning.getBuffer.call(inputmask)).join("")&&(0,_inputHandling.applyInputValue)(this,value)})}if(!npt.inputmask.__valueGet){if(!0!==opts.noValuePatching){if(Object.getOwnPropertyDescriptor){var valueProperty=Object.getPrototypeOf?Object.getOwnPropertyDescriptor(Object.getPrototypeOf(npt),"value"):void 0;valueProperty&&valueProperty.get&&valueProperty.set?(valueGet=valueProperty.get,valueSet=valueProperty.set,Object.defineProperty(npt,"value",{get:getter,set:setter,configurable:!0})):"input"!==npt.tagName.toLowerCase()&&(valueGet=function valueGet(){return this.textContent},valueSet=function valueSet(value){this.textContent=value},Object.defineProperty(npt,"value",{get:getter,set:setter,configurable:!0}))}else document.__lookupGetter__&&npt.__lookupGetter__("value")&&(valueGet=npt.__lookupGetter__("value"),valueSet=npt.__lookupSetter__("value"),npt.__defineGetter__("value",getter),npt.__defineSetter__("value",setter));npt.inputmask.__valueGet=valueGet,npt.inputmask.__valueSet=valueSet}npt.inputmask._valueGet=function(overruleRTL){return inputmask.isRTL&&!0!==overruleRTL?valueGet.call(this.el).split("").reverse().join(""):valueGet.call(this.el)},npt.inputmask._valueSet=function(value,overruleRTL){valueSet.call(this.el,null==value?"":!0!==overruleRTL&&inputmask.isRTL?value.split("").reverse().join(""):value)},void 0===valueGet&&(valueGet=function valueGet(){return this.value},valueSet=function valueSet(value){this.value=value},patchValhook(npt.type),installNativeValueSetFallback(npt))}}"textarea"!==input.tagName.toLowerCase()&&opts.ignorables.push(_keycode.default.ENTER);var elementType=input.getAttribute("type"),isSupported="input"===input.tagName.toLowerCase()&&opts.supportsInputType.includes(elementType)||input.isContentEditable||"textarea"===input.tagName.toLowerCase();if(!isSupported)if("input"===input.tagName.toLowerCase()){var el=document.createElement("input");el.setAttribute("type",elementType),isSupported="text"===el.type,el=null}else isSupported="partial";return!1!==isSupported?patchValueProperty(input):input.inputmask=void 0,isSupported}_eventruler.EventRuler.off(el);var isSupported=isElementTypeSupported(el,opts);if(!1!==isSupported){inputmask.originalPlaceholder=el.placeholder,inputmask.maxLength=void 0!==el?el.maxLength:void 0,-1===inputmask.maxLength&&(inputmask.maxLength=void 0),"inputMode"in el&&null===el.getAttribute("inputmode")&&(el.inputMode=opts.inputmode,el.setAttribute("inputmode",opts.inputmode)),!0===isSupported&&(opts.showMaskOnFocus=opts.showMaskOnFocus&&-1===["cc-number","cc-exp"].indexOf(el.autocomplete),_environment.iphone&&(opts.insertModeVisual=!1),_eventruler.EventRuler.on(el,"submit",_eventhandlers.EventHandlers.submitEvent),_eventruler.EventRuler.on(el,"reset",_eventhandlers.EventHandlers.resetEvent),_eventruler.EventRuler.on(el,"blur",_eventhandlers.EventHandlers.blurEvent),_eventruler.EventRuler.on(el,"focus",_eventhandlers.EventHandlers.focusEvent),_eventruler.EventRuler.on(el,"invalid",_eventhandlers.EventHandlers.invalidEvent),_eventruler.EventRuler.on(el,"click",_eventhandlers.EventHandlers.clickEvent),_eventruler.EventRuler.on(el,"mouseleave",_eventhandlers.EventHandlers.mouseleaveEvent),_eventruler.EventRuler.on(el,"mouseenter",_eventhandlers.EventHandlers.mouseenterEvent),_eventruler.EventRuler.on(el,"paste",_eventhandlers.EventHandlers.pasteEvent),_eventruler.EventRuler.on(el,"cut",_eventhandlers.EventHandlers.cutEvent),_eventruler.EventRuler.on(el,"complete",opts.oncomplete),_eventruler.EventRuler.on(el,"incomplete",opts.onincomplete),_eventruler.EventRuler.on(el,"cleared",opts.oncleared),!0!==opts.inputEventOnly&&(_eventruler.EventRuler.on(el,"keydown",_eventhandlers.EventHandlers.keydownEvent),_eventruler.EventRuler.on(el,"keypress",_eventhandlers.EventHandlers.keypressEvent),_eventruler.EventRuler.on(el,"keyup",_eventhandlers.EventHandlers.keyupEvent)),(_environment.mobile||opts.inputEventOnly)&&el.removeAttribute("maxLength"),_eventruler.EventRuler.on(el,"input",_eventhandlers.EventHandlers.inputFallBackEvent),_eventruler.EventRuler.on(el,"compositionend",_eventhandlers.EventHandlers.compositionendEvent)),_eventruler.EventRuler.on(el,"setvalue",_eventhandlers.EventHandlers.setValueEvent),inputmask.undoValue=_positioning.getBufferTemplate.call(inputmask).join("");var activeElement=(el.inputmask.shadowRoot||el.ownerDocument).activeElement;if(""!==el.inputmask._valueGet(!0)||!1===opts.clearMaskOnLostFocus||activeElement===el){(0,_inputHandling.applyInputValue)(el,el.inputmask._valueGet(!0),opts);var buffer=_positioning.getBuffer.call(inputmask).slice();!1===_validation.isComplete.call(inputmask,buffer)&&opts.clearIncomplete&&_positioning.resetMaskSet.call(inputmask),opts.clearMaskOnLostFocus&&activeElement!==el&&(-1===_positioning.getLastValidPosition.call(inputmask)?buffer=[]:_inputHandling.clearOptionalTail.call(inputmask,buffer)),(!1===opts.clearMaskOnLostFocus||opts.showMaskOnFocus&&activeElement===el||""!==el.inputmask._valueGet(!0))&&(0,_inputHandling.writeBuffer)(el,buffer),activeElement===el&&_positioning.caret.call(inputmask,el,_positioning.seekNext.call(inputmask,_positioning.getLastValidPosition.call(inputmask)))}}}},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.generateMaskSet=generateMaskSet,exports.analyseMask=analyseMask;var _inputmask=_interopRequireDefault(__webpack_require__(12)),_masktoken=_interopRequireDefault(__webpack_require__(20));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}function generateMaskSet(opts,nocache){var ms;function generateMask(mask,metadata,opts){var regexMask=!1,masksetDefinition,maskdefKey;if(null!==mask&&""!==mask||(regexMask=null!==opts.regex,mask=regexMask?(mask=opts.regex,mask.replace(/^(\^)(.*)(\$)$/,"$2")):(regexMask=!0,".*")),1===mask.length&&!1===opts.greedy&&0!==opts.repeat&&(opts.placeholder=""),0<opts.repeat||"*"===opts.repeat||"+"===opts.repeat){var repeatStart="*"===opts.repeat?0:"+"===opts.repeat?1:opts.repeat;mask=opts.groupmarker[0]+mask+opts.groupmarker[1]+opts.quantifiermarker[0]+repeatStart+","+opts.repeat+opts.quantifiermarker[1]}return maskdefKey=regexMask?"regex_"+opts.regex:opts.numericInput?mask.split("").reverse().join(""):mask,!1!==opts.keepStatic&&(maskdefKey="ks_"+maskdefKey),void 0===Inputmask.prototype.masksCache[maskdefKey]||!0===nocache?(masksetDefinition={mask:mask,maskToken:Inputmask.prototype.analyseMask(mask,regexMask,opts),validPositions:{},_buffer:void 0,buffer:void 0,tests:{},excludes:{},metadata:metadata,maskLength:void 0,jitOffset:{}},!0!==nocache&&(Inputmask.prototype.masksCache[maskdefKey]=masksetDefinition,masksetDefinition=_inputmask.default.extend(!0,{},Inputmask.prototype.masksCache[maskdefKey]))):masksetDefinition=_inputmask.default.extend(!0,{},Inputmask.prototype.masksCache[maskdefKey]),masksetDefinition}if("function"==typeof opts.mask&&(opts.mask=opts.mask(opts)),Array.isArray(opts.mask)){if(1<opts.mask.length){null===opts.keepStatic&&(opts.keepStatic=!0);var altMask=opts.groupmarker[0];return(opts.isRTL?opts.mask.reverse():opts.mask).forEach(function(msk){1<altMask.length&&(altMask+=opts.groupmarker[1]+opts.alternatormarker+opts.groupmarker[0]),void 0!==msk.mask&&"function"!=typeof msk.mask?altMask+=msk.mask:altMask+=msk}),altMask+=opts.groupmarker[1],generateMask(altMask,opts.mask,opts)}opts.mask=opts.mask.pop()}return null===opts.keepStatic&&(opts.keepStatic=!1),ms=opts.mask&&void 0!==opts.mask.mask&&"function"!=typeof opts.mask.mask?generateMask(opts.mask.mask,opts.mask,opts):generateMask(opts.mask,opts.mask,opts),ms}function analyseMask(mask,regexMask,opts){var tokenizer=/(?:[?*+]|\{[0-9+*]+(?:,[0-9+*]*)?(?:\|[0-9+*]*)?\})|[^.?*+^${[]()|\\]+|./g,regexTokenizer=/\[\^?]?(?:[^\\\]]+|\\[\S\s]?)*]?|\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\]+|./g,escaped=!1,currentToken=new _masktoken.default,match,m,openenings=[],maskTokens=[],openingToken,currentOpeningToken,alternator,lastMatch,closeRegexGroup=!1;function insertTestDefinition(mtoken,element,position){position=void 0!==position?position:mtoken.matches.length;var prevMatch=mtoken.matches[position-1];if(regexMask)0===element.indexOf("[")||escaped&&/\\d|\\s|\\w]/i.test(element)||"."===element?mtoken.matches.splice(position++,0,{fn:new RegExp(element,opts.casing?"i":""),static:!1,optionality:!1,newBlockMarker:void 0===prevMatch?"master":prevMatch.def!==element,casing:null,def:element,placeholder:void 0,nativeDef:element}):(escaped&&(element=element[element.length-1]),element.split("").forEach(function(lmnt,ndx){prevMatch=mtoken.matches[position-1],mtoken.matches.splice(position++,0,{fn:/[a-z]/i.test(opts.staticDefinitionSymbol||lmnt)?new RegExp("["+(opts.staticDefinitionSymbol||lmnt)+"]",opts.casing?"i":""):null,static:!0,optionality:!1,newBlockMarker:void 0===prevMatch?"master":prevMatch.def!==lmnt&&!0!==prevMatch.static,casing:null,def:opts.staticDefinitionSymbol||lmnt,placeholder:void 0!==opts.staticDefinitionSymbol?lmnt:void 0,nativeDef:(escaped?"'":"")+lmnt})})),escaped=!1;else{var maskdef=opts.definitions&&opts.definitions[element]||opts.usePrototypeDefinitions&&Inputmask.prototype.definitions[element];maskdef&&!escaped?mtoken.matches.splice(position++,0,{fn:maskdef.validator?"string"==typeof maskdef.validator?new RegExp(maskdef.validator,opts.casing?"i":""):new function(){this.test=maskdef.validator}:new RegExp("."),static:maskdef.static||!1,optionality:!1,newBlockMarker:void 0===prevMatch?"master":prevMatch.def!==(maskdef.definitionSymbol||element),casing:maskdef.casing,def:maskdef.definitionSymbol||element,placeholder:maskdef.placeholder,nativeDef:element,generated:maskdef.generated}):(mtoken.matches.splice(position++,0,{fn:/[a-z]/i.test(opts.staticDefinitionSymbol||element)?new RegExp("["+(opts.staticDefinitionSymbol||element)+"]",opts.casing?"i":""):null,static:!0,optionality:!1,newBlockMarker:void 0===prevMatch?"master":prevMatch.def!==element&&!0!==prevMatch.static,casing:null,def:opts.staticDefinitionSymbol||element,placeholder:void 0!==opts.staticDefinitionSymbol?element:void 0,nativeDef:(escaped?"'":"")+element}),escaped=!1)}}function verifyGroupMarker(maskToken){maskToken&&maskToken.matches&&maskToken.matches.forEach(function(token,ndx){var nextToken=maskToken.matches[ndx+1];(void 0===nextToken||void 0===nextToken.matches||!1===nextToken.isQuantifier)&&token&&token.isGroup&&(token.isGroup=!1,regexMask||(insertTestDefinition(token,opts.groupmarker[0],0),!0!==token.openGroup&&insertTestDefinition(token,opts.groupmarker[1]))),verifyGroupMarker(token)})}function defaultCase(){if(0<openenings.length){if(currentOpeningToken=openenings[openenings.length-1],insertTestDefinition(currentOpeningToken,m),currentOpeningToken.isAlternator){alternator=openenings.pop();for(var mndx=0;mndx<alternator.matches.length;mndx++)alternator.matches[mndx].isGroup&&(alternator.matches[mndx].isGroup=!1);0<openenings.length?(currentOpeningToken=openenings[openenings.length-1],currentOpeningToken.matches.push(alternator)):currentToken.matches.push(alternator)}}else insertTestDefinition(currentToken,m)}function reverseTokens(maskToken){function reverseStatic(st){return st===opts.optionalmarker[0]?st=opts.optionalmarker[1]:st===opts.optionalmarker[1]?st=opts.optionalmarker[0]:st===opts.groupmarker[0]?st=opts.groupmarker[1]:st===opts.groupmarker[1]&&(st=opts.groupmarker[0]),st}for(var match in maskToken.matches=maskToken.matches.reverse(),maskToken.matches)if(Object.prototype.hasOwnProperty.call(maskToken.matches,match)){var intMatch=parseInt(match);if(maskToken.matches[match].isQuantifier&&maskToken.matches[intMatch+1]&&maskToken.matches[intMatch+1].isGroup){var qt=maskToken.matches[match];maskToken.matches.splice(match,1),maskToken.matches.splice(intMatch+1,0,qt)}void 0!==maskToken.matches[match].matches?maskToken.matches[match]=reverseTokens(maskToken.matches[match]):maskToken.matches[match]=reverseStatic(maskToken.matches[match])}return maskToken}function groupify(matches){var groupToken=new _masktoken.default(!0);return groupToken.openGroup=!1,groupToken.matches=matches,groupToken}function closeGroup(){if(openingToken=openenings.pop(),openingToken.openGroup=!1,void 0!==openingToken)if(0<openenings.length){if(currentOpeningToken=openenings[openenings.length-1],currentOpeningToken.matches.push(openingToken),currentOpeningToken.isAlternator){alternator=openenings.pop();for(var mndx=0;mndx<alternator.matches.length;mndx++)alternator.matches[mndx].isGroup=!1,alternator.matches[mndx].alternatorGroup=!1;0<openenings.length?(currentOpeningToken=openenings[openenings.length-1],currentOpeningToken.matches.push(alternator)):currentToken.matches.push(alternator)}}else currentToken.matches.push(openingToken);else defaultCase()}function groupQuantifier(matches){var lastMatch=matches.pop();return lastMatch.isQuantifier&&(lastMatch=groupify([matches.pop(),lastMatch])),lastMatch}for(regexMask&&(opts.optionalmarker[0]=void 0,opts.optionalmarker[1]=void 0);match=regexMask?regexTokenizer.exec(mask):tokenizer.exec(mask);){if(m=match[0],regexMask)switch(m.charAt(0)){case"?":m="{0,1}";break;case"+":case"*":m="{"+m+"}";break;case"|":if(0===openenings.length){var altRegexGroup=groupify(currentToken.matches);altRegexGroup.openGroup=!0,openenings.push(altRegexGroup),currentToken.matches=[],closeRegexGroup=!0}break}if(escaped)defaultCase();else switch(m.charAt(0)){case"$":case"^":regexMask||defaultCase();break;case"(?=":openenings.push(new _masktoken.default(!0));break;case"(?!":openenings.push(new _masktoken.default(!0));break;case"(?<=":openenings.push(new _masktoken.default(!0));break;case"(?<!":openenings.push(new _masktoken.default(!0));break;case opts.escapeChar:escaped=!0,regexMask&&defaultCase();break;case opts.optionalmarker[1]:case opts.groupmarker[1]:closeGroup();break;case opts.optionalmarker[0]:openenings.push(new _masktoken.default(!1,!0));break;case opts.groupmarker[0]:openenings.push(new _masktoken.default(!0));break;case opts.quantifiermarker[0]:var quantifier=new _masktoken.default(!1,!1,!0);m=m.replace(/[{}]/g,"");var mqj=m.split("|"),mq=mqj[0].split(","),mq0=isNaN(mq[0])?mq[0]:parseInt(mq[0]),mq1=1===mq.length?mq0:isNaN(mq[1])?mq[1]:parseInt(mq[1]);"*"!==mq0&&"+"!==mq0||(mq0="*"===mq1?0:1),quantifier.quantifier={min:mq0,max:mq1,jit:mqj[1]};var matches=0<openenings.length?openenings[openenings.length-1].matches:currentToken.matches;if(match=matches.pop(),match.isAlternator){matches.push(match),matches=match.matches;var groupToken=new _masktoken.default(!0),tmpMatch=matches.pop();matches.push(groupToken),matches=groupToken.matches,match=tmpMatch}match.isGroup||(match=groupify([match])),matches.push(match),matches.push(quantifier);break;case opts.alternatormarker:if(0<openenings.length){currentOpeningToken=openenings[openenings.length-1];var subToken=currentOpeningToken.matches[currentOpeningToken.matches.length-1];lastMatch=currentOpeningToken.openGroup&&(void 0===subToken.matches||!1===subToken.isGroup&&!1===subToken.isAlternator)?openenings.pop():groupQuantifier(currentOpeningToken.matches)}else lastMatch=groupQuantifier(currentToken.matches);if(lastMatch.isAlternator)openenings.push(lastMatch);else if(lastMatch.alternatorGroup?(alternator=openenings.pop(),lastMatch.alternatorGroup=!1):alternator=new _masktoken.default(!1,!1,!1,!0),alternator.matches.push(lastMatch),openenings.push(alternator),lastMatch.openGroup){lastMatch.openGroup=!1;var alternatorGroup=new _masktoken.default(!0);alternatorGroup.alternatorGroup=!0,openenings.push(alternatorGroup)}break;default:defaultCase()}}for(closeRegexGroup&&closeGroup();0<openenings.length;)openingToken=openenings.pop(),currentToken.matches.push(openingToken);return 0<currentToken.matches.length&&(verifyGroupMarker(currentToken),maskTokens.push(currentToken)),(opts.numericInput||opts.isRTL)&&reverseTokens(maskTokens[0]),maskTokens}},function(module,exports,__webpack_require__){"use strict";function _default(isGroup,isOptional,isQuantifier,isAlternator){this.matches=[],this.openGroup=isGroup||!1,this.alternatorGroup=!1,this.isGroup=isGroup||!1,this.isOptional=isOptional||!1,this.isQuantifier=isQuantifier||!1,this.isAlternator=isAlternator||!1,this.quantifier={min:1,max:1}}Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=_default},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _default={9:{validator:"[0-9\uff10-\uff19]",definitionSymbol:"*"},a:{validator:"[A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]",definitionSymbol:"*"},"*":{validator:"[0-9\uff10-\uff19A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5]"}};exports.default=_default},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _default={_maxTestPos:500,placeholder:"_",optionalmarker:["[","]"],quantifiermarker:["{","}"],groupmarker:["(",")"],alternatormarker:"|",escapeChar:"\\",mask:null,regex:null,oncomplete:function oncomplete(){},onincomplete:function onincomplete(){},oncleared:function oncleared(){},repeat:0,greedy:!1,autoUnmask:!1,removeMaskOnSubmit:!1,clearMaskOnLostFocus:!0,insertMode:!0,insertModeVisual:!0,clearIncomplete:!1,alias:null,onKeyDown:function onKeyDown(){},onBeforeMask:null,onBeforePaste:function onBeforePaste(pastedValue,opts){return"function"==typeof opts.onBeforeMask?opts.onBeforeMask.call(this,pastedValue,opts):pastedValue},onBeforeWrite:null,onUnMask:null,showMaskOnFocus:!0,showMaskOnHover:!0,onKeyValidation:function onKeyValidation(){},skipOptionalPartCharacter:" ",numericInput:!1,rightAlign:!1,undoOnEscape:!0,radixPoint:"",_radixDance:!1,groupSeparator:"",keepStatic:null,positionCaretOnTab:!0,tabThrough:!1,supportsInputType:["text","tel","url","password","search"],ignorables:[8,9,19,27,33,34,35,36,37,38,39,40,45,46,93,112,113,114,115,116,117,118,119,120,121,122,123,0,229],isComplete:null,preValidation:null,postValidation:null,staticDefinitionSymbol:void 0,jitMasking:!1,nullable:!0,inputEventOnly:!1,noValuePatching:!1,positionCaretOnClick:"lvp",casing:null,inputmode:"text",importDataAttributes:!0,shiftPositions:!0,usePrototypeDefinitions:!0};exports.default=_default},function(module,exports,__webpack_require__){"use strict";var _inputmask=_interopRequireDefault(__webpack_require__(2)),_keycode=_interopRequireDefault(__webpack_require__(0)),_escapeRegex=_interopRequireDefault(__webpack_require__(13)),_positioning=__webpack_require__(1);function _typeof(obj){return _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function _typeof(obj){return typeof obj}:function _typeof(obj){return obj&&"function"==typeof Symbol&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj},_typeof(obj)}function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var $=_inputmask.default.dependencyLib,currentYear=(new Date).getFullYear(),formatCode={d:["[1-9]|[12][0-9]|3[01]",Date.prototype.setDate,"day",Date.prototype.getDate],dd:["0[1-9]|[12][0-9]|3[01]",Date.prototype.setDate,"day",function(){return pad(Date.prototype.getDate.call(this),2)}],ddd:[""],dddd:[""],m:["[1-9]|1[012]",Date.prototype.setMonth,"month",function(){return Date.prototype.getMonth.call(this)+1}],mm:["0[1-9]|1[012]",Date.prototype.setMonth,"month",function(){return pad(Date.prototype.getMonth.call(this)+1,2)}],mmm:[""],mmmm:[""],yy:["[0-9]{2}",Date.prototype.setFullYear,"year",function(){return pad(Date.prototype.getFullYear.call(this),2)}],yyyy:["[0-9]{4}",Date.prototype.setFullYear,"year",function(){return pad(Date.prototype.getFullYear.call(this),4)}],h:["[1-9]|1[0-2]",Date.prototype.setHours,"hours",Date.prototype.getHours],hh:["0[1-9]|1[0-2]",Date.prototype.setHours,"hours",function(){return pad(Date.prototype.getHours.call(this),2)}],hx:[function(x){return"[0-9]{".concat(x,"}")},Date.prototype.setHours,"hours",function(x){return Date.prototype.getHours}],H:["1?[0-9]|2[0-3]",Date.prototype.setHours,"hours",Date.prototype.getHours],HH:["0[0-9]|1[0-9]|2[0-3]",Date.prototype.setHours,"hours",function(){return pad(Date.prototype.getHours.call(this),2)}],Hx:[function(x){return"[0-9]{".concat(x,"}")},Date.prototype.setHours,"hours",function(x){return function(){return pad(Date.prototype.getHours.call(this),x)}}],M:["[1-5]?[0-9]",Date.prototype.setMinutes,"minutes",Date.prototype.getMinutes],MM:["0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]",Date.prototype.setMinutes,"minutes",function(){return pad(Date.prototype.getMinutes.call(this),2)}],s:["[1-5]?[0-9]",Date.prototype.setSeconds,"seconds",Date.prototype.getSeconds],ss:["0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]",Date.prototype.setSeconds,"seconds",function(){return pad(Date.prototype.getSeconds.call(this),2)}],l:["[0-9]{3}",Date.prototype.setMilliseconds,"milliseconds",function(){return pad(Date.prototype.getMilliseconds.call(this),3)}],L:["[0-9]{2}",Date.prototype.setMilliseconds,"milliseconds",function(){return pad(Date.prototype.getMilliseconds.call(this),2)}],t:["[ap]"],tt:["[ap]m"],T:["[AP]"],TT:["[AP]M"],Z:[""],o:[""],S:[""]},formatAlias={isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"};function formatcode(match){var dynMatches=new RegExp("\\d+$").exec(match[0]);if(dynMatches&&void 0!==dynMatches[0]){var fcode=formatCode[match[0][0]+"x"].slice("");return fcode[0]=fcode[0](dynMatches[0]),fcode[3]=fcode[3](dynMatches[0]),fcode}if(formatCode[match[0]])return formatCode[match[0]]}function getTokenizer(opts){if(!opts.tokenizer){var tokens=[],dyntokens=[];for(var ndx in formatCode)if(/\.*x$/.test(ndx)){var dynToken=ndx[0]+"\\d+";-1===dyntokens.indexOf(dynToken)&&dyntokens.push(dynToken)}else-1===tokens.indexOf(ndx[0])&&tokens.push(ndx[0]);opts.tokenizer="("+(0<dyntokens.length?dyntokens.join("|")+"|":"")+tokens.join("+|")+")+?|.",opts.tokenizer=new RegExp(opts.tokenizer,"g")}return opts.tokenizer}function prefillYear(dateParts,currentResult,opts){if(dateParts.year!==dateParts.rawyear){var crrntyear=currentYear.toString(),enteredPart=dateParts.rawyear.replace(/[^0-9]/g,""),currentYearPart=crrntyear.slice(0,enteredPart.length),currentYearNextPart=crrntyear.slice(enteredPart.length);if(2===enteredPart.length&&enteredPart===currentYearPart){var entryCurrentYear=new Date(currentYear,dateParts.month-1,dateParts.day);dateParts.day==entryCurrentYear.getDate()&&(!opts.max||opts.max.date.getTime()>=entryCurrentYear.getTime())&&(dateParts.date.setFullYear(currentYear),dateParts.year=crrntyear,currentResult.insert=[{pos:currentResult.pos+1,c:currentYearNextPart[0]},{pos:currentResult.pos+2,c:currentYearNextPart[1]}])}}return currentResult}function isValidDate(dateParts,currentResult,opts){if(void 0===dateParts.rawday||!isFinite(dateParts.rawday)&&new Date(dateParts.date.getFullYear(),isFinite(dateParts.rawmonth)?dateParts.month:dateParts.date.getMonth()+1,0).getDate()>=dateParts.day||"29"==dateParts.day&&!Number.isFinite(dateParts.rawyear)||new Date(dateParts.date.getFullYear(),isFinite(dateParts.rawmonth)?dateParts.month:dateParts.date.getMonth()+1,0).getDate()>=dateParts.day)return currentResult;if("29"==dateParts.day){var tokenMatch=getTokenMatch(currentResult.pos,opts);if("yyyy"===tokenMatch.targetMatch[0]&&currentResult.pos-tokenMatch.targetMatchIndex==2)return currentResult.remove=currentResult.pos+1,currentResult}else if("02"==dateParts.month&&"30"==dateParts.day&&void 0!==currentResult.c)return dateParts.day="03",dateParts.date.setDate(3),dateParts.date.setMonth(1),currentResult.insert=[{pos:currentResult.pos,c:"0"},{pos:currentResult.pos+1,c:currentResult.c}],currentResult.caret=_positioning.seekNext.call(this,currentResult.pos+1),currentResult;return!1}function isDateInRange(dateParts,result,opts,maskset,fromCheckval){if(!result)return result;if(opts.min){if(dateParts.rawyear){var rawYear=dateParts.rawyear.replace(/[^0-9]/g,""),minYear=opts.min.year.substr(0,rawYear.length),maxYear;if(rawYear<minYear){var tokenMatch=getTokenMatch(result.pos,opts);if(rawYear=dateParts.rawyear.substr(0,result.pos-tokenMatch.targetMatchIndex+1).replace(/[^0-9]/g,"0"),minYear=opts.min.year.substr(0,rawYear.length),minYear<=rawYear)return result.remove=tokenMatch.targetMatchIndex+rawYear.length,result;if(rawYear="yyyy"===tokenMatch.targetMatch[0]?dateParts.rawyear.substr(1,1):dateParts.rawyear.substr(0,1),minYear=opts.min.year.substr(2,1),maxYear=opts.max?opts.max.year.substr(2,1):rawYear,1===rawYear.length&&minYear<=rawYear&&rawYear<=maxYear&&!0!==fromCheckval)return"yyyy"===tokenMatch.targetMatch[0]?(result.insert=[{pos:result.pos+1,c:rawYear,strict:!0}],result.caret=result.pos+2,maskset.validPositions[result.pos].input=opts.min.year[1]):(result.insert=[{pos:result.pos+1,c:opts.min.year[1],strict:!0},{pos:result.pos+2,c:rawYear,strict:!0}],result.caret=result.pos+3,maskset.validPositions[result.pos].input=opts.min.year[0]),result;result=!1}}result&&dateParts.year&&dateParts.year===dateParts.rawyear&&opts.min.date.getTime()==opts.min.date.getTime()&&(result=opts.min.date.getTime()<=dateParts.date.getTime())}return result&&opts.max&&opts.max.date.getTime()==opts.max.date.getTime()&&(result=opts.max.date.getTime()>=dateParts.date.getTime()),result}function parse(format,dateObjValue,opts,raw){var mask="",match,fcode;for(getTokenizer(opts).lastIndex=0;match=getTokenizer(opts).exec(format);)if(void 0===dateObjValue)if(fcode=formatcode(match))mask+="("+fcode[0]+")";else switch(match[0]){case"[":mask+="(";break;case"]":mask+=")?";break;default:mask+=(0,_escapeRegex.default)(match[0])}else if(fcode=formatcode(match))if(!0!==raw&&fcode[3]){var getFn=fcode[3];mask+=getFn.call(dateObjValue.date)}else fcode[2]?mask+=dateObjValue["raw"+fcode[2]]:mask+=match[0];else mask+=match[0];return mask}function pad(val,len,right){for(val=String(val),len=len||2;val.length<len;)val=right?val+"0":"0"+val;return val}function analyseMask(maskString,format,opts){var dateObj={date:new Date(1,0,1)},targetProp,mask=maskString,match,dateOperation;function setValue(dateObj,value,opts){if(dateObj[targetProp]=value.replace(/[^0-9]/g,"0"),dateObj["raw"+targetProp]=value,void 0!==dateOperation){var datavalue=dateObj[targetProp];("day"===targetProp&&29===parseInt(datavalue)||"month"===targetProp&&2===parseInt(datavalue))&&(29!==parseInt(dateObj.day)||2!==parseInt(dateObj.month)||""!==dateObj.year&&void 0!==dateObj.year||dateObj.date.setFullYear(2012,1,29)),"day"===targetProp&&0===parseInt(datavalue)&&(datavalue=1),"month"===targetProp&&(datavalue=parseInt(datavalue),0<datavalue)&&(datavalue-=1),"year"===targetProp&&datavalue.length<4&&(datavalue=pad(datavalue,4,!0)),""!==datavalue&&dateOperation.call(dateObj.date,datavalue)}}if("string"==typeof mask){for(getTokenizer(opts).lastIndex=0;match=getTokenizer(opts).exec(format);){var dynMatches=new RegExp("\\d+$").exec(match[0]),fcode=dynMatches?match[0][0]+"x":match[0],value=void 0;if(dynMatches){var lastIndex=getTokenizer(opts).lastIndex,tokanMatch=getTokenMatch(match.index,opts);getTokenizer(opts).lastIndex=lastIndex,value=mask.slice(0,mask.indexOf(tokanMatch.nextMatch[0]))}else value=mask.slice(0,fcode.length);Object.prototype.hasOwnProperty.call(formatCode,fcode)&&(targetProp=formatCode[fcode][2],dateOperation=formatCode[fcode][1],setValue(dateObj,value,opts)),mask=mask.slice(value.length)}return dateObj}if(mask&&"object"===_typeof(mask)&&Object.prototype.hasOwnProperty.call(mask,"date"))return mask}function importDate(dateObj,opts){return parse(opts.inputFormat,{date:dateObj},opts)}function getTokenMatch(pos,opts){var calcPos=0,targetMatch,match,matchLength=0;for(getTokenizer(opts).lastIndex=0;match=getTokenizer(opts).exec(opts.inputFormat);){var dynMatches=new RegExp("\\d+$").exec(match[0]);if(matchLength=dynMatches?parseInt(dynMatches[0]):match[0].length,calcPos+=matchLength,pos<=calcPos){targetMatch=match,match=getTokenizer(opts).exec(opts.inputFormat);break}}return{targetMatchIndex:calcPos-matchLength,nextMatch:match,targetMatch:targetMatch}}_inputmask.default.extendAliases({datetime:{mask:function mask(opts){return opts.numericInput=!1,formatCode.S=opts.i18n.ordinalSuffix.join("|"),opts.inputFormat=formatAlias[opts.inputFormat]||opts.inputFormat,opts.displayFormat=formatAlias[opts.displayFormat]||opts.displayFormat||opts.inputFormat,opts.outputFormat=formatAlias[opts.outputFormat]||opts.outputFormat||opts.inputFormat,opts.placeholder=""!==opts.placeholder?opts.placeholder:opts.inputFormat.replace(/[[\]]/,""),opts.regex=parse(opts.inputFormat,void 0,opts),opts.min=analyseMask(opts.min,opts.inputFormat,opts),opts.max=analyseMask(opts.max,opts.inputFormat,opts),null},placeholder:"",inputFormat:"isoDateTime",displayFormat:void 0,outputFormat:void 0,min:null,max:null,skipOptionalPartCharacter:"",i18n:{dayNames:["Mon","Tue","Wed","Thu","Fri","Sat","Sun","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"],ordinalSuffix:["st","nd","rd","th"]},preValidation:function preValidation(buffer,pos,c,isSelection,opts,maskset,caretPos,strict){if(strict)return!0;if(isNaN(c)&&buffer[pos]!==c){var tokenMatch=getTokenMatch(pos,opts);if(tokenMatch.nextMatch&&tokenMatch.nextMatch[0]===c&&1<tokenMatch.targetMatch[0].length){var validator=formatCode[tokenMatch.targetMatch[0]][0];if(new RegExp(validator).test("0"+buffer[pos-1]))return buffer[pos]=buffer[pos-1],buffer[pos-1]="0",{fuzzy:!0,buffer:buffer,refreshFromBuffer:{start:pos-1,end:pos+1},pos:pos+1}}}return!0},postValidation:function postValidation(buffer,pos,c,currentResult,opts,maskset,strict,fromCheckval){var inputmask=this,tokenMatch,validator;if(strict)return!0;if(!1===currentResult&&(tokenMatch=getTokenMatch(pos+1,opts),tokenMatch.targetMatch&&tokenMatch.targetMatchIndex===pos&&1<tokenMatch.targetMatch[0].length&&void 0!==formatCode[tokenMatch.targetMatch[0]]?validator=formatCode[tokenMatch.targetMatch[0]][0]:(tokenMatch=getTokenMatch(pos+2,opts),tokenMatch.targetMatch&&tokenMatch.targetMatchIndex===pos+1&&1<tokenMatch.targetMatch[0].length&&void 0!==formatCode[tokenMatch.targetMatch[0]]&&(validator=formatCode[tokenMatch.targetMatch[0]][0])),void 0!==validator&&(void 0!==maskset.validPositions[pos+1]&&new RegExp(validator).test(c+"0")?(buffer[pos]=c,buffer[pos+1]="0",currentResult={pos:pos+2,caret:pos}):new RegExp(validator).test("0"+c)&&(buffer[pos]="0",buffer[pos+1]=c,currentResult={pos:pos+2})),!1===currentResult))return currentResult;if(currentResult.fuzzy&&(buffer=currentResult.buffer,pos=currentResult.pos),tokenMatch=getTokenMatch(pos,opts),tokenMatch.targetMatch&&tokenMatch.targetMatch[0]&&void 0!==formatCode[tokenMatch.targetMatch[0]]){validator=formatCode[tokenMatch.targetMatch[0]][0];var part=buffer.slice(tokenMatch.targetMatchIndex,tokenMatch.targetMatchIndex+tokenMatch.targetMatch[0].length);!1===new RegExp(validator).test(part.join(""))&&2===tokenMatch.targetMatch[0].length&&maskset.validPositions[tokenMatch.targetMatchIndex]&&maskset.validPositions[tokenMatch.targetMatchIndex+1]&&(maskset.validPositions[tokenMatch.targetMatchIndex+1].input="0")}var result=currentResult,dateParts=analyseMask(buffer.join(""),opts.inputFormat,opts);return result&&dateParts.date.getTime()==dateParts.date.getTime()&&(opts.prefillYear&&(result=prefillYear(dateParts,result,opts)),result=isValidDate.call(this,dateParts,result,opts),result=isDateInRange(dateParts,result,opts,maskset,fromCheckval)),void 0!==pos&&result&&currentResult.pos!==pos?{buffer:parse(opts.inputFormat,dateParts,opts).split(""),refreshFromBuffer:{start:pos,end:currentResult.pos},pos:currentResult.caret||currentResult.pos}:result},onKeyDown:function onKeyDown(e,buffer,caretPos,opts){var input=this;e.ctrlKey&&e.keyCode===_keycode.default.RIGHT&&(this.inputmask._valueSet(importDate(new Date,opts)),$(this).trigger("setvalue"))},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){return unmaskedValue?parse(opts.outputFormat,analyseMask(maskedValue,opts.inputFormat,opts),opts,!0):unmaskedValue},casing:function casing(elem,test,pos,validPositions){return 0==test.nativeDef.indexOf("[ap]")?elem.toLowerCase():0==test.nativeDef.indexOf("[AP]")?elem.toUpperCase():elem},onBeforeMask:function onBeforeMask(initialValue,opts){return"[object Date]"===Object.prototype.toString.call(initialValue)&&(initialValue=importDate(initialValue,opts)),initialValue},insertMode:!1,shiftPositions:!1,keepStatic:!1,inputmode:"numeric",prefillYear:!0}})},function(module,exports,__webpack_require__){"use strict";var _inputmask=_interopRequireDefault(__webpack_require__(2)),_keycode=_interopRequireDefault(__webpack_require__(0)),_escapeRegex=_interopRequireDefault(__webpack_require__(13));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var $=_inputmask.default.dependencyLib;function autoEscape(txt,opts){for(var escapedTxt="",i=0;i<txt.length;i++)_inputmask.default.prototype.definitions[txt.charAt(i)]||opts.definitions[txt.charAt(i)]||opts.optionalmarker[0]===txt.charAt(i)||opts.optionalmarker[1]===txt.charAt(i)||opts.quantifiermarker[0]===txt.charAt(i)||opts.quantifiermarker[1]===txt.charAt(i)||opts.groupmarker[0]===txt.charAt(i)||opts.groupmarker[1]===txt.charAt(i)||opts.alternatormarker===txt.charAt(i)?escapedTxt+="\\"+txt.charAt(i):escapedTxt+=txt.charAt(i);return escapedTxt}function alignDigits(buffer,digits,opts,force){if(0<buffer.length&&0<digits&&(!opts.digitsOptional||force)){var radixPosition=buffer.indexOf(opts.radixPoint),negationBack=!1;opts.negationSymbol.back===buffer[buffer.length-1]&&(negationBack=!0,buffer.length--),-1===radixPosition&&(buffer.push(opts.radixPoint),radixPosition=buffer.length-1);for(var i=1;i<=digits;i++)isFinite(buffer[radixPosition+i])||(buffer[radixPosition+i]="0")}return negationBack&&buffer.push(opts.negationSymbol.back),buffer}function findValidator(symbol,maskset){var posNdx=0;if("+"===symbol){for(posNdx in maskset.validPositions);posNdx=parseInt(posNdx)}for(var tstNdx in maskset.tests)if(tstNdx=parseInt(tstNdx),posNdx<=tstNdx)for(var ndx=0,ndxl=maskset.tests[tstNdx].length;ndx<ndxl;ndx++)if((void 0===maskset.validPositions[tstNdx]||"-"===symbol)&&maskset.tests[tstNdx][ndx].match.def===symbol)return tstNdx+(void 0!==maskset.validPositions[tstNdx]&&"-"!==symbol?1:0);return posNdx}function findValid(symbol,maskset){var ret=-1;for(var ndx in maskset.validPositions){var tst=maskset.validPositions[ndx];if(tst&&tst.match.def===symbol){ret=parseInt(ndx);break}}return ret}function parseMinMaxOptions(opts){void 0===opts.parseMinMaxOptions&&(null!==opts.min&&(opts.min=opts.min.toString().replace(new RegExp((0,_escapeRegex.default)(opts.groupSeparator),"g"),""),","===opts.radixPoint&&(opts.min=opts.min.replace(opts.radixPoint,".")),opts.min=isFinite(opts.min)?parseFloat(opts.min):NaN,isNaN(opts.min)&&(opts.min=Number.MIN_VALUE)),null!==opts.max&&(opts.max=opts.max.toString().replace(new RegExp((0,_escapeRegex.default)(opts.groupSeparator),"g"),""),","===opts.radixPoint&&(opts.max=opts.max.replace(opts.radixPoint,".")),opts.max=isFinite(opts.max)?parseFloat(opts.max):NaN,isNaN(opts.max)&&(opts.max=Number.MAX_VALUE)),opts.parseMinMaxOptions="done")}function genMask(opts){opts.repeat=0,opts.groupSeparator===opts.radixPoint&&opts.digits&&"0"!==opts.digits&&("."===opts.radixPoint?opts.groupSeparator=",":","===opts.radixPoint?opts.groupSeparator=".":opts.groupSeparator="")," "===opts.groupSeparator&&(opts.skipOptionalPartCharacter=void 0),1<opts.placeholder.length&&(opts.placeholder=opts.placeholder.charAt(0)),"radixFocus"===opts.positionCaretOnClick&&""===opts.placeholder&&(opts.positionCaretOnClick="lvp");var decimalDef="0",radixPointDef=opts.radixPoint;!0===opts.numericInput&&void 0===opts.__financeInput?(decimalDef="1",opts.positionCaretOnClick="radixFocus"===opts.positionCaretOnClick?"lvp":opts.positionCaretOnClick,opts.digitsOptional=!1,isNaN(opts.digits)&&(opts.digits=2),opts._radixDance=!1,radixPointDef=","===opts.radixPoint?"?":"!",""!==opts.radixPoint&&void 0===opts.definitions[radixPointDef]&&(opts.definitions[radixPointDef]={},opts.definitions[radixPointDef].validator="["+opts.radixPoint+"]",opts.definitions[radixPointDef].placeholder=opts.radixPoint,opts.definitions[radixPointDef].static=!0,opts.definitions[radixPointDef].generated=!0)):(opts.__financeInput=!1,opts.numericInput=!0);var mask="[+]",altMask;if(mask+=autoEscape(opts.prefix,opts),""!==opts.groupSeparator?(void 0===opts.definitions[opts.groupSeparator]&&(opts.definitions[opts.groupSeparator]={},opts.definitions[opts.groupSeparator].validator="["+opts.groupSeparator+"]",opts.definitions[opts.groupSeparator].placeholder=opts.groupSeparator,opts.definitions[opts.groupSeparator].static=!0,opts.definitions[opts.groupSeparator].generated=!0),mask+=opts._mask(opts)):mask+="9{+}",void 0!==opts.digits&&0!==opts.digits){var dq=opts.digits.toString().split(",");isFinite(dq[0])&&dq[1]&&isFinite(dq[1])?mask+=radixPointDef+decimalDef+"{"+opts.digits+"}":(isNaN(opts.digits)||0<parseInt(opts.digits))&&(opts.digitsOptional?(altMask=mask+radixPointDef+decimalDef+"{0,"+opts.digits+"}",opts.keepStatic=!0):mask+=radixPointDef+decimalDef+"{"+opts.digits+"}")}return mask+=autoEscape(opts.suffix,opts),mask+="[-]",altMask&&(mask=[altMask+autoEscape(opts.suffix,opts)+"[-]",mask]),opts.greedy=!1,parseMinMaxOptions(opts),mask}function hanndleRadixDance(pos,c,radixPos,maskset,opts){return opts._radixDance&&opts.numericInput&&c!==opts.negationSymbol.back&&pos<=radixPos&&(0<radixPos||c==opts.radixPoint)&&(void 0===maskset.validPositions[pos-1]||maskset.validPositions[pos-1].input!==opts.negationSymbol.back)&&(pos-=1),pos}function decimalValidator(chrs,maskset,pos,strict,opts){var radixPos=maskset.buffer?maskset.buffer.indexOf(opts.radixPoint):-1,result=-1!==radixPos&&new RegExp("[0-9\uff11-\uff19]").test(chrs);return opts._radixDance&&result&&null==maskset.validPositions[radixPos]?{insert:{pos:radixPos===pos?radixPos+1:radixPos,c:opts.radixPoint},pos:pos}:result}function checkForLeadingZeroes(buffer,opts){var numberMatches=new RegExp("(^"+(""!==opts.negationSymbol.front?(0,_escapeRegex.default)(opts.negationSymbol.front)+"?":"")+(0,_escapeRegex.default)(opts.prefix)+")(.*)("+(0,_escapeRegex.default)(opts.suffix)+(""!=opts.negationSymbol.back?(0,_escapeRegex.default)(opts.negationSymbol.back)+"?":"")+"$)").exec(buffer.slice().reverse().join("")),number=numberMatches?numberMatches[2]:"",leadingzeroes=!1;return number&&(number=number.split(opts.radixPoint.charAt(0))[0],leadingzeroes=new RegExp("^[0"+opts.groupSeparator+"]*").exec(number)),!(!leadingzeroes||!(1<leadingzeroes[0].length||0<leadingzeroes[0].length&&leadingzeroes[0].length<number.length))&&leadingzeroes}_inputmask.default.extendAliases({numeric:{mask:genMask,_mask:function _mask(opts){return"("+opts.groupSeparator+"999){+|1}"},digits:"*",digitsOptional:!0,enforceDigitsOnBlur:!1,radixPoint:".",positionCaretOnClick:"radixFocus",_radixDance:!0,groupSeparator:"",allowMinus:!0,negationSymbol:{front:"-",back:""},prefix:"",suffix:"",min:null,max:null,SetMaxOnOverflow:!1,step:1,inputType:"text",unmaskAsNumber:!1,roundingFN:Math.round,inputmode:"decimal",shortcuts:{k:"000",m:"000000"},placeholder:"0",greedy:!1,rightAlign:!0,insertMode:!0,autoUnmask:!1,skipOptionalPartCharacter:"",definitions:{0:{validator:decimalValidator},1:{validator:decimalValidator,definitionSymbol:"9"},"+":{validator:function validator(chrs,maskset,pos,strict,opts){return opts.allowMinus&&("-"===chrs||chrs===opts.negationSymbol.front)}},"-":{validator:function validator(chrs,maskset,pos,strict,opts){return opts.allowMinus&&chrs===opts.negationSymbol.back}}},preValidation:function preValidation(buffer,pos,c,isSelection,opts,maskset,caretPos,strict){if(!1!==opts.__financeInput&&c===opts.radixPoint)return!1;var pattern;if(pattern=opts.shortcuts&&opts.shortcuts[c]){if(1<pattern.length)for(var inserts=[],i=0;i<pattern.length;i++)inserts.push({pos:pos+i,c:pattern[i],strict:!1});return{insert:inserts}}var radixPos=buffer.indexOf(opts.radixPoint),initPos=pos;if(pos=hanndleRadixDance(pos,c,radixPos,maskset,opts),"-"===c||c===opts.negationSymbol.front){if(!0!==opts.allowMinus)return!1;var isNegative=!1,front=findValid("+",maskset),back=findValid("-",maskset);return-1!==front&&(isNegative=[front,back]),!1!==isNegative?{remove:isNegative,caret:initPos-opts.negationSymbol.front.length}:{insert:[{pos:findValidator("+",maskset),c:opts.negationSymbol.front,fromIsValid:!0},{pos:findValidator("-",maskset),c:opts.negationSymbol.back,fromIsValid:void 0}],caret:initPos+opts.negationSymbol.back.length}}if(c===opts.groupSeparator)return{caret:initPos};if(strict)return!0;if(-1!==radixPos&&!0===opts._radixDance&&!1===isSelection&&c===opts.radixPoint&&void 0!==opts.digits&&(isNaN(opts.digits)||0<parseInt(opts.digits))&&radixPos!==pos)return{caret:opts._radixDance&&pos===radixPos-1?radixPos+1:radixPos};if(!1===opts.__financeInput)if(isSelection){if(opts.digitsOptional)return{rewritePosition:caretPos.end};if(!opts.digitsOptional){if(caretPos.begin>radixPos&&caretPos.end<=radixPos)return c===opts.radixPoint?{insert:{pos:radixPos+1,c:"0",fromIsValid:!0},rewritePosition:radixPos}:{rewritePosition:radixPos+1};if(caretPos.begin<radixPos)return{rewritePosition:caretPos.begin-1}}}else if(!opts.showMaskOnHover&&!opts.showMaskOnFocus&&!opts.digitsOptional&&0<opts.digits&&""===this.__valueGet.call(this.el))return{rewritePosition:radixPos};return{rewritePosition:pos}},postValidation:function postValidation(buffer,pos,c,currentResult,opts,maskset,strict){if(!1===currentResult)return currentResult;if(strict)return!0;if(null!==opts.min||null!==opts.max){var unmasked=opts.onUnMask(buffer.slice().reverse().join(""),void 0,$.extend({},opts,{unmaskAsNumber:!0}));if(null!==opts.min&&unmasked<opts.min&&(unmasked.toString().length>opts.min.toString().length||unmasked<0))return!1;if(null!==opts.max&&unmasked>opts.max)return!!opts.SetMaxOnOverflow&&{refreshFromBuffer:!0,buffer:alignDigits(opts.max.toString().replace(".",opts.radixPoint).split(""),opts.digits,opts).reverse()}}return currentResult},onUnMask:function onUnMask(maskedValue,unmaskedValue,opts){if(""===unmaskedValue&&!0===opts.nullable)return unmaskedValue;var processValue=maskedValue.replace(opts.prefix,"");return processValue=processValue.replace(opts.suffix,""),processValue=processValue.replace(new RegExp((0,_escapeRegex.default)(opts.groupSeparator),"g"),""),""!==opts.placeholder.charAt(0)&&(processValue=processValue.replace(new RegExp(opts.placeholder.charAt(0),"g"),"0")),opts.unmaskAsNumber?(""!==opts.radixPoint&&-1!==processValue.indexOf(opts.radixPoint)&&(processValue=processValue.replace(_escapeRegex.default.call(this,opts.radixPoint),".")),processValue=processValue.replace(new RegExp("^"+(0,_escapeRegex.default)(opts.negationSymbol.front)),"-"),processValue=processValue.replace(new RegExp((0,_escapeRegex.default)(opts.negationSymbol.back)+"$"),""),Number(processValue)):processValue},isComplete:function isComplete(buffer,opts){var maskedValue=(opts.numericInput?buffer.slice().reverse():buffer).join("");return maskedValue=maskedValue.replace(new RegExp("^"+(0,_escapeRegex.default)(opts.negationSymbol.front)),"-"),maskedValue=maskedValue.replace(new RegExp((0,_escapeRegex.default)(opts.negationSymbol.back)+"$"),""),maskedValue=maskedValue.replace(opts.prefix,""),maskedValue=maskedValue.replace(opts.suffix,""),maskedValue=maskedValue.replace(new RegExp((0,_escapeRegex.default)(opts.groupSeparator)+"([0-9]{3})","g"),"$1"),","===opts.radixPoint&&(maskedValue=maskedValue.replace((0,_escapeRegex.default)(opts.radixPoint),".")),isFinite(maskedValue)},onBeforeMask:function onBeforeMask(initialValue,opts){var radixPoint=opts.radixPoint||",";isFinite(opts.digits)&&(opts.digits=parseInt(opts.digits)),"number"!=typeof initialValue&&"number"!==opts.inputType||""===radixPoint||(initialValue=initialValue.toString().replace(".",radixPoint));var isNagtive="-"===initialValue.charAt(0)||initialValue.charAt(0)===opts.negationSymbol.front,valueParts=initialValue.split(radixPoint),integerPart=valueParts[0].replace(/[^\-0-9]/g,""),decimalPart=1<valueParts.length?valueParts[1].replace(/[^0-9]/g,""):"",forceDigits=1<valueParts.length;initialValue=integerPart+(""!==decimalPart?radixPoint+decimalPart:decimalPart);var digits=0;if(""!==radixPoint&&(digits=opts.digitsOptional?opts.digits<decimalPart.length?opts.digits:decimalPart.length:opts.digits,""!==decimalPart||!opts.digitsOptional)){var digitsFactor=Math.pow(10,digits||1);initialValue=initialValue.replace((0,_escapeRegex.default)(radixPoint),"."),isNaN(parseFloat(initialValue))||(initialValue=(opts.roundingFN(parseFloat(initialValue)*digitsFactor)/digitsFactor).toFixed(digits)),initialValue=initialValue.toString().replace(".",radixPoint)}if(0===opts.digits&&-1!==initialValue.indexOf(radixPoint)&&(initialValue=initialValue.substring(0,initialValue.indexOf(radixPoint))),null!==opts.min||null!==opts.max){var numberValue=initialValue.toString().replace(radixPoint,".");null!==opts.min&&numberValue<opts.min?initialValue=opts.min.toString().replace(".",radixPoint):null!==opts.max&&numberValue>opts.max&&(initialValue=opts.max.toString().replace(".",radixPoint))}return isNagtive&&"-"!==initialValue.charAt(0)&&(initialValue="-"+initialValue),alignDigits(initialValue.toString().split(""),digits,opts,forceDigits).join("")},onBeforeWrite:function onBeforeWrite(e,buffer,caretPos,opts){function stripBuffer(buffer,stripRadix){if(!1!==opts.__financeInput||stripRadix){var position=buffer.indexOf(opts.radixPoint);-1!==position&&buffer.splice(position,1)}if(""!==opts.groupSeparator)for(;-1!==(position=buffer.indexOf(opts.groupSeparator));)buffer.splice(position,1);return buffer}var result,leadingzeroes=checkForLeadingZeroes(buffer,opts);if(leadingzeroes)for(var caretNdx=buffer.join("").lastIndexOf(leadingzeroes[0].split("").reverse().join(""))-(leadingzeroes[0]==leadingzeroes.input?0:1),offset=leadingzeroes[0]==leadingzeroes.input?1:0,i=leadingzeroes[0].length-offset;0<i;i--)delete this.maskset.validPositions[caretNdx+i],delete buffer[caretNdx+i];if(e)switch(e.type){case"blur":case"checkval":if(null!==opts.min){var unmasked=opts.onUnMask(buffer.slice().reverse().join(""),void 0,$.extend({},opts,{unmaskAsNumber:!0}));if(null!==opts.min&&unmasked<opts.min)return{refreshFromBuffer:!0,buffer:alignDigits(opts.min.toString().replace(".",opts.radixPoint).split(""),opts.digits,opts).reverse()}}if(buffer[buffer.length-1]===opts.negationSymbol.front){var nmbrMtchs=new RegExp("(^"+(""!=opts.negationSymbol.front?(0,_escapeRegex.default)(opts.negationSymbol.front)+"?":"")+(0,_escapeRegex.default)(opts.prefix)+")(.*)("+(0,_escapeRegex.default)(opts.suffix)+(""!=opts.negationSymbol.back?(0,_escapeRegex.default)(opts.negationSymbol.back)+"?":"")+"$)").exec(stripBuffer(buffer.slice(),!0).reverse().join("")),number=nmbrMtchs?nmbrMtchs[2]:"";0==number&&(result={refreshFromBuffer:!0,buffer:[0]})}else""!==opts.radixPoint&&buffer[0]===opts.radixPoint&&(result&&result.buffer?result.buffer.shift():(buffer.shift(),result={refreshFromBuffer:!0,buffer:stripBuffer(buffer)}));if(opts.enforceDigitsOnBlur){result=result||{};var bffr=result&&result.buffer||buffer.slice().reverse();result.refreshFromBuffer=!0,result.buffer=alignDigits(bffr,opts.digits,opts,!0).reverse()}}return result},onKeyDown:function onKeyDown(e,buffer,caretPos,opts){var $input=$(this),bffr;if(e.ctrlKey)switch(e.keyCode){case _keycode.default.UP:return this.inputmask.__valueSet.call(this,parseFloat(this.inputmask.unmaskedvalue())+parseInt(opts.step)),$input.trigger("setvalue"),!1;case _keycode.default.DOWN:return this.inputmask.__valueSet.call(this,parseFloat(this.inputmask.unmaskedvalue())-parseInt(opts.step)),$input.trigger("setvalue"),!1}if(!e.shiftKey&&(e.keyCode===_keycode.default.DELETE||e.keyCode===_keycode.default.BACKSPACE||e.keyCode===_keycode.default.BACKSPACE_SAFARI)&&caretPos.begin!==buffer.length){if(buffer[e.keyCode===_keycode.default.DELETE?caretPos.begin-1:caretPos.end]===opts.negationSymbol.front)return bffr=buffer.slice().reverse(),""!==opts.negationSymbol.front&&bffr.shift(),""!==opts.negationSymbol.back&&bffr.pop(),$input.trigger("setvalue",[bffr.join(""),caretPos.begin]),!1;if(!0===opts._radixDance){var radixPos=buffer.indexOf(opts.radixPoint);if(opts.digitsOptional){if(0===radixPos)return bffr=buffer.slice().reverse(),bffr.pop(),$input.trigger("setvalue",[bffr.join(""),caretPos.begin>=bffr.length?bffr.length:caretPos.begin]),!1}else if(-1!==radixPos&&(caretPos.begin<radixPos||caretPos.end<radixPos||e.keyCode===_keycode.default.DELETE&&caretPos.begin===radixPos))return caretPos.begin!==caretPos.end||e.keyCode!==_keycode.default.BACKSPACE&&e.keyCode!==_keycode.default.BACKSPACE_SAFARI||caretPos.begin++,bffr=buffer.slice().reverse(),bffr.splice(bffr.length-caretPos.begin,caretPos.begin-caretPos.end+1),bffr=alignDigits(bffr,opts.digits,opts).join(""),$input.trigger("setvalue",[bffr,caretPos.begin>=bffr.length?radixPos+1:caretPos.begin]),!1}}}},currency:{prefix:"",groupSeparator:",",alias:"numeric",digits:2,digitsOptional:!1},decimal:{alias:"numeric"},integer:{alias:"numeric",inputmode:"numeric",digits:0},percentage:{alias:"numeric",min:0,max:100,suffix:" %",digits:0,allowMinus:!1},indianns:{alias:"numeric",_mask:function _mask(opts){return"("+opts.groupSeparator+"99){*|1}("+opts.groupSeparator+"999){1|1}"},groupSeparator:",",radixPoint:".",placeholder:"0",digits:2,digitsOptional:!1}})},function(module,exports,__webpack_require__){"use strict";var _window=_interopRequireDefault(__webpack_require__(8)),_inputmask=_interopRequireDefault(__webpack_require__(2)),_canUseDOM=_interopRequireDefault(__webpack_require__(9));function _typeof(obj){return _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function _typeof(obj){return typeof obj}:function _typeof(obj){return obj&&"function"==typeof Symbol&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj},_typeof(obj)}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor))throw new TypeError("Cannot call a class as a function")}function _inherits(subClass,superClass){if("function"!=typeof superClass&&null!==superClass)throw new TypeError("Super expression must either be null or a function");subClass.prototype=Object.create(superClass&&superClass.prototype,{constructor:{value:subClass,writable:!0,configurable:!0}}),superClass&&_setPrototypeOf(subClass,superClass)}function _createSuper(Derived){var hasNativeReflectConstruct=_isNativeReflectConstruct();return function _createSuperInternal(){var Super=_getPrototypeOf(Derived),result;if(hasNativeReflectConstruct){var NewTarget=_getPrototypeOf(this).constructor;result=Reflect.construct(Super,arguments,NewTarget)}else result=Super.apply(this,arguments);return _possibleConstructorReturn(this,result)}}function _possibleConstructorReturn(self,call){return!call||"object"!==_typeof(call)&&"function"!=typeof call?_assertThisInitialized(self):call}function _assertThisInitialized(self){if(void 0===self)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return self}function _wrapNativeSuper(Class){var _cache="function"==typeof Map?new Map:void 0;return _wrapNativeSuper=function _wrapNativeSuper(Class){if(null===Class||!_isNativeFunction(Class))return Class;if("function"!=typeof Class)throw new TypeError("Super expression must either be null or a function");if("undefined"!=typeof _cache){if(_cache.has(Class))return _cache.get(Class);_cache.set(Class,Wrapper)}function Wrapper(){return _construct(Class,arguments,_getPrototypeOf(this).constructor)}return Wrapper.prototype=Object.create(Class.prototype,{constructor:{value:Wrapper,enumerable:!1,writable:!0,configurable:!0}}),_setPrototypeOf(Wrapper,Class)},_wrapNativeSuper(Class)}function _construct(Parent,args,Class){return _construct=_isNativeReflectConstruct()?Reflect.construct:function _construct(Parent,args,Class){var a=[null];a.push.apply(a,args);var Constructor=Function.bind.apply(Parent,a),instance=new Constructor;return Class&&_setPrototypeOf(instance,Class.prototype),instance},_construct.apply(null,arguments)}function _isNativeReflectConstruct(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch(e){return!1}}function _isNativeFunction(fn){return-1!==Function.toString.call(fn).indexOf("[native code]")}function _setPrototypeOf(o,p){return _setPrototypeOf=Object.setPrototypeOf||function _setPrototypeOf(o,p){return o.__proto__=p,o},_setPrototypeOf(o,p)}function _getPrototypeOf(o){return _getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function _getPrototypeOf(o){return o.__proto__||Object.getPrototypeOf(o)},_getPrototypeOf(o)}function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}var document=_window.default.document;if(_canUseDOM.default&&document&&document.head&&document.head.attachShadow&&_window.default.customElements&&void 0===_window.default.customElements.get("input-mask")){var InputmaskElement=function(_HTMLElement){_inherits(InputmaskElement,_HTMLElement);var _super=_createSuper(InputmaskElement);function InputmaskElement(){var _this;_classCallCheck(this,InputmaskElement),_this=_super.call(this);var attributeNames=_this.getAttributeNames(),shadow=_this.attachShadow({mode:"closed"}),input=document.createElement("input");for(var attr in input.type="text",shadow.appendChild(input),attributeNames)Object.prototype.hasOwnProperty.call(attributeNames,attr)&&input.setAttribute(attributeNames[attr],_this.getAttribute(attributeNames[attr]));var im=new _inputmask.default;return im.dataAttribute="",im.mask(input),input.inputmask.shadowRoot=shadow,_this}return InputmaskElement}(_wrapNativeSuper(HTMLElement));_window.default.customElements.define("input-mask",InputmaskElement)}},function(module,exports,__webpack_require__){"use strict";var _jquery=_interopRequireDefault(__webpack_require__(10)),_inputmask=_interopRequireDefault(__webpack_require__(2));function _typeof(obj){return _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function _typeof(obj){return typeof obj}:function _typeof(obj){return obj&&"function"==typeof Symbol&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj},_typeof(obj)}function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}void 0===_jquery.default.fn.inputmask&&(_jquery.default.fn.inputmask=function(fn,options){var nptmask,input=this[0];if(void 0===options&&(options={}),"string"==typeof fn)switch(fn){case"unmaskedvalue":return input&&input.inputmask?input.inputmask.unmaskedvalue():(0,_jquery.default)(input).val();case"remove":return this.each(function(){this.inputmask&&this.inputmask.remove()});case"getemptymask":return input&&input.inputmask?input.inputmask.getemptymask():"";case"hasMaskedValue":return!(!input||!input.inputmask)&&input.inputmask.hasMaskedValue();case"isComplete":return!input||!input.inputmask||input.inputmask.isComplete();case"getmetadata":return input&&input.inputmask?input.inputmask.getmetadata():void 0;case"setvalue":_inputmask.default.setValue(input,options);break;case"option":if("string"!=typeof options)return this.each(function(){if(void 0!==this.inputmask)return this.inputmask.option(options)});if(input&&void 0!==input.inputmask)return input.inputmask.option(options);break;default:return options.alias=fn,nptmask=new _inputmask.default(options),this.each(function(){nptmask.mask(this)})}else{if(Array.isArray(fn))return options.alias=fn,nptmask=new _inputmask.default(options),this.each(function(){nptmask.mask(this)});if("object"==_typeof(fn))return nptmask=new _inputmask.default(fn),void 0===fn.mask&&void 0===fn.alias?this.each(function(){if(void 0!==this.inputmask)return this.inputmask.option(fn);nptmask.mask(this)}):this.each(function(){nptmask.mask(this)});if(void 0===fn)return this.each(function(){nptmask=new _inputmask.default(options),nptmask.mask(this)})}})},function(module,exports,__webpack_require__){"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var _bundle=_interopRequireDefault(__webpack_require__(14));function _interopRequireDefault(obj){return obj&&obj.__esModule?obj:{default:obj}}__webpack_require__(26);var _default=_bundle.default;exports.default=_default}],installedModules={},__webpack_require__.m=modules,__webpack_require__.c=installedModules,__webpack_require__.d=function(exports,name,getter){__webpack_require__.o(exports,name)||Object.defineProperty(exports,name,{enumerable:!0,get:getter})},__webpack_require__.r=function(exports){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(exports,"__esModule",{value:!0})},__webpack_require__.t=function(value,mode){if(1&mode&&(value=__webpack_require__(value)),8&mode)return value;if(4&mode&&"object"==typeof value&&value&&value.__esModule)return value;var ns=Object.create(null);if(__webpack_require__.r(ns),Object.defineProperty(ns,"default",{enumerable:!0,value:value}),2&mode&&"string"!=typeof value)for(var key in value)__webpack_require__.d(ns,key,function(key){return value[key]}.bind(null,key));return ns},__webpack_require__.n=function(module){var getter=module&&module.__esModule?function getDefault(){return module.default}:function getModuleExports(){return module};return __webpack_require__.d(getter,"a",getter),getter},__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property)},__webpack_require__.p="",__webpack_require__(__webpack_require__.s=27);function __webpack_require__(moduleId){if(installedModules[moduleId])return installedModules[moduleId].exports;var module=installedModules[moduleId]={i:moduleId,l:!1,exports:{}};return modules[moduleId].call(module.exports,module,module.exports,__webpack_require__),module.l=!0,module.exports}var modules,installedModules});

/*
 Input Mask plugin binding
 http://github.com/RobinHerbots/jquery.inputmask
 Copyright (c) Robin Herbots
 Licensed under the MIT license
 */
(function (factory) {
    factory(jQuery, window.Inputmask, window);
}
(function ($, Inputmask, window) {
    $(window.document).ajaxComplete(function (event, xmlHttpRequest, ajaxOptions) {
        if ($.inArray("html", ajaxOptions.dataTypes) !== -1) {
            $(".inputmask, [data-inputmask], [data-inputmask-mask], [data-inputmask-alias], [data-inputmask-regex]").each(function (ndx, lmnt) {
                if (lmnt.inputmask === undefined) {
                    Inputmask().mask(lmnt);
                }
            });
        }
    }).ready(function () {
        $(".inputmask, [data-inputmask], [data-inputmask-mask], [data-inputmask-alias],[data-inputmask-regex]").each(function (ndx, lmnt) {
            if (lmnt.inputmask === undefined) {
                Inputmask().mask(lmnt);
            }
        });
    });
}));
// color calc
function leykaRgb2Hsl(r, g, b) {
    var d, h, l, max, min, s;

    r /= 255;
    g /= 255;
    b /= 255;

    max = Math.max(r, g, b);
    min = Math.min(r, g, b);

    h = 0;
    s = 0;
    l = (max + min) / 2;

    if (max === min) {
        h = s = 0;

    } else {
        d = max - min;

        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

        if(max == r) {
            h = (g - b) / d + (g < b ? 6 : 0);
        }
        else if(max == g) {
            h = (b - r) / d + 2;
        }
        else if(max == b) {
            h = (r - g) / d + 4;
        }

        h /= 6;
    }

    h = Math.floor(h * 360);
    s = Math.floor(s * 100);
    l = Math.floor(l * 100);

    return [h, s, l];
}

function leykaHex2Rgb (hex) {
    hex = hex.replace("#", "");

    var intColor = parseInt(hex, 16);
    var r = (intColor >> 16) & 255;
    var g = (intColor >> 8) & 255;
    var b = intColor & 255;

    return [r, g, b];
}

function leykaHsl2Rgb(h, s, l) {
    h /= 360
    s /= 100
    l /= 100

    var r, g, b;

    if(s == 0){
        r = g = b = l; // achromatic
    }else{
        var hue2rgb = function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function leykaHsl2Hex(hue, saturation, luminosity) {
  while (hue < 0) { hue += 360 }
  while (hue > 359) { hue -= 360 }

  var rgb = leykaHsl2Rgb(hue, saturation, luminosity);

  return '#' + rgb
    .map(function (n) {
      return (256 + n).toString(16).substr(-2)
    })
    .join('')
}

function leykaHex2Hsl(hexColor) {
    var rgb = leykaHex2Rgb(hexColor);
    return leykaRgb2Hsl(rgb[0], rgb[1], rgb[2]);
}

function leykaMainHslColor2Background(h, s, l) {
    if(l < 50) {
        l = 95;
    }
    else {
        l = 5;
    }
    return [h, s, l];
}

function leykaMainHslColor2Text(h, s, l) {
    if(l < 50) {
        l = 21;
    }
    else {
        l = 79;
    }
    s = 20;
    return [h, s, l];
}
if(jQuery.ui.autocomplete) {
	let $ = jQuery;

	$.widget("ui.autocomplete", $.ui.autocomplete, {
	    options : $.extend({}, this.options, {
	        multiselect: false,
	        search_on_focus: false,
	        leyka_select_callback: false,
	        pre_selected_values: []
	    }),
	    _create: function(){
	        this._super();

	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
	        	self.options['position'] = { my: "left-5px top+6px", at: "left bottom", collision: "none" };

	            self.selectedItems = {};           

	            self.placeholder = $("<div></div>")
	            	.addClass('placeholder')
	            	.text(self.element.prop('placeholder'));

            	self.element.prop('placeholder', '')

	            self.multiselect = $("<div></div>")
	                .addClass("ui-autocomplete-multiselect ui-state-default ui-widget")
	                .css("width", self.element.width())
	                .insertBefore(self.element)
	                .append(self.placeholder)
	                .append(self.element)
	                .bind("click.autocomplete", function(){
	                	self.placeholder.hide();
	                    self.element.css('display', 'block');
	                	self.element.show();
	                    self.element.focus();
	                });

	            var fontSize = parseInt(self.element.css("fontSize"), 10);
	            function autoSize(e){
	                var $this = $(this);
	                $this.width(1).width(this.scrollWidth+fontSize-1);
	            };

	            var kc = $.ui.keyCode;
	            self.element.bind({
	                "keydown.autocomplete": function(e){
	                    if ((this.value === "") && (e.keyCode == kc.BACKSPACE)) {
	                        var prev = self.element.prev();
	                        delete self.selectedItems[prev.text()];
	                        prev.remove();
	                    }
	                },
	                "focus.autocomplete": function(){
	                	if(o.search_on_focus && this.value === "") {
	                		self.search("");
	                	}
	                	else {
							self.multiselect.addClass("ui-state-active");
	                	}
	                },
	                "blur.autocomplete": function(){
	                	self.multiselect.removeClass("ui-state-active");
	                	if(self.multiselect.find('.ui-autocomplete-multiselect-item').length == 0) {
	                    	self.placeholder.show();
	                    	self.element.hide();
	                    }
	                },

	                "keypress.autocomplete change.autocomplete focus.autocomplete blur.autocomplete": autoSize
	            }).trigger("change");

	            o.select = o.select || function(e, ui) {
	            	if(typeof(self.selectedItems[ui.item.value]) !== "undefined") {
	            		return false;
	            	}

	                $("<div></div>")
	                    .addClass("ui-autocomplete-multiselect-item")
	                    .text(ui.item.label)
	                    .data('value', ui.item.value)
	                    .append(
	                        $("<span></span>")
	                            .addClass("ui-icon ui-icon-close")
	                            .click(function(clickEvent){
	                                var item = $(this).parent();
	                                delete self.selectedItems[item.data('value')];
	                                item.remove();

	                                if(jQuery.isEmptyObject(self.selectedItems)) {
				                    	self.placeholder.show();
				                    	self.element.hide();
				                    }

	                                o.leyka_select_callback(self.selectedItems);

	                                if(clickEvent) {
	                                	clickEvent.stopPropagation();
	                            	}
	                            })
	                    )
	                    .insertBefore(self.element);
	                
	                //self.selectedItems[ui.item.label] = ui.item;
	                self.selectedItems[ui.item.value] = ui.item;
	                self._value("");
	                o.leyka_select_callback(self.selectedItems);
	                return false;
	            }

                if(o.pre_selected_values.length) {
                	$.each(o.pre_selected_values, function(index, el){
                		o.select(null, el);
	                });
                	self.placeholder.hide();
                    self.element.css('display', 'block');
                	self.element.show();
                }
	            
	            /*self.options.open = function(e, ui) {
	                var pos = self.multiselect.position();
	                pos.top += self.multiselect.height();
	                self.menu.element.position(pos);
	            }*/
	        }

	        return this;
	    },
	    reset: function(){
	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
	        	self.selectedItems = [];
	        	self.element.parent().find('.ui-autocomplete-multiselect-item').remove();
            	self.placeholder.show();
            	self.element.hide();

            	o.leyka_select_callback(self.selectedItems);
	    	}
	    }
	});	
}

/* jQuery ui-datepicker extension */

/**
 *
 * https://gist.github.com/Artemeey/8bacd37964a8069a2eeee8c9b0bd2e44/
 *
 * Version: 1.0 (15.06.2016)
 * Requires: jQuery v1.8+
 * Requires: jQuery-UI v1.10+
 *
 * Copyright (c) 2016 Artemeey
 * Under MIT and GPL licenses:
 *  http://www.opensource.org/licenses/mit-license.php
 *  http://www.gnu.org/licenses/gpl.html
 *
 * sample:
 * $('.datepicker').datepicker({
		range:'period', // 'period' or 'multiple'
		onSelect:function(dateText, inst, extensionRange){
			// range - new argument!
			switch(inst.settings.range){
				case 'period':
					console.log(extensionRange.startDateText);
					console.log(extensionRange.endDateText);
					console.log(extensionRange.startDate);
					console.log(extensionRange.endDate);
					break;
				case 'multiple':
					console.log(extensionRange.dates); // object, width UTC-TIME keys
					console.log(extensionRange.datesText); // object, width UTC-TIME keys
					break;
			}
		}
	});
 *
 * extension styles:
 * .selected
 * .selected-start
 * .selected-end
 * .first-of-month
 * .last-of-month
 *
 */
if(jQuery.datepicker) {
	let $ = jQuery;

$.datepicker._get_original = $.datepicker._get;
$.datepicker._get = function(inst, name){
	var func = $.datepicker._get_original(inst, name);

	var range = inst.settings['range'];
	if(!range) return func;

	var that = this;

	switch(range){
		case 'period':
		case 'multiple':
			var datepickerExtension = $(this.dpDiv).data('datepickerExtensionRange');
			if(!datepickerExtension){
				datepickerExtension = new _datepickerExtension();
				$(this.dpDiv).data('datepickerExtensionRange', datepickerExtension);
			}
			datepickerExtension.range = range;
			datepickerExtension.range_multiple_max = inst.settings['range_multiple_max'] || 0;

			switch(name){
				case 'onSelect':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(dateText, inst){
						datepickerExtension.onSelect(dateText, inst);
						func_original(dateText, inst, datepickerExtension);

						 // hide fix
						that._datepickerShowing = false;
						setTimeout(function(){
							that._updateDatepicker(inst);
							that._datepickerShowing = true;
						});

						datepickerExtension.setClassActive(inst);
					};

					break;
				case 'beforeShowDay':
					var func_original = func;
					if(!func_original) func_original = function(){ return [true, '']; };

					func = function(date){
						var state = func_original(date);
						state = datepickerExtension.fillDay(date, state);

						return state;
					};

					break;
				case 'beforeShow':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(input, inst){
						func_original(input, inst);

						datepickerExtension.setClassActive(inst);
					};

					break;
				case 'onChangeMonthYear':
					var func_original = func;
					if(!func_original) func_original = function(){};

					func = function(year, month, inst){
						func_original(year, month, inst);

						datepickerExtension.setClassActive(inst);
					};

					break;
			}
			break;
	}

	return func;
};

$.datepicker._setDate_original = $.datepicker._setDate;
$.datepicker._setDate = function(inst, date, noChange){
	var range = inst.settings['range'];
	if(!range) return $.datepicker._setDate_original(inst, date, noChange);

	var datepickerExtension = this.dpDiv.data('datepickerExtensionRange');
	if(!datepickerExtension) return $.datepicker._setDate_original(inst, date, noChange);

	switch(range){
		case 'period':
			if(!(typeof(date) == 'object' && date.length != undefined)){ date = [date, date]; }

			datepickerExtension.step = 0;

			$.datepicker._setDate_original(inst, date[0], noChange);
			datepickerExtension.startDate = this._getDate(inst);
			datepickerExtension.startDateText = this._formatDate(inst);

			if(!date[1]) {
				date[1] = date[0];
			}
			$.datepicker._setDate_original(inst, date[1], noChange);
			datepickerExtension.endDate = this._getDate(inst);
			datepickerExtension.endDateText = this._formatDate(inst);

			datepickerExtension.setClassActive(inst);

			break;
		case 'multiple':
			if(!(typeof(date) == 'object' && date.length != undefined)){ date = [date]; }

			datepickerExtension.dates = [];
			datepickerExtension.datesText = [];

			var that = this;
			$.map(date, function(date_i){
				$.datepicker._setDate_original(inst, date_i, noChange);
				datepickerExtension.dates.push(that._getDate(inst));
				datepickerExtension.datesText.push(that._formatDate(inst));
			});

			datepickerExtension.setClassActive(inst);

			break;
	}
};

var _datepickerExtension = function(){
	this.range = false,
	this.range_multiple_max = 0,
	this.step = 0,
	this.dates = [],
	this.datesText = [],
	this.startDate = null,
	this.endDate = null,
	this.startDateText = '',
	this.endDateText = '',
	this.onSelect = function(dateText, inst){
		switch(this.range){
			case 'period': return this.onSelectPeriod(dateText, inst); break;
			case 'multiple': return this.onSelectMultiple(dateText, inst); break;
		}
	},
	this.onSelectPeriod = function(dateText, inst){
		this.step++;
		this.step %= 2;

		if(this.step){
			// выбирается первая дата
			this.startDate = this.getSelectedDate(inst);
			this.endDate = this.startDate;

			this.startDateText = dateText;
			this.endDateText = this.startDateText;
		}else{
			// выбирается вторая дата
			this.endDate = this.getSelectedDate(inst);
			this.endDateText = dateText;

			if(this.startDate.getTime() > this.endDate.getTime()){
				this.endDate = this.startDate;
				this.startDate = this.getSelectedDate(inst);

				this.endDateText = this.startDateText;
				this.startDateText = dateText;
			}
		}
	},
	this.onSelectMultiple = function(dateText, inst){
		var date = this.getSelectedDate(inst);

		var index = -1;
		$.map(this.dates, function(date_i, index_date){
			if(date_i.getTime() == date.getTime()) index = index_date;
		});
		var indexText = $.inArray(dateText, this.datesText);

		if(index != -1) this.dates.splice(index, 1);
		else this.dates.push(date);

		if(indexText != -1) this.datesText.splice(indexText, 1);
		else this.datesText.push(dateText);

		if(this.range_multiple_max && this.dates.length > this.range_multiple_max){
			this.dates.splice(0, 1);
			this.datesText.splice(0, 1);
		}
	},
	this.fillDay = function(date, state){
		var _class = state[1];

		if(date.getDate() == 1) _class += ' first-of-month';
		if(date.getDate() == new Date(date.getFullYear(), date.getMonth()+1, 0).getDate()) _class += ' last-of-month';

		state[1] = _class.trim();

		switch(this.range){
			case 'period': return this.fillDayPeriod(date, state); break;
			case 'multiple': return this.fillDayMultiple(date, state); break;
		}
	},
	this.fillDayPeriod = function(date, state){
		if(!this.startDate || !this.endDate) return state;

		var _class = state[1];

		if(date >= this.startDate && date <= this.endDate) _class += ' selected';
		if(date.getTime() == this.startDate.getTime()) _class += ' selected-start';
		if(date.getTime() == this.endDate.getTime()) _class += ' selected-end';

		state[1] = _class.trim();

		return state;
	},
	this.fillDayMultiple = function(date, state){
		var _class = state[1];

		var date_is_selected = false;
		$.map(this.dates, function(date_i){
			if(date_i.getTime() == date.getTime()) date_is_selected = true;
		});
		if(date_is_selected) _class += ' selected selected-start selected-end';

		state[1] = _class.trim();

		return state;
	},
	this.getSelectedDate = function(inst){
		return new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
	};
	this.setClassActive = function(inst){
		var that = this;
		setTimeout(function(){
			$('td.selected > *', inst.dpDiv).addClass('ui-state-active');
			if(that.range == 'multiple') $('td:not(.selected)', inst.dpDiv).removeClass('ui-datepicker-current-day').children().removeClass('ui-state-active');
		});
	};
}; 	

}
/** Modules (Gateways & Extensions) settings board common JS. */

// Filter an extension cards list:
jQuery(document).ready(function($){

    let $filter = $('.leyka-modules-filter'),
        $extensions_list = $('.modules-cards-list'),
        extensions_filter = {};

    $filter.find('.filter-toggle').click(function(){
        $(this).closest('.filter-area').toggleClass('show');
    });

    $filter.find('.filter-category-show-filter').click(function(e){

        e.preventDefault();

        $(this).closest('.filter-area').toggleClass('show');

    });

    $filter.find('.filter-category-reset-filter').click(function(e){

        e.preventDefault();

        reset_filter();

    });

    $filter.find('.filter-category-item').click(function(e){

        e.preventDefault();

        toggle_filter_item($(this));
        apply_filter();

    });

    function reset_filter() {

        extensions_filter = {};

        $filter.find('.filter-category-item').removeClass('active');
        apply_filter();

    }

    function apply_filter() {
        if(Object.keys(extensions_filter).length) {

            $extensions_list.find('.module-card').hide();
            $extensions_list.find('.module-card.' + Object.keys(extensions_filter).join('.')).show();

        } else {
            $extensions_list.find('.module-card').show();
        }
    }

    function toggle_filter_item($filter_item) {

        $filter_item.toggleClass('active');

        if($filter_item.hasClass('active')) {
            extensions_filter[$filter_item.data('category')] = true;
        } else {
            delete extensions_filter[$filter_item.data('category')];
        }

    }

});
/** Common settings functions */

jQuery(document).ready(function($){

    const $body = $('body');

    // Normal datepicker fields:
    $('input.leyka-datepicker').each(function(){

        let $date_field = $(this);

        $date_field.datepicker({
            changeMonth: true,
            changeYear: true,
            minDate: $date_field.data('min-date') ? $date_field.data('min-date') : '',
            maxDate: $date_field.data('max-date') ? $date_field.data('max-date') : '',
            dateFormat: $date_field.data('date-format') ? $date_field.data('date-format') : 'dd.mm.yy',
            altField: $date_field.data('alt-field') ? $date_field.data('alt-field') : '', // Alt field jQuery selector here
            altFormat: $date_field.data('alt-format') ? $date_field.data('alt-format') : 'yy-mm-dd',
        });

    });
    // Normal datepicker fields - END

    // Ranged (optionally) datepicker fields for admin lists filters:
    jQuery.leyka_fill_datepicker_input_period = function leyka_fill_datepicker_input_period(inst, extension_range) {

        let input_text = extension_range.startDateText;
        if(extension_range.endDateText && extension_range.endDateText !== extension_range.startDateText) {
            input_text += ' - '+extension_range.endDateText;
        }
        $(inst.input).val(input_text);

    };

    jQuery.leyka_admin_filter_datepicker_ranged = function($input /*, options*/){

        $input.datepicker({
            range: 'period',
            onSelect:function(dateText, inst, extensionRange){
                $.leyka_fill_datepicker_input_period(inst, extensionRange);
            },

            beforeShow: function(input, instance) {

                let selectedDatesStr = $(input).val(),
                    selectedDatesStrList = selectedDatesStr.split(' - '),
                    selectedDates = [];

                for(let i in selectedDatesStrList) {

                    if(selectedDatesStrList[i]) {

                        let singleDate;
                        try {
                            singleDate = $.datepicker
                                .parseDate($(input).datepicker('option', 'dateFormat'), selectedDatesStrList[i]);
                        } catch {
                            singleDate = new Date();
                        }

                        selectedDates.push(singleDate);

                    }

                }

                $(instance.input).val(selectedDates[0]);
                $(instance.input).datepicker('setDate', selectedDates);

                setTimeout(function(){
                    $.leyka_fill_datepicker_input_period(instance, $(instance.dpDiv).data('datepickerExtensionRange'));
                });

            }
        });

    };
    // Ranged (optionally) datepicker fields for admin lists filters - END

    // Ranged datepicker fields (for admin list filters mostly):
    $.leyka_admin_filter_datepicker_ranged($('input.datepicker-ranged-selector'), {
        warningMessage: leyka.first_donation_date_incomplete_message
    });
    // Ranged datepicker fields - END

    // Campaigns autocomplete select:
    jQuery.leyka_admin_campaigns_select = function($text_selector_field /*, options*/){

        $text_selector_field = $($text_selector_field);

        let $list_select_field = $text_selector_field.siblings('.leyka-campaigns-select'),
            is_multiple_values = !!$list_select_field.prop('multiple'),
            selected_values = [];

        if(is_multiple_values) {
            $list_select_field.find('option').each(function(){

                let $this = $(this);
                selected_values.push({item: {label: $.trim($this.text()), value: $this.val()}});

            });
        }

        let autocomplete_settings = {
            source: leyka.ajaxurl+'?action=leyka_campaigns_autocomplete',
            multiselect: is_multiple_values,
            minLength: 0,
            search_on_focus: true,
        };

        if(is_multiple_values) {

            autocomplete_settings.pre_selected_values = selected_values;
            autocomplete_settings.leyka_select_callback = function(selected_items){

                $list_select_field.html('');

                for(let value in selected_items) {
                    $('<option></option>').val(value).prop('selected', true).appendTo($list_select_field);
                }

            }

        } else {
            autocomplete_settings.select = function(e, ui){

                this.value = ui.item.label;
                $list_select_field.val(ui.item.value);

                if($list_select_field.data('campaign-payment-title-selector')) {
                    $($list_select_field.data('campaign-payment-title-selector')).html(ui.item.payment_title);
                }

                return false;

            };
        }

        $text_selector_field.autocomplete(autocomplete_settings);

    };

    // Campaign(s) select fields (for admin list filters mostly):
    $('input.leyka-campaigns-selector:not(.leyka-js-dont-initialize-common-widget)').each(function(){
        $.leyka_admin_campaigns_select($(this));
    });
    // Campaign(s) select fields  - END

    // Donor's name/email field:
    $('input.leyka-donor-name-email-selector').each(function(){

        let $field = $(this);

        $field.autocomplete({ /** @todo Add nonce to the query */
            source: leyka.ajaxurl+'?action=leyka_donors_autocomplete'
                +($field.data('search-donors-in') ? '&type='+$field.data('search-donors-in') : ''),
            minLength: 0,
            search_on_focus: true
        });

    });
    // Donor's name/email field - END

    if(leyka_ui_widget_available('accordion')) {
        $('.ui-accordion').each(function(){

            let $this = $(this),
                widget_options = {heightStyle: 'content',};

            $this.accordion(widget_options);

        });
    }

    if(leyka_ui_widget_available('wpColorPicker', $.wp)) {
        $('.leyka-setting-field.colorpicker').wpColorPicker({ // Colorpicker fields
            change: function(e, ui) {
                $(e.target).parents('.field').find('.leyka-colorpicker-value').val(ui.color.toString()).change();
            }
        });
    }

    if(leyka_ui_widget_available('selectmenu')) {
        $('.leyka-select-menu').selectmenu();
    }

    // Support metaboxes ONLY where needed (else there are metabox handling errors on the wrong pages):
    $('input.leyka-support-metabox-area').each(function(){
        leyka_support_metaboxes($(this).val());
    });

    // Custom CSS editor fields:
    let $css_editor = $('.css-editor-field'),
        editor = {};

    if(leyka_ui_widget_available('codeEditor', wp) && $css_editor.length) {

        let editor_settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editor_settings.codemirror = _.extend(
            {},
            editor_settings.codemirror, {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css',
            });
        editor = wp.codeEditor.initialize($css_editor, editor_settings);

        if(leyka_is_gutenberg_active()) {

            wp.data.subscribe(function(){ // Obtain the CodeMirror instance, then manually copy editor content into it's textarea
                $css_editor.next('.CodeMirror').get(0).CodeMirror.save();
            });

        }

        $css_editor.data('code-editor-object', editor);

        $('.css-editor-reset-value').on('click.leyka', function(e){ // Additional CSS value reset

            e.preventDefault();

            let $this = $(this),
                $css_editor_field = $this.siblings('.css-editor-field'),
                template_id = $this
                    .parents('.campaign-css')
                    .siblings('.campaign-template')
                        .find('[name="campaign_template"]').val(),
                original_value = $this.siblings('.css-editor-'+template_id+'-original-value').val();

            $css_editor_field.val(original_value);
            editor.codemirror.getDoc().setValue(original_value);

        });

    }
    // Custom CSS editor fields - END

    // Ajax file upload fields support:
    $body.on('click.leyka', '.file-upload-field input[type="file"]', function(e){ // Just to be sure that the input will be called
        e.stopPropagation();
    }).on('change.leyka', '.file-upload-field input[type="file"]', function(e){

        if( !e.target.files ) {
            return;
        }

        let $file_input = $(this),
            $field_wrapper = $file_input.parents('.leyka-file-field-wrapper'),
            $upload_button_wrapper = $field_wrapper.find('.upload-field'),
            option_id = $upload_button_wrapper.data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $ajax_loading = $field_wrapper.find('.loading-indicator-wrap'),
            $error = $field_wrapper.siblings('.field-errors'),
            $main_field = $field_wrapper.find('input.leyka-upload-result'),
            data = new FormData(); // Need to use a FormData object here instead of a generic object

        data.append('action', 'leyka_files_upload');
        data.append('option_id', option_id);
        data.append('nonce', $file_input.data('nonce'));
        data.append('files', []);

        $.each(e.target.files, function(key, value){
            data.append('files', value);
        });

        $ajax_loading.show();
        $error.html('').hide();

        $.ajax({
            url: leyka.ajaxurl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(response){

                $ajax_loading.hide();

                if(
                    typeof response === 'undefined'
                    || typeof response.status === 'undefined'
                    || (response.status !== 0 && typeof response.message === 'undefined')
                ) {
                    return $error.html(leyka.common_error_message).show();
                } else if(response.status !== 0 && typeof response.message !== 'undefined') {
                    return $error.html(response.message).show();
                }

                let preview_html = response.type.includes('image/') ?
                    '<img class="leyka-upload-image-preview" src="'+response.url+'" alt="">' : response.filename;

                $file_preview.show().find('.file-preview').html(preview_html);
                $upload_button_wrapper.hide(); // Hide the "upload" button when picture is uploaded

                $main_field.val(response.path); // Option value will keep the file relative path in WP uploads dir

            },
            error: function(){

                $ajax_loading.hide();
                $error.html(leyka.common_error_message).show();

            }
        });

    });

    $body.on('click.leyka', '.leyka-upload-field-wrapper .delete-uploaded-file', function(e){ // Mark uploaded file to be removed

        e.preventDefault();

        let $delete_link = $(this),
            $field_wrapper = $delete_link.parents('.leyka-upload-field-wrapper'),
            // option_id = $field_wrapper.find('.upload-field').data('option-id'),
            $file_preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result');

        $file_preview.hide().find('.file-preview').html('');
        $field_wrapper.find('.upload-field').show(); // Show the "upload" button when uploaded picture is deleted
        $main_field.val('');

    });
    // Ajax file upload fields - END

    // Media library upload fields:
    $body.on('click.leyka', '.media-upload-field', function(e){

        e.preventDefault();

        let $field = $(this),
            $field_wrapper = $field.parents('.leyka-media-upload-field-wrapper'),
            // option_id = $upload_button_wrapper.data('option-id'),
            $preview = $field_wrapper.find('.uploaded-file-preview'),
            $main_field = $field_wrapper.find('input.leyka-upload-result'),
            media_uploader = wp.media({
                title: $field.data('upload-title') ?
                    $field.data('upload-title') : leyka.media_upload_title,
                button: {
                    text: $field.data('upload-button-label') ? $field.data('upload-button-label') : leyka.media_upload_button_label,
                },
                library: {type: $field.data('upload-files-type') ? $field.data('upload-files-type') : 'image'},
                multiple: $field.data('upload-is-multiple') ? !!$field.data('upload-is-multiple') : false
            }).on('select', function(){ // It's a wp.media event, so dont't use "select.leyka" events types

                let attachment = media_uploader.state().get('selection').first().toJSON();

                $preview
                    .show()
                    .find('.file-preview')
                    .html('<img class="leyka-upload-image-preview" src="'+attachment.url+'" alt="">');

                $field.hide(); // Hide the "upload" button when picture is uploaded

                $main_field.val(attachment.id);

            }).open();

    });
    // Media library upload fields - END

    // Expandable options sections (portlets only):
    /** @todo Remove this completely when all portlets are converted to metaboxes */
    $('.leyka-options-section .header h3').click(function(e){

        e.preventDefault();

        $(this).closest('.leyka-options-section').toggleClass('collapsed');

    });

    // Delete fields comments:
    // $('.leyka-admin .leyka-options-section .field-component.help').contents().filter(function(){
    //     return this.nodeType === 1 || this.nodeType === 3; // 1 is for links, 3 - for plain text
    // }).remove();

    // Connect to stats:
    if($('#leyka_send_plugin_stats-y-field').prop('checked')) {

        $('.leyka-options-section#stats_connections')
            .find('.submit input')
            .removeClass('button-primary')
            .addClass('disconnect-stats')
            .val(leyka.disconnect_stats);

    }

    $('#connect-stats-button').click(function(){
        if($(this).hasClass('disconnect-stats')) {
            $('#leyka_send_plugin_stats-n-field').prop('checked', true);
        } else {
            $('#leyka_send_plugin_stats-y-field').prop('checked', true);
        }
    });

    // Section tabs:
    $('.section-tab-nav-item').click(function(e){

        e.preventDefault();

        let $tabs = $(this).closest('.section-tabs-wrapper');

        $tabs.find('.section-tab-nav-item').removeClass('active');
        $tabs.find('.section-tab-content').removeClass('active');

        $(this).addClass('active');
        $tabs.find('.section-tab-content.tab-' + $(this).data('target')).addClass('active');

    });

    // Screenshots nav:
    $('.tab-screenshot-nav img').click(function(e){

        e.preventDefault();

        let $currentScreenshots = $(this).closest('.tab-screenshots'),
            $currentVisibleScreenshot = $currentScreenshots.find('.tab-screenshot-item.active'),
            $nextScreenshot = null;

        if($(this).closest('.tab-screenshot-nav').hasClass('left')) {
            $nextScreenshot = $currentVisibleScreenshot.prev();
            if(!$nextScreenshot.hasClass('tab-screenshot-item')) {
                $nextScreenshot = $currentScreenshots.find('.tab-screenshot-item').last();
            }
        } else {
            $nextScreenshot = $currentVisibleScreenshot.next();
            if(!$nextScreenshot.hasClass('tab-screenshot-item')) {
                $nextScreenshot = $currentScreenshots.find('.tab-screenshot-item').first();
            }
        }

        if($nextScreenshot) {
            $currentVisibleScreenshot.removeClass('active');
            $nextScreenshot.addClass('active');
        }

    });

    $('[name*="show_donation_comment_field"]').on('change.leyka', function(){

        let $this = $(this),
            checkbox_id = $this.attr('id'),
            length_field_wrapper_id = checkbox_id.replace('_show_donation_comment_field-field', '_donation_comment_max_length-wrapper');

        if($this.prop('checked')) {
            $('#'+length_field_wrapper_id).show();
        } else {
            $('#'+length_field_wrapper_id).hide();
        }

    }).change();

    // Manual emails sending:
    $('.send-donor-thanks').click(function(e){

        e.preventDefault();

        let $this = $(this),
            $wrap = $this.parent(),
            donation_id = $wrap.data('donation-id');

        $this.fadeOut(100, function(){
            $this.html('<img src="'+leyka.ajax_loader_url+'" alt="">').fadeIn(100);
        });

        $wrap.load(leyka.ajaxurl, {
            action: 'leyka_send_donor_email',
            nonce: $wrap.data('nonce'),
            donation_id: donation_id
        });

    });

    // Tooltips:
    let $tooltips = $body.find('.has-tooltip');

    $.widget('custom.leyka_admin_tooltip', $.ui.tooltip, {
        _init: function(){

            this._super(); // Parent _init() method call, just in case

            let $tooltip_element = $(this.element),
                options = {
                    classes: {
                        'ui-tooltip':
                            ($tooltip_element.hasClass('leyka-tooltip-wide') ? 'leyka-tooltip-wide' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-white') ? 'leyka-tooltip-white' : '')+' '
                            +($tooltip_element.hasClass('leyka-tooltip-align-left') ? 'leyka-tooltip-align-left' : '')+' '
                            +$tooltip_element.data('tooltip-additional-classes')
                    },
                    content: function(){

                        let $element = $(this),
                            tooltip_content = $element.siblings('.leyka-tooltip-content:first').html();

                        return tooltip_content ? tooltip_content : $element.prop('title');

                    },
                    // position: {my: 'left top + 15', at: 'left bottom', collision: 'flipfit'} // Default position settings
                    position: {my: 'left top + 0', at: 'center', collision: 'flipfit'}
                };

            if($tooltip_element.hasClass('leyka-tooltip-on-click')) { // Tooltips on click
                options.items = '.has-tooltip.tooltip-opened';
            }

            this.option(options); // Redefine options, set them to Leyka setup

        }
    });

    if($tooltips.length && typeof $().tooltip !== 'undefined' ) {

        // Init all tooltips on initial page rendering:
        $tooltips.each(function(i, element){
            $(element).leyka_admin_tooltip();
        });

        // Tooltips on click:
        let $tooltips_on_click = $('.has-tooltip.leyka-tooltip-on-click');

        $tooltips_on_click.on('click.leyka', function(){ // Tooltips on click - open

            let $element = $(this);
            if($element.hasClass('leyka-tooltip-on-click')) {

                if($element.hasClass('tooltip-opened')) { // Tootips on click - hide
                    $element.leyka_admin_tooltip('close').removeClass('tooltip-opened');
                } else {
                    $element.addClass('tooltip-opened').leyka_admin_tooltip('open'); //.mouseenter();
                }
            }

        }).on('mouseout.leyka', function(e){ // Prevent mouseout and other related events from firing their handlers
            e.stopImmediatePropagation();
        });

        // Close opened tooltip when clicked elsewhere:
        $body.on('click.leyka', function(e){

            if($tooltips_on_click.length) {

                $tooltips_on_click.filter('.tooltip-opened').each(function(i, element){
                    if(element !== e.target) {
                        $(element).leyka_admin_tooltip('close').removeClass('tooltip-opened');
                    }
                });

            }

        });
        // Tooltips on click - END

    }
    // Tooltips - END

    // Multi-valued item complex fields:
    $('.leyka-main-multi-items').each(function(index, outer_items_wrapper){

        let $items_wrapper = $(outer_items_wrapper),
            $item_template = $items_wrapper.siblings('.item-template'),
            $add_item_button = $items_wrapper.siblings('.add-item'),
            items_cookie_name = $items_wrapper.data('items-cookie-name'),
            closed_boxes = typeof $.cookie(items_cookie_name) === 'string' ? JSON.parse($.cookie(items_cookie_name)) : [];

        if($.isArray(closed_boxes)) { // Close the item boxes needed
            $.each(closed_boxes, function(key, value){
                $items_wrapper.find('#'+value).addClass('closed');
            });
        }

        $items_wrapper.sortable({
            placeholder: 'ui-state-highlight', // A class for dropping item placeholder
            update: function(event, ui){

                let items_options = [];
                $.each($items_wrapper.sortable('toArray'), function(key, item_id){ // Value is an item ID (generated randomly)

                    if( !item_id.length ) {
                        return;
                    }

                    let item_options = {'id': item_id}; // Assoc. array key should be initialized explicitly

                    $.each($items_wrapper.find('#'+item_id).find(':input'), function(key, item_setting_input){

                        let $input = $(item_setting_input),
                            input_name = $input.prop('name')
                                .replace($items_wrapper.data('item-inputs-names-prefix'), '')
                                .replace('[]', '');

                        if($input.prop('type') === 'checkbox') {
                            item_options[input_name] = $input.prop('checked');
                        } else {
                            item_options[input_name] = $input.val();
                        }

                    });

                    items_options.push(item_options);

                });

                $items_wrapper.siblings('input.leyka-items-options').val( encodeURIComponent(JSON.stringify(items_options)) );

            }
        });

        $items_wrapper.on('click.leyka', '.item-box-title', function(e){

            let $this = $(this),
                $current_box = $this.parents('.multi-valued-item-box');

            $current_box.toggleClass('closed');

            // Save the open/closed state for all items boxes:
            let current_box_id = $current_box.prop('id'),
                current_box_index = $.inArray(current_box_id, closed_boxes);

            if(current_box_index === -1 && $current_box.hasClass('closed')) {
                closed_boxes.push(current_box_id);
            } else if(current_box_index !== -1 && !$current_box.hasClass('closed')) {
                closed_boxes.splice(current_box_index, 1);
            }

            $.cookie(items_cookie_name, JSON.stringify(closed_boxes));

        });

        $items_wrapper.on('click.leyka', '.delete-item', function(e){

            e.preventDefault();

            if($items_wrapper.find('.multi-valued-item-box').length > $items_wrapper.data('min-items')) {

                $(this).parents('.multi-valued-item-box').remove();
                $items_wrapper.sortable('option', 'update')();

            }

            let items_current_count = $items_wrapper.find('.multi-valued-item-box').length;
            if($items_wrapper.data('min-items') && items_current_count <= $items_wrapper.data('min-items')) {
                $items_wrapper.find('.delete-item').addClass('inactive');
            }
            if(items_current_count < $items_wrapper.data('max-items')) {
                $add_item_button.removeClass('inactive');
            }

        });

        $add_item_button.on('click.leyka', function(e){

            e.preventDefault();

            if($add_item_button.hasClass('inactive')) {
                return;
            }

            // Generate & set the new item ID:
            let new_item_id = '';
            do {
                new_item_id = leyka_get_random_string(4);
            } while($items_wrapper.find('#item-'+new_item_id).length);

            $item_template
                .clone()
                .appendTo($items_wrapper)
                .removeClass('item-template')
                .prop('id', 'item-'+new_item_id)
                .show();

            if($items_wrapper.find('#item-'+new_item_id)) {

                $items_wrapper.sortable('option', 'update')();

                const $new_item = $('#item-'+new_item_id);

                if($new_item && $new_item.hasClass('payment-amount-option')) {

                    const payment_type = $new_item.hasClass('payment_single') ? 'single' : 'recurring';

                    $new_item.find('input').each((idx, payment_amount_option_input) => {

                        if($(payment_amount_option_input).prop('id').indexOf('_amount_') !== -1) {

                            $(payment_amount_option_input)
                                .prop('id', 'leyka_payment_'+payment_type+'_amount_'+new_item_id+'-field')
                                .prop('name', 'leyka_payment_'+payment_type+'_amount_'+new_item_id);

                        } else if($(payment_amount_option_input).prop('id').indexOf('_description_') !== -1) {

                            $(payment_amount_option_input)
                                .prop('id', 'leyka_payment_'+payment_type+'_description_'+new_item_id+'-field')
                                .prop('name', 'leyka_payment_'+payment_type+'_description_'+new_item_id);

                        };

                    })

                }

            }

            let items_current_count = $items_wrapper.find('.multi-valued-item-box').length;

            if($items_wrapper.data('max-items') && items_current_count >= $items_wrapper.data('max-items')) {
                $add_item_button.addClass('inactive');
            }

            if(items_current_count <= 1) { // When adding initial item
                $items_wrapper.find('.delete-item').addClass('inactive');
            } else if(items_current_count > 1) {
                $items_wrapper.find('.delete-item').removeClass('inactive');
            }

        });

        // No items added yet - add the first (empty) one:
        if($items_wrapper.data('show-new-item-if-empty') && !$items_wrapper.find('.multi-valued-item-box').length) {
            $add_item_button.trigger('click.leyka');
        }

        // Refresh the main items option value before submit:
        function leyka_pre_submit_multi_items(e) {

            let items_options = [];
            $.each($items_wrapper.sortable('toArray'), function(key, item_id){ // Value is an item ID (generated randomly)

                if( !item_id.length ) {
                    return;
                }

                let item_options = {'id': item_id}; // Assoc. array key should be initialized explicitly

                $.each($items_wrapper.find('#'+item_id).find(':input'), function(key, item_setting_input){

                    let $input = $(item_setting_input),
                        input_name = $input.prop('name')
                            .replace($items_wrapper.data('item-inputs-names-prefix'), '')
                            .replace('[]', '');

                    if($input.prop('type') === 'checkbox') {
                        item_options[input_name] = $input.prop('checked');
                    } else {
                        item_options[input_name] = $input.val();
                    }

                });

                if ($items_wrapper.hasClass('leyka-main-payments-amounts')) {

                    const item_pure_id = item_id.replace('item-','');
                    let skip = false;

                    items_options.forEach((other_item_option, idx) => {

                        const other_item_pure_id = other_item_option.id.replace('item-','');

                        if ((('leyka_payment_single_amount_'+item_pure_id in item_options) &&
                            (item_options['leyka_payment_single_amount_'+item_pure_id] == other_item_option['leyka_payment_single_amount_'+other_item_pure_id]) &&
                            (item_options['leyka_payment_single_description_'+item_pure_id] == other_item_option['leyka_payment_single_description_'+other_item_pure_id])) ||
                            (('leyka_payment_recurring_amount_'+item_pure_id in item_options) &&
                            (item_options['leyka_payment_recurring_amount_'+item_pure_id] == other_item_option['leyka_payment_recurring_amount_'+other_item_pure_id]) &&
                            (item_options['leyka_payment_recurring_description_'+item_pure_id] == other_item_option['leyka_payment_recurring_description_'+other_item_pure_id]))
                        ) {
                            skip = true;
                        }

                    })

                    if (skip) {
                        $('#'+item_id).remove();
                        return;
                    }

                }

                items_options.push(item_options);

            });

            $items_wrapper.sortable('option', 'update')();

        }

        if(leyka_is_gutenberg_active()) { // Post edit page - Gutenberg mode

            // Trigger the final multi-items values updating ONLY before main saving submit:
            // (Note: in Gutenberg there are also non-main saves - each metabox is also saved individually, via AJAX)
            const unsubscribe = wp.data.subscribe(function(){

                let code_editor = wp.data.select('core/editor');

                if (code_editor.isSavingPost() && !code_editor.isAutosavingPost() && code_editor.didPostSaveRequestSucceed()) {

                    unsubscribe(); // To avoid muliple calls on ajax savings

                    leyka_pre_submit_multi_items();

                }

            });

        } else { // Post edit page (classic editor), general admin pages
            $items_wrapper.parents('form:first').on('submit.leyka', leyka_pre_submit_multi_items);
        }
        // Refresh the main items option value before submit - END

        // Campaigns select fields:

        // Init all existing campaigns list fields on page load:
        $items_wrapper.find('input.leyka-campaigns-selector').each(function(){
            $.leyka_admin_campaigns_select($(this));
        });

        // Init campaign list for a new additional field:
        $add_item_button.on('click.leyka', function(){

            $.leyka_admin_campaigns_select(
                $items_wrapper
                    .find('.multi-valued-item-box:last-child .autocomplete-select[name="campaigns\[\]"]')
                    .siblings('input.leyka-campaigns-selector')
            );

            $.leyka_admin_campaigns_select(
                $items_wrapper
                    .find('.multi-valued-item-box:last-child .autocomplete-select[name="campaigns_exceptions\[\]"]')
                    .siblings('input.leyka-campaigns-selector')
            );

        });

        // Hide/show the campaigns list field when "for all Ccampaigns" checkbox is checked/unchecked:
        $items_wrapper.on('change.leyka', '.item-for-all-campaigns input:checkbox', function(){

            let $checkbox = $(this),
                $campaigns_list_field_wrapper = $checkbox.parents('.single-line').siblings('.single-line.campaigns-list-select'),
                $campaigns_exceptions_list_field_wrapper = $checkbox
                    .parents('.single-line')
                    .siblings('.single-line.campaigns-exceptions-list-select');

            if($checkbox.prop('checked')) {

                $campaigns_list_field_wrapper.hide();
                $campaigns_exceptions_list_field_wrapper.show();

            } else {

                $campaigns_list_field_wrapper.show();
                $campaigns_exceptions_list_field_wrapper.hide();

            }

        });
        // Hide/show the campaigns list field - END

        // Campaigns select fields - END

        // TODO - Temporary solution. Need to check save for multi-item fields (campaign page)
        $items_wrapper.on('change.leyka', '.payment-amount-option-description input, .payment-amount-option-amount input', function(){
            $items_wrapper.sortable('option', 'update')();
        });

    });
    // Multi-valued item complex fields - END

    // Donors management & Donors' accounts fields logical link:
    $('input[name="leyka_donor_accounts_available"]').change(function(){

        let $accounts_available_field = $(this),
            $donors_management_available_field = $('input[name="leyka_donor_management_available"]');

        if($accounts_available_field.prop('checked')) {
            $donors_management_available_field
                .prop('checked', 'checked')
                .prop('disabled', 'disabled')
                .parents('.field-component').addClass('disabled');
        } else {
            $donors_management_available_field
                .prop('disabled', false)
                .parents('.field-component').removeClass('disabled');
        }

    }).change();

});
/** Admin utilities & tools */

function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}

//polyfill for unsupported Number.isInteger
//https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger
Number.isInteger = Number.isInteger || function(value) {
    return typeof value === "number" &&
        isFinite(value) &&
        Math.floor(value) === value;
};

/** @var e JS keyup/keydown event */
function leyka_is_digit_key(e, numpad_allowed) {

    if(typeof numpad_allowed == 'undefined') {
        numpad_allowed = true;
    } else {
        numpad_allowed = !!numpad_allowed;
    }

    if( // Allowed special keys
        e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 13 || // Backspace, delete, tab, enter
        (e.keyCode == 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode == 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    ) {
        return true;
    }

    if(numpad_allowed) {
        if( !e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57 ) {
            return true;
        } else {
            return e.keyCode >= 96 && e.keyCode <= 105;
        }
    } else {
        return !(e.shiftKey || e.keyCode < 48 || e.keyCode > 57);
    }

}

/** @var e JS keyup/keydown event */
function leyka_is_special_key(e) {
    return ( // Allowed special keys
        e.keyCode === 46 || e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 13 || // Backspace, delete, tab, enter
        e.keyCode === 9 || // Tab
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    );
}

function leyka_make_password(pass_length) {

    let text = '',
        possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for(let i = 0; i < parseInt(pass_length); i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;

}

/** Get random latin-numeric string with given length. */
function leyka_get_random_string(length = 6) {
    return Array(length + 1).join((Math.random().toString(36)+'00000000000000000').slice(2, 18)).slice(0, length);
}

function leyka_validate_donor_name(name_string) {
    return !name_string.match(/[ !@#$%^&*()+=\[\]{};:"\\|,<>\/?]/);
}

// Plugin metaboxes rendering:
function leyka_support_metaboxes(metabox_area) {

    if(typeof postboxes === 'undefined') {
        console.log('Leyka error: trying to support metaboxes for "'+metabox_area+'" area, but there are no "postboxes" var.');
        return false;
    }

    jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed'); // Close postboxes that should be closed
    postboxes.add_postbox_toggles(metabox_area);

}

// Metabox thumbnails:
jQuery(document).ready(function($){

    $('.postbox').each(function(){

        let $metabox = $(this),
            thumbnail = $metabox.find('.metabox-content').data('thumbnail');

        if(thumbnail) {
            $metabox
                .find('.postbox-header h2.hndle')
                .prepend('<img class="metabox-thumbnail" src="'+leyka.plugin_url+thumbnail+'" alt="">');
        }

    });

});

/** Check if UI widget is available. Widget is looked in $.ui by default. */
function leyka_ui_widget_available(widget = '', object = null) {

    if(object === null && typeof jQuery.ui !== 'undefined') {
        object = jQuery.ui;
    } else if(object === null || typeof object !== 'object') {
        return false;
    }

    return widget.length ? typeof object[widget] !== 'undefined' : typeof object !== 'undefined';

}

function ucfirst(str) {

    if( !str || !str.length ) {
        return '';
    }

    return str.slice(0, 1).toUpperCase() + str.substring(1);

}

function lcfirst(str) {

    if( !str || !str.length ) {
        return '';
    }

    return str.slice(0, 1).toLowerCase() + str.substring(1);

}

/** * @return boolean True if current page is in Gutenberg mode, false otherwise */
function leyka_is_gutenberg_active() {
    return document.body.classList.contains('block-editor-page');
}
/** Additional donation form fields settings JS */

jQuery(document).ready(function($){

    let $additional_fields_settings_wrapper = $('.leyka-admin .additional-fields-settings');
    if( !$additional_fields_settings_wrapper.length || !leyka_ui_widget_available('sortable') ) {
        return;
    }

    let $items_wrapper = $additional_fields_settings_wrapper.find('.leyka-main-multi-items');

    // Change field box title when field title value changes:
    $items_wrapper.on('keyup.leyka change.leyka click.leyka', '[name="leyka_field_title"]', function(){

        let $field_title = $(this),
            $box_title = $field_title.parents('.multi-valued-item-box').find('h2.hndle .title');

        if($field_title.val().length) {
            $box_title.html($field_title.val());
        } else {
            $box_title.html($box_title.data('empty-box-title'));
        }

    });

    // Display/hide the phone field note if field type is changed to/from "phone":
    $items_wrapper.on('change.leyka', '[name="leyka_field_type"]', function(e){

        let $type_field = $(this),
            $phone_note = $type_field.parents('.box-content').find('.phone-field-note');

        if($type_field.val() === 'phone') {
            $phone_note.show();
        } else {
            $phone_note.hide();
        }

    });

    // Pre-submit actions:
    $items_wrapper.parents('form:first').on('submit.leyka', function(e){

        // Validation:
        if( !leyka_all_fields_valid($items_wrapper) ) {
            e.preventDefault();
        }

    });

    // Validate the multi-valued items complex field:
    function leyka_all_fields_valid($fields_wrapper) {

        let fields_valid = true;

        $fields_wrapper.find('.multi-valued-item-box').each(function(index, item_box){

            let $fields_box = $(item_box),
                $box_errors_list = $fields_box.find('.notes-and-errors');

            $box_errors_list.find('.error').remove();

            let $field = $fields_box.find('[name="leyka_field_type"]'),
                $field_outer_wrapper = $field.parents('.option-block');

            $field_outer_wrapper.removeClass('has-errors');

            // Field type isn't selected:
            if( !$field.val().length || $field.val() === '-' ) {

                fields_valid = false;
                $field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

            $field = $fields_box.find('[name="leyka_field_title"]');
            $field_outer_wrapper = $field.parents('.option-block').removeClass('has-errors');

            // Field title isn't entered:
            if( !$field.val().length ) {

                fields_valid = false;
                $field_outer_wrapper.addClass('has-errors');
                $box_errors_list.append('<li class="error">'+leyka.field_x_required.replace('%s', $field_outer_wrapper.find('.leyka-field-inner-wrapper').data('field-title'))+'</li>');

            }

        });

        return fields_valid;

    }

});
// Campaign add/edit page:
jQuery(document).ready(function($){

    let $page_type = $('#originalaction'),
        $post_type = $('#post_type');

    if( !$page_type.length || $page_type.val() !== 'editpost' || !$post_type.length || $post_type.val() !== 'leyka_campaign' ) {
        return;
    }

    // "Daily rouble mode" change:
    let $daily_rouble_mode_wrapper = $('.daily-rouble-settings-wrapper'),
        $daily_rouble_mode = $daily_rouble_mode_wrapper.find('input#daily-rouble-mode-on'),
        $daily_rouble_settings_block = $daily_rouble_mode_wrapper.find('.daily-rouble-settings'),
        $default_donations_types_field_block = $('#donations-types'),
        $default_donation_type_field_block = $('#donation-type-default'),
        $campaign_template_field = $(':input[name="campaign_template"]');

    $daily_rouble_mode.change(function(){

        if($daily_rouble_mode.prop('checked')) {

            $default_donations_types_field_block.hide();
            $default_donation_type_field_block.hide();
            $daily_rouble_settings_block.show();

        } else {

            $default_donations_types_field_block.show();
            $default_donation_type_field_block.show();
            $daily_rouble_settings_block.hide();

        }

    }).change();
    // "Daily rouble mode" change - END

    // Campaign type change:
    $(':input[name="campaign_type"]').on('change.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        if( !$this.prop('checked') ) {
            return;
        }

        let $persistent_campaign_fields = $('.persistent-campaign-field'),
            $temp_campaign_fields = $('.temporary-campaign-fields');

        if($this.val() === 'persistent') {

            $persistent_campaign_fields.show();
            $temp_campaign_fields.hide();

        } else {

            $persistent_campaign_fields.hide();
            $temp_campaign_fields.show();

        }

    }).change();
    
    // Donation types field change:
    let $donations_types_fields = $(':input[name="donations_type[]"]');

    $donations_types_fields.on('change.leyka', function(e){

        e.preventDefault();

        let donations_types_selected = [];
        $donations_types_fields.filter(':checked').each(function(){
            donations_types_selected.push($(this).val());
        });

        if(donations_types_selected.length > 1 && !$daily_rouble_mode.prop('checked')) {
            $default_donation_type_field_block.show();
        } else {
            $default_donation_type_field_block.hide();
        }

    }).change();

    // Form templates screens demo: /** @todo This template demo code isn't used - it's a candidat for removing */
    // $('.form-template-screenshot').easyModal({
    //     top: 100,
    //     autoOpen: false
    // });
    //
    // $('.form-template-demo').on('click.leyka', function(e){
    //
    //     e.preventDefault();
    //
    //     let $this = $(this), // Demo icon
    //         $template_field = $this.siblings(':input[name="campaign_template"]'),
    //         selected_template_id = $template_field.val() === 'default' ?
    //             $template_field.data('default-template-id'): $template_field.val();
    //
    //     $this
    //         .find('.form-template-screenshot.'+selected_template_id)
    //         .css('display', 'block')
    //         .trigger('openModal');
    //
    // });
    // Form templates screens demo - END

    // Campaign cover upload field:
    $('.upload-photo', '.upload-attachment-field').on('click.leyka', function(e){

        e.preventDefault();

        let $upload_button = $(this),
            $field_wrapper = $upload_button.parents('.upload-photo-field'),
            $field_value = $field_wrapper.find(':input[name="'+$field_wrapper.data('field-name')+'"]'),
            $loading = $field_wrapper.find('.loading-indicator-wrap'),
            $img_wrapper = $upload_button.parents('.upload-photo-complex-field-wrapper').find('.set-page-img-control'),
            frame = wp.media({title: $field_wrapper.data('upload-title'), multiple: false});

        frame.on('select', function(){

            let attachment = frame.state().get('selection').first().toJSON();

            // disableForm();
            $loading.show();

            $field_value.val(attachment.id);

            let nonce_field_name = $field_wrapper.data('field-name').replace('_', '-') + '-nonce',
                ajax_params = {
                    action: $field_wrapper.data('ajax-action'),
                    field_name: $field_wrapper.data('field-name'),
                    attachment_id: attachment.id,
                    campaign_id: $field_wrapper.data('campaign-id'),
                    nonce: $field_wrapper.find(':input[name="'+nonce_field_name+'"]').val()
                };

            $.post(leyka.ajaxurl, ajax_params, null, 'json')
                .done(function(json){

                    if(typeof json.status !== 'undefined' && json.status === 'error') {
                        alert('Ошибка!');
                    } else {

                    	$img_wrapper.find('.img-value').html('<img src="'+json.img_url+'" alt="">');
                    	$img_wrapper.find('.reset-to-default').show();

                    }

                    // reloadPreviewFrame();

                })
                .fail(function(){
                    alert('Ошибка!');
                })
                .always(function(){

                    $loading.hide();
                    // enableForm();

                });

        });

        frame.open();

    });

    // Campaign cover type:
    $('#campaign-cover-type input[type="radio"]').change(function(){
    	if($(this).prop('checked')) {
    		if($(this).val() === 'color') {
    			$('#campaign-cover-bg-color').show();
    			$('#upload-campaign-cover-image').hide();
    		} else {
    			$('#campaign-cover-bg-color').hide();
    			$('#upload-campaign-cover-image').show();
    		}
    	}
    });
    $('#campaign-cover-type input[type="radio"]:checked').change();
    
    // Reset uploaded image to default:
    $('.set-page-img-control .reset-to-default').on('click.leyka', function(e){

        e.preventDefault();

        let $upload_button = $(this),
            $field_wrapper = $upload_button.parents('.set-page-img-control'),
            img_mission = $field_wrapper.data('mission'),
            $loading = $field_wrapper.find('.loading-indicator-wrap'),
        	nonce_field_name = 'reset-campaign-' + img_mission + '-nonce';
        
        let ajax_params = {
            action: 'leyka_reset_campaign_attachment',
            'img_mission': img_mission,
            campaign_id: $field_wrapper.data('campaign-id'),
            nonce: $field_wrapper.find(':input[name="'+nonce_field_name+'"]').val()
        };
        
        $field_wrapper.find('.reset-to-default').hide();
        $loading.show();

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    alert('Ошибка!');
                    $field_wrapper.find('.reset-to-default').show();

                } else {
                	$field_wrapper.find('.img-value').html(leyka.default_image_message);
                }

            })
            .fail(function(){
                alert('Ошибка!');
                $field_wrapper.find('.reset-to-default').show();
            })
            .always(function(){
                $loading.hide();
            });
    });

    // Recalculate total funded amount:
    $('#recalculate_total_funded').click(function(e){

        e.preventDefault();

        let $link = $(this).prop('disabled', 'disabled'),
            $indicator = $link.parent().find('#recalculate_total_funded_loader').show(),
            $message = $link.parent().find('#recalculate_message').hide(),
            $total_collected = $('#collected-amount-number');

        $.get(leyka.ajaxurl, {
            campaign_id: $link.data('campaign-id'),
            action: 'leyka_recalculate_total_funded_amount',
            nonce: $link.data('nonce')
        }, function(resp){

            $link.removeProp('disabled');
            $indicator.hide();

            if(parseFloat(resp) >= 0) {

                resp = parseFloat(resp);

                $total_collected.html(resp);

                // If recalculated sum is different than saved one, refresh the campaign edition page:
                if(parseFloat($total_collected.html()) !== resp) {
                    $('#publish').click();
                }

            } else {
                $message.html(resp).show();
            }

        });

    });
    // Recalculate total funded amount - END

    // Dynamic fields values length display in field description:
    $(':input[maxlength]').keyup(function(e){

        let $field = $(this),
            $description = $('[data-description-for="'+$field.prop('id')+'"]'),
            max_value_length = $field.prop('maxlength'),
            $current_value_length = $description.find('.leyka-field-current-value-length');

        if( !$description.length ) {
            return;
        }

        if($current_value_length.text() >= max_value_length && !leyka_is_special_key(e)) {
            e.preventDefault();
        } else {
            $current_value_length.text($field.val().length);
        }

    }).keyup();

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        let $data_table = $('.leyka-data-table.campaign-donations-table');
        $data_table.DataTable({
            ordering:  false, /** @todo Add ordering to the table & it's AJAX query */
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_campaign_donations';
                    data.campaign_id = $data_table.data('campaign-id');
                }
            },
            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                            +donation_id
                            +'</a>';
                    },
                },
                {
                    data: 'payment_type',
                    className: 'column-donation_type',
                    render: function(payment_type){
                        return '<i class="icon-payment-type icon-'+payment_type.id+' has-tooltip" title="'+payment_type.label+'"></i>';
                    },
                },
                {
                    data: 'donor',
                    className: 'column-donor',
                    render: function(donor, type, row_data){
                        return '<div class="donor-name">'
                            +(donor.id ? '<a href="'+leyka.admin_url+'?page=leyka_donor_info&donor='+donor.id+'">' : '')
                            +donor.name
                            +(donor.id ? '</a>' : '')
                        +'</div>'
                        +'<div class="donor-email">'+donor.email+'</div>';
                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data_amount, type, row_data){

                        let amount_html = data_amount.amount == data_amount.total ?
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label :
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label
                            +'<span class="amount-total"> / '
                                +data_amount.total_formatted+'&nbsp;'+data_amount.currency_label
                            +'</span>';

                        return '<span class="leyka-amount '+(data_amount.amount < 0.0 ? 'leyka-amount-negative' : '')+'">'
                                +'<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title="'+row_data.status.description+'"></i>'
                                +'<span class="leyka-amount-and-status">'
                                    +'<div class="leyka-amount-itself">'+amount_html+'</div>'
                                    +'<div class="leyka-donation-status-label label-'+row_data.status.id+'">'+row_data.status.label+'</div>'
                                +'</span>'
                            +'</span>';

                    }
                },
                {data: 'date', className: 'column-date',},
                {
                    data: 'gateway_pm',
                    className: 'column-gateway_pm data-gateway_pm',
                    render: function(gateway_pm, type, row_data){

                        return '<div class="leyka-gateway-name">'
                                +'<img src="'+gateway_pm.gateway_icon_url+'" alt="'+gateway_pm.gateway_label+'">'
                                +gateway_pm.gateway_label+','
                            +'</div>'
                            +'<div class="leyka-pm-name">'+gateway_pm.pm_label+'</div>';

                    }
                },
            ],
            rowCallback: function(row, data){ // After the data loaded from server, but before row is rendered in the table
                $(row)
                    .addClass('leyka-donations-table-row')
                    .addClass(data.payment_type.type_id === 'correction' ? 'leyka-donation-row-correction' : '')
                    .find('.has-tooltip').leyka_admin_tooltip();
            },

            lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
            language: {
                processing:     leyka_dt.processing,
                search:         leyka_dt.search,
                lengthMenu:     leyka_dt.lengthMenu,
                info:           leyka_dt.info,
                infoEmpty:      leyka_dt.infoEmpty,
                infoFiltered:   leyka_dt.infoFiltered,
                infoPostFix:    leyka_dt.infoPostFix,
                loadingRecords: leyka_dt.loadingRecords,
                zeroRecords:    leyka_dt.zeroRecords,
                emptyTable:     leyka_dt.emptyTable,
                paginate: {
                    first:    leyka_dt.paginate_first,
                    previous: leyka_dt.paginate_previous,
                    next:     leyka_dt.paginate_next,
                    last:     leyka_dt.paginate_last
                },
                aria: {
                    sortAscending:  leyka_dt.aria_sortAsc,
                    sortDescending: leyka_dt.aria_sortDesc
                }
            }
        });

    }

    // Campaign template change:
    $campaign_template_field.on('change.leyka', function(e){

        e.preventDefault();

        let $campaign_template_field = $(this),
            $css_editor_field = $('.css-editor-field'),
            template_selected = $campaign_template_field.val() === 'default' ?
                $campaign_template_field.data('default-template-id') : $campaign_template_field.val();

        if(template_selected === 'star' || template_selected === 'need-help') {

    		$('#campaign-css').show();

    		if(template_selected === 'need-help') { // Display/hide the "Daily rouble" form mode options

                $daily_rouble_mode_wrapper.show();

                if($daily_rouble_mode.prop('checked')) {

                    $default_donations_types_field_block.hide();
                    $default_donation_type_field_block.hide();

                }

            } else {

                $daily_rouble_mode_wrapper.hide();

                if($daily_rouble_mode.prop('checked')) {

                    $default_donations_types_field_block.show();
                    $default_donation_type_field_block.show();

                }

            }

    		// Set the template-specific default CSS editor value, if needed:
            if( !$css_editor_field.data('additional-css-used') ) {

                let original_value = $('.css-editor-'+$campaign_template_field.val()+'-original-value').val();

                $css_editor_field.val(original_value);
                $css_editor_field.data('code-editor-object').codemirror.getDoc().setValue(original_value);

            }

        } else {
        	$('#campaign-css').hide();
        }

    }).change();

    $('input[name="leyka_default_payments_amounts"]').on('change.leyka', function(e){
        if($(this).val() === '1') {
            $('#leyka_campaign_payments_amounts .leyka-options-section').hide();
        } else {
            $('#leyka_campaign_payments_amounts .leyka-options-section').show();
        }
    });

    // Multi-valued items fields:
    $('.multi-valued-items-field-wrapper').each(function(){

        let $items_wrapper = $(this),
            $add_item_button = $items_wrapper.find('.add-item');

        // Each muli-valued item should be added to the Campaign form only once.
        // So if it's already added, hide it from the variants for a new Campaign item:
        function leyka_refresh_campaign_new_items_variants() {

            let $new_item_select = $items_wrapper.find('.leyka-campaign-item-add-wrapper select'),
                added_items_ids = [];

            $items_wrapper.find('.multi-valued-item-box:not([id*="item-"])').each(function(){
                added_items_ids.push($(this).prop('id'));
            });
            $new_item_select.each(function(){

                let selected_id = $(this).val();

                if(selected_id !== '-' && selected_id !== '+') {
                    added_items_ids.push(selected_id);
                }

            });

            $new_item_select.find('option').show(); // First, show all options (new items variants)...

            $(added_items_ids).each(function(){
                // ...Then hide options for fields that are already added to Campaign
                $new_item_select.find('option[value="'+this+'"]').hide();
            });

        }

        $add_item_button.on('click.leyka', function(e){

            e.preventDefault();

            if($add_item_button.hasClass('inactive')) {
                return;
            }

            leyka_refresh_campaign_new_items_variants();

            let $new_item_box_wrapper = $items_wrapper.find('.multi-valued-item-box:visible:last'),
                $new_item_subfields_wrapper = $new_item_box_wrapper.find('.leyka-campaign-new-item-subfields'),
                $add_campaign_item_select = $new_item_box_wrapper.find('.leyka-campaign-item-add-wrapper select');

            if($add_campaign_item_select.val() === '+') {
                $new_item_subfields_wrapper.show();
            } else {
                $new_item_subfields_wrapper.hide();
            }

        });

        $items_wrapper.find('.leyka-main-multi-items').on('click.leyka', '.delete-item', function(){
            leyka_refresh_campaign_new_items_variants();
        });

        $items_wrapper.on('change.leyka', '.leyka-campaign-item-add-wrapper select', function(){

            let $add_campaign_item_select = $(this),
                $new_item_box_wrapper = $add_campaign_item_select
                    .parents('.box-content')
                    .find('.leyka-campaign-new-item-subfields');

            if($add_campaign_item_select.val() === '+') {
                $new_item_box_wrapper.show();
            } else {
                $new_item_box_wrapper.hide();
            }

            leyka_refresh_campaign_new_items_variants();

        }).find('.leyka-campaign-item-add-wrapper select:visible').each(function(){

            // For the case when there are no fields in the Library, display the new field subfields right from the start:

            let $this = $(this);

            if($this.val() === '+') {
                $this.trigger('change.leyka');
            }

        });

    });
    // Multi-valued items fields - END

    /* Support packages Extension - available campaign existence check: */
    /** @todo Move all Support packages code here to the Extension */
    if(typeof($().dialog) === 'undefined') {
        return;
    }

    let $campaign_needed_field = $('input#leyka-campaign-needed-for-support-packages'),
        $modal = $('#leyka-campaign-needed-modal-content'),
        $form = $('form#post');

    if( !$modal.length ) {
        return;
    }

    function leyka_support_packages_campaign_deactivation_dialog($modal, retrigger_action){

        let $modal_fields = $modal.find('#leyka-support-packages-behavior-fields');

        if($modal.data('leyka-dialog-initialized')) {
            $modal.dialog('open');
        } else {

            $modal.dialog({
                dialogClass: 'leyka-dialog',
                modal: true,
                draggable: false,
                width: '600px',
                autoOpen: true,
                closeOnEscape: true,
                resizable: false,
                buttons: [{
                    'text': $modal.data('close-button-text'),
                    'class': 'button-secondary',
                    'click': function(){
                        $modal.dialog('close');
                    }
                }, {
                    'text': $modal.data('submit-button-text'),
                    'class': 'button-primary',
                    'click': function(){

                        let $extension_behavior = $modal.find('[name="support-packages-campaign-changed"]:checked'),
                            $loading = $modal.find('#leyka-loading'),
                            $message = $modal.find('#leyka-message');

                        if( !$extension_behavior.length ) {

                            $modal_fields.show();
                            $message
                                .removeClass('success-message')
                                .addClass('error-message')
                                .html($message.data('validation-error-message'))
                                .show();

                            return;

                        }

                        $message.hide();
                        $loading.show();
                        $modal_fields.hide();

                        $.post(leyka.ajaxurl, {
                            action: 'leyka_support_packages_set_no_campaign_behavior',
                            behavior: $extension_behavior.val(),
                            campaign_id: $modal.find('[name="leyka_support_packages_campaign"]').val(),
                            nonce: $modal.data('nonce'),
                        }, null, 'json')
                            .done(function(json){

                                if(typeof json.status !== 'undefined' && json.status !== 0) { // Server-side error

                                    $modal_fields.show();
                                    $message
                                        .removeClass('success-message')
                                        .addClass('error-message')
                                        .html($message.data('error-message'))
                                        .show();

                                } else {

                                    $modal.data('leyka-support-packages-campaign-checked', true);
                                    $message
                                        .removeClass('error-message')
                                        .addClass('success-message')
                                        .html($message.data('success-message'))
                                        .show();

                                    // $tager.trigger(e.type) doesn't work for the "delete post" link, so use passed function:
                                    if(typeof retrigger_action == 'function') {
                                        retrigger_action();
                                    }

                                }

                            })
                            .fail(function(){ // Ajax request failure

                                $modal_fields.show();
                                $message
                                    .removeClass('success-message')
                                    .addClass('error-message')
                                    .html($message.data('error-message'))
                                    .show();

                            })
                            .always(function(){
                                $loading.hide();
                            });

                    }
                }],
                // Make Dialog position fixed & fix the z-index issue:
                create: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                },
                resizeStart: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                },
                resizeStop: function(e) {
                    $(e.target).parent().css({'position': 'fixed', 'z-index': 1000});
                }
            });

            $modal.data('leyka-dialog-initialized', 1);

        }

    }

    $('.submitdelete.deletion').on('click.leyka', function(e){

        let $this = $(this),
            campaign_original_status = $form.find('#original_post_status').val(),
            campaign_updated_is_finished = $form.find('[name="is_finished"]').prop('checked');

        if(campaign_original_status !== 'publish' || campaign_updated_is_finished) {
            return;
        }

        // The Support packages check passed - submit the campaign changes normally:
        if($modal.data('leyka-support-packages-campaign-checked')) {
            return;
        }

        e.preventDefault();

        leyka_support_packages_campaign_deactivation_dialog($modal, function(){
            window.location.href = $this.attr('href');
        });

    });
    $form.on('submit.leyka', function(e){

        /** @todo Get $campaign_needed_field value via ajax, mb */
        if( !$campaign_needed_field.length || !parseInt($campaign_needed_field.val()) ) {
            return;
        }

        let $this = $(this),
            campaign_updated_status = $form.find('[name="post_status"]').val(),
            campaign_updated_is_finished = $form.find('[name="is_finished"]').prop('checked');

        // The campaign is published, so check won't be needed:
        if(campaign_updated_status === 'publish' && !campaign_updated_is_finished) {
            return;
        }

        // The Support packages check passed - submit the campaign changes normally:
        if($modal.data('leyka-support-packages-campaign-checked')) {
            return;
        }

        e.preventDefault();

        leyka_support_packages_campaign_deactivation_dialog($modal, function(){
            $this.trigger(e.type);
        });

    });

    $modal.on('change.leyka', '[name="support-packages-campaign-changed"]', function(){

        let $this = $(this),
            $new_campaign = $modal.find('.new-campaign');

        if($this.val() === 'another-campaign') {
            $new_campaign.show();
        } else {
            $new_campaign.hide();
        }

    });

});
/* Support packages Extension - available campaign existence check - END */
// "How to setup cron" modal:
jQuery(document).ready(function($){

    if(typeof($().dialog) === 'undefined') {
        return;
    }

    $('.leyka-adb-modal').dialog({
        dialogClass: 'wp-dialog leyka-adb-modal',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: 'center top+25%',
            at: 'center top+25%',
            of: window
        },
        open: function(){
            var $modal = $(this);
            $('.ui-widget-overlay').bind('click', function(){
                $modal.dialog('close');
            });
        },
        create: function () {
            $('.ui-dialog-titlebar-close').addClass('ui-button');

            var $modal = $(this);
            $modal.find('.button-dialog-close').bind('click', function(){
                $modal.dialog('close');
            });
        }

    });

    $('.cron-setup-howto').on('click.leyka', function(e){
        e.preventDefault();
        $('#how-to-setup-cron').dialog('open');
    })

});

// init "stats invite"
jQuery(document).ready(function($){

    $('.send-plugin-stats-invite .send-plugin-usage-stats-y').on('click.leyka', function(e){

        e.preventDefault();

        let $button = $(this),
            $field_wrapper = $button.parents('.invite-link'),
            $loading = $field_wrapper.find('.loader-wrap');

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_usage_stats_y',
            nonce: $field_wrapper.find(':input[name="usage_stats_y"]').val()
        };
        
        $loading.show();
        // $loading.css('display', 'block');
        // $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {

                        $loading.closest('.loading-indicator-wrap').find('.ok-icon').show();
                        // var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $loading.remove();
                        // $indicatorWrap.find('.ok-icon').show();
                        setTimeout(function(){
                            $field_wrapper.closest('.send-plugin-stats-invite').fadeOut('slow');
                        }, 1000);

                    } else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        } else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.hide();
            });
    });

});

// banner
jQuery(document).ready(function($){
    $('.banner-wrapper .close').on('click.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        $this.closest('.banner-wrapper').remove();

        $.post(
            leyka.ajaxurl, {
                action: 'leyka_close_dashboard_banner',
                banner_id: $this.parents('.banner-inner').data('banner-id'),
                /** @todo Add nonce */
            },
            null, 'json'
        );

    });
});

/** Admin JS - Donation adding/editing pages **/
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donation-info-page' ) {
        return;
    }

    // Validate add/edit donation form:
    $('form#post').submit(function(e){

        let $form = $(this),
            is_valid = true,
            $field = $('#campaign-id');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#campaign_id-error').html(leyka.campaign_required).show();

        } else {
            $form.find('#campaign_id-error').html('').hide();
        }

        $field = $('#donor-email');
        if($field.val() && !is_email($field.val())) {

            is_valid = false;
            $form.find('#donor_email-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#donor_email-error').html('').hide();
        }

        $field = $('#donation-amount');
        let amount_clear = parseFloat($field.val().replace(',', '.'));
        if( !$field.val() || amount_clear === 0.0 || isNaN(amount_clear) ) {

            is_valid = false;
            $form.find('#donation_amount-error').html(leyka.amount_incorrect_msg).show();

        } else {
            $form.find('#donation_amount-error').html('').hide();
        }

        $field = $('#donation-pm');
        if($field.val() === 'custom') {
            $field = $('#custom-payment-info');
        }
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#donation_pm-error').html(leyka.donation_source_required).show();
        } else {
            $form.find('#donation_pm-error').html('').hide();
        }

        if( !is_valid ) {
            e.preventDefault();
        }

    });

    /** New donation page: */
    $('#donation-pm').change(function(){

        let $this = $(this);

        if($this.val() === 'custom') {
            $('#custom-payment-info').show();
        } else {

            $('#custom-payment-info').hide();

            var gateway_id = $this.val().split('-')[0];

            $('.gateway-fields').hide();
            $('#'+gateway_id+'-fields').show();
        }
    }).keyup(function(e){
        $(this).trigger('change');
    });

    /** Edit donation page: */
    $('#donation-status-log-toggle').click(function(e){

        e.preventDefault();

        $('#donation-status-log').slideToggle(100);

    });

    $('input[name*=leyka_pm_available]').change(function(){

        let $this = $(this),
            pm = $this.val();

        pm = pm.split('-')[1];
        if($this.attr('checked')) {
            $('[id*=leyka_'+pm+']').slideDown(50);
        } else {
            $('[id*=leyka_'+pm+']').slideUp(50);
        }

    }).each(function(){
        $(this).change();
    });

    $('#campaign-select-trigger').click(function(e){

        e.preventDefault();

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title.data('campaign-payment-title-previous', $campaign_payment_title.text());

        $(this).slideUp(100);
        $('#campaign-select-fields').slideDown(100);
        $('#campaign-field').removeAttr('disabled');

    });

    $('#cancel-campaign-select').click(function(e){

        e.preventDefault();

        $('#campaign-select-fields').slideUp(100);
        $('#campaign-field').attr('disabled', 'disabled');
        $('#campaign-select-trigger').slideDown(100);

        let $campaign_payment_title = $('#campaign-payment-title');
        $campaign_payment_title
            .text($campaign_payment_title.data('campaign-payment-title-previous'))
            .removeData('campaign-payment-title-previous');

    });

    $('.recurrent-cancel').click(function(e){

        e.preventDefault();

        $('#ajax-processing').fadeIn(100);

        let $this = $(this);
        $this.fadeOut(100);

        // Do a recurrent donations cancelling procedure:
        $.post(leyka.ajaxurl, {
            action: 'leyka_cancel_recurrents',
            nonce: $this.data('nonce'),
            donation_id: $this.data('donation-id')
        }, function(response){
            $('#ajax-processing').fadeOut(100);
            response = $.parseJSON(response);

            if(response.status == 0) {

                $('#ajax-response').html('<div class="error-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeIn(100);

            } else if(response.status == 1) {

                $('#ajax-response').html('<div class="success-message">'+response.message+'</div>').fadeIn(100);
                $('#recurrent-cancel-retry').fadeOut(100);

            }
        });

    });

    $('#recurrent-cancel-retry').click(function(e){

        e.preventDefault();

        $('.recurrent-cancel').click();

    });

    // Recurring subscriptions Donations list data table:
    let $data_table = $('.leyka-data-table.recurring-subscription-donations-table');

    if($data_table.length && typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        $data_table.DataTable({

            ordering:  false, /** @todo Add ordering to the table & it's AJAX query */
            searching: false,
            processing: true,
            serverSide: true,

            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_recurring_subscription_donations';
                    data.recurring_subscription_id = $data_table.data('init-recurring-donation-id');
                }
            },

            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                            +donation_id
                            +'</a>';
                    },
                },
                {
                    data: 'donor',
                    className: 'column-donor',
                    render: function(donor, type, row_data){
                        return '<div class="donor-name">'
                            +(donor.id ? '<a href="'+leyka.admin_url+'?page=leyka_donor_info&donor='+donor.id+'">' : '')
                            +donor.name
                            +(donor.id ? '</a>' : '')
                            +'</div>'
                            +'<div class="donor-email">'+donor.email+'</div>';
                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data_amount, type, row_data){

                        let amount_html = data_amount.amount === data_amount.total ?
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label :
                            data_amount.formatted+'&nbsp;'+data_amount.currency_label
                            +'<span class="amount-total"> / '
                            +data_amount.total_formatted+'&nbsp;'+data_amount.currency_label
                            +'</span>';

                        return '<span class="leyka-amount '+(data_amount.amount < 0.0 ? 'leyka-amount-negative' : '')+'">'
                            +'<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title="'+row_data.status.description+'"></i>'
                            +'<span class="leyka-amount-and-status">'
                            +'<div class="leyka-amount-itself">'+amount_html+'</div>'
                            +'<div class="leyka-donation-status-label label-'+row_data.status.id+'">'+row_data.status.label+'</div>'
                            +'</span>'
                            +'</span>';

                    }
                },
                {data: 'date', className: 'column-date',},
                {
                    data: 'gateway_pm',
                    className: 'column-gateway_pm data-gateway_pm',
                    render: function(gateway_pm, type, row_data){

                        return '<div class="leyka-gateway-name">'
                            +'<img src="'+gateway_pm.gateway_icon_url+'" alt="'+gateway_pm.gateway_label+'">'
                            +gateway_pm.gateway_label+','
                            +'</div>'
                            +'<div class="leyka-pm-name">'+gateway_pm.pm_label+'</div>';

                    }
                },
            ],

            rowCallback: function(row, data){ // After the data loaded from server, but before row is rendered in the table
                $(row)
                    .addClass('leyka-donations-table-row')
                    .addClass('leyka-recurring-subscription-donations')
                    .find('.has-tooltip').leyka_admin_tooltip();
            },

            lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],

            language: {
                processing:     leyka_dt.processing,
                search:         leyka_dt.search,
                lengthMenu:     leyka_dt.lengthMenu,
                info:           leyka_dt.info,
                infoEmpty:      leyka_dt.infoEmpty,
                infoFiltered:   leyka_dt.infoFiltered,
                infoPostFix:    leyka_dt.infoPostFix,
                loadingRecords: leyka_dt.loadingRecords,
                zeroRecords:    leyka_dt.zeroRecords,
                emptyTable:     leyka_dt.emptyTable,
                paginate: {
                    first:    leyka_dt.paginate_first,
                    previous: leyka_dt.paginate_previous,
                    next:     leyka_dt.paginate_next,
                    last:     leyka_dt.paginate_last
                },
                aria: {
                    sortAscending:  leyka_dt.aria_sortAsc,
                    sortDescending: leyka_dt.aria_sortDesc
                }
            }

        });

    }

});
/** Donations admin list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donations-list-page' ) {
        return;
    }

    let $admin_list_filters = $page_wrapper.find('form.donations-list-controls'),
        $filters_warning_message = $admin_list_filters.find('#leyka-filter-warning');

    $admin_list_filters.find('[name="donations-list-export"]').click(function(e){

        // Prevent export if no filters were chosed:
        let filters_values = $(this).parents('form').serializeArray(),
            filters_set = false;

        for(let i = 0; i < filters_values.length; i++) {

            if(filters_values[i].name !== 'page' && filters_values[i].value && filters_values[i].value !== '-') {
                filters_set = true;
                break;
            }

        }

        if(filters_set) {
            $filters_warning_message.html('').hide();
        } else {

            e.preventDefault();
            $filters_warning_message.html(leyka.no_filters_while_exporting_warning_message).show();

        }

    });

});
/** Donor's info page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donor-info-page' ) {
        return;
    }

    // Donations list data table:
    if(typeof $().DataTable !== 'undefined' && typeof leyka_dt !== 'undefined') {

        let $data_table = $('.leyka-data-table');
        $data_table.DataTable({
            'processing': true,
            'serverSide': true,
            ajax: {
                url: leyka.ajaxurl,
                type: 'POST',
                data: function(data){
                    data.action = 'leyka_get_donor_donations';
                    data.donor_id = $data_table.data('donor-id');
                }
            },
            columns: [
                {
                    data: 'donation_id',
                    className: 'column-id column-donation_id',
                    render: function(donation_id){
                        return '<a href="'+leyka.admin_url+'admin.php?page=leyka_donation_info&donation='+donation_id+'" target="_blank">'
                                +donation_id
                            +'</a>';
                    },
                },
                {
                    data: 'payment_type',
                    className: 'column-donation_type',
                    render: function(data){
                        return '<i class="icon-payment-type icon-'+data.id+' has-tooltip" title="'+data.label+'"></i>';
                    },
                },
                {data: 'date', className: 'column-date',},
                {
                    data: 'campaign',
                    className: 'column-campaign data-campaign leyka-donation-info-wrapper',
                    render: function(data, type, row_data){

                        return '<i class="icon-leyka-donation-status icon-'+row_data.status.id+' has-tooltip leyka-tooltip-align-left" title=""></i>'
                            +'<span class="leyka-tooltip-content">'
                                +'<strong>'+row_data.status.label+':</strong> '+lcfirst(row_data.status.description)
                            +'</span>'
                            +'<div class="leyka-donation-additional-data">'
                                +'<div class="first-sub-row">'+row_data.campaign_title+'</div>'
                                +'<div class="second-sub-row">'
                                    +(row_data.gateway_pm.gateway_icon_url ? '<img src="'+row_data.gateway_pm.gateway_icon_url+'" alt="'+row_data.gateway_pm.gateway_label+'">' : '')
                                    +row_data.gateway_pm.gateway_label+', '+row_data.gateway_pm.pm_label
                                +'</div>'
                            +'</div>';

                    }
                },
                {
                    data: 'amount',
                    className: 'column-amount data-amount',
                    render: function(data, type, row_data){
                        return data.amount_formatted+'&nbsp;'+data.currency_label
                            +'<span class="amount-total"> / '+data.amount_total_formatted+'&nbsp;'+data.currency_label+'</span>';
                    }
                },
            ],
            rowCallback: function(row, data){ // After the data loaded from server, but before row is rendered in the table
                $(row)
                    .addClass('leyka-donations-table-row')
                    .addClass(data.payment_type.type_id === 'correction' ? 'leyka-donation-row-correction' : '')
                    .find('.has-tooltip').leyka_admin_tooltip();
            },

            pageLength: 10,
            lengthChange: false,
            ordering:  false,
            searching: false,
            language: {
                processing:     leyka_dt.processing,
                search:         leyka_dt.search,
                lengthMenu:     leyka_dt.lengthMenu,
                info:           leyka_dt.info,
                infoEmpty:      leyka_dt.infoEmpty,
                infoFiltered:   leyka_dt.infoFiltered,
                infoPostFix:    leyka_dt.infoPostFix,
                loadingRecords: leyka_dt.loadingRecords,
                zeroRecords:    leyka_dt.zeroRecords,
                emptyTable:     leyka_dt.emptyTable,
                paginate: {
                    first:    leyka_dt.paginate_first,
                    previous: leyka_dt.paginate_previous,
                    next:     leyka_dt.paginate_next,
                    last:     leyka_dt.paginate_last
                },
                aria: {
                    sortAscending:  leyka_dt.aria_sortAsc,
                    sortDescending: leyka_dt.aria_sortDesc
                }
            }
        });

    }
    // Donations list data table - END

});

// Donor info
jQuery(document).ready(function($){

    $('.donor-add-description-link').click(function(e){
        e.preventDefault();
        $('.add-donor-description-form').toggle();
    });

    $('.add-donor-description-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form.closest('.donor-description'),
            $field = $form.find('textarea[name="donor-description"]'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$field.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_save_donor_description',
            nonce: $('#leyka_save_editable_str_nonce').val(),
            text: $field.val(),
            donor: $('#leyka_donor_id').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        setTimeout(function(){
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                            $fieldWrapper.find('.description-text').text(json.saved_text);
                            $fieldWrapper.find('.leyka-editable-str-field').text(json.saved_text);
                            $('.donor-add-description-wrapper').remove();
                            $('.donor-view-description-wrapper').show();
                        }, 1000);
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });    
});

// comments
function leykaSetCommentsListVisibilityState() {
    let $ = jQuery;

    if($('#leyka_donor_admin_comments table tbody tr').length > 1) {
        $('table.donor-comments').show();
        $('.no-comments').hide();
    }
    else {
        $('table.donor-comments').hide();
        $('.no-comments').show();
    }
}

jQuery(document).ready(function($){
    $('.add-donor-comment-link').click(function(e){
        e.preventDefault();

        var $form = $(this).parent().find('.new-donor-comment-form');
        $form.toggle();
        $form.find('.ok-icon').css('display', 'none');
    });

    $('#leyka_donor_admin_comments table').on('click', '.comment-icon-delete', function(e){
        e.preventDefault();

        if(!confirm(leyka.confirm_delete_comment)) {
            return;
        }

        let $button = $(this),
            $row = $(this).closest('tr'),
            $cell = $(this).closest('td'),
            $metabox = $(this).closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $cell.find('.loader-wrap'),
            comment_id = $button.data('comment-id'),
            donor_id = $('#leyka_donor_id').val();

        $button.hide();

        let ajax_params = {
            action: 'leyka_delete_donor_comment',
            nonce: $('input[name="leyka_delete_donor_comment_nonce"]').val(),
            comment_id: comment_id,
            donor: donor_id
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        $row.remove();
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                        }
                        else {
                            alert(leyka.error_message);
                        }
                        $button.show();
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.show();
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
            });
    });

    $('.new-donor-comment-form').submit(function(e){
        e.preventDefault();

        let $form = $(this),
            $button = $(this).find('input[type="submit"]'),
            $fieldWrapper = $form,
            $commentField = $form.find('input[name="donor-comment"]'),
            $metabox = $form.closest('#leyka_donor_admin_comments'),
            $table = $metabox.find('.donor-info-table'),
            $loading = $fieldWrapper.find('.loader-wrap');

        if(!$commentField.val()) {
            return;
        }

        $button.prop('disabled', true);
        
        let ajax_params = {
            action: 'leyka_add_donor_comment',
            nonce: $('#leyka_add_donor_comment_nonce').val(),
            comment: $commentField.val(),
            donor: $('#leyka_donor_id').val()
        };
        
        $loading.css('display', 'block');
        $loading.find('.leyka-loader').css('display', 'block');

        $.post(leyka.ajaxurl, ajax_params, null, 'json')
            .done(function(json){
                if(typeof json.status !== 'undefined') {
                    if(json.status === 'ok') {
                        var $indicatorWrap = $loading.closest('.loading-indicator-wrap');
                        $indicatorWrap.find('.ok-icon').css('display', 'block');
                        $commentField.val("");
                        setTimeout(function(){
                            $indicatorWrap.find('.ok-icon').fadeOut("slow");
                        }, 1000);

                        var $trTemplate = $table.find('tbody tr:first'),
                            $tr = $trTemplate.clone(),
                            comment_html = json.comment_html;

                        $tr = $(comment_html);
                        $table.append($tr);

                        leykaBindEditableStrEvents($tr);
                        leykaSetCommentsListVisibilityState();
                    }
                    else {
                        if(json.message) {
                            alert(json.message);
                            $button.prop('disabled', false);
                        }
                        else {
                            alert(leyka.error_message);
                            $button.prop('disabled', false);
                        }
                    }
                    return;
                }
            })
            .fail(function(){
                alert(leyka.error_message);
                $button.prop('disabled', false);
            })
            .always(function(){
                $loading.css('display', 'none');
                $loading.find('.leyka-loader').css('display', 'none');
                $button.prop('disabled', false);
            });
    });
});


// editable string
function leykaBindEditableStrEvents($container) {
    let $ = jQuery;

    $container.find('.leyka-editable-str-field').on('blur', function(e){
        if($(this).prop('readonly')) {
            return;
        }

        leykaSaveEditableStrAndCloseForm($(this));
    });

    $container.find('input.leyka-editable-str-field').keypress(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        if ( e.key === "Enter" ) {
            e.preventDefault();
            leykaSaveEditableStrAndCloseForm($(this));
        }    
    });

    $container.find('.leyka-editable-str-field').keydown(function( e ) {
        if($(this).prop('readonly')) {
            return;
        }

        var $strField = $(this),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        if ( e.key === "Escape" || e.key === "Esc" ) {
            e.preventDefault();
            $strField.val($strResult.text());
            leykaSaveEditableStrAndCloseForm($strField);
        }    
    });

    $container.find('.leyka-editable-str-btn').click(function(e){
        e.preventDefault();

        var $btn = $(this),
            $strField = $('.leyka-editable-str-field#' + $btn.attr('str-field')),
            $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

        $strResult.hide();
        $strField.show().focus();
        $btn.hide();
        $strField.parent().find('.loading-indicator-wrap').show();
    });
}

function leykaSaveEditableStrAndCloseForm($strField) {
    let $ = jQuery;

    var $btn = $('.leyka-editable-str-btn#' + $strField.attr('str-btn')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result'));

    var endEditCallback = function(){
        $strField.hide();
        $strResult.show();
        $btn.show();
        $strField.parent().find('.loading-indicator-wrap').hide();
        $strField.prop('readonly', false);
    };

    if($strField.val() != $strResult.text()) {
        leykaSaveEditableStr($strField, endEditCallback);
    }
    else {
        endEditCallback();
    }
}

function leykaSaveEditableStr($strField, saveCallback) {
    let $ = jQuery;

    var $button = $('.leyka-editable-str-link#' + $strField.attr('str-edit-link')),
        $strResult = $('.leyka-editable-str-result#' + $strField.attr('str-result')),
        $loading = $strField.parent().find('.loader-wrap'),
        $indicatorWrap = $loading.closest('.loading-indicator-wrap');

    let ajax_params = {
        action: $strField.attr('save-action'),
        nonce: $('#leyka_save_editable_str_nonce').val(),
        text: $strField.val(),
        text_item_id: $strField.attr('text-item-id'),
        donor: $('#leyka_donor_id').val()
    };
    
    $loading.css('display', 'block');
    $loading.find('.leyka-loader').css('display', 'block');
    $strField.prop('readonly', true);

    $.post(leyka.ajaxurl, ajax_params, null, 'json')
        .done(function(json){
            if(typeof json.status !== 'undefined') {
                if(json.status === 'ok') {
                    $indicatorWrap.find('.ok-icon').css('display', 'block');

                    if(json.saved_text) {
                        $strResult.text(json.saved_text);
                    }
                    else {
                        $strResult.text($strField.val());
                    }

                    setTimeout(function(){
                        $indicatorWrap.find('.ok-icon').fadeOut("slow", saveCallback);
                    }, 1000);
                }
                else {
                    if(json.message) {
                        alert(json.message);
                    }
                    else {
                        alert(leyka.error_message);
                    }
                    $strField.prop('readonly', false);
                }
                return;
            }
        })
        .fail(function(){
            alert(leyka.error_message);
            $strField.prop('readonly', false);
        })
        .always(function(){
            $loading.css('display', 'none');
            $loading.find('.leyka-loader').css('display', 'none');
        });

}

jQuery(document).ready(function($){
    leykaBindEditableStrEvents($(document));
});

// tags
jQuery(document).ready(function($){
    if(!$('#leyka_donor_tags').length) {
        return;
    }

    window.tagBox && window.tagBox.init();

    var saveDonorTagsTimeoutId = null;

    $("body").on('DOMSubtreeModified', ".tagchecklist", function() {

        if(saveDonorTagsTimeoutId) {
            clearTimeout(saveDonorTagsTimeoutId);
        }

        saveDonorTagsTimeoutId = setTimeout(function() {

            let ajax_params = {
                action: 'leyka_save_donor_tags',
                nonce: $('#leyka_save_donor_tags_nonce').val(),
                tags: $('textarea[name="tax_input[donors_tag]"]').val(),
                donor: $('#leyka_donor_id').val()
            };
            
            $.post(leyka.ajaxurl, ajax_params, null, 'json')
                .done(function(json){
                    if(typeof json.status !== 'undefined') {
                        if(json.status === 'ok') {
                        }
                        else {
                            if(json.message) {
                                alert(json.message);
                            }
                            else {
                                alert(leyka.error_message);
                            }
                        }
                        return;
                    }
                })
                .fail(function(){
                    alert(leyka.error_message);
                })

            saveDonorTagsTimeoutId = null;
        }, 500);

    });

});
/** Donors list page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'donors-list-page' ) {
        return;
    }

	let selected_values = [];

	// Tags autocomplete:
	$('.leyka-donors-tags-select').each(function(){

	    let $select_field = $(this);

        selected_values = [];
	    $select_field.find('option').each(function(){
            selected_values.push({item: {label: $.trim($(this).text()), value: $(this).val()}});
        });

        $select_field.siblings('input.leyka-donors-tags-selector').autocomplete({
            source: leyka.ajaxurl+'?action=leyka_donors_tags_autocomplete',
            multiselect: true,
            search_on_focus: true,
            minLength: 0,
            pre_selected_values: selected_values,
            leyka_select_callback: function(selected_items){

                $select_field.html('');
                for(let val in selected_items) {
                    $select_field.append( $('<option></option>').val(val).prop('selected', true) );
                }

            }
        });

    });

	// Donors inline edit:
    let $donors_table_body = $('#the-list'),
        $inline_edit_fields = $('#leyka-donors-inline-edit-fields'),
        $form = $donors_table_body.parents('form'),
        columns_number = $inline_edit_fields.data('colspan'),
        $inline_edit_row = $donors_table_body.find('#leyka-inline-edit-row');

    $form.on('submit.leyka', function(e){

        if(
            $form.find(':input[name="action"]').val() === 'bulk-edit'
            || $form.find(':input[name="action2"]').val() === 'bulk-edit'
        ) {

            e.preventDefault();

            if($form.find('input[name="bulk[]"]:checked').length) { // Display the bulk edit fields only if some donors checked

                if( !$inline_edit_row.length ) {

                    $donors_table_body
                        .prepend($('<tr id="leyka-inline-edit-row"><td colspan="'+columns_number+'"></td></tr>'))
                        .find('#leyka-inline-edit-row td')
                        .append($inline_edit_fields.show());

                    $inline_edit_row = $donors_table_body.find('#leyka-inline-edit-row');

                }

                $inline_edit_row.show();
                $form.find('#bulk-action-selector-top').get(0).scrollIntoView(); // Scroll the bulk edit form into view

            }

        }

    });

    $inline_edit_fields.on('click.leyka', '.cancel', function(e){ // Bulk edit cancel

        e.preventDefault();

        $inline_edit_row.hide();

    }).on('click.leyka', '#bulk-edit', function(e){

        e.preventDefault();

        let $submit_button = $(this).prop('disabled', 'disabled'),
            params = $inline_edit_row.find(':input').serializeArray(),
            $message = $inline_edit_fields.find('.result').html('').hide(); // .error-message

        params.push(
            {name: 'action', value: 'leyka_bulk_edit_donors'},
            {name: 'nonce', value: $inline_edit_fields.data('bulk-edit-nonce'),}
        );

        $donors_table_body.find('input[name="bulk[]"]:checked').each(function(){
            params.push({name: 'donors[]', value: $(this).val()});
        });

        $.post(leyka.ajaxurl, params, null, 'json')
            .done(function(json) {

                if(json.status === 'ok') {
                    setTimeout(function(){ window.location.reload(); }, 1000);
                } else if(json.status === 'error' && json.message) { // Show error message returned
                    $message.html(json.message).show();
                } else { // Show the generic error message
                    $message.html($message.data('default-error-text')).show();
                }

            }).fail(function(){ // Show the generic error message
            $message.html($message.data('default-error-text')).show();
        }).always(function(){
            // $loading.remove();
            $submit_button.prop('disabled', false);
        });

    })

});
/** Donors list page - END */
/** Common JS for Extension settings (Extensiton edit page). */

jQuery(document).ready(function($){

    let $admin_page_wrapper = $('.leyka-admin');
    if( !$admin_page_wrapper.length || !$admin_page_wrapper.hasClass('extension-settings') ) {
        return;
    }

});
/** Gateways settings board */

// Payment settings page:
jQuery(document).ready(function($){

    if( !$('#payment-settings-area-new.stage-payment').length ) {
        return;
    }

    let $pm_available_list = $('.pm-available'),
        $pm_order = $('#pm-order-settings'),
        $pm_update_status = $('.pm-update-status'),
        $ok_message = $pm_update_status.find('.ok-message'),
        $error_message = $pm_update_status.find('.error-message'),
        $ajax_loading = $pm_update_status.find('.leyka-loader'),
        $pm_list_empty_block = $('.pm-list-empty');

    $pm_update_status.find('.result').hide();

    function leyka_update_pm_list($pm_order) {

        let params = {
            action: 'leyka_update_pm_list',
            pm_order: $pm_order.data('pm-order'),
            pm_labels: {},
            nonce: $pm_order.data('nonce')
        };

        $pm_order.find('input.pm-label-field.submitable').each(function(){
            params.pm_labels[$(this).prop('name')] = $(this).val();
        });

        $ok_message.hide();
        $error_message.hide();
        $ajax_loading.show();

        $.post(leyka.ajaxurl, params, null, 'json')
            .done(function(json){

                if(typeof json.status !== 'undefined' && json.status === 'error') {

                    $ok_message.hide();
                    $error_message.html(typeof json.message === 'undefined' ? leyka.common_error_message : json.message).show();

                    return;

                }

                $ok_message.show();
                $error_message.html('').hide();

            })
            .fail(function(){
                $error_message.html(leyka.common_error_message).show();
            })
            .always(function(){
                $ajax_loading.hide();
            });

    }

    // PM reordering:
    $pm_order
        .sortable({placeholder: '', items: '> li:visible'})
        .on('sortupdate', function(event){

            $pm_order.data('pm-order',
                $(this).sortable('serialize', {key: 'pm_order[]', attribute: 'data-pm-id', expression: /(.+)/})
            );

            leyka_update_pm_list($pm_order);

            if($pm_order.find('.pm-order:visible').length) {
                $pm_list_empty_block.hide();
            } else {
                $pm_list_empty_block.show();
            }

        }).on('click', '.pm-deactivate', function(e){ // PM deactivation

            e.preventDefault();

            let $pm_sortable_item = $(this).parents('li:first');

            $pm_sortable_item.hide(); // Remove a sortable block from the PM order settings
            $pm_available_list.filter('#'+$pm_sortable_item.data('pm-id')).removeAttr('checked');

            $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

        }).on('click', '.pm-change-label', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first');

            $wrapper.find('.pm-control').hide();
            $wrapper.find('.pm-label').hide();
            $wrapper.find('.pm-label-fields').show();

        }).on('click', '.new-pm-label-ok,.new-pm-label-cancel', function(e){

            e.preventDefault();

            let $this = $(this),
                $wrapper = $this.parents('li:first'),
                $pm_label_wrapper = $wrapper.find('.pm-label'),
                new_pm_label = $wrapper.find('input[id*="pm_label"]').val();

            if($this.hasClass('new-pm-label-ok') && $pm_label_wrapper.text() !== new_pm_label) {

                $pm_label_wrapper.text(new_pm_label);
                $wrapper.find('input.pm-label-field').val(new_pm_label);

                leyka_update_pm_list($pm_order);

            } else {
                $wrapper.find('input[id*="pm_label"]').val($pm_label_wrapper.text());
            }

            $pm_label_wrapper.show();
            $wrapper.find('.pm-label-fields').hide();
            $wrapper.find('.pm-control').show();

        }).on('keydown', 'input[id*="pm_label"]', function(e){

            let keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if(keycode === 13) { // Enter pressed - stop settings form from being submitted, but save PM custom label

                e.preventDefault();
                $(this).parents('.pm-label-fields').find('.new-pm-label-ok').click();

            }

        });

    $('.side-area').stick_in_parent({offset_top: 32}); // The adminbar height

    $pm_available_list.change(function(){

        let $pm_available_checkbox = $(this);

        $('#pm-'+$pm_available_checkbox.prop('id')).toggle(); // Show/hide a PM settings
        $('#'+$pm_available_checkbox.prop('id')+'-commission-wrapper').toggle(); // Show/hide a PM commission field

        let $sortable_pm = $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]');

        // Add/remove a sortable block from the PM order settings:
        if($pm_available_checkbox.prop('checked') && $sortable_pm.length) {
            $sortable_pm.show();
        } else {
            $sortable_pm.hide();
        }

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

    $pm_list_empty_block.on('click.leyka', function(e){

        $pm_list_empty_block.addClass('comment-displayed').find('.pm-list-empty-base-content').hide();
        $pm_list_empty_block.find('.pm-list-empty-comment').show();

    });

    $('.gateway-turn-off').click(function(e){

        e.preventDefault();

        // Emulate a change() checkboxes event manually, to lessen the ajax requests to update the PM order:
        $pm_available_list.filter(':checked').each(function(){

            let $pm_available_checkbox = $(this);

            $pm_available_checkbox.removeAttr('checked'); // Uncheck the active PM checkbox
            $('#pm-'+$pm_available_checkbox.prop('id')).hide(); // Hide a PM settings
            $('.pm-order[data-pm-id="'+$pm_available_checkbox.attr('id')+'"]').hide(); // Hide a PM sortable entry

        });

        $pm_order.sortable('refresh').sortable('refreshPositions').trigger('sortupdate');

    });

});

// Active recurring Gateways CRON job setup "option":
jQuery(document).ready(function($){

    let $active_recurring_cron_setup_field = $('.single-gateway-settings .active-recurring-on');

    if( !$active_recurring_cron_setup_field.length ) {
        return;
    }

    let $gateway_settings_wrapper = $active_recurring_cron_setup_field.parents('.gateway-settings'),
        $recurring_on_field = $gateway_settings_wrapper.find('.active-recurring-available input');

    $recurring_on_field.on('change.leyka', function(){

        if($recurring_on_field.prop('checked')) {
            $active_recurring_cron_setup_field.show();
        } else {
            $active_recurring_cron_setup_field.hide();
        }

    }).change();

});

// Yandex.Kassa old/new API options:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-yandex'),
        $new_api_used = $gateway_settings.find('input[name="leyka_yandex_new_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});

// PayPal old/new API options:
jQuery(document).ready(function($){

    let $gateway_settings = $('.single-gateway-settings.gateway-paypal'),
        $new_api_used = $gateway_settings.find('input[name="leyka_paypal_rest_api"]');

    if( !$gateway_settings.length || !$new_api_used.length ) {
        return;
    }

    $new_api_used.on('change.leyka', function(){

        if($new_api_used.prop('checked')) {

            $gateway_settings.find('.new-api').show();
            $gateway_settings.find('.old-api').hide();

        } else {

            $gateway_settings.find('.new-api').hide();
            $gateway_settings.find('.old-api').show();

        }

    }).change();

});

// PM list scroll in gateways cards:
jQuery(document).ready(function($){

    let icon_width = 40;

    if( !$('.gateways-cards-list').length ) {
        return;
    }

    function scroll_pm_icons_list($pm_icons_list, move_step) {

        let $movable_wrapper = $pm_icons_list.find('.pm-icons-wrapper'),
            $icons_container = $pm_icons_list.find('.pm-icons'),
            $icons_scroll = $pm_icons_list.find('.pm-icons-scroll'),
            current_left_offset = parseInt($.trim($movable_wrapper.css('left').replace('px', ''))),
            new_left_offset = current_left_offset - move_step;
        
        if(new_left_offset >= 0) {

            new_left_offset = 0;
            $pm_icons_list.find('.scroll-arrow.left').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.left').show();
        }
        
        if($icons_container.width() + new_left_offset <= $icons_scroll.width()) {

            new_left_offset = -($icons_container.width() - $icons_scroll.width());
            $pm_icons_list.find('.scroll-arrow.right').hide();

        } else {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }
        
        $movable_wrapper.css('left', String(new_left_offset) + 'px');

    }

    $('.gateway-card-supported-pm-list').each(function(){
        
        let $pm_icons_list = $(this);
        
        $(this).find('.scroll-arrow').click(function(){
            if($(this).hasClass('left')) {
                scroll_pm_icons_list( $pm_icons_list, -icon_width );
            } else {
                scroll_pm_icons_list( $pm_icons_list, icon_width );
            }
        });
        
        let $icons_container = $pm_icons_list.find('.pm-icons'),
            icons_width = icon_width * $icons_container.find('img').length;
        
        if(icons_width > $pm_icons_list.width()) {
            $pm_icons_list.find('.scroll-arrow.right').show();
        }

    });
    
});

jQuery(document).ready(function($){

    // import { registerBlockType } from '@wordpress/blocks';
    //
    // registerBlockType( 'leyka/test', {
    //     title: 'Leyka Test',
    //     description: 'Example block.',
    //     category: 'widgets',
    //     icon: 'smiley',
    //     supports: {
    //         // Removes support for an HTML mode.
    //         html: false,
    //     },
    //
    //     edit: () => {
    //         return '<div> Hello in Editor. </div>';
    //     },
    //
    //     save: () => {
    //         return '<div> Hello in Save.</div>';
    //     },
    // } );

});
/** Help page */
jQuery(document).ready(function($){

    let $page_wrapper = $('.wrap');
    if( !$page_wrapper.length || !$page_wrapper.hasClass('leyka-help-page') ) {
        return;
    }

    let $form = $('#feedback'),
        $loader = $('#feedback-loader'),
        $message_ok = $('#message-ok'),
        $message_error = $('#message-error');

    $form.submit(function(e){

        e.preventDefault();

        if( !validate_feedback_form($form) ) {
            return false;
        }

        $form.hide();
        $loader.show();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            topic: $form.find('input[name="leyka_feedback_topic"]').val(),
            name: $form.find('input[name="leyka_feedback_name"]').val(),
            email: $form.find('input[name="leyka_feedback_email"]').val(),
            text: $form.find('textarea[name="leyka_feedback_text"]').val(),
            nonce: $form.find('#nonce').val()
        }, function(response){

            $loader.hide();

            if(response === '0') {
                $message_ok.fadeIn(100);
            } else {
                $message_error.fadeIn(100);
            }

        });

        return true;

    });

    function validate_feedback_form($form) {

        let is_valid = true,
            $field = $form.find('input[name="leyka_feedback_name"]');

        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('input[name="leyka_feedback_email"]');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else if( !is_email($field.val()) ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.email_invalid_msg).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        $field = $form.find('textarea[name="leyka_feedback_text"]');
        if( !$field.val() ) {

            is_valid = false;
            $form.find('#'+$field.attr('id')+'-error').html(leyka.field_required).show();

        } else {
            $form.find('#'+$field.attr('id')+'-error').html('').hide();
        }

        return is_valid;

    }

});
/** Recurring subscriptions list page */
// jQuery(document).ready(function($){
//
//     let $page_wrapper = $('.wrap');
//     if( !$page_wrapper.length || $page_wrapper.data('leyka-admin-page-type') !== 'recurring-subscriptions-list-page' ) {
//         return;
//     }
//
//     // ...
//
// });
/** Common wizards functions */

// Expandable areas:
jQuery(document).ready(function($){
    $('.expandable-area .expand, .expandable-area .collapse').click(function(e){
        e.preventDefault();
        $(this).parent().toggleClass('collapsed');
    });
});

// Custom file input field:
jQuery(document).ready(function($){
    $('.settings-block.file .button').click(function(e){
        e.preventDefault();
        $(this).parent().find('input[type=file]').trigger('click');
    });
    
    $('.settings-block.file input[type=file]').change(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
    $('.settings-block.file input[type=file]').each(function(){
        $(this).parent().find('.chosen-file').text(String($(this).val()).split(/(\\|\/)/g).pop());
    });
    
});


// Image modal:
jQuery(document).ready(function($){
    
    if(typeof($().easyModal) === 'undefined') {
        return;
    }

    $('.leyka-instructions-screen-full').easyModal({
        top: 100,
        autoOpen: false
    });

    $('.zoom-screen').on('click.leyka', function(e){

        e.preventDefault();
        $(this)
            .closest('.captioned-screen')
            .find('.leyka-instructions-screen-full')
            .css('display', 'block')
            .trigger('openModal');

    });

});

// Notification modal:
jQuery(document).ready(function($){

    if(typeof($().dialog) === 'undefined') {
        return;
    }

    $('.leyka-wizard-modal').dialog({
        dialogClass: 'wp-dialog leyka-wizard-modal',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: 'center',
            at: 'center',
            of: window
        },
        open: function(){
            var $modal = $(this);
            $('.ui-widget-overlay').bind('click', function(){
                $modal.dialog('close');
            });
        },
        create: function () {
            $('.ui-dialog-titlebar-close').addClass('ui-button');

            var $modal = $(this);
            $modal.find('.button-dialog-close').bind('click', function(){
                $modal.dialog('close');
            });
        }

    });

    $('#cp-documents-sent').dialog('open');

});

// copy2clipboard
jQuery(document).ready(function($){
    
    function copyText2Clipboard(copyText) {
        var $copyBufferInput = $('<input>');
        $("body").append($copyBufferInput);
        $copyBufferInput.val(copyText).select();
        document.execCommand("copy");
        $copyBufferInput.remove();
    }
    
    function collectText2Copy($copyLink) {
        var $clone = $copyLink.parent().clone();
        $clone.find('.copy-link').remove();
        $clone.find('.copy-done').remove();
        
        var text = '';
        var $innerControl = $clone.find('input[type=text], input[type=color], input[type=date], input[type=datetime-local], input[type=month], input[type=email], input[type=number], input[type=search], input[type=range], input[type=search], input[type=tel], input[type=time], input[type=url], input[type=week], textarea');
        
        if($innerControl.length > 0) {
            text = $innerControl.val();
        }
        else {
            text = $clone.text();
        }
        
        return $.trim(text);
    }
    
    function addCopyControls($copyContainer) {
        
        var $copyLink = $('<span>');
        $copyLink.addClass('copy-control');
        $copyLink.addClass('copy-link');
        $copyLink.text(leyka_wizard_common.copy2clipboard);
        $copyContainer.append($copyLink);
        
        var $copyDone = $('<span>');
        $copyDone.addClass('copy-control');
        $copyDone.addClass('copy-done');
        $copyDone.text(leyka_wizard_common.copy2clipboard_done);
        $copyContainer.append($copyDone);
        
    }
    
    $('.leyka-wizard-copy2clipboard').each(function(){
        
        var $formFieldInside = $(this).find('.field-component.field');
        
        if($formFieldInside.length) {
            $(this).removeClass('leyka-wizard-copy2clipboard');
            $formFieldInside.addClass('leyka-wizard-copy2clipboard');
            addCopyControls($formFieldInside);
        }
        else {
            addCopyControls($(this));
        }
        
        $(this).find('.copy-link').click(function(){
            
            var $copyLink = $(this);
            
            var copyText = collectText2Copy($copyLink);
            copyText2Clipboard(copyText);
            
            $copyLink.fadeOut(function(){
                $copyLink.siblings('.copy-done').show();
                
                setTimeout(function(){
                    $copyLink.siblings('.copy-done').hide();
                    $copyLink.show();
                }, 2000);
            });
            
        });
    });
});
// CP payment tryout custom setting:
jQuery(document).ready(function($){

    var $cp_payment_tryout_field = $('.settings-block.custom_cp_payment_tryout'),
        $cp_error_message = $cp_payment_tryout_field.find('.field-errors'),
        $call_support_link = $cp_payment_tryout_field.find('.call-support');

    if( !$cp_payment_tryout_field.length ) {
        return;
    }

    $call_support_link.click(function(e){

        e.preventDefault();

        $('#leyka-help-chat-message').val(
            $('.current-wizard-title').val() + '\n'
            + 'Раздел: ' + $('.current-section-title').val() + '\n'
            + 'Шаг: ' + $('.current-step-title').val() + '\n\n'
            + 'Ошибка:\n'
            + $cp_error_message.text()
        );
        $('.help-chat-button').click();

    });

    $('.do-payment').on('click.leyka', function(e){

        e.preventDefault();

        var $payment_tryout_button = $(this);

        if($payment_tryout_button.data('submit-in-process')) {
            return;
        } else {
            $payment_tryout_button.data('submit-in-process', 1);
        }

        // Do a test donation:
        $payment_tryout_button.data('submit-in-process', 0);

        if( !leyka_wizard_cp.cp_public_id ) {

            $cp_error_message.html(leyka_wizard_cp.cp_not_set_up).show();
            return false;

        }

        var widget = new cp.CloudPayments();
        widget.charge({
            language: 'ru-RU',
            publicId: leyka_wizard_cp.cp_public_id,
            description: 'Leyka - payment testing',
            amount: 1.0,
            currency: leyka_wizard_cp.main_currency,
            accountId: leyka_wizard_cp.test_donor_email,
            invoiceId: 'leyka-test-donation'
        }, function(options){ // success callback

            $cp_error_message.html('').hide();
            $call_support_link.hide();

            $payment_tryout_button
                .removeClass('not-tested').hide()
                .siblings('.result.ok').show();

            if( !$cp_payment_tryout_field.find('.do-payment.not-tested').length ) {
                $cp_payment_tryout_field.find('input[name="payment_tryout_completed"]').val(1);
            }

        }, function(reason, options){ // fail callback

            $call_support_link.show();

            $cp_error_message.html(leyka_wizard_cp.cp_donation_failure_reasons[reason] || reason).show();
            $cp_payment_tryout_field.find('.payment-tryout-comment').hide();

        });

    });

});
// CP payment tryout custom setting - END

// Help chat:
jQuery(document).ready(function($){
    
    var $chat = $('.help-chat'),
        $chatButton = $('.help-chat-button');

    if( !$chat.length ) {
        return;
    }

    var $loading = $chat.find('.leyka-loader');

    function disableForm() {
        $chat.find('input[type=text]').prop('disabled', true);
        $chat.find('textarea').prop('disabled', true);
        $chat.find('.button').hide();
    }
    
    function enableForm() {
        $chat.find('input[type=text]').prop('disabled', false);
        $chat.find('textarea').prop('disabled', false);
        $chat.find('.button').show();
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function showOKMessage() {
        $chat.find('.ok-message').show();
        $chat.removeClass('fix-height');
    }

    function hideOKMessage() {
        $chat.find('.ok-message').hide();
        $chat.addClass('fix-height');
    }
    
    function showForm() {
        $chat.find('.form').show();
    }

    function hideForm() {
        $chat.find('.form').hide();
    }

    function validateForm() {
        return true;
    }
    
    function showHelpChat() {
        $chatButton.hide();
        $chat.show();
    }
    
    function hideHelpChat() {
        $chat.hide();
        $chatButton.show();
    }

    $chat.find('.form').submit(function(e) {
        e.preventDefault();
        
        if(!validateForm()) {
            return;
        }

        //hideErrors();
        hideForm();
        showLoading();

        $.post(leyka.ajaxurl, {
            action: 'leyka_send_feedback',
            name: $chat.find('#leyka-help-chat-name').val(),
            topic: "Сообщение из формы обратной связи Лейки",
            email: $chat.find('#leyka-help-chat-email').val(),
            text: $chat.find('#leyka-help-chat-message').val(),
            nonce: $chat.find('#leyka_feedback_sending_nonce').val()
        }, null).done(function(response) {
    
            if(response === '0') {
                showOKMessage();
                hideForm();
            } else {
                alert('Ошибка!');
                showForm();
            }

        }).fail(function() {
            showForm();
        }).always(function() {
            hideLoading();
        });
            
    });
    
    $chatButton.click(function(e){
        e.preventDefault();
        showHelpChat();
        hideOKMessage();
        showForm();
    });

    $chat.find('.close').click(function(e){
        e.preventDefault();
        hideHelpChat();
        hideForm();
        showOKMessage();
    });
    
});


// Campaign decoration custom setting:
jQuery(document).ready(function($){
    
    if( !$('#leyka-settings-form-cd-campaign_decoration').length ) {
        return;
    }

    var campaignAttachmentId = 0;
    var $decorationControlsWrap = $('#campaign-decoration');
    var $previewFrame = $('#leyka-preview-frame');
    var $previewIframe = $previewFrame.find('iframe');
    var $loading = $decorationControlsWrap.find('#campaign-decoration-loading');
    var campaignId = $decorationControlsWrap.find('#leyka-decor-campaign-id').val();
    var $selectTemplateControl = $('#leyka_campaign_template-field');
    
    function disableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', true);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', true);
    }
    
    function enableForm() {
        $decorationControlsWrap.find('#campaign_photo-upload-button').prop('disabled', false);
        $decorationControlsWrap.find('#leyka_campaign_template-field').prop('disabled', false);
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function reloadPreviewFrame() {
        //$previewIframe.get(0).contentWindow.location.reload(true);
        var previewLocation = $previewIframe.get(0).contentWindow.location;
        var href = previewLocation.href;
        href = href.replace(/&rand=.*/, '');
        href += '&rand=' + Math.random();
        previewLocation.href = href;
    }
    
    $previewIframe.on('load', function(){
        $previewIframe.height($previewIframe.contents().find('body').height() + 10);
        $previewIframe.contents().find('body').addClass('wizard-init-campaign-preview');
    });

    $('#campaign_photo-upload-button').on('click.leyka', function(){

        var frame = wp.media({
            title: 'Выбор фотографии кампании',
            multiple: false
        });
        
        frame.on('select', function(){

            var attachment = frame.state().get('selection').first().toJSON();

            if( !attachment.id ) {
                return;
            }

            disableForm();
            showLoading();
            
            $('#leyka-campaign_thumnail').val(attachment.id);
            
            $.post(leyka.ajaxurl, {
                action: 'leyka_set_campaign_photo',
                attachment_id: attachment.id,
                campaign_id: campaignId,
                nonce: $decorationControlsWrap.find('#set-campaign-photo-nonce').val()
            }, null, 'json')
                .done(function(json) {
        
                    if(typeof json.status !== 'undefined' && json.status === 'error') {
                        alert('Ошибка!');
                        return;
                    }
                    
                    reloadPreviewFrame();
                })
                .fail(function() {
                    alert('Ошибка!');
                })
                .always(function() {
                    hideLoading();
                    enableForm();
                });

        });

        frame.open();

    });
    
    $selectTemplateControl.on('change', function(){
        
        disableForm();
        showLoading();
        
        var template = $(this).val();
        $('#leyka-campaign_template').val(template);
        
        $.post(leyka.ajaxurl, {
            action: 'leyka_set_campaign_template',
            campaign_id: campaignId,
            template: template,
            nonce: $decorationControlsWrap.find('#set-campaign-template-nonce').val()
        }, null, 'json')
            .done(function(json) {
    
                if(typeof json.status !== 'undefined' && json.status === 'error') {
                    alert('Ошибка!');
                    return;
                }
                
                reloadPreviewFrame();
                //setFrameClass();
            })
            .fail(function() {
                alert('Ошибка!');
            })
            .always(function() {
                hideLoading();
                enableForm();
            });            
            
    });
    
    function setFrameClass() {
        $selectTemplateControl.find('option').each(function(i, el){
            $previewFrame.removeClass($(el).val());
        });
        $previewFrame.addClass($selectTemplateControl.val());
    }

    // move next button
    $('.step-submit').insertBefore($('#campaign-decoration-loading'));

});

// Edit permalink:
jQuery(document).ready(function($){

    var $edit_permalink_wrap = $('.leyka-campaign-permalink'),
        $edit_link = $edit_permalink_wrap.find('.inline-edit-slug'),
        $current_slug = $edit_permalink_wrap.find('.current-slug'),
        $edit_form = $edit_permalink_wrap.find('.inline-edit-slug-form'),
        $slug_field = $edit_form.find('.leyka-slug-field'),
        $loading = $edit_permalink_wrap.find('.edit-permalink-loading');

    $edit_link.on('click.leyka', function(e){

        e.preventDefault();

        $current_slug.hide();
        $edit_link.hide();
        $edit_form.show();

    });

    $edit_permalink_wrap.find('.slug-submit-buttons')
        .on('click.leyka', '.inline-reset', function(e){

            e.preventDefault();

            $edit_form.hide();
            $slug_field.val($edit_form.data('slug-original'));

            $edit_link.show();
            $current_slug.show();

        })
        .on('click.leyka', '.inline-submit', function(e){

            e.preventDefault();

            $loading.show();
            $edit_form.hide();

            $.post(leyka.ajaxurl, {
                action: 'leyka_edit_campaign_slug',
                campaign_id: $edit_form.data('campaign-id'),
                slug: $slug_field.val(),
                nonce: $edit_form.data('nonce')
            }, null, 'json')
                .done(function(json) {

                    if(typeof json.status === 'undefined') {
                        alert('Ошибка!');
                    } else if(json.status === 'ok' && typeof json.slug !== 'undefined') {

                        $slug_field.val(json.slug);
                        $edit_form.data('slug-original', json.slug);
                        $current_slug.text(json.slug);

                    } else {
                        alert('Ошибка!');
                    }

                }).fail(function(){
                    alert('Ошибка!');
                }).always(function(){

                    $loading.hide();
                    $edit_link.show();
                    $current_slug.show();

                });

        });

});

// Auto-copy campaign shortcode:
jQuery(document).ready(function($){

    var $shortcode_field_wrap = $('.leyka-campaign-shortcode-field'),
        $copy_shortcode_link = $shortcode_field_wrap.siblings('.inline-copy-shortcode'),
        $current_shortcode = $shortcode_field_wrap.siblings('.leyka-current-value');

    $copy_shortcode_link.on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.hide();
        $current_shortcode.hide();
        $shortcode_field_wrap.show();

    });

    $shortcode_field_wrap.find('.inline-reset').on('click.leyka', function(e){

        e.preventDefault();

        $copy_shortcode_link.show();
        $current_shortcode.show();
        $shortcode_field_wrap.hide();

    });

});

// Highlighted keys in rich edit
jQuery(document).ready(function($){
    
    $('.type-rich_html').each(function(){
        initRichHTMLTagsReplace($, $(this));
    });
    
});

function initRichHTMLTagsReplace($, $controlContainer) {

    var isInitEditDocsDone = false;
    var isEditContentLoadDone = false;
    var isEditFieldTouched = false;
    var originalDocHTML = null;
    var $frameBody = null;
    var isSkipDOMSubtreeModified = false;
    var keysValues = [];
    
    function showRestoreOriginalDocHTMLLink() {
        
        var $link = $controlContainer.find('.restore-original-doc');
        
        if(!$link.length) {
            
            $link = $('<a>Вернуть первоначальный текст</a>')
                .attr('href', '#')
                .addClass("inner")
                .addClass("restore-original-doc");
            
            $controlContainer.find('.wp-editor-wrap').append($link);
        }
        
        $link.unbind('click');
        $link.click(restoreOriginalDocHTML);
        $link.show();
        
    }
    
    function restoreOriginalDocHTML() {
        
        if(originalDocHTML) {
            $frameBody.html(originalDocHTML);
        }
        
        $controlContainer.find('.restore-original-doc').hide();
        replaceKeysWithHTML();
        handleChangeEvents();
        $controlContainer.find('.restore-original-doc').hide(); // hack for FF
        
        return false;
    }
    
    function replaceKeysValues(keysValues) {
        for(var i in keysValues[0]) {
            var limit = 100;
            while($frameBody.html().search(keysValues[0][i]) > -1 && limit > 0) {
                limit -= 1;
                var $replacement = $("<span>");
                $replacement.addClass("leyka-doc-key-wrap");
                $replacement.addClass("leyka-doc-key");
                $replacement.attr('data-key', keysValues[0][i].replace("#", "+"));
                $replacement.attr('data-original-value', keysValues[1][i]);
                $replacement.html(keysValues[1][i]);
                $frameBody.html( $frameBody.html().replace(keysValues[0][i], "<span id='key-replacement'> </span>") );
                $frameBody.find('#key-replacement').replaceWith($replacement);
            }
        }
    }
    
    function replaceKeysWithHTML() {
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        
        originalDocHTML = $frameBody.html();
        
        if($controlContainer.find('#leyka_pd_terms_text-field').length > 0 || $controlContainer.find('#leyka_person_pd_terms_text-field').length > 0) {
            keysValues = leykaRichHTMLTags.pdKeys;
        }
        else {
            keysValues = leykaRichHTMLTags.termsKeys;
        }
        
        replaceKeysValues(keysValues);
        
        //$frameBody.find(".leyka-doc-key").each(function(){
        //    $(this).data('original-value', $(this).text());
        //});

    }
    
    function handleChangeEvents() {
        
        $frameBody.unbind("click");
        $frameBody.on('click', function(){
            isEditFieldTouched = true;
        });
        
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.bind("DOMSubtreeModified", function(){
            
            if(!isEditContentLoadDone || !originalDocHTML || !isEditFieldTouched) {
                return;
            }
        
            showRestoreOriginalDocHTMLLink();
        });
        
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").bind("DOMSubtreeModified", function(){
            $(this).removeClass("leyka-doc-key");
            if($(this).text() == $(this).data('original-value') && !isSkipDOMSubtreeModified) {
                $(this).addClass("leyka-doc-key");
                isSkipDOMSubtreeModified = true;
            }
            else {
                isSkipDOMSubtreeModified = false;
            }
        });
        
    }
    
    function initEditDocs($iframe) {
        if(isInitEditDocsDone) {
            return;
        }
        isInitEditDocsDone = true;
        
        var $frameDocument = $iframe.contents();
        
        $frameDocument.find('body').bind("DOMSubtreeModified", function(){
            if($frameDocument.find('body p').length > 0) {
                if(isEditContentLoadDone) {
                    return;
                }
                isEditContentLoadDone = true;
                
                $frameBody = $frameDocument.find('body');
                restoreOriginalDocHTML();
            }
        });
        
    }
    
    function tryInitEditDocs($tinyMCEContainer) {

        var $iframe = $tinyMCEContainer.find('iframe');
        if($iframe.length) {
            $iframe.on('load', function(){
                initEditDocs($(this));
            });
        }

    }
    
    $('.step-next.button, input[name=leyka_settings_beneficiary_submit], input[name=leyka_settings_email_submit]').click(function(e){
        $frameBody.unbind("DOMSubtreeModified");
        $frameBody.find(".leyka-doc-key").unbind("DOMSubtreeModified");
        $frameBody.find('.leyka-doc-key-wrap').each(function(index, el){
            if($(el).hasClass('leyka-doc-key')) {
                $(el).replaceWith($(el).data('key').replace("+", "#"));
            }
            else {
                $(el).replaceWith($(el).html());
            }
        });
        //e.preventDefault();
    });
    
    $controlContainer.find('.wp-editor-container').bind("DOMSubtreeModified", function(){
        tryInitEditDocs($(this));
    });
    tryInitEditDocs($controlContainer.find('.wp-editor-container'));
    
}


// show-hide available tags
jQuery(document).ready(function($){
    $('.hide-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $(this).closest('.field-component').find('.show-available-tags').show();
        $(this).closest('.field-component').find('.placeholders-help').hide();
    });
    
    $('.show-available-tags').click(function(e){
        e.preventDefault();
        $(this).hide();
        $(this).closest('.field-component').find('.hide-available-tags').show();
        $(this).closest('.field-component').find('.placeholders-help').show();
    });
});


// org actual address
jQuery(document).ready(function($){
    
    var $orgActualAddressInput = $('#leyka_org_actual_address-field');
    var $orgActualAddressCheckbox = $('#leyka_org_actual_address_differs-field');
    var $orgActualAddressWrapper = $('#leyka_org_actual_address-wrapper');

    var orgActualAddress = $orgActualAddressInput.val();
    orgActualAddress = $.trim(orgActualAddress);
    
    if(!orgActualAddress) {
        $orgActualAddressWrapper.hide();
        $orgActualAddressCheckbox.prop('checked', false);
    }
    else {
        $orgActualAddressCheckbox.prop('checked', true);
    }
    
    $orgActualAddressCheckbox.change(function(){
        if($(this).prop('checked')) {
            $orgActualAddressWrapper.show();
            $orgActualAddressInput.val(orgActualAddress);
        }
        else {
            $orgActualAddressWrapper.hide();
            orgActualAddress = $orgActualAddressInput.val();
            $orgActualAddressInput.val('');
        }
    });
    
});

// YooKassa shopPassword generator:
jQuery(document).ready(function($){

    var $genBtn = $('#yandex-generate-shop-password');
    
    if( !$genBtn.length ) {
        return;
    }
    
    var $stepSubmit = $('.step-submit');
    $stepSubmit.hide();
    
    $genBtn.click(function(){

        var password = leyka_make_password(10),
            $block = $genBtn.closest('.enum-separated-block');

        $genBtn.hide();
        $block.find('.caption').css('display', 'unset');
        $block.find('.body b').css('display', 'unset').text(password);
        $block.find('input[name=leyka_yandex_shop_password]').val(password);
        $stepSubmit.show();
        
        $(this).closest('.body').removeClass('no-password');

    });

});
// YooKassa shopPassword generator - END

// YooKassa payment tryout:
jQuery(document).ready(function($){

    let $gen_btn = $('#yandex-make-live-payment'),
        $loading = $('.yandex-make-live-payment-loader');

    if( !$gen_btn.length ) {
        return;
    }

    leykaYandexPaymentData.leyka_success_page_url = window.location.href;
    leykaYandexPaymentData.leyka_is_gateway_tryout = 1;

    $gen_btn.click(function(){

        $loading.show();
        $gen_btn.prop('disabled', true);

        $.post(leyka.ajaxurl, leykaYandexPaymentData, null, 'json')
            .done(function(json) {

                if(typeof json.status === 'undefined') {
                    alert('Ошибка!');
                } else if(json.status === 0 && json.payment_url) {
                    window.location.href = json.payment_url;
                } else {
                    alert('Ошибка!');
                }

            }).fail(function(){
                alert('Ошибка!');
            }).always(function(){
                $loading.hide();
                $gen_btn.prop('disabled', false);
            });
            
    });

});
// Yandex.Kassa payment tryout - END