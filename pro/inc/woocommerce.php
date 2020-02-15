<?php

//ADD SECTIONS

add_action('materialis_customize_register_woocommerce_section', 'materialis_pro_customize_register_woocommerce_section', 10, 3);

function materialis_pro_customize_register_woocommerce_section($wp_customize, $panel, $priority)
{
    $wp_customize->add_section('materialis_woocommerce_colors', array(
        'title'    => __('Colors', 'memserize'),
        'priority' => $priority - 5,
        'panel'    => $panel,
    ));
}

require_once materialis_pro_dir("/inc/woocommerce/colors.php");

// ADD CONTROLS

add_action('materialis_customizer_prepend_woocommerce_list_options', 'materialis_pro_customizer_prepend_woocommerce_list_options', 10, 1);

function materialis_pro_customizer_prepend_woocommerce_list_options($section)
{
    materialis_add_kirki_field(array(
        'type'     => 'sortable',
        'settings' => 'woocommerce_card_item_get_print_order',
        'label'    => __('Product Fields Order', 'materialis'),
        'section'  => $section,
        'priority' => 11,
        'default'  => array('title', 'rating', 'price', 'categories'),
        'choices'  => apply_filters('materialis_woocommerce_list_product_options',
            array(
                'title'       => __('Product Name', 'materialis'),
                'rating'      => __('Rating Stars', 'materialis'),
                'price'       => __('Price', 'materialis'),
                'categories'  => __('Product Categories', 'materialis'),
                'description' => __('Product Description (excerpt) ', 'materialis'),
            )
        ),
    ));
}

// FUNTIONS

function materialis_woocommerce_get_onsale_badge_default_color()
{
    $primary_color = get_theme_mod('woocommerce_primary_color', false);
    if ( ! $primary_color) {
        $primary_color = materialis_get_theme_colors('color1');
    }

    return Kirki_Color::adjust_brightness($primary_color, 10);
}
