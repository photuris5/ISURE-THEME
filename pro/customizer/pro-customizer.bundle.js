// Spectrum Colorpicker v1.8.0
// https://github.com/bgrins/spectrum
// Author: Brian Grinstead
// License: MIT

(function (factory) {
    "use strict";

    if (typeof define === 'function' && define.amd) { // AMD
        define(['jquery'], factory);
    }
    else if (typeof exports == "object" && typeof module == "object") { // CommonJS
        module.exports = factory(require('jquery'));
    }
    else { // Browser
        factory(jQuery);
    }
})(function($, undefined) {
    "use strict";

    var defaultOpts = {

        // Callbacks
        beforeShow: noop,
        move: noop,
        change: noop,
        show: noop,
        hide: noop,

        // Options
        color: false,
        flat: false,
        showInput: false,
        allowEmpty: false,
        showButtons: true,
        clickoutFiresChange: true,
        showInitial: false,
        showPalette: false,
        showPaletteOnly: false,
        hideAfterPaletteSelect: false,
        togglePaletteOnly: false,
        showSelectionPalette: true,
        localStorageKey: false,
        appendTo: "body",
        maxSelectionSize: 7,
        cancelText: "cancel",
        chooseText: "choose",
        togglePaletteMoreText: "more",
        togglePaletteLessText: "less",
        clearText: "Clear Color Selection",
        noColorSelectedText: "No Color Selected",
        preferredFormat: false,
        className: "", // Deprecated - use containerClassName and replacerClassName instead.
        containerClassName: "",
        replacerClassName: "",
        showAlpha: false,
        theme: "sp-light",
        palette: [["#ffffff", "#000000", "#ff0000", "#ff8000", "#ffff00", "#008000", "#0000ff", "#4b0082", "#9400d3"]],
        selectionPalette: [],
        disabled: false,
        offset: null
    },
    spectrums = [],
    IE = !!/msie/i.exec( window.navigator.userAgent ),
    rgbaSupport = (function() {
        function contains( str, substr ) {
            return !!~('' + str).indexOf(substr);
        }

        var elem = document.createElement('div');
        var style = elem.style;
        style.cssText = 'background-color:rgba(0,0,0,.5)';
        return contains(style.backgroundColor, 'rgba') || contains(style.backgroundColor, 'hsla');
    })(),
    replaceInput = [
        "<div class='sp-replacer'>",
            "<div class='sp-preview'><div class='sp-preview-inner'></div></div>",
            "<div class='sp-dd'>&#9660;</div>",
        "</div>"
    ].join(''),
    markup = (function () {

        // IE does not support gradients with multiple stops, so we need to simulate
        //  that for the rainbow slider with 8 divs that each have a single gradient
        var gradientFix = "";
        if (IE) {
            for (var i = 1; i <= 6; i++) {
                gradientFix += "<div class='sp-" + i + "'></div>";
            }
        }

        return [
            "<div class='sp-container sp-hidden'>",
                "<div class='sp-palette-container'>",
                    "<div class='sp-palette sp-thumb sp-cf'></div>",
                    "<div class='sp-palette-button-container sp-cf'>",
                        "<button type='button' class='sp-palette-toggle'></button>",
                    "</div>",
                "</div>",
                "<div class='sp-picker-container'>",
                    "<div class='sp-top sp-cf'>",
                        "<div class='sp-fill'></div>",
                        "<div class='sp-top-inner'>",
                            "<div class='sp-color'>",
                                "<div class='sp-sat'>",
                                    "<div class='sp-val'>",
                                        "<div class='sp-dragger'></div>",
                                    "</div>",
                                "</div>",
                            "</div>",
                            "<div class='sp-clear sp-clear-display'>",
                            "</div>",
                            "<div class='sp-hue'>",
                                "<div class='sp-slider'></div>",
                                gradientFix,
                            "</div>",
                        "</div>",
                        "<div class='sp-alpha'><div class='sp-alpha-inner'><div class='sp-alpha-handle'></div></div></div>",
                    "</div>",
                    "<div class='sp-input-container sp-cf'>",
                        "<input class='sp-input' type='text' spellcheck='false'  />",
                    "</div>",
                    "<div class='sp-initial sp-thumb sp-cf'></div>",
                    "<div class='sp-button-container sp-cf'>",
                        "<a class='sp-cancel' href='#'></a>",
                        "<button type='button' class='sp-choose'></button>",
                    "</div>",
                "</div>",
            "</div>"
        ].join("");
    })();

    function paletteTemplate (p, color, className, opts) {
        var html = [];
        for (var i = 0; i < p.length; i++) {
            var current = p[i];
            if(current) {
                var tiny = tinycolor(current);
                var c = tiny.toHsl().l < 0.5 ? "sp-thumb-el sp-thumb-dark" : "sp-thumb-el sp-thumb-light";
                c += (tinycolor.equals(color, current)) ? " sp-thumb-active" : "";
                var formattedString = tiny.toString(opts.preferredFormat || "rgb");
                var swatchStyle = rgbaSupport ? ("background-color:" + tiny.toRgbString()) : "filter:" + tiny.toFilter();
                html.push('<span title="' + formattedString + '" data-color="' + tiny.toRgbString() + '" class="' + c + '"><span class="sp-thumb-inner" style="' + swatchStyle + ';" /></span>');
            } else {
                var cls = 'sp-clear-display';
                html.push($('<div />')
                    .append($('<span data-color="" style="background-color:transparent;" class="' + cls + '"></span>')
                        .attr('title', opts.noColorSelectedText)
                    )
                    .html()
                );
            }
        }
        return "<div class='sp-cf " + className + "'>" + html.join('') + "</div>";
    }

    function hideAll() {
        for (var i = 0; i < spectrums.length; i++) {
            if (spectrums[i]) {
                spectrums[i].hide();
            }
        }
    }

    function instanceOptions(o, callbackContext) {
        var opts = $.extend({}, defaultOpts, o);
        opts.callbacks = {
            'move': bind(opts.move, callbackContext),
            'change': bind(opts.change, callbackContext),
            'show': bind(opts.show, callbackContext),
            'hide': bind(opts.hide, callbackContext),
            'beforeShow': bind(opts.beforeShow, callbackContext)
        };

        return opts;
    }

    function spectrum(element, o) {

        var opts = instanceOptions(o, element),
            flat = opts.flat,
            showSelectionPalette = opts.showSelectionPalette,
            localStorageKey = opts.localStorageKey,
            theme = opts.theme,
            callbacks = opts.callbacks,
            resize = throttle(reflow, 10),
            visible = false,
            isDragging = false,
            dragWidth = 0,
            dragHeight = 0,
            dragHelperHeight = 0,
            slideHeight = 0,
            slideWidth = 0,
            alphaWidth = 0,
            alphaSlideHelperWidth = 0,
            slideHelperHeight = 0,
            currentHue = 0,
            currentSaturation = 0,
            currentValue = 0,
            currentAlpha = 1,
            palette = [],
            paletteArray = [],
            paletteLookup = {},
            selectionPalette = opts.selectionPalette.slice(0),
            maxSelectionSize = opts.maxSelectionSize,
            draggingClass = "sp-dragging",
            shiftMovementDirection = null;

        var doc = element.ownerDocument,
            body = doc.body,
            boundElement = $(element),
            disabled = false,
            container = $(markup, doc).addClass(theme),
            pickerContainer = container.find(".sp-picker-container"),
            dragger = container.find(".sp-color"),
            dragHelper = container.find(".sp-dragger"),
            slider = container.find(".sp-hue"),
            slideHelper = container.find(".sp-slider"),
            alphaSliderInner = container.find(".sp-alpha-inner"),
            alphaSlider = container.find(".sp-alpha"),
            alphaSlideHelper = container.find(".sp-alpha-handle"),
            textInput = container.find(".sp-input"),
            paletteContainer = container.find(".sp-palette"),
            initialColorContainer = container.find(".sp-initial"),
            cancelButton = container.find(".sp-cancel"),
            clearButton = container.find(".sp-clear"),
            chooseButton = container.find(".sp-choose"),
            toggleButton = container.find(".sp-palette-toggle"),
            isInput = boundElement.is("input"),
            isInputTypeColor = isInput && boundElement.attr("type") === "color" && inputTypeColorSupport(),
            shouldReplace = isInput && !flat,
            replacer = (shouldReplace) ? $(replaceInput).addClass(theme).addClass(opts.className).addClass(opts.replacerClassName) : $([]),
            offsetElement = (shouldReplace) ? replacer : boundElement,
            previewElement = replacer.find(".sp-preview-inner"),
            initialColor = opts.color || (isInput && boundElement.val()),
            colorOnShow = false,
            currentPreferredFormat = opts.preferredFormat,
            clickoutFiresChange = !opts.showButtons || opts.clickoutFiresChange,
            isEmpty = !initialColor,
            allowEmpty = opts.allowEmpty && !isInputTypeColor;

        function applyOptions() {

            if (opts.showPaletteOnly) {
                opts.showPalette = true;
            }

            toggleButton.text(opts.showPaletteOnly ? opts.togglePaletteMoreText : opts.togglePaletteLessText);

            if (opts.palette) {
                palette = opts.palette.slice(0);
                paletteArray = $.isArray(palette[0]) ? palette : [palette];
                paletteLookup = {};
                for (var i = 0; i < paletteArray.length; i++) {
                    for (var j = 0; j < paletteArray[i].length; j++) {
                        var rgb = tinycolor(paletteArray[i][j]).toRgbString();
                        paletteLookup[rgb] = true;
                    }
                }
            }

            container.toggleClass("sp-flat", flat);
            container.toggleClass("sp-input-disabled", !opts.showInput);
            container.toggleClass("sp-alpha-enabled", opts.showAlpha);
            container.toggleClass("sp-clear-enabled", allowEmpty);
            container.toggleClass("sp-buttons-disabled", !opts.showButtons);
            container.toggleClass("sp-palette-buttons-disabled", !opts.togglePaletteOnly);
            container.toggleClass("sp-palette-disabled", !opts.showPalette);
            container.toggleClass("sp-palette-only", opts.showPaletteOnly);
            container.toggleClass("sp-initial-disabled", !opts.showInitial);
            container.addClass(opts.className).addClass(opts.containerClassName);

            reflow();
        }

        function initialize() {

            if (IE) {
                container.find("*:not(input)").attr("unselectable", "on");
            }

            applyOptions();

            if (shouldReplace) {
                boundElement.after(replacer).hide();
            }

            if (!allowEmpty) {
                clearButton.hide();
            }

            if (flat) {
                boundElement.after(container).hide();
            }
            else {

                var appendTo = opts.appendTo === "parent" ? boundElement.parent() : $(opts.appendTo);
                if (appendTo.length !== 1) {
                    appendTo = $("body");
                }

                appendTo.append(container);
            }

            updateSelectionPaletteFromStorage();

            offsetElement.on("click.spectrum touchstart.spectrum", function (e) {
                if (!disabled) {
                    toggle();
                }

                e.stopPropagation();

                if (!$(e.target).is("input")) {
                    e.preventDefault();
                }
            });

            if(boundElement.is(":disabled") || (opts.disabled === true)) {
                disable();
            }

            // Prevent clicks from bubbling up to document.  This would cause it to be hidden.
            container.click(stopPropagation);

            // Handle user typed input
            textInput.change(setFromTextInput);
            textInput.on("paste", function () {
                setTimeout(setFromTextInput, 1);
            });
            textInput.keydown(function (e) { if (e.keyCode == 13) { setFromTextInput(); } });

            cancelButton.text(opts.cancelText);
            cancelButton.on("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                revert();
                hide();
            });

            clearButton.attr("title", opts.clearText);
            clearButton.on("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();
                isEmpty = true;
                move();

                if(flat) {
                    //for the flat style, this is a change event
                    updateOriginalInput(true);
                }
            });

            chooseButton.text(opts.chooseText);
            chooseButton.on("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();

                if (IE && textInput.is(":focus")) {
                    textInput.trigger('change');
                }

                if (isValid()) {
                    updateOriginalInput(true);
                    hide();
                }
            });

            toggleButton.text(opts.showPaletteOnly ? opts.togglePaletteMoreText : opts.togglePaletteLessText);
            toggleButton.on("click.spectrum", function (e) {
                e.stopPropagation();
                e.preventDefault();

                opts.showPaletteOnly = !opts.showPaletteOnly;

                // To make sure the Picker area is drawn on the right, next to the
                // Palette area (and not below the palette), first move the Palette
                // to the left to make space for the picker, plus 5px extra.
                // The 'applyOptions' function puts the whole container back into place
                // and takes care of the button-text and the sp-palette-only CSS class.
                if (!opts.showPaletteOnly && !flat) {
                    container.css('left', '-=' + (pickerContainer.outerWidth(true) + 5));
                }
                applyOptions();
            });

            draggable(alphaSlider, function (dragX, dragY, e) {
                currentAlpha = (dragX / alphaWidth);
                isEmpty = false;
                if (e.shiftKey) {
                    currentAlpha = Math.round(currentAlpha * 10) / 10;
                }

                move();
            }, dragStart, dragStop);

            draggable(slider, function (dragX, dragY) {
                currentHue = parseFloat(dragY / slideHeight);
                isEmpty = false;
                if (!opts.showAlpha) {
                    currentAlpha = 1;
                }
                move();
            }, dragStart, dragStop);

            draggable(dragger, function (dragX, dragY, e) {

                // shift+drag should snap the movement to either the x or y axis.
                if (!e.shiftKey) {
                    shiftMovementDirection = null;
                }
                else if (!shiftMovementDirection) {
                    var oldDragX = currentSaturation * dragWidth;
                    var oldDragY = dragHeight - (currentValue * dragHeight);
                    var furtherFromX = Math.abs(dragX - oldDragX) > Math.abs(dragY - oldDragY);

                    shiftMovementDirection = furtherFromX ? "x" : "y";
                }

                var setSaturation = !shiftMovementDirection || shiftMovementDirection === "x";
                var setValue = !shiftMovementDirection || shiftMovementDirection === "y";

                if (setSaturation) {
                    currentSaturation = parseFloat(dragX / dragWidth);
                }
                if (setValue) {
                    currentValue = parseFloat((dragHeight - dragY) / dragHeight);
                }

                isEmpty = false;
                if (!opts.showAlpha) {
                    currentAlpha = 1;
                }

                move();

            }, dragStart, dragStop);

            if (!!initialColor) {
                set(initialColor);

                // In case color was black - update the preview UI and set the format
                // since the set function will not run (default color is black).
                updateUI();
                currentPreferredFormat = opts.preferredFormat || tinycolor(initialColor).format;

                addColorToSelectionPalette(initialColor);
            }
            else {
                updateUI();
            }

            if (flat) {
                show();
            }

            function paletteElementClick(e) {
                if (e.data && e.data.ignore) {
                    set($(e.target).closest(".sp-thumb-el").data("color"));
                    move();
                }
                else {
                    set($(e.target).closest(".sp-thumb-el").data("color"));
                    move();

                    // If the picker is going to close immediately, a palette selection
                    // is a change.  Otherwise, it's a move only.
                    if (opts.hideAfterPaletteSelect) {
                        updateOriginalInput(true);
                        hide();
                    } else {
                        updateOriginalInput();
                    }
                }

                return false;
            }

            var paletteEvent = IE ? "mousedown.spectrum" : "click.spectrum touchstart.spectrum";
            paletteContainer.on(paletteEvent, ".sp-thumb-el", paletteElementClick);
            initialColorContainer.on(paletteEvent, ".sp-thumb-el:nth-child(1)", { ignore: true }, paletteElementClick);
        }

        function updateSelectionPaletteFromStorage() {

            if (localStorageKey && window.localStorage) {

                // Migrate old palettes over to new format.  May want to remove this eventually.
                try {
                    var oldPalette = window.localStorage[localStorageKey].split(",#");
                    if (oldPalette.length > 1) {
                        delete window.localStorage[localStorageKey];
                        $.each(oldPalette, function(i, c) {
                             addColorToSelectionPalette(c);
                        });
                    }
                }
                catch(e) { }

                try {
                    selectionPalette = window.localStorage[localStorageKey].split(";");
                }
                catch (e) { }
            }
        }

        function addColorToSelectionPalette(color) {
            if (showSelectionPalette) {
                var rgb = tinycolor(color).toRgbString();
                if (!paletteLookup[rgb] && $.inArray(rgb, selectionPalette) === -1) {
                    selectionPalette.push(rgb);
                    while(selectionPalette.length > maxSelectionSize) {
                        selectionPalette.shift();
                    }
                }

                if (localStorageKey && window.localStorage) {
                    try {
                        window.localStorage[localStorageKey] = selectionPalette.join(";");
                    }
                    catch(e) { }
                }
            }
        }

        function getUniqueSelectionPalette() {
            var unique = [];
            if (opts.showPalette) {
                for (var i = 0; i < selectionPalette.length; i++) {
                    var rgb = tinycolor(selectionPalette[i]).toRgbString();

                    if (!paletteLookup[rgb]) {
                        unique.push(selectionPalette[i]);
                    }
                }
            }

            return unique.reverse().slice(0, opts.maxSelectionSize);
        }

        function drawPalette() {

            var currentColor = get();

            var html = $.map(paletteArray, function (palette, i) {
                return paletteTemplate(palette, currentColor, "sp-palette-row sp-palette-row-" + i, opts);
            });

            updateSelectionPaletteFromStorage();

            if (selectionPalette) {
                html.push(paletteTemplate(getUniqueSelectionPalette(), currentColor, "sp-palette-row sp-palette-row-selection", opts));
            }

            paletteContainer.html(html.join(""));
        }

        function drawInitial() {
            if (opts.showInitial) {
                var initial = colorOnShow;
                var current = get();
                initialColorContainer.html(paletteTemplate([initial, current], current, "sp-palette-row-initial", opts));
            }
        }

        function dragStart() {
            if (dragHeight <= 0 || dragWidth <= 0 || slideHeight <= 0) {
                reflow();
            }
            isDragging = true;
            container.addClass(draggingClass);
            shiftMovementDirection = null;
            boundElement.trigger('dragstart.spectrum', [ get() ]);
        }

        function dragStop() {
            isDragging = false;
            container.removeClass(draggingClass);
            boundElement.trigger('dragstop.spectrum', [ get() ]);
        }

        function setFromTextInput() {

            var value = textInput.val();

            if ((value === null || value === "") && allowEmpty) {
                set(null);
                move();
                updateOriginalInput();
            }
            else {
                var tiny = tinycolor(value);
                if (tiny.isValid()) {
                    set(tiny);
                    move();
                    updateOriginalInput();
                }
                else {
                    textInput.addClass("sp-validation-error");
                }
            }
        }

        function toggle() {
            if (visible) {
                hide();
            }
            else {
                show();
            }
        }

        function show() {
            var event = $.Event('beforeShow.spectrum');

            if (visible) {
                reflow();
                return;
            }

            boundElement.trigger(event, [ get() ]);

            if (callbacks.beforeShow(get()) === false || event.isDefaultPrevented()) {
                return;
            }

            hideAll();
            visible = true;

            $(doc).on("keydown.spectrum", onkeydown);
            $(doc).on("click.spectrum", clickout);
            $(window).on("resize.spectrum", resize);
            replacer.addClass("sp-active");
            container.removeClass("sp-hidden");

            reflow();
            updateUI();

            colorOnShow = get();

            drawInitial();
            callbacks.show(colorOnShow);
            boundElement.trigger('show.spectrum', [ colorOnShow ]);
        }

        function onkeydown(e) {
            // Close on ESC
            if (e.keyCode === 27) {
                hide();
            }
        }

        function clickout(e) {
            // Return on right click.
            if (e.button == 2) { return; }

            // If a drag event was happening during the mouseup, don't hide
            // on click.
            if (isDragging) { return; }

            if (clickoutFiresChange) {
                updateOriginalInput(true);
            }
            else {
                revert();
            }
            hide();
        }

        function hide() {
            // Return if hiding is unnecessary
            if (!visible || flat) { return; }
            visible = false;

            $(doc).off("keydown.spectrum", onkeydown);
            $(doc).off("click.spectrum", clickout);
            $(window).off("resize.spectrum", resize);

            replacer.removeClass("sp-active");
            container.addClass("sp-hidden");

            callbacks.hide(get());
            boundElement.trigger('hide.spectrum', [ get() ]);
        }

        function revert() {
            set(colorOnShow, true);
            updateOriginalInput(true);
        }

        function set(color, ignoreFormatChange) {
            if (tinycolor.equals(color, get())) {
                // Update UI just in case a validation error needs
                // to be cleared.
                updateUI();
                return;
            }

            var newColor, newHsv;
            if (!color && allowEmpty) {
                isEmpty = true;
            } else {
                isEmpty = false;
                newColor = tinycolor(color);
                newHsv = newColor.toHsv();

                currentHue = (newHsv.h % 360) / 360;
                currentSaturation = newHsv.s;
                currentValue = newHsv.v;
                currentAlpha = newHsv.a;
            }
            updateUI();

            if (newColor && newColor.isValid() && !ignoreFormatChange) {
                currentPreferredFormat = opts.preferredFormat || newColor.getFormat();
            }
        }

        function get(opts) {
            opts = opts || { };

            if (allowEmpty && isEmpty) {
                return null;
            }

            return tinycolor.fromRatio({
                h: currentHue,
                s: currentSaturation,
                v: currentValue,
                a: Math.round(currentAlpha * 1000) / 1000
            }, { format: opts.format || currentPreferredFormat });
        }

        function isValid() {
            return !textInput.hasClass("sp-validation-error");
        }

        function move() {
            updateUI();

            callbacks.move(get());
            boundElement.trigger('move.spectrum', [ get() ]);
        }

        function updateUI() {

            textInput.removeClass("sp-validation-error");

            updateHelperLocations();

            // Update dragger background color (gradients take care of saturation and value).
            var flatColor = tinycolor.fromRatio({ h: currentHue, s: 1, v: 1 });
            dragger.css("background-color", flatColor.toHexString());

            // Get a format that alpha will be included in (hex and names ignore alpha)
            var format = currentPreferredFormat;
            if (currentAlpha < 1 && !(currentAlpha === 0 && format === "name")) {
                if (format === "hex" || format === "hex3" || format === "hex6" || format === "name") {
                    format = "rgb";
                }
            }

            var realColor = get({ format: format }),
                displayColor = '';

             //reset background info for preview element
            previewElement.removeClass("sp-clear-display");
            previewElement.css('background-color', 'transparent');

            if (!realColor && allowEmpty) {
                // Update the replaced elements background with icon indicating no color selection
                previewElement.addClass("sp-clear-display");
            }
            else {
                var realHex = realColor.toHexString(),
                    realRgb = realColor.toRgbString();

                // Update the replaced elements background color (with actual selected color)
                if (rgbaSupport || realColor.alpha === 1) {
                    previewElement.css("background-color", realRgb);
                }
                else {
                    previewElement.css("background-color", "transparent");
                    previewElement.css("filter", realColor.toFilter());
                }

                if (opts.showAlpha) {
                    var rgb = realColor.toRgb();
                    rgb.a = 0;
                    var realAlpha = tinycolor(rgb).toRgbString();
                    var gradient = "linear-gradient(left, " + realAlpha + ", " + realHex + ")";

                    if (IE) {
                        alphaSliderInner.css("filter", tinycolor(realAlpha).toFilter({ gradientType: 1 }, realHex));
                    }
                    else {
                        alphaSliderInner.css("background", "-webkit-" + gradient);
                        alphaSliderInner.css("background", "-moz-" + gradient);
                        alphaSliderInner.css("background", "-ms-" + gradient);
                        // Use current syntax gradient on unprefixed property.
                        alphaSliderInner.css("background",
                            "linear-gradient(to right, " + realAlpha + ", " + realHex + ")");
                    }
                }

                displayColor = realColor.toString(format);
            }

            // Update the text entry input as it changes happen
            if (opts.showInput) {
                textInput.val(displayColor);
            }

            if (opts.showPalette) {
                drawPalette();
            }

            drawInitial();
        }

        function updateHelperLocations() {
            var s = currentSaturation;
            var v = currentValue;

            if(allowEmpty && isEmpty) {
                //if selected color is empty, hide the helpers
                alphaSlideHelper.hide();
                slideHelper.hide();
                dragHelper.hide();
            }
            else {
                //make sure helpers are visible
                alphaSlideHelper.show();
                slideHelper.show();
                dragHelper.show();

                // Where to show the little circle in that displays your current selected color
                var dragX = s * dragWidth;
                var dragY = dragHeight - (v * dragHeight);
                dragX = Math.max(
                    -dragHelperHeight,
                    Math.min(dragWidth - dragHelperHeight, dragX - dragHelperHeight)
                );
                dragY = Math.max(
                    -dragHelperHeight,
                    Math.min(dragHeight - dragHelperHeight, dragY - dragHelperHeight)
                );
                dragHelper.css({
                    "top": dragY + "px",
                    "left": dragX + "px"
                });

                var alphaX = currentAlpha * alphaWidth;
                alphaSlideHelper.css({
                    "left": (alphaX - (alphaSlideHelperWidth / 2)) + "px"
                });

                // Where to show the bar that displays your current selected hue
                var slideY = (currentHue) * slideHeight;
                slideHelper.css({
                    "top": (slideY - slideHelperHeight) + "px"
                });
            }
        }

        function updateOriginalInput(fireCallback) {
            var color = get(),
                displayColor = '',
                hasChanged = !tinycolor.equals(color, colorOnShow);

            if (color) {
                displayColor = color.toString(currentPreferredFormat);
                // Update the selection palette with the current color
                addColorToSelectionPalette(color);
            }

            if (isInput) {
                boundElement.val(displayColor);
            }

            if (fireCallback && hasChanged) {
                callbacks.change(color);
                boundElement.trigger('change', [ color ]);
            }
        }

        function reflow() {
            if (!visible) {
                return; // Calculations would be useless and wouldn't be reliable anyways
            }
            dragWidth = dragger.width();
            dragHeight = dragger.height();
            dragHelperHeight = dragHelper.height();
            slideWidth = slider.width();
            slideHeight = slider.height();
            slideHelperHeight = slideHelper.height();
            alphaWidth = alphaSlider.width();
            alphaSlideHelperWidth = alphaSlideHelper.width();

            if (!flat) {
                container.css("position", "absolute");
                if (opts.offset) {
                    container.offset(opts.offset);
                } else {
                    container.offset(getOffset(container, offsetElement));
                }
            }

            updateHelperLocations();

            if (opts.showPalette) {
                drawPalette();
            }

            boundElement.trigger('reflow.spectrum');
        }

        function destroy() {
            boundElement.show();
            offsetElement.off("click.spectrum touchstart.spectrum");
            container.remove();
            replacer.remove();
            spectrums[spect.id] = null;
        }

        function option(optionName, optionValue) {
            if (optionName === undefined) {
                return $.extend({}, opts);
            }
            if (optionValue === undefined) {
                return opts[optionName];
            }

            opts[optionName] = optionValue;

            if (optionName === "preferredFormat") {
                currentPreferredFormat = opts.preferredFormat;
            }
            applyOptions();
        }

        function enable() {
            disabled = false;
            boundElement.attr("disabled", false);
            offsetElement.removeClass("sp-disabled");
        }

        function disable() {
            hide();
            disabled = true;
            boundElement.attr("disabled", true);
            offsetElement.addClass("sp-disabled");
        }

        function setOffset(coord) {
            opts.offset = coord;
            reflow();
        }

        initialize();

        var spect = {
            show: show,
            hide: hide,
            toggle: toggle,
            reflow: reflow,
            option: option,
            enable: enable,
            disable: disable,
            offset: setOffset,
            set: function (c) {
                set(c);
                updateOriginalInput();
            },
            get: get,
            destroy: destroy,
            container: container
        };

        spect.id = spectrums.push(spect) - 1;

        return spect;
    }

    /**
    * checkOffset - get the offset below/above and left/right element depending on screen position
    * Thanks https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.datepicker.js
    */
    function getOffset(picker, input) {
        var extraY = 0;
        var dpWidth = picker.outerWidth();
        var dpHeight = picker.outerHeight();
        var inputHeight = input.outerHeight();
        var doc = picker[0].ownerDocument;
        var docElem = doc.documentElement;
        var viewWidth = docElem.clientWidth + $(doc).scrollLeft();
        var viewHeight = docElem.clientHeight + $(doc).scrollTop();
        var offset = input.offset();
        var offsetLeft = offset.left;
        var offsetTop = offset.top;

        offsetTop += inputHeight;

        offsetLeft -=
            Math.min(offsetLeft, (offsetLeft + dpWidth > viewWidth && viewWidth > dpWidth) ?
            Math.abs(offsetLeft + dpWidth - viewWidth) : 0);

        offsetTop -=
            Math.min(offsetTop, ((offsetTop + dpHeight > viewHeight && viewHeight > dpHeight) ?
            Math.abs(dpHeight + inputHeight - extraY) : extraY));

        return {
            top: offsetTop,
            bottom: offset.bottom,
            left: offsetLeft,
            right: offset.right,
            width: offset.width,
            height: offset.height
        };
    }

    /**
    * noop - do nothing
    */
    function noop() {

    }

    /**
    * stopPropagation - makes the code only doing this a little easier to read in line
    */
    function stopPropagation(e) {
        e.stopPropagation();
    }

    /**
    * Create a function bound to a given object
    * Thanks to underscore.js
    */
    function bind(func, obj) {
        var slice = Array.prototype.slice;
        var args = slice.call(arguments, 2);
        return function () {
            return func.apply(obj, args.concat(slice.call(arguments)));
        };
    }

    /**
    * Lightweight drag helper.  Handles containment within the element, so that
    * when dragging, the x is within [0,element.width] and y is within [0,element.height]
    */
    function draggable(element, onmove, onstart, onstop) {
        onmove = onmove || function () { };
        onstart = onstart || function () { };
        onstop = onstop || function () { };
        var doc = document;
        var dragging = false;
        var offset = {};
        var maxHeight = 0;
        var maxWidth = 0;
        var hasTouch = ('ontouchstart' in window);

        var duringDragEvents = {};
        duringDragEvents["selectstart"] = prevent;
        duringDragEvents["dragstart"] = prevent;
        duringDragEvents["touchmove mousemove"] = move;
        duringDragEvents["touchend mouseup"] = stop;

        function prevent(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.returnValue = false;
        }

        function move(e) {
            if (dragging) {
                // Mouseup happened outside of window
                if (IE && doc.documentMode < 9 && !e.button) {
                    return stop();
                }

                var t0 = e.originalEvent && e.originalEvent.touches && e.originalEvent.touches[0];
                var pageX = t0 && t0.pageX || e.pageX;
                var pageY = t0 && t0.pageY || e.pageY;

                var dragX = Math.max(0, Math.min(pageX - offset.left, maxWidth));
                var dragY = Math.max(0, Math.min(pageY - offset.top, maxHeight));

                if (hasTouch) {
                    // Stop scrolling in iOS
                    prevent(e);
                }

                onmove.apply(element, [dragX, dragY, e]);
            }
        }

        function start(e) {
            var rightclick = (e.which) ? (e.which == 3) : (e.button == 2);

            if (!rightclick && !dragging) {
                if (onstart.apply(element, arguments) !== false) {
                    dragging = true;
                    maxHeight = $(element).height();
                    maxWidth = $(element).width();
                    offset = $(element).offset();

                    $(doc).on(duringDragEvents);
                    $(doc.body).addClass("sp-dragging");

                    move(e);

                    prevent(e);
                }
            }
        }

        function stop() {
            if (dragging) {
                $(doc).off(duringDragEvents);
                $(doc.body).removeClass("sp-dragging");

                // Wait a tick before notifying observers to allow the click event
                // to fire in Chrome.
                setTimeout(function() {
                    onstop.apply(element, arguments);
                }, 0);
            }
            dragging = false;
        }

        $(element).on("touchstart mousedown", start);
    }

    function throttle(func, wait, debounce) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var throttler = function () {
                timeout = null;
                func.apply(context, args);
            };
            if (debounce) clearTimeout(timeout);
            if (debounce || !timeout) timeout = setTimeout(throttler, wait);
        };
    }

    function inputTypeColorSupport() {
        return $.fn.spectrum.inputTypeColorSupport();
    }

    /**
    * Define a jQuery plugin
    */
    var dataID = "spectrum.id";
    $.fn.spectrum = function (opts, extra) {

        if (typeof opts == "string") {

            var returnValue = this;
            var args = Array.prototype.slice.call( arguments, 1 );

            this.each(function () {
                var spect = spectrums[$(this).data(dataID)];
                if (spect) {
                    var method = spect[opts];
                    if (!method) {
                        throw new Error( "Spectrum: no such method: '" + opts + "'" );
                    }

                    if (opts == "get") {
                        returnValue = spect.get();
                    }
                    else if (opts == "container") {
                        returnValue = spect.container;
                    }
                    else if (opts == "option") {
                        returnValue = spect.option.apply(spect, args);
                    }
                    else if (opts == "destroy") {
                        spect.destroy();
                        $(this).removeData(dataID);
                    }
                    else {
                        method.apply(spect, args);
                    }
                }
            });

            return returnValue;
        }

        // Initializing a new instance of spectrum
        return this.spectrum("destroy").each(function () {
            var options = $.extend({}, $(this).data(), opts);
            var spect = spectrum(this, options);
            $(this).data(dataID, spect.id);
        });
    };

    $.fn.spectrum.load = true;
    $.fn.spectrum.loadOpts = {};
    $.fn.spectrum.draggable = draggable;
    $.fn.spectrum.defaults = defaultOpts;
    $.fn.spectrum.inputTypeColorSupport = function inputTypeColorSupport() {
        if (typeof inputTypeColorSupport._cachedResult === "undefined") {
            var colorInput = $("<input type='color'/>")[0]; // if color element is supported, value will default to not null
            inputTypeColorSupport._cachedResult = colorInput.type === "color" && colorInput.value !== "";
        }
        return inputTypeColorSupport._cachedResult;
    };

    $.spectrum = { };
    $.spectrum.localization = { };
    $.spectrum.palettes = { };

    $.fn.spectrum.processNativeColorInputs = function () {
        var colorInputs = $("input[type=color]");
        if (colorInputs.length && !inputTypeColorSupport()) {
            colorInputs.spectrum({
                preferredFormat: "hex6"
            });
        }
    };

    // TinyColor v1.1.2
    // https://github.com/bgrins/TinyColor
    // Brian Grinstead, MIT License

    (function() {

    var trimLeft = /^[\s,#]+/,
        trimRight = /\s+$/,
        tinyCounter = 0,
        math = Math,
        mathRound = math.round,
        mathMin = math.min,
        mathMax = math.max,
        mathRandom = math.random;

    var tinycolor = function(color, opts) {

        color = (color) ? color : '';
        opts = opts || { };

        // If input is already a tinycolor, return itself
        if (color instanceof tinycolor) {
           return color;
        }
        // If we are called as a function, call using new instead
        if (!(this instanceof tinycolor)) {
            return new tinycolor(color, opts);
        }

        var rgb = inputToRGB(color);
        this._originalInput = color,
        this._r = rgb.r,
        this._g = rgb.g,
        this._b = rgb.b,
        this._a = rgb.a,
        this._roundA = mathRound(1000 * this._a) / 1000,
        this._format = opts.format || rgb.format;
        this._gradientType = opts.gradientType;

        // Don't let the range of [0,255] come back in [0,1].
        // Potentially lose a little bit of precision here, but will fix issues where
        // .5 gets interpreted as half of the total, instead of half of 1
        // If it was supposed to be 128, this was already taken care of by `inputToRgb`
        if (this._r < 1) { this._r = mathRound(this._r); }
        if (this._g < 1) { this._g = mathRound(this._g); }
        if (this._b < 1) { this._b = mathRound(this._b); }

        this._ok = rgb.ok;
        this._tc_id = tinyCounter++;
    };

    tinycolor.prototype = {
        isDark: function() {
            return this.getBrightness() < 128;
        },
        isLight: function() {
            return !this.isDark();
        },
        isValid: function() {
            return this._ok;
        },
        getOriginalInput: function() {
          return this._originalInput;
        },
        getFormat: function() {
            return this._format;
        },
        getAlpha: function() {
            return this._a;
        },
        getBrightness: function() {
            var rgb = this.toRgb();
            return (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
        },
        setAlpha: function(value) {
            this._a = boundAlpha(value);
            this._roundA = mathRound(1000 * this._a) / 1000;
            return this;
        },
        toHsv: function() {
            var hsv = rgbToHsv(this._r, this._g, this._b);
            return { h: hsv.h * 360, s: hsv.s, v: hsv.v, a: this._a };
        },
        toHsvString: function() {
            var hsv = rgbToHsv(this._r, this._g, this._b);
            var h = mathRound(hsv.h * 360), s = mathRound(hsv.s * 100), v = mathRound(hsv.v * 100);
            return (this._a == 1) ?
              "hsv("  + h + ", " + s + "%, " + v + "%)" :
              "hsva(" + h + ", " + s + "%, " + v + "%, "+ this._roundA + ")";
        },
        toHsl: function() {
            var hsl = rgbToHsl(this._r, this._g, this._b);
            return { h: hsl.h * 360, s: hsl.s, l: hsl.l, a: this._a };
        },
        toHslString: function() {
            var hsl = rgbToHsl(this._r, this._g, this._b);
            var h = mathRound(hsl.h * 360), s = mathRound(hsl.s * 100), l = mathRound(hsl.l * 100);
            return (this._a == 1) ?
              "hsl("  + h + ", " + s + "%, " + l + "%)" :
              "hsla(" + h + ", " + s + "%, " + l + "%, "+ this._roundA + ")";
        },
        toHex: function(allow3Char) {
            return rgbToHex(this._r, this._g, this._b, allow3Char);
        },
        toHexString: function(allow3Char) {
            return '#' + this.toHex(allow3Char);
        },
        toHex8: function() {
            return rgbaToHex(this._r, this._g, this._b, this._a);
        },
        toHex8String: function() {
            return '#' + this.toHex8();
        },
        toRgb: function() {
            return { r: mathRound(this._r), g: mathRound(this._g), b: mathRound(this._b), a: this._a };
        },
        toRgbString: function() {
            return (this._a == 1) ?
              "rgb("  + mathRound(this._r) + ", " + mathRound(this._g) + ", " + mathRound(this._b) + ")" :
              "rgba(" + mathRound(this._r) + ", " + mathRound(this._g) + ", " + mathRound(this._b) + ", " + this._roundA + ")";
        },
        toPercentageRgb: function() {
            return { r: mathRound(bound01(this._r, 255) * 100) + "%", g: mathRound(bound01(this._g, 255) * 100) + "%", b: mathRound(bound01(this._b, 255) * 100) + "%", a: this._a };
        },
        toPercentageRgbString: function() {
            return (this._a == 1) ?
              "rgb("  + mathRound(bound01(this._r, 255) * 100) + "%, " + mathRound(bound01(this._g, 255) * 100) + "%, " + mathRound(bound01(this._b, 255) * 100) + "%)" :
              "rgba(" + mathRound(bound01(this._r, 255) * 100) + "%, " + mathRound(bound01(this._g, 255) * 100) + "%, " + mathRound(bound01(this._b, 255) * 100) + "%, " + this._roundA + ")";
        },
        toName: function() {
            if (this._a === 0) {
                return "transparent";
            }

            if (this._a < 1) {
                return false;
            }

            return hexNames[rgbToHex(this._r, this._g, this._b, true)] || false;
        },
        toFilter: function(secondColor) {
            var hex8String = '#' + rgbaToHex(this._r, this._g, this._b, this._a);
            var secondHex8String = hex8String;
            var gradientType = this._gradientType ? "GradientType = 1, " : "";

            if (secondColor) {
                var s = tinycolor(secondColor);
                secondHex8String = s.toHex8String();
            }

            return "progid:DXImageTransform.Microsoft.gradient("+gradientType+"startColorstr="+hex8String+",endColorstr="+secondHex8String+")";
        },
        toString: function(format) {
            var formatSet = !!format;
            format = format || this._format;

            var formattedString = false;
            var hasAlpha = this._a < 1 && this._a >= 0;
            var needsAlphaFormat = !formatSet && hasAlpha && (format === "hex" || format === "hex6" || format === "hex3" || format === "name");

            if (needsAlphaFormat) {
                // Special case for "transparent", all other non-alpha formats
                // will return rgba when there is transparency.
                if (format === "name" && this._a === 0) {
                    return this.toName();
                }
                return this.toRgbString();
            }
            if (format === "rgb") {
                formattedString = this.toRgbString();
            }
            if (format === "prgb") {
                formattedString = this.toPercentageRgbString();
            }
            if (format === "hex" || format === "hex6") {
                formattedString = this.toHexString();
            }
            if (format === "hex3") {
                formattedString = this.toHexString(true);
            }
            if (format === "hex8") {
                formattedString = this.toHex8String();
            }
            if (format === "name") {
                formattedString = this.toName();
            }
            if (format === "hsl") {
                formattedString = this.toHslString();
            }
            if (format === "hsv") {
                formattedString = this.toHsvString();
            }

            return formattedString || this.toHexString();
        },

        _applyModification: function(fn, args) {
            var color = fn.apply(null, [this].concat([].slice.call(args)));
            this._r = color._r;
            this._g = color._g;
            this._b = color._b;
            this.setAlpha(color._a);
            return this;
        },
        lighten: function() {
            return this._applyModification(lighten, arguments);
        },
        brighten: function() {
            return this._applyModification(brighten, arguments);
        },
        darken: function() {
            return this._applyModification(darken, arguments);
        },
        desaturate: function() {
            return this._applyModification(desaturate, arguments);
        },
        saturate: function() {
            return this._applyModification(saturate, arguments);
        },
        greyscale: function() {
            return this._applyModification(greyscale, arguments);
        },
        spin: function() {
            return this._applyModification(spin, arguments);
        },

        _applyCombination: function(fn, args) {
            return fn.apply(null, [this].concat([].slice.call(args)));
        },
        analogous: function() {
            return this._applyCombination(analogous, arguments);
        },
        complement: function() {
            return this._applyCombination(complement, arguments);
        },
        monochromatic: function() {
            return this._applyCombination(monochromatic, arguments);
        },
        splitcomplement: function() {
            return this._applyCombination(splitcomplement, arguments);
        },
        triad: function() {
            return this._applyCombination(triad, arguments);
        },
        tetrad: function() {
            return this._applyCombination(tetrad, arguments);
        }
    };

    // If input is an object, force 1 into "1.0" to handle ratios properly
    // String input requires "1.0" as input, so 1 will be treated as 1
    tinycolor.fromRatio = function(color, opts) {
        if (typeof color == "object") {
            var newColor = {};
            for (var i in color) {
                if (color.hasOwnProperty(i)) {
                    if (i === "a") {
                        newColor[i] = color[i];
                    }
                    else {
                        newColor[i] = convertToPercentage(color[i]);
                    }
                }
            }
            color = newColor;
        }

        return tinycolor(color, opts);
    };

    // Given a string or object, convert that input to RGB
    // Possible string inputs:
    //
    //     "red"
    //     "#f00" or "f00"
    //     "#ff0000" or "ff0000"
    //     "#ff000000" or "ff000000"
    //     "rgb 255 0 0" or "rgb (255, 0, 0)"
    //     "rgb 1.0 0 0" or "rgb (1, 0, 0)"
    //     "rgba (255, 0, 0, 1)" or "rgba 255, 0, 0, 1"
    //     "rgba (1.0, 0, 0, 1)" or "rgba 1.0, 0, 0, 1"
    //     "hsl(0, 100%, 50%)" or "hsl 0 100% 50%"
    //     "hsla(0, 100%, 50%, 1)" or "hsla 0 100% 50%, 1"
    //     "hsv(0, 100%, 100%)" or "hsv 0 100% 100%"
    //
    function inputToRGB(color) {

        var rgb = { r: 0, g: 0, b: 0 };
        var a = 1;
        var ok = false;
        var format = false;

        if (typeof color == "string") {
            color = stringInputToObject(color);
        }

        if (typeof color == "object") {
            if (color.hasOwnProperty("r") && color.hasOwnProperty("g") && color.hasOwnProperty("b")) {
                rgb = rgbToRgb(color.r, color.g, color.b);
                ok = true;
                format = String(color.r).substr(-1) === "%" ? "prgb" : "rgb";
            }
            else if (color.hasOwnProperty("h") && color.hasOwnProperty("s") && color.hasOwnProperty("v")) {
                color.s = convertToPercentage(color.s);
                color.v = convertToPercentage(color.v);
                rgb = hsvToRgb(color.h, color.s, color.v);
                ok = true;
                format = "hsv";
            }
            else if (color.hasOwnProperty("h") && color.hasOwnProperty("s") && color.hasOwnProperty("l")) {
                color.s = convertToPercentage(color.s);
                color.l = convertToPercentage(color.l);
                rgb = hslToRgb(color.h, color.s, color.l);
                ok = true;
                format = "hsl";
            }

            if (color.hasOwnProperty("a")) {
                a = color.a;
            }
        }

        a = boundAlpha(a);

        return {
            ok: ok,
            format: color.format || format,
            r: mathMin(255, mathMax(rgb.r, 0)),
            g: mathMin(255, mathMax(rgb.g, 0)),
            b: mathMin(255, mathMax(rgb.b, 0)),
            a: a
        };
    }


    // Conversion Functions
    // --------------------

    // `rgbToHsl`, `rgbToHsv`, `hslToRgb`, `hsvToRgb` modified from:
    // <http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript>

    // `rgbToRgb`
    // Handle bounds / percentage checking to conform to CSS color spec
    // <http://www.w3.org/TR/css3-color/>
    // *Assumes:* r, g, b in [0, 255] or [0, 1]
    // *Returns:* { r, g, b } in [0, 255]
    function rgbToRgb(r, g, b){
        return {
            r: bound01(r, 255) * 255,
            g: bound01(g, 255) * 255,
            b: bound01(b, 255) * 255
        };
    }

    // `rgbToHsl`
    // Converts an RGB color value to HSL.
    // *Assumes:* r, g, and b are contained in [0, 255] or [0, 1]
    // *Returns:* { h, s, l } in [0,1]
    function rgbToHsl(r, g, b) {

        r = bound01(r, 255);
        g = bound01(g, 255);
        b = bound01(b, 255);

        var max = mathMax(r, g, b), min = mathMin(r, g, b);
        var h, s, l = (max + min) / 2;

        if(max == min) {
            h = s = 0; // achromatic
        }
        else {
            var d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch(max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }

            h /= 6;
        }

        return { h: h, s: s, l: l };
    }

    // `hslToRgb`
    // Converts an HSL color value to RGB.
    // *Assumes:* h is contained in [0, 1] or [0, 360] and s and l are contained [0, 1] or [0, 100]
    // *Returns:* { r, g, b } in the set [0, 255]
    function hslToRgb(h, s, l) {
        var r, g, b;

        h = bound01(h, 360);
        s = bound01(s, 100);
        l = bound01(l, 100);

        function hue2rgb(p, q, t) {
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        if(s === 0) {
            r = g = b = l; // achromatic
        }
        else {
            var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            var p = 2 * l - q;
            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }

        return { r: r * 255, g: g * 255, b: b * 255 };
    }

    // `rgbToHsv`
    // Converts an RGB color value to HSV
    // *Assumes:* r, g, and b are contained in the set [0, 255] or [0, 1]
    // *Returns:* { h, s, v } in [0,1]
    function rgbToHsv(r, g, b) {

        r = bound01(r, 255);
        g = bound01(g, 255);
        b = bound01(b, 255);

        var max = mathMax(r, g, b), min = mathMin(r, g, b);
        var h, s, v = max;

        var d = max - min;
        s = max === 0 ? 0 : d / max;

        if(max == min) {
            h = 0; // achromatic
        }
        else {
            switch(max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return { h: h, s: s, v: v };
    }

    // `hsvToRgb`
    // Converts an HSV color value to RGB.
    // *Assumes:* h is contained in [0, 1] or [0, 360] and s and v are contained in [0, 1] or [0, 100]
    // *Returns:* { r, g, b } in the set [0, 255]
     function hsvToRgb(h, s, v) {

        h = bound01(h, 360) * 6;
        s = bound01(s, 100);
        v = bound01(v, 100);

        var i = math.floor(h),
            f = h - i,
            p = v * (1 - s),
            q = v * (1 - f * s),
            t = v * (1 - (1 - f) * s),
            mod = i % 6,
            r = [v, q, p, p, t, v][mod],
            g = [t, v, v, q, p, p][mod],
            b = [p, p, t, v, v, q][mod];

        return { r: r * 255, g: g * 255, b: b * 255 };
    }

    // `rgbToHex`
    // Converts an RGB color to hex
    // Assumes r, g, and b are contained in the set [0, 255]
    // Returns a 3 or 6 character hex
    function rgbToHex(r, g, b, allow3Char) {

        var hex = [
            pad2(mathRound(r).toString(16)),
            pad2(mathRound(g).toString(16)),
            pad2(mathRound(b).toString(16))
        ];

        // Return a 3 character hex if possible
        if (allow3Char && hex[0].charAt(0) == hex[0].charAt(1) && hex[1].charAt(0) == hex[1].charAt(1) && hex[2].charAt(0) == hex[2].charAt(1)) {
            return hex[0].charAt(0) + hex[1].charAt(0) + hex[2].charAt(0);
        }

        return hex.join("");
    }
        // `rgbaToHex`
        // Converts an RGBA color plus alpha transparency to hex
        // Assumes r, g, b and a are contained in the set [0, 255]
        // Returns an 8 character hex
        function rgbaToHex(r, g, b, a) {

            var hex = [
                pad2(convertDecimalToHex(a)),
                pad2(mathRound(r).toString(16)),
                pad2(mathRound(g).toString(16)),
                pad2(mathRound(b).toString(16))
            ];

            return hex.join("");
        }

    // `equals`
    // Can be called with any tinycolor input
    tinycolor.equals = function (color1, color2) {
        if (!color1 || !color2) { return false; }
        return tinycolor(color1).toRgbString() == tinycolor(color2).toRgbString();
    };
    tinycolor.random = function() {
        return tinycolor.fromRatio({
            r: mathRandom(),
            g: mathRandom(),
            b: mathRandom()
        });
    };


    // Modification Functions
    // ----------------------
    // Thanks to less.js for some of the basics here
    // <https://github.com/cloudhead/less.js/blob/master/lib/less/functions.js>

    function desaturate(color, amount) {
        amount = (amount === 0) ? 0 : (amount || 10);
        var hsl = tinycolor(color).toHsl();
        hsl.s -= amount / 100;
        hsl.s = clamp01(hsl.s);
        return tinycolor(hsl);
    }

    function saturate(color, amount) {
        amount = (amount === 0) ? 0 : (amount || 10);
        var hsl = tinycolor(color).toHsl();
        hsl.s += amount / 100;
        hsl.s = clamp01(hsl.s);
        return tinycolor(hsl);
    }

    function greyscale(color) {
        return tinycolor(color).desaturate(100);
    }

    function lighten (color, amount) {
        amount = (amount === 0) ? 0 : (amount || 10);
        var hsl = tinycolor(color).toHsl();
        hsl.l += amount / 100;
        hsl.l = clamp01(hsl.l);
        return tinycolor(hsl);
    }

    function brighten(color, amount) {
        amount = (amount === 0) ? 0 : (amount || 10);
        var rgb = tinycolor(color).toRgb();
        rgb.r = mathMax(0, mathMin(255, rgb.r - mathRound(255 * - (amount / 100))));
        rgb.g = mathMax(0, mathMin(255, rgb.g - mathRound(255 * - (amount / 100))));
        rgb.b = mathMax(0, mathMin(255, rgb.b - mathRound(255 * - (amount / 100))));
        return tinycolor(rgb);
    }

    function darken (color, amount) {
        amount = (amount === 0) ? 0 : (amount || 10);
        var hsl = tinycolor(color).toHsl();
        hsl.l -= amount / 100;
        hsl.l = clamp01(hsl.l);
        return tinycolor(hsl);
    }

    // Spin takes a positive or negative amount within [-360, 360] indicating the change of hue.
    // Values outside of this range will be wrapped into this range.
    function spin(color, amount) {
        var hsl = tinycolor(color).toHsl();
        var hue = (mathRound(hsl.h) + amount) % 360;
        hsl.h = hue < 0 ? 360 + hue : hue;
        return tinycolor(hsl);
    }

    // Combination Functions
    // ---------------------
    // Thanks to jQuery xColor for some of the ideas behind these
    // <https://github.com/infusion/jQuery-xcolor/blob/master/jquery.xcolor.js>

    function complement(color) {
        var hsl = tinycolor(color).toHsl();
        hsl.h = (hsl.h + 180) % 360;
        return tinycolor(hsl);
    }

    function triad(color) {
        var hsl = tinycolor(color).toHsl();
        var h = hsl.h;
        return [
            tinycolor(color),
            tinycolor({ h: (h + 120) % 360, s: hsl.s, l: hsl.l }),
            tinycolor({ h: (h + 240) % 360, s: hsl.s, l: hsl.l })
        ];
    }

    function tetrad(color) {
        var hsl = tinycolor(color).toHsl();
        var h = hsl.h;
        return [
            tinycolor(color),
            tinycolor({ h: (h + 90) % 360, s: hsl.s, l: hsl.l }),
            tinycolor({ h: (h + 180) % 360, s: hsl.s, l: hsl.l }),
            tinycolor({ h: (h + 270) % 360, s: hsl.s, l: hsl.l })
        ];
    }

    function splitcomplement(color) {
        var hsl = tinycolor(color).toHsl();
        var h = hsl.h;
        return [
            tinycolor(color),
            tinycolor({ h: (h + 72) % 360, s: hsl.s, l: hsl.l}),
            tinycolor({ h: (h + 216) % 360, s: hsl.s, l: hsl.l})
        ];
    }

    function analogous(color, results, slices) {
        results = results || 6;
        slices = slices || 30;

        var hsl = tinycolor(color).toHsl();
        var part = 360 / slices;
        var ret = [tinycolor(color)];

        for (hsl.h = ((hsl.h - (part * results >> 1)) + 720) % 360; --results; ) {
            hsl.h = (hsl.h + part) % 360;
            ret.push(tinycolor(hsl));
        }
        return ret;
    }

    function monochromatic(color, results) {
        results = results || 6;
        var hsv = tinycolor(color).toHsv();
        var h = hsv.h, s = hsv.s, v = hsv.v;
        var ret = [];
        var modification = 1 / results;

        while (results--) {
            ret.push(tinycolor({ h: h, s: s, v: v}));
            v = (v + modification) % 1;
        }

        return ret;
    }

    // Utility Functions
    // ---------------------

    tinycolor.mix = function(color1, color2, amount) {
        amount = (amount === 0) ? 0 : (amount || 50);

        var rgb1 = tinycolor(color1).toRgb();
        var rgb2 = tinycolor(color2).toRgb();

        var p = amount / 100;
        var w = p * 2 - 1;
        var a = rgb2.a - rgb1.a;

        var w1;

        if (w * a == -1) {
            w1 = w;
        } else {
            w1 = (w + a) / (1 + w * a);
        }

        w1 = (w1 + 1) / 2;

        var w2 = 1 - w1;

        var rgba = {
            r: rgb2.r * w1 + rgb1.r * w2,
            g: rgb2.g * w1 + rgb1.g * w2,
            b: rgb2.b * w1 + rgb1.b * w2,
            a: rgb2.a * p  + rgb1.a * (1 - p)
        };

        return tinycolor(rgba);
    };


    // Readability Functions
    // ---------------------
    // <http://www.w3.org/TR/AERT#color-contrast>

    // `readability`
    // Analyze the 2 colors and returns an object with the following properties:
    //    `brightness`: difference in brightness between the two colors
    //    `color`: difference in color/hue between the two colors
    tinycolor.readability = function(color1, color2) {
        var c1 = tinycolor(color1);
        var c2 = tinycolor(color2);
        var rgb1 = c1.toRgb();
        var rgb2 = c2.toRgb();
        var brightnessA = c1.getBrightness();
        var brightnessB = c2.getBrightness();
        var colorDiff = (
            Math.max(rgb1.r, rgb2.r) - Math.min(rgb1.r, rgb2.r) +
            Math.max(rgb1.g, rgb2.g) - Math.min(rgb1.g, rgb2.g) +
            Math.max(rgb1.b, rgb2.b) - Math.min(rgb1.b, rgb2.b)
        );

        return {
            brightness: Math.abs(brightnessA - brightnessB),
            color: colorDiff
        };
    };

    // `readable`
    // http://www.w3.org/TR/AERT#color-contrast
    // Ensure that foreground and background color combinations provide sufficient contrast.
    // *Example*
    //    tinycolor.isReadable("#000", "#111") => false
    tinycolor.isReadable = function(color1, color2) {
        var readability = tinycolor.readability(color1, color2);
        return readability.brightness > 125 && readability.color > 500;
    };

    // `mostReadable`
    // Given a base color and a list of possible foreground or background
    // colors for that base, returns the most readable color.
    // *Example*
    //    tinycolor.mostReadable("#123", ["#fff", "#000"]) => "#000"
    tinycolor.mostReadable = function(baseColor, colorList) {
        var bestColor = null;
        var bestScore = 0;
        var bestIsReadable = false;
        for (var i=0; i < colorList.length; i++) {

            // We normalize both around the "acceptable" breaking point,
            // but rank brightness constrast higher than hue.

            var readability = tinycolor.readability(baseColor, colorList[i]);
            var readable = readability.brightness > 125 && readability.color > 500;
            var score = 3 * (readability.brightness / 125) + (readability.color / 500);

            if ((readable && ! bestIsReadable) ||
                (readable && bestIsReadable && score > bestScore) ||
                ((! readable) && (! bestIsReadable) && score > bestScore)) {
                bestIsReadable = readable;
                bestScore = score;
                bestColor = tinycolor(colorList[i]);
            }
        }
        return bestColor;
    };


    // Big List of Colors
    // ------------------
    // <http://www.w3.org/TR/css3-color/#svg-color>
    var names = tinycolor.names = {
        aliceblue: "f0f8ff",
        antiquewhite: "faebd7",
        aqua: "0ff",
        aquamarine: "7fffd4",
        azure: "f0ffff",
        beige: "f5f5dc",
        bisque: "ffe4c4",
        black: "000",
        blanchedalmond: "ffebcd",
        blue: "00f",
        blueviolet: "8a2be2",
        brown: "a52a2a",
        burlywood: "deb887",
        burntsienna: "ea7e5d",
        cadetblue: "5f9ea0",
        chartreuse: "7fff00",
        chocolate: "d2691e",
        coral: "ff7f50",
        cornflowerblue: "6495ed",
        cornsilk: "fff8dc",
        crimson: "dc143c",
        cyan: "0ff",
        darkblue: "00008b",
        darkcyan: "008b8b",
        darkgoldenrod: "b8860b",
        darkgray: "a9a9a9",
        darkgreen: "006400",
        darkgrey: "a9a9a9",
        darkkhaki: "bdb76b",
        darkmagenta: "8b008b",
        darkolivegreen: "556b2f",
        darkorange: "ff8c00",
        darkorchid: "9932cc",
        darkred: "8b0000",
        darksalmon: "e9967a",
        darkseagreen: "8fbc8f",
        darkslateblue: "483d8b",
        darkslategray: "2f4f4f",
        darkslategrey: "2f4f4f",
        darkturquoise: "00ced1",
        darkviolet: "9400d3",
        deeppink: "ff1493",
        deepskyblue: "00bfff",
        dimgray: "696969",
        dimgrey: "696969",
        dodgerblue: "1e90ff",
        firebrick: "b22222",
        floralwhite: "fffaf0",
        forestgreen: "228b22",
        fuchsia: "f0f",
        gainsboro: "dcdcdc",
        ghostwhite: "f8f8ff",
        gold: "ffd700",
        goldenrod: "daa520",
        gray: "808080",
        green: "008000",
        greenyellow: "adff2f",
        grey: "808080",
        honeydew: "f0fff0",
        hotpink: "ff69b4",
        indianred: "cd5c5c",
        indigo: "4b0082",
        ivory: "fffff0",
        khaki: "f0e68c",
        lavender: "e6e6fa",
        lavenderblush: "fff0f5",
        lawngreen: "7cfc00",
        lemonchiffon: "fffacd",
        lightblue: "add8e6",
        lightcoral: "f08080",
        lightcyan: "e0ffff",
        lightgoldenrodyellow: "fafad2",
        lightgray: "d3d3d3",
        lightgreen: "90ee90",
        lightgrey: "d3d3d3",
        lightpink: "ffb6c1",
        lightsalmon: "ffa07a",
        lightseagreen: "20b2aa",
        lightskyblue: "87cefa",
        lightslategray: "789",
        lightslategrey: "789",
        lightsteelblue: "b0c4de",
        lightyellow: "ffffe0",
        lime: "0f0",
        limegreen: "32cd32",
        linen: "faf0e6",
        magenta: "f0f",
        maroon: "800000",
        mediumaquamarine: "66cdaa",
        mediumblue: "0000cd",
        mediumorchid: "ba55d3",
        mediumpurple: "9370db",
        mediumseagreen: "3cb371",
        mediumslateblue: "7b68ee",
        mediumspringgreen: "00fa9a",
        mediumturquoise: "48d1cc",
        mediumvioletred: "c71585",
        midnightblue: "191970",
        mintcream: "f5fffa",
        mistyrose: "ffe4e1",
        moccasin: "ffe4b5",
        navajowhite: "ffdead",
        navy: "000080",
        oldlace: "fdf5e6",
        olive: "808000",
        olivedrab: "6b8e23",
        orange: "ffa500",
        orangered: "ff4500",
        orchid: "da70d6",
        palegoldenrod: "eee8aa",
        palegreen: "98fb98",
        paleturquoise: "afeeee",
        palevioletred: "db7093",
        papayawhip: "ffefd5",
        peachpuff: "ffdab9",
        peru: "cd853f",
        pink: "ffc0cb",
        plum: "dda0dd",
        powderblue: "b0e0e6",
        purple: "800080",
        rebeccapurple: "663399",
        red: "f00",
        rosybrown: "bc8f8f",
        royalblue: "4169e1",
        saddlebrown: "8b4513",
        salmon: "fa8072",
        sandybrown: "f4a460",
        seagreen: "2e8b57",
        seashell: "fff5ee",
        sienna: "a0522d",
        silver: "c0c0c0",
        skyblue: "87ceeb",
        slateblue: "6a5acd",
        slategray: "708090",
        slategrey: "708090",
        snow: "fffafa",
        springgreen: "00ff7f",
        steelblue: "4682b4",
        tan: "d2b48c",
        teal: "008080",
        thistle: "d8bfd8",
        tomato: "ff6347",
        turquoise: "40e0d0",
        violet: "ee82ee",
        wheat: "f5deb3",
        white: "fff",
        whitesmoke: "f5f5f5",
        yellow: "ff0",
        yellowgreen: "9acd32"
    };

    // Make it easy to access colors via `hexNames[hex]`
    var hexNames = tinycolor.hexNames = flip(names);


    // Utilities
    // ---------

    // `{ 'name1': 'val1' }` becomes `{ 'val1': 'name1' }`
    function flip(o) {
        var flipped = { };
        for (var i in o) {
            if (o.hasOwnProperty(i)) {
                flipped[o[i]] = i;
            }
        }
        return flipped;
    }

    // Return a valid alpha value [0,1] with all invalid values being set to 1
    function boundAlpha(a) {
        a = parseFloat(a);

        if (isNaN(a) || a < 0 || a > 1) {
            a = 1;
        }

        return a;
    }

    // Take input from [0, n] and return it as [0, 1]
    function bound01(n, max) {
        if (isOnePointZero(n)) { n = "100%"; }

        var processPercent = isPercentage(n);
        n = mathMin(max, mathMax(0, parseFloat(n)));

        // Automatically convert percentage into number
        if (processPercent) {
            n = parseInt(n * max, 10) / 100;
        }

        // Handle floating point rounding errors
        if ((math.abs(n - max) < 0.000001)) {
            return 1;
        }

        // Convert into [0, 1] range if it isn't already
        return (n % max) / parseFloat(max);
    }

    // Force a number between 0 and 1
    function clamp01(val) {
        return mathMin(1, mathMax(0, val));
    }

    // Parse a base-16 hex value into a base-10 integer
    function parseIntFromHex(val) {
        return parseInt(val, 16);
    }

    // Need to handle 1.0 as 100%, since once it is a number, there is no difference between it and 1
    // <http://stackoverflow.com/questions/7422072/javascript-how-to-detect-number-as-a-decimal-including-1-0>
    function isOnePointZero(n) {
        return typeof n == "string" && n.indexOf('.') != -1 && parseFloat(n) === 1;
    }

    // Check to see if string passed in is a percentage
    function isPercentage(n) {
        return typeof n === "string" && n.indexOf('%') != -1;
    }

    // Force a hex value to have 2 characters
    function pad2(c) {
        return c.length == 1 ? '0' + c : '' + c;
    }

    // Replace a decimal with it's percentage value
    function convertToPercentage(n) {
        if (n <= 1) {
            n = (n * 100) + "%";
        }

        return n;
    }

    // Converts a decimal to a hex value
    function convertDecimalToHex(d) {
        return Math.round(parseFloat(d) * 255).toString(16);
    }
    // Converts a hex value to a decimal
    function convertHexToDecimal(h) {
        return (parseIntFromHex(h) / 255);
    }

    var matchers = (function() {

        // <http://www.w3.org/TR/css3-values/#integers>
        var CSS_INTEGER = "[-\\+]?\\d+%?";

        // <http://www.w3.org/TR/css3-values/#number-value>
        var CSS_NUMBER = "[-\\+]?\\d*\\.\\d+%?";

        // Allow positive/negative integer/number.  Don't capture the either/or, just the entire outcome.
        var CSS_UNIT = "(?:" + CSS_NUMBER + ")|(?:" + CSS_INTEGER + ")";

        // Actual matching.
        // Parentheses and commas are optional, but not required.
        // Whitespace can take the place of commas or opening paren
        var PERMISSIVE_MATCH3 = "[\\s|\\(]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")\\s*\\)?";
        var PERMISSIVE_MATCH4 = "[\\s|\\(]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")[,|\\s]+(" + CSS_UNIT + ")\\s*\\)?";

        return {
            rgb: new RegExp("rgb" + PERMISSIVE_MATCH3),
            rgba: new RegExp("rgba" + PERMISSIVE_MATCH4),
            hsl: new RegExp("hsl" + PERMISSIVE_MATCH3),
            hsla: new RegExp("hsla" + PERMISSIVE_MATCH4),
            hsv: new RegExp("hsv" + PERMISSIVE_MATCH3),
            hsva: new RegExp("hsva" + PERMISSIVE_MATCH4),
            hex3: /^([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,
            hex6: /^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,
            hex8: /^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/
        };
    })();

    // `stringInputToObject`
    // Permissive string parsing.  Take in a number of formats, and output an object
    // based on detected format.  Returns `{ r, g, b }` or `{ h, s, l }` or `{ h, s, v}`
    function stringInputToObject(color) {

        color = color.replace(trimLeft,'').replace(trimRight, '').toLowerCase();
        var named = false;
        if (names[color]) {
            color = names[color];
            named = true;
        }
        else if (color == 'transparent') {
            return { r: 0, g: 0, b: 0, a: 0, format: "name" };
        }

        // Try to match string input using regular expressions.
        // Keep most of the number bounding out of this function - don't worry about [0,1] or [0,100] or [0,360]
        // Just return an object and let the conversion functions handle that.
        // This way the result will be the same whether the tinycolor is initialized with string or object.
        var match;
        if ((match = matchers.rgb.exec(color))) {
            return { r: match[1], g: match[2], b: match[3] };
        }
        if ((match = matchers.rgba.exec(color))) {
            return { r: match[1], g: match[2], b: match[3], a: match[4] };
        }
        if ((match = matchers.hsl.exec(color))) {
            return { h: match[1], s: match[2], l: match[3] };
        }
        if ((match = matchers.hsla.exec(color))) {
            return { h: match[1], s: match[2], l: match[3], a: match[4] };
        }
        if ((match = matchers.hsv.exec(color))) {
            return { h: match[1], s: match[2], v: match[3] };
        }
        if ((match = matchers.hsva.exec(color))) {
            return { h: match[1], s: match[2], v: match[3], a: match[4] };
        }
        if ((match = matchers.hex8.exec(color))) {
            return {
                a: convertHexToDecimal(match[1]),
                r: parseIntFromHex(match[2]),
                g: parseIntFromHex(match[3]),
                b: parseIntFromHex(match[4]),
                format: named ? "name" : "hex8"
            };
        }
        if ((match = matchers.hex6.exec(color))) {
            return {
                r: parseIntFromHex(match[1]),
                g: parseIntFromHex(match[2]),
                b: parseIntFromHex(match[3]),
                format: named ? "name" : "hex"
            };
        }
        if ((match = matchers.hex3.exec(color))) {
            return {
                r: parseIntFromHex(match[1] + '' + match[1]),
                g: parseIntFromHex(match[2] + '' + match[2]),
                b: parseIntFromHex(match[3] + '' + match[3]),
                format: named ? "name" : "hex"
            };
        }

        return false;
    }

    window.tinycolor = tinycolor;
    })();

    $(function () {
        if ($.fn.spectrum.load) {
            $.fn.spectrum.processNativeColorInputs();
        }
    });

});

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
(function (root, CP_Customizer, $) {

    CP_Customizer.addModule(function (CP_Customizer) {
        var recompile = _.debounce(CP_Customizer.contentStyle.recompileScssStyle, 200);
        var settings = [
            "color_palette",
            "general_site_typography",
            "general_site_typography_size",
            "general_site_h1_typography",
            "general_site_h2_typography",
            "general_site_h3_typography",
            "general_site_h4_typography",
            "general_site_h5_typography",
            "general_site_h6_typography",

            "materialis_woocommerce_list_item_tablet_cols",
            "materialis_woocommerce_list_item_desktop_cols",
            "materialis_woocommerce_primary_color",
            "materialis_woocommerce_secondary_color",
            "materialis_woocommerce_onsale_color"
        ];


        CP_Customizer.bind(CP_Customizer.events.PREVIEW_LOADED, function () {

            var _settings = jQuery.each(CP_Customizer.wpApi.settings.controls, function (index, item) {
                if (item.choices && item.choices.scss_var) {
                    settings.push(index)
                }
            });

            settings = settings.concat(_settings);
            settings = _.uniq(settings);

            settings.forEach(function (settingID) {
                var setting = CP_Customizer.wpApi(settingID);

                if (setting) {
                    setting.bind(recompile);
                }
            });


        });


        var typographyControlVars = function (settingID, value) {

            var result = {};

            if (settingID === 'general_site_typography') {
                for (var param in value) {
                    result['typo_' + param.split('-').join('_')] = value[param];
                }

            }


            if (settingID.match(/general_site_h(\d+)_typography/)) {
                var hNumber = settingID.match(/general_site_h(\d+)_typography/).pop();
                var varName = 'typo_h' + hNumber + '_';
                for (var param in value) {
                    result[varName + param.split('-').join('_')] = value[param];
                }
            }

            return result;

        };

        var repeaterControlVars = function (settingID, value) {
            var result = {};
            switch (settingID) {
                case 'color_palette':
                    result = CP_Customizer.getColorsObj();
                    break;
            }


            return result;

        };

        CP_Customizer.hooks.addFilter('scss_setting_vars', function (result, settingID) {

            var setting = CP_Customizer.wpApi(settingID);
            var control = (setting && setting.findControls().length) ? setting.findControls()[0] : null;

            if (control) {
                var controlType = control.params.type;
                var value = CP_Customizer.utils.getValue(control);
                switch (controlType) {
                    case 'repeater':
                        result = repeaterControlVars(settingID, value);
                        break;

                    case 'kirki-typography':
                        result = typographyControlVars(settingID, value);
                        break;
                    case 'kirki-slider':

                        if (settingID === 'general_site_typography_size') {
                            result = {
                                'typo_font_size': value + 'px'
                            }
                        }
                        break;

                    default:
                        result = {};
                        result[settingID] = value;

                        if (settingID.indexOf('ope_') === 0) {
                            result[settingID.replace('ope_', '')] = value;
                        }

                }
            }

            return result;
        });

    });

})(window, CP_Customizer, jQuery);

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

wp.customize.controlConstructor['sectionseparator'] = wp.customize.Control.extend({

});

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

(function (root, CP_Customizer, $) {

    kirki.kirkiGetColorPalette = function () {
        return CP_Customizer.getPaletteColors(false, false, {
            'color-white': '#ffffff'
        })
    }
    CP_Customizer.jsTPL['colorselect'] = _.template('' +
        '<li class="customize-control customize-control-text">' +
        '    <label>' +
        '        <span class="customize-control-title"><%= label %></span>' +
        '        <input id="<%= id %>" value="<%= value %>" class="customize-control-title">' +
        '        <script>' +
        '                var sp = jQuery("#<%= id %>"); ' +
        '                CP_Customizer.initSpectrumButton(sp);  ' +
        '                sp.spectrum("set", "<%= value %>");  ' +
        '                CP_Customizer.addSpectrumButton(sp); ' +
        '        </script>' +
        '    </label>' +
        '</li>' +
        '');

    CP_Customizer.jsTPL['colorselect-transparent'] = _.template('' +
        '<li class="customize-control customize-control-text">' +
        '    <label>' +
        '        <span class="customize-control-title"><%= label %></span>' +
        '        <input id="<%= id %>" value="<%= value %>" class="customize-control-title">' +
        '        <script>' +
        '                var sp = jQuery("#<%= id %>"); ' +
        '                CP_Customizer.initSpectrumButton(sp);  ' +
        '                sp.spectrum("set", "<%= value %>");  ' +
        '                CP_Customizer.addSpectrumButton(sp); ' +
        '        </script>' +
        '    </label>' +
        '</li>' +
        '');

    CP_Customizer.initSpectrumButton = function (colorpicker, includeTransparent) {
        colorpicker.spectrum({
            allowEmpty: true,
            togglePaletteOnly: true,
            togglePaletteMoreText: window.CP_Customizer.translateCompanionString('add theme color'),
            togglePaletteLessText: window.CP_Customizer.translateCompanionString('use existing color'),
            allowEmpty: true,
            preferredFormat: includeTransparent ? "rgb" : "hex",
            showInput: true,
            showPaletteOnly: true,
            hideAfterPaletteSelect: true,
            palette: CP_Customizer.getPaletteColors(false, includeTransparent, {
                'color-white': '#ffffff',
                'color-black': '#000000',
                'color-gray': '#bdbdbd'
            })
        });
    };

    CP_Customizer.addSpectrumButton = function (colorpicker) {

        colorpicker.on('show.spectrum', function (e, tinycolor) {
            if (!colorpicker.spectrum("container").find("#goToThemeColors").length) {
                colorpicker.spectrum("container").find(".sp-palette-button-container").append('&nbsp;&nbsp;' +
                    '<button type="button" id="goToThemeColors">' + window.CP_Customizer.translateCompanionString("edit theme colors") + '</button>');
            }

            colorpicker.spectrum("container").find("#goToThemeColors").off("click").on("click", function () {
                CP_Customizer.goToThemeColors(colorpicker);
            })
        });
    };

    CP_Customizer.addSpectrumTransparentButton = function (colorpicker) {

        colorpicker.on('show.spectrum', function (e, tinycolor) {
            if (!colorpicker.spectrum("container").find("#useTransparentColor").length) {
                colorpicker.spectrum("container").find(".sp-palette-button-container").append('&nbsp;&nbsp;' +
                    '<button type="button" id="useTransparentColor">' + window.CP_Customizer.translateCompanionString("Use Transparent Color") + '</button>');
            }

            colorpicker.spectrum("container").find("#useTransparentColor").off("click").on("click", function () {
                colorpicker.spectrum("set", "rgba(0,0,0,0)");
            })
        });
    };

    CP_Customizer.goToThemeColors = function ($sp) {
        wp.customize.control('color_palette').focus();
        $sp.spectrum("hide");
        tb_remove();
    };

    CP_Customizer.getThemeColor = function (value, clbk) {
        var name = CP_Customizer.getColorName(value);
        if (!name) {
            name = CP_Customizer.createColor(value, clbk);
        }
        return name;
    };

    CP_Customizer.getColorsObj = function (includeTransparent) {
        var colors = wp.customize.control('color_palette').getValue();
        var obj = {};
        for (var i = 0; i < colors.length; i++) {
            if (colors[i]) {
                obj[colors[i].name] = colors[i].value;
            }
        }

        if (includeTransparent) {
            obj['transparent'] = 'rgba(0,0,0,0)';
        }


        return obj;
    };

    CP_Customizer.getColorValue = function (name, $node, prop) {
        var colors = CP_Customizer.getColorsObj();

        if (name === "transparent") {
            return "rgba(0,0,0,0)";
        }

        if (name === "white") {
            return "#ffffff";
        }

        if (name === "black") {
            return "#000000";
        }

        if (name === "gray") {
            return "#bdbdbd";
        }


        if (name === "color-white") {
            return "#ffffff";
        }

        if (name === "color-black") {
            return "#000000";
        }

        if (name === "color-gray") {
            return "#bdbdbd";
        }

        var result = colors[name];

        if (!result) {
            if ($node) {
                prop = prop || "color";
                var colorName = CP_Customizer.getColorName($node.css(prop));
                result = CP_Customizer.getColorValue(colorName);
            }
        }

        return result;
    };

    var defaultColors = {
        'ffffff': 'color-white',
        '000000': 'color-black',
        'bdbdbd': 'color-gray'
    };

    CP_Customizer.createColor = function (color, clbk, forceCreate) {


        if (defaultColors[tinycolor(color).toHex()] && !forceCreate) {
            return defaultColors[tinycolor(color).toHex()]
        }

        var colors = CP_Customizer.getColorsObj();
        var max = 0;
        for (var c in colors) {
            var nu = parseInt(c.replace(/[a-z]+/, ''));
            if (nu != NaN) {
                max = Math.max(nu, max);
            }
        }
        var name = "color" + (++max);
        colors[name] = color;

        if (clbk) clbk(name);

        var control = wp.customize.control('color_palette');
        var theNewRow = control.addRow({
            name: name,
            label: name,
            value: color
        });
        theNewRow.toggleMinimize();
        control.initColorPicker();

        if (defaultColors[tinycolor(color).toHex()]) {
            return defaultColors[tinycolor(color).toHex()]
        }


        return name;
    };

    CP_Customizer.getColorName = function (color) {
        var colors = CP_Customizer.getColorsObj();
        var parsedColor = tinycolor(color);

        for (var c in colors) {
            var _temp = tinycolor(colors[c]);
            if (parsedColor.toHex() === _temp.toHex()) { // parsed colors by tinycolor will ensure the same Hex if the colors are equal
                return c;
            }
        }

        if (parsedColor.toHex() === tinycolor('#000000').toHex()) {
            return 'color-black';
        }

        if (parsedColor.toHex() === tinycolor('#ffffff').toHex()) {
            return 'color-white';
        }

        if (parsedColor.toHex() === tinycolor('#bdbdbd').toHex()) {
            return 'color-gray';
        }

        if (tinycolor(color).getAlpha() === 0) {
            return "transparent";
        }

        return "";
    };

    CP_Customizer.getPaletteColors = function (json, includeTransparent, extras) {
        var colors = CP_Customizer.getColorsObj(includeTransparent);

        if (_.isObject(extras)) {
            colors = $.extend({}, colors, extras);
        }

        if (!json) return _.values(colors);
        return JSON.stringify(_.values(colors));
    };

    $(document).ready(function () {
        _.delay(function () {
            var control = wp.customize.control('color_palette');
            control.container.off('click', 'button.repeater-add');
            control.container.on('click', 'button.repeater-add', function (e) {
                e.preventDefault();
                CP_Customizer.createColor('#ffffff', undefined, true);
            });

            control.container.find('.repeater-add').html('Add theme color');

            control.container.find("[data-field=name][value=color1], [data-field=name][value=color2], [data-field=name][value=color3], [data-field=name][value=color4], [data-field=name][value=color5]").each(function () {
                $(this).parents(".repeater-row").find(".repeater-row-remove").hide();
            });
        }, 1000);
    })


    CP_Customizer.hooks.addFilter('spectrum_color_palette', function (colors) {
        var siteColors = jQuery.map(CP_Customizer.getColorsObj(), function (value, index) {
            return value;
        });

        return siteColors;

    });
})(window, CP_Customizer, jQuery);

(function (root, CP_Customizer, $) {

    // this should be moved in globalConfigs in the future
    var buttonSizes = {
        "normal": "",
        "small": "small",
        "big": "big"
    };

    var buttonColors = {};

    var oldColors = {
        "transparent": "transparent",
        "blue": "color1",
        "green": "color2",
        "yellow": "color3",
        "orange": "color5",
        "purple": "color4"
    };

    var buttonTextColors = {
        "default": "",
        "white text": "white-text",
        "dark text": "dark-text"
    };

    var buttonShadow = {
        "No shadow": "no-shadow",
        "Small shadow": "mdc-elevation--z3",
        "Medium shadow": "mdc-elevation--z6",
        "Large shadow": "mdc-elevation--z9",
        "Extra large shadow": "mdc-elevation--z12",
    };

    function buttonColorsList() {
        var buttonColors = {
            "default": "",
            "transparent ( link button )": "transparent"
        };

        var colors = CP_Customizer.getColorsObj(true);
        _.each(colors, function (color, name) {
            buttonColors[name] = name;
        });

        return buttonColors;
    }

    var buttonPresets = {
        "square round": "",
        "link": "link",
        "square": "square",
        "round": "round",
        "square round outline": "outline",
        "square outline": "square outline",
        "round outline": "round outline"
    };


    var currentColorRegexp;

    var oldCurrentColorRegexp = new RegExp(jQuery.map(oldColors, function (value, index) {
        return index
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    var currentTextColorRegexp = new RegExp(jQuery.map(buttonTextColors, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    var currentShadowRegexp = new RegExp(jQuery.map(buttonShadow, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');


    var __sizesText = jQuery.map(buttonSizes, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|");
    var buttonSizesRegexp = new RegExp("\\s(" + __sizesText + ')', 'ig');

    var curentPresetRegexp = new RegExp(jQuery.map(buttonPresets, function (value, index) {
        return value
    }).filter(function (item) {
        return item.length
    }).join("|"), 'ig');

    // link with images
    CP_Customizer.hooks.addFilter('container_data_element', function (result, $elem) {
        var _class = CP_Customizer.preview.cleanNode($elem.clone()).attr('class') || "";

        buttonColors = buttonColorsList();
        currentColorRegexp = /(transparent|white|black|(color\d+))/ig;

        if ($elem.is('a') && _class && ($elem.is('.button') || $elem.is('[class*=button]'))) {
            var color = _class.match(currentColorRegexp) ? _class.match(currentColorRegexp)[0] : "";
            if (!color) {
                color = _class.match(oldCurrentColorRegexp) ? _class.match(oldCurrentColorRegexp)[0] : "";
            }
            var size = _class.match(buttonSizesRegexp) ? _class.match(buttonSizesRegexp)[0] : "";
            var textColor = _class.match(currentTextColorRegexp) ? _class.match(currentTextColorRegexp)[0] : "";
            var shadow = _class.match(currentShadowRegexp) ? _class.match(currentShadowRegexp)[0] : "";
            var preset = _class.match(curentPresetRegexp) ? _class.match(curentPresetRegexp)[0] : "";

            if (_class.match(curentPresetRegexp)) {
                if (_class.match(curentPresetRegexp).length == 1) preset = _class.match(curentPresetRegexp);
                else preset = _class.match(curentPresetRegexp)[0] + ' ' + _class.match(curentPresetRegexp)[1];
            }
            else var preset = "";

            if (oldColors[color]) {
                color = oldColors[color]
            }

            color = CP_Customizer.getColorValue(color);

            if (!$elem.is('.button')) {
                $elem.addClass('button');
            }

            if ($elem.is('.button')) {
                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Size"),
                    "type": "select",
                    "choices": buttonSizes,
                    "name": "button_size_option",
                    "classes": "button-pro-option",
                    "value": size.trim()
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Shadow"),
                    "type": "select",
                    "choices": buttonShadow,
                    "name": "button_shadow_option",
                    "classes": "button-pro-option",
                    "value": shadow
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Color"),
                    "type": "colorselect-transparent",
                    // "choices": buttonColors,
                    "name": "button_color_option",
                    "classes": "button-pro-option",
                    "value": color,
                    "getValue": CP_Customizer.utils.getSpectrumColorFormated
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Text Color"),
                    "type": "select",
                    "choices": buttonTextColors,
                    "name": "button_text_color_option",
                    "classes": "button-pro-option",
                    "value": textColor
                });

                result.push({
                    'label': window.CP_Customizer.translateCompanionString("Button Preset"),
                    "type": "select",
                    "choices": buttonPresets,
                    "name": "button_preset_option",
                    "classes": "button-pro-option",
                    "value": preset
                });
            }

            var icon = $elem.attr('data-icon') || "";

            if (!icon) {
                if ($elem.find('span.button-icon').length) {
                    var match = $elem.find('span.button-icon').attr('class').match(/mdi\-[a-z\-]+/ig);
                    icon = ((match || []).pop()) || "";
                }
            }

            result.push({
                'label': window.CP_Customizer.translateCompanionString("Button Icon"),
                "type": "icon",
                "choices": buttonColors,
                "name": "button_icon_option",
                "canHide": true,
                value: {
                    icon: icon,
                    visible: ($elem.find('span.button-icon').length > 0)
                },
                "mediaType": "icon",
                mediaData: false
            });

            result = CP_Customizer.hooks.applyFilters('button_settings_controls', result, $elem);
        }

        return result;
    });

    CP_Customizer.hooks.addAction('container_data_element_setter', function (node, value, field) {

        if (field.name) {
            var _class = node.attr('class');
            var match = false;
            switch (field.name) {
                case "button_size_option":
                    _class = _class.replace(buttonSizesRegexp, " ");
                    match = true;
                    break;

                case "button_text_color_option":
                    _class = _class.replace(currentTextColorRegexp, " ");
                    match = true;
                    break;

                case "button_shadow_option":
                    match = true;
                    _class = _class.replace(currentShadowRegexp, " ");
                    break;

                case "button_color_option":
                    var aux = _class.split(" ");
                    var aux2 = [];
                    for (var i = 0, len = aux.length; i < len; i++) {
                        if (aux[i].search(currentColorRegexp) == -1 && aux[i].search(oldCurrentColorRegexp) == -1) aux2.push(aux[i]);
                    }
                    _class = aux2.join(" ");
                    match = true;

                    value = CP_Customizer.getThemeColor(value, function (value) {
                        match = false;
                        _class = _class.replace(/\s\s+/, " ").trim();
                        _class += " " + value;
                        node.attr('class', _class.trim());
                        CP_Customizer.updateState(true);
                    });
                    break;

                case "button_preset_option":
                    var aux = _class.split(" ");
                    var aux2 = [];
                    for (var i = 0, len = aux.length; i < len; i++) {
                        if (aux[i].search(curentPresetRegexp) == -1) aux2.push(aux[i]);
                    }

                    _class = aux2.join(" ");
                    match = true;
                    break;

            }

            if (match) {
                _class = _class.replace(/\s\s+/, " ").trim();
                _class += " " + value;

                node.attr('class', _class.trim());
            }

        }

        if (field.name === "button_icon_option") {
            node.attr('data-icon', value.icon);
            node.find('span.mdi').remove();

            if (value.visible) {
                node.prepend('<span class="button-icon mdi ' + value.icon + '"></span>');
                root.CP_Customizer.preview.markNode(node);
            }
        }

        CP_Customizer.hooks.doAction('button_settings_updated', node, value, field);
    });


    CP_Customizer.hooks.addFilter('temp_attr_mod_value', function (value, attr, $el) {

        var _class = ($el.attr('class') || "");

        if (attr === "temp-size") {
            value = _class.match(buttonSizesRegexp) ? _class.match(buttonSizesRegexp)[0] : "";
        }

        if (attr === "temp-color") {
            value = _class.match(currentColorRegexp) ? _class.match(currentColorRegexp)[0] : "";
        }

        return value;
    });


})(window, CP_Customizer, jQuery);

(function (root, CP_Customizer, $) {

    CP_Customizer.themeColors = function (useWhiteBlack) {
        var buttonColors = {
            "default": "",
        };

        var colors = CP_Customizer.getColorsObj();
        _.each(colors, function (color, name) {
            buttonColors[name] = name;
        });

        if (useWhiteBlack) {
            buttonColors['color-white'] = 'color-white';
            buttonColors['color-black'] = 'color-black';
            buttonColors['color-gray'] = 'color-gray';
        }

        return buttonColors;
    };

    var currentColorRegexp;


    CP_Customizer.hooks.addFilter('container_data_element', function (result, $elem) {
        var _class = CP_Customizer.preview.cleanNode($elem.clone()).attr('class') || "";

        var colors = CP_Customizer.themeColors(true);
        var colorsList = Object.getOwnPropertyNames(colors);
        currentColorRegexp = /(color\d+)/ig;

        if ($elem.is('i.mdi') && !$elem.parent().is('.read-more')) {

            var color = "";

            for (var i = 0; i < colorsList.length; i++) {
                if ($elem.is('.' + colorsList[i])) {
                    color = colorsList[i];
                }
            }

            result.push({
                label: window.CP_Customizer.translateCompanionString("Icon Color"),
                type: "colorselect",
                choices: colors,
                "name": "icon_color_option",
                "value": CP_Customizer.getColorValue(color, $elem)
            });
        }

        return result;
    });

    CP_Customizer.hooks.addAction('container_data_element_setter', function (node, value, field) {
        if (field.name) {
            var _class = node.attr('class');
            var match = false;
            var colors = CP_Customizer.themeColors(true);
            var colorsList = Object.getOwnPropertyNames(colors);

            switch (field.name) {
                case "icon_color_option":
                    match = true;
                    // _class = _class.replace(currentColorRegexp, " ");

                    value = CP_Customizer.getThemeColor(value, function (value) {
                        match = false;
                        node.removeClass(colorsList.join(' '));
                        node.addClass(value);

                        CP_Customizer.updateState(true);
                    });

                    break;
            }

            if (match) {
                node.removeClass(colorsList.join(' '));
                node.addClass(value);
                CP_Customizer.updateState();
            }

        }
    });

})(window, CP_Customizer, jQuery);

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
