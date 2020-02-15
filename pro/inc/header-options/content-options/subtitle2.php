<?php

add_action("materialis_front_page_header_title_options_before", function ($section, $prefix, $priority) {
    materialis_add_options_group(array(
        "materialis_front_page_header_subtitle_2_pro_options" => array(
            // section, prefix, priority
            "header_background_chooser",
            "header",
            6,
        ),
    ));
}, 1, 3);


add_filter("header_content_subtitle2_group_filter", function ($values) {
    $new = array(
        "header_content_subtitle2_typography",
        "header_content_subtitle_spacing2",
        "header_content_subtitle2_background_options_separator",
        "header_content_subtitle2_background_enabled",
        "header_content_subtitle2_background_color",
        "header_content_subtitle2_background_spacing",
        "header_content_subtitle2_background_border_radius",
        "header_content_subtitle2_background_border_color",
        "header_content_subtitle2_background_border_thickness",
        "header_content_subtitle2_background_shadow",
    );

    return array_merge($values, $new);
});
function materialis_front_page_header_subtitle_2_pro_options($section, $prefix, $priority)
{
    $companion = apply_filters('materialis_is_companion_installed', false);

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => 'header_content_show_subtitle2',
        'label'    => __('Show motto', 'materialis'),
        'section'  => $section,
        'default'  => false,
        'priority' => $priority,
    ));
    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => 'header_content_subtitle2_group',
        'label'           => esc_html__('Options', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'choices'         => array(
            'header_subtitle2',
        ),
        'active_callback' => array(
            array(
                'setting'  => 'header_content_show_subtitle2',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'in_row_with'     => array('header_content_show_subtitle2'),
    ));

    if ( ! $companion) {
        materialis_add_kirki_field(array(
            'type'              => 'text',
            'settings'          => 'header_subtitle2',
            'label'             => __('Motto', 'materialis'),
            'section'           => $section,
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
            'priority'          => $priority,
            'transport'         => 'postMessage',
            'js_vars'           => array(
                array(
                    'element'  => '.header-homepage .header-subtitle2',
                    'function' => 'html',
                ),
            ),
        ));
    }
}

add_action("materialis_front_page_header_subtitle_2_options_after", function ($section, $prefix, $priority) {
    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'header_content_subtitle_spacing2',
        'label'     => __('Motto Spacing', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'top'    => '0',
            'bottom' => '20px',
        ),
        'transport' => 'postMessage',
        'output'    => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'property' => 'margin',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'function' => 'style',
                'property' => 'margin',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => 'header_content_subtitle2_typography',
        'label'     => __('Subtitle Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'font-family'      => 'Roboto',
            'font-size'        => '1.4rem',
            'mobile-font-size' => '1.3rem',
            'font-weight'      => '400',
            'line-height'      => '130%',
            'letter-spacing'   => 'normal',
            'subsets'          => array(),
            'color'            => '#ffffff',
            'text-transform'   => 'none',
            'addwebfont'       => true,
        ),
        'output'    => array(
            array(
                'element' => '.header-homepage p.header-subtitle2',
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '.header-homepage p.header-subtitle2',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Background Options', 'materialis'),
        'section'  => $section,
        'settings' => 'header_content_subtitle2_background_options_separator',
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'label'    => esc_html__('Enable Background', 'materialis'),
        'section'  => $section,
        'settings' => 'header_content_subtitle2_background_enabled',
        'priority' => $priority,
        'default'  => materialis_mod_default('header_element_background_enabled'),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Background Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'header_content_subtitle2_background_color',
        'default'         => materialis_mod_default('header_content_subtitle_background_color'),
        'transport'       => 'postMessage',
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'          => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'property' => 'background',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'function' => 'css',
                'property' => 'background',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'spacing',
        'settings'        => 'header_content_subtitle2_background_spacing',
        'label'           => esc_html__('Background Spacing', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => materialis_mod_default('header_content_subtitle_background_spacing'),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'          => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'property' => 'padding',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'function' => 'style',
                'property' => 'padding',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'dimension',
        'settings'        => 'header_content_subtitle2_background_border_radius',
        'label'           => esc_html__('Border Radius', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => materialis_mod_default('header_element_background_radius'),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'          => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'property' => 'border-radius',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'function' => 'style',
                'property' => 'border-radius',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => 'header_content_subtitle2_background_border_color',
        'label'           => esc_html__('Border Color', 'materialis'),
        'section'         => $section,
        'default'         => materialis_mod_default('header_element_background_border_color'),
        'transport'       => 'postMessage',
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'output'          => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'property' => 'border-color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.header-homepage p.header-subtitle2',
                'function' => 'css',
                'property' => 'border-color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'spacing',
        'settings'        => 'header_content_subtitle2_background_border_thickness',
        'label'           => esc_html__('Background Border Thickness', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => materialis_mod_default('header_element_background_border_thickness'),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'settings'        => 'header_content_subtitle2_background_shadow',
        'label'           => esc_html__('Shadow Elevation', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => materialis_mod_default('header_element_background_shadow'),
        'choices'         => array(
            'min'  => '0',
            'max'  => '12',
            'step' => '1',
        ),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'header_content_subtitle2_background_enabled',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


}, 1, 3);
