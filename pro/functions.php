<?php


function materialis_free_options_exists($stylesheet)
{
    $default = '___MATERIALIS_FREE_MODS_NOT_AVAILABLE___';
    $options = get_option('theme_mods_' . $stylesheet, $default);

    return ($options !== $default);
}

if (is_admin() && ! is_customize_preview()) {

    require_once 'inc/class-wp-license-manager-client.php';
    $licence_manager = new Wp_License_Manager_Client(
        'materialis-pro',
        'Materialis PRO',
        'materialis',
        'http://onepageexpress.com/api/license-manager/v1/',
        'theme'
    );
}


add_action('after_switch_theme', 'materialis_pro_first_activation');
add_action('materialis_show_main_info_pro_messages', '__return_false'); // section info pro button

function materialis_pro_first_activation()
{
    $freeStylesheet     = 'materialis';
    $firstActivationKey = 'materialis_pro_first_activation_processed';

    if (get_stylesheet() === $freeStylesheet || ! materialis_free_options_exists($freeStylesheet)) {
        return;
    }

    $was_processed = get_option($firstActivationKey, false);
    if ($was_processed === false) {
        $freeOptions = get_option('theme_mods_' . $freeStylesheet, array());

        update_option('theme_mods_' . get_stylesheet(), $freeOptions);
        update_option($firstActivationKey, true);
    }

}

add_filter('cloudpress\customizer\supports', "__return_true");
add_filter('materialis_show_info_pro_messages', '__return_false');
add_filter('kirki_skip_fonts_enqueue', '__return_true');
add_filter('cloudpresss\companion\can_edit_in_customizer', '__return_true');
add_filter('header_content_buttons_limit', "__return_false");


function materialis_pro_require($path)
{
    $path = trim($path, "\\/");
    require_once get_template_directory() . "/pro/{$path}";
}

function materialis_pro_dir($path = "")
{
    return get_template_directory() . "/" . materialis_pro_relative_dir($path);
}

function materialis_pro_relative_dir($path)
{
    $path = trim($path, "\\/");

    return "pro/{$path}";
}

function materialis_pro_uri($path = "")
{
    $path = trim($path, "\\/");

    if (strlen($path)) {
        $path = "/" . $path;
    }

    return get_template_directory_uri() . "/pro{$path}";
}


function materialis_no_footer_menu_cb()
{
    return wp_page_menu(array(
        "menu_class" => 'fm2_horizontal_footer_menu',
        "menu_id"    => 'horizontal_main_footer_container',
        'before'     => '<ul id="horizontal_footer_menu" class="fm2_horizontal_footer_menu">',
    ));
}

function materialis_placeholder_p($text, $echo = false)
{
    $content = "";

    if (materialis_is_customize_preview()) {
        $content = '<p class="content-placeholder-p">' . $text . '</p>';
    }

    if ($echo) {
        echo $content;
    } else {
        return $content;
    }
}

function materialis_get_pro_version()
{
    $theme = wp_get_theme();
    $ver   = $theme->get('Version');
    $ver   = apply_filters('materialis_get_pro_version', $ver);

    return $ver;
}


if ( ! defined('MATERIALIS_PRO_CUSTOMIZER_DIR')) {
    define('MATERIALIS_PRO_CUSTOMIZER_DIR', materialis_pro_dir("/customizer"));
}


if ( ! defined('MATERIALIS_PRO_CUSTOMIZER_URI')) {
    define('MATERIALIS_PRO_CUSTOMIZER_URI', materialis_pro_uri("/customizer"));
}


function materialis_print_contextual_jQuery()
{
    $isShortcodeRefresh = apply_filters('materialis_is_shortcode_refresh', false);
    echo $isShortcodeRefresh ? "top.CP_Customizer.preview.jQuery()" : "jQuery";
}

// multilanguage not ready yet
materialis_pro_require("/inc/multilanguage.php");

materialis_pro_require("/inc/header-options.php");
materialis_pro_require("/inc/footer-options.php");
materialis_pro_require("/inc/general-options.php");

materialis_pro_require("/customizer/customizer.php");
materialis_pro_require("/inc/shortcodes.php");
materialis_pro_require("/inc/templates-functions.php");
materialis_pro_require("/inc/integrations/index.php");

if (class_exists('WooCommerce')) {
    materialis_pro_require("/inc/woocommerce.php");
}

add_action('wp_enqueue_scripts', function () {

    $localized_handle = "theme-pro";
    if (apply_filters('materialis_load_bundled_version', true)) {
        $textDomain       = materialis_get_text_domain();
        $localized_handle = "{$textDomain}-theme";
        wp_dequeue_script("{$textDomain}-theme");
        wp_deregister_script("{$textDomain}-theme");
        
        materialis_enqueue_script("{$textDomain}-theme", array(
            'src'  => materialis_pro_uri('/assets/js/theme.bundle.min.js'),
            'deps' => array('jquery', 'jquery-effects-core', 'jquery-effects-slide', 'masonry'),
        ));
        
    } else {
        
	    materialis_enqueue_script('jquery-fancybox', array(
		'src'  => materialis_pro_uri('assets/js/jquery.fancybox.min.js'),
		'deps' => array('jquery'),
		'ver'  => '3.0.47',
	    ));
        

	    materialis_enqueue_script('theme-pro', array(
		'src'  => materialis_pro_uri('assets/js/theme.js'),
		'deps' => array('jquery'),
	    ));
        
    }

    $materialis_theme_pro_settings = apply_filters('materialis_theme_pro_settings', array());
    wp_localize_script($localized_handle, 'materialis_theme_pro_settings', $materialis_theme_pro_settings);

}, 50);


