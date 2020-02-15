<?php


add_filter('materialis_defaults', function ($data) {
    $data['footer_layout_widgets_default_print_order'] = array(
        'first_box_widgets',
        'second_box_widgets',
        'third_box_widgets',
        'newsletter_subscriber_widgets',
    );


    return $data;
});

function materialis_footer_layout()
{

    $section = 'footer_settings';

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Footer Layout', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'settings'        => "footer_layout_separator",
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "4",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "footer_layout_group_button",
        'label'           => esc_html__('Footer Layout Options', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "4",
            ),
        ),
    ));

    $group = "footer_layout_group_button";

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Footer Layout Options', 'materialis'),
        'section'         => $section,
        'settings'        => "footer_layout_options_separator",
        'priority'        => 3,
        'group'           => $group,
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "4",
            ),
        ),
    ));

    $widgets_map = array(
        'first_box_widgets'             => __('Widget 1', 'materialis'),
        'second_box_widgets'            => __('Widget 2', 'materialis'),
        'third_box_widgets'             => __('Widget 3', 'materialis'),
        'newsletter_subscriber_widgets' => __('Widget 4', 'materialis'),
    );

    materialis_add_kirki_field(array(
        'type'            => 'sortable',
        'settings'        => 'footer_layout_widgets_print_order',
        'label'           => __('Widgets Order', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'default'         => materialis_mod_default('footer_layout_widgets_default_print_order'),
        'choices'         => apply_filters('materialis_footer_layout_widges_options',
            array(
                'first_box_widgets'             => $widgets_map['first_box_widgets'],
                'second_box_widgets'            => $widgets_map['second_box_widgets'],
                'third_box_widgets'             => $widgets_map['third_box_widgets'],
                'newsletter_subscriber_widgets' => $widgets_map['newsletter_subscriber_widgets'],
            )
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "4",
            ),
        ),
        'group'           => $group,
    ));

    $widgets = materialis_mod_default('footer_layout_widgets_default_print_order', array());

    foreach ($widgets as $wk => $widget) {

        materialis_add_kirki_field(array(
            'type'            => 'slider',
            'label'           => sprintf(__('%s columns', 'materialis'), $widgets_map[$widgets[$wk]]),
            'section'         => $section,
            'priority'        => 3,
            'settings'        => 'footer_layout_widget_width_' . $widget,
            'default'         => 3,
            'transport'       => 'postMessage',
            'choices'         => array(
                'min'  => '1',
                'max'  => '12',
                'step' => '1',
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'footer_template',
                    'operator' => '==',
                    'value'    => "4",
                ),
                array(
                    'setting'  => 'footer_layout_widgets_print_order',
                    'operator' => 'contains',
                    'value'    => $widget,
                ),
            ),
            'group'           => $group,
        ));

    }

}

function materialis_print_footer_4_widgets()
{

    $widgets = materialis_get_theme_mod('footer_layout_widgets_print_order', materialis_mod_default('footer_layout_widgets_default_print_order'));

    foreach ($widgets as $widget) {
        $columns = materialis_get_theme_mod('footer_layout_widget_width_' . $widget, 3);
        ?>
        <div class="col-sm-<?php echo $columns; ?>" data-widget="<?php echo($widget); ?>">
            <?php
            materialis_print_widget($widget);
            /*
            if ($widget == 'newsletter_subscriber_widgets') {
                materialis_print_area_social_icons('footer', 'content', 'footer-social-icons', 5);
            }
            */
            ?>
        </div>
        <?php
    }

}

materialis_footer_layout();

