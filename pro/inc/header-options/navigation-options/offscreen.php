<?php

add_action("materialis_customize_register_options", function () {
    materialis_offscreen_menu_settings_pro();
});

function materialis_offscreen_menu_social_icons($section, $prefix, $priority)
{

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Offscreen Menu Social Icons Settings', 'materialis'),
        'settings' => "{$prefix}_offscreen_nav_social_separator",
        'section'  => $section,
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => "{$prefix}_show_social",
        'label'    => __('Offscreen nav bar social icons', 'materialis'),
        'section'  => $section,
        'default'  => true,
        'priority' => $priority,

    ));

    $group_choices = array();

    $default_icons = array(
        array(
            "icon" => "mdi-facebook-box",
            "link" => "https://facebook.com",
        ),
        array(
            "icon" => "mdi-twitter-box",
            "link" => "https://twitter.com",
        ),
        array(
            "icon" => "mdi-instagram",
            "link" => "https://instagram.com",
        ),
        array(
            "icon" => "mdi-google-plus-box",
            "link" => "https://plus.google.com/",
        ),
    );

    $icon_setting_prefix = "{$prefix}_offscreen_nav";

    for ($i = 0; $i < 4; $i++) {
        materialis_add_kirki_field(array(
            'type'     => 'checkbox',
            'label'    => sprintf(__('Show Icon %d', 'materialis'), ($i + 1)),
            'section'  => $section,
            'priority' => $priority,
            'settings' => "{$icon_setting_prefix}_social_icon_{$i}_enabled",
            'default'  => true,
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
            'label'     => sprintf(__('Field %d link', 'materialis'), ($i + 1)),
            'transport' => 'postMessage',
            'section'   => $section,
            'priority'  => $priority,
            'default'   => $default_icons[$i]['link'],
        ));

        $group_choices[] = "{$icon_setting_prefix}_social_icon_{$i}_link";
    }

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_offscreen_nav_icons_group",
        'label'           => esc_html__('Offscreen Nav Social Icons', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => $group_choices,
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_show_social",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));
}

