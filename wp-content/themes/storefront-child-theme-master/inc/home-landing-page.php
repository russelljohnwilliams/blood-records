<?php

/**
 * add product details to product page
 */

function get_home_page() {
  ?>

  <div class="home-details-wrapper">
    <!-- ............................................................................. -->
    <div class="first-section-wrapper introduction-wrapper home-section-wrappers">
      <div class="home-page-left-side intro-left-side">
        <?php echo get_field('introduction'); ?>
        <a class='sign-up-button' href=<?php echo get_field('sign_up_button'); ?>>Sign Up</a>
      </div>
      <!-- ............................................................................. -->
      <div class="home-page-right-side intro-right-side">
        <div class="intro-image"><img src=<?php echo get_field('introduction_image'); ?>></div>
        <div class="rotate"><?php echo get_field('sidebar_text'); ?></div>
      </div>
    </div><!-- end of first-section-wrapper -->
    <!-- ............................................................................. -->

    <div class="second-section-wrapper how-it-works-wrapper home-section-wrappers">
      <div class="all-four-sections-wrapper">
        <div class="how-first-col how-it-works-col"><?php echo get_field('heres_how_first_box'); ?></div>
        <div class="how-second-col how-it-works-col"><?php echo get_field('sign_up_second_box'); ?></div>
        <div class="how-third-col how-it-works-col"><?php echo get_field('pre_order_third_box'); ?></div>
        <div class="how-fourth-col how-it-works-col"><?php echo get_field('press_ship_fourth_box'); ?></div>
      </div>
      <!-- ............................................................................. -->

      <div class="sign-up-button-wrapper">
        <a class='sign-up-button' href=<?php echo get_field('sign_up_button'); ?>>Sign Up</a>
      </div>  
    </div><!-- end of second-section-wrapper -->
    <!-- ............................................................................. -->
    <div class="third-section-wrapper products-section-wrapper home-section-wrappers">
      <div class="third-section-header"><h3>Available for pre-order now:</h3></div> 
      <!-- display products here --><?php echo do_shortcode('[products limit="3" columns="3" category="test" cat_operator="AND"]'); ?>
    </div><!-- end of third-section-wrapper -->
    <!-- ............................................................................. -->
    <div class="fourth-section-wrapper about-us-wrapper home-section-wrappers">
      <div class="home-page-left-side about-left-side">
        <div class="about_us_sidebar_text"> <?php echo get_field('about_us_sidebar_text'); ?></div> 
        <div class="about-us-image"><img src=<?php echo get_field('about_us_image'); ?>></div>
      </div>
      <!-- ............................................................................. -->
      <div class="home-page-right-side about-right-side ">
        <div class="about-us-text-wrapper"><?php echo get_field('about_us_text'); ?>
        <div class="about-us-sign-up-button-wrapper"><a class='sign-up-button' href=<?php echo get_field('sign_up_button'); ?>>Sign Up</a></div>
      </div>
    </div>
  </div><!-- end of fourth-section-wrapper -->
  <!-- ............................................................................. -->

  <div class="fifth-section-wrapper footer-image-wrapper home-section-wrappers">
    <div class="footer-image"><img src=<?php echo get_field('full_screen_footer_image'); ?>></div>

  </div><!-- end of fifth-section-wrapper -->



  </div><!-- home-details-wrapper end -->
  <?php
}

