<?php


add_filter("materialis_header_top_bar_content_print", function($areaName, $type) {
    if ($type == 'menu') {
       materialis_print_header_top_bar_menu($areaName);
    }
}, 1, 2);

add_filter("materialis_get_content_types", function($types) {
    $types['menu'] = __("Menu", 'materialis');
    return $types;
});

add_filter("materialis_get_content_types_options", function($options) {
    $options['menu'] = "materialis_top_bar_menu_options";
    return $options;
});



function materialis_top_bar_menu_options($area, $section, $priority, $prefix)
{

    materialis_add_kirki_field(array(
        'type'     => 'sectionseparator',
        'label'    => __('Menu Colors', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'settings' => "{$prefix}_menu_colors_separator",
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Link Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_menu_options_link_color",
        'default'   => "#fff",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Link Hover Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_menu_options_link_hover_color",
        'default'   => "#fff",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a:hover",
                'property' => 'color',
                'suffix'   => '!important',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a:hover",
                'property' => 'color',
                'function' => 'css',
                'suffix'   => '!important',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'    => 'color',
        'label'   => __('Link Visited Color', 'materialis'),
        'section' => $section,
        'settings'  => "{$prefix}_menu_options_link_visited_color",
        'default'   => "#fff",
        'priority'  => $priority,
        'choices'   => array(
            'alpha' => false,
        ),
        'transport' => 'postMessage',
        "output" => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a:visited",
                'property' => 'color',
            ),
        ),
        'js_vars' => array(
            array(
                'element'  => ".header-top-bar .header-top-bar-area.{$area} .top-bar-menu > li > a:visited",
                'property' => 'color',
                'function' => 'css',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'sidebar-button-group',
        'settings'        => "{$prefix}_menu_group_button",
        'label'           => esc_html__('Menu Options', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "choices"         => array(
            "{$prefix}_menu_colors_separator",
            "{$prefix}_menu_options_link_color",
            "{$prefix}_menu_options_link_hover_color",
            "{$prefix}_menu_options_link_visited_color",
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_content",
                'operator' => '==',
                'value'    => 'menu',
            ),
            array(
                'setting'  => "enable_top_bar",
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));

}


/*
    template functions
*/

function materialis_no_top_bar_menu_cb($args)
{
    global $_wp_registered_nav_menus;
    $menu = $_wp_registered_nav_menus[$args['theme_location']];

    echo materialis_placeholder_p(
        sprintf(__('No menu set at for "%1$s" location ', 'materialis'), $menu)
    );
}

function materialis_print_header_top_bar_menu($area)
{
    wp_nav_menu(
        array(
            'theme_location' => "top_bar_{$area}",
            'menu_id'        => "top-bar-{$area}-menu",
            'menu_class'     => 'top-bar-menu',
            'container_id'   => 'top-bar-menu-container',
            'fallback_cb'    => 'materialis_no_top_bar_menu_cb',
            'depth'          => 1,
        )
    );
}
