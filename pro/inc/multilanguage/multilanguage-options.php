<?php

add_action('customize_register', 'materialis_add_multilanguage_customizer_section');

function materialis_add_multilanguage_customizer_section($wp_customize)
{
    $wp_customize->add_section('materialis_multilanguage_settings', array(
        'title'      => __('Multilanguage Options', 'materialis'),
        'panel'      => 'general_settings',
        'capability' => 'edit_theme_options',
    ));

}

// Controls
function add_materialis_multilanguage_controls()
{
    $section       = 'materialis_multilanguage_settings';
    $settingPrefix = "materialis_multilanguage_";

    Kirki::add_field('materialis', array(
        'type'     => 'checkbox',
        'settings' => 'materialis_show_language_switcher',
        'label'    => __('Show side language switcher', 'materialis'),
        'section'  => $section,
        'default'  => true,
    ));

    if (function_exists('pll_current_language')) {
        Kirki::add_field('materialis', array(
            'type'     => 'checkbox',
            'settings' => 'materialis_polylang_display_as_dropdown',
            'label'    => __('Display as dropdown', 'materialis'),
            'section'  => $section,
            'default'  => false,

        ));
    }


    Kirki::add_field('materialis', array(
        'type'            => 'color',
        'settings'        => "{$settingPrefix}background_color",
        'label'           => __('Switcher background color', 'materialis'),
        'section'         => $section,
        'default'         => "#ffffff",
        'choices'         => array(
            'alpha' => true,
        ),
        "output"          => array(
            array(
                'element'  => '.materialis-language-switcher',
                'property' => 'background-color',
                'suffix'   => ' !important',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.materialis-language-switcher',
                'function' => 'css',
                'property' => 'background-color',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'materialis_show_language_switcher',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


    Kirki::add_field('materialis', array(
        'type'            => 'dimension',
        'settings'        => "{$settingPrefix}position",
        'label'           => __('Switcher top offset', 'materialis'),
        'section'         => $section,
        'default'         => "80px",
        "output"          => array(
            array(
                'element'  => '.materialis-language-switcher',
                'property' => 'top',
                'suffix'   => ' !important',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.materialis-language-switcher',
                'function' => 'css',
                'property' => 'top',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'materialis_show_language_switcher',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
}

add_action('init', 'materialis_multilanguage_settings');
function materialis_multilanguage_settings()
{

    if ( ! class_exists("\Kirki")) {
        return;
    }

    add_materialis_multilanguage_controls();
}
