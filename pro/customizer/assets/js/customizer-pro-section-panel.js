(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {

        var sectionPanel = CP_Customizer.panels.sectionPanel;

        sectionPanel.excludeArea('background_color');
        sectionPanel.excludeArea('background_image');

        var priority = 0;

        sectionPanel.registerArea('section_spacing', {
            priority: priority,
            areaTitle: root.CP_Customizer.translateCompanionString('Section Dimensions'),
            init: function ($container) {

                var spacingControl = CP_Customizer.createControl.spacing(
                    this.getPrefixed('spacing'),
                    $container,
                    {
                        sides: ['top', 'bottom'],
                        label: root.CP_Customizer.translateCompanionString('Section Spacing')
                    }
                );

                this.addToControlsList(spacingControl);
            },

            update: function (data) {
                var selector = '.content-section[data-id="' + data.section.attr('data-id') + '"]';

                var currentPadding = {
                    top: CP_Customizer.contentStyle.getNodeProp(data.section, selector, null, 'padding-top'),
                    bottom: CP_Customizer.contentStyle.getNodeProp(data.section, selector, null, 'padding-bottom')
                };


                this.getControl('spacing').attachWithSetter(
                    currentPadding,
                    function (value) {
                        CP_Customizer.contentStyle.setProp(selector, null, 'padding-top', value.top);
                        CP_Customizer.contentStyle.setProp(selector, null, 'padding-bottom', value.bottom);
                    }
                );
            }
        });

        priority = 5;

        sectionPanel.registerArea('background', {
            priority: priority,
            areaTitle: root.CP_Customizer.translateCompanionString('Section Background'),
            init: function ($container) {
                var type = CP_Customizer.createControl.select(
                    this.getPrefixed('type'),
                    $container,
                    {
                        value: '',
                        label: root.CP_Customizer.translateCompanionString('Background Type'),
                        choices: {
                            transparent: root.CP_Customizer.translateCompanionString("Transparent"),
                            color: root.CP_Customizer.translateCompanionString("Color"),
                            image: root.CP_Customizer.translateCompanionString("Image"),
                            gradient: root.CP_Customizer.translateCompanionString("Gradient")
                        }
                    });


                var color = CP_Customizer.createControl.color(
                    this.getPrefixed('color'),
                    $container,
                    {
                        value: '#ffffff',
                        label: root.CP_Customizer.translateCompanionString('Background Color')
                    });


                var image = CP_Customizer.createControl.image(
                    this.getPrefixed('image'),
                    $container,
                    {
                        value: '',
                        label: root.CP_Customizer.translateCompanionString('Background Image')
                    });

                var gradient = CP_Customizer.createControl.gradient(
                    this.getPrefixed('gradient'),
                    $container,
                    {
                        value: '',
                        label: root.CP_Customizer.translateCompanionString('Gradient')
                    });

                var pageBackground = CP_Customizer.createControl.button(
                    this.getPrefixed('page-bg'),
                    $container,
                    root.CP_Customizer.translateCompanionString('Change Page Background Image'),
                    function () {
                        CP_Customizer.hideRightSidebar();
                        CP_Customizer.wpApi.control('background_image').focus();
                    }
                );


                var overlay = CP_Customizer.createControl.color(
                    this.getPrefixed('overlay'),
                    $container,
                    {
                        value: 'rgba(0,0,0,0.5)',
                        label: root.CP_Customizer.translateCompanionString('Background Overlay')
                    });

                this.addToControlsList(type);
                this.addToControlsList(color);
                this.addToControlsList(image);
                this.addToControlsList(gradient);
                this.addToControlsList(pageBackground);
                this.addToControlsList(overlay);
            },

            getCurrentBg: function (data) {
                var color = getComputedStyle(data.section[0]).backgroundColor;

                var image = CP_Customizer.utils.normalizeBackgroundImageValue((getComputedStyle(data.section[0]).backgroundImage || "")) || false;
                image = (image && image !== "none" && !image.endsWith('/none')) ? image : false;

                var gradientClass = (data.section.attr('class') || "").match(new RegExp(CP_Customizer.options().gradients.join("|")));
                gradientClass = (gradientClass || [])[0];

                var bgType = "color";
                var bgValue = color;

                if (tinycolor(color).getAlpha() === 0) {
                    bgType = "transparent";
                    bgValue = 'rgba(0,0,0,0)';
                }

                if (gradientClass) {
                    bgType = "gradient";
                    bgValue = gradientClass;
                } else if (image) {
                    bgType = "image";
                    bgValue = image;
                }

                return {
                    type: bgType,
                    value: bgValue
                };
            },

            updateActiveBgControls: function (bgType, setDefault) {
                this.getControl('color').hide();
                this.getControl('image').hide();
                this.getControl('gradient').hide();
                this.getControl('page-bg').hide();

                switch (bgType) {
                    case "transparent":
                        this.getControl('page-bg').control.container.show();
                        if (setDefault) {
                            this.getControl('color')._value = undefined;
                            this.getControl('color').set('rgba(255,255,255,0)');
                        }
                        break;
                    case "color":
                        this.getControl('color').show();

                        if (setDefault) {
                            this.getControl('color')._value = undefined;
                            this.getControl('color').set('#ffffff');
                        }

                        break;
                    case "image":
                        this.getControl('image').show();
                        if (setDefault) {
                            this.getControl('image')._value = undefined;
                            this.getControl('image').set(CP_Customizer.options('PRO_URL') + "/assets/images/default-row-bg.jpg");
                        }
                        // parallaxBackground.control.container.show();
                        break;
                    case "gradient":
                        this.getControl('gradient').show();
                        if (setDefault) {
                            this.getControl('gradient')._value = undefined;
                            this.getControl('gradient').set('february_ink');
                        }
                        // parallaxBackground.control.container.show();
                        break;
                }
            },

            removeGradientClass: function ($item) {
                $item.removeClass(CP_Customizer.options().gradients.join(" "));
                return $item;
            },


            attachControlSetter: function (control, currentBg, setter) {
                var value = currentBg.type === control ? currentBg.value : false;

                if (value === false && control === 'gradient') {
                    value = '';
                }

                this.getControl(control).attachWithSetter(value, function (value, oldValue) {
                    try {
                        if (setter) {
                            setter.call(this, value, oldValue);
                        }
                    } catch (e) {
                        console.error('Section bg area error', e);
                    }
                });
            },

            update: function (data) {
                var currentBg = this.getCurrentBg(data),
                    dataId = '[data-id=' + data.section.attr('data-id') + ']',
                    overlayAttr = 'data-section-ov',
                    overlayAttrSelector = '[' + overlayAttr + ']',
                    currentBgOverlay = CP_Customizer.contentStyle.getNodeProp(data.section, dataId + overlayAttrSelector, ':before', 'background-color'),
                    self = this;

                this.updateActiveBgControls(currentBg.type);
                this.getControl('type').attachWithSetter(currentBg.type, function (value) {
                    currentBg.type = value;
                    self.updateActiveBgControls(value, true);

                });
                this.attachControlSetter('color', currentBg, function (value) {

                    var availableFor = ["color", "transparent"];

                    if (!value || availableFor.indexOf(currentBg.type) === -1) {
                        return;
                    }
                    self.removeGradientClass(data.section);
                    data.section.css({
                        'background-image': 'none',
                        'background-color': value
                    });

                    CP_Customizer.updateState();
                });
                this.attachControlSetter('image', currentBg, function (value) {

                    if (currentBg.type !== "image") {
                        return
                    }

                    if (value) {
                        value = 'url("' + value + '")';
                    } else {
                        value = "";
                    }
                    data.section.css({
                        'background-color': 'none',
                        'background-image': value,
                        'background-size': 'cover',
                        'background-position': 'center top'
                    });

                    if (value) {
                        self.removeGradientClass(data.section);
                    }

                    CP_Customizer.updateState();
                });
                this.attachControlSetter('gradient', currentBg, function (value) {

                    if (!value) {
                        return;
                    }

                    if (currentBg.type !== "gradient") {
                        return
                    }

                    self.removeGradientClass(data.section);
                    data.section.addClass(value);
                    data.section.css({
                        'background-image': '',
                        'background-color': ''
                    });
                    CP_Customizer.updateState();
                });

                this.attachControlSetter('page-bg', currentBg);

                if (data.section.is('[data-overlap="true"]')) {
                    this.getControl('overlay').hide();
                } else {
                    this.getControl('overlay').show();
                }

                this.getControl('overlay').attachWithSetter(currentBgOverlay, function (value, oldValue) {
                    var hasOverlayAttr = data.section.attr('data-ovid');
                    if (!hasOverlayAttr) {
                        data.section.attr(overlayAttr, 1);
                    }
                    CP_Customizer.contentStyle.setProp(dataId + overlayAttrSelector, ':before', 'background-color', value);
                    CP_Customizer.updateState();
                });

            }
        });

        priority = 10;

        sectionPanel.registerArea('text-options', {
            areaTitle: root.CP_Customizer.translateCompanionString('Text Options'),
            priority: priority,
            textColorOptions: {
                " ": root.CP_Customizer.translateCompanionString("Default"),
                "white-text": root.CP_Customizer.translateCompanionString("White text"),
                "dark-text": root.CP_Customizer.translateCompanionString("Dark text")
            },
            sectionTitleAreaDefault: '' +
            '<div data-section-title-area="true" class="row text-center">' +
            '   <div class="section-title-col" data-type="column">' +
            '       <h2>Lorem impsul dolor sit amet</h2>' +
            '       <p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>' +
            '   </div>' +
            '</div>',

            init: function ($container) {


                var rowShowSectionTitle = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('section-title'),
                    $container,
                    root.CP_Customizer.translateCompanionString('Display section title area')
                );


                var revertColumnsOnMobile = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('swap-columns-on-mobile'),
                    $container,
                    root.CP_Customizer.translateCompanionString('Swap columns position on mobile')
                );

                var rowTextColorClass = CP_Customizer.createControl.select(
                    this.getPrefixed('color'),
                    $container,
                    {
                        value: '',
                        label: root.CP_Customizer.translateCompanionString('Text Color'),
                        choices: this.textColorOptions
                    });

                this.addToControlsList(rowTextColorClass);
                this.addToControlsList(revertColumnsOnMobile);
                this.addToControlsList(rowShowSectionTitle);
            },
            update: function (data) {
                var section = data.section;

                var colorClasses = Object.getOwnPropertyNames(this.textColorOptions).filter(function (item) {
                    return item.trim().length;
                });

                var currentClass = " ";

                for (var i = 0; i < colorClasses.length; i++) {
                    if (section.hasClass(colorClasses[i]) || section.hasClass('section-title-col-' + colorClasses[i])) {
                        currentClass = colorClasses[i];
                    }
                }

                this.getControl('color').attachWithSetter(currentClass, function (newValue, oldValue) {
                    oldValue = oldValue + ' section-title-col-' + oldValue;
                    if (oldValue.trim().length) {
                        section.removeClass(oldValue);
                    }

                    if (section.find('.row[class*=bg-color-], .card ').length) {
                        section.addClass('section-title-col-' + newValue);
                    } else {
                        section.addClass(newValue);
                    }
                });

                var sectionTitleAreaSelector = '[data-section-title-area="true"]';
                var sectionTitleAreaSelectorFallback = 'div > .row > .section-title-col';
                var sectionTitleArea = section.find(sectionTitleAreaSelector);

                if (!sectionTitleArea.length) {
                    sectionTitleArea = section.find(sectionTitleAreaSelectorFallback);
                }

                var self = this;

                var sectionExports = CP_Customizer.getSectionExports(section);
                var canShowSectionTitleControl = _.isUndefined(sectionExports.allowSectionTitleOptions) || sectionExports.allowSectionTitleOptions;

                if (canShowSectionTitleControl) {
                    this.showAll();
                } else {
                    this.hideAll();
                }


                this.getControl('section-title').attachWithSetter(sectionTitleArea.length > 0, function (value) {
                    if (value) {
                        sectionTitleArea = $(self.sectionTitleAreaDefault);
                        CP_Customizer.preview.insertNode(sectionTitleArea, section.children('div').not('[class*=section-separator]').eq(0), 0);
                    } else {
                        var $row = sectionTitleArea.closest('.row');
                        CP_Customizer.preview.removeNode($row);
                    }
                });


                var sectionExportData = CP_Customizer.getSectionExports(section);
                var revertColumnsOnMobile = this.getControl('swap-columns-on-mobile');

                if (sectionExportData.canRevertColumnsOnMobile) {
                    revertColumnsOnMobile.show();
                    var $revertClassesHolder = sectionExportData.revertClassesHolder ? section.find(sectionExportData.revertClassesHolder) : section.find('.row').eq(0).children().last();

                    if ($revertClassesHolder.is('.section-title-col')) {
                        $revertClassesHolder = section.find('.row').eq(1).children().last();
                    }

                    revertColumnsOnMobile.attachWithSetter(
                        $revertClassesHolder.is('.first-xs.last-sm')
                        , function (value) {
                            if (value) {
                                $revertClassesHolder.addClass('first-xs last-sm');
                            } else {
                                $revertClassesHolder.removeClass('first-xs last-sm');
                            }
                        }
                    )

                } else {
                    revertColumnsOnMobile.hide();
                }
            }
        });


        priority = 15;

        sectionPanel.registerArea('section_elevation', {
            priority: priority,
            areaTitle: root.CP_Customizer.translateCompanionString('Section Shadows'),

            elevationClasses: [],
            init: function ($container) {

                var elevationControl = CP_Customizer.createControl.slider(
                    this.getPrefixed('elevation'),
                    $container,
                    {
                        label: root.CP_Customizer.translateCompanionString('Shadow elevation'),
                        choices: {
                            min: 0,
                            max: 24,
                            step: 1
                        },
                        default: 3,
                    }
                );

                this.elevationClasses = _.range(0, 25).map(function (item) {
                    return "mdc-elevation--z" + item
                });

                this.addToControlsList(elevationControl);
            },


            getElementsWithElevation: function (section) {
                var selector = [
                    'div[class*="mdc-elevation--z"]',
                    'img[class*="mdc-elevation--z"]'
                ];

                return section.find(selector.join(',')).filter(function (index, item) {
                    return !$(item).attr('data-fixed-elevation');
                });
            },


            getCurrentElevationLevel: function (section) {
                var elements = this.getElementsWithElevation(section);

                if (elements.length) {
                    var elevationRegexp = /((mdc-elevation--z\d+))/ig;
                    var currentElevationClass = elements.eq(0).attr('class').match(elevationRegexp)[0];
                    return currentElevationClass.replace('mdc-elevation--z', '').trim();
                }

                return 0;
            },


            setElevation: function (section, level) {
                var self = this;
                self.getElementsWithElevation(section).each(function () {
                    $(this).removeClass(self.elevationClasses.join(' ')).addClass('mdc-elevation--z' + level);
                });
            },

            update: function (data) {

                var elevationElements = this.getElementsWithElevation(data.section);

                if (elevationElements.length) {
                    this.enable();
                    var self = this;
                    this.getControl('elevation').attachWithSetter(
                        this.getCurrentElevationLevel(data.section),
                        function (value) {
                            self.setElevation(data.section, value);
                        }
                    );

                }
                else {
                    this.disable();
                }

            }
        });


        sectionPanel.registerArea('section-specific-area', {
            init: function ($container) {
                this.sectionAreaContainer = $container;
            },

            specificAreas: {},

            update: function (data) {
                var sectionSepcificArea = data.sectionExports.sectionArea,
                    areaName = data.section.attr('data-export-id') + '-specific-section-area',
                    area;


                if (sectionSepcificArea && _.isUndefined(this.specificAreas[areaName])) {
                    area = sectionPanel.registerArea(areaName, sectionSepcificArea);

                    area.initAreaTitle(this.sectionAreaContainer);
                    area.init(this.sectionAreaContainer);

                    this.specificAreas[areaName] = area;
                }

                for (var name in this.specificAreas) {

                    if (!this.specificAreas.hasOwnProperty(name)) {
                        continue;
                    }

                    if (name === areaName) {
                        CP_Customizer.log('Custom Section Area updated', this.specificAreas[name])
                        CP_Customizer.hooks.doAction('update_before_section_sidebar_area_' + name, data);
                        this.specificAreas[name].enable();
                        this.specificAreas[name].update(data);
                        CP_Customizer.hooks.doAction('update_after_section_sidebar_area_' + name, data);
                    } else {
                        this.specificAreas[name].disable();
                    }
                }
            }

        });

        sectionPanel.extendArea('list_items', function (area) {
            area = area.extend({
                itemsListControlTemplate: '' +
                '<div class="section-list-item">' +
                '   <div class="handle reorder-handler"></div>' +
                '   <div class="text">' +
                '           <span title="' + root.CP_Customizer.translateCompanionString('Color item') + '" class="featured-item color"><input class="item-colors" type="text"></input></span>' +
                '           <% if(options.showFeaturedCheckbox) { %>' +
                '               <span title="' + root.CP_Customizer.translateCompanionString('Highlight item') + '" class="featured-item"><input class="featured" type="checkbox"></span>' +
                '           <% } %>' +
                '           <span><%= text %></span>' +
                '   </div>' +
                '</div>' +
                '',

                getItemOptions: function (section, item) {
                    var featuredClass = section.attr('data-featured-class');
                    var showFeatured = _.isUndefined(CP_Customizer.getSectionExports(section).showFeaturedCheckbox) ? section.is('[data-category="pricing"]') : CP_Customizer.getSectionExports(section).showFeaturedCheckbox;
                    return {
                        showFeaturedCheckbox: showFeatured,
                        featured: featuredClass ? item.hasClass(featuredClass) : false
                    }
                },


                setFeaturedElementStyle: function ($container, setFeature) {
                    var selector = '[data-featured][data-default]';
                    var $elements = $container.find(selector);

                    if ($container.is(selector)) {
                        $elements = $elements.add($container);
                    }

                    $elements.each(function () {
                        var $item = $(this),
                            defaultClasses = $item.attr('data-default'),
                            featuredClasses = $item.attr('data-featured').trim(),
                            toRemove = '',
                            toAdd = '';

                        if (setFeature) {
                            toRemove = defaultClasses;
                            toAdd = featuredClasses;
                        } else {

                            toRemove = featuredClasses;
                            toAdd = defaultClasses;
                        }

                        var toRemoveSelector = CP_Customizer.utils.normalizeClassAttr(toRemove, true);

                        // try to change only the default elements
                        // and leave the changed ones as they are
                        // no-class - in data-default means intended

                        if (toRemoveSelector.length === 0) {
                            console.warn('Featured default selector is empty! May cause inconsistent changes');
                        } else {
                            if (toRemove !== 'no-class' && $item.is(toRemoveSelector)) {
                                $item.removeClass(toRemove);
                            }
                        }

                        if (toAdd !== 'no-class') {
                            $item.addClass(toAdd);
                        }

                    })
                },

                afterItemCreation: function (sortableItem, data) {
                    sortableItem.data('original', data.original);

                    var section = CP_Customizer.preview.getNodeSection(data.original),
                        sectionId = CP_Customizer.preview.getNodeExportId(data.original),
                        customColor = !_.isUndefined(CP_Customizer.getSectionExports(sectionId).customColor),
                        featuredClass = section.attr('data-featured-class'),
                        area = this;

                    if (featuredClass) {
                        this.getControl('order').control.container.addClass('has-featured');
                    } else {
                        this.getControl('order').control.container.removeClass('has-featured');
                    }


                    function getActiveColor() {
                        colorMapping = CP_Customizer.getSectionExports(sectionId).customColor;
                        return CP_Customizer.contentStyle.getSectionItemColor(data.original, colorMapping, CP_Customizer.getSectionExports(sectionId).customColorDefault);
                    }

                    var colorpicker = sortableItem.find(".item-colors");
                    CP_Customizer.initSpectrumButton(colorpicker);

                    CP_Customizer.addSpectrumButton(colorpicker);

                    sortableItem.find(".item-colors").spectrum("set", getActiveColor());
                    sortableItem.find(".item-colors").change(function () {
                        var $node = sortableItem.data('original'),
                            colorMapping = CP_Customizer.getSectionExports(sectionId).customColor,
                            newVal = $(this).val();

                        CP_Customizer.contentStyle.setSectionItemColor($node, colorMapping, newVal);

                        CP_Customizer.updateState();
                    });

                    sortableItem.find(".featured").prop('checked', data.options.featured);
                    sortableItem.find('.featured').unbind('change').change(function () {
                        var checked = $(this).prop('checked');
                        var node = sortableItem.data('original');
                        if (checked) {
                            node.addClass(featuredClass);
                            area.setFeaturedElementStyle(node, true);
                        } else {
                            node.removeClass(featuredClass);
                            area.setFeaturedElementStyle(node, false);
                        }
                        CP_Customizer.updateState();
                    });

                    if (customColor) {
                        sortableItem.find('.featured-item.color').show();
                    } else {
                        sortableItem.find('.featured-item.color').hide();
                    }
                }
            });

            return area;

        });
    });

})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {
    CP_Customizer.addModule(function (CP_Customizer) {
        CP_Customizer.panels.sectionPanel.registerArea('latest_news', {
            priority: CP_Customizer.MAX_SAFE_INTEGER,
            areaTitle: root.CP_Customizer.translateCompanionString('Latest News Settings'),
            init: function ($container) {
                var rowsControl = CP_Customizer.createControl.number(
                    this.getPrefixed('posts'),
                    $container,
                    {
                        label: window.CP_Customizer.translateCompanionString('Number of posts to display'),
                        min: 1,
                        step: 1
                    }
                );

                this.addToControlsList(rowsControl);
            },
            update: function (data) {
                var section = data.section,
                    $holder = section.find('[data-content-shortcode]'),
                    containsShortcut = CP_Customizer.nodeContainsShortcode(section, 'materialis_latest_news');

                if (!containsShortcut) {
                    this.disable();
                    return;
                }

                this.enable();

                var shortcodeData = CP_Customizer.getNodeShortcode($holder);

                var cols = parseInt(shortcodeData.attrs.columns) ? 12 / shortcodeData.attrs.columns : 3;
                cols = shortcodeData.attrs.posts || (cols || 3)


                this.getControl('posts').attachWithSetter(
                    cols,
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode($holder);

                        shortcodeData.attrs.posts = value;
                        root.CP_Customizer.updateNodeFromShortcodeObject($holder, shortcodeData);
                    }
                );

            }
        });
    });
})(window, CP_Customizer, jQuery);
