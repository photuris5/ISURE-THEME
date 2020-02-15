<?php

add_filter('materialis_nav_style_transport', function ($value) {
    return 'postMessage';
});

add_filter("materialis_navigation_wrapper_class", function ($classes, $inner) {
    $prefix = $inner ? "inner_header" : "header";

    if ( ! materialis_get_theme_mod("{$prefix}_nav_use_dark_logo", false)) {
        $classes[] = "white-logo";
    } else {
        $classes[] = "dark-logo";
    }

    if ( ! materialis_get_theme_mod("{$prefix}_nav_fixed_use_dark_logo", true)) {
        $classes[] = "fixed-white-logo";
    } else {
        $classes[] = "fixed-dark-logo";
    }

    return $classes;
}, 1, 2);

function materialis_offcanvas_primary_menu_footer()
{
    $prefix  = "header_offscreen_nav";
    $area    = "offscreen_nav";
    $enabled = materialis_get_theme_mod("{$prefix}_show_social", true);

    if ( ! intval($enabled)) {
        return;
    }

    materialis_print_area_social_icons($prefix, $area);
}

add_action("materialis_offcanvas_primary_menu_footer", "materialis_offcanvas_primary_menu_footer");


add_action("materialis_customize_register_options", function () {
    materialis_navigation_general_options_pro(true);
    materialis_navigation_general_options_pro(false);

    materialis_navigation_menu_settings(true);
    materialis_navigation_menu_settings(false);
    materialis_navigation_submenu_settings(false);

    materialis_navigation_custom_area_settings(false);
    materialis_navigation_custom_area_settings(true);
});

add_action('materialis_after_navigation_separator_option', 'materialis_use_front_page_nav_options', 10, 3);

function materialis_use_front_page_nav_options($inner, $section, $prefix)
{
    if ($inner) {
        materialis_add_kirki_field(array(
            'type'      => 'checkbox',
            'label'     => __('Use front page navigation style', 'materialis'),
            'section'   => $section,
            'priority'  => 1,
            'settings'  => "{$prefix}_nav_use_front_page",
            'default'   => false,
            'transport' => 'refresh',
        ));
    }
}

// filter mods when use same style on inner nav
add_filter('pre_update_option_' . get_stylesheet(), 'materialis_use_same_header_options_in_inner_page');
add_filter('option_theme_mods_' . get_stylesheet(), 'materialis_use_same_header_options_in_inner_page');

function materialis_use_same_header_options_in_inner_page($mods)
{

    if (isset($mods['inner_header_nav_use_front_page']) && intval($mods['inner_header_nav_use_front_page'])) {
        foreach ($mods as $key => $mod) {
            if (strpos($key, 'header_nav') === 0) {
                $inner_key        = "inner_{$key}";
                $mods[$inner_key] = $mod;
            }
        }
    }

    return $mods;
}

add_filter('customize_control_active', 'materialis_inner_header_nav_controls_active_callback_filter', 10, 2);

function materialis_inner_header_nav_controls_active_callback_filter($active, $control)
{

    if ($control->id === 'inner_header_nav_use_front_page' || $control->id === 'inner_header_nav_separator') {
        return true;
    }

    $useFrontPageStyle = materialis_get_theme_mod('inner_header_nav_use_front_page', false);
    $explicit          = array('inner_header_normal_menu_color_group', 'inner_header_fixed_menu_color_group');

    if (strpos($control->id, 'inner_header_nav') === 0) {
        if ($useFrontPageStyle) {
            $active = false;
        }
    }


    if ($useFrontPageStyle && in_array($control->id, $explicit)) {
        $active = false;
    }

    return $active;
}

add_filter('materialis_navigation_types', 'materialis_pro_navigations_types');
add_filter('materialis_nav_bar_menu_settings_partial_update', 'materialis_pro_nav_bar_menu_settings_partial_update', 10, 2);

function materialis_pro_navigations_types($types)
{
    $types = array_merge($types, array(
        'logo-inside-menu'     => __('Logo inside Navigation', 'materialis'),
        'logo-menu-area'       => __('Logo, Navigation, Custom area', 'materialis'),
        'menu-logo-area'       => __('Menu, Logo, Custom area', 'materialis'),
        'logo-area-menu-below' => __('Logo, Custom area, Navigation below', 'materialis'),
    ));

    return $types;
}

function materialis_pro_nav_bar_menu_settings_partial_update($partial_updates, $prefix)
{
    $partial_updates = array_merge($partial_updates, array(
        array(
            "value"  => "logo-inside-menu",
            "fields" => array(
                "{$prefix}_nav_menu_items_align"   => 'center',
                "{$prefix}_fixed_menu_items_align" => 'center',
            ),
        ),
        array(
            "value"  => "logo-menu-area",
            "fields" => array(
                "{$prefix}_nav_menu_items_align"   => 'flex-end',
                "{$prefix}_fixed_menu_items_align" => 'flex-end',
            ),
        ),

        array(
            "value"  => "menu-logo-area",
            "fields" => array(
                "{$prefix}_nav_menu_items_align"   => 'flex-start',
                "{$prefix}_fixed_menu_items_align" => 'flex-start',
            ),
        ),
        array(
            "value"  => "logo-area-menu-below",
            "fields" => array(
                "{$prefix}_nav_menu_items_align"   => 'center',
                "{$prefix}_fixed_menu_items_align" => 'center',
            ),
        ),
    ));

    return $partial_updates;
}


add_filter('materialis_navigation_styles', 'materialis_pro_navigation_styles');

function materialis_pro_navigation_styles($styles)
{
    $styles = array_merge($styles, array(
        'bordered-active-item' => __('Bordered active item', 'materialis'),
        'solid-active-item'    => __('Background color active item', 'materialis'),
    ));

    return $styles;
}


function materialis_navigation_general_options_pro($inner = false)
{
    $priority = 1;
    $section  = $inner ? "inner_page_navigation" : "front_page_navigation";
    $prefix   = $inner ? "inner_header" : "header";


    do_action('materialis_customizer_navigation_options', $section, $prefix, $priority, $inner);
}


// NAVIGATION MENU SETTINGS - START

