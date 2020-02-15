<?php

add_filter("materialis_media_type_choices", function ($values) {
    $new = array(
        "video"       => __("Video", "materialis"),
        "video_popup" => __("Video Popup Button", "materialis"),
    );


    if (class_exists('\Materialis\Companion')) {


        $companion = \Materialis\Companion::instance();
        $sections  = $companion->getCustomizerData("data:sections");

        foreach ($sections as $section) {
            if ($section['category'] === "header-contents") {
                $choice       = "header_contents|" . $section['id'];
                $new[$choice] = $section['name'];
            }
        }

    }

    return array_merge($values, $new);
});

add_filter('cloudpress\customizer\control\content_sections\data', 'materialis_ignore_header_content_sections', PHP_INT_MAX);


function materialis_ignore_header_content_sections($data)
{
    unset($data['header-contents']);

    return $data;
}

materialis_pro_require("/inc/header-options/content-options/content-types/video.php");

materialis_pro_require("/inc/header-options/content-options/inner-pages.php");
materialis_pro_require("/inc/header-options/content-options/subtitle2.php");
materialis_pro_require("/inc/header-options/content-options/title.php");
materialis_pro_require("/inc/header-options/content-options/subtitle.php");
materialis_pro_require("/inc/header-options/content-options/buttons.php");


add_action("materialis_print_header_media", function ($mediaType) {
    if ($mediaType == "video") {
        materialis_print_header_video();
    }
});

add_action("materialis_print_header_media", function ($mediaType) {
    if ($mediaType == "video_popup") {
        materialis_header_video_popup();
    }
});


add_action("materialis_print_header_media", function ($mediaType) {
    if (strpos($mediaType, 'header_contents|') === 0) {
        $mod_part = str_replace("header_contents|", "", $mediaType);
        materialis_print_header_section_content($mod_part);
    }
});

function materialis_oembed_autoplay_loop_args($provider, $url, $args)
{

    $provider = remove_query_arg('width', $provider);
    $provider = remove_query_arg('height', $provider);
    $provider = remove_query_arg('maxwidth', $provider);
    $provider = remove_query_arg('maxheight', $provider);

    $php_url_query = parse_url($url, PHP_URL_QUERY);

    if (empty($php_url_query)) {
        return $provider;
    }


    $provider_parts = explode('%3F', $provider);

    if (empty($provider_parts['1'])) {
        return $provider;
    }

    parse_str($php_url_query, $query_args);

    foreach ($query_args as $key => $value) {
        if ($key !== 'v') {
            $provider = add_query_arg($key, $value, $provider);

        }
    }

    $provider = add_query_arg("enablejsapi", 1, $provider);
    $provider = add_query_arg("api", 1, $provider);
 

    return $provider;

}


