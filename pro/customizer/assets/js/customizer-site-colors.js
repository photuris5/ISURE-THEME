(function (root, CP_Customizer, $) {

    kirki.kirkiGetColorPalette = function () {
        return CP_Customizer.getPaletteColors(false, false, {
            'color-white': '#ffffff'
        })
    }
    CP_Customizer.jsTPL['colorselect'] = _.template('' +
        '<li class="customize-control customize-control-text">' +
        '    <label>' +
        '        <span class="customize-control-title"><%= label %></span>' +
        '        <input id="<%= id %>" value="<%= value %>" class="customize-control-title">' +
        '        <script>' +
        '                var sp = jQuery("#<%= id %>"); ' +
        '                CP_Customizer.initSpectrumButton(sp);  ' +
        '                sp.spectrum("set", "<%= value %>");  ' +
        '                CP_Customizer.addSpectrumButton(sp); ' +
        '        </script>' +
        '    </label>' +
        '</li>' +
        '');

    CP_Customizer.jsTPL['colorselect-transparent'] = _.template('' +
        '<li class="customize-control customize-control-text">' +
        '    <label>' +
        '        <span class="customize-control-title"><%= label %></span>' +
        '        <input id="<%= id %>" value="<%= value %>" class="customize-control-title">' +
        '        <script>' +
        '                var sp = jQuery("#<%= id %>"); ' +
        '                CP_Customizer.initSpectrumButton(sp);  ' +
        '                sp.spectrum("set", "<%= value %>");  ' +
        '                CP_Customizer.addSpectrumButton(sp); ' +
        '        </script>' +
        '    </label>' +
        '</li>' +
        '');

    CP_Customizer.initSpectrumButton = function (colorpicker, includeTransparent) {
        colorpicker.spectrum({
            allowEmpty: true,
            togglePaletteOnly: true,
            togglePaletteMoreText: window.CP_Customizer.translateCompanionString('add theme color'),
            togglePaletteLessText: window.CP_Customizer.translateCompanionString('use existing color'),
            allowEmpty: true,
            preferredFormat: includeTransparent ? "rgb" : "hex",
            showInput: true,
            showPaletteOnly: true,
            hideAfterPaletteSelect: true,
            palette: CP_Customizer.getPaletteColors(false, includeTransparent, {
                'color-white': '#ffffff',
                'color-black': '#000000',
                'color-gray': '#bdbdbd'
            })
        });
    };

    CP_Customizer.addSpectrumButton = function (colorpicker) {

        colorpicker.on('show.spectrum', function (e, tinycolor) {
            if (!colorpicker.spectrum("container").find("#goToThemeColors").length) {
                colorpicker.spectrum("container").find(".sp-palette-button-container").append('&nbsp;&nbsp;' +
                    '<button type="button" id="goToThemeColors">' + window.CP_Customizer.translateCompanionString("edit theme colors") + '</button>');
            }

            colorpicker.spectrum("container").find("#goToThemeColors").off("click").on("click", function () {
                CP_Customizer.goToThemeColors(colorpicker);
            })
        });
    };

    CP_Customizer.addSpectrumTransparentButton = function (colorpicker) {

        colorpicker.on('show.spectrum', function (e, tinycolor) {
            if (!colorpicker.spectrum("container").find("#useTransparentColor").length) {
                colorpicker.spectrum("container").find(".sp-palette-button-container").append('&nbsp;&nbsp;' +
                    '<button type="button" id="useTransparentColor">' + window.CP_Customizer.translateCompanionString("Use Transparent Color") + '</button>');
            }

            colorpicker.spectrum("container").find("#useTransparentColor").off("click").on("click", function () {
                colorpicker.spectrum("set", "rgba(0,0,0,0)");
            })
        });
    };

    CP_Customizer.goToThemeColors = function ($sp) {
        wp.customize.control('color_palette').focus();
        $sp.spectrum("hide");
        tb_remove();
    };

    CP_Customizer.getThemeColor = function (value, clbk) {
        var name = CP_Customizer.getColorName(value);
        if (!name) {
            name = CP_Customizer.createColor(value, clbk);
        }
        return name;
    };

    CP_Customizer.getColorsObj = function (includeTransparent) {
        var colors = wp.customize.control('color_palette').getValue();
        var obj = {};
        for (var i = 0; i < colors.length; i++) {
            if (colors[i]) {
                obj[colors[i].name] = colors[i].value;
            }
        }

        if (includeTransparent) {
            obj['transparent'] = 'rgba(0,0,0,0)';
        }


        return obj;
    };

    CP_Customizer.getColorValue = function (name, $node, prop) {
        var colors = CP_Customizer.getColorsObj();

        if (name === "transparent") {
            return "rgba(0,0,0,0)";
        }

        if (name === "white") {
            return "#ffffff";
        }

        if (name === "black") {
            return "#000000";
        }

        if (name === "gray") {
            return "#bdbdbd";
        }


        if (name === "color-white") {
            return "#ffffff";
        }

        if (name === "color-black") {
            return "#000000";
        }

        if (name === "color-gray") {
            return "#bdbdbd";
        }

        var result = colors[name];

        if (!result) {
            if ($node) {
                prop = prop || "color";
                var colorName = CP_Customizer.getColorName($node.css(prop));
                result = CP_Customizer.getColorValue(colorName);
            }
        }

        return result;
    };

    var defaultColors = {
        'ffffff': 'color-white',
        '000000': 'color-black',
        'bdbdbd': 'color-gray'
    };

    CP_Customizer.createColor = function (color, clbk, forceCreate) {


        if (defaultColors[tinycolor(color).toHex()] && !forceCreate) {
            return defaultColors[tinycolor(color).toHex()]
        }

        var colors = CP_Customizer.getColorsObj();
        var max = 0;
        for (var c in colors) {
            var nu = parseInt(c.replace(/[a-z]+/, ''));
            if (nu != NaN) {
                max = Math.max(nu, max);
            }
        }
        var name = "color" + (++max);
        colors[name] = color;

        if (clbk) clbk(name);

        var control = wp.customize.control('color_palette');
        var theNewRow = control.addRow({
            name: name,
            label: name,
            value: color
        });
        theNewRow.toggleMinimize();
        control.initColorPicker();

        if (defaultColors[tinycolor(color).toHex()]) {
            return defaultColors[tinycolor(color).toHex()]
        }


        return name;
    };

    CP_Customizer.getColorName = function (color) {
        var colors = CP_Customizer.getColorsObj();
        var parsedColor = tinycolor(color);

        for (var c in colors) {
            var _temp = tinycolor(colors[c]);
            if (parsedColor.toHex() === _temp.toHex()) { // parsed colors by tinycolor will ensure the same Hex if the colors are equal
                return c;
            }
        }

        if (parsedColor.toHex() === tinycolor('#000000').toHex()) {
            return 'color-black';
        }

        if (parsedColor.toHex() === tinycolor('#ffffff').toHex()) {
            return 'color-white';
        }

        if (parsedColor.toHex() === tinycolor('#bdbdbd').toHex()) {
            return 'color-gray';
        }

        if (tinycolor(color).getAlpha() === 0) {
            return "transparent";
        }

        return "";
    };

    CP_Customizer.getPaletteColors = function (json, includeTransparent, extras) {
        var colors = CP_Customizer.getColorsObj(includeTransparent);

        if (_.isObject(extras)) {
            colors = $.extend({}, colors, extras);
        }

        if (!json) return _.values(colors);
        return JSON.stringify(_.values(colors));
    };

    $(document).ready(function () {
        _.delay(function () {
            var control = wp.customize.control('color_palette');
            control.container.off('click', 'button.repeater-add');
            control.container.on('click', 'button.repeater-add', function (e) {
                e.preventDefault();
                CP_Customizer.createColor('#ffffff', undefined, true);
            });

            control.container.find('.repeater-add').html('Add theme color');

            control.container.find("[data-field=name][value=color1], [data-field=name][value=color2], [data-field=name][value=color3], [data-field=name][value=color4], [data-field=name][value=color5]").each(function () {
                $(this).parents(".repeater-row").find(".repeater-row-remove").hide();
            });
        }, 1000);
    })


    CP_Customizer.hooks.addFilter('spectrum_color_palette', function (colors) {
        var siteColors = jQuery.map(CP_Customizer.getColorsObj(), function (value, index) {
            return value;
        });

        return siteColors;

    });
})(window, CP_Customizer, jQuery);
