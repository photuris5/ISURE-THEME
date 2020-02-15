<?php

add_shortcode('materialis_display_woocommerce_items', 'materialis_display_woocommerce_items');

$materialis_display_woocommerce_items_class = array();

function materialis_display_woocommerce_items($atts) {

    if(isset($atts['template_slug'])) {
      $atts['template_slug'] = 'pro/template-parts/woocommerce/' . $atts['template_slug'];
    }

    $atts = shortcode_atts(array(
        'id'              => '',
        'products_number' => '4',
        'filter'          => 'all',
        'columns'         => '3',
        'columns_tablet'  => '3',
        'columns_mobile'  => '12',
        'order'           => 'DESC',
        'order_by'        => 'date',
        'categories'      => '',
        'tags'            => '',
        'products'        => '',
        'custom'          => "false",
        'class'           => '',
        'template_slug'   => 'content',
    ), $atts);

    $content = "";


    $query_args = array();

    if ( ! class_exists('WooCommerce')) {
        return materialis_placeholder_p(__('This element needs the WooCommerce plugin to be installed' , 'materialis') );
    } else {
        if (json_decode($atts['custom'])) {
            $atts['custom'] = true;
            if (empty($atts['products']) || $atts['products'] === 'null') {
                return materialis_placeholder_p(__('Select some products to be displayed here' , 'materialis')) ;
            } else {
                $query_args = array(
                    'post_type'           => 'product',
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => 1,
                    'posts_per_page'      => -1,
                    'meta_query'          => WC()->query->get_meta_query(),
                    'tax_query'           => WC()->query->get_tax_query(),
                );

                $query_args['post__in'] = array_map('trim', explode(',', $atts['products']));
            }
        } else {
            $atts['custom'] = false;
            $query_args     = array(
                'post_type'           => 'product',
                'post_status'         => 'publish',
                'ignore_sticky_posts' => 1,
                'posts_per_page'      => intval($atts['products_number']),
                'order'               => $atts['order'],
                'meta_query'          => WC()->query->get_meta_query(),
                'tax_query'           => WC()->query->get_tax_query(),
            );


            switch (strtolower($atts['order_by'])) {
                case 'price' :
                    $query_args['meta_key'] = '_price';
                    $query_args['orderby']  = 'meta_value_num';
                    break;
                case 'random' :
                    $query_args['orderby'] = 'rand';
                    break;
                case 'sales' :
                    $query_args['meta_key'] = 'total_sales';
                    $query_args['orderby']  = 'meta_value_num';
                    break;
                case 'rating' :
                    $query_args['meta_key'] = '_wc_average_rating';
                    $query_args['orderby']  = 'meta_value_num';
                    break;
                default :
                    $query_args['orderby'] = 'date';
            }

            if ($atts['categories'] && ! empty($atts['categories']) && $atts['categories'] !== 'null') {
                $query_args = materialis_woocommerce_query_maybe_add_category_args($query_args, $atts['categories']);
            }

            if ($atts['tags'] && ! empty($atts['tags']) && $atts['tags'] !== 'null') {
                $query_args = materialis_woocommerce_query_maybe_add_tags_args($query_args, $atts['tags']);
            }


            if ($atts['filter'] == "onsale") {
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $query_args['post__in'] = $product_ids_on_sale;
            }

            if ($atts['filter'] == "featured") {
                $product_visibility_term_ids = wc_get_product_visibility_term_ids();
                $query_args['tax_query'][]   = array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $product_visibility_term_ids['featured'],
                );
            }

        }
    }

    $content = materialis_woocommerce_items_do_query($query_args, $atts, 'products');

    return $content;
}

function materialis_woocommerce_items_do_query_post_classes_filter($classes)
{
    $atts = isset($GLOBALS['materialis_woocommerce_items_do_query_atts']) ? $GLOBALS['materialis_woocommerce_items_do_query_atts'] : false;

    if ( ! ($atts)) {
        return $classes;
    }

    $width        = $atts['columns'];
    $width_tablet = $atts['columns_tablet'];
    $mobile_width = $atts['columns_mobile'];

    $classes[] = "col-md-{$width}";
    $classes[] = "col-sm-{$width_tablet}";
    $classes[] = "col-xs-{$mobile_width}";
    $classes[] = "in-page-section";


    return $classes;
}

function materialis_woocommerce_archive_item_class_in_page($classes)
{
    $atts = isset($GLOBALS['materialis_woocommerce_items_do_query_atts']) ? $GLOBALS['materialis_woocommerce_items_do_query_atts'] : false;

    if ( ! ($atts)) {
        return $classes;
    }

    $classes = $classes + explode(' ', $atts['class']);

    return $classes;
}

function materialis_woocommerce_items_do_query($query_args, $atts, $loop_name)
{

    $products = new WP_Query($query_args);

    // sort as added in the list
    if ($atts['custom'] === true) {
        $ids = $query_args['post__in'];
        usort($products->posts, function ($a, $b) use ($ids) {
            $apos = array_search($a->ID, $ids);
            $bpos = array_search($b->ID, $ids);

            return ($apos < $bpos) ? -1 : 1;
        });
    }

    ob_start();

    $GLOBALS['materialis_woocommerce_items_do_query_atts'] = $atts;

    add_filter('materialis_woocommerce_archive_item_class', 'materialis_woocommerce_archive_item_class_in_page');
    add_filter('post_class', 'materialis_woocommerce_items_do_query_post_classes_filter');

    $templateSlug = $atts['template_slug'];

    if ($products->have_posts()) {
        do_action("woocommerce_shortcode_before_{$loop_name}_loop", $atts); ?>

        <?php woocommerce_product_loop_start(); ?>

        <?php while ($products->have_posts()) : $products->the_post(); ?>

            <?php wc_get_template_part($templateSlug, 'product'); ?>

        <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

        <?php do_action("woocommerce_shortcode_after_{$loop_name}_loop", $atts); ?>

        <?php
    } else {
        echo materialis_placeholder_p(__('No products to display', 'materialis'));
    }


    woocommerce_reset_loop();
    wp_reset_postdata();

    remove_filter('post_class', 'materialis_woocommerce_items_do_query_post_classes_filter');
    $GLOBALS['materialis_woocommerce_items_do_query_atts'] = false;

    return '<div class="woocommerce in-section">' . ob_get_clean() . '</div>';
}


add_shortcode('materialis_woocommerce_shop_url', 'materialis_woocommerce_shop_url');

function materialis_woocommerce_shop_url()
{
    if ( ! function_exists('wc_get_page_permalink')) {
        return '#';
    }

    $shop_page_url = wc_get_page_permalink('shop');

    return $shop_page_url;
}