function materialis_normal_navigation_settings($inner, $prefix, $section, $priority)
{

    materialis_add_kirki_field(array(
        'type'     => 'sidebar-button-group',
        'settings' => "{$prefix}_normal_menu_color_group",
        'label'    => __('Normal Menu Settings', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        "choices"  => array(
            "{$prefix}_normal_menu_color_separator",
            "{$prefix}_nav_menu_items_align",
            "{$prefix}_nav_bar_color",
            "{$prefix}_nav_menu_color",
            "{$prefix}_nav_menu_active_highlight_color",
            "{$prefix}_nav_menu_active_color",
            "{$prefix}_nav_menu_hover_highlight_color",
            "{$prefix}_nav_menu_hover_color",
            "{$prefix}_nav_use_dark_logo",
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Normal Menu Settings', 'materialis'),
        'settings' => "{$prefix}_normal_menu_color_separator",
        'section'  => $section,
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'label'           => esc_html__('Use dark logo image', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'settings'        => "{$prefix}_nav_use_dark_logo",
        'default'         => false,
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'logo_dark',
                'operator' => '!=',
                'value'    => false,
            ),

            array(
                'setting'  => 'logo_dark',
                'operator' => '!=',
                'value'    => '',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_color",
        'label'    => __('Nav Bar Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "rgba(255, 255, 255, 1)",
        'output'  => array(
            array(
                'element'  => materialis_get_nav_selector($inner),
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),

        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => materialis_get_nav_selector($inner),
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_nav_transparent",
                'operator' => '=',
                'value'    => false,
            ),
        ),

    ));

    materialis_add_kirki_field(array(
        'type'      => 'select',
        'settings'  => "{$prefix}_nav_menu_items_align",
        'label'     => esc_attr__('Items align', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'flex-start' => 'Left',
            'center'     => 'Center',
            'flex-end'   => 'Right',
        ),
        'default'   => "flex-end",
        'transport' => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_menu_color",
        'label'     => esc_attr__('Items color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => true,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_menu_color"),
        'transport' => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => "{$prefix}_nav_menu_hover_highlight_color",
        'label'           => esc_attr__('Hover color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'default'         => materialis_get_default_color_for_setting($prefix, "_nav_menu_hover_highlight_color"),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "in",
                "value"    => array("solid-active-item", "material-buttons"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_menu_hover_color",
        'label'     => esc_attr__('Hover item text color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_menu_hover_color"),
        'transport' => 'postMessage',

    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => "{$prefix}_nav_menu_active_highlight_color",
        'label'           => esc_attr__('Active item color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'default'         => materialis_get_default_color_for_setting($prefix, "_nav_menu_active_highlight_color"),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "in",
                "value"    => array("solid-active-item", "material-buttons"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_menu_active_color",
        'label'     => esc_attr__('Active item text color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_menu_active_color"),
        'transport' => 'postMessage',

    ));

}

function materialis_sticky_navigation_settings($inner, $prefix, $section, $priority)
{

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_fixed_menu_color_group",
        'label'           => __('Sticky Menu Settings', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => array(
            "{$prefix}_fixed_menu_color_separator",
            "{$prefix}_fixed_menu_items_align",
            "{$prefix}_nav_fixed_bar_color",
            "{$prefix}_nav_fixed_menu_color",
            "{$prefix}_nav_fixed_menu_active_highlight_color",
            "{$prefix}_nav_fixed_menu_active_color",
            "{$prefix}_nav_fixed_menu_hover_highlight_color",
            "{$prefix}_nav_fixed_menu_hover_color",
            "{$prefix}_nav_fixed_use_dark_logo",
        ),
        "active_callback" => array(
            array(
                'setting'  => "{$prefix}_nav_sticked",
                'operator' => '=',
                'value'    => true,
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Sticky Menu Colors', 'materialis'),
        'settings' => "{$prefix}_fixed_menu_color_separator",
        'section'  => $section,
        'priority' => $priority,
    ));


    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'label'           => esc_html__('Use dark logo image', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'settings'        => "{$prefix}_nav_fixed_use_dark_logo",
        'default'         => true,
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => 'logo_dark',
                'operator' => '!=',
                'value'    => false,
            ),

            array(
                'setting'  => 'logo_dark',
                'operator' => '!=',
                'value'    => '',
            ),
        ),
    ));

    $parent_selector = $inner ? '.materialis-inner-page' : '.materialis-front-page';
    materialis_add_kirki_field(array(
        'type'      => 'select',
        'settings'  => "{$prefix}_fixed_menu_items_align",
        'label'     => esc_attr__('Items align', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'flex-start' => 'Left',
            'center'     => 'Center',
            'flex-end'   => 'Right',
        ),
        'default'   => "flex-end",
        'transport' => 'postMessage',
        'output'    => array(
            array(
                'element'  => "$parent_selector .fixto-fixed .main_menu_col, $parent_selector .fixto-fixed .main-menu",
                'property' => 'justify-content',
                'suffix'   => '!important',
            ),
        ),
        'js_vars'   => array(
            array(
                'element'  => "$parent_selector .fixto-fixed .main_menu_col, $parent_selector .fixto-fixed .main-menu",
                'property' => 'justify-content',
                'suffix'   => '!important',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_fixed_bar_color",
        'label'    => __('Nav Bar Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "rgba(255, 255, 255, 1)",
        'output'  => array(
            array(
                'element'  => materialis_get_sticky_nav_selector($inner),
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => materialis_get_sticky_nav_selector($inner),
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),

    ));


    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_fixed_menu_color",
        'label'     => esc_attr__('Items color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => true,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_fixed_menu_color"),
        'transport' => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => "{$prefix}_nav_fixed_menu_active_highlight_color",
        'label'           => esc_attr__('Active item color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'default'         => materialis_get_default_color_for_setting($prefix, "_nav_fixed_menu_active_highlight_color"),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "in",
                "value"    => array("solid-active-item", "material-buttons"),
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => "{$prefix}_nav_fixed_menu_hover_highlight_color",
        'label'           => esc_attr__('Hover color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        'default'         => materialis_get_default_color_for_setting($prefix, "_nav_fixed_menu_hover_highlight_color"),
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "in",
                "value"    => array("solid-active-item", "material-buttons"),
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_fixed_menu_hover_color",
        'label'     => esc_attr__('Hover item text color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_fixed_menu_hover_color"),
        'transport' => 'postMessage',

    ));

    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_nav_fixed_menu_active_color",
        'label'     => esc_attr__('Active item text color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'default'   => materialis_get_default_color_for_setting($prefix, "_nav_fixed_menu_active_color"),
        'transport' => 'postMessage',

    ));

}

function materialis_navigation_typography($inner, $prefix, $section, $priority)
{
    materialis_add_kirki_field(array(
        'type'     => 'sidebar-button-group',
        'settings' => "{$prefix}_nav_typography_group",
        'label'    => __('Navigation Typography', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        "choices"  => array(
            "{$prefix}_nav_typography",
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'typography',
        'settings' => "{$prefix}_nav_typography",
        'label'    => __('Navigation Typography', 'materialis'),
        'section'  => $section,
        'default'  => array(
            'font-family'      => 'Roboto',
            'font-size'        => '14px',
            'variant'          => '400',
            'line-height'      => '160%',
            'letter-spacing'   => '1px',
            'subsets'          => array(),
            'text-transform'   => 'uppercase',
            'mobile-font-size' => '',
            'addwebfont'       => true,
        ),
        'output'   => array(
            array(
                'element' => $inner ? '.materialis-inner-page #main_menu > li > a' : '.materialis-front-page #main_menu > li > a',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => $inner ? '.materialis-inner-page #main_menu > li > a' : '.materialis-front-page #main_menu > li > a',
            ),
        ),
    ));
}

function materialis_navigation_menu_settings($inner = false)
{
    $priority = 2;
    $section  = $inner ? "inner_page_navigation" : "front_page_navigation";
    $prefix   = $inner ? "inner_header" : "header";

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Navigation Menu Settings', 'materialis'),
        'settings' => "{$prefix}_nav_typo_separator",
        'section'  => $section,
        'priority' => $priority,
    ));

    materialis_normal_navigation_settings($inner, $prefix, $section, $priority);
    materialis_sticky_navigation_settings($inner, $prefix, $section, $priority);
    materialis_navigation_typography($inner, $prefix, $section, $priority);
}

// NAVIGATION MENU SETTINGS - END


// NAVIGATION SUBMENU SETTINGS - START

function materialis_navigation_submenu_settings($inner)
{
    $priority = 3;
    $section  = $inner ? "inner_page_navigation" : "front_page_navigation";
    $prefix   = $inner ? "inner_header" : "header";

    // sub menu settings
    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Navigation submenu settings', 'materialis'),
        'settings' => "{$prefix}_nav_submenu_separator",
        'section'  => $section,
        'priority' => $priority,

    ));

    if ( ! $inner) {
        materialis_add_kirki_field(array(
            'type'     => 'sidebar-button-group',
            'settings' => "{$prefix}_submenus_color_group",
            'label'    => __('Submenu Colors', 'materialis'),
            'section'  => $section,
            'priority' => $priority,
            "choices"  => array(
                "{$prefix}_nav_submenu_background_color",
                "{$prefix}_nav_submenu_text_color",
                "{$prefix}_nav_submenu_hover_background_color",
                "{$prefix}_nav_submenu_hover_text_color",
            ),
        ));


        materialis_add_kirki_field(array(
            'type'      => 'color',
            'settings'  => "{$prefix}_nav_submenu_background_color",
            'label'     => esc_attr__('Background Color', 'materialis'),
            'section'   => $section,
            'choices'   => array(
                'alpha' => true,
            ),
            'default'   => materialis_get_var("dd_submenu_bg"),
            'priority'  => $priority,
            'transport' => 'postMessage',
        ));


        materialis_add_kirki_field(array(
            'type'      => 'color',
            'settings'  => "{$prefix}_nav_submenu_text_color",
            'label'     => esc_attr__('Text Color', 'materialis'),
            'section'   => $section,
            'choices'   => array(
                'alpha' => true,
            ),
            'default'   => materialis_get_var("dd_submenu_color"),
            'priority'  => $priority,
            'transport' => 'postMessage',
        ));


        materialis_add_kirki_field(array(
            'type'      => 'color',
            'settings'  => "{$prefix}_nav_submenu_hover_background_color",
            'label'     => esc_attr__('Hover Background Color', 'materialis'),
            'section'   => $section,
            'priority'  => $priority,
            'choices'   => array(
                'alpha' => true,
            ),
            'default'   => materialis_get_theme_colors("color1"),
            'transport' => 'postMessage',
        ));

        materialis_add_kirki_field(array(
            'type'      => 'color',
            'settings'  => "{$prefix}_nav_submenu_hover_text_color",
            'label'     => esc_attr__('Hover Text Color', 'materialis'),
            'section'   => $section,
            'priority'  => $priority,
            'choices'   => array(
                'alpha' => true,
            ),
            'default'   => "#ffffff",
            'transport' => 'postMessage',
        ));


        materialis_add_kirki_field(array(
            'type'     => 'sidebar-button-group',
            'settings' => "{$prefix}_nav_submenu_item_typography_group",
            'label'    => __('Item Typography', 'materialis'),
            'section'  => $section,
            'priority' => $priority,
            "choices"  => array(
                "{$prefix}_nav_submenu_item_typography",
            ),

        ));
    }


    materialis_add_kirki_field(array(
        'type'      => 'typography',
        'settings'  => "{$prefix}_nav_submenu_item_typography",
        'label'     => __('Item Typography', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'default'   => array(
            'font-family'      => 'Roboto',
            'font-size'        => '0.94rem',
            'variant'          => '400',
            'line-height'      => '1.5',
            'letter-spacing'   => '0.7px',
            'subsets'          => array(),
            'text-transform'   => 'none',
            'addwebfont'       => true,
            'mobile-font-size' => '',
        ),
        'output'    => array(
            array(
                'element' => '#main_menu li li > a',
            ),

        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '#main_menu li li > a',
            ),
        ),
    ));
}

// NAVIGATION SUBMENU SETTINGS - END


function materialis_navigation_custom_area_buttons_setting($prefix, $section, $priority)
{

    $companion = apply_filters('materialis_is_companion_installed', false);

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_navigation_custom_area_buttons_group",
        'label'           => __('Buttons Settings', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => array(
            "{$prefix}_navigation_custom_area_buttons",
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_navigation_custom_area_type",
                'operator' => '==',
                'value'    => 'buttons',
            ),
            array(
                'setting'  => "{$prefix}_nav_bar_type",
                'operator' => 'contains',
                'value'    => 'area',
            ),
        ),
    ));

    materialis_add_kirki_field(
        array(
            'type'     => 'repeater',
            'settings' => "{$prefix}_navigation_custom_area_buttons",
            'label'    => esc_html__('Buttons', 'materialis'),
            'section'  => $section,
            "priority" => $priority,
            "default"  => array(
                array(
                    'label'  => __('Get Started', 'materialis'),
                    'url'    => '#',
                    'target' => '_self',
                    'class'  => 'button color1',
                ),
            ),

            'row_label' => array(
                'type'  => 'text',
                'value' => esc_attr__('Button', 'materialis'),
            ),
            "fields"    => apply_filters('materialis_navigation_custom_area_buttons_fields', array(
                "label" => array(
                    'type'    => $companion ? 'hidden' : 'text',
                    'label'   => esc_attr__('Label', 'materialis'),
                    'default' => 'Action Button',
                ),
                "url"   => array(
                    'type'    => $companion ? 'hidden' : 'text',
                    'label'   => esc_attr__('Link', 'materialis'),
                    'default' => '#',
                ),

                "target" => array(
                    'type'    => 'hidden',
                    'label'   => esc_attr__('Target', 'materialis'),
                    'default' => '_self',
                ),

                "class" => array(
                    'type'    => 'hidden',
                    'label'   => esc_attr__('Class', 'materialis'),
                    'default' => 'button',
                ),
            )),
        )
    );
}

function materialis_nav_bar_default_icons()
{
    $default_icons                                       = materialis_default_icons();
    $default_icons[count($default_icons) - 1]['enabled'] = false;

    return $default_icons;
}


function materialis_navigation_custom_area_social_icons($prefix, $section, $priority, $inner)
{

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Social Icons Colors', 'materialis'),
        'settings' => "{$prefix}_navigation_custom_area_social_color_sep",
        'section'  => $section,
        'priority' => $priority,
    ));

    $styleSelector       = $inner ? ".materialis-inner-page .inner_header-nav-area .social-icons a" : ".materialis-front-page .header-nav-area .social-icons a";
    $styleStickySelector = $inner ? ".materialis-inner-page .fixto-fixed .inner_header-nav-area .social-icons a" : ".materialis-front-page .fixto-fixed .header-nav-area .social-icons a";

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_navigation_custom_area_social_color",
        'label'    => __('Normal Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#FFFFFF",
        'output'  => array(
            array(
                'element'  => $styleSelector,
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => $styleSelector,
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_navigation_custom_area_social_color_sticky",
        'label'    => __('Sticky Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#000000",
        'output'  => array(
            array(
                'element'  => $styleStickySelector,
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => $styleStickySelector,
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Social Icons', 'materialis'),
        'settings' => "{$prefix}_navigation_custom_area_social_sep",
        'section'  => $section,
        'priority' => $priority,
    ));

    $group_choices = array(
        "{$prefix}_navigation_custom_area_social_color_sep",
        "{$prefix}_navigation_custom_area_social_color",
        "{$prefix}_navigation_custom_area_social_color_sticky",
        "{$prefix}_navigation_custom_area_social_sep",
    );


    $icon_setting_prefix = "{$prefix}_nav_custom_area";
    $default_icons       = materialis_nav_bar_default_icons();

    for ($i = 0; $i < count($default_icons); $i++) {
        materialis_add_kirki_field(array(
            'type'     => 'checkbox',
            'label'    => sprintf(__('Show Icon %d', 'materialis'), ($i + 1)),
            'section'  => $section,
            'priority' => $priority,
            'settings' => "{$icon_setting_prefix}_social_icon_{$i}_enabled",
            'default'  => $default_icons[$i]['enabled'],
        ));

        $group_choices[] = "{$icon_setting_prefix}_social_icon_{$i}_enabled";

        materialis_add_kirki_field(array(
            'type'     => 'material-icons-icon-control',
            'settings' => "{$icon_setting_prefix}_social_icon_{$i}_icon",
            'label'    => sprintf(__('Icon %d icon', 'materialis'), ($i + 1)),
            'section'  => $section,
            'priority' => $priority,
            'default'  => $default_icons[$i]['icon'],

        ));

        $group_choices[] = "{$icon_setting_prefix}_social_icon_{$i}_icon";

        materialis_add_kirki_field(array(
            'type'      => 'text',
            'settings'  => "{$icon_setting_prefix}_social_icon_{$i}_link",
            'transport' => 'postMessage',
            'label'     => sprintf(__('Field %d link', 'materialis'), ($i + 1)),
            'section'   => $section,
            'priority'  => $priority,
            'default'   => $default_icons[$i]['link'],
        ));

        $group_choices[] = "{$icon_setting_prefix}_social_icon_{$i}_link";
    }

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_navigation_custom_area_social_group",
        'label'           => __('Social Icons Settings', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => $group_choices,
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_navigation_custom_area_type",
                'operator' => '==',
                'value'    => 'social',
            ),
            array(
                'setting'  => "{$prefix}_nav_bar_type",
                'operator' => 'contains',
                'value'    => 'area',
            ),
        ),
    ));
}

function materialis_navigation_custom_area_search($prefix, $section, $priority, $inner)
{
    $styleSelectorStart       = $inner ? ".materialis-inner-page .nav-search.widget_search .search-form" : ".materialis-front-page  .nav-search.widget_search .search-form";
    $styleStickySelectorStart = $inner ? ".materialis-inner-page .fixto-fixed .nav-search.widget_search .search-form" : ".materialis-front-page .fixto-fixed  .nav-search.widget_search .search-form";

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Search Bar Colors', 'materialis'),
        'settings' => "{$prefix}_navigation_custom_area_search_color_sep",
        'section'  => $section,
        'priority' => $priority,
    ));


    materialis_add_kirki_field(array(
        'type'      => 'color',
        'settings'  => "{$prefix}_navigation_custom_area_search_color",
        'label'     => __('Normal Color', 'materialis'),
        'section'   => $section,
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => true,
        ),
        'default'   => "#FFFFFF",
        'output'    => array(
            array(
                'element'  => "{$styleSelectorStart} *",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleSelectorStart} input",
                'property' => 'border-bottom-color',
                'suffix'   => '!important',
            ),

            array(
                'element'  => "{$styleSelectorStart} input::-webkit-input-placeholder",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleSelectorStart} input:-ms-input-placeholder",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleSelectorStart} input:-moz-placeholder",
                'property' => 'color',
            ),
            array(
                'element'  => "{$styleSelectorStart} .mdc-line-ripple",
                'property' => 'background-color',
            ),

        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "{$styleSelectorStart} *",
                'property' => 'color',
                'function' => 'style',
            ),
            array(
                'element'  => "{$styleSelectorStart} input",
                'property' => 'border-bottom-color',
                'suffix'   => '!important',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleSelectorStart} input::-webkit-input-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleSelectorStart} input:-ms-input-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleSelectorStart} input:-moz-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),
            array(
                'element'  => "{$styleSelectorStart} .mdc-line-ripple",
                'property' => 'background-color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_navigation_custom_area_search_color_sticky",
        'label'    => __('Sticky Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#000000",
        'output'  => array(
            array(
                'element'  => "{$styleStickySelectorStart} *",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input",
                'property' => 'border-bottom-color',
                'suffix'   => '!important',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input::-webkit-input-placeholder",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input:-ms-input-placeholder",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input:-moz-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} .mdc-line-ripple",
                'property' => 'background-color',
            ),
        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "{$styleStickySelectorStart} *",
                'property' => 'color',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input",
                'property' => 'border-bottom-color',
                'suffix'   => '!important',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input::-webkit-input-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input:-ms-input-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} input:-moz-placeholder",
                'property' => 'color',
                'function' => 'style',
            ),

            array(
                'element'  => "{$styleStickySelectorStart} .mdc-line-ripple",
                'property' => 'background-color',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_navigation_custom_area_search_box_group",
        'label'           => __('Search Box Settings', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => array(
            "{$prefix}_navigation_custom_area_search_color_sep",
            "{$prefix}_navigation_custom_area_search_color",
            "{$prefix}_navigation_custom_area_search_color_sticky",
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_navigation_custom_area_type",
                'operator' => '==',
                'value'    => 'search',
            ),
            array(
                'setting'  => "{$prefix}_nav_bar_type",
                'operator' => 'contains',
                'value'    => 'area',
            ),
        ),
    ));
}


function materialis_navigation_custom_area_settings($inner)
{
    $priority = 3;
    $section  = $inner ? "inner_page_navigation" : "front_page_navigation";
    $prefix   = $inner ? "inner_header" : "header";

    $nav_bar_type_active_cb = array(
        array(
            'setting'  => "{$prefix}_nav_bar_type",
            'operator' => 'contains',
            'value'    => 'area',
        ),
    );

    materialis_add_kirki_field(array(
        'type'            => 'sectionseparator',
        'label'           => __('Navigation custom area settings', 'materialis'),
        'settings'        => "{$prefix}_nav_custom_area_separator",
        'section'         => $section,
        'priority'        => $priority,
        'active_callback' => $nav_bar_type_active_cb,
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_navigation_custom_area_type",
        'label'           => esc_html__('Custom area content', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => 'buttons',
        'choices'         => array(
            'buttons' => __('Buttons List', 'materialis'),
            'social'  => __('Social Icons', 'materialis'),
            'search'  => __('Search bar', 'materialis'),
        ),
        'active_callback' => $nav_bar_type_active_cb,
    ));

    materialis_navigation_custom_area_buttons_setting($prefix, $section, $priority);

    materialis_navigation_custom_area_social_icons($prefix, $section, $priority, $inner);

    materialis_navigation_custom_area_search($prefix, $section, $priority, $inner);

}


// NAVIGATION APPLY SETTINGS


add_filter('materialis_primary_drop_menu_classes', function ($classes) {
    $prefix    = materialis_is_front_page(true) ? "header" : "inner_header";
    $variation = materialis_get_theme_mod("{$prefix}_nav_style", "material-buttons");

    $border_style  = materialis_get_theme_mod("{$prefix}_nav_border_style", "bottom");
    $border_effect = materialis_get_theme_mod("{$prefix}_nav_border_effect", "none");
    $solid_effect  = materialis_get_theme_mod("{$prefix}_nav_solid_effect", "none");
    $single_grow   = materialis_get_theme_mod("{$prefix}_nav_border_single_grow", "left");
    $double_grow   = materialis_get_theme_mod("{$prefix}_nav_border_double_grow", "left-and-right");

    $grow_from = ($border_style === "top-and-bottom") ? $double_grow : $single_grow;

    if ($border_effect !== "borders-grow") {
        $grow_from = "none";
    }

    if ($variation === "bordered-active-item") {
        $classes[] = "{$variation}--{$border_style}";
        $classes[] = "effect-{$border_effect}";
        $classes[] = "grow-from-{$grow_from}";

    }


    if ($variation === "solid-active-item") {
        $classes[] = "effect-{$solid_effect}";

    }

    return $classes;
});

function materialis_get_menu_default_colors($variation)
{

    switch ($variation) {

        case "simple":
        case "simple-text-buttons":
        case "bordered-active-item":
            return array(
                'color' => 'rgba(255, 255, 255, 0.65)',

                'active_highlight_color' => "rgba(255, 255, 255, 1)",
                'active_color'           => "rgba(255, 255, 255, 1)",

                'hover_highlight_color' => "rgba(255, 255, 255, 1)",
                'hover_color'           => "rgba(255, 255, 255, 1)",
                /* fixed */
                'fixed_color'           => '#4a4a4a',

                'fixed_active_highlight_color' => materialis_get_theme_colors("color1"),
                'fixed_active_color'           => materialis_get_theme_colors("color2"),

                'fixed_hover_color'           => materialis_get_theme_colors("color1"),
                'fixed_hover_highlight_color' => materialis_get_theme_colors("color5"),
            );
            break;

        case "highlighted":
        case "material-buttons":
        case "solid-active-item":
            return array(
                'color' => '#ffffff',

                'active_highlight_color' => "rgba(255, 255, 255, 0.8),",
                'active_color'           => materialis_get_theme_colors("color1"),

                'hover_highlight_color' => "rgba(255, 255, 255, 0.8),",
                'hover_color'           => materialis_get_theme_colors("color1"),
                /* fixed */
                'fixed_color'           => '#000000',

                'fixed_active_highlight_color' => materialis_get_theme_colors("color1"),
                'fixed_active_color'           => '#ffffff',

                'fixed_hover_highlight_color' => materialis_get_theme_colors("color1"),
                'fixed_hover_color'           => '#ffffff',
            );
            break;
    }
}

function materialis_get_color_setting_matches()
{
    return array(
        "_nav_menu_color"                        => "color",
        "_nav_menu_hover_color"                  => "hover_color",
        "_nav_menu_hover_highlight_color"        => "hover_highlight_color",
        "_nav_menu_active_color"                 => "active_color",
        "_nav_menu_active_highlight_color"       => "active_highlight_color",
        "_nav_fixed_menu_color"                  => "fixed_color",
        "_nav_fixed_menu_hover_color"            => "fixed_hover_color",
        "_nav_fixed_menu_hover_highlight_color"  => "fixed_hover_highlight_color",
        "_nav_fixed_menu_active_color"           => "fixed_active_color",
        "_nav_fixed_menu_active_highlight_color" => "fixed_active_highlight_color",
        "_nav_submenu_background_color"          => "submenu_bg",
        "_nav_submenu_text_color"                => "submenu_color",
        "_nav_submenu_hover_background_color"    => "submenu_hover_bg",
        "_nav_submenu_hover_text_color"          => "submenu_hover_color",
    );
}

function materialis_get_default_color_for_setting($prefix, $setting)
{
    $variation           = materialis_get_theme_mod("{$prefix}_nav_style", "material-buttons");
    $defaults            = materialis_get_menu_default_colors($variation);
    $controlColorMatches = materialis_get_color_setting_matches();

    return $defaults[$controlColorMatches[$setting]];
}

function materialis_get_menu_colors($variation, $prefix)
{
    $defaults = materialis_get_menu_default_colors($variation);

    return array(
        'color' => materialis_get_theme_mod("{$prefix}_nav_menu_color", $defaults['color']),

        'active_highlight_color' => materialis_get_theme_mod("{$prefix}_nav_menu_active_highlight_color", $defaults['active_highlight_color']),
        'active_color'           => materialis_get_theme_mod("{$prefix}_nav_menu_active_color", $defaults['active_color']),

        'hover_highlight_color' => materialis_get_theme_mod("{$prefix}_nav_menu_hover_highlight_color", $defaults['hover_highlight_color']),
        'hover_color'           => materialis_get_theme_mod("{$prefix}_nav_menu_hover_color", $defaults['hover_color']),

        'fixed_color' => materialis_get_theme_mod("{$prefix}_nav_fixed_menu_color", $defaults['fixed_color']),

        'fixed_active_highlight_color' => materialis_get_theme_mod("{$prefix}_nav_fixed_menu_active_highlight_color", $defaults['fixed_active_highlight_color']),
        'fixed_active_color'           => materialis_get_theme_mod("{$prefix}_nav_fixed_menu_active_color", $defaults['fixed_active_color']),

        'fixed_hover_color'           => materialis_get_theme_mod("{$prefix}_nav_fixed_menu_hover_color", $defaults['fixed_hover_color']),
        'fixed_hover_highlight_color' => materialis_get_theme_mod("{$prefix}_nav_fixed_menu_hover_highlight_color", $defaults['fixed_hover_highlight_color']),

    );
}


add_action('wp_head', function () {

    $prefix = materialis_is_front_page(true) ? "header" : "inner_header";

    $content         = "";
    $parent_selector = materialis_is_front_page(true) ? ".materialis-front-page" : ".materialis-inner-page";
    
    if (materialis_can_show_cached_value("{$prefix}-menu-variant-style")) {
        $content = materialis_get_cached_value("{$prefix}-menu-variant-style");
        $content = "/** cached menu style */{$content}";
    } else {
	    $variation = materialis_get_theme_mod("{$prefix}_nav_style", "material-buttons");


	    $default_active_color = materialis_get_var("dd_color");

	    $transparent_nav = materialis_get_theme_mod($prefix . '_nav_transparent', materialis_mod_default("{$prefix}_nav_transparent"));

	    $default_color = materialis_get_var("dd_color");

	    if ( ! $transparent_nav) {
		$default_color        = materialis_get_var("dd_fixed_color");
		$default_active_color = materialis_get_var("dd_fixed_color");
	    }

	    if ($variation == "material-buttons") {
		$default_active_color = "#03a9f4";
	    }

	    $content = "/* {$prefix} ### {$variation} */ \n\n\n";
	    $content .= file_get_contents(get_template_directory() . "/pro/assets/menu-vars/base.inc.php") . "\n\n";

	    if (file_exists(get_template_directory() . "/pro/assets/menu-vars/{$variation}.inc.php")) {
		$content .= file_get_contents(get_template_directory() . "/pro/assets/menu-vars/{$variation}.inc.php") . "\n\n";
	    }
	    $content .= file_get_contents(get_template_directory() . "/pro/assets/menu-vars/submenus.inc.php") . "\n\n";

	    $parent_selector = materialis_is_front_page(true) ? ".materialis-front-page" : ".materialis-inner-page";

	    $vars = array(

		'submenu_bg'          => materialis_get_theme_mod("header_nav_submenu_background_color", materialis_get_var("dd_submenu_bg")),
		'submenu_color'       => materialis_get_theme_mod("header_nav_submenu_text_color", materialis_get_var("dd_submenu_color")),
		'submenu_hover_bg'    => materialis_get_theme_mod("header_nav_submenu_hover_background_color", materialis_get_theme_colors('color1')),
		'submenu_hover_color' => materialis_get_theme_mod("header_nav_submenu_hover_text_color", '#ffffff'),

		'parent_selector' => $parent_selector,
	    );

	    $vars = array_merge($vars, materialis_get_menu_colors($variation, $prefix));

	    foreach ($vars as $var => $value) {

		$content = str_replace("toRgb(\$dd_{$var})", Kirki_Color::get_rgb($value, true), $content);
		$content = str_replace("\$dd_{$var}", $value, $content);
	    }

	    // clear content
	//    $content = preg_replace("#\/\*.+?\*\/#", "", $content);
	//    $content = preg_replace("#[\n]#", "", $content);
	//    $content = preg_replace("#[\n]#", "", $content);

	    // align menu

	    $nav_type = materialis_get_theme_mod($prefix . '_nav_bar_type', 'default');

	    $default_menu_align = "flex-end";
	    if ($nav_type == 'logo-inside-menu' || $nav_type == 'logo-above-menu') {
		$default_menu_align = "center";
	    }

        
        if ( ! is_admin() && ! materialis_is_customize_preview() && ! WP_DEBUG) {
            
            $content = preg_replace("#\/\*(.*)\*\/#", "", $content);
            $content = str_replace("\n", " ", $content);
            $content = preg_replace("#\s\s?#", " ", $content);
            
        }
        materialis_cache_value("{$prefix}-menu-variant-style", trim($content));
    }

    $menu_align = materialis_get_theme_mod($prefix . '_nav_menu_items_align', $default_menu_align);

    ?>
    <style data-prefix="<?php echo $prefix; ?>" data-name="menu-variant-style">
        <?php echo $content; ?>

    </style>
    <style data-name="menu-align">
        <?php echo "$parent_selector .main-menu, $parent_selector .main_menu_col {justify-content:$menu_align;}"; ?>
    </style>
    <?php

});

function materialis_menu_get_preview_data()
{
    $menu_vars = apply_filters('materialis_navigation_styles', array(
        'simple-text-buttons' => esc_html__('Simple text menu', 'materialis'),
        'material-buttons'    => esc_html__('Material Buttons', 'materialis'),
    ));

//    $menu_vars = array_keys($menu_vars);

    foreach ($menu_vars as $var => $labels) {
        ob_start();
        locate_template("pro/assets/menu-vars/{$var}.inc.php", true, true);
        $menu_vars[$var] = ob_get_clean();
    }


    $data = array(
        'menu_vars' => $menu_vars,
    );

    ob_start();
    locate_template("pro/assets/menu-vars/submenus.inc.php", true, true);
    $data['submenu'] = ob_get_clean();

    ob_start();
    locate_template("pro/assets/menu-vars/base.inc.php", true, true);
    $data['base'] = ob_get_clean();

    ob_start();

    ?>
    var __menu_preview_data = <?php echo json_encode($data); ?>
    <?php

    return ob_get_clean();
}

add_action('customize_preview_init', function () {
    materialis_enqueue_script('customize-menu-preview', array(
        'src'  => get_template_directory_uri() . "/customizer/js/customize-menu-preview.js",
        'deps' => array(materialis_get_text_domain() . "-customize-preview"),
    ));

    $data = materialis_menu_get_preview_data();

    wp_add_inline_script('customize-menu-preview', $data);
});


/*
    template functions
*/


add_filter("materialis_header_main_class", function ($classes, $prefix) {
    $nav_type   = materialis_get_theme_mod($prefix . '_nav_bar_type', 'default');
    $menu_align = materialis_get_theme_mod($prefix . '_nav_menu_items_align', 'flex-end');
    if ($nav_type == "logo-menu-area" && $menu_align == "center") {
        $classes[] = "centered-menu";
    }

    return $classes;
}, 1, 2);

function materialis_print_nav_custom_buttons()
{
    $prefix  = materialis_is_front_page(true) ? "header" : "inner_header";
    $setting = "{$prefix}_navigation_custom_area_buttons";
    $default = array(
        array(
            'label'  => __('Get Started', 'materialis'),
            'url'    => '#',
            'target' => '_self',
            'class'  => 'button',
        ),
    );

    materialis_print_buttons_list($setting, $default);

}

function materialis_print_nav_custom_search()
{
    echo materialis_instantiate_widget('WP_Widget_Search', array(
        'before_widget' => '<div class="widget nav-search %s">',
        'after_widget'  => "</div>",
    ));
}

function materialis_print_navigation_custom_area()
{
    $prefix   = materialis_is_front_page(true) ? "header" : "inner_header";
    $to_print = materialis_get_theme_mod("{$prefix}_navigation_custom_area_type", 'buttons');
    ?>
    <div data-dynamic-mod-container class="navigation-custom-area <?php echo "{$prefix}-nav-area" ?>">
        <?php
        switch ($to_print) {
            case 'buttons':
                materialis_print_nav_custom_buttons();
                break;

            case 'social':
                materialis_print_area_social_icons($prefix, "nav_custom_area", 'social-icons', 5);
                break;

            case 'search':
                materialis_print_nav_custom_search();
                break;
        }

        ?>
    </div>
    <?php
}

// buttons group

add_action('materialis_after_navigation_options_area', function ($inner, $section, $prefix, $priority) {

    $selector_start = $inner ? ".materialis-inner-page" : ".materialis-front-page";

    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'settings'  => "{$prefix}_nav_buttons_spacing",
        'label'     => esc_html__('Buttons spacing', 'materialis'),
        'section'   => $section,
        'default'   => '4',
        'choices'   => array(
            'min'  => '0',
            'max'  => '10',
            'step' => '1',
        ),
        "output"    => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu > li",
                'property'      => 'margin',
                'value_pattern' => '0px $px',
            ),

        ),
        'priority'  => $priority,
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu > li",
                'property'      => 'margin',
                'value_pattern' => '0px $px',
            ),
        ),
    ));

    $menuButtonsGroupSetting = array(
        'type'          => 'sidebar-button-group',
        'settings'      => "{$prefix}_menu_buttons_general_settings_group",
        'label'         => __('Buttons Settings', 'materialis'),
        'section'       => $section,
        'priority'      => $priority,
        "choices"       => apply_filters('materialis_menu_buttons_general_settings_group', array(
            "{$prefix}_nav_buttons_spacing",
        ), $section, $prefix, $priority, $selector_start, $inner),
        "always_active" => true,
    );

    if ($inner) {
        $menuButtonsGroupSetting['active_callback'] = array(
            array(
                "setting"  => "inner_header_nav_use_front_page",
                "operator" => "!=",
                "value"    => true,
            ),
        );
    }

    materialis_add_kirki_field($menuButtonsGroupSetting);

}, 10, 4);


