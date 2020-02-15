<?php

/*
Template Name: Boxed Page With Navigation Only
*/

add_filter("materialis_navigation_sticky_attrs", function ($atts) {
    $atts["data-sticky-always"] = 1;

    return $atts;
});

materialis_get_header('small');

?>

    <div class="page-content <?php materialis_page_content_class(); ?>">
        <div class="gridContainer <?php materialis_page_content_wrapper_class(array('gridContainer')); ?>">
            <?php
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>

<?php get_footer(); ?>
