(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {
        var recompile = _.debounce(CP_Customizer.contentStyle.recompileScssStyle, 200);
        var settings = [
            "color_palette",
            "general_site_typography",
            "general_site_typography_size",
            "general_site_h1_typography",
            "general_site_h2_typography",
            "general_site_h3_typography",
            "general_site_h4_typography",
            "general_site_h5_typography",
            "general_site_h6_typography",

            "materialis_woocommerce_list_item_tablet_cols",
            "materialis_woocommerce_list_item_desktop_cols",
            "materialis_woocommerce_primary_color",
            "materialis_woocommerce_secondary_color",
            "materialis_woocommerce_onsale_color"
        ];


        CP_Customizer.bind(CP_Customizer.events.PREVIEW_LOADED, function () {

            var _settings = jQuery.each(CP_Customizer.wpApi.settings.controls, function (index, item) {
                if (item.choices && item.choices.scss_var) {
                    settings.push(index)
                }
            });

            settings = settings.concat(_settings);
            settings = _.uniq(settings);

            settings.forEach(function (settingID) {
                var setting = CP_Customizer.wpApi(settingID);

                if (setting) {
                    setting.bind(recompile);
                }
            });


        });


        var typographyControlVars = function (settingID, value) {

            var result = {};

            if (settingID === 'general_site_typography') {
                for (var param in value) {
                    result['typo_' + param.split('-').join('_')] = value[param];
                }

            }


            if (settingID.match(/general_site_h(\d+)_typography/)) {
                var hNumber = settingID.match(/general_site_h(\d+)_typography/).pop();
                var varName = 'typo_h' + hNumber + '_';
                for (var param in value) {
                    result[varName + param.split('-').join('_')] = value[param];
                }
            }

            return result;

        };

        var repeaterControlVars = function (settingID, value) {
            var result = {};
            switch (settingID) {
                case 'color_palette':
                    result = CP_Customizer.getColorsObj();
                    break;
            }


            return result;

        };

        CP_Customizer.hooks.addFilter('scss_setting_vars', function (result, settingID) {

            var setting = CP_Customizer.wpApi(settingID);
            var control = (setting && setting.findControls().length) ? setting.findControls()[0] : null;

            if (control) {
                var controlType = control.params.type;
                var value = CP_Customizer.utils.getValue(control);
                switch (controlType) {
                    case 'repeater':
                        result = repeaterControlVars(settingID, value);
                        break;

                    case 'kirki-typography':
                        result = typographyControlVars(settingID, value);
                        break;
                    case 'kirki-slider':

                        if (settingID === 'general_site_typography_size') {
                            result = {
                                'typo_font_size': value + 'px'
                            }
                        }
                        break;

                    default:
                        result = {};
                        result[settingID] = value;

                        if (settingID.indexOf('ope_') === 0) {
                            result[settingID.replace('ope_', '')] = value;
                        }

                }
            }

            return result;
        });

    });

})(window, CP_Customizer, jQuery);
