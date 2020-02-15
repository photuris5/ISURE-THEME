(function (root, CP_Customizer, $) {

    var mapsAndSubscribeControls = {
        "materialis_maps": {
            "api_key": {
                control: {
                    label:
                    window.CP_Customizer.translateCompanionString("Api key") +
                    " " +
                    "(<a target='_blank' href='https://developers.google.com/maps/documentation/javascript/get-api-key'>" +
                    window.CP_Customizer.translateCompanionString("Get your api key here") +
                    "</a>)",
                    type: "text",
                    setValue: function (name, value, tag) {
                        CP_Customizer.setMod(tag + '_' + name, value);
                    },
                    getValue: function (name, tag) {
                        return CP_Customizer.getMod(tag + '_' + name);
                    }
                }
            },

            "address": {
                control: {
                    label: window.CP_Customizer.translateCompanionString("Address"),
                    type: "text"
                }
            },

            "lng": {
                control: {
                    label: window.CP_Customizer.translateCompanionString("Lng (optional)"),
                    type: "text"
                }
            },

            "lat": {
                control: {
                    label: window.CP_Customizer.translateCompanionString("Lat (optional)"),
                    type: "text"
                }
            },


            "zoom": {
                control: {
                    label: window.CP_Customizer.translateCompanionString("Zoom"),
                    type: "text",
                    default: 65
                }
            },

            "shortcode": {
                control: {
                    canHide: true,
                    description: window.CP_Customizer.translateCompanionString("Use this field for 3rd party maps plugins. The fields above will be ignored in this case."),
                    enableLabel: window.CP_Customizer.translateCompanionString("Use 3rd party shortcode"),
                    label: window.CP_Customizer.translateCompanionString("3rd party shortcode (optional)"),
                    type: "text-with-checkbox",
                    setParse: function (value) {
                        if (value.visible) {
                            return value.shortcode.replace(/^\[+/, '').replace(/\]+$/, '')
                        }
                        return "";
                    },

                    getParse: function (value) {
                        value = value.replace(/^\[+/, '').replace(/\]+$/, '')
                        if (value) {
                            return {value: "[" + (CP_Customizer.utils.htmlDecode(value)) + "]", visible: true};
                        }
                        return {value: "", visible: false}
                    }
                }

            }
        },

        "materialis_subscribe_form": {
            "shortcode": {
                control: {
                    label: window.CP_Customizer.translateCompanionString("3rd party form shortcode"),
                    type: "text",
                    setParse: function (value) {
                        return value.replace(/^\[+/, '').replace(/\]+$/, '')
                    },
                    getParse: function (value) {
                        return "[" + CP_Customizer.utils.htmlDecode(value.replace(/^\[+/, '').replace(/\]+$/, '')) + "]";
                    }
                }
            }
        }
    };

    CP_Customizer.hooks.addFilter('filter_shortcode_popup_controls', function (controls) {
        var extendedControls = _.extend(
            _.clone(controls),
            mapsAndSubscribeControls
        );

        return extendedControls;
    });


    // wrapped within a function in case some components load slower
    CP_Customizer.hooks.addAction('shortcode_edit_materialis_maps', function (node, shortcode) {
        CP_Customizer.editEscapedShortcodeAtts(node, shortcode);
    });
    CP_Customizer.hooks.addAction('shortcode_edit_materialis_subscribe_form', function (node, shortcode) {
        CP_Customizer.editEscapedShortcodeAtts(node, shortcode);
    });


})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {


    var contentElementSelector = '[data-name="materialis-custom-content-shortcode"]';

    CP_Customizer.content.registerItem({
        "shortcode-content": {
            icon: 'mdi-code-array',
            data: '<div data-editable="true" data-name="materialis-custom-content-shortcode" data-content-shortcode="materialis_shortcode_placeholder">[materialis_shortcode_placeholder]</div>',
            contentElementSelector: contentElementSelector,
            tooltip: window.CP_Customizer.translateCompanionString('Custom shortcode'),
            after: shortcodeEdit
        }
    });

    function shortcodeEdit($node) {

        if ($node.is(contentElementSelector)) {

            // var shortcode = prompt('Set the shortcode ( leave empty to remove )', '[' + $node.attr('data-content-shortcode') + ']');
            var shortcode = $node.attr('data-content-shortcode');
            shortcode = CP_Customizer.utils.phpTrim(shortcode, '[]');

            CP_Customizer.popupPrompt(
                window.CP_Customizer.translateCompanionString('Shortcode'),
                window.CP_Customizer.translateCompanionString('Set the shortcode'),
                "[" + shortcode + "]",
                function (shortcode, oldShortcode) {
                    if (shortcode === null) {
                        return;
                    }

                    if (!shortcode) {
                        $node.remove();
                        return;
                    }

                    shortcode = '[' + CP_Customizer.utils.phpTrim(shortcode, '[]') + ']';
                    CP_Customizer.updateNodeShortcode($node, shortcode);
                }
            );


        }
    }

    CP_Customizer.hooks.addAction('shortcode_edit', shortcodeEdit);

})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {

    var contentElementSelector = '[data-name="materialis-widgets-area"]';

    CP_Customizer.content.registerItem({
        "wisgets-area": {
            icon: 'mdi-widgets',
            data: '<div data-editable="true" data-name="materialis-widgets-area" data-content-shortcode="materialis_display_widgets_area id=\'\'">[materialisv_display_widgets_area id=""]</div>',
            contentElementSelector: contentElementSelector,
            tooltip: window.CP_Customizer.translateCompanionString('Widgets Area'),
            after: shortcodeEdit
        }
    });


    function shortcodeEdit($node, sortcodeObject) {

        var areaId = sortcodeObject ? sortcodeObject.attrs.id : "";

        function popupClose(value, oldValue) {
            var shortcode = {
                "tag": "materialis_display_widgets_area",
                "attrs": {
                    "id": value
                }
            };

            CP_Customizer.updateNodeFromShortcodeObject($node, shortcode);
        }

        var $popup = CP_Customizer.popupSelectPrompt(
            window.CP_Customizer.translateCompanionString('Widgets Area'),
            window.CP_Customizer.translateCompanionString('Select a Widgets Area'),
            areaId,
            CP_Customizer.preview.data('widgets_areas'),
            popupClose,
            window.CP_Customizer.translateCompanionString('No Widgets Area Selected'),
            '<a href="#" class="manage-widgets-areas">' + window.CP_Customizer.translateCompanionString("Manage Widgets Areas") + '</a>'
        );

        $popup.find('a.manage-widgets-areas').click(function () {
            CP_Customizer.closePopUps();
            CP_Customizer.wpApi.control('materialis_users_custom_widgets_areas').focus();
        })
    }

    CP_Customizer.hooks.addAction('shortcode_edit_materialis_display_widgets_area', shortcodeEdit);

})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {

    var tag = 'materialis_display_woocommerce_items';

    var cachedCategories = {};
    var cachedTags = {};


    var popupControls = {

        "custom": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Use custom selection"),
                type: "checkbox",
                default: false,
                text: window.CP_Customizer.translateCompanionString('Search for specific products to display'),
                getValue: function () {
                    try {
                        value = JSON.parse(this.value);
                    } catch (e) {

                    }

                    return value;

                },
                toggleVisibleControls: function () {
                    var val = this.val();
                    if (val) {
                        this.$panel.find('' +
                            '[data-field="categories"],' +
                            '[data-field="order_by"],' +
                            '[data-field="order"],' +
                            '[data-field="tags"],' +
                            '[data-field="products_number"]'
                        ).hide();
                        this.$panel.find('[data-field="products"]').show();

                    } else {
                        this.$panel.find('' +
                            '[data-field="categories"],' +
                            '[data-field="order_by"],' +
                            '[data-field="order"],' +
                            '[data-field="tags"],' +
                            '[data-field="products_number"]'
                        ).show();
                        this.$panel.find('[data-field="products"]').hide();
                    }
                },
                ready: function ($controlWrapper, $panel) {
                    var field = this;
                    field.toggleVisibleControls();
                    $controlWrapper.find('input[type=checkbox]').change(function () {
                        field.toggleVisibleControls();
                    });
                }

            }
        },

        "products_number": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Number of products to display"),
                type: "text",
                default: 4
            }
        },


        "categories": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Categories"),
                type: "selectize",
                default: '',
                data: {
                    choices: function () {
                        return cachedCategories;
                    },
                    multiple: true
                }
            }
        },

        "tags": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Tags"),
                type: "selectize",
                default: '',
                data: {
                    choices: function () {
                        return cachedTags;
                    },
                    multiple: true
                }
            }
        },

        "order_by": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Order By"),
                type: "select",
                default: 'date',
                choices: {
                    'date': 'Date',
                    'price': 'Price',
                    'popularity': 'Popularity',
                    'rating': 'Rating',
                    'random': 'Random'
                }
            }
        },

        "order": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Order"),
                type: "select",
                default: 'DESC',
                choices: {
                    "ASC": "ASC",
                    "DESC": "DESC"
                }
            }
        },

        "products": {
            control: {
                label: window.CP_Customizer.translateCompanionString("Select Products to display"),
                type: "selectize-remote",
                default: null,
                getValue: function () {
                    if (this.value == 'null' || !this.value) {
                        return [];
                    }
                    var ids = this.value.split(',');
                    return ids;

                },
                ready: function ($controlWrapper) {
                    var field = this;

                    if (this.value) {
                        CP_Customizer.IO.rest.get(
                            '/wc/v2/products',
                            {
                                'materialis_woocommerce_api_nonce': CP_Customizer.options('materialis_woocommerce_api_nonce'),
                                include: field.value.join(',')
                            }
                        ).done(function (data) {
                            field.initSelectize(data);
                        }).fail(function () {
                            field.initSelectize([]);
                        })
                    } else {
                        field.initSelectize([]);
                    }
                },

                initSelectize: function (options) {

                    var $select = this.$wrapper.find('select');
                    $select.attr('multiple', true);
                    if (_.isArray(options)) {
                        for (var i = 0; i < options.length; i++) {
                            $select.append('<option selected="true" value="' + options[i].id + '">' + options[i].name + '</option>')
                        }

                    }
                    var field = this;
                    $select.selectize({
                        valueField: 'id',
                        labelField: 'name',
                        searchField: 'name',
                        maxItems: null,
                        plugins: ['remove_button', 'drag_drop'],
                        options: options || [],
                        create: false,
                        load: function (query, callback) {
                            if (!query.length) return callback();
                            CP_Customizer.IO.rest.get(
                                '/wc/v2/products',
                                {
                                    'materialis_woocommerce_api_nonce': CP_Customizer.options('materialis_woocommerce_api_nonce'),
                                    search: query
                                }
                            ).done(function (data) {
                                callback(data);
                            }).fail(function () {
                                callback();
                            })

                        }
                    })
                }
            }
        }
    };

    CP_Customizer.addModule(function (CP_Customizer) {

        if (!CP_Customizer.options('isWoocommerceInstalled', false)) {
            return;
        }

        CP_Customizer.IO.rest.get(
            '/wc/v2/products/categories',
            {
                'materialis_woocommerce_api_nonce': CP_Customizer.options('materialis_woocommerce_api_nonce')
            }
        ).done(function (data) {
            if (_.isArray(data)) {
                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    cachedCategories[item.id] = item.name
                }
            }
        });


        CP_Customizer.IO.rest.get(
            '/wc/v2/products/tags',
            {
                'materialis_woocommerce_api_nonce': CP_Customizer.options('materialis_woocommerce_api_nonce')
            }
        ).done(function (data) {
            if (_.isArray(data)) {
                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    cachedTags[item.id] = item.name
                }
            }
        });

    });


    CP_Customizer.hooks.addFilter('filter_shortcode_popup_controls', function (controls) {
        var popUp = {};
        popUp[tag] = popupControls;
        var extendedControls = _.extend(
            _.clone(controls),
            popUp
        );
        return extendedControls;
    });


    CP_Customizer.hooks.addAction('shortcode_edit_' + tag, function ($node, shortcodeData) {
        CP_Customizer.openShortcodePopupEditor(function (atts) {
            var newShortcode = _.clone(shortcodeData);
            atts.tags = (atts.tags == null) ? '' : atts.tags;
            atts.categories = (atts.categories == null) ? '' : atts.categories;
            newShortcode.attrs = _.extend(newShortcode.attrs, atts);

            CP_Customizer.updateNodeFromShortcodeObject($node, newShortcode);

        }, $node, shortcodeData)
    });


    CP_Customizer.hooks.addAction('dynamic_columns_handle', function (cols, node) {
        if (CP_Customizer.isShortcodeContent(node)) {
            var shortcode = CP_Customizer.getNodeShortcode(node);
            var device = root.CP_Customizer.preview.currentDevice();
            var prop = (device === "tablet") ? "columns_tablet" : "columns";
            shortcode.attrs = shortcode.attrs || {};
            shortcode.attrs[prop] = cols;

            CP_Customizer.updateNodeFromShortcodeObject(node, shortcode);
        }
    });

})(window, CP_Customizer, jQuery);


