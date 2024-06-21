<?php

/**
 * AJAX and their call backs
 */
class Af_Addon_Ajax {


	public function __construct() {

		add_action( 'wp_ajax_af_pao_cat_ajax', array( $this, 'af_pao_cat_ajax' ) );

		add_action( 'wp_ajax_af_pao_prod_ajax', array( $this, 'af_pao_prod_ajax' ) );

		add_action( 'wp_ajax_af_pao_add_field', array( $this, 'af_pao_add_field' ) );

		add_action( 'wp_ajax_af_pao_add_option', array( $this, 'af_pao_add_option' ) );

		add_action( 'wp_ajax_af_pao_remove_field', array( $this, 'af_pao_remove_field' ) );

		add_action( 'wp_ajax_af_pao_remove_option', array( $this, 'af_pao_remove_option' ) );

		add_action( 'wp_ajax_af_addon_dependable_field', array( $this, 'af_addon_dependable_field' ) );

		add_action( 'wp_ajax_af_vari_addons', array( $this, 'af_vari_addons' ) );
	}

	public function af_pao_cat_ajax() {

		$nonce = isset( $_POST['nonce'] ) && '' !== $_POST['nonce'] ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( isset( $_POST['q'] ) && '' !== $_POST['q'] ) {
			if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {
				die( 'Failed ajax security check!' );
			}
			$pro = sanitize_text_field( wp_unslash( $_POST['q'] ) );
		} else {
			$pro = '';
		}

		$data_array         = array();
		$orderby            = 'name';
		$order              = 'asc';
		$hide_empty         = false;
		$cat_args           = array(
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty,
			'name__like' => $pro,
		);
		$product_categories = get_terms( 'product_cat' );
		if ( ! empty( $product_categories ) ) {
			foreach ( $product_categories as $proo ) {
				$pro_front_post = ( mb_strlen( $proo->name ) > 50 ) ? mb_substr( $proo->name, 0, 49 ) . '...' : $proo->name;
				$data_array[]   = array( $proo->term_id, $pro_front_post ); // array( Post ID, Post Title ).
			}
		}
		echo wp_json_encode( $data_array );
		die();
	}

	public function af_pao_prod_ajax() {
		$nonce = isset( $_POST['nonce'] ) && '' !== $_POST['nonce'] ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;
		if ( isset( $_POST['q'] ) && '' !== $_POST['q'] ) {
			if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {
				die( 'Failed ajax security check!' );
			}
			$pro = sanitize_text_field( wp_unslash( $_POST['q'] ) );
		} else {
			$pro = '';
		}
		$data_array = array();
		$args       = array(
			'post_type'   => array( 'product', 'product_variation' ),
			'post_status' => 'publish',
			'numberposts' => 50,
			's'           => $pro,
		);
		$pros       = get_posts( $args );
		if ( ! empty( $pros ) ) {
			foreach ( $pros as $proo ) {
				$title            = ( mb_strlen( $proo->post_title ) > 50 ) ? mb_substr( $proo->post_title, 0, 49 ) . '...' : $proo->post_title;
					$data_array[] = array( $proo->ID, $title ); // array( Post ID, Post Title ).
			}
		}
			echo wp_json_encode( $data_array );
			die();
	}

