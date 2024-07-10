<?php

if (!class_exists('PH_UPS_Registration_Menu')) {

	class PH_UPS_Registration_Menu
	{
		public function __construct()
		{

			add_action('admin_menu', array($this, 'ph_ups_registration_menu'));

			// Update Account to our our account once registration successfull - Multi Warehoiuse
			// add_filter('ph_ups_plugin_settings', array($this, 'ph_modify_ups_settings_data'), 10, 2);

		}

		/**
		 * Admin Menu
		 */
		public function ph_ups_registration_menu()
		{

			// Add Menu Page for Settings
			add_menu_page(
				__('UPS Registration', 'ups-woocommerce-shipping'),
				__('UPS Registration', 'ups-woocommerce-shipping'),
				'manage_options',
				'ph_ups_registration',
				array($this, 'ph_ups_registration_page'),
				plugins_url('ups-woocommerce-shipping') . '/resources/images/ups-menu-icon.svg',
				56
			);

			add_submenu_page(
				'ph_ups_registration',
				__('Registration', 'ups-woocommerce-shipping'),
				__('Registration', 'ups-woocommerce-shipping'),
				'manage_woocommerce',
				'ph_ups_registration',
				array($this, 'ph_ups_registration_page')
			);

			add_submenu_page(
				'ph_ups_registration',
				__('License Activation', 'ups-woocommerce-shipping'),
				__('License Activation', 'ups-woocommerce-shipping'),
				'manage_woocommerce',
				'ph_ups_license_activation',
				array($this, 'ph_ups_license_activation_page')
			);


			add_submenu_page(
				'ph_ups_registration',
				__('Settings', 'ups-woocommerce-shipping'),
				__('Settings', 'ups-woocommerce-shipping'),
				'manage_woocommerce',
				'ph_ups_plugin_settings',
				array($this, 'ph_ups_plugin_setting_page')
			);
		}

		/**
		 * UPS Registration
		 */
		public function ph_ups_registration_page()
		{
			include_once('html-ph-ups-registration-page-content.php');
		}

		/**
		 * UPS License Activation
		 */
		public function ph_ups_license_activation_page()
		{

			if (!headers_sent() && is_admin()) {

				wp_redirect(admin_url('/options-general.php?page=wc_am_client_ups_woocommerce_shipping_dashboard'));
				exit;
			}
		}

		/**
		 * UPS Plugin Settings
		 */
		public function ph_ups_plugin_setting_page()
		{

			if (!headers_sent() && is_admin()) {

				wp_redirect(admin_url('/admin.php?page=wc-settings&tab=shipping&section=wf_shipping_ups'));
				exit;
			}
		}
	}

	new PH_UPS_Registration_Menu();
}
