<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
  wp_dequeue_style( 'storefront-style' );
  wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

require 'inc/product.php';
require 'inc/campaign-page.php';


/**
 * Remove breadcrumbs for Storefront theme
 */
add_action( 'init', 'wc_remove_storefront_breadcrumbs');

function wc_remove_storefront_breadcrumbs() {
  remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}

/**
 * Remove search for Storefront theme
 */

add_action( 'init', 'jk_remove_storefront_header_search' );
function jk_remove_storefront_header_search() {
  remove_action( 'storefront_header', 'storefront_product_search', 40 ); 
}

function storefront_credit() {
  ?>
  <div class="site-info">
    © Blood Records Ltd 2019
  </div>
  <?php
}

function storefront_header_container() {
  echo '<div class="col-full site-branding-and-search-wrapper">';
}


/**
 * add product info to campaign collection page
 */


add_action( 'woocommerce_after_shop_loop_item', 'woo_show_excerpt_shop_page', 5 );


/**
 * Body Class for Page Slug
 **/


function add_slug_body_class( $classes ) {
  global $post;
  $classes[] = $post->post_type . '-slug-' . $post->post_name;
  return $classes;
}

add_filter( 'body_class', 'add_slug_body_class'); 

add_action( 'woocommerce_before_single_product_summary', 'bbloomer_display_acf_field_under_images', 30 );

/**
 * add js to site
 */
function blood_records_enqueue_scripts() {
  wp_enqueue_script( 'my-script', get_template_directory_uri() . '/../storefront-child-theme-master/assets/js/index.js', array(), true );
}

add_action( 'wp_enqueue_scripts', 'blood_records_enqueue_scripts' );

/**
 * remove content from product page
 */


remove_action( 'woocommerce_single_product_summary', 'woocommerce-main-images', 20 ); 
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );        


// add_action( 'woocommerce_before_single_product_summary', 'woocommerce-main-images', 20);
// add_action( 'woocommerce_before_single_product_summary', 'woocommerce-main-image', 20 );
// add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_thumbnails', 2 ); 
// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
// add_action( 'woocommerce_single_product_summary', 'the_content', 20 );
// add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
// remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );          
// remove_action( 'woocommerce_single_product_summary', 'WooCommerce_Product_Subtitle' );          
// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );         
// add_action( 'woocommerce_single_product_summary', 'woocommerce_variable_add_to_cart', 30 );
// add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 0 );          
// add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_price', 2 );          
// add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_images', 0 );           
// add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 1 );           
// add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 2 );          
// add_action( 'woocommerce_single_product_summary', 'the_content', 20 );
// 