	public function af_pao_add_field() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			die( 'Failed security check' );
		}

		if ( isset( $_POST['current_rule_id'] ) && isset( $_POST['type'] ) ) {

			$current_rule_id = sanitize_text_field( wp_unslash( $_POST['current_rule_id'] ) );

			$type = sanitize_text_field( wp_unslash( $_POST['type'] ) );

			$create_new_field = array(

				'post_type'   => 'af_pao_fields',

				'post_status' => 'publish',

				'post_parent' => $current_rule_id,

			);

			$insert_field = wp_insert_post( $create_new_field );

			$field_id = $insert_field;

			?>

				<input type="hidden" name="addon_hidden" class="addon_hidden" value="<?php echo esc_attr( $field_id ); ?>">

				<?php

				if ( 'product' == $type ) {

					$this->af_addon_product_field_template( $current_rule_id, $field_id );

				} elseif ( 'variation' == $type ) {

					$this->af_addon_variation_field_template( $current_rule_id, $field_id );

				} else {

					$this->af_addon_rule_field_template( $current_rule_id, $field_id );
				}
		}

		die();
	}

	public function af_pao_add_option() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			die( 'Failed security check' );

		}

		if ( isset( $_POST['current_rule_id'] ) && isset( $_POST['current_field_id'] ) && isset( $_POST['add_file_with'] ) ) {

			$current_field_id = sanitize_text_field( wp_unslash( $_POST['current_field_id'] ) );

			$current_rule_id = sanitize_text_field( wp_unslash( $_POST['current_rule_id'] ) );

			$add_file_with = sanitize_text_field( wp_unslash( $_POST['add_file_with'] ) );

			$create_new_option = array(

				'post_type'   => 'af_pao_options',

				'post_status' => 'publish',

				'post_parent' => $current_field_id,

			);

			$insert_option = wp_insert_post( $create_new_option );

			$this->af_addon_ajax_row_template( $current_rule_id, $current_field_id, $insert_option );
		}

		die();
	}

	public function af_pao_remove_field() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			die( 'Failed security check' );

		}

		if ( isset( $_POST['remove_field_id'] ) ) {

			$current_field_id = isset( $_POST['current_post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['current_post_id'] ) ) : '';

			$remove_field_id = isset( $_POST['remove_field_id'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_field_id'] ) ) : '';

			wp_delete_post( $remove_field_id );

		}

		die();
	}

	public function af_pao_remove_option() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			die( 'Failed security check' );

		}

		if ( isset( $_POST['remove_option_id'] ) ) {

			$current_rule_id = isset( $_POST['current_rule_id'] ) ? sanitize_text_field( wp_unslash( $_POST['current_rule_id'] ) ) : '';

			$remove_option_id = isset( $_POST['remove_option_id'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_option_id'] ) ) : '';

			wp_delete_post( $remove_option_id );

		}

		die();
	}

	public function af_addon_dependable_field() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : 0;

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			die( 'Failed security check' );

		}

		if ( isset( $_POST['current_field_id'] ) && isset( $_POST['field_value'] ) && isset( $_POST['option_value'] ) ) {

			$current_field_id = sanitize_text_field( wp_unslash( $_POST['current_field_id'] ) );

			$field_value = sanitize_text_field( wp_unslash( $_POST['field_value'] ) );

			$option_value_array = sanitize_text_field( wp_unslash( $_POST['option_value'] ) );

			$option_value = json_decode( stripslashes( $option_value_array ) );

			$field_value = json_decode( stripslashes( $field_value ) );

			$af_aaddon_field_array = array();

			$args = array(

				'post_type'   => 'af_pao_options',

				'post_status' => 'publish',

				'post_parent' => $field_value,

				'fields'      => 'ids',
			);

			$options = get_posts( $args );

			foreach ( $options as $option_id ) {

				$af_aaddon_field_array[ $option_id ] = $option_id;

				$option_name = 'af_addon_field_options_name';

				$addon_option_name = get_post_meta( $option_id, $option_name, true );

				?>

					<option value="<?php echo esc_attr( $option_id ); ?>"

					<?php

					if ( in_array( $option_id, $option_value ) ) {

						echo 'selected';

					}

					?>

						><?php echo esc_html( $addon_option_name ); ?>

					</option>

					<?php
			}
		}

		die();
	}

	public function af_addon_product_field_template( $current_prod_id, $field_id ) {

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
								echo in_array( $selected_fields_option_id, $af_addon_option_depend_selector ) ? esc_html__( 'selected') : '';
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
					<textarea name="af_addon_desc_textarea[<?php echo intval( $field_id ); ?>]" class="desc_text_area<?php echo intval( $field_id ); ?> desc_text_area"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?></textarea>
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
								'post_type'   => 'af_pao_options',
								'post_status' => 'publish',
								'post_parent' => $field_id,
								'fields'      => 'ids',
								'orderby'     => 'menu_order',
								'order'       => 'ASC',
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

	public function af_addon_rule_field_template( $current_post_id, $field_id ) {

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
								'textarea'       => 'Text area',
								'file_upload'    => 'File Upload',
								'number'         => 'Number',
								'radio'          => 'Radio',
								'color_swatcher' => 'Color',
								'image_swatcher' => 'Image Switcher',
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
							<label style="vertical-align: text-bottom;"><?php echo esc_html__( 'Priority', 'addify_pao' ); ?></label>
							<input type="number" min="0" name="af_addon_field_sort[<?php echo esc_attr( $field_id ); ?>]" style="height: 36px; width: 20%; vertical-align: top;" placeholder="0" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_sort', true ) ); ?>">
							<button class="af_addon_remove_btn af_addon_remove_btn_<?php echo intval( $field_id ); ?>" data-remove_field_id="<?php echo intval( $field_id ); ?>" data-current_post_id="<?php echo intval( $current_post_id ); ?>" >
								<?php echo esc_html__( 'Remove', 'addify_pao' ); ?>
							</button>

							<button class="fa fa-sort-up <?php echo intval( $field_id ); ?>fa-sort-up" data-field_id="<?php echo intval( $field_id ); ?>"></button>

							<button class="fa fa-sort-down <?php echo intval( $field_id ); ?>fa-sort-down" data-field_id="<?php echo intval( $field_id ); ?>"></button>
						</div>
					</div>
					<div class="<?php echo intval( $field_id ); ?>_af_addon_dependable_div af_addon_dependable_div">
						<div class="af_addon_dependable_fields_div">
							<div class="af_addon_dependable_selector_div">
								<b><?php echo esc_html__( 'Field Dependability', 'addify_pao' ); ?></b>
								<span class="tooltip">
									<i class="fa fa-question-circle"></i>
									<span class="tooltiptext"><?php echo esc_html( 'Select if you want to make field dependable' ); ?></span>
								</span>
								<select style="width: 100%; height: 40px; margin-top: 7px;" name="af_addon_depend_selector[<?php echo intval( $field_id ); ?>]" class="<?php echo intval( $field_id ); ?>_af_addon_depend_selector af_addon_depend_selector " data-current_field_id="<?php echo intval( $field_id ); ?>">
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

						<b><?php echo esc_html__( 'Field', 'addify_pao' ); ?></b>
						<span class="tooltip">
							<i class="fa fa-question-circle"></i>
							<span class="tooltiptext"><?php echo esc_html( 'Select existing field to make this field dependable to. Field can only be made dependable to multi choice, Dropdown, Checkboxes, Radio Buttons, Images and Image Switcher.' ); ?></span>
						</span>
						<select style="height: 40px; width: 100%; margin-top: 7px;" name="af_addon_field_depend_selector[<?php echo intval( $field_id ); ?>]" class="af_addon_field_depend_selector " data-current_field_id="<?php echo intval( $field_id ); ?>" placeholder="Select Field">
							<option value="0"><?php echo esc_html( '' ); ?></option>
							<?php
							$current_field_id = $field_id;
							$args             = array(
								'post_type'   => 'af_pao_fields',
								'post_status' => 'all',
								'post_parent' => $current_post_id,
								'numberposts' => -1,
								'fields'      => 'ids',
							);

							$fields = get_posts( $args );

							foreach ( $fields as $all_field_ids ) {
								if ( empty( $all_field_ids ) ) {
									continue;
								}
								if ( $current_field_id != $all_field_ids ) {
									$field_type = get_post_meta( $all_field_ids, 'af_addon_type_select', true );

									if ( 'drop_down' == $field_type || 'multi_select' == $field_type || 'check_boxes' == $field_type || 'radio' == $field_type || 'color_swatcher' == $field_type || 'image_swatcher' == $field_type || 'image' == $field_type ) {
										?>
										<option value="<?php echo intval( $all_field_ids ); ?>"
											<?php
											if ( get_post_meta( $field_id, 'af_addon_field_depend_selector', true ) == $all_field_ids ) {
												echo 'selected';
											}
											?>
											>
											<?php
											echo esc_attr( get_post_meta( $all_field_ids, 'af_addon_field_title', true ) );
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
						<b><?php echo esc_html__( 'Options', 'addify_pao' ); ?></b>
						<span class="tooltip">
							<i class="fa fa-question-circle"></i>
							<span class="tooltiptext"><?php echo esc_html( 'Select the field option against which this field will trigger.' ); ?></span>
						</span>
						<?php
						$af_addon_option_depend_selector = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );
						?>
						<select style="height: 40px; width: 100%; margin-top: 7px;" name="af_addon_option_depend_selector[<?php echo intval( $field_id ); ?>][]" multiple="multiple" class="af_addon_option_depend_selector <?php echo intval( $field_id ); ?>_af_addon_option_depend_selector " data-current_field_id="<?php echo intval( $field_id ); ?>" placeholder="Select Option">
						<?php
						foreach ( $af_addon_option_depend_selector as $selected_fields_option_id ) {
							if ( empty( $selected_fields_option_id ) ) {
								continue;
							}
							?>
								<option value="<?php echo intval( $selected_fields_option_id ); ?>"
								<?php
								echo in_array( $selected_fields_option_id, $af_addon_option_depend_selector ) ? esc_html__( 'selected', 'addify_pao' ) : '';
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
					<p><b><?php echo esc_html__( 'Type', 'addify_pao' ); ?></b></p>
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
						><?php echo esc_html__( 'Text area', 'addify_pao' ); ?></option>
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
						><?php echo esc_html__( 'Image Switcher', 'addify_pao' ); ?></option>
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
					<p><b><?php echo esc_html__( 'Title', 'addify_pao' ); ?></b></p>
					<input type="text" name="af_addon_field_title[<?php echo intval( $field_id ); ?>]" class="af_addon_title_field " data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_title[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?>" required>
				</div>
			</div>
			<div class="<?php echo intval( $field_id ); ?>_af_addon_tooltip_div af_addon_tooltip_div">
				<div class="af_pao_width_100">
					<input type="hidden" name="af_addon_tooltip_id" value="<?php echo intval( $field_id ); ?>">
					<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_tooltip_checkbox[<?php echo intval( $field_id ); ?>]" type="checkbox" name="af_addon_tooltip_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_tooltip_checkbox af_addon_tooltip_checkbox<?php echo intval( $field_id ); ?>"
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
					<input data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_desc_checkbox[<?php echo intval( $field_id ); ?>]" type="checkbox" name="af_addon_desc_checkbox[<?php echo intval( $field_id ); ?>]" value="1" class=" af_addon_desc_checkbox af_addon_desc_checkbox<?php echo intval( $field_id ); ?>"
					<?php
					if ( '1' == get_post_meta( $field_id, 'af_addon_desc_checkbox', true ) ) {
						echo 'checked';
					}
					?>
					>
					<span><b><?php echo esc_html__( 'Add Description?', 'addify_pao' ); ?></b></span>
					<textarea name="af_addon_desc_textarea[<?php echo intval( $field_id ); ?>]" class="desc_text_area<?php echo intval( $field_id ); ?> desc_text_area"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?></textarea>
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
					<span><b><?php echo esc_html__( 'Limit Range', 'addify_pao' ); ?></b></span>
					<div class="af_addon_limit_range_divs af_addon_limit_range_divs<?php echo intval( $field_id ); ?>" data-rule_check="rule">

						<input type="number" name="af_addon_min_limit_range[<?php echo intval( $field_id ); ?>]" placeholder="0" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_min_limit_range', true ) ); ?>" style="height: 40px; width:30%;" min="0">

						<span><?php echo esc_html__( '--', 'addify_pao' ); ?></span>

						<input type="number" name="af_addon_max_limit_range[<?php echo intval( $field_id ); ?>]" placeholder="999" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_max_limit_range', true ) ); ?>" style="height: 40px; width:30%;" min="0">
						<p>
							<em><?php echo esc_html__( 'Enter a minimum and maximum value for the limit range.', 'addify_pao' ); ?></em>
						</p>
						<p>
							<em><?php echo esc_html__( 'Only max length will be applied on field type Telephone, which will be fixed length for telephone', 'addify_pao' ); ?></em>
						</p>

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
						<span><b><?php echo esc_html__( 'Price Range', 'addify_pao' ); ?></b></span>
					</div>
					<div class="af_addon_type_price_div<?php echo intval( $field_id ); ?> af_addon_type_price_div">
						<?php $field_price_type = $field_id . '_af_addon_field_price_type'; ?>
						<select data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_price_type[<?php echo intval( $field_id ); ?>]" name="af_addon_field_price_type[<?php echo intval( $field_id ); ?>]" class="af_addon_type_select ">
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

						<input type="number" class="af_addon_field_price" name="af_addon_field_price[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_price', true ) ); ?>" placeholder="0.00" min='0'>
						<p>
							<em><?php echo esc_html__( 'Select price type and price.', 'addify_pao' ); ?></em>
						</p>
					</div>
				</div>
			</div>
			<div class="<?php echo intval( $field_id ); ?>_af_addon_file_extention_div af_addon_file_extention_div">
				<div class="af_pao_width_100">
					<div class="af_addon_file_extention<?php echo intval( $field_id ); ?> af_addon_file_extention">
						<p><b><?php echo esc_html__( 'File Type', 'addify_pao' ); ?></b></p>

						<input type="text" class="af_addon_upload_file_extention " name="af_addon_upload_file_extention[<?php echo intval( $field_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_upload_file_extention', true ) ); ?>">
						<p>
							<em><?php echo esc_html__( 'Enter file extention (Comma Seperated).', 'addify_pao' ); ?></em>
						</p>
						<p>
							<em><?php echo esc_html__( 'e.g, jpg, jpeg etc', 'addify_pao' ); ?></em>
						</p>
					</div>
				</div>
			</div>
			<div class="<?php echo intval( $field_id ); ?>_af_addon_option_table_div af_addon_option_table_div">
				<div class="<?php echo intval( $field_id ); ?>_af_addon_table_div af_addon_table_div">
					<table class="<?php echo intval( $field_id ); ?>_af_addon_option_table af_addon_option_table">
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
								'post_type'   => 'af_pao_options',
								'post_status' => 'publish',
								'post_parent' => $field_id,
								'numberposts' => -1,
								'fields'      => 'ids',
								'orderby'     => 'menu_order',
								'order'       => 'ASC',
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
													value="<?php echo esc_url( $image ); ?>" name="af_addon_field_options_image[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" id="<?php echo intval( $field_id ); ?>af_addon_image_upload<?php echo intval( $option_id ); ?>" class="login_title">

													<img class="<?php echo intval( $field_id ); ?>af_addon_option_image<?php echo intval( $option_id ); ?> af_addon_option_image"  <?php if ( empty( $image ) ) : ?>
													style = 'display: none;'
													<?php endif ?>  src="<?php echo esc_url( $image ); ?>"/>

													<span id="remove_option_image<?php echo intval( $option_id ); ?>"  class="remove_option_image fa fa-trash" <?php if ( empty( $image ) ) : ?>
													style = 'display: none;'
													<?php endif ?>></span>
												</div>

												<div class="<?php echo intval( $field_id ); ?>_af_addon_option_color_div" style="padding: 5px;">

													<input type="text" name="af_addon_option_color[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_color', true ) ); ?>" class="my-color-field <?php echo intval( $field_id ); ?>_af_addon_option_color" data-default-color="#FFFFFF" />
												</div>

												<div class="af_addon_option_name_div <?php echo intval( $field_id ); ?>_af_addon_option_name_div">

													<input type="text" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" <?php echo intval( $field_id ); ?>_af_addon_option_name af_addon_option_name" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_name', true ) ); ?>" required>
												</div>
											</div>
										</td>

										<td>
											<select data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class="af_addon_type_select" style="width: 100% !important;">
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
											<input type="number" min="0" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" af_addon_field_options_price" name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_price', true ) ); ?>" style="width: 100% !important;">
										</td>

										<td>
											<input type="number" min="0" name="af_addon_option_priority[<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $option_id ); ?>]" style="height: 40px; width: 75%; vertical-align: top; float: left;" placeholder="Priority" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_priority', true ) ); ?>">
											<button class="af_addon_delete_btn af_addon_delete_btn_<?php echo intval( $option_id ); ?>" data-remove_option_id="<?php echo intval( $option_id ); ?>"data-current_post_id="<?php echo intval( $current_post_id ); ?>"><?php echo esc_html__( 'X', 'addify_pao' ); ?></button>
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
						<button class="af_addon_add_option_btn" data-current_rule_id ="<?php echo intval( $current_post_id ); ?> "   data-current_field_id ="<?php echo intval( $field_id ); ?>"  data-add_file_with ="rule" ><?php echo esc_html__( 'Add Option', 'addify_pao' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function af_addon_variation_field_template( $current_prod_id, $field_id ) {

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
				<textarea name="af_addon_desc_textarea[<?php echo intval( $field_id ); ?>]" class="desc_text_area<?php echo intval( $field_id ); ?> desc_text_area"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?></textarea>
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
							'post_type'   => 'af_pao_options',
							'post_status' => 'publish',
							'post_parent' => $field_id,
							'fields'      => 'ids',
							'orderby'     => 'menu_order',
							'order'       => 'ASC',
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

	public function af_addon_ajax_row_template( $current_post_id, $field_id, $option_id ) {

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
							value="<?php echo esc_url( $image ); ?>" name="af_addon_field_options_image[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" id="<?php echo intval( $field_id ); ?>af_addon_image_upload<?php echo intval( $option_id ); ?>" class="login_title">

							<img class="<?php echo intval( $field_id ); ?>af_addon_option_image<?php echo intval( $option_id ); ?> af_addon_option_image"  <?php if ( empty( $image ) ) : ?>
							style = 'display: none;'
							<?php endif ?>  src="<?php echo esc_url( $image ); ?>"/>

							<span id="remove_option_image<?php echo intval( $option_id ); ?>"  class="remove_option_image fa fa-trash" <?php if ( empty( $image ) ) : ?>
							style = 'display: none;'
							<?php endif ?>></span>
						</div>

						<div class="<?php echo intval( $field_id ); ?>_af_addon_option_color_div" style="padding: 5px;">

							<input type="text" name="af_addon_option_color[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_color', true ) ); ?>" class="my-color-field <?php echo intval( $field_id ); ?>_af_addon_option_color" data-default-color="#FFFFFF" />
						</div>

						<div class="af_addon_option_name_div <?php echo intval( $field_id ); ?>_af_addon_option_name_div">

							<input type="text" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_name[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" <?php echo intval( $field_id ); ?>_af_addon_option_name af_addon_option_name" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_name', true ) ); ?>" required>
						</div>
					</div>
				</td>

				<td>
					<select data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" name="af_addon_field_options_price_type[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class="af_addon_type_select" style="width: 100% !important;">
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
					<input type="number" min="0" data-current_field_id="<?php echo intval( $field_id ); ?>" data-id_name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" class=" af_addon_field_options_price" name="af_addon_field_options_price[<?php echo intval( $field_id ); ?>][<?php echo intval( $option_id ); ?>]" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_field_options_price', true ) ); ?>" style="width: 100% !important;">
				</td>

				<td>
					<div style="width: 70%; float: left;">
						<input type="number" min="0" name="af_addon_option_priority[<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $option_id ); ?>]" style="height: 40px; width: 100%; vertical-align: top;" placeholder="Priority" value="<?php echo esc_attr( get_post_meta( $option_id, 'af_addon_option_priority', true ) ); ?>">
					</div>
					
					<div style="width: 30%; float: right;">
						<button style="width: 100% !important" class="af_addon_delete_btn af_addon_delete_btn_<?php echo intval( $option_id ); ?>" data-remove_option_id="<?php echo intval( $option_id ); ?>"data-current_post_id="<?php echo intval( $current_post_id ); ?>"><?php echo esc_html__( 'X', 'addify_pao' ); ?></button>
					</div>
				</td>
			</tr>
			<?php
	}

	public function af_vari_addons() {

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, '_addify_pao_nonce' ) ) {

			wp_die( 'Failed security check' );
		}

		if ( isset( $_POST['form_data'] ) ) {

			ob_start();

			parse_str( sanitize_text_field( wp_unslash( $_POST['form_data'] ) ), $form_data );

			$addons = '';

			$prod_price = 0;

			$prod_title = '';

			$class_obj = new Af_Addon_Front_Style();
			$p_tag     = '';

			if ( isset( $form_data['variation_id'] ) && ! empty( $form_data['variation_id'] ) ) {

				$var_prod_info = wc_get_product( $form_data['variation_id'] );

				$prod_price = $var_prod_info->get_price();

				$prod_title = $var_prod_info->get_name();

				if ( '1' == get_post_meta( $form_data['variation_id'], 'var_style_tab_hide', true ) ) {

					$class_obj->af_addon_front_fields_styling( $form_data['variation_id'] );

					af_addon_var_front_fields( $form_data['variation_id'], $form_data['variation_id'] );

				} else {

					$class_obj->af_addon_front_fields_styling( $form_data['product_id'] );

					af_addon_var_front_fields( $form_data['variation_id'], $form_data['product_id'] );

				}

				if ( '1' != get_post_meta( $form_data['variation_id'], 'exclude_var_addons', true ) ) {

					af_addon_front_fields( $form_data['product_id'] );
				}

				if ( '1' != get_post_meta( $form_data['product_id'], 'exclude_rule_addons', true ) && '1' != get_post_meta( $form_data['variation_id'], 'exclude_var_rule_addons', true ) ) {

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

						$af_addon_user_role = get_post_meta( $rule_id, 'af_addon_user_role', true );

						$af_addon_use_roles = is_user_logged_in() ? current( wp_get_current_user()->roles ) : 'guest';

						if ( ! empty( $af_addon_user_role ) && ! in_array( (string) $af_addon_use_roles, (array) $af_addon_user_role, true ) ) {

							continue;
						}

						if ( $this->af_addon_prod_check( $form_data['variation_id'], $rule_id ) ) {

							af_addon_front_fields( $rule_id );
						}
					}
				}
			} elseif ( isset( $form_data['product_id'] ) ) {

				$main_prod_info = wc_get_product( $form_data['product_id'] );

				af_addon_front_fields( $form_data['product_id'] );

				$prod_price = $main_prod_info->get_price();

				$prod_title = $main_prod_info->get_name();

				if ( '1' != get_post_meta( $form_data['product_id'], 'exclude_rule_addons', true ) ) {

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

						$af_addon_user_role = get_post_meta( $rule_id, 'af_addon_user_role', true );

						$af_addon_use_roles = is_user_logged_in() ? current( wp_get_current_user()->roles ) : 'guest';

						if ( ! empty( $af_addon_user_role ) && ! in_array( (string) $af_addon_use_roles, (array) $af_addon_user_role, true ) ) {

							continue;
						}

						if ( $this->af_addon_prod_check( $form_data['product_id'], $rule_id ) ) {

							af_addon_front_fields( $rule_id );
						}
					}
				}
			}

			$addons = ob_get_clean();

			ob_start();

			?>
					
					<p class="af_pao_real_time_product_sub_total_calculation" data-prod_title="<?php echo esc_attr( $prod_title ); ?>" data-prod_price="<?php echo esc_attr( $prod_price ); ?>" data-currency_sym="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>"></p>
					
					<?php

					$p_tag = ob_get_clean();

					wp_send_json(
						array(

							'addons'          => $addons,

							'currency_symbol' => get_woocommerce_currency_symbol(),

							'prod_price'      => $prod_price,

							'prod_title'      => $prod_title,

							'p_tag'           => $p_tag,

						)
					);

					wp_die();
		}
	}

	public function af_addon_prod_check( $prod_id, $rule_id ) {

		$addon_selected_products = get_post_meta( $rule_id, 'af_pao_prod_search', true );

		$addon_selected_category = get_post_meta( $rule_id, 'af_pao_cat_search', true );

		$addon_selected_tags = get_post_meta( $rule_id, 'af_pao_tag_search', true );

		if ( empty( $addon_selected_products ) && empty( $addon_selected_category ) && empty( $addon_selected_tags ) ) {

			return true;
		}

		if ( ! empty( $addon_selected_products ) ) {

			if ( in_array( (int) $prod_id, $addon_selected_products ) ) {

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
}
	new Af_Addon_Ajax();
