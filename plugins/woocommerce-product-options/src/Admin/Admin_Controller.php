<?php

namespace Barn2\Plugin\WC_Product_Options\Admin;

use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Service_Container;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Plugin\Admin\Admin_Links;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\WooCommerce\Admin\Navigation;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\WC_Product_Options\Plugin;
use Barn2\Plugin\WC_Product_Options\Util\Price as Price_Util;
use Barn2\Plugin\WC_Product_Options\Util\Util;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Admin\Settings_Scripts;
use Barn2\Plugin\WC_Product_Options\Dependencies\Lib\Admin\Plugin_Promo;

/**
 * General Admin Functions
 *
 * @package   Barn2\woocommerce-product-options
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Admin_Controller implements Registerable, Service {

	use Service_Container;

	private $plugin;
	private $license_setting;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin          = $plugin;
		$this->license_setting = $plugin->get_license_setting();

		$this->add_services();
	}

	/**
	 * Get the admin services.
	 *
	 * @return void
	 */
	public function add_services() {
		$this->add_service( 'admin_links', new Admin_Links( $this->plugin ) );
		$this->add_service( 'navigation', new Navigation( $this->plugin, 'product-options', __( 'Product Options', 'woocommerce-product-options' ) ) );
		$this->add_service( 'settings_page', new Settings_Page( $this->plugin ) );
		$this->add_service( 'settings_scripts', new Settings_Scripts( $this->plugin ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->register_services();

		// Load admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 5 );

		if ( filter_input( INPUT_GET, 'tab' ) === 'general' ) {
			$plugin_promo = new Plugin_Promo( $this->plugin );
			$plugin_promo->register();
		}
	}

	/**
	 * Load admin assets.
	 *
	 * @param string $hook
	 */
	public function register_assets( string $hook ): void {

		$this->plugin->register_script(
			'wpo-settings-page',
			'assets/js/admin/wpo-settings-page.js',
			array_merge(
				[ 'barn2-tiptip' ],
				Lib_Util::get_script_dependencies( $this->plugin, 'admin/wpo-settings-page.js' )['dependencies']
			),
			$this->plugin->get_version(),
			true
		);

		wp_register_style(
			'wpo-settings-page',
			plugins_url( 'assets/css/admin/wpo-settings-page.css', $this->plugin->get_file() ),
			[ 'wp-components', 'wc-components' ],
			$this->plugin->get_version()
		);

		Util::add_inline_script_params(
			'wpo-settings-page',
			'wpoSettings',
			/**
			 * Filters the parameters for the script of the admin page.
			 *
			 * @param array $params The array of parameters.
			 */
			apply_filters(
				'wc_product_options_settings_app_params',
				[
					'currency'  => Price_Util::get_currency_data(),
					'fileTypes' => get_allowed_mime_types(),
				]
			)
		);
	}

}
