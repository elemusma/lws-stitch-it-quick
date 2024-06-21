<?php


namespace Barn2\Plugin\WC_Product_Options\Admin\Settings_Tab;

use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Admin\Settings_API_Helper;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;

/**
 * The General settings tab.
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class General implements Registerable {

	const TAB_ID       = 'general';
	const OPTION_GROUP = 'wc_product_options_settings';
	const MENU_SLUG    = 'wpo_options';

	private $license_setting;
	private $title;
	private $plugin;

	/**
	 * Get things started.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin          = $plugin;
		$this->license_setting = $plugin->get_license_setting();
		$this->title           = __( 'Settings', 'woocommerce-product-options' );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register the settings.
	 */
	public function register_settings() {

		register_setting(
			self::OPTION_GROUP,
			'wpo_settings',
			[
				'type'        => 'string',
				'description' => 'WooCommerce Product Options settings',
			]
		);

		// Licence Key section.
		Settings_API_Helper::add_settings_section(
			'wpo_license_key',
			self::MENU_SLUG,
			'',
			'',
			[
				$this->license_setting->get_license_key_setting(),
				$this->license_setting->get_license_override_setting()
			]
		);
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

}
