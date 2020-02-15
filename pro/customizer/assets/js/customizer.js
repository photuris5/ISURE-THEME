(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {

        CP_Customizer.IS_PRO = true;

        CP_Customizer.hooks.addFilter('tinymce_google_fonts', function (fonts) {
            var generalFont = CP_Customizer.wpApi('general_site_typography').get()['font-family'];

            fonts[generalFont] = generalFont + ",arial,helvetica,sans-serif";

            return fonts;

        });

        CP_Customizer.hooks.addAction('text_element_clicked', function ($node) {
            root.CP_Customizer.preview.showTextElementCUI($node);
        });


        // video link popup
        CP_Customizer.hooks.addFilter('container_data_element', function (result, $elem) {

            if (!$elem.is('i.mdi')) {
                return result;
            }

            if (!$elem.parent().is('a')) {
                return result;
            }

            if ($elem.parent().is('[data-video-lightbox]')) {
                result[0].label = window.CP_Customizer.translateCompanionString("Video Popup Button");
                result[0].canHide = false;
                result[0].value.target = false;
            }

            return result;
        });

        CP_Customizer.hooks.addFilter('decorable_elements_containers', function (selectors) {

            selectors.push('.header-homepage  .header-content');

            return selectors;
        });


        CP_Customizer.hooks.addFilter('filter_cog_item_section_element', function (section, node) {
            if (node.parent().is('[data-theme]')) {
                section = node;
            }

            return section;
        });


        jQuery('body').on('change', '[data-customize-setting-link="header_show_text_morph_animation"]', function () {
            var value = this.checked;

            var addCurlyBraces = function () {
                if (value) {
                    var $title = CP_Customizer.preview.jQuery('.header-content h1');
                    var curlyRegexp = /[\{|\}]/;

                    if ($title.length && !curlyRegexp.test($title.html())) {
                        var lastChild = $title[0].childNodes.item($title[0].childNodes.length - 1);
                        var text = lastChild.textContent;
                        text = text.replace(/(\w+?)$/, '{$1}');
                        lastChild.textContent = text;

                        CP_Customizer.updateState();
                    }
                }

                CP_Customizer.off(CP_Customizer.events.PREVIEW_LOADED + '.add_curly_braces'/*, addCurlyBraces*/);
            };

            CP_Customizer.on(CP_Customizer.events.PREVIEW_LOADED + '.add_curly_braces', addCurlyBraces);

        });

        // clean content style when section is removed
        CP_Customizer.hooks.addAction('before_section_remove', function ($section) {
            var selector = "^\\[data\\-id=('|\")?" + $section.attr('data-id') + '(\'|")?\\]';

            var selectorRegExp = new RegExp(selector);

            CP_Customizer.contentStyle.removeSelector(selectorRegExp, "all");
        });

    });

    var innerHeaderInehritSettingBinded = false;

    wp.customize.bind('pane-contents-reflowed', function () {

        if (innerHeaderInehritSettingBinded) {
            return;
        }

        innerHeaderInehritSettingBinded = true;
        window.wp.customize('inner_header_nav_use_front_page').bind(function (value) {
            var settings = Object.getOwnPropertyNames(wp.customize.settings.settings);

            settings.forEach(function (setting) {
                if (setting.indexOf('header_nav') === 0) {
                    var innerSetting = wp.customize('inner_' + setting);
                    if (innerSetting) {
                        var oldTransport = innerSetting.transport;
                        var value = wp.customize(setting).get();

                        innerSetting.transport = 'postMessage';
                        innerSetting.set(value);

                        innerSetting.transport = oldTransport;
                    }
                }
            });

        });
    });


})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {

    var colorOptions = [
        {
            "setting": "_nav_menu_color",
            "var": "color"
        },
        {
            "setting": "_nav_menu_hover_color",
            "var": "hover_color"
        },
        {
            "setting": "_nav_menu_hover_highlight_color",
            "var": "hover_highlight_color"
        },
        {
            "setting": "_nav_menu_active_color",
            "var": "active_color"
        }, {
            "setting": "_nav_menu_active_highlight_color",
            "var": "active_highlight_color"
        }, {
            "setting": "_nav_fixed_menu_color",
            "var": "fixed_color"
        },
        {
            "setting": "_nav_fixed_menu_hover_color",
            "var": "fixed_hover_color"
        },
        {
            "setting": "_nav_fixed_menu_hover_highlight_color",
            "var": "fixed_hover_highlight_color"
        },
        {
            "setting": "_nav_fixed_menu_active_color",
            "var": "fixed_active_color"
        }, {
            "setting": "_nav_fixed_menu_active_highlight_color",
            "var": "fixed_active_highlight_color"
        }, {
            "setting": "_nav_submenu_background_color",
            "var": "submenu_bg"
        }, {
            "setting": "_nav_submenu_text_color",
            "var": "submenu_color"
        }, {
            "setting": "_nav_submenu_hover_background_color",
            "var": "submenu_hover_bg"
        }, {
            "setting": "_nav_submenu_hover_text_color",
            "var": "submenu_hover_color"
        }
    ];

    var menuColorsComplexity = {
        "simple-text-buttons": "simple",
        "bordered-active-item": "simple",
        "material-buttons": "highlighted",
        "solid-active-item": "highlighted"
    };

    function __onNavStyleChange(id, value, oldValue) {

        var prefix = id === 'inner_header_nav_style' ? 'inner_header' : 'header';

        if (menuColorsComplexity[value] !== menuColorsComplexity[oldValue]) {
            var colors = CP_Customizer.preview.data('menu_colors_variation', {})[menuColorsComplexity[value]];
            colorOptions.forEach(function (item) {

                if (item.setting.indexOf('submenu') === -1) {

                    var colorVar = item.var;

                    if (!CP_Customizer.wpApi(prefix + '_nav_transparent').get()) {
                        if (item.setting.indexOf('_nav_fixed_') !== 0 && item.setting.indexOf('_nav_submenu_') !== 0) {
                            colorVar = 'fixed_' + item.var;
                        }
                    }

                    CP_Customizer.setMod(prefix + item.setting, colors[colorVar]);
                }
            });
        }
    }

    var onNavStyleChange = _.debounce(__onNavStyleChange, 50);

    wp.customize.bind('pane-contents-reflowed', function () {


        var header_old_value = CP_Customizer.wpApi('header_nav_style').get();
        CP_Customizer.wpApi('header_nav_style').findControls()[0].container.find('select').on('change', function () {
            onNavStyleChange('header_nav_style', CP_Customizer.wpApi('header_nav_style').get(), header_old_value);
            header_old_value = CP_Customizer.wpApi('header_nav_style').get();
        });

        var inner_header_old_value = CP_Customizer.wpApi('inner_header_nav_style').get();
        CP_Customizer.wpApi('inner_header_nav_style').findControls()[0].container.find('select').on('change', function () {
            onNavStyleChange('inner_header_nav_style', CP_Customizer.wpApi('inner_header_nav_style').get(), inner_header_old_value);
            inner_header_old_value = CP_Customizer.wpApi('inner_header_nav_style').get();
        });


        // var header_old_value = CP_Customizer.wpApi('header_nav_style').get();
        CP_Customizer.wpApi('header_nav_transparent').findControls()[0].container.find('input[type="checkbox"]').on('change', function () {
            var complexity = menuColorsComplexity[CP_Customizer.wpApi('header_nav_style').get()];
            var colors = CP_Customizer.preview.data('menu_colors_variation', {})[complexity];
            var prefix = 'header';

            if (!this.checked) {
                colorOptions.forEach(function (item) {
                    if (item.setting.indexOf('_nav_fixed_') !== 0 && item.setting.indexOf('_nav_submenu_') !== 0) {
                        CP_Customizer.setMod(prefix + item.setting, colors['fixed_' + item.var]);
                    }
                });

                CP_Customizer.setMod(prefix + '_header_text_logo_color', '#000000');
            } else {
                colorOptions.forEach(function (item) {
                    if (item.setting.indexOf('_nav_fixed_') !== 0 && item.setting.indexOf('_nav_submenu_') !== 0) {
                        CP_Customizer.setMod(prefix + item.setting, colors[item.var]);
                    }
                });
                CP_Customizer.setMod(prefix + '_header_text_logo_color', '#ffffff');
            }
        });

        CP_Customizer.wpApi('inner_header_nav_transparent').findControls()[0].container.find('input[type="checkbox"]').on('change', function () {
            var complexity = menuColorsComplexity[CP_Customizer.wpApi('inner_header_nav_style').get()];
            var colors = CP_Customizer.preview.data('menu_colors_variation', {})[complexity];
            var prefix = 'inner_header';

            if (!this.checked) {
                colorOptions.forEach(function (item) {
                    if (item.setting.indexOf('_nav_fixed_') !== 0 && item.setting.indexOf('_nav_submenu_') !== 0) {
                        CP_Customizer.setMod(prefix + item.setting, colors['fixed_' + item.var]);
                    }
                });

                CP_Customizer.setMod(prefix + '_header_text_logo_color', '#000000');
            } else {
                colorOptions.forEach(function (item) {
                    if (item.setting.indexOf('_nav_fixed_') !== 0 && item.setting.indexOf('_nav_submenu_') !== 0) {
                        CP_Customizer.setMod(prefix + item.setting, colors[item.var]);
                    }
                });

                CP_Customizer.setMod(prefix + '_header_text_logo_color', '#ffffff');
            }
        });

    });

})(window, CP_Customizer, jQuery);

