<?php
function materialis_page_options()
{
    $section = "page_settings";

    materialis_add_kirki_field(array(
        'type'     => 'checkbox',
        'settings' => 'enable_content_reveal_effect',
        'label'    => __('Enable content reveal effect', 'materialis'),
        'section'  => $section,
        'default'  => false,
        'transport' => 'postMessage',
    ));
}

//materialis_page_options();


add_action('wp_head', 'materialis_add_content_effects');

add_filter("materialis_theme_pro_settings", function($settings){
    $enable_effects = materialis_get_theme_mod("enable_content_reveal_effect", false) && !materialis_is_customize_preview();
    $settings['reveal-effect'] = array(
        "enabled" => $enable_effects
    );
    return $settings;
});

function materialis_add_content_effects()
{
    $enable_effects = materialis_get_theme_mod("enable_content_reveal_effect", false) && !materialis_is_customize_preview();
    if ($enable_effects) {
    	?>
    	<style data-name="site-effects">
    		.content .row > * {
    			visibility: hidden;
    		}
    	</style>
    	<?php
    }
}
