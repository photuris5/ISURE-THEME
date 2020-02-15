(function (root, CP_Customizer, $) {

    var separatorPosition = [];
    var controls = {
        top: {},
        bottom: {}
    };


    CP_Customizer.addModule(function (CP_Customizer) {

        separatorPosition = [
            {value: 'top', label:  CP_Customizer.translateCompanionString('top')},
            {value: 'bottom', label: CP_Customizer.translateCompanionString('bottom')}
        ];

        var sectionPanel = CP_Customizer.panels.sectionPanel;

        sectionPanel.registerArea('section_separators', {
            priority: 7,
            areaTitle: window.CP_Customizer.translateCompanionString('Section Separators'),

            defaultSeparatorTemplate: _.template('' +
                '<div class="section-separator-<%= position %>">\n' +
                '    <svg class="section-separator-<%= position %>" data-separator-name="triangle-asymmetrical-negative" preserveaspectratio="none" viewbox="0 0 1000 100" xmlns="http://www.w3.org/2000/svg">\n' +
                '        <path class="svg-white-bg" d="M737.9,94.7L0,0v100h1000V0L737.9,94.7z"></path>\n' +
                '    </svg>\n' +
                '</div>' +
                ''),

            cachedSeparators: {},

            toggleGroupVisibility: function (position, visible) {
                if (visible) {
                    controls[position].optionsGroup.show();
                } else {
                    controls[position].optionsGroup.hide();
                }
            },

            init: function ($container) {
                var self = this;

                _.each(separatorPosition, function (position) {
                    controls[position.value]['displayControl'] = CP_Customizer.createControl.checkbox(
                        self.getPrefixed('display-' + position.value),
                        $container,
                        window.CP_Customizer.translateCompanionString('Display') +
                        ' ' +
                        position.label +
                        ' ' +
                        window.CP_Customizer.translateCompanionString('separator')
                    );

                    controls[position.value]['optionsGroup'] = CP_Customizer.createControl.controlsGroup(
                        self.getPrefixed('separator-' + position.value + '-options-group'),
                        $container,
                        false
                    );

                    var $groupEl = controls[position.value]['optionsGroup'].el();

                    controls[position.value]['type'] = CP_Customizer.createControl.select(
                        self.getPrefixed('type-' + position.value),
                        $groupEl,
                        {
                            label: position.label + ' ' + window.CP_Customizer.translateCompanionString('separator type'),
                            choices: CP_Customizer.options('section_separators')
                        }
                    );


                    controls[position.value]['color'] = CP_Customizer.createControl.color(
                        self.getPrefixed('color-' + position.value),
                        $groupEl,
                        {
                            label: position.label + ' ' + window.CP_Customizer.translateCompanionString("separator color")
                        }
                    );

                    controls[position.value]['size'] = CP_Customizer.createControl.slider(
                        self.getPrefixed('size-' + position.value),
                        $groupEl,
                        {
                            label:  position.label + ' ' + window.CP_Customizer.translateCompanionString('separator Height'),
                            choice: {
                                min: 1,
                                max: 100,
                                step: 0.1
                            }

                        }
                    );

                });

            },

            update: function (data) {
                var $section = data.section,
                    self = this;
                _.each(separatorPosition, function (position) {
                    self.updatePosition($section, position);
                })
            },


            updatePosition: function ($section, position) {
                var self = this;
                var hasSeparator = ( $section.children('div.section-separator-' + position.value).length > 0);
                var separatorControls = controls[position.value];
                self.toggleGroupVisibility(position.value, hasSeparator);

                separatorControls.displayControl.attachWithSetter(hasSeparator, function (value) {
                    self.toggleGroupVisibility(position.value, value);

                    if (value) {
                        var separatorContent = self.defaultSeparatorTemplate({position: position.value});

                        if ($section.children('div.section-separator-' + position.value).length === 0) {
                            $section.addClass('content-relative');
                            if (position.value === 'top') {
                                CP_Customizer.preview.insertNode($(separatorContent), $section, 0);
                            } else {
                                CP_Customizer.preview.insertNode($(separatorContent), $section);
                            }
                        }

                        self.updatePositionControls($section, position);
                    } else {
                        $section.children('div.section-separator-' + position.value).remove();
                    }

                });

                self.updatePositionControls($section, position);
            },

            updatePositionControls: function ($section, position) {
                var separatorControls = controls[position.value];
                var separator = $section.children('div.section-separator-' + position.value);

                var selector = '[data-id=' + $section.attr('data-id') + '] div.section-separator-' + position.value;
                var pathSelector = selector + ' svg path';
                var self = this;

                separatorControls.type.attachWithSetter(
                    separator.find('svg').attr('data-separator-name'),
                    function (value) {
                        var url = CP_Customizer.options('themeURL') + "/assets/separators/" + value + ".svg";

                        if (!self.cachedSeparators[value]) {

                            CP_Customizer.IO.customGet(url).done(function (data, xhr) {
                                var svg = xhr.responseText;

                                var $svg = $(svg).attr('data-separator-name', value);
                                svg = $svg[0];

                                $svg.addClass('section-separator-' + position.value);

                                CP_Customizer.preview.replaceNode(separator.find('svg'), $svg);
                                self.cachedSeparators[value] = svg.outerHTML;
                            });

                        } else {
                            var $svg = $(self.cachedSeparators[value]).addClass('section-separator-' + position.value);
                            CP_Customizer.preview.replaceNode(separator.find('svg'), $svg);
                        }
                    }
                );


                separatorControls.color.attachWithSetter(
                    CP_Customizer.contentStyle.getNodeProp(separator.find('path'), pathSelector, null, 'fill'),
                    function (value) {
                        CP_Customizer.contentStyle.setProp(pathSelector, null, 'fill', value);

                    }
                );

                separatorControls.size.attachWithSetter(
                    CP_Customizer.utils.cssValueNumber(CP_Customizer.contentStyle.getProp(selector, null, 'height', '20')),
                    function (value) {
                        CP_Customizer.contentStyle.setProp(selector, null, 'height', value + '%');
                    }
                );

            }


        })

    });

})(window, CP_Customizer, jQuery);
