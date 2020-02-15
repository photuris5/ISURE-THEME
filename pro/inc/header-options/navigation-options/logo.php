<?php


// LOGO SETTINGS SETTINGS - START


function materialis_text_logo_settings($inner = false)
{
    $priority = 3;
    $section  = "navigation_logo";
    $prefix   = $inner ? "inner_header" : "header";


    $separatorLabel = $inner ? __('Inner Page Logo Settings', 'materialis') : __('Front Page Logo Settings', 'materialis');

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => $separatorLabel,
        'settings' => "{$prefix}_nav_logo_separator",
        'section'  => $section,
        'priority' => $priority,
    ));

    $transparent_nav = materialis_get_theme_mod($prefix . '_nav_transparent', materialis_mod_default("{$prefix}_nav_transparent"));

    if ($transparent_nav) {
        $default_logo_color = materialis_get_var("header_text_logo_color");
    } else {
        $default_logo_color = materialis_get_var("header_text_logo_sticky_color");
    }

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_header_text_logo_color",
        'label'    => esc_attr__('Text logo color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'default'  => $default_logo_color,
        'choices'  => array(
            'alpha' => false,
        ),
        'output'   => array(
            array(
                'element'  => materialis_get_nav_text_logo_selector($inner),
                'property' => 'color',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => materialis_get_nav_text_logo_selector($inner),
                'property' => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_header_text_logo_sticky_color",
        'label'    => esc_attr__('Sticky nav text logo color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'default'  => materialis_get_var("header_text_logo_sticky_color"),
        'choices'  => array(
            'alpha' => false,
        ),
        'output'   => array(
            array(
                'element'  => materialis_get_sticky_nav_text_logo_selector($inner),
                'property' => 'color',
                'suffix'   => '!important',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => materialis_get_sticky_nav_text_logo_selector($inner),
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sidebar-button-group',
        'settings' => "{$prefix}_header_nav_logo_typo_group",
        'label'    => __('Text Logo Typography', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        "choices"  => array(
            "{$prefix}_text_logo_typography",
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => "{$prefix}_text_logo_typography",
        'label'     => __('Text Logo Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'font-family'    => 'inherit',
            'font-size'      => '1.5rem',
            'font-weight'    => '400',
            'line-height'    => '100%',
            'letter-spacing' => '0px',
            'subsets'        => array(),
            'text-transform' => 'uppercase',
            'addwebfont'     => true,
        ),
        'output'    => array(
            array(
                'element' => materialis_get_nav_text_logo_selector($inner),
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => materialis_get_nav_text_logo_selector($inner),
            ),
        ),
    ));


}

add_action("materialis_customize_register_options", function() {
    materialis_text_logo_settings(false);
    materialis_text_logo_settings(true);
});

// LOGO SETTINGS SETTINGS - END
