<?php

add_filter("materialis_get_theme_colors", function ($colors, $color) {
    $colors = get_theme_mod('color_palette', $colors);

    return $colors;
}, 1, 2);

function materialis_theme_colors_options()
{
    $default = materialis_get_default_colors();

    materialis_add_kirki_field(array(
        'type'     => 'repeater',
        'settings' => 'color_palette',
        'label'    => esc_html__('Site Colors', 'materialis'),
        'section'  => "colors",
        "priority" => 0,

        'row_label' => array(
            'type'  => 'field',
            'field' => 'label',
        ),

        "fields" => array(
            "label" => array(
                'type'    => 'hidden',
                'label'   => esc_attr__('Label', 'materialis'),
                'default' => 'color',
            ),

            "name" => array(
                'type'    => 'hidden',
                'label'   => esc_attr__('Name', 'materialis'),
                'default' => 'color',
            ),

            "value" => array(
                'type'    => 'color',
                'label'   => esc_attr__('Value', 'materialis'),
                'default' => '#000',
            ),
        ),

        "default"   => $default,
/*        "transport" => apply_filters('materialis_is_companion_installed', false) ? 'postMessage' : 'refresh',*/
    ));

}

materialis_theme_colors_options();

add_action('wp_head', 'materialis_print_theme_colors_style', PHP_INT_MAX);


function materialis_print_color_style_brightness($color, $steps, $as_template = false)
{
    if ( ! $as_template) {
        echo Kirki_Color::adjust_brightness($color, $steps);
    } else {
        echo "<# print(top.CP_Customizer.utils.convertColor.brighten(data.color,{$steps})) #>";
    }
}

function materialis_print_color_style_rgba($color, $alpha, $as_template = false)
{
    if ( ! $as_template) {
        echo \Kirki_Color::get_rgba($color, $alpha);
    } else {
        echo "<# print(top.CP_Customizer.utils.convertColor.toRGBA(data.color,{$alpha})) #>";
    }
}


function materialis_print_theme_colors_style()
{

    if (materialis_can_show_cached_value('materialis_colors_cached_style')) {
        if ($style = materialis_get_cached_value('materialis_colors_cached_style')) {
            ?>
            <style data-name="site-colors">
                /** cached colors style */
                <?php echo $style; ?>
                /** cached colors style */
            </style>
            <?php
            return;

        }
    }

    $as_color_template = false;
    $textElements      = array('p', 'span');
    $headers           = range(1, 6);


    foreach ($headers as $header) {
        $textElements[] = "h{$header}";
    }
    ?>
    <style data-name="site-colors">
        <?php

        ob_start();
        $colors = materialis_get_changed_theme_colors();

        $colors = array_merge($colors,
        array(
                array(
                        "name" => "color-white", "value" => "#ffffff",
                ),
                array(
                        "name" => "color-black", "value" => "#000000",
                ),
                array(
                        "name" => "color-gray", "value" => "#bdbdbd",
                ),
        ));

		 foreach ( $colors as $colorData ) {
				$color       = $colorData['value'];
				$hoverColor  = Kirki_Color::adjust_brightness( $color, 20 );
				$colorClass  = "." . $colorData['name'];
				$colorName   = $colorData['name'];

				if(WP_DEBUG){
				echo "\n/* STYLE FOR {$colorName} : {$colorClass} : {$color} : {$hoverColor}*/\n";
                }
				switch ($colorData['name']){
				    case 'color1':
				        include materialis_pro_dir( "/inc/general-options/print-primary-color-style.php") ;
						break;
					case 'color2':
					    include materialis_pro_dir( "/inc/general-options/print-secondary-color-style.php");
						break;
				}

				include materialis_pro_dir(  "/inc/general-options/print-color-style.php");

				do_action('materialis_print_theme_colors_style',$color,$hoverColor,$colorClass,$colorName);

		   };
		 $content = ob_get_clean();

		 if(!is_admin() && !materialis_is_customize_preview() && !WP_DEBUG){
		     $content = str_replace("\n"," ",$content);
		     $content = preg_replace("#\s+#"," ",$content);
		     $content = str_replace("/* */","",$content);
		     materialis_cache_value('materialis_colors_cached_style',$content);
		 }

		 echo $content;
	   ?>
    </style>
    <?php
}
/*
add_filter('cloudpress\customizer\preview_data', function ($data) {

    $colors_template = array();

    $color             = '{{data.color}}';
    $hoverColor        = '{{data.hoverColor}}';
    $colorClass        = '{{data.colorClass}}';
    $colorName         = '{{data.colorName}}';
    $textElements      = array('p', 'span');
    $headers           = range(1, 6);
    $as_color_template = true;

    foreach ($headers as $header) {
        $textElements[] = "h{$header}";
    }

    ob_start();

    include materialis_pro_dir("/inc/general-options/print-primary-color-style.php");

    $colors_template['color1'] = ob_get_clean();


    ob_start();

    include materialis_pro_dir("/inc/general-options/print-secondary-color-style.php");

    $colors_template['color2'] = ob_get_clean();

    ob_start();

    include materialis_pro_dir("/inc/general-options/print-color-style.php");

    $colors_template['general'] = ob_get_clean();

    $data['colors_template'] = $colors_template;

    return $data;

});
*/
