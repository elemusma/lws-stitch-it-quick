<div class="ph_ups_registration_consent">

	<form method="post" action="" id="">

		<h2><?php echo __('Important Updates Regarding UPS Account Integration with PluginHive', 'ups-woocommerce-shipping') ?></h2>


		<div class="ph_ups_registration_consent_data">

			<p><?php echo __('In order to enhance customer safety, reduce fraud, and offer advanced API features, UPS has implemented OAuth 2.0 for API security. Effective from June 5, 2023, access keys will no longer be issued. Starting June 5, 2024, clients must utilize OAuth by including a bearer token with each API request.', 'ups-woocommerce-shipping') ?></p>
			<p><?php echo __('PluginHive, as a UPS Ready Business Solutions provider, is committed to ensuring a seamless transition for all merchants, ensuring the use of the latest UPS APIs without any disruption to your business. This requires few significant changes to our business model:', 'ups-woocommerce-shipping') ?></p>

			<span></span>
			<p><?php echo __(' All UPS API calls will now be routed through PluginHive.io. This ensures secure communication between customers and UPS, with customer data, including UPS account details, exclusively transmitted through PluginHive.io. Rest assured that PluginHive guarantees the safety of this data and it will not be shared with any other entity for any other purpose.', 'ups-woocommerce-shipping') ?></p>
			<span></span>
			<p><?php echo __(' To continue using UPS services, it is essential to maintain an up-to-date plugin license. Once your current license expires, the plugin will no longer function. Therefore, customers must renew their plugin license in order to continue utilizing the shipping capabilities offered by the plugin.', 'ups-woocommerce-shipping') ?></p>
			<span></span>
			<p><?php echo __(' The existing licenses for the 5 site and 25 site plugins will no longer be valid. Instead, customers will need to purchase individual licenses for each website they wish to integrate with UPS.', 'ups-woocommerce-shipping') ?></p>
			<span></span>
			<p><?php echo __(' Once customers opt for the new integration method using OAuth, it will not be possible to revert back to the previous method that involved UPS Web Access keys.', 'ups-woocommerce-shipping') ?></p>
			<span></span>
			<p><?php echo __(' Please note that the plugin license serves as authorization and is strictly non-transferable. It is intended solely for the customer who initially acquired it and cannot be transferred or assigned to any other individual or entity.', 'ups-woocommerce-shipping') ?></p>
			<span></span>
			<p><?php echo __(' The plugin license grants you the ability to process up to 15,000 orders per month. This allocation is ideal for SMALL and MEDIUM-sized eCommerce businesses. However, if you operate a LARGE business that handles more than 500 orders per day, please reach out to <a href="https://www.pluginhive.com/support/" target="_BLANK">PluginHive Support</a> to receive a personalized quote.', 'ups-woocommerce-shipping') ?></p>

			<hr />

			<p><?php echo __('We appreciate your cooperation in making these necessary updates.', 'ups-woocommerce-shipping') ?></p>
			<p><b><?php echo __('NOTE: If you have already received UPS Access keys, your current plugin will continue to function without any issues until June 3, 2024.', 'ups-woocommerce-shipping') ?></b></p>
			<p><?php echo __('If you have any further questions or require assistance with the transition, please don’t hesitate to contact <a href="https://www.pluginhive.com/support/" target="_BLANK">PluginHive Support</a>', 'ups-woocommerce-shipping') ?></p>

		</div>

		<?php

		if ($phLicenseActivationStatus != "Activated") {

			echo "<p style='color: red; text-align:left;'>" . __(' It seems that your plugin license has been deactivated, which means you will not be able to utilize the plugin\'s functionality. Please reactivate your license to regain access. If your license has expired, we kindly request you to renew it in order to proceed further.', 'ups-woocommerce-shipping') . "</p>";
			echo '<p style="text-align:left;"><a target="_BLANK" href="'.admin_url('/options-general.php?page=wc_am_client_ups_woocommerce_shipping_dashboard').'">'.__(' UPS License Activation ', 'ups-woocommerce-shipping').'</a></p>';
			
		} else {
		?>
			<div class="ph_ups_registration_agreement_check">

				<p class="ph_ups_registration_agreement_statement">
					<input type="checkbox" id="ph_ups_registration_agreement" name="ph_ups_registration_agreement">
					<?php echo __(' By checking this box, you acknowledge and agree to the above-mentioned changes to UPS account integration with PluginHive applications.', 'ups-woocommerce-shipping'); ?>
				</p>

			</div>

			<p class="submit">
				<button name="ph_ups_registration_consent_form" id="ph_ups_registration_consent_form" type="submit" value="Agree & Continue"><?php echo __('Agree & Continue', 'ups-woocommerce-shipping'); ?></button>
			</p>

		<?php } ?>

	</form>

</div>