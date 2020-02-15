(function (root, CP_Customizer, $) {

    //TODO: Custom section enabler here;
    return;

    var groupControl,


        cardControl,
        titleControl,
        gutterControl,
        sectionTitleDefaultContent = '' +
            '<div class="section-title-col" data-type="column">' +
            '    <h2 class="">' +
                window.CP_Customizer.translateCompanionString("What Our Clients Say") +
            '    </h2>' +
            '    <p class="">' +
            '        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi.' +
            '    </p>' +
            '</div>';

    CP_Customizer.hooks.addAction('section_panel_before_end', function ($container) {

        groupControl = CP_Customizer.createControl.controlsGroup(
            'section-custom-section-options',
            $container,
            window.CP_Customizer.translateCompanionString('Custom Section Options'));

        var $groupEl = groupControl.el();

        titleControl = CP_Customizer.createControl.checkbox(
            'section-custom-section-options-title',
            $groupEl,
            window.CP_Customizer.translateCompanionString('Display section title')
        );

        cardControl = CP_Customizer.createControl.checkbox(
            'section-custom-section-options-card',
            $groupEl,
            window.CP_Customizer.translateCompanionString('Display items as cards')
        );

        gutterControl = CP_Customizer.createControl.checkbox(
            'section-custom-section-options-gutter',
            $groupEl,
            window.CP_Customizer.translateCompanionString('Add space between items')
        );

    });

    CP_Customizer.hooks.addAction('section_sidebar_opened', function (data) {
        var section = data.section;

        if (!section.is('[data-section-type="custom"]')) {
            groupControl.hide();
            return;
        } else {
            groupControl.show();
        }

        var sectionContentArea = section.find('[data-type=row]').parent();

        titleControl.attachWithSetter(
            (sectionContentArea.children('.section-title-col').length > 0),
            function (value) {
                if (!value) {
                    sectionContentArea.children('.section-title-col').remove();
                } else {
                    CP_Customizer.preview.insertNode($(sectionTitleDefaultContent), sectionContentArea, 0);
                }
            }
        );

        gutterControl.attachWithSetter(
            (!sectionContentArea.find('[data-type=row]').hasClass('no-gutter')),
            function (value) {
                if (!value) {
                    sectionContentArea.find('[data-type=row]').addClass('no-gutter')
                } else {
                    sectionContentArea.find('[data-type=row]').removeClass('no-gutter')
                }
            }
        );

        cardControl.attachWithSetter(
            sectionContentArea.find('[data-type=row] [data-type=column]').hasClass('ope-card'),
            function (value) {
                if (value) {
                    sectionContentArea.find('[data-type=row] [data-type=column]').addClass('ope-card')
                } else {
                    sectionContentArea.find('[data-type=row] [data-type=column]').removeClass('ope-card')
                }
            }
        );

    });
})(window, CP_Customizer, jQuery);