function materialis_offscreen_menu_settings_pro()
{
    $prefix   = "header_offscreen_nav";
    $section  = "navigation_offscreen";
    $priority = 1;

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Offscreen Menu Button Settings', 'materialis'),
        'settings' => "{$prefix}_nav_bar_offscreen_menu_button_settings_separator",
        'section'  => $section,
        'priority' => $priority,
    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_hamburger_color",
        'label'    => __('Button Normal Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => materialis_get_var("dd_color"),
        'output'  => array(
            array(
                'element'  => "[data-component=\"offcanvas\"] i.mdi",
                'property' => 'color',
                'suffix'   => '!important',
            ),
            array(
                'element'  => "[data-component=\"offcanvas\"] .bubble",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "[data-component=\"offcanvas\"] i.mdi",
                'property' => 'color',
                'suffix'   => '!important',
            ),
            array(
                'element'  => "[data-component=\"offcanvas\"] .bubble",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_hamburger_color_sticky",
        'label'    => __('Button Sticky Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#000000",
        'output'  => array(
            array(
                'element'  => ".fixto-fixed [data-component=\"offcanvas\"] i.mdi",
                'property' => 'color',
                'suffix'   => '!important',
            ),
            array(
                'element'  => ".fixto-fixed [data-component=\"offcanvas\"] .bubble",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => ".fixto-fixed [data-component=\"offcanvas\"] i.mdi",
                'property' => 'color',
                'suffix'   => '!important',
            ),
            array(
                'element'  => ".fixto-fixed [data-component=\"offcanvas\"] .bubble",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),


    ));


    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Offscreen Nav Bar Settings', 'materialis'),
        'settings' => "{$prefix}_nav_bar_settings_separator",
        'section'  => $section,
        'priority' => $priority,
    ));
    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_color",
        'label'    => __('Nav Bar Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#222B34",
        'output'  => array(
            array(
                'element'  => "#offcanvas-wrapper",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "#offcanvas-wrapper",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),

        ),


    ));


    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_overlay_color",
        'label'    => __('Nav Bar Overlay Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "rgba(34, 43, 52, 0.7)",
        'output'  => array(
            array(
                'element'  => "html.has-offscreen body:after",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "html.has-offscreen body:after",
                'property' => 'background-color',
                'suffix'   => '!important',
            ),
        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_text_color",
        'label'    => __('Nav Texts Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => false,
        ),

        'default' => "#ffffff",
        'output'  => array(
            array(
                'element'  => "#offcanvas-wrapper *:not(.arrow)",
                'property' => 'color',
                'suffix'   => '!important',
            ),

        ),
        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "#offcanvas-wrapper *:not(.arrow)",
                'property' => 'color',
                'function' => 'css',
                'suffix'   => '!important',
            ),
        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_highlight_color",
        'label'    => __('Highlight Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => false,
        ),

        'default' => "#FFFFFF",
        'output'  => array(
            array(
                'element'  => "#offcanvas_menu li.open, #offcanvas_menu li.current-menu-item, #offcanvas_menu li.current_page_item",
                'property' => 'background-color',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "#offcanvas_menu li.open, #offcanvas_menu li.current-menu-item, #offcanvas_menu li.current_page_item",
                'property' => 'background-color',
                'function' => 'style',
            ),

        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_highlight_text_color",
        'label'    => __('Highlight Text Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => false,
        ),

        'default' => "#2395F6",
        'output'  => array(
            array(
                'element'  => "#offcanvas_menu li.open > a, #offcanvas_menu li.open > a > i ,#offcanvas_menu li.current-menu-item > a, #offcanvas_menu li.current_page_item > a",
                'property' => 'color',
                'suffix'   => '!important',
            ),

            array(
                'element'  => "#offcanvas_menu li.open > a,#offcanvas_menu li.open > a > i , #offcanvas_menu li.current-menu-item > a, #offcanvas_menu li.current_page_item > a",
                'property' => 'border-left-color',
                'suffix'   => '!important',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "#offcanvas_menu li.open > a,#offcanvas_menu li.open > a > i , #offcanvas_menu li.current-menu-item > a, #offcanvas_menu li.current_page_item > a",
                'property' => 'color',
                'suffix'   => '!important',
                'function' => 'style',
            ),

            array(
                'element'  => "#offcanvas_menu li.open > a,#offcanvas_menu li.open > a > i , #offcanvas_menu li.current-menu-item > a, #offcanvas_menu li.current_page_item > a",
                'property' => 'border-left-color',
                'suffix'   => '!important',
            ),
        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'color',
        'settings' => "{$prefix}_nav_bar_offscreen_submenu_color",
        'label'    => __('Submenu Background Color', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'choices'  => array(
            'alpha' => true,
        ),

        'default' => "#686B77",
        'output'  => array(
            array(
                'element'  => "#offcanvas_menu li > ul",
                'property' => 'background-color',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element'  => "#offcanvas_menu li > ul",
                'property' => 'background-color',
                'function' => 'style',
            ),

        ),


    ));

    materialis_add_kirki_field(array(
        'type'     => 'sidebar-button-group',
        'settings' => "{$prefix}_item_offscreen_typography_group",
        'label'    => __('Offscreen Menu Typography', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        "choices"  => array(
            "{$prefix}_item_offscreen_typography",
        ),
    ));

    materialis_add_kirki_field(array(
        'type'     => 'typography',
        'settings' => "{$prefix}_item_offscreen_typography",
        'label'    => __('Offscreen Menu Typography', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'default'  => array(
            'font-family'      => 'Roboto',
            'font-size'        => '0.875rem',
            'font-weight'      => '400',
            'line-height'      => '100%',
            'letter-spacing'   => '0px',
            'subsets'          => array(),
            'text-transform'   => 'none',
            'mobile-font-size' => '',
            'addwebfont'       => true,
        ),
        'output'   => array(
            array(
                'element' => '#offcanvas_menu li > a',
            ),

        ),

        'transport' => 'postMessage',
        'js_vars'   => array(
            array(
                'element' => '#offcanvas_menu li > a',
            ),
        ),
    ));
    materialis_offscreen_menu_social_icons($section, $prefix, $priority + 1);
}
