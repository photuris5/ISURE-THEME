<?php


add_action("materialis_header_background_overlay_settings", "materialis_front_page_header_split_header_settings", 1, 5);

function materialis_front_page_header_split_header_settings($section, $prefix, $group, $inner, $priority)
{
    $prefix   = $inner ? "inner_header_" : "header_";
    $section  = $inner ? "header_image" : "header_background_chooser";
    $priority = 5;
    $group    = "{$prefix}split_options_group_button";

    $defaultColor = "#000000";

    /*
    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Split Background', 'materialis'),
        'section'  => $section,
        'settings' => "{$prefix}split_header_separator",
        'priority' => $priority
    ));*/

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'label'    => __('Use Split Background', 'materialis'),
        'section'  => $section,
        'settings' => $prefix . 'split_header',
        'default'  => false,
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => $group,
        'label'           => esc_html__('Options', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'in_row_with'     => array($prefix . 'split_header'),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Split Options', 'materialis'),
        'section'  => $section,
        'settings' => "{$prefix}split_header_options_separator",
        'priority' => $priority,
        'group'    => $group,
    ));


    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}split_header_color",
        'label'    => esc_attr__('Color', 'materialis'),
        'section'  => $section,
        'choices'  => array(
            'alpha' => true,
        ),
        'default'  => $defaultColor,

        'transport' => 'postMessage',


        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'group'           => $group,
    ));


    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'label'     => __('Angle', 'materialis'),
        'section'   => $section,
        'settings'  => $prefix . 'split_header_angle',
        'default'   => 0,
        'transport' => 'postMessage',
        'choices'   => array(
            'min'  => '-180',
            'max'  => '180',
            'step' => '5',
        ),


        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'group'           => $group,
    ));

    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'label'     => __('Size', 'materialis'),
        'section'   => $section,
        'settings'  => $prefix . 'split_header_size',
        'default'   => 50,
        'transport' => 'postMessage',
        'choices'   => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),


        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'group'           => $group,
    ));


    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'label'     => __('Angle on mobile devices', 'materialis'),
        'section'   => $section,
        'settings'  => $prefix . 'split_header_angle_mobile',
        'default'   => 90,
        'transport' => 'postMessage',
        'choices'   => array(
            'min'  => '-180',
            'max'  => '180',
            'step' => '5',
        ),


        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'group'           => $group,
    ));

    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'label'     => __('Size on mobile devices', 'materialis'),
        'section'   => $section,
        'settings'  => $prefix . 'split_header_size_mobile',
        'default'   => 50,
        'transport' => 'postMessage',
        'choices'   => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),


        'active_callback' => array(
            array(
                'setting'  => $prefix . 'split_header',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'priority'        => $priority,
        'group'           => $group,
    ));

}

// SPLIT HEADER DISPLAY
function materialis_get_split_header_gradient_value($color, $angle, $size, $fade = 0)
{
    $angle          = -90 + intval($angle);
    $fade           = intval($fade) / 2;
    $transparentMax = (100 - $size) - $fade - 0.1;
    $colorMin       = (100 - $size) + $fade;

    $gradient = "{$angle}deg , transparent 0%, transparent {$transparentMax}%, {$color} {$colorMin}%, {$color} 100%";

    return $gradient;
}

// print split header option
add_action('wp_head', function () {

    $defaultColor = '#000000';
    $prefix       = materialis_is_inner(true) ? "inner_header" : "header";
    $enabled      = materialis_get_theme_mod("{$prefix}_split_header", false);

    if ( ! intval($enabled)) {
        return;
    }

    $color = materialis_get_theme_mod("{$prefix}_split_header_color", $defaultColor);
    $angle = materialis_get_theme_mod("{$prefix}_split_header_angle", 0);
    $fade  = materialis_get_theme_mod("{$prefix}_split_header_fade", 0);
    $size  = materialis_get_theme_mod("{$prefix}_split_header_size", 50);


    $gradient = materialis_get_split_header_gradient_value($color, $angle, $size, $fade);


    $angle = materialis_get_theme_mod("{$prefix}_split_header_angle_mobile", 90);
    $size  = materialis_get_theme_mod("{$prefix}_split_header_size_mobile", 50);

    $mobileGradient = materialis_get_split_header_gradient_value($color, $angle, $size, $fade);
    $headerSelector = materialis_is_inner(true) ? ".materialis-inner-page .header" : '.header-homepage';
    ?>
    <style>
        <?php echo $headerSelector; ?> .split-header {
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            position: absolute;
            z-index: -1;
            display: inline-block;
            content: "";
        }
    </style>
    <style data-name="header-split-style">
        <?php echo $headerSelector; ?> .split-header {
            background: linear-gradient(<?php echo $mobileGradient; ?>);
            background: -webkit-linear-gradient(<?php echo $mobileGradient; ?>);
            background: linear-gradient(<?php echo $mobileGradient; ?>);

        }

        @media screen and (min-width: 767px) {
        <?php echo $headerSelector; ?> .split-header {
                background: linear-gradient(<?php echo $gradient; ?>);
                background: -webkit-linear-gradient(<?php echo $gradient; ?>);
                background: linear-gradient(<?php echo $gradient; ?>);

            }
        }

    </style>


    <?php
});


add_action('materialis_after_front_page_header_content', 'materialis_print_split_header_container');
add_action('materialis_after_inner_page_header_content', 'materialis_print_split_header_container');

function materialis_print_split_header_container()
{
    echo "<div class='split-header'></div>";
}
