<?php
/**
 * WooCommerce Group Buy Shortcodes
 *
 */

class WC_Shortcode_groupbuy extends WC_Shortcodes {

		public function __construct() {
				// Regular shortcodes
				add_shortcode( 'groupbuys', array( $this, 'groupbuys' ) );
				add_shortcode( 'recent_groupbuys', array( $this, 'recent_groupbuys' ) );
				add_shortcode( 'featured_groupbuys', array( $this, 'featured_groupbuys' ) );
        add_shortcode( 'ending_soon_groupbuys', array( $this, 'ending_soon_groupbuys' ) );
        add_shortcode( 'future_groupbuys', array( $this, 'future_groupbuys' ) );
        add_shortcode( 'popular_groupbuys', array( $this, 'popular_groupbuys' ) );
        add_shortcode( 'my_groupbuys', array( $this, 'my_groupbuys' ) );
			}
	/**
	 * Output featured products
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function featured_groupbuys( $atts ) {

		global $woocommerce_loop;

		extract(shortcode_atts(array(
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' => 'date',
			'order' => 'desc'
		), $atts));

		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'orderby' => $orderby,
			'order' => $order,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
			'is_groupbuy_archive' => TRUE,
			
		);

		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$args['meta_query'][] = array(
				'key' => '_featured',
				'value' => 'yes'
			);
			$args['meta_query'][] = array(
					'key' => '_visibility',
					'value' => array('catalog', 'visible'),
					'compare' => 'IN'
				);
		} else {
			$args['tax_query'][] = array(
     				'taxonomy' => 'product_visibility',
    				'field'    => 'name',
    				'terms'    => 'featured',
    			);	
		}


		ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

	/**
	 * Recent groupbuy shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function recent_groupbuys( $atts ) {

		global $woocommerce_loop, $woocommerce;

		extract(shortcode_atts(array(
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' => 'date',
			'order' => 'desc'
		), $atts));

		$meta_query = $woocommerce->query->get_meta_query();

		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'orderby' => $orderby,
			'order' => $order,
			'meta_query' => $meta_query,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
			'is_groupbuy_archive' => TRUE
		);

		ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
	   <?php else : ?>
            <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

/**
	 * List multiple groupbuys shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function groupbuys( $atts ) {
		global $woocommerce_loop;

	  	if (empty($atts)) return;

		extract(shortcode_atts(array(
			'columns' 	=> '4',
		  	'orderby'   => 'title',
		  	'order'     => 'asc'
			), $atts));

	  	$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => -1,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
			'is_groupbuy_archive' => TRUE,
			
		);



		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$args['meta_query'][] = array(
					'key' => '_visibility',
					'value' => array('catalog', 'visible'),
					'compare' => 'IN'
				);
		} else {
			$product_visibility_terms  = wc_get_product_visibility_term_ids();
			$product_visibility_not_in = $product_visibility_terms['exclude-from-catalog'];
			if ( ! empty( $product_visibility_not_in ) ) {
						$tax_query[] = array(
							'taxonomy' => 'product_visibility',
							'field'    => 'term_taxonomy_id',
							'terms'    => $product_visibility_not_in,
							'operator' => 'NOT IN',
						);
					}
			
		}

		if(isset($atts['skus'])){
			$skus = explode(',', $atts['skus']);
		  	$skus = array_map('trim', $skus);
	    	$args['meta_query'][] = array(
	      		'key' 		=> '_sku',
	      		'value' 	=> $skus,
	      		'compare' 	=> 'IN'
	    	);
	  	}

		if(isset($atts['ids'])){
			$ids = explode(',', $atts['ids']);
		  	$ids = array_map('trim', $ids);
	    	$args['post__in'] = $ids;
		}

	  	ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
	   <?php else : ?>
        <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

    /**
	 * Recent groupbuy shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function ending_soon_groupbuys( $atts ) {

		global $woocommerce_loop, $woocommerce;

		extract(shortcode_atts(array(
			'per_page' 	=> '12',
			'columns' 	=> '4',

			'order' => 'desc'
		), $atts));

		$meta_query = $woocommerce->query->get_meta_query();
        $meta_query []= array(
                                'key'     => '_groupbuy_closed',
                                'compare' => 'NOT EXISTS',
                        );
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'orderby' => 'meta_value',
			'order' => $order,
			'meta_query' => $meta_query,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
            'meta_key' => '_groupbuy_dates_to',
			'is_groupbuy_archive' => TRUE
		);

		ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
	   <?php else : ?>
            <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}
	   /**
	 * Recent groupbuy shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function future_groupbuys( $atts ) {

		global $woocommerce_loop, $woocommerce;

		extract(shortcode_atts(array(
			'per_page' 	=> '12',
			'columns' 	=> '4',

			'order' => 'desc'
		), $atts));

		$meta_query = $woocommerce->query->get_meta_query();
        $meta_query []= array(
                            'relation' => 'OR', 
                            array(
                                'key'     => '_groupbuy_closed',
                                'value'   => null,
                                'compare' => 'NOT',
                            ),
                            array(
                                'key'     => '_groupbuy_closed',
                                'compare' => 'NOT EXISTS',
                            )
                        );

        $meta_query []=  array( 'key' => '_groupbuy_started',
						            'value'=> '0',);
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'orderby' => 'meta_value',
			'order' => $order,
			'meta_query' => $meta_query,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
            'meta_key' => '_groupbuy_dates_to',
			'is_groupbuy_archive' => TRUE,
			'show_future_groupbuys' => TRUE
		);



		ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
	   <?php else : ?>
            <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

	 /**
	 * Popular groupbuy shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public function popular_groupbuys( $atts ) {

		global $woocommerce_loop, $woocommerce;


		extract(shortcode_atts(array(
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'order' => 'desc'
		), $atts));

		$meta_query = $woocommerce->query->get_meta_query();
    $meta_query []= array(
                                'key'     => '_groupbuy_closed',
                                'compare' => 'NOT EXISTS',
                        );

		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'orderby' => 'meta_value_num',
			'order' => $order,
			'meta_query' => $meta_query,
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'groupbuy')),
            'meta_key' => '_groupbuy_participants_count',
			'is_groupbuy_archive' => TRUE
		);

		ob_start();

		$products = new WP_Query( $args );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
	   <?php else : ?>
            <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

	/**
	 * Output shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return void
     *
	 */
	public static function my_groupbuys( $atts ) {

		global $woocommerce, $wpdb;

		if ( ! is_user_logged_in() ) return;

			$user_id  = get_current_user_id();
			$postids = array();
			$usergroupbuy	 = $wpdb->get_results("SELECT DISTINCT groupbuy_id FROM ".$wpdb->prefix."wc_groupbuy_log WHERE userid = $user_id",ARRAY_N );


			if(isset($usergroupbuy) && !empty($usergroupbuy)) {
				foreach ($usergroupbuy as $groupbuy) {
					$postids []= $groupbuy[0];
				}
			}
			?>

			<div class="wc-groupbuys active-groupbuys clearfix woocommerce">

				<h2><?php _e( 'Active Group Buy Deals', 'wc_groupbuy' ); ?></h2>

				<?php

				$args = array(
					'post__in'            => $postids,
					'post_type'           => 'product',
					'posts_per_page'      => '-1',
					'order'               => 'ASC',
					'orderby'             => 'meta_value',
					'tax_query'           => array(
						array(
						'taxonomy'            => 'product_type',
						'field'               => 'slug',
						'terms'               => 'groupbuy'
						)
					),
					'meta_query'          => array(
						array(
                           
                                'key'     => '_groupbuy_closed',
                                'compare' => 'NOT EXISTS',
                        )
					),
					'is_groupbuy_archive' => TRUE,
					'show_past_groupbuys' => FALSE,
				);


				$activeloop = new WP_Query( $args );

				if ( $activeloop->have_posts() && !empty($postids) ) {
				    woocommerce_product_loop_start();
					while ( $activeloop->have_posts() ):$activeloop->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					woocommerce_product_loop_end();

				} else {
						_e('<p class="no-active-groupbuy">You are not participating in group buy deal(s).</p>',"wc_groupbuy" );
				}

				wp_reset_postdata();

				?>
			</div>
			<div class="wc-groupbuys active-groupbuys clearfix woocommerce"  >
				<h2><?php _e( 'Won Group Buy Deals', 'wc_groupbuy' ); ?></h2>

				<?php

				$args = array(

					'post_type' 					=> 'product',
					'posts_per_page' 			=> '-1',
                    'order'			=> 'ASC',
                    'orderby'		=> 'meta_value',
                    'meta_key' 	=> '_groupbuy_dates_to',
					'meta_query' => array(
					       array(
					           'key' => '_groupbuy_closed',
					           'value' => '2',
					           'compare' => 'IN'
					       ),
					         array(
					           'key' => '_groupbuy_participant_id',
					           'value' => $user_id,
					       )
					   ),
					'show_past_groupbuys' 	=>  TRUE,
					'is_groupbuy_archive' 	=> TRUE,
				);

				$winningloop = new WP_Query( $args );

				if ( $winningloop->have_posts() && !empty($postids) ) {
				       woocommerce_product_loop_start();
					while ( $winningloop->have_posts()): $winningloop->the_post() ;
						wc_get_template_part( 'content', 'product' );
					endwhile;
				        woocommerce_product_loop_end();
				} else {
					_e('<p class="no-winned-groupbuy">You did not win any group buy deals yet.</p>',"wc_groupbuy" );
				}

				wp_reset_postdata();
				echo "</div>";

				}
}