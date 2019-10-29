<?php

/**
 * add product details to product page
 */

function bbloomer_display_acf_field_under_images() {
  ?>

  <div class="single-product-details-wrapper">

    <!-- image, artist and title -->
    
    <div  class="above-the-fold-product-wrapper">
      <div class="single-product-main-image above-the-fold-image"><img src=<?php echo get_field('main_image'); ?>></div>
      <div class="single-product-title-wrapper">
        <div class= "single-product-details product-artist"><?php echo get_field('artist'); ?></div>
        <div class= "single-product-details product-title"><?php echo get_field('title'); ?></div>
      </div>
    </div><!-- above-the-fold-product-wrapper -->
    <div class="center-text"><?php echo get_field('artist'); ?>: <?php echo get_field('title'); ?></div>
    <hr>
    
    <!-- gallery, product info and cart in middle section -->
    
    <div class="middle-section">
      <div class= "left-side single-product-details product-artist-title">
        <?php wc_get_template( 'single-product/product-image.php' ); ?>
      </div>
      <div class="right-side">
        <div class= "single-product-details product-introduction">
          <h3><?php echo get_field('artist'); ?>: <?php echo get_field('title'); ?></h3>
          <?php wc_get_template( 'single-product/price.php' );?>
          <?php echo get_field('product_info'); ?>
        </div>
        <div class="single-product-add-to-cart"><?php wc_get_template( 'single-product/add-to-cart/variation-add-to-cart-button.php' ); ?>
      </div>
    </div>  
  </div><!-- middle-section -->

  <!-- description, metrics and tracklist in lower third of page -->

  <div class="lower-third">
    <div class="left-side product-description">
      <h3>Description</h3>

      <?php echo get_field('product_introduction'); ?>
      <?php echo get_field('further_description'); ?> 
    </div>
    <div class="right-side">
      <div class="single-product-metrics"><h3>Sales</h3><?php
      ?> 
    </div>

    <div class=tracklist><h3>Tracklist</h3>
      <?php
      echo get_field('tracklist');?>
    </div>
  </div>
</div><!-- lower-third -->
</div><!-- single-product-details-wrapper end -->
<?php
}

