<?php

/**
 * Product Options Validation Check.
 */

class Af_Addon_Validation {


	public function __construct() {

		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'addon_field_required_validation' ), 35, 4 );
	}

	public function addon_field_required_validation( $validation, $product_id, $quantity, $variation_id = 0 ) {

		$nonce = isset( $_POST['af_addon_front_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['af_addon_front_nonce_field'] ) ) : 0;

		if ( isset( $_POST['af_product_options_fields_availabel'] ) && ! wp_verify_nonce( $nonce, 'af_addon_front_nonce' ) ) {

			die( 'Failed Security check' );
		}

		$field_error_messages = array();

		$file_error_messages = array();

		$text_error_messages = array();

		$validation_flag = false;

		if ( $variation_id >= 1 ) {

			$fields = get_posts(
				array(

					'post_type'   => 'af_pao_fields',

					'post_status' => 'publish',

					'numberposts' => -1,

					'post_parent' => $variation_id,

					'fields'      => 'ids',

					'orderby'     => 'menu_order',

					'order'       => 'ASC',

				)
			);

			foreach ( $fields as $field_id ) {

				if ( empty( $field_id ) ) {

					continue;
				}

				$field_error_messages[] = $this->af_addon_fields_validation_check( $_POST, $field_id );

				$file_error_messages[] = $this->af_addon_file_extention_validation_check($_POST, $field_id );

				$text_error_messages[] = $this->af_addon_textarea_validation_check( $_POST, $field_id );
			}

			if ( '1' != get_post_meta( $variation_id, 'exclude_var_addons', true ) ) {

				$fields = get_posts(
					array(

						'post_type'   => 'af_pao_fields',

						'post_status' => 'publish',

						'numberposts' => -1,

						'post_parent' => $product_id,

						'fields'      => 'ids',

						'orderby'     => 'menu_order',

						'order'       => 'ASC',
					)
				);

				foreach ( $fields as $field_id ) {

					if ( empty( $field_id ) ) {
						continue;
					}

					$field_error_messages[] = $this->af_addon_fields_validation_check( $_POST, $field_id );

					$file_error_messages[] = $this->af_addon_file_extention_validation_check($_POST, $field_id );

					$text_error_messages[] = $this->af_addon_textarea_validation_check( $_POST, $field_id );
				}
			}

			if ( '1' != get_post_meta( $variation_id, 'exclude_var_rule_addons', true ) && '1' != get_post_meta( wp_get_post_parent_id( $variation_id ), 'exclude_rule_addons', true ) ) {

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

				foreach ( $addon_rules as $rule_id ) {

					$af_addon_user_role = (array) get_post_meta( $rule_id, 'af_addon_user_role', true );

					$af_addon_use_roles = is_user_logged_in() ? current( wp_get_current_user()->roles ) : 'guest';

					if ( ! empty( $af_addon_user_role ) && ! in_array( (string) $af_addon_use_roles, $af_addon_user_role, true ) ) {

						continue;
					}

					if ( $this->af_addon_prod_check( $variation_id, $rule_id ) ) {

						$fields = get_posts(
							array(

								'post_type'   => 'af_pao_fields',

								'post_status' => 'publish',

								'numberposts' => -1,

								'post_parent' => $rule_id,

								'fields'      => 'ids',

								'orderby'     => 'menu_order',

								'order'       => 'ASC',
							)
						);

						foreach ( $fields as $field_id ) {

							if ( empty( $field_id ) ) {
								continue;
							}

							$field_error_messages[] = $this->af_addon_fields_validation_check( $_POST, $field_id );

							$file_error_messages[] = $this->af_addon_file_extention_validation_check($_POST, $field_id );

							$text_error_messages[] = $this->af_addon_textarea_validation_check( $_POST, $field_id );
						}
					}
				}
			}
		} else {

			$fields = get_posts(
				array(

					'post_type'   => 'af_pao_fields',

					'post_status' => 'publish',

					'numberposts' => -1,

					'post_parent' => $product_id,

					'fields'      => 'ids',

					'orderby'     => 'menu_order',

					'order'       => 'ASC',
				)
			);

			foreach ( $fields as $field_id ) {

				if ( empty( $field_id ) ) {
					continue;
				}

				$field_error_messages[] = $this->af_addon_fields_validation_check( $_POST, $field_id );

				$file_error_messages[] = $this->af_addon_file_extention_validation_check($_POST, $field_id );

				$text_error_messages[] = $this->af_addon_textarea_validation_check( $_POST, $field_id );
			}

			if ( '1' != get_post_meta( $product_id, 'exclude_rule_addons', true ) ) {

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

				foreach ( $addon_rules as $rule_id ) {

					if ( empty( $rule_id ) ) {
						continue;
					}

					$af_addon_user_role = get_post_meta( $rule_id, 'af_addon_user_role', true );

					$af_addon_use_roles = is_user_logged_in() ? current( wp_get_current_user()->roles ) : 'guest';

					if ( ! empty( $af_addon_user_role ) && ! in_array( (string) $af_addon_use_roles, (array) $af_addon_user_role, true ) ) {

						continue;
					}

					if ( $this->af_addon_prod_check( $product_id, $rule_id ) ) {

						$fields = get_posts(
							array(

								'post_type'   => 'af_pao_fields',

								'post_status' => 'publish',

								'numberposts' => -1,

								'post_parent' => $rule_id,

								'fields'      => 'ids',

								'orderby'     => 'menu_order',

								'order'       => 'ASC',
							)
						);

						foreach ( $fields as $field_id ) {

							if ( empty( $field_id ) ) {
								continue;
							}

							$field_error_messages[] = $this->af_addon_fields_validation_check( $_POST, $field_id );

							$file_error_messages[] = $this->af_addon_file_extention_validation_check($_POST, $field_id );

							$text_error_messages[] = $this->af_addon_textarea_validation_check( $_POST, $field_id );
						}
					}
				}
			}
		}

		if ( ! empty( array_filter( $field_error_messages ) ) || ! empty( array_filter( $file_error_messages ) ) || ! empty( array_filter( $text_error_messages ) ) ) {

			foreach ( $field_error_messages as $error_message ) {

				wc_add_notice( $error_message, 'error' );
			}

			foreach ( $file_error_messages as $file_error_message ) {

				wc_add_notice( $file_error_message, 'error' );
			}

			foreach ( $text_error_messages as $text_error_message ) {

				wc_add_notice( $text_error_message, 'error' );
			}

			return false;
		}

		return $validation;
	}

	public function af_addon_prod_check( $prod_id, $rule_id ) {

		$addon_selected_products = get_post_meta( $rule_id, 'af_pao_prod_search', true );

		$addon_selected_category = get_post_meta( $rule_id, 'af_pao_cat_search', true );

		$addon_selected_tags = get_post_meta( $rule_id, 'af_pao_tag_search', true );

		if ( empty( $addon_selected_products ) && empty( $addon_selected_category ) && empty( $addon_selected_tags ) ) {

			return true;

		}

		if ( ! empty( $addon_selected_products ) ) {

			if ( in_array( (string) $prod_id, $addon_selected_products ) ) {

				return true;
			}
		}

		if ( ! empty( $addon_selected_category ) ) {

			if ( has_term( $addon_selected_category, 'product_cat', $prod_id ) ) {

				return true;
			}
		}

		if ( ! empty( $addon_selected_tags ) ) {

			if ( has_term( $addon_selected_tags, 'product_tag', $prod_id ) ) {

				return true;
			}
		}

		return false;
	}

	public function af_addon_fields_validation_check( $post_data, $field_id ) {

		$nonce = isset( $post_data['af_addon_front_nonce_field'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_front_nonce_field'] ) ) : 0;

		if ( isset( $post_data['af_product_options_fields_availabel'] ) && ! wp_verify_nonce( $nonce, 'af_addon_front_nonce' ) ) {

			die( 'Failed Security check' );
		}

		$required_msg = '';

		$addon_options = 'af_addons_options_' . $field_id;

		$depend_field = get_post_meta( $field_id, 'af_addon_field_depend_selector', true );

		$depend_option = array();

		if ( get_post_meta( $field_id, 'af_addon_option_depend_selector', true ) ) {

			$depend_option = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );
		}

		$required_value = get_post_meta( $field_id, 'af_addon_required_field', true );

		$depend_selector = get_post_meta( $field_id, 'af_addon_depend_selector', true );

		$field_type = get_post_meta( $field_id, 'af_addon_type_select', true );

		if ( '1' == $required_value ) {

			if ( 'af_addon_dependable' == $depend_selector ) {

				if ( 'multi_select' == get_post_meta( $depend_field, 'af_addon_type_select', true ) || 'check_boxes' == get_post_meta( $depend_field, 'af_addon_type_select', true ) ) {

					$options_array = isset( $post_data[ 'af_addons_options_' . $depend_field ] ) ? sanitize_meta( '', $post_data[ 'af_addons_options_' . $depend_field ], '' ) : array();

					if ( count( array_intersect( $options_array, $depend_option ) ) >= 1 ) {

						if ( '1' == $required_value && empty( $post_data[ $addon_options ] ) ) {

							$field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

							$required_msg = $field_title . ' is a required field.';

						}
					}
				} else {

					$options_array = isset( $post_data[ 'af_addons_options_' . $depend_field ] ) ? sanitize_text_field( $post_data[ 'af_addons_options_' . $depend_field ] ) : '';

					if ( in_array( $options_array, $depend_option, true ) ) {

						if ( '1' == $required_value && empty( $post_data[ $addon_options ] ) ) {

							$field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

							$required_msg = $field_title . ' is a required field.';

						}
					}
				}
			} elseif ( 'file_upload' == $field_type ) {


				if ( empty( $_FILES[ $addon_options ] ) ) {

					$field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

					$required_msg = $field_title . ' is a required field.';
				}
			} elseif ( empty( $post_data[ $addon_options ] ) ) {


					$field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

					$required_msg = $field_title . ' is a required field.';
			}
		}

		return $required_msg;
	}

	public function af_addon_textarea_validation_check( $post_data, $field_id ) {

		$text_area_length_msg = '';

		if ( '1' == get_post_meta( $field_id, 'af_addon_limit_range_checkbox', true ) ) {

			$addon_options = 'af_addons_options_' . $field_id;

			$depend_field = get_post_meta( $field_id, 'af_addon_field_depend_selector', true );

			$depend_option = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );

			$field_type = get_post_meta( $field_id, 'af_addon_type_select', true );

			if ( 'textarea' == $field_type || 'input_text' == $field_type ) {

				if ( ! empty( $post_data[ $addon_options ] ) ) {

					$min_limit_range = get_post_meta( $field_id, 'af_addon_min_limit_range', true );

					$max_limit_range = get_post_meta( $field_id, 'af_addon_max_limit_range', true );

					$field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

					$total_output_lenght = strlen( sanitize_text_field( $post_data[ $addon_options ] ) );

					if ( $total_output_lenght < $min_limit_range || $total_output_lenght > $max_limit_range ) {

						$text_area_length_msg = $field_title . ' is not within limit range. Enter the text within limit range';
					}
				}
			}
		}

		return $text_area_length_msg;
	}

	public function af_addon_file_extention_validation_check( $post_data, $field_id ) {
		
		$nonce = isset( $post_data['af_addon_front_nonce_field'] ) ? sanitize_text_field( wp_unslash( $post_data['af_addon_front_nonce_field'] ) ) : 0;

		if ( isset( $post_data['af_product_options_fields_availabel'] ) && ! wp_verify_nonce( $nonce, 'af_addon_front_nonce' ) ) {

			die( 'Failed Security check' );
		}

		$file_filed_required_msg = '';

		$addon_options = 'af_addons_options_' . $field_id;

		$depend_field = get_post_meta( $field_id, 'af_addon_field_depend_selector', true );

		$depend_option = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );

		$field_type = get_post_meta( $field_id, 'af_addon_type_select', true );

		if ( 'file_upload' == $field_type ) {

			if ( ! empty( $_FILES[ $addon_options ]['name'] ) ) {

				$file_array = sanitize_meta( '', $_FILES[ $addon_options ], '' );

				$file_name = $file_array['name'];

				$file_name_array = explode( '.', $file_name );

				$file_extention = end( $file_name_array );

				$admin_file_extentions = explode( ', ', get_post_meta( $field_id, 'af_addon_upload_file_extention', true ) );

				$admin_file_extention = get_post_meta( $field_id, 'af_addon_upload_file_extention', true );

				$admin_file_upload_title = get_post_meta( $field_id, 'af_addon_field_title', true );

				if ( ! empty($admin_file_extention) && ! in_array( $file_extention, $admin_file_extentions, true ) ) {

					$file_filed_required_msg = 'Invalid file format in ' . $admin_file_upload_title . ', only "' . $admin_file_extention . '" is allowed';
				}
			}
		}

		return $file_filed_required_msg;
	}
}

new Af_Addon_Validation();
