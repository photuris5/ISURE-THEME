<?php


//icl_translation_of

add_filter('cloudpress\customizer\global_data', function($data) {
    if (class_exists('SitePress')) {

        $lang = materialis_get_current_language();
        if ($lang && $lang != materialis_get_default_language()) {
            global $sitepress;
            $home = $sitepress->convert_url( $sitepress->get_wp_api()->get_home_url(), $lang );
            $data['homeUrl']        = $home;
            $data['canSetPrimaryLocation']        = true;


            $theme_locations = get_nav_menu_locations();
            if (isset($theme_locations['primary'])) {
                global $wpml_term_translations;
                $translated_menu_id = $wpml_term_translations->term_id_in( $theme_locations['primary'], $lang );
                if (!$translated_menu_id) {
                    $data['primaryLocationDefaultLanguageMenu'] = $theme_locations['primary'];
                    $data['canSetPrimaryLocation']        = true;
                }
            }
        }

    }
    return $data;
});

add_filter("wpml_current_language", function($lang) {
    global $sitepress, $pagenow;
    if (is_admin() && 'customize.php' === $pagenow) {
        $preview_url = isset($_GET['url']) ? wp_unslash( $_GET['url'] ) : site_url();
        if ($preview_url) {
            $lang = $sitepress->get_language_from_url($preview_url);
            materialis_log2("wpml_current_language => $preview_url => $lang");
        }
    }


    if (is_admin() && 'admin-ajax.php' === $pagenow) {
        $page_id = isset($_POST['customize_post_id']) ?  intval($_POST['customize_post_id']) : -1;
        if ($page_id != -1) {
            $info = apply_filters( 'wpml_post_language_details', null, $page_id );
            return $info['language_code'];
        }
    }

    return $lang;
}, 9999);


add_filter("materialis_get_default_language", function($lang) {
    $lang = apply_filters( 'wpml_default_language', null );
    return $lang;
});

add_filter("materialis_get_post_language", function($lang, $post_id) {
    if ($post_id) {
        $info = apply_filters( 'wpml_post_language_details', null, $post_id );
        return $info['language_code'];
    }
}, 0, 2);


add_filter("materialis_get_current_language", function($lang) {
    $lang = apply_filters( 'wpml_current_language', null );
    return $lang;
});


materialis_set_cookie_language();

add_action( 'init', function() {
    materialis_log2("--------------------------- init ----------------------------------");
    materialis_set_cookie_language();
});


/*
    reset cookie language when opening a page in customizer
    the internal wpml language function has no filter and defaults to cookie language
*/

function materialis_set_cookie_language() {
    global $pagenow;
    if ('customize.php' === $pagenow) {
        global $wpml_request_handler;
        if ($wpml_request_handler) {
            materialis_log2("--------------------------- wpml_current_language => wpml_request_handler ----------------------------------");
            materialis_log2("lang1 == ".$wpml_request_handler->get_cookie_lang() . "=>" . materialis_get_current_language());
            $wpml_request_handler->set_language_cookie(materialis_get_current_language());
            materialis_log2("lang2 == ".$wpml_request_handler->get_cookie_lang() . "=>" . materialis_get_current_language());
        }
    }
}




function materialis_filter_nav_menu_locations( $theme_locations ) {

    global $wpml_term_translations;

    if ( is_admin() && (bool) $theme_locations === true ) {
        $current_lang = materialis_get_current_language();

        foreach ( (array) $theme_locations as $location => $menu_id ) {
            $translated_menu_id = $wpml_term_translations->term_id_in( $menu_id, $current_lang );
            if ( $translated_menu_id ) {
                $theme_locations[ $location ] = $translated_menu_id;
            }
        }
    }

    return $theme_locations;
}




global $materialis_menu_locations;
add_filter( 'theme_mod_nav_menu_locations',  function($theme_locations) {
    global $pagenow;
    if ( is_admin () && isset( $pagenow ) && $pagenow === 'customize.php' ) {
        global $materialis_menu_locations;
        $materialis_menu_locations = $theme_locations;
    }
    return $theme_locations;
}, -99999999);


add_filter( 'theme_mod_nav_menu_locations',  function($theme_locations) {
    global $pagenow;
    if ( is_admin () && isset( $pagenow ) && $pagenow === 'customize.php' ) {
        global $materialis_menu_locations;
        $materialis_menu_locations = materialis_filter_nav_menu_locations ( $materialis_menu_locations );
        return $materialis_menu_locations;
    }
    return $theme_locations;
}, 99999999);






global $materialis_menus;
add_filter( 'wp_get_nav_menus',  function($menus) {
    global $pagenow;
    if ( is_admin () && isset( $pagenow ) && $pagenow === 'customize.php' ) {
        global $materialis_menus;
        $materialis_menus = $menus;
    }
    return $menus;
}, -99999999);


add_filter( 'wp_get_nav_menus',  function($menus) {
    global $pagenow;
    if ( is_admin () && isset( $pagenow ) && $pagenow === 'customize.php' ) {
        global $materialis_menus;
        $materialis_menus = materialis_filter_language_menus ( $materialis_menus );
        return $materialis_menus;
    }
    return $menus;
}, 99999999);