// material buttons
add_filter('materialis_menu_buttons_general_settings_group', function ($choices, $section, $prefix, $priority, $selector_start, $inner) {
    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'settings'        => "{$prefix}_nav_material_roundness",
        'label'           => esc_html__('Roundness', 'materialis'),
        'section'         => $section,
        'default'         => '3',
        'choices'         => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),
        "output"          => array(
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.material-buttons > li > a",
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),


        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.material-buttons > li > a",
                'function' => 'css',
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),
        ),
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "material-buttons",
            ),
        ),
    ));


    $choices[] = "{$prefix}_nav_material_roundness";

    return $choices;
}, 10, 6);


// bordered active items
add_filter('materialis_menu_buttons_general_settings_group', function ($choices, $section, $prefix, $priority, $selector_start, $inner) {
    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_nav_border_style",
        'label'           => esc_html__('Border style', 'materialis'),
        'section'         => $section,
        'default'         => 'bottom',
        'choices'         => apply_filters('materialis_nav_border_styles', array(
            'bottom'         => esc_html__('Bottom', 'materialis'),
            'top'            => esc_html__('Top', 'materialis'),
            'top-and-bottom' => esc_html__('Top and bottom', 'materialis'),
        )),
        'priority'        => $priority,
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'settings'  => "{$prefix}_nav_border_thinkness_distance",
        'label'     => esc_html__('Border thickness', 'materialis'),
        'section'   => $section,
        'default'   => '2',
        'choices'   => array(
            'min'  => '1',
            'max'  => '10',
            'step' => '1',
        ),
        "output"    => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:before",
                'property'      => 'height',
                'value_pattern' => '$px',
            ),
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:after",
                'property'      => 'height',
                'value_pattern' => '$px',
            ),
        ),
        'priority'  => $priority,
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:before",
                'function'      => 'css',
                'property'      => 'height',
                'value_pattern' => '$px',
            ),
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:after",
                'function'      => 'css',
                'property'      => 'height',
                'value_pattern' => '$px',
            ),
        ),

        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'      => 'slider',
        'settings'  => "{$prefix}_nav_border_distance",
        'label'     => esc_html__('Border distance', 'materialis'),
        'section'   => $section,
        'default'   => '50',
        'choices'   => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '5',
        ),
        "output"    => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:before",
                'property'      => 'top',
                'value_pattern' => 'calc( 1em - $em / 100 )',
            ),
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:after",
                'property'      => 'bottom',
                'value_pattern' => 'calc( 1em - $em / 100 )',
            ),
        ),
        'priority'  => $priority,
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:before",
                'function'      => 'css',
                'property'      => 'top',
                'value_pattern' => 'calc( 1em - $em / 100 )',
            ),
            array(
                'element'       => "$selector_start ul.dropdown-menu.bordered-active-item > li:after",
                'function'      => 'css',
                'property'      => 'bottom',
                'value_pattern' => 'calc( 1em - $em / 100 )',
            ),
        ),

        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_nav_border_effect",
        'label'           => esc_html__('Hover effect', 'materialis'),
        'section'         => $section,
        'default'         => 'none',
        'choices'         => apply_filters('materialis_nav_border_effects', array(
            'none'         => esc_html__('None', 'materialis'),
            'borders-out'  => esc_html__('Borders out', 'materialis'),
            'borders-in'   => esc_html__('Borders in', 'materialis'),
            'borders-grow' => esc_html__('Borders grow', 'materialis'),
        )),
        'priority'        => $priority,
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_nav_border_single_grow",
        'label'           => esc_html__('Grow from', 'materialis'),
        'section'         => $section,
        'default'         => 'left',
        'choices'         => array(
            'left'   => esc_html__('Left', 'materialis'),
            'right'  => esc_html__('Right', 'materialis'),
            'center' => esc_html__('Center', 'materialis'),
        ),
        'priority'        => $priority,
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
            array(
                "setting"  => "{$prefix}_nav_border_style",
                "operator" => "in",
                "value"    => array("top", "bottom"),
            ),
            array(
                "setting"  => "{$prefix}_nav_border_effect",
                "operator" => "in",
                "value"    => array("borders-grow"),
            ),
        ),
        'transport'       => 'postMessage',
    ));

    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_nav_border_double_grow",
        'label'           => esc_html__('Grow from', 'materialis'),
        'section'         => $section,
        'default'         => 'left-and-right',
        'choices'         => array(
            'left-and-right' => esc_html__('Left and Right', 'materialis'),
            'center'         => esc_html__('Center', 'materialis'),
        ),
        'priority'        => $priority,
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "bordered-active-item",
            ),
            array(
                "setting"  => "{$prefix}_nav_border_style",
                "operator" => "in",
                "value"    => array("top-and-bottom"),
            ),
            array(
                "setting"  => "{$prefix}_nav_border_effect",
                "operator" => "in",
                "value"    => array("borders-grow"),
            ),
        ),
        'transport'       => 'postMessage',
    ));

    $choices = array_merge($choices, array(
        "{$prefix}_nav_border_style",
        "{$prefix}_nav_border_thinkness_distance",
        "{$prefix}_nav_border_distance",
        "{$prefix}_nav_border_effect",
        "{$prefix}_nav_border_single_grow",
        "{$prefix}_nav_border_double_grow",
    ));

    return $choices;

}, 10, 6);