function materialis_footer_background_type()
{

    $section = 'footer_settings';

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Footer Background', 'materialis'),
        'section'  => $section,
        'priority' => 3,
        'settings' => "footer_background_type_separator",
    ));

    materialis_add_kirki_field(array(
        'type'      => 'textarea',
        'settings'  => 'footer_content_copyright_text',
        'label'     => __('Copyright Text', 'materialis'),
        'section'   => $section,
        'priority'  => 2,
        'default'   => __('&copy; {year} {blogname}. Built using WordPress and <a href="#">Materialis Theme</a>.', 'materialis'),
        'transport' => 'postMessage',
    ));

    materialis_add_kirki_field(array(
        'type'              => 'select',
        'settings'          => 'footer_background_type',
        'label'             => esc_html__('Background Type', 'materialis'),
        'section'           => $section,
        'choices'           => apply_filters('materialis_footer_background_types', array(
            'color'    => __('Color', 'materialis'),
            'image'    => __('Image', 'materialis'),
            'gradient' => __('Gradient', 'materialis'),
        )),
        'default'           => 'color',
        'sanitize_callback' => 'sanitize_text_field',
        'priority'          => 3,
    ));

    // Image background settings

    $group = "footer_bg_options_group_button";

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Background Image Options', 'materialis'),
        'section'         => $section,
        'settings'        => "footer_bg_image_separator",
        'priority'        => 3,
        'group'           => $group,
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => "image",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'              => 'image',
        'settings'          => 'footer_bg_image',
        'label'             => esc_html__('Footer Image', 'materialis'),
        'section'           => $section,
        'sanitize_callback' => 'esc_url_raw',
        'default'           => get_template_directory_uri() . "/assets/images/header-bg-image-default.jpg",
        "priority"          => 3,
        'group'             => $group,
        'active_callback'   => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => 'image',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => 'footer_bg_image_size',
        'label'           => esc_html__('Background Image Size', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'group'           => $group,
        'default'         => 'cover',
        'choices'         => array(
            'auto'    => __('Auto', 'materialis'),
            'contain' => __('Contain', 'materialis'),
            'cover'   => __('Cover', 'materialis'),
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer-content'),
                'property' => 'background-size',
            ),

        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer-content'),
                'function' => 'css',
                'property' => 'background-size',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => 'image',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => 'footer_bg_image_position',
        'label'           => esc_html__('Background Image Position', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'group'           => $group,
        'default'         => "center center",
        'choices'         => array(
            "left top"    => __('Left Top', 'materialis'),
            "left center" => __('Left Center', 'materialis'),
            "left bottom" => __('Left Bottom', 'materialis'),

            "center top"    => __('Center Top', 'materialis'),
            "center center" => __('Center Center', 'materialis'),
            "center bottom" => __('Center Bottom', 'materialis'),

            "right top"    => __('Right Top', 'materialis'),
            "right center" => __('Right Center', 'materialis'),
            "right bottom" => __('Right Bottom', 'materialis'),

        ),
        "output"          => array(
            array(
                'element'  => array('.footer-content'),
                'property' => 'background-position',
            ),

        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => array('.footer-content'),
                'function' => 'css',
                'property' => 'background-position',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => 'image',
            ),
        ),
    ));

    // Gradient background settings

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Background Gradient Options', 'materialis'),
        'section'         => $section,
        'settings'        => "footer_bg_gradient_separator",
        'priority'        => 3,
        'group'           => $group,
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => "gradient",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'web-gradients',
        'settings'        => 'footer_bg_gradient',
        'label'           => esc_html__('Footer Gradient', 'materialis'),
        'section'         => $section,
        'default'         => 'plum_plate',
        "priority"        => 3,
        'group'           => $group,
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => 'gradient',
            ),
        ),
    ));

    // color background settings

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Background Color Options', 'materialis'),
        'section'         => $section,
        'settings'        => "footer_bg_color_separator",
        'priority'        => 3,
        'group'           => $group,
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => "color",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Footer Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_background_color',
        'default'         => materialis_footer_default("footer_background_color"),
        'priority'        => 3,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer-content'),
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer-content'),
                'function' => 'css',
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_background_type',
                'operator' => '==',
                'value'    => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "footer_bg_options_group_button",
        'label'           => esc_html__('Options', 'materialis'),
        'section'         => $section,
        'priority'        => 3,
        'description'     => esc_html__('Options', 'materialis'),
        'active_callback' => array(
            array(
                'setting'  => "footer_background_type",
                'operator' => 'in',
                'value'    => array('color', 'image', 'gradient'),
            ),
        ),
        'in_row_with'     => array('footer_background_type'),
    ));

}

materialis_footer_background_type();


