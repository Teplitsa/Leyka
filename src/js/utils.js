/** Common utilities & tools */
function is_email(email) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(email);
}

function leyka_get_ajax_url() {
    return typeof leyka != 'undefined' ? leyka.ajaxurl : typeof frontend != 'undefined' ? frontend.ajaxurl : '/';
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

    // Allowed special keys
    return (
        e.keyCode === 9 || // Tab
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
        (e.keyCode >= 35 && e.keyCode <= 40) // Home, end, left, right, down, up
    );
}

function leyka_validate_donor_name(name_string) {
    return !name_string.match(/[!@#$%^&*()+=\[\]{};:"\\|,<>\/?]/);
}

function leyka_empty(mixed_var) {

    var undefined,
        empty_values = [undefined, null, false, 0, '', '0'];

    for(var i = 0; i < empty_values.length; i++) {
        if(mixed_var === empty_values[i]) {
            return true;
        }
    }

    if(typeof mixed_var === 'object') {

        for(var key in mixed_var) {
            if(mixed_var.hasOwnProperty(key)) {
                return false;
            }
        }

        return true;

    }

    return false;

}