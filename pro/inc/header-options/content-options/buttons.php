<?php

add_filter('materialis_header_buttons_group', 'materialis_header_app_buttons_controls');

function materialis_header_app_buttons_controls($controls)
{
    array_unshift($controls, 'header_store_badges');
    array_unshift($controls, 'header_buttons_type');

    return $controls;
}


add_action('materialis_front_page_header_buttons_options_before', 'materialis_front_page_header_appstore_buttons_options', 10, 3);


function materialis_header_normal_buttons_active_store_badge($active_callbacks)
{

    $active_callbacks[] = array(
        'setting'  => 'header_buttons_type',
        'operator' => '==',
        'value'    => 'normal',
    );

    return $active_callbacks;
}

add_filter('materialis_header_normal_buttons_active', 'materialis_header_normal_buttons_active_store_badge');

function materialis_front_page_header_appstore_buttons_options($section, $prefix, $priority)
{
    materialis_add_kirki_field(
        array(
            'title'    => __('Buttons Type', 'materialis'),
            'type'     => 'select',
            'settings' => "{$prefix}_buttons_type",
            'section'  => $section,
            'priority' => $priority - 1,
            'default'  => 'normal',
            'choices'  => array(
                'normal' => __('Normal Buttons', 'materialis'),
                'store'  => __('App Store buttons', 'materialis'),
            ),
        )
    );

    materialis_add_kirki_field(array(
        'type'            => 'repeater',
        'settings'        => "{$prefix}_store_badges",
        'label'           => esc_html__('Store Badges', 'materialis'),
        'section'         => $section,
        'priority'        => $priority,
        "default"         => array(
            array(
                'store' => 'google-store',
                'link'  => '#',
            ),
            array(
                'store' => 'apple-store',
                'link'  => '#',
            ),
        ),
        'row_label'       => array(
            'type'  => 'field',
            'field' => 'store',
            'value' => esc_attr__('Store Badge', 'materialis'),
        ),
        "fields"          => array(
            "store" => array(
                "type"    => "select",
                'label'   => esc_attr__('Badge Type', 'materialis'),
                "choices" => array(
                    "google-store" => "Google Play Badge",
                    "apple-store"  => "App Store Badge",
                ),
                "default" => "google-store",
            ),
            'link'  => array(
                'type'    => 'text',
                'label'   => esc_attr__('Link', 'materialis'),
                'default' => '#',
            ),
        ),
        'choices'         => array(
            'limit' => 2,
        ),
        'active_callback' => array(
            array(
                'setting'  => "{$prefix}_buttons_type",
                'operator' => '==',
                'value'    => 'store',
            ),
        ),
    ));



}


add_filter('materialis_header_buttons_content', 'materialis_header_appstore_buttons_content', 10);

function materialis_header_appstore_buttons_content($content)
{

    $buttons_type = materialis_get_theme_mod('header_buttons_type', 'normal');

    if ($buttons_type === 'store') {
        ob_start();

        materialis_print_stores_badges();

        $content = ob_get_clean();
    }

    return $content;

}


function materialis_print_stores_badges()
{
    $stores = materialis_get_theme_mod('header_store_badges', array(
        array(
            'store' => 'google-store',
            'link'  => '#',
        ),
        array(
            'store' => 'apple-store',
            'link'  => '#',
        ),
    ));

    $locale = get_locale();
    $locale = explode('_', $locale);
    $locale = $locale[0];
    $locale = strtolower($locale);


    $imgRoot = materialis_pro_dir() . "/assets/store-badges";


    foreach ((array)$stores as $storeData) {

        $store = $storeData['store'];
        $link  = $storeData['link'];

        $imgPath = "{$imgRoot}/{$store}";

        if ($store === "apple-store") {

            $img = $imgPath . "/download_on_the_app_store_badge_{$locale}_135x40.svg";

            if ( ! file_exists($img)) {
                $img = $imgPath . "/download_on_the_app_store_badge_en_135x40.svg";
            }

            $imgPath = $img;
        }

        if ($store === "google-store") {
            $img = $imgPath . "/{$locale}_badge_web_generic.svg";

            if ( ! file_exists($img)) {
                $img = $imgPath . "/en_badge_web_generic.svg";
            }

            $imgPath = $img;
        }


        $imgData = file_get_contents($imgPath);

        if ($store === "google-store") {
            $imgData = str_replace('viewBox="0 0 155 60"', 'viewBox="10 10 135 40"', $imgData);
        }

        $imgData = preg_replace('/width="\d+px"/', '', $imgData);
        $imgData = preg_replace('/height="\d+px"/', '', $imgData);

        $previewData = '';
        if (materialis_is_customize_preview()) {
            $previewData = 'data-focus-control="header_store_badges" data-dynamic-mod="true" data-type="group"';
        }

        printf('<a ' . $previewData . ' class="badge-button button %3$s" target="_blank" href="%1$s">%2$s</a>', esc_url($link), $imgData, $store);

    }

}
