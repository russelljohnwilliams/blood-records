<?php

function woo_show_excerpt_shop_page() {
  global $product;
  ?>
  <div class="loop-product-details-wrapper">
    <div class= "loop-product-details product-artist"><?php echo get_field('artist'); ?></div>
    <div class= "loop-product-details product-title"><?php echo get_field('title'); ?></div>
    
    <div class= "loop-product-details get-price-and-date"><span>£<?php echo $product->get_price() ; ?></span><?php  echo $product->get_date_on_sale_to(); ?></div>
    <div class= "loop-product-details product-intro"><?php echo get_field('product_introduction'); ?></div>

  </div>

  <?php
}