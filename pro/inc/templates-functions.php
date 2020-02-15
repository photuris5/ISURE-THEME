<?php

//TODO : Decide if this functions should be moved in free ( depending on the templates in free ) = Not used , should be removed
function materialis_get_footer($name = null)
{
    $isInPro = locate_template("pro/footer-{$name}.php", false);

    if ($isInPro) {
        do_action('get_footer', $name);
        locate_template("pro/footer-{$name}.php", true);
    }

    if ( ! $isInPro) {
        get_footer($name);
    }
}


// from WP_THEME
function materialis_scandir($path, $extensions = null, $depth = 0, $relative_path = '')
{
    if ( ! is_dir($path)) {
        return false;
    }

    if ($extensions) {
        $extensions  = (array)$extensions;
        $_extensions = implode('|', $extensions);
    }

    $relative_path = trailingslashit($relative_path);
    if ('/' == $relative_path) {
        $relative_path = '';
    }

    $results = scandir($path);
    $files   = array();

    /**
     * Filters the array of excluded directories and files while scanning theme folder.
     *
     * @since 4.7.4
     *
     * @param array $exclusions Array of excluded directories and files.
     */
    $exclusions = (array)apply_filters('theme_scandir_exclusions', array('CVS', 'node_modules'));

    foreach ($results as $result) {
        if ('.' == $result[0] || in_array($result, $exclusions, true)) {
            continue;
        }
        if (is_dir($path . '/' . $result)) {
            if ( ! $depth) {
                continue;
            }
            $found = materialis_scandir($path . '/' . $result, $extensions, $depth - 1, $relative_path . $result);
            $files = array_merge_recursive($files, $found);
        } else if ( ! $extensions || preg_match('~\.(' . $_extensions . ')$~', $result)) {
            $files[$relative_path . $result] = $path . '/' . $result;
        }
    }

    return $files;
}

function materialis_get_files_in_folder($path, $type = null, $depth = 0, $search_parent = false)
{
    $files = (array)materialis_scandir($path, $type, $depth);

    if ($search_parent && $this->parent()) {
        $files += (array)materialis_scandir($path, $type, $depth);
    }

    return $files;
}


function materialis_pro_get_templates($templateType = 'page')
{
    $files = materialis_get_files_in_folder(materialis_pro_dir('/page-templates'), 'php', 1);

    foreach ($files as $file => $full_path) {


        $file = materialis_pro_relative_dir("/page-templates/{$file}");

        if ( ! preg_match('|Template Name:(.*)$|mi', file_get_contents($full_path), $header)) {
            continue;
        }

        $types = array('page');
        if (preg_match('|Template Post Type:(.*)$|mi', file_get_contents($full_path), $type)) {
            $types = explode(',', _cleanup_header_comment($type[1]));
        }

        foreach ($types as $type) {
            $type = sanitize_key($type);
            if ( ! isset($post_templates[$type])) {
                $post_templates[$type] = array();
            }

            $post_templates[$type][$file] = _cleanup_header_comment($header[1]);
        }
    }


    if (isset($post_templates[$templateType])) {
        return $post_templates[$templateType];
    } else {
        return array();
    }
}

add_filter("theme_page_templates", function ($post_templates, $theme, $post, $post_type) {


    $post_templates = array_merge($post_templates, materialis_pro_get_templates());

    return $post_templates;

}, 10, 4);


add_filter('materialis_maintainable_default_template', function ($template) {
    return materialis_pro_relative_dir('/page-templates/full-width-page.php');
});
