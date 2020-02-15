<?php

add_filter('materialis_inner_pages_header_content_options_after', function($section, $prefix, $priority) {

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings' => 'inner_header_content_title_group',
        'label'    => esc_html__('Title Options', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'inner_header_content_title_typography',
        )
    ));

    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => 'inner_header_content_title_typography',
        'label'     => __('Title Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'font-family'      => 'Roboto',
            'font-size'        => '3.5em',
            'mobile-font-size' => '3.5em',
            'variant'          => '300',
            'line-height'      => '114%',
            'letter-spacing'   => '0.9px',
            'subsets'          => array(),
            'color'            => '#ffffff',
            'text-transform'   => 'none',
            'addwebfont'       => true,
        ),
        'choices'   => array(
            'alpha' => true,
        ),
        'output'    => array(
            array(
                'element' => '.inner-header-description h1.hero-title',
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '.inner-header-description h1.hero-title',
            ),
        ),
    ));

}, 1, 3);

add_filter('materialis_inner_pages_header_content_options_after', function($section, $prefix, $priority) {

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => 'inner_header_content_subtitle_group',
        'label'           => esc_html__('Subtitle Options', 'materialis'),
        'section'         => $section,
        'priority'        => $priority+1,
        'choices'         => array(
            'inner_header_content_subtitle_typography',
        ),
        'active_callback' => array(
            array(
                'setting'  => 'inner_header_show_subtitle',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => 'inner_header_content_subtitle_typography',
        'label'     => __('Subtitle Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority+1,
        'default'   => array(
            'font-family'      => 'Roboto',
            'font-size'        => '1.3em',
            'mobile-font-size' => '1.3em',
            'variant'          => '300',
            'line-height'      => '130%',
            'letter-spacing'   => 'normal',
            'subsets'          => array(),
            'color'            => '#ffffff',
            'text-transform'   => 'none',
            'addwebfont'       => true,
        ),
        'choices'   => array(
            'alpha' => true,
        ),
        'output'    => array(
            array(
                'element' => '.inner-header-description .header-subtitle',
            ),
        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '.inner-header-description .header-subtitle',
            ),
        ),
    ));

}, 2, 3);