function materialis_add_autoplay_loop_to_oembed($html, $url, $args)
{
    $autoplay = (isset($args['autoplay']) && intval($args['autoplay']));
    $loop     = (isset($args['loop']) && intval($args['loop']));
    $mute     = (isset($args['mute']) && intval($args['mute']));

    $result = array();
    preg_match('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $html, $result);

    if (count($result)) {
        $newUrl = $result[0];

        if (intval($autoplay)) {
            $newUrl = add_query_arg('autoplay', 1, $newUrl);


            if (strpos($html, '<iframe') !== false) {
                $allow_content = "";

                if (strpos($html, "allow=") !== false) {
                    $matches = array();
                    preg_match("#allow=['|\"](.*)['|\"]#", $html, $matches);

                    if (count($matches) === 2) {
                        $allow_content = $matches[1];
                        str_replace($matches[0], "", $html);
                    }
                }

                $html = str_replace("<iframe", "<iframe allow='autoplay; {$allow_content}'", $html);
            }

        }

        if (intval($loop)) {
            $newUrl = add_query_arg('loop', 1, $newUrl);

        }

        if (intval($mute)) {
            $newUrl = add_query_arg('mute', 1, $newUrl);

        }        

        if (preg_match("#https?://((m|www)\.)?youtube\.com/watch.*#i", $url)) {
            $videoId = explode('/', $newUrl);
            $videoId = array_pop($videoId);
            $videoId = explode('?', $videoId);
            $videoId = $videoId[0];

            $newUrl = add_query_arg('playlist', $videoId, $newUrl);
        }

        $html = str_replace($result, $newUrl, $html);

    }

    return $html;
}


function materialis_print_header_video()
{

    add_filter('oembed_fetch_url', 'materialis_oembed_autoplay_loop_args', 10, 3);
    add_filter('oembed_result', 'materialis_add_autoplay_loop_to_oembed', 10, 3);

    $video = materialis_get_theme_mod('header_content_video', 'https://www.youtube.com/watch?v=3iXYciBTQ0c');
    $embed = new WP_Embed();

    $autoplay = materialis_get_theme_mod('header_content_video_autoplay', '0');
    $mute = materialis_get_theme_mod('header_content_video_mute', '0');

    if (materialis_is_customize_preview()) {
        $autoplay = false;
    }

    $content = $embed->shortcode(array(
        'src'      => $video,
        'autoplay' => $autoplay,
        'mute'     => $mute,
        'loop'     => materialis_get_theme_mod('header_content_video_loop', '0'),
    ));

    $content = preg_replace('/width="\d+"/', "", $content);
    $content = preg_replace('/height="\d+"/', 'class="header-hero-video"', $content);

    $class = "";

    if (strpos($content, '<iframe') !== false) {
        $class = "iframe-holder ";
    }

    remove_filter('oembed_fetch_url', 'materialis_oembed_autoplay_loop_args');
    remove_filter('oembed_result', 'materialis_add_autoplay_loop_to_oembed');


    echo '<div class="content-video-container ' . $class . '">' . $content . '</div>';
}

function materialis_header_video_popup()
{

    $url   = materialis_get_theme_mod('header_content_video', 'https://www.youtube.com/watch?v=3iXYciBTQ0c');
    $style = "";

    $image    = materialis_get_theme_mod('header_video_popup_image', get_template_directory_uri() . "/assets/images/video-poster.jpg");
    $disabled = materialis_get_theme_mod('header_video_popup_image_disabled', false);

    if (intval($disabled)) {
        $image = false;
    }

    ob_start();
    ?>
    <div class="video-popup-button <?php echo ($image) ? 'with-image' : '' ?>">
        <?php if ($image): ?>
            <img class="poster" src="<?php echo $image ?>"/>
        <?php endif; ?>
        <a class="video-popup-button-link" data-fancybox data-video-lightbox="true" href="<?php echo $url ?>">
            <i class="mdi mdi-play-circle"></i>
        </a>
    </div>
    <?php
    echo ob_get_clean();
}


add_filter("materialis_header_content_partial", function ($values) {
    $new = array(
        "media-on-top"    => __("Text with media above", "materialis"),
        "media-on-bottom" => __("Text with media below", "materialis"),
    );

    return array_merge($values, $new);
});

add_filter("materialis_header_content_partial_update", function ($values) {
    $new = array(
        array(
            "value"  => "media-on-top",
            "fields" => array(
                'header_text_box_text_align' => 'center',
                'header_spacing'             => array(
                    'top'    => '5%',
                    'bottom' => '5%',
                ),
            ),
        ),

        array(
            "value"  => "media-on-bottom",
            "fields" => array(
                'header_text_box_text_align' => 'center',
                'header_spacing'             => array(
                    'top'    => '5%',
                    'bottom' => '5%',
                ),
            ),
        ),
    );

    return array_merge($values, $new);
});

add_action('wp_head', 'materialis_print_header_content_video_img_shadow');


function materialis_print_header_content_video_img_shadow()
{

    $hasShadow = materialis_get_theme_mod('header_content_video_img_shadow', false);

    if ($hasShadow) {
        ?>
        <style data-name="header_content_video_img_shadow">
            .header-description-row img.homepage-header-image,
            .header-description-row .video-popup-button img,
            .header-description-row div.content-video-container {
                -moz-box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
                -webkit-box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            }
        </style>
        <?php
    }
}

function materialis_print_header_section_content($mod_part)
{
    $mod     = "header_section_content_{$mod_part}";
    $content = materialis_get_theme_mod($mod, false);

    if ($content === false || ! trim($content)) {
        $companion = \Materialis\Companion::instance();
        $sections  = $companion->getCustomizerData("data:sections");


        foreach ($sections as $section) {
            if ($section['id'] === $mod_part) {
                $content = $section['content'];
                $content = \Materialis\Companion::filterDefault($content);
                break;
            }
        }
    }

    ?>
    <div class="header-section-content" data-theme="<?php echo $mod; ?>">
        <?php echo $content; ?>
    </div>
    <?php
}
