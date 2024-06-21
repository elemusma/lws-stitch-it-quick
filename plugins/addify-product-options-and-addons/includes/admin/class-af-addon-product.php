<?php

/**
 * Product Level Fields
 */

class Af_Addon_Product {

	public function __construct() {

		add_action( 'save_post_product', array( $this, 'addon_save_product' ), 1 );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'af_product_addon_tab' ), 98 );

		add_filter( 'woocommerce_product_data_panels', array( $this, 'af_product_data_panels' ), 98 );
	}

	public function addon_save_product( $prod_id ) {

		if ( 'auto-draft' != get_post_status( $prod_id ) && 'trash' != get_post_status( $prod_id ) ) {

			$nonce = isset( $_POST['af_prod_metabox_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['af_prod_metabox_nonce_field'] ) ) : 0;

			if ( wp_verify_nonce( $nonce, 'af_prod_metabox_nonce' ) ) {

				$this->af_product_save_style( $prod_id, $_POST );

				$this->af_product_save_fields( $prod_id, $_POST );
			}
		}
	}

	public function af_product_save_style( $prod_id, $post_data ) {

		$exclude_rule_addons = isset( $post_data['exclude_rule_addons'] ) ? sanitize_text_field( wp_unslash( $post_data['exclude_rule_addons'] ) ) : '';
		update_post_meta( $prod_id, 'exclude_rule_addons', $exclude_rule_addons );

		$af_addon_title_display_as_selector = isset( $post_data['af_addon_title_display_as_selector'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_display_as_selector'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_display_as_selector', $af_addon_title_display_as_selector );

		$af_addon_heading_type_selector = isset( $post_data['af_addon_heading_type_selector'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_heading_type_selector'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_heading_type_selector', $af_addon_heading_type_selector );

		$af_addon_seperator_checkbox = isset( $post_data['af_addon_seperator_checkbox'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_seperator_checkbox'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_seperator_checkbox', $af_addon_seperator_checkbox );

		$af_addon_title_seperator = isset( $post_data['af_addon_title_seperator'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_seperator'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_seperator', $af_addon_title_seperator );

		$af_addon_option_title_font_size = isset( $post_data['af_addon_option_title_font_size'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_option_title_font_size'] ) ) : '12';
		update_post_meta( $prod_id, 'af_addon_option_title_font_size', $af_addon_option_title_font_size );

		$af_addon_option_title_font_color = isset( $post_data['af_addon_option_title_font_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_option_title_font_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_option_title_font_color', $af_addon_option_title_font_color );

		$af_addon_title_bg = isset( $post_data['af_addon_title_bg'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_bg'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_bg', $af_addon_title_bg );

		$af_addon_title_color = isset( $post_data['af_addon_title_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_color', $af_addon_title_color );

		$af_addon_bg_color = isset( $post_data['af_addon_bg_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_bg_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_bg_color', $af_addon_bg_color );

		$af_addon_title_top_left_radius = isset( $post_data['af_addon_title_top_left_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_top_left_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_top_left_radius', $af_addon_title_top_left_radius );

		$af_addon_title_top_right_radius = isset( $post_data['af_addon_title_top_right_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_top_right_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_top_right_radius', $af_addon_title_top_right_radius );

		$af_addon_title_bottom_left_radius = isset( $post_data['af_addon_title_bottom_left_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_bottom_left_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_bottom_left_radius', $af_addon_title_bottom_left_radius );

		$af_addon_title_bottom_right_radius = isset( $post_data['af_addon_title_bottom_right_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_bottom_right_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_bottom_right_radius', $af_addon_title_bottom_right_radius );

		$af_addon_title_top_padding = isset( $post_data['af_addon_title_top_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_top_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_top_padding', $af_addon_title_top_padding );

		$af_addon_title_bottom_padding = isset( $post_data['af_addon_title_bottom_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_bottom_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_bottom_padding', $af_addon_title_bottom_padding );

		$af_addon_title_left_padding = isset( $post_data['af_addon_title_left_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_left_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_left_padding', $af_addon_title_left_padding );

		$af_addon_title_right_padding = isset( $post_data['af_addon_title_right_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_right_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_right_padding', $af_addon_title_right_padding );

		$af_addon_field_border = isset( $post_data['af_addon_field_border'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border', $af_addon_field_border );

		$af_addon_field_border_pixels = isset( $post_data['af_addon_field_border_pixels'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_pixels'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_pixels', $af_addon_field_border_pixels );

		$af_addon_field_border_top_left_radius = isset( $post_data['af_addon_field_border_top_left_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_top_left_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_top_left_radius', $af_addon_field_border_top_left_radius );

		$af_addon_field_border_top_right_radius = isset( $post_data['af_addon_field_border_top_right_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_top_right_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_top_right_radius', $af_addon_field_border_top_right_radius );

		$af_addon_field_border_bottom_left_radius = isset( $post_data['af_addon_field_border_bottom_left_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_bottom_left_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_bottom_left_radius', $af_addon_field_border_bottom_left_radius );

		$af_addon_field_border_bottom_right_radius = isset( $post_data['af_addon_field_border_bottom_right_radius'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_bottom_right_radius'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_bottom_right_radius', $af_addon_field_border_bottom_right_radius );

		$af_addon_field_border_top_padding = isset( $post_data['af_addon_field_border_top_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_top_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_top_padding', $af_addon_field_border_top_padding );

		$af_addon_field_border_bottom_padding = isset( $post_data['af_addon_field_border_bottom_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_bottom_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_bottom_padding', $af_addon_field_border_bottom_padding );

		$af_addon_field_border_left_padding = isset( $post_data['af_addon_field_border_left_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_left_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_left_padding', $af_addon_field_border_left_padding );

		$af_addon_field_border_right_padding = isset( $post_data['af_addon_field_border_right_padding'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_right_padding'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_right_padding', $af_addon_field_border_right_padding );

		$af_addon_field_title_position = isset( $post_data['af_addon_field_title_position'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_title_position'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_title_position', $af_addon_field_title_position );

		$af_addon_field_border_color = isset( $post_data['af_addon_field_border_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_border_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_field_border_color', $af_addon_field_border_color );

		$af_addon_title_font_size = isset( $post_data['af_addon_title_font_size'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_font_size'] ) ) : '14';
		update_post_meta( $prod_id, 'af_addon_title_font_size', $af_addon_title_font_size );

		$af_addon_desc_font_size = isset( $post_data['af_addon_desc_font_size'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_desc_font_size'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_desc_font_size', $af_addon_desc_font_size );

		$af_addon_title_position = isset( $post_data['af_addon_title_position'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_title_position'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_title_position', $af_addon_title_position );

		$af_addon_tooltip_background_color = isset( $post_data['af_addon_tooltip_background_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_tooltip_background_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_tooltip_background_color', $af_addon_tooltip_background_color );

		$af_addon_tooltip_text_color = isset( $post_data['af_addon_tooltip_text_color'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_tooltip_text_color'] ) ) : '';
		update_post_meta( $prod_id, 'af_addon_tooltip_text_color', $af_addon_tooltip_text_color );

		$af_addon_tooltip_font_size = isset( $post_data['af_addon_tooltip_font_size'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_tooltip_font_size'] ) ) : '12';
		update_post_meta( $prod_id, 'af_addon_tooltip_font_size', $af_addon_tooltip_font_size );
	}

	public function af_product_save_fields( $prod_id, $post_data ) {

		$fields = get_posts(
			array(
				'post_type'   => 'af_pao_fields',
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_parent' => $prod_id,
				'fields'      => 'ids',
			)
		);

		foreach ( $fields as $field_id ) {

			$field_id            = intval( $field_id );
			$field_id            = (int) $field_id;
			$af_addon_field_sort = isset( $post_data['af_addon_field_sort'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_sort'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_field_sort', $af_addon_field_sort );

			$insert_field = wp_update_post(
				array(
					'post_type'   => 'af_pao_fields',
					'numberposts' => -1,
					'post_status' => 'publish',
					'ID'          => $field_id,
					'menu_order'  => $af_addon_field_sort,
				)
			);

			$depend_selecter = isset( $post_data['af_addon_depend_selector'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_depend_selector'][ $field_id ] ) ) : 'af_addon_not_dependable';
			update_post_meta( $field_id, 'af_addon_depend_selector', $depend_selecter );

			$field_depend_selecter = isset( $post_data['af_addon_field_depend_selector'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_depend_selector'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_field_depend_selector', $field_depend_selecter );

			$option_depend_selecter = isset( $post_data['af_addon_option_depend_selector'][ $field_id ] ) ? sanitize_meta( '', $post_data['af_addon_option_depend_selector'][ $field_id ], '' ) : array();
			update_post_meta( $field_id, 'af_addon_option_depend_selector', $option_depend_selecter );

			$field_type = isset( $post_data['af_addon_type_select'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_type_select'][ $field_id ] ) ) : 'drop_down';
			update_post_meta( $field_id, 'af_addon_type_select', $field_type );

			$field_title = isset( $post_data['af_addon_field_title'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_title'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_field_title', $field_title );

			$tooltip_checkbox = isset( $post_data['af_addon_tooltip_checkbox'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_tooltip_checkbox'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_tooltip_checkbox', $tooltip_checkbox );

			$tooltip_textarea = isset( $post_data['af_addon_tooltip_textarea'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_tooltip_textarea'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_tooltip_textarea', $tooltip_textarea );

			$desc_checkbox = isset( $post_data['af_addon_desc_checkbox'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_desc_checkbox'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_desc_checkbox', $desc_checkbox );

			$desc_textarea = isset( $post_data['af_addon_desc_textarea'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_desc_textarea'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_desc_textarea', $desc_textarea );

			$req_field = isset( $post_data['af_addon_required_field'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_required_field'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_required_field', $req_field );

			$limit_range_checkbox = isset( $post_data['af_addon_limit_range_checkbox'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_limit_range_checkbox'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_limit_range_checkbox', $limit_range_checkbox );

			$min_limit_range = isset( $post_data['af_addon_min_limit_range'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_min_limit_range'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_min_limit_range', $min_limit_range );

			$max_limit_range = isset( $post_data['af_addon_max_limit_range'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_max_limit_range'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_max_limit_range', $max_limit_range );

			$price_range_checkbox = isset( $post_data['af_addon_price_range_checkbox'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_price_range_checkbox'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_price_range_checkbox', $price_range_checkbox );

			$field_price_type = isset( $post_data['af_addon_field_price_type'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_price_type'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_field_price_type', $field_price_type );

			$field_price = isset( $post_data['af_addon_field_price'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_price'][ $field_id ] ) ) : '';
			update_post_meta( $field_id, 'af_addon_field_price', $field_price );

			$af_addon_upload_file_extention = isset( $post_data['af_addon_upload_file_extention'][ $field_id ] ) ? sanitize_text_field( $post_data['af_addon_upload_file_extention'][ $field_id ] ) : 'jpg';
			update_post_meta( $field_id, 'af_addon_upload_file_extention', $af_addon_upload_file_extention );

			$options = get_posts(
				array(
					'post_type'   => 'af_pao_options',
					'numberposts' => -1,
					'post_status' => 'publish',
					'post_parent' => $field_id,
					'fields'      => 'ids',
				)
			);

			foreach ( $options as $option_id ) {

				$option_priority = isset( $post_data['af_addon_option_priority'][ $field_id ][ $option_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_option_priority'][ $field_id ][ $option_id ] ) ) : '';
				update_post_meta( $option_id, 'af_addon_option_priority', $option_priority );

				$update_options_priority = wp_update_post(
					array(
						'post_type'   => 'af_pao_options',
						'numberposts' => -1,
						'post_status' => 'publish',
						'ID'          => $option_id,
						'menu_order'  => $option_priority,
					)
				);

				$image = isset( $post_data['af_addon_field_options_image'][ $field_id ][ $option_id ] ) ? sanitize_text_field( $post_data['af_addon_field_options_image'][ $field_id ][ $option_id ] ) : '';
				update_post_meta( $option_id, 'af_addon_field_options_image', $image );

				if ( ! empty( $image ) ) {

					$img_filetype = wp_check_filetype( $image, null );
					$mime_type    = $img_filetype['type'];

					$attachment = array(
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $image ) ),
						'post_name'      => preg_replace( '/\.[^.]+$/', '', basename( $image ) ),
						'post_type'      => 'af_pao_options',
						'post_status'    => 'publish',
						'post_parent'    => $option_id,
					);

					$attachment_id = wp_insert_attachment( $attachment, $image, $option_id, true );

					if ( is_a( $attachment_id, 'WP_Error' ) ) {
						echo esc_attr( $attachment_id );
						exit;
					}

					if ( 0 != $attachment_id ) {

						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $image );

						wp_update_attachment_metadata( $attachment_id, $image );

						update_post_meta( $option_id, '_thumbnail_id', $attachment_id );

					}
				}

				$name = isset( $post_data['af_addon_field_options_name'][ $field_id ][ $option_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_options_name'][ $field_id ][ $option_id ] ) ) : '';
				update_post_meta( $option_id, 'af_addon_field_options_name', $name );

				$color = isset( $post_data['af_addon_option_color'][ $field_id ][ $option_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_option_color'][ $field_id ][ $option_id ] ) ) : '';
				update_post_meta( $option_id, 'af_addon_option_color', $color );

				$price_type = isset( $post_data['af_addon_field_options_price_type'][ $field_id ][ $option_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_options_price_type'][ $field_id ][ $option_id ] ) ) : 'af_addon_flat_fee';
				update_post_meta( $option_id, 'af_addon_field_options_price_type', $price_type );

				$price = isset( $post_data['af_addon_field_options_price'][ $field_id ][ $option_id ] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_field_options_price'][ $field_id ][ $option_id ] ) ) : '';
				update_post_meta( $option_id, 'af_addon_field_options_price', $price );

			}
		}
	}

	public function af_product_addon_tab( $tabs ) {

		$tabs['af_pao_add_on_tab'] = array(
			'label'    => __( 'Add-Ons', 'addify_pao' ), // Navigation Label Name.
			'target'   => 'af_pao_prod_tab', // The HTML ID of the tab content wrapper.
			'class'    => array(),
			'priority' => 100,
		);

		return $tabs;
	}

	public function af_product_data_panels() {

		$current_prod_id = get_the_ID();

		wp_nonce_field( 'af_prod_metabox_nonce', 'af_prod_metabox_nonce_field' );
		?>

		<div id="af_pao_prod_tab" class='panel woocommerce_options_panel af-pao-div-whole-data' style="padding: 15px;">

			<?php
				$this->af_addon_product_style( $current_prod_id );

				$this->af_addon_product_fields( $current_prod_id );
			?>
	
		</div>
		<?php
	}

	public function af_addon_product_style( $current_prod_id ) {

		?>
		<div class="af_addon_field_div" style="margin-bottom: 10px; background: none;">
			<div class="prod_style_heading_div">
				<div style="text-align: left;" class="af_pao_width_50">
					<b><i><?php echo esc_html__( 'Style', 'addify_pao' ); ?></i></b>
				</div>
				<div style="text-align: right;" class="af_pao_width_50">
					<button class="fa fa-solid fa-angle-up style_up"></button>
					<button class="fa fa-solid fa-angle-down style_down"></button>
				</div>
			</div>
			<div class="prod_style_tab_main_class" style="padding-top: 20px; background: none;">

				<div class="af_addon_title_display_as_selector_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Title display as', 'addify_pao' ); ?></label>
						<select name="af_addon_title_display_as_selector" class="af_addon_title_display_as_selector" style="height: unset; width: 50%;">

							<option value="af_addon_title_display_text"
								<?php
								if ( 'af_addon_title_display_text' == get_post_meta( $current_prod_id, 'af_addon_title_display_as_selector', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'text', 'addify_pao' ); ?>
							</option>
							<option value="af_addon_title_display_heading"
								<?php
								if ( 'af_addon_title_display_heading' == get_post_meta( $current_prod_id, 'af_addon_title_display_as_selector', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'heading', 'addify_pao' ); ?>
							</option>
							<option value="af_addon_title_display_none"
								<?php
								if ( 'af_addon_title_display_none' == get_post_meta( $current_prod_id, 'af_addon_title_display_as_selector', true ) ) {
									echo 'selected';
								}
								?>
								 
							>
								<?php esc_html_e( 'none', 'addify_pao' ); ?>
							</option>
						</select>
						<span><?php echo wp_kses_post( wc_help_tip( 'Specify the field title type as heading, paragraph text or select none to don’t display option title.' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_heading_type_selector_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Heading', 'addify_pao' ); ?></label>
						<select name="af_addon_heading_type_selector" class="af_addon_heading_type_selector" style="height: unset; width: 50%;">
							<option value="addon_h1"
								<?php
								if ( 'addon_h1' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h1', 'addify_pao' ); ?></option>

							<option value="addon_h2"
								<?php
								if ( 'addon_h2' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h2', 'addify_pao' ); ?></option>
							<option value="addon_h3"
								<?php
								if ( 'addon_h3' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h3', 'addify_pao' ); ?></option>
							<option value="addon_h4"
								<?php
								if ( 'addon_h4' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h4', 'addify_pao' ); ?></option>
							<option value="addon_h5"
								<?php
								if ( 'addon_h5' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h5', 'addify_pao' ); ?></option>
							<option value="addon_h6"
								<?php
								if ( 'addon_h6' == get_post_meta( $current_prod_id, 'af_addon_heading_type_selector', true ) ) {
									echo 'selected';
								}
								?>
							><?php echo esc_html__( 'h6', 'addify_pao' ); ?></option>
						</select>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select the heading type of which you want to show the title' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_title_font_size_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Title font size', 'addify_pao' ); ?></label>
						<?php
						if ( empty( get_post_meta( $current_prod_id, 'af_addon_title_font_size', true ) ) ) {

							update_post_meta( $current_prod_id, 'af_addon_title_font_size', 14 );
						}
						?>
						<input type="number" name="af_addon_title_font_size" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_font_size', true ) ); ?>">
						<span><?php echo wp_kses_post( wc_help_tip( 'Select the heading type of which you want to show the title. Size will be in px. Default will be 13px' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_title_color_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Title color', 'addify_pao' ); ?></label>
						<input type="text" name="af_addon_title_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_color', true ) ); ?>" class="my-color-field" data-default-color="#FFFFFF" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select Title text color of your choice' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_title_bg_checkbox_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Title background', 'addify_pao' ); ?></label>
						<input type="checkbox" name="af_addon_title_bg" value="1" class="af_addon_title_bg_class"
							<?php
							if ( '1' == get_post_meta( $current_prod_id, 'af_addon_title_bg', true ) ) {
								echo 'checked';
							}
							?>
						>
						<span><?php esc_html_e( ' Add background', 'addify_pao' ); ?></span>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select if you want to add backdround in title' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_title_bg_color_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Background color', 'addify_pao' ); ?></label>
						<input type="text" name="af_addon_bg_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_bg_color', true ) ); ?>" class="my-color-field" data-default-color="#FFFFFF" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select background color of your choice' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_title_bg_radius_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Background radius', 'addify_pao' ); ?></label>

						<input type="number" name="af_addon_title_top_left_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_top_left_radius', true ) ); ?>" min="0" placeholder="Top left radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top left radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_top_right_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_top_right_radius', true ) ); ?>" min="0" placeholder="Top right radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top right radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_bottom_left_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_bottom_left_radius', true ) ); ?>" min="0" placeholder="Bottom left radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom left radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_bottom_right_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_bottom_right_radius', true ) ); ?>" min="0" placeholder="Bottom right radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom right radius' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_title_bg_padding_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Background padding', 'addify_pao' ); ?></label>

						<input type="number" name="af_addon_title_top_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_top_padding', true ) ); ?>" min="0" placeholder="Top padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_bottom_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_bottom_padding', true ) ); ?>" min="0" placeholder="bottom padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_left_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_left_padding', true ) ); ?>" min="0" placeholder="left padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter left padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_title_right_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_title_right_padding', true ) ); ?>" min="0" placeholder="right padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter right padding' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_add_Seperator_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Add seperator', 'addify_pao' ); ?></label>
						<input type="checkbox" name="af_addon_seperator_checkbox" value="1" class="af_addon_seperator_checkbox_class"
							<?php
							if ( '1' == get_post_meta( $current_prod_id, 'af_addon_seperator_checkbox', true ) ) {
								echo 'checked';
							}
							?>
						>
						<span><?php esc_html_e( 'Add Seperator', 'addify_pao' ); ?></span>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select if you want to add seperaotr after title' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_seperator_selector_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Seperator', 'addify_pao' ); ?></label>
						<select name="af_addon_title_seperator" class="af_addon_title_seperator" style="height: unset; width: unset;">
							<option value="af_addon_title_seperator_br"
								<?php
								if ( 'af_addon_title_seperator_br' == get_post_meta( $current_prod_id, 'af_addon_title_seperator', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'Single line break', 'addify_pao' ); ?>
							</option>
							<option value="af_addon_title_seperator_hr"
								<?php
								if ( 'af_addon_title_seperator_hr' == get_post_meta( $current_prod_id, 'af_addon_title_seperator', true ) ) {
									echo 'selected';
								}
								?>
								 
							>
								<?php esc_html_e( 'Thematic break', 'addify_pao' ); ?>
							</option>
						</select>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select the seperator. By default, the seperator will be single line break' ) ); ?></span>
					</p>
				</div>

				<div>
					<p class="form-field">
						<label><?php esc_html_e( 'Options title font size', 'addify_pao' ); ?></label>
						<?php
						if ( empty( get_post_meta( $current_prod_id, 'af_addon_option_title_font_size', true ) ) ) {
							update_post_meta( $current_prod_id, 'af_addon_option_title_font_size', 12 );
						}
						?>
						<input type="number" name="af_addon_option_title_font_size" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_option_title_font_size', true ) ); ?>" placeholder="px">
						<span><?php echo wp_kses_post( wc_help_tip( 'Default will be 13px. Select your desired font size for options title. This size will only apply on checkboxes, radio buttons & drop down fields' ) ); ?></span>
					</p>
				</div>

				<div>
					<p class="form-field">
						<label><?php esc_html_e( 'Options title font color', 'addify_pao' ); ?></label>
						
						<input type="text" name="af_addon_option_title_font_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_option_title_font_color', true ) ); ?>" class="my-color-field" data-default-color="#FFFFFF" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select your desired Options font color. This colour will only apply on checkboxes, radio buttons & drop down fields' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_field_border_checkbox_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Field border', 'addify_pao' ); ?></label>
						
						<input type="checkbox" name="af_addon_field_border" value="1" class="af_addon_field_border_class"
							<?php
							if ( '1' == get_post_meta( $current_prod_id, 'af_addon_field_border', true ) ) {
								echo 'checked';
							}
							?>
						>
						<span><?php echo esc_html( 'Field border' ); ?></span>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select if you want to set the border on field' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_field_border_pixels_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Border pixels', 'addify_pao' ); ?></label>
						<?php
						if ( empty( get_post_meta( $current_prod_id, 'af_addon_field_border_pixels', true ) ) ) {
							$af_addon_field_border_pixels = 1;
							update_post_meta( $current_prod_id, 'af_addon_field_border_pixels', $af_addon_field_border_pixels );
						}
						?>
						<input type="number" name="af_addon_field_border_pixels" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_pixels', true ) ); ?>">
						<span><?php echo wp_kses_post( wc_help_tip( 'Select if you want to set the border pixels field. In case of empty, default pixel will be 1 px' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_field_border_color_div_id">
					<p class="form-field">
						<label><?php esc_html_e( 'Border color', 'addify_pao' ); ?></label>
						
						<input type="text" name="af_addon_field_border_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_color', true ) ); ?>" class="my-color-field" data-default-color="#FFFFFF" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select if you want to set the border on field' ) ); ?></span>
					</p>
				</div>

				<div class="af_addon_field_border_radius_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Field border radius', 'addify_pao' ); ?></label>

						<input type="number" name="af_addon_field_border_top_left_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_top_left_radius', true ) ); ?>" min="0" placeholder="Top left radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top left radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_top_right_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_top_right_radius', true ) ); ?>" min="0" placeholder="top right radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top right radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_bottom_left_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_bottom_left_radius', true ) ); ?>" min="0" placeholder="bottom left radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom left radius' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_bottom_right_radius" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_bottom_right_radius', true ) ); ?>" min="0" placeholder="bottom right radius">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom right radius' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_field_border_padding_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Field inside padding', 'addify_pao' ); ?></label>

						<input type="number" name="af_addon_field_border_top_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_top_padding', true ) ); ?>" min="0" placeholder="Top padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter top padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_bottom_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_bottom_padding', true ) ); ?>" min="0" placeholder="bottom padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter bottom padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_left_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_left_padding', true ) ); ?>" min="0" placeholder="left padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter left padding' ) ); ?></span>
						<br>
						<br>
						<input type="number" name="af_addon_field_border_right_padding" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_field_border_right_padding', true ) ); ?>" min="0" placeholder="right padding">
						<span><?php echo wp_kses_post( wc_help_tip( 'Enter right padding' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_title_position_inside_main_div_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Field title position', 'addify_pao' ); ?></label>

						<select name="af_addon_field_title_position" class="af_addon_field_title_position" style="height: unset; width: unset;">
							<option value="left"
								<?php
								if ( 'left' == get_post_meta( $current_prod_id, 'af_addon_field_title_position', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'Left', 'addify_pao' ); ?>
							</option>
							<option value="right"
								<?php
								if ( 'right' == get_post_meta( $current_prod_id, 'af_addon_field_title_position', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'Right', 'addify_pao' ); ?>
							</option>
							<option value="center"
								<?php
								if ( 'center' == get_post_meta( $current_prod_id, 'af_addon_field_title_position', true ) ) {
									echo 'selected';
								}
								?>
								 
							>
								<?php esc_html_e( 'Center', 'addify_pao' ); ?>
							</option>
						</select>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select title position' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_title_position_div_id">
					<p class="form-field">

						<label><?php esc_html_e( 'Title position', 'addify_pao' ); ?></label>

						<select name="af_addon_title_position">
							<option value="af_addon_title_inside_border"
								<?php
								if ( 'af_addon_title_inside_border' == get_post_meta( $current_prod_id, 'af_addon_title_position', true ) ) {
									echo 'selected';
								}
								?>
							>
								<?php esc_html_e( 'Inside border', 'addify_pao' ); ?>
							</option>
							<option value="af_addon_title_outside_border"
								<?php
								if ( 'af_addon_title_outside_border' == get_post_meta( $current_prod_id, 'af_addon_title_position', true ) ) {
									echo 'selected';
								}
								?>
								 
							>
								<?php esc_html_e( 'Outside border', 'addify_pao' ); ?>
							</option>
						</select>
						<span><?php echo wp_kses_post( wc_help_tip( 'Select title position' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_desc_font_size_div_id">

					<p class="form-field">

						<label><?php esc_html_e( 'Description font size', 'addify_pao' ); ?></label>
						<?php
						if ( empty( get_post_meta( $current_prod_id, 'af_addon_desc_font_size', true ) ) ) {
							$af_addon_desc_font_size = 12;
							update_post_meta( $current_prod_id, 'af_addon_desc_font_size', $af_addon_desc_font_size );
						}
						?>
						<input type="number" name="af_addon_desc_font_size" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_desc_font_size', true ) ); ?>">
						<span><?php echo wp_kses_post( wc_help_tip( 'Default value will be 12 px. Size will be in px', 'addify_pao' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_tooltip_bg_color_div_id">

					<p class="form-field">

						<label><?php esc_html_e( 'Tool tip background color', 'addify_pao' ); ?></label>
						<input type="text" name="af_addon_tooltip_background_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_tooltip_background_color', true ) ? get_post_meta( $current_prod_id, 'af_addon_tooltip_background_color', true ) : '#000000' ); ?>" class="my-color-field" data-default-color="#000000" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select tooltip background color' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_tooltip_bg_color_div_id">

					<p class="form-field">

						<label><?php esc_html_e( 'Tool tip text color', 'addify_pao' ); ?></label>
						<input type="text" name="af_addon_tooltip_text_color" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_tooltip_text_color', true ) ); ?>" class="my-color-field" data-default-color="#FFFFFF" />
						<span><?php echo wp_kses_post( wc_help_tip( 'Select tooltip text color' ) ); ?></span>

					</p>
				</div>

				<div class="af_addon_tooltip_bg_color_div_id">

					<p class="form-field">

						<label><?php esc_html_e( 'Tool tip font size', 'addify_pao' ); ?></label>

						<?php
						if ( empty( get_post_meta( $current_prod_id, 'af_addon_tooltip_font_size', true ) ) ) {
							update_post_meta( $current_prod_id, 'af_addon_tooltip_font_size', 12 );
						}
						?>
						<input type="number" name="af_addon_tooltip_font_size" value="<?php echo esc_attr( get_post_meta( $current_prod_id, 'af_addon_tooltip_font_size', true ) ); ?>" placeholder="px">
						<span><?php echo wp_kses_post( wc_help_tip( 'Size will be in px. In case of empty, default value will be 12px' ) ); ?></span>

					</p>
				</div>

			</div>
		</div>
		<?php
	}

	public function af_addon_product_fields( $current_prod_id ) {

		?>
		<div class="af_addon_expand_close_btn_div">
			<input type="submit" name="af_addon_expand_all_btn" class="af_addon_expand_all_btn" value="<?php echo esc_html__( 'Expand all', 'addify_pao' ); ?>">
			<span><?php echo esc_html__( '/', 'addify_pao' ); ?></span>
			<input type="submit" name="af_addon_close_all_btn" class="af_addon_close_all_btn" value="<?php echo esc_html__( 'Close all', 'addify_pao' ); ?>">
		</div>
		<br>
		<div class="af_addon_reload_main_div">
			<?php

			$args = array(
				'post_type'   => 'af_pao_fields',
				'post_status' => 'publish',
				'post_parent' => $current_prod_id,
				'numberposts' => -1,
				'fields'      => 'ids',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			);

			$fields = get_posts( $args );

			foreach ( $fields as $field_id ) {
				if ( empty( $field_id ) ) {
					continue;
				}
				?>
				<div class="af_addon_class">
					<div class="af_addon_field_div">
						<div class="af_addon_field_open_close_div">
							<div class="af_addon_title_heading_div">
								<p>
									<b><i><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?></i></b>
									<?php
									$type_selected = array(
										'drop_down'      => 'Drop-down',
										'multi_select'   => 'Multi-select',
										'check_boxes'    => 'Checkboxes',
										'input_text'     => 'Input Text',
										'textarea'       => 'Textarea',
										'file_upload'    => 'File Upload',
										'number'         => 'Number',
										'radio'          => 'Radio',
										'color_swatcher' => 'Color',
										'image_swatcher' => 'Image Swatcher',
										'image'          => 'Image',
										'date_picker'    => 'Date',
										'email'          => 'Email',
										'password'       => 'Password',
										'time_picker'    => 'Time Picker',
										'telephone'      => 'Telephone',
									);
									$selector_key  = get_post_meta( $field_id, 'af_addon_type_select', true );
									if ( $selector_key ) {
										?>
										<i style="opacity: 0.7"> <?php echo esc_attr( $type_selected[ $selector_key ] ); ?> </i>
										<?php
									}
									?>
								</p>
							</div>
							<div class="af_addon_remove_btn_div">
								<label style="vertical-align: text-bottom; float: inherit;"><?php echo esc_html__( 'Priority', 'addify_pao' ); ?></label>
								<input type="number" min="0" name="af_addon_field_sort[<?php echo esc_attr( $field_id ); ?>]" style="height: 36px; width: 20%; vertical-align: top; float: revert;" placeholder="0" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_sort', true ) ); ?>">
								<button class="af_addon_remove_btn af_addon_remove_btn_<?php echo intval( $field_id ); ?>" data-remove_field_id="<?php echo intval( $field_id ); ?>" data-current_post_id="<?php echo intval( $current_prod_id ); ?>" >
									<?php echo esc_html__( 'Remove', 'addify_pao' ); ?>
								</button>

								<button class="fa fa-sort-up <?php echo intval( $field_id ); ?>fa-sort-up" data-field_id="<?php echo intval( $field_id ); ?>"></button>

								<button class="fa fa-sort-down <?php echo intval( $field_id ); ?>fa-sort-down" data-field_id="<?php echo intval( $field_id ); ?>"></button>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_dependable_div af_addon_dependable_div">
							<div class="af_addon_dependable_fields_div">
								<div class="af_addon_dependable_selector_div">
									<p style="margin: unset;">
										<b><?php echo esc_html__( 'Field Dependability', 'addify_pao' ); ?></b>
										<span>
											<?php echo wp_kses_post( wc_help_tip( 'Select if you want to make field dependable' ) ); ?>
										</span>
									</p>
									<select name="af_addon_depend_selector[<?php echo intval( $field_id ); ?>]" class="af_pao_width_100_height_40 <?php echo intval( $field_id ); ?>_af_addon_depend_selector af_addon_depend_selector" data-current_field_id="<?php echo intval( $field_id ); ?>">
										<option value="af_addon_not_dependable"
											<?php
											if ( 'af_addon_not_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ) {
												echo 'selected';
											}
											?>
										>
											<?php echo esc_html__( 'Not dependable', 'addify_pao' ); ?>
										</option>
										<option value="af_addon_dependable"
											<?php
											if ( 'af_addon_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ) {
												echo 'selected';
											}
											?>
										>
											<?php echo esc_html__( 'Dependable', 'addify_pao' ); ?>
										</option>
									</select>
								</div>

								<div class="<?php echo intval( $field_id ); ?>_af_addon_field_depend_selector_div af_addon_field_depend_selector_div">
									<p style="margin: unset;">
										<b> <?php echo esc_html__( 'Field', 'addify_pao' ); ?> </b>
										<span>
											<?php echo wp_kses_post( wc_help_tip( 'Select existing field to make this field dependable to. Field can only be made dependable to multi choice, Dropdown, Checkboxes, Radio Buttons, Images and Image Switcher.' ) ); ?>
										</span>
									</p>
									
									<select style="height: 40px; width: 100%;" name="af_addon_field_depend_selector[<?php echo intval( $field_id ); ?>]" class="af_addon_field_depend_selector" data-current_field_id="<?php echo intval( $field_id ); ?>" placeholder="Select Field">
										<option value="0"><?php echo esc_html( '' ); ?></option>
										<?php
										$current_field_id = $field_id;
										$args             = array(
											'post_type'   => 'af_pao_fields',
											'post_status' => 'all',
											'numberposts' => -1,
											'post_parent' => $current_prod_id,
											'fields'      => 'ids',
										);

										$fields = get_posts( $args );

										foreach ( $fields as $field_ids ) {
											if ( empty( $field_ids ) ) {
												continue;
											}
											if ( $current_field_id != $field_ids ) {
												$field_type = get_post_meta( $field_ids, 'af_addon_type_select', true );

												if ( 'drop_down' == $field_type || 'multi_select' == $field_type || 'check_boxes' == $field_type || 'radio' == $field_type || 'color_swatcher' == $field_type || 'image_swatcher' == $field_type || 'image' == $field_type ) {
													?>
													<option value="<?php echo intval( $field_ids ); ?>"
														<?php
														if ( get_post_meta( $field_id, 'af_addon_field_depend_selector', true ) == $field_ids ) {
															echo 'selected';
														}
														?>
														>
														<?php
														echo esc_attr( get_post_meta( $field_ids, 'af_addon_field_title', true ) );
														?>
													</option>
													<?php
												}
											}
										}
										?>
									</select>
								</div>
								<div class="<?php echo intval( $field_id ); ?>_af_addon_option_depend_selector_div af_addon_option_depend_selector_div">
									<p style="margin: unset;">
										<b>
											<?php echo esc_html__( 'Options', 'addify_pao' ); ?>
										</b>
										<span>
											<?php echo wp_kses_post( wc_help_tip( 'Select the field option against which this field will trigger.' ) ); ?>
										</span>
									</p>
									<?php
									$af_addon_option_depend_selector = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );
									?>
									<select style="height: 40px; width: 100%;" name="af_addon_option_depend_selector[<?php echo intval( $field_id ); ?>][]" multiple="multiple" class="af_addon_option_depend_selector <?php echo intval( $field_id ); ?>_af_addon_option_depend_selector " data-current_field_id="<?php echo intval( $field_id ); ?>" placeholder="Select Option">
											<?php
											foreach ( $af_addon_option_depend_selector as $selected_fields_option_id ) {
												if ( empty( $selected_fields_option_id ) ) {
													continue;
												}
												?>
												<option value="<?php echo intval( $selected_fields_option_id ); ?>"
													<?php
														echo in_array( $selected_fields_option_id, $af_addon_option_depend_selector ) ? esc_html__( 'selected' ) : '';
													?>
													>
													<?php
													echo esc_attr( get_post_meta( $selected_fields_option_id, 'af_addon_field_options_name', true ) );
													?>
												</option>
												<?php
											}
											?>
									</select>
								</div>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_type_and_title_div af_addon_type_and_title_div" >
							<div class="<?php echo intval( $field_id ); ?>_af_addon_type_div af_addon_type_div">
								<p style="margin: unset;">
									<b><?php echo esc_html__( 'Type', 'addify_pao' ); ?></b>
								</p>
								<select name="af_addon_type_select[<?php echo intval( $field_id ); ?>]" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_type_select[<?php echo intval( $field_id ); ?>]" class=" <?php echo intval( $field_id ); ?>_af_addon_field_type_selector af_addon_field_type_selector">
									<option value="drop_down"
										<?php
										if ( 'drop_down' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Drop-down', 'addify_pao' ); ?></option>
									<option value="multi_select"
										<?php
										if ( 'multi_select' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Multi-select', 'addify_pao' ); ?></option>
									<option value="check_boxes"
										<?php
										if ( 'check_boxes' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Checkboxes', 'addify_pao' ); ?></option>
									<option value="input_text"
										<?php
										if ( 'input_text' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Input Text', 'addify_pao' ); ?></option>
									<option value="textarea"
										<?php
										if ( 'textarea' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Textarea', 'addify_pao' ); ?></option>
									<option value="file_upload"
										<?php
										if ( 'file_upload' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'File Upload', 'addify_pao' ); ?></option>
									<option value="number"
										<?php
										if ( 'number' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Number', 'addify_pao' ); ?></option>
									<option value="radio"
										<?php
										if ( 'radio' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Radio', 'addify_pao' ); ?></option>
									<option value="color_swatcher"
										<?php
										if ( 'color_swatcher' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Color', 'addify_pao' ); ?></option>
									<option value="image_swatcher"
										<?php
										if ( 'image_swatcher' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Image Swatcher', 'addify_pao' ); ?></option>
									<option value="image"
										<?php
										if ( 'image' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Image', 'addify_pao' ); ?></option>
									<option value="date_picker"
										<?php
										if ( 'date_picker' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Date Picker', 'addify_pao' ); ?></option>
									<option value="email"
										<?php
										if ( 'email' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Email', 'addify_pao' ); ?></option>
									<option value="password"
										<?php
										if ( 'password' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Password', 'addify_pao' ); ?></option>
									<option value="time_picker"
										<?php
										if ( 'time_picker' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Time Picker', 'addify_pao' ); ?></option>
									<option value="telephone"
										<?php
										if ( 'telephone' == get_post_meta( $field_id, 'af_addon_type_select', true ) ) {
											echo 'selected';
										}
										?>
									><?php echo esc_html__( 'Telephone', 'addify_pao' ); ?></option>
								</select>
							</div>
							<div class="<?php echo intval( $field_id ); ?>_af_addon_title_div af_addon_title_div">
								<p style="margin: unset;">
									<b><?php echo esc_html__( 'Title', 'addify_pao' ); ?></b>
								</p>
								<input type="text" name="af_addon_field_title[<?php echo intval( $field_id ); ?>]" class="af_addon_title_field " data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_title[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?>">
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_tooltip_div af_addon_tooltip_div">
							<div class="af_pao_width_100">
								<input type="hidden" name="af_addon_tooltip_id" value="<?php echo intval( $field_id ); ?>">
								<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_tooltip_checkbox<?php echo intval( $field_id ); ?>" type="checkbox" name="af_addon_tooltip_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_tooltip_checkbox af_addon_tooltip_checkbox<?php echo intval( $field_id ); ?>"
								<?php
								if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {
									echo 'checked';
								}
								?>
								>
								<span><b><?php echo esc_html__( 'Add Tool Tip?', 'addify_pao' ); ?></b></span>
								<textarea name="af_addon_tooltip_textarea[<?php echo intval( $field_id ); ?>]" class=" tooltip_text_area<?php echo intval( $field_id ); ?> tooltip_text_area"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></textarea>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_desc_div af_addon_desc_div">
							<div class="af_pao_width_100">
								<input type="hidden" name="af_addon_desc_id" value="<?php echo intval( $field_id ); ?>">
								<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_desc_checkbox<?php echo intval( $field_id ); ?>" type="checkbox" name="af_addon_desc_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_desc_checkbox af_addon_desc_checkbox<?php echo intval( $field_id ); ?>"
								<?php
								if ( '1' == get_post_meta( $field_id, 'af_addon_desc_checkbox', true ) ) {
									echo 'checked';
								}
								?>
								>
								<span><b><?php echo esc_html__( 'Add Description?', 'addify_pao' ); ?></b></span>
								<textarea name="af_addon_desc_textarea[<?php echo intval( $field_id ); ?>]" class=" desc_text_area<?php echo intval( $field_id ); ?> desc_text_area"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?></textarea>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_req_div af_addon_req_div">
							<div class="af_pao_width_100">
							<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_required_field[<?php echo intval( $field_id ); ?>]" type="checkbox" class="" name="af_addon_required_field[<?php echo intval( $field_id ); ?>]" value="1"
								<?php
								if ( '1' == get_post_meta( $field_id, 'af_addon_required_field', true ) ) {
									echo 'checked';
								}
								?>
							>
							<span><b><?php echo esc_html__( 'Required Field?', 'addify_pao' ); ?></b></span>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_limit_range_div af_addon_limit_range_div">
							<div class="af_pao_width_100">
								<input type="hidden" name="af_addon_limit_range_id" class="af_addon_limit_range_id" value="<?php echo intval( $field_id ); ?>">
								<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_limit_range_checkbox[<?php echo intval( $field_id ); ?>]"  type="checkbox" name="af_addon_limit_range_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_limit_range_checkbox af_addon_limit_range_checkbox<?php echo intval( $field_id ); ?>"
									<?php
									if ( '1' == get_post_meta( $field_id, 'af_addon_limit_range_checkbox', true ) ) {
										echo 'checked';
									}
									?>
								>
								<b><?php echo esc_html__( 'Limit Range', 'addify_pao' ); ?></b>
								<span><b><?php echo wp_kses_post( wc_help_tip( 'Enter a minimum and maximum value for the limit range. Only max length will be applied on field type Telephone, which will be fixed length for telephone' ) ); ?></b></span>
								
								<div class="af_addon_limit_range_divs af_addon_limit_range_divs<?php echo intval( $field_id ); ?>" data-rule_check="rule">
									<div class="af_pao_width_100">
										<input type="number" name="af_addon_min_limit_range[<?php echo intval( $field_id ); ?>]" placeholder="0" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_min_limit_range', true ) ); ?>" style="height: 40px; width:40%; float: none !important;" min="0">

										<span><?php echo esc_html__( '--', 'addify_pao' ); ?></span>

										<input type="number" name="af_addon_max_limit_range[<?php echo intval( $field_id ); ?>]" placeholder="999" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_max_limit_range', true ) ); ?>" style="height: 40px; width:50%; float: none !important;" min="0">
									</div>

								</div>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_price_range_div af_addon_price_range_div">
							<div class="af_pao_width_100">
								<div class="af_addon_price_range_divs af_addon_price_range_divs<?php echo intval( $field_id ); ?>" data-rule_check="rule">
									<input type="hidden" name="af_addon_price_range_id" class="af_addon_price_range_id" value="<?php echo intval( $field_id ); ?>">
									<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_price_range_checkbox[<?php echo intval( $field_id ); ?>]"  type="checkbox" name="af_addon_price_range_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_price_range_checkbox af_addon_price_range_checkbox<?php echo intval( $field_id ); ?>"
									<?php
									if ( '1' == get_post_meta( $field_id, 'af_addon_price_range_checkbox', true ) ) {
										echo 'checked';
									}
									?>
									>
									<b><?php echo esc_html__( 'Price Range', 'addify_pao' ); ?></b>
									<span><b><?php echo wp_kses_post( wc_help_tip( 'Select price type and price' ) ); ?></b></span>
								</div>
								<div class="af_addon_type_price_div<?php echo intval( $field_id ); ?> af_addon_type_price_div">
									<div class="af_pao_width_100">
										<select data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_price_type[<?php echo intval( $field_id ); ?>]" name="af_addon_field_price_type[<?php echo intval( $field_id ); ?>]" class="af_addon_type_select " style="float: none !important;">
											<option value="free"
												<?php
												if ( 'free' == get_post_meta( $field_id, 'af_addon_field_price_type', true ) ) {
													echo 'selected';
												}
												?>
											><?php echo esc_html__( 'Free', 'addify_pao' ); ?></option>
											<option value="flat_fixed_fee"
												<?php
												if ( 'flat_fixed_fee' == get_post_meta( $field_id, 'af_addon_field_price_type', true ) ) {
													echo 'selected';
												}
												?>
											><?php echo esc_html__( 'Flat Fixed Fee', 'addify_pao' ); ?></option>
											<option value="flat_percentage_fee"
												<?php
												if ( 'flat_percentage_fee' == get_post_meta( $field_id, 'af_addon_field_price_type', true ) ) {
													echo 'selected';
												}
												?>
											><?php echo esc_html__( 'Flat percentage fee', 'addify_pao' ); ?></option>
											<option value="fixed_fee_based_on_quantity"
												<?php
												if ( 'fixed_fee_based_on_quantity' == get_post_meta( $field_id, 'af_addon_field_price_type', true ) ) {
													echo 'selected';
												}
												?>
											><?php echo esc_html__( 'Fixed fee based on quantity', 'addify_pao' ); ?></option>
											<option value="Percentage_fee_based_on_quantity"
												<?php
												if ( 'Percentage_fee_based_on_quantity' == get_post_meta( $field_id, 'af_addon_field_price_type', true ) ) {
													echo 'selected';
												}
												?>
											><?php echo esc_html__( 'Percentage fee based on quantity', 'addify_pao' ); ?></option>
										</select>
										<span><?php echo esc_html__( '--', 'addify_pao' ); ?></span>

										<input type="number" min="0" class="af_addon_field_price " name="af_addon_field_price[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_price', true ) ); ?>" placeholder="0.00" style="float: none !important;">
									</div>
									
								</div>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_file_extention_div af_addon_file_extention_div">
							<div class="af_pao_width_100">
								<div class="af_addon_file_extention<?php echo intval( $field_id ); ?> af_addon_file_extention">
									
									<p style="margin: unset;">
										<b><?php echo esc_html__( 'File Type', 'addify_pao' ); ?></b>
										<span><b><?php echo wp_kses_post( wc_help_tip( 'Enter file extention (Comma Seperated). e.g, jpg, jpeg etc' ) ); ?></b></span>
									</p>
									
									<div class="af_pao_width_100">
										<input type="text" class="af_addon_upload_file_extention" style="width: 98% !important" name="af_addon_upload_file_extention[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_upload_file_extention', true ) ); ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_option_table_div af_addon_option_table_div">
							<div class="<?php echo intval( $field_id ); ?>_af_addon_table_div af_addon_table_div">
								<table class="af_addon_option_table <?php echo intval( $field_id ); ?>_af_addon_option_table">
									<thead>
										<tr>
											<th><?php echo esc_html__( 'Option', 'addify_pao' ); ?></th>
											<th><?php echo esc_html__( 'Price Type', 'addify_pao' ); ?></th>
											<th><?php echo esc_html__( 'Price', 'addify_pao' ); ?></th>
											<th><?php echo esc_html__( 'Priority', 'addify_pao' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
											$args = array(
												'post_type' => 'af_pao_options',
												'post_status' => 'publish',
												'post_parent' => $field_id,
												'fields'  => 'ids',
												'orderby' => 'menu_order',
												'order'   => 'ASC',
											);

											$options = get_posts( $args );

											foreach ( $options as $option_id ) {
												if ( empty( $option_id ) ) {
													continue;
												}
												?>
												<tr id="af_addon_option_table_row" class="option_tr"  data-field_id_value="<?php echo intval( $field_id ); ?>" data-option_id_value="<?php echo intval( $option_id ); ?>">
													<input type="hidden" name="af_hidden_id" class="af_hidden_id" value="<?php echo intval( $option_id ); ?>">
													<input type="hidden" name="af_field_id" value="<?php echo intval( $field_id ); ?>">
													<td>
														<div class="af_addon_image_field">
															<div class="af_addon_image_div <?php echo intval( $field_id ); ?>_af_addon_image_div">
																<?php
																	$image = get_the_post_thumbnail_url( $option_id );
																	wp_enqueue_media();

																?>
																<button class="af_addon_add_image_btn <?php echo intval( $field_id ); ?>_af_addon_add_image_btn_<?php echo intval( $option_id ); ?>"
																	<?php if ( ! empty( $image ) ) : ?>
																		style = 'display: none;'
																	<?php endif ?>
																><i class="fa fa-solid fa-plus"></i><i class="fa fa-solid fa-image"></i></button>

																<input type="hidden" 
																class="af_addon_field_options_image[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" 
																value="<?php echo esc_url( $image ); ?>" 
																name="af_addon_field_options_image[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" 
																id="<?php echo intval( $field_id ); ?>af_addon_image_upload<?php echo intval( $option_id ); ?>" class="login_title">

																<img class="<?php echo intval( $field_id ); ?>af_addon_option_image<?php echo intval( $option_id ); ?> af_addon_option_image"  <?php if ( empty( $image ) ) : ?>
																	style = 'display: none;'
																<?php endif ?>  src="<?php echo esc_url( $image ); ?>"/>

																<span id="remove_option_image<?php echo intval( $option_id ); ?>"  class="remove_option_image fa fa-trash" <?php if ( empty( $image ) ) : ?>
																	style = 'display: none;'
																<?php endif ?>></span>
															</div>

															<div class="<?php echo intval( $field_id ); ?>_af_addon_option_color_div" style="padding: 5px;">
																<input type="text" name="af_addon_option_color[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_color', true ) ); ?>" class="my-color-field <?php echo intval( $field_id ); ?>_af_addon_option_color" data-default-color="#FFFFFF"
																>
															</div>

															<div class="af_addon_option_name_div <?php echo intval( $field_id ); ?>_af_addon_option_name_div">
																
																<input type="text" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" <?php echo intval( $field_id ); ?>_af_addon_option_name af_addon_option_name" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_name', true ) ); ?>" required>
															</div>
														</div>
													</td>

													<td>
														<div class="af_addon_price_type_div">
														<select data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class="af_addon_type_select ">
															<option value="free"
															<?php
															if ( 'free' == get_post_meta( $option_id, 'af_addon_field_options_price_type', true ) ) {
																echo 'selected';
															}
															?>
														><?php echo esc_html__( 'Free', 'addify_pao' ); ?></option>
														<option value="flat_fixed_fee"
															<?php
															if ( 'flat_fixed_fee' == get_post_meta( $option_id, 'af_addon_field_options_price_type', true ) ) {
																echo 'selected';
															}
															?>
														><?php echo esc_html__( 'Flat Fixed Fee', 'addify_pao' ); ?></option>
														<option value="flat_percentage_fee"
															<?php
															if ( 'flat_percentage_fee' == get_post_meta( $option_id, 'af_addon_field_options_price_type', true ) ) {
																echo 'selected';
															}
															?>
														><?php echo esc_html__( 'Flat percentage fee', 'addify_pao' ); ?></option>
														<option value="fixed_fee_based_on_quantity"
															<?php
															if ( 'fixed_fee_based_on_quantity' == get_post_meta( $option_id, 'af_addon_field_options_price_type', true ) ) {
																echo 'selected';
															}
															?>
														><?php echo esc_html__( 'Fixed fee based on quantity', 'addify_pao' ); ?></option>
														<option value="Percentage_fee_based_on_quantity"
															<?php
															if ( 'Percentage_fee_based_on_quantity' == get_post_meta( $option_id, 'af_addon_field_options_price_type', true ) ) {
																echo 'selected';
															}
															?>
														><?php echo esc_html__( 'Percentage fee based on quantity', 'addify_pao' ); ?></option>
														</select>
													</td>
													<td>
														<input type="number" min="0" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" af_addon_field_options_price" name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_price', true ) ); ?>"
														>
													</td>
													<td>
														<input type="number" min="0" name="af_addon_option_priority[<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $option_id ); ?>]" style="height: 40px; width: 75%; vertical-align: top; float: left;" placeholder="Priority" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_priority', true ) ); ?>">
														<button class="af_addon_delete_btn af_addon_delete_btn_<?php echo intval( $option_id ); ?>" data-remove_option_id="<?php echo intval( $option_id ); ?>"data-current_post_id="<?php echo intval( $current_prod_id ); ?>"><?php echo esc_html__( 'X', 'addify_pao' ); ?></button>
													</td>
												</tr>
												<?php
											}
											?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="<?php echo intval( $field_id ); ?>_af_addon_add_optn_btn_div af_addon_add_optn_btn_div">
							<div class="af_pao_width_100">
								<button class="af_addon_add_option_btn" data-current_rule_id ="<?php echo intval( $current_prod_id ); ?> "   data-current_field_id ="<?php echo intval( $field_id ); ?>"  data-add_file_with ="rule" ><?php echo esc_html__( 'Add Option', 'addify_pao' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>

		<div class=" af_addon_import_div_rule">
			<div class="af_addon_import_div">
				<div class="af_pao_width_100">
					<div class="af_addon_add_field_button_div">
						<input type="button" name="af_addon_add_field_button" class="af_addon_add_field_button" value="Add Field" data-current_post_id ="<?php echo intval( $current_prod_id ); ?> " data-type="product">
					</div>
					<div class="af_addon_import_export_div">
						<input type="submit" name="af_addon_import_button" class="af_addon_import_button" value="<?php echo esc_html__( 'Import', 'addify_pao' ); ?>">
						<form>
							<?php wp_nonce_field( 'csv_export_form_nonce', 'csv_export_form_nonce_field' ); ?>
							<input type="submit" name="af_addon_export_button" class="af_addon_export_button" value="<?php echo esc_html__( 'Export', 'addify_pao' ); ?>">
							<input type="hidden" name="Export_data_of_current_rule" value="<?php echo intval( $current_prod_id ); ?>">
						</form>
					</div>
					<div class="af_pao_width_100">
						<div class="import_file_div">
							<form method="POST" enctype="multipart/form-data">
								<?php wp_nonce_field( 'csv_form_nonce', 'csv_form_nonce_field' ); ?>
								<input type="file" name="af_addon_import_file" class="af_addon_import_file ">
								<input type="submit" name="af_addon_done_import_button" class="af_addon_done_import_button" value="Done" data-current_post_id ="<?php echo intval( $current_prod_id ); ?> ">
								<input type="hidden" name="for_import_current_post_id" value="<?php echo intval( $current_prod_id ); ?>">
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<hr>

		<div>
			<h3><?php echo esc_html__( 'Global add-ons', 'addify_pao' ); ?></h3>

			<?php $url = admin_url( 'edit.php?post_type=af_addon' ); ?>

			<?php echo esc_html__( 'You can create global ', 'addify_pao' ); ?>
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html__( 'add-ons ', 'addify_pao' ); ?></a>
			<?php echo esc_html__( 'that apply to all products or to certain products, categories and tags.', 'addify_pao' ); ?>

			<p><b><?php echo esc_html__( 'Exclude add-ons', 'addify_pao' ); ?></b>
			&nbsp;
			<input type="checkbox" name="exclude_rule_addons" value="1"
				<?php
				if ( '1' == get_post_meta( $current_prod_id, 'exclude_rule_addons', true ) ) {
					echo 'checked';
				}
				?>
			>
			&nbsp;
			<i><?php echo esc_html__( 'Hide additional Global add-ons that may apply to this product.', 'addify_pao' ); ?></i></p>
		</div>
		<?php
	}
}

new Af_Addon_Product();
