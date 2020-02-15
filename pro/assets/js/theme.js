(function ($) {
    if (!$.fn.animateCss) {
        $.fn.extend({
            animateCss: function (animationName, duration, delay) {
                var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';

                this.css({
                    'visibility' : 'visible',

                    "-moz-animation-delay" : delay,
                    "-webkit-animation-delay" : delay,
                    "animation-delay" : delay,

                    "-moz-animation-duration" : duration,
                    "-webkit-animation-duration" : duration,
                    "animation-duration" : duration,

                });


                this.addClass('animated ' + animationName).one(animationEnd, function() {
                    //$(this).removeClass('animated ' + animationName);
                });

                return this;
            }
        });
    }

})(jQuery);

function materialisRenderMap(settings) {
    var windowWidth = typeof(window.outerWidth) !== "undefined" ? window.outerWidth : document.documentElement.clientWidth;
    var mapOptions = {
        center: {
            lat: settings.lat,
            lng: settings.lng
        },
        scrollwheel: false,
        draggable: (windowWidth > 800),
        zoom: settings.zoom,
        mapTypeId: google.maps.MapTypeId[settings.type]
    };


    var mapHolder = jQuery('[data-id=' + settings['id'] + ']');
    mapHolder.each(function () {
        var map = new google.maps.Map(this, mapOptions);
        var latLang = new google.maps.LatLng(settings.lat, settings.lng);
        var marker = new google.maps.Marker({
            position: latLang,
            map: map
        });

        if (windowWidth < 800) {
            jQuery(this).click(function () {
                map.set("draggable", true);
            });
        }
    })
}

jQuery(function ($) {
    $('[data-fancybox]').fancybox({
        youtube: {
            controls: 1,
            showinfo: 0,
            autoplay: 1
        },
        vimeo: {
            color: 'f00',
            autoplay: 1
        }

    });

    $('[target=lightbox]').fancybox({

        iframe: {
            attr: {
                scrolling: true
            }
        }

    })

    $(document).on('onInit.fb', function (e, instance) {
        $.each(instance.group, function (i, item) {

            var type = item.type;
            if (type === 'iframe') {
                try {
                    item.opts.iframe.scrolling = "auto";
                } catch (e) {

                }
            }
        });
    });

});

(function ($) {

    $(document).on('ope-mobile-menu-show.multilanguage', function () {

        polylangLinksAddedInMenu = true;
        var $mobileUL = $('#fm2_drop_mainmenu_jq_menu_back div.menu ul');
        var $polyLangLinks = $('.materialis-language-switcher a');
        if ($polyLangLinks.length) {
            $polyLangLinks.each(function () {
                var $polylangLink = $(this);
                var $menuLink = $('<li class="ellipsis pll-mobile-menu-item"><a href=#"><p class="xtd_menu_ellipsis"><font class="leaf"></font></p></a></li>');
                $menuLink.find('a').attr('href', $polylangLink.attr('href'));
                $menuLink.find('.leaf').html($polylangLink.html());
                $mobileUL.append($menuLink);
            });
        }

        $(document).off('ope-mobile-menu-show.multilanguage');
    });

    $(document).on('tap', '.materialis-language-switcher', function (event) {
        var $languagesList = $(this);

        if (!$languagesList.hasClass('hover')) {
            event.preventDefault();
            event.stopPropagation();
            $languagesList.addClass('hover');
        } else {
            var isCurrentLang = $(event.target).closest('.current-lang').length !== 0;
            isCurrentLang = isCurrentLang || $(event.target).hasClass('current-lang');
            if (isCurrentLang) {
                event.preventDefault();
                event.stopPropagation();
            }
        }
    });

})(jQuery);


(function ($) {
    var contentSwap = {
        "contentswap-effect": {
            "effectType": "",
            "contentType": "overlay",
            "overflowEnabled": "false",
            "effectDelay": "800",
            "effectEasing": "Ease",
            "overlayColor": "490A3D",
            "innerColor": "ffffff",
            "openPage": "same",
            "name": "",
            "captionType": "490A3D",
            "operationType": "edit",
            "hasls": "true",
            "additionalWrapperClasses": "",
            "direction": "bottom",
            "useSameTemplate": "true"
        }
    };


    var contentSwapTimeout = setTimeout(function () {
        if (window.initHoverFX) {
            initHoverFX(contentSwap);
        }
    }, 10);
    jQuery(window).resize(function (e) {
        clearTimeout(contentSwapTimeout);
        contentSwapTimeout = setTimeout(function () {
            if (window.initHoverFX) {
                initHoverFX(contentSwap, null, e);
            }
        }, 150);

    });


})(jQuery);


(function ($) {

    var revealFx = window.materialis_theme_pro_settings ? materialis_theme_pro_settings['reveal-effect'] : false;
    if (!revealFx || !revealFx.enabled){
        return;
    }

    if (parent.CP_Customizer) {
        return;
    }

    var headerContent = $('.header-description-row *:not(div)');
    var delay = 0;
    headerContent.each(function() {
        $(this).attr('data-reveal-fx', true);
        $(this).attr('data-reveal-fx-delay', delay);
        delay = delay + 1/headerContent.length;
    });

    function enableEffects($el){
        $el.children().each(function() {
            if ($el.is('.row')) {
                var hasRows = $(this).find('.row').length > 1;
                if (!hasRows) {
                    $(this).attr('data-reveal-fx', true);
                } else {
                    $(this).css('visibility', 'visible');
                }
            }
            enableEffects($(this));
        })
    }

    enableEffects($('.content'));

    function triggerEffect(){
        $('[data-reveal-fx]:not(.animated)').each(function () {
            var $self = $(this);
            if ($self.isInView(false)) {
                var delay = 0;
                var children = $self.siblings().length;
                if ($(this).isInView(false)) {
                    $(this).animateCss('fadeIn', '2s', ($(this).attr('data-reveal-fx-delay') || delay) + 's');
                    delay = delay + 1/children;
                }
            }
        });
    }

    $(window).on('scroll', function () {
        triggerEffect()
    });

    triggerEffect();

})(jQuery);
