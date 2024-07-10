<tr valign="top" id="packing_options" class="ph_ups_packaging_tab">
			<td class="forminp" colspan="2" style="padding-left:0px">
				<strong><?php _e('Box Dimensions', 'ups-woocommerce-shipping'); ?></strong><br />
				<table class="ups_boxes widefat">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" /></th>

							<th><?php _e('Box Name', 'ups-woocommerce-shipping'); ?></th>
							<th><?php _e('Outer Length', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Outer Width', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Outer Height', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Inner Length', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Inner Width', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Inner Height', 'ups-woocommerce-shipping');
								echo "( $this->dim_unit )"; ?></th>
							<th><?php _e('Box Weight', 'ups-woocommerce-shipping');
								echo "( $this->weight_unit )"; ?></th>
							<th><?php _e('Max Weight', 'ups-woocommerce-shipping');
								echo "( $this->weight_unit)"; ?></th>
							<th><?php _e('Enabled', 'ups-woocommerce-shipping'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="3">
								<a href="#" class="button plus insert"><?php _e('Add Box', 'ups-woocommerce-shipping'); ?></a>
								<a href="#" class="button minus remove"><?php _e('Remove selected box(es)', 'ups-woocommerce-shipping'); ?></a>
								
								<a href=<?= admin_url('admin.php?page=wc-settings&tab=shipping&section=wf_shipping_ups&ph_ups_reset_boxes') ?> class="button minus reset"><?php _e('Reset Box(es)', 'ups-woocommerce-shipping'); ?></a>
							</th>
							<th colspan="8">
								<small class="description"><?php _e('Items will be packed into these boxes depending based on item dimensions and volume. Outer dimensions will be passed to UPS, whereas inner dimensions will be used for packing. Items not fitting into boxes will be packed individually.', 'ups-woocommerce-shipping'); ?></small>
							</th>
						</tr>
					</tfoot>
					<tbody id="rates">
						<?php

						//To update the frontend with recent changes on clicking Save Changes
						$this->boxes			= (isset($this->settings['boxes']) && is_array($this->settings['boxes'])) ? $this->settings['boxes'] : $default_boxes;
						$this->ups_packaging	= isset($this->settings['ups_packaging']) ? $this->settings['ups_packaging'] : [];

						// Sort boxes based on key to bring the custom boxes at the end of the table.
						ksort($this->boxes);

						if ($this->boxes && !empty($this->boxes)) {
							foreach ($this->boxes as $key => $box) {

								if (!$this->upsSimpleRate && array_key_exists($key, $this->simpleRateBoxes)) {

									echo '<tr style="display:none;">';
								} else {

									echo '<tr>';
								}
						?>

								<?php

								$boxName = isset($box['boxes_name']) ? $box['boxes_name'] : '';

								// Default boxes cannot be removed from settings
								if (array_key_exists($key, $this->packaging) || array_key_exists($key, $this->simpleRateBoxes)) {

									echo '<td class="check-column">';
									if ( $key == 'D_25KG_BOX' || $key == 'E_10KG_BOX') {
										echo '<span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">'.__("Use the box for shipments from the United States and Puerto Rico for Worldwide Express and Worldwide Express Plus services only.", 'ups-woocommerce-shipping').'</span></span>';
									}
									echo '</td>';
									
									echo '<td><input type="text" size="8" readonly name="boxes_name[' . $key . ']" value="' . $box['boxes_name'] . '" /></td>';
								} else {

									echo '<td class="check-column"><input type="checkbox" /></td>';
									echo '<td><input type="text" size="8" name="boxes_name[' . $key . ']" value="' . $boxName . '" /></td>';
								}
								?>
								<td><input type="text" size="5" name="boxes_outer_length[<?= $key; ?>]" value="<?= esc_attr($box['outer_length']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_outer_width[<?= $key; ?>]" value="<?= esc_attr($box['outer_width']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_outer_height[<?= $key; ?>]" value="<?= esc_attr($box['outer_height']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_length[<?= $key; ?>]" value="<?= esc_attr($box['inner_length']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_width[<?= $key; ?>]" value="<?= esc_attr($box['inner_width']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_inner_height[<?= $key; ?>]" value="<?= esc_attr($box['inner_height']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_box_weight[<?= $key; ?>]" value="<?= esc_attr($box['box_weight']); ?>" /></td>
								<td><input type="text" size="5" name="boxes_max_weight[<?= $key; ?>]" value="<?= esc_attr($box['max_weight']); ?>" /></td>


								<?php

								// Enable default boxes based on previous settings or if box enabled from current version of the plugin.
								if (isset($box['box_enabled']) && $box['box_enabled']) {

									echo '<td class="is-enabled"><input type="checkbox" name="boxes_enabled[' . $key . ']" checked /></td>';
								} else {

									echo '<td class="is-enabled"><input type="checkbox" name="boxes_enabled[' . $key . ']"/></td>';
								}

								?>
							</tr>
<?php
							}
						}

				?>
			</tbody>
		</table>
	</td>
</tr>