<?php


add_shortcode('materialis_display_widgets_area', 'materialis_display_widgets_area');


function materialis_display_widgets_area($atts)
{
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);

    $content = '';

    if (empty($atts['id'])) {
        $content = materialis_placeholder_p(__('This is a placeholder','materialis') . '<br/>' . __('Configure this to display a "widgets area"' , 'materialis'));
    } else {
        $sidebars_widgets = wp_get_sidebars_widgets();
        if (empty($sidebars_widgets[$atts['id']])) {
            $widgets_areas_mod = materialis_get_theme_mod('materialis_users_custom_widgets_areas', array());
            $index             = str_replace('materialis_users_custom_widgets_areas_', '', $atts['id']);
            $name              = 'Widgets Area';
            if (isset($widgets_areas_mod[$index])) {
                $name = $widgets_areas_mod[$index]['name'];
                $name = "\"{$name}\" Widgets Area";
            }

            $content = materialis_placeholder_p($name .
                ' ' .
                __("is empty" , 'materialis') .
                '<br/>' .
                __('Configure it from WP Admin' , 'materialis')
                );
        }

        ob_start();
        dynamic_sidebar($atts['id']);
        $content .= ob_get_clean();

    }


    $content = '<div data-name="materialis-widgets-area">' . $content . '</div>';

    return $content;
}
