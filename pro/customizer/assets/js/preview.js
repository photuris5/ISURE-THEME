(function ($) {
    liveUpdateAutoSetting('pro_background_overlay', function (value) {
        var style = jQuery.map(value, function (color, id) {
            return '[data-ovid="' + id + '"]:before { background-color: ' + color + ' !important; }'
        }).join("\n");
        jQuery('style[data-for=pro_background_overlay]').html(style);
    });
    liveUpdate('header_nav_title_typography', function (value) {
        jQuery('#main_menu>li:hover>a, #main_menu>li.hover>a, #main_menu>li.current_page_item>a').css({
            'text-shadow': "0px 0px 0px " + value.color
        });
        jQuery('#main_menu>li:hover>a, #main_menu>li.hover>a, #main_menu>li.current_page_item>a').css({
            'border-bottom-color': value.color
        });
        jQuery('.header-top.homepage.bordered').css({
            'border-bottom-color': value.color
        });
    });
    liveUpdate('header_content_vertical_align', function (value) {
        var header = jQuery('.header-homepage');
        header.removeClass('v-align-top');
        header.removeClass('v-align-middle');
        header.removeClass('v-align-bottom');
        header.addClass(value);
    });
    liveUpdate('header_text_align', function (value) {
        var header = jQuery('.header-content:not(.header-content-centered, .header-content-left, .header-content-right)');
        if (header.length) {
            header.removeClass('container-align-center');
            header.removeClass('container-align-left');
            header.removeClass('container-align-right');
            header.addClass('container-align-' + value);
        }
    });
    liveUpdate('full_height', function (value) {
        var contentVerticalControl = parent.wp.customize.control('header_content_vertical_align');
        contentVerticalControl.active(value);
    });
    liveUpdate('header_content_image_rounded', function (value) {
        if (value) {
            $('.homepage-header-image').addClass('round');
        } else {
            $('.homepage-header-image').removeClass('round');
        }
    });
})(jQuery);
(function ($) {
    wp.customize('footer_show_overlay', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.footer .footer-content').addClass('color-overlay');
            } else {
                $('.footer .footer-content').removeClass('color-overlay');
            }
        });
    });

    var prefix = $('body').hasClass('materialis-front-page') ? 'header' : 'inner_header';

    function getHeaderSplitGradientValue(color, angle, size, fade) {
        angle = -90 + parseInt(angle);
        fade = parseInt(fade) / 2;
        transparentMax = (100 - size) - fade;
        colorMin = (100 - size) + fade;
        var gradient = angle + "deg, " + "transparent 0%, transparent " + transparentMax + "%, " + color + " " + colorMin + "%, " + color + " 100%";
        // return gradient;
        var result = 'background: linear-gradient(' + gradient + ');' + 'background: -webkit-linear-gradient(' + gradient + ');' + 'background: linear-gradient(' + gradient + ');';
        return result;
    }

    function recalculateHeaderSplitGradient() {
        var color = wp.customize(prefix + '_split_header_color').get();
        var angle = wp.customize(prefix + '_split_header_angle').get();
        var fade = wp.customize(prefix + '_split_header_fade') ? wp.customize('split_header_fade').get() : 0;
        var size = wp.customize(prefix + '_split_header_size').get();
        var gradient = getHeaderSplitGradientValue(color, angle, size, fade);
        var angle = wp.customize(prefix + '_split_header_angle_mobile').get();
        var size = wp.customize(prefix + '_split_header_size_mobile').get();
        var mobileGradient = getHeaderSplitGradientValue(color, angle, size, fade);
        var style = '';
        if (prefix === 'header') {
            style += '.header-homepage  .split-header {' + mobileGradient + '}' + "\n\n" + '@media screen and (min-width: 767px) { .header-homepage  .split-header {' + gradient + '} }';
        } else {
            style += '.materialis-inner-page  .split-header {' + mobileGradient + '}' + "\n\n" + '@media screen and (min-width: 767px) { .materialis-inner-page .split-header {' + gradient + '} }';
        }
        jQuery('style[data-name="header-split-style"]').html(style);
    }

    liveUpdate(prefix + '_split_header_fade', recalculateHeaderSplitGradient);
    liveUpdate(prefix + '_split_header_color', recalculateHeaderSplitGradient);
    liveUpdate(prefix + '_split_header_angle', recalculateHeaderSplitGradient);
    liveUpdate(prefix + '_split_header_size', recalculateHeaderSplitGradient);
    liveUpdate(prefix + '_split_header_angle_mobile', recalculateHeaderSplitGradient);
    liveUpdate(prefix + '_split_header_size_mobile', recalculateHeaderSplitGradient);


    liveUpdate('footer_content_copyright_text', function (value) {
        var footerOptions = parent.CP_Customizer.options('footerData', {
            year: (new Date()).getFullYear(),
            blogname: 'BlogName'
        });

        value = value.replace('{year}', footerOptions.year);
        value = value.replace('{blogname}', footerOptions.blogname);


        jQuery('[data-footer-copyright="true"]').html(value);
    });

    var widgets = [
        'footer_layout_widget_width_first_box_widgets',
        'footer_layout_widget_width_second_box_widgets',
        'footer_layout_widget_width_third_box_widgets',
        'footer_layout_widget_width_newsletter_subscriber_widgets'
    ];

    $.each(widgets, function (index, widget) {
        wp.customize(widget, function (value) {
            value.bind(function (newval) {
                var widgetName = widget.replace('footer_layout_widget_width_', '').trim();
                $('.footer.footer-4 .footer-content div[data-widget="' + widgetName + '"]').removeClass().addClass('col-sm-' + newval);
            });
        });
    });


    wp.customize('footer_top_border_thickness', function (value) {
        value.bind(function (newval) {

            var contentSelector = $('.page-content'),
                footerSelector = $('.footer.paralax'),
                footerContentSelector = $('.footer:not(.footer-dark) .footer-content, .footer-dark'),
                parallax = wp.customize('footer_paralax').get(),
                footerProperties = {
                    'border-top-width': newval + 'px',
                    'border-top-style': 'solid'
                };

            footerContentSelector.css(footerProperties);

            if (parallax) {
                var contentMarginBottom = parseInt(footerSelector.outerHeight() - 1);
                contentSelector.css('margin-bottom', contentMarginBottom + 'px');
            }

        });
    });


})(jQuery);
(function ($) {
    function getGradientValue(setting) {
        var control = parent.wp.customize.control(setting);
        var gradient = parent.CP_Customizer.utils.getValue(control);
        var colors = gradient.colors;
        var angle = gradient.angle;
        angle = parseFloat(angle);
        return parent.Materialis.Utils.getGradientString(colors, angle);
    }


    function recalculateFooterOverlayGradient() {
        var gradient = getGradientValue('footer_overlay_gradient_colors');
        $("<style>.footer .footer-content.color-overlay::before { background: " + gradient + "}</style>").appendTo("head");
    }

    liveUpdate('footer_overlay_gradient_colors', recalculateFooterOverlayGradient);

    // dark logo //

    wp.customize('inner_header_nav_use_dark_logo', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.navigation-bar:not(.homepage) .navigation-wrapper').addClass('dark-logo');
                $('.navigation-bar:not(.homepage) .navigation-wrapper').removeClass('white-logo');
            } else {
                $('.navigation-bar:not(.homepage) .navigation-wrapper').addClass('white-logo');
                $('.navigation-bar:not(.homepage) .navigation-wrapper').removeClass('dark-logo');
            }
        });
    });

    wp.customize('header_nav_use_dark_logo', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.navigation-bar.homepage .navigation-wrapper').addClass('dark-logo');
                $('.navigation-bar.homepage .navigation-wrapper').removeClass('white-logo');
            } else {
                $('.navigation-bar.homepage .navigation-wrapper').addClass('white-logo');
                $('.navigation-bar.homepage .navigation-wrapper').removeClass('dark-logo');
            }
        });
    });

    wp.customize('inner_header_nav_fixed_use_dark_logo', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.navigation-bar:not(.homepage) .navigation-wrapper').addClass('fixed-dark-logo');
                $('.navigation-bar:not(.homepage) .navigation-wrapper').removeClass('fixed-white-logo');
            } else {
                $('.navigation-bar:not(.homepage) .navigation-wrapper').addClass('fixed-white-logo');
                $('.navigation-bar:not(.homepage) .navigation-wrapper').removeClass('fixed-dark-logo');
            }
        });
    });

    wp.customize('header_nav_fixed_use_dark_logo', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.navigation-bar.homepage .navigation-wrapper').addClass('fixed-dark-logo');
                $('.navigation-bar.homepage .navigation-wrapper').removeClass('fixed-white-logo');
            } else {
                $('.navigation-bar.homepage .navigation-wrapper').addClass('fixed-white-logo');
                $('.navigation-bar.homepage .navigation-wrapper').removeClass('fixed-dark-logo');
            }
        });
    });
})(jQuery);


