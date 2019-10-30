<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package storefront
 */
require 'inc/home-landing-page.php';

get_header(); ?>

  <div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

      <?php

      if ( is_front_page() && is_home() ) {
      // Default homepage

      } elseif ( is_front_page()){
      // Static homepage
       echo get_home_page();

      } elseif ( is_home()){

      // Blog page

      } else {

      the_post();

      do_action( 'storefront_page_before' );

      get_template_part( 'content', 'page' );

      /**
       * Functions hooked in to storefront_page_after action
       *
       * @hooked storefront_display_comments - 10
       */
      do_action( 'storefront_page_after' );

      }
      // while ( have_posts() ) :
        

      // endwhile; // End of the loop.
      ?>

    </main><!-- #main -->
  </div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();
