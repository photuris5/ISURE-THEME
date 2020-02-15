<?php

function materialis_get_translatable_mods()
{
    $materialis_translatable_mods = array(
        "header_front_page_image",
        
        "header_image",
        "header_bg_position",
        
        "header_slideshow",
        "header_video_external",
        
        "header_content_image",
        "header_content_video",
        
        "header_video_popup_image",
        
        "header_title",
        "header_subtitle",
        "header_subtitle2",
        
        "header_text_morph_alternatives",
        
        "latest_news_read_more",
        
        "header_content_buttons",
        "header_store_badges",
        
        
        "header_navigation_custom_area_buttons",
        "inner_header_navigation_custom_area_buttons",
        
        "header_section_content_header-group-of-images",
        
        "footer_content_copyright_text",
        
        "footer_content_box_text",
        
        "slider_elements",
        "third_party_slider",
    );
    
    return apply_filters("materialis_translatable_mods", $materialis_translatable_mods);
}

add_filter("materialis_translatable_mods", function ($mods) {
    
    // TODO: move each one in it's place//
    
    $sides = array("left", "right");
    
    // header buttons//
    for ($i = 1; $i < 7; $i++) {
        array_push($mods, "header_btn_{$i}_label");
        array_push($mods, "header_btn_{$i}_url");
        array_push($mods, "header_btn_{$i}_target");
    }
    
    foreach ($sides as $key => $side) {
        
        // top bar text//
        array_push($mods, "header_top_bar_area-{$side}_text");
        
        // top bar social icons//
        for ($i = 0; $i < 5; $i++) {
            array_push($mods, "header_top_bar_area-{$side}_social_icon_{$i}_enabled");
            array_push($mods, "header_top_bar_area-{$side}_social_icon_{$i}_link");
        }
        
        // top bar info fields//
        for ($i = 0; $i < 3; $i++) {
            array_push($mods, "header_top_bar_area-{$side}_info_field_{$i}_enabled");
            array_push($mods, "header_top_bar_area-{$side}_info_field_{$i}_icon");
            array_push($mods, "header_top_bar_area-{$side}_info_field_{$i}_text");
        }
    }
    
    // footer social icons//
    for ($i = 0; $i < 5; $i++) {
        array_push($mods, "footer_content_social_icon_{$i}_enabled");
        array_push($mods, "footer_content_social_icon_{$i}_link");
    }
    
    // footer content boxes//
    for ($i = 1; $i < 4; $i++) {
        array_push($mods, "footer_box{$i}_content_text");
    }
    
    return $mods;
});
