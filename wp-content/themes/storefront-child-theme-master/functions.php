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



/**
 * Remove breadcrumbs for Storefront theme
 */
add_action( 'init', 'wc_remove_storefront_breadcrumbs');

function wc_remove_storefront_breadcrumbs() {
  remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
}

function storefront_credit() {
    ?>
    <div class="site-info">
        © Blood Records Ltd 2019
    </div><!-- .site-info -->
    <?php
}

function storefront_header_container() {
  echo '<div class="col-full site-branding-and-search-wrapper">';
}

add_action( 'woocommerce_after_shop_loop_item', 'woo_show_excerpt_shop_page', 5 );
function woo_show_excerpt_shop_page() {
  global $product;
  echo $product->get_description();
 
    ?>
  <div class= "get-price-and-date"><span>£<?php echo $product->get_price() ; ?></span><?php  echo $product->get_date_on_sale_to(); ?></div>
  <?php
    ?>
  <div class= "get-date"></div>
  <?php
  // echo $product->post->post_excerpt;
}


// function blood_records_scripts() {

//   wp_enqueue_script(
//       'jquery_script', // name your script so that you can attach other scripts and de-register, etc.
//       get_template_directory_uri() . '/assets/js/index.js', // this is the location of your script file
//       array('jquery'), // this array lists the scripts upon which your script depends
//   );
  
  
// }


/**
 * Enqueue a script
 */
function myprefix_enqueue_scripts() {
    wp_enqueue_script( 'my-script', get_template_directory_uri() . '/../storefront-child-theme-master/assets/js/index.js', array(), true );
}
add_action( 'wp_enqueue_scripts', 'myprefix_enqueue_scripts' );

// add_action( 'wp_enqueue_scripts', 'blood_records_scripts' );

