<?php
/*
Template Name: Landing Page
*/

add_filter('materialis_footer', function ($footer) {
    $footerTemplate = 'empty';

    return $footerTemplate;
}, 20, 1);
add_filter('materialis_full_width_page', '__return_true');

materialis_get_header('empty');
?>
    <div class="landing page-content <?php materialis_page_content_class(); ?>">
        <div class="<?php materialis_page_content_wrapper_class(); ?>">
            <?php
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>
<?php


get_footer();
?>