// solid buttons
add_filter('materialis_menu_buttons_general_settings_group', function ($choices, $section, $prefix, $priority, $selector_start, $inner) {
    materialis_add_kirki_field(array(
        'type'            => 'select',
        'settings'        => "{$prefix}_nav_solid_effect",
        'label'           => esc_html__('Hover effect', 'materialis'),
        'section'         => $section,
        'default'         => 'none',
        'choices'         => apply_filters('materialis_nav_border_effects', array(
            'none'                    => esc_html__('None', 'materialis'),
            'pull-down'               => esc_html__('Pull down', 'materialis'),
            'pull-up'                 => esc_html__('Pull up', 'materialis'),
            'pull-up-down'            => esc_html__('Pull up and down from edge', 'materialis'),
            'pull-up-down-reverse'    => esc_html__('Pull up and down from center', 'materialis'),
            'pull-left-right'         => esc_html__('Pull left and right from edge', 'materialis'),
            'pull-left-right-reverse' => esc_html__('Pull left and right from center', 'materialis'),
        )),
        'priority'        => $priority,
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "solid-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'settings'        => "{$prefix}_nav_solid_roundness",
        'label'           => esc_html__('Roundness', 'materialis'),
        'section'         => $section,
        'default'         => '3',
        'choices'         => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),
        "output"          => array(
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.solid-active-item > li:before",
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.solid-active-item > li:after",
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),


        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.solid-active-item > li:before",
                'function' => 'css',
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),
            array(
                'element'  => "{$selector_start}  ul.dropdown-menu.solid-active-item > li:after",
                'function' => 'css',
                'property' => 'border-radius',
                'suffix'   => 'px ',
            ),
        ),
        'active_callback' => array(
            array(
                "setting"  => "{$prefix}_nav_style",
                "operator" => "=",
                "value"    => "solid-active-item",
            ),
        ),
        'transport'       => 'postMessage',
    ));


    $choices = array_merge($choices, array(
        "{$prefix}_nav_solid_effect",
        "{$prefix}_nav_solid_roundness",
    ));


    return $choices;
}, 10, 6);


add_filter('cloudpress\customizer\preview_data', function ($data) {

    $data['menu_colors_variation'] = array(
        'simple'      => materialis_get_menu_default_colors('simple'),
        'highlighted' => materialis_get_menu_default_colors('highlighted'),
    );

    return $data;
});
