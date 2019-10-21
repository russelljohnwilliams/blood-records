<?php

/*
Plugin Name: CIO Custom Fields for Woo
Plugin URI: http://vipp.com.au/cio-custom-fields-importer/custom-fields-woocommerce-customers
Description: No code required. Add custom fields to WooCommerce Customers at My Account registration, check out, user profile and my account page. Premium version can do much more.
Author: <a href="http://vipp.com.au">VisualData</a>
Version: 1.0.2

*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );


//register activation hook to run the function once when the plugin is activated.

register_activation_hook( __FILE__, array('VippCustomFieldsWoocommerceCustomer','cio_custom_fields_wc_activate' ) );


/* gather information about all active plugins, including network activated and subsite activated plugins. 
 * get_site_option returns an array with plugin file as key, get_option returns an array with plugin name in array value.
 */ 
if (is_multisite()) {

	$cio_cfwc_active_plugins = array_merge(array_keys(get_site_option('active_sitewide_plugins', array())), get_option( 'active_plugins', array()));
	
} else {

	$cio_cfwc_active_plugins = get_option( 'active_plugins', array());
}

	

 /**
 * Check if WooCommerce is active
 **/ 
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $cio_cfwc_active_plugins ) ) ) {

	
	$cio_cus_fields_wc = new VippCustomFieldsWoocommerceCustomer();
	
	$cio_cus_fields_wc->run();


}

if (class_exists('VippCustomFieldsWoocommerceCustomer')) return;

class VippCustomFieldsWoocommerceCustomer {

	

