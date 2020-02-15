(function ($) {

    if (!top.CP_Customizer) {
        return;
    }

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


    function updateStyle(prefix, parentSelector) {
        var $styleHolder = $('[data-name="menu-variant-style"][data-prefix=' + prefix + ']');

        if (!$styleHolder.length) {
            return;
        }

        var navStyle = wp.customize(prefix + '_nav_style').get();
        var style = window.__menu_preview_data.base;
        style += "\n\n" + window.__menu_preview_data.menu_vars[navStyle];
        style += "\n\n" + window.__menu_preview_data.submenu;

        for (var i = 0; i < colorOptions.length; i++) {
            var option = colorOptions[i];
            var placeholder = "\$dd_" + option.var;
            var currentPrefix = (option.var.indexOf('submenu') !== -1) ? "header" : prefix;
            var value = wp.customize(currentPrefix + option.setting).get();
            var toRGBPlaceholder = "toRgb(" + placeholder + ")";
            style = style.split(toRGBPlaceholder).join(top.CP_Customizer.utils.convertColor.toRGB(value));
            style = style.split(placeholder).join(value);
        }

        style = style.split('$dd_parent_selector').join(parentSelector);

        $styleHolder.html(style);

    }


    function updateMenuStyle(prefix, parentSelector) {

        var $menu = jQuery(parentSelector + ' .main-menu');

        if ($menu.length) {
            var classes = _.keys(top.CP_Customizer.wpApi.control(prefix + "_nav_style").params.choices);
            $menu.removeClass(classes.join(" "));
            $menu.addClass(top.CP_Customizer.wpApi(prefix + "_nav_style").get());


            var borderEffect = _.keys(top.CP_Customizer.wpApi.control(prefix + '_nav_border_effect').params.choices).map(function (_class) {
                return "effect-" + _class;
            }).join(" ");
            $menu.removeClass(borderEffect);


            var borderPositions = _.keys(top.CP_Customizer.wpApi.control(prefix + '_nav_border_style').params.choices).map(function (_class) {
                return "bordered-active-item--" + _class;
            }).join(" ");
            $menu.removeClass(borderPositions);

            var solidEffect = _.keys(top.CP_Customizer.wpApi.control(prefix + '_nav_solid_effect').params.choices).map(function (_class) {
                return "effect-" + _class;
            }).join(" ");

            $menu.removeClass(solidEffect);

            var singleGrow = _.keys(top.CP_Customizer.wpApi.control(prefix + '_nav_border_single_grow').params.choices).map(function (_class) {
                return "grow-from-" + _class;
            }).join(" ");

            $menu.removeClass(singleGrow);

            var doubleGrow = _.keys(top.CP_Customizer.wpApi.control(prefix + '_nav_border_double_grow').params.choices).map(function (_class) {
                return "grow-from-" + _class;
            }).join(" ");

            $menu.removeClass(doubleGrow);


            updateStyle(prefix, parentSelector);


            if (top.CP_Customizer.wpApi(prefix + "_nav_style").get() === "solid-active-item") {
                $menu.addClass("effect-" + top.CP_Customizer.wpApi(prefix + "_nav_solid_effect").get());
            }

            if (top.CP_Customizer.wpApi(prefix + "_nav_style").get() === "bordered-active-item") {
                $menu.addClass("bordered-active-item--" + top.CP_Customizer.wpApi(prefix + "_nav_border_style").get());
                $menu.addClass("effect-" + top.CP_Customizer.wpApi(prefix + "_nav_border_effect").get());

                if (top.CP_Customizer.wpApi(prefix + "_nav_border_effect").get() === "borders-grow") {
                    if (top.CP_Customizer.wpApi(prefix + "_nav_border_style").get() === "top-and-bottom") {
                        $menu.addClass("grow-from-" + top.CP_Customizer.wpApi(prefix + '_nav_border_double_grow').get());
                    } else {
                        $menu.addClass("grow-from-" + top.CP_Customizer.wpApi(prefix + '_nav_border_single_grow').get());
                    }
                }
            }
        }

    }



    function bindStyle(prefix, parentSelector) {

        var menuStyleControls = [
            '_nav_style',
            '_nav_border_effect',
            '_nav_solid_effect',
            '_nav_border_single_grow',
            '_nav_border_double_grow',
            '_nav_border_style'
        ];

        menuStyleControls.forEach(function (item) {
            liveUpdate(prefix + item, function () {
                updateMenuStyle(prefix, parentSelector);
            });
        });






        liveUpdate(prefix + '_nav_menu_items_align', function (value) {
            var $navbar = jQuery(parentSelector + ' .navigation-bar');
            $navbar.find('.main-menu, .main_menu_col').css("justify-content", value);

        });
    }

    function bindColors(prefix, parentSelector) {
        colorOptions.forEach(function (option) {
            liveUpdate(prefix + option.setting, function () {
                updateStyle(prefix, parentSelector);
            })
        });
    }

    function bindData(inner) {
        var prefix = inner ? "inner_header" : "header";
        var parentSelector = inner ? '.materialis-inner-page' : '.materialis-front-page';

        bindStyle(prefix, parentSelector);
        bindColors(prefix, parentSelector);
    }

    bindData(false);
    bindData(true);

})(jQuery);
