/** Common utilities & tools */
function is_email(value) {
    return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test(value);
}

function is_phone_number(value) {
    return /^[0-9\+\-\. ]{10,}$/.test(value);
}

/** Validate the date string in DD.MM.YYYY format */
function is_date(value) {
    return /^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/.test(value);
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
        e.keyCode === 46 || e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 13 || // Backspace, delete, tab, enter
        (e.keyCode === 65 && e.ctrlKey) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey) || // Ctrl+C
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

/**
 * Number.prototype.format(decimal_length, section_length, sections_delimiter, decimal_delimiter)
 *
 * @param integer decimal_length The length of decimal
 * @param integer section_length: length of whole part
 * @param mixed   sections_delimiter: sections delimiter
 * @param mixed   decimal_delimiter: decimal delimiter
 */
Number.prototype.format = function(decimal_length, section_length, sections_delimiter, decimal_delimiter) {

    decimal_length = typeof decimal_length === 'undefined' ? 0 : parseInt(decimal_length);
    section_length = typeof section_length === 'undefined' ? 3 : parseInt(section_length);
    sections_delimiter = typeof sections_delimiter === 'undefined' ? ' ' : sections_delimiter;
    decimal_delimiter = typeof decimal_delimiter === 'undefined' ? '.' : decimal_delimiter;

    var re = '\\d(?=(\\d{' + (section_length || 3) + '})+' + (decimal_length > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~decimal_length));

    return (decimal_delimiter ? num.replace('.', decimal_delimiter) : num).replace(new RegExp(re, 'g'), '$&' + (sections_delimiter || ','));

};

function leyka_translit(text) {

    var chars = {"а" : "a", "б": "b", "в": "v", "г": "g", "д": "d", "е": "e", "ё": "yo", "ж": "zh", "з": "z",
        "и": "i", "й": "j", "к": "k", "л": "l", "м": "m", "н": "n", "о": "o", "п": "p", "р": "r", "с": "s",
        "т": "t", "у": "u", "ф": "f", "х": "kh", "ц": "cz", "ч": "ch", "ш": "sh", "щ": "shh", "ъ": "",
        "ы": "y", "ь": "", "э": "e", "ю": "yu", "я": "ya", "А": "A", "Б": "B", "В": "V", "Г": "G",
        "Д": "D", "Е": "E", "Ё": "Yo", "Ж": "Zh", "З": "Z", "И": "I", "Й": "J", "К": "K", "Л": "L",
        "М": "M", "Н": "N", "О": "O", "П": "P", "Р": "R", "С": "S", "Т": "T", "У": "U", "Ф": "F",
        "Х": "Kh", "Ц": "Cz","Ч": "Ch","Ш": "Sh", "Щ": "Shh", "Ъ": "", "Ы": "Y", "Ь": "", "Э": "E",
        "Ю": "Yu", "Я": "Ya"};

    return text
        .split('')
        .map((char) => { return chars[char.toString()] || char; })
        .join('');

}