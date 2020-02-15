<?php

add_filter("materialis_header_top_bar_content_print", function($areaName, $type) {
    if ($type == 'text') {
        materialis_print_header_top_bar_text($areaName);
    }
}, 1, 2);

add_filter("materialis_get_content_types", function($types) {
    $types['text'] = __("Text", 'materialis');
    return $types;
});

add_filter("materialis_get_content_types_options", function($options) {
    $options['text'] = "materialis_top_bar_text_options";
    return $options;
});


function materialis_top_bar_text_options($area, $section, $priority, $prefix)
{

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Text', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'settings' => "{$prefix}_text_separator",
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_text_group_button",
        'label'           => esc_html__('Text Options', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => array(
            "{$prefix}_text_separator",
            "{$prefix}_text_options_text_color",
            "{$prefix}_text",
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_content",
                'operator' => '==',
                'value'    => 'text',
            ),
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Text Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_text_options_text_color",
        'default'   => materialis_get_var("header_text_logo_color"),
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} span.top-bar-text",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} span.top-bar-text",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'              => 'textarea',
        'settings'          => "{$prefix}_text",
        'label'             => __('Text', 'materialis'),
        'section'           => $section,
        'priority'          => $priority,
        'default'           => "101 Address Avenue,Newport Beach",
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
        'js_vars'           => array(
            array(
                'element'  => ".header-top-bar-area.{$area} .top-bar-text",
                'function' => 'html',
            ),
        ),
    ));
}




/*
    template functions
*/

function materialis_print_header_top_bar_text($area)
{
    $text = materialis_get_theme_mod("header_top_bar_{$area}_text", "101 Address Avenue,Newport Beach");
    printf('<div class="top-bar-field"><span class="top-bar-text">%1$s</span></div> ', $text);
}