add_action('wp_enqueue_scripts', function () {
    
    if (apply_filters('materialis_load_bundled_version', true)) {
        
        $textDomain = materialis_get_text_domain();
        wp_dequeue_style($textDomain . '-style-bundle');
        wp_deregister_style($textDomain . '-style-bundle');
        
        materialis_enqueue_style($textDomain . '-style-bundle', array(
            'src' => materialis_pro_uri('/assets/css/theme.bundle.min.css'),
        ));
        
    } else {
        materialis_enqueue_style('jquery-fancybox', array(
            'src'  => materialis_pro_uri('assets/css/jquery.fancybox.min.css'),
            'deps' => array(),
            'ver'  => '3.0.47',
        ));
    }
}, 50);

function materialis_widgets_init_pro()
{
    register_sidebar(array(
        'name'          => __("Footer Newsletter Subscriber", 'materialis'),
        'id'            => "newsletter_subscriber_widgets",
        'title'         => "Widget Area",
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widgettitle">',
        'after_title'   => '</h5>',
    ));
}

add_action('widgets_init', 'materialis_widgets_init_pro');
function materialis_setup_pro()
{
    register_nav_menus(array(
        'footer_menu'        => __('Footer Menu', 'materialis'),
        'top_bar_area-left'  => __('Top Bar Left Menu', 'materialis'),
        'top_bar_area-right' => __('Top Bar Right Menu', 'materialis'),
    ));
}


add_filter('cloudpress\customizer\global_data', function ($data) {
    $data['PRO_URL'] = materialis_pro_uri();

    return $data;
});


function materialis_footer_no_menu_cb()
{
    return wp_page_menu(array(
        'menu_id'    => 'horizontal_main_footer_container',
        'menu_class' => 'horizontal_footer_menu',
        'depth'      => 1,
    ));
}

function materialis_footer_menu()
{
    wp_nav_menu(array(
        'theme_location'  => 'footer_menu',
        'menu_id'         => 'footer_menu',
        'menu_class'      => 'footer-nav',
        'container_class' => 'horizontal_footer_menu',
        'fallback_cb'     => 'materialis_footer_no_menu_cb',
        'depth'           => 1,
    ));
}


function materialis_no_menu_logo_inside_cb()
{
    materialis_nomenu_fallback(new Materialis_Logo_Page_Menu());
}


add_action('after_setup_theme', 'materialis_setup_pro');

function materialis_tgma_pro_suggest_plugins($plugins)
{
    $plugins[] = array(
        'name'     => 'MailChimp for WordPress',
        'slug'     => 'mailchimp-for-wp',
        'required' => false,
    );

    return $plugins;
}

function materialis_theme_pro_info_plugins($plugins)
{
    $plugins = array_merge($plugins,
        array(
            'mailchimp-for-wp' => array(
                'title'       => __('MailChimp for WordPress', 'materialis'),
                'description' => __('The MailChimp for WordPress plugin is recommended for the One Page Express subscribe sections.', 'materialis'),
                'activate'    => array(
                    'label' => __('Activate', 'materialis'),
                ),
                'install'     => array(
                    'label' => __('Install', 'materialis'),
                ),
            ),
        )
    );

    return $plugins;
}

add_filter('materialis_tgmpa_plugins', 'materialis_tgma_pro_suggest_plugins');
add_filter('materialis_theme_info_plugins', 'materialis_theme_pro_info_plugins');


// MODS EXPORTER
//add_action('customize_controls_print_footer_scripts', function () {
//    global $wp_customize;
//    $data = array();
//    $sets = $wp_customize->settings();
//
//    foreach ($sets as $id => $setting) {
//
//        $contains    = array("header", "top_bar", "layout_boxed_content_enabled");
//        $notContains = array("inner_header", "widgets_", "sidebar_widgets", "CP_AUTOSETTING");
//
//        $containsOK = false;
//
//        foreach ($contains as $contain) {
//            if (strpos($id, $contain) !== false) {
//                $containsOK = true;
//                break;
//            }
//        }
//
//        $notContainsOK = true;
//
//        foreach ($notContains as $contain) {
//            if (strpos($id, $contain) !== false) {
//                $notContainsOK = false;
//                break;
//            }
//        }
//
//
//        if ($containsOK && $notContainsOK) {
//
//            $control = $wp_customize->get_control($id);
//            if ($control) {
//                if ($control->type !== 'sectionseparator') {
//                    $data[$id] = $setting->default;
//                }
//                continue;
//            } else {
//                $data[$id] = $setting->default;
//            }
//        }
//
//    }
//
//    $data = array_merge($data, array(
//        'header_title'                          => '',
//        'header_subtitle'                       => '',
//        'header_subtitle2'                      => '',
//        'header_title_color'                    => '#000000',
//        'header_content_image_rounded'          => false,
//        'header_content_image_border_thickness' => 5,
//    ));
//
//    $data = var_export($data, true);
//    $data = str_replace(site_url(), "", $data);
//    file_put_contents(ABSPATH . '/wp-content/plugins/mods-exporter/preset.php', "<?php\n\n return " . $data . ";\n");
//
//});
