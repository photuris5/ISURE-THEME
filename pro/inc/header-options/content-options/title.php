<?php

add_filter("header_content_title_group_filter", function ($values) {
    $new = array(
        "header_content_title_typography",
        "header_content_title_spacing",

        "header_content_title_background_options_separator",
        "header_content_title_background_enabled",
        "header_content_title_background_color",
        "header_content_title_background_spacing",
        "header_content_title_background_border_radius",
        "header_content_title_background_border_color",
        "header_content_title_background_border_thickness",
        "header_content_title_background_shadow",

        "header_text_morph_separator",
        "header_show_text_morph_animation",
        "header_show_text_morph_animation_info",
        "header_text_morph_alternatives",
    );

    return array_merge($values, $new);
});


add_action("materialis_front_page_header_title_options_before", function ($section, $prefix, $priority) {
    add_filter('materialis_show_header_title_color', '__return_false');
}, 1, 3);

add_action("materialis_front_page_header_title_options_after", function ($section, $prefix, $priority) {

    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => 'header_content_title_typography',
        'label'     => esc_html__('Title Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'font-family'      => 'Roboto',
            'font-size'        => '3.2rem',
            'mobile-font-size' => '2.3em',
            'font-weight'      => '500',
            'line-height'      => '160%',
            'letter-spacing'   => '0.9px',
            'subsets'          => array(),
            'color'            => materialis_get_theme_mod('header_title_color', '#ffffff'),
            'text-transform'   => 'none',
            'addwebfont'       => true,
        ),
        'choices'   => array(
            "alpha" => true,
        ),
        'output'    => array(
            array(
                'element' => '.header-homepage h1.hero-title',
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => ".header-homepage h1.hero-title",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'header_content_title_spacing',
        'label'     => esc_html__('Title Spacing', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'top'    => '0',
            'bottom' => '20px',
        ),
        'transport' => 'postMessage',
        'output'    => array(
            array(
                'element'  => '.header-homepage .hero-title',
                'property' => 'margin',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => ".header-homepage .hero-title",
                'function' => 'style',
                'property' => 'margin',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Text Animation', 'materialis'),
        'section'  => $section,
        'settings' => "header_text_morph_separator",
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => 'header_show_text_morph_animation',
        'label'    => esc_html__('Enable text animation', 'materialis'),
        'section'  => $section,
        'default'  => false,
        'priority' => $priority,
    ));


    materialis_add_kirki_field(array(
        'type'            => 'ope-info',
        'label'           => esc_html__('The text between the curly braces will be replaced with the alternative texts in the following text area. Type one alternative text per line.', 'materialis'),
        'section'         => $section,
        'settings'        => "header_show_text_morph_animation_info",
        'active_callback' => array(
            array(
                'setting'  => 'header_show_text_morph_animation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    materialis_add_kirki_field(array(
        'type'            => 'textarea',
        'settings'        => 'header_text_morph_alternatives',
        'label'           => esc_html__('Alternative text (one per row)', 'materialis'),
        'section'         => $section,
        'default'         => __("some text\nsome other text", 'materialis'),
        'transport'       => "postMessage",
        'priority'        => $priority,
        'active_callback' => array(
            array(
                'setting'  => 'header_show_text_morph_animation',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

}, 1, 3);

function materialis_apply_header_text_effects($text)
{
    if (is_customize_preview()) {
        return $text;
    }

    $matches = array();

    preg_match_all('/\{([^\}]+)\}/i', $text, $matches);

    $alternative_texts = materialis_get_theme_mod("header_text_morph_alternatives", __("some text\nsome other text", 'materialis'));
    $alternative_texts = preg_split("/[\r\n]+/", $alternative_texts);

    for ($i = 0; $i < count($matches[1]); $i++) {
        $orig    = $matches[0][$i];
        $str     = $matches[1][$i];
        $strings = explode("|", $str);
        if (count($alternative_texts)) {
            $str = json_encode(array_merge($strings, $alternative_texts));
        }
        $text = str_replace($orig, '<span data-text-effect="' . esc_attr($str) . '">' . esc_html($strings[0]) . '</span>', $text);
    }

    return $text;
}


add_filter("materialis_header_title", function ($title) {
    $has_text_effect = materialis_get_theme_mod('header_show_text_morph_animation', true);
    if ($has_text_effect) {
        $title = materialis_apply_header_text_effects($title);
    }

    return $title;
});
add_filter("materialis_theme_deps", function ($deps) {
    $textDomain = materialis_get_text_domain();

    $useTextAnimation = materialis_get_theme_mod('header_show_text_morph_animation', false);

    if ($useTextAnimation) {
        array_push($deps, 'typedjs');
    }

    return $deps;
});
// add text animation scripts
add_action('wp_enqueue_scripts', function () {
    $useTextAnimation = materialis_get_theme_mod('header_show_text_morph_animation', false);

    if (intval($useTextAnimation)) {
        materialis_enqueue_script(
            'typedjs',
            array(
                'src'     => get_template_directory_uri() . '/assets/js/libs/typed.js',
                'deps'    => array('jquery'),
                'has_min' => true,
            )
        );

        $materialis_jssettings = array(
            'header_text_morph_speed' => intval(materialis_get_theme_mod('header_text_morph_speed', 200)),
            'header_text_morph'       => materialis_get_theme_mod('header_show_text_morph_animation', true),
        );

        wp_localize_script('typedjs', 'materialis_morph', $materialis_jssettings);
    }

});
