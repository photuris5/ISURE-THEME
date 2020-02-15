<?php



add_action("materialis_front_page_header_media_box_options_after", "materialis_front_page_header_media_box_options_after", 0, 3);

function materialis_front_page_header_media_box_options_after($section, $prefix, $priority)
{

    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'settings'        => $prefix . '_content_image_rounded',
        'label'           => __('Make Image round', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => false,
        'transport'       => 'postMessage',
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('image'),
            ),
        ),

    ));

    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => $prefix . '_content_image_border_color',
        'label'           => __('Image Border Color', 'materialis'),
        'section'         => $section,
        'default'         => "#ffffff",
        'priority'        => $priority,
        'choices'         => array(
            'alpha' => true,
        ),
        "output"          => array(
            array(
                'element'  => '.materialis-front-page .homepage-header-image',
                'property' => 'border-color',
                'suffix'   => '!important',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.materialis-front-page .homepage-header-image',
                'property' => 'border-color',
                'suffix'   => '!important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('image'),
            ),
            array(
                'setting'  => $prefix . '_content_image_rounded',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'slider',
        'settings'        => $prefix . '_content_image_border_thickness',
        'label'           => __('Image Border Thickness', 'materialis'),
        'section'         => $section,
        'default'         => 5,
        'priority'        => $priority,
         'choices' => array(
            'min'  => '0',
            'max'  => '20',
            'step' => '1',
        ),
        "output"          => array(
            array(
                'element'  => '.materialis-front-page .homepage-header-image',
                'property' => 'border-width',
                'suffix'   => 'px !important',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => '.materialis-front-page .homepage-header-image',
                'property' => 'border-width',
                'suffix'   => 'px !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('image'),
            ),
            array(
                'setting'  => $prefix . '_content_image_rounded',
                'operator' => '==',
                'value'    => true,
            ),
        ),
    ));


    // last
    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'settings'        => $prefix . '_content_video_img_shadow',
        'label'           => __('Enable media shadow', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => false,
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('image', 'video', 'video_popup'),
            ),
        ),
    ));
}

add_action("materialis_front_page_header_content_options_before", "materialis_front_page_header_content_options_video", 0, 3);

function materialis_front_page_header_content_options_video($section, $prefix, $priority)
{

    materialis_add_kirki_field(array(
        'type'            => 'text',
        'settings'        => $prefix . '_content_video',
        'label'           => __('Content Video', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => 'https://www.youtube.com/watch?v=3iXYciBTQ0c',
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'contains',
                'value'    => 'video',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'        => 'checkbox',
        'settings'    => $prefix . '_content_video_autoplay',
        'label'       => __('Autoplay video', 'materialis'),
        'description' => __('In customizer the video auto play is turned off for performance improvements', 'materialis'),
        'section'     => $section,
        'priority'    => $priority,
        'default'     => false,

        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => '==',
                'value'    => 'video',
            ),
        ),
    ));

    materialis_add_kirki_field(array(
        'type'        => 'checkbox',
        'settings'    => $prefix . '_content_video_mute',
        'label'       => __('Mute video', 'materialis'),
        'description' => __('"Some browsers (including Chrome) will not autoplay videos unless they are muted.', 'materialis'),
        'section'     => $section,
        'priority'    => $priority,
        'default'     => false,

        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => '==',
                'value'    => 'video',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'checkbox',
        'settings'        => $prefix . '_content_video_loop',
        'label'           => __('Loop Video', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => false,
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => '==',
                'value'    => 'video',
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => $prefix . '_color_video_popup_button',
        'label'           => __('Icon Color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => "#ffffff",
        'choices'         => array(
            'alpha' => true,
        ),
        "output"          => array(
            array(
                'element'  => 'a.video-popup-button-link',
                'property' => 'color',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => 'a.video-popup-button-link',
                'function' => 'css',
                'property' => 'color',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('video_popup'),
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => $prefix . '_color_video_popup_button_hover',
        'label'           => __('Icon Hover Color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => "#7AA7F5",
        'choices'         => array(
            'alpha' => true,
        ),
        "output"          => array(
            array(
                'element'  => 'a.video-popup-button-link:hover',
                'property' => 'color',
            ),
        ),
        'transport'       => 'postMessage',
        'js_vars'         => array(
            array(
                'element'  => 'a.video-popup-button-link:hover',
                'function' => 'css',
                'property' => 'color',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('video_popup'),
            ),
        ),
    ));


    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => $prefix . '_video_popup_image_disabled',
        'label'    => __('Hide Video Poster', 'materialis'),
        'section'  => $section,
        'priority' => $priority,
        'default'  => false,

        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('video_popup'),
            ),

        ),
    ));

    materialis_add_kirki_field(array(
        'type'            => 'image',
        'settings'        => $prefix . '_video_popup_image',
        'label'           => __('Poster', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => get_template_directory_uri() . "/assets/images/video-poster.jpg",
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => '==',
                'value'    => 'video_popup',
            ),

            array(
                'setting'  => $prefix . '_video_popup_image_disabled',
                'operator' => '==',
                'value'    => false,
            ),

        ),
    ));


    materialis_add_kirki_field(array(
        'type'            => 'color',
        'settings'        => $prefix . '_video_popup_overlay_color',
        'label'           => __('Video Poster Overlay Color', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        'default'         => "rgba(0, 0, 0, 0.5)",
        'choices'         => array(
            'alpha' => true,
        ),
        'transport'       => 'postMessage',
        "output"          => array(
            array(
                'element'  => '.video-popup-button.with-image:before',
                'property' => 'background-color',
            ),
        ),
        'js_vars'         => array(
            array(
                'element'  => '.video-popup-button.with-image:before',
                'function' => 'css',
                'property' => 'background-color',
                'suffix'   => ' !important',
            ),
        ),
        'active_callback' => array(
            array(
                'setting'  => $prefix . '_content_media',
                'operator' => 'in',
                'value'    => array('video_popup'),
            ),
            array(
                'setting'  => $prefix . '_video_popup_image_disabled',
                'operator' => '==',
                'value'    => false,
            ),
        ),
    ));
}


add_filter("header_media_box_settings_filter", function ($values) {
    $new = array(

        "header_content_video",
        "header_content_video_autoplay",
        "header_content_video_mute",
        "header_content_video_loop",


        "header_color_video_popup_button",
        "header_color_video_popup_button_hover",

        "header_video_popup_image_disabled",
        "header_video_popup_image",
        "header_video_popup_overlay_color"
    );


    $index = array_search("header_column_width", $values);

    array_splice($values, $index + 1, 0, array(
        "header_content_image_rounded",
        "header_content_image_border_color",
        "header_content_image_border_thickness",
        "header_content_video_img_shadow"
    ));

    return array_merge($values, $new);
});
