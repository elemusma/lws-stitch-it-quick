<?php 
$fme_pgisfw_zoom_settings = get_option('fme_pgisfw_zoom_settings');
if (isset($_GET['rule_id'])) {
	$rule_id=filter_var($_GET['rule_id']);

	$fme_full_option=get_option('fme_pgisfw_save_rule_settings');
	
	$fme_pgisfw_zoom_settings=$fme_full_option[$rule_id];
}
?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Zoom Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

			
					<tr valign="top" class="">
						<th id="fme_pgisfw_th" scope="row" class="titledesc"><label for="fme_pgisfw_show_zoom"><?php echo esc_html__('Zoom', 'fme_pgisfw'); ?></label></th>
						<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
							<input name="fme_pgisfw_show_zoom" id="fme_pgisfw_show_zoom" 
							<?php
							if ( 'yes' == $fme_pgisfw_zoom_settings['fme_pgisfw_show_zoom']) {
								echo 'checked';} 
							?>
								type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_zoom_settings['fme_pgisfw_show_zoom']); ?>">

								<span><?php esc_html_e('Show Zoombox On Image Hover', 'fme_pgisfw'); ?></span>

							</td>
						</tr>
						<tr class="fme_pgisfw_zoombox_frame_width">
							<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_zoombox_frame_width"><?php echo esc_html__('Zoom box frame width (px)', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Zoom box frame width."></span>
							</label>
							</th>
							<td id="fme_pgisfw_td">
								<input type='number' min='50' max='200' class="regular" id="fme_pgisfw_zoombox_frame_width" name="fme_pgisfw_zoombox_frame_width" value="<?php echo esc_attr($fme_pgisfw_zoom_settings['fme_pgisfw_zoombox_frame_width']); ?>">
								<p class="description"><?php echo esc_html__('Default: 100', 'fme_pgisfw'); ?></p>
							</td>
						</tr>
						<tr class="fme_pgisfw_zoombox_frame_height">
							<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_zoombox_frame_height"><?php echo esc_html__('Zoom box frame height (px)', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Zoom box frame height."></span>
							</label>
							</th>
							<td id="fme_pgisfw_td">
								<input type='number' min='50' max='200' class="regular" id="fme_pgisfw_zoombox_frame_height" name="fme_pgisfw_zoombox_frame_height" value="<?php echo esc_attr($fme_pgisfw_zoom_settings['fme_pgisfw_zoombox_frame_height']); ?>">
								<p class="description"><?php echo esc_html__('Default: 100', 'fme_pgisfw'); ?></p>
							</td>
						</tr>


						<tr class="fme_pgisfw_zoombox_radius">
							<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_zoombox_radius"><?php echo esc_html__('Zoom box radius (%)', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Zoom box frame radius. Min 0%, Max 50%"></span>
							</label>
							</th>
							<td id="fme_pgisfw_td">
								<input type='number' min='0' max='50' class="regular" id="fme_pgisfw_zoombox_radius" name="fme_pgisfw_zoombox_radius" value="<?php echo esc_attr($fme_pgisfw_zoom_settings['fme_pgisfw_zoombox_radius']); ?>">
								<p class="description"><?php echo esc_html__('Default: 10%', 'fme_pgisfw'); ?></p>
							</td>
						</tr>

						<tr>
							<td style="display: flow-root;">
								<input type="button" name="fme_pgisfw_btn" onclick="save_zoom_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
							</td>
							<td id="fme_pgisfw_td">
								<span id="fme_zoom_settings_msg" class="fme_success_alert">
									<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
								</span>
							</td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
