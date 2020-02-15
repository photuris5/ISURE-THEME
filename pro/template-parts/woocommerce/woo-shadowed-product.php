<?php
$thumbnail = "";

if (has_post_thumbnail()) {
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'shop_single');
} else {
    $thumbnail = esc_url(wc_placeholder_img_src());
}

/** @var WC_Product $product */
global $product;

?>

<li <?php post_class('col-xs-12'); ?> >
    <?php woocommerce_template_loop_product_link_open(); ?>
    <?php woocommerce_show_product_loop_sale_flash(); ?>
    <div data-hover-fx="woocommerce-shadowed-1" class="contentswap-effect visible woocommerce-shadowed-1">
        <div class="initial-image">
            <img data-size="500x500" src="<?php echo $thumbnail; ?>">
        </div>
        <div class="overlay"></div>
        <div class="swap-inner col-xs-12">
            <div class="row  full-height-row bottom-xs">
                <div class="col-xs-12 text-center content-holder">
                    <h4 class="color-white font-500"><?php the_title(); ?></h4>
                    <p class="small color-white" style="font-style:italic">
                        <?php if ($price_html = $product->get_price_html()) : ?>
                            <span class="price"><?php echo $price_html; ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php woocommerce_template_loop_product_link_close(); ?>
</li>
