<?php

add_filter("header_content_subtitle_group_filter", function ($values) {

    array_unshift($values,"header_content_subtitle_typography");
    array_unshift($values,"header_content_subtitle_spacing");

    return $values;
});

add_action("materialis_front_page_header_subtitle_options_after", function ($section, $prefix, $priority) {

    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'header_content_subtitle_spacing',
        'label'     => __('Subtitle Spacing', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'top'    => '0',
            'bottom' => '20px',
        ),
        'transport' => 'postMessage',
        'output'    => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'property' => 'margin',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => ".header-homepage p.header-subtitle",
                'function' => 'style',
                'property' => 'margin',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => 'header_content_subtitle_typography',
        'label'     => __('Subtitle Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
	        'font-family'      => 'Roboto',
            'font-size'        => '1.4em',
            'mobile-font-size' => '1.3em',
            'font-weight'      => '300',
            'line-height'      => '130%',
            'letter-spacing'   => 'normal',
            'subsets'          => array(),
            'color'            => '#ffffff',
            'text-transform'   => 'none',
            'addwebfont'       => true,
        ),
        'choices'   => array(
            "alpha" => true,
        ),
        'output'    => array(
            array(
                'element' => '.header-homepage p.header-subtitle',
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '.header-homepage p.header-subtitle',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Background Options', 'materialis'),
        'section'  => $section,
        'settings' => "header_content_subtitle_background_options_separator",
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'label'           => esc_html__('Enable Background', 'materialis'),
        'section'         => $section,
        'settings'        => 'header_content_subtitle_background_enabled',
        'priority'        => $priority,
        'default'         => materialis_mod_default("header_content_subtitle_background_enabled"),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => esc_html__('Background Color', 'materialis'),
        'section' => $section,
        'settings'  => 'header_content_subtitle_background_color',
        'default'   => materialis_mod_default("header_element_background_color"),
        'transport' => 'postMessage',
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => true,
        ),
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        "output" => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'property' => 'background',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => ".header-homepage p.header-subtitle",
                'function' => 'css',
                'property' => 'background',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'header_content_subtitle_background_spacing',
        'label'     => esc_html__('Background Spacing', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => materialis_mod_default("header_content_subtitle_background_spacing"),
        'transport' => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'    => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'property' => 'padding',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => ".header-homepage p.header-subtitle",
                'function' => 'style',
                'property' => 'padding',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'dimension',
        'settings'  => 'header_content_subtitle_background_border_radius',
        'label'     => esc_html__('Border Radius', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => materialis_mod_default("header_element_background_radius"),
        'transport' => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'    => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'property' => 'border-radius',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => ".header-homepage p.header-subtitle",
                'function' => 'style',
                'property' => 'border-radius',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => 'header_content_subtitle_background_border_color',
        'label'     => esc_html__('Border Color', 'materialis'),
        'section'   => $section,
        'default'   => materialis_mod_default('header_element_background_border_color'),
        'transport' => 'postMessage',
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => true,
        ),
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output' => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'property' => 'border-color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.header-homepage p.header-subtitle',
                'function' => 'css',
                'property' => 'border-color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'header_content_subtitle_background_border_thickness',
        'label'     => esc_html__('Background Border Thickness', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => materialis_mod_default('header_element_background_border_thickness'),
        'transport' => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'slider',
        'settings' => 'header_content_subtitle_background_shadow',
        'label'    => esc_html__('Shadow Elevation', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'default'  => materialis_mod_default("header_element_background_shadow"),
        'choices'  => array(
            'min'  => '0',
            'max'  => '12',
            'step' => '1',
        ),
        'transport' => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


}, 1, 3);
