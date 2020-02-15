<?php

materialis_pro_require("/inc/general-options/colors.php");
materialis_pro_require("/inc/general-options/typography.php");
materialis_pro_require("/inc/general-options/effects.php");
materialis_pro_require("/inc/general-options/custom-areas.php");

add_filter('cloudpress\companion\cp_data',
    function ($data, $companion) {
        /** @var Materialis\Companion $companion */
        $sectionsJSON = materialis_pro_dir("/sections/sections.php");

        $contentSections = $companion->loadConfig($sectionsJSON);
        $contentSections = Materialis\Companion::filterDefault($contentSections);

        if (isset($data['data']['sections']) && is_array($data['data']['sections'])) {
            $data['data']['sections'] = array_merge($data['data']['sections'], $contentSections);
        }

        return $data;
    }, 20, 2);

add_action('cloudpress\template\load_assets',
    function ($companion) {
        $ver = $companion->version;
        wp_enqueue_style('companion-pro-page-css', materialis_pro_uri('/sections/content.css'), array('companion-page-css'), $ver);

    }
);

add_filter('cloudpress\customizer\control\content_sections\multiple', 'materialis_pro_content_add_insertion_type');

function materialis_pro_content_add_insertion_type($data)
{
    return 'multiple';
}

add_filter("materialis_get_footer_copyright", function ($copyright, $previewAtts) {
    $preview_atts = "";
    if (materialis_is_customize_preview()) {
        $preview_atts = "data-focus-control='footer_content_copyright_text'";
    }

    $defaultText = __('&copy; {year} {blogname}. Built using WordPress and <a href="#">Materialis Theme</a>.', 'materialis');

    $copyrightText = materialis_get_theme_mod('footer_content_copyright_text', $defaultText);

    $copyrightText = str_replace("{year}", date_i18n(__('Y', 'materialis')), $copyrightText);
    $copyrightText = str_replace("{blogname}", esc_html(get_bloginfo('name')), $copyrightText);

    $allowed_html = array(
        'a'      => array(
            'href'  => array(),
            'title' => array(),
        ),
        'em'     => array(),
        'strong' => array(),
    );

    return '<p ' . $previewAtts . ' class="copyright" data-type="group" ' . $preview_atts . '>' . wp_kses_post($copyrightText) . '</p>';
}, 10, 2);

add_filter('cloudpress\customizer\global_data', function ($data) {

    $data['footerData'] = isset($data['footerData']) ? $data['footerData'] : array();

    $data['footerData']['year']     = date_i18n(__('Y', 'materialis'));
    $data['footerData']['blogname'] = esc_html(get_bloginfo('name'));

    return $data;
});


add_filter('materialis_override_with_thumbnail_image', function ($value) {

    if (materialis_is_woocommerce_page()) {
        $value = materialis_get_theme_mod('woocommerce_product_header_image', true);
        $value = (intval($value) === 1);
    }

    return $value;
});