// cols-separator-between
(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {
        CP_Customizer.panels.sectionPanel.extendArea('text-options', function (area) {
            var areaInit = area.init;
            var areaUpdate = area.update;

            area = _.extend(area, {
                init: function ($container) {

                    areaInit.call(this, $container);

                    var color = CP_Customizer.createControl.color(
                        this.getPrefixed('items-separator-color'),
                        $container, {
                            value: '#ffffff',
                            label: window.CP_Customizer.translateCompanionString('Items separator color')
                        });

                    this.addToControlsList(color);

                },

                update: function (data) {

                    areaUpdate.call(this, data);

                    var colorControl = this.getControl('items-separator-color'),
                        section = data.section,
                        separatorHolder = section.find('.cols-separator-between,.cols-border-between');

                    if (separatorHolder.length === 0) {
                        colorControl.hide();
                    } else {
                        colorControl.show();

                        var color,
                            selector,
                            pseudo = null,
                            prop;

                        if (separatorHolder.is('.cols-separator-between')) {
                            pseudo = ':after';
                            prop = "background";
                            selector = CP_Customizer.preview.getNodeAbsSelector(separatorHolder.children(), ' .cols-separator-between > div');
                            color = CP_Customizer.contentStyle.getNodeProp(separatorHolder.children(), selector, pseudo, prop);
                        } else {
                            prop = "border-color";
                            selector = CP_Customizer.preview.getNodeAbsSelector(separatorHolder.children(), ' .cols-border-between > div');
                            color = CP_Customizer.contentStyle.getNodeProp(separatorHolder.children(), selector, pseudo, prop);
                        }

                        colorControl.attachWithSetter(color, function (value) {
                            CP_Customizer.contentStyle.setProp(selector, pseudo, prop, value);
                        });
                    }


                }
            });

            return area;
        });
    });
})(window, CP_Customizer, jQuery);
