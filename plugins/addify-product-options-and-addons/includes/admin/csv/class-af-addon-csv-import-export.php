<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Export CSV
 */
class Af_Addon_Csv_Import_Export {


	public function af_addon_import_csv( $post_data, $file_data ) {

		// Allowed filetypes for import.
		$current_post_id = '';
		if ( isset( $post_data['for_import_current_post_id'] ) ) {
			$current_post_id = sanitize_text_field( $post_data['for_import_current_post_id'] );
		}

		$valid_file_types = array(
			'csv' => 'text/csv',
		);

		// Retrieve the file type from the file name.
		if ( isset( $file_data['af_addon_import_file']['name'] ) ) {
			$filetype = wp_check_filetype( sanitize_text_field( wp_unslash( $file_data['af_addon_import_file']['name'] ) ), $valid_file_types );
		}

		// Check if file type is valid.
		if ( in_array( $filetype['type'], $valid_file_types, true ) ) {

			$target_dir = AFPAO_PLUGIN_DIR . 'includes/admin/uploaded_csv/';

			if ( isset( $file_data['af_addon_import_file']['name'] ) ) {
				$target_dir = $target_dir . basename( sanitize_text_field( isset( $file_data['af_addon_import_file']['name'] ) ) );
			}

			$upload_ok = true;
			if ( isset( $file_data['af_addon_import_file']['tmp_name'] ) ) {
				$tempo_name = sanitize_text_field( $file_data['af_addon_import_file']['tmp_name'] );
			}

			if ( move_uploaded_file( $tempo_name, $target_dir ) ) {

				$handle = fopen( $target_dir, 'r' );
				if ( false !== $handle ) {
					$data2 = fgetcsv( $handle, 1000, ',' );
					$row   = 1;
					while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {

						if ( $data[1] ) {

							$addon_field_id = $this->add_on_import_csv( $data[1] );

							$create_new_field_flag = false;

							if ( ! empty( $addon_field_id ) ) {

								if ( 'af_pao_fields' == get_post_type( $addon_field_id ) ) {

									if ( wp_get_post_parent_id( $addon_field_id ) != $current_post_id ) {
										$create_new_field_flag = true;
									} else {
										$field_id = $addon_field_id;
									}
								} else {
									$create_new_field_flag = true;
								}
							} else {
								$create_new_field_flag = true;
							}

							if ( $create_new_field_flag ) {

								$field_arg = array(
									'post_type'   => 'af_pao_fields',
									'post_status' => 'publish',
									'post_parent' => $current_post_id,
								);

								$created_new_field = wp_insert_post( $field_arg );
								$field_id          = $created_new_field;
							}

							if ( $row >= 1 ) {

								$addon_field_priority       = $this->add_on_import_csv( $data[0] );
								$addon_field_id             = $this->add_on_import_csv( $data[1] );
								$addon_type                 = $this->add_on_import_csv( $data[2] );
								$addon_title                = $this->add_on_import_csv( $data[3] );
								$addon_enable_tooltip       = $this->add_on_import_csv( $data[4] );
								$addon_tooltip              = $this->add_on_import_csv( $data[5] );
								$addon_enable_description   = $this->add_on_import_csv( $data[6] );
								$addon_desc                 = $this->add_on_import_csv( $data[7] );
								$addon_enable_required      = $this->add_on_import_csv( $data[8] );
								$addon_enable_limit_range   = $this->add_on_import_csv( $data[9] );
								$addon_min_limit_range      = $this->add_on_import_csv( $data[10] );
								$addon_max_limit_range      = $this->add_on_import_csv( $data[11] );
								$addon_field_price_range    = $this->add_on_import_csv( $data[12] );
								$addon_field_price_type     = $this->add_on_import_csv( $data[13] );
								$addon_field_price          = $this->add_on_import_csv( $data[14] );
								$addon_field_file_extention = $this->add_on_import_csv( $data[15] );
								$addon_option_id            = $this->add_on_import_csv( $data[16] );
								$addon_option_image         = $this->add_on_import_csv( $data[17] );
								$addon_option_color         = $this->add_on_import_csv( $data[18] );
								$addon_option_title         = $this->add_on_import_csv( $data[19] );
								$addon_option_price_type    = $this->add_on_import_csv( $data[20] );
								$addon_option_price         = $this->add_on_import_csv( $data[21] );
								$addon_option_priority      = $this->add_on_import_csv( $data[22] );

								$option_id_array               = explode( ',', $addon_option_id );
								$option_image_array            = explode( '/addon_img/', $addon_option_image );
								$option_name_array             = explode( ',', $addon_option_title );
								$option_color_array            = explode( ',', $addon_option_color );
								$addon_option_price_type_array = explode( ',', $addon_option_price_type );
								$addon_option_price_array      = explode( ',', $addon_option_price );
								$addon_option_priority_array   = explode( ',', $addon_option_priority );

								$option_name_count = count( $option_name_array );

								$option_field_type_array = array( 'drop_down', 'multi_select', 'check_boxes', 'radio', 'color_swatcher', 'image_swatcher', 'image' );

								if ( in_array( $addon_type, $option_field_type_array, true ) ) {

									if ( ! empty( array_filter( $option_id_array ) ) ) {

										foreach ( $option_id_array as $index => $option_id ) {

											$create_new_option_flag = false;

											if ( 'af_pao_options' == get_post_type( $option_id ) ) {

												if ( wp_get_post_parent_id( $option_id ) != $field_id ) {
													$create_new_option_flag = true;
												} else {
													$new_insert_option = $option_id;
												}
											} else {
												$create_new_option_flag = true;
											}

											if ( $create_new_option_flag ) {

												$create_new_option  = array(
													'post_type' => 'af_pao_options',
													'post_status' => 'publish',
													'post_parent' => $field_id,
												);
												$created_new_option = wp_insert_post( $create_new_option );
												$new_insert_option  = $created_new_option;

											}

											if ( ! empty( $option_image_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_image', $option_image_array[ $index ] );
												$option_image_name          = explode( '/', $option_image_array[ $index ] );
												$option_image_extention_get = explode( '.', end( $option_image_name ) );
												$option_image_extention     = end( $option_image_extention_get );

												$wp_image_extentions_arrays = array( 'jpg', 'jpeg', 'png', 'gif', 'ico' );

												if ( ! empty( $option_image_extention ) && ( in_array( $option_image_extention, $wp_image_extentions_arrays ) ) ) {

													$output = wp_remote_get( $option_image_array[ $index ], array( 'timeout' => 45 ) );

													$exploded_img_name = explode( '/', $option_image_array[ $index ] );
													$exploded_img_name = end( $exploded_img_name );

													$target_img_dir = AFPAO_MEDIA_PATH . $exploded_img_name;

													$img_file = fopen( $target_img_dir, 'w+' );

													if ( false != $img_file && ! empty( $output['body'] ) ) {

														$file_write = fwrite( $img_file, $output['body'] );

														$file_name    = 'addify-product-addons/' . $exploded_img_name;
														$img_filetype = wp_check_filetype( $target_img_dir, null );
														$mime_type    = $img_filetype['type'];

														$attachment = array(
															'post_mime_type' => $mime_type,
															'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
															'post_name'      => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
															'post_type'      => 'af_pao_options',
															'post_status'    => 'publish',
															'post_parent'    => $new_insert_option,
															'file'           => $target_img_dir,
														);

														$attachment_id = wp_insert_attachment( $attachment );

														if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
															include ABSPATH . 'wp-admin/includes/image.php';
														}

														$attach_data = wp_generate_attachment_metadata( $attachment_id, $target_img_dir );
														wp_update_attachment_metadata( $attachment_id, $attach_data );
														set_post_thumbnail( $new_insert_option, $attachment_id );

														set_post_thumbnail( $new_insert_option, $attachment_id );
													}
												}
											}

											if ( ! empty( $option_color_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_option_color', $option_color_array[ $index ] );

											}

											if ( ! empty( $option_name_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_name', $option_name_array[ $index ] );

											}

											if ( ! empty( $addon_option_price_type_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_price_type', $addon_option_price_type_array[ $index ] );

											}

											if ( ! empty( $addon_option_price_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_price', $addon_option_price_array[ $index ] );

											}

											if ( ! empty( $addon_option_priority_array[ $index ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_option_priority', $addon_option_priority_array[ $index ] );

												$update_option_priority = array(
													'post_type' => 'af_pao_options',
													'numberposts' => -1,
													'post_status' => 'publish',
													'ID' => $new_insert_option,
													'menu_order' => $addon_option_priority_array[ $index ],
												);

												$update_options_priority = wp_update_post( $update_option_priority );
											}
										}
									} else {

										foreach ( $option_name_array as $i => $value ) {

											$create_new_option  = array(
												'post_type'   => 'af_pao_options',
												'post_status' => 'publish',
												'post_parent' => $field_id,
											);
											$created_new_option = wp_insert_post( $create_new_option );
											$new_insert_option  = $created_new_option;

											if ( ! empty( $option_image_array[ $i ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_image', $option_image_array[ $i ] );
												$option_image_name          = explode( '/', $option_image_array[ $i ] );
												$option_image_extention_get = explode( '.', end( $option_image_name ) );
												$option_image_extention     = end( $option_image_extention_get );

												$wp_image_extentions_arrays = array( 'jpg', 'jpeg', 'png', 'gif', 'ico' );

												if ( ! empty( $option_image_extention ) && ( in_array( $option_image_extention, $wp_image_extentions_arrays ) ) ) {

													$output = wp_remote_get( $option_image_array[ $i ], array( 'timeout' => 45 ) );

													$exploded_img_name = explode( '/', $option_image_array[ $i ] );
													$exploded_img_name = end( $exploded_img_name );

													$target_img_dir = AFPAO_MEDIA_PATH . $exploded_img_name;

													$img_file = fopen( $target_img_dir, 'w+' );

													if ( false != $img_file && ! empty( $output['body'] ) ) {

														$file_write = fwrite( $img_file, $output['body'] );

														$file_name    = 'addify-product-addons/' . $exploded_img_name;
														$img_filetype = wp_check_filetype( $target_img_dir, null );
														$mime_type    = $img_filetype['type'];

														$attachment = array(
															'post_mime_type' => $mime_type,
															'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
															'post_name'      => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
															'post_type'      => 'af_pao_options',
															'post_status'    => 'publish',
															'post_parent'    => $new_insert_option,
															'file'           => $target_img_dir,
														);

														$attachment_id = wp_insert_attachment( $attachment );

														if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
															include ABSPATH . 'wp-admin/includes/image.php';
														}

														$attach_data = wp_generate_attachment_metadata( $attachment_id, $target_img_dir );
														wp_update_attachment_metadata( $attachment_id, $attach_data );
														set_post_thumbnail( $new_insert_option, $attachment_id );

														set_post_thumbnail( $new_insert_option, $attachment_id );
													}
												}
											}

											if ( ! empty( $option_color_array[ $i ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_option_color', $option_color_array[ $i ] );

											}

											if ( ! empty( $value ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_name', $value );

											}

											if ( ! empty( $addon_option_price_type_array[ $i ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_price_type', $addon_option_price_type_array[ $i ] );

											}

											if ( ! empty( $addon_option_price_array[ $i ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_field_options_price', $addon_option_price_array[ $i ] );

											}

											if ( ! empty( $addon_option_priority_array[ $i ] ) ) {

												update_post_meta( $new_insert_option, 'af_addon_option_priority', $addon_option_priority_array[ $i ] );
											}
										}
									}
								}

								update_post_meta( $field_id, 'af_addon_field_sort', $addon_field_priority );

								$create_new_field = array(
									'post_type'   => 'af_pao_fields',
									'numberposts' => -1,
									'post_status' => 'publish',
									'ID'          => $field_id,
									'menu_order'  => $addon_field_priority,
								);

								$insert_field = wp_update_post( $create_new_field );

								update_post_meta( $field_id, 'af_addon_type_select', $addon_type );

								update_post_meta( $field_id, 'af_addon_field_title', $addon_title );

								if ( 'show_tooltip' == $addon_enable_tooltip ) {
									update_post_meta( $field_id, 'af_addon_tooltip_checkbox', 1 );
								} else {
									update_post_meta( $field_id, 'af_addon_tooltip_checkbox', 0 );
								}

								update_post_meta( $field_id, 'af_addon_tooltip_textarea', $addon_tooltip );

								if ( 'show_description' == $addon_enable_description ) {
									update_post_meta( $field_id, 'af_addon_desc_checkbox', 1 );
								} else {
									update_post_meta( $field_id, 'af_addon_desc_checkbox', 0 );
								}

								update_post_meta( $field_id, 'af_addon_desc_textarea', $addon_desc );

								if ( 'required' == $addon_enable_required ) {
									update_post_meta( $field_id, 'af_addon_required_field', 1 );
								} else {
									update_post_meta( $field_id, 'af_addon_required_field', 0 );
								}

								if ( 'enabled' == $addon_enable_limit_range ) {
									update_post_meta( $field_id, 'af_addon_limit_range_checkbox', 1 );
								} else {
									update_post_meta( $field_id, 'af_addon_limit_range_checkbox', 0 );
								}

								update_post_meta( $field_id, 'af_addon_min_limit_range', $addon_min_limit_range );

								update_post_meta( $field_id, 'af_addon_max_limit_range', $addon_max_limit_range );

								if ( 'checked' == $addon_field_price_range ) {
									update_post_meta( $field_id, 'af_addon_price_range_checkbox', 1 );
								} else {
									update_post_meta( $field_id, 'af_addon_price_range_checkbox', 0 );
								}

								update_post_meta( $field_id, 'af_addon_field_price_type', $addon_field_price_type );

								update_post_meta( $field_id, 'af_addon_field_price', $addon_field_price );

								update_post_meta( $field_id, 'af_addon_upload_file_extention', $addon_field_file_extention );
							}
							++$row;
						}
					}
					fclose( $handle );
				}
			}
		}
	}

	public function af_addon_export_csv( $post_data ) {

		$addon_field_priority             = '';
		$addon_field_id                   = '';
		$addon_field_type                 = '';
		$addon_field_title                = '';
		$addon_dependable                 = '';
		$addon_field_dependable           = '';
		$addon_option_dependable          = '';
		$addon_field_tooltip_checkbox     = '';
		$addon_field_tooltip              = '';
		$addon_field_desc_checkbox        = '';
		$addon_field_desc                 = '';
		$addon_field_req_chekbox          = '';
		$addon_field_limit_range_checkbox = '';
		$addon_field_min_limit_range      = '';
		$addon_field_max_limit_range      = '';
		$addon_field_price_range_checkbox = '';
		$addon_field_price_type           = '';
		$addon_field_price                = '';
		$addon_field_file_extention       = '';
		$addon_option_id                  = '';
		$addon_option_image               = '';
		$addon_option_title               = '';
		$addon_option_color               = '';
		$addon_option_price_type          = '';
		$addon_option_price               = '';
		$addon_option_priority            = '';

		$addon_delimiterr = ',';
		$filename         = 'Add-Ons-csv ' . gmdate( 'Y-m-d' ) . '.csv';
		$f1               = fopen( AFPAO_PLUGIN_DIR . 'assets/files/' . $filename, 'w+' );
		$addon_fields     = array(
			'Field Priority',
			'Field id',
			'Add-On type',
			'Title',
			'Enable tooltip',
			'Tooltip',
			'Enable Description',
			'Description',
			'Enable Required Field',
			'Enable Limit Range',
			'Minimum Limit Range',
			'Maximum Limit Range',
			'Enable Price Range',
			'Price Type',
			'Price',
			'File Type',
			'Option id',
			'Option Image',
			'Option Color',
			'Option Title',
			'Option Price Type',
			'Option Price',
			'Option Priority',
		);

		fputcsv( $f1, $addon_fields, $addon_delimiterr );

		$current_post_id = '';
		if ( isset( $post_data['Export_data_of_current_rule'] ) ) {
			$current_post_id = sanitize_text_field( $post_data['Export_data_of_current_rule'] );
		}

		$args = array(
			'post_type'   => 'af_pao_fields',
			'post_status' => 'publish',
			'post_parent' => $current_post_id,
			'numberposts' => - 1,
			'fields'      => 'ids',
			'order'       => 'ASC',
		);

		$fields = get_posts( $args );

		foreach ( $fields as $field_id ) {
			if ( empty( $field_id ) ) {
				continue;
			}

			$addon_field_id = $field_id;

			$addon_field_priority = get_post_meta( $field_id, 'af_addon_field_sort', true );

			$addon_field_type = get_post_meta( $field_id, 'af_addon_type_select', true );

			$addon_field_title = get_post_meta( $field_id, 'af_addon_field_title', true );

			if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {
				$addon_field_tooltip_checkbox = 'show_tooltip';
			} else {
				$addon_field_tooltip_checkbox = 'not_show_tooltip';
			}

			$addon_field_tooltip = get_post_meta( $field_id, 'af_addon_tooltip_textarea', true );

			if ( '1' == get_post_meta( $field_id, 'af_addon_desc_checkbox', true ) ) {
				$addon_field_desc_checkbox = 'show_description';
			} else {
				$addon_field_desc_checkbox = 'not_show_description';
			}

			$addon_field_desc = get_post_meta( $field_id, 'af_addon_desc_textarea', true );

			if ( '1' == get_post_meta( $field_id, 'af_addon_required_field', true ) ) {
				$addon_field_req_chekbox = 'required';
			} else {
				$addon_field_req_chekbox = 'not_required';
			}

			if ( '1' == get_post_meta( $field_id, 'af_addon_limit_range_checkbox', true ) ) {
				$addon_field_limit_range_checkbox = 'enabled';
			} else {
				$addon_field_limit_range_checkbox = 'disabled';
			}

			$addon_field_min_limit_range = get_post_meta( $field_id, 'af_addon_min_limit_range', true );

			$addon_field_max_limit_range = get_post_meta( $field_id, 'af_addon_max_limit_range', true );

			if ( '1' == get_post_meta( $field_id, 'af_addon_price_range_checkbox', true ) ) {
				$addon_field_price_range_checkbox = 'checked';
			} else {
				$addon_field_price_range_checkbox = 'disabled';
			}

			$addon_field_price_type = get_post_meta( $field_id, 'af_addon_field_price_type', true );

			$addon_field_price = get_post_meta( $field_id, 'af_addon_field_price', true );

			$addon_field_file_extention = get_post_meta( $field_id, 'af_addon_upload_file_extention', true );

			$option_id_array  = array();
			$image_array      = array();
			$name_array       = array();
			$color_array      = array();
			$price_type_array = array();
			$price_array      = array();
			$priority_array   = array();

			$args = array(
				'post_type'   => 'af_pao_options',
				'post_status' => 'publish',
				'numberposts' => - 1,
				'post_parent' => $field_id,
				'fields'      => 'ids',
			);

			$options = get_posts( $args );

			foreach ( $options as $option_id ) {

				if ( empty( $option_id ) ) {
					continue;
				}

				$addon_option_id   = $option_id;
				$option_id_array[] = $addon_option_id;

				$addon_option_image = get_post_meta( $option_id, 'af_addon_field_options_image', true );
				$image_array[]      = $addon_option_image;

				$addon_option_title = get_post_meta( $option_id, 'af_addon_field_options_name', true );
				$name_array[]       = $addon_option_title;

				$addon_option_color = get_post_meta( $option_id, 'af_addon_option_color', true );
				$color_array[]      = $addon_option_color;

				$addon_option_price_type = get_post_meta( $option_id, 'af_addon_field_options_price_type', true );
				$price_type_array[]      = $addon_option_price_type;

				$addon_option_price = get_post_meta( $option_id, 'af_addon_field_options_price', true );
				$price_array[]      = $addon_option_price;

				$addon_option_priority = get_post_meta( $option_id, 'af_addon_option_priority', true );
				$priority_array[]      = $addon_option_priority;

			}

			if ( ! empty( $option_id_array ) ) {
				$addon_option_id = implode( ',', $option_id_array );
			} else {
				$addon_option_id = '';
			}

			$image_array = array_filter( $image_array );
			if ( ! empty( $image_array ) ) {
				$addon_option_image = implode( '/addon_img/', $image_array );
			} else {
				$addon_option_image = '';
			}

			if ( ! empty( $name_array ) ) {
				$addon_option_title = implode( ',', $name_array );
			} else {
				$addon_option_title = '';
			}

			if ( ! empty( $color_array ) ) {
				$addon_option_color = implode( ',', $color_array );
			} else {
				$addon_option_color = '';
			}

			if ( ! empty( $price_type_array ) ) {
				$addon_option_price_type = implode( ',', $price_type_array );
			} else {
				$addon_option_price_type = '';
			}

			if ( ! empty( $price_array ) ) {
				$addon_option_price = implode( ',', $price_array );
			} else {
				$addon_option_price = '';
			}

			if ( ! empty( $priority_array ) ) {
				$addon_option_priority = implode( ',', $priority_array );
			} else {
				$addon_option_priority = '';
			}

			$fields = array(
				$addon_field_priority,
				$addon_field_id,
				$addon_field_type,
				$addon_field_title,
				$addon_field_tooltip_checkbox,
				$addon_field_tooltip,
				$addon_field_desc_checkbox,
				$addon_field_desc,
				$addon_field_req_chekbox,
				$addon_field_limit_range_checkbox,
				$addon_field_min_limit_range,
				$addon_field_max_limit_range,
				$addon_field_price_range_checkbox,
				$addon_field_price_type,
				$addon_field_price,
				$addon_field_file_extention,
				$addon_option_id,
				$addon_option_image,
				$addon_option_color,
				$addon_option_title,
				$addon_option_price_type,
				$addon_option_price,
				$addon_option_priority,
			);

			fputcsv( $f1, $fields, $addon_delimiterr );
		}
		fseek( $f1, 0 );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		fpassthru( $f1 );
		exit;
	}

	public function add_on_import_csv( $s ) {
		if ( preg_match( '#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s ) ) {
			return $s;
		}

		if ( preg_match( '#[\x7F-\x9F\xBC]#', $s ) ) {
			return iconv( 'WINDOWS-1250', 'UTF-8', $s );
		}

		return iconv( 'ISO-8859-2', 'UTF-8', $s );
	}
}
