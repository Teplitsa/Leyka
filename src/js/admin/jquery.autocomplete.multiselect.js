if(jQuery.ui.autocomplete) {
	jQuery.widget("ui.autocomplete", jQuery.ui.autocomplete, {
	    options : jQuery.extend({}, this.options, {
	        multiselect: false
	    }),
	    _create: function(){
	        this._super();

	        var self = this,
	            o = self.options;

	        if (o.multiselect) {
	            console.log('multiselect true');

	            self.selectedItems = {};           
	            self.multiselect = jQuery("<div></div>")
	                .addClass("ui-autocomplete-multiselect ui-state-default ui-widget")
	                .css("width", self.element.width())
	                .insertBefore(self.element)
	                .append(self.element)
	                .bind("click.autocomplete", function(){
	                    self.element.focus();
	                });
	            
	            var fontSize = parseInt(self.element.css("fontSize"), 10);
	            function autoSize(e){
	                var jQuerythis = jQuery(this);
	                jQuerythis.width(1).width(this.scrollWidth+fontSize-1);
	            };

	            var kc = jQuery.ui.keyCode;
	            self.element.bind({
	                "keydown.autocomplete": function(e){
	                    if ((this.value === "") && (e.keyCode == kc.BACKSPACE)) {
	                        var prev = self.element.prev();
	                        delete self.selectedItems[prev.text()];
	                        prev.remove();
	                    }
	                },
	                "focus.autocomplete blur.autocomplete": function(){
	                    self.multiselect.toggleClass("ui-state-active");
	                },
	                "keypress.autocomplete change.autocomplete focus.autocomplete blur.autocomplete": autoSize
	            }).trigger("change");

	            o.select = o.select || function(e, ui) {
	                jQuery("<div></div>")
	                    .addClass("ui-autocomplete-multiselect-item")
	                    .text(ui.item.label)
	                    .append(
	                        jQuery("<span></span>")
	                            .addClass("ui-icon ui-icon-close")
	                            .click(function(){
	                                var item = jQuery(this).parent();
	                                delete self.selectedItems[item.text()];
	                                item.remove();
	                            })
	                    )
	                    .insertBefore(self.element);
	                
	                self.selectedItems[ui.item.label] = ui.item;
	                self._value("");
	                return false;
	            }

	            /*self.options.open = function(e, ui) {
	                var pos = self.multiselect.position();
	                pos.top += self.multiselect.height();
	                self.menu.element.position(pos);
	            }*/
	        }

	        return this;
	    }
	});	
}
