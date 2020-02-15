<?php

add_filter('mc4wp_form_content', 'materialis_mc4wp_filter');
function materialis_mc4wp_filter($content)
{
    $matches = array();
    preg_match_all('/<input[^>]+>/', $content, $matches);

    $email  = "";
    $submit = "";
    for ($i = 0; $i < count($matches[0]); $i++) {
        $match = $matches[0][$i];
        if (strpos($match, "email") !== false) {
            $email = $match;
        }
        if (strpos($match, "submit") !== false) {
            $submit = $match;
        }
    }

    return $email . $submit;
}

add_shortcode('materialis_subscribe_form', 'materialis_subscribe_form');
function materialis_subscribe_form($atts = array())
{
    ob_start();
    echo '<div class="subscribe-form">';
    if (isset($atts['shortcode'])) {
        echo do_shortcode("[" . html_entity_decode(html_entity_decode($atts['shortcode'])) . "]");
    } else {
        materialis_placeholder_p( __('Subscribe form will be displayed here. To activate it you have to set the "subscribe form shortcode" parameter in Customizer.',
                'materialis'), true);
    }
    echo '</div>';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
