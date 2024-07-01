<?php 
$fme_pgisfw_bullets_settings = get_option('fme_pgisfw_bullets_settings');
if (isset($_GET['rule_id'])) {
	$rule_id=filter_var($_GET['rule_id']);

	$fme_full_option=get_option('fme_pgisfw_save_rule_settings');
	
	$fme_pgisfw_bullets_settings=$fme_full_option[$rule_id];

	
}

?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Bullets Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

				<tr valign="top" class="">
					<th id="fme_pgisfw_th" scope="row" class="titledesc">
						<label for="fme_pgisfw_show_bullets">
							<?php echo esc_html__('Show bullets', 'fme_pgisfw'); ?>
						</label>
					</th>
					<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
						<input name="fme_pgisfw_show_bullets" 
						<?php
						if ( 'yes' == $fme_pgisfw_bullets_settings['fme_pgisfw_show_bullets']) {
							echo 'checked';} 
						?>
							id="fme_pgisfw_show_bullets" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_bullets_settings['fme_pgisfw_show_bullets']); ?>">
							<span><?php esc_html_e('Show Bullets For Gallery Slider', 'fme_pgisfw'); ?></span>
						</td>
					</tr>



					<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_bullets_shape"><?php echo esc_html__('Bullets shape', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select shape of bullets i.e Circle for rounded bullets and square for box shape bullets."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-select">
							<select name="fme_pgisfw_bullets_shape" id="fme_pgisfw_bullets_shape" class="">
								<option <?php selected('circular', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="circular" selected="selected"><?php echo esc_html__('Circle', 'fme_pgisfw'); ?></option>
								<option <?php selected('square', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="square"><?php echo esc_html__('Box', 'fme_pgisfw'); ?></option>
								<option <?php selected('lines', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="lines"><?php echo esc_html__('Lines', 'fme_pgisfw'); ?></option>
								<option <?php selected('counter_bullets', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="counter_bullets"><?php echo esc_html__('Counter bullets', 'fme_pgisfw'); ?></option>
								<option <?php selected('bar_counter_bullets', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="bar_counter_bullets"><?php echo esc_html__('Bottom counter bar ', 'fme_pgisfw'); ?></option>
								<option <?php selected('bottom_bar', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_shape'] : '' ); ?> value="bottom_bar"><?php echo esc_html__('Bottom bar ', 'fme_pgisfw'); ?></option>
							</select> 							
						</td>
					</tr>

					<tr id="fme_pgisfw_counter_bullets_font_color_row">
						<th id="fme_pgisfw_th" scope="row">
							<label for="fme_pgisfw_counter_bullets_font_color"><?php echo esc_html__('Counter bullets Font Color', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select color to show on bullet hover."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td">
							<?php
							// haseeb changed
							if (!isset($fme_pgisfw_bullets_settings['fme_pgisfw_counter_bullets_font_color'] ) ) {
								$fme_pgisfw_bullets_settings['fme_pgisfw_counter_bullets_font_color']='#000000';
							}
							//
							
							$opacity = '';
							if (strlen($fme_pgisfw_bullets_settings['fme_pgisfw_counter_bullets_font_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_counter_bullets_font_color" id="fme_pgisfw_counter_bullets_font_color" value="<?php echo esc_attr($fme_pgisfw_bullets_settings['fme_pgisfw_counter_bullets_font_color']) . filter_var($opacity); ?>">
						</td>
					</tr>


					<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_bullets_position"><?php echo esc_html__('Bullets placement', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select position of bullets when thumbnail is hidden from 'Thumbnail Settings'."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-select">
							<select name="fme_pgisfw_bullets_position" id="fme_pgisfw_bullets_position">
								<option <?php selected('inside_image', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_position'] : '' ); ?> value="inside_image"><?php echo esc_html__('Inside Image', 'fme_pgisfw'); ?></option>
								<option <?php selected('bellow_image', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_position'] : '' ); ?> value="bellow_image"><?php echo esc_html__('Bellow Image', 'fme_pgisfw'); ?></option>
							</select> 							
						</td>
					</tr>


					<tr valign="top">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_bullets_inside_position"><?php echo esc_html__('Bullets position', 'fme_pgisfw'); ?>
								<span class="woocommerce-help-tip" data-tip="Select position of bullets when 'Bullets placement' is set to 'inside image'."></span>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-select">
							<select name="fme_pgisfw_bullets_inside_position" id="fme_pgisfw_bullets_inside_position">
								<option <?php selected('bottom_left', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="bottom_left"><?php echo esc_html__('Bottom left', 'fme_pgisfw'); ?></option>
								<option <?php selected('bottom_center', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="bottom_center"><?php echo esc_html__('Bottom center', 'fme_pgisfw'); ?></option>
								<option <?php selected('bottom_right', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="bottom_right"><?php echo esc_html__('Bottom right', 'fme_pgisfw'); ?></option>
								<option <?php selected('top_left', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="top_left"><?php echo esc_html__('Top Left', 'fme_pgisfw'); ?></option>
								<option <?php selected('top_center', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="top_center"><?php echo esc_html__('Top center', 'fme_pgisfw'); ?></option>
								<option <?php selected('top_right', ( isset( $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] ) ) ? $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_inside_position'] : '' ); ?> value="top_right"><?php echo esc_html__('Top right', 'fme_pgisfw'); ?></option>
							</select> 							
						</td>
					</tr>


					<tr valign="top" class="">
						<th id="fme_pgisfw_th" scope="row" class="titledesc">
							<label for="fme_pgisfw_bullets_thumbnail">
								<?php echo esc_html__('Show bullets thumbnail', 'fme_pgisfw'); ?>
							</label>
						</th>
						<td id="fme_pgisfw_td" class="forminp forminp-checkbox">
							<input name="fme_pgisfw_bullets_thumbnail" 
							<?php
							if ( 'yes' == $fme_pgisfw_bullets_settings['fme_pgisfw_bullets_thumbnail']) {
								echo 'checked';} 
							?>
								id="fme_pgisfw_bullets_thumbnail" type="checkbox" class="" value="<?php echo esc_attr($fme_pgisfw_bullets_settings['fme_pgisfw_bullets_thumbnail']); ?>">
								<span><?php esc_html_e('Show thumbnail on bullets hover', 'fme_pgisfw'); ?></span>
							</td>
						</tr>





					<tr>
						<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_bullets_color"><?php echo esc_html__('Bullets background color', 'fme_pgisfw'); ?></label><span class="woocommerce-help-tip" data-tip="Select color to show on bullet points."></span>
						</th>
						<td id="fme_pgisfw_td">
							<?php
							$opacity = '';
							if (strlen($fme_pgisfw_bullets_settings['fme_pgisfw_bullets_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_bullets_color" id="fme_pgisfw_bullets_color" value="<?php echo esc_attr($fme_pgisfw_bullets_settings['fme_pgisfw_bullets_color']) . filter_var($opacity); ?>">
						</td>
					</tr>
					<tr>
						<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_bullets_hover_color"><?php echo esc_html__('Bullets hover color', 'fme_pgisfw'); ?></label><span class="woocommerce-help-tip" data-tip="Select color to show on bullet hover."></span>
						</th>
						<td id="fme_pgisfw_td">
							<?php
							$opacity = '';
							if (strlen($fme_pgisfw_bullets_settings['fme_pgisfw_bullets_hover_color']) == 7) {
								$opacity = 'AA';
							}
							?>
							<input type="text" class="jscolor" name="fme_pgisfw_bullets_hover_color" id="fme_pgisfw_bullets_hover_color" value="<?php echo esc_attr($fme_pgisfw_bullets_settings['fme_pgisfw_bullets_hover_color']) . filter_var($opacity); ?>">
						</td>
					</tr>
					<tr>
						<th style="display: flow-root;">
							<input type="button" name="fme_pgisfw_btn" onclick="save_bullets_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
						</th>
						<td id="fme_pgisfw_td">

							<span id="fme_bullets_settings_msg" class="fme_success_alert">
								<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
							</span>
						</td>
					</tr>

				</tbody>
			</table>
		</div>
	</div>
