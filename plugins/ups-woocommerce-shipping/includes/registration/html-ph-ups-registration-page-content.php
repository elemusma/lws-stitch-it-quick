<div class="wrap">

	<div class="ph_ups_registration">

		<?php

		include_once('html-ph-ups-registration-page-header.php');

		if (!class_exists('Ph_Ups_Auth_Handler')) {

			include_once(plugin_dir_path(__DIR__) . "api-handler/class-ph-ups-auth-handler.php");
		}

		$phLicenseActivationStatus    = get_option('wc_am_client_ups_woocommerce_shipping_activated');

		if (isset($_POST['ph_ups_account_migration_form'])) {

			update_option('ph_ups_account_migration_consent', true);
		}

		if (isset($_POST['ph_ups_registration_agreement']) && $phLicenseActivationStatus == 'Activated') {

			update_option('ph_ups_registration_consent', true);
		}

		$authProviderToken 			= null;
		$iframeURL 					= null;

		$phProductOrderAPIKey 		= get_option('ph_client_ups_product_order_api_key');
		$phUPSMigrationConsent 		= get_option('ph_ups_account_migration_consent', false);
		$phUPSRegistrationConsent 	= get_option('ph_ups_registration_consent', false);

		$upsSettings 				= get_option('woocommerce_' . WF_UPS_ID . '_settings', []);

		$debugMode 					= isset($upsSettings['debug']) && $upsSettings['debug'] == 'yes' ? true : false;
		$phUPSAccessKey 			= isset($upsSettings['access_key']) && !empty($upsSettings['access_key']) ? $upsSettings['access_key'] : null;
		$phUPSClientCredentials 	= isset($upsSettings['client_credentials']) && !empty($upsSettings['client_credentials']) ? $upsSettings['client_credentials'] : null;
		$phUPSClientLicenseHash 	= isset($upsSettings['client_license_hash']) && !empty($upsSettings['client_license_hash']) ? $upsSettings['client_license_hash'] : null;
		$ups_logo_url				= plugins_url('ups-woocommerce-shipping') . '/resources/images/ph-ups-dap-logo.jpg';


		$phPreferNewRegistration 	= false;

		if (isset($_POST['ph_ups_re_registration'])) {

			$phPreferNewRegistration = true;
		}

		if (!empty($phProductOrderAPIKey) && $phLicenseActivationStatus == 'Activated') {

			$authProviderToken = Ph_Ups_Auth_Handler::phGetAuthProviderToken('ph_iframe');
		}

		if ($debugMode) {

			$phRegistrationPageDetails = [

				'ph_ups_license_status'	 			=> $phLicenseActivationStatus,
				'product_order_api_key' 			=> $phProductOrderAPIKey,
				'ph_ups_client_credentials'			=> $phUPSClientCredentials,
				'ph_ups_client_license_hash'		=> $phUPSClientLicenseHash,
				'ph_ups_iframe_url'					=> $iframeURL,
			];

			Ph_UPS_Woo_Shipping_Common::phAddDebugLog("#---------------------- UPS Registration Details ----------------------#", $debugMode);
			Ph_UPS_Woo_Shipping_Common::phAddDebugLog(print_r($phRegistrationPageDetails, true), $debugMode);
		}


		// When Customer Successfully Registered
		if (!empty($phUPSClientCredentials) && !empty($phUPSClientLicenseHash) && !$phPreferNewRegistration) {

		?>
			<div class="phRegistrationSuceess">

				<p><?php echo __(" Congratulations on successfully registering your UPS Account! Now, it's time to head over to the settings and make any necessary configurations. Get ready for a seamless shipping experience! ", "ups-woocommerce-shipping") ?></p>

				<?php

				$ups_account_type 		= get_option('PH_UPS_REG_ACCOUNT_TYPE');
				$ups_reg_date 			= get_option('PH_UPS_REGISTRATION_DATE');

				if (!empty($ups_account_type)) {

					$shipper_number 	= isset($upsSettings['shipper_number']) && !empty($upsSettings['shipper_number']) ? $upsSettings['shipper_number'] : null;
					$ups_account_type 	= ($ups_account_type == 'UPS_READY') ? __("UPS Ready", "ups-woocommerce-shipping") : __("UPS Digital Access Program(Europe)", "ups-woocommerce-shipping");
				?>

					<?php echo __("Program Name: ", "ups-woocommerce-shipping") ?>
					<b><?= $ups_account_type ?></b>

					<br />

					<?php echo __("Account Number: ", "ups-woocommerce-shipping") ?>
					<b><?= $shipper_number ?></b>

					<?php if (!empty($ups_reg_date)) {

						$ups_reg_date 	= date_i18n(Ph_UPS_Woo_Shipping_Common::get_wordpress_date_format(), strtotime($ups_reg_date));
					?>
						<br />

						<?php echo __("Registration Date: ", "ups-woocommerce-shipping") ?>
						<b><?= $ups_reg_date ?></b>

					<?php } ?>


					<br /><br />

				<?php
				}

				// Successful Registration, But License is not active
				if ($phLicenseActivationStatus != 'Activated') {

				?>
					<p style="color: red"><?php echo __(" It appears that your plugin license is currently deactivated, which means you are unable to utilize the plugin's functionality. Please reactivate your license to regain access. If your license has expired, we kindly request you to renew it in order to continue using the plugin.", 'ups-woocommerce-shipping') ?></p>

					<a target="_BLANK" href="<?php echo admin_url('/options-general.php?page=wc_am_client_ups_woocommerce_shipping_dashboard'); ?>"><?php echo __(' UPS License Activation ', 'ups-woocommerce-shipping') ?></a>
					<span></span>
				<?php

				}
				?>
				<a target="_BLANK" href="<?php echo admin_url('/admin.php?page=wc-settings&tab=shipping&section=wf_shipping_ups'); ?>"><?php echo __(' UPS Plugin Settings ', 'ups-woocommerce-shipping') ?></a>
			</div>
			<?php if ($phLicenseActivationStatus == 'Activated' && !Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) { ?>
				<div class="phReRegistration">
					<hr style="margin-bottom: 35px;">
					<form method="post" action="" id="">
						<p><b><?php _e("UPS Account Registration via OAuth 2.0!", "ups-woocommerce-shipping") ?></b></p>
						<span><?php _e("Since UPS will be phasing out their existing APIs & Access Keys by June 2024, migrating to UPS OAuth Registration is mandatory.", "ups-woocommerce-shipping") ?></span><br /><br />
						<button name="ph_ups_re_registration" id="ph_ups_re_registration" type="submit" value="yes"><?php echo __('OAuth Registration', 'ups-woocommerce-shipping'); ?></button>
					</form>
				</div>
			<?php } ?>

			<?php if ($phLicenseActivationStatus == 'Activated' && Ph_UPS_Woo_Shipping_Common::ph_is_oauth_registered_customer()) { ?>
				<div class="phReRegistration">

					<hr style="margin-bottom: 35px;">

					<p><b><?php _e("UPS Account Management", "ups-woocommerce-shipping") ?></b></p>

					<p>
						<?php echo __("This section allows you to update or modify UPS Account Details for shipping purposes. Please note that updating the UPS Account Details will affect the following functionality:", "ups-woocommerce-shipping") ?>
					<ol>
						<li type="circle"><?php echo __("Void Shipment - Please make sure to void the shipments that need to be voided in your WooCommerce store before proceeding.", "ups-woocommerce-shipping") ?></li>
					</ol>
					</p>

					<button name="ph_ups_rest_re_registration" id="ph_ups_rest_re_registration" type="submit" value="yes"><?php echo __('Remove Account & Re-Register', 'ups-woocommerce-shipping'); ?></button>
				</div>
			<?php } ?>
		<?php

			// Existing Customers who configured UPS Details
		} elseif (!empty($phUPSAccessKey) && !$phUPSMigrationConsent) {

		?>
			<div class="phUPSAccountMigration">

				<form method="post" action="" id="">

					<p><?php echo __(' Based on the UPS Plugin Settings, it appears that you have successfully configured UPS Details with Access Keys. As a result, your current plugin will continue to operate smoothly until June 3, 2024. Are you still interested in proceeding? ', 'ups-woocommerce-shipping') ?></p>

					<p class="submit" style="text-align:center">
						<button name="ph_ups_account_migration_form" id="ph_ups_account_migration_form" type="submit" value="Agree & Continue"><?php echo __('Agree & Continue', 'ups-woocommerce-shipping'); ?></button>
					</p>

				</form>
			</div>
		<?php

			// Existing Customers who has Active License but its not registered at PluginHive Servers
		} elseif (!empty($phUPSAccessKey) && empty($phProductOrderAPIKey)) {

		?>
			<div class="phUPSAccountMigration">

				<p style="color: red"><?php echo __(' In order to proceed, please deactivate the Plugin License and then reactivate it. Once you have completed this step, kindly return to the Registration Page to continue.', 'ups-woocommerce-shipping') ?></p>

				<a target="_BLANK" href="<?php echo admin_url('/options-general.php?page=wc_am_client_ups_woocommerce_shipping_dashboard'); ?>"><?php echo __(' UPS License Activation ', 'ups-woocommerce-shipping') ?></a>

			</div>

		<?php
			// Registration Consent for all Customers
		} elseif ((!$phUPSRegistrationConsent || $phLicenseActivationStatus != 'Activated' || empty($authProviderToken)) && !isset($phRegistrationPageDetails)) {

			include_once('html-ph-ups-consent-and-validation.php');

			// Registration Page
		} else {

			$ups_ready_url = Ph_UPS_Woo_Shipping_Common::ph_get_ups_reg_url($authProviderToken, $phProductOrderAPIKey, 'UPS_READY');
			$ups_dap_url	= Ph_UPS_Woo_Shipping_Common::ph_get_ups_reg_url($authProviderToken, $phProductOrderAPIKey, 'UPS_DAP');

		?>


			<div class='ph-ups-reg-info'>

				<img src='<?php echo $ups_logo_url ?>' alt='ups_logo' class='ph-ups-logo'>

				<pre><?php _e('UPS, the UPS brandmark, UPS Ready®, and the color brown are trademarks of United Parcel Service of America, Inc. All Rights Reserved.', 'ups-woocommerce-shipping') ?></pre>
				<br>
				<h3><?php _e('Login to Existing UPS Account', 'ups-woocommerce-shipping') ?></h3>
				<p><?php _e('Integrate your existing UPS account with PluginHive to use your negotiated UPS shipping rates', 'ups-woocommerce-shipping') ?></p>
				<button id='ph-ups-ready-btn' data-ups-reg-url='<?php echo $ups_ready_url; ?>' data-reg-type='UPS_READY' class='button'><?php _e('Login with UPS Ready', 'ups-woocommerce-shipping') ?></button>
				<br>
				<hr>
				<br>
				<h2><?php _e('Sign up for a New UPS Business Account & Get Up to 75% Discount for Merchants in Europe', 'ups-woocommerce-shipping') ?></h2>
				<p><?php _e('Your new UPS account will be ready instantly by filling up the store details. No documentation or minimum shipment contract required.', 'ups-woocommerce-shipping') ?></p>
				<p><i><?php _e('Terms & Conditions Apply', 'ups-woocommerce-shipping') ?></i></p>
				<pre> * <?php _e('Discounts available for merchants shipping from <b>United Kingdom, Germany, Netherlands, France, Italy, Spain, & Belgium</b>', 'ups-woocommerce-shipping') ?></pre>
				<button id='ph-ups-dap-btn' data-ups-reg-url='<?php echo $ups_dap_url; ?>' data-reg-type='UPS_DAP' class='button'><?php _e('Sign Up', 'ups-woocommerce-shipping') ?></button>
				<br>
				<p><?php _e('Read More - <a href="https://www.pluginhive.com/knowledge-base/connect-ups-with-woocommerce/" target="_blank">How to Connect UPS Account to WooCommerce?</a>', 'ups-woocommerce-shipping'); ?></p>
			</div>

		<?php


		}

		?>

	</div>