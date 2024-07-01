<?php 
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}
if ( !class_exists( 'FME_PGISFW_IMAGE_SLIDER_ADMIN' ) ) { 
	class FME_PGISFW_IMAGE_SLIDER_ADMIN extends FME_PGISFW_IMAGE_SLIDER_MAIN {
		public function __construct() {
			add_action( 'init', array( $this, 'fme_pgisfw_load_text_domain' ) );
			add_filter('woocommerce_settings_tabs_array', array( $this, 'fme_pgisfw_woocommerce_settings_tabs_array' ), 50 );
			add_action( 'woocommerce_settings_fme_pgisfw_tab', array( $this, 'fme_pgisfw_admin_settings' ));
			add_action( 'woocommerce_settings_fme_pgisfw_add_new_rule', array( $this, 'fme_pgisfw_add_new_rule' ));
			add_action( 'admin_enqueue_scripts', array( $this, 'fme_pgisfw_admin_scripts' ) );
			add_action('wp_ajax_fme_pgisfw_save_setting', array( $this, 'fme_pgisfw_save_setting' ));
			add_action('wp_ajax_fme_pgisfw_save_general_setting', array( $this, 'fme_pgisfw_save_general_setting' ));
			add_action('wp_ajax_fme_pgisfw_save_thumbnail_settings', array( $this, 'fme_pgisfw_save_thumbnail_settings' ));
			add_action('wp_ajax_fme_pgisfw_save_arrows_settings', array( $this, 'fme_pgisfw_save_arrows_settings' ));
			add_action('wp_ajax_fme_pgisfw_save_lightbox_settings', array( $this, 'fme_pgisfw_save_lightbox_settings' ));
			add_action('wp_ajax_fme_pgisfw_save_zoom_settings', array( $this, 'fme_pgisfw_save_zoom_settings' ));

			add_action('wp_ajax_fme_pgisfw_save_bullets_settings', array( $this, 'fme_pgisfw_save_bullets_settings' ));
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'fme_pgisfw_create_video_data_tab' ));
			add_action('save_post', array( $this, 'fme_pgisfw_save_video_data' ));
			add_action('woocommerce_product_data_panels', array( $this, 'fme_pgisfw_video_tab_form' ));

			add_action('wp_ajax_fme_pgisfw_save_rule_settings', array( $this, 'fme_pgisfw_save_rule_settings' ), 10 , 0);
			add_action('wp_ajax_fme_pgisfw_delete_rule', array( $this, 'fme_pgisfw_delete_rule' ), 10 , 0);
			add_action('wp_ajax_fme_pgisfw_quick_save_rule', array( $this, 'fme_pgisfw_quick_save_rule' ), 10 , 0);
			add_action('wp_ajax_fme_pgisfw_get_products_array', array( $this, 'fme_pgisfw_get_products_array' ), 10 , 0);
		}

		public function fme_pgisfw_create_video_data_tab( $tabs ) {
			$tabs['fme_pgifw_video_tab'] = array(
				'label' => __('Product Videos', 'fme_pgisfw'),
				'target' => 'additional_product_data',
				'priority' => 1155,
			);
			return $tabs;
		}

		public function fme_pgisfw_video_tab_form() {
			$post = get_the_ID();
			?>
	<style type="text/css">
			tr {
				line-height: 4 !important;
			}
		</style>
<div id="additional_product_data" class="panel woocommerce_options_panel hidden">
			<div class="row">
			<div class="col-md-12 col-sm-6">
				<input type="button" id="fme_pgisfw_btnAdd" name="fme_add_video_gallery" value="<?php echo esc_html__('Add New URL', 'fme_pgisfw'); ?>" class="button button-primary">   
			</div>
		</div>
		<div class="row" id="TextBoxContainer">
			<table>
				<tbody>
					<?php
					$get_video_url = get_post_meta($post, 'fme_pgisfw_video_urls', true);
					if (!empty($get_video_url)) {
						?>
						<input type="hidden" value="<?php echo count($get_video_url); ?>" id="fme_count_url_val">
						<?php
						foreach ($get_video_url as $key => $value) {
							?>
							<tr>
								<td>
									<input name ="url[]" class="fme_pdifw_allurls" type="url" id="url<?php echo esc_attr($key+1); ?>" required placeholder="https://example.com" value = "<?php echo esc_attr($value); ?>" class="form-control" />
								</td>
								<td id="fme_pgifw_mb_td" ><button class="fme_pgisfw_upload_btn button" type="button" id="uploadit<?php echo esc_attr($key+1); ?>" onclick="fme_upload_video(<?php echo esc_attr($key+1); ?>);"><?php echo esc_html__('Choose', 'fme_pgisfw'); ?></button>
								</td>
								<td>
									<button type="button" id="deletebtn<?php echo esc_attr($key+1); ?>" class="fme_pgifw_deletebtn button" style=""><?php echo esc_html__('Delete', 'fme_pgisfw'); ?></button>
								</td>
							</tr>
							<?php
						}
					} else {
						?>
						<input type="hidden" value="0" id="fme_count_url_val">
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
</div>
			<?php
		}

		public function fme_pgisfw_save_video_data( $post_id ) {
			if (isset($_REQUEST['url'])) {
				$fme_video_url =map_deep( wp_unslash( $_REQUEST['url'] ), 'filter_var' );
				update_post_meta($post_id, 'fme_pgisfw_video_urls', $fme_video_url);
			} else {
				update_post_meta($post_id, 'fme_pgisfw_video_urls', '');
			}
		}
		public function fme_pgisfw_woocommerce_settings_tabs_array( $tabs ) {
			$tabs['fme_pgisfw_tab'] = __('Gallery Slider', 'fme_pgisfw');
			$tabs['fme_pgisfw_add_new_rule'] =  __('Rules', 'fme_pgisfw');
			return $tabs;
		}
		public function fme_pgisfw_admin_settings() {
			require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/fme-pgisfw-main-settings-page.php' ;
		}
		//new rule page
		public function fme_pgisfw_add_new_rule() {
			require_once FME_PGISFW_PLUGIN_DIR . 'admin/view/add-new-rule.php' ;
		}
		public function fme_pgisfw_load_text_domain() {
			load_plugin_textdomain('fme_pgisfw', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		}
	
		public function fme_pgisfw_admin_scripts() {    
			if (isset($_GET['tab'])) {          
				

				if (( is_admin() && 'fme_pgisfw_tab'== $_GET['tab'] ) ||( is_admin() && 'fme_pgisfw_add_new_rule'== $_GET['tab'] )) {

					if ( ! wp_script_is( 'jquery', 'enqueued' )) {
						wp_enqueue_script('jquery');
					}
					wp_enqueue_style( 'fme_pgisfw_setting_css', plugins_url( 'assets/css/fme_pgisfw_admin.css', __FILE__ ), false , '1.0.8' );
					wp_enqueue_style( 'fme_tabs_css', plugins_url( 'assets/css/fme_tabs_custom_settings.css', __FILE__ ), false , '1.0.8' );
					wp_enqueue_script( 'fme_js_color_en', plugins_url( 'assets/js/color_picker.js', __FILE__ ), false, '1.0.8' );
					wp_enqueue_script( 'fme_pgisfw_setting_js', plugins_url( 'assets/js/fme_pgisfw_admin.js', __FILE__ ), false, '1.1.1' );

					wp_enqueue_script( 'fme_tabs_js', plugins_url( 'assets/js/fme_tabs_custom_settings.js', __FILE__ ), false, '1.0.8' );
					
					wp_enqueue_script( 'select2-min-js', plugins_url( 'assets/js/select2.min.js', __FILE__ ), false, '1.0.8' );
					wp_enqueue_style( 'select2-min-css', plugins_url( 'assets/css/select2.min.css', __FILE__ ), false , '1.0.8' );
					$ewcpm_data = array(
						'admin_url' => admin_url('admin-ajax.php'),
						'fme_pgisfw_nonce' => wp_create_nonce('fme_pgisfw_ajax_nonce'),
					);
					wp_enqueue_style( 'accordion_css', plugins_url( 'assets/css/accordion.css', __FILE__ ), false , '1.0.8' );
					wp_enqueue_script('fme_pgisfw_accordion_js', plugin_dir_url(__FILE__) . 'assets/js/accordion.js', false , '1.0.8' );

					wp_localize_script('fme_pgisfw_setting_js', 'ewcpm_php_vars', $ewcpm_data);
					wp_localize_script('fme_pgisfw_setting_js', 'ajax_url_add_pq', array( 'ajax_url_add_pq_data' => admin_url('admin-ajax.php') ));
				}

				if (( is_admin() && 'fme_pgisfw_add_new_rule'== $_GET['tab'] )) {

					if ( ! wp_script_is( 'jquery', 'enqueued' )) {
						wp_enqueue_script('jquery');
					}
					wp_enqueue_style( 'fme_pgisfw_setting_css', plugins_url( 'assets/css/fme_pgisfw_admin.css', __FILE__ ), false , '1.0.8' );
					wp_enqueue_style( 'fme_tabs_css', plugins_url( 'assets/css/fme_tabs_custom_settings.css', __FILE__ ), false , '1.0.8' );
					wp_enqueue_script( 'fme_js_color_en', plugins_url( 'assets/js/color_picker.js', __FILE__ ), false, '1.0.8' );

					wp_enqueue_script('fme_pgisfw_add_new_rule_setting_js', plugin_dir_url(__FILE__) . 'assets/js/add-new-rule-admin.js', false , '1.1.1' );

					wp_enqueue_script( 'fme_tabs_js', plugins_url( 'assets/js/fme_tabs_custom_settings.js', __FILE__ ), false, '1.0.8' );
					
					wp_enqueue_script( 'select2-min-js', plugins_url( 'assets/js/select2.min.js', __FILE__ ), false, '1.0.8' );
					wp_enqueue_style( 'select2-min-css', plugins_url( 'assets/css/select2.min.css', __FILE__ ), false , '1.0.8' );
					
					wp_localize_script('fme_pgisfw_add_new_rule_setting_js', 'myruleajax', array(
						'ajaxurl'=>admin_url('admin-ajax.php'),
						'fme_pgisfw_new_rule_nonce' => wp_create_nonce('fme_pgisfw_new_rule_ajax_nonce'),
					));
				}
			}
			wp_enqueue_script( 'fme-hide-tab-js', plugins_url( 'assets/js/fme-hide-tab.js', __FILE__ ), false, '1.0.8' );
			wp_localize_script('fme-hide-tab-js', 'fme_pgisfw_menu_string', array(
				'rule' => __('Rules', 'fme_pgisfw'),
			));
			wp_enqueue_style( 'fme_pgisfw_metabox-css', plugins_url( 'assets/css/fme_pgisfw_metabox.css', __FILE__ ), false , '1.0.8' );

			wp_enqueue_script( 'fme_pgisfw_video_setting_js', plugins_url( 'assets/js/fme_pgisfw_video_setting.js', __FILE__ ), false, '1.0.8' );
			wp_localize_script('fme_pgisfw_video_setting_js', 'fme_pgisfw_strings', array(

				'choose' => __('Choose', 'fme_pgisfw'),
				'delete' => __('Delete', 'fme_pgisfw'),
			)
			);
		}
		public function fme_pgisfw_save_setting() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_navigation_icon = isset($_REQUEST['fme_pgisfw_navigation_icon']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon']) : '';
			$fme_pgisfw_navigation_background_color = isset($_REQUEST['fme_pgisfw_navigation_background_color']) ? filter_var($_REQUEST['fme_pgisfw_navigation_background_color']) : '';
			$fme_pgisfw_icon_color = isset($_REQUEST['fme_pgisfw_icon_color']) ? filter_var($_REQUEST['fme_pgisfw_icon_color']) : '';
			$fme_pgisfw_border_color = isset($_REQUEST['fme_pgisfw_border_color']) ? filter_var($_REQUEST['fme_pgisfw_border_color']) : '';
			$fme_pgisfw_slider_layout = isset($_REQUEST['fme_pgisfw_slider_layout']) ? filter_var($_REQUEST['fme_pgisfw_slider_layout']) : '';
			$fmeproductcategory = isset($_REQUEST['fmeproductcategory']) ? filter_var($_REQUEST['fmeproductcategory']) : '';
			$fme_pgisfw_selected_pc = isset($_REQUEST['fme_pgisfw_selected_pc']) ? array_map('filter_var', $_REQUEST['fme_pgisfw_selected_pc']) : '';
			$fme_thumbnails_to_show = isset($_REQUEST['fme_thumbnails_to_show']) ? filter_var($_REQUEST['fme_thumbnails_to_show']) : '4';
			$fme_auto_play = isset($_REQUEST['fme_auto_play']) ? filter_var($_REQUEST['fme_auto_play']) : '';
			$fme_auto_play_timeout = isset($_REQUEST['fme_auto_play_timeout']) ? filter_var($_REQUEST['fme_auto_play_timeout']) : '1000';
			$fme_pgisfw_show_zoom = isset($_REQUEST['fme_pgisfw_show_zoom']) ? filter_var($_REQUEST['fme_pgisfw_show_zoom']) : '';
			$fme_pgisfw_hide_thumbnails = isset($_REQUEST['fme_pgisfw_hide_thumbnails']) ? filter_var($_REQUEST['fme_pgisfw_hide_thumbnails']) : '';
			$fme_pgisfw_show_lightbox = isset($_REQUEST['fme_pgisfw_show_lightbox']) ? filter_var($_REQUEST['fme_pgisfw_show_lightbox']) : '';
			$fme_pgisfw_Lightbox_frame_width = isset($_REQUEST['fme_pgisfw_Lightbox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_Lightbox_frame_width']) : '600';
			$fme_pgisfw_zoombox_frame_width = isset($_REQUEST['fme_pgisfw_zoombox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_width']) : '75';
			$fme_pgisfw_zoombox_frame_height = isset($_REQUEST['fme_pgisfw_zoombox_frame_height']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_height']) : '75';
			$fme_pgisfw_array = array(
				'fme_pgisfw_navigation_icon_status' => $fme_pgisfw_navigation_icon,
				'fme_pgisfw_navigation_background_color' => $fme_pgisfw_navigation_background_color,
				'fme_pgisfw_icon_color' => $fme_pgisfw_icon_color,
				'fme_pgisfw_border_color' => $fme_pgisfw_border_color,
				'fme_pgisfw_slider_layout' => $fme_pgisfw_slider_layout,
				'fmeproductcategory' => $fmeproductcategory,
				'fme_pgisfw_selected_pc' => $fme_pgisfw_selected_pc,
				'fme_thumbnails_to_show' => $fme_thumbnails_to_show,
				'fme_auto_play' => $fme_auto_play,
				'fme_auto_play_timeout' => $fme_auto_play_timeout,
				'fme_pgisfw_show_zoom' => $fme_pgisfw_show_zoom,
				'fme_pgisfw_hide_thumbnails' => $fme_pgisfw_hide_thumbnails,
				'fme_pgisfw_show_lightbox' => $fme_pgisfw_show_lightbox,
				'fme_pgisfw_Lightbox_frame_width'=> $fme_pgisfw_Lightbox_frame_width,
				'fme_pgisfw_zoombox_frame_width'=> $fme_pgisfw_zoombox_frame_width,
				'fme_pgisfw_zoombox_frame_height'=> $fme_pgisfw_zoombox_frame_height,
			);
			update_option('fme_pgisfw_settings', $fme_pgisfw_array);
			wp_die();
		}

		/**********Rule**********************/
		public function fme_pgisfw_save_rule_settings() {
			check_ajax_referer('fme_pgisfw_new_rule_ajax_nonce' , 'fme_pgisfw_new_rule_nonce');
			// rule general setings
			$length = 0;
			$rule_option_array=get_option('fme_pgisfw_save_rule_settings');

			if (''==$rule_option_array  || empty($rule_option_array)) {
				$rule_option_array=array();
				$length=1;

			} else {

				$length=count($rule_option_array);
				$length++;
			}

			$fme_pgisfw_rule_name = isset($_REQUEST['fme_pgisfw_rule_name']) ? filter_var($_REQUEST['fme_pgisfw_rule_name']) : 'Rule' . rand(1, 100);
			$fme_pgisfw_rule_priority = isset($_REQUEST['fme_pgisfw_rule_priority']) ? filter_var($_REQUEST['fme_pgisfw_rule_priority']) : '1'; 

			$fmeproductcategory = isset($_REQUEST['fmeproductcategory']) ? filter_var($_REQUEST['fmeproductcategory']) : '';
			$fme_pgisfw_selected_pc = isset($_REQUEST['fme_pgisfw_selected_pc']) ? array_map('filter_var', $_REQUEST['fme_pgisfw_selected_pc']) : '';

			$fme_auto_play = isset($_REQUEST['fme_auto_play']) ? filter_var($_REQUEST['fme_auto_play']) : '';
			$fme_auto_play_timeout = isset($_REQUEST['fme_auto_play_timeout']) ? filter_var($_REQUEST['fme_auto_play_timeout']) : '1000';

			$fme_pgisfw_rule_enable_disable = isset($_REQUEST['fme_pgisfw_rule_enable_disable']) ? filter_var($_REQUEST['fme_pgisfw_rule_enable_disable']) : 'true';
			$fme_pgisfw_image_aspect_ratio = isset($_REQUEST['fme_pgisfw_image_aspect_ratio']) ? filter_var($_REQUEST['fme_pgisfw_image_aspect_ratio']) : 'off';
			


			$fme_pgisfw_numbering_enable_disable= isset($_REQUEST['fme_pgisfw_numbering_enable_disable']) ? filter_var($_REQUEST['fme_pgisfw_numbering_enable_disable']) : 'true';

			$fme_pgisfw_numbering_color= isset($_REQUEST['fme_pgisfw_numbering_color']) ? filter_var($_REQUEST['fme_pgisfw_numbering_color']) : '#000000';



			//rule thumbnail settings
			$fme_pgisfw_border_color = isset($_REQUEST['fme_pgisfw_border_color']) ? filter_var($_REQUEST['fme_pgisfw_border_color']) : '#000000';
			$fme_pgisfw_slider_mode = isset($_REQUEST['fme_pgisfw_slider_mode']) ? filter_var($_REQUEST['fme_pgisfw_slider_mode'], FILTER_SANITIZE_STRING) : 'loop';

			$fme_pgisfw_slider_layout = isset($_REQUEST['fme_pgisfw_slider_layout']) ? filter_var($_REQUEST['fme_pgisfw_slider_layout']) : 'horizontal';

			$fme_pgisfw_slider_images_style = isset($_REQUEST['fme_pgisfw_slider_images_style']) ? filter_var($_REQUEST['fme_pgisfw_slider_images_style']) : 'style1';

			$fme_thumbnails_to_show = isset($_REQUEST['fme_thumbnails_to_show']) ? filter_var($_REQUEST['fme_thumbnails_to_show']) : '4';

			$fme_pgisfw_hide_thumbnails = isset($_REQUEST['fme_pgisfw_hide_thumbnails']) ? filter_var($_REQUEST['fme_pgisfw_hide_thumbnails']) : 'no';


			

			// rule bullets settings
			$fme_pgisfw_show_bullets = isset($_REQUEST['fme_pgisfw_show_bullets']) ? filter_var($_REQUEST['fme_pgisfw_show_bullets']) : 'no';

			$fme_pgisfw_bullets_thumbnail = isset($_REQUEST['fme_pgisfw_bullets_thumbnail']) ? filter_var($_REQUEST['fme_pgisfw_bullets_thumbnail']) : 'no';

			$fme_pgisfw_bullets_shape = isset($_REQUEST['fme_pgisfw_bullets_shape']) ? filter_var($_REQUEST['fme_pgisfw_bullets_shape']) : 'circular';

			$fme_pgisfw_bullets_color = isset($_REQUEST['fme_pgisfw_bullets_color']) ? filter_var($_REQUEST['fme_pgisfw_bullets_color']) : '#ffffff';

			$fme_pgisfw_bullets_hover_color = isset($_REQUEST['fme_pgisfw_bullets_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_bullets_hover_color']) : '#ffffff';


			$fme_pgisfw_bullets_position = isset($_REQUEST['fme_pgisfw_bullets_position']) ? filter_var($_REQUEST['fme_pgisfw_bullets_position']) : 'bottom_of_image';

			$fme_pgisfw_bullets_inside_position = isset($_REQUEST['fme_pgisfw_bullets_inside_position']) ? filter_var($_REQUEST['fme_pgisfw_bullets_inside_position']) : 'bottom_center';

			$fme_pgisfw_counter_bullets_font_color= isset($_REQUEST['fme_pgisfw_counter_bullets_font_color']) ? filter_var( $_REQUEST['fme_pgisfw_counter_bullets_font_color']) :'#00000AA';
			

			// navigation (arrow) settings
			$fme_pgisfw_navigation_icon = isset($_REQUEST['fme_pgisfw_navigation_icon']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon']) : '';
			$fme_pgisfw_navigation_background_color = isset($_REQUEST['fme_pgisfw_navigation_background_color']) ? filter_var($_REQUEST['fme_pgisfw_navigation_background_color']) : '';
			$fme_pgisfw_icon_color = isset($_REQUEST['fme_pgisfw_icon_color']) ? filter_var($_REQUEST['fme_pgisfw_icon_color']) : '';
			$fme_selected_image = isset($_REQUEST['fme_selected_image']) ? filter_var($_REQUEST['fme_selected_image']) : '';
			$fme_pgisfw_navigation_icon_show_on = isset($_REQUEST['fme_pgisfw_navigation_icon_show_on']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon_show_on']) : '';
			$fme_pgisfw_navigation_hover_color = isset($_REQUEST['fme_pgisfw_navigation_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_navigation_hover_color']) : '';
			$fme_pgisfw_navigation_icon_shape = isset($_REQUEST['fme_pgisfw_navigation_icon_shape']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon_shape']) : '';

		

			// lightbox save setting
			$fme_pgisfw_Lightbox_frame_width = isset($_REQUEST['fme_pgisfw_Lightbox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_Lightbox_frame_width']) : '600';

			$fme_pgisfw_show_lightbox = isset($_REQUEST['fme_pgisfw_show_lightbox']) ? filter_var($_REQUEST['fme_pgisfw_show_lightbox']) : '';
			
			$fme_pgisfw_lightbox_bg_color = isset($_REQUEST['fme_pgisfw_lightbox_bg_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_bg_color']) : '';
			$fme_pgisfw_lightbox_bg_hover_color = isset($_REQUEST['fme_pgisfw_lightbox_bg_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_bg_hover_color']) : '';
			$fme_selected_lightbox_image = isset($_REQUEST['fme_selected_lightbox_image']) ? filter_var($_REQUEST['fme_selected_lightbox_image']) : '';
			$fme_pgisfw_lightbox_icon_color = isset($_REQUEST['fme_pgisfw_lightbox_icon_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_icon_color']) : '';
			$fme_pgisfw_lightbox_position = isset($_REQUEST['fme_pgisfw_lightbox_position']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_position']) : '';
			$fme_pgisfw_lightbox_slide_effect= isset($_REQUEST['fme_pgisfw_lightbox_slide_effect']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_slide_effect']) : 'slide';
		

			//zoom save settings
			$fme_pgisfw_show_zoom = isset($_REQUEST['fme_pgisfw_show_zoom']) ? filter_var($_REQUEST['fme_pgisfw_show_zoom']) : '';
			$fme_pgisfw_zoombox_frame_width = isset($_REQUEST['fme_pgisfw_zoombox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_width']) : '75';
			$fme_pgisfw_zoombox_frame_height = isset($_REQUEST['fme_pgisfw_zoombox_frame_height']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_height']) : '75';
			$fme_pgisfw_zoombox_radius = isset($_REQUEST['fme_pgisfw_zoombox_radius']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_radius']) : '10';

			$update_rule_id =isset($_REQUEST['rule_id']) ? filter_var($_REQUEST['rule_id']) : '';
			if (''!=$update_rule_id) {
				$length=$update_rule_id;    
			}
			$fme_pgisfw_rule_array = array(
				//general
				'fme_pgisfw_enable_disable' => $fme_pgisfw_rule_enable_disable,
				'fme_pgisfw_rule_name' => $fme_pgisfw_rule_name,
				'fme_pgisfw_rule_priority' => $fme_pgisfw_rule_priority,
				'fmeproductcategory' => $fmeproductcategory,
				'fme_pgisfw_selected_pc' => $fme_pgisfw_selected_pc,
				'fme_auto_play' => $fme_auto_play,
				'fme_auto_play_timeout' => $fme_auto_play_timeout,
				'fme_pgisfw_numbering_enable_disable' => $fme_pgisfw_numbering_enable_disable,
				'fme_pgisfw_image_aspect_ratio'       => $fme_pgisfw_image_aspect_ratio,
				'fme_pgisfw_numbering_color' => $fme_pgisfw_numbering_color,    
				// thumbnail
				'fme_pgisfw_slider_mode'  => $fme_pgisfw_slider_mode,
				'fme_pgisfw_border_color' => $fme_pgisfw_border_color,
				'fme_pgisfw_slider_layout' => $fme_pgisfw_slider_layout,
				'fme_pgisfw_slider_images_style' => $fme_pgisfw_slider_images_style,
				'fme_thumbnails_to_show' => $fme_thumbnails_to_show,
				'fme_pgisfw_hide_thumbnails' => $fme_pgisfw_hide_thumbnails,
				// bullets
				'fme_pgisfw_show_bullets' => $fme_pgisfw_show_bullets,
				'fme_pgisfw_bullets_shape' => $fme_pgisfw_bullets_shape,
				'fme_pgisfw_bullets_color' => $fme_pgisfw_bullets_color,
				'fme_pgisfw_bullets_hover_color' => $fme_pgisfw_bullets_hover_color,
				'fme_pgisfw_bullets_position' => $fme_pgisfw_bullets_position,
				'fme_pgisfw_bullets_thumbnail' => $fme_pgisfw_bullets_thumbnail,
				'fme_pgisfw_bullets_inside_position' => $fme_pgisfw_bullets_inside_position,
				'fme_pgisfw_counter_bullets_font_color' => $fme_pgisfw_counter_bullets_font_color,
				//arrow
				'fme_pgisfw_navigation_icon_status' => $fme_pgisfw_navigation_icon,
				'fme_pgisfw_navigation_background_color' => $fme_pgisfw_navigation_background_color,
				'fme_pgisfw_icon_color' => $fme_pgisfw_icon_color,
				'fme_selected_image' => $fme_selected_image,
				'fme_pgisfw_navigation_icon_show_on' => $fme_pgisfw_navigation_icon_show_on,
				'fme_pgisfw_navigation_hover_color' => $fme_pgisfw_navigation_hover_color,
				'fme_pgisfw_navigation_icon_shape' => $fme_pgisfw_navigation_icon_shape,
				//lightbox
				'fme_pgisfw_show_lightbox' => $fme_pgisfw_show_lightbox,
				'fme_pgisfw_Lightbox_frame_width' => $fme_pgisfw_Lightbox_frame_width,
				'fme_pgisfw_lightbox_bg_color' => $fme_pgisfw_lightbox_bg_color,
				'fme_pgisfw_lightbox_bg_hover_color' => $fme_pgisfw_lightbox_bg_hover_color,
				'fme_selected_lightbox_image' => $fme_selected_lightbox_image,
				'fme_pgisfw_lightbox_icon_color' => $fme_pgisfw_lightbox_icon_color,
				'fme_pgisfw_lightbox_position' => $fme_pgisfw_lightbox_position,
				'fme_pgisfw_lightbox_slide_effect' => $fme_pgisfw_lightbox_slide_effect,
				//zoom
				'fme_pgisfw_show_zoom' => $fme_pgisfw_show_zoom,
				'fme_pgisfw_zoombox_frame_width' => $fme_pgisfw_zoombox_frame_width,
				'fme_pgisfw_zoombox_frame_height' => $fme_pgisfw_zoombox_frame_height,
				'fme_pgisfw_zoombox_radius' => $fme_pgisfw_zoombox_radius,
			);
			
			if (empty($update_rule_id) || ''==$update_rule_id ) {
				if (array_key_exists( $length , $rule_option_array)) {
					$length++;
				}
				$rule_option_array[$length]=$fme_pgisfw_rule_array;
			
				update_option('fme_pgisfw_save_rule_settings', $rule_option_array);
			
			} else if (!empty($update_rule_id) || ''!=$update_rule_id ) {

				$rule_option_array[$length]=$fme_pgisfw_rule_array;
			
				update_option('fme_pgisfw_save_rule_settings', $rule_option_array); 
			} 
				echo intval( $length );//don't remove this
			wp_die();
		}

		/***********End rule save function****/

		/********************Quick save rule settings**************/
		public function fme_pgisfw_quick_save_rule() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');

			// rule general setings
			$rule_option_array=get_option('fme_pgisfw_save_rule_settings');
			if (isset($_REQUEST['fme_quick_rule_id'])) {
				$fme_pgisfw_rule_id=isset($_REQUEST['fme_quick_rule_id']) ? filter_var($_REQUEST['fme_quick_rule_id']) :'';
				$fme_pgisfw_rule_priority=isset($_REQUEST['fme_quick_rule_priority']) ? filter_var($_REQUEST['fme_quick_rule_priority']) :'';
				$fme_quick_enable_disable=isset($_REQUEST['fme_quick_enable_disable']) ? filter_var($_REQUEST['fme_quick_enable_disable']) :'';
				$edit_rule_array=$rule_option_array[$fme_pgisfw_rule_id];

				$edit_rule_array['fme_pgisfw_rule_priority']=$fme_pgisfw_rule_priority;
				$edit_rule_array['fme_pgisfw_enable_disable']=$fme_quick_enable_disable;
				$rule_option_array[$fme_pgisfw_rule_id]=$edit_rule_array;
				update_option('fme_pgisfw_save_rule_settings', $rule_option_array);
				
				wp_die();

			}
		}

		public function fme_pgisfw_get_products_array() {

			global $wpdb;
			$json = array();

			$serch_term  = isset($_GET['term']) ? filter_var($_GET['term']) : '';
			if (!$serch_term) {
				$json = array();
			}

			if (!isset($_GET['term'])) { 
				$json = array();
			} else {

				// $sql = 'SELECT post_name, post_title FROM ' . $wpdb->posts . " WHERE post_title LIKE '%" . $serch_term . "%' AND post_type = 'product' LIMIT 100  ";

				$result = $wpdb->get_results(
					$wpdb->prepare(
				'SELECT ID,post_name, post_title FROM ' . $wpdb->posts . " WHERE post_title LIKE %s AND post_type = 'product' LIMIT 100  "
					, '%' . $serch_term . '%'
					)
					);

				$json = array();

				foreach ($result as $row) {

					$json[] = array( 'id'=>$row->ID, 'text'=>$row->post_title );

				}

			}
			echo json_encode($json);
			wp_die();
		}


		/*****************************End save quick rule****************************/

		public function fme_pgisfw_save_general_setting() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fmeproductcategory = isset($_REQUEST['fmeproductcategory']) ? filter_var($_REQUEST['fmeproductcategory']) : '';
			$fme_pgisfw_selected_pc = isset($_REQUEST['fme_pgisfw_selected_pc']) ? array_map('filter_var', $_REQUEST['fme_pgisfw_selected_pc']) : '';

			$fme_auto_play = isset($_REQUEST['fme_auto_play']) ? filter_var($_REQUEST['fme_auto_play']) : '';
			$fme_auto_play_timeout = isset($_REQUEST['fme_auto_play_timeout']) ? filter_var($_REQUEST['fme_auto_play_timeout']) : '1000';

			$fme_pgisfw_enable_disable = isset($_REQUEST['fme_pgisfw_enable_disable']) ? filter_var($_REQUEST['fme_pgisfw_enable_disable']) : 'true';

			$fme_pgisfw_numbering_enable_disable= isset($_REQUEST['fme_pgisfw_numbering_enable_disable']) ? filter_var($_REQUEST['fme_pgisfw_numbering_enable_disable']) : 'true';

			$fme_pgisfw_image_aspect_ratio= isset($_REQUEST['fme_pgisfw_image_aspect_ratio']) ? filter_var($_REQUEST['fme_pgisfw_image_aspect_ratio']) : 'off';
			

			$fme_pgisfw_numbering_color= isset($_REQUEST['fme_pgisfw_numbering_color']) ? filter_var($_REQUEST['fme_pgisfw_numbering_color']) : '#000000';

			$fme_pgisfw_array = array(
				'fmeproductcategory' => $fmeproductcategory,
				'fme_pgisfw_selected_pc' => $fme_pgisfw_selected_pc,
				'fme_auto_play' => $fme_auto_play,
				'fme_auto_play_timeout' => $fme_auto_play_timeout,
				'fme_pgisfw_enable_disable' => $fme_pgisfw_enable_disable,
				'fme_pgisfw_image_aspect_ratio' => $fme_pgisfw_image_aspect_ratio,
				'fme_pgisfw_numbering_enable_disable' => $fme_pgisfw_numbering_enable_disable,
				'fme_pgisfw_numbering_color' => $fme_pgisfw_numbering_color,
			);
			update_option('fme_pgisfw_general_settings', $fme_pgisfw_array);
			wp_die();
		}




		public function fme_pgisfw_save_thumbnail_settings() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_border_color = isset($_REQUEST['fme_pgisfw_border_color']) ? filter_var($_REQUEST['fme_pgisfw_border_color']) : '#000000';
			$fme_pgisfw_slider_mode = isset($_REQUEST['fme_pgisfw_slider_mode']) ? filter_var($_REQUEST['fme_pgisfw_slider_mode'], FILTER_SANITIZE_STRING) : 'loop';

			$fme_pgisfw_slider_layout = isset($_REQUEST['fme_pgisfw_slider_layout']) ? filter_var($_REQUEST['fme_pgisfw_slider_layout']) : 'horizontal';
			$fme_thumbnails_to_show = isset($_REQUEST['fme_thumbnails_to_show']) ? filter_var($_REQUEST['fme_thumbnails_to_show']) : '4';

			$fme_pgisfw_hide_thumbnails = isset($_REQUEST['fme_pgisfw_hide_thumbnails']) ? filter_var($_REQUEST['fme_pgisfw_hide_thumbnails']) : 'no';

			$fme_pgisfw_slider_images_style = isset($_REQUEST['fme_pgisfw_slider_images_style']) ? filter_var($_REQUEST['fme_pgisfw_slider_images_style']) : 'style1';
			


			$fme_pgisfw_array = array(
				'fme_pgisfw_border_color' => $fme_pgisfw_border_color,
				'fme_pgisfw_slider_mode'  => $fme_pgisfw_slider_mode,
				'fme_pgisfw_slider_layout' => $fme_pgisfw_slider_layout,
				'fme_thumbnails_to_show' => $fme_thumbnails_to_show,
				'fme_pgisfw_hide_thumbnails' => $fme_pgisfw_hide_thumbnails,
				'fme_pgisfw_slider_images_style' => $fme_pgisfw_slider_images_style,
			);
			update_option('fme_pgisfw_thumbnail_settings', $fme_pgisfw_array);
			wp_die();
		}





		public function fme_pgisfw_save_bullets_settings() {

			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');

			$fme_pgisfw_show_bullets = isset($_REQUEST['fme_pgisfw_show_bullets']) ? filter_var($_REQUEST['fme_pgisfw_show_bullets']) : 'no';

			$fme_pgisfw_bullets_thumbnail = isset($_REQUEST['fme_pgisfw_bullets_thumbnail']) ? filter_var($_REQUEST['fme_pgisfw_bullets_thumbnail']) : 'no';

			$fme_pgisfw_bullets_shape = isset($_REQUEST['fme_pgisfw_bullets_shape']) ? filter_var($_REQUEST['fme_pgisfw_bullets_shape']) : 'circular';

			$fme_pgisfw_bullets_color = isset($_REQUEST['fme_pgisfw_bullets_color']) ? filter_var($_REQUEST['fme_pgisfw_bullets_color']) : '#ffffff';

			$fme_pgisfw_bullets_hover_color = isset($_REQUEST['fme_pgisfw_bullets_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_bullets_hover_color']) : '#ffffff';


			$fme_pgisfw_bullets_position = isset($_REQUEST['fme_pgisfw_bullets_position']) ? filter_var($_REQUEST['fme_pgisfw_bullets_position']) : 'bottom_of_image';

			$fme_pgisfw_bullets_inside_position = isset($_REQUEST['fme_pgisfw_bullets_inside_position']) ? filter_var($_REQUEST['fme_pgisfw_bullets_inside_position']) : 'bottom_center';

			$fme_pgisfw_counter_bullets_font_color= isset($_REQUEST['fme_pgisfw_counter_bullets_font_color']) ? filter_var( $_REQUEST['fme_pgisfw_counter_bullets_font_color']) :'#00000AA';
			$fme_pgisfw_array = array(
				'fme_pgisfw_show_bullets' => $fme_pgisfw_show_bullets,
				'fme_pgisfw_bullets_shape' => $fme_pgisfw_bullets_shape,
				'fme_pgisfw_bullets_color' => $fme_pgisfw_bullets_color,
				'fme_pgisfw_bullets_hover_color' => $fme_pgisfw_bullets_hover_color,
				'fme_pgisfw_bullets_position' => $fme_pgisfw_bullets_position,
				'fme_pgisfw_bullets_thumbnail' => $fme_pgisfw_bullets_thumbnail,
				'fme_pgisfw_bullets_inside_position' => $fme_pgisfw_bullets_inside_position,
				'fme_pgisfw_counter_bullets_font_color' => $fme_pgisfw_counter_bullets_font_color,
			);
			update_option('fme_pgisfw_bullets_settings', $fme_pgisfw_array);
			wp_die();
		}



		public function fme_pgisfw_save_arrows_settings() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_navigation_icon = isset($_REQUEST['fme_pgisfw_navigation_icon']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon']) : '';
			$fme_pgisfw_navigation_background_color = isset($_REQUEST['fme_pgisfw_navigation_background_color']) ? filter_var($_REQUEST['fme_pgisfw_navigation_background_color']) : '';
			$fme_pgisfw_icon_color = isset($_REQUEST['fme_pgisfw_icon_color']) ? filter_var($_REQUEST['fme_pgisfw_icon_color']) : '';
			$fme_selected_image = isset($_REQUEST['fme_selected_image']) ? filter_var($_REQUEST['fme_selected_image']) : '';
			$fme_pgisfw_navigation_icon_show_on = isset($_REQUEST['fme_pgisfw_navigation_icon_show_on']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon_show_on']) : '';
			$fme_pgisfw_navigation_hover_color = isset($_REQUEST['fme_pgisfw_navigation_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_navigation_hover_color']) : '';
			$fme_pgisfw_navigation_icon_shape = isset($_REQUEST['fme_pgisfw_navigation_icon_shape']) ? filter_var($_REQUEST['fme_pgisfw_navigation_icon_shape']) : '';

			$fme_pgisfw_array = array(
				'fme_pgisfw_navigation_icon_status' => $fme_pgisfw_navigation_icon,
				'fme_pgisfw_navigation_background_color' => $fme_pgisfw_navigation_background_color,
				'fme_pgisfw_icon_color' => $fme_pgisfw_icon_color,
				'fme_selected_image' => $fme_selected_image,
				'fme_pgisfw_navigation_icon_show_on' => $fme_pgisfw_navigation_icon_show_on,
				'fme_pgisfw_navigation_hover_color' => $fme_pgisfw_navigation_hover_color,
				'fme_pgisfw_navigation_icon_shape' => $fme_pgisfw_navigation_icon_shape,
			);
			update_option('fme_pgisfw_arrows_settings', $fme_pgisfw_array);
			wp_die();
		}


		public function fme_pgisfw_save_lightbox_settings() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_Lightbox_frame_width = isset($_REQUEST['fme_pgisfw_Lightbox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_Lightbox_frame_width']) : '600';

			$fme_pgisfw_show_lightbox = isset($_REQUEST['fme_pgisfw_show_lightbox']) ? filter_var($_REQUEST['fme_pgisfw_show_lightbox']) : '';
			
			$fme_pgisfw_lightbox_bg_color = isset($_REQUEST['fme_pgisfw_lightbox_bg_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_bg_color']) : '';
			$fme_pgisfw_lightbox_bg_hover_color = isset($_REQUEST['fme_pgisfw_lightbox_bg_hover_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_bg_hover_color']) : '';
			$fme_selected_lightbox_image = isset($_REQUEST['fme_selected_lightbox_image']) ? filter_var($_REQUEST['fme_selected_lightbox_image']) : '';
			$fme_pgisfw_lightbox_icon_color = isset($_REQUEST['fme_pgisfw_lightbox_icon_color']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_icon_color']) : '';
			$fme_pgisfw_lightbox_position = isset($_REQUEST['fme_pgisfw_lightbox_position']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_position']) : '';
			$fme_pgisfw_lightbox_slide_effect= isset($_REQUEST['fme_pgisfw_lightbox_slide_effect']) ? filter_var($_REQUEST['fme_pgisfw_lightbox_slide_effect']) : 'slide';
			$fme_pgisfw_array = array(
				'fme_pgisfw_show_lightbox' => $fme_pgisfw_show_lightbox,
				'fme_pgisfw_Lightbox_frame_width' => $fme_pgisfw_Lightbox_frame_width,
				'fme_pgisfw_lightbox_bg_color' => $fme_pgisfw_lightbox_bg_color,
				'fme_pgisfw_lightbox_bg_hover_color' => $fme_pgisfw_lightbox_bg_hover_color,
				'fme_selected_lightbox_image' => $fme_selected_lightbox_image,
				'fme_pgisfw_lightbox_icon_color' => $fme_pgisfw_lightbox_icon_color,
				'fme_pgisfw_lightbox_position' => $fme_pgisfw_lightbox_position,
				'fme_pgisfw_lightbox_slide_effect' => $fme_pgisfw_lightbox_slide_effect,
			);
			update_option('fme_pgisfw_lightbox_settings', $fme_pgisfw_array);
			wp_die();
		}



		public function fme_pgisfw_save_zoom_settings() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_show_zoom = isset($_REQUEST['fme_pgisfw_show_zoom']) ? filter_var($_REQUEST['fme_pgisfw_show_zoom']) : '';
			$fme_pgisfw_zoombox_frame_width = isset($_REQUEST['fme_pgisfw_zoombox_frame_width']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_width']) : '75';
			$fme_pgisfw_zoombox_frame_height = isset($_REQUEST['fme_pgisfw_zoombox_frame_height']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_frame_height']) : '75';
			$fme_pgisfw_zoombox_radius = isset($_REQUEST['fme_pgisfw_zoombox_radius']) ? filter_var($_REQUEST['fme_pgisfw_zoombox_radius']) : '10';



			$fme_pgisfw_array = array(
				'fme_pgisfw_show_zoom' => $fme_pgisfw_show_zoom,
				'fme_pgisfw_zoombox_frame_width' => $fme_pgisfw_zoombox_frame_width,
				'fme_pgisfw_zoombox_frame_height' => $fme_pgisfw_zoombox_frame_height,
				'fme_pgisfw_zoombox_radius' => $fme_pgisfw_zoombox_radius,
			);
			update_option('fme_pgisfw_zoom_settings', $fme_pgisfw_array);
			wp_die();
		}

		public function fme_pgisfw_delete_rule() {
			check_ajax_referer('fme_pgisfw_ajax_nonce', 'fme_pgisfw_nonce');
			$fme_pgisfw_rule_id= isset($_REQUEST['rule_id']) ? filter_var($_REQUEST['rule_id']) : '';
			$rule_option_array=get_option('fme_pgisfw_save_rule_settings');

			if (!empty($fme_pgisfw_rule_id) && 'fme_pgisfw_delete_all_rules'==$fme_pgisfw_rule_id) {
				delete_option('fme_pgisfw_save_rule_settings'); 
			} else if (!empty($fme_pgisfw_rule_id)) {

				unset($rule_option_array[$fme_pgisfw_rule_id]);
				update_option('fme_pgisfw_save_rule_settings', $rule_option_array);
			}
			$rule_option_array=map_deep($rule_option_array, 'sanitize_text_field');
			// print_r($rule_option_array);
		}
	}
	new FME_PGISFW_IMAGE_SLIDER_ADMIN();
}

//Notice: Undefined index: fme_pgisfw_add_new_rule in /var/www/html/wordpressproject2/wp-content/plugins/woocommerce/packages/woocommerce-admin/includes/connect-existing-pages.php on line 89

