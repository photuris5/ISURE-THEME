(function (root, CP_Customizer, $) {

    // this should be moved in globalConfigs in the future
    var buttonSizes = {
        "normal": "",
        "small": "small",
        "big": "big"
    };

    var buttonColors = {};

    var oldColors = {
        "transparent": "transparent",
        "blue": "color1",
        "green": "color2",
        "yellow": "color3",
        "orange": "color5",
        "purple": "color4"
    };

    var buttonTextColors = {
        "default": "",
        "white text": "white-text",
        "dark text": "dark-text"
    };

    var buttonShadow = {
        "No shadow": "no-shadow",
        "Small shadow": "mdc-elevation--z3",
        "Medium shadow": "mdc-elevation--z6",
        "Large shadow": "mdc-elevation--z9",
        "Extra large shadow": "mdc-elevation--z12",
    };

    function buttonColorsList() {
        var buttonColors = {
            "default": "",
            "transparent ( link button )": "transparent"
        };

        var colors = CP_Customizer.getColorsObj(true);
        _.each(colors, function (color, name) {
            buttonColors[name] = name;
        });

        return buttonColors;
    }

    var buttonPresets = {
        "square round": "",
        "link": "link",
        "square": "square",
        "round": "round",
        "square round outline": "outline",
        "square outline": "square outline",
        "round outline": "round outline"
    };


    var currentColorRegexp;

    var oldCurrentColorRegexp = new RegExp(jQuery.map(oldColors, function (value, index) {
        return index
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    var currentTextColorRegexp = new RegExp(jQuery.map(buttonTextColors, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    var currentShadowRegexp = new RegExp(jQuery.map(buttonShadow, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');


    var __sizesText = jQuery.map(buttonSizes, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|");
    var buttonSizesRegexp = new RegExp("\\s(" + __sizesText + ')', 'ig');

    var curentPresetRegexp = new RegExp(jQuery.map(buttonPresets, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    // link with images
    CP_Customizer.hooks.addFilter('container_data_element', function (result, $elem) {
        var _class = CP_Customizer.preview.cleanNode($elem.clone()).attr('class') || "";

        buttonColors = buttonColorsList();
        currentColorRegexp = /(transparent|white|black|(color\d+))/ig;

        if ($elem.is('a') && _class && ($elem.is('.button') || $elem.is('[class*=button]'))) {
            var color = _class.match(currentColorRegexp) ? _class.match(currentColorRegexp)[0] : "";
            if (!color) {
                color = _class.match(oldCurrentColorRegexp) ? _class.match(oldCurrentColorRegexp)[0] : "";
            }
            var size = _class.match(buttonSizesRegexp) ? _class.match(buttonSizesRegexp)[0] : "";
            var textColor = _class.match(currentTextColorRegexp) ? _class.match(currentTextColorRegexp)[0] : "";
            var shadow = _class.match(currentShadowRegexp) ? _class.match(currentShadowRegexp)[0] : "";
            var preset = _class.match(curentPresetRegexp) ? _class.match(curentPresetRegexp)[0] : "";

            if (_class.match(curentPresetRegexp)) {
                if (_class.match(curentPresetRegexp).length == 1) preset = _class.match(curentPresetRegexp);
                else preset = _class.match(curentPresetRegexp)[0] + ' ' + _class.match(curentPresetRegexp)[1];
            }
            else var preset = "";

            if (oldColors[color]) {
                color = oldColors[color]
            }

            color = CP_Customizer.getColorValue(color);

            if (!$elem.is('.button')) {
                $elem.addClass('button');
            }

            if ($elem.is('.button')) {
                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Size"),
                    "type": "select",
                    "choices": buttonSizes,
                    "name": "button_size_option",
                    "classes": "button-pro-option",
                    "value": size.trim()
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Shadow"),
                    "type": "select",
                    "choices": buttonShadow,
                    "name": "button_shadow_option",
                    "classes": "button-pro-option",
                    "value": shadow
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Color"),
                    "type": "colorselect-transparent",
                    // "choices": buttonColors,
                    "name": "button_color_option",
                    "classes": "button-pro-option",
                    "value": color,
                    "getValue": CP_Customizer.utils.getSpectrumColorFormated
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Text Color"),
                    "type": "select",
                    "choices": buttonTextColors,
                    "name": "button_text_color_option",
                    "classes": "button-pro-option",
                    "value": textColor
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Preset"),
                    "type": "select",
                    "choices": buttonPresets,
                    "name": "button_preset_option",
                    "classes": "button-pro-option",
                    "value": preset
                });
            }

            var icon = $elem.attr('data-icon') || "";

            if (!icon) {
                if ($elem.find('span.button-icon').length) {
                    var match = $elem.find('span.button-icon').attr('class').match(/mdi\-[a-z\-]+/ig);
                    icon = ((match || []).pop()) || "";
                }
            }

            result.push({
                'label': window.CP_Customizer.translateCompanionString("Button Icon"),
                "type": "icon",
                "choices": buttonColors,
                "name": "button_icon_option",
                "canHide": true,
                value: {
                    icon: icon,
                    visible: ($elem.find('span.button-icon').length > 0)
                },
                "mediaType": "icon",
                mediaData: false
            });

            result = CP_Customizer.hooks.applyFilters('button_settings_controls', result, $elem);
        }

        return result;
    });

    CP_Customizer.hooks.addAction('container_data_element_setter', function (node, value, field) {

        if (field.name) {
            var _class = node.attr('class');
            var match = false;
            switch (field.name) {
                case "button_size_option":
                    _class = _class.replace(buttonSizesRegexp, " ");
                    match = true;
                    break;

                case "button_text_color_option":
                    _class = _class.replace(currentTextColorRegexp, " ");
                    match = true;
                    break;

                case "button_shadow_option":
                    match = true;
                    _class = _class.replace(currentShadowRegexp, " ");
                    break;

                case "button_color_option":
                    var aux = _class.split(" ");
                    var aux2 = [];
                    for (var i = 0, len = aux.length; i < len; i++) {
                        if (aux[i].search(currentColorRegexp) == -1 && aux[i].search(oldCurrentColorRegexp) == -1) aux2.push(aux[i]);
                    }
                    _class = aux2.join(" ");
                    match = true;

                    value = CP_Customizer.getThemeColor(value, function (value) {
                        match = false;
                        _class = _class.replace(/\s\s+/, " ").trim();
                        _class += " " + value;
                        node.attr('class', _class.trim());
                        CP_Customizer.updateState(true);
                    });
                    break;

                case "button_preset_option":
                    var aux = _class.split(" ");
                    var aux2 = [];
                    for (var i = 0, len = aux.length; i < len; i++) {
                        if (aux[i].search(curentPresetRegexp) == -1) aux2.push(aux[i]);
                    }

                    _class = aux2.join(" ");
                    match = true;
                    break;

            }

            if (match) {
                _class = _class.replace(/\s\s+/, " ").trim();
                _class += " " + value;

                node.attr('class', _class.trim());
            }

        }

        if (field.name === "button_icon_option") {
            node.attr('data-icon', value.icon);
            node.find('span.mdi').remove();

            if (value.visible) {
                node.prepend('<span class="button-icon mdi ' + value.icon + '"></span>');
                root.CP_Customizer.preview.markNode(node);
            }
        }

        CP_Customizer.hooks.doAction('button_settings_updated', node, value, field);
    });


    CP_Customizer.hooks.addFilter('temp_attr_mod_value', function (value, attr, $el) {

        var _class = ($el.attr('class') || "");

        if (attr === "temp-size") {
            value = _class.match(buttonSizesRegexp) ? _class.match(buttonSizesRegexp)[0] : "";
        }

        if (attr === "temp-color") {
            value = _class.match(currentColorRegexp) ? _class.match(currentColorRegexp)[0] : "";
        }

        return value;
    });


})(window, CP_Customizer, jQuery);
