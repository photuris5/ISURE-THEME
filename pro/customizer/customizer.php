<?php


add_filter('materialis_show_info_pro_messages', "__return_false");
add_filter('materialis_enable_kirki_selective_refresh', "__return_false");

require_once MATERIALIS_PRO_CUSTOMIZER_DIR . "/webgradients-list.php";


add_action('cloudpress\companion\ready', function ($companion) {
    $customizer = $companion->customizer();
    if ($customizer) {
    $customizer->registerScripts('materialis_pro_customizer_scripts', 80);
    $customizer->previewInit('materialis_pro_customizer_preview_scripts');
    }

});

add_action('customize_register', 'materialis_pro_load_customizer_controls');

function materialis_pro_customizer_scripts()
{

    $ver = materialis_get_pro_version();
    $textDomain = materialis_get_text_domain();

    wp_enqueue_style('ope-pro-customizer', MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/css/customizer.css", $ver);

    if (apply_filters('materialis_load_bundled_version', true)) {
        wp_enqueue_script('customizer-pro', MATERIALIS_PRO_CUSTOMIZER_URI . "/pro-customizer.bundle.min.js", array('customizer-base', $textDomain . '-customize'), $ver, true);
//        pro-customizer.bundle.js
    } else {
    wp_enqueue_script('customizer-ope',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-custom-style-manager',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-custom-style-manager.js", array('customizer-base'), $ver, true);


    wp_enqueue_script('customizer-scss-settings-vars',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-scss-settings-vars.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-sectionsetting-control',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/sectionsetting-control.js", array('customizer-base'), $ver, true);


    wp_enqueue_script('customizer-section-separator',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/sectionseparator-control.js", array('customizer-base'), $ver, true);


    wp_enqueue_script('customizer-pro-section-panel',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-pro-section-panel.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-site-colors',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-site-colors.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-button-style',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-button-style.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-icon-style',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-icon-style.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-galleries-settings',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-galleries-settings.js", array('customizer-base'), $ver, true);

    wp_enqueue_script('customizer-shortcodes-pro', MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-shortcodes.js",
        array('customizer-base'), $ver, true);


    wp_enqueue_script('customizer-section-separator-settings',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-section-separators.js", array('customizer-base'), $ver, true);


    wp_enqueue_script('customizer-custom-sections-settings',
        MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/customizer-custom-sections-settings.js", array('customizer-base'), $ver, true);
   }
}

function materialis_pro_customizer_preview_scripts()
{
    wp_enqueue_script('materialis-pro-customize-preview', MATERIALIS_PRO_CUSTOMIZER_URI . "/assets/js/preview.js",
        array('materialis-customize-preview'), false, true);
}


function materialis_pro_load_customizer_controls($wp_customize)
{


//    require_once MATERIALIS_PRO_CUSTOMIZER_DIR . "/controls/SectionSettingControl.php";
    require_once MATERIALIS_PRO_CUSTOMIZER_DIR . "/controls/GradientControl.php";
    require_once MATERIALIS_PRO_CUSTOMIZER_DIR . "/controls/WebFontsControl.php";

//    $wp_customize->register_control_type("Materialis\\SectionSettingControl");
    $wp_customize->register_control_type("Materialis\\WebFontsControl");
    $wp_customize->register_control_type('Materialis\\GradientControlPro');

    add_filter('kirki/control_types', function ($controls) {
//        $controls['sectionsetting']       = "Materialis\\SectionSettingControl";
        $controls['web-fonts']            = "Materialis\\WebFontsControl";
        $controls['gradient-control-pro'] = "Materialis\\GradientControlPro";

        return $controls;
    });
}


add_filter('cloudpress\customizer\global_data', 'materialis_pro_customizer_data');
function materialis_pro_customizer_data($data)
{


    $data['gradients']          = materialis_get_gradients_classes();
    $data['sectionsOverlays']   = materialis_get_theme_mod('pro_background_overlay', array());
    $data['section_separators'] = materialis_get_separators_list(true);
    $data['proStylesheet']      = materialis_pro_uri();

    return $data;
}
