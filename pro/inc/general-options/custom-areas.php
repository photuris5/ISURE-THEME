<?php

add_action('materialis_customize_register', function ($wp_customize) {
    $wp_customize->add_section('user_custom_widgets_areas', array(
        'title'      => __('Manage Custom Widgets Areas', 'materialis'),
        'priority'   => 100,
        'panel'      => 'general_settings',
        'capability' => 'edit_theme_options',
    ));
});

materialis_add_kirki_field(array(
    'type'      => 'repeater',
    'settings'  => 'materialis_users_custom_widgets_areas',
    'label'     => esc_html__('Custom Widget Areas', 'materialis'),
    'section'   => "user_custom_widgets_areas",
    "priority"  => 0,
    "default"   => array(),
    'row_label' => array(
        'type'  => 'field',
        'field' => 'name',
        'value' => 'Widgets Area',
    ),
    "fields"    => array(
        "name" => array(
            'type'    => 'text',
            'label'   => esc_attr__('Widgets Area name', 'materialis'),
            'default' => 'Widgets Area',
        ),

    ),
));


add_filter('cloudpress\customizer\preview_data', 'materialis_add_users_areas_in_preview');

function materialis_add_users_areas_in_preview($value)
{

    $widgets_areas_mod = materialis_get_theme_mod('materialis_users_custom_widgets_areas', array());
    $widgets_areas     = array();

    foreach ($widgets_areas_mod as $index => $data) {
        $id                 = "materialis_users_custom_widgets_areas_{$index}";
        $widgets_areas[$id] = $data['name'];
    }

    $value['widgets_areas'] = $widgets_areas;

    return $value;
}

function materialis_init_custom_widgets_init()
{
    $widgets_area = materialis_get_theme_mod('materialis_users_custom_widgets_areas', array());

    foreach ($widgets_area as $id => $data) {
        register_sidebar(array(
            'name'          => $data['name'],
            'id'            => "materialis_users_custom_widgets_areas_{$id}",
            'title'         => "Widget Area",
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4>',
            'after_title'   => '</h4>',
        ));
    }

}

add_action('widgets_init', 'materialis_init_custom_widgets_init');
