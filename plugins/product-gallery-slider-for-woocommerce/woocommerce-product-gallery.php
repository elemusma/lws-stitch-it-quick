<?php
/*
 * Plugin Name: Product Gallery Slider for WooCommerce
   Description: Enable attractive slider for product gallery images and add the videos in gallery.
   Author: FME Addons
   Text Domain: fme_pgisfw
   Domain Path:       /languages
   Version: 1.1.3
 * Woo: 7745351:fbd8212ebebc1e74edbce5369122a753
*/
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

if (! function_exists('is_plugin_active')) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php' ;
}

if ( !class_exists( 'FME_PGISFW_IMAGE_SLIDER_MAIN' ) ) {
	class FME_PGISFW_IMAGE_SLIDER_MAIN {
		public function __construct() {

					/**
 * Check if WooCommerce is active
 * if wooCommerce is not active ext Tabs module will not work.
 **/
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

				add_action( 'admin_notices', array( $this, 'FME_PGISFW_admin_notice' ) );
			}
			$this->FME_PGISFW_module_constants();
			
			if (is_admin()) {
				require_once FME_PGISFW_PLUGIN_DIR . 'admin/fme_pgifw_admin.php' ;
					register_activation_hook( __FILE__, array( $this, 'fme_pgisfw_install_default_settings' ) );
			} else {
				require_once FME_PGISFW_PLUGIN_DIR . 'front/fme_pgifw_front.php' ;
			}
			add_action( 'admin_init', array( $this, 'fme_pgifw_update_version' ));
			add_action ( 'wp_ajax_fme_pgisfw_check_variation_product_image', array( $this, 'fme_pgisfw_check_variation_product_image' ));
			add_action ( 'wp_ajax_nopriv_fme_pgisfw_check_variation_product_image', array( $this, 'fme_pgisfw_check_variation_product_image' ));
		}
		public function FME_PGISFW_admin_notice() {
				deactivate_plugins(__FILE__);
				$fme_pgisfw_allowed_tags = array(
				'a' => array(
					'class' => array(),
					'href'  => array(),
					'rel'   => array(),
					'title' => array(),
				),
				'abbr' => array(
					'title' => array(),
				),
				'b' => array(),
				'blockquote' => array(
					'cite'  => array(),
				),
				'cite' => array(
					'title' => array(),
				),
				'code' => array(),
				'del' => array(
					'datetime' => array(),
					'title' => array(),
				),
				'dd' => array(),
				'div' => array(
					'class' => array(),
					'title' => array(),
					'style' => array(),
				),
				'dl' => array(),
				'dt' => array(),
				'em' => array(),
				'h1' => array(),
				'h2' => array(),
				'h3' => array(),
				'h4' => array(),
				'h5' => array(),
				'h6' => array(),
				'i' => array(),
				'img' => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'li' => array(
					'class' => array(),
				),
				'ol' => array(
					'class' => array(),
				),
				'p' => array(
					'class' => array(),
				),
				'q' => array(
					'cite' => array(),
					'title' => array(),
				),
				'span' => array(
					'class' => array(),
					'title' => array(),
					'style' => array(),
				),
				'strike' => array(),
				'strong' => array(),
				'ul' => array(
					'class' => array(),
				),
				);
				$fme_pgisfw_wooextmm_message = '<div id="message" class="error">
				<p><strong> Product gallery image slider for woocommerce.</strong> The <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce plugin</a> must be active for this plugin to work. Please install &amp; activate WooCommerce »</p></div>';
				echo wp_kses(__($fme_pgisfw_wooextmm_message, 'exthwsm'), $fme_pgisfw_allowed_tags);
		}
		public function fme_pgifw_update_version() {

			$current_plugin_version=get_option('fme_pgifw_version');
			if (empty($current_plugin_version)) {
				$this->fme_pgisfw_install_default_settings();
			
			} else {

				$latest_plugin_version=$this->get_latest_plugin__version();
				if ( 0 != $latest_plugin_version && $current_plugin_version != $latest_plugin_version) {

					$this->fme_pgisfw_install_default_settings();
				}
			}
		}
		public function get_latest_plugin__version() {

			if ( is_admin() ) {
				if ( ! function_exists('get_plugin_data') ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php' ;
				}
				$plugin_data = get_plugin_data( __FILE__ );

				return $plugin_data['Version'];
			}
			return 0;
		}

		public function fme_pgisfw_check_variation_product_image() {
			
			// check_ajax_referer('fme_pgisfw_front_ajax_nonce','fme_pgisfw_front_nonce');
			if (isset($_REQUEST['variation_id'])) {
				echo esc_attr(wp_get_attachment_url(sanitize_text_field($_REQUEST['variation_id'])));
			}
			
			wp_die();
		}
		public function FME_PGISFW_module_constants() {
			if ( !defined( 'FME_PGISFW_URL' ) ) {
				define( 'FME_PGISFW_URL', plugin_dir_url( __FILE__ ) );
			}
			if ( !defined( 'FME_PGISFW_BASENAME' ) ) {
				define( 'FME_PGISFW_BASENAME', plugin_basename( __FILE__ ) );
			}
			if ( ! defined( 'FME_PGISFW_PLUGIN_DIR' ) ) {
				define( 'FME_PGISFW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
		}


		public function fme_pgisfw_install_default_settings() { 

			$latest_plugin_version=$this->get_latest_plugin__version();
			if (!empty($latest_plugin_version) && 0 != $latest_plugin_version) {

				update_option('fme_pgifw_version', $latest_plugin_version);

			}

			$fme_pgisfw_general_settings    = get_option('fme_pgisfw_general_settings');
			$fme_pgisfw_thumbnail_settings  = get_option('fme_pgisfw_thumbnail_settings');
			$fme_pgisfw_bullets_settings    = get_option('fme_pgisfw_bullets_settings');
			$fme_pgisfw_arrows_settings     = get_option('fme_pgisfw_arrows_settings');
			$fme_pgisfw_lightbox_settings   = get_option('fme_pgisfw_lightbox_settings');
			$fme_pgisfw_zoom_settings       = get_option('fme_pgisfw_zoom_settings');
			// $fme_pgisfw_old_settings = get_option('fme_pgisfw_settings');
			// if (isset($fme_pgisfw_old_settings)) {
			//  delete_option('fme_pgisfw_settings');
			// }

			if (false == $fme_pgisfw_general_settings) {
					

				$fme_pgisfw_general_settings = array(
				'fmeproductcategory' => '',
				'fme_pgisfw_selected_pc' => '',
				'fme_auto_play' => 'false',
				'fme_auto_play_timeout' => '2000',
				'fme_pgisfw_enable_disable' => 'yes',
				);
			}
			if ( !array_key_exists('fme_pgisfw_image_aspect_ratio', $fme_pgisfw_general_settings) ) {
				$fme_pgisfw_general_settings['fme_pgisfw_image_aspect_ratio'] = 'on';
			}

				update_option('fme_pgisfw_general_settings', $fme_pgisfw_general_settings);

			if (false == $fme_pgisfw_thumbnail_settings) {              
				$fme_pgisfw_thumbnail_settings = array(
					'fme_pgisfw_border_color' => '#000000',
					'fme_pgisfw_slider_layout' => 'horizontal',
					'fme_pgisfw_slider_images_style' => 'style1',
					'fme_thumbnails_to_show' => '5',
					'fme_pgisfw_hide_thumbnails' => 'no',
				);
			}

				update_option('fme_pgisfw_thumbnail_settings', $fme_pgisfw_thumbnail_settings);

				
			if (false == $fme_pgisfw_bullets_settings) {    
				$fme_pgisfw_bullets_settings = array(
					'fme_pgisfw_show_bullets' => 'yes',
					'fme_pgisfw_bullets_shape' => 'lines',
					'fme_pgisfw_bullets_color' => '#770D2B7E',
					'fme_pgisfw_bullets_hover_color' => '#770D2BFF',
					'fme_pgisfw_bullets_position' => 'inside_image',
					'fme_pgisfw_bullets_thumbnail' => 'yes',
					'fme_pgisfw_bullets_inside_position' => 'bottom_center',

				);
			}
				update_option('fme_pgisfw_bullets_settings', $fme_pgisfw_bullets_settings);


			if (false == $fme_pgisfw_arrows_settings) { 

				$fme_pgisfw_arrows_settings = array(
					'fme_pgisfw_navigation_icon_status' => 'true',
					'fme_pgisfw_navigation_background_color' => '#770D2BFF',
					'fme_pgisfw_icon_color' => '#FFFFFFFF',
					'fme_selected_image' => '1',
					'fme_pgisfw_navigation_icon_show_on' => 'fix',
					'fme_pgisfw_navigation_hover_color' => '#C91649EF',
					'fme_pgisfw_navigation_icon_shape' => 'square',
				);
			}
				update_option('fme_pgisfw_arrows_settings', $fme_pgisfw_arrows_settings);
			if (false == $fme_pgisfw_lightbox_settings) {   

				$fme_pgisfw_lightbox_settings = array(
					'fme_pgisfw_show_lightbox' => 'yes',
					'fme_pgisfw_Lightbox_frame_width' => '600',
					'fme_pgisfw_lightbox_bg_color' => '#870F3181',
					'fme_pgisfw_lightbox_bg_hover_color' => '#770D2BD9',
					'fme_selected_lightbox_image' => '1',
					'fme_pgisfw_lightbox_icon_color' => '#FFFFFFFF',
					'fme_pgisfw_lightbox_position' => 'fme_pgifw_top_right',
				);
			}
				update_option('fme_pgisfw_lightbox_settings', $fme_pgisfw_lightbox_settings);

			if (false == $fme_pgisfw_zoom_settings || empty($fme_pgisfw_zoom_settings)) {   
				$fme_pgisfw_zoom_settings = array(
					'fme_pgisfw_show_zoom' => 'yes',
					'fme_pgisfw_zoombox_frame_width' => '100',
					'fme_pgisfw_zoombox_frame_height' => '100',
					'fme_pgisfw_zoombox_radius' => '10',
				);
			}
				update_option('fme_pgisfw_zoom_settings', $fme_pgisfw_zoom_settings);
				$length=0;
		}
	} 
	new FME_PGISFW_IMAGE_SLIDER_MAIN();
}
