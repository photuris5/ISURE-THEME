<?php

add_filter('materialis_header_presets', function ($presets) {
    $result       = array();
    $presets_file = materialis_pro_dir('/customizer/header-presets.php');
    if (file_exists($presets_file)) {
        $result = require $presets_file;
    }

//    $presets = array_merge($presets, $result);
    $presets = $result;

    return $presets;
});

materialis_pro_require("/inc/header-options/navigation.php");
materialis_pro_require("/inc/header-options/split-header.php");
materialis_pro_require("/inc/header-options/background.php");
materialis_pro_require("/inc/header-options/content.php");
