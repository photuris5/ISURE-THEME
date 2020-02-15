<?php

add_action("materialis_top_bar_social_icons_fields_options_before", function($area, $section, $priority, $prefix) {
    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Social Icons Colors', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'settings' => "{$prefix}_social_fields_colors_separator",
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Icon Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_social_icons_options_icon_color",
        'default'   => "#fff",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-social-icons i",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-social-icons i",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Icon Color on Hover', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_social_icons_options_icon_hover_color",
        'default'   => "#fff",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-social-icons i:hover",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-social-icons i:hover",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

},1, 4);
