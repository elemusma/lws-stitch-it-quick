<?php 
$fme_pgisfw_general_settings = get_option('fme_pgisfw_general_settings');
?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('General Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

				<tr valign="top" class="">
					<th id="fme_pgisfw_th" scope="row" class="titledesc"><label for="fme_pgisfw_enable_disable"><?php echo esc_html__('Enable gallery slider', 'fme_pgisfw'); ?></label></th>
					<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
						<input name="fme_pgisfw_enable_disable" 
						<?php
						if (isset($fme_pgisfw_general_settings['fme_pgisfw_enable_disable']) && 'yes' == $fme_pgisfw_general_settings['fme_pgisfw_enable_disable']) {
							echo 'checked';}
						?>
							id="fme_pgisfw_enable_disable" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_general_settings['fme_pgisfw_enable_disable']); ?>">
							<span><?php esc_html_e('Enable Gallery Slider For Your Website.', 'fme_pgisfw'); ?></span>
						</td>
					</tr>

					<tr>
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label id="fme_pgisfw_list_label"><?php echo esc_html__('Product/Category restriction', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Restrict slider to show only on specific products or categories."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td">
							<select class="form-control fmeproductcategory" id="fmeproductcategory" name="selectpc[]" onchange="Fme_pgisfw_choosen_product_cateory('fme_create');">
								<option <?php selected('', ( isset( $fme_pgisfw_general_settings['fmeproductcategory'] ) ) ? $fme_pgisfw_general_settings['fmeproductcategory'] : '' ); ?> value=""><?php echo esc_html__('Visible for All Products', 'fme_pgisfw'); ?></option>
								<option <?php selected('fme_pgisfw_product', ( isset( $fme_pgisfw_general_settings['fmeproductcategory'] ) ) ? $fme_pgisfw_general_settings['fmeproductcategory'] : '' ); ?> value="fme_pgisfw_product"><?php echo esc_html__('Select Products', 'fme_pgisfw'); ?></option>
								<option <?php selected('fme_pgisfw_category', ( isset( $fme_pgisfw_general_settings['fmeproductcategory'] ) ) ? $fme_pgisfw_general_settings['fmeproductcategory'] : '' ); ?> value="fme_pgisfw_category"><?php echo esc_html__('Select Categories', 'fme_pgisfw'); ?></option>
							</select>
							<p class="fme_pgisfw_description" >
								<?php 
								echo esc_html__('Product gallery Slider can be restricted to only selected products/categories.', 'fme_pgisfw'); 
								?>
							</p>
						</td>
					</tr>



					<tr id="fme_pgisfw_Products"
					<?php 
					if ('fme_pgisfw_product' == $fme_pgisfw_general_settings['fmeproductcategory']) {
						echo 'style=display:contents';
					} else {
						echo 'style=display:none';
					}   
					?>
					>
					<th id="fme_pgisfw_th" scope="row" class="titledesc">
						<label id="fme_pgisfw_label"><?php echo esc_html__('Select product', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select specific products where you want to show gallery slider."></span>
						</label>
					</th>
					<td id="fme_pgisfw_td">
						<?php
						if ( '' == $fme_pgisfw_general_settings['fme_pgisfw_selected_pc']) {
							$fme_pgisfw_selected_pc = array();
						} else {
							$fme_pgisfw_selected_pc = $fme_pgisfw_general_settings['fme_pgisfw_selected_pc'];
						}
						global $post;
						$fme_pgisfw_product = array(
							'post_status' => 'publish',
							'ignore_sticky_posts' => 1,
							'posts_per_page' => 200,
							'orderby' => 'title',
							'order' => 'ASC',
							'post_type' => array( 'product' ),
						);
						$fme_pgisfw_Products = get_posts($fme_pgisfw_product);
						if (!empty($fme_pgisfw_Products)) { 
							?>
							<input type="hidden" id="fme_pgisfw_prd_count" value="<?php echo filter_var(count($fme_pgisfw_Products)); ?>" style="display:none;">
							<select class="Fme_choosen" id="fme_pgisfw_product" multiple="multiple" name="">
								<?php



								foreach ($fme_pgisfw_selected_pc as $key => $value) {
									$product_obj = wc_get_product( $value );

									if (!$product_obj) {

										continue;}
							
									?>
							<option value='<?php echo esc_attr( $value ); ?>' Selected>	<?php esc_html_e( $product_obj->get_name()); ?>
							</option>
									<?php 
								}

								foreach ($fme_pgisfw_Products as $fme_pgisfw_products) {
									
									if ( isset($fme_pgisfw_selected_pc) && is_array($fme_pgisfw_selected_pc) && in_array($fme_pgisfw_products->ID, $fme_pgisfw_selected_pc)) {
										continue;
									}

									?>
									<option  value="<?php echo esc_attr($fme_pgisfw_products->ID); ?>"><?php echo filter_var($fme_pgisfw_products->post_title); ?></option>
									<?php
								}
								?>
							</select>
						<?php }; ?>
						<p class="fme_pgisfw_description">
							<?php 
							echo esc_html__('If no product is selected it will show on all shop products.', 'fme_pgisfw'); 
							?>
						</p>
					</td>
				</tr>
				<tr id="fme_pgisfw_category"
				<?php 
				if ('fme_pgisfw_category' == $fme_pgisfw_general_settings['fmeproductcategory']) {
					echo 'style=display:contents';
				} else {
					echo 'style=display:none';
				}   
				?>
				>
				<th id="fme_pgisfw_th">
					<label id="fme_pgisfw_label"><?php echo esc_html__('Select category', 'fme_pgisfw'); ?>
						<span class="woocommerce-help-tip" data-tip="Select specific categories where you want to show gallery slider."></span>
					</label>
				</th>
				<td id="fme_pgisfw_td">
					<?php 
					if ( '' == $fme_pgisfw_general_settings['fme_pgisfw_selected_pc'] ) {
						$fme_pgisfw_selected_pc = array();
					} else {
						$fme_pgisfw_selected_pc = $fme_pgisfw_general_settings['fme_pgisfw_selected_pc'];
					}
					$fme_pgisfw_category = array(
						'taxonomy' => 'product_cat',
					);
					$fme_pgisfw_categories = get_terms($fme_pgisfw_category);
					if (!empty($fme_pgisfw_categories)) { 
						?>
						<select class="Fme_choosen" id="fme_pgisfw_categorys" multiple="multiple" name="">
							<?php
							foreach ($fme_pgisfw_categories as $category) {
								?>
								<option <?php selected(in_array($category->term_id, $fme_pgisfw_selected_pc), true); ?> value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_attr($category->name); ?></option>
								<?php
							}
							?>
						</select>
					<?php } ?>
				</td>
			</tr>


		



			<tr class="autoPlay">
				<th scope="row" id="fme_pgisfw_th">
					<label for="fme_pgisfw_autoPlay"><?php echo esc_html__('Auto play slider', 'fme_pgisfw'); ?>
						<span class="woocommerce-help-tip" data-tip="Enable or diable auto play option for slider."></span>
					</label>
				</th>
				<td id="fme_pgisfw_td">
					<select class="regular" name="fme_pgisfw_autoPlay" id="fme_pgisfw_autoPlay">
						<option <?php selected('true', ( isset( $fme_pgisfw_general_settings['fme_auto_play'] ) ) ? $fme_pgisfw_general_settings['fme_auto_play'] : '' ); ?> value="true"><?php echo esc_html__('Yes', 'fme_pgisfw'); ?></option>
						<option <?php selected('false', ( isset( $fme_pgisfw_general_settings['fme_auto_play'] ) ) ? $fme_pgisfw_general_settings['fme_auto_play'] : '' ); ?> value="false"><?php echo esc_html__('No', 'fme_pgisfw'); ?></option>
					</select>
					<p class="fme_pgisfw_description"><?php echo esc_html__('Default: no', 'fme_pgisfw'); ?></p>
				</td>
			</tr>
			
			<tr valign="top" class="">
				<th id="fme_pgisfw_th" scope="row" class="titledesc"><label for="fme_pgisfw_numbering_enable_disable"><?php echo esc_html__('Enable numbering on gallery', 'fme_pgisfw'); ?></label></th>
				<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
					<input name="fme_pgisfw_numbering_enable_disable" 
					<?php
					if (isset($fme_pgisfw_general_settings['fme_pgisfw_numbering_enable_disable']) && 'yes' == $fme_pgisfw_general_settings['fme_pgisfw_numbering_enable_disable']) {
						echo 'checked';}
					?>
						id="fme_pgisfw_numbering_enable_disable" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_general_settings['fme_pgisfw_numbering_enable_disable']); ?>">
						<span><?php esc_html_e('Enable numbering on images gallery.', 'fme_pgisfw'); ?></span>
					</td>
				</tr>

				<tr valign="top" class="">
				<th id="fme_pgisfw_th" scope="row" class="titledesc"><label for="fme_pgisfw_image_aspect_ratio"><?php echo esc_html__('Adjust aspect ratio', 'fme_pgisfw'); ?></label></th>
				<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
					<input name="fme_pgisfw_image_aspect_ratio" 
					<?php
					if (isset($fme_pgisfw_general_settings['fme_pgisfw_image_aspect_ratio']) && 'on' == $fme_pgisfw_general_settings['fme_pgisfw_image_aspect_ratio']) {
						echo 'checked';}
					?>
						id="fme_pgisfw_image_aspect_ratio" type="checkbox" class="" >
						<span><?php esc_html_e('Enable to automatically adjust image aspect ratio when using vertical layout.', 'fme_pgisfw'); ?></span>
					</td>
				</tr>

				<tr class="fme_pgisfw_numbering_color">
					<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_numbering_color"><?php echo esc_html__(' Numbering font color', 'fme_pgisfw'); ?>
						<span class="woocommerce-help-tip" data-tip="Select font color for gallery numbering."></span>
					</label>
					</th>
					<td id="fme_pgisfw_td">
						<input type="text" class="jscolor" name="fme_pgisfw_numbering_color" id="fme_pgisfw_numbering_color" value="<?php echo esc_attr($fme_pgisfw_general_settings['fme_pgisfw_numbering_color']); ?>">
					</td>
				</tr>
			<tr id="fme_pgisfw_auto_play_speed">
				<th id="fme_pgisfw_th" scope="row">
					
					<label for="fme_pgisfw_autoPlay_time_out"><?php echo esc_html__('Auto play timeout', 'fme_pgisfw'); ?>
						<span class="woocommerce-help-tip" data-tip="Set time for auto play timer to change image."></span>
					</label>
			</th>
			<td id="fme_pgisfw_td">
				<div class="range-slider">
					<input class="regular" type="number" id="fme_pgisfw_range_slider" value="<?php echo esc_attr($fme_pgisfw_general_settings['fme_auto_play_timeout']); ?>" min="100" max="5000">
					<span class="range-slider__value"></span>
				</div>
				<p class="fme_pgisfw_description"><?php echo esc_html__('1000 = 1 sec', 'fme_pgisfw'); ?></p>
			</td>
		</tr>


			<tr>
				<th style="display: flow-root;">
					<input type="button" name="fme_pgisfw_general_btn" onclick="save_general_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
				</th>
				<td id="fme_pgisfw_td">
					<span id="fme_general_settings_msg" class="fme_success_alert">
						<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
					</span>
				</td>
			</tr>

		</tbody>
	</table>
</div>
</div>
