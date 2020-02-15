<?php

add_filter("materialis_header_top_bar_class", function($header_top_bar_class) {
    if (materialis_get_theme_mod('top_bar_background_type', 'color') == 'gradient') {
        $header_top_bar_class = materialis_get_theme_mod("top_bar_background_gradient", "easter_blueberry");
    }
    return $header_top_bar_class;
});

add_action("materialis_top_bar_options_before", 'materialis_add_top_bar_options_pro', 1);

function materialis_add_top_bar_options_pro($section)
{

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Top Bar General Options', 'materialis'),
        'section'  => $section,
        'settings' => "top_bar_general_options_sep",
        'priority' => 1,
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => 'top_bar_background_type',
        'label'           => __('Background Type', 'materialis'),
        'section'         => $section,
        'choices'         => apply_filters('materialis_footer_background', array(
            'color'    => __('Color', 'materialis'),
            'gradient' => __('Gradient', 'materialis'),
        )),
        'default'         => 'color',
        'sanitize_callback' => 'sanitize_text_field',
        'priority'        => 1,
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Background Color', 'materialis'),
        'section' => $section,
        'settings'  => 'top_bar_background_color',
        'default'   => "#222",
        'priority'  => 1,
        'choices'   => array(
            'alpha' => true,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'background',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'background',
                'function' => 'css',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => "top_bar_background_type",
                'operator' => '==',
                'value'    => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'web-gradients',
        'label'     => esc_html__('Background Gradient', 'materialis'),
        'section'   => $section,
        'settings'  => 'top_bar_background_gradient',
        'default'   => 'easter_blueberry',
        "priority"  => 1,
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'top_bar_background_type',
                'operator' => '==',
                'value'    => 'gradient',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'number',
        'label'           => __('Background Height', 'materialis'),
        'section'         => $section,
        'settings'        => 'top_bar_height',
        'choices'         => array(
            'min'         => 5,
            'max'         => 200,
            'step'        => 1,
        ),
        'default'         => '40',
        'priority'        => 1,
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => '.header-top-bar-inner',
                'property' => 'height',
                'suffix'   => 'px',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => '.header-top-bar-inner',
                'property' => 'height',
                'function' => 'css',
                'suffix'   => 'px',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),

    ));

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'label'    => __('Show Bottom Border', 'materialis'),
        'section'  => $section,
        'priority' => 1,
        'settings' => "top_bar_enable_bottom_border",
        'default'  => false,
        'active_callback' => array(
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Bottom Border Color', 'materialis'),
        'section' => $section,
        'settings'  => 'top_bar_bottom_border_color',
        'priority'  => 1,
        'choices'   => array(
            'alpha' => true,
        ),
        'default' => "rgba(39, 124, 234, 1)",
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-color',
                'function' => 'css',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "top_bar_enable_bottom_border",
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    materialis_add_kirki_field(array(
        'type'            => 'number',
        'label'           => __('Bottom Border Thickness', 'materialis'),
        'section'         => $section,
        'settings'        => 'top_bar_bottom_border_thickness',
        'choices'         => array(
            'min'         => 1,
            'max'         => 50,
            'step'        => 1,
        ),
        'default'         => '5',
        'priority'        => 1,
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-width',
                'suffix'   => 'px'
            ),
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-style',
                'value_pattern' => 'solid'
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-width',
                'suffix'   => 'px',
                'function' => 'css',
            ),
            array(
                'element'  => '.header-top-bar',
                'property' => 'border-bottom-style',
                'function' => 'css',
                'value_pattern' => 'solid'
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "top_bar_enable_bottom_border",
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),

    ));

}
