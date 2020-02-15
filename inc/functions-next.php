<?php


//More colors in pro

materialis_add_kirki_field(array(
    'type'     => 'ope-info-pro',
    'label'    => __('Customize all theme colors in PRO. @BTN@', 'materialis'),
    'section'  => 'colors',
    'settings' => "customize_colors_buttons_pro",
));


function materialis_registe_show_inactive_plugin_infos($wp_customize)
{
    if (apply_filters('materialis_show_inactive_plugin_infos', true)) {
        $wp_customize->add_setting('frontpage_header_presets_pro', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control(new Materialis\Info_Control($wp_customize, 'frontpage_header_presets_pro',
            array(
                'label'     => __('10 more beautiful header designs are available in the PRO version. @BTN@', 'materialis'),
                'section'   => 'header_layout',
                'priority'  => 2,
                'transport' => 'postMessage',
            )
        ));
    }
}

add_action("materialis_customize_register", 'materialis_registe_show_inactive_plugin_infos');

function materialis_after_setup_theme_register_navmenus()
{
    add_action('admin_menu', 'materialis_register_theme_page');


    include_once get_template_directory() . "/inc/Materialis_Logo_Nav_Menu.php";
    include_once get_template_directory() . "/inc/Materialis_Logo_Page_Menu.php";
}

add_action('after_setup_theme', 'materialis_after_setup_theme_register_navmenus');