(function (root, CP_Customizer, $) {

    var countUpSelector = '[data-countup="true"]';

    var countupControls = {
        min: {
            control: {
                label: window.CP_Customizer.translateCompanionString('Start counter from'),
                type: 'text',
                attr: 'data-min',
                default: 0
            }
        },

        max: {
            control: {
                label: window.CP_Customizer.translateCompanionString('End counter to'),
                type: 'text',
                attr: 'data-max',
                default: 100
            }
        },

        stop: {
            control: {
                label: window.CP_Customizer.translateCompanionString('Stop circle at value'),
                type: 'text',
                attr: 'data-stop',
                active: function ($item) {
                    return $item.closest('.circle-counter').length > 0;
                },
                default: 50
            }
        },

        prefix: {
            control: {
                label: window.CP_Customizer.translateCompanionString('Prefix ( text in front of the number )'),
                type: 'text',
                attr: 'data-prefix',
                default: ""
            }
        },

        suffix: {
            control: {
                label: window.CP_Customizer.translateCompanionString('Suffix ( text after the number )'),
                type: 'text',
                attr: 'data-suffix',
                default: "%"
            }
        },

        duration: {
            control: {
                label: window.CP_Customizer.translateCompanionString('Counter duration ( in milliseconds )'),
                type: 'text',
                attr: 'data-duration',
                default: 2000
            }
        }

    };

    CP_Customizer.hooks.addFilter('filter_custom_popup_controls', function (controls) {
        var extendedControls = _.extend(_.clone(controls),
            {
                countup: countupControls
            }
        );
        return extendedControls;
    });

    CP_Customizer.preview.registerContainerDataHandler(countUpSelector, function ($item) {
        CP_Customizer.openCustomPopupEditor($item, 'countup', function (values, $item) {
            CP_Customizer.preview.jQuery($item[0]).data().restartCountUp();
        });
    });

    CP_Customizer.hooks.addAction('clean_nodes', function ($nodes) {
        $nodes.find(countUpSelector).each(function () {
            this.innerHTML = "";
            this.removeAttribute('data-max-computed');
        });

        $nodes.find('.circle-counter svg.circle-bar').removeAttr('style');
    });


    CP_Customizer.hooks.addFilter('is_fixed_element', function (value, node) {

        if (node.is(countUpSelector)) {
            value = true;
        }

        return value;
    });


})(window, CP_Customizer, jQuery);
