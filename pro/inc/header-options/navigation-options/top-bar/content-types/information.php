<?php

add_action("materialis_top_bar_information_fields_options_before", function($area, $section, $priority, $prefix) {
	materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Information fields colors', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'settings' => "{$prefix}_info_fields_colors_separator",
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Text Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_information_fields_text_color",
        'default'   => "#FFFFFF",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} span",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} span",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Icon Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_information_fields_icon_color",
        'default'   => "#999",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} i.mdi",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} i.mdi",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

}, 1, 4);
