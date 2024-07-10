<?php

$instance_id =  ( isset( $this->instance_id ) && !empty( $this->instance_id ) ) ? $this->instance_id : '';
$this->custom_services = array();

// To update the frontend with recent changes on clicking Save Changes
if (!empty($instance_id) && isset($this->instance_settings['services'])) {
	$this->custom_services = $this->instance_settings['services'];

} elseif (isset($this->settings['services'])) {	
	$this->custom_services = $this->settings['services'];
}

?>
<tr valign="top" id="service_options" <?php if( empty($instance_id) ) echo 'class="ph_ups_rates_tab"'; ?>>
	<td class="forminp" colspan="2">
		<table class="ups_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php _e('Service Code', 'ups-woocommerce-shipping'); ?></th>
				<th><?php _e('Name', 'ups-woocommerce-shipping'); ?></th>
				<th class="check-column"><label for="ckbCheckAll"><input type="checkbox" id="upsCheckAll"/>
						<div class='enabled-label'><?php _e('Enabled', 'ups-woocommerce-shipping'); ?></div>
					</label></th>
				<th><?php echo sprintf(__('Price Adjustment (%s)', 'ups-woocommerce-shipping'), get_woocommerce_currency_symbol()); ?></th>
				<th><?php _e('Price Adjustment (%)', 'ups-woocommerce-shipping'); ?></th>
			</thead>
			<tfoot>
				<?php

				if (!$this->origin_country == 'PL' && !in_array($this->origin_country, PH_WC_UPS_Constants::EU_ARRAY)) {
				?>
					<tr>
						<th colspan="6">
							<small class="description"><?php _e('<strong>Domestic Rates</strong>: Next Day Air, 2nd Day Air, Ground, 3 Day Select, Next Day Air Saver, Next Day Air Early AM, 2nd Day Air AM', 'ups-woocommerce-shipping'); ?></small><br />
							<small class="description"><?php _e('<strong>International Rates</strong>: Worldwide Express, Worldwide Expedited, Standard, Worldwide Express Plus, UPS Saver', 'ups-woocommerce-shipping'); ?></small>
						</th>
					</tr>
				<?php
				}
				?>
			</tfoot>
			<tbody>
				<?php
				$sort = 0;
				$this->ordered_services = array();
				$use_services = $this->services;

				// These services are not supported by UPS REST API
				$rest_api_deprecated_services = array('US48', '308', '309', '334', '349');

				if ( !Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) {

					if ($this->enable_freight == true) {
						$use_services = (array)$use_services + (array)PH_WC_UPS_Constants::FREIGHT_SERVICES;    //array + NULL will throw fatal error in php version 5.6.21
					}
				}

				foreach ($use_services as $code => $name) {

					if (!empty($this->custom_services) && isset($this->custom_services[$code]) && isset($this->custom_services[$code]['order']) && !empty($this->custom_services[$code]['order'])) {
						$sort = $this->custom_services[$code]['order'];
					}

					while (isset($this->ordered_services[$sort]))
						$sort++;

					$this->ordered_services[$sort] = array($code, $name);

					$sort++;
				}

				ksort($this->ordered_services);

				foreach ($this->ordered_services as $value) {
					
					$code = $value[0];
					$name = $value[1];

					if (Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer() && in_array($code, $rest_api_deprecated_services)) {
						continue;
					}

					$service_order 	 			= isset($this->custom_services[$code]['order']) ? $this->custom_services[$code]['order'] : '';
					$service_name  	 			= isset($this->custom_services[$code]['name']) ? $this->custom_services[$code]['name'] : '';
					$service_enabled 			= (!isset($this->custom_services[$code]['enabled']) || !empty($this->custom_services[$code]['enabled'])) ? 'checked' : '';
					$service_price_adjustment 	= isset($this->custom_services[$code]['adjustment']) ? $this->custom_services[$code]['adjustment'] : '';
					$service_percent_adjustment = isset($this->custom_services[$code]['adjustment_percent']) ? $this->custom_services[$code]['adjustment_percent'] : '';
				?>
					<tr>
						<td class="sort">
							<input type="hidden" class="order" name="ups_service[<?php echo $code; ?>][order]" value="<?php echo $service_order ?>" />
						</td>
						<td>
							<strong><?php echo $code; ?></strong>
							<?php if ($code == 96) : ?>
							<span class="xa-tooltip">
								<img src="<?php echo site_url('/wp-content/plugins/woocommerce/assets/images/help.png'); ?>" height="16" width="16" />
								<span class="xa-tooltiptext">
									In case of Weight Based Packaging, Package Dimensions will be 47x47x47 inches or 119x119x119 cm.
								</span>
							</span>
							<?php endif; ?>
						</td>
						<td>
							<input type="text" name="ups_service[<?php echo $code; ?>][name]" placeholder="<?php echo $name; ?>" value="<?php echo $service_name ?>" size="50" />
						</td>
						<td>
							<input type="checkbox" class="checkBoxClass" name="ups_service[<?php echo $code; ?>][enabled]" <?php echo $service_enabled ?> />
						</td>
						<td>
							<input type="text" name="ups_service[<?php echo $code; ?>][adjustment]" placeholder="N/A" value="<?php echo $service_price_adjustment ?>" size="4" />
						</td>
						<td>
							<input type="text" name="ups_service[<?php echo $code; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo $service_percent_adjustment ?>" size="4" />
						</td>
					</tr>
				<?php

				}
				?>
			</tbody>
		</table>
	</td>
</tr>