<?php


if ( ! function_exists("materialis_log2")) {
    function materialis_log2($msg)
    {
        $time = date(DATE_RFC2822);
        //file_put_contents(ABSPATH."/log-mods.txt", $time . "::". $msg  . "\r\n", FILE_APPEND);
    }
}

if (function_exists('pll_current_language') || class_exists('SitePress')) {
    materialis_pro_require("/inc/multilanguage/multilanguage-mods.php");
    materialis_pro_require("/inc/multilanguage/multilanguage-options.php");
}

// load support for polylang
if (function_exists('pll_current_language')) {
    materialis_pro_require("/inc/multilanguage/polylang-options.php");
}

// load support for WPML
if (class_exists('SitePress')) {
    materialis_pro_require("/inc/multilanguage/wpml-options.php");
}
