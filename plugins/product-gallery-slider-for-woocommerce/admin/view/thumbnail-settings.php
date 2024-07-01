<?php 
$fme_pgisfw_thumbnail_settings = get_option('fme_pgisfw_thumbnail_settings');
if (isset($_GET['rule_id'])) {
	$rule_id=filter_var($_GET['rule_id']);

	$fme_full_option=get_option('fme_pgisfw_save_rule_settings');
	
	$fme_pgisfw_thumbnail_settings=$fme_full_option[$rule_id];
}
if ( is_array($fme_pgisfw_thumbnail_settings) && ! array_key_exists('fme_pgisfw_slider_mode', $fme_pgisfw_thumbnail_settings)) {
	// default thumbnail slider mode
	$fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_mode']='loop';
} 


?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Thumbnail Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

				<tr valign="top" class="">
					<th id="fme_pgisfw_th" scope="row" class="titledesc">
						<label for="fme_pgisfw_hide_thumbnails">
							<?php echo esc_html__('Hide thumbnails', 'fme_pgisfw'); ?>
						</label>
					</th>
					<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
						<input name="fme_pgisfw_hide_thumbnails" 
						<?php
						if ( 'yes' == $fme_pgisfw_thumbnail_settings['fme_pgisfw_hide_thumbnails']) {
							echo 'checked';} 
						?>
							id="fme_pgisfw_hide_thumbnails" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_thumbnail_settings['fme_pgisfw_hide_thumbnails']); ?>">
							<span><?php esc_html_e('Hide thumbnail from slider', 'fme_pgisfw'); ?></span>
						</td>
					</tr>


					<tr class="fme_pgisfw_thumbs">
						<th id="fme_pgisfw_th" scope="row">
							<label for="fme_pgisfw_thumbs"><?php echo esc_html__('Thumbnails to show', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Choose number of thumbnails to show on single view of gallery slider."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" >
							<input type="number" min='1' max='6' class="regular" id="fme_pgisfw_thumbs" name="fme_pgisfw_thumbs" value="<?php echo esc_attr($fme_pgisfw_thumbnail_settings['fme_thumbnails_to_show']); ?>">
							<p class="fme_pgisfw_description"><?php echo esc_html__('Default : 5', 'fme_pgisfw'); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_slider_mode"><?php echo esc_html__('Thumbnail slider mode', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select Thumbnail slider mode"></span>
						</label>
					</th>
					<td id="fme_pgisfw_td" class="forminp forminp-select">
						<select name="fme_pgisfw_slider_mode" id="fme_pgisfw_slider_mode" class="">
							<option <?php selected('loop', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_mode'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_mode'] : '' ); ?> value="loop" selected="selected"><?php echo esc_html__('Loop', 'fme_pgisfw'); ?></option>
							<option <?php selected('slide', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_mode'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_mode'] : '' ); ?> value="slide"><?php echo esc_html__('Slide', 'fme_pgisfw'); ?></option>
							
						</select> 							
					</td>
				</tr>

					<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_slider_layout"><?php echo esc_html__('Thumbnail slider layout', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select position of thumbnail to show. Choose 'Horizontal' to show on bottom, 'Vertical Left' to show on left side and 'Vertical Right' to show on right side respectively."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-select">
							<select name="fme_pgisfw_slider_layout" id="fme_pgisfw_slider_layout" class="">
								<option <?php selected('horizontal', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] : '' ); ?> value="horizontal" selected="selected"><?php echo esc_html__('Horizontal', 'fme_pgisfw'); ?></option>
								<option <?php selected('left', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] : '' ); ?> value="left"><?php echo esc_html__('Vertical Left', 'fme_pgisfw'); ?></option>
								<option <?php selected('right', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_layout'] : '' ); ?> value="right"><?php echo esc_html__('Vertical Right', 'fme_pgisfw'); ?></option>
							</select> 							
						</td>
					</tr>
						<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_slider_images_style"><?php echo esc_html__('Thumbnail images style', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Choose thumbnail images style."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-select">
							<select name="fme_pgisfw_slider_images_style" id="fme_pgisfw_slider_images_style" class="">
								<option <?php selected('style1', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_images_style'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_images_style'] : '' ); ?> value="style1" selected="selected"><?php echo esc_html__('Layout 1', 'fme_pgisfw'); ?></option>
								<option <?php selected('style2', ( isset( $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_images_style'] ) ) ? $fme_pgisfw_thumbnail_settings['fme_pgisfw_slider_images_style'] : '' ); ?> value="style2"><?php echo esc_html__('Layout 2', 'fme_pgisfw'); ?></option>
								
							</select> 							
						</td>
					</tr>

					<tr>
						<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_border_color"><?php echo esc_html__(' Selected image border color', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select border color for images around thumbnail slider."></span>
						</label>
						</th>
						<td id="fme_pgisfw_td">
							<input type="text" class="jscolor" name="fme_pgisfw_border_color" id="fme_pgisfw_border_color" value="<?php echo esc_attr($fme_pgisfw_thumbnail_settings['fme_pgisfw_border_color']); ?>">
						</td>
					</tr>
					<tr>
						<th style="display: flow-root;">
							<input type="button" name="fme_pgisfw_btn" onclick="save_thumbnail_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
						</th>
						<td id="fme_pgisfw_td">
							<span id="fme_thumbnail_settings_msg" class="fme_success_alert">
								<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
							</span>
						</td>
					</tr>

				</tbody>
			</table>
		</div>
	</div>
