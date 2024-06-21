<?php

function af_addon_front_fields( $rule_id ) {

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

		$af_addon_field_id = 'af_addon_field_' . $field_id;

		$dependent_fields = '';

		$dependent_options = '';

		if ( 'af_addon_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ) {

			$dependent_fields = get_post_meta( $field_id, 'af_addon_field_depend_selector', true );

			$dependent_options = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );

			$dependent_options = implode( ',', $dependent_options );
		}

		$type_selected_file_path = array(
			'drop_down'      => 'af-addon-drop-down.php',
			'multi_select'   => 'af-addon-multi-select.php',
			'check_boxes'    => 'af-addon-check-boxes.php',
			'input_text'     => 'af-addon-input-text.php',
			'textarea'       => 'af-addon-textarea.php',
			'file_upload'    => 'af-addon-file-upload.php',
			'number'         => 'af-addon-number.php',
			'radio'          => 'af-addon-radio.php',
			'color_swatcher' => 'af-addon-color-swatcher.php',
			'image_swatcher' => 'af-addon-image-swatcher.php',
			'image'          => 'af-addon-image.php',
			'date_picker'    => 'af-addon-date-picker.php',
			'email'          => 'af-addon-email.php',
			'password'       => 'af-addon-password.php',
			'time_picker'    => 'af-addon-time-picker.php',
			'telephone'      => 'af-addon-telephone.php',
		);

		$headin_type = array(
			'addon_h1' => 'h1',
			'addon_h2' => 'h2',
			'addon_h3' => 'h3',
			'addon_h4' => 'h4',
			'addon_h5' => 'h5',
			'addon_h6' => 'h6',
		);

		$heading_type = $headin_type[ ! empty( get_post_meta( $rule_id, 'af_addon_heading_type_selector', true ) ) ? get_post_meta( $rule_id, 'af_addon_heading_type_selector', true ) : '' ];

		$type_selected_option = get_post_meta( $field_id, 'af_addon_type_select', true );

		$af_addon_front_dep_class = 'af_addon_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ? 'af_addon_front_dep_class' : '';

		?>
		<div class="af_addon_field_class" >
			<?php

			if ( 'af_addon_title_display_heading' == get_post_meta( $rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $rule_id, 'af_addon_field_border', true ) && 'af_addon_title_inside_border' == get_post_meta( $rule_id, 'af_addon_title_position', true ) ) {

					?>
					<div class="addon_field_border_<?php echo intval( $rule_id ); ?>">

						<div class="af_addon_front_field_title_div_<?php echo intval( $rule_id ); ?>">
							
							<<?php echo esc_attr( $heading_type ); ?> class="addon_heading_styling_<?php echo intval( $rule_id ); ?>">
								<?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?>

								<?php

								if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

									?>

									<span class="tooltip_<?php echo esc_attr( $rule_id ); ?>">
									
										<i class="fa fa-question-circle"></i>
									
										<span class="tooltiptext_<?php echo esc_attr( $rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>

									</span>
									<?php

								}

								?>

							</<?php echo esc_attr( $heading_type ); ?>>

						</div>

						<?php

						if ( ! empty( $type_selected_option ) ) {

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
						}

						?>
					</div>
					<?php
				} else {
					?>
					<div class="af_addon_front_field_title_div_<?php echo intval( $rule_id ); ?>">
						<<?php echo esc_attr( $heading_type ); ?> class="addon_heading_styling_<?php echo intval( $rule_id ); ?>">
							<?php
							echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) );
							if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {
								?>
								<span class="tooltip_<?php echo esc_attr( $rule_id ); ?>">
									<i class="fa fa-question-circle"></i>
									<span class="tooltiptext_<?php echo esc_attr( $rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
								</span>
								<?php
							}
							?>
						</<?php echo esc_attr( $heading_type ); ?>>
					</div>

					<div class="addon_field_border_<?php echo intval( $rule_id ); ?>">
						
						<?php

						if ( ! empty( $type_selected_option ) ) {

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
						}

						?>
							
					</div>
					<?php
				}
			} elseif ( 'af_addon_title_display_text' == get_post_meta( $rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $rule_id, 'af_addon_field_border', true ) && 'af_addon_title_inside_border' == get_post_meta( $rule_id, 'af_addon_title_position', true ) ) {

					?>
				
					<div class="addon_field_border_<?php echo intval( $rule_id ); ?>">
				
						<div class="af_addon_front_field_title_div_<?php echo intval( $rule_id ); ?>">
				
							<span><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?></span>
				
							<?php

							if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

								?>
				
								<span class="tooltip_<?php echo esc_attr( $rule_id ); ?>">
				
									<i class="fa fa-question-circle"></i>
				
									<span class="tooltiptext_<?php echo esc_attr( $rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
				
								</span>
				
								<br>
				
								<?php

							}

							?>
				
						</div>
				
						<?php

						if ( ! empty( $type_selected_option ) ) {

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];

						}

						?>
				
					</div>
				
					<?php

				} else {

					?>
				
					<div class="af_addon_front_field_title_div_<?php echo intval( $rule_id ); ?>">
				
						<span><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?></span>
				
						<?php

						if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

							?>
				
							<span class="tooltip_<?php echo esc_attr( $rule_id ); ?>">
				
								<i class="fa fa-question-circle"></i>
				
								<span class="tooltiptext_<?php echo esc_attr( $rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
				
							</span>
				
							<?php

						}

						?>
				
					</div>
				
					<div class="addon_field_border_<?php echo intval( $rule_id ); ?>">
						
						<?php

						if ( ! empty( $type_selected_option ) ) {

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
						}

						?>
				
					</div>
				
					<?php

				}
			} elseif ( 'af_addon_title_display_none' == get_post_meta( $rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $rule_id, 'af_addon_field_border', true ) ) {

					?>
			
					<div class="addon_field_border_<?php echo intval( $rule_id ); ?>">
			
						<?php

						if ( ! empty( $type_selected_option ) ) {

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
						}

						?>
			
					</div>
			
					<?php

				} elseif ( ! empty( $type_selected_option ) ) {


						include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
				}
			}

			if ( '1' == get_post_meta( $field_id, 'af_addon_desc_checkbox', true ) ) {

				?>
			
				<p class="add_on_description_style_<?php echo intval( $rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?> </p>
			
				<?php

			}

			?>
			
			<br>
			
			<?php

			if ( '1' == get_post_meta( $rule_id, 'af_addon_seperator_checkbox', true ) ) {

				if ( 'af_addon_title_seperator_br' == get_post_meta( $rule_id, 'af_addon_title_seperator', true ) ) {

					?>
			
					<br>
			
					<?php

				}

				if ( 'af_addon_title_seperator_hr' == get_post_meta( $rule_id, 'af_addon_title_seperator', true ) ) {

					?>
			
					<hr>
			
					<?php
				}
			}
			?>
		</div>
		<?php
	}
}

