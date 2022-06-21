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

// Upgrade for JSON.stringify to allow arrays (used by LeykaLocalStorage class)
(function(){

    const convArrToObj = function(array){

        let thisEleObj = new Object();

        if(typeof array == "object"){
            for(var i in array){

                var thisEle = convArrToObj(array[i]);
                thisEleObj[i] = thisEle;

            }
        }else {
            thisEleObj = array;
        }

        return thisEleObj;

    };

    let oldJSONStringify = JSON.stringify;

    JSON.stringify = function(input){
        if(oldJSONStringify(input) == '[]')
            return oldJSONStringify(convArrToObj(input));
        else
            return oldJSONStringify(input);
    };

})();

function leyka_equlize_elements_width(elements_selector) {

    const max_width = Math.max.apply(null, jQuery.map(
        jQuery(elements_selector),
        ($_element) => { return Math.ceil(parseFloat(jQuery($_element).css('width'))); })
    );

    jQuery(elements_selector).each((element_idx, $element) => {
        jQuery($element).css('width', max_width);
    })
}

/**
 * Class to handle LocalStorage
 */
class LeykaLocalStorage {

    static get(key) {
        return localStorage.getItem('leyka-' + key);
    }

    static set(key, value) {
        localStorage.setItem('leyka-' + key, value);
    }

    static remove(key) {
        localStorage.removeItem('leyka-' + key);
    }

}

/**
 * Class to handle stored states of the DOM elements (currently only visibility)
 */
class LeykaStateControl {

    static state_hidden = 'hidden';
    static state_visible = 'visible';
    static class_hidden = 'leyka-hidden';
    static class_closed = 'leyka-closed';
    static class_v_c_button = 'leyka-visibility-control-button'; // "v_c" => "visibility_control"
    static data_attribute_v_c_button_targed = 'visibility-control-target';

    static storeElementVisibilityState(selector, state) {

        let elements_visibility = JSON.parse(LeykaLocalStorage.get('elements-visibility')) ? JSON.parse(LeykaLocalStorage.get('elements-visibility')) : [] ;

        elements_visibility[selector] = state;

        LeykaLocalStorage.set('elements-visibility', JSON.stringify(elements_visibility));

    }

    /**
     * Hide element
     * @param selector
     * @return {boolean}
     */
    static hideElement(selector) {

        let $element = jQuery(selector);

        if($element.length === 0) {
            return false;
        }

        $element.addClass(this.class_hidden);
        jQuery('.'+this.class_v_c_button+'[data-'+this.data_attribute_v_c_button_targed+'="'+selector+'"]').addClass(this.class_closed);

        this.storeElementVisibilityState(selector, this.state_hidden);

        return true;

    }

    /**
     * Show element
     * @param selector
     * @return {boolean}
     */
    static showElement(selector) {

        let $element = jQuery(selector);

        if($element.length === 0) {
            return false;
        }

        $element.removeClass(this.class_hidden);
        jQuery('.'+this.class_v_c_button+'[data-'+this.data_attribute_v_c_button_targed+'="'+selector+'"]').removeClass(this.class_closed);

        this.storeElementVisibilityState(selector, this.state_visible);

        return true;

    }

    /**
     * Toggle element visibility
     * @param selector
     * @return {boolean}
     */
    static toggleElementVisibility(selector) {

        let $element = jQuery(selector);

        if($element.length === 0) {
            return false;
        }

        if(this.getElementVisibility(selector) === this.state_visible) {
            this.hideElement(selector);
        } else if(this.getElementVisibility(selector) === this.state_hidden) {
            this.showElement(selector);
        }

        return true;

    }

    /**
     * Get stored visibility state
     * @param selector
     * @return {boolean|string}
     */
    static getElementVisibility(selector) {

        const $element = jQuery(selector);

        if($element.length === 0) {
            return false;
        }

        let elements_visibility = JSON.parse(LeykaLocalStorage.get('elements-visibility')) ? JSON.parse(LeykaLocalStorage.get('elements-visibility')) : [] ;

        if(elements_visibility[selector] == null) {

            elements_visibility[selector] = jQuery($element).hasClass(this.class_hidden) ? this.state_hidden : this.state_visible;

            this.storeElementVisibilityState(selector, elements_visibility[selector]);

        }

        return elements_visibility[selector];

    }

    /**
     * Apply stored visibility state to the array of elements
     * @param selectors Array of selectors
     * @return {boolean|*[]}
     */
    static applyElementsVisibility(selectors) {

        if( !Array.isArray(selectors) ) {
            return false;
        }

        let result = [];

        selectors.forEach((selector) => {

            let $element = jQuery(selector);

            if($element.length === 0) {
                return false;
            }

            result[selector] = this.getElementVisibility(selector);

            if(result[selector] === this.state_visible) {
                $element.removeClass(this.class_hidden);
                jQuery('.'+this.class_v_c_button+'[data-'+this.data_attribute_v_c_button_targed+'="'+selector+'"]').removeClass(this.class_closed);
            } else if(result[selector] === this.state_hidden){
                $element.addClass(this.class_hidden);
                jQuery('.'+this.class_v_c_button+'[data-'+this.data_attribute_v_c_button_targed+'="'+selector+'"]').addClass(this.class_closed);
            }

        }, this);

        return result;

    }

    /**
     * Bind visibility control event to all buttons on the page with the sufficient class and apply visibility states
     */
    static initVisibilityControlButtons() {

        const $buttons = jQuery('.'+this.class_v_c_button);
        let targets_selectors = [];

        $buttons.each((idx, $button) => {

            const target_selector = jQuery($button).data(this.data_attribute_v_c_button_targed);

            targets_selectors.push(target_selector);

            jQuery($button).off('click.LeykaStateControl');
            jQuery($button).on('click.LeykaStateControl', () => {
                this.toggleElementVisibility(target_selector);
            })

        }, this);

        this.applyElementsVisibility(targets_selectors);

    }

}