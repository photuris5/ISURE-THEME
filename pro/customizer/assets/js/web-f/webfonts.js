var $ = jQuery;

var sendData = function (event) {
    if (event.data == 'done') {
        var data = {
            family: $('#font_family_iframe').val(),
            weights: $('#font_weight_iframe').val().split(',')
        }
        //needs to be stringify-ed and then parsed in order to be clonable (???????)
        event.source.postMessage(JSON.parse(JSON.stringify(data)), event.origin);
    } else {
        init(event.data.fonts, event.data.edit);
    }

}

window.addEventListener("message", sendData, false);

function getFont() {
    var data = {
        family: $('#font_family_iframe').val(),
        weights: $('#font_weight_iframe').val().split(',')
    }
    return data;
}

function init(currentFonts, edit) {
    showFonts();

    if (edit) {
        $('body').addClass('edit');
    }

    var fontText = "";
    var clickedFont = "";
    var fontToSelect = edit && currentFonts.length ? currentFonts[0].family : false;
    var installedFamilies = currentFonts.map(function (x) {
        return x.family
    });

    function refresh() {
        fontText = "";
        clickedFont = "";
        fontToSelect = currentFonts.length ? currentFonts[0].family : false;
        installedFamilies = currentFonts.map(function (x) {
            return x.family
        });
    }


    function prepare_data(data) {
        var fontToSelectIndex = -1;
        var _items = [];
        var _restItems = [];
        for (var i = 0; i < data.items.length; i++) {
            var item = data.items[i];
            if (installedFamilies.indexOf(item.family) !== -1) {
                if (edit && item.family == fontToSelect) {
                    data.items = [item];
                    return data;
                } else {
                    _items.push(item);
                }
            } else {
                _restItems.push(item);
            }
        }
        data.items = _items.concat(_restItems);
        return data;
    }

    function showFonts() {
        $('#font-list').empty().append('<li disabled="true">Please wait...</li>');

        // if (parent.cpWebFontsCache) {
        //     refresh();
        //     generate_list(parent.cpWebFontsCache);
        //     $('#search_input').focus();
        //     return;
        // }

        $.ajax({
            url: 'assets/webfonts.json',
            dataType: 'json',
            success: function (data) {
                data = prepare_data(data);
                generate_list(data);
                parent.cpWebFontsCache = data;
                $('#search_input').focus();
            },
            error: function () {
                alert('Failed!');
            }
        });
    }

    function createFamily(inputVal) {
        var family = "";
        $.each(inputVal.replace(/\"|\'/, '').split(','), function (ind, val) {
            if (ind > 0) {
                family += ', ';
            }
            family += val.replace(/^\s*|\s*$/, '');
        });
        //console.log(family);
        var familyArray = family.split(', ');
        for (var i = 0; i < familyArray.length; i++) {
            familyArray[i] = familyArray[i].trim();
            if (familyArray[i].indexOf(' ') != -1) {
                familyArray[i] = "'" + familyArray[i] + "'";
            }
        }
        family = familyArray.join(', ');
        //console.log(family);
        return family;
    }

    function generate_list(data) {
        $('#font-list, .webfonts_options').empty();
        var lastLoaded = 0,
            scrolled = true;
        appendMore(data.items, lastLoaded);
        lastLoaded += 10;
        $('#font-list').parent().scroll(function () {
            if (scrolled && $('#font-list').parent().scrollTop() > $('#font-list').height() - $('#font-list').parent().height()) {
                scrolled = false;
                appendMore(data.items, lastLoaded);
                lastLoaded += 10;
                if (lastLoaded - 10 < data.items.length) {
                    scrolled = true;
                }
            }
        });
    }

    function appendItem(items, i) {
        var fontName, fontsName, fontPreview, liElement;
        fontsName = $('<div class="font_label">' + items[i].family + '</div>');
        fontPreview = $('<div class="font_preview">Grumpy wizards make toxic brew for the evil Queen and Jack.</div>');
        // fontPreview = $('<div class="font_preview">' + items[i].family + '</div>');
        fontPreview.css({
            'font-family': items[i].family + ', serif'
        });
        liElement = $('<li/>').data('index', i).append(fontPreview).click(function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (clickedFont != this) {
                $(clickedFont).removeClass('clicked');
                $(this).addClass('clicked');
                $('#font-list').find('.selected').removeClass('selected');
                $(this).addClass('selected');
                clickedFont = $(this).children('.font_label').eq(0);
                var fontText = $(clickedFont).text();
                $('#font_family_iframe').attr("value", fontText);
                $('#font_weight_iframe').attr("value", "");
                //  alert($('input#font_family_iframe').text());
                fontText = fontText.split(',');
                var index = $(this).data('index');
                $('.webfonts_options').empty();
                if (index > -1) {
                    var existingWeights = [];
                    for (var i = 0; i < currentFonts.length; i++) {
                        if (currentFonts[i].family == items[index].family) {
                            existingWeights = currentFonts[i].weights;
                            continue;
                        }
                    }
                    $.each(items[index].variants, function (ind, val) {
                        var item = $('<li />');
                        var res = val.replace('00', '00 ').split(" ");
                        var f_w, f_s = "";
                        var matches = val.match(/\d+/g);
                        if (matches != null) {
                            f_w = res[0];
                            f_s = res[1]
                        } else {
                            f_s = res[0];
                        }
                        var checked = "";
                        if (existingWeights.indexOf(val) != -1) {
                            checked = 'checked="checked"';
                        }
                        item.append('<div class="cust_ch_label"><input type="checkbox" class="custom_checkbox" ' + checked + ' id="check_' + ind + '" name="variant" value="' + val + '"/><label for="check_' + ind + '" style="width: 162px; font-weight:' + f_w + '; font-family:' + $(clickedFont).text() + '; font-style:' + f_s + '">' + val.replace('00', '00 ') + '<span></span></label></div>');
                        //item.append('<label><input type="checkbox" name="variant" value="' + val + '" />' + val.replace('00', '00 ') + '</label>');
                        $('.webfonts_options').append(item);
                        //$('.cust_ch_label label, .custom_checkbox').click(function() {
                    });
                    $('.webfonts_options li').click(function () {
                        var checkbox = $(this).find('.custom_checkbox');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    });
                }
                $('.webfonts_options .custom_checkbox').change(function () {
                    var self = this;
                    var selectedWeights = $('.webfonts_options').find('[type=checkbox]:checked').map(function (index, el) {
                        return el.value
                    }).toArray();
                    selectedWeights.map(function (weight) {
                        if (weight === "regular") {
                            //weight = "normal";
                        }
                        return weight;
                    });
                    $('#font_weight_iframe').attr("value", selectedWeights.join(","));
                });
                var found = false;
                $('.custom_checkbox').each(function (e) {
                    if ($(this).val() == 'regular' && $('input[type="checkbox"].custom_checkbox:checked').length == 0) {
                        found = true;
                        $(this).prop('checked', 'checked').click();
                    }
                });
                if (!found) {
                    $('.custom_checkbox').first().prop('checked', 'checked').click();
                }
            }
        });
        if (installedFamilies.indexOf(items[i].family) !== -1) {
            liElement.addClass('installed');
        }
        liElement.prepend(fontsName);

        if ( edit || installedFamilies.indexOf(items[i].family) === -1) {
        $('#font-list').append(liElement);
        }
        if (items[i].family == fontToSelect) {
            function trigger() {
            }

            {
                liElement.trigger('click');
            }
            setTimeout(trigger, 1500);
        }
        return liElement;
    }

    function appendMore(items, lastLoaded) {
        var fontName, fontsName, fontPreview, liElement, i, stopAt = "";
        var itemfamily = [];
        $('#font_weight_iframe').attr("value", " ");
        if (lastLoaded + 10 < items.length) {
            stopAt = lastLoaded + 10;
        } else {
            stopAt = items.length;
        }
        for (i = lastLoaded; i < stopAt; i++) {
            itemfamily.push(items[i].family);
            appendItem(items, i);
        }
        $('.search').click(function () {
            $(this).addClass("selected");
        });
        $('#search_input').keyup(function (e) {
            var input_s = $('#search_input').val();
            var exist = false;
            if (input_s.length > 2) {
                $("#font-list li").hide();
                $.each(items, function (id, val) {
                    if (val.family.toUpperCase().indexOf(input_s.toUpperCase()) != -1) {
                        var li = appendItem(items, id);
                        li.addClass('search_result');
                        exist = true;
                    }
                });
                $('.xtd-web-fonts-list').find('.error').hide();
                if (!exist) {
                    $('.xtd-web-fonts-list').append('<div class="error">There are no results for a web font with this name.</div>');
                }
                $('#font-list').parent().unbind('scroll');
                //return false;
            } else {
                $("#font-list").find(".font_label").each(function () {
                    $(this).parent().show();
                });
                lastLoaded = $("#font-list").children().length;
                var scrolled = true;
                if (lastLoaded >= items.length) {
                    scrolled = false;
                }
                $('#font-list').find('.search_result').remove();
                $('#font-list').parent().scroll(function () {
                    if (scrolled && $('#font-list').parent().scrollTop() > $('#font-list').height() - $('#font-list').parent().height()) {
                        scrolled = false;
                        appendMore(items, lastLoaded);
                        lastLoaded += 10;
                        if (lastLoaded - 10 < items.length) {
                            scrolled = true;
                        }
                    }
                });
            }
        });
    }
}