function af_addon_var_front_fields( $product_id, $r_rule_id ) {

	$args = array(
		'post_type'   => 'af_pao_fields',
		'post_status' => 'publish',
		'numberposts' => -1,
		'post_parent' => $product_id,
		'fields'      => 'ids',
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
	);

	$fields = get_posts( $args );

	foreach ( $fields as $field_id ) {

		if ( empty( $field_id ) ) {
			continue;
		}

		$af_addon_field_id = 'af_addon_field_' . $field_id;

		$dependent_fields = '';

		$dependent_options = '';

		if ( 'af_addon_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ) {

			$dependent_fields = get_post_meta( $field_id, 'af_addon_field_depend_selector', true );

			$dependent_options = (array) get_post_meta( $field_id, 'af_addon_option_depend_selector', true );

			$dependent_options = implode( ',', $dependent_options );
		}

		$type_selected_file_path = array(
			'drop_down'      => 'af-addon-drop-down.php',
			'multi_select'   => 'af-addon-multi-select.php',
			'check_boxes'    => 'af-addon-check-boxes.php',
			'input_text'     => 'af-addon-input-text.php',
			'textarea'       => 'af-addon-textarea.php',
			'file_upload'    => 'af-addon-file-upload.php',
			'number'         => 'af-addon-number.php',
			'radio'          => 'af-addon-radio.php',
			'color_swatcher' => 'af-addon-color-swatcher.php',
			'image_swatcher' => 'af-addon-image-swatcher.php',
			'image'          => 'af-addon-image.php',
			'date_picker'    => 'af-addon-date-picker.php',
			'email'          => 'af-addon-email.php',
			'password'       => 'af-addon-password.php',
			'time_picker'    => 'af-addon-time-picker.php',
			'telephone'      => 'af-addon-telephone.php',
		);

		$headin_type = array(
			'addon_h1' => 'h1',
			'addon_h2' => 'h2',
			'addon_h3' => 'h3',
			'addon_h4' => 'h4',
			'addon_h5' => 'h5',
			'addon_h6' => 'h6',
		);

		$heading_type = $headin_type[ ! empty( get_post_meta( $product_id, 'af_addon_heading_type_selector', true ) ) ? get_post_meta( $product_id, 'af_addon_heading_type_selector', true ) : '' ];

		$type_selected_option = get_post_meta( $field_id, 'af_addon_type_select', true );

		$af_addon_front_dep_class = 'af_addon_dependable' == get_post_meta( $field_id, 'af_addon_depend_selector', true ) ? 'af_addon_front_dep_class' : '';

		?>
		<div class="af_addon_field_class" >
			<?php

			if ( 'af_addon_title_display_heading' == get_post_meta( $r_rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $r_rule_id, 'af_addon_field_border', true ) && 'af_addon_title_inside_border' == get_post_meta( $r_rule_id, 'af_addon_title_position', true ) ) {

					?>
					<div class="addon_field_border_<?php echo intval( $r_rule_id ); ?>">

						<div class="af_addon_front_field_title_div_<?php echo intval( $r_rule_id ); ?>">
							
							<<?php echo esc_attr( $heading_type ); ?> class="addon_heading_styling_<?php echo intval( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?>

								<?php

								if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

									?>

									<span class="tooltip_<?php echo esc_attr( $r_rule_id ); ?>">
									
										<i class="fa fa-question-circle"></i>
									
										<span class="tooltiptext_<?php echo esc_attr( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>

									</span>
									<?php

								}

								?>

							</<?php echo esc_attr( $heading_type ); ?>>

						</div>

						<?php

							$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
						?>
					</div>
					<?php
				} else {
					?>
					<div class="af_addon_front_field_title_div_<?php echo intval( $r_rule_id ); ?>">
						<<?php echo esc_attr( $heading_type ); ?> class="addon_heading_styling_<?php echo intval( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?>
							<?php
							if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {
								?>
								<span class="tooltip_<?php echo esc_attr( $r_rule_id ); ?>">
									<i class="fa fa-question-circle"></i>
									<span class="tooltiptext_<?php echo esc_attr( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
								</span>
								<?php
							}
							?>
						</<?php echo esc_attr( $heading_type ); ?>>
					</div>

					<div class="addon_field_border_<?php echo intval( $r_rule_id ); ?>">
						
						<?php

							$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];

						?>
							
					</div>
					<?php
				}
				?>
				<?php
			} elseif ( 'af_addon_title_display_text' == get_post_meta( $r_rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $r_rule_id, 'af_addon_field_border', true ) && 'af_addon_title_inside_border' == get_post_meta( $r_rule_id, 'af_addon_title_position', true ) ) {

					?>
				
					<div class="addon_field_border_<?php echo intval( $r_rule_id ); ?>">
				
						<div class="af_addon_front_field_title_div_<?php echo intval( $r_rule_id ); ?>">
				
							<span><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?></span>
				
							<?php

							if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

								?>
				
								<span class="tooltip_<?php echo esc_attr( $r_rule_id ); ?>">
				
									<i class="fa fa-question-circle"></i>
				
									<span class="tooltiptext_<?php echo esc_attr( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
				
								</span>
				
								<br>
				
								<?php

							}

							?>
				
						</div>
				
						<?php

							$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];

						?>
				
					</div>
				
					<?php

				} else {

					?>
				
					<div class="af_addon_front_field_title_div_<?php echo intval( $r_rule_id ); ?>">
				
						<span><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_field_title', true ) ); ?></span>
				
						<?php

						if ( '1' == get_post_meta( $field_id, 'af_addon_tooltip_checkbox', true ) ) {

							?>
				
							<span class="tooltip_<?php echo esc_attr( $r_rule_id ); ?>">
				
								<i class="fa fa-question-circle"></i>
				
								<span class="tooltiptext_<?php echo esc_attr( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_tooltip_textarea', true ) ); ?></span>
				
							</span>
				
							<?php

						}

						?>
				
					</div>
				
					<div class="addon_field_border_<?php echo intval( $r_rule_id ); ?>">
						
						<?php

							$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];

						?>
				
					</div>
				
					<?php

				}
			} elseif ( 'af_addon_title_display_none' == get_post_meta( $r_rule_id, 'af_addon_title_display_as_selector', true ) ) {

				if ( '1' == get_post_meta( $r_rule_id, 'af_addon_field_border', true ) ) {

					?>
			
					<div class="addon_field_border_<?php echo intval( $r_rule_id ); ?>">
			
						<?php

							$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

							include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];

						?>
			
					</div>
			
					<?php

				} else {

					$rule_id = ( '1' == get_post_meta( $product_id, 'var_style_tab_hide', true ) ) ? $product_id : wp_get_post_parent_id( $product_id );

					include AFPAO_PLUGIN_DIR . 'includes/front/rule_options/' . $type_selected_file_path[ $type_selected_option ];
				}
			}

			if ( '1' == get_post_meta( $field_id, 'af_addon_desc_checkbox', true ) ) {

				?>
			
				<p class="add_on_description_style_<?php echo intval( $r_rule_id ); ?>"><?php echo esc_attr( get_post_meta( $field_id, 'af_addon_desc_textarea', true ) ); ?> </p>
			
				<?php

			}

			?>
			
			<br>
			
			<?php

			if ( '1' == get_post_meta( $r_rule_id, 'af_addon_seperator_checkbox', true ) ) {

				if ( 'af_addon_title_seperator_br' == get_post_meta( $r_rule_id, 'af_addon_title_seperator', true ) ) {

					?>
			
					<br>
			
					<?php

				}

				if ( 'af_addon_title_seperator_hr' == get_post_meta( $r_rule_id, 'af_addon_title_seperator', true ) ) {

					?>
			
					<hr>
			
					<?php
				}
			}
			?>
		</div>
		<?php
	}
}
