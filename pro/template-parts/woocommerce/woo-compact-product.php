<?php
$thumbnail = "";

if (has_post_thumbnail()) {
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');
} else {
    $thumbnail = esc_url(wc_placeholder_img_src());
}

/** @var WC_Product $product */
global $product;

?>

<li <?php post_class('col-xs-12'); ?> >
  <div class="ope-woo-card-item">
    <?php woocommerce_template_loop_product_link_open(); ?>
    <?php woocommerce_show_product_loop_sale_flash(); ?>
    <div class="ope-woo-card-header">
      <img data-size="500x500" src="<?php echo $thumbnail; ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image">
    </div>
    <?php woocommerce_template_loop_product_link_close(); ?>

    <div class="ope-woo-card-content">

      <?php woocommerce_template_loop_product_link_open(); ?>

        <div class="ope-woo-card-content-section ope-woo-card-content-title">
          <h3 class="ope-card-product-tile"><?php the_title(); ?></h3>
        </div>

        <div class="ope-woo-card-content-section ope-woo-card-content-rating">
            <?php woocommerce_template_loop_rating(); ?>
        </div>

      <?php woocommerce_template_loop_product_link_close(); ?>

      <div class="ope-woo-card-content-section ope-woo-card-content-categories">
          <?php echo wc_get_product_category_list($product->get_id()); ?>
      </div>

    </div>

    <div class="ope-woo-card-footer">

      <?php if ($price_html = $product->get_price_html()) : ?>
      <div class="ope-woo-card-price">
         <span class="price"><?php echo $price_html; ?></a>
      </div>
      <?php endif; ?>

      <a href="<?php echo($product->add_to_cart_url()); ?>" data-product_id="<?php echo($product->get_id()); ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" rel="nofollow">
        <i class="mdi mdi-cart"></i>
      </a>

    </div>


  </div>
</li>