	function run() {
	
		//display custom fields in registration form
		add_action( 'woocommerce_register_form', array($this, 'cio_extra_register_fields') );
		
		//save custom fields from registration page
		add_action( 'woocommerce_created_customer', array($this,'cio_save_extra_register_fields') );
	
		//add custom fields to checkout page
		add_filter( 'woocommerce_checkout_fields' , array($this,'cio_custom_checkout_fields') );
	
		//add custom fields to user profile page at back end
		add_filter( 'woocommerce_customer_meta_fields' , array($this,'cio_custom_profile_fields') );
	
		//retrieve value for added custom fields if users have created an account and logged in.
		
		add_filter( 'woocommerce_checkout_get_value' , array($this,'cio_get_current_user_checkout_value'), 10, 2 );
	
	

	}
	
	
	/**
	 * retrieves custom field value if user has an account and has logged in.
	 * this function is added to filter woocommerce_checkout_get_value
	*/
	function cio_get_current_user_checkout_value ($param=null, $field_name) {
	
		$user_pod_id = $this->find_post_id_by_slug('user');

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

		if ($fields_array and is_user_logged_in() and array_key_exists($field_name, $fields_array ) ) {
		
			return get_user_meta(get_current_user_id(), $field_name, true); 
			//return $value = $pod_current_user->field($field_name);
			
		}
		
	
	}
	
	
	
		
	//this function runs when the plugin is activated. It creates default custom fields under user pod so users can delete unwanted fields. 
	static function cio_custom_fields_wc_activate() {
	
			//default fields array, used to insert custom post type _pods_field as children of extended user pod.
	
			 $cio_default_fields_wc  = array(
	
				'cio_section_contact' => array('post_title'  => 'Customer Contact',),
				
				'billing_first_name' 	=> array('post_title'  => 'First Name',),
				'billing_last_name' 	=> array('post_title'  => 'Last Name',),
				'billing_email' 		=> array('post_title'  => 'Billing Email',),
				'billing_phone' 		=> array('post_title'  => 'Phone',),
				'billing_mobile' 		=> array('post_title'  => 'Mobile Phone',),
			
				'cio_end_section_account' => array('post_title'  => 'Account Details',),
				
				'cio_section_billing' => array('post_title'  => 'Billing Details',),
				
				'billing_company' 		=> array('post_title'  => 'Company',),
				'billing_address_1' 	=> array('post_title'  => 'Address Line 1',),
				'billing_address_2' 	=> array('post_title'  => 'Address Line 2',),
				'billing_city' 			=> array('post_title'  => 'Suburb',),
				'billing_postcode' 		=> array('post_title'  => 'Post Code',),
				
				'cio_section_shipping'=> array('post_title'  => 'Shipping Details',),
				
				'shipping_first_name' 	=> array('post_title'  => 'First Name',),
				'shipping_last_name' 	=> array('post_title'  => 'Last Name',),
				'shipping_company' 		=> array('post_title'  => 'Company',),
				'shipping_address_1' 	=> array('post_title'  => 'Address Line 1',),
				'shipping_address_2' 	=> array('post_title'  => 'Address Line 2',),
				'shipping_city' 		=> array('post_title'  => 'Suburb',),
				'shipping_postcode' 	=> array('post_title'  => 'Post Code',),
				'shipping_phone' 		=> array('post_title'  => 'Phone',),
				'shipping_mobile' 		=> array('post_title'  => 'Mobile Phone',),							
				
				'cio_section_others1'=> array('post_title'  => 'Additional Details',),
			);

		
			//check whether the wp content type "user" has been extended, or custom post _pods_pod user has been inserted already.
			$user_pod_id = self::find_post_id_by_slug('user');
	
			if (!$user_pod_id) {
			//insert user as custom post type _pods_pod if record is not found. 
			
				$user_post = array(
					'post_content'   => 'This custom post type (_pods_pod) is used to customise fields of WooCommerce customers at registration, check out and user profile update. Custom fields are stored as custom post type _pods_field, children posts of this post. ',
					'post_name'      => 'user',
					'post_title'     => 'User',
					'post_status'    => 'publish',
					'post_type'      => '_pods_pod',
					'post_author'    => 1,
			
			
			
				);
		
				$new_user_post_id = wp_insert_post($user_post);
			
				//$user_pod_meta = get_post_meta($user_pod_id);
			
			
				update_post_meta($new_user_post_id, 'type', 'user');
				update_post_meta($new_user_post_id, 'storage', 'meta');
				update_post_meta($new_user_post_id, 'object', 'user');
				update_post_meta($new_user_post_id, 'old_name', 'user');
		
		
				//user may be using pods and has extended user already. 
		
				$fields_array = self::find_children_by_parent_post_id($new_user_post_id);
		
				if (!$fields_array) {
			
					$menu_order = 0;
				
					foreach ($cio_default_fields_wc as $field => $v) {
				
						//if no matching post exists, construct the array to insert fields as custom post types
						//the user may be using billing_ and shipping_ post name. the following code skips if a post with the same name exists.
						if (!self::find_post_id_by_slug($field)) { 
					
					
							$field_post = array(
								'post_content'   => '',
								'post_name'      => $field,
								'post_title'     => $v['post_title'],
								'post_status'    => 'publish',
								'post_type'      => '_pods_field',
								'post_parent'	 => $new_user_post_id,
								'menu_order'  	 => $menu_order,
								'post_author'    => 1,
			
			
							);
							$field_id = wp_insert_post($field_post);
							
							//cio_section_ fields should be hidden from users
							if (stristr($field,'cio_section_') or stristr($field,'cio_end_section_') ) {
								update_post_meta($field_id, 'hidden', 1);
							}
							
							$menu_order +=1;
					
						}
			
					}
			
				}
	
		
			}
		}	
	
	
	//find post id by name (slug)
	static function find_post_id_by_slug($slug) {
		
				$slug = sanitize_title_for_query($slug);
			
				global $wpdb;
			
				$table_prefix = $wpdb->get_blog_prefix();
		
				$post_id = $wpdb->get_var( "
					SELECT  ID 
					FROM ". $table_prefix ."posts
					WHERE post_name='". $slug . "' 
					AND post_status='publish' 
					AND post_type='_pods_pod'
				
					" );
		
				if ($post_id) {
			
					return $post_id; 
				} else {
			
					return false;
			
				}
			
			}

	//find post children by parent id. returns array of objects sorted by menu order, accending.
	static function find_children_by_parent_post_id($id) {

		$id = intval($id);
	
		global $wpdb;
	
		$table_prefix = $wpdb->get_blog_prefix();
	
		$fields_array=array();

		$posts_array = $wpdb->get_results( "
			SELECT  ID, post_name, post_title, post_content 
			FROM ". $table_prefix ."posts 
			WHERE post_parent=" . $id . " 
			AND post_status='publish' 
			AND post_type='_pods_field'
			ORDER BY menu_order ASC

			" );
		//be careful, get_post_meta returns multi dimensional array. 
		if ($posts_array) {
			foreach ($posts_array as $v) {
				$field_post_meta = get_post_meta($v->ID);
				$fields_array[$v->post_name] = array_merge($field_post_meta, array(
					'post_title' => $v->post_title,
					'description' => $v->post_content,
			
				));
		
			}
	
		}
	
	
	
		return $fields_array;
	
	}
	
	/**
	 * Add new register fields for WooCommerce registration. 
	 * 
	 * @return string Register fields HTML.
	 */
	function cio_extra_register_fields() {
	
		//$woocommerce_calc_shipping is "yes" or "no" stored in option table
		$woocommerce_calc_shipping = get_option('woocommerce_calc_shipping');

		$billing_section_to_label = true;
		$shipping_section_to_label = true;
		
		//this is required so if customers name contains ' and are not successful in submitting the form, \\ is added to the form causing confusion. 
		$_POST = wp_unslash($_POST);

		$user_pod_id = $this->find_post_id_by_slug('user');

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

		$form_code = '';
		
		if ($fields_array) {
		
			
			//$field is the field name and $v is an associative array with keys being post_title and meta keys from postmeta table
			foreach ($fields_array as $field=>$v) {
				
				
				if (stristr($field, 'shipping_') and 'no' == $woocommerce_calc_shipping ) {
					continue;
				}
				
				//billing_email is silimar to email field and confusing to customers in registration form. it is provided for localisation in checkout page
				if (in_array($field, array('billing_email',) )) {
					continue;
				}
				
				//section names are hidden from users, output a few section name only
				if (stristr($field,'cio_section_') or stristr($field,'cio_end_section_') ){
		
					//label contact information 
					if (stristr($field, 'cio_section_contact') ) {
				
						
						$form_code .= '<div class="cio-reg-sec cio-reg-sec-' . str_replace('_', '-', $field) .'">'. $v['post_title']  .'</div>';
						continue;
						
					}
		
						//label billing section once to avoid confusing users, as the fields share the same names.
					else if (stristr($field, 'cio_section_billing') ) {
				
						
						$form_code .= '<div class="cio-reg-sec cio-reg-sec-' . str_replace('_', '-', $field) .'">'. $v['post_title']  .'</div>';
						continue;
						
					}
				
						//label shipping section once to avoid confusing users, as the fields share the same names
					else if (stristr($field, 'cio_section_shipping')  and 'yes'==$woocommerce_calc_shipping) {
						$form_code .= '<div  class="cio-reg-sec cio-reg-sec-' . str_replace('_', '-', $field) .'">'.  $v['post_title']  .'</div>';
						continue;
					}
					else if (stristr($field, 'cio_section_others1') ) {
						$form_code .= '<div  class="cio-reg-sec cio-reg-sec-' . str_replace('_', '-', $field) .'">'.  $v['post_title']  .'</div>';
						continue;
				
					}
					else { continue;}
					
				} 
				
				
				
				
				
				$form_code .= '<div class="cio-reg-row cio-reg-row-' . str_replace('_', '-', $field) . '"';
				$form_code .= '<p class="form-row form-row-wide">';
				$form_code .= '<div class="cio-reg-label cio-reg-label-' . str_replace('_', '-', $field)  . ' "><label for="reg_' . $field . '">' . $v['post_title'] . ' </label></div>';
				if ( ! empty( $_POST[$field] ) ) {
					$value =  esc_attr( $_POST[$field] );
				} else {
					$value = '';
				}
				
				$form_code .= '<input type="text" class="input-text cio-reg-input cio-reg-input-' . str_replace('_', '-', $field)  .'" name="' . $field . '" id="reg_'. $field  .'" value="' . $value .'" />';
				$form_code .= '</p>';
				$form_code .= '</div>'; //div added since version 1.0.2
			}
		
		
		}
		echo $form_code;

	}


	/**
	 * Save the custom fields from registration page to user meta table.
	 * 
	 * @param  int  $customer_id Current customer ID.
	 *
	 * @return void
	 */
	function cio_save_extra_register_fields( $customer_id ) {

		$_POST = wp_unslash($_POST); //remove backslashes from the submitted form data.
		
		$user_pod_id = $this->find_post_id_by_slug('user');

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);
		
		$wc_fields_billing = array(
			
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_postcode',
			'billing_country',
			'billing_state',
			'billing_email',
			'billing_phone',

		);
		
		
		$wc_fields_shipping = array(
			
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_postcode',
			'shipping_country',
			'shipping_state',
			

		);
	
		
		
		
		//section name and hidden fields are excluded from registration page already. 
		if ($fields_array) {
		
			foreach ($fields_array as $field=>$v) {
				
				//default fields are saved by wc already
				/*
				if (in_array($field, $wc_fields_billing) or in_array($field,$wc_fields_shipping)) {
					continue;
				}
				*/
				
				//the user name and password should not be stored in meta, even if these fields are created in pods.
				if (in_array($field, array('account_username', 'account_password', 'account_password-2', 'email', 'username', 'password'))) {
				
					continue;
				}
					
				
			
				if ( isset( $_POST[$field]) ) {

					
					update_user_meta( $customer_id, $field, sanitize_text_field( $_POST[$field] ) );
				}
			
	
			}
		}
		 

	}

	/**
	 * Add custom fields to check out page filter, wc saves extra fields automatically.
	 * 
	 * @param  array  $wc_fields check out fields
	 *
	 * @return filtered checked out fields
	 */
	function cio_custom_checkout_fields($wc_fields) {
	
		$user_pod_id = $this->find_post_id_by_slug('user');

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

		$wc_fields_billing = array(
			
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_postcode',
			'billing_country',
			'billing_state',
			'billing_email',
			'billing_phone',

		);
		
		
		$wc_fields_shipping = array(
			
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_postcode',
			'shipping_country',
			'shipping_state',
			

		);
		

	
		if ($fields_array) {
		
			
			//$field is the field name and $v is an associative array with keys being post_title and meta keys from postmeta table
			foreach ($fields_array as $field=>$v) {
				
				
			
		
				//section names are hidden from users
				if (stristr($field,'cio_section_') or stristr($field,'cio_end_section_') ) {
					continue;
				}
			
				
				if (stristr($field, 'billing_') ) {
				
					if (in_array($field, $wc_fields_billing)) {
					
						$wc_fields['billing'][$field]['label'] = $v['post_title'];
							
					
					} else {
					
						$wc_fields['billing'][$field] = array(
							'label'     => $v['post_title'],
							'class'     => array('form-row-wide', 'cio-checkout-'.$field),
							'label_class' => array('cio-label-'.$field),
							'clear'     => true,
							);
					}
					
					
				
				} else if (stristr($field, 'shipping_') ) {
				
					if (in_array($field, $wc_fields_shipping)) {
						
							$wc_fields['shipping'][$field]['label'] = $v['post_title'];
					} else {
				
				
						$wc_fields['shipping'][$field] = array(
							'label'     => $v['post_title'],
							'class'     => array('form-row-wide', 'cio-checkout-'.$field),
							'label_class' => array('cio-label-'.$field),
							'clear'     => true,
							);
					}
				
				}
				

			}
	
		}
		
		return $wc_fields;
	
	}
	
	
	/**
	 * Add custom fields to backend user profile page filter, wc saves extra fields automatically.
	 * Note if pods is activated, pods uses the same meta fields and overrides wc. 
	 * @param  array  $wc_fields backend profile fields
	 *
	 * @return filtered backend profile fields
	 */
	function cio_custom_profile_fields($wc_fields) {
	
		$user_pod_id = $this->find_post_id_by_slug('user');

		$fields_array = $this->find_children_by_parent_post_id($user_pod_id);

		$wc_fields_billing = array(
			
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_postcode',
			'billing_country',
			'billing_state',
			'billing_email',
			'billing_phone',

		);
		
		
		$wc_fields_shipping = array(
			
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_postcode',
			'shipping_country',
			'shipping_state',
			

		);
		
		global $cio_cfwc_active_plugins;
		

	
		if ($fields_array) {
		
			
			//$field is the field name and $v is an associative array with keys being post_title and meta keys from postmeta table
			foreach ($fields_array as $field=>$v) {
				

			
		
				//section names are hidden from users
				if (stristr($field,'cio_section_') or stristr($field,'cio_end_section_') ) {
					continue;
				}
			
				
				if (stristr($field, 'billing_') ) {
				
					//avoid duplicate fields under pods and wc
				
					if (in_array('pods/init.php', $cio_cfwc_active_plugins)) {
				
						unset($wc_fields['billing']['fields'][$field]);
						continue;
				
					}
				
					if (in_array($field, $wc_fields_billing)) {
					
						$wc_fields['billing']['fields'][$field]['label'] = $v['post_title'];
							
					
					} else {
					
						$wc_fields['billing']['fields'][$field] = array(
							'label'     	=> $v['post_title'],
							'description'   => $v['description'],
							);
					}
					
					
				
				} else {
				
					//avoid duplicate fields under pods and wc
				
					if (in_array('pods/init.php', $cio_cfwc_active_plugins)) {
				
						unset($wc_fields['shipping']['fields'][$field]);
						continue;
				
					}
				
					if (in_array($field, $wc_fields_shipping)) {
					
						
							$wc_fields['shipping']['fields'][$field]['label'] = $v['post_title'];
					
					} else {
				
				
						$wc_fields['shipping']['fields'][$field] = array(
							'label'     	=> $v['post_title'],
							'description'   => $v['description'],
							);
					}
				
				}
				

			}
	
		}
		
		return $wc_fields;
	
	}


}

?>