(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {

        var shortcodeEdit = function ($node, shortcode) {

            CP_Customizer.openMediaBrowser('gallery', function (selection, ids) {

                shortcode.attrs.ids = ids.join(',');
                shortcode.attrs.columns = selection.gallery.get('columns');
                shortcode.attrs.size = selection.gallery.get('size');
                shortcode.attrs.link = selection.gallery.get('link');

                CP_Customizer.updateNodeFromShortcodeObject($node, shortcode);

            }, shortcode.attrs);
        };

        CP_Customizer.hooks.addAction('shortcode_edit_gallery', shortcodeEdit);
        CP_Customizer.hooks.addAction('shortcode_edit_materialis_gallery', shortcodeEdit);


        CP_Customizer.panels.sectionPanel.registerArea('gallery', {
            priority: CP_Customizer.MAX_SAFE_INTEGER,
            areaTitle: window.CP_Customizer.translateCompanionString('Gallery Settings'),

            init: function ($container) {
                var useMasonryControl = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('masonry'),
                    $container,
                    window.CP_Customizer.translateCompanionString('Use Masonry to display the gallery')
                );


                var useLightBoxControl = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('lightbox'),
                    $container,
                    window.CP_Customizer.translateCompanionString('Open images in Lightbox')
                );

                var showCaptions = CP_Customizer.createControl.checkbox(
                    this.getPrefixed('captions'),
                    $container,
                    window.CP_Customizer.translateCompanionString('Show images captions')
                );


                var columnsPerRow = CP_Customizer.createControl.select(
                    this.getPrefixed('columns'),
                    $container,
                    {
                        label: window.CP_Customizer.translateCompanionString('Columns per row'),

                        choices: {
                            "1": "1 " + window.CP_Customizer.translateCompanionString("Column"),
                            "2": "2 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "3": "3 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "4": "4 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "5": "5 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "6": "6 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "7": "7 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "8": "8 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "9": "9 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "10": "10 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "11": "11 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "12": "12 " + window.CP_Customizer.translateCompanionString("Columns")
                        }

                    }
                );

                var columnsPerRowTablet = CP_Customizer.createControl.select(
                    this.getPrefixed('columns_tablet'),
                    $container,
                    {
                        label: window.CP_Customizer.translateCompanionString('Columns per row on tablet'),

                        choices: {
                            "1": "1 " + window.CP_Customizer.translateCompanionString("Column"),
                            "2": "2 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "3": "3 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "4": "4 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "5": "5 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "6": "6 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "7": "7 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "8": "8 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "9": "9 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "10": "10 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "11": "11 " + window.CP_Customizer.translateCompanionString("Columns"),
                            "12": "12 " + window.CP_Customizer.translateCompanionString("Columns")
                        }

                    }
                );

                this.addToControlsList(useMasonryControl);
                this.addToControlsList(useLightBoxControl);
                this.addToControlsList(columnsPerRow);
                this.addToControlsList(columnsPerRowTablet);
                this.addToControlsList(showCaptions);
            },

            update: function (data) {
                var section = data.section,
                    galleryHolder = section.find('[data-content-shortcode]'),
                    isGallerySection = galleryHolder.length && CP_Customizer.nodeWrapsShortcode(galleryHolder, 'materialis_gallery');

                if (!isGallerySection) {
                    this.disable();
                    return;
                }

                this.enable();

                var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);

                this.getControl('masonry').attachWithSetter(
                    shortcodeData.attrs.masonry === '1',
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);
                        if (value) {
                            shortcodeData.attrs.masonry = '1';
                        } else {
                            shortcodeData.attrs.masonry = '0';
                        }

                        root.CP_Customizer.updateNodeFromShortcodeObject(galleryHolder, shortcodeData);
                    }
                );

                this.getControl('captions').attachWithSetter(
                    CP_Customizer.utils.valueToBool(shortcodeData.attrs.caption || "no"),
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);
                        if (value) {
                            shortcodeData.attrs.caption = 'yes';
                        } else {
                            shortcodeData.attrs.caption = 'no';
                        }

                        root.CP_Customizer.updateNodeFromShortcodeObject(galleryHolder, shortcodeData);
                    }
                );


                this.getControl('lightbox').attachWithSetter(
                    shortcodeData.attrs.lb === '1',
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);
                        if (value) {
                            shortcodeData.attrs.lb = '1';
                        } else {
                            shortcodeData.attrs.lb = '0';
                        }

                        root.CP_Customizer.updateNodeFromShortcodeObject(galleryHolder, shortcodeData, true);
                    }
                );

                this.getControl('columns').attachWithSetter(
                    shortcodeData.attrs.columns || "4",
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);
                        shortcodeData.attrs.columns = value;
                        root.CP_Customizer.updateNodeFromShortcodeObject(galleryHolder, shortcodeData);
                    }
                );

                this.getControl('columns_tablet').attachWithSetter(
                    shortcodeData.attrs.columns_tablet || "4",
                    function (value) {
                        var shortcodeData = CP_Customizer.getNodeShortcode(galleryHolder);
                        shortcodeData.attrs.columns_tablet = value;
                        root.CP_Customizer.updateNodeFromShortcodeObject(galleryHolder, shortcodeData);
                    }
                );
            }
        })

        CP_Customizer.hooks.addAction('section_layout_changed', function (section, layout) {
            var masonryGallery = section.find('.gallery-items-wrapper');
            if (!masonryGallery.length) {
                return;
            }

            if (masonryGallery.data().masonry) {
                masonryGallery.data().masonry.layout();
            }

        });

    });
})(window, CP_Customizer, jQuery);