function materialis_filter_language_menus( $menus ) {
    global $sitepress, $wpml_term_translations;
    $current_language = materialis_get_current_language();

    foreach ( $menus as $index => $menu ) {
        $menu_ttid = is_object ( $menu ) ? $menu->term_taxonomy_id : $menu;
        $menu_language = $wpml_term_translations->get_element_lang_code ( $menu_ttid );
        if ( $menu_language != $current_language && $menu_language != null ) {
            unset( $menus[ $index ] );
        }
    }

    return $menus;
}


add_action('wp_head', function () {
    $general_typo      = materialis_get_theme_mod('general_site_typography', array(
        'font-family' => 'Source Sans Pro',
        'color'       => '#666666',
    ));
    $general_font_size = materialis_get_theme_mod('general_site_typography_size');
    ?>
    <style>
        body .wpml-ls-statics-footer {
            margin-bottom: 0px !important;
            background-color: #222222;
            border-left: none;
            border-right: none;
            border-bottom: none;
            font-family: <?php echo $general_typo['font-family']; ?>, sans-serif;
            font-size: 1rem;
            padding-top:20px;
            padding-bottom: 20px;
        }

        .wpml-ls-statics-footer a {
            color: #ffffff;
            margin-bottom: 0.2em;
            height: calc( 1.5rem + 10px);
            line-height: initial;
        }

        .wpml-ls-statics-footer li {
            transition: all .4s linear;
            line-height: 1.5;

        }

        body .wpml-ls-statics-footer li.wpml-ls-current-language {
            opacity: 0.9;
        }

        p#wpml_credit_footer {
            background-color: #222222;
            text-align: center;
            padding: 20px;
            margin-bottom: 0px;
        }

        .wpml-ls-statics-footer.wpml-ls-legacy-list-vertical {
            width: 100%;
            text-align: center;
        }

        .wpml-ls-statics-footer.wpml-ls-legacy-list-vertical ul {
            display: inline-block;
        }

    </style>
    <?php
}, 4);

add_action('admin_footer', function () {
    global $current_screen;

    if (strpos($current_screen->base, 'wpml-string-translation') === 0) {
        ?>
        <script>
            (function ($) {
                $('option[value="admin_texts_theme_mods_materialis-pro"]').text("<?php _e('Materialis Theme Texts', 'materialis'); ?>");
            })(jQuery);
        </script>
        <?php
    }
});


function materialis_get_wpml_switcher($args = array(), $ulClass = "")
{


    $args = array_merge((array)$args, array());


    $languages = icl_get_languages();
    $result    = "";


    foreach ($languages as $language) {
        $class = 'lang-item';
        if ($language['active'] == 1) {
            $class = "{$class} current-lang";
        }

        $result .= sprintf(
            '<li class="%1$s"><a lang="%2$s" hreflang="%2$s" href="%3$s">%4$s%5$s</a></li>',
            $class,
            $language['native_name'],
            $language['url'],
            sprintf('<img src="%1$s"" title="%2$s" />', $language['country_flag_url'], $language['native_name']),
            sprintf('<span style="margin-left:0.3em;">%1$s</span>', $language['native_name'])
        );
    }

    $result = "<ul class='materialis-language-switcher {$ulClass}'>$result</ul>";


    return $result;
}

add_action('wp_footer', function () {

    $show_switcher = materialis_get_theme_mod('materialis_show_language_switcher', true);

    if ( ! intval($show_switcher)) {
        return;
    }


    $position = 'after';
    $content  = "";

    if (function_exists('icl_get_languages')) {
        $content = materialis_get_wpml_switcher(array(), "{$position}-menu");
    }

    echo $content;
}, 10, 2);

function materialis_wpml_override_copy_content_function()
{
    ob_start();
    global $post, $sitepress, $wpdb;

    if ( ! $post) {
        return '//nothing to change';
    }

    $sourceTRID      = isset($_REQUEST['trid']) ? $_REQUEST['trid'] : null;
    $defaultLanguage = $sitepress->get_default_language();
    $post_id         = $post->ID;
    $trid            = $sitepress->get_element_trid($post_id);
    $trid            = $trid ? $trid : $sourceTRID;
    $defaultID       = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s",
            $trid,
            $defaultLanguage));


    if ($post->ID === $defaultID) {
        return '//nothing to change';
    }

    $primaryPost = get_post($defaultID);
    $content     = wp_json_encode($primaryPost->post_content);

    ?>
    <script>
        // this is me
        function icl_copy_from_original() {

            var tinyMCEisVisible = (tinyMCE.get('content') && !tinyMCE.get('content').isHidden() && tinyMCE.get('content').hasVisual === true);

            var content =<?php echo $content; ?>;
            if (tinyMCEisVisible) {
                tinyMCE.get('content').setContent(content);
            } else {
                edInsertContent(edCanvas, content)
            }


        }
    </script>
    <?php
    $content = ob_get_clean();
    $content = str_replace("<script>", "", $content);
    $content = str_replace("</script>", "", $content);

    return $content;
}

add_action('admin_enqueue_scripts', 'materialis_enqueue_wpml_override_copy_content_function');

function materialis_enqueue_wpml_override_copy_content_function()
{
    wp_add_inline_script('sitepress-scripts', materialis_wpml_override_copy_content_function());
}
