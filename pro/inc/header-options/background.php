<?php

add_action("materialis_header_background_overlay_settings", function ($section, $prefix, $group, $inner, $priority) {
    materialis_add_kirki_field(array(
        'type'     => 'gradient-control-pro',
        'label'    => esc_html__('Gradient', 'materialis'),
        'section'  => $section,
        'settings' => $prefix . '_overlay_gradient_colors',
        'default'  => json_encode(materialis_mod_default($prefix . '_overlay_gradient_colors')),

        'choices' => array(
            'opacity' => 0.8,
        ),

        'active_callback' => array(
            array(
                'setting'  => $prefix . '_overlay_type',
                'operator' => '==',
                'value'    => 'gradient',
            ),
            array(
                'setting'  => $prefix . '_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'transport'       => 'postMessage',
        'group'           => $group,
    ));
}, 2, 5);
add_filter("materialis_get_header_shapes_overlay_filter", function ($result) {
    $shapes = array(
        '10degree-stripes'          => array(
            'label' => __('10deg stripes', 'materialis'),
            'tile'  => false,
        ),
        'rounded-squares-blue'      => array(
            'label' => __('Rounded Squares Blue', 'materialis'),
            'tile'  => false,
        ),
        'many-rounded-squares-blue' => array(
            'label' => __('Many Rounded Squares Blue', 'materialis'),
            'tile'  => false,
        ),
        'two-circles'               => array(
            'label' => __('Two Circles', 'materialis'),
            'tile'  => false,
        ),
        'circles-2'                 => array(
            'label' => __('Circles 2', 'materialis'),
            'tile'  => false,
        ),
        'circles-3'                 => array(
            'label' => __('Circles 3', 'materialis'),
            'tile'  => false,
        ),
        'circles-gradient'          => array(
            'label' => __('Circles Gradient', 'materialis'),
            'tile'  => false,
        ),
        'circles-white-gradient'    => array(
            'label' => __('Circles White Gradient', 'materialis'),
            'tile'  => false,
        ),
        'waves'                     => array(
            'label' => __('Waves', 'materialis'),
            'tile'  => false,
        ),
        'waves-inverted'            => array(
            'label' => __('Waves Inverted', 'materialis'),
            'tile'  => false,
        ),
        'dots'                      => array(
            'label' => __('Dots', 'materialis'),
            'tile'  => true,
        ),
        'left-tilted-lines'         => array(
            'label' => __('Left tilted lines', 'materialis'),
            'tile'  => true,
        ),
        'right-tilted-lines'        => array(
            'label' => __('Right tilted lines', 'materialis'),
            'tile'  => true,
        ),
        'right-tilted-strips'       => array(
            'label' => __('Right tilted strips', 'materialis'),
            'tile'  => false,
        ),
    );

    $shapeURL = materialis_pro_uri('/assets/shapes');
    foreach ($shapes as $shape => $data) {
        $shapes[$shape]['url'] = $shapeURL;
    }

    return array_merge($result, $shapes);
});
