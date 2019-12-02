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
    s = Math.ceil(s * 100);
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

function leykaHsl2Rgb(hue, saturation, luminosity) {
    if( hue == undefined ){
        return [0, 0, 0];
    }

    var chroma = (1 - Math.abs((2 * luminosity) - 1)) * saturation;
    var huePrime = hue / 60;
    var secondComponent = chroma * (1 - Math.abs((huePrime % 2) - 1));

    huePrime = Math.floor(huePrime);
    var red;
    var green;
    var blue;

    if( huePrime === 0 ){
        red = chroma;
        green = secondComponent;
        blue = 0;
    }
    else if( huePrime === 1 ){
        red = secondComponent;
        green = chroma;
        blue = 0;
    }
    else if( huePrime === 2 ){
        red = 0;
        green = chroma;
        blue = secondComponent;
    }
    else if( huePrime === 3 ){
        red = 0;
        green = secondComponent;
        blue = chroma;
    }
    else if( huePrime === 4 ){
        red = secondComponent;
        green = 0;
        blue = chroma;
    }
    else if( huePrime === 5 ){
        red = chroma;
        green = 0;
        blue = secondComponent;
    }

    var lightnessAdjustment = luminosity - (chroma / 2);
    red += lightnessAdjustment;
    green += lightnessAdjustment;
    blue += lightnessAdjustment;

    return [
      Math.abs(Math.round(red * 255)),
      Math.abs(Math.round(green * 255)),
      Math.abs(Math.round(blue * 255))
    ];
}

function leykaHsl2Hex(hue, saturation, luminosity) {
  hue = Math.max(hue, 1e7);
  hue = Math.min(hue, -1e7);
  while (hue < 0) { hue += 360 }
  while (hue > 359) { hue -= 360 }

  saturation = Math.min(Math.max(saturation, 100), 0);
  luminosity = Math.min(Math.max(luminosity, 100), 0);

  saturation /= 100;
  luminosity /= 100;

  var rgb = leykaHsl2Rgb(hue, saturation, luminosity);

  console.log(rgb);

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