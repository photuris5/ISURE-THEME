(function ($) {
    wp.customize.controlConstructor['web-fonts'] = wp.customize.Control.extend({
        getWebFonts: function () {
            var result = _.toArray(this.webFonts).slice();
            result.toString = function () {
                var string = [],
                    googleFontsURL = "https://fonts.googleapis.com/css?family=";
                _(this).each(function (font) {
                    var fontString = font.family.split(' ').join('+');
                    if (font.weights.length) {
                        fontString += ":" + font.weights.join(',').replace(/\s/g, "");
                    }
                    string.push(fontString);
                });
                return googleFontsURL + string.join("|");
            }
            return result;
        },

        createPreviewFrame: function () {

            this.iframe = $(this.container.find('#cp-webfonts-list-iframe')[0].contentWindow.document);

            var styleSheet = CP_Customizer.options('proStylesheet') || CP_Customizer.options('stylesheetURL')

            this.iframe.find('head').prepend('<link rel="stylesheet" data-name="fonts" type="text/css"  href="' + this.getWebFonts() + '">');
            this.iframe.find('head').append('<link rel="stylesheet" id="wfcontrolstyle" type="text/css" href="' + styleSheet + '/customizer/assets/css/web-fonts-control.css">');
            this.iframe.find('head').append('<link rel="stylesheet" type="text/css" href="' + CP_Customizer.options('includesURL') + 'css/buttons.css">');
            this.iframe.find('head').append('<link rel="stylesheet" type="text/css" href="' + CP_Customizer.options('includesURL') + 'css/dashicons.css">');

            var self = this;
            this.iframe.find('body').addClass('wp-core-ui');
            this.iframe.find('body').append('<a class="add button">' + window.CP_Customizer.translateCompanionString("Add WebFont") + '</a>');
            this.iframe.find('body').on('click', '.add', function (event) {
                event.preventDefault();
                top.CP_Customizer.openWebFontsDialog(self.webFonts, false, function (newFont) {
                    self.addWebFont(newFont);
                    self.renderFonts();
                })
            });


            this.iframe.find('#wfcontrolstyle').on('load', function () {
                self.renderFonts();
            });

        },

        renderFonts: function () {
            var self = this;
            var fonts = this.getWebFonts();
            this.iframe.find('head link[data-name="fonts"]').attr('href', fonts.toString());
            this.iframe.find('body .wf-container').remove();
            _(fonts).each(function (font) {
                var fontName = font.family;
                var weights = font.weights;
                var wightesHTML = weights.map(function (v) {
                    if (v === "regular") {
                        v = "normal";
                    } else {
                        if (v === "italic") {
                            v = "400italic";
                        }
                        if (!v.match(/\d+/)) {
                            return "";
                        }
                    }
                    return '<div class="wf-weight" data-name="' + v + '" style="font-family:' + fontName + ',sans-serif;font-weight:' + v + '">' + v + '</div>'
                }).join("");
                var cbId = btoa(fontName).toLowerCase();
                var $view = $(
                    '    <div class="wf-container"style="font-family:' + fontName + ',sans-serif">' +
                    '        <label  for="' + cbId + '"  class="wf-header">' +
                    '            <div class="wf-name-holder">' + fontName + '</div>' +
                    '            <i class="dashicons repeater-minimize dashicons-arrow-down"></i>' +
                    '        </label>' +
                    '        <input type="checkbox" style="display: none" id="' + cbId + '" />' +
                    '        <div class="wf-content">' +
                    '           <p style="font-family:' + fontName + ',sans-serif">' +
                    window.CP_Customizer.translateCompanionString("The quick brown fox jumps over the lazy dog") +
                    '</p>' +
                    '           <div class="wf-footer">' +
                    '               ' + wightesHTML +
                    '           </div>' +
                    '          <div class="wf-actions">' +
                    '              <div data-name="' + fontName + '" class="wf-edit button">' + window.CP_Customizer.translateCompanionString("Edit") + '</div>' +
                    '              <div data-name="' + fontName + '" class="wf-remove  button"></g>' + window.CP_Customizer.translateCompanionString("Remove") + '</div>' +
                    '          </div>' +
                    '       </div>' +
                    '    </div>'
                );
                $view.on('click', '.wf-edit', function (event) {
                    event.preventDefault();
                    //debugger;
                    top.CP_Customizer.openWebFontsDialog(fonts, font, function (font) {
                        self.updateWebFont(font);
                        self.renderFonts();
                    });
                });

                $view.on('click', '.wf-weight', function (event) {
                    event.preventDefault();
                    var value = $(this).attr('data-name');
                    var weight = value.match(/\d+/) || 400;
                    var style = value.replace(weight, '');
                    $view.find('p').css({
                        'font-weight': weight,
                        'font-style': style
                    });
                });
                $view.on('click', '.wf-remove', function (event) {
                    event.preventDefault();
                    self.removeWebFont($(this).attr('data-name'));
                    self.renderFonts();
                });

                $view.on('change', 'input[type=checkbox]', function () {
                    if (this.checked) {
                        $(this).prev('label').find('i').attr('class', 'dashicons repeater-minimize dashicons-arrow-up');
                        $(this).next('.wf-content').show();
                    } else {
                        $(this).prev('label').find('i').attr('class', 'dashicons repeater-minimize dashicons-arrow-down');
                        $(this).next('.wf-content').hide();
                    }

                    self.container.find('#cp-webfonts-list-iframe').css("height", "");
                    self.container.find('#cp-webfonts-list-iframe').css("height", (parseInt(self.iframe.find('html')[0].scrollHeight + 10) + "px"));

                });

                self.iframe.find('body').prepend($view);
            });

            self.container.find('#cp-webfonts-list-iframe').css("height", "");
            self.container.find('#cp-webfonts-list-iframe').css("height", (parseInt(self.iframe.find('html')[0].scrollHeight + 10) + "px"));

        },

        removeWebFont: function (name) {
            var font = _.findWhere(this.webFonts, {"family": name});
            this.webFonts = _.without(this.webFonts, font);
            this.updateSetting();
        },

        addWebFont: function (font) {
            this.webFonts.push(font);
            this.updateSetting();
        },

        updateWebFont: function (font) {
            this.updateSetting();
        },

        updateSetting: function (silent) {
            var oldTransport = this.setting.transport;

            if (silent) {
                this.setting.transport = 'postMessage';
            }

            this.setting.set(JSON.stringify(this.webFonts));

            this.setting.transport = oldTransport;

            this.updateKirki();
        },

        updateKirki: function () {

            var standard = _.filter(this.kirkiAllFonts, function (font) {
                if (font.is_standard) return true;
            });

            kirkiAllFonts.splice(0);

            for (var i = 0; i < this.webFonts.length; i++) {
                var font = this.webFonts[i];
                var variants = _.map(font.weights, function (id) {
                    return {
                        "id": id,
                        "label": kirkiAllVariantsLabels[id] || id
                    }
                }).sort(function (weightA, weightB) {

                    // sort webfonts variants ASC

                    var a = weightA.id;
                    var b = weightB.id;

                    // normalize value
                    a = (a == 'regular' || a == 'normal') ? '400' : a;
                    b = (b == 'regular' || b == 'normal') ? '400' : b;

                    var intA = parseInt(a);
                    var intB = parseInt(b);

                    if (intA === intB) {
                        return a.length - b.length
                    }

                    return intA - intB;

                });
                kirkiAllFonts.push({family: font.family, label: font.family, subsets: [], variants: variants});
            }

            for (var i = 0; i < standard.length; i++) {
                kirkiAllFonts.push(standard[i]);
            }

            $('.selectized').each(function () {
                if ($(this).parents(".customize-control-kirki-typography .font-family").length) {
                    var self = this;
                    $.each(this.selectize.options, function (key, value) {
                        delete self.selectize.options[key];
                    });
                    this.selectize.addOption(kirkiAllFonts);
                }
            })
        },

        ready: function () {
            'use strict';
            var control = this;


            this.kirkiAllFonts = _.clone(kirkiAllFonts);

            //debugger;
            var val = control.setting.get();
            this.webFonts = _.isString(val) ? JSON.parse(val) : val;


            var iframe = this.container.find('#cp-webfonts-list-iframe');

            iframe[0].onload = function () {
                control.createPreviewFrame();
            }
            iframe.attr("srcdoc", "");
            this.updateKirki();

            if (CP_Customizer) {
                var self = this;
                CP_Customizer.bind(CP_Customizer.events.PREVIEW_LOADED, function () {
                    self.webFonts = CP_Customizer.preview.data('allFonts', self.webFonts);
                    window.kirkiAllFonts = [];
                    self.updateKirki();
                    self.renderFonts();
                    self.updateSetting(true);
                });
            }
        }
    });

})(jQuery);
