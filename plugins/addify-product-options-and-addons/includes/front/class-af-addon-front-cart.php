<?php

/**
 * Cart Fucntions.
 */
class Af_Addon_Front_Cart {

	public function __construct() {

		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'addon_add_data_in_cart_menu' ), 10, 3 );

		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 20, 1 );

		add_filter( 'woocommerce_get_item_data', array( $this, 'addon_get_item_data_filter' ), 10, 2 );

		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 20, 2 );

		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'addon_checkout_create_order_line_item' ), 10, 4 );

		add_action( 'woocommerce_order_item_meta_start', array( $this, 'addon_show_option_name' ), 10, 3 );
	}

	public function addon_add_data_in_cart_menu( $cart_item_data, $product_id, $variation_id ) {

		$all_ids = array();

		$selector_data_add = array();

		$addon_rules = get_posts(
			array(
				'post_type'   => 'af_addon',
				'post_status' => 'publish',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		foreach ( $addon_rules as $rule ) {
			$all_ids[] = $rule;
		}

		$nonce = isset( $_POST['af_addon_front_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['af_addon_front_nonce_field'] ) ) : 0;

		if ( isset( $_POST['af_product_options_fields_availabel'] ) && ! wp_verify_nonce( $nonce, 'af_addon_front_nonce' ) ) {

			die( 'Failed Security check' );
		}

		$all_ids[] = $product_id;

		$all_ids[] = $variation_id;

		foreach ( $all_ids as $rule_id ) {

			$args = array(
				'post_type'   => 'af_pao_fields',
				'post_status' => 'publish',
				'numberposts' => -1,
				'post_parent' => $rule_id,
				'fields'      => 'ids',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			);

			$fields = get_posts( $args );

			foreach ( $fields as $field_id ) {

				if ( empty( $field_id ) ) {
					continue;
				}

				$addon_options = 'af_addons_options_' . $field_id;

				$field_type = get_post_meta( $field_id, 'af_addon_type_select', true );

				if ( 'file_upload' == $field_type ) {

					if ( isset( $_FILES[ $addon_options ] ) ) {

						$upload = wp_upload_dir();

						$upload_dir = $upload['basedir'];

						$upload_dir = $upload_dir . '/addify-product-addons/uploaded-files';

						if ( ! is_dir( $upload_dir ) ) {

							mkdir( $upload_dir );
							chmod( $upload_dir, 0777 );
						}

						$af_addon_uploaded_file = '';
						if ( isset( $_FILES[ $addon_options ]['tmp_name'] ) ) {
							$af_addon_uploaded_file = sanitize_text_field( $_FILES[ $addon_options ]['tmp_name'] );
						}

						$af_addon_uploaded_file_filename = '';

						if ( isset( $_FILES[ $addon_options ]['name'] ) ) {
							$af_addon_uploaded_file_filename = ( basename( sanitize_text_field( $_FILES[ $addon_options ]['name'] ) ) );
						}

						$af_addon_uploaded_file_file_type = strtolower( pathinfo( $af_addon_uploaded_file_filename, PATHINFO_EXTENSION ) );

						$uploaded_file_extention = get_post_meta( $field_id, 'af_addon_upload_file_extention', true );

						if ( ! empty( $uploaded_file_extention ) ) {

							$uploaded_file_extention_array = explode( ', ', strtolower( $uploaded_file_extention ) );

							if ( in_array( $af_addon_uploaded_file_file_type, $uploaded_file_extention_array, true ) ) {

								if ( empty( $_FILES[ $addon_options ]['error'] ) ) {

									$target_dir  = $upload['basedir'] . '/addify-product-addons/uploaded-files/';
									$target_file = $target_dir . basename( $af_addon_uploaded_file_filename );
									copy( $af_addon_uploaded_file, $target_file );

									$filename    = basename( $af_addon_uploaded_file_filename );
									$wp_filetype = wp_check_filetype( basename( $filename ), null );
									$url         = $upload_dir . '/' . $af_addon_uploaded_file_filename;

									$selector_data_add[ $field_id ] = array(
										'field_id'   => $field_id,
										'value'      => $af_addon_uploaded_file_filename,
										'field_type' => 'file_upload',
									);
									continue;
								}
							}
						} elseif ( empty( $_FILES[ $addon_options ]['error'] ) ) {


								$target_dir  = $upload['basedir'] . '/addify-product-addons/uploaded-files/';
								$target_file = $target_dir . basename( $af_addon_uploaded_file_filename );
								copy( $af_addon_uploaded_file, $target_file );

								$filename    = basename( $af_addon_uploaded_file_filename );
								$wp_filetype = wp_check_filetype( basename( $filename ), null );
								$url         = $upload_dir . '/' . $af_addon_uploaded_file_filename;

								$selector_data_add[ $field_id ] = array(
									'field_id'   => $field_id,
									'value'      => $af_addon_uploaded_file_filename,
									'field_type' => 'file_upload',
								);
								continue;
						}
					}
				}

				$value = '';
				if ( isset( $_POST[ $addon_options ] ) ) {
					$value = sanitize_text_field( wp_unslash( $_POST[ $addon_options ] ) );
				}

				if ( ! empty( $_POST[ $addon_options ] ) ) {
					$field_type = get_post_meta( $field_id, 'af_addon_type_select', true );
					if ( 'multi_select' == $field_type || 'check_boxes' == $field_type ) {

						$value = sanitize_meta( '', wp_unslash( $_POST[ $addon_options ] ), '' );
					}

					$selector_data_add[ $field_id ] = array(
						'field_id'   => $field_id,
						'value'      => $value,
						'field_type' => $field_type,
					);
				}
			}
		}

		

		$cart_item_data['selected_item_post_id'] = array( $selector_data_add );

		return $cart_item_data;
	}

	public function add_cart_item( $cart_item_data ) {

		$product_price = $cart_item_data['data']->get_price();
		$quantity      = $cart_item_data['quantity'];
		$pao_price     = 0;

		if ( in_array( 'selected_item_post_id', array_keys( $cart_item_data ) ) ) {

			$get_added_data = $cart_item_data['selected_item_post_id'];

			foreach ( $get_added_data as $arrays ) {

				if ( empty( $arrays ) || ! is_array( $arrays ) ) {
					continue;
				}

				foreach ( $arrays as $option_field_id_array ) {

					$field_type = $option_field_id_array['field_type'];

					if ( 'multi_select' == $field_type || 'check_boxes' == $field_type ) {

						$get_multi_select_val = (array) $option_field_id_array['value'];

						foreach ( $get_multi_select_val as $option_id_value ) {

							$pao_price += $this->added_fees( $option_id_value, $cart_item_data, $option_field_id_array['field_type'] );
						}
					} else {

						if ( 'drop_down' == $field_type || 'image' == $field_type || 'image_swatcher' == $field_type || 'color_swatcher' == $field_type || 'radio' == $field_type ) {

							$option_id_value = $option_field_id_array['value'];

						} else {

							$option_id_value = $option_field_id_array['field_id'];
						}
						$pao_price += $this->added_fees( $option_id_value, $cart_item_data, $option_field_id_array['field_type'] );
					}
				}
				if ( $pao_price >= 0.1 ) {
					$cart_item_data['data']->set_price( $pao_price + $product_price );
				}
			}
			return $cart_item_data;
		}
	}

	public function addon_get_item_data_filter( $item_data, $cart_item_data ) {

		$product_quantity = $cart_item_data['quantity'];
		$product_price    = $cart_item_data['data']->get_price();

		if ( in_array( 'selected_item_post_id', array_keys( $cart_item_data ), true ) ) {
			$get_added_data = $cart_item_data['selected_item_post_id'];
			foreach ( $get_added_data as $arrays ) {
				if ( empty( $arrays ) || ! is_array( $arrays ) ) {
					continue;
				}

				foreach ( $arrays as $option_field_id_array ) {

					if ( in_array( 'field_id', array_keys( $option_field_id_array ) ) ) {
						$field_id_value = $option_field_id_array['field_id'];
						$field_type     = $option_field_id_array['field_type'];

						$field_title = get_post_meta( $field_id_value, 'af_addon_field_title', true );

						if ( $field_title ) {
							?>
							<br><b> <?php echo esc_attr( $field_title . ':' ); ?> </b><br>
							<?php
						}

						$field_type = $option_field_id_array['field_type'];

						if ( 'multi_select' == $field_type || 'check_boxes' == $field_type ) {

							$selected_option_ids = (array) $option_field_id_array['value'];

							foreach ( $selected_option_ids as $option_id ) {

								$option_name  = get_post_meta( $option_id, 'af_addon_field_options_name', true );
								$option_price = (float) get_post_meta( $option_id, 'af_addon_field_options_price', true );
								$price_type   = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );

								$pao_price = $option_name;
								if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

									$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
								} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

									$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
								}

								echo esc_attr( $pao_price );

								if ( end( $selected_option_ids ) != $option_id ) {
									echo '<br>';
								}
							}
						} elseif ( 'drop_down' == $field_type || 'radio' == $field_type || 'color_swatcher' == $field_type || 'image_swatcher' == $field_type || 'image' == $field_type ) {

							$option_id    = $option_field_id_array['value'];
							$option_name  = get_post_meta( $option_id, 'af_addon_field_options_name', true );
							$option_price = (float) get_post_meta( $option_id, 'af_addon_field_options_price', true );
							$price_type   = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );

							$pao_price = $option_name;
							if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

								$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
							} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

								$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
							}

							echo esc_attr( $pao_price );

						} else {

							$field_id = $option_field_id_array['field_id'];

							$value = $option_field_id_array['value'];

							if ( '1' == get_post_meta( $field_id, 'af_addon_price_range_checkbox', true ) ) {
								$price_type   = get_post_meta( $field_id, 'af_addon_field_price_type', true );
								$option_price = (float) get_post_meta( $field_id, 'af_addon_field_price', true );
							} else {
								$price_type   = 0;
								$option_price = 0;
							}

							$pao_price = $value;

							if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

								$pao_price = $value . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
							} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

								$pao_price = $value . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
							}

							echo esc_attr( $pao_price );
						}
					}
				}
			}
		}

		return $item_data;
	}

	public function get_cart_item_from_session( $cart_item, $values ) {

		if ( ! empty( $values['selected_item_post_id'] ) ) {

			$cart_item['selected_item_post_id'] = $values['selected_item_post_id'];
			$cart_item                          = $this->add_cart_item( $cart_item );
		}

		return $cart_item;
	}

	public function addon_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {

		$order_id = $order->get_id();

		foreach ( WC()->cart->get_cart() as $item_key => $value_check ) {

			$total_files_in_array = 0;

			if ( ! empty( $value_check ) && $item_key === $cart_item_key && array_key_exists( 'selected_item_post_id', $value_check ) ) {

				$get_data_of_files = $value_check['selected_item_post_id'];

				$item->add_meta_data(
					'selected_item_post_id',
					$get_data_of_files,
					true
				);
			}
		}
	}

	public function addon_show_option_name( $item_id, $item, $order ) {

		foreach ( $item->get_meta_data() as $item_data ) {

			$item_data_array = $item_data->get_data();
			if ( in_array( 'selected_item_post_id', $item_data_array ) && is_array( $item_data_array['value'] ) ) {
				$get_added_data = $item_data_array['value'];

				foreach ( $get_added_data as $option_field_id_array ) {

					foreach ( $option_field_id_array as $option_field_id_array ) {

						$pao_price = '';

						if ( in_array( 'field_id', array_keys( $option_field_id_array ) ) ) {
							$field_id_value = $option_field_id_array['field_id'];
							$field_type     = $option_field_id_array['field_type'];

							$field_title = get_post_meta( $field_id_value, 'af_addon_field_title', true );

							if ( $field_title ) {
								?>
								<br>
								<b> <?php echo esc_attr( $field_title . ':' ); ?> </b>
								<br>
								<?php
							}

							$field_type = $option_field_id_array['field_type'];

							if ( 'multi_select' == $field_type || 'check_boxes' == $field_type ) {

								$get_multi_select_val = (array) $option_field_id_array['value'];

								foreach ( $get_multi_select_val as $option_id_value ) {

									$option_name  = get_post_meta( $option_id_value, 'af_addon_field_options_name', true );
									$option_price = (float) get_post_meta( $option_id_value, 'af_addon_field_options_price', true );
									$price_type   = get_post_meta( $option_id_value, 'af_addon_field_options_price_type', true );

									if ( 'free' == $price_type ) {

										$pao_price = $option_name;
									} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}

									echo esc_attr( $pao_price );

									if ( end( $get_multi_select_val ) != $option_id_value ) {
										echo '<br>';
									}
								}
							} else {

								$field_id = $option_field_id_array['field_id'];

								if ( 'file_upload' == $option_field_id_array['field_type'] ) {

									$upload = wp_upload_dir();
									$url    = $upload['baseurl'] . '/addify-product-addons/uploaded-files/' . $option_field_id_array['value'];
									?>
									<a target="_blank" href="<?php echo esc_attr( $url ); ?>">
										<i class="fa fa-eye">
											<?php echo esc_attr( $option_field_id_array['value'] ); ?>
										</i>
									</a>
									<?php
								}

								if ( 'drop_down' == $field_type || 'image' == $field_type || 'image_swatcher' == $field_type || 'color_swatcher' == $field_type || 'radio' == $field_type ) {

									$option_value = $option_field_id_array['value'];
									$option_name  = get_post_meta( $option_value, 'af_addon_field_options_name', true );
									$option_price = (float) get_post_meta( $option_value, 'af_addon_field_options_price', true );
									$price_type   = get_post_meta( $option_value, 'af_addon_field_options_price_type', true );
									if ( 'free' == $price_type ) {

										$pao_price = $option_name;
									} elseif ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = $option_name . ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}

									echo esc_attr( $pao_price );

								} else {

									$option_price = (float) get_post_meta( $field_id, 'af_addon_field_price', true );
									$price_type   = get_post_meta( $field_id, 'af_addon_field_price_type', true );
									if ( 'flat_fixed_fee' == $price_type || 'fixed_fee_based_on_quantity' == $price_type ) {

										$pao_price = ' (+' . get_woocommerce_currency_symbol() . ' ' . number_format( $option_price, 2, '.', '' ) . ')';
									} elseif ( 'flat_percentage_fee' == $price_type || 'Percentage_fee_based_on_quantity' == $price_type ) {

										$pao_price = ' (+' . number_format( $option_price, 2, '.', '' ) . '%)';
									}
									if ( 'file_upload' != $option_field_id_array['field_type'] ) {
										?>
										<b><?php echo esc_attr( $option_field_id_array['value'] . ' ' . $pao_price ); ?></b>
										<?php
									} else {
										?>
										<b><?php echo esc_attr( $pao_price ); ?></b>
										<?php
									}
								}

								$pao_price = '';
							}
						}
					}
				}
			}
		}
	}

	public function added_fees( $option_id_value, $value_cart, $field_type ) {

		$product_price    = $value_cart['data']->get_price();
		$product_quantity = $value_cart['quantity'];

		if ( 'multi_select' == $field_type || 'drop_down' == $field_type || 'check_boxes' == $field_type || 'image' == $field_type || 'image_swatcher' == $field_type || 'color_swatcher' == $field_type || 'radio' == $field_type ) {

			$price_meta_key      = 'af_addon_field_options_price';
			$price_type_meta_key = 'af_addon_field_options_price_type';
			$price_type          = get_post_meta( $option_id_value, 'af_addon_field_options_price_type', true );
			$option_price        = (float) get_post_meta( $option_id_value, 'af_addon_field_options_price', true );
		} elseif ( '1' == get_post_meta( $option_id_value, 'af_addon_price_range_checkbox', true ) ) {
				$price_type   = get_post_meta( $option_id_value, 'af_addon_field_price_type', true );
				$option_price = (float) get_post_meta( $option_id_value, 'af_addon_field_price', true );
		} else {
			$price_type   = 0;
			$option_price = 0;
		}

		$pao_price = 0;

		if ( 'flat_fixed_fee' == $price_type ) {

			$pao_price = $option_price;

			$pao_price = $pao_price / $product_quantity;

		} elseif ( 'fixed_fee_based_on_quantity' == $price_type ) {

			$pao_price = $option_price;

		} elseif ( 'flat_percentage_fee' == $price_type ) {

			$pao_price = ( number_format( $option_price ) / 100 ) * $product_price;

			$pao_price = $pao_price / $product_quantity;

		} elseif ( 'Percentage_fee_based_on_quantity' == $price_type ) {

			$pao_price = ( number_format( $option_price ) / 100 ) * $product_price;
		}
		return $pao_price;
	}
}

new Af_Addon_Front_Cart();
