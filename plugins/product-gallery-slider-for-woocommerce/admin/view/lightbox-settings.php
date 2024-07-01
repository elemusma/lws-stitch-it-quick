<?php 
$fme_pgisfw_lightbox_settings = get_option('fme_pgisfw_lightbox_settings');
if (isset($_GET['rule_id'])) {
	$rule_id=filter_var($_GET['rule_id']);

	$fme_full_option=get_option('fme_pgisfw_save_rule_settings');
	
	$fme_pgisfw_lightbox_settings=$fme_full_option[$rule_id];
}
?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Lightbox Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

				<tr valign="top" class="">
					<th id="fme_pgisfw_th" scope="row" class="titledesc"><label for="fme_pgisfw_show_lightbox"><?php echo esc_html__('Light box', 'fme_pgisfw'); ?></label></th>
					<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
						<input name="fme_pgisfw_show_lightbox" 
						<?php
						if ( 'yes' == $fme_pgisfw_lightbox_settings['fme_pgisfw_show_lightbox']) {
							echo 'checked';}
						?>
							id="fme_pgisfw_show_lightbox" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_lightbox_settings['fme_pgisfw_show_lightbox']); ?>">
							<span><?php esc_html_e('Show button for lightbox on image.', 'fme_pgisfw'); ?></span>
						</td>
					</tr>


					<tr>
						<th id="fme_pgisfw_th" scope="row">
							<label for="fme_pgisfw_lightbox_bg_color">
								<?php echo esc_html__('Lightbox icon bg color', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select background color for lightbox icon."></span>
							</label>
							
						</th>
						<td id="fme_pgisfw_td">
							<?php
							$opacity = '';
							if (strlen($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_bg_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_lightbox_bg_color" id="fme_pgisfw_lightbox_bg_color" value="<?php echo esc_attr($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_bg_color']) . filter_var($opacity); ?>">
						</td>
					</tr>




					<tr>
						<th id="fme_pgisfw_th" scope="row">
							<label for="fme_pgisfw_lightbox_bg_hover_color">
								<?php echo esc_html__('Lightbox bg hover color', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select background hover color for lightbox icon."></span>
							</label>
							
						</th>
						<td id="fme_pgisfw_td">
							<?php
							$opacity = '';
							if (strlen($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_bg_hover_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_lightbox_bg_hover_color" id="fme_pgisfw_lightbox_bg_hover_color" value="<?php echo esc_attr($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_bg_hover_color']) . filter_var($opacity); ?>">
						</td>
					</tr>

					<tr>
						<th id="fme_pgisfw_th" scope="row">
							<label for="fme_pgisfw_lightbox_icon_color">
								<?php echo esc_html__('Lightbox icon color', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select color for lightbox icon."></span>
							</label>
							
						</th>
						<td id="fme_pgisfw_td">
							<?php
							$opacity = '';
							if (strlen($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_icon_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_lightbox_icon_color" id="fme_pgisfw_lightbox_icon_color" value="<?php echo esc_attr($fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_icon_color']) . filter_var($opacity); ?>">
						</td>
					</tr>



					<tr class="navIcon">
						<th scope="row" id="fme_pgisfw_th">
							<label for="Navigation_Icons"><?php echo esc_html__('Lightbox position', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Set position for lightbox."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td">
							<select class="regular" name="fme_pgisfw_lightbox_position" id="fme_pgisfw_lightbox_position">
								<option <?php selected('fme_pgifw_top_left', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] : '' ); ?> value="fme_pgifw_top_left"><?php echo esc_html__('Top Left', 'fme_pgisfw'); ?></option>
								<option <?php selected('fme_pgifw_top_right', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] : '' ); ?> value="fme_pgifw_top_right"><?php echo esc_html__('Top Right', 'fme_pgisfw'); ?></option>
								<option <?php selected('fme_pgifw_bottom_left', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] : '' ); ?> value="fme_pgifw_bottom_left"><?php echo esc_html__('Bottom Left', 'fme_pgisfw'); ?></option>
								<option <?php selected('fme_pgifw_right_bottom', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_position'] : '' ); ?> value="fme_pgifw_right_bottom"><?php echo esc_html__('Bottom Right', 'fme_pgisfw'); ?></option>
							</select>
							<p class="fme_pgisfw_description"><?php echo esc_html__('Default: Bottom Right', 'fme_pgisfw'); ?></p>
						</td>
					</tr>


					<tr>
						<th id="fme_pgisfw_th" scope="row"><label ><?php echo esc_html__('Select lightbox icon', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select icon to shown in lightbox buttons."></span>
						</label>
						</th>
						<td id="fme_pgisfw_td">
							<div class='fme_lightbox_options'>
								<div >
									<img class="fme_lightbox_images <?php echo ( 0 == $fme_pgisfw_lightbox_settings['fme_selected_lightbox_image'] ) ? 'fme_selected_lightbox_image' : ''; ?>" src="<?php echo filter_var( FME_PGISFW_URL) . 'admin/assets/images/exp1.png'; ?>" curr_image="0">
								</div>
								<div >
									<img class="fme_lightbox_images <?php echo ( 1 == $fme_pgisfw_lightbox_settings['fme_selected_lightbox_image'] ) ? 'fme_selected_lightbox_image' : ''; ?>" src="<?php echo filter_var( FME_PGISFW_URL) . 'admin/assets/images/exp2.png'; ?>" curr_image="1">
								</div>
								<div >
									<img class="fme_lightbox_images <?php echo ( 2 == $fme_pgisfw_lightbox_settings['fme_selected_lightbox_image'] ) ? 'fme_selected_lightbox_image' : ''; ?>" src="<?php echo filter_var( FME_PGISFW_URL) . 'admin/assets/images/exp3.png'; ?>" curr_image="2">
								</div>
								<div >
									<img class="fme_lightbox_images <?php echo ( 3 == $fme_pgisfw_lightbox_settings['fme_selected_lightbox_image'] ) ? 'fme_selected_lightbox_image' : ''; ?>" src="<?php echo filter_var( FME_PGISFW_URL) . 'admin/assets/images/exp4.png'; ?>" curr_image="3">
								</div>
							</div>
						</td>
					</tr>	




					<tr class="fme_pgisfw_Lightbox_frame_width" >
						<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_Lightbox_frame_width"><?php echo esc_html__('Lightbox frame width (px)', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Width of lightbox frame."></span>
						</label>
						</th>
						<td id="fme_pgisfw_td">
							<input type='number' min='100' max='700' class="regular" id="fme_pgisfw_Lightbox_frame_width" name="fme_pgisfw_Lightbox_frame_width" value="<?php echo esc_attr($fme_pgisfw_lightbox_settings['fme_pgisfw_Lightbox_frame_width']); ?>">
							<p class="description"><?php echo esc_html__('Default: 600', 'fme_pgisfw'); ?></p>
						</td>
					</tr>
				
					<tr class="navIcon">
						<th scope="row" id="fme_pgisfw_th">
							<label for="Navigation_Icons"><?php echo esc_html__('Lightbox slide effect', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Set slide effect for lightbox."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td">
							<select class="regular" name="fme_pgisfw_lightbox_slide_effect" id="fme_pgisfw_lightbox_slide_effect">
								<option <?php selected('slide', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] : '' ); ?> value="slide"><?php echo esc_html__('Slide', 'fme_pgisfw'); ?></option>
								<option <?php selected('fade', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] : '' ); ?> value="fade"><?php echo esc_html__('Fade', 'fme_pgisfw'); ?></option>
								<option <?php selected('zoom', ( isset( $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] ) ) ? $fme_pgisfw_lightbox_settings['fme_pgisfw_lightbox_slide_effect'] : '' ); ?> value="zoom"><?php echo esc_html__('Zoom', 'fme_pgisfw'); ?></option>

							</select>
							<p class="fme_pgisfw_description"><?php echo esc_html__('Default: Slide', 'fme_pgisfw'); ?></p>
							
						</td>
					</tr>

					<tr>
						<th style="display: flow-root;">
							<input type="button" name="fme_pgisfw_btn" onclick="save_lightbox_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
						</th>
						<td id="fme_pgisfw_td">
							<span id="fme_lightbox_settings_msg" class="fme_success_alert">
								<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
							</span>
						</td>
					</tr>



				</tbody>
			</table>
		</div>
	</div>