(function ($, CP_Customizer) {
    wp.customize('color_palette', function (value) {
        value.bind(function (newValue) {
            var $style = $('[data-name="site-colors"]').last(),
                value = CP_Customizer.utils.normalizeValue(newValue),
                colorTemplates = CP_Customizer.preview.data('colors_template'),
                styleOutput = "";

            value.forEach(function (color) {

                var data = {
                    color: color.value,
                    colorName: color.name,
                    colorClass: '.' + color.name,
                    hoverColor: CP_Customizer.utils.convertColor.brighten(color.value, 20)
                }

                if (color.name === "color1") {
                    styleOutput += CP_Customizer.jsTPL.compile(colorTemplates['color1'])({
                        data: data
                    });
                }

                if (color.name === "color2") {
                    styleOutput += CP_Customizer.jsTPL.compile(colorTemplates['color2'])({
                        data: data
                    });
                }

                styleOutput += CP_Customizer.jsTPL.compile(colorTemplates['general'])({
                    data: data
                });

            });

            $style.after("<style data-name=\"site-colors\">" + styleOutput + "</style>");
            $style.remove();

        });
    })
})(jQuery, top.CP_Customizer);


(function ($, CP_Customizer) {

    var footerSettings = ['footer_top_border_thickness', 'footer_spacing'];

    footerSettings.forEach(function (item) {
        wp.customize(item, function (v) {
            v.bind(MaterialisTheme.updateFooterParallax);
        })
    });

})(jQuery, top.CP_Customizer);
