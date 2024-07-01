<?php
// error_reporting(0);
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

if ( !class_exists( 'FME_PGISFW_FRONT_MAIN' ) ) { 
	class FME_PGISFW_FRONT_MAIN { 
		public function __construct() { 

			add_action('wp_head', array( $this, 'fme_pgisfw_remove_woo_hooks' ));
			add_action('wp_enqueue_scripts', array( $this, 'fme_pgisfw_enqueue_scripts' ));               
		}
		public function fme_pgisfw_remove_woo_hooks() {

			global $post;
			if (is_product() || has_shortcode($post->post_content, 'product_page')) {
				$product_id;
				if (has_shortcode($post->post_content, 'product_page')) {
					$regex = get_shortcode_regex( array( 'product_page' ) );

					preg_match_all( "/{$regex}/s", get_the_content(), $matches );

					$product_id = explode('"', $matches[3][0])[1];
				}
				if ( empty($product_id) ) {
					$product_id = get_the_ID();
				}

				$fme_pgisfw_is_available = false;
				$fme_pgisfw_terms = get_the_terms ( $product_id, 'product_cat' );
				$fme_pgisfw_parent_id = $fme_pgisfw_terms[0]->parent;
				$fme_pgisfw_category_id = $fme_pgisfw_terms[0]->term_id;

				$fme_pgisfw_general_settings = get_option('fme_pgisfw_general_settings');
				$fme_pgisfw_thumbnail_settings = get_option('fme_pgisfw_thumbnail_settings');
				$fme_pgisfw_arrows_settings = get_option('fme_pgisfw_arrows_settings');
				$fme_pgisfw_lightbox_settings = get_option('fme_pgisfw_lightbox_settings');
				$fme_pgisfw_zoom_settings = get_option('fme_pgisfw_zoom_settings');

				$fme_pgisfw_settings = array_merge($fme_pgisfw_general_settings, $fme_pgisfw_thumbnail_settings);
				$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_arrows_settings);
				$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_lightbox_settings);
				$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_zoom_settings);


				/***************Rules array check **********/
				$selected_rules_array = array();
				$all_rules_array=get_option('fme_pgisfw_save_rule_settings');
				//select all rules possible
				if (isset($all_rules_array) && !empty($all_rules_array)) {
					foreach ($all_rules_array as  $key=>$rule) {
						
						if ('fme_pgisfw_product'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable'] ) {

							if ('' == $rule['fme_pgisfw_selected_pc']) {
								//if selected products are empty then rule will apply on all products       
								$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
							}

							foreach ($rule['fme_pgisfw_selected_pc'] as $prod=> $prodid ) {
								if ($product_id==$prodid) {
									if (!empty($selected_rules_array)) {

										if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
											$selected_rules_array=$rule;
										}
									} else {
										$selected_rules_array=$rule;
									}

								}
							}
						} else if ('fme_pgisfw_category'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
							if ('' == $rule['fme_pgisfw_selected_pc']) {
										//if selected products are empty then rule will apply on all products       
										$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
							}
							foreach ($rule['fme_pgisfw_selected_pc'] as $cat=> $catid ) {
								if ($fme_pgisfw_category_id==$catid) {
									if (!empty($selected_rules_array)) {

										if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
											$selected_rules_array=$rule;
										}
									} else {
										$selected_rules_array=$rule;
									}
								}
							}
						} else if (''==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
						
							if (!empty($selected_rules_array)) {

								if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
									$selected_rules_array=$rule;
								}
							} else {
								$selected_rules_array=$rule;
							}

						
						}


					}
				}
				if (!empty($selected_rules_array)) {
					$fme_pgisfw_settings=$selected_rules_array;
				}


				/*******************End rule check array***********************/

				if ( isset($fme_pgisfw_settings['fme_pgisfw_enable_disable']) && 'yes' == $fme_pgisfw_settings['fme_pgisfw_enable_disable']) {                      

					if ( '' == $fme_pgisfw_settings['fme_pgisfw_selected_pc']) {
						$fme_pgisfw_is_available = true;
					} else if ( 'fme_pgisfw_product' == $fme_pgisfw_settings['fmeproductcategory'] ) {
						if (in_array($product_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc'])) {
							$fme_pgisfw_is_available = true;
						}
					} else if ( 'fme_pgisfw_category' == $fme_pgisfw_settings['fmeproductcategory'] ) {
						if (in_array($fme_pgisfw_category_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc']) || in_array($fme_pgisfw_parent_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc'])) {
							$fme_pgisfw_is_available = true;
						}
					}
				}

				if ( $fme_pgisfw_is_available ) {   


						add_filter('woocommerce_single_product_image_thumbnail_html', array( $this, 'RemoveDefaultWooCommerceGallery' ), 10);

						add_action('woocommerce_product_thumbnails', array( $this, 'fme_pgisfw_show_product_image' ), 20);
						$current_theme = wp_get_theme();
					if ('Flatsome' == $current_theme) {
						remove_action('flatsome_product_image_tools_bottom', 'flatsome_product_video_button');
						remove_action('flatsome_product_image_tools_bottom', 'flatsome_product_lightbox_button');
						echo '<script>
							jQuery(document).ready(function(){
								jQuery(".zoom-button").hide();
								});
							</script>';
					}       
				} 
			}
		}

		public function RemoveDefaultWooCommerceGallery( $html ) {
		
			remove_filter('woocommerce_single_product_image_thumbnail_html', 'RemoveDefaultWooCommerceGallery', 10 );        
			return ( '' );
		}
		
		public function fme_pgisfw_enqueue_scripts() {

			if (!is_admin()) {
				global $post;
				
				if (class_exists( 'WooCommerce' ) && ( is_product() || has_shortcode( $post->post_content, 'product_page') ) ) {

					wp_enqueue_style('slick_css', plugins_url('js/slick/slick.css', __FILE__), '1.0.8', true);
					wp_enqueue_style('slick_css_theme', plugins_url('js/slick/slick-theme.css', __FILE__), '1.0', true);
					// wp_enqueue_script('jquery');

					wp_enqueue_script('fme_pgisfw_slick_js', plugins_url('js/slick/slick.js', __FILE__), array( 'jquery' ), '1.0.8', true);


					$product_id;
					if (has_shortcode($post->post_content, 'product_page')) {
						$regex = get_shortcode_regex( array( 'product_page' ) );

						preg_match_all( "/{$regex}/s", get_the_content(), $matches );

						$product_id = explode('"', $matches[3][0])[1];
					}
					if ( empty($product_id) ) {
						$product_id = get_the_ID();
					}


					$fme_pgisfw_is_available = false;
					$fme_pgisfw_terms = get_the_terms ( $product_id, 'product_cat' );                   
					$fme_pgisfw_parent_id = $fme_pgisfw_terms[0]->parent;
					$fme_pgisfw_category_id = $fme_pgisfw_terms[0]->term_id;
					// $fme_pgisfw_settings = get_option('fme_pgisfw_settings');

					$fme_pgisfw_general_settings = get_option('fme_pgisfw_general_settings');
					$fme_pgisfw_thumbnail_settings = get_option('fme_pgisfw_thumbnail_settings');
					$fme_pgisfw_arrows_settings = get_option('fme_pgisfw_arrows_settings');
					$fme_pgisfw_lightbox_settings = get_option('fme_pgisfw_lightbox_settings');
					$fme_pgisfw_zoom_settings = get_option('fme_pgisfw_zoom_settings');

					$fme_pgisfw_settings = array_merge($fme_pgisfw_general_settings, $fme_pgisfw_thumbnail_settings);
					$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_arrows_settings);
					$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_lightbox_settings);
					$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_zoom_settings);

									/***************Rules array check **********/
					$selected_rules_array = array();
					$all_rules_array=get_option('fme_pgisfw_save_rule_settings');
					//select all rules possible
					if (isset($all_rules_array) && !empty($all_rules_array)) {
						foreach ($all_rules_array as  $key=>$rule) {

							if ('fme_pgisfw_product'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable'] ) {
								if ('' == $rule['fme_pgisfw_selected_pc']) {
									// $products = wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) );
									$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();


								}
								foreach ($rule['fme_pgisfw_selected_pc'] as $prod=> $prodid ) {
									if ($product_id==$prodid) {
										if (!empty($selected_rules_array)) {

											if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
												$selected_rules_array=$rule;
											}
										} else {
											$selected_rules_array=$rule;
										}

									}
								}
							} else if ('fme_pgisfw_category'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {

								if ('' == $rule['fme_pgisfw_selected_pc']) {
										//if selected products are empty then rule will apply on all products       
										$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
								}
								foreach ($rule['fme_pgisfw_selected_pc'] as $cat=> $catid ) {
									if ($fme_pgisfw_category_id==$catid) {
										if (!empty($selected_rules_array)) {

											if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
												$selected_rules_array=$rule;
											}
										} else {
											$selected_rules_array=$rule;
										}
									}
								}
							} else if (''==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
						
								if (!empty($selected_rules_array)) {

									if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
										$selected_rules_array=$rule;
									}
								} else {
									$selected_rules_array=$rule;
								}

						
							}


						}
					}
					if (!empty($selected_rules_array)) {
						$fme_pgisfw_settings=$selected_rules_array;
					}
				
					/********************End rule array check**********************/
					
					if ( isset($fme_pgisfw_settings['fme_pgisfw_enable_disable']) && 'yes' == $fme_pgisfw_settings['fme_pgisfw_enable_disable']) {                      

						if ( '' == $fme_pgisfw_settings['fme_pgisfw_selected_pc'] ) {
							$fme_pgisfw_is_available = true;
						} else if ( 'fme_pgisfw_product' == $fme_pgisfw_settings['fmeproductcategory'] ) {
							if (in_array($product_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc'])) {
								$fme_pgisfw_is_available = true;
							}
						} else if ( 'fme_pgisfw_category' == $fme_pgisfw_settings['fmeproductcategory'] ) {
							if (in_array($fme_pgisfw_category_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc']) || in_array($fme_pgisfw_parent_id, $fme_pgisfw_settings['fme_pgisfw_selected_pc']) ) {
								$fme_pgisfw_is_available = true;
							}
						}
					}

					if ( $fme_pgisfw_is_available ) {

						wp_enqueue_style('splide_css', plugins_url('css/splide.min.css', __FILE__), '1.0.8', true);
						wp_enqueue_style('fme_pgifw_css', plugins_url('css/fme_pgifw.css', __FILE__), '1.0.8', true);
						wp_enqueue_style('fme_pgifw_glightbox_css', plugins_url('css/glightbox.min.css', __FILE__), '1.0.8', true);
						$fme_color=$fme_pgisfw_settings['fme_pgisfw_border_color'];
						$fme_inline_style =' #secondary-slider  .is-active {border:1px solid ' . $fme_color . ' !important;
					}';
						wp_add_inline_style( 'fme_pgifw_css', $fme_inline_style );
						wp_enqueue_style('fme_pgisfw_font_awsome_css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', '1.0', true);
						wp_enqueue_script('fme_pgisfw_splide_js', plugins_url('js/splide.min.js', __FILE__), array( 'jquery' ), '1.0.8', true);

						wp_enqueue_script('fme_pgisfw_glightbox_js', plugins_url('js/glightbox.min.js', __FILE__), array( 'jquery' ), '1.0.8', true);
						wp_register_script('fme_pgifw', plugins_url('js/fme_pgifw.js', __FILE__), array( 'jquery' ), '1.1.3', true);
						wp_enqueue_script('fme_pgisfw', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js', array( 'jquery' ), '1.0', true);
						wp_enqueue_script('fme_blowup', plugins_url('js/blowup.js', __FILE__), array( 'jquery' ), '1.0.8', true);


						$fme_pgisfw_my_options = get_option('fme_pgisfw_settings');


						$fme_pgisfw_general_settings = get_option('fme_pgisfw_general_settings');
						$fme_pgisfw_thumbnail_settings = get_option('fme_pgisfw_thumbnail_settings');
						$fme_pgisfw_arrows_settings = get_option('fme_pgisfw_arrows_settings');
						$fme_pgisfw_lightbox_settings = get_option('fme_pgisfw_lightbox_settings');
						$fme_pgisfw_zoom_settings = get_option('fme_pgisfw_zoom_settings');
						$fme_pgisfw_bullets_settings = get_option('fme_pgisfw_bullets_settings');

						$fme_pgisfw_my_options = array_merge($fme_pgisfw_general_settings, $fme_pgisfw_thumbnail_settings);
						$fme_pgisfw_my_options = array_merge($fme_pgisfw_my_options, $fme_pgisfw_arrows_settings);
						$fme_pgisfw_my_options = array_merge($fme_pgisfw_my_options, $fme_pgisfw_lightbox_settings);
						$fme_pgisfw_my_options = array_merge($fme_pgisfw_my_options, $fme_pgisfw_zoom_settings);
						$fme_pgisfw_my_options = array_merge($fme_pgisfw_my_options, $fme_pgisfw_bullets_settings);


						/***************Rules array check **********/
						$selected_rules_array = array();
						$all_rules_array=get_option('fme_pgisfw_save_rule_settings');
						//select all rules possible
						if (isset($all_rules_array) && !empty($all_rules_array)) {
							foreach ($all_rules_array as  $key=>$rule) {

								if ('fme_pgisfw_product'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable'] ) {

									if ('' == $rule['fme_pgisfw_selected_pc']) {
										//if selected products are empty then rule will apply on all products       
										$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
									}

									foreach ($rule['fme_pgisfw_selected_pc'] as $prod=> $prodid ) {
										if ($product_id==$prodid) {
											if (!empty($selected_rules_array)) {

												if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
													$selected_rules_array=$rule;
												}
											} else {
												$selected_rules_array=$rule;
											}

										}
									}
								} else if ('fme_pgisfw_category'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
									if ('' == $rule['fme_pgisfw_selected_pc']) {
										//if selected products are empty then rule will apply on all products       
										$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
									}
									foreach ($rule['fme_pgisfw_selected_pc'] as $cat=> $catid ) {
										if ($fme_pgisfw_category_id==$catid) {
											if (!empty($selected_rules_array)) {

												if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
													$selected_rules_array=$rule;
												}
											} else {
												$selected_rules_array=$rule;
											}
										}
									}
								} else if (''==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
						
									if (!empty($selected_rules_array)) {

										if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
											$selected_rules_array=$rule;
										}
									} else {
										$selected_rules_array=$rule;
									}

						
								}


							}
						}
						if (!empty($selected_rules_array)) {
							$fme_pgisfw_my_options=$selected_rules_array;
						}
				

						/******************End rule check array************************/
						
						$fme_pgisfw_settings_array = array(
							'fme_pgisfw_slider_mode'        => isset( $fme_pgisfw_my_options['fme_pgisfw_slider_mode'] )  ? $fme_pgisfw_my_options['fme_pgisfw_slider_mode'] : 'loop',
							'fme_pgisfw_slider_layout'      => isset( $fme_pgisfw_my_options['fme_pgisfw_slider_layout'] )  ? $fme_pgisfw_my_options['fme_pgisfw_slider_layout'] : '',
							'fme_pgisfw_navigation_icon_status'=> isset( $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_status'] )  ? $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_status'] : '',
							'fme_pgisfw_image_aspect_ratio' => isset($fme_pgisfw_my_options['fme_pgisfw_image_aspect_ratio'])? $fme_pgisfw_my_options['fme_pgisfw_image_aspect_ratio'] : 'off',
							'fme_auto_play'=> isset( $fme_pgisfw_my_options['fme_auto_play'] )  ? $fme_pgisfw_my_options['fme_auto_play'] : '',
							'fme_thumbnails_to_show'=> isset( $fme_pgisfw_my_options['fme_thumbnails_to_show'] )  ? $fme_pgisfw_my_options['fme_thumbnails_to_show'] : '',
							'fme_pgisfw_thumbnail_images_style' => isset( $fme_pgisfw_my_options['fme_pgisfw_slider_images_style'] )  ? $fme_pgisfw_my_options['fme_pgisfw_slider_images_style'] : '',
							'fme_auto_play_timeout'=> isset( $fme_pgisfw_my_options['fme_auto_play_timeout'] )  ? $fme_pgisfw_my_options['fme_auto_play_timeout'] : '',
							'fme_pgisfw_hide_thumbnails'=> isset( $fme_pgisfw_my_options['fme_pgisfw_hide_thumbnails'] )  ? $fme_pgisfw_my_options['fme_pgisfw_hide_thumbnails'] : '',
							'fme_pgisfw_show_lightbox'=> isset( $fme_pgisfw_my_options['fme_pgisfw_show_lightbox'] )  ? $fme_pgisfw_my_options['fme_pgisfw_show_lightbox'] : '',
							'fme_pgisfw_show_zoom'=> isset( $fme_pgisfw_my_options['fme_pgisfw_show_zoom'] )  ? $fme_pgisfw_my_options['fme_pgisfw_show_zoom'] : '',
							'fme_pgisfw_icon_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_icon_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_icon_color'] : '',
							'fme_pgisfw_Lightbox_frame_width'=> isset( $fme_pgisfw_my_options['fme_pgisfw_Lightbox_frame_width'] )  ? $fme_pgisfw_my_options['fme_pgisfw_Lightbox_frame_width'] : '',
							'fme_pgisfw_navigation_background_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_navigation_background_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_navigation_background_color'] : '',
							'fme_pgisfw_zoombox_frame_width'=> isset( $fme_pgisfw_my_options['fme_pgisfw_zoombox_frame_width'] )  ? $fme_pgisfw_my_options['fme_pgisfw_zoombox_frame_width'] : '',
							'fme_pgisfw_zoombox_frame_height'=> isset( $fme_pgisfw_my_options['fme_pgisfw_zoombox_frame_height'] )  ? $fme_pgisfw_my_options['fme_pgisfw_zoombox_frame_height'] : '',
							'fme_selected_image'=> isset( $fme_pgisfw_my_options['fme_selected_image'] )  ? $fme_pgisfw_my_options['fme_selected_image'] : '',
							'fme_pgisfw_navigation_icon_show_on'=> isset( $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_show_on'] )  ? $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_show_on'] : '',
							'fme_pgisfw_navigation_hover_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_navigation_hover_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_navigation_hover_color'] : '',
							'fme_pgisfw_navigation_icon_shape'=> isset( $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_shape'] )  ? $fme_pgisfw_my_options['fme_pgisfw_navigation_icon_shape'] : '',
							'fme_pgisfw_lightbox_bg_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_lightbox_bg_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_lightbox_bg_color'] : '',
							'fme_pgisfw_lightbox_bg_hover_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_lightbox_bg_hover_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_lightbox_bg_hover_color'] : '',
							'fme_pgisfw_lightbox_icon_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_lightbox_icon_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_lightbox_icon_color'] : '',
							'fme_pgisfw_show_bullets'=> isset( $fme_pgisfw_my_options['fme_pgisfw_show_bullets'] )  ? $fme_pgisfw_my_options['fme_pgisfw_show_bullets'] : '',
							'fme_pgisfw_bullets_shape'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_shape'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_shape'] : '',
							'fme_pgisfw_bullets_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_color'] : '',
							'fme_pgisfw_bullets_hover_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_hover_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_hover_color'] : '',
							'fme_pgisfw_bullets_position'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_position'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_position'] : '',
							'fme_pgisfw_bullets_thumbnail'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_thumbnail'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_thumbnail'] : '',
							'fme_pgisfw_bullets_inside_position'=> isset( $fme_pgisfw_my_options['fme_pgisfw_bullets_inside_position'] )  ? $fme_pgisfw_my_options['fme_pgisfw_bullets_inside_position'] : '',
							'fme_pgifw_admin_url'=> admin_url('admin-ajax.php'),
							'fme_pgisfw_lightbox_slide_effect'=> isset( $fme_pgisfw_my_options['fme_pgisfw_lightbox_slide_effect'] )  ? $fme_pgisfw_my_options['fme_pgisfw_lightbox_slide_effect'] : '',
							'fme_pgisfw_counter_bullets_font_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_counter_bullets_font_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_counter_bullets_font_color'] : '#000000',
							'fme_pgisfw_numbering_enable_disable'=> isset( $fme_pgisfw_my_options['fme_pgisfw_numbering_enable_disable'] )  ? $fme_pgisfw_my_options['fme_pgisfw_numbering_enable_disable'] : 'no',
							'fme_pgisfw_numbering_color'=> isset( $fme_pgisfw_my_options['fme_pgisfw_numbering_color'] )  ? $fme_pgisfw_my_options['fme_pgisfw_numbering_color'] : '#000000',

						);
						wp_localize_script('fme_pgifw', 'fme_pgisfw_settings_data', $fme_pgisfw_settings_array);
						wp_enqueue_script('fme_pgifw');

						wp_localize_script('fme_blowup', 'fme_zoombox', $fme_pgisfw_zoom_settings);


					}
				}
			}
		}
		public function fme_pgisfw_get_all_products() {

			$all_product_ids=array();
				$products = wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) );
			foreach ($products as $key => $product) {
				array_push($all_product_ids, $product->get_id());
				// echo $product->get_id();

			}
				return $all_product_ids;
		}
		public function fme_pgisfw_show_product_image() {

			global $post, $product, $woocommerce; 

			$fme_pgisfw_version = '3.0';
			echo '<div class="fme_images" >';
			if (version_compare($woocommerce->version, $fme_pgisfw_version, '>=' )) {
				$fme_pgisfw_attachment_ids = $product->get_gallery_image_ids();
			} else {
				$fme_pgisfw_attachment_ids = $product->get_gallery_attachment_ids();
			}
			$product_id=get_the_ID();
			$fme_pgisfw_terms = get_the_terms ( $product_id, 'product_cat' );                   
			$fme_pgisfw_parent_id = $fme_pgisfw_terms[0]->parent;
			$fme_pgisfw_category_id = $fme_pgisfw_terms[0]->term_id;

			$fme_pgisfw_image_link       = wp_get_attachment_url(get_post_thumbnail_id());
			$fme_pgisfw_product_video_link = get_post_meta(get_the_ID(), 'fme_pgisfw_video_urls', true);  

			if ($product->is_type( 'variable' )) {
				$variations = $product->get_available_variations();
				foreach ( $variations as $variation ) {
					if (!in_array($variation['image_id'], $fme_pgisfw_attachment_ids)) {
						array_push($fme_pgisfw_attachment_ids, $variation['image_id']);
					}
				}
			}


			$fme_pgisfw_general_settings = get_option('fme_pgisfw_general_settings');
			$fme_pgisfw_thumbnail_settings = get_option('fme_pgisfw_thumbnail_settings');
			$fme_pgisfw_arrows_settings = get_option('fme_pgisfw_arrows_settings');
			$fme_pgisfw_lightbox_settings = get_option('fme_pgisfw_lightbox_settings');
			$fme_pgisfw_zoom_settings = get_option('fme_pgisfw_zoom_settings');

			$fme_pgisfw_settings = array_merge($fme_pgisfw_general_settings, $fme_pgisfw_thumbnail_settings);
			$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_arrows_settings);
			$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_lightbox_settings);
			$fme_pgisfw_settings = array_merge($fme_pgisfw_settings, $fme_pgisfw_zoom_settings);

			$fme_lightbox_icons_array = array( 'expand-arrows-alt', 'expand-alt', 'search-plus', 'expand' );


			$fme_lightbox_icons = $fme_lightbox_icons_array[$fme_pgisfw_settings['fme_selected_lightbox_image']];


				/***************Rules array check **********/
				$selected_rules_array = array();
				$all_rules_array=get_option('fme_pgisfw_save_rule_settings');
				//select all rules possible
			if (isset($all_rules_array) && !empty($all_rules_array)) {  
				foreach ($all_rules_array as  $key=>$rule) {

					if ('fme_pgisfw_product'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable'] ) {
						if ('' == $rule['fme_pgisfw_selected_pc']) {
								//if selected products are empty then rule will apply on all products       
								$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
						}
						foreach ($rule['fme_pgisfw_selected_pc'] as $prod=> $prodid ) {
							if ($product_id==$prodid) {
								if (!empty($selected_rules_array)) {

									if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
										$selected_rules_array=$rule;
									}
								} else {
									$selected_rules_array=$rule;
								}

							}
						}
					} else if ('fme_pgisfw_category'==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {

						if ('' == $rule['fme_pgisfw_selected_pc']) {
										//if selected products are empty then rule will apply on all products       
										$rule['fme_pgisfw_selected_pc']=$this->fme_pgisfw_get_all_products();
						}
						foreach ($rule['fme_pgisfw_selected_pc'] as $cat=> $catid ) {
							if ($fme_pgisfw_category_id==$catid) {
								if (!empty($selected_rules_array)) {

									if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
										$selected_rules_array=$rule;
									}
								} else {
									$selected_rules_array=$rule;
								}
							}
						}
					} else if (''==$rule['fmeproductcategory'] && 'yes'==$rule['fme_pgisfw_enable_disable']) {
						
						if (!empty($selected_rules_array)) {

							if ( intval( $selected_rules_array['fme_pgisfw_rule_priority']) < intval($rule['fme_pgisfw_rule_priority'] ) ) {
								$selected_rules_array=$rule;
							}
						} else {
							$selected_rules_array=$rule;
						}

						
					}


				}
			}
			if (!empty($selected_rules_array)) {
				$fme_pgisfw_settings=$selected_rules_array;
				$fme_lightbox_icons = $fme_lightbox_icons_array[$fme_pgisfw_settings['fme_selected_lightbox_image']];
			}
				

				/******************End rule check array************************/


			$ab=-1;
			if ( 'left' == $fme_pgisfw_settings['fme_pgisfw_slider_layout'] ) {
				//haseeb changed
				$ftotal=count($fme_pgisfw_attachment_ids);
				if ( isset ( $fme_pgisfw_product_video_link ) && '' != $fme_pgisfw_product_video_link ) {
					$total_vid=count($fme_pgisfw_product_video_link);
					$ftotal=$ftotal+$total_vid; 
				
				}
				$fi=1;
				$ftotal++;
				?>
				<div id="secondary-slider" class="splide" style="
				<?php
				if ( 'yes' == $fme_pgisfw_settings['fme_pgisfw_hide_thumbnails']) {
					echo 'display : none;'; } 
				?>
			">
			<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='image' value='<?php echo esc_attr($fme_pgisfw_image_link); ?>'>
			<div class="splide__track" >
				<ul class="splide__list">
					<li class="splide__slide">
						<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_image_link); ?>">
					</li>
					<?php
					if ( isset ($fme_pgisfw_product_video_link) && '' != $fme_pgisfw_product_video_link ) {
						foreach ($fme_pgisfw_product_video_link as $key => $fme_pgisfw_value) {
							if ( false !== strpos($fme_pgisfw_value, 'https://www.youtube.com/watch?v=') ) {
								$fme_pgifw_youtube_url = $fme_pgisfw_value;
								$fme_pgifw_youtube_url = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
								$fme_pgisfw_thumbnail = str_replace('https://www.youtube.com/watch?v=', '', $fme_pgisfw_value);
								$fme_pgisfw_value = 'http://img.youtube.com/vi/' . $fme_pgisfw_thumbnail . '/default.jpg';

								?>
								<li class="splide__slide">
									<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgifw_youtube_url); ?>" style="visibility: hidden;">
									<div class="tc_video_slide">
										<img width="70" height="70" style="width: auto; height: auto;" src="<?php echo esc_attr($fme_pgisfw_value); ?>"
										>
									</div>
								</li>
								<?php
							} elseif (false !== strpos( $fme_pgisfw_value, 'vimeo' )) {
													$regs = array();
											$id   = '';
								if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $fme_pgisfw_value, $regs ) ) {
									$id = $regs[3];
								}
											$fme_pgisfw_values = 'https://player.vimeo.com/video/' . $id;
													$fmefvg_video_viemo_url = (int) filter_var( $fme_pgisfw_value, FILTER_SANITIZE_NUMBER_INT );
										$videoId                = $fmefvg_video_viemo_url;
										$vimeourl               = $fme_pgisfw_value;
										$image              = $this->get_vimeo_thumbnail($vimeourl);
										
										
								?>
										<li class="splide__slide">
														<img class="fme_pgifw_image_url_check" src="<?php echo esc_url($fme_pgisfw_values); ?>" style="visibility: hidden;">
														<div class="tc_video_slide">
															<img width="70" height="70" style="width: auto; height: auto;" src="<?php echo esc_url($image); ?>"
															>
														</div>
													</li>

										<?php

							} else {
								?>
								<li class="splide__slide">
									<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_value); ?>" style="visibility: hidden;">
									<div class="tc_video_slide">
										<video style="object-fit: fill; width: auto;" width="70" height="70" >
											<source src="<?php echo esc_attr($fme_pgisfw_value); ?>" type="video/mp4">
											</video>
										</div>
									</li>
								   <?php
							}
						}
					}
					?>
						<?php


						foreach ($fme_pgisfw_attachment_ids as $attachment_id) {
							$fme_pgisfw_imgfull_src = wp_get_attachment_image_src($attachment_id, 'full');
							?>
							<li class="splide__slide">
								<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr ($fme_pgisfw_imgfull_src[0]); ?>">
							</li>
						<?php } ?>
					</ul>
				</div>
			</div>
			
			<div class="primary-slider">
				<div class="splide__track">
					<ul class="splide__list">
						<li class="splide__slide primary_splide">
							<span class="fme-slider-numbering">
							<?php 
							echo '1/' . filter_var($ftotal);
							$fi++; 
							?>
							</span>
							<div class="magnify">
								
								<img class="fme_small fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_image_link); ?>" />
								<?php
								if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox'] ) {
									?>
									<?php $ab=$ab+1; ?>
									<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
								<?php } ?>
							</div>
						</li>
						<?php
						if ( isset ($fme_pgisfw_product_video_link) && '' != $fme_pgisfw_product_video_link  ) {
							foreach ($fme_pgisfw_product_video_link as $key => $fme_pgisfw_value) { 
								if ( false !== strpos ($fme_pgisfw_value, 'https://www.youtube.com/watch?v=') ) {
									$fme_pgifw_youtube_url = $fme_pgisfw_value;
									$fme_pgifw_youtube_url = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
									$fme_vid_url = $fme_pgisfw_value;
									$fme_pgisfw_value = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
									$fme_pgisfw_value = $fme_pgisfw_value . '?enablejsapi=1&version=3';

									?>
									<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='video' value='<?php echo esc_attr($fme_vid_url); ?>'>
									<li class="splide__slide">
										<span class="fme-slider-numbering">
										<?php 
										echo filter_var($fi) . '/' . filter_var($ftotal);
										$fi++; 
										?>
										</span>
										<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgifw_youtube_url); ?>" style="visibility: hidden;">
										<div class="tc_video_slide tc_video_slide_primary">
											<?php
											if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
												?>
												<?php $ab=$ab+1; ?>
												<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
											<?php } ?>
											<iframe  class="fme_video_size" src="<?php echo esc_attr($fme_pgisfw_value); ?>"
												frameborder="0" allowfullscreen></iframe>
											</div>
										</li>
										<?php
								} elseif (false !== strpos( $fme_pgisfw_value, 'vimeo' ) ) {
										$regs = array();
											$id   = '';
									if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $fme_pgisfw_value, $regs ) ) {
										$id = $regs[3];
									}
											$fme_pgisfw_values = 'https://player.vimeo.com/video/' . $id;
									?>
											<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='video' value='<?php echo esc_url($fme_pgisfw_values); ?>'>
									<li class="splide__slide">
										<span class="fme-slider-numbering">
										<?php 
										echo filter_var($fi) . '/' . filter_var($ftotal);
										$fi++; 
										?>
										</span>
										<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgifw_youtube_url); ?>" style="visibility: hidden;">
										<div class="tc_video_slide tc_video_slide_primary">
											<?php
											if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
												?>
												<?php $ab=$ab+1; ?>
												<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
											<?php } ?>
											<iframe  class="fme_video_size"  data-src="<?php echo esc_url( $fme_pgisfw_values ); ?>" src="<?php echo esc_url($fme_pgisfw_values) . '?api=1'; ?>"
												frameborder="0" allowfullscreen></iframe>
											</div>
										</li>


											<?php


								} else {
									?>
										<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='video' value='<?php echo esc_attr($fme_pgisfw_value); ?>'>
										<li class="splide__slide primary_splide">
											<span class="fme-slider-numbering">
											<?php 
											echo filter_var($fi) . '/' . filter_var($ftotal);
											$fi++; 
											?>
											</span>
											<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_value); ?>" style="visibility: hidden;">
											<div class="tc_video_slide tc_video_slide_primary">
												<video style="object-fit: fill;" class="fme_video_size" controls>
													<source src="<?php echo esc_attr($fme_pgisfw_value); ?>" type="video/mp4">
														Your browser does not support the video tag.
													</video>
													<?php
													if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
														?>
														<?php $ab=$ab+1; ?>
														<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
													<?php } ?>
												</div>
											</li>
											<?php
								}
							}
						}
						?>
								<?php
								foreach ($fme_pgisfw_attachment_ids as $attachment_id) {
									$fme_pgisfw_imgfull_src = wp_get_attachment_image_src($attachment_id, 'full' );
									?>
									<input type="hidden" name="fme_all_urls[]" url_type='image' class="fme_url_field" value='<?php echo esc_attr($fme_pgisfw_imgfull_src[0]); ?>'>
									<li class="splide__slide primary_splide">
										<span class="fme-slider-numbering">
										<?php 
										echo filter_var($fi) . '/' . filter_var($ftotal);
										$fi++; 
										?>
										</span>
										<div class="magnify">
											
											<img class="fme_small fme_pgifw_image_url_check" style="object-fit: cover;" src="<?php echo esc_attr($fme_pgisfw_imgfull_src[0]); ?>" />
											<?php
											if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox'] ) {
												?>
												<?php $ab=$ab+1; ?>
												<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
											<?php } ?>
										</div>
									</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					
					<?php
			} else {
				//haseeb changed

				$ftotal=count($fme_pgisfw_attachment_ids);
				if ( isset ( $fme_pgisfw_product_video_link ) && '' != $fme_pgisfw_product_video_link ) {
					$total_vid=count($fme_pgisfw_product_video_link);
					$ftotal=$ftotal+$total_vid; 
				}
				$fi=1;
				$ftotal++;
				?>
					<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='image' value='<?php echo esc_attr($fme_pgisfw_image_link); ?>'>
					<div class="primary-slider">
						<div class="splide__track">
							<ul class="splide__list">
								<li class="splide__slide primary_splide">
									<span class="fme-slider-numbering">
									<?php 
									echo '1/' . filter_var($ftotal);
									$fi++; 
									?>
									</span>
									<div class="magnify">
										
										<img class="fme_small fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_image_link); ?>" />
										<?php
										if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox'] ) {
											?>
											<?php $ab=$ab+1; ?>
											<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
										<?php } ?>
									</div>
								</li>
								<?php
								
								if ( isset ( $fme_pgisfw_product_video_link ) && '' != $fme_pgisfw_product_video_link ) {
									foreach ($fme_pgisfw_product_video_link as $key => $fme_pgisfw_value) {
										
										if ( false !== strpos($fme_pgisfw_value, 'https://www.youtube.com/watch?v=') ) {
											$fme_pgifw_youtube_url = $fme_pgisfw_value;
											$fme_pgifw_youtube_url = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
											$fme_vid_url = $fme_pgisfw_value;
											$fme_pgisfw_value = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
											$fme_pgisfw_value = $fme_pgisfw_value . '?enablejsapi=1&version=3';
											?>
											<input type="hidden" name="fme_all_urls[]" url_type='video' class="fme_url_field" value='<?php echo esc_attr($fme_vid_url); ?>'>
											<li class="splide__slide">
												<span class="fme-slider-numbering">
												<?php 
												echo filter_var($fi) . '/' . filter_var($ftotal);
												$fi++; 
												?>
												</span>
												<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgifw_youtube_url); ?>" style="visibility: hidden;">
												<div class="tc_video_slide tc_video_slide_primary">
													<?php
													if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
														?>
														<?php $ab=$ab+1; ?>
														<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
													<?php } ?>
													<iframe class="fme_video_size" src="<?php echo esc_attr($fme_pgisfw_value); ?>"
														frameborder="0" allowfullscreen></iframe>
													</div>
												</li>
												<?php
										} elseif (false !== strpos( $fme_pgisfw_value, 'vimeo' ) ) {
										$regs = array();
											$id   = '';
											if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $fme_pgisfw_value, $regs ) ) {
												$id = $regs[3];
											}
											$fme_pgisfw_values = 'https://player.vimeo.com/video/' . $id;
											?>
											<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='video' value='<?php echo esc_url($fme_pgisfw_values); ?>'>
									<li class="splide__slide">
										<span class="fme-slider-numbering">
										<?php 
										echo filter_var($fi) . '/' . filter_var($ftotal);
										$fi++; 
											?>
										</span>
										<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_values); ?>" style="visibility: hidden;">
										<div class="tc_video_slide tc_video_slide_primary">
											<?php
											if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
												?>
												<?php $ab=$ab+1; ?>
												<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
											<?php } ?>
											<iframe  class="fme_video_size"  data-src="<?php echo esc_url( $fme_pgisfw_values ); ?>" src="<?php echo esc_url($fme_pgisfw_values); ?>"
												frameborder="0" allowfullscreen></iframe>
											</div>
										</li>


											<?php


										} else {
											?>
												<input type="hidden" name="fme_all_urls[]" class="fme_url_field" url_type='video' value='<?php echo esc_attr($fme_pgisfw_value); ?>'>
												
												<li class="splide__slide">
													<span class="fme-slider-numbering">
													<?php 
													echo filter_var($fi) . '/' . filter_var($ftotal);
													$fi++; 
													?>
													</span>
													<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_value); ?>" style="visibility: hidden;">
													<div class="tc_video_slide tc_video_slide_primary">
														<video style="object-fit: fill;" class="fme_video_size" controls>
															<source src="<?php echo esc_attr($fme_pgisfw_value); ?>" type="video/mp4">
																Your browser does not support the video tag.
															</video>
															<?php
															if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
																?>
																<?php $ab=$ab+1; ?>
																<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
															<?php } ?>
														</div>
													</li>
													<?php
										}
									}
								}
								?>
										<?php
										
										
										foreach ($fme_pgisfw_attachment_ids as $attachment_id ) {
											$fme_pgisfw_imgfull_src = wp_get_attachment_image_src($attachment_id, 'full');
											?>
											<input type="hidden" name="fme_all_urls[]" url_type='image' class="fme_url_field" value='<?php echo esc_attr($fme_pgisfw_imgfull_src[0]); ?>'>
											
											<li class="splide__slide primary_splide">
												<span class="fme-slider-numbering">
												<?php 
												echo filter_var($fi) . '/' . filter_var($ftotal);
												$fi++; 
												?>
												</span>
												<div class="magnify">
													
													<img class="fme_small fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_imgfull_src[0]); ?>" />
													<?php
													if ( 'yes' === $fme_pgisfw_settings['fme_pgisfw_show_lightbox']) {
														?>
														<?php $ab=$ab+1; ?>
														<span class="fme_lightbox_icon <?php echo esc_attr(sanitize_text_field(wp_unslash($fme_pgisfw_settings['fme_pgisfw_lightbox_position']))); ?>" index='<?php echo esc_attr($ab); ?>'><i class="fme_lightbox_icons fa fa-<?php echo esc_attr(sanitize_text_field(wp_unslash($fme_lightbox_icons))); ?>"></i></span>
													<?php } ?>
												</div>
											</li>
										<?php } ?>
									</ul>
								</div>
							</div>

							<div id="secondary-slider" class="splide" style="
							<?php
							if ( 'yes' == $fme_pgisfw_settings['fme_pgisfw_hide_thumbnails'] ) {
								echo 'display : none;';
							}
							if ( ( !isset($fme_pgisfw_product_video_link ) || empty ( $fme_pgisfw_product_video_link ) ) && ( !isset($fme_pgisfw_attachment_ids ) || empty ( $fme_pgisfw_attachment_ids ) ) ) {
									echo 'display : none;'; 
							}

							?>
								">
								
								
								<div class="splide__track fme-font-slider" >
									<ul class="splide__list">
										<li class="splide__slide">
											<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_image_link); ?>">
										</li>
										<?php

										if ( isset($fme_pgisfw_product_video_link) && '' != $fme_pgisfw_product_video_link ) {

											foreach ($fme_pgisfw_product_video_link as $key => $fme_pgisfw_value) {
												if (  false !== strpos ($fme_pgisfw_value, 'https://www.youtube.com/watch?v=') ) {
													$fme_pgifw_youtube_url = $fme_pgisfw_value;
													$fme_pgifw_youtube_url = str_replace('watch?v=', 'embed/', $fme_pgisfw_value);
													$fme_pgisfw_thumbnail = str_replace('https://www.youtube.com/watch?v=', '', $fme_pgisfw_value);
													$fme_pgisfw_value = 'http://img.youtube.com/vi/' . $fme_pgisfw_thumbnail . '/default.jpg';
													?>
													<li class="splide__slide">
														<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgifw_youtube_url); ?>" style="visibility: hidden;">
														<!-- <i class="far fa-play-circle fa-lg fme_play"></i> -->
														<div class="tc_video_slide">
															<img width="70" height="70" style="width: auto; height: auto;" src="<?php echo esc_attr($fme_pgisfw_value); ?>"
															>
														</div>
													</li>
													<?php
												} elseif (false !== strpos( $fme_pgisfw_value, 'vimeo' )) {
													$regs = array();
											$id   = '';
													if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $fme_pgisfw_value, $regs ) ) {
														$id = $regs[3];
													}
											$fme_pgisfw_values = 'https://player.vimeo.com/video/' . $id;
													$fmefvg_video_viemo_url = (int) filter_var( $fme_pgisfw_value, FILTER_SANITIZE_NUMBER_INT );
										$videoId                = $fmefvg_video_viemo_url;
										$vimeourl               = $fme_pgisfw_value;
										$image              = $this->get_vimeo_thumbnail($vimeourl);
										
										
													?>
										<li class="splide__slide">
														<img class="fme_pgifw_image_url_check" src="<?php echo esc_url($fme_pgisfw_values); ?>" style="visibility: hidden;">
														<div class="tc_video_slide">
															<img width="70" height="70" style="width: auto; height: auto;" src="<?php echo esc_url($image); ?>"
															>
														</div>
													</li>

										<?php

												} else {
													?>
													<li class="splide__slide">
														<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_value); ?>" style="visibility: hidden;">
														<div class="tc_video_slide">
															<video style="object-fit: fill; width: auto;" width="70" height="70">
																<source src="<?php echo esc_attr($fme_pgisfw_value); ?>" type="video/mp4">
																</video>
															</div>
														</li>
														<?php
												}
											}
										}
										?>
											<?php
											foreach ($fme_pgisfw_attachment_ids as $attachment_id) {
												$fme_pgisfw_imgfull_src = wp_get_attachment_image_src($attachment_id, 'full');
												?>
												<li class="splide__slide">
													<img class="fme_pgifw_image_url_check" src="<?php echo esc_attr($fme_pgisfw_imgfull_src[0]); ?>">
												</li>
												
											<?php } ?>
										</ul>

									</div>
								</div>
								<?php
			}
							echo '</div>';
		}
		public function get_vimeo_thumbnail( $vimeourl ) {
											$curl                   = curl_init();
											curl_setopt_array(
											$curl,
											array(
												CURLOPT_URL =>  'https://vimeo.com/api/oembed.json?url=' . $vimeourl,
												CURLOPT_RETURNTRANSFER => true,
												CURLOPT_ENCODING => '',
												CURLOPT_MAXREDIRS => 10,
												CURLOPT_TIMEOUT => 30,
												CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
												CURLOPT_CUSTOMREQUEST => 'GET',
												CURLOPT_POSTFIELDS => '',
											)
										);

										$response = curl_exec( $curl );
										$err      = curl_error( $curl );
										$thumby   = json_decode( $response );
										curl_close( $curl );
										$thumbnail = $thumby->thumbnail_url;
										
										$thumbarr  = explode( '_', $thumbnail );
										
										$thumbnail =  $thumbarr[0]??'' ;
			if (!empty($thumbnail)) {
				$image     =  $thumbnail . '_640.jpg';
			} else {
				$image=FME_PGISFW_URL . 'front/img-placeholder.png';
			}
										return $image;
		}
	}
					new FME_PGISFW_FRONT_MAIN();
}
