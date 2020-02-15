<?php
/*
 * Template Name: Full Width Template
 */

add_filter('materialis_full_width_page', '__return_true');

materialis_get_header();
?>
    <div <?php echo materialis_page_content_atts(); ?>>
        <div class="<?php materialis_page_content_wrapper_class(); ?>">
            <?php
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>

<?php get_footer(); ?>