function materialis_footer_overlay_options()
{

    $section = 'footer_settings';

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => 'footer_show_overlay',
        'label'    => esc_html__('Show overlay', 'materialis'),
        'section'  => $section,
        'default'  => false,
        'priority' => 4,
    ));


    // overlay options settings

    $group = "footer_overlay_options_group_button";

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Overlay Options', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_options_separator',
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => 'footer_overlay_type',
        'label'           => esc_html__('Overlay Type', 'materialis'),
        'section'         => $section,
        'choices'         => apply_filters('materialis_overlay_types', array(
            'none'     => __('Shape Only', 'materialis'),
            'color'    => __('Color', 'materialis'),
            'gradient' => __('Gradient', 'materialis'),
        )),
        'default'         => 'color',
        'priority'        => 4,
        'group'           => $group,
        'update'          => apply_filters('materialis_footer_overlay_shapes_partial_update', array(
            array(
                "value"  => "none",
                "fields" => array(
                    'footer_overlay_shape' => 'circles',
                ),
            ),
            array(
                "value"  => "color",
                "fields" => array(
                    'footer_overlay_shape' => 'none',
                ),
            ),
            array(
                "value"  => "gradient",
                "fields" => array(
                    'footer_overlay_shape' => 'none',
                ),
            ),
        )),
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Overlay Color Options', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_color_options_separator',
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'footer_overlay_type',
                'operator' => '==',
                'value'    => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Overlay Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_overlay_color',
        'default'         => "#ffffff",
        'priority'        => 4,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer-content.color-overlay::before'),
                'property' => 'background',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer-content.color-overlay::before'),
                'function' => 'css',
                'property' => 'background',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'footer_overlay_type',
                'operator' => '==',
                'value'    => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Overlay Gradient Options', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_gradient_options_separator',
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'footer_overlay_type',
                'operator' => '==',
                'value'    => 'gradient',
            ),
        ),
    ));

    $gradients = materialis_get_parsed_gradients();

    materialis_add_kirki_field(array(
        'type'            => 'gradient-control-pro',
        'label'           => esc_html__('Gradient Colors', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_overlay_gradient_colors',
        'priority'        => 4,
        'group'           => $group,
        'default'         => json_encode($gradients['plum_plate']),
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'footer_overlay_type',
                'operator' => '==',
                'value'    => 'gradient',
            ),
        ),
        'transport'       => 'postMessage',
    ));

    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'label'           => esc_html__('Overlay Opacity', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_opacity',
        'default'         => 0.5,
        'transport'       => 'postMessage',
        'choices'         => array(
            'min'  => '0',
            'max'  => '1',
            'step' => '0.01',
        ),
        "output"          => array(
            array(
                'element'  => array('.footer-content.color-overlay::before'),
                'property' => 'opacity',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer-content.color-overlay::before'),
                'function' => 'css',
                'property' => 'opacity',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
            array(
                'setting'  => 'footer_overlay_type',
                'operator' => 'in',
                'value'    => array('color'),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Overlay Shapes Options', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_shapes_options_separator',
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'label'           => esc_html__('Overlay Shapes', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_overlay_shape',
        'default'         => "none",
        'priority'        => 4,
        'choices'         => materialis_get_header_shapes_overlay(),
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'group'           => $group,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'label'           => esc_html__('Shape Light', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'group'           => $group,
        'settings'        => 'footer_overlay_shape_light',
        'default'         => 0,
        'choices'         => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'       => array('.footer-content::after'),
                'property'      => 'filter',
                'value_pattern' => 'invert($%) ',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'       => array('.footer-content::after'),
                'function'      => 'css',
                'property'      => 'filter',
                'value_pattern' => 'invert($%) ',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_overlay_shape',
                'operator' => '!=',
                'value'    => 'none',
            ),

            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "footer_overlay_options_group_button",
        'label'           => esc_html__('Options', 'materialis'),
        'section'         => $section,
        'priority'        => 4,
        'active_callback' => array(
            array(
                'setting'  => 'footer_show_overlay',
                'operator' => '==',
                'value'    => true,
            ),
        ),
        'in_row_with'     => array('footer_show_overlay'),
    ));


    materialis_add_kirki_field(array(
        'type'      => 'spacing',
        'settings'  => 'footer_spacing',
        'label'     => esc_html__('Footer Spacing', 'materialis'),
        'section'   => $section,
        'priority'  => 4,
        'default'   => materialis_footer_default('footer_spacing', array()),
        'transport' => 'postMessage',
        'output'    => array(
            array(
                'element'     => '.footer .footer-content',
                'property'    => 'padding',
                'media_query' => '@media (min-width: 767px)',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'     => '.footer .footer-content',
                'function'    => 'style',
                'property'    => 'padding',
                'media_query' => '@media (min-width: 767px)',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'label'    => __('Show Footer Border', 'materialis'),
        'section'  => $section,
        'priority' => 4,
        'settings' => "footer_enable_top_border",
        'default'  => true,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => __('Footer Border Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_top_border_color',
        'priority'        => 4,
        'choices'         => array(
            'alpha' => true,
        ),
        'default'         => "#e8e8e8",
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array(
                    '.footer:not(.footer-dark) .footer-content',
                    '.footer-dark'
                ),
                'property' => 'border-top-color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array(
                    '.footer:not(.footer-dark) .footer-content',
                    '.footer-dark'
                ),
                'property' => 'border-top-color',
                'function' => 'css',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "footer_enable_top_border",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
    materialis_add_kirki_field(array(
        'type'            => 'number',
        'label'           => __('Footer Border Thickness', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_top_border_thickness',
        'choices'         => array(
            'min'  => 1,
            'max'  => 50,
            'step' => 1,
        ),
        'default'         => '1',
        'priority'        => 4,
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array(
                    '.footer:not(.footer-dark) .footer-content',
                    '.footer-dark'
                ),
                'property' => 'border-top-width',
                'suffix'   => 'px',
            ),
            array(
                'element'  => array(
                    '.footer:not(.footer-dark) .footer-content',
                    '.footer-dark'
                ),
                'property'      => 'border-top-style',
                'value_pattern' => 'solid',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "footer_enable_top_border",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


}

materialis_footer_overlay_options();

function materialis_footer_default($setting, $default = false)
{
    $values          = materialis_footer_templates_update_pro(array());
    $footer_template = materialis_get_theme_mod("footer_template", "simple");

    $footer_default = array();

    foreach ($values as $key => $value) {
        if ($value['value'] == $footer_template) {
            $footer_default = $value['fields'];
            break;
        }
    }

    return (isset($footer_default[$setting]) ? $footer_default[$setting] : $default);
}

function materialis_footer_fonts_color()
{

    $section = 'footer_settings';

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Footer Colors', 'materialis'),
        'section'  => $section,
        'priority' => 5,
        'settings' => "footer_font_colors_separator",
    ));

    // font colors options and section

    $group = "footer_font_colors_group_button";

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => esc_html__('Footer Colors Options', 'materialis'),
        'section'  => $section,
        'priority' => 5,
        'group'    => $group,
        'settings' => "footer_font_colors_options_separator",
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Title Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_font_title_color',
        'default'         => materialis_footer_default("footer_font_title_color"),
        'priority'        => 5,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer h1, .footer h2, .footer h3, .footer h4, .footer h5, .footer h6'),
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer h1, .footer h2, .footer h3, .footer h4, .footer h5, .footer h6'),
                'function' => 'css',
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("1", "4"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'label'     => esc_html__('Paragraph Color', 'materialis'),
        'section'   => $section,
        'settings'  => 'footer_font_paragraph_color',
        'default'   => materialis_footer_default("footer_font_paragraph_color"),
        'priority'  => 5,
        'group'     => $group,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output"    => array(
            array(
                'element'  => array('.footer p, .footer'),
                'property' => 'color',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => array('.footer p, .footer'),
                'function' => 'css',
                'property' => 'color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Anchor Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_font_anchor_color',
        'default'         => materialis_footer_default("footer_font_anchor_color"),
        'priority'        => 5,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer a', '.footer ul.materialis-footer-menu li a'),
                'property' => 'color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer a', '.footer ul.materialis-footer-menu li a'),
                'function' => 'css',
                'property' => 'color',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("1", "4", "dark"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Anchor Color on Hover', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_font_anchor_hover_color',
        'default'         => materialis_footer_default("footer_font_anchor_hover_color"),
        'priority'        => 5,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer a:hover'),
                'property' => 'color',
            ),
            array(
                'element'  => array('.footer ul.materialis-footer-menu li a:hover'),
                'property' => 'color',

            ),

        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer a:hover'),
                'function' => 'css',
                'property' => 'color',
            ),
            array(
                'element'  => array('.footer ul.materialis-footer-menu li a:hover'),
                'function' => 'css',
                'property' => 'color',

            ),

        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("1", "4", "dark"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Icon Color', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_font_icon_color',
        'default'         => materialis_footer_default("footer_font_icon_color"),
        'priority'        => 5,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer a .mdi', '.footer .mdi'),
                'property' => 'color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer a .mdi', '.footer .mdi'),
                'function' => 'css',
                'property' => 'color',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("1",  "contact-boxes"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'label'           => esc_html__('Icon Color on Hover', 'materialis'),
        'section'         => $section,
        'settings'        => 'footer_font_icon_hover_color',
        'default'         => materialis_footer_default("footer_font_icon_hover_color"),
        'priority'        => 5,
        'group'           => $group,
        'choices'         => array(
            'alpha' => false,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => array('.footer a:hover .mdi'),
                'property' => 'color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => array('.footer a:hover .mdi'),
                'function' => 'css',
                'property' => 'color',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("1",  "contact-boxes"),
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'priority'        => 5,
        'settings'        => 'footer_accent_color',
        'label'           => __('Accent Bg. Color', 'materialis'),
        'section'         => $section,
        'default'         => materialis_footer_default("footer_accent_color"),
        'transport'       => 'postMessage',
        'choices'         => array(
            'alpha' => true,
        ),
        'group'           => $group,
        "output"          => array(
            array(
                'element'  => '.footer-border-accent',
                'property' => 'border-color',
                'suffix'   => ' !important',
            ),
            array(
                'element'  => '.footer-bg-accent',
                'property' => 'background-color',
                'suffix'   => ' !important',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.footer-border-accent',
                'function' => 'css',
                'property' => 'border-color',
                'suffix'   => ' !important',
            ),
            array(
                'element'  => '.footer-bg-accent',
                'function' => 'css',
                'property' => 'background-color',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => 'in',
                'value'    => array("4", "contact-boxes"),
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sidebar-button-group',
        'settings' => "footer_font_colors_group_button",
        'label'    => esc_html__('Footer Colors Options', 'materialis'),
        'section'  => $section,
        'priority' => 5,
    ));

}

materialis_footer_fonts_color();


// print gradient overlay option
add_action('wp_head', function () {
    $type = materialis_get_theme_mod('footer_overlay_type', "color");
    if ($type != "gradient") {
        return;
    }

    $colors = materialis_get_theme_mod('footer_overlay_gradient_colors', "");
    $colors = json_decode($colors, true);

    $gradient = materialis_get_gradient_value($colors['colors'], $colors['angle']);

    ?>
    <style data-name="footer-gradient-overlay">
        .footer-content.color-overlay::before {
            background: <?php echo $gradient; ?>;
        }
    </style>
    <?php
});

add_action('wp_head', 'materialis_print_footer_shape', PHP_INT_MAX);
function materialis_print_footer_shape()
{

    $value           = materialis_get_theme_mod('footer_overlay_shape', "none");
    $overlay_enabled = materialis_get_theme_mod('footer_show_overlay', true);

    if ($value != "none" && $overlay_enabled) {
        $selector = '.footer-content::after';
        $value    = materialis_get_header_shape_overlay_value($value);
        ?>
        <style data-name="footer-shapes">
            <?php echo esc_html($selector)." {background:$value}"; ?>
        </style>
        <?php
    }
}

function materialis_update_footer_settings($dark_bg, $spacing = array())
{
    $settings = array(
        "footer_font_title_color",
        "footer_font_paragraph_color",
        "footer_font_anchor_color",
        "footer_font_anchor_hover_color",
        "footer_font_icon_color",
        "footer_font_icon_hover_color",
        "footer_accent_color",
        "footer_background_color",
    );

    $defaults = array(
        "footer_background_type" => "color",
    );

    foreach ($settings as $index => $setting) {
        $defaults[$setting] = materialis_get_var($setting . "_" . ($dark_bg ? "dark" : "light"));
    }

    if ($dark_bg) {
        $defaults['footer_enable_top_border'] = false;
    } else {
        $defaults['footer_enable_top_border'] = true;
    }


    if (count($spacing)) {
        $defaults = array_merge(
            $defaults,
            array("footer_spacing" => $spacing)
        );
    }

    return $defaults;
}


add_filter("materialis_footer_templates_update", "materialis_footer_templates_update_pro");

function materialis_footer_templates_update_pro($values)
{
    $values = array_merge($values, array(
        array(
            "value"  => "contact-boxes",
            "fields" => materialis_update_footer_settings(true, array(
                'top'    => '0px',
                'bottom' => '0px',
            )),
        ),
        array(
            "value"  => "content-lists",
            "fields" => materialis_update_footer_settings(true, array(
                'top'    => '0px',
                'bottom' => '0px',
            )),
        ),
        array(
            "value"  => "simple",
            "fields" => materialis_update_footer_settings(false, array(
                'top'    => '40px',
                'bottom' => '40px',
            )),
        ),
        array(
            "value"  => "1",
            "fields" => materialis_update_footer_settings(false, array(
                'top'    => '30px',
                'bottom' => '30px',
            )),
        ),
        array(
            "value"  => "4",
            "fields" => materialis_update_footer_settings(false, array(
                'top'    => '15px',
                'bottom' => '15px',
            )),
        ),
        array(
            "value"  => "7",
            "fields" => materialis_update_footer_settings(false, array(
                'top'    => '20px',
                'bottom' => '20px',
            )),
        ),
        array(
            "value"  => "dark",
            "fields" => materialis_update_footer_settings(true, array(
                'top'    => '15px',
                'bottom' => '15px',
            )),
        ),
    ));

    return $values;
}

function materialis_footer_templates_pro($values)
{
    $new = array(
        "1" => __("Copyright / Menu / Social", 'materialis'),
        "4" => __("Columns with widget areas", 'materialis'),
    );

    $result = $values + $new;

    return $result;
}

add_filter('materialis_footer_templates', 'materialis_footer_templates_pro');


function materialis_footer_templates_with_social_pro($values)
{
    $new = array(
        "1",
        "7",
    );

    return array_merge($values, $new);
}

add_filter('materialis_footer_templates_with_social', 'materialis_footer_templates_with_social_pro');


add_action("materialis_customize_register_options", function () {
    materialis_footer_settings_pro();
});


function materialis_footer_settings_pro()
{

    $section = 'footer_settings';

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => esc_html__('Description Content', 'materialis'),
        'section'         => $section,
        'priority'        => 1,
        'group'           => 'footer_content_7_box_group_button',
        'settings'        => "footer_content_box_separator",
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "7",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'              => 'textarea',
        'settings'          => 'footer_content_box_text',
        'label'             => esc_html__('Text', 'materialis'),
        'section'           => $section,
        'priority'          => 1,
        'group'             => 'footer_content_7_box_group_button',
        'default'           => __("Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.", 'materialis'),
        'sanitize_callback' => 'wp_kses_post',
        'active_callback'   => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "7",
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "footer_content_7_box_group_button",
        'label'           => esc_html__('Description Options', 'materialis'),
        'section'         => $section,
        'priority'        => 1,
        'active_callback' => array(
            array(
                'setting'  => 'footer_template',
                'operator' => '==',
                'value'    => "7",
            ),
        ),
    ));

}

function materialis_print_footer_box_description()
{

    $text = materialis_get_theme_mod('footer_content_box_text', __("Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.", 'materialis'));

    echo materialis_wp_kses_post($text);
}


add_filter("materialis_footer_background_atts", function ($attrs) {
    if ( ! isset($attrs['style'])) {
        $attrs['style'] = "";
    } else {
        $attrs['style'] .= ";";
    }

    $bgType = materialis_get_theme_mod('footer_background_type', 'color');
    $theme  = wp_get_theme();

    $show_overlay = materialis_get_theme_mod("footer_show_overlay", false);
    if ($show_overlay) {
        $attrs['class'] .= " color-overlay ";
    }

    switch ($bgType) {
        case 'image':
            $bgImage        = materialis_get_theme_mod('footer_bg_image', get_template_directory_uri() . "/assets/images/header-bg-image-default.jpg");
            $attrs['style'] = 'background-image:url("' . esc_url($bgImage) . '")';
            break;

        case 'gradient':
            $bgGradient     = materialis_get_theme_mod("footer_bg_gradient", "plum_plate");
            $attrs['class'] .= $bgGradient;
            break;
    }

    return $attrs;
});
