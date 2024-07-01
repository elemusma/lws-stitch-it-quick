<?php 
$fme_pgisfw_arrows_settings = get_option('fme_pgisfw_arrows_settings');
if (isset($_GET['rule_id'])) {
	$rule_id=filter_var($_GET['rule_id']);

	$fme_full_option=get_option('fme_pgisfw_save_rule_settings');
	
	$fme_pgisfw_arrows_settings=$fme_full_option[$rule_id];

}
?>
<div class="panel panel-info" id="fme_settings_panel">
	<div class="panel-heading">
		<h2 class="panel-title" id="fme_panel_title"><?php echo esc_html__('Arrow Settings', 'fme_pgisfw'); ?></h2>
	</div>
	<div class="panel-body">
		<table class="form-table fme_table_admin" role="presentation">
			<tbody>

				<tr class="navIcon">
					<th scope="row" id="fme_pgisfw_th">
						<label for="Navigation_Icons"><?php echo esc_html__('Show navigation icons', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Show arrow buttons to navigate images in slider."></span>
						</label>
					</th>
					<td id="fme_pgisfw_td">
						<select class="regular" name="fme_pgisfw_navigation_icon" id="fme_pgisfw_navigation_icon">
							<option <?php selected('true', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_status'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_status'] : '' ); ?> value="true"><?php echo esc_html__('Yes', 'fme_pgisfw'); ?></option>
							<option <?php selected('false', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_status'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_status'] : '' ); ?> value="false"><?php echo esc_html__('No', 'fme_pgisfw'); ?></option>
						</select>
						<p class="fme_pgisfw_description"><?php echo esc_html__('Default: Yes', 'fme_pgisfw'); ?></p>
					</td>
				</tr>


				<tr class="navIcon">
					<th scope="row" id="fme_pgisfw_th">
						<label for="Navigation_Icons"><?php echo esc_html__('Show navigation on', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select option to show navigation on hover or fixed."></span>
						</label>
					</th>
					<td id="fme_pgisfw_td">
						<select class="regular" name="fme_pgisfw_navigation_icon_show_on" id="fme_pgisfw_navigation_icon_show_on">
							<option <?php selected('hover', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_show_on'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_show_on'] : '' ); ?> value="hover"><?php echo esc_html__('On Hover', 'fme_pgisfw'); ?></option>
							<option <?php selected('fix', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_show_on'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_show_on'] : '' ); ?> value="fix"><?php echo esc_html__('Fixed', 'fme_pgisfw'); ?></option>
						</select>
						<p class="fme_pgisfw_description"><?php echo esc_html__('Default: On hover', 'fme_pgisfw'); ?></p>
					</td>
				</tr>


				<tr class="navIcon">
					<th scope="row" id="fme_pgisfw_th">
						<label for="Navigation_Icons"><?php echo esc_html__('Navigation button shape', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select navigation Button shape."></span>
						</label>
					</th>
					<td id="fme_pgisfw_td">
						<select class="regular" name="fme_pgisfw_navigation_icon_shape" id="fme_pgisfw_navigation_icon_shape">
							<option <?php selected('rounded', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_shape'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_shape'] : '' ); ?> value="rounded"><?php echo esc_html__('Circle', 'fme_pgisfw'); ?></option>
							<option <?php selected('square', ( isset( $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_shape'] ) ) ? $fme_pgisfw_arrows_settings['fme_pgisfw_navigation_icon_shape'] : '' ); ?> value="square"><?php echo esc_html__('Square', 'fme_pgisfw'); ?></option>
						</select>
						<p class="fme_pgisfw_description"><?php echo esc_html__('Default: Circle', 'fme_pgisfw'); ?></p>
					</td>
				</tr>


				<tr>
					<th id="fme_pgisfw_th" scope="row">
						<label for="fme_pgisfw_navigation_background_color"><?php echo esc_html__('Navigation button background color', 'fme_pgisfw'); ?>
							<span class="woocommerce-help-tip" data-tip="Select background color of navigation buttons.">
						</label>
					
					</span>
				</th>
				<td id="fme_pgisfw_td">
					<?php
					$opacity = '';
					if (strlen($fme_pgisfw_arrows_settings['fme_pgisfw_navigation_background_color']) == 7) {
						$opacity = 'AA';
					}
					?>
					<input type="text" class="jscolor" name="fme_pgisfw_navigation_background_color" id="fme_pgisfw_navigation_background_color" value="<?php echo esc_attr($fme_pgisfw_arrows_settings['fme_pgisfw_navigation_background_color']) . filter_var($opacity); ?>">
				</td>
			</tr>



			<tr>
				<th id="fme_pgisfw_th" scope="row">
					<label for="fme_pgisfw_navigation_hover_color">
						<?php echo esc_html__('navigation button hover color', 'fme_pgisfw'); ?>
						<span class="woocommerce-help-tip" data-tip="Select hover color of navigation buttons."></span>
					</label>
					
				</th>
				<td id="fme_pgisfw_td">
					<?php
					$opacity = '';
					if (strlen($fme_pgisfw_arrows_settings['fme_pgisfw_navigation_hover_color']) == 7) {
						$opacity = 'AA';
					}
					?>
					<input type="text" class="jscolor" name="fme_pgisfw_navigation_hover_color" id="fme_pgisfw_navigation_hover_color" value="<?php echo esc_attr($fme_pgisfw_arrows_settings['fme_pgisfw_navigation_hover_color']) . filter_var($opacity); ?>">
				</td>
			</tr>


			<tr>
				<th id="fme_pgisfw_th" scope="row"><label for="fme_pgisfw_icon_color"><?php echo esc_html__('Navigation icon color', 'fme_pgisfw'); ?>
					<span class="woocommerce-help-tip" data-tip="Select color of icon shown in navigation buttons."></span>
				</label>
				</th>
				<td id="fme_pgisfw_td">
					<?php
					$opacity = '';
					if (strlen($fme_pgisfw_arrows_settings['fme_pgisfw_icon_color']) == 7) {
						$opacity = 'AA';
					}
					?>
					<input type="text" class="jscolor" name="fme_pgisfw_icon_color" id="fme_pgisfw_icon_color" value="<?php echo esc_attr($fme_pgisfw_arrows_settings['fme_pgisfw_icon_color']) . filter_var($opacity); ?>">
				</td>
			</tr>

			<tr>
				<th id="fme_pgisfw_th" scope="row"><label ><?php echo esc_html__('Select navigation icon', 'fme_pgisfw'); ?>
					<span class="woocommerce-help-tip" data-tip="Select icon to shown in navigation buttons."></span>
				</label>
				</th>
				<td id="fme_pgisfw_td">
					<div class='fme_arrows_options'>
						<div >
							<img class="fme_arrow_images <?php echo ( 0 == $fme_pgisfw_arrows_settings['fme_selected_image'] ) ? 'fme_selected_image' : ''; ?>" src="<?php echo filter_var(FME_PGISFW_URL) . 'admin/assets/images/img1.png'; ?>" curr_image="0">
						</div>
						<div >
							<img class="fme_arrow_images <?php echo ( 1 == $fme_pgisfw_arrows_settings['fme_selected_image'] ) ? 'fme_selected_image' : ''; ?>" src="<?php echo filter_var(FME_PGISFW_URL) . 'admin/assets/images/img2.png'; ?>" curr_image="1">
						</div>
						<div >
							<img class="fme_arrow_images <?php echo ( 2 == $fme_pgisfw_arrows_settings['fme_selected_image'] ) ? 'fme_selected_image' : ''; ?>" src="<?php echo filter_var(FME_PGISFW_URL) . 'admin/assets/images/img3.png'; ?>" curr_image="2">
						</div>
						<div >
							<img class="fme_arrow_images <?php echo ( 3 == $fme_pgisfw_arrows_settings['fme_selected_image'] ) ? 'fme_selected_image' : ''; ?>" src="<?php echo filter_var(FME_PGISFW_URL) . 'admin/assets/images/img4.png'; ?>" curr_image="3">
						</div>
						<div >
							<img class="fme_arrow_images <?php echo ( 4 == $fme_pgisfw_arrows_settings['fme_selected_image'] ) ? 'fme_selected_image' : ''; ?>" src="<?php echo filter_var(FME_PGISFW_URL) . 'admin/assets/images/img5.png'; ?>" curr_image="4">
						</div> 
					</div>
				</td>
			</tr>		
			
			<tr>
				<th style="display: flow-root;">
					<input type="button" name="fme_pgisfw_btn" onclick="save_arrows_settings();" class="button-primary fme_pgisfw_btn" value="<?php echo esc_html__('Save Settings', 'fme_pgisfw'); ?>">
				</th>
				<td id="fme_pgisfw_td">
					<span id="fme_arrows_settings_msg" class="fme_success_alert">
						<b><?php esc_html_e('Success!', 'fme_pgisfw'); ?></b> <?php echo esc_html__(' Settings Saved Successfully!', 'fme_pgisfw'); ?>
					</span>
				</td>
			</tr>

		</tbody>
	</table>
</div>
</div>
