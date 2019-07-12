if(jQuery.ui.autocomplete) {
	let $ = jQuery;

	$.widget("ui.autocomplete", $.ui.autocomplete, {
	    options : $.extend({}, this.options, {
	        multiselect: false,
	        search_on_focus: false,
	        leyka_select_callback: false,
	        pre_selected_values: [],
	        position: { my: "left-5px top+6px", at: "left bottom", collision: "none" }
	    }),
	    _create: function(){
	        this._super();

	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
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
