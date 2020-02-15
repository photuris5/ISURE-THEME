<?php

add_action('wp_head', 'materialis_print_woocommerce_theme_colors_style', PHP_INT_MAX);

materialis_add_kirki_field(array(
    'type'     => 'color',
    'settings' => "woocommerce_primary_color",
    'label'    => __('Primary Color', 'memserize'),
    'section'  => 'materialis_woocommerce_colors',
    'default'  => materialis_get_theme_colors('color1'),
));

materialis_add_kirki_field(array(
    'type'     => 'color',
    'settings' => "woocommerce_secondary_color",
    'label'    => __('Secondary Color', 'memserize'),
    'section'  => 'materialis_woocommerce_colors',
    'default'  => materialis_get_theme_colors('color2'),
));

materialis_add_kirki_field(array(
    'type'     => 'color',
    'settings' => "woocommerce_onsale_color",
    'label'    => __('"Sale" Badge Color', 'memserize'),
    'section'  => 'materialis_woocommerce_colors',
    'default'  => materialis_woocommerce_get_onsale_badge_default_color(),
));

materialis_add_kirki_field(array(
    'type'     => 'color',
    'settings' => "woocommerce_rating_stars_color",
    'label'    => __('Rating Stars Color', 'memserize'),
    'section'  => 'materialis_woocommerce_colors',
    'default'  => materialis_woocommerce_get_onsale_badge_default_color(),
));

function materialis_print_woocommerce_theme_colors_style()
{
    ?>
    <style data-name="woocommerce-colors">
        <?php

    // show on front page too for woocommerce sections //
    if (class_exists('WooCommerce')) {

        $vars = materialis_woocommerce_get_colors();

        foreach ($vars as $name => $var) {
            $$name = $var;
        }

        include materialis_pro_dir("/inc/woocommerce/print-colors.php");

    }
    ?>
    </style>
    <?php
}

function materialis_woocommerce_get_colors()
{
    $vars = array();

    $vars['color1']       = get_theme_mod('woocommerce_primary_color', materialis_get_theme_colors('color1'));
    $vars['color1_light'] = Kirki_Color::adjust_brightness($vars['color1'], 10);

    $vars['color2']       = get_theme_mod('woocommerce_secondary_color', materialis_get_theme_colors('color2'));
    $vars['color2_light'] = Kirki_Color::adjust_brightness($vars['color2'], 10);

    $vars['onsale_color']       = get_theme_mod('woocommerce_onsale_color', materialis_woocommerce_get_onsale_badge_default_color());
    $vars['rating_stars_color'] = get_theme_mod('woocommerce_rating_stars_color', materialis_woocommerce_get_onsale_badge_default_color());

    return apply_filters('materialis_woocommerce_get_colors', $vars);
}
