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
        l = 80;
    }
    else {
        l = 20;
    }
    return [h, s, l];
}

function leykaMainHslColor2Text(h, s, l) {
    if(l < 50) {
        l = 90;
    }
    else {
        l = 10;
    }
    s = 5;
    return [h, s, l];
}