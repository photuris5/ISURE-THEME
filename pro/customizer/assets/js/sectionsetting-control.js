wp.customize.controlConstructor['sectionsetting'] = wp.customize.Control.extend({
    attachControls: function (control) {

        if (!_.isArray(control)) {
            control = [control]
        }

        this.items = [];
        for (var i = 0; i < control.length; i++) {
            var c = control[i];
            var _wpControl = wp.customize.control(c);
            if (_wpControl) {
                var $container = _wpControl.container;

                this.items.push({
                    'container': $container,
                    'originalParent': $container.parent()
                });


                this.container.find('.setting-control-container').append($container);
            }
        }
    },

    free: function () {
        this.items = this.items || [];


        while (this.items.length) {
            var item = this.items.shift();
            item.originalParent.append(item.container);
        }
    }
});
