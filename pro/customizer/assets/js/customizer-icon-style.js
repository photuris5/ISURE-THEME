(function (root, CP_Customizer, $) {

    CP_Customizer.themeColors = function (useWhiteBlack) {
        var buttonColors = {
            "default": "",
        };

        var colors = CP_Customizer.getColorsObj();
        _.each(colors, function (color, name) {
            buttonColors[name] = name;
        });

        if (useWhiteBlack) {
            buttonColors['color-white'] = 'color-white';
            buttonColors['color-black'] = 'color-black';
            buttonColors['color-gray'] = 'color-gray';
        }

        return buttonColors;
    };

    var currentColorRegexp;


    CP_Customizer.hooks.addFilter('container_data_element', function (result, $elem) {
        var _class = CP_Customizer.preview.cleanNode($elem.clone()).attr('class') || "";

        var colors = CP_Customizer.themeColors(true);
        var colorsList = Object.getOwnPropertyNames(colors);
        currentColorRegexp = /(color\d+)/ig;

        if ($elem.is('i.mdi') && !$elem.parent().is('.read-more')) {

            var color = "";

            for (var i = 0; i < colorsList.length; i++) {
                if ($elem.is('.' + colorsList[i])) {
                    color = colorsList[i];
                }
            }

            result.push({
                label: window.CP_Customizer.translateCompanionString("Icon Color"),
                type: "colorselect",
                choices: colors,
                "name": "icon_color_option",
                "value": CP_Customizer.getColorValue(color, $elem)
            });
        }

        return result;
    });

    CP_Customizer.hooks.addAction('container_data_element_setter', function (node, value, field) {
        if (field.name) {
            var _class = node.attr('class');
            var match = false;
            var colors = CP_Customizer.themeColors(true);
            var colorsList = Object.getOwnPropertyNames(colors);

            switch (field.name) {
                case "icon_color_option":
                    match = true;
                    // _class = _class.replace(currentColorRegexp, " ");

                    value = CP_Customizer.getThemeColor(value, function (value) {
                        match = false;
                        node.removeClass(colorsList.join(' '));
                        node.addClass(value);

                        CP_Customizer.updateState(true);
                    });

                    break;
            }

            if (match) {
                node.removeClass(colorsList.join(' '));
                node.addClass(value);
                CP_Customizer.updateState();
            }

        }
    });

})(window, CP_Customizer, jQuery);
